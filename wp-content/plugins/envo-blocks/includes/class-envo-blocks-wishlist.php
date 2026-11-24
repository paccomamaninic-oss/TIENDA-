<?php
/**
 * Wishlist: the Wishlist Button block (a heart on the product page, in a product template or for a chosen product)
 * and the Wishlist block (the list on the wishlist page).
 *
 * Guests keep their list in the browser (localStorage), customers in their account (user meta), and the guest list
 * is merged into the account after signing in. The products are rendered by the REST route
 * envo-blocks/v1/wishlist/items, so cached pages show the visitor's own list. The wishlist page is set in
 * WooCommerce > Settings > Advanced > Page setup, created from the block settings, or set automatically when a page
 * with the Wishlist block is published.
 *
 * Other plugins can add to the wishlist with the filters envo_blocks_wishlist_data (front-end data),
 * envo_blocks_wishlist_shared_ids, envo_blocks_wishlist_page_top and envo_blocks_wishlist_page_tools (the Wishlist
 * block) and the script API window.envoBlocksWishlist.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wishlist.
 */
final class Envo_Blocks_Wishlist {

	/** Most products a wishlist keeps. */
	const MAX = 200;

	/** Option with the wishlist page ID. */
	const PAGE_OPTION = 'envo_blocks_wishlist_page';

	/** User meta with a customer's list. */
	const META = '_envo_blocks_wishlist';

	/**
	 * Hooks (only with WooCommerce).
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 9 );
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'save_post', array( __CLASS__, 'detect_page' ), 20, 2 );
		add_action( 'wp_footer', array( __CLASS__, 'print_data' ), 1 );
		add_filter( 'woocommerce_settings_pages', array( __CLASS__, 'page_setting' ) );
		add_filter( 'display_post_states', array( __CLASS__, 'post_state' ), 10, 2 );
	}

	/**
	 * The wishlist page (published) in the language of the visitor, or 0.
	 *
	 * @return int
	 */
	public static function page_id() {
		$page = Envo_Blocks_Multilang::translate_id( absint( get_option( self::PAGE_OPTION, 0 ) ), 'page' );

		return $page && 'publish' === get_post_status( $page ) ? $page : 0;
	}

	/**
	 * Wishlist icons (24 px grid). One shape each: outline when the product is not in the list, filled when it is.
	 *
	 * @return array<string, string> Name => SVG shapes.
	 */
	public static function icons() {
		return array(
			'heart'       => '<path d="M12 20.3s-7.6-4.5-9.3-9.5C1.6 7.4 3.8 4 7.3 4c2 0 3.7 1.1 4.7 2.8C13 5.1 14.7 4 16.7 4c3.5 0 5.7 3.4 4.6 6.8-1.7 5-9.3 9.5-9.3 9.5z"/>',
			'heart-round' => '<path d="M12 20.6l-1.3-1.2C6 15.1 3 12.3 3 8.8 3 6 5.2 3.8 8 3.8c1.6 0 3.1.7 4 1.9.9-1.2 2.4-1.9 4-1.9 2.8 0 5 2.2 5 5 0 3.5-3 6.3-7.7 10.6z"/>',
			'bookmark'    => '<path d="M6.5 3.5h11a1 1 0 0 1 1 1v16l-6.5-4.2-6.5 4.2v-16a1 1 0 0 1 1-1z"/>',
			'star'        => '<path d="M12 3.2l2.7 5.5 6 .9-4.4 4.2 1 6-5.3-2.8-5.3 2.8 1-6-4.4-4.2 6-.9z"/>',
		);
	}

	/**
	 * Wishlist icon.
	 *
	 * @param string $name  Icon name (icons()).
	 * @param string $class CSS class.
	 * @return string
	 */
	public static function icon( $name, $class = 'sb-wl-icon' ) {
		$icons = self::icons();
		$name  = isset( $icons[ $name ] ) ? $name : 'heart';

		return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
	}

	/* ------------------------------------------------------------------ storage */

