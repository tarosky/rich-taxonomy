<?php

namespace Tarosky\RichTaxonomy\Controller;

use Tarosky\RichTaxonomy\Pattern\Singleton;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;

/**
 * Rest API handler.
 *
 * @package rich-taxonomy
 */
class RestApi extends Singleton {

	use PageAccessor, SettingAccessor;

	/**
	 * Constructor.
	 */
	protected function init() {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	/**
	 * Register REST API endpoint.
	 */
	public function register_rest() {
		register_rest_route( 'rich-taxonomy/v1', 'post/(?P<term_id>\d+)', [
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
		] );
	}

	/**
	 * Handle request.
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'edit_posts' );
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
	 * Handle request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_Error|\WP_REST_Response
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
