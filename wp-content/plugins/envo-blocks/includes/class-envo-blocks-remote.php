<?php
/**
 * The template library on our server: new free designs without an update of the plugin.
 *
 * - The public list of the library (twice a day; the last good copy is kept, so a site without a connection still
 *   has it) adds the free templates, sections, presets and layouts this plugin does not bundle yet – only those whose
 *   Envo blocks this version has – and the current PRO lists as locked teasers while Envo Blocks PRO is not installed.
 * - The file of such a free design is loaded when it is imported or inserted, no license needed, and compared with
 *   the checksum of the list. The bundled files always win, and a site without a connection works with them.
 * - The previews of new designs come from the manifest next to the preview images.
 *
 * External service: assets.envoblocks.com (backup: envo-demos.com), see "External services" in readme.txt. Only the
 * list, the preview manifest and the files are requested; nothing about the site is sent but the plugin version in
 * the user agent.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remote library of the free plugin.
 */
final class Envo_Blocks_Remote {

	/**
	 * Library hosts: the main one, then the backup on another hosting.
	 */
	const HOSTS = array( 'https://assets.envoblocks.com/library/', 'https://envo-demos.com/library/' );

	/**
	 * Manifest of the previews on the assets server.
	 */
	const PREVIEWS = 'https://assets.envoblocks.com/previews/manifest.json';

	/**
	 * Option with the last good list, transient of the last check.
	 */
	const OPTION = 'envo_blocks_remote_index';
	const CHECK  = 'envo_blocks_remote_checked';

	/**
	 * Lists of the library.
	 */
	const TYPES = array( 'templates', 'sections', 'presets', 'layouts' );

	/**
	 * Index of this request.
	 *
	 * @var array|null|false
	 */
	private static $index = false;

	/**
	 * Hooks (the lists are filtered before Envo Blocks PRO adds its own entries).
	 */
	public static function init() {
		add_filter( 'envo_blocks_list', array( __CLASS__, 'add_templates' ), 5 );
		add_filter( 'envo_blocks_sections', array( __CLASS__, 'add_sections' ), 5 );
		add_filter( 'envo_blocks_presets', array( __CLASS__, 'add_presets' ), 5 );
		add_filter( 'envo_blocks_layouts', array( __CLASS__, 'add_layouts' ), 5 );
		add_filter( 'envo_blocks_previews', array( __CLASS__, 'add_previews' ), 5 );
		// Twice a day in WP-Cron, so the notice of new templates knows them without waiting on a screen.
		add_action( 'envo_blocks_remote_refresh', array( __CLASS__, 'index' ) );
		add_action(
			'admin_init',
			static function () {
				if ( ! wp_next_scheduled( 'envo_blocks_remote_refresh' ) ) {
					wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', 'envo_blocks_remote_refresh' );
				}
			}
		);
	}

	/**
	 * Library hosts (filter envo_blocks_library_hosts, e.g. for a local copy).
	 *
	 * @return string[]
	 */
	private static function hosts() {
		$hosts = (array) apply_filters( 'envo_blocks_library_hosts', self::HOSTS );
		return array_values( array_filter( array_map( 'esc_url_raw', $hosts ) ) );
	}

	/**
	 * GET of a fixed address of ours.
	 *
	 * @param string $url     URL.
	 * @param int    $timeout Seconds.
	 * @return array|WP_Error
	 */
	private static function get( $url, $timeout ) {
		return wp_remote_get(
			$url,
			array(
				'timeout'    => $timeout,
				'user-agent' => 'EnvoBlocks/' . ENVO_BLOCKS_VERSION,
			)
		);
	}

	/**
	 * A request to the library: the hosts in turn while they do not answer (no connection, 5xx).
	 *
	 * @param array $query   Query arguments (list / file).
	 * @param int   $timeout Seconds per host.
	 * @return array|null array( 'code' => HTTP status, 'body' => text ), or null when no host answered.
	 */
	private static function request( array $query, $timeout ) {
		foreach ( self::hosts() as $host ) {
			$response = self::get( add_query_arg( $query, $host ), $timeout );
			$code     = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
			if ( $code > 0 && $code < 500 ) {
				return array(
					'code' => $code,
					'body' => (string) wp_remote_retrieve_body( $response ),
				);
			}
		}
		return null;
	}

