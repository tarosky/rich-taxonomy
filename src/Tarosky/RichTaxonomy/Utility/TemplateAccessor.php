<?php

namespace Tarosky\RichTaxonomy\Utility;

use Tarosky\RichTaxonomy\Controller\Templates;

/**
 * Template accessor.
 *
 * @package rich-taxonomy
 */
trait TemplateAccessor {

	/**
	 * Get template instance.
	 *
	 * @return Templates
	 */
	public function template() {
		return Templates::get_instance();
	}

	/**
	 * Detect if this template is block theme.
	 *
	 * @return bool
	 */
	public function is_block_theme() {
		return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}
}
