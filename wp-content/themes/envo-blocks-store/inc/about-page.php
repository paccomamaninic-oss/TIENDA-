<?php
/**
 * Appearance > About Envo Blocks Store: getting started steps, the recommended plugin and help links.
 *
 * @package Envo_Blocks_Store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Envo_Blocks_Store_About_Page' ) ) :

	/**
	 * The theme's page under Appearance.
	 */
	class Envo_Blocks_Store_About_Page {

		const SLUG = 'envo-blocks-store-about';

		/**
		 * Hooks.
		 */
		public static function init() {
			add_action( 'admin_menu', array( __CLASS__, 'register' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		}

		/**
		 * Appearance > About Envo Blocks Store.
		 */
		public static function register() {
			add_theme_page(
				__( 'About Envo Blocks Store', 'envo-blocks-store' ),
				__( 'About Envo Blocks Store', 'envo-blocks-store' ),
				'edit_theme_options',
				self::SLUG,
				array( __CLASS__, 'render' )
			);
		}

		/**
		 * Styles, and the plugin installer when the plugin is not active.
		 *
		 * @param string $hook_suffix Current admin page (hook suffix).
		 */
		public static function enqueue( $hook_suffix ) {
			if ( 'appearance_page_' . self::SLUG !== $hook_suffix ) {
				return;
			}
			$version = wp_get_theme( get_template() )->get( 'Version' );
			wp_enqueue_style( 'envo-blocks-store-about', get_theme_file_uri( 'assets/admin/about.css' ), array(), $version );
			if ( 'active' !== Envo_Blocks_Store_Plugin_Notice::state() && current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ) ) {
				Envo_Blocks_Store_Plugin_Notice::enqueue_installer();
			}
		}

		/**
		 * Site Editor link to one of the theme's templates or template parts.
		 *
		 * @param string $type wp_template or wp_template_part.
		 * @param string $slug Template slug.
		 */
		private static function editor_url( $type, $slug ) {
			return admin_url( 'site-editor.php?postType=' . $type . '&postId=' . rawurlencode( get_stylesheet() . '//' . $slug ) . '&canvas=edit' );
		}

		/**
		 * Getting started steps: title, text, link label, URL.
		 */
		private static function steps() {
			if ( class_exists( 'WooCommerce' ) ) {
				$products = array(
					__( 'Add your products', 'envo-blocks-store' ),
					__( 'Add products in WooCommerce. The shop, product, cart, checkout and My Account pages already match the theme.', 'envo-blocks-store' ),
					__( 'Add a product', 'envo-blocks-store' ),
					admin_url( 'post-new.php?post_type=product' ),
				);
			} else {
				$products = array(
					__( 'Start selling', 'envo-blocks-store' ),
					__( 'Install WooCommerce to add products. The shop, product, cart, checkout and My Account pages are styled automatically.', 'envo-blocks-store' ),
					__( 'Find WooCommerce', 'envo-blocks-store' ),
					admin_url( 'plugin-install.php?s=WooCommerce&tab=search&type=term' ),
				);
			}

			return array(
				array(
					__( 'Add your logo and menu', 'envo-blocks-store' ),
					__( 'Open the header in the Site Editor, upload your logo to the Site Logo block and pick a menu for the Navigation block. Two more headers are ready in the Header patterns.', 'envo-blocks-store' ),
					__( 'Edit the header', 'envo-blocks-store' ),
					self::editor_url( 'wp_template_part', 'header' ),
				),
				array(
					__( 'Set up the store home page', 'envo-blocks-store' ),
					__( 'With Settings › Reading on “Your latest posts”, the Home template shows the store home page above your posts. For a static homepage, create a page with the “Store home” pattern.', 'envo-blocks-store' ),
					__( 'Edit the Home template', 'envo-blocks-store' ),
					self::editor_url( 'wp_template', 'home' ),
				),
				$products,
				array(
					__( 'Choose colours and fonts', 'envo-blocks-store' ),
					__( 'All colours and sizes come from the theme styles, so a change in Styles applies to every page at once.', 'envo-blocks-store' ),
					__( 'Open Styles', 'envo-blocks-store' ),
					admin_url( 'site-editor.php?path=' . rawurlencode( '/wp_global_styles' ) ),
				),
			);
		}

		/**
		 * The page.
		 */
		public static function render() {
			$theme = wp_get_theme( get_template() );
			$can   = current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' );
			$state = Envo_Blocks_Store_Plugin_Notice::state();
			?>
			<div class="wrap ebs-about">
				<h1 class="screen-reader-text"><?php esc_html_e( 'About Envo Blocks Store', 'envo-blocks-store' ); ?></h1>

				<section class="ebs-about__hero">
					<div class="ebs-about__intro">
						<p class="ebs-about__eyebrow">
							<?php
							/* translators: %s: Theme version. */
							printf( esc_html__( 'Version %s', 'envo-blocks-store' ), esc_html( $theme->get( 'Version' ) ) );
							?>
						</p>
						<h2 class="ebs-about__title"><?php esc_html_e( 'Welcome to Envo Blocks Store', 'envo-blocks-store' ); ?></h2>
						<p class="ebs-about__lead"><?php esc_html_e( 'A fast, easy-to-use block theme for online stores. The store home page, headers, footers and every shop page are edited visually in the Site Editor – no page builder and no settings panels to learn.', 'envo-blocks-store' ); ?></p>
						<p class="ebs-about__buttons">
							<a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>"><?php esc_html_e( 'Open the Site Editor', 'envo-blocks-store' ); ?></a>
							<a class="button button-hero" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View your site', 'envo-blocks-store' ); ?></a>
						</p>
					</div>
					<div class="ebs-about__shot">
						<img src="<?php echo esc_url( get_theme_file_uri( 'screenshot.png' ) ); ?>" width="600" height="450" alt="<?php esc_attr_e( 'Envo Blocks Store theme screenshot', 'envo-blocks-store' ); ?>" decoding="async" />
					</div>
				</section>

				<h2 class="ebs-about__heading"><?php esc_html_e( 'Getting started', 'envo-blocks-store' ); ?></h2>
				<ol class="ebs-about__steps">
					<?php foreach ( self::steps() as $i => $step ) : ?>
						<li class="ebs-about__step">
							<span class="ebs-about__number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $i + 1 ) ); ?></span>
							<h3><?php echo esc_html( $step[0] ); ?></h3>
							<p><?php echo esc_html( $step[1] ); ?></p>
							<a href="<?php echo esc_url( $step[3] ); ?>"><?php echo esc_html( $step[2] ); ?> &rarr;</a>
						</li>
					<?php endforeach; ?>
				</ol>

				<section class="ebs-about__plugin">
					<div class="ebs-about__plugin-body">
						<div class="ebs-about__plugin-head">
							<img class="ebs-about__plugin-logo" src="<?php echo esc_url( get_theme_file_uri( 'assets/admin/envo-blocks.svg' ) ); ?>" width="64" height="64" alt="" />
							<div>
								<p class="ebs-about__badge"><?php esc_html_e( 'Recommended plugin – free', 'envo-blocks-store' ); ?></p>
								<h2><?php esc_html_e( 'Envo Blocks', 'envo-blocks-store' ); ?></h2>
								<p><?php esc_html_e( 'Made for this theme: import complete store pages or single sections in one click, then add the WooCommerce blocks, page layouts and store features WordPress does not have.', 'envo-blocks-store' ); ?></p>
							</div>
						</div>
						<ul class="ebs-about__checks">
							<?php foreach ( Envo_Blocks_Store_Plugin_Notice::features( true ) as $feature ) : ?>
								<li><?php echo esc_html( $feature ); ?></li>
							<?php endforeach; ?>
						</ul>
						<p class="ebs-about__plugin-actions">
							<?php if ( 'active' === $state ) : ?>
								<span class="ebs-about__active"><?php esc_html_e( 'Installed and active', 'envo-blocks-store' ); ?></span>
								<a class="button button-primary button-hero" href="<?php echo esc_url( Envo_Blocks_Store_Plugin_Notice::plugin_url() ); ?>"><?php esc_html_e( 'Open Templates Import', 'envo-blocks-store' ); ?></a>
							<?php elseif ( $can ) : ?>
								<?php echo Envo_Blocks_Store_Plugin_Notice::button(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in button(). ?>
							<?php endif; ?>
							<a href="<?php echo esc_url( 'https://wordpress.org/plugins/' . Envo_Blocks_Store_Plugin_Notice::SLUG . '/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Plugin details', 'envo-blocks-store' ); ?></a>
						</p>
						<p class="ebs-plugin-notice__message" role="status" aria-live="polite"></p>
					</div>
					<div class="ebs-about__plugin-media" aria-hidden="true">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/admin/envo-blocks-preview.webp' ) ); ?>" width="360" height="250" alt="" decoding="async" />
					</div>
				</section>

				<h2 class="ebs-about__heading"><?php esc_html_e( 'Need help?', 'envo-blocks-store' ); ?></h2>
				<div class="ebs-about__help">
					<div class="ebs-about__card">
						<h3><?php esc_html_e( 'Support forum', 'envo-blocks-store' ); ?></h3>
						<p><?php esc_html_e( 'Ask a question or report a problem in the theme’s free support forum on WordPress.org.', 'envo-blocks-store' ); ?></p>
						<a href="<?php echo esc_url( 'https://wordpress.org/support/theme/envo-blocks-store/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get support', 'envo-blocks-store' ); ?> &rarr;</a>
					</div>
					<div class="ebs-about__card">
						<h3><?php esc_html_e( 'Theme guide', 'envo-blocks-store' ); ?></h3>
						<p><?php esc_html_e( 'How to install the theme and set up your store, step by step, on the theme’s home page.', 'envo-blocks-store' ); ?></p>
						<a href="<?php echo esc_url( $theme->get( 'ThemeURI' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read the guide', 'envo-blocks-store' ); ?> &rarr;</a>
					</div>
					<div class="ebs-about__card">
						<h3><?php esc_html_e( 'Enjoying Envo Blocks Store?', 'envo-blocks-store' ); ?></h3>
						<p><?php esc_html_e( 'A short review helps other people find the theme and keeps us improving it.', 'envo-blocks-store' ); ?></p>
						<a href="<?php echo esc_url( 'https://wordpress.org/support/theme/envo-blocks-store/reviews/#new-post' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'envo-blocks-store' ); ?> &rarr;</a>
					</div>
				</div>
			</div>
			<?php
		}
	}

	Envo_Blocks_Store_About_Page::init();

endif;
