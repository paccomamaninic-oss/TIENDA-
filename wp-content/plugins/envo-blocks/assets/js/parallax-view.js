/**
 * Envo Blocks - Parallax Section: moves (or zooms) the background while the section passes the screen.
 * Runs only for sections on the screen (IntersectionObserver) and once per frame (requestAnimationFrame).
 */
( function () {
	'use strict';

	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' );
	var phone = window.matchMedia && window.matchMedia( '(max-width: 781px)' );
	var sections = Array.prototype.slice.call( document.querySelectorAll( '.sb-plx:not(.sb-plx--none)' ) );
	if ( ! sections.length || ( reduce && reduce.matches ) ) {
		return;
	}

	var visible = [];
	var queued = false;

	function still( section ) {
		return section.classList.contains( 'sb-plx--still-mobile' ) && phone && phone.matches;
	}

	function update() {
		queued = false;
		var screen = window.innerHeight;
		visible.forEach( function ( section ) {
			var media = section.querySelector( '.sb-plx__media' );
			if ( ! media ) {
				return;
			}
			if ( still( section ) ) {
				media.style.transform = '';
				return;
			}
			var box = section.getBoundingClientRect();
			// -1 when the section enters at the bottom, 0 in the middle of the screen, 1 when it leaves at the top.
			var progress = ( screen / 2 - ( box.top + box.height / 2 ) ) / ( screen / 2 + box.height / 2 );
			progress = Math.max( -1, Math.min( 1, progress ) );
			if ( ! section.sbPlxSpeed ) {
				section.sbPlxSpeed = parseFloat( getComputedStyle( section ).getPropertyValue( '--sb-plx-speed' ) ) || 30;
			}
			var speed = section.sbPlxSpeed;
			if ( section.classList.contains( 'sb-plx--zoom' ) ) {
				// From 1 to 1 + speed / 200 (with reverse: from bigger to 1).
				var grow = ( progress + 1 ) / 2;
				if ( section.classList.contains( 'sb-plx--reverse' ) ) {
					grow = 1 - grow;
				}
				media.style.transform = 'scale(' + ( 1 + ( speed / 200 ) * grow ).toFixed( 4 ) + ')';
				return;
			}
			// The background moves by up to half of the extra size each way: slower than the page, or faster (reverse).
			var shift = ( box.height * speed ) / 200;
			var y = progress * shift * ( section.classList.contains( 'sb-plx--reverse' ) ? -1 : 1 );
			media.style.transform = 'translate3d(0,' + y.toFixed( 1 ) + 'px,0)';
		} );
	}

	function queue() {
		if ( ! queued ) {
			queued = true;
			window.requestAnimationFrame( update );
		}
	}

	if ( window.IntersectionObserver ) {
		var observer = new window.IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					var index = visible.indexOf( entry.target );
					if ( entry.isIntersecting && -1 === index ) {
						visible.push( entry.target );
					} else if ( ! entry.isIntersecting && -1 !== index ) {
						visible.splice( index, 1 );
					}
				} );
				queue();
			},
			{ rootMargin: '100px 0px' }
		);
		sections.forEach( function ( section ) {
			observer.observe( section );
		} );
	} else {
		visible = sections;
	}

	window.addEventListener( 'scroll', queue, { passive: true } );
	window.addEventListener( 'resize', queue );
	queue();
} )();
