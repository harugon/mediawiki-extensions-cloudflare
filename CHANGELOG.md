# Changelog

All notable changes to this extension are documented in this file.

## 0.4.0

This release adds Cloudflare API Token authentication support and improves MediaWiki purge handling.

### Added

- Added `CloudflareAPIToken` configuration support.
- Added shared URL expansion logic via `UrlExpander`.

### Changed

- Cloudflare API Token authentication is now the recommended method.
- `CloudflareEmail` / `CloudflareAPIKey` remain supported as a legacy fallback for backward compatibility.
- Replaced deprecated `MWException` usage.
- Updated hook implementations and PHPDoc annotations.

Thanks to @hexmode for the contribution.

## 0.2.0 (2023-10-07)

- Migrated to utilizing EventRelayer for cache purging, moving away from the previous hook-based implementation.
- Removed the dependency on cloudflare/sdk. This change eliminates the need to run `composer install --no-dev` in the extension directory when installing from Git, easing the installation process.

## 0.1.2

- 設定が不正だったのを修正

## 0.1.1

パージするコンテンツを指定できるように

- 設定項目追加 `$wgCloudflarePurgePage`, `$wgCloudflarePurgeFile`
- i18n 追加 en.json, ja.json

## 0.1.0

初回リリース
