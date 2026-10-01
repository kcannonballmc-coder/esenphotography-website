<?php
/**
 * Contact page (slug "contact"): photo + enquiry form. Intro text is the page content.
 */

$GLOBALS['esen_hide_instagram'] = true;
get_header();
the_post();

$esen_sent  = isset( $_GET['sent'] ); // phpcs:ignore WordPress.Security.NonceVerification
$esen_error = isset( $_GET['error'] ) ? sanitize_key( $_GET['error'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
?>

<section class="contact">
	<div class="contact__photo"><?php echo esen_img( esen_photo_id( 'contact' ), '(max-width: 1000px) 100vw, 45vw', array( 'loading' => 'eager', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>

	<div class="contact__body">
		<p class="eyebrow">Contact</p>
		<h1 class="display display--md">Let's tell <em>your story.</em></h1>
		<div class="prose"><?php the_content(); ?></div>

		<div id="form" class="contact__form-wrap">
			<?php if ( $esen_sent ) : ?>
				<div class="notice notice--ok" role="status">
					<h2 class="h3">Thank you, <em>we've got your message.</em></h2>
					<p>We'll get back to you as soon as possible, usually within two working days.</p>
				</div>
			<?php else : ?>
				<?php if ( $esen_error ) : ?>
					<div class="notice notice--error" role="alert">
						<?php if ( 'mail' === $esen_error ) : ?>
							Sorry, your message could not be sent. Please email us directly at <a href="mailto:<?php echo esc_attr( esen_config( 'email' ) ); ?>"><?php echo esc_html( esen_config( 'email' ) ); ?></a>.
						<?php else : ?>
							Please fill in your name, a valid email address, the type of session and a message.
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="esen_contact">
					<?php wp_nonce_field( 'esen_contact', 'esen_contact_nonce' ); ?>
					<p class="form__hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

					<div class="form__row">
						<label class="field"><span>Name *</span><input type="text" name="name" required autocomplete="name"></label>
						<label class="field"><span>Email *</span><input type="email" name="email" required autocomplete="email"></label>
					</div>
					<div class="form__row">
						<label class="field"><span>Event date</span><input type="date" name="date"></label>
						<label class="field"><span>Location</span><input type="text" name="location" placeholder="City or venue"></label>
					</div>
					<fieldset class="field field--chips">
						<legend>Type of session *</legend>
						<?php foreach ( esen_contact_session_types() as $esen_i => $esen_type ) : ?>
							<label class="chip"><input type="radio" name="type" value="<?php echo esc_attr( $esen_type ); ?>" <?php echo 0 === $esen_i ? 'required' : ''; ?>><span><?php echo esc_html( $esen_type ); ?></span></label>
						<?php endforeach; ?>
					</fieldset>
					<label class="field"><span>Message *</span><textarea name="message" rows="5" required placeholder="Tell us a little about your plans"></textarea></label>
					<button class="btn btn--solid" type="submit">Send message</button>
				</form>
			<?php endif; ?>
		</div>

		<div class="contact__details">
			<div><span class="eyebrow">Email</span><a href="mailto:<?php echo esc_attr( esen_config( 'email' ) ); ?>"><?php echo esc_html( esen_config( 'email' ) ); ?></a></div>
			<div><span class="eyebrow">Instagram</span><a href="https://instagram.com/<?php echo esc_attr( esen_config( 'instagram' ) ); ?>" target="_blank" rel="noopener">@<?php echo esc_html( esen_config( 'instagram' ) ); ?></a></div>
			<div><span class="eyebrow">Based in</span><span><?php echo esc_html( esen_config( 'location' ) ); ?></span></div>
		</div>
	</div>
</section>

<?php
get_footer();
