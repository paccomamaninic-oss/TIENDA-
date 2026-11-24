<?php
/**
 * Title: Home: latest posts
 * Slug: envo-blocks-store/home-blog
 * Categories: envo-blocks-store, posts, query
 * Keywords: blog, posts, news
 * Block Types: core/query
 * Viewport width: 1400
 * Description: The three latest posts as cards.
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:group {"align":"full","className":"eb-section eb-blog","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull eb-section eb-blog" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.75rem, 2.6vw, 2.25rem)"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-size:clamp(1.75rem, 2.6vw, 2.25rem)"><?php esc_html_e( 'From our blog', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:query {"queryId":13,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<div class="wp-block-query alignwide" style="margin-top:var(--wp--preset--spacing--40)"><!-- wp:post-template {"className":"eb-posts","style":{"spacing":{"blockGap":"20px"}},"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"eb-post","style":{"border":{"radius":"16px","width":"1px"},"spacing":{"padding":{"top":"10px","bottom":"24px","left":"10px","right":"10px"},"blockGap":"12px"}},"borderColor":"line","layout":{"type":"default"}} -->
<div class="wp-block-group eb-post has-border-color has-line-border-color" style="border-width:1px;border-radius:16px;padding-top:10px;padding-right:10px;padding-bottom:24px;padding-left:10px"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","style":{"border":{"radius":"10px"}}} /-->

<!-- wp:group {"style":{"spacing":{"padding":{"left":"14px","right":"14px"},"blockGap":"8px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group" style="padding-right:14px;padding-left:14px"><!-- wp:post-date /-->

<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"1.125rem","lineHeight":"1.4"},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}}} /-->

<!-- wp:post-excerpt {"excerptLength":18,"textColor":"muted","fontSize":"extra-small"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
