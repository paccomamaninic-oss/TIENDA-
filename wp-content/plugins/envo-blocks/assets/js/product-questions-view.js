/**
 * Envo Blocks - Product Questions: search in the answered questions and "Show more questions".
 */
( function () {
	'use strict';

	function setup( root ) {
		var items = [].slice.call( root.querySelectorAll( '.sb-pq__item' ) );
		var more = root.querySelector( '.sb-pq__more' );
		var search = root.querySelector( '.sb-pq__search input' );
		var none = root.querySelector( '.sb-pq__none' );
		var perPage = parseInt( root.getAttribute( 'data-per-page' ), 10 ) || 5;
		var shown = perPage;

		function update() {
			var term = search ? search.value.trim().toLowerCase() : '';
			var visible = 0;
			items.forEach( function ( item, index ) {
				var match = ! term || -1 !== item.textContent.toLowerCase().indexOf( term );
				item.hidden = ! match || ( ! term && index >= shown );
				if ( ! item.hidden ) {
					visible++;
				}
			} );
			if ( more ) {
				more.parentNode.hidden = !! term || shown >= items.length;
			}
			if ( none ) {
				none.hidden = ! term || visible > 0;
			}
		}

		if ( more ) {
			more.addEventListener( 'click', function () {
				shown += perPage;
				update();
			} );
		}
		if ( search ) {
			search.addEventListener( 'input', update );
		}
	}

	function init() {
		document.querySelectorAll( '.sb-pq' ).forEach( setup );
		// After sending the form the page comes back with #questions-…: show the notice.
		var target = window.location.hash ? document.getElementById( window.location.hash.slice( 1 ) ) : null;
		if ( target && target.classList.contains( 'sb-pq' ) ) {
			var notice = target.querySelector( '.sb-pq__notice' );
			( notice || target ).scrollIntoView( { block: 'center' } );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
