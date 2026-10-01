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
		'hero'      => 9512,
		'menu'      => 9498,
		'intro_a'   => 9517,
		'intro_b'   => 9497,
		'wedding'   => 9514,
		'family'    => 9501,
		'newborn'   => 9518,
		'fashion'   => 9510,
		'cta'       => 9513,
		'about'     => 9499,
		'about_2'   => 9550,
		'contact'   => 9549,
		'strip'     => array( 9509, 9524, 9508, 9543, 9533, 9502, 9539, 9505, 9521 ),
		'instagram' => array( 9519, 9540, 9507, 9496, 9538, 9525 ),
	),
);
