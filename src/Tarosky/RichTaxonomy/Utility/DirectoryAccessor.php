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
		return plugin_dir_path( dirname( __DIR__, 3 ) );
	}

	/**
	 * Get root url.
	 *
	 * @return string
	 */
	public function root_url() {
		return untrailingslashit( plugin_dir_url( $this->root_dir() . 'assets' ) );
	}

	/**
	 * Get asset url.
	 *
	 * @param string $path Relative path of assets.
	 * @return string
	 */
	public function asset_url( $path ) {
		return $this->root_url() . '/dist/' . ltrim( $path, '/' );
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
	 * @deprecated
	 * @param string   $handle Handle name.
	 * @param string   $path   Relative path.
	 * @param string[] $deps   Dependencies.
	 */
	public function enqueue_js( $handle, $path, $deps ) {
		wp_enqueue_script( $handle, $this->asset_url( $path ), $deps, rich_taxonomy_version(), true );
	}
}
