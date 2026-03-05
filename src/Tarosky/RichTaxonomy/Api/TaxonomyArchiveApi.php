<?php

namespace Tarosky\RichTaxonomy\Api;

use Tarosky\RichTaxonomy\Pattern\RestApiPattern;

/**
 * REST API for taxonomy archive page creation.
 *
 * @package rich-taxonomy
 */
class TaxonomyArchiveApi extends RestApiPattern {

	/**
	 * @inheritDoc
	 */
	protected function route() {
		return 'taxonomy-archive/(?P<taxonomy>[a-zA-Z0-9_-]+)';
	}

	/**
	 * @inheritDoc
	 */
	protected function get_rest_setting() {
		return [
			[
				'methods'             => 'POST',
				'args'                => [
					'taxonomy' => [
						'type'              => 'string',
						'description'       => _x( 'Taxonomy name to create archive page.', 'rest-param', 'rich-taxonomy' ),
						'validate_callback' => [ $this, 'validate_taxonomy' ],
					],
				],
				'callback'            => [ $this, 'callback' ],
				'permission_callback' => [ $this, 'permission_callback' ],
			],
		];
	}

	/**
	 * Validate taxonomy.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return \WP_Error|true
	 */
	public function validate_taxonomy( $taxonomy ) {
		$taxonomy_obj = get_taxonomy( $taxonomy );
		if ( ! $taxonomy_obj ) {
			return new \WP_Error( 'rich_taxonomy_api_error', __( 'Taxonomy not found.', 'rich-taxonomy' ) );
		}
		if ( ! $this->setting()->is_rich( $taxonomy ) ) {
			return new \WP_Error( 'rich_taxonomy_api_error', sprintf(
				/* translators: %s is taxonomy label */
				__( '%s is not enabled for taxonomy pages.', 'rich-taxonomy' ),
				$taxonomy_obj->label
			) );
		}
		if ( $this->has_post_for_taxonomy( $taxonomy ) ) {
			return new \WP_Error( 'rich_taxonomy_api_error', sprintf(
				/* translators: %s is taxonomy label */
				__( '%s already has a taxonomy archive page.', 'rich-taxonomy' ),
				$taxonomy_obj->label
			) );
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function callback( $request ) {
		$taxonomy = $request->get_param( 'taxonomy' );
		$post_id  = $this->draft_for_taxonomy( $taxonomy, 'api' );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		return new \WP_REST_Response( [
			'post_id'   => $post_id,
			'edit_link' => get_edit_post_link( $post_id, 'api' ),
		] );
	}
}
