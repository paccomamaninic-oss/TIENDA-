<?php
/**
 * Invoice Download: a button with the PDF invoice (and credit notes) of the order of the order confirmation page,
 * when the invoice is issued and the visitor may see the order. A sample button in the editor.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_links = array();
$envo_blocks_text  = Envo_Blocks_Blocks::text( $attributes, 'text' );

if ( Envo_Blocks_Blocks::is_preview() ) {
	$envo_blocks_links[] = array( '#', '' !== $envo_blocks_text ? $envo_blocks_text : __( 'Download invoice (PDF)', 'envo-blocks' ) );
	$envo_blocks_note    = Envo_Blocks_Invoice_Settings::get()['enabled'] ? '' : __( 'Invoices are off: turn them on in Envo Blocks > Invoices.', 'envo-blocks' );
} else {
	$envo_blocks_note  = '';
	$envo_blocks_order = class_exists( 'Envo_Blocks_Payment_QR' ) ? Envo_Blocks_Payment_QR::current_order() : null;
	$envo_blocks_inv   = $envo_blocks_order ? Envo_Blocks_Invoices::invoice( $envo_blocks_order ) : null;
	if ( $envo_blocks_inv ) {
		$envo_blocks_guest   = ! $envo_blocks_order->get_user_id();
		$envo_blocks_links[] = array(
			Envo_Blocks_Invoices::url( $envo_blocks_order, 'invoice', $envo_blocks_guest ),
			'' !== $envo_blocks_text ? $envo_blocks_text : $envo_blocks_inv['labels']['invoice'] . ' ' . $envo_blocks_inv['number'] . ' (PDF)',
		);
		if ( ! isset( $attributes['showCredits'] ) || $attributes['showCredits'] ) {
			foreach ( Envo_Blocks_Invoices::credits( $envo_blocks_order ) as $envo_blocks_i => $envo_blocks_credit ) {
				$envo_blocks_links[] = array( Envo_Blocks_Invoices::url( $envo_blocks_order, 'credit-' . $envo_blocks_i, $envo_blocks_guest ), $envo_blocks_credit['labels']['credit_note'] . ' ' . $envo_blocks_credit['number'] . ' (PDF)' );
			}
		}
	}
}

if ( ! $envo_blocks_links ) {
	return;
}
$envo_blocks_align   = Envo_Blocks_Blocks::choice( $attributes, 'textAlign', array( 'left', 'center', 'right' ) );
$envo_blocks_wrapper = get_block_wrapper_attributes( array( 'class' => 'sb-invoice-download sb-align-' . $envo_blocks_align ) );
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<?php if ( '' !== $envo_blocks_note ) : ?>
		<p class="sb-notice"><?php echo esc_html( $envo_blocks_note ); ?></p>
	<?php endif; ?>
	<?php foreach ( $envo_blocks_links as $envo_blocks_link ) : ?>
		<a class="sb-invoice-download__button wp-element-button" href="<?php echo esc_url( $envo_blocks_link[0] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $envo_blocks_link[1] ); ?></a>
	<?php endforeach; ?>
</div>
