# Cloudflare® - MediaWiki

[![CI](https://github.com/harugon/mediawiki-extensions-cloudflare/actions/workflows/ci.yml/badge.svg)](https://github.com/harugon/mediawiki-extensions-cloudflare/actions/workflows/ci.yml)

[日本語](./README.md)

Purge Cloudflare cache when updating MediaWiki images.

[Use MediaWiki with Cloudflare – harugon's blog (Japanese)](https://blog.r9g.net/archives/121)

It's recommended to read the blog above before installing this extension.

## Requirements

- PHP 7.4
- MediaWiki 1.35

## Installation

[Releases · harugon/mediawiki\-extensions\-cloudflare](https://github.com/harugon/mediawiki-extensions-cloudflare/releases)

Download `Cloudflare-{version}.tar.gz` from the URL above and extract it to `extensions` directory.

Add the following codes and Cloudflare's API information at the bottom of your `LocalSettings.php`:

```php
wfLoadExtension('Cloudflare');
$wgCloudflareEmail = '';
$wgCloudflareAPIKey = '';
$wgCloudflareZoneID = '';
```

## Configuration

| Variable                 | Default value | Notes                                                                                                                   |
| ------------------------ | ------------- | ----------------------------------------------------------------------------------------------------------------------- |
| `$wgCloudflareEmail`     | `""`          | Your email used for logging in Cloudflare                                                                               |
| `$wgCloudflareAPIKey`    | `""`          | API key ([API token - Cloudflare](https://dash.cloudflare.com/profile/api-tokens) &rarr; Your global API key is needed) |
| `$wgCloudflareZoneID`    | `""`          | Site (URL) ID (You can get it from the dashboard of the site)                                                           |
| `$wgCloudflarePurgePage` | `false`       | Purge cache when articles are updated                                                                                   |
| `$wgCloudflarePurgeFile` | `true`        | Purge cache when files (images) are updated                                                                             |

### Caching article pages

When `$wgCloudflarePurgePage` is enabled, you need to set the the "Cache Level" of article page URLs in "Page Rule" to "Cache Everything".

## What's next

- API Rate limits
- Purge article pages when using MobileFrontend
- When Varnish is used as middleware (Cloudflare &rarr; Varnish &rarr; origin; is there any chance that Cloudflare will be left out?)
- Is it suitable to use `$wgEventRelayerConfig['cdn-url-purges']`?
- Work with guzzle

## Disclaimer

Cloudflare, the Cloudflare logo, and Cloudflare Workers are trademarks and/or registered trademarks of Cloudflare, Inc. in the United States and other jurisdictions.
