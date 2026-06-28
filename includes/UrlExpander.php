<?php

namespace MediaWiki\Extension\Cloudflare;

use MediaWiki\MediaWikiServices;

/**
 * Trait providing URL expansion from local to fully-qualified.
 */
trait UrlExpander {

	/**
	 * Expand a potentially local URL to a fully-qualified URL.
	 * @param string|null $url
	 * @return string|null
	 */
	private function expandURL( ?string $url ): ?string {
		if ( $url === null ) {
			return null;
		}
		return MediaWikiServices::getInstance()->getUrlUtils()->expand( $url, PROTO_INTERNAL );
	}
}
