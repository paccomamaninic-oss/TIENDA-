<?php
/**
 * A distraction-free store layout in a classic theme (the checkout): the page without the theme header and footer,
 * only the layout (Envo_Blocks_Layouts::is_bare()). The head and the footer scripts stay, so WooCommerce, payment
 * plugins and the theme styles work as on any page.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

$envo_blocks_layout_html = Envo_Blocks_Layouts::render_classic();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'envo-layout-bare' ); ?>>
<?php wp_body_open(); ?>
<?php
// The block markup is rendered by do_blocks() from a layout the site's editors made.
echo $envo_blocks_layout_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<?php wp_footer(); ?>
</body>
</html>
