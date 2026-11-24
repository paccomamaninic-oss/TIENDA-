<?php
/**
 * Shop Toolbar: the number of products found and the sorting menu of the main product query (shop, categories,
 * tags, product search). Elsewhere it renders nothing; the editor preview shows a sample.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_count = ! isset( $attributes['showCount'] ) || $attributes['showCount'];
$envo_blocks_sort  = ! isset( $attributes['showSorting'] ) || $attributes['showSorting'];
$envo_blocks_style = Envo_Blocks_Blocks::choice( $attributes, 'barStyle', array( 'plain', 'bar', 'pill' ) );

if ( Envo_Blocks_Blocks::is_preview() ) {
	$envo_blocks_html = ( $envo_blocks_count ? '<p class="woocommerce-result-count">' . esc_html__( 'Showing 1–12 of 48 results', 'envo-blocks' ) . '</p>' : '' )
		. ( $envo_blocks_sort ? '<form class="woocommerce-ordering"><select class="orderby" aria-label="' . esc_attr__( 'Shop order', 'envo-blocks' ) . '"><option>' . esc_html__( 'Default sorting', 'envo-blocks' ) . '</option></select></form>' : '' );
} else {
	if ( ! function_exists( 'woocommerce_result_count' ) || ! ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) ) {
		return;
	}
	wc_setup_loop(); // The totals of the main query, as the shop template does.
	ob_start();
	if ( $envo_blocks_count ) {
		woocommerce_result_count();
	}
	if ( $envo_blocks_sort ) {
		woocommerce_catalog_ordering();
	}
	$envo_blocks_html = ob_get_clean();
}

if ( '' === trim( $envo_blocks_html ) ) {
	return;
}

printf(
	'<div %1$s>%2$s</div>',
	get_block_wrapper_attributes( array( 'class' => 'envo-toolbar envo-toolbar--' . $envo_blocks_style ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$envo_blocks_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the WooCommerce templates (or the static preview above).
);
