<?php
/**
 * Title: Shop sidebar
 * Slug: envo-blocks-store/hidden-shop-sidebar
 * Inserter: no
 *
 * Used by the shop and product templates with a sidebar (WooCommerce only).
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:group {"tagName":"aside","className":"eb-sidebar","style":{"spacing":{"blockGap":"20px"}},"layout":{"type":"default"}} -->
<aside class="wp-block-group eb-sidebar"><!-- wp:group {"className":"eb-widget","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:search {"label":"<?php echo esc_attr_x( 'Search products', 'Search form label', 'envo-blocks-store' ); ?>","showLabel":false,"placeholder":"<?php echo esc_attr_x( 'Search products…', 'Search input placeholder', 'envo-blocks-store' ); ?>","buttonText":"<?php echo esc_attr_x( 'Search', 'Search button text', 'envo-blocks-store' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"post_type":"product"},"namespace":"woocommerce/product-search"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-widget","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:heading {"level":2,"className":"eb-widget__title"} -->
<h2 class="wp-block-heading eb-widget__title"><?php esc_html_e( 'Categories', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-categories {"hasCount":true,"className":"eb-widget__list"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-widget","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:heading {"level":2,"className":"eb-widget__title"} -->
<h2 class="wp-block-heading eb-widget__title"><?php esc_html_e( 'On sale', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-collection {"queryId":21,"query":{"perPage":3,"pages":1,"offset":0,"postType":"product","order":"desc","orderBy":"date","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":true,"woocommerceStockStatus":["instock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false},"tagName":"div","displayLayout":{"type":"list","columns":1,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/on-sale","hideControls":["inherit","on-sale"],"queryContextIncludes":["collection"],"className":"eb-mini-products"} -->
<div class="wp-block-woocommerce-product-collection eb-mini-products"><!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true,"width":"64px","height":"64px"} /-->

<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->

<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-widget eb-widget--promo","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"28px","bottom":"28px","left":"24px","right":"24px"},"blockGap":"8px"},"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"backgroundColor":"primary","textColor":"background","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget eb-widget--promo has-background-color has-primary-background-color has-text-color has-background has-link-color" style="border-radius:16px;padding-top:28px;padding-right:24px;padding-bottom:28px;padding-left:24px"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size" style="font-weight:600"><?php esc_html_e( 'Free delivery', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"1.375rem","lineHeight":"1.25"}},"textColor":"background"} -->
<h2 class="wp-block-heading has-background-color has-text-color" style="font-size:1.375rem;line-height:1.25"><?php esc_html_e( 'On every order over $50', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Free returns within 30 days.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></aside>
<!-- /wp:group -->
