<?php
/**
 * Order Tracking: the WooCommerce tracking form ([woocommerce_order_tracking]) with a heading, a text and colors.
 * After a successful search WooCommerce shows the order and its updates in the same place.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_title = Envo_Blocks_Blocks::text( $attributes, 'title', __( 'Track your order', 'envo-blocks' ) );
$envo_blocks_text  = Envo_Blocks_Blocks::text( $attributes, 'description', __( 'Enter your order number and the email address you used at checkout. The order number is in the confirmation email.', 'envo-blocks' ) );

if ( Envo_Blocks_Blocks::is_preview() ) {
	// The editor preview runs in the REST API, where the WooCommerce shortcode has no front end: a static copy.
	$envo_blocks_form = '<form class="woocommerce-form woocommerce-form-track-order track_order">'
		. '<p class="form-row form-row-first"><label>' . esc_html__( 'Order ID', 'envo-blocks' ) . '</label><input class="input-text" type="text" placeholder="' . esc_attr__( 'Found in your order confirmation email.', 'envo-blocks' ) . '"></p>'
		. '<p class="form-row form-row-last"><label>' . esc_html__( 'Billing email', 'envo-blocks' ) . '</label><input class="input-text" type="email" placeholder="' . esc_attr__( 'Email you used during checkout.', 'envo-blocks' ) . '"></p>'
		. '<div class="clear"></div><p class="form-row"><button type="button" class="button">' . esc_html__( 'Track', 'envo-blocks' ) . '</button></p></form>';
	$envo_blocks_id = '';
} else {
	// The form carries a fresh nonce: pages with it must not come from a page cache.
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	if ( ! headers_sent() ) {
		nocache_headers();
	}

	// With more forms on a page only the one that was sent shows the order; the page jumps to it.
	$envo_blocks_id    = 'envo-blocks-tracking-' . Envo_Blocks_Blocks::instance( 'order-tracking' );
	$envo_blocks_nonce = isset( $_REQUEST['woocommerce-order-tracking-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-order-tracking-nonce'] ) ) : '';
	$envo_blocks_sent  = '';
	if ( $envo_blocks_nonce && wp_verify_nonce( $envo_blocks_nonce, 'woocommerce-order_tracking' ) && isset( $_REQUEST['envo_blocks_tracking'] ) ) {
		$envo_blocks_sent = sanitize_key( wp_unslash( $_REQUEST['envo_blocks_tracking'] ) );
	}
	if ( '' !== $envo_blocks_sent && $envo_blocks_sent !== $envo_blocks_id ) {
		ob_start();
		wc_get_template( 'order/form-tracking.php' );
		$envo_blocks_form = ob_get_clean();
	} else {
		$envo_blocks_form = do_shortcode( '[woocommerce_order_tracking]' );
	}
	$envo_blocks_form = preg_replace( '/(<form[^>]*\saction="[^"#]*)"/', '$1#' . $envo_blocks_id . '"', $envo_blocks_form, 1 );
	$envo_blocks_form = str_replace( '</form>', '<input type="hidden" name="envo_blocks_tracking" value="' . esc_attr( $envo_blocks_id ) . '"></form>', $envo_blocks_form );
	if ( false === strpos( $envo_blocks_form, '</form>' ) ) {
		// The order was found: a way back to the empty form.
		$envo_blocks_form .= '<p class="sb-tracking__again"><a href="' . esc_url( get_permalink() . '#' . $envo_blocks_id ) . '">' . esc_html__( 'Track another order', 'envo-blocks' ) . '</a></p>';
	}
}

$envo_blocks_style   = Envo_Blocks_Blocks::choice( $attributes, 'boxStyle', array( 'box', 'plain' ) );
$envo_blocks_align   = Envo_Blocks_Blocks::choice( $attributes, 'textAlign', array( 'left', 'center' ) );
$envo_blocks_wrapper = get_block_wrapper_attributes(
	array_filter(
		array(
			'id'    => $envo_blocks_id,
			'class' => 'sb-tracking sb-tracking--' . $envo_blocks_style . ' sb-align-' . $envo_blocks_align,
			'style' => Envo_Blocks_Blocks::color_vars(
				$attributes,
				array(
					'background'  => '--sb-tracking-bg',
					'textColor'   => '--sb-tracking-text',
					'buttonColor' => '--sb-tracking-btn-bg',
					'buttonText'  => '--sb-tracking-btn-text',
				)
			) . '--sb-tracking-width:' . Envo_Blocks_Blocks::int( $attributes, 'maxWidth', 640, 320, 1200 ) . 'px;',
		)
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<div class="sb-tracking__inner">
		<?php if ( '' !== $envo_blocks_title ) : ?>
			<h2 class="sb-tracking__title"><?php echo esc_html( $envo_blocks_title ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $envo_blocks_text ) : ?>
			<p class="sb-tracking__text"><?php echo esc_html( $envo_blocks_text ); ?></p>
		<?php endif; ?>
		<div class="sb-tracking__form">
			<?php echo wp_kses( $envo_blocks_form, Envo_Blocks_Blocks::allowed_html( true ) ); ?>
		</div>
	</div>
</div>
