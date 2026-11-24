<?php
/**
 * Features Bar: a row of shop benefits with icons.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_items = '';
$envo_blocks_count = 0;

$envo_blocks_list = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : Envo_Blocks_Blocks::default_features();
foreach ( $envo_blocks_list as $envo_blocks_item ) {
	if ( ! is_array( $envo_blocks_item ) ) {
		continue;
	}

	$envo_blocks_title = Envo_Blocks_Blocks::text( $envo_blocks_item, 'title' );
	$envo_blocks_text  = Envo_Blocks_Blocks::text( $envo_blocks_item, 'text' );
	$envo_blocks_icon  = isset( $envo_blocks_item['icon'] ) && is_string( $envo_blocks_item['icon'] ) ? Envo_Blocks_Blocks::icon( $envo_blocks_item['icon'] ) : '';
	$envo_blocks_url   = isset( $envo_blocks_item['url'] ) && is_string( $envo_blocks_item['url'] ) ? esc_url( $envo_blocks_item['url'] ) : '';

	if ( '' === $envo_blocks_title && '' === $envo_blocks_text ) {
		continue;
	}

	$envo_blocks_body = ( $envo_blocks_icon ? '<span class="sb-features__icon">' . $envo_blocks_icon . '</span>' : '' )
		. '<span class="sb-features__body">'
		. ( '' !== $envo_blocks_title ? '<strong class="sb-features__title">' . esc_html( $envo_blocks_title ) . '</strong>' : '' )
		. ( '' !== $envo_blocks_text ? '<span class="sb-features__text">' . esc_html( $envo_blocks_text ) . '</span>' : '' )
		. '</span>';

	$envo_blocks_items .= '<li class="sb-features__item">' . ( $envo_blocks_url ? '<a class="sb-features__inner sb-features__link" href="' . $envo_blocks_url . '">' . $envo_blocks_body . '</a>' : '<div class="sb-features__inner">' . $envo_blocks_body . '</div>' ) . '</li>';
	++$envo_blocks_count;
}

if ( ! $envo_blocks_count ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Add at least one item in the block settings.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_layout  = Envo_Blocks_Blocks::choice( $attributes, 'layout', array( 'side', 'top' ) );
$envo_blocks_istyle  = Envo_Blocks_Blocks::choice( $attributes, 'iconStyle', array( 'plain', 'circle', 'square' ) );
$envo_blocks_align   = Envo_Blocks_Blocks::choice( $attributes, 'contentAlign', array( 'center', 'left' ) );
$envo_blocks_columns = min( $envo_blocks_count, Envo_Blocks_Blocks::int( $attributes, 'columns', 4, 1, 6 ) );
$envo_blocks_mobile  = min( $envo_blocks_count, Envo_Blocks_Blocks::int( $attributes, 'columnsMobile', 1, 1, 3 ) );
$envo_blocks_size    = Envo_Blocks_Blocks::int( $attributes, 'iconSize', 36, 16, 96 );

$envo_blocks_classes = 'sb-features sb-features--' . $envo_blocks_layout . ' sb-features--icon-' . $envo_blocks_istyle . ' sb-align-' . $envo_blocks_align;
if ( ! empty( $attributes['dividers'] ) ) {
	$envo_blocks_classes .= ' sb-features--dividers';
}

$envo_blocks_style = '--sb-cols:' . $envo_blocks_columns . ';--sb-cols-m:' . $envo_blocks_mobile . ';--sb-icon-size:' . $envo_blocks_size . 'px;' . Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'iconColor'      => '--sb-features-icon',
		'iconBackground' => '--sb-features-icon-bg',
		'titleColor'     => '--sb-features-title',
		'textColor'      => '--sb-features-text',
		'background'     => '--sb-features-bg',
		'dividerColor'   => '--sb-features-divider',
	)
);

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => $envo_blocks_classes,
		'style' => $envo_blocks_style,
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<ul class="sb-features__list"><?php echo wp_kses( $envo_blocks_items, Envo_Blocks_Blocks::allowed_html() ); ?></ul>
</div>
