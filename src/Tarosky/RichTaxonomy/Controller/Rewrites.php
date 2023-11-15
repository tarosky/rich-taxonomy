<?php

namespace Tarosky\RichTaxonomy\Controller;


use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;
use Tarosky\RichTaxonomy\Utility\TemplateAccessor;

/**
 * Rewrite rule controllers.
 *
 * @package rich-taxonomy
 */
class Rewrites extends Singleton {

	use PageAccessor;
	use SettingAccessor;
	use TemplateAccessor;

	/**
	 * Constructor
	 */
	protected function init() {
		// Change request.
		add_filter( 'posts_results', [ $this, 'posts_results' ], 10, 2 );
		// Filter for template.
		add_action( 'template_redirect', [ $this, 'register_template_filters' ] );
		// Change title.
		add_filter( 'single_term_title', [ $this, 'change_title' ] );
		add_filter( 'single_tag_title', [ $this, 'change_title' ] );
		add_filter( 'single_cat_title', [ $this, 'change_title' ] );
		// Add url.
		add_filter( 'post_type_link', [ $this, 'filter_permalink' ], 10, 2 );
	}

	/**
	 * Register template filters.
	 *
	 * @return void
	 */
	public function register_template_filters() {
		if ( $this->is_block_theme() ) {
			// This is block theme, so do nothing.
			return;
		}
		// Change template for archive.
		add_filter( 'tag_template', [ $this, 'archive_template_include' ], 10, 3 );
		add_filter( 'category_template', [ $this, 'archive_template_include' ], 10, 3 );
		add_filter( 'taxonomy_template', [ $this, 'archive_template_include' ], 10, 3 );
		// Change single page for template.
		add_filter( 'singular_template', [ $this, 'singular_template_include' ], 10, 3 );
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
		if ( is_admin() && ! $include_admin ) {
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
		$post            = $this->get_post( $term, ! current_user_can( 'edit_posts' ) );
		$should_override = apply_filters( 'rich_taxonomy_override', true, $post, $query );
		if ( ! $should_override ) {
			return null;
		}
		return apply_filters( 'rich_taxonomy_taxonomy_page', $post, $term, $query );
	}

	/**
	 * Change archive title.
	 *
	 * @param string $title Archive page title.
	 * @return string
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

	/**
	 * Change template.
	 *
	 * @param string   $template  Path to the template. See locate_template().
	 * @param string   $type      Sanitized filename without extension.
	 * @param string[] $templates A list of template candidates, in descending order of priority.
	 * @return string
	 */
	public function archive_template_include( $template, $type, $templates ) {
		global $wp_query;
		$page = $this->get_taxonomy_page_from_query( $wp_query );
		if ( ! $page ) {
			return $template;
		}
		$alternative_template = Templates::get_instance()->get_post_template_file( $page );
		if ( $alternative_template ) {
			$template = $alternative_template;
		}
		return $template;
	}

	/**
	 * Change template.
	 *
	 * @param string   $template  Path to the template. See locate_template().
	 * @param string   $type      Sanitized filename without extension.
	 * @param string[] $templates A list of template candidates, in descending order of priority.
	 * @return string
	 */
	public function singular_template_include( $template, $type, $templates ) {
		if ( is_singular() && $this->post_type() === get_queried_object()->post_type ) {
			$alternative_template = $this->template()->get_post_template_file( get_queried_object() );
			if ( $alternative_template ) {
				$template = $alternative_template;
			}
		}
		return $template;
	}

	/**
	 * Customize permalink.
	 *
	 * @param string   $link URL.
	 * @param \WP_Post $post Post object.
	 * @return string
	 */
	public function filter_permalink( $link, $post ) {
		if ( is_admin() || $post->post_type !== $this->post_type() ) {
			return $link;
		}
		if ( 'publish' !== $post->post_status ) {
			return $link;
		}
		$term = $this->get_assigned_term( $post );
		if ( ! $term ) {
			return $link;
		}
		return get_term_link( $term );
	}
}
