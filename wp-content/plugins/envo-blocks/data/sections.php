<?php
/**
 * Blocks (page sections) for the "Sections" tab: the header, the footer and every section of the free
 * template demos. Inserted where the user is editing; "demo" links the card to the live demo.
 *
 * Made from the demos (Store Demos › Template Library › Plugin files, or sbp-demo-tools/free-library/build.py)
 * – edit the demos, not this file.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_woocommerce = array(
	array(
		'slug' => 'woocommerce',
		'name' => 'WooCommerce',
	),
);

return array(
	array(
		'name'             => __( 'Stride – Header', 'envo-blocks' ),
		'file'             => 'stride-01-header.json',
		'image'            => 'stride-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'stride',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Stride – Hero: New season is here', 'envo-blocks' ),
		'file'     => 'stride-02-hero.json',
		'image'    => 'stride-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'stride',
	),
	array(
		'name'     => __( 'Stride – Features', 'envo-blocks' ),
		'file'     => 'stride-03-features.json',
		'image'    => 'stride-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'stride',
	),
	array(
		'name'             => __( 'Stride – Banner', 'envo-blocks' ),
		'file'             => 'stride-04-banner.json',
		'image'            => 'stride-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'stride',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Stride – Products: Recent Products', 'envo-blocks' ),
		'file'             => 'stride-05-products.json',
		'image'            => 'stride-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'stride',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Stride – Banner: New Sneakers in stock', 'envo-blocks' ),
		'file'     => 'stride-06-banner.json',
		'image'    => 'stride-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'stride',
	),
	array(
		'name'             => __( 'Stride – Products: Featured Products', 'envo-blocks' ),
		'file'             => 'stride-07-products.json',
		'image'            => 'stride-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'stride',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Stride – About: Welcome to Stride', 'envo-blocks' ),
		'file'     => 'stride-08-about.json',
		'image'    => 'stride-08-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'stride',
	),
	array(
		'name'             => __( 'Stride – Products: Best Sellers', 'envo-blocks' ),
		'file'             => 'stride-09-products.json',
		'image'            => 'stride-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'stride',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Stride – Contact', 'envo-blocks' ),
		'file'     => 'stride-10-contact.json',
		'image'    => 'stride-10-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'stride',
	),
	array(
		'name'     => __( 'Stride – Footer', 'envo-blocks' ),
		'file'     => 'stride-11-footer.json',
		'image'    => 'stride-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'stride',
	),
	array(
		'name'             => __( 'Motion – Header', 'envo-blocks' ),
		'file'             => 'motion-01-header.json',
		'image'            => 'motion-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'motion',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Motion – Hero: Categories', 'envo-blocks' ),
		'file'     => 'motion-02-hero.json',
		'image'    => 'motion-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'motion',
	),
	array(
		'name'     => __( 'Motion – Features', 'envo-blocks' ),
		'file'     => 'motion-03-features.json',
		'image'    => 'motion-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'motion',
	),
	array(
		'name'             => __( 'Motion – Banner', 'envo-blocks' ),
		'file'             => 'motion-04-banner.json',
		'image'            => 'motion-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'motion',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motion – Products: Hand-picked', 'envo-blocks' ),
		'file'             => 'motion-05-products.json',
		'image'            => 'motion-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'motion',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motion – Products: Summer Collection', 'envo-blocks' ),
		'file'             => 'motion-06-products.json',
		'image'            => 'motion-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'motion',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Motion – About: Our labels', 'envo-blocks' ),
		'file'     => 'motion-07-about.json',
		'image'    => 'motion-07-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'motion',
	),
	array(
		'name'             => __( 'Motion – Products: Summer edit', 'envo-blocks' ),
		'file'             => 'motion-08-products.json',
		'image'            => 'motion-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'motion',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motion – Products: Most popular', 'envo-blocks' ),
		'file'             => 'motion-09-products.json',
		'image'            => 'motion-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'motion',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Motion – Blog: Journal', 'envo-blocks' ),
		'file'     => 'motion-10-blog.json',
		'image'    => 'motion-10-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'motion',
	),
	array(
		'name'     => __( 'Motion – Footer', 'envo-blocks' ),
		'file'     => 'motion-11-footer.json',
		'image'    => 'motion-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'motion',
	),
	array(
		'name'             => __( 'Flexa – Header', 'envo-blocks' ),
		'file'             => 'flexa-01-header.json',
		'image'            => 'flexa-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'flexa',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Flexa – Hero: New fitness trends', 'envo-blocks' ),
		'file'     => 'flexa-02-hero.json',
		'image'    => 'flexa-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'flexa',
	),
	array(
		'name'             => __( 'Flexa – Banner', 'envo-blocks' ),
		'file'             => 'flexa-03-banner.json',
		'image'            => 'flexa-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'flexa',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Flexa – Products: On Sale', 'envo-blocks' ),
		'file'             => 'flexa-04-products.json',
		'image'            => 'flexa-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'flexa',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Flexa – Banner: Outdoor season', 'envo-blocks' ),
		'file'     => 'flexa-05-banner.json',
		'image'    => 'flexa-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'flexa',
	),
	array(
		'name'     => __( 'Flexa – About: Welcome to Flexa', 'envo-blocks' ),
		'file'     => 'flexa-06-about.json',
		'image'    => 'flexa-06-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'flexa',
	),
	array(
		'name'             => __( 'Flexa – Products: Coach picks', 'envo-blocks' ),
		'file'             => 'flexa-07-products.json',
		'image'            => 'flexa-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'flexa',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Flexa – Products: On Sale Products', 'envo-blocks' ),
		'file'             => 'flexa-08-products.json',
		'image'            => 'flexa-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'flexa',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Flexa – Footer', 'envo-blocks' ),
		'file'     => 'flexa-09-footer.json',
		'image'    => 'flexa-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'flexa',
	),
	array(
		'name'             => __( 'Sprint – Header', 'envo-blocks' ),
		'file'             => 'sprint-01-header.json',
		'image'            => 'sprint-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'sprint',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Sprint – Hero: Categories', 'envo-blocks' ),
		'file'     => 'sprint-02-hero.json',
		'image'    => 'sprint-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'sprint',
	),
	array(
		'name'     => __( 'Sprint – Features', 'envo-blocks' ),
		'file'     => 'sprint-03-features.json',
		'image'    => 'sprint-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'sprint',
	),
	array(
		'name'             => __( 'Sprint – Banner', 'envo-blocks' ),
		'file'             => 'sprint-04-banner.json',
		'image'            => 'sprint-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'sprint',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sprint – Products: Fashion edit', 'envo-blocks' ),
		'file'             => 'sprint-05-products.json',
		'image'            => 'sprint-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sprint',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Sprint – Banner: Spring', 'envo-blocks' ),
		'file'     => 'sprint-06-banner.json',
		'image'    => 'sprint-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'sprint',
	),
	array(
		'name'     => __( 'Sprint – Banner: Fresh drops', 'envo-blocks' ),
		'file'     => 'sprint-07-banner.json',
		'image'    => 'sprint-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'sprint',
	),
	array(
		'name'             => __( 'Sprint – Products: Rain Ready', 'envo-blocks' ),
		'file'             => 'sprint-08-products.json',
		'image'            => 'sprint-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sprint',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sprint – Products: Most loved', 'envo-blocks' ),
		'file'             => 'sprint-09-products.json',
		'image'            => 'sprint-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sprint',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Sprint – Blog: Style notes', 'envo-blocks' ),
		'file'     => 'sprint-10-blog.json',
		'image'    => 'sprint-10-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'sprint',
	),
	array(
		'name'     => __( 'Sprint – Footer', 'envo-blocks' ),
		'file'     => 'sprint-11-footer.json',
		'image'    => 'sprint-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'sprint',
	),
	array(
		'name'             => __( 'Gadgetry – Header', 'envo-blocks' ),
		'file'             => 'gadgetry-01-header.json',
		'image'            => 'gadgetry-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'gadgetry',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gadgetry – Hero: End of season', 'envo-blocks' ),
		'file'     => 'gadgetry-02-hero.json',
		'image'    => 'gadgetry-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'gadgetry',
	),
	array(
		'name'     => __( 'Gadgetry – Banner: Phones', 'envo-blocks' ),
		'file'     => 'gadgetry-03-banner.json',
		'image'    => 'gadgetry-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gadgetry',
	),
	array(
		'name'             => __( 'Gadgetry – Banner', 'envo-blocks' ),
		'file'             => 'gadgetry-04-banner.json',
		'image'            => 'gadgetry-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'gadgetry',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Gadgetry – Products: Best Sellers', 'envo-blocks' ),
		'file'             => 'gadgetry-05-products.json',
		'image'            => 'gadgetry-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gadgetry',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gadgetry – Banner: Smart watches', 'envo-blocks' ),
		'file'     => 'gadgetry-06-banner.json',
		'image'    => 'gadgetry-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gadgetry',
	),
	array(
		'name'             => __( 'Gadgetry – Products: Shop by category', 'envo-blocks' ),
		'file'             => 'gadgetry-07-products.json',
		'image'            => 'gadgetry-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gadgetry',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gadgetry – Banner: Weekend deal', 'envo-blocks' ),
		'file'     => 'gadgetry-08-banner.json',
		'image'    => 'gadgetry-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gadgetry',
	),
	array(
		'name'             => __( 'Gadgetry – Products: Recent Products', 'envo-blocks' ),
		'file'             => 'gadgetry-09-products.json',
		'image'            => 'gadgetry-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gadgetry',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gadgetry – Footer', 'envo-blocks' ),
		'file'     => 'gadgetry-10-footer.json',
		'image'    => 'gadgetry-10-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'gadgetry',
	),
	array(
		'name'             => __( 'Circuit – Header', 'envo-blocks' ),
		'file'             => 'circuit-01-header.json',
		'image'            => 'circuit-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'circuit',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Circuit – Hero: Categories', 'envo-blocks' ),
		'file'     => 'circuit-02-hero.json',
		'image'    => 'circuit-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'circuit',
	),
	array(
		'name'     => __( 'Circuit – Banner: New drones in stock', 'envo-blocks' ),
		'file'     => 'circuit-03-banner.json',
		'image'    => 'circuit-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'circuit',
	),
	array(
		'name'             => __( 'Circuit – Banner', 'envo-blocks' ),
		'file'             => 'circuit-04-banner.json',
		'image'            => 'circuit-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'circuit',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Circuit – Products: Recent Products', 'envo-blocks' ),
		'file'             => 'circuit-05-products.json',
		'image'            => 'circuit-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'circuit',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Circuit – About: Why Circuit', 'envo-blocks' ),
		'file'     => 'circuit-06-about.json',
		'image'    => 'circuit-06-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'circuit',
	),
	array(
		'name'             => __( 'Circuit – Products: Top Rated', 'envo-blocks' ),
		'file'             => 'circuit-07-products.json',
		'image'            => 'circuit-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'circuit',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Circuit – Banner: Pocket drones', 'envo-blocks' ),
		'file'     => 'circuit-08-banner.json',
		'image'    => 'circuit-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'circuit',
	),
	array(
		'name'     => __( 'Circuit – Features', 'envo-blocks' ),
		'file'     => 'circuit-09-features.json',
		'image'    => 'circuit-09-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'circuit',
	),
	array(
		'name'             => __( 'Circuit – Products: On Sale', 'envo-blocks' ),
		'file'             => 'circuit-10-products.json',
		'image'            => 'circuit-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'circuit',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Circuit – Contact: Need help?', 'envo-blocks' ),
		'file'     => 'circuit-11-contact.json',
		'image'    => 'circuit-11-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'circuit',
	),
	array(
		'name'     => __( 'Circuit – Footer', 'envo-blocks' ),
		'file'     => 'circuit-12-footer.json',
		'image'    => 'circuit-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'circuit',
	),
	array(
		'name'             => __( 'Couture – Header', 'envo-blocks' ),
		'file'             => 'couture-01-header.json',
		'image'            => 'couture-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'couture',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Couture – Hero: The summer edit', 'envo-blocks' ),
		'file'     => 'couture-02-hero.json',
		'image'    => 'couture-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'couture',
	),
	array(
		'name'     => __( 'Couture – Features', 'envo-blocks' ),
		'file'     => 'couture-03-features.json',
		'image'    => 'couture-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'couture',
	),
	array(
		'name'             => __( 'Couture – Banner', 'envo-blocks' ),
		'file'             => 'couture-04-banner.json',
		'image'            => 'couture-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'couture',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Couture – Products: Shop by category', 'envo-blocks' ),
		'file'             => 'couture-05-products.json',
		'image'            => 'couture-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'couture',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Couture – About: Our promise', 'envo-blocks' ),
		'file'     => 'couture-06-about.json',
		'image'    => 'couture-06-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'couture',
	),
	array(
		'name'             => __( 'Couture – Products: New In', 'envo-blocks' ),
		'file'             => 'couture-07-products.json',
		'image'            => 'couture-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'couture',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Couture – Banner: Denim days', 'envo-blocks' ),
		'file'     => 'couture-08-banner.json',
		'image'    => 'couture-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'couture',
	),
	array(
		'name'             => __( 'Couture – Products: On Sale Products', 'envo-blocks' ),
		'file'             => 'couture-09-products.json',
		'image'            => 'couture-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'couture',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Couture – Features: Our promise', 'envo-blocks' ),
		'file'     => 'couture-10-features.json',
		'image'    => 'couture-10-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'couture',
	),
	array(
		'name'     => __( 'Couture – Footer', 'envo-blocks' ),
		'file'     => 'couture-11-footer.json',
		'image'    => 'couture-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'couture',
	),
	array(
		'name'             => __( 'Velvet – Header', 'envo-blocks' ),
		'file'             => 'velvet-01-header.json',
		'image'            => 'velvet-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'velvet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Velvet – Hero: Categories', 'envo-blocks' ),
		'file'     => 'velvet-02-hero.json',
		'image'    => 'velvet-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'velvet',
	),
	array(
		'name'     => __( 'Velvet – Features', 'envo-blocks' ),
		'file'     => 'velvet-03-features.json',
		'image'    => 'velvet-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'velvet',
	),
	array(
		'name'             => __( 'Velvet – Banner', 'envo-blocks' ),
		'file'             => 'velvet-04-banner.json',
		'image'            => 'velvet-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'velvet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velvet – Products: Party Picks', 'envo-blocks' ),
		'file'             => 'velvet-05-products.json',
		'image'            => 'velvet-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'velvet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Velvet – About: Welcome to Velvet', 'envo-blocks' ),
		'file'     => 'velvet-06-about.json',
		'image'    => 'velvet-06-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'velvet',
	),
	array(
		'name'             => __( 'Velvet – Products: Just Arrived', 'envo-blocks' ),
		'file'             => 'velvet-07-products.json',
		'image'            => 'velvet-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'velvet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Velvet – Blog: The journal', 'envo-blocks' ),
		'file'     => 'velvet-08-blog.json',
		'image'    => 'velvet-08-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'velvet',
	),
	array(
		'name'     => __( 'Velvet – Footer', 'envo-blocks' ),
		'file'     => 'velvet-09-footer.json',
		'image'    => 'velvet-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'velvet',
	),
	array(
		'name'             => __( 'Fitora – Header', 'envo-blocks' ),
		'file'             => 'fitora-01-header.json',
		'image'            => 'fitora-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'fitora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Fitora – Hero: Up to 40% off', 'envo-blocks' ),
		'file'     => 'fitora-02-hero.json',
		'image'    => 'fitora-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'fitora',
	),
	array(
		'name'             => __( 'Fitora – Banner', 'envo-blocks' ),
		'file'             => 'fitora-03-banner.json',
		'image'            => 'fitora-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'fitora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fitora – Products: This week\'s deals', 'envo-blocks' ),
		'file'             => 'fitora-04-products.json',
		'image'            => 'fitora-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'fitora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fitora – Products: Team favourites', 'envo-blocks' ),
		'file'             => 'fitora-05-products.json',
		'image'            => 'fitora-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'fitora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Fitora – Banner: Home gym', 'envo-blocks' ),
		'file'     => 'fitora-06-banner.json',
		'image'    => 'fitora-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'fitora',
	),
	array(
		'name'     => __( 'Fitora – Contact', 'envo-blocks' ),
		'file'     => 'fitora-07-contact.json',
		'image'    => 'fitora-07-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'fitora',
	),
	array(
		'name'     => __( 'Fitora – Footer', 'envo-blocks' ),
		'file'     => 'fitora-08-footer.json',
		'image'    => 'fitora-08-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'fitora',
	),
	array(
		'name'             => __( 'Northbound – Header', 'envo-blocks' ),
		'file'             => 'northbound-01-header.json',
		'image'            => 'northbound-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'northbound',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Northbound – Hero: Senior partners only', 'envo-blocks' ),
		'file'     => 'northbound-02-hero.json',
		'image'    => 'northbound-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'     => __( 'Northbound – Banner: Halvard', 'envo-blocks' ),
		'file'     => 'northbound-03-banner.json',
		'image'    => 'northbound-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'     => __( 'Northbound – Banner: What we do', 'envo-blocks' ),
		'file'     => 'northbound-04-banner.json',
		'image'    => 'northbound-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'     => __( 'Northbound – About: Our method', 'envo-blocks' ),
		'file'     => 'northbound-05-about.json',
		'image'    => 'northbound-05-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'     => __( 'Northbound – Banner: Results', 'envo-blocks' ),
		'file'     => 'northbound-06-banner.json',
		'image'    => 'northbound-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'     => __( 'Northbound – Banner: The partners', 'envo-blocks' ),
		'file'     => 'northbound-07-banner.json',
		'image'    => 'northbound-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'     => __( 'Northbound – Pricing: Packages', 'envo-blocks' ),
		'file'     => 'northbound-08-pricing.json',
		'image'    => 'northbound-08-pricing.webp',
		'category' => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'             => __( 'Northbound – Products: The reading list', 'envo-blocks' ),
		'file'             => 'northbound-09-products.json',
		'image'            => 'northbound-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'northbound',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Northbound – Banner: Let’s talk', 'envo-blocks' ),
		'file'     => 'northbound-10-banner.json',
		'image'    => 'northbound-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'     => __( 'Northbound – Footer', 'envo-blocks' ),
		'file'     => 'northbound-11-footer.json',
		'image'    => 'northbound-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'northbound',
	),
	array(
		'name'             => __( 'Daybreak – Header', 'envo-blocks' ),
		'file'             => 'daybreak-01-header.json',
		'image'            => 'daybreak-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'daybreak',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Daybreak – Hero: Slow mornings, good coffee.', 'envo-blocks' ),
		'file'     => 'daybreak-02-hero.json',
		'image'    => 'daybreak-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'daybreak',
	),
	array(
		'name'     => __( 'Daybreak – About: Our story', 'envo-blocks' ),
		'file'     => 'daybreak-03-about.json',
		'image'    => 'daybreak-03-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'daybreak',
	),
	array(
		'name'     => __( 'Daybreak – Banner: The menu', 'envo-blocks' ),
		'file'     => 'daybreak-04-banner.json',
		'image'    => 'daybreak-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'daybreak',
	),
	array(
		'name'     => __( 'Daybreak – Banner: From the bakery', 'envo-blocks' ),
		'file'     => 'daybreak-05-banner.json',
		'image'    => 'daybreak-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'daybreak',
	),
	array(
		'name'     => __( 'Daybreak – Banner: Events & the back room', 'envo-blocks' ),
		'file'     => 'daybreak-06-banner.json',
		'image'    => 'daybreak-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'daybreak',
	),
	array(
		'name'             => __( 'Daybreak – Products: Take some home', 'envo-blocks' ),
		'file'             => 'daybreak-07-products.json',
		'image'            => 'daybreak-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'daybreak',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Daybreak – Contact: Visit us', 'envo-blocks' ),
		'file'     => 'daybreak-08-contact.json',
		'image'    => 'daybreak-08-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'daybreak',
	),
	array(
		'name'     => __( 'Daybreak – Footer', 'envo-blocks' ),
		'file'     => 'daybreak-09-footer.json',
		'image'    => 'daybreak-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'daybreak',
	),
	array(
		'name'             => __( 'Linea – Header', 'envo-blocks' ),
		'file'             => 'linea-01-header.json',
		'image'            => 'linea-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'linea',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Linea – Hero: Rooms that feel like you.', 'envo-blocks' ),
		'file'     => 'linea-02-hero.json',
		'image'    => 'linea-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
	array(
		'name'     => __( 'Linea – Banner', 'envo-blocks' ),
		'file'     => 'linea-03-banner.json',
		'image'    => 'linea-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
	array(
		'name'     => __( 'Linea – Banner: The studio', 'envo-blocks' ),
		'file'     => 'linea-04-banner.json',
		'image'    => 'linea-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
	array(
		'name'     => __( 'Linea – Banner: Services', 'envo-blocks' ),
		'file'     => 'linea-05-banner.json',
		'image'    => 'linea-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
	array(
		'name'     => __( 'Linea – Banner: Selected projects', 'envo-blocks' ),
		'file'     => 'linea-06-banner.json',
		'image'    => 'linea-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
	array(
		'name'     => __( 'Linea – Banner: Process', 'envo-blocks' ),
		'file'     => 'linea-07-banner.json',
		'image'    => 'linea-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
	array(
		'name'     => __( 'Linea – Banner: Work with us', 'envo-blocks' ),
		'file'     => 'linea-08-banner.json',
		'image'    => 'linea-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
	array(
		'name'             => __( 'Linea – Products: Studio shop', 'envo-blocks' ),
		'file'             => 'linea-09-products.json',
		'image'            => 'linea-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'linea',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Linea – Banner', 'envo-blocks' ),
		'file'     => 'linea-10-banner.json',
		'image'    => 'linea-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
	array(
		'name'     => __( 'Linea – Footer', 'envo-blocks' ),
		'file'     => 'linea-11-footer.json',
		'image'    => 'linea-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'linea',
	),
);
