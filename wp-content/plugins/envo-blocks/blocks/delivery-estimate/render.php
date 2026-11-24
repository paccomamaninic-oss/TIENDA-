<?php
/**
 * Delivery Estimate: countdown to the cut-off time and the estimated delivery dates.
 *
 * The server renders the current state (also for visitors without JavaScript and for the editor);
 * delivery-view.js recalculates it in the browser, so it stays correct on cached pages, and runs the countdown.
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

// On a product that cannot be bought now there is nothing to ship.
if ( ( ! isset( $attributes['hideOutOfStock'] ) || $attributes['hideOutOfStock'] ) && ! Envo_Blocks_Blocks::is_preview() && function_exists( 'wc_get_product' ) ) {
	$envo_blocks_product_id = isset( $block->context['postId'] ) && 'product' === ( isset( $block->context['postType'] ) ? $block->context['postType'] : '' ) ? (int) $block->context['postId'] : ( is_singular( 'product' ) ? get_queried_object_id() : 0 );
	$envo_blocks_product    = $envo_blocks_product_id ? wc_get_product( $envo_blocks_product_id ) : null;

	if ( $envo_blocks_product && ( ! $envo_blocks_product->is_in_stock() || ! $envo_blocks_product->is_purchasable() ) ) {
		return;
	}
}

$envo_blocks_days_attr = static function ( $key ) use ( $attributes ) {
	$days = isset( $attributes[ $key ] ) && is_array( $attributes[ $key ] ) ? $attributes[ $key ] : array( 1, 2, 3, 4, 5 );
	$days = array_values( array_unique( array_filter( array_map( 'intval', $days ), static function ( $day ) {
		return $day >= 1 && $day <= 7;
	} ) ) );
	sort( $days );

	return $days ? $days : array( 1, 2, 3, 4, 5 );
};

$envo_blocks_ship_days     = $envo_blocks_days_attr( 'shipDays' );
$envo_blocks_delivery_days = $envo_blocks_days_attr( 'deliveryDays' );

// Holidays: "YYYY-MM-DD" (one date) or "MM-DD" (every year), separated by commas or new lines.
$envo_blocks_holidays = array();
foreach ( preg_split( '/[\s,;]+/', isset( $attributes['holidays'] ) && is_string( $attributes['holidays'] ) ? $attributes['holidays'] : '' ) as $envo_blocks_day ) {
	if ( preg_match( '/^(\d{4}-)?\d{2}-\d{2}$/', $envo_blocks_day ) ) {
		$envo_blocks_holidays[] = $envo_blocks_day;
	}
}

$envo_blocks_cutoff = isset( $attributes['cutoff'] ) && is_string( $attributes['cutoff'] ) && preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $attributes['cutoff'], $envo_blocks_m ) ? array( (int) $envo_blocks_m[1], (int) $envo_blocks_m[2] ) : array( 14, 0 );

$envo_blocks_processing = Envo_Blocks_Blocks::int( $attributes, 'processingDays', 0, 0, 30 );
$envo_blocks_min        = Envo_Blocks_Blocks::int( $attributes, 'deliveryMin', 1, 0, 60 );
$envo_blocks_max        = max( $envo_blocks_min, Envo_Blocks_Blocks::int( $attributes, 'deliveryMax', 2, 0, 60 ) );

$envo_blocks_is_day = static function ( DateTimeImmutable $date, $days ) use ( $envo_blocks_holidays ) {
	return in_array( (int) $date->format( 'N' ), $days, true ) && ! in_array( $date->format( 'Y-m-d' ), $envo_blocks_holidays, true ) && ! in_array( $date->format( 'm-d' ), $envo_blocks_holidays, true );
};

$envo_blocks_next = static function ( DateTimeImmutable $date, $days, $count ) use ( $envo_blocks_is_day ) {
	$guard = 0;
	while ( $count > 0 && $guard < 400 ) {
		$date = $date->modify( '+1 day' );
		if ( $envo_blocks_is_day( $date, $days ) ) {
			--$count;
		}
		++$guard;
	}

	return $date;
};

$envo_blocks_now      = new DateTimeImmutable( 'now', wp_timezone() );
$envo_blocks_today    = $envo_blocks_now->setTime( 0, 0 );
$envo_blocks_deadline = $envo_blocks_today->setTime( $envo_blocks_cutoff[0], $envo_blocks_cutoff[1] );
$envo_blocks_ship     = $envo_blocks_is_day( $envo_blocks_today, $envo_blocks_ship_days ) && $envo_blocks_now < $envo_blocks_deadline ? $envo_blocks_today : $envo_blocks_next( $envo_blocks_today, $envo_blocks_ship_days, 1 );
$envo_blocks_ship     = $envo_blocks_next( $envo_blocks_ship, $envo_blocks_ship_days, $envo_blocks_processing );
$envo_blocks_from     = $envo_blocks_next( $envo_blocks_ship, $envo_blocks_delivery_days, $envo_blocks_min );
$envo_blocks_to       = $envo_blocks_next( $envo_blocks_ship, $envo_blocks_delivery_days, $envo_blocks_max );
$envo_blocks_is_today = $envo_blocks_ship == $envo_blocks_today; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- Compares dates.

$envo_blocks_messages = array(
	'today'    => Envo_Blocks_Blocks::text( $attributes, 'messageToday', __( 'Order within {countdown} and we ship it today.', 'envo-blocks' ) ),
	'later'    => Envo_Blocks_Blocks::text( $attributes, 'messageLater', __( 'Order now and we ship it {date}.', 'envo-blocks' ) ),
	'delivery' => Envo_Blocks_Blocks::text( $attributes, 'messageDelivery', __( 'Estimated delivery: {delivery}', 'envo-blocks' ) ),
);

$envo_blocks_long  = static function ( DateTimeImmutable $date ) use ( $envo_blocks_today ) {
	if ( $date == $envo_blocks_today->modify( '+1 day' ) ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
		return __( 'tomorrow', 'envo-blocks' );
	}

	/* translators: Long date format of the shipping day, see https://www.php.net/manual/datetime.format.php */
	return wp_date( __( 'l, F j', 'envo-blocks' ), $date->getTimestamp(), $date->getTimezone() );
};
$envo_blocks_short = static function ( DateTimeImmutable $date ) {
	/* translators: Short date format of the delivery days, see https://www.php.net/manual/datetime.format.php */
	return wp_date( __( 'D, M j', 'envo-blocks' ), $date->getTimestamp(), $date->getTimezone() );
};

