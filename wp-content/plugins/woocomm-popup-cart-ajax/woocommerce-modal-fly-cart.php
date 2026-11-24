<?php
/**
 * Plugin Name:       Modal Fly Cart & AJAX Add to Cart for WooCommerce
 * Plugin URI:        https://athemeart.com/downloads/woocommerce-popup-cart-ajax/
 * Description:       Free limited edition – WooCommerce fly / modal mini-cart with AJAX add-to-cart. Upgrade to PRO for advanced design and extras.
 * Version:           2.0.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            aThemeArt
 * Author URI:        https://athemeart.com/
 * Text Domain:       atawc_lang
 * Domain Path:       /languages
 * WC requires at least: 5.0
 * WC tested up to:   9.3
 * License:           GPLv3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package ATA_WC_Smart_Popup_Cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ATA_WCSPC_FREE_VERSION', '2.0.1' );
define( 'ATA_WCSPC_FREE_FILE', __FILE__ );
define( 'ATA_WCSPC_FREE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ATA_WCSPC_FREE_URL', plugin_dir_url( __FILE__ ) );
define( 'ATA_WCSPC_FREE_BASENAME', plugin_basename( __FILE__ ) );

// Backward-compatible constants used by assets.
defined( 'WCSPC_PATH' ) or define( 'WCSPC_PATH', ATA_WCSPC_FREE_PATH );
defined( 'WCSPC_URL' ) or define( 'WCSPC_URL', ATA_WCSPC_FREE_URL );
defined( 'WCSPC_FILE' ) or define( 'WCSPC_FILE', ATA_WCSPC_FREE_BASENAME );

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Main free plugin class.
 */
final class ATA_WC_Smart_Popup_Cart {

	/**
	 * @var ATA_WC_Smart_Popup_Cart|null
	 */
	protected static $instance = null;

	/**
	 * @return ATA_WC_Smart_Popup_Cart
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	private function includes() {
		require_once ATA_WCSPC_FREE_PATH . 'inc/default.php';
		require_once ATA_WCSPC_FREE_PATH . 'inc/class-admin.php';
		require_once ATA_WCSPC_FREE_PATH . 'inc/class-frontend.php';
	}

	private function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		if ( is_admin() ) {
			add_action( 'init', array( 'ATA_WC_Smart_Popup_Cart_Admin', 'instance' ) );
		}
		add_action( 'init', array( 'ATA_WC_Smart_Popup_Cart_Frontend', 'instance' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'atawc_lang', false, dirname( ATA_WCSPC_FREE_BASENAME ) . '/languages/' );
	}
}

/**
 * Missing WooCommerce notice.
 */
function ata_wcspc_free_missing_wc_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'Modal Fly Cart for WooCommerce requires WooCommerce to be installed and active.', 'atawc_lang' ); ?></p>
	</div>
	<?php
}

/**
 * Bootstrap.
 */
function ata_wcspc_free_constructor() {
	// Do not run free if PRO is already active.
	if ( class_exists( 'ATA_WC_Smart_Popup_Cart_PRO' ) ) {
		return;
	}
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'ata_wcspc_free_missing_wc_notice' );
		return;
	}
	ATA_WC_Smart_Popup_Cart::instance();
}
add_action( 'plugins_loaded', 'ata_wcspc_free_constructor', 20 );
