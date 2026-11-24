/**
 * Envo Blocks - entrance animations: plays the animation of a block (.envo-anim) when it comes into view.
 * data-envo-anim = movement, -speed, -delay (ms), -repeat (every time it comes into view), -stagger (ms between the
 * items of the block, e.g. columns, products, gallery images), -on (devices). Plain ES5, no dependencies.
 */
( function () {
	'use strict';

	var root = document.documentElement;
	root.classList.add( 'envo-anim-js', 'envo-anim-on' );

	function device() {
		var w = window.innerWidth;
		return w >= 1025 ? 'desktop' : w >= 768 ? 'tablet' : 'mobile';
	}

	/** The items of a block for "one by one": the descendant with the most child elements (columns, products, images). */
	function itemsOf( block ) {
		var best = block;
		var bestCount = block.children.length;
		var level = [ block ];
		for ( var depth = 0; depth < 4; depth++ ) {
			var next = [];
			level.forEach( function ( node ) {
				Array.prototype.forEach.call( node.children, function ( child ) {
					if ( child.children.length > bestCount ) {
						best = child;
						bestCount = child.children.length;
					}
					next.push( child );
				} );
			} );
			level = next;
		}
		return bestCount > 1
			? Array.prototype.filter.call( best.children, function ( child ) {
					return 'SCRIPT' !== child.tagName && 'STYLE' !== child.tagName && 'TEMPLATE' !== child.tagName;
			  } )
			: [];
	}

	function done( el ) {
		el.classList.remove( 'envo-anim-play', 'envo-anim--' + el.getAttribute( 'data-envo-anim-name' ) );
		el.classList.add( 'envo-anim-done' );
	}

	function play( el, name, delay ) {
		el.setAttribute( 'data-envo-anim-name', name );
		el.style.setProperty( '--envo-anim-delay', delay + 'ms' );
		el.classList.remove( 'envo-anim-done' );
		el.classList.add( 'envo-anim--' + name, 'envo-anim-play' );
		var finished = false;
		var finish = function ( event ) {
			if ( finished || ( event && event.target !== el ) ) {
				return;
			}
			finished = true;
			el.removeEventListener( 'animationend', finish );
			done( el );
		};
		el.addEventListener( 'animationend', finish );
		// Safety net: the longest movement (slow) plus the delay.
		window.setTimeout( finish, delay + 2600 );
	}

	function init() {
		var blocks = Array.prototype.slice.call( document.querySelectorAll( '.envo-anim[data-envo-anim]' ) );
		if ( ! blocks.length ) {
			return;
		}
		var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var current = device();

		var entries = [];
		blocks.forEach( function ( block ) {
			var on = block.getAttribute( 'data-envo-anim-on' );
			if ( reduce || ( on && on.split( ' ' ).indexOf( current ) < 0 ) || ! ( 'IntersectionObserver' in window ) ) {
				block.classList.add( 'envo-anim-done' );
				return;
			}
			var step = parseInt( block.getAttribute( 'data-envo-anim-stagger' ), 10 );
			var items = step > 0 ? itemsOf( block ) : [];
			if ( items.length ) {
				block.classList.add( 'envo-anim-done' );
				items.forEach( function ( item ) {
					item.classList.add( 'envo-anim-item' );
				} );
			}
			entries.push( {
				block: block,
				name: block.getAttribute( 'data-envo-anim' ),
				delay: parseInt( block.getAttribute( 'data-envo-anim-delay' ), 10 ) || 0,
				repeat: block.hasAttribute( 'data-envo-anim-repeat' ),
				items: items,
				step: step || 0,
				shown: false,
			} );
		} );
		if ( ! entries.length ) {
			return;
		}

		var targets = function ( entry ) {
			return entry.items.length ? entry.items : [ entry.block ];
		};

		var observer = new window.IntersectionObserver(
			function ( changes ) {
				changes.forEach( function ( change ) {
					var entry = change.target.envoAnim;
					if ( ! entry ) {
						return;
					}
					if ( change.isIntersecting ) {
						if ( entry.shown ) {
							return;
						}
						entry.shown = true;
						targets( entry ).forEach( function ( el, index ) {
							play( el, entry.name, entry.delay + index * entry.step );
						} );
						if ( ! entry.repeat ) {
							observer.unobserve( entry.block );
						}
					} else if ( change.boundingClientRect.bottom < 0 && ! entry.shown && ! entry.repeat ) {
						// Already above the screen (the page opened lower down): show it without the movement.
						entry.shown = true;
						targets( entry ).forEach( done );
						observer.unobserve( entry.block );
					} else if ( entry.repeat && entry.shown ) {
						// Out of view: ready for the next time.
						entry.shown = false;
						targets( entry ).forEach( function ( el ) {
							el.classList.remove( 'envo-anim-done', 'envo-anim-play' );
						} );
					}
				} );
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0 }
		);

		entries.forEach( function ( entry ) {
			entry.block.envoAnim = entry;
			observer.observe( entry.block );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
