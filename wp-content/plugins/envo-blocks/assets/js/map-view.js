/**
 * Envo Blocks - Map: "load after a click".
 *
 * The map (an iframe of Google Maps or OpenStreetMap) is created only when the visitor clicks the button. The choice
 * is remembered in the browser (localStorage), so the next maps load at once. Plain JavaScript.
 */
( function () {
	'use strict';

	var KEY = 'envoBlocksMapConsent';

	function remembered() {
		try {
			return '1' === window.localStorage.getItem( KEY );
		} catch ( e ) {
			return false;
		}
	}

	function remember() {
		try {
			window.localStorage.setItem( KEY, '1' );
		} catch ( e ) {}
	}

	function load( box, focus ) {
		var frame = document.createElement( 'iframe' );
		frame.className = 'sb-map__frame';
		frame.src = box.getAttribute( 'data-sb-map-src' );
		frame.title = box.getAttribute( 'data-sb-map-title' ) || '';
		frame.setAttribute( 'loading', 'lazy' );
		frame.setAttribute( 'referrerpolicy', 'no-referrer-when-downgrade' );
		frame.setAttribute( 'allowfullscreen', '' );
		box.parentNode.replaceChild( frame, box );
		if ( focus ) {
			frame.setAttribute( 'tabindex', '-1' );
			frame.focus();
		}
	}

	function init() {
		var boxes = Array.prototype.slice.call( document.querySelectorAll( '.sb-map__consent[data-sb-map-src]' ) );

		if ( remembered() ) {
			boxes.forEach( function ( box ) {
				load( box, false );
			} );
			return;
		}

		boxes.forEach( function ( box ) {
			var button = box.querySelector( '.sb-map__load' );
			if ( ! button ) {
				return;
			}
			button.addEventListener( 'click', function () {
				remember();
				// Every map waiting on the page loads with the first click.
				document.querySelectorAll( '.sb-map__consent[data-sb-map-src]' ).forEach( function ( other ) {
					load( other, other === box );
				} );
			} );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
