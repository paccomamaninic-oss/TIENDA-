<?php
/**
 * PDF invoices for WooCommerce: invoices, credit notes and packing slips.
 *
 * - Issuing: by order status (settings), by hand or in bulk. The document is a snapshot (Envo_Blocks_Invoice_Data)
 *   stored with the order; it never changes. Numbers come from atomic counters (no gaps, no duplicates).
 * - Credit notes: for refunds of invoiced orders and for "Cancel and reissue".
 * - PDF: made from the snapshot (Envo_Blocks_Invoice_PDF), stored in uploads/envo-blocks-invoices/ under a name
 *   nobody can guess, sent only through download() after a permission check.
 * - Customer: e-mail attachments, My account buttons, the Invoice Download block.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Invoices.
 */
final class Envo_Blocks_Invoices {

	const META         = '_envo_blocks_invoice';
	const META_CREDITS = '_envo_blocks_credit_notes';
	const META_HISTORY = '_envo_blocks_invoice_history';
	const DIR          = 'envo-blocks-invoices';
	const QUERY        = 'envo-blocks-invoice';

	/**
	 * Hooks (with WooCommerce).
	 */
	public static function init() {
		Envo_Blocks_Invoice_Admin::init();
		add_action( 'init', array( __CLASS__, 'download' ), 20 );
		if ( ! Envo_Blocks_Invoice_Settings::raw( 'enabled' ) ) {
			return;
		}
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'status_changed' ), 20, 4 );
		add_action( 'woocommerce_order_refunded', array( __CLASS__, 'refunded' ), 20, 2 );
		add_filter( 'woocommerce_email_attachments', array( __CLASS__, 'attachments' ), 20, 4 );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( __CLASS__, 'account_actions' ), 20, 2 );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_details_links' ), 20 );
	}

	/* ------------------------------------------------------------------ */
	/* Documents of an order                                              */
	/* ------------------------------------------------------------------ */

	/**
	 * The invoice of an order (snapshot), or null.
	 *
	 * @param WC_Order $order Order.
	 * @return array|null
	 */
	public static function invoice( $order ) {
		$data = $order instanceof WC_Order ? $order->get_meta( self::META ) : null;
		return is_array( $data ) && ! empty( $data['number'] ) ? $data : null;
	}

	/**
	 * Credit notes of an order.
	 *
	 * @param WC_Order $order Order.
	 * @return array
	 */
	public static function credits( $order ) {
		$list = $order instanceof WC_Order ? $order->get_meta( self::META_CREDITS ) : array();
		return is_array( $list ) ? array_values( $list ) : array();
	}

	/**
	 * Whether the order should get an invoice now (settings: statuses, zero totals).
	 *
	 * @param WC_Order $order Order.
	 * @return bool
	 */
	public static function should_issue( WC_Order $order ) {
		$s = Envo_Blocks_Invoice_Settings::get();
		if ( ! $s['enabled'] || self::invoice( $order ) || ! in_array( $order->get_status(), $s['statuses'], true ) ) {
			return false;
		}
		return $s['zero'] || (float) $order->get_total() > 0;
	}

	/**
	 * Issues the invoice of an order (once).
	 *
	 * @param WC_Order $order Order.
	 * @return array|WP_Error The snapshot.
	 */
	public static function issue( WC_Order $order ) {
		$existing = self::invoice( $order );
		if ( $existing ) {
			return $existing;
		}
		if ( ! self::lock( $order->get_id() ) ) {
			return new WP_Error( 'envo_blocks_invoice_locked', __( 'The invoice is being issued, try again in a moment.', 'envo-blocks' ) );
		}
		// Another request may have issued it while this one waited.
		$fresh = wc_get_order( $order->get_id() );
		if ( $fresh && self::invoice( $fresh ) ) {
			self::unlock( $order->get_id() );
			return self::invoice( $fresh );
		}

		$data = Envo_Blocks_Invoice_Data::build( $order, 'invoice' );
		self::number( $data, 'invoice' );
		$data['reference'] = 'invoice' === Envo_Blocks_Invoice_Settings::get()['reference'] ? preg_replace( '/\D+/', '', $data['number'] ) : $data['order']['number'];
		$order->update_meta_data( self::META, $data );
		$order->save_meta_data();
		self::unlock( $order->get_id() );

		/* translators: %s: invoice number. */
		$order->add_order_note( sprintf( __( 'Invoice %s issued.', 'envo-blocks' ), $data['number'] ) );
		self::file( $order, 'invoice' );
		do_action( 'envo_blocks_invoice_issued', $data, $order );
		return $data;
	}

	/**
	 * Issues a credit note: for a refund, or for the whole invoice (refund null).
	 *
	 * @param WC_Order             $order  Order.
	 * @param WC_Order_Refund|null $refund Refund.
	 * @param string               $reason Reason (whole invoice).
	 * @return array|WP_Error
	 */
	public static function credit( WC_Order $order, $refund = null, $reason = '' ) {
		$invoice = self::invoice( $order );
		if ( ! $invoice ) {
			return new WP_Error( 'envo_blocks_no_invoice', __( 'The order has no invoice.', 'envo-blocks' ) );
		}
		$credits = self::credits( $order );
		foreach ( $credits as $credit ) {
			if ( $refund && isset( $credit['refund']['id'] ) && (int) $credit['refund']['id'] === $refund->get_id() ) {
				return $credit;
			}
		}
		$data             = Envo_Blocks_Invoice_Data::build( $order, 'credit', $refund );
		$data['original'] = array(
			'number' => $invoice['number'],
			'issued' => $invoice['issued'],
		);
		if ( ! $refund ) {
			$data['refund'] = array(
				'id'     => 0,
				'reason' => $reason,
			);
		}
		self::number( $data, 'credit' );
		$data['reference'] = $invoice['reference'];
		$credits[]         = $data;
		$order->update_meta_data( self::META_CREDITS, $credits );
		$order->save_meta_data();
		/* translators: %s: credit note number. */
		$order->add_order_note( sprintf( __( 'Credit note %s issued.', 'envo-blocks' ), $data['number'] ) );
		self::file( $order, 'credit-' . ( count( $credits ) - 1 ) );
		do_action( 'envo_blocks_credit_note_issued', $data, $order );
		return $data;
	}

	/**
	 * Cancels the invoice with a credit note of the whole amount and issues a new one with the current data.
	 *
	 * @param WC_Order $order  Order.
	 * @param string   $reason Reason.
	 * @return array|WP_Error The new invoice.
	 */
	public static function reissue( WC_Order $order, $reason = '' ) {
		$invoice = self::invoice( $order );
		if ( ! $invoice ) {
			return self::issue( $order );
		}
		$credit = self::credit( $order, null, $reason );
		if ( is_wp_error( $credit ) ) {
			return $credit;
		}
		$history   = $order->get_meta( self::META_HISTORY );
		$history   = is_array( $history ) ? $history : array();
		$history[] = $invoice;
		$order->update_meta_data( self::META_HISTORY, $history );
		$order->delete_meta_data( self::META );
		$order->save_meta_data();
		return self::issue( $order );
	}

	/**
	 * Gives the document its number (atomic counter of the type and period).
	 *
	 * @param array  $data Snapshot (changed).
	 * @param string $type invoice|credit.
	 */
	private static function number( array &$data, $type ) {
		$s      = Envo_Blocks_Invoice_Settings::get();
		$config = $s['numbers'][ $type ];
		$time   = (int) $data['issued'];
		$year   = (int) wp_date( 'Y', $time );
		$month  = (int) wp_date( 'n', $time );
		$fyear  = $s['year_start'] > 1 && $month < $s['year_start'] ? $year - 1 : $year;
		$period = 'never' === $config['reset'] ? 'all' : ( 'month' === $config['reset'] ? wp_date( 'Y-m', $time ) : (string) $fyear );
		$option = 'envo_blocks_inv_seq_' . $type . '_' . $period;

		global $wpdb;
		add_option( $option, '0', '', 'no' );
		// LAST_INSERT_ID( expr ) keeps the new value for this connection: read and increment in one statement.
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = LAST_INSERT_ID( GREATEST( CAST( option_value AS UNSIGNED ) + 1, %d ) ) WHERE option_name = %s", (int) $config['start'], $option ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$sequence = (int) $wpdb->get_var( 'SELECT LAST_INSERT_ID()' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		wp_cache_delete( $option, 'options' );

		$number = preg_replace_callback(
			'/\{number(?::(\d))?\}/',
			static function ( $m ) use ( $sequence ) {
				return str_pad( (string) $sequence, isset( $m[1] ) ? (int) $m[1] : 1, '0', STR_PAD_LEFT );
			},
			$config['format']
		);
		$number = strtr(
			$number,
			array(
				'{year}'         => (string) $year,
				'{year_short}'   => substr( (string) $year, -2 ),
				'{fiscal_year}'  => $fyear . '-' . substr( (string) ( $fyear + 1 ), -2 ),
				'{month}'        => str_pad( (string) $month, 2, '0', STR_PAD_LEFT ),
				'{order_number}' => $data['order']['number'],
			)
		);
		$data['number']   = $number;
		$data['sequence'] = $sequence;
	}

	/**
	 * Lock of one order (two requests must not issue two invoices).
	 *
	 * @param int $id Order id.
	 * @return bool
	 */
	private static function lock( $id ) {
		global $wpdb;
		return '1' === (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK( %s, 10 )', 'envo_blocks_invoice_' . (int) $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Releases the lock of an order.
	 *
	 * @param int $id Order id.
	 */
	private static function unlock( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK( %s )', 'envo_blocks_invoice_' . (int) $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/* ------------------------------------------------------------------ */
	/* Files                                                              */
	/* ------------------------------------------------------------------ */

	/**
	 * Snapshot of a document of an order: invoice, credit-<index>, packing (made now, no number).
	 *
	 * @param WC_Order $order Order.
	 * @param string   $doc   Document.
	 * @return array|null
	 */
	public static function document( WC_Order $order, $doc ) {
		if ( 'invoice' === $doc ) {
			return self::invoice( $order );
		}
		if ( 'packing' === $doc ) {
			return Envo_Blocks_Invoice_Data::build( $order, 'packing' );
		}
		if ( preg_match( '/^credit-(\d+)$/', $doc, $m ) ) {
			$credits = self::credits( $order );
			return isset( $credits[ (int) $m[1] ] ) ? $credits[ (int) $m[1] ] : null;
		}
		return null;
	}

	/**
	 * Path of the stored PDF of a document; makes it when it is missing. '' when the PDF cannot be made.
	 *
	 * @param WC_Order $order      Order.
	 * @param string   $doc        invoice|credit-<index>.
	 * @param bool     $regenerate Make it again from the snapshot.
	 * @return string
	 */
	public static function file( WC_Order $order, $doc, $regenerate = false ) {
		$data = self::document( $order, $doc );
		if ( ! $data || 'packing' === $doc ) {
			return '';
		}
		$files = $order->get_meta( '_envo_blocks_invoice_files' );
		$files = is_array( $files ) ? $files : array();
		$base  = self::base_dir();
		$key   = $doc . ':' . $data['number'];
		if ( ! $regenerate && isset( $files[ $key ] ) && is_file( $base . $files[ $key ] ) ) {
			return $base . $files[ $key ];
		}
		$pdf = Envo_Blocks_Invoice_PDF::pdf( $data );
		if ( is_wp_error( $pdf ) ) {
			return '';
		}
		$relative = isset( $files[ $key ] ) ? $files[ $key ] : wp_date( 'Y', (int) $data['issued'] ) . '/' . sanitize_file_name( $data['number'] ) . '-' . wp_generate_password( 16, false ) . '.pdf';
		wp_mkdir_p( dirname( $base . $relative ) );
		self::protect( $base );
		if ( false === file_put_contents( $base . $relative, $pdf ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Own protected folder.
			return '';
		}
		$files[ $key ] = $relative;
		$order->update_meta_data( '_envo_blocks_invoice_files', $files );
		$order->save_meta_data();
		return $base . $relative;
	}

	/**
	 * Folder of the PDF files (with a trailing slash).
	 *
	 * @return string
	 */
	public static function base_dir() {
		$uploads = wp_upload_dir( null, false );
		return trailingslashit( $uploads['basedir'] ) . self::DIR . '/';
	}

	/**
	 * No listing and no direct access (Apache); the names are random for servers that ignore .htaccess.
	 *
	 * @param string $dir Folder.
	 */
	public static function protect( $dir ) {
		wp_mkdir_p( $dir );
		$files = array(
			'.htaccess'  => "# Envo Blocks invoices: no direct access.\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tDeny from all\n</IfModule>\n",
			'index.php'  => "<?php\n// Silence is golden.\n",
			'index.html' => '',
		);
		foreach ( $files as $name => $content ) {
			if ( ! is_file( $dir . $name ) ) {
				file_put_contents( $dir . $name, $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Protection files.
			}
		}
	}

	/* ------------------------------------------------------------------ */
	/* Download                                                           */
	/* ------------------------------------------------------------------ */

	/**
	 * Link to a document.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $doc   Document.
	 * @param bool     $key   With the order key (guests, e-mails).
	 * @return string
	 */
	public static function url( WC_Order $order, $doc = 'invoice', $key = false ) {
		$args = array(
			self::QUERY => $order->get_id(),
			'doc'       => $doc,
		);
		if ( $key ) {
			$args['key'] = $order->get_order_key();
		}
		return add_query_arg( $args, home_url( '/' ) );
	}

	/**
	 * Whether the current visitor may open a document of the order.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $doc   Document.
	 * @return bool
	 */
	public static function can_view( WC_Order $order, $doc ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}
		if ( 'packing' === $doc ) {
			return false;
		}
		if ( $order->get_user_id() ) {
			return get_current_user_id() === $order->get_user_id();
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- The order key is the check.
		$key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
		return Envo_Blocks_Invoice_Settings::get()['guest_links'] && '' !== $key && $order->key_is_valid( $key );
	}

	/**
	 * Sends a document (?envo-blocks-invoice=<order id>&doc=…): the PDF, or for shop managers an HTML preview when
	 * the PDF library is missing.
	 */
	public static function download() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only; access checked in can_view().
		if ( ! isset( $_GET[ self::QUERY ] ) ) {
			return;
		}
		$order = wc_get_order( absint( $_GET[ self::QUERY ] ) );
		$doc   = isset( $_GET['doc'] ) ? sanitize_key( wp_unslash( $_GET['doc'] ) ) : 'invoice';
		// phpcs:enable
		if ( ! $order instanceof WC_Order || $order instanceof WC_Order_Refund || ! self::can_view( $order, $doc ) ) {
			wp_die( esc_html__( 'You cannot open this document.', 'envo-blocks' ), '', array( 'response' => 403 ) );
		}
		$data = self::document( $order, $doc );
		if ( ! $data ) {
			wp_die( esc_html__( 'The document does not exist (yet).', 'envo-blocks' ), '', array( 'response' => 404 ) );
		}
		nocache_headers();
		$name = sanitize_file_name( ( 'packing' === $doc ? $data['labels']['packing_slip'] . '-' . $data['order']['number'] : $data['number'] ) . '.pdf' );
		$pdf  = 'packing' === $doc ? Envo_Blocks_Invoice_PDF::pdf( $data ) : '';
		$file = 'packing' === $doc ? '' : self::file( $order, $doc );
		if ( '' !== $file ) {
			$pdf = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
		}
		if ( ! is_string( $pdf ) || '' === $pdf ) {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				// Without the PDF library (or when it failed): the HTML of the document, for checking.
				header( 'Content-Type: text/html; charset=utf-8' );
				echo Envo_Blocks_Invoice_PDF::html( $data, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template.
				exit;
			}
			wp_die( esc_html__( 'The PDF could not be made. Please contact the shop.', 'envo-blocks' ), '', array( 'response' => 500 ) );
		}
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: inline; filename="' . $name . '"' );
		header( 'Content-Length: ' . strlen( $pdf ) );
		header( 'X-Robots-Tag: noindex, nofollow' );
		echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- PDF data.
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* WooCommerce hooks                                                  */
	/* ------------------------------------------------------------------ */

	/**
	 * Issues the invoice when the order reaches a status of the settings.
	 *
	 * @param int      $id    Order id.
	 * @param string   $from  Old status.
	 * @param string   $to    New status.
	 * @param WC_Order $order Order.
	 */
	public static function status_changed( $id, $from, $to, $order = null ) {
		$order = $order instanceof WC_Order ? $order : wc_get_order( $id );
		if ( $order instanceof WC_Order && self::should_issue( $order ) ) {
			self::issue( $order );
		}
	}

	/**
	 * Credit note for a refund of an invoiced order.
	 *
	 * @param int $order_id  Order id.
	 * @param int $refund_id Refund id.
	 */
	public static function refunded( $order_id, $refund_id ) {
		$order  = wc_get_order( $order_id );
		$refund = wc_get_order( $refund_id );
		if ( $order instanceof WC_Order && $refund instanceof WC_Order_Refund && self::invoice( $order ) ) {
			self::credit( $order, $refund );
		}
	}

	/**
	 * PDF attachments of the customer e-mails. The status e-mails are sent before "status changed", so the invoice
	 * is issued here when it is due.
	 *
	 * @param array    $attachments Files.
	 * @param string   $email_id    E-mail id.
	 * @param mixed    $object      Order (or other object).
	 * @param WC_Email $email       E-mail.
	 * @return array
	 */
	public static function attachments( $attachments, $email_id, $object = null, $email = null ) {
		if ( ! $object instanceof WC_Order || $object instanceof WC_Order_Refund ) {
			return $attachments;
		}
		$s = Envo_Blocks_Invoice_Settings::get();
		if ( in_array( $email_id, $s['emails'], true ) ) {
			if ( ! self::invoice( $object ) && self::should_issue( $object ) ) {
				self::issue( $object );
			}
			$file = self::invoice( $object ) ? self::file( $object, 'invoice' ) : '';
		} elseif ( in_array( $email_id, array( 'customer_refunded_order', 'customer_partially_refunded_order' ), true ) && $s['credit_mail'] ) {
			// WooCommerce sends this e-mail before "order refunded": the credit note of the refund is issued here.
			$refund = is_object( $email ) && isset( $email->refund ) && $email->refund instanceof WC_Order_Refund ? $email->refund : null;
			if ( $refund && self::invoice( $object ) ) {
				self::credit( $object, $refund );
			}
			$file = '';
			foreach ( self::credits( $object ) as $i => $credit ) {
				if ( ! $refund || ( isset( $credit['refund']['id'] ) && (int) $credit['refund']['id'] === $refund->get_id() ) ) {
					$file = self::file( $object, 'credit-' . $i );
				}
			}
		} else {
			$file = '';
		}
		if ( '' !== $file ) {
			$attachments   = (array) $attachments;
			$attachments[] = $file;
		}
		return $attachments;
	}

	/**
	 * Buttons in My account > Orders.
	 *
	 * @param array    $actions Actions.
	 * @param WC_Order $order   Order.
	 * @return array
	 */
	public static function account_actions( $actions, $order ) {
		$invoice = self::invoice( $order );
		if ( $invoice ) {
			$actions['envo-blocks-invoice'] = array(
				'url'  => self::url( $order ),
				'name' => $invoice['labels']['invoice'],
			);
		}
		return $actions;
	}

	/**
	 * Links under the order details (My account > View order, order received page).
	 *
	 * @param WC_Order $order Order.
	 */
	public static function order_details_links( $order ) {
		if ( ! $order instanceof WC_Order || ! self::invoice( $order ) || ! self::can_view( $order, 'invoice' ) ) {
			return;
		}
		$links = array( '<a class="button" href="' . esc_url( self::url( $order, 'invoice', ! $order->get_user_id() ) ) . '">' . esc_html( self::invoice( $order )['labels']['invoice'] . ' ' . self::invoice( $order )['number'] ) . ' (PDF)</a>' );
		foreach ( self::credits( $order ) as $i => $credit ) {
			$links[] = '<a class="button" href="' . esc_url( self::url( $order, 'credit-' . $i, ! $order->get_user_id() ) ) . '">' . esc_html( $credit['labels']['credit_note'] . ' ' . $credit['number'] ) . ' (PDF)</a>';
		}
		echo '<p class="envo-blocks-invoice-links">' . implode( ' ', $links ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
	}
}
