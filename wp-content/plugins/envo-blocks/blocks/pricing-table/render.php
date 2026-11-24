<?php
/**
 * Pricing Table: plans with an old (crossed-out) price, billing period, badge, features and a button.
 *
 * Features are lines of text; a line starting with "-" is shown as not included.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_plans = isset( $attributes['plans'] ) && is_array( $attributes['plans'] ) ? $attributes['plans'] : Envo_Blocks_Blocks::default_plans();

$envo_blocks_currency = isset( $attributes['currency'] ) && is_string( $attributes['currency'] ) ? mb_substr( sanitize_text_field( $attributes['currency'] ), 0, 8 ) : '$';
$envo_blocks_after    = 'after' === ( isset( $attributes['currencyPosition'] ) ? $attributes['currencyPosition'] : '' );
$envo_blocks_savings  = ! isset( $attributes['showSavings'] ) || $attributes['showSavings'];
/* translators: %s: discount in percent, e.g. 25% */
$envo_blocks_save_tpl = Envo_Blocks_Blocks::text( $attributes, 'savingsText', __( 'Save %s', 'envo-blocks' ) );

// "1 234,50" or "29.90" -> float; anything else (e.g. "Free") -> null.
$envo_blocks_number = static function ( $value ) {
	$value = str_replace( array( ' ', "\xc2\xa0" ), '', (string) $value );
	if ( preg_match( '/^\d+(?:[.,]\d+)?$/', $value ) ) {
		return (float) str_replace( ',', '.', $value );
	}
	return null;
};

$envo_blocks_money = static function ( $amount, $class ) use ( $envo_blocks_currency, $envo_blocks_after ) {
	$symbol = '' !== $envo_blocks_currency ? '<span class="sb-pricing__currency">' . esc_html( $envo_blocks_currency ) . '</span>' : '';
	$value  = '<span class="sb-pricing__amount">' . esc_html( $amount ) . '</span>';

	return '<span class="' . esc_attr( $class ) . '">' . ( $envo_blocks_after ? $value . $symbol : $symbol . $value ) . '</span>';
};

$envo_blocks_cards = '';
$envo_blocks_count = 0;

foreach ( $envo_blocks_plans as $envo_blocks_plan ) {
	if ( ! is_array( $envo_blocks_plan ) ) {
		continue;
	}

	$envo_blocks_name  = Envo_Blocks_Blocks::text( $envo_blocks_plan, 'name' );
	$envo_blocks_price = Envo_Blocks_Blocks::text( $envo_blocks_plan, 'price' );

	if ( '' === $envo_blocks_name && '' === $envo_blocks_price ) {
		continue;
	}

	$envo_blocks_old      = Envo_Blocks_Blocks::text( $envo_blocks_plan, 'oldPrice' );
	$envo_blocks_period   = Envo_Blocks_Blocks::text( $envo_blocks_plan, 'period' );
	$envo_blocks_note     = Envo_Blocks_Blocks::text( $envo_blocks_plan, 'note' );
	$envo_blocks_desc     = Envo_Blocks_Blocks::text( $envo_blocks_plan, 'description' );
	$envo_blocks_badge    = Envo_Blocks_Blocks::text( $envo_blocks_plan, 'badge' );
	$envo_blocks_btn      = Envo_Blocks_Blocks::text( $envo_blocks_plan, 'buttonText' );
	$envo_blocks_url      = isset( $envo_blocks_plan['buttonUrl'] ) && is_string( $envo_blocks_plan['buttonUrl'] ) ? $envo_blocks_plan['buttonUrl'] : '';
	$envo_blocks_featured = ! empty( $envo_blocks_plan['featured'] );
	$envo_blocks_newtab   = ! empty( $envo_blocks_plan['newTab'] );

	// Percentage saved, only when both prices are numbers and the old one is higher.
	$envo_blocks_pct = '';
	$envo_blocks_a   = $envo_blocks_number( $envo_blocks_price );
	$envo_blocks_b   = $envo_blocks_number( $envo_blocks_old );
	if ( $envo_blocks_savings && null !== $envo_blocks_a && null !== $envo_blocks_b && $envo_blocks_b > $envo_blocks_a ) {
		// str_replace, not sprintf: the text can be edited in the block and may contain other "%" characters.
		$envo_blocks_pct = str_replace( '%s', round( ( 1 - $envo_blocks_a / $envo_blocks_b ) * 100 ) . '%', $envo_blocks_save_tpl );
	}

	$envo_blocks_features = '';
	$envo_blocks_lines    = isset( $envo_blocks_plan['features'] ) && is_string( $envo_blocks_plan['features'] ) ? preg_split( '/\r\n|\r|\n/', $envo_blocks_plan['features'] ) : array();
	foreach ( $envo_blocks_lines as $envo_blocks_line ) {
		$envo_blocks_line = trim( sanitize_text_field( $envo_blocks_line ) );
		if ( '' === $envo_blocks_line ) {
			continue;
		}

		$envo_blocks_off = 0 === strpos( $envo_blocks_line, '-' );
		$envo_blocks_txt = $envo_blocks_off ? ltrim( substr( $envo_blocks_line, 1 ) ) : $envo_blocks_line;

		$envo_blocks_features .= '<li class="sb-pricing__feature' . ( $envo_blocks_off ? ' is-off' : '' ) . '">'
			. Envo_Blocks_Blocks::icon( $envo_blocks_off ? 'close' : 'check', 'sb-pricing__mark' )
			. '<span>' . esc_html( $envo_blocks_txt ) . '</span>'
			. ( $envo_blocks_off ? '<span class="screen-reader-text">' . esc_html__( '(not included)', 'envo-blocks' ) . '</span>' : '' )
			. '</li>';
	}

	$envo_blocks_card  = '<div class="sb-pricing__plan' . ( $envo_blocks_featured ? ' is-featured' : '' ) . '">';
	$envo_blocks_card .= '' !== $envo_blocks_badge ? '<span class="sb-pricing__badge">' . esc_html( $envo_blocks_badge ) . '</span>' : '';
	$envo_blocks_card .= '' !== $envo_blocks_name ? '<h3 class="sb-pricing__name">' . esc_html( $envo_blocks_name ) . '</h3>' : '';
	$envo_blocks_card .= '' !== $envo_blocks_desc ? '<p class="sb-pricing__desc">' . esc_html( $envo_blocks_desc ) . '</p>' : '';

	$envo_blocks_card .= '<div class="sb-pricing__price">';
	if ( '' !== $envo_blocks_old ) {
		$envo_blocks_card .= '<del class="sb-pricing__old"><span class="screen-reader-text">' . esc_html__( 'Original price:', 'envo-blocks' ) . ' </span>' . $envo_blocks_money( $envo_blocks_old, 'sb-pricing__money' ) . '</del>';
	}
	if ( '' !== $envo_blocks_price ) {
		$envo_blocks_card .= '<ins class="sb-pricing__now">' . ( '' !== $envo_blocks_old ? '<span class="screen-reader-text">' . esc_html__( 'Current price:', 'envo-blocks' ) . ' </span>' : '' )
			. ( null === $envo_blocks_a ? '<span class="sb-pricing__money"><span class="sb-pricing__amount">' . esc_html( $envo_blocks_price ) . '</span></span>' : $envo_blocks_money( $envo_blocks_price, 'sb-pricing__money' ) )
			. '</ins>';
	}
	$envo_blocks_card .= '' !== $envo_blocks_period ? '<span class="sb-pricing__period">' . esc_html( $envo_blocks_period ) . '</span>' : '';
	$envo_blocks_card .= '</div>';

	if ( '' !== $envo_blocks_pct || '' !== $envo_blocks_note ) {
		$envo_blocks_card .= '<p class="sb-pricing__note">'
			. ( '' !== $envo_blocks_pct ? '<span class="sb-pricing__save">' . esc_html( $envo_blocks_pct ) . '</span>' : '' )
			. ( '' !== $envo_blocks_note ? '<span>' . esc_html( $envo_blocks_note ) . '</span>' : '' )
			. '</p>';
	}

	$envo_blocks_card .= '' !== $envo_blocks_features ? '<ul class="sb-pricing__features">' . $envo_blocks_features . '</ul>' : '';

	if ( '' !== $envo_blocks_btn ) {
		$envo_blocks_card .= '<div class="sb-pricing__action"><a class="sb-pricing__button wp-element-button" href="' . esc_url( '' !== $envo_blocks_url ? $envo_blocks_url : '#' ) . '"'
			. ( $envo_blocks_newtab ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $envo_blocks_btn ) . '</a></div>';
	}

	$envo_blocks_card  .= '</div>';
	$envo_blocks_cards .= $envo_blocks_card;
	++$envo_blocks_count;
}

