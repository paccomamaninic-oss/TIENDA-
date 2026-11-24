<?php
/**
 * Snapshot of an invoice or credit note: everything the document shows, copied from the order and the settings
 * when it is issued. The snapshot never changes afterwards; the PDF is made from it.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Invoice data.
 */
final class Envo_Blocks_Invoice_Data {

	const VERSION = 1;

	/**
	 * Snapshot of an order (invoice, packing slip) or of a refund (credit note), without the number.
	 *
	 * @param WC_Order             $order  Order.
	 * @param string               $type   invoice|credit|packing.
	 * @param WC_Order_Refund|null $refund Refund of a credit note (null = the whole order is cancelled).
	 * @param array|null           $settings Settings (default: the saved ones).
	 * @return array
	 */
	public static function build( WC_Order $order, $type = 'invoice', $refund = null, $settings = null ) {
		$s      = is_array( $settings ) ? $settings : Envo_Blocks_Invoice_Settings::get();
		$preset = Envo_Blocks_Invoice_Presets::get( $s['preset'] );
		$now    = time();
		$credit = 'credit' === $type;
		$sign   = $credit ? -1 : 1;

		$lines = self::lines( $credit && $refund ? $refund : $order, $s, $credit && $refund ? 1 : $sign );
		if ( $credit && $refund && ! $lines ) {
			// A refund of an amount without items: one line with the amount (the tax split is not known).
			$lines[] = self::line( 'fee', $refund->get_reason() ? $refund->get_reason() : $s['labels']['credit_note'], '', '', array(), 1, -1 * (float) $refund->get_amount(), 0, 0, array() );
		}
		$taxes = self::tax_rates( $order );
		// Reduced rates (Japan: ※ at the items and a note): lines taxed only below the highest rate of the order.
		$texts_reduced = '';
		$top           = $taxes ? max( wp_list_pluck( $taxes, 'percent' ) ) : 0;
		if ( '' !== $s['texts']['reduced'] && count( array_unique( wp_list_pluck( $taxes, 'percent' ) ) ) > 1 ) {
			foreach ( $lines as &$line ) {
				$rates = array_intersect_key( $taxes, $line['taxes'] );
				if ( $rates && max( wp_list_pluck( $rates, 'percent' ) ) < $top ) {
					$line['reduced'] = true;
					$texts_reduced   = $s['texts']['reduced'];
				}
			}
			unset( $line );
		}
		$recap = self::recap( $lines, $taxes, $s, $order->get_currency() );

		$net   = array_sum( wp_list_pluck( $lines, 'net' ) );
		$tax   = array_sum( wp_list_pluck( $lines, 'tax' ) );
		$total = $credit && $refund ? -1 * (float) $refund->get_amount() : $sign * (float) $order->get_total();
		$paid  = ! $credit && $order->is_paid();

		$created = $order->get_date_created();
		$paid_at = $order->get_date_paid();
		$supply  = 'order' === $s['supply'] && $created ? $created->getTimestamp() : ( 'paid' === $s['supply'] && $paid_at ? $paid_at->getTimestamp() : $now );
		$buyer   = self::buyer( $order, $s );
		$base    = function_exists( 'wc_get_base_location' ) ? wc_get_base_location()['country'] : '';
		$country = $order->get_billing_country();
		$texts   = array();

		if ( '' !== $texts_reduced ) {
			$texts[] = $texts_reduced;
		}
		if ( ! $s['seller']['vat'] && '' !== $s['texts']['non_vat'] ) {
			$texts[] = $s['texts']['non_vat'];
		}
		$has_tax_id = (bool) array_filter( wp_list_pluck( $buyer['ids'], 'value' ), 'strlen' );
		if ( $s['seller']['vat'] && $has_tax_id && abs( $tax ) < 0.005 && '' !== $s['texts']['reverse_charge'] && $country && $country !== $base
			&& in_array( $country, Envo_Blocks_Invoice_Presets::eu_countries(), true ) && in_array( $base, Envo_Blocks_Invoice_Presets::eu_countries(), true ) ) {
			$texts[] = $s['texts']['reverse_charge'];
		}

		$qr = '';
		if ( 'invoice' === $type && ! $paid && $s['qr'] && class_exists( 'Envo_Blocks_Payment_QR' ) ) {
			$payload = Envo_Blocks_Payment_QR::for_order( $order );
			$qr      = $payload ? $payload['text'] : '';
		}

		$data = array(
			'v'        => self::VERSION,
			'type'     => $type,
			'number'   => '',
			'sequence' => 0,
			'issued'   => $now,
			'tz'       => wp_timezone_string(),
			'supply'   => $supply,
			'due'      => $paid || $credit ? 0 : $now + DAY_IN_SECONDS * (int) $s['due_days'],
			'order'    => array(
				'id'      => $order->get_id(),
				'number'  => (string) $order->get_order_number(),
				'date'    => $created ? $created->getTimestamp() : $now,
				'payment' => $order->get_payment_method_title(),
				'note'    => $order->get_customer_note(),
			),
			'refund'   => $refund ? array(
				'id'     => $refund->get_id(),
				'reason' => $refund->get_reason(),
			) : null,
			'original' => null,
			'currency' => $order->get_currency(),
			'preset'   => $s['preset'],
			'lang'     => $s['lang'],
			'dir'      => self::direction( $s ),
			'paper'    => $s['paper'],
			'date'     => $s['date'],
			'grouping' => $s['grouping'],
			'accent'   => $s['accent'],
			'labels'   => $s['labels'],
			'columns'  => $s['columns'],
			'fields'   => array_values( array_filter( $s['fields'], static function ( $f ) {
				return $f['column'];
			} ) ),
			'seller'   => $s['seller'],
			'buyer'    => $buyer,
			'lines'    => $lines,
			'rates'    => array_map(
				static function ( $rate ) {
					return $rate['percent'];
				},
				$taxes
			),
			'recap'    => $recap,
			'totals'   => array(
				'net'   => round( $net, 2 ),
				'tax'   => round( $tax, 2 ),
				'total' => round( $total, 2 ),
				'paid'  => $paid ? round( $total, 2 ) : 0,
				'due'   => $paid ? 0 : round( $total, 2 ),
				'words' => $s['words'] ? self::words( abs( $total ), $order->get_currency(), $s['grouping'] ) : '',
			),
			'vat'      => $s['seller']['vat'],
			'texts'    => $texts,
			'note'     => $s['note'],
			'terms'    => 'invoice' === $type ? $s['terms'] : '',
			'warning'  => $preset['warning'],
			'qr'       => $qr,
			'bank'     => $paid ? '' : $s['seller']['bank'],
			'reference' => '',
		);
		/**
		 * Filters the snapshot of a document before it is stored (PRO adds bilingual labels, conditions …).
		 *
		 * @param array    $data  Snapshot.
		 * @param WC_Order $order Order.
		 */
		return apply_filters( 'envo_blocks_invoice_data', $data, $order );
	}

