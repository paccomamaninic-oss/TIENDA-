<?php
/**
 * Title: Project Section
 * Slug: modern-drone/project-section
 * Categories: modern-drone, project-section
 */
$modern_drone_pluginsList = get_option( 'active_plugins' );
$modern_drone_plugin = 'woocommerce/woocommerce.php';
$modern_drone_results = in_array( $modern_drone_plugin , $modern_drone_pluginsList);
if ( $modern_drone_results )  {
?>
<!-- wp:spacer {"height":"70px"} -->
<div style="height:70px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"style":{"spacing":{"padding":{"right":"0","left":"0","top":"0","bottom":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"0"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:woocommerce/product-collection {"queryId":0,"query":{"perPage":9,"pages":0,"offset":0,"postType":"product","order":"asc","orderBy":"title","search":"","exclude":[],"inherit":false,"taxQuery":[],"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":true,"relatedBy":{"categories":true,"tags":true}},"tagName":"div","displayLayout":{"type":"flex","columns":6,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"queryContextIncludes":["collection"],"__privatePreviewState":{"isPreview":false,"previewMessage":"Actual products will vary depending on the page being viewed."},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-woocommerce-product-collection"><!-- wp:woocommerce/product-template {"className":"product-main-list owl-carousel"} -->
<!-- wp:group {"className":"product-main-box","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group product-main-box"><!-- wp:group {"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group"><!-- wp:post-terms {"term":"product_tag","className":"product-tag-main"} /-->

<!-- wp:woocommerce/product-summary {"isDescendentOfQueryLoop":true,"showDescriptionIfEmpty":true,"showLink":true,"summaryLength":1,"linkText":"r","className":"product-main-summary"} /-->

<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} /--></div>
<!-- /wp:group -->

<!-- wp:post-terms {"term":"product_cat","className":"category-product","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"small","fontFamily":"poppins"} /-->

<!-- wp:post-title {"textAlign":"center","isLink":true,"style":{"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}},"typography":{"lineHeight":"1.6","fontStyle":"normal","fontWeight":"600","fontSize":"18px"}},"fontFamily":"oswald","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->

<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true,"textAlign":"center","fontFamily":"poppins","fontSize":"small","style":{"spacing":{"margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}},"typography":{"fontStyle":"normal","fontWeight":"500"}}} /-->

<!-- wp:spacer {"height":"1px","className":"price-spacer"} -->
<div style="height:1px" aria-hidden="true" class="wp-block-spacer price-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:woocommerce/product-button {"textAlign":"center","isDescendentOfQueryLoop":true,"fontSize":"small"} /--></div>
<!-- /wp:group -->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<?php } else { ?>


<!-- wp:spacer {"height":"70px"} -->
<div style="height:70px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"className":"owl-carousel pro-static-sec product-main-list","style":{"spacing":{"padding":{"right":"0","left":"0","top":"0","bottom":"0"},"margin":{"top":"0","bottom":"0"},"blockGap":"0"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group owl-carousel pro-static-sec product-main-list" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:group {"className":"static-pro-box","style":{"border":{"width":"2px","color":"#828282","radius":{"topLeft":"10px","topRight":"10px","bottomLeft":"10px","bottomRight":"10px"}},"spacing":{"padding":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group static-pro-box has-border-color" style="border-color:#828282;border-width:2px;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:buttons {"className":"static-pro-button","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<div class="wp-block-buttons static-pro-button"><!-- wp:button {"textAlign":"left","backgroundColor":"border-color","style":{"spacing":{"padding":{"left":"var:preset|spacing|20","right":"var:preset|spacing|20","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"radius":{"topLeft":"0px","topRight":"8px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-border-color-background-color has-background has-text-align-left wp-element-button" href="#" style="border-top-left-radius:0px;border-top-right-radius:8px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><img class="wp-image-52" style="width: 16px;" src="<?php echo esc_url( get_template_directory_uri() . '/images/arrow-right.png'); ?>" alt=""></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group"><!-- wp:image {"id":100,"sizeSlug":"full","linkDestination":"none","align":"center","className":"pro-static-img"} -->
<figure class="wp-block-image aligncenter size-full pro-static-img"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/drone01.png'); ?>" alt="" class="wp-image-100"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"medium"} -->
<p class="has-text-align-center has-border-color-color has-text-color has-link-color has-medium-font-size"><?php echo esc_html__('Drone','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","className":"pro-static-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"fontFamily":"oswald"} -->
<h2 class="wp-block-heading has-text-align-center pro-static-heading has-oswald-font-family" style="font-size:16px;font-style:normal;font-weight:600"><a href="#"><?php echo esc_html__('Falcon X1 4K GPS Drone','modern-drone'); ?></a></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}},"typography":{"textDecoration":"line-through"}},"textColor":"border-color","fontSize":"extra-small","fontFamily":"poppins"} -->
<p class="has-border-color-color has-text-color has-link-color has-poppins-font-family has-extra-small-font-size" style="text-decoration:line-through"><?php echo esc_html__('$789,00','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"primary","fontSize":"small","fontFamily":"poppins"} -->
<p class="has-primary-color has-text-color has-link-color has-poppins-font-family has-small-font-size" style="font-style:normal;font-weight:700"><?php echo esc_html__('$685,00','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"1px","className":"price-spacer price-spacer-pro","style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);height:1px" aria-hidden="true" class="wp-block-spacer price-spacer price-spacer-pro"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":107,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/quick-view.png'); ?>" alt="" class="wp-image-107"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":108,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/pro-heart.png'); ?>" alt="" class="wp-image-108"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"static-pro-box","style":{"border":{"width":"2px","color":"#828282","radius":{"topLeft":"10px","topRight":"10px","bottomLeft":"10px","bottomRight":"10px"}},"spacing":{"padding":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group static-pro-box has-border-color" style="border-color:#828282;border-width:2px;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:buttons {"className":"static-pro-button","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<div class="wp-block-buttons static-pro-button"><!-- wp:button {"textAlign":"left","backgroundColor":"border-color","style":{"spacing":{"padding":{"left":"var:preset|spacing|20","right":"var:preset|spacing|20","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"radius":{"topLeft":"0px","topRight":"8px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-border-color-background-color has-background has-text-align-left wp-element-button" href="#" style="border-top-left-radius:0px;border-top-right-radius:8px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><img class="wp-image-52" style="width: 16px;" src="<?php echo esc_url( get_template_directory_uri() . '/images/arrow-right.png'); ?>" alt=""></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group"><!-- wp:image {"id":122,"sizeSlug":"full","linkDestination":"none","align":"center","className":"pro-static-img"} -->
<figure class="wp-block-image aligncenter size-full pro-static-img"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/drone02.png'); ?>" alt="" class="wp-image-122"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"medium"} -->
<p class="has-text-align-center has-border-color-color has-text-color has-link-color has-medium-font-size"><?php echo esc_html__('Drone','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","className":"pro-static-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"fontFamily":"oswald"} -->
<h2 class="wp-block-heading has-text-align-center pro-static-heading has-oswald-font-family" style="font-size:16px;font-style:normal;font-weight:600"><a href="#"><?php echo esc_html__('AeroPro Mini Foldable Drone','modern-drone'); ?></a></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}},"typography":{"textDecoration":"line-through"}},"textColor":"border-color","fontSize":"extra-small","fontFamily":"poppins"} -->
<p class="has-border-color-color has-text-color has-link-color has-poppins-font-family has-extra-small-font-size" style="text-decoration:line-through"><?php echo esc_html__('$789,00','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"primary","fontSize":"small","fontFamily":"poppins"} -->
<p class="has-primary-color has-text-color has-link-color has-poppins-font-family has-small-font-size" style="font-style:normal;font-weight:700"><?php echo esc_html__('$685,00','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"1px","className":"price-spacer price-spacer-pro","style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);height:1px" aria-hidden="true" class="wp-block-spacer price-spacer price-spacer-pro"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":107,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/quick-view.png'); ?>" alt="" class="wp-image-107"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":108,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/pro-heart.png'); ?>" alt="" class="wp-image-108"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"static-pro-box","style":{"border":{"width":"2px","color":"#828282","radius":{"topLeft":"10px","topRight":"10px","bottomLeft":"10px","bottomRight":"10px"}},"spacing":{"padding":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group static-pro-box has-border-color" style="border-color:#828282;border-width:2px;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:buttons {"className":"static-pro-button","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<div class="wp-block-buttons static-pro-button"><!-- wp:button {"textAlign":"left","backgroundColor":"border-color","style":{"spacing":{"padding":{"left":"var:preset|spacing|20","right":"var:preset|spacing|20","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"radius":{"topLeft":"0px","topRight":"8px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-border-color-background-color has-background has-text-align-left wp-element-button" href="#" style="border-top-left-radius:0px;border-top-right-radius:8px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><img class="wp-image-52" style="width: 16px;" src="<?php echo esc_url( get_template_directory_uri() . '/images/arrow-right.png'); ?>" alt=""></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group"><!-- wp:image {"id":123,"sizeSlug":"full","linkDestination":"none","align":"center","className":"pro-static-img"} -->
<figure class="wp-block-image aligncenter size-full pro-static-img"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/drone03.png'); ?>" alt="" class="wp-image-123"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"medium"} -->
<p class="has-text-align-center has-border-color-color has-text-color has-link-color has-medium-font-size">Drone</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","className":"pro-static-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"fontFamily":"oswald"} -->
<h2 class="wp-block-heading has-text-align-center pro-static-heading has-oswald-font-family" style="font-size:16px;font-style:normal;font-weight:600"><a href="#"><?php echo esc_html__('SkyRider Racing Drone','modern-drone'); ?></a></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}},"typography":{"textDecoration":"line-through"}},"textColor":"border-color","fontSize":"extra-small","fontFamily":"poppins"} -->
<p class="has-border-color-color has-text-color has-link-color has-poppins-font-family has-extra-small-font-size" style="text-decoration:line-through"><?php echo esc_html__('$789,00','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"primary","fontSize":"small","fontFamily":"poppins"} -->
<p class="has-primary-color has-text-color has-link-color has-poppins-font-family has-small-font-size" style="font-style:normal;font-weight:700"><?php echo esc_html__('$685,00','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"1px","className":"price-spacer price-spacer-pro","style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);height:1px" aria-hidden="true" class="wp-block-spacer price-spacer price-spacer-pro"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":107,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/quick-view.png'); ?>" alt="" class="wp-image-107"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":108,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/pro-heart.png'); ?>" alt="" class="wp-image-108"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"static-pro-box","style":{"border":{"width":"2px","color":"#828282","radius":{"topLeft":"10px","topRight":"10px","bottomLeft":"10px","bottomRight":"10px"}},"spacing":{"padding":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group static-pro-box has-border-color" style="border-color:#828282;border-width:2px;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:buttons {"className":"static-pro-button","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<div class="wp-block-buttons static-pro-button"><!-- wp:button {"textAlign":"left","backgroundColor":"border-color","style":{"spacing":{"padding":{"left":"var:preset|spacing|20","right":"var:preset|spacing|20","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"radius":{"topLeft":"0px","topRight":"8px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-border-color-background-color has-background has-text-align-left wp-element-button" href="#" style="border-top-left-radius:0px;border-top-right-radius:8px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><img class="wp-image-52" style="width: 16px;" src="<?php echo esc_url( get_template_directory_uri() . '/images/arrow-right.png'); ?>" alt=""></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group"><!-- wp:image {"id":124,"sizeSlug":"full","linkDestination":"none","align":"center","className":"pro-static-img"} -->
<figure class="wp-block-image aligncenter size-full pro-static-img"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/drone04.png'); ?>" alt="" class="wp-image-124"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"medium"} -->
<p class="has-text-align-center has-border-color-color has-text-color has-link-color has-medium-font-size"><?php echo esc_html__('Drone','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","className":"pro-static-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"fontFamily":"oswald"} -->
<h2 class="wp-block-heading has-text-align-center pro-static-heading has-oswald-font-family" style="font-size:16px;font-style:normal;font-weight:600"><a href="#"><?php echo esc_html__('VisionPro 8K Cinematic Drone','modern-drone'); ?></a></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}},"typography":{"textDecoration":"line-through"}},"textColor":"border-color","fontSize":"extra-small","fontFamily":"poppins"} -->
<p class="has-border-color-color has-text-color has-link-color has-poppins-font-family has-extra-small-font-size" style="text-decoration:line-through"><?php echo esc_html__('$789,00','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"primary","fontSize":"small","fontFamily":"poppins"} -->
<p class="has-primary-color has-text-color has-link-color has-poppins-font-family has-small-font-size" style="font-style:normal;font-weight:700"><?php echo esc_html__('$685,00','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"1px","className":"price-spacer price-spacer-pro","style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);height:1px" aria-hidden="true" class="wp-block-spacer price-spacer price-spacer-pro"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":107,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/quick-view.png'); ?>" alt="" class="wp-image-107"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":108,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/pro-heart.png'); ?>" alt="" class="wp-image-108"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"static-pro-box","style":{"border":{"width":"2px","color":"#828282","radius":{"topLeft":"10px","topRight":"10px","bottomLeft":"10px","bottomRight":"10px"}},"spacing":{"padding":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group static-pro-box has-border-color" style="border-color:#828282;border-width:2px;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:buttons {"className":"static-pro-button","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<div class="wp-block-buttons static-pro-button"><!-- wp:button {"textAlign":"left","backgroundColor":"border-color","style":{"spacing":{"padding":{"left":"var:preset|spacing|20","right":"var:preset|spacing|20","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"radius":{"topLeft":"0px","topRight":"8px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-border-color-background-color has-background has-text-align-left wp-element-button" href="#" style="border-top-left-radius:0px;border-top-right-radius:8px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><img class="wp-image-52" style="width: 16px;" src="<?php echo esc_url( get_template_directory_uri() . '/images/arrow-right.png'); ?>" alt=""></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group"><!-- wp:image {"id":125,"sizeSlug":"full","linkDestination":"none","align":"center","className":"pro-static-img"} -->
<figure class="wp-block-image aligncenter size-full pro-static-img"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/drone05.png'); ?>" alt="" class="wp-image-125"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"medium"} -->
<p class="has-text-align-center has-border-color-color has-text-color has-link-color has-medium-font-size"><?php echo esc_html__('Drone','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","className":"pro-static-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"fontFamily":"oswald"} -->
<h2 class="wp-block-heading has-text-align-center pro-static-heading has-oswald-font-family" style="font-size:16px;font-style:normal;font-weight:600"><a href="#"><?php echo esc_html__('Smart Drone Battery Pack','modern-drone'); ?></a></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}},"typography":{"textDecoration":"line-through"}},"textColor":"border-color","fontSize":"extra-small","fontFamily":"poppins"} -->
<p class="has-border-color-color has-text-color has-link-color has-poppins-font-family has-extra-small-font-size" style="text-decoration:line-through"><?php echo esc_html__('$789,00','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"primary","fontSize":"small","fontFamily":"poppins"} -->
<p class="has-primary-color has-text-color has-link-color has-poppins-font-family has-small-font-size" style="font-style:normal;font-weight:700"><?php echo esc_html__('$685,00','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"1px","className":"price-spacer price-spacer-pro","style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);height:1px" aria-hidden="true" class="wp-block-spacer price-spacer price-spacer-pro"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":107,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/quick-view.png'); ?>" alt="" class="wp-image-107"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":108,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/pro-heart.png'); ?>" alt="" class="wp-image-108"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"static-pro-box","style":{"border":{"width":"2px","color":"#828282","radius":{"topLeft":"10px","topRight":"10px","bottomLeft":"10px","bottomRight":"10px"}},"spacing":{"padding":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group static-pro-box has-border-color" style="border-color:#828282;border-width:2px;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:buttons {"className":"static-pro-button","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<div class="wp-block-buttons static-pro-button"><!-- wp:button {"textAlign":"left","backgroundColor":"border-color","style":{"spacing":{"padding":{"left":"var:preset|spacing|20","right":"var:preset|spacing|20","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"radius":{"topLeft":"0px","topRight":"8px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-border-color-background-color has-background has-text-align-left wp-element-button" href="#" style="border-top-left-radius:0px;border-top-right-radius:8px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><img class="wp-image-52" style="width: 16px;" src="<?php echo esc_url( get_template_directory_uri() . '/images/arrow-right.png'); ?>" alt=""></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group"><!-- wp:image {"id":126,"sizeSlug":"full","linkDestination":"none","align":"center","className":"pro-static-img"} -->
<figure class="wp-block-image aligncenter size-full pro-static-img"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/drone06.png'); ?>" alt="" class="wp-image-126"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"medium"} -->
<p class="has-text-align-center has-border-color-color has-text-color has-link-color has-medium-font-size">Drone</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","className":"pro-static-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"fontFamily":"oswald"} -->
<h2 class="wp-block-heading has-text-align-center pro-static-heading has-oswald-font-family" style="font-size:16px;font-style:normal;font-weight:600"><a href="#"><?php echo esc_html__('Drone Ultra HD 4K & 8K','modern-drone'); ?></a></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}},"typography":{"textDecoration":"line-through"}},"textColor":"border-color","fontSize":"extra-small","fontFamily":"poppins"} -->
<p class="has-border-color-color has-text-color has-link-color has-poppins-font-family has-extra-small-font-size" style="text-decoration:line-through"><?php echo esc_html__('$789,00','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"primary","fontSize":"small","fontFamily":"poppins"} -->
<p class="has-primary-color has-text-color has-link-color has-poppins-font-family has-small-font-size" style="font-style:normal;font-weight:700"><?php echo esc_html__('$685,00','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"1px","className":"price-spacer price-spacer-pro","style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);height:1px" aria-hidden="true" class="wp-block-spacer price-spacer price-spacer-pro"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":107,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/quick-view.png'); ?>" alt="" class="wp-image-107"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":108,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/pro-heart.png'); ?>" alt="" class="wp-image-108"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"static-pro-box","style":{"border":{"width":"2px","color":"#828282","radius":{"topLeft":"10px","topRight":"10px","bottomLeft":"10px","bottomRight":"10px"}},"spacing":{"padding":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group static-pro-box has-border-color" style="border-color:#828282;border-width:2px;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:buttons {"className":"static-pro-button","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<div class="wp-block-buttons static-pro-button"><!-- wp:button {"textAlign":"left","backgroundColor":"border-color","style":{"spacing":{"padding":{"left":"var:preset|spacing|20","right":"var:preset|spacing|20","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"radius":{"topLeft":"0px","topRight":"8px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-border-color-background-color has-background has-text-align-left wp-element-button" href="#" style="border-top-left-radius:0px;border-top-right-radius:8px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><img class="wp-image-52" style="width: 16px;" src="<?php echo esc_url( get_template_directory_uri() . '/images/arrow-right.png'); ?>" alt=""></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group"><!-- wp:image {"id":127,"sizeSlug":"full","linkDestination":"none","align":"center","className":"pro-static-img"} -->
<figure class="wp-block-image aligncenter size-full pro-static-img"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/drone07.png'); ?>" alt="" class="wp-image-127"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"medium"} -->
<p class="has-text-align-center has-border-color-color has-text-color has-link-color has-medium-font-size"><?php echo esc_html__('Drone','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","className":"pro-static-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"fontFamily":"oswald"} -->
<h2 class="wp-block-heading has-text-align-center pro-static-heading has-oswald-font-family" style="font-size:16px;font-style:normal;font-weight:600"><a href="#"><?php echo esc_html__('Drone Remote Controller Pro','modern-drone'); ?></a></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}},"typography":{"textDecoration":"line-through"}},"textColor":"border-color","fontSize":"extra-small","fontFamily":"poppins"} -->
<p class="has-border-color-color has-text-color has-link-color has-poppins-font-family has-extra-small-font-size" style="text-decoration:line-through"><?php echo esc_html__('$789,00','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"primary","fontSize":"small","fontFamily":"poppins"} -->
<p class="has-primary-color has-text-color has-link-color has-poppins-font-family has-small-font-size" style="font-style:normal;font-weight:700"><?php echo esc_html__('$685,00','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"1px","className":"price-spacer price-spacer-pro","style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);height:1px" aria-hidden="true" class="wp-block-spacer price-spacer price-spacer-pro"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":107,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/quick-view.png'); ?>" alt="" class="wp-image-107"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":108,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/pro-heart.png'); ?>" alt="" class="wp-image-108"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"static-pro-box","style":{"border":{"width":"2px","color":"#828282","radius":{"topLeft":"10px","topRight":"10px","bottomLeft":"10px","bottomRight":"10px"}},"spacing":{"padding":{"bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group static-pro-box has-border-color" style="border-color:#828282;border-width:2px;border-top-left-radius:10px;border-top-right-radius:10px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:buttons {"className":"static-pro-button","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<div class="wp-block-buttons static-pro-button"><!-- wp:button {"textAlign":"left","backgroundColor":"border-color","style":{"spacing":{"padding":{"left":"var:preset|spacing|20","right":"var:preset|spacing|20","top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"border":{"radius":{"topLeft":"0px","topRight":"8px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-border-color-background-color has-background has-text-align-left wp-element-button" href="#" style="border-top-left-radius:0px;border-top-right-radius:8px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><img class="wp-image-52" style="width: 16px;" src="<?php echo esc_url( get_template_directory_uri() . '/images/arrow-right.png'); ?>" alt=""></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"90%"}} -->
<div class="wp-block-group"><!-- wp:image {"id":125,"sizeSlug":"full","linkDestination":"none","align":"center","className":"pro-static-img"} -->
<figure class="wp-block-image aligncenter size-full pro-static-img"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/drone05.png'); ?>" alt="" class="wp-image-125"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}}},"textColor":"border-color","fontSize":"medium"} -->
<p class="has-text-align-center has-border-color-color has-text-color has-link-color has-medium-font-size"><?php echo esc_html__('Drone','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","className":"pro-static-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"fontFamily":"oswald"} -->
<h2 class="wp-block-heading has-text-align-center pro-static-heading has-oswald-font-family" style="font-size:16px;font-style:normal;font-weight:600"><a href="#"><?php echo esc_html__('VisionPro 8K Cinematic Drone','modern-drone'); ?></a></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|border-color"}}},"typography":{"textDecoration":"line-through"}},"textColor":"border-color","fontSize":"extra-small","fontFamily":"poppins"} -->
<p class="has-border-color-color has-text-color has-link-color has-poppins-font-family has-extra-small-font-size" style="text-decoration:line-through"><?php echo esc_html__('$789,00','modern-drone'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"700"}},"textColor":"primary","fontSize":"small","fontFamily":"poppins"} -->
<p class="has-primary-color has-text-color has-link-color has-poppins-font-family has-small-font-size" style="font-style:normal;font-weight:700"><?php echo esc_html__('$685,00','modern-drone'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"1px","className":"price-spacer price-spacer-pro","style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
<div style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20);height:1px" aria-hidden="true" class="wp-block-spacer price-spacer price-spacer-pro"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:image {"id":107,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/quick-view.png'); ?>" alt="" class="wp-image-107"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":108,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_template_directory_uri() . '/images/pro-heart.png'); ?>" alt="" class="wp-image-108"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->
 
<?php } ?>