<?php
/**
 * Wizard screen (Envo Blocks › Store in 5 minutes), its REST routes and the notices:
 *   - "Set up your store in 5 minutes" until the store is built;
 *   - "Not happy with how your store looks? Try Envo Blocks Store" when the store was built with another theme.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Store wizard.
 */
final class Envo_Blocks_Wizard {
	const SLUG = 'envo-blocks-wizard';
	const NS   = 'envo-blocks-wizard/v1';

	/**
	 * The theme the templates are made for.
	 */
	const OUR_THEME = 'envo-blocks-store';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_action( 'admin_post_envo_blocks_wizard_switch_ours', array( __CLASS__, 'switch_ours' ) );
		add_action( 'admin_post_envo_blocks_wizard_dismiss', array( __CLASS__, 'dismiss' ) );
		// Envo Blocks sends the user here after its activation (Envo_Blocks_Admin::activation_redirect()) and links
		// the wizard in its setup notice and on Templates Import.
		add_filter( 'envo_blocks_wizard_url', array( __CLASS__, 'wizard_url' ) );
		// Setup notices of Envo Blocks: no "Store in 5 minutes" once the store is built, and no theme notice while the
		// wizard's own "Not happy? Try Envo Blocks Store" notice is there.
		add_filter(
			'envo_blocks_wizard_done',
			static function () {
				return (bool) Envo_Blocks_Wizard_Builder::state()['built'];
			}
		);
		add_filter(
			'envo_blocks_show_theme_notice',
			static function ( $show ) {
				return Envo_Blocks_Wizard_Builder::state()['built'] && ! get_option( 'envo_blocks_wizard_notice_dismissed' ) ? false : $show;
			}
		);
	}

	/**
	 * Whether the current user can use the wizard.
	 *
	 * @return bool
	 */
	public static function can_use() {
		return current_user_can( 'edit_theme_options' ) && current_user_can( 'publish_pages' );
	}

	/**
	 * Filter envo_blocks_wizard_url: the wizard address for users who can use it.
	 *
	 * @param string $url Address so far.
	 * @return string
	 */
	public static function wizard_url( $url ) {
		return self::can_use() ? admin_url( 'admin.php?page=' . self::SLUG ) : $url;
	}

	/**
	 * Our theme from the Envo Blocks installer: status, WordPress.org, what the user may do.
	 *
	 * @return array
	 */
	private static function our_theme() {
		return Envo_Blocks_Installer::our_theme();
	}

	/**
	 * Menu item under Envo Blocks.
	 */
	public static function menu() {
		$title = __( 'Store in 5 minutes', 'envo-blocks' );
		add_submenu_page( 'envo-blocks', $title, $title, 'edit_theme_options', self::SLUG, array( __CLASS__, 'render' ), 1 );
	}

	/**
	 * Whether our theme is installed / active.
	 *
	 * @return array
	 */
	private static function theme_info() {
		$ours   = wp_get_theme( Envo_Blocks_Wizard::OUR_THEME );
		$status = $ours->exists() ? array() : self::our_theme();
		$others = array();
		foreach ( wp_get_themes() as $slug => $theme ) {
			if ( Envo_Blocks_Wizard::OUR_THEME !== $slug && $theme->is_block_theme() ) {
				$others[] = array( 'slug' => $slug, 'name' => $theme->get( 'Name' ), 'screenshot' => $theme->get_screenshot() );
			}
		}
		return array(
			'current'     => get_stylesheet(),
			'currentName' => wp_get_theme()->get( 'Name' ),
			'ours'        => array(
				'slug'       => Envo_Blocks_Wizard::OUR_THEME,
				'name'       => $ours->exists() ? $ours->get( 'Name' ) : 'Envo Blocks Store',
				'installed'  => $ours->exists(),
				// Missing: the screenshot on WordPress.org (when it is there).
				'screenshot' => $ours->exists() ? $ours->get_screenshot() : ( isset( $status['screenshot'] ) ? (string) $status['screenshot'] : '' ),
				// Missing theme: can it come from WordPress.org, may this user install it, its page (download).
				'wporg'      => ! empty( $status['wporg'] ),
				'canInstall' => ! empty( $status['canInstall'] ) && current_user_can( 'switch_themes' ),
				'link'       => isset( $status['link'] ) ? (string) $status['link'] : '',
			),
			'others'      => $others,
		);
	}

	/**
	 * Data for the screen.
	 *
	 * @return array
	 */
	private static function data() {
		$state = Envo_Blocks_Wizard_Builder::state();
		return array(
			'rest'      => esc_url_raw( rest_url( self::NS ) ),
			'restRoot'  => esc_url_raw( rest_url() ),
			// Theme installer of Envo Blocks (shared with Templates Import).
			'installRest' => esc_url_raw( rest_url( 'envo-blocks/v1/dependency' ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'home'      => home_url( '/' ),
			'theme'     => self::theme_info(),
			'templates' => array_values( Envo_Blocks_Wizard_Builder::templates() ),
			'state'     => array(
				'template' => $state['template'],
				'built'    => (bool) $state['built'],
				'colors'   => $state['colors'],
				'name'     => $state['name'] ? $state['name'] : get_option( 'blogname' ),
				'home'     => isset( $state['pages']['home'] ) ? (int) $state['pages']['home'] : 0,
				'pages'    => (object) array_map( 'intval', $state['pages'] ),
				'parts'    => isset( $state['parts'] ) ? $state['parts'] : array( 'header' => 'site', 'footer' => 'site' ),
				'logo'     => ! empty( $state['logo'] ) ? array( 'id' => (int) $state['logo'], 'url' => (string) wp_get_attachment_image_url( (int) $state['logo'], 'medium' ) ) : null,
				'undo'     => (bool) get_option( Envo_Blocks_Wizard_Builder::SNAPSHOT ),
			),
			'store'     => Envo_Blocks_Wizard_Store::data(),
			'more'      => self::more_links(),
			'presets'   => array( '#1e3a8a', '#2563eb', '#0d9488', '#15803d', '#7c3aed', '#db2777', '#e31e24', '#ea580c', '#facc15', '#111827' ),
		);
	}

	/**
	 * Links of the "More store settings" card: WooCommerce settings and the store features of Envo Blocks (only
	 * the pages this site has – PRO ones with PRO).
	 *
	 * @return array
	 */
	private static function more_links() {
		$wc = array();
		if ( function_exists( 'WC' ) ) {
			foreach ( array(
				'general'  => __( 'General – address, selling locations, currency', 'envo-blocks' ),
				'products' => __( 'Products – shop page, measurements, stock', 'envo-blocks' ),
				'tax'      => __( 'Tax – prices with or without tax, rates', 'envo-blocks' ),
				'shipping' => __( 'Shipping – zones, methods, classes', 'envo-blocks' ),
				'checkout' => __( 'Payments – payment methods', 'envo-blocks' ),
				'account'  => __( 'Accounts & privacy – guest checkout, data', 'envo-blocks' ),
				'email'    => __( 'Emails – sender, order e-mails', 'envo-blocks' ),
			) as $tab => $label ) {
				$wc[] = array( 'label' => $label, 'url' => admin_url( 'admin.php?page=wc-settings&tab=' . $tab ) );
			}
		}
		$pages = array_keys( (array) apply_filters( 'envo_blocks_admin_pages', array() ) );
		$envo  = array();
		foreach ( array(
			'envo-blocks-checkout'            => __( 'Checkout fields', 'envo-blocks' ),
			'envo-blocks-payment-qr'          => __( 'Payment QR code', 'envo-blocks' ),
			'envo-blocks-invoices'            => __( 'PDF invoices', 'envo-blocks' ),
			'envo-blocks-reviews'             => __( 'Reviews with photos', 'envo-blocks' ),
			'envo-blocks-questions'           => __( 'Product questions', 'envo-blocks' ),
			'envo-blocks-lowest-price'        => __( 'Lowest price (EU Omnibus)', 'envo-blocks' ),
			'envo-blocks-catalog-mode'        => __( 'Catalog mode', 'envo-blocks' ),
			'envo-blocks-feed'                => __( 'Product feed (Google, Meta)', 'envo-blocks' ),
			'envo-blocks-abandoned-carts'     => __( 'Abandoned carts', 'envo-blocks' ),
			'envo-blocks-stock-alerts'        => __( 'Back in stock alerts', 'envo-blocks' ),
			'envo-blocks-sales-notifications' => __( 'Sales notifications', 'envo-blocks' ),
			'envo-blocks-gift-cards'          => __( 'Gift cards', 'envo-blocks' ),
			'envo-blocks-points'              => __( 'Loyalty points', 'envo-blocks' ),
		) as $slug => $label ) {
			if ( in_array( $slug, $pages, true ) ) {
				$envo[] = array( 'label' => $label, 'url' => admin_url( 'admin.php?page=' . $slug ) );
			}
		}
		return array(
			'layouts' => in_array( 'envo-blocks-layouts', $pages, true ) ? admin_url( 'admin.php?page=envo-blocks-layouts' ) : '',
			'wc'      => $wc,
			'envo'    => $envo,
		);
	}

	/**
	 * Scripts of the screen.
	 *
	 * @param string $hook Screen hook.
	 */
	public static function assets( $hook ) {
		if ( false === strpos( (string) $hook, self::SLUG ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'envo-blocks-wizard', ENVO_BLOCKS_URL . 'assets/css/wizard.css', array(), ENVO_BLOCKS_VERSION );
		wp_enqueue_script( 'envo-blocks-wizard', ENVO_BLOCKS_URL . 'assets/js/wizard.js', array( 'wp-i18n' ), ENVO_BLOCKS_VERSION, true );
		wp_set_script_translations( 'envo-blocks-wizard', 'envo-blocks' );
		wp_add_inline_script( 'envo-blocks-wizard', 'window.envoBlocksWizard = ' . wp_json_encode( self::data() ) . ';', 'before' );
	}

	/**
	 * Screen.
	 */
	public static function render() {
		// wp-header-end: WordPress puts the admin notices here, above the wizard.
		echo '<div class="wrap ebw-wrap"><hr class="wp-header-end"><div id="ebw-app" class="ebw"><p class="ebw-loading">' . esc_html__( 'Loading…', 'envo-blocks' ) . '</p></div></div>';
	}

	/**
	 * REST routes.
	 */
	public static function routes() {
		$can = function () {
			return current_user_can( 'edit_theme_options' ) && current_user_can( 'publish_pages' );
		};
		register_rest_route( self::NS, '/theme', array( 'methods' => 'POST', 'permission_callback' => $can, 'callback' => array( __CLASS__, 'rest_theme' ) ) );
		register_rest_route( self::NS, '/stage', array( 'methods' => 'POST', 'permission_callback' => $can, 'callback' => array( __CLASS__, 'rest_stage' ) ) );
		register_rest_route( self::NS, '/preview', array( 'methods' => 'GET', 'permission_callback' => $can, 'callback' => array( __CLASS__, 'rest_preview' ) ) );
		register_rest_route( self::NS, '/build', array( 'methods' => 'POST', 'permission_callback' => $can, 'callback' => array( __CLASS__, 'rest_build' ) ) );
		register_rest_route( self::NS, '/undo', array( 'methods' => 'POST', 'permission_callback' => $can, 'callback' => array( __CLASS__, 'rest_undo' ) ) );
		register_rest_route( self::NS, '/undo-preview', array( 'methods' => 'GET', 'permission_callback' => $can, 'callback' => array( 'Envo_Blocks_Wizard_Builder', 'undo_preview' ) ) );
		$shop = function () {
			return current_user_can( 'manage_woocommerce' );
		};
		register_rest_route( self::NS, '/store', array( 'methods' => 'POST', 'permission_callback' => $shop, 'callback' => array( __CLASS__, 'rest_store' ) ) );
		register_rest_route(
			self::NS,
			'/states',
			array(
				'methods'             => 'GET',
				'permission_callback' => $shop,
				'callback'            => function ( $request ) {
					return rest_ensure_response( (object) Envo_Blocks_Wizard_Store::states( strtoupper( sanitize_key( (string) $request->get_param( 'country' ) ) ) ) );
				},
			)
		);
		register_rest_route( self::NS, '/samples/delete', array( 'methods' => 'POST', 'permission_callback' => $shop, 'callback' => array( __CLASS__, 'rest_delete_samples' ) ) );
	}

	/**
	 * POST /theme { stylesheet }.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function rest_theme( $request ) {
		if ( ! current_user_can( 'switch_themes' ) ) {
			return new WP_Error( 'ebw_cap', __( 'You cannot switch themes.', 'envo-blocks' ), array( 'status' => 403 ) );
		}
		$r = Envo_Blocks_Wizard_Builder::use_theme( sanitize_key( (string) $request->get_param( 'stylesheet' ) ) );
		return is_wp_error( $r ) ? $r : rest_ensure_response( self::theme_info() );
	}

	/**
	 * POST /stage { template, index }.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function rest_stage( $request ) {
		$r = Envo_Blocks_Wizard_Builder::stage( (string) $request->get_param( 'template' ), (int) $request->get_param( 'index' ) );
		return is_wp_error( $r ) ? $r : rest_ensure_response( $r );
	}

	/**
	 * GET /preview ?brand=&accent= : frame address + the contrast decisions.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function rest_preview( $request ) {
		$state    = Envo_Blocks_Wizard_Builder::state();
		$template = Envo_Blocks_Wizard_Builder::state_template();
		if ( ! $template || ! $state['pages'] ) {
			return new WP_Error( 'envo_blocks_wizard_state', __( 'Choose a template first.', 'envo-blocks' ), array( 'status' => 400 ) );
		}
		$bases = array();
		// Any staged page of the template (page switcher of the preview); the home page by default.
		$key   = sanitize_key( (string) $request->get_param( 'page' ) );
		$page  = isset( $state['pages'][ $key ] ) ? $state['pages'][ $key ] : ( isset( $state['pages']['home'] ) ? $state['pages']['home'] : reset( $state['pages'] ) );
		$args  = array( 'page_id' => (int) $page, 'preview' => 'true', 'ebw_frame' => 1 );
		foreach ( array( 'brand', 'accent' ) as $name ) {
			$v = (string) $request->get_param( $name );
			if ( Envo_Blocks_Wizard_Palette::rgb( $v ) && isset( $template['palette'][ 'eb-' . $name ] ) ) {
				$bases[ $name ]        = strtolower( $v );
				$args[ 'ebw_' . $name ] = ltrim( strtolower( $v ), '#' );
			}
		}
		$name = trim( sanitize_text_field( (string) $request->get_param( 'name' ) ) );
		if ( '' !== $name ) {
			$args['ebw_name'] = rawurlencode( $name );
		}
		if ( absint( $request->get_param( 'logo' ) ) ) {
			$args['ebw_logo'] = absint( $request->get_param( 'logo' ) );
		}
		if ( empty( $args['ebw_brand'] ) ) {
			$args['ebw_brand'] = ltrim( $template['palette']['eb-brand'], '#' );
		}
		return rest_ensure_response(
			array(
				'url'     => add_query_arg( $args, home_url( '/' ) ),
				'summary' => Envo_Blocks_Wizard_Palette::summary( Envo_Blocks_Wizard_Palette::recolor( $template['palette'], $bases ) ),
			)
		);
	}

	/**
	 * POST /build { brand, accent, name }.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function rest_build( $request ) {
		$r = Envo_Blocks_Wizard_Builder::build(
			array( 'brand' => strtolower( (string) $request->get_param( 'brand' ) ), 'accent' => strtolower( (string) $request->get_param( 'accent' ) ) ),
			(string) $request->get_param( 'name' ),
			array(
				'pages'  => $request->has_param( 'pages' ) ? array_map( 'sanitize_key', (array) $request->get_param( 'pages' ) ) : null,
				'header' => sanitize_key( (string) $request->get_param( 'header' ) ),
				'logo'   => absint( $request->get_param( 'logo' ) ),
				'footer' => sanitize_key( (string) $request->get_param( 'footer' ) ),
			)
		);
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$r['theme'] = self::theme_info();
		return rest_ensure_response( $r );
	}

	/**
	 * POST /store { country, currency, bacs, cod, shipping, samples }: the "Your store" step.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function rest_store( $request ) {
		$r = Envo_Blocks_Wizard_Store::apply(
			array(
				'country'  => strtoupper( sanitize_key( (string) $request->get_param( 'country' ) ) ),
				'currency' => strtoupper( sanitize_key( (string) $request->get_param( 'currency' ) ) ),
				'bacs'     => (array) $request->get_param( 'bacs' ),
				'cod'      => (bool) $request->get_param( 'cod' ),
				'shipping' => (array) $request->get_param( 'shipping' ),
				'samples'  => (bool) $request->get_param( 'samples' ),
				'address'  => (array) $request->get_param( 'address' ),
				'pages'    => (array) $request->get_param( 'pages' ),
			)
		);
		return is_wp_error( $r ) ? $r : rest_ensure_response( array( 'done' => $r, 'store' => Envo_Blocks_Wizard_Store::data() ) );
	}

	/**
	 * POST /samples/delete: the unedited sample products to the trash.
	 *
	 * @return WP_REST_Response
	 */
	public static function rest_delete_samples() {
		return rest_ensure_response( array_merge( Envo_Blocks_Wizard_Store::delete_samples(), array( 'store' => Envo_Blocks_Wizard_Store::data() ) ) );
	}

	/**
	 * POST /undo.
	 *
	 * @return WP_REST_Response
	 */
	public static function rest_undo() {
		return rest_ensure_response( Envo_Blocks_Wizard_Builder::undo() );
	}

	/**
	 * Admin notices.
	 */
	public static function notices() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $screen && false !== strpos( (string) $screen->id, self::SLUG ) ) {
			return;
		}
		$state = Envo_Blocks_Wizard_Builder::state();
		if ( ! $state['built'] ) {
			// "Store in 5 minutes" itself is offered by the closable setup notice (Envo_Blocks_Setup_Notices).
			return;
		}
		if ( Envo_Blocks_Wizard::OUR_THEME === get_stylesheet() || get_option( 'envo_blocks_wizard_notice_dismissed' ) ) {
			return;
		}
		if ( ! wp_get_theme( Envo_Blocks_Wizard::OUR_THEME )->exists() ) {
			// Missing: offered only when this user can install it from WordPress.org (the button installs it).
			$status = self::our_theme();
			if ( empty( $status['wporg'] ) || empty( $status['canInstall'] ) || ! current_user_can( 'switch_themes' ) ) {
				return;
			}
		}
		printf(
			'<div class="notice notice-warning ebw-notice"><p><strong>%1$s</strong> %2$s</p><p><a class="button button-primary" href="%3$s">%4$s</a> <a class="button" href="%5$s">%6$s</a> <a href="%7$s">%8$s</a></p></div>',
			esc_html__( 'Not happy with how your store looks?', 'envo-blocks' ),
			esc_html(
				sprintf(
					/* translators: %s: current theme name */
					__( 'Your template was made for the Envo Blocks Store theme – with %s the header, fonts and shop pages can look different. Switching keeps your pages and colours, and you can switch back any time.', 'envo-blocks' ),
					wp_get_theme()->get( 'Name' )
				)
			),
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=envo_blocks_wizard_switch_ours' ), 'envo_blocks_wizard_switch_ours' ) ),
			esc_html__( 'Try Envo Blocks Store', 'envo-blocks' ),
			esc_url( home_url( '/' ) ),
			esc_html__( 'View my store', 'envo-blocks' ),
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=envo_blocks_wizard_dismiss' ), 'envo_blocks_wizard_dismiss' ) ),
			esc_html__( 'I am happy, hide this', 'envo-blocks' )
		);
	}

	/**
	 * admin-post: switch to our theme from the notice.
	 */
	public static function switch_ours() {
		check_admin_referer( 'envo_blocks_wizard_switch_ours' );
		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_die( esc_html__( 'You cannot switch themes.', 'envo-blocks' ) );
		}
		if ( ! wp_get_theme( Envo_Blocks_Wizard::OUR_THEME )->exists() && class_exists( 'Envo_Blocks_Installer' ) ) {
			// From WordPress.org with the Envo Blocks installer (checks install_themes itself).
			$installed = Envo_Blocks_Installer::install_theme( Envo_Blocks_Wizard::OUR_THEME, false );
			if ( is_wp_error( $installed ) ) {
				wp_die( esc_html( $installed->get_error_message() ), '', array( 'back_link' => true ) );
			}
		}
		Envo_Blocks_Wizard_Builder::use_theme( Envo_Blocks_Wizard::OUR_THEME );
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG . '&switched=1' ) );
		exit;
	}

	/**
	 * admin-post: hide the "not happy" notice.
	 */
	public static function dismiss() {
		check_admin_referer( 'envo_blocks_wizard_dismiss' );
		update_option( 'envo_blocks_wizard_notice_dismissed', 1, false );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}
}
