<?php
/**
 * Template of an invoice, credit note or packing slip (HTML for mPDF: tables and simple CSS).
 *
 * @package EnvoBlocks
 *
 * @var array $data    Snapshot (Envo_Blocks_Invoice_Data).
 * @var bool  $preview For a browser instead of mPDF.
 */

defined( 'ABSPATH' ) || exit;

$envo_l       = $data['labels'];
$envo_type    = $data['type'];
$envo_packing = 'packing' === $envo_type;
$envo_vat     = $data['vat'] && ! $envo_packing;
$envo_rtl     = 'rtl' === $data['dir'];
// A value without right-to-left letters keeps its own order in a right-to-left document (numbers, dates, Latin names).
$envo_v       = static function ( $text ) use ( $envo_rtl ) {
	$html = esc_html( (string) $text );
	return $envo_rtl && '' !== $html && ! preg_match( '/[\x{0590}-\x{08FF}\x{FB1D}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', (string) $text ) ? '<bdo dir="ltr">' . $html . '</bdo>' : $html;
};
$envo_money   = static function ( $amount ) use ( $data, $envo_v ) {
	return $envo_v( Envo_Blocks_Invoice_Data::money( $amount, $data ) );
};
$envo_plain   = static function ( $amount ) use ( $data, $envo_v ) {
	return $envo_v( Envo_Blocks_Invoice_Data::money( $amount, $data, false ) );
};
$envo_text    = static function ( $text ) use ( $envo_v ) {
	return implode( '<br>', array_map( $envo_v, preg_split( '/\r\n|\r|\n/', (string) $text ) ) );
};
$envo_title   = $envo_packing ? $envo_l['packing_slip'] : ( 'credit' === $envo_type ? $envo_l['credit_note'] : $envo_l['invoice'] );
$envo_accent  = $data['accent'];
$envo_start   = $envo_rtl ? 'right' : 'left';
$envo_end     = $envo_rtl ? 'left' : 'right';

