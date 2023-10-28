<?php

namespace MediaWiki\Extension\Cloudflare;

use Config;
use ManualLogEntry;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\Hook\UploadCompleteHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;

class HookHandler implements PageSaveCompleteHook,PageDeleteCompleteHook,PageMoveCompleteHook,UploadCompleteHook
{


    private CloudflareAPIRequester $cloudflareAPIRequester;
    private Config $config;

    public function __construct(Config $config, CloudflareAPIRequester $cloudflareAPIRequester){
        $this->config = $config;
        $this->cloudflareAPIRequester = $cloudflareAPIRequester;
    }


    public function onPageSaveComplete($wikiPage, $user, $summary, $flags, $revisionRecord, $editResult)
    {
        $url = $wikiPage->getTitle()->getFullURL();
        if ( $this->config->get( 'CloudflarePurgePage' ) ) {
                $this->cloudflareAPIRequester->cachePurge( [$url] );
        }
    }

    public function onUploadComplete($uploadBase)
    {
        $file = $uploadBase->getLocalFile()->getUrl();
        $fileUrl = $this->expandURL( $file );

        //TODO: LocalFile::getThumbnails
        //遅延させないと消えないかも
        if ( $this->config->get( 'CloudflarePurgeFile' ) ) {
                $this->cloudflareAPIRequester->cachePurge( [$fileUrl] );
        }
    }

    public function onPageDeleteComplete(ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID, RevisionRecord $deletedRev, ManualLogEntry $logEntry, int $archivedRevisionCount)
    {

    }

    /**
     *
     * https://www.mediawiki.org/wiki/Manual:Hooks/PageMoveComplete
     *
     * @param $old
     * @param $new
     * @param $user
     * @param $pageid
     * @param $redirid
     * @param $reason
     * @param $revision
     * @return void
     */
    public function onPageMoveComplete($old, $new, $user, $pageid, $redirid, $reason, $revision)
    {

    }
    private function expandURL( $url ): string {
        return (string)MediaWikiServices::getInstance()->getUrlUtils()->expand( $url, PROTO_INTERNAL );
    }
}