<?php

namespace MediaWiki\Extension\Cloudflare;

use Config;
use GuzzleHttp\Exception\RequestException;
use MediaWiki\Http\HttpRequestFactory;
use Psr\Log\LoggerInterface;

class CloudflareAPIRequester {

	/**
	 * @var Config
	 */
	private $config;
	/**
	 * @var HttpRequestFactory
	 */
	private $httpRequestFactory;
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param Config $config
	 * @param HttpRequestFactory $httpRequestFactory
	 * @param LoggerInterface $logger
	 */
	public function __construct( $config, $httpRequestFactory, $logger ) {
		$this->config = $config;
		$this->httpRequestFactory = $httpRequestFactory;
		$this->logger = $logger;
	}

	/**
	 * 指定されたURLのキャッシュを削除する
	 * NB: When including the Origin header, be sure to include the scheme and hostname.  The port number can be omitted
	 * if it is the default port (80 for http, 443 for https), but must be included otherwise.
	 *
	 * @param array $urls 削除するURLの配列 max 30
	 * @return void
	 * @throws Exception If required configuration values are missing
	 */
	public function cachePurge( $urls ): void {
		$apiToken = $this->config->get( 'CloudflareAPIToken' );
		$zoneId = $this->config->get( 'CloudflareZoneID' );

		// Check if the necessary configuration values are set
		if ( !$apiToken || !$zoneId ) {
			throw new Exception( 'Cloudflare configuration values are missing' );
		}

		/**
		 * Cloudflare API Documentation
		 * https://developers.cloudflare.com/api/operations/zone-purge#purge-cached-content-by-url
		 */
		$endpoint = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache";
		$headers = [
			'Authorization' => 'Bearer ' . $apiToken,
			'Content-Type' => 'application/json',
		];
		$body = [
			'files' => $urls,
		];

		$guzzleClient = $this->httpRequestFactory->createGuzzleClient();

		try {
			$response = $guzzleClient->post( $endpoint, [
				'headers' => $headers,
				'json' => $body,
			] );
			$this->logger->info(
				'Purge cache succeeded with status: ' . $response->getStatusCode() . ' and Urls: '
					. implode( ', ', $urls )
			);
		} catch ( RequestException $e ) {
			$this->logger->error( 'Failed to purge cache: ' . $e->getMessage() );
		}
	}
}
