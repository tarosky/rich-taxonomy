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
			// For block themes, modify template ID via template_include filter.
			add_filter( 'template_include', [ $this, 'filter_block_template_include' ], 100 );
			return;
		}
		// Change template for archive.
		add_filter( 'tag_template', [ $this, 'archive_template_include' ], 10, 3 );
		add_filter( 'category_template', [ $this, 'archive_template_include' ], 10, 3 );
		add_filter( 'taxonomy_template', [ $this, 'archive_template_include' ], 10, 3 );
		// Change single page for template.
		add_filter( 'single_template', [ $this, 'singular_template_include' ], 10, 3 );
		add_filter( 'singular_template', [ $this, 'singular_template_include' ], 10, 3 );
	}

	/**
	 * Filter block template include for taxonomy archives.
	 *
	 * When a Taxonomy Page exists, change the block template ID to singular.
	 *
	 * @param string $template Path to the template file.
	 * @return string
	 */
	public function filter_block_template_include( $template ) {
		global $wp_query, $_wp_current_template_id, $_wp_current_template_content;
		$page = $this->get_taxonomy_page_from_query( $wp_query );
		if ( ! $page ) {
			return $template;
		}
		// Get template assigned to the Taxonomy Page post.
		$theme_slug     = get_stylesheet();
		$page_template  = get_page_template_slug( $page );
		$template_slugs = $page_template ? [ $page_template, 'single', 'index' ] : [ 'single', 'index' ];
		foreach ( $template_slugs as $slug ) {
			// Try database templates first, then file templates.
			$block_template = get_block_template( $theme_slug . '//' . $slug, 'wp_template' );
			if ( ! $block_template ) {
				$block_template = get_block_file_template( $theme_slug . '//' . $slug, 'wp_template' );
			}
			if ( $block_template ) {
				$_wp_current_template_id      = $block_template->id;
				$_wp_current_template_content = $block_template->content;
				break;
			}
		}
		return $template;
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
		// Ensure we're on a taxonomy archive and the queried object is valid.
		if ( ! ( is_category() || is_tag() || is_tax() ) ) {
			return null;
		}
		$term = get_queried_object();
		// Make sure $term is a WP_Term object and has a taxonomy property.
		if ( ! ( $term instanceof \WP_Term ) || empty( $term->taxonomy ) ) {
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
