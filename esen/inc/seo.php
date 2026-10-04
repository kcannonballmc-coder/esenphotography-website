<?php
/**
 * SEO: per-page titles, descriptions and headings, social tags, structured data,
 * and a sitemap limited to real pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Title / description / H1 per page, keyed by page path ('' = homepage).
 * "h1" may contain <em> and <br>.
 */
function esen_seo_map() {
	return array(
		''                => array(
			'title'       => 'Wedding & Family Photographer Den Bosch · Esen Photography',
			'description' => 'Esen Photography is a husband-and-wife team in Den Bosch (\'s-Hertogenbosch) for natural, heartfelt wedding, family, newborn and fashion photography.',
		),
		'gallery'         => array(
			'title'       => 'Portfolio: Wedding, Family & Newborn Photos · Esen Photography',
			'description' => 'Browse the Esen Photography portfolio: wedding, family, newborn and fashion photography by Esen & Mouchammed, based in Den Bosch, the Netherlands.',
			'h1'          => 'Our photography <em>portfolio</em>',
		),
		'gallery/wedding' => array(
			'title'       => 'Wedding Photographer in Den Bosch · Esen Photography',
			'description' => 'Wedding photographer in Den Bosch. Esen & Mouchammed capture your day naturally, from quiet details to the celebration. View our wedding photos and check availability.',
			'h1'          => 'Wedding photographer <em>in Den Bosch</em>',
		),
		'gallery/family'  => array(
			'title'       => 'Family Photographer in Den Bosch · Esen Photography',
			'description' => 'Family photographer in Den Bosch. Relaxed, playful sessions without stiff poses, for the people who matter most. View our family photos and book your session.',
			'h1'          => 'Family photographer <em>in Den Bosch</em>',
		),
		'gallery/newborn' => array(
			'title'       => 'Newborn Photographer in Den Bosch · Esen Photography',
			'description' => 'Newborn photographer in Den Bosch. Calm, gentle sessions at your baby\'s pace to treasure the very first days. View our newborn photos and book your session.',
			'h1'          => 'Newborn photographer <em>in Den Bosch</em>',
		),
		'gallery/fashion' => array(
			'title'       => 'Fashion Photographer in Den Bosch · Esen Photography',
			'description' => 'Fashion photographer in Den Bosch for brands, designers and creatives. Natural, elegant editorial and lookbook photography. View our work and get in touch.',
			'h1'          => 'Fashion photographer <em>in Den Bosch</em>',
		),
		'about'           => array(
			'title'       => 'About Esen & Mouchammed · Photographers in Den Bosch',
			'description' => 'Meet Esen & Mouchammed, the husband-and-wife photographers behind Esen Photography in Den Bosch. Our story, our approach and what makes a photo you can feel.',
		),
		'contact'         => array(
			'title'       => 'Contact & Bookings · Esen Photography, Den Bosch',
			'description' => 'Contact Esen Photography in Den Bosch to book a wedding, family, newborn, maternity or fashion session. Send us your date and plans and we\'ll reply soon.',
		),
	);
}

/**
 * SEO values for the page being viewed.
 *
 * @param string $key Optional: title, description or h1.
 * @return mixed Array of values, a single value, or '' when not set.
 */
function esen_seo( $key = null ) {
	$map  = esen_seo_map();
	$path = is_front_page() ? '' : ( is_page() ? get_page_uri( get_queried_object_id() ) : null );
	$data = ( null !== $path && isset( $map[ $path ] ) ) ? $map[ $path ] : array();
	return null === $key ? $data : ( $data[ $key ] ?? '' );
}

add_filter( 'pre_get_document_title', function ( $title ) {
	$custom = esen_seo( 'title' );
	return $custom ? $custom : $title;
} );

add_filter( 'document_title_separator', function () {
	return '·';
} );

/**
 * Meta description, Open Graph / Twitter tags and favicon.
 */