	/**
	 * Lines of an order or refund: items, shipping, fees.
	 *
	 * @param WC_Order|WC_Order_Refund $order Order or refund.
	 * @param array                    $s     Settings.
	 * @param int                      $sign  1, or -1 for a credit note of the whole order.
	 * @return array
	 */
	private static function lines( $order, array $s, $sign ) {
		$lines = array();
		foreach ( $order->get_items( 'line_item' ) as $item ) {
			$product = $item->get_product();
			$qty     = (float) $item->get_quantity();
			$subtot  = (float) $item->get_subtotal();
			$total   = (float) $item->get_total();
			$fields  = array();
			foreach ( $s['fields'] as $field ) {
				$fields[ $field['key'] ] = $product ? self::product_field( $product, $field['key'] ) : '';
			}
			$lines[] = self::line(
				'item',
				$item->get_name(),
				$product ? $product->get_sku() : '',
				self::item_meta( $item ),
				$fields,
				$sign * $qty,
				$sign * $total,
				$qty ? abs( $subtot ) / abs( $qty ) : 0,
				$sign * ( $subtot - $total ),
				self::taxes_of( $item, $sign )
			);
		}
		foreach ( $order->get_items( 'shipping' ) as $item ) {
			$lines[] = self::line( 'shipping', $item->get_name(), '', '', array(), $sign * 1, $sign * (float) $item->get_total(), abs( (float) $item->get_total() ), 0, self::taxes_of( $item, $sign ) );
		}
		foreach ( $order->get_items( 'fee' ) as $item ) {
			$lines[] = self::line( 'fee', $item->get_name(), '', '', array(), $sign * 1, $sign * (float) $item->get_total(), abs( (float) $item->get_total() ), 0, self::taxes_of( $item, $sign ) );
		}
		return $lines;
	}

