<?php
/**
 * A store layout in a classic theme: the theme header and footer, the layout between them (Envo_Blocks_Layouts).
 * The layout is rendered while the page styles are enqueued (Envo_Blocks_Layouts::prerender_classic()), so the
 * styles of its blocks are in the head.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$envo_blocks_layout_html = Envo_Blocks_Layouts::render_classic();

// The block markup is rendered by do_blocks() from a layout the site's editors made.
echo $envo_blocks_layout_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer( 'shop' );
