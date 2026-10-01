<?php
/**
 * "Tell us your story" band with background photo, used at the end of most pages.
 */
?>
<section class="cta">
	<div class="cta__img"><?php echo esen_img( esen_photo_id( 'cta' ), '100vw', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<div class="wrap reveal">
		<p class="eyebrow">Let's create together</p>
		<h2 class="display display--md">Tell us <em>your story.</em></h2>
		<p>Have a date in mind or a question about a session? We'd love to hear from you.</p>
		<a class="btn btn--light" href="<?php echo esc_url( esen_url( 'contact' ) ); ?>">Get in touch</a>
	</div>
</section>
