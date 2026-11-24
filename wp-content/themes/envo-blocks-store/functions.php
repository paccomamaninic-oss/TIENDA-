<?php
/**
 * Envo Blocks Store: global styles live in theme.json; there is one stylesheet for the home page
 * patterns and one for WooCommerce.
 *
 * @package Envo_Blocks_Store
 */

add_action(
	'wp_enqueue_scripts',
	function () {
		$version = wp_get_theme()->get( 'Version' );
		wp_enqueue_style( 'envo-blocks-store-patterns', get_theme_file_uri( 'assets/css/patterns.css' ), array(), $version );
		if ( class_exists( 'WooCommerce' ) ) {
			wp_enqueue_style( 'envo-blocks-store-woocommerce', get_theme_file_uri( 'assets/css/woocommerce.css' ), array(), $version );
		}
	},
	20
);

add_action(
	'after_setup_theme',
	function () {
		add_editor_style( 'assets/css/patterns.css' );
	}
);

add_action(
	'init',
	function () {
		register_block_pattern_category(
			'envo-blocks-store',
			array(
				'label'       => __( 'Envo Blocks Store', 'envo-blocks-store' ),
				'description' => __( 'Store sections from the Envo Blocks Store theme.', 'envo-blocks-store' ),
			)
		);
	}
);

/**
 * Prints a row of sample product cards made of core blocks. The home page patterns use it
 * instead of the WooCommerce Product Collection block when WooCommerce is not active, so the
 * layout still looks like a shop (for example in the theme preview on WordPress.org).
 *
 * @param array $products Items with the keys image, alt, name, price, old_price (optional) and rating (1-5).
 */
function envo_blocks_store_sample_products( $products ) {
	$link = home_url( '/' );
	echo '<!-- wp:group {"className":"eb-sample-products","layout":{"type":"grid","columnCount":4}} -->' . "\n";
	echo '<div class="wp-block-group eb-sample-products">';
	foreach ( $products as $product ) {
		$rating = max( 1, min( 5, (int) $product['rating'] ) );
		echo '<!-- wp:group {"className":"eb-sample-product","layout":{"type":"default"}} -->' . "\n";
		echo '<div class="wp-block-group eb-sample-product">';
		if ( ! empty( $product['old_price'] ) ) {
			echo '<!-- wp:paragraph {"className":"eb-sample-product__badge"} -->' . "\n";
			echo '<p class="eb-sample-product__badge">' . esc_html__( 'Sale', 'envo-blocks-store' ) . '</p>' . "\n";
			echo '<!-- /wp:paragraph -->' . "\n\n";
		}
		echo '<!-- wp:image {"sizeSlug":"full","linkDestination":"custom","className":"eb-sample-product__image"} -->' . "\n";
		echo '<figure class="wp-block-image size-full eb-sample-product__image"><a href="' . esc_url( $link ) . '"><img src="' . esc_url( get_theme_file_uri( 'assets/images/' . $product['image'] ) ) . '" alt="' . esc_attr( $product['alt'] ) . '"/></a></figure>' . "\n";
		echo '<!-- /wp:image -->' . "\n\n";
		echo '<!-- wp:heading {"textAlign":"center","level":3,"className":"eb-sample-product__title"} -->' . "\n";
		echo '<h3 class="wp-block-heading has-text-align-center eb-sample-product__title"><a href="' . esc_url( $link ) . '">' . esc_html( $product['name'] ) . '</a></h3>' . "\n";
		echo '<!-- /wp:heading -->' . "\n\n";
		echo '<!-- wp:paragraph {"align":"center","className":"eb-sample-product__rating"} -->' . "\n";
		/* translators: %d: star rating from 1 to 5. */
		echo '<p class="has-text-align-center eb-sample-product__rating"><span aria-hidden="true">' . esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ) . '</span><span class="screen-reader-text">' . esc_html( sprintf( __( 'Rated %d out of 5', 'envo-blocks-store' ), $rating ) ) . '</span></p>' . "\n";
		echo '<!-- /wp:paragraph -->' . "\n\n";
		echo '<!-- wp:paragraph {"align":"center","className":"eb-sample-product__price"} -->' . "\n";
		echo '<p class="has-text-align-center eb-sample-product__price">' . ( empty( $product['old_price'] ) ? '' : '<del>' . esc_html( $product['old_price'] ) . '</del> ' ) . '<ins>' . esc_html( $product['price'] ) . '</ins></p>' . "\n";
		echo '<!-- /wp:paragraph -->' . "\n\n";
		echo '<!-- wp:buttons {"className":"eb-sample-product__button","layout":{"type":"flex","justifyContent":"center"}} -->' . "\n";
		echo '<div class="wp-block-buttons eb-sample-product__button"><!-- wp:button -->' . "\n";
		echo '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $link ) . '">' . esc_html__( 'View product', 'envo-blocks-store' ) . '</a></div>' . "\n";
		echo '<!-- /wp:button --></div>' . "\n";
		echo '<!-- /wp:buttons --></div>' . "\n";
		echo '<!-- /wp:group -->' . "\n\n";
	}
	echo '</div>' . "\n";
	echo '<!-- /wp:group -->';
}

/* Appearance > About Envo Blocks Store and the Envo Blocks recommendation. */
require get_template_directory() . '/inc/plugin-notice.php';
require get_template_directory() . '/inc/about-page.php';
