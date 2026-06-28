<?php

namespace MediaWiki\Extension\Cloudflare\Tests\Unit;

use File;
use ManualLogEntry;
use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\Cloudflare\CloudflareAPIRequester;
use MediaWiki\Extension\Cloudflare\HookHandler;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWikiUnitTestCase;

/**
 * @covers \MediaWiki\Extension\Cloudflare\HookHandler
 */
class HookHandlerTest extends MediaWikiUnitTestCase {

	/**
	 * @param array $config Config overrides.
	 * @param CloudflareAPIRequester $requester
	 * @param TitleFactory|null $titleFactory
	 * @return HookHandler
	 */
	private function newHandler(
		array $config,
		CloudflareAPIRequester $requester,
		?TitleFactory $titleFactory = null
	): HookHandler {
		$config += [
			'CloudflarePurgePage' => false,
			'CloudflarePurgeFile' => false,
		];
		return new HookHandler(
			new HashConfig( $config ),
			$requester,
			$titleFactory ?? $this->createMock( TitleFactory::class )
		);
	}

	/**
	 * A TitleFactory that maps the given page identity to a title returning $url.
	 *
	 * @param ProperPageIdentity $page
	 * @param string $url
	 * @return TitleFactory
	 */
	private function titleFactoryFor( ProperPageIdentity $page, string $url ): TitleFactory {
		$title = $this->createMock( Title::class );
		$title->method( 'getFullURL' )->willReturn( $url );

		$titleFactory = $this->createMock( TitleFactory::class );
		$titleFactory->method( 'newFromPageIdentity' )->with( $page )->willReturn( $title );
		return $titleFactory;
	}

	public function testOnPageSaveCompletePurgesPageWhenEnabled(): void {
		$page = $this->createMock( ProperPageIdentity::class );

		$requester = $this->createMock( CloudflareAPIRequester::class );
		$requester->expects( $this->once() )
			->method( 'cachePurge' )
			->with( [ 'https://example.com/wiki/Foo' ] );

		$handler = $this->newHandler(
			[ 'CloudflarePurgePage' => true ],
			$requester,
			$this->titleFactoryFor( $page, 'https://example.com/wiki/Foo' )
		);

		$handler->onPageSaveComplete( $page, null, '', 0, null, null );
	}

	public function testOnPageSaveCompleteDoesNothingWhenDisabled(): void {
		$requester = $this->createMock( CloudflareAPIRequester::class );
		$requester->expects( $this->never() )->method( 'cachePurge' );

		$handler = $this->newHandler( [ 'CloudflarePurgePage' => false ], $requester );
		$handler->onPageSaveComplete(
			$this->createMock( ProperPageIdentity::class ), null, '', 0, null, null
		);
	}

	public function testOnArticlePurgePurgesPageWhenEnabled(): void {
		$page = $this->createMock( ProperPageIdentity::class );

		$requester = $this->createMock( CloudflareAPIRequester::class );
		$requester->expects( $this->once() )
			->method( 'cachePurge' )
			->with( [ 'https://example.com/wiki/Foo' ] );

		$handler = $this->newHandler(
			[ 'CloudflarePurgePage' => true ],
			$requester,
			$this->titleFactoryFor( $page, 'https://example.com/wiki/Foo' )
		);

		$handler->onArticlePurge( $page );
	}

	public function testOnPageDeleteCompletePurgesPageWhenEnabled(): void {
		$page = $this->createMock( ProperPageIdentity::class );

		$requester = $this->createMock( CloudflareAPIRequester::class );
		$requester->expects( $this->once() )
			->method( 'cachePurge' )
			->with( [ 'https://example.com/wiki/Foo' ] );

		$handler = $this->newHandler(
			[ 'CloudflarePurgePage' => true ],
			$requester,
			$this->titleFactoryFor( $page, 'https://example.com/wiki/Foo' )
		);

		$handler->onPageDeleteComplete(
			$page,
			$this->createMock( Authority::class ),
			'reason',
			1,
			$this->createMock( RevisionRecord::class ),
			$this->createMock( ManualLogEntry::class ),
			0
		);
	}

	public function testOnPageMoveCompletePurgesOldAndNewUrls(): void {
		$old = $this->createMock( ProperPageIdentity::class );
		$new = $this->createMock( ProperPageIdentity::class );

		$oldTitle = $this->createMock( Title::class );
		$oldTitle->method( 'getFullURL' )->willReturn( 'https://example.com/wiki/Old' );
		$newTitle = $this->createMock( Title::class );
		$newTitle->method( 'getFullURL' )->willReturn( 'https://example.com/wiki/New' );

		$titleFactory = $this->createMock( TitleFactory::class );
		$titleFactory->method( 'newFromPageIdentity' )->willReturnMap( [
			[ $old, $oldTitle ],
			[ $new, $newTitle ],
		] );

		$requester = $this->createMock( CloudflareAPIRequester::class );
		$requester->expects( $this->once() )
			->method( 'cachePurge' )
			->with( [ 'https://example.com/wiki/Old', 'https://example.com/wiki/New' ] );

		$handler = $this->newHandler( [ 'CloudflarePurgePage' => true ], $requester, $titleFactory );
		$handler->onPageMoveComplete( $old, $new, null, 0, 0, '', null );
	}

	public function testOnPageMoveCompleteDoesNothingWhenDisabled(): void {
		$requester = $this->createMock( CloudflareAPIRequester::class );
		$requester->expects( $this->never() )->method( 'cachePurge' );

		$handler = $this->newHandler( [ 'CloudflarePurgePage' => false ], $requester );
		$handler->onPageMoveComplete(
			$this->createMock( ProperPageIdentity::class ),
			$this->createMock( ProperPageIdentity::class ),
			null, 0, 0, '', null
		);
	}

	public function testOnLocalFilePurgeThumbnailsDoesNothingWhenFilePurgeDisabled(): void {
		$requester = $this->createMock( CloudflareAPIRequester::class );
		$requester->expects( $this->never() )->method( 'cachePurge' );

		$handler = $this->newHandler( [ 'CloudflarePurgeFile' => false ], $requester );

		// File purge disabled => the handler returns before touching the file or services.
		$handler->onLocalFilePurgeThumbnails(
			$this->createMock( File::class ),
			false,
			[ 'https://example.com/images/thumb/Foo.png' ]
		);
	}
}
