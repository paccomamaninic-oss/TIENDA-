/**
 * Envo Blocks - Recently Viewed Products: loads the products remembered in localStorage
 * (filled on every product page) from the WooCommerce Store API.
 */
( function () {
	'use strict';

	var KEY = 'envoBlocksRecent';

	function readIds() {
		try {
			var list = JSON.parse( localStorage.getItem( KEY ) || '[]' );
			return Array.isArray( list ) ? list.filter( function ( id ) {
				return id > 0;
			} ) : [];
		} catch ( e ) {
			return [];
		}
	}

	function escapeHtml( text ) {
		return String( text === undefined || text === null ? '' : text ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function decode( text ) {
		var area = document.createElement( 'textarea' );
		area.innerHTML = text || '';
		return area.value;
	}

	/** Only http(s) or relative links. */
	function safeUrl( url ) {
		url = String( url || '' );
		return /^https?:\/\//i.test( url ) || /^(\/(?!\/)|\?)/.test( url ) ? url : '#';
	}

	/** Price markup from the Store API, reduced to text and the few tags WooCommerce uses for prices. */
	function safePrice( html ) {
		var allowed = { SPAN: 1, DEL: 1, INS: 1, BDI: 1, SMALL: 1, STRONG: 1, BR: 1 };
		var doc = new DOMParser().parseFromString( '<div>' + html + '</div>', 'text/html' );

		function clean( node ) {
			return Array.prototype.map
				.call( node.childNodes, function ( child ) {
					if ( 3 === child.nodeType ) {
						return escapeHtml( child.textContent );
					}
					if ( 1 !== child.nodeType ) {
						return '';
					}
					var inner = clean( child );
					if ( ! allowed[ child.tagName ] ) {
						return inner;
					}
					var tag = child.tagName.toLowerCase();
					var cls = child.getAttribute( 'class' );
					return 'br' === tag ? '<br>' : '<' + tag + ( cls ? ' class="' + escapeHtml( cls ) + '"' : '' ) + '>' + inner + '</' + tag + '>';
				} )
				.join( '' );
		}

		return clean( doc.body.firstChild );
	}

	function card( product, config ) {
		var image = product.images && product.images[ 0 ];
		var rating = parseFloat( product.average_rating ) || 0;
		var html = '<li class="sb-recent__item"><a class="sb-recent__link" href="' + escapeHtml( safeUrl( product.permalink ) ) + '"><span class="sb-recent__image">';

		if ( image ) {
			html += '<img src="' + escapeHtml( safeUrl( image.thumbnail || image.src ) ) + '" alt="' + escapeHtml( decode( image.alt || product.name ) ) + '" loading="lazy">';
		}

		html += '</span><span class="sb-recent__name">' + escapeHtml( decode( product.name ) ) + '</span></a>';

		if ( config.showRating && rating > 0 ) {
			html += '<span class="sb-stars" role="img" aria-label="' + escapeHtml( config.ratedText.replace( '%s', rating.toFixed( 1 ) ) ) + '" style="--sb-stars:' + ( rating / 5 ) * 100 + '%"></span>';
		}

		if ( config.showPrice && product.price_html ) {
			html += '<span class="sb-recent__price">' + safePrice( product.price_html ) + '</span>';
		}

		// The Store API builds add_to_cart.url from the API request, so the link is made from the product URL instead:
		// simple products are added with ?add-to-cart, the others open the product page (to choose options).
		if ( config.showButton && product.add_to_cart && product.add_to_cart.text ) {
			var permalink = safeUrl( product.permalink );
			var direct = 'simple' === product.type && ! product.has_options && product.is_purchasable && product.is_in_stock && '#' !== permalink;
			var href = direct ? permalink + ( -1 === permalink.indexOf( '?' ) ? '?' : '&' ) + 'add-to-cart=' + parseInt( product.id, 10 ) : permalink;
			html += '<a class="sb-recent__button wp-element-button" href="' + escapeHtml( href ) + '" rel="nofollow">' + escapeHtml( decode( product.add_to_cart.text ) ) + '</a>';
		}

		return html + '</li>';
	}

	function init( box ) {
		var config;
		try {
			config = JSON.parse( box.getAttribute( 'data-config' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var list = box.querySelector( '.sb-recent__list' );
		var empty = box.querySelector( '.sb-recent__empty' );
		var clear = box.querySelector( '[data-sb-clear]' );

		function showEmpty() {
			list.innerHTML = '';
			if ( config.emptyText ) {
				empty.textContent = config.emptyText;
				empty.hidden = false;
				box.classList.add( 'is-ready' );
				if ( clear ) {
					clear.hidden = true;
				}
			} else {
				box.classList.remove( 'is-ready' );
			}
		}

		if ( clear ) {
			clear.addEventListener( 'click', function () {
				try {
					localStorage.removeItem( KEY );
				} catch ( e ) {}
				showEmpty();
			} );
		}

		var ids = readIds()
			.filter( function ( id ) {
				return id !== config.exclude;
			} )
			.slice( 0, config.limit );

		if ( ! ids.length ) {
			showEmpty();
			return;
		}

		var url = config.restUrl + ( -1 === config.restUrl.indexOf( '?' ) ? '?' : '&' ) + 'include=' + ids.join( ',' ) + '&per_page=' + ids.length;

		fetch( url, { credentials: 'same-origin' } )
			.then( function ( response ) {
				return response.ok ? response.json() : [];
			} )
			.then( function ( products ) {
				var byId = {};
				( Array.isArray( products ) ? products : [] ).forEach( function ( product ) {
					byId[ product.id ] = product;
				} );

				var html = ids
					.filter( function ( id ) {
						return byId[ id ];
					} )
					.map( function ( id ) {
						return card( byId[ id ], config );
					} )
					.join( '' );

				if ( ! html ) {
					showEmpty();
					return;
				}

				list.innerHTML = html;
				box.classList.add( 'is-ready' );
			} )
			.catch( showEmpty );
	}

	function start() {
		document.querySelectorAll( '.sb-recent[data-config]' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