	/**
	 * One line.
	 *
	 * @param string $kind     item|shipping|fee.
	 * @param string $name     Name.
	 * @param string $sku      SKU.
	 * @param string $meta     Variation and item meta (plain text).
	 * @param array  $fields   Product fields.
	 * @param float  $qty      Quantity.
	 * @param float  $net      Net total after discount.
	 * @param float  $unit     Net unit price before discount.
	 * @param float  $discount Net discount.
	 * @param array  $taxes    rate id => tax.
	 * @return array
	 */
	private static function line( $kind, $name, $sku, $meta, array $fields, $qty, $net, $unit, $discount, array $taxes ) {
		$tax = array_sum( $taxes );
		return array(
			'kind'     => $kind,
			'name'     => wp_strip_all_tags( (string) $name ),
			'sku'      => (string) $sku,
			'meta'     => $meta,
			'fields'   => $fields,
			'qty'      => (float) $qty,
			'unit'     => round( (float) $unit, 4 ),
			'discount' => round( (float) $discount, 2 ),
			'net'      => round( (float) $net, 2 ),
			'taxes'    => array_map(
				static function ( $v ) {
					return round( (float) $v, 2 );
				},
				$taxes
			),
			'tax'      => round( (float) $tax, 2 ),
			'gross'    => round( (float) $net + (float) $tax, 2 ),
		);
	}

	/**
	 * Taxes of an order item: rate id => amount.
	 *
	 * @param WC_Order_Item $item Item.
	 * @param int           $sign Sign.
	 * @return array
	 */
	private static function taxes_of( $item, $sign ) {
		$taxes = $item->get_taxes();
		$out   = array();
		foreach ( isset( $taxes['total'] ) ? (array) $taxes['total'] : array() as $rate => $amount ) {
			if ( '' !== $amount && null !== $amount ) {
				$out[ (int) $rate ] = $sign * (float) $amount;
			}
		}
		return $out;
	}

	/**
	 * Variation and item meta as plain text ("Color: Red, Size: M").
	 *
	 * @param WC_Order_Item_Product $item Item.
	 * @return string
	 */
	private static function item_meta( $item ) {
		$parts = array();
		foreach ( $item->get_all_formatted_meta_data() as $meta ) {
			$parts[] = wp_strip_all_tags( $meta->display_key ) . ': ' . trim( wp_strip_all_tags( $meta->display_value ) );
		}
		return implode( ', ', $parts );
	}

	/**
	 * A product field of the invoice (variation first, then its parent).
	 *
	 * @param WC_Product $product Product.
	 * @param string     $key     Field key.
	 * @return string
	 */
	public static function product_field( $product, $key ) {
		$value = (string) $product->get_meta( '_envo_blocks_inv_' . $key );
		if ( '' === $value && $product->get_parent_id() ) {
			$parent = wc_get_product( $product->get_parent_id() );
			$value  = $parent ? (string) $parent->get_meta( '_envo_blocks_inv_' . $key ) : '';
		}
		return $value;
	}

