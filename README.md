# Cloudflare® - MediaWiki
MediaWikiの投稿、更新時にCloudflareのキャッシュをパージします

## Install

[Releases · harugon/mediawiki\-extensions\-cloudflare](https://github.com/harugon/mediawiki-extensions-cloudflare/releases)

上記のURLより``Cloudflare-{バーション}.tar.gz``のファイルをダウンロードしextensionsに展開

LocalSettings.phpに
CloudflareのAPI情報とともに追記します。
```php
wfLoadExtension('Cloudflare');
$wgCloudflareEmail = '';
$wgCloudflareAPIKey = '';
$wgCloudflareZoneID = '';
```
## Config

| 変数                |  例 |       説明                                  |
|---------------------|---|-----------------------------------------|
| $wgCloudflareEmail  |   |  Cloudflareに登録してあるメールアドレス |
| $wgCloudflareAPIKey |   |  APIkey（ [API トークン-  Cloudflare](https://dash.cloudflare.com/profile/api-tokens)　→ Global API Key が必要です ） |
| $wgCloudflareZoneID |   |  サイト（URL）固有のID （サイトごとのダッシュボードで見ることができます）                                      |



Cloudflare, the Cloudflare logo, and Cloudflare Workers are trademarks and/or registered trademarks of Cloudflare, Inc. in the United States and other jurisdictions.
