<?php
/**
 * Develop by : Saiful Islam (aThemeArt)
 *
 * Default options & helpers (Free limited edition).
 *
 * @package ATA_WC_Smart_Popup_Cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default options for free edition.
 *
 * @return array
 */
function ata_wcspc_free_get_default_options() {
	$defaults = array(
		'_wcspc_auto_show_ajax'       => 'yes',
		'_wcspc_auto_show_normal'     => 'yes',
		'_wcspc_manual_show'          => '',
		'_wcspc_position'             => '01',
		'_wcspc_primary_colors'       => '#ffffff',
		'_wcspc_secondary_colors'     => '#a7a7a7',
		'_wcspc_bg_colors'            => '#9b5c8f',
		'_wcspc_total'                => 'yes',
		'_wcspc_buttons'              => '01',
		'_wcspc_continue'             => 'yes',
		'_wcspc_reload'               => 'no',
		'_wcspc_count'                => 'yes',
		'_wcspc_count_position'       => 'bottom-left',
		'_wcspc_count_icon'           => 'wcspc-icon-cart1',
		'_wcspc_count_hide_empty'     => 'no',
		'_wcspc_count_hide_checkout'  => 'no',
		'_wcspc_style'                => '01',
	);

	return apply_filters( 'ata_wcspc_free_default_options', $defaults );
}

/**
 * Get options (merged with defaults).
 *
 * @param string $key Optional key.
 * @return mixed
 */
function ata_wcspc_free_get_option( $key = '' ) {
	$defaults = ata_wcspc_free_get_default_options();
	$options  = get_option( 'ata_wcspc_free_options', array() );

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	// Migrate legacy key.
	$legacy = get_option( 'wcspcOptions', false );
	if ( false !== $legacy && is_array( $legacy ) && empty( $options ) ) {
		$options = $legacy;
		update_option( 'ata_wcspc_free_options', $options );
	}

	$options = wp_parse_args( $options, $defaults );
	$options = apply_filters( 'ata_wcspc_free_get_options', $options );

	if ( '' === $key ) {
		return $options;
	}

	return isset( $options[ $key ] ) ? $options[ $key ] : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Sanitize color.
 *
 * @param mixed $color Color.
 * @return string
 */
function ata_wcspc_free_sanitize_color( $color ) {
	if ( empty( $color ) ) {
		return '';
	}
	$c = sanitize_hex_color( $color );
	return $c ? $c : '';
}

/**
 * Sanitize options.
 *
 * @param array $input Input.
 * @return array
 */
function ata_wcspc_free_sanitize_options( $input ) {
	if ( ! is_array( $input ) ) {
		return ata_wcspc_free_get_default_options();
	}

	$defaults = ata_wcspc_free_get_default_options();
	$output   = array();
	$yes_no   = array( 'yes', 'no' );

	$output['_wcspc_auto_show_ajax']      = isset( $input['_wcspc_auto_show_ajax'] ) && in_array( $input['_wcspc_auto_show_ajax'], $yes_no, true ) ? $input['_wcspc_auto_show_ajax'] : $defaults['_wcspc_auto_show_ajax'];
	$output['_wcspc_auto_show_normal']    = isset( $input['_wcspc_auto_show_normal'] ) && in_array( $input['_wcspc_auto_show_normal'], $yes_no, true ) ? $input['_wcspc_auto_show_normal'] : $defaults['_wcspc_auto_show_normal'];
	$output['_wcspc_manual_show']         = isset( $input['_wcspc_manual_show'] ) ? sanitize_text_field( $input['_wcspc_manual_show'] ) : '';
	$output['_wcspc_position']            = isset( $input['_wcspc_position'] ) ? sanitize_text_field( $input['_wcspc_position'] ) : $defaults['_wcspc_position'];
	$output['_wcspc_primary_colors']      = ! empty( $input['_wcspc_primary_colors'] ) ? ata_wcspc_free_sanitize_color( $input['_wcspc_primary_colors'] ) : $defaults['_wcspc_primary_colors'];
	$output['_wcspc_secondary_colors']    = ! empty( $input['_wcspc_secondary_colors'] ) ? ata_wcspc_free_sanitize_color( $input['_wcspc_secondary_colors'] ) : $defaults['_wcspc_secondary_colors'];
	$output['_wcspc_bg_colors']           = ! empty( $input['_wcspc_bg_colors'] ) ? ata_wcspc_free_sanitize_color( $input['_wcspc_bg_colors'] ) : $defaults['_wcspc_bg_colors'];
	$output['_wcspc_total']               = isset( $input['_wcspc_total'] ) && in_array( $input['_wcspc_total'], $yes_no, true ) ? $input['_wcspc_total'] : $defaults['_wcspc_total'];
	$output['_wcspc_buttons']             = isset( $input['_wcspc_buttons'] ) ? sanitize_text_field( $input['_wcspc_buttons'] ) : $defaults['_wcspc_buttons'];
	$output['_wcspc_continue']            = isset( $input['_wcspc_continue'] ) && in_array( $input['_wcspc_continue'], $yes_no, true ) ? $input['_wcspc_continue'] : $defaults['_wcspc_continue'];
	$output['_wcspc_reload']              = isset( $input['_wcspc_reload'] ) && in_array( $input['_wcspc_reload'], $yes_no, true ) ? $input['_wcspc_reload'] : $defaults['_wcspc_reload'];
	$output['_wcspc_count']               = isset( $input['_wcspc_count'] ) && in_array( $input['_wcspc_count'], $yes_no, true ) ? $input['_wcspc_count'] : $defaults['_wcspc_count'];
	$output['_wcspc_count_position']      = isset( $input['_wcspc_count_position'] ) ? sanitize_text_field( $input['_wcspc_count_position'] ) : $defaults['_wcspc_count_position'];
	$output['_wcspc_count_icon']          = isset( $input['_wcspc_count_icon'] ) ? sanitize_text_field( $input['_wcspc_count_icon'] ) : $defaults['_wcspc_count_icon'];
	$output['_wcspc_count_hide_empty']    = isset( $input['_wcspc_count_hide_empty'] ) && in_array( $input['_wcspc_count_hide_empty'], $yes_no, true ) ? $input['_wcspc_count_hide_empty'] : $defaults['_wcspc_count_hide_empty'];
	$output['_wcspc_count_hide_checkout'] = isset( $input['_wcspc_count_hide_checkout'] ) && in_array( $input['_wcspc_count_hide_checkout'], $yes_no, true ) ? $input['_wcspc_count_hide_checkout'] : $defaults['_wcspc_count_hide_checkout'];
	$output['_wcspc_style']               = '01';

	return apply_filters( 'ata_wcspc_free_sanitize_options', $output, $input );
}

// Backward compatibility.
if ( ! function_exists( 'wcspc_get_default_options' ) ) {
	function wcspc_get_default_options() {
		return ata_wcspc_free_get_default_options();
	}
}

if ( ! function_exists( 'wcspc_get_option' ) ) {
	function wcspc_get_option( $key ) {
		if ( 'wcspcOptions' === $key ) {
			return ata_wcspc_free_get_option();
		}
		return ata_wcspc_free_get_option( $key );
	}
}
