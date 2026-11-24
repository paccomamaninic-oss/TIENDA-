<?php
/**
 * Copies the images of an imported template or block into the Media Library of this site, so the page
 * does not load them from the demo server afterwards.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Images are taken only from:
 * - the media folder of this plugin (assets/media/, copied without any HTTP request),
 * - our demo / product sites (MEDIA_HOSTS, filter envo_blocks_media_hosts).
 *
 * Every image is downloaded once: the attachment keeps its source address (meta SOURCE_META) and later
 * imports reuse it. Images that cannot be copied stay as they are.
 */
final class Envo_Blocks_Media {

	/**
	 * Hosts whose images are copied to this site.
	 */
	const MEDIA_HOSTS = array( 'demos.envoblocks.com', 'envoblocks.com' );

	/**
	 * Attachment meta with the address the image was copied from.
	 */
	const SOURCE_META = '_envo_blocks_source';

	/**
	 * Placeholder of the plugin URL in bundled JSON files ("{envo-blocks}/assets/media/x.webp").
	 */
	const PLUGIN_TOKEN = '{envo-blocks}/';

	/**
	 * Most images copied in one request (the rest stay remote).
	 */
	const MAX_IMAGES = 60;

	/**
	 * Largest image that is downloaded, in bytes.
	 */
	const MAX_BYTES = 10485760;

	/**
	 * Image addresses in attribute values (also inside HTML or CSS).
	 */
	const IMAGE_PATTERN = '#https?://[^\s"\'<>()\\\\]+?\.(?:jpe?g|png|gif|webp|avif)(?=[\s"\'<>()?\\\\]|$)#i';

	/**
	 * Block attribute listing the "<prefix>Url" keys the library build added next to image IDs ("backgroundImageId"
	 * → "backgroundImageUrl"), so localize() can copy the image and set the new ID. Removed after the import.
	 */
	const ADDED_URLS = 'envoMediaUrls';

	/**
	 * Addresses already resolved in this request: source URL => array( id, url ), or false.
	 *
	 * @var array
	 */
	private static $done = array();

	/**
	 * Number of images copied in this request.
	 *
	 * @var int
	 */
	private static $count = 0;

	/**
	 * Images that came with an uploaded ZIP file: source URL => path of the extracted file. They are used
	 * instead of downloading, whatever their host is.
	 *
	 * @var array
	 */
	private static $files = array();

	/**
	 * Sets the images of an uploaded ZIP file (see $files); an empty array clears them.
	 *
	 * @param array $files Source URL => absolute path of a temporary file.
	 */
	public static function use_files( $files ) {
		self::$files = is_array( $files ) ? $files : array();
	}

	/**
	 * Replaces the plugin placeholder in the blocks of a bundled file with the plugin URL.
	 *
	 * @param array $blocks Blocks (name, attributes, innerBlocks).
	 * @return array
	 */
	public static function expand_plugin_urls( $blocks ) {
		return self::map_strings(
			$blocks,
			static function ( $value ) {
				return false === strpos( $value, self::PLUGIN_TOKEN ) ? $value : str_replace( self::PLUGIN_TOKEN, ENVO_BLOCKS_URL, $value );
			}
		);
	}

	/**
	 * Copies the images used in the blocks to the Media Library and points the blocks to the copies.
	 * Without the "upload_files" capability the blocks are returned unchanged.
	 *
	 * @param array $blocks  Blocks (name, attributes, innerBlocks).
	 * @param int   $post_id Post the images are attached to (0 = none).
	 * @return array
	 */
	public static function localize( $blocks, $post_id = 0 ) {
		if ( ! is_array( $blocks ) || ! current_user_can( 'upload_files' ) ) {
			return $blocks;
		}

		$post_id = ( $post_id && current_user_can( 'edit_post', $post_id ) ) ? (int) $post_id : 0;

		// 1. Every image address in any attribute, also inside HTML or CSS.
		$found = self::image_urls( $blocks );

		// 2. Copy them.
		$map = array();
		foreach ( $found as $url ) {
			$copy = self::copy( $url, $post_id );
			if ( $copy ) {
				$map[ $url ] = $copy;
			}
		}

		if ( ! $map ) {
			return $blocks;
		}

		// 3. New addresses everywhere, and the new attachment IDs next to them.
		return self::replace( $blocks, $map );
	}

