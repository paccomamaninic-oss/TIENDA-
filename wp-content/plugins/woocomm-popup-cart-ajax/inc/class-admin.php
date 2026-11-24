<?php
/**
 * Develop by : Saiful Islam (aThemeArt)
 *
 * Admin settings – free limited edition.
 *
 * @package ATA_WC_Smart_Popup_Cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ATA_WC_Smart_Popup_Cart_Admin
 */
class ATA_WC_Smart_Popup_Cart_Admin {

	/**
	 * @var string
	 */
	protected $option_name = 'ata_wcspc_free_options';

	/**
	 * @var ATA_WC_Smart_Popup_Cart_Admin|null
	 */
	protected static $instance = null;

	/**
	 * @return ATA_WC_Smart_Popup_Cart_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . ATA_WCSPC_FREE_BASENAME, array( $this, 'plugin_links' ) );
	}

	/**
	 * @param array $links Links.
	 * @return array
	 */
	public function plugin_links( $links ) {
		$settings = '<a href="' . esc_url( admin_url( 'admin.php?page=ata-wcspc-free' ) ) . '">' . esc_html__( 'Settings', 'atawc_lang' ) . '</a>';
		$pro      = '<a href="https://athemeart.com/downloads/woocommerce-popup-cart-ajax/" target="_blank" rel="noopener noreferrer" style="color:#9b5c8f;font-weight:600;">' . esc_html__( 'Upgrade to Pro', 'atawc_lang' ) . '</a>';
		array_unshift( $links, $settings, $pro );
		return $links;
	}

