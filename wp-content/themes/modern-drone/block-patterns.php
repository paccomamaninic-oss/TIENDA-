<?php
/**
 * Modern Drone: Block Patterns
 *
 * @since Modern Drone 1.0
 */

/**
 * Registers block patterns and categories.
 *
 * @since Modern Drone 1.0
 *
 * @return void
 */
function modern_drone_register_block_patterns() {
	$modern_drone_block_pattern_categories = array(
		'modern-drone'    => array( 'label' => esc_html__( 'Modern Drone', 'modern-drone' ) ),
	);

	$modern_drone_block_pattern_categories = apply_filters( 'modern_drone_block_pattern_categories', $modern_drone_block_pattern_categories );

	foreach ( $modern_drone_block_pattern_categories as $name => $properties ) {
		if ( ! WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
			register_block_pattern_category( $name, $properties );
		}
	}
}
add_action( 'init', 'modern_drone_register_block_patterns', 9 );
