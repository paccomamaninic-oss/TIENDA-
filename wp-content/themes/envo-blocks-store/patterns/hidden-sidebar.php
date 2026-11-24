<?php
/**
 * Title: Sidebar
 * Slug: envo-blocks-store/hidden-sidebar
 * Inserter: no
 *
 * Used by the "Page with sidebar" and "Post with sidebar" templates.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"tagName":"aside","className":"eb-sidebar","style":{"spacing":{"blockGap":"20px"}},"layout":{"type":"default"}} -->
<aside class="wp-block-group eb-sidebar"><!-- wp:group {"className":"eb-widget","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:pattern {"slug":"envo-blocks-store/hidden-search"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-widget","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:heading {"level":2,"className":"eb-widget__title"} -->
<h2 class="wp-block-heading eb-widget__title"><?php esc_html_e( 'About the store', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Everyday favourites for home, style and sound, chosen by a small team and packed with care.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"foreground","textColor":"background","fontSize":"extra-small"} -->
<div class="wp-block-button has-custom-font-size has-extra-small-font-size"><a class="wp-block-button__link has-background-color has-foreground-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Visit the shop', 'envo-blocks-store' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-widget","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:heading {"level":2,"className":"eb-widget__title"} -->
<h2 class="wp-block-heading eb-widget__title"><?php esc_html_e( 'Latest posts', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:latest-posts {"postsToShow":4,"displayPostDate":true,"displayFeaturedImage":true,"featuredImageSizeSlug":"thumbnail","featuredImageAlign":"left","featuredImageSizeWidth":56,"featuredImageSizeHeight":56,"className":"eb-widget__posts"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-widget","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><?php if ( class_exists( 'WooCommerce' ) ) : ?><!-- wp:heading {"level":2,"className":"eb-widget__title"} -->
<h2 class="wp-block-heading eb-widget__title"><?php esc_html_e( 'Shop by category', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-categories {"hasCount":true,"className":"eb-widget__list"} /--><?php else : ?><!-- wp:heading {"level":2,"className":"eb-widget__title"} -->
<h2 class="wp-block-heading eb-widget__title"><?php esc_html_e( 'Categories', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:categories {"showPostCounts":true,"className":"eb-widget__list"} /--><?php endif; ?></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-widget eb-widget--promo","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"28px","bottom":"28px","left":"24px","right":"24px"},"blockGap":"8px"},"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"backgroundColor":"primary","textColor":"background","layout":{"type":"default"}} -->
<div class="wp-block-group eb-widget eb-widget--promo has-background-color has-primary-background-color has-text-color has-background has-link-color" style="border-radius:16px;padding-top:28px;padding-right:24px;padding-bottom:28px;padding-left:24px"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size" style="font-weight:600"><?php esc_html_e( 'Free delivery', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"1.375rem","lineHeight":"1.25"}},"textColor":"background"} -->
<h2 class="wp-block-heading has-background-color has-text-color" style="font-size:1.375rem;line-height:1.25"><?php esc_html_e( 'On every order over $50', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Start shopping →', 'envo-blocks-store' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></aside>
<!-- /wp:group -->
