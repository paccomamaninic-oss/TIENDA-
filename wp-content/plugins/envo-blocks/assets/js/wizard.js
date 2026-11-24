/* Store in 5 minutes – wizard screen. Steps: theme → template → colours → done. Data: window.envoBlocksWizard. */
( function () {
	const D = window.envoBlocksWizard;
	const { __, sprintf } = wp.i18n;
	const app = document.getElementById( 'ebw-app' );
	const STEPS = [ 'theme', 'template', 'colors', 'import', 'store', 'done' ];

	const s = {
		step: 'theme',
		showOthers: false,
		other: '',
		busy: '',
		progress: null,
		error: '',
		template: D.state.template,
		colors: Object.assign( {}, D.state.colors ),
		name: D.state.name,
		summary: null,
		device: 'desktop',
		page: 'home',
		logo: D.state.logo || null,
		shop: storeDefaults(),
		search: '',
		cats: [], // Checked categories of the template sidebar; none = all.
		edition: 'all',
		// What to import: page keys, header / footer = site | pages | none.
		pick: { pages: null, header: ( D.state.parts || {} ).header || 'site', footer: ( D.state.parts || {} ).footer || 'site' },
		frame: '',
		notice: '',
	};
	if ( D.state.built ) {
		s.step = 'done';
	} else if ( D.state.template && D.state.home ) {
		s.step = 'colors';
	}
	if ( /[?&]switched=1/.test( location.search ) ) {
		s.notice = __( 'Switched to Envo Blocks Store. Your pages and colours stayed as they were.', 'envo-blocks' );
	}

	/**
	 * <img> of a preview of the free plugin's manifest (same as the Templates Import): the kept copy on this site
	 * or the assets server, the blurred placeholder under it; fallback = a plain address.
	 */
	function previewImg( set, fallback, sizes ) {
		if ( ! set ) {
			return fallback ? `<img src="${ esc( fallback ) }" alt="" loading="lazy">` : '';
		}
		const remote = set.remote ? ` data-keep="${ esc( set.key ) }"` : '';
		const lqip = set.lqip ? ` style="background:url(${ esc( set.lqip ) }) center / cover no-repeat"` : '';
		return `<img src="${ esc( set.src ) }" srcset="${ esc( set.srcset ) }" sizes="${ esc( sizes ) }" alt="" loading="lazy"${ remote }${ lqip }>`;
	}

	/**
	 * Previews shown from the assets server are kept on this site by the free plugin (POST envo-blocks/v1/preview-cache,
	 * like the Templates Import), so the next time they load from here.
	 */
	const keepSent = new Set();
	function keepPreviews() {
		const imgs = [ ...app.querySelectorAll( 'img[data-keep]' ) ].filter( ( i ) => ! keepSent.has( i.dataset.keep ) );
		if ( ! imgs.length ) {
			return;
		}
		Promise.all( imgs.map( ( i ) => ( i.complete ? Promise.resolve() : new Promise( ( r ) => {
			i.addEventListener( 'load', r, { once: true } );
			i.addEventListener( 'error', r, { once: true } );
		} ) ) ) ).then( () => {
			const keys = imgs.filter( ( i ) => i.naturalWidth > 0 ).map( ( i ) => i.dataset.keep ).filter( ( k ) => ! keepSent.has( k ) );
			if ( ! keys.length ) {
				return;
			}
			keys.forEach( ( k ) => keepSent.add( k ) );
			fetch( D.restRoot + 'envo-blocks/v1/preview-cache', {
				method: 'POST',
				headers: { 'X-WP-Nonce': D.nonce, 'Content-Type': 'application/json' },
				body: JSON.stringify( { keys } ),
				credentials: 'same-origin',
			} ).catch( () => {} );
		} );
	}

	/** Starting values of the "Your store" step: from the site language, delivery prices by currency. */
	function storeDefaults() {
		const st = D.store || {};
		const sg = st.suggest || {};
		return {
			country: sg.country || '',
			currency: sg.currency || '',
			currencyTouched: false,
			bacs: { on: true, name: ( st.account || {} ).name || '', iban: ( st.account || {} ).iban || '', bic: ( st.account || {} ).bic || '' },
			cod: false,
			shipping: Object.assign( { on: true }, shipDefaults( sg.currency ) ),
			samples: ( st.products || 0 ) < 3,
			address: Object.assign( { address: '', address2: '', city: '', postcode: '', state: '' }, st.address || {} ),
			addressOpen: false,
			pages: pageDefaults( st.storePages || {} ),
			message: '',
		};
	}

	/** The current store pages; a missing one is created (terms / privacy as a starting draft). */
	function pageDefaults( current ) {
		const out = {};
		Object.keys( storePages() ).forEach( ( k ) => ( out[ k ] = current[ k ] ? String( current[ k ] ) : 'new' ) );
		return out;
	}

	/** Store pages of the step: key => [label, required content of the page ('' = any page)]. */
	function storePages() {
		return {
			shop: [ __( 'Shop', 'envo-blocks' ), '' ],
			cart: [ __( 'Cart', 'envo-blocks' ), 'cart' ],
			checkout: [ __( 'Checkout', 'envo-blocks' ), 'checkout' ],
			myaccount: [ __( 'My account', 'envo-blocks' ), 'myaccount' ],
			terms: [ __( 'Terms and conditions', 'envo-blocks' ), '' ],
			privacy: [ __( 'Privacy policy', 'envo-blocks' ), '' ],
		};
	}

	/** One row of the "Store pages" card: the page select and what happens with the choice. */
	function pageRow( key ) {
		const st = D.store;
		const [ label, needs ] = storePages()[ key ];
		const legal = 'terms' === key || 'privacy' === key;
		const value = s.shop.pages[ key ];
		const page = ( st.pages || [] ).find( ( p ) => String( p.id ) === value );
		const opts = ( legal ? `<option value="" ${ '' === value ? 'selected' : '' }>${ esc( __( '— None —', 'envo-blocks' ) ) }</option>` : '' )
			+ `<option value="new" ${ 'new' === value ? 'selected' : '' }>${ esc( legal ? __( '+ Create a starting draft', 'envo-blocks' ) : __( '+ Create a new page', 'envo-blocks' ) ) }</option>`
			+ ( st.pages || [] ).map( ( p ) => `<option value="${ p.id }" ${ String( p.id ) === value ? 'selected' : '' }>${ esc( p.title + ( 'publish' !== p.status ? ' (' + __( 'draft', 'envo-blocks' ) + ')' : '' ) + ( needs && p.has.includes( needs ) ? ' ✓' : '' ) ) }</option>` ).join( '' );
		let hint = '';
		if ( 'new' === value ) {
			hint = legal ? __( 'A page with the main points and your store details, marked to be checked before you start selling.', 'envo-blocks' ) : __( 'WooCommerce makes the page with the right content.', 'envo-blocks' );
		} else if ( page ) {
			hint = needs && ! page.has.includes( needs ) ? `<span class="ebw-warn">${ esc( sprintf( __( 'This page has no %s block – choose a page marked ✓ or create a new one.', 'envo-blocks' ), label ) ) }</span>` : '';
			hint += 'publish' !== page.status ? ' ' + esc( __( 'Draft – published when you save.', 'envo-blocks' ) ) : ` <a href="${ esc( page.url ) }" target="_blank" rel="noopener">${ esc( __( 'View ↗', 'envo-blocks' ) ) }</a>`;
			return `<div class="ebw-pagerow"><label for="ebw-pg-${ key }">${ esc( label ) }</label><select id="ebw-pg-${ key }" data-shop="pages.${ key }">${ opts }</select><p class="ebw-small">${ hint }</p></div>`;
		} else {
			hint = __( 'No page – WooCommerce leaves it out.', 'envo-blocks' );
		}
		return `<div class="ebw-pagerow"><label for="ebw-pg-${ key }">${ esc( label ) }</label><select id="ebw-pg-${ key }" data-shop="pages.${ key }">${ opts }</select><p class="ebw-small">${ esc( hint ) }</p></div>`;
	}

	const statesOf = {};

	/** States / regions of a country for the address (loads them once; renders again when they arrive). */
	function states( country ) {
		if ( ! ( country in statesOf ) ) {
			statesOf[ country ] = null;
			if ( D.store && country === ( D.store.suggest || {} ).country && D.store.states ) {
				statesOf[ country ] = D.store.states;
			} else {
				api( '/states' + ( D.rest.includes( '?' ) ? '&' : '?' ) + 'country=' + encodeURIComponent( country ), 'GET' ).then( ( r ) => {
					statesOf[ country ] = r || {};
					if ( 'store' === s.step ) render();
				} ).catch( () => ( statesOf[ country ] = {} ) );
			}
		}
		return statesOf[ country ] || {};
	}

	/** The address in one line for the folded section. */
	function addressLine() {
		const a = s.shop.address;
		return [ a.address, a.address2, [ a.postcode, a.city ].filter( Boolean ).join( ' ' ) ].map( ( x ) => ( x || '' ).trim() ).filter( Boolean ).join( ', ' );
	}

	function shipDefaults( currency ) {
		const map = { CZK: [ 89, 1500 ], EUR: [ 4.9, 60 ], USD: [ 6.9, 75 ], GBP: [ 4.5, 50 ], PLN: [ 15, 250 ], HUF: [ 1490, 25000 ], CHF: [ 7.9, 80 ] };
		const v = map[ currency ] || [ '', '' ];
		return { cost: v[ 0 ], free: v[ 1 ] };
	}

	const esc = ( v ) => String( v == null ? '' : v ).replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ] ) );
	const ours = () => D.theme.current === D.theme.ours.slug;
	const tpl = () => D.templates.find( ( t ) => t.slug === s.template );
	// Our theme can be used: installed, or installable from WordPress.org by this user (Envo Blocks installer).
	const oursReady = () => D.theme.ours.installed || ( D.theme.ours.wporg && D.theme.ours.canInstall );

	async function api( path, method = 'GET', body = null ) {
		// Paths of the wizard (/theme …) or a full address of another REST route (the installer of Envo Blocks).
		const res = await fetch( /^https?:/.test( path ) ? path : D.rest + path, {
			method,
			headers: { 'X-WP-Nonce': D.nonce, 'Content-Type': 'application/json' },
			body: body ? JSON.stringify( body ) : null,
			credentials: 'same-origin',
		} );
		const json = await res.json();
		if ( ! res.ok ) {
			throw new Error( json && json.message ? json.message : res.statusText );
		}
		return json;
	}

	function go( step ) {
		s.step = step;
		s.error = '';
		render();
		window.scrollTo( 0, 0 );
	}

	/* ---------------------------------------------------------------------------------------------------- */

	function header() {
		const labels = {
			theme: __( 'Theme', 'envo-blocks' ),
			template: __( 'Template', 'envo-blocks' ),
			colors: __( 'Your colours', 'envo-blocks' ),
			import: __( 'Import', 'envo-blocks' ),
			store: __( 'Your store', 'envo-blocks' ),
			done: __( 'Done', 'envo-blocks' ),
		};
		const at = STEPS.indexOf( s.step );
		return `<header class="ebw-head">
			<div class="ebw-head__title"><span class="ebw-logo" aria-hidden="true">5′</span><div><h1>${ esc( __( 'Store in 5 minutes', 'envo-blocks' ) ) }</h1>
			<p>${ esc( __( 'Pick a template and your brand colour – we set up the pages and colours of the whole store.', 'envo-blocks' ) ) }</p></div></div>
			<ol class="ebw-steps">${ STEPS.map( ( k, i ) => `<li class="${ i < at ? 'is-done' : '' } ${ i === at ? 'is-current' : '' }"><span>${ i + 1 }</span>${ esc( labels[ k ] ) }</li>` ).join( '' ) }</ol>
		</header>`;
	}

	function viewTheme() {
		const o = D.theme.ours;
		if ( ours() ) {
			return `<section class="ebw-card ebw-ok">
				<h2>${ esc( __( 'You are using Envo Blocks Store', 'envo-blocks' ) ) }</h2>
				<p>${ esc( __( 'The theme our templates are made for – the store will look exactly like the demo.', 'envo-blocks' ) ) }</p>
				<p><button class="button button-primary button-hero" data-go="template">${ esc( __( 'Continue', 'envo-blocks' ) ) }</button></p>
			</section>`;
		}
		const others = D.theme.others.map( ( t ) => `<label class="ebw-theme ${ ( s.other || D.theme.current ) === t.slug ? 'is-selected' : '' }">
			<input type="radio" name="ebw-other" value="${ esc( t.slug ) }" ${ ( s.other || D.theme.current ) === t.slug ? 'checked' : '' }>
			${ t.screenshot ? `<img src="${ esc( t.screenshot ) }" alt="">` : '<span class="ebw-theme__blank"></span>' }
			<span>${ esc( t.name ) }${ t.slug === D.theme.current ? ` <em>${ esc( __( '(current)', 'envo-blocks' ) ) }</em>` : '' }</span>
		</label>` ).join( '' );
		return `<section class="ebw-reco">
			<div class="ebw-reco__shot">${ o.screenshot ? `<img src="${ esc( o.screenshot ) }" alt="">` : '' }</div>
			<div class="ebw-reco__body">
				<span class="ebw-badge">${ esc( __( 'Strongly recommended', 'envo-blocks' ) ) }</span>
				<h2>${ esc( o.name ) }</h2>
				<p class="ebw-lead">${ esc( __( 'Our templates are designed with this theme. Choose it and your store looks exactly like the demo you pick – from the first click.', 'envo-blocks' ) ) }</p>
				<ul class="ebw-checks">
					<li>${ esc( __( 'Fonts, spacing and buttons match the template 1:1', 'envo-blocks' ) ) }</li>
					<li>${ esc( __( 'Shop, product, cart and checkout pages in the same style', 'envo-blocks' ) ) }</li>
					<li>${ esc( __( 'Your brand colour reaches every page, not only the home page', 'envo-blocks' ) ) }</li>
					<li>${ esc( __( 'Free, made by us – tested with every Envo Blocks release', 'envo-blocks' ) ) }</li>
				</ul>
				${ o.installed
					? `<button class="button button-primary button-hero" data-theme="${ esc( o.slug ) }">${ esc( sprintf( __( 'Use %s', 'envo-blocks' ), o.name ) ) }</button>`
					: oursReady()
					? `<button class="button button-primary button-hero" data-theme="${ esc( o.slug ) }">${ esc( sprintf( __( 'Install and use %s', 'envo-blocks' ), o.name ) ) }</button>
						<p class="ebw-small">${ esc( __( 'Free from WordPress.org – installed and activated for you.', 'envo-blocks' ) ) }</p>`
					: `<p class="ebw-warn">${ esc( o.wporg
						? __( 'The theme is not installed on this site yet. Ask an administrator to install it.', 'envo-blocks' )
						: __( 'The theme is not installed on this site yet.', 'envo-blocks' ) ) }</p>
						${ o.link ? `<p><a class="button button-large" href="${ esc( o.link ) }" target="_blank" rel="noopener noreferrer">${ esc( __( 'How to get the theme ↗', 'envo-blocks' ) ) }</a></p>` : '' }` }
				<p class="ebw-small">${ esc( sprintf( __( 'Your current theme: %s. You can switch back any time in Appearance › Themes.', 'envo-blocks' ), D.theme.currentName ) ) }</p>
			</div>
		</section>
		<div class="ebw-luck">
			${ s.showOthers
				? `<div class="ebw-card">
					<h3>${ esc( __( 'Try your luck with another theme', 'envo-blocks' ) ) }</h3>
					<p>${ esc( __( 'The template brings its own header and footer, so it will work. Fonts, spacing and the shop pages come from the theme, though, and may not match. Not happy with the result? You can switch to Envo Blocks Store later with one click.', 'envo-blocks' ) ) }</p>
					<div class="ebw-themes">${ others }</div>
					<p><button class="button button-large" data-other-go>${ esc( __( 'Continue with this theme', 'envo-blocks' ) ) }</button></p>
				</div>`
				: `<button class="button-link" data-show-others>${ esc( __( 'No thanks, I will try my luck with another theme', 'envo-blocks' ) ) }</button>` }
		</div>`;
	}

	/** Search of the template step: every word must be in the name, tagline, page names or slug. */
	function inEdition( t, edition ) {
		return 'all' === edition || ( 'pro' === edition ) === !! t.premium;
	}

	function matches( t ) {
		const words = s.search.toLowerCase().split( /\s+/ ).filter( Boolean );
		const hay = [ t.name, t.slug, t.tagline ].concat( t.pages, t.categories || [] ).join( ' ' ).toLowerCase();
		const inCat = ! s.cats.length || ( t.categories || [] ).some( ( c ) => s.cats.includes( c ) );
		return inCat && inEdition( t, s.edition ) && words.every( ( w ) => hay.includes( w ) );
	}

	function searchCount() {
		const n = D.templates.filter( matches ).length;
		return s.search || s.cats.length || 'all' !== s.edition ? sprintf( __( '%1$d of %2$d templates', 'envo-blocks' ), n, D.templates.length ) : sprintf( __( '%d templates', 'envo-blocks' ), D.templates.length );
	}

	function templateSidebar() {
		const ed = { all: D.templates.length, free: D.templates.filter( ( t ) => ! t.premium ).length, pro: D.templates.filter( ( t ) => t.premium ).length };
		const counts = {};
		D.templates.filter( ( t ) => inEdition( t, s.edition ) ).forEach( ( t ) => ( t.categories || [] ).forEach( ( c ) => ( counts[ c ] = ( counts[ c ] || 0 ) + 1 ) ) );
		const cats = [ ...new Set( D.templates.flatMap( ( t ) => t.categories || [] ) ) ].sort( ( a, b ) => a.localeCompare( b ) );
		return `<aside class="ebw-side">
			<div class="ebw-edition" role="group" aria-label="${ esc( __( 'Free or PRO', 'envo-blocks' ) ) }">
				${ [ [ 'all', __( 'All', 'envo-blocks' ) ], [ 'free', __( 'Free', 'envo-blocks' ) ], [ 'pro', __( 'PRO', 'envo-blocks' ) ] ].map( ( [ k, label ] ) => `<button type="button" class="ebw-edition__btn ${ s.edition === k ? 'is-active' : '' }" data-edition="${ k }" aria-pressed="${ s.edition === k }" ${ ed[ k ] ? '' : 'disabled' }>${ esc( label ) }<span>${ ed[ k ] }</span></button>` ).join( '' ) }
			</div>
			<div class="ebw-side__titlebar"><h3>${ esc( __( 'Show', 'envo-blocks' ) ) }</h3>${ s.cats.length ? `<button type="button" class="ebw-side__clear" data-cats-clear>${ esc( __( 'Show all', 'envo-blocks' ) ) }</button>` : '' }</div>
			<ul class="ebw-side__list">${ cats.map( ( c ) => `<li><label class="ebw-side__check"><input type="checkbox" data-cat="${ esc( c ) }" ${ s.cats.includes( c ) ? 'checked' : '' }><span>${ esc( c ) }</span><em>${ counts[ c ] || 0 }</em></label></li>` ).join( '' ) }</ul>
		</aside>`;
	}

	/** PRO template without an active license: a link to buy, activate or renew Envo Blocks PRO instead of "Use". */
	function lockedButton( t ) {
		const a = t.action || {};
		const label = { activate: __( 'Activate your PRO license', 'envo-blocks' ), renew: __( 'Renew your PRO license', 'envo-blocks' ) }[ a.type ] || __( 'Get Envo Blocks PRO', 'envo-blocks' );
		return a.url ? `<a class="button" href="${ esc( a.url ) }" target="_blank" rel="noopener">${ esc( label ) }</a>` : `<span class="ebw-locked">${ esc( __( 'Needs Envo Blocks PRO', 'envo-blocks' ) ) }</span>`;
	}

	function viewTemplate() {
		const cards = D.templates.map( ( t ) => `<article class="ebw-tpl ${ s.template === t.slug ? 'is-selected' : '' }" data-find="${ esc( [ t.name, t.slug, t.tagline ].concat( t.pages ).join( ' ' ).toLowerCase() ) }" ${ matches( t ) ? '' : 'hidden' }>
			<div class="ebw-tpl__shot">${ previewImg( t.image, t.preview, '(max-width: 960px) 100vw, 33vw' ) }${ t.premium ? '<span class="ebw-pro">PRO</span>' : '' }</div>
			<div class="ebw-tpl__body">
				<h3>${ esc( t.name ) }</h3>
				<p>${ esc( t.tagline ) }</p>
				<p class="ebw-swatches">${ [ t.brand, t.accent ].filter( Boolean ).map( ( c ) => `<span style="background:${ esc( c ) }"></span>` ).join( '' ) }
					<small>${ esc( sprintf( __( '%d pages: %s', 'envo-blocks' ), t.pages.length, t.pages.join( ', ' ) ) ) }</small></p>
				<p>${ t.locked ? lockedButton( t ) : `<button class="button button-primary" data-template="${ esc( t.slug ) }">${ esc( __( 'Use this template', 'envo-blocks' ) ) }</button>` }
				<a class="button-link" href="${ esc( t.demo ) }" target="_blank" rel="noopener">${ esc( __( 'Live demo ↗', 'envo-blocks' ) ) }</a></p>
			</div>
		</article>` ).join( '' );
		return `<p class="ebw-intro">${ esc( __( 'Pick the template closest to your store. Your products are put into it, and you change the colours in the next step.', 'envo-blocks' ) ) }</p>
			<div class="ebw-search">
				<input type="search" data-search value="${ esc( s.search ) }" placeholder="${ esc( __( 'Search templates – fashion, electronics, dark, sport…', 'envo-blocks' ) ) }" aria-label="${ esc( __( 'Search templates', 'envo-blocks' ) ) }">
				<span class="ebw-search__count" aria-live="polite">${ esc( searchCount() ) }</span>
			</div>
			<div class="ebw-tpl-layout">
				${ templateSidebar() }
				<div>
					<div class="ebw-tpls">${ cards }</div>
					<p class="ebw-search__none" ${ D.templates.some( matches ) ? 'hidden' : '' }>${ esc( __( 'No template matches. Try another word or show all.', 'envo-blocks' ) ) }</p>
				</div>
			</div>
			<p><button class="button" data-go="theme">${ esc( __( '← Back', 'envo-blocks' ) ) }</button></p>`;
	}

	function colorField( key, label ) {
		const t = tpl();
		const value = s.colors[ key ] || t[ key ];
		const presets = [ t[ key ] ].concat( D.presets.filter( ( c ) => c !== t[ key ] ) );
		const sum = s.summary && s.summary[ key ];
		return `<div class="ebw-field">
			<label>${ esc( label ) }</label>
			<div class="ebw-color"><input type="color" value="${ esc( value ) }" data-color="${ key }"><input type="text" value="${ esc( value ) }" maxlength="7" data-hex="${ key }" spellcheck="false"></div>
			<div class="ebw-presets">${ presets.map( ( c, i ) => `<button type="button" class="${ c === value ? 'is-on' : '' }" style="background:${ esc( c ) }" data-preset="${ key }" data-value="${ esc( c ) }" title="${ esc( i ? c : __( 'Template colour', 'envo-blocks' ) + ' ' + c ) }"></button>` ).join( '' ) }</div>
			${ sum && sum.text ? `<p class="ebw-contrast"><span style="background:${ esc( value ) };color:${ esc( sum.text ) }">Aa</span> ${ esc( sprintf( __( 'Text on it: %1$s, contrast %2$s:1', 'envo-blocks' ), sum.text, sum.contrast ) ) } ${ sum.contrast >= 4.5 ? '✓' : '' }</p>` : '' }
		</div>`;
	}

	function viewColors() {
		const t = tpl();
		if ( ! t ) {
			return viewTemplate();
		}
		const banner = ours() ? '' : `<div class="ebw-banner">
			<span>${ esc( sprintf( __( 'Preview with %s. The template was designed for Envo Blocks Store.', 'envo-blocks' ), D.theme.currentName ) ) }</span>
			${ oursReady() ? `<button class="button button-small" data-theme="${ esc( D.theme.ours.slug ) }" data-stay>${ esc( __( 'See it with Envo Blocks Store', 'envo-blocks' ) ) }</button>` : '' }
		</div>`;
		const pos = panelPos();
		// The preview fills the whole window (real desktop width); the options float above it in a panel that is
		// moved by its title bar and can be folded to the bar.
		return `<div class="ebw-full">
			<div class="ebw-full__frame is-${ s.device }"><iframe title="${ esc( __( 'Preview', 'envo-blocks' ) ) }" src="${ esc( s.frame ) }"></iframe></div>
			<aside class="ebw-panel ${ s.collapsed ? 'is-collapsed' : '' }" style="left:${ pos.x }px;top:${ pos.y }px;max-height:${ window.innerHeight - pos.y - 16 }px">
				<div class="ebw-panel__bar" data-drag title="${ esc( __( 'Drag to move', 'envo-blocks' ) ) }">
					<span class="ebw-panel__grip" aria-hidden="true">⠿</span>
					<strong>${ esc( t.name ) }</strong>
					<span class="ebw-panel__step">${ esc( sprintf( __( 'Step %1$d of %2$d', 'envo-blocks' ), STEPS.indexOf( 'colors' ) + 1, STEPS.length ) ) }</span>
					<button type="button" class="ebw-panel__fold" data-collapse aria-expanded="${ s.collapsed ? 'false' : 'true' }" title="${ esc( s.collapsed ? __( 'Show options', 'envo-blocks' ) : __( 'Fold the options', 'envo-blocks' ) ) }">${ s.collapsed ? '▾' : '▴' }</button>
				</div>
				<div class="ebw-panel__body">
					${ s.error ? `<div class="notice notice-error inline"><p>${ esc( s.error ) }</p></div>` : '' }
					${ banner }
					<div class="ebw-field">
						<div class="ebw-field__head"><label for="ebw-page">${ esc( __( 'Preview page', 'envo-blocks' ) ) }</label>
							<div class="ebw-devices">
								<button class="${ s.device === 'desktop' ? 'is-on' : '' }" data-device="desktop">${ esc( __( 'Desktop', 'envo-blocks' ) ) }</button>
								<button class="${ s.device === 'phone' ? 'is-on' : '' }" data-device="phone">${ esc( __( 'Phone', 'envo-blocks' ) ) }</button>
							</div>
						</div>
						<div class="ebw-pager">
							<button type="button" class="button" data-page-step="-1" aria-label="${ esc( __( 'Previous page', 'envo-blocks' ) ) }">‹</button>
							<select id="ebw-page" data-page>${ t.keys.map( ( k, i ) => `<option value="${ esc( k ) }" ${ k === s.page ? 'selected' : '' }>${ esc( 'home' === k ? __( 'Home (front page)', 'envo-blocks' ) : t.pages[ i ] ) }</option>` ).join( '' ) }</select>
							<button type="button" class="button" data-page-step="1" aria-label="${ esc( __( 'Next page', 'envo-blocks' ) ) }">›</button>
						</div>
					</div>
					<div class="ebw-field"><label for="ebw-name">${ esc( __( 'Store name', 'envo-blocks' ) ) }</label><input id="ebw-name" type="text" class="regular-text" value="${ esc( s.name ) }">
						<p class="ebw-small">${ esc( __( 'Shown in the header instead of the template’s name.', 'envo-blocks' ) ) }</p></div>
					<div class="ebw-field ebw-logo-field">${ logoField() }</div>
					${ colorField( 'brand', __( 'Brand colour', 'envo-blocks' ) ) }
					${ t.accent ? colorField( 'accent', __( 'Second accent', 'envo-blocks' ) ) : '' }
					<p class="ebw-small">${ esc( __( 'Light and dark shades and the text on coloured buttons are worked out for you, so everything stays readable.', 'envo-blocks' ) ) }</p>
					<p><button class="button-link" data-reset>${ esc( __( 'Back to the template colours', 'envo-blocks' ) ) }</button></p>
					<div class="ebw-actions">
						<button class="button" data-go="template">${ esc( __( '← Template', 'envo-blocks' ) ) }</button>
						<button class="button button-primary button-large" data-next>${ esc( __( 'Next: what to import →', 'envo-blocks' ) ) }</button>
					</div>
				</div>
			</aside>
		</div>`;
	}

	/** Logo of the store: the chosen image, or the store name in the template's lettering. */
	function logoField() {
		// Without a logo only the button and a short note (the panel stays short); the preview box comes with a logo.
		if ( ! s.logo ) {
			return `<label>${ esc( __( 'Logo', 'envo-blocks' ) ) }</label>
				<div class="ebw-logo-empty">
					<button type="button" class="button" data-logo-pick>${ esc( __( 'Upload or choose logo…', 'envo-blocks' ) ) }</button>
					<span class="ebw-small">${ esc( __( 'Without a logo the store name is shown in the template’s lettering.', 'envo-blocks' ) ) }</span>
				</div>`;
		}
		return `<label>${ esc( __( 'Logo', 'envo-blocks' ) ) }</label>
			<div class="ebw-brandlogo"><img src="${ esc( s.logo.url ) }" alt=""></div>
			<p class="ebw-logo__actions">
				<button type="button" class="button" data-logo-pick>${ esc( s.logo ? __( 'Change logo…', 'envo-blocks' ) : __( 'Upload or choose logo…', 'envo-blocks' ) ) }</button>
				${ s.logo ? `<button type="button" class="button-link button-link-delete" data-logo-remove>${ esc( __( 'Use the name instead', 'envo-blocks' ) ) }</button>` : '' }
			</p>`;
	}

	/** WordPress media window (upload or Media Library), images only. */
	let mediaFrame = null;
	function pickLogo() {
		if ( ! window.wp || ! wp.media ) {
			return;
		}
		if ( ! mediaFrame ) {
			mediaFrame = wp.media( {
				title: __( 'Logo of your store', 'envo-blocks' ),
				button: { text: __( 'Use as logo', 'envo-blocks' ) },
				library: { type: 'image' },
				multiple: false,
			} );
			mediaFrame.on( 'select', () => {
				const a = mediaFrame.state().get( 'selection' ).first().toJSON();
				const size = a.sizes && ( a.sizes.medium || a.sizes.full );
				setLogo( { id: a.id, url: size ? size.url : a.url } );
			} );
		}
		mediaFrame.open();
	}

	function setLogo( logo ) {
		s.logo = logo;
		const field = app.querySelector( '.ebw-logo-field' );
		if ( field ) {
			field.innerHTML = logoField();
		}
		refreshPreview( true );
	}

	/** Saved panel position (per browser), kept inside the window. */
	function panelPos() {
		if ( ! s.panel ) {
			try {
				s.panel = JSON.parse( window.localStorage.getItem( 'envoBlocksWizardPanel' ) || 'null' );
			} catch ( e ) {}
		}
		const w = 360;
		const p = s.panel || { x: window.innerWidth - w - 24, y: 24 };
		return { x: Math.max( 0, Math.min( p.x, window.innerWidth - w ) ), y: Math.max( 0, Math.min( p.y, window.innerHeight - 48 ) ) };
	}

	function savePanel( x, y ) {
		s.panel = { x, y };
		try {
			window.localStorage.setItem( 'envoBlocksWizardPanel', JSON.stringify( s.panel ) );
		} catch ( e ) {}
	}

	/** Live preview of a staged page in the chosen colours (small, without popups and animations). */
	function liveUrl( key ) {
		const t = tpl();
		const q = new URLSearchParams( { page_id: D.state.pages[ key ], preview: 'true', ebw_frame: 1, ebw_thumb: 1, ebw_brand: ( s.colors.brand || t.brand ).replace( '#', '' ) } );
		if ( t.accent ) {
			q.set( 'ebw_accent', ( s.colors.accent || t.accent ).replace( '#', '' ) );
		}
		if ( s.name ) q.set( 'ebw_name', s.name );
		if ( s.logo ) q.set( 'ebw_logo', s.logo.id );
		return D.home + ( D.home.includes( '?' ) ? '&' : '?' ) + q.toString();
	}

	/**
	 * Loads the card previews one after another (a whole store page each – all at once would make the screen
	 * stutter) and scales the 1440 px wide page to the card.
	 */
	let liveQueue = [];
	function startLive() {
		fitLive();
		liveQueue = [ ...app.querySelectorAll( '.ebw-page__live[data-live]:not([src])' ) ];
		const next = () => {
			const f = liveQueue.shift();
			if ( ! f || ! f.isConnected ) {
				return f ? next() : undefined;
			}
			let done = false;
			const finish = () => {
				if ( done ) return;
				done = true;
				// A moment for the page images, then the live picture covers the static one.
				setTimeout( () => {
					f.classList.add( 'is-loaded' );
					next();
				}, 900 );
			};
			f.addEventListener( 'load', finish, { once: true } );
			setTimeout( finish, 15000 );
			f.src = f.dataset.live;
		};
		next();
	}

	function fitLive() {
		app.querySelectorAll( '.ebw-page__shot' ).forEach( ( el ) => el.style.setProperty( '--ebw-scale', el.clientWidth / 1440 ) );
	}
	window.addEventListener( 'resize', fitLive );

	/** Choice of pages / parts shown without a new render (that would load the card previews again). */
	function syncPick() {
		const picked = pickedPages();
		const t = tpl();
		app.querySelectorAll( '.ebw-page' ).forEach( ( li ) => {
			const c = li.querySelector( '[data-pick-page]' );
			const on = picked.includes( c.value );
			c.checked = on;
			li.classList.toggle( 'is-on', on );
		} );
		const count = app.querySelector( '.ebw-pages__count' );
		if ( count ) {
			const all = picked.length === t.keys.length;
			count.firstChild.textContent = sprintf( __( '%1$d of %2$d pages chosen', 'envo-blocks' ), picked.length, t.keys.length ) + ' ';
			const b = count.querySelector( '[data-pick-all]' );
			b.dataset.pickAll = all ? '0' : '1';
			b.textContent = all ? __( 'Clear all', 'envo-blocks' ) : __( 'Choose all', 'envo-blocks' );
		}
		const warn = app.querySelector( '.ebw-home-warn' );
		if ( warn ) {
			warn.hidden = picked.includes( 'home' );
		}
		const build = app.querySelector( '[data-build]' );
		if ( build ) {
			build.disabled = ! picked.length;
		}
		app.querySelectorAll( '[data-part]' ).forEach( ( r ) => {
			r.checked = s.pick[ r.dataset.part ] === r.value;
			r.closest( '.ebw-radio' ).classList.toggle( 'is-selected', r.checked );
		} );
	}

	/** Page keys chosen for import (all by default). */
	function pickedPages() {
		return s.pick.pages || tpl().keys.slice();
	}

	function partChoice( area, label, help ) {
		const n = tpl().parts[ area ];
		const opts = [
			[ 'site', __( 'Use it for the whole site', 'envo-blocks' ), __( 'Every page gets it – also the shop, product, cart, checkout and blog pages. It replaces the theme’s own; you can edit it later in Appearance › Editor.', 'envo-blocks' ) ],
			[ 'pages', __( 'Only on the imported pages', 'envo-blocks' ), __( 'The other pages keep the theme’s own.', 'envo-blocks' ) ],
			[ 'none', __( 'Don’t import it', 'envo-blocks' ), __( 'All pages keep the theme’s own.', 'envo-blocks' ) ],
		];
		return `<fieldset class="ebw-card ebw-part">
			<legend>${ esc( label ) }</legend>
			<p class="ebw-small">${ esc( help ) }${ n ? '' : ' ' + esc( __( '(This template has none.)', 'envo-blocks' ) ) }</p>
			${ opts.map( ( [ v, title, desc ] ) => `<label class="ebw-radio ${ s.pick[ area ] === v ? 'is-selected' : '' }">
				<input type="radio" name="ebw-${ area }" value="${ v }" data-part="${ area }" ${ s.pick[ area ] === v ? 'checked' : '' } ${ n ? '' : 'disabled' }>
				<span><strong>${ esc( title ) }</strong>${ 'site' === v ? ` <em class="ebw-tag">${ esc( __( 'Recommended', 'envo-blocks' ) ) }</em>` : '' }<br><span class="ebw-small">${ esc( desc ) }</span></span>
			</label>` ).join( '' ) }
		</fieldset>`;
	}

	function viewImport() {
		const t = tpl();
		if ( ! t ) {
			return viewTemplate();
		}
		const picked = pickedPages();
		return `<p class="ebw-intro">${ esc( __( 'Choose what goes into your store. You can add the other pages later by running the wizard again.', 'envo-blocks' ) ) }</p>
		<div class="ebw-import">
			<section class="ebw-card">
				<h3>${ esc( __( 'Pages', 'envo-blocks' ) ) }</h3>
				<p class="ebw-small">${ esc( __( 'Links of the template to pages you leave out go to the front page.', 'envo-blocks' ) ) }</p>
				<ul class="ebw-pages">${ t.keys.map( ( k, i ) => {
					const on = picked.includes( k );
					const title = 'home' === k ? __( 'Home', 'envo-blocks' ) : t.pages[ i ];
					// The whole card is the checkbox: green frame, green veil and a tick over the picture when chosen.
					return `<li class="ebw-page ${ on ? 'is-on' : '' }">
						<label>
							<input type="checkbox" class="screen-reader-text" value="${ esc( k ) }" data-pick-page ${ on ? 'checked' : '' }>
							<span class="ebw-page__shot">
								${ previewImg( t.images && t.images[ i ], t.thumbs && t.thumbs[ i ], '260px' ) }
								${ D.state.pages && D.state.pages[ k ] ? `<iframe class="ebw-page__live" data-live="${ esc( liveUrl( k ) ) }" title="" tabindex="-1" aria-hidden="true" scrolling="no"></iframe>` : '' }
								<span class="ebw-page__tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="30" height="30"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
							</span>
							<span class="ebw-page__name">${ esc( title ) }${ 'home' === k ? ` <em class="ebw-tag">${ esc( __( 'Front page', 'envo-blocks' ) ) }</em>` : '' }</span>
						</label>
						<button type="button" class="button-link ebw-page__peek" data-peek="${ esc( k ) }">${ esc( __( 'Preview', 'envo-blocks' ) ) }</button>
					</li>`;
				} ).join( '' ) }</ul>
				<p class="ebw-small ebw-pages__count">${ esc( sprintf( __( '%1$d of %2$d pages chosen', 'envo-blocks' ), picked.length, t.keys.length ) ) }
					<button type="button" class="button-link" data-pick-all="${ picked.length === t.keys.length ? '0' : '1' }">${ esc( picked.length === t.keys.length ? __( 'Clear all', 'envo-blocks' ) : __( 'Choose all', 'envo-blocks' ) ) }</button></p>
				<p class="ebw-warn ebw-home-warn" ${ picked.includes( 'home' ) ? 'hidden' : '' }>${ esc( __( 'Without Home your front page stays as it is now.', 'envo-blocks' ) ) }</p>
			</section>
			<div class="ebw-parts">
				${ partChoice( 'header', __( 'Header', 'envo-blocks' ), __( 'Logo, menu, search, cart and the bar above them.', 'envo-blocks' ) ) }
				${ partChoice( 'footer', __( 'Footer', 'envo-blocks' ), __( 'Links, contacts and payment icons at the bottom.', 'envo-blocks' ) ) }
			</div>
		</div>
		<div class="ebw-actions ebw-actions--page">
			<button class="button button-large" data-go="colors">${ esc( __( '← Colours', 'envo-blocks' ) ) }</button>
			<button class="button button-primary button-hero" data-build ${ picked.length ? '' : 'disabled' }>${ esc( D.state.built ? __( 'Save changes', 'envo-blocks' ) : __( 'Build my store', 'envo-blocks' ) ) }</button>
		</div>`;
	}

	function viewStore() {
		const st = D.store || {};
		if ( ! st.available ) {
			return `<section class="ebw-card"><p>${ esc( __( 'WooCommerce is not active – there is nothing to set up here.', 'envo-blocks' ) ) }</p>
				<p><button class="button button-primary" data-go="done">${ esc( __( 'Continue', 'envo-blocks' ) ) }</button></p></section>`;
		}
		const sh = s.shop;
		const sym = ( st.currencies[ sh.currency ] || '' ).replace( /^.*\((.*)\)$/, '$1' );
		const opts = ( list, value ) => Object.keys( list ).map( ( k ) => `<option value="${ esc( k ) }" ${ k === value ? 'selected' : '' }>${ esc( list[ k ] ) }</option>` ).join( '' );
		return `<p class="ebw-intro">${ esc( __( 'A few basics of your shop. Everything can be changed later in WooCommerce › Settings – or skip this step.', 'envo-blocks' ) ) }</p>
		<div class="ebw-shop">
			<section class="ebw-card">
				<h3>${ esc( __( 'Country and currency', 'envo-blocks' ) ) }</h3>
				<p class="ebw-small">${ esc( __( 'Filled in from the language of your site. Prices are shown the way that country writes them.', 'envo-blocks' ) ) }</p>
				<div class="ebw-field"><label for="ebw-country">${ esc( __( 'Your store is in', 'envo-blocks' ) ) }</label><select id="ebw-country" data-shop="country">${ opts( st.countries, sh.country ) }</select></div>
				<div class="ebw-field"><label for="ebw-currency">${ esc( __( 'Currency', 'envo-blocks' ) ) }</label><select id="ebw-currency" data-shop="currency">${ opts( st.currencies, sh.currency ) }</select></div>
				<details class="ebw-addr" data-addr ${ sh.addressOpen ? 'open' : '' }>
					<summary><strong>${ esc( __( 'Store address', 'envo-blocks' ) ) }</strong> <span class="ebw-small">${ esc( addressLine() || __( 'optional – for invoices, e-mails and taxes', 'envo-blocks' ) ) }</span></summary>
					<div class="ebw-sub">
						<div class="ebw-field"><label for="ebw-a1">${ esc( __( 'Address line 1', 'envo-blocks' ) ) }</label><input id="ebw-a1" type="text" data-shop="address.address" value="${ esc( sh.address.address ) }" autocomplete="address-line1"></div>
						<div class="ebw-field"><label for="ebw-a2">${ esc( __( 'Address line 2 (optional)', 'envo-blocks' ) ) }</label><input id="ebw-a2" type="text" data-shop="address.address2" value="${ esc( sh.address.address2 ) }" autocomplete="address-line2"></div>
						<div class="ebw-sub--row">
							<div class="ebw-field"><label for="ebw-city">${ esc( __( 'City', 'envo-blocks' ) ) }</label><input id="ebw-city" type="text" data-shop="address.city" value="${ esc( sh.address.city ) }" autocomplete="address-level2"></div>
							<div class="ebw-field ebw-field--zip"><label for="ebw-zip">${ esc( __( 'Postcode', 'envo-blocks' ) ) }</label><input id="ebw-zip" type="text" data-shop="address.postcode" value="${ esc( sh.address.postcode ) }" autocomplete="postal-code"></div>
						</div>
						${ Object.keys( states( sh.country ) ).length ? `<div class="ebw-field"><label for="ebw-state">${ esc( __( 'State / region', 'envo-blocks' ) ) }</label><select id="ebw-state" data-shop="address.state"><option value="">—</option>${ opts( states( sh.country ), sh.address.state ) }</select></div>` : '' }
						<p class="ebw-small">${ esc( __( 'Shown on invoices and in the order e-mails; WooCommerce also counts taxes from it. The country is the one chosen above.', 'envo-blocks' ) ) }</p>
					</div>
				</details>
			</section>
			<section class="ebw-card">
				<h3>${ esc( __( 'Payments', 'envo-blocks' ) ) }</h3>
				<label class="ebw-check"><input type="checkbox" data-shop="bacs.on" ${ sh.bacs.on ? 'checked' : '' }>
					<span><strong>${ esc( __( 'Bank transfer with a QR code', 'envo-blocks' ) ) }</strong><br><span class="ebw-small">${ esc( __( 'The customer scans the QR code on the thank-you page or in the order e-mail and the payment is filled in for them.', 'envo-blocks' ) ) }</span></span></label>
				${ sh.bacs.on ? `<div class="ebw-sub">
					<div class="ebw-field"><label for="ebw-acc">${ esc( __( 'Account holder', 'envo-blocks' ) ) }</label><input id="ebw-acc" type="text" data-shop="bacs.name" value="${ esc( sh.bacs.name ) }"></div>
					<div class="ebw-field"><label for="ebw-iban">IBAN</label><input id="ebw-iban" type="text" data-shop="bacs.iban" value="${ esc( sh.bacs.iban ) }" placeholder="CZ65 0800 0000 1920 0014 5399" spellcheck="false"></div>
					<div class="ebw-field"><label for="ebw-bic">${ esc( __( 'BIC / SWIFT (optional)', 'envo-blocks' ) ) }</label><input id="ebw-bic" type="text" data-shop="bacs.bic" value="${ esc( sh.bacs.bic ) }" spellcheck="false"></div>
					<p class="ebw-small">${ esc( __( 'Without an IBAN the transfer is on, but there is no QR code yet.', 'envo-blocks' ) ) }</p>
				</div>` : '' }
				<label class="ebw-check"><input type="checkbox" data-shop="cod" ${ sh.cod ? 'checked' : '' }>
					<span><strong>${ esc( __( 'Cash on delivery', 'envo-blocks' ) ) }</strong><br><span class="ebw-small">${ esc( __( 'The customer pays the courier.', 'envo-blocks' ) ) }</span></span></label>
			</section>
			<section class="ebw-card">
				<h3>${ esc( __( 'Delivery', 'envo-blocks' ) ) }</h3>
				<label class="ebw-check"><input type="checkbox" data-shop="shipping.on" ${ sh.shipping.on ? 'checked' : '' }>
					<span><strong>${ esc( sprintf( __( 'Delivery within %s', 'envo-blocks' ), st.countries[ sh.country ] || sh.country ) ) }</strong><br><span class="ebw-small">${ esc( __( 'One shipping zone with a price and free delivery from an amount. The free shipping bar of your template shows this amount by itself.', 'envo-blocks' ) ) }</span></span></label>
				${ sh.shipping.on ? `<div class="ebw-sub ebw-sub--row">
					<div class="ebw-field"><label for="ebw-cost">${ esc( __( 'Price', 'envo-blocks' ) ) }</label><span class="ebw-money"><input id="ebw-cost" type="number" min="0" step="0.01" data-shop="shipping.cost" value="${ esc( sh.shipping.cost ) }"><em>${ esc( sym ) }</em></span></div>
					<div class="ebw-field"><label for="ebw-free">${ esc( __( 'Free from', 'envo-blocks' ) ) }</label><span class="ebw-money"><input id="ebw-free" type="number" min="0" step="1" data-shop="shipping.free" value="${ esc( sh.shipping.free ) }"><em>${ esc( sym ) }</em></span></div>
				</div><p class="ebw-small">${ esc( __( 'Leave “Free from” empty for no free delivery.', 'envo-blocks' ) ) }</p>` : '' }
			</section>
			<section class="ebw-card">
				<h3>${ esc( __( 'Sample products', 'envo-blocks' ) ) }</h3>
				${ st.samples && st.samples.total ? `<p>${ esc( sprintf( __( 'Your store already has %d sample products.', 'envo-blocks' ), st.samples.total ) ) }</p>` : `<label class="ebw-check"><input type="checkbox" data-shop="samples" ${ sh.samples ? 'checked' : '' }>
					<span><strong>${ esc( __( 'Add 6 sample products', 'envo-blocks' ) ) }</strong><br><span class="ebw-small">${ esc( sprintf( __( 'So the template’s product blocks are not empty while you add your own. Named “Sample product – …”, in the category “Sample products”, with the WooCommerce placeholder picture. Your store has %d products now.', 'envo-blocks' ), st.products || 0 ) ) }</span></span></label>
					<p class="ebw-small">${ esc( __( 'Later one click deletes all samples you did not edit; an edited sample stays as your product.', 'envo-blocks' ) ) }</p>` }
			</section>
			<section class="ebw-card ebw-card--wide">
				<h3>${ esc( __( 'Store pages', 'envo-blocks' ) ) }</h3>
				<p class="ebw-small">${ esc( __( 'The pages WooCommerce uses for the shop, cart, checkout and customer accounts, and the terms and privacy policy the checkout links to. Pick your own page or let the wizard create one.', 'envo-blocks' ) ) }</p>
				<div class="ebw-storepages">${ Object.keys( storePages() ).map( pageRow ).join( '' ) }</div>
			</section>
		</div>
		<div class="ebw-actions ebw-actions--page">
			<button class="button button-large" data-go="done">${ esc( __( 'Skip this step', 'envo-blocks' ) ) }</button>
			<button class="button button-primary button-hero" data-shop-save>${ esc( __( 'Save and finish', 'envo-blocks' ) ) }</button>
		</div>`;
	}

	function viewDone() {
		const tryOurs = ! ours() && oursReady();
		return `${ s.notice ? `<div class="notice notice-success inline"><p>${ esc( s.notice ) }</p></div>` : '' }
		<section class="ebw-card ebw-done">
			<h2>${ esc( __( 'Your store is ready', 'envo-blocks' ) ) }</h2>
			<p>${ esc( __( 'The pages are published, the template home page is your front page and the brand colours are set for the whole site.', 'envo-blocks' ) ) }</p>
			<p class="ebw-row">
				<a class="button button-primary button-hero" href="${ esc( D.home ) }" target="_blank" rel="noopener">${ esc( __( 'View my store ↗', 'envo-blocks' ) ) }</a>
				<a class="button button-hero" href="post.php?post=${ D.state.home }&action=edit">${ esc( __( 'Edit the home page', 'envo-blocks' ) ) }</a>
				<button class="button button-hero" data-go="colors">${ esc( __( 'Change colours', 'envo-blocks' ) ) }</button>
			</p>
		</section>
		${ tryOurs ? `<section class="ebw-reco ebw-reco--again">
			<div class="ebw-reco__shot">${ D.theme.ours.screenshot ? `<img src="${ esc( D.theme.ours.screenshot ) }" alt="">` : '' }</div>
			<div class="ebw-reco__body">
				<span class="ebw-badge">${ esc( __( 'Not happy with the result?', 'envo-blocks' ) ) }</span>
				<h2>${ esc( sprintf( __( 'Try %s', 'envo-blocks' ), D.theme.ours.name ) ) }</h2>
				<p class="ebw-lead">${ esc( sprintf( __( 'Your template was made for our theme. With %s the fonts, spacing and shop pages can look different from the demo. One click switches the theme – your pages, products and colours stay.', 'envo-blocks' ), D.theme.currentName ) ) }</p>
				<button class="button button-primary button-hero" data-theme="${ esc( D.theme.ours.slug ) }" data-stay>${ esc( sprintf( __( 'Switch to %s', 'envo-blocks' ), D.theme.ours.name ) ) }</button>
				<p class="ebw-small">${ esc( __( 'You can switch back any time in Appearance › Themes.', 'envo-blocks' ) ) }</p>
			</div>
		</section>` : '' }
		${ D.store && D.store.available && ! D.store.done ? `<section class="ebw-card ebw-shop-cta">
			<h3>${ esc( __( 'Set up your store', 'envo-blocks' ) ) }</h3>
			<p>${ esc( __( 'Country and currency, bank transfer with a QR code, cash on delivery, delivery prices with free delivery, sample products.', 'envo-blocks' ) ) }</p>
			<p><button class="button button-primary" data-go="store">${ esc( __( 'Set up the store →', 'envo-blocks' ) ) }</button></p>
		</section>` : '' }
		${ viewMore() }
		${ D.store && D.store.samples && D.store.samples.total ? `<section class="ebw-card ebw-samples">
			<h3>${ esc( __( 'Sample products', 'envo-blocks' ) ) }</h3>
			<p>${ esc( sprintf( __( 'Sample products in your store: %1$d (not edited: %2$d).', 'envo-blocks' ), D.store.samples.total, D.store.samples.untouched ) ) }</p>
			${ D.store.samples.untouched ? `<p><button class="button" data-samples-delete>${ esc( sprintf( __( 'Delete the unedited sample products (%d)', 'envo-blocks' ), D.store.samples.untouched ) ) }</button></p>` : '' }
			<p class="ebw-small">${ esc( __( 'Samples you edited stay – they are your products now.', 'envo-blocks' ) ) }</p>
		</section>` : '' }
		<p class="ebw-undo"><button class="button-link button-link-delete" data-undo>${ esc( __( 'Undo everything the wizard did', 'envo-blocks' ) ) }</button>
		<span class="ebw-small">${ esc( __( 'Puts back your theme, front page, store name and colours; the template pages go to the trash.', 'envo-blocks' ) ) }</span></p>`;
	}

	/** "More store settings": Store Layouts, WooCommerce settings and the store features of Envo Blocks. */
	function viewMore() {
		const m = D.more || {};
		if ( ! m.layouts && ! ( m.wc || [] ).length && ! ( m.envo || [] ).length ) {
			return '';
		}
		const list = ( items ) => `<ul class="ebw-more__list">${ items.map( ( i ) => `<li><a href="${ esc( i.url ) }">${ esc( i.label ) }</a></li>` ).join( '' ) }</ul>`;
		return `<section class="ebw-card ebw-more">
			<h3>${ esc( __( 'More store settings', 'envo-blocks' ) ) }</h3>
			<p class="ebw-small">${ esc( __( 'Everything the wizard set and much more – whenever you need it.', 'envo-blocks' ) ) }</p>
			${ m.layouts ? `<div class="ebw-more__layouts">
				<div><strong>${ esc( __( 'Store layouts', 'envo-blocks' ) ) }</strong><br><span class="ebw-small">${ esc( __( 'Your own design of the product page, shop, cart, checkout, side cart, thank-you page and My account – built from blocks, for the whole store or only some products and categories.', 'envo-blocks' ) ) }</span></div>
				<a class="button button-primary" href="${ esc( m.layouts ) }">${ esc( __( 'Open Store Layouts →', 'envo-blocks' ) ) }</a>
			</div>` : '' }
			<div class="ebw-more__cols">
				${ ( m.wc || [] ).length ? `<div><h4>${ esc( __( 'WooCommerce', 'envo-blocks' ) ) }</h4>${ list( m.wc ) }</div>` : '' }
				${ ( m.envo || [] ).length ? `<div><h4>${ esc( __( 'Envo Blocks', 'envo-blocks' ) ) }</h4>${ list( m.envo ) }</div>` : '' }
			</div>
		</section>`;
	}

	function overlay() {
		if ( ! s.busy ) {
			return '';
		}
		const p = s.progress;
		return `<div class="ebw-overlay"><div class="ebw-overlay__box">
			<span class="spinner is-active"></span>
			<p>${ esc( s.busy ) }</p>
			${ p ? `<div class="ebw-bar"><span style="width:${ Math.round( ( 100 * p.done ) / p.total ) }%"></span></div><p class="ebw-small">${ esc( p.label ) }</p>` : '' }
		</div></div>`;
	}

	function render() {
		const views = { theme: viewTheme, template: viewTemplate, colors: viewColors, import: viewImport, store: viewStore, done: viewDone };
		const full = 'colors' === s.step && !! tpl();
		// Full-window preview: the admin page under it must not scroll.
		document.documentElement.classList.toggle( 'ebw-is-full', full );
		app.innerHTML = ( full ? '' : header() + ( s.error ? `<div class="notice notice-error inline"><p>${ esc( s.error ) }</p></div>` : '' ) ) + views[ s.step ]() + overlay() + undoModal();
		if ( 'import' === s.step ) {
			startLive();
		}
		keepPreviews();
	}

	// Moving the panel: pointer on the title bar (not on its button); the preview frame ignores the pointer meanwhile,
	// otherwise it would swallow the moves.
	let drag = null;
	app.addEventListener( 'pointerdown', ( e ) => {
		const bar = e.target.closest( '[data-drag]' );
		if ( ! bar || e.target.closest( 'button' ) || 0 !== e.button ) {
			return;
		}
		const panel = bar.closest( '.ebw-panel' );
		const r = panel.getBoundingClientRect();
		drag = { panel, dx: e.clientX - r.left, dy: e.clientY - r.top, w: r.width };
		bar.setPointerCapture( e.pointerId );
		app.querySelector( '.ebw-full' ).classList.add( 'is-dragging' );
		e.preventDefault();
	} );
	app.addEventListener( 'pointermove', ( e ) => {
		if ( ! drag ) {
			return;
		}
		const x = Math.max( 0, Math.min( e.clientX - drag.dx, window.innerWidth - drag.w ) );
		const y = Math.max( 0, Math.min( e.clientY - drag.dy, window.innerHeight - 48 ) );
		drag.panel.style.left = x + 'px';
		drag.panel.style.top = y + 'px';
		// Lower on the screen the panel gets shorter (its options scroll), it never runs out of the window.
		drag.panel.style.maxHeight = window.innerHeight - y - 16 + 'px';
	} );
	const endDrag = () => {
		if ( ! drag ) {
			return;
		}
		savePanel( parseInt( drag.panel.style.left, 10 ), parseInt( drag.panel.style.top, 10 ) );
		const full = app.querySelector( '.ebw-full' );
		if ( full ) {
			full.classList.remove( 'is-dragging' );
		}
		drag = null;
	};
	app.addEventListener( 'pointerup', endDrag );
	app.addEventListener( 'pointercancel', endDrag );
	window.addEventListener( 'resize', () => {
		const panel = app.querySelector( '.ebw-panel' );
		if ( panel ) {
			const p = panelPos();
			panel.style.left = p.x + 'px';
			panel.style.top = p.y + 'px';
			panel.style.maxHeight = window.innerHeight - p.y - 16 + 'px';
		}
	} );

	/* ---------------------------------------------------------------------------------------------------- */

	let timer = 0;
	function refreshPreview( now ) {
		clearTimeout( timer );
		timer = setTimeout( async () => {
			const q = new URLSearchParams();
			[ 'brand', 'accent' ].forEach( ( k ) => s.colors[ k ] && q.set( k, s.colors[ k ] ) );
			q.set( 'page', s.page );
			if ( s.name ) q.set( 'name', s.name );
			if ( s.logo ) q.set( 'logo', s.logo.id );
			try {
				const r = await api( '/preview?' + q.toString() );
				s.summary = r.summary;
				const frame = app.querySelector( '.ebw-full__frame iframe' );
				s.frame = r.url;
				if ( frame && frame.getAttribute( 'src' ) !== r.url ) {
					frame.setAttribute( 'src', r.url );
				}
				const side = app.querySelector( '.ebw-panel' );
				if ( side ) {
					// Only the contrast lines change: update them without rebuilding the inputs.
					app.querySelectorAll( '.ebw-contrast' ).forEach( ( el ) => el.remove() );
					[ 'brand', 'accent' ].forEach( ( k ) => {
						const field = app.querySelector( `[data-color="${ k }"]` );
						const sum = s.summary && s.summary[ k ];
						if ( field && sum && sum.text ) {
							const p = document.createElement( 'p' );
							p.className = 'ebw-contrast';
							p.innerHTML = `<span style="background:${ esc( s.colors[ k ] || tpl()[ k ] ) };color:${ esc( sum.text ) }">Aa</span> ${ esc( sprintf( __( 'Text on it: %1$s, contrast %2$s:1', 'envo-blocks' ), sum.text, sum.contrast ) ) } ${ sum.contrast >= 4.5 ? '✓' : '' }`;
							field.closest( '.ebw-field' ).appendChild( p );
						}
					} );
				}
			} catch ( e ) {
				s.error = e.message;
				render();
			}
		}, now ? 0 : 350 );
	}

	function setColor( key, value, from ) {
		if ( ! /^#[0-9a-f]{6}$/i.test( value ) ) {
			return;
		}
		s.colors[ key ] = value.toLowerCase();
		if ( from !== 'color' ) {
			const c = app.querySelector( `[data-color="${ key }"]` );
			if ( c ) c.value = s.colors[ key ];
		}
		if ( from !== 'hex' ) {
			const h = app.querySelector( `[data-hex="${ key }"]` );
			if ( h ) h.value = s.colors[ key ];
		}
		app.querySelectorAll( `[data-preset="${ key }"]` ).forEach( ( b ) => b.classList.toggle( 'is-on', b.dataset.value === s.colors[ key ] ) );
		refreshPreview();
	}

	async function useTheme( slug, stay ) {
		const install = slug === D.theme.ours.slug && ! D.theme.ours.installed;
		s.busy = install
			? sprintf( __( 'Installing %s from WordPress.org…', 'envo-blocks' ), D.theme.ours.name )
			: __( 'Switching the theme…', 'envo-blocks' );
		render();
		try {
			if ( install ) {
				// The installer of Envo Blocks (the same as in Templates Import): themes_api + Theme_Upgrader, only
				// the theme the templates recommend. It does not activate – /theme below switches with the snapshot
				// for undo.
				await api( D.installRest, 'POST', { type: 'theme', slug, activate: false } );
				s.busy = __( 'Switching the theme…', 'envo-blocks' );
				render();
			}
			D.theme = await api( '/theme', 'POST', { stylesheet: slug } );
			s.busy = '';
			if ( stay ) {
				if ( s.step === 'done' ) {
					s.notice = __( 'Switched to Envo Blocks Store. Your pages and colours stayed as they were.', 'envo-blocks' );
				}
				render();
				if ( s.step === 'colors' ) refreshPreview( true );
			} else {
				go( 'template' );
			}
		} catch ( e ) {
			s.busy = '';
			s.error = e.message;
			render();
		}
	}

	async function stage( slug ) {
		const t = D.templates.find( ( x ) => x.slug === slug );
		s.template = slug;
		s.colors = {};
		s.page = 'home';
		s.pick.pages = null;
		s.busy = sprintf( __( 'Preparing %s…', 'envo-blocks' ), t.name );
		try {
			for ( let i = 0; i < t.pages.length; i++ ) {
				s.progress = { done: i, total: t.pages.length, label: sprintf( __( 'Page %1$d of %2$d: %3$s – matching your products and copying images', 'envo-blocks' ), i + 1, t.pages.length, t.pages[ i ] ) };
				render();
				const r = await api( '/stage', 'POST', { template: slug, index: i } );
				D.state.home = r.home;
				D.state.pages = r.pages;
			}
			D.state.template = slug;
			D.state.built = false;
			s.busy = '';
			s.progress = null;
			s.frame = '';
			go( 'colors' );
			refreshPreview( true );
		} catch ( e ) {
			s.busy = '';
			s.progress = null;
			s.error = e.message;
			render();
		}
	}

	async function build() {
		s.busy = __( 'Building your store…', 'envo-blocks' );
		render();
		try {
			const r = await api( '/build', 'POST', { brand: s.colors.brand || '', accent: s.colors.accent || '', name: s.name, logo: s.logo ? s.logo.id : 0, pages: pickedPages(), header: s.pick.header, footer: s.pick.footer } );
			D.theme = r.theme;
			D.state.built = true;
			s.busy = '';
			s.notice = '';
			go( D.store && D.store.available ? 'store' : 'done' );
		} catch ( e ) {
			s.busy = '';
			s.error = e.message;
			render();
		}
	}

	/** One value of the store step from its field (data-shop="bacs.iban"…). */
	function setShop( el ) {
		const path = el.dataset.shop.split( '.' );
		const value = 'checkbox' === el.type ? el.checked : el.value;
		let o = s.shop;
		path.slice( 0, -1 ).forEach( ( k ) => ( o = o[ k ] ) );
		o[ path[ path.length - 1 ] ] = value;
		if ( 'currency' === el.dataset.shop ) {
			s.shop.currencyTouched = true;
			Object.assign( s.shop.shipping, shipDefaults( value ) );
		}
		if ( 'country' === el.dataset.shop ) {
			s.shop.address.state = '';
		}
		if ( 'country' === el.dataset.shop && ! s.shop.currencyTouched && D.store.currencyOf[ value ] ) {
			// The currency follows the country until it is chosen by hand.
			s.shop.currency = D.store.currencyOf[ value ];
			Object.assign( s.shop.shipping, shipDefaults( s.shop.currency ) );
		}
	}

	async function saveStore() {
		s.busy = __( 'Setting up your store…', 'envo-blocks' );
		render();
		try {
			const sh = s.shop;
			const r = await api( '/store', 'POST', { country: sh.country, currency: sh.currency, bacs: sh.bacs, cod: sh.cod, shipping: sh.shipping, samples: sh.samples, address: sh.address, pages: sh.pages } );
			D.store = r.store;
			s.busy = '';
			s.notice = __( 'Your store settings are saved.', 'envo-blocks' );
			go( 'done' );
		} catch ( e ) {
			s.busy = '';
			s.error = e.message;
			render();
		}
	}

	async function deleteSamples() {
		s.busy = __( 'Deleting the sample products…', 'envo-blocks' );
		render();
		try {
			const r = await api( '/samples/delete', 'POST' );
			D.store = r.store;
			s.busy = '';
			s.notice = sprintf( __( 'Sample products deleted: %d.', 'envo-blocks' ), r.deleted ) + ( r.kept ? ' ' + sprintf( __( 'Kept because you edited them: %d.', 'envo-blocks' ), r.kept ) : '' );
			render();
		} catch ( e ) {
			s.busy = '';
			s.error = e.message;
			render();
		}
	}

	/** "Undo everything": first what will happen (and what you changed by hand since), then the confirmation. */
	async function undo() {
		s.busy = __( 'Checking what goes back…', 'envo-blocks' );
		render();
		try {
			s.undoPreview = await api( '/undo-preview' );
		} catch ( e ) {
			s.error = e.message;
		}
		s.busy = '';
		render();
	}

	async function doUndo() {
		s.undoPreview = null;
		s.busy = __( 'Undoing…', 'envo-blocks' );
		render();
		try {
			await api( '/undo', 'POST' );
			location.href = location.pathname + '?page=envo-blocks-wizard';
		} catch ( e ) {
			s.busy = '';
			s.error = e.message;
			render();
		}
	}

	function undoModal() {
		const p = s.undoPreview;
		if ( ! p ) {
			return '';
		}
		return `<div class="ebw-overlay" role="dialog" aria-modal="true" aria-labelledby="ebw-undo-title"><div class="ebw-overlay__box ebw-undo-box">
			<h2 id="ebw-undo-title">${ esc( __( 'Undo everything the wizard did?', 'envo-blocks' ) ) }</h2>
			${ p.warnings.length ? `<div class="ebw-undo-warn">
				<strong>${ esc( __( 'You changed these after the wizard – undo replaces your changes:', 'envo-blocks' ) ) }</strong>
				<ul>${ p.warnings.map( ( w ) => `<li>${ esc( w ) }</li>` ).join( '' ) }</ul>
			</div>` : '' }
			<p><strong>${ esc( __( 'What goes back:', 'envo-blocks' ) ) }</strong></p>
			<ul class="ebw-undo-list">${ p.items.map( ( w ) => `<li>${ esc( w ) }</li>` ).join( '' ) || `<li>${ esc( __( 'Nothing to put back.', 'envo-blocks' ) ) }</li>` }</ul>
			<p class="ebw-small">${ esc( __( 'Pictures copied into the Media Library stay there. Pages and products go to the trash, so you can restore them.', 'envo-blocks' ) ) }</p>
			<div class="ebw-actions">
				<button class="button button-large" data-undo-cancel>${ esc( __( 'Keep everything', 'envo-blocks' ) ) }</button>
				<button class="button button-large ebw-danger" data-undo-confirm>${ esc( p.warnings.length ? __( 'Undo anyway', 'envo-blocks' ) : __( 'Undo everything', 'envo-blocks' ) ) }</button>
			</div>
		</div></div>`;
	}

	app.addEventListener( 'click', ( e ) => {
		const b = e.target.closest( 'button, [data-preset]' );
		if ( ! b ) return;
		const d = b.dataset;
		if ( d.edition ) {
			s.edition = d.edition;
			render();
			return;
		}
		if ( 'catsClear' in d ) {
			s.cats = [];
			render();
			return;
		}
		if ( 'collapse' in d ) {
			// Only the class: a new render would load the preview again.
			s.collapsed = ! s.collapsed;
			const panel = b.closest( '.ebw-panel' );
			panel.classList.toggle( 'is-collapsed', s.collapsed );
			b.textContent = s.collapsed ? '▾' : '▴';
			b.setAttribute( 'aria-expanded', s.collapsed ? 'false' : 'true' );
			b.title = s.collapsed ? __( 'Show options', 'envo-blocks' ) : __( 'Fold the options', 'envo-blocks' );
			return;
		}
		if ( d.go ) {
			go( d.go );
			if ( d.go === 'colors' ) refreshPreview( true );
		} else if ( d.theme ) {
			useTheme( d.theme, 'stay' in d );
		} else if ( 'showOthers' in d ) {
			s.showOthers = true;
			render();
		} else if ( 'otherGo' in d ) {
			const picked = app.querySelector( 'input[name="ebw-other"]:checked' );
			const slug = picked ? picked.value : D.theme.current;
			slug === D.theme.current ? go( 'template' ) : useTheme( slug, false );
		} else if ( d.template ) {
			stage( d.template );
		} else if ( d.preset ) {
			setColor( d.preset, d.value );
		} else if ( 'reset' in d ) {
			s.colors = {};
			render();
			refreshPreview( true );
		} else if ( d.device ) {
			s.device = d.device;
			render();
		} else if ( 'shopSave' in d ) {
			saveStore();
		} else if ( 'samplesDelete' in d ) {
			deleteSamples();
		} else if ( 'logoPick' in d ) {
			pickLogo();
		} else if ( 'logoRemove' in d ) {
			setLogo( null );
		} else if ( 'next' in d ) {
			go( 'import' );
		} else if ( d.pageStep ) {
			const keys = tpl().keys;
			s.page = keys[ ( keys.indexOf( s.page ) + Number( d.pageStep ) + keys.length ) % keys.length ];
			app.querySelector( '[data-page]' ).value = s.page;
			refreshPreview( true );
		} else if ( d.pickAll ) {
			s.pick.pages = '1' === d.pickAll ? tpl().keys.slice() : [];
			syncPick();
		} else if ( d.peek ) {
			// "Preview" next to a page in the import step: back to the full preview of that page.
			s.page = d.peek;
			go( 'colors' );
			refreshPreview( true );
		} else if ( 'build' in d ) {
			build();
		} else if ( 'undoCancel' in d ) {
			s.undoPreview = null;
			render();
		} else if ( 'undoConfirm' in d ) {
			doUndo();
		} else if ( 'undo' in d ) {
			undo();
		}
	} );
	app.addEventListener( 'input', ( e ) => {
		const d = e.target.dataset;
		if ( d.color ) setColor( d.color, e.target.value, 'color' );
		if ( d.hex ) setColor( d.hex, e.target.value.trim(), 'hex' );
		if ( e.target.matches( '[data-shop]' ) && 'text,number'.includes( e.target.type ) ) {
			// Typed values without a new render (the field keeps the focus).
			setShop( e.target );
			const line = app.querySelector( '[data-addr] summary .ebw-small' );
			if ( line && e.target.dataset.shop.startsWith( 'address.' ) ) {
				line.textContent = addressLine() || __( 'optional – for invoices, e-mails and taxes', 'envo-blocks' );
			}
		}
		if ( e.target.id === 'ebw-name' ) {
			s.name = e.target.value;
			refreshPreview();
		}
		if ( 'search' in d ) {
			// Filter without a new render (the field keeps the focus and the caret).
			s.search = e.target.value;
			let any = false;
			app.querySelectorAll( '.ebw-tpl' ).forEach( ( card ) => {
				const t = D.templates.find( ( x ) => card.querySelector( `[data-template="${ x.slug }"]` ) );
				const on = t ? matches( t ) : true;
				card.hidden = ! on;
				any = any || on;
			} );
			app.querySelector( '.ebw-search__count' ).textContent = searchCount();
			app.querySelector( '.ebw-search__none' ).hidden = any;
		}
	} );
	app.addEventListener( 'toggle', ( e ) => {
		if ( e.target.matches && e.target.matches( '[data-addr]' ) ) {
			s.shop.addressOpen = e.target.open;
		}
	}, true );
	app.addEventListener( 'change', ( e ) => {
		const t = e.target;
		if ( t.matches( '[data-shop]' ) ) {
			setShop( t );
			render();
			return;
		}
		if ( t.matches( '[data-page]' ) ) {
			s.page = t.value;
			refreshPreview( true );
			return;
		}
		if ( t.matches( '[data-cat]' ) ) {
			s.cats = [ ...app.querySelectorAll( '[data-cat]:checked' ) ].map( ( c ) => c.dataset.cat );
			render();
			return;
		}
		if ( t.matches( '[data-pick-page]' ) ) {
			s.pick.pages = [ ...app.querySelectorAll( '[data-pick-page]:checked' ) ].map( ( c ) => c.value );
			syncPick();
			return;
		}
		if ( t.matches( '[data-part]' ) ) {
			s.pick[ t.dataset.part ] = t.value;
			syncPick();
			return;
		}
		if ( e.target.name === 'ebw-other' ) {
			s.other = e.target.value;
			app.querySelectorAll( '.ebw-theme' ).forEach( ( l ) => l.classList.toggle( 'is-selected', l.contains( e.target ) ) );
		}
	} );

	render();
	if ( s.step === 'colors' ) {
		refreshPreview( true );
	}
} )();
