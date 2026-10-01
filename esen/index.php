<?php
/**
 * Fallback for blog/archive views.
 */

get_header();
?>

<section class="section page-plain">
	<div class="wrap">
		<header class="page-plain__head">
			<h1 class="display display--md"><?php echo is_singular() ? esc_html( get_the_title() ) : esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article class="prose post-item">
					<?php if ( is_singular() ) : ?>
						<?php the_content(); ?>
					<?php else : ?>
						<h2 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php the_excerpt(); ?>
					<?php endif; ?>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p>Nothing here yet.</p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
