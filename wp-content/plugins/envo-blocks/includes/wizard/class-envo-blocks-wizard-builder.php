<?php
/**
 * Builds the store from a template package (templates/*.json):
 *   stage – one draft page per template page: products / categories matched to this store
 *           (Envo_Blocks_Ids::remap) and the images copied into the Media Library (Envo_Blocks_Media::localize);
 *   build – publishes the pages, links them together, sets the front page, the store name and the colours;
 *   undo  – puts back the theme, front page, name and colours from before the wizard and trashes its pages.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builder.
 */
final class Envo_Blocks_Wizard_Builder {
	/**
	 * Wizard state: template, pages (key => ID), built, colors, name.
	 */
	const STATE = 'envo_blocks_wizard_state';

	/**
	 * Site settings from before the first change of the wizard (for undo).
	 */
	const SNAPSHOT = 'envo_blocks_wizard_snapshot';

	/**
	 * Marks the pages made by the wizard.
	 */
	const PAGE_META = '_envo_blocks_wizard_page';

	/**
	 * Prepared content of a page (images copied, IDs matched, placeholders kept): every build starts from it, so
	 * building again (new colours, other choices) never cuts the header twice.
	 */
	const STAGED_META = '_envo_blocks_wizard_staged';

	/**
	 * Header / footer used for the whole site: area => blocks (written into the theme parts, again after a switch).
	 */
	const PARTS = 'envo_blocks_wizard_parts';

	/**
	 * Template parts as they were before the wizard: theme => slug => array( id, content, created ).
	 */
	const PARTS_BACKUP = 'envo_blocks_wizard_parts_backup';

	/**
	 * The templates of the wizard (data/wizard/index.php, made with the packages): slug => brand / accent colour,
	 * number of header / footer blocks and the pages (key => title).
	 *
	 * @return array
	 */
	public static function index() {
		static $index = null;
		if ( null === $index ) {
			$file  = ENVO_BLOCKS_PATH . 'data/wizard/index.php';
			$index = is_file( $file ) ? (array) include $file : array();
		}
		return $index;
	}

	/**
	 * The templates for the template step: those of the Templates Import (name, description, categories, Free / PRO)
	 * that have a wizard package. PRO ones are locked without an active Envo Blocks PRO license.
	 *
	 * @return array slug => data (without the page contents).
	 */
	public static function templates() {
		$list  = array();
		$index = self::index();
		foreach ( Envo_Blocks_Library::all() as $lib ) {
			$slug = $lib['id'];
			if ( ! isset( $index[ $slug ] ) ) {
				continue;
			}
			$t       = $index[ $slug ];
			$premium = ! empty( $lib['premium'] );
			$locked  = $premium && ! Envo_Blocks_Library::can_import_premium( $lib );
			$keys    = array_map( 'strval', array_keys( $t['pages'] ) );
			$list[ $slug ] = array(
				'slug'       => $slug,
				'name'       => $lib['name'],
				'tagline'    => $lib['description'],
				'preview'    => '',
				'pages'      => array_values( $t['pages'] ),
				'keys'       => $keys,
				'thumbs'     => array_fill( 0, count( $keys ), '' ),
				// Same previews as the Templates Import (rosso.webp, rosso-about.webp…): served from this site once
				// they were kept, with a tiny blurred placeholder meanwhile.
				'image'      => self::preview_set( $slug . '.webp' ),
				'images'     => array_map(
					static function ( $key ) use ( $slug ) {
						return self::preview_set( 'home' === $key ? $slug . '.webp' : $slug . '-' . $key . '.webp' );
					},
					$keys
				),
				'parts'      => array( 'header' => (int) $t['header'], 'footer' => (int) $t['footer'] ),
				'brand'      => (string) $t['brand'],
				'accent'     => (string) $t['accent'],
				'demo'       => $lib['link'],
				'categories' => array_values( (array) $lib['category'] ),
				'premium'    => $premium,
				'locked'     => $locked,
				'action'     => $locked ? Envo_Blocks_License::action() : null,
			);
		}
		return $list;
	}

	/**
	 * Preview image of the free plugin's manifest (src, srcset, lqip, key, remote), or null.
	 *
	 * @param string $key Manifest key.
	 * @return array|null
	 */
	private static function preview_set( $key ) {
		return Envo_Blocks_Previews::get( $key );
	}

