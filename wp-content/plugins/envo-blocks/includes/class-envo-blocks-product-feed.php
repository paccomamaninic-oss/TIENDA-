<?php
/**
 * Product feed: the products of the shop as an XML feed for Google Merchant Center (Google Shopping, free listings)
 * and for the Meta catalog (Facebook and Instagram shops), which reads the same format.
 *
 * - Format: RSS 2.0 with the g: fields of Google (id, title, description, link, image_link, availability, price,
 *   sale_price, brand, gtin, mpn, condition, item_group_id, color, size, product_type …).
 * - The feed is a file in uploads/envo-blocks-feeds/, built in pages of 100 products and swapped in when done. It is
 *   built again by WP-Cron once an hour when products changed, and at least once a day; the URL
 *   ?envo_blocks_feed=products serves the file (and builds it the first time).
 * - Left out: hidden and password-protected products, products without a price or an image, products whose prices
 *   the catalog mode hides, and products marked "Hide from product feeds" (Product data › Advanced).
 * - Envo Blocks › Settings › Product Feed: on/off, what goes in, brand and attribute mapping, the URL, the status.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product feed.
 */
final class Envo_Blocks_Product_Feed {

	const OPTION  = 'envo_blocks_feed';
	const STATUS  = 'envo_blocks_feed_status';
	const DIRTY   = 'envo_blocks_feed_dirty';
	const PAGE    = 'envo-blocks-feed';
	const EXCLUDE = '_envo_blocks_feed_exclude';
	const CRON    = 'envo_blocks_feed_build';
	const QUERY   = 'envo_blocks_feed';

