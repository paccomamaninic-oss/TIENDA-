<?php
/**
 * Product Filter block (markup in Envo_Blocks_Filter_Blocks::render_filter()).
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

echo Envo_Blocks_Filter_Blocks::render_filter( is_array( $attributes ) ? $attributes : array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped while built.
