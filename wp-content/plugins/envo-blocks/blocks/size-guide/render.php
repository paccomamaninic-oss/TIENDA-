<?php
/**
 * Size Guide: a button that opens the inner blocks (the size chart) in a native <dialog>.
 *
 * @package EnvoBlocks
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Inner blocks.
 */

defined( 'ABSPATH' ) || exit;

if ( '' === trim( wp_strip_all_tags( $content, true ) ) && false === strpos( $content, '<img' ) ) {
	return;
}

$envo_blocks_text  = Envo_Blocks_Blocks::text( $attributes, 'buttonText', __( 'Size guide', 'envo-blocks' ) );
$envo_blocks_title = Envo_Blocks_Blocks::text( $attributes, 'modalTitle', $envo_blocks_text );
$envo_blocks_style = Envo_Blocks_Blocks::choice( $attributes, 'buttonStyle', array( 'link', 'button', 'outline' ) );
$envo_blocks_align = Envo_Blocks_Blocks::choice( $attributes, 'contentAlign', array( 'left', 'center', 'right' ) );
$envo_blocks_id    = wp_unique_id( 'sb-size-' );

// The dialog is moved to <body> by the view script, so it gets its own copy of the custom properties.
$envo_blocks_vars = '--sb-size-w:' . Envo_Blocks_Blocks::int( $attributes, 'modalWidth', 720, 320, 1400 ) . 'px;' . Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'buttonColor'     => '--sb-size-btn',
		'buttonTextColor' => '--sb-size-btn-text',
		'modalBackground' => '--sb-size-bg',
		'modalText'       => '--sb-size-text',
	)
);

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-size sb-align-' . $envo_blocks_align,
		'style' => $envo_blocks_vars,
	)
);

$envo_blocks_button_class = 'sb-size__open sb-size__open--' . $envo_blocks_style . ( 'link' === $envo_blocks_style ? '' : ' wp-element-button' );
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<button type="button" class="<?php echo esc_attr( $envo_blocks_button_class ); ?>" aria-haspopup="dialog" aria-controls="<?php echo esc_attr( $envo_blocks_id ); ?>">
		<?php echo wp_kses( ! isset( $attributes['showIcon'] ) || $attributes['showIcon'] ? Envo_Blocks_Blocks::icon( 'ruler' ) : '', Envo_Blocks_Blocks::allowed_html() ); ?>
		<span><?php echo esc_html( $envo_blocks_text ); ?></span>
	</button>
	<dialog class="sb-size__dialog" style="<?php echo esc_attr( $envo_blocks_vars ); ?>" id="<?php echo esc_attr( $envo_blocks_id ); ?>" aria-labelledby="<?php echo esc_attr( $envo_blocks_id ); ?>-title">
		<div class="sb-size__head">
			<h2 class="sb-size__title" id="<?php echo esc_attr( $envo_blocks_id ); ?>-title"><?php echo esc_html( $envo_blocks_title ); ?></h2>
			<button type="button" class="sb-size__close" data-sb-close aria-label="<?php esc_attr_e( 'Close', 'envo-blocks' ); ?>"><?php echo wp_kses( Envo_Blocks_Blocks::icon( 'close' ), Envo_Blocks_Blocks::allowed_html() ); ?></button>
		</div>
		<div class="sb-size__body"><?php echo wp_kses( $content, Envo_Blocks_Blocks::allowed_html() ); ?></div>
	</dialog>
</div>
