<?php
/**
 * Opening Hours: the week (one line per day, ranges like "9:00-12:00, 13:00-18:00", empty = closed), special days
 * and a live "Open now / Closed · opens …" status.
 *
 * The server renders the current state; hours-view.js works out the status and "today" again in the browser, in
 * the time zone of the site, because pages are often cached.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

global $wp_locale;

$envo_blocks_lines = isset( $attributes['hours'] ) && is_array( $attributes['hours'] ) && 7 === count( $attributes['hours'] ) ? $attributes['hours'] : Envo_Blocks_Blocks::default_hours();
$envo_blocks_week  = array(); // ISO day => ranges in minutes.
foreach ( array_values( $envo_blocks_lines ) as $envo_blocks_i => $envo_blocks_line ) {
	$envo_blocks_week[ $envo_blocks_i + 1 ] = Envo_Blocks_Blocks::time_ranges( is_string( $envo_blocks_line ) ? $envo_blocks_line : '' );
}

// Special days: "2026-12-24 = 9:00-12:00 | Christmas Eve", "12-25 =" (closed every year).
$envo_blocks_special = array();
$envo_blocks_raw     = isset( $attributes['exceptions'] ) && is_string( $attributes['exceptions'] ) ? $attributes['exceptions'] : '';
foreach ( preg_split( '/\r\n|\r|\n/', $envo_blocks_raw ) as $envo_blocks_line ) {
	if ( ! preg_match( '/^\s*(?:(\d{4})-)?(\d{1,2})-(\d{1,2})\s*[=:]?\s*([^|]*?)\s*(?:\|\s*(.*?))?\s*$/u', $envo_blocks_line, $envo_blocks_m ) ) {
		continue;
	}
	$envo_blocks_key                         = ( '' !== $envo_blocks_m[1] ? $envo_blocks_m[1] . '-' : '' ) . sprintf( '%02d-%02d', (int) $envo_blocks_m[2], (int) $envo_blocks_m[3] );
	$envo_blocks_special[ $envo_blocks_key ] = array(
		'ranges' => Envo_Blocks_Blocks::time_ranges( $envo_blocks_m[4] ),
		'note'   => isset( $envo_blocks_m[5] ) ? sanitize_text_field( $envo_blocks_m[5] ) : '',
	);
}

$envo_blocks_tz     = wp_timezone();
$envo_blocks_now    = new DateTimeImmutable( 'now', $envo_blocks_tz );
$envo_blocks_today  = $envo_blocks_now->setTime( 0, 0 );
$envo_blocks_minute = (int) $envo_blocks_now->format( 'G' ) * 60 + (int) $envo_blocks_now->format( 'i' );
$envo_blocks_closed = Envo_Blocks_Blocks::text( $attributes, 'closedText', __( 'Closed', 'envo-blocks' ) );

$envo_blocks_ranges_on = static function ( DateTimeImmutable $date ) use ( $envo_blocks_week, $envo_blocks_special ) {
	foreach ( array( $date->format( 'Y-m-d' ), $date->format( 'm-d' ) ) as $key ) {
		if ( isset( $envo_blocks_special[ $key ] ) ) {
			return $envo_blocks_special[ $key ]['ranges'];
		}
	}
	return $envo_blocks_week[ (int) $date->format( 'N' ) ];
};

$envo_blocks_text_of = static function ( $ranges ) use ( $envo_blocks_closed ) {
	if ( ! $ranges ) {
		return $envo_blocks_closed;
	}
	$parts = array();
	foreach ( $ranges as $range ) {
		$parts[] = Envo_Blocks_Blocks::format_minutes( $range[0] ) . ' – ' . Envo_Blocks_Blocks::format_minutes( $range[1] );
	}
	return implode( ', ', $parts );
};

// Day names (ISO 1 = Monday) in the order of the week of the site (Settings > General > Week starts on).
$envo_blocks_names = array();
for ( $envo_blocks_d = 1; $envo_blocks_d <= 7; $envo_blocks_d++ ) {
	$envo_blocks_names[ $envo_blocks_d ] = $wp_locale->get_weekday( $envo_blocks_d % 7 );
}
$envo_blocks_start = (int) get_option( 'start_of_week', 1 );
$envo_blocks_order = array();
for ( $envo_blocks_d = 0; $envo_blocks_d < 7; $envo_blocks_d++ ) {
	$envo_blocks_order[] = ( ( $envo_blocks_start + $envo_blocks_d + 6 ) % 7 ) + 1;
}

// Table rows, consecutive days with the same hours joined when asked.
$envo_blocks_rows = array();
foreach ( $envo_blocks_order as $envo_blocks_iso ) {
	$envo_blocks_value = $envo_blocks_text_of( $envo_blocks_week[ $envo_blocks_iso ] );
	$envo_blocks_last  = count( $envo_blocks_rows ) - 1;
	if ( ! empty( $attributes['groupDays'] ) && $envo_blocks_last >= 0 && $envo_blocks_rows[ $envo_blocks_last ]['value'] === $envo_blocks_value ) {
		$envo_blocks_rows[ $envo_blocks_last ]['days'][] = $envo_blocks_iso;
		continue;
	}
	$envo_blocks_rows[] = array(
		'days'  => array( $envo_blocks_iso ),
		'value' => $envo_blocks_value,
	);
}

// Status now (the same rules as hours-view.js).
$envo_blocks_texts = array(
	/* translators: %s: closing time */
	'open'     => __( 'Open now · closes at %s', 'envo-blocks' ),
	/* translators: 1: "today", "tomorrow" or a day name, 2: opening time */
	'closed'   => __( 'Closed · opens %1$s at %2$s', 'envo-blocks' ),
	'closedAll' => __( 'Closed', 'envo-blocks' ),
	'today'    => __( 'today', 'envo-blocks' ),
	'tomorrow' => __( 'tomorrow', 'envo-blocks' ),
);
$envo_blocks_status = array( 'closed', $envo_blocks_texts['closedAll'] );
$envo_blocks_yday   = $envo_blocks_ranges_on( $envo_blocks_today->modify( '-1 day' ) );
foreach ( $envo_blocks_ranges_on( $envo_blocks_today ) as $envo_blocks_range ) {
	if ( $envo_blocks_range[0] <= $envo_blocks_minute && $envo_blocks_minute < $envo_blocks_range[1] ) {
		$envo_blocks_status = array( 'open', sprintf( $envo_blocks_texts['open'], Envo_Blocks_Blocks::format_minutes( $envo_blocks_range[1] ) ) );
	}
}
foreach ( $envo_blocks_yday as $envo_blocks_range ) {
	if ( $envo_blocks_range[1] > 1440 && $envo_blocks_minute < $envo_blocks_range[1] - 1440 ) {
		$envo_blocks_status = array( 'open', sprintf( $envo_blocks_texts['open'], Envo_Blocks_Blocks::format_minutes( $envo_blocks_range[1] ) ) );
	}
}
if ( 'closed' === $envo_blocks_status[0] ) {
	for ( $envo_blocks_d = 0; $envo_blocks_d <= 7; $envo_blocks_d++ ) {
		$envo_blocks_date = $envo_blocks_today->modify( '+' . $envo_blocks_d . ' days' );
		foreach ( $envo_blocks_ranges_on( $envo_blocks_date ) as $envo_blocks_range ) {
			if ( $envo_blocks_d * 1440 + $envo_blocks_range[0] > $envo_blocks_minute ) {
				$envo_blocks_when   = 0 === $envo_blocks_d ? $envo_blocks_texts['today'] : ( 1 === $envo_blocks_d ? $envo_blocks_texts['tomorrow'] : $envo_blocks_names[ (int) $envo_blocks_date->format( 'N' ) ] );
				$envo_blocks_status = array( 'closed', sprintf( $envo_blocks_texts['closed'], $envo_blocks_when, Envo_Blocks_Blocks::format_minutes( $envo_blocks_range[0] ) ) );
				break 2;
			}
		}
	}
}

