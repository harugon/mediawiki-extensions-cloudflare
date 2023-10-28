<?php

namespace MediaWiki\Extension\Cloudflare;

/**
* "ExtensionFunctions": [
 * "MediaWiki\\Extension\\Cloudflare\\Cloudflare::onExtensionFunction"
 * ],
 */
class Cloudflare {
	public static function onExtensionFunction() {
		global $wgEventRelayerConfig;
		   $wgEventRelayerConfig['cdn-url-purges'] = [
			   'class' => \MediaWiki\Extension\Cloudflare\EventRelayer::class,
				'args' => [],
			];
	}

}
