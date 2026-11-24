/**
 * Envo Blocks - Sticky Column: the space taken by the admin bar and a sticky header ("auto" offset), and content
 * taller than the screen that floats – it sticks at its bottom while the page scrolls down and at its top while it
 * scrolls up (position: sticky with a moving "top"). Runs once per frame, only while scrolling or resizing.
 */
( function () {
	'use strict';

	var blocks = Array.prototype.slice.call( document.querySelectorAll( '.sb-sticky' ) );
	if ( ! blocks.length ) {
		return;
	}

	var HEADERS = '.wp-site-blocks > header, header.wp-block-template-part, #masthead, .site-header, body > header';
	var state = blocks.map( function () {
		return { top: null };
	} );
	var lastY = window.pageYOffset;
	var queued = false;

	function fixed( el ) {
		var position = el ? getComputedStyle( el ).position : '';
		return 'fixed' === position || 'sticky' === position;
	}

	// Height at the top of the screen covered by the admin bar and a sticky or fixed header (0 when it scrolled away).
	function covered( withHeader ) {
		var bar = document.getElementById( 'wpadminbar' );
		var top = bar && fixed( bar ) ? bar.getBoundingClientRect().bottom : 0;
		if ( withHeader ) {
			var header = document.querySelector( HEADERS );
			if ( header && fixed( header ) ) {
				var box = header.getBoundingClientRect();
				// Only a header at the top of the screen (not one that scrolled away or hides itself).
				if ( box.bottom > top && box.top <= top + 1 ) {
					top = box.bottom;
				}
			}
		}
		return Math.max( 0, Math.round( top ) );
	}

	function update() {
		queued = false;
		var y = window.pageYOffset;
		var dy = y - lastY;
		lastY = y;
		var screen = window.innerHeight;
		blocks.forEach( function ( el, i ) {
			var css = getComputedStyle( el );
			if ( 'sticky' !== css.position ) {
				// Phones (or tablets): not sticky.
				el.style.top = '';
				state[ i ].top = null;
				return;
			}
			var auto = covered( 'auto' === el.getAttribute( 'data-sb-sticky' ) );
			el.style.setProperty( '--sb-sticky-auto', auto + 'px' );
			var gap = parseFloat( css.getPropertyValue( '--sb-sticky-top' ) ) || 0;
			var bottom = parseFloat( css.getPropertyValue( '--sb-sticky-bottom' ) ) || 0;
			var highest = auto + gap;
			var height = el.offsetHeight;
			if ( height + highest + bottom <= screen ) {
				// Fits on the screen: the CSS top.
				el.style.top = '';
				state[ i ].top = null;
				return;
			}
			// Taller than the screen: move between its top edge (scrolling up) and its bottom edge (scrolling down).
			var lowest = screen - height - bottom;
			var top = null === state[ i ].top ? highest : state[ i ].top - dy;
			top = Math.min( highest, Math.max( lowest, top ) );
			state[ i ].top = top;
			el.style.top = Math.round( top ) + 'px';
		} );
	}

	function queue() {
		if ( ! queued ) {
			queued = true;
			window.requestAnimationFrame( update );
		}
	}

	window.addEventListener( 'scroll', queue, { passive: true } );
	window.addEventListener( 'resize', queue );
	if ( window.ResizeObserver ) {
		// Content that grows later (images, a filter that opens) changes what fits on the screen.
		var observer = new window.ResizeObserver( queue );
		blocks.forEach( function ( el ) {
			observer.observe( el );
		} );
	}
	queue();
} )();
