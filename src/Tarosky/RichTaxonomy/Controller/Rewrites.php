<?php

namespace Tarosky\RichTaxonomy\Controller;


use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;

/**
 * Rewrite rule controllers.
 *
 * @package rich-taxonomy
 */
class Rewrites extends Singleton {

	use PageAccessor,
		SettingAccessor;

	/**
	 * Constructor
	 */
	protected function init() {
		add_filter( 'posts_results', [ $this, 'posts_results' ], 10, 2 );
		add_filter( 'single_term_title', [ $this, 'change_title' ] );
		add_filter( 'single_tag_title', [ $this, 'change_title' ] );
		add_filter( 'single_cat_title', [ $this, 'change_title' ] );
	}

	/**
	 * Filter posts results.
	 *
	 * @param \WP_Post[] $posts    Retrieved post.
	 * @param \WP_Query  $wp_query Query object.
	 * @return \WP_Post[]
	 */
	public function posts_results( $posts, $wp_query ) {
		$page = $this->get_taxonomy_page_from_query( $wp_query );
		if ( ! $page ) {
			return $posts;
		}
		return [ $page ];
	}


	/**
	 * Get page object from WP_Query.
	 *
	 * @param \WP_Query $query         Query object.
	 * @param bool      $include_admin If true, get taxonomy page even if in admin.
	 * @param bool      $only_first    If false, include each paged archive.
	 * @return \WP_Post|null
	 */
	public function get_taxonomy_page_from_query( $query = null, $include_admin = false, $only_first = true ) {
		if ( is_admin() && ! $include_admin) {
			return null;
		}
		if ( ! $query->is_main_query() ) {
			return null;
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
		} else {
			return null;
		}
		// Is first page?
		$paged = $query->get( 'paged' );
		$paged = max( (int) $paged, 1 );
		if ( 1 < $paged && $only_first ) {
			return null;
		}
		// Is rich taxonomy?
		if ( ! $this->setting()->is_rich( $term->taxonomy ) ) {
			return null;
		}
		// Should override?
		$post = $this->get_post( $term, true );
		$should_override = apply_filters( 'rich_taxonomy_override', true, $post, $query );
		if ( ! $should_override ) {
			return null;
		}
		return apply_filters( 'rich_taxonomy_taxonomy_page', $post, $term, $query );
	}

	/**
	 * Change archive title.
	 *
	 * @param string $title
	 */
	public function change_title( $title ) {
		global $wp_query;
		$page = $this->get_taxonomy_page_from_query( $wp_query, false, false );
		if ( ! $page ) {
			return $title;
		}
		$title = get_the_title( $page );
		return apply_filters( 'rich_taxonomy_archive_title', $title, $page, get_queried_object() );
	}
}
