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
		// Add columns.
		add_filter( 'manage_' . $this->post_type() . '_posts_columns', [ $this, 'posts_columns' ] );
		add_action( 'manage_' . $this->post_type() . '_posts_custom_column', [ $this, 'posts_custom_columns' ], 10, 2  );
		// Edit form tag.
		add_action( 'admin_head', function() {
			$taxonomies = $this->setting()->rich_taxonomies();
			foreach ( $taxonomies as $taxonomy ) {
				add_action( $taxonomy . '_term_edit_form_top', [ $this, 'edit_form_fields' ], 10, 2 );
			}
		} );
		// Classic editor helper.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		// Block editor helper.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
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
				'create_posts'           => 'do_not_allow',
				'delete_posts'           => 'do_not_allow',
				'delete_published_posts' => 'do_not_allow',
				'delete_private_posts'   => 'do_not_allow',
				'delete_others_posts'    => 'do_not_allow',
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

	/**
	 * Add columns
	 *
	 * @param array $columns Column names.
	 * @return array
	 */
	public function posts_columns( $columns ) {
		$new_columns = [];
		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;
			if ( 'title' === $key ) {
				$new_columns[ 'taxonomy' ] = __( 'Taxonomy', 'rich-taxonomy' );
			}
		}
		return $new_columns;
	}

	/**
	 * Render post custom columns.
	 *
	 * @param string $column
	 * @param int $post_id
	 */
	public function posts_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'taxonomy':
				$term = get_term( (int) get_post_meta( $post_id, $this->post_meta_key(), true ) );
				if ( ! $term ) {
					echo 'err';
				} else {
					printf(
						'<a href="%s">%s</a><code>%s</code>',
						get_edit_term_link( $term->term_id, $term->taxonomy ),
						esc_html( $term->name ),
						esc_html( get_taxonomy( $term->taxonomy )->label )
					);
				}
				break;
			default:
				// Do nothing.
				break;
		}
	}

	/**
	 * Render form.
	 *
	 * @param \WP_Term $tag      Term object.
	 * @param string   $taxonomy Taxonomy.
	 */
	public function edit_form_fields( $tag, $taxonomy ) {
		$post = $this->get_post( $tag );
		?>
		<p>
			<?php if ( $post ) : ?>
				<?php printf( esc_html__( 'This term has taxonomy page: "%s"', 'rich-taxonomy' ), esc_html( get_the_title( $post ) ) ) ?>
				&raquo; <a href="<?php echo esc_url( get_edit_post_link( $post ) ) ?>"><?php esc_html_e( 'Edit', 'rich-taxonomy' ); ?></a>
			<?php else : ?>
				<span class="description"><?php esc_html_e( 'This term has no taxonomy page.', 'rich-taxonomy' ) ?></span>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Register meta box for classic editor.
	 *
	 * @param $post_type
	 */
	public function add_meta_box( $post_type ) {
		if ( $this->post_type() !== $post_type ) {
			return;
		}
		if ( get_current_screen()->is_block_editor() ) {
			return;
		}
		\add_meta_box( 'rich-taxonomy-original', __( 'Original Taxonomy', 'rich-taxonomy' ), function( \WP_Post $post ) {
			$term = $this->get_assigned_term( $post );
			if ( $term ) {
				printf(
					'<p>%s &raquo; <a href="%s" rel="noopener noreferrer" target="_blank">%s</a></p>',
					sprintf(
						wp_kses( __( 'Assigned Term: <strong>%s</strong> <code>%s</code>', 'rich-taxonomy' ), [ 'strong' => [], 'code' => [] ] ),
						esc_html( $term->name ),
						esc_html( get_taxonomy( $term->taxonomy )->label )
					),
					get_edit_term_link( $term->term_id ),
					esc_html__( 'Edit', 'rich-taxonomy' )
				);
			} else {
				printf( '<p class="description">%s</p>', esc_html__( 'This post has no assigned term.', 'rich-taxonomy' ) );
			}
		}, $post_type, 'side', 'high' );
	}

	/**
	 * Enqueue Block editor assets.
	 */
	public function enqueue_block_editor_assets() {
		$screen = get_current_screen();
		if ( $this->post_type() !== $screen->post_type ) {
			return;
		}
		$this->enqueue_js( 'rich-taxonomy-editor-helper', 'js/editor-helper.js', [
			'wp-plugins',
			'wp-edit-post',
			'wp-components',
			'wp-data',
			'wp-api-fetch',
			'wp-i18n',
			'wp-compose',
			'wp-dom-ready',
		] );
	}
}
