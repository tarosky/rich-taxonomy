<?php

namespace Tarosky\RichTaxonomy\Controller;


use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;

/**
 * Sync data between term and post.
 *
 * @package rich-taxonomy
 */
class DataSync extends Singleton {

	use PageAccessor;
	use SettingAccessor;

	/**
	 * Register hooks.
	 */
	protected function init() {
		add_filter( 'get_the_excerpt', [ $this, 'term_page_excerpt' ], 10, 2 );
		// If term is deleted, remove related posts.
		add_action( 'delete_term', [ $this, 'delete_term_page' ], 10, 3 );
	}

	/**
	 * Filter excerpt
	 *
	 * @param string   $excerpt Post excerpt.
	 * @param \WP_Post $post Post object.
	 * @return string
	 */
	public function term_page_excerpt( $excerpt, $post ) {
		if ( $this->post_type() !== $post->post_type ) {
			return $excerpt;
		}
		if ( ! $excerpt ) {
			return $excerpt;
		}
		$term = $this->get_assigned_term( $post );
		if ( ! $term ) {
			return $excerpt;
		}
		return $term->description;
	}

	/**
	 * If term is deleted, delete term page too.
	 *
	 * @param int $term_id  Term id.
	 * @param int $tt_id    Term taxonomy id.
	 * @param int $taxonomy Term id.
	 */
	public function delete_term_page( $term_id, $tt_id, $taxonomy ) {
		if ( ! $this->setting()->is_rich( $taxonomy ) ) {
			return;
		}
		$post = $this->get_post( $term_id );
		if ( ! $post ) {
			return;
		}
		wp_delete_post( $post->ID );
	}
}
