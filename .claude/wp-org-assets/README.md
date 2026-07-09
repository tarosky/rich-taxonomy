# WP.org アセットのソース

このディレクトリは WordPress.org 用アセット（バナー）の **編集可能なソース** を保管する。
配布物（`.wordpress-org/*.jpg`）はここから生成する。`.claude` は `.distignore` で
WP.org 配布物から除外されるため、ここにソースを置いても配布 zip には含まれない。

> このバナーは Tarosky プラグインの **リファレンス（理想形）**。他プラグインのバナーは
> この `banner.html` を出発点にし、下記のデザイン原則を踏襲すること。

## ファイル

| ファイル | 役割 |
|---|---|
| `banner.html` | バナーのマスターソース。ロゴ SVG はインライン埋め込み（外部依存なし） |

## デザイン原則（重要）

- 背景は白ベース + ブランドブルー `#00A9D9` の柔らかいラジアルグロー。
- **四辺はすべて純白 `#FFFFFF` にフェードさせる。** WordPress.org のプラグインページは
  白背景なので、端が純白でないとバナーが「箱」に見えて浮く。グローは端の手前で必ず
  透明（= 白）に収束させ、生成後にピクセルで検算する（下記）。
- メインのグローは価値を象徴する語（このプラグインでは "Rich"）の中心に置く。
- ロゴ・タイトル・タグラインのレイアウトは維持。TAROSKY 公式ワードマークは
  Drive の `horizontal_rgb.svg` を無改変で埋め込む（配色・書体を変えない）。

## 再現手順（macOS + Chrome DevTools MCP + ImageMagick）

```bash
# 1) banner.html を Chrome DevTools MCP で 1544x500 のビューポートで開き、
#    PNG スクリーンショットを撮る（SVG を正確に描くため ImageMagick では開かない）
#    -> banner-1544x500.png

# 2) 四辺が純白か検算（すべて min=65535 = 255 であること）
for g in North South West East; do
  dim=1544x1; case $g in West|East) dim=1x500;; esac
  echo "$g min=$(magick banner-1544x500.png -gravity $g -crop $dim+0+0 +repage -format '%[min]' info:)"
done

# 3) JPG 化と 772x250（正確な 50% 縮小）
magick banner-1544x500.png -background white -flatten -quality 92 banner-1544x500.jpg
magick banner-1544x500.jpg -resize 772x250 -quality 92 banner-772x250.jpg
identify banner-1544x500.jpg banner-772x250.jpg

# 4) .wordpress-org/ に配置
cp banner-1544x500.jpg banner-772x250.jpg ../../.wordpress-org/
```

グローの位置・サイズは `banner.html` の `.banner { background: ... }` のラジアル
グラデーション（`<半径x> <半径y> at <中心x> <中心y>`）で調整する。中心を端に寄せる
ときは、半径を「端までの距離」より小さくして端の手前で `rgba(...,0)` に収束させること。
