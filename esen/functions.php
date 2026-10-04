<?php
/**
 * Esen theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ESEN_VERSION', '1.0.0' );

/**
 * Site settings from inc/config.php (contact details, photo IDs).
 *
 * @param string $key Optional top-level key.
 * @return mixed
 */
function esen_config( $key = null ) {
	static $config = null;
	if ( null === $config ) {
		$config = require get_template_directory() . '/inc/config.php';
	}
	return null === $key ? $config : ( $config[ $key ] ?? null );
}

/**
 * Attachment ID for a named photo slot (see inc/config.php).
 */
function esen_photo_id( $slot ) {
	$photos = esen_config( 'photos' );
	return isset( $photos[ $slot ] ) ? $photos[ $slot ] : 0;
}

/**
 * Responsive <img> for an attachment. Returns '' when the attachment is missing.
 */
function esen_img( $id, $sizes = '100vw', $attrs = array() ) {
	if ( ! $id || ! wp_attachment_is_image( $id ) ) {
		return '';
	}
	return wp_get_attachment_image( $id, 'full', false, array_merge( array( 'sizes' => $sizes, 'loading' => 'lazy', 'decoding' => 'async' ), $attrs ) );
}

/**
 * URL of a page found by its path (e.g. "gallery/wedding"), falling back to home.
 */
function esen_url( $path ) {
	$page = get_page_by_path( $path );
	return $page ? get_permalink( $page ) : home_url( '/' . trim( $path, '/' ) . '/' );
}

function esen_logo_url() {
	return get_template_directory_uri() . '/assets/img/logo.png';
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_post_type_support( 'page', 'excerpt' ); // Short description shown on portfolio tiles and gallery headers.
	register_nav_menus( array( 'primary' => __( 'Main menu', 'esen' ) ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'esen-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap', array(), null );
	wp_enqueue_style( 'esen', get_template_directory_uri() . '/assets/css/main.css', array( 'esen-fonts' ), filemtime( get_template_directory() . '/assets/css/main.css' ) );
	wp_enqueue_script( 'esen', get_template_directory_uri() . '/assets/js/main.js', array(), filemtime( get_template_directory() . '/assets/js/main.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );

	// This theme does not use block styles on the front end apart from basic content.
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}, 20 );

add_filter( 'wp_resource_hints', function ( $urls, $type ) {
	if ( 'preconnect' === $type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

// Mark JS as available before first paint so reveal animations don't flash.
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('js')</script>\n";
}, 1 );

/**
 * Image IDs used in a page's content (gallery and image blocks), in order.
 */
function esen_content_image_ids( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	$ids  = array();
	$walk = function ( $blocks ) use ( &$walk, &$ids ) {
		foreach ( $blocks as $block ) {
			if ( 'core/image' === $block['blockName'] && ! empty( $block['attrs']['id'] ) ) {
				$ids[] = (int) $block['attrs']['id'];
			}
			if ( 'core/gallery' === $block['blockName'] && ! empty( $block['attrs']['ids'] ) ) {
				$ids = array_merge( $ids, array_map( 'intval', $block['attrs']['ids'] ) );
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'] );
			}
		}
	};
	$walk( parse_blocks( $post->post_content ) );
	return array_values( array_unique( $ids ) );
}

/**
 * Page content without image/gallery blocks (those are rendered by the gallery template).
 */
function esen_content_without_images( $post = null ) {
	$post   = get_post( $post );
	$output = '';
	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( in_array( $block['blockName'], array( 'core/gallery', 'core/image' ), true ) ) {
			continue;
		}
		$output .= render_block( $block );
	}
	return $output;
}

/**
 * Pages under the Portfolio page ("gallery") use the gallery layout.
 */
add_filter( 'template_include', function ( $template ) {
	if ( is_page() ) {
		$parent = wp_get_post_parent_id( get_queried_object_id() );
		if ( $parent && 'gallery' === get_post_field( 'post_name', $parent ) ) {
			return get_template_directory() . '/template-gallery.php';
		}
	}
	return $template;
} );

require get_template_directory() . '/inc/contact-form.php';
require get_template_directory() . '/inc/seo.php';
