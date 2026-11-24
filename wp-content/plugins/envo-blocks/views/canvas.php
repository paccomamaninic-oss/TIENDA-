<?php
/**
 * "Envo Blocks Canvas" for classic themes: the page content without the header, footer and sidebar of the theme.
 * The styles and scripts of the theme and the plugins still load (wp_head / wp_footer).
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'envo-blocks-canvas' ); ?>>
<?php wp_body_open(); ?>
<main id="content" class="envo-blocks-canvas__main">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>
<?php wp_footer(); ?>
</body>
</html>
