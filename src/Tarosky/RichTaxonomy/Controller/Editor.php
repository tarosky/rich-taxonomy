<?php

namespace Tarosky\RichTaxonomy\Controller;


use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\DirectoryAccessor;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;

/**
 * Editor instance.
 *
 * @package rich-taxonomy
 */
class Editor extends Singleton {

	use SettingAccessor,
		PageAccessor,
		DirectoryAccessor;

	/**
	 * Constructor.
	 */
	protected function init() {
		// Register actions links.
		add_filter( 'tag_row_actions', [ $this, 'action_links' ], 10, 2 );
		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		// Register post types.
		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	/**
	 * Add action links.
	 *
	 * @param string[] $actions Links.
	 * @param \WP_Term $tag     Term object.
	 */
	public function action_links( $actions, $tag ) {
		if ( $this->setting()->is_rich( $tag->taxonomy ) ) {
			$link = $this->has_post( $tag ) ? get_edit_post_link( $this->get_post( $tag ) ) : sprintf( '#create-%d', $tag->term_id );
			$actions['edit_rich_taxonomy'] = sprintf( '<a class="rich-taxonomy-link" href="%s">%s</a>', esc_url( $link ), esc_html__( 'Taxonomy Page', 'rich-taxonomy' ) );
		}
		return $actions;
	}

	/**
	 * Register post type.
	 */
	public function register_post_type() {
		// If nothing is created, do not create post type.
		if ( empty( $this->setting()->rich_taxonomies() ) ) {
			return;
		}
		// Register post type.
		$can = current_user_can( 'edit_posts' );
		$post_type_args = [
			'label'               => __( 'Taxonomy Page', 'rich-taxonomy' ),
			'public'              => $can,
			'rewrite'             => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => $can,
			'show_ui'             => true,
			'menu_icon'           => 'dashicons-admin-page',
			'show_in_nav_menus'   => false,
			'show_in_rest'        => true,
			'capability_type'     => 'post',
			'capabilities'        => [
				'create_posts' => 'do_not_allow',
				'delete_posts' => 'do_not_allow',
			],
			'map_meta_cap'        => true,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		];
		$post_type_args = apply_filters( 'rich_taxonomy_post_type_args', $post_type_args );
		\register_post_type( $this->post_type(), $post_type_args );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param string $suffix Admin page suffix.
	 */
	public function admin_enqueue_scripts( $suffix ) {
		if ( 'edit-tags.php' === $suffix ) {
			$this->enqueue_js( 'rich-taxonomy-admin-ui-tag-list', 'js/admin-ui-tag-list.js', [ 'jquery', 'wp-i18n', 'wp-api-fetch' ] );
		}
	}
}
