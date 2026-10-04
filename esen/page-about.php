<?php
/**
 * About page (slug "about"). The story text is the page content.
 */

$GLOBALS['esen_has_hero'] = true;
get_header();
the_post();

get_template_part( 'parts/page-hero', null, array(
	'eyebrow' => 'About us',
	'title'   => 'Meet <em>Esen &amp; Mouchammed</em>',
	'text'    => 'Wedding, family and newborn photographers based in Den Bosch (\'s-Hertogenbosch).',
) );
?>

<section class="section">
	<div class="wrap about">
		<div class="about__photo reveal">
			<?php echo esen_img( esen_photo_id( 'about' ), '(max-width: 1000px) 100vw, 40vw', array( 'alt' => 'Esen Photography' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<div class="about__text reveal">
			<p class="eyebrow">Our story</p>
			<h2 class="h2">A love for photography, <em>a love for each other.</em></h2>
			<div class="prose"><?php the_content(); ?></div>
			<p class="intro__sign"><span>Esen &amp; Mouchammed</span></p>
		</div>
	</div>
</section>

<section class="section quote">
	<div class="wrap reveal">
		<blockquote>&ldquo;Our approach is natural and timeless, capturing the <em>little details</em> that make your story truly unique.&rdquo;</blockquote>
		<cite>Esen</cite>
	</div>
</section>

<section class="section">
	<div class="wrap values">
		<div class="values__photo reveal"><?php echo esen_img( esen_photo_id( 'about_2' ), '(max-width: 1000px) 100vw, 45vw', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<div class="reveal">
			<p class="eyebrow">What we believe</p>
			<h2 class="h2">The best pictures are <em>the ones you can feel.</em></h2>
			<div class="values__list">
				<div><h3 class="h3">Natural</h3><p>No stiff poses. We guide you gently and let real moments happen.</p></div>
				<div><h3 class="h3">Timeless</h3><p>Honest colours and classic compositions that still feel right in twenty years.</p></div>
				<div><h3 class="h3">Personal</h3><p>Two photographers, one team. We take the time to get to know you and your story.</p></div>
			</div>
		</div>
	</div>
</section>

<?php
get_template_part( 'parts/cta' );
get_footer();
