<?php
/**
 * Live Preview のデモデータ。
 *
 * シナリオ: 架空の旅行マガジン「Wander Japan」。カテゴリー「Kyoto」のアーカイブを Taxonomy Page（LP）に差し替え、
 * 導入文・おすすめエリア・Archive Block（3 件表示＋トグル）を見せる。比較用の「Osaka」は通常のアーカイブのまま。
 * 着地は /category/kyoto/。
 *
 * このファイルがマスター。編集したら次で blueprint.json に埋め込むこと:
 *   php <skill>/scripts/blueprint.php embed .
 */
require '/wordpress/wp-load.php';

// runPHP はログインユーザー無しで動く。権限チェックのある API（tax_input など）が黙って無視されるので、管理者として実行する。
wp_set_current_user( 1 );

update_option( 'blogname', 'Wander Japan' );
update_option( 'blogdescription', 'Slow travel guides to Japanese cities' );

// update_option() だけでは $wp_rewrite が古い構造のままで、最後の flush が投稿・カテゴリーのルールを書き出さない。
global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/%postname%/' );

// カテゴリーで Taxonomy Page を有効にする。init は空の設定で走り終わっているので、投稿タイプとルールを登録し直す。
update_option( 'rich_taxonomy_names', array( 'category' ) );
\Tarosky\RichTaxonomy\Controller\Editor::get_instance()->register_post_type();
\Tarosky\RichTaxonomy\Controller\TaxonomyArchiveRewrites::get_instance()->add_rewrite_rules();

$kyoto = wp_insert_term( 'Kyoto', 'category', array(
	'slug'        => 'kyoto',
	'description' => 'Temples, tea houses and quiet lanes in the old capital.',
) );
$osaka = wp_insert_term( 'Osaka', 'category', array(
	'slug'        => 'osaka',
	'description' => 'Street food, neon and the friendliest city in Japan.',
) );

/**
 * 記事を作る。日付は現在時刻からの相対（日数）。
 *
 * @param int    $cat_id Category ID.
 * @param string $title  Title.
 * @param string $body   Body text.
 * @param int    $days   Days ago.
 */
$create_article = function ( $cat_id, $title, $body, $days ) {
	wp_insert_post( array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => $title,
		'post_content'  => "<!-- wp:paragraph -->\n<p>" . $body . "</p>\n<!-- /wp:paragraph -->",
		'post_excerpt'  => $body,
		'post_date'     => wp_date( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS ),
		'post_category' => array( $cat_id ),
	) );
};

$kyoto_articles = array(
	array( 'An Early Morning at Fushimi Inari', 'Beat the crowds and walk the torii gates in the blue hour before sunrise.' ),
	array( 'Five Tea Houses Worth the Detour', 'From centuries-old counters to modern matcha bars tucked behind Gion.' ),
	array( 'Cycling the Kamo River', 'A flat, easy route from Demachiyanagi to Shichijo with plenty of places to stop.' ),
	array( 'Arashiyama Beyond the Bamboo Grove', 'Boat rides, monkey park views and a riverside lunch away from the main path.' ),
	array( 'A Rainy Day in Kyoto', 'Museums, covered markets and temples that look even better in the rain.' ),
	array( 'Where to Stay: Machiya Guesthouses', 'What to expect from a night in a restored wooden townhouse.' ),
	array( 'Eating Your Way Through Nishiki Market', 'Pickles, grilled skewers and soy milk doughnuts, stall by stall.' ),
);
foreach ( $kyoto_articles as $index => $article ) {
	$create_article( (int) $kyoto['term_id'], $article[0], $article[1], 3 + $index * 6 );
}

$osaka_articles = array(
	array( 'Takoyaki Crawl in Dotonbori', 'Six stalls, one evening, and how to tell the good ones from the tourist traps.' ),
	array( 'A Day Trip to the Osaka Castle Park', 'Plum groves, the moat walk and the best viewpoint for photos.' ),
	array( 'Shinsekai After Dark', 'Retro arcades, kushikatsu and the glow of Tsutenkaku tower.' ),
);
foreach ( $osaka_articles as $index => $article ) {
	$create_article( (int) $osaka['term_id'], $article[0], $article[1], 5 + $index * 9 );
}

// Kyoto の LP。プラグインの API で下書きを作り（ターム ID のメタも付く）、本文を入れて公開する。
$page_id = \Tarosky\RichTaxonomy\Controller\Setting::get_instance()->draft_for_term( get_term( (int) $kyoto['term_id'], 'category' ) );
$content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Kyoto rewards the slow traveler. This guide collects everything we have written about the city, from dawn at the shrines to late-night bowls of ramen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Where to Start</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Higashiyama</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Stone-paved slopes, Kiyomizu-dera and the lantern-lit lanes of Gion.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Arashiyama</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Bamboo groves, river boats and mountain views on the western edge of the city.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Central Kyoto</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Nishiki Market, the Imperial Palace and the best coffee shops in town.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Latest Kyoto Stories</h2>
<!-- /wp:heading -->

<!-- wp:rich-taxonomy/term-archive-block {"number":3,"toggle":"Show more stories"} /-->
HTML;
wp_update_post( array(
	'ID'           => $page_id,
	'post_title'   => 'Kyoto Travel Guide',
	'post_status'  => 'publish',
	'post_content' => $content,
) );

// 不要な既定コンテンツを片付ける。
wp_delete_post( 1, true ); // Hello world!
wp_delete_post( 2, true ); // Sample Page

// 新しいパーマリンク構造とベース URL 用のルールを反映する。
flush_rewrite_rules();
