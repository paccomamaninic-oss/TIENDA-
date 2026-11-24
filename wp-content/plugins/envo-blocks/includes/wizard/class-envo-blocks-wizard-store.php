<?php
/**
 * "Your store" step of the wizard (after the import, can be skipped): country and currency, payments (bank transfer
 * with the QR code of Envo Blocks, cash on delivery), one shipping zone with a free-shipping limit (the free shipping
 * bar uses it by itself), the store pages (shop, cart, checkout, My account, terms, privacy – chosen or created) and a
 * few sample products that can be deleted with one click while they are unedited.
 *
 * Everything the step changes is saved before, so "Undo everything" puts it back.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Store settings.
 */
final class Envo_Blocks_Wizard_Store {
	/**
	 * WooCommerce settings from before the step (option => value, or null when the option did not exist).
	 */
	const BACKUP = 'envo_blocks_wizard_store_backup';

	/**
	 * Marks the sample products; the value is the modification time right after they were made.
	 */
	const SAMPLE_META = '_envo_blocks_wizard_sample';

	/**
	 * Options the step may change.
	 */
	const OPTIONS = array(
		'woocommerce_default_country',
		'woocommerce_currency',
		'woocommerce_currency_pos',
		'woocommerce_price_thousand_sep',
		'woocommerce_price_decimal_sep',
		'woocommerce_price_num_decimals',
		'woocommerce_bacs_settings',
		'woocommerce_bacs_accounts',
		'woocommerce_cod_settings',
		'woocommerce_store_address',
		'woocommerce_store_address_2',
		'woocommerce_store_city',
		'woocommerce_store_postcode',
		'woocommerce_shop_page_id',
		'woocommerce_cart_page_id',
		'woocommerce_checkout_page_id',
		'woocommerce_myaccount_page_id',
		'woocommerce_terms_page_id',
		'woocommerce_refund_returns_page_id',
		'wp_page_for_privacy_policy',
	);

	/**
	 * Store pages of the step: key => option. The first four are needed by WooCommerce; terms and privacy may be none.
	 */
	const PAGES = array(
		'shop'      => 'woocommerce_shop_page_id',
		'cart'      => 'woocommerce_cart_page_id',
		'checkout'  => 'woocommerce_checkout_page_id',
		'myaccount' => 'woocommerce_myaccount_page_id',
		'terms'     => 'woocommerce_terms_page_id',
		'privacy'   => 'wp_page_for_privacy_policy',
	);

