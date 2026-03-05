<?php
/**
 * Template for taxonomy archive page (taxonomy base URL).
 *
 * @package rich-taxonomy
 */

get_header();
?>

<main class="wp-block-group has-global-padding is-layout-constrained" id="wp--skip-link--target">
	<div class="wp-block-group alignfull has-global-padding is-layout-constrained" style="padding-top:var(--wp--preset--spacing--60, 1.5rem);padding-bottom:var(--wp--preset--spacing--60, 1.5rem)">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
