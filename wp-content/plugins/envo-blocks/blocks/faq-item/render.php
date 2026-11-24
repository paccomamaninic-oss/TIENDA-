<?php
/**
 * FAQ Item: one question of the FAQ block. It prints nothing itself: its question and its answer (already rendered)
 * wait for the FAQ block, which renders after its children.
 *
 * @package EnvoBlocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (the answer).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

Envo_Blocks_FAQ::collect( $attributes, $content );
