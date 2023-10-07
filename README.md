# Cloudflare® - MediaWiki

[English](./README.en.md)

ページの更新、画像の再アップロード時に Cloudflare のキャッシュをパージします
(主に画像のキャッシュを消すことを目的としています)

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

`$wgCloudflarePurgePage`を有効化する場合 ページルール (Page Rule) に　Bypass Cache on Cookie　を設定する必要があります。
(BusinessプランとEnterpriseプランのみ有効です。)

## 問題

- API Rate limits
- Varnish を挟んでいる場合　‥（Cloudflare->Varnish->origin 先に Cloudflare が消える可能性がある？）


## Disclaimer

Cloudflare, the Cloudflare logo, and Cloudflare Workers are trademarks and/or registered trademarks of Cloudflare, Inc. in the United States and other jurisdictions.
