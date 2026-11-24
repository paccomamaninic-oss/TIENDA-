/**
 * Envo Blocks - Language Switcher: the open dropdown closes on a click outside, on Escape and when another opens.
 */
( function () {
	'use strict';

	function menus() {
		return document.querySelectorAll( '.sb-langs__menu[open]' );
	}

	document.addEventListener( 'click', function ( event ) {
		menus().forEach( function ( menu ) {
			if ( ! menu.contains( event.target ) ) {
				menu.removeAttribute( 'open' );
			}
		} );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}
		menus().forEach( function ( menu ) {
			menu.removeAttribute( 'open' );
			var toggle = menu.querySelector( 'summary' );
			if ( toggle && menu.contains( document.activeElement ) ) {
				toggle.focus();
			}
		} );
	} );
} )();
