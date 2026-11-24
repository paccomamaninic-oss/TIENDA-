/**
 * Envo Blocks - FAQ block: one question open at a time in browsers without <details name> and opens the question
 * a link points to (#faq-…).
 */
( function () {
	'use strict';

	var nameSupport = 'name' in document.createElement( 'details' );

	if ( ! nameSupport ) {
		document.addEventListener( 'toggle', function ( event ) {
			var item = event.target;
			if ( ! item.classList || ! item.classList.contains( 'sb-faq__item' ) || ! item.open || ! item.getAttribute( 'name' ) ) {
				return;
			}
			document.querySelectorAll( 'details.sb-faq__item[name="' + item.getAttribute( 'name' ) + '"][open]' ).forEach( function ( other ) {
				if ( other !== item ) {
					other.open = false;
				}
			} );
		}, true );
	}

	function openHash() {
		var id = decodeURIComponent( window.location.hash.slice( 1 ) );
		var item = id ? document.getElementById( id ) : null;
		if ( item && item.classList.contains( 'sb-faq__item' ) ) {
			item.open = true;
			item.scrollIntoView( { block: 'start' } );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', openHash );
	} else {
		openHash();
	}
	window.addEventListener( 'hashchange', openHash );
} )();
