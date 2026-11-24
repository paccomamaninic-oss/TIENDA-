<?php
/**
 * Preview images of templates, sections and presets: loaded from assets.envoblocks.com and kept on this site
 * after they were shown once.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * The plugin ships only data/previews.php (version, size and a tiny blurred placeholder of every preview). The
 * images themselves are on our assets server in two widths (300 and 600 px), with the version in the file name,
 * so browsers and the server cache them for a long time. When the template window has shown a preview, the editor
 * asks this site to keep a copy in uploads/envo-blocks-previews/; from then on the site serves it itself and does
 * not contact the assets server for it again.
 */
final class Envo_Blocks_Previews {

	/**
	 * Default address of the previews.
	 */
	const BASE = 'https://assets.envoblocks.com/previews/';

	/**
	 * Folder in uploads with the kept copies.
	 */
	const FOLDER = 'envo-blocks-previews';

	/**
	 * Widths of every preview.
	 */
	const WIDTHS = array( 300, 600 );

	/**
	 * Most previews kept in one request, and the most the folder may hold (bytes).
	 */
	const MAX_PER_REQUEST = 24;
	const MAX_FOLDER_SIZE = 62914560;

	/**
	 * Manifest cache.
	 *
	 * @var array|null
	 */
	private static $manifest = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'envo_blocks_prune_previews', array( __CLASS__, 'prune' ) );
	}

	/**
	 * The manifest: path => array( v, w, h, lqip ) or array( v, svg ).
	 *
	 * @return array
	 */
	private static function manifest() {
		if ( null === self::$manifest ) {
			$file           = ENVO_BLOCKS_PATH . 'data/previews.php';
			$data           = is_file( $file ) ? include $file : array();
			self::$manifest = (array) apply_filters( 'envo_blocks_previews', is_array( $data ) ? $data : array() );
		}
		return self::$manifest;
	}

	/**
	 * Address of the assets server (filter envo_blocks_preview_base, e.g. for a local copy).
	 *
	 * @return string With a trailing slash.
	 */
	private static function base() {
		return trailingslashit( (string) apply_filters( 'envo_blocks_preview_base', self::BASE ) );
	}

	/**
	 * Folder and URL of the kept copies.
	 *
	 * @return array Array with "dir" and "url", or empty values when uploads are not available.
	 */
	private static function cache_location() {
		$uploads = wp_get_upload_dir();
		if ( ! empty( $uploads['error'] ) ) {
			return array(
				'dir' => '',
				'url' => '',
			);
		}
		return array(
			'dir' => trailingslashit( $uploads['basedir'] ) . self::FOLDER . '/',
			'url' => trailingslashit( $uploads['baseurl'] ) . self::FOLDER . '/',
		);
	}

	/**
	 * File name of one size of a preview on the server and in the cache: "sections/hero-sale.1a2b3c4d-300.webp".
	 *
	 * @param string   $key   Path in the manifest.
	 * @param array    $entry Manifest entry.
	 * @param int|null $width Width (null for SVG).
	 * @return string
	 */
	private static function file( $key, $entry, $width ) {
		$dir  = dirname( $key );
		$dir  = '.' === $dir ? '' : $dir . '/';
		$stem = pathinfo( $key, PATHINFO_FILENAME );
		return ! empty( $entry['svg'] ) ? $dir . $stem . '.' . $entry['v'] . '.svg' : $dir . $stem . '.' . $entry['v'] . '-' . (int) $width . '.webp';
	}

	/**
	 * Whether a manifest path is valid (letters, digits, dashes, folders; no "..").
	 *
	 * @param string $key Path.
	 * @return bool
	 */
	private static function valid_key( $key ) {
		return is_string( $key ) && 1 === preg_match( '#^(?:[a-z0-9_-]+/){0,3}[a-z0-9][a-z0-9_-]{0,63}\.(?:webp|png|jpe?g|svg)$#i', $key ) && false === strpos( $key, '..' );
	}

	/**
	 * Image data of a preview for the editor, or null when it is not in the manifest.
	 *
	 * @param string $key Path in the manifest, e.g. "sections/hero-sale.webp".
	 * @return array|null Array with src, srcset, lqip, ratio, key and remote (not kept on this site yet).
	 */
	public static function get( $key ) {
		$manifest = self::manifest();
		if ( ! self::valid_key( $key ) || empty( $manifest[ $key ]['v'] ) ) {
			return null;
		}
		$entry = $manifest[ $key ];
		$cache = self::cache_location();
		$base  = self::base();

		$urls   = array();
		$remote = false;
		foreach ( ! empty( $entry['svg'] ) ? array( null ) : self::WIDTHS as $width ) {
			$file = self::file( $key, $entry, $width );
			if ( '' !== $cache['dir'] && is_file( $cache['dir'] . $file ) ) {
				$urls[ (int) $width ] = $cache['url'] . $file;
			} else {
				$urls[ (int) $width ] = $base . $file;
				$remote               = true;
			}
		}

		if ( ! empty( $entry['svg'] ) ) {
			return array(
				'src'    => esc_url_raw( $urls[0] ),
				'srcset' => '',
				'lqip'   => '',
				'ratio'  => '',
				'key'    => $key,
				'remote' => $remote,
			);
		}

		$srcset = array();
		foreach ( $urls as $width => $url ) {
			$srcset[] = esc_url_raw( $url ) . ' ' . $width . 'w';
		}

		return array(
			'src'    => esc_url_raw( $urls[600] ),
			'srcset' => implode( ', ', $srcset ),
			'lqip'   => isset( $entry['lqip'] ) && 0 === strpos( (string) $entry['lqip'], 'data:image/webp;base64,' ) ? (string) $entry['lqip'] : '',
			'ratio'  => ! empty( $entry['w'] ) && ! empty( $entry['h'] ) ? (int) $entry['w'] . ' / ' . (int) $entry['h'] : '',
			'key'    => $key,
			'remote' => $remote,
		);
	}

	/**
	 * Just the address (600 px) of a preview, or "".
	 *
	 * @param string $key Path in the manifest.
	 * @return string
	 */
	public static function url( $key ) {
		$data = self::get( $key );
		return $data ? $data['src'] : '';
	}

	/**
	 * Keeps copies of previews on this site (both widths). Only previews from the manifest, only images, and only
	 * from the assets server; files that are already here are skipped.
	 *
	 * @param array $keys Paths in the manifest.
	 * @return array Paths kept now or before.
	 */
	public static function keep( $keys ) {
		$cache    = self::cache_location();
		$manifest = self::manifest();
		if ( '' === $cache['dir'] || ! wp_mkdir_p( $cache['dir'] ) ) {
			return array();
		}
		if ( self::folder_size( $cache['dir'] ) > self::MAX_FOLDER_SIZE ) {
			return array();
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$kept  = array();
		$count = 0;
		foreach ( array_unique( array_map( 'strval', (array) $keys ) ) as $key ) {
			if ( $count >= self::MAX_PER_REQUEST || ! self::valid_key( $key ) || empty( $manifest[ $key ]['v'] ) || ! empty( $manifest[ $key ]['svg'] ) ) {
				continue;
			}
			++$count;
			$ok = true;
			foreach ( self::WIDTHS as $width ) {
				$file   = self::file( $key, $manifest[ $key ], $width );
				$target = $cache['dir'] . $file;
				if ( is_file( $target ) ) {
					continue;
				}
				$ok = self::download( self::base() . $file, $target ) && $ok;
			}
			if ( $ok ) {
				$kept[] = $key;
			}
		}

		if ( $kept && ! wp_next_scheduled( 'envo_blocks_prune_previews' ) ) {
			wp_schedule_event( time() + WEEK_IN_SECONDS, 'weekly', 'envo_blocks_prune_previews' );
		}

		return $kept;
	}

	/**
	 * Downloads one WebP file.
	 *
	 * @param string $url    Address on the assets server.
	 * @param string $target Path in the cache folder.
	 * @return bool
	 */
	private static function download( $url, $target ) {
		$tmp = download_url( $url, 15 );
		if ( is_wp_error( $tmp ) ) {
			return false;
		}
		$size = filesize( $tmp );
		$mime = function_exists( 'wp_get_image_mime' ) ? wp_get_image_mime( $tmp ) : '';
		if ( ! $size || $size > 2 * MB_IN_BYTES || 'image/webp' !== $mime || ! wp_mkdir_p( dirname( $target ) ) ) {
			wp_delete_file( $tmp );
			return false;
		}
		// Copying the temporary download into uploads (works when the temp folder is on another disk).
		$moved = copy( $tmp, $target );
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		return $moved;
	}

	/**
	 * Size of the cache folder in bytes.
	 *
	 * @param string $dir Folder.
	 * @return int
	 */
	private static function folder_size( $dir ) {
		$size = 0;
		if ( ! is_dir( $dir ) ) {
			return 0;
		}
		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $files as $file ) {
			$size += $file->getSize();
		}
		return $size;
	}

	/**
	 * Weekly: removes kept copies of older versions (not in the manifest any more).
	 */
	public static function prune() {
		$cache = self::cache_location();
		if ( '' === $cache['dir'] || ! is_dir( $cache['dir'] ) ) {
			return;
		}
		$current = array();
		foreach ( self::manifest() as $key => $entry ) {
			if ( self::valid_key( $key ) && ! empty( $entry['v'] ) && empty( $entry['svg'] ) ) {
				foreach ( self::WIDTHS as $width ) {
					$current[ self::file( $key, $entry, $width ) ] = true;
				}
			}
		}
		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $cache['dir'], FilesystemIterator::SKIP_DOTS ) );
		foreach ( $files as $file ) {
			$rel = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $cache['dir'] ) ) );
			if ( $file->isFile() && ! isset( $current[ $rel ] ) ) {
				wp_delete_file( $file->getPathname() );
			}
		}
	}

	/**
	 * Removes the cache folder (uninstall).
	 */
	public static function delete_all() {
		wp_clear_scheduled_hook( 'envo_blocks_prune_previews' );
		$cache = self::cache_location();
		if ( '' === $cache['dir'] || ! is_dir( $cache['dir'] ) ) {
			return;
		}
		$items = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $cache['dir'], FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $items as $item ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Own folder in uploads.
			$item->isDir() ? rmdir( $item->getPathname() ) : wp_delete_file( $item->getPathname() );
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Own folder in uploads.
		rmdir( $cache['dir'] );
	}
}