	/**
	 * Tax rates of an order: rate id => array( label, percent, compound ).
	 *
	 * @param WC_Order $order Order.
	 * @return array
	 */
	private static function tax_rates( WC_Order $order ) {
		$rates = array();
		foreach ( $order->get_items( 'tax' ) as $tax ) {
			$id      = (int) $tax->get_rate_id();
			$percent = method_exists( $tax, 'get_rate_percent' ) ? $tax->get_rate_percent() : null;
			if ( null === $percent || '' === $percent ) {
				$percent = class_exists( 'WC_Tax' ) ? (float) WC_Tax::get_rate_percent_value( $id ) : 0;
			}
			$rates[ $id ] = array(
				'label'    => $tax->get_label(),
				'percent'  => (float) $percent,
				'compound' => $tax->is_compound(),
			);
		}
		return $rates;
	}

	/**
	 * Tax recap: one row per rate (base, tax). With the per-rate mode (Japan) the tax of every rate is counted from
	 * its base once per document and the difference to the taxes of WooCommerce is kept.
	 *
	 * @param array  $lines    Lines.
	 * @param array  $rates    Rates of the order.
	 * @param array  $s        Settings.
	 * @param string $currency Currency.
	 * @return array
	 */
	private static function recap( array $lines, array $rates, array $s, $currency ) {
		$rows = array();
		$none = 0.0;
		foreach ( $lines as $line ) {
			if ( ! $line['taxes'] ) {
				$none += $line['net'];
				continue;
			}
			foreach ( $line['taxes'] as $rate => $amount ) {
				if ( ! isset( $rows[ $rate ] ) ) {
					$info          = isset( $rates[ $rate ] ) ? $rates[ $rate ] : array( 'label' => '', 'percent' => 0, 'compound' => false );
					$rows[ $rate ] = array(
						'label'   => $info['label'],
						'percent' => $info['percent'],
						'base'    => 0.0,
						'tax'     => 0.0,
					);
				}
				$rows[ $rate ]['base'] += $line['net'];
				$rows[ $rate ]['tax']  += $amount;
			}
		}
		$rows = array_values( $rows );
		usort(
			$rows,
			static function ( $a, $b ) {
				return $b['percent'] <=> $a['percent'];
			}
		);
		if ( abs( $none ) >= 0.005 && $s['seller']['vat'] ) {
			$rows[] = array(
				'label'   => '',
				'percent' => 0,
				'base'    => $none,
				'tax'     => 0.0,
			);
		}

		$diff = 0.0;
		foreach ( $rows as &$row ) {
			$row['base'] = round( $row['base'], 2 );
			$row['tax']  = round( $row['tax'], 2 );
			if ( 'per_rate' === $s['recap'] && $row['percent'] > 0 ) {
				$exact      = $row['base'] * $row['percent'] / 100;
				$fn         = $s['rounding'];
				$decimals   = self::decimals( $currency );
				$factor     = pow( 10, $decimals );
				$counted    = $fn( round( abs( $exact ) * $factor, 6 ) ) / $factor * ( $exact < 0 ? -1 : 1 );
				$diff      += $counted - $row['tax'];
				$row['tax'] = round( $counted, $decimals );
			}
		}
		unset( $row );
		return array(
			'rows'     => $rows,
			'rounding' => round( $diff, 2 ),
		);
	}

	/**
	 * Buyer: addresses and the identifiers mapped in the settings.
	 *
	 * @param WC_Order $order Order.
	 * @param array    $s     Settings.
	 * @return array
	 */
	private static function buyer( WC_Order $order, array $s ) {
		$billing  = self::address( $order, 'billing' );
		$shipping = $order->has_shipping_address() ? self::address( $order, 'shipping' ) : '';
		$ids      = array();
		foreach ( $s['buyer'] as $pair ) {
			$value = '' === $pair['source'] ? '' : self::source_value( $order, $pair['source'] );
			if ( '' !== $value ) {
				$ids[] = array(
					'label' => $pair['label'],
					'value' => $value,
				);
			}
		}
		return array(
			'name'     => trim( $order->get_formatted_billing_full_name() ),
			'company'  => $order->get_billing_company(),
			'address'  => $billing,
			'shipping' => $shipping === $billing ? '' : $shipping,
			'email'    => $order->get_billing_email(),
			'phone'    => $order->get_billing_phone(),
			'country'  => $order->get_billing_country(),
			'state'    => $order->get_billing_state(),
			'ids'      => $ids,
		);
	}

