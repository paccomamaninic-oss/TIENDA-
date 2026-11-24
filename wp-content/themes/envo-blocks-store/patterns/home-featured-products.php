<?php
/**
 * Title: Home: featured products
 * Slug: envo-blocks-store/home-featured-products
 * Categories: envo-blocks-store, woo-commerce
 * Keywords: products, featured, shop
 * Viewport width: 1400
 * Description: A photo promotion card next to four featured products.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","className":"eb-section eb-showcase","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-section eb-showcase" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"20px","left":"20px"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"28%"} -->
<div class="wp-block-column" style="flex-basis:28%"><!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/promo-woman.webp' ) ); ?>","alt":"<?php esc_attr_e( 'Woman with dark hair in a black top', 'envo-blocks-store' ); ?>","dimRatio":50,"overlayColor":"primary-dark","isUserOverlayColor":true,"focalPoint":{"x":0.45,"y":0.3},"minHeight":420,"contentPosition":"bottom left","isDark":true,"className":"eb-promo","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"32px","bottom":"32px","left":"32px","right":"32px"}}}} -->
<div class="wp-block-cover is-dark has-custom-content-position is-position-bottom-left eb-promo" style="border-radius:16px;padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px;min-height:420px"><img class="wp-block-cover__image-background" alt="<?php esc_attr_e( 'Woman with dark hair in a black top', 'envo-blocks-store' ); ?>" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/promo-woman.webp' ) ); ?>" style="object-position:45% 30%" data-object-fit="cover" data-object-position="45% 30%"/><span aria-hidden="true" class="wp-block-cover__background has-primary-dark-background-color has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"style":{"typography":{"fontSize":"2rem","lineHeight":"1.1"}},"textColor":"background"} -->
<h2 class="wp-block-heading has-background-color has-text-color" style="font-size:2rem;line-height:1.1"><?php esc_html_e( 'Featured products', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"background","fontSize":"extra-small"} -->
<p class="has-background-color has-text-color has-extra-small-font-size"><?php esc_html_e( 'Hand-picked favourites our customers keep coming back for.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"background","textColor":"foreground"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Shop now', 'envo-blocks-store' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"72%"} -->
<div class="wp-block-column" style="flex-basis:72%"><?php if ( class_exists( 'WooCommerce' ) ) : ?><!-- wp:woocommerce/product-collection {"queryId":11,"query":{"perPage":4,"pages":1,"offset":0,"postType":"product","order":"desc","orderBy":"date","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"featured":true,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/featured","hideControls":["inherit","featured"],"queryContextIncludes":["collection"],"className":"eb-products"} -->
<div class="wp-block-woocommerce-product-collection eb-products"><!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->
<!-- /wp:woocommerce/product-image -->

<!-- wp:post-title {"textAlign":"center","level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->

<!-- wp:woocommerce/product-rating {"isDescendentOfQueryLoop":true,"textAlign":"center"} /-->

<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"textAlign":"center"} /-->

<!-- wp:woocommerce/product-button {"textAlign":"center","isDescendentOfQueryLoop":true,"fontSize":"small"} /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection --><?php else : ?>
<?php
	envo_blocks_store_sample_products(
		array(
			array( 'image' => 'hero-headphones.webp', 'alt' => __( 'White over-ear headphones', 'envo-blocks-store' ), 'name' => __( 'Studio Headphones', 'envo-blocks-store' ), 'price' => __( '$79.00', 'envo-blocks-store' ), 'old_price' => __( '$99.00', 'envo-blocks-store' ), 'rating' => 5 ),
			array( 'image' => 'banner-handbag.webp', 'alt' => __( 'Black leather handbag', 'envo-blocks-store' ), 'name' => __( 'City Leather Handbag', 'envo-blocks-store' ), 'price' => __( '$129.00', 'envo-blocks-store' ), 'rating' => 4 ),
			array( 'image' => 'banner-sneakers.webp', 'alt' => __( 'Pair of white sparkly sneakers', 'envo-blocks-store' ), 'name' => __( 'Sparkle Sneakers', 'envo-blocks-store' ), 'price' => __( '$64.00', 'envo-blocks-store' ), 'old_price' => __( '$79.00', 'envo-blocks-store' ), 'rating' => 5 ),
			array( 'image' => 'banner-sunglasses.webp', 'alt' => __( 'Rimless sunglasses with brown gradient lenses', 'envo-blocks-store' ), 'name' => __( 'Rimless Sunglasses', 'envo-blocks-store' ), 'price' => __( '$29.00', 'envo-blocks-store' ), 'rating' => 4 ),
		)
	);
	?>
<?php endif; ?></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
