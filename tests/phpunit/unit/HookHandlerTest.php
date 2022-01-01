<?php
namespace MediaWiki\Extension\Clouflare\Tests;

use Config;
use HashConfig;
use MediaWiki\Extension\Cloudflare\HookHandler;
use MediaWikiUnitTestCase;
use MultiConfig;

class HookHandlerTest extends MediaWikiUnitTestCase {
	/** @var Config */
	protected $config;
	/** @var string */
	protected $server = 'サーバ';

	protected function setUp(): void {
		parent::setUp();
		$this->config = new MultiConfig( [
			new HashConfig( [ 'Server' => $this->server ] ),
			new HashConfig( [ 'CloudflareEmail' => '' ] ),
			new HashConfig( [ 'CloudflareAPIKey' => '' ] ),
			new HashConfig( [ 'CloudflareZoneID' => '' ] ),
			new HashConfig( [ 'CloudflarePurgeFile' => true ] ),
			new HashConfig( [ 'CloudflarePurgePage' => true ] ),
		] );
	}

	/**
	 * @dataProvider urlDataProvider
	 */
	public function testonTitleSquidURLs( array $urls ) {
		$Title = $this->getMockBuilder( \Title::class )
			->disableOriginalConstructor()
			->getMock();
		$HookHandler = $this->getMockBuilder( HookHandler::class )
			->onlyMethods( [ 'purge' ] )
			->setConstructorArgs( [ $this->config ] )
			->getMock();
		$HookHandler->expects( $this->once() )
			->method( 'purge' )
			->with( $urls );
		$HookHandler->onTitleSquidURLs( $Title, $urls );
	}

	/**
	 * @dataProvider FileUrlDataProvider
	 */
	public function testonLocalFilePurgeThumbnails( array $urls, array $expected ) {
		$File = $this->getMockBuilder( \File::class )
			->disableOriginalConstructor()
			->getMock();
		$HookHandler = $this->getMockBuilder( HookHandler::class )
			->onlyMethods( [ 'purge' ] )
			->setConstructorArgs( [ $this->config ] )
			->getMock();
		$HookHandler->expects( $this->once() )
			->method( 'purge' )
			->with( $expected );
		$HookHandler->onLocalFilePurgeThumbnails( $File, '', $urls );
	}

	public function urlDataProvider(): array {
		return [
			[ [ 'aaa','eee' ] ],
			[ [ 'bbb','eee' ] ],
			[ [ 'ccc','eee' ] ],
			[ [ 'ddd','eee' ] ],
		];
	}

	public function fileUrlDataProvider(): array {
		return [
			[ [ 'aaa','eee' ],[ $this->server . 'aaa',$this->server . 'eee' ] ],
			[ [ 'bbb','eee' ],[ $this->server . 'bbb',$this->server . 'eee' ] ],
			[ [ 'ccc','eee' ],[ $this->server . 'ccc',$this->server . 'eee' ] ],
			[ [ 'ddd','eee' ],[ $this->server . 'ddd',$this->server . 'eee' ] ]
		];
	}

}
