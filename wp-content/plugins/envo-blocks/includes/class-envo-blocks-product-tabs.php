<?php
/**
 * Product Tabs: the block that replaces the WooCommerce tabs of the product page (Description, Additional information,
 * Reviews), and the product's own tabs.
 *
 * - The Product Tabs block holds Product Tab blocks, one per tab, in any order. A tab shows a part of WooCommerce
 *   (description, additional information, reviews), own content made of blocks, the product's own tabs, or the tabs
 *   other plugins add with the woocommerce_product_tabs filter.
 * - Product data > Tabs (classic product editor): tabs of one product (title + text). They also show in the
 *   WooCommerce tabs of classic themes and of the Product Details block.
 * - The Product Tab blocks render before their parent: each one leaves its data on a stack and the parent takes them
 *   (see collect() and take()), so the custom content is rendered once.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product tabs.
 */
final class Envo_Blocks_Product_Tabs {

	const META = '_envo_blocks_tabs';
	const MAX  = 10;

	/** Prefix of the product's own tabs in the woocommerce_product_tabs list. */
	const KEY_PREFIX = 'envo_blocks_tab_';

	/** Tabs of WooCommerce itself. */
	const CORE = array( 'description', 'additional_information', 'reviews' );

	/** Globals that build() changes for the WooCommerce callbacks (the post, the product and what setup_postdata() sets). */
	const GLOBALS = array( 'post', 'product', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' );

	/**
	 * Data of the rendered Product Tab blocks, waiting for their parent.
	 *
	 * @var array
	 */
	private static $stack = array();

	/**
	 * Description being rendered (the block inside the product description would render it again and again).
	 *
	 * @var bool
	 */
	private static $in_description = false;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'add_product_tabs' ), 98 );
		if ( is_admin() ) {
			add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'tab' ) );
			add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
			add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Data                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * The product's own tabs (cleaned).
	 *
	 * @param int $product_id Product ID.
	 * @return array List of array( title, content ).
	 */
	public static function own( $product_id ) {
		$raw = get_post_meta( $product_id, self::META, true );
		return self::sanitize( is_array( $raw ) ? $raw : array() );
	}

	/**
	 * Cleans a list of tabs (from the meta or from the form).
	 *
	 * @param array $list Raw list.
	 * @return array
	 */
	private static function sanitize( array $list ) {
		$out = array();
		foreach ( array_values( $list ) as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$title   = isset( $item['title'] ) ? sanitize_text_field( (string) $item['title'] ) : '';
			$content = isset( $item['content'] ) ? wp_kses_post( (string) $item['content'] ) : '';
			if ( '' === trim( $title ) || '' === trim( $content ) ) {
				continue;
			}
			$out[] = array(
				'title'   => $title,
				'content' => $content,
			);
			if ( count( $out ) >= self::MAX ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * HTML of a text of a tab (paragraphs and shortcodes like in a post).
	 *
	 * @param string $content Saved text (already cleaned by wp_kses_post()).
	 * @return string
	 */
	public static function format( $content ) {
		return do_shortcode( shortcode_unautop( wpautop( wptexturize( $content ) ) ) );
	}

	/**
	 * The product's own tabs in the WooCommerce tabs (classic themes, the Product Details block, and the "Tabs of
	 * other plugins" of the Product Tabs block skips them).
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function add_product_tabs( $tabs ) {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return $tabs;
		}
		foreach ( self::own( $product->get_id() ) as $i => $tab ) {
			$tabs[ self::KEY_PREFIX . $i ] = array(
				'title'    => $tab['title'],
				'priority' => 40 + $i,
				'callback' => static function () use ( $tab ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Saved through wp_kses_post(), then paragraphs and shortcodes.
					echo self::format( $tab['content'] );
				},
			);
		}
		return $tabs;
	}

	/* ------------------------------------------------------------------ */
	/* Blocks                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * A Product Tab block was rendered: its data waits for the parent.
	 *
	 * @param array  $attributes Attributes of the Product Tab block.
	 * @param string $content    Its rendered inner blocks (own content).
	 */
	public static function collect( array $attributes, $content ) {
		self::$stack[] = array(
			'attributes' => $attributes,
			'content'    => (string) $content,
		);
	}

	/**
	 * Data of the last $count Product Tab blocks (the children of the parent that renders now).
	 *
	 * @param int $count Number of children.
	 * @return array
	 */
	public static function take( $count ) {
		if ( $count <= 0 ) {
			return array();
		}
		return array_splice( self::$stack, -min( $count, count( self::$stack ) ) );
	}

	/**
	 * Default title of a tab source.
	 *
	 * @param string $source Source.
	 * @return string
	 */
	public static function source_title( $source ) {
		switch ( $source ) {
			case 'description':
				return __( 'Description', 'envo-blocks' );
			case 'additional_information':
				return __( 'Additional information', 'envo-blocks' );
			case 'reviews':
				return __( 'Reviews ({count})', 'envo-blocks' );
		}
		return __( 'Tab', 'envo-blocks' );
	}

	/**
	 * Tabs of a product for the Product Tabs block.
	 *
	 * @param WC_Product $product  Product.
	 * @param array      $children Data of the Product Tab blocks (see collect()).
	 * @param array      $options  headings (bool) – the headings WooCommerce prints in the description and additional
	 *                             information.
	 * @return array List of array( key, title, icon, html ).
	 */
	public static function build( $product, array $children, array $options ) {
		$product_id = $product->get_id();

		// WooCommerce callbacks work with the global post and product; setup_postdata() also sets the page globals.
		// All of them get their old values back below (also when they did not exist).
		$saved = array();
		foreach ( self::GLOBALS as $name ) {
			$saved[ $name ] = array_key_exists( $name, $GLOBALS ) ? array( $GLOBALS[ $name ] ) : null;
		}
		$GLOBALS['post'] = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Restored below.
		setup_postdata( $GLOBALS['post'] );
		$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Restored below.

		$wc_tabs = apply_filters( 'woocommerce_product_tabs', array() );
		$wc_tabs = is_array( $wc_tabs ) ? $wc_tabs : array();

		if ( empty( $options['headings'] ) ) {
			add_filter( 'woocommerce_product_description_heading', '__return_empty_string', 999 );
			add_filter( 'woocommerce_product_additional_information_heading', '__return_empty_string', 999 );
		}

		$terms = wc_get_product_term_ids( $product_id, 'product_cat' );
		$out   = array();

		foreach ( $children as $n => $child ) {
			$a      = $child['attributes'];
			$source = isset( $a['source'] ) && in_array( $a['source'], array( 'description', 'additional_information', 'reviews', 'custom', 'product', 'others' ), true ) ? $a['source'] : 'custom';
			$title  = isset( $a['title'] ) && is_string( $a['title'] ) ? trim( wp_strip_all_tags( $a['title'] ) ) : '';
			$icon   = isset( $a['icon'] ) && is_string( $a['icon'] ) ? $a['icon'] : '';

			// Only for products of some categories.
			$cats = isset( $a['categories'] ) && is_array( $a['categories'] ) ? array_map( 'absint', $a['categories'] ) : array();
			if ( $cats && ! Envo_Blocks_Multilang::intersects( $cats, $terms, 'product_cat' ) ) {
				continue;
			}

			if ( in_array( $source, self::CORE, true ) ) {
				if ( empty( $wc_tabs[ $source ]['callback'] ) || ( 'description' === $source && self::$in_description ) ) {
					continue; // WooCommerce has no such tab for this product (no text, no attributes, reviews off).
				}
				if ( 'reviews' === $source ) {
					wp_enqueue_script( 'wc-single-product' ); // The stars of the review form, also outside product pages.
				}
				$title = '' !== $title ? $title : self::source_title( $source );
				$out[] = array(
					'key'   => $source,
					'title' => self::count_title( $title, $product ),
					'icon'  => $icon,
					'html'  => self::callback_html( $source, $wc_tabs[ $source ] ),
				);
			} elseif ( 'custom' === $source ) {
				if ( '' === trim( $child['content'] ) ) {
					continue;
				}
				$out[] = array(
					'key'   => 'custom-' . ( $n + 1 ),
					'title' => '' !== $title ? $title : self::source_title( 'custom' ),
					'icon'  => $icon,
					'html'  => $child['content'],
				);
			} elseif ( 'product' === $source ) {
				foreach ( self::own( $product_id ) as $i => $tab ) {
					$out[] = array(
						'key'   => 'product-' . ( $i + 1 ),
						'title' => $tab['title'],
						'icon'  => $icon,
						'html'  => self::format( $tab['content'] ),
					);
				}
			} else {
				foreach ( $wc_tabs as $key => $tab ) {
					if ( in_array( $key, self::CORE, true ) || 0 === strpos( (string) $key, self::KEY_PREFIX ) || empty( $tab['callback'] ) ) {
						continue;
					}
					$out[] = array(
						'key'   => sanitize_key( $key ),
						'title' => isset( $tab['title'] ) ? wp_strip_all_tags( (string) $tab['title'] ) : '',
						'icon'  => $icon,
						'html'  => self::callback_html( $key, $tab ),
					);
				}
			}
		}

		remove_filter( 'woocommerce_product_description_heading', '__return_empty_string', 999 );
		remove_filter( 'woocommerce_product_additional_information_heading', '__return_empty_string', 999 );

		foreach ( $saved as $name => $value ) {
			if ( null === $value ) {
				unset( $GLOBALS[ $name ] );
			} else {
				$GLOBALS[ $name ] = $value[0]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- The original value.
			}
		}

		return array_values(
			array_filter(
				$out,
				static function ( $tab ) {
					return '' !== $tab['title'] && '' !== trim( $tab['html'] );
				}
			)
		);
	}

	/**
	 * Output of a WooCommerce tab callback.
	 *
	 * @param string $key Tab key.
	 * @param array  $tab Tab (title, callback).
	 * @return string
	 */
	private static function callback_html( $key, array $tab ) {
		if ( ! is_callable( $tab['callback'] ) ) {
			return '';
		}
		$description = 'description' === $key;
		if ( $description ) {
			self::$in_description = true;
		}
		ob_start();
		call_user_func( $tab['callback'], $key, $tab );
		$html = (string) ob_get_clean();
		if ( $description ) {
			self::$in_description = false;
		}
		return $html;
	}

	/**
	 * Title with {count} = the number of reviews.
	 *
	 * @param string     $title   Title.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private static function count_title( $title, $product ) {
		if ( false === strpos( $title, '{count}' ) ) {
			return $title;
		}
		return str_replace( '{count}', number_format_i18n( (int) $product->get_review_count() ), $title );
	}

	/* ------------------------------------------------------------------ */
	/* Product editor                                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Product data > Tabs.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function tab( $tabs ) {
		$tabs['envo_blocks_tabs'] = array(
			'label'    => __( 'Tabs', 'envo-blocks' ),
			'target'   => 'envo_blocks_tabs_data',
			'class'    => array(),
			'priority' => 76,
		);
		return $tabs;
	}

	/**
	 * The Tabs panel.
	 */
	public static function panel() {
		global $post;
		$tabs = $post ? self::own( $post->ID ) : array();
		?>
		<div id="envo_blocks_tabs_data" class="panel woocommerce_options_panel hidden eb-pt-admin">
			<div class="options_group">
				<p class="eb-pt-admin__intro"><?php esc_html_e( 'Tabs of this product only, e.g. care instructions, ingredients or a warranty. They show after the Reviews tab of WooCommerce, or where the "The product\'s own tabs" tab of the Product Tabs block is. Shortcodes work in the text.', 'envo-blocks' ); ?></p>
				<?php wp_nonce_field( 'envo_blocks_tabs', 'envo_blocks_tabs_nonce' ); ?>
				<div class="eb-pt-admin__rows" data-max="<?php echo esc_attr( (string) self::MAX ); ?>">
					<?php
					foreach ( $tabs as $i => $tab ) {
						self::row( (string) $i, $tab );
					}
					?>
				</div>
				<p class="eb-pt-admin__add-wrap"><button type="button" class="button eb-pt-admin__add"><?php esc_html_e( 'Add tab', 'envo-blocks' ); ?></button></p>
				<template class="eb-pt-admin__template">
					<?php
					self::row(
						'__i__',
						array(
							'title'   => '',
							'content' => '',
						)
					);
					?>
				</template>
			</div>
		</div>
		<?php
	}

	/**
	 * One tab row of the panel.
	 *
	 * @param string $i   Row index.
	 * @param array  $tab Tab.
	 */
	private static function row( $i, array $tab ) {
		$name = 'envo_blocks_tabs[' . $i . ']';
		?>
		<div class="eb-pt-admin__row">
			<p class="form-field">
				<label><?php esc_html_e( 'Title', 'envo-blocks' ); ?></label>
				<input type="text" class="eb-pt-admin__title" name="<?php echo esc_attr( $name . '[title]' ); ?>" value="<?php echo esc_attr( $tab['title'] ); ?>">
			</p>
			<div class="eb-pt-admin__content">
				<textarea class="eb-pt-admin__text" id="<?php echo esc_attr( 'envo_blocks_tab_' . $i ); ?>" name="<?php echo esc_attr( $name . '[content]' ); ?>" rows="8"><?php echo esc_textarea( $tab['content'] ); ?></textarea>
			</div>
			<p class="eb-pt-admin__tools">
				<button type="button" class="button eb-pt-admin__up" aria-label="<?php esc_attr_e( 'Move up', 'envo-blocks' ); ?>">↑</button>
				<button type="button" class="button eb-pt-admin__down" aria-label="<?php esc_attr_e( 'Move down', 'envo-blocks' ); ?>">↓</button>
				<button type="button" class="button-link button-link-delete eb-pt-admin__remove"><?php esc_html_e( 'Remove tab', 'envo-blocks' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Saves the tabs with the product.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function save( $product_id ) {
		if ( ! isset( $_POST['envo_blocks_tabs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envo_blocks_tabs_nonce'] ) ), 'envo_blocks_tabs' ) || ! current_user_can( 'edit_product', $product_id ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cleaned in sanitize().
		$list = isset( $_POST['envo_blocks_tabs'] ) && is_array( $_POST['envo_blocks_tabs'] ) ? wp_unslash( $_POST['envo_blocks_tabs'] ) : array();
		$list = self::sanitize( $list );
		if ( $list ) {
			update_post_meta( $product_id, self::META, $list );
		} else {
			delete_post_meta( $product_id, self::META );
		}
	}

	/**
	 * The editor and the panel script on the product screen.
	 *
	 * @param string $hook Admin page.
	 */
	public static function admin_assets( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'product' !== $screen->id ) {
			return;
		}
		wp_enqueue_editor();
		wp_enqueue_style( 'envo-blocks-product-tabs-admin', ENVO_BLOCKS_URL . 'assets/css/product-tabs-admin.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/product-tabs-admin.css' ) );
		wp_enqueue_script( 'envo-blocks-product-tabs-admin', ENVO_BLOCKS_URL . 'assets/js/product-tabs-admin.js', array( 'jquery', 'editor' ), Envo_Blocks_Blocks::ver( 'assets/js/product-tabs-admin.js' ), true );
	}
}
