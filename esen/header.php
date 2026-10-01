<?php
/**
 * Site header: logo, "Book a session", burger and full-screen menu.
 *
 * Templates that open with a full-bleed photo set $GLOBALS['esen_has_hero'] = true
 * before get_header(), so the header starts transparent over the image.
 */

$esen_has_hero = ! empty( $GLOBALS['esen_has_hero'] );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( $esen_has_hero ? 'has-hero' : 'no-hero' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>

<header class="header<?php echo $esen_has_hero ? '' : ' is-solid'; ?>" id="header">
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Esen Photography, home">
		<img src="<?php echo esc_url( esen_logo_url() ); ?>" alt="Esen Photography" width="250" height="100">
	</a>
	<div class="header__right">
		<a class="header__cta" href="<?php echo esc_url( esen_url( 'contact' ) ); ?>">Book a session</a>
		<button class="burger" id="burger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="menu"><span></span><span></span></button>
	</div>
</header>

<div class="menu" id="menu" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Menu">
	<button class="burger menu__close" id="menu-close" type="button" aria-label="Close menu"><span></span><span></span></button>
	<nav class="menu__nav" aria-label="Main">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ol class="menu__list">%3$s</ol>',
				'depth'          => 2,
			) );
		} else {
			?>
			<ol class="menu__list">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
				<li><a href="<?php echo esc_url( esen_url( 'gallery' ) ); ?>">Portfolio</a></li>
				<li><a href="<?php echo esc_url( esen_url( 'about' ) ); ?>">About us</a></li>
				<li><a href="<?php echo esc_url( esen_url( 'contact' ) ); ?>">Contact</a></li>
			</ol>
			<?php
		}
		?>
		<div class="menu__meta">
			<a href="mailto:<?php echo esc_attr( esen_config( 'email' ) ); ?>"><?php echo esc_html( esen_config( 'email' ) ); ?></a>
			<a href="https://instagram.com/<?php echo esc_attr( esen_config( 'instagram' ) ); ?>" target="_blank" rel="noopener">Instagram</a>
			<span>Den Bosch, NL</span>
		</div>
	</nav>
	<div class="menu__photo"><?php echo esen_img( esen_photo_id( 'menu' ), '45vw', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
</div>

<main id="main">
