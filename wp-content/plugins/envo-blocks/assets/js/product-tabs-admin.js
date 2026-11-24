/**
 * Envo Blocks - Product data > Tabs: add, move and remove tab rows; every text gets the WordPress editor
 * (Visual / Text).
 */
( function ( $ ) {
	'use strict';

	$( function () {
		const $panel = $( '#envo_blocks_tabs_data' );
		if ( ! $panel.length ) {
			return;
		}
		const $rows = $panel.find( '.eb-pt-admin__rows' );
		const $add = $panel.find( '.eb-pt-admin__add' );
		const template = $panel.find( '.eb-pt-admin__template' ).get( 0 );
		const max = Number( $rows.data( 'max' ) ) || 10;
		let next = $rows.children().length;

		function editorOn( textarea ) {
			if ( window.wp && wp.editor && wp.editor.initialize ) {
				wp.editor.initialize( textarea.id, {
					tinymce: { wpautop: true, plugins: 'lists,paste,wordpress,wplink,wptextpattern', toolbar1: 'formatselect,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,undo,redo' },
					quicktags: true,
					mediaButtons: true,
				} );
			}
		}

		function editorOff( $row ) {
			$row.find( '.eb-pt-admin__text' ).each( function () {
				if ( window.wp && wp.editor && wp.editor.remove ) {
					wp.editor.remove( this.id );
				}
			} );
		}

		function refresh() {
			$add.prop( 'disabled', $rows.children().length >= max );
		}

		$rows.find( '.eb-pt-admin__text' ).each( function () {
			editorOn( this );
		} );
		refresh();

		$add.on( 'click', function () {
			const $row = $( template.innerHTML.replace( /__i__/g, 'n' + next++ ).trim() );
			$rows.append( $row );
			editorOn( $row.find( '.eb-pt-admin__text' ).get( 0 ) );
			$row.find( '.eb-pt-admin__title' ).trigger( 'focus' );
			refresh();
		} );

		$rows.on( 'click', '.eb-pt-admin__remove', function () {
			const $row = $( this ).closest( '.eb-pt-admin__row' );
			editorOff( $row );
			$row.remove();
			refresh();
		} );

		// Moving a row with TinyMCE inside needs the editor rebuilt (an iframe loses its content when moved).
		$rows.on( 'click', '.eb-pt-admin__up, .eb-pt-admin__down', function () {
			const $row = $( this ).closest( '.eb-pt-admin__row' );
			const up = $( this ).hasClass( 'eb-pt-admin__up' );
			const $other = up ? $row.prev() : $row.next();
			if ( ! $other.length ) {
				return;
			}
			if ( window.tinymce ) {
				window.tinymce.triggerSave();
			}
			editorOff( $row );
			if ( up ) {
				$row.insertBefore( $other );
			} else {
				$row.insertAfter( $other );
			}
			editorOn( $row.find( '.eb-pt-admin__text' ).get( 0 ) );
		} );

		$panel.closest( 'form' ).on( 'submit', function () {
			if ( window.tinymce ) {
				window.tinymce.triggerSave();
			}
		} );
	} );
}( jQuery ) );
