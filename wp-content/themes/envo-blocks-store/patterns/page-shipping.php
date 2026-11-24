<?php
/**
 * Title: Shipping & returns page
 * Slug: envo-blocks-store/page-shipping
 * Categories: envo-blocks-store, text
 * Keywords: shipping, delivery, returns, refund
 * Block Types: core/post-content
 * Post Types: page
 * Viewport width: 1100
 * Description: Shipping methods in a table, and the steps for a return.
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Shipping', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'We ship every working day. Orders placed before 2 pm leave the warehouse the same afternoon and you receive a tracking link by e-mail.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:table {"className":"eb-table"} -->
<figure class="wp-block-table eb-table"><table><thead><tr><th><?php esc_html_e( 'Method', 'envo-blocks-store' ); ?></th><th><?php esc_html_e( 'Delivery time', 'envo-blocks-store' ); ?></th><th><?php esc_html_e( 'Price', 'envo-blocks-store' ); ?></th></tr></thead><tbody><tr><td><?php esc_html_e( 'Standard', 'envo-blocks-store' ); ?></td><td><?php esc_html_e( '2–3 working days', 'envo-blocks-store' ); ?></td><td><?php esc_html_e( '$4.90, free over $50', 'envo-blocks-store' ); ?></td></tr><tr><td><?php esc_html_e( 'Express', 'envo-blocks-store' ); ?></td><td><?php esc_html_e( 'Next working day', 'envo-blocks-store' ); ?></td><td><?php esc_html_e( '$9.90', 'envo-blocks-store' ); ?></td></tr><tr><td><?php esc_html_e( 'Pick-up point', 'envo-blocks-store' ); ?></td><td><?php esc_html_e( '2–4 working days', 'envo-blocks-store' ); ?></td><td><?php esc_html_e( '$2.90', 'envo-blocks-store' ); ?></td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading {"anchor":"returns"} -->
<h2 class="wp-block-heading" id="returns"><?php esc_html_e( 'Returns', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Changed your mind? Return any unused product within 30 days, free of charge.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol class="wp-block-list"><!-- wp:list-item -->
<li><?php esc_html_e( 'Write to us with your order number.', 'envo-blocks-store' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Pack the product in its original packaging.', 'envo-blocks-store' ); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e( 'Drop it off with the prepaid label we send you.', 'envo-blocks-store' ); ?></li>
<!-- /wp:list-item --></ol>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'We refund you within 5 working days after the parcel arrives.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->
