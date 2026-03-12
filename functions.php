<?php
defined( 'ABSPATH' ) || exit;
/**
 * Rich Taxonomy Functions.
 *
 * @package rich-taxonomy
 */

/**
 * Get taxonomy page object (term archive or taxonomy archive).
 *
 * @param bool $only_first    If false, include each paged archive.
 * @param bool $include_admin If true, get taxonomy page even if in admin.
 * @return \WP_Post|null
 */
function rich_taxonomy_current_post( $only_first = true, $include_admin = false ) {
	global $wp_query;
	// Taxonomy archive (base URL) - main query returns our page directly.
	if ( $wp_query->is_main_query() && $wp_query->is_singular( 'taxonomy-page' ) ) {
		$post = $wp_query->get_queried_object();
		return $post instanceof \WP_Post ? $post : null;
	}
	// Term archive (e.g. /product-category/123/).
	return \Tarosky\RichTaxonomy\Controller\Rewrites::get_instance()->get_taxonomy_page_from_query( $wp_query, $include_admin, $only_first );
}

/**
 * Alternative of get_template_part.
 *
 * @see \Tarosky\RichTaxonomy\Controller\Templates::load_template
 */
function rich_taxonomy_template( $template, $suffix = '', $args = [] ) {
	\Tarosky\RichTaxonomy\Controller\Templates::get_instance()->load_template( $template, $suffix, $args );
}
