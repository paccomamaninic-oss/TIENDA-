<?php
/**
 * "What's new" after an update of the plugin (Envo_Blocks_News): per version a few new features (name + one sentence)
 * and the fixes. Short on purpose – the full list is the changelog. Only versions with real news get an entry.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

return array(
	'1.8.0' => array(
		'new'   => array(
			array( __( 'Store in 5 minutes', 'envo-blocks' ), __( 'A wizard builds your whole store from a template in your brand colour – pages with your products, header, footer, logo and colours. Envo Blocks › Store in 5 minutes.', 'envo-blocks' ) ),
			array( _x( 'Sticky Column', 'block title', 'envo-blocks' ), _x( 'Blocks that stay in view while the page scrolls – inside their column or group, like a sticky sidebar. A tall one floats up and down with the scrolling.', 'block description', 'envo-blocks' ) ),
			array( _x( 'Tax Switcher', 'block title', 'envo-blocks' ), _x( 'Lets visitors see the prices with or without tax (VAT) – a switch, two buttons or a dropdown for the header or the shop. Every price of the store follows. Shows only when WooCommerce calculates taxes.', 'block description', 'envo-blocks' ) ),
			array( __( 'Envo Blocks Store theme', 'envo-blocks' ), __( 'Install and activate our free theme from WordPress.org right in Templates Import.', 'envo-blocks' ) ),
		),
	),
	'1.7.0' => array(
		'new'   => array(
			array( __( 'Parallax Section', 'envo-blocks' ), __( 'An image or video background that moves slower than the page or zooms while scrolling, with any blocks on top.', 'envo-blocks' ) ),
			array( __( 'Form Styler', 'envo-blocks' ), __( 'Forms of Contact Form 7, WPForms, Fluent Forms and other form plugins in the look of your store.', 'envo-blocks' ) ),
			array( __( 'Language Switcher', 'envo-blocks' ), __( 'The languages of WPML, Polylang or TranslatePress in your header or footer.', 'envo-blocks' ) ),
			array( __( 'New templates without updates', 'envo-blocks' ), __( 'Templates Import loads new free templates from our library as soon as they are published.', 'envo-blocks' ) ),
		),
		'fixed' => array(
			__( 'Classic themes: templates and store layouts keep their width, and small block images stay small in Twenty Twenty-One.', 'envo-blocks' ),
		),
	),
);
