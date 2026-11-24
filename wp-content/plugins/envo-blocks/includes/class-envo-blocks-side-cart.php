<?php
/**
 * Side cart: a Store Layout of the type "side_cart" is the content of a cart panel that slides in from the side of
 * every page – opened by the WooCommerce Mini-Cart, cart links of the theme, the Sticky Cart block of Envo Blocks PRO
 * and (optionally) after Add to cart.
 *
 * Cache safe: the panel HTML printed in the footer is the same for every visitor (no cart data in it). The blocks of
 * the panel (Cart Items, Cart Summary, Free Shipping Bar, Coupon Box …) get the cart from the Store API in the
 * browser (assets/js/side-cart-view.js). Not printed on the cart and the checkout.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Side cart.
 */
final class Envo_Blocks_Side_Cart {

	const TYPE = 'side_cart';
	const META = '_envo_side_cart';

	/**
	 * Layout of the panel on this request (null = not looked up yet).
	 *
	 * @var array|false|null
	 */
	private static $layout = null;

	/**
	 * The rendered panel (rendered while the page styles are enqueued, so the block styles are in the head).
	 *
	 * @var string
	 */
	private static $html = '';

	/**
	 * Whether the panel blocks are being rendered (the blocks render nothing elsewhere on the front end).
	 *
	 * @var bool
	 */
	private static $rendering = false;

