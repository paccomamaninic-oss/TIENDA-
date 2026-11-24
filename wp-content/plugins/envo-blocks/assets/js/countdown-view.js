/**
 * Envo Blocks - Countdown: ticks every second towards the end set in the block (a date, or the next chosen day at
 * the daily time, in the shop's time zone - see clock.js), then hides the block or shows the text.
 */
( function () {
	'use strict';

	var clock = window.envoBlocksClock;

	/** Next daily deadline (ms): today when it is a chosen day and the time has not passed, else the next chosen day. */
	function nextDaily( config ) {
		var parts = String( config.time || '14:00' ).split( ':' );
		var now = clock.wall( config );
		for ( var i = 0; i <= 7; i++ ) {
			var date = clock.day( now, i );
			var end = new Date( date.getTime() + ( +parts[ 0 ] * 60 + +parts[ 1 ] ) * 60000 );
			if ( -1 !== ( config.days || [] ).indexOf( clock.iso( date ) ) && end > now ) {
				return { end: clock.toTime( config, end ), today: 0 === i };
			}
		}
		return null;
	}

	function init( box ) {
		var config;
		try {
			config = JSON.parse( box.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var nums = {};
		box.querySelectorAll( '[data-unit]' ).forEach( function ( node ) {
			nums[ node.getAttribute( 'data-unit' ) ] = node;
		} );
		var done = box.querySelector( '.sb-countdown__done' );
		var target = config.end;
		var timer;

		function set( key, value ) {
			if ( nums[ key ] ) {
				var text = ( value < 10 ? '0' : '' ) + value;
				if ( nums[ key ].textContent !== text ) {
					nums[ key ].textContent = text;
				}
			}
		}

		function tick() {
			if ( 'daily' === config.mode ) {
				var next = nextDaily( config );
				if ( ! next ) {
					box.hidden = true;
					return;
				}
				target = next.end;
				box.hidden = 'hide' === config.outside && ! next.today;
			}

			var left = Math.max( 0, Math.floor( ( target - Date.now() ) / 1000 ) );

			if ( ! left && 'date' === config.mode ) {
				box.classList.add( 'is-done' );
				if ( 'hide' === config.finished ) {
					box.hidden = true;
				} else if ( done ) {
					done.hidden = false;
				}
				clearInterval( timer );
			}

			var days = Math.floor( left / 86400 );
			set( 'd', days );
			set( 'h', config.showDays ? Math.floor( ( left % 86400 ) / 3600 ) : Math.floor( left / 3600 ) );
			set( 'm', Math.floor( ( left % 3600 ) / 60 ) );
			set( 's', left % 60 );
		}

		tick();
		timer = setInterval( tick, 1000 );
	}

	function start() {
		if ( ! clock ) {
			return;
		}
		document.querySelectorAll( '.sb-countdown[data-config]' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
