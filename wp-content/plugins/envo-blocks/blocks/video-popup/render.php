<?php
/**
 * Video Popup: a play button or an image with a play button; video-popup-view.js opens the video in a <dialog>.
 * The player (iframe or <video>) is created only after the click, so nothing loads from the video site before.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_url   = isset( $attributes['videoUrl'] ) && is_string( $attributes['videoUrl'] ) ? $attributes['videoUrl'] : '';
$envo_blocks_embed = Envo_Blocks_Blocks::video_embed( $envo_blocks_url, Envo_Blocks_Blocks::int( $attributes, 'startAt', 0, 0, 86400 ) );

if ( ! $envo_blocks_embed ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( '' === trim( $envo_blocks_url ) ? __( 'Enter the address of a YouTube, Vimeo or Dailymotion video, or choose a video file, in the block settings.', 'envo-blocks' ) : __( 'This video address is not supported. Use a YouTube, Vimeo or Dailymotion link, or a link to an mp4 / webm file.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_layout = Envo_Blocks_Blocks::choice( $attributes, 'layout', array( 'image', 'button' ) );
$envo_blocks_icon   = Envo_Blocks_Blocks::choice( $attributes, 'iconStyle', array( 'filled', 'outline', 'glass', 'square' ) );
$envo_blocks_align  = Envo_Blocks_Blocks::choice( $attributes, 'contentAlign', array( 'center', 'left', 'right' ) );
$envo_blocks_lpos   = Envo_Blocks_Blocks::choice( $attributes, 'labelPosition', array( 'right', 'below' ) );
$envo_blocks_title  = Envo_Blocks_Blocks::text( $attributes, 'videoTitle', __( 'Video', 'envo-blocks' ) );
$envo_blocks_label  = Envo_Blocks_Blocks::text( $attributes, 'label' );
$envo_blocks_cap    = Envo_Blocks_Blocks::text( $attributes, 'caption' );

$envo_blocks_ratio = static function ( $key, $default ) use ( $attributes ) {
	$value = Envo_Blocks_Blocks::choice( $attributes, $key, array( $default, '16/9', '4/3', '1/1', '3/4', '9/16', '21/9' ) );
	return $value;
};

$envo_blocks_play = '<span class="sb-video__play" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" focusable="false"><path d="M8 5.5v13a.75.75 0 0 0 1.14.64l10.4-6.5a.75.75 0 0 0 0-1.28L9.14 4.86A.75.75 0 0 0 8 5.5z" fill="currentColor"/></svg></span>';

/* translators: %s: video title */
$envo_blocks_aria = sprintf( __( 'Play video: %s', 'envo-blocks' ), $envo_blocks_title );

if ( 'image' === $envo_blocks_layout ) {
	$envo_blocks_image = '';
	$envo_blocks_id    = Envo_Blocks_Blocks::int( $attributes, 'imageId', 0, 0, PHP_INT_MAX );
	$envo_blocks_alt   = Envo_Blocks_Blocks::text( $attributes, 'imageAlt' );

	if ( $envo_blocks_id && wp_attachment_is_image( $envo_blocks_id ) ) {
		$envo_blocks_image = wp_get_attachment_image( $envo_blocks_id, 'large', false, array( 'class' => 'sb-video__img', 'alt' => $envo_blocks_alt, 'loading' => 'lazy' ) );
	} elseif ( ! empty( $attributes['imageUrl'] ) && is_string( $attributes['imageUrl'] ) ) {
		$envo_blocks_image = '<img class="sb-video__img" src="' . esc_url( $attributes['imageUrl'] ) . '" alt="' . esc_attr( $envo_blocks_alt ) . '" loading="lazy">';
	}

	$envo_blocks_trigger = '<button type="button" class="sb-video__trigger sb-video__trigger--image" aria-haspopup="dialog" aria-label="' . esc_attr( $envo_blocks_aria ) . '">'
		. '<span class="sb-video__media' . ( '' === $envo_blocks_image ? ' is-empty' : '' ) . '">' . $envo_blocks_image . '</span>'
		. '<span class="sb-video__overlay" aria-hidden="true"></span>'
		. '<span class="sb-video__center">' . $envo_blocks_play
		. ( '' !== $envo_blocks_label ? '<span class="sb-video__label">' . esc_html( $envo_blocks_label ) . '</span>' : '' )
		. '</span>'
		. ( '' !== $envo_blocks_cap ? '<span class="sb-video__caption">' . esc_html( $envo_blocks_cap ) . '</span>' : '' )
		. '</button>';
} else {
	$envo_blocks_trigger = '<button type="button" class="sb-video__trigger sb-video__trigger--button sb-video__label--' . $envo_blocks_lpos . '" aria-haspopup="dialog"' . ( '' === $envo_blocks_label ? ' aria-label="' . esc_attr( $envo_blocks_aria ) . '"' : '' ) . '>'
		. $envo_blocks_play
		. ( '' !== $envo_blocks_label ? '<span class="sb-video__label">' . esc_html( $envo_blocks_label ) . '</span>' : '' )
		. '</button>';
}

