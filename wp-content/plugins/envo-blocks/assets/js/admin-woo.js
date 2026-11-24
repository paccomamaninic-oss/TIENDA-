/**
 * Envo Blocks - the "Install and activate WooCommerce" button of the message on the Envo Blocks pages and of the admin
 * notice on the other screens (whose close button is remembered per user). Uses the
 * installer of the template window (REST envo-blocks/v1/dependency), then reloads the page: the WooCommerce features
 * of Envo Blocks show up in the menu.
 */
( function ( wp ) {
	'use strict';

	const { __ } = wp.i18n;

	document.addEventListener( 'click', function ( event ) {
		// Closing the notice on the other admin screens: remembered for this user.
		const close = event.target.closest ? event.target.closest( '.eb-woo-admin-notice .notice-dismiss' ) : null;
		if ( close ) {
			const notice = close.closest( '.eb-woo-admin-notice' );
			const body = new window.FormData();
			body.append( 'action', 'envo_blocks_dismiss_woo' );
			body.append( 'nonce', notice.getAttribute( 'data-nonce' ) || '' );
			window.fetch( window.ajaxurl || '/wp-admin/admin-ajax.php', { method: 'POST', credentials: 'same-origin', body: body } );
			return;
		}

		const button = event.target.closest ? event.target.closest( '.eb-woo-notice__button' ) : null;
		if ( ! button || button.disabled ) {
			return;
		}
		const status = button.parentNode.querySelector( '.eb-woo-notice__status' );
		const activateOnly = '1' === button.getAttribute( 'data-activate' );

		button.disabled = true;
		status.classList.remove( 'is-error' );
		status.textContent = activateOnly ? __( 'Activating WooCommerce…', 'envo-blocks' ) : __( 'Installing and activating WooCommerce… This can take a minute.', 'envo-blocks' );

		wp.apiFetch( {
			path: '/envo-blocks/v1/dependency',
			method: 'POST',
			data: { type: 'plugin', slug: 'woocommerce', activate: true },
		} )
			.then( function ( result ) {
				if ( ! result || 'active' !== result.status ) {
					throw new Error( __( 'WooCommerce was installed but could not be activated. Activate it on the Plugins screen.', 'envo-blocks' ) );
				}
				status.textContent = __( 'WooCommerce is active. Reloading…', 'envo-blocks' );
				window.location.reload();
			} )
			.catch( function ( error ) {
				button.disabled = false;
				status.classList.add( 'is-error' );
				status.textContent = ( error && error.message ) || __( 'WooCommerce could not be installed. Install it on the Plugins screen.', 'envo-blocks' );
			} );
	} );
}( window.wp ) );
