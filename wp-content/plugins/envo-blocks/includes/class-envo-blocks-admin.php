<?php
/**
 * Envo Blocks admin pages: welcome, blocks, guides and Envo Blocks PRO.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * A top-level "Envo Blocks" menu with four pages that share one header and tabs. The pages only show
 * information and links; they save nothing.
 */
final class Envo_Blocks_Admin {

	const SLUG = 'envo-blocks';

	/**
	 * The Settings page: every setting of Envo Blocks (and PRO) with a sidebar.
	 */
	const SETTINGS = 'envo-blocks-settings';

	/**
	 * Content from data/admin.php.
	 *
	 * @var array|null
	 */
	private static $data = null;

	/**
	 * Pages added by other plugins (filter envo_blocks_admin_pages), validated.
	 *
	 * @var array|null
	 */
	private static $extra = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu_order' ), 999 );
		add_filter( 'submenu_file', array( __CLASS__, 'submenu_file' ) );
		add_filter( 'parent_file', array( __CLASS__, 'parent_file' ) );
		add_filter( 'admin_title', array( __CLASS__, 'admin_title' ), 10, 2 );
		add_action( 'all_admin_notices', array( __CLASS__, 'settings_list_bar' ) );
		add_action( 'admin_init', array( __CLASS__, 'activation_redirect' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'woo_admin_notice' ) );
		add_action( 'wp_ajax_envo_blocks_dismiss_woo', array( __CLASS__, 'dismiss_woo_notice' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( ENVO_BLOCKS_PATH . 'envo-blocks.php' ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Activation of the plugin (register_activation_hook): the next admin page opens Templates Import, once.
	 *
	 * @param bool $network_wide Activated for the whole network.
	 */
	public static function activated( $network_wide = false ) {
		// A new install (or an activation again) needs no "What's new" of this version (Envo_Blocks_News); an update
		// from WordPress.org does not activate, so it keeps the older version and shows it.
		update_option( 'envo_blocks_news_version', ENVO_BLOCKS_VERSION, false );
		// "Store in 5 minutes" and then the theme notice (Envo_Blocks_Setup_Notices), each until the user closes it.
		if ( ! $network_wide ) {
			Envo_Blocks_Setup_Notices::activated();
		}
		// Only an activation by hand on the Plugins screen: not for the whole network, not by WP-CLI, AJAX (e.g. an
		// installer of another plugin) or REST (e.g. the block directory, onboarding of other plugins).
		$rest = defined( 'REST_REQUEST' ) && REST_REQUEST;
		$cli  = defined( 'WP_CLI' ) && WP_CLI;
		if ( ! $network_wide && ! $rest && ! $cli && ! wp_doing_ajax() && get_current_user_id() ) {
			set_transient( 'envo_blocks_activation_redirect', get_current_user_id(), 60 );
		}
	}

	/**
	 * Address of the "Store in 5 minutes" wizard when a plugin on this site has it (filter envo_blocks_wizard_url),
	 * else an empty string. Used by the activation redirect and the Templates Import page.
	 *
	 * @return string
	 */
	public static function wizard_url() {
		return (string) apply_filters( 'envo_blocks_wizard_url', '' );
	}

	/**
	 * Sends the user who activated the plugin – once – to "Store in 5 minutes" when the wizard is on the site, else to
	 * Templates Import. Not when several plugins were activated at once, and not in AJAX, cron, the network admin or for
	 * users who cannot use the page.
	 */
	public static function activation_redirect() {
		$user = get_transient( 'envo_blocks_activation_redirect' );
		if ( false === $user ) {
			return;
		}
		if ( (int) $user !== get_current_user_id() || wp_doing_ajax() || wp_doing_cron() || is_network_admin() ) {
			return;
		}
		delete_transient( 'envo_blocks_activation_redirect' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reads which screen WordPress shows.
		if ( isset( $_GET['activate-multi'] ) ) {
			return;
		}
		// The wizard decides itself who may use it (its filter returns '' for other users).
		$wizard = self::wizard_url();
		if ( '' !== $wizard ) {
			wp_safe_redirect( $wizard );
			exit;
		}
		if ( ! current_user_can( 'edit_posts' ) || ! self::post_types() ) {
			return;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG . '-import' ) );
		exit;
	}

	/**
	 * Content of the pages.
	 *
	 * @return array
	 */
	private static function data() {
		if ( null === self::$data ) {
			$data       = include ENVO_BLOCKS_PATH . 'data/admin.php';
			self::$data = is_array( $data ) ? $data : array();
		}
		return self::$data;
	}

	/**
	 * One address from data/admin.php.
	 *
	 * @param string $key Key in "urls".
	 * @return string
	 */
	private static function url( $key ) {
		$data = self::data();
		$url  = isset( $data['urls'][ $key ] ) ? (string) $data['urls'][ $key ] : '';
		// The docs in the language of the user when they are translated into it (envoblocks.com/<lang>/docs/…).
		if ( in_array( $key, array( 'docs', 'pro_docs' ), true ) ) {
			$lang = strtolower( substr( get_user_locale(), 0, 2 ) );
			if ( in_array( $lang, isset( $data['doc_languages'] ) ? (array) $data['doc_languages'] : array(), true ) ) {
				$url = str_replace( '//envoblocks.com/docs/', '//envoblocks.com/' . $lang . '/docs/', $url );
			}
		}
		return $url;
	}

	/**
	 * Docs of every block of Envo Blocks and Envo Blocks PRO for the "Documentation" button of the block settings:
	 * block name => URL. Child blocks (FAQ Item, Product Tab, Menu Item …) get the docs of their parent.
	 *
	 * @return array
	 */
	public static function block_doc_urls() {
		$data  = self::data();
		$free  = isset( $data['block_docs'] ) ? (array) $data['block_docs'] : array();
		$pro   = array();
		$alias = array( 'popup' => 'popups' ); // Block folder => key on the Blocks page.
		foreach ( isset( $data['pro_groups'] ) ? (array) $data['pro_groups'] : array() as $group ) {
			foreach ( (array) $group['blocks'] as $key => $block ) {
				$pro[ $key ] = $block[2];
			}
		}
		$url = static function ( $name ) use ( $free, $pro, $alias ) {
			list( $ns, $slug ) = array_pad( explode( '/', $name, 2 ), 2, '' );
			if ( 0 === strpos( $name, Envo_Blocks_Pro_Teasers::PREFIX ) ) { // Teaser of a PRO block: the docs of that block.
				$ns   = 'envo-blocks-pro';
				$slug = substr( $name, strlen( Envo_Blocks_Pro_Teasers::PREFIX ) );
			}
			if ( 'envo-blocks' === $ns && isset( $free[ $slug ] ) ) {
				$section = $free[ $slug ];
				return self::url( 'docs' ) . ( false !== strpos( $section, '/' ) ? $section : $section . '/' . $slug ) . '/';
			}
			$key = isset( $alias[ $slug ] ) ? $alias[ $slug ] : $slug;
			if ( 'envo-blocks-pro' === $ns && isset( $pro[ $key ] ) ) {
				return self::url( 'pro_docs' ) . $pro[ $key ] . '/';
			}
			return '';
		};
		$out = array();
		foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
			if ( 0 !== strpos( $name, 'envo-blocks/' ) && 0 !== strpos( $name, 'envo-blocks-pro/' ) ) {
				continue;
			}
			$link = $url( $name );
			if ( '' === $link && ! empty( $type->parent ) ) {
				$link = $url( (string) current( (array) $type->parent ) );
			}
			if ( '' !== $link ) {
				$out[ $name ] = esc_url_raw( $link );
			}
		}
		return $out;
	}

	/**
	 * Pages: slug => array( menu title, page title ).
	 *
	 * @return array
	 */
	private static function pages() {
		$pages = array(
			self::SLUG             => array( __( 'Welcome', 'envo-blocks' ), __( 'Welcome', 'envo-blocks' ) ),
			self::SLUG . '-import' => array( __( 'Templates Import', 'envo-blocks' ), __( 'Templates Import', 'envo-blocks' ) ),
			self::SLUG . '-blocks' => array( __( 'Blocks', 'envo-blocks' ), __( 'Blocks', 'envo-blocks' ) ),
			self::SLUG . '-guides' => array( __( 'Guides', 'envo-blocks' ), __( 'Guides', 'envo-blocks' ) ),
		);
		foreach ( self::extra_pages() as $slug => $page ) {
			if ( self::SLUG . '-layouts' === $slug ) {
				// Store Layouts comes right after Blocks.
				$at    = array_search( self::SLUG . '-blocks', array_keys( $pages ), true ) + 1;
				$pages = array_slice( $pages, 0, $at, true ) + array( $slug => array( $page['menu'], $page['title'] ) ) + array_slice( $pages, $at, null, true );
				continue;
			}
			$pages[ $slug ] = array( $page['menu'], $page['title'] );
		}
		if ( ! isset( $pages[ self::SETTINGS ] ) ) {
			$pages[ self::SETTINGS ] = array( __( 'Settings', 'envo-blocks' ), __( 'Settings', 'envo-blocks' ) );
		}
		$pages[ self::SLUG . '-pro' ] = array( self::has_pro() ? __( 'Envo Blocks PRO', 'envo-blocks' ) : __( 'Get PRO', 'envo-blocks' ), __( 'Envo Blocks PRO', 'envo-blocks' ) );
		return $pages;
	}

	/**
	 * Pages that other plugins (Envo Blocks PRO: settings and license) add to the menu, with the same header and tabs.
	 *
	 * Filter "envo_blocks_admin_pages": array( 'envo-blocks-<name>' => array( 'menu' => menu title, 'title' => page
	 * title, 'callback' => renders the content, 'capability' => who may open it (default manage_options) ) ).
	 *
	 * @return array
	 */
	private static function extra_pages() {
		if ( null === self::$extra ) {
			self::$extra = array();
			$raw         = apply_filters( 'envo_blocks_admin_pages', array() );
			foreach ( is_array( $raw ) ? $raw : array() as $slug => $page ) {
				$slug = sanitize_key( $slug );
				if ( 0 !== strpos( $slug, self::SLUG . '-' ) || ! is_array( $page ) || empty( $page['menu'] ) || ! isset( $page['callback'] ) || ! is_callable( $page['callback'] ) ) {
					continue;
				}
				if ( in_array( $slug, array( self::SLUG . '-import', self::SLUG . '-blocks', self::SLUG . '-guides', self::SLUG . '-pro' ), true ) ) {
					continue;
				}
				self::$extra[ $slug ] = array(
					'menu'       => (string) $page['menu'],
					'title'      => isset( $page['title'] ) ? (string) $page['title'] : (string) $page['menu'],
					'callback'   => $page['callback'],
					'capability' => isset( $page['capability'] ) ? (string) $page['capability'] : 'manage_options',
					'section'    => isset( $page['section'] ) ? sanitize_key( $page['section'] ) : '',
				);
			}
		}
		return self::$extra;
	}

	/**
	 * Capability needed for a page.
	 *
	 * @param string $slug Page slug.
	 * @return string
	 */
	private static function capability( $slug ) {
		$extra = self::extra_pages();
		return isset( $extra[ $slug ] ) ? $extra[ $slug ]['capability'] : 'edit_posts';
	}

	/**
	 * Whether Envo Blocks PRO is active with an active license.
	 *
	 * @return bool
	 */
	private static function has_pro() {
		return Envo_Blocks_License::is_active();
	}

	/**
	 * Whether the PRO plugin is active (with or without a license). Then the pages show what PRO adds through the
	 * hooks envo_blocks_admin_* instead of the teasers.
	 *
	 * @return bool
	 */
	private static function pro_installed() {
		return Envo_Blocks_License::STATE_NONE !== Envo_Blocks_License::state();
	}

	/**
	 * Admin menu.
	 */
	public static function menu() {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- The menu icon must be a data URI.
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="black" d="M4 2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm1 3v3h10V5H5Zm0 5v5h4v-5H5Zm6 0v2h4v-2h-4Zm0 3v2h4v-2h-4Z"/></svg>'
		);

		add_menu_page( __( 'Envo Blocks', 'envo-blocks' ), __( 'Envo Blocks', 'envo-blocks' ), 'edit_posts', self::SLUG, array( __CLASS__, 'render' ), $icon, 59 );

		foreach ( self::pages() as $slug => $titles ) {
			$menu_title = $titles[0];
			if ( self::SLUG . '-pro' === $slug && ! self::has_pro() ) {
				// "Get PRO" is a button at the end of the submenu (styled in menu_style()).
				$menu_title = '<span class="envo-blocks-menu-upgrade">' . esc_html( $titles[0] ) . '</span>';
			}
			// Settings pages open inside Settings: reachable by their address, not in the menu.
			$parent = self::SETTINGS !== $slug && self::is_section( $slug ) ? '' : self::SLUG;
			add_submenu_page( $parent, $titles[1] . ' ‹ ' . __( 'Envo Blocks', 'envo-blocks' ), $menu_title, self::capability( $slug ), $slug, array( __CLASS__, 'render' ) );
		}
	}

	/**
	 * Pages shown inside Settings (with the settings sidebar, not in the menu): slug => group. Pages of other plugins
	 * can join with 'section' => group in the filter envo_blocks_admin_pages.
	 *
	 * @return array
	 */
	private static function section_groups() {
		$groups = array(
			self::SLUG . '-catalog-mode'    => 'store',
			self::SLUG . '-reviews'         => 'store',
			self::SLUG . '-checkout'        => 'store',
			self::SLUG . '-lowest-price'    => 'store',
			self::SLUG . '-payment-qr'      => 'store',
			self::SLUG . '-invoices'        => 'store',
			self::SLUG . '-abandoned-carts' => 'sales',
			self::SLUG . '-stock-alerts'    => 'sales',
			self::SETTINGS                  => 'blocks',
		);
		foreach ( self::extra_pages() as $slug => $page ) {
			if ( ! isset( $groups[ $slug ] ) && '' !== $page['section'] ) {
				$groups[ $slug ] = $page['section'];
			}
		}
		return $groups;
	}

	/**
	 * Whether a page is shown inside Settings.
	 *
	 * @param string $slug Page slug.
	 * @return bool
	 */
	private static function is_section( $slug ) {
		return isset( self::section_groups()[ $slug ] );
	}

	/**
	 * The items of the settings sidebar, by group: the setting pages, the lists of Envo Blocks PRO (discount rules,
	 * add-on groups) and, without PRO, what PRO adds (locked).
	 *
	 * @return array group => array( label, items => array( array( label, url, slug, locked, children ) ) )
	 */
	private static function hub_items() {
		$pages  = self::pages();
		// Blocks first: Settings opens the block settings (with PRO).
		$groups = array(
			'blocks' => array( __( 'Blocks', 'envo-blocks' ), array() ),
			'store'  => array( __( 'Store', 'envo-blocks' ), array() ),
			'sales'  => array( __( 'Sales', 'envo-blocks' ), array() ),
			'other'  => array( __( 'More', 'envo-blocks' ), array() ),
		);
		$item   = static function ( $label, $url, $slug, $locked = false, $children = array() ) {
			return array(
				'label'    => $label,
				'url'      => $url,
				'slug'     => $slug,
				'locked'   => $locked,
				'children' => $children,
			);
		};

		// Lists of post types of Envo Blocks PRO, first in Sales.
		foreach ( array( 'ebp_addon_group', 'ebp_discount_rule' ) as $type ) {
			$object = get_post_type_object( $type );
			if ( $object && current_user_can( $object->cap->edit_posts ) ) {
				$groups['sales'][1][] = $item( $object->labels->menu_name, admin_url( 'edit.php?post_type=' . $type ), 'type:' . $type );
			}
		}
		foreach ( self::section_groups() as $slug => $group ) {
			if ( ! isset( $pages[ $slug ] ) || ! current_user_can( self::capability( $slug ) ) || ( self::SETTINGS === $slug && ! isset( self::extra_pages()[ $slug ] ) ) ) {
				continue;
			}
			$group    = isset( $groups[ $group ] ) ? $group : 'other';
			$label    = self::SETTINGS === $slug ? __( 'Block settings', 'envo-blocks' ) : $pages[ $slug ][0];
			/**
			 * Links inside a settings page (e.g. the sections of the PRO block settings): anchor => label.
			 *
			 * @param array  $children Anchors.
			 * @param string $slug     Page.
			 */
			$children = (array) apply_filters( 'envo_blocks_settings_children', array(), $slug );
			$groups[ $group ][1][] = $item( $label, admin_url( 'admin.php?page=' . $slug ), $slug, false, $children );
		}

		// Without PRO: what PRO adds, locked, to the PRO page.
		if ( ! defined( 'EBP_VERSION' ) ) {
			$pro = admin_url( 'admin.php?page=' . self::SLUG . '-pro' );
			foreach ( array( __( 'Product Add-Ons', 'envo-blocks' ), __( 'Discount Rules', 'envo-blocks' ), __( 'Abandoned Carts', 'envo-blocks' ), __( 'Back in Stock', 'envo-blocks' ), __( 'Sales Notifications', 'envo-blocks' ), __( 'Gift Cards', 'envo-blocks' ), __( 'Loyalty Points', 'envo-blocks' ), __( 'Wholesale Prices', 'envo-blocks' ) ) as $label ) {
				$groups['sales'][1][] = $item( $label, $pro, '', true );
			}
			$groups['blocks'][1][] = $item( __( 'Block settings', 'envo-blocks' ), $pro, '', true );
		}

		return array_filter(
			$groups,
			static function ( $group ) {
				return (bool) $group[1];
			}
		);
	}

	/**
	 * Renders a settings page inside Settings: the sidebar with all settings and the page.
	 *
	 * @param string $page Page slug.
	 */
	private static function hub( $page ) {
		$extra = self::extra_pages();
		if ( ! isset( $extra[ $page ] ) ) {
			// Settings without its own page (no PRO): the first settings page.
			foreach ( self::section_groups() as $slug => $group ) {
				if ( isset( $extra[ $slug ] ) && current_user_can( $extra[ $slug ]['capability'] ) ) {
					$page = $slug;
					break;
				}
			}
		}
		$children = array();
		echo '<div class="eb-hub">';
		echo '<nav class="eb-hub__nav" aria-label="' . esc_attr__( 'Settings', 'envo-blocks' ) . '">';
		foreach ( self::hub_items() as $group ) {
			echo '<p class="eb-hub__group">' . esc_html( $group[0] ) . '</p><ul>';
			foreach ( $group[1] as $item ) {
				$active = $item['slug'] === $page;
				if ( $active ) {
					$children = $item['children'];
				}
				echo '<li><a class="eb-hub__link' . ( $active ? ' is-active' : '' ) . ( $item['locked'] ? ' is-locked' : '' ) . '" href="' . esc_url( $item['url'] ) . '"' . ( $active ? ' aria-current="page"' : '' ) . '>' . esc_html( $item['label'] ) . ( $item['locked'] ? ' <span class="eb-hub__pro">PRO</span>' : '' ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</nav><div class="eb-hub__main">';
		// The sections of a long settings page (e.g. the PRO block settings): a bar that stays at the top while scrolling.
		if ( $children ) {
			echo '<nav class="eb-hub__bar" aria-label="' . esc_attr__( 'Sections', 'envo-blocks' ) . '">';
			foreach ( $children as $anchor => $label ) {
				echo '<a href="#' . esc_attr( $anchor ) . '">' . esc_html( $label ) . '</a>';
			}
			echo '</nav>';
			// Marks the section in view.
			echo "<script>(function(){var b=document.currentScript.previousElementSibling,l=[].slice.call(b.querySelectorAll('a'));function u(){var c=0,t=b.getBoundingClientRect().bottom+100;l.forEach(function(a,i){var e=document.getElementById(a.getAttribute('href').slice(1));if(e&&e.getBoundingClientRect().top<t){c=i;}});l.forEach(function(a,i){a.classList.toggle('is-active',i===c);});if(b.scrollWidth>b.clientWidth){b.scrollLeft=l[c].offsetLeft-b.offsetLeft-16;}}addEventListener('scroll',u,{passive:true});l.forEach(function(a){a.addEventListener('click',function(){setTimeout(function(){l.forEach(function(o){o.classList.toggle('is-active',o===a);});},700);});});addEventListener('load',u);u();})();</script>";
		}
		if ( isset( $extra[ $page ] ) ) {
			echo '<div class="eb-extra eb-extra--' . esc_attr( substr( $page, strlen( self::SLUG ) + 1 ) ) . '">';
			call_user_func( $extra[ $page ]['callback'] );
			echo '</div>';
		} else {
			echo '<p>' . esc_html__( 'There are no settings yet.', 'envo-blocks' ) . '</p>';
		}
		echo '</div></div>';
	}

	/**
	 * The menu shows Settings for every settings page and for the lists of discount rules and add-on groups.
	 *
	 * @param string|null $file Submenu file.
	 * @return string|null
	 */
	public static function submenu_file( $file ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reads which page is open.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( ( '' !== $page && self::is_section( $page ) ) || self::is_settings_list() ) {
			return self::SETTINGS;
		}
		return $file;
	}

	/**
	 * Settings pages are not in the menu, so WordPress knows no title for them: the title of the page.
	 *
	 * @param string $admin_title Full title.
	 * @param string $title       Page title (empty for pages outside the menu).
	 * @return string
	 */
	public static function admin_title( $admin_title, $title ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reads which page is open.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( '' === $title && '' !== $page && self::is_section( $page ) && isset( self::pages()[ $page ] ) ) {
			return self::pages()[ $page ][1] . ' ‹ ' . __( 'Envo Blocks', 'envo-blocks' ) . $admin_title;
		}
		return $admin_title;
	}

	/**
	 * The lists of discount rules and add-on groups belong to the Envo Blocks menu.
	 *
	 * @param string $file Parent file.
	 * @return string
	 */
	public static function parent_file( $file ) {
		return self::is_settings_list() ? self::SLUG : $file;
	}

	/**
	 * Whether the screen is a list or an edit screen of a post type that belongs to Settings.
	 *
	 * @return bool
	 */
	private static function is_settings_list() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && in_array( $screen->post_type, array( 'ebp_addon_group', 'ebp_discount_rule' ), true ) && in_array( $screen->base, array( 'edit', 'post' ), true );
	}

	/**
	 * Above the lists of discount rules and add-on groups: the way back to Settings and the other settings.
	 */
	public static function settings_list_bar() {
		if ( ! self::is_settings_list() ) {
			return;
		}
		$links = array();
		foreach ( self::hub_items() as $group ) {
			foreach ( $group[1] as $item ) {
				if ( ! $item['locked'] ) {
					$current = 'type:' . get_current_screen()->post_type === $item['slug'];
					$links[] = '<a href="' . esc_url( $item['url'] ) . '"' . ( $current ? ' class="is-active" aria-current="page"' : '' ) . '>' . esc_html( $item['label'] ) . '</a>';
				}
			}
		}
		echo '<nav class="eb-hub-bar" aria-label="' . esc_attr__( 'Settings', 'envo-blocks' ) . '"><a class="eb-hub-bar__back" href="' . esc_url( admin_url( 'admin.php?page=' . self::SETTINGS ) ) . '">← ' . esc_html__( 'Envo Blocks Settings', 'envo-blocks' ) . '</a>' . implode( '', $links ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped while built.
		echo '<style>.eb-hub-bar{display:flex;flex-wrap:wrap;gap:6px 14px;align-items:center;margin:14px 20px 0 0;padding:10px 14px;border:1px solid #e5e7eb;border-radius:10px;background:#fff}.eb-hub-bar a{text-decoration:none;color:#50575e}.eb-hub-bar a.is-active{color:#6d28d9;font-weight:600}.eb-hub-bar__back{margin-right:8px;font-weight:600;color:#6d28d9!important}</style>';
	}

	/**
	 * The order of the submenu: the templates and blocks first, then the store features (also the post types and
	 * pages of Envo Blocks PRO), then the settings. Items of other plugins that are not in the list go before Settings.
	 * Welcome stays the first item and the target of the "Envo Blocks" menu link.
	 */
	public static function menu_order() {
		global $submenu;
		if ( empty( $submenu[ self::SLUG ] ) ) {
			return;
		}
		$order = array(
			self::SLUG,
			self::SLUG . '-import',
			self::SLUG . '-blocks',
			self::SLUG . '-layouts',
			'edit.php?post_type=ebp_popup',
			self::SLUG . '-guides',
			'*', // Everything else.
			self::SETTINGS,
			self::SLUG . '-license',
			self::SLUG . '-pro',
		);
		$rank  = array_flip( $order );
		$items = array_values( $submenu[ self::SLUG ] );
		$keyed = array();
		foreach ( $items as $index => $item ) {
			$position = isset( $rank[ $item[2] ] ) ? $rank[ $item[2] ] : $rank['*'];
			$keyed[]  = array( $position, $index, $item );
		}
		usort(
			$keyed,
			static function ( $a, $b ) {
				return $a[0] === $b[0] ? $a[1] - $b[1] : $a[0] - $b[0];
			}
		);
		$submenu[ self::SLUG ] = array_column( $keyed, 2 ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Reordering our own submenu.
	}

	/**
	 * Style of the "Get PRO" button in the admin menu; the menu is on every admin page.
	 */
	private static function menu_style() {
		if ( self::has_pro() ) {
			return;
		}
		wp_register_style( 'envo-blocks-menu', false, array(), ENVO_BLOCKS_VERSION );
		wp_enqueue_style( 'envo-blocks-menu' );
		wp_add_inline_style(
			'envo-blocks-menu',
			'#adminmenu .envo-blocks-menu-upgrade{display:block;margin:4px 0 2px;padding:6px 10px;border-radius:6px;background:linear-gradient(135deg,#4338ca,#6d28d9 55%,#a21caf);box-shadow:0 1px 3px rgba(0,0,0,.3);color:#fff;font-weight:600;line-height:1.4;text-align:center;white-space:nowrap}'
			. '#adminmenu a:hover .envo-blocks-menu-upgrade,#adminmenu a:focus .envo-blocks-menu-upgrade{filter:brightness(1.15);color:#fff}'
			. '#adminmenu .wp-submenu a:focus .envo-blocks-menu-upgrade{box-shadow:0 0 0 2px #fff}'
		);
	}

	/**
	 * Styles of the pages.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function assets( $hook ) {
		self::menu_style();
		// Every admin screen and the editor: links to envoblocks.com say they come from the plugin (affiliates get no
		// credit for visitors who already use the free plugin).
		wp_enqueue_script( 'envo-blocks-link-source', ENVO_BLOCKS_URL . 'assets/js/link-source.js', array(), Envo_Blocks_Blocks::ver( 'assets/js/link-source.js' ), true );

		if ( false === strpos( (string) $hook, self::SLUG ) ) {
			if ( self::show_woo_admin_notice() ) {
				// The install button and the close button of the WooCommerce notice.
				wp_enqueue_script( 'envo-blocks-admin-woo', ENVO_BLOCKS_URL . 'assets/js/admin-woo.js', array( 'wp-api-fetch', 'wp-i18n' ), Envo_Blocks_Blocks::ver( 'assets/js/admin-woo.js' ), true );
				wp_set_script_translations( 'envo-blocks-admin-woo', 'envo-blocks' );
				wp_add_inline_style( 'common', '.eb-woo-admin-notice .eb-woo-notice__actions{display:flex;flex-wrap:wrap;gap:8px 14px;align-items:center}.eb-woo-notice__status.is-error{color:#b32d2e;font-weight:600}' );
			}
			return;
		}
		wp_enqueue_style( 'envo-blocks-admin', ENVO_BLOCKS_URL . 'assets/css/admin.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/admin.css' ) );

		if ( 'active' !== self::woo_status() ) {
			// The "Install and activate WooCommerce" button of the message.
			wp_enqueue_script( 'envo-blocks-admin-woo', ENVO_BLOCKS_URL . 'assets/js/admin-woo.js', array( 'wp-api-fetch', 'wp-i18n' ), Envo_Blocks_Blocks::ver( 'assets/js/admin-woo.js' ), true );
			wp_set_script_translations( 'envo-blocks-admin-woo', 'envo-blocks' );
		}

		if ( self::SLUG . '-import' !== self::current() || ! self::post_types() ) {
			return;
		}

		// The same template window as in the editor, as a page: only templates, "Import" creates a new draft.
		wp_enqueue_style( 'envo-blocks-editor', ENVO_BLOCKS_URL . 'assets/css/editor.css', array( 'wp-components' ), Envo_Blocks_Blocks::ver( 'assets/css/editor.css' ) );
		wp_enqueue_script(
			'envo-blocks-editor',
			ENVO_BLOCKS_URL . 'assets/js/editor.js',
			array( 'wp-api-fetch', 'wp-blocks', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'wp-url' ),
			Envo_Blocks_Blocks::ver( 'assets/js/editor.js' ),
			true
		);
		wp_localize_script(
			'envo-blocks-editor',
			'envoBlocksData',
			array_merge(
				Envo_Blocks_Editor::data(),
				array(
					'adminPage' => true,
					'postTypes' => self::post_types(),
					'editUrl'   => esc_url_raw( admin_url( 'post.php?action=edit&post=' ) ),
				)
			)
		);
		wp_set_script_translations( 'envo-blocks-editor', 'envo-blocks' );
	}

	/**
	 * Post types a template can be imported into (pages first), for the current user.
	 *
	 * @return array List of array( slug, label, restBase ).
	 */
	private static function post_types() {
		$list = array();
		foreach ( array( 'page', 'post' ) as $slug ) {
			$object = get_post_type_object( $slug );
			if ( ! $object || ! current_user_can( $object->cap->create_posts ) || ! use_block_editor_for_post_type( $slug ) ) {
				continue;
			}
			$list[] = array(
				'slug'     => $slug,
				'label'    => $object->labels->singular_name,
				'restBase' => $object->rest_base ? $object->rest_base : $slug . 's',
			);
		}
		return $list;
	}

	/**
	 * Links under the plugin name on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$own = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ) . '">' . esc_html__( 'Welcome', 'envo-blocks' ) . '</a>',
			'<a href="' . esc_url( self::url( 'docs' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Guides', 'envo-blocks' ) . '</a>',
		);
		if ( ! self::has_pro() ) {
			$own[] = '<a class="envo-blocks-plugins-pro" style="color:#6d28d9;font-weight:600" href="' . esc_url( admin_url( 'admin.php?page=' . self::SLUG . '-pro' ) ) . '">' . esc_html__( 'Get PRO', 'envo-blocks' ) . '</a>';
		}
		return array_merge( $own, $links );
	}

	/**
	 * Current page slug.
	 *
	 * @return string
	 */
	private static function current() {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : self::SLUG; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only selects which information page to show.
		return array_key_exists( $page, self::pages() ) ? $page : self::SLUG;
	}

	/**
	 * Renders the current page.
	 */
	public static function render() {
		$page = self::current();

		if ( ! current_user_can( self::capability( $page ) ) ) {
			return;
		}
		$extra = self::extra_pages();

		echo '<div class="wrap eb-admin">';
		echo '<h1 class="screen-reader-text">' . esc_html( self::pages()[ $page ][1] ) . '</h1>';
		self::header( $page );
		self::woo_notice();

		if ( self::is_section( $page ) ) {
			self::hub( $page );
			echo '</div>';
			return;
		}

		if ( isset( $extra[ $page ] ) ) {
			echo '<div class="eb-extra eb-extra--' . esc_attr( substr( $page, strlen( self::SLUG ) + 1 ) ) . '">';
			call_user_func( $extra[ $page ]['callback'] );
			echo '</div></div>';
			return;
		}

		switch ( $page ) {
			case self::SLUG . '-import':
				self::page_import();
				break;
			case self::SLUG . '-blocks':
				self::page_blocks();
				break;
			case self::SLUG . '-guides':
				self::page_guides();
				break;
			case self::SLUG . '-pro':
				self::page_pro();
				break;
			default:
				self::page_welcome();
		}

		echo '</div>';
	}

	/**
	 * WooCommerce is installed, active or missing (for the message on the Envo Blocks pages).
	 *
	 * @return string active | installed | missing
	 */
	private static function woo_status() {
		if ( class_exists( 'WooCommerce' ) ) {
			return 'active';
		}
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$status = Envo_Blocks_Installer::plugin_status(
			array(
				'slug' => 'woocommerce',
				'name' => 'WooCommerce',
			)
		);
		return $status['status'];
	}

	/**
	 * Message without an active WooCommerce: what needs it, and a button that installs and activates it (the installer
	 * of the template window, REST /dependency).
	 */
	private static function woo_notice() {
		$status = self::woo_status();
		if ( 'active' === $status ) {
			return;
		}
		$can = 'installed' === $status ? current_user_can( 'activate_plugins' ) : current_user_can( 'install_plugins' );
		?>
		<div class="eb-woo-notice" role="region" aria-label="<?php esc_attr_e( 'WooCommerce is needed', 'envo-blocks' ); ?>">
			<div class="eb-woo-notice__icon"><?php self::the_icon( 'cart' ); ?></div>
			<div class="eb-woo-notice__body">
				<h2><?php echo 'installed' === $status ? esc_html__( 'WooCommerce is installed but not active', 'envo-blocks' ) : esc_html__( 'WooCommerce is not installed', 'envo-blocks' ); ?></h2>
				<p><?php esc_html_e( 'Checkout Fields, Payment QR Code, Invoices, Store Layouts and the blocks marked WooCommerce need an active WooCommerce. They appear in the Envo Blocks menu and in the block inserter as soon as it is active. The templates, sections, presets and the other blocks work without it.', 'envo-blocks' ); ?></p>
				<p class="eb-woo-notice__actions">
					<?php if ( $can ) : ?>
						<button type="button" class="button button-primary eb-woo-notice__button" data-activate="<?php echo 'installed' === $status ? '1' : '0'; ?>">
							<?php echo 'installed' === $status ? esc_html__( 'Activate WooCommerce', 'envo-blocks' ) : esc_html__( 'Install and activate WooCommerce', 'envo-blocks' ); ?>
						</button>
					<?php else : ?>
						<span><?php esc_html_e( 'Ask the administrator of the site to install and activate WooCommerce.', 'envo-blocks' ); ?></span>
					<?php endif; ?>
					<a href="https://wordpress.org/plugins/woocommerce/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'About WooCommerce', 'envo-blocks' ); ?></a>
					<span class="eb-woo-notice__status" role="status" aria-live="polite"></span>
				</p>
			</div>
		</div>
		<?php
	}

	/** User meta: the WooCommerce notice was closed. */
	const WOO_DISMISSED = 'envo_blocks_woo_notice_dismissed';

	/**
	 * Whether the WooCommerce notice is shown on this admin screen: WooCommerce not active, the user can install or
	 * activate plugins and has not closed it; not on the Envo Blocks pages (they have the big message) and not while
	 * plugins are being installed.
	 *
	 * @return bool
	 */
	private static function show_woo_admin_notice() {
		if ( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}
		if ( get_user_meta( get_current_user_id(), self::WOO_DISMISSED, true ) ) {
			return false;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && ( false !== strpos( (string) $screen->id, self::SLUG ) || in_array( $screen->id, array( 'update', 'plugin-install' ), true ) ) ) {
			return false;
		}
		return 'active' !== self::woo_status();
	}

	/**
	 * Notice on the other admin screens without an active WooCommerce, with the install button; it can be closed.
	 */
	public static function woo_admin_notice() {
		if ( ! self::show_woo_admin_notice() ) {
			return;
		}
		$status = self::woo_status();
		$can    = 'installed' === $status ? current_user_can( 'activate_plugins' ) : current_user_can( 'install_plugins' );
		?>
		<div class="notice notice-warning is-dismissible eb-woo-admin-notice" data-nonce="<?php echo esc_attr( wp_create_nonce( 'envo_blocks_dismiss_woo' ) ); ?>">
			<p>
				<strong><?php esc_html_e( 'Envo Blocks:', 'envo-blocks' ); ?></strong>
				<?php esc_html_e( 'Checkout Fields, Payment QR Code, Invoices, Store Layouts and the WooCommerce blocks need an active WooCommerce.', 'envo-blocks' ); ?>
			</p>
			<p class="eb-woo-notice__actions">
				<?php if ( $can ) : ?>
					<button type="button" class="button button-primary eb-woo-notice__button" data-activate="<?php echo 'installed' === $status ? '1' : '0'; ?>"><?php echo 'installed' === $status ? esc_html__( 'Activate WooCommerce', 'envo-blocks' ) : esc_html__( 'Install and activate WooCommerce', 'envo-blocks' ); ?></button>
				<?php endif; ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ); ?>"><?php esc_html_e( 'Envo Blocks', 'envo-blocks' ); ?></a>
				<span class="eb-woo-notice__status" role="status" aria-live="polite"></span>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX: the WooCommerce notice was closed (for this user).
	 */
	public static function dismiss_woo_notice() {
		check_ajax_referer( 'envo_blocks_dismiss_woo', 'nonce' );
		update_user_meta( get_current_user_id(), self::WOO_DISMISSED, 1 );
		wp_send_json_success();
	}

	/**
	 * Logo, name, version and tabs.
	 *
	 * @param string $page Current page.
	 */
	private static function header( $page ) {
		?>
		<header class="eb-admin__header">
			<div class="eb-admin__brand">
				<img src="<?php echo esc_url( ENVO_BLOCKS_URL . 'assets/images/logo.svg' ); ?>" alt="" width="40" height="40">
				<div>
					<p class="eb-admin__name"><?php esc_html_e( 'Envo Blocks – Store Templates, Blocks & Layouts for WooCommerce', 'envo-blocks' ); ?></p>
					<p class="eb-admin__version">
						<?php
						/* translators: %s: version number. */
						echo esc_html( sprintf( __( 'Version %s', 'envo-blocks' ), ENVO_BLOCKS_VERSION ) );
						?>
					</p>
				</div>
			</div>
			<nav class="eb-admin__tabs" aria-label="<?php esc_attr_e( 'Envo Blocks pages', 'envo-blocks' ); ?>">
				<?php foreach ( self::pages() as $slug => $titles ) : ?>
					<?php
					if ( ! current_user_can( self::capability( $slug ) ) || ( self::SETTINGS !== $slug && self::is_section( $slug ) ) ) {
						continue;
					}
					$is_active = $slug === $page || ( self::SETTINGS === $slug && self::is_section( $page ) );
					?>
					<a class="eb-admin__tab<?php echo $is_active ? ' is-active' : ''; ?><?php echo self::SLUG . '-pro' === $slug ? ' eb-admin__tab--pro' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $titles[0] ); ?></a>
				<?php endforeach; ?>
			</nav>
		</header>
		<?php
	}

	/**
	 * Small inline icon.
	 *
	 * @param string $name Icon name.
	 * @return string SVG markup (static, safe).
	 */
	private static function icon( $name ) {
		$paths = array(
			'flag'     => 'M5 21V4h9l1 2h5v10h-7l-1-2H7v7H5Z',
			'cart'     => 'M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM3 3h2l2.7 10.6a2 2 0 0 0 2 1.4h7.6a2 2 0 0 0 1.9-1.4L21 7H6.2',
			'layout'   => 'M4 4h16v6H4V4Zm0 8h7v8H4v-8Zm9 0h7v8h-7v-8Z',
			'sos'      => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 15h.01M12 13.5c0-2 2.5-2.2 2.5-4.3a2.5 2.5 0 0 0-5 0',
			'products' => 'M4 7l8-4 8 4v10l-8 4-8-4V7Zm8 4 8-4M12 11 4 7m8 4v10',
			'category' => 'M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z',
			'menu'     => 'M4 6h16M4 12h16M4 18h10',
			'heart'    => 'M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z',
			'template' => 'M4 3h16v18H4V3Zm3 4h10M7 11h10M7 15h6',
			'block'    => 'M4 4h16v16H4V4Zm0 6h16',
			'preset'   => 'M12 3l2.6 5.6L20 9.3l-4 4 1 5.7-5-2.8-5 2.8 1-5.7-4-4 5.4-.7L12 3Z',
			'check'    => 'M5 12.5l4.5 4.5L19 7.5',
			'external' => 'M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5',
		);
		$path = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['block'];

		return '<svg class="eb-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="' . esc_attr( $path ) . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * Allowed markup of the icons.
	 *
	 * @return array
	 */
	private static function icon_html() {
		return array(
			'svg'  => array(
				'class'       => true,
				'viewbox'     => true,
				'width'       => true,
				'height'      => true,
				'aria-hidden' => true,
				'focusable'   => true,
			),
			'path' => array(
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			),
		);
	}

	/**
	 * Echoes an icon.
	 *
	 * @param string $name Icon name.
	 */
	private static function the_icon( $name ) {
		echo wp_kses( self::icon( $name ), self::icon_html() );
	}

	/**
	 * Blocks of this plugin from their block.json files.
	 *
	 * @return array List of array( slug, title, description ).
	 */
	private static function free_blocks() {
		$list = array();
		foreach ( (array) glob( ENVO_BLOCKS_PATH . 'blocks/*/block.json' ) as $file ) {
			$json = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file of this plugin.
			if ( ! is_array( $json ) || empty( $json['name'] ) || ! empty( $json['parent'] ) ) {
				continue; // Inner blocks (Product Tab) belong to their parent.
			}
			$type   = WP_Block_Type_Registry::get_instance()->get_registered( $json['name'] );
			$list[] = array(
				'slug'        => basename( dirname( $file ) ),
				'title'       => $type && $type->title ? $type->title : ( isset( $json['title'] ) ? $json['title'] : $json['name'] ),
				'description' => $type && $type->description ? $type->description : ( isset( $json['description'] ) ? $json['description'] : '' ),
				'woo'         => in_array( basename( dirname( $file ) ), Envo_Blocks_Blocks::WOO, true ),
				'icon'        => isset( $json['icon'] ) && is_string( $json['icon'] ) ? sanitize_html_class( $json['icon'] ) : 'block-default',
			);
		}
		usort(
			$list,
			static function ( $a, $b ) {
				return strcasecmp( $a['title'], $b['title'] );
			}
		);
		return $list;
	}

	/**
	 * Number of Envo Blocks PRO blocks (data/admin.php).
	 *
	 * @return int
	 */
	private static function pro_count() {
		$count = 0;
		foreach ( self::data()['pro_groups'] as $group ) {
			$count += count( $group['blocks'] );
		}
		return $count;
	}

	/**
	 * Small "PRO" label.
	 */
	private static function pro_tag() {
		echo ' <span class="eb-tag eb-tag--pro">' . esc_html__( 'PRO', 'envo-blocks' ) . '</span>';
	}

	/**
	 * Link under the PRO block lists: to the license when PRO is installed without an active license, otherwise
	 * to the PRO page; nothing with an active license.
	 */
	private static function pro_list_link() {
		if ( self::has_pro() ) {
			return;
		}
		if ( self::pro_installed() ) {
			$action = Envo_Blocks_License::action();
			echo '<p class="eb-links"><a href="' . esc_url( $action['url'] ) . '">' . esc_html( 'renew' === $action['type'] ? __( 'Renew License', 'envo-blocks' ) : __( 'Activate License', 'envo-blocks' ) ) . '</a></p>';
			return;
		}
		echo '<p class="eb-links"><a class="eb-link-pro" href="' . esc_url( admin_url( 'admin.php?page=' . self::SLUG . '-pro' ) ) . '">' . esc_html__( 'Get Envo Blocks PRO', 'envo-blocks' ) . '</a></p>';
	}

	/**
	 * Welcome page: the PRO blocks as chips in their groups.
	 */
	private static function pro_blocks_card() {
		?>
		<section class="eb-card eb-pro-blocks">
			<h2><?php esc_html_e( 'Envo Blocks PRO blocks', 'envo-blocks' ); ?><?php self::pro_tag(); ?></h2>
			<?php if ( ! self::pro_installed() ) : ?>
				<p><?php esc_html_e( 'More WooCommerce and content blocks in Envo Blocks PRO:', 'envo-blocks' ); ?></p>
			<?php endif; ?>
			<?php foreach ( self::data()['pro_groups'] as $group ) : ?>
				<p class="eb-pro-blocks__group"><?php echo esc_html( $group['title'] ); ?></p>
				<ul class="eb-chips eb-chips--pro">
					<?php foreach ( $group['blocks'] as $block ) : ?>
						<li><?php echo esc_html( $block[0] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
			<p class="eb-links"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG . '-blocks#eb-pro-blocks' ) ); ?>"><?php esc_html_e( 'All PRO blocks with guides and demos', 'envo-blocks' ); ?></a></p>
		</section>
		<?php
	}

	/**
	 * Icon of a block (a Dashicon, as in the block inserter).
	 *
	 * @param string $name Dashicon name without "dashicons-".
	 */
	private static function block_icon( $name ) {
		echo '<span class="eb-block__icon dashicons dashicons-' . esc_attr( sanitize_html_class( $name ) ) . '" aria-hidden="true"></span>';
	}

	/**
	 * Small PRO teaser box (Welcome and Blocks pages), only without PRO.
	 */
	private static function pro_box() {
		if ( self::pro_installed() ) {
			return;
		}
		$data  = self::data();
		$count = self::pro_count();
		?>
		<aside class="eb-card eb-probox">
			<img class="eb-probox__img" src="<?php echo esc_url( self::gallery_url( 'teklo-products-carousel' ) ); ?>" alt="" loading="lazy">
			<div class="eb-probox__body">
				<span class="eb-badge"><?php esc_html_e( 'PRO', 'envo-blocks' ); ?></span>
				<h2>
					<?php
					/* translators: %d: number of PRO blocks. */
					echo esc_html( sprintf( __( '%d more blocks for your store', 'envo-blocks' ), $count ) );
					?>
				</h2>
				<p><?php esc_html_e( 'Product carousels, mega menus, wishlist, compare, variation swatches, sticky cart and premium templates.', 'envo-blocks' ); ?></p>
				<a class="button eb-button-pro" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG . '-pro' ) ); ?>"><?php esc_html_e( 'See what is in PRO', 'envo-blocks' ); ?></a>
			</div>
		</aside>
		<?php
	}

	/**
	 * URL of a PRO preview image, or "".
	 *
	 * @param string $id Image name without extension.
	 * @return string
	 */
	private static function gallery_url( $id ) {
		$id = sanitize_file_name( $id );
		$remote = Envo_Blocks_Previews::url( 'pro/sections/' . $id . '.webp' );
		if ( '' !== $remote ) {
			return $remote;
		}
		return is_file( ENVO_BLOCKS_PATH . 'assets/images/pro/sections/' . $id . '.webp' ) ? ENVO_BLOCKS_URL . 'assets/images/pro/sections/' . $id . '.webp' : '';
	}

	/**
	 * Welcome page.
	 */
	private static function page_welcome() {
		// What this site has: without PRO the locked PRO teasers are not counted (like the blocks below).
		$own       = static function ( $items ) {
			return self::pro_installed() ? count( $items ) : count(
				array_filter(
					$items,
					static function ( $item ) {
						return empty( $item['premium'] );
					}
				)
			);
		};
		$templates = $own( Envo_Blocks_Library::all() );
		$sections  = $own( Envo_Blocks_Library::sections( 'sections' ) );
		$presets   = $own( Envo_Blocks_Library::sections( 'presets' ) );
		$blocks    = count( self::free_blocks() ) + ( self::pro_installed() ? self::pro_count() : 0 );
		$editor    = admin_url( 'post-new.php?post_type=page' );
		?>
		<section class="eb-hero">
			<div class="eb-hero__text">
				<h2><?php esc_html_e( 'Build store pages in minutes', 'envo-blocks' ); ?></h2>
				<p><?php esc_html_e( 'Import a ready-made page, drop in single sections or ready-made block presets, and add WooCommerce blocks that help you sell: trust badges, coupons, delivery estimates and more.', 'envo-blocks' ); ?></p>
				<div class="eb-hero__buttons">
					<?php if ( self::post_types() ) : ?>
						<a class="button button-hero eb-button-light" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG . '-import' ) ); ?>"><?php esc_html_e( 'Templates Import', 'envo-blocks' ); ?></a>
					<?php endif; ?>
					<a class="button button-hero <?php echo self::post_types() ? 'eb-button-ghost' : 'eb-button-light'; ?>" href="<?php echo esc_url( $editor ); ?>"><?php esc_html_e( 'Create a page', 'envo-blocks' ); ?></a>
					<a class="button button-hero eb-button-ghost" href="<?php echo esc_url( self::url( 'demos' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View live demos', 'envo-blocks' ); ?></a>
				</div>
			</div>
			<?php
			/**
			 * Numbers in the Welcome header: list of array( icon name, number, label ). Envo Blocks PRO adds its own.
			 */
			$stats = apply_filters(
				'envo_blocks_admin_stats',
				array(
					'templates' => array( 'template', $templates, __( 'Templates', 'envo-blocks' ) ),
					'sections'  => array( 'layout', $sections, __( 'Page sections', 'envo-blocks' ) ),
					'presets'   => array( 'preset', $presets, __( 'Presets', 'envo-blocks' ) ),
					'blocks'    => array( 'block', $blocks, __( 'Blocks', 'envo-blocks' ) ),
				)
			);
			?>
			<ul class="eb-stats">
				<?php foreach ( (array) $stats as $stat ) : ?>
					<li><?php self::the_icon( (string) $stat[0] ); ?><strong><?php echo esc_html( number_format_i18n( (int) $stat[1] ) ); ?></strong><span><?php echo esc_html( (string) $stat[2] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</section>

		<div class="eb-columns">
			<div class="eb-main">
				<section class="eb-card">
					<h2><?php esc_html_e( 'Get started in three steps', 'envo-blocks' ); ?></h2>
					<ol class="eb-steps">
						<li>
							<strong><?php esc_html_e( 'Open a page in the editor', 'envo-blocks' ); ?></strong>
							<span><?php esc_html_e( 'Create a new page or edit an existing one.', 'envo-blocks' ); ?></span>
						</li>
						<li>
							<strong><?php esc_html_e( 'Click "Templates Import" in the toolbar', 'envo-blocks' ); ?></strong>
							<span><?php esc_html_e( 'Choose Templates for a whole page, Sections for one part of a page or Presets for a ready-made block.', 'envo-blocks' ); ?></span>
						</li>
						<li>
							<strong><?php esc_html_e( 'Import and make it yours', 'envo-blocks' ); ?></strong>
							<span><?php esc_html_e( 'Change texts, colors and images. Products and categories come from your own store.', 'envo-blocks' ); ?></span>
						</li>
					</ol>
					<p class="eb-links">
						<a href="<?php echo esc_url( $editor ); ?>"><?php esc_html_e( 'Create a page', 'envo-blocks' ); ?></a>
						<a href="<?php echo esc_url( self::url( 'docs' ) . 'getting-started/import-templates/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'How to import a template', 'envo-blocks' ); ?></a>
					</p>
				</section>

				<section class="eb-card">
					<h2><?php esc_html_e( 'Blocks in the Envo Blocks section of the inserter', 'envo-blocks' ); ?></h2>
					<ul class="eb-chips">
						<?php foreach ( self::free_blocks() as $block ) : ?>
							<li><?php echo esc_html( $block['title'] ); ?></li>
						<?php endforeach; ?>
					</ul>
					<p class="eb-links"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG . '-blocks' ) ); ?>"><?php esc_html_e( 'All blocks with guides and demos', 'envo-blocks' ); ?></a></p>
				</section>
				<?php self::pro_blocks_card(); ?>
				<?php do_action( 'envo_blocks_admin_welcome_main' ); ?>
			</div>

			<div class="eb-side">
				<?php self::pro_box(); ?>
				<?php do_action( 'envo_blocks_admin_welcome_side' ); ?>
				<section class="eb-card">
					<h2><?php esc_html_e( 'Need help?', 'envo-blocks' ); ?></h2>
					<ul class="eb-list">
						<li><a href="<?php echo esc_url( self::url( 'docs' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'envo-blocks' ); ?></a></li>
						<li><a href="<?php echo esc_url( self::url( 'docs' ) . 'faq/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Frequently asked questions', 'envo-blocks' ); ?></a></li>
						<li><a href="<?php echo esc_url( self::url( 'support' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support forum', 'envo-blocks' ); ?></a></li>
					</ul>
				</section>
				<section class="eb-card eb-review">
					<h2><?php esc_html_e( 'Do you like Envo Blocks?', 'envo-blocks' ); ?></h2>
					<p><?php esc_html_e( 'A short review on WordPress.org helps other store owners find the plugin.', 'envo-blocks' ); ?></p>
					<a class="button" href="<?php echo esc_url( self::url( 'review' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Write a review', 'envo-blocks' ); ?></a>
				</section>
			</div>
		</div>
		<?php
	}

