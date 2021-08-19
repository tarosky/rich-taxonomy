<?php

namespace Tarosky\RichTaxonomy\Utility;

use Tarosky\RichTaxonomy\Controller\Setting;

/**
 * Trait SettingAccessor
 *
 * @package rich-taxonomy
 */
trait SettingAccessor {

	/**
	 * Get
	 *
	 * @return Setting
	 */
	public function setting() {
		return Setting::get_instance();
	}
}