// Logo: JPEG and PNG as they are, other formats (WebP) as PNG – mPDF reads only some formats.
$envo_logo = '';
if ( ! empty( $data['seller']['logo'] ) ) {
	$envo_file = get_attached_file( (int) $data['seller']['logo'] );
	if ( $envo_file && is_file( $envo_file ) ) {
		$envo_ext = strtolower( pathinfo( $envo_file, PATHINFO_EXTENSION ) );
		if ( in_array( $envo_ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {
			$envo_logo = $preview ? (string) wp_get_attachment_url( (int) $data['seller']['logo'] ) : $envo_file;
		} elseif ( 'webp' === $envo_ext && function_exists( 'imagecreatefromwebp' ) ) {
			$envo_img = @imagecreatefromwebp( $envo_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- A broken file only loses the logo.
			if ( $envo_img ) {
				imagesavealpha( $envo_img, true );
				ob_start();
				imagepng( $envo_img );
				$envo_logo = 'data:image/png;base64,' . base64_encode( (string) ob_get_clean() ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Image data.
				imagedestroy( $envo_img );
			}
		}
	}
}

// QR payment code (bank transfer orders that are not paid).
$envo_qr = '';
if ( '' !== $data['qr'] && 'invoice' === $envo_type && class_exists( 'Envo_Blocks_QR' ) ) {
	$envo_code = Envo_Blocks_QR::encode( $data['qr'], 'M' );
	$envo_png  = $envo_code ? $envo_code->png( 5, 2 ) : '';
	$envo_qr   = '' !== $envo_png ? 'data:image/png;base64,' . base64_encode( $envo_png ) : ''; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Image data.
}

// Columns of the items table.
$envo_cols = array( 'item' => $envo_l['item'] );
if ( $data['columns']['sku'] ) {
	$envo_cols['sku'] = $envo_l['sku'];
}
foreach ( $data['fields'] as $envo_field ) {
	$envo_cols[ 'field:' . $envo_field['key'] ] = $envo_field['label'];
}
$envo_cols['qty'] = $envo_l['qty'];
if ( ! $envo_packing ) {
	if ( $data['columns']['unit'] ) {
		$envo_cols['unit'] = $envo_l['unit_price'];
	}
	// The discount column also when a line has a discount (else unit price × quantity would not match the total).
	if ( $data['columns']['discount'] || array_filter( wp_list_pluck( $data['lines'], 'discount' ), static function ( $v ) {
		return abs( (float) $v ) >= 0.005;
	} ) ) {
		$envo_cols['discount'] = $envo_l['discount'];
	}
	if ( $envo_vat ) {
		$envo_cols['net'] = $envo_l['net'];
		if ( $data['columns']['rate'] ) {
			$envo_cols['rate'] = $envo_l['tax_rate'];
		}
		if ( $data['columns']['tax'] ) {
			$envo_cols['tax'] = $envo_l['tax'];
		}
	}
	$envo_cols['total'] = $envo_l['total'];
}
$envo_numeric = array( 'qty', 'unit', 'discount', 'net', 'rate', 'tax', 'total' );
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( $data['lang'] ); ?>" dir="<?php echo esc_attr( $data['dir'] ); ?>">
<head>
<meta charset="utf-8">
<title><?php echo esc_html( $envo_title . ( '' !== $data['number'] ? ' ' . $data['number'] : '' ) ); ?></title>
<style>
	body { font-family: notosans, "Noto Sans", "Segoe UI", Arial, sans-serif; font-size: 9pt; color: #1f2937; line-height: 1.4; }
	table { border-collapse: collapse; width: 100%; }
	td, th { vertical-align: top; text-align: <?php echo esc_html( $envo_start ); ?>; }
	.head td { padding: 0 0 14pt; }
	.logo { max-height: 48pt; max-width: 180pt; }
	.shop { font-size: 13pt; font-weight: bold; }
	.title { text-align: <?php echo esc_html( $envo_end ); ?>; }
	.doc-title { margin: 0; font-size: 18pt; font-weight: bold; color: <?php echo esc_html( $envo_accent ); ?>; }
	.doc-no { font-size: 11pt; font-weight: bold; }
	.parties td { width: 50%; padding: 8pt 10pt; border: 0.5pt solid #e5e7eb; }
	.label { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.5pt; color: #6b7280; margin-bottom: 3pt; }
	.name { font-weight: bold; font-size: 10pt; }
	.ids { margin-top: 4pt; }
	.meta { margin: 10pt 0; }
	.meta td { padding: 2pt 8pt 2pt 0; }
	.k { color: #6b7280; }
	.items th { background: <?php echo esc_html( $envo_accent ); ?>; color: #ffffff; font-weight: bold; padding: 5pt 4pt; font-size: 7.5pt; }
	.items td { padding: 5pt 4pt; border-bottom: 0.5pt solid #e5e7eb; }
	.num { text-align: <?php echo esc_html( $envo_end ); ?>; white-space: nowrap; }
	.sub { color: #6b7280; font-size: 8pt; }
	.sums { margin-top: 10pt; }
	.recap th { color: #6b7280; font-weight: normal; font-size: 8pt; padding: 3pt 6pt; border-bottom: 0.5pt solid #e5e7eb; }
	.recap td { padding: 3pt 6pt; }
	.totals td { padding: 3pt 6pt; }
	.grand { font-size: 12pt; font-weight: bold; border-top: 1pt solid <?php echo esc_html( $envo_accent ); ?>; padding-top: 6pt; }
	.pay { margin-top: 14pt; }
	.pay td { padding: 8pt 10pt; background: #f5f5ff; }
	.qr { width: 100pt; text-align: center; }
	.qr-img { width: 92pt; height: 92pt; }
	.texts { margin-top: 12pt; }
	.texts p { margin: 0 0 4pt; }
	.warning { color: #92400e; font-size: 8pt; }
	.foot { font-size: 7.5pt; color: #6b7280; text-align: center; border-top: 0.5pt solid #e5e7eb; padding-top: 4pt; }
	<?php if ( $preview ) : ?>
	html { background: #e5e7eb; }
	body { width: <?php echo 'Letter' === $data['paper'] ? '216mm' : '210mm'; ?>; min-height: 297mm; margin: 20px auto; padding: 15mm; box-sizing: border-box; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
	.preview-note { width: 210mm; margin: 12px auto -8px; font: 13px/1.5 sans-serif; color: #92400e; }
	<?php endif; ?>
</style>
</head>
<body>
<?php if ( ! $preview ) : ?>
<htmlpagefooter name="envofoot"><div class="foot"><?php echo $envo_text( $data['seller']['footer'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?><?php echo '' !== trim( (string) $data['seller']['footer'] ) ? '<br>' : ''; ?><?php echo $envo_v( $envo_l['page'] ); ?> {PAGENO} / {nbpg}</div></htmlpagefooter>
<sethtmlpagefooter name="envofoot" value="on" />
<?php endif; ?>

<table class="head"><tr>
	<td>
		<?php if ( '' !== $envo_logo ) : ?>
			<img class="logo" src="<?php echo esc_attr( $envo_logo ); ?>" alt="">
		<?php else : ?>
			<div class="shop"><?php echo $envo_v( $data['seller']['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<?php endif; ?>
	</td>
	<td class="title">
		<div class="doc-title"><?php echo $envo_v( $envo_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<?php if ( '' !== $data['number'] ) : ?>
			<div class="doc-no"><?php echo $envo_v( $envo_l['number'] . ': ' . $data['number'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<?php endif; ?>
	</td>
</tr></table>

<table class="parties"><tr>
	<td>
		<div class="label"><?php echo $envo_v( $envo_l['seller'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<div class="name"><?php echo $envo_v( $data['seller']['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<div><?php echo $envo_text( $data['seller']['address'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></div>
		<?php if ( $data['seller']['ids'] ) : ?>
			<div class="ids">
				<?php foreach ( $data['seller']['ids'] as $envo_id ) : ?>
					<?php if ( '' !== $envo_id['value'] ) : ?>
						<div><?php echo $envo_v( $envo_id['label'] . ': ' . $envo_id['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</td>
	<td>
		<div class="label"><?php echo $envo_v( $envo_l['buyer'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<?php if ( '' !== $data['buyer']['company'] ) : ?>
			<div class="name"><?php echo $envo_v( $data['buyer']['company'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
			<div><?php echo $envo_v( $data['buyer']['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<?php else : ?>
			<div class="name"><?php echo $envo_v( $data['buyer']['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<?php endif; ?>
		<div><?php echo $envo_text( $data['buyer']['address'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></div>
		<?php if ( $data['buyer']['ids'] ) : ?>
			<div class="ids">
				<?php foreach ( $data['buyer']['ids'] as $envo_id ) : ?>
					<div><?php echo $envo_v( $envo_id['label'] . ': ' . $envo_id['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( '' !== $data['buyer']['shipping'] ) : ?>
			<div class="label" style="margin-top:8pt;"><?php echo $envo_v( $envo_l['ship_to'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
			<div><?php echo $envo_text( $data['buyer']['shipping'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></div>
		<?php endif; ?>
	</td>
</tr></table>

<?php
$envo_left  = array();
$envo_right = array();
if ( ! $envo_packing ) {
	$envo_left[] = array( $envo_l['issue_date'], Envo_Blocks_Invoice_Data::date( $data['issued'], $data ) );
	if ( 'invoice' === $envo_type && $data['vat'] ) {
		$envo_left[] = array( $envo_l['supply_date'], Envo_Blocks_Invoice_Data::date( $data['supply'], $data ) );
	}
	if ( $data['due'] ) {
		$envo_left[] = array( $envo_l['due_date'], Envo_Blocks_Invoice_Data::date( $data['due'], $data ) );
	}
}
if ( 'credit' === $envo_type && $data['original'] ) {
	$envo_left[] = array( $envo_l['original_invoice'], $data['original']['number'] . ' (' . Envo_Blocks_Invoice_Data::date( $data['original']['issued'], $data ) . ')' );
	if ( ! empty( $data['refund']['reason'] ) ) {
		$envo_left[] = array( $envo_l['reason'], $data['refund']['reason'] );
	}
}
$envo_right[] = array( $envo_l['order'], $data['order']['number'] );
$envo_right[] = array( $envo_l['order_date'], Envo_Blocks_Invoice_Data::date( $data['order']['date'], $data ) );
if ( ! $envo_packing && '' !== $data['order']['payment'] ) {
	$envo_right[] = array( $envo_l['payment_method'], $data['order']['payment'] );
}
if ( ! $envo_packing && '' !== $data['reference'] && ( $data['due'] || 'invoice' !== $envo_type ) ) {
	$envo_right[] = array( $envo_l['reference'], $data['reference'] );
}
?>
<table class="meta">
	<?php for ( $envo_i = 0, $envo_n = max( count( $envo_left ), count( $envo_right ) ); $envo_i < $envo_n; $envo_i++ ) : ?>
		<tr>
			<td class="k" style="width:17%;"><?php echo $envo_v( isset( $envo_left[ $envo_i ] ) ? $envo_left[ $envo_i ][0] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
			<td style="width:33%;"><?php echo $envo_v( isset( $envo_left[ $envo_i ] ) ? $envo_left[ $envo_i ][1] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
			<td class="k" style="width:17%;"><?php echo $envo_v( isset( $envo_right[ $envo_i ] ) ? $envo_right[ $envo_i ][0] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
			<td style="width:33%;"><?php echo $envo_v( isset( $envo_right[ $envo_i ] ) ? $envo_right[ $envo_i ][1] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
		</tr>
	<?php endfor; ?>
</table>

<table class="items">
	<thead><tr>
		<?php foreach ( $envo_cols as $envo_key => $envo_label ) : ?>
			<th class="<?php echo in_array( $envo_key, $envo_numeric, true ) ? 'num' : ( 'item' === $envo_key ? 'col-item' : '' ); ?>"><?php echo $envo_v( $envo_label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></th>
		<?php endforeach; ?>
	</tr></thead>
	<tbody>
		<?php foreach ( $data['lines'] as $envo_line ) : ?>
			<?php
			if ( $envo_packing && 'item' !== $envo_line['kind'] ) {
				continue;
			}
			?>
			<tr>
				<?php foreach ( array_keys( $envo_cols ) as $envo_key ) : ?>
					<?php if ( 'item' === $envo_key ) : ?>
						<td><?php echo $envo_v( ( 'shipping' === $envo_line['kind'] ? $envo_l['shipping'] . ': ' . $envo_line['name'] : $envo_line['name'] ) . ( ! empty( $envo_line['reduced'] ) ? ' ※' : '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?>
							<?php if ( '' !== $envo_line['meta'] ) : ?>
								<div class="sub"><?php echo $envo_v( $envo_line['meta'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
							<?php endif; ?>
						</td>
					<?php elseif ( 0 === strpos( $envo_key, 'field:' ) ) : ?>
						<td><?php echo $envo_v( isset( $envo_line['fields'][ substr( $envo_key, 6 ) ] ) ? $envo_line['fields'][ substr( $envo_key, 6 ) ] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
					<?php elseif ( 'sku' === $envo_key ) : ?>
						<td><?php echo $envo_v( $envo_line['sku'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
					<?php elseif ( 'qty' === $envo_key ) : ?>
						<td class="num"><?php echo $envo_v( Envo_Blocks_Invoice_Data::qty( $envo_line['qty'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
					<?php elseif ( 'unit' === $envo_key ) : ?>
						<td class="num"><?php echo $envo_plain( $envo_line['unit'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td>
					<?php elseif ( 'discount' === $envo_key ) : ?>
						<td class="num"><?php echo abs( $envo_line['discount'] ) >= 0.005 ? $envo_plain( -1 * $envo_line['discount'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td>
					<?php elseif ( 'net' === $envo_key ) : ?>
						<td class="num"><?php echo $envo_plain( $envo_line['net'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td>
					<?php elseif ( 'rate' === $envo_key ) : ?>
						<td class="num"><?php echo $envo_v( Envo_Blocks_Invoice_Data::rate( $envo_line, $data ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
					<?php elseif ( 'tax' === $envo_key ) : ?>
						<td class="num"><?php echo $envo_plain( $envo_line['tax'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td>
					<?php elseif ( 'total' === $envo_key ) : ?>
						<td class="num"><?php echo $envo_plain( $envo_line['gross'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if ( ! $envo_packing ) : ?>
<table class="sums"><tr>
	<td style="width:55%;">
		<?php if ( $envo_vat && $data['recap']['rows'] ) : ?>
			<table class="recap">
				<tr><th><?php echo $envo_v( $envo_l['tax_rate'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></th><th class="num"><?php echo $envo_v( $envo_l['tax_base'] ); ?></th><th class="num"><?php echo $envo_v( $envo_l['tax'] ); ?></th></tr>
				<?php foreach ( $data['recap']['rows'] as $envo_row ) : ?>
					<tr>
						<td><?php echo $envo_v( trim( Envo_Blocks_Invoice_Data::percent( $envo_row['percent'] ) . ( '' !== $envo_row['label'] ? ' ' . $envo_row['label'] : '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></td>
						<td class="num"><?php echo $envo_plain( $envo_row['base'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td>
						<td class="num"><?php echo $envo_plain( $envo_row['tax'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td>
					</tr>
				<?php endforeach; ?>
				<?php if ( abs( $data['recap']['rounding'] ) >= 0.005 ) : ?>
					<tr><td colspan="2"><?php echo $envo_v( $envo_l['rounding'] ); ?></td><td class="num"><?php echo $envo_plain( -1 * $data['recap']['rounding'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td></tr>
				<?php endif; ?>
			</table>
		<?php endif; ?>
	</td>
	<td style="width:45%;">
		<table class="totals">
			<?php if ( $envo_vat ) : ?>
				<tr><td><?php echo $envo_v( $envo_l['subtotal_net'] ); ?></td><td class="num"><?php echo $envo_money( $data['totals']['net'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td></tr>
				<tr><td><?php echo $envo_v( $envo_l['total_tax'] ); ?></td><td class="num"><?php echo $envo_money( $data['totals']['tax'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td></tr>
			<?php endif; ?>
			<tr><td class="grand"><?php echo $envo_v( $data['totals']['due'] && 'invoice' === $envo_type ? $envo_l['amount_due'] : $envo_l['total_due'] ); ?></td><td class="num grand"><?php echo $envo_money( $data['totals']['total'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td></tr>
			<?php if ( $data['totals']['paid'] ) : ?>
				<tr><td><?php echo $envo_v( $envo_l['paid'] ); ?></td><td class="num"><?php echo $envo_money( $data['totals']['paid'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></td></tr>
			<?php endif; ?>
		</table>
		<?php if ( '' !== $data['totals']['words'] ) : ?>
			<p><span class="label"><?php echo $envo_v( $envo_l['amount_words'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?>:</span> <?php echo $envo_v( $data['totals']['words'] ); ?></p>
		<?php endif; ?>
	</td>
</tr></table>

	<?php if ( 'invoice' === $envo_type && ( $data['totals']['paid'] || '' !== $data['bank'] || '' !== $envo_qr ) ) : ?>
<table class="pay"><tr>
		<?php if ( $data['totals']['paid'] ) : ?>
	<td><strong><?php echo $envo_v( $envo_l['paid_note'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></strong></td>
		<?php else : ?>
	<td>
			<?php if ( '' !== $data['bank'] ) : ?>
		<div class="label"><?php echo $envo_v( $envo_l['bank'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
		<div><?php echo $envo_text( $data['bank'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></div>
			<?php endif; ?>
			<?php if ( '' !== $data['reference'] ) : ?>
		<div><?php echo $envo_v( $envo_l['reference'] . ': ' . $data['reference'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
			<?php endif; ?>
			<?php if ( '' !== $envo_qr ) : ?>
		<div style="margin-top:6pt;"><?php echo $envo_v( $envo_l['scan_to_pay'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></div>
			<?php endif; ?>
	</td>
			<?php if ( '' !== $envo_qr ) : ?>
	<td class="qr"><img class="qr-img" src="<?php echo esc_attr( $envo_qr ); ?>" alt=""></td>
			<?php endif; ?>
		<?php endif; ?>
</tr></table>
	<?php endif; ?>
<?php endif; ?>

<div class="texts">
	<?php foreach ( $data['texts'] as $envo_line_text ) : ?>
		<p><strong><?php echo $envo_v( $envo_line_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?></strong></p>
	<?php endforeach; ?>
	<?php if ( ! $envo_packing && '' !== trim( (string) $data['terms'] ) ) : ?>
		<p><?php echo $envo_text( $data['terms'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></p>
	<?php endif; ?>
	<?php if ( '' !== trim( (string) $data['note'] ) ) : ?>
		<p><?php echo $envo_text( $data['note'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></p>
	<?php endif; ?>
	<?php if ( '' !== trim( (string) $data['order']['note'] ) ) : ?>
		<p><span class="label"><?php echo $envo_v( $envo_l['note'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $envo_v. ?>:</span> <?php echo $envo_v( $data['order']['note'] ); ?></p>
	<?php endif; ?>
</div>

<?php if ( $preview ) : ?>
	<div class="foot" style="margin-top:24pt;"><?php echo $envo_text( $data['seller']['footer'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?></div>
<?php endif; ?>
</body>
</html>
