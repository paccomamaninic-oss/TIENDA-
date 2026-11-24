<?php
/**
 * Modern Drone: Customizer
 *
 * @subpackage Modern Drone
 * @since 1.0
 */

function modern_drone_customize_register( $wp_customize ) {

	wp_enqueue_style('customizercustom_css', esc_url( get_template_directory_uri() ). '/inc/customizer/customizer.css');

	$wp_customize->add_section('modern_drone_premium_features_section', array(
		'title'    => __('🔒 Unlock Premium Features', 'modern-drone'),
		'priority' => 2,
	));
	
	$wp_customize->add_setting('modern_drone_premium_features');
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'modern_drone_premium_features',
			array(
				'section'     => 'modern_drone_premium_features_section',
				'type'        => 'hidden',
				'description' => '<div style="background: linear-gradient(135deg, #2B136B 0%, #A47AE2 100%); padding: 20px; border-radius: 8px; text-align: center; color: #fff;">
									<h3 style="margin-top: 0; color: #fff;">' . __('Unlock Advanced Features', 'modern-drone') . '</h3>
									<p style="margin: 15px 0;">' . __('Upgrade to Pro to get:', 'modern-drone') . '</p>
									<ul style="list-style: none; padding: 0; text-align: left; max-width: 300px; margin: 20px auto;">
										<li style="margin-bottom: 10px;">✓ ' . __('Advanced Footer Builder', 'modern-drone') . '</li>
										<li style="margin-bottom: 10px;">✓ ' . __('Typography Controls', 'modern-drone') . '</li>
										<li style="margin-bottom: 10px;">✓ ' . __('WooCommerce Styling Options', 'modern-drone') . '</li>
										<li style="margin-bottom: 10px;">✓ ' . __('Priority Support', 'modern-drone') . '</li>
										<li style="margin-bottom: 10px;">✓ ' . __('One-Click Demo Import', 'modern-drone') . '</li>
									</ul>
									<a href="' . esc_url(admin_url('themes.php?page=modern-drone-pro')) . '" 
									   style="display: inline-block; background: #fff; color: #667eea; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: 600; margin: 10px 5px; transition: all 0.3s;">
									   ' . __('View All Features', 'modern-drone') . '
									</a>
									<a href="' . esc_url(MODERN_DRONE_BUY_PRO) . '" target="_blank" 
									   style="display: inline-block; background: #ffc107; color: #333; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: 600; margin: 10px 5px;">
									   ' . __('Upgrade Now 🚀', 'modern-drone') . '
									</a>
									<a href="' . esc_url(MODERN_DRONE_BUNDLE_LINK) . '" target="_blank" 
									   style="display: inline-block; background: #28a745; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: 600; margin: 10px 5px;">
									   ' . __('WordPress Bundle 🎁', 'modern-drone') . '
									</a>
								  </div>',
			)
		)
	);
}
add_action( 'customize_register', 'modern_drone_customize_register' );