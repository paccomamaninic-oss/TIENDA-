/**
 * Envo Blocks - Tax Switcher: saves the choice (cookie) and reloads the page, so every price comes from the server
 * in the chosen mode. Without JavaScript the links (?envo-tax=…) do the same.
 *
 * A page from a page cache made for the other mode is reloaded once with ?envo-tax=… (fresh from the server).
 */
( function () {
	'use strict';

	var blocks = document.querySelectorAll( '.envo-tax[data-cookie]' );
	if ( ! blocks.length ) {
		return;
	}
	var first = blocks[ 0 ];
	var COOKIE = first.getAttribute( 'data-cookie' );
	var PARAM = first.getAttribute( 'data-param' );
	var PATH = first.getAttribute( 'data-path' ) || '/';

	function readCookie() {
		var match = document.cookie.match( new RegExp( '(?:^|; )' + COOKIE + '=(incl|excl)' ) );
		return match ? match[ 1 ] : '';
	}

	function writeCookie( mode ) {
		var secure = 'https:' === window.location.protocol ? '; secure' : '';
		document.cookie = COOKIE + '=' + mode + '; path=' + PATH + '; max-age=31536000; samesite=lax' + secure;
	}

	// Cart fragments of classic themes and the total of the Mini-Cart block are kept in the browser: drop them.
	function dropCachedCart() {
		[ window.sessionStorage, window.localStorage ].forEach( function ( store ) {
			try {
				Object.keys( store ).forEach( function ( key ) {
					if ( 0 === key.indexOf( 'wc_fragments_' ) || 0 === key.indexOf( 'wc_cart_hash_' ) || 0 === key.indexOf( 'wc-blocks_mini_cart' ) ) {
						store.removeItem( key );
					}
				} );
			} catch ( e ) {}
		} );
	}

	function urlWithout() {
		var url = new URL( window.location.href );
		url.searchParams.delete( PARAM );
		return url;
	}

	function choose( mode, block ) {
		if ( 'incl' !== mode && 'excl' !== mode ) {
			return;
		}
		writeCookie( mode );
		dropCachedCart();
		blocks.forEach( function ( b ) {
			b.classList.add( 'is-busy' );
		} );
		if ( block && block.getAttribute( 'data-mode' ) === mode && readCookie() === mode ) {
			blocks.forEach( function ( b ) {
				b.classList.remove( 'is-busy' );
			} );
			return;
		}
		var url = urlWithout();
		if ( url.href !== window.location.href ) {
			window.location.replace( url.href );
		} else {
			window.location.reload();
		}
	}

	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( '.envo-tax [data-envo-tax]' ) : null;
		if ( ! link ) {
			return;
		}
		event.preventDefault();
		choose( link.getAttribute( 'data-envo-tax' ), link.closest( '.envo-tax' ) );
	} );

	// Links with role button / switch: Space too, like real buttons.
	document.addEventListener( 'keydown', function ( event ) {
		if ( ' ' !== event.key && 'Spacebar' !== event.key ) {
			return;
		}
		var link = event.target.closest ? event.target.closest( '.envo-tax [data-envo-tax][role]' ) : null;
		if ( link ) {
			event.preventDefault();
			link.click();
		}
	} );

	document.addEventListener( 'change', function ( event ) {
		if ( event.target.matches && event.target.matches( '.envo-tax [data-envo-tax-select]' ) ) {
			choose( event.target.value, event.target.closest( '.envo-tax' ) );
		}
	} );

	// A page from a page cache with the prices of the other mode: once from the server with ?envo-tax=… .
	var chosen = readCookie();
	var shown = first.getAttribute( 'data-mode' );
	if ( chosen && shown && chosen !== shown ) {
		var key = 'envoTaxFix:' + window.location.pathname + ':' + chosen;
		var tried = false;
		try {
			tried = '1' === window.sessionStorage.getItem( key );
			window.sessionStorage.setItem( key, '1' );
		} catch ( e ) {
			tried = true;
		}
		if ( ! tried ) {
			var url = new URL( window.location.href );
			url.searchParams.set( PARAM, chosen );
			window.location.replace( url.href );
		}
	}
} )();
