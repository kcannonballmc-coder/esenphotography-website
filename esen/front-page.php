<?php
/**
 * Homepage.
 */

$GLOBALS['esen_has_hero'] = true;
get_header();

$esen_specialties = array(
	array( 'wedding', 'Wedding', 'Your day, told naturally, from quiet details to the celebration.' ),
	array( 'family', 'Family', 'Relaxed sessions for the people who matter most.' ),
	array( 'newborn', 'Newborn', 'Gentle, calm portraits of the very first days.' ),
	array( 'fashion', 'Fashion', 'Editorial portraits for brands, designers and creatives.' ),
);
?>

<section class="hero">
	<div class="hero__img"><?php echo esen_img( esen_photo_id( 'hero' ), '100vw', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => 'Couple walking hand in hand at golden hour' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<div class="hero__content wrap">
		<div class="hero__row">
			<div>
				<h1>
					<span class="eyebrow">Wedding, family &amp; newborn photographer in Den Bosch</span>
					<span class="display">Cherish every<br><em>moment,</em> forever.</span>
				</h1>
				<p class="hero__sub">Natural, heartfelt photography for life's most precious chapters. Based in Den Bosch ('s-Hertogenbosch), available across the Netherlands.</p>
				<a class="btn btn--light" href="<?php echo esc_url( esen_url( 'contact' ) ); ?>">Check availability</a>
			</div>
			<span class="hero__scroll" aria-hidden="true">Scroll</span>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap intro">
		<div class="intro__photos reveal">
			<div class="a"><?php echo esen_img( esen_photo_id( 'intro_a' ), '(max-width: 1000px) 80vw, 35vw', array( 'alt' => 'A couple in the city' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div class="b"><?php echo esen_img( esen_photo_id( 'intro_b' ), '(max-width: 1000px) 40vw, 18vw', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
		<div class="intro__text reveal">
			<p class="eyebrow">Meet us</p>
			<h2 class="h2">A love for photography, <em>a love for each other.</em></h2>
			<p class="lead">We're Esen &amp; Mouchammed, the husband-and-wife team behind Esen Photography. We met over ten years ago in our university's photography club, and what began as a shared hobby became a life together.</p>
			<p>Today we bring that same curiosity to every session: the first smile of a newborn, a family gathering, the quiet moments of a wedding day. We've learned that the best pictures are the ones you can feel.</p>
			<div class="intro__sign"><span>Esen &amp; Mouchammed</span><a class="link" href="<?php echo esc_url( esen_url( 'about' ) ); ?>">Our story</a></div>
		</div>
	</div>
</section>

<section class="section section--flush-top">
	<div class="wrap">
		<div class="spec-head reveal">
			<div>
				<p class="eyebrow">Our specialties</p>
				<h2 class="h2">Every chapter, <em>beautifully told.</em></h2>
			</div>
			<p class="lead">From intimate weddings to growing families, we turn your memories into timeless art, with warmth and a personal touch in every session.</p>
		</div>
		<div class="spec">
			<?php foreach ( $esen_specialties as $esen_i => $esen_spec ) : ?>
				<a class="card reveal" href="<?php echo esc_url( esen_url( 'gallery/' . $esen_spec[0] ) ); ?>">
					<?php echo esen_img( esen_photo_id( $esen_spec[0] ), '(max-width: 600px) 100vw, (max-width: 1000px) 50vw, 25vw', array( 'alt' => $esen_spec[1] . ' photography' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<div class="card__body">
						<span class="card__num">0<?php echo esc_html( $esen_i + 1 ); ?></span>
						<h3 class="card__title"><?php echo esc_html( $esen_spec[1] ); ?></h3>
						<p class="card__text"><?php echo esc_html( $esen_spec[2] ); ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
		<p class="spec-note reveal">Maternity sessions are available too. <a href="<?php echo esc_url( esen_url( 'contact' ) ); ?>">Just ask.</a></p>
	</div>
</section>

<section class="section quote">
	<div class="wrap reveal">
		<blockquote>&ldquo;Every photograph should tell a story, not just of a moment, but of the <em>emotions and memories</em> within it.&rdquo;</blockquote>
		<cite>Esen</cite>
	</div>
</section>

<section class="section">
	<div class="strip-head wrap reveal">
		<p class="eyebrow">Recent work</p>
		<h2 class="h2">Moments we've <em>loved capturing</em></h2>
	</div>
	<div class="strip">
		<?php foreach ( (array) esen_photo_id( 'strip' ) as $esen_id ) : ?>
			<figure class="reveal"><?php echo esen_img( $esen_id, '(max-width: 600px) 50vw, 33vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
		<?php endforeach; ?>
	</div>
	<div class="strip-cta reveal"><a class="btn" href="<?php echo esc_url( esen_url( 'gallery' ) ); ?>">View the portfolio</a></div>
</section>

<section class="section section--sand">
	<div class="wrap">
		<div class="reveal narrow">
			<p class="eyebrow">How it works</p>
			<h2 class="h2">Simple, personal, <em>and relaxed.</em></h2>
		</div>
		<div class="process">
			<div class="step reveal"><span class="step__num">i.</span><h3 class="h3">Say hello</h3><p>Tell us about your plans, your date and what matters to you. We'll get back to you with availability and options.</p></div>
			<div class="step reveal"><span class="step__num">ii.</span><h3 class="h3">Plan together</h3><p>We'll talk through locations, timing and the little details, so you can relax and simply enjoy the day.</p></div>
			<div class="step reveal"><span class="step__num">iii.</span><h3 class="h3">Relive it</h3><p>Receive a carefully edited gallery of your story, ready to share, print and keep forever.</p></div>
		</div>
	</div>
</section>

<?php get_template_part( 'parts/cta' ); ?>

<?php
get_footer();
