<?php
/**
 * Price history of the products and the lowest price of the last days (EU Omnibus directive: a discount must show the
 * lowest price of the 30 days before it).
 *
 * Every product and variation keeps its price changes in the post meta _envo_blocks_price_history as a short list
 * [ time, price, regular price ], oldest first. A change is recorded when WooCommerce saves the product (editing,
 * bulk edit, import, scheduled sales) and by a daily check that also catches changes made around WooCommerce and
 * gives every product its first entry. Prices come only from the store – nothing is made up.
 *
 * Shown by the Lowest Price block and, when turned on in Envo Blocks > Lowest Price, under the price of the products.
 * WooCommerce > Settings > Products > Lowest price only links to that page.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Price history and the lowest price.
 */
class Envo_Blocks_Price_History {

	/**
	 * Post meta with the price changes.
	 */
	const META = '_envo_blocks_price_history';

	/**
	 * Changes older than this are dropped (the newest one before stays: it was the price at the start).
	 */
	const KEEP_DAYS = 400;

	/**
	 * Daily check of the prices (and the first entries after the installation).
	 */
	const CRON = 'envo_blocks_price_history_check';

	/**
	 * Products checked per run of the check; the next run follows at once until all are done.
	 */
	const BATCH = 200;

	/**
	 * Options (Envo Blocks > Lowest Price).
	 */
	const OPT_DAYS = 'envo_blocks_lowest_price_days';
	const OPT_AUTO = 'envo_blocks_lowest_price_auto';
	const OPT_WHEN = 'envo_blocks_lowest_price_when';
	const OPT_TEXT = 'envo_blocks_lowest_price_text';

	/**
	 * Admin page (Envo Blocks > Lowest Price).
	 */
	const PAGE = 'envo-blocks-lowest-price';

