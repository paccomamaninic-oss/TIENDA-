<?php
/**
 * Product Tab: one tab of the Product Tabs block. It prints nothing itself: its data (and its own content, already
 * rendered) waits for the parent block, which renders after its children.
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

Envo_Blocks_Product_Tabs::collect( $attributes, $content );
