# Rich Taxonomy - プロジェクトガイド

WordPressのタクソノミーアーカイブにリッチなコンテンツページを割り当てるプラグイン。

## アーキテクチャ

### パターン

- **Singleton**: 全コントローラー・APIは `Pattern\Singleton` を継承。`init()` でフック登録
- **Trait合成**: `PageAccessor`, `SettingAccessor`, `TemplateAccessor`, `DirectoryAccessor` で横断的関心事を共有
- **RestApiPattern**: REST APIは `Pattern\RestApiPattern` を継承。`route()`, `get_rest_setting()`, `callback()` を実装

### カスタム投稿タイプ `taxonomy-page`

- タームアーカイブの代わりに表示されるリッチコンテンツページ
- `_rich_taxonomy_term_id` メタでタームと紐付け
- ターム編集画面からのみ作成可能（`create_posts` capability を無効化）
- ブロックテーマ・クラシックテーマ両対応

### ディレクトリ構成

```
src/Tarosky/RichTaxonomy/
├── Api/          # REST API エンドポイント
├── Blocks/       # Gutenbergブロック
├── Controller/   # フック登録・ビジネスロジック
├── Pattern/      # Singleton, RestApiPattern 基底クラス
└── Utility/      # Trait（PageAccessor等）
```

## 開発環境

```bash
# 事前インストール
npm install
composer install

# Docker
npm start             # Dockerを起動
npm stop              # 停止
npm run cli           # Dockerコンテナ内でwp-cliを実行 e.g. npm run cli plugin list

# PHP
composer run lint     # PHPCS
composer run phpstan  # PHPStan
npm test              # Docker内でPHPUnit実行

# JS/CSS
npm run package       # ビルド
npm run lint          # ESLint + Stylelint
```

## CI（GitHub Actions）

PR作成時に以下が自動実行される。すべてパスしないとマージ不可：

- PHPUnit（PHP × WP のバージョンマトリックスは `.github/workflows/test.yml` を参照）
- PHPCS（WordPress-Core）
- PHPStan Level 5
- アセットビルド確認

> **バージョンの情報源（Single Source of Truth）** — 本ドキュメントには具体的なバージョン番号を記載しない（乖離防止のため）。
> - **対応 PHP**: `composer.json`（`require.php` / `config.platform.php`）が正。`rich-taxonomy.php` の `Requires PHP` はこれに追従する
> - **Node**: `.node-version`
> - **CI テストマトリックス（PHP × WP）**: `.github/workflows/test.yml`

## コーディング規約

- WordPress Coding Standards 準拠
- PSR-0 オートロード（`Tarosky\RichTaxonomy` → `src/`）
- 対応 PHP バージョンは `composer.json`（`require.php` / `config.platform.php`）と CI マトリックス（`.github/workflows/test.yml`）に従う
- 新規コードは PHPStan Level 5 をパスすること（既存のbaselineエラーを増やさない）

## 注意事項

- リライトルール変更時は `flush_rewrite_rules()` が必要（プラグイン有効化時に自動実行）
- フック優先度はデフォルト10を使用。テーマとの競合回避が必要な場合のみ変更
- `is_block_theme()` で分岐する箇所があるため、テンプレート関連の変更はブロックテーマでも検証すること
