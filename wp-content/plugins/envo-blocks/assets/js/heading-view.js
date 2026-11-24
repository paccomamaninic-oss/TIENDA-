/**
 * Envo Blocks - Advanced Heading: rotates the texts of the middle part (slide, fade, zoom, flip,
 * blur or typing) and plays the highlight / underline decoration when the heading comes into view.
 * With "reduce motion" the first text stays and nothing moves.
 */
( function () {
	'use strict';

	var OUT = 350; // Length of the "out" animation in ms (see blocks.css).
	var IN = 450;

	var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function onVisible( element, callback ) {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			callback();
			return;
		}
		var observer = new IntersectionObserver(
			function ( entries ) {
				if ( entries[ 0 ].isIntersecting ) {
					observer.disconnect();
					callback();
				}
			},
			{ threshold: 0.4 }
		);
		observer.observe( element );
	}

	/** Restarts the decoration (highlight / underline) for a new text. */
	function decorate( rotator, again ) {
		if ( again ) {
			rotator.classList.remove( 'is-decorated' );
			void rotator.offsetWidth; // Reflow, so the transition runs again.
		}
		rotator.classList.add( 'is-decorated' );
	}

	/** Animates the width of the rotator from its current size to the size of the new text. */
	function resize( rotator, change ) {
		var from = rotator.getBoundingClientRect().width;
		change();
		rotator.style.width = '';
		var to = rotator.getBoundingClientRect().width;
		if ( Math.abs( to - from ) < 1 ) {
			return;
		}
		rotator.classList.add( 'is-sizing' );
		rotator.style.width = from + 'px';
		void rotator.offsetWidth;
		rotator.style.width = to + 'px';
		setTimeout( function () {
			rotator.classList.remove( 'is-sizing' );
			rotator.style.width = '';
		}, 400 );
	}

	function rotate( part, config, rotator, word ) {
		var index = 0;
		var decorated = part.className.indexOf( 'sb-aheading__deco--' ) !== -1;

		function next() {
			if ( ! config.loop && index >= config.words.length - 1 ) {
				return;
			}
			index = ( index + 1 ) % config.words.length;
			word.classList.remove( 'is-in' );
			word.classList.add( 'is-out' );

			setTimeout( function () {
				resize( rotator, function () {
					word.textContent = config.words[ index ];
				} );
				word.classList.remove( 'is-out' );
				word.classList.add( 'is-in' );
				if ( decorated ) {
					decorate( rotator, true );
				}
				setTimeout( function () {
					word.classList.remove( 'is-in' );
				}, IN );
				setTimeout( next, config.interval );
			}, OUT );
		}

		setTimeout( next, config.interval );
	}

	function type( part, config, rotator, word ) {
		var index = 0;
		var decorated = part.className.indexOf( 'sb-aheading__deco--' ) !== -1;
		var cursor = document.createElement( 'span' );
		cursor.className = 'sb-aheading__cursor';
		rotator.appendChild( cursor );

		function erase() {
			var text = word.textContent;
			if ( text.length ) {
				word.textContent = text.slice( 0, -1 );
				setTimeout( erase, 35 );
				return;
			}
			index = ( index + 1 ) % config.words.length;
			if ( decorated ) {
				rotator.classList.remove( 'is-decorated' );
			}
			setTimeout( write, 250 );
		}

		function write() {
			var target = config.words[ index ];
			var text = word.textContent;
			if ( text.length < target.length ) {
				word.textContent = target.slice( 0, text.length + 1 );
				setTimeout( write, 75 );
				return;
			}
			if ( decorated ) {
				decorate( rotator, false );
			}
			if ( ! config.loop && index >= config.words.length - 1 ) {
				cursor.remove();
				return;
			}
			setTimeout( erase, config.interval );
		}

		if ( config.words.length > 1 ) {
			setTimeout( erase, config.interval );
		}
	}

	function init( part ) {
		var config;
		try {
			config = JSON.parse( part.getAttribute( 'data-sb-heading' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var rotator = part.querySelector( '.sb-aheading__rotator' );
		var word = rotator && rotator.querySelector( '.sb-aheading__word' );
		if ( ! word || ! Array.isArray( config.words ) || ! config.words.length ) {
			return;
		}

		var decorated = part.className.indexOf( 'sb-aheading__deco--' ) !== -1;

		if ( reduced ) {
			if ( decorated ) {
				decorate( rotator, false );
			}
			return;
		}

		onVisible( part, function () {
			if ( decorated ) {
				decorate( rotator, false );
			}

			if ( 'none' === config.animation ) {
				return;
			}

			if ( 'typing' === config.animation ) {
				type( part, config, rotator, word );
				return;
			}

			// One text only: play the entrance animation once.
			word.classList.add( 'is-in' );
			setTimeout( function () {
				word.classList.remove( 'is-in' );
			}, IN );

			if ( config.words.length > 1 ) {
				rotate( part, config, rotator, word );
			}
		} );
	}

	function start() {
		document.querySelectorAll( '.sb-aheading__part--middle[data-sb-heading]' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
