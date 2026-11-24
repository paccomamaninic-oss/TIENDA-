<?php
/**
 * Title: Product with sidebar
 * Slug: envo-blocks-store/template-product-sidebar
 * Template Types: single-product
 * Inserter: no
 * Viewport width: 1400
 * Description: Single product page with the shop sidebar (search, categories, products on sale).
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","className":"sky-product","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|70"},"margin":{"top":"0"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group sky-product" style="margin-top:0;padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:columns {"align":"wide","className":"eb-with-sidebar","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|50","left":"48px"}}}} -->
<div class="wp-block-columns alignwide eb-with-sidebar"><!-- wp:column {"width":"72%"} -->
<div class="wp-block-column" style="flex-basis:72%"><!-- wp:woocommerce/breadcrumbs {"align":"","style":{"typography":{"textTransform":"uppercase","fontSize":"0.75rem"},"elements":{"link":{"color":{"text":"var:preset|color|muted"}}}}} /-->

<!-- wp:woocommerce/store-notices /-->

<!-- wp:columns {"className":"sky-product__top","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"2.5rem"},"margin":{"top":"var:preset|spacing|30"}}}} -->
<div class="wp-block-columns sky-product__top" style="margin-top:var(--wp--preset--spacing--30)"><!-- wp:column {"width":"42%"} -->
<div class="wp-block-column" style="flex-basis:42%"><!-- wp:woocommerce/product-image-gallery /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"58%","className":"sky-product__summary"} -->
<div class="wp-block-column sky-product__summary" style="flex-basis:58%"><!-- wp:post-title {"level":1,"style":{"typography":{"fontSize":"1.875rem","fontWeight":"600","lineHeight":"1.2","letterSpacing":"-0.01em"},"spacing":{"margin":{"top":"0","bottom":"0.25rem"}}},"__woocommerceNamespace":"woocommerce/product-query/product-title"} /-->

<!-- wp:woocommerce/product-rating {"isDescendentOfSingleProductTemplate":true} /-->

<!-- wp:woocommerce/product-price {"isDescendentOfSingleProductTemplate":true,"style":{"typography":{"fontSize":"1.5rem"},"spacing":{"margin":{"top":"1rem","bottom":"1rem"}}}} /-->

<!-- wp:post-excerpt {"textColor":"muted","fontSize":"extra-small","__woocommerceNamespace":"woocommerce/product-query/product-summary","excerptLength":100} /-->

<!-- wp:pattern {"slug":"envo-blocks-store/hidden-product-meta"} /-->

<!-- wp:woocommerce/add-to-cart-form {"quantitySelectorStyle":"stepper","className":"sky-product__cart"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:woocommerce/product-details {"align":"","hideTabTitle":true,"className":"sky-product__tabs","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} /-->

<!-- wp:pattern {"slug":"envo-blocks-store/hidden-related-products"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"28%"} -->
<div class="wp-block-column" style="flex-basis:28%"><!-- wp:pattern {"slug":"envo-blocks-store/hidden-shop-sidebar"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
