<?php
/**
 * Test Setting class.
 *
 * @package rich-taxonomy
 */

use Tarosky\RichTaxonomy\Controller\Setting;

class TestSetting extends WP_UnitTestCase {

	/**
	 * @var Setting
	 */
	private $setting;

	public function setUp(): void {
		parent::setUp();
		$this->setting = Setting::get_instance();
		delete_option( 'rich_taxonomy_names' );
	}

	public function tearDown(): void {
		delete_option( 'rich_taxonomy_names' );
		parent::tearDown();
	}

	/**
	 * Test rich_taxonomies returns empty array by default.
	 */
	public function test_rich_taxonomies_default_is_empty() {
		$this->assertSame( [], $this->setting->rich_taxonomies() );
	}

	/**
	 * Test rich_taxonomies returns saved taxonomies.
	 */
	public function test_rich_taxonomies_returns_saved_values() {
		update_option( 'rich_taxonomy_names', [ 'category', 'post_tag' ] );
		$this->assertSame( [ 'category', 'post_tag' ], $this->setting->rich_taxonomies() );
	}

	/**
	 * Test is_rich returns true for enabled taxonomy.
	 */
	public function test_is_rich_returns_true_for_enabled() {
		update_option( 'rich_taxonomy_names', [ 'category' ] );
		$this->assertTrue( $this->setting->is_rich( 'category' ) );
	}

	/**
	 * Test is_rich returns false for disabled taxonomy.
	 */
	public function test_is_rich_returns_false_for_disabled() {
		update_option( 'rich_taxonomy_names', [ 'category' ] );
		$this->assertFalse( $this->setting->is_rich( 'post_tag' ) );
	}

	/**
	 * Test is_rich returns false when no option set.
	 */
	public function test_is_rich_returns_false_when_empty() {
		$this->assertFalse( $this->setting->is_rich( 'category' ) );
	}

	/**
	 * Test rich_taxonomies filter works.
	 */
	public function test_rich_taxonomies_filter() {
		$callback = function () {
			return [ 'custom_taxonomy' ];
		};
		add_filter( 'rich_taxonomy_taxonomies', $callback );
		$this->assertSame( [ 'custom_taxonomy' ], $this->setting->rich_taxonomies() );
		remove_filter( 'rich_taxonomy_taxonomies', $callback );
	}
}
