<?php
/**
 * Page not found.
 */

get_header();
?>

<section class="section page-plain page-404">
	<div class="wrap">
		<p class="eyebrow">Error 404</p>
		<h1 class="display display--md">This moment <em>wasn't captured.</em></h1>
		<p class="lead">The page you're looking for doesn't exist or has moved.</p>
		<p><a class="btn btn--solid" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to home</a></p>
	</div>
</section>

<?php
get_footer();
