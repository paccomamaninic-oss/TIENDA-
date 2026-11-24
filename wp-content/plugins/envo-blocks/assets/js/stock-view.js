/**
 * Envo Blocks - Stock Indicator: follows the chosen variation of a variable product.
 *
 * WooCommerce triggers found_variation (with the variation data) and reset_data on the variations form through
 * jQuery; the stock of every variation comes from the server in data-config, so nothing is guessed here.
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

		var label = box.querySelector( '[data-sb-stock-text]' );
		var number = window.Intl ? new Intl.NumberFormat( config.locale || undefined ) : null;

		function render( state ) {
			var visible = !! state && !! config.shown[ state[ 0 ] ];
			box.hidden = ! visible;
			if ( ! visible ) {
				return;
			}
			[ 'low', 'in', 'out', 'backorder' ].forEach( function ( name ) {
				box.classList.toggle( 'is-' + name, name === state[ 0 ] );
			} );
			if ( label ) {
				label.textContent = config.texts[ state[ 0 ] ].replace( '{stock}', number ? number.format( state[ 1 ] ) : state[ 1 ] );
			}
			box.style.setProperty( '--sb-stock-fill', Math.min( 100, ( state[ 1 ] / config.full ) * 100 ) + '%' );
		}

		if ( ! $ || ! config.variations || ! Object.keys( config.variations ).length ) {
			return;
		}

		$( document.body ).on( 'found_variation', 'form.variations_form', function ( event, variation ) {
			if ( parseInt( this.getAttribute( 'data-product_id' ), 10 ) !== config.product || ! variation ) {
				return;
			}
			render( config.variations[ variation.variation_id ] || config.initial );
		} );
		$( document.body ).on( 'reset_data', 'form.variations_form', function () {
			if ( parseInt( this.getAttribute( 'data-product_id' ), 10 ) === config.product ) {
				render( config.initial );
			}
		} );
	}

	function start() {
		var boxes = document.querySelectorAll( '.sb-stock[data-config]' );
		boxes.forEach( function ( box ) {
			init( box, window.jQuery );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
