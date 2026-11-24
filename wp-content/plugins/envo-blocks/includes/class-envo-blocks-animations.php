<?php
/**
 * Entrance animations for every block (WordPress, WooCommerce, Envo Blocks and other plugins).
 *
 * The settings are the block attribute "envoAnimation" (only in the block comment, the saved HTML of the block does
 * not change, so turning the plugin off never makes a block invalid). On the site the outer element of the block gets
 * the class "envo-anim" and data attributes; assets/js/animations-view.js plays the animation when the block comes
 * into view. Without JavaScript, with "reduce motion" or when the script fails, the content is simply shown.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Entrance animations.
 */
final class Envo_Blocks_Animations {

	const ATTRIBUTE = 'envoAnimation';

	/**
	 * Whether a block with an animation was rendered in this request.
	 *
	 * @var bool
	 */
	private static $used = false;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'register_block_type_args', array( __CLASS__, 'add_attribute' ), 20, 2 );
		add_filter( 'render_block', array( __CLASS__, 'render' ), 20, 2 );
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 2 );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'editor_canvas' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'editor' ) );
	}

	/**
	 * Animation names (as in Animate.css and Elementor) => group. The keyframes are in assets/css/animations.css.
	 *
	 * @return array
	 */
	public static function names() {
		return array(
			'fadeIn'            => 'fade',
			'fadeInUp'          => 'fade',
			'fadeInDown'        => 'fade',
			'fadeInLeft'        => 'fade',
			'fadeInRight'       => 'fade',
			'zoomIn'            => 'zoom',
			'zoomInUp'          => 'zoom',
			'zoomInDown'        => 'zoom',
			'zoomInLeft'        => 'zoom',
			'zoomInRight'       => 'zoom',
			'zoomOut'           => 'zoom',
			'slideInUp'         => 'slide',
			'slideInDown'       => 'slide',
			'slideInLeft'       => 'slide',
			'slideInRight'      => 'slide',
			'bounceIn'          => 'bounce',
			'bounceInUp'        => 'bounce',
			'bounceInDown'      => 'bounce',
			'bounceInLeft'      => 'bounce',
			'bounceInRight'     => 'bounce',
			'flipInX'           => 'flip',
			'flipInY'           => 'flip',
			'rotateIn'          => 'rotate',
			'rotateInDownLeft'  => 'rotate',
			'rotateInDownRight' => 'rotate',
			'rotateInUpLeft'    => 'rotate',
			'rotateInUpRight'   => 'rotate',
			'lightSpeedInLeft'  => 'special',
			'lightSpeedInRight' => 'special',
			'rollIn'            => 'special',
			'blurIn'            => 'special',
			'bounce'            => 'attention',
			'flash'             => 'attention',
			'pulse'             => 'attention',
			'rubberBand'        => 'attention',
			'shake'             => 'attention',
			'headShake'         => 'attention',
			'swing'             => 'attention',
			'tada'              => 'attention',
			'wobble'            => 'attention',
			'jello'             => 'attention',
			'heartBeat'         => 'attention',
		);
	}

	/**
	 * Names of the blocks without the animation (no own wrapper element). Filter envo_blocks_animation_excluded_blocks.
	 *
	 * @return string[]
	 */
	public static function excluded_names() {
		return array_values( (array) apply_filters( 'envo_blocks_animation_excluded_blocks', array( 'core/block', 'core/pattern', 'core/missing', 'core/freeform', 'core/html', 'core/shortcode', 'core/more', 'core/nextpage', 'core/legacy-widget', 'core/widget-group', 'core/post-content', 'core/template-part' ) ) );
	}

	/**
	 * Blocks without the animation: no own wrapper element, or parts of the WooCommerce cart and checkout (rendered
	 * by their own scripts).
	 *
	 * @param string $name Block name.
	 * @return bool
	 */
	public static function is_excluded( $name ) {
		$name = (string) $name;
		if ( '' === $name ) {
			return true;
		}
		if ( in_array( $name, self::excluded_names(), true ) ) {
			return true;
		}
		foreach ( array( 'woocommerce/checkout-', 'woocommerce/cart-', 'woocommerce/filled-cart', 'woocommerce/empty-cart', 'woocommerce/mini-cart-', 'woocommerce/proceed-to-checkout' ) as $prefix ) {
			if ( 0 === strpos( $name, $prefix ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Adds the attribute to every block type, so server-rendered blocks accept it too.
	 *
	 * @param array  $args Block type arguments.
	 * @param string $name Block name.
	 * @return array
	 */
	public static function add_attribute( $args, $name ) {
		if ( self::is_excluded( $name ) ) {
			return $args;
		}
		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}
		if ( ! isset( $args['attributes'][ self::ATTRIBUTE ] ) ) {
			$args['attributes'][ self::ATTRIBUTE ] = array(
				'type'    => 'object',
				'default' => array(),
			);
		}
		return $args;
	}

	/**
	 * Sanitized settings of a block, or null without an animation.
	 *
	 * @param mixed $raw Attribute value.
	 * @return array|null
	 */
	public static function settings( $raw ) {
		if ( ! is_array( $raw ) || empty( $raw['type'] ) || ! is_string( $raw['type'] ) || ! isset( self::names()[ $raw['type'] ] ) ) {
			return null;
		}
		$speeds = array( 'slow', 'normal', 'fast' );
		return array(
			'type'    => $raw['type'],
			'speed'   => isset( $raw['speed'] ) && in_array( $raw['speed'], $speeds, true ) ? $raw['speed'] : 'normal',
			'delay'   => isset( $raw['delay'] ) ? max( 0, min( 5000, (int) $raw['delay'] ) ) : 0,
			'repeat'  => ! empty( $raw['repeat'] ),
			'stagger' => ! empty( $raw['stagger'] ),
			'step'    => isset( $raw['step'] ) ? max( 20, min( 1000, (int) $raw['step'] ) ) : 120,
			'desktop' => ! isset( $raw['desktop'] ) || ! empty( $raw['desktop'] ),
			'tablet'  => ! isset( $raw['tablet'] ) || ! empty( $raw['tablet'] ),
			'mobile'  => ! isset( $raw['mobile'] ) || ! empty( $raw['mobile'] ),
		);
	}

	/**
	 * Adds the class and the data attributes to the outer element of an animated block.
	 *
	 * @param string $content Block HTML.
	 * @param array  $block   Parsed block.
	 * @return string
	 */
	public static function render( $content, $block ) {
		if ( '' === trim( (string) $content ) || empty( $block['attrs'][ self::ATTRIBUTE ] ) || is_admin() || self::is_excluded( isset( $block['blockName'] ) ? $block['blockName'] : '' ) ) {
			return $content;
		}
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return $content; // Editor previews (ServerSideRender) show the block without the animation.
		}
		$s = self::settings( $block['attrs'][ self::ATTRIBUTE ] );
		if ( ! $s ) {
			return $content;
		}

		$tags = new WP_HTML_Tag_Processor( $content );
		if ( ! $tags->next_tag() ) {
			return $content;
		}
		$devices = array();
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			if ( $s[ $device ] ) {
				$devices[] = $device;
			}
		}
		if ( ! $devices ) {
			return $content;
		}

		$tags->add_class( 'envo-anim' );
		$tags->set_attribute( 'data-envo-anim', $s['type'] );
		$tags->set_attribute( 'data-envo-anim-speed', $s['speed'] );
		if ( $s['delay'] ) {
			$tags->set_attribute( 'data-envo-anim-delay', (string) $s['delay'] );
		}
		if ( $s['repeat'] ) {
			$tags->set_attribute( 'data-envo-anim-repeat', '1' );
		}
		if ( $s['stagger'] ) {
			$tags->set_attribute( 'data-envo-anim-stagger', (string) $s['step'] );
		}
		if ( count( $devices ) < 3 ) {
			$tags->set_attribute( 'data-envo-anim-on', implode( ' ', $devices ) );
		}

		self::$used = true;
		wp_enqueue_style( 'envo-blocks-animations' );
		wp_enqueue_script( 'envo-blocks-animations' );

		return $tags->get_updated_html();
	}

	/**
	 * Registers the style and the script of the site.
	 */
	public static function register_assets() {
		wp_register_style( 'envo-blocks-animations', ENVO_BLOCKS_URL . 'assets/css/animations.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/animations.css' ) );
		wp_register_script(
			'envo-blocks-animations',
			ENVO_BLOCKS_URL . 'assets/js/animations-view.js',
			array(),
			Envo_Blocks_Blocks::ver( 'assets/js/animations-view.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	/**
	 * Whether the page probably has animated blocks before they are rendered (classic themes render the content after
	 * wp_head; block themes render the template before it).
	 *
	 * @return bool
	 */
	private static function page_has_animations() {
		if ( self::$used ) {
			return true;
		}
		$post = is_singular() ? get_queried_object() : null;
		$has  = $post instanceof WP_Post && false !== strpos( (string) $post->post_content, '"' . self::ATTRIBUTE . '"' );
		return (bool) apply_filters( 'envo_blocks_page_has_animations', $has );
	}

	/**
	 * In the head of pages with animations: hides the animated blocks until the script shows them (no flash of the
	 * content before the animation). Shown anyway after 3 seconds when the script did not start.
	 */
	public static function head() {
		if ( ! self::page_has_animations() ) {
			return;
		}
		wp_enqueue_style( 'envo-blocks-animations' );
		wp_enqueue_script( 'envo-blocks-animations' );
		echo "<script>document.documentElement.classList.add('envo-anim-js');</script>\n";
	}

	/**
	 * The keyframes in the editor canvas, for the Play button of the settings.
	 */
	public static function editor_canvas() {
		if ( is_admin() ) {
			wp_enqueue_style( 'envo-blocks-animations' );
		}
	}

	/**
	 * The Animation panel of every block.
	 */
	public static function editor() {
		wp_enqueue_script(
			'envo-blocks-animations-editor',
			ENVO_BLOCKS_URL . 'assets/js/animations-editor.js',
			array( 'wp-hooks', 'wp-compose', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-blocks', 'wp-i18n', 'wp-data' ),
			Envo_Blocks_Blocks::ver( 'assets/js/animations-editor.js' ),
			true
		);
		wp_set_script_translations( 'envo-blocks-animations-editor', 'envo-blocks' );
		wp_add_inline_script(
			'envo-blocks-animations-editor',
			'window.envoBlocksAnimations = ' . wp_json_encode(
				array(
					'names'    => self::names(),
					'excluded' => self::excluded_names(),
					// Without Envo Blocks PRO: a short note that the scrolling and mouse effects are there.
					'proUrl'   => apply_filters( 'envo_blocks_motion_effects_active', false ) ? '' : admin_url( 'admin.php?page=envo-blocks-pro' ),
				)
			) . ';',
			'before'
		);
	}
}
