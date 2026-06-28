<?php
namespace MediaWiki\Extension\Cloudflare;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use RuntimeException;

class EventRelayer extends \EventRelayer {
	use UrlExpander;

	/**
	 * @var CloudflareAPIRequester
	 */
	private $cloudflareAPIRequester;

	/** @var Config */
	private $config;

	/**
	 * @param array $params
	 */
	public function __construct( array $params ) {
		parent::__construct( $params );
		$this->cloudflareAPIRequester = MediaWikiServices::getInstance()->getService( 'CloudflareAPIRequester' );
		$this->config = MediaWikiServices::getInstance()->getMainConfig();
	}

	/**
	 * Relay CDN URL purge events to Cloudflare API.
	 *
	 * @param string $channel
	 * @param array $events List of event data maps
	 * @return bool Success
	 * @throws RuntimeException If Cloudflare configuration values are missing
	 */
	protected function doNotify( $channel, array $events ): bool {
		if ( $channel === 'cdn-url-purges' ) {
			$uploadPath = $this->config->get( 'UploadPath' );

			$files = [];
			$articles = [];
			foreach ( $events as $event ) {
				/** @var array{url: string|null, timestamp: int} $event */
				$url = $this->expandURL( $event['url'] ?? null );
				if ( $url === null ) {
					continue;
				}
				$isFileUrl = str_contains( $url, $uploadPath );

				if ( $isFileUrl ) {
					$files[] = $url;
				} else {
					$articles[] = $url;
				}
			}

			if ( $this->config->get( 'CloudflarePurgePage' ) && $this->config->get( 'CloudflarePurgeFile' ) ) {
				$allUrls = array_merge( $articles, $files );
				$this->cloudflareAPIRequester->cachePurge( $allUrls );
			} elseif ( $this->config->get( 'CloudflarePurgePage' ) ) {
				if ( count( $articles ) > 0 ) {
					$this->cloudflareAPIRequester->cachePurge( $articles );
				}
			} elseif ( $this->config->get( 'CloudflarePurgeFile' ) ) {
				if ( count( $files ) > 0 ) {
					$this->cloudflareAPIRequester->cachePurge( $files );
				}
			}

		}
		return true;
	}
}
