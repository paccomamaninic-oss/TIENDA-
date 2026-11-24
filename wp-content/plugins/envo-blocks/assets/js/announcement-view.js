/**
 * Envo Blocks - Announcement Bar: rotation of the messages, the ticker speed and the close button.
 *
 * With "reduce motion" (or while the pointer or the keyboard focus is in the bar) nothing moves on its own;
 * the arrows still change the message.
 */
( function () {
	'use strict';

	var still = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' );

	function remember( config ) {
		var key = 'envoBlocksBar:' + config.id;
		try {
			if ( config.days > 0 ) {
				localStorage.setItem( key, String( Date.now() + config.days * 86400000 ) );
			} else {
				sessionStorage.setItem( key, '0' );
			}
		} catch ( e ) {}
	}

	function init( bar ) {
		var config;
		try {
			config = JSON.parse( bar.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var close = bar.querySelector( '[data-sb-close]' );
		if ( close ) {
			close.addEventListener( 'click', function () {
				bar.hidden = true;
				remember( config );
			} );
		}

		var paused = false;
		[ 'mouseenter', 'focusin' ].forEach( function ( type ) {
			bar.addEventListener( type, function () {
				paused = true;
				bar.classList.add( 'is-paused' );
			} );
		} );
		[ 'mouseleave', 'focusout' ].forEach( function ( type ) {
			bar.addEventListener( type, function ( event ) {
				if ( 'focusout' === type && bar.contains( event.relatedTarget ) ) {
					return;
				}
				paused = false;
				bar.classList.remove( 'is-paused' );
			} );
		} );

		if ( 'marquee' === config.mode ) {
			var track = bar.querySelector( '.sb-announce__track' );
			var first = bar.querySelector( '.sb-announce__list' );
			var copy = bar.querySelector( '.sb-announce__list.is-copy' );
			if ( copy ) {
				copy.inert = true;
			}
			var measure = function () {
				if ( track && first && first.offsetWidth ) {
					track.style.setProperty( '--sb-announce-duration', Math.max( 4, first.offsetWidth / config.speed ) + 's' );
				}
			};
			measure();
			window.addEventListener( 'resize', measure );
			return;
		}

		if ( 'rotate' !== config.mode ) {
			return;
		}

		var items = Array.prototype.slice.call( bar.querySelectorAll( '.sb-announce__item' ) );
		var index = 0;

		function show( next ) {
			items[ index ].classList.remove( 'is-active' );
			index = ( next + items.length ) % items.length;
			items[ index ].classList.add( 'is-active' );
		}

		bar.querySelectorAll( '[data-sb-step]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				show( index + parseInt( button.getAttribute( 'data-sb-step' ), 10 ) );
			} );
		} );

		setInterval( function () {
			if ( ! paused && ! bar.hidden && ! ( still && still.matches ) && ! document.hidden ) {
				show( index + 1 );
			}
		}, config.interval * 1000 );
	}

	function start() {
		document.querySelectorAll( '.sb-announce[data-config]' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
