<?php
/**
 * Live preview of a template page: a temporary draft that the editor opens in a new tab.
 *
 * Each user has one preview draft that is reused for the next preview, hidden from the Pages list and
 * deleted a day after the last preview (and when the plugin is deleted).
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Temporary preview drafts.
 */
final class Envo_Blocks_Preview {

	const META  = '_envo_blocks_preview';
	const EVENT = 'envo_blocks_delete_preview';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( self::EVENT, array( __CLASS__, 'delete' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'hide_in_admin' ) );
	}

	/**
	 * Creates or updates the preview draft of the current user.
	 *
	 * @param string $title   Page title.
	 * @param string $content  Serialized block markup made by the editor from the template.
	 * @param string $template Page template of the theme ("" = default), e.g. the canvas template of a template
	 *                         with its own header and footer.
	 * @return string|WP_Error Preview URL.
	 */
	public static function save( $title, $content, $template = '' ) {
		$existing = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'draft',
				'author'         => get_current_user_id(),
				'meta_key'       => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key -- One small lookup per preview click.
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		// wp_insert_post() runs the usual content filters (kses for users without unfiltered_html).
		$id = wp_insert_post(
			wp_slash(
				array(
					'ID'           => $existing ? (int) $existing[0] : 0,
					'post_type'    => 'page',
					'post_status'  => 'draft',
					/* translators: %s: page name of the template. */
					'post_title'   => sprintf( __( 'Envo Blocks preview: %s', 'envo-blocks' ), $title ),
					'post_content' => $content,
					'post_author'  => get_current_user_id(),
				)
			),
			true
		);

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		update_post_meta( $id, self::META, 1 );

		// Only a page template the theme has (the same list the template window offers).
		if ( '' !== $template && array_key_exists( $template, wp_get_theme()->get_page_templates( null, 'page' ) ) ) {
			update_post_meta( $id, '_wp_page_template', $template );
		} else {
			delete_post_meta( $id, '_wp_page_template' );
		}

		wp_clear_scheduled_hook( self::EVENT, array( $id ) );
		wp_schedule_single_event( time() + DAY_IN_SECONDS, self::EVENT, array( $id ) );

		return get_preview_post_link( $id );
	}

	/**
	 * Deletes a preview draft (scheduled event).
	 *
	 * @param int $id Post ID.
	 */
	public static function delete( $id ) {
		$id = absint( $id );
		if ( $id && get_post_meta( $id, self::META, true ) ) {
			wp_delete_post( $id, true );
		}
	}

	/**
	 * Deletes all preview drafts (uninstall).
	 */
	public static function delete_all() {
		$ids = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'meta_key'       => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key -- Only on uninstall.
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		foreach ( $ids as $id ) {
			wp_clear_scheduled_hook( self::EVENT, array( (int) $id ) );
			wp_delete_post( (int) $id, true );
		}
	}

	/**
	 * Keeps preview drafts out of the Pages list.
	 *
	 * @param WP_Query $query Query.
	 */
	public static function hide_in_admin( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'page' !== $query->get( 'post_type' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'edit-page' !== $screen->id ) {
			return;
		}

		$meta   = (array) $query->get( 'meta_query' );
		$meta[] = array(
			'key'     => self::META,
			'compare' => 'NOT EXISTS',
		);
		$query->set( 'meta_query', $meta );
	}
}
