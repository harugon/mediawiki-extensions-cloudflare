<?php

namespace MediaWiki\Extension\Cloudflare;

use Config;
use ManualLogEntry;
use MediaWiki\Hook\LocalFilePurgeThumbnailsHook;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\Page\Hook\ArticlePurgeHook;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\Title\TitleFactory;

/**
 * Class HookHandler
 *
 * https://developers.cloudflare.com/cache/concepts/default-cache-behavior/
 * historyページはキャッシュされないmax-age=0のため
 *
 */
class HookHandler implements
	ArticlePurgeHook,
	LocalFilePurgeThumbnailsHook,
	PageDeleteCompleteHook,
	PageMoveCompleteHook,
	PageSaveCompleteHook
{
	use UrlExpander;

	private CloudflareAPIRequester $cloudflareAPIRequester;
	private Config $config;
	private TitleFactory $titleFactory;

	/**
	 * @param Config $config
	 * @param CloudflareAPIRequester $cloudflareAPIRequester
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		Config $config,
		CloudflareAPIRequester $cloudflareAPIRequester,
		TitleFactory $titleFactory
	) {
		$this->config = $config;
		$this->cloudflareAPIRequester = $cloudflareAPIRequester;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * Check whether Cloudflare cache purging is enabled for pages.
	 *
	 * @return bool
	 */
	private function canPurge(): bool {
		return $this->config->get( 'CloudflarePurgePage' );
	}

	/**
	 * Purge a page's URL from Cloudflare cache.
	 *
	 * @param ProperPageIdentity $page
	 * @return void
	 */
	private function pagePurge( $page ) {
		if ( $this->canPurge() === true ) {
			$title = $this->titleFactory->newFromPageIdentity( $page );
			$url = $title->getFullURL();
			$this->cloudflareAPIRequester->cachePurge( [ $url ] );
		}
	}

	/**
	 * Purge modified page from Cloudflare cache after save.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param \WikiPage $wikiPage
	 * @param \MediaWiki\User\UserIdentity $user
	 * @param string $summary
	 * @param int $flags
	 * @param RevisionRecord $revisionRecord
	 * @param \MediaWiki\Storage\EditResult $editResult
	 * @return void
	 */
	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ): void {
		$this->pagePurge( $wikiPage );
	}

	/**
	 * Purge page from Cloudflare cache on "action=purge".
	 *
	 * @param \WikiPage $wikiPage
	 * @return void
	 */
	public function onArticlePurge( $wikiPage ) {
		$this->pagePurge( $wikiPage );
	}

	/**
	 * Purge deleted page's URL from Cloudflare cache.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param ProperPageIdentity $page
	 * @param Authority $deleter
	 * @param string $reason
	 * @param int $pageID
	 * @param RevisionRecord $deletedRev
	 * @param ManualLogEntry $logEntry
	 * @param int $archivedRevisionCount
	 * @return void
	 */
	public function onPageDeleteComplete(
		ProperPageIdentity $page,
		Authority $deleter,
		string $reason,
		int $pageID,
		RevisionRecord $deletedRev,
		ManualLogEntry $logEntry,
		int $archivedRevisionCount
	): void {
		$this->pagePurge( $page );
	}

	/**
	 * Purge old and new page URLs from Cloudflare cache after a page move.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param ProperPageIdentity $old
	 * @param ProperPageIdentity $new
	 * @param \MediaWiki\User\UserIdentity $user
	 * @param int $pageid
	 * @param int $redirid
	 * @param string $reason
	 * @param RevisionRecord $revision
	 * @return void
	 */
	public function onPageMoveComplete( $old, $new, $user, $pageid, $redirid, $reason, $revision ): void {
		if ( $this->canPurge() === true ) {
			$oldTitle = $this->titleFactory->newFromPageIdentity( $old );
			$newTitle = $this->titleFactory->newFromPageIdentity( $new );
			$oldUrl = $oldTitle->getFullURL();
			$newUrl = $newTitle->getFullURL();
			$this->cloudflareAPIRequester->cachePurge( [ $oldUrl, $newUrl ] );
		}
	}

	/**
	 * Purge local file and thumbnail URLs from Cloudflare cache.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param \File $file
	 * @param string|false $archiveName Name of an old file version or false if current
	 * @param string[] $urls Thumbnail URLs to purge
	 * @return void
	 */
	public function onLocalFilePurgeThumbnails( $file, $archiveName, $urls ): void {
		// サムネイルが生成されていない場合 $urls が空 GD,ImageMagicがインストールされていない場合など
		//上書きアップロードの場合は、古い画像毎に呼び出される
		if ( $this->config->get( 'CloudflarePurgeFile' ) ) {
				$purgeUrl = [];
				$originalUrl = $file->getUrl();
				$expandedOriginal = $this->expandURL( $originalUrl );
				if ( $expandedOriginal !== null ) {
					$purgeUrl[] = $expandedOriginal;
				}
				// オリジナル画像のURLを追加　アーカイブ画像の場合は削除する必要がないが判別方法がわからないので追加

				foreach ( $urls as $url ) {
					$expandedUrl = $this->expandURL( $url );
					if ( $expandedUrl !== null ) {
						$purgeUrl[] = $expandedUrl;
					}
				}
				if ( count( $purgeUrl ) > 0 ) {
					$this->cloudflareAPIRequester->cachePurge( $purgeUrl );
				}
		}
	}
}
