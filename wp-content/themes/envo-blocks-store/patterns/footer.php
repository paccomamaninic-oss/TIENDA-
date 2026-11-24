<?php
/**
 * Title: Footer (simple)
 * Slug: envo-blocks-store/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"},"margin":{"top":"0"}}},"backgroundColor":"secondary","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-secondary-background-color has-background" style="margin-top:0;padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide"><!-- wp:site-title {"level":0,"style":{"typography":{"fontWeight":"700"},"elements":{"link":{"color":{"text":"var:preset|color|foreground"}}}},"fontSize":"small"} /-->

<!-- wp:paragraph {"textColor":"muted","fontSize":"extra-small"} -->
<p class="has-muted-color has-text-color has-extra-small-font-size"><?php
/* translators: %s: WordPress link. */
printf( esc_html__( 'Designed with %s', 'envo-blocks-store' ), '<a href="' . esc_url( __( 'https://wordpress.org', 'envo-blocks-store' ) ) . '" rel="nofollow">WordPress</a>' );
?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