	/**
	 * An address without the name, one line each.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $type  billing|shipping.
	 * @return string
	 */
	private static function address( WC_Order $order, $type ) {
		$get     = static function ( $key ) use ( $order, $type ) {
			$method = 'get_' . $type . '_' . $key;
			return method_exists( $order, $method ) ? (string) $order->$method() : '';
		};
		$address = WC()->countries->get_formatted_address(
			array(
				'first_name' => 'shipping' === $type ? $get( 'first_name' ) : '',
				'last_name'  => 'shipping' === $type ? $get( 'last_name' ) : '',
				'company'    => 'shipping' === $type ? $get( 'company' ) : '',
				'address_1'  => $get( 'address_1' ),
				'address_2'  => $get( 'address_2' ),
				'city'       => $get( 'city' ),
				'state'      => $get( 'state' ),
				'postcode'   => $get( 'postcode' ),
				'country'    => $get( 'country' ),
			),
			"\n"
		);
		return trim( html_entity_decode( wp_strip_all_tags( str_replace( array( '<br/>', '<br>', '<br />' ), "\n", $address ) ), ENT_QUOTES, 'UTF-8' ) );
	}

	/**
	 * Value of a buyer identifier source:
	 * - "billing_company", "billing_phone" … (order getters),
	 * - "field:namespace/id" (additional checkout fields, e.g. of Envo Blocks Checkout Fields),
	 * - "meta:_key" (any order meta, e.g. of a VAT plugin).
	 *
	 * @param WC_Order $order  Order.
	 * @param string   $source Source.
	 * @return string
	 */
	public static function source_value( WC_Order $order, $source ) {
		if ( 0 === strpos( $source, 'meta:' ) ) {
			return trim( (string) $order->get_meta( substr( $source, 5 ) ) );
		}
		if ( 0 === strpos( $source, 'field:' ) ) {
			$key = substr( $source, 6 );
			foreach ( array( 'other', 'billing' ) as $group ) {
				$value = $order->get_meta( '_wc_' . $group . '/' . $key );
				if ( '' !== (string) $value ) {
					return trim( (string) $value );
				}
			}
			return '';
		}
		$method = 'get_' . $source;
		return preg_match( '/^(billing|shipping)_[a-z0-9_]+$/', $source ) && is_callable( array( $order, $method ) ) ? trim( (string) $order->$method() ) : '';
	}

	/**
	 * Sources for the buyer identifiers: value => label.
	 *
	 * @return array
	 */
	public static function sources() {
		$out = array(
			'billing_company' => __( 'Billing company', 'envo-blocks' ),
			'billing_phone'   => __( 'Billing phone', 'envo-blocks' ),
			'billing_state'   => __( 'Billing state / region', 'envo-blocks' ),
		);
		if ( class_exists( 'Envo_Blocks_Checkout_Fields' ) ) {
			foreach ( Envo_Blocks_Checkout_Fields::settings()['fields'] as $field ) {
				/* translators: %s: field label. */
				$out[ 'field:' . Envo_Blocks_Checkout_Fields::FIELD_NS . '/' . $field['id'] ] = sprintf( __( 'Checkout field: %s', 'envo-blocks' ), $field['label'] );
			}
		}
		return $out;
	}

	/**
	 * Direction of the document text.
	 *
	 * @param array $s Settings.
	 * @return string ltr|rtl
	 */
	private static function direction( array $s ) {
		if ( 'auto' !== $s['direction'] ) {
			return $s['direction'];
		}
		return in_array( substr( get_locale(), 0, 2 ), array( 'ar', 'he', 'fa', 'ur' ), true ) ? 'rtl' : 'ltr';
	}