	/**
	 * IDs already in the feed being built (two products with the same SKU get their own IDs).
	 *
	 * @var array
	 */
	private static $ids = array();

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'serve' ), 0 );
		add_action( self::CRON, array( __CLASS__, 'cron' ) );
		add_action( 'init', array( __CLASS__, 'schedule' ) );
		add_action( 'woocommerce_update_product', array( __CLASS__, 'changed' ) );
		add_action( 'woocommerce_new_product', array( __CLASS__, 'changed' ) );
		add_action( 'woocommerce_product_set_stock_status', array( __CLASS__, 'changed' ) );
		add_action( 'woocommerce_variation_set_stock_status', array( __CLASS__, 'changed' ) );
		add_action( 'trashed_post', array( __CLASS__, 'changed' ) );
		add_action( 'woocommerce_product_options_advanced', array( __CLASS__, 'product_field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'product_save' ) );
		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'admin_post_envo_blocks_feed', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_envo_blocks_feed_build', array( __CLASS__, 'build_now' ) );
	}

	/**
	 * Settings with defaults.
	 *
	 * @return array
	 */
	public static function settings() {
		$saved = get_option( self::OPTION, array() );
		$out   = wp_parse_args(
			is_array( $saved ) ? $saved : array(),
			array(
				'enabled'    => false,
				'cats'       => array(),
				'stock'      => true,
				'variations' => true,
				'brand'      => taxonomy_exists( 'product_brand' ) ? 'taxonomy' : 'text',
				'brand_text' => get_bloginfo( 'name' ),
				'condition'  => 'new',
				'color'      => taxonomy_exists( 'pa_color' ) ? 'pa_color' : '',
				'size'       => taxonomy_exists( 'pa_size' ) ? 'pa_size' : '',
				'category'   => '',
			)
		);
		$out['cats'] = array_values( array_filter( array_map( 'absint', (array) $out['cats'] ) ) );
		return $out;
	}

	/**
	 * Public URL of the feed.
	 *
	 * @return string
	 */
	public static function url() {
		return add_query_arg( self::QUERY, 'products', home_url( '/' ) );
	}

	/**
	 * Path of the feed file (the name has a part from the salt, so the folder cannot be listed by guessing).
	 *
	 * @return string
	 */
	private static function file() {
		$dir = wp_upload_dir( null, false );
		return trailingslashit( $dir['basedir'] ) . 'envo-blocks-feeds/products-' . substr( wp_hash( 'envo-blocks-feed' ), 0, 12 ) . '.xml';
	}

	/* ------------------------------------------------------------------ building */

	/**
	 * Hourly check (WP-Cron).
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', self::CRON );
		}
	}

	/**
	 * A product changed: the feed is built again at the next hourly check.
	 *
	 * @param int $id Product or post ID.
	 */
	public static function changed( $id = 0 ) {
		if ( ! $id || in_array( get_post_type( $id ), array( 'product', 'product_variation' ), true ) ) {
			update_option( self::DIRTY, 1, false );
		}
	}

	/**
	 * Builds the feed when products changed or it is older than a day.
	 */
	public static function cron() {
		if ( ! self::settings()['enabled'] ) {
			return;
		}
		$status = (array) get_option( self::STATUS, array() );
		$age    = isset( $status['time'] ) ? time() - (int) $status['time'] : PHP_INT_MAX;
		if ( get_option( self::DIRTY ) || $age > DAY_IN_SECONDS || ! is_file( self::file() ) ) {
			self::build();
		}
	}

	/**
	 * Builds the feed file.
	 *
	 * @return array Status: time, items, skipped (reason => count), seconds.
	 */
	public static function build() {
		$start = microtime( true );
		$file  = self::file();
		wp_mkdir_p( dirname( $file ) );
		if ( ! is_file( dirname( $file ) . '/index.html' ) ) {
			file_put_contents( dirname( $file ) . '/index.html', '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- An empty index.
		}
		$tmp = $file . '.tmp';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Written in pieces (large shops).
		$out = fopen( $tmp, 'wb' );
		if ( ! $out ) {
			return array();
		}
		$s         = self::settings();
		self::$ids = array();
		$items     = 0;
		$skipped = array();
		$skip    = static function ( $reason ) use ( &$skipped ) {
			$skipped[ $reason ] = isset( $skipped[ $reason ] ) ? $skipped[ $reason ] + 1 : 1;
		};
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fwrite( $out, '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n<channel>\n" );
		fwrite( $out, '<title>' . self::x( get_bloginfo( 'name' ) ) . "</title>\n<link>" . self::x( home_url( '/' ) ) . "</link>\n<description>" . self::x( get_bloginfo( 'description' ) ) . "</description>\n" );

		if ( class_exists( 'Envo_Blocks_Catalog_Mode' ) ) {
			Envo_Blocks_Catalog_Mode::bypass( true ); // Raw prices; products of the catalog mode are left out below.
		}
		// wc_get_products() takes the categories by slug (with their children in the query of WooCommerce).
		$cat_slugs = array();
		foreach ( $s['cats'] as $cat_id ) {
			$term = get_term( $cat_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$cat_slugs[] = $term->slug;
			}
		}
		$page = 1;
		do {
			$args = array(
				'status'   => 'publish',
				'type'     => array( 'simple', 'variable', 'external' ),
				'limit'    => 100,
				'page'     => $page,
				'orderby'  => 'ID',
				'order'    => 'ASC',
			);
			if ( $cat_slugs ) {
				$args['category'] = $cat_slugs;
			}
			$products = wc_get_products( $args );
			foreach ( $products as $product ) {
				$reason = self::excluded( $product );
				if ( $reason ) {
					$skip( $reason );
					continue;
				}
				if ( $product->is_type( 'variable' ) && $s['variations'] ) {
					foreach ( $product->get_children() as $child_id ) {
						$variation = wc_get_product( $child_id );
						if ( ! $variation || 'publish' !== $variation->get_status() ) {
							continue;
						}
						$xml = self::item( $variation, $product, $s, $reason );
						if ( $xml ) {
							fwrite( $out, $xml );
							++$items;
						} else {
							$skip( $reason );
						}
					}
					continue;
				}
				$xml = self::item( $product, null, $s, $reason );
				if ( $xml ) {
					fwrite( $out, $xml );
					++$items;
				} else {
					$skip( $reason );
				}
			}
			++$page;
			wp_cache_flush_runtime();
		} while ( count( $products ) === 100 );
		if ( class_exists( 'Envo_Blocks_Catalog_Mode' ) ) {
			Envo_Blocks_Catalog_Mode::bypass( false );
		}

		fwrite( $out, "</channel>\n</rss>\n" );
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		rename( $tmp, $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename

		$status = array(
			'time'    => time(),
			'items'   => $items,
			'skipped' => $skipped,
			'seconds' => round( microtime( true ) - $start, 1 ),
		);
		update_option( self::STATUS, $status, false );
		delete_option( self::DIRTY );
		return $status;
	}

	/**
	 * Why a product is left out ('' = it goes in).
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private static function excluded( $product ) {
		if ( $product->get_meta( self::EXCLUDE ) ) {
			return 'marked';
		}
		if ( 'hidden' === $product->get_catalog_visibility() || post_password_required( $product->get_id() ) || '' !== get_post_field( 'post_password', $product->get_id() ) ) {
			return 'hidden';
		}
		if ( class_exists( 'Envo_Blocks_Catalog_Mode' ) && Envo_Blocks_Catalog_Mode::applies( $product ) ) {
			return 'catalog';
		}
		if ( ! self::settings()['stock'] && ! $product->is_in_stock() ) {
			return 'stock';
		}
		return '';
	}

	/**
	 * One <item> of the feed.
	 *
	 * @param WC_Product      $product Product or variation.
	 * @param WC_Product|null $parent  Parent of a variation.
	 * @param array           $s       Settings.
	 * @param string          $reason  Why it was left out (set when '' is returned).
	 * @return string
	 */
	private static function item( $product, $parent, $s, &$reason ) {
		$main  = $parent ? $parent : $product;
		$price = $product->is_type( 'variable' ) ? $product->get_variation_regular_price( 'min' ) : $product->get_regular_price();
		$sale  = $product->is_type( 'variable' ) ? $product->get_variation_sale_price( 'min' ) : $product->get_sale_price();
		if ( '' === (string) $price || (float) $price <= 0 ) {
			$reason = 'price';
			return '';
		}
		$image_id = $product->get_image_id() ? $product->get_image_id() : $main->get_image_id();
		$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
		if ( ! $image ) {
			$reason = 'image';
			return '';
		}
		if ( ! $s['stock'] && ! $product->is_in_stock() ) {
			$reason = 'stock';
			return '';
		}

		$currency = get_woocommerce_currency();
		$money    = static function ( $value ) use ( $currency ) {
			return wc_format_decimal( $value, wc_get_price_decimals() ) . ' ' . $currency;
		};
		$text = $main->get_description() ? $main->get_description() : $main->get_short_description();
		if ( $parent && $product->get_description() ) {
			$text = $product->get_description();
		}
		$text  = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $text ) ) ) );
		$title = $main->get_name();
		if ( $parent ) {
			// "Ridge Trail Boot – Green, 40": the values of the variation, unless the name has them already.
			$values = wc_get_formatted_variation( $product, true, false, false );
			if ( '' !== $values && false === strpos( $product->get_name(), $values ) ) {
				$title .= ' – ' . $values;
			} else {
				$title = $product->get_name();
			}
		}
		$id = $product->get_sku() ? $product->get_sku() : (string) $product->get_id();
		if ( isset( self::$ids[ $id ] ) ) {
			$id = (string) $product->get_id();
		}
		self::$ids[ $id ] = true;

		$f   = array();
		$f[] = array( 'g:id', $id );
		$f[] = array( 'g:title', mb_substr( $title, 0, 150 ) );
		$f[] = array( 'g:description', mb_substr( '' !== $text ? $text : $title, 0, 5000 ) );
		$f[] = array( 'g:link', $product->get_permalink() );
		$f[] = array( 'g:image_link', $image );
		foreach ( array_slice( $main->get_gallery_image_ids(), 0, 10 ) as $extra ) {
			$url = wp_get_attachment_image_url( $extra, 'full' );
			if ( $url && $url !== $image ) {
				$f[] = array( 'g:additional_image_link', $url );
			}
		}
		$stock = $product->get_stock_status();
		$f[]   = array( 'g:availability', 'instock' === $stock ? 'in_stock' : ( 'onbackorder' === $stock ? 'backorder' : 'out_of_stock' ) );
		$f[]   = array( 'g:price', $money( $price ) );
		if ( '' !== (string) $sale && (float) $sale < (float) $price ) {
			$f[]  = array( 'g:sale_price', $money( $sale ) );
			$from = $product->get_date_on_sale_from();
			$to   = $product->get_date_on_sale_to();
			if ( $from && $to ) {
				$f[] = array( 'g:sale_price_effective_date', $from->format( 'c' ) . '/' . $to->format( 'c' ) );
			}
		}
		$f[] = array( 'g:condition', $s['condition'] );

		// Identifiers: brand, GTIN (the GTIN, UPC, EAN or ISBN field of WooCommerce) and MPN (the SKU).
		$brand = self::brand( $main, $s );
		$gtin  = method_exists( $product, 'get_global_unique_id' ) ? (string) $product->get_global_unique_id() : '';
		if ( '' === $gtin && $parent && method_exists( $parent, 'get_global_unique_id' ) ) {
			$gtin = (string) $parent->get_global_unique_id();
		}
		if ( '' !== $brand ) {
			$f[] = array( 'g:brand', $brand );
		}
		if ( '' !== $gtin ) {
			$f[] = array( 'g:gtin', $gtin );
		}
		if ( $product->get_sku() ) {
			$f[] = array( 'g:mpn', $product->get_sku() );
		}
		if ( '' === $gtin && ! $product->get_sku() ) {
			$f[] = array( 'g:identifier_exists', 'no' );
		}

		if ( $parent ) {
			$f[] = array( 'g:item_group_id', $parent->get_sku() ? $parent->get_sku() : (string) $parent->get_id() );
		}
		foreach ( array( 'color' => 'g:color', 'size' => 'g:size' ) as $key => $tag ) {
			$value = $s[ $key ] ? self::attribute( $product, $parent, $s[ $key ] ) : '';
			if ( '' !== $value ) {
				$f[] = array( $tag, $value );
			}
		}
		$type = self::category_path( $main );
		if ( '' !== $type ) {
			$f[] = array( 'g:product_type', $type );
		}
		if ( '' !== trim( $s['category'] ) ) {
			$f[] = array( 'g:google_product_category', trim( $s['category'] ) );
		}
		if ( $product->get_weight() ) {
			$f[] = array( 'g:shipping_weight', wc_format_decimal( $product->get_weight() ) . ' ' . get_option( 'woocommerce_weight_unit' ) );
		}

		/**
		 * Fields of one product of the feed: array( array( tag, value ) ), e.g. to add custom_label_0.
		 *
		 * @param array           $f       Fields.
		 * @param WC_Product      $product Product or variation.
		 * @param WC_Product|null $parent  Parent of a variation.
		 */
		$f   = (array) apply_filters( 'envo_blocks_feed_item', $f, $product, $parent );
		$xml = "<item>\n";
		foreach ( $f as $field ) {
			$xml .= '<' . $field[0] . '>' . self::x( (string) $field[1] ) . '</' . $field[0] . ">\n";
		}
		return $xml . "</item>\n";
	}

	/**
	 * Brand of a product by the settings.
	 *
	 * @param WC_Product $product Product (the parent of a variation).
	 * @param array      $s       Settings.
	 * @return string
	 */
	private static function brand( $product, $s ) {
		if ( 'taxonomy' === $s['brand'] && taxonomy_exists( 'product_brand' ) ) {
			$terms = get_the_terms( $product->get_id(), 'product_brand' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				return html_entity_decode( $terms[0]->name, ENT_QUOTES, 'UTF-8' );
			}
		} elseif ( 0 === strpos( $s['brand'], 'pa_' ) ) {
			$value = $product->get_attribute( $s['brand'] );
			if ( '' !== $value ) {
				return trim( explode( ',', $value )[0] );
			}
		}
		return (string) $s['brand_text'];
	}

	/**
	 * Value of an attribute: of the variation, else of the product.
	 *
	 * @param WC_Product      $product  Product or variation.
	 * @param WC_Product|null $parent   Parent of a variation.
	 * @param string          $taxonomy Attribute taxonomy.
	 * @return string
	 */
	private static function attribute( $product, $parent, $taxonomy ) {
		$value = $product->get_attribute( $taxonomy );
		if ( '' === $value && $parent ) {
			$value = $parent->get_attribute( $taxonomy );
		}
		// A product (not a variation) with several values: "Blue/Red", as Google asks.
		return str_replace( ', ', '/', html_entity_decode( $value, ENT_QUOTES, 'UTF-8' ) );
	}

	/**
	 * "Phones > Smartphones" from the deepest category of the product.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private static function category_path( $product ) {
		$best = array();
		foreach ( $product->get_category_ids() as $id ) {
			$path = array_reverse( array_merge( array( $id ), get_ancestors( $id, 'product_cat', 'taxonomy' ) ) );
			if ( count( $path ) > count( $best ) ) {
				$best = $path;
			}
		}
		$names = array();
		foreach ( $best as $id ) {
			$term = get_term( $id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) && 'uncategorized' !== $term->slug ) {
				$names[] = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' );
			}
		}
		return implode( ' > ', $names );
	}

	/**
	 * Text for XML.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function x( $text ) {
		// Characters XML 1.0 does not allow (copied from Word etc.) would break the whole feed.
		$text = preg_replace( '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $text );
		return htmlspecialchars( (string) $text, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * The feed URL serves the file (built the first time).
	 */
	public static function serve() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- A public feed.
		if ( ! isset( $_GET[ self::QUERY ] ) ) {
			return;
		}
		if ( ! self::settings()['enabled'] ) {
			status_header( 404 );
			exit;
		}
		$file = self::file();
		if ( ! is_file( $file ) ) {
			self::build();
		}
		nocache_headers();
		header( 'Content-Type: application/xml; charset=UTF-8' );
		header( 'X-Robots-Tag: noindex' );
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}

	/* ------------------------------------------------------------------ product */

	/**
	 * "Hide from product feeds" in Product data › Advanced.
	 */
	public static function product_field() {
		echo '<div class="options_group">';
		woocommerce_wp_checkbox(
			array(
				'id'          => self::EXCLUDE,
				'label'       => __( 'Product feeds', 'envo-blocks' ),
				'description' => __( 'Hide from the product feed (Google Merchant Center, Meta catalog)', 'envo-blocks' ),
			)
		);
		echo '</div>';
	}

	/**
	 * Saves the checkbox.
	 *
	 * @param WC_Product $product Product.
	 */
	public static function product_save( $product ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce checked the nonce of the product form.
		$product->update_meta_data( self::EXCLUDE, empty( $_POST[ self::EXCLUDE ] ) ? '' : 'yes' );
	}

	/* ------------------------------------------------------------------ admin */

	/**
	 * Envo Blocks › Settings › Product Feed.
	 *
	 * @param array $pages Pages.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Product Feed', 'envo-blocks' ),
			'title'      => __( 'Product Feed', 'envo-blocks' ),
			'callback'   => array( __CLASS__, 'render' ),
			'capability' => 'manage_woocommerce',
			'section'    => 'sales',
		);
		return $pages;
	}

	/**
	 * Styles of the page.
	 *
	 * @param string $hook Admin page.
	 */
	public static function admin_assets( $hook ) {
		if ( false !== strpos( (string) $hook, self::PAGE ) ) {
			wp_enqueue_style( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/css/checkout-fields.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/checkout-fields.css' ) );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_script( 'wc-enhanced-select' );
		}
	}

	/**
	 * The page: the URL and the status, then the settings.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show after a redirect.
		$done   = isset( $_GET['eb-feed'] ) ? sanitize_key( wp_unslash( $_GET['eb-feed'] ) ) : '';
		$s      = self::settings();
		$status = (array) get_option( self::STATUS, array() );
		$labels = array(
			'price'   => __( 'no price', 'envo-blocks' ),
			'image'   => __( 'no image', 'envo-blocks' ),
			'hidden'  => __( 'hidden or with a password', 'envo-blocks' ),
			'catalog' => __( 'prices hidden by the catalog mode', 'envo-blocks' ),
			'stock'   => __( 'out of stock', 'envo-blocks' ),
			'marked'  => __( 'marked "Hide from the product feed"', 'envo-blocks' ),
		);
		$attrs = array( '' => __( '— None —', 'envo-blocks' ) );
		foreach ( wc_get_attribute_taxonomies() as $tax ) {
			$attrs[ 'pa_' . $tax->attribute_name ] = $tax->attribute_label;
		}
		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);
		?>
		<div class="eb-cf eb-feed">
			<?php if ( 'saved' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The settings were saved.', 'envo-blocks' ); ?></p></div>
			<?php elseif ( 'built' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The feed was built.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>
			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Product feed', 'envo-blocks' ); ?></h2>
				<p class="eb-cf__intro"><?php esc_html_e( 'Your products as one XML feed for Google Merchant Center (Google Shopping and the free product listings in Google) and for the Meta catalog (Facebook and Instagram shops). Add the feed URL there once; the feed updates itself every hour when products change.', 'envo-blocks' ); ?></p>
				<?php if ( $s['enabled'] ) : ?>
					<p><label for="eb-feed-url"><strong><?php esc_html_e( 'Feed URL', 'envo-blocks' ); ?></strong></label><br>
						<input type="text" id="eb-feed-url" class="large-text code" readonly value="<?php echo esc_attr( self::url() ); ?>" onclick="this.select()"></p>
					<?php if ( ! empty( $status['time'] ) ) : ?>
						<p>
							<?php
							/* translators: 1: number of products, 2: date and time */
							echo esc_html( sprintf( __( '%1$s products in the feed, built %2$s.', 'envo-blocks' ), number_format_i18n( (int) $status['items'] ), wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $status['time'] ) ) );
							?>
						</p>
						<?php if ( ! empty( $status['skipped'] ) ) : ?>
							<p class="description">
								<?php
								$parts = array();
								foreach ( (array) $status['skipped'] as $reason => $count ) {
									$parts[] = number_format_i18n( (int) $count ) . ' ' . ( isset( $labels[ $reason ] ) ? $labels[ $reason ] : $reason );
								}
								/* translators: %s: list of reasons with counts */
								echo esc_html( sprintf( __( 'Left out: %s.', 'envo-blocks' ), implode( ', ', $parts ) ) );
								?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="envo_blocks_feed_build">
						<?php wp_nonce_field( 'envo_blocks_feed_build' ); ?>
						<p>
							<button type="submit" class="button"><?php esc_html_e( 'Build the feed now', 'envo-blocks' ); ?></button>
							<a class="button-link" href="<?php echo esc_url( self::url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open the feed', 'envo-blocks' ); ?></a>
						</p>
					</form>
					<p class="description">
						<a href="https://merchants.google.com/" target="_blank" rel="noopener">Google Merchant Center</a> › <?php esc_html_e( 'Products › Add products › Add products from a file › Enter a link to your file', 'envo-blocks' ); ?><br>
						<a href="https://business.facebook.com/commerce/" target="_blank" rel="noopener">Meta Commerce Manager</a> › <?php esc_html_e( 'Catalog › Data sources › Add items › Data feed › Scheduled feed', 'envo-blocks' ); ?>
					</p>
				<?php else : ?>
					<p class="eb-cf__intro"><?php esc_html_e( 'The feed is off. Turn it on below to get its URL.', 'envo-blocks' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Settings', 'envo-blocks' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="envo_blocks_feed">
					<?php wp_nonce_field( 'envo_blocks_feed' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Product feed', 'envo-blocks' ); ?></th>
							<td><label><input type="checkbox" name="enabled" value="1" <?php checked( $s['enabled'] ); ?>> <?php esc_html_e( 'Make the product feed', 'envo-blocks' ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-feed-cats"><?php esc_html_e( 'Categories', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-feed-cats" name="cats[]" multiple class="wc-enhanced-select" style="width:100%;max-width:420px" data-placeholder="<?php esc_attr_e( 'All categories', 'envo-blocks' ); ?>">
									<?php foreach ( is_array( $cats ) ? $cats : array() as $cat ) : ?>
										<option value="<?php echo (int) $cat->term_id; ?>" <?php selected( in_array( (int) $cat->term_id, $s['cats'], true ) ); ?>><?php echo esc_html( $cat->name ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Empty = all products. A single product is left out with "Hide from the product feed" in Product data › Advanced.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Products', 'envo-blocks' ); ?></th>
							<td>
								<label><input type="checkbox" name="variations" value="1" <?php checked( $s['variations'] ); ?>> <?php esc_html_e( 'Every variation as its own product (recommended by Google)', 'envo-blocks' ); ?></label><br>
								<label><input type="checkbox" name="stock" value="1" <?php checked( $s['stock'] ); ?>> <?php esc_html_e( 'Also products that are out of stock (shown as out of stock)', 'envo-blocks' ); ?></label>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-feed-brand"><?php esc_html_e( 'Brand', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-feed-brand" name="brand">
									<?php if ( taxonomy_exists( 'product_brand' ) ) : ?>
										<option value="taxonomy" <?php selected( $s['brand'], 'taxonomy' ); ?>><?php esc_html_e( 'Brands of WooCommerce', 'envo-blocks' ); ?></option>
									<?php endif; ?>
									<?php foreach ( $attrs as $key => $label ) : ?>
										<?php if ( '' !== $key ) : ?>
											<?php /* translators: %s: attribute name */ ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $s['brand'], $key ); ?>><?php echo esc_html( sprintf( __( 'Attribute: %s', 'envo-blocks' ), $label ) ); ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
									<option value="text" <?php selected( $s['brand'], 'text' ); ?>><?php esc_html_e( 'Always the text below', 'envo-blocks' ); ?></option>
								</select>
								<p><input type="text" name="brand_text" class="regular-text" value="<?php echo esc_attr( $s['brand_text'] ); ?>"></p>
								<p class="description"><?php esc_html_e( 'The text is used when a product has no brand. Your own products: your shop name.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Attributes', 'envo-blocks' ); ?></th>
							<td>
								<?php foreach ( array( 'color' => __( 'Color', 'envo-blocks' ), 'size' => __( 'Size', 'envo-blocks' ) ) as $key => $label ) : ?>
									<label style="display:inline-block;margin:0 16px 8px 0"><?php echo esc_html( $label ); ?><br>
										<select name="<?php echo esc_attr( $key ); ?>">
											<?php foreach ( $attrs as $value => $name ) : ?>
												<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $s[ $key ], $value ); ?>><?php echo esc_html( $name ); ?></option>
											<?php endforeach; ?>
										</select>
									</label>
								<?php endforeach; ?>
								<p class="description"><?php esc_html_e( 'Google asks for the color and the size of clothing and shoes.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-feed-condition"><?php echo esc_html_x( 'Condition', 'product condition', 'envo-blocks' ); ?></label></th>
							<td>
								<select id="eb-feed-condition" name="condition">
									<option value="new" <?php selected( $s['condition'], 'new' ); ?>><?php esc_html_e( 'New', 'envo-blocks' ); ?></option>
									<option value="refurbished" <?php selected( $s['condition'], 'refurbished' ); ?>><?php esc_html_e( 'Refurbished', 'envo-blocks' ); ?></option>
									<option value="used" <?php selected( $s['condition'], 'used' ); ?>><?php esc_html_e( 'Used', 'envo-blocks' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-feed-category"><?php esc_html_e( 'Google product category', 'envo-blocks' ); ?></label></th>
							<td>
								<input type="text" id="eb-feed-category" name="category" class="regular-text" value="<?php echo esc_attr( $s['category'] ); ?>" placeholder="267">
								<p class="description"><?php esc_html_e( 'Optional: the number or the path of the Google category of most of your products (e.g. 267 = Mobile Phones). Google finds it by itself when empty.', 'envo-blocks' ); ?></p>
							</td>
						</tr>
					</table>
					<p class="description"><?php esc_html_e( 'The feed also takes the GTIN, UPC, EAN or ISBN field of WooCommerce (Product data › Inventory) and the SKU as the MPN.', 'envo-blocks' ); ?></p>
					<?php submit_button( __( 'Save changes', 'envo-blocks' ) ); ?>
				</form>
			</section>
		</div>
		<?php
	}

	/**
	 * Saves the settings (admin-post.php) and builds the feed.
	 */
	public static function save_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_feed' );
		$attr = static function ( $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked above.
			$value = isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : '';
			return '' === $value || taxonomy_exists( $value ) ? $value : '';
		};
		$brand = isset( $_POST['brand'] ) ? sanitize_key( wp_unslash( $_POST['brand'] ) ) : 'text';
		update_option(
			self::OPTION,
			array(
				'enabled'    => ! empty( $_POST['enabled'] ),
				'cats'       => isset( $_POST['cats'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['cats'] ) ) : array(), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- absint.
				'stock'      => ! empty( $_POST['stock'] ),
				'variations' => ! empty( $_POST['variations'] ),
				'brand'      => in_array( $brand, array( 'taxonomy', 'text' ), true ) || taxonomy_exists( $brand ) ? $brand : 'text',
				'brand_text' => isset( $_POST['brand_text'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_text'] ) ) : '',
				'condition'  => isset( $_POST['condition'] ) && in_array( wp_unslash( $_POST['condition'] ), array( 'new', 'refurbished', 'used' ), true ) ? sanitize_key( wp_unslash( $_POST['condition'] ) ) : 'new',
				'color'      => $attr( 'color' ),
				'size'       => $attr( 'size' ),
				'category'   => isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '',
			)
		);
		if ( self::settings()['enabled'] ) {
			self::build();
		}
		wp_safe_redirect( add_query_arg( 'eb-feed', 'saved', admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * "Build the feed now" (admin-post.php).
	 */
	public static function build_now() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_feed_build' );
		self::build();
		wp_safe_redirect( add_query_arg( 'eb-feed', 'built', admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}
}
