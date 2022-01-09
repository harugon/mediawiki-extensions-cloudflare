# Cloudflare® - MediaWiki

[![CI](https://github.com/harugon/mediawiki-extensions-cloudflare/actions/workflows/ci.yml/badge.svg)](https://github.com/harugon/mediawiki-extensions-cloudflare/actions/workflows/ci.yml)

[English](./README.en.md)

MediaWiki の画像　更新時に Cloudflare のキャッシュをパージします

[MediaWiki で CloudFlare を使う – harugon のブログ](https://blog.r9g.net/archives/121)

導入前に上記のページを読むことをおすすめします。

## Requirements

- PHP 7.4
- MediaWiki 1.35

## Install

[Releases · harugon/mediawiki\-extensions\-cloudflare](https://github.com/harugon/mediawiki-extensions-cloudflare/releases)

上記の URL より`Cloudflare-{バーション}.tar.gz`のファイルをダウンロードし extensions に展開

LocalSettings.php に
Cloudflare の API 情報とともに追記します。

```php
wfLoadExtension('Cloudflare');
$wgCloudflareEmail = '';
$wgCloudflareAPIKey = '';
$wgCloudflareZoneID = '';
```

## Config

| 変数                   | 初期値 | 説明                                                                                                                 |
| ---------------------- | ------ | -------------------------------------------------------------------------------------------------------------------- |
| $wgCloudflareEmail     | ""     | Cloudflare に登録してあるメールアドレス                                                                              |
| $wgCloudflareAPIKey    | ""     | APIkey（ [API トークン- Cloudflare](https://dash.cloudflare.com/profile/api-tokens)　 → Global API Key が必要です ） |
| $wgCloudflareZoneID    | ""     | サイト（URL）固有の ID （サイトごとのダッシュボードで見ることができます）                                            |
| $wgCloudflarePurgePage | false  | 記事を更新時に purge する                                                                                            |
| $wgCloudflarePurgeFile | true   | ファイル（画像）を更新時に purge する                                                                                |

### 記事ページをキャッシュする

`$wgCloudflarePurgePage`を有効化する場合 ページルール (Page Rule)　にて記事ページ URL に
「キャッシュレベル (Cache Level)　-> すべてをキャッシュする( Cache Everything )」を指定する必要があります。

## 問題

- API Rate limits
- MobileFrontend 使用サイトでの記事ページの purge
- Varnish を挟んでいる場合　‥（Cloudflare->Varnish->origin 先に Cf が消える可能性がある？）
- $wgEventRelayerConfig['cdn-url-purges'] を使うと大げさ？
- guzzle を使っている

## Disclosure

Cloudflare, the Cloudflare logo, and Cloudflare Workers are trademarks and/or registered trademarks of Cloudflare, Inc. in the United States and other jurisdictions.
