<?php
/**
 * Default page.
 */

get_header();
the_post();
?>

<section class="section page-plain">
	<div class="wrap">
		<header class="page-plain__head reveal">
			<h1 class="display display--md"><?php the_title(); ?></h1>
		</header>
		<div class="prose reveal"><?php the_content(); ?></div>
	</div>
</section>

<?php
get_footer();