	/**
	 * Copies one image (or returns the earlier copy).
	 *
	 * @param string $url     Source address.
	 * @param int    $post_id Parent post.
	 * @return array|false Array( id, url ) or false.
	 */
	private static function copy( $url, $post_id ) {
		if ( isset( self::$done[ $url ] ) ) {
			return self::$done[ $url ];
		}

		self::$done[ $url ] = false;

		// An image of a ZIP file that is already on this site (exported here) stays as it is.
		if ( isset( self::$files[ $url ] ) && self::is_own( $url ) ) {
			return false;
		}

		$local = isset( self::$files[ $url ] ) ? self::$files[ $url ] : self::bundled_file( $url );
		if ( ! $local && ! self::is_allowed_remote( $url ) ) {
			return false;
		}

		$existing = self::find_copy( $url );
		if ( $existing ) {
			self::$done[ $url ] = $existing;
			return $existing;
		}

		if ( self::$count >= self::MAX_IMAGES ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$name = sanitize_file_name( wp_basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
		$type = wp_check_filetype( $name, self::image_mimes() );
		if ( ! $type['type'] ) {
			return false;
		}

		if ( $local ) {
			// A file of this plugin or of an uploaded ZIP: copied on disk, no HTTP request.
			$tmp = wp_tempnam( $name );
			if ( ! $tmp || ! copy( $local, $tmp ) ) {
				return false;
			}
		} else {
			$tmp = download_url( $url, 20 );
			if ( is_wp_error( $tmp ) ) {
				return false;
			}
		}

		if ( ! is_file( $tmp ) || filesize( $tmp ) > self::MAX_BYTES ) {
			wp_delete_file( $tmp );
			return false;
		}

		// The real content must be an image of the allowed types, whatever the name says.
		$check = wp_check_filetype_and_ext( $tmp, $name, self::image_mimes() );
		if ( empty( $check['type'] ) || 0 !== strpos( $check['type'], 'image/' ) ) {
			wp_delete_file( $tmp );
			return false;
		}

		$file = array(
			'name'     => $check['proper_filename'] ? $check['proper_filename'] : $name,
			'tmp_name' => $tmp,
		);

		self::$count++;
		$id = media_handle_sideload( $file, $post_id );
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			return false;
		}

		update_post_meta( $id, self::SOURCE_META, esc_url_raw( $url ) );

		self::$done[ $url ] = array(
			'id'  => (int) $id,
			'url' => (string) wp_get_attachment_url( $id ),
		);

		return self::$done[ $url ];
	}

	/**
	 * Image addresses used in the blocks (any attribute, also inside HTML or CSS).
	 *
	 * @param array $blocks Blocks.
	 * @return string[]
	 */
	public static function image_urls( $blocks ) {
		$found = array();
		self::map_strings(
			$blocks,
			static function ( $value ) use ( &$found ) {
				if ( preg_match_all( self::IMAGE_PATTERN, $value, $matches ) ) {
					foreach ( $matches[0] as $url ) {
						$found[ $url ] = true;
					}
				}
				return $value;
			}
		);
		return array_keys( $found );
	}

	/**
	 * File of an image for "Export as ZIP": an image of this site's uploads or of this plugin is read from
	 * disk, an image of our demo sites is downloaded. Other addresses are left out (they stay links).
	 *
	 * @param string $url Address.
	 * @return array|false Array( path, temporary ) or false.
	 */
	public static function export_file( $url ) {
		$name = sanitize_file_name( wp_basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
		$type = wp_check_filetype( $name, self::image_mimes() );
		if ( ! $type['type'] ) {
			return false;
		}

		$bundled = self::bundled_file( $url );
		if ( $bundled ) {
			return array( $bundled, false );
		}

		$uploads = wp_get_upload_dir();
		$base    = set_url_scheme( $uploads['baseurl'] );
		if ( 0 === strpos( set_url_scheme( $url ), $base . '/' ) ) {
			$dir  = realpath( $uploads['basedir'] );
			$file = realpath( $uploads['basedir'] . '/' . rawurldecode( substr( set_url_scheme( $url ), strlen( $base ) + 1 ) ) );
			return ( $dir && $file && 0 === strpos( $file, $dir . DIRECTORY_SEPARATOR ) && is_file( $file ) && filesize( $file ) <= self::MAX_BYTES ) ? array( $file, false ) : false;
		}

		if ( ! self::is_allowed_remote( $url ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$tmp = download_url( $url, 20 );
		if ( is_wp_error( $tmp ) ) {
			return false;
		}
		if ( ! is_file( $tmp ) || filesize( $tmp ) > self::MAX_BYTES ) {
			wp_delete_file( $tmp );
			return false;
		}

		return array( $tmp, true );
	}

	/**
	 * Earlier copy of the image, if it is still in the Media Library.
	 *
	 * @param string $url Source address.
	 * @return array|false
	 */
	private static function find_copy( $url ) {
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => self::SOURCE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => esc_url_raw( $url ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( ! $ids ) {
			return false;
		}

		$copy = wp_get_attachment_url( $ids[0] );

		return $copy ? array(
			'id'  => (int) $ids[0],
			'url' => (string) $copy,
		) : false;
	}

	/**
	 * Path of an image in the media folder of this plugin, or "" for any other address.
	 *
	 * @param string $url Address.
	 * @return string
	 */
	private static function bundled_file( $url ) {
		$base = ENVO_BLOCKS_URL . 'assets/media/';
		if ( 0 !== strpos( $url, $base ) ) {
			return '';
		}

		$dir  = realpath( ENVO_BLOCKS_PATH . 'assets/media' );
		$file = realpath( ENVO_BLOCKS_PATH . 'assets/media/' . rawurldecode( substr( $url, strlen( $base ) ) ) );

		return ( $dir && $file && 0 === strpos( $file, $dir . DIRECTORY_SEPARATOR ) && is_file( $file ) ) ? $file : '';
	}

	/**
	 * Whether a remote image may be downloaded: https/http from one of our hosts, not this site itself.
	 *
	 * @param string $url Address.
	 * @return bool
	 */
	private static function is_allowed_remote( $url ) {
		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$port = wp_parse_url( $url, PHP_URL_PORT );
		if ( '' === $host || ! in_array( wp_parse_url( $url, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) ) {
			return false;
		}

		// Images that are already on this site stay as they are.
		$uploads = wp_get_upload_dir();
		$own     = wp_parse_url( $uploads['baseurl'] );
		if ( isset( $own['host'] ) && strtolower( $own['host'] ) === $host && ( isset( $own['port'] ) ? $own['port'] : null ) === $port ) {
			return false;
		}

		$hosts = array_map( 'strtolower', (array) apply_filters( 'envo_blocks_media_hosts', self::MEDIA_HOSTS ) );

		return in_array( $host, $hosts, true );
	}

	/**
	 * Whether an address points to an existing file in the uploads folder of this site.
	 *
	 * @param string $url Address.
	 * @return bool
	 */
	private static function is_own( $url ) {
		$uploads = wp_get_upload_dir();
		$base    = set_url_scheme( $uploads['baseurl'] ) . '/';
		$url     = set_url_scheme( $url );

		return 0 === strpos( $url, $base ) && is_file( $uploads['basedir'] . '/' . rawurldecode( substr( $url, strlen( $base ) ) ) );
	}

	/**
	 * Image types that may be copied (no SVG).
	 *
	 * @return array
	 */
	private static function image_mimes() {
		return array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'gif'          => 'image/gif',
			'webp'         => 'image/webp',
			'avif'         => 'image/avif',
		);
	}

	/**
	 * Puts the copies into the blocks: every address is replaced, and an ID key next to a replaced address
	 * ("url" -> "id", "imageUrl" -> "imageId", "mediaUrl" -> "mediaId") gets the new attachment ID.
	 *
	 * @param array $blocks Blocks.
	 * @param array $map    Source URL => array( id, url ).
	 * @return array
	 */
	private static function replace( $blocks, $map ) {
		$from = array_keys( $map );
		$to   = wp_list_pluck( $map, 'url' );

		$walk = static function ( $node ) use ( &$walk, $map, $from, $to ) {
			if ( ! is_array( $node ) ) {
				return is_string( $node ) ? str_replace( $from, $to, $node ) : $node;
			}

			$ids = array();
			foreach ( $node as $key => $value ) {
				if ( is_string( $value ) && isset( $map[ $value ] ) && is_string( $key ) ) {
					$prefix = preg_replace( '/(url|Url|URL)$/', '', $key );
					$id_key = '' === $prefix ? 'id' : $prefix . 'Id';
					if ( $id_key !== $key && array_key_exists( $id_key, $node ) ) {
						$ids[ $id_key ] = $map[ $value ]['id'];
					}
				}
				$node[ $key ] = $walk( $value );
			}

			// After the loop, so the original ID (before or after the address) is not written back.
			return array_merge( $node, $ids );
		};

		foreach ( $blocks as $i => $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			if ( isset( $block['attributes'] ) && is_array( $block['attributes'] ) ) {
				$blocks[ $i ]['attributes'] = $walk( $block['attributes'] );
			}
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$blocks[ $i ]['innerBlocks'] = self::replace( $block['innerBlocks'], $map );
			}
		}

		return $blocks;
	}

	/**
	 * Removes the image addresses the library build added next to image IDs (see ADDED_URLS): the blocks do not
	 * have such attributes. Call it after localize(), which needs them to set the new IDs.
	 *
	 * @param array $blocks Blocks (name, attributes, innerBlocks).
	 * @return array
	 */
	public static function drop_added_urls( $blocks ) {
		foreach ( (array) $blocks as $i => $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			if ( isset( $block['attributes'][ self::ADDED_URLS ] ) && is_array( $block['attributes'] ) ) {
				foreach ( (array) $block['attributes'][ self::ADDED_URLS ] as $key ) {
					unset( $blocks[ $i ]['attributes'][ $key ] );
				}
				unset( $blocks[ $i ]['attributes'][ self::ADDED_URLS ] );
			}
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$blocks[ $i ]['innerBlocks'] = self::drop_added_urls( $block['innerBlocks'] );
			}
		}

		return $blocks;
	}

	/**
	 * Runs a callback on every string attribute of the blocks.
	 *
	 * @param array    $blocks   Blocks.
	 * @param callable $callback Gets and returns a string.
	 * @return array
	 */
	private static function map_strings( $blocks, $callback ) {
		$walk = static function ( $node ) use ( &$walk, $callback ) {
			if ( is_string( $node ) ) {
				return $callback( $node );
			}
			if ( is_array( $node ) ) {
				foreach ( $node as $key => $value ) {
					$node[ $key ] = $walk( $value );
				}
			}
			return $node;
		};

		foreach ( (array) $blocks as $i => $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			if ( isset( $block['attributes'] ) && is_array( $block['attributes'] ) ) {
				$blocks[ $i ]['attributes'] = $walk( $block['attributes'] );
			}
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$blocks[ $i ]['innerBlocks'] = self::map_strings( $block['innerBlocks'], $callback );
			}
		}

		return $blocks;
	}
}
