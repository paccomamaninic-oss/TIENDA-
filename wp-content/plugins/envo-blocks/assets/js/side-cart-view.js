/**
 * Envo Blocks – Side cart: the cart panel of a "Side cart" store layout.
 *
 * The panel HTML is the same for every visitor (cache safe); the cart comes from the WooCommerce Store API when the
 * page has a cart (cookie) or the panel opens, and after every change of the cart (Add to cart buttons, other cart
 * blocks). Opened by [data-envo-sc-open], the WooCommerce Mini-Cart, cart links of the theme, the Sticky Cart of
 * Envo Blocks PRO (window.envoSideCart.open()) and after Add to cart.
 *
 * Other scripts (Envo Blocks PRO) get the cart with the event "envo-side-cart:update" on document (detail = cart)
 * and use window.envoSideCart: open, close, request, update, money, decode, notify, cart, config.
 * Plain ES5, no build step needed.
 */
( function () {
	'use strict';

	var root, panel, overlay, content, statusEl;
	var config = {};
	var cart = null;
	var nonce = '';
	var opener = null;
	var ownEvent = false;
	var busy = 0;

	/* ------------------------------------------------------------ helpers */

	function decode( text ) {
		// Names come with HTML entities; DOMParser does not run scripts, textContent keeps it plain text.
		return new window.DOMParser().parseFromString( String( text || '' ), 'text/html' ).body.textContent || '';
	}

	function money( minor, currency ) {
		var unit = currency && typeof currency.currency_minor_unit === 'number' ? currency.currency_minor_unit : 2;
		var value = ( parseInt( minor, 10 ) || 0 ) / Math.pow( 10, unit );
		var parts = value.toFixed( unit ).split( '.' );
		parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, currency ? currency.currency_thousand_separator : ',' );
		var number = parts.length > 1 ? parts[ 0 ] + ( currency ? currency.currency_decimal_separator : '.' ) + parts[ 1 ] : parts[ 0 ];
		return ( currency ? currency.currency_prefix : '' ) + number + ( currency ? currency.currency_suffix : '' );
	}

	function format( template, value ) {
		return String( template ).replace( '%d', value ).replace( '%s', value );
	}

	function make( tag, className, text ) {
		var node = document.createElement( tag );
		if ( className ) {
			node.className = className;
		}
		if ( undefined !== text ) {
			node.textContent = text;
		}
		return node;
	}

	function all( selector ) {
		return Array.prototype.slice.call( panel.querySelectorAll( selector ) );
	}

	function announce( text ) {
		if ( statusEl ) {
			statusEl.textContent = '';
			window.setTimeout( function () {
				statusEl.textContent = text;
			}, 50 );
		}
	}

	/* ------------------------------------------------------------ Store API */

	function request( path, body, retried ) {
		var options = { credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-Envo-Side-Cart': '1' }, cache: 'no-store' };
		if ( body ) {
			options.method = 'POST';
			options.body = JSON.stringify( body );
			options.headers.Nonce = nonce;
		}
		return window.fetch( config.api + path, options ).then( function ( response ) {
			var next = response.headers.get( 'Nonce' );
			if ( next ) {
				nonce = next;
			}
			// An expired nonce (e.g. a cached page): get a new one with a GET and try once more.
			if ( body && ( 401 === response.status || 403 === response.status ) && ! retried ) {
				return request( '', null ).then( function () {
					return request( path, body, true );
				} );
			}
			return response.json().then( function ( data ) {
				if ( ! response.ok ) {
					throw data;
				}
				return data;
			} );
		} );
	}

	function setLoading( on ) {
		busy = Math.max( 0, busy + ( on ? 1 : -1 ) );
		panel.classList.toggle( 'is-loading', busy > 0 );
	}

	function load() {
		setLoading( true );
		return request( '', null )
			.then( update )
			.catch( function () {} )
			.then( function () {
				root.classList.remove( 'is-pending' ); // Loaded (or failed): the empty state may show now.
				setLoading( false );
			} );
	}

	/** The cart changed here: other carts, mini carts and the Free Shipping Bar update. */
	function notify( type ) {
		ownEvent = true;
		document.body.dispatchEvent( new window.CustomEvent( type, { bubbles: true, cancelable: true } ) );
		ownEvent = false;
		if ( window.jQuery ) {
			window.jQuery( document.body ).trigger( 'wc_fragment_refresh' );
		}
	}

	function change( path, body, done ) {
		setLoading( true );
		return request( path, body )
			.then( function ( data ) {
				update( data );
				notify( '/remove-item' === path ? 'wc-blocks_removed_from_cart' : 'wc-blocks_added_to_cart' );
				if ( done ) {
					done( data );
				}
				return data;
			} )
			.catch( function ( error ) {
				announce( error && error.message ? decode( error.message ) : config.i18n.error );
				load();
				throw error;
			} )
			.then(
				function ( data ) {
					setLoading( false );
					return data;
				},
				function () {
					setLoading( false );
				}
			);
	}

	/* ------------------------------------------------------------ rendering */

	function lineTotal( item, key ) {
		var totals = item.totals || {};
		return ( parseInt( totals[ key ], 10 ) || 0 ) + ( config.taxIncl ? parseInt( totals[ key + '_tax' ], 10 ) || 0 : 0 );
	}

	function update( data ) {
		cart = data || null;
		root.classList.remove( 'is-pending' );
		var count = cart && cart.items_count ? cart.items_count : 0;
		root.classList.toggle( 'is-empty', count < 1 );
		panel.classList.toggle( 'is-empty', count < 1 );

		all( '.envo-sc-items__count' ).forEach( function ( node ) {
			node.textContent = count > 0 ? format( 1 === count ? config.i18n.item : config.i18n.items, count ) : '';
		} );
		all( '.envo-sc-items__list' ).forEach( renderItems );
		renderSummary();

		document.dispatchEvent( new window.CustomEvent( 'envo-side-cart:update', { detail: cart } ) );
	}

	function renderItems( list ) {
		list.textContent = '';
		var items = cart && cart.items ? cart.items : [];

		items.forEach( function ( item ) {
			var name = decode( item.name );
			var li = make( 'li', 'envo-sc-item' );

			var imageLink = make( 'a', 'envo-sc-item__image' );
			imageLink.href = item.permalink || '#';
			imageLink.tabIndex = -1;
			imageLink.setAttribute( 'aria-hidden', 'true' );
			var img = make( 'img' );
			img.src = item.images && item.images[ 0 ] ? item.images[ 0 ].thumbnail || item.images[ 0 ].src : config.placeholder;
			img.alt = '';
			img.loading = 'lazy';
			imageLink.appendChild( img );
			li.appendChild( imageLink );

			var main = make( 'div', 'envo-sc-item__main' );
			var link = make( 'a', 'envo-sc-item__name', name );
			link.href = item.permalink || '#';
			main.appendChild( link );

			var meta = [];
			( item.variation || [] ).forEach( function ( v ) {
				meta.push( decode( v.attribute ) + ': ' + decode( v.value ) );
			} );
			( item.item_data || [] ).forEach( function ( v ) {
				meta.push( decode( v.name || v.key ) + ': ' + decode( v.display || v.value ) );
			} );
			if ( meta.length ) {
				main.appendChild( make( 'span', 'envo-sc-item__meta', meta.join( ', ' ) ) );
			}

			var actions = make( 'div', 'envo-sc-item__actions' );
			var limits = item.quantity_limits || { minimum: 1, maximum: 9999, multiple_of: 1, editable: true };
			var step = limits.multiple_of || 1;

			if ( false !== limits.editable ) {
				var qty = make( 'span', 'envo-sc-qty' );
				var minus = make( 'button', '', '−' );
				minus.type = 'button';
				minus.setAttribute( 'aria-label', format( config.i18n.decrease, name ) );
				minus.disabled = item.quantity - step < limits.minimum;
				var input = make( 'input' );
				input.type = 'number';
				input.value = item.quantity;
				input.min = limits.minimum;
				input.max = limits.maximum;
				input.step = step;
				input.inputMode = 'numeric';
				input.setAttribute( 'aria-label', format( config.i18n.quantity, name ) );
				var plus = make( 'button', '', '+' );
				plus.type = 'button';
				plus.setAttribute( 'aria-label', format( config.i18n.increase, name ) );
				plus.disabled = item.quantity + step > limits.maximum;

				minus.addEventListener( 'click', function () {
					change( '/update-item', { key: item.key, quantity: item.quantity - step } ).catch( function () {} );
				} );
				plus.addEventListener( 'click', function () {
					change( '/update-item', { key: item.key, quantity: item.quantity + step } ).catch( function () {} );
				} );
				input.addEventListener( 'change', function () {
					var value = Math.round( ( parseFloat( input.value ) || 0 ) / step ) * step;
					if ( value < 1 ) {
						removeItem( item, name );
					} else {
						change( '/update-item', { key: item.key, quantity: Math.max( limits.minimum, Math.min( limits.maximum, value ) ) } ).catch( function () {} );
					}
				} );

				qty.appendChild( minus );
				qty.appendChild( input );
				qty.appendChild( plus );
				actions.appendChild( qty );
			} else {
				actions.appendChild( make( 'span', 'envo-sc-item__meta', '× ' + item.quantity ) );
			}

			var box = list.closest( '.envo-sc-items' );
			var remove = make( 'button', 'envo-sc-item__remove', box ? box.getAttribute( 'data-label-remove' ) || '×' : '×' );
			remove.type = 'button';
			remove.setAttribute( 'aria-label', format( config.i18n.remove, name ) );
			remove.addEventListener( 'click', function () {
				removeItem( item, name );
			} );
			actions.appendChild( remove );
			main.appendChild( actions );
			li.appendChild( main );

			var price = make( 'span', 'envo-sc-item__price' );
			var now = lineTotal( item, 'line_total' );
			var before = item.prices && item.prices.regular_price ? ( parseInt( item.prices.regular_price, 10 ) || 0 ) * item.quantity : 0;
			var shown = lineTotal( item, 'line_subtotal' );
			if ( before > shown && ! config.taxIncl ) {
				price.appendChild( make( 'del', '', money( before, item.totals ) ) );
				price.appendChild( document.createTextNode( ' ' ) );
			}
			price.appendChild( document.createTextNode( money( shown || now, item.totals ) ) );
			li.appendChild( price );

			list.appendChild( li );
		} );
	}

	function renderSummary() {
		var totals = cart && cart.totals ? cart.totals : null;
		var subtotal = totals ? ( parseInt( totals.total_items, 10 ) || 0 ) + ( config.taxIncl ? parseInt( totals.total_items_tax, 10 ) || 0 : 0 ) : 0;
		all( '.envo-sc-subtotal' ).forEach( function ( node ) {
			node.textContent = totals ? money( subtotal, totals ) : '';
		} );

		// Savings: sale prices and coupons.
		var savings = 0;
		( cart && cart.items ? cart.items : [] ).forEach( function ( item ) {
			var prices = item.prices || {};
			var regular = parseInt( prices.regular_price, 10 ) || 0;
			var price = parseInt( prices.price, 10 ) || 0;
			if ( regular > price ) {
				savings += ( regular - price ) * item.quantity;
			}
		} );
		savings += totals ? ( parseInt( totals.total_discount, 10 ) || 0 ) + ( config.taxIncl ? parseInt( totals.total_discount_tax, 10 ) || 0 : 0 ) : 0;
		all( '.envo-sc-summary__savings' ).forEach( function ( row ) {
			row.hidden = savings <= 0;
			var value = row.querySelector( '.envo-sc-savings' );
			if ( value ) {
				value.textContent = savings > 0 ? money( savings, totals ) : '';
			}
		} );

		all( '.envo-sc-coupons' ).forEach( function ( list ) {
			list.textContent = '';
			( cart && cart.coupons ? cart.coupons : [] ).forEach( function ( coupon ) {
				var li = make( 'li', 'envo-sc-coupons__item' );
				li.appendChild( make( 'span', '', String( coupon.code ).toUpperCase() ) );
				var remove = make( 'button', 'envo-sc-coupons__remove', '×' );
				remove.type = 'button';
				remove.setAttribute( 'aria-label', format( list.getAttribute( 'data-label-remove' ) || '%s', coupon.code ) );
				remove.addEventListener( 'click', function () {
					change( '/remove-coupon', { code: coupon.code } ).catch( function () {} );
				} );
				li.appendChild( remove );
				list.appendChild( li );
			} );
		} );
	}

	function removeItem( item, name ) {
		change( '/remove-item', { key: item.key }, function () {
			announce( format( config.i18n.removed, name ) );
			var first = panel.querySelector( '.envo-sc-item button, .envo-sc__close' );
			if ( first ) {
				first.focus();
			}
		} ).catch( function () {} );
	}

	/* ------------------------------------------------------------ panel */

	function focusable() {
		return Array.prototype.slice.call( panel.querySelectorAll( 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select, textarea' ) ).filter( function ( node ) {
			return node.offsetParent !== null;
		} );
	}

	function open( from ) {
		if ( panel.classList.contains( 'is-open' ) ) {
			return;
		}
		opener = from || document.activeElement;
		panel.hidden = false;
		overlay.hidden = false;
		void panel.offsetWidth; // Start the slide-in from the hidden position.
		panel.classList.add( 'is-open' );
		overlay.classList.add( 'is-open' );
		document.documentElement.classList.add( 'envo-sc-lock' );
		load(); // Always fresh: the cart may have changed in another tab.
		window.setTimeout( function () {
			panel.focus( { preventScroll: true } ); // The dialog itself; Tab goes on to the close button.
		}, 60 );
	}

	function close() {
		if ( ! panel.classList.contains( 'is-open' ) ) {
			return;
		}
		panel.classList.remove( 'is-open' );
		overlay.classList.remove( 'is-open' );
		document.documentElement.classList.remove( 'envo-sc-lock' );
		window.setTimeout( function () {
			if ( ! panel.classList.contains( 'is-open' ) ) {
				panel.hidden = true;
				overlay.hidden = true;
			}
		}, 320 );
		if ( opener && opener.focus && opener.offsetParent !== null ) {
			opener.focus();
		}
	}

	/** The colours of the layout's first block are the panel's (the close button and the sticky summary). */
	function adoptColors() {
		var first = content.firstElementChild;
		if ( ! first ) {
			return;
		}
		var style = window.getComputedStyle( first );
		var bg = style.backgroundColor;
		if ( bg && 'transparent' !== bg && 'rgba(0, 0, 0, 0)' !== bg ) {
			panel.style.setProperty( '--envo-sc-bg', bg );
		}
		panel.style.setProperty( '--envo-sc-ink', style.color );
	}

	/** Whether a click opens the panel: own buttons, the Mini-Cart, cart links of the theme. */
	function opensPanel( target ) {
		if ( ! target || ! target.closest ) {
			return null;
		}
		var own = target.closest( '[data-envo-sc-open]' );
		if ( own ) {
			return own;
		}
		if ( ! config.links || panel.contains( target ) ) {
			return null;
		}
		var mini = target.closest( '[data-envo-sc-mini-cart] .wc-block-mini-cart__button' );
		if ( mini ) {
			return mini;
		}
		var link = target.closest( 'a[href]' );
		if ( ! link || link.closest( '.woocommerce-message, .wc-block-components-notice-banner, .woocommerce-mini-cart__buttons' ) ) {
			return null;
		}
		var href = link.href.split( '#' )[ 0 ].replace( /\/$/, '' );
		return href === config.cartUrl.replace( /\/$/, '' ) && ( link.closest( 'header, .site-header, .wp-block-template-part, .cart-contents' ) || link.classList.contains( 'cart-contents' ) ) ? link : null;
	}

	/* ------------------------------------------------------------ coupon */

	function watchCoupons() {
		all( 'form.envo-sc-coupon' ).forEach( function ( form ) {
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
				var input = form.querySelector( 'input' );
				var code = input ? input.value.trim() : '';
				if ( ! code ) {
					return;
				}
				change( '/apply-coupon', { code: code } ).then( function ( data ) {
					if ( data ) {
						input.value = '';
					}
				} );
			} );
		} );
	}

	/* ------------------------------------------------------------ start */

	function init() {
		root = document.getElementById( 'envo-side-cart' );
		if ( ! root ) {
			return;
		}
		try {
			config = JSON.parse( root.getAttribute( 'data-envo-sc' ) );
		} catch ( e ) {
			return;
		}
		document.body.appendChild( root ); // Out of wrappers with transforms or stacking contexts.
		panel = root.querySelector( '.envo-sc__panel' );
		overlay = root.querySelector( '.envo-sc__overlay' );
		content = root.querySelector( '.envo-sc__content' );
		statusEl = root.querySelector( '.envo-sc__status' );

		// Capture: before the WooCommerce Mini-Cart opens its own drawer.
		window.addEventListener(
			'click',
			function ( event ) {
				if ( event.defaultPrevented || event.button > 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
					return;
				}
				var from = opensPanel( event.target );
				if ( from ) {
					event.preventDefault();
					event.stopPropagation();
					open( from );
				}
			},
			true
		);

		root.querySelector( '.envo-sc__close' ).addEventListener( 'click', close );
		overlay.addEventListener( 'click', close );
		panel.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				close();
			} else if ( 'Tab' === event.key ) {
				var nodes = focusable();
				if ( ! nodes.length ) {
					return;
				}
				var first = nodes[ 0 ];
				var last = nodes[ nodes.length - 1 ];
				if ( event.shiftKey && document.activeElement === first ) {
					event.preventDefault();
					last.focus();
				} else if ( ! event.shiftKey && document.activeElement === last ) {
					event.preventDefault();
					first.focus();
				}
			}
		} );

		// Other carts and Add to cart buttons changed the cart (several signals may come for one change).
		var pending = null;
		var pendingAdded = false;
		function changed( added ) {
			if ( ownEvent ) {
				return;
			}
			pendingAdded = pendingAdded || added;
			window.clearTimeout( pending );
			pending = window.setTimeout( function () {
				var wasAdded = pendingAdded;
				pendingAdded = false;
				load().then( function () {
					if ( wasAdded && config.openOnAdd ) {
						open( null );
					}
				} );
			}, 120 );
		}

		// The Add to cart buttons of the WooCommerce blocks (Interactivity API) send no event: watch their Store API
		// requests instead.
		var nativeFetch = window.fetch;
		window.fetch = function ( input, init ) {
			var promise = nativeFetch.apply( this, arguments );
			try {
				var url = String( input && input.url ? input.url : input );
				var method = String( ( init && init.method ) || ( input && input.method ) || 'GET' ).toUpperCase();
				var own = init && init.headers && init.headers[ 'X-Envo-Side-Cart' ];
				if ( ! own && 'POST' === method && /wc\/store(\/v1)?\/(cart|batch)/.test( url ) ) {
					var body = init && typeof init.body === 'string' ? init.body : '';
					var added = /add-item/.test( url ) || /add-item/.test( body );
					promise.then( function ( response ) {
						if ( response && response.ok ) {
							changed( added );
						}
					} ).catch( function () {} );
				}
			} catch ( e ) {}
			return promise;
		};
		document.body.addEventListener( 'wc-blocks_added_to_cart', function () {
			changed( true );
		} );
		document.body.addEventListener( 'wc-blocks_removed_from_cart', function () {
			changed( false );
		} );
		if ( window.jQuery ) {
			window.jQuery( document.body ).on( 'added_to_cart', function () {
				changed( true );
			} );
			window.jQuery( document.body ).on( 'removed_from_cart updated_cart_totals', function () {
				changed( false );
			} );
		}

		watchCoupons();
		adoptColors();

		window.envoSideCart = {
			open: open,
			close: close,
			load: load,
			request: request,
			change: change,
			update: update,
			money: money,
			decode: decode,
			notify: notify,
			cart: function () {
				return cart;
			},
			config: config,
			panel: panel,
		};
		document.dispatchEvent( new window.CustomEvent( 'envo-side-cart:ready', { detail: window.envoSideCart } ) );

		if ( config.open ) {
			open( null );
		} else if ( /(?:^|;\s*)woocommerce_items_in_cart=/.test( document.cookie ) ) {
			load(); // A cart exists: counts of other blocks in the panel are ready when it opens.
		}

		// Back / forward cache: the cart may have changed meanwhile.
		window.addEventListener( 'pageshow', function ( event ) {
			if ( event.persisted ) {
				load();
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
