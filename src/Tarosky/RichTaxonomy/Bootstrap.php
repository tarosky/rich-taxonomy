<?php

namespace Tarosky\RichTaxonomy;


use Tarosky\RichTaxonomy\Api\PostApi;
use Tarosky\RichTaxonomy\Api\TermApi;
use Tarosky\RichTaxonomy\Blocks\TermArchiveBlock;
use Tarosky\RichTaxonomy\Controller\DataSync;
use Tarosky\RichTaxonomy\Controller\Editor;
use Tarosky\RichTaxonomy\Controller\Rewrites;
use Tarosky\RichTaxonomy\Controller\Setting;
use Tarosky\RichTaxonomy\Controller\Templates;
use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\DirectoryAccessor;
use Tarosky\RichTaxonomy\Vendor\TaroCustomStyle;

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
		// Controllers.
		Setting::get_instance();
		Editor::get_instance();
		Templates::get_instance();
		DataSync::get_instance();
		// REST API
		TermApi::get_instance();
		PostApi::get_instance();
		// Rewrite rules.
		Rewrites::get_instance();
		// Register block.
		TermArchiveBlock::get_instance();
		// Register scripts.
		add_action( 'init', [ $this, 'register_assets' ] );
		// Register block assets.
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_block_assets' ] );
		// Additional hooks.
		TaroCustomStyle::get_instance();
	}

	/**
	 * Register assets.
	 */
	public function register_assets() {
		$json = $this->root_dir() . 'wp-dependencies.json';
		if ( ! file_exists( $json ) ) {
			if ( 'cli' !== php_sapi_name() ) {
				// Raise error if this is production.
				trigger_error( __( 'Dependency list is missing.', 'rich-taxonomy' ), E_USER_WARNING );
			}
			return;
		}
		$assets = json_decode( file_get_contents( $json ), true );
		if ( $assets ) {
			foreach ( $assets as $asset ) {
				$url = $this->root_url() . '/' . $asset['path'];
				switch ( $asset['ext'] ) {
					case 'js':
						wp_register_script( $asset['handle'], $url, $asset['deps'], $asset['hash'], $asset['footer'] );
						break;
					case 'css':
						wp_register_style( $asset['handle'], $url, $asset['deps'], $asset['hash'], $asset['media'] );
						break;
				}
			}
		}
	}

	/**
	 * Register block assets.
	 */
	public function register_block_assets() {
		wp_localize_script( 'rich-taxonomy-ensure-post-type', 'RichTaxonomyEnsurePostType', [
			'postType' => Editor::get_instance()->post_type(),
		] );
	}
}
