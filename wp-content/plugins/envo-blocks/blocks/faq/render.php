<?php
/**
 * FAQ: questions with answers that open and close (see Envo_Blocks_FAQ).
 *
 * Every question is a <details> element, so it opens without JavaScript; with "One open at a time" the items share a
 * name (the browser closes the others, faq-view.js does it in older browsers and opens a question linked by #anchor).
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (empty: the FAQ Item blocks leave their data in collect()).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

// The children rendered first; take their data even when nothing is shown.
$envo_blocks_items = array_values(
	array_filter(
		Envo_Blocks_FAQ::take( count( $block->inner_blocks ) ),
		static function ( $item ) {
			return '' !== trim( wp_strip_all_tags( $item['question'] ) );
		}
	)
);
if ( ! $envo_blocks_items ) {
	return;
}

$envo_blocks_style    = Envo_Blocks_Blocks::choice( $attributes, 'itemStyle', array( 'cards', 'boxed', 'lines', 'minimal' ) );
$envo_blocks_icon     = Envo_Blocks_Blocks::choice( $attributes, 'icon', array( 'plus', 'chevron', 'none' ) );
$envo_blocks_icon_pos = Envo_Blocks_Blocks::choice( $attributes, 'iconPosition', array( 'right', 'left' ) );
$envo_blocks_tag      = Envo_Blocks_Blocks::choice( $attributes, 'questionTag', array( 'h3', 'h2', 'h4', 'p' ) );
$envo_blocks_multiple = ! empty( $attributes['allowMultiple'] );
$envo_blocks_first    = ! empty( $attributes['firstOpen'] );

if ( ! isset( $attributes['schema'] ) || ! empty( $attributes['schema'] ) ) {
	Envo_Blocks_FAQ::add_schema( $envo_blocks_items );
}

$envo_blocks_style_attr = Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'questionColor'      => '--sb-faq-q',
		'questionBackground' => '--sb-faq-q-bg',
		'openBackground'     => '--sb-faq-open-bg',
		'answerColor'        => '--sb-faq-a',
		'iconColor'          => '--sb-faq-icon',
		'borderColor'        => '--sb-faq-border',
	)
);
$envo_blocks_style_attr .= '--sb-faq-gap:' . Envo_Blocks_Blocks::int( $attributes, 'gap', 12, 0, 60 ) . 'px;';
$envo_blocks_style_attr .= '--sb-faq-pad:' . Envo_Blocks_Blocks::int( $attributes, 'padding', 20, 0, 60 ) . 'px;';
$envo_blocks_style_attr .= '--sb-faq-radius:' . Envo_Blocks_Blocks::int( $attributes, 'radius', 10, 0, 40 ) . 'px;';

// Items of one block share a name: one open at a time.
$envo_blocks_name = 'sb-faq-' . Envo_Blocks_Blocks::instance( 'faq' );

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'sb-faq sb-faq--' . $envo_blocks_style . ' sb-faq--icon-' . $envo_blocks_icon . ' sb-faq--icon-' . $envo_blocks_icon_pos,
		'style' => $envo_blocks_style_attr,
	)
);
?>
<div <?php echo $envo_blocks_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes(). ?>>
	<?php foreach ( $envo_blocks_items as $envo_blocks_i => $envo_blocks_item ) : ?>
		<?php
		$envo_blocks_id   = Envo_Blocks_FAQ::anchor( $envo_blocks_item['question'] );
		$envo_blocks_open = $envo_blocks_item['open'] || ( 0 === $envo_blocks_i && $envo_blocks_first );
		?>
		<details class="sb-faq__item" id="<?php echo esc_attr( $envo_blocks_id ); ?>"<?php echo $envo_blocks_multiple ? '' : ' name="' . esc_attr( $envo_blocks_name ) . '"'; ?><?php echo $envo_blocks_open ? ' open' : ''; ?>>
			<summary class="sb-faq__question">
				<<?php echo esc_html( $envo_blocks_tag ); ?> class="sb-faq__title"><?php echo wp_kses( $envo_blocks_item['question'], array( 'strong' => array(), 'em' => array(), 'b' => array(), 'i' => array(), 'code' => array(), 'br' => array() ) ); ?></<?php echo esc_html( $envo_blocks_tag ); ?>>
				<?php if ( 'none' !== $envo_blocks_icon ) : ?>
					<span class="sb-faq__icon" aria-hidden="true"></span>
				<?php endif; ?>
			</summary>
			<div class="sb-faq__answer">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks, escaped where they are made.
				echo $envo_blocks_item['answer'];
				?>
			</div>
		</details>
	<?php endforeach; ?>
</div>
