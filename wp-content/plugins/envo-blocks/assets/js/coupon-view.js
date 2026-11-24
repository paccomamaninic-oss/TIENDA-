/**
 * Envo Blocks - Coupon Code Box: copy the code and apply it to the cart (Store API).
 */
( function () {
	'use strict';

	function copyText( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text );
		}

		return new Promise( function ( resolve, reject ) {
			var area = document.createElement( 'textarea' );
			area.value = text;
			area.setAttribute( 'readonly', '' );
			area.style.position = 'fixed';
			area.style.opacity = '0';
			document.body.appendChild( area );
			area.select();
			try {
				document.execCommand( 'copy' ) ? resolve() : reject();
			} catch ( e ) {
				reject( e );
			}
			document.body.removeChild( area );
		} );
	}

	/** Store API messages contain HTML entities and tags; show them as plain text. */
	function plain( html ) {
		var doc = new DOMParser().parseFromString( html, 'text/html' );
		return ( doc.body.textContent || '' ).trim();
	}

	function init( box ) {
		var config;
		try {
			config = JSON.parse( box.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var copyButton = box.querySelector( '[data-sb-copy]' );
		var label = box.querySelector( '[data-sb-copy-label]' );
		var applyButton = box.querySelector( '[data-sb-apply]' );
		var status = box.querySelector( '.sb-coupon__status' );
		var original = label ? label.textContent : '';
		var timer;

		function say( text, error ) {
			if ( ! status ) {
				return;
			}
			status.textContent = text;
			status.classList.toggle( 'is-error', !! error );
		}

		if ( copyButton ) {
			copyButton.addEventListener( 'click', function () {
				copyText( config.code ).then( function () {
					box.classList.add( 'is-copied' );
					if ( label ) {
						label.textContent = config.copied;
					}
					clearTimeout( timer );
					timer = setTimeout( function () {
						box.classList.remove( 'is-copied' );
						if ( label ) {
							label.textContent = original;
						}
					}, 2000 );
				} );
			} );
		}

		if ( applyButton && config.restUrl ) {
			applyButton.addEventListener( 'click', function () {
				applyButton.disabled = true;
				fetch( config.restUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json', Nonce: config.nonce },
					body: JSON.stringify( { code: config.code } ),
				} )
					.then( function ( response ) {
						return response.json().then( function ( data ) {
							return { ok: response.ok, data: data };
						} );
					} )
					.then( function ( result ) {
						if ( result.ok ) {
							box.classList.add( 'is-applied' );
							say( config.applied );
							document.body.dispatchEvent( new CustomEvent( 'wc-blocks_added_to_cart' ) );
							if ( window.jQuery ) {
								window.jQuery( document.body ).trigger( 'wc_fragment_refresh' ).trigger( 'applied_coupon', [ config.code ] );
							}
						} else {
							applyButton.disabled = false;
							say( ( result.data && result.data.message ? plain( result.data.message ) : '' ) || config.failed, true );
						}
					} )
					.catch( function () {
						applyButton.disabled = false;
						say( config.failed, true );
					} );
			} );
		}
	}

	function start() {
		document.querySelectorAll( '.sb-coupon[data-config]' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
