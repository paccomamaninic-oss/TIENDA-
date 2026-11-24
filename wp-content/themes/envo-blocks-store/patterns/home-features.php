<?php
/**
 * Title: Home: store benefits
 * Slug: envo-blocks-store/home-features
 * Categories: envo-blocks-store, call-to-action
 * Keywords: features, benefits, shipping, support
 * Viewport width: 1400
 * Description: Three store benefits with line icons.
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:group {"align":"full","className":"eb-section eb-features","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-section eb-features" style="padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)"><!-- wp:columns {"align":"wide","className":"eb-features__row","style":{"spacing":{"blockGap":{"top":"0","left":"0"}}}} -->
<div class="wp-block-columns alignwide eb-features__row"><!-- wp:column {"className":"eb-feature"} -->
<div class="wp-block-column eb-feature"><!-- wp:group {"style":{"spacing":{"blockGap":"16px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:image {"width":"44px","height":"44px","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icon-delivery.svg' ) ); ?>" alt="" style="width:44px;height:44px"/></figure>
<!-- /wp:image -->

<!-- wp:group {"style":{"spacing":{"blockGap":"2px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem"}}} -->
<h3 class="wp-block-heading" style="font-size:1.125rem"><?php esc_html_e( 'Free delivery', 'envo-blocks-store' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"extra-small"} -->
<p class="has-muted-color has-text-color has-extra-small-font-size"><?php esc_html_e( 'Free shipping on all orders over $50.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"eb-feature"} -->
<div class="wp-block-column eb-feature"><!-- wp:group {"style":{"spacing":{"blockGap":"16px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:image {"width":"44px","height":"44px","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icon-rating.svg' ) ); ?>" alt="" style="width:44px;height:44px"/></figure>
<!-- /wp:image -->

<!-- wp:group {"style":{"spacing":{"blockGap":"2px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem"}}} -->
<h3 class="wp-block-heading" style="font-size:1.125rem"><?php esc_html_e( '5-star service', 'envo-blocks-store' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"extra-small"} -->
<p class="has-muted-color has-text-color has-extra-small-font-size"><?php esc_html_e( 'Loved by thousands of happy customers.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"eb-feature"} -->
<div class="wp-block-column eb-feature"><!-- wp:group {"style":{"spacing":{"blockGap":"16px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:image {"width":"44px","height":"44px","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icon-support.svg' ) ); ?>" alt="" style="width:44px;height:44px"/></figure>
<!-- /wp:image -->

<!-- wp:group {"style":{"spacing":{"blockGap":"2px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem"}}} -->
<h3 class="wp-block-heading" style="font-size:1.125rem"><?php esc_html_e( 'Support 24/7', 'envo-blocks-store' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"extra-small"} -->
<p class="has-muted-color has-text-color has-extra-small-font-size"><?php esc_html_e( 'We are here to help, any day of the week.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
