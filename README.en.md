# Cloudflare® - MediaWiki

This extension purges Cloudflare cache when updating pages or re-uploading images, with the primary aim of clearing image cache.

It's recommended to read the page [Using CloudFlare with MediaWiki – harugon's blog](https://blog.r9g.net/archives/121) before installation.

## Requirements
- PHP 7.4
- MediaWiki 1.35

## Install

Download the file `Cloudflare-{version}.tar.gz` from the URL [Releases · harugon/mediawiki-extensions-cloudflare](https://github.com/harugon/mediawiki-extensions-cloudflare/releases) and extract it to the extensions directory.

Add the following to LocalSettings.php along with your Cloudflare API information:

```php
wfLoadExtension('Cloudflare');
$wgCloudflareEmail = '';
$wgCloudflareAPIKey = '';
$wgCloudflareZoneID = '';
```

## Config

| Variable                 | Default value | Notes                                                                                                                   |
| ------------------------ | ------------- | ----------------------------------------------------------------------------------------------------------------------- |
| `$wgCloudflareEmail`     | `""`          | Your email used for logging in Cloudflare                                                                               |
| `$wgCloudflareAPIKey`    | `""`          | API key ([API token - Cloudflare](https://dash.cloudflare.com/profile/api-tokens) &rarr; Your global API key is needed) |
| `$wgCloudflareZoneID`    | `""`          | Site (URL) ID (You can get it from the dashboard of the site)                                                           |
| `$wgCloudflarePurgePage` | `false`       | Purge cache when articles are updated                                                                                   |
| `$wgCloudflarePurgeFile` | `true`        | Purge cache when files (images) are updated                                                                             |
|

### Caching Article Pages

If enabling `$wgCloudflarePurgePage`, it's necessary to set a Page Rule of Bypass Cache on Cookie. (Only available on Business and Enterprise plans.)

## Issues

- API Rate limits
- When Varnish is in place... (There's a possibility that Cloudflare may disappear first in the sequence Cloudflare->Varnish->origin?)

## Disclaimer

Cloudflare, the Cloudflare logo, and Cloudflare Workers are trademarks and/or registered trademarks of Cloudflare, Inc. in the United States and other jurisdictions.