	/**
	 * Templates Import page: the template window of the editor, rendered by assets/js/editor.js.
	 */
	private static function page_import() {
		if ( ! self::post_types() ) {
			echo '<div class="eb-card"><p>' . esc_html__( 'You cannot create posts or pages, or the block editor is turned off for them.', 'envo-blocks' ) . '</p></div>';
			return;
		}
		?>
		<div class="eb-note" role="note">
			<?php self::the_icon( 'block' ); ?>
			<p>
				<?php esc_html_e( 'Here you import whole templates into a new page or post. Page sections and presets are inserted right where you edit: open a page or post, click "Templates Import" in the toolbar and switch to Sections or Presets.', 'envo-blocks' ); ?>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Open a new page', 'envo-blocks' ); ?></a>
			</p>
		</div>
		<div id="envo-blocks-templates-app" class="eb-templates-app">
			<p class="eb-templates-app__loading"><?php esc_html_e( 'Loading templates…', 'envo-blocks' ); ?></p>
		</div>
		<noscript><div class="eb-card"><p><?php esc_html_e( 'The template library needs JavaScript.', 'envo-blocks' ); ?></p></div></noscript>
		<?php
	}

	/**
	 * Blocks page.
	 */
	private static function page_blocks() {
		$data      = self::data();
		$pro_icons = include ENVO_BLOCKS_PATH . 'data/pro-blocks.php';
		$pro_icons = is_array( $pro_icons ) ? $pro_icons : array();
		?>
		<div class="eb-intro">
			<h2><?php esc_html_e( 'Blocks', 'envo-blocks' ); ?></h2>
			<p><?php esc_html_e( 'Find them in the Envo Blocks section of the block inserter. Each block has a guide and a live demo with ready-made variants.', 'envo-blocks' ); ?></p>
		</div>
		<div class="eb-grid">
			<?php foreach ( self::free_blocks() as $block ) : ?>
				<?php $section = isset( $data['block_docs'][ $block['slug'] ] ) ? $data['block_docs'][ $block['slug'] ] : ''; ?>
				<article class="eb-card eb-block">
					<h3>
						<?php self::block_icon( $block['icon'] ); ?>
						<?php echo esc_html( $block['title'] ); ?>
						<?php if ( $block['woo'] ) : ?>
							<span class="eb-tag"><?php esc_html_e( 'WooCommerce', 'envo-blocks' ); ?></span>
						<?php endif; ?>
					</h3>
					<p><?php echo esc_html( $block['description'] ); ?></p>
					<p class="eb-links">
						<?php if ( $section ) : ?>
							<a href="<?php echo esc_url( self::url( 'docs' ) . ( false !== strpos( $section, '/' ) ? $section : $section . '/' . $block['slug'] ) . '/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Guide', 'envo-blocks' ); ?></a>
						<?php endif; ?>
						<?php if ( ! in_array( $block['slug'], isset( $data['no_demo'] ) ? (array) $data['no_demo'] : array(), true ) ) : ?>
							<a href="<?php echo esc_url( self::url( 'block_demos' ) . ( isset( $data['block_demo'][ $block['slug'] ] ) ? $data['block_demo'][ $block['slug'] ] : $block['slug'] ) . '/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Live demo', 'envo-blocks' ); ?></a>
						<?php endif; ?>
					</p>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		?>
		<div class="eb-intro eb-intro--pro" id="eb-pro-blocks">
			<h2><?php esc_html_e( 'Envo Blocks PRO blocks', 'envo-blocks' ); ?><?php self::pro_tag(); ?></h2>
			<p>
				<?php
				if ( self::has_pro() ) {
					esc_html_e( 'Find them in the Envo Blocks PRO section of the block inserter.', 'envo-blocks' );
				} elseif ( self::pro_installed() ) {
					esc_html_e( 'Find them in the Envo Blocks PRO section of the block inserter. Without an active license they are shown on your pages but cannot be edited.', 'envo-blocks' );
				} else {
					esc_html_e( 'These blocks come with Envo Blocks PRO. Open a guide or a live demo to see what they do.', 'envo-blocks' );
				}
				?>
			</p>
			<?php self::pro_list_link(); ?>
		</div>
		<?php foreach ( $data['pro_groups'] as $group ) : ?>
			<h3 class="eb-group-title"><?php self::the_icon( $group['icon'] ); ?><?php echo esc_html( $group['title'] ); ?></h3>
			<div class="eb-grid">
				<?php foreach ( $group['blocks'] as $key => $block ) : ?>
					<article class="eb-card eb-block eb-block--pro">
						<h3><?php self::block_icon( isset( $pro_icons[ $key ] ) ? $pro_icons[ $key ] : 'block-default' ); ?><?php echo esc_html( $block[0] ); ?><?php self::pro_tag(); ?></h3>
						<p><?php echo esc_html( $block[1] ); ?></p>
						<p class="eb-links">
							<a href="<?php echo esc_url( self::url( 'pro_docs' ) . $block[2] . '/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Guide', 'envo-blocks' ); ?></a>
							<?php if ( '' !== $block[3] ) : ?>
								<a href="<?php echo esc_url( self::url( 'block_demos' ) . $block[3] . '/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Live demo', 'envo-blocks' ); ?></a>
							<?php endif; ?>
						</p>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		<?php
		do_action( 'envo_blocks_admin_blocks' );
		if ( ! self::pro_installed() ) {
			echo '<div class="eb-after">';
			self::pro_box();
			echo '</div>';
		}
	}

