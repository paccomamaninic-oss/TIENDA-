<?php
/**
 * REST API endpoints.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST routes. Cookie authentication + the X-WP-Nonce header (sent automatically by
 * wp.apiFetch in the editor) validate the request; capabilities are checked per route.
 */
final class Envo_Blocks_REST {

	const NAMESPACE_ID = 'envo-blocks/v1';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Registers routes.
	 */
	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE_ID,
			'/templates',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_templates' ),
				'permission_callback' => array( __CLASS__, 'can_list' ),
			)
		);

		register_rest_route(
			self::NAMESPACE_ID,
			'/import',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'import_template' ),
				'permission_callback' => array( __CLASS__, 'can_import' ),
				'args'                => array(
					'id'      => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => array( __CLASS__, 'validate_id' ),
					),
					'post_id' => array(
						'type'              => 'integer',
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'page'    => array(
						'type'              => 'string',
						'required'          => false,
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => array( __CLASS__, 'validate_page' ),
					),
					'media'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
					// False: the header and footer blocks of the template are left out (the theme shows its own).
					'parts'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_ID,
			'/sections',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_sections' ),
				'permission_callback' => array( __CLASS__, 'can_list' ),
				'args'                => array(
					'type' => array(
						'type'    => 'string',
						'default' => 'sections',
						'enum'    => Envo_Blocks_Library::SECTION_TYPES,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_ID,
			'/section',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'get_section' ),
				'permission_callback' => array( __CLASS__, 'can_import' ),
				'args'                => array(
					'id'      => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => array( __CLASS__, 'validate_id' ),
					),
					'post_id' => array(
						'type'              => 'integer',
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'type'    => array(
						'type'    => 'string',
						'default' => 'sections',
						'enum'    => Envo_Blocks_Library::SECTION_TYPES,
					),
					'media'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_ID,
			'/preview-cache',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'keep_previews' ),
				'permission_callback' => array( __CLASS__, 'can_list' ),
				'args'                => array(
					'keys' => array(
						'type'     => 'array',
						'required' => true,
						'items'    => array( 'type' => 'string' ),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_ID,
			'/refs',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'get_refs' ),
				'permission_callback' => array( __CLASS__, 'can_list' ),
				'args'                => array(
					'blocks' => array(
						'type'     => 'array',
						'required' => true,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_ID,
			'/preview',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'save_preview' ),
				'permission_callback' => array( __CLASS__, 'can_preview' ),
				'args'                => array(
					'title'   => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'content'  => array(
						'type'     => 'string',
						'required' => true,
					),
					'template' => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_ID,
			'/dependency',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'install_dependency' ),
				'permission_callback' => array( __CLASS__, 'can_install' ),
				'args'                => array(
					'type'     => array(
						'type'     => 'string',
						'required' => true,
						'enum'     => array( 'plugin', 'theme' ),
					),
					'slug'     => array(
						'type'              => 'string',
						'required'          => true,
						'validate_callback' => array( __CLASS__, 'validate_slug' ),
					),
					'activate' => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
			)
		);
	}

	/**
	 * GET /sections (type "sections" or "presets").
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function get_sections( $request ) {
		return rest_ensure_response( Envo_Blocks_Library::sections_public_list( (string) $request->get_param( 'type' ) ) );
	}

	/**
	 * POST /section: the sanitized blocks of one section, to insert where the user is editing.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_section( $request ) {
		$section = Envo_Blocks_Library::get_section( (string) $request->get_param( 'id' ), (string) $request->get_param( 'type' ) );
		if ( ! $section ) {
			return new WP_Error( 'envo_blocks_not_found', __( 'The block was not found.', 'envo-blocks' ), array( 'status' => 404 ) );
		}

		if ( $section['premium'] && ! Envo_Blocks_Library::can_import_premium( $section ) ) {
			return new WP_Error( 'envo_blocks_premium', __( 'An active PRO license is required to insert this block.', 'envo-blocks' ), array( 'status' => 403 ) );
		}

		$data = Envo_Blocks_Library::load_section( $section );

		return is_wp_error( $data ) ? $data : rest_ensure_response( self::with_media( $data, $request ) );
	}

	/**
	 * POST /preview-cache: keeps copies of previews the template window has shown, so the next time they load
	 * from this site instead of the assets server.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function keep_previews( $request ) {
		return rest_ensure_response( array( 'kept' => Envo_Blocks_Previews::keep( (array) $request->get_param( 'keys' ) ) ) );
	}

	/**
	 * POST /refs: slugs of the products, categories and brands used in blocks ("Export as JSON" adds them to
	 * the file, so an import on another site can find the same items).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function get_refs( $request ) {
		$refs = Envo_Blocks_Ids::refs( (array) $request->get_param( 'blocks' ) );
		return rest_ensure_response( $refs ? $refs : new stdClass() );
	}

	/**
	 * The preview is a draft page of the current user.
	 *
	 * @return bool
	 */
	public static function can_preview() {
		return current_user_can( 'edit_pages' );
	}

	/**
	 * POST /preview: saves the page into the user's temporary preview draft and returns its preview URL.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function save_preview( $request ) {
		$content = (string) $request->get_param( 'content' );
		if ( strlen( $content ) > Envo_Blocks_Library::MAX_STRING * 5 ) {
			return new WP_Error( 'envo_blocks_preview_too_large', __( 'The page is too large for a preview.', 'envo-blocks' ), array( 'status' => 413 ) );
		}

		$url = Envo_Blocks_Preview::save( (string) $request->get_param( 'title' ), $content, (string) $request->get_param( 'template' ) );

		return is_wp_error( $url ) ? $url : rest_ensure_response( array( 'url' => esc_url_raw( $url ) ) );
	}

	/**
	 * Installing needs one of the plugin or theme capabilities; the installer checks each step again.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public static function can_install( $request ) {
		if ( 'theme' === $request->get_param( 'type' ) ) {
			return current_user_can( 'install_themes' ) || current_user_can( 'switch_themes' );
		}

		return current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' );
	}

	/**
	 * Validates a WordPress.org slug.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function validate_slug( $value ) {
		return is_string( $value ) && 1 === preg_match( '/^[a-z0-9][a-z0-9-]{0,99}$/', $value );
	}

	/**
	 * Validates a page key (empty = the first page).
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function validate_page( $value ) {
		return '' === $value || self::validate_id( $value );
	}

	/**
	 * POST /dependency: installs and activates a plugin or theme named by a template.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function install_dependency( $request ) {
		$slug     = (string) $request->get_param( 'slug' );
		$activate = (bool) $request->get_param( 'activate' );

		$result = 'theme' === $request->get_param( 'type' )
			? Envo_Blocks_Installer::install_theme( $slug, $activate )
			: Envo_Blocks_Installer::install_plugin( $slug, $activate );

		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	/**
	 * Capability check for listing templates.
	 *
	 * @return bool
	 */
	public static function can_list() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Capability check for importing: the user must be able to edit the target post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public static function can_import( $request ) {
		$post_id = absint( $request->get_param( 'post_id' ) );

		if ( $post_id ) {
			return current_user_can( 'edit_post', $post_id );
		}

		return current_user_can( 'edit_posts' );
	}

	/**
	 * Validates the template ID format.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function validate_id( $value ) {
		return is_string( $value ) && 1 === preg_match( '/^[a-z0-9][a-z0-9_-]{0,63}$/', $value );
	}

	/**
	 * GET /templates.
	 *
	 * @return WP_REST_Response
	 */
	public static function get_templates() {
		return rest_ensure_response( Envo_Blocks_Library::public_list() );
	}

	/**
	 * POST /import.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function import_template( $request ) {
		$template = Envo_Blocks_Library::get( (string) $request->get_param( 'id' ) );

		if ( ! $template ) {
			return new WP_Error( 'envo_blocks_not_found', __( 'Template was not found.', 'envo-blocks' ), array( 'status' => 404 ) );
		}

		if ( $template['premium'] && ! Envo_Blocks_Library::can_import_premium( $template ) ) {
			return new WP_Error( 'envo_blocks_premium', __( 'An active PRO license is required to import this template.', 'envo-blocks' ), array( 'status' => 403 ) );
		}

		$page = Envo_Blocks_Library::page( $template, (string) $request->get_param( 'page' ) );
		if ( ! $page ) {
			return new WP_Error( 'envo_blocks_not_found', __( 'This page of the template was not found.', 'envo-blocks' ), array( 'status' => 404 ) );
		}

		$data = Envo_Blocks_Library::load( $template, $page );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		if ( false === $request->get_param( 'parts' ) ) {
			// Before the images are copied: those of the header and footer are not needed.
			$data = Envo_Blocks_Library::without_parts( $data );
		}
		unset( $data['parts'] );

		return rest_ensure_response( self::with_media( $data, $request ) );
	}

	/**
	 * Images of the loaded blocks: the plugin placeholder becomes the plugin URL and, unless "media" is
	 * false (preview), the images are copied into the Media Library so the page does not load them from
	 * the demo server.
	 *
	 * @param array           $data    Loaded data with "blocks".
	 * @param WP_REST_Request $request Request.
	 * @return array
	 */
	private static function with_media( $data, $request ) {
		// Products, categories and brands of the demo site -> the same (or similar) items of this store.
		$data['blocks'] = Envo_Blocks_Ids::remap( $data['blocks'], isset( $data['refs'] ) ? $data['refs'] : array() );
		unset( $data['refs'] );

		$data['blocks'] = Envo_Blocks_Media::expand_plugin_urls( $data['blocks'] );

		if ( false !== $request->get_param( 'media' ) ) {
			$data['blocks'] = Envo_Blocks_Media::localize( $data['blocks'], (int) $request->get_param( 'post_id' ) );
		}
		$data['blocks'] = Envo_Blocks_Media::drop_added_urls( $data['blocks'] );

		return $data;
	}
}
