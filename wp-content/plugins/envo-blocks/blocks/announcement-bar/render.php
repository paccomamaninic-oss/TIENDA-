<?php
/**
 * Announcement Bar: messages that take turns (rotate), scroll as a ticker (marquee) or sit side by side (static).
 *
 * announcement-view.js runs the rotation and the ticker; with "reduce motion" nothing moves on its own (the arrows
 * still work). A closed bar stays hidden in that browser for the chosen number of days (0 = until the browser tab
 * is closed); changing the messages shows it again.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_raw      = isset( $attributes['messages'] ) && is_array( $attributes['messages'] ) && $attributes['messages'] ? $attributes['messages'] : Envo_Blocks_Blocks::default_announcements();
$envo_blocks_messages = array();

foreach ( $envo_blocks_raw as $envo_blocks_item ) {
	$envo_blocks_text = is_array( $envo_blocks_item ) ? Envo_Blocks_Blocks::text( $envo_blocks_item, 'text' ) : '';
	if ( '' === $envo_blocks_text ) {
		continue;
	}
	$envo_blocks_messages[] = array(
		'text' => $envo_blocks_text,
		'url'  => isset( $envo_blocks_item['url'] ) && is_string( $envo_blocks_item['url'] ) ? esc_url( trim( $envo_blocks_item['url'] ) ) : '',
		'icon' => isset( $envo_blocks_item['icon'] ) && is_string( $envo_blocks_item['icon'] ) && in_array( $envo_blocks_item['icon'], Envo_Blocks_Blocks::icon_names(), true ) ? $envo_blocks_item['icon'] : '',
	);
}

if ( ! $envo_blocks_messages ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Add a message in the block settings.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_mode = Envo_Blocks_Blocks::choice( $attributes, 'mode', array( 'rotate', 'marquee', 'static' ) );
if ( 1 === count( $envo_blocks_messages ) && 'rotate' === $envo_blocks_mode ) {
	$envo_blocks_mode = 'static';
}

$envo_blocks_dismiss = ! empty( $attributes['dismissible'] );
$envo_blocks_id      = substr( md5( (string) wp_json_encode( $envo_blocks_messages ) ), 0, 10 );
$envo_blocks_arrows  = 'rotate' === $envo_blocks_mode && ( ! isset( $attributes['arrows'] ) || $attributes['arrows'] );

$envo_blocks_config = array(
	'mode'     => $envo_blocks_mode,
	'interval' => Envo_Blocks_Blocks::int( $attributes, 'interval', 5, 2, 30 ),
	'speed'    => Envo_Blocks_Blocks::int( $attributes, 'speed', 60, 10, 300 ),
	'id'       => $envo_blocks_id,
	'days'     => Envo_Blocks_Blocks::int( $attributes, 'dismissDays', 7, 0, 365 ),
);

$envo_blocks_align   = Envo_Blocks_Blocks::choice( $attributes, 'textAlign', array( 'center', 'left', 'right' ) );
$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class'      => 'sb-announce sb-announce--' . $envo_blocks_mode . ' sb-align-' . $envo_blocks_align,
		'style'      => Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'background' => '--sb-announce-bg',
				'textColor'  => '--sb-announce-text',
				'iconColor'  => '--sb-announce-icon',
			)
		),
		'role'       => 'region',
		'aria-label' => __( 'Announcements', 'envo-blocks' ),
	)
);

$envo_blocks_list = static function ( $hidden ) use ( $envo_blocks_messages, $envo_blocks_mode ) {
	// The copy that makes the ticker seamless is hidden from screen readers (announcement-view.js also makes it inert).
	$html = '<ul class="sb-announce__list' . ( $hidden ? ' is-copy' : '' ) . '"' . ( $hidden ? ' aria-hidden="true"' : '' ) . '>';
	foreach ( $envo_blocks_messages as $i => $message ) {
		$icon  = '' !== $message['icon'] ? Envo_Blocks_Blocks::icon( $message['icon'], 'sb-announce__icon' ) : '';
		$text  = '<span class="sb-announce__text">' . esc_html( $message['text'] ) . '</span>';
		$body  = '' !== $message['url'] ? '<a class="sb-announce__link" href="' . esc_url( $message['url'] ) . '">' . $icon . $text . '</a>' : $icon . $text;
		$state = 'rotate' === $envo_blocks_mode && 0 === $i ? ' is-active' : '';
		$html .= '<li class="sb-announce__item' . $state . '">' . $body . '</li>';
	}
	return $html . '</ul>';
};
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-config="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>">
	<div class="sb-announce__inner">
		<?php if ( $envo_blocks_arrows ) : ?>
			<button type="button" class="sb-announce__arrow" data-sb-step="-1" aria-label="<?php esc_attr_e( 'Previous message', 'envo-blocks' ); ?>">&#8249;</button>
		<?php endif; ?>
		<div class="sb-announce__viewport"<?php echo 'rotate' === $envo_blocks_mode ? ' aria-live="polite"' : ''; ?>>
			<?php if ( 'marquee' === $envo_blocks_mode ) : ?>
				<div class="sb-announce__track">
					<?php echo wp_kses( $envo_blocks_list( false ) . $envo_blocks_list( true ), Envo_Blocks_Blocks::allowed_html() ); ?>
				</div>
			<?php else : ?>
				<?php echo wp_kses( $envo_blocks_list( false ), Envo_Blocks_Blocks::allowed_html() ); ?>
			<?php endif; ?>
		</div>
		<?php if ( $envo_blocks_arrows ) : ?>
			<button type="button" class="sb-announce__arrow" data-sb-step="1" aria-label="<?php esc_attr_e( 'Next message', 'envo-blocks' ); ?>">&#8250;</button>
		<?php endif; ?>
		<?php if ( $envo_blocks_dismiss ) : ?>
			<button type="button" class="sb-announce__close" data-sb-close aria-label="<?php esc_attr_e( 'Close the announcement', 'envo-blocks' ); ?>">&times;</button>
		<?php endif; ?>
	</div>
</div>
<?php
// A bar closed earlier is hidden before it is painted (the view script loads later).
if ( $envo_blocks_dismiss && ! Envo_Blocks_Blocks::is_preview() ) {
	wp_print_inline_script_tag(
		'(function(b){try{var v=localStorage.getItem("envoBlocksBar:' . $envo_blocks_id . '")||sessionStorage.getItem("envoBlocksBar:' . $envo_blocks_id . '");if(v!==null&&(+v===0||+v>Date.now()))b.hidden=true;}catch(e){}})(document.currentScript.previousElementSibling);'
	);
}
