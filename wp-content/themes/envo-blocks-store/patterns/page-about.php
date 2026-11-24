<?php
/**
 * Title: About page
 * Slug: envo-blocks-store/page-about
 * Categories: envo-blocks-store, about
 * Keywords: about, story, team, values
 * Block Types: core/post-content
 * Post Types: page
 * Viewport width: 1100
 * Description: The story of the store with a photo, three values and a short call to action.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:paragraph {"fontSize":"medium"} -->
<p class="has-medium-font-size"><?php esc_html_e( 'We started as a small boutique with a simple idea: well-made everyday things at fair prices. Today we ship to customers everywhere, and every order is still packed by hand.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","style":{"border":{"radius":"16px"}}} -->
<figure class="wp-block-image size-full has-custom-border"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/banner-store.webp' ) ); ?>" alt="<?php esc_attr_e( 'Bright clothing boutique with clothes on hanging rails', 'envo-blocks-store' ); ?>" style="border-radius:16px"/></figure>
<!-- /wp:image -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'What we care about', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"20px","left":"20px"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem"}}} -->
<h3 class="wp-block-heading" style="font-size:1.125rem"><?php esc_html_e( 'Chosen with care', 'envo-blocks-store' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'We test every product before it goes in the shop.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem"}}} -->
<h3 class="wp-block-heading" style="font-size:1.125rem"><?php esc_html_e( 'Less packaging', 'envo-blocks-store' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Recycled cardboard and paper tape, no plastic fillers.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="border-radius:16px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem"}}} -->
<h3 class="wp-block-heading" style="font-size:1.125rem"><?php esc_html_e( 'Real people', 'envo-blocks-store' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Questions are answered by our team, not a bot.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--50)"><?php esc_html_e( 'New pieces arrive every week. Come and have a look.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"foreground","textColor":"background"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-foreground-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Visit the shop', 'envo-blocks-store' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
