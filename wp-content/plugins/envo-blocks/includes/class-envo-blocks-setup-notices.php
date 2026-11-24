<?php
/**
 * Two admin notices after the plugin was activated, one at a time, each closed for good by the user:
 *   1. "Store in 5 minutes" – a link to the store wizard (only when a plugin on the site has it, see
 *      Envo_Blocks_Admin::wizard_url()); not on the wizard screen itself;
 *   2. after it was closed (or without the wizard): a small card for our theme Envo Blocks Store with
 *      "Install & activate" (the same installer as Templates Import); never while the theme is active.
 *
 * Only for users who can use them; nothing is shown on sites where the plugin was only updated.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setup notices.
 */
final class Envo_Blocks_Setup_Notices {

	/** Option: the plugin was activated on this site, the notices may show. */
	const OPTION = 'envo_blocks_setup_notices';

	/** User meta: the user closed the wizard notice / the theme notice. */
	const META = array(
		'wizard' => 'envo_blocks_wizard_notice_dismissed',
		'theme'  => 'envo_blocks_theme_notice_dismissed',
	);

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'render' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_ajax_envo_blocks_dismiss_setup', array( __CLASS__, 'dismiss' ) );
	}

	/**
	 * Activation of the plugin: the notices may show from now on.
	 */
	public static function activated() {
		update_option( self::OPTION, 1, false );
	}

	/**
	 * Whether the wizard notice is due for this user.
	 *
	 * @return bool
	 */
	private static function wizard_due() {
		return '' !== Envo_Blocks_Admin::wizard_url()
			&& ! get_user_meta( get_current_user_id(), self::META['wizard'], true )
			// The wizard says when the store is built already (nothing left to start).
			&& ! apply_filters( 'envo_blocks_wizard_done', false );
	}

	/**
	 * Our theme for the theme notice, or null when the notice is not due for this user: closed, theme active, or
	 * nothing the user could do (install / activate).
	 *
	 * @return array|null
	 */
	private static function theme_due() {
		if ( get_user_meta( get_current_user_id(), self::META['theme'], true ) ) {
			return null;
		}
		if ( ! current_user_can( 'switch_themes' ) && ! current_user_can( 'install_themes' ) ) {
			return null;
		}
		$theme = Envo_Blocks_Installer::our_theme();
		if ( 'active' === $theme['status'] || ! apply_filters( 'envo_blocks_show_theme_notice', true ) ) {
			return null;
		}
		return $theme;
	}

	/**
	 * Which notices this screen shows: array( 'wizard' => bool, 'theme' => theme array|null ). The theme notice is
	 * printed hidden under the wizard notice, which shows it when closed.
	 *
	 * @return array
	 */
	private static function due() {
		static $due = null;
		if ( null !== $due ) {
			return $due;
		}
		$due = array(
			'wizard' => false,
			'theme'  => null,
		);
		if ( ! get_option( self::OPTION ) || is_network_admin() ) {
			return $due;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$id     = $screen ? (string) $screen->id : '';
		// Not while installing or updating something, and not on the wizard screen.
		if ( in_array( $id, array( 'update', 'plugin-install', 'theme-install', 'update-core' ), true ) || false !== strpos( $id, 'envo-blocks-wizard' ) ) {
			return $due;
		}
		$due['wizard'] = self::wizard_due();
		// Templates Import has the theme banner already.
		if ( false === strpos( $id, Envo_Blocks_Admin::SLUG . '-import' ) ) {
			$due['theme'] = self::theme_due();
		}
		return $due;
	}

	/**
	 * Script and style of the notices.
	 */
	public static function assets() {
		$due = self::due();
		if ( ! $due['wizard'] && ! $due['theme'] ) {
			return;
		}
		wp_enqueue_script( 'envo-blocks-admin-setup', ENVO_BLOCKS_URL . 'assets/js/admin-setup.js', array( 'wp-api-fetch', 'wp-i18n' ), Envo_Blocks_Blocks::ver( 'assets/js/admin-setup.js' ), true );
		wp_set_script_translations( 'envo-blocks-admin-setup', 'envo-blocks' );
		wp_add_inline_style(
			'common',
			'.eb-setup-notice .eb-setup-notice__row{display:flex;flex-wrap:wrap;gap:8px 14px;align-items:center;margin:.5em 0}'
			. '.eb-theme-notice{display:flex;gap:16px;align-items:center;padding-top:10px;padding-bottom:10px}'
			. '.eb-theme-notice[hidden]{display:none}'
			. '.eb-theme-notice__shot{flex:none;width:120px;height:auto;border:1px solid #dcdcde;border-radius:3px}'
			. '.eb-theme-notice__body{flex:1;min-width:0}.eb-theme-notice__body p{margin:.3em 0}'
			. '.eb-setup-notice__status.is-error{color:#b32d2e;font-weight:600}'
			. '@media (max-width:600px){.eb-theme-notice__shot{display:none}}'
		);
	}

	/**
	 * The notices.
	 */
	public static function render() {
		$due = self::due();
		if ( ! $due['wizard'] && ! $due['theme'] ) {
			return;
		}
		$nonce = wp_create_nonce( 'envo_blocks_dismiss_setup' );

		if ( $due['wizard'] ) {
			?>
			<div class="notice notice-info is-dismissible eb-setup-notice" data-notice="wizard" data-nonce="<?php echo esc_attr( $nonce ); ?>">
				<p class="eb-setup-notice__row">
					<span>
						<strong><?php esc_html_e( 'Store in 5 minutes', 'envo-blocks' ); ?></strong> –
						<?php esc_html_e( 'pick a template and your brand color, and we set up the pages, the front page and the colors of your store.', 'envo-blocks' ); ?>
					</span>
					<a class="button button-primary" href="<?php echo esc_url( Envo_Blocks_Admin::wizard_url() ); ?>"><?php esc_html_e( 'Start the wizard', 'envo-blocks' ); ?></a>
				</p>
			</div>
			<?php
		}

		$theme = $due['theme'];
		if ( ! $theme ) {
			return;
		}
		$action   = '';
		$activate = current_user_can( 'switch_themes' );
		if ( 'installed' === $theme['status'] && $activate ) {
			$action = __( 'Activate', 'envo-blocks' );
		} elseif ( 'missing' === $theme['status'] && $theme['wporg'] && current_user_can( 'install_themes' ) ) {
			$action = $activate ? __( 'Install & activate', 'envo-blocks' ) : __( 'Install', 'envo-blocks' );
		}
		if ( '' === $action && '' === $theme['link'] ) {
			return;
		}
		?>
		<div class="notice notice-info is-dismissible eb-setup-notice eb-theme-notice" data-notice="theme" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-slug="<?php echo esc_attr( $theme['slug'] ); ?>" data-activate="<?php echo $activate ? '1' : '0'; ?>"<?php echo $due['wizard'] ? ' hidden' : ''; ?>>
			<img class="eb-theme-notice__shot" src="<?php echo esc_url( ENVO_BLOCKS_URL . 'assets/images/envo-blocks-store-theme.webp' ); ?>" width="120" height="90" alt="">
			<div class="eb-theme-notice__body">
				<p>
					<strong>
						<?php
						/* translators: %s: theme name. */
						echo esc_html( sprintf( __( '%s – the theme our templates are made for', 'envo-blocks' ), $theme['name'] ) );
						?>
					</strong>
				</p>
				<p>
					<?php
					echo esc_html(
						'missing' === $theme['status'] && ! $theme['wporg']
							? __( 'A free block theme for WooCommerce. With it the templates look exactly like the demos; with another theme the fonts, spacing and shop pages can look different.', 'envo-blocks' )
							: __( 'A free block theme for WooCommerce from WordPress.org. With it the templates look exactly like the demos; with another theme the fonts, spacing and shop pages can look different. Your pages and products stay when you switch.', 'envo-blocks' )
					);
					?>
				</p>
				<p class="eb-setup-notice__row">
					<?php if ( '' !== $action ) : ?>
						<button type="button" class="button button-primary eb-theme-notice__button" data-install="<?php echo 'missing' === $theme['status'] ? '1' : '0'; ?>"><?php echo esc_html( $action ); ?></button>
					<?php endif; ?>
					<?php if ( '' !== $theme['link'] ) : ?>
						<a class="<?php echo '' === $action ? 'button button-primary' : ''; ?>" href="<?php echo esc_url( $theme['link'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more', 'envo-blocks' ); ?></a>
					<?php endif; ?>
					<span class="eb-setup-notice__status" role="status" aria-live="polite"></span>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX: the user closed a notice (wizard or theme).
	 */
	public static function dismiss() {
		check_ajax_referer( 'envo_blocks_dismiss_setup', 'nonce' );
		$which = isset( $_POST['notice'] ) ? sanitize_key( wp_unslash( $_POST['notice'] ) ) : '';
		if ( ! isset( self::META[ $which ] ) ) {
			wp_send_json_error( null, 400 );
		}
		update_user_meta( get_current_user_id(), self::META[ $which ], 1 );
		wp_send_json_success();
	}
}
