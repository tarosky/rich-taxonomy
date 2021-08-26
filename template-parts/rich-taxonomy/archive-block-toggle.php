<?php
/**
 * Archive block toggle button.
 *
 * @package rich-taxonomy
 * @since 1.0.0
 * @var array{toggle: string, more: string, number: int, url: string, query: WP_Query, term: WP_Term } $args
 */

?>

<div class="rich-taxonomy-toggle-button wp-block-buttons is-content-justification-center">
	<div class="wp-block-button">
		<button class="wp-block-button__link rich-taxonomy-toggle"><?php echo esc_html( $args['toggle'] ); ?></button>
	</div>
</div>
