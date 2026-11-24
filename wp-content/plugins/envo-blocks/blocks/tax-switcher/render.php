<?php
/**
 * Tax Switcher: prices with or without tax (see Envo_Blocks_Tax_Switcher).
 *
 * Links with ?envo-tax=incl|excl, so it works without JavaScript; tax-switcher-view.js saves the cookie and reloads
 * the page instead. Shows nothing on the site unless WooCommerce calculates taxes, a tax rate exists and switching
 * is on (Envo Blocks › Settings › Tax Switcher); the editor then shows the look with a note.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Envo_Blocks_Tax_Switcher' ) ) {
	return;
}

$envo_blocks_note = '';
if ( ! Envo_Blocks_Tax_Switcher::available() ) {
	$envo_blocks_note = __( 'WooCommerce does not calculate taxes yet (or there is no tax rate), so the switch shows nothing on the site. Turn on taxes in WooCommerce › Settings › General and add the rates in WooCommerce › Settings › Tax.', 'envo-blocks' );
} elseif ( ! Envo_Blocks_Tax_Switcher::enabled() ) {
	$envo_blocks_note = __( 'Switching is off in Envo Blocks › Settings › Tax Switcher, so the switch shows nothing on the site.', 'envo-blocks' );
}
if ( '' !== $envo_blocks_note && ! Envo_Blocks_Blocks::is_preview() ) {
	return;
}

$envo_blocks_variant = Envo_Blocks_Blocks::choice( $attributes, 'variant', array( 'buttons', 'toggle', 'select' ) );
$envo_blocks_order   = Envo_Blocks_Blocks::choice( $attributes, 'order', array( 'incl', 'excl' ) );
$envo_blocks_justify = Envo_Blocks_Blocks::choice( $attributes, 'justify', array( 'left', 'center', 'right' ) );
$envo_blocks_radius  = Envo_Blocks_Blocks::int( $attributes, 'radius', 20, 0, 40 );
$envo_blocks_label   = Envo_Blocks_Blocks::text( $attributes, 'label', __( 'Prices', 'envo-blocks' ) );
$envo_blocks_current = '' === $envo_blocks_note ? Envo_Blocks_Tax_Switcher::mode() : 'incl';
$envo_blocks_modes   = 'excl' === $envo_blocks_order ? array( 'excl', 'incl' ) : array( 'incl', 'excl' );
$envo_blocks_texts   = array(
	'incl' => Envo_Blocks_Blocks::text( $attributes, 'inclText', Envo_Blocks_Tax_Switcher::label( 'incl' ) ),
	'excl' => Envo_Blocks_Blocks::text( $attributes, 'exclText', Envo_Blocks_Tax_Switcher::label( 'excl' ) ),
);
$envo_blocks_id      = 'envo-tax-' . Envo_Blocks_Blocks::instance( 'tax-switcher' );

// Link of a mode: the current URL with ?envo-tax= (the editor preview links nowhere).
$envo_blocks_href = static function ( $mode ) {
	if ( Envo_Blocks_Blocks::is_preview() ) {
		return '#';
	}
	return add_query_arg( Envo_Blocks_Tax_Switcher::PARAM, $mode );
};

$envo_blocks_style  = Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'textColor'        => '--envo-tax-text',
		'background'       => '--envo-tax-bg',
		'activeBackground' => '--envo-tax-active-bg',
		'activeText'       => '--envo-tax-active-text',
		'borderColor'      => '--envo-tax-border',
	)
);
$envo_blocks_style .= '--envo-tax-radius:' . $envo_blocks_radius . 'px;';

$envo_blocks_attrs = get_block_wrapper_attributes(
	array(
		'class'        => 'envo-tax envo-tax--' . $envo_blocks_variant . ' envo-tax--justify-' . $envo_blocks_justify,
		'style'        => $envo_blocks_style,
		'data-mode'    => $envo_blocks_current,
		'data-cookie'  => Envo_Blocks_Tax_Switcher::COOKIE,
		'data-param'   => Envo_Blocks_Tax_Switcher::PARAM,
		'data-path'    => COOKIEPATH ? COOKIEPATH : '/',
		'data-default' => Envo_Blocks_Tax_Switcher::default_mode(),
	)
);

$envo_blocks_title = '';
if ( ! empty( $attributes['showLabel'] ) ) {
	$envo_blocks_title = 'select' === $envo_blocks_variant
		? '<label class="envo-tax__label" for="' . esc_attr( $envo_blocks_id ) . '-select">' . esc_html( $envo_blocks_label ) . '</label>'
		: '<span class="envo-tax__label" id="' . esc_attr( $envo_blocks_id ) . '-label">' . esc_html( $envo_blocks_label ) . '</span>';
}
$envo_blocks_named = '' !== $envo_blocks_title && 'select' !== $envo_blocks_variant
	? ' aria-labelledby="' . esc_attr( $envo_blocks_id ) . '-label"'
	: ' aria-label="' . esc_attr( $envo_blocks_label ) . '"';

$envo_blocks_html = '';
if ( 'buttons' === $envo_blocks_variant ) {
	$envo_blocks_html .= '<div class="envo-tax__group" role="group"' . $envo_blocks_named . '>';
	foreach ( $envo_blocks_modes as $envo_blocks_mode ) {
		$envo_blocks_on    = $envo_blocks_mode === $envo_blocks_current;
		$envo_blocks_html .= '<a class="envo-tax__option' . ( $envo_blocks_on ? ' is-active' : '' ) . '" href="' . esc_url( $envo_blocks_href( $envo_blocks_mode ) ) . '" role="button" aria-pressed="' . ( $envo_blocks_on ? 'true' : 'false' ) . '" data-envo-tax="' . esc_attr( $envo_blocks_mode ) . '" rel="nofollow">' . esc_html( $envo_blocks_texts[ $envo_blocks_mode ] ) . '</a>';
	}
	$envo_blocks_html .= '</div>';
} elseif ( 'toggle' === $envo_blocks_variant ) {
	// One switch: on = the second mode. The texts on both sides switch to their mode too.
	$envo_blocks_second = $envo_blocks_modes[1];
	$envo_blocks_on     = $envo_blocks_second === $envo_blocks_current;
	$envo_blocks_other  = $envo_blocks_on ? $envo_blocks_modes[0] : $envo_blocks_second;
	$envo_blocks_side   = static function ( $mode ) use ( $envo_blocks_current, $envo_blocks_texts, $envo_blocks_href ) {
		return '<a class="envo-tax__side' . ( $mode === $envo_blocks_current ? ' is-active' : '' ) . '" href="' . esc_url( $envo_blocks_href( $mode ) ) . '" data-envo-tax="' . esc_attr( $mode ) . '" tabindex="-1" aria-hidden="true" rel="nofollow">' . esc_html( $envo_blocks_texts[ $mode ] ) . '</a>';
	};
	$envo_blocks_html .= '<span class="envo-tax__group">' . $envo_blocks_side( $envo_blocks_modes[0] )
		. '<a class="envo-tax__switch' . ( $envo_blocks_on ? ' is-on' : '' ) . '" href="' . esc_url( $envo_blocks_href( $envo_blocks_other ) ) . '" role="switch" aria-checked="' . ( $envo_blocks_on ? 'true' : 'false' ) . '" data-envo-tax="' . esc_attr( $envo_blocks_other ) . '" rel="nofollow"'
		. ' aria-label="' . esc_attr( '' !== $envo_blocks_title ? $envo_blocks_label . ': ' . $envo_blocks_texts[ $envo_blocks_second ] : $envo_blocks_texts[ $envo_blocks_second ] ) . '">'
		. '<span class="envo-tax__knob" aria-hidden="true"></span></a>'
		. $envo_blocks_side( $envo_blocks_second ) . '</span>';
} else {
	$envo_blocks_options = '';
	foreach ( $envo_blocks_modes as $envo_blocks_mode ) {
		$envo_blocks_options .= '<option value="' . esc_attr( $envo_blocks_mode ) . '"' . selected( $envo_blocks_mode, $envo_blocks_current, false ) . '>' . esc_html( $envo_blocks_texts[ $envo_blocks_mode ] ) . '</option>';
	}
	$envo_blocks_html .= '<form class="envo-tax__form" method="get" action="">'
		. '<select class="envo-tax__select" id="' . esc_attr( $envo_blocks_id ) . '-select" name="' . esc_attr( Envo_Blocks_Tax_Switcher::PARAM ) . '" data-envo-tax-select' . ( '' === $envo_blocks_title ? ' aria-label="' . esc_attr( $envo_blocks_label ) . '"' : '' ) . '>' . $envo_blocks_options . '</select>'
		. '<noscript><button type="submit" class="envo-tax__apply">' . esc_html__( 'Apply', 'envo-blocks' ) . '</button></noscript></form>';
}

echo '<div ' . $envo_blocks_attrs . '>' . $envo_blocks_title . $envo_blocks_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
if ( '' !== $envo_blocks_note ) {
	echo Envo_Blocks_Blocks::notice( $envo_blocks_note ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in notice().
}
