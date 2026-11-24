<?php
/**
 * Title: Home: welcome banner
 * Slug: envo-blocks-store/home-banner
 * Categories: envo-blocks-store, banner, call-to-action
 * Keywords: banner, welcome, about, call to action
 * Viewport width: 1400
 * Description: A wide photo banner with a welcome message, two buttons and a short story.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","className":"eb-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-section" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/banner-store.webp' ) ); ?>","alt":"<?php esc_attr_e( 'Bright clothing boutique with clothes on hanging rails', 'envo-blocks-store' ); ?>","dimRatio":60,"overlayColor":"foreground","isUserOverlayColor":true,"minHeight":420,"isDark":true,"align":"wide","className":"eb-banner","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"56px","bottom":"56px","left":"56px","right":"56px"}}},"layout":{"type":"default"}} -->
<div class="wp-block-cover alignwide is-dark eb-banner" style="border-radius:16px;padding-top:56px;padding-right:56px;padding-bottom:56px;padding-left:56px;min-height:420px"><span aria-hidden="true" class="wp-block-cover__background has-foreground-background-color has-background-dim-60 has-background-dim"></span><img class="wp-block-cover__image-background" alt="<?php esc_attr_e( 'Bright clothing boutique with clothes on hanging rails', 'envo-blocks-store' ); ?>" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/banner-store.webp' ) ); ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"32px","left":"64px"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"textColor":"background","fontSize":"extra-small"} -->
<p class="has-background-color has-text-color has-extra-small-font-size" style="font-weight:600"><?php esc_html_e( 'Welcome to our store', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontSize":"clamp(1.875rem, 3vw, 2.5rem)","lineHeight":"1.15"},"spacing":{"margin":{"top":"0.5rem","bottom":"1.5rem"}}},"textColor":"background"} -->
<h2 class="wp-block-heading has-background-color has-text-color" style="margin-top:0.5rem;margin-bottom:1.5rem;font-size:clamp(1.875rem, 3vw, 2.5rem);line-height:1.15"><?php esc_html_e( 'Everything you love, in one place', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"primary","textColor":"background"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-primary-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Shop now', 'envo-blocks-store' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"backgroundColor":"background","textColor":"foreground"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Learn more', 'envo-blocks-store' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"textColor":"background"} -->
<p class="has-background-color has-text-color"><?php esc_html_e( 'We started as a small boutique with a simple idea: well-made clothes and accessories at fair prices. Today we ship to customers everywhere, but every order is still packed with the same care.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"textColor":"background"} -->
<p class="has-background-color has-text-color"><?php esc_html_e( 'New pieces arrive every week, and returns are free for 30 days.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->
