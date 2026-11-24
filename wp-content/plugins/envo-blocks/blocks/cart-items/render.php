<?php
/**
 * Cart Items: the products in the cart of a side cart layout. The list is filled in the browser from the Store API
 * (assets/js/side-cart-view.js), so the HTML is the same for every visitor; the editor preview shows sample items.
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

$envo_blocks_style   = Envo_Blocks_Blocks::choice( $attributes, 'itemStyle', array( 'list', 'compact', 'cards' ) );
$envo_blocks_heading = Envo_Blocks_Blocks::text( $attributes, 'heading', __( 'Your cart', 'envo-blocks' ) );
$envo_blocks_empty   = Envo_Blocks_Blocks::text( $attributes, 'emptyText', __( 'Your cart is empty.', 'envo-blocks' ) );
$envo_blocks_flags   = array(
	'showImages'   => 'no-images',
	'showQuantity' => 'no-quantity',
	'showRemove'   => 'no-remove',
);
$envo_blocks_classes = array( 'envo-sc-items', 'envo-sc-items--' . $envo_blocks_style );
foreach ( $envo_blocks_flags as $envo_blocks_key => $envo_blocks_class ) {
	if ( isset( $attributes[ $envo_blocks_key ] ) && ! $attributes[ $envo_blocks_key ] ) {
		$envo_blocks_classes[] = 'is-' . $envo_blocks_class;
	}
}

$envo_blocks_html = '';
if ( ! isset( $attributes['showHeading'] ) || $attributes['showHeading'] ) {
	$envo_blocks_count = '';
	if ( ! isset( $attributes['showCount'] ) || $attributes['showCount'] ) {
		/* translators: %d: number of items. */
		$envo_blocks_count = '<span class="envo-sc-items__count">' . ( $envo_blocks_preview ? esc_html( sprintf( _n( '%d item', '%d items', 3, 'envo-blocks' ), 3 ) ) : '' ) . '</span>';
	}
	$envo_blocks_html .= '<div class="envo-sc-items__head"><h2 class="envo-sc-items__title">' . esc_html( $envo_blocks_heading ) . '</h2>' . $envo_blocks_count . '</div>';
}

$envo_blocks_html .= '<ul class="envo-sc-items__list">';
if ( $envo_blocks_preview ) {
	foreach ( Envo_Blocks_Side_Cart::sample_items() as $envo_blocks_item ) {
		$envo_blocks_html .= '<li class="envo-sc-item"><span class="envo-sc-item__image"><img src="' . esc_url( $envo_blocks_item['image'] ) . '" alt=""></span>';
		$envo_blocks_html .= '<div class="envo-sc-item__main"><span class="envo-sc-item__name">' . esc_html( $envo_blocks_item['name'] ) . '</span>';
		if ( '' !== $envo_blocks_item['meta'] ) {
			$envo_blocks_html .= '<span class="envo-sc-item__meta">' . esc_html( $envo_blocks_item['meta'] ) . '</span>';
		}
		$envo_blocks_html .= '<div class="envo-sc-item__actions"><span class="envo-sc-qty"><button type="button">−</button><input type="number" value="' . (int) $envo_blocks_item['quantity'] . '" readonly><button type="button">+</button></span>';
		$envo_blocks_html .= '<button type="button" class="envo-sc-item__remove">' . esc_html__( 'Remove', 'envo-blocks' ) . '</button></div></div>';
		$envo_blocks_html .= '<span class="envo-sc-item__price">' . ( '' !== $envo_blocks_item['regular'] ? '<del>' . esc_html( $envo_blocks_item['regular'] ) . '</del> ' : '' ) . esc_html( $envo_blocks_item['price'] ) . '</span></li>';
	}
}
$envo_blocks_html .= '</ul>';

$envo_blocks_html .= '<div class="envo-sc-items__empty"><p>' . esc_html( $envo_blocks_empty ) . '</p>';
if ( ! isset( $attributes['emptyButton'] ) || $attributes['emptyButton'] ) {
	$envo_blocks_html .= '<a class="envo-sc-btn envo-sc-btn--primary" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Start shopping', 'envo-blocks' ) . '</a>';
}
$envo_blocks_html .= '</div>';

printf(
	'<div %1$s data-label-remove="%3$s">%2$s</div>',
	get_block_wrapper_attributes( array( 'class' => implode( ' ', $envo_blocks_classes ) ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$envo_blocks_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
	esc_attr__( 'Remove', 'envo-blocks' )
);
