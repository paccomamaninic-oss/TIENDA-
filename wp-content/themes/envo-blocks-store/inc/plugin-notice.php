<?php
/**
 * Admin notice recommending the free Envo Blocks plugin.
 *
 * One button installs (from wordpress.org) and activates the plugin over AJAX. Without JavaScript the button
 * falls back to the core install / activate screens. The notice can be dismissed for good.
 *
 * @package Envo_Blocks_Store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Envo_Blocks_Store_Plugin_Notice' ) ) :

	/**
	 * Envo Blocks recommendation.
	 */
	class Envo_Blocks_Store_Plugin_Notice {

		const SLUG      = 'envo-blocks';
		const FILE      = 'envo-blocks/envo-blocks.php';
		const NAME      = 'Envo Blocks';
		const DISMISSED = 'envo_blocks_store_plugin_notice_dismissed';
		const NONCE     = 'envo_blocks_store_plugin_notice';

		/**
		 * Hooks.
		 */
		public static function init() {
			add_action( 'admin_notices', array( __CLASS__, 'render' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
			add_action( 'wp_ajax_envo_blocks_store_install_plugin', array( __CLASS__, 'ajax_install' ) );
			add_action( 'wp_ajax_envo_blocks_store_dismiss_plugin_notice', array( __CLASS__, 'ajax_dismiss' ) );
		}

		/**
		 * 'active', 'installed' (not active) or 'missing'.
		 */
		public static function state() {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( is_plugin_active( self::FILE ) ) {
				return 'active';
			}
			$plugins = get_plugins();
			return isset( $plugins[ self::FILE ] ) ? 'installed' : 'missing';
		}

		/**
		 * Admin screens (hook suffixes) with the notice: Dashboard, Appearance > Themes and Plugins only,
		 * so it does not follow the user through the whole admin.
		 */
		private static function screens() {
			return array( 'index.php', 'themes.php', 'plugins.php' );
		}

		/**
		 * Only for people who can install and activate plugins, until it is dismissed or the plugin is active.
		 *
		 * @param string $hook_suffix Current admin page (hook suffix).
		 */
		private static function should_show( $hook_suffix ) {
			if ( ! in_array( $hook_suffix, self::screens(), true ) ) {
				return false;
			}
			if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
				return false;
			}
			return ! get_option( self::DISMISSED ) && 'active' !== self::state();
		}

		/**
		 * Where to go after the plugin is active: its Templates Import page.
		 */
		public static function plugin_url() {
			return admin_url( 'admin.php?page=' . self::SLUG . '-import' );
		}

		/**
		 * The install / activate button (also used on the theme page). Empty when the plugin is active.
		 *
		 * @param string $class Extra button classes.
		 */
		public static function button( $class = 'button-hero' ) {
			$state = self::state();
			if ( 'active' === $state ) {
				return '';
			}
			if ( 'installed' === $state ) {
				$label = __( 'Activate Envo Blocks', 'envo-blocks-store' );
				$url   = wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( self::FILE ) ), 'activate-plugin_' . self::FILE );
			} else {
				$label = __( 'Install Envo Blocks – free', 'envo-blocks-store' );
				$url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . self::SLUG ), 'install-plugin_' . self::SLUG );
			}
			return sprintf(
				'<a href="%1$s" class="button button-primary %2$s ebs-plugin-notice__button" data-state="%3$s">%4$s</a>',
				esc_url( $url ),
				esc_attr( $class ),
				esc_attr( $state ),
				esc_html( $label )
			);
		}

		/**
		 * Features of the plugin: four for the notice, all of them for the theme page.
		 *
		 * @param bool $all All features instead of the short list.
		 */
		public static function features( $all = false ) {
			if ( ! $all ) {
				return array(
					__( 'Ready-made store templates and page sections, imported in one click', 'envo-blocks-store' ),
					__( 'Layouts for the product, shop, cart and checkout pages', 'envo-blocks-store' ),
					__( 'Wishlist, product filters, product tabs and more store blocks', 'envo-blocks-store' ),
					__( 'PDF invoices and your own checkout fields', 'envo-blocks-store' ),
				);
			}
			return array(
				__( 'Store templates, page sections and block presets, imported in one click', 'envo-blocks-store' ),
				__( 'Store layouts for the product page, shop, cart, checkout and My Account', 'envo-blocks-store' ),
				__( 'Wishlist with a heart button on products and a wishlist page', 'envo-blocks-store' ),
				__( 'Product filters by price, category, attribute, rating and stock', 'envo-blocks-store' ),
				__( 'PDF invoices, credit notes and packing slips for your orders', 'envo-blocks-store' ),
				__( 'Checkout fields: show, hide, rename, reorder or add your own', 'envo-blocks-store' ),
				__( 'Product tabs, product videos and customer questions', 'envo-blocks-store' ),
				__( 'Review photos, the lowest price (EU Omnibus) and catalog mode', 'envo-blocks-store' ),
				__( 'A product feed for Google Merchant Center and Meta', 'envo-blocks-store' ),
				__( 'Store blocks: free shipping bar, delivery estimate, size guide, FAQ and more', 'envo-blocks-store' ),
			);
		}

		/**
		 * The installer script (shared by the notice and the theme page).
		 */
		public static function enqueue_installer() {
			$version = wp_get_theme( get_template() )->get( 'Version' );
			wp_enqueue_script( 'envo-blocks-store-plugin-notice', get_theme_file_uri( 'assets/admin/plugin-notice.js' ), array(), $version, true );
			wp_localize_script(
				'envo-blocks-store-plugin-notice',
				'envoBlocksStorePluginNotice',
				array(
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( self::NONCE ),
					'installing' => __( 'Installing…', 'envo-blocks-store' ),
					'activating' => __( 'Activating…', 'envo-blocks-store' ),
					'done'       => __( 'Activated – opening Envo Blocks…', 'envo-blocks-store' ),
					'failed'     => __( 'The plugin could not be installed automatically.', 'envo-blocks-store' ),
					'manual'     => __( 'Install it from Plugins › Add New', 'envo-blocks-store' ),
				)
			);
		}

		/**
		 * Styles and script, only where the notice is shown.
		 *
		 * @param string $hook_suffix Current admin page (hook suffix).
		 */
		public static function enqueue( $hook_suffix ) {
			if ( ! self::should_show( $hook_suffix ) ) {
				return;
			}
			wp_enqueue_style( 'envo-blocks-store-plugin-notice', get_theme_file_uri( 'assets/admin/plugin-notice.css' ), array(), wp_get_theme( get_template() )->get( 'Version' ) );
			self::enqueue_installer();
		}

		/**
		 * The notice.
		 */
		public static function render() {
			global $hook_suffix;

			if ( ! self::should_show( (string) $hook_suffix ) ) {
				return;
			}

			$theme = wp_get_theme( get_template() )->get( 'Name' );
			?>
			<div class="notice notice-info is-dismissible ebs-plugin-notice">
				<div class="ebs-plugin-notice__inner">
					<img class="ebs-plugin-notice__logo" src="<?php echo esc_url( get_theme_file_uri( 'assets/admin/envo-blocks.svg' ) ); ?>" width="52" height="52" alt="" />
					<div class="ebs-plugin-notice__body">
						<h2 class="ebs-plugin-notice__title"><?php esc_html_e( 'Build your store faster with Envo Blocks', 'envo-blocks-store' ); ?></h2>
						<p class="ebs-plugin-notice__text">
							<?php
							printf(
								/* translators: 1: Theme name, 2: Plugin name */
								esc_html__( '%1$s works best with %2$s, our free plugin. Import complete store pages or single sections in one click and add the store blocks WordPress does not have.', 'envo-blocks-store' ),
								'<strong>' . esc_html( $theme ) . '</strong>',
								'<strong>' . esc_html( self::NAME ) . '</strong>'
							);
							?>
						</p>
						<ul class="ebs-plugin-notice__features">
							<?php foreach ( self::features() as $feature ) : ?>
								<li><?php echo esc_html( $feature ); ?></li>
							<?php endforeach; ?>
						</ul>
						<p class="ebs-plugin-notice__actions">
							<?php echo self::button(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in button(). ?>
							<a href="<?php echo esc_url( admin_url( 'themes.php?page=envo-blocks-store-about' ) ); ?>" class="ebs-plugin-notice__more"><?php esc_html_e( 'Getting started with Envo Blocks Store', 'envo-blocks-store' ); ?></a>
						</p>
						<p class="ebs-plugin-notice__message" role="status" aria-live="polite"></p>
					</div>
					<div class="ebs-plugin-notice__media" aria-hidden="true">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/admin/envo-blocks-preview.webp' ) ); ?>" width="360" height="250" alt="" decoding="async" />
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Install (when missing) and activate the plugin.
		 */
		public static function ajax_install() {
			check_ajax_referer( self::NONCE, 'nonce' );

			if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You are not allowed to install plugins.', 'envo-blocks-store' ) ), 403 );
			}

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( 'missing' === self::state() ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/misc.php';
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

				$api = plugins_api(
					'plugin_information',
					array(
						'slug'   => self::SLUG,
						'fields' => array( 'sections' => false ),
					)
				);
				if ( is_wp_error( $api ) ) {
					wp_send_json_error( array( 'message' => $api->get_error_message() ) );
				}

				$skin     = new WP_Ajax_Upgrader_Skin();
				$upgrader = new Plugin_Upgrader( $skin );
				$result   = $upgrader->install( $api->download_link );

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				}
				if ( is_wp_error( $skin->result ) ) {
					wp_send_json_error( array( 'message' => $skin->result->get_error_message() ) );
				}
				if ( $skin->get_errors()->has_errors() ) {
					wp_send_json_error( array( 'message' => $skin->get_error_messages() ) );
				}
				if ( ! $result ) {
					// Usually a host that needs FTP credentials: the core install screen asks for them.
					wp_send_json_error( array( 'message' => '' ) );
				}
				wp_clean_plugins_cache();
			}

			$activated = activate_plugin( self::FILE );
			if ( is_wp_error( $activated ) ) {
				wp_send_json_error( array( 'message' => $activated->get_error_message() ) );
			}

			wp_send_json_success( array( 'redirect' => self::plugin_url() ) );
		}

		/**
		 * Remember a dismissed notice.
		 */
		public static function ajax_dismiss() {
			check_ajax_referer( self::NONCE, 'nonce' );

			if ( current_user_can( 'install_plugins' ) ) {
				update_option( self::DISMISSED, 1, false );
			}
			wp_send_json_success();
		}
	}

	Envo_Blocks_Store_Plugin_Notice::init();

endif;
