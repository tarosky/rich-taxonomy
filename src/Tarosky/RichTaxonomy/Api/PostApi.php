<?php

namespace Tarosky\RichTaxonomy\Api;


use Tarosky\RichTaxonomy\Pattern\RestApiPattern;

/**
 * REST API for post's term.
 *
 * @package rich-taxonomy
 */
class PostApi extends RestApiPattern {

	/**
	 * @inheritDoc
	 */
	protected function route() {
		return 'term/(?P<post_id>\d+)';
	}

	/**
	 * @inheritDoc
	 */
	protected function get_rest_setting() {
		return [
			[
				'methods'             => 'GET',
				'args'                => [
					'post_id' => [
						'required'          => true,
						'type'              => 'int',
						'description'       => _x( 'Post ID', 'rest-param', 'rich-taxonomy' ),
						'validate_callback' => [ $this, 'validate_post' ],
					],
				],
				'callback'            => [ $this, 'callback' ],
				'permission_callback' => [ $this, 'permission_callback' ],
			],
		];
	}

	/**
	 * Validate post id.
	 *
	 * @param mixed $value Variable.
	 *
	 * @return \WP_Error|bool
	 */
	public function validate_post( $value ) {
		if ( ! is_numeric( $value ) ) {
			return false;
		}
		$post = get_post( $value );
		if ( ! $post || $this->post_type() !== $post->post_type ) {
			return new \WP_Error( 'rest_api_error', __( 'Invalid post requested.', 'rich-taxonomy' ) );
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'edit_post', $request->get_param( 'post_id' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function callback( $request ) {
		$term = $this->get_assigned_term( $request->get_param( 'post_id' ) );
		if ( ! $term ) {
			return new \WP_Error( 'rest_api_error', __( 'No term.', 'rich-taxonomy' ) );
		}
		return new \WP_REST_Response( [
			'name'      => $term->name,
			'slug'      => $term->slug,
			'taxonomy'  => [
				'name'  => $term->taxonomy,
				'label' => get_taxonomy( $term->taxonomy )->label,
			],
			'edit_link' => get_edit_term_link( $term->term_id, $term->taxonomy ),
		] );
	}
}
