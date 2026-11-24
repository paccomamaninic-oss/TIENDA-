/**
 * Envo Blocks - Wishlist view script.
 *
 * Keeps the visitor's wishlist (guests: localStorage, customers: their account through the REST route, the guest list
 * is merged in after signing in) and drives the hearts (Wishlist Button blocks) and the Wishlist block on the wishlist
 * page. The products of the lists come from the REST route envo-blocks/v1/wishlist/items; Add to cart uses the
 * WooCommerce Store API and tells the other carts (mini carts) about it.
 *
 * Other scripts build on window.envoBlocksWishlist: the list (ids, has, add, remove, addMany), the helpers
 * (fetchItems, addToCart, showToast …) and hooks: on( 'refresh' | 'removeRow' | 'cartAdded' | 'click', fn ).
 * Plain JavaScript, no dependencies.
 */
( function () {
	'use strict';

	var data = window.envoBlocksWishlistData || {};
	var i18n = data.i18n || {};
	var KEY = 'envoBlocksWishlist';
	var ids = []; // Oldest first.
	var cartNonce = '';
	var toastEl, toastTimer, liveEl;
	var hooks = { refresh: [], removeRow: [], cartAdded: [], click: [] };

	/* ------------------------------------------------------------ helpers */

	function run( name, args ) {
		var handled = false;
		hooks[ name ].forEach( function ( fn ) {
			handled = true === fn.apply( null, args || [] ) || handled;
		} );
		return handled;
	}

	function format( template, value ) {
		return String( template || '' ).replace( '%s', value ).replace( '%d', value );
	}

	function countText( n ) {
		// Czech-like plural forms work for English too (1 / 2-4 / 5+ all use the right _n() string there).
		return format( 1 === n ? i18n.one : ( n >= 2 && n <= 4 ? i18n.few : i18n.many ), n );
	}

	function announce( text ) {
		if ( ! liveEl ) {
			liveEl = document.createElement( 'div' );
			liveEl.className = 'sb-wl-sr';
			liveEl.setAttribute( 'aria-live', 'polite' );
			document.body.appendChild( liveEl );
		}
		liveEl.textContent = '';
		window.setTimeout( function () {
			liveEl.textContent = text;
		}, 50 );
	}

	function has( id ) {
		return -1 !== ids.indexOf( id );
	}

	function newestFirst( list ) {
		return list.slice().reverse();
	}

	/* ------------------------------------------------------------ storage */

	function readLocal() {
		try {
			var saved = JSON.parse( window.localStorage.getItem( KEY ) || '[]' );
			return Array.isArray( saved ) ? saved.map( Number ).filter( function ( n ) {
				return n > 0;
			} ) : [];
		} catch ( e ) {
			return [];
		}
	}

	function writeLocal( list ) {
		try {
			if ( list.length ) {
				window.localStorage.setItem( KEY, JSON.stringify( list ) );
			} else {
				window.localStorage.removeItem( KEY );
			}
		} catch ( e ) {}
	}

	function api( body ) {
		return window.fetch( data.rest, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': data.nonce },
			body: JSON.stringify( body ),
		} ).then( function ( response ) {
			return response.json().then( function ( json ) {
				if ( ! response.ok ) {
					throw json;
				}
				return json;
			} );
		} );
	}

	/** Customers: the list from the server wins (it may have changed in another tab or device). */
	function serverSync( promise ) {
		return promise.then( function ( json ) {
			if ( json && Array.isArray( json.ids ) ) {
				var changed = json.ids.join( ',' ) !== ids.join( ',' );
				ids = json.ids;
				if ( changed ) {
					refresh();
				}
			}
		} );
	}

	function setIds( next, change ) {
		ids = next.slice( -( data.max || 200 ) );
		if ( data.loggedIn ) {
			serverSync( api( change ) ).catch( function () {
				announce( i18n.error );
			} );
		} else {
			writeLocal( ids );
		}
		refresh();
	}

	function add( id ) {
		if ( ! has( id ) ) {
			setIds( ids.concat( id ), { add: id } );
		}
	}

	function remove( id ) {
		if ( has( id ) ) {
			setIds( ids.filter( function ( n ) {
				return n !== id;
			} ), { remove: id } );
		}
	}

	function addMany( list ) {
		var next = ids.slice();
		list.forEach( function ( id ) {
			if ( -1 === next.indexOf( id ) ) {
				next.push( id );
			}
		} );
		setIds( next, { merge: list } );
	}

	/* ------------------------------------------------------------ everything that shows the list */

	function refresh() {
		updateToggles();
		updatePages();
		run( 'refresh', [ ids.slice() ] );
		document.dispatchEvent( new window.CustomEvent( 'envo-blocks-wishlist-changed', { detail: { ids: ids.slice() } } ) );
	}

	function updateToggles() {
		document.querySelectorAll( '.sb-wl-toggle[data-sb-wl-id]' ).forEach( function ( button ) {
			var id = parseInt( button.getAttribute( 'data-sb-wl-id' ), 10 );
			var on = has( id );
			var name = button.getAttribute( 'data-sb-wl-name' ) || '';
			button.classList.toggle( 'is-active', on );
			button.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
			button.setAttribute( 'aria-label', format( on ? i18n.remove : i18n.add, name ) );
			var text = button.querySelector( '.sb-wlb__text' );
			if ( text ) {
				text.textContent = on ? text.getAttribute( 'data-added' ) : text.getAttribute( 'data-add' );
			}
		} );
	}

	/* ------------------------------------------------------------ product lists */

	function fetchItems( list, opts ) {
		var params = [ 'ids=' + list.join( ',' ) ];
		Object.keys( opts || {} ).forEach( function ( key ) {
			params.push( key + '=' + ( opts[ key ] ? 1 : 0 ) );
		} );
		return window.fetch( data.rest + '/items?' + params.join( '&' ), { credentials: 'same-origin' } ).then( function ( response ) {
			if ( ! response.ok ) {
				throw response;
			}
			return response.json();
		} );
	}

	function pageConfig( page ) {
		try {
			return JSON.parse( page.getAttribute( 'data-sb-wl-page' ) ) || {};
		} catch ( e ) {
			return {};
		}
	}

	/** Shows the products of a Wishlist block; fetches them only when the list differs from what is shown. */
	function updatePage( page ) {
		var config = pageConfig( page );
		var list = config.shared && config.shared.length ? config.shared : newestFirst( ids );
		var key = list.join( ',' );
		var ul = page.querySelector( '.sb-wl-list' );

		if ( page.getAttribute( 'data-sb-wl-rendered' ) === key && ! page.classList.contains( 'is-loading' ) ) {
			setPageCount( page );
			return;
		}
		page.setAttribute( 'data-sb-wl-rendered', key );

		if ( ! list.length ) {
			ul.innerHTML = '';
			page.classList.remove( 'is-loading' );
			setPageCount( page );
			return;
		}

		ul.setAttribute( 'aria-busy', 'true' );
		fetchItems( list, config.opts ).then( function ( json ) {
			if ( page.getAttribute( 'data-sb-wl-rendered' ) !== key ) {
				return; // A newer list is on its way.
			}
			ul.innerHTML = json.html || '';
			// Products that no longer exist drop out of the list.
			if ( ! config.shared || ! config.shared.length ) {
				var gone = list.filter( function ( id ) {
					return -1 === ( json.ids || [] ).indexOf( id );
				} );
				if ( gone.length && json.ids ) {
					forget( gone );
				}
			}
		} ).catch( function () {} ).then( function () {
			ul.removeAttribute( 'aria-busy' );
			page.classList.remove( 'is-loading' );
			setPageCount( page );
		} );
	}

	/** Removes products that cannot be shown any more, without re-rendering the lists. */
	function forget( gone ) {
		ids = ids.filter( function ( id ) {
			return -1 === gone.indexOf( id );
		} );
		if ( data.loggedIn ) {
			gone.forEach( function ( id ) {
				api( { remove: id } ).catch( function () {} );
			} );
		} else {
			writeLocal( ids );
		}
		run( 'refresh', [ ids.slice() ] );
	}

	function setPageCount( page ) {
		var n = page.querySelectorAll( '.sb-wl-item:not(.sb-wl-item--skeleton):not(.is-leaving)' ).length;
		page.classList.toggle( 'is-empty', 0 === n && ! page.classList.contains( 'is-loading' ) );
		var count = page.querySelector( '.sb-wl-page__count' );
		if ( count ) {
			count.textContent = n ? countText( n ) : '';
		}
	}

	function updatePages() {
		document.querySelectorAll( '.sb-wl-page[data-sb-wl-page]' ).forEach( updatePage );
	}

	/** Takes a product out of the lists on the screen at once (the hearts and the counts follow from refresh()). */
	function removeRow( id ) {
		document.querySelectorAll( '.sb-wl-list [data-sb-wl-item="' + id + '"]' ).forEach( function ( row ) {
			row.classList.add( 'is-leaving' );
			window.setTimeout( function () {
				var page = row.closest( '.sb-wl-page' );
				if ( row.parentNode ) {
					row.parentNode.removeChild( row );
				}
				if ( page ) {
					setPageCount( page );
				}
			}, 250 );
		} );
		var key = newestFirst( ids.filter( function ( n ) {
			return n !== id;
		} ) ).join( ',' );
		document.querySelectorAll( '.sb-wl-page[data-sb-wl-page]' ).forEach( function ( page ) {
			var config = pageConfig( page );
			if ( ! config.shared || ! config.shared.length ) {
				page.setAttribute( 'data-sb-wl-rendered', key );
			}
		} );
		run( 'removeRow', [ id, key ] );
	}

	/* ------------------------------------------------------------ add to cart (Store API) */

	function cartRequest( path, body, retried ) {
		var options = { credentials: 'same-origin', headers: { 'Content-Type': 'application/json' } };
		if ( body ) {
			options.method = 'POST';
			options.body = JSON.stringify( body );
			options.headers.Nonce = cartNonce;
		}
		return window.fetch( data.cartApi + path, options ).then( function ( response ) {
			var next = response.headers.get( 'Nonce' );
			if ( next ) {
				cartNonce = next;
			}
			if ( body && ( 401 === response.status || 403 === response.status ) && ! retried ) {
				return cartRequest( '', null ).then( function () {
					return cartRequest( path, body, true );
				} );
			}
			return response.json().then( function ( json ) {
				if ( ! response.ok ) {
					throw json;
				}
				return json;
			} );
		} );
	}

	function addToCart( id ) {
		var ready = cartNonce ? window.Promise.resolve() : cartRequest( '', null );
		return ready.then( function () {
			return cartRequest( '/add-item', { id: id, quantity: 1 } );
		} );
	}

	function cartChanged() {
		document.body.dispatchEvent( new window.CustomEvent( 'wc-blocks_added_to_cart', { bubbles: true, cancelable: true } ) );
		if ( window.jQuery ) {
			window.jQuery( document.body ).trigger( 'wc_fragment_refresh' );
		}
	}

	/** After a product went to the cart from a list: "Remove after adding to the cart" of the Wishlist block. */
	function afterCart( button, id ) {
		var page = button.closest( '.sb-wl-page' );
		var config = page ? pageConfig( page ) : {};
		if ( config.removeOnAdd && ( ! config.shared || ! config.shared.length ) ) {
			removeRow( id );
			remove( id );
		}
	}

	function cartButton( button ) {
		var id = parseInt( button.getAttribute( 'data-sb-wl-cart' ), 10 );
		var label = button.textContent;
		button.classList.add( 'is-busy' );
		addToCart( id ).then( function () {
			button.classList.remove( 'is-busy' );
			button.classList.add( 'is-done' );
			button.textContent = i18n.inCart;
			announce( i18n.toCart );
			window.setTimeout( function () {
				button.classList.remove( 'is-done' );
				button.textContent = label;
			}, 2200 );
			run( 'cartAdded', [ button, id ] );
			afterCart( button, id );
			cartChanged();
		} ).catch( function ( error ) {
			button.classList.remove( 'is-busy' );
			showToast( error && error.message ? decodeText( error.message ) : i18n.error );
		} );
	}

	function decodeText( html ) {
		var node = document.createElement( 'textarea' );
		node.innerHTML = String( html ).replace( /<[^>]*>/g, '' );
		return node.value;
	}

	/* ------------------------------------------------------------ notices */

	/**
	 * A short notice. With a link: "View" opens the wishlist page – or what another script set as
	 * envoBlocksWishlist.view (a function, e.g. a drawer).
	 */
	function showToast( text, withLink ) {
		if ( ! toastEl ) {
			toastEl = document.createElement( 'div' );
			toastEl.className = 'sb-wl-toast';
			toastEl.setAttribute( 'role', 'status' );
			document.body.appendChild( toastEl );
		}
		toastEl.innerHTML = '';
		if ( withLink ) {
			toastEl.insertAdjacentHTML( 'beforeend', data.icon || '' );
		}
		var span = document.createElement( 'span' );
		span.textContent = text;
		toastEl.appendChild( span );

		var view = window.envoBlocksWishlist && window.envoBlocksWishlist.view;
		if ( withLink && ( view || data.pageUrl ) && ! document.querySelector( '.sb-wl-page:not(.is-shared)' ) ) {
			var link;
			if ( 'function' === typeof view ) {
				link = document.createElement( 'button' );
				link.type = 'button';
				link.addEventListener( 'click', function () {
					hideToast();
					view( link );
				} );
			} else {
				link = document.createElement( 'a' );
				link.href = data.pageUrl;
			}
			link.textContent = i18n.view;
			toastEl.appendChild( link );
		}

		window.clearTimeout( toastTimer );
		void toastEl.offsetWidth;
		toastEl.classList.add( 'is-visible' );
		toastTimer = window.setTimeout( hideToast, 3200 );
	}

	function hideToast() {
		if ( toastEl ) {
			toastEl.classList.remove( 'is-visible' );
		}
	}

	/* ------------------------------------------------------------ clicks */

	function onClick( event ) {
		var target = event.target;
		if ( ! target.closest ) {
			return;
		}

		var toggle = target.closest( '.sb-wl-toggle[data-sb-wl-id]' );
		if ( toggle ) {
			event.preventDefault();
			event.stopPropagation();
			var id = parseInt( toggle.getAttribute( 'data-sb-wl-id' ), 10 );
			var adding = ! has( id );
			if ( adding ) {
				add( id );
			} else {
				removeRow( id );
				remove( id );
			}
			toggle.classList.remove( 'is-popping' );
			void toggle.offsetWidth;
			toggle.classList.add( 'is-popping' );
			showToast( adding ? i18n.added : i18n.removed, adding );
			return;
		}

		var removeButton = target.closest( '[data-sb-wl-remove]' );
		if ( removeButton ) {
			event.preventDefault();
			var removeId = parseInt( removeButton.getAttribute( 'data-sb-wl-remove' ), 10 );
			removeRow( removeId );
			remove( removeId );
			announce( i18n.removed );
			return;
		}

		var cart = target.closest( '[data-sb-wl-cart]' );
		if ( cart ) {
			event.preventDefault();
			cartButton( cart );
			return;
		}

		run( 'click', [ event, target ] );
	}

	/* ------------------------------------------------------------ API for other scripts */

	window.envoBlocksWishlist = {
		data: data,
		i18n: i18n,
		view: null,
		ids: function () {
			return ids.slice();
		},
		has: has,
		add: add,
		remove: remove,
		addMany: addMany,
		refresh: refresh,
		updateToggles: updateToggles,
		fetchItems: fetchItems,
		newestFirst: newestFirst,
		pageConfig: pageConfig,
		removeRow: removeRow,
		addToCart: addToCart,
		afterCart: afterCart,
		cartChanged: cartChanged,
		showToast: showToast,
		hideToast: hideToast,
		announce: announce,
		format: format,
		countText: countText,
		on: function ( name, fn ) {
			if ( hooks[ name ] ) {
				hooks[ name ].push( fn );
			}
		},
	};

	/* ------------------------------------------------------------ start */

	function init() {
		if ( ! data.rest ) {
			return;
		}

		if ( data.loggedIn ) {
			ids = Array.isArray( data.ids ) ? data.ids.slice() : [];
			// The list saved as a guest joins the account list after signing in.
			var guest = readLocal();
			if ( guest.length ) {
				writeLocal( [] );
				var missing = guest.filter( function ( id ) {
					return ! has( id );
				} );
				if ( missing.length ) {
					ids = ids.concat( missing );
					serverSync( api( { merge: missing } ) ).catch( function () {} );
				}
			}
		} else {
			ids = readLocal();
			// Guests: other tabs change the list too.
			window.addEventListener( 'storage', function ( event ) {
				if ( KEY === event.key ) {
					ids = readLocal();
					refresh();
				}
			} );
		}

		document.addEventListener( 'click', onClick, true );
		document.dispatchEvent( new window.CustomEvent( 'envo-blocks-wishlist-ready' ) );
		refresh();

		// Back / forward cache: the list may have changed meanwhile.
		window.addEventListener( 'pageshow', function ( event ) {
			if ( event.persisted && ! data.loggedIn ) {
				ids = readLocal();
				refresh();
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