	/* ------------------------------------------------------------------ */
	/* Formatting                                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * Amount for the document (WooCommerce decimals and separators; Indian grouping 1,00,000 for India).
	 *
	 * @param float $amount   Amount.
	 * @param array $data     Snapshot.
	 * @param bool  $currency With the currency symbol.
	 * @return string
	 */
	public static function money( $amount, array $data, $currency = true ) {
		$decimals = self::decimals( $data['currency'] );
		$dec_sep  = function_exists( 'wc_get_price_decimal_separator' ) ? wc_get_price_decimal_separator() : '.';
		$thou_sep = function_exists( 'wc_get_price_thousand_separator' ) ? wc_get_price_thousand_separator() : ',';
		$negative = $amount < 0;
		$number   = number_format( abs( (float) $amount ), $decimals, '.', '' );
		$parts    = explode( '.', $number );
		$int      = $parts[0];
		if ( 'indian' === $data['grouping'] && strlen( $int ) > 3 ) {
			$last = substr( $int, -3 );
			$rest = substr( $int, 0, -3 );
			$int  = preg_replace( '/\B(?=(\d{2})+(?!\d))/', $thou_sep, $rest ) . $thou_sep . $last;
		} else {
			$int = preg_replace( '/\B(?=(\d{3})+(?!\d))/', $thou_sep, $int );
		}
		$text = $int . ( isset( $parts[1] ) ? $dec_sep . $parts[1] : '' );
		if ( $currency ) {
			$symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( get_woocommerce_currency_symbol( $data['currency'] ), ENT_QUOTES, 'UTF-8' ) : $data['currency'];
			$format = function_exists( 'get_woocommerce_price_format' ) ? get_woocommerce_price_format() : '%1$s%2$s';
			$text   = str_replace( "\xc2\xa0", ' ', sprintf( html_entity_decode( $format, ENT_QUOTES, 'UTF-8' ), $symbol, $text ) );
		}
		return ( $negative ? '−' : '' ) . $text;
	}

	/**
	 * Decimals of amounts: the WooCommerce setting, but none for currencies without minor units (JPY, KRW …).
	 *
	 * @param string $currency Currency code.
	 * @return int
	 */
	public static function decimals( $currency ) {
		$decimals = function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2;
		$none     = array( 'BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' );
		return in_array( strtoupper( (string) $currency ), $none, true ) ? 0 : (int) $decimals;
	}

	/**
	 * Quantity without needless decimals.
	 *
	 * @param float $qty Quantity.
	 * @return string
	 */
	public static function qty( $qty ) {
		$text = rtrim( rtrim( number_format( (float) $qty, 3, '.', '' ), '0' ), '.' );
		return str_replace( '-', '−', $text );
	}

	/**
	 * Tax rate ("21 %", "9 % + 9 %").
	 *
	 * @param array $line Line.
	 * @param array $data Snapshot.
	 * @return string
	 */
	public static function rate( array $line, array $data ) {
		$out = array();
		foreach ( array_keys( $line['taxes'] ) as $rate ) {
			$out[] = self::rate_percent( $rate, $data );
		}
		$out = array_filter( $out, 'strlen' );
		return $out ? implode( ' + ', $out ) : ( $data['vat'] ? '0 %' : '' );
	}

	/**
	 * Percent of a tax rate id as text.
	 *
	 * @param int   $rate Rate id.
	 * @param array $data Snapshot.
	 * @return string
	 */
	private static function rate_percent( $rate, array $data ) {
		$percent = isset( $data['rates'][ $rate ] ) ? $data['rates'][ $rate ] : null;
		if ( null === $percent ) {
			return '';
		}
		return self::percent( $percent );
	}

	/**
	 * A percent value ("21 %", "8.25 %").
	 *
	 * @param float $percent Percent.
	 * @return string
	 */
	public static function percent( $percent ) {
		$dec = function_exists( 'wc_get_price_decimal_separator' ) ? wc_get_price_decimal_separator() : '.';
		return str_replace( '.', $dec, rtrim( rtrim( number_format( (float) $percent, 4, '.', '' ), '0' ), '.' ) ) . ' %';
	}

