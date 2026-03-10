<?php
/**
 * PHPStan bootstrap file.
 *
 * Defines constants that are set in the main plugin file but not available
 * during static analysis when src/ files are analyzed in isolation.
 */

if ( ! defined( 'RICH_TAXONOMY_PLUGIN_DIR' ) ) {
	define( 'RICH_TAXONOMY_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
}
