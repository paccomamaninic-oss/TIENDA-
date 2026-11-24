<?php
/**
 * Title: Footer (store)
 * Slug: envo-blocks-store/footer-store
 * Categories: footer
 * Block Types: core/template-part/footer
 *
 * A dark footer with three columns of quick links, an about text with a shipping note,
 * and a bottom row with the site title, credit and store promises.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$envo_blocks_store_account = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
$envo_blocks_store_cart    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'cart' ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","className":"eb-footer-store","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|40"},"margin":{"top":"0"},"blockGap":"var:preset|spacing|50"},"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"backgroundColor":"foreground","textColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-footer-store has-background-color has-foreground-background-color has-text-color has-background has-link-color" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"40px","left":"64px"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%"><!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"1.375rem"}},"textColor":"background"} -->
<h2 class="wp-block-heading has-background-color has-text-color" style="font-size:1.375rem"><?php esc_html_e( 'Quick links', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"isStackedOnMobile":false,"className":"eb-footer-store__links","style":{"spacing":{"blockGap":{"left":"24px"}}}} -->
<div class="wp-block-columns is-not-stacked-on-mobile eb-footer-store__links"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:page-list {"fontSize":"extra-small"} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:list {"className":"is-style-default","fontSize":"extra-small"} -->
<ul class="wp-block-list is-style-default has-extra-small-font-size"><!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Shop', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'New arrivals', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Sale', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Gift ideas', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:list {"className":"is-style-default","fontSize":"extra-small"} -->
<ul class="wp-block-list is-style-default has-extra-small-font-size"><?php if ( class_exists( 'WooCommerce' ) ) : ?>
<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_account ); ?>"><?php esc_html_e( 'My account', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $envo_blocks_store_cart ); ?>"><?php esc_html_e( 'Cart', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->
<?php else : ?>
<!-- wp:list-item -->
<li><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Log in', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->
<?php endif; ?>
<?php if ( get_privacy_policy_url() ) : ?>

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy policy', 'envo-blocks-store' ); ?></a></li>
<!-- /wp:list-item -->
<?php endif; ?></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%"><!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"1.375rem"}},"textColor":"background"} -->
<h2 class="wp-block-heading has-background-color has-text-color" style="font-size:1.375rem"><?php esc_html_e( 'About us', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"eb-footer-store__about","fontSize":"extra-small"} -->
<p class="eb-footer-store__about has-extra-small-font-size"><?php esc_html_e( 'We are an independent online store with everyday favourites for home, style and tech. Every order is packed with care and backed by friendly support.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"eb-footer-store__note","style":{"border":{"radius":"10px"},"spacing":{"padding":{"top":"14px","bottom":"14px","left":"18px","right":"18px"},"blockGap":"6px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group eb-footer-store__note" style="border-radius:10px;padding-top:14px;padding-right:18px;padding-bottom:14px;padding-left:18px"><!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Order by 2 pm and we ship it the same day.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Free returns within 30 days.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"align":"wide","className":"eb-footer-store__bottom","style":{"spacing":{"padding":{"top":"20px"},"blockGap":"12px"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide eb-footer-store__bottom" style="padding-top:20px"><!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group"><!-- wp:site-title {"level":0,"style":{"typography":{"fontWeight":"700"}},"fontSize":"extra-small"} /-->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php
/* translators: %s: WordPress link. */
printf( esc_html__( '· Designed with %s', 'envo-blocks-store' ), '<a href="' . esc_url( __( 'https://wordpress.org', 'envo-blocks-store' ) ) . '" rel="nofollow">WordPress</a>' );
?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"eb-footer-store__promises","fontSize":"extra-small"} -->
<p class="eb-footer-store__promises has-extra-small-font-size"><?php esc_html_e( 'Secure checkout · Free returns · Support 24/7', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
