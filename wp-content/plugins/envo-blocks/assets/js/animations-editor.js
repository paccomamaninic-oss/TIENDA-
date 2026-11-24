/**
 * Envo Blocks - the Animation panel of every block (entrance animation), with a preview in the editor.
 * Plain ES5 on the wp.* globals, no build step needed.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var C = wp.components;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var data = window.envoBlocksAnimations || {};
	var names = data.names || {};
	var excluded = data.excluded || [];
	var ATTR = 'envoAnimation';
	var NO_MARGIN = { __nextHasNoMarginBottom: true, __next40pxDefaultSize: true };

	var GROUPS = [
		[ 'fade', __( 'Fading', 'envo-blocks' ) ],
		[ 'zoom', __( 'Zooming', 'envo-blocks' ) ],
		[ 'slide', __( 'Sliding', 'envo-blocks' ) ],
		[ 'bounce', __( 'Bouncing', 'envo-blocks' ) ],
		[ 'flip', __( 'Flipping', 'envo-blocks' ) ],
		[ 'rotate', __( 'Rotating', 'envo-blocks' ) ],
		[ 'special', __( 'Specials', 'envo-blocks' ) ],
		[ 'attention', __( 'Attention seekers', 'envo-blocks' ) ],
	];

	function isExcluded( name ) {
		if ( ! name || excluded.indexOf( name ) > -1 ) {
			return true;
		}
		return /^woocommerce\/(checkout-|cart-|filled-cart|empty-cart|mini-cart-|proceed-to-checkout)/.test( name );
	}

	/** "fadeInUp" -> "Fade In Up". */
	function label( name ) {
		var words = name.replace( /([A-Z])/g, ' $1' );
		return words.charAt( 0 ).toUpperCase() + words.slice( 1 );
	}

	/* ------------------------------------------------------------ the attribute on every block */

	wp.hooks.addFilter( 'blocks.registerBlockType', 'envo-blocks/animation-attribute', function ( settings, name ) {
		if ( isExcluded( name ) || ! settings.attributes || settings.attributes[ ATTR ] ) {
			return settings;
		}
		var attributes = Object.assign( {}, settings.attributes );
		attributes[ ATTR ] = { type: 'object', default: {} };
		return Object.assign( {}, settings, { attributes: attributes } );
	} );

	/* ------------------------------------------------------------ preview in the editor canvas */

	function canvas() {
		var frame = document.querySelector( 'iframe[name="editor-canvas"]' );
		return frame && frame.contentDocument ? frame.contentDocument : document;
	}

	function itemsOf( block ) {
		var best = block;
		var bestCount = block.children.length;
		var level = [ block ];
		for ( var depth = 0; depth < 4; depth++ ) {
			var next = [];
			level.forEach( function ( node ) {
				Array.prototype.forEach.call( node.children, function ( child ) {
					if ( child.children.length > bestCount && ! child.classList.contains( 'block-list-appender' ) ) {
						best = child;
						bestCount = child.children.length;
					}
					next.push( child );
				} );
			} );
			level = next;
		}
		return bestCount > 1 ? Array.prototype.slice.call( best.children ) : [];
	}

	function preview( clientId, s ) {
		var node = canvas().getElementById( 'block-' + clientId );
		if ( ! node || ! s.type ) {
			return;
		}
		var items = s.stagger ? itemsOf( node ) : [];
		var targets = items.length ? items : [ node ];
		var speed = { slow: '1.6s', fast: '0.5s' }[ s.speed ] || '0.9s';
		targets.forEach( function ( target, index ) {
			var cls = [ 'envo-anim-play', 'envo-anim--' + s.type ];
			target.classList.remove.apply( target.classList, cls );
			void target.offsetWidth; // Restart the animation.
			target.style.setProperty( '--envo-anim-duration', speed );
			target.style.setProperty( '--envo-anim-delay', ( ( s.delay || 0 ) + index * ( s.stagger ? s.step || 120 : 0 ) ) + 'ms' );
			target.style.setProperty( '--envo-anim-d', '40px' );
			target.classList.add.apply( target.classList, cls );
			var end = function ( event ) {
				if ( event.target !== target ) {
					return;
				}
				target.removeEventListener( 'animationend', end );
				target.classList.remove.apply( target.classList, cls );
				[ '--envo-anim-duration', '--envo-anim-delay', '--envo-anim-d' ].forEach( function ( prop ) {
					target.style.removeProperty( prop );
				} );
			};
			target.addEventListener( 'animationend', end );
		} );
	}

	/* ------------------------------------------------------------ the panel */

	function Panel( props ) {
		var s = Object.assign( { speed: 'normal', delay: 0, repeat: false, stagger: false, step: 120, desktop: true, tablet: true, mobile: true }, props.attributes[ ATTR ] || {} );
		var set = function ( changes, play ) {
			var next = Object.assign( {}, s, changes );
			var value = next.type ? next : {};
			var attrs = {};
			attrs[ ATTR ] = value;
			props.setAttributes( attrs );
			if ( play && next.type ) {
				window.setTimeout( function () {
					preview( props.clientId, next );
				}, 50 );
			}
		};

		var options = [ el( 'option', { key: '', value: '' }, __( 'None', 'envo-blocks' ) ) ].concat(
			GROUPS.map( function ( group ) {
				return el(
					'optgroup',
					{ key: group[ 0 ], label: group[ 1 ] },
					Object.keys( names )
						.filter( function ( name ) {
							return names[ name ] === group[ 0 ];
						} )
						.map( function ( name ) {
							return el( 'option', { key: name, value: name }, label( name ) );
						} )
				);
			} )
		);

		return el(
			C.PanelBody,
			{ title: __( 'Animation', 'envo-blocks' ), initialOpen: !! s.type, className: 'envo-anim-panel' },
			el(
				'div',
				{ style: { display: 'flex', flexDirection: 'column', gap: '16px' } },
				el(
					C.SelectControl,
					Object.assign( {}, NO_MARGIN, {
						label: __( 'Entrance animation', 'envo-blocks' ),
						help: s.type ? undefined : __( 'Plays when the block comes into view.', 'envo-blocks' ),
						value: s.type || '',
						onChange: function ( v ) {
							set( { type: v }, true );
						},
					} ),
					options
				),
				s.type
					? [
							el( C.SelectControl, Object.assign( { key: 'speed' }, NO_MARGIN, {
								label: __( 'Speed', 'envo-blocks' ),
								value: s.speed,
								options: [
									{ value: 'slow', label: __( 'Slow', 'envo-blocks' ) },
									{ value: 'normal', label: __( 'Normal', 'envo-blocks' ) },
									{ value: 'fast', label: __( 'Fast', 'envo-blocks' ) },
								],
								onChange: function ( v ) {
									set( { speed: v }, true );
								},
							} ) ),
							el( C.RangeControl, Object.assign( { key: 'delay' }, NO_MARGIN, {
								label: __( 'Delay (ms)', 'envo-blocks' ),
								value: s.delay,
								min: 0,
								max: 3000,
								step: 100,
								onChange: function ( v ) {
									set( { delay: v || 0 } );
								},
							} ) ),
							el( C.ToggleControl, {
								key: 'stagger',
								__nextHasNoMarginBottom: true,
								label: __( 'Items one by one', 'envo-blocks' ),
								help: __( 'Columns, products, images or list items come in one after another.', 'envo-blocks' ),
								checked: !! s.stagger,
								onChange: function ( v ) {
									set( { stagger: v }, true );
								},
							} ),
							s.stagger
								? el( C.RangeControl, Object.assign( { key: 'step' }, NO_MARGIN, {
										label: __( 'Time between the items (ms)', 'envo-blocks' ),
										value: s.step,
										min: 50,
										max: 600,
										step: 10,
										onChange: function ( v ) {
											set( { step: v || 120 } );
										},
								  } ) )
								: null,
							el( C.ToggleControl, {
								key: 'repeat',
								__nextHasNoMarginBottom: true,
								label: __( 'Every time it comes into view', 'envo-blocks' ),
								help: s.repeat ? undefined : __( 'Off: only the first time.', 'envo-blocks' ),
								checked: !! s.repeat,
								onChange: function ( v ) {
									set( { repeat: v } );
								},
							} ),
							el(
								C.BaseControl,
								{ key: 'devices', __nextHasNoMarginBottom: true, label: __( 'Play on', 'envo-blocks' ), id: 'envo-anim-devices-' + props.clientId },
								el(
									'div',
									{ style: { display: 'flex', flexDirection: 'column', gap: '8px' } },
									[ [ 'desktop', __( 'Computers', 'envo-blocks' ) ], [ 'tablet', __( 'Tablets', 'envo-blocks' ) ], [ 'mobile', __( 'Phones', 'envo-blocks' ) ] ].map( function ( d ) {
										return el( C.CheckboxControl, {
											key: d[ 0 ],
											__nextHasNoMarginBottom: true,
											label: d[ 1 ],
											checked: false !== s[ d[ 0 ] ],
											onChange: function ( v ) {
												var change = {};
												change[ d[ 0 ] ] = v;
												set( change );
											},
										} );
									} )
								)
							),
							el(
								'div',
								{ key: 'play', style: { display: 'flex', gap: '8px' } },
								el(
									C.Button,
									{
										variant: 'secondary',
										icon: 'controls-play',
										onClick: function () {
											preview( props.clientId, s );
										},
									},
									__( 'Play', 'envo-blocks' )
								),
								el(
									C.Button,
									{
										variant: 'tertiary',
										isDestructive: true,
										onClick: function () {
											set( { type: '' } );
										},
									},
									__( 'Remove', 'envo-blocks' )
								)
							),
					  ]
					: null,
				data.proUrl
					? el(
							'p',
							{ className: 'components-base-control__help', style: { margin: 0 } },
							__( 'Scrolling and mouse effects (parallax, 3D tilt):', 'envo-blocks' ),
							' ',
							el( 'a', { href: data.proUrl }, 'Envo Blocks PRO' )
					  )
					: null
			)
		);
	}

	var withPanel = wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( isExcluded( props.name ) || ! wp.blocks.getBlockType( props.name ) || ! wp.blocks.getBlockType( props.name ).attributes[ ATTR ] ) {
				return el( BlockEdit, props );
			}
			return el(
				Fragment,
				null,
				el( BlockEdit, props ),
				props.isSelected ? el( InspectorControls, null, el( Panel, props ) ) : null
			);
		};
	}, 'withEnvoAnimation' );

	wp.hooks.addFilter( 'editor.BlockEdit', 'envo-blocks/animation-panel', withPanel );
}( window.wp ) );
