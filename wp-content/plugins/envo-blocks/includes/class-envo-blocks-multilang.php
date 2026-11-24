<?php
/**
 * Multilingual sites: WPML, Polylang and TranslatePress.
 *
 * - wpml-config.xml (read by WPML and Polylang) lists the texts of the blocks and the settings to translate, the
 *   post types and the product data.
 * - WPML and Polylang give a translated page, product or category its own ID. Settings and rules keep the IDs they
 *   were saved with, so the comparisons go through source_ids() (both sides in the default language) and the
 *   pages through translate_id() (the page in the language of the visitor).
 * - The browser scripts call the REST API of the plugins without the language of the page: rest_pre_dispatch
 *   switches to the language of the "lang" parameter or of the page that sent the request (Referer), so texts,
 *   products and prices come in the right language.
 * - Caches with texts or products get the language in their key (cache_suffix()).
 *
 * TranslatePress translates the page HTML and keeps one ID for every object: only the REST language matters there.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Multilingual support.
 */
final class Envo_Blocks_Multilang {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'rest_language' ), 5, 3 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'rest_vary' ), 10, 3 );
		add_filter( 'render_block_data', array( __CLASS__, 'block_ids' ) );
	}

	/**
	 * Attributes of the blocks of Envo Blocks and Envo Blocks PRO that hold IDs: attribute => post type or taxonomy;
	 * "list[*].key" for a key in the items of a list.
	 *
	 * @return array
	 */
	private static function id_attributes() {
		/**
		 * Block attributes with product, category or brand IDs, translated to the language of the visitor before the
		 * block renders (WPML, Polylang).
		 *
		 * @param array $map Attribute => post type or taxonomy.
		 */
		return (array) apply_filters(
			'envo_blocks_multilang_id_attributes',
			array(
				'productId'             => 'product',
				'productIds'            => 'product',
				'categoryId'            => 'product_cat',
				'categoryIds'           => 'product_cat',
				'categories'            => 'product_cat',
				'brandIds'              => 'product_brand',
				'tabs[*].categoryId'    => 'product_cat',
				'hotspots[*].productId' => 'product',
			)
		);
	}

	/**
	 * The product, category and brand IDs of a block of the plugins in the language of the visitor: a block saved
	 * with the English products shows their German translations on the German page.
	 *
	 * @param array $block Parsed block.
	 * @return array
	 */
	public static function block_ids( $block ) {
		if ( empty( $block['blockName'] ) || empty( $block['attrs'] ) || ( 0 !== strpos( $block['blockName'], 'envo-blocks/' ) && 0 !== strpos( $block['blockName'], 'envo-blocks-pro/' ) ) ) {
			return $block;
		}
		if ( ! in_array( self::plugin(), array( 'polylang', 'wpml' ), true ) || '' === self::current() ) {
			return $block;
		}
		$map = static function ( $value, $type ) {
			if ( is_array( $value ) ) {
				return array_map(
					static function ( $id ) use ( $type ) {
						return is_numeric( $id ) ? self::translate_id( (int) $id, $type ) : $id;
					},
					$value
				);
			}
			return is_numeric( $value ) && (int) $value > 0 ? self::translate_id( (int) $value, $type ) : $value;
		};
		foreach ( self::id_attributes() as $attribute => $type ) {
			if ( ! taxonomy_exists( $type ) && ! post_type_exists( $type ) ) {
				continue;
			}
			if ( preg_match( '/^(\w+)\[\*\]\.(\w+)$/', $attribute, $m ) ) {
				if ( isset( $block['attrs'][ $m[1] ] ) && is_array( $block['attrs'][ $m[1] ] ) ) {
					foreach ( $block['attrs'][ $m[1] ] as $i => $item ) {
						if ( is_array( $item ) && isset( $item[ $m[2] ] ) ) {
							$block['attrs'][ $m[1] ][ $i ][ $m[2] ] = $map( $item[ $m[2] ], $type );
						}
					}
				}
			} elseif ( isset( $block['attrs'][ $attribute ] ) ) {
				$block['attrs'][ $attribute ] = $map( $block['attrs'][ $attribute ], $type );
			}
		}
		return $block;
	}

	/**
	 * The active multilingual plugin: wpml, polylang, translatepress or ''.
	 *
	 * @return string
	 */
	public static function plugin() {
		if ( defined( 'POLYLANG_VERSION' ) && function_exists( 'pll_current_language' ) ) {
			return 'polylang'; // Before WPML: Polylang answers some WPML hooks too.
		}
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return 'wpml';
		}
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			return 'translatepress';
		}
		return '';
	}

	/**
	 * Language of the request (WPML and Polylang: the language code, TranslatePress: the locale), '' without a
	 * multilingual plugin or when the plugin shows all languages (e.g. in wp-admin).
	 *
	 * @return string
	 */
	public static function current() {
		switch ( self::plugin() ) {
			case 'polylang':
				return (string) pll_current_language( 'slug' );
			case 'wpml':
				$lang = apply_filters( 'wpml_current_language', null );
				return 'all' === $lang ? '' : (string) $lang;
			case 'translatepress':
				global $TRP_LANGUAGE; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Global of TranslatePress.
				return is_string( $TRP_LANGUAGE ) ? $TRP_LANGUAGE : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		}
		return '';
	}

	/**
	 * Default language of the site, '' without a multilingual plugin.
	 *
	 * @return string
	 */
	public static function default_language() {
		switch ( self::plugin() ) {
			case 'polylang':
				return (string) pll_default_language( 'slug' );
			case 'wpml':
				return (string) apply_filters( 'wpml_default_language', null );
			case 'translatepress':
				$settings = get_option( 'trp_settings' );
				return isset( $settings['default-language'] ) ? (string) $settings['default-language'] : '';
		}
		return '';
	}

	/**
	 * A part for cache keys of content in the language of the request: '' in the default language.
	 *
	 * @return string
	 */
	public static function cache_suffix() {
		$lang = self::current();
		return '' === $lang || self::default_language() === $lang ? '' : '_' . sanitize_key( $lang );
	}

	/**
	 * ID of a post or term in a language (WPML and Polylang): the translation, or the ID itself when it has none.
	 *
	 * @param int    $id   Post or term ID.
	 * @param string $type Post type or taxonomy.
	 * @param string $lang Language code; '' = the language of the request.
	 * @return int
	 */
	public static function translate_id( $id, $type, $lang = '' ) {
		$id = (int) $id;
		if ( ! $id ) {
			return 0;
		}
		$lang = '' !== $lang ? $lang : self::current();
		if ( '' === $lang ) {
			return $id;
		}

		switch ( self::plugin() ) {
			case 'polylang':
				if ( taxonomy_exists( $type ) ) {
					$other = pll_is_translated_taxonomy( $type ) ? pll_get_term( $id, $lang ) : 0;
				} else {
					$other = pll_is_translated_post_type( $type ) ? pll_get_post( $id, $lang ) : 0;
				}
				return $other ? (int) $other : $id;
			case 'wpml':
				return (int) apply_filters( 'wpml_object_id', $id, $type, true, $lang );
		}
		return $id;
	}

	/**
	 * IDs in the default language, for comparisons that must work in every language (a rule saved with the
	 * English category also applies to its German translation).
	 *
	 * @param int[]  $ids  Post or term IDs.
	 * @param string $type Post type or taxonomy.
	 * @return int[]
	 */
	public static function source_ids( $ids, $type ) {
		$ids  = array_map( 'intval', (array) $ids );
		$lang = self::default_language();
		if ( '' === $lang || 'translatepress' === self::plugin() ) {
			return $ids;
		}
		return array_values(
			array_unique(
				array_map(
					static function ( $id ) use ( $type, $lang ) {
						return self::translate_id( $id, $type, $lang );
					},
					$ids
				)
			)
		);
	}

	/**
	 * Whether two lists of IDs share one, in any language.
	 *
	 * @param int[]  $a    IDs.
	 * @param int[]  $b    IDs.
	 * @param string $type Post type or taxonomy.
	 * @return bool
	 */
	public static function intersects( $a, $b, $type ) {
		return (bool) array_intersect( self::source_ids( $a, $type ), self::source_ids( $b, $type ) );
	}

	/**
	 * Language code of a post (WPML, Polylang), '' when unknown or without a multilingual plugin.
	 *
	 * @param int $id Post ID.
	 * @return string
	 */
	public static function post_language( $id ) {
		switch ( self::plugin() ) {
			case 'polylang':
				return (string) pll_get_post_language( (int) $id, 'slug' );
			case 'wpml':
				$details = apply_filters( 'wpml_post_language_details', null, (int) $id );
				return is_array( $details ) && isset( $details['language_code'] ) ? (string) $details['language_code'] : '';
		}
		return '';
	}

	/**
	 * Of post IDs, those in the language of the request (and those without a language). For queries the
	 * multilingual plugin does not filter, e.g. WP_Query with post__in under Polylang.
	 *
	 * @param int[] $ids Post IDs.
	 * @return int[]
	 */
	public static function current_language_ids( $ids ) {
		$lang = self::current();
		if ( '' === $lang || ! in_array( self::plugin(), array( 'polylang', 'wpml' ), true ) ) {
			return array_map( 'intval', (array) $ids );
		}
		return array_values(
			array_filter(
				array_map( 'intval', (array) $ids ),
				static function ( $id ) use ( $lang ) {
					$own = self::post_language( $id );
					return '' === $own || $own === $lang;
				}
			)
		);
	}

	/**
	 * Query arguments that return the posts of all languages (WPML and Polylang filter by the current one).
	 *
	 * @param array $args get_posts() arguments.
	 * @return array
	 */
	public static function all_languages( array $args ) {
		switch ( self::plugin() ) {
			case 'polylang':
				$args['lang'] = '';
				break;
			case 'wpml':
				$args['suppress_filters'] = true; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.SuppressFilters_suppress_filters -- WPML: posts of all languages.
				break;
		}
		return $args;
	}

	/**
	 * Of posts in all languages, those for the language of the request: its own posts, plus the posts of the
	 * default language that have no translation into it (so an untranslated store layout or add-on group still
	 * works in every language).
	 *
	 * @param WP_Post[] $posts Posts (of one post type).
	 * @return WP_Post[]
	 */
	public static function for_current_language( array $posts ) {
		$lang = self::current();
		if ( '' === $lang || ! in_array( self::plugin(), array( 'polylang', 'wpml' ), true ) ) {
			return $posts;
		}
		$default = self::default_language();
		$out     = array();
		foreach ( $posts as $post ) {
			$own = self::post_language( $post->ID );
			if ( '' === $own || $own === $lang ) {
				$out[] = $post;
			} elseif ( $own === $default && self::translate_id( $post->ID, $post->post_type, $lang ) === (int) $post->ID ) {
				$out[] = $post;
			}
		}
		return $out;
	}

	/**
	 * Registers a text of a setting or a post (not a block) for translation: WPML String Translation, Polylang
	 * Languages › Translations. Polylang lists only the strings registered on the same admin request.
	 *
	 * @param string $name    Unique name, e.g. "Discount rule 12".
	 * @param string $value   Text in the default language.
	 * @param string $context Group.
	 */
	public static function register_string( $name, $value, $context = 'Envo Blocks' ) {
		if ( '' === trim( (string) $value ) ) {
			return;
		}
		switch ( self::plugin() ) {
			case 'polylang':
				if ( function_exists( 'pll_register_string' ) ) {
					pll_register_string( $name, (string) $value, $context );
				}
				break;
			case 'wpml':
				do_action( 'wpml_register_single_string', $context, $name, (string) $value );
				break;
		}
	}

	/**
	 * The translation of a text registered with register_string(), or the text itself.
	 *
	 * @param string $value   Text in the default language.
	 * @param string $name    Its name.
	 * @param string $context Group.
	 * @return string
	 */
	public static function translate_string( $value, $name, $context = 'Envo Blocks' ) {
		if ( '' === trim( (string) $value ) ) {
			return (string) $value;
		}
		switch ( self::plugin() ) {
			case 'polylang':
				return function_exists( 'pll__' ) ? (string) pll__( (string) $value ) : (string) $value;
			case 'wpml':
				return (string) apply_filters( 'wpml_translate_single_string', (string) $value, $context, $name );
		}
		return (string) $value;
	}

	/**
	 * Namespaces of the REST routes that the browser scripts of Envo Blocks and Envo Blocks PRO call.
	 *
	 * @return string[]
	 */
	private static function namespaces() {
		/**
		 * REST namespaces that get the language of the page that calls them.
		 *
		 * @param string[] $namespaces Namespaces.
		 */
		return (array) apply_filters( 'envo_blocks_multilang_rest_namespaces', array( 'envo-blocks/v1', 'envo-blocks-pro/v1', 'sbp/v1', 'ebp/v1' ) );
	}

	/**
	 * Switches a REST request of the plugins to the language of its "lang" parameter or of the page that sent it.
	 *
	 * @param mixed           $result  Response so far (null = not handled yet).
	 * @param WP_REST_Server  $server  Server.
	 * @param WP_REST_Request $request Request.
	 * @return mixed
	 */
	public static function rest_language( $result, $server, $request ) {
		$plugin = self::plugin();
		if ( '' === $plugin || ! $request instanceof WP_REST_Request ) {
			return $result;
		}
		if ( ! self::is_our_route( $request ) ) {
			return $result;
		}

		$lang = sanitize_text_field( (string) $request->get_param( 'lang' ) );
		if ( '' === $lang ) {
			$lang = self::language_of_url( (string) $request->get_header( 'referer' ) );
		}
		if ( '' !== $lang ) {
			self::switch_to( $lang );
		}
		return $result;
	}

	/**
	 * Responses of the plugins' routes depend on the page that asked (its language): browsers and proxies must not
	 * give one language's cached answer to another.
	 *
	 * @param WP_HTTP_Response $response Response.
	 * @param WP_REST_Server   $server   Server.
	 * @param WP_REST_Request  $request  Request.
	 * @return WP_HTTP_Response
	 */
	public static function rest_vary( $response, $server, $request ) {
		if ( '' !== self::plugin() && $response instanceof WP_HTTP_Response && $request instanceof WP_REST_Request && self::is_our_route( $request ) && '' === (string) $request->get_param( 'lang' ) ) {
			$headers = $response->get_headers();
			$vary    = isset( $headers['Vary'] ) ? $headers['Vary'] . ', Referer' : 'Referer';
			$response->header( 'Vary', $vary );
		}
		return $response;
	}

	/**
	 * Whether a REST request goes to a route of the plugins.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	private static function is_our_route( $request ) {
		$route = ltrim( $request->get_route(), '/' );
		foreach ( self::namespaces() as $namespace ) {
			if ( 0 === strpos( $route, $namespace . '/' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Language of a URL of the site: the language in the URL, or the default language (a URL without one, e.g. with
	 * "hide the default language"); '' for another site.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function language_of_url( $url ) {
		$home = preg_replace( '#^https?://#', '', home_url() );
		if ( '' === $url || false === strpos( preg_replace( '#^https?://#', '', $url ), $home ) ) {
			return '';
		}
		$lang = '';
		switch ( self::plugin() ) {
			case 'polylang':
				$lang = function_exists( 'PLL' ) && isset( PLL()->links_model ) ? (string) PLL()->links_model->get_language_from_url( $url ) : '';
				break;
			case 'wpml':
				global $sitepress;
				$lang = is_object( $sitepress ) && method_exists( $sitepress, 'get_language_from_url' ) ? (string) $sitepress->get_language_from_url( $url ) : '';
				break;
			case 'translatepress':
				$trp           = TRP_Translate_Press::get_trp_instance();
				$url_converter = $trp ? $trp->get_component( 'url_converter' ) : null;
				$lang          = $url_converter && method_exists( $url_converter, 'get_lang_from_url_string' ) ? (string) $url_converter->get_lang_from_url_string( $url ) : '';
				break;
		}
		return '' !== $lang ? $lang : self::default_language();
	}

	/**
	 * Switches the request to a language: the queries of the multilingual plugin and the WordPress locale (texts).
	 *
	 * @param string $lang Language code (TranslatePress: locale).
	 */
	public static function switch_to( $lang ) {
		$locale = '';
		switch ( self::plugin() ) {
			case 'polylang':
				$language = function_exists( 'PLL' ) && isset( PLL()->model ) ? PLL()->model->get_language( $lang ) : false;
				if ( ! $language ) {
					return;
				}
				PLL()->curlang = $language;
				$locale        = $language->locale;
				break;
			case 'wpml':
				do_action( 'wpml_switch_language', $lang );
				$languages = apply_filters( 'wpml_active_languages', null, array() );
				$locale    = isset( $languages[ $lang ]['default_locale'] ) ? $languages[ $lang ]['default_locale'] : '';
				break;
			case 'translatepress':
				global $TRP_LANGUAGE; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Global of TranslatePress.
				$settings = get_option( 'trp_settings' );
				if ( empty( $settings['translation-languages'] ) || ! in_array( $lang, (array) $settings['translation-languages'], true ) ) {
					return;
				}
				$TRP_LANGUAGE = $lang; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase, WordPress.WP.GlobalVariablesOverride.Prohibited -- The language of this request.
				$locale       = $lang;
				break;
		}
		if ( '' === $locale ) {
			return;
		}
		if ( get_locale() !== $locale && switch_to_locale( $locale ) ) {
			return; // WordPress reloads all translations.
		}
		// The WordPress language pack of the locale is not installed (switch_to_locale() needs it), or the plugin
		// already reports the new locale (TranslatePress) while the translations of the old one are loaded: at
		// least the texts of the plugins, from their language packs or their own files.
		if ( get_locale() !== $locale ) {
			add_filter(
				'locale',
				static function () use ( $locale ) {
					return $locale;
				},
				999
			);
		}
		$dirs = array( 'envo-blocks' => ENVO_BLOCKS_PATH . 'languages/' );
		if ( defined( 'EBP_PATH' ) ) {
			$dirs['envo-blocks-pro'] = EBP_PATH . 'languages/';
		}
		foreach ( $dirs as $domain => $dir ) {
			unload_textdomain( $domain, true );
			// A language pack in wp-content/languages/plugins first, then the files of the plugin.
			if ( ! load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo', $locale ) ) {
				load_textdomain( $domain, $dir . $domain . '-' . $locale . '.mo', $locale );
			}
		}
	}
}
