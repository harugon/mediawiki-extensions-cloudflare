# Cloudflare® - MediaWiki

[English](./README.en.md)

ページの更新、画像の再アップロード時に Cloudflare のキャッシュをパージします
(主に画像のキャッシュを消すことを目的としています)

[MediaWiki で CloudFlare を使う – harugon のブログ](https://blog.r9g.net/archives/121)

導入前に上記のページを読むことをおすすめします。

## Requirements

- PHP 8.0
- MediaWiki 1.43

## Install

[Releases · harugon/mediawiki\-extensions\-cloudflare](https://github.com/harugon/mediawiki-extensions-cloudflare/releases)

上記の URL より`Cloudflare-{バーション}.tar.gz`のファイルをダウンロードし extensions に展開

LocalSettings.php に
Cloudflare の API 情報とともに追記します。

```php
wfLoadExtension('Cloudflare');
$wgCloudflareAPIToken = '';
$wgCloudflareZoneID = '';
```

推奨される認証方法は `$wgCloudflareAPIToken` です。[API トークン - Cloudflare](https://dash.cloudflare.com/profile/api-tokens) で "キャッシュのパージ" 権限を持つトークンを作成してください。

非推奨の `$wgCloudflareEmail` と `$wgCloudflareAPIKey` も引き続き使用できますが、将来のリリースで削除されます。

```php
// 非推奨:
$wgCloudflareEmail = '';
$wgCloudflareAPIKey = '';
```

## Config

| 変数                   | 初期値 | 説明                                                                                                                 |
| ---------------------- | ------ | -------------------------------------------------------------------------------------------------------------------- |
| $wgCloudflareAPIToken    | ""     | API Token（ [API トークン - Cloudflare](https://dash.cloudflare.com/profile/api-tokens) で作成したキャッシュパージ権限付きトークン） |
| $wgCloudflareEmail      | ""     | **非推奨.** レガシー認証用のメールアドレス。代わりに $wgCloudflareAPIToken を使用してください。 |
| $wgCloudflareAPIKey     | ""     | **非推奨.** レガシー認証用のグローバルAPIキー。代わりに $wgCloudflareAPIToken を使用してください。 |
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
