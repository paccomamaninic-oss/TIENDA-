<?php
/**
 * Template registry, loading, validation and sanitization.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the list of importable templates and the safe loading of their JSON files.
 */
final class Envo_Blocks_Library {

	const SCHEMA         = 'envo-blocks/v1';
	const LEGACY_SCHEMA  = 'store-blocks/v1'; // Files exported before the plugin was renamed.
	const OLDEST_SCHEMA  = 'templates-import/v1'; // Files of the first versions (core blocks only).
	const MAX_FILE_SIZE  = 1048576; // 1 MB.
	const MAX_BLOCKS     = 500;
	const MAX_DEPTH      = 16;

	/**
	 * Class of a block that only makes sense in a store (the group around a product section, the cart in a header).
	 * Without WooCommerce the import leaves such blocks out, so templates for any business work on sites without a
	 * shop too.
	 */
	const NEEDS_WOOCOMMERCE = 'envo-blocks-needs-woocommerce';
	const MAX_VALUE_DEPTH = 16;
	const MAX_ITEMS      = 1000;
	const MAX_STRING     = 200000;

	/**
	 * Runtime cache.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Returns all valid templates keyed by ID.
	 *
	 * @return array
	 */
	public static function all() {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$raw = include ENVO_BLOCKS_PATH . 'data/templates.php';
		$raw = array_merge( is_array( $raw ) ? $raw : array(), self::teasers( 'templates' ) );
		$raw = apply_filters( 'envo_blocks_list', $raw );

		$templates = array();
		foreach ( (array) $raw as $entry ) {
			$template = self::normalize( $entry );
			if ( $template ) {
				$templates[ $template['id'] ] = $template;
			}
		}

		// The newest templates first (publish date of their demo); the same date or none keeps the order of the lists.
		$order = array_flip( array_keys( $templates ) );
		uasort(
			$templates,
			static function ( $a, $b ) use ( $order ) {
				$by_date = strcmp( $b['added'], $a['added'] );
				return 0 !== $by_date ? $by_date : $order[ $a['id'] ] - $order[ $b['id'] ];
			}
		);

		self::$cache = $templates;
		return $templates;
	}

	/**
	 * Returns a single template by ID (whitelist lookup) or null.
	 *
	 * @param string $id Template ID.
	 * @return array|null
	 */
	public static function get( $id ) {
		$all = self::all();
		return ( is_string( $id ) && isset( $all[ $id ] ) ) ? $all[ $id ] : null;
	}

