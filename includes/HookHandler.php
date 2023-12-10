<?php

namespace MediaWiki\Extension\Cloudflare;

use Config;
use ManualLogEntry;
use MediaWiki\Hook\LocalFilePurgeThumbnailsHook;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;

/**
 * Class HookHandler
 *
 * https://developers.cloudflare.com/cache/concepts/default-cache-behavior/
 * historyページはキャッシュされないmax-age=0のため
 *
 */
class HookHandler implements
	PageSaveCompleteHook,
	PageDeleteCompleteHook,
    PageMoveCompleteHook,
	LocalFilePurgeThumbnailsHook
{

	private CloudflareAPIRequester $cloudflareAPIRequester;
	private Config $config;

	/**
	 * @param Config $config
	 * @param CloudflareAPIRequester $cloudflareAPIRequester
	 */
	public function __construct( Config $config, CloudflareAPIRequester $cloudflareAPIRequester ) {
		$this->config = $config;
		$this->cloudflareAPIRequester = $cloudflareAPIRequester;
	}

	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ): void
    {
		if ( $this->config->get( 'CloudflarePurgePage' ) ) {
			$url = $wikiPage->getTitle()->getFullURL();
			$this->cloudflareAPIRequester->cachePurge( [ $url ] );
		}
	}


	public function onPageDeleteComplete( ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID, RevisionRecord $deletedRev, ManualLogEntry $logEntry, int $archivedRevisionCount ): void
    {
		if ( $this->config->get( 'CloudflarePurgePage' ) ) {
			$url = $page->getTitle()->getFullURL();
			$this->cloudflareAPIRequester->cachePurge( [ $url ] );
		}
	}

	public function onPageMoveComplete( $old, $new, $user, $pageid, $redirid, $reason, $revision ): void
    {
		if ( $this->config->get( 'CloudflarePurgePage' ) ) {
			$oldUrl = $old->getTitle()->getFullURL();
			$newUrl = $new->getTitle()->getFullURL();
			$this->cloudflareAPIRequester->cachePurge( [ $oldUrl, $newUrl ] );
		}
	}

	/**
	 *
	 */
	public function onLocalFilePurgeThumbnails( $file, $archiveName, $urls ): void
    {
		//サムネイルが生成されていない場合 $urls が空 GD,ImageMagicがインストールされていない場合など
		//上書きアップロードの場合は、古い画像毎に呼び出される
		if ( $this->config->get( 'CloudflarePurgeFile' ) ) {
				$purgeURL = [];
				$originalUrl = $file->getUrl();
				$purgeURL[] = $this->expandURL( $originalUrl );
				//オリジナル画像のURLを追加　アーカイブ画像の場合は削除する必要がないが判別方法がわからないので追加

				foreach ( $urls as $url ) {
					$purgeURL[] = $this->expandURL( $url );
				}
				$this->cloudflareAPIRequester->cachePurge( $purgeURL );
		}
	}

	/**
	 * Expand a potentially local URL to a fully-qualified URL.
	 * @param $url
	 * @return string
	 */
	private function expandURL( $url ): string {
		return (string)MediaWikiServices::getInstance()->getUrlUtils()->expand( $url, PROTO_INTERNAL );
	}

}
