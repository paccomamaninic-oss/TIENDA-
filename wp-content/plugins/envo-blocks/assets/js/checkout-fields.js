/**
 * Envo Blocks > Checkout Fields: drag rows to change the order, add and remove additional fields, show the options
 * box only for "Select", confirm the reset.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		const $form = $( '.eb-cf form' );
		const $rows = $form.find( '.eb-cf__rows' );
		const $add = $form.find( '.eb-cf__add' );
		const template = $form.find( '.eb-cf__template' ).get( 0 );
		let next = $rows.children().length;

		$form.find( '.eb-cf__sortable' ).sortable( {
			handle: '.eb-cf__handle',
			axis: 'y',
			placeholder: 'eb-cf__placeholder',
			helper: function ( event, row ) {
				// Keep the column widths while dragging.
				row.children().each( function () {
					$( this ).width( $( this ).width() );
				} );
				return row;
			},
		} );

		function refresh() {
			const count = $rows.children().length;
			$form.find( '.eb-cf__empty' ).prop( 'hidden', count > 0 );
			$add.prop( 'disabled', count >= Number( $add.data( 'max' ) || 30 ) );
		}

		$add.on( 'click', function () {
			const html = template.innerHTML.replace( /__i__/g, 'n' + next++ );
			const $row = $( html.trim() );
			$rows.append( $row );
			$row.find( 'input[type=text]' ).first().trigger( 'focus' );
			refresh();
		} );

		$rows.on( 'click', '.eb-cf__remove', function () {
			$( this ).closest( 'tr' ).remove();
			refresh();
		} );

		$rows.on( 'change', '.eb-cf__type', function () {
			$( this ).closest( 'tr' ).find( '.eb-cf__options' ).prop( 'hidden', 'select' !== this.value );
		} );

		$form.find( '.eb-cf__reset' ).on( 'click', function ( event ) {
			if ( ! window.confirm( $( this ).data( 'confirm' ) ) ) {
				event.preventDefault();
			}
		} );

		refresh();
	} );
} )( jQuery );
