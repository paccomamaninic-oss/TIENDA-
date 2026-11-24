<?php
/**
 * Installs and activates the plugins and themes that templates require or recommend.
 *
 * Only WordPress.org plugins and themes are installed, only the slugs named in the template data
 * (see Envo_Blocks_Library::dependency_slugs()), and only for users who may install them.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin and theme status, installation and activation.
 */
final class Envo_Blocks_Installer {

	/**
	 * Main file of an installed plugin (folder/file.php) for a WordPress.org slug, or an empty string.
	 *
	 * @param string $slug Plugin slug (its folder).
	 * @return string
	 */
	public static function plugin_file( $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$found = '';
		foreach ( array_keys( get_plugins() ) as $file ) {
			if ( 0 !== strpos( $file, $slug . '/' ) ) {
				continue;
			}
			// Prefer folder/folder.php when the folder has more plugin files.
			if ( $slug . '/' . $slug . '.php' === $file ) {
				return $file;
			}
			if ( '' === $found ) {
				$found = $file;
			}
		}

		return $found;
	}

	/**
	 * Status of a plugin for the editor: active, installed (not active) or missing.
	 *
	 * @param array $plugin array( 'slug' => …, 'name' => … ).
	 * @return array
	 */
	public static function plugin_status( $plugin ) {
		$file = self::plugin_file( $plugin['slug'] );

		if ( '' === $file ) {
			$status = 'missing';
		} else {
			$status = is_plugin_active( $file ) ? 'active' : 'installed';
		}

		return array(
			'slug'   => $plugin['slug'],
			'name'   => $plugin['name'],
			'status' => $status,
		);
	}

	/**
	 * Status of a theme: active, installed or missing.
	 *
	 * @param array $theme array( 'slug' => …, 'name' => … ).
	 * @return array
	 */
	public static function theme_status( $theme ) {
		$installed = wp_get_theme( $theme['slug'] );

		if ( ! $installed->exists() ) {
			$status = 'missing';
		} else {
			// A child theme of it counts as active too.
			$status = in_array( $theme['slug'], array( get_stylesheet(), get_template() ), true ) ? 'active' : 'installed';
		}

		$info  = 'missing' === $status && current_user_can( 'install_themes' ) ? self::wporg_theme( $theme['slug'] ) : null;
		$wporg = null !== $info;

		return array(
			'slug'     => $theme['slug'],
			'name'     => $theme['name'],
			'status'   => $status,
			'download' => isset( $theme['download'] ) ? $theme['download'] : '',
			'link'     => isset( $theme['link'] ) ? $theme['link'] : '',
			// A missing theme that WordPress.org has: the editor, Templates Import and the store wizard show
			// "Install & activate" instead of the link to its page. Asked only when it matters (missing + allowed).
			'wporg'    => $wporg,
			// Its screenshot on WordPress.org while it is not installed (the installed one comes from the theme).
			'screenshot' => $wporg ? (string) $info['screenshot'] : '',
		);
	}

	/**
	 * Whether WordPress.org has the theme (themes_api), so it can be installed from there.
	 *
	 * @param string $slug Theme slug.
	 * @return bool
	 */
	public static function theme_on_wporg( $slug ) {
		return null !== self::wporg_theme( $slug );
	}

