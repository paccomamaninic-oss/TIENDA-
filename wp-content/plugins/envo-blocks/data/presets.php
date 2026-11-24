<?php
/**
 * Presets for the "Presets" tab: the examples of the free block demos (ready-made settings of the blocks
 * of this plugin). The category is the name of the block.
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
		'name'             => __( 'Automatic threshold', 'envo-blocks' ),
		'file'             => 'shipping-bar-1.json',
		'image'            => 'shipping-bar-1.webp',
		'category'         => array(
			__( 'Free Shipping Bar', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/shipping-bar/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Manual threshold, own messages', 'envo-blocks' ),
		'file'             => 'shipping-bar-2.json',
		'image'            => 'shipping-bar-2.webp',
		'category'         => array(
			__( 'Free Shipping Bar', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/shipping-bar/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Left aligned, no bar, tinted', 'envo-blocks' ),
		'file'             => 'shipping-bar-3.json',
		'image'            => 'shipping-bar-3.webp',
		'category'         => array(
			__( 'Free Shipping Bar', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/shipping-bar/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Violet, thick bar', 'envo-blocks' ),
		'file'             => 'shipping-bar-4.json',
		'image'            => 'shipping-bar-4.webp',
		'category'         => array(
			__( 'Free Shipping Bar', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/shipping-bar/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Dark, right aligned', 'envo-blocks' ),
		'file'             => 'shipping-bar-5.json',
		'image'            => 'shipping-bar-5.webp',
		'category'         => array(
			__( 'Free Shipping Bar', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/shipping-bar/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Hides when reached', 'envo-blocks' ),
		'file'             => 'shipping-bar-6.json',
		'image'            => 'shipping-bar-6.webp',
		'category'         => array(
			__( 'Free Shipping Bar', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/shipping-bar/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Thin mint line', 'envo-blocks' ),
		'file'             => 'shipping-bar-7.json',
		'image'            => 'shipping-bar-7.webp',
		'category'         => array(
			__( 'Free Shipping Bar', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/shipping-bar/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Default', 'envo-blocks' ),
		'file'     => 'features-bar-1.json',
		'image'    => 'features-bar-1.webp',
		'category' => array(
			__( 'Features Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/features-bar/',
	),
	array(
		'name'     => __( 'Icons on top in violet circles', 'envo-blocks' ),
		'file'     => 'features-bar-2.json',
		'image'    => 'features-bar-2.webp',
		'category' => array(
			__( 'Features Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/features-bar/',
	),
	array(
		'name'     => __( 'Dark strip, three items', 'envo-blocks' ),
		'file'     => 'features-bar-3.json',
		'image'    => 'features-bar-3.webp',
		'category' => array(
			__( 'Features Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/features-bar/',
	),
	array(
		'name'     => __( 'Left aligned, no dividers', 'envo-blocks' ),
		'file'     => 'features-bar-4.json',
		'image'    => 'features-bar-4.webp',
		'category' => array(
			__( 'Features Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/features-bar/',
	),
	array(
		'name'     => __( 'Default', 'envo-blocks' ),
		'file'     => 'trust-badges-1.json',
		'image'    => 'trust-badges-1.webp',
		'category' => array(
			__( 'Trust Badges', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/trust-badges/',
	),
	array(
		'name'     => __( 'With heading and more methods', 'envo-blocks' ),
		'file'     => 'trust-badges-2.json',
		'image'    => 'trust-badges-2.webp',
		'category' => array(
			__( 'Trust Badges', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/trust-badges/',
	),
	array(
		'name'     => __( 'Monochrome in a box', 'envo-blocks' ),
		'file'     => 'trust-badges-3.json',
		'image'    => 'trust-badges-3.webp',
		'category' => array(
			__( 'Trust Badges', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/trust-badges/',
	),
	array(
		'name'     => __( 'Only guarantees', 'envo-blocks' ),
		'file'     => 'trust-badges-4.json',
		'image'    => 'trust-badges-4.webp',
		'category' => array(
			__( 'Trust Badges', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/trust-badges/',
	),
	array(
		'name'     => __( 'Only payment methods, large', 'envo-blocks' ),
		'file'     => 'trust-badges-5.json',
		'image'    => 'trust-badges-5.webp',
		'category' => array(
			__( 'Trust Badges', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/trust-badges/',
	),
	array(
		'name'     => __( 'Default', 'envo-blocks' ),
		'file'     => 'delivery-estimate-1.json',
		'image'    => 'delivery-estimate-1.webp',
		'category' => array(
			__( 'Delivery Estimate', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/delivery-estimate/',
	),
	array(
		'name'     => __( 'Later cut-off with seconds', 'envo-blocks' ),
		'file'     => 'delivery-estimate-2.json',
		'image'    => 'delivery-estimate-2.webp',
		'category' => array(
			__( 'Delivery Estimate', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/delivery-estimate/',
	),
	array(
		'name'     => __( 'Plain text, own messages', 'envo-blocks' ),
		'file'     => 'delivery-estimate-3.json',
		'image'    => 'delivery-estimate-3.webp',
		'category' => array(
			__( 'Delivery Estimate', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/delivery-estimate/',
	),
	array(
		'name'     => __( 'Line style, Saturday shipping', 'envo-blocks' ),
		'file'     => 'delivery-estimate-4.json',
		'image'    => 'delivery-estimate-4.webp',
		'category' => array(
			__( 'Delivery Estimate', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/delivery-estimate/',
	),
	array(
		'name'     => __( 'Default link', 'envo-blocks' ),
		'file'     => 'size-guide-1.json',
		'image'    => 'size-guide-1.webp',
		'category' => array(
			__( 'Size Guide', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/size-guide/',
	),
	array(
		'name'     => __( 'Violet button', 'envo-blocks' ),
		'file'     => 'size-guide-2.json',
		'image'    => 'size-guide-2.webp',
		'category' => array(
			__( 'Size Guide', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/size-guide/',
	),
	array(
		'name'     => __( 'Outline button, dark window', 'envo-blocks' ),
		'file'     => 'size-guide-3.json',
		'image'    => 'size-guide-3.webp',
		'category' => array(
			__( 'Size Guide', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/size-guide/',
	),
	array(
		'name'     => __( 'Link without icon, aligned right', 'envo-blocks' ),
		'file'     => 'size-guide-4.json',
		'image'    => 'size-guide-4.webp',
		'category' => array(
			__( 'Size Guide', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/size-guide/',
	),
	array(
		'name'             => __( 'Default', 'envo-blocks' ),
		'file'             => 'coupon-box-1.json',
		'image'            => 'coupon-box-1.webp',
		'category'         => array(
			__( 'Coupon Code Box', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/coupon-box/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Ticket with apply button', 'envo-blocks' ),
		'file'             => 'coupon-box-2.json',
		'image'            => 'coupon-box-2.webp',
		'category'         => array(
			__( 'Coupon Code Box', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/coupon-box/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Filled violet, stacked', 'envo-blocks' ),
		'file'             => 'coupon-box-3.json',
		'image'            => 'coupon-box-3.webp',
		'category'         => array(
			__( 'Coupon Code Box', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/coupon-box/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Solid, free shipping', 'envo-blocks' ),
		'file'             => 'coupon-box-4.json',
		'image'            => 'coupon-box-4.webp',
		'category'         => array(
			__( 'Coupon Code Box', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/coupon-box/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Whole shop', 'envo-blocks' ),
		'file'             => 'rating-summary-1.json',
		'image'            => 'rating-summary-1.webp',
		'category'         => array(
			__( 'Rating Summary', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/rating-summary/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'One product, stacked', 'envo-blocks' ),
		'file'             => 'rating-summary-2.json',
		'image'            => 'rating-summary-2.webp',
		'category'         => array(
			__( 'Rating Summary', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/rating-summary/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Violet, thick bars', 'envo-blocks' ),
		'file'             => 'rating-summary-3.json',
		'image'            => 'rating-summary-3.webp',
		'category'         => array(
			__( 'Rating Summary', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/rating-summary/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Compact', 'envo-blocks' ),
		'file'             => 'rating-summary-4.json',
		'image'            => 'rating-summary-4.webp',
		'category'         => array(
			__( 'Rating Summary', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/rating-summary/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Default', 'envo-blocks' ),
		'file'             => 'recently-viewed-1.json',
		'image'            => 'recently-viewed-1.webp',
		'category'         => array(
			__( 'Recently Viewed Products', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/recently-viewed/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'With rating and button', 'envo-blocks' ),
		'file'             => 'recently-viewed-2.json',
		'image'            => 'recently-viewed-2.webp',
		'category'         => array(
			__( 'Recently Viewed Products', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/recently-viewed/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Six small cards', 'envo-blocks' ),
		'file'             => 'recently-viewed-3.json',
		'image'            => 'recently-viewed-3.webp',
		'category'         => array(
			__( 'Recently Viewed Products', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/recently-viewed/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'On the product page', 'envo-blocks' ),
		'file'             => 'wishlist-1.json',
		'image'            => 'wishlist-1.webp',
		'category'         => array(
			__( 'Wishlist', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/wishlist/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Three styles', 'envo-blocks' ),
		'file'             => 'wishlist-2.json',
		'image'            => 'wishlist-2.webp',
		'category'         => array(
			__( 'Wishlist', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/wishlist/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Brand colors', 'envo-blocks' ),
		'file'             => 'wishlist-3.json',
		'image'            => 'wishlist-3.webp',
		'category'         => array(
			__( 'Wishlist', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/wishlist/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'The wishlist page: cards', 'envo-blocks' ),
		'file'             => 'wishlist-4.json',
		'image'            => 'wishlist-4.webp',
		'category'         => array(
			__( 'Wishlist', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/wishlist/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Rows, removed after adding to the cart', 'envo-blocks' ),
		'file'             => 'wishlist-5.json',
		'image'            => 'wishlist-5.webp',
		'category'         => array(
			__( 'Wishlist', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/wishlist/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Default', 'envo-blocks' ),
		'file'     => 'pricing-table-1.json',
		'image'    => 'pricing-table-1.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'Monthly or yearly in euros', 'envo-blocks' ),
		'file'     => 'pricing-table-2.json',
		'image'    => 'pricing-table-2.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'Dark cards', 'envo-blocks' ),
		'file'     => 'pricing-table-3.json',
		'image'    => 'pricing-table-3.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'One-time payment, left aligned', 'envo-blocks' ),
		'file'     => 'pricing-table-4.json',
		'image'    => 'pricing-table-4.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'Four plans with a custom price', 'envo-blocks' ),
		'file'     => 'pricing-table-5.json',
		'image'    => 'pricing-table-5.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'One plan, launch offer', 'envo-blocks' ),
		'file'     => 'pricing-table-6.json',
		'image'    => 'pricing-table-6.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'Currency after the price', 'envo-blocks' ),
		'file'     => 'pricing-table-7.json',
		'image'    => 'pricing-table-7.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'Soft pastel cards', 'envo-blocks' ),
		'file'     => 'pricing-table-8.json',
		'image'    => 'pricing-table-8.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'Shipping options without buttons', 'envo-blocks' ),
		'file'     => 'pricing-table-9.json',
		'image'    => 'pricing-table-9.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'Every plan discounted', 'envo-blocks' ),
		'file'     => 'pricing-table-10.json',
		'image'    => 'pricing-table-10.webp',
		'category' => array(
			__( 'Pricing Table', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/pricing-table/',
	),
	array(
		'name'     => __( 'Default', 'envo-blocks' ),
		'file'     => 'advanced-heading-1.json',
		'image'    => 'advanced-heading-1.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'Typing with a marker highlight', 'envo-blocks' ),
		'file'     => 'advanced-heading-2.json',
		'image'    => 'advanced-heading-2.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'Stacked, flip and underline', 'envo-blocks' ),
		'file'     => 'advanced-heading-3.json',
		'image'    => 'advanced-heading-3.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'Parts with background and border', 'envo-blocks' ),
		'file'     => 'advanced-heading-4.json',
		'image'    => 'advanced-heading-4.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'Last part on a new line', 'envo-blocks' ),
		'file'     => 'advanced-heading-5.json',
		'image'    => 'advanced-heading-5.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'Line break only on mobile', 'envo-blocks' ),
		'file'     => 'advanced-heading-6.json',
		'image'    => 'advanced-heading-6.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'Blur on a dark background', 'envo-blocks' ),
		'file'     => 'advanced-heading-7.json',
		'image'    => 'advanced-heading-7.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'Dashed frame around the middle', 'envo-blocks' ),
		'file'     => 'advanced-heading-8.json',
		'image'    => 'advanced-heading-8.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'One text with a marker', 'envo-blocks' ),
		'file'     => 'advanced-heading-9.json',
		'image'    => 'advanced-heading-9.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'H1, left aligned, typing once', 'envo-blocks' ),
		'file'     => 'advanced-heading-10.json',
		'image'    => 'advanced-heading-10.webp',
		'category' => array(
			__( 'Advanced Heading', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/advanced-heading/',
	),
	array(
		'name'     => __( 'Google Maps with a card', 'envo-blocks' ),
		'file'     => 'map-1.json',
		'image'    => 'map-1.webp',
		'category' => array(
			__( 'Map', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/map/',
	),
	array(
		'name'     => __( 'Black and white, dark card on the right', 'envo-blocks' ),
		'file'     => 'map-2.json',
		'image'    => 'map-2.webp',
		'category' => array(
			__( 'Map', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/map/',
	),
	array(
		'name'     => __( 'OpenStreetMap', 'envo-blocks' ),
		'file'     => 'map-3.json',
		'image'    => 'map-3.webp',
		'category' => array(
			__( 'Map', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/map/',
	),
	array(
		'name'     => __( 'Loads after a click', 'envo-blocks' ),
		'file'     => 'map-4.json',
		'image'    => 'map-4.webp',
		'category' => array(
			__( 'Map', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/map/',
	),
	array(
		'name'     => __( 'Satellite, tall, no card', 'envo-blocks' ),
		'file'     => 'map-5.json',
		'image'    => 'map-5.webp',
		'category' => array(
			__( 'Map', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/map/',
	),
	array(
		'name'     => __( 'Image with a play button', 'envo-blocks' ),
		'file'     => 'video-popup-1.json',
		'image'    => 'video-popup-1.webp',
		'category' => array(
			__( 'Video Popup', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/video-popup/',
	),
	array(
		'name'     => __( 'Violet button, caption', 'envo-blocks' ),
		'file'     => 'video-popup-2.json',
		'image'    => 'video-popup-2.webp',
		'category' => array(
			__( 'Video Popup', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/video-popup/',
	),
	array(
		'name'     => __( 'Frosted glass, bottom left', 'envo-blocks' ),
		'file'     => 'video-popup-3.json',
		'image'    => 'video-popup-3.webp',
		'category' => array(
			__( 'Video Popup', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/video-popup/',
	),
	array(
		'name'     => __( 'Only the button with text', 'envo-blocks' ),
		'file'     => 'video-popup-4.json',
		'image'    => 'video-popup-4.webp',
		'category' => array(
			__( 'Video Popup', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/video-popup/',
	),
	array(
		'name'     => __( 'Large rounded square, starts later', 'envo-blocks' ),
		'file'     => 'video-popup-5.json',
		'image'    => 'video-popup-5.webp',
		'category' => array(
			__( 'Video Popup', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/video-popup/',
	),
	array(
		'name'     => __( 'Above the header, taking turns', 'envo-blocks' ),
		'file'     => 'announcement-bar-1.json',
		'image'    => 'announcement-bar-1.webp',
		'category' => array(
			__( 'Announcement Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/announcement-bar/',
	),
	array(
		'name'     => __( 'Scrolling ticker with links', 'envo-blocks' ),
		'file'     => 'announcement-bar-2.json',
		'image'    => 'announcement-bar-2.webp',
		'category' => array(
			__( 'Announcement Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/announcement-bar/',
	),
	array(
		'name'     => __( 'Side by side, light', 'envo-blocks' ),
		'file'     => 'announcement-bar-3.json',
		'image'    => 'announcement-bar-3.webp',
		'category' => array(
			__( 'Announcement Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/announcement-bar/',
	),
	array(
		'name'     => __( 'A sale you can close', 'envo-blocks' ),
		'file'     => 'announcement-bar-4.json',
		'image'    => 'announcement-bar-4.webp',
		'category' => array(
			__( 'Announcement Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/announcement-bar/',
	),
	array(
		'name'     => __( 'Left aligned on a dark header', 'envo-blocks' ),
		'file'     => 'announcement-bar-5.json',
		'image'    => 'announcement-bar-5.webp',
		'category' => array(
			__( 'Announcement Bar', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/announcement-bar/',
	),
	array(
		'name'     => __( 'Sale until a date', 'envo-blocks' ),
		'file'     => 'countdown-1.json',
		'image'    => 'countdown-1.webp',
		'category' => array(
			__( 'Countdown', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/countdown/',
	),
	array(
		'name'     => __( 'Order by 2 pm, ships today', 'envo-blocks' ),
		'file'     => 'countdown-2.json',
		'image'    => 'countdown-2.webp',
		'category' => array(
			__( 'Countdown', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/countdown/',
	),
	array(
		'name'     => __( 'Deal of the day, dark', 'envo-blocks' ),
		'file'     => 'countdown-3.json',
		'image'    => 'countdown-3.webp',
		'category' => array(
			__( 'Countdown', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/countdown/',
	),
	array(
		'name'     => __( 'When it ends: a text', 'envo-blocks' ),
		'file'     => 'countdown-4.json',
		'image'    => 'countdown-4.webp',
		'category' => array(
			__( 'Countdown', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/countdown/',
	),
	array(
		'name'     => __( 'Weekend sale, no seconds', 'envo-blocks' ),
		'file'     => 'countdown-5.json',
		'image'    => 'countdown-5.webp',
		'category' => array(
			__( 'Countdown', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/countdown/',
	),
	array(
		'name'     => __( 'Default', 'envo-blocks' ),
		'file'     => 'opening-hours-1.json',
		'image'    => 'opening-hours-1.webp',
		'category' => array(
			__( 'Opening Hours', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/opening-hours/',
	),
	array(
		'name'     => __( 'Lunch break and holidays', 'envo-blocks' ),
		'file'     => 'opening-hours-2.json',
		'image'    => 'opening-hours-2.webp',
		'category' => array(
			__( 'Opening Hours', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/opening-hours/',
	),
	array(
		'name'     => __( 'Late nights, dark', 'envo-blocks' ),
		'file'     => 'opening-hours-3.json',
		'image'    => 'opening-hours-3.webp',
		'category' => array(
			__( 'Opening Hours', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/opening-hours/',
	),
	array(
		'name'     => __( 'Only the status, in a contact card', 'envo-blocks' ),
		'file'     => 'opening-hours-4.json',
		'image'    => 'opening-hours-4.webp',
		'category' => array(
			__( 'Opening Hours', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/opening-hours/',
	),
	array(
		'name'     => __( 'Two stores side by side', 'envo-blocks' ),
		'file'     => 'opening-hours-5.json',
		'image'    => 'opening-hours-5.webp',
		'category' => array(
			__( 'Opening Hours', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/opening-hours/',
	),
	array(
		'name'             => __( 'On the product, above the button', 'envo-blocks' ),
		'file'             => 'stock-indicator-1.json',
		'image'            => 'stock-indicator-1.webp',
		'category'         => array(
			__( 'Stock Indicator', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/stock-indicator/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'The last piece', 'envo-blocks' ),
		'file'             => 'stock-indicator-2.json',
		'image'            => 'stock-indicator-2.webp',
		'category'         => array(
			__( 'Stock Indicator', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/stock-indicator/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( '"In stock" above the limit', 'envo-blocks' ),
		'file'             => 'stock-indicator-3.json',
		'image'            => 'stock-indicator-3.webp',
		'category'         => array(
			__( 'Stock Indicator', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/stock-indicator/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Out of stock', 'envo-blocks' ),
		'file'             => 'stock-indicator-4.json',
		'image'            => 'stock-indicator-4.webp',
		'category'         => array(
			__( 'Stock Indicator', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/stock-indicator/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Only the text, violet', 'envo-blocks' ),
		'file'             => 'stock-indicator-5.json',
		'image'            => 'stock-indicator-5.webp',
		'category'         => array(
			__( 'Stock Indicator', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/stock-indicator/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Default', 'envo-blocks' ),
		'file'             => 'order-tracking-1.json',
		'image'            => 'order-tracking-1.webp',
		'category'         => array(
			__( 'Order Tracking', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/order-tracking/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Centered, plain', 'envo-blocks' ),
		'file'             => 'order-tracking-2.json',
		'image'            => 'order-tracking-2.webp',
		'category'         => array(
			__( 'Order Tracking', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/order-tracking/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Dark brand box', 'envo-blocks' ),
		'file'             => 'order-tracking-3.json',
		'image'            => 'order-tracking-3.webp',
		'category'         => array(
			__( 'Order Tracking', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/order-tracking/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Next to the help', 'envo-blocks' ),
		'file'             => 'order-tracking-4.json',
		'image'            => 'order-tracking-4.webp',
		'category'         => array(
			__( 'Order Tracking', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/order-tracking/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'QR Platba (CZK)', 'envo-blocks' ),
		'file'     => 'payment-qr-1.json',
		'image'    => 'payment-qr-1.webp',
		'category' => array(
			__( 'Payment QR Code', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/payment-qr/',
	),
	array(
		'name'     => __( 'EPC QR for euro payments', 'envo-blocks' ),
		'file'     => 'payment-qr-2.json',
		'image'    => 'payment-qr-2.webp',
		'category' => array(
			__( 'Payment QR Code', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/payment-qr/',
	),
	array(
		'name'     => __( 'Dark brand box', 'envo-blocks' ),
		'file'     => 'payment-qr-3.json',
		'image'    => 'payment-qr-3.webp',
		'category' => array(
			__( 'Payment QR Code', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/payment-qr/',
	),
	array(
		'name'     => __( 'Next to the thank-you', 'envo-blocks' ),
		'file'     => 'payment-qr-4.json',
		'image'    => 'payment-qr-4.webp',
		'category' => array(
			__( 'Payment QR Code', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/payment-qr/',
	),
	array(
		'name'     => __( 'Classic, on top', 'envo-blocks' ),
		'file'     => 'product-tabs-1.json',
		'image'    => 'product-tabs-1.webp',
		'category' => array(
			__( 'Product Tabs', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/product-tabs/',
	),
	array(
		'name'     => __( 'On the left, with icons', 'envo-blocks' ),
		'file'     => 'product-tabs-2.json',
		'image'    => 'product-tabs-2.webp',
		'category' => array(
			__( 'Product Tabs', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/product-tabs/',
	),
	array(
		'name'     => __( 'Folder tabs with a spec sheet', 'envo-blocks' ),
		'file'     => 'product-tabs-3.json',
		'image'    => 'product-tabs-3.webp',
		'category' => array(
			__( 'Product Tabs', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/product-tabs/',
	),
	array(
		'name'     => __( 'Accordion', 'envo-blocks' ),
		'file'     => 'product-tabs-4.json',
		'image'    => 'product-tabs-4.webp',
		'category' => array(
			__( 'Product Tabs', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/product-tabs/',
	),
	array(
		'name'     => __( 'On the right, on dark', 'envo-blocks' ),
		'file'     => 'product-tabs-5.json',
		'image'    => 'product-tabs-5.webp',
		'category' => array(
			__( 'Product Tabs', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/product-tabs/',
	),
	array(
		'name'             => __( 'A shop with filters', 'envo-blocks' ),
		'file'             => 'product-filters-1.json',
		'image'            => 'product-filters-1.webp',
		'category'         => array(
			__( 'Product Filters', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/product-filters/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Looks of the options', 'envo-blocks' ),
		'file'     => 'product-filters-2.json',
		'image'    => 'product-filters-2.webp',
		'category' => array(
			__( 'Product Filters', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/product-filters/',
	),
	array(
		'name'             => __( 'Filters in a drawer', 'envo-blocks' ),
		'file'             => 'product-filters-3.json',
		'image'            => 'product-filters-3.webp',
		'category'         => array(
			__( 'Product Filters', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/product-filters/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Under the price of a product on sale', 'envo-blocks' ),
		'file'             => 'lowest-price-1.json',
		'image'            => 'lowest-price-1.webp',
		'category'         => array(
			__( 'Lowest Price', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/lowest-price/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'An earlier promotion counts', 'envo-blocks' ),
		'file'             => 'lowest-price-2.json',
		'image'            => 'lowest-price-2.webp',
		'category'         => array(
			__( 'Lowest Price', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/lowest-price/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Your words, 60 days', 'envo-blocks' ),
		'file'             => 'lowest-price-3.json',
		'image'            => 'lowest-price-3.webp',
		'category'         => array(
			__( 'Lowest Price', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/lowest-price/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'For any product', 'envo-blocks' ),
		'file'     => 'lowest-price-4.json',
		'image'    => 'lowest-price-4.webp',
		'category' => array(
			__( 'Lowest Price', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/lowest-price/',
	),
	array(
		'name'             => __( 'On the product page', 'envo-blocks' ),
		'file'             => 'review-photos-1.json',
		'image'            => 'review-photos-1.webp',
		'category'         => array(
			__( 'Customer Photos', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/review-photos/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'A photo wall of the whole shop', 'envo-blocks' ),
		'file'     => 'review-photos-2.json',
		'image'    => 'review-photos-2.webp',
		'category' => array(
			__( 'Customer Photos', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/review-photos/',
	),
	array(
		'name'     => __( 'One product, your colors', 'envo-blocks' ),
		'file'     => 'review-photos-3.json',
		'image'    => 'review-photos-3.webp',
		'category' => array(
			__( 'Customer Photos', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/review-photos/',
	),
	array(
		'name'     => __( 'Shop questions as cards', 'envo-blocks' ),
		'file'     => 'faq-1.json',
		'image'    => 'faq-1.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/faq/',
	),
	array(
		'name'     => __( 'Next to the support', 'envo-blocks' ),
		'file'     => 'faq-2.json',
		'image'    => 'faq-2.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/faq/',
	),
	array(
		'name'     => __( 'Lines, the icon on the left', 'envo-blocks' ),
		'file'     => 'faq-3.json',
		'image'    => 'faq-3.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/faq/',
	),
	array(
		'name'     => __( 'Your colors, more open at once', 'envo-blocks' ),
		'file'     => 'faq-4.json',
		'image'    => 'faq-4.webp',
		'category' => array(
			__( 'FAQ', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/faq/',
	),
	array(
		'name'             => __( 'On the product page', 'envo-blocks' ),
		'file'             => 'product-questions-1.json',
		'image'            => 'product-questions-1.webp',
		'category'         => array(
			__( 'Product Questions', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/product-questions/',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'     => __( 'Beside the product facts', 'envo-blocks' ),
		'file'     => 'product-questions-2.json',
		'image'    => 'product-questions-2.webp',
		'category' => array(
			__( 'Product Questions', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/product-questions/',
	),
	array(
		'name'     => __( 'Your colors, only the answers', 'envo-blocks' ),
		'file'     => 'product-questions-3.json',
		'image'    => 'product-questions-3.webp',
		'category' => array(
			__( 'Product Questions', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/product-questions/',
	),
	array(
		'name'     => __( 'A contact form in two columns', 'envo-blocks' ),
		'file'     => 'form-styler-1.json',
		'image'    => 'form-styler-1.webp',
		'category' => array(
			__( 'Form Styler', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/form-styler/',
	),
	array(
		'name'     => __( 'Filled fields next to your contact', 'envo-blocks' ),
		'file'     => 'form-styler-2.json',
		'image'    => 'form-styler-2.webp',
		'category' => array(
			__( 'Form Styler', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/form-styler/',
	),
	array(
		'name'     => __( 'Lines on a dark section', 'envo-blocks' ),
		'file'     => 'form-styler-3.json',
		'image'    => 'form-styler-3.webp',
		'category' => array(
			__( 'Form Styler', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/form-styler/',
	),
	array(
		'name'     => __( 'WPForms with the same settings', 'envo-blocks' ),
		'file'     => 'form-styler-4.json',
		'image'    => 'form-styler-4.webp',
		'category' => array(
			__( 'Form Styler', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/form-styler/',
	),
	array(
		'name'     => __( 'A dropdown in the store header', 'envo-blocks' ),
		'file'     => 'language-switcher-1.json',
		'image'    => 'language-switcher-1.webp',
		'category' => array(
			__( 'Language Switcher', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/language-switcher/',
	),
	array(
		'name'     => __( 'Codes in a top bar', 'envo-blocks' ),
		'file'     => 'language-switcher-2.json',
		'image'    => 'language-switcher-2.webp',
		'category' => array(
			__( 'Language Switcher', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/language-switcher/',
	),
	array(
		'name'     => __( 'Round flags in the footer', 'envo-blocks' ),
		'file'     => 'language-switcher-3.json',
		'image'    => 'language-switcher-3.webp',
		'category' => array(
			__( 'Language Switcher', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/language-switcher/',
	),
	array(
		'name'     => __( 'Names in a row', 'envo-blocks' ),
		'file'     => 'language-switcher-4.json',
		'image'    => 'language-switcher-4.webp',
		'category' => array(
			__( 'Language Switcher', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/language-switcher/',
	),
	array(
		'name'     => __( 'A hero that moves slower than the page', 'envo-blocks' ),
		'file'     => 'parallax-1.json',
		'image'    => 'parallax-1.webp',
		'category' => array(
			__( 'Parallax Section', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/parallax/',
	),
	array(
		'name'     => __( 'Zoom while scrolling', 'envo-blocks' ),
		'file'     => 'parallax-2.json',
		'image'    => 'parallax-2.webp',
		'category' => array(
			__( 'Parallax Section', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/parallax/',
	),
	array(
		'name'     => __( 'A light overlay with dark text', 'envo-blocks' ),
		'file'     => 'parallax-3.json',
		'image'    => 'parallax-3.webp',
		'category' => array(
			__( 'Parallax Section', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/parallax/',
	),
	array(
		'name'     => __( 'A video background', 'envo-blocks' ),
		'file'     => 'parallax-4.json',
		'image'    => 'parallax-4.webp',
		'category' => array(
			__( 'Parallax Section', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/parallax/',
	),
	array(
		'name'     => __( 'Any blocks inside', 'envo-blocks' ),
		'file'     => 'parallax-5.json',
		'image'    => 'parallax-5.webp',
		'category' => array(
			__( 'Parallax Section', 'envo-blocks' ),
		),
		'link'     => 'https://demos.envoblocks.com/block-demos/parallax/',
	),
	array(
		'name'      => __( 'A table of contents next to a long guide', 'envo-blocks' ),
		'file'      => 'sticky-column-1.json',
		'image'     => 'sticky-column-1.webp',
		'category'  => array(
			__( 'Sticky Column', 'envo-blocks' ),
		),
		'link'      => 'https://demos.envoblocks.com/block-demos/sticky-column/',
		'image_url' => 'https://demos.envoblocks.com/wp-content/uploads/2026/11/demo-sticky-column.webp',
	),
	array(
		'name'      => __( 'An order summary beside the details', 'envo-blocks' ),
		'file'      => 'sticky-column-2.json',
		'image'     => 'sticky-column-2.webp',
		'category'  => array(
			__( 'Sticky Column', 'envo-blocks' ),
		),
		'link'      => 'https://demos.envoblocks.com/block-demos/sticky-column/',
		'image_url' => 'https://demos.envoblocks.com/wp-content/uploads/2026/11/demo-sticky-column.webp',
	),
	array(
		'name'      => __( 'A tall sidebar that floats', 'envo-blocks' ),
		'file'      => 'sticky-column-3.json',
		'image'     => 'sticky-column-3.webp',
		'category'  => array(
			__( 'Sticky Column', 'envo-blocks' ),
		),
		'link'      => 'https://demos.envoblocks.com/block-demos/sticky-column/',
		'image_url' => 'https://demos.envoblocks.com/wp-content/uploads/2026/11/demo-sticky-column.webp',
	),
	array(
		'name'             => __( 'Two buttons in the store header', 'envo-blocks' ),
		'file'             => 'tax-switcher-1.json',
		'image'            => 'tax-switcher-1.webp',
		'category'         => array(
			__( 'Tax Switcher', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/tax-switcher/',
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/11/demo-tax-switcher.webp',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'A switch next to the list', 'envo-blocks' ),
		'file'             => 'tax-switcher-2.json',
		'image'            => 'tax-switcher-2.webp',
		'category'         => array(
			__( 'Tax Switcher', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/tax-switcher/',
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/11/demo-tax-switcher.webp',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'A dropdown on the product page', 'envo-blocks' ),
		'file'             => 'tax-switcher-3.json',
		'image'            => 'tax-switcher-3.webp',
		'category'         => array(
			__( 'Tax Switcher', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/tax-switcher/',
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/11/demo-tax-switcher.webp',
		'required_plugins' => $envo_blocks_woocommerce,
	),
	array(
		'name'             => __( 'Your own words', 'envo-blocks' ),
		'file'             => 'tax-switcher-4.json',
		'image'            => 'tax-switcher-4.webp',
		'category'         => array(
			__( 'Tax Switcher', 'envo-blocks' ),
		),
		'link'             => 'https://demos.envoblocks.com/block-demos/tax-switcher/',
		'image_url'        => 'https://demos.envoblocks.com/wp-content/uploads/2026/11/demo-tax-switcher.webp',
		'required_plugins' => $envo_blocks_woocommerce,
	),
);
