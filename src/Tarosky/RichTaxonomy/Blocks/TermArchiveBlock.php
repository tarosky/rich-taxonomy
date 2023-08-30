<?php

namespace Tarosky\RichTaxonomy\Blocks;


use Tarosky\RichTaxonomy\Pattern\DynamicBlockPattern;
use Tarosky\RichTaxonomy\Utility\PageAccessor;
use Tarosky\RichTaxonomy\Utility\TemplateAccessor;

/**
 * Term archive block.
 *
 * @package rich-taxonomy
 */
class TermArchiveBlock extends DynamicBlockPattern {

	use PageAccessor;
	use TemplateAccessor;

	/**
	 * Get attribute.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		return [
			'toggle' => [
				'type'    => 'string',
				'default' => '',
			],
			'more'   => [
				'type'    => 'string',
				'default' => '',
			],
			'number' => [
				'type'    => 'int',
				'default' => 0,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function render_callback( $attributes, $content = '' ) {
		$term = $this->get_assigned_term();
		if ( ! $term ) {
			return '';
		}
		$attributes         = apply_filters( 'rich_taxonomy_archive_block_arguments', wp_parse_args( $attributes, [
			'toggle' => __( 'More', 'rich-taxonomy' ),
			'more'   => '',
			'number' => 0,
			'url'    => '',
		] ), get_the_ID() );
		$attributes['term'] = $term;
		if ( empty( $attributes['more'] ) ) {
			// translators: %s is the page title.
			$attributes['more'] = sprintf( __( 'Archive of %s', 'rich-taxonomy' ), get_the_title() );
		}
		// Build query arguments.
		$args = [];
		switch ( $term->taxonomy ) {
			case 'category':
				$args = [ 'category_name' => $term->slug ];
				break;
			case 'post_tag':
				$args = [ 'tag' => $term->slug ];
				break;
			default:
				$args = [ $term->taxonomy => $term->slug ];
				break;
		}
		$args      = apply_filters( 'rich_taxonomy_archive_block_query_args', $args, $term, get_the_ID() );
		$sub_query = new \WP_Query( $args );
		if ( ! $sub_query->have_posts() ) {
			return '';
		}
		$attributes['query'] = $sub_query;
		// If next page exists, get link.
		if ( $sub_query->found_posts > $sub_query->post_count ) {
			global $wp_rewrite;
			$term_link = get_term_link( $term );
			if ( $wp_rewrite->using_permalinks() ) {
				$attributes['url'] = implode( '/', [ untrailingslashit( $term_link ), $wp_rewrite->pagination_base, 2 ] );
			} else {
				$attributes['url'] = add_query_arg( [
					'paged' => 2,
				], $term_link );
			}
		}
		// Render with query.
		ob_start();
		$this->template()->load_template( 'template-parts/rich-taxonomy/archive-block-wrapper', '', $attributes );
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	/**
	 * @inheritDoc
	 */
	protected function editor_script() {
		return 'rich-taxonomy-term-archive-block';
	}

	/**
	 * @inheritDoc
	 */
	protected function script() {
		return 'rich-taxonomy-term-archive-block-helper';
	}

	/**
	 * @inheritDoc
	 */
	protected function editor_style() {
		return 'rich-taxonomy-term-archive-block-editor';
	}


	/**
	 * @inheritDoc
	 */
	protected function style() {
		return 'rich-taxonomy-term-archive-block';
	}
}
