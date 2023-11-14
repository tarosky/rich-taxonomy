<?php

namespace Tarosky\RichTaxonomy\Pattern;


/**
 * Vendor pattern for hooks.
 */
abstract class AbstractVendor extends Singleton {

	/**
	 * Is this service active?
	 *
	 * @return bool
	 */
	abstract protected function is_active();

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	abstract protected function register_hooks();

	/**
	 * If this is active, regsiter hooks.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'plugins_loaded', [ $this, 'ensure_plugins_loaded' ], 9999 );
	}

	/**
	 * Check if this service is active and register hooks.
	 *
	 * @return void
	 */
	public function ensure_plugins_loaded() {
		if ( $this->is_active() ) {
			$this->register_hooks();
		}
	}
}
