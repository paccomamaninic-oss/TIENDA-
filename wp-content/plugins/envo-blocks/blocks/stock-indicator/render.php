<?php
/**
 * Stock Indicator: "Only {stock} left in stock" with a bar, from the real stock of the product.
 *
 * Shown only for products (or variations) whose stock WooCommerce manages; nothing is made up. For variable
 * products stock-view.js switches to the chosen variation (WooCommerce events found_variation / reset_data).
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_source = Envo_Blocks_Blocks::choice( $attributes, 'source', array( 'current', 'product' ) );
if ( 'product' === $envo_blocks_source ) {
	$envo_blocks_id = Envo_Blocks_Blocks::int( $attributes, 'productId', 0, 0, PHP_INT_MAX );
} elseif ( ! empty( $block->context['postId'] ) && 'product' === get_post_type( (int) $block->context['postId'] ) ) {
	// A product template, a product list, or the Single Product block (which passes only postId).
	$envo_blocks_id = (int) $block->context['postId'];
} else {
	$envo_blocks_id = is_singular( 'product' ) ? get_queried_object_id() : 0;
}
$envo_blocks_product = $envo_blocks_id ? wc_get_product( $envo_blocks_id ) : null;

if ( ! $envo_blocks_product ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( 'product' === $envo_blocks_source ? __( 'Choose a product in the block settings.', 'envo-blocks' ) : __( 'The stock of the current product is shown here on the product page.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_threshold = Envo_Blocks_Blocks::int( $attributes, 'threshold', 10, 1, 10000 );
$envo_blocks_full      = max( $envo_blocks_threshold, Envo_Blocks_Blocks::int( $attributes, 'barFull', 20, 1, 100000 ) );

/**
 * State of one product or variation: [ state, quantity ] or null when WooCommerce does not count its stock.
 * States: low (at most the threshold), in, out, backorder.
 */
$envo_blocks_state = static function ( $product ) use ( $envo_blocks_threshold ) {
	if ( ! $product || ! $product->managing_stock() ) {
		return null;
	}
	$qty = (int) $product->get_stock_quantity();
	if ( $qty <= 0 ) {
		return array( $product->backorders_allowed() ? 'backorder' : 'out', 0 );
	}
	return array( $qty <= $envo_blocks_threshold ? 'low' : 'in', $qty );
};

$envo_blocks_initial    = $envo_blocks_state( $envo_blocks_product );
$envo_blocks_variations = array();
if ( $envo_blocks_product->is_type( 'variable' ) ) {
	foreach ( $envo_blocks_product->get_children() as $envo_blocks_child ) {
		$envo_blocks_variation = wc_get_product( $envo_blocks_child );
		$envo_blocks_v_state   = $envo_blocks_state( $envo_blocks_variation );
		if ( $envo_blocks_v_state ) {
			$envo_blocks_variations[ $envo_blocks_child ] = $envo_blocks_v_state;
		}
	}
}

if ( ! $envo_blocks_initial && ! $envo_blocks_variations ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'WooCommerce does not count the stock of this product. Turn on "Manage stock?" in its Inventory settings to show how many are left.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_texts = array(
	'low'       => Envo_Blocks_Blocks::text( $attributes, 'lowText', __( 'Only {stock} left in stock', 'envo-blocks' ) ),
	'in'        => Envo_Blocks_Blocks::text( $attributes, 'inStockText', __( 'In stock', 'envo-blocks' ) ),
	'out'       => Envo_Blocks_Blocks::text( $attributes, 'outText', __( 'Out of stock', 'envo-blocks' ) ),
	'backorder' => Envo_Blocks_Blocks::text( $attributes, 'backorderText', __( 'Available on backorder', 'envo-blocks' ) ),
);
$envo_blocks_shown = array(
	'low'       => true,
	'in'        => ! empty( $attributes['showInStock'] ),
	'out'       => ! isset( $attributes['showOutOfStock'] ) || $attributes['showOutOfStock'],
	'backorder' => ! isset( $attributes['showOutOfStock'] ) || $attributes['showOutOfStock'],
);

$envo_blocks_text = static function ( $state ) use ( $envo_blocks_texts ) {
	return $state ? str_replace( '{stock}', number_format_i18n( $state[1] ), $envo_blocks_texts[ $state[0] ] ) : '';
};
$envo_blocks_width = static function ( $state ) use ( $envo_blocks_full ) {
	return $state ? round( min( 100, $state[1] / $envo_blocks_full * 100 ), 1 ) : 0;
};

$envo_blocks_visible = $envo_blocks_initial && $envo_blocks_shown[ $envo_blocks_initial[0] ];
if ( ! $envo_blocks_visible && ! $envo_blocks_variations && ! Envo_Blocks_Blocks::is_preview() ) {
	return;
}

$envo_blocks_config = array(
	'product'    => $envo_blocks_product->get_id(),
	'initial'    => $envo_blocks_initial,
	'variations' => (object) $envo_blocks_variations,
	'texts'      => $envo_blocks_texts,
	'shown'      => $envo_blocks_shown,
	'full'       => $envo_blocks_full,
	'locale'     => str_replace( '_', '-', determine_locale() ),
);

$envo_blocks_preview_state = $envo_blocks_initial ? $envo_blocks_initial : reset( $envo_blocks_variations );
$envo_blocks_now_state     = Envo_Blocks_Blocks::is_preview() ? $envo_blocks_preview_state : $envo_blocks_initial;
$envo_blocks_wrapper       = get_block_wrapper_attributes(
	array(
		'class' => 'sb-stock' . ( $envo_blocks_now_state ? ' is-' . $envo_blocks_now_state[0] : '' ),
		'style' => Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'barColor'   => '--sb-stock-bar',
				'trackColor' => '--sb-stock-track',
				'textColor'  => '--sb-stock-text',
			)
		) . '--sb-stock-fill:' . $envo_blocks_width( $envo_blocks_now_state ) . '%;',
	)
);
$envo_blocks_hidden = ! Envo_Blocks_Blocks::is_preview() && ! $envo_blocks_visible;
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-config="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>"<?php echo $envo_blocks_hidden ? ' hidden' : ''; ?>>
	<p class="sb-stock__text">
		<?php if ( ! isset( $attributes['showIcon'] ) || $attributes['showIcon'] ) : ?>
			<span class="sb-stock__dot" aria-hidden="true"></span>
		<?php endif; ?>
		<span data-sb-stock-text><?php echo esc_html( $envo_blocks_text( $envo_blocks_now_state ) ); ?></span>
	</p>
	<?php if ( ! isset( $attributes['showBar'] ) || $attributes['showBar'] ) : ?>
		<div class="sb-stock__bar" aria-hidden="true"><span class="sb-stock__fill"></span></div>
	<?php endif; ?>
</div>
