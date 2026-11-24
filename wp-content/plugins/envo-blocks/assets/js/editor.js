( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.element || ! wp.data || ! wp.blocks ) {
		return;
	}

	const registerPlugin = wp.plugins && wp.plugins.registerPlugin;
	const { createElement: el, Fragment, useState, useEffect, useRef, useMemo, useCallback, createPortal } = wp.element;
	const { Button, Spinner } = wp.components;
	const { __, _n, sprintf } = wp.i18n;
	const { select, dispatch } = wp.data;
	const { createBlock, getBlockType } = wp.blocks;
	const apiFetch = wp.apiFetch;
	const cleanForSlug = wp.url.cleanForSlug;
	const PluginMoreMenuItem = ( wp.editor && wp.editor.PluginMoreMenuItem ) || ( wp.editPost && wp.editPost.PluginMoreMenuItem );

	
	const SCHEMA = 'envo-blocks/v1';
	const config = window.envoBlocksData || {};
	const pluginName = config.pluginName || 'Envo Blocks';
	// Envo Blocks > Templates admin page: only templates; "Import" creates a new post or page (see CreateDialog).
	const PAGE = !! config.adminPage;
	// Envo Blocks > Store Layouts: the same window in the Layouts mode.
	const START_MODE = 'layouts' === config.startMode ? 'layouts' : 'templates';

	/* ------------------------------------------------------------------ */
	/* Helpers                                                            */
	/* ------------------------------------------------------------------ */

	// A "Contact support" link of the last error (PRO: the template library could not be reached).
	var lastSupport = null;

	function errorMessage( error ) {
		var message = error && typeof error.message === 'string' && error.message
			? error.message
			: __( 'Something went wrong. Please try again.', 'envo-blocks' );
		var url = error && error.data && typeof error.data.support === 'string' ? error.data.support : '';
		lastSupport = /^https:\/\//.test( url ) ? { message: message, url: url } : null;
		return message;
	}

	/** The error box of the window, with the support link when the error has one. */
	function errorBox( message ) {
		if ( ! message ) {
			return null;
		}
		var support = lastSupport && lastSupport.message === message ? lastSupport.url : '';
		return el(
			'div',
			{ className: 'envo-blocks-error', role: 'alert' },
			message,
			support
				? el(
						'p',
						{ className: 'envo-blocks-error__support' },
						el( 'a', { href: support, target: '_blank', rel: 'noopener noreferrer', className: 'envo-blocks-btn envo-blocks-btn--secondary' }, __( 'Contact support', 'envo-blocks' ) )
				  )
				: null
		);
	}

	function notice( type, message ) {
		if ( PAGE ) {
			return; // No notices store outside the editor.
		}
		dispatch( 'core/notices' ).createNotice( type, message, { type: 'snackbar', isDismissible: true } );
	}

	function icon( path ) {
		return el(
			'svg',
			{ width: 24, height: 24, viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg', 'aria-hidden': 'true', focusable: 'false' },
			el( 'path', { d: path } )
		);
	}

	// The Envo Blocks mark without its background: white blocks for the header button.
	const brandIcon = el(
		'svg',
		{ className: 'envo-blocks-header-button__icon', width: 18, height: 18, viewBox: '8 8 20 20', xmlns: 'http://www.w3.org/2000/svg', 'aria-hidden': 'true', focusable: 'false' },
		el( 'rect', { x: 8, y: 8, width: 20, height: 5, rx: 1.5, fill: 'currentColor' } ),
		el( 'rect', { x: 8, y: 16, width: 9, height: 12, rx: 1.5, fill: 'currentColor', opacity: 0.85 } ),
		el( 'rect', { x: 19, y: 16, width: 9, height: 5, rx: 1.5, fill: 'currentColor', opacity: 0.85 } ),
		el( 'rect', { x: 19, y: 23, width: 9, height: 5, rx: 1.5, fill: 'currentColor', opacity: 0.85 } )
	);

	const downloadIcon = icon( 'M18 11.3l-1-1.1-4 4V3h-1.5v11.3L7 10.2l-1 1.1 6.2 5.8 5.8-5.8zm.5 3.7v3.5h-13V15H4v5h16v-5h-1.5z' );
	const uploadIcon = icon( 'M18.5 15v3.5H13V20h5.5a1.5 1.5 0 0 0 1.5-1.5V15h-1.5zM16 8l-4-4-4 4 1.1 1.1 2.1-2.2V16h1.5V6.9l2.2 2.2L16 8zM5.5 18.5V15H4v3.5A1.5 1.5 0 0 0 5.5 20H11v-1.5H5.5z' );
	const closeIcon = icon( 'M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z' );

	/* ------------------------------------------------------------------ */
	/* Export as JSON                                                     */
	/* ------------------------------------------------------------------ */

	function stripBlocks( blocks ) {
		return ( blocks || [] ).map( function ( block ) {
			return {
				name: block.name,
				// JSON round-trip drops functions / undefined and converts rich-text objects to strings.
				attributes: JSON.parse( JSON.stringify( block.attributes || {} ) ),
				innerBlocks: stripBlocks( block.innerBlocks ),
			};
		} );
	}

	function exportAsJson() {
		const blocks = select( 'core/block-editor' ).getBlocks();

		if ( ! blocks.length ) {
			notice( 'error', __( 'There is nothing to export. Add some blocks first.', 'envo-blocks' ) );
			return;
		}

		const title = select( 'core/editor' ).getEditedPostAttribute( 'title' ) || '';
		const payload = {
			schema: SCHEMA,
			title: title,
			blocks: stripBlocks( blocks ),
		};

		// Slugs of the chosen products and categories, so an import on another site finds the same items.
		apiFetch( { path: '/envo-blocks/v1/refs', method: 'POST', data: { blocks: payload.blocks } } )
			.then( function ( refs ) {
				if ( refs && typeof refs === 'object' && Object.keys( refs ).length ) {
					payload.refs = refs;
				}
			} )
			.catch( function () {} ) // The file is still useful without them.
			.then( function () {
				download( payload, title );
			} );
	}

	/** Export as ZIP: the JSON with the images of the page, so it can be imported on a site without internet access. */
	function exportAsZip() {
		const blocks = select( 'core/block-editor' ).getBlocks();

		if ( ! blocks.length ) {
			notice( 'error', __( 'There is nothing to export. Add some blocks first.', 'envo-blocks' ) );
			return;
		}

		const title = select( 'core/editor' ).getEditedPostAttribute( 'title' ) || '';
		notice( 'info', __( 'Preparing the ZIP file with the images…', 'envo-blocks' ) );

		apiFetch( { path: '/envo-blocks/v1/export-zip', method: 'POST', data: { title: title, blocks: stripBlocks( blocks ) } } )
			.then( function ( result ) {
				const binary = window.atob( ( result && result.zip ) || '' );
				const bytes = new Uint8Array( binary.length );
				for ( let i = 0; i < binary.length; i++ ) {
					bytes[ i ] = binary.charCodeAt( i );
				}
				saveFile( new Blob( [ bytes ], { type: 'application/zip' } ), ( cleanForSlug( title ) || 'template' ) + '.zip' );
				notice(
					'success',
					sprintf( /* translators: %d: number of images. */ _n( 'Page exported as ZIP with %d image.', 'Page exported as ZIP with %d images.', result.images || 0, 'envo-blocks' ), result.images || 0 )
				);
			} )
			.catch( function ( e ) {
				notice( 'error', errorMessage( e ) );
			} );
	}

	function saveFile( blob, name ) {
		const url = URL.createObjectURL( blob );
		const link = document.createElement( 'a' );
		link.href = url;
		link.download = name;
		document.body.appendChild( link );
		link.click();
		link.remove();
		setTimeout( function () {
			URL.revokeObjectURL( url );
		}, 1000 );
	}

	function download( payload, title ) {
		saveFile( new Blob( [ JSON.stringify( payload, null, 2 ) ], { type: 'application/json' } ), ( cleanForSlug( title ) || 'template' ) + '.json' );

		notice( 'success', __( 'Page exported as JSON.', 'envo-blocks' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Import                                                             */
	/* ------------------------------------------------------------------ */

	function toBlocks( list ) {
		const result = [];
		( Array.isArray( list ) ? list : [] ).forEach( function ( item ) {
			if ( ! item || typeof item.name !== 'string' || ! getBlockType( item.name ) ) {
				return;
			}
			const attributes = item.attributes && ! Array.isArray( item.attributes ) ? item.attributes : {};
			result.push( createBlock( item.name, attributes, toBlocks( item.innerBlocks ) ) );
		} );
		return result;
	}

	/**
	 * Sends a template file (JSON, or ZIP with images) to the site. With "keep" the site stores the result for the
	 * editor and answers with a token (Envo Blocks > Templates Import page).
	 */
	function uploadTemplateFile( file, keep, postId ) {
		const body = new window.FormData();
		body.append( 'file', file );
		if ( keep ) {
			body.append( 'keep', '1' );
		}
		if ( postId ) {
			body.append( 'post_id', String( postId ) );
		}
		return apiFetch( { path: '/envo-blocks/v1/import-file', method: 'POST', body: body } );
	}

	/**
	 * Puts blocks into the editor. Returns false when the user cancels.
	 */
	function applyBlocks( blocks, title ) {
		const existing = select( 'core/block-editor' ).getBlocks();
		const isEmpty =
			existing.length === 0 ||
			( existing.length === 1 && typeof wp.blocks.isUnmodifiedDefaultBlock === 'function' && wp.blocks.isUnmodifiedDefaultBlock( existing[ 0 ] ) );

		if ( ! isEmpty && ! window.confirm( __( 'This will replace the current content of the page. Continue?', 'envo-blocks' ) ) ) {
			return false;
		}

		if ( existing.length ) {
			dispatch( 'core/block-editor' ).replaceBlocks(
				existing.map( function ( block ) {
					return block.clientId;
				} ),
				blocks
			);
		} else {
			dispatch( 'core/block-editor' ).insertBlocks( blocks );
		}

		if ( title && ! select( 'core/editor' ).getEditedPostAttribute( 'title' ) ) {
			dispatch( 'core/editor' ).editPost( { title: title } );
		}

		return true;
	}

	/* ------------------------------------------------------------------ */
	/* Pop-up                                                             */
	/* ------------------------------------------------------------------ */

	const REOPEN_KEY = 'envoBlocksReopen';
	const caps = config.caps || {};

	// Previews shown from the assets server, kept on this site in one request a moment later.
	const keepQueue = {};
	let keepTimer = 0;

	function keepPreview( key ) {
		keepQueue[ key ] = true;
		if ( keepTimer ) {
			return;
		}
		keepTimer = setTimeout( function () {
			const keys = Object.keys( keepQueue );
			keys.forEach( function ( item ) {
				delete keepQueue[ item ];
			} );
			keepTimer = 0;
			apiFetch( { path: '/envo-blocks/v1/preview-cache', method: 'POST', data: { keys: keys } } ).catch( function () {} );
		}, 2000 );
	}

	/**
	 * Image that gets its address only when it comes near the visible part of the window (200 px ahead), so
	 * the previews are not all downloaded at once. The browser's own lazy loading starts far too early inside
	 * the scrolling window. A preview from the assets server ("set": 300/600 px, blurred placeholder, aspect
	 * ratio) shows its placeholder at once and asks this site to keep a copy after it was loaded.
	 */
	function LazyImg( props ) {
		const set = props.set && props.set.src ? props.set : null;
		const ref = useRef( null );
		const [ visible, setVisible ] = useState( ! ( 'IntersectionObserver' in window ) );
		const [ loaded, setLoaded ] = useState( false );

		useEffect(
			function () {
				if ( visible || ! ref.current ) {
					return;
				}
				const observer = new window.IntersectionObserver(
					function ( entries ) {
						if ( entries.some( function ( entry ) {
							return entry.isIntersecting;
						} ) ) {
							setVisible( true );
							observer.disconnect();
						}
					},
					{ rootMargin: '200px 0px' }
				);
				observer.observe( ref.current );
				return function () {
					observer.disconnect();
				};
			},
			[ visible ]
		);

		const style = {};
		if ( set && set.ratio ) {
			style.aspectRatio = set.ratio;
		}
		if ( set && set.lqip && ! loaded ) {
			style.backgroundImage = 'url(' + set.lqip + ')';
			style.backgroundSize = 'cover';
			style.backgroundPosition = 'center top';
		}

		return el( 'img', {
			ref: ref,
			src: visible ? ( set ? set.src : props.src ) : undefined,
			srcSet: visible && set && set.srcset ? set.srcset : undefined,
			sizes: set && set.srcset ? props.sizes || '300px' : undefined,
			alt: props.alt || '',
			decoding: 'async',
			style: style,
			className: ( props.className || '' ) + ( visible ? '' : ' is-lazy' ) + ( set && set.ratio ? ' has-ratio' : '' ),
			onLoad: function () {
				setLoaded( true );
				if ( set && set.remote && set.key ) {
					keepPreview( set.key );
				}
			},
		} );
	}

	/** Labels of the button shown instead of "Import" for PRO items without an active license. */
	function licenseLabel( type ) {
		return {
			buy: __( 'Buy PRO', 'envo-blocks' ),
			activate: __( 'Activate License', 'envo-blocks' ),
			renew: __( 'Renew License', 'envo-blocks' ),
		}[ type ] || __( 'Buy PRO', 'envo-blocks' );
	}

	/** "Buy PRO" / "Activate License" / "Renew License" link for a locked PRO item. */
	function licenseButton( action, extraClass ) {
		return el(
			'a',
			{
				className: 'envo-blocks-btn envo-blocks-btn--primary envo-blocks-btn--' + action.type + ( extraClass || '' ),
				href: action.url || '#',
				target: '_blank',
				rel: 'noopener noreferrer',
			},
			licenseLabel( action.type )
		);
	}

	function proBadge() {
		return el( 'span', { className: 'envo-blocks-badge envo-blocks-badge--pro' }, __( 'PRO', 'envo-blocks' ) );
	}

	/** Small pictures of the subpages under the card image; a click opens the template on that page. */
	function subpages( template, onOpen ) {
		const list = Array.isArray( template.pages ) ? template.pages.slice( 1 ) : [];
		if ( ! list.length ) {
			return null;
		}

		const shown = list.length > 4 ? list.slice( 0, 4 ) : list;
		const more = list.length - 3; // The 4th tile carries "+N" when there are more than 4 subpages.

		return el(
			'div',
			{ className: 'envo-blocks-card__subpages' },
			shown.map( function ( page, index ) {
				const isMore = list.length > 4 && 3 === index;
				return el(
					'button',
					{
						key: page.key,
						type: 'button',
						className: 'envo-blocks-card__subpage' + ( isMore ? ' is-more' : '' ),
						title: isMore ? sprintf( /* translators: %d: number of other pages. */ _n( '%d more page', '%d more pages', more, 'envo-blocks' ), more ) : page.name,
						'aria-label': isMore
							? sprintf( /* translators: 1: template name, 2: number of other pages. */ __( '%1$s: %2$d more pages', 'envo-blocks' ), template.name, more )
							: sprintf( /* translators: 1: template name, 2: page name. */ __( '%1$s: page %2$s', 'envo-blocks' ), template.name, page.name ),
						onClick: function () {
							onOpen( template, page.key );
						},
					},
					page.image ? el( LazyImg, { src: page.image, set: page.imageSet } ) : null,
					isMore ? el( 'span', { className: 'envo-blocks-card__more', 'aria-hidden': 'true' }, '+' + more ) : null
				);
			} )
		);
	}

	function Card( { template, onOpen } ) {
		const pages = Array.isArray( template.pages ) ? template.pages.length : 1;
		// The long screenshot of the homepage when there is one: the card is taller than the 4:3 picture.
		const first = Array.isArray( template.pages ) && template.pages[ 0 ] ? template.pages[ 0 ] : null;
		const image = ( first && first.preview ) || template.image;
		const imageSet = ( first && first.preview && first.previewSet ) || ( ! ( first && first.preview ) && template.imageSet ) || null;

		return el(
			'article',
			{ className: 'envo-blocks-card' },
			el(
				'div',
				{ className: 'envo-blocks-card__visual' },
			el(
				'button',
				{
					type: 'button',
					className: 'envo-blocks-card__media',
					onClick: function () {
						onOpen( template );
					},
					'aria-label': sprintf( /* translators: %s: template name. */ __( 'Open template %s', 'envo-blocks' ), template.name ),
				},
				image ? el( LazyImg, { src: image, set: imageSet } ) : null,
				template.premium ? proBadge() : null,
				pages > 1
					? el( 'span', { className: 'envo-blocks-card__pages' }, sprintf( /* translators: %d: number of pages. */ _n( '%d page', '%d pages', pages, 'envo-blocks' ), pages ) )
					: null
			),
			// Subpages lie over the bottom of the picture.
			subpages( template, onOpen )
			),
			el( 'h3', { className: 'envo-blocks-card__title' }, template.name ),
			el(
				'div',
				{ className: 'envo-blocks-card__actions' },
				// The live demo, when the template has one; the preview of each page is in the next step.
				template.link
					? el( 'a', { className: 'envo-blocks-btn envo-blocks-btn--secondary', href: template.link, target: '_blank', rel: 'noopener noreferrer' }, __( 'Live demo', 'envo-blocks' ) )
					: null,
				// A locked PRO template: "Buy PRO" (or Activate / Renew) right on the card; the image still opens the detail.
				template.action
					? licenseButton( template.action )
					: el(
							'button',
							{
								type: 'button',
								className: 'envo-blocks-btn envo-blocks-btn--primary',
								onClick: function () {
									onOpen( template );
								},
							},
							pages > 1 ? __( 'Choose pages', 'envo-blocks' ) : __( 'Select', 'envo-blocks' )
					  )
			)
		);
	}

	/** One plugin or the theme with its status and an install / activate button. */
	function Dependency( { item, type, busy, disabled, onAction } ) {
		const isTheme = 'theme' === type;
		const canInstall = isTheme ? caps.installThemes : caps.installPlugins;
		const canActivate = isTheme ? caps.switchThemes : caps.activatePlugins;
		const labels = {
			active: __( 'Active', 'envo-blocks' ),
			installed: __( 'Installed, not active', 'envo-blocks' ),
			missing: __( 'Not installed', 'envo-blocks' ),
		};

		let action = null;
		if ( 'missing' === item.status && item.link && ! item.wporg ) {
			// Our theme, not on WordPress.org (yet) or this user cannot install themes: its page says what it is and has the download.
			return el(
				'li',
				{ className: 'envo-blocks-dep is-missing' },
				el( 'span', { className: 'envo-blocks-dep__name' }, item.name ),
				el( 'span', { className: 'envo-blocks-dep__status' }, labels.missing ),
				el(
					'a',
					{ className: 'envo-blocks-btn envo-blocks-btn--small', href: item.link, target: '_blank', rel: 'noopener noreferrer' },
					__( 'Learn more', 'envo-blocks' )
				)
			);
		}
		if ( 'missing' === item.status && item.download ) {
			// Not on WordPress.org yet: download the ZIP and upload it in Appearance > Themes / Plugins > Add New.
			return el(
				'li',
				{ className: 'envo-blocks-dep is-missing' },
				el( 'span', { className: 'envo-blocks-dep__name' }, item.name ),
				el( 'span', { className: 'envo-blocks-dep__status' }, labels.missing ),
				el(
					'a',
					{ className: 'envo-blocks-btn envo-blocks-btn--small', href: item.download, target: '_blank', rel: 'noopener noreferrer' },
					__( 'Download ZIP', 'envo-blocks' )
				),
				el(
					'span',
					{ className: 'envo-blocks-dep__note' },
					isTheme
						? __( 'Then upload it in Appearance > Themes > Add New > Upload Theme.', 'envo-blocks' )
						: __( 'Then upload it in Plugins > Add New > Upload Plugin.', 'envo-blocks' )
				)
			);
		}
		if ( 'missing' === item.status && canInstall && canActivate ) {
			action = __( 'Install & activate', 'envo-blocks' );
		} else if ( 'missing' === item.status && canInstall ) {
			action = __( 'Install', 'envo-blocks' );
		} else if ( 'installed' === item.status && canActivate ) {
			action = __( 'Activate', 'envo-blocks' );
		}

		let button = null;
		if ( action ) {
			button = el(
				'button',
				{
					type: 'button',
					className: 'envo-blocks-btn envo-blocks-btn--small' + ( busy ? ' is-busy' : '' ),
					disabled: disabled,
					onClick: function () {
						onAction( type, item, 'missing' !== item.status || canActivate );
					},
				},
				busy ? ( 'missing' === item.status ? __( 'Installing…', 'envo-blocks' ) : __( 'Activating…', 'envo-blocks' ) ) : action
			);
		} else if ( 'active' !== item.status ) {
			button = el( 'a', { className: 'envo-blocks-dep__link', href: isTheme ? config.themesUrl : config.pluginsUrl, target: '_blank', rel: 'noopener noreferrer' }, __( 'Ask an administrator', 'envo-blocks' ) );
		}

		return el(
			'li',
			{ className: 'envo-blocks-dep is-' + item.status },
			el( 'span', { className: 'envo-blocks-dep__name' }, item.name ),
			el( 'span', { className: 'envo-blocks-dep__status' }, labels[ item.status ] || item.status ),
			button
		);
	}

	/**
	 * Templates Import page: our theme above the template grid while another theme is active – "Install & activate"
	 * from WordPress.org (the same installer as the theme of a template), "Activate" when it is installed, else a link
	 * to its page. The user can hide it (remembered in this browser).
	 */
	const THEME_BANNER_KEY = 'envoBlocksThemeBannerHidden';
	function themeBannerHidden() {
		try {
			return '1' === window.localStorage.getItem( THEME_BANNER_KEY );
		} catch ( e ) {
			return false;
		}
	}
	function ThemeBanner( { item, busySlug, onAction } ) {
		const [ hidden, setHidden ] = useState( themeBannerHidden );
		const busy = busySlug === 'theme:' + item.slug;
		if ( hidden || ( 'active' === item.status && ! busy ) ) {
			return null;
		}

		let action = null;
		let activate = true;
		if ( 'installed' === item.status && caps.switchThemes ) {
			action = __( 'Activate', 'envo-blocks' );
		} else if ( 'missing' === item.status && item.wporg && caps.installThemes ) {
			activate = !! caps.switchThemes;
			action = activate ? __( 'Install & activate', 'envo-blocks' ) : __( 'Install', 'envo-blocks' );
		}

		return el(
			'div',
			{ className: 'envo-blocks-theme-banner' },
			item.screenshot || config.themeShot
				? el( 'img', { className: 'envo-blocks-theme-banner__shot', src: item.screenshot || config.themeShot, alt: '', loading: 'lazy' } )
				: null,
			el(
				'div',
				{ className: 'envo-blocks-theme-banner__text' },
				el( 'strong', null, sprintf( /* translators: %s: theme name. */ __( '%s – the theme our templates are made for', 'envo-blocks' ), item.name ) ),
				el(
					'p',
					null,
					'missing' === item.status && ! item.wporg
						? __( 'A free block theme for WooCommerce. With it the templates look exactly like the demos; with another theme the fonts, spacing and shop pages can look different.', 'envo-blocks' )
						: __( 'A free block theme for WooCommerce from WordPress.org. With it the templates look exactly like the demos; with another theme the fonts, spacing and shop pages can look different. Your pages and products stay when you switch.', 'envo-blocks' )
				)
			),
			el(
				'div',
				{ className: 'envo-blocks-theme-banner__actions' },
				action
					? el(
							'button',
							{
								type: 'button',
								className: 'envo-blocks-btn' + ( busy ? ' is-busy' : '' ),
								disabled: !! busySlug,
								onClick: function () {
									onAction( 'theme', item, activate );
								},
							},
							busy ? ( 'missing' === item.status ? __( 'Installing…', 'envo-blocks' ) : __( 'Activating…', 'envo-blocks' ) ) : action
					  )
					: null,
				item.link
					? el( 'a', { className: action ? 'envo-blocks-theme-banner__link' : 'envo-blocks-btn', href: item.link, target: '_blank', rel: 'noopener noreferrer' }, __( 'Learn more', 'envo-blocks' ) )
					: null,
				config.wizardUrl
					? el( 'a', { className: 'envo-blocks-theme-banner__link', href: config.wizardUrl }, __( 'Store in 5 minutes', 'envo-blocks' ) )
					: null
			),
			el(
				'button',
				{
					type: 'button',
					className: 'envo-blocks-theme-banner__close',
					'aria-label': __( 'Hide', 'envo-blocks' ),
					onClick: function () {
						try {
							window.localStorage.setItem( THEME_BANNER_KEY, '1' );
						} catch ( e ) {}
						setHidden( true );
					},
				},
				closeIcon
			)
		);
	}

	function DependencyGroup( { title, help, items, type, busySlug, onAction } ) {
		// Plugins and the theme that are already active need nothing more, so they are not listed.
		items = ( items || [] ).filter( function ( item ) {
			return 'active' !== item.status || busySlug === type + ':' + item.slug;
		} );
		if ( ! items.length ) {
			return null;
		}

		return el(
			'div',
			{ className: 'envo-blocks-deps' },
			el( 'h4', { className: 'envo-blocks-deps__title' }, title ),
			help ? el( 'p', { className: 'envo-blocks-deps__help' }, help ) : null,
			el(
				'ul',
				{ className: 'envo-blocks-deps__list' },
				items.map( function ( item ) {
					return el( Dependency, {
						key: item.slug,
						item: item,
						type: type,
						busy: busySlug === type + ':' + item.slug,
						disabled: !! busySlug,
						onAction: onAction,
					} );
				} )
			)
		);
	}

	/** Second step: choose the page of the template, install what it needs, import. */
	/** Page templates of the theme for a post type ("" = default template). */
	function pageTemplates( postType ) {
		const all = config.pageTemplates || {};
		return Array.isArray( all[ postType ] ) ? all[ postType ] : [];
	}

	/**
	 * Preselected page template: a canvas template for templates with their own header and footer (when they are
	 * imported, withParts not false), otherwise the current one.
	 */
	function defaultPageTemplate( template, postType, current, withParts ) {
		if ( template && template.canvas && false !== withParts ) {
			const canvas = pageTemplates( postType ).find( function ( item ) {
				return item.canvas;
			} );
			if ( canvas ) {
				return canvas.slug;
			}
		}
		return current || '';
	}

	/**
	 * "Import the header and footer of the template" (templates whose files mark them). Off: only the content between
	 * them is imported and the theme shows its own header and footer.
	 */
	function PartsToggle( { template, value, onChange } ) {
		if ( ! template || ! template.parts || template.action ) {
			return null;
		}
		return el(
			'div',
			{ className: 'envo-blocks-parts' },
			el(
				'label',
				{ className: 'envo-blocks-parts__label' },
				el( 'input', {
					type: 'checkbox',
					checked: value,
					onChange: function ( event ) {
						onChange( event.target.checked );
					},
				} ),
				el( 'span', null, __( 'Import the header and footer of the template', 'envo-blocks' ) )
			),
			el(
				'p',
				{ className: 'envo-blocks-parts__help' },
				value
					? __( 'The page gets the header and footer of the demo store.', 'envo-blocks' )
					: __( 'Only the content between them is imported; the page shows the header and footer of your theme.', 'envo-blocks' )
			)
		);
	}

	/** "Page template" select with a note about canvas pages. */
	function PageTemplatePicker( { template, postType, value, onChange, withParts } ) {
		const ownParts = template && template.canvas && false !== withParts; // The imported page brings its header and footer.
		const choices = pageTemplates( postType );
		const hasCanvas = choices.some( function ( item ) {
			return item.canvas;
		} );
		const chosen = choices.find( function ( item ) {
			return item.slug === value;
		} );

		let note = null;
		if ( chosen && chosen.canvas ) {
			note = el(
				'p',
				{ className: 'envo-blocks-canvas-note' },
				__( 'Canvas page: the header and footer of your theme are not shown, only the imported content.', 'envo-blocks' ),
				ownParts ? ' ' + __( 'This template has its own header and footer.', 'envo-blocks' ) : ''
			);
		} else if ( ownParts ) {
			note = el(
				'p',
				{ className: 'envo-blocks-canvas-note is-warning' },
				hasCanvas
					? __( 'This template has its own header and footer. Choose a canvas page template, otherwise the header and footer of your theme are shown as well.', 'envo-blocks' )
					: __( 'This template has its own header and footer, and your theme has no canvas template for this type of content: the header and footer of your theme are shown as well. You can delete either of them after the import.', 'envo-blocks' )
			);
		}

		if ( choices.length < 2 ) {
			return note;
		}

		const id = 'envo-blocks-page-template-' + postType;
		return el(
			'div',
			{ className: 'envo-blocks-page-template' },
			el( 'label', { className: 'envo-blocks-page-template__label', htmlFor: id }, __( 'Page template', 'envo-blocks' ) ),
			el(
				'select',
				{
					id: id,
					className: 'envo-blocks-page-template__select',
					value: chosen ? value : '',
					onChange: function ( event ) {
						onChange( event.target.value );
					},
				},
				choices.map( function ( item ) {
					return el( 'option', { key: item.slug || 'default', value: item.slug }, item.canvas ? item.title + ' — ' + __( 'canvas', 'envo-blocks' ) : item.title );
				} )
			),
			note
		);
	}

	function Detail( { template, pageKey, onPage, onBack, onImport, onPreview, busy, previewBusy, busySlug, onDependency, needsReload, picker } ) {
		const pages = Array.isArray( template.pages ) && template.pages.length ? template.pages : [ { key: '', name: template.name, image: template.image, link: template.link } ];
		const page = pages.find( function ( item ) {
			return item.key === pageKey;
		} ) || pages[ 0 ];
		const missing = ( template.requiredPlugins || [] ).filter( function ( item ) {
			return 'active' !== item.status;
		} );
		const link = page.link || template.link;
		const locked = template.action || null; // PRO template without an active license.

		let primaryLabel = sprintf( /* translators: %s: page name. */ __( 'Import "%s"', 'envo-blocks' ), page.name );
		if ( busy ) {
			primaryLabel = __( 'Importing…', 'envo-blocks' );
		} else if ( needsReload ) {
			primaryLabel = __( 'Reload the editor and import', 'envo-blocks' );
		}

		return el(
			'div',
			{ className: 'envo-blocks-detail' + ( pages.length > 1 ? ' has-pages' : '' ) },
			el(
				'div',
				{ className: 'envo-blocks-detail__main' },
				el(
					'div',
					{ className: 'envo-blocks-detail__preview', tabIndex: 0, 'aria-label': sprintf( /* translators: %s: page name. */ __( 'Preview of %s', 'envo-blocks' ), page.name ) },
					page.preview || page.image
						? el( 'img', { key: page.key, src: page.preview || page.image, alt: '' } )
						: el( 'div', { className: 'envo-blocks-detail__empty' }, __( 'No preview image', 'envo-blocks' ) )
				)
			),
			pages.length > 1
				? el(
						'div',
						{ className: 'envo-blocks-pages', role: 'group', 'aria-label': __( 'Pages of the template', 'envo-blocks' ) },
						pages.map( function ( item ) {
							const active = item.key === page.key;
							return el(
								'button',
								{
									key: item.key,
									type: 'button',
									className: 'envo-blocks-page' + ( active ? ' is-active' : '' ),
									'aria-pressed': active ? 'true' : 'false',
									onClick: function () {
										onPage( item.key );
									},
								},
								el( 'span', { className: 'envo-blocks-page__img' }, item.image ? el( LazyImg, { key: item.image, src: item.image, set: item.imageSet, sizes: '200px' } ) : null ),
								el( 'span', { className: 'envo-blocks-page__name' }, item.name )
							);
						} )
				  )
				: null,
			el(
				'aside',
				{ className: 'envo-blocks-detail__side' },
				el(
					'div',
					{ className: 'envo-blocks-detail__scroll' },
				el(
					'button',
					{ type: 'button', className: 'envo-blocks-back', onClick: onBack },
					'← ' + __( 'All templates', 'envo-blocks' )
				),
				el( 'h3', { className: 'envo-blocks-detail__title' }, template.name ),
				template.description ? el( 'p', { className: 'envo-blocks-detail__desc' }, template.description ) : null,
				el(
					'p',
					{ className: 'envo-blocks-detail__info' },
					__( 'Only the layout, texts and settings of the page are imported. Products, categories, reviews, coupons and other dynamic content are not imported: the blocks load the content of your own store.', 'envo-blocks' )
				),
				locked ? null : picker || null,
				el( DependencyGroup, {
					title: __( 'Required plugins', 'envo-blocks' ),
					help: __( 'The pages use blocks of these plugins. They must be active before you import.', 'envo-blocks' ),
					items: template.requiredPlugins,
					type: 'plugin',
					busySlug: busySlug,
					onAction: onDependency,
				} ),
				el( DependencyGroup, {
					title: __( 'Recommended plugins', 'envo-blocks' ),
					items: template.recommendedPlugins,
					type: 'plugin',
					busySlug: busySlug,
					onAction: onDependency,
				} ),
				el( DependencyGroup, {
					title: __( 'Recommended theme', 'envo-blocks' ),
					help: __( 'The template was designed with this theme. Activating a theme changes the look of the whole site.', 'envo-blocks' ),
					items: template.recommendedTheme ? [ template.recommendedTheme ] : [],
					type: 'theme',
					busySlug: busySlug,
					onAction: onDependency,
				} )
				),
				el(
					'div',
					{ className: 'envo-blocks-detail__actions' },
					missing.length
						? el(
								'p',
								{ className: 'envo-blocks-detail__note', role: 'status' },
								sprintf( /* translators: %s: list of plugin names. */ __( 'Activate the required plugins first: %s.', 'envo-blocks' ), missing.map( function ( item ) {
									return item.name;
								} ).join( ', ' ) )
						  )
						: null,
					locked
						? el(
								'p',
								{ className: 'envo-blocks-detail__pro' },
								'activate' === locked.type
									? __( 'This is a PRO template. Activate your PRO license to preview and import it.', 'envo-blocks' )
									: 'renew' === locked.type
									? __( 'This is a PRO template. Your PRO license has expired; renew it to preview and import the template.', 'envo-blocks' )
									: __( 'This is a PRO template. It is included in Envo Blocks PRO.', 'envo-blocks' )
						  )
						: null,
					locked
						? null
						: el(
								'p',
								{ className: 'envo-blocks-detail__warning' },
								PAGE
									? __( 'Importing creates a new draft post or page with this content and opens it in the editor. Nothing else on your site changes.', 'envo-blocks' )
									: __( 'Importing replaces the current content of the page you are editing. To add only a part of a page, use Sections.', 'envo-blocks' )
						  ),
					needsReload && ! missing.length
						? el( 'p', { className: 'envo-blocks-detail__note', role: 'status' }, __( 'New plugins were activated. The editor reloads to load their blocks; your changes are saved as a draft first.', 'envo-blocks' ) )
						: null,
					locked
						? el( 'div', { className: 'envo-blocks-detail__buttons' }, licenseButton( locked ) )
						: el(
						'div',
						{ className: 'envo-blocks-detail__buttons' },
						onPreview && el(
							'button',
							{
								type: 'button',
								className: 'envo-blocks-btn envo-blocks-btn--secondary' + ( previewBusy ? ' is-busy' : '' ),
								disabled: busy || previewBusy || !! busySlug || missing.length > 0 || needsReload,
								title: __( 'Opens this page in a new tab with your theme and your products, without changing the page you are editing.', 'envo-blocks' ),
								onClick: function () {
									onPreview( template, page );
								},
							},
							previewBusy ? __( 'Preparing…', 'envo-blocks' ) : __( 'Preview', 'envo-blocks' )
						),
						el(
							'button',
							{
								type: 'button',
								className: 'envo-blocks-btn envo-blocks-btn--primary' + ( busy ? ' is-busy' : '' ),
								disabled: busy || !! busySlug || missing.length > 0,
								onClick: function () {
									onImport( template, page );
								},
							},
							primaryLabel
						)
					),
					link
						? el( 'a', { className: 'envo-blocks-detail__demo', href: link, target: '_blank', rel: 'noopener noreferrer' }, __( 'Open the live demo', 'envo-blocks' ) + ' ↗' )
						: null
				)
			)
		);
	}

	/**
	 * Inserts blocks where the user is editing: after the selected block (in the same parent when that parent
	 * accepts them, otherwise after its top-level block), in place of an empty paragraph, or at the end.
	 */
	function insertAtCursor( blocks ) {
		const editor = select( 'core/block-editor' );
		const actions = dispatch( 'core/block-editor' );
		const selected = editor.getSelectedBlockClientId();

		if ( ! selected ) {
			actions.insertBlocks( blocks, editor.getBlockCount(), undefined, true );
			return;
		}

		const block = editor.getBlock( selected );
		if ( block && typeof wp.blocks.isUnmodifiedDefaultBlock === 'function' && wp.blocks.isUnmodifiedDefaultBlock( block ) ) {
			actions.replaceBlocks( selected, blocks );
			return;
		}

		let target = selected;
		let root = editor.getBlockRootClientId( target );
		const fits = function ( rootId ) {
			return blocks.every( function ( item ) {
				return editor.canInsertBlockType( item.name, rootId );
			} );
		};
		while ( root && ! fits( root ) ) {
			target = root;
			root = editor.getBlockRootClientId( target );
		}

		actions.insertBlocks( blocks, editor.getBlockIndex( target ) + 1, root || undefined, true );
	}

	/** Short PRO line on the card of a locked block. */
	function proLine( action ) {
		return {
			activate: __( 'PRO – activate your license', 'envo-blocks' ),
			renew: __( 'PRO – license expired', 'envo-blocks' ),
		}[ action.type ] || __( 'Included in Envo Blocks PRO', 'envo-blocks' );
	}

	function SectionCard( { section, busy, disabled, onInsert, layout } ) {
		const missing = ( section.requiredPlugins || [] ).filter( function ( item ) {
			return 'active' !== item.status;
		} );
		const locked = section.action || null; // PRO block without the PRO plugin or an active license.
		const media = [
			section.image ? el( LazyImg, { key: 'img', src: section.image, set: section.imageSet, sizes: '(max-width: 960px) 100vw, 420px' } ) : el( 'span', { key: 'img', className: 'envo-blocks-section__empty' }, section.name ),
			section.premium ? el( 'span', { key: 'pro' }, proBadge() ) : null,
		];

		return el(
			'article',
			{ className: 'envo-blocks-section' + ( locked ? ' is-locked' : '' ) },
			// A locked block opens its live demo; the others are inserted.
			locked && section.link
				? el(
						'a',
						{
							className: 'envo-blocks-section__media',
							href: section.link,
							target: '_blank',
							rel: 'noopener noreferrer',
							'aria-label': sprintf( /* translators: %s: block name. */ __( 'Live demo of %s (opens in a new tab)', 'envo-blocks' ), section.name ),
						},
						media
				  )
				: el(
						'button',
						{
							type: 'button',
							className: 'envo-blocks-section__media',
							disabled: !! locked || disabled || missing.length > 0,
							onClick: function () {
								onInsert( section );
							},
							'aria-label': layout
								? sprintf( /* translators: %s: layout name. */ __( 'Use %s', 'envo-blocks' ), section.name )
								: sprintf( /* translators: %s: block name. */ __( 'Insert %s', 'envo-blocks' ), section.name ),
						},
						media
				  ),
			el(
				'div',
				{ className: 'envo-blocks-section__bar' },
				el(
					'div',
					{ className: 'envo-blocks-section__text' },
					el( 'h3', { className: 'envo-blocks-section__title' }, section.name ),
					locked
						? el( 'span', { className: 'envo-blocks-section__pro' }, proLine( locked ) )
						: missing.length
						? el( 'span', { className: 'envo-blocks-section__needs' }, sprintf( /* translators: %s: list of plugin names. */ __( 'Needs %s', 'envo-blocks' ), missing.map( function ( item ) {
								return item.name;
						  } ).join( ', ' ) ) )
						: el(
								'span',
								{ className: 'envo-blocks-section__cats' },
								layout && section.color ? el( 'i', { className: 'envo-blocks-section__swatch', style: { background: section.color }, 'aria-hidden': 'true' } ) : null,
								( section.categories || [] ).join( ' · ' )
						  )
				),
				locked
					? el(
							'div',
							{ className: 'envo-blocks-section__actions' },
							section.link
								? el(
										'a',
										{
											className: 'envo-blocks-btn envo-blocks-btn--secondary envo-blocks-btn--small-primary',
											href: section.link,
											target: '_blank',
											rel: 'noopener noreferrer',
										},
										__( 'Live demo', 'envo-blocks' )
								  )
								: null,
							licenseButton( locked, ' envo-blocks-btn--small-primary' )
					  )
					: el(
					'div',
					{ className: 'envo-blocks-section__actions' },
					layout && section.link
						? el( 'a', { className: 'envo-blocks-btn envo-blocks-btn--secondary envo-blocks-btn--small-primary', href: section.link, target: '_blank', rel: 'noopener noreferrer' }, __( 'Preview', 'envo-blocks' ) )
						: null,
					el(
						'button',
						{
							type: 'button',
							className: 'envo-blocks-btn envo-blocks-btn--primary envo-blocks-btn--small-primary' + ( busy ? ' is-busy' : '' ),
							disabled: disabled || missing.length > 0,
							onClick: function () {
								onInsert( section );
							},
						},
						layout ? ( busy ? __( 'Creating…', 'envo-blocks' ) : __( 'Use', 'envo-blocks' ) ) : busy ? __( 'Inserting…', 'envo-blocks' ) : __( 'Insert', 'envo-blocks' )
					)
				)
			)
		);
	}

	// Free and PRO items of a list: the "All / Free / PRO" filter is shown only when the list has both.
	function editionCounts( items ) {
		const pro = ( items || [] ).filter( function ( item ) {
			return item.premium;
		} ).length;
		return { all: ( items || [] ).length, free: ( items || [] ).length - pro, pro: pro };
	}

	function inEdition( item, edition ) {
		return 'all' === edition || ( 'pro' === edition ) === !! item.premium;
	}

	function EditionFilter( { edition, counts, onChange } ) {
		if ( ! counts.free || ! counts.pro ) {
			return null;
		}
		return el(
			'div',
			{ className: 'envo-blocks-edition', role: 'group', 'aria-label': __( 'Free or PRO', 'envo-blocks' ) },
			[
				[ 'all', __( 'All', 'envo-blocks' ) ],
				[ 'free', __( 'Free', 'envo-blocks' ) ],
				[ 'pro', __( 'PRO', 'envo-blocks' ) ],
			].map( function ( item ) {
				return el(
					'button',
					{
						key: item[ 0 ],
						type: 'button',
						className: 'envo-blocks-edition__btn' + ( edition === item[ 0 ] ? ' is-active' : '' ),
						'aria-pressed': edition === item[ 0 ],
						onClick: function () {
							onChange( item[ 0 ] );
						},
					},
					item[ 1 ],
					el( 'span', { className: 'envo-blocks-edition__count' }, counts[ item[ 0 ] ] )
				);
			} )
		);
	}

	// The switches and the note stay in view; only the list of categories scrolls when the window is low.
	function SectionSidebar( { categories, counts, checked, onToggle, onClear, help, top, edition } ) {
		const ref = useRef( null );
		// Ends at the bottom of the window also before it sticks (on the Templates Import page it starts lower).
		useEffect( function () {
			const fit = function () {
				const aside = ref.current;
				if ( ! aside || 'sticky' !== window.getComputedStyle( aside ).position ) {
					if ( aside ) {
						aside.style.maxHeight = '';
					}
					return;
				}
				aside.style.maxHeight = Math.max( 240, window.innerHeight - Math.max( aside.getBoundingClientRect().top, 0 ) - 24 ) + 'px';
			};
			fit();
			window.addEventListener( 'resize', fit );
			window.addEventListener( 'scroll', fit, true );
			return function () {
				window.removeEventListener( 'resize', fit );
				window.removeEventListener( 'scroll', fit, true );
			};
		}, [] );
		return el(
			'aside',
			{ className: 'envo-blocks-sidebar', ref: ref },
			el(
				'div',
				{ className: 'envo-blocks-sidebar__head' },
				top || null,
				edition ? el( EditionFilter, edition ) : null,
				el(
					'div',
					{ className: 'envo-blocks-sidebar__titlebar' },
					el( 'h3', { className: 'envo-blocks-sidebar__title' }, __( 'Show', 'envo-blocks' ) ),
					checked.length
						? el( 'button', { type: 'button', className: 'envo-blocks-sidebar__clear', onClick: onClear }, __( 'Show all', 'envo-blocks' ) )
						: null
				)
			),
			el(
				'ul',
				{ className: 'envo-blocks-sidebar__list', tabIndex: 0, 'aria-label': __( 'Categories', 'envo-blocks' ) },
				categories.map( function ( name ) {
					const id = 'envo-blocks-cat-' + name.toLowerCase().replace( /[^a-z0-9]+/g, '-' );
					return el(
						'li',
						{ key: name },
						el(
							'label',
							{ className: 'envo-blocks-check', htmlFor: id },
							el( 'input', {
								id: id,
								type: 'checkbox',
								checked: checked.indexOf( name ) !== -1,
								onChange: function () {
									onToggle( name );
								},
							} ),
							el( 'span', { className: 'envo-blocks-check__label' }, name ),
							el( 'span', { className: 'envo-blocks-check__count' }, counts[ name ] || 0 )
						)
					);
				} )
			),
			help ? el( 'p', { className: 'envo-blocks-sidebar__help' }, help ) : null
		);
	}

	// The window opens again in the mode used last (Templates, Sections or Presets) while the editor stays open.
	let lastMode = 'templates';

	function Popup( { onClose, initial } ) {
		const [ templates, setTemplates ] = useState( null );
		const [ error, setError ] = useState( '' );
		const [ query, setQuery ] = useState( '' );
		const [ tplChecked, setTplChecked ] = useState( [] ); // Checked template categories; none = all.
		const [ busyId, setBusyId ] = useState( '' );
		const [ view, setView ] = useState( initial || null ); // { id, page } of the open template, or null for the grid.
		const [ busySlug, setBusySlug ] = useState( '' );
		const [ needsReload, setNeedsReload ] = useState( false );
		const [ previewBusy, setPreviewBusy ] = useState( false );
		const [ mode, setMode ] = useState( initial ? 'templates' : PAGE ? START_MODE : lastMode ); // "templates", "blocks", "presets" or "layouts".
		const [ creating, setCreating ] = useState( null ); // Admin page: { template, page } waiting for the post type and title.
		const [ pageTemplate, setPageTemplate ] = useState( null ); // Editor: chosen page template; null = preselected.
		const [ withParts, setWithParts ] = useState( true ); // Import the header and footer of the template.
		const [ libraries, setLibraries ] = useState( {} ); // Loaded blocks per type: { sections: [], presets: [] }.
		const [ checked, setChecked ] = useState( [] );
		const [ edition, setEdition ] = useState( 'all' ); // "all", "free" or "pro" items, in every mode.
		const [ insertingId, setInsertingId ] = useState( '' );
		const [ fileBusy, setFileBusy ] = useState( false );
		const searchRef = useRef( null );
		const fileRef = useRef( null );

		// "Sections" (mode "blocks") and "Presets" are both libraries of blocks inserted at the cursor; they differ only in their source.
		// "Layouts" are designs of the store pages: "Use" makes a store layout of one and opens it.
		const isLibrary = 'templates' !== mode;
		const libraryType = 'presets' === mode || 'layouts' === mode ? mode : 'sections';
		const sections = isLibrary && undefined !== libraries[ libraryType ] ? libraries[ libraryType ] : null;

		const load = useCallback( function () {
			return apiFetch( { path: '/envo-blocks/v1/templates' } ).then( function ( data ) {
				setTemplates( Array.isArray( data ) ? data : [] );
			} );
		}, [] );

		useEffect( function () {
			let active = true;
			load().catch( function ( e ) {
				if ( active ) {
					setError( errorMessage( e ) );
					setTemplates( [] );
				}
			} );
			return function () {
				active = false;
			};
		}, [ load ] );

		useEffect(
			function () {
				if ( PAGE ) {
					return; // A normal admin page: it scrolls and has nothing to close.
				}
				document.body.classList.add( 'envo-blocks-no-scroll' );
				if ( searchRef.current ) {
					searchRef.current.focus();
				}

				function onKey( event ) {
					if ( event.key === 'Escape' ) {
						event.stopPropagation();
						onClose();
					}
				}
				document.addEventListener( 'keydown', onKey, true );

				return function () {
					document.removeEventListener( 'keydown', onKey, true );
					document.body.classList.remove( 'envo-blocks-no-scroll' );
				};
			},
			[ onClose ]
		);

		const templateCategories = useMemo(
			function () {
				const found = [];
				const counts = {};
				( templates || [] ).forEach( function ( item ) {
					( Array.isArray( item.categories ) ? item.categories : [] ).forEach( function ( name ) {
						if ( found.indexOf( name ) === -1 ) {
							found.push( name );
						}
						counts[ name ] = ( counts[ name ] || 0 ) + 1;
					} );
				} );
				return { names: found, counts: counts };
			},
			[ templates ]
		);

		// The search looks in the name, the description, the categories and the page names of a template; every
		// word has to be found, in any order.
		const words = useMemo(
			function () {
				return query.trim().toLowerCase().split( /\s+/ ).filter( Boolean );
			},
			[ query ]
		);
		const templateText = function ( item ) {
			return [ item.name, item.description ]
				.concat( item.categories || [] )
				.concat( ( item.pages || [] ).map( function ( page ) {
					return page.name;
				} ) )
				.join( ' ' )
				.toLowerCase();
		};
		const found = function ( text ) {
			return words.every( function ( word ) {
				return text.indexOf( word ) !== -1;
			} );
		};

		const visible = useMemo(
			function () {
				return ( templates || [] ).filter( function ( item ) {
					const inCategory = ! tplChecked.length || ( Array.isArray( item.categories ) && item.categories.some( function ( name ) {
						return tplChecked.indexOf( name ) !== -1;
					} ) );
					return inCategory && inEdition( item, edition ) && ( ! words.length || found( templateText( item ) ) );
				} );
			},
			[ templates, words, tplChecked, edition ]
		);

		// A section also matches the description and categories of the demo it comes from: the same demo name in the
		// link (/template-demos/<demo>/; the host differs on the demo site itself).
		const demoName = function ( link ) {
			const m = /\/template-demos\/([^/?#]+)/.exec( String( link || '' ) );
			return m ? m[ 1 ] : '';
		};
		const demoTexts = useMemo(
			function () {
				const map = {};
				( templates || [] ).forEach( function ( item ) {
					const name = demoName( item.link );
					if ( name ) {
						map[ name ] = templateText( item );
					}
				} );
				return map;
			},
			[ templates ]
		);

		useEffect(
			function () {
				if ( ! isLibrary || null !== sections ) {
					return;
				}
				const type = libraryType;
				function store( list ) {
					setLibraries( function ( previous ) {
						const next = Object.assign( {}, previous );
						next[ type ] = list;
						return next;
					} );
				}
				apiFetch( { path: '/envo-blocks/v1/sections?type=' + encodeURIComponent( type ) } )
					.then( function ( data ) {
						store( Array.isArray( data ) ? data : [] );
					} )
					.catch( function ( e ) {
						setError( errorMessage( e ) );
						store( [] );
					} );
			},
			[ isLibrary, libraryType, sections ]
		);

		const sectionCategories = useMemo(
			function () {
				const found = [];
				const counts = {};
				( sections || [] ).forEach( function ( item ) {
					( item.categories || [] ).forEach( function ( name ) {
						if ( found.indexOf( name ) === -1 ) {
							found.push( name );
						}
						counts[ name ] = ( counts[ name ] || 0 ) + 1;
					} );
				} );
				return { names: found, counts: counts };
			},
			[ sections ]
		);

		const visibleSections = useMemo(
			function () {
				return ( sections || [] ).filter( function ( item ) {
					const inCategory = ! checked.length || ( item.categories || [] ).some( function ( name ) {
						return checked.indexOf( name ) !== -1;
					} );
					const text = [ item.name ].concat( item.categories || [] ).join( ' ' ).toLowerCase() + ' ' + ( demoTexts[ demoName( item.link ) ] || '' );
					return inCategory && inEdition( item, edition ) && ( ! words.length || found( text ) );
				} );
			},
			[ sections, checked, words, edition, demoTexts ]
		);

		function insertSection( section ) {
			if ( insertingId ) {
				return;
			}
			if ( 'layouts' === libraryType ) {
				useLayout( section );
				return;
			}
			setError( '' );
			setInsertingId( section.id );

			apiFetch( {
				path: '/envo-blocks/v1/section',
				method: 'POST',
				data: { id: section.id, post_id: currentPostId(), type: libraryType },
			} )
				.then( function ( result ) {
					const blocks = toBlocks( result && result.blocks );
					if ( ! blocks.length ) {
						throw new Error( __( 'It does not contain anything that can be used on this site.', 'envo-blocks' ) );
					}
					insertAtCursor( blocks );
					notice( 'success', sprintf( /* translators: %s: block name. */ __( '"%s" inserted.', 'envo-blocks' ), section.name ) );
					onClose();
				} )
				.catch( function ( e ) {
					setError( errorMessage( e ) );
				} )
				.then( function () {
					setInsertingId( '' );
				} );
		}

		/** Makes a draft store layout of a design and opens it in the editor. */
		function useLayout( section ) {
			if ( ! PAGE ) {
				const editor = select( 'core/editor' );
				if ( editor.isEditedPostDirty() && ! window.confirm( __( 'This makes a new store layout and opens it. Changes of this page that are not saved are lost. Go on?', 'envo-blocks' ) ) ) {
					return;
				}
			}
			setError( '' );
			setInsertingId( section.id );
			apiFetch( { path: '/envo-blocks/v1/layout', method: 'POST', data: { design: section.id } } )
				.then( function ( result ) {
					if ( ! result || ! result.editUrl ) {
						throw new Error( __( 'The layout could not be created.', 'envo-blocks' ) );
					}
					window.location.href = result.editUrl;
				} )
				.catch( function ( e ) {
					setError( errorMessage( e ) );
					setInsertingId( '' );
				} );
		}

		// A different template starts with its own preselected page template.
		const viewId = view ? view.id : '';
		useEffect(
			function () {
				setPageTemplate( null );
				setWithParts( true );
			},
			[ viewId ]
		);

		const current = view && templates
			? templates.find( function ( item ) {
					return item.id === view.id;
			  } )
			: null;
		// Our theme (the recommended theme of the templates) with its current status, for the Templates Import banner.
		const ourTheme = PAGE && templates
			? ( templates.find( function ( item ) {
					return item.recommendedTheme && config.ourThemeSlug === item.recommendedTheme.slug;
			  } ) || {} ).recommendedTheme || null
			: null;

		function installDependency( type, item, activate ) {
			if ( busySlug ) {
				return;
			}
			if ( 'theme' === type && activate && ! window.confirm( sprintf( /* translators: %s: theme name. */ __( 'Activate the theme "%s"? It changes the look of the whole site.', 'envo-blocks' ), item.name ) ) ) {
				return;
			}
			setError( '' );
			setBusySlug( type + ':' + item.slug );

			apiFetch( {
				path: '/envo-blocks/v1/dependency',
				method: 'POST',
				data: { type: type, slug: item.slug, activate: activate },
			} )
				.then( function ( result ) {
					if ( ! PAGE && 'plugin' === type && result && 'active' === result.status && 'active' !== item.status ) {
						// Blocks of a newly activated plugin are known to the editor only after a reload.
						setNeedsReload( true );
					}
					if ( PAGE && 'theme' === type && result && 'active' === result.status && 'active' !== item.status ) {
						// Templates Import: the page templates of the import dialog come from the new theme.
						window.location.reload();
						return new Promise( function () {} ); // Keeps the button busy until the page reloads.
					}
					notice( 'success', 'active' === ( result && result.status )
						? sprintf( /* translators: %s: plugin or theme name. */ __( '"%s" is active.', 'envo-blocks' ), item.name )
						: sprintf( /* translators: %s: plugin or theme name. */ __( '"%s" is installed.', 'envo-blocks' ), item.name ) );
					return load();
				} )
				.catch( function ( e ) {
					setError( errorMessage( e ) );
				} )
				.then( function () {
					setBusySlug( '' );
				} );
		}

		/**
		 * Live preview: the page is built from the template like an import, saved in a temporary draft
		 * and opened in a new tab. The tab is opened at once so the browser does not block it as a pop-up.
		 */
		function previewPage( template, page ) {
			if ( previewBusy ) {
				return;
			}
			const tab = window.open( '', '_blank' );
			setError( '' );
			setPreviewBusy( true );

			apiFetch( {
				path: '/envo-blocks/v1/import',
				method: 'POST',
				data: { id: template.id, page: page.key || '', post_id: currentPostId(), media: false, parts: withParts }, // The preview keeps the images remote.
			} )
				.then( function ( result ) {
					const blocks = toBlocks( result && result.blocks );
					if ( ! blocks.length ) {
						throw new Error( __( 'The template does not contain any usable blocks.', 'envo-blocks' ) );
					}
					// The page template chosen for the import (a canvas template for templates with their own header
					// and footer), so the preview looks like the imported page.
					const pageTemplate = PAGE ? defaultPageTemplate( template, 'page', '', withParts ) : editorTemplates( template ).chosen;
					return apiFetch( {
						path: '/envo-blocks/v1/preview',
						method: 'POST',
						data: { title: page.name, content: wp.blocks.serialize( blocks ), template: pageTemplate || '' },
					} );
				} )
				.then( function ( result ) {
					if ( ! result || ! result.url ) {
						throw new Error( __( 'The preview could not be created.', 'envo-blocks' ) );
					}
					if ( tab && ! tab.closed ) {
						tab.location.href = result.url;
					} else {
						window.open( result.url, '_blank', 'noopener' );
					}
				} )
				.catch( function ( e ) {
					if ( tab && ! tab.closed ) {
						tab.close();
					}
					setError( errorMessage( e ) );
				} )
				.then( function () {
					setPreviewBusy( false );
				} );
		}

		function reloadAndImport( template, page ) {
			try {
				window.sessionStorage.setItem( REOPEN_KEY, JSON.stringify( { id: template.id, page: page.key, autoImport: true, parts: withParts } ) );
			} catch ( e ) {}

			const editor = select( 'core/editor' );
			const unsaved = editor.isEditedPostDirty() || editor.isEditedPostNew();
			( unsaved ? Promise.resolve( dispatch( 'core/editor' ).savePost() ) : Promise.resolve() ).then( function () {
				// A new post has its own address only after the first save, so open it by ID instead of reloading post-new.php.
				const id = currentPostId();
				window.location.href = id ? 'post.php?post=' + encodeURIComponent( id ) + '&action=edit' : window.location.href;
			} );
		}

		/** Editor: post type, current and chosen page template of the post being edited. */
		function editorTemplates( template ) {
			if ( PAGE ) {
				return null;
			}
			const editor = select( 'core/editor' );
			const postType = editor.getCurrentPostType();
			const now = editor.getEditedPostAttribute( 'template' ) || '';
			return {
				postType: postType,
				current: now,
				chosen: null !== pageTemplate ? pageTemplate : defaultPageTemplate( template, postType, now, withParts ),
			};
		}

		function importTemplate( template, page, save, parts ) {
			const keepParts = undefined === parts ? withParts : parts; // After a reload: the choice made before it.
			if ( busyId ) {
				return;
			}
			if ( PAGE ) {
				setCreating( { template: template, page: page } );
				return;
			}
			if ( needsReload ) {
				reloadAndImport( template, page );
				return;
			}
			setError( '' );
			setBusyId( template.id );

			apiFetch( {
				path: '/envo-blocks/v1/import',
				method: 'POST',
				data: {
					id: template.id,
					page: page.key || '',
					post_id: currentPostId(),
					parts: keepParts,
				},
			} )
				.then( function ( result ) {
					const blocks = toBlocks( result && result.blocks );
					if ( ! blocks.length ) {
						throw new Error( __( 'The template does not contain any usable blocks.', 'envo-blocks' ) );
					}
					if ( applyBlocks( blocks, result.title ) ) {
						// The page template chosen in the detail (not after the admin page: it was chosen there).
						const tpl = save ? null : editorTemplates( template );
						if ( tpl && tpl.chosen !== tpl.current && pageTemplates( tpl.postType ).some( function ( item ) {
							return item.slug === tpl.chosen;
						} ) ) {
							dispatch( 'core/editor' ).editPost( { template: tpl.chosen } );
						}
						if ( save ) {
							// A draft made on the Envo Blocks > Templates Import page keeps its content right away.
							dispatch( 'core/editor' ).savePost();
						}
						notice( 'success', sprintf( /* translators: %s: page name. */ __( '"%s" imported.', 'envo-blocks' ), page.name ) );
						onClose();
					}
				} )
				.catch( function ( e ) {
					setError( errorMessage( e ) );
				} )
				.then( function () {
					setBusyId( '' );
				} );
		}

		// After "Reload the editor and import": continue with the import once the templates are loaded.
		useEffect(
			function () {
				if ( ! current || ! view || ! view.autoImport || autoImported ) {
					return;
				}
				autoImported = true;
				const page = ( current.pages || [] ).find( function ( item ) {
					return item.key === view.page;
				} ) || ( current.pages || [] )[ 0 ];
				setView( { id: view.id, page: view.page } );
				const parts = false !== view.parts;
				setWithParts( parts );
				if ( ! page ) {
					return;
				}
				// The editor loads the post content after start-up; importing earlier would be overwritten.
				let tries = 0;
				( function waitForEditor() {
					const editor = select( 'core/editor' );
					const ready = typeof editor.__unstableIsEditorReady === 'function' ? editor.__unstableIsEditorReady() : true;
					if ( ready || tries++ > 40 ) {
						setTimeout( function () {
							importTemplate( current, page, !! view.save, parts );
						}, 300 );
					} else {
						setTimeout( waitForEditor, 250 );
					}
				} )();
			},
			[ current ]
		);

		/** Import from file: a JSON file (Export as JSON) or a ZIP file with the images (Export as ZIP, our support). */
		function importFromFile( file ) {
			if ( ! file || fileBusy ) {
				return;
			}
			setError( '' );
			setFileBusy( true );

			uploadTemplateFile( file, PAGE, currentPostId() )
				.then( function ( result ) {
					if ( PAGE ) {
						if ( ! result || ! result.token ) {
							throw new Error( __( 'The file could not be read.', 'envo-blocks' ) );
						}
						setCreating( { file: result.token, name: result.title || '' } );
						return;
					}
					const blocks = toBlocks( result && result.blocks );
					if ( ! blocks.length ) {
						throw new Error( __( 'The file does not contain any usable blocks.', 'envo-blocks' ) );
					}
					if ( applyBlocks( blocks, result.title ) ) {
						notice( 'success', sprintf( /* translators: %s: page name. */ __( '"%s" imported.', 'envo-blocks' ), result.title || file.name ) );
						onClose();
					}
				} )
				.catch( function ( e ) {
					setError( errorMessage( e ) );
				} )
				.then( function () {
					setFileBusy( false );
				} );
		}

		/** Templates / Sections / Presets switch (in the header and at the top of the sidebar). */
		function modeSwitch( extraClass ) {
			return el(
				'div',
				{ className: 'envo-blocks-switch' + extraClass, role: 'group', 'aria-label': __( 'What to import', 'envo-blocks' ) },
				[
					[ 'templates', __( 'Templates', 'envo-blocks' ) ],
					[ 'blocks', __( 'Sections', 'envo-blocks' ) ],
					[ 'presets', __( 'Presets', 'envo-blocks' ) ],
					[ 'layouts', __( 'Layouts', 'envo-blocks' ) ],
				].map( function ( item ) {
					const active = item[ 0 ] === mode;
					return el(
						'button',
						{
							key: item[ 0 ],
							type: 'button',
							className: 'envo-blocks-switch__item' + ( active ? ' is-active' : '' ),
							'aria-pressed': active ? 'true' : 'false',
							onClick: function () {
								if ( item[ 0 ] !== mode ) {
									setChecked( [] ); // Categories of Sections and Presets differ.
								}
								lastMode = item[ 0 ];
								setMode( item[ 0 ] );
								setView( null );
								setError( '' );
							},
						},
						item[ 1 ]
					);
				} )
			);
		}

		let body;
		if ( isLibrary ) {
			body = null === sections
				? el( 'div', { className: 'envo-blocks-state' }, el( Spinner, null ) )
				: el(
						'div',
						{ className: 'envo-blocks-blocks' },
						el( SectionSidebar, {
							categories: sectionCategories.names,
							counts: sectionCategories.counts,
							checked: checked,
							onToggle: function ( name ) {
								setChecked( checked.indexOf( name ) !== -1 ? checked.filter( function ( item ) {
									return item !== name;
								} ) : checked.concat( [ name ] ) );
							},
							onClear: function () {
								setChecked( [] );
							},
							help: 'layouts' === mode
								? __( 'Designs of the product page, the shop, the cart, the side cart, the checkout, the order received page and My account. "Use" makes a store layout of the design and opens it; it is used on the store once you publish it.', 'envo-blocks' )
								: 'presets' === mode
								? __( 'Ready-made settings of the blocks of this plugin, as in the block demos. They are inserted after the selected block, or at the end of the page.', 'envo-blocks' )
								: __( 'Sections are inserted after the selected block, or at the end of the page. The rest of the page stays as it is.', 'envo-blocks' ),
							top: PAGE ? null : modeSwitch( ' envo-blocks-switch--sidebar' ),
							edition: { edition: edition, counts: editionCounts( sections ), onChange: setEdition },
						} ),
						visibleSections.length
							? el(
									'div',
									{ className: 'envo-blocks-sections' },
									visibleSections.map( function ( section ) {
										return el( SectionCard, {
											key: section.id,
											section: section,
											layout: 'layouts' === mode,
											busy: insertingId === section.id,
											disabled: !! insertingId,
											onInsert: insertSection,
										} );
									} )
							  )
							: el( 'p', { className: 'envo-blocks-state', role: 'status' }, 'layouts' === mode ? __( 'No layouts found.', 'envo-blocks' ) : 'presets' === mode ? __( 'No presets found.', 'envo-blocks' ) : __( 'No sections found.', 'envo-blocks' ) )
				  );
		} else if ( templates === null ) {
			body = el( 'div', { className: 'envo-blocks-state' }, el( Spinner, null ) );
		} else if ( current ) {
			body = el( Detail, {
				template: current,
				pageKey: view.page,
				onPage: function ( key ) {
					setView( { id: current.id, page: key } );
				},
				onBack: function () {
					setView( null );
					setError( '' );
				},
				onImport: importTemplate,
				// The preview makes the blocks in the browser, which needs the block editor: not on the Templates Import page
				// (there the live demo link shows the page).
				onPreview: PAGE ? null : previewPage,
				previewBusy: previewBusy,
				busy: busyId === current.id,
				busySlug: busySlug,
				onDependency: installDependency,
				needsReload: needsReload,
				picker: PAGE
					? null
					: ( function () {
							const tpl = editorTemplates( current );
							return el(
								Fragment,
								null,
								el( PartsToggle, { template: current, value: withParts, onChange: setWithParts } ),
								el( PageTemplatePicker, { template: current, postType: tpl.postType, value: tpl.chosen, onChange: setPageTemplate, withParts: withParts } )
							);
					  } )(),
			} );
		} else {
			body = el(
				'div',
				{ className: 'envo-blocks-blocks' },
				el( SectionSidebar, {
					categories: templateCategories.names,
					counts: templateCategories.counts,
					checked: tplChecked,
					onToggle: function ( name ) {
						setTplChecked( tplChecked.indexOf( name ) !== -1 ? tplChecked.filter( function ( item ) {
							return item !== name;
						} ) : tplChecked.concat( [ name ] ) );
					},
					onClear: function () {
						setTplChecked( [] );
					},
					help: PAGE
						? __( 'Choose a template to see its pages and what it needs. Each page is imported into a new draft post or page.', 'envo-blocks' )
						: __( 'Choose a template to see its pages, what it needs and to import a page into the page you are editing.', 'envo-blocks' ),
					top: PAGE ? null : modeSwitch( ' envo-blocks-switch--sidebar' ),
					edition: { edition: edition, counts: editionCounts( templates ), onChange: setEdition },
				} ),
				visible.length
					? el(
							'div',
							{ className: 'envo-blocks-grid' },
							visible.map( function ( template ) {
								return el( Card, {
									key: template.id,
									template: template,
									onOpen: function ( item, pageKey ) {
										setError( '' );
										setView( { id: item.id, page: pageKey || ( item.pages && item.pages[ 0 ] ? item.pages[ 0 ].key : '' ) } );
									},
								} );
							} )
					  )
					: el( 'p', { className: 'envo-blocks-state', role: 'status' }, __( 'No templates found.', 'envo-blocks' ) )
			);
		}

		return el(
			'div',
			PAGE
				? { className: 'envo-blocks-modal is-page' + ( current ? ' is-detail' : ' is-blocks' ) }
				: { className: 'envo-blocks-modal' + ( current && ! isLibrary ? ' is-detail' : ' is-blocks' ), role: 'dialog', 'aria-modal': 'true', 'aria-labelledby': 'envo-blocks-modal-title' },
			el(
				'header',
				{ className: 'envo-blocks-modal__header' },
				PAGE ? el( 'h2', { className: 'envo-blocks-page-title' }, 'layouts' === mode ? __( 'Layout designs', 'envo-blocks' ) : __( 'Templates', 'envo-blocks' ) ) : el(
					'div',
					{ className: 'envo-blocks-brand' },
					config.logo ? el( 'img', { src: config.logo, alt: '', width: 36, height: 36 } ) : null,
					el( 'h2', { className: 'envo-blocks-brand__name', id: 'envo-blocks-modal-title' }, pluginName )
				),
				PAGE ? null : modeSwitch( '' ),
				el(
					'div',
					{ className: 'envo-blocks-tools' },
					current && ! isLibrary
						? null
						: el( 'input', {
								ref: searchRef,
								type: 'search',
								className: 'envo-blocks-search',
								value: query,
								placeholder: 'layouts' === mode ? __( 'Search layouts…', 'envo-blocks' ) : 'presets' === mode ? __( 'Search presets…', 'envo-blocks' ) : 'blocks' === mode ? __( 'Search sections…', 'envo-blocks' ) : __( 'Search templates…', 'envo-blocks' ),
								'aria-label': 'layouts' === mode ? __( 'Search layouts', 'envo-blocks' ) : 'presets' === mode ? __( 'Search presets', 'envo-blocks' ) : 'blocks' === mode ? __( 'Search sections', 'envo-blocks' ) : __( 'Search templates', 'envo-blocks' ),
								maxLength: 100,
								onChange: function ( event ) {
									setQuery( event.target.value );
								},
						  } ),
					el( 'input', {
						ref: fileRef,
						type: 'file',
						accept: '.json,.zip,application/json,application/zip',
						className: 'envo-blocks-file',
						tabIndex: -1,
						'aria-hidden': 'true',
						onChange: function ( event ) {
							const file = event.target.files && event.target.files[ 0 ];
							event.target.value = '';
							importFromFile( file );
						},
					} ),
					'layouts' === mode ? null : el(
						'button',
						{
							type: 'button',
							className: 'envo-blocks-upload' + ( fileBusy ? ' is-busy' : '' ),
							disabled: fileBusy,
							title: __( 'Import a JSON or ZIP file made by Export as JSON / Export as ZIP, or sent by our support.', 'envo-blocks' ),
							onClick: function () {
								if ( fileRef.current ) {
									fileRef.current.click();
								}
							},
						},
						fileBusy ? el( Spinner, null ) : uploadIcon,
						el( 'span', null, fileBusy ? __( 'Importing…', 'envo-blocks' ) : __( 'Import from file', 'envo-blocks' ) )
					),
					PAGE
						? null
						: el(
								'button',
								{ type: 'button', className: 'envo-blocks-close', onClick: onClose, 'aria-label': __( 'Close', 'envo-blocks' ) },
								closeIcon
						  )
				)
			),
			el(
				'div',
				{ className: 'envo-blocks-modal__body' },
				el(
					'div',
					{ className: 'envo-blocks-container', role: 'region', 'aria-label': 'layouts' === mode ? __( 'Layouts', 'envo-blocks' ) : 'presets' === mode ? __( 'Presets', 'envo-blocks' ) : 'blocks' === mode ? __( 'Sections', 'envo-blocks' ) : current ? current.name : __( 'Templates', 'envo-blocks' ) },
					errorBox( error ),
					PAGE && ! isLibrary && ! current && ourTheme
						? el( ThemeBanner, { item: ourTheme, busySlug: busySlug, onAction: installDependency } )
						: null,
					body
				)
			),
			creating
				? el( CreateDialog, {
						template: creating.template,
						page: creating.page,
						file: creating.file,
						name: creating.name,
						onCancel: function () {
							setCreating( null );
						},
				  } )
				: null
		);
	}

	/** ID of the post open in the editor; 0 on the admin page. */
	function currentPostId() {
		return PAGE ? 0 : select( 'core/editor' ).getCurrentPostId();
	}

	/**
	 * Admin page: asks for the post type and the title, creates a draft and opens it in the editor, which
	 * then imports the chosen page of the template (the same way as after "Reload the editor and import").
	 */
	function CreateDialog( { template, page, file, name, onCancel } ) {
		const types = Array.isArray( config.postTypes ) ? config.postTypes : [];
		const label = file ? name : page.name || template.name || '';
		const [ type, setType ] = useState( types.length ? types[ 0 ].slug : '' );
		const [ title, setTitle ] = useState( label );
		const [ parts, setParts ] = useState( true );
		const [ pageTpl, setPageTpl ] = useState( defaultPageTemplate( template, types.length ? types[ 0 ].slug : '', '' ) );
		const [ busy, setBusy ] = useState( false );
		const [ error, setError ] = useState( '' );
		const titleRef = useRef( null );

		useEffect( function () {
			if ( titleRef.current ) {
				titleRef.current.focus();
				titleRef.current.select();
			}
			function onKey( event ) {
				if ( event.key === 'Escape' ) {
					onCancel();
				}
			}
			document.addEventListener( 'keydown', onKey );
			return function () {
				document.removeEventListener( 'keydown', onKey );
			};
		}, [] );

		function submit( event ) {
			event.preventDefault();
			const chosen = types.find( function ( item ) {
				return item.slug === type;
			} );
			if ( ! chosen || busy ) {
				return;
			}
			setBusy( true );
			setError( '' );

			apiFetch( {
				path: '/wp/v2/' + chosen.restBase,
				method: 'POST',
				data: Object.assign( { title: title.trim(), status: 'draft' }, pageTpl ? { template: pageTpl } : {} ),
			} )
				.then( function ( post ) {
					if ( ! post || ! post.id ) {
						throw new Error( __( 'The draft could not be created.', 'envo-blocks' ) );
					}
					try {
						window.sessionStorage.setItem(
							REOPEN_KEY,
							JSON.stringify( file ? { file: file, save: true } : { id: template.id, page: page.key || '', autoImport: true, save: true, parts: parts } )
						);
					} catch ( e ) {}
					window.location.href = config.editUrl + encodeURIComponent( post.id );
				} )
				.catch( function ( e ) {
					setError( errorMessage( e ) );
					setBusy( false );
				} );
		}

		return el(
			'div',
			{
				className: 'envo-blocks-create',
				onClick: function ( event ) {
					if ( event.target === event.currentTarget && ! busy ) {
						onCancel();
					}
				},
			},
			el(
				'form',
				{ className: 'envo-blocks-create__box', role: 'dialog', 'aria-modal': 'true', 'aria-labelledby': 'envo-blocks-create-title', onSubmit: submit },
				el( 'h2', { id: 'envo-blocks-create-title' }, label ? sprintf( /* translators: %s: page name. */ __( 'Import "%s"', 'envo-blocks' ), label ) : __( 'Import the file', 'envo-blocks' ) ),
				el( 'p', { className: 'envo-blocks-create__help' }, __( 'A new draft is created with this content and opened in the editor.', 'envo-blocks' ) ),
				types.length > 1
					? el(
							'fieldset',
							{ className: 'envo-blocks-create__types' },
							el( 'legend', null, __( 'Create as', 'envo-blocks' ) ),
							types.map( function ( item ) {
								return el(
									'label',
									{ key: item.slug, className: 'envo-blocks-create__type' + ( type === item.slug ? ' is-active' : '' ) },
									el( 'input', {
										type: 'radio',
										name: 'envo-blocks-type',
										value: item.slug,
										checked: type === item.slug,
										onChange: function () {
											setType( item.slug );
											setPageTpl( defaultPageTemplate( template, item.slug, '', parts ) );
										},
									} ),
									el( 'span', null, item.label )
								);
							} )
					  )
					: null,
				el(
					'label',
					{ className: 'envo-blocks-create__label', htmlFor: 'envo-blocks-create-name' },
					__( 'Title', 'envo-blocks' )
				),
				el( 'input', {
					ref: titleRef,
					id: 'envo-blocks-create-name',
					className: 'envo-blocks-create__input',
					type: 'text',
					value: title,
					maxLength: 200,
					required: true,
					onChange: function ( event ) {
						setTitle( event.target.value );
					},
				} ),
				file ? null : el( PartsToggle, {
					template: template,
					value: parts,
					onChange: function ( next ) {
						setParts( next );
						setPageTpl( defaultPageTemplate( template, type, '', next ) );
					},
				} ),
				el( PageTemplatePicker, { template: template, postType: type, value: pageTpl, onChange: setPageTpl, withParts: parts } ),
				errorBox( error ),
				el(
					'div',
					{ className: 'envo-blocks-create__buttons' },
					el( 'button', { type: 'button', className: 'envo-blocks-btn envo-blocks-btn--secondary', onClick: onCancel, disabled: busy }, __( 'Cancel', 'envo-blocks' ) ),
					el(
						'button',
						{ type: 'submit', className: 'envo-blocks-btn envo-blocks-btn--primary' + ( busy ? ' is-busy' : '' ), disabled: busy || ! title.trim() || ! types.length },
						busy ? __( 'Creating…', 'envo-blocks' ) : __( 'Create and import', 'envo-blocks' )
					)
				)
			)
		);
	}

	/* ------------------------------------------------------------------ */
	/* Header button                                                      */
	/* ------------------------------------------------------------------ */

	/**
	 * The editor has no official slot in the header, so a container is
	 * placed into the header toolbar (left side, next to List View) and kept there if the header re-renders.
	 */
	function useHeaderSlot() {
		const [ slot, setSlot ] = useState( null );

		useEffect( function () {
			const wrapper = document.createElement( 'div' );
			wrapper.className = 'envo-blocks-header-slot';
			let frame = 0;

			function place() {
				frame = 0;
				const toolbar = document.querySelector( '.editor-header__toolbar, .edit-post-header__toolbar' );
				if ( ! toolbar ) {
					return;
				}
				// Place the button right after the document tools (inserter, undo, redo, list view).
				const tools = toolbar.querySelector( '.editor-document-tools, .edit-post-header-toolbar' );
				const parent = tools && tools.parentNode ? tools.parentNode : toolbar;
				const before = tools && tools.parentNode ? tools.nextSibling : null;
				if ( wrapper.parentNode !== parent || wrapper.previousSibling !== ( tools || wrapper.previousSibling ) ) {
					parent.insertBefore( wrapper, before );
				}
			}

			function schedule() {
				if ( ! frame ) {
					frame = window.requestAnimationFrame( place );
				}
			}

			place();
			setSlot( wrapper );

			const observer = new window.MutationObserver( schedule );
			observer.observe( document.body, { childList: true, subtree: true } );

			return function () {
				observer.disconnect();
				if ( frame ) {
					window.cancelAnimationFrame( frame );
				}
				wrapper.remove();
			};
		}, [] );

		return slot;
	}

	/** Template and page to reopen after "Reload the editor and import" (stored before the reload). */
	function takeReopen() {
		try {
			const raw = window.sessionStorage.getItem( REOPEN_KEY );
			window.sessionStorage.removeItem( REOPEN_KEY );
			const value = raw ? JSON.parse( raw ) : null;
			if ( value && typeof value.file === 'string' ) {
				return { file: value.file, save: !! value.save };
			}
			return value && typeof value.id === 'string' ? { id: value.id, page: String( value.page || '' ), autoImport: !! value.autoImport, save: !! value.save, parts: false !== value.parts } : null;
		} catch ( e ) {
			return null;
		}
	}

	// Read once when the script starts: the editor may mount the plugin more than once.
	const reopen = PAGE ? null : takeReopen();
	let autoImported = false;

	/** A file uploaded on the Envo Blocks > Templates Import page: put it into the new draft and save it. */
	function importKeptFile( token, save ) {
		let tries = 0;
		( function waitForEditor() {
			const editor = select( 'core/editor' );
			const ready = typeof editor.__unstableIsEditorReady === 'function' ? editor.__unstableIsEditorReady() : true;
			if ( ! ready && tries++ < 40 ) {
				setTimeout( waitForEditor, 250 );
				return;
			}
			setTimeout( function () {
				apiFetch( { path: '/envo-blocks/v1/import-file/' + encodeURIComponent( token ) } )
					.then( function ( result ) {
						const blocks = toBlocks( result && result.blocks );
						if ( ! blocks.length ) {
							throw new Error( __( 'The file does not contain any usable blocks.', 'envo-blocks' ) );
						}
						if ( applyBlocks( blocks, result.title ) ) {
							if ( save ) {
								dispatch( 'core/editor' ).savePost();
							}
							notice( 'success', sprintf( /* translators: %s: page name. */ __( '"%s" imported.', 'envo-blocks' ), result.title || '' ) );
						}
					} )
					.catch( function ( e ) {
						notice( 'error', errorMessage( e ) );
					} );
			}, 300 );
		} )();
	}

	if ( reopen && reopen.file ) {
		importKeptFile( reopen.file, reopen.save );
	}

	function EnvoBlocksPlugin() {
		const [ initial ] = useState( reopen && ! reopen.file ? reopen : null );
		const [ open, setOpen ] = useState( !! initial );
		const slot = useHeaderSlot();
		const lastFocus = useRef( null );

		const openPopup = useCallback( function () {
			lastFocus.current = document.activeElement;
			setOpen( true );
		}, [] );

		const closePopup = useCallback( function () {
			setOpen( false );
			if ( lastFocus.current && typeof lastFocus.current.focus === 'function' ) {
				lastFocus.current.focus();
			}
		}, [] );

		return el(
			Fragment,
			null,
			PluginMoreMenuItem ? el( PluginMoreMenuItem, { icon: downloadIcon, onClick: exportAsJson }, __( 'Export as JSON', 'envo-blocks' ) ) : null,
			PluginMoreMenuItem ? el( PluginMoreMenuItem, { icon: downloadIcon, onClick: exportAsZip }, __( 'Export as ZIP (with images)', 'envo-blocks' ) ) : null,
			slot
				? createPortal(
						el( Button, { variant: 'primary', className: 'envo-blocks-header-button', onClick: openPopup }, brandIcon, __( 'Templates Import', 'envo-blocks' ) ),
						slot
				  )
				: null,
			open ? createPortal( el( Popup, { onClose: closePopup, initial: initial } ), document.body ) : null
		);
	}

	if ( PAGE ) {
		const root = document.getElementById( 'envo-blocks-templates-app' );
		if ( root ) {
			const app = el( Popup, { onClose: function () {} } );
			if ( typeof wp.element.createRoot === 'function' ) {
				wp.element.createRoot( root ).render( app );
			} else {
				wp.element.render( app, root );
			}
		}
	} else if ( registerPlugin ) {
		registerPlugin( 'envo-blocks', { render: EnvoBlocksPlugin } );
	}
} )( window.wp );
