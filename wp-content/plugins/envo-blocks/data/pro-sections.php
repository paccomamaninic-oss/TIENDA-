<?php
/**
 * Teasers of the PRO sections, listed while the PRO plugin is not installed: a preview with a PRO label,
 * the live demo and a "Buy PRO" button. The JSON files are in the remote library. Copy of data/sections.php of
 * Envo Blocks PRO (same keys as data/sections.php of this plugin).
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
		'name'             => __( 'Zentro – Header', 'envo-blocks' ),
		'file'             => 'zentro-01-header.json',
		'image'            => 'zentro-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'zentro',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Zentro – Hero: The new', 'envo-blocks' ),
		'file'             => 'zentro-02-hero.json',
		'image'            => 'zentro-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'zentro',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Zentro – Banner: Shop now ›', 'envo-blocks' ),
		'file'     => 'zentro-03-banner.json',
		'image'    => 'zentro-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'zentro',
	),
	array(
		'name'             => __( 'Zentro – Products: Special offer', 'envo-blocks' ),
		'file'             => 'zentro-04-products.json',
		'image'            => 'zentro-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'zentro',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Zentro – Deals: Deal of the week', 'envo-blocks' ),
		'file'             => 'zentro-05-deals.json',
		'image'            => 'zentro-05-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'zentro',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Zentro – Products: Best sellers', 'envo-blocks' ),
		'file'             => 'zentro-07-products.json',
		'image'            => 'zentro-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'zentro',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Zentro – Banner: Starting at $286 29', 'envo-blocks' ),
		'file'     => 'zentro-08-banner.json',
		'image'    => 'zentro-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'zentro',
	),
	array(
		'name'             => __( 'Zentro – Products: Recently added', 'envo-blocks' ),
		'file'             => 'zentro-09-products.json',
		'image'            => 'zentro-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'zentro',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Zentro – Brands', 'envo-blocks' ),
		'file'             => 'zentro-10-brands.json',
		'image'            => 'zentro-10-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'zentro',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Zentro – Products: Top rated', 'envo-blocks' ),
		'file'             => 'zentro-11-products.json',
		'image'            => 'zentro-11-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'zentro',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Zentro – Footer', 'envo-blocks' ),
		'file'     => 'zentro-13-footer.json',
		'image'    => 'zentro-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'zentro',
	),
	array(
		'name'             => __( 'Vyra – Header', 'envo-blocks' ),
		'file'             => 'vyra-01-header.json',
		'image'            => 'vyra-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'vyra',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Vyra – Hero: New · Vyra One', 'envo-blocks' ),
		'file'     => 'vyra-02-hero.json',
		'image'    => 'vyra-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'vyra',
	),
	array(
		'name'     => __( 'Vyra – Banner: 4k +', 'envo-blocks' ),
		'file'     => 'vyra-03-banner.json',
		'image'    => 'vyra-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'vyra',
	),
	array(
		'name'     => __( 'Vyra – Banner: The headset', 'envo-blocks' ),
		'file'     => 'vyra-04-banner.json',
		'image'    => 'vyra-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'vyra',
	),
	array(
		'name'     => __( 'Vyra – Banner: Experience', 'envo-blocks' ),
		'file'     => 'vyra-05-banner.json',
		'image'    => 'vyra-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'vyra',
	),
	array(
		'name'     => __( 'Vyra – Banner: Mixed reality', 'envo-blocks' ),
		'file'     => 'vyra-06-banner.json',
		'image'    => 'vyra-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'vyra',
	),
	array(
		'name'     => __( 'Vyra – Banner: Editions', 'envo-blocks' ),
		'file'     => 'vyra-07-banner.json',
		'image'    => 'vyra-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'vyra',
	),
	array(
		'name'     => __( 'Vyra – Banner: Specifications', 'envo-blocks' ),
		'file'     => 'vyra-08-banner.json',
		'image'    => 'vyra-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'vyra',
	),
	array(
		'name'             => __( 'Vyra – Reviews', 'envo-blocks' ),
		'file'             => 'vyra-09-reviews.json',
		'image'            => 'vyra-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'vyra',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Vyra – FAQ', 'envo-blocks' ),
		'file'     => 'vyra-10-faq.json',
		'image'    => 'vyra-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'vyra',
	),
	array(
		'name'             => __( 'Vyra – Footer', 'envo-blocks' ),
		'file'             => 'vyra-12-footer.json',
		'image'            => 'vyra-12-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'vyra',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pulse Store – Header', 'envo-blocks' ),
		'file'             => 'pulse-store-01-header.json',
		'image'            => 'pulse-store-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'pulse-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pulse Store – Hero', 'envo-blocks' ),
		'file'             => 'pulse-store-02-hero.json',
		'image'            => 'pulse-store-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'pulse-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pulse Store – Banner: Summer offer', 'envo-blocks' ),
		'file'     => 'pulse-store-03-banner.json',
		'image'    => 'pulse-store-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pulse-store',
	),
	array(
		'name'             => __( 'Pulse Store – Products: Hot right now', 'envo-blocks' ),
		'file'             => 'pulse-store-04-products.json',
		'image'            => 'pulse-store-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'pulse-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pulse Store – Banner: Just arrived', 'envo-blocks' ),
		'file'     => 'pulse-store-05-banner.json',
		'image'    => 'pulse-store-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pulse-store',
	),
	array(
		'name'             => __( 'Pulse Store – Brands: Trusted by', 'envo-blocks' ),
		'file'             => 'pulse-store-06-brands.json',
		'image'            => 'pulse-store-06-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'pulse-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pulse Store – Products: Upgrade day', 'envo-blocks' ),
		'file'             => 'pulse-store-07-products.json',
		'image'            => 'pulse-store-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'pulse-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pulse Store – Contact', 'envo-blocks' ),
		'file'     => 'pulse-store-08-contact.json',
		'image'    => 'pulse-store-08-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'pulse-store',
	),
	array(
		'name'     => __( 'Pulse Store – Footer', 'envo-blocks' ),
		'file'     => 'pulse-store-09-footer.json',
		'image'    => 'pulse-store-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'pulse-store',
	),
	array(
		'name'             => __( 'Sorbet – Header', 'envo-blocks' ),
		'file'             => 'sorbet-01-header.json',
		'image'            => 'sorbet-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Hero: New in', 'envo-blocks' ),
		'file'             => 'sorbet-02-hero.json',
		'image'            => 'sorbet-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Banner: Weekend dresses', 'envo-blocks' ),
		'file'             => 'sorbet-03-banner.json',
		'image'            => 'sorbet-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Banner: Popular categories', 'envo-blocks' ),
		'file'             => 'sorbet-04-banner.json',
		'image'            => 'sorbet-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Products: Latest dresses', 'envo-blocks' ),
		'file'             => 'sorbet-05-products.json',
		'image'            => 'sorbet-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Banner: 25% off', 'envo-blocks' ),
		'file'             => 'sorbet-06-banner.json',
		'image'            => 'sorbet-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Products: Up to 20% off', 'envo-blocks' ),
		'file'             => 'sorbet-07-products.json',
		'image'            => 'sorbet-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Banner: Up to 25% off', 'envo-blocks' ),
		'file'             => 'sorbet-08-banner.json',
		'image'            => 'sorbet-08-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Deals: Deal of the day', 'envo-blocks' ),
		'file'             => 'sorbet-09-deals.json',
		'image'            => 'sorbet-09-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Banner', 'envo-blocks' ),
		'file'             => 'sorbet-10-banner.json',
		'image'            => 'sorbet-10-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sorbet – Products: New season', 'envo-blocks' ),
		'file'             => 'sorbet-11-products.json',
		'image'            => 'sorbet-11-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sorbet',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Sorbet – Blog: From the journal', 'envo-blocks' ),
		'file'     => 'sorbet-12-blog.json',
		'image'    => 'sorbet-12-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'sorbet',
	),
	array(
		'name'     => __( 'Sorbet – About: Brands we love', 'envo-blocks' ),
		'file'     => 'sorbet-13-about.json',
		'image'    => 'sorbet-13-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'sorbet',
	),
	array(
		'name'     => __( 'Sorbet – Footer', 'envo-blocks' ),
		'file'     => 'sorbet-15-footer.json',
		'image'    => 'sorbet-15-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'sorbet',
	),
	array(
		'name'             => __( 'Apex – Header', 'envo-blocks' ),
		'file'             => 'apex-01-header.json',
		'image'            => 'apex-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'apex',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Apex – Hero: Sportbike · 2026', 'envo-blocks' ),
		'file'     => 'apex-02-hero.json',
		'image'    => 'apex-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'     => __( 'Apex – Banner: 200 hp', 'envo-blocks' ),
		'file'     => 'apex-03-banner.json',
		'image'    => 'apex-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'     => __( 'Apex – Banner', 'envo-blocks' ),
		'file'     => 'apex-04-banner.json',
		'image'    => 'apex-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'     => __( 'Apex – Banner: Engine', 'envo-blocks' ),
		'file'     => 'apex-05-banner.json',
		'image'    => 'apex-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'             => __( 'Apex – Banner: 360° tour', 'envo-blocks' ),
		'file'             => 'apex-06-banner.json',
		'image'            => 'apex-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'apex',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Apex – Banner', 'envo-blocks' ),
		'file'     => 'apex-07-banner.json',
		'image'    => 'apex-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'     => __( 'Apex – Banner: Track-bred', 'envo-blocks' ),
		'file'     => 'apex-08-banner.json',
		'image'    => 'apex-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'             => __( 'Apex – Banner: Specifications', 'envo-blocks' ),
		'file'             => 'apex-09-banner.json',
		'image'            => 'apex-09-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'apex',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Apex – Products: Gear up', 'envo-blocks' ),
		'file'             => 'apex-10-products.json',
		'image'            => 'apex-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'apex',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Apex – Banner', 'envo-blocks' ),
		'file'     => 'apex-11-banner.json',
		'image'    => 'apex-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'             => __( 'Apex – Reviews', 'envo-blocks' ),
		'file'             => 'apex-12-reviews.json',
		'image'            => 'apex-12-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'apex',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Apex – Products: Order', 'envo-blocks' ),
		'file'             => 'apex-13-products.json',
		'image'            => 'apex-13-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'apex',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Apex – Banner', 'envo-blocks' ),
		'file'     => 'apex-14-banner.json',
		'image'    => 'apex-14-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'     => __( 'Apex – Banner: Test ride', 'envo-blocks' ),
		'file'     => 'apex-15-banner.json',
		'image'    => 'apex-15-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'apex',
	),
	array(
		'name'             => __( 'Apex – Footer', 'envo-blocks' ),
		'file'             => 'apex-17-footer.json',
		'image'            => 'apex-17-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'apex',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motivo Sport – Header', 'envo-blocks' ),
		'file'             => 'motivo-sport-01-header.json',
		'image'            => 'motivo-sport-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'motivo-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motivo Sport – Hero: Push Past Your Limits!', 'envo-blocks' ),
		'file'             => 'motivo-sport-02-hero.json',
		'image'            => 'motivo-sport-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'motivo-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motivo Sport – Banner: Leather Bags & Satchels', 'envo-blocks' ),
		'file'             => 'motivo-sport-03-banner.json',
		'image'            => 'motivo-sport-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'motivo-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motivo Sport – Categories: Browse by Category', 'envo-blocks' ),
		'file'             => 'motivo-sport-04-categories.json',
		'image'            => 'motivo-sport-04-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'motivo-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motivo Sport – Products: Featured Products', 'envo-blocks' ),
		'file'             => 'motivo-sport-05-products.json',
		'image'            => 'motivo-sport-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'motivo-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motivo Sport – Products: Top Rated Products', 'envo-blocks' ),
		'file'             => 'motivo-sport-06-products.json',
		'image'            => 'motivo-sport-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'motivo-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Motivo Sport – Products: Instagram', 'envo-blocks' ),
		'file'             => 'motivo-sport-07-products.json',
		'image'            => 'motivo-sport-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'motivo-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Motivo Sport – Newsletter: Subscribe to Our Newsletter', 'envo-blocks' ),
		'file'     => 'motivo-sport-08-newsletter.json',
		'image'    => 'motivo-sport-08-newsletter.webp',
		'category' => array(
			__( 'Newsletter', 'envo-blocks' ),
		),
		'demo'     => 'motivo-sport',
	),
	array(
		'name'     => __( 'Motivo Sport – Blog: Latest News', 'envo-blocks' ),
		'file'     => 'motivo-sport-09-blog.json',
		'image'    => 'motivo-sport-09-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'motivo-sport',
	),
	array(
		'name'     => __( 'Motivo Sport – Footer', 'envo-blocks' ),
		'file'     => 'motivo-sport-10-footer.json',
		'image'    => 'motivo-sport-10-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'motivo-sport',
	),
	array(
		'name'             => __( 'Gizmo – Header', 'envo-blocks' ),
		'file'             => 'gizmo-01-header.json',
		'image'            => 'gizmo-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'gizmo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Gizmo – Hero: Super discount', 'envo-blocks' ),
		'file'             => 'gizmo-02-hero.json',
		'image'            => 'gizmo-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'gizmo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gizmo – Banner: Weekend discount', 'envo-blocks' ),
		'file'     => 'gizmo-03-banner.json',
		'image'    => 'gizmo-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gizmo',
	),
	array(
		'name'     => __( 'Gizmo – Banner: Shop by categories', 'envo-blocks' ),
		'file'     => 'gizmo-04-banner.json',
		'image'    => 'gizmo-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gizmo',
	),
	array(
		'name'             => __( 'Gizmo – Products: New arrivals', 'envo-blocks' ),
		'file'             => 'gizmo-05-products.json',
		'image'            => 'gizmo-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gizmo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gizmo – Banner: Bigger screen, lighter bag', 'envo-blocks' ),
		'file'     => 'gizmo-06-banner.json',
		'image'    => 'gizmo-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gizmo',
	),
	array(
		'name'             => __( 'Gizmo – Deals: Hot deals', 'envo-blocks' ),
		'file'             => 'gizmo-07-deals.json',
		'image'            => 'gizmo-07-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'gizmo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Gizmo – Products: Recently launched', 'envo-blocks' ),
		'file'             => 'gizmo-08-products.json',
		'image'            => 'gizmo-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gizmo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gizmo – About: Popular brands', 'envo-blocks' ),
		'file'     => 'gizmo-09-about.json',
		'image'    => 'gizmo-09-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'gizmo',
	),
	array(
		'name'             => __( 'Gizmo – Products: Ultimate tech gadgets', 'envo-blocks' ),
		'file'             => 'gizmo-10-products.json',
		'image'            => 'gizmo-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gizmo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gizmo – Blog: From our articles', 'envo-blocks' ),
		'file'     => 'gizmo-11-blog.json',
		'image'    => 'gizmo-11-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'gizmo',
	),
	array(
		'name'     => __( 'Gizmo – Footer', 'envo-blocks' ),
		'file'     => 'gizmo-13-footer.json',
		'image'    => 'gizmo-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'gizmo',
	),
	array(
		'name'             => __( 'Marigold – Header', 'envo-blocks' ),
		'file'             => 'marigold-01-header.json',
		'image'            => 'marigold-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'marigold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Marigold – Hero: New summer collection', 'envo-blocks' ),
		'file'             => 'marigold-02-hero.json',
		'image'            => 'marigold-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'marigold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Marigold – Banner: Weekend discount', 'envo-blocks' ),
		'file'             => 'marigold-03-banner.json',
		'image'            => 'marigold-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'marigold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Marigold – Products: Men\'s summer', 'envo-blocks' ),
		'file'             => 'marigold-04-products.json',
		'image'            => 'marigold-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'marigold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Marigold – Deals: Evening shoes', 'envo-blocks' ),
		'file'             => 'marigold-05-deals.json',
		'image'            => 'marigold-05-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'marigold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Marigold – Banner: Members\' summer sale', 'envo-blocks' ),
		'file'             => 'marigold-06-banner.json',
		'image'            => 'marigold-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'marigold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Marigold – Blog: Latest news', 'envo-blocks' ),
		'file'     => 'marigold-07-blog.json',
		'image'    => 'marigold-07-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'marigold',
	),
	array(
		'name'     => __( 'Marigold – Footer', 'envo-blocks' ),
		'file'     => 'marigold-09-footer.json',
		'image'    => 'marigold-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'marigold',
	),
	array(
		'name'             => __( 'Mono – Header', 'envo-blocks' ),
		'file'             => 'mono-01-header.json',
		'image'            => 'mono-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'mono',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Mono – Hero: New collection', 'envo-blocks' ),
		'file'             => 'mono-02-hero.json',
		'image'            => 'mono-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'mono',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Mono – Categories: Featured categories', 'envo-blocks' ),
		'file'             => 'mono-03-categories.json',
		'image'            => 'mono-03-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'mono',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Mono – Products: Featured products', 'envo-blocks' ),
		'file'             => 'mono-04-products.json',
		'image'            => 'mono-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'mono',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Mono – Banner: Offers and deals', 'envo-blocks' ),
		'file'             => 'mono-05-banner.json',
		'image'            => 'mono-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'mono',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Mono – About: From our Instagram', 'envo-blocks' ),
		'file'     => 'mono-06-about.json',
		'image'    => 'mono-06-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'mono',
	),
	array(
		'name'     => __( 'Mono – Footer', 'envo-blocks' ),
		'file'     => 'mono-07-footer.json',
		'image'    => 'mono-07-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'mono',
	),
	array(
		'name'             => __( 'Nexo – Header', 'envo-blocks' ),
		'file'             => 'nexo-01-header.json',
		'image'            => 'nexo-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'nexo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nexo – Hero: One store. Every screen.', 'envo-blocks' ),
		'file'     => 'nexo-02-hero.json',
		'image'    => 'nexo-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'nexo',
	),
	array(
		'name'             => __( 'Nexo – Products: Shop by device', 'envo-blocks' ),
		'file'             => 'nexo-03-products.json',
		'image'            => 'nexo-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nexo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nexo – Banner: Size guide', 'envo-blocks' ),
		'file'     => 'nexo-04-banner.json',
		'image'    => 'nexo-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'nexo',
	),
	array(
		'name'             => __( 'Nexo – Products: Compare', 'envo-blocks' ),
		'file'             => 'nexo-05-products.json',
		'image'            => 'nexo-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nexo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nexo – Deals: Deal of the week', 'envo-blocks' ),
		'file'             => 'nexo-06-deals.json',
		'image'            => 'nexo-06-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'nexo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nexo – Banner: Trade-in', 'envo-blocks' ),
		'file'             => 'nexo-07-banner.json',
		'image'            => 'nexo-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'nexo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nexo – Products: Best sellers', 'envo-blocks' ),
		'file'             => 'nexo-08-products.json',
		'image'            => 'nexo-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nexo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nexo – Brands', 'envo-blocks' ),
		'file'             => 'nexo-09-brands.json',
		'image'            => 'nexo-09-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'nexo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nexo – Features', 'envo-blocks' ),
		'file'     => 'nexo-10-features.json',
		'image'    => 'nexo-10-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'nexo',
	),
	array(
		'name'             => __( 'Nexo – Reviews', 'envo-blocks' ),
		'file'             => 'nexo-11-reviews.json',
		'image'            => 'nexo-11-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'nexo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nexo – Footer', 'envo-blocks' ),
		'file'     => 'nexo-13-footer.json',
		'image'    => 'nexo-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'nexo',
	),
	array(
		'name'             => __( 'Rosso Fashion – Header', 'envo-blocks' ),
		'file'             => 'rosso-01-header.json',
		'image'            => 'rosso-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'rosso',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Rosso Fashion – Hero: Sale', 'envo-blocks' ),
		'file'             => 'rosso-02-hero.json',
		'image'            => 'rosso-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'rosso',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Rosso Fashion – Footer', 'envo-blocks' ),
		'file'     => 'rosso-03-footer.json',
		'image'    => 'rosso-03-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'rosso',
	),
	array(
		'name'             => __( 'Bitbox – Header', 'envo-blocks' ),
		'file'             => 'bitbox-01-header.json',
		'image'            => 'bitbox-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Bitbox – Hero: New · M3 chip', 'envo-blocks' ),
		'file'             => 'bitbox-02-hero.json',
		'image'            => 'bitbox-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Bitbox – Banner: Next-day delivery', 'envo-blocks' ),
		'file'     => 'bitbox-03-banner.json',
		'image'    => 'bitbox-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'bitbox',
	),
	array(
		'name'             => __( 'Bitbox – Categories: Departments', 'envo-blocks' ),
		'file'             => 'bitbox-04-categories.json',
		'image'            => 'bitbox-04-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Bitbox – Products: Picked for you', 'envo-blocks' ),
		'file'             => 'bitbox-05-products.json',
		'image'            => 'bitbox-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Bitbox – Products: Top 5 this week', 'envo-blocks' ),
		'file'             => 'bitbox-06-products.json',
		'image'            => 'bitbox-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Bitbox – Products: Compare', 'envo-blocks' ),
		'file'             => 'bitbox-07-products.json',
		'image'            => 'bitbox-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Bitbox – Products: Spotlight', 'envo-blocks' ),
		'file'             => 'bitbox-08-products.json',
		'image'            => 'bitbox-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Bitbox – Brands: Official partner of 40+ brands', 'envo-blocks' ),
		'file'             => 'bitbox-09-brands.json',
		'image'            => 'bitbox-09-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Bitbox – Banner: Bitbox club', 'envo-blocks' ),
		'file'             => 'bitbox-10-banner.json',
		'image'            => 'bitbox-10-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'bitbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Bitbox – Footer', 'envo-blocks' ),
		'file'     => 'bitbox-12-footer.json',
		'image'    => 'bitbox-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'bitbox',
	),
	array(
		'name'             => __( 'Nova Tech – Header', 'envo-blocks' ),
		'file'             => 'nova-tech-01-header.json',
		'image'            => 'nova-tech-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nova Tech – Hero: Step inside', 'envo-blocks' ),
		'file'             => 'nova-tech-02-hero.json',
		'image'            => 'nova-tech-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nova Tech – Features', 'envo-blocks' ),
		'file'     => 'nova-tech-03-features.json',
		'image'    => 'nova-tech-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'nova-tech',
	),
	array(
		'name'             => __( 'Nova Tech – Categories: Shop by category', 'envo-blocks' ),
		'file'             => 'nova-tech-04-categories.json',
		'image'            => 'nova-tech-04-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nova Tech – Deals: Limited offer', 'envo-blocks' ),
		'file'             => 'nova-tech-05-deals.json',
		'image'            => 'nova-tech-05-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nova Tech – Products: Hand-picked', 'envo-blocks' ),
		'file'             => 'nova-tech-06-products.json',
		'image'            => 'nova-tech-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nova Tech – About: Welcome to Nova Tech', 'envo-blocks' ),
		'file'             => 'nova-tech-07-about.json',
		'image'            => 'nova-tech-07-about.webp',
		'category'         => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nova Tech – Products: Just landed', 'envo-blocks' ),
		'file'             => 'nova-tech-08-products.json',
		'image'            => 'nova-tech-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nova Tech – Products: Best sellers', 'envo-blocks' ),
		'file'             => 'nova-tech-09-products.json',
		'image'            => 'nova-tech-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nova Tech – Brands: Brands we carry', 'envo-blocks' ),
		'file'             => 'nova-tech-10-brands.json',
		'image'            => 'nova-tech-10-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'nova-tech',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nova Tech – Contact', 'envo-blocks' ),
		'file'     => 'nova-tech-11-contact.json',
		'image'    => 'nova-tech-11-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'nova-tech',
	),
	array(
		'name'     => __( 'Nova Tech – Footer', 'envo-blocks' ),
		'file'     => 'nova-tech-12-footer.json',
		'image'    => 'nova-tech-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'nova-tech',
	),
	array(
		'name'             => __( 'Arena Sport – Header', 'envo-blocks' ),
		'file'             => 'arena-sport-01-header.json',
		'image'            => 'arena-sport-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'arena-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arena Sport – Hero: Built for the court', 'envo-blocks' ),
		'file'             => 'arena-sport-02-hero.json',
		'image'            => 'arena-sport-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'arena-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arena Sport – Products: Shop the look', 'envo-blocks' ),
		'file'             => 'arena-sport-03-products.json',
		'image'            => 'arena-sport-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'arena-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arena Sport – Products: New products', 'envo-blocks' ),
		'file'             => 'arena-sport-04-products.json',
		'image'            => 'arena-sport-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'arena-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arena Sport – Brands: Trusted by', 'envo-blocks' ),
		'file'             => 'arena-sport-05-brands.json',
		'image'            => 'arena-sport-05-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'arena-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arena Sport – Banner: Sale', 'envo-blocks' ),
		'file'             => 'arena-sport-06-banner.json',
		'image'            => 'arena-sport-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'arena-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arena Sport – Products: Featured products', 'envo-blocks' ),
		'file'             => 'arena-sport-07-products.json',
		'image'            => 'arena-sport-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'arena-sport',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Arena Sport – Contact: Contact us', 'envo-blocks' ),
		'file'     => 'arena-sport-08-contact.json',
		'image'    => 'arena-sport-08-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'arena-sport',
	),
	array(
		'name'     => __( 'Arena Sport – Footer', 'envo-blocks' ),
		'file'     => 'arena-sport-09-footer.json',
		'image'    => 'arena-sport-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'arena-sport',
	),
	array(
		'name'             => __( 'Volta Mobile – Header', 'envo-blocks' ),
		'file'             => 'volta-mobile-01-header.json',
		'image'            => 'volta-mobile-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'volta-mobile',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Volta Mobile – Hero: Phone-deals', 'envo-blocks' ),
		'file'             => 'volta-mobile-02-hero.json',
		'image'            => 'volta-mobile-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'volta-mobile',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Volta Mobile – Features', 'envo-blocks' ),
		'file'     => 'volta-mobile-03-features.json',
		'image'    => 'volta-mobile-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'volta-mobile',
	),
	array(
		'name'             => __( 'Volta Mobile – Categories: Popular Categories', 'envo-blocks' ),
		'file'             => 'volta-mobile-05-categories.json',
		'image'            => 'volta-mobile-05-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'volta-mobile',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Volta Mobile – Deals: Hurry Up Deals', 'envo-blocks' ),
		'file'             => 'volta-mobile-06-deals.json',
		'image'            => 'volta-mobile-06-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'volta-mobile',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Volta Mobile – Banner: Just landed', 'envo-blocks' ),
		'file'     => 'volta-mobile-07-banner.json',
		'image'    => 'volta-mobile-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'volta-mobile',
	),
	array(
		'name'             => __( 'Volta Mobile – Products: Featured Products', 'envo-blocks' ),
		'file'             => 'volta-mobile-08-products.json',
		'image'            => 'volta-mobile-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'volta-mobile',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Volta Mobile – Products: Bundle Products', 'envo-blocks' ),
		'file'             => 'volta-mobile-09-products.json',
		'image'            => 'volta-mobile-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'volta-mobile',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Volta Mobile – Products: Best Sellers', 'envo-blocks' ),
		'file'             => 'volta-mobile-11-products.json',
		'image'            => 'volta-mobile-11-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'volta-mobile',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Volta Mobile – Banner: From Instagram', 'envo-blocks' ),
		'file'     => 'volta-mobile-12-banner.json',
		'image'    => 'volta-mobile-12-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'volta-mobile',
	),
	array(
		'name'     => __( 'Volta Mobile – Footer', 'envo-blocks' ),
		'file'     => 'volta-mobile-13-footer.json',
		'image'    => 'volta-mobile-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'volta-mobile',
	),
	array(
		'name'             => __( 'Throttle – Header', 'envo-blocks' ),
		'file'             => 'throttle-01-header.json',
		'image'            => 'throttle-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'throttle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Throttle – Hero: 30% off', 'envo-blocks' ),
		'file'             => 'throttle-02-hero.json',
		'image'            => 'throttle-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'throttle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Throttle – Banner: Throttle GT', 'envo-blocks' ),
		'file'             => 'throttle-03-banner.json',
		'image'            => 'throttle-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'throttle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Throttle – Products: Recently released', 'envo-blocks' ),
		'file'             => 'throttle-04-products.json',
		'image'            => 'throttle-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'throttle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Throttle – Footer', 'envo-blocks' ),
		'file'     => 'throttle-05-footer.json',
		'image'    => 'throttle-05-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'throttle',
	),
	array(
		'name'             => __( 'Shopora – Header', 'envo-blocks' ),
		'file'             => 'shopora-01-header.json',
		'image'            => 'shopora-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Shopora – Hero: 25% offer', 'envo-blocks' ),
		'file'             => 'shopora-02-hero.json',
		'image'            => 'shopora-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Shopora – Features', 'envo-blocks' ),
		'file'     => 'shopora-03-features.json',
		'image'    => 'shopora-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'shopora',
	),
	array(
		'name'             => __( 'Shopora – Deals: Deal Of The Day', 'envo-blocks' ),
		'file'             => 'shopora-04-deals.json',
		'image'            => 'shopora-04-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Shopora – Banner: Best offer', 'envo-blocks' ),
		'file'             => 'shopora-05-banner.json',
		'image'            => 'shopora-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Shopora – Products: Featured Products', 'envo-blocks' ),
		'file'             => 'shopora-06-products.json',
		'image'            => 'shopora-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Shopora – Banner: 25% off', 'envo-blocks' ),
		'file'             => 'shopora-07-banner.json',
		'image'            => 'shopora-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Shopora – Products: New Arrival', 'envo-blocks' ),
		'file'             => 'shopora-08-products.json',
		'image'            => 'shopora-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Shopora – Reviews: What our customers say', 'envo-blocks' ),
		'file'             => 'shopora-09-reviews.json',
		'image'            => 'shopora-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Shopora – Products: Top Rating', 'envo-blocks' ),
		'file'             => 'shopora-10-products.json',
		'image'            => 'shopora-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Shopora – Blog: Latest News & Blogs', 'envo-blocks' ),
		'file'     => 'shopora-11-blog.json',
		'image'    => 'shopora-11-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'shopora',
	),
	array(
		'name'             => __( 'Shopora – Footer', 'envo-blocks' ),
		'file'             => 'shopora-12-footer.json',
		'image'            => 'shopora-12-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'shopora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velora – Header', 'envo-blocks' ),
		'file'             => 'velora-01-header.json',
		'image'            => 'velora-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velora – Hero: Beauty that inspires', 'envo-blocks' ),
		'file'             => 'velora-02-hero.json',
		'image'            => 'velora-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Velora – Banner: Glow every day', 'envo-blocks' ),
		'file'     => 'velora-03-banner.json',
		'image'    => 'velora-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'velora',
	),
	array(
		'name'             => __( 'Velora – Products: Best Sellers', 'envo-blocks' ),
		'file'             => 'velora-04-products.json',
		'image'            => 'velora-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velora – Categories: Shop By Category', 'envo-blocks' ),
		'file'             => 'velora-05-categories.json',
		'image'            => 'velora-05-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Velora – Features', 'envo-blocks' ),
		'file'     => 'velora-06-features.json',
		'image'    => 'velora-06-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'velora',
	),
	array(
		'name'             => __( 'Velora – Products: Shop the look', 'envo-blocks' ),
		'file'             => 'velora-07-products.json',
		'image'            => 'velora-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Velora – Features', 'envo-blocks' ),
		'file'     => 'velora-08-features.json',
		'image'    => 'velora-08-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'velora',
	),
	array(
		'name'     => __( 'Velora – Banner', 'envo-blocks' ),
		'file'     => 'velora-09-banner.json',
		'image'    => 'velora-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'velora',
	),
	array(
		'name'             => __( 'Velora – Products: Sale 50% off', 'envo-blocks' ),
		'file'             => 'velora-10-products.json',
		'image'            => 'velora-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velora – Brands: Shop by Brand', 'envo-blocks' ),
		'file'             => 'velora-11-brands.json',
		'image'            => 'velora-11-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velora – Products: Featured fragrance', 'envo-blocks' ),
		'file'             => 'velora-12-products.json',
		'image'            => 'velora-12-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velora – Products: Top Rated', 'envo-blocks' ),
		'file'             => 'velora-13-products.json',
		'image'            => 'velora-13-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velora – Categories: The Makeup Edit', 'envo-blocks' ),
		'file'             => 'velora-14-categories.json',
		'image'            => 'velora-14-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Velora – Products: Bundle & Save 10%', 'envo-blocks' ),
		'file'             => 'velora-15-products.json',
		'image'            => 'velora-15-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Velora – Banner', 'envo-blocks' ),
		'file'     => 'velora-16-banner.json',
		'image'    => 'velora-16-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'velora',
	),
	array(
		'name'             => __( 'Velora – About: Get 10% off your first order', 'envo-blocks' ),
		'file'             => 'velora-18-about.json',
		'image'            => 'velora-18-about.webp',
		'category'         => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'             => 'velora',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Velora – Footer', 'envo-blocks' ),
		'file'     => 'velora-20-footer.json',
		'image'    => 'velora-20-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'velora',
	),
	array(
		'name'             => __( 'Aurelle – Header', 'envo-blocks' ),
		'file'             => 'aurelle-01-header.json',
		'image'            => 'aurelle-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurelle – Hero: New season jewellery', 'envo-blocks' ),
		'file'             => 'aurelle-02-hero.json',
		'image'            => 'aurelle-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurelle – Categories', 'envo-blocks' ),
		'file'             => 'aurelle-03-categories.json',
		'image'            => 'aurelle-03-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Aurelle – Features', 'envo-blocks' ),
		'file'     => 'aurelle-04-features.json',
		'image'    => 'aurelle-04-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'aurelle',
	),
	array(
		'name'             => __( 'Aurelle – Products: Featured pieces', 'envo-blocks' ),
		'file'             => 'aurelle-05-products.json',
		'image'            => 'aurelle-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurelle – Categories: Browse categories', 'envo-blocks' ),
		'file'             => 'aurelle-06-categories.json',
		'image'            => 'aurelle-06-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurelle – Banner: Gold week', 'envo-blocks' ),
		'file'             => 'aurelle-07-banner.json',
		'image'            => 'aurelle-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurelle – Products: Top rated', 'envo-blocks' ),
		'file'             => 'aurelle-08-products.json',
		'image'            => 'aurelle-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurelle – Products: Recently added', 'envo-blocks' ),
		'file'             => 'aurelle-09-products.json',
		'image'            => 'aurelle-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurelle – Products: Shop the look', 'envo-blocks' ),
		'file'             => 'aurelle-10-products.json',
		'image'            => 'aurelle-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Aurelle – Blog: From the journal', 'envo-blocks' ),
		'file'     => 'aurelle-11-blog.json',
		'image'    => 'aurelle-11-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'aurelle',
	),
	array(
		'name'             => __( 'Aurelle – Footer', 'envo-blocks' ),
		'file'             => 'aurelle-13-footer.json',
		'image'            => 'aurelle-13-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'aurelle',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Solo – Header', 'envo-blocks' ),
		'file'             => 'solo-01-header.json',
		'image'            => 'solo-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'solo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Solo – Hero: Samsung Galaxy S10', 'envo-blocks' ),
		'file'     => 'solo-02-hero.json',
		'image'    => 'solo-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'     => __( 'Solo – Banner', 'envo-blocks' ),
		'file'     => 'solo-03-banner.json',
		'image'    => 'solo-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'     => __( 'Solo – Banner: Infinity-O display', 'envo-blocks' ),
		'file'     => 'solo-04-banner.json',
		'image'    => 'solo-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'     => __( 'Solo – Banner', 'envo-blocks' ),
		'file'     => 'solo-05-banner.json',
		'image'    => 'solo-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'             => __( 'Solo – Banner: Night mode', 'envo-blocks' ),
		'file'             => 'solo-06-banner.json',
		'image'            => 'solo-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'solo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Solo – Banner', 'envo-blocks' ),
		'file'     => 'solo-07-banner.json',
		'image'    => 'solo-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'     => __( 'Solo – Banner: Share it', 'envo-blocks' ),
		'file'     => 'solo-08-banner.json',
		'image'    => 'solo-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'     => __( 'Solo – Banner: The most powerful', 'envo-blocks' ),
		'file'     => 'solo-09-banner.json',
		'image'    => 'solo-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'             => __( 'Solo – Reviews', 'envo-blocks' ),
		'file'             => 'solo-10-reviews.json',
		'image'            => 'solo-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'solo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Solo – Banner', 'envo-blocks' ),
		'file'     => 'solo-11-banner.json',
		'image'    => 'solo-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'             => __( 'Solo – Banner: Buy now', 'envo-blocks' ),
		'file'             => 'solo-12-banner.json',
		'image'            => 'solo-12-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'solo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Solo – Banner', 'envo-blocks' ),
		'file'     => 'solo-13-banner.json',
		'image'    => 'solo-13-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'solo',
	),
	array(
		'name'             => __( 'Solo – Footer', 'envo-blocks' ),
		'file'             => 'solo-14-footer.json',
		'image'            => 'solo-14-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'solo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kinetic – Header', 'envo-blocks' ),
		'file'             => 'kinetic-01-header.json',
		'image'            => 'kinetic-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'kinetic',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kinetic – Hero: Spring / Summer season', 'envo-blocks' ),
		'file'             => 'kinetic-02-hero.json',
		'image'            => 'kinetic-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'kinetic',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kinetic – Products: Shop by', 'envo-blocks' ),
		'file'             => 'kinetic-03-products.json',
		'image'            => 'kinetic-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kinetic',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kinetic – Products', 'envo-blocks' ),
		'file'             => 'kinetic-04-products.json',
		'image'            => 'kinetic-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kinetic',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kinetic – Products: Featured products', 'envo-blocks' ),
		'file'             => 'kinetic-05-products.json',
		'image'            => 'kinetic-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kinetic',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kinetic – Banner: Summer sale', 'envo-blocks' ),
		'file'     => 'kinetic-06-banner.json',
		'image'    => 'kinetic-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kinetic',
	),
	array(
		'name'             => __( 'Kinetic – Banner: Train harder. Recover smarter.', 'envo-blocks' ),
		'file'             => 'kinetic-07-banner.json',
		'image'            => 'kinetic-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'kinetic',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kinetic – Footer', 'envo-blocks' ),
		'file'     => 'kinetic-08-footer.json',
		'image'    => 'kinetic-08-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'kinetic',
	),
	array(
		'name'             => __( 'Nordhem – Header', 'envo-blocks' ),
		'file'             => 'nordhem-01-header.json',
		'image'            => 'nordhem-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'nordhem',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nordhem – Hero', 'envo-blocks' ),
		'file'             => 'nordhem-02-hero.json',
		'image'            => 'nordhem-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'nordhem',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nordhem – Features', 'envo-blocks' ),
		'file'     => 'nordhem-03-features.json',
		'image'    => 'nordhem-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'nordhem',
	),
	array(
		'name'             => __( 'Nordhem – Products: Featured products', 'envo-blocks' ),
		'file'             => 'nordhem-04-products.json',
		'image'            => 'nordhem-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nordhem',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nordhem – Banner: It is time for a', 'envo-blocks' ),
		'file'             => 'nordhem-05-banner.json',
		'image'            => 'nordhem-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'nordhem',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nordhem – Products: Trending now', 'envo-blocks' ),
		'file'             => 'nordhem-06-products.json',
		'image'            => 'nordhem-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nordhem',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nordhem – Products: Shop the room', 'envo-blocks' ),
		'file'             => 'nordhem-07-products.json',
		'image'            => 'nordhem-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'nordhem',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nordhem – Banner: From Instagram', 'envo-blocks' ),
		'file'     => 'nordhem-08-banner.json',
		'image'    => 'nordhem-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'nordhem',
	),
	array(
		'name'     => __( 'Nordhem – Footer', 'envo-blocks' ),
		'file'     => 'nordhem-10-footer.json',
		'image'    => 'nordhem-10-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'nordhem',
	),
	array(
		'name'             => __( 'Urbano – Header', 'envo-blocks' ),
		'file'             => 'urbano-01-header.json',
		'image'            => 'urbano-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'urbano',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Urbano – Hero: Exclusive', 'envo-blocks' ),
		'file'             => 'urbano-02-hero.json',
		'image'            => 'urbano-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'urbano',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Urbano – Features', 'envo-blocks' ),
		'file'     => 'urbano-03-features.json',
		'image'    => 'urbano-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'urbano',
	),
	array(
		'name'             => __( 'Urbano – Products: Featured products', 'envo-blocks' ),
		'file'             => 'urbano-04-products.json',
		'image'            => 'urbano-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'urbano',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Urbano – Categories: Browse our categories', 'envo-blocks' ),
		'file'             => 'urbano-05-categories.json',
		'image'            => 'urbano-05-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'urbano',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Urbano – Products', 'envo-blocks' ),
		'file'             => 'urbano-06-products.json',
		'image'            => 'urbano-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'urbano',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Urbano – Products: Best selling', 'envo-blocks' ),
		'file'             => 'urbano-07-products.json',
		'image'            => 'urbano-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'urbano',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Urbano – Brands', 'envo-blocks' ),
		'file'             => 'urbano-08-brands.json',
		'image'            => 'urbano-08-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'urbano',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Urbano – Reviews: Recent posts', 'envo-blocks' ),
		'file'             => 'urbano-09-reviews.json',
		'image'            => 'urbano-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'urbano',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Urbano – Footer', 'envo-blocks' ),
		'file'     => 'urbano-11-footer.json',
		'image'    => 'urbano-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'urbano',
	),
	array(
		'name'             => __( 'Brava – Header', 'envo-blocks' ),
		'file'             => 'brava-01-header.json',
		'image'            => 'brava-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'brava',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Brava – Hero: Get the fresh new collection', 'envo-blocks' ),
		'file'             => 'brava-02-hero.json',
		'image'            => 'brava-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'brava',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Brava – Products: Featured products', 'envo-blocks' ),
		'file'             => 'brava-03-products.json',
		'image'            => 'brava-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'brava',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Brava – Banner: Brava watches', 'envo-blocks' ),
		'file'     => 'brava-04-banner.json',
		'image'    => 'brava-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'brava',
	),
	array(
		'name'             => __( 'Brava – Products', 'envo-blocks' ),
		'file'             => 'brava-05-products.json',
		'image'            => 'brava-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'brava',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Brava – Banner: Big sale', 'envo-blocks' ),
		'file'     => 'brava-07-banner.json',
		'image'    => 'brava-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'brava',
	),
	array(
		'name'     => __( 'Brava – Features', 'envo-blocks' ),
		'file'     => 'brava-08-features.json',
		'image'    => 'brava-08-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'brava',
	),
	array(
		'name'             => __( 'Brava – Brands', 'envo-blocks' ),
		'file'             => 'brava-09-brands.json',
		'image'            => 'brava-09-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'brava',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Brava – Footer', 'envo-blocks' ),
		'file'     => 'brava-10-footer.json',
		'image'    => 'brava-10-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'brava',
	),
	array(
		'name'             => __( 'Voltix – Header', 'envo-blocks' ),
		'file'             => 'voltix-01-header.json',
		'image'            => 'voltix-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Voltix – Hero: Electronic deals', 'envo-blocks' ),
		'file'             => 'voltix-02-hero.json',
		'image'            => 'voltix-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Voltix – Features', 'envo-blocks' ),
		'file'     => 'voltix-03-features.json',
		'image'    => 'voltix-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'voltix',
	),
	array(
		'name'             => __( 'Voltix – Deals: Flash deal', 'envo-blocks' ),
		'file'             => 'voltix-04-deals.json',
		'image'            => 'voltix-04-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Voltix – Products: Most viewed products', 'envo-blocks' ),
		'file'             => 'voltix-05-products.json',
		'image'            => 'voltix-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Voltix – Banner: Wireless', 'envo-blocks' ),
		'file'     => 'voltix-06-banner.json',
		'image'    => 'voltix-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'voltix',
	),
	array(
		'name'             => __( 'Voltix – Categories: Top categories', 'envo-blocks' ),
		'file'             => 'voltix-07-categories.json',
		'image'            => 'voltix-07-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Voltix – Products: Best sellers on electronics', 'envo-blocks' ),
		'file'             => 'voltix-08-products.json',
		'image'            => 'voltix-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Voltix – Banner: Check new arrivals', 'envo-blocks' ),
		'file'             => 'voltix-09-banner.json',
		'image'            => 'voltix-09-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Voltix – Categories: Laptops & computers', 'envo-blocks' ),
		'file'             => 'voltix-10-categories.json',
		'image'            => 'voltix-10-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Voltix – Categories: Mobile phones & accessories', 'envo-blocks' ),
		'file'             => 'voltix-11-categories.json',
		'image'            => 'voltix-11-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Voltix – Products: Top rated products', 'envo-blocks' ),
		'file'             => 'voltix-12-products.json',
		'image'            => 'voltix-12-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Voltix – Brands: Top brands', 'envo-blocks' ),
		'file'             => 'voltix-13-brands.json',
		'image'            => 'voltix-13-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'voltix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Voltix – Footer', 'envo-blocks' ),
		'file'     => 'voltix-14-footer.json',
		'image'    => 'voltix-14-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'voltix',
	),
	array(
		'name'             => __( 'Avenue Fashion – Header', 'envo-blocks' ),
		'file'             => 'avenue-01-header.json',
		'image'            => 'avenue-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Avenue Fashion – Hero: Hello, it\'s the new season', 'envo-blocks' ),
		'file'             => 'avenue-02-hero.json',
		'image'            => 'avenue-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Avenue Fashion – Banner: Bags & accessories', 'envo-blocks' ),
		'file'     => 'avenue-03-banner.json',
		'image'    => 'avenue-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'avenue',
	),
	array(
		'name'             => __( 'Avenue Fashion – Products', 'envo-blocks' ),
		'file'             => 'avenue-04-products.json',
		'image'            => 'avenue-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Avenue Fashion – Deals', 'envo-blocks' ),
		'file'             => 'avenue-05-deals.json',
		'image'            => 'avenue-05-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Avenue Fashion – Products', 'envo-blocks' ),
		'file'             => 'avenue-06-products.json',
		'image'            => 'avenue-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Avenue Fashion – Banner: Sale', 'envo-blocks' ),
		'file'             => 'avenue-07-banner.json',
		'image'            => 'avenue-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Avenue Fashion – Products', 'envo-blocks' ),
		'file'             => 'avenue-08-products.json',
		'image'            => 'avenue-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Avenue Fashion – Products: On sale', 'envo-blocks' ),
		'file'             => 'avenue-09-products.json',
		'image'            => 'avenue-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Avenue Fashion – Brands: Brands we love', 'envo-blocks' ),
		'file'             => 'avenue-10-brands.json',
		'image'            => 'avenue-10-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'avenue',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Avenue Fashion – Contact', 'envo-blocks' ),
		'file'     => 'avenue-11-contact.json',
		'image'    => 'avenue-11-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'avenue',
	),
	array(
		'name'     => __( 'Avenue Fashion – Footer', 'envo-blocks' ),
		'file'     => 'avenue-12-footer.json',
		'image'    => 'avenue-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'avenue',
	),
	array(
		'name'             => __( 'Ampere – Header', 'envo-blocks' ),
		'file'             => 'ampere-01-header.json',
		'image'            => 'ampere-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Hero: From $99', 'envo-blocks' ),
		'file'             => 'ampere-02-hero.json',
		'image'            => 'ampere-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Products: Featured products', 'envo-blocks' ),
		'file'             => 'ampere-03-products.json',
		'image'            => 'ampere-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Products: Trending this week', 'envo-blocks' ),
		'file'             => 'ampere-04-products.json',
		'image'            => 'ampere-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ampere – Banner: Ampere audio', 'envo-blocks' ),
		'file'     => 'ampere-05-banner.json',
		'image'    => 'ampere-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'ampere',
	),
	array(
		'name'             => __( 'Ampere – Products: Buy more & save', 'envo-blocks' ),
		'file'             => 'ampere-06-products.json',
		'image'            => 'ampere-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Products: Featured products', 'envo-blocks' ),
		'file'             => 'ampere-07-products.json',
		'image'            => 'ampere-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Deals: Exclusive smart watch – 30% off', 'envo-blocks' ),
		'file'             => 'ampere-09-deals.json',
		'image'            => 'ampere-09-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Banner', 'envo-blocks' ),
		'file'             => 'ampere-10-banner.json',
		'image'            => 'ampere-10-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Brands: Shop by brand', 'envo-blocks' ),
		'file'             => 'ampere-11-brands.json',
		'image'            => 'ampere-11-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Banner: Super powerful collection', 'envo-blocks' ),
		'file'             => 'ampere-12-banner.json',
		'image'            => 'ampere-12-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Products: Recommended products', 'envo-blocks' ),
		'file'             => 'ampere-13-products.json',
		'image'            => 'ampere-13-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ampere – Products: Flash deals', 'envo-blocks' ),
		'file'             => 'ampere-14-products.json',
		'image'            => 'ampere-14-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ampere – Banner: Shop by tag', 'envo-blocks' ),
		'file'     => 'ampere-15-banner.json',
		'image'    => 'ampere-15-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'ampere',
	),
	array(
		'name'             => __( 'Ampere – Reviews: Latest reviews', 'envo-blocks' ),
		'file'             => 'ampere-16-reviews.json',
		'image'            => 'ampere-16-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'ampere',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ampere – Blog: Latest articles', 'envo-blocks' ),
		'file'     => 'ampere-17-blog.json',
		'image'    => 'ampere-17-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'ampere',
	),
	array(
		'name'     => __( 'Ampere – Features', 'envo-blocks' ),
		'file'     => 'ampere-18-features.json',
		'image'    => 'ampere-18-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'ampere',
	),
	array(
		'name'     => __( 'Ampere – Footer', 'envo-blocks' ),
		'file'     => 'ampere-21-footer.json',
		'image'    => 'ampere-21-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'ampere',
	),
	array(
		'name'             => __( 'Lumen – Header', 'envo-blocks' ),
		'file'             => 'lumen-01-header.json',
		'image'            => 'lumen-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'lumen',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Lumen – Hero: Holiday sale Fashion', 'envo-blocks' ),
		'file'     => 'lumen-02-hero.json',
		'image'    => 'lumen-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'lumen',
	),
	array(
		'name'             => __( 'Lumen – Products: Top brands', 'envo-blocks' ),
		'file'             => 'lumen-03-products.json',
		'image'            => 'lumen-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'lumen',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Lumen – Banner: Top products Trousers', 'envo-blocks' ),
		'file'     => 'lumen-04-banner.json',
		'image'    => 'lumen-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'lumen',
	),
	array(
		'name'             => __( 'Lumen – Products: Sale', 'envo-blocks' ),
		'file'             => 'lumen-05-products.json',
		'image'            => 'lumen-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'lumen',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Lumen – Banner: Inspiration', 'envo-blocks' ),
		'file'     => 'lumen-06-banner.json',
		'image'    => 'lumen-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'lumen',
	),
	array(
		'name'             => __( 'Lumen – Products: Weekly', 'envo-blocks' ),
		'file'             => 'lumen-07-products.json',
		'image'            => 'lumen-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'lumen',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Lumen – Footer', 'envo-blocks' ),
		'file'     => 'lumen-08-footer.json',
		'image'    => 'lumen-08-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'lumen',
	),
	array(
		'name'             => __( 'Gemma – Header', 'envo-blocks' ),
		'file'             => 'gemma-01-header.json',
		'image'            => 'gemma-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'gemma',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gemma – Hero: New collection Necklaces', 'envo-blocks' ),
		'file'     => 'gemma-02-hero.json',
		'image'    => 'gemma-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'     => __( 'Gemma – Banner: Visit ›', 'envo-blocks' ),
		'file'     => 'gemma-03-banner.json',
		'image'    => 'gemma-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'     => __( 'Gemma – Banner: Recent products', 'envo-blocks' ),
		'file'     => 'gemma-04-banner.json',
		'image'    => 'gemma-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'             => __( 'Gemma – Products', 'envo-blocks' ),
		'file'             => 'gemma-05-products.json',
		'image'            => 'gemma-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gemma',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gemma – About: Shop by brands', 'envo-blocks' ),
		'file'     => 'gemma-06-about.json',
		'image'    => 'gemma-06-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'             => __( 'Gemma – Brands', 'envo-blocks' ),
		'file'             => 'gemma-07-brands.json',
		'image'            => 'gemma-07-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'gemma',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gemma – Banner: On sale products', 'envo-blocks' ),
		'file'     => 'gemma-08-banner.json',
		'image'    => 'gemma-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'             => __( 'Gemma – Products', 'envo-blocks' ),
		'file'             => 'gemma-09-products.json',
		'image'            => 'gemma-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gemma',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gemma – Features', 'envo-blocks' ),
		'file'     => 'gemma-10-features.json',
		'image'    => 'gemma-10-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'             => __( 'Gemma – Products: Summer offer', 'envo-blocks' ),
		'file'             => 'gemma-11-products.json',
		'image'            => 'gemma-11-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'gemma',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Gemma – Banner: Big sale', 'envo-blocks' ),
		'file'     => 'gemma-12-banner.json',
		'image'    => 'gemma-12-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'     => __( 'Gemma – Banner: Follow us on social', 'envo-blocks' ),
		'file'     => 'gemma-13-banner.json',
		'image'    => 'gemma-13-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'     => __( 'Gemma – Contact: Location', 'envo-blocks' ),
		'file'     => 'gemma-14-contact.json',
		'image'    => 'gemma-14-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'     => __( 'Gemma – Footer', 'envo-blocks' ),
		'file'     => 'gemma-16-footer.json',
		'image'    => 'gemma-16-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'gemma',
	),
	array(
		'name'             => __( 'Noir & Gold – Header', 'envo-blocks' ),
		'file'             => 'noir-gold-01-header.json',
		'image'            => 'noir-gold-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'noir-gold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Noir & Gold – Hero: Discover', 'envo-blocks' ),
		'file'             => 'noir-gold-02-hero.json',
		'image'            => 'noir-gold-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'noir-gold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Noir & Gold – Products: New arrivals', 'envo-blocks' ),
		'file'             => 'noir-gold-03-products.json',
		'image'            => 'noir-gold-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'noir-gold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Noir & Gold – About: Welcome to Noir & Gold', 'envo-blocks' ),
		'file'             => 'noir-gold-04-about.json',
		'image'            => 'noir-gold-04-about.webp',
		'category'         => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'             => 'noir-gold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Noir & Gold – Products: Featured products', 'envo-blocks' ),
		'file'             => 'noir-gold-05-products.json',
		'image'            => 'noir-gold-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'noir-gold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Noir & Gold – Banner: Training Deal -20%', 'envo-blocks' ),
		'file'     => 'noir-gold-06-banner.json',
		'image'    => 'noir-gold-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'noir-gold',
	),
	array(
		'name'     => __( 'Noir & Gold – Blog: From our journal', 'envo-blocks' ),
		'file'     => 'noir-gold-07-blog.json',
		'image'    => 'noir-gold-07-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'noir-gold',
	),
	array(
		'name'             => __( 'Noir & Gold – Banner: Let\'s stay in touch', 'envo-blocks' ),
		'file'             => 'noir-gold-08-banner.json',
		'image'            => 'noir-gold-08-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'noir-gold',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Noir & Gold – Footer', 'envo-blocks' ),
		'file'     => 'noir-gold-09-footer.json',
		'image'    => 'noir-gold-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'noir-gold',
	),
	array(
		'name'             => __( 'Duo – Header', 'envo-blocks' ),
		'file'             => 'duo-01-header.json',
		'image'            => 'duo-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'duo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Duo – Hero: NEW · Zenbook Pro Duo', 'envo-blocks' ),
		'file'     => 'duo-02-hero.json',
		'image'    => 'duo-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'duo',
	),
	array(
		'name'     => __( 'Duo – Banner: Screens', 'envo-blocks' ),
		'file'     => 'duo-03-banner.json',
		'image'    => 'duo-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'duo',
	),
	array(
		'name'     => __( 'Duo – Banner: Creators', 'envo-blocks' ),
		'file'     => 'duo-04-banner.json',
		'image'    => 'duo-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'duo',
	),
	array(
		'name'     => __( 'Duo – Banner: Comfort', 'envo-blocks' ),
		'file'     => 'duo-05-banner.json',
		'image'    => 'duo-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'duo',
	),
	array(
		'name'     => __( 'Duo – Banner: Specifications', 'envo-blocks' ),
		'file'     => 'duo-06-banner.json',
		'image'    => 'duo-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'duo',
	),
	array(
		'name'             => __( 'Duo – Reviews', 'envo-blocks' ),
		'file'             => 'duo-07-reviews.json',
		'image'            => 'duo-07-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'duo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Duo – Deals: Launch offer', 'envo-blocks' ),
		'file'             => 'duo-08-deals.json',
		'image'            => 'duo-08-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'duo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Duo – Products: Accessories', 'envo-blocks' ),
		'file'             => 'duo-09-products.json',
		'image'            => 'duo-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'duo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Duo – FAQ', 'envo-blocks' ),
		'file'     => 'duo-10-faq.json',
		'image'    => 'duo-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'duo',
	),
	array(
		'name'     => __( 'Duo – Banner: Create without limits.', 'envo-blocks' ),
		'file'     => 'duo-11-banner.json',
		'image'    => 'duo-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'duo',
	),
	array(
		'name'             => __( 'Duo – Footer', 'envo-blocks' ),
		'file'             => 'duo-12-footer.json',
		'image'            => 'duo-12-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'duo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aero – Header', 'envo-blocks' ),
		'file'             => 'aero-01-header.json',
		'image'            => 'aero-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'aero',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Aero – Hero: New · Mavic Air 2', 'envo-blocks' ),
		'file'     => 'aero-02-hero.json',
		'image'    => 'aero-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'aero',
	),
	array(
		'name'     => __( 'Aero – Banner: 34 min', 'envo-blocks' ),
		'file'     => 'aero-03-banner.json',
		'image'    => 'aero-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aero',
	),
	array(
		'name'     => __( 'Aero – Banner: Features', 'envo-blocks' ),
		'file'     => 'aero-04-banner.json',
		'image'    => 'aero-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aero',
	),
	array(
		'name'     => __( 'Aero – Banner: SmartPhoto', 'envo-blocks' ),
		'file'     => 'aero-05-banner.json',
		'image'    => 'aero-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aero',
	),
	array(
		'name'     => __( 'Aero – Banner: Shot on Mavic', 'envo-blocks' ),
		'file'     => 'aero-06-banner.json',
		'image'    => 'aero-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aero',
	),
	array(
		'name'     => __( 'Aero – Banner: Golden hour', 'envo-blocks' ),
		'file'     => 'aero-07-banner.json',
		'image'    => 'aero-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aero',
	),
	array(
		'name'     => __( 'Aero – Banner: Kits', 'envo-blocks' ),
		'file'     => 'aero-08-banner.json',
		'image'    => 'aero-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aero',
	),
	array(
		'name'     => __( 'Aero – Banner: Specifications', 'envo-blocks' ),
		'file'     => 'aero-09-banner.json',
		'image'    => 'aero-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aero',
	),
	array(
		'name'             => __( 'Aero – Reviews', 'envo-blocks' ),
		'file'             => 'aero-10-reviews.json',
		'image'            => 'aero-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'aero',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aero – Footer', 'envo-blocks' ),
		'file'             => 'aero-11-footer.json',
		'image'            => 'aero-11-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'aero',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pop – Header', 'envo-blocks' ),
		'file'             => 'pop-01-header.json',
		'image'            => 'pop-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'pop',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pop – Hero: Sound that\'s wireless.', 'envo-blocks' ),
		'file'     => 'pop-02-hero.json',
		'image'    => 'pop-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'pop',
	),
	array(
		'name'     => __( 'Pop – Banner: Spatial audio', 'envo-blocks' ),
		'file'     => 'pop-03-banner.json',
		'image'    => 'pop-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pop',
	),
	array(
		'name'     => __( 'Pop – Banner: Sound', 'envo-blocks' ),
		'file'     => 'pop-04-banner.json',
		'image'    => 'pop-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pop',
	),
	array(
		'name'     => __( 'Pop – Banner: Life', 'envo-blocks' ),
		'file'     => 'pop-05-banner.json',
		'image'    => 'pop-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pop',
	),
	array(
		'name'     => __( 'Pop – Banner: 30 h total listening', 'envo-blocks' ),
		'file'     => 'pop-06-banner.json',
		'image'    => 'pop-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pop',
	),
	array(
		'name'     => __( 'Pop – Banner: Pair', 'envo-blocks' ),
		'file'     => 'pop-07-banner.json',
		'image'    => 'pop-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pop',
	),
	array(
		'name'     => __( 'Pop – Banner: Compare', 'envo-blocks' ),
		'file'     => 'pop-08-banner.json',
		'image'    => 'pop-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pop',
	),
	array(
		'name'             => __( 'Pop – Reviews', 'envo-blocks' ),
		'file'             => 'pop-09-reviews.json',
		'image'            => 'pop-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'pop',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pop – Products: Buy', 'envo-blocks' ),
		'file'             => 'pop-10-products.json',
		'image'            => 'pop-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'pop',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pop – Footer', 'envo-blocks' ),
		'file'             => 'pop-12-footer.json',
		'image'            => 'pop-12-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'pop',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Harvest – Header', 'envo-blocks' ),
		'file'             => 'harvest-01-header.json',
		'image'            => 'harvest-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'harvest',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Harvest – Hero: Farm-fresh, every day', 'envo-blocks' ),
		'file'     => 'harvest-02-hero.json',
		'image'    => 'harvest-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'harvest',
	),
	array(
		'name'     => __( 'Harvest – Banner: Aisles', 'envo-blocks' ),
		'file'     => 'harvest-03-banner.json',
		'image'    => 'harvest-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'harvest',
	),
	array(
		'name'             => __( 'Harvest – Deals: Deal of the day', 'envo-blocks' ),
		'file'             => 'harvest-04-deals.json',
		'image'            => 'harvest-04-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'harvest',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Harvest – Products: Best sellers', 'envo-blocks' ),
		'file'             => 'harvest-05-products.json',
		'image'            => 'harvest-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'harvest',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Harvest – Banner: Weekly box', 'envo-blocks' ),
		'file'     => 'harvest-06-banner.json',
		'image'    => 'harvest-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'harvest',
	),
	array(
		'name'     => __( 'Harvest – Banner: How it works', 'envo-blocks' ),
		'file'     => 'harvest-07-banner.json',
		'image'    => 'harvest-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'harvest',
	),
	array(
		'name'             => __( 'Harvest – Products: Offers', 'envo-blocks' ),
		'file'             => 'harvest-08-products.json',
		'image'            => 'harvest-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'harvest',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Harvest – Reviews', 'envo-blocks' ),
		'file'             => 'harvest-09-reviews.json',
		'image'            => 'harvest-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'harvest',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Harvest – Newsletter', 'envo-blocks' ),
		'file'     => 'harvest-10-newsletter.json',
		'image'    => 'harvest-10-newsletter.webp',
		'category' => array(
			__( 'Newsletter', 'envo-blocks' ),
		),
		'demo'     => 'harvest',
	),
	array(
		'name'     => __( 'Harvest – Footer', 'envo-blocks' ),
		'file'     => 'harvest-12-footer.json',
		'image'    => 'harvest-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'harvest',
	),
	array(
		'name'             => __( 'Maison – Header', 'envo-blocks' ),
		'file'             => 'maison-01-header.json',
		'image'            => 'maison-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'maison',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Maison – Hero: The bag edit · Autumn 2026', 'envo-blocks' ),
		'file'     => 'maison-02-hero.json',
		'image'    => 'maison-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'maison',
	),
	array(
		'name'             => __( 'Maison – Banner: The Everyday', 'envo-blocks' ),
		'file'             => 'maison-03-banner.json',
		'image'            => 'maison-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'maison',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Maison – Banner: Craft', 'envo-blocks' ),
		'file'     => 'maison-04-banner.json',
		'image'    => 'maison-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'maison',
	),
	array(
		'name'     => __( 'Maison – Banner: Compare', 'envo-blocks' ),
		'file'     => 'maison-05-banner.json',
		'image'    => 'maison-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'maison',
	),
	array(
		'name'             => __( 'Maison – Reviews', 'envo-blocks' ),
		'file'             => 'maison-06-reviews.json',
		'image'            => 'maison-06-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'maison',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Maison – Features', 'envo-blocks' ),
		'file'     => 'maison-07-features.json',
		'image'    => 'maison-07-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'maison',
	),
	array(
		'name'             => __( 'Maison – Footer', 'envo-blocks' ),
		'file'             => 'maison-09-footer.json',
		'image'            => 'maison-09-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'maison',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurum – Header', 'envo-blocks' ),
		'file'             => 'aurum-01-header.json',
		'image'            => 'aurum-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'aurum',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Aurum – Hero: Eau de Parfum', 'envo-blocks' ),
		'file'     => 'aurum-02-hero.json',
		'image'    => 'aurum-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'aurum',
	),
	array(
		'name'     => __( 'Aurum – Banner: The perfumer', 'envo-blocks' ),
		'file'     => 'aurum-03-banner.json',
		'image'    => 'aurum-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aurum',
	),
	array(
		'name'     => __( 'Aurum – Banner: The notes', 'envo-blocks' ),
		'file'     => 'aurum-04-banner.json',
		'image'    => 'aurum-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aurum',
	),
	array(
		'name'     => __( 'Aurum – Banner: The bottle', 'envo-blocks' ),
		'file'     => 'aurum-05-banner.json',
		'image'    => 'aurum-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aurum',
	),
	array(
		'name'             => __( 'Aurum – Banner: Sizes', 'envo-blocks' ),
		'file'             => 'aurum-06-banner.json',
		'image'            => 'aurum-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'aurum',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Aurum – Banner: The ritual', 'envo-blocks' ),
		'file'     => 'aurum-07-banner.json',
		'image'    => 'aurum-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'aurum',
	),
	array(
		'name'             => __( 'Aurum – Reviews', 'envo-blocks' ),
		'file'             => 'aurum-08-reviews.json',
		'image'            => 'aurum-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'aurum',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurum – Products: The shelf', 'envo-blocks' ),
		'file'             => 'aurum-09-products.json',
		'image'            => 'aurum-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'aurum',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurum – Banner: Make it yours', 'envo-blocks' ),
		'file'             => 'aurum-10-banner.json',
		'image'            => 'aurum-10-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'aurum',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Aurum – Footer', 'envo-blocks' ),
		'file'             => 'aurum-11-footer.json',
		'image'            => 'aurum-11-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'aurum',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Teklo – Header', 'envo-blocks' ),
		'file'             => 'teklo-01-header.json',
		'image'            => 'teklo-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'teklo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Teklo – Hero: Studio Pro Headphones', 'envo-blocks' ),
		'file'     => 'teklo-02-hero.json',
		'image'    => 'teklo-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'teklo',
	),
	array(
		'name'     => __( 'Teklo – Banner: Log in and get up to 50% off', 'envo-blocks' ),
		'file'     => 'teklo-03-banner.json',
		'image'    => 'teklo-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'teklo',
	),
	array(
		'name'     => __( 'Teklo – Banner: Popular categories', 'envo-blocks' ),
		'file'     => 'teklo-04-banner.json',
		'image'    => 'teklo-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'teklo',
	),
	array(
		'name'             => __( 'Teklo – Products: Trending accessories', 'envo-blocks' ),
		'file'             => 'teklo-05-products.json',
		'image'            => 'teklo-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'teklo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Teklo – Banner: Experience sound', 'envo-blocks' ),
		'file'             => 'teklo-06-banner.json',
		'image'            => 'teklo-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'teklo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Teklo – About: Our advantages', 'envo-blocks' ),
		'file'     => 'teklo-07-about.json',
		'image'    => 'teklo-07-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'teklo',
	),
	array(
		'name'             => __( 'Teklo – Deals: Deal of the day', 'envo-blocks' ),
		'file'             => 'teklo-08-deals.json',
		'image'            => 'teklo-08-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'teklo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Teklo – Products: Recently launched', 'envo-blocks' ),
		'file'             => 'teklo-09-products.json',
		'image'            => 'teklo-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'teklo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Teklo – About: Popular brands', 'envo-blocks' ),
		'file'     => 'teklo-10-about.json',
		'image'    => 'teklo-10-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'teklo',
	),
	array(
		'name'             => __( 'Teklo – Products: Top picks for you', 'envo-blocks' ),
		'file'             => 'teklo-11-products.json',
		'image'            => 'teklo-11-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'teklo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Teklo – Blog: From our articles', 'envo-blocks' ),
		'file'     => 'teklo-12-blog.json',
		'image'    => 'teklo-12-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'teklo',
	),
	array(
		'name'     => __( 'Teklo – Banner: All promotions →', 'envo-blocks' ),
		'file'     => 'teklo-13-banner.json',
		'image'    => 'teklo-13-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'teklo',
	),
	array(
		'name'     => __( 'Teklo – Footer', 'envo-blocks' ),
		'file'     => 'teklo-14-footer.json',
		'image'    => 'teklo-14-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'teklo',
	),
	array(
		'name'             => __( 'Redline – Header', 'envo-blocks' ),
		'file'             => 'redline-01-header.json',
		'image'            => 'redline-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'redline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Redline – Hero: New 2026 Charger SXT RWD', 'envo-blocks' ),
		'file'     => 'redline-02-hero.json',
		'image'    => 'redline-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'redline',
	),
	array(
		'name'     => __( 'Redline – Banner: 300 hp', 'envo-blocks' ),
		'file'     => 'redline-03-banner.json',
		'image'    => 'redline-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redline',
	),
	array(
		'name'             => __( 'Redline – Banner: Walk around', 'envo-blocks' ),
		'file'             => 'redline-04-banner.json',
		'image'            => 'redline-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'redline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Redline – Banner: Inside and out', 'envo-blocks' ),
		'file'     => 'redline-05-banner.json',
		'image'    => 'redline-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redline',
	),
	array(
		'name'     => __( 'Redline – Banner: Ways to buy', 'envo-blocks' ),
		'file'     => 'redline-06-banner.json',
		'image'    => 'redline-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redline',
	),
	array(
		'name'     => __( 'Redline – Banner: The lineup', 'envo-blocks' ),
		'file'     => 'redline-07-banner.json',
		'image'    => 'redline-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redline',
	),
	array(
		'name'             => __( 'Redline – Reviews: Owners', 'envo-blocks' ),
		'file'             => 'redline-08-reviews.json',
		'image'            => 'redline-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'redline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Redline – Banner: Test drive', 'envo-blocks' ),
		'file'     => 'redline-09-banner.json',
		'image'    => 'redline-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redline',
	),
	array(
		'name'     => __( 'Redline – FAQ', 'envo-blocks' ),
		'file'     => 'redline-10-faq.json',
		'image'    => 'redline-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'redline',
	),
	array(
		'name'             => __( 'Redline – Footer', 'envo-blocks' ),
		'file'             => 'redline-11-footer.json',
		'image'            => 'redline-11-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'redline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tidal – Header', 'envo-blocks' ),
		'file'             => 'tidal-01-header.json',
		'image'            => 'tidal-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'tidal',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Tidal – Hero: Submariner Date · 41 mm', 'envo-blocks' ),
		'file'     => 'tidal-02-hero.json',
		'image'    => 'tidal-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'tidal',
	),
	array(
		'name'     => __( 'Tidal – Banner: 300 m', 'envo-blocks' ),
		'file'     => 'tidal-03-banner.json',
		'image'    => 'tidal-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tidal',
	),
	array(
		'name'     => __( 'Tidal – Banner: The watch', 'envo-blocks' ),
		'file'     => 'tidal-04-banner.json',
		'image'    => 'tidal-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tidal',
	),
	array(
		'name'     => __( 'Tidal – Banner', 'envo-blocks' ),
		'file'     => 'tidal-05-banner.json',
		'image'    => 'tidal-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tidal',
	),
	array(
		'name'     => __( 'Tidal – Banner: Purchase', 'envo-blocks' ),
		'file'     => 'tidal-06-banner.json',
		'image'    => 'tidal-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tidal',
	),
	array(
		'name'     => __( 'Tidal – Banner: The collection', 'envo-blocks' ),
		'file'     => 'tidal-07-banner.json',
		'image'    => 'tidal-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tidal',
	),
	array(
		'name'             => __( 'Tidal – Reviews: Owners', 'envo-blocks' ),
		'file'             => 'tidal-08-reviews.json',
		'image'            => 'tidal-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'tidal',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Tidal – FAQ: Care', 'envo-blocks' ),
		'file'     => 'tidal-09-faq.json',
		'image'    => 'tidal-09-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'tidal',
	),
	array(
		'name'             => __( 'Tidal – Footer', 'envo-blocks' ),
		'file'             => 'tidal-10-footer.json',
		'image'            => 'tidal-10-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'tidal',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Petal – Header', 'envo-blocks' ),
		'file'             => 'petal-01-header.json',
		'image'            => 'petal-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'petal',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Petal – Hero: New season · clean beauty', 'envo-blocks' ),
		'file'     => 'petal-02-hero.json',
		'image'    => 'petal-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'petal',
	),
	array(
		'name'     => __( 'Petal – Features', 'envo-blocks' ),
		'file'     => 'petal-03-features.json',
		'image'    => 'petal-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'petal',
	),
	array(
		'name'             => __( 'Petal – Products: Featured products', 'envo-blocks' ),
		'file'             => 'petal-05-products.json',
		'image'            => 'petal-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'petal',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Petal – Banner: Cosmetic creams', 'envo-blocks' ),
		'file'     => 'petal-06-banner.json',
		'image'    => 'petal-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'petal',
	),
	array(
		'name'     => __( 'Petal – Banner: Discover the power of beauty', 'envo-blocks' ),
		'file'     => 'petal-07-banner.json',
		'image'    => 'petal-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'petal',
	),
	array(
		'name'             => __( 'Petal – Products: Beauty products', 'envo-blocks' ),
		'file'             => 'petal-08-products.json',
		'image'            => 'petal-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'petal',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Petal – About: Rituals by our community', 'envo-blocks' ),
		'file'     => 'petal-09-about.json',
		'image'    => 'petal-09-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'petal',
	),
	array(
		'name'     => __( 'Petal – Banner: Kerry Matthews', 'envo-blocks' ),
		'file'     => 'petal-10-banner.json',
		'image'    => 'petal-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'petal',
	),
	array(
		'name'     => __( 'Petal – Blog: Our beauty journal', 'envo-blocks' ),
		'file'     => 'petal-11-blog.json',
		'image'    => 'petal-11-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'petal',
	),
	array(
		'name'     => __( 'Petal – Footer', 'envo-blocks' ),
		'file'     => 'petal-13-footer.json',
		'image'    => 'petal-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'petal',
	),
	array(
		'name'             => __( 'Torque – Header', 'envo-blocks' ),
		'file'             => 'torque-01-header.json',
		'image'            => 'torque-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'torque',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Torque – Hero: Sport sedans', 'envo-blocks' ),
		'file'     => 'torque-02-hero.json',
		'image'    => 'torque-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'torque',
	),
	array(
		'name'             => __( 'Torque – Banner: Amazing offer', 'envo-blocks' ),
		'file'             => 'torque-03-banner.json',
		'image'            => 'torque-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'torque',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Torque – Banner: Up to 30% off', 'envo-blocks' ),
		'file'             => 'torque-04-banner.json',
		'image'            => 'torque-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'torque',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Torque – Products: Top picks', 'envo-blocks' ),
		'file'             => 'torque-05-products.json',
		'image'            => 'torque-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'torque',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Torque – Products: Trending deals', 'envo-blocks' ),
		'file'             => 'torque-06-products.json',
		'image'            => 'torque-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'torque',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Torque – Banner: Up to 25% discount', 'envo-blocks' ),
		'file'     => 'torque-07-banner.json',
		'image'    => 'torque-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'torque',
	),
	array(
		'name'             => __( 'Torque – Deals: Deal of the day', 'envo-blocks' ),
		'file'             => 'torque-08-deals.json',
		'image'            => 'torque-08-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'torque',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Torque – Banner: New for 2026', 'envo-blocks' ),
		'file'     => 'torque-09-banner.json',
		'image'    => 'torque-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'torque',
	),
	array(
		'name'     => __( 'Torque – Features', 'envo-blocks' ),
		'file'     => 'torque-10-features.json',
		'image'    => 'torque-10-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'torque',
	),
	array(
		'name'             => __( 'Torque – Products: Best sellers', 'envo-blocks' ),
		'file'             => 'torque-11-products.json',
		'image'            => 'torque-11-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'torque',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Torque – Banner', 'envo-blocks' ),
		'file'     => 'torque-12-banner.json',
		'image'    => 'torque-12-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'torque',
	),
	array(
		'name'     => __( 'Torque – Footer', 'envo-blocks' ),
		'file'     => 'torque-13-footer.json',
		'image'    => 'torque-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'torque',
	),
	array(
		'name'             => __( 'Tech Store – Header', 'envo-blocks' ),
		'file'             => 'tech-store-01-header.json',
		'image'            => 'tech-store-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Hero: Just dropped', 'envo-blocks' ),
		'file'             => 'tech-store-02-hero.json',
		'image'            => 'tech-store-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Tech Store – Banner', 'envo-blocks' ),
		'file'     => 'tech-store-03-banner.json',
		'image'    => 'tech-store-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tech-store',
	),
	array(
		'name'             => __( 'Tech Store – Categories: Popular Categories', 'envo-blocks' ),
		'file'             => 'tech-store-04-categories.json',
		'image'            => 'tech-store-04-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Deals: Hurry Up Deals', 'envo-blocks' ),
		'file'             => 'tech-store-05-deals.json',
		'image'            => 'tech-store-05-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Categories', 'envo-blocks' ),
		'file'             => 'tech-store-06-categories.json',
		'image'            => 'tech-store-06-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Banner', 'envo-blocks' ),
		'file'             => 'tech-store-07-banner.json',
		'image'            => 'tech-store-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Products: Featured Products', 'envo-blocks' ),
		'file'             => 'tech-store-08-products.json',
		'image'            => 'tech-store-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Products: Bundle & save', 'envo-blocks' ),
		'file'             => 'tech-store-09-products.json',
		'image'            => 'tech-store-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Products', 'envo-blocks' ),
		'file'             => 'tech-store-10-products.json',
		'image'            => 'tech-store-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Reviews: Customers Testimonials', 'envo-blocks' ),
		'file'             => 'tech-store-11-reviews.json',
		'image'            => 'tech-store-11-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Brands', 'envo-blocks' ),
		'file'             => 'tech-store-12-brands.json',
		'image'            => 'tech-store-12-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tech Store – Products: From Instagram', 'envo-blocks' ),
		'file'             => 'tech-store-13-products.json',
		'image'            => 'tech-store-13-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'tech-store',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Tech Store – Footer', 'envo-blocks' ),
		'file'     => 'tech-store-14-footer.json',
		'image'    => 'tech-store-14-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'tech-store',
	),
	array(
		'name'             => __( 'Clubhouse – Header', 'envo-blocks' ),
		'file'             => 'clubhouse-01-header.json',
		'image'            => 'clubhouse-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'clubhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Clubhouse – Hero: The golf shop', 'envo-blocks' ),
		'file'     => 'clubhouse-02-hero.json',
		'image'    => 'clubhouse-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'clubhouse',
	),
	array(
		'name'     => __( 'Clubhouse – Features', 'envo-blocks' ),
		'file'     => 'clubhouse-03-features.json',
		'image'    => 'clubhouse-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'clubhouse',
	),
	array(
		'name'             => __( 'Clubhouse – About: Everything for your game', 'envo-blocks' ),
		'file'             => 'clubhouse-04-about.json',
		'image'            => 'clubhouse-04-about.webp',
		'category'         => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'             => 'clubhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Clubhouse – Products: New in the pro shop', 'envo-blocks' ),
		'file'             => 'clubhouse-05-products.json',
		'image'            => 'clubhouse-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'clubhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Clubhouse – Banner: Members\' trip 2026', 'envo-blocks' ),
		'file'     => 'clubhouse-06-banner.json',
		'image'    => 'clubhouse-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'clubhouse',
	),
	array(
		'name'             => __( 'Clubhouse – Deals: Forged irons, $100 off', 'envo-blocks' ),
		'file'             => 'clubhouse-07-deals.json',
		'image'            => 'clubhouse-07-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'clubhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Clubhouse – Banner: Fitting studio', 'envo-blocks' ),
		'file'     => 'clubhouse-08-banner.json',
		'image'    => 'clubhouse-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'clubhouse',
	),
	array(
		'name'             => __( 'Clubhouse – Products: Made for clay', 'envo-blocks' ),
		'file'             => 'clubhouse-09-products.json',
		'image'            => 'clubhouse-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'clubhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Clubhouse – About: Where our members play', 'envo-blocks' ),
		'file'     => 'clubhouse-10-about.json',
		'image'    => 'clubhouse-10-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'clubhouse',
	),
	array(
		'name'             => __( 'Clubhouse – Reviews: What our players say', 'envo-blocks' ),
		'file'             => 'clubhouse-11-reviews.json',
		'image'            => 'clubhouse-11-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'clubhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Clubhouse – Footer', 'envo-blocks' ),
		'file'     => 'clubhouse-13-footer.json',
		'image'    => 'clubhouse-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'clubhouse',
	),
	array(
		'name'             => __( 'Fashion Boutique – Header', 'envo-blocks' ),
		'file'             => 'fashion-boutique-01-header.json',
		'image'            => 'fashion-boutique-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fashion Boutique – Hero: Summer sale', 'envo-blocks' ),
		'file'             => 'fashion-boutique-02-hero.json',
		'image'            => 'fashion-boutique-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fashion Boutique – Categories', 'envo-blocks' ),
		'file'             => 'fashion-boutique-03-categories.json',
		'image'            => 'fashion-boutique-03-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fashion Boutique – Products: Featured products', 'envo-blocks' ),
		'file'             => 'fashion-boutique-04-products.json',
		'image'            => 'fashion-boutique-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fashion Boutique – Products: Top rated products', 'envo-blocks' ),
		'file'             => 'fashion-boutique-05-products.json',
		'image'            => 'fashion-boutique-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fashion Boutique – Banner: Clothing & more', 'envo-blocks' ),
		'file'             => 'fashion-boutique-06-banner.json',
		'image'            => 'fashion-boutique-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fashion Boutique – Products: New arrivals', 'envo-blocks' ),
		'file'             => 'fashion-boutique-07-products.json',
		'image'            => 'fashion-boutique-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Fashion Boutique – Banner: Free shipping & return', 'envo-blocks' ),
		'file'     => 'fashion-boutique-08-banner.json',
		'image'    => 'fashion-boutique-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'fashion-boutique',
	),
	array(
		'name'             => __( 'Fashion Boutique – Reviews: Testimonials', 'envo-blocks' ),
		'file'             => 'fashion-boutique-09-reviews.json',
		'image'            => 'fashion-boutique-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Fashion Boutique – Brands', 'envo-blocks' ),
		'file'             => 'fashion-boutique-10-brands.json',
		'image'            => 'fashion-boutique-10-brands.webp',
		'category'         => array(
			__( 'Brands', 'envo-blocks' ),
		),
		'demo'             => 'fashion-boutique',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Fashion Boutique – Newsletter: Subscribe newsletter', 'envo-blocks' ),
		'file'     => 'fashion-boutique-11-newsletter.json',
		'image'    => 'fashion-boutique-11-newsletter.webp',
		'category' => array(
			__( 'Newsletter', 'envo-blocks' ),
		),
		'demo'     => 'fashion-boutique',
	),
	array(
		'name'     => __( 'Fashion Boutique – Blog: From the blog', 'envo-blocks' ),
		'file'     => 'fashion-boutique-12-blog.json',
		'image'    => 'fashion-boutique-12-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'fashion-boutique',
	),
	array(
		'name'     => __( 'Fashion Boutique – Footer', 'envo-blocks' ),
		'file'     => 'fashion-boutique-13-footer.json',
		'image'    => 'fashion-boutique-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'fashion-boutique',
	),
	array(
		'name'             => __( 'Simmer – Header', 'envo-blocks' ),
		'file'             => 'simmer-01-header.json',
		'image'            => 'simmer-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'simmer',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Simmer – Hero: A kitchen made for espresso', 'envo-blocks' ),
		'file'             => 'simmer-02-hero.json',
		'image'            => 'simmer-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'simmer',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Simmer – Features', 'envo-blocks' ),
		'file'     => 'simmer-03-features.json',
		'image'    => 'simmer-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'simmer',
	),
	array(
		'name'             => __( 'Simmer – Banner: Shop by ritual', 'envo-blocks' ),
		'file'             => 'simmer-04-banner.json',
		'image'            => 'simmer-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'simmer',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Simmer – Products: Loved in our test kitchen', 'envo-blocks' ),
		'file'             => 'simmer-05-products.json',
		'image'            => 'simmer-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'simmer',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Simmer – Banner: Bake night', 'envo-blocks' ),
		'file'     => 'simmer-06-banner.json',
		'image'    => 'simmer-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'simmer',
	),
	array(
		'name'             => __( 'Simmer – Deals: The rose mixer, $50 off', 'envo-blocks' ),
		'file'             => 'simmer-07-deals.json',
		'image'            => 'simmer-07-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'simmer',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Simmer – Banner: Kitchen planning', 'envo-blocks' ),
		'file'     => 'simmer-08-banner.json',
		'image'    => 'simmer-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'simmer',
	),
	array(
		'name'             => __( 'Simmer – Products: Just landed', 'envo-blocks' ),
		'file'             => 'simmer-09-products.json',
		'image'            => 'simmer-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'simmer',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Simmer – Banner: Recipes, tips & good habits', 'envo-blocks' ),
		'file'     => 'simmer-10-banner.json',
		'image'    => 'simmer-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'simmer',
	),
	array(
		'name'             => __( 'Simmer – Reviews: What our customers make', 'envo-blocks' ),
		'file'             => 'simmer-11-reviews.json',
		'image'            => 'simmer-11-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'simmer',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Simmer – About: Your e-mail address', 'envo-blocks' ),
		'file'     => 'simmer-12-about.json',
		'image'    => 'simmer-12-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'simmer',
	),
	array(
		'name'     => __( 'Simmer – Footer', 'envo-blocks' ),
		'file'     => 'simmer-14-footer.json',
		'image'    => 'simmer-14-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'simmer',
	),
	array(
		'name'             => __( 'Talon – Header', 'envo-blocks' ),
		'file'             => 'talon-01-header.json',
		'image'            => 'talon-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'talon',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Talon – Hero: Drop 01 · The circuit eagle', 'envo-blocks' ),
		'file'             => 'talon-02-hero.json',
		'image'            => 'talon-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'talon',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Talon – Banner: 180 g/m²', 'envo-blocks' ),
		'file'     => 'talon-03-banner.json',
		'image'    => 'talon-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'talon',
	),
	array(
		'name'             => __( 'Talon – Banner: Colours', 'envo-blocks' ),
		'file'             => 'talon-04-banner.json',
		'image'            => 'talon-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'talon',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Talon – Banner: Front & back', 'envo-blocks' ),
		'file'             => 'talon-05-banner.json',
		'image'            => 'talon-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'talon',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Talon – Banner: Details', 'envo-blocks' ),
		'file'             => 'talon-06-banner.json',
		'image'            => 'talon-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'talon',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Talon – Banner: Fit', 'envo-blocks' ),
		'file'     => 'talon-07-banner.json',
		'image'    => 'talon-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'talon',
	),
	array(
		'name'             => __( 'Talon – Reviews', 'envo-blocks' ),
		'file'             => 'talon-08-reviews.json',
		'image'            => 'talon-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'talon',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Talon – Banner: Buy', 'envo-blocks' ),
		'file'             => 'talon-09-banner.json',
		'image'            => 'talon-09-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'talon',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Talon – FAQ', 'envo-blocks' ),
		'file'     => 'talon-10-faq.json',
		'image'    => 'talon-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'talon',
	),
	array(
		'name'             => __( 'Talon – Footer', 'envo-blocks' ),
		'file'             => 'talon-11-footer.json',
		'image'            => 'talon-11-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'talon',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tempo – Header', 'envo-blocks' ),
		'file'             => 'tempo-01-header.json',
		'image'            => 'tempo-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'tempo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tempo – Hero: Made to roam.', 'envo-blocks' ),
		'file'             => 'tempo-02-hero.json',
		'image'            => 'tempo-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'tempo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Tempo – Banner', 'envo-blocks' ),
		'file'     => 'tempo-03-banner.json',
		'image'    => 'tempo-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tempo',
	),
	array(
		'name'             => __( 'Tempo – Banner: Walk around', 'envo-blocks' ),
		'file'             => 'tempo-04-banner.json',
		'image'            => 'tempo-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'tempo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tempo – Banner: The tech', 'envo-blocks' ),
		'file'             => 'tempo-05-banner.json',
		'image'            => 'tempo-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'tempo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Tempo – Banner: 310 g', 'envo-blocks' ),
		'file'     => 'tempo-06-banner.json',
		'image'    => 'tempo-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tempo',
	),
	array(
		'name'     => __( 'Tempo – Banner: Every street is a track', 'envo-blocks' ),
		'file'     => 'tempo-07-banner.json',
		'image'    => 'tempo-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tempo',
	),
	array(
		'name'     => __( 'Tempo – Banner: Sizes', 'envo-blocks' ),
		'file'     => 'tempo-08-banner.json',
		'image'    => 'tempo-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'tempo',
	),
	array(
		'name'             => __( 'Tempo – Reviews: From the crew', 'envo-blocks' ),
		'file'             => 'tempo-09-reviews.json',
		'image'            => 'tempo-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'tempo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tempo – Products: More kicks', 'envo-blocks' ),
		'file'             => 'tempo-10-products.json',
		'image'            => 'tempo-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'tempo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Tempo – Banner: Buy', 'envo-blocks' ),
		'file'             => 'tempo-11-banner.json',
		'image'            => 'tempo-11-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'tempo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Tempo – FAQ', 'envo-blocks' ),
		'file'     => 'tempo-12-faq.json',
		'image'    => 'tempo-12-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'tempo',
	),
	array(
		'name'             => __( 'Tempo – Footer', 'envo-blocks' ),
		'file'             => 'tempo-14-footer.json',
		'image'            => 'tempo-14-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'tempo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Volt – Header', 'envo-blocks' ),
		'file'             => 'volt-01-header.json',
		'image'            => 'volt-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'volt',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Volt – Hero', 'envo-blocks' ),
		'file'     => 'volt-02-hero.json',
		'image'    => 'volt-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'volt',
	),
	array(
		'name'             => __( 'Volt – Products: Our best selling phones', 'envo-blocks' ),
		'file'             => 'volt-03-products.json',
		'image'            => 'volt-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'volt',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Volt – Products: Looking for your next gadget?', 'envo-blocks' ),
		'file'             => 'volt-04-products.json',
		'image'            => 'volt-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'volt',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Volt – About: Why choose Volt', 'envo-blocks' ),
		'file'     => 'volt-05-about.json',
		'image'    => 'volt-05-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'volt',
	),
	array(
		'name'     => __( 'Volt – Banner: Same-day repair', 'envo-blocks' ),
		'file'     => 'volt-06-banner.json',
		'image'    => 'volt-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'volt',
	),
	array(
		'name'     => __( 'Volt – Banner: 36k+', 'envo-blocks' ),
		'file'     => 'volt-07-banner.json',
		'image'    => 'volt-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'volt',
	),
	array(
		'name'     => __( 'Volt – Banner: Trending setups of the day', 'envo-blocks' ),
		'file'     => 'volt-08-banner.json',
		'image'    => 'volt-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'volt',
	),
	array(
		'name'             => __( 'Volt – FAQ: Have any questions?', 'envo-blocks' ),
		'file'             => 'volt-09-faq.json',
		'image'            => 'volt-09-faq.webp',
		'category'         => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'             => 'volt',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Volt – Footer', 'envo-blocks' ),
		'file'     => 'volt-10-footer.json',
		'image'    => 'volt-10-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'volt',
	),
	array(
		'name'             => __( 'Arc – Header', 'envo-blocks' ),
		'file'             => 'arc-01-header.json',
		'image'            => 'arc-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arc – Hero: 2027 · 1000 cc superbike', 'envo-blocks' ),
		'file'             => 'arc-02-hero.json',
		'image'            => 'arc-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Arc – Banner: 212 hp', 'envo-blocks' ),
		'file'     => 'arc-03-banner.json',
		'image'    => 'arc-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'arc',
	),
	array(
		'name'             => __( 'Arc – Banner: Design', 'envo-blocks' ),
		'file'             => 'arc-04-banner.json',
		'image'            => 'arc-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arc – Banner: Riding modes', 'envo-blocks' ),
		'file'             => 'arc-05-banner.json',
		'image'            => 'arc-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Arc – Banner: Every angle', 'envo-blocks' ),
		'file'     => 'arc-06-banner.json',
		'image'    => 'arc-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'arc',
	),
	array(
		'name'     => __( 'Arc – Banner: On track', 'envo-blocks' ),
		'file'     => 'arc-07-banner.json',
		'image'    => 'arc-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'arc',
	),
	array(
		'name'     => __( 'Arc – Banner: Packages', 'envo-blocks' ),
		'file'     => 'arc-08-banner.json',
		'image'    => 'arc-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'arc',
	),
	array(
		'name'     => __( 'Arc – Banner: Test ride', 'envo-blocks' ),
		'file'     => 'arc-09-banner.json',
		'image'    => 'arc-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'arc',
	),
	array(
		'name'     => __( 'Arc – Banner: Specifications', 'envo-blocks' ),
		'file'     => 'arc-10-banner.json',
		'image'    => 'arc-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'arc',
	),
	array(
		'name'             => __( 'Arc – Reviews: Owners', 'envo-blocks' ),
		'file'             => 'arc-11-reviews.json',
		'image'            => 'arc-11-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arc – Banner: Reserve', 'envo-blocks' ),
		'file'             => 'arc-12-banner.json',
		'image'            => 'arc-12-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arc – Products: The range', 'envo-blocks' ),
		'file'             => 'arc-13-products.json',
		'image'            => 'arc-13-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arc – FAQ', 'envo-blocks' ),
		'file'             => 'arc-14-faq.json',
		'image'            => 'arc-14-faq.webp',
		'category'         => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Arc – Footer', 'envo-blocks' ),
		'file'             => 'arc-15-footer.json',
		'image'            => 'arc-15-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'arc',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Saltwater – Header', 'envo-blocks' ),
		'file'             => 'saltwater-01-header.json',
		'image'            => 'saltwater-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'saltwater',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Saltwater – Hero: New colour', 'envo-blocks' ),
		'file'             => 'saltwater-02-hero.json',
		'image'            => 'saltwater-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'saltwater',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Saltwater – Features', 'envo-blocks' ),
		'file'     => 'saltwater-03-features.json',
		'image'    => 'saltwater-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'saltwater',
	),
	array(
		'name'             => __( 'Saltwater – Banner: Shop by colour', 'envo-blocks' ),
		'file'             => 'saltwater-04-banner.json',
		'image'            => 'saltwater-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'saltwater',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Saltwater – Products: Best sellers', 'envo-blocks' ),
		'file'             => 'saltwater-05-products.json',
		'image'            => 'saltwater-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'saltwater',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Saltwater – Banner: Made for salt, sun & chlorine', 'envo-blocks' ),
		'file'     => 'saltwater-06-banner.json',
		'image'    => 'saltwater-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'saltwater',
	),
	array(
		'name'             => __( 'Saltwater – Banner: See the back', 'envo-blocks' ),
		'file'             => 'saltwater-07-banner.json',
		'image'            => 'saltwater-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'saltwater',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Saltwater – Deals: Deal of the week', 'envo-blocks' ),
		'file'             => 'saltwater-08-deals.json',
		'image'            => 'saltwater-08-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'saltwater',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Saltwater – About: Find your fit', 'envo-blocks' ),
		'file'     => 'saltwater-09-about.json',
		'image'    => 'saltwater-09-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'saltwater',
	),
	array(
		'name'             => __( 'Saltwater – Products: Complete the look', 'envo-blocks' ),
		'file'             => 'saltwater-10-products.json',
		'image'            => 'saltwater-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'saltwater',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Saltwater – Reviews', 'envo-blocks' ),
		'file'             => 'saltwater-11-reviews.json',
		'image'            => 'saltwater-11-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'saltwater',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Saltwater – Banner: saltwater.swim', 'envo-blocks' ),
		'file'     => 'saltwater-12-banner.json',
		'image'    => 'saltwater-12-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'saltwater',
	),
	array(
		'name'     => __( 'Saltwater – About: 10% off your first swimsuit', 'envo-blocks' ),
		'file'     => 'saltwater-13-about.json',
		'image'    => 'saltwater-13-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'saltwater',
	),
	array(
		'name'     => __( 'Saltwater – Footer', 'envo-blocks' ),
		'file'     => 'saltwater-15-footer.json',
		'image'    => 'saltwater-15-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'saltwater',
	),
	array(
		'name'             => __( 'Halo – Header', 'envo-blocks' ),
		'file'             => 'halo-01-header.json',
		'image'            => 'halo-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'halo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Halo – Hero: 498 g', 'envo-blocks' ),
		'file'     => 'halo-02-hero.json',
		'image'    => 'halo-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'halo',
	),
	array(
		'name'             => __( 'Halo – Banner: Overview', 'envo-blocks' ),
		'file'             => 'halo-03-banner.json',
		'image'            => 'halo-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'halo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Halo – Banner: Experiences', 'envo-blocks' ),
		'file'     => 'halo-04-banner.json',
		'image'    => 'halo-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'halo',
	),
	array(
		'name'     => __( 'Halo – Banner: The film', 'envo-blocks' ),
		'file'     => 'halo-05-banner.json',
		'image'    => 'halo-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'halo',
	),
	array(
		'name'     => __( 'Halo – Banner: Bundles', 'envo-blocks' ),
		'file'     => 'halo-06-banner.json',
		'image'    => 'halo-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'halo',
	),
	array(
		'name'     => __( 'Halo – Banner: Specifications', 'envo-blocks' ),
		'file'     => 'halo-07-banner.json',
		'image'    => 'halo-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'halo',
	),
	array(
		'name'             => __( 'Halo – Reviews', 'envo-blocks' ),
		'file'             => 'halo-08-reviews.json',
		'image'            => 'halo-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'halo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Halo – Banner: Buy', 'envo-blocks' ),
		'file'             => 'halo-09-banner.json',
		'image'            => 'halo-09-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'halo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Halo – FAQ', 'envo-blocks' ),
		'file'     => 'halo-10-faq.json',
		'image'    => 'halo-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'halo',
	),
	array(
		'name'             => __( 'Halo – Footer', 'envo-blocks' ),
		'file'             => 'halo-11-footer.json',
		'image'            => 'halo-11-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'halo',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Forge – Header', 'envo-blocks' ),
		'file'             => 'forge-01-header.json',
		'image'            => 'forge-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Forge – Hero: New season', 'envo-blocks' ),
		'file'             => 'forge-02-hero.json',
		'image'            => 'forge-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Forge – Banner: Best seller', 'envo-blocks' ),
		'file'             => 'forge-03-banner.json',
		'image'            => 'forge-03-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Forge – Banner: Shop by categories', 'envo-blocks' ),
		'file'             => 'forge-04-banner.json',
		'image'            => 'forge-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Forge – Products: New arrivals', 'envo-blocks' ),
		'file'             => 'forge-05-products.json',
		'image'            => 'forge-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Forge – Banner: The logo hoodie, made to last', 'envo-blocks' ),
		'file'             => 'forge-06-banner.json',
		'image'            => 'forge-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Forge – Deals: Hot deals', 'envo-blocks' ),
		'file'             => 'forge-07-deals.json',
		'image'            => 'forge-07-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Forge – Banner: Seen from behind', 'envo-blocks' ),
		'file'     => 'forge-08-banner.json',
		'image'    => 'forge-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'forge',
	),
	array(
		'name'             => __( 'Forge – Products: Recently launched', 'envo-blocks' ),
		'file'             => 'forge-09-products.json',
		'image'            => 'forge-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Forge – Products: Finish the look', 'envo-blocks' ),
		'file'             => 'forge-10-products.json',
		'image'            => 'forge-10-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'forge',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Forge – Blog: From our journal', 'envo-blocks' ),
		'file'     => 'forge-11-blog.json',
		'image'    => 'forge-11-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'forge',
	),
	array(
		'name'     => __( 'Forge – Footer', 'envo-blocks' ),
		'file'     => 'forge-12-footer.json',
		'image'    => 'forge-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'forge',
	),
	array(
		'name'             => __( 'Ionix – Header', 'envo-blocks' ),
		'file'             => 'ionix-01-header.json',
		'image'            => 'ionix-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'ionix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ionix – Hero: Special offer VR zone', 'envo-blocks' ),
		'file'             => 'ionix-02-hero.json',
		'image'            => 'ionix-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'ionix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ionix – Banner: New technologies', 'envo-blocks' ),
		'file'     => 'ionix-03-banner.json',
		'image'    => 'ionix-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'ionix',
	),
	array(
		'name'             => __( 'Ionix – Products: Electronics', 'envo-blocks' ),
		'file'             => 'ionix-04-products.json',
		'image'            => 'ionix-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ionix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ionix – Banner: Hi-tech news', 'envo-blocks' ),
		'file'     => 'ionix-05-banner.json',
		'image'    => 'ionix-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'ionix',
	),
	array(
		'name'             => __( 'Ionix – Deals: Today hot deals', 'envo-blocks' ),
		'file'             => 'ionix-06-deals.json',
		'image'            => 'ionix-06-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'ionix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ionix – Products: Special offers', 'envo-blocks' ),
		'file'             => 'ionix-07-products.json',
		'image'            => 'ionix-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ionix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ionix – Banner: Gaming collection', 'envo-blocks' ),
		'file'             => 'ionix-08-banner.json',
		'image'            => 'ionix-08-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'ionix',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ionix – Blog: Innovative gadgets', 'envo-blocks' ),
		'file'     => 'ionix-09-blog.json',
		'image'    => 'ionix-09-blog.webp',
		'category' => array(
			__( 'Blog', 'envo-blocks' ),
		),
		'demo'     => 'ionix',
	),
	array(
		'name'     => __( 'Ionix – About: Our partners', 'envo-blocks' ),
		'file'     => 'ionix-10-about.json',
		'image'    => 'ionix-10-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'ionix',
	),
	array(
		'name'     => __( 'Ionix – Footer', 'envo-blocks' ),
		'file'     => 'ionix-12-footer.json',
		'image'    => 'ionix-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'ionix',
	),
	array(
		'name'             => __( 'Ironhouse – Header', 'envo-blocks' ),
		'file'             => 'ironhouse-01-header.json',
		'image'            => 'ironhouse-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'ironhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ironhouse – Hero: No joining fee · cancel any time', 'envo-blocks' ),
		'file'             => 'ironhouse-02-hero.json',
		'image'            => 'ironhouse-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'ironhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ironhouse – Banner', 'envo-blocks' ),
		'file'     => 'ironhouse-03-banner.json',
		'image'    => 'ironhouse-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'ironhouse',
	),
	array(
		'name'             => __( 'Ironhouse – Pricing: Memberships', 'envo-blocks' ),
		'file'             => 'ironhouse-04-pricing.json',
		'image'            => 'ironhouse-04-pricing.webp',
		'category'         => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'             => 'ironhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ironhouse – Products: Passes & training', 'envo-blocks' ),
		'file'             => 'ironhouse-05-products.json',
		'image'            => 'ironhouse-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'ironhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ironhouse – Banner: The club', 'envo-blocks' ),
		'file'             => 'ironhouse-06-banner.json',
		'image'            => 'ironhouse-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'ironhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ironhouse – Banner: Timetable', 'envo-blocks' ),
		'file'             => 'ironhouse-07-banner.json',
		'image'            => 'ironhouse-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'ironhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ironhouse – Banner: Personal training', 'envo-blocks' ),
		'file'     => 'ironhouse-08-banner.json',
		'image'    => 'ironhouse-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'ironhouse',
	),
	array(
		'name'             => __( 'Ironhouse – Reviews: Members', 'envo-blocks' ),
		'file'             => 'ironhouse-09-reviews.json',
		'image'            => 'ironhouse-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'ironhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ironhouse – FAQ', 'envo-blocks' ),
		'file'     => 'ironhouse-10-faq.json',
		'image'    => 'ironhouse-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'ironhouse',
	),
	array(
		'name'     => __( 'Ironhouse – Contact: Ironhouse Brooklyn', 'envo-blocks' ),
		'file'     => 'ironhouse-11-contact.json',
		'image'    => 'ironhouse-11-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'ironhouse',
	),
	array(
		'name'             => __( 'Ironhouse – About: Your first workout is waiting', 'envo-blocks' ),
		'file'             => 'ironhouse-12-about.json',
		'image'            => 'ironhouse-12-about.webp',
		'category'         => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'             => 'ironhouse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Ironhouse – Footer', 'envo-blocks' ),
		'file'     => 'ironhouse-14-footer.json',
		'image'    => 'ironhouse-14-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'ironhouse',
	),
	array(
		'name'             => __( 'Brewline – Header', 'envo-blocks' ),
		'file'             => 'brewline-01-header.json',
		'image'            => 'brewline-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'brewline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Brewline – Hero: Specialty coffee subscription', 'envo-blocks' ),
		'file'     => 'brewline-02-hero.json',
		'image'    => 'brewline-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'     => __( 'Brewline – Banner: How it works', 'envo-blocks' ),
		'file'     => 'brewline-03-banner.json',
		'image'    => 'brewline-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'             => __( 'Brewline – Banner: Subscribe', 'envo-blocks' ),
		'file'             => 'brewline-04-banner.json',
		'image'            => 'brewline-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'brewline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Brewline – Banner: Coffee of the month · November', 'envo-blocks' ),
		'file'     => 'brewline-05-banner.json',
		'image'    => 'brewline-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'             => __( 'Brewline – Banner: Taste quiz', 'envo-blocks' ),
		'file'             => 'brewline-06-banner.json',
		'image'            => 'brewline-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'brewline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Brewline – Banner: Single origins', 'envo-blocks' ),
		'file'     => 'brewline-07-banner.json',
		'image'    => 'brewline-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'             => __( 'Brewline – Products', 'envo-blocks' ),
		'file'             => 'brewline-08-products.json',
		'image'            => 'brewline-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'brewline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Brewline – Banner: Stock up', 'envo-blocks' ),
		'file'             => 'brewline-09-banner.json',
		'image'            => 'brewline-09-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'brewline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Brewline – Banner: Direct trade', 'envo-blocks' ),
		'file'     => 'brewline-10-banner.json',
		'image'    => 'brewline-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'     => __( 'Brewline – Banner: Brew guides', 'envo-blocks' ),
		'file'     => 'brewline-11-banner.json',
		'image'    => 'brewline-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'             => __( 'Brewline – Reviews', 'envo-blocks' ),
		'file'             => 'brewline-12-reviews.json',
		'image'            => 'brewline-12-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'brewline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Brewline – Banner: Gifts', 'envo-blocks' ),
		'file'     => 'brewline-13-banner.json',
		'image'    => 'brewline-13-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'     => __( 'Brewline – Contact: The roastery café', 'envo-blocks' ),
		'file'     => 'brewline-14-contact.json',
		'image'    => 'brewline-14-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'     => __( 'Brewline – Footer', 'envo-blocks' ),
		'file'     => 'brewline-16-footer.json',
		'image'    => 'brewline-16-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'brewline',
	),
	array(
		'name'             => __( 'Greenbox – Header', 'envo-blocks' ),
		'file'             => 'greenbox-01-header.json',
		'image'            => 'greenbox-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'greenbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Greenbox – Hero: Fresh from 40 local farms', 'envo-blocks' ),
		'file'     => 'greenbox-02-hero.json',
		'image'    => 'greenbox-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'     => __( 'Greenbox – Banner: 20 min recipes', 'envo-blocks' ),
		'file'     => 'greenbox-03-banner.json',
		'image'    => 'greenbox-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'     => __( 'Greenbox – Banner', 'envo-blocks' ),
		'file'     => 'greenbox-04-banner.json',
		'image'    => 'greenbox-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'             => __( 'Greenbox – Banner: Step 1 of 1', 'envo-blocks' ),
		'file'             => 'greenbox-05-banner.json',
		'image'            => 'greenbox-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'greenbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Greenbox – Banner: Week of 27 October', 'envo-blocks' ),
		'file'     => 'greenbox-06-banner.json',
		'image'    => 'greenbox-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'     => __( 'Greenbox – Banner: How it works', 'envo-blocks' ),
		'file'     => 'greenbox-07-banner.json',
		'image'    => 'greenbox-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'             => __( 'Greenbox – Products: Add-ons', 'envo-blocks' ),
		'file'             => 'greenbox-08-products.json',
		'image'            => 'greenbox-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'greenbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Greenbox – Banner: local farms within 120 miles', 'envo-blocks' ),
		'file'     => 'greenbox-09-banner.json',
		'image'    => 'greenbox-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'             => __( 'Greenbox – Reviews: 4.8 from 31,000 reviews', 'envo-blocks' ),
		'file'             => 'greenbox-10-reviews.json',
		'image'            => 'greenbox-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'greenbox',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Greenbox – Contact: Delivery', 'envo-blocks' ),
		'file'     => 'greenbox-11-contact.json',
		'image'    => 'greenbox-11-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'     => __( 'Greenbox – FAQ: questions? answers.', 'envo-blocks' ),
		'file'     => 'greenbox-12-faq.json',
		'image'    => 'greenbox-12-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'     => __( 'Greenbox – Footer', 'envo-blocks' ),
		'file'     => 'greenbox-13-footer.json',
		'image'    => 'greenbox-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'greenbox',
	),
	array(
		'name'             => __( 'Pawpack – Header', 'envo-blocks' ),
		'file'             => 'pawpack-01-header.json',
		'image'            => 'pawpack-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'pawpack',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pawpack – Hero: The October box: Spooky Snacks', 'envo-blocks' ),
		'file'             => 'pawpack-02-hero.json',
		'image'            => 'pawpack-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'pawpack',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pawpack – Banner', 'envo-blocks' ),
		'file'     => 'pawpack-03-banner.json',
		'image'    => 'pawpack-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pawpack',
	),
	array(
		'name'     => __( 'Pawpack – Banner: This month', 'envo-blocks' ),
		'file'     => 'pawpack-04-banner.json',
		'image'    => 'pawpack-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pawpack',
	),
	array(
		'name'             => __( 'Pawpack – Pricing: Plans', 'envo-blocks' ),
		'file'             => 'pawpack-05-pricing.json',
		'image'            => 'pawpack-05-pricing.webp',
		'category'         => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'             => 'pawpack',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pawpack – Banner: how it works', 'envo-blocks' ),
		'file'     => 'pawpack-06-banner.json',
		'image'    => 'pawpack-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pawpack',
	),
	array(
		'name'     => __( 'Pawpack – Banner: pawpackpups', 'envo-blocks' ),
		'file'     => 'pawpack-07-banner.json',
		'image'    => 'pawpack-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pawpack',
	),
	array(
		'name'             => __( 'Pawpack – Products: Extras', 'envo-blocks' ),
		'file'             => 'pawpack-08-products.json',
		'image'            => 'pawpack-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'pawpack',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pawpack – Banner: New', 'envo-blocks' ),
		'file'     => 'pawpack-09-banner.json',
		'image'    => 'pawpack-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pawpack',
	),
	array(
		'name'             => __( 'Pawpack – Reviews: tails are wagging', 'envo-blocks' ),
		'file'             => 'pawpack-10-reviews.json',
		'image'            => 'pawpack-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'pawpack',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pawpack – FAQ: questions, woof?', 'envo-blocks' ),
		'file'     => 'pawpack-11-faq.json',
		'image'    => 'pawpack-11-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'pawpack',
	),
	array(
		'name'     => __( 'Pawpack – Footer', 'envo-blocks' ),
		'file'     => 'pawpack-13-footer.json',
		'image'    => 'pawpack-13-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'pawpack',
	),
	array(
		'name'             => __( 'Driftwood – Header', 'envo-blocks' ),
		'file'             => 'driftwood-01-header.json',
		'image'            => 'driftwood-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'driftwood',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Driftwood – Hero: Driftwood City · 2026', 'envo-blocks' ),
		'file'             => 'driftwood-02-hero.json',
		'image'            => 'driftwood-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'driftwood',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Driftwood – Banner', 'envo-blocks' ),
		'file'     => 'driftwood-03-banner.json',
		'image'    => 'driftwood-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'driftwood',
	),
	array(
		'name'             => __( 'Driftwood – Banner: Range', 'envo-blocks' ),
		'file'             => 'driftwood-04-banner.json',
		'image'            => 'driftwood-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'driftwood',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Driftwood – Banner: Lights', 'envo-blocks' ),
		'file'             => 'driftwood-05-banner.json',
		'image'            => 'driftwood-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'driftwood',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Driftwood – Banner: Anatomy', 'envo-blocks' ),
		'file'     => 'driftwood-06-banner.json',
		'image'    => 'driftwood-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'driftwood',
	),
	array(
		'name'             => __( 'Driftwood – Banner: Build yours', 'envo-blocks' ),
		'file'             => 'driftwood-07-banner.json',
		'image'            => 'driftwood-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'driftwood',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Driftwood – Banner: The maths', 'envo-blocks' ),
		'file'     => 'driftwood-08-banner.json',
		'image'    => 'driftwood-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'driftwood',
	),
	array(
		'name'     => __( 'Driftwood – Banner', 'envo-blocks' ),
		'file'     => 'driftwood-09-banner.json',
		'image'    => 'driftwood-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'driftwood',
	),
	array(
		'name'             => __( 'Driftwood – Reviews: Riders', 'envo-blocks' ),
		'file'             => 'driftwood-10-reviews.json',
		'image'            => 'driftwood-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'driftwood',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Driftwood – Contact: Test ride', 'envo-blocks' ),
		'file'     => 'driftwood-11-contact.json',
		'image'    => 'driftwood-11-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'driftwood',
	),
	array(
		'name'     => __( 'Driftwood – FAQ', 'envo-blocks' ),
		'file'     => 'driftwood-12-faq.json',
		'image'    => 'driftwood-12-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'driftwood',
	),
	array(
		'name'     => __( 'Driftwood – Banner: Driftwood', 'envo-blocks' ),
		'file'     => 'driftwood-13-banner.json',
		'image'    => 'driftwood-13-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'driftwood',
	),
	array(
		'name'             => __( 'Driftwood – Footer', 'envo-blocks' ),
		'file'             => 'driftwood-15-footer.json',
		'image'            => 'driftwood-15-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'driftwood',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nimbus – Header', 'envo-blocks' ),
		'file'             => 'nimbus-01-header.json',
		'image'            => 'nimbus-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'nimbus',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nimbus – Hero: The Nimbus Hybrid', 'envo-blocks' ),
		'file'             => 'nimbus-02-hero.json',
		'image'            => 'nimbus-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'nimbus',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nimbus – Banner: 100 nights to try', 'envo-blocks' ),
		'file'     => 'nimbus-03-banner.json',
		'image'    => 'nimbus-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'nimbus',
	),
	array(
		'name'             => __( 'Nimbus – Banner: Inside the Nimbus', 'envo-blocks' ),
		'file'             => 'nimbus-04-banner.json',
		'image'            => 'nimbus-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'nimbus',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nimbus – About: Find your firmness', 'envo-blocks' ),
		'file'             => 'nimbus-05-about.json',
		'image'            => 'nimbus-05-about.webp',
		'category'         => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'             => 'nimbus',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Nimbus – Banner: Sizes & prices', 'envo-blocks' ),
		'file'             => 'nimbus-06-banner.json',
		'image'            => 'nimbus-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'nimbus',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nimbus – Banner: Compare', 'envo-blocks' ),
		'file'     => 'nimbus-07-banner.json',
		'image'    => 'nimbus-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'nimbus',
	),
	array(
		'name'             => __( 'Nimbus – Banner: Complete the bed', 'envo-blocks' ),
		'file'             => 'nimbus-08-banner.json',
		'image'            => 'nimbus-08-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'nimbus',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nimbus – Banner: The 100-night trial', 'envo-blocks' ),
		'file'     => 'nimbus-09-banner.json',
		'image'    => 'nimbus-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'nimbus',
	),
	array(
		'name'             => __( 'Nimbus – Reviews', 'envo-blocks' ),
		'file'             => 'nimbus-10-reviews.json',
		'image'            => 'nimbus-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'nimbus',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Nimbus – FAQ: Questions, answered softly', 'envo-blocks' ),
		'file'     => 'nimbus-11-faq.json',
		'image'    => 'nimbus-11-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'nimbus',
	),
	array(
		'name'             => __( 'Nimbus – Footer', 'envo-blocks' ),
		'file'             => 'nimbus-12-footer.json',
		'image'            => 'nimbus-12-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'nimbus',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pulse – Header', 'envo-blocks' ),
		'file'             => 'pulse-01-header.json',
		'image'            => 'pulse-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'pulse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pulse – Hero: Pulse One · 2026', 'envo-blocks' ),
		'file'     => 'pulse-02-hero.json',
		'image'    => 'pulse-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'pulse',
	),
	array(
		'name'     => __( 'Pulse – Banner: Health', 'envo-blocks' ),
		'file'     => 'pulse-03-banner.json',
		'image'    => 'pulse-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pulse',
	),
	array(
		'name'             => __( 'Pulse – Banner: 150+ sport modes', 'envo-blocks' ),
		'file'             => 'pulse-04-banner.json',
		'image'            => 'pulse-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'pulse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pulse – Banner: Battery', 'envo-blocks' ),
		'file'     => 'pulse-05-banner.json',
		'image'    => 'pulse-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pulse',
	),
	array(
		'name'             => __( 'Pulse – Banner: Choose yours', 'envo-blocks' ),
		'file'             => 'pulse-06-banner.json',
		'image'            => 'pulse-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'pulse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Pulse – Banner: Straps', 'envo-blocks' ),
		'file'             => 'pulse-07-banner.json',
		'image'            => 'pulse-07-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'pulse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pulse – Banner: Specs · 42 / 46 mm', 'envo-blocks' ),
		'file'     => 'pulse-08-banner.json',
		'image'    => 'pulse-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'pulse',
	),
	array(
		'name'             => __( 'Pulse – Reviews: Reviews · 4.7 / 5', 'envo-blocks' ),
		'file'             => 'pulse-09-reviews.json',
		'image'            => 'pulse-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'pulse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Pulse – FAQ', 'envo-blocks' ),
		'file'     => 'pulse-10-faq.json',
		'image'    => 'pulse-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'pulse',
	),
	array(
		'name'             => __( 'Pulse – Footer', 'envo-blocks' ),
		'file'             => 'pulse-11-footer.json',
		'image'            => 'pulse-11-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'pulse',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Penny – Header', 'envo-blocks' ),
		'file'             => 'penny-01-header.json',
		'image'            => 'penny-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'penny',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Penny – Hero: New · the subscription tracker', 'envo-blocks' ),
		'file'     => 'penny-02-hero.json',
		'image'    => 'penny-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'penny',
	),
	array(
		'name'     => __( 'Penny – Banner: 1.2m', 'envo-blocks' ),
		'file'     => 'penny-03-banner.json',
		'image'    => 'penny-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'penny',
	),
	array(
		'name'             => __( 'Penny – Banner: Features', 'envo-blocks' ),
		'file'             => 'penny-04-banner.json',
		'image'            => 'penny-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'penny',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Penny – Banner: Download Penny', 'envo-blocks' ),
		'file'     => 'penny-05-banner.json',
		'image'    => 'penny-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'penny',
	),
	array(
		'name'             => __( 'Penny – Pricing', 'envo-blocks' ),
		'file'             => 'penny-06-pricing.json',
		'image'            => 'penny-06-pricing.webp',
		'category'         => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'             => 'penny',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Penny – Banner: Compare', 'envo-blocks' ),
		'file'     => 'penny-07-banner.json',
		'image'    => 'penny-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'penny',
	),
	array(
		'name'     => __( 'Penny – Banner: Security', 'envo-blocks' ),
		'file'     => 'penny-08-banner.json',
		'image'    => 'penny-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'penny',
	),
	array(
		'name'             => __( 'Penny – Reviews: 4.8 · 92,000 ratings', 'envo-blocks' ),
		'file'             => 'penny-09-reviews.json',
		'image'            => 'penny-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'penny',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Penny – FAQ', 'envo-blocks' ),
		'file'     => 'penny-10-faq.json',
		'image'    => 'penny-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'penny',
	),
	array(
		'name'     => __( 'Penny – Banner: Start free today', 'envo-blocks' ),
		'file'     => 'penny-11-banner.json',
		'image'    => 'penny-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'penny',
	),
	array(
		'name'             => __( 'Penny – Footer', 'envo-blocks' ),
		'file'             => 'penny-13-footer.json',
		'image'            => 'penny-13-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'penny',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Inkwell – Header', 'envo-blocks' ),
		'file'             => 'inkwell-01-header.json',
		'image'            => 'inkwell-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'inkwell',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Inkwell – Hero: Book of the season · Autumn 2026', 'envo-blocks' ),
		'file'     => 'inkwell-02-hero.json',
		'image'    => 'inkwell-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'     => __( 'Inkwell – Banner', 'envo-blocks' ),
		'file'     => 'inkwell-03-banner.json',
		'image'    => 'inkwell-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'     => __( 'Inkwell – Banner: Chapter I — The first page', 'envo-blocks' ),
		'file'     => 'inkwell-04-banner.json',
		'image'    => 'inkwell-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'     => __( 'Inkwell – Banner: Chapter II — The shelves', 'envo-blocks' ),
		'file'     => 'inkwell-05-banner.json',
		'image'    => 'inkwell-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'             => __( 'Inkwell – Products: Chapter III — The autumn list', 'envo-blocks' ),
		'file'             => 'inkwell-06-products.json',
		'image'            => 'inkwell-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'inkwell',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Inkwell – Products: Chapter IV — What we are reading', 'envo-blocks' ),
		'file'             => 'inkwell-07-products.json',
		'image'            => 'inkwell-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'inkwell',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Inkwell – FAQ: Meet the author', 'envo-blocks' ),
		'file'     => 'inkwell-08-faq.json',
		'image'    => 'inkwell-08-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'     => __( 'Inkwell – Banner: Chapter V — The book club', 'envo-blocks' ),
		'file'     => 'inkwell-09-banner.json',
		'image'    => 'inkwell-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'     => __( 'Inkwell – Banner: Chapter VI — Upcoming events', 'envo-blocks' ),
		'file'     => 'inkwell-10-banner.json',
		'image'    => 'inkwell-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'             => __( 'Inkwell – Reviews: Chapter VII — Readers write back', 'envo-blocks' ),
		'file'             => 'inkwell-11-reviews.json',
		'image'            => 'inkwell-11-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'inkwell',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Inkwell – Banner: Letters from the shop', 'envo-blocks' ),
		'file'             => 'inkwell-12-banner.json',
		'image'            => 'inkwell-12-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'inkwell',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Inkwell – Contact: Chapter VIII — Find us', 'envo-blocks' ),
		'file'     => 'inkwell-13-contact.json',
		'image'    => 'inkwell-13-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'     => __( 'Inkwell – Footer', 'envo-blocks' ),
		'file'     => 'inkwell-14-footer.json',
		'image'    => 'inkwell-14-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'inkwell',
	),
	array(
		'name'     => __( 'Rankwise – Header', 'envo-blocks' ),
		'file'     => 'rankwise-01-header.json',
		'image'    => 'rankwise-01-header.webp',
		'category' => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'     => __( 'Rankwise – Hero: SEO for online shops', 'envo-blocks' ),
		'file'     => 'rankwise-02-hero.json',
		'image'    => 'rankwise-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'     => __( 'Rankwise – Banner: 212 %', 'envo-blocks' ),
		'file'     => 'rankwise-03-banner.json',
		'image'    => 'rankwise-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'             => __( 'Rankwise – Banner: Results', 'envo-blocks' ),
		'file'             => 'rankwise-04-banner.json',
		'image'            => 'rankwise-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'rankwise',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Rankwise – Banner: Week 1 – 2: the audit', 'envo-blocks' ),
		'file'     => 'rankwise-05-banner.json',
		'image'    => 'rankwise-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'     => __( 'Rankwise – Banner: Roadmap', 'envo-blocks' ),
		'file'     => 'rankwise-06-banner.json',
		'image'    => 'rankwise-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'     => __( 'Rankwise – Banner: Every month', 'envo-blocks' ),
		'file'     => 'rankwise-07-banner.json',
		'image'    => 'rankwise-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'             => __( 'Rankwise – Banner: Pricing', 'envo-blocks' ),
		'file'             => 'rankwise-08-banner.json',
		'image'            => 'rankwise-08-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'rankwise',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Rankwise – About: Your strategist', 'envo-blocks' ),
		'file'     => 'rankwise-09-about.json',
		'image'    => 'rankwise-09-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'             => __( 'Rankwise – Banner: Clients', 'envo-blocks' ),
		'file'             => 'rankwise-10-banner.json',
		'image'            => 'rankwise-10-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'rankwise',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Rankwise – FAQ', 'envo-blocks' ),
		'file'     => 'rankwise-11-faq.json',
		'image'    => 'rankwise-11-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'     => __( 'Rankwise – Banner: Free mini audit', 'envo-blocks' ),
		'file'     => 'rankwise-12-banner.json',
		'image'    => 'rankwise-12-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'rankwise',
	),
	array(
		'name'             => __( 'Rankwise – Footer', 'envo-blocks' ),
		'file'             => 'rankwise-13-footer.json',
		'image'            => 'rankwise-13-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'rankwise',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Hatch – Header', 'envo-blocks' ),
		'file'     => 'hatch-01-header.json',
		'image'    => 'hatch-01-header.webp',
		'category' => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'     => __( 'Hatch – Hero: Web design for WooCommerce', 'envo-blocks' ),
		'file'     => 'hatch-02-hero.json',
		'image'    => 'hatch-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'     => __( 'Hatch – Banner', 'envo-blocks' ),
		'file'     => 'hatch-03-banner.json',
		'image'    => 'hatch-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'     => __( 'Hatch – Banner: Selected work', 'envo-blocks' ),
		'file'     => 'hatch-04-banner.json',
		'image'    => 'hatch-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'             => __( 'Hatch – Banner: Before / after', 'envo-blocks' ),
		'file'             => 'hatch-05-banner.json',
		'image'            => 'hatch-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'hatch',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Hatch – Banner: How we work', 'envo-blocks' ),
		'file'     => 'hatch-06-banner.json',
		'image'    => 'hatch-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'     => __( 'Hatch – Banner: In every package', 'envo-blocks' ),
		'file'     => 'hatch-07-banner.json',
		'image'    => 'hatch-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'             => __( 'Hatch – Banner: Packages', 'envo-blocks' ),
		'file'             => 'hatch-08-banner.json',
		'image'            => 'hatch-08-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'hatch',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Hatch – Banner: The studio', 'envo-blocks' ),
		'file'     => 'hatch-09-banner.json',
		'image'    => 'hatch-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'             => __( 'Hatch – Reviews: Kind words', 'envo-blocks' ),
		'file'             => 'hatch-10-reviews.json',
		'image'            => 'hatch-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'hatch',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Hatch – FAQ', 'envo-blocks' ),
		'file'     => 'hatch-11-faq.json',
		'image'    => 'hatch-11-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'     => __( 'Hatch – About: Let’s hatch your shop .', 'envo-blocks' ),
		'file'     => 'hatch-12-about.json',
		'image'    => 'hatch-12-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'hatch',
	),
	array(
		'name'             => __( 'Hatch – Footer', 'envo-blocks' ),
		'file'             => 'hatch-13-footer.json',
		'image'            => 'hatch-13-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'hatch',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Palette – Header', 'envo-blocks' ),
		'file'     => 'palette-01-header.json',
		'image'    => 'palette-01-header.webp',
		'category' => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'     => 'palette',
	),
	array(
		'name'     => __( 'Palette – Hero: 36 video lessons', 'envo-blocks' ),
		'file'     => 'palette-02-hero.json',
		'image'    => 'palette-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'palette',
	),
	array(
		'name'     => __( 'Palette – Banner: What you will paint', 'envo-blocks' ),
		'file'     => 'palette-03-banner.json',
		'image'    => 'palette-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'palette',
	),
	array(
		'name'     => __( 'Palette – FAQ: Syllabus', 'envo-blocks' ),
		'file'     => 'palette-04-faq.json',
		'image'    => 'palette-04-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'palette',
	),
	array(
		'name'     => __( 'Palette – About: Your teacher', 'envo-blocks' ),
		'file'     => 'palette-05-about.json',
		'image'    => 'palette-05-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'palette',
	),
	array(
		'name'     => __( 'Palette – Banner: Pricing', 'envo-blocks' ),
		'file'     => 'palette-06-banner.json',
		'image'    => 'palette-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'palette',
	),
	array(
		'name'             => __( 'Palette – Reviews: Students', 'envo-blocks' ),
		'file'             => 'palette-07-reviews.json',
		'image'            => 'palette-07-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'palette',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Palette – FAQ', 'envo-blocks' ),
		'file'     => 'palette-08-faq.json',
		'image'    => 'palette-08-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'palette',
	),
	array(
		'name'             => __( 'Palette – Banner: Free lesson', 'envo-blocks' ),
		'file'             => 'palette-09-banner.json',
		'image'            => 'palette-09-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'palette',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Palette – Footer', 'envo-blocks' ),
		'file'             => 'palette-10-footer.json',
		'image'            => 'palette-10-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'palette',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Northlight – Header', 'envo-blocks' ),
		'file'     => 'northlight-01-header.json',
		'image'    => 'northlight-01-header.webp',
		'category' => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'     => 'northlight',
	),
	array(
		'name'             => __( 'Northlight – Hero: Leeds · the Dales · on location', 'envo-blocks' ),
		'file'             => 'northlight-02-hero.json',
		'image'            => 'northlight-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'northlight',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Northlight – Banner: 640+', 'envo-blocks' ),
		'file'     => 'northlight-03-banner.json',
		'image'    => 'northlight-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northlight',
	),
	array(
		'name'     => __( 'Northlight – Banner: Portfolio', 'envo-blocks' ),
		'file'     => 'northlight-04-banner.json',
		'image'    => 'northlight-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northlight',
	),
	array(
		'name'             => __( 'Northlight – Banner: Sessions', 'envo-blocks' ),
		'file'             => 'northlight-05-banner.json',
		'image'            => 'northlight-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'northlight',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Northlight – Contact: Contact sheet', 'envo-blocks' ),
		'file'     => 'northlight-06-contact.json',
		'image'    => 'northlight-06-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'northlight',
	),
	array(
		'name'     => __( 'Northlight – Banner: How it works', 'envo-blocks' ),
		'file'     => 'northlight-07-banner.json',
		'image'    => 'northlight-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northlight',
	),
	array(
		'name'     => __( 'Northlight – Banner: Behind the camera', 'envo-blocks' ),
		'file'     => 'northlight-08-banner.json',
		'image'    => 'northlight-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northlight',
	),
	array(
		'name'             => __( 'Northlight – Reviews: Kind words', 'envo-blocks' ),
		'file'             => 'northlight-09-reviews.json',
		'image'            => 'northlight-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'northlight',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Northlight – Banner: Gift a session', 'envo-blocks' ),
		'file'     => 'northlight-10-banner.json',
		'image'    => 'northlight-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'northlight',
	),
	array(
		'name'     => __( 'Northlight – FAQ: Questions', 'envo-blocks' ),
		'file'     => 'northlight-11-faq.json',
		'image'    => 'northlight-11-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'northlight',
	),
	array(
		'name'             => __( 'Northlight – Footer', 'envo-blocks' ),
		'file'             => 'northlight-12-footer.json',
		'image'            => 'northlight-12-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'northlight',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Fade – Header', 'envo-blocks' ),
		'file'     => 'fade-01-header.json',
		'image'    => 'fade-01-header.webp',
		'category' => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'     => 'fade',
	),
	array(
		'name'     => __( 'Fade – Hero: Est. 1998 · Tuesday – Saturday', 'envo-blocks' ),
		'file'     => 'fade-02-hero.json',
		'image'    => 'fade-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'fade',
	),
	array(
		'name'     => __( 'Fade – Banner: Services', 'envo-blocks' ),
		'file'     => 'fade-04-banner.json',
		'image'    => 'fade-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'fade',
	),
	array(
		'name'     => __( 'Fade – Banner: The barbers', 'envo-blocks' ),
		'file'     => 'fade-05-banner.json',
		'image'    => 'fade-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'fade',
	),
	array(
		'name'             => __( 'Fade – Products: Grooming shelf', 'envo-blocks' ),
		'file'             => 'fade-06-products.json',
		'image'            => 'fade-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'fade',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Fade – Contact: Opening hours', 'envo-blocks' ),
		'file'     => 'fade-07-contact.json',
		'image'    => 'fade-07-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'fade',
	),
	array(
		'name'             => __( 'Fade – Reviews: Word on the street', 'envo-blocks' ),
		'file'             => 'fade-08-reviews.json',
		'image'            => 'fade-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'fade',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Fade – Footer', 'envo-blocks' ),
		'file'     => 'fade-09-footer.json',
		'image'    => 'fade-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'fade',
	),
	array(
		'name'             => __( 'Bloomery – Header', 'envo-blocks' ),
		'file'             => 'bloomery-01-header.json',
		'image'            => 'bloomery-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'bloomery',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Bloomery – Hero: Hand-tied every morning · London', 'envo-blocks' ),
		'file'     => 'bloomery-02-hero.json',
		'image'    => 'bloomery-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'bloomery',
	),
	array(
		'name'     => __( 'Bloomery – Banner: What’s the occasion?', 'envo-blocks' ),
		'file'     => 'bloomery-03-banner.json',
		'image'    => 'bloomery-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'bloomery',
	),
	array(
		'name'             => __( 'Bloomery – Products: Bouquets', 'envo-blocks' ),
		'file'             => 'bloomery-04-products.json',
		'image'            => 'bloomery-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'bloomery',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Bloomery – Banner: Size guide', 'envo-blocks' ),
		'file'     => 'bloomery-05-banner.json',
		'image'    => 'bloomery-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'bloomery',
	),
	array(
		'name'     => __( 'Bloomery – Banner: Flower language', 'envo-blocks' ),
		'file'     => 'bloomery-06-banner.json',
		'image'    => 'bloomery-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'bloomery',
	),
	array(
		'name'     => __( 'Bloomery – Banner: Flower subscription', 'envo-blocks' ),
		'file'     => 'bloomery-07-banner.json',
		'image'    => 'bloomery-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'bloomery',
	),
	array(
		'name'     => __( 'Bloomery – Banner: Weddings', 'envo-blocks' ),
		'file'     => 'bloomery-08-banner.json',
		'image'    => 'bloomery-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'bloomery',
	),
	array(
		'name'     => __( 'Bloomery – Banner: Visit the shop', 'envo-blocks' ),
		'file'     => 'bloomery-09-banner.json',
		'image'    => 'bloomery-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'bloomery',
	),
	array(
		'name'             => __( 'Bloomery – Reviews: Kind words', 'envo-blocks' ),
		'file'             => 'bloomery-10-reviews.json',
		'image'            => 'bloomery-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'bloomery',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Bloomery – Footer', 'envo-blocks' ),
		'file'     => 'bloomery-11-footer.json',
		'image'    => 'bloomery-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'bloomery',
	),
	array(
		'name'             => __( 'Kiln – Header', 'envo-blocks' ),
		'file'             => 'kiln-01-header.json',
		'image'            => 'kiln-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'kiln',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kiln – Hero: Small-batch stoneware · Bristol', 'envo-blocks' ),
		'file'     => 'kiln-02-hero.json',
		'image'    => 'kiln-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'kiln',
	),
	array(
		'name'             => __( 'Kiln – Products: This firing · 6 designs', 'envo-blocks' ),
		'file'             => 'kiln-03-products.json',
		'image'            => 'kiln-03-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kiln',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kiln – Banner: From clay to cup', 'envo-blocks' ),
		'file'     => 'kiln-04-banner.json',
		'image'    => 'kiln-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kiln',
	),
	array(
		'name'     => __( 'Kiln – About: Our glazes', 'envo-blocks' ),
		'file'     => 'kiln-05-about.json',
		'image'    => 'kiln-05-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'kiln',
	),
	array(
		'name'     => __( 'Kiln – Banner: Workshops', 'envo-blocks' ),
		'file'     => 'kiln-06-banner.json',
		'image'    => 'kiln-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kiln',
	),
	array(
		'name'     => __( 'Kiln – Contact: The studio', 'envo-blocks' ),
		'file'     => 'kiln-07-contact.json',
		'image'    => 'kiln-07-contact.webp',
		'category' => array(
			__( 'Contact', 'envo-blocks' ),
		),
		'demo'     => 'kiln',
	),
	array(
		'name'             => __( 'Kiln – Reviews: From your shelves', 'envo-blocks' ),
		'file'             => 'kiln-08-reviews.json',
		'image'            => 'kiln-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'kiln',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kiln – Footer', 'envo-blocks' ),
		'file'     => 'kiln-09-footer.json',
		'image'    => 'kiln-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'kiln',
	),
	array(
		'name'             => __( 'Plaza – Header', 'envo-blocks' ),
		'file'             => 'plaza-01-header.json',
		'image'            => 'plaza-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'plaza',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Plaza – Hero: Autumn at Plaza', 'envo-blocks' ),
		'file'     => 'plaza-02-hero.json',
		'image'    => 'plaza-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'plaza',
	),
	array(
		'name'     => __( 'Plaza – Banner: Shop by department', 'envo-blocks' ),
		'file'     => 'plaza-03-banner.json',
		'image'    => 'plaza-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'plaza',
	),
	array(
		'name'             => __( 'Plaza – Products: Today’s deals 🔥', 'envo-blocks' ),
		'file'             => 'plaza-04-products.json',
		'image'            => 'plaza-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'plaza',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Plaza – Products: Trending now', 'envo-blocks' ),
		'file'             => 'plaza-05-products.json',
		'image'            => 'plaza-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'plaza',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Plaza – Banner: Gifts by budget', 'envo-blocks' ),
		'file'     => 'plaza-06-banner.json',
		'image'    => 'plaza-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'plaza',
	),
	array(
		'name'             => __( 'Plaza – Products: Bestsellers this week', 'envo-blocks' ),
		'file'             => 'plaza-07-products.json',
		'image'            => 'plaza-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'plaza',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Plaza – Banner: Next-day delivery', 'envo-blocks' ),
		'file'     => 'plaza-08-banner.json',
		'image'    => 'plaza-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'plaza',
	),
	array(
		'name'     => __( 'Plaza – Footer', 'envo-blocks' ),
		'file'     => 'plaza-09-footer.json',
		'image'    => 'plaza-09-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'plaza',
	),
	array(
		'name'             => __( 'Kindred – Header', 'envo-blocks' ),
		'file'             => 'kindred-01-header.json',
		'image'            => 'kindred-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'kindred',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kindred – Hero: things we’d actually keep.', 'envo-blocks' ),
		'file'     => 'kindred-02-hero.json',
		'image'    => 'kindred-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'kindred',
	),
	array(
		'name'     => __( 'Kindred – Banner: the edits', 'envo-blocks' ),
		'file'     => 'kindred-03-banner.json',
		'image'    => 'kindred-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kindred',
	),
	array(
		'name'             => __( 'Kindred – Products: edit 01', 'envo-blocks' ),
		'file'             => 'kindred-04-products.json',
		'image'            => 'kindred-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kindred',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kindred – Products: edit 02', 'envo-blocks' ),
		'file'             => 'kindred-05-products.json',
		'image'            => 'kindred-05-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kindred',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kindred – Products: edit 03', 'envo-blocks' ),
		'file'             => 'kindred-06-products.json',
		'image'            => 'kindred-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kindred',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kindred – Products: edit 04', 'envo-blocks' ),
		'file'             => 'kindred-07-products.json',
		'image'            => 'kindred-07-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kindred',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kindred – Products: gift finder', 'envo-blocks' ),
		'file'             => 'kindred-08-products.json',
		'image'            => 'kindred-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kindred',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Kindred – Products: new in.', 'envo-blocks' ),
		'file'             => 'kindred-09-products.json',
		'image'            => 'kindred-09-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'kindred',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kindred – Banner: chosen, not stocked', 'envo-blocks' ),
		'file'     => 'kindred-10-banner.json',
		'image'    => 'kindred-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kindred',
	),
	array(
		'name'     => __( 'Kindred – Footer', 'envo-blocks' ),
		'file'     => 'kindred-11-footer.json',
		'image'    => 'kindred-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'kindred',
	),
	array(
		'name'             => __( 'Relay – Header', 'envo-blocks' ),
		'file'             => 'relay-01-header.json',
		'image'            => 'relay-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'relay',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Relay – Hero: New: AI drafts in every reply', 'envo-blocks' ),
		'file'     => 'relay-02-hero.json',
		'image'    => 'relay-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'relay',
	),
	array(
		'name'     => __( 'Relay – Banner: Northwind', 'envo-blocks' ),
		'file'     => 'relay-03-banner.json',
		'image'    => 'relay-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'relay',
	),
	array(
		'name'             => __( 'Relay – Banner: Product tour', 'envo-blocks' ),
		'file'             => 'relay-04-banner.json',
		'image'            => 'relay-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'relay',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Relay – Banner: Channels', 'envo-blocks' ),
		'file'     => 'relay-05-banner.json',
		'image'    => 'relay-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'relay',
	),
	array(
		'name'             => __( 'Relay – Pricing', 'envo-blocks' ),
		'file'             => 'relay-06-pricing.json',
		'image'            => 'relay-06-pricing.webp',
		'category'         => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'             => 'relay',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Relay – Banner: Add-ons', 'envo-blocks' ),
		'file'     => 'relay-07-banner.json',
		'image'    => 'relay-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'relay',
	),
	array(
		'name'     => __( 'Relay – Banner: Customer stories', 'envo-blocks' ),
		'file'     => 'relay-08-banner.json',
		'image'    => 'relay-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'relay',
	),
	array(
		'name'     => __( 'Relay – FAQ', 'envo-blocks' ),
		'file'     => 'relay-09-faq.json',
		'image'    => 'relay-09-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'relay',
	),
	array(
		'name'     => __( 'Relay – Banner', 'envo-blocks' ),
		'file'     => 'relay-10-banner.json',
		'image'    => 'relay-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'relay',
	),
	array(
		'name'     => __( 'Relay – Footer', 'envo-blocks' ),
		'file'     => 'relay-11-footer.json',
		'image'    => 'relay-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'relay',
	),
	array(
		'name'             => __( 'Kestrel – Header', 'envo-blocks' ),
		'file'             => 'kestrel-01-header.json',
		'image'            => 'kestrel-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'kestrel',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kestrel – Hero: managed web hosting', 'envo-blocks' ),
		'file'     => 'kestrel-02-hero.json',
		'image'    => 'kestrel-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'     => __( 'Kestrel – Banner: 84 ms', 'envo-blocks' ),
		'file'     => 'kestrel-03-banner.json',
		'image'    => 'kestrel-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'             => __( 'Kestrel – Pricing: plans', 'envo-blocks' ),
		'file'             => 'kestrel-04-pricing.json',
		'image'            => 'kestrel-04-pricing.webp',
		'category'         => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'             => 'kestrel',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kestrel – Banner: speed', 'envo-blocks' ),
		'file'     => 'kestrel-05-banner.json',
		'image'    => 'kestrel-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'     => __( 'Kestrel – Banner: everything included', 'envo-blocks' ),
		'file'     => 'kestrel-06-banner.json',
		'image'    => 'kestrel-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'     => __( 'Kestrel – Banner: data centres', 'envo-blocks' ),
		'file'     => 'kestrel-07-banner.json',
		'image'    => 'kestrel-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'     => __( 'Kestrel – Banner: free migration', 'envo-blocks' ),
		'file'     => 'kestrel-08-banner.json',
		'image'    => 'kestrel-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'     => __( 'Kestrel – Banner: support', 'envo-blocks' ),
		'file'     => 'kestrel-09-banner.json',
		'image'    => 'kestrel-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'             => __( 'Kestrel – Reviews', 'envo-blocks' ),
		'file'             => 'kestrel-10-reviews.json',
		'image'            => 'kestrel-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'kestrel',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Kestrel – Banner: extras', 'envo-blocks' ),
		'file'     => 'kestrel-11-banner.json',
		'image'    => 'kestrel-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'     => __( 'Kestrel – FAQ', 'envo-blocks' ),
		'file'     => 'kestrel-12-faq.json',
		'image'    => 'kestrel-12-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'     => __( 'Kestrel – Banner', 'envo-blocks' ),
		'file'     => 'kestrel-13-banner.json',
		'image'    => 'kestrel-13-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'     => __( 'Kestrel – Footer', 'envo-blocks' ),
		'file'     => 'kestrel-14-footer.json',
		'image'    => 'kestrel-14-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'kestrel',
	),
	array(
		'name'             => __( 'Redpen – Header', 'envo-blocks' ),
		'file'             => 'redpen-01-header.json',
		'image'            => 'redpen-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'redpen',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Redpen – Hero: Hi, I’m Clara 👋', 'envo-blocks' ),
		'file'     => 'redpen-02-hero.json',
		'image'    => 'redpen-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'     => __( 'Redpen – Banner: Before & after', 'envo-blocks' ),
		'file'     => 'redpen-03-banner.json',
		'image'    => 'redpen-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'     => __( 'Redpen – Banner: Services', 'envo-blocks' ),
		'file'     => 'redpen-04-banner.json',
		'image'    => 'redpen-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'             => __( 'Redpen – Banner: Samples', 'envo-blocks' ),
		'file'             => 'redpen-05-banner.json',
		'image'            => 'redpen-05-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'redpen',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Redpen – Pricing', 'envo-blocks' ),
		'file'     => 'redpen-06-pricing.json',
		'image'    => 'redpen-06-pricing.webp',
		'category' => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'     => __( 'Redpen – Banner: How it works', 'envo-blocks' ),
		'file'     => 'redpen-07-banner.json',
		'image'    => 'redpen-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'             => __( 'Redpen – Reviews: Kind words', 'envo-blocks' ),
		'file'             => 'redpen-08-reviews.json',
		'image'            => 'redpen-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'redpen',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Redpen – Banner: About', 'envo-blocks' ),
		'file'     => 'redpen-09-banner.json',
		'image'    => 'redpen-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'     => __( 'Redpen – FAQ', 'envo-blocks' ),
		'file'     => 'redpen-10-faq.json',
		'image'    => 'redpen-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'     => __( 'Redpen – Banner: Staring at a blank page?', 'envo-blocks' ),
		'file'     => 'redpen-11-banner.json',
		'image'    => 'redpen-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'     => __( 'Redpen – Footer', 'envo-blocks' ),
		'file'     => 'redpen-12-footer.json',
		'image'    => 'redpen-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'redpen',
	),
	array(
		'name'             => __( 'Keyline – Header', 'envo-blocks' ),
		'file'             => 'keyline-01-header.json',
		'image'            => 'keyline-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'keyline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Keyline – Hero: 4.9 from 60+ clients', 'envo-blocks' ),
		'file'     => 'keyline-02-hero.json',
		'image'    => 'keyline-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'     => __( 'Keyline – Banner: Sound familiar?', 'envo-blocks' ),
		'file'     => 'keyline-03-banner.json',
		'image'    => 'keyline-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'             => __( 'Keyline – Pricing', 'envo-blocks' ),
		'file'             => 'keyline-04-pricing.json',
		'image'            => 'keyline-04-pricing.webp',
		'category'         => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'             => 'keyline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Keyline – Banner: Case study · Little Fern Bakery', 'envo-blocks' ),
		'file'     => 'keyline-05-banner.json',
		'image'    => 'keyline-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'     => __( 'Keyline – Banner: The first 90 days', 'envo-blocks' ),
		'file'     => 'keyline-06-banner.json',
		'image'    => 'keyline-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'     => __( 'Keyline – Banner: About', 'envo-blocks' ),
		'file'     => 'keyline-07-banner.json',
		'image'    => 'keyline-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'             => __( 'Keyline – Reviews', 'envo-blocks' ),
		'file'             => 'keyline-08-reviews.json',
		'image'            => 'keyline-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'keyline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Keyline – Banner: Not ready for an audit?', 'envo-blocks' ),
		'file'     => 'keyline-09-banner.json',
		'image'    => 'keyline-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'     => __( 'Keyline – FAQ', 'envo-blocks' ),
		'file'     => 'keyline-10-faq.json',
		'image'    => 'keyline-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'     => __( 'Keyline – Banner', 'envo-blocks' ),
		'file'     => 'keyline-11-banner.json',
		'image'    => 'keyline-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'     => __( 'Keyline – Footer', 'envo-blocks' ),
		'file'     => 'keyline-12-footer.json',
		'image'    => 'keyline-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'keyline',
	),
	array(
		'name'             => __( 'Foundry – Header', 'envo-blocks' ),
		'file'             => 'foundry-01-header.json',
		'image'            => 'foundry-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'foundry',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Foundry – Hero', 'envo-blocks' ),
		'file'     => 'foundry-02-hero.json',
		'image'    => 'foundry-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'     => __( 'Foundry – Banner: Web design', 'envo-blocks' ),
		'file'     => 'foundry-03-banner.json',
		'image'    => 'foundry-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'     => __( 'Foundry – Banner: Selected work ]', 'envo-blocks' ),
		'file'     => 'foundry-04-banner.json',
		'image'    => 'foundry-04-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'     => __( 'Foundry – Pricing: Packages ]', 'envo-blocks' ),
		'file'     => 'foundry-05-pricing.json',
		'image'    => 'foundry-05-pricing.webp',
		'category' => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'             => __( 'Foundry – Banner: Care plan ]', 'envo-blocks' ),
		'file'             => 'foundry-06-banner.json',
		'image'            => 'foundry-06-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'foundry',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Foundry – Banner: Process ]', 'envo-blocks' ),
		'file'     => 'foundry-07-banner.json',
		'image'    => 'foundry-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'             => __( 'Foundry – Reviews: Clients ]', 'envo-blocks' ),
		'file'             => 'foundry-08-reviews.json',
		'image'            => 'foundry-08-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'foundry',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Foundry – Banner: About ]', 'envo-blocks' ),
		'file'     => 'foundry-09-banner.json',
		'image'    => 'foundry-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'     => __( 'Foundry – FAQ: Faq ]', 'envo-blocks' ),
		'file'     => 'foundry-10-faq.json',
		'image'    => 'foundry-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'     => __( 'Foundry – Banner: Got a project? Let’s build it.', 'envo-blocks' ),
		'file'     => 'foundry-11-banner.json',
		'image'    => 'foundry-11-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'     => __( 'Foundry – Footer', 'envo-blocks' ),
		'file'     => 'foundry-12-footer.json',
		'image'    => 'foundry-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'foundry',
	),
	array(
		'name'             => __( 'Sienna – Header', 'envo-blocks' ),
		'file'             => 'sienna-01-header.json',
		'image'            => 'sienna-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'sienna',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sienna – Hero: Autumn edit 2026', 'envo-blocks' ),
		'file'             => 'sienna-02-hero.json',
		'image'            => 'sienna-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'sienna',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Sienna – Banner: Shop by category', 'envo-blocks' ),
		'file'     => 'sienna-03-banner.json',
		'image'    => 'sienna-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'sienna',
	),
	array(
		'name'             => __( 'Sienna – Products: Trending now', 'envo-blocks' ),
		'file'             => 'sienna-04-products.json',
		'image'            => 'sienna-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sienna',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Sienna – Banner: Evening edit', 'envo-blocks' ),
		'file'     => 'sienna-05-banner.json',
		'image'    => 'sienna-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'sienna',
	),
	array(
		'name'             => __( 'Sienna – Products: Shop the look', 'envo-blocks' ),
		'file'             => 'sienna-06-products.json',
		'image'            => 'sienna-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sienna',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sienna – Deals: Hot deals of the week', 'envo-blocks' ),
		'file'             => 'sienna-07-deals.json',
		'image'            => 'sienna-07-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'sienna',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sienna – Products: On sale now', 'envo-blocks' ),
		'file'             => 'sienna-08-products.json',
		'image'            => 'sienna-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'sienna',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Sienna – Banner: The leather jacket', 'envo-blocks' ),
		'file'             => 'sienna-09-banner.json',
		'image'            => 'sienna-09-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'sienna',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Sienna – Banner: The lookbook', 'envo-blocks' ),
		'file'     => 'sienna-10-banner.json',
		'image'    => 'sienna-10-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'sienna',
	),
	array(
		'name'     => __( 'Sienna – Features', 'envo-blocks' ),
		'file'     => 'sienna-11-features.json',
		'image'    => 'sienna-11-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'sienna',
	),
	array(
		'name'     => __( 'Sienna – Footer', 'envo-blocks' ),
		'file'     => 'sienna-12-footer.json',
		'image'    => 'sienna-12-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'sienna',
	),
	array(
		'name'             => __( 'Celeste – Header', 'envo-blocks' ),
		'file'             => 'celeste-01-header.json',
		'image'            => 'celeste-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'celeste',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Celeste – Hero: Galaxy Tab S8+', 'envo-blocks' ),
		'file'             => 'celeste-02-hero.json',
		'image'            => 'celeste-02-hero.webp',
		'category'         => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'             => 'celeste',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Celeste – Deals: Deal of the week', 'envo-blocks' ),
		'file'             => 'celeste-03-deals.json',
		'image'            => 'celeste-03-deals.webp',
		'category'         => array(
			__( 'Deals', 'envo-blocks' ),
		),
		'demo'             => 'celeste',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Celeste – Products: Best sellers', 'envo-blocks' ),
		'file'             => 'celeste-04-products.json',
		'image'            => 'celeste-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'celeste',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Celeste – Banner: Get the weekly deals', 'envo-blocks' ),
		'file'     => 'celeste-05-banner.json',
		'image'    => 'celeste-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'celeste',
	),
	array(
		'name'     => __( 'Celeste – Footer', 'envo-blocks' ),
		'file'     => 'celeste-06-footer.json',
		'image'    => 'celeste-06-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'celeste',
	),
	array(
		'name'             => __( 'Cutline – Header', 'envo-blocks' ),
		'file'             => 'cutline-01-header.json',
		'image'            => 'cutline-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'cutline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Cutline – Hero', 'envo-blocks' ),
		'file'     => 'cutline-02-hero.json',
		'image'    => 'cutline-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'cutline',
	),
	array(
		'name'     => __( 'Cutline – Banner: 400k+', 'envo-blocks' ),
		'file'     => 'cutline-03-banner.json',
		'image'    => 'cutline-03-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'cutline',
	),
	array(
		'name'             => __( 'Cutline – Banner: Features', 'envo-blocks' ),
		'file'             => 'cutline-04-banner.json',
		'image'            => 'cutline-04-banner.webp',
		'category'         => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'             => 'cutline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Cutline – Banner: Workflow', 'envo-blocks' ),
		'file'     => 'cutline-05-banner.json',
		'image'    => 'cutline-05-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'cutline',
	),
	array(
		'name'     => __( 'Cutline – Banner: Made for creators', 'envo-blocks' ),
		'file'     => 'cutline-06-banner.json',
		'image'    => 'cutline-06-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'cutline',
	),
	array(
		'name'             => __( 'Cutline – Pricing', 'envo-blocks' ),
		'file'             => 'cutline-07-pricing.json',
		'image'            => 'cutline-07-pricing.webp',
		'category'         => array(
			__( 'Pricing', 'envo-blocks' ),
		),
		'demo'             => 'cutline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Cutline – Banner: Requirements', 'envo-blocks' ),
		'file'     => 'cutline-08-banner.json',
		'image'    => 'cutline-08-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'cutline',
	),
	array(
		'name'             => __( 'Cutline – Reviews: 4.8 from 11,000 reviews', 'envo-blocks' ),
		'file'             => 'cutline-09-reviews.json',
		'image'            => 'cutline-09-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'cutline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Cutline – FAQ', 'envo-blocks' ),
		'file'     => 'cutline-10-faq.json',
		'image'    => 'cutline-10-faq.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'demo'     => 'cutline',
	),
	array(
		'name'     => __( 'Cutline – About: Your next video starts here.', 'envo-blocks' ),
		'file'     => 'cutline-11-about.json',
		'image'    => 'cutline-11-about.webp',
		'category' => array(
			__( 'About', 'envo-blocks' ),
		),
		'demo'     => 'cutline',
	),
	array(
		'name'             => __( 'Cutline – Footer', 'envo-blocks' ),
		'file'             => 'cutline-12-footer.json',
		'image'            => 'cutline-12-footer.webp',
		'category'         => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'             => 'cutline',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Blaze – Header', 'envo-blocks' ),
		'file'             => 'blaze-01-header.json',
		'image'            => 'blaze-01-header.webp',
		'category'         => array(
			__( 'Header', 'envo-blocks' ),
		),
		'demo'             => 'blaze',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Blaze – Hero: New season · 2026', 'envo-blocks' ),
		'file'     => 'blaze-02-hero.json',
		'image'    => 'blaze-02-hero.webp',
		'category' => array(
			__( 'Hero', 'envo-blocks' ),
		),
		'demo'     => 'blaze',
	),
	array(
		'name'     => __( 'Blaze – Features', 'envo-blocks' ),
		'file'     => 'blaze-03-features.json',
		'image'    => 'blaze-03-features.webp',
		'category' => array(
			__( 'Features', 'envo-blocks' ),
		),
		'demo'     => 'blaze',
	),
	array(
		'name'             => __( 'Blaze – Products: Top picks', 'envo-blocks' ),
		'file'             => 'blaze-04-products.json',
		'image'            => 'blaze-04-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'blaze',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Blaze – Categories: Shop by sport', 'envo-blocks' ),
		'file'             => 'blaze-05-categories.json',
		'image'            => 'blaze-05-categories.webp',
		'category'         => array(
			__( 'Categories', 'envo-blocks' ),
		),
		'demo'             => 'blaze',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Blaze – Products: Gear up', 'envo-blocks' ),
		'file'             => 'blaze-06-products.json',
		'image'            => 'blaze-06-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'blaze',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Blaze – Banner: Racket sports', 'envo-blocks' ),
		'file'     => 'blaze-07-banner.json',
		'image'    => 'blaze-07-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'blaze',
	),
	array(
		'name'             => __( 'Blaze – Products: Best sellers', 'envo-blocks' ),
		'file'             => 'blaze-08-products.json',
		'image'            => 'blaze-08-products.webp',
		'category'         => array(
			__( 'Products', 'envo-blocks' ),
		),
		'demo'             => 'blaze',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Blaze – Banner: Summer sale', 'envo-blocks' ),
		'file'     => 'blaze-09-banner.json',
		'image'    => 'blaze-09-banner.webp',
		'category' => array(
			__( 'Banner', 'envo-blocks' ),
		),
		'demo'     => 'blaze',
	),
	array(
		'name'             => __( 'Blaze – Reviews: Athletes say', 'envo-blocks' ),
		'file'             => 'blaze-10-reviews.json',
		'image'            => 'blaze-10-reviews.webp',
		'category'         => array(
			__( 'Reviews', 'envo-blocks' ),
		),
		'demo'             => 'blaze',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Blaze – Footer', 'envo-blocks' ),
		'file'     => 'blaze-11-footer.json',
		'image'    => 'blaze-11-footer.webp',
		'category' => array(
			__( 'Footer', 'envo-blocks' ),
		),
		'demo'     => 'blaze',
	),
);
