<?php
/**
 * Develop by : Saiful Islam (aThemeArt)
 *
 * Frontend – cart rendering & AJAX (Free limited edition).
 *
 * @package ATA_WC_Smart_Popup_Cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ATA_WC_Smart_Popup_Cart_Frontend
 */
class ATA_WC_Smart_Popup_Cart_Frontend {

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var ATA_WC_Smart_Popup_Cart_Frontend|null
	 */
	protected static $instance = null;

	/**
	 * @return ATA_WC_Smart_Popup_Cart_Frontend
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->options = ata_wcspc_free_get_option();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'render_cart_shell' ) );
		add_action( 'wp_head', array( $this, 'inline_css' ) );

		add_action( 'wp_ajax_wcspc_get_cart', array( $this, 'ajax_get_cart' ) );
		add_action( 'wp_ajax_nopriv_wcspc_get_cart', array( $this, 'ajax_get_cart' ) );
		add_action( 'wp_ajax_wcspc_remove_item', array( $this, 'ajax_remove_item' ) );
		add_action( 'wp_ajax_nopriv_wcspc_remove_item', array( $this, 'ajax_remove_item' ) );
		add_action( 'wp_ajax_wcspc_update_qty', array( $this, 'ajax_update_qty' ) );
		add_action( 'wp_ajax_nopriv_wcspc_update_qty', array( $this, 'ajax_update_qty' ) );
		add_action( 'wp_ajax_wcspc_add_variation_to_cart', array( $this, 'ajax_add_variation' ) );
		add_action( 'wp_ajax_nopriv_wcspc_add_variation_to_cart', array( $this, 'ajax_add_variation' ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'perfect-scrollbar', ATA_WCSPC_FREE_URL . 'assets/perfect-scrollbar/css/perfect-scrollbar.min.css', array(), '1.0.0' );
		wp_enqueue_style( 'perfect-scrollbar-theme', ATA_WCSPC_FREE_URL . 'assets/perfect-scrollbar/css/custom-theme.css', array(), '1.0.0' );
		wp_enqueue_script( 'perfect-scrollbar', ATA_WCSPC_FREE_URL . 'assets/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js', array( 'jquery' ), '1.0.0', true );

		wp_enqueue_style( 'ata-wcspc-free-fonts', ATA_WCSPC_FREE_URL . 'assets/css/fonts.css', array(), ATA_WCSPC_FREE_VERSION );
		wp_enqueue_style( 'ata-wcspc-free-frontend', ATA_WCSPC_FREE_URL . 'assets/css/frontend.css', array(), ATA_WCSPC_FREE_VERSION );

		wp_enqueue_script( 'ata-wcspc-free-ajax-atc', ATA_WCSPC_FREE_URL . 'assets/js/woo-ajax-add-to-cart.js', array( 'jquery' ), ATA_WCSPC_FREE_VERSION, true );
		wp_enqueue_script( 'ata-wcspc-free-frontend', ATA_WCSPC_FREE_URL . 'assets/js/frontend.js', array( 'jquery' ), ATA_WCSPC_FREE_VERSION, true );

		wp_localize_script(
			'ata-wcspc-free-frontend',
			'wcspcVars',
			array(
				'ajaxurl'             => admin_url( 'admin-ajax.php' ),
				'nonce'               => wp_create_nonce( 'ata_wcspc_security' ),
				'auto_show'           => $this->options['_wcspc_auto_show_ajax'],
				'manual_show'         => $this->options['_wcspc_manual_show'],
				'reload'              => $this->options['_wcspc_reload'],
				'hide_count_empty'    => $this->options['_wcspc_count_hide_empty'],
				'hide_count_checkout' => $this->options['_wcspc_count_hide_checkout'],
				'i18n'                => array(
					'error' => esc_html__( 'Something went wrong. Please try again.', 'atawc_lang' ),
				),
			)
		);
	}

	protected function verify_ajax() {
		$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ata_wcspc_security' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'atawc_lang' ) ), 403 );
		}
	}

	public function ajax_get_cart() {
		$this->verify_ajax();
		wp_send_json_success(
			array(
				'count' => WC()->cart->get_cart_contents_count(),
				'html'  => $this->get_cart_html(),
			)
		);
	}

	public function ajax_remove_item() {
		$this->verify_ajax();
		$key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		if ( empty( $key ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Missing cart item key.', 'atawc_lang' ) ), 400 );
		}
		WC()->cart->remove_cart_item( $key );
		wp_send_json_success(
			array(
				'count'    => WC()->cart->get_cart_contents_count(),
				'subtotal' => WC()->cart->get_cart_subtotal(),
				'html'     => $this->get_cart_html(),
			)
		);
	}

	public function ajax_update_qty() {
		$this->verify_ajax();
		$key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		$qty = isset( $_POST['cart_item_qty'] ) ? absint( $_POST['cart_item_qty'] ) : 0;
		if ( empty( $key ) || $qty < 1 ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid quantity or item.', 'atawc_lang' ) ), 400 );
		}
		WC()->cart->set_quantity( $key, $qty, true );
		wp_send_json_success(
			array(
				'count'    => WC()->cart->get_cart_contents_count(),
				'subtotal' => WC()->cart->get_cart_subtotal(),
				'html'     => $this->get_cart_html(),
			)
		);
	}

	/**
	 * Variable product AJAX add to cart.
	 */
	public function ajax_add_variation() {
		$this->verify_ajax();

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;

		$variations = array();
		if ( isset( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
			foreach ( wp_unslash( $_POST['variation'] ) as $attr_key => $attr_value ) {
				$attr_key = sanitize_title( $attr_key );
				if ( 0 !== strpos( $attr_key, 'attribute_' ) ) {
					$attr_key = 'attribute_' . $attr_key;
				}
				$variations[ $attr_key ] = sanitize_text_field( $attr_value );
			}
		}

		if ( ! $product_id || ! $variation_id ) {
			wp_send_json_error(
				array(
					'error'       => true,
					'message'     => esc_html__( 'Please select product options.', 'atawc_lang' ),
					'product_url' => $product_id ? get_permalink( $product_id ) : home_url( '/' ),
				),
				400
			);
		}

		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', $product_id );
		$passed     = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

		if ( $passed && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => $quantity ), true );
			}
			WC_AJAX::get_refreshed_fragments();
			return;
		}

		wp_send_json_error(
			array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
			)
		);
	}

	public function render_cart_shell() {
		$position = isset( $this->options['_wcspc_position'] ) ? $this->options['_wcspc_position'] : '01';
		?>
		<div id="wcspc-area" class="wcspc-area wcspc-effect-<?php echo esc_attr( $position ); ?>">
			<?php echo $this->get_cart_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
		if ( 'yes' === $this->options['_wcspc_count'] ) {
			$count      = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
			$hide_class = '';
			if ( 'yes' === $this->options['_wcspc_count_hide_empty'] && $count <= 0 ) {
				$hide_class = 'wcspc-count-hide';
			}
			if ( 'yes' === $this->options['_wcspc_count_hide_checkout'] && ( is_cart() || is_checkout() ) ) {
				$hide_class = 'wcspc-count-hide';
			}
			$pos  = isset( $this->options['_wcspc_count_position'] ) ? $this->options['_wcspc_count_position'] : 'bottom-left';
			$icon = isset( $this->options['_wcspc_count_icon'] ) ? $this->options['_wcspc_count_icon'] : 'wcspc-icon-cart1';
			?>
			<div id="wcspc-count" class="wcspc-count wcspc-count-<?php echo esc_attr( $pos ); ?><?php echo $hide_class ? ' ' . esc_attr( $hide_class ) : ''; ?>">
				<i class="<?php echo esc_attr( $icon ); ?>"></i>
				<span id="wcspc-count-number"><?php echo esc_html( $count ); ?></span>
			</div>
			<?php
		}
		?>
		<input type="hidden" id="wcspc-nonce" value="<?php echo esc_attr( wp_create_nonce( 'ata_wcspc_security' ) ); ?>" />
		<div class="wcspc-overlay"></div>
		<?php
		if ( 'yes' === $this->options['_wcspc_auto_show_normal']
			&& ( isset( $_POST['add-to-cart'] ) || isset( $_GET['add-to-cart'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			?>
			<script>
				jQuery(function () {
					setTimeout(function () {
						if (typeof wcspc_show_cart === 'function') { wcspc_show_cart(); }
					}, 800);
				});
			</script>
			<?php
		}
	}

	/**
	 * @return string
	 */
	public function get_cart_html() {
		$cart_html = '<div class="wcspc-close"><i class="wcspc-icon-close"></i></div>';

		if ( ! WC()->cart ) {
			return $cart_html . '<div class="wcspc-no-item">' . esc_html__( 'Have no product in the cart!', 'atawc_lang' ) . '</div>';
		}

		$items = WC()->cart->get_cart();
		do_action( 'ata_wcspc_free_before_cart_items' );

		if ( count( $items ) > 0 ) {
			$items = array_reverse( $items );
			$cart_html .= '<div class="wcspc-area-top wcspc-items">';

			foreach ( $items as $cart_item_key => $cart_item ) {
				$_product = $cart_item['data'];
				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
					continue;
				}

				$product_permalink = apply_filters(
					'woocommerce_cart_item_permalink',
					$_product->is_visible() ? $_product->get_permalink( $cart_item ) : '',
					$cart_item,
					$cart_item_key
				);
				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );

				$item_html  = '<div data-key="' . esc_attr( $cart_item_key ) . '" class="wcspc-item"><div class="wcspc-item-inner">';
				$item_html .= '<div class="wcspc-item-thumb">';
				$item_html .= $product_permalink ? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ) : $thumbnail;
				$item_html .= '</div><div class="wcspc-item-info">';
				$item_html .= '<span class="wcspc-item-title">';
				$item_html .= $product_permalink
					? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), esc_html( $name ) )
					: esc_html( $name );
				$item_html .= '</span>';

				// Always show variation attributes when present.
				if ( $_product->is_type( 'variation' ) && ! empty( $cart_item['variation'] ) ) {
					$item_html .= wc_get_formatted_cart_item_data( $cart_item );
				}

				$item_html .= '<span class="wcspc-item-price">' . wp_kses_post( $price ) . '</span></div>';

				$max_qty  = $_product->get_max_purchase_quantity();
				$max_attr = ( $max_qty > 0 ) ? ' max="' . esc_attr( $max_qty ) . '"' : '';

				$item_html .= '<div class="wcspc-item-qty"><div class="wcspc-item-qty-inner">';
				$item_html .= '<span class="wcspc-item-qty-plus">+</span>';
				$item_html .= '<input class="wcspc-item-qty-input" type="number" value="' . esc_attr( $cart_item['quantity'] ) . '" step="1" min="1"' . $max_attr . ' data-key="' . esc_attr( $cart_item_key ) . '"/>';
				$item_html .= '<span class="wcspc-item-qty-minus">-</span></div></div>';
				$item_html .= '<span class="wcspc-item-remove wcspc-icon-remove" data-key="' . esc_attr( $cart_item_key ) . '"></span>';
				$item_html .= '</div></div>';

				$cart_html .= apply_filters( 'ata_wcspc_free_cart_item_html', $item_html, $cart_item, $cart_item_key );
			}
			$cart_html .= '</div>';

			$cart_html .= '<div class="wcspc-area-bot">';
			if ( 'yes' === $this->options['_wcspc_total'] ) {
				$cart_html .= '<div class="wcspc-total"><div class="wcspc-total-inner"><div class="wcspc-total-left">' . esc_html__( 'Total', 'atawc_lang' ) . '</div><div id="wcspc-subtotal" class="wcspc-total-right">' . wp_kses_post( WC()->cart->get_cart_subtotal() ) . '</div></div></div>';
			}

			$buttons = isset( $this->options['_wcspc_buttons'] ) ? $this->options['_wcspc_buttons'] : '01';
			if ( '01' === $buttons ) {
				$cart_html .= '<div class="wcspc-action"><div class="wcspc-action-inner"><div class="wcspc-action-left"><a href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'Cart', 'atawc_lang' ) . '</a></div><div class="wcspc-action-right"><a href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Checkout', 'atawc_lang' ) . '</a></div></div></div>';
			} elseif ( '02' === $buttons ) {
				$cart_html .= '<div class="wcspc-action"><div class="wcspc-action-inner"><div class="wcspc-action-full"><a href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'Cart', 'atawc_lang' ) . '</a></div></div></div>';
			} else {
				$cart_html .= '<div class="wcspc-action"><div class="wcspc-action-inner"><div class="wcspc-action-full"><a href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Checkout', 'atawc_lang' ) . '</a></div></div></div>';
			}

			if ( 'yes' === $this->options['_wcspc_continue'] ) {
				$cart_html .= '<div class="wcspc-continue"><span id="wcspc-continue">' . esc_html__( 'Continue Shopping', 'atawc_lang' ) . '</span></div>';
			}
			$cart_html .= '</div>';
		} else {
			$cart_html .= '<div class="wcspc-no-item">' . esc_html__( 'Have no product in the cart!', 'atawc_lang' ) . '</div>';
		}

		do_action( 'ata_wcspc_free_after_cart_items' );
		return apply_filters( 'ata_wcspc_free_cart_html', $cart_html );
	}

	public function inline_css() {
		$o         = $this->options;
		$primary   = ! empty( $o['_wcspc_primary_colors'] ) ? $o['_wcspc_primary_colors'] : '#ffffff';
		$secondary = ! empty( $o['_wcspc_secondary_colors'] ) ? $o['_wcspc_secondary_colors'] : '#a7a7a7';
		$bg        = ! empty( $o['_wcspc_bg_colors'] ) ? $o['_wcspc_bg_colors'] : '#9b5c8f';
		?>
		<style id="ata-wcspc-free-dynamic-css">
			#wcspc-area.wcspc-area,
			#wcspc-count.wcspc-count {
				background: <?php echo esc_attr( $bg ); ?> !important;
			}
			#wcspc-count.wcspc-count,
			#wcspc-count.wcspc-count i,
			#wcspc-area .wcspc-close,
			#wcspc-area .wcspc-area-bot .wcspc-total .wcspc-total-inner,
			#wcspc-area .wcspc-area-bot .wcspc-continue span,
			#wcspc-area .wcspc-item-remove:before {
				color: <?php echo esc_attr( $primary ); ?> !important;
			}
			#wcspc-count.wcspc-count span,
			#wcspc-area .wcspc-area-bot .wcspc-action .wcspc-action-inner > div a {
				background: <?php echo esc_attr( $secondary ); ?> !important;
				color: <?php echo esc_attr( $primary ); ?> !important;
				border-color: <?php echo esc_attr( $secondary ); ?> !important;
			}
			#wcspc-area .wcspc-area-bot .wcspc-action .wcspc-action-inner > div a:hover,
			#wcspc-area .wcspc-close:hover,
			#wcspc-area .wcspc-continue span:hover {
				color: <?php echo esc_attr( $secondary ); ?> !important;
			}
		</style>
		<?php
	}
}
