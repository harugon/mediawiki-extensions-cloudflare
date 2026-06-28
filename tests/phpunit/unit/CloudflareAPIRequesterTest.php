<?php

namespace MediaWiki\Extension\Cloudflare\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\Cloudflare\CloudflareAPIRequester;
use MediaWiki\Http\HttpRequestFactory;
use MediaWikiUnitTestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

/**
 * @covers \MediaWiki\Extension\Cloudflare\CloudflareAPIRequester
 */
class CloudflareAPIRequesterTest extends MediaWikiUnitTestCase {

	/**
	 * Build a requester, filling in the config keys the class reads so that
	 * HashConfig never throws for a missing key.
	 *
	 * @param array $config Overrides for the Cloudflare config values.
	 * @param HttpRequestFactory|null $httpRequestFactory
	 * @param LoggerInterface|null $logger
	 * @return CloudflareAPIRequester
	 */
	private function newRequester(
		array $config = [],
		?HttpRequestFactory $httpRequestFactory = null,
		?LoggerInterface $logger = null
	): CloudflareAPIRequester {
		$config += [
			'CloudflareAPIToken' => false,
			'CloudflareEmail' => false,
			'CloudflareAPIKey' => false,
			'CloudflareZoneID' => false,
		];
		return new CloudflareAPIRequester(
			new HashConfig( $config ),
			$httpRequestFactory ?? $this->createMock( HttpRequestFactory::class ),
			$logger ?? new NullLogger()
		);
	}

	/**
	 * Build a real Guzzle client backed by a mock handler. The supplied
	 * $container array is populated with the requests that were sent so the
	 * test can assert on how the request was built.
	 *
	 * @param Response|RequestException $response The queued result.
	 * @param array &$container Filled with the request history.
	 * @return Client
	 */
	private function newGuzzleClient( $response, array &$container ): Client {
		$handlerStack = HandlerStack::create( new MockHandler( [ $response ] ) );
		$handlerStack->push( Middleware::history( $container ) );
		return new Client( [ 'handler' => $handlerStack ] );
	}

	public function testCachePurgeThrowsWhenZoneIdMissing(): void {
		$requester = $this->newRequester( [ 'CloudflareAPIToken' => 'token' ] );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Cloudflare configuration values are missing' );
		$requester->cachePurge( [ 'https://example.com/wiki/Foo' ] );
	}

	public function testCachePurgeThrowsWhenNoCredentials(): void {
		// Zone ID is present but neither the token nor the legacy email/key pair.
		$requester = $this->newRequester( [ 'CloudflareZoneID' => 'zone-1' ] );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Cloudflare configuration values are missing' );
		$requester->cachePurge( [ 'https://example.com/wiki/Foo' ] );
	}

	public function testCachePurgeSendsBearerTokenRequest(): void {
		$container = [];
		$client = $this->newGuzzleClient( new Response( 200, [], '{"success":true}' ), $container );

		$httpRequestFactory = $this->createMock( HttpRequestFactory::class );
		$httpRequestFactory->method( 'createGuzzleClient' )->willReturn( $client );

		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->once() )->method( 'info' );
		$logger->expects( $this->never() )->method( 'error' );

		$requester = $this->newRequester(
			[ 'CloudflareZoneID' => 'zone-1', 'CloudflareAPIToken' => 'secret-token' ],
			$httpRequestFactory,
			$logger
		);

		$urls = [ 'https://example.com/wiki/Foo', 'https://example.com/wiki/Bar' ];
		$requester->cachePurge( $urls );

		$this->assertCount( 1, $container, 'Exactly one request should be sent' );
		/** @var RequestInterface $request */
		$request = $container[0]['request'];

		$this->assertSame( 'POST', $request->getMethod() );
		$this->assertSame(
			'https://api.cloudflare.com/client/v4/zones/zone-1/purge_cache',
			(string)$request->getUri()
		);
		$this->assertSame( 'Bearer secret-token', $request->getHeaderLine( 'Authorization' ) );
		$this->assertSame( 'application/json', $request->getHeaderLine( 'Content-Type' ) );
		$this->assertSame( '', $request->getHeaderLine( 'X-Auth-Email' ) );

		$body = json_decode( (string)$request->getBody(), true );
		$this->assertSame( [ 'files' => $urls ], $body );
	}

	public function testCachePurgeSendsLegacyEmailAndKeyHeaders(): void {
		// The legacy auth path emits a deprecation warning; silence it for the assertion.
		$this->filterDeprecated( '/CloudflareEmail and CloudflareAPIKey are deprecated/' );

		$container = [];
		$client = $this->newGuzzleClient( new Response( 200 ), $container );

		$httpRequestFactory = $this->createMock( HttpRequestFactory::class );
		$httpRequestFactory->method( 'createGuzzleClient' )->willReturn( $client );

		$requester = $this->newRequester(
			[
				'CloudflareZoneID' => 'zone-1',
				'CloudflareEmail' => 'admin@example.com',
				'CloudflareAPIKey' => 'legacy-key',
			],
			$httpRequestFactory
		);

		$requester->cachePurge( [ 'https://example.com/wiki/Foo' ] );

		$this->assertCount( 1, $container );
		/** @var RequestInterface $request */
		$request = $container[0]['request'];
		$this->assertSame( 'admin@example.com', $request->getHeaderLine( 'X-Auth-Email' ) );
		$this->assertSame( 'legacy-key', $request->getHeaderLine( 'X-Auth-Key' ) );
		$this->assertSame( '', $request->getHeaderLine( 'Authorization' ) );
	}

	public function testCachePurgePrefersTokenOverLegacyCredentials(): void {
		$container = [];
		$client = $this->newGuzzleClient( new Response( 200 ), $container );

		$httpRequestFactory = $this->createMock( HttpRequestFactory::class );
		$httpRequestFactory->method( 'createGuzzleClient' )->willReturn( $client );

		$requester = $this->newRequester(
			[
				'CloudflareZoneID' => 'zone-1',
				'CloudflareAPIToken' => 'secret-token',
				'CloudflareEmail' => 'admin@example.com',
				'CloudflareAPIKey' => 'legacy-key',
			],
			$httpRequestFactory
		);

		$requester->cachePurge( [ 'https://example.com/wiki/Foo' ] );

		/** @var RequestInterface $request */
		$request = $container[0]['request'];
		$this->assertSame( 'Bearer secret-token', $request->getHeaderLine( 'Authorization' ) );
		$this->assertSame( '', $request->getHeaderLine( 'X-Auth-Email' ) );
	}

	public function testCachePurgeLogsErrorAndDoesNotThrowOnRequestException(): void {
		$endpoint = 'https://api.cloudflare.com/client/v4/zones/zone-1/purge_cache';
		$exception = new RequestException( 'Service Unavailable', new Request( 'POST', $endpoint ) );

		$container = [];
		$client = $this->newGuzzleClient( $exception, $container );

		$httpRequestFactory = $this->createMock( HttpRequestFactory::class );
		$httpRequestFactory->method( 'createGuzzleClient' )->willReturn( $client );

		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->once() )
			->method( 'error' )
			->with( $this->stringContains( 'Failed to purge cache' ) );
		$logger->expects( $this->never() )->method( 'info' );

		$requester = $this->newRequester(
			[ 'CloudflareZoneID' => 'zone-1', 'CloudflareAPIToken' => 'secret-token' ],
			$httpRequestFactory,
			$logger
		);

		// Must not bubble the Guzzle exception up to the caller.
		$requester->cachePurge( [ 'https://example.com/wiki/Foo' ] );
	}
}
