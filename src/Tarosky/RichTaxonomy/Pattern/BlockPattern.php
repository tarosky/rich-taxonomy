<?php

namespace Tarosky\RichTaxonomy\Pattern;

/**
 * Block pattern.
 *
 * @package Tarosky\RichTaxonomy\Pattern
 */
abstract class BlockPattern extends Singleton {

	/**
	 * Block name.
	 *
	 * @return string
	 */
	protected function name() {
		$class_name = explode( '\\', get_called_class() );
		return preg_replace( '/^-/u', '', preg_replace_callback( '/[A-Z]/u', function ( $matches ) {
			list( $letter ) = $matches;
			return '-' . strtolower( $letter );
		}, $class_name[ count( $class_name ) - 1 ] ) );
	}

	/**
	 * Get block name.
	 *
	 * @return string
	 */
	protected function block_name() {
		return sprintf( 'rich-taxonomy/%s', $this->name() );
	}

	/**
	 * Constructor.
	 */
	protected function init() {
		add_action( 'init', [ $this, 'register_block' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'block_editor_assets' ], 1 );
	}

	/**
	 * Register blocks.
	 */
	public function register_block() {
		$args = [
			'attributes' => $this->get_attributes(),
		];
		foreach ( [ 'style', 'editor_style', 'script', 'editor_script' ] as $key ) {
			$asset = $this->{$key}();
			if ( $asset ) {
				$args[ $key ] = apply_filters( 'rich_taxonomy_block_asset_' . $key, $asset, $this->block_name() );
			}
		}
		// Register callback.
		if ( is_callable( [ $this, 'render_callback' ] ) ) {
			$args['render_callback'] = [ $this, 'render_callback' ];
		}
		// Apply filter for block.
		$args = apply_filters( 'rich_taxonomy_block', $args, $this->block_name() );
		register_block_type( $this->block_name(), $args );
	}

	/**
	 * Get an attribute.
	 *
	 * @return array
	 */
	abstract protected function get_attributes();

	/**
	 * Script name.
	 *
	 * @return string
	 */
	protected function editor_script() {
		return '';
	}

	/**
	 * Script for block.
	 *
	 * @return string
	 */
	protected function script() {
		return '';
	}

	/**
	 * Editor style.
	 *
	 * @return string
	 */
	protected function editor_style() {
		return '';
	}

	/**
	 * Stylesheet.
	 *
	 * @return string
	 */
	protected function style() {
		return '';
	}

	/**
	 * Editor assets.
	 */
	public function block_editor_assets() {
		$editor_script = $this->editor_script();
		if ( $editor_script ) {
			$class_name = explode( '\\', get_called_class() );
			$base_class = $class_name[ count( $class_name ) - 1 ];
			wp_localize_script( $editor_script, 'RichTaxonomy' . $base_class, $this->block_variables() );
		}
	}

	/**
	 * Get block variables.
	 *
	 * @return array
	 */
	protected function block_variables() {
		return [
			'name'       => $this->block_name(),
			'attributes' => $this->get_attributes(),
		];
	}
}