	/**
	 * The theme on WordPress.org: array( 'screenshot' => address ) when it is there, else null. The answer is kept for
	 * a day; "not found" for a few hours (a theme that has just been approved shows up soon), a failed connection for
	 * an hour.
	 *
	 * @param string $slug Theme slug.
	 * @return array|null
	 */
	private static function wporg_theme( $slug ) {
		static $known = array();

		$slug = sanitize_key( $slug );
		if ( array_key_exists( $slug, $known ) ) {
			return $known[ $slug ];
		}

		$key    = 'envo_blocks_wporg_theme_' . md5( $slug );
		$cached = get_transient( $key );
		if ( false !== $cached ) {
			$known[ $slug ] = is_array( $cached ) ? $cached : null;
			return $known[ $slug ];
		}

		require_once ABSPATH . 'wp-admin/includes/theme.php';

		$api = themes_api(
			'theme_information',
			array(
				'slug'   => $slug,
				'fields' => array(
					'sections'    => false,
					'tags'        => false,
					'screenshots' => false,
				),
			)
		);

		if ( ! is_wp_error( $api ) && is_object( $api ) && ! empty( $api->download_link ) ) {
			// WordPress.org gives the screenshot as //ts.w.org/… .
			$shot = isset( $api->screenshot_url ) ? (string) $api->screenshot_url : '';
			$shot = 0 === strpos( $shot, '//' ) ? 'https:' . $shot : $shot;
			$info = array( 'screenshot' => esc_url_raw( $shot, array( 'https' ) ) );
			set_transient( $key, $info, DAY_IN_SECONDS );
		} else {
			// "Not found" (an answer of WordPress.org, an error without data) is kept longer; a failed connection or
			// an unreadable answer (error data = the reason) is asked again sooner.
			$not_found = is_wp_error( $api ) && null === $api->get_error_data();
			set_transient( $key, 'no', $not_found ? 6 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
			$info = null;
		}

		$known[ $slug ] = $info;
		return $info;
	}

	/**
	 * Our theme (Envo Blocks Store) for the store wizard and other places outside the template list: its status,
	 * whether it can be installed from WordPress.org and what the current user may do with it.
	 *
	 * @return array
	 */
	public static function our_theme() {
		$theme = self::theme_status( Envo_Blocks_Library::THEME );

		$theme['canInstall']  = current_user_can( 'install_themes' );
		$theme['canActivate'] = current_user_can( 'switch_themes' );

		return $theme;
	}

	/**
	 * What the current user may do (shown or hidden buttons in the editor).
	 *
	 * @return array
	 */
	public static function capabilities() {
		return array(
			'installPlugins'  => current_user_can( 'install_plugins' ),
			'activatePlugins' => current_user_can( 'activate_plugins' ),
			'installThemes'   => current_user_can( 'install_themes' ),
			'switchThemes'    => current_user_can( 'switch_themes' ),
		);
	}

	/**
	 * Installs (when missing) and activates a plugin from WordPress.org.
	 *
	 * @param string $slug     Plugin slug.
	 * @param bool   $activate Activate after installing.
	 * @return array|WP_Error New status.
	 */
	public static function install_plugin( $slug, $activate ) {
		if ( ! in_array( $slug, Envo_Blocks_Library::dependency_slugs( 'plugin' ), true ) ) {
			return new WP_Error( 'envo_blocks_unknown_plugin', __( 'This plugin is not used by any template.', 'envo-blocks' ), array( 'status' => 400 ) );
		}

		$file = self::plugin_file( $slug );

		if ( '' === $file ) {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return self::forbidden();
			}

			$ready = self::filesystem_ready();
			if ( is_wp_error( $ready ) ) {
				return $ready;
			}

			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array( 'sections' => false ),
				)
			);
			if ( is_wp_error( $api ) ) {
				return self::error( $api );
			}

			$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$result   = $upgrader->install( $api->download_link );
			$error    = self::upgrader_error( $upgrader, $result );
			if ( $error ) {
				return $error;
			}

