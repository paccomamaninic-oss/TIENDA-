/**
 * Envo Blocks - Delivery Estimate: recalculates the ship and delivery dates in the shop's time zone
 * (pages may be cached) and runs the countdown to the cut-off time.
 *
 * Dates are handled as "wall clock" values in UTC Date objects, so adding days is not affected by the visitor's time zone.
 */
( function () {
	'use strict';

	var DAY = 86400000;

	function sprintf( format, values ) {
		var i = 0;
		return format.replace( /%(?:(\d+)\$)?d/g, function ( match, position ) {
			var index = position ? parseInt( position, 10 ) - 1 : i++;
			return String( values[ index ] );
		} );
	}

	function escapeHtml( text ) {
		return String( text ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	/** Current wall clock time of the shop as a UTC Date. */
	function shopNow( config ) {
		var now = new Date();

		if ( config.tz && window.Intl ) {
			try {
				var parts = {};
				new Intl.DateTimeFormat( 'en-US', {
					timeZone: config.tz,
					hourCycle: 'h23',
					year: 'numeric',
					month: '2-digit',
					day: '2-digit',
					hour: '2-digit',
					minute: '2-digit',
					second: '2-digit',
				} )
					.formatToParts( now )
					.forEach( function ( part ) {
						parts[ part.type ] = part.value;
					} );
				return new Date( Date.UTC( +parts.year, +parts.month - 1, +parts.day, +parts.hour % 24, +parts.minute, +parts.second ) );
			} catch ( e ) {}
		}

		return new Date( now.getTime() + config.offset * 1000 );
	}

	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}

	function isDay( date, days, holidays ) {
		var iso = date.getUTCDay() || 7;
		var md = pad( date.getUTCMonth() + 1 ) + '-' + pad( date.getUTCDate() );
		var ymd = date.getUTCFullYear() + '-' + md;
		return -1 !== days.indexOf( iso ) && -1 === holidays.indexOf( ymd ) && -1 === holidays.indexOf( md );
	}

	function next( date, days, holidays, count ) {
		var guard = 0;
		while ( count > 0 && guard < 400 ) {
			date = new Date( date.getTime() + DAY );
			if ( isDay( date, days, holidays ) ) {
				count--;
			}
			guard++;
		}
		return date;
	}

	function format( config, date, long ) {
		try {
			return new Intl.DateTimeFormat( config.locale, long ? { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' } : { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'UTC' } ).format( date );
		} catch ( e ) {
			return date.toISOString().slice( 0, 10 );
		}
	}

	function init( box ) {
		var config;
		try {
			config = JSON.parse( box.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var ship = box.querySelector( '[data-sb-ship]' );
		var arrive = box.querySelector( '[data-sb-arrive]' );
		var state = '';
		var countdown = null;
		var deadline = 0;

		function hl( text, attr ) {
			return '<strong class="sb-delivery__hl"' + ( attr ? ' ' + attr : '' ) + '>' + escapeHtml( text ) + '</strong>';
		}

		function render() {
			var now = shopNow( config );
			var today = new Date( Date.UTC( now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate() ) );
			var cutoff = new Date( today.getTime() + ( config.cutoff[ 0 ] * 60 + config.cutoff[ 1 ] ) * 60000 );
			var shipToday = isDay( today, config.shipDays, config.holidays ) && now < cutoff;
			var shipDate = shipToday ? today : next( today, config.shipDays, config.holidays, 1 );

			shipDate = next( shipDate, config.shipDays, config.holidays, config.processing );

			var from = next( shipDate, config.deliveryDays, config.holidays, config.min );
			var to = next( shipDate, config.deliveryDays, config.holidays, config.max );
			var isToday = shipDate.getTime() === today.getTime();
			var key = isToday + '|' + shipDate.getTime() + '|' + from.getTime() + '|' + to.getTime();

			deadline = Date.now() + ( cutoff.getTime() - now.getTime() );

			if ( key !== state ) {
				state = key;

				if ( ship ) {
					var dateText = shipDate.getTime() === today.getTime() + DAY ? config.words.tomorrow : format( config, shipDate, true );
					ship.innerHTML = isToday
						? escapeHtml( config.messages.today ).replace( '{countdown}', hl( '', 'data-sb-countdown' ) )
						: escapeHtml( config.messages.later ).replace( '{date}', hl( dateText ) );
					countdown = ship.querySelector( '[data-sb-countdown]' );
				}

				if ( arrive ) {
					var range = from.getTime() === to.getTime() ? format( config, from ) : format( config, from ) + ' – ' + format( config, to );
					arrive.innerHTML = escapeHtml( config.messages.delivery ).replace( '{delivery}', hl( range ) );
				}
			}

			if ( countdown ) {
				var left = Math.max( 0, Math.floor( ( deadline - Date.now() ) / 1000 ) );
				var minutes = config.seconds ? Math.floor( left / 60 ) : Math.ceil( left / 60 ); // Without seconds round up: "0 min" only at the cut-off.
				var h = Math.floor( minutes / 60 );
				var m = minutes % 60;
				countdown.textContent = config.seconds ? sprintf( config.words.hms, [ h, m, left % 60 ] ) : sprintf( config.words.hm, [ h, m ] );
			}
		}

		render();
		box.classList.add( 'is-ready' );
		setInterval( render, 1000 );
	}

	function start() {
		document.querySelectorAll( '.sb-delivery[data-config]' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
