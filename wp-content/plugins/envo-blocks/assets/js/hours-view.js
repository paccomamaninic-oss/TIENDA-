/**
 * Envo Blocks - Opening Hours: the "Open now / Closed · opens …" status and today's row, worked out in the shop's
 * time zone (clock.js) and refreshed every minute; special days that have passed are removed from the list.
 */
( function () {
	'use strict';

	var clock = window.envoBlocksClock;

	function sprintf( format, values ) {
		var i = 0;
		return format.replace( /%(?:(\d+)\$)?s/g, function ( match, position ) {
			return String( values[ position ? parseInt( position, 10 ) - 1 : i++ ] );
		} );
	}

	function rangesOn( config, date ) {
		var keys = clock.keys( date );
		var special = config.special || {};
		for ( var i = 0; i < keys.length; i++ ) {
			if ( Object.prototype.hasOwnProperty.call( special, keys[ i ] ) ) {
				return special[ keys[ i ] ] || [];
			}
		}
		return ( config.week || {} )[ clock.iso( date ) ] || [];
	}

	function status( config ) {
		var now = clock.wall( config );
		var today = clock.day( now );
		var minute = now.getUTCHours() * 60 + now.getUTCMinutes();
		var fmt = config.fmt || {};
		var texts = config.texts || {};
		var i;
		var r;

		var current = rangesOn( config, today );
		for ( i = 0; i < current.length; i++ ) {
			r = current[ i ];
			if ( r[ 0 ] <= minute && minute < r[ 1 ] ) {
				return [ 'open', sprintf( texts.open, [ fmt[ r[ 1 ] ] ] ) ];
			}
		}
		var yesterday = rangesOn( config, clock.day( now, -1 ) );
		for ( i = 0; i < yesterday.length; i++ ) {
			r = yesterday[ i ];
			if ( r[ 1 ] > 1440 && minute < r[ 1 ] - 1440 ) {
				return [ 'open', sprintf( texts.open, [ fmt[ r[ 1 ] ] ] ) ];
			}
		}
		for ( var d = 0; d <= 7; d++ ) {
			var date = clock.day( now, d );
			var list = rangesOn( config, date );
			for ( i = 0; i < list.length; i++ ) {
				if ( d * 1440 + list[ i ][ 0 ] > minute ) {
					var when = 0 === d ? texts.today : ( 1 === d ? texts.tomorrow : config.names[ clock.iso( date ) ] );
					return [ 'closed', sprintf( texts.closed, [ when, fmt[ list[ i ][ 0 ] ] ] ) ];
				}
			}
		}
		return [ 'closed', texts.closedAll ];
	}

	function init( box ) {
		var config;
		try {
			config = JSON.parse( box.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var line = box.querySelector( '[data-sb-status]' );
		var text = box.querySelector( '[data-sb-status-text]' );

		function update() {
			var now = clock.wall( config );
			var iso = String( clock.iso( now ) );

			if ( line && text ) {
				var state = status( config );
				line.classList.toggle( 'is-open', 'open' === state[ 0 ] );
				line.classList.toggle( 'is-closed', 'open' !== state[ 0 ] );
				text.textContent = state[ 1 ];
			}

			if ( box.classList.contains( 'sb-hours--today' ) ) {
				box.querySelectorAll( 'tr[data-days]' ).forEach( function ( row ) {
					row.classList.toggle( 'is-today', -1 !== row.getAttribute( 'data-days' ).split( ',' ).indexOf( iso ) );
				} );
			}

			var todayKey = clock.keys( now )[ 0 ];
			var special = box.querySelector( '.sb-hours__special' );
			box.querySelectorAll( '.sb-hours__special li[data-date]' ).forEach( function ( item ) {
				if ( item.getAttribute( 'data-date' ) < todayKey ) {
					item.remove();
				}
			} );
			if ( special && ! special.querySelector( 'li' ) ) {
				special.remove();
			}
		}

		update();
		setInterval( update, 60000 );
	}

	function start() {
		if ( ! clock ) {
			return;
		}
		document.querySelectorAll( '.sb-hours[data-config]' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