// Special days of the next 30 days (the browser drops those that have passed on a cached page).
$envo_blocks_upcoming = array();
foreach ( $envo_blocks_special as $envo_blocks_key => $envo_blocks_day ) {
	$envo_blocks_date = 10 === strlen( $envo_blocks_key ) ? DateTimeImmutable::createFromFormat( '!Y-m-d', $envo_blocks_key, $envo_blocks_tz ) : DateTimeImmutable::createFromFormat( '!Y-m-d', $envo_blocks_today->format( 'Y' ) . '-' . $envo_blocks_key, $envo_blocks_tz );
	if ( ! $envo_blocks_date ) {
		continue;
	}
	if ( 5 === strlen( $envo_blocks_key ) && $envo_blocks_date < $envo_blocks_today ) {
		$envo_blocks_date = $envo_blocks_date->modify( '+1 year' );
	}
	$envo_blocks_diff = (int) $envo_blocks_today->diff( $envo_blocks_date )->format( '%r%a' );
	if ( $envo_blocks_diff >= 0 && $envo_blocks_diff <= 30 ) {
		$envo_blocks_upcoming[ $envo_blocks_date->format( 'Y-m-d' ) ] = array(
			'label' => wp_date( 'l, ' . get_option( 'date_format' ), $envo_blocks_date->setTime( 12, 0 )->getTimestamp() ),
			'value' => $envo_blocks_text_of( $envo_blocks_day['ranges'] ),
			'note'  => $envo_blocks_day['note'],
		);
	}
}
ksort( $envo_blocks_upcoming );