$envo_blocks_classes = 'sb-video sb-video--' . $envo_blocks_layout . ' sb-video--icon-' . $envo_blocks_icon . ' sb-align-' . $envo_blocks_align;
if ( ! isset( $attributes['pulse'] ) || $attributes['pulse'] ) {
	$envo_blocks_classes .= ' sb-video--pulse';
}
if ( ! isset( $attributes['hoverZoom'] ) || $attributes['hoverZoom'] ) {
	$envo_blocks_classes .= ' sb-video--zoom';
}

$envo_blocks_vars = '--sb-video-icon:' . Envo_Blocks_Blocks::int( $attributes, 'iconSize', 84, 32, 200 ) . 'px;'
	. '--sb-video-radius:' . Envo_Blocks_Blocks::int( $attributes, 'radius', 14, 0, 60 ) . 'px;'
	. '--sb-video-ratio:' . $envo_blocks_ratio( 'aspectRatio', '16/9' ) . ';'
	. Envo_Blocks_Blocks::color_vars(
		$attributes,
		array(
			'iconColor'      => '--sb-video-icon-color',
			'iconBackground' => '--sb-video-icon-bg',
			'labelColor'     => '--sb-video-label',
			'overlayColor'   => '--sb-video-overlay',
		)
	);

// The dialog is moved to <body> by the view script, so it gets its own custom properties.
$envo_blocks_dialog_vars = '--sb-video-w:' . Envo_Blocks_Blocks::int( $attributes, 'popupWidth', 1000, 320, 1800 ) . 'px;'
	. '--sb-video-popup-ratio:' . $envo_blocks_ratio( 'popupRatio', '16/9' ) . ';'
	. Envo_Blocks_Blocks::color_vars( $attributes, array( 'backdropColor' => '--sb-video-backdrop' ) );

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => $envo_blocks_classes,
		'style' => $envo_blocks_vars,
	)
);

$envo_blocks_config = array(
	'type'  => $envo_blocks_embed['type'],
	'src'   => $envo_blocks_embed['src'],
	'title' => $envo_blocks_title,
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-sb-video="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>">
	<?php echo wp_kses( $envo_blocks_trigger, Envo_Blocks_Blocks::allowed_html() ); ?>
	<dialog class="sb-video__dialog" style="<?php echo esc_attr( $envo_blocks_dialog_vars ); ?>" aria-label="<?php echo esc_attr( $envo_blocks_title ); ?>">
		<button type="button" class="sb-video__close" data-sb-video-close aria-label="<?php esc_attr_e( 'Close video', 'envo-blocks' ); ?>"><?php echo wp_kses( Envo_Blocks_Blocks::icon( 'close' ), Envo_Blocks_Blocks::allowed_html() ); ?></button>
		<div class="sb-video__frame"></div>
	</dialog>
</div>
