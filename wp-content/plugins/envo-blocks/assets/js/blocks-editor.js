/**
 * Envo Blocks - editor script of the blocks (settings + server preview).
 * Plain ES5 on the wp.* globals, no build step needed.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;

	/**
	 * Inner block templates: WordPress 7.2 reads them from the block type settings (the "template" prop of
	 * useInnerBlocksProps is deprecated there), older versions only from the prop.
	 */
	function innerTemplate( template ) {
		return window.envoBlocksBlocks && window.envoBlocksBlocks.typeTemplates ? undefined : template;
	}
	var InspectorControls = wp.blockEditor.InspectorControls;
	var InnerBlocks = wp.blockEditor.InnerBlocks;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var useInnerBlocksProps = wp.blockEditor.useInnerBlocksProps;
	var PanelColorSettings = wp.blockEditor.PanelColorSettings;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var CheckboxControl = wp.components.CheckboxControl;
	var ComboboxControl = wp.components.ComboboxControl;
	var Button = wp.components.Button;
	var Disabled = wp.components.Disabled;
	var ServerSideRender = wp.serverSideRender;
	var apiFetch = wp.apiFetch;

	var data = window.envoBlocksBlocks || {};
	var NO_MARGIN = { __nextHasNoMarginBottom: true, __next40pxDefaultSize: true, className: 'sb-ctl' };

	function props( extra ) {
		return Object.assign( {}, NO_MARGIN, extra );
	}

	/** A highlighted note about what Envo Blocks PRO adds to a block, with a "Learn more" link (only without PRO). */
	function proNote( text ) {
		return el(
			'div',
			{ className: 'envo-pro-note' },
			el( 'span', { className: 'envo-pro-note__badge' }, 'PRO' ),
			el( 'p', { className: 'envo-pro-note__text' }, text ),
			data.proUrl ? el( Button, { variant: 'secondary', size: 'compact', href: data.proUrl, target: '_blank', rel: 'noopener noreferrer', className: 'envo-pro-note__button' }, __( 'Learn more', 'envo-blocks' ) ) : null
		);
	}

	/** Field helpers bound to the block attributes. */
	function makeFields( attributes, setAttributes ) {
		function set( key, value ) {
			var update = {};
			update[ key ] = value;
			setAttributes( update );
		}
		return {
			toggle: function ( key, label, help ) {
				return el( ToggleControl, props( { label: label, help: help, checked: !! attributes[ key ], onChange: function ( v ) { set( key, v ); } } ) );
			},
			select: function ( key, label, options, help ) {
				return el( SelectControl, props( { label: label, help: help, value: attributes[ key ], options: options, onChange: function ( v ) { set( key, v ); } } ) );
			},
			range: function ( key, label, min, max, help ) {
				return el( RangeControl, props( { label: label, help: help, value: attributes[ key ], min: min, max: max, onChange: function ( v ) { set( key, undefined === v ? min : v ); } } ) );
			},
			text: function ( key, label, help, placeholder ) {
				return el( TextControl, props( { label: label, help: help, placeholder: placeholder, value: attributes[ key ] || '', onChange: function ( v ) { set( key, v ); } } ) );
			},
			colors: function ( title, fields ) {
				return el(
					InspectorControls,
					{ group: 'styles' },
					el( PanelColorSettings, {
						title: title,
						initialOpen: false,
						colorSettings: fields.map( function ( field ) {
							return {
								label: field[ 1 ],
								value: attributes[ field[ 0 ] ] || undefined,
								enableAlpha: true,
								onChange: function ( v ) { set( field[ 0 ], v || '' ); },
							};
						} ),
					} )
				);
			},
		};
	}

	/**
	 * Server preview. The wrapper already gets the WordPress styles (spacing, typography) from useBlockProps(),
	 * so only the block's own attributes go to the server.
	 */
	function preview( name, attributes ) {
		var skip = [ 'style', 'fontSize', 'align', 'className', 'anchor', 'lock', 'metadata' ];
		var own = {};
		Object.keys( attributes ).forEach( function ( key ) {
			if ( -1 === skip.indexOf( key ) ) {
				own[ key ] = attributes[ key ];
			}
		} );

		return el( 'div', useBlockProps(), el( Disabled, null, el( ServerSideRender, { block: name, attributes: own } ) ) );
	}

	var ALIGN = function ( withRight ) {
		var list = [
			{ value: 'left', label: __( 'Left', 'envo-blocks' ) },
			{ value: 'center', label: __( 'Center', 'envo-blocks' ) },
		];
		if ( withRight ) {
			list.push( { value: 'right', label: __( 'Right', 'envo-blocks' ) } );
		}
		return list;
	};

	var ICON_LABELS = {
		truck: __( 'Truck', 'envo-blocks' ),
		return: __( 'Return arrow', 'envo-blocks' ),
		shield: __( 'Shield', 'envo-blocks' ),
		lock: __( 'Lock', 'envo-blocks' ),
		support: __( 'Headset', 'envo-blocks' ),
		card: __( 'Payment card', 'envo-blocks' ),
		gift: __( 'Gift', 'envo-blocks' ),
		star: __( 'Star', 'envo-blocks' ),
		clock: __( 'Clock', 'envo-blocks' ),
		box: __( 'Package', 'envo-blocks' ),
		tag: __( 'Price tag', 'envo-blocks' ),
		leaf: __( 'Leaf', 'envo-blocks' ),
		heart: __( 'Heart', 'envo-blocks' ),
		check: __( 'Check mark', 'envo-blocks' ),
		globe: __( 'Globe', 'envo-blocks' ),
		store: __( 'Store', 'envo-blocks' ),
		ruler: __( 'Ruler', 'envo-blocks' ),
		calendar: __( 'Calendar', 'envo-blocks' ),
		document: __( 'Document', 'envo-blocks' ),
		info: __( 'Information', 'envo-blocks' ),
		chat: __( 'Speech bubble', 'envo-blocks' ),
	};

	function iconOptions( withNone ) {
		var options = withNone ? [ { value: '', label: __( 'No icon', 'envo-blocks' ) } ] : [];
		( data.icons || Object.keys( ICON_LABELS ) ).forEach( function ( name ) {
			options.push( { value: name, label: ICON_LABELS[ name ] || name } );
		} );
		return options;
	}

	/**
	 * List of rows (icon + text fields) with move, remove and add buttons.
	 * fields: [ [ key, label ], ... ]
	 */
	function Repeater( config ) {
		var items = Array.isArray( config.value ) ? config.value : [];

		function update( next ) {
			config.onChange( next );
		}

		return el(
			'div',
			{ className: 'sb-repeater' },
			items.map( function ( item, index ) {
				function change( key, value ) {
					var next = items.slice();
					next[ index ] = Object.assign( {}, item );
					next[ index ][ key ] = value;
					update( next );
				}
				function move( step ) {
					var next = items.slice();
					var moved = next.splice( index, 1 )[ 0 ];
					next.splice( index + step, 0, moved );
					update( next );
				}

				return el(
					'div',
					{ className: 'sb-repeater__row', key: index },
					el( SelectControl, props( { label: __( 'Icon', 'envo-blocks' ), value: item.icon || '', options: iconOptions( true ), onChange: function ( v ) { change( 'icon', v ); } } ) ),
					config.fields.map( function ( field ) {
						return el( TextControl, props( { key: field[ 0 ], label: field[ 1 ], type: field[ 2 ] || 'text', value: item[ field[ 0 ] ] || '', onChange: function ( v ) { change( field[ 0 ], v ); } } ) );
					} ),
					el(
						'div',
						{ className: 'sb-repeater__tools' },
						el( Button, { size: 'small', variant: 'secondary', disabled: 0 === index, label: __( 'Move up', 'envo-blocks' ), onClick: function () { move( -1 ); } }, '↑' ),
						el( Button, { size: 'small', variant: 'secondary', disabled: index === items.length - 1, label: __( 'Move down', 'envo-blocks' ), onClick: function () { move( 1 ); } }, '↓' ),
						el( Button, { size: 'small', variant: 'link', isDestructive: true, onClick: function () { update( items.filter( function ( x, i ) { return i !== index; } ) ); } }, __( 'Remove', 'envo-blocks' ) )
					)
				);
			} ),
			el( Button, { variant: 'secondary', onClick: function () { update( items.concat( [ Object.assign( { icon: 'check' }, config.empty || {} ) ] ) ); } }, config.addLabel )
		);
	}

	/** Checkboxes for days of the week (ISO numbers 1 = Monday … 7 = Sunday). */
	function DaysControl( config ) {
		var names = [
			__( 'Monday', 'envo-blocks' ),
			__( 'Tuesday', 'envo-blocks' ),
			__( 'Wednesday', 'envo-blocks' ),
			__( 'Thursday', 'envo-blocks' ),
			__( 'Friday', 'envo-blocks' ),
			__( 'Saturday', 'envo-blocks' ),
			__( 'Sunday', 'envo-blocks' ),
		];
		var value = Array.isArray( config.value ) ? config.value : [];

		return el(
			'fieldset',
			{ className: 'sb-ctl sb-days' },
			el( 'legend', { className: 'sb-days__legend' }, config.label ),
			names.map( function ( name, i ) {
				var day = i + 1;
				return el( CheckboxControl, {
					key: day,
					__nextHasNoMarginBottom: true,
					label: name,
					checked: -1 !== value.indexOf( day ),
					onChange: function ( checked ) {
						var next = value.filter( function ( d ) { return d !== day; } );
						if ( checked ) {
							next.push( day );
						}
						next.sort();
						config.onChange( next );
					},
				} );
			} )
		);
	}

	/** Searchable product picker (Store API). */
	function ProductPicker( config ) {
		var state = useState( [] );
		var options = state[ 0 ];
		var setOptions = state[ 1 ];
		var search = useState( '' );

		useEffect( function () {
			var path = '/wc/store/v1/products?per_page=20' + ( search[ 0 ] ? '&search=' + encodeURIComponent( search[ 0 ] ) : '' );
			var current = config.value ? apiFetch( { path: '/wc/store/v1/products/' + config.value } ).catch( function () { return null; } ) : Promise.resolve( null );

			Promise.all( [ apiFetch( { path: path } ).catch( function () { return []; } ), current ] ).then( function ( result ) {
				var list = ( result[ 0 ] || [] ).slice();
				if ( result[ 1 ] && ! list.some( function ( p ) { return p.id === result[ 1 ].id; } ) ) {
					list.unshift( result[ 1 ] );
				}
				setOptions( list.map( function ( p ) {
					var area = document.createElement( 'textarea' );
					area.innerHTML = p.name;
					return { value: String( p.id ), label: area.value };
				} ) );
			} );
		}, [ search[ 0 ], config.value ] );

		return el( ComboboxControl, props( {
			label: config.label,
			value: config.value ? String( config.value ) : null,
			options: options,
			onFilterValueChange: function ( v ) { search[ 1 ]( v ); },
			onChange: function ( v ) { config.onChange( v ? parseInt( v, 10 ) : 0 ); },
		} ) );
	}

	var STYLE_ATTRS = { category: 'envo-blocks' };

	/* Trust Badges */

	registerBlockType( 'envo-blocks/trust-badges', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var payments = data.payments || {};
			var chosen = Array.isArray( a.payments ) ? a.payments : [];

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'General', 'envo-blocks' ), initialOpen: true },
						f.text( 'heading', __( 'Heading (optional)', 'envo-blocks' ), undefined, __( 'e.g. Guaranteed safe checkout', 'envo-blocks' ) ),
						f.select( 'contentAlign', __( 'Alignment', 'envo-blocks' ), ALIGN( true ) )
					),
					el(
						PanelBody,
						{ title: __( 'Payment methods', 'envo-blocks' ), initialOpen: true },
						f.toggle( 'showPayments', __( 'Show payment methods', 'envo-blocks' ) ),
						a.showPayments
							? el(
									Fragment,
									null,
									el( 'div', { className: 'sb-ctl sb-checks' }, Object.keys( payments ).map( function ( key ) {
										return el( CheckboxControl, {
											key: key,
											__nextHasNoMarginBottom: true,
											label: payments[ key ],
											checked: -1 !== chosen.indexOf( key ),
											onChange: function ( checked ) {
												var next = Object.keys( payments ).filter( function ( k ) {
													return k === key ? checked : -1 !== chosen.indexOf( k );
												} );
												config.setAttributes( { payments: next } );
											},
										} );
									} ) ),
									f.select( 'payStyle', __( 'Style', 'envo-blocks' ), [
										{ value: 'color', label: __( 'In color', 'envo-blocks' ) },
										{ value: 'mono', label: __( 'Grayscale (color on hover)', 'envo-blocks' ) },
									] ),
									f.range( 'paySize', __( 'Icon width (px)', 'envo-blocks' ), 28, 96 )
							  )
							: null
					),
					el(
						PanelBody,
						{ title: __( 'Guarantees', 'envo-blocks' ), initialOpen: false },
						f.toggle( 'showBadges', __( 'Show guarantees', 'envo-blocks' ) ),
						a.showBadges
							? el( Repeater, {
									value: Array.isArray( a.badges ) ? a.badges : data.badges || [],
									fields: [ [ 'text', __( 'Text', 'envo-blocks' ) ] ],
									addLabel: __( 'Add guarantee', 'envo-blocks' ),
									onChange: function ( v ) { config.setAttributes( { badges: v } ); },
							  } )
							: null
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'iconColor', __( 'Icons', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'borderColor', __( 'Border', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/trust-badges', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Features Bar */

	registerBlockType( 'envo-blocks/features-bar', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Items', 'envo-blocks' ), initialOpen: true },
						el( Repeater, {
							value: Array.isArray( a.items ) ? a.items : data.features || [],
							fields: [
								[ 'title', __( 'Title', 'envo-blocks' ) ],
								[ 'text', __( 'Text', 'envo-blocks' ) ],
								[ 'url', __( 'Link (optional)', 'envo-blocks' ), 'url' ],
							],
							addLabel: __( 'Add item', 'envo-blocks' ),
							onChange: function ( v ) { config.setAttributes( { items: v } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Layout', 'envo-blocks' ), initialOpen: false },
						f.select( 'layout', __( 'Icon position', 'envo-blocks' ), [
							{ value: 'side', label: __( 'Next to the text', 'envo-blocks' ) },
							{ value: 'top', label: __( 'Above the text', 'envo-blocks' ) },
						] ),
						f.select( 'contentAlign', __( 'Alignment', 'envo-blocks' ), ALIGN( false ) ),
						f.range( 'columns', __( 'Columns', 'envo-blocks' ), 1, 6 ),
						f.range( 'columnsMobile', __( 'Columns on mobile', 'envo-blocks' ), 1, 3 ),
						f.toggle( 'dividers', __( 'Dividers between items', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Icons', 'envo-blocks' ), initialOpen: false },
						f.select( 'iconStyle', __( 'Icon style', 'envo-blocks' ), [
							{ value: 'plain', label: __( 'Plain', 'envo-blocks' ) },
							{ value: 'circle', label: __( 'In a circle', 'envo-blocks' ) },
							{ value: 'square', label: __( 'In a rounded square', 'envo-blocks' ) },
						] ),
						f.range( 'iconSize', __( 'Icon size (px)', 'envo-blocks' ), 16, 96 )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'iconColor', __( 'Icons', 'envo-blocks' ) ],
					[ 'iconBackground', __( 'Icon background', 'envo-blocks' ) ],
					[ 'titleColor', __( 'Title', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'dividerColor', __( 'Dividers', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/features-bar', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Delivery Estimate */

	registerBlockType( 'envo-blocks/delivery-estimate', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Shipping', 'envo-blocks' ), initialOpen: true },
						el( TextControl, props( {
							label: __( 'Cut-off time for same-day shipping', 'envo-blocks' ),
							help: __( 'In the time zone of the site (Settings > General).', 'envo-blocks' ),
							type: 'time',
							value: a.cutoff,
							onChange: function ( v ) { config.setAttributes( { cutoff: v } ); },
						} ) ),
						el( DaysControl, { label: __( 'Shipping days', 'envo-blocks' ), value: a.shipDays, onChange: function ( v ) { config.setAttributes( { shipDays: v } ); } } ),
						f.range( 'processingDays', __( 'Extra processing days', 'envo-blocks' ), 0, 14, __( 'Shipping days needed before the order leaves (0 = ships the same day).', 'envo-blocks' ) ),
						el( TextareaControl, props( {
							label: __( 'Holidays (no shipping and no delivery)', 'envo-blocks' ),
							help: __( 'One date per line: 2026-12-24 for a single day or 12-25 for every year.', 'envo-blocks' ),
							value: a.holidays,
							rows: 3,
							onChange: function ( v ) { config.setAttributes( { holidays: v } ); },
						} ) ),
						f.toggle( 'hideOutOfStock', __( 'Hide on products that are out of stock', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Delivery', 'envo-blocks' ), initialOpen: false },
						f.toggle( 'showDelivery', __( 'Show estimated delivery', 'envo-blocks' ) ),
						a.showDelivery
							? el(
									Fragment,
									null,
									f.range( 'deliveryMin', __( 'Delivery takes at least (days)', 'envo-blocks' ), 0, 30 ),
									f.range( 'deliveryMax', __( 'Delivery takes at most (days)', 'envo-blocks' ), 0, 30 ),
									el( DaysControl, { label: __( 'Delivery days of the carrier', 'envo-blocks' ), value: a.deliveryDays, onChange: function ( v ) { config.setAttributes( { deliveryDays: v } ); } } )
							  )
							: null
					),
					el(
						PanelBody,
						{ title: __( 'Messages', 'envo-blocks' ), initialOpen: false },
						el( 'p', { className: 'components-base-control__help' }, __( 'Leave empty for the default text. Placeholders: {countdown}, {date}, {delivery}.', 'envo-blocks' ) ),
						f.text( 'messageToday', __( 'Before the cut-off', 'envo-blocks' ), undefined, __( 'Order within {countdown} and we ship it today.', 'envo-blocks' ) ),
						f.text( 'messageLater', __( 'After the cut-off', 'envo-blocks' ), undefined, __( 'Order now and we ship it {date}.', 'envo-blocks' ) ),
						f.text( 'messageDelivery', __( 'Delivery', 'envo-blocks' ), undefined, __( 'Estimated delivery: {delivery}', 'envo-blocks' ) ),
						f.toggle( 'showSeconds', __( 'Show seconds in the countdown', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Look', 'envo-blocks' ), initialOpen: false },
						f.select( 'boxStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'box', label: __( 'Box with background', 'envo-blocks' ) },
							{ value: 'line', label: __( 'Lines above and below', 'envo-blocks' ) },
							{ value: 'plain', label: __( 'Plain text', 'envo-blocks' ) },
						] ),
						f.toggle( 'showIcon', __( 'Show icons', 'envo-blocks' ) )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'highlightColor', __( 'Countdown and dates', 'envo-blocks' ) ],
					[ 'iconColor', __( 'Icons', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/delivery-estimate', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Size Guide */

	var SIZE_TEMPLATE = [
		[ 'core/paragraph', { content: __( 'Measure yourself and compare the values with the table. If you are between two sizes, choose the bigger one.', 'envo-blocks' ) } ],
		[
			'core/table',
			{
				hasFixedLayout: true,
				head: [ { cells: [ __( 'Size', 'envo-blocks' ), __( 'Chest (cm)', 'envo-blocks' ), __( 'Waist (cm)', 'envo-blocks' ), __( 'Hips (cm)', 'envo-blocks' ) ].map( function ( t ) { return { content: t, tag: 'th' }; } ) } ],
				body: [
					[ 'S', '86–91', '71–76', '86–91' ],
					[ 'M', '91–97', '76–81', '91–97' ],
					[ 'L', '97–102', '81–86', '97–102' ],
					[ 'XL', '102–107', '86–91', '102–107' ],
				].map( function ( row ) {
					return { cells: row.map( function ( t ) { return { content: t, tag: 'td' }; } ) };
				} ),
			},
		],
	];

	registerBlockType( 'envo-blocks/size-guide', Object.assign( {}, STYLE_ATTRS, {
		template: SIZE_TEMPLATE,
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var blockProps = useBlockProps( { className: 'sb-size sb-size--editor sb-align-' + ( a.contentAlign || 'left' ) } );
			var innerProps = useInnerBlocksProps( { className: 'sb-size__body' }, { template: innerTemplate( SIZE_TEMPLATE ) } );
			var text = a.buttonText || __( 'Size guide', 'envo-blocks' );
			var style = a.buttonStyle || 'link';

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Button', 'envo-blocks' ), initialOpen: true },
						f.text( 'buttonText', __( 'Button text', 'envo-blocks' ), undefined, __( 'Size guide', 'envo-blocks' ) ),
						f.select( 'buttonStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'link', label: __( 'Text link', 'envo-blocks' ) },
							{ value: 'button', label: __( 'Button (theme style)', 'envo-blocks' ) },
							{ value: 'outline', label: __( 'Outline button', 'envo-blocks' ) },
						] ),
						f.toggle( 'showIcon', __( 'Ruler icon', 'envo-blocks' ) ),
						f.select( 'contentAlign', __( 'Alignment', 'envo-blocks' ), ALIGN( true ) )
					),
					el(
						PanelBody,
						{ title: __( 'Window', 'envo-blocks' ), initialOpen: false },
						f.text( 'modalTitle', __( 'Window title', 'envo-blocks' ), __( 'Empty = the button text.', 'envo-blocks' ) ),
						f.range( 'modalWidth', __( 'Window width (px)', 'envo-blocks' ), 320, 1400 )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'buttonColor', __( 'Button / link', 'envo-blocks' ) ],
					[ 'buttonTextColor', __( 'Button text', 'envo-blocks' ) ],
					[ 'modalBackground', __( 'Window background', 'envo-blocks' ) ],
					[ 'modalText', __( 'Window text', 'envo-blocks' ) ],
				] ),
				el(
					'div',
					blockProps,
					el(
						'span',
						{ className: 'sb-size__open sb-size__open--' + style + ( 'link' === style ? '' : ' wp-element-button' ), style: { color: a.buttonColor && 'button' !== style ? a.buttonColor : undefined, background: a.buttonColor && 'button' === style ? a.buttonColor : undefined } },
						text
					),
					el(
						'div',
						{ className: 'sb-size__editor-panel' },
						el( 'p', { className: 'sb-size__editor-label' }, ( a.modalTitle || text ) + ' — ' + __( 'content of the pop-up window', 'envo-blocks' ) ),
						el( 'div', innerProps )
					)
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} ) );

	/* Pricing Table */

	function PlanEditor( config ) {
		var plan = config.plan;

		function change( key, value ) {
			var next = Object.assign( {}, plan );
			next[ key ] = value;
			config.onChange( next );
		}

		function text( key, label, help, placeholder, type ) {
			return el( TextControl, props( { label: label, help: help, placeholder: placeholder, type: type || 'text', value: plan[ key ] || '', onChange: function ( v ) { change( key, v ); } } ) );
		}

		return el(
			PanelBody,
			{ title: ( plan.name || __( 'Plan', 'envo-blocks' ) ) + ( plan.featured ? ' ★' : '' ), initialOpen: false },
			text( 'name', __( 'Name', 'envo-blocks' ) ),
			text( 'description', __( 'Short description', 'envo-blocks' ) ),
			text( 'badge', __( 'Badge', 'envo-blocks' ), undefined, __( 'e.g. Most popular', 'envo-blocks' ) ),
			el( ToggleControl, props( { label: __( 'Highlight this plan', 'envo-blocks' ), checked: !! plan.featured, onChange: function ( v ) { change( 'featured', v ); } } ) ),
			el(
				'div',
				{ className: 'sb-ctl sb-price-row' },
				text( 'price', __( 'Price', 'envo-blocks' ), undefined, '19' ),
				text( 'oldPrice', __( 'Old price (crossed out)', 'envo-blocks' ), undefined, '29' )
			),
			text( 'period', __( 'Billing period', 'envo-blocks' ), __( 'Any text, e.g. "/ month", "per year", "one-time payment".', 'envo-blocks' ), __( '/ month', 'envo-blocks' ) ),
			text( 'note', __( 'Note under the price', 'envo-blocks' ), undefined, __( 'e.g. Billed yearly', 'envo-blocks' ) ),
			el( TextareaControl, props( {
				label: __( 'Features', 'envo-blocks' ),
				help: __( 'One per line. Start a line with "-" for a feature that is not included.', 'envo-blocks' ),
				rows: 6,
				value: plan.features || '',
				onChange: function ( v ) { change( 'features', v ); },
			} ) ),
			text( 'buttonText', __( 'Button text', 'envo-blocks' ), __( 'Empty = no button.', 'envo-blocks' ) ),
			text( 'buttonUrl', __( 'Button link', 'envo-blocks' ), undefined, 'https://', 'url' ),
			el( ToggleControl, props( { label: __( 'Open the link in a new tab', 'envo-blocks' ), checked: !! plan.newTab, onChange: function ( v ) { change( 'newTab', v ); } } ) ),
			el(
				'div',
				{ className: 'sb-repeater__tools' },
				el( Button, { size: 'small', variant: 'secondary', disabled: config.first, label: __( 'Move up', 'envo-blocks' ), onClick: function () { config.onMove( -1 ); } }, '↑' ),
				el( Button, { size: 'small', variant: 'secondary', disabled: config.last, label: __( 'Move down', 'envo-blocks' ), onClick: function () { config.onMove( 1 ); } }, '↓' ),
				el( Button, { size: 'small', variant: 'secondary', onClick: config.onDuplicate }, __( 'Duplicate', 'envo-blocks' ) ),
				el( Button, { size: 'small', variant: 'link', isDestructive: true, onClick: config.onRemove }, __( 'Remove', 'envo-blocks' ) )
			)
		);
	}

	registerBlockType( 'envo-blocks/pricing-table', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var plans = Array.isArray( a.plans ) ? a.plans : data.plans || [];

			function setPlans( next ) {
				config.setAttributes( { plans: next } );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					plans.map( function ( plan, index ) {
						return el( PlanEditor, {
							key: index,
							plan: plan,
							first: 0 === index,
							last: index === plans.length - 1,
							onChange: function ( next ) {
								var list = plans.slice();
								list[ index ] = next;
								setPlans( list );
							},
							onMove: function ( step ) {
								var list = plans.slice();
								var moved = list.splice( index, 1 )[ 0 ];
								list.splice( index + step, 0, moved );
								setPlans( list );
							},
							onDuplicate: function () {
								var list = plans.slice();
								list.splice( index + 1, 0, Object.assign( {}, plan, { featured: false, badge: '' } ) );
								setPlans( list );
							},
							onRemove: function () {
								setPlans( plans.filter( function ( p, i ) { return i !== index; } ) );
							},
						} );
					} ),
					el(
						PanelBody,
						{ title: __( 'Add plan', 'envo-blocks' ), initialOpen: true },
						el( Button, {
							variant: 'secondary',
							onClick: function () {
								setPlans( plans.concat( [ { name: __( 'New plan', 'envo-blocks' ), price: '0', period: __( '/ month', 'envo-blocks' ), buttonText: __( 'Get started', 'envo-blocks' ), buttonUrl: '#' } ] ) );
							},
						}, __( 'Add plan', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Prices', 'envo-blocks' ), initialOpen: false },
						f.text( 'currency', __( 'Currency symbol', 'envo-blocks' ), __( 'e.g. $, €, Kč. Empty = no symbol.', 'envo-blocks' ) ),
						f.select( 'currencyPosition', __( 'Currency position', 'envo-blocks' ), [
							{ value: 'before', label: __( 'Before the price ($19)', 'envo-blocks' ) },
							{ value: 'after', label: __( 'After the price (19 €)', 'envo-blocks' ) },
						] ),
						f.toggle( 'showSavings', __( 'Show the discount in percent', 'envo-blocks' ), __( 'Shown when a plan has an old price higher than the price.', 'envo-blocks' ) ),
						a.showSavings
							? f.text(
									'savingsText',
									__( 'Discount text', 'envo-blocks' ),
									/* translators: Help text; %s is a literal placeholder the user types, not replaced here. */
									__( '%s is replaced by the percentage.', 'envo-blocks' ),
									/* translators: %s: discount in percent, e.g. 25% */
									__( 'Save %s', 'envo-blocks' )
							  )
							: null,
						f.range( 'priceSize', __( 'Price size (px)', 'envo-blocks' ), 20, 96 )
					),
					el(
						PanelBody,
						{ title: __( 'Layout', 'envo-blocks' ), initialOpen: false },
						f.range( 'columns', __( 'Columns', 'envo-blocks' ), 1, 5 ),
						f.range( 'gap', __( 'Space between plans (px)', 'envo-blocks' ), 0, 80 ),
						f.select( 'contentAlign', __( 'Alignment', 'envo-blocks' ), ALIGN( false ) ),
						f.select( 'cardStyle', __( 'Card style', 'envo-blocks' ), [
							{ value: 'border', label: __( 'Border', 'envo-blocks' ) },
							{ value: 'shadow', label: __( 'Shadow', 'envo-blocks' ) },
							{ value: 'flat', label: __( 'Flat background', 'envo-blocks' ) },
						] ),
						f.range( 'radius', __( 'Corner radius (px)', 'envo-blocks' ), 0, 48 ),
						f.toggle( 'featuredScale', __( 'Make the highlighted plan bigger', 'envo-blocks' ) ),
						f.toggle( 'buttonFullWidth', __( 'Full-width buttons', 'envo-blocks' ) )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'accentColor', __( 'Accent (highlight, checks, buttons)', 'envo-blocks' ) ],
					[ 'cardBackground', __( 'Card background', 'envo-blocks' ) ],
					[ 'textColor', __( 'Card text', 'envo-blocks' ) ],
					[ 'borderColor', __( 'Card border', 'envo-blocks' ) ],
					[ 'featuredBackground', __( 'Highlighted card background', 'envo-blocks' ) ],
					[ 'featuredText', __( 'Highlighted card text', 'envo-blocks' ) ],
					[ 'oldPriceColor', __( 'Old price', 'envo-blocks' ) ],
					[ 'badgeBackground', __( 'Badge background', 'envo-blocks' ) ],
					[ 'badgeText', __( 'Badge text', 'envo-blocks' ) ],
					[ 'buttonBackground', __( 'Button background', 'envo-blocks' ) ],
					[ 'buttonText', __( 'Button text', 'envo-blocks' ) ],
					[ 'checkColor', __( 'Check marks', 'envo-blocks' ) ],
					[ 'savingsColor', __( 'Discount label', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/pricing-table', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Advanced Heading */

	var HEADING_DEFAULTS = data.heading || { before: '', words: '', after: '' };

	function fontOptions() {
		var options = [ { value: '', label: __( 'Default (theme)', 'envo-blocks' ) } ];
		Object.keys( data.fonts || {} ).forEach( function ( slug ) {
			options.push( { value: slug, label: data.fonts[ slug ] } );
		} );
		return options;
	}

	/** Typography, border and padding of one part; key is "before", "middle" or "after". */
	function PartPanel( config ) {
		var part = config.attributes[ config.partKey ] || {};

		function change( key, value ) {
			var next = Object.assign( {}, part );
			if ( undefined === value || '' === value ) {
				delete next[ key ];
			} else {
				next[ key ] = value;
			}
			var update = {};
			update[ config.partKey ] = next;
			config.setAttributes( update );
		}

		function select( key, label, options ) {
			return el( SelectControl, props( { label: label, value: part[ key ] || '', options: options, onChange: function ( v ) { change( key, v ); } } ) );
		}

		function text( key, label, help, placeholder ) {
			return el( TextControl, props( { label: label, help: help, placeholder: placeholder, value: part[ key ] || '', onChange: function ( v ) { change( key, v ); } } ) );
		}

		function range( key, label, min, max ) {
			return el( RangeControl, props( { label: label, value: part[ key ], min: min, max: max, allowReset: true, onChange: function ( v ) { change( key, v ); } } ) );
		}

		return el(
			PanelBody,
			{ title: config.title, initialOpen: false },
			select( 'fontFamily', __( 'Font', 'envo-blocks' ), fontOptions() ),
			el(
				'div',
				{ className: 'sb-ctl sb-price-row' },
				text( 'fontSize', __( 'Size', 'envo-blocks' ), undefined, '48px' ),
				text( 'fontSizeMobile', __( 'Size on mobile', 'envo-blocks' ), undefined, '32px' )
			),
			el( 'p', { className: 'components-base-control__help sb-help-tight' }, __( 'Sizes: 48, 48px, 3rem, 4vw… Empty = the size of the heading.', 'envo-blocks' ) ),
			select( 'fontWeight', __( 'Weight', 'envo-blocks' ), [
				{ value: '', label: __( 'Default', 'envo-blocks' ) },
				{ value: '300', label: __( 'Light', 'envo-blocks' ) },
				{ value: '400', label: __( 'Normal', 'envo-blocks' ) },
				{ value: '500', label: __( 'Medium', 'envo-blocks' ) },
				{ value: '600', label: __( 'Semi bold', 'envo-blocks' ) },
				{ value: '700', label: __( 'Bold', 'envo-blocks' ) },
				{ value: '800', label: __( 'Extra bold', 'envo-blocks' ) },
				{ value: '900', label: __( 'Black', 'envo-blocks' ) },
			] ),
			el(
				'div',
				{ className: 'sb-ctl sb-price-row' },
				select( 'fontStyle', __( 'Style', 'envo-blocks' ), [
					{ value: '', label: __( 'Default', 'envo-blocks' ) },
					{ value: 'normal', label: __( 'Normal', 'envo-blocks' ) },
					{ value: 'italic', label: __( 'Italic', 'envo-blocks' ) },
				] ),
				select( 'textTransform', __( 'Letter case', 'envo-blocks' ), [
					{ value: '', label: __( 'Default', 'envo-blocks' ) },
					{ value: 'uppercase', label: __( 'UPPERCASE', 'envo-blocks' ) },
					{ value: 'lowercase', label: __( 'lowercase', 'envo-blocks' ) },
					{ value: 'capitalize', label: __( 'Capitalize', 'envo-blocks' ) },
				] )
			),
			el(
				'div',
				{ className: 'sb-ctl sb-price-row' },
				text( 'letterSpacing', __( 'Letter spacing', 'envo-blocks' ), undefined, '0.02em' ),
				text( 'lineHeight', __( 'Line height', 'envo-blocks' ), undefined, '1.2' )
			),
			range( 'borderWidth', __( 'Border width (px)', 'envo-blocks' ), 0, 20 ),
			part.borderWidth
				? select( 'borderStyle', __( 'Border style', 'envo-blocks' ), [
						{ value: 'solid', label: __( 'Solid', 'envo-blocks' ) },
						{ value: 'dashed', label: __( 'Dashed', 'envo-blocks' ) },
						{ value: 'dotted', label: __( 'Dotted', 'envo-blocks' ) },
						{ value: 'double', label: __( 'Double', 'envo-blocks' ) },
				  ] )
				: null,
			range( 'borderRadius', __( 'Corner radius (px)', 'envo-blocks' ), 0, 100 ),
			range( 'paddingY', __( 'Padding top and bottom (px)', 'envo-blocks' ), 0, 80 ),
			range( 'paddingX', __( 'Padding left and right (px)', 'envo-blocks' ), 0, 120 )
		);
	}

	/** Colors of one part in the Styles tab. */
	function partColors( attributes, setAttributes, partKey, title, extra ) {
		var part = attributes[ partKey ] || {};

		function setter( key ) {
			return function ( value ) {
				var next = Object.assign( {}, part );
				if ( value ) {
					next[ key ] = value;
				} else {
					delete next[ key ];
				}
				var update = {};
				update[ partKey ] = next;
				setAttributes( update );
			};
		}

		var settings = [
			{ label: __( 'Text', 'envo-blocks' ), value: part.color, onChange: setter( 'color' ), enableAlpha: true },
			{ label: __( 'Background', 'envo-blocks' ), value: part.background, onChange: setter( 'background' ), enableAlpha: true },
			{ label: __( 'Border', 'envo-blocks' ), value: part.borderColor, onChange: setter( 'borderColor' ), enableAlpha: true },
		].concat( extra || [] );

		return el( PanelColorSettings, { title: title, initialOpen: false, colorSettings: settings } );
	}

	registerBlockType( 'envo-blocks/advanced-heading', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var set = config.setAttributes;
			var f = makeFields( a, set );

			function lineBefore( key ) {
				var part = a[ key ] || {};
				return el( SelectControl, props( {
					label: 'middle' === key ? __( 'Middle part starts on a new line', 'envo-blocks' ) : __( 'Last part starts on a new line', 'envo-blocks' ),
					value: part.lineBefore || '',
					options: [
						{ value: '', label: __( 'No', 'envo-blocks' ) },
						{ value: 'always', label: __( 'Yes', 'envo-blocks' ) },
						{ value: 'desktop', label: __( 'Only on desktop', 'envo-blocks' ) },
						{ value: 'mobile', label: __( 'Only on mobile', 'envo-blocks' ) },
					],
					onChange: function ( v ) {
						var next = Object.assign( {}, part );
						if ( v ) {
							next.lineBefore = v;
						} else {
							delete next.lineBefore;
						}
						var update = {};
						update[ key ] = next;
						set( update );
					},
				} ) );
			}

			function partText( key ) {
				var part = a[ key ] || {};
				return el( TextareaControl, props( {
					label: 'before' === key ? __( 'First part', 'envo-blocks' ) : __( 'Last part', 'envo-blocks' ),
					help: __( 'Press Enter for a line break.', 'envo-blocks' ),
					rows: 2,
					value: Object.prototype.hasOwnProperty.call( part, 'text' ) ? part.text : HEADING_DEFAULTS[ key ],
					onChange: function ( v ) {
						var update = {};
						update[ key ] = Object.assign( {}, part, { text: v } );
						set( update );
					},
				} ) );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Texts', 'envo-blocks' ), initialOpen: true },
						partText( 'before' ),
						el( TextareaControl, props( {
							label: __( 'Middle part', 'envo-blocks' ),
							help: __( 'One text per line. With more lines the texts change with the animation below.', 'envo-blocks' ),
							rows: 4,
							value: 'string' === typeof a.words ? a.words : HEADING_DEFAULTS.words,
							onChange: function ( v ) { set( { words: v } ); },
						} ) ),
						'stacked' !== a.layout ? lineBefore( 'middle' ) : null,
						partText( 'after' ),
						'stacked' !== a.layout ? lineBefore( 'after' ) : null
					),
					el(
						PanelBody,
						{ title: __( 'Animation of the middle part', 'envo-blocks' ), initialOpen: true },
						f.select( 'animation', __( 'Animation', 'envo-blocks' ), [
							{ value: 'none', label: __( 'None', 'envo-blocks' ) },
							{ value: 'slide-up', label: __( 'Slide up', 'envo-blocks' ) },
							{ value: 'slide-down', label: __( 'Slide down', 'envo-blocks' ) },
							{ value: 'fade', label: __( 'Fade', 'envo-blocks' ) },
							{ value: 'zoom', label: __( 'Zoom', 'envo-blocks' ) },
							{ value: 'flip', label: __( 'Flip', 'envo-blocks' ) },
							{ value: 'blur', label: __( 'Blur', 'envo-blocks' ) },
							{ value: 'typing', label: __( 'Typing', 'envo-blocks' ) },
						] ),
						'none' !== a.animation ? f.range( 'interval', __( 'Time per text (ms)', 'envo-blocks' ), 800, 10000 ) : null,
						'none' !== a.animation ? f.toggle( 'loop', __( 'Repeat', 'envo-blocks' ), __( 'Off = stops on the last text.', 'envo-blocks' ) ) : null,
						f.select( 'decoration', __( 'Decoration', 'envo-blocks' ), [
							{ value: 'none', label: __( 'None', 'envo-blocks' ) },
							{ value: 'highlight', label: __( 'Marker highlight', 'envo-blocks' ) },
							{ value: 'underline', label: __( 'Underline', 'envo-blocks' ) },
						] ),
						el( 'p', { className: 'components-base-control__help' }, __( 'The animation runs on the site, the preview here shows the first text.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Heading', 'envo-blocks' ), initialOpen: false },
						f.select( 'tag', __( 'HTML tag', 'envo-blocks' ), [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ].map( function ( t ) { return { value: t, label: t.toUpperCase() }; } ) ),
						f.select( 'textAlign', __( 'Alignment', 'envo-blocks' ), ALIGN( true ) ),
						f.select( 'layout', __( 'Layout', 'envo-blocks' ), [
							{ value: 'inline', label: __( 'In one line (wraps when needed)', 'envo-blocks' ) },
							{ value: 'stacked', label: __( 'Each part on its own line', 'envo-blocks' ) },
						] ),
						f.text( 'linkUrl', __( 'Link (optional)', 'envo-blocks' ) )
					),
					el( PartPanel, { title: __( 'First part: typography and border', 'envo-blocks' ), partKey: 'before', attributes: a, setAttributes: set } ),
					el( PartPanel, { title: __( 'Middle part: typography and border', 'envo-blocks' ), partKey: 'middle', attributes: a, setAttributes: set } ),
					el( PartPanel, { title: __( 'Last part: typography and border', 'envo-blocks' ), partKey: 'after', attributes: a, setAttributes: set } )
				),
				el(
					InspectorControls,
					{ group: 'styles' },
					partColors( a, set, 'before', __( 'First part colors', 'envo-blocks' ) ),
					partColors( a, set, 'middle', __( 'Middle part colors', 'envo-blocks' ), [
						{ label: __( 'Decoration', 'envo-blocks' ), value: a.decorationColor || undefined, onChange: function ( v ) { set( { decorationColor: v || '' } ); }, enableAlpha: true },
					] ),
					partColors( a, set, 'after', __( 'Last part colors', 'envo-blocks' ) )
				),
				preview( 'envo-blocks/advanced-heading', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Video Popup */

	var RATIOS = function () {
		return [
			{ value: '16/9', label: __( 'Wide 16:9', 'envo-blocks' ) },
			{ value: '21/9', label: __( 'Cinema 21:9', 'envo-blocks' ) },
			{ value: '4/3', label: __( 'Classic 4:3', 'envo-blocks' ) },
			{ value: '1/1', label: __( 'Square 1:1', 'envo-blocks' ) },
			{ value: '3/4', label: __( 'Portrait 3:4', 'envo-blocks' ) },
			{ value: '9/16', label: __( 'Vertical 9:16 (Shorts, Reels)', 'envo-blocks' ) },
		];
	};

	function mediaButton( allowed, value, label, replaceLabel, onSelect ) {
		var MediaUpload = wp.blockEditor.MediaUpload;
		var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
		return el(
			MediaUploadCheck,
			null,
			el( MediaUpload, {
				allowedTypes: allowed,
				value: value || undefined,
				onSelect: onSelect,
				render: function ( rc ) {
					return el( Button, { variant: 'secondary', onClick: rc.open, className: 'sb-ctl' }, value ? replaceLabel : label );
				},
			} )
		);
	}

	registerBlockType( 'envo-blocks/video-popup', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var set = config.setAttributes;
			var f = makeFields( a, set );
			var image = 'image' === a.layout;

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Video', 'envo-blocks' ), initialOpen: true },
						f.text( 'videoUrl', __( 'Video address', 'envo-blocks' ), __( 'A YouTube, Vimeo or Dailymotion link, or a link to an mp4 / webm file.', 'envo-blocks' ), 'https://www.youtube.com/watch?v=…' ),
						mediaButton( [ 'video' ], 0, __( 'Or choose a video file', 'envo-blocks' ), '', function ( media ) {
							set( { videoUrl: media && media.url ? media.url : '' } );
						} ),
						f.text( 'videoTitle', __( 'Video title', 'envo-blocks' ), __( 'For screen readers and the window. Empty = "Video".', 'envo-blocks' ) ),
						el( TextControl, props( {
							label: __( 'Start at (seconds)', 'envo-blocks' ),
							type: 'number',
							min: 0,
							value: a.startAt || '',
							onChange: function ( v ) { set( { startAt: Math.max( 0, parseInt( v, 10 ) || 0 ) } ); },
						} ) )
					),
					el(
						PanelBody,
						{ title: __( 'Layout', 'envo-blocks' ), initialOpen: true },
						f.select( 'layout', __( 'Show', 'envo-blocks' ), [
							{ value: 'image', label: __( 'Image with a play button', 'envo-blocks' ) },
							{ value: 'button', label: __( 'Only the play button', 'envo-blocks' ) },
						] ),
						image
							? el(
									Fragment,
									null,
									mediaButton( [ 'image' ], a.imageId, __( 'Choose image', 'envo-blocks' ), __( 'Replace image', 'envo-blocks' ), function ( media ) {
										set( { imageId: media && media.id ? media.id : 0, imageUrl: media && media.url ? media.url : '', imageAlt: media && media.alt ? media.alt : a.imageAlt } );
									} ),
									a.imageId || a.imageUrl
										? el( Button, { variant: 'link', isDestructive: true, className: 'sb-ctl', onClick: function () { set( { imageId: 0, imageUrl: '' } ); } }, __( 'Remove image', 'envo-blocks' ) )
										: el( 'p', { className: 'components-base-control__help' }, __( 'Without an image a colored background is shown.', 'envo-blocks' ) ),
									f.text( 'imageAlt', __( 'Image alt text', 'envo-blocks' ) ),
									f.select( 'aspectRatio', __( 'Image ratio', 'envo-blocks' ), RATIOS() ),
									f.range( 'radius', __( 'Corner radius (px)', 'envo-blocks' ), 0, 60 ),
									f.toggle( 'hoverZoom', __( 'Zoom the image on hover', 'envo-blocks' ) ),
									f.text( 'caption', __( 'Caption at the bottom (optional)', 'envo-blocks' ) )
							  )
							: null,
						f.text( 'label', __( 'Text next to the button (optional)', 'envo-blocks' ), undefined, __( 'Watch the video', 'envo-blocks' ) ),
						! image && a.label
							? f.select( 'labelPosition', __( 'Text position', 'envo-blocks' ), [
									{ value: 'right', label: __( 'Next to the button', 'envo-blocks' ) },
									{ value: 'below', label: __( 'Below the button', 'envo-blocks' ) },
							  ] )
							: null,
						f.select( 'contentAlign', image ? __( 'Button position', 'envo-blocks' ) : __( 'Alignment', 'envo-blocks' ), image
							? [
									{ value: 'center', label: __( 'Center', 'envo-blocks' ) },
									{ value: 'left', label: __( 'Bottom left', 'envo-blocks' ) },
									{ value: 'right', label: __( 'Bottom right', 'envo-blocks' ) },
							  ]
							: ALIGN( true ) )
					),
					el(
						PanelBody,
						{ title: __( 'Play button', 'envo-blocks' ), initialOpen: false },
						f.select( 'iconStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'filled', label: __( 'Filled circle', 'envo-blocks' ) },
							{ value: 'outline', label: __( 'Outline circle', 'envo-blocks' ) },
							{ value: 'glass', label: __( 'Frosted glass', 'envo-blocks' ) },
							{ value: 'square', label: __( 'Rounded square', 'envo-blocks' ) },
						] ),
						f.range( 'iconSize', __( 'Size (px)', 'envo-blocks' ), 32, 200 ),
						f.toggle( 'pulse', __( 'Pulsing rings', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Pop-up window', 'envo-blocks' ), initialOpen: false },
						f.range( 'popupWidth', __( 'Maximum width (px)', 'envo-blocks' ), 320, 1800 ),
						f.select( 'popupRatio', __( 'Video ratio', 'envo-blocks' ), RATIOS() ),
						el( 'p', { className: 'components-base-control__help' }, __( 'The video opens on the site; in the editor the button does nothing.', 'envo-blocks' ) )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'iconBackground', __( 'Play button', 'envo-blocks' ) ],
					[ 'iconColor', __( 'Play icon', 'envo-blocks' ) ],
					[ 'labelColor', __( 'Text', 'envo-blocks' ) ],
					[ 'overlayColor', __( 'Image overlay', 'envo-blocks' ) ],
					[ 'backdropColor', __( 'Window background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/video-popup', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	if ( ! data.woo ) {
		return;
	}

	/* Coupon Code Box */

	function CouponSuggestions( config ) {
		var state = useState( null );

		useEffect( function () {
			apiFetch( { path: '/wc/v3/coupons?per_page=100&status=publish' } )
				.then( function ( list ) {
					state[ 1 ]( Array.isArray( list ) ? list : [] );
				} )
				.catch( function () {
					state[ 1 ]( [] );
				} );
		}, [] );

		if ( ! state[ 0 ] || ! state[ 0 ].length ) {
			return null;
		}

		return el( SelectControl, props( {
			label: __( 'Or pick a coupon', 'envo-blocks' ),
			value: '',
			options: [ { value: '', label: __( '— Choose —', 'envo-blocks' ) } ].concat(
				state[ 0 ].map( function ( c ) {
					return { value: c.code, label: c.code + ( c.amount ? ' (' + c.amount + ( 'percent' === c.discount_type ? ' %' : '' ) + ')' : '' ) };
				} )
			),
			onChange: function ( v ) {
				if ( v ) {
					config.onChange( v );
				}
			},
		} ) );
	}

	registerBlockType( 'envo-blocks/coupon-box', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Coupon', 'envo-blocks' ), initialOpen: true },
						f.text( 'code', __( 'Coupon code', 'envo-blocks' ), __( 'A coupon from Marketing > Coupons. The discount and dates are read from it.', 'envo-blocks' ) ),
						el( CouponSuggestions, { onChange: function ( v ) { config.setAttributes( { code: v } ); } } ),
						f.toggle( 'hideInvalid', __( 'Hide when the coupon is expired or used up', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Texts', 'envo-blocks' ), initialOpen: false },
						f.text( 'title', __( 'Title', 'envo-blocks' ), __( 'Empty = the discount, e.g. "20% off".', 'envo-blocks' ) ),
						f.text( 'description', __( 'Description', 'envo-blocks' ), __( 'Empty = the description of the coupon.', 'envo-blocks' ) ),
						f.toggle( 'showDiscount', __( 'Use the discount as the default title', 'envo-blocks' ) ),
						f.toggle( 'showMinimum', __( 'Show the minimum order amount', 'envo-blocks' ) ),
						f.toggle( 'showExpiry', __( 'Show the expiry date', 'envo-blocks' ) ),
						f.text( 'copyText', __( 'Copy button', 'envo-blocks' ), undefined, __( 'Copy code', 'envo-blocks' ) ),
						f.text( 'copiedText', __( 'After copying', 'envo-blocks' ), undefined, __( 'Copied!', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Apply to cart', 'envo-blocks' ), initialOpen: false },
						f.toggle( 'applyButton', __( 'Show "Apply to cart" button', 'envo-blocks' ), __( 'Adds the coupon to the cart of the visitor without going to the cart page.', 'envo-blocks' ) ),
						a.applyButton ? f.text( 'applyText', __( 'Button text', 'envo-blocks' ), undefined, __( 'Apply to cart', 'envo-blocks' ) ) : null
					),
					el(
						PanelBody,
						{ title: __( 'Look', 'envo-blocks' ), initialOpen: false },
						f.select( 'boxStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'dashed', label: __( 'Dashed border', 'envo-blocks' ) },
							{ value: 'solid', label: __( 'Solid border', 'envo-blocks' ) },
							{ value: 'ticket', label: __( 'Ticket', 'envo-blocks' ) },
							{ value: 'filled', label: __( 'Filled with the accent color', 'envo-blocks' ) },
						] ),
						f.select( 'layout', __( 'Layout', 'envo-blocks' ), [
							{ value: 'row', label: __( 'Text and code side by side', 'envo-blocks' ) },
							{ value: 'stacked', label: __( 'Stacked, centered', 'envo-blocks' ) },
						] )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'accentColor', __( 'Accent (border, icon)', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'codeBackground', __( 'Code background', 'envo-blocks' ) ],
					[ 'buttonColor', __( 'Copy button', 'envo-blocks' ) ],
					[ 'buttonText', __( 'Copy button text', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/coupon-box', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Rating Summary */

	registerBlockType( 'envo-blocks/rating-summary', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Reviews', 'envo-blocks' ), initialOpen: true },
						f.select( 'source', __( 'Reviews of', 'envo-blocks' ), [
							{ value: 'current', label: __( 'The current product (product page)', 'envo-blocks' ) },
							{ value: 'product', label: __( 'A chosen product', 'envo-blocks' ) },
							{ value: 'store', label: __( 'All products of the shop', 'envo-blocks' ) },
						] ),
						'product' === a.source ? el( ProductPicker, { label: __( 'Product', 'envo-blocks' ), value: a.productId, onChange: function ( v ) { config.setAttributes( { productId: v } ); } } ) : null,
						f.text( 'heading', __( 'Heading (optional)', 'envo-blocks' ), undefined, __( 'Customer reviews', 'envo-blocks' ) ),
						f.toggle( 'hideEmpty', __( 'Hide when there are no reviews', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Content', 'envo-blocks' ), initialOpen: false },
						f.select( 'layout', __( 'Layout', 'envo-blocks' ), [
							{ value: 'side', label: __( 'Average next to the bars', 'envo-blocks' ) },
							{ value: 'stacked', label: __( 'Average above the bars', 'envo-blocks' ) },
						] ),
						f.toggle( 'showAverage', __( 'Average and stars', 'envo-blocks' ) ),
						f.toggle( 'showCount', __( 'Number of reviews', 'envo-blocks' ) ),
						f.toggle( 'showBars', __( 'Bars 5★ – 1★', 'envo-blocks' ) ),
						a.showBars
							? f.select( 'barValue', __( 'Value next to the bars', 'envo-blocks' ), [
									{ value: 'count', label: __( 'Number of reviews', 'envo-blocks' ) },
									{ value: 'percent', label: __( 'Percentage', 'envo-blocks' ) },
									{ value: 'none', label: __( 'Nothing', 'envo-blocks' ) },
							  ] )
							: null,
						a.showBars ? f.range( 'barHeight', __( 'Bar height (px)', 'envo-blocks' ), 2, 30 ) : null,
						'store' !== a.source ? f.toggle( 'showLink', __( 'Link to the reviews', 'envo-blocks' ) ) : null,
						'store' !== a.source && a.showLink ? f.text( 'linkText', __( 'Link text', 'envo-blocks' ), undefined, __( 'Read the reviews', 'envo-blocks' ) ) : null
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'starColor', __( 'Stars', 'envo-blocks' ) ],
					[ 'barColor', __( 'Bars', 'envo-blocks' ) ],
					[ 'trackColor', __( 'Bar track', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/rating-summary', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Customer Photos (review photos) */

	registerBlockType( 'envo-blocks/review-photos', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Photos', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'Customers add photos to their reviews (Envo Blocks › Reviews). Only photos of approved reviews show.', 'envo-blocks' ) ),
						f.select( 'source', __( 'Photos of', 'envo-blocks' ), [
							{ value: 'current', label: __( 'The current product (product page)', 'envo-blocks' ) },
							{ value: 'product', label: __( 'A chosen product', 'envo-blocks' ) },
							{ value: 'store', label: __( 'All products of the shop', 'envo-blocks' ) },
						] ),
						'product' === a.source ? el( ProductPicker, { label: __( 'Product', 'envo-blocks' ), value: a.productId, onChange: function ( v ) { config.setAttributes( { productId: v } ); } } ) : null,
						f.text( 'heading', __( 'Heading (optional)', 'envo-blocks' ), undefined, __( 'Customer photos', 'envo-blocks' ) ),
						f.range( 'limit', __( 'Most photos', 'envo-blocks' ), 1, 48 ),
						f.toggle( 'hideEmpty', __( 'Hide when there are no photos', 'envo-blocks' ) ),
						a.hideEmpty ? null : f.text( 'emptyText', __( 'Text without photos', 'envo-blocks' ), undefined, __( 'No customer photos yet. Add one with your review!', 'envo-blocks' ) ),
						data.pro ? null : proNote( __( 'Envo Blocks PRO asks your customers for a review by e-mail after the order and thanks for a review with a photo with a coupon.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Layout', 'envo-blocks' ), initialOpen: false },
						f.range( 'columns', __( 'Columns', 'envo-blocks' ), 1, 8 ),
						f.select( 'aspect', __( 'Photo shape', 'envo-blocks' ), [
							{ value: 'square', label: __( 'Square', 'envo-blocks' ) },
							{ value: 'portrait', label: __( 'Portrait (3:4)', 'envo-blocks' ) },
							{ value: 'landscape', label: __( 'Landscape (4:3)', 'envo-blocks' ) },
							{ value: 'natural', label: __( 'As uploaded', 'envo-blocks' ) },
						] ),
						f.range( 'radius', __( 'Rounded corners (px)', 'envo-blocks' ), 0, 40 ),
						f.toggle( 'showRating', __( 'Stars on the photos', 'envo-blocks' ) ),
						'store' === a.source ? f.toggle( 'showProduct', __( 'Link to the product in the photo window', 'envo-blocks' ) ) : null
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'starColor', __( 'Stars', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/review-photos', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Recently Viewed Products */

	registerBlockType( 'envo-blocks/recently-viewed', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Products', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'The preview shows the newest products. Visitors see the products they viewed last; with no history the block is hidden.', 'envo-blocks' ) ),
						f.toggle( 'showHeading', __( 'Show heading', 'envo-blocks' ) ),
						a.showHeading ? f.text( 'heading', __( 'Heading', 'envo-blocks' ), undefined, __( 'Recently viewed', 'envo-blocks' ) ) : null,
						f.range( 'limit', __( 'Number of products', 'envo-blocks' ), 1, 12 ),
						f.toggle( 'excludeCurrent', __( 'Leave out the current product', 'envo-blocks' ) ),
						f.text( 'emptyText', __( 'Text when the history is empty', 'envo-blocks' ), __( 'Empty = hide the block.', 'envo-blocks' ) ),
						f.toggle( 'showClear', __( '"Clear history" link', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Layout', 'envo-blocks' ), initialOpen: false },
						f.range( 'columns', __( 'Columns', 'envo-blocks' ), 1, 6 ),
						f.range( 'columnsMobile', __( 'Columns on mobile', 'envo-blocks' ), 1, 3 ),
						f.select( 'imageRatio', __( 'Image ratio', 'envo-blocks' ), [
							{ value: '1', label: __( 'Square', 'envo-blocks' ) },
							{ value: '3/4', label: __( 'Portrait 3:4', 'envo-blocks' ) },
							{ value: '2/3', label: __( 'Portrait 2:3', 'envo-blocks' ) },
							{ value: '4/3', label: __( 'Landscape 4:3', 'envo-blocks' ) },
							{ value: 'auto', label: __( 'Original', 'envo-blocks' ) },
						] ),
						f.toggle( 'showPrice', __( 'Price', 'envo-blocks' ) ),
						f.toggle( 'showRating', __( 'Rating', 'envo-blocks' ) ),
						f.toggle( 'showButton', __( 'Add to cart button', 'envo-blocks' ) )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'priceColor', __( 'Price', 'envo-blocks' ) ],
					[ 'cardBackground', __( 'Card background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/recently-viewed', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Map (the map as a background, any blocks on top) */

	/** Same address as Envo_Blocks_Blocks::map_url() in PHP. */
	function mapUrl( a ) {
		var zoom = Math.max( 1, Math.min( 20, parseInt( a.zoom, 10 ) || 14 ) );
		var lat = parseFloat( a.latitude );
		var lng = parseFloat( a.longitude );
		var point = ! isNaN( lat ) && ! isNaN( lng );
		var lang = ( document.documentElement.lang || 'en' ).slice( 0, 2 );

		if ( 'osm' === a.provider ) {
			if ( ! point ) {
				return '';
			}
			var halfLng = ( 360 / Math.pow( 2, zoom ) ) * 1.2;
			var halfLat = halfLng * 0.45;
			return 'https://www.openstreetmap.org/export/embed.html?bbox=' + encodeURIComponent( [ lng - halfLng, lat - halfLat, lng + halfLng, lat + halfLat ].map( function ( n ) { return n.toFixed( 6 ); } ).join( ',' ) ) +
				'&layer=mapnik&marker=' + encodeURIComponent( lat.toFixed( 6 ) + ',' + lng.toFixed( 6 ) );
		}

		var place = point ? lat.toFixed( 6 ) + ',' + lng.toFixed( 6 ) : ( a.address || '' ).trim();
		if ( ! place ) {
			return '';
		}
		var satellite = 'satellite' === a.mapType;
		var key = ( a.apiKey || '' ).replace( /[^A-Za-z0-9_\-]/g, '' );
		if ( key ) {
			return 'https://www.google.com/maps/embed/v1/place?key=' + key + '&q=' + encodeURIComponent( place ) + '&zoom=' + zoom + '&maptype=' + ( satellite ? 'satellite' : 'roadmap' ) + '&language=' + lang;
		}
		return 'https://maps.google.com/maps?q=' + encodeURIComponent( place ) + '&z=' + zoom + '&t=' + ( satellite ? 'k' : 'm' ) + '&hl=' + lang + '&output=embed';
	}

	var MAP_TEMPLATE = [
		[ 'core/heading', { level: 3, content: __( 'Visit our store', 'envo-blocks' ) } ],
		[ 'core/paragraph', { content: __( 'Main Street 12, Springfield', 'envo-blocks' ) } ],
		[ 'core/paragraph', { content: __( 'Mon–Fri 9:00–18:00, Sat 9:00–13:00', 'envo-blocks' ) } ],
		[ 'core/buttons', {}, [ [ 'core/button', { text: __( 'Get directions', 'envo-blocks' ) } ] ] ],
	];

	registerBlockType( 'envo-blocks/map', Object.assign( {}, STYLE_ATTRS, {
		template: MAP_TEMPLATE,
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var url = mapUrl( a );
			var osm = 'osm' === a.provider;
			var screen = 'screen' === a.heightMode;
			var classes = [ 'sb-map', 'sb-map--editor', 'sb-map--' + ( a.cardPosition || 'left' ), 'sb-map--' + ( a.cardVertical || 'middle' ) ];
			if ( a.grayscale ) {
				classes.push( 'sb-map--grayscale' );
			}
			if ( false !== a.showCard ) {
				classes.push( 'sb-map--card' );
			}
			if ( a.overlayColor ) {
				classes.push( 'sb-map--overlay' );
			}
			var style = {
				'--sb-map-h': ( a.height || ( screen ? 80 : 480 ) ) + ( screen ? 'vh' : 'px' ),
				'--sb-map-card-w': ( a.cardWidth || 380 ) + 'px',
				'--sb-map-card-r': ( undefined === a.cardRadius ? 12 : a.cardRadius ) + 'px',
			};
			if ( a.overlayColor ) {
				style[ '--sb-map-overlay' ] = a.overlayColor;
			}
			if ( a.cardBackground ) {
				style[ '--sb-map-card-bg' ] = a.cardBackground;
			}
			if ( a.cardText ) {
				style[ '--sb-map-card-text' ] = a.cardText;
			}
			var blockProps = useBlockProps( { className: classes.join( ' ' ), style: style } );
			var innerProps = useInnerBlocksProps( { className: 'sb-map__content' }, { template: innerTemplate( MAP_TEMPLATE ) } );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Place', 'envo-blocks' ), initialOpen: true },
						f.select( 'provider', __( 'Map', 'envo-blocks' ), [
							{ value: 'google', label: __( 'Google Maps', 'envo-blocks' ) },
							{ value: 'osm', label: __( 'OpenStreetMap', 'envo-blocks' ) },
						] ),
						! osm ? f.text( 'address', __( 'Address', 'envo-blocks' ), __( 'E.g. "Main Street 12, Springfield". Or leave it empty and enter the coordinates.', 'envo-blocks' ) ) : null,
						f.text( 'latitude', __( 'Latitude', 'envo-blocks' ), osm ? __( 'OpenStreetMap needs the coordinates, e.g. 50.0815. Find them by a right-click on the place in openstreetmap.org ("Show address").', 'envo-blocks' ) : __( 'Optional, more precise than the address.', 'envo-blocks' ), '50.0815' ),
						f.text( 'longitude', __( 'Longitude', 'envo-blocks' ), undefined, '14.4280' ),
						f.range( 'zoom', __( 'Zoom', 'envo-blocks' ), 1, 20 ),
						! osm
							? f.select( 'mapType', __( 'Map type', 'envo-blocks' ), [
									{ value: 'roadmap', label: __( 'Map', 'envo-blocks' ) },
									{ value: 'satellite', label: __( 'Satellite', 'envo-blocks' ) },
							  ] )
							: null,
						! osm ? f.text( 'apiKey', __( 'Google Maps API key (optional)', 'envo-blocks' ), __( 'The map works without a key. With a key of the Maps Embed API (free in the Google Cloud console) it uses the official embed.', 'envo-blocks' ) ) : null
					),
					el(
						PanelBody,
						{ title: __( 'Look', 'envo-blocks' ), initialOpen: false },
						f.select( 'heightMode', __( 'Height', 'envo-blocks' ), [
							{ value: 'px', label: __( 'In pixels', 'envo-blocks' ) },
							{ value: 'screen', label: __( 'Part of the screen height', 'envo-blocks' ) },
						] ),
						screen ? f.range( 'height', __( 'Height (% of the screen)', 'envo-blocks' ), 30, 100 ) : f.range( 'height', __( 'Height (px)', 'envo-blocks' ), 200, 1400 ),
						f.toggle( 'grayscale', __( 'Black and white map', 'envo-blocks' ) ),
						f.toggle( 'interactive', __( 'Visitors can move and zoom the map', 'envo-blocks' ), __( 'Off: the map is only a picture in the background.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Content on the map', 'envo-blocks' ), initialOpen: false },
						f.toggle( 'showCard', __( 'In a card', 'envo-blocks' ) ),
						f.select( 'cardPosition', __( 'Horizontal position', 'envo-blocks' ), ALIGN( true ) ),
						f.select( 'cardVertical', __( 'Vertical position', 'envo-blocks' ), [
							{ value: 'top', label: __( 'Top', 'envo-blocks' ) },
							{ value: 'middle', label: __( 'Middle', 'envo-blocks' ) },
							{ value: 'bottom', label: __( 'Bottom', 'envo-blocks' ) },
						] ),
						f.range( 'cardWidth', __( 'Width (px)', 'envo-blocks' ), 200, 900 ),
						false !== a.showCard ? f.range( 'cardRadius', __( 'Corner radius (px)', 'envo-blocks' ), 0, 40 ) : null,
						f.toggle( 'stackOnMobile', __( 'Under the map on phones', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Privacy', 'envo-blocks' ), initialOpen: false },
						f.toggle( 'clickToLoad', __( 'Load the map only after a click', 'envo-blocks' ), __( 'The map service (and its cookies) loads only when the visitor clicks the button; the choice is remembered in the browser.', 'envo-blocks' ) ),
						a.clickToLoad ? f.text( 'loadButtonText', __( 'Button text', 'envo-blocks' ), undefined, __( 'Show the map', 'envo-blocks' ) ) : null,
						a.clickToLoad ? f.text( 'loadNotice', __( 'Text above the button', 'envo-blocks' ), undefined, osm ? __( 'The map is loaded from OpenStreetMap.', 'envo-blocks' ) : __( 'The map is loaded from Google Maps, which may store cookies.', 'envo-blocks' ) ) : null
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'overlayColor', __( 'Color over the map', 'envo-blocks' ) ],
					[ 'cardBackground', __( 'Card background', 'envo-blocks' ) ],
					[ 'cardText', __( 'Card text', 'envo-blocks' ) ],
				] ),
				el(
					'div',
					blockProps,
					el(
						'div',
						{ className: 'sb-map__map' },
						url
							? el( 'iframe', { className: 'sb-map__frame', src: url, title: a.address || __( 'Map', 'envo-blocks' ), loading: 'lazy' } )
							: el( 'div', { className: 'sb-map__empty' }, osm ? __( 'Enter the latitude and longitude of the place in the block settings.', 'envo-blocks' ) : __( 'Enter the address (or the coordinates) of the place in the block settings.', 'envo-blocks' ) )
					),
					el( 'div', innerProps )
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} ) );

	var TEXT_ALIGN = [
		{ value: 'center', label: __( 'Center', 'envo-blocks' ) },
		{ value: 'left', label: __( 'Left', 'envo-blocks' ) },
		{ value: 'right', label: __( 'Right', 'envo-blocks' ) },
	];

	/* Announcement Bar */

	registerBlockType( 'envo-blocks/announcement-bar', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Messages', 'envo-blocks' ), initialOpen: true },
						el( Repeater, {
							value: Array.isArray( a.messages ) && a.messages.length ? a.messages : data.announcements || [],
							fields: [
								[ 'text', __( 'Text', 'envo-blocks' ) ],
								[ 'url', __( 'Link (optional)', 'envo-blocks' ), 'url' ],
							],
							addLabel: __( 'Add message', 'envo-blocks' ),
							onChange: function ( v ) { config.setAttributes( { messages: v } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Behaviour', 'envo-blocks' ), initialOpen: false },
						f.select( 'mode', __( 'Show the messages', 'envo-blocks' ), [
							{ value: 'rotate', label: __( 'One at a time, taking turns', 'envo-blocks' ) },
							{ value: 'marquee', label: __( 'Scrolling ticker (marquee)', 'envo-blocks' ) },
							{ value: 'static', label: __( 'All side by side', 'envo-blocks' ) },
						], __( 'Nothing moves for visitors who turned on "reduce motion", and the bar stops while the pointer or the keyboard is in it.', 'envo-blocks' ) ),
						'rotate' === a.mode ? f.range( 'interval', __( 'Seconds per message', 'envo-blocks' ), 2, 30 ) : null,
						'rotate' === a.mode ? f.toggle( 'arrows', __( 'Show arrows', 'envo-blocks' ) ) : null,
						'marquee' === a.mode ? f.range( 'speed', __( 'Speed (pixels per second)', 'envo-blocks' ), 10, 300 ) : null,
						f.toggle( 'dismissible', __( 'Visitors can close the bar', 'envo-blocks' ) ),
						a.dismissible ? f.range( 'dismissDays', __( 'Keep it closed for (days)', 'envo-blocks' ), 0, 365, __( '0 = until the browser tab is closed. Changing the messages shows the bar again.', 'envo-blocks' ) ) : null,
						f.select( 'textAlign', __( 'Alignment', 'envo-blocks' ), TEXT_ALIGN )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'iconColor', __( 'Icons', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/announcement-bar', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Countdown */

	registerBlockType( 'envo-blocks/countdown', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var daily = 'daily' === a.mode;

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Counts down to', 'envo-blocks' ), initialOpen: true },
						f.select( 'mode', __( 'End', 'envo-blocks' ), [
							{ value: 'date', label: __( 'A date and time', 'envo-blocks' ) },
							{ value: 'daily', label: __( 'A time every day', 'envo-blocks' ) },
						] ),
						! daily
							? el( TextControl, props( {
									label: __( 'Ends at', 'envo-blocks' ),
									help: __( 'In the time zone of the site (Settings > General). Every visitor sees the same end.', 'envo-blocks' ),
									type: 'datetime-local',
									value: a.endDate,
									onChange: function ( v ) { config.setAttributes( { endDate: v } ); },
							  } ) )
							: null,
						daily
							? el( TextControl, props( {
									label: __( 'Every day at', 'envo-blocks' ),
									help: __( 'For example the cut-off time for same-day shipping. In the time zone of the site.', 'envo-blocks' ),
									type: 'time',
									value: a.dailyTime,
									onChange: function ( v ) { config.setAttributes( { dailyTime: v } ); },
							  } ) )
							: null,
						daily ? el( DaysControl, { label: __( 'On these days', 'envo-blocks' ), value: a.days, onChange: function ( v ) { config.setAttributes( { days: v } ); } } ) : null,
						daily
							? f.select( 'outside', __( 'After the time and on other days', 'envo-blocks' ), [
									{ value: 'next', label: __( 'Count down to the next chosen day', 'envo-blocks' ) },
									{ value: 'hide', label: __( 'Hide until the next chosen day', 'envo-blocks' ) },
							  ] )
							: null,
						! daily
							? f.select( 'finished', __( 'When it ends', 'envo-blocks' ), [
									{ value: 'hide', label: __( 'Hide the block', 'envo-blocks' ) },
									{ value: 'text', label: __( 'Show a text', 'envo-blocks' ) },
							  ] )
							: null,
						! daily && 'text' === a.finished ? f.text( 'finishedText', __( 'Text when it ends', 'envo-blocks' ), undefined, __( 'This offer has ended.', 'envo-blocks' ) ) : null
					),
					el(
						PanelBody,
						{ title: __( 'Look', 'envo-blocks' ), initialOpen: false },
						f.text( 'title', __( 'Text above (optional)', 'envo-blocks' ), undefined, __( 'Sale ends in', 'envo-blocks' ) ),
						f.select( 'look', __( 'Style', 'envo-blocks' ), [
							{ value: 'boxes', label: __( 'Numbers in boxes', 'envo-blocks' ) },
							{ value: 'plain', label: __( 'Plain numbers', 'envo-blocks' ) },
						] ),
						f.toggle( 'showDays', __( 'Show days', 'envo-blocks' ), __( 'Off: the hours go above 24.', 'envo-blocks' ) ),
						f.toggle( 'showSeconds', __( 'Show seconds', 'envo-blocks' ) ),
						f.toggle( 'showLabels', __( 'Show labels', 'envo-blocks' ) ),
						f.select( 'textAlign', __( 'Alignment', 'envo-blocks' ), TEXT_ALIGN )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'numberColor', __( 'Numbers', 'envo-blocks' ) ],
					[ 'boxBackground', __( 'Boxes', 'envo-blocks' ) ],
					[ 'labelColor', __( 'Labels', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/countdown', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Opening Hours */

	var WEEK_DAYS = [
		__( 'Monday', 'envo-blocks' ),
		__( 'Tuesday', 'envo-blocks' ),
		__( 'Wednesday', 'envo-blocks' ),
		__( 'Thursday', 'envo-blocks' ),
		__( 'Friday', 'envo-blocks' ),
		__( 'Saturday', 'envo-blocks' ),
		__( 'Sunday', 'envo-blocks' ),
	];

	registerBlockType( 'envo-blocks/opening-hours', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var hours = Array.isArray( a.hours ) && 7 === a.hours.length ? a.hours : ( data.hours || [ '', '', '', '', '', '', '' ] );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Week', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'For example 9:00-17:00, or 9:00-12:00, 13:00-18:00 with a lunch break. Leave empty for closed. A time after midnight (18:00-2:00) belongs to the day it starts.', 'envo-blocks' ) ),
						WEEK_DAYS.map( function ( name, i ) {
							return el( TextControl, props( {
								key: i,
								label: name,
								value: hours[ i ] || '',
								placeholder: __( 'Closed', 'envo-blocks' ),
								onChange: function ( v ) {
									var next = hours.slice();
									next[ i ] = v;
									config.setAttributes( { hours: next } );
								},
							} ) );
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Special days', 'envo-blocks' ), initialOpen: false },
						el( TextareaControl, props( {
							label: __( 'Holidays and other days', 'envo-blocks' ),
							help: __( 'One per line: 2026-12-24 = 9:00-12:00 | Christmas Eve, or 12-25 = (closed every year). The note after | is optional.', 'envo-blocks' ),
							value: a.exceptions,
							rows: 4,
							onChange: function ( v ) { config.setAttributes( { exceptions: v } ); },
						} ) ),
						f.toggle( 'showExceptions', __( 'List the special days of the next 30 days', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Content', 'envo-blocks' ), initialOpen: false },
						f.text( 'title', __( 'Heading (optional)', 'envo-blocks' ), undefined, __( 'Opening hours', 'envo-blocks' ) ),
						f.toggle( 'showStatus', __( 'Show "Open now / Closed"', 'envo-blocks' ), __( 'Worked out in the visitor\'s browser in the time zone of the site, so it is right on cached pages too.', 'envo-blocks' ) ),
						f.toggle( 'showTable', __( 'Show the week', 'envo-blocks' ) ),
						f.toggle( 'highlightToday', __( 'Highlight today', 'envo-blocks' ) ),
						f.toggle( 'groupDays', __( 'Join days with the same hours', 'envo-blocks' ), __( 'Monday – Friday instead of five rows.', 'envo-blocks' ) ),
						f.text( 'closedText', __( 'Text for closed days', 'envo-blocks' ), undefined, __( 'Closed', 'envo-blocks' ) ),
						f.select( 'boxStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'box', label: __( 'Box with a border', 'envo-blocks' ) },
							{ value: 'plain', label: __( 'Plain', 'envo-blocks' ) },
						] )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'openColor', __( 'Open', 'envo-blocks' ) ],
					[ 'closedColor', __( 'Closed', 'envo-blocks' ) ],
					[ 'todayBackground', __( 'Today', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/opening-hours', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Stock Indicator */

	registerBlockType( 'envo-blocks/stock-indicator', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Stock', 'envo-blocks' ), initialOpen: true },
						f.select( 'source', __( 'Stock of', 'envo-blocks' ), [
							{ value: 'current', label: __( 'The current product (product page)', 'envo-blocks' ) },
							{ value: 'product', label: __( 'A chosen product', 'envo-blocks' ) },
						] ),
						'product' === a.source ? el( ProductPicker, { label: __( 'Product', 'envo-blocks' ), value: a.productId, onChange: function ( v ) { config.setAttributes( { productId: v } ); } } ) : null,
						el( 'p', { className: 'components-base-control__help' }, __( 'Only the real stock is shown: the product (or variation) needs "Manage stock?" turned on in WooCommerce.', 'envo-blocks' ) ),
						f.range( 'threshold', __( 'Show "Only … left" at this stock or less', 'envo-blocks' ), 1, 100 ),
						f.range( 'barFull', __( 'The bar is full at', 'envo-blocks' ), 1, 500, __( 'Stock at or above this number fills the bar.', 'envo-blocks' ) ),
						f.toggle( 'showInStock', __( 'Show "In stock" above that number', 'envo-blocks' ) ),
						f.toggle( 'showOutOfStock', __( 'Show "Out of stock" and backorders', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Texts', 'envo-blocks' ), initialOpen: false },
						el( 'p', { className: 'components-base-control__help' }, __( 'Leave empty for the default text. {stock} is the number in stock.', 'envo-blocks' ) ),
						f.text( 'lowText', __( 'Low stock', 'envo-blocks' ), undefined, __( 'Only {stock} left in stock', 'envo-blocks' ) ),
						f.text( 'inStockText', __( 'In stock', 'envo-blocks' ), undefined, __( 'In stock', 'envo-blocks' ) ),
						f.text( 'outText', __( 'Out of stock', 'envo-blocks' ), undefined, __( 'Out of stock', 'envo-blocks' ) ),
						f.text( 'backorderText', __( 'On backorder', 'envo-blocks' ), undefined, __( 'Available on backorder', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Look', 'envo-blocks' ), initialOpen: false },
						f.toggle( 'showBar', __( 'Show the bar', 'envo-blocks' ) ),
						f.toggle( 'showIcon', __( 'Show the colored dot', 'envo-blocks' ) )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'barColor', __( 'Bar', 'envo-blocks' ) ],
					[ 'trackColor', __( 'Bar background', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/stock-indicator', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Lowest Price */

	registerBlockType( 'envo-blocks/lowest-price', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Lowest price', 'envo-blocks' ), initialOpen: true },
						f.select( 'source', __( 'Price of', 'envo-blocks' ), [
							{ value: 'current', label: __( 'The current product (product page)', 'envo-blocks' ) },
							{ value: 'product', label: __( 'A chosen product', 'envo-blocks' ) },
						] ),
						'product' === a.source ? el( ProductPicker, { label: __( 'Product', 'envo-blocks' ), value: a.productId, onChange: function ( v ) { config.setAttributes( { productId: v } ); } } ) : null,
						el( 'p', { className: 'components-base-control__help' }, __( 'The lowest price comes from the price history that Envo Blocks keeps for every product. Defaults: Envo Blocks > Lowest Price.', 'envo-blocks' ) ),
						f.range( 'days', __( 'Number of days (0 = the setting)', 'envo-blocks' ), 0, 365, __( 'The EU rule is 30 days.', 'envo-blocks' ) ),
						f.select( 'showWhen', __( 'Show it', 'envo-blocks' ), [
							{ value: '', label: __( 'As in the setting', 'envo-blocks' ) },
							{ value: 'sale', label: __( 'Only for products on sale', 'envo-blocks' ) },
							{ value: 'always', label: __( 'For every product', 'envo-blocks' ) },
						] ),
						f.text( 'text', __( 'Text', 'envo-blocks' ), __( '{days} is the number of days, {price} the lowest price. Leave empty for the text of the setting.', 'envo-blocks' ), __( 'Lowest price in the last {days} days: {price}', 'envo-blocks' ) ),
						f.select( 'boxStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'plain', label: __( 'Plain text', 'envo-blocks' ) },
							{ value: 'box', label: __( 'With a background', 'envo-blocks' ) },
						] )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'priceColor', __( 'Price', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/lowest-price', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Product Filter + Active Filters */

	registerBlockType( 'envo-blocks/product-filter', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var data = ( window.envoBlocksBlocks && window.envoBlocksBlocks.filters ) || { attributes: [], brands: false, pro: false };
			var pro = !! data.pro;
			var proLabel = pro ? '' : ' (PRO)';
			var types = [
				{ value: 'attribute', label: __( 'Attribute (color, size …)', 'envo-blocks' ) },
				{ value: 'price', label: __( 'Price', 'envo-blocks' ) },
				{ value: 'category', label: __( 'Category', 'envo-blocks' ) },
				{ value: 'rating', label: __( 'Rating', 'envo-blocks' ) },
				{ value: 'stock', label: __( 'Stock status', 'envo-blocks' ) },
				{ value: 'sale', label: __( 'On sale', 'envo-blocks' ) + proLabel },
			];
			if ( data.brands ) {
				types.push( { value: 'brand', label: __( 'Brand', 'envo-blocks' ) + proLabel } );
			}
			var displays = [
				{ value: 'checkbox', label: __( 'Check boxes', 'envo-blocks' ) },
				{ value: 'list', label: __( 'Links', 'envo-blocks' ) },
				{ value: 'buttons', label: __( 'Buttons', 'envo-blocks' ) + proLabel },
				{ value: 'dropdown', label: __( 'Dropdown', 'envo-blocks' ) + proLabel },
			];
			if ( 'attribute' === a.filterType ) {
				displays.splice( 2, 0, { value: 'swatches', label: __( 'Swatches (colors, images)', 'envo-blocks' ) + proLabel } );
			}
			var list = 'price' !== a.filterType;

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Filter', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'Filters the products of the shop, the categories and the product search (classic shop or the Product Collection block). On other pages the options link to the shop.', 'envo-blocks' ) ),
						f.select( 'filterType', __( 'Filter by', 'envo-blocks' ), types ),
						'attribute' === a.filterType ? f.select( 'attribute', __( 'Attribute', 'envo-blocks' ), [ { value: '', label: __( 'The first attribute', 'envo-blocks' ) } ].concat( data.attributes ) ) : null,
						'attribute' === a.filterType ? f.select( 'queryType', __( 'More values', 'envo-blocks' ), [
							{ value: 'or', label: __( 'Products with any of them', 'envo-blocks' ) },
							{ value: 'and', label: __( 'Products with all of them', 'envo-blocks' ) },
						] ) : null,
						'category' === a.filterType || 'brand' === a.filterType ? f.range( 'depth', __( 'Levels of subcategories', 'envo-blocks' ), 1, 3, __( 'On a category page the filter lists its subcategories.', 'envo-blocks' ) ) : null,
						list && 'sale' !== a.filterType ? f.select( 'display', __( 'Look', 'envo-blocks' ), displays ) : null,
						'price' === a.filterType ? f.range( 'step', __( 'Step of the slider', 'envo-blocks' ), 1, 100 ) : null,
						f.text( 'title', __( 'Title', 'envo-blocks' ), __( 'Leave empty for the name of the filter.', 'envo-blocks' ) ),
						f.toggle( 'hideTitle', __( 'Hide the title', 'envo-blocks' ) ),
						f.select( 'collapsible', __( 'Collapsible', 'envo-blocks' ), [
							{ value: '', label: __( 'No', 'envo-blocks' ) },
							{ value: 'open', label: __( 'Yes, open', 'envo-blocks' ) },
							{ value: 'closed', label: __( 'Yes, closed', 'envo-blocks' ) },
						] ),
						list ? f.toggle( 'showCount', __( 'Show the number of products', 'envo-blocks' ) ) : null,
						list ? f.range( 'limit', __( 'Options before "Show more" (0 = all)', 'envo-blocks' ), 0, 30 ) : null,
						list ? el( wp.components.ToggleControl, {
							label: __( 'Hide options without products', 'envo-blocks' ) + proLabel,
							help: __( 'Otherwise they are greyed out. Options that no product of the page has are never shown.', 'envo-blocks' ),
							checked: !! a.hideEmpty,
							disabled: ! pro,
							onChange: function ( v ) {
								config.setAttributes( { hideEmpty: v } );
							},
						} ) : null,
						pro ? null : proNote( __( 'Envo Blocks PRO filters without reloading the page and adds brands, "On sale", swatches, buttons, dropdowns and a filter drawer for phones.', 'envo-blocks' ) )
					)
				),
				preview( 'envo-blocks/product-filter', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	registerBlockType( 'envo-blocks/active-filters', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Active filters', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'Shown only while a filter is on. The preview shows a sample.', 'envo-blocks' ) ),
						f.text( 'title', __( 'Title', 'envo-blocks' ) ),
						f.toggle( 'showClear', __( 'Show "Clear all"', 'envo-blocks' ) )
					)
				),
				preview( 'envo-blocks/active-filters', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Order Tracking */

	registerBlockType( 'envo-blocks/order-tracking', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Content', 'envo-blocks' ), initialOpen: true },
						f.text( 'title', __( 'Heading', 'envo-blocks' ), undefined, __( 'Track your order', 'envo-blocks' ) ),
						el( TextareaControl, props( {
							label: __( 'Text', 'envo-blocks' ),
							value: a.description,
							placeholder: __( 'Enter your order number and the email address you used at checkout. The order number is in the confirmation email.', 'envo-blocks' ),
							rows: 3,
							onChange: function ( v ) { config.setAttributes( { description: v } ); },
						} ) ),
						el( 'p', { className: 'components-base-control__help' }, __( 'The form is the WooCommerce order tracking: after a search the order and its updates appear in the same place.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Look', 'envo-blocks' ), initialOpen: false },
						f.select( 'boxStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'box', label: __( 'Box with a border', 'envo-blocks' ) },
							{ value: 'plain', label: __( 'Plain', 'envo-blocks' ) },
						] ),
						f.select( 'textAlign', __( 'Alignment', 'envo-blocks' ), ALIGN( false ) ),
						f.range( 'maxWidth', __( 'Maximum width (px)', 'envo-blocks' ), 320, 1200 )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'buttonColor', __( 'Button', 'envo-blocks' ) ],
					[ 'buttonText', __( 'Button text', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/order-tracking', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Free Shipping Progress */

	registerBlockType( 'envo-blocks/shipping-bar', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Free shipping', 'envo-blocks' ), initialOpen: true },
						f.select( 'thresholdSource', __( 'Amount for free shipping', 'envo-blocks' ), [
							{ value: 'auto', label: __( 'From WooCommerce "Free shipping" methods', 'envo-blocks' ) },
							{ value: 'manual', label: __( 'Set here', 'envo-blocks' ) },
						], __( 'Automatic uses the lowest minimum order amount of the Free shipping methods in your shipping zones.', 'envo-blocks' ) ),
						'manual' === a.thresholdSource ? el( TextControl, props( {
							label: __( 'Amount (in the shop currency)', 'envo-blocks' ),
							type: 'number',
							min: 0,
							value: a.threshold || '',
							onChange: function ( v ) { config.setAttributes( { threshold: parseFloat( v ) || 0 } ); },
						} ) ) : null,
						f.toggle( 'hideWhenReached', __( 'Hide when free shipping is reached', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Messages', 'envo-blocks' ), initialOpen: false },
						el( 'p', { className: 'components-base-control__help' }, __( 'Use {remaining} for the missing amount and {threshold} for the free shipping amount. Leave empty for the default text.', 'envo-blocks' ) ),
						f.text( 'messageProgress', __( 'While the amount is not reached', 'envo-blocks' ), undefined, __( 'Add {remaining} more to get free shipping!', 'envo-blocks' ) ),
						f.text( 'messageReached', __( 'When it is reached', 'envo-blocks' ), undefined, __( 'You have free shipping!', 'envo-blocks' ) ),
						f.text( 'messageEmpty', __( 'When the cart is empty', 'envo-blocks' ), undefined, __( 'Free shipping on orders over {threshold}.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Look', 'envo-blocks' ), initialOpen: false },
						f.select( 'textAlign', __( 'Text alignment', 'envo-blocks' ), ALIGN( true ) ),
						f.toggle( 'showBar', __( 'Show progress bar', 'envo-blocks' ) ),
						a.showBar ? f.range( 'barHeight', __( 'Bar height (px)', 'envo-blocks' ), 2, 30 ) : null
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'barColor', __( 'Bar', 'envo-blocks' ) ],
					[ 'trackColor', __( 'Bar track', 'envo-blocks' ) ],
					[ 'successColor', __( 'Bar when reached', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/shipping-bar', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Wishlist: Wishlist Button and Wishlist (the list) */

	function wishlistIconOptions() {
		return [
			{ value: 'heart', label: __( 'Heart', 'envo-blocks' ) },
			{ value: 'heart-round', label: __( 'Round heart', 'envo-blocks' ) },
			{ value: 'bookmark', label: __( 'Bookmark', 'envo-blocks' ) },
			{ value: 'star', label: __( 'Star', 'envo-blocks' ) },
		];
	}

	/**
	 * The wishlist page: where it is, or a button that creates it (with the Wishlist block) when there is none yet.
	 * On the Wishlist block itself it says that a published page with the block becomes the wishlist page.
	 */
	function WishlistPageNotice( config ) {
		var info = data.wishlist || {};
		var pageState = useState( info.page || {} );
		var page = pageState[ 0 ];
		var busyState = useState( false );
		var errorState = useState( '' );

		function create() {
			busyState[ 1 ]( true );
			errorState[ 1 ]( '' );
			apiFetch( { path: '/envo-blocks/v1/wishlist/page', method: 'POST' } )
				.then( function ( result ) {
					info.page = result;
					pageState[ 1 ]( result );
				} )
				.catch( function ( e ) {
					errorState[ 1 ]( e && e.message ? e.message : __( 'The wishlist page could not be created.', 'envo-blocks' ) );
				} )
				.then( function () {
					busyState[ 1 ]( false );
				} );
		}

		if ( page && page.id ) {
			return el(
				'p',
				{ className: 'components-base-control__help', style: { marginTop: 0 } },
				__( 'Wishlist page:', 'envo-blocks' ) + ' ',
				el( 'a', { href: page.url, target: '_blank', rel: 'noopener noreferrer' }, page.title || page.url ),
				page.edit ? el( Fragment, null, ' · ', el( 'a', { href: page.edit, target: '_blank', rel: 'noopener noreferrer' }, __( 'Edit', 'envo-blocks' ) ) ) : null
			);
		}

		if ( config.onPage ) {
			return el( 'p', { className: 'sb-notice' }, __( 'No wishlist page is set yet: this page becomes the wishlist page when it is published.', 'envo-blocks' ) );
		}

		return el(
			'div',
			{ className: 'sb-notice', style: { marginBottom: '16px' } },
			el( 'p', { style: { marginTop: 0 } }, __( 'You have no wishlist page yet. The "View" link after saving a product leads there.', 'envo-blocks' ) ),
			info.canCreate
				? el( Button, { variant: 'primary', isBusy: busyState[ 0 ], disabled: busyState[ 0 ], onClick: create }, __( 'Create the wishlist page', 'envo-blocks' ) )
				: el( 'a', { href: info.settingsUrl }, __( 'Set it in WooCommerce > Settings > Advanced', 'envo-blocks' ) ),
			errorState[ 0 ] ? el( 'p', { style: { color: '#d63638', marginBottom: 0 } }, errorState[ 0 ] ) : null
		);
	}

	registerBlockType( 'envo-blocks/wishlist-button', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var withText = 'icon' !== a.buttonStyle;

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Wishlist button', 'envo-blocks' ), initialOpen: true },
						el( WishlistPageNotice, {} ),
						el( 'p', { className: 'components-base-control__help', style: { marginTop: 0 } }, __( 'On the product page and in a product template (Product Collection) the button uses that product. Choose a product only to place the button somewhere else.', 'envo-blocks' ) ),
						el( ProductPicker, { label: __( 'Product', 'envo-blocks' ), value: a.productId, onChange: function ( v ) { config.setAttributes( { productId: v } ); } } ),
						f.select( 'buttonStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'button', label: __( 'Button with an icon and text', 'envo-blocks' ) },
							{ value: 'icon', label: __( 'Round icon', 'envo-blocks' ) },
							{ value: 'link', label: __( 'Icon and text (link style)', 'envo-blocks' ) },
						] ),
						f.select( 'icon', __( 'Icon', 'envo-blocks' ), wishlistIconOptions() ),
						f.range( 'iconSize', __( 'Icon size (px)', 'envo-blocks' ), 14, 48 ),
						withText ? f.text( 'addText', __( 'Text', 'envo-blocks' ), undefined, __( 'Add to wishlist', 'envo-blocks' ) ) : null,
						withText ? f.text( 'addedText', __( 'Text when saved', 'envo-blocks' ), undefined, __( 'In your wishlist', 'envo-blocks' ) ) : null
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'iconColor', __( 'Icon and text', 'envo-blocks' ) ],
					[ 'activeColor', __( 'Icon when saved', 'envo-blocks' ) ],
					[ 'buttonBackground', __( 'Background', 'envo-blocks' ) ],
					[ 'borderColor', __( 'Border', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/wishlist-button', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	registerBlockType( 'envo-blocks/wishlist', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Wishlist', 'envo-blocks' ), initialOpen: true },
						el( WishlistPageNotice, { onPage: true } ),
						el( 'p', { className: 'components-base-control__help', style: { marginTop: 0 } }, __( 'The preview shows a few products of the shop; visitors see their own saved products.', 'envo-blocks' ) ),
						f.select( 'layout', __( 'Layout', 'envo-blocks' ), [
							{ value: 'grid', label: __( 'Grid of cards', 'envo-blocks' ) },
							{ value: 'list', label: __( 'List (rows)', 'envo-blocks' ) },
						] ),
						'grid' === a.layout ? f.range( 'columns', __( 'Columns', 'envo-blocks' ), 1, 6, __( 'Up to 3 on tablets, 2 on phones.', 'envo-blocks' ) ) : null,
						f.toggle( 'showPrice', __( 'Price', 'envo-blocks' ) ),
						f.toggle( 'showStock', __( 'Stock status', 'envo-blocks' ) ),
						f.toggle( 'showAddToCart', __( 'Add to cart button', 'envo-blocks' ), __( 'Products with options get a link to the product page.', 'envo-blocks' ) ),
						a.showAddToCart ? f.toggle( 'removeOnAdd', __( 'Remove from the wishlist after adding to the cart', 'envo-blocks' ) ) : null,
						data.pro ? null : proNote( __( 'A share link, "Add all to cart", hearts on product images and a header icon with a drawer are in Envo Blocks PRO.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Empty wishlist', 'envo-blocks' ), initialOpen: false },
						f.text( 'emptyTitle', __( 'Title', 'envo-blocks' ), undefined, __( 'Your wishlist is empty', 'envo-blocks' ) ),
						f.text( 'emptyText', __( 'Text', 'envo-blocks' ), undefined, __( 'Tap the heart on any product to save it for later.', 'envo-blocks' ) ),
						f.text( 'emptyButtonText', __( 'Button text', 'envo-blocks' ), __( 'The button leads to the shop.', 'envo-blocks' ), __( 'Continue shopping', 'envo-blocks' ) )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'cardBackground', __( 'Card background', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'accentColor', __( 'Buttons', 'envo-blocks' ) ],
					[ 'accentTextColor', __( 'Button text', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/wishlist', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );
	/* Payment QR Code */

	registerBlockType( 'envo-blocks/payment-qr', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var accounts = data.bankAccounts || {};
			var accountOptions = [ { value: '', label: __( 'As in Envo Blocks > Payment QR Code', 'envo-blocks' ) } ].concat(
				Object.keys( accounts ).map( function ( key ) {
					return { value: key, label: accounts[ key ] };
				} )
			);

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Payment', 'envo-blocks' ), initialOpen: true },
						f.select( 'format', __( 'QR code format', 'envo-blocks' ), [
							{ value: '', label: __( 'As in Envo Blocks > Payment QR Code', 'envo-blocks' ) },
							{ value: 'auto', label: __( 'Automatic: QR Platba for CZK, EPC QR for EUR', 'envo-blocks' ) },
							{ value: 'spd', label: __( 'Only QR Platba (CZK)', 'envo-blocks' ) },
							{ value: 'epc', label: __( 'Only EPC QR (EUR)', 'envo-blocks' ) },
						], __( 'Orders in other currencies get no code.', 'envo-blocks' ) ),
						f.select( 'account', __( 'Account', 'envo-blocks' ), accountOptions, __( 'The accounts with an IBAN from WooCommerce > Settings > Payments > Direct bank transfer.', 'envo-blocks' ) ),
						f.text( 'message', __( 'Payment message', 'envo-blocks' ), __( 'Placeholders: {order_number}, {order_id}, {site_title}. Empty = as in Envo Blocks > Payment QR Code.', 'envo-blocks' ), 'Order {order_number}' ),
						el( 'p', { className: 'components-base-control__help' }, __( 'Put the block in the Order Confirmation template. It shows only for orders paid by direct bank transfer that wait for the payment.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Content', 'envo-blocks' ), initialOpen: false },
						f.text( 'title', __( 'Heading', 'envo-blocks' ), undefined, __( 'Pay by QR code', 'envo-blocks' ) ),
						el( TextareaControl, props( {
							label: __( 'Text', 'envo-blocks' ),
							value: a.description,
							placeholder: __( 'Scan the code with the app of your bank: the account, amount and reference are filled in.', 'envo-blocks' ),
							rows: 3,
							onChange: function ( v ) { config.setAttributes( { description: v } ); },
						} ) ),
						f.toggle( 'showDetails', __( 'Payment details', 'envo-blocks' ), __( 'Account, amount, variable symbol and message for entering the payment by hand.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Look', 'envo-blocks' ), initialOpen: false },
						f.select( 'layout', __( 'Layout', 'envo-blocks' ), [
							{ value: 'side', label: __( 'Code beside the details', 'envo-blocks' ) },
							{ value: 'stack', label: __( 'Code above the details', 'envo-blocks' ) },
						] ),
						f.select( 'boxStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'box', label: __( 'Box with a border', 'envo-blocks' ) },
							{ value: 'plain', label: __( 'Plain', 'envo-blocks' ) },
						] ),
						f.range( 'qrSize', __( 'QR code size (px)', 'envo-blocks' ), 120, 360 )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/payment-qr', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Invoice Download */

	registerBlockType( 'envo-blocks/invoice-download', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Button', 'envo-blocks' ), initialOpen: true },
						f.text( 'text', __( 'Button text', 'envo-blocks' ), __( 'Empty = the document name and number, e.g. "Invoice 202600012 (PDF)".', 'envo-blocks' ) ),
						f.toggle( 'showCredits', __( 'Credit notes too', 'envo-blocks' ) ),
						f.select( 'textAlign', __( 'Alignment', 'envo-blocks' ), [
							{ value: 'left', label: __( 'Left', 'envo-blocks' ) },
							{ value: 'center', label: __( 'Center', 'envo-blocks' ) },
							{ value: 'right', label: __( 'Right', 'envo-blocks' ) },
						] ),
						el( 'p', { className: 'components-base-control__help' }, __( 'Put the block in the Order Confirmation template. It shows when the invoice of the order is issued (Envo Blocks > Invoices).', 'envo-blocks' ) )
					)
				),
				preview( 'envo-blocks/invoice-download', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );
	/* Product Tabs */

	var PT_SOURCES = {
		description: __( 'Description', 'envo-blocks' ),
		additional_information: __( 'Additional information', 'envo-blocks' ),
		reviews: __( 'Reviews ({count})', 'envo-blocks' ),
		custom: __( 'Own content (blocks)', 'envo-blocks' ),
		product: __( "The product's own tabs", 'envo-blocks' ),
		others: __( 'Tabs of other plugins', 'envo-blocks' ),
	};

	/** Sources that add any number of tabs (or none) on the product page. */
	var PT_AUTO = [ 'product', 'others' ];

	var PT_TEMPLATE = [
		[ 'envo-blocks/product-tab', { source: 'description' } ],
		[ 'envo-blocks/product-tab', { source: 'additional_information' } ],
		[ 'envo-blocks/product-tab', { source: 'reviews' } ],
		[ 'envo-blocks/product-tab', { source: 'product' } ],
		[ 'envo-blocks/product-tab', { source: 'others' } ],
	];

	var PT_TAB_TEMPLATE = [ [ 'core/paragraph', { placeholder: __( 'Content of the tab: text, images, a table, any blocks…', 'envo-blocks' ) } ] ];

	function ptTitle( attrs ) {
		var source = PT_SOURCES[ attrs.source ] ? attrs.source : 'custom';
		if ( attrs.title ) {
			return attrs.title.replace( '{count}', '3' );
		}
		if ( 'custom' === source ) {
			return __( 'Tab', 'envo-blocks' );
		}
		return PT_SOURCES[ source ].replace( '{count}', '3' );
	}

	function ptIcon( name ) {
		var svg = name && data.iconSvgs ? data.iconSvgs[ name ] : '';
		return svg ? el( 'span', { className: 'sb-ptabs__icon-wrap', dangerouslySetInnerHTML: { __html: svg } } ) : null;
	}

	/** Product categories as tokens (stored as term IDs). */
	function CategoryPicker( config ) {
		var state = useState( [] );
		var terms = state[ 0 ];

		useEffect( function () {
			apiFetch( { path: '/wp/v2/product_cat?per_page=100&_fields=id,name' } ).then( function ( list ) {
				state[ 1 ]( list || [] );
			} ).catch( function () {} );
		}, [] );

		var byId = {};
		var byName = {};
		terms.forEach( function ( term ) {
			var area = document.createElement( 'textarea' );
			area.innerHTML = term.name;
			byId[ term.id ] = area.value;
			byName[ area.value ] = term.id;
		} );

		return el( wp.components.FormTokenField, {
			__next40pxDefaultSize: true,
			__nextHasNoMarginBottom: true,
			__experimentalExpandOnFocus: true,
			label: config.label,
			value: ( config.value || [] ).map( function ( id ) { return byId[ id ] || String( id ); } ),
			suggestions: Object.keys( byName ),
			onChange: function ( tokens ) {
				config.onChange( tokens.map( function ( token ) {
					return byName[ token ] || ( byId[ parseInt( token, 10 ) ] ? parseInt( token, 10 ) : 0 );
				} ).filter( Boolean ) );
			},
		} );
	}

	registerBlockType( 'envo-blocks/product-tabs', Object.assign( {}, STYLE_ATTRS, {
		template: PT_TEMPLATE,
		transforms: {
			from: [
				{
					type: 'block',
					blocks: [ 'woocommerce/product-details' ],
					transform: function () {
						return wp.blocks.createBlock( 'envo-blocks/product-tabs', {}, PT_TEMPLATE.map( function ( item ) {
							return wp.blocks.createBlock( item[ 0 ], item[ 1 ] );
						} ) );
					},
				},
			],
		},
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var clientId = config.clientId;
			var children = wp.data.useSelect( function ( select ) {
				return select( 'core/block-editor' ).getBlocks( clientId );
			}, [ clientId ] );
			var selectedIndex = wp.data.useSelect( function ( select ) {
				var store = select( 'core/block-editor' );
				var selected = store.getSelectedBlockClientId();
				if ( ! selected ) {
					return -1;
				}
				var chain = [ selected ].concat( store.getBlockParents( selected ) );
				return store.getBlockOrder( clientId ).findIndex( function ( id ) {
					return -1 !== chain.indexOf( id );
				} );
			}, [ clientId ] );
			var editor = wp.data.useDispatch( 'core/block-editor' );
			var activeState = useState( 0 );
			var current = Math.max( 0, Math.min( activeState[ 0 ], children.length - 1 ) );

			useEffect( function () {
				if ( selectedIndex >= 0 ) {
					activeState[ 1 ]( selectedIndex );
				}
			}, [ selectedIndex ] );

			var accordion = 'accordion' === a.layout;
			var position = accordion ? 'top' : ( a.navPosition || 'top' );
			var style = {};
			[
				[ 'tabColor', '--sb-pt-color' ],
				[ 'tabActiveColor', '--sb-pt-active' ],
				[ 'accentColor', '--sb-pt-accent' ],
				[ 'tabBackground', '--sb-pt-bg' ],
				[ 'tabActiveBackground', '--sb-pt-active-bg' ],
				[ 'borderColor', '--sb-pt-border' ],
				[ 'panelBackground', '--sb-pt-panel-bg' ],
				[ 'panelText', '--sb-pt-panel-text' ],
			].forEach( function ( pair ) {
				if ( a[ pair[ 0 ] ] ) {
					style[ pair[ 1 ] ] = a[ pair[ 0 ] ];
				}
			} );
			style[ '--sb-pt-nav-w' ] = ( a.navWidth || 220 ) + 'px';
			style[ '--sb-pt-pad' ] = ( undefined === a.panelPadding ? 24 : a.panelPadding ) + 'px';
			style[ '--sb-pt-radius' ] = ( undefined === a.radius ? 6 : a.radius ) + 'px';

			var blockProps = useBlockProps( {
				className: [
					'sb-ptabs',
					'sb-ptabs--tabs',
					'sb-ptabs--editor',
					'sb-ptabs--' + ( a.tabStyle || 'underline' ),
					'sb-ptabs--nav-' + position,
					'sb-ptabs--align-' + ( accordion ? 'left' : ( a.navAlign || 'left' ) ),
					a.panelBackground ? 'sb-ptabs--has-panel-bg' : '',
				].join( ' ' ),
				style: style,
			} );
			var innerProps = useInnerBlocksProps( { className: 'sb-ptabs__panels' }, {
				allowedBlocks: [ 'envo-blocks/product-tab' ],
				template: innerTemplate( PT_TEMPLATE ),
				renderAppender: false,
			} );

			function addTab() {
				var block = wp.blocks.createBlock( 'envo-blocks/product-tab', { source: 'custom', title: __( 'New tab', 'envo-blocks' ) }, [ wp.blocks.createBlock( 'core/paragraph', {} ) ] );
				editor.insertBlock( block, children.length, clientId );
				activeState[ 1 ]( children.length );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Tabs', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'Every tab is a Product Tab block: click a tab to edit it, move it with the arrows of its toolbar or in the List View, add one with the + button. Tabs without content for a product (no description, no attributes, reviews off) are left out.', 'envo-blocks' ) ),
						f.select( 'source', __( 'Tabs of', 'envo-blocks' ), [
							{ value: 'current', label: __( 'The current product (product page)', 'envo-blocks' ) },
							{ value: 'product', label: __( 'A chosen product', 'envo-blocks' ) },
						] ),
						'product' === a.source ? el( ProductPicker, { label: __( 'Product', 'envo-blocks' ), value: a.productId, onChange: function ( v ) { config.setAttributes( { productId: v } ); } } ) : null,
						f.toggle( 'showHeadings', __( 'Headings inside the panels', 'envo-blocks' ), __( 'The "Description" and "Additional information" headings WooCommerce prints above the text.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Layout', 'envo-blocks' ), initialOpen: true },
						f.select( 'layout', __( 'Show as', 'envo-blocks' ), [
							{ value: 'tabs', label: __( 'Tabs', 'envo-blocks' ) },
							{ value: 'accordion', label: __( 'Accordion', 'envo-blocks' ) },
						] ),
						accordion ? null : f.select( 'navPosition', __( 'Tabs position', 'envo-blocks' ), [
							{ value: 'top', label: __( 'Top', 'envo-blocks' ) },
							{ value: 'left', label: __( 'Left', 'envo-blocks' ) },
							{ value: 'right', label: __( 'Right', 'envo-blocks' ) },
						] ),
						f.select( 'tabStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'underline', label: __( 'Underline', 'envo-blocks' ) },
							{ value: 'boxed', label: __( 'Boxed', 'envo-blocks' ) },
							{ value: 'pills', label: __( 'Pills', 'envo-blocks' ) },
							{ value: 'minimal', label: __( 'Text only', 'envo-blocks' ) },
						] ),
						! accordion && 'top' === ( a.navPosition || 'top' ) ? f.select( 'navAlign', __( 'Tabs alignment', 'envo-blocks' ), [
							{ value: 'left', label: __( 'Left', 'envo-blocks' ) },
							{ value: 'center', label: __( 'Center', 'envo-blocks' ) },
							{ value: 'right', label: __( 'Right', 'envo-blocks' ) },
							{ value: 'stretch', label: __( 'Full width', 'envo-blocks' ) },
						] ) : null,
						! accordion && 'top' !== ( a.navPosition || 'top' ) ? f.range( 'navWidth', __( 'Width of the tabs column (px)', 'envo-blocks' ), 120, 480 ) : null,
						accordion ? null : f.toggle( 'mobileAccordion', __( 'Accordion on phones', 'envo-blocks' ), __( 'Below 782 px the tabs turn into an accordion.', 'envo-blocks' ) ),
						accordion ? f.toggle( 'firstOpen', __( 'First item open', 'envo-blocks' ) ) : null,
						accordion ? f.toggle( 'allowMultiple', __( 'More items open at once', 'envo-blocks' ) ) : null,
						accordion ? el( 'p', { className: 'components-base-control__help' }, __( 'The editor shows the items as tabs; the page shows the accordion.', 'envo-blocks' ) ) : null,
						f.range( 'panelPadding', __( 'Space around the content (px)', 'envo-blocks' ), 0, 80 ),
						f.range( 'radius', __( 'Corner radius (px)', 'envo-blocks' ), 0, 40 )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'tabColor', __( 'Tab text', 'envo-blocks' ) ],
					[ 'tabActiveColor', __( 'Active tab text', 'envo-blocks' ) ],
					[ 'accentColor', __( 'Accent (line, active pill)', 'envo-blocks' ) ],
					[ 'tabBackground', __( 'Tab background', 'envo-blocks' ) ],
					[ 'tabActiveBackground', __( 'Active tab background', 'envo-blocks' ) ],
					[ 'borderColor', __( 'Lines and borders', 'envo-blocks' ) ],
					[ 'panelBackground', __( 'Content background', 'envo-blocks' ) ],
					[ 'panelText', __( 'Content text', 'envo-blocks' ) ],
				] ),
				el(
					'div',
					blockProps,
					el(
						'div',
						{ className: 'sb-ptabs__nav', role: 'tablist' },
						children.map( function ( child, index ) {
							var auto = -1 !== PT_AUTO.indexOf( child.attributes.source );
							return el(
								'button',
								{
									key: child.clientId,
									type: 'button',
									role: 'tab',
									'aria-selected': index === current,
									className: 'sb-ptabs__tab' + ( index === current ? ' is-active' : '' ) + ( auto ? ' sb-ptabs__tab--auto' : '' ),
									title: auto ? __( 'Adds its tabs only when the product has them.', 'envo-blocks' ) : undefined,
									onClick: function () {
										activeState[ 1 ]( index );
										editor.selectBlock( child.clientId );
									},
								},
								ptIcon( child.attributes.icon ),
								el( 'span', null, ptTitle( child.attributes ) )
							);
						} ),
						el( Button, { className: 'sb-ptabs__add', icon: 'plus', label: __( 'Add tab', 'envo-blocks' ), onClick: addTab } )
					),
					el( 'style', null, '[data-block="' + clientId + '"] > .sb-ptabs__panels > .wp-block-envo-blocks-product-tab:not(:nth-child(' + ( current + 1 ) + ')){display:none}' ),
					el( 'div', innerProps )
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} ) );

	/** Content of a tab with own blocks. */
	function ProductTabCustom() {
		var blockProps = useBlockProps( { className: 'sb-ptabs__panel' } );
		var innerProps = useInnerBlocksProps( blockProps, { template: innerTemplate( PT_TAB_TEMPLATE ) } );
		return el( 'div', innerProps );
	}

	/** A tab filled from the product: a sample of what shows on the product page. */
	function ProductTabAuto( props ) {
		var source = props.source;
		var blockProps = useBlockProps( { className: 'sb-ptabs__panel' } );
		var body;

		if ( 'additional_information' === source ) {
			body = el(
				'table',
				{ className: 'sb-ptab-sample__table' },
				el( 'tbody', null, [
					[ __( 'Weight', 'envo-blocks' ), '0.4 kg' ],
					[ __( 'Dimensions', 'envo-blocks' ), '30 × 20 × 4 cm' ],
					[ __( 'Color', 'envo-blocks' ), __( 'Black, White', 'envo-blocks' ) ],
				].map( function ( row ) {
					return el( 'tr', { key: row[ 0 ] }, el( 'th', null, row[ 0 ] ), el( 'td', null, row[ 1 ] ) );
				} ) )
			);
		} else if ( 'reviews' === source ) {
			body = el( 'p', null, '★★★★★ ' + __( 'The reviews of the product and the form for a new review.', 'envo-blocks' ) );
		} else if ( 'product' === source ) {
			body = el( 'p', null, __( 'The tabs of the product (Products > Edit > Product data > Tabs), each as its own tab, here in the order. Nothing when the product has none.', 'envo-blocks' ) );
		} else if ( 'others' === source ) {
			body = el( 'p', null, __( 'Tabs that other plugins add to WooCommerce products (e.g. vendor info, FAQ, size charts), each as its own tab. Nothing when there are none.', 'envo-blocks' ) );
		} else {
			body = el( 'p', null, __( 'The description of the product (its main text) is shown here.', 'envo-blocks' ) );
		}

		return el(
			'div',
			blockProps,
			el( 'div', { className: 'sb-ptab-sample' }, el( 'span', { className: 'sb-ptab-sample__label' }, PT_SOURCES[ source ].replace( ' ({count})', '' ) + ' — ' + __( 'filled on the product page', 'envo-blocks' ) ), body )
		);
	}

	registerBlockType( 'envo-blocks/product-tab', Object.assign( {}, STYLE_ATTRS, {
		__experimentalLabel: function ( attrs ) {
			return ptTitle( attrs );
		},
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var source = PT_SOURCES[ a.source ] ? a.source : 'custom';
			var auto = -1 !== PT_AUTO.indexOf( source );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Tab', 'envo-blocks' ), initialOpen: true },
						f.select( 'source', __( 'Content', 'envo-blocks' ), Object.keys( PT_SOURCES ).map( function ( key ) {
							return { value: key, label: PT_SOURCES[ key ].replace( ' ({count})', '' ) };
						} ) ),
						auto ? null : f.text( 'title', __( 'Title', 'envo-blocks' ), 'reviews' === source ? __( '{count} is the number of reviews.', 'envo-blocks' ) : undefined, 'custom' === source ? __( 'e.g. Shipping & returns', 'envo-blocks' ) : PT_SOURCES[ source ] ),
						f.select( 'icon', __( 'Icon', 'envo-blocks' ), iconOptions( true ) ),
						el( CategoryPicker, {
							label: __( 'Only for products in these categories', 'envo-blocks' ),
							value: a.categories,
							onChange: function ( v ) { config.setAttributes( { categories: v } ); },
						} ),
						el( 'p', { className: 'components-base-control__help' }, __( 'Empty = every product.', 'envo-blocks' ) )
					)
				),
				'custom' === source ? el( ProductTabCustom ) : el( ProductTabAuto, { source: source } )
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} ) );
	/* FAQ */

	var RichText = wp.blockEditor.RichText;

	function faqSample( question, answer ) {
		return [ 'envo-blocks/faq-item', { question: question }, [ [ 'core/paragraph', { content: answer } ] ] ];
	}

	var FAQ_TEMPLATE = [
		faqSample( __( 'How long does delivery take?', 'envo-blocks' ), __( 'Orders placed by 2 pm on a working day leave the same day. Delivery usually takes 1–3 working days.', 'envo-blocks' ) ),
		faqSample( __( 'Can I return a product?', 'envo-blocks' ), __( 'Yes, within 30 days of delivery. Unused products in their original packaging get a full refund.', 'envo-blocks' ) ),
		faqSample( __( 'Which payment methods do you accept?', 'envo-blocks' ), __( 'Cards, Apple Pay and Google Pay, bank transfer and cash on delivery.', 'envo-blocks' ) ),
	];

	var FAQ_ITEM_TEMPLATE = [ [ 'core/paragraph', { placeholder: __( 'The answer: text, a list, an image, a button…', 'envo-blocks' ) } ] ];

	registerBlockType( 'envo-blocks/faq', Object.assign( {}, STYLE_ATTRS, {
		template: FAQ_TEMPLATE,
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var style = {};
			[
				[ 'questionColor', '--sb-faq-q' ],
				[ 'questionBackground', '--sb-faq-q-bg' ],
				[ 'openBackground', '--sb-faq-open-bg' ],
				[ 'answerColor', '--sb-faq-a' ],
				[ 'iconColor', '--sb-faq-icon' ],
				[ 'borderColor', '--sb-faq-border' ],
			].forEach( function ( pair ) {
				if ( a[ pair[ 0 ] ] ) {
					style[ pair[ 1 ] ] = a[ pair[ 0 ] ];
				}
			} );
			style[ '--sb-faq-gap' ] = ( undefined === a.gap ? 12 : a.gap ) + 'px';
			style[ '--sb-faq-pad' ] = ( undefined === a.padding ? 20 : a.padding ) + 'px';
			style[ '--sb-faq-radius' ] = ( undefined === a.radius ? 10 : a.radius ) + 'px';

			var blockProps = useBlockProps( {
				className: [
					'sb-faq',
					'sb-faq--editor',
					'sb-faq--' + ( a.itemStyle || 'cards' ),
					'sb-faq--icon-' + ( a.icon || 'plus' ),
					'sb-faq--icon-' + ( a.iconPosition || 'right' ),
				].join( ' ' ),
				style: style,
			} );
			var innerProps = useInnerBlocksProps( blockProps, {
				allowedBlocks: [ 'envo-blocks/faq-item' ],
				template: innerTemplate( FAQ_TEMPLATE ),
				renderAppender: InnerBlocks.ButtonBlockAppender,
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Questions', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'Every question is a FAQ Item block: type the question, the answer below it is made of blocks. Add a question with the + button, move it with the arrows of its toolbar. The editor shows all answers; the page shows them when a question is clicked.', 'envo-blocks' ) ),
						f.toggle( 'firstOpen', __( 'First question open', 'envo-blocks' ) ),
						f.toggle( 'allowMultiple', __( 'More questions open at once', 'envo-blocks' ), __( 'Off: opening a question closes the open one.', 'envo-blocks' ) ),
						f.select( 'questionTag', __( 'Question as', 'envo-blocks' ), [
							{ value: 'h2', label: __( 'Heading 2', 'envo-blocks' ) },
							{ value: 'h3', label: __( 'Heading 3', 'envo-blocks' ) },
							{ value: 'h4', label: __( 'Heading 4', 'envo-blocks' ) },
							{ value: 'p', label: __( 'Text', 'envo-blocks' ) },
						], __( 'For the outline of the page; the look stays the same.', 'envo-blocks' ) ),
						f.toggle( 'schema', __( 'FAQ data for search engines', 'envo-blocks' ), __( 'Adds schema.org FAQPage data (JSON-LD) with the questions and answers to the page. Turn it off when an SEO plugin already adds FAQ data for this page.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Layout', 'envo-blocks' ), initialOpen: true },
						f.select( 'itemStyle', __( 'Style', 'envo-blocks' ), [
							{ value: 'cards', label: __( 'Cards', 'envo-blocks' ) },
							{ value: 'boxed', label: __( 'One box', 'envo-blocks' ) },
							{ value: 'lines', label: __( 'Lines', 'envo-blocks' ) },
							{ value: 'minimal', label: __( 'Text only', 'envo-blocks' ) },
						] ),
						f.select( 'icon', __( 'Icon', 'envo-blocks' ), [
							{ value: 'plus', label: __( 'Plus / minus', 'envo-blocks' ) },
							{ value: 'chevron', label: __( 'Arrow', 'envo-blocks' ) },
							{ value: 'none', label: __( 'None', 'envo-blocks' ) },
						] ),
						'none' === a.icon ? null : f.select( 'iconPosition', __( 'Icon position', 'envo-blocks' ), [
							{ value: 'right', label: __( 'Right', 'envo-blocks' ) },
							{ value: 'left', label: __( 'Left', 'envo-blocks' ) },
						] ),
						'cards' === ( a.itemStyle || 'cards' ) ? f.range( 'gap', __( 'Space between questions (px)', 'envo-blocks' ), 0, 60 ) : null,
						f.range( 'padding', __( 'Inner space (px)', 'envo-blocks' ), 0, 60 ),
						-1 !== [ 'cards', 'boxed' ].indexOf( a.itemStyle || 'cards' ) ? f.range( 'radius', __( 'Corner radius (px)', 'envo-blocks' ), 0, 40 ) : null
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'questionColor', __( 'Question text', 'envo-blocks' ) ],
					[ 'questionBackground', __( 'Background', 'envo-blocks' ) ],
					[ 'openBackground', __( 'Background of the open question', 'envo-blocks' ) ],
					[ 'answerColor', __( 'Answer text', 'envo-blocks' ) ],
					[ 'iconColor', __( 'Icon', 'envo-blocks' ) ],
					[ 'borderColor', __( 'Lines and borders', 'envo-blocks' ) ],
				] ),
				el( 'div', innerProps )
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} ) );

	registerBlockType( 'envo-blocks/faq-item', Object.assign( {}, STYLE_ATTRS, {
		template: FAQ_ITEM_TEMPLATE,
		__experimentalLabel: function ( attrs ) {
			var area = document.createElement( 'div' );
			area.innerHTML = attrs.question || '';
			return area.textContent || __( 'FAQ Item', 'envo-blocks' );
		},
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var blockProps = useBlockProps( { className: 'sb-faq__item' } );
			var answerProps = useInnerBlocksProps( { className: 'sb-faq__answer' }, { template: innerTemplate( FAQ_ITEM_TEMPLATE ) } );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Question', 'envo-blocks' ), initialOpen: true },
						f.toggle( 'open', __( 'Open when the page loads', 'envo-blocks' ) )
					)
				),
				el(
					'div',
					blockProps,
					el(
						'div',
						{ className: 'sb-faq__question' },
						el( RichText, {
							tagName: 'div',
							className: 'sb-faq__title',
							value: a.question,
							allowedFormats: [ 'core/bold', 'core/italic' ],
							placeholder: __( 'Question…', 'envo-blocks' ),
							onChange: function ( v ) { config.setAttributes( { question: v } ); },
						} ),
						el( 'span', { className: 'sb-faq__icon', 'aria-hidden': true } )
					),
					el( 'div', answerProps )
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} ) );
	/* Product Questions */

	registerBlockType( 'envo-blocks/product-questions', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Questions', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'Customers ask on the product page; you answer in Envo Blocks › Settings › Product Questions. Only answered questions are shown.', 'envo-blocks' ) ),
						f.select( 'source', __( 'Questions of', 'envo-blocks' ), [
							{ value: 'current', label: __( 'The current product (product page)', 'envo-blocks' ) },
							{ value: 'product', label: __( 'A chosen product', 'envo-blocks' ) },
						] ),
						'product' === a.source ? el( ProductPicker, { label: __( 'Product', 'envo-blocks' ), value: a.productId, onChange: function ( v ) { config.setAttributes( { productId: v } ); } } ) : null,
						f.text( 'title', __( 'Heading', 'envo-blocks' ), undefined, __( 'Questions & answers', 'envo-blocks' ) ),
						f.range( 'perPage', __( 'Questions before "Show more"', 'envo-blocks' ), 1, 20 ),
						f.toggle( 'showSearch', __( 'Search field', 'envo-blocks' ), __( 'From 4 answered questions.', 'envo-blocks' ) ),
						f.toggle( 'showForm', __( '"Ask a question" form', 'envo-blocks' ) )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'accentColor', __( 'Accent (Q / A, buttons)', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'borderColor', __( 'Lines and borders', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/product-questions', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );
	/* Form Styler */

	var formsRequest = null;

	/** The active form plugins with their forms (fetched once per editor). */
	function useFormPlugins() {
		var state = useState( null );

		useEffect( function () {
			if ( ! formsRequest ) {
				formsRequest = apiFetch( { path: '/envo-blocks/v1/forms' } ).catch( function () {
					formsRequest = null;
					return [];
				} );
			}
			formsRequest.then( function ( list ) {
				state[ 1 ]( Array.isArray( list ) ? list : [] );
			} );
		}, [] );

		return state[ 0 ];
	}

	registerBlockType( 'envo-blocks/form-styler', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var plugins = useFormPlugins();
			var current = ( plugins || [] ).filter( function ( p ) { return p.value === a.plugin; } )[ 0 ];

			var pluginOptions = [ { value: '', label: plugins ? __( '— Choose —', 'envo-blocks' ) : __( 'Loading…', 'envo-blocks' ) } ]
				.concat( ( plugins || [] ).map( function ( p ) { return { value: p.value, label: p.label }; } ) )
				.concat( [ { value: 'shortcode', label: __( 'Other form (shortcode)', 'envo-blocks' ) } ] );

			var source = [
				el( 'p', { key: 'help', className: 'components-base-control__help' }, plugins && ! plugins.length ?
					__( 'No supported form plugin is active. Install Contact Form 7, WPForms, Fluent Forms, Ninja Forms, Formidable Forms, Forminator or Gravity Forms, or paste the shortcode of another form.', 'envo-blocks' ) :
					__( 'The form plugin keeps the fields, e-mails and entries; this block gives the form your look.', 'envo-blocks' )
				),
				el( SelectControl, props( {
					key: 'plugin',
					label: __( 'Form plugin', 'envo-blocks' ),
					value: a.plugin,
					options: pluginOptions,
					onChange: function ( v ) { config.setAttributes( { plugin: v, formId: '' } ); },
				} ) ),
			];

			if ( 'shortcode' === a.plugin ) {
				source.push( el( TextControl, props( {
					key: 'shortcode',
					label: __( 'Form shortcode', 'envo-blocks' ),
					help: __( 'One shortcode only, for example [my_form id="3"]. The fields get the look of the block; how well depends on the form plugin.', 'envo-blocks' ),
					value: a.shortcode,
					onChange: function ( v ) { config.setAttributes( { shortcode: v } ); },
				} ) ) );
			} else if ( current ) {
				source.push( current.forms.length ?
					el( SelectControl, props( {
						key: 'form',
						label: __( 'Form', 'envo-blocks' ),
						value: a.formId,
						options: [ { value: '', label: __( '— Choose —', 'envo-blocks' ) } ].concat( current.forms ),
						onChange: function ( v ) { config.setAttributes( { formId: v } ); },
					} ) ) :
					el( 'p', { key: 'none', className: 'components-base-control__help' }, __( 'This plugin has no published form yet. Make one in its menu and come back.', 'envo-blocks' ) )
				);
			} else if ( a.plugin && plugins ) {
				source.push( el( 'p', { key: 'off', className: 'components-base-control__help' }, __( 'This form plugin is not active.', 'envo-blocks' ) ) );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el( PanelBody, { title: __( 'Form', 'envo-blocks' ), initialOpen: true }, source ),
					el(
						PanelBody,
						{ title: __( 'Fields', 'envo-blocks' ), initialOpen: false },
						f.select( 'fieldStyle', __( 'Field style', 'envo-blocks' ), [
							{ value: 'outline', label: __( 'Border', 'envo-blocks' ) },
							{ value: 'filled', label: __( 'Filled', 'envo-blocks' ) },
							{ value: 'underline', label: __( 'Line under the field', 'envo-blocks' ) },
						] ),
						el( SelectControl, props( {
							label: __( 'Layout', 'envo-blocks' ),
							value: String( a.columns || 1 ),
							options: [
								{ value: '1', label: __( 'One column', 'envo-blocks' ) },
								{ value: '2', label: __( 'Two columns (short fields side by side)', 'envo-blocks' ) },
							],
							onChange: function ( v ) { config.setAttributes( { columns: parseInt( v, 10 ) || 1 } ); },
						} ) ),
						f.range( 'labelSize', __( 'Label size (px, 0 = as the text)', 'envo-blocks' ), 0, 30 ),
						f.range( 'fieldRadius', __( 'Corner radius', 'envo-blocks' ), 0, 40 ),
						f.range( 'fieldPadding', __( 'Space inside the fields', 'envo-blocks' ), 4, 30 ),
						f.range( 'borderWidth', __( 'Border width', 'envo-blocks' ), 0, 6 ),
						f.range( 'fieldGap', __( 'Space between the fields', 'envo-blocks' ), 0, 60 )
					),
					el(
						PanelBody,
						{ title: __( 'Button', 'envo-blocks' ), initialOpen: false },
						f.select( 'buttonWidth', __( 'Width', 'envo-blocks' ), [
							{ value: 'auto', label: __( 'By the text', 'envo-blocks' ) },
							{ value: 'full', label: __( 'Full width', 'envo-blocks' ) },
						] ),
						'full' === a.buttonWidth ? null : f.select( 'buttonAlign', __( 'Alignment', 'envo-blocks' ), ALIGN( true ) ),
						f.range( 'buttonRadius', __( 'Corner radius (-1 = as the theme buttons)', 'envo-blocks' ), -1, 60 )
					)
				),
				f.colors( __( 'Labels and fields', 'envo-blocks' ), [
					[ 'labelColor', __( 'Labels', 'envo-blocks' ) ],
					[ 'fieldText', __( 'Field text', 'envo-blocks' ) ],
					[ 'fieldBackground', __( 'Field background', 'envo-blocks' ) ],
					[ 'fieldBorder', __( 'Field border', 'envo-blocks' ) ],
					[ 'focusColor', __( 'Active field, checkboxes', 'envo-blocks' ) ],
				] ),
				f.colors( __( 'Button colors', 'envo-blocks' ), [
					[ 'buttonBackground', __( 'Background', 'envo-blocks' ) ],
					[ 'buttonText', __( 'Text', 'envo-blocks' ) ],
					[ 'buttonHoverBackground', __( 'Background on hover', 'envo-blocks' ) ],
					[ 'buttonHoverText', __( 'Text on hover', 'envo-blocks' ) ],
				] ),
				f.colors( __( 'Messages', 'envo-blocks' ), [
					[ 'successColor', __( 'Sent', 'envo-blocks' ) ],
					[ 'errorColor', __( 'Errors', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/form-styler', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );
	/* Language Switcher */

	registerBlockType( 'envo-blocks/language-switcher', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Languages', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'Shows the languages of WPML, Polylang or TranslatePress, in their order. Each link opens the same page in the other language.', 'envo-blocks' ) ),
						f.select( 'layout', __( 'Layout', 'envo-blocks' ), [
							{ value: 'dropdown', label: __( 'Dropdown', 'envo-blocks' ) },
							{ value: 'inline', label: __( 'Row of links', 'envo-blocks' ) },
						] ),
						f.select( 'show', __( 'Show', 'envo-blocks' ), [
							{ value: 'flag-name', label: __( 'Flag and name', 'envo-blocks' ) },
							{ value: 'name', label: __( 'Name', 'envo-blocks' ) },
							{ value: 'flag', label: __( 'Flag', 'envo-blocks' ) },
							{ value: 'flag-code', label: __( 'Flag and code', 'envo-blocks' ) },
							{ value: 'code', label: __( 'Code (EN, DE)', 'envo-blocks' ) },
						] ),
						-1 === a.show.indexOf( 'flag' ) ? null : f.select( 'flagShape', __( 'Flag shape', 'envo-blocks' ), [
							{ value: 'rect', label: __( 'Rectangle', 'envo-blocks' ) },
							{ value: 'round', label: __( 'Circle', 'envo-blocks' ) },
						] ),
						'inline' === a.layout ? f.toggle( 'hideCurrent', __( 'Hide the current language', 'envo-blocks' ) ) : null,
						f.toggle( 'hideMissing', __( 'Hide languages without a translation of the page', 'envo-blocks' ), __( 'WPML and Polylang: otherwise such a language links to its home page.', 'envo-blocks' ) ),
						f.select( 'justify', __( 'Alignment', 'envo-blocks' ), ALIGN( true ) ),
						'dropdown' === a.layout ? f.select( 'menuAlign', __( 'Menu opens to the', 'envo-blocks' ), [
							{ value: 'left', label: __( 'Right (aligned left)', 'envo-blocks' ) },
							{ value: 'right', label: __( 'Left (aligned right)', 'envo-blocks' ) },
						] ) : null
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'activeColor', __( 'Current language and hover', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'menuBackground', __( 'Menu background', 'envo-blocks' ) ],
					[ 'menuTextColor', __( 'Menu text', 'envo-blocks' ) ],
					[ 'borderColor', __( 'Border', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/language-switcher', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Tax Switcher */

	registerBlockType( 'envo-blocks/tax-switcher', Object.assign( {}, STYLE_ATTRS, {
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Switch', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'Visitors choose whether the prices are shown with or without tax; every price of the store follows. Shows only when WooCommerce calculates taxes. More in Envo Blocks › Settings › Tax Switcher.', 'envo-blocks' ) ),
						f.select( 'variant', __( 'Style', 'envo-blocks' ), [
							{ value: 'buttons', label: __( 'Two buttons', 'envo-blocks' ) },
							{ value: 'toggle', label: __( 'Switch', 'envo-blocks' ) },
							{ value: 'select', label: __( 'Dropdown', 'envo-blocks' ) },
						] ),
						f.select( 'order', __( 'Order of the options', 'envo-blocks' ), [
							{ value: 'incl', label: __( 'With tax first', 'envo-blocks' ) },
							{ value: 'excl', label: __( 'Without tax first', 'envo-blocks' ) },
						] ),
						f.text( 'inclText', __( 'Text for prices with tax', 'envo-blocks' ), __( 'Empty = "Incl. VAT" (or "Incl. Tax", by the country of the store).', 'envo-blocks' ) ),
						f.text( 'exclText', __( 'Text for prices without tax', 'envo-blocks' ), __( 'Empty = "Excl. VAT" (or "Excl. Tax").', 'envo-blocks' ) ),
						f.toggle( 'showLabel', __( 'Show a label', 'envo-blocks' ) ),
						a.showLabel ? f.text( 'label', __( 'Label', 'envo-blocks' ), null, __( 'Prices', 'envo-blocks' ) ) : null,
						f.select( 'justify', __( 'Alignment', 'envo-blocks' ), ALIGN( true ) ),
						f.range( 'radius', __( 'Corner radius', 'envo-blocks' ), 0, 40 )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'background', __( 'Background', 'envo-blocks' ) ],
					[ 'activeBackground', __( 'Active background', 'envo-blocks' ) ],
					[ 'activeText', __( 'Active text', 'envo-blocks' ) ],
					[ 'borderColor', __( 'Border', 'envo-blocks' ) ],
				] ),
				preview( 'envo-blocks/tax-switcher', a )
			);
		},
		save: function () {
			return null;
		},
	} ) );

	/* Parallax Section */

	// Centered text: WordPress 7.1 keeps it in style.typography.textAlign, older versions in textAlign / align.
	var CENTER = { typography: { textAlign: 'center' } };
	var PLX_TEMPLATE = [
		[ 'core/heading', { level: 2, textAlign: 'center', style: CENTER, content: __( 'Summer collection', 'envo-blocks' ) } ],
		[ 'core/paragraph', { align: 'center', style: CENTER, content: __( 'Light fabrics and bright colors for warm days.', 'envo-blocks' ) } ],
		[ 'core/buttons', { layout: { type: 'flex', justifyContent: 'center' } }, [ [ 'core/button', { text: __( 'Shop now', 'envo-blocks' ) } ] ] ],
	];

	registerBlockType( 'envo-blocks/parallax', Object.assign( {}, STYLE_ATTRS, {
		template: PLX_TEMPLATE,
		edit: function ( config ) {
			var a = config.attributes;
			var set = config.setAttributes;
			var f = makeFields( a, set );
			var FocalPointPicker = wp.components.FocalPointPicker;
			var video = 'video' === a.mediaType;
			var screen = 'screen' === a.heightMode;
			var focal = a.focalPoint || { x: 0.5, y: 0.5 };
			var position = Math.round( focal.x * 100 ) + '% ' + Math.round( focal.y * 100 ) + '%';
			var classes = [ 'sb-plx', 'sb-plx--editor', 'sb-plx--' + ( a.verticalAlign || 'center' ) ];
			if ( ! a.mediaUrl ) {
				classes.push( 'sb-plx--no-media' );
			}
			var style = {
				'--sb-plx-h': ( a.minHeight || 0 ) + ( screen ? 'vh' : 'px' ),
				'--sb-plx-overlay-o': ( undefined === a.overlayOpacity ? 40 : a.overlayOpacity ) / 100,
			};
			if ( a.contentWidth ) {
				style[ '--sb-plx-w' ] = a.contentWidth + 'px';
			}
			[ [ 'overlayColor', '--sb-plx-overlay' ], [ 'textColor', '--sb-plx-text' ], [ 'backgroundColor', '--sb-plx-bg' ] ].forEach( function ( pair ) {
				if ( a[ pair[ 0 ] ] ) {
					style[ pair[ 1 ] ] = a[ pair[ 0 ] ];
				}
			} );
			var blockProps = useBlockProps( { className: classes.join( ' ' ), style: style } );
			var innerProps = useInnerBlocksProps( { className: 'sb-plx__content' }, { template: innerTemplate( PLX_TEMPLATE ) } );

			function pick( media ) {
				var isVideo = !! media && ( 'video' === media.type || /^video\//.test( media.mime || '' ) );
				set( {
					mediaId: media && media.id ? media.id : 0,
					mediaUrl: media && media.url ? media.url : '',
					mediaType: isVideo ? 'video' : 'image',
					mediaAlt: media && media.alt && ! isVideo ? media.alt : '',
				} );
			}

			var media = null;
			if ( a.mediaUrl ) {
				media = video
					? el( 'video', { className: 'sb-plx__img', src: a.mediaUrl, style: { objectPosition: position }, autoPlay: true, muted: true, loop: true, playsInline: true } )
					: el( 'img', { className: 'sb-plx__img', src: a.mediaUrl, alt: '', style: { objectPosition: position } } );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Background', 'envo-blocks' ), initialOpen: true },
						mediaButton( [ 'image', 'video' ], a.mediaId, __( 'Choose an image or a video', 'envo-blocks' ), __( 'Replace the background', 'envo-blocks' ), pick ),
						a.mediaUrl ? el( Button, { variant: 'link', isDestructive: true, className: 'sb-ctl', onClick: function () { pick( null ); } }, __( 'Remove the background', 'envo-blocks' ) ) : null,
						a.mediaUrl && FocalPointPicker
							? el( FocalPointPicker, {
									__nextHasNoMarginBottom: true,
									label: __( 'Focal point', 'envo-blocks' ),
									url: a.mediaUrl,
									value: focal,
									onChange: function ( v ) { set( { focalPoint: v } ); },
							  } )
							: null,
						a.mediaUrl && ! video ? f.text( 'mediaAlt', __( 'Alternative text', 'envo-blocks' ), __( 'Describe the image if it says something the text does not; leave it empty for a decorative background.', 'envo-blocks' ) ) : null,
						f.range( 'overlayOpacity', __( 'Overlay opacity (%)', 'envo-blocks' ), 0, 100, __( 'A darker overlay keeps light text readable on any photo. Its color is under Styles.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Parallax', 'envo-blocks' ), initialOpen: true },
						f.select( 'effect', __( 'Effect while scrolling', 'envo-blocks' ), [
							{ value: 'scroll', label: __( 'Parallax (the background moves slower)', 'envo-blocks' ) },
							{ value: 'zoom', label: __( 'Zoom (the background grows)', 'envo-blocks' ) },
							{ value: 'none', label: __( 'None (a still background)', 'envo-blocks' ) },
						] ),
						'none' !== a.effect ? f.range( 'speed', __( 'Strength', 'envo-blocks' ), 5, 80 ) : null,
						'none' !== a.effect ? f.toggle( 'reverse', 'zoom' === a.effect ? __( 'Reverse (from bigger to normal)', 'envo-blocks' ) : __( 'Reverse (the background moves faster than the page)', 'envo-blocks' ) ) : null,
						'none' !== a.effect ? f.toggle( 'onMobile', __( 'Also on phones', 'envo-blocks' ), __( 'Off: a still background on screens up to 781 px.', 'envo-blocks' ) ) : null,
						el( 'p', { className: 'components-base-control__help' }, __( 'The effect runs on the site; the editor shows a still background. Visitors who turned on "reduce motion" in their system see a still background too.', 'envo-blocks' ) )
					),
					el(
						PanelBody,
						{ title: __( 'Size and content', 'envo-blocks' ), initialOpen: false },
						f.select( 'heightMode', __( 'Minimum height', 'envo-blocks' ), [
							{ value: 'px', label: __( 'In pixels', 'envo-blocks' ) },
							{ value: 'screen', label: __( 'Part of the screen height', 'envo-blocks' ) },
						] ),
						screen ? f.range( 'minHeight', __( 'Minimum height (% of the screen)', 'envo-blocks' ), 20, 100 ) : f.range( 'minHeight', __( 'Minimum height (px)', 'envo-blocks' ), 0, 1200 ),
						f.range( 'contentWidth', __( 'Content width (px)', 'envo-blocks' ), 0, 1600, __( '0 = the content width of the theme.', 'envo-blocks' ) ),
						f.select( 'verticalAlign', __( 'Content position', 'envo-blocks' ), [
							{ value: 'top', label: __( 'Top', 'envo-blocks' ) },
							{ value: 'center', label: __( 'Middle', 'envo-blocks' ) },
							{ value: 'bottom', label: __( 'Bottom', 'envo-blocks' ) },
						] )
					)
				),
				f.colors( __( 'Colors', 'envo-blocks' ), [
					[ 'overlayColor', __( 'Overlay', 'envo-blocks' ) ],
					[ 'textColor', __( 'Text', 'envo-blocks' ) ],
					[ 'backgroundColor', __( 'Background without an image', 'envo-blocks' ) ],
				] ),
				el(
					'div',
					blockProps,
					media ? el( 'div', { className: 'sb-plx__media', 'aria-hidden': 'true' }, media ) : null,
					el( 'span', { className: 'sb-plx__overlay', 'aria-hidden': 'true' } ),
					el( 'div', innerProps )
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} ) );

	/* Sticky Column */

	var STICKY_TEMPLATE = [
		[ 'core/heading', { level: 4, content: __( 'On this page', 'envo-blocks' ) } ],
		[ 'core/list', {}, [
			[ 'core/list-item', { content: __( 'Overview', 'envo-blocks' ) } ],
			[ 'core/list-item', { content: __( 'Details', 'envo-blocks' ) } ],
			[ 'core/list-item', { content: __( 'Questions', 'envo-blocks' ) } ],
		] ],
	];

	registerBlockType( 'envo-blocks/sticky-column', Object.assign( {}, STYLE_ATTRS, {
		template: STICKY_TEMPLATE,
		edit: function ( config ) {
			var a = config.attributes;
			var f = makeFields( a, config.setAttributes );
			var blockProps = useBlockProps( { className: 'sb-sticky sb-sticky--editor' } );
			var innerProps = useInnerBlocksProps( blockProps, { template: innerTemplate( STICKY_TEMPLATE ) } );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Sticking', 'envo-blocks' ), initialOpen: true },
						el( 'p', { className: 'components-base-control__help' }, __( 'The blocks stay in view inside the column or group this block is in, until its end. Put it in the shorter column of a Columns block. The editor shows it without sticking.', 'envo-blocks' ) ),
						f.select( 'offsetMode', __( 'Space at the top', 'envo-blocks' ), [
							{ value: 'auto', label: __( 'Below a sticky header (automatic)', 'envo-blocks' ) },
							{ value: 'custom', label: __( 'From the top of the screen', 'envo-blocks' ) },
						] ),
						f.range( 'offset', 'auto' === a.offsetMode ? __( 'Extra space (px)', 'envo-blocks' ) : __( 'Space from the top (px)', 'envo-blocks' ), 0, 200 ),
						f.range( 'bottomOffset', __( 'Space at the bottom (px)', 'envo-blocks' ), 0, 200, __( 'For content taller than the screen: it floats – while scrolling down it stops this far above the bottom, while scrolling up at the top.', 'envo-blocks' ) ),
						f.select( 'until', __( 'Sticky on', 'envo-blocks' ), [
							{ value: 'mobile', label: __( 'Computers and tablets', 'envo-blocks' ) },
							{ value: 'tablet', label: __( 'Computers only', 'envo-blocks' ) },
							{ value: 'never', label: __( 'Every screen, phones too', 'envo-blocks' ) },
						] )
					)
				),
				el( 'div', innerProps )
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} ) );

	/* "Documentation" at the end of the settings of every block of Envo Blocks and Envo Blocks PRO. */

	var DOCS = window.envoBlocksDocs || {};
	if ( wp.hooks && wp.compose ) {
		wp.hooks.addFilter(
			'editor.BlockEdit',
			'envo-blocks/docs-button',
			wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
				return function ( props ) {
					var url = DOCS[ props.name ];
					if ( ! url || ! props.isSelected ) {
						return el( BlockEdit, props );
					}
					return el(
						Fragment,
						null,
						el( BlockEdit, props ),
						el(
							InspectorControls,
							null,
							el(
								PanelBody,
								{ className: 'envo-docs-link' },
								el( Button, { variant: 'secondary', href: url, target: '_blank', rel: 'noopener noreferrer', icon: 'book-alt' }, __( 'Documentation', 'envo-blocks' ) )
							)
						)
					);
				};
			}, 'withEnvoBlocksDocs' )
		);
	}
} )( window.wp );