	/**
	 * Guides page.
	 */
	private static function page_guides() {
		$data = self::data();
		?>
		<div class="eb-intro">
			<h2><?php esc_html_e( 'Guides', 'envo-blocks' ); ?></h2>
			<p><?php esc_html_e( 'Step-by-step guides with pictures. They open on envoblocks.com in a new tab.', 'envo-blocks' ); ?></p>
		</div>
		<div class="eb-grid eb-grid--guides">
			<?php
			$guides = $data['guides'];
			if ( self::pro_installed() ) {
				foreach ( $data['pro_groups'] as $group ) {
					$links = array();
					foreach ( $group['blocks'] as $block ) {
						$links[] = array( $block[0], self::url( 'pro_docs' ) . $block[2] . '/' );
					}
					/* translators: %s: name of a group of blocks, e.g. Product blocks. */
					$guides[] = array( 'title' => sprintf( __( 'PRO: %s', 'envo-blocks' ), $group['title'] ), 'icon' => $group['icon'], 'links' => $links );
				}
			}
			?>
			<?php foreach ( (array) apply_filters( 'envo_blocks_admin_guides', $guides ) as $group ) : ?>
				<section class="eb-card eb-guide">
					<h3><?php self::the_icon( $group['icon'] ); ?><?php echo esc_html( $group['title'] ); ?></h3>
					<ul class="eb-list">
						<?php foreach ( $group['links'] as $link ) : ?>
							<li><a href="<?php echo esc_url( $link[1] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link[0] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
			<?php if ( ! self::pro_installed() ) : ?>
			<section class="eb-card eb-guide eb-guide--pro">
				<h3><?php self::the_icon( 'preset' ); ?><?php esc_html_e( 'Envo Blocks PRO', 'envo-blocks' ); ?></h3>
				<ul class="eb-list">
					<li><a href="<?php echo esc_url( self::url( 'pro_docs' ) . 'getting-started/install-pro/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Install Envo Blocks PRO', 'envo-blocks' ); ?></a></li>
					<li><a href="<?php echo esc_url( self::url( 'pro_docs' ) . 'getting-started/activate-license-key/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Activate the license key', 'envo-blocks' ); ?></a></li>
					<li><a href="<?php echo esc_url( self::url( 'pro_docs' ) . 'templates/importing-templates/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Import a premium template', 'envo-blocks' ); ?></a></li>
					<li><a href="<?php echo esc_url( self::url( 'pro_docs' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'All PRO documentation', 'envo-blocks' ); ?></a></li>
				</ul>
			</section>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Envo Blocks PRO page.
	 */
	private static function page_pro() {
		$data   = self::data();
		$state  = Envo_Blocks_License::state();
		$action = Envo_Blocks_License::action();
		$count  = 0;
		foreach ( $data['pro_groups'] as $group ) {
			$count += count( $group['blocks'] );
		}
		$names = array();
		$raw   = include ENVO_BLOCKS_PATH . 'data/pro-sections.php';
		foreach ( is_array( $raw ) ? $raw : array() as $entry ) {
			if ( isset( $entry['file'], $entry['name'] ) ) {
				$names[ substr( $entry['file'], 0, -5 ) ] = array( $entry['name'], isset( $entry['demo'] ) ? $entry['demo'] : '' );
			}
		}
		?>
		<section class="eb-hero eb-hero--pro">
			<div class="eb-hero__text">
				<span class="eb-badge eb-badge--light"><?php esc_html_e( 'PRO', 'envo-blocks' ); ?></span>
				<?php if ( Envo_Blocks_License::STATE_ACTIVE === $state ) : ?>
					<h2><?php esc_html_e( 'Envo Blocks PRO is active', 'envo-blocks' ); ?></h2>
					<p><?php esc_html_e( 'Thank you! All PRO blocks, premium templates and premium page sections are ready to use.', 'envo-blocks' ); ?></p>
					<div class="eb-hero__buttons">
						<a class="button button-hero eb-button-light" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG . '-settings' ) ); ?>"><?php esc_html_e( 'PRO settings', 'envo-blocks' ); ?></a>
						<a class="button button-hero eb-button-ghost" href="<?php echo esc_url( self::url( 'pro_docs' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'PRO documentation', 'envo-blocks' ); ?></a>
					</div>
				<?php else : ?>
					<h2>
						<?php
						/* translators: %d: number of PRO blocks. */
						echo esc_html( sprintf( __( 'Everything for a complete store: %d more blocks and premium templates', 'envo-blocks' ), $count ) );
						?>
					</h2>
					<p><?php esc_html_e( 'Envo Blocks PRO adds product and category carousels, mega menus, AJAX search, wishlist, compare, variation swatches, sticky cart and more, plus premium templates and page sections from our demos.', 'envo-blocks' ); ?></p>
					<div class="eb-hero__buttons">
						<a class="button button-hero eb-button-light" href="<?php echo esc_url( 'buy' === $action['type'] ? self::url( 'pricing' ) : $action['url'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php
							if ( 'activate' === $action['type'] ) {
								esc_html_e( 'Activate License', 'envo-blocks' );
							} elseif ( 'renew' === $action['type'] ) {
								esc_html_e( 'Renew License', 'envo-blocks' );
							} else {
								esc_html_e( 'Get Envo Blocks PRO', 'envo-blocks' );
							}
							?>
						</a>
						<a class="button button-hero eb-button-ghost" href="<?php echo esc_url( self::url( 'demos' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View live demos', 'envo-blocks' ); ?></a>
					</div>
				<?php endif; ?>
			</div>
			<ul class="eb-checks">
				<li><?php self::the_icon( 'check' ); ?><?php echo esc_html( sprintf( /* translators: %d: number of PRO blocks. */ __( '%d WooCommerce and content blocks', 'envo-blocks' ), $count ) ); ?></li>
				<li><?php self::the_icon( 'check' ); ?><?php esc_html_e( 'Premium templates and page sections', 'envo-blocks' ); ?></li>
				<li><?php self::the_icon( 'check' ); ?><?php esc_html_e( 'Live demos of every block and template', 'envo-blocks' ); ?></li>
				<li><?php self::the_icon( 'check' ); ?><?php esc_html_e( 'Updates and support', 'envo-blocks' ); ?></li>
			</ul>
		</section>

