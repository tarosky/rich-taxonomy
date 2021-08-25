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
}
