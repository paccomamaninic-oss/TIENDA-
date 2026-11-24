<?php
/**
 * Product Tabs: the tabs of the product page (see Envo_Blocks_Product_Tabs).
 *
 * Tabs on top, on the left or on the right, or an accordion; on phones the tabs can turn into an accordion. Panels
 * other than the open one get the hidden attribute already here, product-tabs-view.js switches them.
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (empty: the Product Tab blocks leave their data in collect()).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

// The children rendered first; take their data even when nothing is shown.
$envo_blocks_children = Envo_Blocks_Product_Tabs::take( count( $block->inner_blocks ) );

$envo_blocks_source = Envo_Blocks_Blocks::choice( $attributes, 'source', array( 'current', 'product' ) );
if ( 'product' === $envo_blocks_source ) {
	$envo_blocks_id = Envo_Blocks_Blocks::int( $attributes, 'productId', 0, 0, PHP_INT_MAX );
} elseif ( ! empty( $block->context['postId'] ) && 'product' === get_post_type( (int) $block->context['postId'] ) ) {
	$envo_blocks_id = (int) $block->context['postId'];
} else {
	$envo_blocks_id = is_singular( 'product' ) ? get_queried_object_id() : 0;
}
$envo_blocks_product = $envo_blocks_id ? wc_get_product( $envo_blocks_id ) : null;

if ( ! $envo_blocks_product ) {
	echo wp_kses_post( Envo_Blocks_Blocks::notice( 'product' === $envo_blocks_source ? __( 'Choose a product in the block settings.', 'envo-blocks' ) : __( 'The tabs of the current product are shown here on the product page.', 'envo-blocks' ) ) );
	return;
}

$envo_blocks_tabs = Envo_Blocks_Product_Tabs::build(
	$envo_blocks_product,
	$envo_blocks_children,
	array( 'headings' => ! empty( $attributes['showHeadings'] ) )
);
if ( ! $envo_blocks_tabs ) {
	return;
}

$envo_blocks_layout    = Envo_Blocks_Blocks::choice( $attributes, 'layout', array( 'tabs', 'accordion' ) );
$envo_blocks_position  = Envo_Blocks_Blocks::choice( $attributes, 'navPosition', array( 'top', 'left', 'right' ) );
$envo_blocks_style     = Envo_Blocks_Blocks::choice( $attributes, 'tabStyle', array( 'underline', 'boxed', 'pills', 'minimal' ) );
$envo_blocks_align     = Envo_Blocks_Blocks::choice( $attributes, 'navAlign', array( 'left', 'center', 'right', 'stretch' ) );
$envo_blocks_mobile    = ! isset( $attributes['mobileAccordion'] ) || ! empty( $attributes['mobileAccordion'] );
$envo_blocks_first     = ! isset( $attributes['firstOpen'] ) || ! empty( $attributes['firstOpen'] );
$envo_blocks_multiple  = ! empty( $attributes['allowMultiple'] );
$envo_blocks_accordion = 'accordion' === $envo_blocks_layout;
$envo_blocks_open      = $envo_blocks_accordion && ! $envo_blocks_first ? -1 : 0;

$envo_blocks_style_attr = Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'tabColor'            => '--sb-pt-color',
		'tabActiveColor'      => '--sb-pt-active',
		'accentColor'         => '--sb-pt-accent',
		'tabBackground'       => '--sb-pt-bg',
		'tabActiveBackground' => '--sb-pt-active-bg',
		'borderColor'         => '--sb-pt-border',
		'panelBackground'     => '--sb-pt-panel-bg',
		'panelText'           => '--sb-pt-panel-text',
	)
);
$envo_blocks_style_attr .= '--sb-pt-nav-w:' . Envo_Blocks_Blocks::int( $attributes, 'navWidth', 220, 120, 480 ) . 'px;';
$envo_blocks_style_attr .= '--sb-pt-pad:' . Envo_Blocks_Blocks::int( $attributes, 'panelPadding', 24, 0, 80 ) . 'px;';
$envo_blocks_style_attr .= '--sb-pt-radius:' . Envo_Blocks_Blocks::int( $attributes, 'radius', 6, 0, 40 ) . 'px;';

// woocommerce: the styles of WooCommerce for its tab content (reviews, stars, attributes table) on any page.
$envo_blocks_classes = array(
	'woocommerce',
	'sb-ptabs',
	'sb-ptabs--' . $envo_blocks_layout,
	'sb-ptabs--' . $envo_blocks_style,
);
if ( ! $envo_blocks_accordion ) {
	$envo_blocks_classes[] = 'sb-ptabs--nav-' . $envo_blocks_position;
	$envo_blocks_classes[] = 'sb-ptabs--align-' . $envo_blocks_align;
	if ( $envo_blocks_mobile ) {
		$envo_blocks_classes[] = 'sb-ptabs--m-accordion';
	}
}

// IDs: tab-reviews etc. like WooCommerce for the first block on the page (links to #tab-reviews keep working).
$envo_blocks_n      = Envo_Blocks_Blocks::instance( 'product-tabs' );
$envo_blocks_prefix = 1 === $envo_blocks_n ? 'tab-' : 'tab' . $envo_blocks_n . '-';

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class'          => implode( ' ', $envo_blocks_classes ),
		'style'          => $envo_blocks_style_attr,
		'data-multiple'  => $envo_blocks_multiple ? '1' : '0',
		'data-collapse'  => $envo_blocks_accordion && ! $envo_blocks_first ? '1' : '0',
	)
);
?>
<div <?php echo $envo_blocks_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes(). ?>>
	<?php if ( ! $envo_blocks_accordion ) : ?>
		<div class="sb-ptabs__nav" role="tablist" aria-orientation="<?php echo 'top' === $envo_blocks_position ? 'horizontal' : 'vertical'; ?>">
			<?php foreach ( $envo_blocks_tabs as $envo_blocks_i => $envo_blocks_tab ) : ?>
				<?php $envo_blocks_tab_id = $envo_blocks_prefix . $envo_blocks_tab['key']; ?>
				<button type="button" role="tab" class="sb-ptabs__tab<?php echo $envo_blocks_open === $envo_blocks_i ? ' is-active' : ''; ?>" id="<?php echo esc_attr( $envo_blocks_tab_id . '-title' ); ?>" aria-controls="<?php echo esc_attr( $envo_blocks_tab_id ); ?>" aria-selected="<?php echo $envo_blocks_open === $envo_blocks_i ? 'true' : 'false'; ?>" tabindex="<?php echo $envo_blocks_open === $envo_blocks_i ? '0' : '-1'; ?>">
					<?php echo wp_kses( Envo_Blocks_Blocks::icon( $envo_blocks_tab['icon'], 'sb-ptabs__icon' ), Envo_Blocks_Blocks::allowed_html() ); ?>
					<span><?php echo esc_html( $envo_blocks_tab['title'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<div class="sb-ptabs__panels">
		<?php foreach ( $envo_blocks_tabs as $envo_blocks_i => $envo_blocks_tab ) : ?>
			<?php
			$envo_blocks_tab_id = $envo_blocks_prefix . $envo_blocks_tab['key'];
			$envo_blocks_is_open = $envo_blocks_open === $envo_blocks_i;
			?>
			<div class="sb-ptabs__item sb-ptabs__item--<?php echo esc_attr( $envo_blocks_tab['key'] ); ?><?php echo $envo_blocks_is_open ? ' is-open' : ''; ?>">
				<h3 class="sb-ptabs__heading">
					<button type="button" class="sb-ptabs__toggle" id="<?php echo esc_attr( $envo_blocks_tab_id . '-toggle' ); ?>" aria-controls="<?php echo esc_attr( $envo_blocks_tab_id ); ?>" aria-expanded="<?php echo $envo_blocks_is_open ? 'true' : 'false'; ?>">
						<?php echo wp_kses( Envo_Blocks_Blocks::icon( $envo_blocks_tab['icon'], 'sb-ptabs__icon' ), Envo_Blocks_Blocks::allowed_html() ); ?>
						<span><?php echo esc_html( $envo_blocks_tab['title'] ); ?></span>
						<span class="sb-ptabs__chevron" aria-hidden="true"></span>
					</button>
				</h3>
				<div class="sb-ptabs__panel woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $envo_blocks_tab['key'] ); ?>" id="<?php echo esc_attr( $envo_blocks_tab_id ); ?>" role="<?php echo $envo_blocks_accordion ? 'region' : 'tabpanel'; ?>" aria-labelledby="<?php echo esc_attr( $envo_blocks_tab_id . ( $envo_blocks_accordion ? '-toggle' : '-title' ) ); ?>" tabindex="0"<?php echo $envo_blocks_is_open ? '' : ' hidden'; ?>>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output of WooCommerce tab templates and of rendered blocks, escaped where it is made.
					echo $envo_blocks_tab['html'];
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
