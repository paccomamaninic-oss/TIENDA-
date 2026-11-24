<?php
/**
 * Cart Summary: the subtotal, the savings, a coupon field and the buttons of a side cart layout. The values are
 * filled in the browser from the Store API (assets/js/side-cart-view.js); the editor preview shows the sample cart.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_preview = Envo_Blocks_Blocks::is_preview();
if ( ! $envo_blocks_preview && ! Envo_Blocks_Side_Cart::rendering() ) {
	return; // Only in the side cart panel.
}

$envo_blocks_on      = static function ( $key ) use ( $attributes ) {
	$defaults = array(
		'showSavings'  => true,
		'showCoupon'   => false,
		'showNote'     => true,
		'showViewCart' => true,
		'showCheckout' => true,
		'sticky'       => true,
	);
	return isset( $attributes[ $key ] ) ? (bool) $attributes[ $key ] : $defaults[ $key ];
};
$envo_blocks_buttons = Envo_Blocks_Blocks::choice( $attributes, 'buttons', array( 'stacked', 'row', 'checkout' ) );
$envo_blocks_classes = 'envo-sc-summary envo-sc-summary--' . $envo_blocks_buttons . ( $envo_blocks_on( 'sticky' ) ? ' is-sticky' : '' );
$envo_blocks_html    = '';

if ( $envo_blocks_on( 'showCoupon' ) ) {
	$envo_blocks_html .= '<form class="envo-sc-coupon" novalidate><label class="screen-reader-text" for="envo-sc-coupon-code">' . esc_html__( 'Coupon code', 'envo-blocks' ) . '</label>';
	$envo_blocks_html .= '<input type="text" id="envo-sc-coupon-code" name="coupon" placeholder="' . esc_attr__( 'Coupon code', 'envo-blocks' ) . '" autocomplete="off"' . ( $envo_blocks_preview ? ' readonly' : '' ) . '>';
	$envo_blocks_html .= '<button type="submit" class="envo-sc-btn envo-sc-btn--secondary">' . esc_html__( 'Apply', 'envo-blocks' ) . '</button></form>';
	/* translators: %s: coupon code. */
	$envo_blocks_html .= '<ul class="envo-sc-coupons" data-label-remove="' . esc_attr__( 'Remove the coupon %s', 'envo-blocks' ) . '"></ul>';
}
if ( $envo_blocks_on( 'showSavings' ) ) {
	$envo_blocks_html .= '<p class="envo-sc-summary__row envo-sc-summary__savings"' . ( $envo_blocks_preview ? '' : ' hidden' ) . '><span>' . esc_html__( 'You save', 'envo-blocks' ) . '</span><strong class="envo-sc-savings">' . ( $envo_blocks_preview ? esc_html( Envo_Blocks_Side_Cart::plain_price( 50 ) ) : '' ) . '</strong></p>';
}
$envo_blocks_html .= '<p class="envo-sc-summary__row envo-sc-summary__subtotal"><span>' . esc_html__( 'Subtotal', 'envo-blocks' ) . '</span><strong class="envo-sc-subtotal">' . ( $envo_blocks_preview ? esc_html( Envo_Blocks_Side_Cart::sample_subtotal() ) : '' ) . '</strong></p>';
if ( $envo_blocks_on( 'showNote' ) ) {
	$envo_blocks_html .= '<p class="envo-sc-summary__note">' . esc_html( Envo_Blocks_Blocks::text( $attributes, 'note', __( 'Shipping and taxes are calculated at checkout.', 'envo-blocks' ) ) ) . '</p>';
}

$envo_blocks_links = '';
if ( $envo_blocks_on( 'showViewCart' ) && 'checkout' !== $envo_blocks_buttons ) {
	$envo_blocks_links .= '<a class="envo-sc-btn envo-sc-btn--secondary" href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'View cart', 'envo-blocks' ) . '</a>';
}
if ( $envo_blocks_on( 'showCheckout' ) ) {
	$envo_blocks_links .= '<a class="envo-sc-btn envo-sc-btn--primary" href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html( Envo_Blocks_Blocks::text( $attributes, 'checkoutLabel', __( 'Checkout', 'envo-blocks' ) ) ) . '</a>';
}
if ( '' !== $envo_blocks_links ) {
	$envo_blocks_html .= '<div class="envo-sc-summary__buttons">' . $envo_blocks_links . '</div>';
}
if ( $envo_blocks_on( 'showViewCart' ) && 'checkout' === $envo_blocks_buttons ) {
	$envo_blocks_html .= '<p class="envo-sc-summary__link"><a href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'View cart', 'envo-blocks' ) . '</a></p>';
}

printf(
	'<div %1$s>%2$s</div>',
	get_block_wrapper_attributes( array( 'class' => $envo_blocks_classes ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$envo_blocks_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
);
