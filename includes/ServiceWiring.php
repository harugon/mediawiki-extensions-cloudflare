<?php

use MediaWiki\Extension\Cloudflare\CloudflareAPIRequester;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

return [
	'CloudflareAPIRequester' => static function ( MediaWikiServices $services ) {
		// Get the necessary services from the service container
		$config = $services->getMainConfig();
		$httpRequestFactory = $services->getHttpRequestFactory();
		$logger = LoggerFactory::getInstance( 'cloudflare' );
		// Create and return a new instance of CloudflareAPIRequester with the necessary dependencies
		return new CloudflareAPIRequester( $config, $httpRequestFactory, $logger );
	},
];