	/**
	 * The list of the library: refreshed twice a day (an hour after a failure); the last good copy otherwise.
	 * Loaded only where the lists are used (template window, notices of new templates), never on the front end.
	 *
	 * @param bool $refresh Load it now.
	 * @return array|null
	 */
	public static function index( $refresh = false ) {
		if ( false !== self::$index && ! $refresh ) {
			return self::$index;
		}
		$last = get_option( self::OPTION );
		$last = is_array( $last ) ? $last : null;

		// Only on the screens of Envo Blocks, in REST requests of the editor and in cron: visitors and other admin screens
		// (the Dashboard with the notice of new templates) never wait for our server.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reads which screen this is.
		$here = ( is_admin() && 0 === strpos( $page, 'envo-blocks' ) ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI );
		if ( $here && ( $refresh || false === get_site_transient( self::CHECK ) ) && (bool) apply_filters( 'envo_blocks_remote_library', true ) ) {
			// Mark the check first: a slow or missing connection is tried again only an hour later.
			set_site_transient( self::CHECK, 0, HOUR_IN_SECONDS );
			$result = self::request( array( 'list' => 1 ), 6 );
			$data   = $result && 200 === $result['code'] ? json_decode( $result['body'], true ) : null;
			if ( is_array( $data ) && 'envo-library/v1' === ( $data['schema'] ?? '' ) && isset( $data['hashes'] ) ) {
				$last = array(
					'version'  => sanitize_text_field( (string) ( $data['version'] ?? '' ) ),
					'free'     => array(),
					'pro'      => array(),
					'hashes'   => array(),
					'previews' => self::load_previews(),
					'loaded'   => time(),
				);
				foreach ( self::TYPES as $type ) {
					$last['free'][ $type ] = isset( $data['free'][ $type ] ) ? array_values( (array) $data['free'][ $type ] ) : array();
					$last['pro'][ $type ]  = isset( $data[ $type ] ) ? array_values( (array) $data[ $type ] ) : array();
				}
				// Only the checksums of the free files are needed here.
				foreach ( (array) $data['hashes'] as $key => $hash ) {
					if ( is_string( $key ) && 0 === strpos( $key, 'free/' ) && is_string( $hash ) ) {
						$last['hashes'][ $key ] = $hash;
					}
				}
				update_option( self::OPTION, $last, false );
				set_site_transient( self::CHECK, 1, 12 * HOUR_IN_SECONDS );
			}
		}
		self::$index = $last;
		return $last;
	}

	/**
	 * The preview manifest of the assets server: path => array( v, w, h ) or array( v, svg ).
	 *
	 * @return array
	 */
	private static function load_previews() {
		$response = self::get( (string) apply_filters( 'envo_blocks_remote_previews', self::PREVIEWS ), 6 );
		$data     = ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ? json_decode( (string) wp_remote_retrieve_body( $response ), true ) : null;
		$out      = array();
		foreach ( is_array( $data ) ? $data : array() as $key => $entry ) {
			if ( is_string( $key ) && is_array( $entry ) && ! empty( $entry['v'] ) && 1 === preg_match( '/^[a-f0-9]{4,32}$/', (string) $entry['v'] ) ) {
				$out[ $key ] = ! empty( $entry['svg'] )
					? array( 'v' => (string) $entry['v'], 'svg' => true )
					: array( 'v' => (string) $entry['v'], 'w' => (int) ( $entry['w'] ?? 0 ), 'h' => (int) ( $entry['h'] ?? 0 ) );
			}
		}
		return $out;
	}

	/**
	 * Free entries of the remote list that the plugin does not bundle (by file name) and whose Envo blocks this
	 * version has.
	 *
	 * @param string $type    One of TYPES.
	 * @param array  $bundled Raw bundled entries.
	 * @return array
	 */
	private static function missing( $type, $bundled ) {
		$index = self::index();
		if ( ! $index || empty( $index['free'][ $type ] ) ) {
			return array();
		}
		$have = array();
		foreach ( (array) $bundled as $entry ) {
			if ( is_array( $entry ) && ! empty( $entry['file'] ) ) {
				$have[ $entry['file'] ] = true;
			}
		}
		$registry = WP_Block_Type_Registry::get_instance();
		$out      = array();
		foreach ( $index['free'][ $type ] as $entry ) {
			if ( ! is_array( $entry ) || empty( $entry['file'] ) || isset( $have[ $entry['file'] ] ) ) {
				continue;
			}
			// A design made with a block of a newer version waits for the update.
			foreach ( isset( $entry['blocks'] ) ? (array) $entry['blocks'] : array() as $block ) {
				if ( is_string( $block ) && 0 === strpos( $block, 'envo-blocks/' ) && ! $registry->is_registered( $block ) ) {
					continue 2;
				}
			}
			unset( $entry['blocks'] );
			$entry['premium'] = false;
			$entry['remote']  = true;
			$out[]            = $entry;
		}
		return $out;
	}

