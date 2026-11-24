<?php
/**
 * Customer Photos: the photos of approved reviews as a gallery; a click opens the photo with the review.
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_source = Envo_Blocks_Blocks::choice( $attributes, 'source', array( 'current', 'product', 'store' ) );
$envo_blocks_id     = 0;

if ( 'product' === $envo_blocks_source ) {
	$envo_blocks_id = Envo_Blocks_Blocks::int( $attributes, 'productId', 0, 0, PHP_INT_MAX );
} elseif ( 'current' === $envo_blocks_source ) {
	// A product template, or the Single Product block (which passes only postId).
	if ( ! empty( $block->context['postId'] ) && 'product' === get_post_type( (int) $block->context['postId'] ) ) {
		$envo_blocks_id = (int) $block->context['postId'];
	} elseif ( is_singular( 'product' ) ) {
		$envo_blocks_id = get_queried_object_id();
	}
}

if ( 'store' !== $envo_blocks_source && ! $envo_blocks_id ) {
	if ( Envo_Blocks_Blocks::is_preview() ) {
		$envo_blocks_id = 0; // The editor of a template: the photos of the whole shop as a sample.
	} else {
		echo wp_kses_post( Envo_Blocks_Blocks::notice( 'product' === $envo_blocks_source ? __( 'Choose a product in the block settings.', 'envo-blocks' ) : __( 'The photos of the current product are shown here on the product page.', 'envo-blocks' ) ) );
		return;
	}
}

$envo_blocks_limit   = Envo_Blocks_Blocks::int( $attributes, 'limit', 8, 1, 48 );
$envo_blocks_photos  = Envo_Blocks_Review_Photos::gallery( $envo_blocks_id, $envo_blocks_limit );
$envo_blocks_heading = Envo_Blocks_Blocks::text( $attributes, 'heading' );

if ( ! $envo_blocks_photos ) {
	if ( ! empty( $attributes['hideEmpty'] ) && ! Envo_Blocks_Blocks::is_preview() ) {
		return;
	}
	$envo_blocks_empty = Envo_Blocks_Blocks::text( $attributes, 'emptyText', __( 'No customer photos yet. Add one with your review!', 'envo-blocks' ) );
}

$envo_blocks_show_rating  = ! isset( $attributes['showRating'] ) || $attributes['showRating'];
$envo_blocks_show_product = 'store' === $envo_blocks_source && ( ! isset( $attributes['showProduct'] ) || $attributes['showProduct'] );
$envo_blocks_aspect       = Envo_Blocks_Blocks::choice( $attributes, 'aspect', array( 'square', 'portrait', 'landscape', 'natural' ) );

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-rp sb-rp--' . $envo_blocks_aspect,
		'style' => '--sb-rp-cols:' . Envo_Blocks_Blocks::int( $attributes, 'columns', 4, 1, 8 ) . ';--sb-rp-radius:' . Envo_Blocks_Blocks::int( $attributes, 'radius', 10, 0, 40 ) . 'px;' . Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'starColor'  => '--sb-star',
				'textColor'  => '--sb-rp-text',
				'background' => '--sb-rp-bg',
			)
		),
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<?php if ( '' !== $envo_blocks_heading ) : ?>
		<p class="sb-rp__heading"><?php echo esc_html( $envo_blocks_heading ); ?></p>
	<?php endif; ?>
	<?php if ( ! $envo_blocks_photos ) : ?>
		<p class="sb-rp__empty"><?php echo esc_html( $envo_blocks_empty ); ?></p>
	<?php else : ?>
		<ul class="sb-rp__grid">
			<?php
			foreach ( $envo_blocks_photos as $envo_blocks_photo ) :
				$envo_blocks_comment = $envo_blocks_photo['comment'];
				$envo_blocks_rating  = (int) get_comment_meta( (int) $envo_blocks_comment->comment_ID, 'rating', true );
				$envo_blocks_text    = wp_trim_words( wp_strip_all_tags( (string) $envo_blocks_comment->comment_content ), 60 );
				$envo_blocks_verified = function_exists( 'wc_review_is_from_verified_owner' ) && wc_review_is_from_verified_owner( (int) $envo_blocks_comment->comment_ID );
				$envo_blocks_info    = array(
					'author'   => get_comment_author( $envo_blocks_comment ),
					'date'     => get_comment_date( '', $envo_blocks_comment ),
					'text'     => $envo_blocks_text,
					'rating'   => $envo_blocks_show_rating ? $envo_blocks_rating : 0,
					'verified' => $envo_blocks_verified ? __( 'Verified owner', 'envo-blocks' ) : '',
					'product'  => $envo_blocks_show_product ? get_the_title( (int) $envo_blocks_comment->comment_post_ID ) : '',
					'url'      => $envo_blocks_show_product ? get_permalink( (int) $envo_blocks_comment->comment_post_ID ) : '',
				);
				?>
				<li class="sb-rp__item">
					<a class="sb-rp__photo" href="<?php echo esc_url( (string) wp_get_attachment_image_url( $envo_blocks_photo['id'], 'large' ) ); ?>" data-sb-rp-photo data-sb-rp-info="<?php echo esc_attr( wp_json_encode( $envo_blocks_info ) ); ?>">
						<?php
						echo wp_get_attachment_image(
							$envo_blocks_photo['id'],
							'woocommerce_thumbnail',
							false,
							array(
								'loading' => 'lazy',
								/* translators: %s: name of the customer */
								'alt'     => sprintf( __( 'Photo from the review of %s', 'envo-blocks' ), get_comment_author( $envo_blocks_comment ) ),
							)
						);
						?>
						<?php if ( $envo_blocks_show_rating && $envo_blocks_rating ) : ?>
							<span class="sb-rp__badge"><?php echo wp_kses( Envo_Blocks_Blocks::stars( $envo_blocks_rating ), Envo_Blocks_Blocks::allowed_html() ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
