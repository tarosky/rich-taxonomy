<?php
/**
Plugin Name: Rich Taxonomy
Description: Add rich LP for taxnomy archive.
Plugin URI: https://wordpress.org/plugins/rich-taxonomy/
Author: Tarosky INC.
Version: nightly
Author URI: https://tarosky.co.jp/
License: GPL3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: rich-taxonomy
Domain Path: /languages
 */

defined( 'ABSPATH' ) or die();

/**
 * Initializer.
 */
function rich_taxonomy_init() {
	// Load text domain.
	load_plugin_textdomain( 'rich-taxonomy', false, basename( __DIR__ ) . '/languages' );
	// Initialize.
	$autoloader = __DIR__ . '/vendor/autoload.php';
	if ( file_exists( $autoloader ) ) {
		require $autoloader;
		Tarosky\RichTaxonomy\Bootstrap::get_instance();
	}
	// Load functions.
	require_once __DIR__ . '/functions.php';
}
add_action( 'plugin_loaded', 'rich_taxonomy_init' );

/**
 * Get plugin version.
 *
 * @return string
 */
function rich_taxonomy_version() {
	static $version = '';
	if ( $version ) {
		return $version;
	}
	$info    = get_file_data( __FILE__, [
		'version' => 'Version',
	] );
	$version = $info['version'];
	return $version;
}
