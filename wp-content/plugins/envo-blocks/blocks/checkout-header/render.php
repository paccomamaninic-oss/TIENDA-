<?php
/**
 * Checkout Header: the logo (or the site name), a secure checkout note and a link back to the cart – the header of
 * a distraction-free checkout layout, without the menu.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_style  = Envo_Blocks_Blocks::choice( $attributes, 'headerStyle', array( 'split', 'centered' ) );
$envo_blocks_width  = isset( $attributes['logoWidth'] ) ? max( 40, min( 400, (int) $attributes['logoWidth'] ) ) : 140;
$envo_blocks_secure = Envo_Blocks_Blocks::text( $attributes, 'secureText', __( 'Secure checkout', 'envo-blocks' ) );
$envo_blocks_back   = Envo_Blocks_Blocks::text( $attributes, 'backText', __( 'Back to cart', 'envo-blocks' ) );
$envo_blocks_html   = '';

if ( ! isset( $attributes['showLogo'] ) || $attributes['showLogo'] ) {
	$envo_blocks_logo  = (int) get_theme_mod( 'custom_logo' );
	$envo_blocks_image = $envo_blocks_logo ? wp_get_attachment_image( $envo_blocks_logo, 'medium', false, array( 'class' => 'envo-checkout-header__logo-image', 'alt' => get_bloginfo( 'name' ) ) ) : '';
	$envo_blocks_html .= '<a class="envo-checkout-header__logo" href="' . esc_url( home_url( '/' ) ) . '" style="--envo-ch-logo:' . (int) $envo_blocks_width . 'px">' . ( '' !== $envo_blocks_image ? $envo_blocks_image : '<span class="envo-checkout-header__name">' . esc_html( get_bloginfo( 'name' ) ) . '</span>' ) . '</a>';
}

$envo_blocks_lock  = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><rect x="5" y="10.5" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>';
$envo_blocks_html .= '<span class="envo-checkout-header__secure">' . $envo_blocks_lock . '<span>' . esc_html( $envo_blocks_secure ) . '</span></span>';

if ( ( ! isset( $attributes['showBack'] ) || $attributes['showBack'] ) && function_exists( 'wc_get_cart_url' ) ) {
	$envo_blocks_html .= '<a class="envo-checkout-header__back" href="' . esc_url( wc_get_cart_url() ) . '"><span aria-hidden="true">&larr;</span> ' . esc_html( $envo_blocks_back ) . '</a>';
}

printf(
	'<header %1$s>%2$s</header>',
	get_block_wrapper_attributes( array( 'class' => 'envo-checkout-header envo-checkout-header--' . $envo_blocks_style ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$envo_blocks_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
);