	/**
	 * A product was added to the cart on this request (a form, not AJAX): the panel opens on the page.
	 *
	 * @var bool
	 */
	private static $added = false;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'prerender' ), 5 ); // Before the block support styles are printed (10).
		add_action( 'wp_footer', array( __CLASS__, 'footer' ), 20 );
		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'added' ) );
		add_filter( 'render_block_data', array( __CLASS__, 'mini_cart_data' ) );
		add_filter( 'render_block_woocommerce/mini-cart', array( __CLASS__, 'mini_cart_html' ) );
		add_action( 'envo_blocks_layout_save', array( __CLASS__, 'save' ) );
	}

	/**
	 * Script, style and the blocks of the panel.
	 */
	public static function register() {
		wp_register_style( 'envo-blocks-side-cart', ENVO_BLOCKS_URL . 'assets/css/side-cart.css', array( 'envo-blocks-layouts' ), Envo_Blocks_Blocks::ver( 'assets/css/side-cart.css' ) );
		wp_register_script(
			'envo-blocks-side-cart',
			ENVO_BLOCKS_URL . 'assets/js/side-cart-view.js',
			array(),
			Envo_Blocks_Blocks::ver( 'assets/js/side-cart-view.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		foreach ( array( 'cart-items', 'cart-summary' ) as $name ) {
			register_block_type( ENVO_BLOCKS_PATH . 'blocks/' . $name );
		}
	}

	/**
	 * The side cart layout of this request, or false.
	 *
	 * @return array|false
	 */
	public static function layout() {
		if ( null !== self::$layout ) {
			return self::$layout;
		}
		self::$layout = false;
		if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() || ! function_exists( 'WC' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}
		if ( is_cart() || is_checkout() ) {
			return false; // The cart and the checkout show the cart themselves.
		}
		self::$layout = Envo_Blocks_Layouts::resolve( self::TYPE );
		return self::$layout;
	}

	/**
	 * Whether the store has a side cart on this page (Envo Blocks PRO: the Sticky Cart opens it then).
	 *
	 * @return bool
	 */
	public static function active() {
		return (bool) self::layout();
	}

	/**
	 * Settings of a side cart layout.
	 *
	 * @param int $id Layout ID (0 = the defaults).
	 * @return array side (right, left), width (px), open_on_add, links (cart icons and links open the panel).
	 */
	public static function settings( $id ) {
		$saved = $id ? get_post_meta( $id, self::META, true ) : array();
		$saved = is_array( $saved ) ? $saved : array();
		$width = isset( $saved['width'] ) ? (int) $saved['width'] : 420;
		return array(
			'side'        => isset( $saved['side'] ) && 'left' === $saved['side'] ? 'left' : 'right',
			'width'       => max( 320, min( 640, $width ) ),
			'open_on_add' => ! isset( $saved['open_on_add'] ) || ! empty( $saved['open_on_add'] ),
			'links'       => ! isset( $saved['links'] ) || ! empty( $saved['links'] ),
		);
	}

	/**
	 * A product was added with a form (no AJAX): open the panel on the next page.
	 */
	public static function added() {
		if ( ! wp_doing_ajax() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			self::$added = true;
		}
	}

	/**
	 * Renders the panel while the page styles are enqueued.
	 */
	public static function prerender() {
		$layout = self::layout();
		if ( ! $layout ) {
			return;
		}
		wp_enqueue_style( 'envo-blocks-layouts' );
		wp_enqueue_style( 'envo-blocks-side-cart' );
		wp_enqueue_script( 'envo-blocks-side-cart' );

		$settings = self::settings( $layout['id'] );
		$preview  = $layout['preview'];
		$close    = '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
		$config   = array(
			'api'         => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
			'products'    => esc_url_raw( rest_url( 'wc/store/v1/products' ) ),
			'cartUrl'     => esc_url_raw( wc_get_cart_url() ),
			'checkoutUrl' => esc_url_raw( wc_get_checkout_url() ),
			'placeholder' => esc_url_raw( wc_placeholder_img_src( 'woocommerce_thumbnail' ) ),
			'taxIncl'     => 'incl' === get_option( 'woocommerce_tax_display_cart' ),
			'openOnAdd'   => $settings['open_on_add'],
			'links'       => $settings['links'],
			'open'        => $preview || self::$added, // Previews and demos open at once; so does a page after Add to cart.
			'i18n'        => array(
				/* translators: %d: number of items. */
				'items'    => _n( '%d item', '%d items', 2, 'envo-blocks' ),
				/* translators: %d: number of items. */
				'item'     => _n( '%d item', '%d items', 1, 'envo-blocks' ),
				/* translators: %s: product name. */
				'remove'   => __( 'Remove %s from the cart', 'envo-blocks' ),
				/* translators: %s: product name. */
				'decrease' => __( 'Decrease the quantity of %s', 'envo-blocks' ),
				/* translators: %s: product name. */
				'increase' => __( 'Increase the quantity of %s', 'envo-blocks' ),
				/* translators: %s: product name. */
				'quantity' => __( 'Quantity of %s', 'envo-blocks' ),
				/* translators: %s: product name. */
				'removed'  => __( '%s was removed from the cart.', 'envo-blocks' ),
				'error'    => __( 'The cart could not be updated. Please try again.', 'envo-blocks' ),
				'save'     => __( 'You save', 'envo-blocks' ),
			),
		);

		self::$rendering = true;
		$inner           = do_blocks( $layout['content'] );
		self::$rendering = false;

		$vars  = $layout['id'] ? Envo_Blocks_Layouts::custom_vars( $layout['id'], $layout['content'] ) : '';
		$class = 'envo-layout envo-layout--side_cart envo-sc__content' . ( $layout['id'] ? ' envo-layout-' . $layout['id'] : '' );

		self::$html  = '<div class="envo-sc is-empty is-pending" id="envo-side-cart" data-envo-sc="' . esc_attr( (string) wp_json_encode( $config ) ) . '">';
		self::$html .= '<div class="envo-sc__overlay" hidden></div>';
		self::$html .= '<div class="envo-sc__panel envo-sc__panel--' . esc_attr( $settings['side'] ) . '" style="--envo-sc-width:' . (int) $settings['width'] . 'px" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Cart', 'envo-blocks' ) . '" tabindex="-1" hidden>';
		self::$html .= '<button type="button" class="envo-sc__close" aria-label="' . esc_attr__( 'Close the cart', 'envo-blocks' ) . '">' . $close . '</button>';
		self::$html .= '<div class="' . esc_attr( $class ) . '"' . ( '' !== $vars ? ' style="' . esc_attr( $vars ) . '"' : '' ) . '>' . $inner . '</div>';
		self::$html .= '<div class="envo-sc__loading" aria-hidden="true"><span></span></div>';
		self::$html .= '<p class="envo-sc__status screen-reader-text" role="status" aria-live="polite"></p>';
		self::$html .= '</div></div>';
	}

	/**
	 * Prints the panel.
	 */
	public static function footer() {
		if ( '' !== self::$html ) {
			echo self::$html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks, escaped above.
		}
	}

	/**
	 * Whether the blocks of the panel render now: in the panel, and in the editor preview.
	 *
	 * @return bool
	 */
	public static function rendering() {
		return self::$rendering;
	}

	/**
	 * The WooCommerce Mini-Cart does not open its own drawer after Add to cart when the side cart does.
	 *
	 * @param array $block Parsed block.
	 * @return array
	 */
	public static function mini_cart_data( $block ) {
		if ( 'woocommerce/mini-cart' === ( $block['blockName'] ?? '' ) && self::active() ) {
			$block['attrs']['addToCartBehaviour'] = 'none';
		}
		return $block;
	}

	/**
	 * Marks the Mini-Cart: its button opens the side cart (side-cart-view.js).
	 *
	 * @param string $html Block HTML.
	 * @return string
	 */
	public static function mini_cart_html( $html ) {
		if ( ! self::active() || ! self::settings( self::$layout['id'] )['links'] ) {
			return $html;
		}
		$tags = new WP_HTML_Tag_Processor( $html );
		if ( $tags->next_tag( array( 'class_name' => 'wc-block-mini-cart' ) ) ) {
			$tags->set_attribute( 'data-envo-sc-mini-cart', '1' );
			return $tags->get_updated_html();
		}
		return $html;
	}

	/* ------------------------------------------------------------------ */
	/* Settings of a layout                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Fields in the Layout settings box of a side cart layout.
	 *
	 * @param WP_Post $post Layout.
	 */
	public static function fields( $post ) {
		$s = self::settings( $post->ID );
		?>
		<p>
			<label for="envo-sc-side"><strong><?php esc_html_e( 'Panel', 'envo-blocks' ); ?></strong></label><br>
			<select id="envo-sc-side" name="envo_side_cart[side]" class="widefat">
				<option value="right" <?php selected( $s['side'], 'right' ); ?>><?php esc_html_e( 'Slides in from the right', 'envo-blocks' ); ?></option>
				<option value="left" <?php selected( $s['side'], 'left' ); ?>><?php esc_html_e( 'Slides in from the left', 'envo-blocks' ); ?></option>
			</select>
		</p>
		<p>
			<label for="envo-sc-width"><strong><?php esc_html_e( 'Width (px)', 'envo-blocks' ); ?></strong></label><br>
			<input type="number" id="envo-sc-width" name="envo_side_cart[width]" class="widefat" min="320" max="640" step="10" value="<?php echo esc_attr( $s['width'] ); ?>">
		</p>
		<p>
			<input type="hidden" name="envo_side_cart[sent]" value="1">
			<label><input type="checkbox" name="envo_side_cart[open_on_add]" value="1" <?php checked( $s['open_on_add'] ); ?>> <?php esc_html_e( 'Open after Add to cart', 'envo-blocks' ); ?></label><br>
			<label><input type="checkbox" name="envo_side_cart[links]" value="1" <?php checked( $s['links'] ); ?>> <?php esc_html_e( 'Cart icons and cart links open it', 'envo-blocks' ); ?></label>
		</p>
		<p class="description"><?php esc_html_e( 'The WooCommerce Mini-Cart, the cart links of the theme and the Sticky Cart of Envo Blocks PRO open this panel. Not shown on the cart and the checkout.', 'envo-blocks' ); ?></p>
		<?php
	}

	/**
	 * Saves the fields (the nonce of the box is checked by Envo_Blocks_Layouts::save()).
	 *
	 * @param int $id Layout ID.
	 */
	public static function save( $id ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked by Envo_Blocks_Layouts::save().
		if ( ! isset( $_POST['envo_side_cart']['sent'] ) ) {
			return;
		}
		$raw = wp_unslash( $_POST['envo_side_cart'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below.
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		update_post_meta(
			$id,
			self::META,
			array(
				'side'        => isset( $raw['side'] ) && 'left' === $raw['side'] ? 'left' : 'right',
				'width'       => isset( $raw['width'] ) ? max( 320, min( 640, absint( $raw['width'] ) ) ) : 420,
				'open_on_add' => ! empty( $raw['open_on_add'] ),
				'links'       => ! empty( $raw['links'] ),
			)
		);
	}

	/* ------------------------------------------------------------------ */
	/* Sample cart of the editor previews                                 */
	/* ------------------------------------------------------------------ */

	/**
	 * Items of the editor preview: the cart of the editor, or sample products.
	 *
	 * @return array List of array( name, image, price, quantity, meta, link ).
	 */
	public static function sample_items() {
		$items = array();
		if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
			foreach ( array_slice( WC()->cart->get_cart(), 0, 3 ) as $item ) {
				$product = $item['data'];
				if ( $product instanceof WC_Product ) {
					$items[] = self::sample_item( $product, (int) $item['quantity'] );
				}
			}
			return $items;
		}
		$ids = array_unique( array_filter( array( Envo_Blocks_Layouts::sample_product( 'simple' ), Envo_Blocks_Layouts::sample_product() ) ) );
		foreach ( $ids as $i => $id ) {
			$product = wc_get_product( $id );
			if ( $product ) {
				$items[] = self::sample_item( $product, 0 === $i ? 1 : 2 );
			}
		}
		return $items;
	}

	/**
	 * One preview item.
	 *
	 * @param WC_Product $product  Product.
	 * @param int        $quantity Quantity.
	 * @return array
	 */
	private static function sample_item( $product, $quantity ) {
		$image = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
		return array(
			'name'     => $product->get_name(),
			'image'    => $image ? $image : wc_placeholder_img_src( 'woocommerce_thumbnail' ),
			'price'    => self::plain_price( (float) wc_get_price_to_display( $product ) * $quantity ),
			'regular'  => $product->is_on_sale() ? self::plain_price( (float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) * $quantity ) : '',
			'quantity' => $quantity,
			'meta'     => $product->is_type( 'variable' ) ? __( 'Colour: Black', 'envo-blocks' ) : '',
		);
	}

	/**
	 * Subtotal of the preview items.
	 *
	 * @return string
	 */
	public static function sample_subtotal() {
		if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
			return wp_strip_all_tags( html_entity_decode( WC()->cart->get_cart_subtotal(), ENT_QUOTES, 'UTF-8' ) );
		}
		$total = 0.0;
		$ids   = array_unique( array_filter( array( Envo_Blocks_Layouts::sample_product( 'simple' ), Envo_Blocks_Layouts::sample_product() ) ) );
		foreach ( $ids as $i => $id ) {
			$product = wc_get_product( $id );
			$total  += $product ? (float) wc_get_price_to_display( $product ) * ( 0 === $i ? 1 : 2 ) : 0;
		}
		return self::plain_price( $total );
	}

	/**
	 * A price as plain text (to be escaped).
	 *
	 * @param float $amount Amount.
	 * @return string
	 */
	public static function plain_price( $amount ) {
		return wp_strip_all_tags( html_entity_decode( wc_price( $amount ), ENT_QUOTES, 'UTF-8' ) );
	}
}
