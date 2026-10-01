<?php
/**
 * Contact form: rendered by template-contact.php, submitted to admin-post.php,
 * delivered with wp_mail() to the address in inc/config.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function esen_contact_session_types() {
	return array( 'Wedding', 'Family', 'Newborn', 'Maternity', 'Fashion', 'Something else' );
}

function esen_handle_contact() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/contact/' );
	$back = remove_query_arg( array( 'sent', 'error' ), $back );

	if ( ! isset( $_POST['esen_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['esen_contact_nonce'] ) ), 'esen_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'expired', $back ) . '#form' );
		exit;
	}

	// Honeypot: real visitors never fill this hidden field.
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', '1', $back ) . '#form' );
		exit;
	}

	$name     = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$date     = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );
	$location = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
	$type     = sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) );
	$message  = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( ! $name || ! is_email( $email ) || ! $message || ! in_array( $type, esen_contact_session_types(), true ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'missing', $back ) . '#form' );
		exit;
	}

	$body = implode( "\n", array(
		'Name: ' . $name,
		'Email: ' . $email,
		'Session: ' . $type,
		'Date: ' . ( $date ? $date : '-' ),
		'Location: ' . ( $location ? $location : '-' ),
		'',
		'Message:',
		$message,
		'',
		'-- ',
		'Sent from the contact form on ' . home_url( '/' ),
	) );

	$sent = wp_mail(
		esen_config( 'email' ),
		sprintf( 'New %s enquiry from %s', strtolower( $type ), $name ),
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);

	wp_safe_redirect( add_query_arg( $sent ? 'sent' : 'error', $sent ? '1' : 'mail', $back ) . '#form' );
	exit;
}
add_action( 'admin_post_nopriv_esen_contact', 'esen_handle_contact' );
add_action( 'admin_post_esen_contact', 'esen_handle_contact' );
