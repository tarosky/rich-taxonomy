<?php

namespace Tarosky\RichTaxonomy\Controller;

use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;
use Tarosky\RichTaxonomy\Utility\TemplateAccessor;

/**
 * Rewrite rules for taxonomy archive (base URL) pages.
 *
 * @package rich-taxonomy
 */
class TaxonomyArchiveRewrites extends Singleton {

	use PageAccessor;
	use SettingAccessor;
	use TemplateAccessor;

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
		// Priority 30: run after theme's rules (20, 25) so our rule is prepended last and matches first.
		add_action( 'init', [ $this, 'add_rewrite_rules' ], 30 );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		// Priority 11: run after theme's pre_get_posts (10) to override taxonomy_archive handling.
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], 11 );
		add_filter( 'template_include', [ $this, 'template_include' ], 999 );
		add_filter( 'post_type_link', [ $this, 'filter_permalink' ], 11, 2 );
	}

	/**
	 * Add rewrite rules for taxonomy base URLs.
	 */
	public function add_rewrite_rules() {
		foreach ( $this->setting()->rich_taxonomies() as $taxonomy_name ) {
			$taxonomy = get_taxonomy( $taxonomy_name );
			if ( ! $taxonomy || ! is_array( $taxonomy->rewrite ?? null ) ) {
				continue;
			}
			// Use rewrite slug (category/tag use permalink settings). Fallback to taxonomy name.
			$slug = ! empty( $taxonomy->rewrite['slug'] ) ? $taxonomy->rewrite['slug'] : $taxonomy_name;
			$rule = preg_quote( $slug, '/' ) . '/?$';
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
		setup_postdata( $post );

		// Use plugin template first (guaranteed to display content with the_content()).
		$plugin_template = RICH_TAXONOMY_PLUGIN_DIR . 'templates/singular-taxonomy-page.php';
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		if ( $this->is_block_theme() ) {
			return $this->filter_block_template_include( $template, $post );
		}

		// Classic theme: use Templates::get_post_template_file() for custom template selection.
		$custom_template = \Tarosky\RichTaxonomy\Controller\Templates::get_instance()->get_post_template_file( $post );
		if ( $custom_template ) {
			return $custom_template;
		}
		// Fallback: use theme templates.
		$fallback = locate_template( [ 'single.php', 'page.php', 'singular.php', 'index.php' ] );
		return $fallback ? $fallback : $template;
	}

	/**
	 * Filter block template include for taxonomy archive base URL (singular taxonomy-page).
	 *
	 * @param string   $template Path to the template file.
	 * @param \WP_Post $post     Taxonomy page post.
	 * @return string
	 */
	protected function filter_block_template_include( $template, $post ) {
		global $_wp_current_template_id, $_wp_current_template_content;
		$theme_slug     = get_stylesheet();
		$custom_slug    = \Tarosky\RichTaxonomy\Controller\Templates::get_instance()->get_post_template( $post );
		$custom_slug    = $custom_slug ? preg_replace( '/\.php$/', '', $custom_slug ) : '';
		$page_template  = get_page_template_slug( $post ) ?: $custom_slug;
		$template_slugs = $page_template ? [ $page_template, 'single', 'index' ] : [ 'single', 'index' ];
		foreach ( $template_slugs as $slug ) {
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
