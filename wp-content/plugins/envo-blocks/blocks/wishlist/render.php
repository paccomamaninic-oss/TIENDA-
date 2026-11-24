<?php
/**
 * Wishlist: the visitor's saved products on the wishlist page (Envo_Blocks_Wishlist).
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

echo wp_kses( Envo_Blocks_Wishlist::render_page( $attributes ), Envo_Blocks_Wishlist::allowed_html() );
