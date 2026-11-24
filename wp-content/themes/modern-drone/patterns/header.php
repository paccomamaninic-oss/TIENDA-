<?php
/**
 * Title: Header
 * Slug: modern-drone/header
 * Categories: modern-drone, header
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}},"border":{"bottom":{"style":"none","width":"0px"},"top":[],"right":[],"left":[]}},"backgroundColor":"background","layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group has-background-background-color has-background" style="border-bottom-style:none;border-bottom-width:0px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:columns {"className":"topbar-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"0","right":"0"}},"border":{"bottom":{"color":"var:preset|color|border-color","width":"1px"},"top":[],"right":[],"left":[]}}} -->
<div class="wp-block-columns topbar-section" style="border-bottom-color:var(--wp--preset--color--border-color);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--30);padding-right:0;padding-bottom:var(--wp--preset--spacing--30);padding-left:0"><!-- wp:column {"verticalAlignment":"center","width":"35%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:35%"><!-- wp:group {"className":"phone-email","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
<div class="wp-block-group phone-email"><!-- wp:paragraph {"className":"top-header-text","style":{"typography":{"fontSize":"15px"},"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"textColor":"base","fontFamily":"poppins"} -->
<p class="top-header-text has-base-color has-text-color has-link-color has-poppins-font-family" style="font-size:15px"><a href="tel:8001234567890"><i class="fa-solid fa-phone"></i><?php echo esc_html__(' (+800) 123 456 7890','modern-drone'); ?></a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"top-header-text","style":{"typography":{"fontSize":"15px"},"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"textColor":"base","fontFamily":"poppins"} -->
<p class="top-header-text has-base-color has-text-color has-link-color has-poppins-font-family" style="font-size:15px"><a href="mailto:moderndrone@email.com"><i class="fa-regular fa-envelope"></i><?php echo esc_html__(' moderndrone@example.com','modern-drone'); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"30%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:30%"><!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}},"typography":{"fontSize":"15px","textTransform":"uppercase"}},"textColor":"base","fontFamily":"poppins"} -->
<p class="has-text-align-center has-base-color has-text-color has-link-color has-poppins-font-family" style="font-size:15px;text-transform:uppercase"><?php echo esc_html__('Free Shipping on Orders $500+','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"35%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:35%"><!-- wp:group {"className":"location-group","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group location-group"><!-- wp:paragraph {"align":"left","className":"top-header-text","style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}},"typography":{"fontSize":"15px"}},"textColor":"base","fontFamily":"poppins"} -->
<p class="has-text-align-left top-header-text has-base-color has-text-color has-link-color has-poppins-font-family" style="font-size:15px"><a href="#"><i class="fa-solid fa-location-dot"></i><?php echo esc_html__(' Store Location','modern-drone'); ?></a></p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->

<!-- /wp:shortcode -->

<!-- wp:shortcode -->

<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"className":"middle-header","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"},"margin":{"top":"0","bottom":"0"}}}} -->
<div class="wp-block-columns middle-header" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)"><!-- wp:column {"verticalAlignment":"center","width":"25%","className":"logo-text"} -->
<div class="wp-block-column is-vertically-aligned-center logo-text" style="flex-basis:25%"><!-- wp:site-title {"textAlign":"left","className":"header-logo","style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"25px","textTransform":"capitalize"}},"textColor":"primary","fontFamily":"poppins"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%"><!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search for...","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"style":{"typography":{"fontSize":"15px"},"border":{"width":"0px","style":"none"},"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"textColor":"base","fontFamily":"poppins"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%"><!-- wp:group {"className":"icon-group","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group icon-group"><!-- wp:image {"lightbox":{"enabled":false},"id":25,"scale":"cover","sizeSlug":"full","linkDestination":"custom","className":"heart-image","style":{"layout":{"selfStretch":"fit","flexSize":null},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<figure class="wp-block-image size-full heart-image" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0"><a href="#"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/heart.png'); ?>" alt="" class="wp-image-25" style="object-fit:cover"/></a></figure>
<!-- /wp:image -->

<!-- wp:woocommerce/cart-link {"cartIcon":"bag","content":""} /-->

<!-- wp:woocommerce/customer-account {"displayStyle":"icon_only","iconStyle":"line","iconClass":"wc-block-customer-account__account-icon","textColor":"base","style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}},"typography":{"fontSize":"20px"},"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bottom-header-col","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group bottom-header-col"><!-- wp:group {"className":"bottom-header","style":{"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"radius":{"bottomLeft":"20px","bottomRight":"20px"}}},"backgroundColor":"background","layout":{"type":"constrained","contentSize":"%"}} -->
<div class="wp-block-group bottom-header has-background-background-color has-background" style="border-bottom-left-radius:20px;border-bottom-right-radius:20px;margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--50)"><!-- wp:navigation {"textColor":"base","metadata":{"ignoredHookedBlocks":["woocommerce/customer-account","woocommerce/mini-cart"]},"style":{"typography":{"fontSize":"14px","textTransform":"uppercase","fontStyle":"normal","fontWeight":"400","letterSpacing":"0px"}},"fontFamily":"poppins","layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:navigation-link {"label":"<?php echo esc_html__('Home','modern-drone'); ?>","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->
<!-- wp:navigation-link {"label":"<?php echo esc_html__('Catalog','modern-drone'); ?>","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php echo esc_html__('Gift Card','modern-drone'); ?>","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->
	
<!-- wp:navigation-link {"label":"<?php echo esc_html__('Pages','modern-drone'); ?>","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php echo esc_html__('Feature','modern-drone'); ?>","type":"","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php esc_html_e('Buy Now','modern-drone'); ?>","type":"link","opensInNewTab":true,"url":"https://www.ovationthemes.com/products/drone-WordPress-theme","kind":"custom","className":"buynow"} /-->

<!-- /wp:navigation --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->