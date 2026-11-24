/**
 * Envo Blocks - Product Filter: the price slider (two range inputs over one track, synced with the fields; a value at
 * the end of the range is not sent), "Show more" and the dropdown look. The options are links, so the rest works
 * without JavaScript. Envo Blocks PRO loads the results without reloading the page: it catches the event
 * "eb-filter-navigate" and calls window.envoBlocksFilters.init( root ) on the new filters.
 */
( function () {
	'use strict';

	function price( form ) {
		var slider = form.querySelector( '.eb-filter__slider' );
		var lo = form.querySelector( '.eb-filter__range--min' );
		var hi = form.querySelector( '.eb-filter__range--max' );
		var min = form.querySelector( 'input[name="min_price"]' );
		var max = form.querySelector( 'input[name="max_price"]' );
		if ( ! slider || ! lo || ! hi || ! min || ! max || form.ebFilter ) {
			return;
		}
		form.ebFilter = true;
		slider.hidden = false;
		var fill = document.createElement( 'span' );
		fill.className = 'eb-filter__slider-fill';
		slider.insertBefore( fill, slider.firstChild );
		var low = parseFloat( form.getAttribute( 'data-min' ) );
		var high = parseFloat( form.getAttribute( 'data-max' ) );

		function paint() {
			var a = ( parseFloat( lo.value ) - low ) / ( high - low ) * 100;
			var b = ( parseFloat( hi.value ) - low ) / ( high - low ) * 100;
			fill.style.left = a + '%';
			fill.style.width = Math.max( 0, b - a ) + '%';
		}
		function fromRange( event ) {
			if ( parseFloat( lo.value ) > parseFloat( hi.value ) ) {
				if ( event.target === lo ) {
					lo.value = hi.value;
				} else {
					hi.value = lo.value;
				}
			}
			min.value = lo.value;
			max.value = hi.value;
			paint();
		}
		function fromField() {
			lo.value = min.value === '' ? low : min.value;
			hi.value = max.value === '' ? high : max.value;
			paint();
		}
		lo.addEventListener( 'input', fromRange );
		hi.addEventListener( 'input', fromRange );
		min.addEventListener( 'input', fromField );
		max.addEventListener( 'input', fromField );
		form.addEventListener( 'submit', function () {
			// The ends of the range are no filter.
			min.disabled = min.value === '' || parseFloat( min.value ) <= low;
			max.disabled = max.value === '' || parseFloat( max.value ) >= high;
			window.setTimeout( function () {
				min.disabled = false;
				max.disabled = false;
			}, 0 );
		} );
		paint();
	}

	function more( button ) {
		if ( button.ebFilter ) {
			return;
		}
		button.ebFilter = true;
		var label = button.textContent;
		button.addEventListener( 'click', function () {
			var items = button.previousElementSibling.querySelectorAll( ':scope > .is-more' );
			var show = items.length > 0 && items[ 0 ].hidden;
			items.forEach( function ( item ) {
				item.hidden = ! show;
			} );
			button.textContent = show ? button.getAttribute( 'data-less' ) : label;
		} );
	}

	function select( field ) {
		if ( field.ebFilter ) {
			return;
		}
		field.ebFilter = true;
		field.addEventListener( 'change', function () {
			field.dispatchEvent( new CustomEvent( 'eb-filter-navigate', { bubbles: true, cancelable: true, detail: { url: field.value } } ) );
		} );
	}

	function init( root ) {
		root = root || document;
		root.querySelectorAll( '.eb-filter__price' ).forEach( price );
		root.querySelectorAll( '.eb-filter__more' ).forEach( more );
		root.querySelectorAll( '.eb-filter__select' ).forEach( select );
	}

	// A dropdown goes to its address, unless Envo Blocks PRO loads it without a reload (it cancels the event).
	window.addEventListener( 'eb-filter-navigate', function ( event ) {
		if ( ! event.defaultPrevented && event.detail && event.detail.url ) {
			window.location.href = event.detail.url;
		}
	} );

	window.envoBlocksFilters = { init: init };
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			init();
		} );
	} else {
		init();
	}
} )();
