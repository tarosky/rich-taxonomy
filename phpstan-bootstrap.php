<?php
/**
 * PHPStan bootstrap file.
 *
 * Defines constants that are set in the main plugin file but not available
 * during static analysis when src/ files are analyzed in isolation.
 */

if ( ! defined( 'RICH_TAXONOMY_PLUGIN_DIR' ) ) {
	define( 'RICH_TAXONOMY_PLUGIN_DIR', __DIR__ . '/' );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}
