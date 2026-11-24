<?php
/**
 * Lowest Price: "Lowest price in the last 30 days: {price}" from the price history of the product (EU Omnibus rule).
 *
 * The days, when it shows and the text come from Envo Blocks > Lowest Price unless the block sets its own. For variable products lowest-view.js switches to the chosen variation (WooCommerce events
 * found_variation / reset_data); the line of every variation comes from the server in data-config.
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
	echo wp_kses_post( Envo_Blocks_Blocks::notice( 'product' === $envo_blocks_source ? __( 'Choose a product in the block settings.', 'envo-blocks' ) : __( 'The lowest price of the current product is shown here on the product page.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_days = Envo_Blocks_Blocks::int( $attributes, 'days', 0, 0, 365 );
$envo_blocks_days = $envo_blocks_days ? $envo_blocks_days : Envo_Blocks_Price_History::days();
$envo_blocks_when = Envo_Blocks_Blocks::choice( $attributes, 'showWhen', array( '', 'sale', 'always' ) );
$envo_blocks_when = '' !== $envo_blocks_when ? $envo_blocks_when : get_option( Envo_Blocks_Price_History::OPT_WHEN, 'sale' );
$envo_blocks_text = Envo_Blocks_Blocks::text( $attributes, 'text', Envo_Blocks_Price_History::text() );

$envo_blocks_line = static function ( $product ) use ( $envo_blocks_days, $envo_blocks_when, $envo_blocks_text ) {
	return Envo_Blocks_Price_History::line( $product, $envo_blocks_days, $envo_blocks_when, $envo_blocks_text );
};

$envo_blocks_initial    = $envo_blocks_line( $envo_blocks_product );
$envo_blocks_variations = array();
if ( $envo_blocks_product->is_type( 'variable' ) ) {
	foreach ( $envo_blocks_product->get_children() as $envo_blocks_child ) {
		$envo_blocks_v_line = $envo_blocks_line( wc_get_product( $envo_blocks_child ) );
		if ( '' !== $envo_blocks_v_line ) {
			$envo_blocks_variations[ $envo_blocks_child ] = $envo_blocks_v_line;
		}
	}
}

$envo_blocks_preview = Envo_Blocks_Blocks::is_preview();
if ( '' === $envo_blocks_initial && ! $envo_blocks_variations ) {
	if ( $envo_blocks_preview ) {
		echo wp_kses_post( Envo_Blocks_Blocks::notice( 'sale' === $envo_blocks_when ? __( 'This product is not on sale now: the lowest price shows only with a discount.', 'envo-blocks' ) : __( 'This product has no price.', 'envo-blocks' ) ) );
	}
	return;
}

// In the editor a variable product shows the line of its first variation that has one.
$envo_blocks_now = '' !== $envo_blocks_initial ? $envo_blocks_initial : ( $envo_blocks_preview ? reset( $envo_blocks_variations ) : '' );
$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-lowest' . ( 'box' === Envo_Blocks_Blocks::choice( $attributes, 'boxStyle', array( 'plain', 'box' ) ) ? ' sb-lowest--box' : '' ),
		'style' => Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'textColor'  => '--sb-lowest-text',
				'priceColor' => '--sb-lowest-price',
				'background' => '--sb-lowest-bg',
			)
		),
	)
);
$envo_blocks_config = array(
	'product'    => $envo_blocks_product->get_id(),
	'initial'    => $envo_blocks_initial,
	'variations' => (object) $envo_blocks_variations,
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-config="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>"<?php echo '' === $envo_blocks_now ? ' hidden' : ''; ?>>
	<p class="sb-lowest__text" data-sb-lowest-text><?php echo wp_kses_post( $envo_blocks_now ); ?></p>
</div>
