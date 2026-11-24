<?php
/**
 * Title: Related products
 * Slug: envo-blocks-store/hidden-related-products
 * Inserter: no
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:woocommerce/product-collection {"queryId":1,"query":{"perPage":4,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"title","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false,"relatedBy":{"categories":true,"tags":true}},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/related","hideControls":["inherit"],"queryContextIncludes":["collection"],"className":"sky-product__related","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
<div class="wp-block-woocommerce-product-collection sky-product__related"><!-- wp:heading {"className":"sky-product__related-title","style":{"typography":{"fontSize":"1rem","fontWeight":"600","textTransform":"uppercase"},"spacing":{"margin":{"bottom":"1.5rem"}}}} -->
<h2 class="wp-block-heading sky-product__related-title" style="margin-bottom:1.5rem;font-size:1rem;font-weight:600;text-transform:uppercase"><?php esc_html_e( 'Related products', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"right"} /-->
<!-- /wp:woocommerce/product-image -->

<!-- wp:post-terms {"term":"product_cat","textAlign":"center","className":"sky-card__cats","style":{"typography":{"fontSize":"0.6875rem","textTransform":"uppercase"},"spacing":{"margin":{"top":"0.75rem","bottom":"0"}}},"textColor":"muted"} /-->

<!-- wp:post-title {"textAlign":"center","level":3,"isLink":true,"style":{"spacing":{"margin":{"bottom":"0.25rem","top":"0.25rem"}}},"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->

<!-- wp:woocommerce/product-rating {"isDescendentOfQueryLoop":true,"textAlign":"center"} /-->

<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"textAlign":"center"} /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection -->
