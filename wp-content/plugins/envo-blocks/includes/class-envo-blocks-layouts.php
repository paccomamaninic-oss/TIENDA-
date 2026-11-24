<?php
/**
 * Store Layouts: own designs of the WooCommerce pages – product page, shop and categories, cart, order received and
 * My account – made of blocks, for any theme.
 *
 * - A layout is a post of the type "envo_layout" (block editor). Published = in use, draft = not in use.
 * - The page shows the theme header and footer; only the main part between them comes from the layout:
 *   block themes get it in the template content (header and footer template parts kept), classic themes through
 *   views/layout.php (get_header(), the layout, get_footer()).
 * - Free: one layout per page type for the whole store. Envo Blocks PRO adds rules (categories, tags, products) and
 *   the PRO designs through the filters envo_blocks_layout_match and envo_blocks_layouts.
 * - Ready-made designs are the "Layouts" tab of the template window (Envo_Blocks_Library, type "layouts"): files in
 *   templates/layouts/ ({ "schema": "envo-blocks-layout/v1", "type": …, "content": block markup }), the PRO ones from
 *   the PRO library. "Use" makes a draft layout of the design and opens it in the editor.
 * - Nothing changes on the site until a layout is published. ?envo_layouts=off shows the theme pages to editors, and
 *   Envo Blocks › Store Layouts can turn all layouts off.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Store Layouts.
 */
final class Envo_Blocks_Layouts {

	const POST_TYPE = 'envo_layout';
	const SCHEMA    = 'envo-blocks-layout/v1';
	const PAGE      = 'envo-blocks-layouts';
	const OFF       = 'envo_blocks_layouts_off';

	// Meta of a layout: page type, rules (Envo Blocks PRO), product of the editor preview, design it was made from.
	const META_TYPE    = '_envo_layout_type';
	const META_RULES   = '_envo_layout_rules';
	const META_PRODUCT = '_envo_layout_product';
	const META_DESIGN  = '_envo_layout_design';
	const META_COLORS  = '_envo_layout_colors';
	const META_BARE    = '_envo_layout_bare'; // Checkout layouts: without the theme header and footer.

	// The cart panel of every page (Envo_Blocks_Side_Cart), not a page of its own.
	const TYPE_SIDE_CART = 'side_cart';

	// Marks the top-level blocks of the rendered layout (they get the post ID of the page as context).
	const MARK = 'envoLayout';

	// Address of the live demos of the designs.
	const DEMO_URL = 'https://demos.envoblocks.com/';

