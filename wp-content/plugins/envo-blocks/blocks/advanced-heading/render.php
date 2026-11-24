<?php
/**
 * Advanced Heading: three parts (before, middle, after) with their own typography, colors, background and border.
 * The middle part can rotate several texts (one per line) with an animation, run by heading-view.js.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_defaults = Envo_Blocks_Blocks::default_heading();

$envo_blocks_tag    = Envo_Blocks_Blocks::choice( $attributes, 'tag', array( 'h2', 'h1', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ) );
$envo_blocks_align  = Envo_Blocks_Blocks::choice( $attributes, 'textAlign', array( 'center', 'left', 'right' ) );
$envo_blocks_layout = Envo_Blocks_Blocks::choice( $attributes, 'layout', array( 'inline', 'stacked' ) );
$envo_blocks_anim   = Envo_Blocks_Blocks::choice( $attributes, 'animation', array( 'slide-up', 'none', 'typing', 'fade', 'slide-down', 'zoom', 'flip', 'blur' ) );
$envo_blocks_deco   = Envo_Blocks_Blocks::choice( $attributes, 'decoration', array( 'none', 'highlight', 'underline' ) );

// Texts of the middle part: one per line. Without the attribute the translated default texts are used.
$envo_blocks_raw   = isset( $attributes['words'] ) && is_string( $attributes['words'] ) ? $attributes['words'] : $envo_blocks_defaults['words'];
$envo_blocks_words = array();
foreach ( preg_split( '/\r\n|\r|\n/', $envo_blocks_raw ) as $envo_blocks_word ) {
	$envo_blocks_word = trim( sanitize_text_field( $envo_blocks_word ) );
	if ( '' !== $envo_blocks_word ) {
		$envo_blocks_words[] = $envo_blocks_word;
	}
}
$envo_blocks_words = array_slice( $envo_blocks_words, 0, 12 );

$envo_blocks_part_text = static function ( $key ) use ( $attributes, $envo_blocks_defaults ) {
	$part = isset( $attributes[ $key ] ) && is_array( $attributes[ $key ] ) ? $attributes[ $key ] : array();
	// Line breaks typed in the text (Enter) are kept.
	return array_key_exists( 'text', $part ) ? trim( sanitize_textarea_field( is_string( $part['text'] ) ? $part['text'] : '' ) ) : $envo_blocks_defaults[ $key ];
};

$envo_blocks_html = static function ( $text ) {
	return nl2br( esc_html( $text ), false );
};

// Optional line break before the middle or last part: always, only on desktop or only on mobile.
$envo_blocks_break = static function ( $key ) use ( $attributes ) {
	$mode = isset( $attributes[ $key ]['lineBefore'] ) ? $attributes[ $key ]['lineBefore'] : '';
	return in_array( $mode, array( 'always', 'desktop', 'mobile' ), true ) ? '<br class="sb-aheading__br sb-aheading__br--' . $mode . '">' : '';
};

$envo_blocks_before = $envo_blocks_part_text( 'before' );
$envo_blocks_after  = $envo_blocks_part_text( 'after' );

if ( '' === $envo_blocks_before && '' === $envo_blocks_after && ! $envo_blocks_words ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Enter the texts of the heading in the block settings.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_parts = array();

if ( '' !== $envo_blocks_before ) {
	$envo_blocks_parts[] = '<span class="sb-aheading__part sb-aheading__part--before" style="' . esc_attr( Envo_Blocks_Blocks::part_style( isset( $attributes['before'] ) ? $attributes['before'] : array() ) ) . '">' . $envo_blocks_html( $envo_blocks_before ) . '</span>';
}

if ( $envo_blocks_words ) {
	$envo_blocks_rotating = count( $envo_blocks_words ) > 1 && 'none' !== $envo_blocks_anim;
	$envo_blocks_classes  = 'sb-aheading__part sb-aheading__part--middle';
	if ( 'none' !== $envo_blocks_deco ) {
		$envo_blocks_classes .= ' sb-aheading__deco--' . $envo_blocks_deco;
	}

	$envo_blocks_data = '';
	if ( 'none' !== $envo_blocks_anim || 'none' !== $envo_blocks_deco ) {
		$envo_blocks_data = ' data-sb-heading="' . esc_attr(
			wp_json_encode(
				array(
					'words'     => $envo_blocks_words,
					'animation' => $envo_blocks_anim,
					'interval'  => Envo_Blocks_Blocks::int( $attributes, 'interval', 2500, 800, 15000 ),
					'loop'      => ! isset( $attributes['loop'] ) || $attributes['loop'],
				)
			)
		) . '"';
	}

	$envo_blocks_style = Envo_Blocks_Blocks::part_style( isset( $attributes['middle'] ) ? $attributes['middle'] : array() );
	$envo_blocks_deco_color = Envo_Blocks_Blocks::color( isset( $attributes['decorationColor'] ) ? $attributes['decorationColor'] : '' );
	if ( '' !== $envo_blocks_deco_color ) {
		$envo_blocks_style .= '--sb-ah-deco:' . $envo_blocks_deco_color . ';';
	}

	// Screen readers get all the texts once; the animated copy is hidden from them.
	$envo_blocks_parts[] = $envo_blocks_break( 'middle' ) . '<span class="' . esc_attr( $envo_blocks_classes ) . '" style="' . esc_attr( $envo_blocks_style ) . '"' . $envo_blocks_data . '>'
		. '<span class="sb-aheading__rotator"' . ( $envo_blocks_rotating ? ' aria-hidden="true"' : '' ) . '><span class="sb-aheading__word">' . esc_html( $envo_blocks_words[0] ) . '</span></span>'
		. ( $envo_blocks_rotating ? '<span class="screen-reader-text">' . esc_html( implode( ', ', $envo_blocks_words ) ) . '</span>' : '' )
		. '</span>';
}

if ( '' !== $envo_blocks_after ) {
	$envo_blocks_parts[] = $envo_blocks_break( 'after' ) . '<span class="sb-aheading__part sb-aheading__part--after" style="' . esc_attr( Envo_Blocks_Blocks::part_style( isset( $attributes['after'] ) ? $attributes['after'] : array() ) ) . '">' . $envo_blocks_html( $envo_blocks_after ) . '</span>';
}

// A space between the parts keeps the words apart in the inline layout (the stacked layout ignores it).
$envo_blocks_parts = implode( ' ', $envo_blocks_parts );

$envo_blocks_link = isset( $attributes['linkUrl'] ) && is_string( $attributes['linkUrl'] ) ? esc_url( $attributes['linkUrl'] ) : '';
if ( '' !== $envo_blocks_link ) {
	$envo_blocks_parts = '<a class="sb-aheading__link" href="' . $envo_blocks_link . '">' . $envo_blocks_parts . '</a>';
}

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-aheading sb-aheading--' . $envo_blocks_layout . ' sb-align-' . $envo_blocks_align . ' sb-aheading--anim-' . $envo_blocks_anim,
	)
);

printf(
	'<%1$s %2$s>%3$s</%1$s>',
	tag_escape( $envo_blocks_tag ),
	wp_kses_data( $envo_blocks_wrapper ),
	wp_kses( $envo_blocks_parts, Envo_Blocks_Blocks::allowed_html() )
);
