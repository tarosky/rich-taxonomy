<?php

namespace Tarosky\RichTaxonomy\Vendor;



use Tarosky\RichTaxonomy\Pattern\AbstractVendor;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;

/**
 * Additional hooks for Taro Custom Style.
 */
class TaroCustomStyle extends AbstractVendor {

	use PageAccessor,
		SettingAccessor;

	/**
	 * {@inheritDoc}
	 */
	protected function is_active() {
		return function_exists( 'tcs_get_style_group' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function register_hooks() {
		add_filter( 'tcs_style_groups', [ $this, 'add_style_group' ] );
		add_filter( 'tcs_object_for_style', [ $this, 'override_rich_taxonomy_page' ] );
	}

	/**
	 * Add style group for rich term page.
	 *
	 * @param \WP_Term[] $styles Style group of terms.
	 * @return \WP_Term[]
	 */
	public function add_style_group( $styles ) {
		$rich_taxonomy_page = rich_taxonomy_current_post();
		if ( ! $rich_taxonomy_page ) {
			return $styles;
		}
		// Rich taxonomy page found.
		$style_group = get_the_terms( $rich_taxonomy_page, 'style-group' );
		if ( $style_group && ! is_wp_error( $style_group ) ) {
			foreach ( $style_group as $style ) {
				$styles[] = $style;
			}
		}
		return $styles;
	}

	/**
	 * If current page is a rich taxonomy archive, change queried object.
	 *
	 * @param \WP_Term|\WP_Post|null $object
	 * @return \WP_Term|\WP_Post|null
	 */
	public function override_rich_taxonomy_page( $object ) {
		return rich_taxonomy_current_post() ?: $object;
	}
}
