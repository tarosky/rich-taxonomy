<?php

namespace Tarosky\RichTaxonomy;


use Tarosky\RichTaxonomy\Controller\Editor;
use Tarosky\RichTaxonomy\Controller\RestApi;
use Tarosky\RichTaxonomy\Controller\Setting;
use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\DirectoryAccessor;

/**
 * Bootstrap file.
 *
 * @package rich-taxonomy
 */
class Bootstrap extends Singleton {

	use DirectoryAccessor;

	/**
	 * Make instance.
	 */
	protected function init() {
		Setting::get_instance();
		Editor::get_instance();
		RestApi::get_instance();
	}
}
