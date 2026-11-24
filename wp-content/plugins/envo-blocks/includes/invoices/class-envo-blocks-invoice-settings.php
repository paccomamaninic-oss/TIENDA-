<?php
/**
 * Settings of the PDF invoices (option envo_blocks_invoices): defaults, cleaning, presets.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Invoice settings.
 */
final class Envo_Blocks_Invoice_Settings {

	const OPTION = 'envo_blocks_invoices';

	/**
	 * Settings of this request.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Customer e-mails that can carry the invoice (id => title).
	 *
	 * @return array
	 */
	public static function emails() {
		return array(
			'customer_on_hold_order'    => __( 'Order on-hold', 'envo-blocks' ),
			'customer_processing_order' => __( 'Processing order', 'envo-blocks' ),
			'customer_completed_order'  => __( 'Completed order', 'envo-blocks' ),
			'customer_invoice'          => __( 'Order details (sent from the order screen)', 'envo-blocks' ),
		);
	}

	/**
	 * Defaults (the preset of the shop country).
	 *
	 * @return array
	 */
	public static function defaults() {
		$country  = function_exists( 'wc_get_base_location' ) ? wc_get_base_location()['country'] : '';
		$defaults = array(
			'enabled'     => false,
			'preset'      => Envo_Blocks_Invoice_Presets::for_country( $country ),
			'lang'        => 'en',
			'statuses'    => array( 'processing', 'completed' ),
			'zero'        => false,
			'emails'      => array( 'customer_processing_order', 'customer_completed_order', 'customer_invoice' ),
			'credit_mail' => true,
			'guest_links' => true,
			'due_days'    => 14,
			'paper'       => 'A4',
			'date'        => 'Y-m-d',
			'direction'   => 'auto',
			'year_start'  => 1,
			'supply'      => 'issue',
			'reference'   => 'order',
			'seller'      => array(
				'name'    => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				'address' => self::store_address(),
				'ids'     => array(),
				'vat'     => true,
				'bank'    => '',
				'footer'  => '',
				'logo'    => 0,
			),
			'numbers'     => array(
				'invoice' => array(
					'format' => '{year}{number:5}',
					'reset'  => 'year',
					'start'  => 1,
				),
				'credit'  => array(
					'format' => 'CN{year}{number:4}',
					'reset'  => 'year',
					'start'  => 1,
				),
			),
			'columns'     => array(
				'sku'      => true,
				'unit'     => true,
				'discount' => false,
				'rate'     => true,
				'tax'      => true,
			),
			'fields'      => array(),
			'buyer'       => array(),
			'note'        => '',
			'terms'       => '',
			'qr'          => true,
			'recap'       => 'wc',
			'rounding'    => 'floor',
			'words'       => false,
			'grouping'    => 'intl',
			'accent'      => '#4f46e5',
			'texts'       => array(
				'non_vat'        => '',
				'reverse_charge' => '',
				'reduced'        => '',
			),
			'labels'      => Envo_Blocks_Invoice_Presets::labels( 'en' ),
		);
		return self::with_preset( $defaults, $defaults['preset'] );
	}

	/**
	 * The store address of WooCommerce > Settings > General, one line each.
	 *
	 * @return string
	 */
	private static function store_address() {
		$lines   = array_filter(
			array(
				get_option( 'woocommerce_store_address', '' ),
				get_option( 'woocommerce_store_address_2', '' ),
				trim( get_option( 'woocommerce_store_postcode', '' ) . ' ' . get_option( 'woocommerce_store_city', '' ) ),
			)
		);
		$country = function_exists( 'wc_get_base_location' ) ? wc_get_base_location()['country'] : '';
		if ( $country && function_exists( 'WC' ) && WC()->countries ) {
			$countries = WC()->countries->get_countries();
			$lines[]   = isset( $countries[ $country ] ) ? html_entity_decode( $countries[ $country ], ENT_QUOTES, 'UTF-8' ) : $country;
		}
		return implode( "\n", $lines );
	}

