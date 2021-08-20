<?php

namespace Tarosky\RichTaxonomy\Api;

use Tarosky\RichTaxonomy\Pattern\RestApiPattern;
use Tarosky\RichTaxonomy\Pattern\Singleton;

/**
 * Rest API handler.
 *
 * @package rich-taxonomy
 */
class TermApi extends RestApiPattern {

	/**
	 * @inheritDoc
	 */
	protected function route() {
		return 'post/(?P<term_id>\d+)';
	}

	protected function get_rest_setting() {
		return [
			[
				'methods'             => 'POST',
				'args'                => [
					'term_id' => [
						'type'              => 'int',
						'description'       => _x( 'Term ID to create post.', 'rest-param', 'rich-taxonomy' ),
						'validate_callback' => [ $this, 'validate_term_id' ],
					],
				],
				'callback'            => [ $this, 'callback' ],
				'permission_callback' => [ $this, 'permission_callback' ],
			]
		];
	}

	/**
	 * Test variables.
	 *
	 * @param int $var Term Id.
	 * @return \WP_Error|true
	 */
	public function validate_term_id( $var ) {
		$term = get_term( $var );
		if ( ! $term ) {
			return new \WP_Error( 'rich_taxonomy_api_error', __( 'Term not found.', 'rich-taxonomy' ) );
		}
		if ( ! $this->setting()->is_rich( $term->taxonomy ) ) {
			return new \WP_Error( 'rich_taxonomy_api_error', sprintf( __( '%s is not able to have a taxonomy page.' ), $term->name ) );
		}
		if ( $this->has_post( $term ) ) {
			return new \WP_Error( 'rich_taxonomy_api_error', sprintf( __( '%s already has a taxonomy page.' ), $term->name ) );
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function callback( $request ) {
		$term = get_term( $request->get_param( 'term_id' ) );
		$post_id = $this->draft_for_term( $term );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		return new \WP_REST_Response( [
			'post_id'   => $post_id,
			'edit_link' => get_edit_post_link( $post_id, 'api' ),
		] );
	}
}
