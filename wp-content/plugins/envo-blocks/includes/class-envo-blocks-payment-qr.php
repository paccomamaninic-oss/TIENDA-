<?php
/**
 * Payment QR code for "Direct bank transfer" (WooCommerce bacs) orders: QR Platba / SPD (Czech Banking
 * Association, CZK) and EPC QR / SEPA Credit Transfer ("GiroCode", EUR). The customer scans it in the banking app
 * and gets the account, amount, currency, variable symbol and message filled in.
 *
 * - Data: the bank accounts of WooCommerce > Settings > Payments > Direct bank transfer, the order total and number.
 * - Settings: Envo Blocks > Payment QR Code (format, account, message, QR code in e-mails). WooCommerce shows the
 *   Direct bank transfer settings in its own React screen, which has no room for the fields of other plugins.
 * - Shown by the Payment QR Code block (order confirmation) and in the customer e-mails of bacs orders (PNG
 *   embedded in the message). The QR code is made on the site (Envo_Blocks_QR): no payment data leaves the server.
 * - Not a payment gateway: it does not confirm payments.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Payment QR code.
 */
final class Envo_Blocks_Payment_QR {

	const CID    = 'envo-blocks-payment-qr';
	const OPTION = 'envo_blocks_payment_qr';
	const PAGE   = 'envo-blocks-payment-qr';

	/**
	 * PNG image waiting to be embedded in the e-mail being sent.
	 *
	 * @var string
	 */
	private static $image = '';

