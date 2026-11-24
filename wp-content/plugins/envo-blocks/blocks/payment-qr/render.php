<?php
/**
 * Payment QR Code: for the order confirmation of an order paid by direct bank transfer (WooCommerce bacs, on hold
 * or pending) a QR code for the banking app – QR Platba (CZK) or EPC QR (EUR) – with the details for entering
 * the payment by hand. Nothing for other orders, currencies or pages; a sample order in the editor.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_args = array(
	'format'  => Envo_Blocks_Blocks::choice( $attributes, 'format', array( '', 'auto', 'spd', 'epc' ) ),
	'account' => isset( $attributes['account'] ) ? sanitize_key( $attributes['account'] ) : '',
	'message' => Envo_Blocks_Blocks::text( $attributes, 'message' ),
);
$envo_blocks_note = '';

if ( Envo_Blocks_Blocks::is_preview() ) {
	$envo_blocks_payload = Envo_Blocks_Payment_QR::sample( $envo_blocks_args );
	$envo_blocks_note    = Envo_Blocks_Payment_QR::accounts()
		? __( 'Preview with a sample order. The code shows on the order confirmation of orders paid by direct bank transfer in CZK or EUR.', 'envo-blocks' )
		: __( 'Preview with a sample order and account. Add the bank account with its IBAN in WooCommerce > Settings > Payments > Direct bank transfer.', 'envo-blocks' );
} elseif ( apply_filters( 'envo_blocks_payment_qr_sample', false ) ) {
	// Demo pages (filter "envo_blocks_payment_qr_sample"): the sample order, without the editor note.
	$envo_blocks_payload = Envo_Blocks_Payment_QR::sample( $envo_blocks_args );
} else {
	$envo_blocks_order   = Envo_Blocks_Payment_QR::current_order();
	$envo_blocks_payload = $envo_blocks_order ? Envo_Blocks_Payment_QR::for_order( $envo_blocks_order, $envo_blocks_args ) : null;
}

$envo_blocks_qr = $envo_blocks_payload ? Envo_Blocks_Payment_QR::qr( $envo_blocks_payload ) : null;
if ( ! $envo_blocks_qr ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'No QR code for this currency or format: QR Platba needs CZK, EPC QR needs EUR.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_title   = Envo_Blocks_Blocks::text( $attributes, 'title', __( 'Pay by QR code', 'envo-blocks' ) );
$envo_blocks_text    = Envo_Blocks_Blocks::text( $attributes, 'description', __( 'Scan the code with the app of your bank: the account, amount and reference are filled in.', 'envo-blocks' ) );
$envo_blocks_style   = Envo_Blocks_Blocks::choice( $attributes, 'boxStyle', array( 'box', 'plain' ) );
$envo_blocks_layout  = Envo_Blocks_Blocks::choice( $attributes, 'layout', array( 'side', 'stack' ) );
$envo_blocks_details = ! isset( $attributes['showDetails'] ) || $attributes['showDetails'];
$envo_blocks_label   = 'spd' === $envo_blocks_payload['format'] ? __( 'QR Platba', 'envo-blocks' ) : __( 'EPC QR (SEPA)', 'envo-blocks' );
$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-payqr sb-payqr--' . $envo_blocks_style . ' sb-payqr--' . $envo_blocks_layout,
		'style' => Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'background' => '--sb-payqr-bg',
				'textColor'  => '--sb-payqr-text',
			)
		) . '--sb-payqr-size:' . Envo_Blocks_Blocks::int( $attributes, 'qrSize', 200, 120, 360 ) . 'px;',
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<?php if ( '' !== $envo_blocks_note ) : ?>
		<p class="sb-notice"><?php echo esc_html( $envo_blocks_note ); ?></p>
	<?php endif; ?>
	<div class="sb-payqr__inner">
		<figure class="sb-payqr__code">
			<?php echo wp_kses( $envo_blocks_qr->svg( 4, __( 'Payment QR code', 'envo-blocks' ) ), Envo_Blocks_Blocks::allowed_html() ); ?>
			<figcaption class="sb-payqr__format"><?php echo esc_html( $envo_blocks_label ); ?></figcaption>
		</figure>
		<div class="sb-payqr__body">
			<?php if ( '' !== $envo_blocks_title ) : ?>
				<h2 class="sb-payqr__title"><?php echo esc_html( $envo_blocks_title ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $envo_blocks_text ) : ?>
				<p class="sb-payqr__text"><?php echo esc_html( $envo_blocks_text ); ?></p>
			<?php endif; ?>
			<?php if ( $envo_blocks_details ) : ?>
				<dl class="sb-payqr__details">
					<?php foreach ( Envo_Blocks_Payment_QR::details( $envo_blocks_payload ) as $envo_blocks_row ) : ?>
						<div><dt><?php echo esc_html( $envo_blocks_row[0] ); ?></dt><dd><?php echo esc_html( $envo_blocks_row[1] ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</div>
	</div>
</div>
