/**
 * Envo Blocks - Free Shipping Progress: follows the cart (Store API) after every add to cart or cart update.
 */
( function () {
	'use strict';

	function formatPrice( value, c ) {
		var fixed = Math.abs( value ).toFixed( c.decimals );
		var parts = fixed.split( '.' );
		parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, c.thousand );
		var number = parts.join( c.decimals > 0 ? c.decimal : '' );
		return String( c.format )
			.replace( '%1$s', c.symbol )
			.replace( '%2$s', number );
	}

	function render( box, config, total ) {
		var remaining = Math.max( 0, config.threshold - total );
		var percent = Math.max( 0, Math.min( 100, Math.floor( ( total / config.threshold ) * 100 ) ) );
		var state = remaining <= 0 ? 'reached' : ( total <= 0 ? 'empty' : 'progress' );
		var template = 'reached' === state ? config.messages.messageReached : ( 'empty' === state ? config.messages.messageEmpty : config.messages.messageProgress );
		var text = String( template )
			.split( '{remaining}' ).join( formatPrice( remaining, config.currency ) )
			.split( '{threshold}' ).join( formatPrice( config.threshold, config.currency ) );

		var label = box.querySelector( '.sb-ship__text' );
		if ( label ) { label.textContent = text; }
		var fill = box.querySelector( '.sb-ship__fill' );
		if ( fill ) { fill.style.width = percent + '%'; }
		var bar = box.querySelector( '.sb-ship__bar' );
		if ( bar ) { bar.setAttribute( 'aria-valuenow', String( percent ) ); }

		box.classList.remove( 'sb-ship--progress', 'sb-ship--reached', 'sb-ship--empty' );
		box.classList.add( 'sb-ship--' + state );
		box.classList.toggle( 'is-hidden', 'reached' === state && !! config.hide );
	}

	function cartTotal( cart, config ) {
		var t = cart && cart.totals ? cart.totals : null;
		if ( ! t ) { return 0; }
		var unit = Math.pow( 10, parseInt( t.currency_minor_unit, 10 ) || 0 );
		var total = ( parseInt( t.total_items, 10 ) || 0 ) - ( parseInt( t.total_discount, 10 ) || 0 );
		if ( config.inclTax ) {
			total += ( parseInt( t.total_items_tax, 10 ) || 0 ) - ( parseInt( t.total_discount_tax, 10 ) || 0 );
		}
		return Math.max( 0, total / unit );
	}

	function refresh( boxes ) {
		boxes.forEach( function ( item ) {
			window.fetch( item.config.restUrl, { credentials: 'same-origin', cache: 'no-store' } )
				.then( function ( response ) { return response.ok ? response.json() : null; } )
				.then( function ( cart ) {
					if ( cart ) { render( item.box, item.config, cartTotal( cart, item.config ) ); }
				} )
				.catch( function () {} );
		} );
	}

	var boxes = [];
	var listening = false;

	function listen() {
		if ( listening ) { return; }
		listening = true;
		var timer = null;
		function schedule() {
			window.clearTimeout( timer );
			timer = window.setTimeout( function () { refresh( boxes ); }, 250 );
		}
		var events = [ 'wc-blocks_added_to_cart', 'wc-blocks_removed_from_cart' ];
		events.forEach( function ( name ) { document.body.addEventListener( name, schedule ); } );
		if ( window.jQuery ) {
			window.jQuery( document.body ).on( 'added_to_cart removed_from_cart updated_cart_totals updated_checkout wc_fragments_refreshed', schedule );
		}
		// The side cart (store layouts) loaded the cart: use it, no request of our own. It also corrects a bar of a
		// page served from a cache.
		document.addEventListener( 'envo-side-cart:update', function ( event ) {
			if ( event.detail ) {
				boxes.forEach( function ( item ) {
					render( item.box, item.config, cartTotal( event.detail, item.config ) );
				} );
			}
		} );
	}

	function init( box ) {
		if ( box.getAttribute( 'data-sb-ship-ready' ) ) { return; }
		box.setAttribute( 'data-sb-ship-ready', '1' );
		if ( box.getAttribute( 'data-preview' ) ) { return; }
		var config;
		try { config = JSON.parse( box.getAttribute( 'data-sb-ship' ) ); } catch ( e ) { return; }
		if ( ! config || ! config.threshold || ! config.restUrl ) { return; }
		boxes.push( { box: box, config: config } );
		listen();
	}

	function initAll() {
		Array.prototype.forEach.call( document.querySelectorAll( '.sb-ship[data-sb-ship]' ), init );
	}

	function boot() {
		initAll();
		if ( window.MutationObserver ) {
			new MutationObserver( initAll ).observe( document.documentElement, { childList: true, subtree: true } );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}() );