	/**
	 * Positive, unique IDs.
	 *
	 * @param mixed $ids Raw IDs.
	 * @return int[]
	 */
	private static function sanitize_ids( $ids ) {
		return array_values( array_unique( array_filter( array_map( 'absint', is_array( $ids ) ? $ids : array() ) ) ) );
	}

	/**
	 * Product IDs in a customer's wishlist (oldest first).
	 *
	 * @param int $user_id User ID.
	 * @return int[]
	 */
	public static function user_ids( $user_id ) {
		$ids = get_user_meta( $user_id, self::META, true );

		return is_array( $ids ) ? self::sanitize_ids( $ids ) : array();
	}

	/**
	 * Saves a customer's wishlist.
	 *
	 * @param int   $user_id User ID.
	 * @param int[] $ids     Product IDs (oldest first).
	 * @return int[] The saved IDs.
	 */
	public static function save_user_ids( $user_id, $ids ) {
		$ids = array_slice( self::sanitize_ids( $ids ), -self::MAX );
		update_user_meta( $user_id, self::META, $ids );

		return $ids;
	}

	/**
	 * Product IDs from a request value ("1,2,3" or an array), at most MAX.
	 *
	 * @param mixed $value Raw value.
	 * @return int[]
	 */
	public static function parse_ids( $value ) {
		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}

