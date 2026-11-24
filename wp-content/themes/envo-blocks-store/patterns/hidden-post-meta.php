<?php
/**
 * Title: Post categories and tags
 * Slug: envo-blocks-store/hidden-post-meta
 * Inserter: no
 *
 * @package Envo_Blocks_Store
 */

?>
<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"blockGap":"0.25rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--50)"><!-- wp:post-terms {"term":"category","prefix":"<?php echo esc_attr__( 'Categories: ', 'envo-blocks-store' ); ?>"} /-->

<!-- wp:post-terms {"term":"post_tag","prefix":"<?php echo esc_attr__( 'Tags: ', 'envo-blocks-store' ); ?>"} /--></div>
<!-- /wp:group -->
