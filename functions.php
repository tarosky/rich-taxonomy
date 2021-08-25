<?php
/**
 * Rich Taxonomy Functions.
 *
 * @package rich-taxonomy
 */

/**
 * Get taxonomy page object in
 *
 * @retun WP_Post|null
 */
function rich_taxonomy_current_post() {
	global $wp_query;
	return \Tarosky\RichTaxonomy\Controller\Rewrites::get_instance()->get_taxonomy_page_from_query( $wp_query, false, false );
}

/**
 * Alternative of get_template_part.
 *
 * @see \Tarosky\RichTaxonomy\Controller\Templates::load_template
 */
function rich_taxonomy_template( $template, $suffix = '', $args = [] ) {
	\Tarosky\RichTaxonomy\Controller\Templates::get_instance()->load_template( $template, $suffix, $args );
}
