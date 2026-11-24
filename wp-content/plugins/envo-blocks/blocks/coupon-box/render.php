<?php
/**
 * Coupon Code Box: a WooCommerce coupon with copy and apply buttons.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_code = Envo_Blocks_Blocks::text( $attributes, 'code' );

if ( '' === $envo_blocks_code ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Enter the coupon code in the block settings.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_coupon_id = function_exists( 'wc_get_coupon_id_by_code' ) ? wc_get_coupon_id_by_code( $envo_blocks_code ) : 0;
$envo_blocks_coupon    = $envo_blocks_coupon_id ? new WC_Coupon( $envo_blocks_coupon_id ) : null;
$envo_blocks_expires   = $envo_blocks_coupon ? $envo_blocks_coupon->get_date_expires() : null;
$envo_blocks_expired   = $envo_blocks_expires && $envo_blocks_expires->getTimestamp() < time();
$envo_blocks_used_up   = $envo_blocks_coupon && $envo_blocks_coupon->get_usage_limit() > 0 && $envo_blocks_coupon->get_usage_count() >= $envo_blocks_coupon->get_usage_limit();
$envo_blocks_valid     = $envo_blocks_coupon && 'publish' === get_post_status( $envo_blocks_coupon_id ) && ! $envo_blocks_expired && ! $envo_blocks_used_up;

if ( ! $envo_blocks_valid ) {
	if ( Envo_Blocks_Blocks::is_preview() ) {
		$envo_blocks_reason = ! $envo_blocks_coupon
			/* translators: %s: coupon code */
			? sprintf( __( 'Coupon "%s" does not exist in WooCommerce. The box is shown, but the discount cannot be read.', 'envo-blocks' ), $envo_blocks_code )
			/* translators: %s: coupon code */
			: sprintf( __( 'Coupon "%s" has expired, is used up or not published.', 'envo-blocks' ), $envo_blocks_code );
		echo wp_kses_post( Envo_Blocks_Blocks::notice( $envo_blocks_reason ) );
	} elseif ( ! isset( $attributes['hideInvalid'] ) || $attributes['hideInvalid'] ) {
		return;
	}
}

// Discount and conditions read from the coupon.
$envo_blocks_discount = '';
$envo_blocks_meta     = array();

if ( $envo_blocks_coupon ) {
	$envo_blocks_amount = (float) $envo_blocks_coupon->get_amount();

	if ( ! empty( $attributes['showDiscount'] ) && $envo_blocks_amount > 0 ) {
		$envo_blocks_discount = 'percent' === $envo_blocks_coupon->get_discount_type()
			/* translators: %s: percentage */
			? sprintf( __( '%s%% off', 'envo-blocks' ), wc_format_localized_decimal( $envo_blocks_amount ) )
			/* translators: %s: amount */
			: sprintf( __( '%s off', 'envo-blocks' ), wp_strip_all_tags( wc_price( $envo_blocks_amount ) ) );
	}

	if ( ! empty( $attributes['showDiscount'] ) && $envo_blocks_coupon->get_free_shipping() ) {
		$envo_blocks_discount .= ( $envo_blocks_discount ? ' + ' : '' ) . __( 'free shipping', 'envo-blocks' );
	}

	if ( ! empty( $attributes['showMinimum'] ) && (float) $envo_blocks_coupon->get_minimum_amount() > 0 ) {
		/* translators: %s: minimum order amount */
		$envo_blocks_meta[] = sprintf( __( 'Minimum order %s', 'envo-blocks' ), wp_strip_all_tags( wc_price( $envo_blocks_coupon->get_minimum_amount() ) ) );
	}

	if ( ! empty( $attributes['showExpiry'] ) && $envo_blocks_expires ) {
		/* translators: %s: date */
		$envo_blocks_meta[] = sprintf( __( 'Valid until %s', 'envo-blocks' ), wc_format_datetime( $envo_blocks_expires ) );
	}
}

