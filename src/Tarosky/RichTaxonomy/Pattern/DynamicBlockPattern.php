<?php

namespace Tarosky\RichTaxonomy\Pattern;

/**
 * Dynamic block.
 *
 * @package rich-taxonomy
 */
abstract class DynamicBlockPattern extends BlockPattern {

	/**
	 * Render callback.
	 *
	 * @param array  $attributes Attributes.
	 * @param string $content    Content string.
	 *
	 * @return string
	 */
	abstract public function render_callback( $attributes, $content = '' );
}