	/**
	 * One template package with the page contents: free ones from data/wizard/, PRO ones from the template library
	 * of Envo Blocks PRO (only with an active license).
	 *
	 * @param string $slug Slug.
	 * @return array|WP_Error
	 */
	public static function load_template( $slug ) {
		$slug  = sanitize_key( $slug );
		$entry = '' !== $slug && isset( self::index()[ $slug ] ) ? Envo_Blocks_Library::get( $slug ) : null;
		if ( ! $entry ) {
			return new WP_Error( 'ebw_template', __( 'Template was not found.', 'envo-blocks' ), array( 'status' => 404 ) );
		}
		if ( ! empty( $entry['premium'] ) ) {
			if ( ! Envo_Blocks_Library::can_import_premium( $entry ) ) {
				return new WP_Error( 'ebw_premium', __( 'This template needs an active Envo Blocks PRO license.', 'envo-blocks' ), array( 'status' => 403 ) );
			}
			$json = apply_filters( 'envo_blocks_premium_json', null, $entry, 'wizard', $slug . '.json' );
			if ( is_wp_error( $json ) ) {
				return $json;
			}
		} else {
			$file = ENVO_BLOCKS_PATH . 'data/wizard/' . $slug . '.json';
			$json = is_file( $file ) ? file_get_contents( $file ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file of this plugin.
		}
		$data = is_string( $json ) ? json_decode( $json, true ) : null;
		if ( ! is_array( $data ) || empty( $data['pages'] ) || ! is_array( $data['pages'] ) || empty( $data['palette'] ) || ! is_array( $data['palette'] ) ) {
			return new WP_Error( 'ebw_template', __( 'Template was not found.', 'envo-blocks' ), array( 'status' => 404 ) );
		}
		$data['slug'] = $slug;
		return $data;
	}

	/**
	 * One template package, or null.
	 *
	 * @param string $slug Slug.
	 * @return array|null
	 */
	public static function template( $slug ) {
		$data = self::load_template( $slug );
		return is_wp_error( $data ) ? null : $data;
	}

	/**
	 * Wizard state.
	 *
	 * @return array
	 */
	public static function state() {
		$state = get_option( self::STATE );
		return wp_parse_args( is_array( $state ) ? $state : array(), array( 'template' => '', 'pages' => array(), 'built' => false, 'colors' => array(), 'name' => '' ) );
	}

	/**
	 * Package of the template in the state.
	 *
	 * @return array|null
	 */
	public static function state_template() {
		$state = self::state();
		if ( ! $state['template'] ) {
			return null;
		}
		// Kept when the template was prepared: the palette and the header / footer counts are needed on every page
		// of the preview and after the build, and a PRO package would have to come from the library each time.
		if ( ! empty( $state['package']['palette'] ) ) {
			return $state['package'];
		}
		$template = self::template( $state['template'] );
		return $template ? array_diff_key( $template, array( 'pages' => 1 ) ) : null;
	}

	/**
	 * Saves the site settings the wizard may change – once, before its first change.
	 */
	public static function snapshot() {
		if ( get_option( self::SNAPSHOT ) ) {
			return;
		}
		update_option(
			self::SNAPSHOT,
			array(
				'stylesheet'    => get_stylesheet(),
				'show_on_front' => get_option( 'show_on_front' ),
				'page_on_front' => (int) get_option( 'page_on_front' ),
				'blogname'      => get_option( 'blogname' ),
				'site_logo'     => (int) get_option( 'site_logo' ),
			),
			false
		);
	}

	/**
	 * Switches the theme (and the canvas template of the wizard's pages to the one of that theme).
	 *
	 * @param string $stylesheet Theme.
	 * @return true|WP_Error
	 */
	public static function use_theme( $stylesheet ) {
		$theme = wp_get_theme( $stylesheet );
		if ( ! $theme->exists() ) {
			return new WP_Error( 'ebw_theme', __( 'This theme is not installed.', 'envo-blocks' ) );
		}
		self::snapshot();
		if ( get_stylesheet() !== $stylesheet ) {
			switch_theme( $stylesheet );
		}
		self::after_switch_theme();
		return true;
	}

	/**
	 * A theme switched anywhere (Appearance › Themes too): the wizard's pages get the canvas of the new theme,
	 * otherwise they would show the theme's header above the template's own.
	 *
	 * @param string $name New theme name.
	 */
	public static function after_switch_theme() {
		$state = self::state();
		$modes = isset( $state['parts'] ) ? $state['parts'] : array();
		foreach ( $state['pages'] as $id ) {
			update_post_meta( $id, '_wp_page_template', self::page_template( $modes ) );
		}
		// The header / footer chosen for the whole site go into the new theme too.
		$parts = get_option( self::PARTS );
		if ( is_array( $parts ) ) {
			foreach ( $parts as $area => $content ) {
				self::write_parts( $area, $content );
			}
		}
	}

	/**
	 * Page template of the wizard's pages for the chosen header / footer modes. The theme's header / footer part is
	 * shown for "site" (the part now holds the template's header) and for "none" (the theme's own header stays);
	 * only "pages" – the header is in the page content – needs a template without that part. No mode (a page that
	 * is only prepared, header still in its content) counts as "pages".
	 *
	 * @param array $modes 'header' / 'footer' => site | pages | none.
	 * @return string
	 */
	public static function page_template( $modes ) {
		$h = isset( $modes['header'] ) && in_array( $modes['header'], array( 'site', 'none' ), true );
		$f = isset( $modes['footer'] ) && in_array( $modes['footer'], array( 'site', 'none' ), true );
		if ( $h && $f ) {
			return 'envo-blocks-parts';
		}
		if ( $h || $f ) {
			return $h ? 'envo-blocks-parts-header' : 'envo-blocks-parts-footer';
		}
		return self::canvas();
	}

	/**
	 * Block templates of the wizard (block themes): the page content with the theme's header and / or footer part,
	 * without a page title – used when the template's header / footer became the site's header / footer.
	 */
	public static function register_templates() {
		if ( ! function_exists( 'register_block_template' ) || ! wp_is_block_theme() ) {
			return;
		}
		$main = '<!-- wp:group {"tagName":"main","align":"full","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained"}} -->'
			. '<main class="wp-block-group alignfull" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">'
			. '<!-- wp:post-content {"align":"full","style":{"spacing":{"blockGap":"0","padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"100%"}} /-->'
			. '</main><!-- /wp:group -->';
		$header = '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->';
		$footer = '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->';
		$list   = array(
			'envo-blocks-parts'        => array( __( 'Store page (site header and footer)', 'envo-blocks' ), $header . $main . $footer ),
			'envo-blocks-parts-header' => array( __( 'Store page (site header only)', 'envo-blocks' ), $header . $main ),
			'envo-blocks-parts-footer' => array( __( 'Store page (site footer only)', 'envo-blocks' ), $main . $footer ),
		);
		foreach ( $list as $slug => $t ) {
			register_block_template(
				'envo-blocks//' . $slug,
				array(
					'title'       => $t[0],
					'description' => __( 'Page content without a title, between the header and footer of the site.', 'envo-blocks' ),
					'content'     => $t[1],
					'post_types'  => array( 'page' ),
				)
			);
		}
	}

	/**
	 * Splits a template page into header blocks, body and footer blocks (first / last top-level blocks).
	 *
	 * @param string $content Serialized blocks.
	 * @param int    $header  Number of header blocks.
	 * @param int    $footer  Number of footer blocks.
	 * @return array( header, body, footer ) serialized.
	 */
	public static function split( $content, $header, $footer ) {
		$named = array_values( array_filter( parse_blocks( $content ), function ( $b ) { return null !== $b['blockName']; } ) );
		$count = count( $named );
		$h     = min( $header, $count );
		$f     = min( $footer, $count - $h );
		return array(
			serialize_blocks( array_slice( $named, 0, $h ) ),
			serialize_blocks( array_slice( $named, $h, $count - $h - $f ) ),
			serialize_blocks( array_slice( $named, $count - $f ) ),
		);
	}

	/**
	 * Template parts of the active theme in an area: "header" / "footer" and those its theme.json lists there
	 * (Envo Blocks Store also uses header-store, header-simple…), so every page of the site gets the same one.
	 *
	 * @param string $area header | footer.
	 * @return array slug => title.
	 */
	private static function theme_parts( $area ) {
		$list = array( $area => ucfirst( $area ) );
		if ( class_exists( 'WP_Theme_JSON_Resolver' ) ) {
			foreach ( WP_Theme_JSON_Resolver::get_theme_data()->get_template_parts() as $slug => $part ) {
				if ( isset( $part['area'] ) && $area === $part['area'] ) {
					$list[ $slug ] = isset( $part['title'] ) ? $part['title'] : $slug;
				}
			}
		}
		return $list;
	}

	/**
	 * Writes the header / footer into the template parts of the active theme (a saved part of the theme, like an
	 * edit in the Site Editor). What was there before is kept for undo.
	 *
	 * @param string $area    header | footer.
	 * @param string $content Blocks.
	 */
	private static function write_parts( $area, $content ) {
		$theme  = get_stylesheet();
		$backup = get_option( self::PARTS_BACKUP );
		$backup = is_array( $backup ) ? $backup : array();
		foreach ( self::theme_parts( $area ) as $slug => $title ) {
			$existing = get_block_template( $theme . '//' . $slug, 'wp_template_part' );
			if ( $existing && $existing->wp_id ) {
				if ( ! isset( $backup[ $theme ][ $slug ] ) ) {
					$backup[ $theme ][ $slug ] = array( 'id' => (int) $existing->wp_id, 'content' => $existing->content, 'created' => false );
				}
				wp_update_post( array( 'ID' => $existing->wp_id, 'post_content' => wp_slash( $content ) ) );
				$backup[ $theme ][ $slug ]['hash'] = self::content_hash( $existing->wp_id );
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'wp_template_part',
					'post_status'  => 'publish',
					'post_name'    => $slug,
					'post_title'   => $title,
					'post_content' => wp_slash( $content ),
				),
				true
			);
			if ( ! is_wp_error( $id ) ) {
				wp_set_object_terms( $id, $theme, 'wp_theme' );
				wp_set_object_terms( $id, $area, 'wp_template_part_area' );
				$backup[ $theme ][ $slug ] = array( 'id' => $id, 'content' => null, 'created' => true, 'hash' => self::content_hash( $id ) );
			}
		}
		update_option( self::PARTS_BACKUP, $backup, false );
	}

