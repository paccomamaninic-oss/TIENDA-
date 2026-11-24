<?php
/**
 * Block Editor integration.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads the editor script and styles.
 */
final class Envo_Blocks_Editor {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Enqueues assets, only in the post editor and only for users who can edit posts.
	 */
	public static function enqueue() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		wp_enqueue_style(
			'envo-blocks-editor',
			ENVO_BLOCKS_URL . 'assets/css/editor.css',
			array(),
			Envo_Blocks_Blocks::ver( 'assets/css/editor.css' )
		);

		wp_enqueue_script(
			'envo-blocks-editor',
			ENVO_BLOCKS_URL . 'assets/js/editor.js',
			array(
				'wp-api-fetch',
				'wp-blocks',
				'wp-components',
				'wp-data',
				'wp-edit-post',
				'wp-editor',
				'wp-element',
				'wp-i18n',
				'wp-plugins',
				'wp-url',
			),
			Envo_Blocks_Blocks::ver( 'assets/js/editor.js' ),
			true
		);

		wp_localize_script( 'envo-blocks-editor', 'envoBlocksData', self::data() );

		wp_set_script_translations( 'envo-blocks-editor', 'envo-blocks' );
	}

	/**
	 * Data for the script (the editor and the Envo Blocks > Templates admin page).
	 *
	 * @return array
	 */
	public static function data() {
		$theme = wp_get_theme( Envo_Blocks_Library::THEME['slug'] );

		return array(
			'pluginName'    => __( 'Envo Blocks', 'envo-blocks' ),
			// Our theme for the banner of the Templates Import page (its status comes with the templates).
			'ourThemeSlug'  => Envo_Blocks_Library::THEME['slug'],
			'themeShot'     => $theme->exists() ? esc_url_raw( (string) $theme->get_screenshot() ) : '',
			'wizardUrl'     => esc_url_raw( Envo_Blocks_Admin::wizard_url() ),
			'caps'          => Envo_Blocks_Installer::capabilities(),
			'pluginsUrl'    => esc_url_raw( admin_url( 'plugin-install.php' ) ),
			'themesUrl'     => esc_url_raw( admin_url( 'theme-install.php' ) ),
			'logo'          => esc_url( add_query_arg( 'ver', Envo_Blocks_Blocks::ver( 'assets/images/logo.svg' ), ENVO_BLOCKS_URL . 'assets/images/logo.svg' ) ),
			'pageTemplates' => self::page_templates(),
		);
	}

	/**
	 * Page templates of the active theme a template can be imported with, per post type: "" is the default
	 * template; "canvas" marks templates without the theme header and footer.
	 *
	 * @return array post type => list of array( slug, title, canvas ).
	 */
	public static function page_templates() {
		$out = array();
		foreach ( array( 'page', 'post' ) as $type ) {
			$list = array(
				array(
					'slug'   => '',
					'title'  => __( 'Default template', 'envo-blocks' ),
					'canvas' => false,
				),
			);
			foreach ( wp_get_theme()->get_page_templates( null, $type ) as $slug => $title ) {
				$list[] = array(
					'slug'   => (string) $slug,
					'title'  => wp_strip_all_tags( (string) $title ),
					'canvas' => 1 === preg_match( '/canvas|blank|no[-_ ]?header/i', $slug . ' ' . $title ),
				);
			}
			$out[ $type ] = $list;
		}
		return $out;
	}
}
