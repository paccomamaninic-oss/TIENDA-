<?php
/**
 * "Envo Blocks Canvas" page template (no header and footer of the theme) for themes that have no canvas template of
 * their own. Templates with their own header and footer are imported into it (the template window preselects it).
 *
 * - Block themes: a block template registered with register_block_template() – only the page content.
 * - Classic themes: a page template in the Template list, rendered from views/canvas.php (wp_head, content, wp_footer).
 *
 * Nothing changes on the site until a page uses the template.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Canvas page template.
 */
final class Envo_Blocks_Canvas {

	const SLUG = 'envo-blocks-canvas';

	/**
	 * Whether the theme has a canvas template (cached for the request).
	 *
	 * @var bool|null
	 */
	private static $theme_has = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block_template' ) );
		foreach ( array( 'page', 'post' ) as $type ) {
			add_filter( "theme_{$type}_templates", array( __CLASS__, 'add_classic_template' ), 10, 4 );
		}
		add_filter( 'template_include', array( __CLASS__, 'classic_template' ), 99 );
		add_action( 'added_post_meta', array( __CLASS__, 'remember_use' ), 10, 4 );
		add_action( 'updated_post_meta', array( __CLASS__, 'remember_use' ), 10, 4 );
		add_action( 'save_post_wp_template', array( __CLASS__, 'forget_theme_canvas' ) );
		add_action( 'switch_theme', array( __CLASS__, 'forget_theme_canvas' ) );
		add_filter( 'get_block_templates', array( __CLASS__, 'reindex_templates' ), PHP_INT_MAX );
	}

	/**
	 * WordPress 7.x keys the templates registered by plugins with their IDs in get_block_templates() (array_merge
	 * of a list with 'envo-blocks//envo-blocks-canvas' => template), and wp_get_post_content_block_attributes()
	 * reads [0]: the editor of a page that uses the canvas ended with a fatal error (parse_blocks( null )) in themes
	 * without a canvas of their own. The list gets numbered keys again.
	 *
	 * @param WP_Block_Template[] $templates Templates.
	 * @return WP_Block_Template[]
	 */
	public static function reindex_templates( $templates ) {
		return is_array( $templates ) ? array_values( $templates ) : $templates;
	}

	/**
	 * Remembers that a page or post uses the canvas, so the template stays available after a switch to a theme with
	 * its own canvas (the page keeps its look instead of getting the theme's header and footer).
	 *
	 * @param int    $meta_id    Meta ID.
	 * @param int    $object_id  Post ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value.
	 */
	public static function remember_use( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( '_wp_page_template' === $meta_key && self::SLUG === $meta_value && ! get_option( 'envo_blocks_canvas_used' ) ) {
			update_option( 'envo_blocks_canvas_used', 1 );
		}
	}

	/**
	 * Title of the template in the Template lists.
	 *
	 * @return string
	 */
	private static function title() {
		return __( 'Envo Blocks Canvas (no header and footer)', 'envo-blocks' );
	}

	/**
	 * Whether the active theme has its own canvas / blank template (then that one is used and ours is not added).
	 *
	 * @return bool
	 */
	public static function theme_has_canvas() {
		if ( null !== self::$theme_has ) {
			return self::$theme_has;
		}
		// Reading the templates of a block theme takes ~50 ms, so the answer is kept per theme and version
		// (saving a template in the Site Editor clears it).
		$theme = wp_get_theme();
		$key   = $theme->get_stylesheet() . '|' . $theme->get( 'Version' );
		$saved = get_transient( 'envo_blocks_theme_canvas' );
		if ( is_array( $saved ) && isset( $saved['theme'], $saved['has'] ) && $key === $saved['theme'] ) {
			self::$theme_has = (bool) $saved['has'];
			return self::$theme_has;
		}

		self::$theme_has = false;
		// Block themes list their custom templates (theme.json) here too.
		foreach ( $theme->get_page_templates( null, 'page' ) as $slug => $title ) {
			if ( self::SLUG !== $slug && 1 === preg_match( '/canvas|blank|no[-_ ]?header/i', $slug . ' ' . $title ) ) {
				self::$theme_has = true;
				break;
			}
		}
		set_transient(
			'envo_blocks_theme_canvas',
			array(
				'theme' => $key,
				'has'   => self::$theme_has,
			),
			WEEK_IN_SECONDS
		);
		return self::$theme_has;
	}

	/**
	 * Forgets the saved answer of theme_has_canvas() when templates change.
	 */
	public static function forget_theme_canvas() {
		delete_transient( 'envo_blocks_theme_canvas' );
	}

	/**
	 * Block themes: the canvas as a block template (WordPress 6.7+).
	 */
	public static function register_block_template() {
		// Not needed when the theme has its own canvas – unless a page already uses ours.
		if ( ! wp_is_block_theme() || ! function_exists( 'register_block_template' ) || ( self::theme_has_canvas() && ! get_option( 'envo_blocks_canvas_used' ) ) ) {
			return;
		}

		// Full width main area with the page content only (no template parts).
		$content = '<!-- wp:group {"tagName":"main","align":"full","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained"}} -->'
			. '<main class="wp-block-group alignfull" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">'
			. '<!-- wp:post-content {"align":"full","style":{"spacing":{"blockGap":"0","padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"100%"}} /-->'
			. '</main><!-- /wp:group -->';

		register_block_template(
			'envo-blocks//' . self::SLUG,
			array(
				'title'       => self::title(),
				'description' => __( 'Only the content of the page, without the header and footer of the theme. For pages that have their own header and footer, such as the Envo Blocks templates.', 'envo-blocks' ),
				'content'     => $content,
				'post_types'  => array( 'page', 'post' ),
			)
		);
	}

	/**
	 * Classic themes: adds the canvas to the Template list of pages and posts.
	 *
	 * @param array $templates Page templates of the theme (file => name).
	 * @return array
	 */
	public static function add_classic_template( $templates ) {
		if ( wp_is_block_theme() || ! is_array( $templates ) ) {
			return $templates;
		}
		// Look at the theme's own list (before this filter adds ours).
		if ( null === self::$theme_has ) {
			self::$theme_has = false;
			foreach ( $templates as $slug => $title ) {
				if ( 1 === preg_match( '/canvas|blank|no[-_ ]?header/i', $slug . ' ' . $title ) ) {
					self::$theme_has = true;
					break;
				}
			}
		}
		if ( ! self::$theme_has ) {
			$templates[ self::SLUG ] = self::title();
		}
		return $templates;
	}

	/**
	 * Classic themes: renders a page or post that uses the canvas with views/canvas.php.
	 *
	 * @param string $template Template file chosen by WordPress.
	 * @return string
	 */
	public static function classic_template( $template ) {
		if ( wp_is_block_theme() || ! is_singular() || self::SLUG !== get_page_template_slug() ) {
			return $template;
		}
		return ENVO_BLOCKS_PATH . 'views/canvas.php';
	}
}
