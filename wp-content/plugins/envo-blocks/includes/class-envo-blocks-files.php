<?php
/**
 * Template files: "Import from file" (a JSON file, or a ZIP file with the JSON and its images) and "Export as ZIP"
 * (the JSON of a page with its images). Useful when the template library cannot be reached, to move a page to
 * another site, or for files sent by our support.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * ZIP layout: template.json in the root, with a "files" map ( image address => "images/<name>" ) next to the blocks,
 * and the images in the images/ folder. On import every listed image is used instead of downloading its address.
 */
final class Envo_Blocks_Files {

	/**
	 * Largest uploaded ZIP file, in bytes.
	 */
	const MAX_ZIP = 52428800; // 50 MB.

	/**
	 * Largest ZIP file made by "Export as ZIP", in bytes.
	 */
	const MAX_EXPORT = 41943040; // 40 MB.

	/**
	 * Most images in one ZIP file.
	 */
	const MAX_FILES = 60;

	/**
	 * Name of the JSON file inside a ZIP file.
	 */
	const JSON_NAME = 'template.json';

	/**
	 * Minutes a prepared import waits for the editor (Envo Blocks > Templates Import page).
	 */
	const KEEP_MINUTES = 30;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Routes.
	 */
	public static function register_routes() {
		register_rest_route(
			Envo_Blocks_REST::NAMESPACE_ID,
			'/import-file',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'import_file' ),
				'permission_callback' => array( 'Envo_Blocks_REST', 'can_import' ),
				'args'                => array(
					'post_id' => array(
						'type'              => 'integer',
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'keep'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			)
		);

		register_rest_route(
			Envo_Blocks_REST::NAMESPACE_ID,
			'/import-file/(?P<token>[a-zA-Z0-9]{32})',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'kept_import' ),
				'permission_callback' => array( 'Envo_Blocks_REST', 'can_list' ),
			)
		);

		register_rest_route(
			Envo_Blocks_REST::NAMESPACE_ID,
			'/export-zip',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'export_zip' ),
				'permission_callback' => array( 'Envo_Blocks_REST', 'can_list' ),
				'args'                => array(
					'title'  => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'blocks' => array(
						'type'     => 'array',
						'required' => true,
					),
				),
			)
		);
	}

	/**
	 * POST /import-file: the uploaded file ("file") as blocks, with the products, categories and images of this
	 * site. With "keep" the result is stored for the editor and only a token is returned.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function import_file( $request ) {
		$files = $request->get_file_params();
		$file  = isset( $files['file'] ) && is_array( $files['file'] ) ? $files['file'] : null;

		if ( ! $file || ! empty( $file['error'] ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return self::error( 'envo_blocks_no_file', __( 'The file could not be uploaded. It may be larger than your server allows.', 'envo-blocks' ), 400 );
		}

		$name = isset( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : '';
		$head = (string) file_get_contents( $file['tmp_name'], false, null, 0, 4 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The uploaded temporary file.

		if ( "PK\x03\x04" === $head ) {
			$data = self::read_zip( $file['tmp_name'] );
		} else {
			$data = self::read_json( $file['tmp_name'] );
		}

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$fallback = preg_replace( '/\.(json|zip)$/i', '', $name );
		$template = Envo_Blocks_Library::parse( $data['json'], '' !== $fallback ? ucfirst( str_replace( array( '-', '_' ), ' ', $fallback ) ) : __( 'Imported page', 'envo-blocks' ) );

		Envo_Blocks_Media::use_files( $data['files'] );
		if ( ! is_wp_error( $template ) ) {
			$template['blocks'] = Envo_Blocks_Ids::remap( $template['blocks'], $template['refs'] );
			unset( $template['refs'] );
			$template['blocks'] = Envo_Blocks_Media::expand_plugin_urls( $template['blocks'] );
			$template['blocks'] = Envo_Blocks_Media::localize( $template['blocks'], (int) $request->get_param( 'post_id' ) );
			$template['blocks'] = Envo_Blocks_Media::drop_added_urls( $template['blocks'] );
		}
		Envo_Blocks_Media::use_files( array() );
		self::delete_files( $data['files'] );

		if ( is_wp_error( $template ) ) {
			return $template;
		}

		if ( $request->get_param( 'keep' ) ) {
			$token = wp_generate_password( 32, false, false );
			set_transient( self::key( $token ), $template, self::KEEP_MINUTES * MINUTE_IN_SECONDS );

			return rest_ensure_response(
				array(
					'token' => $token,
					'title' => $template['title'],
				)
			);
		}

		return rest_ensure_response( $template );
	}

	/**
	 * GET /import-file/<token>: a prepared import (once).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function kept_import( $request ) {
		$key  = self::key( (string) $request['token'] );
		$data = get_transient( $key );
		delete_transient( $key );

		if ( ! is_array( $data ) ) {
			return self::error( 'envo_blocks_import_expired', __( 'The uploaded file is no longer available. Please upload it again.', 'envo-blocks' ), 404 );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Transient of a prepared import: per user, so nobody else can take it.
	 *
	 * @param string $token Token.
	 * @return string
	 */
	private static function key( $token ) {
		return 'envo_blocks_file_' . get_current_user_id() . '_' . md5( $token );
	}

	/**
	 * Reads an uploaded JSON file.
	 *
	 * @param string $path Temporary file.
	 * @return array|WP_Error Array( json, files ).
	 */
	private static function read_json( $path ) {
		$size = filesize( $path );
		$max  = (int) apply_filters( 'envo_blocks_max_file_size', Envo_Blocks_Library::MAX_FILE_SIZE );

		if ( false === $size || $size > $max ) {
			return self::error( 'envo_blocks_file_too_large', __( 'Template file is too large.', 'envo-blocks' ), 422 );
		}

		$json = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The uploaded temporary file.

		return array(
			'json'  => false === $json ? '' : $json,
			'files' => array(),
		);
	}

	/**
	 * Reads an uploaded ZIP file: template.json (or the only JSON file in the root) and the images listed in its
	 * "files" map, extracted into temporary files.
	 *
	 * @param string $path Temporary file.
	 * @return array|WP_Error Array( json, files => image address => temporary file ).
	 */
	private static function read_zip( $path ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return self::error( 'envo_blocks_no_zip', __( 'Your server cannot open ZIP files (the PHP zip extension is missing). Upload the JSON file instead.', 'envo-blocks' ), 422 );
		}

		$size = filesize( $path );
		if ( false === $size || $size > self::MAX_ZIP ) {
			return self::error( 'envo_blocks_file_too_large', __( 'Template file is too large.', 'envo-blocks' ), 422 );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$zip = new ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			return self::error( 'envo_blocks_bad_zip', __( 'The ZIP file could not be opened.', 'envo-blocks' ), 422 );
		}

		$name = false !== $zip->locateName( self::JSON_NAME ) ? self::JSON_NAME : '';
		if ( '' === $name ) {
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				$entry = (string) $zip->getNameIndex( $i );
				if ( false === strpos( $entry, '/' ) && '.json' === strtolower( substr( $entry, -5 ) ) ) {
					$name = $entry;
					break;
				}
			}
		}

		$stat = '' === $name ? false : $zip->statName( $name );
		$max  = (int) apply_filters( 'envo_blocks_max_file_size', Envo_Blocks_Library::MAX_FILE_SIZE );
		if ( ! $stat || $stat['size'] > $max ) {
			$zip->close();
			return self::error( 'envo_blocks_zip_no_json', __( 'The ZIP file does not contain a template (template.json).', 'envo-blocks' ), 422 );
		}

		$json = (string) $zip->getFromName( $name );
		$data = json_decode( $json, true, 64 );
		$list = ( is_array( $data ) && isset( $data['files'] ) && is_array( $data['files'] ) ) ? $data['files'] : array();
		$out  = array();

		if ( $list && current_user_can( 'upload_files' ) ) {

			foreach ( array_slice( $list, 0, self::MAX_FILES, true ) as $url => $entry ) {
				if ( ! is_string( $url ) || ! is_string( $entry ) || 1 !== preg_match( '#^images/[A-Za-z0-9._-]{1,120}$#', $entry ) || 1 !== preg_match( '#^https?://[^\s]+$#i', $url ) || strlen( $url ) > 2000 ) {
					continue;
				}
				$info = $zip->statName( $entry );
				if ( ! $info || $info['size'] > Envo_Blocks_Media::MAX_BYTES ) {
					continue;
				}
				$tmp = wp_tempnam( wp_basename( $entry ) );
				if ( ! $tmp || false === file_put_contents( $tmp, $zip->getFromName( $entry ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- A temporary file.
					continue;
				}
				$out[ $url ] = $tmp;
			}
		}

		$zip->close();

		return array(
			'json'  => $json,
			'files' => $out,
		);
	}

	/**
	 * Deletes the temporary files of a ZIP import.
	 *
	 * @param array $files Image address => temporary file.
	 */
	private static function delete_files( $files ) {
		foreach ( (array) $files as $tmp ) {
			if ( is_string( $tmp ) && is_file( $tmp ) ) {
				wp_delete_file( $tmp );
			}
		}
	}

	/**
	 * POST /export-zip: a ZIP file with template.json and the images of the page, as base64 (the editor saves it).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function export_zip( $request ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return self::error( 'envo_blocks_no_zip', __( 'Your server cannot create ZIP files (the PHP zip extension is missing). Use Export as JSON instead.', 'envo-blocks' ), 422 );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$blocks = (array) $request->get_param( 'blocks' );
		$title  = (string) $request->get_param( 'title' );
		$tmp    = wp_tempnam( 'envo-blocks-export.zip' );
		$zip    = new ZipArchive();

		if ( ! $tmp || true !== $zip->open( $tmp, ZipArchive::OVERWRITE ) ) {
			return self::error( 'envo_blocks_zip_failed', __( 'The ZIP file could not be created.', 'envo-blocks' ), 500 );
		}

		$files = array();
		$used  = array();
		$total = 0;
		$temps = array();

		foreach ( array_slice( Envo_Blocks_Media::image_urls( $blocks ), 0, self::MAX_FILES ) as $url ) {
			$source = Envo_Blocks_Media::export_file( $url );
			if ( ! $source ) {
				continue;
			}
			if ( $source[1] ) {
				$temps[] = $source[0];
			}
			$size = (int) filesize( $source[0] );
			if ( $total + $size > self::MAX_EXPORT ) {
				continue;
			}

			$base = sanitize_file_name( wp_basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
			$base = preg_replace( '/[^A-Za-z0-9._-]/', '-', $base );
			$name = $base;
			for ( $n = 2; isset( $used[ $name ] ); $n++ ) {
				$name = $n . '-' . $base;
			}
			$used[ $name ] = true;

			$zip->addFile( $source[0], 'images/' . $name );
			$files[ $url ] = 'images/' . $name;
			$total        += $size;
		}

		$payload = array(
			'schema' => Envo_Blocks_Library::SCHEMA,
			'title'  => $title,
			'blocks' => $blocks,
		);
		$refs    = Envo_Blocks_Ids::refs( $blocks );
		if ( $refs ) {
			$payload['refs'] = $refs;
		}
		if ( $files ) {
			$payload['files'] = $files;
		}

		$zip->addFromString( self::JSON_NAME, (string) wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
		$zip->close();

		foreach ( $temps as $file ) {
			wp_delete_file( $file );
		}

		$content = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The ZIP file made above.
		wp_delete_file( $tmp );

		if ( false === $content ) {
			return self::error( 'envo_blocks_zip_failed', __( 'The ZIP file could not be created.', 'envo-blocks' ), 500 );
		}

		return rest_ensure_response(
			array(
				'zip'    => base64_encode( $content ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Binary file sent in a JSON response.
				'images' => count( $files ),
			)
		);
	}

	/**
	 * Error response.
	 *
	 * @param string $code    Code.
	 * @param string $message Message.
	 * @param int    $status  HTTP status.
	 * @return WP_Error
	 */
	private static function error( $code, $message, $status ) {
		return new WP_Error( $code, $message, array( 'status' => $status ) );
	}
}
