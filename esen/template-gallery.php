<?php
/**
 * Category gallery (any child page of "gallery"). Chosen automatically in functions.php.
 *
 * Photos come from the gallery/image blocks in the page content, so they can be
 * added, removed and reordered in the WordPress editor.
 */

$GLOBALS['esen_has_hero'] = true;
get_header();
the_post();

$esen_ids      = esen_content_image_ids();
$esen_parent   = wp_get_post_parent_id( get_the_ID() );
$esen_siblings = get_pages( array( 'parent' => $esen_parent, 'sort_column' => 'menu_order', 'post_status' => 'publish' ) );
$esen_index    = array_search( get_the_ID(), wp_list_pluck( $esen_siblings, 'ID' ), true );
$esen_next     = $esen_siblings ? $esen_siblings[ ( (int) $esen_index + 1 ) % count( $esen_siblings ) ] : null;

get_template_part( 'parts/page-hero', null, array(
	'eyebrow' => 'Portfolio',
	'title'   => esc_html( get_the_title() ),
	'text'    => get_the_excerpt(),
) );
?>

<section class="section section--tight">
	<div class="wrap">
		<?php $esen_intro = trim( esen_content_without_images() ); ?>
		<?php if ( $esen_intro ) : ?>
			<div class="prose prose--center reveal"><?php echo $esen_intro; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</div>

	<?php if ( $esen_ids ) : ?>
		<div class="masonry" data-lightbox-group>
			<?php foreach ( $esen_ids as $esen_id ) : ?>
				<?php
				$esen_full = wp_get_attachment_image_url( $esen_id, '2048x2048' );
				if ( ! $esen_full ) {
					$esen_full = wp_get_attachment_image_url( $esen_id, 'full' );
				}
				?>
				<a class="masonry__item reveal" href="<?php echo esc_url( $esen_full ); ?>" data-lightbox>
					<?php echo esen_img( $esen_id, '(max-width: 600px) 50vw, (max-width: 1100px) 50vw, 33vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="empty wrap">New photos coming soon.</p>
	<?php endif; ?>

	<nav class="gallery-nav wrap" aria-label="Portfolio">
		<a class="link" href="<?php echo esc_url( get_permalink( $esen_parent ) ); ?>">All stories</a>
		<?php if ( $esen_next && $esen_next->ID !== get_the_ID() ) : ?>
			<a class="gallery-nav__next" href="<?php echo esc_url( get_permalink( $esen_next ) ); ?>">
				<span class="eyebrow">Next story</span>
				<span class="h2"><?php echo esc_html( $esen_next->post_title ); ?> <em>&rarr;</em></span>
			</a>
		<?php endif; ?>
	</nav>
</section>

<?php
get_template_part( 'parts/cta' );
get_footer();
