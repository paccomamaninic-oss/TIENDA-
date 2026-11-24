<?php
/**
 * Review photos: customers add up to 3 photos to their product review (classic and block product pages). The photos
 * are attachments of the product (Media Library), listed in the comment meta envo_blocks_photos, and show under the
 * review text once the review is approved. The Customer Photos block shows them as a gallery.
 *
 * Settings: Envo Blocks › Reviews (options envo_blocks_review_photos, _max, _mb).
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Photos of product reviews.
 */
class Envo_Blocks_Review_Photos {

	/**
	 * Comment meta with the attachment IDs.
	 */
	const META = 'envo_blocks_photos';

	/**
	 * Option of the settings.
	 */
	const OPTION = 'envo_blocks_review_photos';

	/**
	 * Name of the file field.
	 */
	const FIELD = 'envo_blocks_review_photos';

	/**
	 * Envo Blocks › Reviews.
	 */
	const PAGE = 'envo-blocks-reviews';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'admin_post_envo_blocks_reviews', array( __CLASS__, 'save_settings' ) );
		add_filter( 'comment_form_field_comment', array( __CLASS__, 'form_field' ) );
		add_action( 'comment_post', array( __CLASS__, 'save' ), 20, 3 );
		add_filter( 'comment_text', array( __CLASS__, 'show' ), 20, 2 );
		add_action( 'delete_comment', array( __CLASS__, 'delete' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * Settings with defaults.
	 *
	 * @return array{enabled:bool, max:int, mb:int}
	 */
	public static function settings() {
		$php = (int) floor( wp_max_upload_size() / MB_IN_BYTES );
		$mb  = (int) get_option( self::OPTION . '_mb', 5 );

		return array(
			'enabled' => 'no' !== get_option( self::OPTION, 'yes' ),
			'max'     => max( 1, min( 10, (int) get_option( self::OPTION . '_max', 3 ) ) ),
			'mb'      => max( 1, $php > 0 ? min( $mb, $php ) : $mb ),
		);
	}

	/**
	 * Envo Blocks › Reviews.
	 *
	 * @param array $pages Pages of the Envo Blocks menu.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		$pages[ self::PAGE ] = array(
			'menu'       => __( 'Reviews', 'envo-blocks' ),
			'title'      => __( 'Reviews', 'envo-blocks' ),
			'callback'   => array( __CLASS__, 'render' ),
			'capability' => 'manage_woocommerce',
		);

		return $pages;
	}

	/**
	 * Styles of the page (the cards of the Checkout Fields page).
	 *
	 * @param string $hook Admin page.
	 */
	public static function admin_assets( $hook ) {
		if ( false !== strpos( (string) $hook, self::PAGE ) ) {
			wp_enqueue_style( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/css/checkout-fields.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/checkout-fields.css' ) );
		}
	}

	/**
	 * The page: photos in reviews (and with PRO the review e-mails).
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show after a redirect.
		$saved = isset( $_GET['eb-rv'] ) && 'saved' === sanitize_key( wp_unslash( $_GET['eb-rv'] ) );
		$s     = self::settings();
		$php   = (int) floor( wp_max_upload_size() / MB_IN_BYTES );
		?>
		<div class="eb-cf eb-rv">
			<?php if ( $saved ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The settings were saved.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>

			<section class="eb-cf__card">
				<h2><?php esc_html_e( 'Photos in reviews', 'envo-blocks' ); ?></h2>
				<p class="eb-cf__intro"><?php esc_html_e( 'Customers add photos to their product review. The photos are saved as small WebP files without the camera data, show under the review once you approve it and in the Customer Photos block.', 'envo-blocks' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="envo_blocks_reviews">
					<?php wp_nonce_field( 'envo_blocks_reviews' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Photos in reviews', 'envo-blocks' ); ?></th>
							<td><label><input type="checkbox" name="enabled" value="1" <?php checked( $s['enabled'] ); ?>> <?php esc_html_e( 'Customers can add photos to their review', 'envo-blocks' ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-rv-max"><?php esc_html_e( 'Photos per review', 'envo-blocks' ); ?></label></th>
							<td><input type="number" id="eb-rv-max" name="max" min="1" max="10" class="small-text" value="<?php echo esc_attr( (string) $s['max'] ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="eb-rv-mb"><?php esc_html_e( 'Largest photo (MB)', 'envo-blocks' ); ?></label></th>
							<td>
								<input type="number" id="eb-rv-mb" name="mb" min="1" max="50" class="small-text" value="<?php echo esc_attr( (string) get_option( self::OPTION . '_mb', 5 ) ); ?>">
								<?php /* translators: %d: upload limit of the server in MB */ ?>
								<p class="description"><?php echo esc_html( sprintf( __( 'JPG, PNG or WebP. The server allows up to %d MB.', 'envo-blocks' ), $php ) ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save changes', 'envo-blocks' ) ); ?>
				</form>
				<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product-reviews' ) ); ?>"><?php esc_html_e( 'Approve reviews in Products › Reviews', 'envo-blocks' ); ?></a> · <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=products' ) ); ?>"><?php esc_html_e( 'Review settings of WooCommerce (ratings, verified owners)', 'envo-blocks' ); ?></a></p>
			</section>
			<?php
			/**
			 * More cards of the Reviews page (Envo Blocks PRO: review requests and rewards).
			 */
			do_action( 'envo_blocks_reviews_page_cards' );
			?>
		</div>
		<?php
	}

	/**
	 * Saves the settings (admin-post.php).
	 */
	public static function save_settings() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change these settings.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_reviews' );
		update_option( self::OPTION, empty( $_POST['enabled'] ) ? 'no' : 'yes' );
		update_option( self::OPTION . '_max', max( 1, min( 10, isset( $_POST['max'] ) ? absint( $_POST['max'] ) : 3 ) ) );
		update_option( self::OPTION . '_mb', max( 1, min( 50, isset( $_POST['mb'] ) ? absint( $_POST['mb'] ) : 5 ) ) );
		wp_safe_redirect( add_query_arg( 'eb-rv', 'saved', admin_url( 'admin.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * Whether photos can be added to reviews of this post.
	 *
	 * @param int $post_id Post.
	 * @return bool
	 */
	private static function allowed( $post_id ) {
		return 'product' === get_post_type( $post_id ) && self::settings()['enabled'] && post_type_supports( 'product', 'comments' );
	}

	/**
	 * The photo field after the review text (comment_form() of the classic and the block product page).
	 *
	 * @param string $field HTML of the comment field.
	 * @return string
	 */
	public static function form_field( $field ) {
		if ( ! self::allowed( get_the_ID() ) ) {
			return $field;
		}
		$s = self::settings();
		wp_enqueue_style( 'envo-blocks-review-photos' );
		wp_enqueue_script( 'envo-blocks-review-photos' );

		return $field . '<p class="comment-form-envo-photos sb-rp-field">'
			. '<label for="envo-blocks-review-photos">' . esc_html__( 'Add photos (optional)', 'envo-blocks' ) . '</label>'
			// The input is hidden behind an own button: the browser's button text would be in the browser's language.
			. '<input class="sb-rp-field__input" type="file" id="envo-blocks-review-photos" name="' . esc_attr( self::FIELD ) . '[]" accept="image/jpeg,image/png,image/webp" multiple data-max="' . (int) $s['max'] . '" data-mb="' . (int) $s['mb'] . '" aria-describedby="envo-blocks-review-photos-help" />'
			. '<label class="sb-rp-field__button" for="envo-blocks-review-photos"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9 3 7.2 5H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3.2L15 3H9Zm3 5a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></svg>' . esc_html__( 'Choose photos', 'envo-blocks' ) . '</label>'
			. '<span class="sb-rp-field__help" id="envo-blocks-review-photos-help">'
			/* translators: 1: number of photos, 2: size in MB */
			. esc_html( sprintf( _n( 'Up to %1$d photo, JPG, PNG or WebP, up to %2$d MB.', 'Up to %1$d photos, JPG, PNG or WebP, up to %2$d MB each.', $s['max'], 'envo-blocks' ), $s['max'], $s['mb'] ) )
			. '</span><span class="sb-rp-field__preview" aria-live="polite"></span></p>';
	}

	/**
	 * Saves the photos of a new review.
	 *
	 * @param int        $comment_id Comment.
	 * @param int|string $approved   1, 0 or 'spam'.
	 * @param array      $data       Comment data.
	 */
	public static function save( $comment_id, $approved, $data ) {
		if ( 'spam' === $approved || empty( $data['comment_post_ID'] ) || ! self::allowed( (int) $data['comment_post_ID'] ) || empty( $_FILES[ self::FIELD ]['name'] ) || ! is_array( $_FILES[ self::FIELD ]['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- wp-comments-post.php has no nonce for visitors; the files are checked below.
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$s     = self::settings();
		$files = $_FILES[ self::FIELD ]; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Each file is checked below.
		$mimes = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'webp'         => 'image/webp',
		);
		$ids   = array();
		$post  = (int) $data['comment_post_ID'];
		$count = min( count( $files['name'] ), $s['max'] );

		for ( $i = 0; $i < $count; $i++ ) {
			if ( UPLOAD_ERR_OK !== (int) $files['error'][ $i ] || ! is_uploaded_file( $files['tmp_name'][ $i ] ) || (int) $files['size'][ $i ] > $s['mb'] * MB_IN_BYTES ) {
				continue;
			}
			$check = wp_check_filetype_and_ext( $files['tmp_name'][ $i ], $files['name'][ $i ], $mimes );
			if ( empty( $check['type'] ) || ! @getimagesize( $files['tmp_name'][ $i ] ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- A broken image only means "no photo".
				continue;
			}
			$file = self::clean_copy( $files['tmp_name'][ $i ], $check );
			if ( ! $file ) {
				continue;
			}
			$file['name'] = sanitize_file_name( 'review-' . $comment_id . '-' . ( $i + 1 ) . '.' . $file['ext'] );
			$id = media_handle_sideload(
				$file,
				$post,
				/* translators: %s: product name */
				sprintf( __( 'Review photo – %s', 'envo-blocks' ), get_the_title( $post ) ),
				array( 'post_author' => isset( $data['user_id'] ) ? (int) $data['user_id'] : 0 )
			);
			if ( ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_envo_blocks_review', (int) $comment_id );
				update_post_meta( $id, '_wp_attachment_image_alt', sprintf( /* translators: %s: product name */ __( 'Customer photo of %s', 'envo-blocks' ), get_the_title( $post ) ) );
				$ids[] = (int) $id;
			}
		}

		if ( $ids ) {
			update_comment_meta( $comment_id, self::META, $ids );
			delete_transient( 'envo_blocks_review_photos' );
		}
	}

	/**
	 * A new copy of an uploaded photo: turned upright, at most 2000 px, WebP when the server can write it. Saving it
	 * again drops the camera data (EXIF with the GPS position of a phone photo).
	 *
	 * @param string $tmp   Uploaded file.
	 * @param array  $check Result of wp_check_filetype_and_ext().
	 * @return array|null File array for media_handle_sideload() plus 'ext', or null when the image cannot be read.
	 */
	private static function clean_copy( $tmp, $check ) {
		$editor = wp_get_image_editor( $tmp );
		if ( is_wp_error( $editor ) ) {
			return null;
		}
		if ( method_exists( $editor, 'maybe_exif_rotate' ) ) {
			$editor->maybe_exif_rotate();
		}
		$size = $editor->get_size();
		if ( max( $size['width'], $size['height'] ) > 2000 ) {
			$editor->resize( 2000, 2000, false );
		}
		$mime = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ? 'image/webp' : $check['type'];
		$ext  = 'image/webp' === $mime ? 'webp' : $check['ext'];
		$editor->set_quality( 82 );
		$base = wp_tempnam( 'envo-review' );
		wp_delete_file( $base ); // Only the name is needed; the copy gets the extension of its type.
		$saved = $editor->save( $base . '.' . $ext, $mime );
		if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) {
			return null;
		}

		return array(
			'tmp_name' => $saved['path'],
			'type'     => $mime,
			'ext'      => $ext,
			'error'    => 0,
			'size'     => (int) filesize( $saved['path'] ),
		);
	}

	/**
	 * Attachment IDs of a review (only existing images).
	 *
	 * @param int $comment_id Comment.
	 * @return int[]
	 */
	public static function photos( $comment_id ) {
		$ids = get_comment_meta( $comment_id, self::META, true );

		return array_values( array_filter( array_map( 'absint', is_array( $ids ) ? $ids : array() ), 'wp_attachment_is_image' ) );
	}

	/**
	 * Photos under the text of an approved product review.
	 *
	 * @param string          $text    Comment text.
	 * @param WP_Comment|null $comment Comment.
	 * @return string
	 */
	public static function show( $text, $comment = null ) {
		if ( ! $comment instanceof WP_Comment || 'product' !== get_post_type( (int) $comment->comment_post_ID ) || ( '1' !== (string) $comment->comment_approved && ! is_admin() ) ) {
			return $text;
		}
		$ids = self::photos( (int) $comment->comment_ID );
		if ( ! $ids ) {
			return $text;
		}
		if ( ! is_admin() ) {
			wp_enqueue_style( 'envo-blocks-review-photos' );
			wp_enqueue_script( 'envo-blocks-review-photos' );
		}

		return $text . self::thumbs( $ids, is_admin() ? 'thumbnail' : 'woocommerce_gallery_thumbnail' );
	}

	/**
	 * Thumbnails that open the large photo (review-photos-view.js shows it in a window).
	 *
	 * @param int[]  $ids  Attachments.
	 * @param string $size Thumbnail size.
	 * @return string
	 */
	public static function thumbs( $ids, $size ) {
		$html = '<span class="sb-rp-thumbs">';
		foreach ( $ids as $index => $id ) {
			$full  = wp_get_attachment_image_url( $id, 'large' );
			$html .= '<a class="sb-rp-thumb" href="' . esc_url( $full ) . '" data-sb-rp-photo target="_blank" rel="noopener">'
				. wp_get_attachment_image(
					$id,
					$size,
					false,
					array(
						'loading' => 'lazy',
						/* translators: %d: number of the photo */
						'alt'     => sprintf( __( 'Customer photo %d', 'envo-blocks' ), $index + 1 ),
					)
				)
				. '</a>';
		}

		return $html . '</span>';
	}

	/**
	 * A deleted review takes its photos along.
	 *
	 * @param int $comment_id Comment.
	 */
	public static function delete( $comment_id ) {
		foreach ( self::photos( $comment_id ) as $id ) {
			if ( (int) get_post_meta( $id, '_envo_blocks_review', true ) === (int) $comment_id ) {
				wp_delete_attachment( $id, true );
			}
		}
		delete_transient( 'envo_blocks_review_photos' );
	}

	/**
	 * Script and styles of the photo field, the thumbnails and the photo window.
	 */
	public static function register_assets() {
		wp_register_style( 'envo-blocks-review-photos', ENVO_BLOCKS_URL . 'assets/css/review-photos.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/review-photos.css' ) );
		wp_register_script(
			'envo-blocks-review-photos',
			ENVO_BLOCKS_URL . 'assets/js/review-photos-view.js',
			array(),
			Envo_Blocks_Blocks::ver( 'assets/js/review-photos-view.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_localize_script(
			'envo-blocks-review-photos',
			'envoBlocksReviewPhotos',
			array(
				'close'    => __( 'Close', 'envo-blocks' ),
				'prev'     => __( 'Previous photo', 'envo-blocks' ),
				'next'     => __( 'Next photo', 'envo-blocks' ),
				/* translators: %d: number of photos */
				'tooMany'  => __( 'Only the first %d photos are added.', 'envo-blocks' ),
				/* translators: %d: size in MB */
				'tooLarge' => __( 'A photo is larger than %d MB and is left out.', 'envo-blocks' ),
			)
		);
	}

	/**
	 * The product page loads the styles early (the review list is printed after the head).
	 */
	public static function assets() {
		if ( is_singular( 'product' ) && self::settings()['enabled'] ) {
			wp_enqueue_style( 'envo-blocks-review-photos' );
			wp_enqueue_script( 'envo-blocks-review-photos' );
		}
	}

	/**
	 * Approved reviews with photos, newest first.
	 *
	 * @param int $product_id Product, 0 = all products.
	 * @param int $limit      Most photos.
	 * @return array[] array( id, comment ) per photo.
	 */
	public static function gallery( $product_id, $limit ) {
		$args = array(
			'status'    => 'approve',
			'post_type' => 'product',
			'meta_key'  => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Only reviews with photos.
			'number'    => max( 10, $limit ),
			'orderby'   => 'comment_date_gmt',
			'order'     => 'DESC',
		);
		if ( $product_id ) {
			$args['post_id'] = $product_id;
		}
		$out = array();
		foreach ( get_comments( $args ) as $comment ) {
			if ( 'publish' !== get_post_status( (int) $comment->comment_post_ID ) ) {
				continue;
			}
			foreach ( self::photos( (int) $comment->comment_ID ) as $id ) {
				$out[] = array(
					'id'      => $id,
					'comment' => $comment,
				);
				if ( count( $out ) >= $limit ) {
					return $out;
				}
			}
		}

		return $out;
	}
}
