<?php
/**
 * Wishlist Button: a heart that adds the product to the wishlist (Envo_Blocks_Wishlist).
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

echo wp_kses( Envo_Blocks_Wishlist::render_button( $attributes, $block ), Envo_Blocks_Wishlist::allowed_html() );
