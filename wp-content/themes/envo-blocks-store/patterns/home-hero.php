<?php
/**
 * Title: Home: hero with categories
 * Slug: envo-blocks-store/home-hero
 * Categories: envo-blocks-store, banner
 * Keywords: hero, banner, categories, shop
 * Viewport width: 1400
 * Description: A category list next to a large promotion and two smaller product tiles.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","className":"eb-section eb-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-section eb-hero" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"20px","left":"20px"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"23%","className":"eb-hero__cats"} -->
<div class="wp-block-column eb-hero__cats" style="flex-basis:23%"><!-- wp:group {"className":"eb-cats","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"12px","bottom":"16px","left":"12px","right":"12px"},"blockGap":"8px"}},"backgroundColor":"surface","layout":{"type":"default"}} -->
<div class="wp-block-group eb-cats has-surface-background-color has-background" style="border-radius:16px;padding-top:12px;padding-right:12px;padding-bottom:16px;padding-left:12px"><!-- wp:heading {"className":"eb-cats__title","style":{"border":{"radius":"10px"},"spacing":{"padding":{"top":"12px","bottom":"12px","left":"16px","right":"16px"}},"typography":{"fontSize":"1rem","fontWeight":"600"}},"backgroundColor":"primary","textColor":"background"} -->
<h2 class="wp-block-heading eb-cats__title has-background-color has-primary-background-color has-text-color has-background" style="border-radius:10px;padding-top:12px;padding-right:16px;padding-bottom:12px;padding-left:16px;font-size:1rem;font-weight:600"><?php esc_html_e( 'Categories', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<?php if ( class_exists( 'WooCommerce' ) ) : ?>
<!-- wp:woocommerce/product-categories {"hasCount":false,"className":"eb-cats__list"} /-->
<?php else : ?>
<!-- wp:list {"className":"eb-cats__list eb-cats__static"} -->
<ul class="wp-block-list eb-cats__list eb-cats__static"><!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'New arrivals', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Clothing', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Shoes', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Bags', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Accessories', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Audio', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Home & kitchen', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Beauty', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Sports', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Sale', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->
<?php endif; ?>

<!-- wp:paragraph {"className":"eb-cats__all","fontSize":"extra-small"} -->
<p class="eb-cats__all has-extra-small-font-size"><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'All categories →', 'envo-blocks-store' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"51%"} -->
<div class="wp-block-column" style="flex-basis:51%"><!-- wp:group {"className":"eb-hero__main","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"0","bottom":"0","left":"48px","right":"0"}}},"backgroundColor":"tint","layout":{"type":"default"}} -->
<div class="wp-block-group eb-hero__main has-tint-background-color has-background" style="border-radius:16px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:48px"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"0","left":"0"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"52%","style":{"spacing":{"padding":{"top":"48px","bottom":"48px"}}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:48px;padding-bottom:48px;flex-basis:52%"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"textColor":"primary","fontSize":"extra-small"} -->
<p class="has-primary-color has-text-color has-extra-small-font-size" style="font-weight:600"><?php esc_html_e( 'Up to 25% off', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(1.875rem, 2.8vw, 2.5rem)","lineHeight":"1.1","fontWeight":"700"},"spacing":{"margin":{"top":"0.5rem","bottom":"1rem"}}}} -->
<h1 class="wp-block-heading" style="margin-top:0.5rem;margin-bottom:1rem;font-size:clamp(1.875rem, 2.8vw, 2.5rem);font-weight:700;line-height:1.1"><?php esc_html_e( 'Summer Shopping Week', 'envo-blocks-store' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"spacing":{"margin":{"bottom":"1.5rem"}}}} -->
<p style="margin-bottom:1.5rem"><?php esc_html_e( 'Fresh deals on everyday favourites, from headphones and gadgets to bags and sneakers.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"foreground","textColor":"background"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-foreground-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Shop now', 'envo-blocks-store' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"48%","className":"eb-hero__figure"} -->
<div class="wp-block-column is-vertically-aligned-center eb-hero__figure" style="flex-basis:48%"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"eb-hero__image"} -->
<figure class="wp-block-image size-full eb-hero__image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero-headphones.webp' ) ); ?>" alt="<?php esc_attr_e( 'White over-ear headphones', 'envo-blocks-store' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"26%","className":"eb-hero__tiles"} -->
<div class="wp-block-column eb-hero__tiles" style="flex-basis:26%"><!-- wp:group {"className":"eb-tile","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"16px","left":"24px","right":"24px"},"blockGap":"4px"}},"backgroundColor":"secondary","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group eb-tile has-secondary-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:16px;padding-left:24px"><!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Handbags', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.375rem"}}} -->
<h3 class="wp-block-heading" style="font-size:1.375rem"><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Up to 20% off', 'envo-blocks-store' ); ?></a></h3>
<!-- /wp:heading -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"eb-tile__image"} -->
<figure class="wp-block-image size-full eb-tile__image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/banner-handbag.webp' ) ); ?>" alt="<?php esc_attr_e( 'Black leather handbag', 'envo-blocks-store' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-tile","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"16px","left":"24px","right":"24px"},"blockGap":"4px"}},"backgroundColor":"surface","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group eb-tile has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:16px;padding-left:24px"><!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Sneakers', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.375rem"}}} -->
<h3 class="wp-block-heading" style="font-size:1.375rem"><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'New colours in', 'envo-blocks-store' ); ?></a></h3>
<!-- /wp:heading -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"eb-tile__image"} -->
<figure class="wp-block-image size-full eb-tile__image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/banner-sneakers.webp' ) ); ?>" alt="<?php esc_attr_e( 'Pair of white sparkly sneakers', 'envo-blocks-store' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
