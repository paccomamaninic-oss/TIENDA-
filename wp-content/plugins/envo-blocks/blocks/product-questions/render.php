<?php
/**
 * Product Questions: the answered questions of a product and the form to ask one (see Envo_Blocks_Product_Questions).
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_source = Envo_Blocks_Blocks::choice( $attributes, 'source', array( 'current', 'product' ) );
if ( 'product' === $envo_blocks_source ) {
	$envo_blocks_id = Envo_Blocks_Blocks::int( $attributes, 'productId', 0, 0, PHP_INT_MAX );
} elseif ( ! empty( $block->context['postId'] ) && 'product' === get_post_type( (int) $block->context['postId'] ) ) {
	$envo_blocks_id = (int) $block->context['postId'];
} else {
	$envo_blocks_id = is_singular( 'product' ) ? get_queried_object_id() : 0;
}

if ( ! $envo_blocks_id || 'product' !== get_post_type( $envo_blocks_id ) ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( 'product' === $envo_blocks_source ? __( 'Choose a product in the block settings.', 'envo-blocks' ) : __( 'The questions of the current product are shown here on the product page.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_style = Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'accentColor' => '--sb-pq-accent',
		'textColor'   => '--sb-pq-text',
		'background'  => '--sb-pq-bg',
		'borderColor' => '--sb-pq-border',
	)
);

$envo_blocks_html = Envo_Blocks_Product_Questions::html(
	$envo_blocks_id,
	array(
		'title'      => isset( $attributes['title'] ) && '' !== trim( $attributes['title'] ) ? $attributes['title'] : __( 'Questions & answers', 'envo-blocks' ),
		'perPage'    => Envo_Blocks_Blocks::int( $attributes, 'perPage', 5, 1, 50 ),
		'showSearch' => ! isset( $attributes['showSearch'] ) || ! empty( $attributes['showSearch'] ),
		'showForm'   => ! isset( $attributes['showForm'] ) || ! empty( $attributes['showForm'] ),
		'wrapper'    => get_block_wrapper_attributes(
			array(
				'class' => 'sb-pq' . ( ! empty( $attributes['background'] ) ? ' sb-pq--has-bg' : '' ),
				'style' => $envo_blocks_style,
			)
		),
	)
);
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in html() and by get_block_wrapper_attributes().
echo $envo_blocks_html;
