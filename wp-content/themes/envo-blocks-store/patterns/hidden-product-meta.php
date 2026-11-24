<?php
/**
 * Title: Product SKU, categories and tags
 * Slug: envo-blocks-store/hidden-product-meta
 * Inserter: no
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:woocommerce/product-meta {"className":"sky-product__meta"} -->
<div class="wp-block-woocommerce-product-meta sky-product__meta"><!-- wp:group {"style":{"spacing":{"blockGap":"0.25rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:woocommerce/product-sku /-->

<!-- wp:post-terms {"term":"product_cat","prefix":"<?php echo esc_attr__( 'Categories: ', 'envo-blocks-store' ); ?>"} /-->

<!-- wp:post-terms {"term":"product_tag","prefix":"<?php echo esc_attr__( 'Tags: ', 'envo-blocks-store' ); ?>"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:woocommerce/product-meta -->
