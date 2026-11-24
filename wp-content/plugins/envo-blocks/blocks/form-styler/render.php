<?php
/**
 * Form Styler: the form of a contact form plugin in the look of the block (see Envo_Blocks_Form_Styler).
 *
 * The form plugin renders its own form (shortcode); the wrapper carries the settings as custom properties and
 * classes, form-styler.css applies them to the fields of every supported plugin.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_code = Envo_Blocks_Form_Styler::shortcode( $attributes );
if ( '' === $envo_blocks_code ) {
	echo Envo_Blocks_Blocks::notice( __( 'Choose a form in the block settings.', 'envo-blocks' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in notice().
	return;
}

// The form plugins print forms, inputs and their scripts: their output is not filtered, like in the Shortcode block.
$envo_blocks_form = do_shortcode( $envo_blocks_code );
if ( '' === trim( $envo_blocks_form ) ) {
	echo Envo_Blocks_Blocks::notice( __( 'The form plugin returned nothing for this form. Check that the form exists and is published.', 'envo-blocks' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in notice().
	return;
}

$envo_blocks_plugin = Envo_Blocks_Blocks::choice( $attributes, 'plugin', array( 'shortcode', 'cf7', 'wpforms', 'fluentform', 'ninja', 'formidable', 'forminator', 'gravity' ) );
$envo_blocks_field  = Envo_Blocks_Blocks::choice( $attributes, 'fieldStyle', array( 'outline', 'filled', 'underline' ) );
$envo_blocks_width  = Envo_Blocks_Blocks::choice( $attributes, 'buttonWidth', array( 'auto', 'full' ) );
$envo_blocks_align  = Envo_Blocks_Blocks::choice( $attributes, 'buttonAlign', array( 'left', 'center', 'right' ) );
$envo_blocks_cols   = Envo_Blocks_Blocks::int( $attributes, 'columns', 1, 1, 2 );

$envo_blocks_style  = Envo_Blocks_Form_Styler::theme_button_vars();
$envo_blocks_style .= '--envo-fs-radius:' . Envo_Blocks_Blocks::int( $attributes, 'fieldRadius', 6, 0, 40 ) . 'px;';
$envo_blocks_style .= '--envo-fs-pad:' . Envo_Blocks_Blocks::int( $attributes, 'fieldPadding', 12, 4, 30 ) . 'px;';
$envo_blocks_style .= '--envo-fs-bw:' . Envo_Blocks_Blocks::int( $attributes, 'borderWidth', 1, 0, 6 ) . 'px;';
$envo_blocks_style .= '--envo-fs-gap:' . Envo_Blocks_Blocks::int( $attributes, 'fieldGap', 18, 0, 60 ) . 'px;';
$envo_blocks_label  = Envo_Blocks_Blocks::int( $attributes, 'labelSize', 0, 0, 30 );
if ( $envo_blocks_label ) {
	$envo_blocks_style .= '--envo-fs-label-size:' . $envo_blocks_label . 'px;';
}
$envo_blocks_radius = Envo_Blocks_Blocks::int( $attributes, 'buttonRadius', -1, -1, 60 );
if ( $envo_blocks_radius >= 0 ) {
	$envo_blocks_style .= '--envo-fs-btn-radius:' . $envo_blocks_radius . 'px;';
}
$envo_blocks_style .= Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'labelColor'            => '--envo-fs-label',
		'fieldBackground'       => '--envo-fs-field-bg',
		'fieldText'             => '--envo-fs-field-text',
		'fieldBorder'           => '--envo-fs-border',
		'focusColor'            => '--envo-fs-focus',
		'buttonBackground'      => '--envo-fs-btn-bg',
		'buttonText'            => '--envo-fs-btn-text',
		'buttonHoverBackground' => '--envo-fs-btn-hover-bg',
		'buttonHoverText'       => '--envo-fs-btn-hover-text',
		'successColor'          => '--envo-fs-success',
		'errorColor'            => '--envo-fs-error',
	)
);

// An own hover colour replaces the darker shade of the button.
if ( '' !== Envo_Blocks_Blocks::color( isset( $attributes['buttonHoverBackground'] ) ? $attributes['buttonHoverBackground'] : '' ) ) {
	$envo_blocks_style .= '--envo-fs-btn-hover-filter:none;';
}

$envo_blocks_attrs = get_block_wrapper_attributes(
	array(
		'class' => implode(
			' ',
			array_filter(
				array(
					'envo-form-styler',
					'envo-form-styler--' . $envo_blocks_plugin,
					'envo-form-styler--' . $envo_blocks_field,
					'envo-form-styler--btn-' . $envo_blocks_align,
					'full' === $envo_blocks_width ? 'envo-form-styler--btn-full' : '',
					2 === $envo_blocks_cols ? 'envo-form-styler--cols-2' : '',
				)
			)
		),
		'style' => $envo_blocks_style,
	)
);

echo '<div ' . $envo_blocks_attrs . '>' . $envo_blocks_form . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wrapper attributes are escaped by WordPress, the form is the output of its plugin.
