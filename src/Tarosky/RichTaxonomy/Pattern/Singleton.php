<?php

namespace Tarosky\RichTaxonomy\Pattern;

/**
 * Singleton pattern.
 *
 * @package rich-taxonomy
 */
abstract class Singleton {

	/**
	 * @var static[] Instance holder.
	 */
	private static $instances = [];

	/**
	 * Constructor.
	 */
	final protected function __construct() {
		$this->init();
	}

	/**
	 * Initializer.
	 */
	protected function init() {
		// Do nothing.
	}

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	final public static function get_instance() {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name();
		}
		return self::$instances[ $class_name ];
	}
}