	/**
	 * Settings with a preset applied: labels, identifier labels (values kept by position), texts, paper, date and
	 * the other preset values. Seller name, address, bank, numbering and the e-mails stay.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Preset key.
	 * @return array
	 */
	public static function with_preset( array $settings, $key ) {
		$preset             = Envo_Blocks_Invoice_Presets::get( $key );
		$settings['preset'] = $key;
		$settings['lang']   = $preset['lang'];
		$settings['labels'] = Envo_Blocks_Invoice_Presets::labels( $preset['lang'] );
		if ( '' !== $preset['title'] ) {
			$settings['labels']['invoice'] = $preset['title'];
		}
		if ( '' !== $preset['tax'] ) {
			$settings['labels']['tax'] = $preset['tax'];
		}
		$old = isset( $settings['seller']['ids'] ) ? array_values( $settings['seller']['ids'] ) : array();
		$ids = array();
		foreach ( $preset['seller'] as $i => $label ) {
			$ids[] = array(
				'label' => $label,
				'value' => isset( $old[ $i ]['value'] ) ? $old[ $i ]['value'] : '',
			);
		}
		$settings['seller']['ids'] = $ids;
		if ( '' !== $preset['footer'] && '' === trim( (string) $settings['seller']['footer'] ) ) {
			$settings['seller']['footer'] = $preset['footer'];
		}
		$settings['texts']      = array_merge( array( 'non_vat' => '', 'reverse_charge' => '', 'reduced' => '' ), $preset['texts'] );
		$settings['paper']      = $preset['paper'];
		$settings['date']       = $preset['date'];
		$settings['grouping']   = $preset['grouping'];
		$settings['recap']      = $preset['recap'];
		$settings['words']      = $preset['words'];
		$settings['year_start'] = $preset['year'];
		$settings['reference']  = 'cz' === $key || 'sk' === $key ? 'order' : $settings['reference'];
		// Buyer identifiers: keep the mapped fields, add the preset labels that are missing.
		$labels = wp_list_pluck( (array) $settings['buyer'], 'label' );
		foreach ( $preset['buyer'] as $label ) {
			if ( ! in_array( $label, $labels, true ) ) {
				$settings['buyer'][] = array(
					'label'  => $label,
					'source' => '',
				);
			}
		}
		// Product fields the preset recommends (India: HSN/SAC).
		$keys = wp_list_pluck( (array) $settings['fields'], 'key' );
		foreach ( $preset['fields'] as $field => $label ) {
			if ( ! in_array( $field, $keys, true ) ) {
				$settings['fields'][] = array(
					'key'    => $field,
					'label'  => $label,
					'column' => true,
				);
			}
		}
		return $settings;
	}

	/**
	 * Order status without the "wc-" prefix.
	 *
	 * @param string $status Status key.
	 * @return string
	 */
	public static function status_slug( $status ) {
		return 0 === strpos( (string) $status, 'wc-' ) ? substr( (string) $status, 3 ) : (string) $status;
	}

	/**
	 * One raw value of the saved settings, without building the defaults (safe before "init", no translations).
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	public static function raw( $key ) {
		$saved = get_option( self::OPTION, array() );
		return is_array( $saved ) && isset( $saved[ $key ] ) ? $saved[ $key ] : null;
	}

	/**
	 * Saved settings over the defaults.
	 *
	 * @return array
	 */
	public static function get() {
		if ( null === self::$cache ) {
			$saved       = get_option( self::OPTION, array() );
			self::$cache = is_array( $saved ) && $saved ? self::sanitize( $saved ) : self::defaults();
		}
		return self::$cache;
	}

	/**
	 * Saves settings.
	 *
	 * @param array $settings Settings (already cleaned).
	 */
	public static function save( array $settings ) {
		update_option( self::OPTION, $settings, false );
		self::$cache = null;
	}

