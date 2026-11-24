<?php
/**
 * Title: FAQ page
 * Slug: envo-blocks-store/page-faq
 * Categories: envo-blocks-store, text
 * Keywords: faq, questions, answers, help
 * Block Types: core/post-content
 * Post Types: page
 * Viewport width: 1100
 * Description: Frequently asked questions as expandable answers, with a contact box below.
 *
 * @package Envo_Blocks_Store
 */

$envo_blocks_store_faq = array(
	array( __( 'How long does delivery take?', 'envo-blocks-store' ), __( 'Orders placed before 2 pm on a working day leave our warehouse the same afternoon. Delivery within the country usually takes 2–3 working days.', 'envo-blocks-store' ) ),
	array( __( 'How much does shipping cost?', 'envo-blocks-store' ), __( 'Shipping is free on every order over $50. Smaller orders ship for a flat $4.90.', 'envo-blocks-store' ) ),
	array( __( 'Can I return a product?', 'envo-blocks-store' ), __( 'Yes. You can return any unused product within 30 days, free of charge.', 'envo-blocks-store' ) ),
	array( __( 'Which payment methods do you accept?', 'envo-blocks-store' ), __( 'We accept all major cards, Apple Pay and Google Pay. Payments are processed securely and we never store your card details.', 'envo-blocks-store' ) ),
	array( __( 'Do you offer gift wrapping?', 'envo-blocks-store' ), __( 'Every order can be gift-wrapped for free. Leave a note at checkout and we will add a handwritten card.', 'envo-blocks-store' ) ),
	array( __( 'How do I look after leather bags?', 'envo-blocks-store' ), __( 'Wipe them with a soft, dry cloth and treat them with a leather balm twice a year. Keep them out of direct sunlight when you are not using them.', 'envo-blocks-store' ) ),
);
?>
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Answers to the questions we hear most often. Can’t find yours? Get in touch and we will help.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<?php foreach ( $envo_blocks_store_faq as $envo_blocks_store_item ) : ?>
<!-- wp:details -->
<details class="wp-block-details"><summary><?php echo esc_html( $envo_blocks_store_item[0] ); ?></summary><!-- wp:paragraph -->
<p><?php echo esc_html( $envo_blocks_store_item[1] ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<?php endforeach; ?>
<!-- wp:group {"style":{"border":{"radius":"16px"},"spacing":{"padding":{"top":"28px","bottom":"28px","left":"28px","right":"28px"},"margin":{"top":"var:preset|spacing|50"}}},"backgroundColor":"secondary","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group has-secondary-background-color has-background" style="border-radius:16px;margin-top:var(--wp--preset--spacing--50);padding-top:28px;padding-right:28px;padding-bottom:28px;padding-left:28px"><!-- wp:group {"style":{"spacing":{"blockGap":"4px"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"1.375rem"}}} -->
<h2 class="wp-block-heading" style="font-size:1.375rem"><?php esc_html_e( 'Still have a question?', 'envo-blocks-store' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"extra-small"} -->
<p class="has-extra-small-font-size"><?php esc_html_e( 'Our team replies within one working day.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}}} -->
<p style="font-weight:600"><?php esc_html_e( 'E-mail: hello@example.com', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
