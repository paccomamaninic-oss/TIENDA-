/**
 * Envo Blocks - the setup notices after activation (Envo_Blocks_Setup_Notices): closing one is remembered for the
 * user; closing "Store in 5 minutes" shows the theme notice in its place; the theme notice installs and activates
 * Envo Blocks Store with the installer of Templates Import (REST envo-blocks/v1/dependency).
 */
( function ( wp ) {
	'use strict';

	const { __, sprintf } = wp.i18n;

	function remember( notice ) {
		const body = new window.FormData();
		body.append( 'action', 'envo_blocks_dismiss_setup' );
		body.append( 'notice', notice.getAttribute( 'data-notice' ) || '' );
		body.append( 'nonce', notice.getAttribute( 'data-nonce' ) || '' );
		window.fetch( window.ajaxurl || '/wp-admin/admin-ajax.php', { method: 'POST', credentials: 'same-origin', body: body } );
	}

	document.addEventListener( 'click', function ( event ) {
		const close = event.target.closest ? event.target.closest( '.eb-setup-notice .notice-dismiss' ) : null;
		if ( close ) {
			const notice = close.closest( '.eb-setup-notice' );
			remember( notice );
			if ( 'wizard' === notice.getAttribute( 'data-notice' ) ) {
				// The theme notice takes its place (WordPress removes the closed one).
				const theme = document.querySelector( '.eb-theme-notice[hidden]' );
				if ( theme ) {
					theme.hidden = false;
				}
			}
			return;
		}

		const button = event.target.closest ? event.target.closest( '.eb-theme-notice__button' ) : null;
		if ( ! button || button.disabled ) {
			return;
		}
		const notice = button.closest( '.eb-theme-notice' );
		const status = notice.querySelector( '.eb-setup-notice__status' );
		const install = '1' === button.getAttribute( 'data-install' );
		const activate = '1' === notice.getAttribute( 'data-activate' );

		button.disabled = true;
		status.classList.remove( 'is-error' );
		status.textContent = install ? __( 'Installing…', 'envo-blocks' ) : __( 'Activating…', 'envo-blocks' );

		wp.apiFetch( {
			path: '/envo-blocks/v1/dependency',
			method: 'POST',
			data: { type: 'theme', slug: notice.getAttribute( 'data-slug' ), activate: activate },
		} )
			.then( function ( result ) {
				if ( result && 'active' === result.status ) {
					status.textContent = sprintf( /* translators: %s: plugin or theme name. */ __( '"%s" is active.', 'envo-blocks' ), 'Envo Blocks Store' );
					button.remove();
					return;
				}
				status.textContent = sprintf( /* translators: %s: plugin or theme name. */ __( '"%s" is installed.', 'envo-blocks' ), 'Envo Blocks Store' );
				button.remove();
			} )
			.catch( function ( error ) {
				button.disabled = false;
				status.classList.add( 'is-error' );
				status.textContent = ( error && error.message ) || __( 'Something went wrong. Please try again.', 'envo-blocks' );
			} );
	} );
}( window.wp ) );
