<?php
/**
 * PRO plugin / license state for premium templates and blocks.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolves the state of the PRO version:
 * - none     PRO plugin is not active            -> "Buy PRO"
 * - inactive PRO plugin active, no active license -> "Activate License"
 * - expired  license expired                     -> "Renew License"
 * - active   license active                      -> import allowed
 *
 * The premium files themselves are never part of this plugin: the PRO plugin hands over their folder
 * (filter envo_blocks_premium_source) only while its license is active.
 */
final class Envo_Blocks_License {

	const STATE_NONE     = 'none';
	const STATE_INACTIVE = 'inactive';
	const STATE_EXPIRED  = 'expired';
	const STATE_ACTIVE   = 'active';

	/**
	 * Runtime cache.
	 *
	 * @var string|null
	 */
	private static $state = null;

	/**
	 * Settings from data/license.php (filterable).
	 *
	 * @return array
	 */
	private static function config() {
		$defaults = array(
			'pro_plugin'     => '',
			'license_option' => '',
			'buy_url'        => '',
			'renew_url'      => '',
			'activate_url'   => '',
		);

		$config = include ENVO_BLOCKS_PATH . 'data/license.php';
		$config = wp_parse_args( is_array( $config ) ? $config : array(), $defaults );

		return (array) apply_filters( 'envo_blocks_license_config', $config );
	}

	/**
	 * Whether the PRO plugin is active.
	 *
	 * @return bool
	 */
	private static function is_pro_plugin_active() {
		$config = self::config();
		$file   = isset( $config['pro_plugin'] ) ? (string) $config['pro_plugin'] : '';

		if ( 1 !== preg_match( '/^[A-Za-z0-9_\-\/.]+\.php$/', $file ) || false !== strpos( $file, '..' ) ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $file );
	}

	/**
	 * Current state.
	 *
	 * @return string One of the STATE_* constants.
	 */
	public static function state() {
		if ( null !== self::$state ) {
			return self::$state;
		}

		if ( ! self::is_pro_plugin_active() ) {
			$state = self::STATE_NONE;
		} else {
			if ( function_exists( 'ebp_license_status' ) ) {
				// The PRO plugin knows best (it also checks the expiry date).
				$status = (string) ebp_license_status();
			} else {
				$config  = self::config();
				$option  = isset( $config['license_option'] ) ? (string) $config['license_option'] : '';
				$license = ( '' !== $option ) ? get_site_option( $option ) : false;
				$status  = ( is_array( $license ) && isset( $license['status'] ) && is_string( $license['status'] ) ) ? $license['status'] : '';
			}

			if ( 'active' === $status ) {
				$state = self::STATE_ACTIVE;
			} elseif ( 'expired' === $status ) {
				$state = self::STATE_EXPIRED;
			} else {
				$state = self::STATE_INACTIVE;
			}
		}

		$state = apply_filters( 'envo_blocks_license_state', $state );

		if ( ! in_array( $state, array( self::STATE_NONE, self::STATE_INACTIVE, self::STATE_EXPIRED, self::STATE_ACTIVE ), true ) ) {
			$state = self::STATE_NONE;
		}

		self::$state = $state;
		return $state;
	}

	/**
	 * Whether premium templates may be imported according to the license.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return self::STATE_ACTIVE === self::state();
	}

	/**
	 * Button shown instead of "Import" for premium templates that cannot be imported.
	 *
	 * @return array Array with "type" (buy|activate|renew) and "url".
	 */
	public static function action() {
		$config = self::config();

		switch ( self::state() ) {
			case self::STATE_INACTIVE:
				$type = 'activate';
				break;
			case self::STATE_EXPIRED:
				$type = 'renew';
				break;
			default:
				$type = 'buy';
		}

		$raw = isset( $config[ $type . '_url' ] ) ? (string) $config[ $type . '_url' ] : '';

		return array(
			'type' => $type,
			'url'  => esc_url_raw( $raw, array( 'http', 'https' ) ),
		);
	}
}
