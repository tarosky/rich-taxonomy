<?php

namespace Tarosky\RichTaxonomy\Utility;

use Tarosky\RichTaxonomy\Controller\Setting;

/**
 * Term detector.
 *
 * @package rich-taxonomy
 */
trait PageAccessor {

	/**
	 * Post type.
	 *
	 * @return string
	 */
	public function post_type() {
		return 'taxonomy-page';
	}

	/**
	 * Meta key for rich term.
	 *
	 * @return string
	 */
	public function post_meta_key() {
		return '_rich_taxonomy_term_id';
	}

	/**
	 * Get term object.
	 *
	 * @param \WP_Term $term Term object.
	 * @return bool
	 */
	public function has_post( $term ) {
		return (bool) $this->get_post( $term );
	}

	/**
	 * Get post object assigned to term.
	 *
	 * @param \WP_Term|int $term         Term object or term id.
	 * @param bool         $only_publish If true, returns only published page.
	 * @return \WP_Post|null
	 */
	public function get_post( $term, $only_publish = false ) {
		$query_args = apply_filters( 'rich_taxonomy_get_post_args', [
			'post_type'      => $this->post_type(),
			'posts_per_page' => 1,
			'meta_query'     => [
				[
					'key'   => $this->post_meta_key(),
					'value' => isset( $term->term_id ) ? $term->term_id : $term,
				],
			],
			'no_found_rows'  => true,
		] );
		if ( $only_publish ) {
			$query_args['post_status'] = 'publish';
		}
		$query = new \WP_Query( $query_args );
		if ( $query->have_posts() ) {
			return $query->posts[0];
		} else {
			return null;
		}
	}

	/**
	 * Get term object of post.
	 *
	 * @param \WP_Post|null|int $post Post object.
	 * @return \WP_Term|null
	 */
	public function get_assigned_term( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return null;
		}
		$term_id = get_post_meta( $post->ID, $this->post_meta_key(), true );
		if ( ! $term_id ) {
			return null;
		}
		return get_term( $term_id );
	}

	/**
	 * Create draft for term.
	 *
	 * @param \WP_Term $term    Term object.
	 * @param string   $context Context for creation.
	 *
	 * @return int|\WP_Error
	 */
	public function draft_for_term( $term, $context = '' ) {
		$default_args = apply_filters( 'rich_taxonomy_default_post_object', [
			'post_type'    => $this->post_type(),
			'post_title'   => $term->name,
			'post_name'    => $term->slug,
			'post_excerpt' => $term->description,
			'post_content' => '',
			'post_status'  => 'draft',
		], $term, $context );
		$post_id      = wp_insert_post( $default_args, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		update_post_meta( $post_id, $this->post_meta_key(), $term->term_id );
		return $post_id;
	}
}
