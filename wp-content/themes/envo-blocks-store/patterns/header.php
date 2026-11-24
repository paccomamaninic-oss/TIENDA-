<?php
/**
 * Title: Header (shop icons)
 * Slug: envo-blocks-store/header
 * Categories: header
 * Block Types: core/template-part/header
 *
 * The account and cart icons are placed here explicitly; WooCommerce's automatic
 * insertion next to the navigation is switched off with ignoredHookedBlocks.
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:group {"align":"full","className":"sky-header","style":{"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sky-header has-background-background-color has-background" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"className":"sky-brand","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group sky-brand"><!-- wp:site-logo {"width":36,"shouldSyncIcon":false,"className":"sky-logo"} /-->

<!-- wp:site-title {"level":0,"className":"sky-site-title","style":{"typography":{"fontSize":"1.375rem","fontWeight":"700","lineHeight":"1.2"},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"sky-header-nav","style":{"spacing":{"blockGap":"1.5rem"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group sky-header-nav"><!-- wp:navigation {"className":"sky-nav","overlayBackgroundColor":"background","overlayTextColor":"foreground","metadata":{"ignoredHookedBlocks":["woocommerce/customer-account","woocommerce/mini-cart"]},"style":{"typography":{"fontSize":"1rem","fontWeight":"600"}},"layout":{"type":"flex","justifyContent":"right"}} /-->

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
<!-- /wp:group --></div>
<!-- /wp:group -->
