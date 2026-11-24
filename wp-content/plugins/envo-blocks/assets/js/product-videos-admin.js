/**
 * Envo Blocks - Product data > Videos: add and remove video rows, pick a video file and a cover image in the
 * Media Library.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		const $panel = $( '#envo_blocks_videos_data' );
		if ( ! $panel.length ) {
			return;
		}
		const i18n = window.envoBlocksVideos || {};
		const $rows = $panel.find( '.eb-pv-admin__rows' );
		const $add = $panel.find( '.eb-pv-admin__add' );
		const template = $panel.find( '.eb-pv-admin__template' ).get( 0 );
		const max = Number( $rows.data( 'max' ) ) || 5;
		let next = $rows.children().length;

		function refresh() {
			$add.prop( 'disabled', $rows.children().length >= max );
		}

		function pick( title, type, done ) {
			const frame = wp.media( {
				title: title,
				library: { type: type },
				button: { text: i18n.use || 'Use' },
				multiple: false,
			} );
			frame.on( 'select', function () {
				done( frame.state().get( 'selection' ).first().toJSON() );
			} );
			frame.open();
		}

		$add.on( 'click', function () {
			$rows.append( $( template.innerHTML.replace( /__i__/g, 'n' + next++ ).trim() ) );
			$rows.children().last().find( '.eb-pv-admin__url' ).trigger( 'focus' );
			refresh();
		} );

		$rows.on( 'click', '.eb-pv-admin__remove', function () {
			$( this ).closest( '.eb-pv-admin__row' ).remove();
			refresh();
		} );

		$rows.on( 'click', '.eb-pv-admin__file', function () {
			const $row = $( this ).closest( '.eb-pv-admin__row' );
			pick( i18n.video, 'video', function ( file ) {
				$row.find( '.eb-pv-admin__url' ).val( file.url ).trigger( 'change' );
			} );
		} );

		$rows.on( 'click', '.eb-pv-admin__choose', function () {
			const $row = $( this ).closest( '.eb-pv-admin__row' );
			pick( i18n.image, 'image', function ( image ) {
				const small = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
				$row.find( '.eb-pv-admin__poster' ).val( image.id );
				$row.find( '.eb-pv-admin__preview' ).html( $( '<img alt="">' ).attr( 'src', small ) );
				$row.find( '.eb-pv-admin__clear' ).prop( 'hidden', false );
			} );
		} );

		$rows.on( 'click', '.eb-pv-admin__clear', function () {
			const $row = $( this ).closest( '.eb-pv-admin__row' );
			$row.find( '.eb-pv-admin__poster' ).val( '' );
			$row.find( '.eb-pv-admin__preview' ).empty();
			$( this ).prop( 'hidden', true );
		} );

		refresh();
	} );
} )( jQuery );
