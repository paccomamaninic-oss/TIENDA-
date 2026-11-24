/**
 * Envo Blocks – links from wp-admin and the editor to envoblocks.com get utm_source=envo-blocks&utm_medium=plugin,
 * so the store knows the visitor came from the plugin (not from an affiliate). Nothing is collected or sent:
 * the parameters are only added to the link the user opens.
 */
( function () {
	var hosts = /(^|\.)envoblocks\.com$/i;

	function mark( event ) {
		var link = event.target && event.target.closest ? event.target.closest( 'a[href]' ) : null;
		if ( ! link ) {
			return;
		}
		var url;
		try {
			url = new URL( link.href );
		} catch ( e ) {
			return;
		}
		// Always set, also over a utm_source already in the link: a link added by other code (e.g. with an
		// affiliate's ?ref=) must not lose the mark. With the mark the store ignores ?ref= (no commission).
		if ( ! hosts.test( url.hostname ) ) {
			return;
		}
		url.searchParams.set( 'utm_source', 'envo-blocks' );
		url.searchParams.set( 'utm_medium', 'plugin' );
		link.href = url.toString();
	}

	// Before the browser follows the link: click, middle/ctrl click and "copy link address".
	[ 'mousedown', 'keydown', 'contextmenu' ].forEach( function ( type ) {
		document.addEventListener( type, mark, true );
	} );
} )();
