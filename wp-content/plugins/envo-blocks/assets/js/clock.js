/**
 * Envo Blocks - the shop's clock for the view scripts (Countdown, Opening Hours).
 *
 * Pages may be cached, so "now" is worked out in the browser, in the time zone of the shop (Settings > General).
 * Wall clock values are UTC Date objects whose UTC fields show the shop's local date and time, so adding days
 * is not affected by the visitor's time zone. config = { tz, offset, locale } from Envo_Blocks_Blocks::clock().
 */
( function () {
	'use strict';

	var DAY = 86400000;

	/** Current wall clock time of the shop. */
	function wall( config ) {
		var now = new Date();

		if ( config && config.tz && window.Intl ) {
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

		return new Date( now.getTime() + ( ( config && config.offset ) || 0 ) * 1000 );
	}

	/** Real time (ms) of a wall clock moment of the shop. */
	function toTime( config, wallDate ) {
		return Date.now() + ( wallDate.getTime() - wall( config ).getTime() );
	}

	/** Midnight of a wall clock date, plus days. */
	function day( wallDate, add ) {
		return new Date( Date.UTC( wallDate.getUTCFullYear(), wallDate.getUTCMonth(), wallDate.getUTCDate() ) + ( add || 0 ) * DAY );
	}

	/** ISO weekday 1 = Monday … 7 = Sunday. */
	function iso( wallDate ) {
		return wallDate.getUTCDay() || 7;
	}

	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}

	/** "2026-12-24" and "12-24" of a wall clock date. */
	function keys( wallDate ) {
		var md = pad( wallDate.getUTCMonth() + 1 ) + '-' + pad( wallDate.getUTCDate() );
		return [ wallDate.getUTCFullYear() + '-' + md, md ];
	}

	window.envoBlocksClock = {
		DAY: DAY,
		wall: wall,
		toTime: toTime,
		day: day,
		iso: iso,
		pad: pad,
		keys: keys,
	};
} )();
