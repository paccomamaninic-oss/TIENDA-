<?php
/**
 * Content of the Envo Blocks admin pages: addresses, guides and what Envo Blocks PRO adds.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_docs     = 'https://envoblocks.com/docs/envo-blocks/';
$envo_blocks_pro_docs = 'https://envoblocks.com/docs/envo-blocks-pro/';

return array(
	'urls'       => array(
		'site'           => 'https://envoblocks.com/',
		'pricing'        => 'https://envoblocks.com/pricing/',
		'docs'           => $envo_blocks_docs,
		'pro_docs'       => $envo_blocks_pro_docs,
		'demos'          => 'https://demos.envoblocks.com/',
		'block_demos'    => 'https://demos.envoblocks.com/block-demos/',
		'template_demos' => 'https://demos.envoblocks.com/template-demos/',
		'support'        => 'https://wordpress.org/support/plugin/envo-blocks/',
		'review'         => 'https://wordpress.org/support/plugin/envo-blocks/reviews/#new-post',
	),

	// Languages of the docs on envoblocks.com (/<lang>/docs/…): the links open them in the language of the user.
	'doc_languages' => array( 'cs', 'de', 'es', 'fr', 'it', 'nl', 'pl', 'pt' ),

	// Docs of the blocks of this plugin: block folder => section of the docs ("section/doc" when the doc has another slug).
	'block_docs' => array(
		'features-bar'      => 'shop-blocks',
		'trust-badges'      => 'shop-blocks',
		'delivery-estimate' => 'shop-blocks',
		'coupon-box'        => 'shop-blocks',
		'rating-summary'    => 'shop-blocks',
		'review-photos'     => 'shop-blocks',
		'product-questions' => 'shop-blocks',
		'recently-viewed'   => 'shop-blocks',
		'size-guide'        => 'shop-blocks',
		'stock-indicator'   => 'shop-blocks',
		'lowest-price'      => 'shop-blocks',
		'product-filter'    => 'shop-blocks',
		'active-filters'    => 'shop-blocks/product-filter',
		'order-tracking'    => 'shop-blocks',
		'payment-qr'        => 'shop-blocks',
		'product-tabs'      => 'shop-blocks',
		'shipping-bar'      => 'shop-blocks',
		'wishlist-button'   => 'shop-blocks',
		'wishlist'          => 'shop-blocks',
		'tax-switcher'      => 'shop-blocks/tax-switcher',
		'advanced-heading'  => 'content-blocks',
		'pricing-table'     => 'content-blocks',
		'video-popup'       => 'content-blocks',
		'map'               => 'content-blocks',
		'faq'               => 'content-blocks/faq-block',
		'form-styler'       => 'content-blocks/form-styler',
		'language-switcher' => 'content-blocks/language-switcher',
		'parallax'          => 'content-blocks/parallax-section',
		'sticky-column'     => 'content-blocks/sticky-column',
		'announcement-bar'  => 'content-blocks',
		'countdown'         => 'content-blocks',
		'opening-hours'     => 'content-blocks',
		'invoice-download'  => 'store-features/invoices',
		'store-breadcrumbs' => 'store-layouts',
		'shop-toolbar'      => 'store-layouts',
		'order-received'    => 'store-layouts',
		'my-account'        => 'store-layouts',
		'checkout-form'     => 'store-layouts/checkout-layouts',
		'checkout-header'   => 'store-layouts/checkout-layouts',
		'cart-items'        => 'store-layouts/side-cart',
		'cart-summary'      => 'store-layouts/side-cart',
	),

	// Blocks whose demo has another slug (block folder => demo slug).
	'block_demo' => array(
		'product-filter' => 'product-filters',
		'active-filters' => 'product-filters',
	),

	// Blocks without a live demo (block folders).
	'no_demo'    => array( 'invoice-download', 'store-breadcrumbs', 'shop-toolbar', 'order-received', 'my-account', 'cart-items', 'cart-summary', 'checkout-form', 'checkout-header' ),

	'guides'     => array(
		array(
			'title' => __( 'Getting started', 'envo-blocks' ),
			'icon'  => 'flag',
			'links' => array(
				array( __( 'Installation', 'envo-blocks' ), $envo_blocks_docs . 'getting-started/installation/' ),
				array( __( 'First steps', 'envo-blocks' ), $envo_blocks_docs . 'getting-started/first-steps/' ),
				array( __( 'Store in 5 minutes', 'envo-blocks' ), $envo_blocks_docs . 'getting-started/store-in-5-minutes/' ),
				array( __( 'Import a template', 'envo-blocks' ), $envo_blocks_docs . 'getting-started/import-templates/' ),
				array( __( 'Header and footer of templates', 'envo-blocks' ), $envo_blocks_docs . 'getting-started/header-footer/' ),
				array( __( 'Export and import a page as a file', 'envo-blocks' ), $envo_blocks_docs . 'getting-started/export-json/' ),
			),
		),
		array(
			'title' => __( 'Store layouts', 'envo-blocks' ),
			'icon'  => 'layout',
			'links' => array(
				array( __( 'Store layouts', 'envo-blocks' ), $envo_blocks_docs . 'store-layouts/use-store-layouts/' ),
				array( __( 'Side cart', 'envo-blocks' ), $envo_blocks_docs . 'store-layouts/side-cart/' ),
				array( __( 'Checkout layouts', 'envo-blocks' ), $envo_blocks_docs . 'store-layouts/checkout-layouts/' ),
				array( __( 'Store Breadcrumbs', 'envo-blocks' ), $envo_blocks_docs . 'store-layouts/store-breadcrumbs/' ),
				array( __( 'Shop Toolbar', 'envo-blocks' ), $envo_blocks_docs . 'store-layouts/shop-toolbar/' ),
				array( __( 'Order Received', 'envo-blocks' ), $envo_blocks_docs . 'store-layouts/order-received/' ),
				array( __( 'My Account', 'envo-blocks' ), $envo_blocks_docs . 'store-layouts/my-account/' ),
			),
		),
		array(
			'title' => __( 'Shop blocks', 'envo-blocks' ),
			'icon'  => 'cart',
			'links' => array(
				array( __( 'Features Bar', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/features-bar/' ),
				array( __( 'Trust Badges', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/trust-badges/' ),
				array( __( 'Delivery Estimate', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/delivery-estimate/' ),
				array( __( 'Coupon Code Box', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/coupon-box/' ),
				array( __( 'Rating Summary', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/rating-summary/' ),
				array( __( 'Recently Viewed Products', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/recently-viewed/' ),
				array( __( 'Size Guide', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/size-guide/' ),
				array( __( 'Stock Indicator', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/stock-indicator/' ),
				array( __( 'Lowest Price', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/lowest-price/' ),
				array( __( 'Order Tracking', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/order-tracking/' ),
				array( __( 'Payment QR Code', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/payment-qr/' ),
				array( __( 'Product Tabs', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/product-tabs/' ),
				array( __( 'Free Shipping Progress', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/shipping-bar/' ),
				array( __( 'Wishlist Button', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/wishlist-button/' ),
				array( __( 'Wishlist', 'envo-blocks' ), $envo_blocks_docs . 'shop-blocks/wishlist/' ),
			),
		),
		array(
			'title' => __( 'Content blocks', 'envo-blocks' ),
			'icon'  => 'layout',
			'links' => array(
				array( __( 'Advanced Heading', 'envo-blocks' ), $envo_blocks_docs . 'content-blocks/advanced-heading/' ),
				array( __( 'Pricing Table', 'envo-blocks' ), $envo_blocks_docs . 'content-blocks/pricing-table/' ),
				array( __( 'Video Popup', 'envo-blocks' ), $envo_blocks_docs . 'content-blocks/video-popup/' ),
				array( __( 'Map', 'envo-blocks' ), $envo_blocks_docs . 'content-blocks/map/' ),
				array( __( 'Announcement Bar', 'envo-blocks' ), $envo_blocks_docs . 'content-blocks/announcement-bar/' ),
				array( __( 'Countdown', 'envo-blocks' ), $envo_blocks_docs . 'content-blocks/countdown/' ),
				array( __( 'Opening Hours', 'envo-blocks' ), $envo_blocks_docs . 'content-blocks/opening-hours/' ),
				array( __( 'Entrance animations', 'envo-blocks' ), $envo_blocks_docs . 'content-blocks/entrance-animations/' ),
			),
		),
		array(
			'title' => __( 'Store features', 'envo-blocks' ),
			'icon'  => 'check',
			'links' => array(
				array( __( 'PDF Invoices', 'envo-blocks' ), $envo_blocks_docs . 'store-features/invoices/' ),
			),
		),
		array(
			'title' => __( 'Help', 'envo-blocks' ),
			'icon'  => 'sos',
			'links' => array(
				array( __( 'Frequently asked questions', 'envo-blocks' ), $envo_blocks_docs . 'faq/' ),
				array( __( 'All documentation', 'envo-blocks' ), $envo_blocks_docs ),
				array( __( 'Support forum on WordPress.org', 'envo-blocks' ), 'https://wordpress.org/support/plugin/envo-blocks/' ),
			),
		),
	),

	// Blocks of Envo Blocks PRO, grouped as in its docs: block folder => array( title, description, guide path, demo slug ).
	// Shown on the Blocks, Welcome, Guides and PRO pages – also without PRO, as an offer. A 5th item 'feature' marks a
	// feature that is not a block (no locked teaser block in the inserter).
	'pro_groups' => array(
		array(
			'title'  => __( 'Product blocks', 'envo-blocks' ),
			'icon'   => 'products',
			'blocks' => array(
				'products-carousel' => array( __( 'Products Carousel', 'envo-blocks' ), __( 'Slider / carousel of WooCommerce products using the default WooCommerce product look.', 'envo-blocks' ), 'product-blocks/products-carousel', 'products-carousel' ),
				'products-tabs' => array( __( 'Products Tabs', 'envo-blocks' ), __( 'Tabs with WooCommerce products: new, featured, on sale, best sellers or a category. Each tab shows a grid or a carousel.', 'envo-blocks' ), 'product-blocks/products-tabs', 'products-tabs' ),
				'product-list' => array( __( 'Product List', 'envo-blocks' ), __( 'A compact widget-like list of products in columns and rows: small image on the left, title, price and button next to it.', 'envo-blocks' ), 'product-blocks/product-list', 'product-list' ),
				'product-mosaic' => array( __( 'Product Mosaic', 'envo-blocks' ), __( 'WooCommerce products as image tiles in aligned mosaic layouts, with the title, description, price and button placed over the image.', 'envo-blocks' ), 'product-blocks/product-mosaic', 'product-mosaic' ),
				'product-split' => array( __( 'Product Split', 'envo-blocks' ), __( 'A selected WooCommerce product in two halves: title, short description, price and button on one side, the product image (or your own image) on the other.', 'envo-blocks' ), 'product-blocks/product-split', 'product-split' ),
				'product-deal' => array( __( 'Product Deal', 'envo-blocks' ), __( 'A product on sale with a countdown to the end of the offer and a stock progress bar.', 'envo-blocks' ), 'product-blocks/product-deal', 'product-deal' ),
				'product-showcase' => array( __( 'Product Showcase', 'envo-blocks' ), __( 'One product at a time with a large image: a counter with a progress line, title, price, description, quantity with Add to cart and a link to the product. Up and down arrows slide to the next product.', 'envo-blocks' ), 'product-blocks/product-showcase', 'product-showcase' ),
				'product-spotlight' => array( __( 'Product Spotlight', 'envo-blocks' ), __( 'Product carousel with the featured product\'s title, description, price and button on the left and the product images on the right: the active one in the middle, the others half hidden behind it. Full-width background, contained carousel.', 'envo-blocks' ), 'product-blocks/product-spotlight', 'product-spotlight' ),
				'product-vertical-slider' => array( __( 'Product Vertical Slider', 'envo-blocks' ), __( 'WooCommerce products that slide up and down, one at a time: product image with the title, description, price and button.', 'envo-blocks' ), 'product-blocks/product-vertical-slider', 'product-vertical-slider' ),
				'bestsellers' => array( __( 'Bestsellers Ranking', 'envo-blocks' ), __( 'A numbered ranking of the best selling products with image, price, rating and number of sold units.', 'envo-blocks' ), 'product-blocks/bestsellers', 'bestsellers' ),
				'shop-the-look' => array( __( 'Shop the Look', 'envo-blocks' ), __( 'An image with pins. A pin opens a card of a WooCommerce product with price and add to cart.', 'envo-blocks' ), 'product-blocks/shop-the-look', 'shop-the-look' ),
				'reviews-carousel' => array( __( 'Reviews Carousel', 'envo-blocks' ), __( 'Slider of customer reviews of your WooCommerce products: stars, text, author and product.', 'envo-blocks' ), 'product-blocks/reviews-carousel', 'reviews-carousel' ),
				'load-more' => array( __( 'Load More Products', 'envo-blocks' ), __( 'A Load more button or infinite scroll instead of page numbers: the next products are added to the list without leaving the page. In a Product Collection, or for the shop pages from the settings.', 'envo-blocks' ), 'product-blocks/load-more', 'load-more' ),
			),
		),
		array(
			'title'  => __( 'Category and brand blocks', 'envo-blocks' ),
			'icon'   => 'category',
			'blocks' => array(
				'categories-carousel' => array( __( 'Product Categories Carousel', 'envo-blocks' ), __( 'Slider / carousel of WooCommerce product categories using the default WooCommerce category look.', 'envo-blocks' ), 'category-blocks/categories-carousel', 'categories-carousel' ),
				'category-mosaic' => array( __( 'Category Mosaic', 'envo-blocks' ), __( 'Product categories as image tiles in aligned mosaic layouts: equal tiles, two wide and four squares, large and small tiles, and more.', 'envo-blocks' ), 'category-blocks/category-mosaic', 'category-mosaic' ),
				'category-banners' => array( __( 'Category Banners', 'envo-blocks' ), __( 'Category tiles in mosaic layouts with a badge showing the best discount in the category ("Up to -40%") and a button.', 'envo-blocks' ), 'category-blocks/category-banners', 'category-banners' ),
				'category-split' => array( __( 'Category Split', 'envo-blocks' ), __( 'A WooCommerce category with its image and title on one side and products on the other side.', 'envo-blocks' ), 'category-blocks/category-split', 'category-split' ),
				'category-showcase' => array( __( 'Category Showcase', 'envo-blocks' ), __( 'A list of categories on one side and the products of the chosen category on the other, as tiles over their images.', 'envo-blocks' ), 'category-blocks/category-showcase', 'category-showcase' ),
				'brands-carousel' => array( __( 'Brands Carousel', 'envo-blocks' ), __( 'A carousel of brand logos: WooCommerce brands or your own logos with links.', 'envo-blocks' ), 'category-blocks/brands-carousel', 'brands-carousel' ),
			),
		),
		array(
			'title'  => __( 'Header and navigation', 'envo-blocks' ),
			'icon'   => 'menu',
			'blocks' => array(
				'menu' => array( __( 'Mega Menu', 'envo-blocks' ), __( 'A navigation menu whose items open large panels with any blocks: columns of links, images, buttons, products. Items can also show a panel of a product category. Accordion or side drawer on mobile.', 'envo-blocks' ), 'header-blocks/mega-menu', 'menu' ),
				'mega-menu' => array( __( 'Category Mega Menu', 'envo-blocks' ), __( 'A menu of your product categories with large panels of subcategories, images and a promo card. Horizontal bar or a vertical "All categories" menu; an accordion on mobile.', 'envo-blocks' ), 'header-blocks/category-mega-menu', 'mega-menu' ),
				'product-search' => array( __( 'Product Search (AJAX)', 'envo-blocks' ), __( 'A product search field that shows matching products with image, title and price while typing, without loading a new page.', 'envo-blocks' ), 'header-blocks/product-search', 'product-search' ),
				'sticky-cart' => array( __( 'Sticky Cart', 'envo-blocks' ), __( 'A cart icon with the number of items, e.g. in the header. When it scrolls out of view, a floating cart button appears in a corner of the screen; both open a cart drawer with the products, quantities, subtotal and the checkout.', 'envo-blocks' ), 'header-blocks/sticky-cart', 'sticky-cart' ),
				'off-canvas' => array( __( 'Off-Canvas Panel', 'envo-blocks' ), __( 'A button that opens a side panel with any blocks – a menu, product filters, contact details. Only on phones and tablets if you like; on wider screens the content then stays in the page.', 'envo-blocks' ), 'header-blocks/off-canvas', 'off-canvas' ),
				'sticky-account' => array( __( 'Sticky Account', 'envo-blocks' ), __( 'An account icon, e.g. in the header. When it scrolls out of view, a floating account button appears; both open a panel with the account menu, or a login form for guests. Stacks next to the Sticky Cart button instead of covering it.', 'envo-blocks' ), 'header-blocks/sticky-account', 'sticky-account' ),
				'sticky-wishlist' => array( __( 'Sticky Wishlist', 'envo-blocks' ), __( 'A wishlist icon with the count, e.g. in the header. When it scrolls out of view, a floating button appears; both open a drawer with the saved products. With it on the page, the product images in lists get a heart. Builds on the free Wishlist blocks. Stacks next to the Sticky Cart and Sticky Account buttons.', 'envo-blocks' ), 'header-blocks/sticky-wishlist', 'wishlist' ),
			),
		),
		array(
			'title'  => __( 'Wishlist, compare and product page', 'envo-blocks' ),
			'icon'   => 'heart',
			'blocks' => array(
				'compare-button' => array( __( 'Compare Button', 'envo-blocks' ), __( 'A Compare button for the product page or a product template: the product goes to the compare bar, and the visitor compares up to four products side by side.', 'envo-blocks' ), 'shop-features/compare-button', 'compare' ),
				'compare-icon' => array( __( 'Compare Icon', 'envo-blocks' ), __( 'A compare icon with the number of products in the comparison, e.g. in the header. A click opens the comparison window. With it on the page, the product images in lists get compare buttons.', 'envo-blocks' ), 'shop-features/compare-icon', 'compare' ),
				'comparison-table' => array( __( 'Comparison Table', 'envo-blocks' ), __( 'A product comparison table: chosen products with price, rating, availability, attributes and Add to cart, one of them highlighted – or the visitor\'s own comparison on a Compare page.', 'envo-blocks' ), 'shop-features/comparison-table', 'compare' ),
				'variation-swatches' => array( __( 'Variation Swatches', 'envo-blocks' ), __( 'Color, image or size swatches of a variable product for product templates (Product Collection): hovering a color shows that variation\'s image, a click opens the product with the value selected.', 'envo-blocks' ), 'shop-features/variation-swatches', 'variation-swatches' ),
				'swatches-filter' => array( __( 'Variation Swatches Filter', 'envo-blocks' ), __( 'Filter products by color, size or any attribute shown as swatches – in the shop and the categories, or anywhere else as a "shop by color" link list.', 'envo-blocks' ), 'shop-features/swatches-filter', 'swatches-filter' ),
				'product-gallery' => array( __( 'Product Gallery (Vertical)', 'envo-blocks' ), __( 'The product images with the thumbnails in a column next to the main image or in a row below: swipe, zoom, full screen and the image of the chosen color. Plus a small image gallery on the product cards of lists.', 'envo-blocks' ), 'shop-features/product-gallery', 'product-gallery' ),
				'quick-view-button' => array( __( 'Quick View', 'envo-blocks' ), __( 'An eye button on the product images and a Quick View Button that open a window with the gallery, price, variations with swatches and Add to cart – without leaving the page.', 'envo-blocks' ), 'shop-features/quick-view', 'quick-view' ),
				'product-badges' => array( __( 'Product Badges', 'envo-blocks' ), __( 'Automatic labels on the product images – the discount in percent, Hot, Only 3 left, Sold out, New, Featured – and your own label per product. The block shows them on the product page.', 'envo-blocks' ), 'shop-features/product-badges', 'product-badges' ),
				'sticky-add-to-cart' => array( __( 'Sticky Add to Cart', 'envo-blocks' ), __( 'A bar with the product, its price and the Add to cart button that appears when the visitor scrolls past the product\'s own button. Place it in the single product template or on any page with a chosen product.', 'envo-blocks' ), 'shop-features/sticky-add-to-cart', 'sticky-add-to-cart' ),
				'checkout-steps' => array( __( 'Checkout Steps', 'envo-blocks' ), __( 'The WooCommerce checkout in steps – details, shipping, payment – with a progress bar, Continue and Back buttons and a summary of the finished steps. For the Checkout block and the classic checkout.', 'envo-blocks' ), 'shop-features/checkout-steps', 'checkout-steps' ),
				'popups' => array( __( 'Popups', 'envo-blocks' ), __( 'Popups with any blocks – a window, a slide-in, a bar or the full screen – after some seconds, after scrolling, when leaving, after adding to the cart or with a link. Site-wide popups with rules for pages, visitors and how often.', 'envo-blocks' ), 'shop-features/popups', 'popups' ),
				'sale-countdown-bar' => array( __( 'Sale Countdown Bar', 'envo-blocks' ), __( 'A bar with a sale message and a countdown to a date, a time every day or a day of the week, with a coupon code that copies with a click – above the header or floating at the bottom.', 'envo-blocks' ), 'shop-features/sale-countdown-bar', 'sale-countdown-bar' ),
				'bought-together' => array( __( 'Frequently Bought Together', 'envo-blocks' ), __( 'The product with 1–4 products that go with it, check boxes, the total price and one "Add all to cart" button.', 'envo-blocks' ), 'shop-features/bought-together', 'bought-together' ),
				'cart-recommendations' => array( __( 'Cart Recommendations', 'envo-blocks' ), __( 'Products to add from the side cart with one click: the cross-sells of the cart, best sellers, products on sale or the newest – a list, a scrolling row or two columns. For side cart layouts.', 'envo-blocks' ), 'store-layouts/store-layouts-pro', '' ),
				'volume-pricing' => array( __( 'Volume Pricing', 'envo-blocks' ), __( 'Quantity discounts ("3+ pieces: 10 % off") set on the product, shown in a table where a click sets the quantity; the cart charges the lower price.', 'envo-blocks' ), 'shop-features/volume-pricing', 'volume-pricing' ),
				'product-addons' => array( __( 'Product Add-Ons', 'envo-blocks' ), __( 'Extra fields before Add to cart – engraving, gift wrap, a protection plan, accessories, a photo to print – with a price for every choice that the cart really charges. On one product or for whole categories.', 'envo-blocks' ), 'shop-features/product-addons', 'product-addons' ),
				'deals' => array( __( 'Deals & Offers', 'envo-blocks' ), __( 'Discount rules that run your promotions – buy 2 get 1 free, a free gift, a percent off a category, an amount off big orders, with dates and days – and a block that shows the running deals and what is missing.', 'envo-blocks' ), 'shop-features/discount-rules', 'deals' ),
				'quote' => array( __( 'Quote Request', 'envo-blocks' ), __( 'Request a quote for catalog products: a quote list with quantities and a form; the request becomes an order you answer with prices and a payment link.', 'envo-blocks' ), 'shop-features/request-a-quote', 'request-a-quote' ),
				'price-chart' => array( __( 'Price History Chart', 'envo-blocks' ), __( 'The price of a product in the last 30–365 days as a chart, with the current, lowest and highest price, from the real price history; follows the chosen variation.', 'envo-blocks' ), 'shop-features/price-chart', 'price-alerts' ),
				'price-alert' => array( __( 'Price Alert', 'envo-blocks' ), __( '"Notify me when the price drops": visitors leave their e-mail and get one e-mail when the price of the product or the chosen variation goes down.', 'envo-blocks' ), 'shop-features/price-alert', 'price-alerts' ),
				'stock-alert' => array( __( 'Back in Stock', 'envo-blocks' ), __( '"Notify me when it is available": on a product that is out of stock visitors leave their e-mail and get one e-mail when it is in stock again – also for the chosen variation, with a list of the most wanted products.', 'envo-blocks' ), 'shop-features/back-in-stock', 'back-in-stock' ),
				'wholesale-form' => array( __( 'Wholesale Prices', 'envo-blocks' ), __( 'Own prices for business customers – a wholesale price per product or a percent off the shop or a category, customer discounts and a form where customers apply for a wholesale account.', 'envo-blocks' ), 'shop-features/wholesale-prices', 'wholesale-prices' ),
				'filter-panel' => array( __( 'Product Filters PRO & Filter Panel', 'envo-blocks' ), __( 'The product filters without reloading the page, brand and "on sale" filters, swatches, buttons and dropdowns, and the Filter Panel: the filters in a drawer on phones with "Show N products".', 'envo-blocks' ), 'shop-features/filter-panel', 'product-filters' ),
			),
		),
		array(
			'title'  => __( 'Content blocks', 'envo-blocks' ),
			'icon'   => 'layout',
			'blocks' => array(
				'motion-effects' => array( __( 'Motion Effects', 'envo-blocks' ), __( 'Scrolling and mouse effects for any block: vertical and horizontal parallax, transparency, blur, rotate and scale while scrolling, a block that follows the mouse and a 3D tilt.', 'envo-blocks' ), 'content-blocks/motion-effects', 'motion-effects', 'feature' ),
				'display-conditions' => array( __( 'Display Conditions', 'envo-blocks' ), __( 'Show or hide any block by rules: signed in or guest, user role, device, browser, date, days and time, URL parameter, the site the visitor came from, a cookie, new or returning visitor, the cart and the orders of the customer. Works on cached pages.', 'envo-blocks' ), 'content-blocks/display-conditions', 'display-conditions', 'feature' ),
				'slider' => array( __( 'Content Slider', 'envo-blocks' ), __( 'A slider where every slide has its own background and can hold any blocks. Full-width backgrounds with centered content.', 'envo-blocks' ), 'content-blocks/slider', 'slider' ),
				'content-tabs' => array( __( 'Content Tabs', 'envo-blocks' ), __( 'Horizontal or vertical tabs; every tab holds any blocks.', 'envo-blocks' ), 'content-blocks/content-tabs', 'content-tabs' ),
				'content-switcher' => array( __( 'Content Switcher', 'envo-blocks' ), __( 'A switch between two or more versions of content, e.g. annual and lifetime pricing. Every option holds any blocks.', 'envo-blocks' ), 'content-blocks/content-switcher', 'content-switcher' ),
				'image-compare' => array( __( 'Before / After', 'envo-blocks' ), __( 'Two images on top of each other with a slider between them, to compare before and after.', 'envo-blocks' ), 'content-blocks/image-compare', 'image-compare' ),
				'layered-image' => array( __( 'Image with Background', 'envo-blocks' ), __( 'A front image (ideally with a transparent background) over a background image or a decorative shape. The layers move slightly in space with the mouse.', 'envo-blocks' ), 'content-blocks/layered-image', 'layered-image' ),
			),
		),
	),

	// PRO blocks shown as pictures on the PRO page (previews in assets/images/pro/sections/).
	'pro_gallery' => array( 'vyra-hero', 'bitbox-products-tabs', 'tidal-hero', 'petal-hero', 'teklo-products-carousel', 'gizmo-categories', 'redline-hero', 'bitbox-category-showcase', 'tidal-reviews' ),
);
