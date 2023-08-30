<?php

namespace Tarosky\RichTaxonomy\Pattern;

use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\SettingAccessor;

/**
 * Rest API pattern.
 *
 * @package rich-taxonomy
 */
abstract class RestApiPattern extends Singleton {

	use PageAccessor;
	use SettingAccessor;

	protected $namespace = 'rich-taxonomy/v1';

	/**
	 * Get route.
	 *
	 * @return string
	 */
	abstract protected function route();

	/**
	 * Constructor
	 */
	protected function init() {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	/**
	 * Register REST API.
	 */
	public function register_rest() {
		register_rest_route( $this->namespace, $this->route(), $this->get_rest_setting() );
	}

	/**
	 * REST API  setting.
	 *
	 * @return array[]
	 */
	abstract protected function get_rest_setting();

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
	 * Handle request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	abstract public function callback( $request );
}
