/**
 * Esen theme: header state, menu overlay, reveal-on-scroll and photo lightbox.
 */
( function () {
	document.documentElement.classList.add( 'js' );

	var body = document.body;
	var header = document.getElementById( 'header' );
	var hasHero = body.classList.contains( 'has-hero' );

	// Header: transparent over hero images, solid once scrolled.
	function onScroll() {
		if ( hasHero ) {
			header.classList.toggle( 'is-solid', window.scrollY > 60 );
		}
	}
	onScroll();
	window.addEventListener( 'scroll', onScroll, { passive: true } );

	// Menu overlay.
	var menu = document.getElementById( 'menu' );
	var burger = document.getElementById( 'burger' );
	var menuClose = document.getElementById( 'menu-close' );

	function setMenu( open ) {
		menu.classList.toggle( 'is-open', open );
		menu.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
		burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		body.style.overflow = open ? 'hidden' : '';
		( open ? menuClose : burger ).focus();
	}
	burger.addEventListener( 'click', function () { setMenu( true ); } );
	menuClose.addEventListener( 'click', function () { setMenu( false ); } );

	// Reveal elements as they scroll into view.
	var reveals = document.querySelectorAll( '.reveal' );
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-in' );
					io.unobserve( entry.target );
				}
			} );
		}, { rootMargin: '0px 0px -8% 0px' } );
		reveals.forEach( function ( el ) { io.observe( el ); } );
	} else {
		reveals.forEach( function ( el ) { el.classList.add( 'is-in' ); } );
	}

	// Lightbox for gallery photos.
	var box = document.getElementById( 'lightbox' );
	var links = Array.prototype.slice.call( document.querySelectorAll( '[data-lightbox]' ) );
	var current = 0;

	if ( box && links.length ) {
		var img = box.querySelector( 'img' );
		var count = box.querySelector( '.lightbox__count' );

		var show = function ( index ) {
			current = ( index + links.length ) % links.length;
			var thumb = links[ current ].querySelector( 'img' );
			img.classList.add( 'is-loading' );
			img.onload = function () { img.classList.remove( 'is-loading' ); };
			img.src = links[ current ].href;
			img.alt = thumb ? thumb.alt : '';
			count.textContent = ( current + 1 ) + ' / ' + links.length;
			// Preload the next photo.
			new Image().src = links[ ( current + 1 ) % links.length ].href;
		};
		var open = function ( index ) {
			show( index );
			box.classList.add( 'is-open' );
			box.setAttribute( 'aria-hidden', 'false' );
			body.style.overflow = 'hidden';
			box.querySelector( '.lightbox__close' ).focus();
		};
		var close = function () {
			box.classList.remove( 'is-open' );
			box.setAttribute( 'aria-hidden', 'true' );
			body.style.overflow = '';
			links[ current ].focus();
		};

		links.forEach( function ( link, i ) {
			link.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				open( i );
			} );
		} );
		box.querySelector( '.lightbox__close' ).addEventListener( 'click', close );
		box.querySelector( '.lightbox__prev' ).addEventListener( 'click', function () { show( current - 1 ); } );
		box.querySelector( '.lightbox__next' ).addEventListener( 'click', function () { show( current + 1 ); } );
		box.addEventListener( 'click', function ( e ) {
			if ( e.target === box ) { close(); }
		} );

		// Swipe on touch screens.
		var startX = null;
		box.addEventListener( 'touchstart', function ( e ) { startX = e.touches[ 0 ].clientX; }, { passive: true } );
		box.addEventListener( 'touchend', function ( e ) {
			if ( startX === null ) { return; }
			var dx = e.changedTouches[ 0 ].clientX - startX;
			if ( Math.abs( dx ) > 50 ) { show( current + ( dx < 0 ? 1 : -1 ) ); }
			startX = null;
		} );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( box && box.classList.contains( 'is-open' ) ) {
			if ( e.key === 'Escape' ) { close(); }
			if ( e.key === 'ArrowRight' ) { show( current + 1 ); }
			if ( e.key === 'ArrowLeft' ) { show( current - 1 ); }
		} else if ( e.key === 'Escape' && menu.classList.contains( 'is-open' ) ) {
			setMenu( false );
		}
	} );
}() );

/**
 * Contact form (Web3Forms): send without leaving the page, show a thank-you message.
 * Without JavaScript the form posts normally and Web3Forms redirects back with ?sent=1.
 */
( function () {
	var form = document.querySelector( '[data-contact-form]' );
	var success = document.querySelector( '[data-form-success]' );
	var failure = document.querySelector( '[data-form-error]' );
	if ( ! success ) { return; }

	function done( ok ) {
		if ( ok ) {
			success.hidden = false;
			if ( form ) { form.hidden = true; }
			success.scrollIntoView( { behavior: 'smooth', block: 'center' } );
		} else {
			failure.hidden = false;
		}
	}

	if ( /[?&]sent=1/.test( window.location.search ) ) { done( true ); }
	if ( ! form || ! window.fetch ) { return; }

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		failure.hidden = true;
		form.classList.add( 'is-sending' );
		var data = Object.fromEntries( new FormData( form ) );
		delete data.redirect;
		fetch( form.action, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
			body: JSON.stringify( data ),
		} )
			.then( function ( r ) { return r.json(); } )
			.then( function ( res ) { done( !! res.success ); } )
			.catch( function () { done( false ); } )
			.finally( function () { form.classList.remove( 'is-sending' ); } );
	} );
}() );
