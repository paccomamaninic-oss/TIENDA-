<?php
/**
 * Title: Home: contact and offer
 * Slug: envo-blocks-store/home-contact
 * Categories: envo-blocks-store, contact, call-to-action
 * Keywords: contact, address, social, offer
 * Viewport width: 1400
 * Description: A dark contact card with social links next to a product offer.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","className":"eb-section eb-contact","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-section eb-contact" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"20px","left":"20px"}}}} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:group {"className":"eb-contact__card","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"48px","bottom":"48px","left":"48px","right":"48px"},"blockGap":"12px"},"elements":{"link":{"color":{"text":"var:preset|color|background"}}}},"backgroundColor":"foreground","textColor":"background","layout":{"type":"default"}} -->
<div class="wp-block-group eb-contact__card has-background-color has-foreground-background-color has-text-color has-background has-link-color" style="border-radius:16px;padding-top:48px;padding-right:48px;padding-bottom:48px;padding-left:48px"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600","letterSpacing":"0.12em","textTransform":"uppercase"}},"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size" style="font-weight:600;letter-spacing:0.12em;text-transform:uppercase"><?php esc_html_e( 'Contact', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontSize":"2.25rem"}},"textColor":"background"} -->
<h2 class="wp-block-heading has-background-color has-text-color" style="font-size:2.25rem"><?php esc_html_e( 'Be in touch', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:social-links {"iconColor":"foreground","iconColorValue":"#222529","iconBackgroundColor":"background","iconBackgroundColorValue":"#ffffff","style":{"spacing":{"margin":{"top":"20px","bottom":"20px"},"blockGap":{"left":"12px"}}}} -->
<ul class="wp-block-social-links has-icon-color has-icon-background-color" style="margin-top:20px;margin-bottom:20px"><!-- wp:social-link {"url":"#","service":"facebook"} /-->

<!-- wp:social-link {"url":"#","service":"instagram"} /-->

<!-- wp:social-link {"url":"#","service":"youtube"} /-->

<!-- wp:social-link {"url":"#","service":"wordpress"} /--></ul>
<!-- /wp:social-links -->

<!-- wp:paragraph -->
<p><?php esc_html_e( '123 Market Street, Springfield', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'hello@example.com', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( '+1 555 0100 200', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:group {"className":"eb-offer","style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"48px","bottom":"48px","left":"48px","right":"48px"},"blockGap":"12px"}},"backgroundColor":"secondary","layout":{"type":"default"}} -->
<div class="wp-block-group eb-offer has-secondary-background-color has-background" style="border-radius:16px;padding-top:48px;padding-right:48px;padding-bottom:48px;padding-left:48px"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"textColor":"primary","fontSize":"extra-small"} -->
<p class="has-primary-color has-text-color has-extra-small-font-size" style="font-weight:600"><?php esc_html_e( 'Sun season', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"fontSize":"2.25rem"}}} -->
<h2 class="wp-block-heading" style="font-size:2.25rem"><?php esc_html_e( 'Sunglasses from $29', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Light frames, UV400 lenses and a case in every box.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"eb-offer__image","style":{"spacing":{"margin":{"top":"24px","bottom":"24px"}}}} -->
<figure class="wp-block-image size-full eb-offer__image" style="margin-top:24px;margin-bottom:24px"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/banner-sunglasses.webp' ) ); ?>" alt="<?php esc_attr_e( 'Rimless sunglasses with brown gradient lenses', 'envo-blocks-store' ); ?>"/></figure>
<!-- /wp:image -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"foreground","textColor":"background"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background-color has-foreground-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $envo_blocks_store_shop ); ?>"><?php esc_html_e( 'Shop sunglasses', 'envo-blocks-store' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
