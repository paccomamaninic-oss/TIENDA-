<?php
/**
 * Tax Switcher: visitors choose whether the prices of the store are shown with or without tax (VAT).
 *
 * Only when WooCommerce calculates taxes (WooCommerce › Settings › General › Enable taxes) and at least one tax rate
 * exists. The choice is kept in the cookie envo_tax_display (incl|excl); the links of the block carry ?envo-tax=…
 * too, so they work without JavaScript. The prices are switched on the server: the options
 * woocommerce_tax_display_shop and woocommerce_tax_display_cart are filtered for the requests of the visitor (pages,
 * wc-ajax, the Store API of the Cart / Checkout / Mini-Cart blocks), so every price follows – lists, the product page,
 * the mini cart, the classic and the block cart, our blocks. Admin screens, e-mails, cron, WP-CLI and other REST
 * routes keep the settings of WooCommerce.
 *
 * Settings: Envo Blocks › Settings › Tax Switcher (option envo_blocks_tax_switcher). Block envo-blocks/tax-switcher
 * and the shortcode [envo_tax_switcher].
 *
 * Page caches: a page made for a visitor whose choice differs from the store default is never cached
 * (DONOTCACHEPAGE + no-cache headers), the cookie is registered with WP Super Cache, WP Rocket and LiteSpeed Cache
 * (a cached copy per choice), and the block reloads a stale cached page once with ?envo-tax=… .
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prices with or without tax, chosen by the visitor.
 */
final class Envo_Blocks_Tax_Switcher {

	/**
	 * Cookie with the choice of the visitor.
	 */
	const COOKIE = 'envo_tax_display';

	/**
	 * URL parameter of the links (works without JavaScript).
	 */
	const PARAM = 'envo-tax';

	/**
	 * Envo Blocks › Settings › Tax Switcher.
	 */
	const PAGE = 'envo-blocks-tax-switcher';

	/**
	 * Option with the settings.
	 */
	const OPTION = 'envo_blocks_tax_switcher';

	/**
	 * Transient: whether tax rates exist.
	 */
	const RATES = 'envo_blocks_tax_rates';

	/**
	 * While true the options are not filtered (reading the store default, sending e-mails).
	 *
	 * @var int
	 */
	private static $paused = 0;

