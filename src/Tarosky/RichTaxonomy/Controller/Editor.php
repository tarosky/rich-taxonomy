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

	use SettingAccessor;
	use PageAccessor;
	use DirectoryAccessor;

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
		add_action( 'manage_' . $this->post_type() . '_posts_custom_column', [ $this, 'posts_custom_columns' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'posts_list_actions' ], 10, 2 );
		// Edit form tag.
		add_action( 'admin_head', function () {
			$taxonomies = $this->setting()->rich_taxonomies();
			foreach ( $taxonomies as $taxonomy ) {
				add_action( $taxonomy . '_term_edit_form_top', [ $this, 'edit_form_fields' ], 10, 2 );
			}
		} );
		// Classic editor helper.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		// Block editor helper.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		// Add notice for broken pages.
		add_action( 'admin_notices', [ $this, 'notice_for_broken_pages' ] );
	}

	/**
	 * Add action links.
	 *
	 * @param string[] $actions Links.
	 * @param \WP_Term $tag     Term object.
	 */
	public function action_links( $actions, $tag ) {
		if ( $this->setting()->is_rich( $tag->taxonomy ) ) {
			$link                          = $this->has_post( $tag ) ? get_edit_post_link( $this->get_post( $tag ) ) : sprintf( '#create-%d', $tag->term_id );
			$actions['edit_rich_taxonomy'] = sprintf( '<a class="rich-taxonomy-link" href="%s">%s</a>', esc_url( $link ), esc_html__( 'Taxonomy Page', 'rich-taxonomy' ) );
		}
		return $actions;
	}

	/**
	 * Post list action.
	 *
	 * @param string[] $actions Action links.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return string[]
	 */
	public function posts_list_actions( $actions, $post ) {
		if ( $this->post_type() !== $post->post_type ) {
			return $actions;
		}
		// If already published, check original page.
		if ( 'publish' === $post->post_status ) {
			$term = $this->get_assigned_term( $post );
			if ( $term && ! is_wp_error( $term ) ) {
				$actions['rich-taxonomy-preview'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_term_link( $term ) ),
					esc_html__( 'View Term Archive', 'rich-taxonomy' )
				);
			}
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
		$can            = current_user_can( 'edit_posts' );
		$post_type_args = [
			'label'               => __( 'Taxonomy Page', 'rich-taxonomy' ),
			'public'              => $can,
			'rewrite'             => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => $can,
			'show_ui'             => true,
			'menu_icon'           => 'dashicons-admin-page',
			'show_in_nav_menus'   => false,
			'show_in_rest'        => true,
			'capability_type'     => 'post',
			'capabilities'        => [
				'create_posts' => 'do_not_allow',
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
			wp_enqueue_script( 'rich-taxonomy-admin-ui-tag-list' );
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
				$new_columns['taxonomy'] = __( 'Taxonomy', 'rich-taxonomy' );
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
				if ( ! $term || is_wp_error( $term ) ) {
					printf( '<span style="color:lightgray"><span class="dashicons dashicons-no"></span> %s</span>', esc_html__( 'Error', 'rich-taxonomy' ) );
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
				<?php
				// translators: %s is post title.
				printf( esc_html__( 'This term has taxonomy page: "%s"', 'rich-taxonomy' ), esc_html( get_the_title( $post ) ) );
				?>
				&raquo; <a href="<?php echo esc_url( get_edit_post_link( $post ) ); ?>"><?php esc_html_e( 'Edit', 'rich-taxonomy' ); ?></a>
			<?php else : ?>
				<span class="description"><?php esc_html_e( 'This term has no taxonomy page.', 'rich-taxonomy' ); ?></span>
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
		\add_meta_box( 'rich-taxonomy-original', __( 'Original Taxonomy', 'rich-taxonomy' ), function ( \WP_Post $post ) {
			$term = $this->get_assigned_term( $post );
			if ( $term ) {
				printf(
					'<p>%s &raquo; <a href="%s" rel="noopener noreferrer" target="_blank">%s</a></p>',
					sprintf(
						// translators: %1$s is term name, %2$s is taxonomy.
						wp_kses( __( 'Assigned Term: <strong>%1$s</strong> <code>%2$s</code>', 'rich-taxonomy' ), [ 'strong' => [], 'code' => [] ] ),
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
		wp_enqueue_script( 'rich-taxonomy-editor-helper' );
	}

	/**
	 * Display a notice for broken pages.
	 *
	 * @return void
	 */
	public function notice_for_broken_pages( $pagenow ) {
		// Only show on the taxonomy page list screen.
		$screen = get_current_screen();
		if ( ! $screen || 'edit-taxonomy-page' !== $screen->id || $screen->post_type !== $this->post_type() ) {
			return;
		}
		// Get all the taxonomies selected in Settings.
		$enabled_taxonomies = $this->setting()->rich_taxonomies();
		// Get all taxonomy pages.
		$paged          = max( 1, get_query_var( 'paged', 1 ) );
		$per_page       = get_user_option( 'edit_' . $screen->post_type . '_per_page' );
		$posts_per_page = $per_page ? $per_page : 20;
		$query          = new \WP_Query([
			'post_type'      => $this->post_type(),
			'post_status'    => 'any',
			'posts_per_page' => $posts_per_page,
			'paged'          => $paged,
			'fields'         => 'ids',
		]);
		// Stop here if no posts.
		if ( ! $query->have_posts() ) {
			return;
		}
		$broken_pages = [];
		foreach ( $query->posts as $post_id ) {
			$term_id = get_post_meta( $post_id, $this->post_meta_key(), true );
			$term    = get_term( (int) $term_id );
			if ( $term && ! is_wp_error( $term ) ) {
				if ( ! in_array( $term->taxonomy, $enabled_taxonomies, true ) ) {
					$taxonomy_obj   = get_taxonomy( $term->taxonomy );
					$broken_pages[] = [
						'title'    => get_the_title( $post_id ),
						'edit_url' => get_edit_post_link( $post_id ),
						'taxonomy' => $taxonomy_obj ? $taxonomy_obj->label : $term->taxonomy,
					];
				}
			}
		}
		if ( ! empty( $broken_pages ) ) {
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Warning:', 'rich-taxonomy' ); ?></strong>
					<?php esc_html_e( 'These pages may not display correctly. Make sure their taxonomies are selected under Settings → Reading.', 'rich-taxonomy' ); ?>
				</p>
				<ul style="list-style: disc; margin-left: 16px;">
					<?php foreach ( $broken_pages as $page ) : ?>
						<li>
							<a href="<?php echo esc_url( $page['edit_url'] ); ?>"><?php echo esc_html( $page['title'] ); ?></a>
							(
							<?php esc_html_e( 'Taxonomy:', 'rich-taxonomy' ); ?>
							<code><?php echo esc_html( $page['taxonomy'] ); ?></code>
							)
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		}
	}
}
