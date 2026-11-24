/**
 * Envo Blocks - Size Guide: opens the size chart in the native <dialog>, closes it with the
 * close button, Esc (built in) or a click on the backdrop.
 */
( function () {
	'use strict';

	function init( box ) {
		var open = box.querySelector( '.sb-size__open' );
		var dialog = box.querySelector( '.sb-size__dialog' );

		if ( ! open || ! dialog || 'function' !== typeof dialog.showModal ) {
			return;
		}

		// Out of the page flow (e.g. inside a sticky or transformed parent) so the backdrop covers everything.
		document.body.appendChild( dialog );

		open.addEventListener( 'click', function () {
			dialog.showModal();
			document.documentElement.classList.add( 'sb-size-open' );
		} );

		dialog.addEventListener( 'close', function () {
			document.documentElement.classList.remove( 'sb-size-open' );
			open.focus();
		} );

		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog || event.target.closest( '[data-sb-close]' ) ) {
				dialog.close();
			}
		} );
	}

	function start() {
		document.querySelectorAll( '.sb-size' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