		<?php do_action( 'envo_blocks_admin_pro_page' ); ?>

		<section class="eb-gallery-wrap">
			<h2><?php esc_html_e( 'Premium page sections', 'envo-blocks' ); ?></h2>
			<p><?php esc_html_e( 'Insert them from the Sections tab of the template window. Click a picture to see it in its live demo.', 'envo-blocks' ); ?></p>
			<div class="eb-gallery">
				<?php foreach ( $data['pro_gallery'] as $id ) : ?>
					<?php
					$src = self::gallery_url( $id );
					if ( ! $src ) {
						continue;
					}
					$name = isset( $names[ $id ] ) ? $names[ $id ][0] : '';
					$demo = isset( $names[ $id ] ) && $names[ $id ][1] ? self::url( 'template_demos' ) . $names[ $id ][1] . '/' : self::url( 'demos' );
					?>
					<a class="eb-shot" href="<?php echo esc_url( $demo ); ?>" target="_blank" rel="noopener noreferrer">
						<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
						<span><?php echo esc_html( $name ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section>
			<h2 class="eb-title"><?php esc_html_e( 'PRO blocks', 'envo-blocks' ); ?></h2>
			<div class="eb-grid eb-grid--pro">
				<?php foreach ( $data['pro_groups'] as $group ) : ?>
					<section class="eb-card eb-guide">
						<h3><?php self::the_icon( $group['icon'] ); ?><?php echo esc_html( $group['title'] ); ?></h3>
						<ul class="eb-chips eb-chips--pro">
							<?php foreach ( $group['blocks'] as $block ) : ?>
								<li><?php echo esc_html( $block[0] ); ?></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endforeach; ?>
			</div>
		</section>

		<?php if ( Envo_Blocks_License::STATE_ACTIVE !== $state ) : ?>
			<section class="eb-cta">
				<h2><?php esc_html_e( 'Ready to build a better store?', 'envo-blocks' ); ?></h2>
				<p><?php esc_html_e( 'Yearly and lifetime plans for 3 to 100 sites.', 'envo-blocks' ); ?></p>
				<a class="button button-hero eb-button-pro" href="<?php echo esc_url( self::url( 'pricing' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'See plans and pricing', 'envo-blocks' ); ?></a>
			</section>
		<?php endif; ?>
		<?php
	}
}
