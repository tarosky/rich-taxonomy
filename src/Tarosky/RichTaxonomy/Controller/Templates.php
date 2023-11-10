<?php

namespace Tarosky\RichTaxonomy\Controller;


use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\DirectoryAccessor;
use Tarosky\RichTaxonomy\Utility\PageAccessor;

/**
 * Template Selector.
 *
 * @package Tarosky\RichTaxonomy\Controller
 */
class Templates extends Singleton {

	use PageAccessor;
	use DirectoryAccessor;

	const META_KEY = '_rich_taxonomy_template';

	/**
	 * Register hooks.
	 */
	protected function init() {
		add_action( 'save_post', [ $this, 'save_post' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Register meta boxes.
	 *
	 * @param string $post_type
	 */
	public function add_meta_boxes( $post_type ) {
		$screen = get_current_screen();
		if ( $post_type !== $this->post_type() ) {
			// Do nothing.
			return;
		}
		// TODO: Change block
		if ( $screen->is_block_editor() ) {
			// return;
		}
		add_meta_box( 'template_selector', __( 'Template', 'rich-taxonomy' ), [ $this, 'render_meta_box' ], $post_type, 'side' );
	}

	/**
	 * Save post information.
	 *
	 * @param int $post_id Post Id.
	 */
	public function save_post( $post_id ) {
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_richtaxonomynonce' ), 'rich_taxonomy_template' ) ) {
			return;
		}
		update_post_meta( $post_id, self::META_KEY, filter_input( INPUT_POST, 'rich-taxonomy-template' ) );
	}

	/**
	 * Render meta box.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'rich_taxonomy_template', '_richtaxonomynonce', false );
		$current       = $this->get_post_template( $post );
		$template_list = $this->get_template_list();
		if ( $template_list ) :
			?>
		<p>
			<label>
				<?php esc_html_e( 'Template File', 'rich-taxonomy' ); ?><br />
				<select name="rich-taxonomy-template" class="widefat" style="box-sizing: border-box;">
					<?php
					foreach ( $template_list as $template ) {
						printf(
							'<option value="%s"%s>%s</option>',
							esc_attr( $template ),
							selected( $template, $current, false ),
							esc_html( $template )
						);
					}
					?>
				</select>
			</label>
		</p>
		<?php else : ?>
		<p class="wp-ui-text-notification">
			<?php esc_html_e( 'No template available. Maybe uncommon theme structure or using block theme.', 'rich-taxonomy' ); ?>
		</p>
			<?php
		endif;
	}

	/**
	 * Get templates.
	 *
	 * @return string[]
	 */
	public function get_template_list() {
		$default = $this->get_default_template();
		$list    = [];
		foreach ( $this->get_dirs() as $dir ) {
			foreach ( scandir( $dir ) as $file ) {
				if ( ! preg_match( '/^(single\.php|singular\.php|page\.php|page-.*\.php)$/u', $file ) ) {
					continue 1;
				}
				if ( $default !== $file && ! in_array( $file, $list, true ) ) {
					$list[] = $file;
				}
			}
		}
		if ( ! empty( $default ) ) {
			array_unshift( $list, $default );
		}
		return apply_filters( 'rich_taxonomy_template_list', $list );
	}

	/**
	 * Get template for post.
	 *
	 * @param null|int|\WP_Post $post
	 * @return string
	 */
	public function get_post_template( $post = null ) {
		$post     = get_post( $post );
		$template = get_post_meta( $post->ID, self::META_KEY, true );
		if ( ! $template ) {
			$template = $this->get_default_template();
		}
		return apply_filters( 'rich_taxonomy_template', $template, $post );
	}

	/**
	 * Get default template.
	 *
	 * @return string
	 */
	public function get_default_template() {
		$found = apply_filters( 'rich_taxonomy_default_template', '' );
		if ( $found ) {
			return $found;
		}
		$files = [ 'singular-' . $this->post_type() . '.php', 'page.php', 'singular.php', 'single.php', 'index.php' ];
		foreach ( $files as $file ) {
			foreach ( $this->get_dirs() as $dir ) {
				$path = $dir . '/' . $file;
				if ( file_exists( $path ) ) {
					$found = $file;
					break 2;
				}
			}
		}
		return $found;
	}

	/**
	 * Get target directories.
	 *
	 * @return string[]
	 */
	private function get_dirs() {
		// Build template list.
		$dirs = [ get_stylesheet_directory() ];
		// If child theme exists, select it.
		if ( get_stylesheet_directory() !== get_template_directory() ) {
			$dirs[] = get_template_directory();
		}
		return $dirs;
	}

	/**
	 * Get post template absolute path.
	 *
	 * @param \WP_Post $post Post object.
	 * @return string
	 */
	public function get_post_template_file( $post ) {
		$template = [
			$this->get_post_template(),
			$this->get_default_template(),
		];
		foreach ( $template as $t ) {
			foreach ( $this->get_dirs() as $dir ) {
				$path = $dir . '/' . $t;
				if ( file_exists( $path ) ) {
					return $path;
				}
			}
		}
		return '';
	}

	/**
	 * Load template from plugin or theme.
	 *
	 * @param string $template Template name.
	 * @param string $suffix   Suffix.
	 * @param array  $args     Arguments.
	 */
	public function load_template( $template, $suffix = '', $args = [] ) {
		$dirs      = $this->get_dirs();
		$dirs[]    = untrailingslashit( $this->root_dir() );
		$templates = [ $template ];
		if ( $suffix ) {
			array_unshift( $templates, $template . '-' . $suffix );
		}
		$found = '';
		foreach ( $templates as $t ) {
			foreach ( $dirs as $dir ) {
				$path = $dir . '/' . $t . '.php';
				if ( file_exists( $path ) ) {
					$found = $path;
					break 2;
				}
			}
		}
		$found = apply_filters( 'rich_taxonomy_include_template', $found, $template, $suffix, $args );
		if ( ! $found ) {
			return;
		}
		\load_template( $found, false, $args );
	}
}