add_action( 'wp_head', function () {
	$description = esen_seo( 'description' );
	if ( ! $description && is_singular() ) {
		$description = wp_strip_all_tags( get_the_excerpt() );
	}
	if ( ! $description ) {
		$description = 'Wedding, family, newborn and fashion photography by Esen & Mouchammed in Den Bosch.';
	}

	$image_id = ( is_singular() && has_post_thumbnail() ) ? get_post_thumbnail_id() : esen_photo_id( 'hero' );
	$image    = wp_get_attachment_image_src( $image_id, 'large' );
	$url      = is_singular() ? get_permalink() : home_url( '/' );

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:site_name" content="Esen Photography">' . "\n" );
	printf( '<meta property="og:type" content="website">' . "\n" );
	printf( '<meta property="og:locale" content="en_US">' . "\n" );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image[0] ) );
		printf( '<meta property="og:image:width" content="%d">' . "\n", (int) $image[1] );
		printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $image[2] );
	}
	printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );

	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( get_template_directory_uri() . '/assets/img/favicon.svg' ) );
	}
}, 5 );

/**
 * Structured data: the business on every page, breadcrumbs on inner pages.
 */
add_action( 'wp_head', function () {
	$home  = home_url( '/' );
	$logo  = esen_logo_url();
	$photo = wp_get_attachment_image_url( esen_photo_id( 'hero' ), 'large' );

	$graph = array(
		array(
			'@type'       => 'ProfessionalService',
			'@id'         => $home . '#business',
			'name'        => 'Esen Photography',
			'description' => 'Wedding, family, newborn and fashion photography by the husband-and-wife team Esen & Mouchammed.',
			'url'         => $home,
			'email'       => esen_config( 'email' ),
			'logo'        => $logo,
			'image'       => $photo ? $photo : $logo,
			'address'     => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => '\'s-Hertogenbosch',
				'addressRegion'   => 'Noord-Brabant',
				'addressCountry'  => 'NL',
			),
			'areaServed'  => array(
				array( '@type' => 'City', 'name' => '\'s-Hertogenbosch' ),
				array( '@type' => 'Country', 'name' => 'Netherlands' ),
			),
			'knowsAbout'  => array( 'Wedding photography', 'Family photography', 'Newborn photography', 'Maternity photography', 'Fashion photography' ),
			'sameAs'      => array( 'https://www.instagram.com/' . esen_config( 'instagram' ) . '/' ),
		),
		array(
			'@type'     => 'WebSite',
			'@id'       => $home . '#website',
			'url'       => $home,
			'name'      => 'Esen Photography',
			'publisher' => array( '@id' => $home . '#business' ),
			'inLanguage' => 'en',
		),
	);

	if ( is_page() && ! is_front_page() ) {
		$items    = array( array( 'name' => 'Home', 'url' => $home ) );
		$page_id  = get_queried_object_id();
		$trail    = array_reverse( get_post_ancestors( $page_id ) );
		$trail[]  = $page_id;
		foreach ( $trail as $id ) {
			$items[] = array( 'name' => get_the_title( $id ), 'url' => get_permalink( $id ) );
		}
		$list = array();
		foreach ( $items as $i => $item ) {
			$list[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $item['name'] ), 'item' => $item['url'] );
		}
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
	}

	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}, 6 );

/**
 * Sitemap: pages only (no posts, theme templates, categories, tags or authors).
 */
add_filter( 'wp_sitemaps_post_types', function ( $post_types ) {
	return isset( $post_types['page'] ) ? array( 'page' => $post_types['page'] ) : array();
} );
add_filter( 'wp_sitemaps_taxonomies', '__return_empty_array' );
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

// Draft-like or utility pages stay out of the sitemap too.
add_filter( 'wp_sitemaps_posts_query_args', function ( $args ) {
	$args['post_status'] = 'publish';
	return $args;
} );

/**
 * Keep blog, author, category, tag and date archives out of Google: the site has no blog.
 */
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_home() || is_archive() || is_search() || ( is_singular() && ! is_page() ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
} );
