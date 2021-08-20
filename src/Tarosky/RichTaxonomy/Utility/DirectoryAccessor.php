<?php

namespace Tarosky\RichTaxonomy\Utility;

/**
 * Directory access helper.
 *
 * @package Tarosky\RichTaxonomy\Utility
 */
trait DirectoryAccessor {

	/**
	 * Get base directory.
	 *
	 * @return string
	 */
	public function root_dir() {
		return plugin_dir_path( dirname( dirname( dirname( __DIR__ ) ) ) );
	}

	/**
	 * Get asset url.
	 *
	 * @param string $path Relative path of assets.
	 * @return string
	 */
	public function asset_url( $path ) {
		$base = plugin_dir_url( $this->root_dir() . 'assets' ) . 'dist/';
		return $base . ltrim( $path, '/' );
	}

	/**
	 * Get asset path.
	 *
	 * @param string $path Relative path of assets.
	 * @return string
	 */
	public function asset_path( $path ) {
		$base = $this->root_dir() . 'dist/';
		return $base . ltrim( $path, '/' );
	}

	/**
	 * Enqueue script.
	 *
	 * @param string   $handle Handle name.
	 * @param string   $path   Relative path.
	 * @param string[] $deps   Dependencies.
	 */
	public function enqueue_js( $handle, $path, $deps ) {
		wp_enqueue_script( $handle, $this->asset_url( $path ), $deps, rich_taxonomy_version(), true );
	}
}
