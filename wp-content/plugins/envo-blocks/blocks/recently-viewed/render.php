<?php
/**
 * Recently Viewed Products: an empty list that recently-viewed-view.js fills from the Store API
 * with the products remembered in the browser. The editor preview shows the newest products instead.
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_limit   = Envo_Blocks_Blocks::int( $attributes, 'limit', 4, 1, 12 );
$envo_blocks_columns = Envo_Blocks_Blocks::int( $attributes, 'columns', 4, 1, 6 );
$envo_blocks_mobile  = Envo_Blocks_Blocks::int( $attributes, 'columnsMobile', 2, 1, 3 );
$envo_blocks_ratio   = Envo_Blocks_Blocks::choice( $attributes, 'imageRatio', array( '1', '3/4', '4/3', '2/3', 'auto' ) );
$envo_blocks_heading = ! isset( $attributes['showHeading'] ) || $attributes['showHeading'] ? Envo_Blocks_Blocks::text( $attributes, 'heading', __( 'Recently viewed', 'envo-blocks' ) ) : '';
$envo_blocks_preview = Envo_Blocks_Blocks::is_preview();

$envo_blocks_current = 0;
if ( ! empty( $attributes['excludeCurrent'] ) ) {
	if ( isset( $block->context['postId'], $block->context['postType'] ) && 'product' === $block->context['postType'] ) {
		$envo_blocks_current = (int) $block->context['postId'];
	} elseif ( is_singular( 'product' ) ) {
		$envo_blocks_current = get_queried_object_id();
	}
}

$envo_blocks_config = array(
	'restUrl'    => esc_url_raw( rest_url( 'wc/store/v1/products' ) ),
	'limit'      => $envo_blocks_limit,
	'exclude'    => $envo_blocks_current,
	'showPrice'  => ! isset( $attributes['showPrice'] ) || $attributes['showPrice'],
	'showRating' => ! empty( $attributes['showRating'] ),
	'showButton' => ! empty( $attributes['showButton'] ),
	'emptyText'  => Envo_Blocks_Blocks::text( $attributes, 'emptyText' ),
	/* translators: %s: average rating */
	'ratedText'  => __( 'Rated %s out of 5', 'envo-blocks' ),
);

$envo_blocks_items = '';
if ( $envo_blocks_preview ) {
	foreach ( wc_get_products( array( 'limit' => $envo_blocks_limit, 'status' => 'publish', 'visibility' => 'catalog', 'orderby' => 'date', 'order' => 'DESC' ) ) as $envo_blocks_product ) {
		$envo_blocks_image  = wp_get_attachment_image_url( $envo_blocks_product->get_image_id(), 'woocommerce_thumbnail' );
		$envo_blocks_items .= '<li class="sb-recent__item"><a class="sb-recent__link" href="#"><span class="sb-recent__image">'
			. ( $envo_blocks_image ? '<img src="' . esc_url( $envo_blocks_image ) . '" alt="" loading="lazy">' : '' )
			. '</span><span class="sb-recent__name">' . esc_html( $envo_blocks_product->get_name() ) . '</span></a>'
			. ( ! empty( $attributes['showRating'] ) && $envo_blocks_product->get_average_rating() > 0 ? Envo_Blocks_Blocks::stars( (float) $envo_blocks_product->get_average_rating() ) : '' )
			. ( $envo_blocks_config['showPrice'] ? '<span class="sb-recent__price">' . wp_kses_post( $envo_blocks_product->get_price_html() ) . '</span>' : '' )
			. ( $envo_blocks_config['showButton'] ? '<span class="sb-recent__button wp-element-button">' . esc_html( $envo_blocks_product->add_to_cart_text() ) . '</span>' : '' )
			. '</li>';
	}

	if ( '' === $envo_blocks_items ) {
		echo wp_kses_post( Envo_Blocks_Blocks::notice( __( 'There are no products yet. Visitors will see the products they viewed last.', 'envo-blocks' ) ) );
		return;
	}
}

$envo_blocks_classes = 'sb-recent' . ( $envo_blocks_preview ? ' is-ready' : '' );

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => $envo_blocks_classes,
		'style' => '--sb-cols:' . $envo_blocks_columns . ';--sb-cols-m:' . $envo_blocks_mobile . ';--sb-recent-ratio:' . ( 'auto' === $envo_blocks_ratio ? 'auto' : $envo_blocks_ratio ) . ';' . Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'textColor'      => '--sb-recent-text',
				'priceColor'     => '--sb-recent-price',
				'cardBackground' => '--sb-recent-card',
			)
		),
	)
);
?>
<section <?php echo wp_kses_data( $envo_blocks_wrapper ); ?> data-config="<?php echo esc_attr( wp_json_encode( $envo_blocks_config ) ); ?>">
	<?php if ( '' !== $envo_blocks_heading || ! empty( $attributes['showClear'] ) ) : ?>
		<div class="sb-recent__head">
			<?php if ( '' !== $envo_blocks_heading ) : ?>
				<h2 class="sb-recent__heading"><?php echo esc_html( $envo_blocks_heading ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $attributes['showClear'] ) ) : ?>
				<button type="button" class="sb-recent__clear" data-sb-clear><?php esc_html_e( 'Clear history', 'envo-blocks' ); ?></button>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<ul class="sb-recent__list"><?php echo wp_kses( $envo_blocks_items, Envo_Blocks_Blocks::allowed_html() ); ?></ul>
	<p class="sb-recent__empty" hidden></p>
</section>
