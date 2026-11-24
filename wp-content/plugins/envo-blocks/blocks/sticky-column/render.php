<?php
/**
 * Sticky Column: the inner blocks stay in view while the page scrolls, inside their parent (a column, a group).
 *
 * CSS position: sticky does the work, so it stays smooth and needs no JavaScript for content shorter than the screen.
 * sticky-view.js only finds the height of a sticky header ("auto" offset) and lets content taller than the screen float:
 * it sticks at its bottom while scrolling down and at its top while scrolling up, so every part of it can be seen.
 *
 * @package EnvoBlocks
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Inner blocks.
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_auto  = 'custom' !== ( isset( $attributes['offsetMode'] ) ? $attributes['offsetMode'] : 'auto' );
$envo_blocks_until = Envo_Blocks_Blocks::choice( $attributes, 'until', array( 'mobile', 'tablet', 'never' ) );

$envo_blocks_wrapper = get_block_wrapper_attributes(
	array(
		'class'          => 'sb-sticky sb-sticky--until-' . $envo_blocks_until,
		'style'          => '--sb-sticky-top:' . Envo_Blocks_Blocks::int( $attributes, 'offset', 24, 0, 400 ) . 'px;--sb-sticky-bottom:' . Envo_Blocks_Blocks::int( $attributes, 'bottomOffset', 24, 0, 400 ) . 'px;',
		'data-sb-sticky' => $envo_blocks_auto ? 'auto' : 'custom',
	)
);
?>
<div <?php echo wp_kses_data( $envo_blocks_wrapper ); ?>>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner blocks. ?>
</div>
