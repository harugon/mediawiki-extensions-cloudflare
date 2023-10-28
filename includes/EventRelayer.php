<?php
namespace MediaWiki\Extension\Cloudflare;

use MediaWiki\MediaWikiServices;

class EventRelayer extends \EventRelayer {

	/**
	 * @var CloudflareAPIRequester
	 */
	private $cloudflareAPIRequester;
	private $config;

	public function __construct( array $params ) {
		parent::__construct( $params );
		$this->cloudflareAPIRequester = MediaWikiServices::getInstance()->getService( 'CloudflareAPIRequester' );
		$this->config = MediaWikiServices::getInstance()->getMainConfig();
	}

	/**
	 * @param string $channel
	 * @param array $events List of event data maps
	 * @return bool Success
	 * @throws \MWException
	 */
	protected function doNotify( $channel, array $events ): bool {
		if ( $channel === 'cdn-url-purges' ) {
			$uploadPath = $this->config->get( 'UploadPath' );

			$files = [];
			$articles = [];
			foreach ( $events as $event ) {
				/** @var $event array['urls' => $url,'timestamp' => $ts] */
				$url = $this->expandURL( $event['url'] );
				$isFileURL = strpos( $url, $uploadPath ) !== false;
                //@todo: PHP8 str_contains($url, $uploadPath);

				if ( $isFileURL ) {
					$files[] = $url;
				} else {
					$articles[] = $url;
				}
			}

			if ( $this->config->get( 'CloudflarePurgePage' ) && $this->config->get( 'CloudflarePurgeFile' ) ) {
				$allUrls = array_merge( $articles, $files );
				$this->cloudflareAPIRequester->cachePurge( $allUrls );
			} elseif ( $this->config->get( 'CloudflarePurgePage' ) ) {
                if ( count( $articles ) > 0){
                    $this->cloudflareAPIRequester->cachePurge( $articles );
                }
			} elseif ( $this->config->get( 'CloudflarePurgeFile' ) ) {
                if ( count( $files ) > 0){
                    $this->cloudflareAPIRequester->cachePurge( $files );
                }
			}

		}
		return true;
	}

	private function expandURL( $url ): string {
		return (string)MediaWikiServices::getInstance()->getUrlUtils()->expand( $url, PROTO_INTERNAL );
	}
}
