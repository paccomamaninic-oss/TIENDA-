/**
 * Envo Blocks > Invoices: the logo picker (media library).
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var $cell = $( '.eb-inv-logo' );
		var frame;

		$cell.on( 'click', '.eb-inv-logo__pick', function ( event ) {
			event.preventDefault();
			if ( ! frame ) {
				frame = wp.media( { library: { type: 'image' }, multiple: false } );
				frame.on( 'select', function () {
					var image = frame.state().get( 'selection' ).first().toJSON();
					var size = image.sizes && image.sizes.medium ? image.sizes.medium : image;
					$cell.find( 'input[type=hidden]' ).val( image.id );
					$cell.find( '.eb-inv-logo__img' ).html( $( '<img>', { src: size.url, alt: '', style: 'max-height:60px;width:auto;' } ) );
				} );
			}
			frame.open();
		} );

		$cell.on( 'click', '.eb-inv-logo__remove', function ( event ) {
			event.preventDefault();
			$cell.find( 'input[type=hidden]' ).val( '0' );
			$cell.find( '.eb-inv-logo__img' ).empty();
		} );
	} );
} )( jQuery );
