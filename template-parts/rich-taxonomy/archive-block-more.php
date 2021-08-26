<?php
/**
 * Archive block more button.
 *
 * @package rich-taxonomy
 * @since 1.0.0
 * @var array{toggle: string, more: string, number: int, url: string, query: WP_Query, term: WP_Term } $args
 */

?>

<div class="rich-taxonomy-more-button wp-block-buttons is-content-justification-center">
	<div class="wp-block-button">
		<a class="wp-block-button__link" href="<?php echo esc_url( $args['url'] ); ?>"><?php echo esc_html( $args['more'] ); ?></a>
	</div>
</div>