	/**
	 * Fingerprint of a post's content as WordPress stored it (saving normalises the HTML a little, so the text the
	 * wizard sent is not what is in the database).
	 *
	 * @param int $id Post.
	 * @return string
	 */
	private static function content_hash( $id ) {
		return md5( trim( (string) get_post_field( 'post_content', $id ) ) );
	}

	/**
	 * Puts the template parts of every theme back as they were before the wizard.
	 *
	 * @return int Parts restored or removed.
	 */
	private static function restore_parts() {
		$backup = get_option( self::PARTS_BACKUP );
		$n      = 0;
		foreach ( is_array( $backup ) ? $backup : array() as $theme => $parts ) {
			foreach ( $parts as $slug => $b ) {
				if ( $b['created'] ) {
					// A part the wizard created: without it the theme's own file is used again.
					wp_delete_post( $b['id'], true );
				} else {
					wp_update_post( array( 'ID' => $b['id'], 'post_content' => wp_slash( $b['content'] ) ) );
				}
				$n++;
			}
		}
		delete_option( self::PARTS_BACKUP );
		delete_option( self::PARTS );
		return $n;
	}

	/**
	 * Page template without the theme's header and footer (the templates bring their own).
	 *
	 * @param string $stylesheet Theme.
	 * @return string
	 */
	public static function canvas( $stylesheet = '' ) {
		return Envo_Blocks_Wizard::OUR_THEME === ( $stylesheet ? $stylesheet : get_stylesheet() ) ? 'page-canvas' : 'envo-blocks-canvas';
	}

