<?php

namespace Tarosky\RichTaxonomy\Controller;

use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;

/**
 * Rewrite rules for taxonomy archive (base URL) pages.
 *
 * @package rich-taxonomy
 */
class TaxonomyArchiveRewrites extends Singleton {

	use PageAccessor;
	use SettingAccessor;

	/**
	 * Query var for taxonomy archive.
	 *
	 * @var string
	 */
	const QUERY_VAR = 'rich_taxonomy_archive';

	/**
	 * Constructor.
	 */
	protected function init() {
		// Priority 25: run after theme's rules (20) so our rule is prepended last and matches first.
		add_action( 'init', [ $this, 'add_rewrite_rules' ], 25 );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		// Priority 11: run after theme's pre_get_posts (10) to override taxonomy_archive handling.
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], 11 );
		// Load template directly to bypass theme's template_include (which may load wrong template).
		add_action( 'template_redirect', [ $this, 'template_redirect' ], 5 );
		add_filter( 'template_include', [ $this, 'template_include' ], 999 );
		add_filter( 'post_type_link', [ $this, 'filter_permalink' ], 11, 2 );
	}

	/**
	 * Add rewrite rules for taxonomy base URLs.
	 */
	public function add_rewrite_rules() {
		foreach ( $this->setting()->rich_taxonomies() as $taxonomy_name ) {
			$taxonomy = get_taxonomy( $taxonomy_name );
			if ( ! $taxonomy || empty( $taxonomy->rewrite['slug'] ) ) {
				continue;
			}
			$slug = $taxonomy->rewrite['slug'];
			$rule = $slug . '/?$';
			add_rewrite_rule( $rule, 'index.php?' . self::QUERY_VAR . '=' . $taxonomy_name, 'top' );
		}
	}

	/**
	 * Register query var.
	 *
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	/**
	 * Get taxonomy from query (supports theme's taxonomy_archive var).
	 *
	 * @param \WP_Query $query Query object.
	 * @return string|null
	 */
	protected function get_taxonomy_from_query( $query ) {
		$taxonomy = $query->get( self::QUERY_VAR );
		if ( $taxonomy ) {
			return $taxonomy;
		}
		// Support theme's taxonomy_archive query var (e.g. pubplagraph theme).
		$taxonomy_archive = $query->get( 'taxonomy_archive' );
		return $taxonomy_archive ? $taxonomy_archive : null;
	}

	/**
	 * Handle taxonomy archive request - replace main query with our page.
	 *
	 * @param \WP_Query $query Query object.
	 */
	public function pre_get_posts( $query ) {
		if ( ! $query->is_main_query() ) {
			return;
		}
		$taxonomy = $this->get_taxonomy_from_query( $query );
		if ( ! $taxonomy ) {
			return;
		}
		if ( ! $this->setting()->is_rich( $taxonomy ) ) {
			return;
		}
		$page = $this->get_post_for_taxonomy( $taxonomy, ! current_user_can( 'edit_posts' ) );
		if ( ! $page ) {
			return;
		}
		$should_override = apply_filters( 'rich_taxonomy_taxonomy_archive_override', true, $page, $query );
		if ( ! $should_override ) {
			return;
		}
		$query->set( 'post_type', $this->post_type() );
		$query->set( 'p', $page->ID );
		$query->set( 'page_id', $page->ID );
		$query->set( 'posts_per_page', 1 );
		$query->set( self::QUERY_VAR, '' );
		$query->set( 'taxonomy_archive', '' );
		$query->set( 'is_taxonomy_archive', false );
		$query->is_singular = true;
		$query->is_single   = true;
		$query->is_archive  = false;
		$query->is_tax      = false;
		$query->is_category = false;
		$query->is_tag      = false;
	}

	/**
	 * Load template directly - bypasses template_include to avoid theme conflicts.
	 */
	public function template_redirect() {
		global $wp_query, $post;
		if ( ! $wp_query->is_main_query() ) {
			return;
		}
		if ( ! $wp_query->is_singular( 'taxonomy-page' ) ) {
			return;
		}
		$post = $wp_query->get_queried_object();
		if ( ! $post instanceof \WP_Post || ! $this->is_taxonomy_archive_page( $post ) ) {
			return;
		}
		$GLOBALS['post'] = $post;
		setup_postdata( $post );
		$template = RICH_TAXONOMY_PLUGIN_DIR . 'templates/singular-taxonomy-page.php';
		if ( file_exists( $template ) ) {
			load_template( $template, false );
			exit;
		}
	}

	/**
	 * Ensure singular template is used when we override taxonomy archive.
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function template_include( $template ) {
		global $wp_query, $post;
		if ( ! $wp_query->is_main_query() ) {
			return $template;
		}
		if ( ! $wp_query->is_singular( 'taxonomy-page' ) ) {
			return $template;
		}
		$post = $wp_query->get_queried_object();
		if ( ! $post instanceof \WP_Post || ! $this->is_taxonomy_archive_page( $post ) ) {
			return $template;
		}
		$GLOBALS['post'] = $post;
		$custom_template = \Tarosky\RichTaxonomy\Controller\Templates::get_instance()->get_post_template_file( $post );
		if ( $custom_template ) {
			return $custom_template;
		}
		// Plugin template (guaranteed to display content).
		$plugin_template = RICH_TAXONOMY_PLUGIN_DIR . 'templates/singular-taxonomy-page.php';
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
		// Fallback: use theme templates.
		$fallback = locate_template( [ 'single.php', 'page.php', 'singular.php', 'index.php' ] );
		return $fallback ? $fallback : $template;
	}

	/**
	 * Customize permalink for taxonomy archive pages.
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
		$taxonomy = $this->get_assigned_taxonomy( $post );
		if ( ! $taxonomy ) {
			return $link;
		}
		$taxonomy_obj = get_taxonomy( $taxonomy );
		if ( ! $taxonomy_obj || empty( $taxonomy_obj->rewrite['slug'] ) ) {
			return $link;
		}
		return home_url( '/' . $taxonomy_obj->rewrite['slug'] . '/' );
	}
}
