<?php
/**
 * Site-specific settings: contact details and which uploaded photo goes where.
 * Photo values are WordPress attachment IDs (Media Library).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'email'     => 'esen.photonl@gmail.com',
	'instagram' => 'esenphotography.nl',
	'location'  => 'Den Bosch, the Netherlands',

	// Filled in by tools/upload-photos.ps1 (see tools/photo-ids.json).
	'photos'    => array(
		'hero'      => 0,
		'menu'      => 0,
		'intro_a'   => 0,
		'intro_b'   => 0,
		'wedding'   => 0,
		'family'    => 0,
		'newborn'   => 0,
		'fashion'   => 0,
		'cta'       => 0,
		'about'     => 0,
		'about_2'   => 0,
		'contact'   => 0,
		'strip'     => array(),
		'instagram' => array(),
	),
);
