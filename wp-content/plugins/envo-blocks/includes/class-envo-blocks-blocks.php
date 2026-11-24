<?php
/**
 * WooCommerce blocks of the free plugin (trust badges, coupon, features, delivery estimate,
 * rating summary, recently viewed products, size guide).
 *
 * Every block lives in blocks/<name>/ with its block.json and a render.php (dynamic blocks).
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the blocks, their assets and the "Envo Blocks" section of the block inserter.
 */
final class Envo_Blocks_Blocks {

	const CATEGORY = 'envo-blocks';

	/**
	 * Blocks that work without WooCommerce.
	 *
	 * @var string[]
	 */
	const BASIC = array( 'trust-badges', 'features-bar', 'delivery-estimate', 'size-guide', 'pricing-table', 'advanced-heading', 'map', 'video-popup', 'announcement-bar', 'countdown', 'opening-hours', 'faq-item', 'faq', 'form-styler', 'language-switcher', 'parallax', 'sticky-column' );

	/**
	 * Blocks that need WooCommerce.
	 *
	 * @var string[]
	 */
	const WOO = array( 'coupon-box', 'rating-summary', 'recently-viewed', 'stock-indicator', 'lowest-price', 'order-tracking', 'shipping-bar', 'wishlist-button', 'wishlist', 'payment-qr', 'invoice-download', 'product-tab', 'product-tabs', 'product-filter', 'active-filters', 'review-photos', 'product-questions', 'tax-switcher' );

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'category' ) );
		add_action( 'wp_footer', array( __CLASS__, 'track_product_view' ) );

		// The shop-wide rating counts of the Rating Summary block are cached; reviews change them.
		foreach ( array( 'comment_post', 'edit_comment', 'deleted_comment', 'wp_set_comment_status' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'flush_rating_cache' ) );
		}

		add_filter( 'render_block_woocommerce/mini-cart', array( __CLASS__, 'mini_cart_rest_url' ) );

		add_filter( 'render_block_core/group', array( __CLASS__, 'classic_content_size' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'classic_content_size_style' ) );
		add_filter( 'wp_get_attachment_image_attributes', array( __CLASS__, 'keep_image_size' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'doc_links' ) );
	}

	/**
	 * The docs of every block for the "Documentation" button of its settings (blocks-editor.js), only in the editor.
	 */
	public static function doc_links() {
		if ( wp_script_is( 'envo-blocks-blocks-editor', 'registered' ) && class_exists( 'Envo_Blocks_Admin' ) ) {
			wp_add_inline_script( 'envo-blocks-blocks-editor', 'window.envoBlocksDocs = ' . wp_json_encode( Envo_Blocks_Admin::block_doc_urls() ) . ';', 'before' );
		}
	}

	/**
	 * Twenty Twenty-One gives every image of wp_get_attachment_image() an inline width, height and max-width, which
	 * beat the sizes of the blocks (a 40 px category thumbnail became 500 px wide). Images of Envo Blocks and
	 * Envo Blocks PRO (classes envo-…, ebp-…, sb-…) are sized by the blocks' CSS, so that style is left out.
	 *
	 * @param array $attr Image attributes.
	 * @return array
	 */
	public static function keep_image_size( $attr ) {
		if ( ! empty( $attr['style'] ) && ! empty( $attr['class'] ) && preg_match( '/(^|\s)(envo|ebp|sb)-/', $attr['class'] ) ) {
			$attr['style'] = (string) preg_replace( '/^width:100%;height:[\d.]+%;max-width:\d+px;/', '', $attr['style'] );
			if ( '' === $attr['style'] ) {
				unset( $attr['style'] );
			}
		}

		return $attr;
	}

	/**
	 * Classic themes without theme.json (Twenty Twenty, Twenty Twenty-One …) cap every block inside a group at their
	 * own narrow content width, so templates, sections and store layouts squeezed their columns into ~600 px. A group
	 * with its own content width (Layout › Content width) carries it as --envo-content-size, and the style of
	 * classic_content_size_style() gives its blocks that width back.
	 *
	 * @param string $content Block HTML.
	 * @param array  $block   Parsed block.
	 * @return string
	 */
	public static function classic_content_size( $content, $block ) {
		$layout = isset( $block['attrs']['layout'] ) && is_array( $block['attrs']['layout'] ) ? $block['attrs']['layout'] : array();
		$size   = isset( $layout['contentSize'] ) ? (string) $layout['contentSize'] : '';
		if ( '' === $size || ! isset( $layout['type'] ) || 'constrained' !== $layout['type'] || ! preg_match( '/^[\w.%(), +*\/-]+$/', $size ) || wp_theme_has_theme_json() ) {
			return $content;
		}

		$tags = new WP_HTML_Tag_Processor( $content );
		if ( ! $tags->next_tag() ) {
			return $content;
		}
		$tags->set_attribute( 'style', '--envo-content-size:' . $size . ';' . (string) $tags->get_attribute( 'style' ) );

		return $tags->get_updated_html();
	}

	/**
	 * The style for classic_content_size(): blocks of such a group (inside its inner container in classic themes)
	 * get the group's content width. The :not(#envo-cs) outweighs the theme's own selectors.
	 */
	public static function classic_content_size_style() {
		if ( wp_theme_has_theme_json() ) {
			return;
		}
		$children = ':not(.alignleft):not(.alignright):not(.alignwide):not(.alignfull)';
		wp_register_style( 'envo-blocks-content-size', false, array(), ENVO_BLOCKS_VERSION );
		wp_enqueue_style( 'envo-blocks-content-size' );
		wp_add_inline_style(
			'envo-blocks-content-size',
			'.wp-block-group[style*="--envo-content-size"]:not(#envo-cs)>' . $children . ',.wp-block-group[style*="--envo-content-size"]:not(#envo-cs)>.wp-block-group__inner-container>' . $children . '{max-width:var(--envo-content-size);margin-left:auto;margin-right:auto}'
		);
	}

	/**
	 * WooCommerce 11.2: on the cart and checkout pages the Mini-Cart block still loads the cart store of the
	 * Interactivity API but skips its state, so the store fetches "<page>/undefinedwc/store/v1/cart" again and
	 * again (404s in the console). The REST root is all it misses.
	 *
	 * @param string $content Block HTML.
	 * @return string
	 */
	public static function mini_cart_rest_url( $content ) {
		if ( function_exists( 'wp_interactivity_state' ) && function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
			$state = wp_interactivity_state( 'woocommerce' );
			if ( empty( $state['restUrl'] ) ) {
				wp_interactivity_state( 'woocommerce', array( 'restUrl' => get_rest_url() ) );
			}
		}
		return $content;
	}

	/**
	 * Clears the cached shop-wide rating counts.
	 */
	public static function flush_rating_cache() {
		delete_transient( 'envo_blocks_rating_counts' );
	}

	/**
	 * Default guarantees of the Trust Badges block (translatable, so they are not in block.json).
	 *
	 * @return array
	 */
	public static function default_badges() {
		return array(
			array(
				'icon' => 'lock',
				'text' => __( 'Secure payment', 'envo-blocks' ),
			),
			array(
				'icon' => 'return',
				'text' => __( '30-day returns', 'envo-blocks' ),
			),
			array(
				'icon' => 'truck',
				'text' => __( 'Fast delivery', 'envo-blocks' ),
			),
		);
	}

	/**
	 * Default plans of the Pricing Table block. Features: one per line, "-" at the start = not included.
	 *
	 * @return array
	 */
	public static function default_plans() {
		return array(
			array(
				'name'        => __( 'Starter', 'envo-blocks' ),
				'description' => __( 'For small shops that are just starting.', 'envo-blocks' ),
				'price'       => '9',
				'period'      => __( '/ month', 'envo-blocks' ),
				'features'    => implode( "\n", array( __( '1 online store', 'envo-blocks' ), __( '100 products', 'envo-blocks' ), __( 'Email support', 'envo-blocks' ), '-' . __( 'Custom domain', 'envo-blocks' ), '-' . __( 'Advanced reports', 'envo-blocks' ) ) ),
				'buttonText'  => __( 'Get started', 'envo-blocks' ),
				'buttonUrl'   => '#',
			),
			array(
				'name'        => __( 'Professional', 'envo-blocks' ),
				'description' => __( 'Everything a growing shop needs.', 'envo-blocks' ),
				'price'       => '19',
				'oldPrice'    => '29',
				'period'      => __( '/ month', 'envo-blocks' ),
				'note'        => __( 'Billed yearly', 'envo-blocks' ),
				'badge'       => __( 'Most popular', 'envo-blocks' ),
				'featured'    => true,
				'features'    => implode( "\n", array( __( '3 online stores', 'envo-blocks' ), __( 'Unlimited products', 'envo-blocks' ), __( 'Priority support', 'envo-blocks' ), __( 'Custom domain', 'envo-blocks' ), '-' . __( 'Advanced reports', 'envo-blocks' ) ) ),
				'buttonText'  => __( 'Choose Professional', 'envo-blocks' ),
				'buttonUrl'   => '#',
			),
			array(
				'name'        => __( 'Business', 'envo-blocks' ),
				'description' => __( 'For teams and larger catalogues.', 'envo-blocks' ),
				'price'       => '49',
				'period'      => __( '/ month', 'envo-blocks' ),
				'features'    => implode( "\n", array( __( 'Unlimited stores', 'envo-blocks' ), __( 'Unlimited products', 'envo-blocks' ), __( '24/7 phone support', 'envo-blocks' ), __( 'Custom domain', 'envo-blocks' ), __( 'Advanced reports', 'envo-blocks' ) ) ),
				'buttonText'  => __( 'Contact sales', 'envo-blocks' ),
				'buttonUrl'   => '#',
			),
		);
	}

	/**
	 * Default texts of the Advanced Heading block.
	 *
	 * @return array { before, words (one per line), after }
	 */
	public static function default_heading() {
		return array(
			'before' => __( 'We build', 'envo-blocks' ),
			'words'  => implode( "\n", array( __( 'online stores', 'envo-blocks' ), __( 'websites', 'envo-blocks' ), __( 'brands', 'envo-blocks' ) ) ),
			'after'  => __( 'that sell.', 'envo-blocks' ),
		);
	}

	/**
	 * Font families of the theme (theme.json) for the Advanced Heading: slug => name.
	 *
	 * @return array
	 */
	public static function font_families() {
		$settings = function_exists( 'wp_get_global_settings' ) ? wp_get_global_settings( array( 'typography', 'fontFamilies' ) ) : array();
		$out      = array();

		foreach ( array( 'theme', 'custom', 'default' ) as $origin ) {
			if ( empty( $settings[ $origin ] ) || ! is_array( $settings[ $origin ] ) ) {
				continue;
			}
			foreach ( $settings[ $origin ] as $font ) {
				if ( isset( $font['slug'], $font['name'] ) && ! isset( $out[ $font['slug'] ] ) ) {
					$out[ sanitize_key( $font['slug'] ) ] = sanitize_text_field( $font['name'] );
				}
			}
		}

		return $out;
	}

	/**
	 * Validates a CSS length: a number (pixels) or a number with px, em, rem, %, vw, vh or ch.
	 *
	 * @param mixed $value    Raw value.
	 * @param bool  $negative Allow negative values.
	 * @return string The length, or an empty string.
	 */
	public static function length( $value, $negative = false ) {
		if ( is_int( $value ) || is_float( $value ) ) {
			$value = (string) $value;
		}
		if ( ! is_string( $value ) || ! preg_match( '/^(-?)(\d*\.?\d+)(px|em|rem|%|vw|vh|ch)?$/', trim( $value ), $m ) ) {
			return '';
		}
		if ( '-' === $m[1] && ! $negative ) {
			return '';
		}

		return $m[1] . $m[2] . ( isset( $m[3] ) && '' !== $m[3] ? $m[3] : 'px' );
	}

	/**
	 * Inline CSS of one part of the Advanced Heading (typography, colors, background, border).
	 *
	 * @param mixed $part Part settings.
	 * @return string
	 */
	public static function part_style( $part ) {
		$part  = is_array( $part ) ? $part : array();
		$style = '';

		if ( ! empty( $part['fontFamily'] ) && is_string( $part['fontFamily'] ) && preg_match( '/^[a-z0-9-]+$/', $part['fontFamily'] ) ) {
			$style .= 'font-family:var(--wp--preset--font-family--' . $part['fontFamily'] . ');';
		}

		foreach ( array( 'fontSize' => '--sb-fs', 'fontSizeMobile' => '--sb-fs-m' ) as $key => $property ) {
			$length = self::length( isset( $part[ $key ] ) ? $part[ $key ] : '' );
			if ( '' !== $length && (float) $length > 0 ) {
				$style .= $property . ':' . $length . ';';
			}
		}

		$choices = array(
			'fontWeight'    => array( 'font-weight', array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ) ),
			'fontStyle'     => array( 'font-style', array( 'normal', 'italic' ) ),
			'textTransform' => array( 'text-transform', array( 'none', 'uppercase', 'lowercase', 'capitalize' ) ),
		);
		foreach ( $choices as $key => $definition ) {
			if ( isset( $part[ $key ] ) && in_array( (string) $part[ $key ], $definition[1], true ) ) {
				$style .= $definition[0] . ':' . $part[ $key ] . ';';
			}
		}

		$spacing = self::length( isset( $part['letterSpacing'] ) ? $part['letterSpacing'] : '', true );
		if ( '' !== $spacing ) {
			$style .= 'letter-spacing:' . $spacing . ';';
		}

		if ( isset( $part['lineHeight'] ) && is_numeric( $part['lineHeight'] ) && $part['lineHeight'] > 0 ) {
			$style .= 'line-height:' . min( 4, (float) $part['lineHeight'] ) . ';';
		}

		$color = self::color( isset( $part['color'] ) ? $part['color'] : '' );
		if ( '' !== $color ) {
			$style .= 'color:' . $color . ';';
		}

		$background = self::color( isset( $part['background'] ) ? $part['background'] : '' );
		if ( '' !== $background ) {
			$style .= 'background-color:' . $background . ';';
		}

		$border = self::int( $part, 'borderWidth', 0, 0, 20 );
		if ( $border ) {
			$border_style = isset( $part['borderStyle'] ) && in_array( $part['borderStyle'], array( 'solid', 'dashed', 'dotted', 'double' ), true ) ? $part['borderStyle'] : 'solid';
			$border_color = self::color( isset( $part['borderColor'] ) ? $part['borderColor'] : '' );
			$style       .= 'border:' . $border . 'px ' . $border_style . ' ' . ( '' !== $border_color ? $border_color : 'currentColor' ) . ';';
		}

		$radius = self::int( $part, 'borderRadius', 0, 0, 200 );
		if ( $radius ) {
			$style .= 'border-radius:' . $radius . 'px;';
		}

		// Padding: by default a little space inside a background or border, none for plain text.
		$boxed = '' !== $background || $border;
		if ( $boxed || isset( $part['paddingX'] ) || isset( $part['paddingY'] ) ) {
			$style .= 'padding:' . self::int( $part, 'paddingY', $boxed ? 2 : 0, 0, 80 ) . 'px ' . self::int( $part, 'paddingX', $boxed ? 12 : 0, 0, 120 ) . 'px;';
		}

		return $style;
	}

	/**
	 * Default messages of the Announcement Bar block.
	 *
	 * @return array
	 */
	public static function default_announcements() {
		return array(
			array(
				'icon' => 'truck',
				'text' => __( 'Free shipping on orders over $50', 'envo-blocks' ),
				'url'  => '',
			),
			array(
				'icon' => 'return',
				'text' => __( '30-day returns, no questions asked', 'envo-blocks' ),
				'url'  => '',
			),
			array(
				'icon' => 'gift',
				'text' => __( 'New season – shop the collection', 'envo-blocks' ),
				'url'  => '',
			),
		);
	}

	/**
	 * Default week of the Opening Hours block: Monday … Sunday, ranges separated by commas, empty = closed.
	 *
	 * @return string[]
	 */
	public static function default_hours() {
		return array( '9:00-17:00', '9:00-17:00', '9:00-17:00', '9:00-17:00', '9:00-17:00', '9:00-12:00', '' );
	}

	/**
	 * Clock of the shop for the view scripts: the time zone name (when it is a named zone), the current offset
	 * and the locale. Pages can be cached, so the scripts work out "now" in the shop's time zone themselves.
	 *
	 * @return array
	 */
	public static function clock() {
		$tz  = wp_timezone_string();
		$now = new DateTimeImmutable( 'now', wp_timezone() );

		return array(
			'tz'     => preg_match( '/^[+-]\d{2}:\d{2}$/', $tz ) ? '' : $tz,
			'offset' => $now->getOffset(),
			'locale' => str_replace( '_', '-', determine_locale() ),
		);
	}

	/**
	 * Time ranges of a day written like "9:00-12:00, 13:00-18:00" as minutes [ start, end ]; a range that ends
	 * before it starts goes past midnight (end + 24 h). Empty or anything without a time = closed.
	 *
	 * @param string $text Ranges.
	 * @return array[]
	 */
	public static function time_ranges( $text ) {
		$out = array();
		if ( ! is_string( $text ) || ! preg_match_all( '/(\d{1,2})(?:[:.](\d{2}))?\s*(?:-|–|—)\s*(\d{1,2})(?:[:.](\d{2}))?/u', $text, $m, PREG_SET_ORDER ) ) {
			return $out;
		}
		foreach ( $m as $range ) {
			$start = min( 24, (int) $range[1] ) * 60 + min( 59, (int) ( isset( $range[2] ) && '' !== $range[2] ? $range[2] : 0 ) );
			$end   = min( 24, (int) $range[3] ) * 60 + min( 59, (int) ( isset( $range[4] ) ? $range[4] : 0 ) );
			if ( $end <= $start ) {
				$end += 1440;
			}
			if ( $start < 1440 ) {
				$out[] = array( $start, min( $end, 2880 ) );
			}
		}
		usort(
			$out,
			static function ( $a, $b ) {
				return $a[0] - $b[0];
			}
		);
		return $out;
	}

	/**
	 * Minutes after midnight in the time format of the site (Settings > General).
	 *
	 * @param int $minutes Minutes (24:00 and later wrap around).
	 * @return string
	 */
	public static function format_minutes( $minutes ) {
		return wp_date( get_option( 'time_format', 'G:i' ), ( (int) $minutes % 1440 ) * 60, new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Default items of the Features Bar block.
	 *
	 * @return array
	 */
	public static function default_features() {
		return array(
			array(
				'icon'  => 'truck',
				'title' => __( 'Free shipping', 'envo-blocks' ),
				'text'  => __( 'On qualifying orders', 'envo-blocks' ),
			),
			array(
				'icon'  => 'return',
				'title' => __( 'Easy returns', 'envo-blocks' ),
				'text'  => __( '30 days to change your mind', 'envo-blocks' ),
			),
			array(
				'icon'  => 'support',
				'title' => __( 'Friendly support', 'envo-blocks' ),
				'text'  => __( 'We are here to help', 'envo-blocks' ),
			),
			array(
				'icon'  => 'lock',
				'title' => __( 'Secure payment', 'envo-blocks' ),
				'text'  => __( '100% protected checkout', 'envo-blocks' ),
			),
		);
	}

	/**
	 * Registers assets and blocks.
	 */
	public static function register() {
		$view = array(
			'strategy'  => 'defer',
			'in_footer' => true,
		);

		wp_register_script(
			'envo-blocks-blocks-editor',
			ENVO_BLOCKS_URL . 'assets/js/blocks-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-server-side-render', 'wp-data', 'wp-api-fetch' ),
			self::ver( 'assets/js/blocks-editor.js' ),
			true
		);
		wp_set_script_translations( 'envo-blocks-blocks-editor', 'envo-blocks' );
		wp_add_inline_script(
			'envo-blocks-blocks-editor',
			'window.envoBlocksBlocks = ' . wp_json_encode(
				array(
					'woo'      => class_exists( 'WooCommerce' ),
					'typeTemplates' => version_compare( get_bloginfo( 'version' ), '7.2-alpha', '>=' ), // Inner block templates in the block type settings.
					'icons'    => self::icon_names(),
					'iconSvgs' => array_combine( self::icon_names(), array_map( array( __CLASS__, 'icon' ), self::icon_names() ) ),
					'payments' => self::payment_names(),
					'badges'   => self::default_badges(),
					'features' => self::default_features(),
					'announcements' => self::default_announcements(),
					'hours'    => self::default_hours(),
					'plans'    => self::default_plans(),
					'heading'  => self::default_heading(),
					'fonts'    => self::font_families(),
					'wishlist' => class_exists( 'Envo_Blocks_Wishlist' ) && class_exists( 'WooCommerce' ) ? Envo_Blocks_Wishlist::editor_data() : null,
					'bankAccounts' => class_exists( 'Envo_Blocks_Payment_QR' ) && class_exists( 'WooCommerce' ) ? Envo_Blocks_Payment_QR::account_options() : array(),
					'filters'  => class_exists( 'WooCommerce' ) ? array(
						'attributes' => array_map(
							static function ( $taxonomy ) {
								return array(
									'value' => $taxonomy,
									'label' => wc_attribute_label( $taxonomy ),
								);
							},
							wc_get_attribute_taxonomy_names()
						),
						'brands'     => taxonomy_exists( 'product_brand' ),
						'pro'        => Envo_Blocks_Filters::pro(),
					) : null,
					'pro'      => defined( 'EBP_VERSION' ),
					// "Learn more" of the PRO notes in the block settings.
					'proUrl'   => esc_url_raw( (string) apply_filters( 'envo_blocks_pro_url', 'https://envoblocks.com/' ) ),
				)
			) . ';',
			'before'
		);
		wp_register_style( 'envo-blocks-blocks-editor', ENVO_BLOCKS_URL . 'assets/css/blocks-editor.css', array(), self::ver( 'assets/css/blocks-editor.css' ) );

		wp_register_style( 'envo-blocks-blocks', ENVO_BLOCKS_URL . 'assets/css/blocks.css', array(), self::ver( 'assets/css/blocks.css' ) );

		wp_register_script( 'envo-blocks-coupon', ENVO_BLOCKS_URL . 'assets/js/coupon-view.js', array(), self::ver( 'assets/js/coupon-view.js' ), $view );
		wp_register_script( 'envo-blocks-delivery', ENVO_BLOCKS_URL . 'assets/js/delivery-view.js', array(), self::ver( 'assets/js/delivery-view.js' ), $view );
		wp_register_script( 'envo-blocks-recent', ENVO_BLOCKS_URL . 'assets/js/recently-viewed-view.js', array(), self::ver( 'assets/js/recently-viewed-view.js' ), $view );
		wp_register_script( 'envo-blocks-heading', ENVO_BLOCKS_URL . 'assets/js/heading-view.js', array(), self::ver( 'assets/js/heading-view.js' ), $view );
		wp_register_script( 'envo-blocks-size-guide', ENVO_BLOCKS_URL . 'assets/js/size-guide-view.js', array(), self::ver( 'assets/js/size-guide-view.js' ), $view );
		wp_register_script( 'envo-blocks-video', ENVO_BLOCKS_URL . 'assets/js/video-popup-view.js', array(), self::ver( 'assets/js/video-popup-view.js' ), $view );
		wp_register_script( 'envo-blocks-map', ENVO_BLOCKS_URL . 'assets/js/map-view.js', array(), self::ver( 'assets/js/map-view.js' ), $view );
		wp_register_script( 'envo-blocks-clock', ENVO_BLOCKS_URL . 'assets/js/clock.js', array(), self::ver( 'assets/js/clock.js' ), $view );
		wp_register_script( 'envo-blocks-announcement', ENVO_BLOCKS_URL . 'assets/js/announcement-view.js', array(), self::ver( 'assets/js/announcement-view.js' ), $view );
		wp_register_script( 'envo-blocks-countdown', ENVO_BLOCKS_URL . 'assets/js/countdown-view.js', array( 'envo-blocks-clock' ), self::ver( 'assets/js/countdown-view.js' ), $view );
		wp_register_script( 'envo-blocks-hours', ENVO_BLOCKS_URL . 'assets/js/hours-view.js', array( 'envo-blocks-clock' ), self::ver( 'assets/js/hours-view.js' ), $view );
		wp_register_script( 'envo-blocks-stock', ENVO_BLOCKS_URL . 'assets/js/stock-view.js', array(), self::ver( 'assets/js/stock-view.js' ), $view );
		wp_register_script( 'envo-blocks-lowest', ENVO_BLOCKS_URL . 'assets/js/lowest-view.js', array(), self::ver( 'assets/js/lowest-view.js' ), $view );
		wp_register_script( 'envo-blocks-shipping', ENVO_BLOCKS_URL . 'assets/js/shipping-bar-view.js', array(), self::ver( 'assets/js/shipping-bar-view.js' ), $view );
		wp_register_style( 'envo-blocks-filters', ENVO_BLOCKS_URL . 'assets/css/filters.css', array(), self::ver( 'assets/css/filters.css' ) );
		wp_register_script( 'envo-blocks-filters', ENVO_BLOCKS_URL . 'assets/js/filters-view.js', array(), self::ver( 'assets/js/filters-view.js' ), $view );
		wp_register_style( 'envo-blocks-language-switcher', ENVO_BLOCKS_URL . 'assets/css/language-switcher.css', array(), self::ver( 'assets/css/language-switcher.css' ) );
		wp_register_script( 'envo-blocks-language-switcher', ENVO_BLOCKS_URL . 'assets/js/language-switcher-view.js', array(), self::ver( 'assets/js/language-switcher-view.js' ), $view );
		wp_register_style( 'envo-blocks-tax-switcher', ENVO_BLOCKS_URL . 'assets/css/tax-switcher.css', array(), self::ver( 'assets/css/tax-switcher.css' ) );
		wp_register_script( 'envo-blocks-tax-switcher', ENVO_BLOCKS_URL . 'assets/js/tax-switcher-view.js', array(), self::ver( 'assets/js/tax-switcher-view.js' ), $view );
		wp_register_style( 'envo-blocks-sticky', ENVO_BLOCKS_URL . 'assets/css/sticky.css', array(), self::ver( 'assets/css/sticky.css' ) );
		wp_register_script( 'envo-blocks-sticky', ENVO_BLOCKS_URL . 'assets/js/sticky-view.js', array(), self::ver( 'assets/js/sticky-view.js' ), $view );
		wp_register_style( 'envo-blocks-parallax', ENVO_BLOCKS_URL . 'assets/css/parallax.css', array(), self::ver( 'assets/css/parallax.css' ) );
		wp_register_script( 'envo-blocks-parallax', ENVO_BLOCKS_URL . 'assets/js/parallax-view.js', array(), self::ver( 'assets/js/parallax-view.js' ), $view );
		wp_register_style( 'envo-blocks-form-styler', ENVO_BLOCKS_URL . 'assets/css/form-styler.css', array(), self::ver( 'assets/css/form-styler.css' ) );
		wp_register_style( 'envo-blocks-faq', ENVO_BLOCKS_URL . 'assets/css/faq.css', array(), self::ver( 'assets/css/faq.css' ) );
		wp_register_script( 'envo-blocks-faq', ENVO_BLOCKS_URL . 'assets/js/faq-view.js', array(), self::ver( 'assets/js/faq-view.js' ), $view );
		wp_register_style( 'envo-blocks-product-tabs', ENVO_BLOCKS_URL . 'assets/css/product-tabs.css', array(), self::ver( 'assets/css/product-tabs.css' ) );
		wp_register_script( 'envo-blocks-product-tabs', ENVO_BLOCKS_URL . 'assets/js/product-tabs-view.js', array(), self::ver( 'assets/js/product-tabs-view.js' ), $view );

		$names = self::BASIC;
		if ( class_exists( 'WooCommerce' ) ) {
			$names = array_merge( $names, self::WOO );
		}

		foreach ( $names as $name ) {
			register_block_type( ENVO_BLOCKS_PATH . 'blocks/' . $name );
		}
	}

	/**
	 * Player of the Video Popup block for a video URL.
	 *
	 * YouTube (watch, youtu.be, shorts, embed – played from youtube-nocookie.com), Vimeo, Dailymotion,
	 * or a video file (mp4, webm, ogg, mov) played by the browser.
	 *
	 * @param string $url   Video URL.
	 * @param int    $start Start time in seconds.
	 * @return array|null { type: iframe|file, src: string } or null for an unknown URL.
	 */
	public static function video_embed( $url, $start = 0 ) {
		$url = is_string( $url ) ? trim( $url ) : '';
		if ( '' === $url || ! preg_match( '#^https?://#i', $url ) ) {
			return null;
		}

		$start = max( 0, (int) $start );

		if ( preg_match( '#^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $m ) ) {
			if ( ! $start && preg_match( '/[?&#]t=(\d+)/', $url, $t ) ) {
				$start = (int) $t[1];
			}
			$args = array(
				'autoplay'       => 1,
				'rel'            => 0,
				'modestbranding' => 1,
				'playsinline'    => 1,
			);
			if ( $start ) {
				$args['start'] = $start;
			}
			return array(
				'type' => 'iframe',
				'src'  => add_query_arg( $args, 'https://www.youtube-nocookie.com/embed/' . $m[1] ),
			);
		}

		if ( preg_match( '#^https?://(?:www\.|player\.)?vimeo\.com/(?:video/)?(\d+)(?:/([a-f0-9]+))?#', $url, $m ) ) {
			$args = array(
				'autoplay' => 1,
				'dnt'      => 1,
			);
			if ( ! empty( $m[2] ) ) {
				$args['h'] = $m[2];
			} elseif ( preg_match( '#[?&]h=([a-f0-9]+)#', $url, $h ) ) {
				$args['h'] = $h[1];
			}
			return array(
				'type' => 'iframe',
				'src'  => add_query_arg( $args, 'https://player.vimeo.com/video/' . $m[1] ) . ( $start ? '#t=' . $start . 's' : '' ),
			);
		}

		if ( preg_match( '#^https?://(?:www\.)?(?:dailymotion\.com/video/|dai\.ly/)([A-Za-z0-9]+)#', $url, $m ) ) {
			$args = array( 'autoplay' => 1 );
			if ( $start ) {
				$args['start'] = $start;
			}
			return array(
				'type' => 'iframe',
				'src'  => add_query_arg( $args, 'https://www.dailymotion.com/embed/video/' . $m[1] ),
			);
		}

		if ( preg_match( '#\.(?:mp4|m4v|webm|ogv|ogg|mov)(?:[?\#].*)?$#i', $url ) ) {
			return array(
				'type' => 'file',
				'src'  => esc_url_raw( $url ) . ( $start ? '#t=' . $start : '' ),
			);
		}

		return null;
	}

	/**
	 * Address of the embedded map of the Map block, or an empty string without a place.
	 *
	 * Google: the address or the coordinates; with an API key the official Maps Embed API, without it the classic
	 * embed of maps.google.com. OpenStreetMap: needs the coordinates (it has no address search without an API).
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function map_url( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : array();
		$zoom       = self::int( $attributes, 'zoom', 14, 1, 20 );
		$lat        = isset( $attributes['latitude'] ) && is_numeric( $attributes['latitude'] ) ? max( -90, min( 90, (float) $attributes['latitude'] ) ) : null;
		$lng        = isset( $attributes['longitude'] ) && is_numeric( $attributes['longitude'] ) ? max( -180, min( 180, (float) $attributes['longitude'] ) ) : null;
		$address    = isset( $attributes['address'] ) ? trim( sanitize_text_field( (string) $attributes['address'] ) ) : '';
		$has_point  = null !== $lat && null !== $lng;

		if ( isset( $attributes['provider'] ) && 'osm' === $attributes['provider'] ) {
			if ( ! $has_point ) {
				return '';
			}
			// Bounding box around the point for the zoom (about 1000 px wide).
			$half_lng = 360 / pow( 2, $zoom ) * 1.2;
			$half_lat = $half_lng * 0.45;

			return add_query_arg(
				array(
					'bbox'   => rawurlencode( sprintf( '%.6F,%.6F,%.6F,%.6F', $lng - $half_lng, $lat - $half_lat, $lng + $half_lng, $lat + $half_lat ) ),
					'layer'  => 'mapnik',
					'marker' => rawurlencode( sprintf( '%.6F,%.6F', $lat, $lng ) ),
				),
				'https://www.openstreetmap.org/export/embed.html'
			);
		}

		$place = $has_point ? sprintf( '%.6F,%.6F', $lat, $lng ) : $address;

		if ( '' === $place ) {
			return '';
		}

		$satellite = isset( $attributes['mapType'] ) && 'satellite' === $attributes['mapType'];
		$key       = isset( $attributes['apiKey'] ) ? preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $attributes['apiKey'] ) : '';

		if ( '' !== $key ) {
			return add_query_arg(
				array(
					'key'      => $key,
					'q'        => rawurlencode( $place ),
					'zoom'     => $zoom,
					'maptype'  => $satellite ? 'satellite' : 'roadmap',
					'language' => rawurlencode( substr( get_locale(), 0, 2 ) ),
				),
				'https://www.google.com/maps/embed/v1/place'
			);
		}

		return add_query_arg(
			array(
				'q'      => rawurlencode( $place ),
				'z'      => $zoom,
				't'      => $satellite ? 'k' : 'm',
				'hl'     => rawurlencode( substr( get_locale(), 0, 2 ) ),
				'output' => 'embed',
			),
			'https://maps.google.com/maps'
		);
	}

	/**
	 * Asset version: the plugin version plus the file time, so browsers load a changed file
	 * even when the plugin version stays the same.
	 *
	 * @param string $file Path relative to the plugin folder.
	 * @return string
	 */
	public static function ver( $file ) {
		$path = ENVO_BLOCKS_PATH . $file;
		$time = is_file( $path ) ? filemtime( $path ) : 0;

		return ENVO_BLOCKS_VERSION . ( $time ? '.' . $time : '' );
	}

	/**
	 * Own "Envo Blocks" section in the block inserter, at the top of the list.
	 *
	 * @param array $categories Block categories.
	 * @return array
	 */
	public static function category( $categories ) {
		foreach ( $categories as $category ) {
			if ( isset( $category['slug'] ) && self::CATEGORY === $category['slug'] ) {
				return $categories;
			}
		}

		array_unshift(
			$categories,
			array(
				'slug'  => self::CATEGORY,
				'title' => __( 'Envo Blocks', 'envo-blocks' ),
				'icon'  => null,
			)
		);

		return $categories;
	}

	/**
	 * Remembers the viewed product in the browser (localStorage) for the Recently Viewed Products block.
	 * Done on every product page, so the list fills even before the block is shown anywhere.
	 */
	public static function track_product_view() {
		if ( ! function_exists( 'is_product' ) || ! is_product() || ! WP_Block_Type_Registry::get_instance()->is_registered( 'envo-blocks/recently-viewed' ) ) {
			return;
		}

		$id = absint( get_queried_object_id() );
		if ( ! $id ) {
			return;
		}

		wp_print_inline_script_tag(
			sprintf(
				'(function(id){try{var k="envoBlocksRecent",l=JSON.parse(localStorage.getItem(k)||"[]");l=[id].concat(l.filter(function(x){return x!==id;})).slice(0,24);localStorage.setItem(k,JSON.stringify(l));}catch(e){}})(%d);',
				$id
			)
		);
	}

	/**
	 * Whether the block is rendered for the editor preview (ServerSideRender uses the REST API).
	 *
	 * @return bool
	 */
	public static function is_preview() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}

	/**
	 * Number of the block on the page (1, 2, …), the same on every request of the page.
	 *
	 * @param string $name Block name.
	 * @return int
	 */
	public static function instance( $name ) {
		static $counts = array();
		$counts[ $name ] = isset( $counts[ $name ] ) ? $counts[ $name ] + 1 : 1;
		return $counts[ $name ];
	}

	/**
	 * Message shown only in the editor preview.
	 *
	 * @param string $message Text.
	 * @return string
	 */
	public static function notice( $message ) {
		if ( ! self::is_preview() ) {
			return '';
		}

		return '<p class="sb-notice">' . esc_html( $message ) . '</p>';
	}

	/**
	 * Tags and attributes allowed in the markup of the blocks, for wp_kses(): post HTML plus the SVG icons
	 * and payment marks, the map iframe and the native dialog; with $forms also the form fields (the WooCommerce
	 * order tracking form).
	 *
	 * @param bool $forms Allow form, input, select, textarea, label and button.
	 * @return array
	 */
	public static function allowed_html( $forms = false ) {
		static $allowed = null;
		if ( null !== $allowed ) {
			return $forms ? self::with_forms( $allowed ) : $allowed;
		}

		$paint = array(
			'fill'            => true,
			'fill-opacity'    => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'transform'       => true,
		);

		$post    = wp_kses_allowed_html( 'post' );
		$allowed = array_merge(
			$post,
			array(
				// Responsive attributes added by wp_get_attachment_image().
				'img'    => array_merge(
					isset( $post['img'] ) ? $post['img'] : array(),
					array(
						'srcset'        => true,
						'sizes'         => true,
						'decoding'      => true,
						'fetchpriority' => true,
					)
				),
				'svg'    => array_merge(
					$paint,
					array(
						'xmlns'           => true,
						'version'         => true,
						'class'           => true,
						'viewbox'         => true,
						'width'           => true,
						'height'          => true,
						'role'            => true,
						'aria-hidden'     => true,
						'aria-label'      => true,
						'focusable'       => true,
						'shape-rendering' => true,
					)
				),
				'path'   => array_merge( $paint, array( 'd' => true ) ),
				'circle' => array_merge(
					$paint,
					array(
						'cx' => true,
						'cy' => true,
						'r'  => true,
					)
				),
				'rect'   => array_merge(
					$paint,
					array(
						'x'      => true,
						'y'      => true,
						'width'  => true,
						'height' => true,
						'rx'     => true,
					)
				),
				'text'   => array(
					'x'           => true,
					'y'           => true,
					'text-anchor' => true,
					'font-family' => true,
					'font-size'   => true,
					'font-weight' => true,
					'font-style'  => true,
					'fill'        => true,
				),
				'tspan'  => array( 'fill' => true ),
				'iframe' => array(
					'class'           => true,
					'src'             => true,
					'title'           => true,
					'width'           => true,
					'height'          => true,
					'loading'         => true,
					'referrerpolicy'  => true,
					'allow'           => true,
					'allowfullscreen' => true,
				),
				'dialog' => array(
					'class'           => true,
					'id'              => true,
					'style'           => true,
					'aria-label'      => true,
					'aria-labelledby' => true,
				),
			)
		);

		return $forms ? self::with_forms( $allowed ) : $allowed;
	}

	/**
	 * Form fields added to allowed tags.
	 *
	 * @param array $allowed Allowed tags.
	 * @return array
	 */
	private static function with_forms( $allowed ) {
		$common = array(
			'class'       => true,
			'id'          => true,
			'name'        => true,
			'type'        => true,
			'value'       => true,
			'placeholder' => true,
			'required'    => true,
			'autocomplete' => true,
			'for'         => true,
			'method'      => true,
			'action'      => true,
			'aria-label'  => true,
			'aria-describedby' => true,
			'aria-required' => true,
		);
		foreach ( array( 'form', 'input', 'select', 'option', 'textarea', 'label', 'button', 'fieldset', 'legend' ) as $tag ) {
			$allowed[ $tag ] = array_merge( isset( $allowed[ $tag ] ) ? $allowed[ $tag ] : array(), $common );
		}
		return $allowed;
	}

	/**
	 * Amount of the order from which the shipping is free: set in the block, or the lowest minimum of the WooCommerce
	 * "Free shipping" methods.
	 *
	 * @param array $attributes Block attributes (thresholdSource, threshold).
	 * @return float
	 */
	public static function shipping_threshold( $attributes ) {
		if ( isset( $attributes['threshold'] ) && is_numeric( $attributes['threshold'] ) && (float) $attributes['threshold'] > 0 && ( ! isset( $attributes['thresholdSource'] ) || 'manual' === $attributes['thresholdSource'] ) ) {
			return min( 10000000, (float) $attributes['threshold'] );
		}

		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return 0.0;
		}

		$zone_ids = array( 0 );
		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			$zone_ids[] = absint( $zone['zone_id'] );
		}

		$values = array();
		foreach ( $zone_ids as $zone_id ) {
			$zone = new WC_Shipping_Zone( $zone_id );

			foreach ( $zone->get_shipping_methods( true ) as $method ) {
				if ( 'free_shipping' !== $method->id ) {
					continue;
				}

				$requires = isset( $method->requires ) ? $method->requires : '';
				$minimum  = isset( $method->min_amount ) ? (float) $method->min_amount : 0.0;

				if ( in_array( $requires, array( 'min_amount', 'either', 'both' ), true ) && $minimum > 0 ) {
					$values[] = $minimum;
				}
			}
		}

		return $values ? min( $values ) : 0.0;
	}

	/**
	 * Value of the cart that counts towards free shipping (after discounts, with or without tax like the shop
	 * displays prices).
	 *
	 * @return float
	 */
	public static function shipping_cart_total() {
		$cart = function_exists( 'WC' ) ? WC()->cart : null;

		if ( ! $cart ) {
			return 0.0;
		}

		$total = (float) $cart->get_displayed_subtotal() - (float) $cart->get_discount_total();

		if ( $cart->display_prices_including_tax() ) {
			$total -= (float) $cart->get_discount_tax();
		}

		/**
		 * Filters the cart value used by the Free Shipping Progress block.
		 *
		 * @param float $total Cart value.
		 */
		return max( 0.0, (float) apply_filters( 'envo_blocks_shipping_bar_cart_total', $total ) );
	}

	/**
	 * Validates a CSS color from the color picker: hex, rgb()/rgba() or a CSS variable of the theme palette.
	 * rgb()/rgba() is returned as hex, because wp_kses() removes inline styles with rgb() values.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function color( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( $value );

		if ( preg_match( '/^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(0|1|1\.0+|0?\.\d+)\s*)?\)$/', $value, $m ) ) {
			$hex = sprintf( '#%02x%02x%02x', min( 255, (int) $m[1] ), min( 255, (int) $m[2] ), min( 255, (int) $m[3] ) );
			if ( isset( $m[4] ) && (float) $m[4] < 1 ) {
				$hex .= sprintf( '%02x', (int) round( (float) $m[4] * 255 ) );
			}
			return $hex;
		}

		if ( preg_match( '/^var\(--wp--preset--color--[a-z0-9-]+\)$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Builds inline custom properties from color attributes.
	 *
	 * @param array $attributes Block attributes.
	 * @param array $map        Attribute => custom property.
	 * @return string
	 */
	public static function color_vars( $attributes, $map ) {
		$style = '';

		foreach ( $map as $key => $property ) {
			$color = self::color( isset( $attributes[ $key ] ) ? $attributes[ $key ] : '' );
			if ( '' !== $color ) {
				$style .= $property . ':' . $color . ';';
			}
		}

		return $style;
	}

	/**
	 * Integer attribute clamped to a range.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $key        Attribute name.
	 * @param int    $default    Default.
	 * @param int    $min        Minimum.
	 * @param int    $max        Maximum.
	 * @return int
	 */
	public static function int( $attributes, $key, $default, $min, $max ) {
		$value = isset( $attributes[ $key ] ) && is_numeric( $attributes[ $key ] ) ? (int) $attributes[ $key ] : $default;

		return max( $min, min( $max, $value ) );
	}

	/**
	 * Text attribute, or the default when empty.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $key        Attribute name.
	 * @param string $default    Default.
	 * @return string
	 */
	public static function text( $attributes, $key, $default = '' ) {
		$value = isset( $attributes[ $key ] ) && is_string( $attributes[ $key ] ) ? trim( sanitize_text_field( $attributes[ $key ] ) ) : '';

		return '' !== $value ? $value : $default;
	}

	/**
	 * One of the allowed values.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $key        Attribute name.
	 * @param array  $allowed    Allowed values (the first is the default).
	 * @return string
	 */
	public static function choice( $attributes, $key, $allowed ) {
		return isset( $attributes[ $key ] ) && in_array( $attributes[ $key ], $allowed, true ) ? $attributes[ $key ] : $allowed[0];
	}

	/**
	 * Line icons (24×24, stroke = currentColor). Paths only, wrapped by icon().
	 *
	 * @return array
	 */
	private static function icons() {
		return array(
			'truck'    => '<path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
			'return'   => '<path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/>',
			'shield'   => '<path d="M12 3 4 6v6c0 4.5 3.4 8.2 8 9 4.6-.8 8-4.5 8-9V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
			'lock'     => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
			'support'  => '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3" y="14" width="4" height="6" rx="1.5"/><rect x="17" y="14" width="4" height="6" rx="1.5"/><path d="M19 20a3 3 0 0 1-3 2h-3"/>',
			'card'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/>',
			'gift'     => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v9h14v-9M12 8v13"/><path d="M12 8C10 4 6.5 4 6.5 6.5S10 8 12 8c2 0 5.5 1 5.5-1.5S14 4 12 8"/>',
			'star'     => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2-5.5-2.9-5.5 2.9 1-6.2L3 9.6l6.2-.9z"/>',
			'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
			'box'      => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
			'tag'      => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
			'leaf'     => '<path d="M5 19C5 10 10 5 20 4c0 10-5 15-14 15"/><path d="M5 19 13 11"/>',
			'heart'    => '<path d="M12 20s-8-4.7-8-10.5A4.5 4.5 0 0 1 12 7a4.5 4.5 0 0 1 8 2.5C20 15.3 12 20 12 20z"/>',
			'check'    => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
			'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3c-3 3.5-3 14.5 0 18"/>',
			'store'    => '<path d="M4 10v10h16V10"/><path d="M3 10 5 4h14l2 6c0 1.7-1.3 3-3 3s-3-1.3-3-3c0 1.7-1.3 3-3 3s-3-1.3-3-3c0 1.7-1.3 3-3 3s-3-1.3-3-3z"/><path d="M10 20v-5h4v5"/>',
			'ruler'    => '<rect x="2" y="8" width="20" height="8" rx="1" transform="rotate(-45 12 12)"/><path d="m8.5 9.5 1.5 1.5M11 7l1.5 1.5M13.5 4.5 15 6M6 12l1.5 1.5"/>',
			'copy'     => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>',
			'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
			'document' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
			'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.01"/>',
			'chat'     => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 9h8M8 12h5"/>',
			'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		);
	}

	/**
	 * Names of the icons (for the editor select).
	 *
	 * @return string[]
	 */
	public static function icon_names() {
		return array_values( array_diff( array_keys( self::icons() ), array( 'close', 'copy' ) ) );
	}

	/**
	 * SVG markup of a line icon.
	 *
	 * @param string $name  Icon name.
	 * @param string $class Extra class.
	 * @return string
	 */
	public static function icon( $name, $class = '' ) {
		$icons = self::icons();
		if ( ! isset( $icons[ $name ] ) ) {
			return '';
		}

		return '<svg class="sb-icon' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
	}

	/**
	 * Simplified payment method marks (38×24 cards). Not official logos, just recognisable marks.
	 *
	 * @return array name => array( label, inner svg )
	 */
	private static function payments() {
		$text = static function ( $label, $color, $size = 9, $weight = 800, $style = '' ) {
			return '<text x="19" y="15.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="' . $size . '" font-weight="' . $weight . '" fill="' . $color . '"' . $style . '>' . $label . '</text>';
		};

		return array(
			'visa'       => array( 'Visa', $text( 'VISA', '#1a1f71', 10, 900, ' font-style="italic"' ) ),
			'mastercard' => array( 'Mastercard', '<circle cx="15.5" cy="12" r="6" fill="#eb001b"/><circle cx="22.5" cy="12" r="6" fill="#f79e1b" fill-opacity=".9"/>' ),
			'maestro'    => array( 'Maestro', '<circle cx="15.5" cy="12" r="6" fill="#0099df"/><circle cx="22.5" cy="12" r="6" fill="#ed0006" fill-opacity=".85"/>' ),
			'amex'       => array( 'American Express', '<rect x="3" y="5" width="32" height="14" rx="2" fill="#1f72cd"/>' . $text( 'AMEX', '#fff', 8.5 ) ),
			'paypal'     => array( 'PayPal', '<text x="19" y="15.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="8.5" font-weight="800" font-style="italic"><tspan fill="#003087">Pay</tspan><tspan fill="#0070e0">Pal</tspan></text>' ),
			'applepay'   => array( 'Apple Pay', '<path fill="#000" d="M10.6 9.3c.4-.5.7-1.1.6-1.8-.6 0-1.3.4-1.7.9-.4.4-.7 1.1-.6 1.7.6.1 1.3-.3 1.7-.8zm.6.9c-.9-.1-1.7.5-2.2.5-.4 0-1.1-.5-1.9-.5-1 0-1.9.6-2.4 1.5-1 1.8-.3 4.4.7 5.8.5.7 1 1.5 1.8 1.5.7 0 1-.5 1.9-.5s1.1.5 1.9.5 1.3-.7 1.7-1.4c.5-.8.7-1.6.8-1.6 0 0-1.5-.6-1.5-2.3 0-1.4 1.1-2.1 1.2-2.2-.7-1-1.7-1.1-2-1.3z"/>' . '<text x="25" y="15.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="8.5" font-weight="600" fill="#000">Pay</text>' ),
			'googlepay'  => array( 'Google Pay', '<text x="19" y="15.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="8.5" font-weight="600"><tspan fill="#4285f4">G</tspan><tspan fill="#3c4043"> Pay</tspan></text>' ),
			'klarna'     => array( 'Klarna', '<rect x="3" y="5" width="32" height="14" rx="3" fill="#ffb3c7"/>' . $text( 'Klarna.', '#0b051d', 7.5 ) ),
			'bank'       => array( __( 'Bank transfer', 'envo-blocks' ), '<path fill="none" stroke="#3c434a" stroke-width="1.3" stroke-linejoin="round" d="M12 10.5 19 6.5l7 4zM13.5 11v5.5M17 11v5.5M21 11v5.5M24.5 11v5.5M11.5 18h15"/>' ),
			'cod'        => array( __( 'Cash on delivery', 'envo-blocks' ), '<rect x="9" y="7.5" width="20" height="10" rx="1.5" fill="none" stroke="#1a7f37" stroke-width="1.3"/><circle cx="19" cy="12.5" r="2.4" fill="none" stroke="#1a7f37" stroke-width="1.3"/>' ),
		);
	}

	/**
	 * Payment marks for the editor: name => label.
	 *
	 * @return array
	 */
	public static function payment_names() {
		$out = array();
		foreach ( self::payments() as $name => $payment ) {
			$out[ $name ] = $payment[0];
		}

		return $out;
	}

	/**
	 * SVG of a payment method card.
	 *
	 * @param string $name Payment name.
	 * @return string
	 */
	public static function payment( $name ) {
		$payments = self::payments();
		if ( ! isset( $payments[ $name ] ) ) {
			return '';
		}

		return '<svg class="sb-pay" viewBox="0 0 38 24" role="img" aria-label="' . esc_attr( $payments[ $name ][0] ) . '"><rect x=".5" y=".5" width="37" height="23" rx="3.5" fill="#fff" stroke="#d9dce1"/>' . $payments[ $name ][1] . '</svg>';
	}

	/**
	 * Five stars filled to the rating.
	 *
	 * @param float $rating Rating 0-5.
	 * @return string
	 */
	public static function stars( $rating ) {
		$percent = max( 0, min( 100, $rating / 5 * 100 ) );
		/* translators: %s: average rating */
		$label = sprintf( __( 'Rated %s out of 5', 'envo-blocks' ), number_format_i18n( $rating, 1 ) );

		return '<span class="sb-stars" role="img" aria-label="' . esc_attr( $label ) . '" style="--sb-stars:' . esc_attr( round( $percent, 1 ) ) . '%"></span>';
	}
}
