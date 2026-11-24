<?php
/**
 * Title: Home: two promo strips
 * Slug: envo-blocks-store/home-promo-strips
 * Categories: envo-blocks-store, banner, call-to-action
 * Keywords: banner, promotion, offer, strip
 * Viewport width: 1400
 * Description: Two short, wide promotion banners side by side, each with a product cut-out.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","className":"eb-section eb-strips","style":{"spacing":{"padding":{"top":"0","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-section eb-strips" style="padding-top:0;padding-bottom:var(--wp--preset--spacing--30)"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"20px","left":"20px"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"eb-strip","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"36px","right":"28px"},"blockGap":"24px"}},"backgroundColor":"secondary","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group eb-strip has-secondary-background-color has-background" style="border-radius:16px;padding-top:20px;padding-right:28px;padding-bottom:20px;padding-left:36px"><!-- wp:group {"style":{"spacing":{"blockGap":"6px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"textColor":"primary","fontSize":"extra-small"} -->
<p class="has-primary-color has-text-color has-extra-small-font-size" style="font-weight:600"><?php esc_html_e( 'New in audio', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.5rem","lineHeight":"1.25"}}} -->
<h3 class="wp-block-heading" style="font-size:1.5rem;line-height:1.25"><?php esc_html_e( 'Room-filling sound from $49', 'envo-blocks-store' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"eb-strip__link","fontSize":"extra-small"} -->
<p class="eb-strip__link has-extra-small-font-size"><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Shop speakers →', 'envo-blocks-store' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"eb-strip__image"} -->
<figure class="wp-block-image size-full eb-strip__image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/banner-speaker.webp' ) ); ?>" alt="<?php esc_attr_e( 'Round white wireless speaker on a stand', 'envo-blocks-store' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"eb-strip eb-strip--dark","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"36px","right":"28px"},"blockGap":"24px"},"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"backgroundColor":"foreground","textColor":"background","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group eb-strip eb-strip--dark has-background-color has-foreground-background-color has-text-color has-background has-link-color" style="border-radius:16px;padding-top:20px;padding-right:28px;padding-bottom:20px;padding-left:36px"><!-- wp:group {"style":{"spacing":{"blockGap":"6px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"textColor":"secondary","fontSize":"extra-small"} -->
<p class="has-secondary-color has-text-color has-extra-small-font-size" style="font-weight:600"><?php esc_html_e( 'Kitchen & home', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.5rem","lineHeight":"1.25"}},"textColor":"background"} -->
<h3 class="wp-block-heading has-background-color has-text-color" style="font-size:1.5rem;line-height:1.25"><?php esc_html_e( 'Glass bottles, 3 for 2', 'envo-blocks-store' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"eb-strip__link","fontSize":"extra-small"} -->
<p class="eb-strip__link has-extra-small-font-size"><a href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Shop the offer →', 'envo-blocks-store' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"eb-strip__image"} -->
<figure class="wp-block-image size-full eb-strip__image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/banner-bottle.webp' ) ); ?>" alt="<?php esc_attr_e( 'Glass bottle with a gold lid', 'envo-blocks-store' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