	/**
	 * Cleans settings (saved or from the form, unslashed).
	 *
	 * @param array $raw Raw settings.
	 * @return array
	 */
	public static function sanitize( array $raw ) {
		$d    = self::defaults();
		$text = static function ( $value, $default = '' ) {
			return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $default;
		};
		$area = static function ( $value ) {
			return is_scalar( $value ) ? sanitize_textarea_field( (string) $value ) : '';
		};
		$bool = static function ( $value ) {
			return ! empty( $value ) && 'no' !== $value;
		};
		$pick = static function ( $value, array $allowed, $default ) {
			return in_array( $value, $allowed, true ) ? $value : $default;
		};

		$s                = array();
		$s['enabled']     = $bool( isset( $raw['enabled'] ) ? $raw['enabled'] : false );
		$s['preset']      = $pick( isset( $raw['preset'] ) ? $raw['preset'] : '', array_keys( Envo_Blocks_Invoice_Presets::all() ), $d['preset'] );
		$s['lang']        = $pick( isset( $raw['lang'] ) ? $raw['lang'] : '', array_keys( Envo_Blocks_Invoice_Presets::languages() ), 'en' );
		$statuses         = function_exists( 'wc_get_order_statuses' ) ? array_map( array( __CLASS__, 'status_slug' ), array_keys( wc_get_order_statuses() ) ) : array();
		$s['statuses']    = array_values( array_intersect( isset( $raw['statuses'] ) ? (array) $raw['statuses'] : array(), array_diff( $statuses, array( 'cancelled', 'failed', 'checkout-draft' ) ) ) );
		$s['zero']        = $bool( isset( $raw['zero'] ) ? $raw['zero'] : false );
		$s['emails']      = array_values( array_intersect( isset( $raw['emails'] ) ? (array) $raw['emails'] : array(), array_keys( self::emails() ) ) );
		$s['credit_mail'] = $bool( isset( $raw['credit_mail'] ) ? $raw['credit_mail'] : false );
		$s['guest_links'] = $bool( isset( $raw['guest_links'] ) ? $raw['guest_links'] : false );
		$s['due_days']    = max( 0, min( 365, (int) ( isset( $raw['due_days'] ) ? $raw['due_days'] : $d['due_days'] ) ) );
		$s['paper']       = $pick( isset( $raw['paper'] ) ? $raw['paper'] : '', array( 'A4', 'Letter' ), 'A4' );
		$s['date']        = $text( isset( $raw['date'] ) ? $raw['date'] : '', $d['date'] );
		$s['date']        = '' === $s['date'] ? 'Y-m-d' : $s['date'];
		$s['direction']   = $pick( isset( $raw['direction'] ) ? $raw['direction'] : '', array( 'auto', 'ltr', 'rtl' ), 'auto' );
		$s['year_start']  = max( 1, min( 12, (int) ( isset( $raw['year_start'] ) ? $raw['year_start'] : 1 ) ) );
		$s['supply']      = $pick( isset( $raw['supply'] ) ? $raw['supply'] : '', array( 'issue', 'order', 'paid' ), 'issue' );
		$s['reference']   = $pick( isset( $raw['reference'] ) ? $raw['reference'] : '', array( 'order', 'invoice' ), 'order' );

		$seller      = isset( $raw['seller'] ) && is_array( $raw['seller'] ) ? $raw['seller'] : array();
		$s['seller'] = array(
			'name'    => $text( isset( $seller['name'] ) ? $seller['name'] : '' ),
			'address' => $area( isset( $seller['address'] ) ? $seller['address'] : '' ),
			'ids'     => self::pairs( isset( $seller['ids'] ) ? $seller['ids'] : array(), 'value' ),
			'vat'     => $bool( isset( $seller['vat'] ) ? $seller['vat'] : false ),
			'bank'    => $area( isset( $seller['bank'] ) ? $seller['bank'] : '' ),
			'footer'  => $area( isset( $seller['footer'] ) ? $seller['footer'] : '' ),
			'logo'    => absint( isset( $seller['logo'] ) ? $seller['logo'] : 0 ),
		);

		$s['numbers'] = array();
		foreach ( array( 'invoice', 'credit' ) as $type ) {
			$n                    = isset( $raw['numbers'][ $type ] ) && is_array( $raw['numbers'][ $type ] ) ? $raw['numbers'][ $type ] : array();
			$format               = $text( isset( $n['format'] ) ? $n['format'] : '' );
			$s['numbers'][ $type ] = array(
				'format' => false === strpos( $format, '{number' ) ? $d['numbers'][ $type ]['format'] : $format,
				'reset'  => $pick( isset( $n['reset'] ) ? $n['reset'] : '', array( 'never', 'year', 'month' ), 'year' ),
				'start'  => max( 1, (int) ( isset( $n['start'] ) ? $n['start'] : 1 ) ),
			);
		}

		$s['columns'] = array();
		foreach ( array_keys( $d['columns'] ) as $column ) {
			$s['columns'][ $column ] = $bool( isset( $raw['columns'][ $column ] ) ? $raw['columns'][ $column ] : false );
		}

		$s['fields'] = array();
		foreach ( isset( $raw['fields'] ) && is_array( $raw['fields'] ) ? array_slice( array_values( $raw['fields'] ), 0, 10 ) : array() as $field ) {
			$label = is_array( $field ) ? $text( isset( $field['label'] ) ? $field['label'] : '' ) : '';
			$key   = is_array( $field ) ? sanitize_key( isset( $field['key'] ) && '' !== $field['key'] ? $field['key'] : sanitize_title( $label ) ) : '';
			if ( '' !== $label && '' !== $key ) {
				$s['fields'][] = array(
					'key'    => substr( $key, 0, 30 ),
					'label'  => $label,
					'column' => $bool( isset( $field['column'] ) ? $field['column'] : false ),
				);
			}
		}

		$s['buyer'] = self::pairs( isset( $raw['buyer'] ) ? $raw['buyer'] : array(), 'source' );
		foreach ( $s['buyer'] as &$pair ) {
			$pair['source'] = preg_replace( '/[^a-z0-9_\-\/:]/', '', strtolower( $pair['source'] ) );
		}
		unset( $pair );

		$s['note']     = $area( isset( $raw['note'] ) ? $raw['note'] : '' );
		$s['terms']    = $area( isset( $raw['terms'] ) ? $raw['terms'] : '' );
		$s['qr']       = $bool( isset( $raw['qr'] ) ? $raw['qr'] : false );
		$s['recap']    = $pick( isset( $raw['recap'] ) ? $raw['recap'] : '', array( 'wc', 'per_rate' ), 'wc' );
		$s['rounding'] = $pick( isset( $raw['rounding'] ) ? $raw['rounding'] : '', array( 'floor', 'round', 'ceil' ), 'floor' );
		$s['words']    = $bool( isset( $raw['words'] ) ? $raw['words'] : false );
		$s['grouping'] = $pick( isset( $raw['grouping'] ) ? $raw['grouping'] : '', array( 'intl', 'indian' ), 'intl' );
		$accent        = isset( $raw['accent'] ) ? sanitize_hex_color( (string) $raw['accent'] ) : '';
		$s['accent']   = $accent ? $accent : $d['accent'];

		$s['texts'] = array();
		foreach ( array( 'non_vat', 'reverse_charge', 'reduced' ) as $key ) {
			$s['texts'][ $key ] = $area( isset( $raw['texts'][ $key ] ) ? $raw['texts'][ $key ] : '' );
		}

		$s['labels'] = Envo_Blocks_Invoice_Presets::labels( $s['lang'] );
		foreach ( array_keys( $s['labels'] ) as $key ) {
			if ( isset( $raw['labels'][ $key ] ) && '' !== trim( (string) $raw['labels'][ $key ] ) ) {
				$s['labels'][ $key ] = $text( $raw['labels'][ $key ] );
			}
		}
		return $s;
	}

	/**
	 * List of array( label, <second> ) pairs with a label.
	 *
	 * @param mixed  $rows   Rows.
	 * @param string $second Name of the second key.
	 * @return array
	 */
	private static function pairs( $rows, $second ) {
		$out = array();
		foreach ( is_array( $rows ) ? array_slice( array_values( $rows ), 0, 10 ) : array() as $row ) {
			if ( ! is_array( $row ) || ! isset( $row['label'] ) || '' === trim( (string) $row['label'] ) ) {
				continue;
			}
			$out[] = array(
				'label'  => sanitize_text_field( (string) $row['label'] ),
				$second => isset( $row[ $second ] ) ? sanitize_text_field( (string) $row[ $second ] ) : '',
			);
		}
		return $out;
	}
}
