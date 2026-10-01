<?php
/**
 * Site footer: Instagram row (optional), links and contact details.
 */

$esen_instagram = esen_config( 'instagram' );
$esen_email     = esen_config( 'email' );
?>
</main>

<?php if ( empty( $GLOBALS['esen_hide_instagram'] ) ) : ?>
<section class="insta" aria-label="Instagram">
	<div class="insta__head wrap">
		<h2 class="h3">Follow along <em>@<?php echo esc_html( $esen_instagram ); ?></em></h2>
		<a class="link" href="https://instagram.com/<?php echo esc_attr( $esen_instagram ); ?>" target="_blank" rel="noopener">Instagram</a>
	</div>
	<div class="insta__grid">
		<?php foreach ( (array) esen_photo_id( 'instagram' ) as $esen_id ) : ?>
			<a href="https://instagram.com/<?php echo esc_attr( $esen_instagram ); ?>" target="_blank" rel="noopener" aria-label="Esen Photography on Instagram"><?php echo esen_img( $esen_id, '(max-width: 1000px) 33vw, 17vw', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<footer class="footer">
	<div class="wrap">
		<div class="footer__grid">
			<div>
				<a class="footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( esen_logo_url() ); ?>" alt="Esen Photography" width="250" height="100" loading="lazy"></a>
				<p class="footer__tag">Natural &amp; heartfelt photography in Den Bosch.</p>
			</div>
			<div>
				<h2 class="footer__title">Explore</h2>
				<ul>
					<li><a href="<?php echo esc_url( esen_url( 'gallery' ) ); ?>">Portfolio</a></li>
					<li><a href="<?php echo esc_url( esen_url( 'about' ) ); ?>">About us</a></li>
					<li><a href="<?php echo esc_url( esen_url( 'contact' ) ); ?>">Contact</a></li>
				</ul>
			</div>
			<div>
				<h2 class="footer__title">Portfolio</h2>
				<ul>
					<li><a href="<?php echo esc_url( esen_url( 'gallery/wedding' ) ); ?>">Wedding</a></li>
					<li><a href="<?php echo esc_url( esen_url( 'gallery/family' ) ); ?>">Family</a></li>
					<li><a href="<?php echo esc_url( esen_url( 'gallery/newborn' ) ); ?>">Newborn</a></li>
					<li><a href="<?php echo esc_url( esen_url( 'gallery/fashion' ) ); ?>">Fashion</a></li>
				</ul>
			</div>
			<div>
				<h2 class="footer__title">Say hello</h2>
				<ul>
					<li><a href="mailto:<?php echo esc_attr( $esen_email ); ?>"><?php echo esc_html( $esen_email ); ?></a></li>
					<li><a href="https://instagram.com/<?php echo esc_attr( $esen_instagram ); ?>" target="_blank" rel="noopener">@<?php echo esc_html( $esen_instagram ); ?></a></li>
					<li><?php echo esc_html( esen_config( 'location' ) ); ?></li>
				</ul>
			</div>
		</div>
		<div class="footer__bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Esen Photography</span>
			<span>Wedding · Family · Newborn · Fashion</span>
		</div>
	</div>
</footer>

<div class="lightbox" id="lightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Photo viewer">
	<button class="lightbox__close" type="button" aria-label="Close"><span></span><span></span></button>
	<button class="lightbox__nav lightbox__prev" type="button" aria-label="Previous photo">&#8592;</button>
	<figure class="lightbox__figure"><img alt=""></figure>
	<button class="lightbox__nav lightbox__next" type="button" aria-label="Next photo">&#8594;</button>
	<span class="lightbox__count"></span>
</div>

<?php wp_footer(); ?>
</body>
</html>
