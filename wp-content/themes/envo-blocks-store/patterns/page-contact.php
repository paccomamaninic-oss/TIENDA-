<?php
/**
 * Title: Contact page
 * Slug: envo-blocks-store/page-contact
 * Categories: envo-blocks-store, contact
 * Keywords: contact, address, email, phone, opening hours
 * Block Types: core/post-content
 * Post Types: page
 * Viewport width: 1100
 * Description: Contact details, opening hours and social links in cards.
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Questions about an order or a product? Write to us or call, we reply within one working day.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"20px","left":"20px"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"28px","bottom":"28px","left":"28px","right":"28px"},"blockGap":"10px"}},"backgroundColor":"secondary"} -->
<div class="wp-block-column has-secondary-background-color has-background" style="border-radius:16px;padding-top:28px;padding-right:28px;padding-bottom:28px;padding-left:28px"><!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"1.25rem"}}} -->
<h2 class="wp-block-heading" style="font-size:1.25rem"><?php esc_html_e( 'Get in touch', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'E-mail: hello@example.com', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Phone: +1 555 0100 200', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Address: 123 Market Street, Springfield', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"28px","bottom":"28px","left":"28px","right":"28px"},"blockGap":"10px"}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="border-radius:16px;padding-top:28px;padding-right:28px;padding-bottom:28px;padding-left:28px"><!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"1.25rem"}}} -->
<h2 class="wp-block-heading" style="font-size:1.25rem"><?php esc_html_e( 'Opening hours', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Monday – Friday: 9:00 – 18:00', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Saturday: 10:00 – 14:00', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Sunday: closed', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"1.25rem"},"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--50);font-size:1.25rem"><?php esc_html_e( 'Follow us', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:social-links {"iconColor":"background","iconColorValue":"#ffffff","iconBackgroundColor":"primary","iconBackgroundColorValue":"#0088cc"} -->
<ul class="wp-block-social-links has-icon-color has-icon-background-color"><!-- wp:social-link {"url":"#","service":"facebook"} /-->

<!-- wp:social-link {"url":"#","service":"instagram"} /-->

<!-- wp:social-link {"url":"#","service":"x"} /-->

<!-- wp:social-link {"url":"#","service":"youtube"} /--></ul>
<!-- /wp:social-links -->
