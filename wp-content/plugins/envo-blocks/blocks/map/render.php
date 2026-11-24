<?php
/**
 * Map: a Google or OpenStreetMap map as the background of the block, the inner blocks on top of it (in a card or
 * free). With "load after a click" the map (and its cookies) loads only when the visitor asks for it.
 *
 * @package EnvoBlocks
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Inner blocks.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_url = Envo_Blocks_Blocks::map_url( $attributes );

if ( '' === $envo_blocks_url ) {
	return;
}

$envo_blocks_provider = 'osm' === ( isset( $attributes['provider'] ) ? $attributes['provider'] : '' ) ? 'osm' : 'google';
$envo_blocks_click    = ! empty( $attributes['clickToLoad'] );
$envo_blocks_screen   = isset( $attributes['heightMode'] ) && 'screen' === $attributes['heightMode'];
$envo_blocks_height   = $envo_blocks_screen
	? Envo_Blocks_Blocks::int( $attributes, 'height', 80, 30, 100 ) . 'vh'
	: Envo_Blocks_Blocks::int( $attributes, 'height', 480, 200, 1400 ) . 'px';
$envo_blocks_has_card = '' !== trim( wp_strip_all_tags( $content, true ) ) || false !== strpos( $content, '<img' );
$envo_blocks_title    = Envo_Blocks_Blocks::text( $attributes, 'address', __( 'Map', 'envo-blocks' ) );

$envo_blocks_vars  = '--sb-map-h:' . $envo_blocks_height . ';';
$envo_blocks_vars .= '--sb-map-card-w:' . Envo_Blocks_Blocks::int( $attributes, 'cardWidth', 380, 200, 900 ) . 'px;';
$envo_blocks_vars .= '--sb-map-card-r:' . Envo_Blocks_Blocks::int( $attributes, 'cardRadius', 12, 0, 40 ) . 'px;';
$envo_blocks_vars .= Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'overlayColor'   => '--sb-map-overlay',
		'cardBackground' => '--sb-map-card-bg',
		'cardText'       => '--sb-map-card-text',
	)
);

$envo_blocks_classes = array(
	'sb-map',
	'sb-map--' . Envo_Blocks_Blocks::choice( $attributes, 'cardPosition', array( 'left', 'center', 'right' ) ),
	'sb-map--' . Envo_Blocks_Blocks::choice( $attributes, 'cardVertical', array( 'middle', 'top', 'bottom' ) ),
);
if ( ! empty( $attributes['grayscale'] ) ) {
	$envo_blocks_classes[] = 'sb-map--grayscale';
}
if ( isset( $attributes['interactive'] ) && ! $attributes['interactive'] ) {
	$envo_blocks_classes[] = 'sb-map--static';
}
if ( ! isset( $attributes['showCard'] ) || $attributes['showCard'] ) {
	$envo_blocks_classes[] = 'sb-map--card';
}
if ( ! isset( $attributes['stackOnMobile'] ) || $attributes['stackOnMobile'] ) {
	$envo_blocks_classes[] = 'sb-map--stack';
}
if ( ! empty( $attributes['overlayColor'] ) ) {
	$envo_blocks_classes[] = 'sb-map--overlay';
}
if ( $envo_blocks_has_card ) {
	$envo_blocks_classes[] = 'sb-map--has-content';
}

$envo_blocks_iframe = sprintf(
	'<iframe class="sb-map__frame" src="%1$s" title="%2$s" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>',
	esc_url( $envo_blocks_url ),
	esc_attr( $envo_blocks_title )
);

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $envo_blocks_classes ),
		'style' => $envo_blocks_vars,
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<div class="sb-map__map">
		<?php if ( $envo_blocks_click ) : ?>
			<div class="sb-map__consent" data-sb-map-src="<?php echo esc_url( $envo_blocks_url ); ?>" data-sb-map-title="<?php echo esc_attr( $envo_blocks_title ); ?>">
				<p class="sb-map__notice">
					<?php
					echo esc_html(
						Envo_Blocks_Blocks::text(
							$attributes,
							'loadNotice',
							'osm' === $envo_blocks_provider
								? __( 'The map is loaded from OpenStreetMap.', 'envo-blocks' )
								: __( 'The map is loaded from Google Maps, which may store cookies.', 'envo-blocks' )
						)
					);
					?>
				</p>
				<button type="button" class="sb-map__load wp-element-button"><?php echo esc_html( Envo_Blocks_Blocks::text( $attributes, 'loadButtonText', __( 'Show the map', 'envo-blocks' ) ) ); ?></button>
			</div>
		<?php else : ?>
			<?php echo wp_kses( $envo_blocks_iframe, Envo_Blocks_Blocks::allowed_html() ); ?>
		<?php endif; ?>
	</div>
	<?php if ( $envo_blocks_has_card ) : ?>
		<div class="sb-map__content"><?php echo wp_kses( $content, Envo_Blocks_Blocks::allowed_html() ); ?></div>
	<?php endif; ?>
</div>