$envo_blocks_title       = Envo_Blocks_Blocks::text( $attributes, 'title', $envo_blocks_discount ? $envo_blocks_discount : __( 'Special offer', 'envo-blocks' ) );
$envo_blocks_description = Envo_Blocks_Blocks::text( $attributes, 'description', $envo_blocks_coupon && $envo_blocks_coupon->get_description() ? $envo_blocks_coupon->get_description() : '' );
$envo_blocks_copy        = Envo_Blocks_Blocks::text( $attributes, 'copyText', __( 'Copy code', 'envo-blocks' ) );
$envo_blocks_copied      = Envo_Blocks_Blocks::text( $attributes, 'copiedText', __( 'Copied!', 'envo-blocks' ) );
$envo_blocks_apply       = Envo_Blocks_Blocks::text( $attributes, 'applyText', __( 'Apply to cart', 'envo-blocks' ) );
$envo_blocks_show_apply  = ! empty( $attributes['applyButton'] ) && $envo_blocks_valid;

$envo_blocks_box    = Envo_Blocks_Blocks::choice( $attributes, 'boxStyle', array( 'dashed', 'solid', 'ticket', 'filled' ) );
$envo_blocks_layout = Envo_Blocks_Blocks::choice( $attributes, 'layout', array( 'row', 'stacked' ) );

$envo_blocks_config = array(
	'code'    => $envo_blocks_code,
	'copied'  => $envo_blocks_copied,
	'applied' => __( 'Coupon applied to your cart.', 'envo-blocks' ),
	'failed'  => __( 'The coupon could not be applied.', 'envo-blocks' ),
);

if ( $envo_blocks_show_apply ) {
	$envo_blocks_config['restUrl'] = esc_url_raw( rest_url( 'wc/store/v1/cart/apply-coupon' ) );
	$envo_blocks_config['nonce']   = wp_create_nonce( 'wc_store_api' );
}

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-coupon sb-coupon--' . $envo_blocks_box . ' sb-coupon--' . $envo_blocks_layout,
		'style' => Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'accentColor'    => '--sb-coupon-accent',
				'textColor'      => '--sb-coupon-text',
				'background'     => '--sb-coupon-bg',
				'codeBackground' => '--sb-coupon-code-bg',
				'buttonColor'    => '--sb-coupon-btn-bg',
				'buttonText'     => '--sb-coupon-btn-text',
			)
		),
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-config="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>">
	<div class="sb-coupon__info">
		<span class="sb-coupon__badge"><?php echo wp_kses( Envo_Blocks_Blocks::icon( 'tag' ), Envo_Blocks_Blocks::allowed_html() ); ?></span>
		<div class="sb-coupon__texts">
			<p class="sb-coupon__title"><?php echo esc_html( $envo_blocks_title ); ?></p>
			<?php if ( '' !== $envo_blocks_description ) : ?>
				<p class="sb-coupon__desc"><?php echo esc_html( $envo_blocks_description ); ?></p>
			<?php endif; ?>
			<?php if ( $envo_blocks_meta ) : ?>
				<p class="sb-coupon__meta"><?php echo esc_html( implode( ' · ', $envo_blocks_meta ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="sb-coupon__actions">
		<button type="button" class="sb-coupon__code" data-sb-copy aria-label="<?php echo esc_attr( $envo_blocks_copy . ': ' . $envo_blocks_code ); ?>">
			<span class="sb-coupon__value"><?php echo esc_html( $envo_blocks_code ); ?></span>
			<span class="sb-coupon__copy"><?php echo wp_kses( Envo_Blocks_Blocks::icon( 'copy' ), Envo_Blocks_Blocks::allowed_html() ); ?><span data-sb-copy-label><?php echo esc_html( $envo_blocks_copy ); ?></span></span>
		</button>
		<?php if ( $envo_blocks_show_apply ) : ?>
			<button type="button" class="sb-coupon__apply wp-element-button" data-sb-apply><?php echo esc_html( $envo_blocks_apply ); ?></button>
		<?php endif; ?>
	</div>
	<p class="sb-coupon__status" role="status" aria-live="polite"></p>
</div>
