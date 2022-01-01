# Cloudflare® - MediaWiki
[![CI](https://github.com/harugon/mediawiki-extensions-cloudflare/actions/workflows/ci.yml/badge.svg)](https://github.com/harugon/mediawiki-extensions-cloudflare/actions/workflows/ci.yml)

MediaWikiの画像　更新時にCloudflareのキャッシュをパージします

[MediaWikiでCloudFlareを使う – harugonのブログ](https://blog.r9g.net/archives/121)

導入前に上記のページを読むことをおすすめします。

## Requirements
* PHP 7.4
* MediaWiki 1.35
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

| 変数                |  初期値 |       説明                                  |
|---------------------|---|-----------------------------------------|
| $wgCloudflareEmail  |""|  Cloudflareに登録してあるメールアドレス |
| $wgCloudflareAPIKey |""|  APIkey（ [API トークン-  Cloudflare](https://dash.cloudflare.com/profile/api-tokens)　→ Global API Key が必要です ） |
| $wgCloudflareZoneID |""|  サイト（URL）固有のID （サイトごとのダッシュボードで見ることができます）                                      |
| $wgCloudflarePurgePage | false|  記事を更新時にpurgeする                                       |
| $wgCloudflarePurgeFile |  true | ファイル（画像）を更新時にpurgeする                                      |


### 記事ページをキャッシュする
``$wgCloudflarePurgePage``を有効化する場合 ページルール (Page Rule)　にて記事ページURLに
「キャッシュレベル (Cache Level)　-> すべてをキャッシュする( Cache Everything )」を指定する必要があります。

## 問題
* API Rate limits
* MobileFrontend使用サイトでの記事ページのpurgeはできません
* MobileFrontendをurl分離してCloudflareのMobile redirectで分岐している場合は使用できますが拡張の読み込み位置に注意する必要があります？？
* Varnishを挟んでいる場合、更新前のコンテンツが読み込まれる可能性あります（Cloudflare->Varnish->origin 先にCfが消える可能性がある？）

## Disclosure

Cloudflare, the Cloudflare logo, and Cloudflare Workers are trademarks and/or registered trademarks of Cloudflare, Inc. in the United States and other jurisdictions.
