<?php
/**
 * Plugin Name:       Envo Blocks – Store Templates, Blocks & Layouts for WooCommerce
 * Plugin URI:        https://envoblocks.com/
 * Description:       Store page templates import and export, product, shop and cart layouts and WooCommerce blocks: product tabs, trust badges, size guide and more.
 * Version:           1.8.0
 * Author:            EnvoThemes
 * Author URI:        https://envothemes.com/
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       envo-blocks
 * Domain Path:       /languages
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

define( 'ENVO_BLOCKS_VERSION', '1.8.0' );
define( 'ENVO_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ENVO_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-license.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-library.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-remote.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-news.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-installer.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-media.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-previews.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-ids.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-preview.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-rest.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-files.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-editor.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-blocks.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-wishlist.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-animations.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-canvas.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-layouts.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-side-cart.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-checkout-fields.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-product-videos.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-product-tabs.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-faq.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-form-styler.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-multilang.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-language-switcher.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-product-questions.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-product-feed.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-review-photos.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-catalog-mode.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-tax-switcher.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-price-history.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-filters.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-filter-blocks.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-qr.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-payment-qr.php';
require_once ENVO_BLOCKS_PATH . 'includes/invoices/class-envo-blocks-invoice-presets.php';
require_once ENVO_BLOCKS_PATH . 'includes/invoices/class-envo-blocks-invoice-settings.php';
require_once ENVO_BLOCKS_PATH . 'includes/invoices/class-envo-blocks-invoice-data.php';
require_once ENVO_BLOCKS_PATH . 'includes/invoices/class-envo-blocks-invoice-pdf.php';
require_once ENVO_BLOCKS_PATH . 'includes/invoices/class-envo-blocks-invoice-admin.php';
require_once ENVO_BLOCKS_PATH . 'includes/invoices/class-envo-blocks-invoices.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-pro-teasers.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-admin.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-setup-notices.php';
require_once ENVO_BLOCKS_PATH . 'includes/class-envo-blocks-pro-settings-teasers.php';
require_once ENVO_BLOCKS_PATH . 'includes/wizard/class-envo-blocks-wizard-palette.php';
require_once ENVO_BLOCKS_PATH . 'includes/wizard/class-envo-blocks-wizard-builder.php';
require_once ENVO_BLOCKS_PATH . 'includes/wizard/class-envo-blocks-wizard-store.php';
require_once ENVO_BLOCKS_PATH . 'includes/wizard/class-envo-blocks-wizard.php';

// After activation the next admin page is "Store in 5 minutes" (once, see Envo_Blocks_Admin::activation_redirect()).
register_activation_hook( __FILE__, array( 'Envo_Blocks_Admin', 'activated' ) );

// Bundled translations in languages/ (PHP .mo/.l10n.php and JS .json); a translate.wordpress.org language pack in
// wp-content/languages/plugins is used first when one is installed.
add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'envo-blocks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	},
	0
);

add_action(
	'plugins_loaded',
	static function () {
		Envo_Blocks_REST::init();
		Envo_Blocks_Remote::init();
		Envo_Blocks_News::init();
		Envo_Blocks_Previews::init();
		Envo_Blocks_Files::init();
		Envo_Blocks_Preview::init();
		Envo_Blocks_Editor::init();
		Envo_Blocks_Setup_Notices::init();
		Envo_Blocks_Blocks::init();
		Envo_Blocks_Animations::init();
		Envo_Blocks_Canvas::init();
		Envo_Blocks_Checkout_Fields::init();
		Envo_Blocks_FAQ::init();
		Envo_Blocks_Form_Styler::init();
		Envo_Blocks_Multilang::init();
		if ( class_exists( 'WooCommerce' ) ) {
			Envo_Blocks_Product_Videos::init();
			Envo_Blocks_Product_Tabs::init();
			Envo_Blocks_Review_Photos::init();
			Envo_Blocks_Product_Questions::init();
			Envo_Blocks_Product_Feed::init();
			Envo_Blocks_Catalog_Mode::init();
			Envo_Blocks_Tax_Switcher::init();
			Envo_Blocks_Price_History::init();
			Envo_Blocks_Filters::init();
			Envo_Blocks_Wishlist::init();
			Envo_Blocks_Payment_QR::init();
			Envo_Blocks_Invoices::init();
			Envo_Blocks_Layouts::init();
			Envo_Blocks_Side_Cart::init();
		}
		Envo_Blocks_Pro_Teasers::init();

		// "Store in 5 minutes": the wizard screen and its REST routes, the brand colours of the built store, the page
		// templates of its pages and the store name / logo in the preview.
		Envo_Blocks_Wizard_Palette::init();
		Envo_Blocks_Wizard::init();
		add_action( 'after_switch_theme', array( 'Envo_Blocks_Wizard_Builder', 'after_switch_theme' ) );
		add_action( 'init', array( 'Envo_Blocks_Wizard_Builder', 'register_templates' ) );
		add_filter( 'render_block', array( 'Envo_Blocks_Wizard_Builder', 'preview_logo' ), 10, 2 );

		if ( is_admin() ) {
			Envo_Blocks_Admin::init();
			Envo_Blocks_Pro_Settings_Teasers::init();
		}
	}
);
