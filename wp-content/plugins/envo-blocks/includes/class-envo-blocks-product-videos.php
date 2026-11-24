<?php
/**
 * Product videos: YouTube, Vimeo, Dailymotion or own video files (mp4, webm) in the product gallery.
 *
 * - Product data > Videos (classic product editor): up to MAX videos, each with an optional cover image and its place
 *   in the gallery (first, after the main image, last). Meta _envo_blocks_videos.
 * - The gallery of WooCommerce (classic themes and the Product Image Gallery block): a slide with the cover and a play
 *   button; the player loads only after the click (nothing from the video service before), and stops when another
 *   slide is shown.
 * - Product lists: a product with an own video file plays it (muted) over its image while the pointer is on it.
 * - Envo Blocks PRO: its Product Gallery (Vertical) block reads the same videos through get().
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product videos.
 */
final class Envo_Blocks_Product_Videos {

	const META = '_envo_blocks_videos';
	const MAX  = 5;

	/**
	 * Gallery images already printed for the current product (to find the last one).
	 *
	 * @var int
	 */
	private static $printed = 0;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
		if ( is_admin() ) {
			add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'tab' ) );
			add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
			add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		}
		add_filter( 'woocommerce_single_product_image_thumbnail_html', array( __CLASS__, 'gallery_html' ), 20, 2 );
		add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'reset_count' ), 1 );
		add_filter( 'woocommerce_product_get_image', array( __CLASS__, 'card_image' ), 30, 2 );
		add_filter( 'render_block_woocommerce/product-image', array( __CLASS__, 'card_image_block' ), 30, 3 );
		add_filter( 'woocommerce_blocks_product_grid_item_html', array( __CLASS__, 'card_grid_item' ), 30, 3 );
	}

	/**
	 * Scripts and styles of the front end.
	 */
	public static function register_assets() {
		wp_register_style( 'envo-blocks-product-videos', ENVO_BLOCKS_URL . 'assets/css/product-videos.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/product-videos.css' ) );
		wp_register_script( 'envo-blocks-product-videos', ENVO_BLOCKS_URL . 'assets/js/product-videos-view.js', array(), Envo_Blocks_Blocks::ver( 'assets/js/product-videos-view.js' ), array( 'in_footer' => true ) );
	}

	/**
	 * Loads the front-end script and style.
	 */
	public static function enqueue() {
		wp_enqueue_style( 'envo-blocks-product-videos' );
		wp_enqueue_script( 'envo-blocks-product-videos' );
	}

	/* ------------------------------------------------------------------ */
	/* Data                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Videos of a product, ready to show.
	 *
	 * @param WC_Product|int $product Product or its ID (a variation uses its parent).
	 * @return array List of array( url, embed => array( type, src ), poster_id, poster => array( thumb, single, full,
	 *               width, height ), position => first|second|last, label ).
	 */
	public static function get( $product ) {
		$product = is_numeric( $product ) ? wc_get_product( (int) $product ) : $product;
		if ( ! $product instanceof WC_Product ) {
			return array();
		}
		if ( $product->get_parent_id() ) {
			$product = wc_get_product( $product->get_parent_id() );
			if ( ! $product ) {
				return array();
			}
		}

		static $cache = array();
		$id = $product->get_id();
		if ( isset( $cache[ $id ] ) ) {
			return $cache[ $id ];
		}

		$out = array();
		foreach ( self::raw( $id ) as $n => $item ) {
			$embed = Envo_Blocks_Blocks::video_embed( $item['url'] );
			if ( ! $embed ) {
				continue;
			}
			$poster_id = $item['poster'] ? $item['poster'] : (int) $product->get_image_id();
			$full      = $poster_id ? wp_get_attachment_image_src( $poster_id, 'full' ) : false;
			$single    = $poster_id ? wp_get_attachment_image_src( $poster_id, 'woocommerce_single' ) : false;
			$thumb     = $poster_id ? wp_get_attachment_image_src( $poster_id, 'woocommerce_gallery_thumbnail' ) : false;
			$out[]     = array(
				'url'       => $item['url'],
				'embed'     => $embed,
				'poster_id' => $poster_id,
				'poster'    => array(
					'full'   => $full ? $full[0] : wc_placeholder_img_src( 'full' ),
					'single' => $single ? $single[0] : wc_placeholder_img_src( 'woocommerce_single' ),
					'thumb'  => $thumb ? $thumb[0] : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ),
					'width'  => $full ? (int) $full[1] : 1200,
					'height' => $full ? (int) $full[2] : 1200,
				),
				'position'  => $item['position'],
				/* translators: 1: product name, 2: number of the video. */
				'label'     => sprintf( __( 'Video of %1$s (%2$d)', 'envo-blocks' ), wp_strip_all_tags( $product->get_name() ), $n + 1 ),
			);
		}

		$cache[ $id ] = $out;
		return $out;
	}

	/**
	 * Saved videos of a product (cleaned).
	 *
	 * @param int $product_id Product ID.
	 * @return array List of array( url, poster, position ).
	 */
	private static function raw( $product_id ) {
		$raw = get_post_meta( $product_id, self::META, true );
		return self::sanitize( is_array( $raw ) ? $raw : array() );
	}

	/**
	 * Cleans a list of videos (from the meta or from the form).
	 *
	 * @param array $list Raw list.
	 * @return array
	 */
	private static function sanitize( array $list ) {
		$out = array();
		foreach ( array_values( $list ) as $item ) {
			if ( ! is_array( $item ) || empty( $item['url'] ) ) {
				continue;
			}
			$url = esc_url_raw( trim( (string) $item['url'] ), array( 'http', 'https' ) );
			if ( '' === $url || ! Envo_Blocks_Blocks::video_embed( $url ) ) {
				continue;
			}
			$out[] = array(
				'url'      => $url,
				'poster'   => isset( $item['poster'] ) ? absint( $item['poster'] ) : 0,
				'position' => isset( $item['position'] ) && in_array( $item['position'], array( 'first', 'second', 'last' ), true ) ? $item['position'] : 'second',
			);
			if ( count( $out ) >= self::MAX ) {
				break;
			}
		}
		return $out;
	}

	/* ------------------------------------------------------------------ */
	/* Product editor                                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Product data > Videos.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function tab( $tabs ) {
		$tabs['envo_blocks_videos'] = array(
			'label'    => __( 'Videos', 'envo-blocks' ),
			'target'   => 'envo_blocks_videos_data',
			'class'    => array(),
			'priority' => 75,
		);
		return $tabs;
	}

	/**
	 * The Videos panel.
	 */
	public static function panel() {
		global $post;
		$videos = $post ? self::raw( $post->ID ) : array();
		?>
		<div id="envo_blocks_videos_data" class="panel woocommerce_options_panel hidden eb-pv-admin">
			<div class="options_group">
				<p class="eb-pv-admin__intro"><?php esc_html_e( 'Videos in the product gallery: a YouTube, Vimeo or Dailymotion link, or a video file (mp4, webm) from the Media Library. The player loads only when the visitor clicks play. A video file also plays (muted) over the product image in product lists while the pointer is on it.', 'envo-blocks' ); ?></p>
				<?php wp_nonce_field( 'envo_blocks_videos', 'envo_blocks_videos_nonce' ); ?>
				<div class="eb-pv-admin__rows" data-max="<?php echo esc_attr( (string) self::MAX ); ?>">
					<?php
					foreach ( $videos as $i => $video ) {
						self::row( (string) $i, $video );
					}
					?>
				</div>
				<p class="eb-pv-admin__add-wrap"><button type="button" class="button eb-pv-admin__add"><?php esc_html_e( 'Add video', 'envo-blocks' ); ?></button></p>
				<template class="eb-pv-admin__template">
					<?php
					self::row(
						'__i__',
						array(
							'url'      => '',
							'poster'   => 0,
							'position' => 'second',
						)
					);
					?>
				</template>
			</div>
		</div>
		<?php
	}

	/**
	 * One video row of the panel.
	 *
	 * @param string $i     Row index.
	 * @param array  $video Video.
	 */
	private static function row( $i, array $video ) {
		$name   = 'envo_blocks_videos[' . $i . ']';
		$poster = $video['poster'] ? wp_get_attachment_image_url( $video['poster'], 'thumbnail' ) : '';
		?>
		<div class="eb-pv-admin__row">
			<p class="form-field">
				<label><?php esc_html_e( 'Video link or file', 'envo-blocks' ); ?></label>
				<input type="url" class="eb-pv-admin__url" name="<?php echo esc_attr( $name . '[url]' ); ?>" value="<?php echo esc_attr( $video['url'] ); ?>" placeholder="https://www.youtube.com/watch?v=…">
				<button type="button" class="button eb-pv-admin__file"><?php esc_html_e( 'Choose file', 'envo-blocks' ); ?></button>
			</p>
			<p class="form-field">
				<label><?php esc_html_e( 'Cover image', 'envo-blocks' ); ?></label>
				<input type="hidden" class="eb-pv-admin__poster" name="<?php echo esc_attr( $name . '[poster]' ); ?>" value="<?php echo esc_attr( (string) $video['poster'] ); ?>">
				<span class="eb-pv-admin__preview"><?php echo $poster ? '<img src="' . esc_url( $poster ) . '" alt="">' : ''; ?></span>
				<button type="button" class="button eb-pv-admin__choose"><?php esc_html_e( 'Choose image', 'envo-blocks' ); ?></button>
				<button type="button" class="button-link eb-pv-admin__clear" <?php echo $poster ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove image', 'envo-blocks' ); ?></button>
				<span class="description"><?php esc_html_e( 'Without an image the main product image is used.', 'envo-blocks' ); ?></span>
			</p>
			<p class="form-field">
				<label><?php esc_html_e( 'Place in the gallery', 'envo-blocks' ); ?></label>
				<select name="<?php echo esc_attr( $name . '[position]' ); ?>">
					<option value="first" <?php selected( $video['position'], 'first' ); ?>><?php esc_html_e( 'First (before the main image)', 'envo-blocks' ); ?></option>
					<option value="second" <?php selected( $video['position'], 'second' ); ?>><?php esc_html_e( 'After the main image', 'envo-blocks' ); ?></option>
					<option value="last" <?php selected( $video['position'], 'last' ); ?>><?php esc_html_e( 'Last', 'envo-blocks' ); ?></option>
				</select>
				<button type="button" class="button-link button-link-delete eb-pv-admin__remove"><?php esc_html_e( 'Remove video', 'envo-blocks' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Saves the videos with the product.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function save( $product_id ) {
		if ( ! isset( $_POST['envo_blocks_videos_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['envo_blocks_videos_nonce'] ) ), 'envo_blocks_videos' ) || ! current_user_can( 'edit_product', $product_id ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cleaned in sanitize().
		$list = isset( $_POST['envo_blocks_videos'] ) && is_array( $_POST['envo_blocks_videos'] ) ? wp_unslash( $_POST['envo_blocks_videos'] ) : array();
		$list = self::sanitize( $list );
		if ( $list ) {
			update_post_meta( $product_id, self::META, $list );
		} else {
			delete_post_meta( $product_id, self::META );
		}
	}

	/**
	 * Media Library and the panel script on the product screen.
	 *
	 * @param string $hook Admin page.
	 */
	public static function admin_assets( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'product' !== $screen->id ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'envo-blocks-product-videos-admin', ENVO_BLOCKS_URL . 'assets/css/product-videos-admin.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/product-videos-admin.css' ) );
		wp_enqueue_script( 'envo-blocks-product-videos-admin', ENVO_BLOCKS_URL . 'assets/js/product-videos-admin.js', array( 'jquery' ), Envo_Blocks_Blocks::ver( 'assets/js/product-videos-admin.js' ), true );
		wp_localize_script(
			'envo-blocks-product-videos-admin',
			'envoBlocksVideos',
			array(
				'video' => __( 'Choose a video file', 'envo-blocks' ),
				'image' => __( 'Choose a cover image', 'envo-blocks' ),
				'use'   => __( 'Use', 'envo-blocks' ),
			)
		);
	}

	/* ------------------------------------------------------------------ */
	/* Gallery of WooCommerce                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * A new product gallery starts.
	 */
	public static function reset_count() {
		self::$printed = 0;
	}

	/**
	 * Adds the video slides around the images of the WooCommerce gallery.
	 *
	 * @param string $html          Image slide.
	 * @param int    $attachment_id Image ID.
	 * @return string
	 */
	public static function gallery_html( $html, $attachment_id ) {
		global $product;
		if ( ! $product instanceof WC_Product || ! is_singular( 'product' ) ) {
			return $html;
		}
		$videos = self::get( $product );
		if ( ! $videos ) {
			return $html;
		}
		self::enqueue();

		$images = array_values( array_filter( array_merge( array( (int) $product->get_image_id() ), array_map( 'intval', $product->get_gallery_image_ids() ) ) ) );
		$is_main = 0 === self::$printed;
		++self::$printed;
		$is_last = self::$printed >= max( 1, count( $images ) );

		$before = '';
		$after  = '';
		foreach ( $videos as $video ) {
			if ( $is_main && 'first' === $video['position'] ) {
				$before .= self::slide( $video );
			} elseif ( $is_main && 'second' === $video['position'] ) {
				$after .= self::slide( $video );
			} elseif ( $is_last && 'last' === $video['position'] ) {
				$after .= self::slide( $video );
			}
		}
		return $before . $html . $after;
	}

	/**
	 * A video slide in the markup of the WooCommerce gallery.
	 *
	 * @param array $video Video from get().
	 * @return string
	 */
	public static function slide( array $video ) {
		return sprintf(
			'<div data-thumb="%1$s" data-thumb-alt="%2$s" class="woocommerce-product-gallery__image eb-pv" data-eb-pv="%3$s">'
			. '<a href="%4$s" class="eb-pv__play" aria-label="%2$s">'
			. '<img src="%5$s" class="eb-pv__poster" alt="%2$s" width="%6$d" height="%7$d" loading="lazy" decoding="async" data-large_image="%4$s" data-large_image_width="%6$d" data-large_image_height="%7$d">'
			. '%8$s</a></div>',
			esc_url( $video['poster']['thumb'] ),
			esc_attr( $video['label'] ),
			esc_attr( wp_json_encode( $video['embed'] ) ),
			esc_url( $video['poster']['full'] ),
			esc_url( $video['poster']['single'] ),
			(int) $video['poster']['width'],
			(int) $video['poster']['height'],
			self::play_icon()
		);
	}

	/**
	 * The round play button over a cover.
	 *
	 * @return string
	 */
	public static function play_icon() {
		return '<span class="eb-pv__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="28" height="28" focusable="false"><path d="M8 5.5v13l10.5-6.5z" fill="currentColor"/></svg></span>';
	}

	/* ------------------------------------------------------------------ */
	/* Product lists                                                      */
	/* ------------------------------------------------------------------ */

	/**
	 * The first own video file of a product as an attribute of its card image ('' when it has none).
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private static function card_attribute( $product ) {
		foreach ( self::get( $product ) as $video ) {
			if ( 'file' === $video['embed']['type'] ) {
				self::enqueue();
				return ' data-eb-pv-card="' . esc_url( $video['embed']['src'] ) . '"';
			}
		}
		return '';
	}

	/**
	 * Product image of the classic lists.
	 *
	 * @param string     $html    Image HTML.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function card_image( $html, $product ) {
		if ( ! $product instanceof WC_Product || false !== strpos( $html, 'data-eb-pv-card' ) || is_admin() ) {
			return $html;
		}
		$attribute = self::card_attribute( $product );
		return '' === $attribute ? $html : preg_replace( '/<img\b/', '<img' . $attribute, $html, 1 );
	}

	/**
	 * Product Image block of the product templates (Product Collection).
	 *
	 * @param string   $html     Block HTML.
	 * @param array    $parsed   Parsed block.
	 * @param WP_Block $instance Block instance.
	 * @return string
	 */
	public static function card_image_block( $html, $parsed, $instance ) {
		if ( false !== strpos( $html, 'data-eb-pv-card' ) || ! $instance instanceof WP_Block || empty( $instance->context['postId'] ) ) {
			return $html;
		}
		$product = wc_get_product( $instance->context['postId'] );
		if ( ! $product ) {
			return $html;
		}
		$attribute = self::card_attribute( $product );
		return '' === $attribute ? $html : preg_replace( '/<img\b/', '<img' . $attribute, $html, 1 );
	}

	/**
	 * Older WooCommerce product grid blocks.
	 *
	 * @param string     $html    Item HTML.
	 * @param object     $data    Item parts.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function card_grid_item( $html, $data, $product ) {
		if ( ! $product instanceof WC_Product || false !== strpos( $html, 'data-eb-pv-card' ) ) {
			return $html;
		}
		$attribute = self::card_attribute( $product );
		return '' === $attribute ? $html : preg_replace( '/<img\b/', '<img' . $attribute, $html, 1 );
	}
}
