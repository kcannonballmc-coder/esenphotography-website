<?php
/**
 * Full-bleed page header with the page's featured image.
 *
 * Args: eyebrow (string), title (HTML allowed: <em>), text (string).
 */

$esen_args = wp_parse_args( $args ?? array(), array(
	'eyebrow' => '',
	'title'   => esc_html( get_the_title() ),
	'text'    => '',
	'image'   => get_post_thumbnail_id(),
) );
?>
<section class="page-hero">
	<div class="page-hero__img"><?php echo esen_img( $esen_args['image'], '100vw', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<div class="page-hero__content wrap">
		<?php if ( $esen_args['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $esen_args['eyebrow'] ); ?></p><?php endif; ?>
		<h1 class="display display--md"><?php echo wp_kses( $esen_args['title'], array( 'em' => array(), 'br' => array() ) ); ?></h1>
		<?php if ( $esen_args['text'] ) : ?><p class="page-hero__text"><?php echo esc_html( $esen_args['text'] ); ?></p><?php endif; ?>
	</div>
</section>