$envo_blocks_left      = (int) ceil( max( 0, $envo_blocks_deadline->getTimestamp() - $envo_blocks_now->getTimestamp() ) / 60 );
$envo_blocks_countdown = sprintf(
	/* translators: 1: hours, 2: minutes */
	__( '%1$d h %2$d min', 'envo-blocks' ),
	intdiv( $envo_blocks_left, 60 ),
	$envo_blocks_left % 60
);

$envo_blocks_highlight = static function ( $value ) {
	return '<strong class="sb-delivery__hl">' . esc_html( $value ) . '</strong>';
};

$envo_blocks_line = $envo_blocks_is_today
	? str_replace( '{countdown}', '<strong class="sb-delivery__hl" data-sb-countdown>' . esc_html( $envo_blocks_countdown ) . '</strong>', esc_html( $envo_blocks_messages['today'] ) )
	: str_replace( '{date}', $envo_blocks_highlight( $envo_blocks_long( $envo_blocks_ship ) ), esc_html( $envo_blocks_messages['later'] ) );

$envo_blocks_range = $envo_blocks_from == $envo_blocks_to ? $envo_blocks_short( $envo_blocks_from ) : $envo_blocks_short( $envo_blocks_from ) . ' – ' . $envo_blocks_short( $envo_blocks_to ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
$envo_blocks_show  = ! isset( $attributes['showDelivery'] ) || $attributes['showDelivery'];

$envo_blocks_tz = wp_timezone_string();

$envo_blocks_config = array(
	'cutoff'       => $envo_blocks_cutoff,
	'shipDays'     => $envo_blocks_ship_days,
	'deliveryDays' => $envo_blocks_delivery_days,
	'holidays'     => $envo_blocks_holidays,
	'processing'   => $envo_blocks_processing,
	'min'          => $envo_blocks_min,
	'max'          => $envo_blocks_max,
	'tz'           => preg_match( '/^[+-]\d{2}:\d{2}$/', $envo_blocks_tz ) ? '' : $envo_blocks_tz,
	'offset'       => $envo_blocks_now->getOffset(),
	'locale'       => str_replace( '_', '-', determine_locale() ),
	'seconds'      => ! empty( $attributes['showSeconds'] ),
	'messages'     => $envo_blocks_messages,
	'words'        => array(
		'tomorrow' => __( 'tomorrow', 'envo-blocks' ),
		/* translators: 1: hours, 2: minutes */
		'hm'       => __( '%1$d h %2$d min', 'envo-blocks' ),
		/* translators: 1: hours, 2: minutes, 3: seconds */
		'hms'      => __( '%1$d h %2$d min %3$d s', 'envo-blocks' ),
	),
);

$envo_blocks_style   = Envo_Blocks_Blocks::choice( $attributes, 'boxStyle', array( 'box', 'plain', 'line' ) );
$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-delivery sb-delivery--' . $envo_blocks_style,
		'style' => Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'iconColor'      => '--sb-delivery-icon',
				'highlightColor' => '--sb-delivery-hl',
				'textColor'      => '--sb-delivery-text',
				'background'     => '--sb-delivery-bg',
			)
		),
	)
);
$envo_blocks_icon    = ! isset( $attributes['showIcon'] ) || $attributes['showIcon'];
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-config="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>">
	<p class="sb-delivery__row sb-delivery__ship">
		<?php echo wp_kses( $envo_blocks_icon ? Envo_Blocks_Blocks::icon( 'clock', 'sb-delivery__icon' ) : '', Envo_Blocks_Blocks::allowed_html() ); ?>
		<span data-sb-ship><?php echo wp_kses( $envo_blocks_line, Envo_Blocks_Blocks::allowed_html() ); ?></span>
	</p>
	<?php if ( $envo_blocks_show ) : ?>
		<p class="sb-delivery__row sb-delivery__arrive">
			<?php echo wp_kses( $envo_blocks_icon ? Envo_Blocks_Blocks::icon( 'truck', 'sb-delivery__icon' ) : '', Envo_Blocks_Blocks::allowed_html() ); ?>
			<span data-sb-arrive><?php echo wp_kses( str_replace( '{delivery}', $envo_blocks_highlight( $envo_blocks_range ), esc_html( $envo_blocks_messages['delivery'] ) ), Envo_Blocks_Blocks::allowed_html() ); ?></span>
		</p>
	<?php endif; ?>
</div>