	public function register_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Modal / Ajax Cart', 'atawc_lang' ),
			__( 'Modal / Ajax Cart', 'atawc_lang' ),
			'manage_woocommerce',
			'ata-wcspc-free',
			array( $this, 'render_page' )
		);
	}

	/**
	 * @param string $hook Hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'woocommerce_page_ata-wcspc-free' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'ata-wcspc-free-fonts', ATA_WCSPC_FREE_URL . 'assets/css/fonts.css', array(), ATA_WCSPC_FREE_VERSION );
		wp_enqueue_style( 'ata-wcspc-free-admin', ATA_WCSPC_FREE_URL . 'assets/css/admin.css', array(), ATA_WCSPC_FREE_VERSION );
		wp_enqueue_script( 'ata-wcspc-free-admin', ATA_WCSPC_FREE_URL . 'assets/js/backend.js', array( 'jquery', 'wp-color-picker' ), ATA_WCSPC_FREE_VERSION, true );
	}

	public function register_settings() {
		register_setting(
			'ata_wcspc_free_settings_group',
			$this->option_name,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'ata_wcspc_free_sanitize_options',
				'default'           => ata_wcspc_free_get_default_options(),
			)
		);
	}

	/**
	 * @param string $label Label.
	 * @param string $desc  Description.
	 */
	protected function row_start( $label, $desc = '' ) {
		echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label>';
		if ( $desc ) {
			echo '<p class="description ata-field-hint">' . esc_html( $desc ) . '</p>';
		}
		echo '</th><td>';
	}

	protected function row_end( $extra = '' ) {
		if ( $extra ) {
			echo '<p class="description">' . esc_html( $extra ) . '</p>';
		}
		echo '</td></tr>';
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'atawc_lang' ) );
		}

		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error( 'ata_wcspc_free_messages', 'saved', __( 'Settings saved.', 'atawc_lang' ), 'updated' );
		}
		settings_errors( 'ata_wcspc_free_messages' );

		$opts = ata_wcspc_free_get_option();
		?>
		<div class="wrap ata-wcspc-wrap">
			<div class="ata-wcspc-header">
				<div class="ata-wcspc-header-inner">
					<div class="ata-wcspc-header-left">
						<h1>
							<span class="dashicons dashicons-cart"></span>
							<?php esc_html_e( 'Modal / Ajax Cart', 'atawc_lang' ); ?>
							<span class="ata-wcspc-badge-free"><?php esc_html_e( 'Free', 'atawc_lang' ); ?></span>
							<span class="ata-wcspc-version">v<?php echo esc_html( ATA_WCSPC_FREE_VERSION ); ?></span>
						</h1>
						<p class="ata-wcspc-subtitle"><?php esc_html_e( 'Limited free edition – core fly cart & AJAX. Upgrade for advanced design and extras.', 'atawc_lang' ); ?></p>
					</div>
					<div class="ata-wcspc-header-right">
						<a class="ata-wcspc-upgrade-btn" href="https://athemeart.com/downloads/woocommerce-popup-cart-ajax/" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Upgrade to Pro', 'atawc_lang' ); ?>
						</a>
					</div>
				</div>
			</div>
			<form method="post" action="options.php" class="ata-wcspc-form" id="ata-wcspc-settings-form">
				<?php settings_fields( 'ata_wcspc_free_settings_group' ); ?>

				<div class="ata-wcspc-tabs">
					<nav class="ata-wcspc-tab-nav" role="tablist">
						<button type="button" class="ata-wcspc-tab active" data-tab="general"><?php esc_html_e( 'General', 'atawc_lang' ); ?></button>
						<button type="button" class="ata-wcspc-tab" data-tab="design"><?php esc_html_e( 'Design', 'atawc_lang' ); ?></button>
						<button type="button" class="ata-wcspc-tab" data-tab="cart-button"><?php esc_html_e( 'Cart Button', 'atawc_lang' ); ?></button>
						<button type="button" class="ata-wcspc-tab" data-tab="advanced"><?php esc_html_e( 'Advanced', 'atawc_lang' ); ?></button>
					</nav>

					<div class="ata-wcspc-tab-panels">
						<div class="ata-wcspc-panel active" id="ata-panel-general">
							<div class="ata-wcspc-card">
								<h2><?php esc_html_e( 'Behavior', 'atawc_lang' ); ?></h2>
								<table class="form-table" role="presentation">
									<?php
									$this->row_start( __( 'Show after AJAX add to cart', 'atawc_lang' ), __( 'Open the mini-cart after AJAX add-to-cart on shop/archive pages.', 'atawc_lang' ) );
									$this->render_select( '_wcspc_auto_show_ajax', $opts, array( 'yes' => __( 'Yes', 'atawc_lang' ), 'no' => __( 'No', 'atawc_lang' ) ) );
									$this->row_end();

									$this->row_start( __( 'Show after normal add to cart', 'atawc_lang' ), __( 'Open after classic add-to-cart or single product add.', 'atawc_lang' ) );
									$this->render_select( '_wcspc_auto_show_normal', $opts, array( 'yes' => __( 'Yes', 'atawc_lang' ), 'no' => __( 'No', 'atawc_lang' ) ) );
									$this->row_end();

									$this->row_start( __( 'Manual trigger selector', 'atawc_lang' ), __( 'CSS class or ID that opens the cart. Example: .mini-cart-btn', 'atawc_lang' ) );
									$this->render_text( '_wcspc_manual_show', $opts );
									$this->row_end();

									$this->row_start( __( 'Fly cart position', 'atawc_lang' ), __( 'Where the panel appears. Center acts like a modal.', 'atawc_lang' ) );
									$this->render_select(
										'_wcspc_position',
										$opts,
										array(
											'01' => __( '01 – Page Right', 'atawc_lang' ),
											'02' => __( '02 – Page Left', 'atawc_lang' ),
											'03' => __( '03 – Page Top', 'atawc_lang' ),
											'04' => __( '04 – Page Bottom', 'atawc_lang' ),
											'05' => __( '05 – Page Center (Modal)', 'atawc_lang' ),
										)
									);
									$this->row_end();

									$this->row_start( __( 'Show total / subtotal', 'atawc_lang' ), __( 'Show cart subtotal in the panel footer.', 'atawc_lang' ) );
									$this->render_select( '_wcspc_total', $opts, array( 'yes' => __( 'Yes', 'atawc_lang' ), 'no' => __( 'No', 'atawc_lang' ) ) );
									$this->row_end();

									$this->row_start( __( 'Action buttons', 'atawc_lang' ), __( 'Cart and/or Checkout links at the bottom of the panel.', 'atawc_lang' ) );
									$this->render_select(
										'_wcspc_buttons',
										$opts,
										array(
											'01' => __( 'Cart & Checkout', 'atawc_lang' ),
											'02' => __( 'Cart only', 'atawc_lang' ),
											'03' => __( 'Checkout only', 'atawc_lang' ),
										)
									);
									$this->row_end();

									$this->row_start( __( 'Continue shopping', 'atawc_lang' ), __( 'Show a control that closes the mini-cart.', 'atawc_lang' ) );
									$this->render_select( '_wcspc_continue', $opts, array( 'yes' => __( 'Yes', 'atawc_lang' ), 'no' => __( 'No', 'atawc_lang' ) ) );
									$this->row_end();
									?>
								</table>
							</div>
						</div>

						<div class="ata-wcspc-panel" id="ata-panel-design">
							<div class="ata-wcspc-card">
								<h2><?php esc_html_e( 'Basic colors', 'atawc_lang' ); ?></h2>
								<p class="ata-card-intro"><?php esc_html_e( 'Free edition includes core panel colors. PRO unlocks item, quantity, and button styling.', 'atawc_lang' ); ?></p>
								<table class="form-table" role="presentation">
									<?php
									$this->row_start( __( 'Panel background color', 'atawc_lang' ), __( 'Main background of the fly cart panel.', 'atawc_lang' ) );
									$this->render_text( '_wcspc_bg_colors', $opts, true );
									$this->row_end();

									$this->row_start( __( 'Primary text color', 'atawc_lang' ), __( 'Default text and icons inside the panel.', 'atawc_lang' ) );
									$this->render_text( '_wcspc_primary_colors', $opts, true );
									$this->row_end();

									$this->row_start( __( 'Accent color', 'atawc_lang' ), __( 'Highlights and action accents.', 'atawc_lang' ) );
									$this->render_text( '_wcspc_secondary_colors', $opts, true );
									$this->row_end();
									?>
								</table>
							</div>
							<div class="ata-wcspc-card ata-wcspc-locked">
								<h2><?php esc_html_e( 'Advanced design', 'atawc_lang' ); ?> <span class="ata-wcspc-pro-badge"><?php esc_html_e( 'Pro', 'atawc_lang' ); ?></span></h2>
								<p><?php esc_html_e( 'Item backgrounds, quantity control colors, Cart/Checkout button styles, close icon color, background image — available in PRO.', 'atawc_lang' ); ?></p>
								
							</div>
						</div>

						<div class="ata-wcspc-panel" id="ata-panel-cart-button">
							<div class="ata-wcspc-card">
								<h2><?php esc_html_e( 'Floating cart button', 'atawc_lang' ); ?></h2>
								<table class="form-table" role="presentation">
									<?php
									$this->row_start( __( 'Show floating button', 'atawc_lang' ), __( 'Fixed corner button that opens the mini-cart.', 'atawc_lang' ) );
									$this->render_select( '_wcspc_count', $opts, array( 'yes' => __( 'Yes', 'atawc_lang' ), 'no' => __( 'No', 'atawc_lang' ) ) );
									$this->row_end();

									$this->row_start( __( 'Button position', 'atawc_lang' ), __( 'Corner of the screen.', 'atawc_lang' ) );
									$this->render_select(
										'_wcspc_count_position',
										$opts,
										array(
											'top-left'     => __( 'Top Left', 'atawc_lang' ),
											'top-right'    => __( 'Top Right', 'atawc_lang' ),
											'bottom-left'  => __( 'Bottom Left', 'atawc_lang' ),
											'bottom-right' => __( 'Bottom Right', 'atawc_lang' ),
										)
									);
									$this->row_end();

									$this->row_start( __( 'Cart icon', 'atawc_lang' ), __( 'Icon style for the floating button.', 'atawc_lang' ) );
									$icons = array();
									for ( $i = 1; $i <= 10; $i++ ) {
										$icons[ 'wcspc-icon-cart' . $i ] = 'wcspc-icon-cart' . $i;
									}
									$this->render_select( '_wcspc_count_icon', $opts, $icons );
									echo '<span id="wcspc_count_icon_view" class="ata-wcspc-icon-preview"></span>';
									$this->row_end();

									$this->row_start( __( 'Hide when empty', 'atawc_lang' ), __( 'Hide the button until the cart has items.', 'atawc_lang' ) );
									$this->render_select( '_wcspc_count_hide_empty', $opts, array( 'yes' => __( 'Yes', 'atawc_lang' ), 'no' => __( 'No', 'atawc_lang' ) ) );
									$this->row_end();

									$this->row_start( __( 'Hide on Cart & Checkout', 'atawc_lang' ), __( 'Hide on cart and checkout pages.', 'atawc_lang' ) );
									$this->render_select( '_wcspc_count_hide_checkout', $opts, array( 'yes' => __( 'Yes', 'atawc_lang' ), 'no' => __( 'No', 'atawc_lang' ) ) );
									$this->row_end();
									?>
								</table>
							</div>
							<div class="ata-wcspc-card ata-wcspc-locked">
								<h2><?php esc_html_e( 'Button colors', 'atawc_lang' ); ?> <span class="ata-wcspc-pro-badge"><?php esc_html_e( 'Pro', 'atawc_lang' ); ?></span></h2>
								<p><?php esc_html_e( 'Custom floating button background, icon color, and badge colors are available in PRO.', 'atawc_lang' ); ?></p>
							</div>
						</div>

						<div class="ata-wcspc-panel" id="ata-panel-advanced">
							<div class="ata-wcspc-card">
								<h2><?php esc_html_e( 'Advanced', 'atawc_lang' ); ?></h2>
								<table class="form-table" role="presentation">
									<?php
									$this->row_start( __( 'Reload cart on page load', 'atawc_lang' ), __( 'Recommended if you use full-page caching.', 'atawc_lang' ) );
									$this->render_select( '_wcspc_reload', $opts, array( 'yes' => __( 'Yes', 'atawc_lang' ), 'no' => __( 'No', 'atawc_lang' ) ) );
									$this->row_end();
									?>
								</table>
							</div>
							<div class="ata-wcspc-card ata-wcspc-locked">
								<h2><?php esc_html_e( 'More features', 'atawc_lang' ); ?> <span class="ata-wcspc-pro-badge"><?php esc_html_e( 'Pro', 'atawc_lang' ); ?></span></h2>
								<ul>
									<li><?php esc_html_e( 'Free shipping progress bar', 'atawc_lang' ); ?></li>
									<li><?php esc_html_e( 'Custom empty cart message', 'atawc_lang' ); ?></li>
									<li><?php esc_html_e( 'Full design controls (qty, items, buttons)', 'atawc_lang' ); ?></li>
									<li><?php esc_html_e( 'Background image for the panel', 'atawc_lang' ); ?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="ata-wcspc-footer">
					<?php submit_button( __( 'Save Settings', 'atawc_lang' ), 'primary large', 'submit', false ); ?>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * @param string $name    Key.
	 * @param array  $opts    Options.
	 * @param array  $choices Choices.
	 */
	protected function render_select( $name, $opts, $choices ) {
		$current = isset( $opts[ $name ] ) ? $opts[ $name ] : '';
		printf( '<select name="%s[%s]" id="%s">', esc_attr( $this->option_name ), esc_attr( $name ), esc_attr( $name ) );
		foreach ( $choices as $value => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	/**
	 * @param string $name         Key.
	 * @param array  $opts         Options.
	 * @param bool   $color_picker Color picker.
	 */
	protected function render_text( $name, $opts, $color_picker = false ) {
		$value = isset( $opts[ $name ] ) ? $opts[ $name ] : '';
		$class = $color_picker ? 'ata-wcspc-color-picker' : 'regular-text';
		printf(
			'<input type="text" class="%s" name="%s[%s]" id="%s" value="%s" />',
			esc_attr( $class ),
			esc_attr( $this->option_name ),
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}
}
