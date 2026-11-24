<?php
/**
 * Free Shipping Progress: how much is missing in the cart for free shipping, with a bar. The text and the bar follow
 * the cart in the browser (Store API cart, after every add to cart – also from the PRO Sticky Cart drawer).
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'WC' ) ) {
	return;
}

$envo_blocks_threshold = Envo_Blocks_Blocks::shipping_threshold( $attributes );
$envo_blocks_preview   = Envo_Blocks_Blocks::is_preview();

if ( $envo_blocks_threshold <= 0 ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Set the amount for free shipping in the block settings (or add a "Free shipping" method with a minimum order amount).', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_total     = $envo_blocks_preview ? $envo_blocks_threshold * 0.4 : Envo_Blocks_Blocks::shipping_cart_total();
$envo_blocks_remaining = max( 0.0, $envo_blocks_threshold - $envo_blocks_total );
$envo_blocks_percent   = (int) max( 0, min( 100, floor( $envo_blocks_total / $envo_blocks_threshold * 100 ) ) );
$envo_blocks_empty     = ! $envo_blocks_preview && $envo_blocks_total <= 0 && WC()->cart && WC()->cart->is_empty();

$envo_blocks_messages = array();
foreach ( array(
	'messageProgress' => __( 'Add {remaining} more to get free shipping!', 'envo-blocks' ),
	'messageReached'  => __( 'You have free shipping!', 'envo-blocks' ),
	'messageEmpty'    => __( 'Free shipping on orders over {threshold}.', 'envo-blocks' ),
) as $envo_blocks_key => $envo_blocks_default ) {
	$envo_blocks_messages[ $envo_blocks_key ] = Envo_Blocks_Blocks::text( $attributes, $envo_blocks_key, $envo_blocks_default );
}

if ( $envo_blocks_remaining <= 0 ) {
	$envo_blocks_state = 'reached';
} elseif ( $envo_blocks_empty ) {
	$envo_blocks_state = 'empty';
} else {
	$envo_blocks_state = 'progress';
}

$envo_blocks_hide = ! empty( $attributes['hideWhenReached'] );
$envo_blocks_text = str_replace(
	array( '{remaining}', '{threshold}' ),
	array( wc_price( $envo_blocks_remaining ), wc_price( $envo_blocks_threshold ) ),
	esc_html( $envo_blocks_messages[ 'message' . ucfirst( $envo_blocks_state ) ] )
);

// Currency settings for the live update in the browser (the text is formatted there as plain text).
$envo_blocks_config = array(
	'threshold' => $envo_blocks_threshold,
	'messages'  => $envo_blocks_messages,
	'hide'      => $envo_blocks_hide,
	'inclTax'   => (bool) ( WC()->cart && WC()->cart->display_prices_including_tax() ),
	'restUrl'   => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
	'currency'  => array(
		'symbol'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
		'format'   => get_woocommerce_price_format(),
		'decimals' => wc_get_price_decimals(),
		'decimal'  => wc_get_price_decimal_separator(),
		'thousand' => wc_get_price_thousand_separator(),
	),
);

$envo_blocks_classes = array( 'sb-ship', 'woocommerce', 'sb-ship--' . $envo_blocks_state, 'sb-ship--align-' . Envo_Blocks_Blocks::choice( $attributes, 'textAlign', array( 'center', 'left', 'right' ) ) );
if ( 'reached' === $envo_blocks_state && $envo_blocks_hide && ! $envo_blocks_preview ) {
	$envo_blocks_classes[] = 'is-hidden';
}
if ( '' !== Envo_Blocks_Blocks::color( isset( $attributes['textColor'] ) ? $attributes['textColor'] : '' ) ) {
	$envo_blocks_classes[] = 'sb-has-ship-text';
}
if ( '' !== Envo_Blocks_Blocks::color( isset( $attributes['background'] ) ? $attributes['background'] : '' ) ) {
	$envo_blocks_classes[] = 'sb-has-ship-bg';
}

$envo_blocks_extra = array(
	'class' => implode( ' ', $envo_blocks_classes ),
	'style' => Envo_Blocks_Blocks::color_vars(
		$attributes,
		array(
			'textColor'    => '--sb-ship-text',
			'barColor'     => '--sb-ship-bar',
			'trackColor'   => '--sb-ship-track',
			'successColor' => '--sb-ship-success',
			'background'   => '--sb-ship-bg',
		)
	) . '--sb-ship-height:' . Envo_Blocks_Blocks::int( $attributes, 'barHeight', 8, 2, 30 ) . 'px;',
);
if ( $envo_blocks_preview ) {
	$envo_blocks_extra['data-preview'] = '1';
}
$envo_blocks_wrapper = get_block_wrapper_attributes( $envo_blocks_extra );
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-sb-ship="<?php echo esc_attr( (string) wp_json_encode( $envo_blocks_config ) ); ?>">
	<p class="sb-ship__text" aria-live="polite"><?php echo wp_kses_post( $envo_blocks_text ); ?></p>
	<?php if ( ! isset( $attributes['showBar'] ) || $attributes['showBar'] ) : ?>
		<div class="sb-ship__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) $envo_blocks_percent ); ?>" aria-label="<?php esc_attr_e( 'Progress to free shipping', 'envo-blocks' ); ?>"><span class="sb-ship__fill" style="width:<?php echo esc_attr( (string) $envo_blocks_percent ); ?>%"></span></div>
	<?php endif; ?>
</div>