	/**
	 * Euro countries (for the currency suggested for a country).
	 */
	const EURO = array( 'AT', 'BE', 'CY', 'DE', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PT', 'SI', 'SK' );

	/**
	 * Currency of a country (euro countries and the most common others; '' = unknown).
	 *
	 * @param string $country Country code.
	 * @return string
	 */
	public static function currency_of( $country ) {
		if ( in_array( $country, self::EURO, true ) ) {
			return 'EUR';
		}
		$map = array(
			'CZ' => 'CZK', 'PL' => 'PLN', 'HU' => 'HUF', 'RO' => 'RON', 'BG' => 'BGN', 'DK' => 'DKK', 'SE' => 'SEK', 'NO' => 'NOK',
			'CH' => 'CHF', 'GB' => 'GBP', 'US' => 'USD', 'CA' => 'CAD', 'AU' => 'AUD', 'NZ' => 'NZD', 'BR' => 'BRL', 'MX' => 'MXN',
			'JP' => 'JPY', 'IN' => 'INR', 'UA' => 'UAH', 'TR' => 'TRY', 'ZA' => 'ZAR', 'IS' => 'ISK', 'RS' => 'RSD', 'BA' => 'BAM',
		);
		return isset( $map[ $country ] ) ? $map[ $country ] : '';
	}

	/**
	 * Suggestion from the site language (cs_CZ → CZ + CZK, de_DE → DE + EUR, en_US → US + USD), else the store's
	 * current settings.
	 *
	 * @return array country, currency.
	 */
	public static function suggestion() {
		$locale    = get_locale();
		$countries = function_exists( 'WC' ) ? WC()->countries->get_countries() : array();
		$by_lang   = array( 'cs' => 'CZ', 'sk' => 'SK', 'pl' => 'PL', 'hu' => 'HU', 'de' => 'DE', 'fr' => 'FR', 'it' => 'IT', 'es' => 'ES', 'nl' => 'NL', 'pt' => 'PT', 'da' => 'DK', 'sv' => 'SE', 'nb' => 'NO', 'fi' => 'FI', 'ro' => 'RO', 'bg' => 'BG', 'hr' => 'HR', 'sl' => 'SI', 'el' => 'GR', 'et' => 'EE', 'lt' => 'LT', 'lv' => 'LV', 'en' => 'US' );
		$country   = '';
		if ( preg_match( '/^([a-z]{2,3})(?:_([A-Z]{2}))?/', $locale, $m ) ) {
			$country = isset( $m[2] ) && isset( $countries[ $m[2] ] ) ? $m[2] : ( isset( $by_lang[ $m[1] ] ) ? $by_lang[ $m[1] ] : '' );
		}
		if ( '' === $country ) {
			$country = strtok( (string) get_option( 'woocommerce_default_country', 'US' ), ':' );
		}
		$currency = self::currency_of( $country );
		return array(
			'country'  => $country,
			'currency' => '' !== $currency ? $currency : (string) get_option( 'woocommerce_currency', 'USD' ),
		);
	}

	/**
	 * Data of the step for the screen.
	 *
	 * @return array
	 */
	public static function data() {
		if ( ! function_exists( 'WC' ) ) {
			return array( 'available' => false );
		}
		$currencies = array();
		foreach ( get_woocommerce_currencies() as $code => $name ) {
			$currencies[ $code ] = $name . ' (' . html_entity_decode( get_woocommerce_currency_symbol( $code ), ENT_QUOTES, 'UTF-8' ) . ')';
		}
		$bacs     = (array) get_option( 'woocommerce_bacs_accounts', array() );
		$account  = $bacs ? (array) reset( $bacs ) : array();
		$samples  = self::samples();
		return array(
			'available'  => true,
			'suggest'    => self::suggestion(),
			'countries'  => WC()->countries->get_countries(),
			'currencies' => $currencies,
			'currencyOf' => array_filter( array_combine( array_keys( WC()->countries->get_countries() ), array_map( array( __CLASS__, 'currency_of' ), array_keys( WC()->countries->get_countries() ) ) ) ),
			'account'    => array(
				'name' => isset( $account['account_name'] ) ? $account['account_name'] : get_option( 'blogname' ),
				'iban' => isset( $account['iban'] ) ? $account['iban'] : '',
				'bic'  => isset( $account['bic'] ) ? $account['bic'] : '',
			),
			'samples'    => array( 'total' => count( $samples ), 'untouched' => count( array_filter( $samples, array( __CLASS__, 'untouched' ) ) ) ),
			'done'       => (bool) get_option( self::BACKUP ),
			'products'   => (int) wp_count_posts( 'product' )->publish,
			'address'    => array(
				'address'  => (string) get_option( 'woocommerce_store_address', '' ),
				'address2' => (string) get_option( 'woocommerce_store_address_2', '' ),
				'city'     => (string) get_option( 'woocommerce_store_city', '' ),
				'postcode' => (string) get_option( 'woocommerce_store_postcode', '' ),
				'state'    => (string) substr( strstr( (string) get_option( 'woocommerce_default_country', '' ), ':' ) ?: '', 1 ),
			),
			'states'     => self::states( self::suggestion()['country'] ),
			'pages'      => self::page_list(),
			'storePages' => self::store_pages(),
		);
	}

	/**
	 * Pages to choose from (published and drafts – a chosen draft is published), with what WooCommerce content they
	 * hold: a cart / checkout / My account page without its block or shortcode does not work.
	 *
	 * @return array[] id, title, status, has (cart, checkout, myaccount).
	 */
	private static function page_list() {
		$list = array();
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'private' ), 'numberposts' => 300, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
			$c      = $p->post_content;
			$list[] = array(
				'id'     => $p->ID,
				'title'  => '' !== $p->post_title ? html_entity_decode( $p->post_title, ENT_QUOTES, 'UTF-8' ) : '#' . $p->ID,
				'status' => $p->post_status,
				'url'    => get_permalink( $p ),
				'has'    => array_keys(
					array_filter(
						array(
							'cart'      => false !== strpos( $c, 'wp:woocommerce/cart' ) || false !== strpos( $c, 'woocommerce_cart' ),
							'checkout'  => false !== strpos( $c, 'wp:woocommerce/checkout' ) || false !== strpos( $c, 'woocommerce_checkout' ),
							'myaccount' => false !== strpos( $c, 'woocommerce_my_account' ) || false !== strpos( $c, '"shortcode":"my_account"' ) || false !== strpos( $c, 'wp:woocommerce/customer-account' ),
						)
					)
				),
			);
		}
		return $list;
	}

	/**
	 * The current store pages: key => page ID (0 = none or the page is gone / in the trash).
	 *
	 * @return array
	 */
	private static function store_pages() {
		$out = array();
		foreach ( self::PAGES as $key => $option ) {
			$id          = (int) get_option( $option, 0 );
			$status      = $id ? get_post_status( $id ) : false;
			$out[ $key ] = $status && 'trash' !== $status && 'page' === get_post_type( $id ) ? $id : 0;
		}
		return $out;
	}

	/**
	 * Sets the store pages: an existing page (a draft is published), a new one, or none (terms / privacy only).
	 *
	 * @param array $in key => page ID | 'new' | ''.
	 * @return array created page IDs, published draft IDs.
	 */
	private static function set_pages( $in ) {
		$created   = array();
		$published = array();
		$wc_new    = false;
		foreach ( self::PAGES as $key => $option ) {
			if ( ! isset( $in[ $key ] ) ) {
				continue;
			}
			$value = (string) $in[ $key ];
			if ( 'new' === $value ) {
				if ( in_array( $key, array( 'terms', 'privacy' ), true ) ) {
					$id = self::create_legal( $key );
					if ( $id ) {
						update_option( $option, $id );
						$created[] = $id;
					}
				} else {
					// WooCommerce makes its own pages (with the cart / checkout blocks of this version) for empty options.
					update_option( $option, 0 );
					$wc_new = true;
				}
				continue;
			}
			$id = (int) $value;
			if ( ! $id ) {
				if ( in_array( $key, array( 'terms', 'privacy' ), true ) ) {
					update_option( $option, 0 );
				}
				continue;
			}
			if ( 'page' !== get_post_type( $id ) ) {
				continue;
			}
			if ( 'publish' !== get_post_status( $id ) ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
				$published[] = $id;
			}
			update_option( $option, $id );
		}
		if ( $wc_new && class_exists( 'WC_Install' ) ) {
			$before = array();
			foreach ( array( 'woocommerce_shop_page_id', 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id', 'woocommerce_refund_returns_page_id' ) as $option ) {
				$before[ $option ] = (int) get_option( $option, 0 );
			}
			WC_Install::create_pages();
			foreach ( $before as $option => $id ) {
				$now = (int) get_option( $option, 0 );
				if ( $now && $now !== $id && ! in_array( $now, $created, true ) ) {
					$created[] = $now;
				}
			}
		}
		return array( 'created' => $created, 'published' => $published );
	}

	/**
	 * A starting draft of the terms or the privacy policy (published, so the checkout links it), clearly marked to be
	 * checked: the store name, address and e-mail filled in.
	 *
	 * @param string $key terms | privacy.
	 * @return int Page ID.
	 */
	private static function create_legal( $key ) {
		$name    = get_option( 'blogname' );
		$address = implode( ', ', array_filter( array( get_option( 'woocommerce_store_address' ), get_option( 'woocommerce_store_address_2' ), trim( get_option( 'woocommerce_store_postcode' ) . ' ' . get_option( 'woocommerce_store_city' ) ) ) ) );
		$email   = get_option( 'woocommerce_email_from_address', get_option( 'admin_email' ) );
		$p       = function ( $text ) {
			return '<!-- wp:paragraph --><p>' . esc_html( $text ) . '</p><!-- /wp:paragraph -->';
		};
		$h       = function ( $text ) {
			return '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $text ) . '</h2><!-- /wp:heading -->';
		};
		$note    = '<!-- wp:paragraph {"style":{"color":{"background":"#fff7d6"},"spacing":{"padding":{"top":"12px","right":"16px","bottom":"12px","left":"16px"}}}} --><p class="has-background" style="background-color:#fff7d6;padding-top:12px;padding-right:16px;padding-bottom:12px;padding-left:16px">' . esc_html__( 'Starting draft made by the store wizard – check and complete it for your store and the law of your country before you start selling, then delete this note.', 'envo-blocks' ) . '</p><!-- /wp:paragraph -->';
		$seller  = sprintf( /* translators: 1: store name, 2: address, 3: e-mail */ __( 'Seller: %1$s, %2$s, e-mail %3$s.', 'envo-blocks' ), $name, '' !== $address ? $address : __( '[your address]', 'envo-blocks' ), $email );
		if ( 'privacy' === $key ) {
			$title   = __( 'Privacy policy', 'envo-blocks' );
			$content = $note . $p( $seller )
				. $h( __( 'What data we collect', 'envo-blocks' ) ) . $p( __( 'When you order, we collect your name, billing and delivery address, e-mail, phone number and the details of your order. When you create an account, we also keep your login details and order history.', 'envo-blocks' ) )
				. $h( __( 'Why we use it', 'envo-blocks' ) ) . $p( __( 'To process and deliver your order, to handle payments, returns and complaints, and to meet our legal and accounting obligations. We send newsletters only if you agree to them.', 'envo-blocks' ) )
				. $h( __( 'Who we share it with', 'envo-blocks' ) ) . $p( __( 'With the carrier that delivers your order, the payment provider and our accountant – only what they need. [List your carriers and services here.]', 'envo-blocks' ) )
				. $h( __( 'How long we keep it', 'envo-blocks' ) ) . $p( __( 'Order data as long as accounting law requires; account data until you delete your account.', 'envo-blocks' ) )
				. $h( __( 'Your rights', 'envo-blocks' ) ) . $p( sprintf( /* translators: %s: e-mail */ __( 'You can ask for a copy of your data, its correction or deletion, and object to its use – write to %s. You can also complain to your data protection authority.', 'envo-blocks' ), $email ) )
				. $h( __( 'Cookies', 'envo-blocks' ) ) . $p( __( 'The store uses cookies needed for the cart and login. [Add analytics or marketing cookies if you use them.]', 'envo-blocks' ) );
		} else {
			$title   = __( 'Terms and conditions', 'envo-blocks' );
			$content = $note . $p( $seller )
				. $h( __( 'Orders', 'envo-blocks' ) ) . $p( __( 'The order is placed when you finish the checkout. You get a confirmation by e-mail; the contract is made when we confirm the order.', 'envo-blocks' ) )
				. $h( __( 'Prices and payment', 'envo-blocks' ) ) . $p( __( 'Prices are shown including tax [check]. You can pay by the methods offered in the checkout; by bank transfer the goods are sent after the payment arrives.', 'envo-blocks' ) )
				. $h( __( 'Delivery', 'envo-blocks' ) ) . $p( __( 'The delivery methods, prices and times are shown in the checkout. [Add your usual delivery time.]', 'envo-blocks' ) )
				. $h( __( 'Returns', 'envo-blocks' ) ) . $p( __( 'You can withdraw from the contract within 14 days of receiving the goods without giving a reason. Send the goods back unused; we refund the payment within 14 days of receiving them.', 'envo-blocks' ) )
				. $h( __( 'Complaints', 'envo-blocks' ) ) . $p( sprintf( /* translators: %s: e-mail */ __( 'If the goods are faulty, write to %s with the order number and a description of the fault. We handle complaints within 30 days.', 'envo-blocks' ), $email ) );
		}
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $content ) );
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	/**
	 * States / regions of a country (empty when the country has none in WooCommerce).
	 *
	 * @param string $country Country code.
	 * @return array code => name.
	 */
	public static function states( $country ) {
		$states = function_exists( 'WC' ) ? WC()->countries->get_states( $country ) : array();
		return is_array( $states ) ? array_map( 'html_entity_decode', $states ) : array();
	}

	/**
	 * Saves the settings the step is about to change (once).
	 */
	private static function backup() {
		if ( get_option( self::BACKUP ) ) {
			return;
		}
		$backup = array( 'options' => array(), 'zone' => 0 );
		foreach ( self::OPTIONS as $option ) {
			$value                        = get_option( $option, null );
			$backup['options'][ $option ] = $value;
		}
		update_option( self::BACKUP, $backup, false );
	}

	/**
	 * Applies the step.
	 *
	 * @param array $in country, currency, bacs (on, name, iban, bic), cod, shipping (on, cost, free), samples.
	 * @return array|WP_Error What was done.
	 */
	public static function apply( $in ) {
		if ( ! function_exists( 'WC' ) ) {
			return new WP_Error( 'ebw_wc', __( 'WooCommerce is not active.', 'envo-blocks' ) );
		}
		$countries = WC()->countries->get_countries();
		$country   = isset( $in['country'] ) && isset( $countries[ $in['country'] ] ) ? $in['country'] : self::suggestion()['country'];
		$currency  = isset( $in['currency'] ) && array_key_exists( $in['currency'], get_woocommerce_currencies() ) ? $in['currency'] : self::suggestion()['currency'];
		self::backup();
		$done = array();

		// Country, currency and the price format of that country ("1 234,50 Kč", "1.234,50 €", "$1,234.50").
		$addr   = isset( $in['address'] ) ? (array) $in['address'] : array();
		$states = self::states( $country );
		$state  = isset( $addr['state'] ) && isset( $states[ $addr['state'] ] ) ? $addr['state'] : '';
		update_option( 'woocommerce_default_country', '' !== $state ? $country . ':' . $state : $country );
		update_option( 'woocommerce_currency', $currency );
		// Store address (invoices, e-mails, taxes): only when something was filled in.
		$fields = array( 'address' => 'woocommerce_store_address', 'address2' => 'woocommerce_store_address_2', 'city' => 'woocommerce_store_city', 'postcode' => 'woocommerce_store_postcode' );
		if ( array_filter( array_map( 'trim', array_map( 'strval', array_intersect_key( $addr, $fields ) ) ) ) ) {
			foreach ( $fields as $key => $option ) {
				update_option( $option, sanitize_text_field( isset( $addr[ $key ] ) ? (string) $addr[ $key ] : '' ) );
			}
			$done[] = 'address';
		}
		$comma = ! in_array( $country, array( 'US', 'GB', 'IE', 'CA', 'AU', 'NZ', 'MX', 'IN', 'JP', 'CN', 'KR', 'IL', 'TH', 'CH', 'MT', 'CY', 'SG', 'HK', 'ZA', 'PH', 'MY' ), true );
		$dots  = in_array( $country, array( 'DE', 'AT', 'NL', 'BE', 'IT', 'ES', 'PT', 'DK', 'ID', 'TR', 'GR', 'RO', 'HR', 'SI', 'BR', 'AR', 'CL', 'CO', 'RS', 'BA', 'IS' ), true );
		update_option( 'woocommerce_price_decimal_sep', $comma ? ',' : '.' );
		update_option( 'woocommerce_price_thousand_sep', $comma ? ( $dots ? '.' : ' ' ) : ',' );
		update_option( 'woocommerce_currency_pos', in_array( $currency, array( 'USD', 'GBP', 'AUD', 'CAD', 'NZD', 'MXN', 'BRL', 'INR', 'JPY' ), true ) && ! $comma ? 'left' : 'right_space' );
		update_option( 'woocommerce_price_num_decimals', in_array( $currency, array( 'HUF', 'JPY', 'ISK' ), true ) ? 0 : 2 );
		$done[] = 'country';

		// Payments.
		$bacs = isset( $in['bacs'] ) ? (array) $in['bacs'] : array();
		if ( ! empty( $bacs['on'] ) ) {
			$iban = strtoupper( preg_replace( '/\s+/', '', (string) ( isset( $bacs['iban'] ) ? $bacs['iban'] : '' ) ) );
			$settings            = (array) get_option( 'woocommerce_bacs_settings', array() );
			$settings['enabled'] = 'yes';
			update_option( 'woocommerce_bacs_settings', $settings );
			if ( '' !== $iban ) {
				update_option(
					'woocommerce_bacs_accounts',
					array(
						array(
							'account_name'   => sanitize_text_field( (string) ( isset( $bacs['name'] ) ? $bacs['name'] : '' ) ),
							'account_number' => '',
							'bank_name'      => '',
							'sort_code'      => '',
							'iban'           => $iban,
							'bic'            => strtoupper( sanitize_text_field( (string) ( isset( $bacs['bic'] ) ? $bacs['bic'] : '' ) ) ),
						),
					)
				);
			}
			$done[] = 'bacs';
		}
		if ( ! empty( $in['cod'] ) ) {
			$settings            = (array) get_option( 'woocommerce_cod_settings', array() );
			$settings['enabled'] = 'yes';
			update_option( 'woocommerce_cod_settings', $settings );
			$done[] = 'cod';
		}

		// One shipping zone for the country: flat rate + free shipping from a limit.
		$ship = isset( $in['shipping'] ) ? (array) $in['shipping'] : array();
		if ( ! empty( $ship['on'] ) && class_exists( 'WC_Shipping_Zone' ) ) {
			$backup = get_option( self::BACKUP );
			if ( ! empty( $backup['zone'] ) ) {
				$old = new WC_Shipping_Zone( (int) $backup['zone'] );
				if ( $old->get_id() ) {
					$old->delete();
				}
			}
			$zone = new WC_Shipping_Zone();
			$zone->set_zone_name( $countries[ $country ] );
			$zone->add_location( $country, 'country' );
			$zone->save();
			$cost = max( 0, (float) str_replace( ',', '.', (string) ( isset( $ship['cost'] ) ? $ship['cost'] : 0 ) ) );
			$free = max( 0, (float) str_replace( ',', '.', (string) ( isset( $ship['free'] ) ? $ship['free'] : 0 ) ) );
			$flat = $zone->add_shipping_method( 'flat_rate' );
			update_option( 'woocommerce_flat_rate_' . $flat . '_settings', array( 'title' => __( 'Delivery', 'envo-blocks' ), 'tax_status' => 'taxable', 'cost' => wc_format_decimal( $cost ) ) );
			if ( $free > 0 ) {
				$fid = $zone->add_shipping_method( 'free_shipping' );
				// The free shipping bar of Envo Blocks (threshold "auto") reads this limit.
				update_option( 'woocommerce_free_shipping_' . $fid . '_settings', array( 'title' => __( 'Free delivery', 'envo-blocks' ), 'requires' => 'min_amount', 'min_amount' => wc_format_decimal( $free ), 'ignore_discounts' => 'no' ) );
			}
			WC_Cache_Helper::get_transient_version( 'shipping', true );
			$backup         = get_option( self::BACKUP );
			$backup['zone'] = $zone->get_id();
			update_option( self::BACKUP, $backup, false );
			$done[] = 'shipping';
		}

		// Store pages.
		if ( ! empty( $in['pages'] ) ) {
			$pages            = self::set_pages( (array) $in['pages'] );
			$backup           = get_option( self::BACKUP );
			$backup['created'] = array_values( array_unique( array_merge( isset( $backup['created'] ) ? $backup['created'] : array(), $pages['created'] ) ) );
			$backup['published'] = array_values( array_unique( array_merge( isset( $backup['published'] ) ? $backup['published'] : array(), $pages['published'] ) ) );
			foreach ( $backup['created'] as $id ) {
				$backup['created_sig'][ $id ] = get_post_field( 'post_modified_gmt', $id );
			}
			update_option( self::BACKUP, $backup, false );
			$done[] = 'pages';
		}

		// Sample products.
		if ( ! empty( $in['samples'] ) && ! self::samples() ) {
			$done['samples'] = self::make_samples();
		}
		// The values the wizard set: a different value later was changed by hand (the undo warns about it).
		$backup            = get_option( self::BACKUP );
		$backup['applied'] = array();
		foreach ( self::OPTIONS as $option ) {
			$backup['applied'][ $option ] = get_option( $option, null );
		}
		if ( ! empty( $backup['zone'] ) ) {
			$backup['zone_sig'] = self::zone_sig( new WC_Shipping_Zone( (int) $backup['zone'] ) );
		}
		update_option( self::BACKUP, $backup, false );
		return $done;
	}

	/**
	 * A few sample products: clearly named, in a "Sample products" category, without a picture (WooCommerce shows
	 * its placeholder image).
	 *
	 * @return int Count.
	 */
	private static function make_samples() {
		$cat = term_exists( 'envo-sample-products', 'product_cat' );
		if ( ! $cat ) {
			$cat = wp_insert_term( __( 'Sample products', 'envo-blocks' ), 'product_cat', array( 'slug' => 'envo-sample-products' ) );
		}
		$cat_id = is_array( $cat ) ? (int) $cat['term_id'] : 0;
		$list   = array(
			array( __( 'Sample product – Classic', 'envo-blocks' ), '19.90', '' ),
			array( __( 'Sample product – Bestseller', 'envo-blocks' ), '34.90', '29.90' ),
			array( __( 'Sample product – Premium', 'envo-blocks' ), '89.00', '' ),
			array( __( 'Sample product – New', 'envo-blocks' ), '24.50', '' ),
			array( __( 'Sample product – On sale', 'envo-blocks' ), '59.00', '44.00' ),
			array( __( 'Sample product – Gift', 'envo-blocks' ), '12.00', '' ),
		);
		$n = 0;
		foreach ( $list as $row ) {
			$p = new WC_Product_Simple();
			$p->set_name( $row[0] );
			$p->set_regular_price( $row[1] );
			if ( '' !== $row[2] ) {
				$p->set_sale_price( $row[2] );
			}
			$p->set_short_description( __( 'This is a sample product made by the store wizard. Replace it with your own product, or delete all unedited samples with one click in Envo Blocks › Store in 5 minutes.', 'envo-blocks' ) );
			$p->set_category_ids( $cat_id ? array( $cat_id ) : array() );
			$p->set_status( 'publish' );
			$id = $p->save();
			if ( $id ) {
				// The modification time right now: a later edit changes it, and the product is no longer "untouched".
				update_post_meta( $id, self::SAMPLE_META, get_post_field( 'post_modified_gmt', $id ) );
				$n++;
			}
		}
		return $n;
	}

	/**
	 * Sample products (any status but trash).
	 *
	 * @return int[]
	 */
	public static function samples() {
		return get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => self::SAMPLE_META ) );
	}

	/**
	 * Whether a sample product was not edited since the wizard made it.
	 *
	 * @param int $id Product.
	 * @return bool
	 */
	public static function untouched( $id ) {
		return get_post_field( 'post_modified_gmt', $id ) === get_post_meta( $id, self::SAMPLE_META, true );
	}

	/**
	 * Moves the unedited sample products to the trash; edited ones stay.
	 *
	 * @return array deleted, kept.
	 */
	public static function delete_samples() {
		$deleted = 0;
		$kept    = 0;
		foreach ( self::samples() as $id ) {
			if ( self::untouched( $id ) && wp_trash_post( $id ) ) {
				$deleted++;
			} else {
				$kept++;
			}
		}
		$cat = get_term_by( 'slug', 'envo-sample-products', 'product_cat' );
		if ( $cat && ! $kept ) {
			wp_delete_term( $cat->term_id, 'product_cat' );
		}
		return array( 'deleted' => $deleted, 'kept' => $kept );
	}

	/**
	 * Undo of the step: the settings back, the zone removed, the sample products to the trash.
	 *
	 * @return array
	 */
	public static function undo() {
		$backup = get_option( self::BACKUP );
		$done   = array( 'samples' => 0, 'kept' => 0 );
		foreach ( self::samples() as $id ) {
			if ( self::untouched( $id ) ) {
				$done['samples'] += wp_trash_post( $id ) ? 1 : 0;
			} else {
				// Edited: the customer's product now – no longer a sample.
				delete_post_meta( $id, self::SAMPLE_META );
				$done['kept']++;
			}
		}
		$cat = get_term_by( 'slug', 'envo-sample-products', 'product_cat' );
		if ( $cat && ! $done['kept'] ) {
			wp_delete_term( $cat->term_id, 'product_cat' );
		}
		if ( is_array( $backup ) ) {
			foreach ( $backup['options'] as $option => $value ) {
				if ( null === $value ) {
					delete_option( $option );
				} else {
					update_option( $option, $value );
				}
			}
			if ( ! empty( $backup['zone'] ) && class_exists( 'WC_Shipping_Zone' ) ) {
				$zone = new WC_Shipping_Zone( (int) $backup['zone'] );
				if ( $zone->get_id() ) {
					$zone->delete();
				}
			}
			foreach ( isset( $backup['created'] ) ? $backup['created'] : array() as $id ) {
				if ( get_post( $id ) ) {
					wp_trash_post( $id );
				}
			}
			foreach ( isset( $backup['published'] ) ? $backup['published'] : array() as $id ) {
				if ( 'publish' === get_post_status( $id ) ) {
					wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
				}
			}
		}
		delete_option( self::BACKUP );
		return $done;
	}

	/**
	 * Names of the settings in the undo list.
	 *
	 * @return array option => label.
	 */
	private static function labels() {
		return array(
			'woocommerce_default_country'    => __( 'Store country', 'envo-blocks' ),
			'woocommerce_currency'           => __( 'Currency', 'envo-blocks' ),
			'woocommerce_currency_pos'       => __( 'Price format', 'envo-blocks' ),
			'woocommerce_price_thousand_sep' => __( 'Price format', 'envo-blocks' ),
			'woocommerce_price_decimal_sep'  => __( 'Price format', 'envo-blocks' ),
			'woocommerce_price_num_decimals' => __( 'Price format', 'envo-blocks' ),
			'woocommerce_bacs_settings'      => __( 'Bank transfer', 'envo-blocks' ),
			'woocommerce_bacs_accounts'      => __( 'Bank account (IBAN)', 'envo-blocks' ),
			'woocommerce_cod_settings'       => __( 'Cash on delivery', 'envo-blocks' ),
			'woocommerce_store_address'      => __( 'Store address', 'envo-blocks' ),
			'woocommerce_store_address_2'    => __( 'Store address', 'envo-blocks' ),
			'woocommerce_store_city'         => __( 'Store address', 'envo-blocks' ),
			'woocommerce_store_postcode'     => __( 'Store address', 'envo-blocks' ),
			'woocommerce_shop_page_id'           => __( 'Store pages', 'envo-blocks' ),
			'woocommerce_cart_page_id'           => __( 'Store pages', 'envo-blocks' ),
			'woocommerce_checkout_page_id'       => __( 'Store pages', 'envo-blocks' ),
			'woocommerce_myaccount_page_id'      => __( 'Store pages', 'envo-blocks' ),
			'woocommerce_terms_page_id'          => __( 'Store pages', 'envo-blocks' ),
			'woocommerce_refund_returns_page_id' => __( 'Store pages', 'envo-blocks' ),
			'wp_page_for_privacy_policy'         => __( 'Store pages', 'envo-blocks' ),
		);
	}

	/**
	 * What the undo of this step will do, with warnings for settings changed by hand after the wizard.
	 *
	 * @return array items, warnings.
	 */
	public static function undo_preview() {
		$backup = get_option( self::BACKUP );
		$items  = array();
		$warn   = array();
		if ( is_array( $backup ) ) {
			$back    = array();
			$changed = array();
			foreach ( $backup['options'] as $option => $before ) {
				$now = get_option( $option, null );
				if ( self::same( $now, $before ) ) {
					continue;
				}
				$back[ self::labels()[ $option ] ] = true;
				// Changed by hand after the wizard: not what the wizard set.
				if ( isset( $backup['applied'] ) && array_key_exists( $option, $backup['applied'] ) && ! self::same( $now, $backup['applied'][ $option ] ) ) {
					$changed[ self::labels()[ $option ] ] = true;
				}
			}
			if ( $back ) {
				/* translators: %s: list of settings */
				$items[] = sprintf( __( 'WooCommerce settings go back to how they were: %s.', 'envo-blocks' ), implode( ', ', array_keys( $back ) ) );
			}
			if ( $changed ) {
				/* translators: %s: list of settings */
				$warn[] = sprintf( __( 'You changed these WooCommerce settings after the wizard – your changes are replaced: %s.', 'envo-blocks' ), implode( ', ', array_keys( $changed ) ) );
			}
			$created = array_filter( isset( $backup['created'] ) ? $backup['created'] : array(), 'get_post' );
			if ( $created ) {
				/* translators: %s: page titles */
				$items[] = sprintf( __( 'The pages the wizard made go to the trash: %s.', 'envo-blocks' ), implode( ', ', array_map( 'get_the_title', $created ) ) );
				$edited = array_filter(
					$created,
					function ( $id ) use ( $backup ) {
						return isset( $backup['created_sig'][ $id ] ) && get_post_field( 'post_modified_gmt', $id ) !== $backup['created_sig'][ $id ];
					}
				);
				if ( $edited ) {
					/* translators: %s: page titles */
					$warn[] = sprintf( __( 'You edited these pages after the wizard – they go to the trash anyway (you can restore them there): %s.', 'envo-blocks' ), implode( ', ', array_map( 'get_the_title', $edited ) ) );
				}
			}
			$published = array_filter(
				isset( $backup['published'] ) ? $backup['published'] : array(),
				function ( $id ) {
					return 'publish' === get_post_status( $id );
				}
			);
			if ( $published ) {
				/* translators: %s: page titles */
				$items[] = sprintf( __( 'Pages the wizard published become drafts again: %s.', 'envo-blocks' ), implode( ', ', array_map( 'get_the_title', $published ) ) );
			}
			if ( ! empty( $backup['zone'] ) && class_exists( 'WC_Shipping_Zone' ) ) {
				$zone = new WC_Shipping_Zone( (int) $backup['zone'] );
				if ( $zone->get_id() ) {
					/* translators: %s: zone name */
					$items[] = sprintf( __( 'The delivery zone “%s” is deleted.', 'envo-blocks' ), $zone->get_zone_name() );
					if ( isset( $backup['zone_sig'] ) && self::zone_sig( $zone ) !== $backup['zone_sig'] ) {
						/* translators: %s: zone name */
						$warn[] = sprintf( __( 'You changed the delivery zone “%s” after the wizard – it is deleted anyway.', 'envo-blocks' ), $zone->get_zone_name() );
					}
				}
			}
		}
		$samples   = self::samples();
		$untouched = array_filter( $samples, array( __CLASS__, 'untouched' ) );
		if ( $untouched ) {
			/* translators: %d: number of products */
			$items[] = sprintf( _n( '%d unedited sample product goes to the trash.', '%d unedited sample products go to the trash.', count( $untouched ), 'envo-blocks' ), count( $untouched ) );
		}
		if ( count( $samples ) > count( $untouched ) ) {
			/* translators: %d: number of products */
			$items[] = sprintf( _n( '%d sample product you edited stays as your product.', '%d sample products you edited stay as your products.', count( $samples ) - count( $untouched ), 'envo-blocks' ), count( $samples ) - count( $untouched ) );
		}
		return array( 'items' => $items, 'warnings' => $warn );
	}

	/**
	 * Whether two option values are the same: an option saved as a number is read back as a string in the next request.
	 *
	 * @param mixed $a Value.
	 * @param mixed $b Value.
	 * @return bool
	 */
	private static function same( $a, $b ) {
		$norm = function ( $v ) use ( &$norm ) {
			return is_array( $v ) ? array_map( $norm, $v ) : ( is_bool( $v ) || null === $v ? $v : (string) $v );
		};
		return $norm( $a ) === $norm( $b );
	}

	/**
	 * Fingerprint of a shipping zone (locations + methods + their settings), to see a later change by hand.
	 *
	 * @param WC_Shipping_Zone $zone Zone.
	 * @return string
	 */
	private static function zone_sig( $zone ) {
		$data = array( $zone->get_zone_name(), $zone->get_zone_locations() );
		foreach ( $zone->get_shipping_methods() as $m ) {
			$data[] = array( $m->id, $m->is_enabled(), get_option( $m->get_instance_option_key() ) );
		}
		return md5( wp_json_encode( $data ) );
	}
}
