<?php
/**
 * Rating Summary: average rating and the breakdown of reviews by stars.
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_source  = Envo_Blocks_Blocks::choice( $attributes, 'source', array( 'current', 'product', 'store' ) );
$envo_blocks_counts  = array();
$envo_blocks_product = null;
$envo_blocks_link    = '';

if ( 'store' === $envo_blocks_source ) {
	$envo_blocks_counts = get_transient( 'envo_blocks_rating_counts' );

	if ( ! is_array( $envo_blocks_counts ) ) {
		global $wpdb;

		// Approved reviews of published products, grouped by rating.
		$envo_blocks_rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cached in a transient below, cleared when reviews change.
			"SELECT m.meta_value AS rating, COUNT(*) AS total
			FROM {$wpdb->comments} c
			INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID AND m.meta_key = 'rating'
			INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID AND p.post_type = 'product' AND p.post_status = 'publish'
			WHERE c.comment_approved = '1' AND m.meta_value BETWEEN 1 AND 5
			GROUP BY m.meta_value"
		);

		$envo_blocks_counts = array();
		foreach ( (array) $envo_blocks_rows as $envo_blocks_row ) {
			$envo_blocks_counts[ (int) $envo_blocks_row->rating ] = (int) $envo_blocks_row->total;
		}

		set_transient( 'envo_blocks_rating_counts', $envo_blocks_counts, HOUR_IN_SECONDS );
	}
} else {
	if ( 'product' === $envo_blocks_source ) {
		$envo_blocks_id = Envo_Blocks_Blocks::int( $attributes, 'productId', 0, 0, PHP_INT_MAX );
	} elseif ( isset( $block->context['postId'], $block->context['postType'] ) && 'product' === $block->context['postType'] ) {
		$envo_blocks_id = (int) $block->context['postId'];
	} else {
		$envo_blocks_id = is_singular( 'product' ) ? get_queried_object_id() : 0;
	}

	$envo_blocks_product = $envo_blocks_id ? wc_get_product( $envo_blocks_id ) : null;

	if ( ! $envo_blocks_product ) {
		echo wp_kses_post( Envo_Blocks_Blocks::notice( 'product' === $envo_blocks_source ? __( 'Choose a product in the block settings.', 'envo-blocks' ) : __( 'The rating of the current product is shown here on the product page.', 'envo-blocks' ) ) );
		return;
	}

	$envo_blocks_counts = array_map( 'intval', (array) $envo_blocks_product->get_rating_counts() );
	$envo_blocks_link   = 'product' === $envo_blocks_source ? get_permalink( $envo_blocks_product->get_id() ) . '#reviews' : '#reviews';
}

$envo_blocks_total = 0;
$envo_blocks_sum   = 0;
for ( $envo_blocks_star = 1; $envo_blocks_star <= 5; $envo_blocks_star++ ) {
	$envo_blocks_counts[ $envo_blocks_star ] = isset( $envo_blocks_counts[ $envo_blocks_star ] ) ? (int) $envo_blocks_counts[ $envo_blocks_star ] : 0;
	$envo_blocks_total             += $envo_blocks_counts[ $envo_blocks_star ];
	$envo_blocks_sum               += $envo_blocks_star * $envo_blocks_counts[ $envo_blocks_star ];
}

if ( ! $envo_blocks_total && ! empty( $attributes['hideEmpty'] ) && ! Envo_Blocks_Blocks::is_preview() ) {
	return;
}

$envo_blocks_average = $envo_blocks_total ? $envo_blocks_sum / $envo_blocks_total : 0;
$envo_blocks_heading = Envo_Blocks_Blocks::text( $attributes, 'heading' );
$envo_blocks_layout  = Envo_Blocks_Blocks::choice( $attributes, 'layout', array( 'side', 'stacked' ) );
$envo_blocks_value   = Envo_Blocks_Blocks::choice( $attributes, 'barValue', array( 'count', 'percent', 'none' ) );
$envo_blocks_show    = static function ( $key ) use ( $attributes ) {
	return ! isset( $attributes[ $key ] ) || $attributes[ $key ];
};

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-rating sb-rating--' . $envo_blocks_layout,
		'style' => '--sb-rating-bar-h:' . Envo_Blocks_Blocks::int( $attributes, 'barHeight', 8, 2, 30 ) . 'px;' . Envo_Blocks_Blocks::color_vars(
			$attributes,
			array(
				'starColor'  => '--sb-star',
				'barColor'   => '--sb-rating-bar',
				'trackColor' => '--sb-rating-track',
				'textColor'  => '--sb-rating-text',
				'background' => '--sb-rating-bg',
			)
		),
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<?php if ( '' !== $envo_blocks_heading ) : ?>
		<p class="sb-rating__heading"><?php echo esc_html( $envo_blocks_heading ); ?></p>
	<?php endif; ?>
	<div class="sb-rating__inner">
		<?php if ( $envo_blocks_show( 'showAverage' ) || $envo_blocks_show( 'showCount' ) || ( $envo_blocks_show( 'showLink' ) && $envo_blocks_link ) ) : ?>
			<div class="sb-rating__summary">
				<?php if ( $envo_blocks_show( 'showAverage' ) ) : ?>
					<span class="sb-rating__average"><?php echo esc_html( number_format_i18n( $envo_blocks_average, 1 ) ); ?></span>
					<?php echo wp_kses( Envo_Blocks_Blocks::stars( $envo_blocks_average ), Envo_Blocks_Blocks::allowed_html() ); ?>
				<?php endif; ?>
				<?php if ( $envo_blocks_show( 'showCount' ) ) : ?>
					<span class="sb-rating__count">
						<?php
						echo esc_html(
							$envo_blocks_total
								/* translators: %s: number of reviews */
								? sprintf( _n( 'Based on %s review', 'Based on %s reviews', $envo_blocks_total, 'envo-blocks' ), number_format_i18n( $envo_blocks_total ) )
								: __( 'No reviews yet', 'envo-blocks' )
						);
						?>
					</span>
				<?php endif; ?>
				<?php if ( $envo_blocks_show( 'showLink' ) && $envo_blocks_link ) : ?>
					<a class="sb-rating__link" href="<?php echo esc_url( $envo_blocks_link ); ?>"><?php echo esc_html( Envo_Blocks_Blocks::text( $attributes, 'linkText', $envo_blocks_total ? __( 'Read the reviews', 'envo-blocks' ) : __( 'Write a review', 'envo-blocks' ) ) ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( $envo_blocks_show( 'showBars' ) ) : ?>
			<ul class="sb-rating__bars">
				<?php
				for ( $envo_blocks_star = 5; $envo_blocks_star >= 1; $envo_blocks_star-- ) :
					$envo_blocks_percent = $envo_blocks_total ? round( $envo_blocks_counts[ $envo_blocks_star ] / $envo_blocks_total * 100 ) : 0;
					/* translators: 1: number of stars, 2: number of reviews */
					$envo_blocks_label = sprintf( _n( '%1$d star: %2$s review', '%1$d stars: %2$s reviews', $envo_blocks_counts[ $envo_blocks_star ], 'envo-blocks' ), $envo_blocks_star, number_format_i18n( $envo_blocks_counts[ $envo_blocks_star ] ) );
					?>
					<li class="sb-rating__row" aria-label="<?php echo esc_attr( $envo_blocks_label ); ?>">
						<span class="sb-rating__star" aria-hidden="true"><?php echo esc_html( $envo_blocks_star ); ?> ★</span>
						<span class="sb-rating__bar" aria-hidden="true"><span style="width:<?php echo esc_attr( $envo_blocks_percent ); ?>%"></span></span>
						<?php if ( 'none' !== $envo_blocks_value ) : ?>
							<span class="sb-rating__value" aria-hidden="true"><?php echo esc_html( 'percent' === $envo_blocks_value ? $envo_blocks_percent . ' %' : number_format_i18n( $envo_blocks_counts[ $envo_blocks_star ] ) ); ?></span>
						<?php endif; ?>
					</li>
				<?php endfor; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>