	/**
	 * Date for the document (its format, the time zone of the site at the time of issue).
	 *
	 * @param int   $time Timestamp.
	 * @param array $data Snapshot.
	 * @return string
	 */
	public static function date( $time, array $data ) {
		if ( ! $time ) {
			return '';
		}
		try {
			$zone = new DateTimeZone( $data['tz'] );
		} catch ( Exception $e ) {
			$zone = wp_timezone();
		}
		return wp_date( $data['date'], (int) $time, $zone );
	}

	/**
	 * Amount in words (English; Indian system with lakh and crore for the Indian grouping).
	 *
	 * @param float  $amount   Amount.
	 * @param string $currency Currency code.
	 * @param string $grouping intl|indian.
	 * @return string
	 */
	public static function words( $amount, $currency, $grouping = 'intl' ) {
		$amount = round( abs( (float) $amount ), 2 );
		$int    = (int) floor( $amount );
		$cents  = (int) round( ( $amount - $int ) * 100 );
		$names  = array(
			'INR' => array( 'Rupees', 'Paise' ),
			'USD' => array( 'Dollars', 'Cents' ),
			'EUR' => array( 'Euros', 'Cents' ),
			'GBP' => array( 'Pounds', 'Pence' ),
			'AUD' => array( 'Dollars', 'Cents' ),
			'CAD' => array( 'Dollars', 'Cents' ),
			'AED' => array( 'Dirhams', 'Fils' ),
			'SAR' => array( 'Riyals', 'Halalas' ),
		);
		$unit   = isset( $names[ $currency ] ) ? $names[ $currency ] : array( $currency, '' );
		$cents  = $cents ? ' and ' . ( '' !== $unit[1] ? self::number_words( $cents, $grouping ) . ' ' . $unit[1] : $cents . '/100' ) : '';
		// India: "Rupees … Only"; elsewhere "… Dollars and … Cents".
		return 'INR' === $currency
			? 'Rupees ' . self::number_words( $int, $grouping ) . $cents . ' Only'
			: self::number_words( $int, $grouping ) . ' ' . $unit[0] . $cents;
	}

	/**
	 * A whole number in English words.
	 *
	 * @param int    $n        Number.
	 * @param string $grouping intl|indian.
	 * @return string
	 */
	private static function number_words( $n, $grouping ) {
		$ones = array( '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen' );
		$tens = array( '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety' );
		$below_thousand = static function ( $n ) use ( $ones, $tens ) {
			$out = array();
			if ( $n >= 100 ) {
				$out[] = $ones[ (int) ( $n / 100 ) ] . ' Hundred';
				$n    %= 100;
			}
			if ( $n >= 20 ) {
				$out[] = $tens[ (int) ( $n / 10 ) ] . ( $n % 10 ? '-' . $ones[ $n % 10 ] : '' );
			} elseif ( $n > 0 ) {
				$out[] = $ones[ $n ];
			}
			return implode( ' ', $out );
		};
		if ( 0 === $n ) {
			return 'Zero';
		}
		$scales = 'indian' === $grouping
			? array( array( 10000000, 'Crore' ), array( 100000, 'Lakh' ), array( 1000, 'Thousand' ) )
			: array( array( 1000000000, 'Billion' ), array( 1000000, 'Million' ), array( 1000, 'Thousand' ) );
		$out    = array();
		foreach ( $scales as $scale ) {
			if ( $n >= $scale[0] ) {
				$count = (int) ( $n / $scale[0] );
				$out[] = ( $count >= 1000 ? self::number_words( $count, $grouping ) : $below_thousand( $count ) ) . ' ' . $scale[1];
				$n    %= $scale[0];
			}
		}
		if ( $n > 0 ) {
			$out[] = $below_thousand( $n );
		}
		return implode( ' ', $out );
	}
}
