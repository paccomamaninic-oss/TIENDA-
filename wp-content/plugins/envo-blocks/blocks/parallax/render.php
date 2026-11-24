<?php
/**
 * Parallax Section: an image or video background that moves slower than the page (or zooms) while scrolling, the
 * inner blocks on top of it.
 *
 * The background is a little larger than the section and parallax-view.js moves it with transform (smooth, and it
 * works on phones, unlike background-attachment: fixed). Without JavaScript, or with "reduce motion" on, it stays a
 * plain section with a background.
 *
 * @package EnvoBlocks
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Inner blocks.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_type   = Envo_Blocks_Blocks::choice( $attributes, 'mediaType', array( 'image', 'video' ) );
$envo_blocks_effect = Envo_Blocks_Blocks::choice( $attributes, 'effect', array( 'scroll', 'zoom', 'none' ) );
$envo_blocks_speed  = Envo_Blocks_Blocks::int( $attributes, 'speed', 30, 5, 80 );
$envo_blocks_screen = isset( $attributes['heightMode'] ) && 'screen' === $attributes['heightMode'];
$envo_blocks_height = $envo_blocks_screen
	? Envo_Blocks_Blocks::int( $attributes, 'minHeight', 80, 20, 100 ) . 'vh'
	: Envo_Blocks_Blocks::int( $attributes, 'minHeight', 520, 0, 1600 ) . 'px';
$envo_blocks_width  = Envo_Blocks_Blocks::int( $attributes, 'contentWidth', 0, 0, 2000 );
$envo_blocks_valign = Envo_Blocks_Blocks::choice( $attributes, 'verticalAlign', array( 'center', 'top', 'bottom' ) );

// Focal point of the background (0–1 each way).
$envo_blocks_focal = isset( $attributes['focalPoint'] ) && is_array( $attributes['focalPoint'] ) ? $attributes['focalPoint'] : array();
$envo_blocks_fx    = isset( $envo_blocks_focal['x'] ) ? max( 0, min( 1, (float) $envo_blocks_focal['x'] ) ) : 0.5;
$envo_blocks_fy    = isset( $envo_blocks_focal['y'] ) ? max( 0, min( 1, (float) $envo_blocks_focal['y'] ) ) : 0.5;
$envo_blocks_pos   = round( $envo_blocks_fx * 100, 1 ) . '% ' . round( $envo_blocks_fy * 100, 1 ) . '%';

// The background: an image of the media library (with srcset), an image or a video URL. WordPress decides about lazy
// loading (a hero at the top of the page loads at once).
$envo_blocks_id    = isset( $attributes['mediaId'] ) ? absint( $attributes['mediaId'] ) : 0;
$envo_blocks_url   = isset( $attributes['mediaUrl'] ) ? (string) $attributes['mediaUrl'] : '';
$envo_blocks_media = '';
// The ID counts only when it is the file of the address: an imported preset or page can bring the ID of another
// site (the import copies images and sets their new ID, but not videos).
if ( $envo_blocks_id && '' !== $envo_blocks_url ) {
	$envo_blocks_file = (string) wp_get_attachment_url( $envo_blocks_id );
	$envo_blocks_name = static function ( $address ) {
		return preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', strtolower( wp_basename( (string) wp_parse_url( $address, PHP_URL_PATH ) ) ) );
	};
	if ( '' === $envo_blocks_file || $envo_blocks_name( $envo_blocks_file ) !== $envo_blocks_name( $envo_blocks_url ) ) {
		$envo_blocks_id = 0;
	}
}
if ( 'video' === $envo_blocks_type ) {
	$envo_blocks_src = $envo_blocks_id ? (string) wp_get_attachment_url( $envo_blocks_id ) : $envo_blocks_url;
	if ( '' !== $envo_blocks_src ) {
		$envo_blocks_media = '<video class="sb-plx__img" src="' . esc_url( $envo_blocks_src ) . '" style="object-position:' . esc_attr( $envo_blocks_pos ) . '" autoplay muted loop playsinline aria-hidden="true"></video>';
	}
} else {
	$envo_blocks_alt = Envo_Blocks_Blocks::text( $attributes, 'mediaAlt' );
	if ( $envo_blocks_id && wp_attachment_is_image( $envo_blocks_id ) ) {
		$envo_blocks_media = wp_get_attachment_image(
			$envo_blocks_id,
			'full',
			false,
			array(
				'class'   => 'sb-plx__img',
				'alt'     => $envo_blocks_alt,
				'style'   => 'object-position:' . $envo_blocks_pos,
				'sizes'   => '100vw',
			)
		);
	} elseif ( '' !== $envo_blocks_url ) {
		$envo_blocks_media = '<img class="sb-plx__img" src="' . esc_url( $envo_blocks_url ) . '" alt="' . esc_attr( $envo_blocks_alt ) . '" style="object-position:' . esc_attr( $envo_blocks_pos ) . '" decoding="async">';
	}
}

$envo_blocks_vars  = '--sb-plx-h:' . $envo_blocks_height . ';';
$envo_blocks_vars .= '--sb-plx-speed:' . $envo_blocks_speed . ';';
$envo_blocks_vars .= '--sb-plx-overlay-o:' . ( Envo_Blocks_Blocks::int( $attributes, 'overlayOpacity', 40, 0, 100 ) / 100 ) . ';';
if ( $envo_blocks_width ) {
	$envo_blocks_vars .= '--sb-plx-w:' . $envo_blocks_width . 'px;';
}
$envo_blocks_vars .= Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'overlayColor'    => '--sb-plx-overlay',
		'textColor'       => '--sb-plx-text',
		'backgroundColor' => '--sb-plx-bg',
	)
);

$envo_blocks_classes = array( 'sb-plx', 'sb-plx--' . $envo_blocks_effect, 'sb-plx--' . $envo_blocks_valign );
if ( ! empty( $attributes['reverse'] ) ) {
	$envo_blocks_classes[] = 'sb-plx--reverse';
}
if ( isset( $attributes['onMobile'] ) && ! $attributes['onMobile'] ) {
	$envo_blocks_classes[] = 'sb-plx--still-mobile';
}
if ( '' === $envo_blocks_media ) {
	$envo_blocks_classes[] = 'sb-plx--no-media';
}

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $envo_blocks_classes ),
		'style' => $envo_blocks_vars,
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<?php if ( '' !== $envo_blocks_media ) : ?>
		<div class="sb-plx__media" aria-hidden="<?php echo '' === Envo_Blocks_Blocks::text( $attributes, 'mediaAlt' ) || 'video' === $envo_blocks_type ? 'true' : 'false'; ?>">
			<?php echo $envo_blocks_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() or escaped above. ?>
		</div>
	<?php endif; ?>
	<span class="sb-plx__overlay" aria-hidden="true"></span>
	<div class="sb-plx__content">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner blocks. ?>
	</div>
</div>
