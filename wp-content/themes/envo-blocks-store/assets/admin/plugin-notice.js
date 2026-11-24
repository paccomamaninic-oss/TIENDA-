/* global envoBlocksStorePluginNotice */
( function () {
	'use strict';

	var config = window.envoBlocksStorePluginNotice;
	if ( ! config ) {
		return;
	}

	function post( action ) {
		var data = new window.FormData();
		data.append( 'action', action );
		data.append( 'nonce', config.nonce );
		return window
			.fetch( config.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' } )
			.then( function ( response ) {
				return response.json();
			} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.ebs-plugin-notice__button' );

		if ( button ) {
			// After a failure the button is a plain link to the core install / activate screen.
			if ( button.hasAttribute( 'data-fallback' ) ) {
				return;
			}
			event.preventDefault();
			if ( button.classList.contains( 'is-busy' ) ) {
				return;
			}

			var notice = button.closest( '.ebs-plugin-notice, .ebs-about__plugin' );
			var message = notice.querySelector( '.ebs-plugin-notice__message' );
			var label = button.textContent;

			button.classList.add( 'is-busy' );
			button.setAttribute( 'aria-disabled', 'true' );
			button.textContent = 'missing' === button.getAttribute( 'data-state' ) ? config.installing : config.activating;
			message.textContent = '';

			post( 'envo_blocks_store_install_plugin' )
				.then( function ( result ) {
					if ( result && result.success ) {
						button.textContent = config.done;
						window.location.href = result.data.redirect;
						return;
					}
					throw new Error( result && result.data && result.data.message ? result.data.message : '' );
				} )
				.catch( function ( error ) {
					button.classList.remove( 'is-busy' );
					button.removeAttribute( 'aria-disabled' );
					button.setAttribute( 'data-fallback', '1' );
					button.textContent = 'missing' === button.getAttribute( 'data-state' ) ? config.manual : label;
					message.textContent = config.failed + ( error && error.message ? ' ' + error.message : '' );
					notice.classList.add( 'has-error' );
				} );
			return;
		}

		// WordPress adds the dismiss button to .is-dismissible notices.
		if ( event.target.closest( '.ebs-plugin-notice .notice-dismiss' ) ) {
			post( 'envo_blocks_store_dismiss_plugin_notice' );
		}
	} );
} )();