	/**
	 * Hooks (with WooCommerce).
	 */
	public static function init() {
		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_envo_blocks_payment_qr', array( __CLASS__, 'save' ) );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'email' ), 15, 4 );
		add_action( 'phpmailer_init', array( __CLASS__, 'embed' ) );
		add_action( 'wp_mail_succeeded', array( __CLASS__, 'cleanup' ) );
		add_action( 'wp_mail_failed', array( __CLASS__, 'cleanup' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Settings (Envo Blocks > Payment QR Code)                           */
	/* ------------------------------------------------------------------ */

	/**
	 * Settings with their defaults.
	 *
	 * @return array array( email => bool, format => auto|spd|epc, account => key, message => template ).
	 */
	public static function settings() {
		$raw = get_option( self::OPTION, array() );
		$raw = is_array( $raw ) ? $raw : array();
		return array(
			'email'   => ! isset( $raw['email'] ) || ! empty( $raw['email'] ),
			'format'  => isset( $raw['format'] ) && in_array( $raw['format'], array( 'auto', 'spd', 'epc' ), true ) ? $raw['format'] : 'auto',
			'account' => isset( $raw['account'] ) ? (string) $raw['account'] : '',
			'message' => isset( $raw['message'] ) && '' !== trim( (string) $raw['message'] ) ? (string) $raw['message'] : 'Order {order_number}',
		);
	}

	/**
	 * One setting.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public static function setting( $key ) {
		$settings = self::settings();
		return $settings[ $key ];
	}

	/**
	 * Envo Blocks > Payment QR Code.
	 *
	 * @param array $pages Pages of the Envo Blocks menu.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Payment QR Code', 'envo-blocks' ),
			'title'      => __( 'Payment QR Code', 'envo-blocks' ),
			'callback'   => array( __CLASS__, 'render' ),
			'capability' => 'manage_woocommerce',
		);
		return $pages;
	}

	/**
	 * Styles of the page (the cards of the Checkout Fields page).
	 *
	 * @param string $hook Admin page.
	 */
	public static function assets( $hook ) {
		if ( false !== strpos( (string) $hook, self::PAGE ) ) {
			wp_enqueue_style( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/css/checkout-fields.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/checkout-fields.css' ) );
		}
	}

	/**
	 * Saves the form (admin-post.php).
	 */
	public static function save() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_payment_qr' );
		$format  = isset( $_POST['format'] ) ? sanitize_key( wp_unslash( $_POST['format'] ) ) : 'auto';
		$account = isset( $_POST['account'] ) ? sanitize_key( wp_unslash( $_POST['account'] ) ) : '';
		update_option(
			self::OPTION,
			array(
				'email'   => ! empty( $_POST['email'] ),
				'format'  => in_array( $format, array( 'auto', 'spd', 'epc' ), true ) ? $format : 'auto',
				'account' => array_key_exists( $account, self::account_options() ) ? $account : '',
				'message' => isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '',
			),
			false
		);
		wp_safe_redirect( add_query_arg( 'eb-pqr', 'saved', admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * The page: settings, the accounts and a sample code.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$settings = self::settings();
		$accounts = self::account_options();
		$bacs     = get_option( 'woocommerce_bacs_settings', array() );
		$enabled  = is_array( $bacs ) && isset( $bacs['enabled'] ) && 'yes' === $bacs['enabled'];
		$link     = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bacs' );
		$sample   = self::sample();
		$qr       = $sample ? self::qr( $sample ) : null;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show after a redirect.
		$saved = isset( $_GET['eb-pqr'] ) && 'saved' === sanitize_key( wp_unslash( $_GET['eb-pqr'] ) );
		?>
		<div class="eb-cf eb-pqr">
			<?php if ( $saved ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The settings were saved.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>
			<?php if ( ! $enabled ) : ?>
				<div class="notice notice-warning inline"><p>
					<?php esc_html_e( 'Direct bank transfer is not enabled: the QR code shows only for orders paid this way.', 'envo-blocks' ); ?>
					<a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Payment settings', 'envo-blocks' ); ?></a>
				</p></div>
			<?php endif; ?>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Payment QR code', 'envo-blocks' ); ?></h2>
				<p class="eb-cf__intro"><?php esc_html_e( 'A QR code for the banking app of the customer, for orders paid by direct bank transfer that wait for the payment: QR Platba (SPD) for CZK and EPC QR (SEPA, "GiroCode") for EUR. The customer scans it and gets the account, amount and reference filled in. Add the Payment QR Code block to the Order Confirmation template; the customer e-mails get the code too. It does not confirm payments.', 'envo-blocks' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="envo_blocks_payment_qr">
					<?php wp_nonce_field( 'envo_blocks_payment_qr' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'QR code in e-mails', 'envo-blocks' ); ?></th>
							<td><label><input type="checkbox" name="email" value="1" <?php checked( $settings['email'] ); ?>> <?php esc_html_e( 'Add the code to the customer e-mails of orders waiting for the bank transfer', 'envo-blocks' ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-pqr-format"><?php esc_html_e( 'QR code format', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-pqr-format" name="format">
									<option value="auto" <?php selected( $settings['format'], 'auto' ); ?>><?php esc_html_e( 'Automatic: QR Platba for CZK, EPC QR for EUR', 'envo-blocks' ); ?></option>
									<option value="spd" <?php selected( $settings['format'], 'spd' ); ?>><?php esc_html_e( 'Only QR Platba (CZK)', 'envo-blocks' ); ?></option>
									<option value="epc" <?php selected( $settings['format'], 'epc' ); ?>><?php esc_html_e( 'Only EPC QR (EUR)', 'envo-blocks' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Orders in other currencies get no code.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-pqr-account"><?php esc_html_e( 'Account', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-pqr-account" name="account">
									<option value=""><?php esc_html_e( 'The first account with an IBAN', 'envo-blocks' ); ?></option>
									<?php foreach ( $accounts as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['account'], (string) $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php if ( $accounts ) : ?>
										<?php esc_html_e( 'The accounts with a valid IBAN from the Direct bank transfer settings of WooCommerce.', 'envo-blocks' ); ?>
									<?php else : ?>
										<strong><?php esc_html_e( 'No account with a valid IBAN yet: add the IBAN (and BIC) of your account to the Direct bank transfer settings of WooCommerce.', 'envo-blocks' ); ?></strong>
									<?php endif; ?>
									<a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Bank accounts', 'envo-blocks' ); ?></a>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-pqr-message"><?php esc_html_e( 'Payment message', 'envo-blocks' ); ?></label></th>
							<td>
								<input type="text" id="eb-pqr-message" class="regular-text" name="message" value="<?php echo esc_attr( $settings['message'] ); ?>">
								<p class="description"><?php esc_html_e( 'Placeholders: {order_number}, {order_id}, {site_title}. The digits of the order number are also the variable symbol (QR Platba).', 'envo-blocks' ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save changes', 'envo-blocks' ) ); ?>
				</form>
			</section>

			<?php if ( $qr && $accounts ) : ?>
				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'Sample', 'envo-blocks' ); ?></h2>
					<p class="eb-cf__intro"><?php esc_html_e( 'The code of a sample order with these settings. Try it with your banking app, without sending the payment.', 'envo-blocks' ); ?></p>
					<div class="eb-pqr__sample">
						<div class="eb-pqr__code"><?php echo wp_kses( $qr->svg( 4, __( 'Payment QR code', 'envo-blocks' ) ), Envo_Blocks_Blocks::allowed_html() ); ?></div>
						<table class="widefat striped eb-pqr__details">
							<?php foreach ( self::details( $sample ) as $row ) : ?>
								<tr><th><?php echo esc_html( $row[0] ); ?></th><td><?php echo esc_html( $row[1] ); ?></td></tr>
							<?php endforeach; ?>
						</table>
					</div>
				</section>
			<?php endif; ?>
		</div>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/* Accounts and payload                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Bank accounts of the bacs gateway that have a valid IBAN.
	 *
	 * @return array List of array( name, iban, bic ), keys as in the WooCommerce list.
	 */
	public static function accounts() {
		$out = array();
		foreach ( (array) get_option( 'woocommerce_bacs_accounts', array() ) as $i => $account ) {
			if ( ! is_array( $account ) ) {
				continue;
			}
			$iban = strtoupper( preg_replace( '/\s+/', '', isset( $account['iban'] ) ? (string) $account['iban'] : '' ) );
			if ( ! self::iban_valid( $iban ) ) {
				continue;
			}
			$bic       = strtoupper( preg_replace( '/\s+/', '', isset( $account['bic'] ) ? (string) $account['bic'] : '' ) );
			$out[ $i ] = array(
				'name' => isset( $account['account_name'] ) ? trim( wp_strip_all_tags( (string) $account['account_name'] ) ) : '',
				'iban' => $iban,
				'bic'  => preg_match( '/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $bic ) ? $bic : '',
			);
		}
		return $out;
	}

	/**
	 * Accounts for a select: key => "name IBAN".
	 *
	 * @return array
	 */
	public static function account_options() {
		$options = array();
		foreach ( self::accounts() as $i => $account ) {
			$options[ (string) $i ] = trim( $account['name'] . ' ' . self::format_iban( $account['iban'] ) );
		}
		return $options;
	}

	/**
	 * IBAN check (country, length, mod 97).
	 *
	 * @param string $iban IBAN without spaces.
	 * @return bool
	 */
	public static function iban_valid( $iban ) {
		if ( ! preg_match( '/^[A-Z]{2}[0-9]{2}[A-Z0-9]{11,30}$/', $iban ) ) {
			return false;
		}
		$moved = substr( $iban, 4 ) . substr( $iban, 0, 4 );
		$rest  = 0;
		foreach ( str_split( $moved ) as $char ) {
			$digits = ctype_alpha( $char ) ? (string) ( ord( $char ) - 55 ) : $char;
			foreach ( str_split( $digits ) as $digit ) {
				$rest = ( $rest * 10 + (int) $digit ) % 97;
			}
		}
		return 1 === $rest;
	}

	/**
	 * IBAN in groups of four for reading.
	 *
	 * @param string $iban IBAN.
	 * @return string
	 */
	public static function format_iban( $iban ) {
		return trim( chunk_split( $iban, 4, ' ' ) );
	}

	/**
	 * Payment data and QR text of an order (or of sample values), or null when no QR code can be made.
	 *
	 * @param array $data array( amount, currency, number, id ) of the order.
	 * @param array $args Optional array( format => auto|spd|epc, account => key, message => template ); empty values
	 *                    use the gateway settings. 'accounts' replaces the shop accounts (editor sample).
	 * @return array|null array( format, text, account, amount, currency, vs, message ).
	 */
	public static function payload( array $data, array $args = array() ) {
		$format   = ! empty( $args['format'] ) && in_array( $args['format'], array( 'auto', 'spd', 'epc' ), true ) ? $args['format'] : self::setting( 'format' );
		$currency = strtoupper( (string) $data['currency'] );
		$type     = 'CZK' === $currency ? 'spd' : ( 'EUR' === $currency ? 'epc' : '' );
		if ( '' === $type || ( 'auto' !== $format && $format !== $type ) ) {
			return null; // Other currencies, or a format the shop does not want.
		}

		$accounts = isset( $args['accounts'] ) ? (array) $args['accounts'] : self::accounts();
		$key      = isset( $args['account'] ) && '' !== (string) $args['account'] ? (string) $args['account'] : self::setting( 'account' );
		$account  = '' !== $key && isset( $accounts[ $key ] ) ? $accounts[ $key ] : reset( $accounts );
		if ( ! $account ) {
			return null;
		}

		$amount = round( (float) $data['amount'], 2 );
		if ( $amount < 0.01 || $amount > ( 'spd' === $type ? 9999999.99 : 999999999.99 ) ) {
			return null;
		}
		$template = isset( $args['message'] ) && '' !== trim( (string) $args['message'] ) ? (string) $args['message'] : self::setting( 'message' );
		$message  = strtr(
			$template,
			array(
				'{order_number}' => (string) $data['number'],
				'{order_id}'     => (string) $data['id'],
				'{site_title}'   => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			)
		);
		// Variable symbol: the digits of the order number, at most 10 (the last ones).
		$vs = substr( preg_replace( '/\D+/', '', (string) $data['number'] ), -10 );

		$text = 'spd' === $type
			? self::spd( $account, $amount, $currency, $vs, $message )
			: self::epc( $account, $amount, $message );

		return array(
			'format'   => $type,
			'text'     => $text,
			'account'  => $account,
			'amount'   => $amount,
			'currency' => $currency,
			'vs'       => 'spd' === $type ? $vs : '',
			'message'  => 'spd' === $type ? self::spd_value( $message, 60 ) : self::sepa_text( $message, 140 ),
		);
	}

	/**
	 * Payload of a WooCommerce order, when it waits for a bank transfer.
	 *
	 * @param WC_Order $order Order.
	 * @param array    $args  See payload().
	 * @return array|null
	 */
	public static function for_order( $order, array $args = array() ) {
		if ( ! $order instanceof WC_Order || 'bacs' !== $order->get_payment_method() || ! $order->has_status( array( 'on-hold', 'pending' ) ) ) {
			return null;
		}
		return self::payload(
			array(
				'amount'   => $order->get_total(),
				'currency' => $order->get_currency(),
				'number'   => $order->get_order_number(),
				'id'       => $order->get_id(),
			),
			$args
		);
	}

	/**
	 * The order of the order received page (as the WooCommerce order confirmation blocks find it), when the visitor
	 * may see it: a valid order key, and the customer's own order or a guest order still in the session, within
	 * the grace period or after the e-mail check of WooCommerce.
	 *
	 * @return WC_Order|null
	 */
	public static function current_order() {
		$order = function_exists( 'wc_get_order' ) ? wc_get_order( absint( get_query_var( 'order-received' ) ) ) : null;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- The order key is the check.
		$key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
		if ( ! $order instanceof WC_Order || '' === $key || ! $order->key_is_valid( $key ) ) {
			return null;
		}

		if ( 0 < $order->get_user_id() ) {
			/** This filter is documented in WooCommerce (order confirmation blocks). */
			$verify = apply_filters( 'woocommerce_order_received_verify_known_shoppers', true );
			return ! $verify || $order->get_user_id() === get_current_user_id() ? $order : null;
		}

		$session = WC()->session;
		if ( $session instanceof WC_Session && $order->get_id() === (int) $session->get( 'store_api_draft_order' ) ) {
			return $order;
		}
		/** This filter is documented in WooCommerce (order confirmation blocks). */
		$grace   = (int) apply_filters( 'woocommerce_order_email_verification_grace_period', 10 * MINUTE_IN_SECONDS, $order, 'order-received' );
		$created = $order->get_date_created();
		if ( $created && time() - $created->getTimestamp() <= $grace ) {
			return $order;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked below.
		if ( isset( $_POST['email'], $_POST['_wpnonce'] ) ) {
			$nonce = sanitize_key( wp_unslash( $_POST['_wpnonce'] ) );
			if ( ( wp_verify_nonce( $nonce, 'wc_verify_email' ) || wp_verify_nonce( $nonce, 'wc_create_account' ) ) && $order->get_billing_email() && sanitize_email( wp_unslash( $_POST['email'] ) ) === $order->get_billing_email() ) {
				return $order;
			}
		}
		// phpcs:enable
		/** This filter is documented in WooCommerce (order confirmation blocks). */
		return apply_filters( 'woocommerce_order_email_verification_required', true, $order, 'order-received' ) ? null : $order;
	}

	/**
	 * Sample payload for the editor preview: the shop currency (CZK or EUR, else CZK) and account, or a sample account.
	 *
	 * @param array $args See payload().
	 * @return array|null
	 */
	public static function sample( array $args = array() ) {
		$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'CZK';
		$format   = isset( $args['format'] ) ? $args['format'] : '';
		if ( 'spd' === $format || 'epc' === $format ) {
			$currency = 'spd' === $format ? 'CZK' : 'EUR';
		} elseif ( 'CZK' !== $currency && 'EUR' !== $currency ) {
			$currency = 'CZK';
		}
		if ( ! self::accounts() ) {
			$args['accounts'] = array(
				array(
					'name' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
					'iban' => 'CZ6508000000192000145399',
					'bic'  => 'GIBACZPX',
				),
			);
			$args['account']  = '';
		}
		return self::payload(
			array(
				'amount'   => 'CZK' === $currency ? 1290 : 54.9,
				'currency' => $currency,
				'number'   => '1234',
				'id'       => 1234,
			),
			$args
		);
	}

	/**
	 * QR Platba / Short Payment Descriptor 1.0 (Czech Banking Association): "SPD*1.0*ACC:…*AM:…*CC:…*…".
	 *
	 * @param array  $account  Account.
	 * @param float  $amount   Amount.
	 * @param string $currency Currency.
	 * @param string $vs       Variable symbol (digits).
	 * @param string $message  Message.
	 * @return string
	 */
	private static function spd( array $account, $amount, $currency, $vs, $message ) {
		$parts = array( 'SPD', '1.0', 'ACC:' . $account['iban'] . ( $account['bic'] ? '+' . $account['bic'] : '' ) );
		$parts[] = 'AM:' . number_format( $amount, 2, '.', '' );
		$parts[] = 'CC:' . $currency;
		if ( '' !== $vs ) {
			$parts[] = 'X-VS:' . $vs;
		}
		$name = self::spd_value( $account['name'], 35 );
		if ( '' !== $name ) {
			$parts[] = 'RN:' . $name;
		}
		$message = self::spd_value( $message, 60 );
		if ( '' !== $message ) {
			$parts[] = 'MSG:' . $message;
		}
		return implode( '*', $parts );
	}

	/**
	 * A text value of SPD: without diacritics, in capitals (the compact QR alphanumeric set), limited length.
	 *
	 * @param string $text Text.
	 * @param int    $max  Maximum length.
	 * @return string
	 */
	public static function spd_value( $text, $max ) {
		$text = strtoupper( remove_accents( wp_strip_all_tags( (string) $text ) ) );
		$text = preg_replace( '/[^A-Z0-9 $+\-.\/:]/', ' ', $text ); // No "*" (separator) and no "%" (escape).
		return trim( substr( (string) preg_replace( '/\s+/', ' ', $text ), 0, $max ) );
	}

	/**
	 * EPC QR code (EPC069-12 version 002, SEPA Credit Transfer, UTF-8, at most 331 bytes).
	 *
	 * @param array  $account Account.
	 * @param float  $amount  Amount in EUR.
	 * @param string $message Remittance text.
	 * @return string
	 */
	private static function epc( array $account, $amount, $message ) {
		$name  = self::sepa_text( '' !== $account['name'] ? $account['name'] : get_bloginfo( 'name' ), 70 );
		$lines = array(
			'BCD',
			'002',
			'1',
			'SCT',
			$account['bic'],
			'' !== $name ? $name : 'Merchant',
			$account['iban'],
			'EUR' . number_format( $amount, 2, '.', '' ),
			'',
			'',
		);
		$text  = self::sepa_text( $message, 140 );
		$room  = 331 - strlen( implode( "
", $lines ) ) - 1;
		while ( '' !== $text && strlen( $text ) > $room ) {
			$text = rtrim( self::cut( $text, self::length( $text ) - 1 ) );
		}
		$lines[] = $text;
		return implode( "
", $lines );
	}

	/**
	 * A text value of EPC QR (UTF-8): one line without control characters, limited number of characters.
	 *
	 * @param string $text Text.
	 * @param int    $max  Maximum characters.
	 * @return string
	 */
	public static function sepa_text( $text, $max ) {
		$text = wp_check_invalid_utf8( wp_strip_all_tags( (string) $text ), true );
		$text = trim( (string) preg_replace( '/[\x00-\x1F\x7F\s]+/u', ' ', $text ) );
		return trim( self::cut( $text, $max ) );
	}

	/**
	 * Characters of a UTF-8 text.
	 *
	 * @param string $text Text.
	 * @return int
	 */
	private static function length( $text ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( $text, 'UTF-8' ) : (int) preg_match_all( '/./us', $text );
	}

	/**
	 * The first characters of a UTF-8 text.
	 *
	 * @param string $text Text.
	 * @param int    $max  Characters.
	 * @return string
	 */
	private static function cut( $text, $max ) {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $text, 0, $max, 'UTF-8' );
		}
		return preg_match( '/^.{0,' . (int) $max . '}/us', $text, $match ) ? $match[0] : '';
	}

	/**
	 * QR code of a payload (error correction M, as the EPC guidelines ask).
	 *
	 * @param array $payload Payload.
	 * @return Envo_Blocks_QR|null
	 */
	public static function qr( array $payload ) {
		return Envo_Blocks_QR::encode( $payload['text'], 'M' );
	}

	/**
	 * Amount for reading (with the currency of the order).
	 *
	 * @param array $payload Payload.
	 * @return string Plain text.
	 */
	public static function amount_text( array $payload ) {
		if ( function_exists( 'wc_price' ) ) {
			return html_entity_decode( wp_strip_all_tags( wc_price( $payload['amount'], array( 'currency' => $payload['currency'] ) ) ), ENT_QUOTES, 'UTF-8' );
		}
		return number_format_i18n( $payload['amount'], 2 ) . ' ' . $payload['currency'];
	}

	/* ------------------------------------------------------------------ */
	/* E-mail                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Adds the QR code to the customer e-mails of bacs orders that wait for the payment.
	 *
	 * @param WC_Order $order         Order.
	 * @param bool     $sent_to_admin E-mail to the shop.
	 * @param bool     $plain_text    Plain text e-mail.
	 * @param WC_Email $email         E-mail.
	 */
	public static function email( $order, $sent_to_admin = false, $plain_text = false, $email = null ) {
		if ( $sent_to_admin || ! self::setting( 'email' ) ) {
			return;
		}
		$payload = self::for_order( $order );
		if ( ! $payload ) {
			return;
		}
		$details = self::details( $payload );

		if ( $plain_text ) {
			echo "\n" . esc_html__( 'Pay by bank transfer', 'envo-blocks' ) . "\n";
			foreach ( $details as $row ) {
				echo esc_html( $row[0] . ': ' . $row[1] ) . "\n";
			}
			echo "\n";
			return;
		}

		$qr  = self::qr( $payload );
		$png = $qr ? $qr->png( 5, 4 ) : '';
		if ( '' === $png ) {
			return;
		}
		$src = self::image_src( $png );
		echo '<div style="margin:0 0 32px;">';
		echo '<h2>' . esc_html__( 'Pay by QR code', 'envo-blocks' ) . '</h2>';
		echo '<p style="margin:0 0 12px;">' . esc_html__( 'Scan the code with the app of your bank: the payment details are filled in.', 'envo-blocks' ) . '</p>';
		echo '<img src="' . esc_attr( $src ) . '" width="220" height="220" alt="' . esc_attr__( 'Payment QR code', 'envo-blocks' ) . '" style="display:block;width:220px;height:220px;margin:0 0 12px;border:0;">';
		echo '<table cellspacing="0" cellpadding="4" style="border-collapse:collapse;">';
		foreach ( $details as $row ) {
			echo '<tr><th style="text-align:left;padding:4px 12px 4px 0;">' . esc_html( $row[0] ) . '</th><td style="padding:4px 0;">' . esc_html( $row[1] ) . '</td></tr>';
		}
		echo '</table></div>';
	}

	/**
	 * Where the PNG comes from: embedded in the message (cid) when it is sent, a data URI in previews.
	 *
	 * @param string $png PNG data.
	 * @return string
	 */
	private static function image_src( $png ) {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return 'data:image/png;base64,' . base64_encode( $png ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- The image itself.
		}
		self::$image = $png;
		return 'cid:' . self::CID;
	}

	/**
	 * Embeds the waiting QR image in the e-mail being sent.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $mailer Mailer.
	 */
	public static function embed( $mailer ) {
		if ( '' === self::$image || ! is_object( $mailer ) || ! method_exists( $mailer, 'addStringEmbeddedImage' ) ) {
			return;
		}
		if ( false !== strpos( (string) $mailer->Body, 'cid:' . self::CID ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer property.
			$mailer->addStringEmbeddedImage( self::$image, self::CID, 'payment-qr.png', 'base64', 'image/png' );
		}
	}

	/**
	 * Forgets the image after the e-mail.
	 */
	public static function cleanup() {
		self::$image = '';
	}

	/**
	 * Details for entering the payment by hand.
	 *
	 * @param array $payload Payload.
	 * @return array List of array( label, value ).
	 */
	public static function details( array $payload ) {
		$rows = array();
		if ( '' !== $payload['account']['name'] ) {
			$rows[] = array( __( 'Account name', 'envo-blocks' ), $payload['account']['name'] );
		}
		$rows[] = array( __( 'IBAN', 'envo-blocks' ), self::format_iban( $payload['account']['iban'] ) );
		if ( '' !== $payload['account']['bic'] ) {
			$rows[] = array( __( 'BIC / SWIFT', 'envo-blocks' ), $payload['account']['bic'] );
		}
		$rows[] = array( __( 'Amount', 'envo-blocks' ), self::amount_text( $payload ) );
		if ( '' !== $payload['vs'] ) {
			$rows[] = array( __( 'Variable symbol', 'envo-blocks' ), $payload['vs'] );
		}
		if ( '' !== $payload['message'] ) {
			$rows[] = array( __( 'Message', 'envo-blocks' ), $payload['message'] );
		}
		return $rows;
	}
}
