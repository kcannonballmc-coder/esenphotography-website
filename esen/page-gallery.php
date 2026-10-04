<?php
/**
 * Portfolio overview (page slug "gallery"): one tile per child page.
 */

$GLOBALS['esen_has_hero'] = true;
get_header();
the_post();

$esen_categories = get_pages( array(
	'parent'      => get_the_ID(),
	'sort_column' => 'menu_order',
	'post_status' => 'publish',
) );

get_template_part( 'parts/page-hero', null, array(
	'eyebrow' => 'Portfolio',
	'title'   => esen_seo( 'h1' ),
	'text'    => 'Weddings, families, newborns and fashion: a selection of our favourite work.',
) );
?>

<section class="section">
	<div class="wrap">
		<?php $esen_intro = trim( esen_content_without_images() ); ?>
		<?php if ( $esen_intro ) : ?>
			<div class="prose prose--center reveal"><?php echo $esen_intro; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>

		<div class="portfolio">
			<?php foreach ( $esen_categories as $esen_i => $esen_cat ) : ?>
				<?php $esen_count = count( esen_content_image_ids( $esen_cat ) ); ?>
				<a class="portfolio__item reveal" href="<?php echo esc_url( get_permalink( $esen_cat ) ); ?>">
					<div class="portfolio__img"><?php echo esen_img( get_post_thumbnail_id( $esen_cat ), '(max-width: 800px) 100vw, 50vw', array( 'alt' => $esen_cat->post_title . ' photography' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<div class="portfolio__meta">
						<span class="card__num">0<?php echo esc_html( $esen_i + 1 ); ?></span>
						<h2 class="h2"><?php echo esc_html( $esen_cat->post_title ); ?></h2>
						<?php if ( $esen_cat->post_excerpt ) : ?><p><?php echo esc_html( $esen_cat->post_excerpt ); ?></p><?php endif; ?>
						<span class="link"><?php echo $esen_count ? esc_html( sprintf( 'View %d photos', $esen_count ) ) : 'View gallery'; ?></span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'parts/cta' );
get_footer();
