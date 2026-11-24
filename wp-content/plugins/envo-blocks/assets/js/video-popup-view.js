/**
 * Envo Blocks - Video Popup: opens the video in the native <dialog>. The player (iframe or <video>)
 * is created on open and removed on close, so the video stops and nothing loads from the video site before a click.
 */
( function () {
	'use strict';

	function init( box ) {
		var config;
		try {
			config = JSON.parse( box.getAttribute( 'data-sb-video' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var trigger = box.querySelector( '.sb-video__trigger' );
		var dialog = box.querySelector( '.sb-video__dialog' );
		var frame = dialog && dialog.querySelector( '.sb-video__frame' );

		if ( ! trigger || ! dialog || ! frame || ! config.src || 'function' !== typeof dialog.showModal ) {
			return;
		}

		// Out of the page flow (sticky or transformed parents), so the backdrop covers the whole page.
		document.body.appendChild( dialog );

		function player() {
			var element;
			if ( 'file' === config.type ) {
				element = document.createElement( 'video' );
				element.controls = true;
				element.autoplay = true;
				element.playsInline = true;
				element.src = config.src;
			} else {
				element = document.createElement( 'iframe' );
				element.src = config.src;
				element.title = config.title || '';
				element.allow = 'autoplay; fullscreen; picture-in-picture; encrypted-media';
				element.allowFullscreen = true;
				element.setAttribute( 'referrerpolicy', 'strict-origin-when-cross-origin' );
			}
			element.className = 'sb-video__player';
			return element;
		}

		trigger.addEventListener( 'click', function () {
			frame.appendChild( player() );
			dialog.showModal();
			document.documentElement.classList.add( 'sb-video-open' );
		} );

		// Removing the player stops the video. Runs on the close button / backdrop and on Esc (the "close" event).
		function cleanUp() {
			if ( ! frame.firstChild ) {
				return;
			}
			frame.innerHTML = '';
			document.documentElement.classList.remove( 'sb-video-open' );
			trigger.focus();
		}

		dialog.addEventListener( 'close', cleanUp );

		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog || event.target.closest( '[data-sb-video-close]' ) ) {
				dialog.close();
				cleanUp();
			}
		} );
	}

	function start() {
		document.querySelectorAll( '.sb-video[data-sb-video]' ).forEach( init );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
