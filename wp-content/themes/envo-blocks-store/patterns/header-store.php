<?php
/**
 * Title: Header (store, three rows)
 * Slug: envo-blocks-store/header-store
 * Categories: header
 * Block Types: core/template-part/header
 *
 * A notice bar, a row with the logo, trending links, product search and the account and
 * cart icons, and a menu row with social icons. WooCommerce's automatic insertion of the
 * account and cart icons next to the navigation is switched off with ignoredHookedBlocks.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","className":"eb-header-store","style":{"spacing":{"padding":{"top":"0","bottom":"0"},"blockGap":"0"}},"backgroundColor":"background","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull eb-header-store has-background-background-color has-background" style="padding-top:0;padding-bottom:0"><!-- wp:group {"align":"full","className":"eb-topbar","style":{"spacing":{"padding":{"top":"8px","bottom":"8px"}}},"backgroundColor":"secondary","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-topbar has-secondary-background-color has-background" style="padding-top:8px;padding-bottom:8px"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide"><!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Free delivery on orders over $50', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'New arrivals every week', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Support: Mon – Fri, 9:00 – 18:00', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}},"spacing":{"padding":{"top":"20px","bottom":"20px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:20px;padding-bottom:20px"><!-- wp:group {"align":"wide","className":"eb-header-store__main","style":{"spacing":{"blockGap":"32px"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide eb-header-store__main"><!-- wp:group {"className":"sky-brand","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group sky-brand"><!-- wp:site-logo {"width":40,"shouldSyncIcon":false,"className":"sky-logo"} /-->

<!-- wp:site-title {"level":0,"className":"sky-site-title","style":{"typography":{"fontSize":"1.5rem","fontWeight":"700","lineHeight":"1.2"},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"eb-header-store__search","style":{"spacing":{"blockGap":"6px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group eb-header-store__search"><!-- wp:paragraph {"className":"eb-header-store__trending","fontSize":"extra-small"} -->
<p class="eb-header-store__trending has-extra-small-font-size"><?php esc_html_e( 'Trending:', 'envo-blocks-store' ); ?> <a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'New in', 'envo-blocks-store' ); ?></a> // <a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Sale', 'envo-blocks-store' ); ?></a> // <a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Gift ideas', 'envo-blocks-store' ); ?></a></p>
<!-- /wp:paragraph -->

<?php if ( class_exists( 'WooCommerce' ) ) : ?>
<!-- wp:search {"label":"<?php echo esc_attr_x( 'Search products', 'Search form label', 'envo-blocks-store' ); ?>","showLabel":false,"placeholder":"<?php echo esc_attr_x( 'Search products…', 'Search input placeholder', 'envo-blocks-store' ); ?>","buttonText":"<?php echo esc_attr_x( 'Search', 'Search button text', 'envo-blocks-store' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"post_type":"product"},"namespace":"woocommerce/product-search"} /-->
<?php else : ?>
<!-- wp:search {"label":"<?php echo esc_attr_x( 'Search', 'Search form label', 'envo-blocks-store' ); ?>","showLabel":false,"placeholder":"<?php echo esc_attr_x( 'Search…', 'Search input placeholder', 'envo-blocks-store' ); ?>","buttonText":"<?php echo esc_attr_x( 'Search', 'Search button text', 'envo-blocks-store' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true} /-->
<?php endif; ?></div>
<!-- /wp:group -->

<!-- wp:group {"className":"sky-header-shop","style":{"spacing":{"blockGap":"0.75rem"},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}},"textColor":"foreground","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group sky-header-shop has-foreground-color has-text-color has-link-color"><?php if ( class_exists( 'WooCommerce' ) ) : ?><!-- wp:woocommerce/customer-account {"displayStyle":"icon_only","iconStyle":"line","iconClass":"wc-block-customer-account__account-icon","style":{"typography":{"fontSize":"16px"}}} /-->

<!-- wp:woocommerce/mini-cart {"iconColor":{"color":"#222529"},"iconColorValue":"#222529","productCountColor":{"color":"#0088cc"},"productCountColorValue":"#0088cc","style":{"typography":{"fontSize":"16px"}}} /--><?php else : ?><!-- wp:image {"width":"24px","height":"24px","sizeSlug":"full","linkDestination":"custom","className":"eb-header-icon"} -->
<figure class="wp-block-image size-full is-resized eb-header-icon"><a href="<?php echo esc_url( is_user_logged_in() ? get_edit_profile_url() : wp_login_url() ); ?>"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icon-account.svg' ) ); ?>" alt="<?php esc_attr_e( 'My account', 'envo-blocks-store' ); ?>" style="width:24px;height:24px"/></a></figure>
<!-- /wp:image -->

<!-- wp:image {"width":"24px","height":"24px","sizeSlug":"full","linkDestination":"custom","className":"eb-header-icon"} -->
<figure class="wp-block-image size-full is-resized eb-header-icon"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icon-cart.svg' ) ); ?>" alt="<?php esc_attr_e( 'Cart', 'envo-blocks-store' ); ?>" style="width:24px;height:24px"/></a></figure>
<!-- /wp:image --><?php endif; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}},"spacing":{"padding":{"top":"12px","bottom":"12px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:12px;padding-bottom:12px"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide"><!-- wp:navigation {"className":"sky-nav","overlayBackgroundColor":"background","overlayTextColor":"foreground","metadata":{"ignoredHookedBlocks":["woocommerce/customer-account","woocommerce/mini-cart"]},"style":{"typography":{"fontSize":"1.0625rem","fontWeight":"600"},"spacing":{"blockGap":"2.25rem"}},"layout":{"type":"flex","justifyContent":"left"}} /-->

<!-- wp:social-links {"iconColor":"primary","iconColorValue":"#0088cc","size":"has-small-icon-size","className":"is-style-logos-only eb-header-store__social","style":{"spacing":{"blockGap":{"left":"14px"}}}} -->
<ul class="wp-block-social-links has-small-icon-size has-icon-color is-style-logos-only eb-header-store__social"><!-- wp:social-link {"url":"#","service":"facebook"} /-->

<!-- wp:social-link {"url":"#","service":"instagram"} /-->

<!-- wp:social-link {"url":"#","service":"x"} /-->

<!-- wp:social-link {"url":"#","service":"youtube"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
