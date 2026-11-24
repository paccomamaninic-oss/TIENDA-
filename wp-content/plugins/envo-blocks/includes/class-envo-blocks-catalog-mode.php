<?php
/**
 * Catalog mode: products without prices and without Add to cart – for everyone, or only for visitors who are not
 * signed in ("Log in to see prices") – with a button of your choice in place of Add to cart: a link (Contact us),
 * the login, or with Envo Blocks PRO "Request a quote".
 *
 * Settings: Envo Blocks › Catalog Mode (options envo_blocks_catalog_*); WooCommerce › Settings › Products › Catalog mode
 * only links there.
 * Works in classic and block themes: the products are not purchasable, the prices are replaced
 * (woocommerce_get_price_html) and the buttons of lists (woocommerce_loop_add_to_cart_link, the Product Button block)
 * and of the product page (the summary hook, the Add to Cart blocks) become the catalog button.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Catalog mode.
 */
class Envo_Blocks_Catalog_Mode {

	/**
	 * Section of WooCommerce › Settings › Products.
	 */
	const SECTION = 'envo-catalog';

	/**
	 * Envo Blocks › Catalog Mode.
	 */
	const PAGE = 'envo-blocks-catalog-mode';

	/**
	 * Products whose catalog button is already printed on the product page (no second one from a block).
	 *
	 * @var int[]
	 */
	private static $printed = array();

	/**
	 * While true the prices are not hidden (Envo Blocks PRO reads them for a quote request).
	 *
	 * @var bool
	 */
	private static $bypass = false;

