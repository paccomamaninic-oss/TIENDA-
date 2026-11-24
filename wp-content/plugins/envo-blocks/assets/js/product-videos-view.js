/**
 * Envo Blocks - product videos.
 *
 * Gallery of WooCommerce: a click on a video slide puts the player in place of the cover (nothing is loaded from the
 * video service before); the player is removed when another slide is shown. The thumbnails of video slides get a play
 * mark. Product lists: images with data-eb-pv-card play that video file (muted, in a loop) while the pointer is on
 * them. Plain JavaScript.
 */
( function () {
	'use strict';

	var touch = window.matchMedia && window.matchMedia( '(hover: none)' ).matches;

	/** Player element for an embed ({ type: iframe|file, src }). */
	function player( embed, label ) {
		var el;
		if ( 'file' === embed.type ) {
			el = document.createElement( 'video' );
			el.src = embed.src;
			el.controls = true;
			el.autoplay = true;
			el.playsInline = true;
			el.setAttribute( 'playsinline', '' );
		} else {
			el = document.createElement( 'iframe' );
			el.src = embed.src;
			el.allow = 'autoplay; encrypted-media; fullscreen; picture-in-picture';
			el.allowFullscreen = true;
			el.setAttribute( 'title', label || '' );
		}
		el.className = 'eb-pv__player';
		return el;
	}

	/** Shows the player in a video slide. */
	function play( slide ) {
		if ( slide.querySelector( '.eb-pv__player' ) ) {
			return;
		}
		var embed;
		try {
			embed = JSON.parse( slide.getAttribute( 'data-eb-pv' ) || '{}' );
		} catch ( e ) {
			return;
		}
		if ( ! embed || ! embed.src ) {
			return;
		}
		var link = slide.querySelector( '.eb-pv__play' );
		var label = link ? link.getAttribute( 'aria-label' ) : '';
		slide.classList.add( 'is-playing' );
		slide.appendChild( player( embed, label ) );
	}

	/** Removes the player (the video stops) and shows the cover again. */
	function stop( slide ) {
		var el = slide.querySelector( '.eb-pv__player' );
		if ( el ) {
			el.remove();
		}
		slide.classList.remove( 'is-playing' );
	}

	// A click on the cover plays the video; WooCommerce must not open its lightbox for it (capture phase, before jQuery).
	document.addEventListener(
		'click',
		function ( event ) {
			var link = event.target.closest && event.target.closest( '.eb-pv .eb-pv__play' );
			if ( ! link ) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();
			event.stopImmediatePropagation();
			play( link.closest( '.eb-pv' ) );
		},
		true
	);

	/** Gallery of WooCommerce: stop videos of slides that are no longer shown, mark video thumbnails. */
	function watchGallery( gallery ) {
		if ( gallery.getAttribute( 'data-eb-pv-done' ) ) {
			return;
		}
		gallery.setAttribute( 'data-eb-pv-done', '1' );
		var slides = Array.prototype.slice.call( gallery.querySelectorAll( '.woocommerce-product-gallery__image' ) );

		function markThumbs() {
			var thumbs = gallery.querySelectorAll( '.flex-control-thumbs li' );
			if ( ! thumbs.length ) {
				return false;
			}
			slides.forEach( function ( slide, n ) {
				// Only when missing: setting the class again would wake the observer below again and again.
				if ( slide.classList.contains( 'eb-pv' ) && thumbs[ n ] && ! thumbs[ n ].classList.contains( 'eb-pv-thumb' ) ) {
					thumbs[ n ].classList.add( 'eb-pv-thumb' );
				}
			} );
			return true;
		}

		var observer = new MutationObserver( function () {
			markThumbs();
			slides.forEach( function ( slide ) {
				if ( slide.classList.contains( 'eb-pv' ) && slide.classList.contains( 'is-playing' ) && ! slide.classList.contains( 'flex-active-slide' ) && gallery.querySelector( '.flex-active-slide' ) ) {
					stop( slide );
				}
			} );
		} );
		observer.observe( gallery, { attributes: true, attributeFilter: [ 'class' ], subtree: true, childList: true } );
		markThumbs();
	}

	/** Product lists: muted video over the image while the pointer is on it. */
	function setupCard( img ) {
		img.setAttribute( 'data-eb-pv-card-done', '1' );
		if ( touch || img.closest( '.woocommerce-cart-form, .wc-block-cart, .wc-block-mini-cart__drawer, .widget_shopping_cart, .woocommerce-mini-cart' ) ) {
			return;
		}
		var base = img.closest( 'a' ) || img;
		var host = base.parentElement;
		if ( ! host ) {
			return;
		}
		var video = null;
		var timer = 0;

		function show() {
			window.clearTimeout( timer );
			if ( ! video ) {
				if ( 'static' === window.getComputedStyle( base ).position ) {
					base.style.position = 'relative';
				}
				video = document.createElement( 'video' );
				video.className = 'eb-pv-card';
				video.src = img.getAttribute( 'data-eb-pv-card' );
				video.muted = true;
				video.loop = true;
				video.playsInline = true;
				video.setAttribute( 'playsinline', '' );
				video.setAttribute( 'aria-hidden', 'true' );
				video.preload = 'auto';
				base.appendChild( video );
			}
			var started = video.play();
			if ( started && started.catch ) {
				started.catch( function () {} );
			}
			// Shown once it can play, so a slow file does not cover the image with an empty box.
			if ( video.readyState >= 2 ) {
				video.classList.add( 'is-on' );
			} else {
				video.addEventListener( 'playing', function () {
					video.classList.add( 'is-on' );
				}, { once: true } );
			}
		}

		function hide() {
			if ( ! video ) {
				return;
			}
			video.classList.remove( 'is-on' );
			timer = window.setTimeout( function () {
				video.pause();
			}, 250 );
		}

		base.addEventListener( 'mouseenter', show );
		base.addEventListener( 'mouseleave', hide );
		base.addEventListener( 'focusin', show );
		base.addEventListener( 'focusout', hide );
	}

	function scan( root ) {
		Array.prototype.forEach.call( ( root || document ).querySelectorAll( '.woocommerce-product-gallery' ), function ( gallery ) {
			if ( gallery.querySelector( '.eb-pv' ) ) {
				watchGallery( gallery );
			}
		} );
		Array.prototype.forEach.call( ( root || document ).querySelectorAll( 'img[data-eb-pv-card]:not([data-eb-pv-card-done])' ), setupCard );
	}

	function start() {
		scan( document );
		// Lists added later (load more, carousels, tabs).
		new MutationObserver( function ( list ) {
			list.forEach( function ( record ) {
				Array.prototype.forEach.call( record.addedNodes, function ( node ) {
					if ( 1 === node.nodeType && ( node.matches( 'img[data-eb-pv-card]' ) || node.querySelector( 'img[data-eb-pv-card], .eb-pv' ) ) ) {
						scan( node.parentElement || document );
					}
				} );
			} );
		} ).observe( document.body, { childList: true, subtree: true } );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
