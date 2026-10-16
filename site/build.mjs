/**
 * Builds the static Esen Photography website into ../_site.
 *
 *   node site/build.mjs
 *
 * Environment variables (optional):
 *   BASE_PATH      URL prefix when not served from a domain root, e.g. "/esenphotography-website"
 *   CUSTOM_DOMAIN  writes a CNAME file for GitHub Pages, e.g. "esenphotography.com"
 *
 * Content lives in site/data/*.json, styles and scripts in site/assets, photos in site/photos.
 */
import { readFileSync, writeFileSync, mkdirSync, rmSync, cpSync, existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const SRC = dirname( fileURLToPath( import.meta.url ) );
const OUT = join( SRC, '..', '_site' );
const BASE = ( process.env.BASE_PATH || '' ).replace( /\/$/, '' );
const DOMAIN = process.env.CUSTOM_DOMAIN || '';

const readJson = ( file ) => JSON.parse( readFileSync( join( SRC, 'data', file ), 'utf8' ).replace( /^﻿/, '' ) );
const site = readJson( 'site.json' );
const altText = readJson( 'alt.json' );
const photoList = readJson( 'photos.json' );

// ---------- helpers ----------

const esc = ( s = '' ) => String( s ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
const url = ( path = '/' ) => BASE + path;              // link inside the site
const abs = ( path = '/' ) => site.url + path;          // full URL for canonical / sitemap / schema
const year = new Date().getFullYear();

const photos = new Map( photoList.map( ( p ) => [ p.key, p ] ) );
function photo( key ) {
	const p = photos.get( key );
	if ( ! p ) {
		throw new Error( `Unknown photo key: ${ key }` );
	}
	return p;
}
const smSize = ( p ) => {
	const scale = Math.min( 1, 800 / Math.max( p.width, p.height ) );
	return [ Math.round( p.width * scale ), Math.round( p.height * scale ) ];
};
const photoUrl = ( p, size = 'lg' ) => url( `/photos/${ size }/${ p.name }.jpg` );

/** Responsive <img>. alt: undefined = the photo's description, '' = decorative. */
function img( key, sizes = '100vw', { eager = false, alt, cls = '' } = {} ) {
	const p = photo( key );
	const [ smW ] = smSize( p );
	const altValue = alt === undefined ? ( altText[ p.key ] || '' ) : alt;
	return `<img src="${ photoUrl( p ) }" srcset="${ photoUrl( p, 'sm' ) } ${ smW }w, ${ photoUrl( p ) } ${ p.width }w" sizes="${ sizes }" width="${ p.width }" height="${ p.height }" alt="${ esc( altValue ) }"${ cls ? ` class="${ cls }"` : '' }${ eager ? ' fetchpriority="high"' : ' loading="lazy"' } decoding="async">`;
}

function galleryPhotos( gallery ) {
	const lead = gallery.first.map( photo );
	const leadKeys = new Set( lead.map( ( p ) => p.key ) );
	return [ ...lead, ...photoList.filter( ( p ) => p.category === gallery.slug && ! leadKeys.has( p.key ) ) ];
}

const pages = []; // { path, html, sitemap }
function addPage( path, html, sitemap = true ) {
	pages.push( { path, html, sitemap } );
}

// ---------- layout ----------

const nav = [
	{ title: 'Home', path: '/' },
	{ title: 'Portfolio', path: '/portfolio/', children: site.galleries.map( ( g ) => ( { title: g.title, path: `/portfolio/${ g.slug }/` } ) ) },
	{ title: 'About us', path: '/about/' },
	{ title: 'Contact', path: '/contact/' },
];

function schema( path, crumbs ) {
	const home = abs( '/' );
	const graph = [
		{
			'@type': 'ProfessionalService',
			'@id': `${ home }#business`,
			name: site.name,
			description: 'Wedding, family, newborn and fashion photography by the husband-and-wife team Esen & Mouchammed.',
			url: home,
			email: site.email,
			logo: abs( '/assets/img/logo.png' ),
			image: abs( `/photos/lg/${ photo( site.photos.hero ).name }.jpg` ),
			address: { '@type': 'PostalAddress', addressLocality: "'s-Hertogenbosch", addressRegion: 'Noord-Brabant', addressCountry: 'NL' },
			areaServed: [ { '@type': 'City', name: "'s-Hertogenbosch" }, { '@type': 'Country', name: 'Netherlands' } ],
			knowsAbout: [ 'Wedding photography', 'Family photography', 'Newborn photography', 'Maternity photography', 'Fashion photography' ],
			sameAs: [ `https://www.instagram.com/${ site.instagram }/` ],
		},
		{ '@type': 'WebSite', '@id': `${ home }#website`, url: home, name: site.name, inLanguage: 'en', publisher: { '@id': `${ home }#business` } },
	];
	if ( crumbs ) {
		graph.push( {
			'@type': 'BreadcrumbList',
			itemListElement: [ { name: 'Home', path: '/' }, ...crumbs ].map( ( c, i ) => ( { '@type': 'ListItem', position: i + 1, name: c.name, item: abs( c.path ) } ) ),
		} );
	}
	return JSON.stringify( { '@context': 'https://schema.org', '@graph': graph } ).replace( /</g, '\\u003c' );
}

function layout( { path, title, description, body, hasHero = false, hideInstagram = false, ogImage = site.photos.hero, crumbs = null, noindex = false } ) {
	const og = photo( ogImage );
	const menuItems = nav.map( ( item ) => {
		const current = item.path === path ? ' class="current-menu-item"' : '';
		const sub = item.children ? `<ul class="sub-menu">${ item.children.map( ( c ) => `<li><a href="${ url( c.path ) }">${ c.title }</a></li>` ).join( '' ) }</ul>` : '';
		return `<li${ current }><a href="${ url( item.path ) }">${ item.title }</a>${ sub }</li>`;
	} ).join( '\n\t\t\t\t' );

	const instagram = hideInstagram ? '' : `
<section class="insta" aria-label="Instagram">
	<div class="insta__head wrap">
		<h2 class="h3">Follow along <em>@${ site.instagram }</em></h2>
		<a class="link" href="https://instagram.com/${ site.instagram }" target="_blank" rel="noopener">Instagram</a>
	</div>
	<div class="insta__grid">
		${ site.photos.instagram.map( ( k ) => `<a href="https://instagram.com/${ site.instagram }" target="_blank" rel="noopener" aria-label="Esen Photography on Instagram">${ img( k, '(max-width: 1000px) 33vw, 17vw', { alt: '' } ) }</a>` ).join( '\n\t\t' ) }
	</div>
</section>`;

	return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${ esc( title ) }</title>
<meta name="description" content="${ esc( description ) }">
${ noindex ? '<meta name="robots" content="noindex, follow">' : '<meta name="robots" content="max-image-preview:large">' }
<link rel="canonical" href="${ abs( path ) }">
<meta property="og:site_name" content="${ site.name }">
<meta property="og:type" content="website">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="${ esc( title ) }">
<meta property="og:description" content="${ esc( description ) }">
<meta property="og:url" content="${ abs( path ) }">
<meta property="og:image" content="${ abs( `/photos/lg/${ og.name }.jpg` ) }">
<meta property="og:image:width" content="${ og.width }">
<meta property="og:image:height" content="${ og.height }">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="${ url( '/assets/img/favicon.svg' ) }" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500&display=swap">
<link rel="stylesheet" href="${ url( '/assets/css/main.css' ) }?v=${ BUILD_ID }">
<script>document.documentElement.classList.add('js')</script>
<script src="${ url( '/assets/js/main.js' ) }?v=${ BUILD_ID }" defer></script>
<script type="application/ld+json">${ schema( path, crumbs ) }</script>
</head>
<body class="${ hasHero ? 'has-hero' : 'no-hero' }">
<a class="skip-link" href="#main">Skip to content</a>

<header class="header${ hasHero ? '' : ' is-solid' }" id="header">
	<a class="logo" href="${ url( '/' ) }" aria-label="Esen Photography, home">
		<img src="${ url( '/assets/img/logo.png' ) }" alt="Esen Photography" width="250" height="100">
	</a>
	<div class="header__right">
		<a class="header__cta" href="${ url( '/contact/' ) }">Book a session</a>
		<button class="burger" id="burger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="menu"><span></span><span></span></button>
	</div>
</header>

<div class="menu" id="menu" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Menu">
	<button class="burger menu__close" id="menu-close" type="button" aria-label="Close menu"><span></span><span></span></button>
	<nav class="menu__nav" aria-label="Main">
		<ol class="menu__list">
				${ menuItems }
		</ol>
		<div class="menu__meta">
			<a href="mailto:${ site.email }">${ site.email }</a>
			<a href="https://instagram.com/${ site.instagram }" target="_blank" rel="noopener">Instagram</a>
			<span>Den Bosch, NL</span>
		</div>
	</nav>
	<div class="menu__photo">${ img( site.photos.menu, '45vw', { alt: '' } ) }</div>
</div>

<main id="main">
${ body }
</main>
${ instagram }

<footer class="footer">
	<div class="wrap">
		<div class="footer__grid">
			<div>
				<a class="footer__logo" href="${ url( '/' ) }"><img src="${ url( '/assets/img/logo.png' ) }" alt="Esen Photography" width="250" height="100" loading="lazy"></a>
				<p class="footer__tag">Natural &amp; heartfelt photography in Den Bosch.</p>
			</div>
			<div>
				<h2 class="footer__title">Explore</h2>
				<ul>
					<li><a href="${ url( '/portfolio/' ) }">Portfolio</a></li>
					<li><a href="${ url( '/about/' ) }">About us</a></li>
					<li><a href="${ url( '/contact/' ) }">Contact</a></li>
				</ul>
			</div>
			<div>
				<h2 class="footer__title">Portfolio</h2>
				<ul>
					${ site.galleries.map( ( g ) => `<li><a href="${ url( `/portfolio/${ g.slug }/` ) }">${ g.title }</a></li>` ).join( '\n\t\t\t\t\t' ) }
				</ul>
			</div>
			<div>
				<h2 class="footer__title">Say hello</h2>
				<ul>
					<li><a href="mailto:${ site.email }">${ site.email }</a></li>
					<li><a href="https://instagram.com/${ site.instagram }" target="_blank" rel="noopener">@${ site.instagram }</a></li>
					<li>${ esc( site.location ) }</li>
				</ul>
			</div>
		</div>
		<div class="footer__bottom">
			<span>&copy; ${ year } Esen Photography</span>
			<span>Wedding · Family · Newborn · Fashion</span>
		</div>
	</div>
</footer>

<div class="lightbox" id="lightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Photo viewer">
	<button class="lightbox__close" type="button" aria-label="Close"><span></span><span></span></button>
	<button class="lightbox__nav lightbox__prev" type="button" aria-label="Previous photo">&#8592;</button>
	<figure class="lightbox__figure"><img alt=""></figure>
	<button class="lightbox__nav lightbox__next" type="button" aria-label="Next photo">&#8594;</button>
	<span class="lightbox__count"></span>
</div>
</body>
</html>
`;
}

// ---------- shared sections ----------

const pageHero = ( { eyebrow, title, text, image } ) => `
<section class="page-hero">
	<div class="page-hero__img">${ img( image, '100vw', { eager: true, alt: '' } ) }</div>
	<div class="page-hero__content wrap">
		${ eyebrow ? `<p class="eyebrow">${ eyebrow }</p>` : '' }
		<h1 class="display display--md">${ title }</h1>
		${ text ? `<p class="page-hero__text">${ esc( text ) }</p>` : '' }
	</div>
</section>`;

const cta = () => `
<section class="cta">
	<div class="cta__img">${ img( site.photos.cta, '100vw', { alt: '' } ) }</div>
	<div class="wrap reveal">
		<p class="eyebrow">Let's create together</p>
		<h2 class="display display--md">Tell us <em>your story.</em></h2>
		<p>Have a date in mind or a question about a session? We'd love to hear from you.</p>
		<a class="btn btn--light" href="${ url( '/contact/' ) }">Get in touch</a>
	</div>
</section>`;

// ---------- pages ----------

const BUILD_ID = Date.now().toString( 36 );

// Home
addPage( '/', layout( {
	path: '/',
	...site.pages.home,
	hasHero: true,
	body: `
<section class="hero">
	<div class="hero__img">${ img( site.photos.hero, '100vw', { eager: true } ) }</div>
	<div class="hero__content wrap">
		<div class="hero__row">
			<div>
				<h1>
					<span class="eyebrow">Wedding, family &amp; newborn photographer in Den Bosch</span>
					<span class="display">Cherish every<br><em>moment,</em> forever.</span>
				</h1>
				<p class="hero__sub">Natural, heartfelt photography for life's most precious chapters. Based in Den Bosch ('s-Hertogenbosch), available across the Netherlands.</p>
				<a class="btn btn--light" href="${ url( '/contact/' ) }">Check availability</a>
			</div>
			<span class="hero__scroll" aria-hidden="true">Scroll</span>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap intro">
		<div class="intro__photos reveal">
			<div class="a">${ img( site.photos.intro_a, '(max-width: 1000px) 80vw, 35vw' ) }</div>
			<div class="b">${ img( site.photos.intro_b, '(max-width: 1000px) 40vw, 18vw' ) }</div>
		</div>
		<div class="intro__text reveal">
			<p class="eyebrow">Meet us</p>
			<h2 class="h2">A love for photography, <em>a love for each other.</em></h2>
			<p class="lead">We're Esen &amp; Mouchammed, the husband-and-wife team behind Esen Photography. We met over ten years ago in our university's photography club, and what began as a shared hobby became a life together.</p>
			<p>Today we bring that same curiosity to every session: the first smile of a newborn, a family gathering, the quiet moments of a wedding day. We've learned that the best pictures are the ones you can feel.</p>
			<div class="intro__sign"><span>Esen &amp; Mouchammed</span><a class="link" href="${ url( '/about/' ) }">Our story</a></div>
		</div>
	</div>
</section>

<section class="section section--flush-top">
	<div class="wrap">
		<div class="spec-head reveal">
			<div>
				<p class="eyebrow">Our specialties</p>
				<h2 class="h2">Every chapter, <em>beautifully told.</em></h2>
			</div>
			<p class="lead">From intimate weddings to growing families, we turn your memories into timeless art, with warmth and a personal touch in every session.</p>
		</div>
		<div class="spec">
			${ site.galleries.map( ( g, i ) => `<a class="card reveal" href="${ url( `/portfolio/${ g.slug }/` ) }">
				${ img( g.card, '(max-width: 600px) 100vw, (max-width: 1000px) 50vw, 25vw' ) }
				<div class="card__body">
					<span class="card__num">0${ i + 1 }</span>
					<h3 class="card__title">${ g.title }</h3>
					<p class="card__text">${ esc( g.excerpt ) }</p>
				</div>
			</a>` ).join( '\n\t\t\t' ) }
		</div>
		<p class="spec-note reveal">Maternity sessions are available too. <a href="${ url( '/contact/' ) }">Just ask.</a></p>
	</div>
</section>

<section class="section quote">
	<div class="wrap reveal">
		<blockquote>&ldquo;Every photograph should tell a story, not just of a moment, but of the <em>emotions and memories</em> within it.&rdquo;</blockquote>
		<cite>Esen</cite>
	</div>
</section>

<section class="section">
	<div class="strip-head wrap reveal">
		<p class="eyebrow">Recent work</p>
		<h2 class="h2">Moments we've <em>loved capturing</em></h2>
	</div>
	<div class="strip">
		${ site.photos.strip.map( ( k ) => `<figure class="reveal">${ img( k, '(max-width: 600px) 50vw, 33vw' ) }</figure>` ).join( '\n\t\t' ) }
	</div>
	<div class="strip-cta reveal"><a class="btn" href="${ url( '/portfolio/' ) }">View the portfolio</a></div>
</section>

<section class="section section--sand">
	<div class="wrap">
		<div class="reveal narrow">
			<p class="eyebrow">How it works</p>
			<h2 class="h2">Simple, personal, <em>and relaxed.</em></h2>
		</div>
		<div class="process">
			<div class="step reveal"><span class="step__num">i.</span><h3 class="h3">Say hello</h3><p>Tell us about your plans, your date and what matters to you. We'll get back to you with availability and options.</p></div>
			<div class="step reveal"><span class="step__num">ii.</span><h3 class="h3">Plan together</h3><p>We'll talk through locations, timing and the little details, so you can relax and simply enjoy the day.</p></div>
			<div class="step reveal"><span class="step__num">iii.</span><h3 class="h3">Relive it</h3><p>Receive a carefully edited gallery of your story, ready to share, print and keep forever.</p></div>
		</div>
	</div>
</section>
${ cta() }`,
} ) );

// Portfolio overview
addPage( '/portfolio/', layout( {
	path: '/portfolio/',
	...site.pages.portfolio,
	hasHero: true,
	ogImage: site.photos.portfolio_hero,
	crumbs: [ { name: 'Portfolio', path: '/portfolio/' } ],
	body: `${ pageHero( { eyebrow: 'Portfolio', title: 'Our photography <em>portfolio</em>', text: 'Weddings, families, newborns and fashion: a selection of our favourite work.', image: site.photos.portfolio_hero } ) }

<section class="section">
	<div class="wrap">
		<div class="portfolio">
			${ site.galleries.map( ( g, i ) => `<a class="portfolio__item reveal" href="${ url( `/portfolio/${ g.slug }/` ) }">
				<div class="portfolio__img">${ img( g.cover, '(max-width: 800px) 100vw, 50vw' ) }</div>
				<div class="portfolio__meta">
					<span class="card__num">0${ i + 1 }</span>
					<h2 class="h2">${ g.title }</h2>
					<p>${ esc( g.excerpt ) }</p>
					<span class="link">View ${ galleryPhotos( g ).length } photos</span>
				</div>
			</a>` ).join( '\n\t\t\t' ) }
		</div>
	</div>
</section>
${ cta() }`,
} ) );

// Category galleries
site.galleries.forEach( ( g, i ) => {
	const next = site.galleries[ ( i + 1 ) % site.galleries.length ];
	const path = `/portfolio/${ g.slug }/`;
	addPage( path, layout( {
		path,
		title: g.seoTitle,
		description: g.description,
		hasHero: true,
		ogImage: g.cover,
		crumbs: [ { name: 'Portfolio', path: '/portfolio/' }, { name: g.title, path } ],
		body: `${ pageHero( { eyebrow: 'Portfolio', title: `${ g.title } photographer <em>in&nbsp;Den&nbsp;Bosch</em>`, text: g.excerpt, image: g.cover } ) }

<section class="section section--tight">
	<div class="wrap">
		<div class="prose prose--center reveal"><p>${ esc( g.intro ) }</p></div>
	</div>
	<div class="masonry">
		${ galleryPhotos( g ).map( ( p ) => `<a class="masonry__item reveal" href="${ photoUrl( p ) }" data-lightbox>${ img( p.key, '(max-width: 1100px) 50vw, 33vw' ) }</a>` ).join( '\n\t\t' ) }
	</div>
	<nav class="gallery-nav wrap" aria-label="Portfolio">
		<a class="link" href="${ url( '/portfolio/' ) }">All stories</a>
		<a class="gallery-nav__next" href="${ url( `/portfolio/${ next.slug }/` ) }">
			<span class="eyebrow">Next story</span>
			<span class="h2">${ next.title } <em>&rarr;</em></span>
		</a>
	</nav>
</section>
${ cta() }`,
	} ) );
} );

// About
addPage( '/about/', layout( {
	path: '/about/',
	title: site.pages.about.title,
	description: site.pages.about.description,
	hasHero: true,
	ogImage: site.photos.about_hero,
	crumbs: [ { name: 'About', path: '/about/' } ],
	body: `${ pageHero( { eyebrow: 'About us', title: 'Meet <em>Esen &amp; Mouchammed</em>', text: "Wedding, family and newborn photographers based in Den Bosch ('s-Hertogenbosch).", image: site.photos.about_hero } ) }

<section class="section">
	<div class="wrap about">
		<div class="about__photo reveal">${ img( site.photos.about, '(max-width: 1000px) 100vw, 40vw' ) }</div>
		<div class="about__text reveal">
			<p class="eyebrow">Our story</p>
			<h2 class="h2">A love for photography, <em>a love for each other.</em></h2>
			<div class="prose">
				${ site.pages.about.story.map( ( p ) => `<p>${ esc( p ) }</p>` ).join( '\n\t\t\t\t' ) }
			</div>
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
		<div class="values__photo reveal">${ img( site.photos.about_2, '(max-width: 1000px) 100vw, 45vw' ) }</div>
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
${ cta() }`,
} ) );

// Contact
const formKey = site.web3formsKey;
addPage( '/contact/', layout( {
	path: '/contact/',
	title: site.pages.contact.title,
	description: site.pages.contact.description,
	hideInstagram: true,
	ogImage: site.photos.contact,
	crumbs: [ { name: 'Contact', path: '/contact/' } ],
	body: `
<section class="contact">
	<div class="contact__photo">${ img( site.photos.contact, '(max-width: 1000px) 100vw, 45vw', { eager: true, alt: '' } ) }</div>

	<div class="contact__body">
		<p class="eyebrow">Contact</p>
		<h1 class="display display--md">Let's tell <em>your story.</em></h1>
		<div class="prose"><p>${ esc( site.pages.contact.intro ) }</p></div>

		<div id="form" class="contact__form-wrap">
			<div class="notice notice--ok" role="status" hidden data-form-success>
				<h2 class="h3">Thank you, <em>we've got your message.</em></h2>
				<p>We'll get back to you as soon as possible, usually within two working days.</p>
			</div>
			<div class="notice notice--error" role="alert" hidden data-form-error>
				Sorry, your message could not be sent. Please email us directly at <a href="mailto:${ site.email }">${ site.email }</a>.
			</div>
			${ formKey ? `<form class="form" method="post" action="https://api.web3forms.com/submit" data-contact-form>
				<input type="hidden" name="access_key" value="${ esc( formKey ) }">
				<input type="hidden" name="subject" value="New enquiry from the Esen Photography website">
				<input type="hidden" name="from_name" value="Esen Photography website">
				<input type="hidden" name="redirect" value="${ abs( '/contact/' ) }?sent=1#form">
				<input type="checkbox" name="botcheck" class="form__hp" tabindex="-1" autocomplete="off" aria-hidden="true">

				<div class="form__row">
					<label class="field"><span>Name *</span><input type="text" name="name" required autocomplete="name"></label>
					<label class="field"><span>Email *</span><input type="email" name="email" required autocomplete="email"></label>
				</div>
				<div class="form__row">
					<label class="field"><span>Event date</span><input type="date" name="date"></label>
					<label class="field"><span>Location</span><input type="text" name="location" placeholder="City or venue"></label>
				</div>
				<fieldset class="field field--chips">
					<legend>Type of session *</legend>
					${ site.sessionTypes.map( ( t, i ) => `<label class="chip"><input type="radio" name="session" value="${ esc( t ) }"${ i === 0 ? ' required' : '' }><span>${ esc( t ) }</span></label>` ).join( '\n\t\t\t\t\t' ) }
				</fieldset>
				<label class="field"><span>Message *</span><textarea name="message" rows="5" required placeholder="Tell us a little about your plans"></textarea></label>
				<button class="btn btn--solid" type="submit">Send message</button>
			</form>` : `<p class="notice">Email us at <a href="mailto:${ site.email }">${ site.email }</a> and tell us about your date and plans.</p>
			<p><a class="btn btn--solid" href="mailto:${ site.email }?subject=${ encodeURIComponent( 'Photography enquiry' ) }">Write to us</a></p>` }
		</div>

		<div class="contact__details">
			<div><span class="eyebrow">Email</span><a href="mailto:${ site.email }">${ site.email }</a></div>
			<div><span class="eyebrow">Instagram</span><a href="https://instagram.com/${ site.instagram }" target="_blank" rel="noopener">@${ site.instagram }</a></div>
			<div><span class="eyebrow">Based in</span><span>${ esc( site.location ) }</span></div>
		</div>
	</div>
</section>`,
} ) );

// 404 (GitHub Pages serves /404.html for unknown URLs)
addPage( '/404.html', layout( {
	path: '/404.html',
	title: 'Page not found · Esen Photography',
	description: 'The page you are looking for does not exist.',
	noindex: true,
	body: `
<section class="section page-plain page-404">
	<div class="wrap">
		<p class="eyebrow">Error 404</p>
		<h1 class="display display--md">This moment <em>wasn't captured.</em></h1>
		<p class="lead">The page you're looking for doesn't exist or has moved.</p>
		<p><a class="btn btn--solid" href="${ url( '/' ) }">Back to home</a></p>
	</div>
</section>`,
} ), false );

// Old WordPress addresses forward to their new pages.
const redirects = {
	'/home/': '/',
	'/gallery/': '/portfolio/',
	'/services/': '/',
	...Object.fromEntries( site.galleries.map( ( g ) => [ `/gallery/${ g.slug }/`, `/portfolio/${ g.slug }/` ] ) ),
};
for ( const [ from, to ] of Object.entries( redirects ) ) {
	addPage( from, `<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Moved</title><link rel="canonical" href="${ abs( to ) }"><meta name="robots" content="noindex"><meta http-equiv="refresh" content="0; url=${ url( to ) }"></head><body><p>This page has moved to <a href="${ url( to ) }">${ abs( to ) }</a>.</p></body></html>\n`, false );
}

// ---------- write ----------

rmSync( OUT, { recursive: true, force: true } );
for ( const page of pages ) {
	const file = page.path.endsWith( '.html' ) ? join( OUT, page.path ) : join( OUT, page.path, 'index.html' );
	mkdirSync( dirname( file ), { recursive: true } );
	writeFileSync( file, page.html );
}

cpSync( join( SRC, 'assets' ), join( OUT, 'assets' ), { recursive: true } );
cpSync( join( SRC, 'photos' ), join( OUT, 'photos' ), { recursive: true } );

const today = new Date().toISOString().slice( 0, 10 );
writeFileSync( join( OUT, 'sitemap.xml' ), `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${ pages.filter( ( p ) => p.sitemap ).map( ( p ) => `\t<url><loc>${ abs( p.path ) }</loc><lastmod>${ today }</lastmod></url>` ).join( '\n' ) }
</urlset>
` );
writeFileSync( join( OUT, 'robots.txt' ), `User-agent: *\nAllow: /\n\nSitemap: ${ abs( '/sitemap.xml' ) }\n` );
writeFileSync( join( OUT, '.nojekyll' ), '' );
if ( DOMAIN ) {
	writeFileSync( join( OUT, 'CNAME' ), `${ DOMAIN }\n` );
}

if ( ! existsSync( join( OUT, 'photos', 'lg' ) ) ) {
	throw new Error( 'No photos found in site/photos. Run tools/resize-photos.ps1 first.' );
}
console.log( `Built ${ pages.length } pages into _site${ BASE ? ` (base path ${ BASE })` : '' }${ DOMAIN ? `, domain ${ DOMAIN }` : '' }` );
