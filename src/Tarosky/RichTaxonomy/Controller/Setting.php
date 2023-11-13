<?php

namespace Tarosky\RichTaxonomy\Controller;

use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\TemplateAccessor;

/**
 * Setting class.
 *
 * @package rich-taxonomy
 */
class Setting extends Singleton {

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
		add_action( 'admin_notices', [ $this, 'notice_for_block_theme' ] );
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
			printf( '<p class="description">%s</p>', esc_html__( 'Specified taxonomies will have taxonomy page. To create a new taxonomy page, go to the terms archive in admin panel.', 'rich-taxonomy' ) );
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
		register_setting( 'reading', $this->option_name );
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
	 * Display a notice for block themes.
	 *
	 * @return void
	 */
	public function notice_for_block_theme( $pagenow ) {
		if ( $this->is_block_theme() ) {
			printf(
				'<div class="error"><p>%s</p></div>',
				sprintf(
					// translators: %s is a theme name.
					__( 'Your current theme %s is a block theme. Rich Taxonomy is not necessary.', 'rich-taxonomy' ),
					esc_html( wp_get_theme()->get( 'Name' ) )
				)
			);
		}
	}
}
