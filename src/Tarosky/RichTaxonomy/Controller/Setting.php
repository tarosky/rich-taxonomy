<?php

namespace Tarosky\RichTaxonomy\Controller;

use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\TemplateAccessor;

/**
 * Setting class.
 *
 * @package rich-taxonomy
 */
class Setting extends Singleton {

	use PageAccessor;
	use TemplateAccessor;

	/**
	 * @var string Option key.
	 */
	protected $option_name = 'rich_taxonomy_names';

	/**
	 * Denied taxonomies.
	 *
	 * @return string[]
	 */
	protected function denied_taxonomies() {
		return apply_filters( 'rich_taxonomy_denied_taxnomies', [ 'post_format' ] );
	}

	/**
	 * Constructor.
	 */
	protected function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_notices', [ $this, 'notice_for_getting_started' ] );
		add_action( 'admin_footer-options-reading.php', [ $this, 'print_taxonomy_archive_script' ] );
	}

	/**
	 * Print script for taxonomy archive page creation.
	 */
	public function print_taxonomy_archive_script() {
		if ( ! wp_script_is( 'wp-api-fetch', 'enqueued' ) ) {
			wp_enqueue_script( 'wp-api-fetch' );
		}
		?>
		<script>
		document.addEventListener( 'DOMContentLoaded', function() {
			document.querySelectorAll( '.rich-taxonomy-create-archive' ).forEach( function( link ) {
				link.addEventListener( 'click', function( e ) {
					e.preventDefault();
					var restUrl = this.getAttribute( 'data-rest-url' );
					var nonce = this.getAttribute( 'data-nonce' );
					if ( ! restUrl ) return;
					this.style.pointerEvents = 'none';
					this.textContent = '<?php echo esc_js( __( 'Creating...', 'rich-taxonomy' ) ); ?>';
					wp.apiFetch( {
						url: restUrl,
						method: 'POST',
						headers: { 'X-WP-Nonce': nonce }
					} ).then( function( res ) {
						if ( res.edit_link ) {
							window.location.href = res.edit_link;
						}
					} ).catch( function( err ) {
						alert( err.message || '<?php echo esc_js( __( 'Failed to create.', 'rich-taxonomy' ) ); ?>' );
						link.style.pointerEvents = '';
						link.textContent = link.getAttribute( 'data-original-text' ) || '<?php echo esc_js( __( 'Create', 'rich-taxonomy' ) ); ?>';
					} );
				} );
			} );
		} );
		</script>
		<?php
	}

	/**
	 * Register settings.
	 */
	public function admin_init() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		// Register setting fields.
		add_settings_section( 'rich-taxonomy-section', __( 'Taxonomy Page', 'rich-taxonomy' ), function () {
			printf( '<p class="description">%s</p>', esc_html__( 'Selected taxonomies will have their own page. To create or manage taxonomy pages, go to the taxonomy terms list (e.g., Categories or Tags), then hover over a term and click “Taxonomy Page”.', 'rich-taxonomy' ) );
		}, 'reading' );
		// Taxonomies.
		add_settings_field( $this->option_name, __( 'Taxonomies', 'rich-taxonomy' ), function () {
			$taxonomies = array_filter(
				get_taxonomies( [
					'public' => true,
				], OBJECT ),
				function ( \WP_Taxonomy $taxonomy ) {
					return ! in_array( $taxonomy->name, $this->denied_taxonomies(), true );
				}
			);
			foreach ( $taxonomies as $taxonomy ) {
				printf(
					'<label style="display: inline-block; margin: 0 1em 0.5em 0;"><input type="checkbox" name="%s[]" value="%s" %s/> %s</label>',
					esc_attr( $this->option_name ),
					esc_attr( $taxonomy->name ),
					checked( $this->is_rich( $taxonomy->name ), true, false ),
					esc_html( $taxonomy->label )
				);
			}
		}, 'reading', 'rich-taxonomy-section' );
		// Taxonomy archive page links.
		add_settings_field( 'rich_taxonomy_archive_pages', __( 'Taxonomy Archive Page', 'rich-taxonomy' ), [ $this, 'render_taxonomy_archive_links' ], 'reading', 'rich-taxonomy-section' );
		register_setting( 'reading', $this->option_name );
		add_action( 'update_option_' . $this->option_name, [ $this, 'flush_rewrite_on_option_update' ], 10, 3 );
	}

	/**
	 * Flush rewrite rules when taxonomy settings change.
	 *
	 * @param mixed  $old_value Old option value.
	 * @param mixed  $value     New option value.
	 * @param string $option    Option name.
	 */
	public function flush_rewrite_on_option_update( $old_value, $value, $option ) {
		if ( $old_value !== $value ) {
			flush_rewrite_rules();
		}
	}

	/**
	 * Render taxonomy archive page links.
	 */
	public function render_taxonomy_archive_links() {
		$rich_taxonomies = $this->rich_taxonomies();
		if ( empty( $rich_taxonomies ) ) {
			echo '<p class="description">' . esc_html__( 'Select taxonomies above first.', 'rich-taxonomy' ) . '</p>';
			return;
		}
		echo '<p class="description">' . esc_html__( 'Create a custom page for the taxonomy base URL (e.g., /category/, /tag/).', 'rich-taxonomy' ) . '</p>';
		echo '<ul style="margin-top: 0.5em;">';
		foreach ( $rich_taxonomies as $taxonomy_name ) {
			$taxonomy = get_taxonomy( $taxonomy_name );
			if ( ! $taxonomy ) {
				continue;
			}
			$page  = $this->get_post_for_taxonomy( $taxonomy_name );
			$label = $taxonomy->label;
			if ( $page ) {
				$link = get_edit_post_link( $page );
				$text = sprintf(
					/* translators: %s: taxonomy label */
					__( 'Edit %s archive page', 'rich-taxonomy' ),
					$label
				);
				printf( '<li><a href="%s">%s</a></li>', esc_url( $link ), esc_html( $text ) );
			} else {
				$rest_url = rest_url( 'rich-taxonomy/v1/taxonomy-archive/' . $taxonomy_name );
				$nonce    = wp_create_nonce( 'wp_rest' );
				$text     = sprintf(
					/* translators: %s: taxonomy label */
					__( 'Create %s archive page', 'rich-taxonomy' ),
					$label
				);
				printf(
					'<li><a href="#" class="rich-taxonomy-create-archive" data-taxonomy="%s" data-nonce="%s" data-rest-url="%s" data-original-text="%s">%s</a></li>',
					esc_attr( $taxonomy_name ),
					esc_attr( $nonce ),
					esc_attr( $rest_url ),
					esc_attr( $text ),
					esc_html( $text )
				);
			}
		}
		echo '</ul>';
	}

	/**
	 * Get taxonomies to be rich.
	 *
	 * @return string[]
	 */
	public function rich_taxonomies() {
		$taxonomies = (array) get_option( $this->option_name, [] );

		return apply_filters( 'rich_taxonomy_taxonomies', $taxonomies );
	}

	/**
	 * If taxonomy should be rich, return true.
	 *
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return bool
	 */
	public function is_rich( $taxonomy ) {
		return in_array( $taxonomy, $this->rich_taxonomies(), true );
	}

	/**
	 * Display a notice for getting started.
	 *
	 * @return void
	 */
	public function notice_for_getting_started( $pagenow ) {
		if ( empty( array_filter( $this->rich_taxonomies() ) ) ) {
			$plugin_name = '<strong>' . esc_html__( 'Rich Taxonomy', 'rich-taxonomy' ) . '</strong>';
			$message     = sprintf(
				// translators: %s is the plugin name.
				__( 'To start using %s, go to Settings → Reading and choose the taxonomies you want to enable pages for.', 'rich-taxonomy' ),
				$plugin_name
			);
			printf(
				'<div class="notice notice-info"><p>%s</p></div>',
				$message
			);
		}
	}
}
