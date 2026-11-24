<?php
/**
 * Store Breadcrumbs: woocommerce_breadcrumb() with an own separator. The editor preview shows a sample path.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'woocommerce_breadcrumb' ) ) {
	return;
}

$envo_blocks_sep   = mb_substr( Envo_Blocks_Blocks::text( $attributes, 'separator', '/' ), 0, 3 );
$envo_blocks_align = Envo_Blocks_Blocks::choice( $attributes, 'textAlign', array( 'left', 'center', 'right' ) );
$envo_blocks_glue  = '<span class="envo-crumbs__sep" aria-hidden="true">' . esc_html( $envo_blocks_sep ) . '</span>';

if ( Envo_Blocks_Blocks::is_preview() ) {
	$envo_blocks_trail = '<nav class="woocommerce-breadcrumb"><a href="#">' . esc_html__( 'Home', 'envo-blocks' ) . '</a>' . $envo_blocks_glue . '<a href="#">' . esc_html__( 'Shop', 'envo-blocks' ) . '</a>' . $envo_blocks_glue . '<a href="#">' . esc_html__( 'Category', 'envo-blocks' ) . '</a>' . $envo_blocks_glue . esc_html__( 'Product', 'envo-blocks' ) . '</nav>';
} else {
	ob_start();
	woocommerce_breadcrumb(
		array(
			'delimiter'   => $envo_blocks_glue,
			'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'envo-blocks' ) . '">',
			'wrap_after'  => '</nav>',
		)
	);
	$envo_blocks_trail = ob_get_clean();
}

if ( '' === trim( wp_strip_all_tags( $envo_blocks_trail ) ) ) {
	return;
}

printf(
	'<div %1$s>%2$s</div>',
	get_block_wrapper_attributes( array( 'class' => 'envo-crumbs has-text-align-' . $envo_blocks_align ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	wp_kses_post( $envo_blocks_trail )
);