// Times for the browser: every start and end in the format of the site.
$envo_blocks_fmt = array();
foreach ( array_merge( array_values( $envo_blocks_week ), wp_list_pluck( $envo_blocks_special, 'ranges' ) ) as $envo_blocks_list ) {
	foreach ( $envo_blocks_list as $envo_blocks_range ) {
		foreach ( $envo_blocks_range as $envo_blocks_value ) {
			$envo_blocks_fmt[ $envo_blocks_value ] = Envo_Blocks_Blocks::format_minutes( $envo_blocks_value );
		}
	}
}

$envo_blocks_config = array_merge(
	Envo_Blocks_Blocks::clock(),
	array(
		'week'    => $envo_blocks_week,
		'special' => array_map(
			static function ( $day ) {
				return $day['ranges'];
			},
			$envo_blocks_special
		),
		'fmt'     => (object) $envo_blocks_fmt,
		'names'   => $envo_blocks_names,
		'texts'   => $envo_blocks_texts,
	)
);

$envo_blocks_title  = Envo_Blocks_Blocks::text( $attributes, 'title' );
$envo_blocks_status_on = ! isset( $attributes['showStatus'] ) || $attributes['showStatus'];
$envo_blocks_table  = ! isset( $attributes['showTable'] ) || $attributes['showTable'];
$envo_blocks_hl     = ! isset( $attributes['highlightToday'] ) || $attributes['highlightToday'];
$envo_blocks_list   = ( ! isset( $attributes['showExceptions'] ) || $attributes['showExceptions'] ) && $envo_blocks_upcoming;
$envo_blocks_today_iso = (int) $envo_blocks_now->format( 'N' );
$envo_blocks_style  = Envo_Blocks_Blocks::choice( $attributes, 'boxStyle', array( 'box', 'plain' ) );
$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-hours sb-hours--' . $envo_blocks_style . ( $envo_blocks_hl ? ' sb-hours--today' : '' ),
		'style' => Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'openColor'       => '--sb-hours-open',
				'closedColor'     => '--sb-hours-closed',
				'todayBackground' => '--sb-hours-today',
				'textColor'       => '--sb-hours-text',
				'background'      => '--sb-hours-bg',
			)
		),
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-config="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>">
	<?php if ( '' !== $envo_blocks_title ) : ?>
		<p class="sb-hours__title"><?php echo esc_html( $envo_blocks_title ); ?></p>
	<?php endif; ?>
	<?php if ( $envo_blocks_status_on ) : ?>
		<p class="sb-hours__status is-<?php echo esc_attr( $envo_blocks_status[0] ); ?>" data-sb-status>
			<span class="sb-hours__dot" aria-hidden="true"></span><span data-sb-status-text><?php echo esc_html( $envo_blocks_status[1] ); ?></span>
		</p>
	<?php endif; ?>
	<?php if ( $envo_blocks_table ) : ?>
		<table class="sb-hours__table">
			<tbody>
				<?php foreach ( $envo_blocks_rows as $envo_blocks_row ) : ?>
					<?php
					$envo_blocks_first = reset( $envo_blocks_row['days'] );
					$envo_blocks_lastd = end( $envo_blocks_row['days'] );
					$envo_blocks_label = $envo_blocks_names[ $envo_blocks_first ] . ( $envo_blocks_first !== $envo_blocks_lastd ? ' – ' . $envo_blocks_names[ $envo_blocks_lastd ] : '' );
					?>
					<tr data-days="<?php echo esc_attr( implode( ',', $envo_blocks_row['days'] ) ); ?>"<?php echo $envo_blocks_hl && in_array( $envo_blocks_today_iso, $envo_blocks_row['days'], true ) ? ' class="is-today"' : ''; ?>>
						<th scope="row"><?php echo esc_html( $envo_blocks_label ); ?></th>
						<td<?php echo $envo_blocks_closed === $envo_blocks_row['value'] ? ' class="is-closed"' : ''; ?>><?php echo esc_html( $envo_blocks_row['value'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<?php if ( $envo_blocks_list ) : ?>
		<div class="sb-hours__special">
			<p class="sb-hours__special-title"><?php esc_html_e( 'Special opening hours', 'envo-blocks' ); ?></p>
			<ul>
				<?php foreach ( $envo_blocks_upcoming as $envo_blocks_date => $envo_blocks_day ) : ?>
					<li data-date="<?php echo esc_attr( $envo_blocks_date ); ?>">
						<span class="sb-hours__special-day"><?php echo esc_html( $envo_blocks_day['label'] ); ?></span>
						<span class="sb-hours__special-value"><?php echo esc_html( $envo_blocks_day['value'] . ( '' !== $envo_blocks_day['note'] ? ' · ' . $envo_blocks_day['note'] : '' ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