	/**
	 * Validates and normalizes one entry of the data file. Invalid entries are rejected.
	 *
	 * @param mixed $entry Raw entry.
	 * @return array|null
	 */
	private static function normalize( $entry ) {
		if ( ! is_array( $entry ) ) {
			return null;
		}

		$name  = isset( $entry['name'] ) ? sanitize_text_field( (string) $entry['name'] ) : '';
		$file  = isset( $entry['file'] ) ? (string) $entry['file'] : '';
		$image = isset( $entry['image'] ) ? (string) $entry['image'] : '';

		if ( '' === $name || 1 !== preg_match( '/^[a-z0-9][a-z0-9_-]{0,63}\.json$/', $file ) ) {
			return null;
		}

		if ( 1 !== preg_match( '/^[a-z0-9][a-z0-9_-]{0,63}\.(?:png|jpe?g|gif|webp|svg)$/i', $image ) ) {
			$image = '';
		}

		$link      = isset( $entry['link'] ) ? esc_url_raw( (string) $entry['link'], array( 'http', 'https' ) ) : '';
		$image_url = ( isset( $entry['image_url'] ) && is_string( $entry['image_url'] ) ) ? esc_url_raw( $entry['image_url'], array( 'http', 'https' ) ) : '';

		$pages = self::normalize_pages( isset( $entry['pages'] ) ? $entry['pages'] : array() );
		if ( ! $pages ) {
			// Without a list of pages the template is one page: its own file.
			$pages = array(
				array(
					'key'       => substr( $file, 0, -5 ),
					'name'      => $name,
					'file'      => $file,
					'image'     => $image,
					'image_url' => $image_url,
					'preview'   => self::image_name( isset( $entry['preview'] ) ? $entry['preview'] : '' ),
					'link'      => $link,
				),
			);
		}

		return array(
			'image_url'           => $image_url,
			'category'            => self::normalize_categories( isset( $entry['category'] ) ? $entry['category'] : array() ),
			'id'                  => substr( $file, 0, -5 ),
			'name'                => $name,
			'description'         => isset( $entry['description'] ) ? sanitize_text_field( (string) $entry['description'] ) : '',
			'image'               => $image,
			'link'                => $link,
			'file'                => $file,
			'premium'             => isset( $entry['premium'] ) && filter_var( $entry['premium'], FILTER_VALIDATE_BOOLEAN ),
			// Publish date of the demo (YYYY-MM-DD), for the order of the list.
			'added'               => isset( $entry['added'] ) && 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $entry['added'] ) ? (string) $entry['added'] : '',
			// A free design of the template library on our server, not bundled with this version (Envo_Blocks_Remote).
			'remote'              => ! empty( $entry['remote'] ),
			// The pages have their own header and footer: a canvas page template is preselected on import.
			'canvas'              => isset( $entry['canvas'] ) && filter_var( $entry['canvas'], FILTER_VALIDATE_BOOLEAN ),
			// The page files mark their header and footer blocks ("parts"): the import can leave them out.
			'parts'               => isset( $entry['parts'] ) && filter_var( $entry['parts'], FILTER_VALIDATE_BOOLEAN ),
			'pages'               => $pages,
			'required_plugins'    => self::normalize_dependencies( isset( $entry['required_plugins'] ) ? $entry['required_plugins'] : array() ),
			'recommended_plugins' => self::normalize_dependencies( isset( $entry['recommended_plugins'] ) ? $entry['recommended_plugins'] : array() ),
			'recommended_theme'   => self::recommended_theme( isset( $entry['recommended_theme'] ) ? $entry['recommended_theme'] : null ),
		);
	}

	/**
	 * Our free block theme, recommended with every template. Not on WordPress.org yet: "Learn more" opens its page.
	 */
	const THEME = array(
		'slug' => 'envo-blocks-store',
		'name' => 'Envo Blocks Store',
		'link' => 'https://envoblocks.com/envo-blocks-store/',
	);

	/**
	 * Recommended theme of a template: the one it names, else our theme. Our theme always gets the link to its page
	 * instead of a ZIP download.
	 *
	 * @param mixed $raw array( 'slug' => …, 'name' => … ) from the template list, or nothing.
	 * @return array
	 */
	private static function recommended_theme( $raw ) {
		$theme = current( self::normalize_dependencies( $raw ? array( $raw ) : array() ) );
		if ( ! $theme || self::THEME['slug'] === $theme['slug'] ) {
			$theme = self::THEME;
		}
		return $theme;
	}

	/**
	 * Pages of a template (homepage and subpages such as About us or FAQ). Each needs a name and a JSON file.
	 *
	 * @param mixed $raw List of pages.
	 * @return array
	 */
	private static function normalize_pages( $raw ) {
		$pages = array();

		foreach ( array_slice( is_array( $raw ) ? $raw : array(), 0, 20 ) as $page ) {
			if ( ! is_array( $page ) ) {
				continue;
			}

			$name  = isset( $page['name'] ) ? sanitize_text_field( (string) $page['name'] ) : '';
			$file  = isset( $page['file'] ) ? (string) $page['file'] : '';
			$image = isset( $page['image'] ) ? (string) $page['image'] : '';

			if ( '' === $name || 1 !== preg_match( '/^[a-z0-9][a-z0-9_-]{0,63}\.json$/', $file ) || isset( $pages[ $file ] ) ) {
				continue;
			}

			$pages[ $file ] = array(
				'key'       => substr( $file, 0, -5 ),
				'name'      => $name,
				'file'      => $file,
				'image'     => 1 === preg_match( '/^[a-z0-9][a-z0-9_-]{0,63}\.(?:png|jpe?g|gif|webp|svg)$/i', $image ) ? $image : '',
				'image_url' => isset( $page['image_url'] ) && is_string( $page['image_url'] ) ? esc_url_raw( $page['image_url'], array( 'http', 'https' ) ) : '',
				'preview'   => self::image_name( isset( $page['preview'] ) ? $page['preview'] : '' ),
				'link'      => isset( $page['link'] ) ? esc_url_raw( (string) $page['link'], array( 'http', 'https' ) ) : '',
			);
		}

		return array_values( $pages );
	}

	/**
	 * Plugins or a theme from WordPress.org: a slug (folder name on WordPress.org) and a display name.
	 *
	 * @param mixed $raw List of array( 'slug' => ..., 'name' => ... ).
	 * @return array
	 */
	private static function normalize_dependencies( $raw ) {
		$out = array();

		foreach ( array_slice( is_array( $raw ) ? $raw : array(), 0, 20 ) as $item ) {
			$slug = is_array( $item ) && isset( $item['slug'] ) ? (string) $item['slug'] : ( is_string( $item ) ? $item : '' );
			if ( 1 !== preg_match( '/^[a-z0-9][a-z0-9-]{0,99}$/', $slug ) || isset( $out[ $slug ] ) ) {
				continue;
			}

			$out[ $slug ] = array(
				'slug' => $slug,
				'name' => is_array( $item ) && ! empty( $item['name'] ) ? sanitize_text_field( (string) $item['name'] ) : $slug,
			);
			// Not on WordPress.org (yet): a ZIP the user downloads and uploads in Appearance > Themes / Plugins > Add New.
			$download = is_array( $item ) && ! empty( $item['download'] ) ? esc_url_raw( (string) $item['download'], array( 'https' ) ) : '';
			if ( '' !== $download ) {
				$out[ $slug ]['download'] = $download;
			}
		}

		return array_values( $out );
	}

	/**
	 * A valid image file name in assets/images/, or an empty string.
	 *
	 * @param mixed $name File name.
	 * @return string
	 */
	private static function image_name( $name ) {
		return is_string( $name ) && 1 === preg_match( '/^[a-z0-9][a-z0-9_-]{0,63}\.(?:png|jpe?g|gif|webp|svg)$/i', $name ) ? $name : '';
	}

	/**
	 * Preview data (sizes, placeholder) of a template or page image, when it comes from the assets server.
	 *
	 * @param array $item Template or page with "image" and "image_url".
	 * @return array|null
	 */
	private static function image_set( $item ) {
		return ( '' === $item['image_url'] && '' !== $item['image'] ) ? Envo_Blocks_Previews::get( $item['image'] ) : null;
	}

	/**
	 * URL of the long preview of a page: assets server, or a bundled file.
	 *
	 * @param string $name File name.
	 * @return string
	 */
	private static function preview_url( $name ) {
		if ( '' === $name ) {
			return '';
		}
		$remote = Envo_Blocks_Previews::url( $name );
		if ( '' !== $remote ) {
			return $remote;
		}
		return is_file( ENVO_BLOCKS_PATH . 'assets/images/' . $name ) ? esc_url_raw( ENVO_BLOCKS_URL . 'assets/images/' . rawurlencode( $name ) ) : '';
	}

	/**
	 * Image URL of a template or page (bundled image file or an absolute URL from a filter).
	 *
	 * @param array $item Template or page with "image" and "image_url".
	 * @return string
	 */
	private static function image_url( $item ) {
		if ( '' !== $item['image_url'] ) {
			return $item['image_url'];
		}

		// A preview on the assets server (or kept on this site), see Envo_Blocks_Previews.
		$remote = '' !== $item['image'] ? Envo_Blocks_Previews::url( $item['image'] ) : '';
		if ( '' !== $remote ) {
			return $remote;
		}

		if ( '' !== $item['image'] && is_file( ENVO_BLOCKS_PATH . 'assets/images/' . $item['image'] ) ) {
			return esc_url_raw( ENVO_BLOCKS_URL . 'assets/images/' . rawurlencode( $item['image'] ) );
		}

		return '';
	}

	/**
	 * Every plugin or theme slug named by a template (the installer only accepts these).
	 *
	 * @param string $type "plugin" or "theme".
	 * @return string[]
	 */
	public static function dependency_slugs( $type ) {
		$slugs = array();

		foreach ( self::all() as $template ) {
			$items = 'theme' === $type
				? array_filter( array( $template['recommended_theme'] ) )
				: array_merge( $template['required_plugins'], $template['recommended_plugins'] );

			foreach ( $items as $item ) {
				$slugs[ $item['slug'] ] = $item['slug'];
			}
		}

		if ( 'plugin' === $type ) {
			foreach ( self::SECTION_TYPES as $section_type ) {
				foreach ( self::sections( $section_type ) as $section ) {
					foreach ( $section['required_plugins'] as $item ) {
						$slugs[ $item['slug'] ] = $item['slug'];
					}
				}
			}
		}

		return array_values( $slugs );
	}

	/**
	 * Libraries of blocks: "sections" (page sections) and "presets" (ready-made settings of our blocks) are inserted
	 * into the page; "layouts" are designs of the store pages (Envo_Blocks_Layouts makes a store layout of one).
	 */
	const SECTION_TYPES = array( 'sections', 'presets', 'layouts' );

	/**
	 * Template demos on our demo site (live demo links of the PRO blocks).
	 */
	const DEMO_URL = 'https://demos.envoblocks.com/template-demos/';

	/**
	 * Runtime cache of the sections, per type.
	 *
	 * @var array
	 */
	private static $sections = array();

	/**
	 * All valid sections (single blocks or groups to insert into a page) keyed by ID.
	 *
	 * @return array
	 */
	public static function sections( $type = 'sections' ) {
		$type = in_array( $type, self::SECTION_TYPES, true ) ? $type : 'sections';
		if ( isset( self::$sections[ $type ] ) ) {
			return self::$sections[ $type ];
		}

		$file = ENVO_BLOCKS_PATH . 'data/' . $type . '.php';
		$raw  = is_file( $file ) ? include $file : array();
		$raw  = array_merge( is_array( $raw ) ? $raw : array(), self::teasers( $type ) );
		$raw  = apply_filters( 'envo_blocks_' . $type, $raw );

		self::$sections[ $type ] = array();
		foreach ( (array) $raw as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}

			$name = isset( $entry['name'] ) ? sanitize_text_field( (string) $entry['name'] ) : '';
			$file = isset( $entry['file'] ) ? (string) $entry['file'] : '';
			if ( '' === $name || 1 !== preg_match( '/^[a-z0-9][a-z0-9_-]{0,63}\.json$/', $file ) ) {
				continue;
			}

			$id = substr( $file, 0, -5 );

			self::$sections[ $type ][ $id ] = array(
				'id'               => $id,
				'type'             => $type,
				'name'             => $name,
				'file'             => $file,
				'image'            => self::image_name( isset( $entry['image'] ) ? $entry['image'] : '' ),
				'image_url'        => isset( $entry['image_url'] ) && is_string( $entry['image_url'] ) ? esc_url_raw( $entry['image_url'], array( 'http', 'https' ) ) : '',
				'category'         => self::normalize_categories( isset( $entry['category'] ) ? $entry['category'] : array() ),
				'required_plugins' => self::normalize_dependencies( isset( $entry['required_plugins'] ) ? $entry['required_plugins'] : array() ),
				'premium'          => isset( $entry['premium'] ) && filter_var( $entry['premium'], FILTER_VALIDATE_BOOLEAN ),
				'remote'           => ! empty( $entry['remote'] ),
				'link'             => isset( $entry['link'] ) && is_string( $entry['link'] ) ? esc_url_raw( $entry['link'], array( 'http', 'https' ) ) : self::demo_url( isset( $entry['demo'] ) ? $entry['demo'] : '' ),
				'preview_key'      => isset( $entry['preview_key'] ) && is_string( $entry['preview_key'] ) ? $entry['preview_key'] : '',
				// Layouts: the store page of the design and the main colour of its palette.
				'layout_type'      => isset( $entry['layout_type'] ) && class_exists( 'Envo_Blocks_Layouts' ) ? Envo_Blocks_Layouts::valid_type( $entry['layout_type'] ) : '',
				'color'            => isset( $entry['color'] ) ? Envo_Blocks_Blocks::color( $entry['color'] ) : '',
			);
			if ( 'layouts' === $type ) {
				if ( '' === self::$sections[ $type ][ $id ]['layout_type'] ) {
					unset( self::$sections[ $type ][ $id ] );
					continue;
				}
				// The live demo of a design: on this site when it can be shown here, else on our demo site.
				self::$sections[ $type ][ $id ]['link'] = Envo_Blocks_Layouts::demo_link( self::$sections[ $type ][ $id ] );
			}
		}

		return self::$sections[ $type ];
	}

	/**
	 * Address of a template demo on our demo site, from its slug ("demo" key of a block).
	 *
	 * @param string $slug Demo slug.
	 * @return string URL, or "" for an invalid slug.
	 */
	private static function demo_url( $slug ) {
		if ( ! is_string( $slug ) || 1 !== preg_match( '/^[a-z0-9][a-z0-9-]{0,63}$/', $slug ) ) {
			return '';
		}

		// On the demo site itself (Store Demos plugin) the demos are local.
		$base = post_type_exists( 'sdemo_template' ) ? home_url( '/template-demos/' ) : self::DEMO_URL;
		$base = (string) apply_filters( 'envo_blocks_demo_url', $base );

		return esc_url_raw( trailingslashit( $base ) . $slug . '/', array( 'http', 'https' ) );
	}

	/**
	 * Teasers of the PRO templates, blocks and presets (data/pro-{type}.php, a copy of the lists of the PRO
	 * plugin), listed only while the PRO plugin is not installed so the user sees everything PRO adds, locked,
	 * with a "Buy PRO" button. Their previews are on the assets server like the free ones; their files are in
	 * the PRO plugin, so they cannot be imported (load() and load_section() find no premium folder without PRO).
	 *
	 * @param string $type "templates" or a section type.
	 * @return array Raw entries.
	 */
	private static function teasers( $type ) {
		$file = ENVO_BLOCKS_PATH . 'data/pro-' . $type . '.php';
		$show = Envo_Blocks_License::STATE_NONE === Envo_Blocks_License::state();

		if ( ! apply_filters( 'envo_blocks_show_pro_teasers', $show, $type ) || ! is_file( $file ) ) {
			return array();
		}

		// The current list of the library (new PRO designs too), else the copy bundled with this version.
		$raw  = Envo_Blocks_Remote::pro_teasers( $type );
		$raw  = null === $raw ? include $file : $raw;
		$list = array();
		foreach ( is_array( $raw ) ? $raw : array() as $entry ) {
			if ( is_array( $entry ) ) {
				$entry['premium'] = true;
				$list[]           = $entry;
			}
		}

		return $list;
	}

	/**
	 * One section by ID, or null.
	 *
	 * @param string $id Section ID.
	 * @return array|null
	 */
	public static function get_section( $id, $type = 'sections' ) {
		$all = self::sections( $type );
		return is_string( $id ) && isset( $all[ $id ] ) ? $all[ $id ] : null;
	}

	/**
	 * Sections for the editor.
	 *
	 * @return array
	 */
	public static function sections_public_list( $type = 'sections' ) {
		$list = array();

		foreach ( self::sections( $type ) as $section ) {
			$folder = 'assets/images/' . $section['type'] . '/';
			$key    = '' !== $section['preview_key'] ? $section['preview_key'] : ( '' !== $section['image'] ? $section['type'] . '/' . $section['image'] : '' );
			$set    = '' !== $key ? Envo_Blocks_Previews::get( $key ) : null;
			// A preview on the assets server first; image_url (e.g. the screenshot of a layout demo) when there is none.
			$image  = $set ? $set['src'] : $section['image_url'];
			if ( '' === $image && $set ) {
				$image = $set['src'];
			} elseif ( '' === $image && '' !== $section['image'] && is_file( ENVO_BLOCKS_PATH . $folder . $section['image'] ) ) {
				$image = esc_url_raw( ENVO_BLOCKS_URL . $folder . rawurlencode( $section['image'] ) );
			}

			$list[] = array(
				'id'              => $section['id'],
				'name'            => $section['name'],
				'categories'      => $section['category'],
				'image'           => $image,
				'imageSet'        => $set && $set['src'] === $image ? $set : null,
				'requiredPlugins' => array_map( array( 'Envo_Blocks_Installer', 'plugin_status' ), $section['required_plugins'] ),
				'premium'         => $section['premium'],
				'link'            => $section['link'],
				'action'          => $section['premium'] && ! self::can_import_premium( $section ) ? Envo_Blocks_License::action() : null,
				'layoutType'      => $section['layout_type'],
				'color'           => $section['color'],
			);
		}

		return $list;
	}

	/**
	 * Validates category names: a list of short, unique, plain-text strings.
	 *
	 * @param mixed $raw Array of names (a single string is accepted too).
	 * @return string[]
	 */
	private static function normalize_categories( $raw ) {
		$raw        = is_array( $raw ) ? $raw : array( $raw );
		$categories = array();

		foreach ( array_slice( $raw, 0, 10 ) as $category ) {
			if ( ! is_scalar( $category ) ) {
				continue;
			}

			$category = mb_substr( sanitize_text_field( (string) $category ), 0, 50 );

			if ( '' !== $category && ! in_array( $category, $categories, true ) ) {
				$categories[] = $category;
			}
		}

		return $categories;
	}

	/**
	 * Data safe to expose to the editor UI.
	 *
	 * @return array
	 */
	public static function public_list() {
		$list = array();

		foreach ( self::all() as $template ) {
			$pages = array();
			foreach ( $template['pages'] as $page ) {
				$pages[] = array(
					'key'   => $page['key'],
					'name'  => $page['name'],
					'image'      => self::image_url( $page ),
					'imageSet'   => self::image_set( $page ),
					'preview'    => self::preview_url( $page['preview'] ),
					'previewSet' => '' !== $page['preview'] ? Envo_Blocks_Previews::get( $page['preview'] ) : null,
					'link'       => $page['link'],
				);
			}

			$list[] = array(
				'id'                 => $template['id'],
				'name'               => $template['name'],
				'description'        => $template['description'],
				'categories'         => $template['category'],
				'image'              => self::image_url( $template ),
				'imageSet'           => self::image_set( $template ),
				'link'               => $template['link'],
				'pages'              => $pages,
				'requiredPlugins'    => array_map( array( 'Envo_Blocks_Installer', 'plugin_status' ), $template['required_plugins'] ),
				'recommendedPlugins' => array_map( array( 'Envo_Blocks_Installer', 'plugin_status' ), $template['recommended_plugins'] ),
				'recommendedTheme'   => $template['recommended_theme'] ? Envo_Blocks_Installer::theme_status( $template['recommended_theme'] ) : null,
				'premium'            => $template['premium'],
				'canvas'             => $template['canvas'],
				'parts'              => $template['parts'],
				'action'             => $template['premium'] && ! self::can_import_premium( $template ) ? Envo_Blocks_License::action() : null,
			);
		}

		return $list;
	}

	/**
	 * One page of a template by its key, or null.
	 *
	 * @param array  $template Template (from self::get()).
	 * @param string $key      Page key (the JSON file name without .json); empty = the first page.
	 * @return array|null
	 */
	public static function page( $template, $key ) {
		foreach ( $template['pages'] as $page ) {
			if ( '' === $key || $page['key'] === $key ) {
				return $page;
			}
		}

		return null;
	}

	/**
	 * Loads, validates and sanitizes the JSON file of a template.
	 *
	 * @param array      $template Template (from self::get()).
	 * @param array|null $page     Page of the template (from self::page()), or null for the template's own file.
	 * @return array|WP_Error Array with "title" and "blocks".
	 */
	public static function load( $template, $page = null ) {
		if ( $template['premium'] ) {
			$remote = self::premium_json( $template, 'templates', $page ? $page['file'] : $template['file'], $page ? $page['name'] : $template['name'] );
			if ( null !== $remote ) {
				return $remote;
			}
		}
		if ( $template['remote'] && ! $template['premium'] ) {
			$json = Envo_Blocks_Remote::file( 'templates', $page ? $page['file'] : $template['file'] );
			return is_wp_error( $json ) ? $json : self::parse( $json, $page ? $page['name'] : $template['name'] );
		}
		$dir = $template['premium'] ? self::premium_base_dir( $template ) : ENVO_BLOCKS_PATH . 'templates';
		if ( is_wp_error( $dir ) ) {
			return $dir;
		}

		return self::load_file( $dir, $page ? $page['file'] : $template['file'], $page ? $page['name'] : $template['name'] );
	}

	/**
	 * Whether the current user may import a premium template or block.
	 *
	 * @param array $item Template or section.
	 * @return bool
	 */
	public static function can_import_premium( $item ) {
		return (bool) apply_filters( 'envo_blocks_can_import_premium', Envo_Blocks_License::is_active(), $item );
	}

	/**
	 * Asks the PRO plugin for the content of a premium file (from its remote library). Null = PRO has none to give
	 * (older PRO versions keep the files in their folder), then premium_base_dir() is asked.
	 *
	 * @param array  $item Template or section.
	 * @param string $type "templates", "sections" or "presets".
	 * @param string $file Validated file name.
	 * @param string $name Title used when the file has none.
	 * @return array|WP_Error|null Array with "title" and "blocks".
	 */
	private static function premium_json( $item, $type, $file, $name ) {
		/**
		 * Filters the JSON text of a premium file. Return the text, a WP_Error (shown in the template window; a "support"
		 * URL in its data adds a "Contact support" link) or null.
		 *
		 * @param string|WP_Error|null $json Null.
		 * @param array                $item Template or section.
		 * @param string               $type "templates", "sections" or "presets".
		 * @param string               $file File name, e.g. "nexo.json".
		 */
		$json = apply_filters( 'envo_blocks_premium_json', null, $item, $type, $file );
		if ( null === $json ) {
			return null;
		}
		if ( is_wp_error( $json ) ) {
			return $json;
		}
		return self::parse( (string) $json, $name );
	}

	/**
	 * Asks the PRO plugin for the folder with premium template files. Premium files are never read from
	 * this plugin; PRO hands over its folder only while its license is active. The folder must be inside
	 * the plugins folder.
	 *
	 * @param array  $item      Template or section.
	 * @param string $subfolder Optional subfolder (sections).
	 * @return string|WP_Error
	 */
	private static function premium_base_dir( $item, $subfolder = '' ) {
		$source  = apply_filters( 'envo_blocks_premium_source', null, $item );
		$base    = ( is_array( $source ) && isset( $source['dir'] ) && is_string( $source['dir'] ) ) ? realpath( rtrim( $source['dir'], '/\\' ) . ( $subfolder ? '/' . $subfolder : '' ) ) : false;
		$plugins = realpath( WP_PLUGIN_DIR );

		if ( false === $base || false === $plugins || 0 !== strpos( $base, $plugins . DIRECTORY_SEPARATOR ) ) {
			return new WP_Error(
				'envo_blocks_premium_unavailable',
				__( 'PRO templates are not available. Make sure the PRO plugin is active and its license is active.', 'envo-blocks' ),
				array( 'status' => 403 )
			);
		}

		return $base;
	}

	/**
	 * Loads, validates and sanitizes the JSON file of a section (from templates/sections/).
	 *
	 * @param array $section Section (from self::get_section()).
	 * @return array|WP_Error Array with "title" and "blocks".
	 */
	public static function load_section( $section ) {
		if ( 'layouts' === $section['type'] ) {
			// Layouts are not inserted into a page: "Use" makes a store layout (Envo_Blocks_Layouts::rest_create()).
			return new WP_Error( 'envo_blocks_layout', __( 'Store layouts are used from Envo Blocks › Store Layouts.', 'envo-blocks' ), array( 'status' => 400 ) );
		}
		if ( $section['premium'] ) {
			$remote = self::premium_json( $section, $section['type'], $section['file'], $section['name'] );
			if ( null !== $remote ) {
				return $remote;
			}
		}
		if ( $section['remote'] && ! $section['premium'] ) {
			$json = Envo_Blocks_Remote::file( $section['type'], $section['file'] );
			return is_wp_error( $json ) ? $json : self::parse( $json, $section['name'] );
		}
		$dir = $section['premium'] ? self::premium_base_dir( $section, $section['type'] ) : ENVO_BLOCKS_PATH . 'templates/' . $section['type'];
		if ( is_wp_error( $dir ) ) {
			return $dir;
		}

		return self::load_file( $dir, $section['file'], $section['name'] );
	}

	/**
	 * Loads, validates and sanitizes a JSON file of the plugin.
	 *
	 * @param string $folder Absolute folder: inside this plugin, or the premium folder of the PRO plugin.
	 * @param string $file   Validated file name.
	 * @param string $name   Title used when the file has none.
	 * @return array|WP_Error Array with "title" and "blocks".
	 */
	private static function load_file( $folder, $file, $name ) {
		$dir  = realpath( $folder );
		$path = ( false === $dir ) ? false : realpath( $dir . DIRECTORY_SEPARATOR . $file );

		if ( false === $dir || false === $path || 0 !== strpos( $path, $dir . DIRECTORY_SEPARATOR ) || ! is_file( $path ) || ! is_readable( $path ) ) {
			return new WP_Error( 'envo_blocks_file_missing', __( 'Template file was not found.', 'envo-blocks' ), array( 'status' => 404 ) );
		}

		$max_size = (int) apply_filters( 'envo_blocks_max_file_size', self::MAX_FILE_SIZE );
		$size     = filesize( $path );
		if ( false === $size || $size > $max_size ) {
			return new WP_Error( 'envo_blocks_file_too_large', __( 'Template file is too large.', 'envo-blocks' ), array( 'status' => 422 ) );
		}

		// Local, validated file path inside the plugin directory (not a remote URL).
		$json = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		return self::parse( false === $json ? '' : $json, $name );
	}

	/**
	 * Validates and sanitizes the JSON of a template: a file of the library, or a file the user uploaded
	 * ("Import from file"). The same checks apply to both.
	 *
	 * @param string $json JSON text.
	 * @param string $name Title used when the file has none.
	 * @return array|WP_Error Array with "title", "blocks" and "refs".
	 */
	public static function parse( $json, $name ) {
		$data = '' === $json ? null : json_decode( $json, true, 64 );

		if ( ! is_array( $data ) || JSON_ERROR_NONE !== json_last_error() ) {
			return new WP_Error( 'envo_blocks_invalid_json', __( 'Template file is not valid JSON.', 'envo-blocks' ), array( 'status' => 422 ) );
		}

		if ( ! isset( $data['schema'] ) || ! in_array( $data['schema'], array( self::SCHEMA, self::LEGACY_SCHEMA, self::OLDEST_SCHEMA ), true ) || ! isset( $data['blocks'] ) ) {
			return new WP_Error( 'envo_blocks_invalid_schema', __( 'Template file has an unsupported format.', 'envo-blocks' ), array( 'status' => 422 ) );
		}

		if ( self::LEGACY_SCHEMA === $data['schema'] ) {
			$data['blocks'] = self::rename_legacy_blocks( $data['blocks'] );
		}

		$count = 0;
		$parts = self::raw_parts( $data, is_array( $data['blocks'] ) ? count( $data['blocks'] ) : 0 );
		if ( $parts ) {
			// One top-level block at a time, so the indexes of the header and footer follow blocks that are dropped.
			$blocks = array();
			$map    = array();
			foreach ( array_values( $data['blocks'] ) as $i => $raw ) {
				$one = self::sanitize_blocks( array( $raw ), 0, $count );
				if ( is_wp_error( $one ) ) {
					return $one;
				}
				$map[ $i ] = array();
				foreach ( $one as $block ) {
					$map[ $i ][] = count( $blocks );
					$blocks[]    = $block;
				}
			}
			foreach ( $parts as $key => $list ) {
				$moved = array();
				foreach ( $list as $i ) {
					$moved = array_merge( $moved, $map[ $i ] );
				}
				$parts[ $key ] = $moved;
			}
		} else {
			$blocks = self::sanitize_blocks( $data['blocks'], 0, $count );
			if ( is_wp_error( $blocks ) ) {
				return $blocks;
			}
		}

		$title = ( isset( $data['title'] ) && is_string( $data['title'] ) ) ? sanitize_text_field( $data['title'] ) : $name;

		return array(
			'title'  => $title,
			'blocks' => $blocks,
			// Slugs of the products and categories the template uses (matched on import).
			'refs'   => Envo_Blocks_Ids::sanitize_refs( isset( $data['refs'] ) ? $data['refs'] : array() ),
			// Top-level blocks of the header and the footer of the page (empty: the file does not say).
			'parts'  => $parts ? $parts : array(),
		);
	}

	/**
	 * Header and footer blocks named by a template file: "parts": { "header": [ 0, 1 ], "footer": [ 9 ] } (top-level
	 * indexes, made by the library generator from the header and footer of the demo).
	 *
	 * @param array $data  Decoded file.
	 * @param int   $count Number of top-level blocks.
	 * @return array array( header => int[], footer => int[] ), or an empty array.
	 */
	private static function raw_parts( $data, $count ) {
		if ( empty( $data['parts'] ) || ! is_array( $data['parts'] ) ) {
			return array();
		}
		$out = array();
		foreach ( array( 'header', 'footer' ) as $key ) {
			$list = isset( $data['parts'][ $key ] ) && is_array( $data['parts'][ $key ] ) ? $data['parts'][ $key ] : array();
			$out[ $key ] = array_values(
				array_unique(
					array_filter(
						array_map( 'intval', $list ),
						static function ( $i ) use ( $count ) {
							return $i >= 0 && $i < $count;
						}
					)
				)
			);
		}
		return $out['header'] || $out['footer'] ? $out : array();
	}

	/**
	 * Blocks of a loaded page without its header and footer.
	 *
	 * @param array $data Loaded page (blocks, parts).
	 * @return array The same data, the blocks named in "parts" removed.
	 */
	public static function without_parts( $data ) {
		if ( empty( $data['parts'] ) ) {
			return $data;
		}
		$drop = array_flip( array_merge( $data['parts']['header'], $data['parts']['footer'] ) );
		$data['blocks'] = array_values(
			array_filter(
				$data['blocks'],
				static function ( $i ) use ( $drop ) {
					return ! isset( $drop[ $i ] );
				},
				ARRAY_FILTER_USE_KEY
			)
		);
		return $data;
	}

	/**
	 * Whether a block of a template is left out because it needs WooCommerce, which is not active
	 * (see self::NEEDS_WOOCOMMERCE).
	 *
	 * @param array $block Raw block.
	 * @return bool
	 */
	private static function skipped_without_woocommerce( $block ) {
		if ( class_exists( 'WooCommerce' ) || empty( $block['attributes']['className'] ) || ! is_string( $block['attributes']['className'] ) ) {
			return false;
		}

		return in_array( self::NEEDS_WOOCOMMERCE, preg_split( '/\s+/', trim( $block['attributes']['className'] ) ), true );
	}

	/**
	 * Recursively validates and sanitizes a list of blocks.
	 *
	 * @param mixed $blocks Blocks.
	 * @param int   $depth  Current depth.
	 * @param int   $count  Running block counter.
	 * @return array|WP_Error
	 */
	private static function sanitize_blocks( $blocks, $depth, &$count ) {
		if ( ! is_array( $blocks ) || $depth > self::MAX_DEPTH ) {
			return self::invalid( __( 'Template structure is invalid.', 'envo-blocks' ) );
		}

		$registry = WP_Block_Type_Registry::get_instance();
		$clean    = array();

		foreach ( $blocks as $block ) {
			$count++;
			if ( $count > self::MAX_BLOCKS ) {
				return self::invalid( __( 'Template contains too many blocks.', 'envo-blocks' ) );
			}

			if ( ! is_array( $block ) || ! isset( $block['name'] ) || ! is_string( $block['name'] ) ) {
				return self::invalid( __( 'Template contains an invalid block.', 'envo-blocks' ) );
			}

			if ( self::skipped_without_woocommerce( $block ) ) {
				continue;
			}

			$name = $block['name'];
			if ( 1 !== preg_match( '/^[a-z0-9-]+\/[a-z0-9-]+$/', $name ) || ! $registry->is_registered( $name ) ) {
				return self::invalid( __( 'Template uses a block that is not available on this site.', 'envo-blocks' ) );
			}

			$attributes = isset( $block['attributes'] ) ? $block['attributes'] : array();
			$inner      = isset( $block['innerBlocks'] ) ? $block['innerBlocks'] : array();

			if ( ! is_array( $attributes ) ) {
				return self::invalid( __( 'Template contains invalid block attributes.', 'envo-blocks' ) );
			}

			$attributes = self::sanitize_value( $attributes, 0 );
			if ( is_wp_error( $attributes ) ) {
				return $attributes;
			}

			$inner = self::sanitize_blocks( $inner, $depth + 1, $count );
			if ( is_wp_error( $inner ) ) {
				return $inner;
			}

			$clean[] = array(
				'name'        => $name,
				'attributes'  => $attributes,
				'innerBlocks' => $inner,
			);
		}

		return $clean;
	}

	/**
	 * Recursively validates and sanitizes an attribute value.
	 *
	 * Rejects (instead of silently fixing) anything unexpected.
	 *
	 * @param mixed $value Value.
	 * @param int   $depth Current depth.
	 * @return mixed|WP_Error
	 */
	private static function sanitize_value( $value, $depth ) {
		if ( null === $value || is_bool( $value ) || is_int( $value ) ) {
			return $value;
		}

		if ( is_float( $value ) ) {
			return is_finite( $value ) ? $value : self::invalid( __( 'Template contains an invalid number.', 'envo-blocks' ) );
		}

		if ( is_string( $value ) ) {
			if ( strlen( $value ) > self::MAX_STRING ) {
				return self::invalid( __( 'Template contains a value that is too long.', 'envo-blocks' ) );
			}

			$value = wp_check_invalid_utf8( $value );

			if ( 1 === preg_match( '/^\s*(?:javascript|vbscript):/i', $value ) ) {
				return self::invalid( __( 'Template contains an unsafe URL.', 'envo-blocks' ) );
			}

			// Anything that can hold markup is filtered with the "post" allow-list.
			return ( false !== strpos( $value, '<' ) ) ? wp_kses_post( $value ) : $value;
		}

		if ( is_array( $value ) ) {
			if ( $depth >= self::MAX_VALUE_DEPTH || count( $value ) > self::MAX_ITEMS ) {
				return self::invalid( __( 'Template structure is too complex.', 'envo-blocks' ) );
			}

			$clean = array();
			foreach ( $value as $key => $item ) {
				if ( is_string( $key ) && 1 !== preg_match( '/^[A-Za-z0-9_:\-]{1,64}$/', $key ) ) {
					return self::invalid( __( 'Template contains an invalid attribute name.', 'envo-blocks' ) );
				}

				$item = self::sanitize_value( $item, $depth + 1 );
				if ( is_wp_error( $item ) ) {
					return $item;
				}

				$clean[ $key ] = $item;
			}
			return $clean;
		}

		return self::invalid( __( 'Template contains an unsupported value.', 'envo-blocks' ) );
	}

	/**
	 * Old exports use the block namespace of the plugin before the rename (store-blocks/…).
	 *
	 * @param mixed $blocks Blocks.
	 * @return mixed
	 */
	private static function rename_legacy_blocks( $blocks ) {
		if ( ! is_array( $blocks ) ) {
			return $blocks;
		}

		foreach ( $blocks as $index => $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			if ( isset( $block['name'] ) && is_string( $block['name'] ) && 0 === strpos( $block['name'], 'store-blocks/' ) ) {
				$blocks[ $index ]['name'] = 'envo-blocks/' . substr( $block['name'], 13 );
			}

			if ( isset( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = self::rename_legacy_blocks( $block['innerBlocks'] );
			}
		}

		return $blocks;
	}

	/**
	 * Builds a validation error.
	 *
	 * @param string $message Message.
	 * @return WP_Error
	 */
	private static function invalid( $message ) {
		return new WP_Error( 'envo_blocks_invalid_template', $message, array( 'status' => 422 ) );
	}
}