	/**
	 * Turns the hiding of the prices off (true) or on again (false), e.g. while an order is made from a quote request.
	 *
	 * @param bool $bypass Bypass.
	 */
	public static function bypass( $bypass ) {
		self::$bypass = (bool) $bypass;
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_get_sections_products', array( __CLASS__, 'section' ) );
		add_filter( 'woocommerce_get_settings_products', array( __CLASS__, 'settings' ), 10, 2 );
		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'admin_post_envo_blocks_catalog', array( __CLASS__, 'save' ) );
		if ( 'off' === self::mode() || is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'purchasable' ), 50, 2 );
		add_filter( 'woocommerce_variation_is_purchasable', array( __CLASS__, 'purchasable' ), 50, 2 );
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'price' ), 50, 2 );
		// Hidden prices are hidden in the data too (the Store API of the blocks, feeds of other plugins).
		foreach ( array( 'product', 'product_variation' ) as $type ) {
			foreach ( array( 'price', 'regular_price', 'sale_price' ) as $key ) {
				add_filter( 'woocommerce_' . $type . '_get_' . $key, array( __CLASS__, 'raw_price' ), 50, 2 );
			}
		}
		foreach ( array( 'price', 'regular_price', 'sale_price' ) as $key ) {
			add_filter( 'woocommerce_variation_prices_' . $key, array( __CLASS__, 'raw_price' ), 50, 2 );
		}
		add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__, 'prices_hash' ), 50 );
		add_filter( 'woocommerce_structured_data_product', array( __CLASS__, 'structured_data' ), 50, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( __CLASS__, 'loop_button' ), 50, 2 );
		add_filter( 'render_block_woocommerce/product-button', array( __CLASS__, 'block_loop_button' ), 50, 3 );
		add_filter( 'woocommerce_blocks_product_grid_item_html', array( __CLASS__, 'grid_item' ), 50, 3 );
		add_action( 'woocommerce_before_single_product', array( __CLASS__, 'single_product' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_button' ), 30 );
		foreach ( array( 'woocommerce/add-to-cart-form', 'woocommerce/add-to-cart-with-options' ) as $name ) {
			add_filter( 'render_block_' . $name, array( __CLASS__, 'block_single_button' ), 50, 3 );
		}
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'styles' ) );
	}

	/**
	 * The mode: off, guests (visitors who are not signed in) or all.
	 *
	 * @return string
	 */
	public static function mode() {
		$mode = get_option( 'envo_blocks_catalog_mode', 'off' );

		return in_array( $mode, array( 'guests', 'all' ), true ) ? $mode : 'off';
	}

	/**
	 * A setting with its default.
	 *
	 * @param string $key Option without the prefix.
	 * @return mixed
	 */
	private static function option( $key ) {
		$defaults = array(
			'cats'   => array(),
			'prices' => 'hide',
			'button' => 'guests' === self::mode() ? 'login' : 'link',
			'label'  => '',
			'url'    => '',
			'text'   => '',
		);

		return get_option( 'envo_blocks_catalog_' . $key, $defaults[ $key ] );
	}

	/**
	 * Whether a product is shown as a catalog product for the visitor now.
	 *
	 * @param WC_Product|mixed $product Product or variation.
	 * @return bool
	 */
	public static function applies( $product ) {
		if ( ! $product instanceof WC_Product || 'off' === self::mode() || ( 'guests' === self::mode() && is_user_logged_in() ) ) {
			return false;
		}
		$cats = array_filter( array_map( 'absint', (array) self::option( 'cats' ) ) );
		if ( ! $cats ) {
			return true;
		}
		$id  = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
		// Both in the default language: the categories are saved in one language, the product may be a translation.
		$own  = Envo_Blocks_Multilang::source_ids( wc_get_product_cat_ids( $id ), 'product_cat' );
		$cats = Envo_Blocks_Multilang::source_ids( $cats, 'product_cat' );
		foreach ( $cats as $cat ) {
			if ( in_array( $cat, $own, true ) || array_intersect( $own, get_term_children( $cat, 'product_cat' ) ) ) {
				return true;
			}
		}

		/**
		 * Filters whether a product is a catalog product (no price, no Add to cart).
		 *
		 * @param bool       $applies Whether it is.
		 * @param WC_Product $product Product.
		 */
		return (bool) apply_filters( 'envo_blocks_catalog_applies', false, $product );
	}

	/**
	 * Catalog products cannot be bought (also not through the Store API).
	 *
	 * @param bool       $purchasable Purchasable.
	 * @param WC_Product $product     Product.
	 * @return bool
	 */
	public static function purchasable( $purchasable, $product ) {
		return self::applies( $product ) ? false : $purchasable;
	}

	/**
	 * The text instead of the price ("Log in to see prices"), or the price.
	 *
	 * @param string     $html    Price HTML.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function price( $html, $product ) {
		if ( 'hide' !== self::option( 'prices' ) || ! self::applies( $product ) ) {
			return $html;
		}
		$text = trim( (string) self::option( 'text' ) );
		if ( '' === $text && 'guests' === self::mode() ) {
			$text = __( 'Log in to see prices', 'envo-blocks' );
		}

		return '' !== $text ? '<span class="envo-catalog-price">' . esc_html( $text ) . '</span>' : '';
	}

	/**
	 * No price in the data of a catalog product whose prices are hidden.
	 *
	 * @param mixed      $price   Price.
	 * @param WC_Product $product Product or variation.
	 * @return mixed
	 */
	public static function raw_price( $price, $product ) {
		return ! self::$bypass && 'hide' === self::option( 'prices' ) && self::applies( $product ) ? '' : $price;
	}

	/**
	 * Variation prices are cached per visitor group: signed in or not (guests may see no prices).
	 *
	 * @param array $hash Parts of the cache key.
	 * @return array
	 */
	public static function prices_hash( $hash ) {
		$hash[] = 'envo-catalog-' . ( is_user_logged_in() ? 'in' : 'out' ) . '-' . self::mode();

		return $hash;
	}

	/**
	 * No offer with a price in the structured data of a product without a price.
	 *
	 * @param array      $data    Structured data.
	 * @param WC_Product $product Product.
	 * @return array
	 */
	public static function structured_data( $data, $product ) {
		if ( 'hide' === self::option( 'prices' ) && self::applies( $product ) ) {
			unset( $data['offers'] );
		}

		return $data;
	}

	/**
	 * The catalog button of a product ('' = none).
	 *
	 * @param WC_Product $product Product.
	 * @param string     $where   loop or single.
	 * @return string
	 */
	public static function button( $product, $where = 'loop' ) {
		$type  = (string) self::option( 'button' );
		$label = trim( (string) self::option( 'label' ) );
		$html  = '';
		if ( 'link' === $type ) {
			$url  = trim( (string) self::option( 'url' ) );
			$url  = '' !== $url ? $url : get_permalink( $product->get_id() );
			$html = '<a class="envo-catalog-button wp-element-button button" href="' . esc_url( $url ) . '">' . esc_html( '' !== $label ? $label : __( 'Contact us', 'envo-blocks' ) ) . '</a>';
		} elseif ( 'login' === $type && ! is_user_logged_in() ) {
			$account = wc_get_page_permalink( 'myaccount' );
			$url     = $account ? add_query_arg( 'redirect_to', rawurlencode( get_permalink( $product->get_id() ) ), $account ) : wp_login_url( get_permalink( $product->get_id() ) );
			$html    = '<a class="envo-catalog-button wp-element-button button" href="' . esc_url( $url ) . '">' . esc_html( '' !== $label ? $label : __( 'Log in to buy', 'envo-blocks' ) ) . '</a>';
		}

		/**
		 * Filters the button of a catalog product (Envo Blocks PRO adds "Request a quote").
		 *
		 * @param string     $html    Button HTML.
		 * @param WC_Product $product Product.
		 * @param string     $type    Chosen button: none, link, login, quote.
		 * @param string     $where   loop or single.
		 */
		return (string) apply_filters( 'envo_blocks_catalog_button', $html, $product, $type, $where );
	}

	/**
	 * Lists of the classic templates: the catalog button instead of Add to cart.
	 *
	 * @param string     $link    Button HTML.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function loop_button( $link, $product ) {
		return self::applies( $product ) ? self::button( $product ) : $link;
	}

	/**
	 * The Product Button block of product lists.
	 *
	 * @param string   $html       Block HTML.
	 * @param array    $block_data Block.
	 * @param WP_Block $block      Block instance.
	 * @return string
	 */
	public static function block_loop_button( $html, $block_data, $block ) {
		$product = self::block_product( $block );
		if ( ! $product || ! self::applies( $product ) ) {
			return $html;
		}
		$button = self::button( $product );

		return '' !== $button ? '<div class="wp-block-button wc-block-components-product-button envo-catalog-wrap">' . $button . '</div>' : '';
	}

	/**
	 * The older product grid blocks (Products by Category, Hand-picked Products …): the catalog button, and no price in
	 * the data attributes of their button.
	 *
	 * @param string     $html    Item HTML.
	 * @param object     $data    Parts of the item.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function grid_item( $html, $data, $product ) {
		if ( ! self::applies( $product ) || empty( $data->button ) ) {
			return $html;
		}
		$button = self::button( $product );

		return str_replace( $data->button, '' !== $button ? '<div class="wp-block-button wc-block-grid__product-add-to-cart envo-catalog-wrap">' . $button . '</div>' : '', $html );
	}

	/**
	 * Classic product page: no Add to cart form for a catalog product (a variable product would say "unavailable").
	 */
	public static function single_product() {
		global $product;
		if ( $product instanceof WC_Product && self::applies( $product ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		}
	}

	/**
	 * Classic product page: the catalog button in place of the form.
	 */
	public static function single_button() {
		global $product;
		if ( $product instanceof WC_Product && self::applies( $product ) && ! in_array( $product->get_id(), self::$printed, true ) ) {
			self::$printed[] = $product->get_id();
			echo '<div class="envo-catalog-single">' . self::button( $product, 'single' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped while built.
		}
	}

	/**
	 * Add to Cart blocks of the product page: the catalog button in place of the form.
	 *
	 * @param string   $html       Block HTML.
	 * @param array    $block_data Block.
	 * @param WP_Block $block      Block instance.
	 * @return string
	 */
	public static function block_single_button( $html, $block_data, $block ) {
		$product = self::block_product( $block );
		if ( ! $product || ! self::applies( $product ) ) {
			return $html;
		}
		if ( in_array( $product->get_id(), self::$printed, true ) ) {
			return '';
		}
		self::$printed[] = $product->get_id();

		return '<div class="envo-catalog-single">' . self::button( $product, 'single' ) . '</div>';
	}

	/**
	 * The product of a block: its postId context, else the global product.
	 *
	 * @param WP_Block|null $block Block instance.
	 * @return WC_Product|null
	 */
	private static function block_product( $block ) {
		if ( $block instanceof WP_Block && ! empty( $block->context['postId'] ) && 'product' === get_post_type( (int) $block->context['postId'] ) ) {
			return wc_get_product( (int) $block->context['postId'] );
		}
		global $product;

		return $product instanceof WC_Product ? $product : null;
	}

	/**
	 * A few styles of the price text and the buttons.
	 */
	public static function styles() {
		wp_register_style( 'envo-blocks-catalog', false, array(), ENVO_BLOCKS_VERSION );
		wp_enqueue_style( 'envo-blocks-catalog' );
		wp_add_inline_style( 'envo-blocks-catalog', '.envo-catalog-price{font-size:.9em;opacity:.8}.envo-catalog-single{margin:1em 0}.envo-catalog-button{display:inline-block;text-decoration:none}' );
	}

	/**
	 * The section in WooCommerce › Settings › Products (it only points to Envo Blocks › Catalog Mode).
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public static function section( $sections ) {
		$sections[ self::SECTION ] = __( 'Catalog mode', 'envo-blocks' );

		return $sections;
	}

	/**
	 * The WooCommerce section: a link to the settings in Envo Blocks.
	 *
	 * @param array  $settings Settings of the current section.
	 * @param string $section  Current section.
	 * @return array
	 */
	public static function settings( $settings, $section ) {
		if ( self::SECTION !== $section ) {
			return $settings;
		}

		return array(
			array(
				'title' => __( 'Catalog mode', 'envo-blocks' ),
				'type'  => 'title',
				'desc'  => sprintf(
					/* translators: %s: link to Envo Blocks > Catalog Mode. */
					__( 'Products without prices and without Add to cart are set in %s.', 'envo-blocks' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Envo Blocks › Catalog Mode', 'envo-blocks' ) . '</a>'
				),
				'id'    => 'envo_blocks_catalog',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'envo_blocks_catalog',
			),
		);
	}

	/**
	 * Envo Blocks › Catalog Mode.
	 *
	 * @param array $pages Pages of the Envo Blocks menu.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Catalog Mode', 'envo-blocks' ),
			'title'      => __( 'Catalog Mode', 'envo-blocks' ),
			'callback'   => array( __CLASS__, 'render' ),
			'capability' => 'manage_woocommerce',
		);

		return $pages;
	}

	/**
	 * Styles of the page (the cards of the Checkout Fields page) and the category picker of WooCommerce.
	 *
	 * @param string $hook Admin page.
	 */
	public static function admin_assets( $hook ) {
		if ( false === strpos( (string) $hook, self::PAGE ) ) {
			return;
		}
		wp_enqueue_style( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/css/checkout-fields.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/checkout-fields.css' ) );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_script( 'wc-enhanced-select' );
	}

	/**
	 * The buttons a catalog product can have instead of Add to cart.
	 *
	 * @return array Key => label.
	 */
	public static function buttons() {
		$buttons = array(
			'none'  => __( 'No button', 'envo-blocks' ),
			'link'  => __( 'A link (e.g. Contact us)', 'envo-blocks' ),
			'login' => __( 'Log in (for visitors who are not signed in)', 'envo-blocks' ),
		);

		/**
		 * Filters the buttons a catalog product can have instead of Add to cart.
		 *
		 * @param array $buttons Key => label.
		 */
		return (array) apply_filters( 'envo_blocks_catalog_buttons', $buttons );
	}

	/**
	 * The page: the settings of the catalog mode.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show after a redirect.
		$saved  = isset( $_GET['eb-cm'] ) && 'saved' === sanitize_key( wp_unslash( $_GET['eb-cm'] ) );
		$mode   = self::mode();
		$chosen = array_map( 'absint', (array) self::option( 'cats' ) );
		$terms  = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 500,
			)
		);
		?>
		<div class="eb-cf eb-cm">
			<?php if ( $saved ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The settings were saved.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Catalog mode', 'envo-blocks' ); ?></h2>
				<p class="eb-cf__intro"><?php esc_html_e( 'Show products without prices and without Add to cart – for everyone, or only for visitors who are not signed in (wholesale, B2B, "ask for a price"). A button of your choice takes the place of Add to cart.', 'envo-blocks' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="envo_blocks_catalog">
					<?php wp_nonce_field( 'envo_blocks_catalog' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="eb-cm-mode"><?php esc_html_e( 'Catalog mode', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-cm-mode" name="mode">
									<option value="off" <?php selected( $mode, 'off' ); ?>><?php esc_html_e( 'Off', 'envo-blocks' ); ?></option>
									<option value="guests" <?php selected( $mode, 'guests' ); ?>><?php esc_html_e( 'For visitors who are not signed in', 'envo-blocks' ); ?></option>
									<option value="all" <?php selected( $mode, 'all' ); ?>><?php esc_html_e( 'For everyone', 'envo-blocks' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-cm-cats"><?php esc_html_e( 'Only these categories', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-cm-cats" name="cats[]" multiple class="wc-enhanced-select" style="width:100%;max-width:420px" data-placeholder="<?php esc_attr_e( 'All products', 'envo-blocks' ); ?>">
									<?php foreach ( is_array( $terms ) ? $terms : array() as $term ) : ?>
										<option value="<?php echo (int) $term->term_id; ?>" <?php selected( in_array( (int) $term->term_id, $chosen, true ) ); ?>><?php echo esc_html( $term->name ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Empty = all products. Subcategories count.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-cm-prices"><?php esc_html_e( 'Prices', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-cm-prices" name="prices">
									<option value="hide" <?php selected( self::option( 'prices' ), 'hide' ); ?>><?php esc_html_e( 'Hide the prices', 'envo-blocks' ); ?></option>
									<option value="show" <?php selected( self::option( 'prices' ), 'show' ); ?>><?php esc_html_e( 'Show the prices (only no Add to cart)', 'envo-blocks' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-cm-text"><?php esc_html_e( 'Text instead of the price', 'envo-blocks' ); ?></label></th>
							<td>
								<input type="text" id="eb-cm-text" class="regular-text" name="text" value="<?php echo esc_attr( (string) self::option( 'text' ) ); ?>" placeholder="<?php esc_attr_e( 'Log in to see prices', 'envo-blocks' ); ?>">
								<p class="description"><?php esc_html_e( 'Empty: "Log in to see prices" for visitors who are not signed in, nothing for everyone.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-cm-button"><?php esc_html_e( 'Button instead of Add to cart', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-cm-button" name="button">
									<?php foreach ( self::buttons() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( self::option( 'button' ), $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<?php if ( ! isset( self::buttons()['quote'] ) ) : ?>
									<p class="description"><?php esc_html_e( '"Request a quote" with a quote list comes with Envo Blocks PRO.', 'envo-blocks' ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-cm-label"><?php esc_html_e( 'Button text', 'envo-blocks' ); ?></label></th>
							<td><input type="text" id="eb-cm-label" class="regular-text" name="label" value="<?php echo esc_attr( (string) self::option( 'label' ) ); ?>" placeholder="<?php esc_attr_e( 'Contact us', 'envo-blocks' ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-cm-url"><?php esc_html_e( 'Link of the button', 'envo-blocks' ); ?></label></th>
							<td>
								<input type="url" id="eb-cm-url" class="regular-text" name="url" value="<?php echo esc_attr( (string) self::option( 'url' ) ); ?>" placeholder="https://">
								<p class="description"><?php esc_html_e( 'For the link button, e.g. your contact page. Empty = the product page.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<?php
						/**
						 * More rows of the catalog mode settings (Envo Blocks PRO: the quote page).
						 */
						do_action( 'envo_blocks_catalog_settings_rows' );
						?>
					</table>
					<?php submit_button( __( 'Save changes', 'envo-blocks' ) ); ?>
				</form>
			</section>
			<?php
			/**
			 * More cards of the Catalog Mode page (Envo Blocks PRO: the quote requests).
			 */
			do_action( 'envo_blocks_catalog_page_cards' );
			?>
		</div>
		<?php
	}

	/**
	 * Saves the settings (admin-post.php).
	 */
	public static function save() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_catalog' );
		$pick = static function ( $key, $allowed, $default ) {
			$value = isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : $default; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked above.
			return in_array( $value, $allowed, true ) ? $value : $default;
		};
		update_option( 'envo_blocks_catalog_mode', $pick( 'mode', array( 'off', 'guests', 'all' ), 'off' ) );
		update_option( 'envo_blocks_catalog_cats', array_map( 'strval', array_filter( array_map( 'absint', isset( $_POST['cats'] ) ? (array) wp_unslash( $_POST['cats'] ) : array() ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- absint.
		update_option( 'envo_blocks_catalog_prices', $pick( 'prices', array( 'hide', 'show' ), 'hide' ) );
		update_option( 'envo_blocks_catalog_button', $pick( 'button', array_keys( self::buttons() ), 'link' ) );
		update_option( 'envo_blocks_catalog_text', isset( $_POST['text'] ) ? sanitize_text_field( wp_unslash( $_POST['text'] ) ) : '' );
		update_option( 'envo_blocks_catalog_label', isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '' );
		update_option( 'envo_blocks_catalog_url', isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '' );

		/**
		 * After the catalog mode settings were saved (the nonce is checked); Envo Blocks PRO saves its rows here.
		 */
		do_action( 'envo_blocks_catalog_save' );

		wp_safe_redirect( add_query_arg( 'eb-cm', 'saved', admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}
}
