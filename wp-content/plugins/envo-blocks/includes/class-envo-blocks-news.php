<?php
/**
 * Two small notices in the admin, both dismissible:
 *
 * - New templates: when the library has templates the site has not seen yet (a newer plugin, or new designs in the
 *   template library on our server), their main pages with a preview. The site remembers the templates it has seen
 *   (option envo_blocks_seen_templates); on the first run everything counts as seen, so a fresh install shows nothing.
 *   Free and PRO templates alike, newest first (the order of the library).
 * - What's new: after an update to a version with an entry in data/whats-new.php, its new features (name + one
 *   sentence) and fixes, once (option envo_blocks_news_version). Only for users who manage plugins, never after a
 *   fresh install.
 *
 * Both appear only on the Dashboard, the Plugins screen and the screens of Envo Blocks.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * New templates and "What's new" notices.
 */
final class Envo_Blocks_News {

	const SEEN    = 'envo_blocks_seen_templates';
	const VERSION = 'envo_blocks_news_version';
	const SHOWN   = 4;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'first_run' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_action( 'wp_ajax_envo_blocks_news_seen', array( __CLASS__, 'ajax_seen' ) );
		add_action( 'load-envo-blocks_page_' . Envo_Blocks_Admin::SLUG . '-import', array( __CLASS__, 'opened' ) );
	}

	/**
	 * First admin page after the install: the templates there are seen, and the version needs no "What's new".
	 * After an update the version option stays older, so the notice shows once.
	 */
	public static function first_run() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		if ( null === self::seen() ) {
			self::mark_seen();
		}
		if ( false === get_option( self::VERSION ) ) {
			// Activation stores the version (Envo_Blocks_Admin::activated()); without it this is an update from a
			// version before this notice: show what is new.
			update_option( self::VERSION, '0', false );
		}
	}

	/**
	 * IDs of the templates the site has seen; null before the first run.
	 *
	 * @return string[]|null
	 */
	private static function seen() {
		$seen = get_option( self::SEEN, null );
		return is_array( $seen ) ? $seen : null;
	}

	/**
	 * Marks every current template as seen.
	 */
	public static function mark_seen() {
		update_option( self::SEEN, array_keys( Envo_Blocks_Library::all() ), false );
	}

	/**
	 * Opening Templates Import from the notice (?envo_new=1) marks the new templates as seen.
	 */
	public static function opened() {
		if ( isset( $_GET['envo_new'] ) && current_user_can( 'edit_posts' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only marks the notice as read.
			self::mark_seen();
		}
	}

	/**
	 * Where the notices may show: our screens, the Dashboard and Plugins.
	 *
	 * @return string "own", "other" or "" (not here).
	 */
	private static function place() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return '';
		}
		if ( false !== strpos( (string) $screen->id, Envo_Blocks_Admin::SLUG ) ) {
			return 'own';
		}
		return in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ? 'other' : '';
	}

	/**
	 * The notices.
	 */
	public static function notices() {
		$place = self::place();
		if ( '' === $place ) {
			return;
		}
		$news = current_user_can( 'update_plugins' ) ? self::whats_new() : null;
		if ( $news ) {
			self::render_whats_new( $news );
		}
		if ( current_user_can( 'edit_posts' ) ) {
			$new = self::unseen();
			if ( $new ) {
				self::render_templates( $new );
			}
		}
		if ( $news || ! empty( $new ) ) {
			self::script();
		}
	}

	/**
	 * The entry of data/whats-new.php for this version when the site has not seen it, else null.
	 *
	 * @return array|null
	 */
	private static function whats_new() {
		$seen = (string) get_option( self::VERSION, ENVO_BLOCKS_VERSION );
		if ( version_compare( $seen, ENVO_BLOCKS_VERSION, '>=' ) ) {
			return null;
		}
		$all = include ENVO_BLOCKS_PATH . 'data/whats-new.php';
		$all = is_array( $all ) ? $all : array();
		if ( empty( $all[ ENVO_BLOCKS_VERSION ] ) || ! is_array( $all[ ENVO_BLOCKS_VERSION ] ) ) {
			// Nothing to tell for this version (a small fix release): seen.
			update_option( self::VERSION, ENVO_BLOCKS_VERSION, false );
			return null;
		}
		return $all[ ENVO_BLOCKS_VERSION ];
	}

	/**
	 * Templates not seen yet (main pages only), newest first.
	 *
	 * @return array Entries of Envo_Blocks_Library::public_list().
	 */
	private static function unseen() {
		$seen = self::seen();
		if ( null === $seen ) {
			return array();
		}
		$ids = array_diff( array_keys( Envo_Blocks_Library::all() ), $seen );
		if ( ! $ids ) {
			return array();
		}
		$out = array();
		foreach ( Envo_Blocks_Library::public_list() as $template ) {
			if ( in_array( $template['id'], $ids, true ) ) {
				$out[] = $template;
			}
		}
		return $out;
	}

	/**
	 * "What's new in Envo Blocks x.y".
	 *
	 * @param array $news Entry of data/whats-new.php.
	 */
	private static function render_whats_new( $news ) {
		$new   = isset( $news['new'] ) ? array_slice( (array) $news['new'], 0, 5 ) : array();
		$fixed = isset( $news['fixed'] ) ? array_slice( (array) $news['fixed'], 0, 3 ) : array();
		?>
		<div class="notice notice-info is-dismissible envo-news" data-envo-news="version" data-nonce="<?php echo esc_attr( wp_create_nonce( 'envo_blocks_news_seen' ) ); ?>">
			<p class="envo-news__title">
				<strong>
					<?php
					/* translators: %s: version number of the plugin. */
					echo esc_html( sprintf( __( 'What’s new in Envo Blocks %s', 'envo-blocks' ), ENVO_BLOCKS_VERSION ) );
					?>
				</strong>
			</p>
			<?php if ( $new ) : ?>
				<ul class="envo-news__features">
					<?php foreach ( $new as $item ) : ?>
						<?php if ( is_array( $item ) && 2 === count( $item ) ) : ?>
							<li><strong><?php echo esc_html( $item[0] ); ?></strong> – <?php echo esc_html( $item[1] ); ?></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( $fixed ) : ?>
				<p class="envo-news__fixed">
					<strong><?php esc_html_e( 'Fixed:', 'envo-blocks' ); ?></strong>
					<?php echo esc_html( implode( ' ', array_map( 'strval', $fixed ) ) ); ?>
				</p>
			<?php endif; ?>
			<p class="envo-news__links">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Envo_Blocks_Admin::SLUG . '-blocks' ) ); ?>"><?php esc_html_e( 'See the blocks', 'envo-blocks' ); ?></a>
				· <a href="https://wordpress.org/plugins/envo-blocks/#developers" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Full changelog', 'envo-blocks' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * "N new templates" with the previews of their main pages.
	 *
	 * @param array $new Templates (public list entries).
	 */
	private static function render_templates( $new ) {
		$url  = admin_url( 'admin.php?page=' . Envo_Blocks_Admin::SLUG . '-import&envo_new=1' );
		$more = count( $new ) - self::SHOWN;
		?>
		<div class="notice notice-info is-dismissible envo-news" data-envo-news="templates" data-nonce="<?php echo esc_attr( wp_create_nonce( 'envo_blocks_news_seen' ) ); ?>">
			<p class="envo-news__title">
				<strong>
					<?php
					/* translators: %d: number of new templates. */
					echo esc_html( sprintf( _n( '%d new store template', '%d new store templates', count( $new ), 'envo-blocks' ), count( $new ) ) );
					?>
				</strong>
				– <?php esc_html_e( 'complete stores you can import in Templates Import.', 'envo-blocks' ); ?>
			</p>
			<ul class="envo-news__list">
				<?php foreach ( array_slice( $new, 0, self::SHOWN ) as $template ) : ?>
					<li>
						<a href="<?php echo esc_url( $url ); ?>">
							<span class="envo-news__img">
								<?php if ( '' !== $template['image'] ) : ?>
									<img src="<?php echo esc_url( $template['image'] ); ?>" alt="" loading="lazy" decoding="async" width="150" height="112">
								<?php endif; ?>
								<?php if ( $template['premium'] ) : ?>
									<span class="envo-news__pro"><?php esc_html_e( 'PRO', 'envo-blocks' ); ?></span>
								<?php endif; ?>
							</span>
							<span class="envo-news__name"><?php echo esc_html( $template['name'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
				<?php if ( $more > 0 ) : ?>
					<li class="envo-news__more">
						<a href="<?php echo esc_url( $url ); ?>">
							<?php
							/* translators: %d: number of further new templates. */
							echo esc_html( sprintf( __( '+%d more', 'envo-blocks' ), $more ) );
							?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
			<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'See the new templates', 'envo-blocks' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Styles and the script of the close buttons (once per page).
	 */
	private static function script() {
		?>
		<style>
			.envo-news__title { font-size: 14px; }
			.envo-news__features { margin: 6px 0 4px 18px; list-style: disc; }
			.envo-news__features li { margin: 3px 0; }
			.envo-news__fixed, .envo-news__links { margin-top: 4px; }
			.envo-news__list { display: flex; flex-wrap: wrap; gap: 12px; margin: 10px 0 4px; }
			.envo-news__list li { margin: 0; }
			.envo-news .envo-news__list a { display: block; width: 150px; color: inherit; text-decoration: none; box-shadow: none; }
			.envo-news__img { position: relative; display: block; width: 150px; height: 112px; overflow: hidden; border-radius: 6px; background: #f0f0f1; box-shadow: 0 0 0 1px rgba(0, 0, 0, .08); }
			.envo-news__img img { display: block; width: 100%; height: 100%; object-fit: cover; object-position: top; }
			.envo-news__pro { position: absolute; top: 6px; left: 6px; padding: 1px 6px; border-radius: 8px; background: #7c3aed; color: #fff; font-size: 10px; font-weight: 700; }
			.envo-news__name { display: block; margin-top: 6px; font-weight: 600; }
			.envo-news .envo-news__more a { display: flex; align-items: center; justify-content: center; height: 112px; border-radius: 6px; background: #f6f7f7; font-size: 15px; font-weight: 600; }
		</style>
		<script>
			( function () {
				// The close button (added by WordPress) remembers the notice.
				document.querySelectorAll( '.envo-news' ).forEach( function ( notice ) {
					notice.addEventListener( 'click', function ( event ) {
						if ( event.target.closest( '.notice-dismiss' ) ) {
							var body = new FormData();
							body.append( 'action', 'envo_blocks_news_seen' );
							body.append( 'what', notice.getAttribute( 'data-envo-news' ) );
							body.append( '_ajax_nonce', notice.getAttribute( 'data-nonce' ) );
							fetch( window.ajaxurl || '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', credentials: 'same-origin', body: body } );
						}
					} );
				} );
			} )();
		</script>
		<?php
	}

	/**
	 * A notice was closed.
	 */
	public static function ajax_seen() {
		check_ajax_referer( 'envo_blocks_news_seen' );
		$what = isset( $_POST['what'] ) ? sanitize_key( wp_unslash( $_POST['what'] ) ) : '';
		if ( 'version' === $what && current_user_can( 'update_plugins' ) ) {
			update_option( self::VERSION, ENVO_BLOCKS_VERSION, false );
			wp_send_json_success();
		}
		if ( 'templates' === $what && current_user_can( 'edit_posts' ) ) {
			self::mark_seen();
			wp_send_json_success();
		}
		wp_send_json_error( null, 403 );
	}
}
