/**
 * Envo Blocks - Lowest Price: follows the chosen variation of a variable product.
 *
 * WooCommerce triggers found_variation (with the variation data) and reset_data on the variations form through
 * jQuery; the line of every variation (HTML made on the server, the price formatted by WooCommerce) comes in
 * data-config, so nothing is counted here.
 */
( function () {
	'use strict';

	function init( box, $ ) {
		var config;
		try {
			config = JSON.parse( box.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var label = box.querySelector( '[data-sb-lowest-text]' );

		function render( html ) {
			box.hidden = ! html;
			if ( html && label ) {
				label.innerHTML = html;
			}
		}

		if ( ! $ || ! config.variations || ! Object.keys( config.variations ).length ) {
			return;
		}

		$( document.body ).on( 'found_variation', 'form.variations_form', function ( event, variation ) {
			if ( parseInt( this.getAttribute( 'data-product_id' ), 10 ) !== config.product || ! variation ) {
				return;
			}
			render( config.variations[ variation.variation_id ] || '' );
		} );
		$( document.body ).on( 'reset_data', 'form.variations_form', function () {
			if ( parseInt( this.getAttribute( 'data-product_id' ), 10 ) === config.product ) {
				render( config.initial );
			}
		} );
	}

	function start() {
		document.querySelectorAll( '.sb-lowest[data-config]' ).forEach( function ( box ) {
			init( box, window.jQuery );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
