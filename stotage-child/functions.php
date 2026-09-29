<?php
/**
 * Stotage Child theme functions.
 *
 * Add custom PHP (hooks, filters, small features) in this file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the parent theme stylesheet, then this child theme's stylesheet.
 * The child version uses the file's modified time so browsers pick up changes right after a deploy.
 */
function stotage_child_enqueue_styles() {
	wp_enqueue_style(
		'stotage-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( get_template() )->get( 'Version' )
	);

	wp_enqueue_style(
		'stotage-child-style',
		get_stylesheet_uri(),
		array( 'stotage-parent-style' ),
		filemtime( get_stylesheet_directory() . '/style.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'stotage_child_enqueue_styles', 20 );