	/**
	 * Layout used on this request: array( id, type, content, title ) or false (null = not looked up yet).
	 *
	 * @var array|false|null
	 */
	private static $current = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_action( 'template_redirect', array( __CLASS__, 'demo_redirect' ), 1 );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 9999 );
		add_filter( 'render_block_context', array( __CLASS__, 'block_context' ), 10, 3 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'block_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'editor_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'site_editor_assets' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'rest_routes' ) );
		add_filter( 'preview_post_link', array( __CLASS__, 'preview_link' ), 10, 2 );
		add_filter( 'post_type_link', array( __CLASS__, 'view_link' ), 10, 2 );

		if ( is_admin() ) {
			add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
			add_action( 'add_meta_boxes_' . self::POST_TYPE, array( __CLASS__, 'meta_boxes' ) );
			add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
			add_action( 'admin_post_envo_blocks_layouts', array( __CLASS__, 'admin_action' ) );
			add_action( 'load-edit.php', array( __CLASS__, 'list_redirect' ) );
			add_action( 'load-post-new.php', array( __CLASS__, 'new_type' ) );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Types and posts                                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Page types: key => array( label, description ).
	 *
	 * @return array
	 */
	public static function types() {
		return array(
			'product'   => array( __( 'Product page', 'envo-blocks' ), __( 'Every single product page.', 'envo-blocks' ) ),
			'shop'      => array( __( 'Shop page', 'envo-blocks' ), __( 'The shop, product categories, tags, brands and product search.', 'envo-blocks' ) ),
			'cart'      => array( __( 'Cart', 'envo-blocks' ), __( 'The cart page, also when the cart is empty.', 'envo-blocks' ) ),
			'thankyou'  => array( __( 'Order received', 'envo-blocks' ), __( 'The thank-you page after an order.', 'envo-blocks' ) ),
			'account'   => array( __( 'My account', 'envo-blocks' ), __( 'My account with all its sections, and the login form.', 'envo-blocks' ) ),
			'checkout'  => array( __( 'Checkout', 'envo-blocks' ), __( 'The checkout page (not the order received and order payment pages). The WooCommerce checkout itself stays: payments, shipping and coupons work as before.', 'envo-blocks' ) ),
			'side_cart' => array( __( 'Side cart', 'envo-blocks' ), __( 'The cart panel that slides in from the side of every page (Mini-Cart, Sticky Cart, after Add to cart).', 'envo-blocks' ) ),
		);
	}

	/**
	 * Label of a page type.
	 *
	 * @param string $type Type key.
	 * @return string
	 */
	public static function type_label( $type ) {
		$types = self::types();
		return isset( $types[ $type ] ) ? $types[ $type ][0] : '';
	}

	/**
	 * Valid type key or "".
	 *
	 * @param mixed $type Value.
	 * @return string
	 */
	public static function valid_type( $type ) {
		return is_string( $type ) && array_key_exists( $type, self::types() ) ? $type : '';
	}

	/**
	 * Post type and meta.
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Store Layouts', 'envo-blocks' ),
					'singular_name'      => __( 'Store Layout', 'envo-blocks' ),
					'add_new'            => __( 'Add Layout', 'envo-blocks' ),
					'add_new_item'       => __( 'Add Store Layout', 'envo-blocks' ),
					'edit_item'          => __( 'Edit Store Layout', 'envo-blocks' ),
					'new_item'           => __( 'New Store Layout', 'envo-blocks' ),
					'search_items'       => __( 'Search Store Layouts', 'envo-blocks' ),
					'not_found'          => __( 'No store layouts yet.', 'envo-blocks' ),
					'not_found_in_trash' => __( 'No store layouts in the trash.', 'envo-blocks' ),
					'item_published'     => __( 'Layout published: it is used on the store pages now.', 'envo-blocks' ),
					'item_updated'       => __( 'Layout updated.', 'envo-blocks' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => false, // Envo Blocks › Store Layouts lists them.
				'show_in_rest'        => true,
				'supports'            => array( 'title', 'editor', 'custom-fields', 'revisions' ),
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
				'rewrite'             => false,
				'query_var'           => false,
			)
		);

		$auth = static function ( $allowed, $key, $post_id ) {
			return current_user_can( 'edit_post', $post_id );
		};
		register_post_meta(
			self::POST_TYPE,
			self::META_TYPE,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => array( __CLASS__, 'valid_type' ),
				'auth_callback'     => $auth,
			)
		);
		register_post_meta(
			self::POST_TYPE,
			self::META_PRODUCT,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => $auth,
			)
		);
	}

	/**
	 * Blocks made for the layouts (parts of the WooCommerce pages that core and WooCommerce offer only in the Site
	 * Editor): breadcrumbs, the shop toolbar, the order received details and My account.
	 */
	public static function register_blocks() {
		wp_register_script(
			'envo-blocks-layouts-editor',
			ENVO_BLOCKS_URL . 'assets/js/layouts-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-server-side-render', 'wp-data', 'wp-hooks', 'wp-compose' ),
			Envo_Blocks_Blocks::ver( 'assets/js/layouts-editor.js' ),
			true
		);
		wp_set_script_translations( 'envo-blocks-layouts-editor', 'envo-blocks' );
		wp_register_style( 'envo-blocks-layouts', ENVO_BLOCKS_URL . 'assets/css/layouts.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/layouts.css' ) );

		foreach ( array( 'store-breadcrumbs', 'shop-toolbar', 'order-received', 'my-account', 'checkout-form', 'checkout-header' ) as $name ) {
			register_block_type( ENVO_BLOCKS_PATH . 'blocks/' . $name );
		}
	}

	/**
	 * Type of a layout post.
	 *
	 * @param int $id Post ID.
	 * @return string
	 */
	public static function post_type_of( $id ) {
		return self::valid_type( get_post_meta( $id, self::META_TYPE, true ) );
	}

	/**
	 * Rules of a layout (set by Envo Blocks PRO; empty = the whole page type).
	 *
	 * @param int $id Post ID.
	 * @return array
	 */
	public static function rules( $id ) {
		$rules = get_post_meta( $id, self::META_RULES, true );
		return is_array( $rules ) ? $rules : array();
	}

	/**
	 * Whether all layouts are turned off (Envo Blocks › Store Layouts).
	 *
	 * @return bool
	 */
	public static function is_off() {
		return (bool) get_option( self::OFF );
	}

	/* ------------------------------------------------------------------ */
	/* Which layout a page uses                                           */
	/* ------------------------------------------------------------------ */

	/**
	 * Page type of the current request, or "".
	 *
	 * @return string
	 */
	public static function request_type() {
		if ( ! function_exists( 'is_woocommerce' ) || is_admin() || is_feed() || is_embed() ) {
			return '';
		}
		if ( is_product() ) {
			return 'product';
		}
		if ( is_order_received_page() ) {
			return 'thankyou';
		}
		if ( is_cart() ) {
			return 'cart';
		}
		// The checkout itself; never the order payment page (order-pay, ?pay_for_order) – it shows another form.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of the page kind.
		if ( is_checkout() && ! is_checkout_pay_page() && ! isset( $_GET['pay_for_order'] ) && ! is_wc_endpoint_url() ) {
			return 'checkout';
		}
		if ( is_account_page() ) {
			return 'account';
		}
		if ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) {
			return 'shop';
		}
		return '';
	}

	/**
	 * Published layouts of a type, best first: the highest match (envo_blocks_layout_match), then the menu order and
	 * the newest.
	 *
	 * @param string $type Page type.
	 * @return array List of array( post, score ); score 0 = does not apply here.
	 */
	public static function candidates( $type ) {
		// Multilingual sites: the layouts of the visitor's language, or the default language when not translated.
		$posts = get_posts(
			Envo_Blocks_Multilang::all_languages(
				array(
					'post_type'        => self::POST_TYPE,
					'post_status'      => 'publish',
					'numberposts'      => 50,
					'meta_key'         => self::META_TYPE, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Few posts of an own post type.
					'meta_value'       => $type, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'orderby'          => array(
						'menu_order' => 'ASC',
						'date'       => 'DESC',
					),
					'suppress_filters' => false,
				)
			)
		);
		$posts = Envo_Blocks_Multilang::for_current_language( $posts );

		$list = array();
		foreach ( $posts as $post ) {
			$rules = self::rules( $post->ID );
			/**
			 * Filters whether a published layout is used on the current page (Envo Blocks PRO checks its rules).
			 *
			 * @param int     $score 1 = the layout is for the whole page type (no rules), 0 = it has rules (not used
			 *                       without PRO). A higher number wins: rules for a product beat a category.
			 * @param WP_Post $post  The layout.
			 * @param string  $type  Page type.
			 * @param array   $rules Rules of the layout.
			 */
			$score  = (int) apply_filters( 'envo_blocks_layout_match', $rules ? 0 : 1, $post, $type, $rules );
			$list[] = array( $post, max( 0, $score ) );
		}

		usort(
			$list,
			static function ( $a, $b ) {
				return $b[1] - $a[1];
			}
		);
		return $list;
	}

	/**
	 * The layout of this request: a preview (editors), or the best published layout of the page type.
	 *
	 * @return array|false array( id, type, content, title, preview ).
	 */
	public static function current() {
		if ( null !== self::$current ) {
			return self::$current;
		}
		self::$current = self::resolve( self::request_type() );
		return self::$current;
	}

	/**
	 * The layout of a type on this request: a preview (editors), a demo, or the best published layout of the type.
	 *
	 * @param string $type Type (a page type, or "side_cart" for the cart panel of every page).
	 * @return array|false array( id, type, content, title, preview ).
	 */
	public static function resolve( $type ) {
		if ( '' === $type ) {
			return false;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only previews, checked by capability.
		if ( isset( $_GET['envo_layouts'] ) && 'off' === $_GET['envo_layouts'] && current_user_can( 'edit_posts' ) ) {
			return false;
		}

		// Preview of a layout post (the editor's Preview button, links on Store Layouts).
		if ( isset( $_GET['envo_layout'] ) ) {
			$id   = absint( $_GET['envo_layout'] );
			$post = $id ? get_post( $id ) : null;
			if ( $post && self::POST_TYPE === $post->post_type && current_user_can( 'edit_post', $id ) && self::post_type_of( $id ) === $type ) {
				$content = $post->post_content;
				if ( isset( $_GET['preview'] ) ) {
					$autosave = wp_get_post_autosave( $id, get_current_user_id() );
					if ( $autosave && strtotime( $autosave->post_modified_gmt ) >= strtotime( $post->post_modified_gmt ) ) {
						$content = $autosave->post_content;
					}
				}
				return self::item( $id, $type, $content, get_the_title( $post ), true );
			}
		}

		/**
		 * Filters a layout shown by another plugin on this page (Store Demos: the live demo of a layout demo post).
		 *
		 * @param array|null $layout Null, or array( content, title ).
		 * @param string     $type   Page type.
		 */
		$other = apply_filters( 'envo_blocks_layout_preview', null, $type );
		if ( is_array( $other ) && isset( $other['content'] ) ) {
			return self::item( 0, $type, (string) $other['content'], (string) ( $other['title'] ?? '' ), true );
		}

		// Preview of a design of the library ("Preview" in the Layouts tab, live demos).
		if ( isset( $_GET['envo_layout_design'] ) && self::may_preview_designs() ) {
			$design = Envo_Blocks_Library::get_section( sanitize_key( wp_unslash( $_GET['envo_layout_design'] ) ), 'layouts' );
			if ( $design && $design['layout_type'] === $type ) {
				$data = self::load_design( $design );
				if ( ! is_wp_error( $data ) ) {
					return self::item( 0, $type, $data['content'], $design['name'], true );
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( self::is_off() ) {
			return false;
		}

		foreach ( self::candidates( $type ) as $candidate ) {
			if ( $candidate[1] > 0 ) {
				return self::item( $candidate[0]->ID, $type, $candidate[0]->post_content, get_the_title( $candidate[0] ), false );
			}
		}
		return false;
	}

	/**
	 * A layout to render.
	 *
	 * @param int    $id      Post ID (0 = design preview).
	 * @param string $type    Page type.
	 * @param string $content Block markup.
	 * @param string $title   Name.
	 * @param bool   $preview Preview for an editor.
	 * @return array|false
	 */
	private static function item( $id, $type, $content, $title, $preview ) {
		/**
		 * Filters the block markup of the layout used on the page.
		 *
		 * @param string $content Block markup.
		 * @param int    $id      Layout post ID (0 = a design preview).
		 * @param string $type    Page type.
		 */
		$content = (string) apply_filters( 'envo_blocks_layout_content', (string) $content, $id, $type );
		if ( '' === trim( $content ) ) {
			return false;
		}
		return array(
			'id'      => (int) $id,
			'type'    => $type,
			'content' => $content,
			'title'   => (string) $title,
			'preview' => (bool) $preview,
		);
	}

	/**
	 * Whether the visitor may see design previews: editors, or everybody where a filter allows it (our demo site).
	 *
	 * @return bool
	 */
	public static function may_preview_designs() {
		/**
		 * Filters whether every visitor may preview the designs of the Layouts library (?envo_layout_design=).
		 *
		 * @param bool $public False.
		 */
		return current_user_can( 'edit_posts' ) || (bool) apply_filters( 'envo_blocks_layout_public_designs', false );
	}

	/* ------------------------------------------------------------------ */
	/* Rendering                                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * Puts the layout into the page.
	 *
	 * @param string $template Template file.
	 * @return string
	 */
	public static function template_include( $template ) {
		$layout = self::current();
		if ( ! $layout ) {
			return $template;
		}

		if ( 'product' === $layout['type'] && function_exists( 'WC' ) && WC()->structured_data && is_singular( 'product' ) ) {
			// The product data for search engines (WooCommerce adds it in the summary of its own template).
			add_action( 'wp_footer', array( __CLASS__, 'product_structured_data' ), 1 );
		}
		wp_enqueue_style( 'envo-blocks-layouts' );

		$canvas = ABSPATH . WPINC . '/template-canvas.php';
		if ( wp_normalize_path( $template ) === wp_normalize_path( $canvas ) ) {
			global $_wp_current_template_content;
			$_wp_current_template_content = self::compose( (string) $_wp_current_template_content, $layout ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- The template content of this request.
			return $template;
		}
		if ( wp_is_block_theme() ) {
			return $template; // Another plugin renders this page.
		}
		// The blocks are rendered when the page styles are enqueued (the global styles are registered by then), so
		// their styles are in the head; views/layout.php prints them.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'prerender_classic' ), 99 );
		return ENVO_BLOCKS_PATH . ( self::is_bare( $layout ) ? 'views/layout-bare.php' : 'views/layout.php' );
	}

	/**
	 * Whether a layout is shown without the theme header and footer (the distraction-free checkout). Layout posts
	 * keep it in META_BARE; designs and demos mark their first block with the class "envo-l-bare".
	 *
	 * @param array $layout Layout.
	 * @return bool
	 */
	public static function is_bare( $layout ) {
		if ( 'checkout' !== $layout['type'] ) {
			return false;
		}
		$bare = $layout['id'] ? get_post_meta( $layout['id'], self::META_BARE, true ) : '';
		if ( '' === $bare ) {
			$bare = false !== strpos( $layout['content'], 'envo-l-bare' ) ? '1' : '0';
		}
		/**
		 * Filters whether a checkout layout is shown without the theme header and footer.
		 *
		 * @param bool  $bare   Distraction-free.
		 * @param array $layout Layout.
		 */
		return (bool) apply_filters( 'envo_blocks_layout_bare', '1' === (string) $bare, $layout );
	}

	/**
	 * WooCommerce product data for search engines, once (its template adds it in woocommerce_single_product_summary).
	 */
	public static function product_structured_data() {
		global $product;
		if ( did_action( 'woocommerce_single_product_summary' ) || ! is_a( $product, 'WC_Product' ) ) {
			return;
		}
		WC()->structured_data->generate_product_data( $product );
	}

	/**
	 * Template content of a block theme with the layout: the header and footer template parts of the theme's
	 * template stay, the main part between them is the layout.
	 *
	 * @param string $template Template block markup.
	 * @param array  $layout   Layout.
	 * @return string
	 */
	private static function compose( $template, $layout ) {
		$blocks = array_values(
			array_filter(
				parse_blocks( $template ),
				static function ( $block ) {
					return null !== $block['blockName'] || '' !== trim( (string) $block['innerHTML'] );
				}
			)
		);

		// A theme may wrap the whole page in one group: look inside it.
		$wrap = null;
		if ( 1 === count( $blocks ) && ! empty( $blocks[0]['innerBlocks'] ) && 'core/template-part' !== $blocks[0]['blockName'] ) {
			$wrap   = $blocks[0];
			$blocks = $wrap['innerBlocks'];
		}

		$count = count( $blocks );
		$head  = 0;
		while ( $head < $count && 'core/template-part' === $blocks[ $head ]['blockName'] ) {
			++$head;
		}
		$foot = $count;
		while ( $foot > $head && 'core/template-part' === $blocks[ $foot - 1 ]['blockName'] ) {
			--$foot;
		}

		$main = self::main( $layout );
		if ( self::is_bare( $layout ) ) {
			return $main; // Distraction-free: no header and footer template parts.
		}
		$out  = '';
		foreach ( array_slice( $blocks, 0, $head ) as $block ) {
			$out .= serialize_block( $block );
		}
		$out .= $main;
		foreach ( array_slice( $blocks, $foot ) as $block ) {
			$out .= serialize_block( $block );
		}

		if ( $wrap && $head > 0 ) {
			// Only the parts are kept: the wrapper's own markup would need its inner HTML rebuilt.
			return $out;
		}
		return $wrap ? $main : $out;
	}

	/**
	 * The main element with the layout blocks, which are marked as the layout's own (block_context()).
	 *
	 * @param array $layout  Layout.
	 * @param bool  $classic Classic theme: a div (the theme's header.php may open its own main element).
	 * @return string Block markup.
	 */
	public static function main( $layout, $classic = false ) {
		$inner = '';
		foreach ( parse_blocks( $layout['content'] ) as $block ) {
			if ( null !== $block['blockName'] ) {
				$block['attrs'][ self::MARK ] = true;
			}
			$inner .= serialize_block( $block );
		}
		$class = 'envo-layout envo-layout--' . $layout['type'] . ( $layout['id'] ? ' envo-layout-' . $layout['id'] : '' );
		$style = $layout['id'] ? self::custom_vars( $layout['id'], $layout['content'] ) : '';
		$style = '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '';
		if ( $classic ) {
			return '<div class="' . esc_attr( $class . ' alignfull' ) . '"' . $style . '>' . $inner . '</div>';
		}
		return '<main id="wp--skip-link--target" class="' . esc_attr( $class ) . '"' . $style . '>' . $inner . '</main>';
	}

	/**
	 * Classic themes: the layout rendered in wp_enqueue_scripts.
	 *
	 * @var string|null
	 */
	private static $classic_html = null;

	/**
	 * Renders the layout of a classic theme while the page styles are enqueued.
	 */
	public static function prerender_classic() {
		if ( null === self::$classic_html ) {
			self::$classic_html = self::render_classic();
		}
	}

	/**
	 * The rendered layout for classic themes (views/layout.php).
	 *
	 * @return string HTML.
	 */
	public static function render_classic() {
		if ( null !== self::$classic_html ) {
			return self::$classic_html;
		}
		$layout = self::current();
		if ( ! $layout ) {
			return '';
		}
		if ( 'shop' !== $layout['type'] && have_posts() ) {
			the_post(); // The product (WooCommerce sets the global $product) or the page.
		}
		return do_blocks( self::main( $layout, true ) );
	}

	/**
	 * The layout's top-level blocks get the page (product, cart page …) as context, like blocks in a template.
	 *
	 * @param array         $context      Context.
	 * @param array         $parsed_block Block.
	 * @param WP_Block|null $parent       Parent block.
	 * @return array
	 */
	public static function block_context( $context, $parsed_block, $parent = null ) {
		if ( empty( $parsed_block['attrs'][ self::MARK ] ) || null !== $parent || ! empty( $context['postId'] ) || ! is_singular() ) {
			return $context;
		}
		$context['postId']   = get_queried_object_id();
		$context['postType'] = get_post_type( $context['postId'] );
		return $context;
	}

	/**
	 * Body classes of a page with a layout.
	 *
	 * @param string[] $classes Classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		$layout = self::current();
		if ( $layout ) {
			$classes[] = 'has-envo-layout';
			$classes[] = 'has-envo-layout--' . $layout['type'];
			if ( self::is_bare( $layout ) ) {
				$classes[] = 'envo-layout-bare';
			}
			if ( ! wp_is_block_theme() ) {
				$classes[] = 'envo-layout-classic';
			}
		}
		return $classes;
	}

	/**
	 * Styles of the layouts inside the editor of a layout (the canvas iframe).
	 */
	public static function block_assets() {
		if ( is_admin() && '' !== self::editor_type() ) {
			wp_enqueue_style( 'envo-blocks-layouts' );
			global $post;
			$vars = $post && self::POST_TYPE === $post->post_type ? self::custom_vars( $post->ID, $post->post_content ) : '';
			if ( '' !== $vars ) {
				wp_add_inline_style( 'envo-blocks-layouts', '.envo-pal-custom{' . $vars . '}' );
			}
			if ( 'side_cart' === self::editor_type() ) {
				// The editor shows a side cart as wide as the panel.
				wp_add_inline_style( 'envo-blocks-layouts', '.editor-styles-wrapper .is-root-container{max-width:440px;margin-inline:auto;min-height:100vh;box-shadow:0 0 0 1px rgba(0,0,0,.08),0 12px 40px rgba(0,0,0,.12)}.editor-styles-wrapper .is-root-container .is-layout-flex>*{min-width:0;max-width:100%}.editor-styles-wrapper .is-root-container{overflow-x:hidden}' );
			}
		}
	}

	/**
	 * Page type of the layout open in the editor: a store layout, or a post another plugin edits as a layout (Store
	 * Demos: the layout demos); "" on other screens.
	 *
	 * @return string Page type, or "-" for a layout without a type yet, or "".
	 */
	private static function editor_type() {
		static $type = null;
		if ( null !== $type ) {
			return $type;
		}
		$post_type = self::screen_post_type();
		$type      = '';
		if ( '' === $post_type ) {
			return $type;
		}
		global $post;
		$id = $post ? (int) $post->ID : 0;
		if ( self::POST_TYPE === $post_type ) {
			$type = $id ? self::post_type_of( $id ) : '';
			$type = '' !== $type ? $type : '-';
		}
		/**
		 * Filters the page type of a post edited as a store layout (other post types than envo_layout).
		 *
		 * @param string $type      Page type, "-" (a layout without a type) or "" (not a layout).
		 * @param string $post_type Post type of the screen.
		 * @param int    $id        Post ID.
		 */
		$type = (string) apply_filters( 'envo_blocks_layout_editor_type', $type, $post_type, $id );
		return $type;
	}

	/**
	 * Post type of the current admin screen.
	 *
	 * @return string
	 */
	private static function screen_post_type() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && 'post' === $screen->base ? (string) $screen->post_type : '';
	}

	/**
	 * Editor of a layout: the product of the preview (layouts-editor.js gives it to the product blocks as context).
	 */
	public static function editor_assets() {
		$type = self::editor_type();
		if ( '' === $type ) {
			return;
		}
		$type    = self::valid_type( $type );
		global $post;
		$id      = $post ? (int) $post->ID : 0;
		$product = $id ? (int) get_post_meta( $id, self::META_PRODUCT, true ) : 0;
		if ( 'product' === $type && ( ! $product || 'product' !== get_post_type( $product ) ) ) {
			$product = self::sample_product();
		}
		wp_enqueue_script( 'envo-blocks-layouts-editor' );
		wp_add_inline_script(
			'envo-blocks-layouts-editor',
			'window.envoBlocksLayout = ' . wp_json_encode(
				array(
					'type'      => $type,
					'typeLabel' => self::type_label( $type ),
					'product'   => 'product' === $type ? $product : 0,
					'postType'  => self::screen_post_type(),
				)
			) . ';',
			'before'
		);
	}

	/* ------------------------------------------------------------------ */
	/* Sample pages for previews                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * A product to show a product layout with: a published, physical product in stock with gallery images, a
	 * variable one when there is one (the variation fields belong to the page).
	 *
	 * @param string $kind "variable" (product layouts) or "simple" (put into the cart of the cart demos).
	 * @return int Product ID or 0.
	 */
	public static function sample_product( $kind = 'variable' ) {
		static $found = array();
		if ( isset( $found[ $kind ] ) ) {
			return $found[ $kind ];
		}
		$meta = array(
			array(
				'key'   => '_stock_status',
				'value' => 'instock',
			),
			array(
				'key'   => '_virtual',
				'value' => 'no',
			),
			array(
				'key'     => '_product_image_gallery',
				'value'   => '',
				'compare' => '!=',
			),
		);
		$tries = array(
			array( $meta, $kind ),
			array( array_slice( $meta, 0, 2 ), $kind ),
			array( array(), '' ),
		);
		$ids = array();
		foreach ( $tries as $try ) {
			$args = array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_query'  => $try[0], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- One preview link.
			);
			if ( '' !== $try[1] ) {
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => $try[1],
					),
				);
			}
			$ids = get_posts( $args );
			if ( $ids ) {
				break;
			}
		}
		/**
		 * Filters the product that previews of product layouts show (and that the cart demos put into the cart).
		 *
		 * @param int    $id   Product ID.
		 * @param string $kind "variable" or "simple".
		 */
		$found[ $kind ] = (int) apply_filters( 'envo_blocks_layout_sample_product', $ids ? (int) $ids[0] : 0, $kind );
		return $found[ $kind ];
	}

	/**
	 * An order to show the order received layouts with: an order marked as a sample (meta _envo_layout_sample), the
	 * newest order of the current user, or for shop managers the newest order (visitors see only the sample).
	 *
	 * @return WC_Order|null
	 */
	public static function sample_order() {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return null;
		}
		$args = array(
			'limit'   => 1,
			'orderby' => 'date',
			'order'   => 'DESC',
			'status'  => array( 'wc-processing', 'wc-completed', 'wc-on-hold' ),
		);
		// A sample order of the site first (meta _envo_layout_sample, our demo site), then the user's own newest order,
		// then the newest order for shop managers.
		$orders = wc_get_orders(
			array_merge(
				$args,
				array(
					'meta_key'   => '_envo_layout_sample', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value' => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			)
		);
		if ( ! $orders && get_current_user_id() ) {
			$orders = wc_get_orders( array_merge( $args, array( 'customer_id' => get_current_user_id() ) ) );
		}
		if ( ! $orders && current_user_can( 'edit_shop_orders' ) ) {
			$orders = wc_get_orders( $args );
		}
		/**
		 * Filters the order that previews of order received layouts show.
		 *
		 * @param WC_Order|null $order Order.
		 */
		$order = apply_filters( 'envo_blocks_layout_sample_order', $orders ? $orders[0] : null );
		return $order instanceof WC_Order ? $order : null;
	}

	/**
	 * A page of the type to preview a layout on.
	 *
	 * @param string $type    Page type.
	 * @param int    $product Product for product layouts (0 = sample product).
	 * @return string URL or "".
	 */
	public static function sample_url( $type, $product = 0 ) {
		if ( ! function_exists( 'wc_get_page_permalink' ) ) {
			return '';
		}
		switch ( $type ) {
			case 'product':
				$product = $product && 'product' === get_post_type( $product ) ? $product : self::sample_product();
				$url     = $product ? get_permalink( $product ) : '';
				break;
			case 'shop':
				$url = wc_get_page_permalink( 'shop' );
				break;
			case 'cart':
				$url = wc_get_cart_url();
				break;
			case 'thankyou':
				$order = self::sample_order();
				$url   = $order ? $order->get_checkout_order_received_url() : '';
				break;
			case 'account':
				$url = wc_get_page_permalink( 'myaccount' );
				break;
			case 'checkout':
				$url = wc_get_checkout_url();
				break;
			case 'side_cart':
				// A product page: the panel opens over it with the sample product in the cart.
				$product = self::sample_product( 'simple' );
				$url     = $product ? get_permalink( $product ) : wc_get_page_permalink( 'shop' );
				break;
			default:
				$url = '';
		}
		/**
		 * Filters the page a layout of the type is previewed on.
		 *
		 * @param string $url  URL.
		 * @param string $type Page type.
		 */
		return (string) apply_filters( 'envo_blocks_layout_sample_url', (string) $url, $type );
	}

	/**
	 * Preview address of a layout post.
	 *
	 * @param int  $id      Post ID.
	 * @param bool $preview With unsaved changes (the editor's autosave).
	 * @return string
	 */
	public static function post_preview_url( $id, $preview = false ) {
		$url = self::sample_url( self::post_type_of( $id ), (int) get_post_meta( $id, self::META_PRODUCT, true ) );
		if ( '' === $url ) {
			return '';
		}
		$args = array( 'envo_layout' => $id );
		if ( $preview ) {
			$args['preview'] = 'true';
		}
		return add_query_arg( $args, $url );
	}

	/**
	 * The editor's Preview button opens a store page with the layout.
	 *
	 * @param string  $link Preview link.
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function preview_link( $link, $post ) {
		if ( $post && self::POST_TYPE === $post->post_type ) {
			$url = self::post_preview_url( $post->ID, true );
			return '' !== $url ? $url : $link;
		}
		return $link;
	}

	/**
	 * "View" links of a layout open a store page with it.
	 *
	 * @param string  $link Link.
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function view_link( $link, $post ) {
		if ( $post && self::POST_TYPE === $post->post_type ) {
			$url = self::post_preview_url( $post->ID );
			return '' !== $url ? $url : $link;
		}
		return $link;
	}

	/**
	 * Live demo of a design: ?envo_layout_demo=<design> opens a fitting store page with the design (a full cart for
	 * cart designs). Links of the Layouts tab and of our demo site.
	 */
	public static function demo_redirect() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview redirect.
		$id = isset( $_GET['envo_layout_demo'] ) ? sanitize_key( wp_unslash( $_GET['envo_layout_demo'] ) ) : '';
		if ( '' === $id || ! self::may_preview_designs() ) {
			return;
		}
		$design = Envo_Blocks_Library::get_section( $id, 'layouts' );
		$url    = $design ? self::sample_url( $design['layout_type'] ) : '';
		if ( '' === $url ) {
			wp_die( esc_html__( 'There is no store page to show this layout on yet (for example no product or no order).', 'envo-blocks' ), '', array( 'response' => 404 ) );
		}
		if ( in_array( $design['layout_type'], array( 'cart', 'checkout', 'side_cart' ), true ) ) {
			self::fill_demo_cart();
		}
		wp_safe_redirect( add_query_arg( 'envo_layout_design', $id, $url ) );
		exit;
	}

	/**
	 * A cart demo shows a full cart: an empty cart gets the sample simple product.
	 */
	public static function fill_demo_cart() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->cart->is_empty() ) {
			return;
		}
		$product = self::sample_product( 'simple' );
		$object  = $product ? wc_get_product( $product ) : null;
		if ( $object && $object->is_type( 'simple' ) && $object->is_purchasable() ) {
			WC()->cart->add_to_cart( $product );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Designs (the Layouts library)                                      */
	/* ------------------------------------------------------------------ */

	/**
	 * Live demo of a design: on this site when the design can be shown here (WooCommerce, and PRO designs with an
	 * active license), otherwise its page on our demo site (Store Demos › Layout Demos, /layout-demos/<design>/).
	 *
	 * @param array $design Library entry.
	 * @return string
	 */
	public static function demo_link( $design ) {
		$here = class_exists( 'WooCommerce' ) && ( ! $design['premium'] || Envo_Blocks_Library::can_import_premium( $design ) );
		/**
		 * Filters the site of the live demos of the layout designs.
		 *
		 * @param string $base Base URL.
		 */
		if ( $here ) {
			return esc_url_raw( add_query_arg( 'envo_layout_demo', $design['id'], home_url( '/' ) ) );
		}
		$base = (string) apply_filters( 'envo_blocks_layout_demo_url', self::DEMO_URL . 'layout-demos/' );
		return esc_url_raw( trailingslashit( $base ) . $design['id'] . '/' );
	}

	/**
	 * Block markup of a design: a file of this plugin, or a PRO design from the PRO plugin (only with a license).
	 *
	 * @param array $design Library entry (Envo_Blocks_Library::get_section( $id, 'layouts' )).
	 * @return array|WP_Error array( type, content, title ).
	 */
	public static function load_design( $design ) {
		if ( $design['premium'] ) {
			if ( ! Envo_Blocks_Library::can_import_premium( $design ) ) {
				return new WP_Error( 'envo_blocks_layout_locked', __( 'This layout is included in Envo Blocks PRO.', 'envo-blocks' ), array( 'status' => 403 ) );
			}
			/** This filter is documented in includes/class-envo-blocks-library.php */
			$json = apply_filters( 'envo_blocks_premium_json', null, $design, 'layouts', $design['file'] );
			if ( is_wp_error( $json ) ) {
				return $json;
			}
			if ( ! is_string( $json ) ) {
				return new WP_Error( 'envo_blocks_premium_unavailable', __( 'PRO layouts are not available. Make sure the PRO plugin is active and its license is active.', 'envo-blocks' ), array( 'status' => 403 ) );
			}
		} elseif ( ! empty( $design['remote'] ) ) {
			// A free design of the template library on our server, newer than this version.
			$json = Envo_Blocks_Remote::file( 'layouts', $design['file'] );
			if ( is_wp_error( $json ) ) {
				return $json;
			}
		} else {
			$path = ENVO_BLOCKS_PATH . 'templates/layouts/' . $design['file'];
			$json = is_file( $path ) ? file_get_contents( $path ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- A file of this plugin.
			if ( false === $json ) {
				return new WP_Error( 'envo_blocks_file_missing', __( 'Template file was not found.', 'envo-blocks' ), array( 'status' => 404 ) );
			}
		}

		$data = json_decode( $json, true );
		if ( ! is_array( $data ) || self::SCHEMA !== ( $data['schema'] ?? '' ) || $design['layout_type'] !== ( $data['type'] ?? '' ) || ! is_string( $data['content'] ?? null ) ) {
			return new WP_Error( 'envo_blocks_invalid_template', __( 'The layout file is not valid.', 'envo-blocks' ), array( 'status' => 422 ) );
		}
		return array(
			'type'    => $design['layout_type'],
			'content' => $data['content'],
			'title'   => $design['name'],
		);
	}

	/**
	 * REST: POST /envo-blocks/v1/layout {design} makes a draft layout of a design.
	 */
	public static function rest_routes() {
		register_rest_route(
			'envo-blocks/v1',
			'/layout',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_create' ),
				'permission_callback' => static function () {
					$type = get_post_type_object( self::POST_TYPE );
					return $type && current_user_can( $type->cap->create_posts );
				},
				'args'                => array(
					'design' => array(
						'type'     => 'string',
						'required' => true,
						'pattern'  => '^[a-z0-9][a-z0-9_-]{0,63}$',
					),
				),
			)
		);
	}

	/**
	 * Makes a draft layout of a design.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function rest_create( $request ) {
		$design = Envo_Blocks_Library::get_section( (string) $request->get_param( 'design' ), 'layouts' );
		if ( ! $design ) {
			return new WP_Error( 'envo_blocks_not_found', __( 'Layout not found.', 'envo-blocks' ), array( 'status' => 404 ) );
		}
		$data = self::load_design( $design );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => self::POST_TYPE,
					'post_status'  => 'draft',
					'post_title'   => $design['name'],
					'post_content' => $data['content'],
				)
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		update_post_meta( $id, self::META_TYPE, $data['type'] );
		update_post_meta( $id, self::META_DESIGN, $design['id'] );

		return rest_ensure_response(
			array(
				'id'      => $id,
				'editUrl' => esc_url_raw( admin_url( 'post.php?action=edit&post=' . $id ) ),
			)
		);
	}

	/* ------------------------------------------------------------------ */
	/* Admin                                                              */
	/* ------------------------------------------------------------------ */

	/**
	 * Envo Blocks › Store Layouts.
	 *
	 * @param array $pages Pages.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Store Layouts', 'envo-blocks' ),
			'title'      => __( 'Store Layouts', 'envo-blocks' ),
			'callback'   => array( __CLASS__, 'render_page' ),
			'capability' => 'edit_pages',
		);
		return $pages;
	}

	/**
	 * The post list of the layouts is the Store Layouts page.
	 */
	public static function list_redirect() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only redirects.
		if ( isset( $_GET['post_type'] ) && self::POST_TYPE === $_GET['post_type'] && ! isset( $_GET['post_status'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE ) );
			exit;
		}
	}

	/**
	 * A new empty layout of a type (post-new.php?post_type=envo_layout&envo_layout_type=…).
	 */
	public static function new_type() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preselects the type of a new draft.
		$type = isset( $_GET['envo_layout_type'] ) ? self::valid_type( sanitize_key( wp_unslash( $_GET['envo_layout_type'] ) ) ) : '';
		if ( '' === $type ) {
			return;
		}
		add_action(
			'save_post_' . self::POST_TYPE,
			static function ( $id, $post ) use ( $type ) {
				if ( 'auto-draft' === $post->post_status && ! get_post_meta( $id, self::META_TYPE, true ) ) {
					update_post_meta( $id, self::META_TYPE, $type );
				}
			},
			5,
			2
		);
	}

	/**
	 * Scripts of the Store Layouts page: the template window in the Layouts mode.
	 *
	 * @param string $hook Admin page.
	 */
	public static function admin_assets( $hook ) {
		if ( false === strpos( (string) $hook, self::PAGE ) ) {
			return;
		}
		wp_enqueue_style( 'envo-blocks-layouts-admin', ENVO_BLOCKS_URL . 'assets/css/layouts-admin.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/layouts-admin.css' ) );
		wp_enqueue_style( 'envo-blocks-editor', ENVO_BLOCKS_URL . 'assets/css/editor.css', array( 'wp-components' ), Envo_Blocks_Blocks::ver( 'assets/css/editor.css' ) );
		wp_enqueue_script(
			'envo-blocks-editor',
			ENVO_BLOCKS_URL . 'assets/js/editor.js',
			array( 'wp-api-fetch', 'wp-blocks', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'wp-url' ),
			Envo_Blocks_Blocks::ver( 'assets/js/editor.js' ),
			true
		);
		wp_localize_script(
			'envo-blocks-editor',
			'envoBlocksData',
			array_merge(
				Envo_Blocks_Editor::data(),
				array(
					'adminPage' => true,
					'startMode' => 'layouts',
					'postTypes' => array(),
				)
			)
		);
		wp_set_script_translations( 'envo-blocks-editor', 'envo-blocks' );
	}

	/**
	 * Store Layouts page: the layouts of the site, then the designs.
	 */
	public static function render_page() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<div class="eb-card"><p>' . esc_html__( 'Store layouts need WooCommerce.', 'envo-blocks' ) . '</p></div>';
			return;
		}
		$by_type = array();
		foreach ( get_posts(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
				'numberposts' => 200,
				'orderby'     => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
			)
		) as $post ) {
			$by_type[ self::post_type_of( $post->ID ) ][] = $post;
		}
		$off = self::is_off();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Message after an action of this page.
		$done = isset( $_GET['envo_done'] ) ? sanitize_key( wp_unslash( $_GET['envo_done'] ) ) : '';
		?>
		<?php if ( '' !== $done ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( self::done_message( $done ) ); ?></p></div>
		<?php endif; ?>
		<div class="eb-note" role="note">
			<p><?php esc_html_e( 'Store layouts change the product page, the shop, the cart, the order received page and My account in any theme: the theme header and footer stay, the part between them comes from the layout. Start from a ready-made design below or with an empty layout, edit it with blocks and publish it. A published layout is used at once; a draft is not.', 'envo-blocks' ); ?>
				<a href="https://envoblocks.com/docs/envo-blocks/store-layouts/use-store-layouts/" target="_blank" rel="noopener"><?php esc_html_e( 'Read the guide', 'envo-blocks' ); ?></a></p>
		</div>

		<div class="eb-card eb-layouts">
			<div class="eb-layouts__head">
				<h2><?php esc_html_e( 'Your store layouts', 'envo-blocks' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'envo_blocks_layouts' ); ?>
					<input type="hidden" name="action" value="envo_blocks_layouts">
					<input type="hidden" name="do" value="<?php echo $off ? 'on' : 'off'; ?>">
					<?php if ( $off ) : ?>
						<span class="eb-layouts__off"><?php esc_html_e( 'All layouts are turned off: the store shows the theme pages.', 'envo-blocks' ); ?></span>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Turn layouts on', 'envo-blocks' ); ?></button>
					<?php else : ?>
						<button type="submit" class="button" title="<?php esc_attr_e( 'The store shows the theme pages until you turn them on again. Nothing is deleted.', 'envo-blocks' ); ?>"><?php esc_html_e( 'Turn all layouts off', 'envo-blocks' ); ?></button>
					<?php endif; ?>
				</form>
			</div>
			<table class="widefat striped eb-layouts__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Page', 'envo-blocks' ); ?></th>
						<th><?php esc_html_e( 'Layout', 'envo-blocks' ); ?></th>
						<th><?php esc_html_e( 'Shown on', 'envo-blocks' ); ?></th>
						<th><?php esc_html_e( 'Status', 'envo-blocks' ); ?></th>
						<th><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'envo-blocks' ); ?></span></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( self::types() as $type => $info ) : ?>
					<?php
					$posts = isset( $by_type[ $type ] ) ? $by_type[ $type ] : array();
					$used  = self::used_for_type( $type );
					$new   = admin_url( 'post-new.php?post_type=' . self::POST_TYPE . '&envo_layout_type=' . $type );
					?>
					<?php if ( ! $posts ) : ?>
						<tr>
							<th scope="row"><strong><?php echo esc_html( $info[0] ); ?></strong><br><span class="description"><?php echo esc_html( $info[1] ); ?></span><?php self::the_template_link( $type ); ?></th>
							<td colspan="3"><span class="description"><?php esc_html_e( 'The theme page (no layout).', 'envo-blocks' ); ?></span></td>
							<td class="eb-layouts__actions"><a href="<?php echo esc_url( $new ); ?>"><?php esc_html_e( 'Empty layout', 'envo-blocks' ); ?></a></td>
						</tr>
					<?php endif; ?>
					<?php foreach ( $posts as $i => $post ) : ?>
						<?php
						$active  = 'publish' === $post->post_status;
						$rules   = self::rules( $post->ID );
						$preview = self::post_preview_url( $post->ID );
						?>
						<tr>
							<th scope="row">
								<?php if ( 0 === $i ) : ?>
									<strong><?php echo esc_html( $info[0] ); ?></strong><br><span class="description"><?php echo esc_html( $info[1] ); ?></span><?php self::the_template_link( $type ); ?>
								<?php endif; ?>
							</th>
							<td><a class="row-title" href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( get_the_title( $post ) ? get_the_title( $post ) : __( '(no title)', 'envo-blocks' ) ); ?></a></td>
							<td><?php echo esc_html( self::rules_summary( $post, $type, $rules ) ); ?></td>
							<td>
								<?php if ( ! $active ) : ?>
									<span class="eb-layouts__status"><?php esc_html_e( 'Draft – not used', 'envo-blocks' ); ?></span>
								<?php elseif ( $off ) : ?>
									<span class="eb-layouts__status"><?php esc_html_e( 'Published – all layouts are off', 'envo-blocks' ); ?></span>
								<?php elseif ( $rules && ! has_filter( 'envo_blocks_layout_match' ) ) : ?>
									<span class="eb-layouts__status is-warning"><?php esc_html_e( 'Not used: its rules need Envo Blocks PRO', 'envo-blocks' ); ?></span>
								<?php elseif ( ! $rules && $used && $used !== $post->ID ) : ?>
									<span class="eb-layouts__status is-warning">
										<?php
										/* translators: %s: layout name. */
										echo esc_html( sprintf( __( 'Not used: "%s" comes first', 'envo-blocks' ), get_the_title( $used ) ) );
										?>
									</span>
								<?php else : ?>
									<span class="eb-layouts__status is-active"><?php esc_html_e( 'Active', 'envo-blocks' ); ?></span>
								<?php endif; ?>
							</td>
							<td class="eb-layouts__actions">
								<a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php esc_html_e( 'Edit', 'envo-blocks' ); ?></a>
								<?php if ( '' !== $preview ) : ?>
									· <a href="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'envo-blocks' ); ?></a>
								<?php endif; ?>
								· <a href="<?php echo esc_url( self::action_url( $active ? 'draft' : 'publish', $post->ID ) ); ?>"><?php echo $active ? esc_html__( 'Stop using', 'envo-blocks' ) : esc_html__( 'Use it', 'envo-blocks' ); ?></a>
								· <a class="submitdelete" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php esc_html_e( 'Trash', 'envo-blocks' ); ?></a>
								<?php if ( count( $posts ) - 1 === $i ) : ?>
									<br><a href="<?php echo esc_url( $new ); ?>"><?php esc_html_e( 'Empty layout', 'envo-blocks' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
			/**
			 * Below the list of layouts (Envo Blocks PRO: a note about its rules).
			 */
			do_action( 'envo_blocks_layouts_after_list' );
			?>
		</div>

		<div id="envo-blocks-templates-app" class="eb-templates-app">
			<p class="eb-templates-app__loading"><?php esc_html_e( 'Loading layouts…', 'envo-blocks' ); ?></p>
		</div>
		<noscript><div class="eb-card"><p><?php esc_html_e( 'The template library needs JavaScript.', 'envo-blocks' ); ?></p></div></noscript>
		<?php
	}

	/**
	 * Block templates of the theme a page type replaces the main part of (Site Editor slugs).
	 *
	 * @return array Page type => template slugs.
	 */
	public static function template_slugs() {
		return array(
			'product'   => array( 'single-product' ),
			'shop'      => array( 'archive-product', 'taxonomy-product_cat', 'taxonomy-product_tag', 'taxonomy-product_attribute', 'taxonomy-product_brand', 'product-search-results' ),
			'cart'      => array( 'page-cart' ),
			'thankyou'  => array( 'order-confirmation' ),
			'account'   => array(),
			'checkout'  => array( 'page-checkout' ),
			'side_cart' => array(),
		);
	}

	/**
	 * Link to the theme template of a page type in the Site Editor (block themes), under its name on Store Layouts.
	 *
	 * @param string $type Page type.
	 */
	private static function the_template_link( $type ) {
		$slugs = self::template_slugs();
		if ( ! wp_is_block_theme() || empty( $slugs[ $type ] ) || ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		// The keys of the list are not always 0, 1 … (WooCommerce filters it).
		$template = current( get_block_templates( array( 'slug__in' => array( $slugs[ $type ][0] ) ) ) );
		if ( ! $template instanceof WP_Block_Template ) {
			return;
		}
		$url = admin_url( 'site-editor.php?postType=wp_template&postId=' . rawurlencode( $template->id ) . '&canvas=edit' );
		echo '<br><a class="eb-layouts__template" href="' . esc_url( $url ) . '">' . esc_html__( 'Theme template in the Site Editor', 'envo-blocks' ) . '</a>';
	}

	/**
	 * Site Editor: a notice on the theme templates whose main part a published layout replaces on the store.
	 */
	public static function site_editor_assets() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'site-editor' !== $screen->id || ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$map = array();
		foreach ( self::template_slugs() as $type => $slugs ) {
			$used = self::used_for_type( $type );
			$some = array_filter(
				self::candidates( $type ),
				static function ( $candidate ) {
					return (bool) self::rules( $candidate[0]->ID );
				}
			);
			if ( ( ! $used && ! $some ) || self::is_off() ) {
				continue;
			}
			$info = array(
				'title'   => $used ? get_the_title( $used ) : get_the_title( reset( $some )[0] ),
				'editUrl' => esc_url_raw( (string) get_edit_post_link( $used ? $used : reset( $some )[0]->ID, 'raw' ) ),
				'all'     => (bool) $used,
			);
			foreach ( $slugs as $slug ) {
				$map[ $slug ] = $info;
			}
		}
		if ( ! $map ) {
			return;
		}
		wp_enqueue_script( 'envo-blocks-layouts-site-editor', ENVO_BLOCKS_URL . 'assets/js/layouts-site-editor.js', array( 'wp-data', 'wp-notices', 'wp-i18n' ), Envo_Blocks_Blocks::ver( 'assets/js/layouts-site-editor.js' ), true );
		wp_add_inline_script(
			'envo-blocks-layouts-site-editor',
			'window.envoBlocksLayoutTemplates = ' . wp_json_encode(
				array(
					'templates' => $map,
					'pageUrl'   => esc_url_raw( admin_url( 'admin.php?page=' . self::PAGE ) ),
				)
			) . ';',
			'before'
		);
		wp_set_script_translations( 'envo-blocks-layouts-site-editor', 'envo-blocks' );
	}

	/**
	 * The published layout used for the whole page type (no rules), or 0.
	 *
	 * @param string $type Page type.
	 * @return int
	 */
	private static function used_for_type( $type ) {
		foreach ( self::candidates( $type ) as $candidate ) {
			if ( ! self::rules( $candidate[0]->ID ) ) {
				return $candidate[0]->ID;
			}
		}
		return 0;
	}

	/**
	 * Where a layout is shown, in words.
	 *
	 * @param WP_Post $post  Layout.
	 * @param string  $type  Page type.
	 * @param array   $rules Rules.
	 * @return string
	 */
	private static function rules_summary( $post, $type, $rules ) {
		/* translators: %s: page type, e.g. "Product page". */
		$text = sprintf( __( 'Every %s', 'envo-blocks' ), strtolower( self::type_label( $type ) ) );
		if ( 'shop' === $type ) {
			$text = __( 'The shop, all categories and product searches', 'envo-blocks' );
		} elseif ( 'side_cart' === $type ) {
			$text = __( 'Every page except the cart and the checkout', 'envo-blocks' );
		}
		/**
		 * Filters the "Shown on" text of a layout (Envo Blocks PRO describes its rules).
		 *
		 * @param string  $text  Text.
		 * @param WP_Post $post  Layout.
		 * @param array   $rules Rules.
		 */
		return (string) apply_filters( 'envo_blocks_layout_rules_summary', $text, $post, $rules );
	}

	/**
	 * Link of an action of the Store Layouts page.
	 *
	 * @param string $do Action.
	 * @param int    $id Post ID.
	 * @return string
	 */
	private static function action_url( $do, $id ) {
		return wp_nonce_url( admin_url( 'admin-post.php?action=envo_blocks_layouts&do=' . $do . '&post=' . $id ), 'envo_blocks_layouts' );
	}

	/**
	 * Message after an action.
	 *
	 * @param string $done Action.
	 * @return string
	 */
	private static function done_message( $done ) {
		$messages = array(
			'publish' => __( 'The layout is used on the store pages now.', 'envo-blocks' ),
			'draft'   => __( 'The layout is a draft now and not used.', 'envo-blocks' ),
			'off'     => __( 'All layouts are turned off.', 'envo-blocks' ),
			'on'      => __( 'Layouts are turned on.', 'envo-blocks' ),
		);
		return isset( $messages[ $done ] ) ? $messages[ $done ] : '';
	}

	/**
	 * Actions of the Store Layouts page: use / stop using a layout, all layouts off / on.
	 */
	public static function admin_action() {
		check_admin_referer( 'envo_blocks_layouts' );
		$do = isset( $_REQUEST['do'] ) ? sanitize_key( wp_unslash( $_REQUEST['do'] ) ) : '';

		if ( in_array( $do, array( 'off', 'on' ), true ) ) {
			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'envo-blocks' ), 403 );
			}
			update_option( self::OFF, 'off' === $do ? 1 : 0 );
		} elseif ( in_array( $do, array( 'publish', 'draft' ), true ) ) {
			$id   = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : 0;
			$post = $id ? get_post( $id ) : null;
			if ( ! $post || self::POST_TYPE !== $post->post_type || ! current_user_can( 'publish_post', $id ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'envo-blocks' ), 403 );
			}
			wp_update_post(
				array(
					'ID'          => $id,
					'post_status' => $do,
				)
			);
		} else {
			$do = '';
		}
		wp_safe_redirect( add_query_arg( 'envo_done', $do, admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * Meta box of the layout settings in the editor.
	 */
	public static function meta_boxes() {
		add_meta_box( 'envo-layout-settings', __( 'Layout settings', 'envo-blocks' ), array( __CLASS__, 'render_meta_box' ), self::POST_TYPE, 'side', 'high' );
	}

	/**
	 * Layout settings: page type, where it is shown (PRO), product of the preview.
	 *
	 * @param WP_Post $post Layout.
	 */
	public static function render_meta_box( $post ) {
		$type    = self::post_type_of( $post->ID );
		$product = (int) get_post_meta( $post->ID, self::META_PRODUCT, true );
		wp_nonce_field( 'envo_layout_settings', 'envo_layout_nonce' );
		?>
		<style>#envo-layout-settings .inside{overflow:hidden}#envo-layout-settings :is(select,input[type=text],.widefat){box-sizing:border-box;width:100%;max-width:100%}</style>
		<p>
			<label for="envo-layout-type"><strong><?php esc_html_e( 'Page', 'envo-blocks' ); ?></strong></label><br>
			<select id="envo-layout-type" name="envo_layout_type" class="widefat">
				<?php if ( '' === $type ) : ?>
					<option value=""><?php esc_html_e( 'Choose…', 'envo-blocks' ); ?></option>
				<?php endif; ?>
				<?php foreach ( self::types() as $key => $info ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $info[0] ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p class="description"><?php esc_html_e( 'Published = used on these pages, draft = not used. The theme header and footer stay.', 'envo-blocks' ); ?></p>
		<?php if ( has_action( 'envo_blocks_layout_rules_fields' ) ) : ?>
			<?php
			/**
			 * Fields of the rules (Envo Blocks PRO): where the layout is shown.
			 *
			 * @param WP_Post $post  Layout.
			 * @param string  $type  Page type.
			 * @param array   $rules Rules.
			 */
			do_action( 'envo_blocks_layout_rules_fields', $post, $type, self::rules( $post->ID ) );
			?>
		<?php else : ?>
			<p><strong><?php esc_html_e( 'Shown on', 'envo-blocks' ); ?></strong><br><?php esc_html_e( 'All pages of this kind.', 'envo-blocks' ); ?></p>
			<p class="description"><?php esc_html_e( 'With Envo Blocks PRO a layout can be shown only for some categories, tags or products.', 'envo-blocks' ); ?></p>
		<?php endif; ?>
		<?php self::colors_fields( $post ); ?>
		<?php if ( 'checkout' === $type ) : ?>
			<?php $bare = (string) get_post_meta( $post->ID, self::META_BARE, true ); ?>
			<?php $bare = '' === $bare ? ( false !== strpos( $post->post_content, 'envo-l-bare' ) ? '1' : '0' ) : $bare; ?>
			<p>
				<input type="hidden" name="envo_layout_bare_sent" value="1">
				<label><input type="checkbox" name="envo_layout_bare" value="1" <?php checked( '1', $bare ); ?>> <strong><?php esc_html_e( 'Distraction-free checkout', 'envo-blocks' ); ?></strong></label><br>
				<span class="description"><?php esc_html_e( 'Without the header and the footer of the theme (no menu): add the Checkout Header block for the logo, a secure checkout note and a link back to the cart.', 'envo-blocks' ); ?></span>
			</p>
		<?php endif; ?>
		<?php if ( self::TYPE_SIDE_CART === $type ) : ?>
			<?php Envo_Blocks_Side_Cart::fields( $post ); ?>
		<?php endif; ?>
		<?php if ( 'product' === $type ) : ?>
			<p>
				<label for="envo-layout-product"><strong><?php esc_html_e( 'Product of the preview', 'envo-blocks' ); ?></strong></label><br>
				<select id="envo-layout-product" name="envo_layout_product" class="widefat">
					<option value="0"><?php esc_html_e( 'Automatic', 'envo-blocks' ); ?></option>
					<?php
					$ids = get_posts(
						array(
							'post_type'   => 'product',
							'post_status' => 'publish',
							'numberposts' => 60,
							'fields'      => 'ids',
						)
					);
					if ( $product && ! in_array( $product, $ids, true ) ) {
						array_unshift( $ids, $product );
					}
					foreach ( $ids as $id ) :
						?>
						<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $product, $id ); ?>><?php echo esc_html( get_the_title( $id ) ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="description"><?php esc_html_e( 'The editor and the preview show this product. Save and reload the editor after a change.', 'envo-blocks' ); ?></span>
			</p>
		<?php endif; ?>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/* Colours                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * Colour roles of the layout palettes: key => label.
	 *
	 * @return array
	 */
	public static function color_roles() {
		return array(
			'accent'  => __( 'Accent (buttons, highlights)', 'envo-blocks' ),
			'on'      => __( 'Text on the accent', 'envo-blocks' ),
			'bg'      => __( 'Page background', 'envo-blocks' ),
			'surface' => __( 'Boxes and cards', 'envo-blocks' ),
			'ink'     => __( 'Text', 'envo-blocks' ),
			'muted'   => __( 'Secondary text', 'envo-blocks' ),
			'line'    => __( 'Lines and borders', 'envo-blocks' ),
		);
	}

	/**
	 * The palettes of the designs (the same values as the envo-pal-* classes of assets/css/layouts.css).
	 *
	 * @return array Slug => array( label, colours by role ).
	 */
	public static function palettes() {
		$p = static function ( $label, $bg, $surface, $ink, $muted, $line, $accent, $on ) {
			return array(
				'label'  => $label,
				'colors' => compact( 'accent', 'on', 'bg', 'surface', 'ink', 'muted', 'line' ),
			);
		};
		return array(
			'ivory'    => $p( __( 'Ivory', 'envo-blocks' ), '#faf7f2', '#ffffff', '#1f1b16', '#6f665c', '#e8e1d6', '#1f1b16', '#ffffff' ),
			'sage'     => $p( __( 'Sage', 'envo-blocks' ), '#f1f4ef', '#ffffff', '#1d2a22', '#5d6b62', '#dbe3d8', '#2f6b4f', '#ffffff' ),
			'ocean'    => $p( __( 'Ocean', 'envo-blocks' ), '#f2f6fb', '#ffffff', '#0f1f33', '#56657a', '#dbe4f0', '#1d5fd6', '#ffffff' ),
			'coral'    => $p( __( 'Coral', 'envo-blocks' ), '#fff5f1', '#ffffff', '#2a1712', '#7b5a51', '#f3d9cf', '#e2553d', '#ffffff' ),
			'violet'   => $p( __( 'Violet', 'envo-blocks' ), '#f6f3fd', '#ffffff', '#1d1533', '#6a6283', '#e4ddf7', '#6d3ee8', '#ffffff' ),
			'sand'     => $p( __( 'Sand', 'envo-blocks' ), '#f4ece0', '#fffaf3', '#2b2118', '#7d6d5b', '#e6d9c6', '#b4682e', '#ffffff' ),
			'blush'    => $p( __( 'Blush', 'envo-blocks' ), '#fdf2f5', '#ffffff', '#2d1720', '#82606d', '#f3d8e1', '#c2416b', '#ffffff' ),
			'sun'      => $p( __( 'Sun', 'envo-blocks' ), '#fff8e1', '#ffffff', '#241c05', '#6f6440', '#f1e3b5', '#f2b705', '#241c05' ),
			'mono'     => $p( __( 'Mono', 'envo-blocks' ), '#ffffff', '#f5f5f5', '#0a0a0a', '#666666', '#e5e5e5', '#0a0a0a', '#ffffff' ),
			'noir'     => $p( __( 'Noir (dark)', 'envo-blocks' ), '#111111', '#1c1c1c', '#f5f5f5', '#a3a3a3', '#2e2e2e', '#e8c27a', '#111111' ),
			'midnight' => $p( __( 'Midnight (dark)', 'envo-blocks' ), '#0e1726', '#16223a', '#e9eef7', '#93a1b8', '#26344f', '#4cc9f0', '#0e1726' ),
			'forest'   => $p( __( 'Forest (dark)', 'envo-blocks' ), '#102a1f', '#173a2b', '#eef5ef', '#9fb8a8', '#24503b', '#c8e86b', '#102a1f' ),
		);
	}

	/**
	 * Palette class used in a layout (envo-pal-<slug>), or "".
	 *
	 * @param string $content Block markup.
	 * @return string
	 */
	private static function palette_in( $content ) {
		return preg_match( '/\benvo-pal-([a-z0-9]+)\b/', (string) $content, $m ) ? $m[1] : '';
	}

	/**
	 * Colours of a layout by role: the saved ones, or those of the palette its blocks use.
	 *
	 * @param int    $id      Layout ID.
	 * @param string $content Block markup.
	 * @return array|null Null when the layout uses no palette (made from scratch).
	 */
	public static function layout_colors( $id, $content ) {
		$saved = get_post_meta( $id, self::META_COLORS, true );
		if ( is_array( $saved ) && count( array_intersect_key( $saved, self::color_roles() ) ) === count( self::color_roles() ) ) {
			return $saved;
		}
		$palettes = self::palettes();
		$slug     = self::palette_in( $content );
		return isset( $palettes[ $slug ] ) ? $palettes[ $slug ]['colors'] : null;
	}

	/**
	 * CSS variables of a layout with own colours (the class envo-pal-custom has no colours of its own).
	 *
	 * @param int    $id      Layout ID.
	 * @param string $content Block markup.
	 * @return string
	 */
	public static function custom_vars( $id, $content ) {
		if ( 'custom' !== self::palette_in( $content ) ) {
			return '';
		}
		$colors = self::layout_colors( $id, $content );
		if ( ! $colors ) {
			return '';
		}
		$css = '';
		foreach ( array( 'accent' => 'accent', 'on' => 'on-accent', 'bg' => 'bg', 'surface' => 'surface', 'ink' => 'ink', 'muted' => 'muted', 'line' => 'line' ) as $role => $var ) {
			$color = sanitize_hex_color( $colors[ $role ] );
			if ( $color ) {
				$css .= '--envo-' . $var . ':' . $color . ';';
			}
		}
		return $css . '--ebp-vs-accent:var(--envo-accent);';
	}

	/**
	 * Whether a background colour is dark (the dark palette helpers apply then).
	 *
	 * @param string $hex #rrggbb.
	 * @return bool
	 */
	private static function is_dark( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 6 !== strlen( $hex ) ) {
			return false;
		}
		$rgb = array_map( 'hexdec', str_split( $hex, 2 ) );
		return ( 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2] ) / 255 < 0.35;
	}

	/**
	 * Colour fields of the settings box.
	 *
	 * @param WP_Post $post Layout.
	 */
	private static function colors_fields( $post ) {
		$colors   = self::layout_colors( $post->ID, $post->post_content );
		$palettes = self::palettes();
		$current  = self::palette_in( $post->post_content );
		?>
		<p style="margin-bottom:4px"><strong><?php esc_html_e( 'Colours', 'envo-blocks' ); ?></strong></p>
		<?php if ( ! $colors ) : ?>
			<p class="description"><?php esc_html_e( 'This layout uses no palette of the designs: change its colours in the blocks.', 'envo-blocks' ); ?></p>
			<?php
			return;
		endif;
		?>
		<select id="envo-layout-palette" name="envo_layout_palette" class="widefat">
			<?php foreach ( $palettes as $slug => $palette ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" data-colors="<?php echo esc_attr( wp_json_encode( $palette['colors'] ) ); ?>" <?php selected( $current, $slug ); ?>><?php echo esc_html( $palette['label'] ); ?></option>
			<?php endforeach; ?>
			<option value="custom" <?php selected( $current, 'custom' ); ?>><?php esc_html_e( 'Own colours', 'envo-blocks' ); ?></option>
		</select>
		<span class="envo-layout-colors" style="display:grid;grid-template-columns:auto 1fr;gap:4px 8px;align-items:center;margin-top:8px">
			<?php foreach ( self::color_roles() as $role => $label ) : ?>
				<input type="color" id="envo-layout-color-<?php echo esc_attr( $role ); ?>" name="envo_layout_colors[<?php echo esc_attr( $role ); ?>]" value="<?php echo esc_attr( $colors[ $role ] ); ?>" data-role="<?php echo esc_attr( $role ); ?>">
				<label for="envo-layout-color-<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php endforeach; ?>
		</span>
		<span class="description"><?php esc_html_e( 'Save the layout to recolour it: the sections, the buttons, prices and fields of WooCommerce. A changed colour picks "Own colours".', 'envo-blocks' ); ?></span>
		<script>
		( function () {
			var select = document.getElementById( 'envo-layout-palette' );
			var inputs = document.querySelectorAll( '.envo-layout-colors input[type=color]' );
			select.addEventListener( 'change', function () {
				var option = select.options[ select.selectedIndex ];
				var colors = option.getAttribute( 'data-colors' );
				if ( ! colors ) {
					return;
				}
				colors = JSON.parse( colors );
				inputs.forEach( function ( input ) {
					input.value = colors[ input.getAttribute( 'data-role' ) ];
				} );
			} );
			inputs.forEach( function ( input ) {
				input.addEventListener( 'input', function () {
					select.value = 'custom';
				} );
			} );
		} )();
		</script>
		<?php
	}

	/**
	 * Recolours a layout when other colours were chosen: every palette colour in the block settings and the inline
	 * styles is replaced by the colour of the same role, and the palette class is changed.
	 *
	 * @param int     $id   Layout ID.
	 * @param WP_Post $post Layout.
	 */
	private static function save_colors( $id, $post ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked in save().
		if ( ! isset( $_POST['envo_layout_palette'], $_POST['envo_layout_colors'] ) || ! is_array( $_POST['envo_layout_colors'] ) ) {
			return;
		}
		$slug = sanitize_key( wp_unslash( $_POST['envo_layout_palette'] ) );
		$raw  = wp_unslash( $_POST['envo_layout_colors'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each value goes through sanitize_hex_color().
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$post = get_post( $id ); // The content saved by the editor right before.
		$from = self::layout_colors( $id, $post->post_content );
		if ( ! $from ) {
			return;
		}
		$palettes = self::palettes();
		$to       = array();
		foreach ( self::color_roles() as $role => $label ) {
			$color       = isset( $raw[ $role ] ) ? sanitize_hex_color( $raw[ $role ] ) : '';
			$to[ $role ] = $color ? strtolower( $color ) : $from[ $role ];
		}
		if ( 'custom' !== $slug && ( ! isset( $palettes[ $slug ] ) || $palettes[ $slug ]['colors'] !== $to ) ) {
			$slug = 'custom';
		}
		if ( $to === $from && self::palette_in( $post->post_content ) === $slug ) {
			return;
		}

		$content = self::recolor( $post->post_content, $from, $to );
		$tokens  = 'envo-pal-' . $slug . ( self::is_dark( $to['bg'] ) ? ' envo-l-dark' : '' );
		$content = preg_replace( '/\benvo-pal-[a-z0-9]+(?: envo-l-dark)?/', $tokens, $content );

		remove_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ), 10 );
		wp_update_post(
			wp_slash(
				array(
					'ID'           => $id,
					'post_content' => $content,
				)
			)
		);
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
		update_post_meta( $id, self::META_COLORS, $to );
	}

	/**
	 * Replaces the palette colours of a layout. A colour shared by two roles (e.g. white boxes and white text on the
	 * accent) is told apart by where it is used: backgrounds, texts, lines or accents.
	 *
	 * @param string $content Block markup.
	 * @param array  $from    Old colours by role.
	 * @param array  $to      New colours by role.
	 * @return string
	 */
	public static function recolor( $content, $from, $to ) {
		$orders = array(
			'bg'     => array( 'bg', 'surface', 'accent', 'line', 'ink', 'muted', 'on' ),
			'text'   => array( 'ink', 'muted', 'on', 'accent', 'surface', 'bg', 'line' ),
			'line'   => array( 'line', 'muted', 'ink', 'surface', 'bg', 'accent', 'on' ),
			'accent' => array( 'accent', 'ink', 'on', 'bg', 'surface', 'line', 'muted' ),
		);
		$kind = static function ( $key ) {
			$key = strtolower( $key );
			if ( preg_match( '/border|divider|track|line/', $key ) ) {
				return 'line';
			}
			if ( preg_match( '/background|^bg/', $key ) ) {
				return 'bg';
			}
			if ( preg_match( '/text|title|price|^color$|panelcolor/', $key ) ) {
				return 'text';
			}
			return 'accent';
		};
		$map = static function ( $hex, $where ) use ( $orders, $from, $to ) {
			$hex = strtolower( $hex );
			foreach ( $orders[ $where ] as $role ) {
				if ( strtolower( $from[ $role ] ) === $hex ) {
					return $to[ $role ];
				}
			}
			return $hex;
		};
		// Block settings: "key":"#rrggbb" (the border colour of a group is "color" inside "border").
		$content = preg_replace_callback(
			'/"([a-zA-Z]+)":"(#[0-9a-fA-F]{6})"/',
			static function ( $m ) use ( $kind, $map ) {
				$where = 'color' === $m[1] ? 'line' : $kind( $m[1] );
				return '"' . $m[1] . '":"' . $map( $m[2], $where ) . '"';
			},
			$content
		);
		// Inline styles of the saved HTML: background-color, color, border-color.
		$content = preg_replace_callback(
			'/(background-color|border(?:-[a-z]+)?-color|(?<![a-z-])color):\s*(#[0-9a-fA-F]{6})/',
			static function ( $m ) use ( $map ) {
				$where = 0 === strpos( $m[1], 'background' ) ? 'bg' : ( 0 === strpos( $m[1], 'border' ) ? 'line' : 'text' );
				return $m[1] . ':' . $map( $m[2], $where );
			},
			$content
		);
		return $content;
	}

	/**
	 * Saves the layout settings.
	 *
	 * @param int     $id   Post ID.
	 * @param WP_Post $post Post.
	 */
	public static function save( $id, $post ) {
		if ( ! isset( $_POST['envo_layout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envo_layout_nonce'] ) ), 'envo_layout_settings' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $id ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		$type = isset( $_POST['envo_layout_type'] ) ? self::valid_type( sanitize_key( wp_unslash( $_POST['envo_layout_type'] ) ) ) : '';
		if ( '' !== $type ) {
			update_post_meta( $id, self::META_TYPE, $type );
		}
		if ( isset( $_POST['envo_layout_product'] ) ) {
			update_post_meta( $id, self::META_PRODUCT, absint( $_POST['envo_layout_product'] ) );
		}
		if ( isset( $_POST['envo_layout_bare_sent'] ) ) {
			update_post_meta( $id, self::META_BARE, empty( $_POST['envo_layout_bare'] ) ? '0' : '1' );
		}
		/**
		 * Saves the rules (Envo Blocks PRO). The nonce is checked.
		 *
		 * @param int     $id   Layout ID.
		 * @param WP_Post $post Layout.
		 */
		self::save_colors( $id, $post );
		do_action( 'envo_blocks_layout_save', $id, $post );
	}
}
