<?php
/**
 * Designs of the store pages for the "Layouts" tab (Envo Blocks › Store Layouts): the free layout demos of
 * demos.envoblocks.com. "Use" makes a store layout of a design.
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
		'name'             => __( 'Classic · Ivory', 'envo-blocks' ),
		'file'             => 'product-classic-ivory.json',
		'image'            => 'product-classic-ivory.webp',
		'category'         => array(
			__( 'Product page', 'envo-blocks' ),
		),
		'layout_type'      => 'product',
		'color'            => '#1f1b16',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-product-classic-ivory-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Sage', 'envo-blocks' ),
		'file'             => 'product-classic-sage.json',
		'image'            => 'product-classic-sage.webp',
		'category'         => array(
			__( 'Product page', 'envo-blocks' ),
		),
		'layout_type'      => 'product',
		'color'            => '#2f6b4f',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-product-classic-sage-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Ocean', 'envo-blocks' ),
		'file'             => 'product-classic-ocean.json',
		'image'            => 'product-classic-ocean.webp',
		'category'         => array(
			__( 'Product page', 'envo-blocks' ),
		),
		'layout_type'      => 'product',
		'color'            => '#1d5fd6',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-product-classic-ocean-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Ivory', 'envo-blocks' ),
		'file'             => 'shop-classic-ivory.json',
		'image'            => 'shop-classic-ivory.webp',
		'category'         => array(
			__( 'Shop page', 'envo-blocks' ),
		),
		'layout_type'      => 'shop',
		'color'            => '#1f1b16',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-shop-classic-ivory-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Ocean', 'envo-blocks' ),
		'file'             => 'shop-classic-ocean.json',
		'image'            => 'shop-classic-ocean.webp',
		'category'         => array(
			__( 'Shop page', 'envo-blocks' ),
		),
		'layout_type'      => 'shop',
		'color'            => '#1d5fd6',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-shop-classic-ocean-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Sage', 'envo-blocks' ),
		'file'             => 'shop-classic-sage.json',
		'image'            => 'shop-classic-sage.webp',
		'category'         => array(
			__( 'Shop page', 'envo-blocks' ),
		),
		'layout_type'      => 'shop',
		'color'            => '#2f6b4f',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-shop-classic-sage-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Ivory', 'envo-blocks' ),
		'file'             => 'cart-classic-ivory.json',
		'image'            => 'cart-classic-ivory.webp',
		'category'         => array(
			__( 'Cart', 'envo-blocks' ),
		),
		'layout_type'      => 'cart',
		'color'            => '#1f1b16',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-cart-classic-ivory-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Ocean', 'envo-blocks' ),
		'file'             => 'cart-classic-ocean.json',
		'image'            => 'cart-classic-ocean.webp',
		'category'         => array(
			__( 'Cart', 'envo-blocks' ),
		),
		'layout_type'      => 'cart',
		'color'            => '#1d5fd6',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-cart-classic-ocean-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Mono', 'envo-blocks' ),
		'file'             => 'cart-classic-mono.json',
		'image'            => 'cart-classic-mono.webp',
		'category'         => array(
			__( 'Cart', 'envo-blocks' ),
		),
		'layout_type'      => 'cart',
		'color'            => '#0a0a0a',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-cart-classic-mono-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Sage', 'envo-blocks' ),
		'file'             => 'thankyou-classic-sage.json',
		'image'            => 'thankyou-classic-sage.webp',
		'category'         => array(
			__( 'Order received', 'envo-blocks' ),
		),
		'layout_type'      => 'thankyou',
		'color'            => '#2f6b4f',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-thankyou-classic-sage-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Ocean', 'envo-blocks' ),
		'file'             => 'thankyou-classic-ocean.json',
		'image'            => 'thankyou-classic-ocean.webp',
		'category'         => array(
			__( 'Order received', 'envo-blocks' ),
		),
		'layout_type'      => 'thankyou',
		'color'            => '#1d5fd6',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-thankyou-classic-ocean-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Classic · Ivory', 'envo-blocks' ),
		'file'             => 'thankyou-classic-ivory.json',
		'image'            => 'thankyou-classic-ivory.webp',
		'category'         => array(
			__( 'Order received', 'envo-blocks' ),
		),
		'layout_type'      => 'thankyou',
		'color'            => '#1f1b16',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-thankyou-classic-ivory-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Sidebar · Ivory', 'envo-blocks' ),
		'file'             => 'account-sidebar-ivory.json',
		'image'            => 'account-sidebar-ivory.webp',
		'category'         => array(
			__( 'My account', 'envo-blocks' ),
		),
		'layout_type'      => 'account',
		'color'            => '#1f1b16',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-account-sidebar-ivory-819x1024.webp',
	),
	array(
		'name'             => __( 'Sidebar · Ocean', 'envo-blocks' ),
		'file'             => 'account-sidebar-ocean.json',
		'image'            => 'account-sidebar-ocean.webp',
		'category'         => array(
			__( 'My account', 'envo-blocks' ),
		),
		'layout_type'      => 'account',
		'color'            => '#1d5fd6',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-account-sidebar-ocean-819x1024.webp',
	),
	array(
		'name'             => __( 'Sidebar · Sage', 'envo-blocks' ),
		'file'             => 'account-sidebar-sage.json',
		'image'            => 'account-sidebar-sage.webp',
		'category'         => array(
			__( 'My account', 'envo-blocks' ),
		),
		'layout_type'      => 'account',
		'color'            => '#2f6b4f',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-account-sidebar-sage-819x1024.webp',
	),
	array(
		'name'             => __( 'Essential · Ivory', 'envo-blocks' ),
		'file'             => 'side-cart-essential-ivory.json',
		'image'            => 'side-cart-essential-ivory.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#1f1b16',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-essential-ivory-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Essential · Ocean', 'envo-blocks' ),
		'file'             => 'side-cart-essential-ocean.json',
		'image'            => 'side-cart-essential-ocean.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#1d5fd6',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-essential-ocean-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Essential · Sage', 'envo-blocks' ),
		'file'             => 'side-cart-essential-sage.json',
		'image'            => 'side-cart-essential-sage.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#2f6b4f',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-essential-sage-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Compact · Mono', 'envo-blocks' ),
		'file'             => 'side-cart-compact-mono.json',
		'image'            => 'side-cart-compact-mono.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#0a0a0a',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-compact-mono-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Compact · Coral', 'envo-blocks' ),
		'file'             => 'side-cart-compact-coral.json',
		'image'            => 'side-cart-compact-coral.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#e2553d',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-compact-coral-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Compact · Violet', 'envo-blocks' ),
		'file'             => 'side-cart-compact-violet.json',
		'image'            => 'side-cart-compact-violet.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#6d3ee8',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-compact-violet-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Night · Noir', 'envo-blocks' ),
		'file'             => 'side-cart-night-noir.json',
		'image'            => 'side-cart-night-noir.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#111111',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-night-noir-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Night · Midnight', 'envo-blocks' ),
		'file'             => 'side-cart-night-midnight.json',
		'image'            => 'side-cart-night-midnight.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#0e1726',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-night-midnight-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Night · Forest', 'envo-blocks' ),
		'file'             => 'side-cart-night-forest.json',
		'image'            => 'side-cart-night-forest.webp',
		'category'         => array(
			__( 'Side cart', 'envo-blocks' ),
		),
		'layout_type'      => 'side_cart',
		'color'            => '#102a1f',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-side-cart-night-forest-1-819x1024.webp',
	),
	array(
		'name'             => __( 'Express · Ivory', 'envo-blocks' ),
		'file'             => 'checkout-express-ivory.json',
		'image'            => 'checkout-express-ivory.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#1f1b16',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-express-ivory-1024x838.webp',
	),
	array(
		'name'             => __( 'Express · Ocean', 'envo-blocks' ),
		'file'             => 'checkout-express-ocean.json',
		'image'            => 'checkout-express-ocean.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#1d5fd6',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-express-ocean-1024x838.webp',
	),
	array(
		'name'             => __( 'Express · Sage', 'envo-blocks' ),
		'file'             => 'checkout-express-sage.json',
		'image'            => 'checkout-express-sage.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#2f6b4f',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-express-sage-1024x838.webp',
	),
	array(
		'name'             => __( 'Classic · Mono', 'envo-blocks' ),
		'file'             => 'checkout-classic-mono.json',
		'image'            => 'checkout-classic-mono.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#0a0a0a',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-classic-mono-1024x986.webp',
	),
	array(
		'name'             => __( 'Classic · Coral', 'envo-blocks' ),
		'file'             => 'checkout-classic-coral.json',
		'image'            => 'checkout-classic-coral.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#e2553d',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-classic-coral-1024x986.webp',
	),
	array(
		'name'             => __( 'Classic · Violet', 'envo-blocks' ),
		'file'             => 'checkout-classic-violet.json',
		'image'            => 'checkout-classic-violet.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#6d3ee8',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-classic-violet-1024x986.webp',
	),
	array(
		'name'             => __( 'Focus · Noir', 'envo-blocks' ),
		'file'             => 'checkout-focus-noir.json',
		'image'            => 'checkout-focus-noir.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#111111',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-focus-noir-1024x966.webp',
	),
	array(
		'name'             => __( 'Focus · Midnight', 'envo-blocks' ),
		'file'             => 'checkout-focus-midnight.json',
		'image'            => 'checkout-focus-midnight.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#0e1726',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-focus-midnight-1024x966.webp',
	),
	array(
		'name'             => __( 'Focus · Forest', 'envo-blocks' ),
		'file'             => 'checkout-focus-forest.json',
		'image'            => 'checkout-focus-forest.webp',
		'category'         => array(
			__( 'Checkout', 'envo-blocks' ),
		),
		'layout_type'      => 'checkout',
		'color'            => '#102a1f',
		'required_plugins' => $envo_blocks_woocommerce,
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/10/layout-checkout-focus-forest-1024x966.webp',
	),
);
