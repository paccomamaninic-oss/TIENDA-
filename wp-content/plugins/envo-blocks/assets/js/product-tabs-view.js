/**
 * Envo Blocks - Product Tabs: switches the tabs and the accordion.
 *
 * - Tabs: one panel open; arrows, Home and End move between the tabs (WAI-ARIA tabs pattern).
 * - Accordion (the layout, or the tabs on phones): the headings open and close their panel; one at a time unless
 *   data-multiple="1".
 * - Links to an element inside a closed panel (#reviews from the rating link, #tab-reviews, #comment-12) and the same
 *   hash in the address open that panel first.
 */
( function () {
	'use strict';

	function init( root ) {
		var nav = root.querySelector( ':scope > .sb-ptabs__nav' );
		var tabs = nav ? Array.prototype.slice.call( nav.querySelectorAll( ':scope > .sb-ptabs__tab' ) ) : [];
		var items = Array.prototype.slice.call( root.querySelectorAll( ':scope > .sb-ptabs__panels > .sb-ptabs__item' ) );
		var multiple = '1' === root.getAttribute( 'data-multiple' );

		function panel( item ) {
			return item.querySelector( ':scope > .sb-ptabs__panel' );
		}

		/** Accordion now: the layout, or the tabs with their bar hidden on a phone. */
		function isAccordion() {
			return ! nav || 'none' === window.getComputedStyle( nav ).display;
		}

		function setOpen( index, open ) {
			var item = items[ index ];
			if ( ! item ) {
				return;
			}
			item.classList.toggle( 'is-open', open );
			panel( item ).hidden = ! open;
			var toggle = item.querySelector( '.sb-ptabs__toggle' );
			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			}
			if ( tabs[ index ] ) {
				tabs[ index ].classList.toggle( 'is-active', open );
				tabs[ index ].setAttribute( 'aria-selected', open ? 'true' : 'false' );
				tabs[ index ].setAttribute( 'tabindex', open ? '0' : '-1' );
			}
		}

		/** Opens one panel and closes the others (tabs, or a single-open accordion). */
		function show( index ) {
			items.forEach( function ( item, i ) {
				setOpen( i, i === index );
			} );
		}

		tabs.forEach( function ( tab, index ) {
			tab.addEventListener( 'click', function () {
				show( index );
			} );
			tab.addEventListener( 'keydown', function ( event ) {
				var vertical = 'vertical' === nav.getAttribute( 'aria-orientation' );
				var next = null;
				if ( ( vertical ? 'ArrowDown' : 'ArrowRight' ) === event.key ) {
					next = ( index + 1 ) % tabs.length;
				} else if ( ( vertical ? 'ArrowUp' : 'ArrowLeft' ) === event.key ) {
					next = ( index - 1 + tabs.length ) % tabs.length;
				} else if ( 'Home' === event.key ) {
					next = 0;
				} else if ( 'End' === event.key ) {
					next = tabs.length - 1;
				}
				if ( null === next ) {
					return;
				}
				// Right to left: the arrows of a horizontal bar go the other way.
				if ( ! vertical && 'rtl' === window.getComputedStyle( nav ).direction && /Arrow/.test( event.key ) ) {
					next = 'ArrowRight' === event.key ? ( index - 1 + tabs.length ) % tabs.length : ( index + 1 ) % tabs.length;
				}
				event.preventDefault();
				show( next );
				tabs[ next ].focus();
			} );
		} );

		items.forEach( function ( item, index ) {
			var toggle = item.querySelector( '.sb-ptabs__toggle' );
			if ( ! toggle ) {
				return;
			}
			toggle.addEventListener( 'click', function () {
				var open = ! item.classList.contains( 'is-open' );
				if ( open && ! multiple ) {
					show( index );
				} else {
					setOpen( index, open );
				}
			} );
		} );

		// Back from a phone to the tabs: exactly one panel open.
		if ( nav ) {
			var wasAccordion = isAccordion();
			window.addEventListener( 'resize', function () {
				var now = isAccordion();
				if ( now === wasAccordion ) {
					return;
				}
				wasAccordion = now;
				if ( ! now ) {
					var first = items.findIndex( function ( item ) {
						return item.classList.contains( 'is-open' );
					} );
					show( first < 0 ? 0 : first );
				}
			} );
		}

		/** Opens the panel with the element of an id. */
		root.openTarget = function ( id ) {
			var target = id ? document.getElementById( id ) : null;
			if ( ! target || ! root.contains( target ) ) {
				return false;
			}
			var index = items.findIndex( function ( item ) {
				return item.contains( target );
			} );
			if ( index < 0 ) {
				return false;
			}
			if ( ! items[ index ].classList.contains( 'is-open' ) ) {
				if ( multiple && isAccordion() ) {
					setOpen( index, true );
				} else {
					show( index );
				}
			}
			return true;
		};
	}

	function idOf( hash ) {
		try {
			return hash && hash.length > 1 ? decodeURIComponent( hash.slice( 1 ) ) : '';
		} catch ( e ) {
			return '';
		}
	}

	/** Opens a panel for the id; true when one of the blocks has it. */
	function openFor( id ) {
		var found = false;
		document.querySelectorAll( '.sb-ptabs' ).forEach( function ( root ) {
			if ( ! found && root.openTarget ) {
				found = root.openTarget( id );
			}
		} );
		return found;
	}

	function start() {
		document.querySelectorAll( '.sb-ptabs' ).forEach( init );

		var id = idOf( window.location.hash );
		if ( openFor( id ) ) {
			var target = document.getElementById( id );
			if ( target ) {
				target.scrollIntoView();
			}
		}

		window.addEventListener( 'hashchange', function () {
			openFor( idOf( window.location.hash ) );
		} );

		// Same-page links (the "(3 customer reviews)" link of WooCommerce points to #reviews).
		document.addEventListener( 'click', function ( event ) {
			var link = event.target.closest ? event.target.closest( 'a[href*="#"]' ) : null;
			if ( ! link || link.closest( '.sb-ptabs__nav' ) ) {
				return;
			}
			var url = new URL( link.href, window.location.href );
			if ( url.pathname !== window.location.pathname || url.search !== window.location.search ) {
				return;
			}
			var target = idOf( url.hash );
			if ( openFor( target ) ) {
				var element = document.getElementById( target );
				if ( element ) {
					event.preventDefault();
					element.scrollIntoView( { behavior: 'smooth', block: 'start' } );
					if ( window.history && window.history.replaceState ) {
						window.history.replaceState( null, '', url.hash );
					}
				}
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
