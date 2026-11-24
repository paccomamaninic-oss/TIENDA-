<?php
/**
 * Checkout Form: the WooCommerce checkout of the store inside a checkout layout. It renders the content of the
 * checkout page as it is (the Checkout block with its inner blocks, or the [woocommerce_checkout] shortcode of a
 * classic checkout), so payment methods, shipping, coupons and checkout plugins work untouched; the block only
 * arranges it (two columns, one column, the summary on top, a sticky summary). The editor shows a sketch.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_layout = Envo_Blocks_Blocks::choice( $attributes, 'formLayout', array( 'two-columns', 'one-column', 'summary-top' ) );
$envo_blocks_sticky = ! isset( $attributes['stickySummary'] ) || $attributes['stickySummary'];
$envo_blocks_box    = ! isset( $attributes['summaryBox'] ) || $attributes['summaryBox'];
$envo_blocks_class  = 'envo-checkout envo-checkout--' . $envo_blocks_layout . ( $envo_blocks_sticky ? ' is-sticky' : '' ) . ( $envo_blocks_box ? ' has-summary-box' : '' );

if ( Envo_Blocks_Blocks::is_preview() ) {
	// A sketch of the checkout: the real form of the store is shown on the site.
	$envo_blocks_field = static function ( $label ) {
		return '<span class="envo-checkout-sketch__field"><span>' . esc_html( $label ) . '</span></span>';
	};
	$envo_blocks_html  = '<div class="envo-checkout-sketch">';
	$envo_blocks_html .= '<div class="envo-checkout-sketch__main">';
	$envo_blocks_html .= '<p class="envo-checkout-sketch__title">' . esc_html__( 'Contact information', 'envo-blocks' ) . '</p>' . $envo_blocks_field( __( 'Email address', 'envo-blocks' ) );
	$envo_blocks_html .= '<p class="envo-checkout-sketch__title">' . esc_html__( 'Shipping address', 'envo-blocks' ) . '</p><span class="envo-checkout-sketch__row">' . $envo_blocks_field( __( 'First name', 'envo-blocks' ) ) . $envo_blocks_field( __( 'Last name', 'envo-blocks' ) ) . '</span>' . $envo_blocks_field( __( 'Address', 'envo-blocks' ) ) . '<span class="envo-checkout-sketch__row">' . $envo_blocks_field( __( 'City', 'envo-blocks' ) ) . $envo_blocks_field( __( 'Postcode', 'envo-blocks' ) ) . '</span>';
	$envo_blocks_html .= '<p class="envo-checkout-sketch__title">' . esc_html__( 'Payment options', 'envo-blocks' ) . '</p>' . $envo_blocks_field( __( 'Direct bank transfer', 'envo-blocks' ) ) . $envo_blocks_field( __( 'Cash on delivery', 'envo-blocks' ) );
	$envo_blocks_html .= '<span class="envo-checkout-sketch__button">' . esc_html__( 'Place order', 'envo-blocks' ) . '</span></div>';
	$envo_blocks_html .= '<div class="envo-checkout-sketch__summary"><p class="envo-checkout-sketch__title">' . esc_html__( 'Order summary', 'envo-blocks' ) . '</p>';
	foreach ( class_exists( 'Envo_Blocks_Side_Cart' ) ? Envo_Blocks_Side_Cart::sample_items() : array() as $envo_blocks_item ) {
		$envo_blocks_html .= '<span class="envo-checkout-sketch__item"><img src="' . esc_url( $envo_blocks_item['image'] ) . '" alt=""><span>' . esc_html( $envo_blocks_item['name'] ) . '</span><strong>' . esc_html( $envo_blocks_item['price'] ) . '</strong></span>';
	}
	$envo_blocks_html .= '<span class="envo-checkout-sketch__total"><span>' . esc_html__( 'Total', 'envo-blocks' ) . '</span><strong>' . esc_html( class_exists( 'Envo_Blocks_Side_Cart' ) ? Envo_Blocks_Side_Cart::sample_subtotal() : '' ) . '</strong></span></div></div>';
	$envo_blocks_html .= '<p class="envo-checkout-sketch__note">' . esc_html__( 'A sketch: the store shows its own WooCommerce checkout here, with your payment methods and shipping.', 'envo-blocks' ) . '</p>';
} else {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url() ) {
		return; // Only on the checkout itself.
	}
	$envo_blocks_page    = get_post( wc_get_page_id( 'checkout' ) );
	$envo_blocks_content = $envo_blocks_page ? (string) $envo_blocks_page->post_content : '';
	if ( ! has_block( 'woocommerce/checkout', $envo_blocks_content ) && ! has_shortcode( $envo_blocks_content, 'woocommerce_checkout' ) ) {
		$envo_blocks_content = '[woocommerce_checkout]'; // A checkout page without the checkout: the classic one.
	}
	/**
	 * Filters the content the Checkout Form block renders (the content of the checkout page).
	 *
	 * @param string $content Block markup or shortcodes.
	 */
	$envo_blocks_content = (string) apply_filters( 'envo_blocks_checkout_form_content', $envo_blocks_content );
	// As the_content does: the blocks, then the shortcodes (a Shortcode block only keeps its text).
	$envo_blocks_html = has_blocks( $envo_blocks_content ) ? do_blocks( $envo_blocks_content ) : shortcode_unautop( wpautop( $envo_blocks_content ) );
	$envo_blocks_html = do_shortcode( $envo_blocks_html );
}

printf(
	'<div %1$s>%2$s</div>',
	get_block_wrapper_attributes( array( 'class' => $envo_blocks_class ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$envo_blocks_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The checkout of WooCommerce (escaped by it) or the sketch above.
);