	/**
	 * Mode from the URL parameter of this request.
	 *
	 * @var string
	 */
	private static $requested = '';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_post_envo_blocks_tax_switcher', array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		foreach ( array( 'woocommerce_tax_rate_added', 'woocommerce_tax_rate_updated', 'woocommerce_tax_rate_deleted' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'flush_rates' ) );
		}
		add_action( 'init', array( __CLASS__, 'shortcode' ) );

		if ( ! self::enabled() ) {
			return;
		}

		add_action( 'init', array( __CLASS__, 'from_url' ), 1 );
		foreach ( array( 'shop', 'cart' ) as $where ) {
			add_filter( 'option_woocommerce_tax_display_' . $where, array( __CLASS__, 'display_option' ), 20, 2 );
		}
		add_filter( 'woocommerce_get_price_suffix', array( __CLASS__, 'suffix' ), 20, 2 );
		add_action( 'woocommerce_email_header', array( __CLASS__, 'pause' ), 0 );
		add_action( 'woocommerce_email_footer', array( __CLASS__, 'resume' ), 999 );
		add_action( 'template_redirect', array( __CLASS__, 'no_cache' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'styles' ) );

		// Page caches that can keep a copy per cookie value.
		add_filter( 'rocket_cache_dynamic_cookies', array( __CLASS__, 'cache_cookies' ) );
		add_filter( 'litespeed_vary_cookies', array( __CLASS__, 'cache_cookies' ) );
		add_action( 'admin_init', array( __CLASS__, 'super_cache_cookie' ) );
	}

	/**
	 * Settings with their defaults.
	 *
	 * @return array { enabled: bool, cart: follow|woo, suffix: replace|woo|none, incl: string, excl: string }
	 */
	public static function settings() {
		$saved = get_option( self::OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();

		$settings = array_merge(
			array(
				'enabled' => true,
				'cart'    => 'follow',
				'suffix'  => 'replace',
				'incl'    => '',
				'excl'    => '',
			),
			$saved
		);

		$settings['enabled'] = (bool) $settings['enabled'];
		$settings['cart']    = in_array( $settings['cart'], array( 'follow', 'woo' ), true ) ? $settings['cart'] : 'follow';
		$settings['suffix']  = in_array( $settings['suffix'], array( 'replace', 'woo', 'none' ), true ) ? $settings['suffix'] : 'replace';

		return $settings;
	}

	/**
	 * Whether switching is turned on in the settings.
	 *
	 * @return bool
	 */
	public static function enabled() {
		return self::settings()['enabled'];
	}

	/**
	 * Whether WooCommerce calculates taxes and at least one tax rate exists – only then the switch makes sense.
	 *
	 * @return bool
	 */
	public static function available() {
		$available = function_exists( 'wc_tax_enabled' ) && wc_tax_enabled() && self::has_rates();

		/**
		 * Filters whether prices can be switched between with and without tax (e.g. a demo page with sample rates).
		 *
		 * @param bool $available Taxes are calculated and a tax rate exists.
		 */
		return (bool) apply_filters( 'envo_blocks_tax_switcher_available', $available );
	}

	/**
	 * Whether at least one tax rate exists (any tax class), cached for a day and after every change of the rates.
	 *
	 * @return bool
	 */
	public static function has_rates() {
		$cached = get_transient( self::RATES );
		if ( false === $cached ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- One cheap check, cached in a transient.
			$cached = $wpdb->get_var( "SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates LIMIT 1" ) ? 'yes' : 'no';
			set_transient( self::RATES, $cached, DAY_IN_SECONDS );
		}

		return 'yes' === $cached;
	}

	/**
	 * Number of tax rates (for the settings page).
	 *
	 * @return int
	 */
	private static function count_rates() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Admin page only.
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_tax_rates" );
	}

	/**
	 * A tax rate was added, changed or deleted.
	 */
	public static function flush_rates() {
		delete_transient( self::RATES );
	}

	/**
	 * The default of the store: how WooCommerce shows the prices in the shop (incl|excl).
	 *
	 * @return string
	 */
	public static function default_mode() {
		++self::$paused;
		$mode = get_option( 'woocommerce_tax_display_shop', 'excl' );
		--self::$paused;

		return 'incl' === $mode ? 'incl' : 'excl';
	}

	/**
	 * The mode of the visitor now: the URL parameter of this request, the cookie, else the store default.
	 *
	 * @return string incl|excl
	 */
	public static function mode() {
		if ( '' !== self::$requested ) {
			return self::$requested;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Compared with two fixed values.
		$cookie = isset( $_COOKIE[ self::COOKIE ] ) ? (string) wp_unslash( $_COOKIE[ self::COOKIE ] ) : '';

		return in_array( $cookie, array( 'incl', 'excl' ), true ) ? $cookie : self::default_mode();
	}

	/**
	 * Whether the visitor sees another mode than the store default.
	 *
	 * @return bool
	 */
	public static function switched() {
		return self::available() && self::mode() !== self::default_mode();
	}

	/**
	 * ?envo-tax=incl|excl (the links of the block without JavaScript, the fix of a stale cached page): the mode for
	 * this request and the cookie for the next ones.
	 */
	public static function from_url() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- A display preference, no action.
		$mode = isset( $_GET[ self::PARAM ] ) ? sanitize_key( wp_unslash( $_GET[ self::PARAM ] ) ) : '';
		if ( ! in_array( $mode, array( 'incl', 'excl' ), true ) || ! self::available() ) {
			return;
		}
		self::$requested = $mode;
		if ( ! headers_sent() ) {
			setcookie(
				self::COOKIE,
				$mode,
				array(
					'expires'  => time() + YEAR_IN_SECONDS,
					'path'     => COOKIEPATH ? COOKIEPATH : '/',
					'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
					'secure'   => is_ssl(),
					'httponly' => false,
					'samesite' => 'Lax',
				)
			);
		}
		$_COOKIE[ self::COOKIE ] = $mode;
	}

	/**
	 * Whether the prices of this request follow the visitor: the store (pages, wc-ajax, admin-ajax called from the
	 * store) and the Store API – not admin screens, e-mails, cron, WP-CLI or other REST routes.
	 *
	 * @return bool
	 */
	private static function applies() {
		if ( self::$paused || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return false;
		}
		if ( is_admin() ) {
			if ( ! wp_doing_ajax() ) {
				return false;
			}
			$referer = (string) wp_get_referer();
			if ( '' !== $referer && false !== strpos( $referer, '/wp-admin/' ) ) {
				return false;
			}
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';
			if ( '' === $route ) {
				$route = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			}
			if ( false === strpos( $route, '/wc/store/' ) ) {
				return false;
			}
		}

		return self::available();
	}

	/**
	 * Filter of woocommerce_tax_display_shop and woocommerce_tax_display_cart: the mode of the visitor.
	 *
	 * @param mixed  $value  Saved value.
	 * @param string $option Option name.
	 * @return mixed
	 */
	public static function display_option( $value, $option ) {
		if ( 'woocommerce_tax_display_cart' === $option && 'woo' === self::settings()['cart'] ) {
			return $value;
		}
		if ( ! self::applies() ) {
			return $value;
		}

		return self::mode();
	}

	/**
	 * The text after the prices: "(incl. VAT)" or "(ex. VAT)" for the mode of the visitor, the suffix of
	 * WooCommerce, or none.
	 *
	 * @param string     $html    Suffix HTML.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function suffix( $html, $product ) {
		$settings = self::settings();
		if ( 'woo' === $settings['suffix'] || ! self::applies() ) {
			return $html;
		}
		if ( 'none' === $settings['suffix'] ) {
			return '';
		}
		$text = self::suffix_text( self::mode() );

		return '' === $text ? '' : ' <small class="woocommerce-price-suffix envo-tax-suffix">' . esc_html( $text ) . '</small>';
	}

	/**
	 * The suffix of a mode: the text of the settings or "(incl. VAT)" / "(ex. VAT)" of WooCommerce (VAT or tax by
	 * the country of the store).
	 *
	 * @param string $mode incl|excl.
	 * @return string
	 */
	public static function suffix_text( $mode ) {
		$settings = self::settings();
		$text     = trim( (string) $settings[ 'incl' === $mode ? 'incl' : 'excl' ] );
		if ( '' !== $text ) {
			return $text;
		}
		if ( ! function_exists( 'WC' ) || ! WC()->countries ) {
			return '';
		}

		return 'incl' === $mode ? WC()->countries->inc_tax_or_vat() : WC()->countries->ex_tax_or_vat();
	}

	/**
	 * Default labels of the switch: "Incl. VAT" / "Excl. VAT" (or tax, by the country of the store).
	 *
	 * @param string $mode incl|excl.
	 * @return string
	 */
	public static function label( $mode ) {
		$word = function_exists( 'WC' ) && WC()->countries ? WC()->countries->tax_or_vat() : __( 'Tax', 'envo-blocks' );

		return 'incl' === $mode
			/* translators: %s: "VAT" or "Tax" (WooCommerce, by the country of the store). */
			? sprintf( __( 'Incl. %s', 'envo-blocks' ), $word )
			/* translators: %s: "VAT" or "Tax" (WooCommerce, by the country of the store). */
			: sprintf( __( 'Excl. %s', 'envo-blocks' ), $word );
	}

	/**
	 * E-mails show the prices as set in WooCommerce (an order made by a visitor who chose the other mode).
	 */
	public static function pause() {
		++self::$paused;
	}

	/**
	 * After an e-mail.
	 */
	public static function resume() {
		self::$paused = max( 0, self::$paused - 1 );
	}

	/**
	 * A page made for a visitor whose choice differs from the store default must not land in a shared page cache.
	 */
	public static function no_cache() {
		if ( ! self::switched() ) {
			return;
		}
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		do_action( 'litespeed_control_set_nocache', 'envo tax switcher' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache.
		nocache_headers();
	}

	/**
	 * Body class envo-tax-incl / envo-tax-excl (e.g. for own CSS).
	 *
	 * @param string[] $classes Classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		if ( self::available() ) {
			$classes[] = 'envo-tax-' . self::mode();
		}

		return $classes;
	}

	/**
	 * The text after the prices stays on one line ("(incl. VAT)" broke after "(incl." in narrow product cards).
	 */
	public static function styles() {
		if ( 'replace' !== self::settings()['suffix'] || ! self::available() ) {
			return;
		}
		wp_register_style( 'envo-blocks-tax-suffix', false, array(), ENVO_BLOCKS_VERSION );
		wp_enqueue_style( 'envo-blocks-tax-suffix' );
		wp_add_inline_style( 'envo-blocks-tax-suffix', '.envo-tax-suffix{white-space:nowrap}' );
	}

	/**
	 * Pages opened with ?envo-tax=… are copies of the normal page: noindex.
	 *
	 * @param array $robots Robots directives.
	 * @return array
	 */
	public static function robots( $robots ) {
		if ( '' !== self::$requested ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}

		return $robots;
	}

	/**
	 * WP Rocket (rocket_cache_dynamic_cookies) and LiteSpeed Cache (litespeed_vary_cookies): a cached copy per choice.
	 *
	 * @param array $cookies Cookies.
	 * @return array
	 */
	public static function cache_cookies( $cookies ) {
		$cookies   = is_array( $cookies ) ? $cookies : array();
		$cookies[] = self::COOKIE;

		return array_values( array_unique( $cookies ) );
	}

	/**
	 * WP Super Cache: the cookie is part of the cache key (wpsc_add_cookie() saves it in its config once).
	 */
	public static function super_cache_cookie() {
		if ( ! function_exists( 'wpsc_add_cookie' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$known = isset( $GLOBALS['wpsc_cookies'] ) && is_array( $GLOBALS['wpsc_cookies'] ) ? $GLOBALS['wpsc_cookies'] : array();
		if ( ! in_array( self::COOKIE, $known, true ) ) {
			wpsc_add_cookie( self::COOKIE );
		}
	}

	/**
	 * Shortcode [envo_tax_switcher] (classic themes, widgets): the block with the same attributes
	 * (variant, label, show_label, incl, excl, justify, order).
	 */
	public static function shortcode() {
		add_shortcode(
			'envo_tax_switcher',
			static function ( $atts ) {
				$atts  = shortcode_atts(
					array(
						'variant'    => 'buttons',
						'label'      => '',
						'show_label' => '',
						'incl'       => '',
						'excl'       => '',
						'justify'    => 'left',
						'order'      => 'incl',
					),
					$atts,
					'envo_tax_switcher'
				);
				$attrs = array(
					'variant'   => $atts['variant'],
					'label'     => $atts['label'],
					'showLabel' => in_array( strtolower( (string) $atts['show_label'] ), array( '1', 'yes', 'true' ), true ),
					'inclText'  => $atts['incl'],
					'exclText'  => $atts['excl'],
					'justify'   => $atts['justify'],
					'order'     => $atts['order'],
				);
				return render_block(
					array(
						'blockName'    => 'envo-blocks/tax-switcher',
						'attrs'        => $attrs,
						'innerBlocks'  => array(),
						'innerHTML'    => '',
						'innerContent' => array(),
					)
				);
			}
		);
	}

	/**
	 * Envo Blocks › Settings › Tax Switcher (group Store).
	 *
	 * @param array $pages Pages of the Envo Blocks menu.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Tax Switcher', 'envo-blocks' ),
			'title'      => __( 'Tax Switcher', 'envo-blocks' ),
			'callback'   => array( __CLASS__, 'render' ),
			'capability' => 'manage_woocommerce',
			'section'    => 'store',
		);

		return $pages;
	}

	/**
	 * Styles of the page (the cards of the Checkout Fields page).
	 *
	 * @param string $hook Admin page.
	 */
	public static function admin_assets( $hook ) {
		if ( false === strpos( (string) $hook, self::PAGE ) ) {
			return;
		}
		wp_enqueue_style( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/css/checkout-fields.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/checkout-fields.css' ) );
	}

	/**
	 * The settings page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show after a redirect.
		$saved    = isset( $_GET['eb-tax'] ) && 'saved' === sanitize_key( wp_unslash( $_GET['eb-tax'] ) );
		$settings = self::settings();
		$taxes    = function_exists( 'wc_tax_enabled' ) && wc_tax_enabled();
		$rates    = self::count_rates();
		$display  = array(
			'incl' => __( 'Including tax', 'envo-blocks' ),
			'excl' => __( 'Excluding tax', 'envo-blocks' ),
		);
		$shop     = self::default_mode();
		++self::$paused;
		$cart = 'incl' === get_option( 'woocommerce_tax_display_cart', 'excl' ) ? 'incl' : 'excl';
		--self::$paused;
		$ok   = '<span class="dashicons dashicons-yes-alt" style="color:#00a32a" aria-hidden="true"></span> ';
		$warn = '<span class="dashicons dashicons-warning" style="color:#d63638" aria-hidden="true"></span> ';
		?>
		<div class="eb-cf eb-tax">
			<?php if ( $saved ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The settings were saved.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Prices with or without tax', 'envo-blocks' ); ?></h2>
				<p class="eb-cf__intro"><?php esc_html_e( 'Let visitors choose whether the prices of the store are shown with or without tax (VAT) – handy when you sell to businesses and to consumers. Put the Tax Switcher block into the header or above the products; the choice is remembered and every price follows: product lists, the product page, the mini cart, the cart and the checkout.', 'envo-blocks' ); ?></p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Taxes in WooCommerce', 'envo-blocks' ); ?></th>
						<td>
							<?php
							if ( $taxes ) {
								echo $ok . esc_html__( 'On – WooCommerce calculates taxes.', 'envo-blocks' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed markup.
							} else {
								echo $warn . esc_html__( 'Off – turn on "Enable tax rates and calculations" in WooCommerce › Settings › General.', 'envo-blocks' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed markup.
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Tax rates', 'envo-blocks' ); ?></th>
						<td>
							<?php
							if ( $rates ) {
								/* translators: %d: number of tax rates. */
								echo $ok . esc_html( sprintf( _n( '%d tax rate', '%d tax rates', $rates, 'envo-blocks' ), $rates ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed markup.
							} else {
								echo $warn . esc_html__( 'No tax rates yet – add them in WooCommerce › Settings › Tax.', 'envo-blocks' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed markup.
							}
							if ( $taxes ) {
								echo ' <a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=tax' ) ) . '">' . esc_html__( 'Tax settings', 'envo-blocks' ) . '</a>';
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Default for visitors', 'envo-blocks' ); ?></th>
						<td>
							<?php
							/* translators: 1: "Including tax" or "Excluding tax" for the shop, 2: the same for the cart. */
							echo esc_html( sprintf( __( 'Shop: %1$s, cart and checkout: %2$s', 'envo-blocks' ), $display[ $shop ], $display[ $cart ] ) );
							?>
							<p class="description"><?php esc_html_e( 'Visitors who have not chosen yet see the prices as set in WooCommerce › Settings › Tax ("Display prices in the shop").', 'envo-blocks' ); ?></p>
						</td>
					</tr>
				</table>
				<?php if ( ! $taxes || ! $rates ) : ?>
					<p class="eb-cf__intro"><strong><?php esc_html_e( 'Until taxes are on and at least one tax rate exists, the Tax Switcher block shows nothing in the store.', 'envo-blocks' ); ?></strong></p>
				<?php endif; ?>
			</section>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Settings', 'envo-blocks' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="envo_blocks_tax_switcher">
					<?php wp_nonce_field( 'envo_blocks_tax_switcher' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Switching', 'envo-blocks' ); ?></th>
							<td>
								<label><input type="checkbox" name="enabled" value="1" <?php checked( $settings['enabled'] ); ?>> <?php esc_html_e( 'Visitors can switch between prices with and without tax', 'envo-blocks' ); ?></label>
								<p class="description"><?php esc_html_e( 'Off: the Tax Switcher blocks show nothing and every visitor sees the prices as set in WooCommerce.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-tax-cart"><?php esc_html_e( 'Cart and checkout', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-tax-cart" name="cart">
									<option value="follow" <?php selected( $settings['cart'], 'follow' ); ?>><?php esc_html_e( 'As chosen by the visitor', 'envo-blocks' ); ?></option>
									<option value="woo" <?php selected( $settings['cart'], 'woo' ); ?>><?php esc_html_e( 'As set in WooCommerce (only the shop prices switch)', 'envo-blocks' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-tax-suffix"><?php esc_html_e( 'Text after the prices', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-tax-suffix" name="suffix">
									<option value="replace" <?php selected( $settings['suffix'], 'replace' ); ?>><?php esc_html_e( 'By the choice: "incl. VAT" or "ex. VAT"', 'envo-blocks' ); ?></option>
									<option value="woo" <?php selected( $settings['suffix'], 'woo' ); ?>><?php esc_html_e( 'The price display suffix of WooCommerce', 'envo-blocks' ); ?></option>
									<option value="none" <?php selected( $settings['suffix'], 'none' ); ?>><?php esc_html_e( 'No text', 'envo-blocks' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'A fixed suffix of WooCommerce like "incl. VAT" would be wrong after a switch, so by default it is replaced with the text of the chosen mode.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-tax-incl"><?php esc_html_e( 'Text with tax', 'envo-blocks' ); ?></label></th>
							<td><input type="text" id="eb-tax-incl" class="regular-text" name="incl" value="<?php echo esc_attr( $settings['incl'] ); ?>" placeholder="<?php echo esc_attr( function_exists( 'WC' ) && WC()->countries ? WC()->countries->inc_tax_or_vat() : '' ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-tax-excl"><?php esc_html_e( 'Text without tax', 'envo-blocks' ); ?></label></th>
							<td><input type="text" id="eb-tax-excl" class="regular-text" name="excl" value="<?php echo esc_attr( $settings['excl'] ); ?>" placeholder="<?php echo esc_attr( function_exists( 'WC' ) && WC()->countries ? WC()->countries->ex_tax_or_vat() : '' ); ?>"></td>
						</tr>
					</table>
					<?php submit_button( __( 'Save changes', 'envo-blocks' ) ); ?>
				</form>
			</section>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Good to know', 'envo-blocks' ); ?></h2>
				<ul class="ul-disc">
					<li><?php esc_html_e( 'Add the switch with the Tax Switcher block (Envo Blocks section of the inserter), e.g. in the header template part; in classic themes and widgets use the shortcode [envo_tax_switcher].', 'envo-blocks' ); ?></li>
					<li><?php esc_html_e( 'The choice is kept in a cookie (envo_tax_display) for a year. The amounts charged never change – only how the prices are shown.', 'envo-blocks' ); ?></li>
					<li><?php esc_html_e( 'Orders, e-mails and the admin always use the settings of WooCommerce.', 'envo-blocks' ); ?></li>
					<li><?php esc_html_e( 'Page cache: WP Super Cache, WP Rocket and LiteSpeed Cache keep a copy per choice by themselves. With another cache plugin or a CDN, add the cookie envo_tax_display to its "do not cache" or "vary by cookie" list; otherwise the block reloads a page that came from the cache with the other prices once.', 'envo-blocks' ); ?></li>
				</ul>
			</section>
		</div>
		<?php
	}

	/**
	 * Saves the settings (admin-post.php).
	 */
	public static function save() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_tax_switcher' );
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked above.
		$cart   = isset( $_POST['cart'] ) ? sanitize_key( wp_unslash( $_POST['cart'] ) ) : 'follow';
		$suffix = isset( $_POST['suffix'] ) ? sanitize_key( wp_unslash( $_POST['suffix'] ) ) : 'replace';
		update_option(
			self::OPTION,
			array(
				'enabled' => ! empty( $_POST['enabled'] ),
				'cart'    => in_array( $cart, array( 'follow', 'woo' ), true ) ? $cart : 'follow',
				'suffix'  => in_array( $suffix, array( 'replace', 'woo', 'none' ), true ) ? $suffix : 'replace',
				'incl'    => isset( $_POST['incl'] ) ? sanitize_text_field( wp_unslash( $_POST['incl'] ) ) : '',
				'excl'    => isset( $_POST['excl'] ) ? sanitize_text_field( wp_unslash( $_POST['excl'] ) ) : '',
			)
		);
		// phpcs:enable

		wp_safe_redirect( add_query_arg( 'eb-tax', 'saved', admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}
}
