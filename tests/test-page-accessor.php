<?php
/**
 * Test PageAccessor trait.
 *
 * @package rich-taxonomy
 */

use Tarosky\RichTaxonomy\Utility\PageAccessor;

/**
 * Concrete class to test PageAccessor trait.
 */
class PageAccessorTestable {
	use PageAccessor;
}

class TestPageAccessor extends WP_UnitTestCase {

	/**
	 * @var PageAccessorTestable
	 */
	private $accessor;

	public function setUp(): void {
		parent::setUp();
		$this->accessor = new PageAccessorTestable();
		// Register the custom post type for testing.
		register_post_type( 'taxonomy-page', [
			'public' => false,
		] );
	}

	public function tearDown(): void {
		unregister_post_type( 'taxonomy-page' );
		parent::tearDown();
	}

	/**
	 * Test post_type returns expected value.
	 */
	public function test_post_type() {
		$this->assertSame( 'taxonomy-page', $this->accessor->post_type() );
	}

	/**
	 * Test post_meta_key returns expected value.
	 */
	public function test_post_meta_key() {
		$this->assertSame( '_rich_taxonomy_term_id', $this->accessor->post_meta_key() );
	}

	/**
	 * Test draft_for_term creates a draft post linked to term.
	 */
	public function test_draft_for_term_creates_post() {
		$term_id = self::factory()->term->create( [
			'taxonomy' => 'category',
			'name'     => 'Test Category',
			'slug'     => 'test-category',
		] );
		$term    = get_term( $term_id, 'category' );

		$post_id = $this->accessor->draft_for_term( $term );

		$this->assertIsInt( $post_id );
		$post = get_post( $post_id );
		$this->assertSame( 'taxonomy-page', $post->post_type );
		$this->assertSame( 'draft', $post->post_status );
		$this->assertSame( 'Test Category', $post->post_title );
		$this->assertSame( 'test-category', $post->post_name );
	}

	/**
	 * Test draft_for_term stores term_id as post meta.
	 */
	public function test_draft_for_term_sets_meta() {
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$term    = get_term( $term_id, 'category' );

		$post_id = $this->accessor->draft_for_term( $term );

		$stored_term_id = get_post_meta( $post_id, '_rich_taxonomy_term_id', true );
		$this->assertEquals( $term_id, $stored_term_id );
	}

	/**
	 * Test get_post returns the linked post (published).
	 */
	public function test_get_post_returns_linked_post() {
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$term    = get_term( $term_id, 'category' );
		$post_id = $this->accessor->draft_for_term( $term );
		wp_update_post( [
			'ID'          => $post_id,
			'post_status' => 'publish',
		] );

		$found = $this->accessor->get_post( $term );

		$this->assertInstanceOf( WP_Post::class, $found );
		$this->assertEquals( $post_id, $found->ID );
	}

	/**
	 * Test get_post returns null when no post linked.
	 */
	public function test_get_post_returns_null_when_not_found() {
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$term    = get_term( $term_id, 'category' );

		$this->assertNull( $this->accessor->get_post( $term ) );
	}

	/**
	 * Test has_post returns true when published post exists.
	 */
	public function test_has_post_returns_true() {
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$term    = get_term( $term_id, 'category' );
		$post_id = $this->accessor->draft_for_term( $term );
		wp_update_post( [
			'ID'          => $post_id,
			'post_status' => 'publish',
		] );

		$this->assertTrue( $this->accessor->has_post( $term ) );
	}

	/**
	 * Test has_post returns false when no post.
	 */
	public function test_has_post_returns_false() {
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$term    = get_term( $term_id, 'category' );

		$this->assertFalse( $this->accessor->has_post( $term ) );
	}

	/**
	 * Test get_post with only_publish flag.
	 */
	public function test_get_post_only_publish_skips_draft() {
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$term    = get_term( $term_id, 'category' );
		$this->accessor->draft_for_term( $term );

		$this->assertNull( $this->accessor->get_post( $term, true ) );
	}

	/**
	 * Test get_post with only_publish returns published post.
	 */
	public function test_get_post_only_publish_returns_published() {
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$term    = get_term( $term_id, 'category' );
		$post_id = $this->accessor->draft_for_term( $term );
		wp_update_post( [
			'ID'          => $post_id,
			'post_status' => 'publish',
		] );

		$found = $this->accessor->get_post( $term, true );
		$this->assertInstanceOf( WP_Post::class, $found );
		$this->assertEquals( $post_id, $found->ID );
	}

	/**
	 * Test get_assigned_term returns the linked term.
	 */
	public function test_get_assigned_term() {
		$term_id = self::factory()->term->create( [ 'taxonomy' => 'category' ] );
		$term    = get_term( $term_id, 'category' );
		$post_id = $this->accessor->draft_for_term( $term );

		$found_term = $this->accessor->get_assigned_term( $post_id );

		$this->assertInstanceOf( WP_Term::class, $found_term );
		$this->assertEquals( $term_id, $found_term->term_id );
	}

	/**
	 * Test get_assigned_term returns null for unlinked post.
	 */
	public function test_get_assigned_term_returns_null_for_unlinked() {
		$post_id = self::factory()->post->create( [
			'post_type' => 'taxonomy-page',
		] );

		$this->assertNull( $this->accessor->get_assigned_term( $post_id ) );
	}
}