	/**
	 * Prepares one page of a template as a draft. Index 0 starts over: earlier unbuilt drafts are trashed.
	 *
	 * @param string $slug  Template.
	 * @param int    $index Page index.
	 * @return array|WP_Error Progress.
	 */
	public static function stage( $slug, $index ) {
		$template = self::load_template( $slug );
		if ( is_wp_error( $template ) ) {
			return $template;
		}
		if ( ! isset( $template['pages'][ $index ] ) ) {
			return new WP_Error( 'ebw_template', __( 'Template was not found.', 'envo-blocks' ) );
		}
		$state = self::state();
		if ( 0 === $index ) {
			self::snapshot();
			if ( ! $state['built'] ) {
				self::trash_pages( $state['pages'] );
			}
			$state = array_merge(
				$state,
				array(
					'template' => $template['slug'],
					'package'  => array(
						'slug'    => $template['slug'],
						'header'  => isset( $template['header'] ) ? (int) $template['header'] : 0,
						'footer'  => isset( $template['footer'] ) ? (int) $template['footer'] : 0,
						'palette' => $template['palette'],
					),
					'pages'    => array(),
					'built'    => false,
					'colors'   => array(),
				)
			);
		}
		$page = $template['pages'][ $index ];
		$id   = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'post_title'  => 'home' === $page['key'] ? __( 'Home', 'envo-blocks' ) : $page['title'],
				'post_name'   => 'home' === $page['key'] ? 'home' : $page['key'],
				'meta_input'  => array(
					self::PAGE_META     => $page['key'],
					'_wp_page_template' => self::canvas(),
				),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$content = self::prepare( $page['content'], isset( $page['refs'] ) ? $page['refs'] : array(), $id );
		wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $content ) ) );
		update_post_meta( $id, self::STAGED_META, wp_slash( $content ) );
		if ( 'home' === $page['key'] || empty( $state['logo_sig'] ) ) {
			$state['logo_sig'] = self::find_logo( $content, isset( $template['header'] ) ? (int) $template['header'] : 0 );
		}
		$state['pages'][ $page['key'] ] = $id;
		update_option( self::STATE, $state, false );
		return array(
			'done'  => $index + 1,
			'total' => count( $template['pages'] ),
			'title' => $page['title'],
			'home'  => isset( $state['pages']['home'] ) ? $state['pages']['home'] : 0,
			'pages' => $state['pages'],
		);
	}

	/**
	 * Content of a template page for this store: product / category IDs matched, images copied.
	 *
	 * @param string $content Serialized blocks.
	 * @param array  $refs    Demo ID → slug of products, categories, brands.
	 * @param int    $post_id Page the images are attached to.
	 * @return string
	 */
	private static function prepare( $content, $refs, $post_id ) {
		$blocks = parse_blocks( $content );
		// The import code works with the editor's block format (name / attributes / innerBlocks).
		$editor = self::to_editor( $blocks );
		if ( class_exists( 'Envo_Blocks_Ids' ) ) {
			$editor = Envo_Blocks_Ids::remap( $editor, Envo_Blocks_Ids::sanitize_refs( $refs ) );
		}
		if ( class_exists( 'Envo_Blocks_Media' ) ) {
			// Images in attributes: copied, and "<prefix>Url" next to "<prefix>Id" gives the ID of the copy.
			$editor = Envo_Blocks_Media::localize( $editor, $post_id );
		}
		$blocks  = self::strip_media_keys( self::attrs_back( $blocks, $editor ) );
		$content = serialize_blocks( $blocks );
		if ( class_exists( 'Envo_Blocks_Media' ) ) {
			// Images in the saved HTML (one pseudo block holding the whole content).
			$out     = Envo_Blocks_Media::localize( array( array( 'name' => 'core/html', 'attributes' => array( 'content' => $content ), 'innerBlocks' => array() ) ), $post_id );
			$content = isset( $out[0]['attributes']['content'] ) ? $out[0]['attributes']['content'] : $content;
		}
		return $content;
	}

	/**
	 * Removes the image addresses the package added next to attachment IDs (listed in "ebwMedia").
	 *
	 * @param array $blocks parse_blocks() tree.
	 * @return array
	 */
	private static function strip_media_keys( $blocks ) {
		foreach ( $blocks as $i => $b ) {
			if ( isset( $b['attrs']['ebwMedia'] ) ) {
				foreach ( (array) $b['attrs']['ebwMedia'] as $key ) {
					unset( $blocks[ $i ]['attrs'][ $key ] );
				}
				unset( $blocks[ $i ]['attrs']['ebwMedia'] );
			}
			$blocks[ $i ]['innerBlocks'] = self::strip_media_keys( $b['innerBlocks'] );
		}
		return $blocks;
	}

	/**
	 * parse_blocks() tree → editor format.
	 *
	 * @param array $blocks Blocks.
	 * @return array
	 */
	private static function to_editor( $blocks ) {
		$out = array();
		foreach ( $blocks as $b ) {
			if ( $b['blockName'] ) {
				$out[] = array( 'name' => $b['blockName'], 'attributes' => $b['attrs'], 'innerBlocks' => self::to_editor( $b['innerBlocks'] ) );
			}
		}
		return $out;
	}

	/**
	 * Copies the remapped attributes back into the parse_blocks() tree (same order of named blocks).
	 *
	 * An image copied into the Media Library gets a new attachment ID in "id" (core/image, core/cover); the saved
	 * HTML of the block names the ID too (class "wp-image-<id>"), so it is changed there as well – otherwise the
	 * editor reports the block as invalid.
	 *
	 * @param array $blocks Blocks.
	 * @param array $mapped Editor format.
	 * @return array
	 */
	private static function attrs_back( $blocks, $mapped ) {
		$i = 0;
		foreach ( $blocks as $k => $b ) {
			if ( ! $b['blockName'] ) {
				continue;
			}
			if ( isset( $mapped[ $i ] ) ) {
				$new = (array) $mapped[ $i ]['attributes'];
				if ( isset( $b['attrs']['id'], $new['id'] ) && is_int( $b['attrs']['id'] ) && (int) $new['id'] !== $b['attrs']['id'] ) {
					foreach ( $b['innerContent'] as $n => $part ) {
						if ( is_string( $part ) ) {
							$blocks[ $k ]['innerContent'][ $n ] = preg_replace( '/\bwp-image-' . $b['attrs']['id'] . '\b/', 'wp-image-' . (int) $new['id'], $part );
						}
					}
				}
				$blocks[ $k ]['attrs']       = $new;
				$blocks[ $k ]['innerBlocks'] = self::attrs_back( $b['innerBlocks'], $mapped[ $i ]['innerBlocks'] );
			}
			$i++;
		}
		return $blocks;
	}

	/**
	 * Builds the store: publishes the chosen pages, links them, front page, header / footer, name, colours.
	 *
	 * @param array  $colors 'brand' / 'accent' => #rrggbb.
	 * @param string $name   Store name ('' keeps it).
	 * @param array  $opts   'pages' => keys to import (default all), 'header' / 'footer' => site | pages | none.
	 * @return array|WP_Error
	 */
	public static function build( $colors, $name, $opts = array() ) {
		$state    = self::state();
		$template = self::state_template();
		if ( ! $template || ! $state['pages'] ) {
			return new WP_Error( 'envo_blocks_wizard_state', __( 'Choose a template first.', 'envo-blocks' ) );
		}
		$keys = isset( $opts['pages'] ) && is_array( $opts['pages'] ) ? array_values( array_intersect( array_keys( $state['pages'] ), $opts['pages'] ) ) : array_keys( $state['pages'] );
		if ( ! $keys ) {
			return new WP_Error( 'ebw_pages', __( 'Choose at least one page to import.', 'envo-blocks' ), array( 'status' => 400 ) );
		}
		$modes = array();
		foreach ( array( 'header', 'footer' ) as $area ) {
			$modes[ $area ] = isset( $opts[ $area ] ) && in_array( $opts[ $area ], array( 'site', 'pages', 'none' ), true ) ? $opts[ $area ] : 'site';
		}
		self::snapshot();
		$name = trim( wp_strip_all_tags( (string) $name ) );
		$logo = isset( $opts['logo'] ) && wp_attachment_is_image( (int) $opts['logo'] ) ? (int) $opts['logo'] : 0;

		// Pages not chosen: to the trash (the template's links to them go to the front page).
		$skipped = array_diff_key( $state['pages'], array_flip( $keys ) );
		self::trash_pages( $skipped );
		$state['pages'] = array_intersect_key( $state['pages'], array_flip( $keys ) );

		foreach ( $state['pages'] as $key => $id ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish', 'post_parent' => 0 ) );
		}
		// Links between the pages of the template: real addresses now that the pages are published.
		$map = array(
			'{{home}}'  => trailingslashit( home_url() ),
			'{{site}}/' => trailingslashit( home_url() ),
		);
		foreach ( $state['pages'] as $key => $id ) {
			$map[ '{{page:' . $key . '}}' ] = get_permalink( $id );
		}
		$links = function ( $content ) use ( $map ) {
			// Links to pages that were not imported go to the front page.
			return preg_replace( '/\{\{page:[a-z0-9-]+\}\}/', $map['{{home}}'], strtr( $content, $map ) );
		};
		$hn = isset( $template['header'] ) ? (int) $template['header'] : 0;
		$fn = isset( $template['footer'] ) ? (int) $template['footer'] : 0;
		foreach ( $state['pages'] as $key => $id ) {
			$staged = get_post_meta( $id, self::STAGED_META, true );
			$staged = '' !== $staged ? $staged : get_post_field( 'post_content', $id );
			list( $head, $body, $foot ) = self::split( $staged, $hn, $fn );
			// The header / footer stay in the page only when chosen so ("only on these pages").
			$content = ( 'pages' === $modes['header'] ? $head : '' ) . $body . ( 'pages' === $modes['footer'] ? $foot : '' );
			$content = self::brand_content( $content, self::state_logo_sig(), $name, $logo );
			wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $links( $content ) ) ) );
			update_post_meta( $id, '_wp_page_template', self::page_template( $modes ) );
		}

		// Header / footer for the whole site: from the template's home page, into the theme's template parts.
		$home_staged = isset( $state['pages']['home'] ) ? get_post_meta( $state['pages']['home'], self::STAGED_META, true ) : '';
		if ( '' === $home_staged ) {
			$first       = reset( $state['pages'] );
			$home_staged = get_post_meta( $first, self::STAGED_META, true );
		}
		list( $head, , $foot ) = self::split( $home_staged, $hn, $fn );
		self::restore_parts();
		$parts = array();
		if ( 'site' === $modes['header'] && '' !== trim( $head ) ) {
			$parts['header'] = $links( self::brand_content( $head, self::state_logo_sig(), $name, $logo ) );
		}
		if ( 'site' === $modes['footer'] && '' !== trim( $foot ) ) {
			$parts['footer'] = $links( $foot );
		}
		foreach ( $parts as $area => $content ) {
			self::write_parts( $area, $content );
		}
		update_option( self::PARTS, $parts, false );

		if ( isset( $state['pages']['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $state['pages']['home'] );
		}
		$state['parts'] = $modes;
		if ( '' !== $name ) {
			update_option( 'blogname', $name );
		}
		// The logo is also the site logo (Site Logo block, the theme's own header).
		if ( $logo ) {
			update_option( 'site_logo', $logo );
			set_theme_mod( 'custom_logo', $logo );
		}
		$state['logo'] = $logo;
		$colors = array_filter( array_intersect_key( (array) $colors, array( 'brand' => 1, 'accent' => 1 ) ), array( 'Envo_Blocks_Wizard_Palette', 'rgb' ) );
		update_option( Envo_Blocks_Wizard_Palette::OPTION, Envo_Blocks_Wizard_Palette::recolor( $template['palette'], $colors ) );
		$state['built']    = true;
		$state['built_at'] = current_time( 'mysql', true );
		$state['colors'] = $colors;
		$state['name']   = $name;
		$state['theme']  = get_stylesheet();
		update_option( self::STATE, $state, false );
		delete_option( 'envo_blocks_wizard_notice_dismissed' );
		$first = isset( $state['pages']['home'] ) ? $state['pages']['home'] : reset( $state['pages'] );
		return array( 'url' => isset( $state['pages']['home'] ) ? home_url( '/' ) : get_permalink( $first ), 'edit' => admin_url( 'post.php?post=' . (int) $first . '&action=edit' ), 'parts' => array_keys( $parts ) );
	}

	/**
	 * Puts the site back as it was before the wizard.
	 *
	 * @return array What was done.
	 */
	public static function undo() {
		$state = self::state();
		$snap  = get_option( self::SNAPSHOT );
		$done  = array( 'pages' => self::trash_pages( $state['pages'] ) );
		// Before a theme switch: after_switch_theme() must not write the parts into the old theme again.
		$done['parts'] = self::restore_parts();
		$done['store'] = Envo_Blocks_Wizard_Store::undo();
		delete_option( self::STATE );
		if ( is_array( $snap ) ) {
			if ( get_stylesheet() !== $snap['stylesheet'] && wp_get_theme( $snap['stylesheet'] )->exists() ) {
				switch_theme( $snap['stylesheet'] );
				$done['theme'] = $snap['stylesheet'];
			}
			update_option( 'show_on_front', $snap['show_on_front'] );
			update_option( 'page_on_front', $snap['page_on_front'] );
			update_option( 'blogname', $snap['blogname'] );
			if ( isset( $snap['site_logo'] ) ) {
				if ( $snap['site_logo'] ) {
					update_option( 'site_logo', $snap['site_logo'] );
					set_theme_mod( 'custom_logo', $snap['site_logo'] );
				} else {
					delete_option( 'site_logo' );
					remove_theme_mod( 'custom_logo' );
				}
			}
		}
		delete_option( Envo_Blocks_Wizard_Palette::OPTION );
		delete_option( self::STATE );
		delete_option( self::SNAPSHOT );
		return $done;
	}

	/**
	 * Moves the wizard's pages to the trash (they can still be restored there).
	 *
	 * @param array $ids Page IDs.
	 * @return int Count.
	 */
	private static function trash_pages( $ids ) {
		$n = 0;
		foreach ( (array) $ids as $id ) {
			if ( get_post_meta( $id, self::PAGE_META, true ) && wp_trash_post( $id ) ) {
				$n++;
			}
		}
		return $n;
	}

	/* ---------------------------------------------------------------------------------------------------------- */
	/* Store name and logo in the template's header                                                              */
	/* ---------------------------------------------------------------------------------------------------------- */

	/**
	 * Signature of a block for finding the logo again in other pages: tag, style and text of its HTML.
	 *
	 * @param array $block Parsed block.
	 * @return string
	 */
	private static function logo_sig( $block ) {
		$html = trim( (string) $block['innerHTML'] );
		if ( ! preg_match( '/^<(p|h[1-6])\b[^>]*?(?:style="([^"]*)")?[^>]*>/i', $html, $m ) ) {
			return '';
		}
		$text = trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' ) ) );
		return md5( strtolower( $m[1] ) . '|' . ( isset( $m[2] ) ? $m[2] : '' ) . '|' . $text );
	}

	/**
	 * The logo of a template: in the header blocks, the first paragraph / heading with a link and a big font
	 * (24 px or more – the logo text: "ROSSO", "bitbox", "zentro.").
	 *
	 * @param string $content Page content.
	 * @param int    $header  Number of header blocks.
	 * @return string Signature, or ''.
	 */
	public static function find_logo( $content, $header ) {
		$named = array_values( array_filter( parse_blocks( $content ), function ( $b ) { return null !== $b['blockName']; } ) );
		$found = '';
		$walk  = function ( $blocks ) use ( &$walk, &$found ) {
			foreach ( $blocks as $b ) {
				if ( '' !== $found ) {
					return;
				}
				if ( in_array( $b['blockName'], array( 'core/paragraph', 'core/heading' ), true ) && false !== stripos( $b['innerHTML'], '<a ' ) ) {
					$size = isset( $b['attrs']['style']['typography']['fontSize'] ) ? (string) $b['attrs']['style']['typography']['fontSize'] : '';
					preg_match_all( '/(\d+(?:\.\d+)?)px/', $size, $px );
					if ( $px[1] && max( array_map( 'floatval', $px[1] ) ) >= 24 ) {
						$found = self::logo_sig( $b );
						return;
					}
				}
				$walk( $b['innerBlocks'] );
			}
		};
		$walk( array_slice( $named, 0, max( 0, (int) $header ) ) );
		return $found;
	}

	/**
	 * New HTML of the logo paragraph: the store name in the template's lettering, or the logo image.
	 *
	 * Name: the link text becomes the name; a coloured part of the template's logo ("ROSS<O>", "bit<box>") goes to
	 * the last word of the name (the last letter of a one-word name); a gradient text (Vyra) wraps the whole name;
	 * an icon without letters (Marigold's ☀) stays. Logo: an image in the link, as high as the lettering.
	 *
	 * @param string $html    Paragraph HTML.
	 * @param string $name    Store name ('' keeps the text).
	 * @param int    $logo_id Attachment ID of the logo (0 = text).
	 * @param float  $font    Font size of the logo in px.
	 * @return string
	 */
	public static function logo_html( $html, $name, $logo_id, $font ) {
		if ( ! preg_match( '/^(\s*<(?:p|h[1-6])\b[^>]*>)(.*)(<\/(?:p|h[1-6])>\s*)$/is', $html, $m ) ) {
			return $html;
		}
		list( , $open, $inner, $close ) = $m;
		if ( ! preg_match( '/^(.*?<a\b[^>]*>)(.*?)(<\/a>)(.*)$/is', $inner, $a ) ) {
			return $html;
		}
		list( , $before, $link, $end, $after ) = $a;
		$name = trim( (string) $name );

		if ( $logo_id && wp_attachment_is_image( $logo_id ) ) {
			$src    = wp_get_attachment_image_url( $logo_id, 'medium' );
			$height = (int) max( 32, min( 64, round( $font * 1.35 ) ) );
			$img    = '<img class="wp-image-' . (int) $logo_id . '" style="height:' . $height . 'px;width:auto;vertical-align:middle" src="' . esc_url( $src ) . '" alt="' . esc_attr( '' !== $name ? $name : get_option( 'blogname' ) ) . '">';
			// Decoration after the link ("/" of Apex, "." of Zentro) belongs to the lettering, not to an image logo.
			return $open . $before . $img . $end . $close;
		}
		if ( '' === $name ) {
			return $html;
		}

		$safe = esc_html( $name );
		// Gradient lettering: one inline element with a background clipped to the text – the whole name goes in it.
		if ( preg_match( '/^\s*(<(span|mark|strong|em)\b[^>]*background[^>]*>).*<\/\2>\s*$/is', $link, $g ) ) {
			return $open . $before . $g[1] . $safe . '</' . $g[2] . '>' . $end . $after . $close;
		}
		// Icon without letters at the start (☀): kept.
		$icon = '';
		if ( preg_match( '/^\s*(<(span|mark|strong|em)\b[^>]*>[^<a-z0-9]*<\/\2>\s*)/iu', $link, $i ) ) {
			$icon = $i[1];
			$link = substr( $link, strlen( $i[0] ) );
		}
		// Coloured part with letters: its opening tag wraps the last word (or the last letter) of the name.
		if ( preg_match( '/<(span|mark|strong|em)\b([^>]*(?:color|class)[^>]*)>[^<]*[a-z0-9][^<]*<\/\1>/iu', $link, $c ) ) {
			$words = preg_split( '/(\s+)/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE );
			if ( count( $words ) > 1 ) {
				$last = array_pop( $words );
				$head = implode( '', $words );
			} else {
				$head = mb_substr( $name, 0, -1 );
				$last = mb_substr( $name, -1 );
			}
			return $open . $before . $icon . esc_html( $head ) . '<' . $c[1] . $c[2] . '>' . esc_html( $last ) . '</' . $c[1] . '>' . $end . $after . $close;
		}
		return $open . $before . $icon . $safe . $end . $after . $close;
	}

	/**
	 * Puts the store name / logo into every logo paragraph (same signature) of a content.
	 *
	 * @param string $content Content.
	 * @param string $sig     Logo signature.
	 * @param string $name    Name.
	 * @param int    $logo_id Logo attachment.
	 * @return string
	 */
	public static function brand_content( $content, $sig, $name, $logo_id ) {
		if ( '' === $sig || ( '' === trim( (string) $name ) && ! $logo_id ) ) {
			return $content;
		}
		$walk = function ( $blocks ) use ( &$walk, $sig, $name, $logo_id ) {
			foreach ( $blocks as $i => $b ) {
				if ( in_array( $b['blockName'], array( 'core/paragraph', 'core/heading' ), true ) && self::logo_sig( $b ) === $sig ) {
					$html = self::logo_html( $b['innerHTML'], $name, $logo_id, self::font_px( $b ) );
					$blocks[ $i ]['innerHTML']    = $html;
					$blocks[ $i ]['innerContent'] = array( $html );
				}
				$blocks[ $i ]['innerBlocks'] = $walk( $b['innerBlocks'] );
			}
			return $blocks;
		};
		return serialize_blocks( $walk( parse_blocks( $content ) ) );
	}

	/**
	 * Biggest font size of a block in px (28 when unknown).
	 *
	 * @param array $block Block.
	 * @return float
	 */
	private static function font_px( $block ) {
		$size = isset( $block['attrs']['style']['typography']['fontSize'] ) ? (string) $block['attrs']['style']['typography']['fontSize'] : '';
		preg_match_all( '/(\d+(?:\.\d+)?)px/', $size, $px );
		return $px[1] ? max( array_map( 'floatval', $px[1] ) ) : 28;
	}

	/**
	 * Logo signature of the prepared template; worked out from the staged home page when a template was prepared
	 * before the wizard knew about logos.
	 *
	 * @return string
	 */
	public static function state_logo_sig() {
		$state = self::state();
		if ( ! empty( $state['logo_sig'] ) ) {
			return $state['logo_sig'];
		}
		$template = self::state_template();
		if ( ! $template || ! $state['pages'] ) {
			return '';
		}
		$id      = isset( $state['pages']['home'] ) ? $state['pages']['home'] : reset( $state['pages'] );
		$content = get_post_meta( $id, self::STAGED_META, true );
		$content = '' !== $content ? $content : get_post_field( 'post_content', $id );
		$state['logo_sig'] = self::find_logo( $content, isset( $template['header'] ) ? (int) $template['header'] : 0 );
		update_option( self::STATE, $state, false );
		return $state['logo_sig'];
	}

	/**
	 * Preview (?ebw_name / ?ebw_logo): the logo paragraph shows the name / logo being typed or chosen.
	 *
	 * @param string $html  Rendered block.
	 * @param array  $block Parsed block.
	 * @return string
	 */
	public static function preview_logo( $html, $block ) {
		static $on = null;
		if ( null === $on ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview for editors.
			$on = ( isset( $_GET['ebw_name'] ) || isset( $_GET['ebw_logo'] ) ) && current_user_can( 'edit_theme_options' ) ? self::state_logo_sig() : '';
		}
		if ( '' === $on || ! in_array( $block['blockName'], array( 'core/paragraph', 'core/heading' ), true ) || self::logo_sig( $block ) !== $on ) {
			return $html;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$name = isset( $_GET['ebw_name'] ) ? sanitize_text_field( wp_unslash( $_GET['ebw_name'] ) ) : '';
		$logo = isset( $_GET['ebw_logo'] ) ? absint( $_GET['ebw_logo'] ) : 0;
		// phpcs:enable
		return self::logo_html( $html, $name, $logo, self::font_px( $block ) );
	}

	/**
	 * What "Undo everything" will do, before it is done: the list of things put back, and warnings for what was
	 * changed by hand after the wizard (those changes would be lost).
	 *
	 * @return array items, warnings (texts).
	 */
	public static function undo_preview() {
		$state = self::state();
		$snap  = get_option( self::SNAPSHOT );
		$built = isset( $state['built_at'] ) ? $state['built_at'] : '';
		$items = array();
		$warn  = array();

		// Pages.
		if ( $state['pages'] ) {
			$items[] = sprintf( /* translators: %d: number of pages */ _n( '%d page of the template goes to the trash (you can restore it there).', '%d pages of the template go to the trash (you can restore them there).', count( $state['pages'] ), 'envo-blocks' ), count( $state['pages'] ) );
			foreach ( $state['pages'] as $id ) {
				if ( '' !== $built && get_post_field( 'post_modified_gmt', $id ) > $built ) {
					/* translators: %s: page title */
					$warn[] = sprintf( __( 'You edited the page “%s” after the wizard – it goes to the trash too.', 'envo-blocks' ), get_the_title( $id ) );
				}
			}
		}
		if ( is_array( $snap ) ) {
			if ( get_stylesheet() !== $snap['stylesheet'] && wp_get_theme( $snap['stylesheet'] )->exists() ) {
				/* translators: %s: theme name */
				$items[] = sprintf( __( 'The theme is switched back to %s.', 'envo-blocks' ), wp_get_theme( $snap['stylesheet'] )->get( 'Name' ) );
			}
			if ( get_option( 'show_on_front' ) !== $snap['show_on_front'] || (int) get_option( 'page_on_front' ) !== (int) $snap['page_on_front'] ) {
				$items[] = __( 'The front page is set back as it was.', 'envo-blocks' );
			}
			if ( get_option( 'blogname' ) !== $snap['blogname'] ) {
				/* translators: %s: site name */
				$items[] = sprintf( __( 'The site name goes back to “%s”.', 'envo-blocks' ), $snap['blogname'] );
				if ( ! empty( $state['name'] ) && get_option( 'blogname' ) !== $state['name'] ) {
					/* translators: %s: site name */
					$warn[] = sprintf( __( 'You renamed the site to “%s” after the wizard – that name is replaced too.', 'envo-blocks' ), get_option( 'blogname' ) );
				}
			}
			if ( isset( $snap['site_logo'] ) && (int) get_option( 'site_logo' ) !== (int) $snap['site_logo'] ) {
				$items[] = $snap['site_logo'] ? __( 'The site logo goes back to the previous one.', 'envo-blocks' ) : __( 'The site logo is removed (the site had none).', 'envo-blocks' );
			}
		}

		// Header / footer parts.
		$parts  = get_option( self::PARTS );
		$backup = get_option( self::PARTS_BACKUP );
		if ( is_array( $backup ) && $backup ) {
			$items[] = __( 'The header and footer of the theme are put back.', 'envo-blocks' );
			foreach ( $backup as $theme => $list ) {
				foreach ( $list as $slug => $b ) {
					if ( ! empty( $b['hash'] ) && get_post( $b['id'] ) && self::content_hash( $b['id'] ) !== $b['hash'] ) {
						/* translators: %s: template part name, e.g. header */
						$warn[] = sprintf( __( 'You edited the template part “%s” in the Site Editor after the wizard – those edits are lost.', 'envo-blocks' ), $slug );
					}
				}
			}
		}
		if ( get_option( Envo_Blocks_Wizard_Palette::OPTION ) ) {
			$items[] = __( 'The brand colours of the template are removed.', 'envo-blocks' );
		}

		$store = Envo_Blocks_Wizard_Store::undo_preview();
		return array(
			'items'    => array_merge( $items, $store['items'] ),
			'warnings' => array_merge( $warn, $store['warnings'] ),
		);
	}
}
