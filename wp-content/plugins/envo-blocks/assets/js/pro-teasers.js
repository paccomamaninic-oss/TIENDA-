/**
 * Envo Blocks PRO blocks as locked teasers in the block inserter (only without the PRO plugin).
 * Inserted, a teaser shows a placeholder with a link to the Envo Blocks PRO page; it renders nothing on the site.
 */
( function ( wp ) {
	'use strict';

	const data = window.envoBlocksProTeasers;
	if ( ! wp || ! wp.blocks || ! wp.element || ! data || ! Array.isArray( data.blocks ) ) {
		return;
	}

	const { createElement: el } = wp.element;
	const { __, sprintf } = wp.i18n;
	const { Placeholder, Button, Dashicon } = wp.components;
	const { useBlockProps } = wp.blockEditor;
	const { useDispatch } = wp.data;
	const action = data.action || { url: '', label: __( 'Get Envo Blocks PRO', 'envo-blocks' ), note: '' };

	const lock = el(
		'svg',
		{ className: 'envo-blocks-pro-teaser-icon__lock', viewBox: '0 0 24 24', width: 12, height: 12, 'aria-hidden': 'true', focusable: 'false' },
		el( 'path', { d: 'M7 11V8a5 5 0 0 1 10 0v3M6 11h12v9H6z', fill: 'none', stroke: 'currentColor', strokeWidth: 2.4, strokeLinejoin: 'round' } )
	);

	/** Block icon with a small lock. */
	function teaserIcon( name ) {
		return el( 'span', { className: 'envo-blocks-pro-teaser-icon' }, el( Dashicon, { icon: name } ), lock );
	}

	data.blocks.forEach( function ( block ) {
		if ( wp.blocks.getBlockType( block.name ) && wp.blocks.getBlockType( block.name ).envoBlocksTeaser ) {
			return;
		}

		wp.blocks.registerBlockType( block.name, {
			apiVersion: 3,
			title: block.title,
			description: block.description,
			category: data.category,
			icon: { src: teaserIcon( block.icon ), foreground: '#6d28d9' },
			keywords: [ 'pro', 'envo-blocks' ],
			envoBlocksTeaser: true,
			supports: {
				inserter: !! block.inserter,
				html: false,
				reusable: false,
				lock: false,
			},
			edit: function ( props ) {
				const blockProps = useBlockProps( { className: 'envo-blocks-pro-teaser' } );
				const { removeBlock } = useDispatch( 'core/block-editor' );

				return el(
					'div',
					blockProps,
					el(
						Placeholder,
						{
							icon: teaserIcon( block.icon ),
							label: sprintf( /* translators: %s: block name. */ __( '%s is a Envo Blocks PRO block', 'envo-blocks' ), block.title ),
							instructions: block.description,
							className: 'envo-blocks-pro-teaser__placeholder',
						},
						el(
							'div',
							{ className: 'envo-blocks-pro-teaser__actions' },
							el( Button, { variant: 'primary', href: action.url, target: '_blank', rel: 'noopener noreferrer', className: 'envo-blocks-pro-teaser__get' }, action.label ),
							block.demo ? el( Button, { variant: 'secondary', href: block.demo, target: '_blank', rel: 'noopener noreferrer' }, __( 'Live demo', 'envo-blocks' ) ) : null,
							el(
								Button,
								{
									variant: 'tertiary',
									onClick: function () {
										removeBlock( props.clientId );
									},
								},
								__( 'Remove block', 'envo-blocks' )
							)
						),
						el( 'p', { className: 'envo-blocks-pro-teaser__note' }, ( action.note ? action.note + ' ' : '' ) + __( 'This placeholder is not shown on your site.', 'envo-blocks' ) )
					)
				);
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp );
