<?php
/**
 * Get archive wrapper.
 *
 * @package rich-taxonomy
 * @since 1.0.0
 * @var array{toggle: string, more: string, number: int, url: string, query: WP_Query, term: WP_Term } $args
 */

$should_hide = 0 < $args['number'] && $args['query']->post_count > $args['number'];

?>
<div class="rich-taxonomy-wrapper">

	<nav class="rich-taxonomy-container">
		<?php
		$counter = 0;
		while ( $args['query']->have_posts() ) {
			++$counter;
			$args['query']->the_post();
			rich_taxonomy_template( 'template-parts/rich-taxonomy/archive-block-loop', get_post_type(), array_merge( [
				// Hide less than number.
				'hide' => ( 0 < max( 0, $args['number'] ) && $args['number'] < $counter ),
			], $args ) );
		}
		wp_reset_postdata();
		?>
	</nav>

	<footer class="rich-taxonomy-footer">
		<?php
		if ( $should_hide ) {
			rich_taxonomy_template( 'template-parts/rich-taxonomy/archive-block-toggle', $args['term']->taxonomy, $args );
		}
		?>
		<?php
		if ( $args['url'] ) {
			rich_taxonomy_template( 'template-parts/rich-taxonomy/archive-block-more', $args['term']->taxonomy, $args );
		}
		?>
	</footer>
</div>
