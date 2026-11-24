/**
 * Envo Blocks - review photos: the photo field of the review form (the form gets multipart, a preview, the limits)
 * and the photo window of the review thumbnails and of the Customer Photos block.
 */
( function () {
	'use strict';

	var i18n = window.envoBlocksReviewPhotos || {};
	var dialog = null;
	var list = [];
	var current = 0;

	function text( tag, className, value ) {
		var node = document.createElement( tag );
		node.className = className;
		node.textContent = value;
		return node;
	}

	/* ------------------------------------------------------------ the form */

	function setupField( input ) {
		var form = input.form;
		if ( form ) {
			form.enctype = 'multipart/form-data';
			form.encoding = 'multipart/form-data';
		}
		var max = parseInt( input.getAttribute( 'data-max' ), 10 ) || 3;
		var mb = parseInt( input.getAttribute( 'data-mb' ), 10 ) || 5;
		var preview = input.parentNode.querySelector( '.sb-rp-field__preview' );

		input.addEventListener( 'change', function () {
			var files = Array.prototype.slice.call( input.files || [] );
			var notes = [];
			var kept = files.filter( function ( file ) {
				return file.size <= mb * 1024 * 1024;
			} );
			if ( kept.length < files.length ) {
				notes.push( String( i18n.tooLarge || '' ).replace( '%d', mb ) );
			}
			if ( kept.length > max ) {
				kept = kept.slice( 0, max );
				notes.push( String( i18n.tooMany || '' ).replace( '%d', max ) );
			}
			if ( kept.length !== files.length && window.DataTransfer ) {
				var transfer = new window.DataTransfer();
				kept.forEach( function ( file ) {
					transfer.items.add( file );
				} );
				input.files = transfer.files;
			}
			if ( ! preview ) {
				return;
			}
			preview.textContent = '';
			kept.forEach( function ( file ) {
				var img = document.createElement( 'img' );
				img.alt = '';
				img.src = window.URL.createObjectURL( file );
				img.onload = function () {
					window.URL.revokeObjectURL( img.src );
				};
				preview.appendChild( img );
			} );
			if ( notes.length ) {
				preview.appendChild( text( 'span', 'sb-rp-field__note', notes.join( ' ' ) ) );
			}
		} );
	}

	/* ------------------------------------------------------------ the window */

	function build() {
		dialog = document.createElement( 'dialog' );
		dialog.className = 'sb-rp-dialog';
		dialog.innerHTML = '<div class="sb-rp-dialog__box"><button type="button" class="sb-rp-dialog__close"></button>'
			+ '<figure class="sb-rp-dialog__figure"><img alt="" /></figure><div class="sb-rp-dialog__info"></div>'
			+ '<button type="button" class="sb-rp-dialog__nav sb-rp-dialog__prev">‹</button><button type="button" class="sb-rp-dialog__nav sb-rp-dialog__next">›</button></div>';
		dialog.querySelector( '.sb-rp-dialog__close' ).setAttribute( 'aria-label', i18n.close || 'Close' );
		dialog.querySelector( '.sb-rp-dialog__close' ).textContent = '×';
		dialog.querySelector( '.sb-rp-dialog__prev' ).setAttribute( 'aria-label', i18n.prev || 'Previous' );
		dialog.querySelector( '.sb-rp-dialog__next' ).setAttribute( 'aria-label', i18n.next || 'Next' );
		dialog.querySelector( '.sb-rp-dialog__close' ).addEventListener( 'click', function () {
			dialog.close();
		} );
		dialog.querySelector( '.sb-rp-dialog__prev' ).addEventListener( 'click', function () {
			show( current - 1 );
		} );
		dialog.querySelector( '.sb-rp-dialog__next' ).addEventListener( 'click', function () {
			show( current + 1 );
		} );
		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog ) {
				dialog.close();
			}
		} );
		dialog.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				show( current - 1 );
			} else if ( 'ArrowRight' === event.key ) {
				show( current + 1 );
			}
		} );
		document.body.appendChild( dialog );
	}

	function show( index ) {
		current = ( index + list.length ) % list.length;
		var link = list[ current ];
		var img = dialog.querySelector( 'img' );
		var inner = link.querySelector( 'img' );
		img.src = link.href;
		img.alt = inner ? inner.alt : '';
		var box = dialog.querySelector( '.sb-rp-dialog__info' );
		box.textContent = '';
		var info = null;
		try {
			info = JSON.parse( link.getAttribute( 'data-sb-rp-info' ) || 'null' );
		} catch ( e ) {
			info = null;
		}
		dialog.classList.toggle( 'has-info', !! info );
		if ( info ) {
			if ( info.rating ) {
				var stars = text( 'span', 'sb-rp-dialog__stars', '★★★★★'.slice( 0, info.rating ) + '☆☆☆☆☆'.slice( 0, 5 - info.rating ) );
				stars.setAttribute( 'aria-label', info.rating + ' / 5' );
				box.appendChild( stars );
			}
			var who = text( 'p', 'sb-rp-dialog__author', info.author || '' );
			if ( info.verified ) {
				who.appendChild( text( 'span', 'sb-rp-dialog__verified', info.verified ) );
			}
			box.appendChild( who );
			if ( info.date ) {
				box.appendChild( text( 'p', 'sb-rp-dialog__date', info.date ) );
			}
			if ( info.text ) {
				box.appendChild( text( 'p', 'sb-rp-dialog__text', info.text ) );
			}
			if ( info.url ) {
				var a = text( 'a', 'sb-rp-dialog__product', info.product );
				a.href = info.url;
				box.appendChild( a );
			}
		}
		dialog.classList.toggle( 'is-single', list.length < 2 );
	}

	function open( link ) {
		if ( ! window.HTMLDialogElement ) {
			return false;
		}
		if ( ! dialog ) {
			build();
		}
		var group = link.closest( '.sb-rp-thumbs, .sb-rp__grid' ) || link.parentNode;
		list = Array.prototype.slice.call( group.querySelectorAll( '[data-sb-rp-photo]' ) );
		show( Math.max( 0, list.indexOf( link ) ) );
		if ( ! dialog.open ) {
			dialog.showModal();
		}
		return true;
	}

	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( '[data-sb-rp-photo]' ) : null;
		if ( link && ! event.metaKey && ! event.ctrlKey && open( link ) ) {
			event.preventDefault();
		}
	} );

	function boot() {
		document.querySelectorAll( 'input[name="envo_blocks_review_photos[]"]' ).forEach( setupField );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