			wp_clean_plugins_cache();
			$file = self::plugin_file( $slug );
			if ( '' === $file ) {
				return new WP_Error( 'envo_blocks_install_failed', __( 'The plugin was downloaded but could not be found.', 'envo-blocks' ), array( 'status' => 500 ) );
			}
		}

		if ( $activate && ! is_plugin_active( $file ) ) {
			if ( ! current_user_can( 'activate_plugin', $file ) ) {
				return self::forbidden();
			}

			$result = activate_plugin( $file );
			if ( is_wp_error( $result ) ) {
				return self::error( $result );
			}

			// WooCommerce and others open a setup wizard on the next admin page; the user is in the editor.
			delete_transient( '_wc_activation_redirect' );
		}

		return self::plugin_status(
			array(
				'slug' => $slug,
				'name' => $slug,
			)
		);
	}

	/**
	 * Installs (when missing) and optionally activates a theme from WordPress.org.
	 *
	 * @param string $slug     Theme slug.
	 * @param bool   $activate Switch to the theme after installing.
	 * @return array|WP_Error New status.
	 */
	public static function install_theme( $slug, $activate ) {
		if ( ! in_array( $slug, Envo_Blocks_Library::dependency_slugs( 'theme' ), true ) ) {
			return new WP_Error( 'envo_blocks_unknown_theme', __( 'This theme is not recommended by any template.', 'envo-blocks' ), array( 'status' => 400 ) );
		}

		if ( ! wp_get_theme( $slug )->exists() ) {
			if ( ! current_user_can( 'install_themes' ) ) {
				return self::forbidden();
			}

			$ready = self::filesystem_ready();
			if ( is_wp_error( $ready ) ) {
				return $ready;
			}

			require_once ABSPATH . 'wp-admin/includes/theme.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$api = themes_api(
				'theme_information',
				array(
					'slug'   => $slug,
					'fields' => array( 'sections' => false ),
				)
			);
			if ( is_wp_error( $api ) ) {
				return self::error( $api );
			}

			$upgrader = new Theme_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$result   = $upgrader->install( $api->download_link );
			$error    = self::upgrader_error( $upgrader, $result );
			if ( $error ) {
				return $error;
			}

			wp_clean_themes_cache();
		}

		if ( $activate && get_stylesheet() !== $slug ) {
			if ( ! current_user_can( 'switch_themes' ) ) {
				return self::forbidden();
			}

			$theme = wp_get_theme( $slug );
			if ( ! $theme->exists() || ! $theme->is_allowed() ) {
				return new WP_Error( 'envo_blocks_theme_not_allowed', __( 'This theme cannot be activated on this site.', 'envo-blocks' ), array( 'status' => 400 ) );
			}

			switch_theme( $theme->get_stylesheet() );
		}

		return self::theme_status(
			array(
				'slug' => $slug,
				'name' => $slug,
			)
		);
	}

	/**
	 * Installing works only when WordPress can write files without asking for FTP details.
	 *
	 * @return true|WP_Error
	 */
	private static function filesystem_ready() {
		if ( ! function_exists( 'get_filesystem_method' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( 'direct' !== get_filesystem_method() || ! WP_Filesystem() ) {
			return new WP_Error(
				'envo_blocks_filesystem',
				__( 'WordPress cannot install files on this server without FTP details. Please install it in Plugins > Add New (or Appearance > Themes).', 'envo-blocks' ),
				array( 'status' => 500 )
			);
		}

		return true;
	}

	/**
	 * Error of an upgrader run, or null when it worked.
	 *
	 * @param WP_Upgrader         $upgrader Upgrader.
	 * @param bool|WP_Error|mixed $result   Result of install().
	 * @return WP_Error|null
	 */
	private static function upgrader_error( $upgrader, $result ) {
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}

		$skin_errors = $upgrader->skin->get_errors();
		if ( $skin_errors instanceof WP_Error && $skin_errors->has_errors() ) {
			return self::error( $skin_errors );
		}

		if ( ! $result ) {
			return new WP_Error( 'envo_blocks_install_failed', __( 'The installation failed.', 'envo-blocks' ), array( 'status' => 500 ) );
		}

		return null;
	}

	/**
	 * A WP_Error with a REST status and a plain-text message.
	 *
	 * @param WP_Error $error Original error.
	 * @return WP_Error
	 */
	private static function error( $error ) {
		return new WP_Error( 'envo_blocks_install_failed', wp_strip_all_tags( $error->get_error_message() ), array( 'status' => 500 ) );
	}

	/**
	 * Missing capability.
	 *
	 * @return WP_Error
	 */
	private static function forbidden() {
		return new WP_Error( 'envo_blocks_forbidden', __( 'You are not allowed to do this.', 'envo-blocks' ), array( 'status' => 403 ) );
	}
}