if ( ! $envo_blocks_count ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Add at least one plan in the block settings.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_columns = min( $envo_blocks_count, Envo_Blocks_Blocks::int( $attributes, 'columns', 3, 1, 5 ) );
$envo_blocks_style   = Envo_Blocks_Blocks::choice( $attributes, 'cardStyle', array( 'border', 'shadow', 'flat' ) );
$envo_blocks_align   = Envo_Blocks_Blocks::choice( $attributes, 'contentAlign', array( 'center', 'left' ) );

$envo_blocks_classes = 'sb-pricing sb-pricing--' . $envo_blocks_style . ' sb-align-' . $envo_blocks_align;
if ( 1 === $envo_blocks_columns ) {
	$envo_blocks_classes .= ' sb-pricing--single';
}
if ( ! isset( $attributes['featuredScale'] ) || $attributes['featuredScale'] ) {
	$envo_blocks_classes .= ' sb-pricing--scale';
}
if ( ! isset( $attributes['buttonFullWidth'] ) || $attributes['buttonFullWidth'] ) {
	$envo_blocks_classes .= ' sb-pricing--full-button';
}

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => $envo_blocks_classes,
		'style' => '--sb-cols:' . $envo_blocks_columns
			. ';--sb-pricing-radius:' . Envo_Blocks_Blocks::int( $attributes, 'radius', 14, 0, 48 ) . 'px'
			. ';--sb-pricing-gap:' . Envo_Blocks_Blocks::int( $attributes, 'gap', 24, 0, 80 ) . 'px'
			. ';--sb-pricing-price:' . Envo_Blocks_Blocks::int( $attributes, 'priceSize', 48, 20, 96 ) . 'px;'
			. Envo_Blocks_Blocks::color_vars(
				$attributes,
				array(
					'accentColor'        => '--sb-pricing-accent',
					'cardBackground'     => '--sb-pricing-bg',
					'textColor'          => '--sb-pricing-text',
					'featuredBackground' => '--sb-pricing-featured-bg',
					'featuredText'       => '--sb-pricing-featured-text',
					'oldPriceColor'      => '--sb-pricing-old',
					'badgeBackground'    => '--sb-pricing-badge-bg',
					'badgeText'          => '--sb-pricing-badge-text',
					'buttonBackground'   => '--sb-pricing-btn-bg',
					'buttonText'         => '--sb-pricing-btn-text',
					'checkColor'         => '--sb-pricing-check',
					'savingsColor'       => '--sb-pricing-save',
					'borderColor'        => '--sb-pricing-border',
				)
			),
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<?php echo wp_kses( $envo_blocks_cards, Envo_Blocks_Blocks::allowed_html() ); ?>
</div>
