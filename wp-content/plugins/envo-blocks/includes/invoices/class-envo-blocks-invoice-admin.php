<?php
/**
 * Admin of the PDF invoices: Envo Blocks > Invoices, the Invoice box of the order screen, the invoice column and
 * bulk action of the order list, the invoice fields of products.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Invoice admin.
 */
final class Envo_Blocks_Invoice_Admin {

	const PAGE = 'envo-blocks-invoices';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_envo_blocks_invoices', array( __CLASS__, 'save' ) );
		add_action( 'admin_post_envo_blocks_invoice_preview', array( __CLASS__, 'preview' ) );
		add_action( 'admin_post_envo_blocks_invoice_action', array( __CLASS__, 'order_action' ) );
		add_action( 'admin_post_envo_blocks_invoice_font', array( __CLASS__, 'font_upload' ) );
		if ( ! Envo_Blocks_Invoice_Settings::raw( 'enabled' ) ) {
			return;
		}
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ), 30 );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( __CLASS__, 'columns' ), 20 );
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'columns' ), 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( __CLASS__, 'column' ), 20, 2 );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'column' ), 20, 2 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( __CLASS__, 'bulk_actions' ), 20 );
		add_filter( 'bulk_actions-edit-shop_order', array( __CLASS__, 'bulk_actions' ), 20 );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( __CLASS__, 'bulk_issue' ), 20, 3 );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( __CLASS__, 'bulk_issue' ), 20, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		if ( Envo_Blocks_Invoice_Settings::raw( 'fields' ) ) {
			add_action( 'woocommerce_product_options_sku', array( __CLASS__, 'product_fields' ) );
			add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_fields' ) );
			add_action( 'woocommerce_variation_options_pricing', array( __CLASS__, 'variation_fields' ), 20, 3 );
			add_action( 'woocommerce_admin_process_variation_object', array( __CLASS__, 'save_variation_fields' ), 20, 2 );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Settings page                                                      */
	/* ------------------------------------------------------------------ */

	/**
	 * Envo Blocks > Invoices.
	 *
	 * @param array $pages Pages.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Invoices', 'envo-blocks' ),
			'title'      => __( 'PDF Invoices', 'envo-blocks' ),
			'callback'   => array( __CLASS__, 'render' ),
			'capability' => 'manage_woocommerce',
		);
		return $pages;
	}

	/**
	 * Styles of the page (cards of the Checkout Fields page) and the media picker of the logo.
	 *
	 * @param string $hook Admin page.
	 */
	public static function assets( $hook ) {
		if ( false === strpos( (string) $hook, self::PAGE ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/css/checkout-fields.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/checkout-fields.css' ) );
		wp_enqueue_script( 'envo-blocks-invoices-admin', ENVO_BLOCKS_URL . 'assets/js/invoices-admin.js', array( 'jquery' ), Envo_Blocks_Blocks::ver( 'assets/js/invoices-admin.js' ), true );
	}

	/**
	 * Saves the settings form; "Apply preset" also fills in the preset, "Add checkout fields" adds the buyer
	 * identifier fields to Checkout Fields.
	 */
	public static function save() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_invoices' );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cleaned in sanitize().
		$raw      = isset( $_POST['eb_inv'] ) && is_array( $_POST['eb_inv'] ) ? wp_unslash( $_POST['eb_inv'] ) : array();
		$settings = Envo_Blocks_Invoice_Settings::sanitize( $raw );
		$done     = 'saved';
		if ( isset( $_POST['apply_preset'] ) ) {
			$preset   = isset( $_POST['preset_choice'] ) ? sanitize_key( wp_unslash( $_POST['preset_choice'] ) ) : $settings['preset'];
			$settings = Envo_Blocks_Invoice_Settings::with_preset( $settings, $preset );
			$done     = 'preset';
		}
		if ( isset( $_POST['add_fields'] ) ) {
			$settings = self::add_checkout_fields( $settings );
			$done     = 'fields';
		}
		Envo_Blocks_Invoice_Settings::save( $settings );
		wp_safe_redirect( add_query_arg( 'eb-inv', $done, admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * Adds "Company ID" and "Tax ID" (labels of the preset) to Envo Blocks > Checkout Fields and maps them.
	 *
	 * @param array $settings Invoice settings.
	 * @return array
	 */
	private static function add_checkout_fields( array $settings ) {
		if ( ! class_exists( 'Envo_Blocks_Checkout_Fields' ) ) {
			return $settings;
		}
		$preset = Envo_Blocks_Invoice_Presets::get( $settings['preset'] );
		$labels = $preset['buyer'] ? $preset['buyer'] : array( 'Company ID', 'Tax ID' );
		$option = get_option( Envo_Blocks_Checkout_Fields::OPTION, array() );
		$option = is_array( $option ) ? $option : array();
		if ( ! isset( $option['fields'] ) || ! is_array( $option['fields'] ) ) {
			$option['fields'] = array();
		}
		$current = Envo_Blocks_Checkout_Fields::settings();
		if ( empty( $option['address'] ) ) {
			$option['address'] = $current['address'];
		}
		$ids = wp_list_pluck( $current['fields'], 'id' );
		foreach ( $labels as $label ) {
			$id = 'invoice-' . sanitize_title( $label );
			if ( ! in_array( $id, $ids, true ) ) {
				$option['fields'][] = array(
					'id'       => $id,
					'label'    => $label,
					'type'     => 'text',
					'location' => 'contact',
					'required' => false,
				);
			}
			$source = 'field:' . Envo_Blocks_Checkout_Fields::FIELD_NS . '/' . $id;
			$mapped = false;
			foreach ( $settings['buyer'] as &$pair ) {
				if ( $pair['label'] === $label ) {
					$pair['source'] = $source;
					$mapped         = true;
				}
			}
			unset( $pair );
			if ( ! $mapped ) {
				$settings['buyer'][] = array(
					'label'  => $label,
					'source' => $source,
				);
			}
		}
		update_option( Envo_Blocks_Checkout_Fields::OPTION, $option, true );
		return $settings;
	}

	/**
	 * The settings page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$s       = Envo_Blocks_Invoice_Settings::get();
		$presets = Envo_Blocks_Invoice_Presets::all();
		$preset  = Envo_Blocks_Invoice_Presets::get( $s['preset'] );
		$sources = array( '' => __( '— not mapped —', 'envo-blocks' ) ) + Envo_Blocks_Invoice_Data::sources();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show.
		$done     = isset( $_GET['eb-inv'] ) ? sanitize_key( wp_unslash( $_GET['eb-inv'] ) ) : '';
		$statuses = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
		$name     = static function ( $path ) {
			return 'eb_inv[' . $path . ']';
		};
		$check    = static function ( $path, $on, $label ) use ( $name ) {
			echo '<label><input type="hidden" name="' . esc_attr( $name( $path ) ) . '" value="0"><input type="checkbox" name="' . esc_attr( $name( $path ) ) . '" value="1" ' . checked( $on, true, false ) . '> ' . esc_html( $label ) . '</label>';
		};
		?>
		<div class="eb-cf eb-inv">
			<?php if ( 'saved' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The settings were saved.', 'envo-blocks' ); ?></p></div>
			<?php elseif ( 'preset' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The preset filled in the labels, identifiers and texts. Check them and fill in your identifiers.', 'envo-blocks' ); ?></p></div>
			<?php elseif ( 'fields' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The fields were added to Envo Blocks > Checkout Fields (contact part of the checkout) and mapped to the invoice.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>
			<?php if ( ! Envo_Blocks_Invoice_PDF::available() ) : ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'The PDF library is not installed in this copy of the plugin. Invoices are stored and shown as HTML for shop managers only.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>
			<?php if ( '' !== $preset['warning'] ) : ?>
				<div class="notice notice-warning inline"><p><?php echo esc_html( $preset['warning'] ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="envo_blocks_invoices">
				<?php wp_nonce_field( 'envo_blocks_invoices' ); ?>
				<input type="hidden" name="<?php echo esc_attr( $name( 'preset' ) ); ?>" value="<?php echo esc_attr( $s['preset'] ); ?>">
				<input type="hidden" name="<?php echo esc_attr( $name( 'lang' ) ); ?>" value="<?php echo esc_attr( $s['lang'] ); ?>">

				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'PDF invoices', 'envo-blocks' ); ?></h2>
					<p class="eb-cf__intro"><?php esc_html_e( 'Invoices, credit notes and packing slips for WooCommerce orders. An issued document keeps its data even when the order or these settings change later. The country presets only fill in labels and texts; check them with your accountant – the plugin does not guarantee that they meet local law.', 'envo-blocks' ); ?></p>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><?php esc_html_e( 'Invoices', 'envo-blocks' ); ?></th><td><?php $check( 'enabled', $s['enabled'], __( 'Issue invoices', 'envo-blocks' ) ); ?></td></tr>
						<tr>
							<th scope="row"><label for="eb-inv-preset"><?php esc_html_e( 'Country preset', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-inv-preset" name="preset_choice">
									<?php foreach ( $presets as $key => $item ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $s['preset'], $key ); ?>><?php echo esc_html( $item['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<button type="submit" name="apply_preset" value="1" class="button"><?php esc_html_e( 'Apply preset', 'envo-blocks' ); ?></button>
								<p class="description"><?php esc_html_e( 'Fills in the document labels, the identifier names, the tax texts, the paper and the date format. Your name, address, numbers and e-mails stay.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Issue at', 'envo-blocks' ); ?></th>
							<td>
								<?php foreach ( $statuses as $key => $label ) : ?>
									<?php
									$slug = Envo_Blocks_Invoice_Settings::status_slug( $key );
									if ( in_array( $slug, array( 'cancelled', 'failed', 'checkout-draft', 'refunded' ), true ) ) {
										continue;
									}
									?>
									<label style="margin-right:14px;"><input type="checkbox" name="<?php echo esc_attr( $name( 'statuses' ) ); ?>[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $s['statuses'], true ) ); ?>> <?php echo esc_html( $label ); ?></label>
								<?php endforeach; ?>
								<p class="description"><?php esc_html_e( 'The invoice is issued when the order gets one of these statuses. You can also issue it by hand in the order.', 'envo-blocks' ); ?></p>
								<?php $check( 'zero', $s['zero'], __( 'Also for orders with a zero total', 'envo-blocks' ) ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Attach to e-mails', 'envo-blocks' ); ?></th>
							<td>
								<?php foreach ( Envo_Blocks_Invoice_Settings::emails() as $key => $label ) : ?>
									<label style="display:block;"><input type="checkbox" name="<?php echo esc_attr( $name( 'emails' ) ); ?>[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $s['emails'], true ) ); ?>> <?php echo esc_html( $label ); ?></label>
								<?php endforeach; ?>
								<?php $check( 'credit_mail', $s['credit_mail'], __( 'Credit notes to the "Refunded order" e-mail', 'envo-blocks' ) ); ?>
							</td>
						</tr>
						<tr><th scope="row"><?php esc_html_e( 'Guests', 'envo-blocks' ); ?></th><td><?php $check( 'guest_links', $s['guest_links'], __( 'Guests can open their invoice with the link of their order (order key)', 'envo-blocks' ) ); ?></td></tr>
					</table>
				</section>

				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'Seller', 'envo-blocks' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr><th scope="row"><label for="eb-inv-name"><?php esc_html_e( 'Name', 'envo-blocks' ); ?></label></th><td><input type="text" id="eb-inv-name" class="regular-text" name="<?php echo esc_attr( $name( 'seller][name' ) ); ?>" value="<?php echo esc_attr( $s['seller']['name'] ); ?>"></td></tr>
						<tr><th scope="row"><label for="eb-inv-address"><?php esc_html_e( 'Address', 'envo-blocks' ); ?></label></th><td><textarea id="eb-inv-address" class="large-text" rows="4" name="<?php echo esc_attr( $name( 'seller][address' ) ); ?>"><?php echo esc_textarea( $s['seller']['address'] ); ?></textarea></td></tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Identifiers', 'envo-blocks' ); ?></th>
							<td>
								<?php self::pair_rows( $name( 'seller][ids' ), $s['seller']['ids'], 'value', __( 'Name, e.g. VAT ID', 'envo-blocks' ), __( 'Value', 'envo-blocks' ) ); ?>
								<p class="description"><?php esc_html_e( 'Company and tax numbers, as many as your country needs. Empty values are not printed.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr><th scope="row"><?php esc_html_e( 'Tax', 'envo-blocks' ); ?></th><td><?php $check( 'seller][vat', $s['seller']['vat'], __( 'Registered for VAT / GST / sales tax (prints the tax columns and the tax recap)', 'envo-blocks' ) ); ?></td></tr>
						<tr><th scope="row"><label for="eb-inv-bank"><?php esc_html_e( 'Bank account', 'envo-blocks' ); ?></label></th><td><textarea id="eb-inv-bank" class="large-text" rows="3" name="<?php echo esc_attr( $name( 'seller][bank' ) ); ?>"><?php echo esc_textarea( $s['seller']['bank'] ); ?></textarea><p class="description"><?php esc_html_e( 'Printed on unpaid invoices. Orders paid by bank transfer also get the payment QR code (Envo Blocks > Payment QR Code).', 'envo-blocks' ); ?></p></td></tr>
						<tr><th scope="row"><label for="eb-inv-footer"><?php esc_html_e( 'Footer', 'envo-blocks' ); ?></label></th><td><textarea id="eb-inv-footer" class="large-text" rows="2" name="<?php echo esc_attr( $name( 'seller][footer' ) ); ?>"><?php echo esc_textarea( $s['seller']['footer'] ); ?></textarea><p class="description"><?php esc_html_e( 'E.g. the company register entry, directors, contact.', 'envo-blocks' ); ?></p></td></tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Logo', 'envo-blocks' ); ?></th>
							<td class="eb-inv-logo">
								<input type="hidden" name="<?php echo esc_attr( $name( 'seller][logo' ) ); ?>" value="<?php echo esc_attr( (string) $s['seller']['logo'] ); ?>">
								<span class="eb-inv-logo__img"><?php echo $s['seller']['logo'] ? wp_get_attachment_image( $s['seller']['logo'], 'medium', false, array( 'style' => 'max-height:60px;width:auto;' ) ) : ''; ?></span>
								<button type="button" class="button eb-inv-logo__pick"><?php esc_html_e( 'Choose logo', 'envo-blocks' ); ?></button>
								<button type="button" class="button-link eb-inv-logo__remove"><?php esc_html_e( 'Remove', 'envo-blocks' ); ?></button>
							</td>
						</tr>
					</table>
				</section>

				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'Buyer and products', 'envo-blocks' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Buyer identifiers', 'envo-blocks' ); ?></th>
							<td>
								<?php self::pair_rows( $name( 'buyer' ), $s['buyer'], 'source', __( 'Name on the invoice', 'envo-blocks' ), '', $sources ); ?>
								<p class="description"><?php esc_html_e( 'Which checkout field holds the company or tax number of the customer. Printed only when the customer filled it in; with a tax number and no tax, EU orders get the reverse charge text.', 'envo-blocks' ); ?></p>
								<?php if ( class_exists( 'Envo_Blocks_Checkout_Fields' ) ) : ?>
									<button type="submit" name="add_fields" value="1" class="button"><?php esc_html_e( 'Add these fields to the checkout', 'envo-blocks' ); ?></button>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Product fields', 'envo-blocks' ); ?></th>
							<td>
								<?php self::field_rows( $name( 'fields' ), $s['fields'] ); ?>
								<p class="description"><?php esc_html_e( 'Own fields of products and variations (e.g. HSN/SAC code, country of origin), edited under the SKU of the product and printed as columns.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
					</table>
				</section>

				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'Numbers and dates', 'envo-blocks' ); ?></h2>
					<table class="form-table" role="presentation">
						<?php foreach ( array( 'invoice' => __( 'Invoices', 'envo-blocks' ), 'credit' => __( 'Credit notes', 'envo-blocks' ) ) as $type => $label ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $label ); ?></th>
								<td>
									<input type="text" class="regular-text code" name="<?php echo esc_attr( $name( 'numbers][' . $type . '][format' ) ); ?>" value="<?php echo esc_attr( $s['numbers'][ $type ]['format'] ); ?>">
									<select name="<?php echo esc_attr( $name( 'numbers][' . $type . '][reset' ) ); ?>">
										<option value="year" <?php selected( $s['numbers'][ $type ]['reset'], 'year' ); ?>><?php esc_html_e( 'New series every year', 'envo-blocks' ); ?></option>
										<option value="month" <?php selected( $s['numbers'][ $type ]['reset'], 'month' ); ?>><?php esc_html_e( 'New series every month', 'envo-blocks' ); ?></option>
										<option value="never" <?php selected( $s['numbers'][ $type ]['reset'], 'never' ); ?>><?php esc_html_e( 'One series', 'envo-blocks' ); ?></option>
									</select>
									<label><?php esc_html_e( 'Start at', 'envo-blocks' ); ?> <input type="number" min="1" class="small-text" name="<?php echo esc_attr( $name( 'numbers][' . $type . '][start' ) ); ?>" value="<?php echo esc_attr( (string) $s['numbers'][ $type ]['start'] ); ?>"></label>
								</td>
							</tr>
						<?php endforeach; ?>
						<tr><td></td><td><p class="description" style="margin-top:-10px;"><?php esc_html_e( 'Placeholders: {number}, {number:5} (with zeros), {year}, {year_short}, {fiscal_year}, {month}, {order_number}. A number is never used twice. "Start at" moves the series forward, e.g. when you come from another system.', 'envo-blocks' ); ?></p></td></tr>
						<tr>
							<th scope="row"><label for="eb-inv-year"><?php esc_html_e( 'Year starts in', 'envo-blocks' ); ?></label></th>
							<td><select id="eb-inv-year" name="<?php echo esc_attr( $name( 'year_start' ) ); ?>">
								<?php for ( $m = 1; $m <= 12; $m++ ) : ?>
									<option value="<?php echo esc_attr( (string) $m ); ?>" <?php selected( $s['year_start'], $m ); ?>><?php echo esc_html( date_i18n( 'F', mktime( 0, 0, 0, $m, 1, 2000 ) ) ); ?></option>
								<?php endfor; ?>
							</select> <span class="description"><?php esc_html_e( 'For the yearly series, e.g. April for a fiscal year.', 'envo-blocks' ); ?></span></td>
						</tr>
						<tr><th scope="row"><label for="eb-inv-date"><?php esc_html_e( 'Date format', 'envo-blocks' ); ?></label></th><td><input type="text" id="eb-inv-date" class="small-text code" name="<?php echo esc_attr( $name( 'date' ) ); ?>" value="<?php echo esc_attr( $s['date'] ); ?>"> <span class="description"><?php echo esc_html( wp_date( $s['date'] ) ); ?></span></td></tr>
						<tr>
							<th scope="row"><label for="eb-inv-supply"><?php esc_html_e( 'Date of supply', 'envo-blocks' ); ?></label></th>
							<td><select id="eb-inv-supply" name="<?php echo esc_attr( $name( 'supply' ) ); ?>">
								<option value="issue" <?php selected( $s['supply'], 'issue' ); ?>><?php esc_html_e( 'Date of issue', 'envo-blocks' ); ?></option>
								<option value="order" <?php selected( $s['supply'], 'order' ); ?>><?php esc_html_e( 'Order date', 'envo-blocks' ); ?></option>
								<option value="paid" <?php selected( $s['supply'], 'paid' ); ?>><?php esc_html_e( 'Payment date', 'envo-blocks' ); ?></option>
							</select></td>
						</tr>
						<tr><th scope="row"><label for="eb-inv-due"><?php esc_html_e( 'Due in (days)', 'envo-blocks' ); ?></label></th><td><input type="number" min="0" max="365" id="eb-inv-due" class="small-text" name="<?php echo esc_attr( $name( 'due_days' ) ); ?>" value="<?php echo esc_attr( (string) $s['due_days'] ); ?>"> <span class="description"><?php esc_html_e( 'Unpaid invoices only.', 'envo-blocks' ); ?></span></td></tr>
						<tr>
							<th scope="row"><label for="eb-inv-ref"><?php esc_html_e( 'Payment reference', 'envo-blocks' ); ?></label></th>
							<td><select id="eb-inv-ref" name="<?php echo esc_attr( $name( 'reference' ) ); ?>">
								<option value="order" <?php selected( $s['reference'], 'order' ); ?>><?php esc_html_e( 'Order number', 'envo-blocks' ); ?></option>
								<option value="invoice" <?php selected( $s['reference'], 'invoice' ); ?>><?php esc_html_e( 'Digits of the invoice number', 'envo-blocks' ); ?></option>
							</select></td>
						</tr>
					</table>
				</section>

				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'Content and look', 'envo-blocks' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Columns', 'envo-blocks' ); ?></th>
							<td>
								<?php
								$columns = array(
									'sku'      => __( 'SKU', 'envo-blocks' ),
									'unit'     => __( 'Unit price', 'envo-blocks' ),
									'discount' => __( 'Discount', 'envo-blocks' ),
									'rate'     => __( 'Tax rate', 'envo-blocks' ),
									'tax'      => __( 'Tax', 'envo-blocks' ),
								);
								foreach ( $columns as $key => $label ) {
									echo '<span style="margin-right:14px;">';
									$check( 'columns][' . $key, $s['columns'][ $key ], $label );
									echo '</span>';
								}
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Tax recap', 'envo-blocks' ); ?></th>
							<td>
								<select name="<?php echo esc_attr( $name( 'recap' ) ); ?>">
									<option value="wc" <?php selected( $s['recap'], 'wc' ); ?>><?php esc_html_e( 'Taxes as WooCommerce counted them', 'envo-blocks' ); ?></option>
									<option value="per_rate" <?php selected( $s['recap'], 'per_rate' ); ?>><?php esc_html_e( 'Tax counted once per rate and document (Japan)', 'envo-blocks' ); ?></option>
								</select>
								<select name="<?php echo esc_attr( $name( 'rounding' ) ); ?>">
									<option value="floor" <?php selected( $s['rounding'], 'floor' ); ?>><?php esc_html_e( 'Round down', 'envo-blocks' ); ?></option>
									<option value="round" <?php selected( $s['rounding'], 'round' ); ?>><?php esc_html_e( 'Round', 'envo-blocks' ); ?></option>
									<option value="ceil" <?php selected( $s['rounding'], 'ceil' ); ?>><?php esc_html_e( 'Round up', 'envo-blocks' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'With "once per rate" a difference to the order is shown as a rounding line; the total stays the one of the order.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Amounts', 'envo-blocks' ); ?></th>
							<td>
								<?php $check( 'words', $s['words'], __( 'Total in words (English)', 'envo-blocks' ) ); ?><br>
								<select name="<?php echo esc_attr( $name( 'grouping' ) ); ?>">
									<option value="intl" <?php selected( $s['grouping'], 'intl' ); ?>>1,000,000</option>
									<option value="indian" <?php selected( $s['grouping'], 'indian' ); ?>>10,00,000 (<?php esc_html_e( 'India', 'envo-blocks' ); ?>)</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'QR payment', 'envo-blocks' ); ?></th>
							<td>
								<?php $check( 'qr', $s['qr'], __( 'QR code on unpaid bank transfer invoices (CZK, EUR)', 'envo-blocks' ) ); ?>
								<p class="description">
									<?php esc_html_e( 'The code of Envo Blocks > Payment QR Code (its format, account and message). Only an invoice issued before the payment can carry it: to send bank transfer customers the invoice with the code, check "On hold" under "Issue at" and attach the invoice to the "Order on-hold" e-mail.', 'envo-blocks' ); ?>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=envo-blocks-payment-qr' ) ); ?>"><?php esc_html_e( 'Payment QR Code settings', 'envo-blocks' ); ?></a>
								</p>
							</td>
						</tr>
						<tr><th scope="row"><label for="eb-inv-terms"><?php esc_html_e( 'Payment terms', 'envo-blocks' ); ?></label></th><td><textarea id="eb-inv-terms" class="large-text" rows="2" name="<?php echo esc_attr( $name( 'terms' ) ); ?>"><?php echo esc_textarea( $s['terms'] ); ?></textarea></td></tr>
						<tr><th scope="row"><label for="eb-inv-note"><?php esc_html_e( 'Note', 'envo-blocks' ); ?></label></th><td><textarea id="eb-inv-note" class="large-text" rows="2" name="<?php echo esc_attr( $name( 'note' ) ); ?>"><?php echo esc_textarea( $s['note'] ); ?></textarea><p class="description"><?php esc_html_e( 'Printed on every document, e.g. "Thank you for your order".', 'envo-blocks' ); ?></p></td></tr>
						<tr><th scope="row"><label for="eb-inv-nonvat"><?php esc_html_e( 'Text when not registered for tax', 'envo-blocks' ); ?></label></th><td><input type="text" id="eb-inv-nonvat" class="large-text" name="<?php echo esc_attr( $name( 'texts][non_vat' ) ); ?>" value="<?php echo esc_attr( $s['texts']['non_vat'] ); ?>"></td></tr>
						<tr><th scope="row"><label for="eb-inv-rc"><?php esc_html_e( 'Reverse charge text', 'envo-blocks' ); ?></label></th><td><input type="text" id="eb-inv-rc" class="large-text" name="<?php echo esc_attr( $name( 'texts][reverse_charge' ) ); ?>" value="<?php echo esc_attr( $s['texts']['reverse_charge'] ); ?>"></td></tr>
						<tr><th scope="row"><label for="eb-inv-reduced"><?php esc_html_e( 'Reduced rate note', 'envo-blocks' ); ?></label></th><td><input type="text" id="eb-inv-reduced" class="large-text" name="<?php echo esc_attr( $name( 'texts][reduced' ) ); ?>" value="<?php echo esc_attr( $s['texts']['reduced'] ); ?>"><p class="description"><?php esc_html_e( 'When set, items with a lower tax rate get "※" and this note is printed (Japan).', 'envo-blocks' ); ?></p></td></tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Paper and direction', 'envo-blocks' ); ?></th>
							<td>
								<select name="<?php echo esc_attr( $name( 'paper' ) ); ?>">
									<option value="A4" <?php selected( $s['paper'], 'A4' ); ?>>A4</option>
									<option value="Letter" <?php selected( $s['paper'], 'Letter' ); ?>>Letter</option>
								</select>
								<select name="<?php echo esc_attr( $name( 'direction' ) ); ?>">
									<option value="auto" <?php selected( $s['direction'], 'auto' ); ?>><?php esc_html_e( 'Text direction of the site language', 'envo-blocks' ); ?></option>
									<option value="ltr" <?php selected( $s['direction'], 'ltr' ); ?>><?php esc_html_e( 'Left to right', 'envo-blocks' ); ?></option>
									<option value="rtl" <?php selected( $s['direction'], 'rtl' ); ?>><?php esc_html_e( 'Right to left', 'envo-blocks' ); ?></option>
								</select>
								<label><?php esc_html_e( 'Color', 'envo-blocks' ); ?> <input type="color" name="<?php echo esc_attr( $name( 'accent' ) ); ?>" value="<?php echo esc_attr( $s['accent'] ); ?>"></label>
							</td>
						</tr>
					</table>
				</section>

				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'Labels of the documents', 'envo-blocks' ); ?></h2>
					<p class="eb-cf__intro">
						<?php
						/* translators: %s: language name. */
						echo esc_html( sprintf( __( 'The words printed on the documents, in %s (from the preset). Change any of them, e.g. into your own language.', 'envo-blocks' ), Envo_Blocks_Invoice_Presets::languages()[ $s['lang'] ] ) );
						?>
					</p>
					<details>
						<summary class="button"><?php esc_html_e( 'Show the labels', 'envo-blocks' ); ?></summary>
						<table class="form-table" role="presentation">
							<?php foreach ( Envo_Blocks_Invoice_Presets::label_keys() as $key => $english ) : ?>
								<tr><th scope="row"><?php echo esc_html( $english ); ?></th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $name( 'labels][' . $key ) ); ?>" value="<?php echo esc_attr( $s['labels'][ $key ] ); ?>"></td></tr>
							<?php endforeach; ?>
						</table>
					</details>
				</section>

				<div class="eb-inv-actions">
					<?php submit_button( __( 'Save changes', 'envo-blocks' ), 'primary', 'submit', false ); ?>
					<a class="button" target="_blank" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=envo_blocks_invoice_preview' ), 'envo_blocks_invoice_preview' ) ); ?>"><?php esc_html_e( 'Preview (saved settings)', 'envo-blocks' ); ?></a>
					<span class="description"><?php esc_html_e( 'The preview shows the saved settings: save your changes first.', 'envo-blocks' ); ?></span>
				</div>
			</form>

			<?php self::fonts_card(); ?>
		</div>
		<?php
	}

	/**
	 * Rows of label + value (or label + source select): the saved rows and two empty ones.
	 *
	 * @param string     $name    Field name.
	 * @param array      $rows    Rows.
	 * @param string     $second  Key of the second value.
	 * @param string     $label   Placeholder of the label.
	 * @param string     $value   Placeholder of the value.
	 * @param array|null $options Options of a select instead of a text field.
	 */
	private static function pair_rows( $name, array $rows, $second, $label, $value, $options = null ) {
		$rows[] = array( 'label' => '', $second => '' );
		$rows[] = array( 'label' => '', $second => '' );
		echo '<div class="eb-inv-rows">';
		foreach ( array_values( $rows ) as $i => $row ) {
			echo '<div style="margin:0 0 6px;"><input type="text" class="regular-text" style="width:220px;" placeholder="' . esc_attr( $label ) . '" name="' . esc_attr( $name . '[' . $i . '][label]' ) . '" value="' . esc_attr( $row['label'] ) . '"> ';
			if ( is_array( $options ) ) {
				echo '<select name="' . esc_attr( $name . '[' . $i . '][' . $second . ']' ) . '">';
				foreach ( $options as $key => $text ) {
					echo '<option value="' . esc_attr( $key ) . '" ' . selected( $row[ $second ], $key, false ) . '>' . esc_html( $text ) . '</option>';
				}
				if ( '' !== $row[ $second ] && ! isset( $options[ $row[ $second ] ] ) ) {
					echo '<option value="' . esc_attr( $row[ $second ] ) . '" selected>' . esc_html( $row[ $second ] ) . '</option>';
				}
				echo '</select>';
			} else {
				echo '<input type="text" class="regular-text" placeholder="' . esc_attr( $value ) . '" name="' . esc_attr( $name . '[' . $i . '][' . $second . ']' ) . '" value="' . esc_attr( $row[ $second ] ) . '">';
			}
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Rows of the product fields.
	 *
	 * @param string $name   Field name.
	 * @param array  $fields Fields.
	 */
	private static function field_rows( $name, array $fields ) {
		$fields[] = array( 'key' => '', 'label' => '', 'column' => true );
		$fields[] = array( 'key' => '', 'label' => '', 'column' => true );
		foreach ( array_values( $fields ) as $i => $field ) {
			echo '<div style="margin:0 0 6px;"><input type="text" style="width:220px;" placeholder="' . esc_attr__( 'Label, e.g. HSN/SAC', 'envo-blocks' ) . '" name="' . esc_attr( $name . '[' . $i . '][label]' ) . '" value="' . esc_attr( $field['label'] ) . '"> ';
			echo '<input type="hidden" name="' . esc_attr( $name . '[' . $i . '][key]' ) . '" value="' . esc_attr( $field['key'] ) . '">';
			echo '<label><input type="hidden" name="' . esc_attr( $name . '[' . $i . '][column]' ) . '" value="0"><input type="checkbox" name="' . esc_attr( $name . '[' . $i . '][column]' ) . '" value="1" ' . checked( $field['column'], true, false ) . '> ' . esc_html__( 'Column on the invoice', 'envo-blocks' ) . '</label></div>';
		}
	}

	/**
	 * Card of the fonts: bundled scripts, packs (installed, needed), manual upload.
	 */
	private static function fonts_card() {
		$url = static function ( $do, $key ) {
			return wp_nonce_url( add_query_arg( array( 'action' => 'envo_blocks_invoice_font', 'do' => $do, 'pack' => $key ), admin_url( 'admin-post.php' ) ), 'envo_blocks_invoice_font' );
		};
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show.
		$done = isset( $_GET['eb-font'] ) ? sanitize_text_field( wp_unslash( $_GET['eb-font'] ) ) : '';
		?>
		<section class="eb-cf__card" id="eb-inv-fonts">
			<h2><?php esc_html_e( 'Fonts', 'envo-blocks' ); ?></h2>
			<p class="eb-cf__intro"><?php esc_html_e( 'Latin (also Turkish, Vietnamese and all European languages), Greek and Cyrillic are included. Other scripts need a font pack; an invoice with characters of such a script (e.g. a customer name in Japanese) shows a notice in the order. A pack is downloaded from assets.envoblocks.com only once, when you click Download, and every file is checked against its checksum. The fonts are then stored on your site and every PDF uses them from your own server; assets.envoblocks.com is not contacted again.', 'envo-blocks' ); ?></p>
			<?php if ( 'ok' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The font pack is installed. Make the PDF of existing invoices again in the order if they need it.', 'envo-blocks' ); ?></p></div>
			<?php elseif ( 'removed' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The font pack was removed.', 'envo-blocks' ); ?></p></div>
			<?php elseif ( '' !== $done ) : ?>
				<div class="notice notice-error inline"><p><?php echo esc_html( $done ); ?></p></div>
			<?php endif; ?>
			<table class="widefat striped" style="max-width:720px;">
				<thead><tr><th><?php esc_html_e( 'Font pack', 'envo-blocks' ); ?></th><th><?php esc_html_e( 'Size', 'envo-blocks' ); ?></th><th><?php esc_html_e( 'Status', 'envo-blocks' ); ?></th><th></th></tr></thead>
				<tbody>
					<?php foreach ( Envo_Blocks_Invoice_PDF::packs() as $key => $pack ) : ?>
						<?php $installed = Envo_Blocks_Invoice_PDF::installed( $key ); ?>
						<tr>
							<td><?php echo esc_html( $pack['name'] ); ?></td>
							<td><?php echo esc_html( number_format_i18n( $pack['size'], 1 ) . ' MB' ); ?></td>
							<td><?php echo $installed ? esc_html__( 'Installed', 'envo-blocks' ) : esc_html__( 'Not installed', 'envo-blocks' ); ?></td>
							<td>
								<?php if ( $installed ) : ?>
									<a class="button-link-delete" href="<?php echo esc_url( $url( 'remove', $key ) ); ?>"><?php esc_html_e( 'Remove', 'envo-blocks' ); ?></a>
								<?php else : ?>
									<a class="button" href="<?php echo esc_url( $url( 'download', $key ) ); ?>"><?php esc_html_e( 'Download', 'envo-blocks' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:14px;">
				<input type="hidden" name="action" value="envo_blocks_invoice_font">
				<input type="hidden" name="do" value="upload">
				<?php wp_nonce_field( 'envo_blocks_invoice_font' ); ?>
				<label><?php esc_html_e( 'No connection to assets.envoblocks.com? Upload the zip of a pack:', 'envo-blocks' ); ?>
					<select name="pack">
						<?php foreach ( Envo_Blocks_Invoice_PDF::packs() as $key => $pack ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $pack['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<input type="file" name="pack_zip" accept=".zip">
				<button type="submit" class="button"><?php esc_html_e( 'Upload', 'envo-blocks' ); ?></button>
			</form>
		</section>
		<?php
	}

	/**
	 * Font packs: download, upload (zip), remove.
	 */
	public static function font_upload() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( '', 403 );
		}
		check_admin_referer( 'envo_blocks_invoice_font' );
		// phpcs:disable WordPress.Security.NonceVerification -- Checked above.
		$do  = isset( $_REQUEST['do'] ) ? sanitize_key( wp_unslash( $_REQUEST['do'] ) ) : '';
		$key = isset( $_REQUEST['pack'] ) ? sanitize_key( wp_unslash( $_REQUEST['pack'] ) ) : '';
		$result = true;
		if ( 'download' === $do ) {
			$result = Envo_Blocks_Invoice_PDF::download( $key );
		} elseif ( 'remove' === $do ) {
			Envo_Blocks_Invoice_PDF::remove( $key );
			$result = 'removed';
		} elseif ( 'upload' === $do ) {
			$tmp    = isset( $_FILES['pack_zip']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['pack_zip']['tmp_name'] ) ) : '';
			$result = '' !== $tmp && is_uploaded_file( $tmp ) ? Envo_Blocks_Invoice_PDF::install_zip( $key, $tmp ) : new WP_Error( 'envo_blocks_font_upload', __( 'Choose the zip file of the pack.', 'envo-blocks' ) );
		}
		// phpcs:enable
		$flag = is_wp_error( $result ) ? $result->get_error_message() : ( 'removed' === $result ? 'removed' : 'ok' );
		wp_safe_redirect( add_query_arg( 'eb-font', rawurlencode( $flag ), admin_url( 'admin.php?page=' . self::PAGE ) ) . '#eb-inv-fonts' );
		exit;
	}

	/**
	 * Preview of the saved settings: the last order (or a sample) as a document without a number.
	 */
	public static function preview() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( '', 403 );
		}
		check_admin_referer( 'envo_blocks_invoice_preview' );
		$orders = wc_get_orders(
			array(
				'limit'   => 1,
				'type'    => 'shop_order',
				'status'  => array( 'processing', 'completed', 'on-hold', 'pending' ),
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);
		if ( ! $orders ) {
			wp_die( esc_html__( 'The preview needs at least one order.', 'envo-blocks' ) );
		}
		$data           = Envo_Blocks_Invoice_Data::build( $orders[0], 'invoice' );
		$data['number'] = 'PREVIEW';
		$data['reference'] = $data['order']['number'];
		$pdf            = Envo_Blocks_Invoice_PDF::pdf( $data );
		nocache_headers();
		if ( is_wp_error( $pdf ) ) {
			header( 'Content-Type: text/html; charset=utf-8' );
			echo '<p class="preview-note">' . esc_html( $pdf->get_error_message() ) . ' ' . esc_html__( 'HTML preview:', 'envo-blocks' ) . '</p>';
			echo Envo_Blocks_Invoice_PDF::html( $data, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template.
			exit;
		}
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: inline; filename="preview.pdf"' );
		echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- PDF data.
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Order screen                                                       */
	/* ------------------------------------------------------------------ */

	/**
	 * The Invoice box of the order screen (HPOS and posts).
	 */
	public static function meta_box() {
		$screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
		add_meta_box( 'envo-blocks-invoice', __( 'Invoice', 'envo-blocks' ), array( __CLASS__, 'box' ), $screen, 'side', 'high' );
	}

	/**
	 * Content of the Invoice box.
	 *
	 * @param WP_Post|WC_Order $object Order or its post.
	 */
	public static function box( $object ) {
		$order = $object instanceof WC_Order ? $object : wc_get_order( $object->ID );
		if ( ! $order ) {
			return;
		}
		$invoice = Envo_Blocks_Invoices::invoice( $order );
		$action  = static function ( $do, $label, $class = 'button', $confirm = '' ) use ( $order ) {
			$url = wp_nonce_url( add_query_arg( array( 'action' => 'envo_blocks_invoice_action', 'do' => $do, 'order' => $order->get_id() ), admin_url( 'admin-post.php' ) ), 'envo_blocks_invoice_' . $order->get_id() );
			return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '"' . ( '' !== $confirm ? ' onclick="return confirm(' . esc_attr( wp_json_encode( $confirm ) ) . ');"' : '' ) . '>' . esc_html( $label ) . '</a>';
		};
		echo '<div class="envo-blocks-invoice-box">';
		if ( $invoice ) {
			/* translators: 1: invoice number, 2: date. */
			echo '<p><strong>' . esc_html( sprintf( __( 'Invoice %1$s', 'envo-blocks' ), $invoice['number'] ) ) . '</strong><br>' . esc_html( Envo_Blocks_Invoice_Data::date( $invoice['issued'], $invoice ) ) . '</p>';
			echo '<p><a class="button button-primary" target="_blank" href="' . esc_url( Envo_Blocks_Invoices::url( $order ) ) . '">' . esc_html__( 'Open PDF', 'envo-blocks' ) . '</a> ' . $action( 'send', __( 'Send to customer', 'envo-blocks' ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $action.
			$missing = Envo_Blocks_Invoice_PDF::missing_packs( $invoice );
			if ( $missing ) {
				$packs = Envo_Blocks_Invoice_PDF::packs();
				/* translators: %s: font pack names. */
				echo '<p style="color:#b45309;">' . esc_html( sprintf( __( 'The invoice contains characters of a script without a font: install the font pack %s in Envo Blocks > Invoices and make the PDF again.', 'envo-blocks' ), implode( ', ', array_map( static function ( $key ) use ( $packs ) {
					return $packs[ $key ]['name'];
				}, $missing ) ) ) ) . '</p>';
			}
			foreach ( Envo_Blocks_Invoices::credits( $order ) as $i => $credit ) {
				/* translators: %s: credit note number. */
				echo '<p><a target="_blank" href="' . esc_url( Envo_Blocks_Invoices::url( $order, 'credit-' . $i ) ) . '">' . esc_html( sprintf( __( 'Credit note %s', 'envo-blocks' ), $credit['number'] ) ) . '</a> <span class="description">(' . esc_html( Envo_Blocks_Invoice_Data::money( $credit['totals']['total'], $credit ) ) . ')</span></p>';
			}
			echo '<p>' . $action( 'regenerate', __( 'Make the PDF again', 'envo-blocks' ), 'button-link' ) . '<br>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $action.
			echo $action( 'reissue', __( 'Cancel and reissue', 'envo-blocks' ), 'button-link', __( 'Issue a credit note for the whole invoice and a new invoice with the current order data?', 'envo-blocks' ) ) . '<br>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $action.
			echo $action( 'credit', __( 'Credit note for the whole invoice', 'envo-blocks' ), 'button-link', __( 'Issue a credit note for the whole invoice?', 'envo-blocks' ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $action.
		} else {
			echo '<p>' . esc_html__( 'No invoice yet.', 'envo-blocks' ) . '</p>';
			echo '<p>' . $action( 'issue', __( 'Issue invoice', 'envo-blocks' ), 'button button-primary' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $action.
		}
		echo '<p><a target="_blank" href="' . esc_url( Envo_Blocks_Invoices::url( $order, 'packing' ) ) . '">' . esc_html__( 'Packing slip', 'envo-blocks' ) . '</a></p>';
		echo '</div>';
	}

	/**
	 * Actions of the Invoice box.
	 */
	public static function order_action() {
		$id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
		check_admin_referer( 'envo_blocks_invoice_' . $id );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( '', 403 );
		}
		$order = wc_get_order( $id );
		$do    = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';
		if ( ! $order instanceof WC_Order ) {
			wp_die( esc_html__( 'The order does not exist.', 'envo-blocks' ) );
		}
		$result = null;
		switch ( $do ) {
			case 'issue':
				$result = Envo_Blocks_Invoices::issue( $order );
				break;
			case 'regenerate':
				Envo_Blocks_Invoices::file( $order, 'invoice', true );
				foreach ( array_keys( Envo_Blocks_Invoices::credits( $order ) ) as $i ) {
					Envo_Blocks_Invoices::file( $order, 'credit-' . $i, true );
				}
				break;
			case 'reissue':
				$result = Envo_Blocks_Invoices::reissue( $order, __( 'Cancelled and reissued', 'envo-blocks' ) );
				break;
			case 'credit':
				$result = Envo_Blocks_Invoices::credit( $order, null, __( 'Invoice cancelled', 'envo-blocks' ) );
				break;
			case 'send':
				$mails = WC()->mailer()->get_emails();
				if ( isset( $mails['WC_Email_Customer_Invoice'] ) ) {
					$mails['WC_Email_Customer_Invoice']->trigger( $order->get_id(), $order );
					/* translators: %s: e-mail address. */
					$order->add_order_note( sprintf( __( 'Invoice sent to %s.', 'envo-blocks' ), $order->get_billing_email() ) );
				}
				break;
		}
		$back = wp_get_referer() ? wp_get_referer() : $order->get_edit_order_url();
		if ( is_wp_error( $result ) ) {
			set_transient( 'envo_blocks_invoice_notice_' . get_current_user_id(), $result->get_error_message(), 60 );
		}
		wp_safe_redirect( $back );
		exit;
	}

	/**
	 * Error of the last action (one request).
	 */
	public static function notices() {
		$key     = 'envo_blocks_invoice_notice_' . get_current_user_id();
		$message = get_transient( $key );
		if ( $message ) {
			delete_transient( $key );
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
		}
	}

	/* ------------------------------------------------------------------ */
	/* Order list                                                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Invoice column after the order total.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		$out = array();
		foreach ( $columns as $key => $label ) {
			$out[ $key ] = $label;
			if ( 'order_total' === $key ) {
				$out['envo_blocks_invoice'] = __( 'Invoice', 'envo-blocks' );
			}
		}
		if ( ! isset( $out['envo_blocks_invoice'] ) ) {
			$out['envo_blocks_invoice'] = __( 'Invoice', 'envo-blocks' );
		}
		return $out;
	}

	/**
	 * Content of the invoice column.
	 *
	 * @param string       $column Column.
	 * @param int|WC_Order $order  Order or post id.
	 */
	public static function column( $column, $order ) {
		if ( 'envo_blocks_invoice' !== $column ) {
			return;
		}
		$order   = $order instanceof WC_Order ? $order : wc_get_order( $order );
		$invoice = $order ? Envo_Blocks_Invoices::invoice( $order ) : null;
		echo $invoice ? '<a target="_blank" href="' . esc_url( Envo_Blocks_Invoices::url( $order ) ) . '">' . esc_html( $invoice['number'] ) . '</a>' : '–';
	}

	/**
	 * Bulk action "Issue invoices".
	 *
	 * @param array $actions Actions.
	 * @return array
	 */
	public static function bulk_actions( $actions ) {
		$actions['envo_blocks_issue_invoices'] = __( 'Issue invoices', 'envo-blocks' );
		return $actions;
	}

	/**
	 * Issues the invoices of the chosen orders.
	 *
	 * @param string $redirect Redirect URL.
	 * @param string $action   Action.
	 * @param array  $ids      Order ids.
	 * @return string
	 */
	public static function bulk_issue( $redirect, $action, $ids ) {
		if ( 'envo_blocks_issue_invoices' !== $action || ! current_user_can( 'manage_woocommerce' ) ) {
			return $redirect;
		}
		$count = 0;
		foreach ( (array) $ids as $id ) {
			$order = wc_get_order( $id );
			if ( $order instanceof WC_Order && ! Envo_Blocks_Invoices::invoice( $order ) && ! is_wp_error( Envo_Blocks_Invoices::issue( $order ) ) ) {
				++$count;
			}
		}
		return add_query_arg( 'envo_blocks_invoices_issued', $count, $redirect );
	}

	/* ------------------------------------------------------------------ */
	/* Product fields                                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Invoice fields under the SKU of a product.
	 */
	public static function product_fields() {
		global $product_object;
		foreach ( Envo_Blocks_Invoice_Settings::get()['fields'] as $field ) {
			woocommerce_wp_text_input(
				array(
					'id'    => 'envo_blocks_inv_' . $field['key'],
					'label' => $field['label'],
					'value' => $product_object ? $product_object->get_meta( '_envo_blocks_inv_' . $field['key'] ) : '',
				)
			);
		}
	}

	/**
	 * Saves the invoice fields of a product.
	 *
	 * @param WC_Product $product Product.
	 */
	public static function save_product_fields( $product ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce checked the nonce of the product form.
		foreach ( Envo_Blocks_Invoice_Settings::get()['fields'] as $field ) {
			$key = 'envo_blocks_inv_' . $field['key'];
			if ( isset( $_POST[ $key ] ) ) {
				$product->update_meta_data( '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
		// phpcs:enable
	}

	/**
	 * Invoice fields of a variation (empty = the value of the product).
	 *
	 * @param int     $loop      Index.
	 * @param array   $data      Variation data.
	 * @param WP_Post $variation Variation post.
	 */
	public static function variation_fields( $loop, $data, $variation ) {
		$product = wc_get_product( $variation->ID );
		foreach ( Envo_Blocks_Invoice_Settings::get()['fields'] as $field ) {
			woocommerce_wp_text_input(
				array(
					'id'            => 'envo_blocks_inv_' . $field['key'] . '_' . $loop,
					'name'          => 'envo_blocks_inv_' . $field['key'] . '[' . $loop . ']',
					'label'         => $field['label'],
					'placeholder'   => __( 'Same as the product', 'envo-blocks' ),
					'value'         => $product ? $product->get_meta( '_envo_blocks_inv_' . $field['key'] ) : '',
					'wrapper_class' => 'form-row form-row-full',
				)
			);
		}
	}

	/**
	 * Saves the invoice fields of a variation.
	 *
	 * @param WC_Product_Variation $variation Variation.
	 * @param int                  $i         Index.
	 */
	public static function save_variation_fields( $variation, $i ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce checked the nonce.
		foreach ( Envo_Blocks_Invoice_Settings::get()['fields'] as $field ) {
			$key = 'envo_blocks_inv_' . $field['key'];
			if ( isset( $_POST[ $key ][ $i ] ) ) {
				$variation->update_meta_data( '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ][ $i ] ) ) );
			}
		}
		// phpcs:enable
	}
}
