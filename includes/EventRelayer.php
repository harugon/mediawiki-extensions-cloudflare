<?php
namespace MediaWiki\Extension\Cloudflare;


/*
 * ExtensionFunctions で　セットする必要ある
 * Manual:$wgEventRelayerConfig - MediaWiki
 * https://www.mediawiki.org/wiki/Manual:$wgEventRelayerConfig
 */
class EventRelayer extends \EventRelayer
{

    protected function doNotify($channel, array $events)
    {
        if($channel === 'cdn-url-purges'){
            /**@var $event array['url' => $url,'timestamp' => $ts] */
        }
    }
}