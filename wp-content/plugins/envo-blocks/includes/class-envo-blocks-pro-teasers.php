<?php
/**
 * Envo Blocks PRO blocks as locked teasers in the block inserter while the PRO plugin is not active.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every PRO block gets a teaser block "envo-blocks/pro-<name>" with the same title, description and icon (with a
 * lock). Inserted, it shows a placeholder with "Get Envo Blocks PRO", "Live demo" and "Remove block"; it saves
 * nothing visible and renders nothing on the site. A teaser is in the inserter while the real PRO block is not
 * there: without the PRO plugin, or with it but without an active license (PRO then hides its blocks). The button
 * leads to the Envo Blocks PRO page, the license page or the renewal. The teasers stay registered, so a teaser left
 * in a page can still be removed later.
 */
final class Envo_Blocks_Pro_Teasers {

	const PREFIX   = 'envo-blocks/pro-';
	const CATEGORY = 'envo-blocks-pro';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 20 );
		add_filter( 'block_categories_all', array( __CLASS__, 'category' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Whether the teasers are offered in the inserter: without an active PRO license.
	 *
	 * @return bool
	 */
	private static function show() {
		$show = Envo_Blocks_License::STATE_ACTIVE !== Envo_Blocks_License::state();
		return (bool) apply_filters( 'envo_blocks_show_pro_block_teasers', $show );
	}

	/**
	 * Whether a teaser is in the inserter: the real PRO block is not (not registered, or hidden by PRO).
	 *
	 * @param string $name Teaser name.
	 * @return bool
	 */
	private static function in_inserter( $name ) {
		if ( ! self::show() ) {
			return false;
		}
		$real = WP_Block_Type_Registry::get_instance()->get_registered( 'envo-blocks-pro/' . substr( $name, strlen( self::PREFIX ) ) );
		return ! $real || ( isset( $real->supports['inserter'] ) && false === $real->supports['inserter'] );
	}

	/**
	 * Button of the placeholder: Get PRO, Activate license or Renew license.
	 *
	 * @return array Array with type, url, label and note.
	 */
	private static function action() {
		$action = Envo_Blocks_License::action();
		if ( 'activate' === $action['type'] && '' !== $action['url'] ) {
			return array(
				'type'  => 'activate',
				'url'   => $action['url'],
				'label' => __( 'Activate license', 'envo-blocks' ),
				'note'  => __( 'Envo Blocks PRO is installed. Activate your license key to use this block.', 'envo-blocks' ),
			);
		}
		if ( 'renew' === $action['type'] && '' !== $action['url'] ) {
			return array(
				'type'  => 'renew',
				'url'   => $action['url'],
				'label' => __( 'Renew license', 'envo-blocks' ),
				'note'  => __( 'Your Envo Blocks PRO license has expired. Renew it to use this block.', 'envo-blocks' ),
			);
		}
		return array(
			'type'  => 'buy',
			'url'   => admin_url( 'admin.php?page=envo-blocks-pro' ),
			'label' => __( 'Get Envo Blocks PRO', 'envo-blocks' ),
			'note'  => '',
		);
	}

	/**
	 * The teasers: name => array( title, description, icon, demo URL ).
	 *
	 * @return array
	 */
	private static function blocks() {
		$icons = include ENVO_BLOCKS_PATH . 'data/pro-blocks.php';
		$admin = include ENVO_BLOCKS_PATH . 'data/admin.php';
		$demos = isset( $admin['urls']['block_demos'] ) ? (string) $admin['urls']['block_demos'] : '';
		$list  = array();
		foreach ( isset( $admin['pro_groups'] ) ? (array) $admin['pro_groups'] : array() as $group ) {
			foreach ( isset( $group['blocks'] ) ? (array) $group['blocks'] : array() as $key => $block ) {
				$key = sanitize_key( $key );
				if ( '' === $key || ! is_array( $block ) || empty( $block[0] ) || ( isset( $block[4] ) && 'feature' === $block[4] ) ) {
					continue;
				}
				$list[ self::PREFIX . $key ] = array(
					'title'       => (string) $block[0],
					'description' => isset( $block[1] ) ? (string) $block[1] : '',
					'icon'        => isset( $icons[ $key ] ) ? sanitize_html_class( $icons[ $key ] ) : 'block-default',
					'demo'        => ( $demos && ! empty( $block[3] ) ) ? esc_url_raw( $demos . sanitize_title( $block[3] ) . '/' ) : '',
				);
			}
		}
		return $list;
	}

	/**
	 * Registers the teaser blocks on the server: no attributes, nothing rendered on the site; in the inserter only without PRO.
	 */
	public static function register() {
		// Also loaded inside the editor canvas (iframe), where the placeholder is.
		wp_register_style( 'envo-blocks-pro-teasers', ENVO_BLOCKS_URL . 'assets/css/pro-teasers.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/pro-teasers.css' ) );
		foreach ( self::blocks() as $name => $block ) {
			if ( WP_Block_Type_Registry::get_instance()->is_registered( $name ) ) {
				continue;
			}
			register_block_type(
				$name,
				array(
					'api_version'     => 3,
					'title'           => $block['title'],
					'description'     => $block['description'],
					'category'        => self::CATEGORY,
					'icon'            => $block['icon'],
					'supports'        => array(
						'inserter' => self::in_inserter( $name ),
						'html'     => false,
						'reusable' => false,
					),
					'editor_style'    => 'envo-blocks-pro-teasers',
					'render_callback' => '__return_empty_string',
				)
			);
		}
	}

	/**
	 * "Envo Blocks PRO" section of the inserter (the same slug as the PRO plugin uses), only for the teasers.
	 *
	 * @param array $categories Categories.
	 * @return array
	 */
	public static function category( $categories ) {
		$any = false;
		foreach ( array_keys( self::blocks() ) as $name ) {
			$any = $any || self::in_inserter( $name );
		}
		if ( ! $any ) {
			return $categories;
		}
		foreach ( $categories as $category ) {
			if ( isset( $category['slug'] ) && self::CATEGORY === $category['slug'] ) {
				return $categories;
			}
		}
		$position = 0;
		foreach ( $categories as $i => $category ) {
			if ( isset( $category['slug'] ) && Envo_Blocks_Blocks::CATEGORY === $category['slug'] ) {
				$position = $i + 1;
			}
		}
		array_splice(
			$categories,
			$position,
			0,
			array(
				array(
					'slug'  => self::CATEGORY,
					'title' => __( 'Envo Blocks PRO', 'envo-blocks' ),
					'icon'  => null,
				),
			)
		);
		return $categories;
	}

	/**
	 * Editor script of the teasers.
	 */
	public static function enqueue() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$blocks = array();
		foreach ( self::blocks() as $name => $block ) {
			$blocks[] = array(
				'name'        => $name,
				'title'       => $block['title'],
				'description' => $block['description'],
				'icon'        => $block['icon'],
				'demo'        => $block['demo'],
				'inserter'    => self::in_inserter( $name ),
			);
		}

		wp_enqueue_script(
			'envo-blocks-pro-teasers',
			ENVO_BLOCKS_URL . 'assets/js/pro-teasers.js',
			array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n' ),
			Envo_Blocks_Blocks::ver( 'assets/js/pro-teasers.js' ),
			true
		);
		wp_localize_script(
			'envo-blocks-pro-teasers',
			'envoBlocksProTeasers',
			array(
				'blocks'   => $blocks,
				'category' => self::CATEGORY,
				'action'   => self::action(),
			)
		);
		wp_set_script_translations( 'envo-blocks-pro-teasers', 'envo-blocks' );

		wp_enqueue_style( 'envo-blocks-pro-teasers' ); // The inserter is outside the canvas.
	}
}
