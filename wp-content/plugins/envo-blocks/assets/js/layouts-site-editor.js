/**
 * Envo Blocks – Site Editor: a notice on a theme template whose main part a published store layout replaces on the
 * store (the header and footer of the template stay). Plain ES5 on the wp.* globals.
 */
( function ( wp ) {
	'use strict';

	var data = window.envoBlocksLayoutTemplates;
	if ( ! wp || ! wp.data || ! data || ! data.templates ) {
		return;
	}
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var NOTICE = 'envo-blocks-layout-template';
	var shown = '';

	function slugOf( id ) {
		// Template IDs are "<theme>//<slug>".
		var parts = String( id || '' ).split( '//' );
		return parts.length > 1 ? parts[ 1 ] : '';
	}

	function current() {
		var site = wp.data.select( 'core/edit-site' );
		var editor = wp.data.select( 'core/editor' );
		var type = ( editor && editor.getCurrentPostType && editor.getCurrentPostType() ) || ( site && site.getEditedPostType && site.getEditedPostType() );
		var id = ( editor && editor.getCurrentPostId && editor.getCurrentPostId() ) || ( site && site.getEditedPostId && site.getEditedPostId() );
		return 'wp_template' === type ? slugOf( id ) : '';
	}

	wp.data.subscribe( function () {
		var slug = current();
		if ( slug === shown ) {
			return;
		}
		shown = slug;
		var notices = wp.data.dispatch( 'core/notices' );
		notices.removeNotice( NOTICE );
		var info = data.templates[ slug ];
		if ( ! info ) {
			return;
		}
		var text = info.all
			/* translators: %s: store layout name. */
			? sprintf( __( 'On the store this page shows the store layout "%s": changes of the main part of this template are not visible there (the header and footer are).', 'envo-blocks' ), info.title )
			/* translators: %s: store layout name. */
			: sprintf( __( 'On the store some of these pages (chosen categories, tags or products) show the store layout "%s" instead of the main part of this template.', 'envo-blocks' ), info.title );
		notices.createNotice( 'warning', text, {
			id: NOTICE,
			isDismissible: true,
			actions: [
				{ label: __( 'Edit the layout', 'envo-blocks' ), url: info.editUrl },
				{ label: __( 'Store Layouts', 'envo-blocks' ), url: data.pageUrl },
			],
		} );
	} );
} )( window.wp );
