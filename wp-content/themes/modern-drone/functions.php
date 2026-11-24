<?php
/**
 * Modern Drone functions and definitions
 *
 * @package Modern Drone
 */

if ( ! function_exists( 'modern_drone_setup' ) ) :
function modern_drone_setup() {

	global $content_width;

	if ( ! isset( $content_width ) ) {
		$content_width = 640;
	}

	// Theme version
	if ( ! defined( 'MODERN_DRONE_VERSION' ) ) {
		define( 'MODERN_DRONE_VERSION', wp_get_theme()->get( 'Version' ) );
	}

	// Load text domain
	load_theme_textdomain( 'modern-drone', get_template_directory() . '/languages' );

	// Theme supports
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );

	add_theme_support( 'custom-logo', array(
		'height'      => 240,
		'width'       => 240,
		'flex-height' => true,
	) );

	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff',
	) );

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	// WooCommerce
	add_theme_support( 'woocommerce' );

	// Editor styles
	add_editor_style( array( 'assets/css/editor-style.css' ) );

	require get_parent_theme_file_path( '/inc/dashboard/dashboard.php' );

	require get_parent_theme_file_path( '/inc/admin/theme-upsell.php' );

    require get_parent_theme_file_path( '/inc/customizer/customizer.php' );

}
endif;
add_action( 'after_setup_theme', 'modern_drone_setup' );

/**
 * Enqueue scripts and styles
 */
function modern_drone_scripts() {

	$modern_drone_version = MODERN_DRONE_VERSION;

	// Main stylesheet
	wp_enqueue_style(
		'modern-drone-basic-style',
		get_stylesheet_uri(),
		array(),
		$modern_drone_version
	);

	// Animate CSS
	wp_enqueue_style(
		'animate-css',
		get_template_directory_uri() . '/assets/css/animate.css',
		array(),
		$modern_drone_version
	);

	// Font Awesome
	wp_enqueue_style(
		'fontawesome',
		get_template_directory_uri() . '/assets/font-awesome/css/all.css',
		array(),
		'7.0.0'
	);

	// Owl Carousel CSS
	wp_enqueue_style(
		'owl-carousel-style',
		get_template_directory_uri() . '/assets/css/owl-carousel.css',
		array(),
		$modern_drone_version
	);

	// WOW JS
	wp_enqueue_script(
		'wow-js',
		get_template_directory_uri() . '/assets/js/wow.js',
		array( 'jquery' ),
		$modern_drone_version,
		true
	);

	// Owl Carousel JS
	wp_enqueue_script(
		'owl-carousel-js',
		get_template_directory_uri() . '/assets/js/owl-carousel.js',
		array( 'jquery' ),
		$modern_drone_version,
		true
	);

	// Main Script
	wp_enqueue_script(
		'modern-drone-main-script',
		get_template_directory_uri() . '/assets/js/script.js',
		array( 'jquery', 'wow-js' ),
		$modern_drone_version,
		true
	);

	// RTL support
	wp_style_add_data( 'modern-drone-basic-style', 'rtl', 'replace' );
}
add_action( 'wp_enqueue_scripts', 'modern_drone_scripts' );

function modern_drone_enqueue_admin_script($hook) {
    // Enqueue admin JS for notices
    wp_enqueue_script('modern-drone-welcome-notice', get_template_directory_uri() . '/inc/dashboard/modern-drone-welcome-notice.js', array('jquery'), '', true);
    
    // Localize script to pass data to JavaScript
    wp_localize_script('modern-drone-welcome-notice', 'modern_drone_localize', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('modern_drone_welcome_nonce'),
        'dismiss_nonce' => wp_create_nonce('modern_drone_welcome_nonce'), // Nonce for dismissal
        'redirect_url' => admin_url('themes.php?page=modern-drone-guide-page')
    ));
}
add_action('admin_enqueue_scripts', 'modern_drone_enqueue_admin_script');

function modern_drone_admin_theme_style() {
   wp_enqueue_style('modern-drone-custom-admin-style', esc_url(get_template_directory_uri()) . '/inc/dashboard/dashboard.css');
   wp_enqueue_style('modern-drone-admin-upsell-style', esc_url(get_template_directory_uri()) . '/assets/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'modern_drone_admin_theme_style');

add_filter( 'woocommerce_enable_setup_wizard', '__return_false' );

// add_action( 'admin_init', function() {
//     update_option( 'essential_blocks_quick_setup_shown', true );
//     update_option( 'essential_blocks_user_type', 'old' );
//     remove_submenu_page( 'admin.php', 'eb-quick-setup' );
// });

// Block Patterns.
require get_template_directory() . '/block-patterns.php';
require get_template_directory() . '/custom-setting.php';
require get_template_directory() .'/inc/TGM/tgm.php';
require_once get_template_directory() . '/inc/dashboard/welcome-notice.php';

/**
 * Redirect to Pro upsell page after theme activation
 */
add_action('after_switch_theme', 'modern_drone_redirect_after_activation');
function modern_drone_redirect_after_activation() {
    if ( ! get_option( 'modern_drone_activation_redirect', false ) ) {
        update_option( 'modern_drone_activation_redirect', true );
        wp_safe_redirect( admin_url( 'themes.php?page=modern-drone-pro' ) );
        exit;
    }
}

add_action( 'admin_bar_menu', 'modern_drone_add_upgrade_button', 100 );

function modern_drone_add_upgrade_button( $modern_drone_wp_admin_bar ) {

    $modern_drone_theme_name = wp_get_theme()->get( 'Name' );

    $modern_drone_args = array(
        'id'    => 'modern_drone_upgrade',
        'title' => '<span style="color:#fff;font-weight:600;">
            🚀 Upgrade to ' . esc_html( $modern_drone_theme_name ) . ' Pro - 20% OFF 
            <span style="background:#ff5722;color:#fff;padding:2px 8px;border-radius:3px;margin-left:6px;">
                Buy Now
            </span>
        </span>',
        'href'  => esc_url( MODERN_DRONE_BUY_PRO ),
        'meta'  => array(
            'class'  => 'modern-drone-upgrade-btn',
            'title'  => 'Upgrade to Pro',
            'target' => '_blank'
        )
    );

    $modern_drone_wp_admin_bar->add_node( $modern_drone_args );
}