	/**
	 * New free templates.
	 *
	 * @param array $list Raw entries.
	 * @return array
	 */
	public static function add_templates( $list ) {
		return array_merge( (array) $list, self::missing( 'templates', $list ) );
	}

	/**
	 * New free sections.
	 *
	 * @param array $list Raw entries.
	 * @return array
	 */
	public static function add_sections( $list ) {
		return array_merge( (array) $list, self::missing( 'sections', $list ) );
	}

	/**
	 * New free presets.
	 *
	 * @param array $list Raw entries.
	 * @return array
	 */
	public static function add_presets( $list ) {
		return array_merge( (array) $list, self::missing( 'presets', $list ) );
	}

	/**
	 * New free store layouts.
	 *
	 * @param array $list Raw entries.
	 * @return array
	 */
	public static function add_layouts( $list ) {
		return array_merge( (array) $list, self::missing( 'layouts', $list ) );
	}

	/**
	 * Previews of designs newer than the plugin; the bundled manifest wins.
	 *
	 * @param array $manifest path => entry.
	 * @return array
	 */
	public static function add_previews( $manifest ) {
		$index = self::index();
		foreach ( (array) ( $index['previews'] ?? array() ) as $key => $entry ) {
			if ( ! isset( $manifest[ $key ] ) ) {
				$manifest[ $key ] = $entry;
			}
		}
		return $manifest;
	}

	/**
	 * The current PRO list (teasers while PRO is not installed), or null to use the bundled copy.
	 *
	 * @param string $type One of TYPES.
	 * @return array|null
	 */
	public static function pro_teasers( $type ) {
		$index = self::index();
		return $index && ! empty( $index['pro'][ $type ] ) ? (array) $index['pro'][ $type ] : null;
	}

	/**
	 * The JSON text of a free file of the library, checked against the list (kept an hour for the pages of one
	 * template).
	 *
	 * @param string $type One of TYPES.
	 * @param string $file File name.
	 * @return string|WP_Error
	 */
	public static function file( $type, $file ) {
		if ( ! in_array( $type, self::TYPES, true ) || 1 !== preg_match( '/^[a-z0-9][a-z0-9_-]{0,63}\.json$/', (string) $file ) ) {
			return new WP_Error( 'envo_blocks_remote_file', __( 'Unknown template file.', 'envo-blocks' ), array( 'status' => 400 ) );
		}
		$rel   = 'free/' . $type . '/' . $file;
		$cache = 'envo_blocks_lib_' . md5( $rel );
		$hit   = get_site_transient( $cache );
		if ( is_string( $hit ) && '' !== $hit ) {
			return $hit;
		}

		$result = self::request( array( 'file' => $rel ), 20 );
		if ( ! $result || 200 !== $result['code'] ) {
			return new WP_Error( 'envo_blocks_remote_offline', __( 'This design is loaded from our template library, which could not be reached from your server. Please try again later.', 'envo-blocks' ), array( 'status' => 503 ) );
		}
		$index = self::index();
		$hash  = (string) ( $index['hashes'][ $rel ] ?? '' );
		if ( '' === $hash || ! hash_equals( $hash, hash( 'sha256', $result['body'] ) ) ) {
			$index = self::index( true ); // The library may be newer than our copy of the list.
			$hash  = (string) ( $index['hashes'][ $rel ] ?? '' );
		}
		if ( '' === $hash || ! hash_equals( $hash, hash( 'sha256', $result['body'] ) ) ) {
			return new WP_Error( 'envo_blocks_remote_checksum', __( 'The downloaded design is damaged. Please try again.', 'envo-blocks' ), array( 'status' => 502 ) );
		}
		set_site_transient( $cache, $result['body'], HOUR_IN_SECONDS );
		return $result['body'];
	}
}
