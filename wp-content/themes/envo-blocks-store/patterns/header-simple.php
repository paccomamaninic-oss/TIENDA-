<?php
/**
 * Title: Header (simple)
 * Slug: envo-blocks-store/header-simple
 * Categories: header
 * Block Types: core/template-part/header
 *
 * Logo, site title and menu. WooCommerce's automatic insertion of the account and cart
 * icons next to the navigation is switched off with ignoredHookedBlocks.
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
<div class="wp-block-group sky-header-nav"><!-- wp:navigation {"className":"sky-nav","overlayBackgroundColor":"background","overlayTextColor":"foreground","metadata":{"ignoredHookedBlocks":["woocommerce/customer-account","woocommerce/mini-cart"]},"style":{"typography":{"fontSize":"1rem","fontWeight":"600"}},"layout":{"type":"flex","justifyContent":"right"}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