	/**
	 * Hooks.
	 */
	public static function init() {
		foreach ( array( 'woocommerce_new_product', 'woocommerce_update_product', 'woocommerce_new_product_variation', 'woocommerce_update_product_variation' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'on_save' ), 20, 2 );
		}
		add_action( self::CRON, array( __CLASS__, 'check' ) );
		add_action( 'init', array( __CLASS__, 'schedule' ) );

		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'admin_post_envo_blocks_lowest_price', array( __CLASS__, 'save' ) );
		// WooCommerce > Settings > Products > Lowest price: only a link to the page (shop owners look for it there too).
		add_filter( 'woocommerce_get_sections_products', array( __CLASS__, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_products', array( __CLASS__, 'settings' ), 10, 2 );

		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'price_html' ), 20, 2 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'auto_style' ) );
	}

	/**
	 * The daily check, and right away after the installation (it gives the products their first entry).
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'daily', self::CRON );
		}
	}

	/**
	 * A product or variation was saved: record its price when it changed.
	 *
	 * @param int        $id      Product ID.
	 * @param WC_Product $product Product (not passed by older WooCommerce versions).
	 */
	public static function on_save( $id, $product = null ) {
		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( $id );
		}
		if ( $product ) {
			self::record( $product );
		}
	}

	/**
	 * Adds the current price of a product to its history when it differs from the last entry.
	 *
	 * @param WC_Product $product Product or variation.
	 * @param int        $time    Time of the change (now by default).
	 * @return bool Whether an entry was added.
	 */
	public static function record( $product, $time = 0 ) {
		// Variable and grouped products have no price of their own: their variations / children have.
		if ( $product->is_type( array( 'variable', 'grouped' ) ) ) {
			return false;
		}
		$price   = (string) $product->get_price( 'edit' );
		$regular = (string) $product->get_regular_price( 'edit' );
		if ( '' === $price || ! is_numeric( $price ) ) {
			return false;
		}

		$history = self::history( $product->get_id() );
		$last    = end( $history );
		if ( $last && (float) $last[1] === (float) $price && (float) $last[2] === (float) ( '' === $regular ? $price : $regular ) ) {
			return false;
		}

		$history[] = array( $time ? (int) $time : time(), $price, '' === $regular ? $price : $regular );
		update_post_meta( $product->get_id(), self::META, self::prune( $history ) );
		delete_transient( 'envo_blocks_price_history_state' );

		/**
		 * The price of a product or variation changed (Envo Blocks PRO sends the price-drop alerts on it).
		 *
		 * @param WC_Product $product Product or variation.
		 * @param float|null $old     Price before (null for the first entry).
		 * @param float      $new     Price now.
		 */
		do_action( 'envo_blocks_price_changed', $product, $last ? (float) $last[1] : null, (float) $price );
		return true;
	}

	/**
	 * Price changes of a product: list of [ time, price, regular price ], oldest first.
	 *
	 * @param int $id Product or variation ID.
	 * @return array
	 */
	public static function history( $id ) {
		$history = get_post_meta( $id, self::META, true );
		if ( ! is_array( $history ) ) {
			return array();
		}
		$history = array_values(
			array_filter(
				$history,
				static function ( $row ) {
					return is_array( $row ) && 3 === count( $row ) && is_numeric( $row[0] ) && is_numeric( $row[1] );
				}
			)
		);
		usort(
			$history,
			static function ( $a, $b ) {
				return (int) $a[0] - (int) $b[0];
			}
		);
		return $history;
	}

	/**
	 * Drops the changes older than KEEP_DAYS, but keeps the newest of them (the price at the start of the period).
	 *
	 * @param array $history Price changes, oldest first.
	 * @return array
	 */
	private static function prune( $history ) {
		$cutoff = time() - self::KEEP_DAYS * DAY_IN_SECONDS;
		$keep   = array();
		$before = null;
		foreach ( $history as $row ) {
			if ( (int) $row[0] < $cutoff ) {
				$before = $row;
			} else {
				$keep[] = $row;
			}
		}
		return $before ? array_merge( array( $before ), $keep ) : $keep;
	}

	/**
	 * Lowest price of a product in the days before its current price (Omnibus): the lowest price that applied in the
	 * $days days before the current price started. Without a discount ($always) the lowest price of the last $days
	 * days, the current price included.
	 *
	 * @param WC_Product $product Product or variation (not variable).
	 * @param int        $days    Number of days.
	 * @return array|null array( price (float), on_sale (bool), known (bool: from the history; false = the regular
	 *                    price, the history does not reach back far enough) ) or null without a price.
	 */
	public static function lowest( $product, $days ) {
		$price = $product->get_price();
		if ( '' === $price || ! is_numeric( $price ) ) {
			return null;
		}
		$price   = (float) $price;
		$on_sale = $product->is_on_sale();
		$days    = max( 1, (int) $days );
		$history = self::history( $product->get_id() );

		// When did the current price start? The oldest entry of the last unbroken run with this price.
		$start = time();
		for ( $i = count( $history ) - 1; $i >= 0; $i-- ) {
			if ( (float) $history[ $i ][1] !== $price ) {
				break;
			}
			$start = (int) $history[ $i ][0];
		}

		if ( ! $on_sale ) {
			// No discount: the lowest price of the last days, the current one included.
			$from   = time() - $days * DAY_IN_SECONDS;
			$lowest = $price;
			foreach ( self::prices_between( $history, $from, time() ) as $p ) {
				$lowest = min( $lowest, $p );
			}
			return array( $lowest, false, true );
		}

		// A discount: the lowest price of the days before it started.
		$prices = self::prices_between( $history, $start - $days * DAY_IN_SECONDS, $start - 1 );
		if ( ! $prices ) {
			// The history starts with this discount (the plugin was installed during it): the regular price.
			$regular = (float) $product->get_regular_price();
			return $regular > 0 ? array( $regular, true, false ) : null;
		}
		return array( min( $prices ), true, true );
	}

	/**
	 * Prices that applied between two times: the one in force at $from and every change up to $to.
	 *
	 * @param array $history Price changes, oldest first.
	 * @param int   $from    Start time.
	 * @param int   $to      End time.
	 * @return float[]
	 */
	private static function prices_between( $history, $from, $to ) {
		$prices = array();
		$at     = null;
		foreach ( $history as $row ) {
			if ( (int) $row[0] <= $from ) {
				$at = (float) $row[1];
			} elseif ( (int) $row[0] <= $to ) {
				$prices[] = (float) $row[1];
			}
		}
		if ( null !== $at ) {
			$prices[] = $at;
		}
		return $prices;
	}

	/**
	 * Daily check: records the prices that changed without a WooCommerce save and gives the products without a
	 * history their first entry. Works in batches; the next batch is scheduled at once.
	 */
	public static function check() {
		$after = (int) get_option( 'envo_blocks_price_history_cursor', 0 );
		// Only the IDs after the last checked one.
		$where = static function ( $sql ) use ( $after ) {
			global $wpdb;
			return $sql . $wpdb->prepare( " AND {$wpdb->posts}.ID > %d", $after );
		};
		add_filter( 'posts_where', $where );
		$ids = get_posts(
			array(
				'post_type'        => array( 'product', 'product_variation' ),
				'post_status'      => array( 'publish', 'private', 'draft', 'pending', 'future' ),
				'posts_per_page'   => self::BATCH,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'fields'           => 'ids',
				'no_found_rows'    => true,
				'suppress_filters' => false,
			)
		);
		remove_filter( 'posts_where', $where );

		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( $product ) {
				self::record( $product );
			}
		}

		if ( count( $ids ) ) {
			update_option( 'envo_blocks_price_history_cursor', (int) end( $ids ), false );
			wp_schedule_single_event( time() + 30, self::CRON );
		} else {
			// All checked: the next daily run starts from the beginning.
			update_option( 'envo_blocks_price_history_cursor', 0, false );
		}
	}

	/* ------------------------------------------------------------------ settings */

	/**
	 * Days of the lowest price (setting).
	 *
	 * @return int
	 */
	public static function days() {
		return min( 365, max( 1, (int) get_option( self::OPT_DAYS, 30 ) ) );
	}

	/**
	 * Default text: {days} = number of days, {price} = the lowest price.
	 *
	 * @return string
	 */
	public static function default_text() {
		return __( 'Lowest price in the last {days} days: {price}', 'envo-blocks' );
	}

	/**
	 * The text of the setting, or the default one.
	 *
	 * @return string
	 */
	public static function text() {
		$text = trim( (string) get_option( self::OPT_TEXT, '' ) );
		return '' !== $text ? $text : self::default_text();
	}

	/**
	 * "Lowest price" section in WooCommerce > Settings > Products.
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public static function settings_section( $sections ) {
		$sections['envo-lowest-price'] = __( 'Lowest price', 'envo-blocks' );
		return $sections;
	}

	/**
	 * The WooCommerce section only points to Envo Blocks > Lowest Price, where the settings are.
	 *
	 * @param array  $settings Settings of the current section.
	 * @param string $section  Current section.
	 * @return array
	 */
	public static function settings( $settings, $section ) {
		if ( 'envo-lowest-price' !== $section ) {
			return $settings;
		}
		return array(
			array(
				'title' => __( 'Lowest price before a discount', 'envo-blocks' ),
				'type'  => 'title',
				'desc'  => sprintf(
					/* translators: %s: link to Envo Blocks > Lowest Price. */
					__( 'The lowest price of the days before a discount (EU Omnibus rule) is set in %s.', 'envo-blocks' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Envo Blocks › Lowest Price', 'envo-blocks' ) . '</a>'
				),
				'id'    => 'envo_lowest_price',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'envo_lowest_price',
			),
		);
	}

	/**
	 * Envo Blocks > Lowest Price.
	 *
	 * @param array $pages Pages of the Envo Blocks menu.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Lowest Price', 'envo-blocks' ),
			'title'      => __( 'Lowest Price', 'envo-blocks' ),
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
	public static function admin_assets( $hook ) {
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
		check_admin_referer( 'envo_blocks_lowest_price' );
		$auto = isset( $_POST['auto'] ) ? sanitize_key( wp_unslash( $_POST['auto'] ) ) : 'off';
		$when = isset( $_POST['when'] ) ? sanitize_key( wp_unslash( $_POST['when'] ) ) : 'sale';
		update_option( self::OPT_DAYS, min( 365, max( 1, isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30 ) ) );
		update_option( self::OPT_AUTO, in_array( $auto, array( 'off', 'product', 'all' ), true ) ? $auto : 'off' );
		update_option( self::OPT_WHEN, in_array( $when, array( 'sale', 'always' ), true ) ? $when : 'sale' );
		update_option( self::OPT_TEXT, isset( $_POST['text'] ) ? sanitize_text_field( wp_unslash( $_POST['text'] ) ) : '' );
		wp_safe_redirect( add_query_arg( 'eb-lp', 'saved', admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * State of the history: products and variations with an entry, and the date of the oldest entry.
	 *
	 * @return array array( count (int), since (int timestamp or 0) ).
	 */
	private static function state() {
		$state = get_transient( 'envo_blocks_price_history_state' );
		if ( is_array( $state ) ) {
			return $state;
		}
		$ids   = get_posts(
			array(
				'post_type'      => array( 'product', 'product_variation' ),
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Once per hour for this admin page.
			)
		);
		$since = 0;
		foreach ( $ids as $id ) {
			$history = self::history( $id );
			if ( $history && ( ! $since || (int) $history[0][0] < $since ) ) {
				$since = (int) $history[0][0];
			}
		}
		$state = array( count( $ids ), $since );
		set_transient( 'envo_blocks_price_history_state', $state, HOUR_IN_SECONDS );
		return $state;
	}

	/**
	 * The page: what it is, the settings and the state of the history.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show after a redirect.
		$saved = isset( $_GET['eb-lp'] ) && 'saved' === sanitize_key( wp_unslash( $_GET['eb-lp'] ) );
		$auto  = get_option( self::OPT_AUTO, 'off' );
		$when  = get_option( self::OPT_WHEN, 'sale' );
		$state = self::state();
		?>
		<div class="eb-cf eb-lp">
			<?php if ( $saved ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The settings were saved.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Lowest price before a discount', 'envo-blocks' ); ?></h2>
				<p class="eb-cf__intro"><?php esc_html_e( 'In the EU a discount must show the lowest price of the 30 days before it (Omnibus directive). Envo Blocks keeps the price changes of your products and variations and shows that price with the Lowest Price block, or under the price of every product when you turn it on here.', 'envo-blocks' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="envo_blocks_lowest_price">
					<?php wp_nonce_field( 'envo_blocks_lowest_price' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="eb-lp-days"><?php esc_html_e( 'Number of days', 'envo-blocks' ); ?></label></th>
							<td>
								<input type="number" id="eb-lp-days" name="days" min="1" max="365" step="1" class="small-text" value="<?php echo esc_attr( (string) self::days() ); ?>">
								<p class="description"><?php esc_html_e( 'The EU rule is 30 days.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-lp-auto"><?php esc_html_e( 'Show under the price', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-lp-auto" name="auto">
									<option value="off" <?php selected( $auto, 'off' ); ?>><?php esc_html_e( 'No – only where I add the Lowest Price block', 'envo-blocks' ); ?></option>
									<option value="product" <?php selected( $auto, 'product' ); ?>><?php esc_html_e( 'On the product page', 'envo-blocks' ); ?></option>
									<option value="all" <?php selected( $auto, 'all' ); ?>><?php esc_html_e( 'On the product page and in product lists', 'envo-blocks' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Works with block and classic themes. Do not add the Lowest Price block to the same place too.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-lp-when"><?php esc_html_e( 'Show it', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-lp-when" name="when">
									<option value="sale" <?php selected( $when, 'sale' ); ?>><?php esc_html_e( 'Only for products on sale', 'envo-blocks' ); ?></option>
									<option value="always" <?php selected( $when, 'always' ); ?>><?php esc_html_e( 'For every product', 'envo-blocks' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-lp-text"><?php esc_html_e( 'Text', 'envo-blocks' ); ?></label></th>
							<td>
								<input type="text" id="eb-lp-text" class="large-text" name="text" value="<?php echo esc_attr( (string) get_option( self::OPT_TEXT, '' ) ); ?>" placeholder="<?php echo esc_attr( self::default_text() ); ?>">
								<p class="description"><?php esc_html_e( '{days} is the number of days, {price} the lowest price. Leave empty for the default text.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save changes', 'envo-blocks' ) ); ?>
				</form>
			</section>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Price history', 'envo-blocks' ); ?></h2>
				<p class="eb-cf__intro"><?php esc_html_e( 'A change is saved whenever a product is saved (editing, bulk edit, import, scheduled sales); a daily check catches the rest. Until the history reaches back far enough, a discount shows the regular price as the lowest price.', 'envo-blocks' ); ?></p>
				<table class="widefat striped" style="max-width:640px">
					<tr>
						<th><?php esc_html_e( 'Products and variations with a history', 'envo-blocks' ); ?></th>
						<td><?php echo esc_html( number_format_i18n( $state[0] ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'History since', 'envo-blocks' ); ?></th>
						<td><?php echo $state[1] ? esc_html( wp_date( get_option( 'date_format' ), $state[1] ) ) : esc_html__( 'Not started yet – the first check runs within minutes.', 'envo-blocks' ); ?></td>
					</tr>
				</table>
				<p>
					<?php
					printf(
						/* translators: %s: link to the guide. */
						esc_html__( 'Add the Lowest Price block to the product template, or see the %s.', 'envo-blocks' ),
						'<a href="https://envoblocks.com/docs/envo-blocks/shop-blocks/lowest-price/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'guide', 'envo-blocks' ) . '</a>'
					);
					?>
				</p>
			</section>

			<?php
			/**
			 * More cards on Envo Blocks > Lowest Price (Envo Blocks PRO: the price-drop alerts).
			 */
			do_action( 'envo_blocks_lowest_price_page' );
			?>
		</div>
		<?php
	}

	/* ------------------------------------------------------------------ display */

	/**
	 * The text with the lowest price of a product, or "" when nothing is shown.
	 *
	 * @param WC_Product $product Product or variation.
	 * @param int        $days    Days.
	 * @param string     $when    "sale" or "always".
	 * @param string     $text    Text with {days} and {price}.
	 * @return string HTML (the price is wc_price()).
	 */
	public static function line( $product, $days, $when, $text ) {
		if ( ! $product || $product->is_type( array( 'variable', 'grouped' ) ) ) {
			return '';
		}
		$lowest = self::lowest( $product, $days );
		if ( ! $lowest || ( 'sale' === $when && ! $lowest[1] ) ) {
			return '';
		}
		$price = wc_price( wc_get_price_to_display( $product, array( 'price' => $lowest[0] ) ) );
		return str_replace( array( '{days}', '{price}' ), array( esc_html( number_format_i18n( $days ) ), $price ), esc_html( $text ) );
	}

	/**
	 * Lowest price under the price of the products (setting "Show under the price").
	 *
	 * @param string     $html    Price HTML.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function price_html( $html, $product ) {
		$auto = get_option( self::OPT_AUTO, 'off' );
		if ( 'off' === $auto || ! $product || ( is_admin() && ! wp_doing_ajax() ) ) {
			return $html;
		}
		if ( 'product' === $auto ) {
			$main = is_singular( 'product' ) ? get_queried_object_id() : 0;
			$id   = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
			// The variation prices of the product page come by AJAX for large variable products.
			if ( ! $main && ! ( wp_doing_ajax() && $product->is_type( 'variation' ) ) ) {
				return $html;
			}
			if ( $main && $id !== $main ) {
				return $html;
			}
		}
		$for = $product;
		if ( $product->is_type( 'variable' ) ) {
			// WooCommerce shows the price of a chosen variation only when the variations differ in price; with one
			// price for all, the line goes under the price of the product (from its cheapest variation).
			if ( (float) $product->get_variation_price( 'min' ) !== (float) $product->get_variation_price( 'max' ) ) {
				return $html;
			}
			$prices = $product->get_variation_prices();
			$first  = $prices['price'] ? array_keys( $prices['price'] ) : array();
			$for    = $first ? wc_get_product( reset( $first ) ) : null;
			if ( ! $for ) {
				return $html;
			}
		}
		$line = self::line( $for, self::days(), get_option( self::OPT_WHEN, 'sale' ), self::text() );
		return '' === $line ? $html : $html . ' <span class="envo-lowest-price">' . $line . '</span>';
	}

	/**
	 * Style of the line under the price (only when the setting is on).
	 */
	public static function auto_style() {
		if ( 'off' === get_option( self::OPT_AUTO, 'off' ) ) {
			return;
		}
		wp_register_style( 'envo-blocks-lowest-price', false, array(), ENVO_BLOCKS_VERSION );
		wp_enqueue_style( 'envo-blocks-lowest-price' );
		wp_add_inline_style( 'envo-blocks-lowest-price', '.envo-lowest-price{display:block;font-size:.8em;font-weight:400;opacity:.75;margin-top:.15em}.envo-lowest-price .amount{font-weight:600}' );
	}

	/**
	 * Removes the data of the price history (uninstall).
	 */
	public static function uninstall() {
		delete_metadata( 'post', 0, self::META, '', true );
		foreach ( array( self::OPT_DAYS, self::OPT_AUTO, self::OPT_WHEN, self::OPT_TEXT, 'envo_blocks_price_history_cursor' ) as $option ) {
			delete_option( $option );
		}
		delete_transient( 'envo_blocks_price_history_state' );
		wp_clear_scheduled_hook( self::CRON );
	}
}
