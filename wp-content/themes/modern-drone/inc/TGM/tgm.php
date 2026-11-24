<?php
	
load_template( get_template_directory() . '/inc/TGM/class-tgm-plugin-activation.php' );

/**
 * Recommended plugins.
 */
function modern_drone_register_recommended_plugins() {
	$plugins = array(
		array(
			'name'             => __( 'Essential Blocks', 'modern-drone' ),
			'slug'             => 'essential-blocks',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => __( 'WooCommerce', 'modern-drone' ),
			'slug'             => 'woocommerce',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => __( 'YITH WooCommerce Wishlist', 'modern-drone' ),
			'slug'             => 'yith-woocommerce-wishlist',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => __( 'YITH WooCommerce Compare', 'modern-drone' ),
			'slug'             => 'yith-woocommerce-compare',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => __( 'Ovation Elements', 'modern-drone' ),
			'slug'             => 'ovation-elements',
			'required'         => false,
			'force_activation' => false,
		)
	);
	$config = array();
	modern_drone_tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'modern_drone_register_recommended_plugins' );