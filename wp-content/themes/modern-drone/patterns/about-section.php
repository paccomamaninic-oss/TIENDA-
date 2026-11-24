<?php
/**
 * Title: About Section
 * Slug: modern-drone/about-section
 * Categories: modern-drone, about-section
 */
?>

<!-- wp:group {"className":"about-section","style":{"spacing":{"padding":{"right":"0px","left":"0px","top":"0","bottom":"0"},"margin":{"top":"0px","bottom":"0px"}},"typography":{"fontSize":"14px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group about-section" style="margin-top:0px;margin-bottom:0px;padding-top:0;padding-right:0px;padding-bottom:0;padding-left:0px;font-size:14px"><!-- wp:cover {"url":"<?php echo esc_url( get_template_directory_uri() . '/images/about-back.png'); ?>","id":96,"dimRatio":20,"overlayColor":"secondary","isUserOverlayColor":true,"minHeight":600,"sizeSlug":"large","className":"banner-bg","style":{"spacing":{"padding":{"top":"0px","bottom":"20px","left":"0px","right":"0px"}}},"layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-cover banner-bg" style="padding-top:0px;padding-right:0px;padding-bottom:20px;padding-left:0px;min-height:600px"><img class="wp-block-cover__image-background wp-image-96 size-large" alt="" src="<?php echo esc_url( get_template_directory_uri() . '/images/about-back.png'); ?>" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-secondary-background-color has-background-dim-20 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"head-box wow fadeInDown","style":{"spacing":{"margin":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|70"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group head-box wow fadeInDown" style="margin-top:var(--wp--preset--spacing--40);margin-bottom:var(--wp--preset--spacing--70)"><!-- wp:paragraph {"align":"center","className":"sec-sub-title","style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}},"typography":{"textTransform":"capitalize","letterSpacing":"0px","fontStyle":"normal","fontWeight":"700","fontSize":"25px"}},"textColor":"base"} -->
<p class="has-text-align-center sec-sub-title has-base-color has-text-color has-link-color" style="font-size:25px;font-style:normal;font-weight:700;letter-spacing:0px;text-transform:capitalize"><?php echo esc_html__('Who We ','modern-drone'); ?><mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-primary-color"><?php echo esc_html__('Are','modern-drone'); ?></mark></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":3,"className":"about-sec-title","style":{"typography":{"textTransform":"none","lineHeight":"1.5","fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"textColor":"base","fontSize":"medium","fontFamily":"poppins"} -->
<h3 class="wp-block-heading has-text-align-center about-sec-title has-base-color has-text-color has-link-color has-poppins-font-family has-medium-font-size" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:0;font-style:normal;font-weight:400;line-height:1.5;text-transform:none"><?php echo esc_html__('Trusted manufacturing experts with over 20 years of industry experience.','modern-drone'); ?></h3>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"about-cols wow fadeInUp"} -->
<div class="wp-block-columns about-cols wow fadeInUp"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:group {"className":"about-img-box-inner","style":{"spacing":{"padding":{"right":"0px"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group about-img-box-inner" style="padding-right:0px"><!-- wp:image {"id":30,"width":"auto","height":"300px","sizeSlug":"full","linkDestination":"none","style":{"border":{"radius":"10px","width":"1px"}},"borderColor":"white"} -->
<figure class="wp-block-image size-full is-resized has-custom-border"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/about.png'); ?>" alt="" class="has-border-color has-white-border-color wp-image-30" style="border-width:1px;border-radius:10px;width:auto;height:300px"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"abt-exp-box","style":{"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}},"border":{"radius":"10px","width":"1px"}},"backgroundColor":"primary","borderColor":"white","layout":{"type":"default"}} -->
<div class="wp-block-group abt-exp-box has-border-color has-white-border-color has-primary-background-color has-background" style="border-width:1px;border-radius:10px;margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"26px","fontStyle":"normal","fontWeight":"600"},"elements":{"link":{"color":{"text":"var:preset|color|secondary"}}},"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|20"}}},"textColor":"secondary"} -->
<p class="has-text-align-center has-secondary-color has-text-color has-link-color" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--20);font-size:26px;font-style:normal;font-weight:600"><?php echo esc_html__('20 +','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px","fontStyle":"normal","fontWeight":"500"},"elements":{"link":{"color":{"text":"var:preset|color|secondary"}}},"spacing":{"margin":{"top":"0","bottom":"0"}}},"textColor":"secondary"} -->
<p class="has-text-align-center has-secondary-color has-text-color has-link-color" style="margin-top:0;margin-bottom:0;font-size:16px;font-style:normal;font-weight:500"><?php echo esc_html__('years of','modern-drone'); ?><br><?php echo esc_html__('experience','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","className":"about-text-box","style":{"spacing":{"padding":{"left":"0"}}}} -->
<div class="wp-block-column is-vertically-aligned-center about-text-box" style="padding-left:0"><!-- wp:paragraph {"align":"right","style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}},"spacing":{"margin":{"top":"0","bottom":"0","left":"80px"}},"typography":{"lineHeight":"2","fontSize":"18px","fontStyle":"normal","fontWeight":"400"}},"textColor":"base"} -->
<p class="has-text-align-right has-base-color has-text-color has-link-color" style="margin-top:0;margin-bottom:0;margin-left:80px;font-size:18px;font-style:normal;font-weight:400;line-height:2"><?php echo esc_html__('At ForgeFlow Industries, we specialize in delivering innovative and cost-effective manufacturing solutions. From design and prototyping to full-scale production, we ensure precision, durability, and efficiency at every stage.At ForgeFlow Industries, we specialize in delivering innovative and cost-effective manufacturing solutions. From design and prototyping to full-scale production, we ensure precision, durability, and efficiency at every stage.','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->