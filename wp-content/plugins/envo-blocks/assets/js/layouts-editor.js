/**
 * Envo Blocks - Store Layouts in the editor: the blocks made for the layouts (server preview) and, in the editor of a
 * product layout, the product of the preview as the context of the top-level blocks (title, price, gallery, add to
 * cart … show that product, as they do in a template).
 * Plain ES5 on the wp.* globals, no build step needed.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.blockEditor ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelColorSettings = wp.blockEditor.PanelColorSettings;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var TextControl = wp.components.TextControl;
	var Disabled = wp.components.Disabled;
	var ServerSideRender = wp.serverSideRender;

	var layout = window.envoBlocksLayout || {};
	var NO_MARGIN = { __nextHasNoMarginBottom: true, __next40pxDefaultSize: true };

	function control( type, extra ) {
		return el( type, Object.assign( {}, NO_MARGIN, extra ) );
	}

	function set( setAttributes, key ) {
		return function ( value ) {
			var update = {};
			update[ key ] = value;
			setAttributes( update );
		};
	}

	/** The server preview with the block's own settings (the supports are on the wrapper). */
	function preview( name, attributes ) {
		var skip = [ 'style', 'fontSize', 'align', 'className', 'anchor', 'lock', 'metadata', 'textColor', 'backgroundColor', 'borderColor' ];
		var own = {};
		Object.keys( attributes ).forEach( function ( key ) {
			if ( -1 === skip.indexOf( key ) ) {
				own[ key ] = attributes[ key ];
			}
		} );
		return el( 'div', useBlockProps(), el( Disabled, null, el( ServerSideRender, { block: name, attributes: own } ) ) );
	}

	function colors( attributes, setAttributes, fields ) {
		return el(
			InspectorControls,
			{ group: 'styles' },
			el( PanelColorSettings, {
				title: __( 'Colors', 'envo-blocks' ),
				initialOpen: false,
				colorSettings: fields.map( function ( field ) {
					return {
						label: field[ 1 ],
						value: attributes[ field[ 0 ] ] || undefined,
						onChange: function ( v ) {
							set( setAttributes, field[ 0 ] )( v || '' );
						},
					};
				} ),
			} )
		);
	}

	function panel( title, children ) {
		return el( InspectorControls, null, el( PanelBody, { title: title, initialOpen: true }, children ) );
	}

	var CATEGORY = { category: 'envo-blocks' };

	registerBlockType( 'envo-blocks/store-breadcrumbs', Object.assign( {}, CATEGORY, {
		edit: function ( config ) {
			var a = config.attributes;
			return el(
				Fragment,
				null,
				panel( __( 'Breadcrumbs', 'envo-blocks' ), [
					control( TextControl, { key: 'sep', label: __( 'Separator', 'envo-blocks' ), value: a.separator, maxLength: 3, onChange: set( config.setAttributes, 'separator' ) } ),
					control( SelectControl, {
						key: 'align',
						label: __( 'Alignment', 'envo-blocks' ),
						value: a.textAlign,
						options: [
							{ value: 'left', label: __( 'Left', 'envo-blocks' ) },
							{ value: 'center', label: __( 'Center', 'envo-blocks' ) },
							{ value: 'right', label: __( 'Right', 'envo-blocks' ) },
						],
						onChange: set( config.setAttributes, 'textAlign' ),
					} ),
					el( 'p', { key: 'help', className: 'components-base-control__help' }, __( 'The editor shows a sample path; the store shows the real one.', 'envo-blocks' ) ),
				] ),
				preview( 'envo-blocks/store-breadcrumbs', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	registerBlockType( 'envo-blocks/shop-toolbar', Object.assign( {}, CATEGORY, {
		edit: function ( config ) {
			var a = config.attributes;
			return el(
				Fragment,
				null,
				panel( __( 'Shop toolbar', 'envo-blocks' ), [
					control( ToggleControl, { key: 'count', label: __( 'Number of products', 'envo-blocks' ), checked: !! a.showCount, onChange: set( config.setAttributes, 'showCount' ) } ),
					control( ToggleControl, { key: 'sort', label: __( 'Sorting', 'envo-blocks' ), checked: !! a.showSorting, onChange: set( config.setAttributes, 'showSorting' ) } ),
					control( SelectControl, {
						key: 'style',
						label: __( 'Style', 'envo-blocks' ),
						value: a.barStyle,
						options: [
							{ value: 'plain', label: __( 'Plain', 'envo-blocks' ) },
							{ value: 'bar', label: __( 'Bar with a line', 'envo-blocks' ) },
							{ value: 'pill', label: __( 'Rounded', 'envo-blocks' ) },
						],
						onChange: set( config.setAttributes, 'barStyle' ),
					} ),
					el( 'p', { key: 'help', className: 'components-base-control__help' }, __( 'Shown on the shop, the product categories and tags, and the product search. Put a Product Collection that inherits the query below it.', 'envo-blocks' ) ),
				] ),
				preview( 'envo-blocks/shop-toolbar', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	registerBlockType( 'envo-blocks/order-received', Object.assign( {}, CATEGORY, {
		edit: function ( config ) {
			var a = config.attributes;
			return el(
				Fragment,
				null,
				panel( __( 'Order received', 'envo-blocks' ), [
					control( SelectControl, {
						key: 'style',
						label: __( 'Style', 'envo-blocks' ),
						value: a.boxStyle,
						options: [
							{ value: 'cards', label: __( 'Cards', 'envo-blocks' ) },
							{ value: 'receipt', label: __( 'Receipt', 'envo-blocks' ) },
							{ value: 'minimal', label: __( 'Minimal', 'envo-blocks' ) },
							{ value: 'split', label: __( 'Two columns', 'envo-blocks' ) },
						],
						onChange: set( config.setAttributes, 'boxStyle' ),
					} ),
					control( ToggleControl, { key: 'msg', label: __( 'Thank-you message of WooCommerce', 'envo-blocks' ), checked: !! a.showMessage, onChange: set( config.setAttributes, 'showMessage' ) } ),
					control( ToggleControl, { key: 'cust', label: __( 'Addresses', 'envo-blocks' ), checked: !! a.showCustomer, onChange: set( config.setAttributes, 'showCustomer' ) } ),
					el( 'p', { key: 'help', className: 'components-base-control__help' }, __( 'Shown on the order received page; the editor shows your newest order or a sample.', 'envo-blocks' ) ),
				] ),
				colors( a, config.setAttributes, [ [ 'accentColor', __( 'Accent', 'envo-blocks' ) ] ] ),
				preview( 'envo-blocks/order-received', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	registerBlockType( 'envo-blocks/my-account', Object.assign( {}, CATEGORY, {
		edit: function ( config ) {
			var a = config.attributes;
			return el(
				Fragment,
				null,
				panel( __( 'My account', 'envo-blocks' ), [
					control( SelectControl, {
						key: 'style',
						label: __( 'Menu', 'envo-blocks' ),
						value: a.menuStyle,
						options: [
							{ value: 'sidebar', label: __( 'Sidebar', 'envo-blocks' ) },
							{ value: 'tabs', label: __( 'Tabs above', 'envo-blocks' ) },
							{ value: 'tiles', label: __( 'Tiles', 'envo-blocks' ) },
							{ value: 'compact', label: __( 'Compact list', 'envo-blocks' ) },
						],
						onChange: set( config.setAttributes, 'menuStyle' ),
					} ),
					el( 'p', { key: 'help', className: 'components-base-control__help' }, __( 'Visitors who are not signed in see the login and register forms in the same style.', 'envo-blocks' ) ),
				] ),
				colors( a, config.setAttributes, [
					[ 'accentColor', __( 'Accent', 'envo-blocks' ) ],
					[ 'surfaceColor', __( 'Boxes', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/my-account', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	registerBlockType( 'envo-blocks/checkout-form', Object.assign( {}, CATEGORY, {
		edit: function ( config ) {
			var a = config.attributes;
			var s = config.setAttributes;
			return el(
				Fragment,
				null,
				panel( __( 'Checkout form', 'envo-blocks' ), [
					control( SelectControl, {
						key: 'layout',
						label: __( 'Layout', 'envo-blocks' ),
						value: a.formLayout,
						options: [
							{ value: 'two-columns', label: __( 'Two columns (form and summary)', 'envo-blocks' ) },
							{ value: 'one-column', label: __( 'One column', 'envo-blocks' ) },
							{ value: 'summary-top', label: __( 'Summary on top', 'envo-blocks' ) },
						],
						onChange: set( s, 'formLayout' ),
					} ),
					'two-columns' === a.formLayout ? control( ToggleControl, { key: 'sticky', label: __( 'The summary stays in view while scrolling', 'envo-blocks' ), checked: !! a.stickySummary, onChange: set( s, 'stickySummary' ) } ) : null,
					control( ToggleControl, { key: 'box', label: __( 'Summary in a box', 'envo-blocks' ), checked: !! a.summaryBox, onChange: set( s, 'summaryBox' ) } ),
					el( 'p', { key: 'help', className: 'components-base-control__help' }, __( 'Shows the WooCommerce checkout of your store (the Checkout block or the classic checkout) with your payment methods, shipping and coupons. Edit its fields on the checkout page. The editor shows a sketch.', 'envo-blocks' ) ),
				] ),
				preview( 'envo-blocks/checkout-form', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	registerBlockType( 'envo-blocks/checkout-header', Object.assign( {}, CATEGORY, {
		edit: function ( config ) {
			var a = config.attributes;
			var s = config.setAttributes;
			return el(
				Fragment,
				null,
				panel( __( 'Checkout header', 'envo-blocks' ), [
					control( SelectControl, {
						key: 'style',
						label: __( 'Style', 'envo-blocks' ),
						value: a.headerStyle,
						options: [
							{ value: 'split', label: __( 'Logo, note and link in a row', 'envo-blocks' ) },
							{ value: 'centered', label: __( 'Centered', 'envo-blocks' ) },
						],
						onChange: set( s, 'headerStyle' ),
					} ),
					control( ToggleControl, { key: 'logo', label: __( 'Logo (or the site name)', 'envo-blocks' ), checked: !! a.showLogo, onChange: set( s, 'showLogo' ) } ),
					a.showLogo ? el( wp.components.RangeControl, Object.assign( {}, NO_MARGIN, { key: 'w', label: __( 'Logo width (px)', 'envo-blocks' ), value: a.logoWidth, min: 40, max: 400, onChange: set( s, 'logoWidth' ) } ) ) : null,
					control( TextControl, { key: 'secure', label: __( 'Secure checkout note', 'envo-blocks' ), value: a.secureText, placeholder: __( 'Secure checkout', 'envo-blocks' ), onChange: set( s, 'secureText' ) } ),
					control( ToggleControl, { key: 'back', label: __( 'Link back to the cart', 'envo-blocks' ), checked: !! a.showBack, onChange: set( s, 'showBack' ) } ),
					a.showBack ? control( TextControl, { key: 'backtext', label: __( 'Link text', 'envo-blocks' ), value: a.backText, placeholder: __( 'Back to cart', 'envo-blocks' ), onChange: set( s, 'backText' ) } ) : null,
					el( 'p', { key: 'help', className: 'components-base-control__help' }, __( 'The logo of Appearance › Customize › Site Identity (or the Site Logo block of the Site Editor).', 'envo-blocks' ) ),
				] ),
				preview( 'envo-blocks/checkout-header', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	var SIDE_CART_HELP =__( 'Shown in the side cart panel and filled with the visitor’s cart in the browser; the editor shows a sample.', 'envo-blocks' );

	registerBlockType( 'envo-blocks/cart-items', Object.assign( {}, CATEGORY, {
		edit: function ( config ) {
			var a = config.attributes;
			var s = config.setAttributes;
			return el(
				Fragment,
				null,
				panel( __( 'Cart items', 'envo-blocks' ), [
					control( ToggleControl, { key: 'head', label: __( 'Heading', 'envo-blocks' ), checked: !! a.showHeading, onChange: set( s, 'showHeading' ) } ),
					a.showHeading ? control( TextControl, { key: 'title', label: __( 'Heading text', 'envo-blocks' ), value: a.heading, placeholder: __( 'Your cart', 'envo-blocks' ), onChange: set( s, 'heading' ) } ) : null,
					a.showHeading ? control( ToggleControl, { key: 'count', label: __( 'Number of items', 'envo-blocks' ), checked: !! a.showCount, onChange: set( s, 'showCount' ) } ) : null,
					control( SelectControl, {
						key: 'style',
						label: __( 'Style', 'envo-blocks' ),
						value: a.itemStyle,
						options: [
							{ value: 'list', label: __( 'List', 'envo-blocks' ) },
							{ value: 'compact', label: __( 'Compact', 'envo-blocks' ) },
							{ value: 'cards', label: __( 'Cards', 'envo-blocks' ) },
						],
						onChange: set( s, 'itemStyle' ),
					} ),
					control( ToggleControl, { key: 'img', label: __( 'Images', 'envo-blocks' ), checked: !! a.showImages, onChange: set( s, 'showImages' ) } ),
					control( ToggleControl, { key: 'qty', label: __( 'Quantity buttons', 'envo-blocks' ), checked: !! a.showQuantity, onChange: set( s, 'showQuantity' ) } ),
					control( ToggleControl, { key: 'rm', label: __( 'Remove links', 'envo-blocks' ), checked: !! a.showRemove, onChange: set( s, 'showRemove' ) } ),
					control( TextControl, { key: 'empty', label: __( 'Text of an empty cart', 'envo-blocks' ), value: a.emptyText, placeholder: __( 'Your cart is empty.', 'envo-blocks' ), onChange: set( s, 'emptyText' ) } ),
					control( ToggleControl, { key: 'shop', label: __( 'Start shopping button (empty cart)', 'envo-blocks' ), checked: !! a.emptyButton, onChange: set( s, 'emptyButton' ) } ),
					el( 'p', { key: 'help', className: 'components-base-control__help' }, SIDE_CART_HELP ),
				] ),
				preview( 'envo-blocks/cart-items', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	registerBlockType( 'envo-blocks/cart-summary', Object.assign( {}, CATEGORY, {
		edit: function ( config ) {
			var a = config.attributes;
			var s = config.setAttributes;
			return el(
				Fragment,
				null,
				panel( __( 'Cart summary', 'envo-blocks' ), [
					control( ToggleControl, { key: 'save', label: __( 'Savings (sale prices and coupons)', 'envo-blocks' ), checked: !! a.showSavings, onChange: set( s, 'showSavings' ) } ),
					control( ToggleControl, { key: 'coupon', label: __( 'Coupon field', 'envo-blocks' ), checked: !! a.showCoupon, onChange: set( s, 'showCoupon' ) } ),
					control( ToggleControl, { key: 'note', label: __( 'Note', 'envo-blocks' ), checked: !! a.showNote, onChange: set( s, 'showNote' ) } ),
					a.showNote ? control( TextControl, { key: 'notetext', label: __( 'Note text', 'envo-blocks' ), value: a.note, placeholder: __( 'Shipping and taxes are calculated at checkout.', 'envo-blocks' ), onChange: set( s, 'note' ) } ) : null,
					control( SelectControl, {
						key: 'buttons',
						label: __( 'Buttons', 'envo-blocks' ),
						value: a.buttons,
						options: [
							{ value: 'stacked', label: __( 'Under each other', 'envo-blocks' ) },
							{ value: 'row', label: __( 'Side by side', 'envo-blocks' ) },
							{ value: 'checkout', label: __( 'Checkout + a View cart link', 'envo-blocks' ) },
						],
						onChange: set( s, 'buttons' ),
					} ),
					control( ToggleControl, { key: 'view', label: __( 'View cart', 'envo-blocks' ), checked: !! a.showViewCart, onChange: set( s, 'showViewCart' ) } ),
					control( ToggleControl, { key: 'checkout', label: __( 'Checkout', 'envo-blocks' ), checked: !! a.showCheckout, onChange: set( s, 'showCheckout' ) } ),
					a.showCheckout ? control( TextControl, { key: 'label', label: __( 'Checkout button text', 'envo-blocks' ), value: a.checkoutLabel, placeholder: __( 'Checkout', 'envo-blocks' ), onChange: set( s, 'checkoutLabel' ) } ) : null,
					control( ToggleControl, { key: 'sticky', label: __( 'Stick to the bottom of the panel', 'envo-blocks' ), checked: !! a.sticky, onChange: set( s, 'sticky' ) } ),
					el( 'p', { key: 'help', className: 'components-base-control__help' }, SIDE_CART_HELP ),
				] ),
				preview( 'envo-blocks/cart-summary', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	// The product of the preview for every block that has the layout itself as its context (the blocks inside a
	// product list keep the products the list gives them).
	var Provider = wp.blockEditor.BlockContextProvider;
	if ( layout.product && Provider && wp.hooks && wp.compose ) {
		var context = { postId: layout.product, postType: 'product' };
		wp.hooks.addFilter(
			'editor.BlockEdit',
			'envo-blocks/layout-product',
			wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
				return function ( props ) {
					if ( ! props.context || ( layout.postType || 'envo_layout' ) !== props.context.postType ) {
						return el( BlockEdit, props );
					}
					return el( Provider, { value: context }, el( BlockEdit, Object.assign( {}, props, { context: Object.assign( {}, props.context, context ) } ) ) );
				};
			}, 'withEnvoLayoutProduct' )
		);
	}
} )( window.wp );