		return array_slice( self::sanitize_ids( $value ), 0, self::MAX );
	}

	/**
	 * A product that can be shown in a wishlist: published and not hidden from the catalog.
	 *
	 * @param int $id Product ID.
	 * @return WC_Product|null
	 */
	public static function product( $id ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;

		if ( ! $product || 'publish' !== $product->get_status() || 'hidden' === $product->get_catalog_visibility() || ! empty( $product->get_parent_id() ) ) {
			return null;
		}

		return $product;
	}

	/* ------------------------------------------------------------------ products of the list */

	/**
	 * Stock text of a wishlist item.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private static function stock_html( $product ) {
		if ( ! $product->is_in_stock() ) {
			return '<span class="sb-wl-item__stock is-out">' . esc_html__( 'Out of stock', 'envo-blocks' ) . '</span>';
		}
		if ( $product->is_on_backorder() ) {
			return '<span class="sb-wl-item__stock is-backorder">' . esc_html__( 'Available on backorder', 'envo-blocks' ) . '</span>';
		}

		return '<span class="sb-wl-item__stock is-in">' . esc_html__( 'In stock', 'envo-blocks' ) . '</span>';
	}

	/**
	 * The products of a wishlist as list items.
	 *
	 * @param int[] $ids  Product IDs in the order to show.
	 * @param array $opts Parts: price, stock, cart, remove (booleans).
	 * @return array{html:string,ids:int[]} The markup and the IDs that were shown.
	 */
	public static function items_html( $ids, $opts ) {
		$opts  = wp_parse_args(
			$opts,
			array(
				'price'  => true,
				'stock'  => true,
				'cart'   => true,
				'remove' => true,
			)
		);
		$html  = '';
		$shown = array();
		$close = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';

		foreach ( $ids as $id ) {
			$product = self::product( $id );

			if ( ! $product ) {
				continue;
			}

			$shown[]   = $product->get_id();
			$permalink = $product->get_permalink();
			$name      = $product->get_name();

			$html .= '<li class="sb-wl-item" data-sb-wl-item="' . esc_attr( (string) $product->get_id() ) . '">';
			$html .= '<a class="sb-wl-item__image" href="' . esc_url( $permalink ) . '" tabindex="-1" aria-hidden="true">' . $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) . '</a>';
			$html .= '<div class="sb-wl-item__body"><a class="sb-wl-item__name" href="' . esc_url( $permalink ) . '">' . esc_html( $name ) . '</a>';

			if ( $opts['price'] && '' !== $product->get_price_html() ) {
				$html .= '<span class="sb-wl-item__price">' . wp_kses_post( $product->get_price_html() ) . '</span>';
			}
			if ( $opts['stock'] ) {
				$html .= self::stock_html( $product );
			}

			$html .= '</div>';

			if ( $opts['cart'] ) {
				$html .= '<div class="sb-wl-item__actions">';

				if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
					$html .= '<button type="button" class="sb-wl-btn sb-wl-btn--cart" data-sb-wl-cart="' . esc_attr( (string) $product->get_id() ) . '">' . esc_html__( 'Add to cart', 'envo-blocks' ) . '</button>';
				} else {
					$html .= '<a class="sb-wl-btn sb-wl-btn--cart sb-wl-btn--link" href="' . esc_url( $permalink ) . '">' . esc_html( $product->add_to_cart_text() ) . '</a>';
				}

				$html .= '</div>';
			}

			if ( $opts['remove'] ) {
				$html .= sprintf(
					'<button type="button" class="sb-wl-item__remove" data-sb-wl-remove="%1$d" aria-label="%2$s">%3$s</button>',
					$product->get_id(),
					/* translators: %s: product name. */
					esc_attr( sprintf( __( 'Remove %s from the wishlist', 'envo-blocks' ), $name ) ),
					$close
				);
			}

			$html .= '</li>';
		}

		return array(
			'html' => $html,
			'ids'  => $shown,
		);
	}

	/* ------------------------------------------------------------------ REST API */

	/**
	 * Registers the REST routes.
	 */
	public static function routes() {
		register_rest_route(
			'envo-blocks/v1',
			'/wishlist',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => static function () {
						return rest_ensure_response( array( 'ids' => self::user_ids( get_current_user_id() ) ) );
					},
					'permission_callback' => 'is_user_logged_in',
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'rest_update' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'add'    => array( 'type' => 'integer' ),
						'remove' => array( 'type' => 'integer' ),
						'merge'  => array(
							'type'  => 'array',
							'items' => array( 'type' => 'integer' ),
						),
					),
				),
			)
		);

		register_rest_route(
			'envo-blocks/v1',
			'/wishlist/items',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => static function ( WP_REST_Request $request ) {
					$opts = array();
					foreach ( array( 'price', 'stock', 'cart', 'remove' ) as $key ) {
						$opts[ $key ] = '0' !== (string) $request->get_param( $key );
					}

					return rest_ensure_response( self::items_html( self::parse_ids( (string) $request->get_param( 'ids' ) ), $opts ) );
				},
				'permission_callback' => '__return_true',
				'args'                => array(
					'ids' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);

		register_rest_route(
			'envo-blocks/v1',
			'/wishlist/page',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => static function () {
					$page = self::create_page();

					if ( ! $page ) {
						return new WP_Error( 'envo_blocks_wishlist_page', __( 'The wishlist page could not be created.', 'envo-blocks' ), array( 'status' => 500 ) );
					}

					return rest_ensure_response( self::page_info() );
				},
				'permission_callback' => static function () {
					return current_user_can( 'publish_pages' ) && current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Adds, removes or merges products in the customer's wishlist.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function rest_update( WP_REST_Request $request ) {
		$user = get_current_user_id();
		$ids  = self::user_ids( $user );

		$add = absint( $request->get_param( 'add' ) );
		if ( $add && self::product( $add ) && ! in_array( $add, $ids, true ) ) {
			$ids[] = $add;
		}

		$remove = absint( $request->get_param( 'remove' ) );
		if ( $remove ) {
			$ids = array_values( array_diff( $ids, array( $remove ) ) );
		}

		foreach ( self::parse_ids( $request->get_param( 'merge' ) ) as $id ) {
			if ( ! in_array( $id, $ids, true ) && self::product( $id ) ) {
				$ids[] = $id;
			}
		}

		return rest_ensure_response( array( 'ids' => self::save_user_ids( $user, $ids ) ) );
	}

	/* ------------------------------------------------------------------ wishlist page */

	/**
	 * Creates the wishlist page (with the Wishlist block) and sets it.
	 *
	 * @return int Page ID, or 0.
	 */
	public static function create_page() {
		$page = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => __( 'Wishlist', 'envo-blocks' ),
				'post_name'    => 'wishlist',
				'post_content' => '<!-- wp:envo-blocks/wishlist /-->',
			),
			true
		);

		if ( is_wp_error( $page ) || ! $page ) {
			return 0;
		}

		self::set_page( $page );

		return (int) $page;
	}

	/**
	 * Sets the wishlist page.
	 *
	 * @param int $page Page ID.
	 */
	public static function set_page( $page ) {
		update_option( self::PAGE_OPTION, absint( $page ) );
	}

	/**
	 * The wishlist page for the editor.
	 *
	 * @return array{id:int,title:string,url:string,edit:string}
	 */
	public static function page_info() {
		$page = self::page_id();

		return array(
			'id'    => $page,
			'title' => $page ? get_the_title( $page ) : '',
			'url'   => $page ? (string) get_permalink( $page ) : '',
			'edit'  => $page ? (string) get_edit_post_link( $page, 'raw' ) : '',
		);
	}

	/**
	 * A published page with the Wishlist block becomes the wishlist page when none is set.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public static function detect_page( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type || 'publish' !== $post->post_status || self::page_id() ) {
			return;
		}

		if ( has_block( 'envo-blocks/wishlist', $post ) ) {
			self::set_page( $post_id );
		}
	}

	/**
	 * "Wishlist page" in WooCommerce > Settings > Advanced > Page setup, after the checkout page.
	 *
	 * @param array $settings Page setup settings.
	 * @return array
	 */
	public static function page_setting( $settings ) {
		$field = array(
			'title'    => __( 'Wishlist page', 'envo-blocks' ),
			/* translators: %s: block name. */
			'desc'     => sprintf( __( 'Page with the %s block. A published page with the block becomes the wishlist page when none is set.', 'envo-blocks' ), __( 'Wishlist', 'envo-blocks' ) ),
			'id'       => self::PAGE_OPTION,
			'type'     => 'single_select_page_with_search',
			'default'  => '',
			'class'    => 'wc-page-search',
			'css'      => 'min-width:300px;',
			'args'     => array( 'exclude' => array() ),
			'desc_tip' => true,
			'autoload' => false,
		);

		$out   = array();
		$added = false;
		foreach ( $settings as $setting ) {
			$out[] = $setting;
			if ( ! $added && isset( $setting['id'] ) && 'woocommerce_checkout_page_id' === $setting['id'] ) {
				$out[] = $field;
				$added = true;
			}
		}
		if ( ! $added ) {
			$out[] = $field;
		}

		return $out;
	}

	/**
	 * "Wishlist Page" label in the list of pages.
	 *
	 * @param string[] $states Post states.
	 * @param WP_Post  $post   Post.
	 * @return string[]
	 */
	public static function post_state( $states, $post ) {
		if ( $post instanceof WP_Post && 'page' === $post->post_type && self::page_id() === $post->ID ) {
			$states['envo_blocks_wishlist'] = __( 'Wishlist Page', 'envo-blocks' );
		}

		return $states;
	}

	/* ------------------------------------------------------------------ assets and front-end data */

	/**
	 * Registers the assets and the editor data (the blocks are registered with the other WooCommerce blocks).
	 */
	public static function register() {
		wp_register_style( 'envo-blocks-wishlist', ENVO_BLOCKS_URL . 'assets/css/wishlist.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/wishlist.css' ) );
		wp_register_script(
			'envo-blocks-wishlist',
			ENVO_BLOCKS_URL . 'assets/js/wishlist-view.js',
			array(),
			Envo_Blocks_Blocks::ver( 'assets/js/wishlist-view.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	/**
	 * Data of the editor script (the wishlist page and who may create it).
	 *
	 * @return array
	 */
	public static function editor_data() {
		return array(
			'page'        => self::page_info(),
			'canCreate'   => current_user_can( 'publish_pages' ) && current_user_can( 'manage_options' ),
			'settingsUrl' => admin_url( 'admin.php?page=wc-settings&tab=advanced' ),
			'icons'       => array_map(
				static function ( $name ) {
					return self::icon( $name );
				},
				array_combine( array_keys( self::icons() ), array_keys( self::icons() ) )
			),
		);
	}

	/**
	 * Front-end data of the view script (added once, before the script is printed).
	 */
	public static function print_data() {
		if ( ! wp_script_is( 'envo-blocks-wishlist', 'enqueued' ) ) {
			return;
		}

		$logged_in = is_user_logged_in();
		$page      = self::page_id();

		$data = array(
			'loggedIn' => $logged_in,
			'ids'      => $logged_in ? self::user_ids( get_current_user_id() ) : array(),
			'rest'     => esc_url_raw( rest_url( 'envo-blocks/v1/wishlist' ) ),
			'nonce'    => $logged_in ? wp_create_nonce( 'wp_rest' ) : '',
			'cartApi'  => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
			'pageUrl'  => $page ? esc_url_raw( get_permalink( $page ) ) : '',
			'icon'     => self::icon( 'heart' ),
			'max'      => self::MAX,
			'i18n'     => array(
				/* translators: %s: product name. */
				'add'        => __( 'Add %s to the wishlist', 'envo-blocks' ),
				/* translators: %s: product name. */
				'remove'     => __( 'Remove %s from the wishlist', 'envo-blocks' ),
				'added'      => __( 'Added to your wishlist', 'envo-blocks' ),
				'removed'    => __( 'Removed from your wishlist', 'envo-blocks' ),
				'view'       => __( 'View', 'envo-blocks' ),
				'toCart'     => __( 'Added to the cart', 'envo-blocks' ),
				'inCart'     => __( 'In the cart', 'envo-blocks' ),
				'error'      => __( 'Something went wrong. Please try again.', 'envo-blocks' ),
				/* translators: %d: number of products. */
				'one'        => _n( '%d product', '%d products', 1, 'envo-blocks' ),
				/* translators: %d: number of products. */
				'few'        => _n( '%d product', '%d products', 3, 'envo-blocks' ),
				/* translators: %d: number of products. */
				'many'       => _n( '%d product', '%d products', 5, 'envo-blocks' ),
			),
		);

		/**
		 * Filters the front-end data of the wishlist script (window.envoBlocksWishlistData).
		 *
		 * @param array $data Data.
		 */
		$data = apply_filters( 'envo_blocks_wishlist_data', $data );

		wp_add_inline_script( 'envo-blocks-wishlist', 'window.envoBlocksWishlistData = ' . wp_json_encode( $data ) . ';', 'before' );
	}

	/**
	 * Tags and attributes of the wishlist markup, for wp_kses(): the blocks' HTML plus the states of the buttons.
	 *
	 * @return array
	 */
	public static function allowed_html() {
		$allowed = Envo_Blocks_Blocks::allowed_html( true );
		$extra   = array(
			'aria-pressed' => true,
			'aria-live'    => true,
			'aria-busy'    => true,
			'aria-hidden'  => true,
			'aria-label'   => true,
			'tabindex'     => true,
			'hidden'       => true,
		);
		foreach ( array( 'button', 'p', 'a', 'div', 'span', 'ul', 'li', 'h2' ) as $tag ) {
			$allowed[ $tag ] = array_merge( isset( $allowed[ $tag ] ) ? $allowed[ $tag ] : array(), $extra );
		}

		return $allowed;
	}

	/* ------------------------------------------------------------------ Wishlist Button block */

	/**
	 * Product of a block: chosen in the block, from the product template, or the product page.
	 *
	 * @param array         $attributes Block attributes (productId).
	 * @param WP_Block|null $block      Block instance.
	 * @return WC_Product|null
	 */
	public static function block_product( $attributes, $block ) {
		$id = isset( $attributes['productId'] ) ? absint( $attributes['productId'] ) : 0;

		if ( ! $id && $block instanceof WP_Block && ! empty( $block->context['postId'] ) && 'product' === get_post_type( $block->context['postId'] ) ) {
			$id = absint( $block->context['postId'] );
		}
		if ( ! $id && function_exists( 'is_product' ) && is_product() ) {
			$id = get_queried_object_id();
		}
		if ( ! $id && isset( $GLOBALS['product'] ) && $GLOBALS['product'] instanceof WC_Product ) {
			$id = $GLOBALS['product']->get_id();
		}
		if ( ! $id && Envo_Blocks_Blocks::is_preview() && function_exists( 'wc_get_products' ) ) {
			$latest = wc_get_products(
				array(
					'limit'  => 1,
					'status' => 'publish',
					'return' => 'ids',
				)
			);
			$id     = $latest ? (int) $latest[0] : 0;
		}

		return $id && function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
	}

	/**
	 * Wishlist Button block.
	 *
	 * @param array         $attributes Block attributes.
	 * @param WP_Block|null $block      Block instance.
	 * @return string
	 */
	public static function render_button( $attributes, $block ) {
		$attributes = is_array( $attributes ) ? $attributes : array();
		$product    = self::block_product( $attributes, $block );

		if ( ! $product ) {
			return '';
		}

		$style = Envo_Blocks_Blocks::choice( $attributes, 'buttonStyle', array( 'button', 'icon', 'link' ) );
		$icon  = isset( $attributes['icon'] ) && is_string( $attributes['icon'] ) ? $attributes['icon'] : 'heart';
		$add   = Envo_Blocks_Blocks::text( $attributes, 'addText', __( 'Add to wishlist', 'envo-blocks' ) );
		$added = Envo_Blocks_Blocks::text( $attributes, 'addedText', __( 'In your wishlist', 'envo-blocks' ) );
		$id    = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		$name  = wp_strip_all_tags( $product->get_name() );

		$vars  = sprintf( '--sb-wlb-icon:%dpx;', Envo_Blocks_Blocks::int( $attributes, 'iconSize', 20, 14, 48 ) );
		$vars .= Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'iconColor'        => '--sb-wlb-color',
				'activeColor'      => '--sb-wlb-active',
				'buttonBackground' => '--sb-wlb-bg',
				'borderColor'      => '--sb-wlb-border',
			)
		);

		$wrapper = get_block_wrapper_attributes(
			array(
				'class' => 'sb-wlb sb-wlb--' . $style,
				'style' => $vars,
			)
		);

		wp_enqueue_script( 'envo-blocks-wishlist' );

		return sprintf(
			'<div %1$s><button type="button" class="sb-wl-toggle sb-wlb__btn" data-sb-wl-id="%2$d" data-sb-wl-name="%3$s" aria-pressed="false" aria-label="%4$s">%5$s%6$s</button></div>',
			$wrapper,
			$id,
			esc_attr( $name ),
			/* translators: %s: product name. */
			esc_attr( sprintf( __( 'Add %s to the wishlist', 'envo-blocks' ), $name ) ),
			self::icon( $icon, 'sb-wl-icon sb-wlb__icon' ),
			'icon' === $style ? '' : '<span class="sb-wlb__text" data-add="' . esc_attr( $add ) . '" data-added="' . esc_attr( $added ) . '">' . esc_html( $add ) . '</span>'
		);
	}

	/* ------------------------------------------------------------------ Wishlist block (the list) */

	/**
	 * Wishlist block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_page( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : array();
		$preview    = Envo_Blocks_Blocks::is_preview();
		$layout     = 'list' === ( isset( $attributes['layout'] ) ? $attributes['layout'] : '' ) ? 'list' : 'grid';
		$columns    = Envo_Blocks_Blocks::int( $attributes, 'columns', 4, 1, 6 );

		/**
		 * Filters the products of a shared list shown read-only by the Wishlist block (none by default).
		 *
		 * @param int[] $shared     Product IDs.
		 * @param array $attributes Block attributes.
		 */
		$shared = $preview ? array() : self::parse_ids( apply_filters( 'envo_blocks_wishlist_shared_ids', array(), $attributes ) );

		$opts = array(
			'price'  => ! isset( $attributes['showPrice'] ) || (bool) $attributes['showPrice'],
			'stock'  => ! isset( $attributes['showStock'] ) || (bool) $attributes['showStock'],
			'cart'   => ! isset( $attributes['showAddToCart'] ) || (bool) $attributes['showAddToCart'],
			'remove' => ! $shared,
		);

		$config = array(
			'layout'      => $layout,
			'opts'        => $opts,
			'shared'      => $shared,
			'removeOnAdd' => ! empty( $attributes['removeOnAdd'] ),
		);

		$vars  = sprintf( '--sb-wl-cols:%d;', $columns );
		$vars .= Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'cardBackground'  => '--sb-wl-card-bg',
				'textColor'       => '--sb-wl-text',
				'accentColor'     => '--sb-wl-accent',
				'accentTextColor' => '--sb-wl-accent-text',
			)
		);

		// Customers (and shared lists, and the editor preview) get the products in the HTML, guests from the script.
		$ids = array();
		if ( $preview ) {
			$ids = wc_get_products(
				array(
					'limit'  => 'list' === $layout ? 3 : $columns,
					'status' => 'publish',
					'return' => 'ids',
				)
			);
		} elseif ( $shared ) {
			$ids = $shared;
		} elseif ( is_user_logged_in() ) {
			$ids = array_reverse( self::user_ids( get_current_user_id() ) );
		}

		$rendered = $ids || is_user_logged_in() || $preview ? self::items_html( $ids, $opts ) : null;
		$count    = $rendered ? count( $rendered['ids'] ) : 0;

		$wrapper = get_block_wrapper_attributes(
			array(
				'class'               => 'sb-wl-page sb-wl-page--' . $layout . ( $shared ? ' is-shared' : '' ) . ( null === $rendered ? ' is-loading' : '' ) . ( $rendered && ! $count ? ' is-empty' : '' ),
				'style'               => $vars,
				'data-sb-wl-page'     => wp_json_encode( $config ),
				'data-sb-wl-rendered' => $rendered ? implode( ',', $rendered['ids'] ) : '',
			)
		);

		$context = array(
			'shared' => $shared,
			'cart'   => $opts['cart'],
		);

		$html = '<div ' . $wrapper . '>';

		/**
		 * Filters the HTML above the bar of the Wishlist block (e.g. a note about a shared list).
		 *
		 * @param string $html       HTML.
		 * @param array  $attributes Block attributes.
		 * @param array  $context    shared (int[]), cart (bool).
		 */
		$html .= apply_filters( 'envo_blocks_wishlist_page_top', '', $attributes, $context );

		$html .= '<div class="sb-wl-page__bar">';
		$html .= '<p class="sb-wl-page__count" aria-live="polite">' . ( $count ? esc_html( sprintf( /* translators: %d: number of products. */ _n( '%d product', '%d products', $count, 'envo-blocks' ), $count ) ) : '' ) . '</p>';

		/**
		 * Filters the buttons on the right of the bar of the Wishlist block.
		 *
		 * @param string $html       HTML.
		 * @param array  $attributes Block attributes.
		 * @param array  $context    shared (int[]), cart (bool).
		 */
		$html .= '<div class="sb-wl-page__tools">' . apply_filters( 'envo_blocks_wishlist_page_tools', '', $attributes, $context ) . '</div>';
		$html .= '</div>';
		$html .= '<ul class="sb-wl-list sb-wl-list--' . $layout . '">';
		$html .= $rendered ? $rendered['html'] : str_repeat( '<li class="sb-wl-item sb-wl-item--skeleton" aria-hidden="true"><span class="sb-wl-item__image"></span><span class="sb-wl-item__body"><span></span><span></span></span></li>', 'list' === $layout ? 2 : min( 4, $columns ) );
		$html .= '</ul>';

		$html .= '<div class="sb-wl-empty">' . self::icon( 'heart', 'sb-wl-icon sb-wl-empty__icon' );
		$html .= '<h2 class="sb-wl-empty__title">' . esc_html( Envo_Blocks_Blocks::text( $attributes, 'emptyTitle', __( 'Your wishlist is empty', 'envo-blocks' ) ) ) . '</h2>';
		$html .= '<p class="sb-wl-empty__text">' . esc_html( Envo_Blocks_Blocks::text( $attributes, 'emptyText', __( 'Tap the heart on any product to save it for later.', 'envo-blocks' ) ) ) . '</p>';
		$html .= '<a class="sb-wl-btn" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html( Envo_Blocks_Blocks::text( $attributes, 'emptyButtonText', __( 'Continue shopping', 'envo-blocks' ) ) ) . '</a></div>';

		$html .= '</div>';

		if ( ! $preview ) {
			wp_enqueue_script( 'envo-blocks-wishlist' );
		}

		return $html;
	}
}
