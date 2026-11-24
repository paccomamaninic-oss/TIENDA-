<?php
/**
 * Countdown: to a date and time, or every day to a time on the chosen days (in the time zone of the site).
 *
 * The server renders the current state (also for visitors without JavaScript); countdown-view.js recalculates it in
 * the browser, so it stays right on cached pages. No "evergreen" tricks: the end is the same for every visitor.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_mode = Envo_Blocks_Blocks::choice( $attributes, 'mode', array( 'date', 'daily' ) );
$envo_blocks_tz   = wp_timezone();
$envo_blocks_now  = new DateTimeImmutable( 'now', $envo_blocks_tz );
$envo_blocks_end  = null;
$envo_blocks_time = '14:00';
$envo_blocks_days = array();

if ( 'date' === $envo_blocks_mode ) {
	$envo_blocks_raw = isset( $attributes['endDate'] ) && is_string( $attributes['endDate'] ) ? $attributes['endDate'] : '';
	$envo_blocks_end = preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $envo_blocks_raw ) ? DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', substr( $envo_blocks_raw, 0, 16 ), $envo_blocks_tz ) : false;

	if ( ! $envo_blocks_end ) {
		echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Choose the end date and time in the block settings.', 'envo-blocks' ) ) );
		return;
	}
} else {
	$envo_blocks_time = isset( $attributes['dailyTime'] ) && is_string( $attributes['dailyTime'] ) && preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $attributes['dailyTime'] ) ? $attributes['dailyTime'] : '14:00';
	$envo_blocks_days = isset( $attributes['days'] ) && is_array( $attributes['days'] ) ? $attributes['days'] : array( 1, 2, 3, 4, 5 );
	$envo_blocks_days = array_values(
		array_unique(
			array_filter(
				array_map( 'intval', $envo_blocks_days ),
				static function ( $day ) {
					return $day >= 1 && $day <= 7;
				}
			)
		)
	);

	if ( ! $envo_blocks_days ) {
		echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Choose at least one day in the block settings.', 'envo-blocks' ) ) );
		return;
	}

	// The next deadline: today when it is a chosen day and the time has not passed yet, else the next chosen day.
	list( $envo_blocks_h, $envo_blocks_m ) = array_map( 'intval', explode( ':', $envo_blocks_time ) );
	for ( $envo_blocks_i = 0; $envo_blocks_i <= 7 && ! $envo_blocks_end; $envo_blocks_i++ ) {
		$envo_blocks_day = $envo_blocks_now->setTime( $envo_blocks_h, $envo_blocks_m )->modify( '+' . $envo_blocks_i . ' days' );
		if ( in_array( (int) $envo_blocks_day->format( 'N' ), $envo_blocks_days, true ) && $envo_blocks_day > $envo_blocks_now ) {
			$envo_blocks_end = $envo_blocks_day;
		}
	}
}

$envo_blocks_left     = max( 0, $envo_blocks_end->getTimestamp() - $envo_blocks_now->getTimestamp() );
$envo_blocks_finished = Envo_Blocks_Blocks::choice( $attributes, 'finished', array( 'hide', 'text' ) );
$envo_blocks_outside  = Envo_Blocks_Blocks::choice( $attributes, 'outside', array( 'next', 'hide' ) );
$envo_blocks_done_txt = Envo_Blocks_Blocks::text( $attributes, 'finishedText', __( 'This offer has ended.', 'envo-blocks' ) );

// A date that has passed stays passed: nothing to show for "hide" (the editor keeps a note).
if ( 'date' === $envo_blocks_mode && ! $envo_blocks_left && 'hide' === $envo_blocks_finished ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'The countdown has ended and is hidden on the site. Choose a later date, or show a text when it ends.', 'envo-blocks' ) ) );
	return;
}

// Daily: outside the chosen days the countdown can wait hidden for the next day (the browser shows it then).
$envo_blocks_hidden = 'daily' === $envo_blocks_mode && 'hide' === $envo_blocks_outside && $envo_blocks_end->format( 'Y-m-d' ) !== $envo_blocks_now->format( 'Y-m-d' ) && ! Envo_Blocks_Blocks::is_preview();

$envo_blocks_show_days    = ! isset( $attributes['showDays'] ) || $attributes['showDays'];
$envo_blocks_show_seconds = ! isset( $attributes['showSeconds'] ) || $attributes['showSeconds'];
$envo_blocks_labels       = ! isset( $attributes['showLabels'] ) || $attributes['showLabels'];

$envo_blocks_units = array(
	'd' => array( __( 'Days', 'envo-blocks' ), $envo_blocks_show_days ? intdiv( $envo_blocks_left, 86400 ) : null ),
	'h' => array( __( 'Hours', 'envo-blocks' ), $envo_blocks_show_days ? intdiv( $envo_blocks_left % 86400, 3600 ) : intdiv( $envo_blocks_left, 3600 ) ),
	'm' => array( __( 'Minutes', 'envo-blocks' ), intdiv( $envo_blocks_left % 3600, 60 ) ),
	's' => array( __( 'Seconds', 'envo-blocks' ), $envo_blocks_show_seconds ? $envo_blocks_left % 60 : null ),
);

$envo_blocks_config = array_merge(
	Envo_Blocks_Blocks::clock(),
	array(
		'mode'     => $envo_blocks_mode,
		'end'      => $envo_blocks_end->getTimestamp() * 1000,
		'time'     => $envo_blocks_time,
		'days'     => $envo_blocks_days,
		'outside'  => $envo_blocks_outside,
		'finished' => $envo_blocks_finished,
		'showDays' => $envo_blocks_show_days,
	)
);

$envo_blocks_title   = Envo_Blocks_Blocks::text( $attributes, 'title' );
$envo_blocks_look    = Envo_Blocks_Blocks::choice( $attributes, 'look', array( 'boxes', 'plain' ) );
$envo_blocks_align   = Envo_Blocks_Blocks::choice( $attributes, 'textAlign', array( 'center', 'left', 'right' ) );
$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-countdown sb-countdown--' . $envo_blocks_look . ' sb-align-' . $envo_blocks_align . ( $envo_blocks_left ? '' : ' is-done' ),
		'style' => Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'numberColor'   => '--sb-cd-num',
				'boxBackground' => '--sb-cd-box',
				'labelColor'    => '--sb-cd-label',
				'textColor'     => '--sb-cd-text',
				'background'    => '--sb-cd-bg',
			)
		),
	)
);
/* translators: %s: date and time */
$envo_blocks_ends = sprintf( __( 'Ends %s', 'envo-blocks' ), wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $envo_blocks_end->getTimestamp() ) );
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-config="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>"<?php echo $envo_blocks_hidden ? ' hidden' : ''; ?>>
	<?php if ( '' !== $envo_blocks_title ) : ?>
		<p class="sb-countdown__title"><?php echo esc_html( $envo_blocks_title ); ?></p>
	<?php endif; ?>
	<div class="sb-countdown__units" role="timer" aria-live="off">
		<span class="sb-countdown__sr" data-sb-ends><?php echo esc_html( $envo_blocks_ends ); ?></span>
		<?php foreach ( $envo_blocks_units as $envo_blocks_key => $envo_blocks_unit ) : ?>
			<?php
			if ( null === $envo_blocks_unit[1] ) {
				continue;
			}
			?>
			<span class="sb-countdown__unit" aria-hidden="true">
				<span class="sb-countdown__num" data-unit="<?php echo esc_attr( $envo_blocks_key ); ?>"><?php echo esc_html( str_pad( (string) $envo_blocks_unit[1], 2, '0', STR_PAD_LEFT ) ); ?></span>
				<?php if ( $envo_blocks_labels ) : ?>
					<span class="sb-countdown__label"><?php echo esc_html( $envo_blocks_unit[0] ); ?></span>
				<?php endif; ?>
			</span>
		<?php endforeach; ?>
	</div>
	<?php if ( 'text' === $envo_blocks_finished && 'date' === $envo_blocks_mode ) : ?>
		<p class="sb-countdown__done"<?php echo $envo_blocks_left ? ' hidden' : ''; ?>><?php echo esc_html( $envo_blocks_done_txt ); ?></p>
	<?php endif; ?>
</div>
