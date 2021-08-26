<?php
/**
 * Get archive wrapper.
 *
 * @package rich-taxonomy
 * @since 1.0.0
 * @var array{hide: bool, toggle: string, more: string, number: int, url: string, query: WP_Query, term: WP_Term } $args
 */

$classes = [ 'rich-taxonomy-item' ];
if ( $args['hide'] ) {
	$classes[] = 'rich-taxonomy-item-hidden';
}
?>

<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<a class="rich-taxonomy-item-link" href="<?php the_permalink(); ?>">

		<?php if ( has_post_thumbnail() ) : ?>
		<figure class="rich-taxonomy-item-thumbnail">
			<?php
			the_post_thumbnail( 'post-thumbnail', [
				'alt'   => get_the_title(),
				'class' => 'rich-taxonomy-item-image',
			] );
			?>
		</figure>
		<?php endif; ?>
		<div class="rich-taxonomy-item-body">
			<span class="rich-taxonomy-item-title">
				<?php the_title(); ?>
			</span>
			<span class="rich-taxonomy-item-meta">
				<time class="rich-taxonomy-item-time"><?php the_time( get_option( 'date_format' ) ); ?></time>
			</span>
		</div>
	</a>

</div>
