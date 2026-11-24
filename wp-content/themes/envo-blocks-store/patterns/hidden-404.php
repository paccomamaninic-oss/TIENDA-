<?php
/**
 * Title: 404 content
 * Slug: envo-blocks-store/hidden-404
 * Inserter: no
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:heading {"textAlign":"center","level":1,"textColor":"primary"} -->
<h1 class="wp-block-heading has-text-align-center has-primary-color has-text-color"><?php echo esc_html_x( '404', 'Error code for a page that was not found', 'envo-blocks-store' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php esc_html_e( 'The page you are looking for could not be found. Try searching instead.', 'envo-blocks-store' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:pattern {"slug":"envo-blocks-store/hidden-search"} /-->
