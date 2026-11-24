<?php
/**
 * Order Received: the thank-you page of WooCommerce (the [woocommerce_checkout] output on the order received
 * endpoint: its checks of the order key and the customer, the payment instructions, the order details) in a style.
 *
 * Previews of a layout (editors, the demo site) show a sample order without the "woocommerce_thankyou" actions,
 * so no tracking code counts the order again; the editor shows a static sample when the store has no order.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_order' ) ) {
	return;
}

$envo_blocks_style   = Envo_Blocks_Blocks::choice( $attributes, 'boxStyle', array( 'cards', 'receipt', 'minimal', 'split' ) );
$envo_blocks_classes = array( 'envo-order', 'envo-order--' . $envo_blocks_style );
if ( isset( $attributes['showMessage'] ) && ! $attributes['showMessage'] ) {
	$envo_blocks_classes[] = 'envo-order--no-message';
}
if ( isset( $attributes['showCustomer'] ) && ! $attributes['showCustomer'] ) {
	$envo_blocks_classes[] = 'envo-order--no-customer';
}

$envo_blocks_order = null;
$envo_blocks_html  = '';
$envo_blocks_real  = ! Envo_Blocks_Blocks::is_preview() && is_order_received_page();
$envo_blocks_lay   = Envo_Blocks_Layouts::current();
if ( $envo_blocks_real && $envo_blocks_lay && $envo_blocks_lay['preview'] ) {
	// A preview of a layout: a shop manager may look at an order of another customer, and the design previews of
	// the demo site show the sample order (WooCommerce would ask to log in or for the e-mail address).
	$envo_blocks_id    = absint( get_query_var( 'order-received' ) );
	$envo_blocks_found = $envo_blocks_id ? wc_get_order( $envo_blocks_id ) : false;
	if ( $envo_blocks_found && ( $envo_blocks_found->get_meta( '_envo_layout_sample' ) || ( (int) $envo_blocks_found->get_customer_id() !== get_current_user_id() && current_user_can( 'edit_shop_orders' ) ) ) ) {
		$envo_blocks_real  = false;
		$envo_blocks_order = $envo_blocks_found;
	}
}

if ( $envo_blocks_real ) {
	$envo_blocks_html = do_shortcode( '[woocommerce_checkout]' );
} else {
	$envo_blocks_order = $envo_blocks_order ? $envo_blocks_order : Envo_Blocks_Layouts::sample_order();
	if ( $envo_blocks_order ) {
		global $wp_filter;
		$envo_blocks_hooks = array( 'woocommerce_thankyou', 'woocommerce_thankyou_' . $envo_blocks_order->get_payment_method() );
		$envo_blocks_saved = array();
		foreach ( $envo_blocks_hooks as $envo_blocks_hook ) {
			if ( isset( $wp_filter[ $envo_blocks_hook ] ) ) {
				$envo_blocks_saved[ $envo_blocks_hook ] = $wp_filter[ $envo_blocks_hook ];
				unset( $wp_filter[ $envo_blocks_hook ] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Put back right below.
			}
		}
		// Only the order details run in the thank-you action, inside the order box as on the real page.
		add_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		ob_start();
		echo '<div class="woocommerce">';
		wc_get_template( 'checkout/thankyou.php', array( 'order' => $envo_blocks_order ) );
		echo '</div>';
		$envo_blocks_html = ob_get_clean();
		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		foreach ( $envo_blocks_saved as $envo_blocks_hook => $envo_blocks_value ) {
			$wp_filter[ $envo_blocks_hook ] = $envo_blocks_value; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	} elseif ( Envo_Blocks_Blocks::is_preview() ) {
		$envo_blocks_html = '<div class="woocommerce"><div class="woocommerce-order">'
			. '<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">' . esc_html__( 'Thank you. Your order has been received.', 'envo-blocks' ) . '</p>'
			. '<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">'
			. '<li class="woocommerce-order-overview__order order">' . esc_html__( 'Order number:', 'envo-blocks' ) . ' <strong>1024</strong></li>'
			. '<li class="woocommerce-order-overview__date date">' . esc_html__( 'Date:', 'envo-blocks' ) . ' <strong>' . esc_html( wp_date( get_option( 'date_format' ) ) ) . '</strong></li>'
			. '<li class="woocommerce-order-overview__total total">' . esc_html__( 'Total:', 'envo-blocks' ) . ' <strong>' . wp_kses_post( wc_price( 129 ) ) . '</strong></li>'
			. '<li class="woocommerce-order-overview__payment-method method">' . esc_html__( 'Payment method:', 'envo-blocks' ) . ' <strong>' . esc_html__( 'Direct bank transfer', 'envo-blocks' ) . '</strong></li>'
			. '</ul><section class="woocommerce-order-details"><h2 class="woocommerce-order-details__title">' . esc_html__( 'Order details', 'envo-blocks' ) . '</h2>'
			. '<p>' . esc_html__( 'The products, the totals and the addresses of the order are shown here.', 'envo-blocks' ) . '</p></section></div></div>';
	}
}

if ( '' === trim( $envo_blocks_html ) ) {
	return;
}

$envo_blocks_vars = Envo_Blocks_Blocks::color_vars( $attributes, array( 'accentColor' => '--envo-accent' ) );
printf(
	'<div %1$s>%2$s</div>',
	get_block_wrapper_attributes( array_filter( array( 'class' => implode( ' ', $envo_blocks_classes ), 'style' => $envo_blocks_vars ) ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$envo_blocks_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce templates escape their output.
);
