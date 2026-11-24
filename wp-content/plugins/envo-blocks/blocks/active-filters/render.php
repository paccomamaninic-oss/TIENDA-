<?php
/**
 * Active Filters block (markup in Envo_Blocks_Filter_Blocks::render_active()).
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

echo Envo_Blocks_Filter_Blocks::render_active( is_array( $attributes ) ? $attributes : array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped while built.
