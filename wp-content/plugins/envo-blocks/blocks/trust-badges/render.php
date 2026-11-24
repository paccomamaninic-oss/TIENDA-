<?php
/**
 * Trust Badges: payment method marks and short guarantees.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_heading  = Envo_Blocks_Blocks::text( $attributes, 'heading' );
$envo_blocks_align    = Envo_Blocks_Blocks::choice( $attributes, 'contentAlign', array( 'center', 'left', 'right' ) );
$envo_blocks_style    = Envo_Blocks_Blocks::choice( $attributes, 'payStyle', array( 'color', 'mono' ) );
$envo_blocks_pay_size = Envo_Blocks_Blocks::int( $attributes, 'paySize', 44, 28, 96 );

$envo_blocks_payments = '';
if ( ! empty( $attributes['showPayments'] ) && ! empty( $attributes['payments'] ) && is_array( $attributes['payments'] ) ) {
	foreach ( array_unique( $attributes['payments'] ) as $envo_blocks_name ) {
		$envo_blocks_svg = is_string( $envo_blocks_name ) ? Envo_Blocks_Blocks::payment( $envo_blocks_name ) : '';
		if ( $envo_blocks_svg ) {
			$envo_blocks_payments .= '<li class="sb-trust__pay">' . $envo_blocks_svg . '</li>';
		}
	}
}

$envo_blocks_badges = '';
$envo_blocks_list   = isset( $attributes['badges'] ) && is_array( $attributes['badges'] ) ? $attributes['badges'] : Envo_Blocks_Blocks::default_badges();
if ( ! empty( $attributes['showBadges'] ) ) {
	foreach ( $envo_blocks_list as $envo_blocks_badge ) {
		if ( ! is_array( $envo_blocks_badge ) ) {
			continue;
		}

		$envo_blocks_text = Envo_Blocks_Blocks::text( $envo_blocks_badge, 'text' );
		$envo_blocks_icon = isset( $envo_blocks_badge['icon'] ) && is_string( $envo_blocks_badge['icon'] ) ? Envo_Blocks_Blocks::icon( $envo_blocks_badge['icon'], 'sb-trust__icon' ) : '';

		if ( '' === $envo_blocks_text && '' === $envo_blocks_icon ) {
			continue;
		}

		$envo_blocks_badges .= '<li class="sb-trust__badge">' . $envo_blocks_icon . ( '' !== $envo_blocks_text ? '<span>' . esc_html( $envo_blocks_text ) . '</span>' : '' ) . '</li>';
	}
}

if ( '' === $envo_blocks_payments && '' === $envo_blocks_badges ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'Choose payment methods or add a guarantee in the block settings.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_vars = Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'iconColor'   => '--sb-trust-icon',
		'textColor'   => '--sb-trust-text',
		'background'  => '--sb-trust-bg',
		'borderColor' => '--sb-trust-border',
	)
);

$envo_blocks_classes = 'sb-trust sb-align-' . $envo_blocks_align . ' sb-trust--' . $envo_blocks_style;
if ( ! empty( $attributes['background'] ) || ! empty( $attributes['borderColor'] ) ) {
	$envo_blocks_classes .= ' sb-trust--boxed';
}

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => $envo_blocks_classes,
		'style' => $envo_blocks_vars . '--sb-pay-w:' . $envo_blocks_pay_size . 'px;',
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<?php if ( '' !== $envo_blocks_heading ) : ?>
		<p class="sb-trust__heading"><?php echo esc_html( $envo_blocks_heading ); ?></p>
	<?php endif; ?>
	<?php if ( '' !== $envo_blocks_payments ) : ?>
		<ul class="sb-trust__pays" aria-label="<?php esc_attr_e( 'Accepted payment methods', 'envo-blocks' ); ?>"><?php echo wp_kses( $envo_blocks_payments, Envo_Blocks_Blocks::allowed_html() ); ?></ul>
	<?php endif; ?>
	<?php if ( '' !== $envo_blocks_badges ) : ?>
		<ul class="sb-trust__badges"><?php echo wp_kses( $envo_blocks_badges, Envo_Blocks_Blocks::allowed_html() ); ?></ul>
	<?php endif; ?>
</div>
