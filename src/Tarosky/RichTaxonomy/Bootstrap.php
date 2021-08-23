<?php

namespace Tarosky\RichTaxonomy;


use Tarosky\RichTaxonomy\Api\PostApi;
use Tarosky\RichTaxonomy\Api\TermApi;
use Tarosky\RichTaxonomy\Controller\Editor;
use Tarosky\RichTaxonomy\Controller\Rewrites;
use Tarosky\RichTaxonomy\Controller\Setting;
use Tarosky\RichTaxonomy\Pattern\Singleton;

/**
 * Bootstrap file.
 *
 * @package rich-taxonomy
 */
class Bootstrap extends Singleton {

	/**
	 * Make instance.
	 */
	protected function init() {
		// Controllers.
		Setting::get_instance();
		Editor::get_instance();
		// REST API
		TermApi::get_instance();
		PostApi::get_instance();
		// Rewrite rules.
		Rewrites::get_instance();
	}
}
