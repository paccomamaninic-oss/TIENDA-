<?php
/**
 * Theme Upsell Page
 * 
 * @package Modern Drone
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add theme upsell page to admin menu
 */
add_action( 'admin_menu', 'modern_drone_add_theme_page' );
function modern_drone_add_theme_page() {
    add_theme_page(
        __( 'Upgrade to PRO', 'modern-drone' ),
        __( 'Upgrade to PRO', 'modern-drone' ),
        'manage_options',
        'modern-drone-pro',
        'modern_drone_pro_page_callback'
    );
}
/**
 * Render theme upsell page
 */
function modern_drone_pro_page_callback() {
    ?>
    <div class="wrap ot-pro-wrap">
        <h1><?php esc_html_e('Upgrade to Modern Drone Pro 🚀', 'modern-drone'); ?></h1>
        
        <div class="ot-pro-hero">
            <div class="hero-content">
                <div class="hero-left">
                    <h2><?php esc_html_e('Take Your Modern Drone Business to the Next Level', 'modern-drone'); ?></h2>
                    <p class="subtitle"><?php esc_html_e('Get access to premium features, advanced layouts, and priority support', 'modern-drone'); ?></p>
                    <div class="button-group">
                        <?php
                        // Check if theme is FSE (block theme) or customizer based
                        if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
                            // Block theme - link to Site Editor
                            $editor_url = admin_url( 'site-editor.php' );
                            $editor_text = __('Site Editor', 'modern-drone');
                            $editor_icon = 'dashicons-layout';
                        } else {
                            // Classic theme - link to Customizer
                            $editor_url = admin_url( 'customize.php' );
                            $editor_text = __('Customize', 'modern-drone');
                            $editor_icon = 'dashicons-admin-customizer';
                        }
                        // Check if the Ovation Elements plugin is active
                        $is_plugin_active = is_plugin_active('ovation-elements/ovation-elements.php');
                        ?>
                        <a class="button button-hero button-primary theme-install" id="install-activate-button" href="#" style="display:<?php echo $is_plugin_active ? 'none' : 'inline-flex'; ?> !important;">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Begin Installation', 'modern-drone'); ?>
                        </a>
                        <a class="button button-hero button-success" id="view-site-button" href="<?php echo esc_url( home_url('/') ); ?>" target="_blank" style="display:<?php echo $is_plugin_active ? 'inline-flex' : 'none'; ?> !important;">
                            <span class="dashicons dashicons-admin-site"></span>
                            <?php _e('View Site', 'modern-drone'); ?>
                        </a>
                        <a target="_blank" href="<?php echo esc_url( $editor_url ); ?>" class="button button-hero button-customize" style="display:<?php echo $is_plugin_active ? 'inline-flex' : 'none'; ?> !important;">
                            <span class="dashicons <?php echo esc_attr( $editor_icon ); ?>"></span>
                            <?php echo esc_html( $editor_text ); ?>
                        </a>
                        <a href="<?php echo esc_url( MODERN_DRONE_LIVE_DEMO ); ?>" target="_blank" class="button button-hero button-demo">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e('Live Demo', 'modern-drone'); ?>
                        </a>
                        <a href="<?php echo esc_url( MODERN_DRONE_BUY_PRO ); ?>" target="_blank" class="button button-primary button-hero button-pro">
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php esc_html_e('Get Pro Theme', 'modern-drone'); ?>
                        </a>
                        <a href="<?php echo esc_url( MODERN_DRONE_FREE_DOC ); ?>" target="_blank" class="button button-hero button-docs">
                            <span class="dashicons dashicons-book"></span>
                            <?php esc_html_e('Documentation', 'modern-drone'); ?>
                        </a>
                        <a href="<?php echo esc_url( MODERN_DRONE_BUNDLE_LINK ); ?>" target="_blank" class="button button-hero button-bundle">
                            <span class="dashicons dashicons-cart"></span>
                            <?php esc_html_e('WordPress Theme Bundle', 'modern-drone'); ?>
                        </a>
                    </div>
                </div>
                <div class="hero-right">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/screenshot.png" alt="<?php esc_attr_e('Modern Drone Theme Screenshot', 'modern-drone'); ?>" class="theme-screenshot">
                </div>
            </div>
        </div>

        <div class="ot-pro-features">
            <h2><?php esc_html_e('Why Upgrade to Pro?', 'modern-drone'); ?></h2>
            
            <div class="feature-grid">
                <div class="feature-box">
                    <span class="dashicons dashicons-layout"></span>
                    <h3><?php esc_html_e('Different styling Options', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('This content will change based on different styling options.', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-admin-customizer"></span>
                    <h3><?php esc_html_e('Section Reordering Option', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('Sections can be reordered or rearranged as per your preference.', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-editor-table"></span>
                    <h3><?php esc_html_e('Footer Builder', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('Create custom footers with advanced widgets', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-art"></span>
                    <h3><?php esc_html_e('Typography Controls', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('Full control over fonts, sizes, and text styling', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-cart"></span>
                    <h3><?php esc_html_e('WooCommerce Styling', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('Advanced WooCommerce integration with custom product layouts', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <h3><?php esc_html_e('Advanced Options', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('Advanced theme options available for themes', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-performance"></span>
                    <h3><?php esc_html_e('Performance Optimized', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('3X faster loading with optimized code and assets', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-sos"></span>
                    <h3><?php esc_html_e('Priority Support', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('Get expert help within 24 hours through our priority support system', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <h3><?php esc_html_e('Unlimited Color Schemes', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('Customize every color to match your brand identity with unlimited color options', 'modern-drone'); ?></p>
                </div>
                
                <div class="feature-box">
                    <span class="dashicons dashicons-download"></span>
                    <h3><?php esc_html_e('One-Click Demo Import', 'modern-drone'); ?></h3>
                    <p><?php esc_html_e('Import complete demo content with just one click and get started instantly', 'modern-drone'); ?></p>
                </div>
            </div>
        </div>

        <div class="ot-pro-comparison">
            <h2><?php esc_html_e('Free vs Pro Comparison', 'modern-drone'); ?></h2>
            
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Feature', 'modern-drone'); ?></th>
                        <th><?php esc_html_e('Free', 'modern-drone'); ?></th>
                        <th class="pro-col"><?php esc_html_e('Pro', 'modern-drone'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php esc_html_e('Typography Controls', 'modern-drone'); ?></td>
                        <td><?php esc_html_e('Limited', 'modern-drone'); ?></td>
                        <td class="pro-col"><?php esc_html_e('Full Control', 'modern-drone'); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('WooCommerce Styling', 'modern-drone'); ?></td>
                        <td>❌</td>
                        <td class="pro-col">✅</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Custom Widgets', 'modern-drone'); ?></td>
                        <td><?php esc_html_e('Basic', 'modern-drone'); ?></td>
                        <td class="pro-col"><?php esc_html_e('Advanced', 'modern-drone'); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Color Schemes', 'modern-drone'); ?></td>
                        <td><?php esc_html_e('Limited', 'modern-drone'); ?></td>
                        <td class="pro-col"><?php esc_html_e('Unlimited', 'modern-drone'); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Demo Import', 'modern-drone'); ?></td>
                        <td>❌</td>
                        <td class="pro-col">✅</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Priority Support', 'modern-drone'); ?></td>
                        <td>❌</td>
                        <td class="pro-col">✅</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Custom Post Types', 'modern-drone'); ?></td>
                        <td>❌</td>
                        <td class="pro-col">✅</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Page Builder Integration', 'modern-drone'); ?></td>
                        <td><?php esc_html_e('Gutenberg', 'modern-drone'); ?></td>
                        <td class="pro-col"><?php esc_html_e('Gutenberg/Customizer', 'modern-drone'); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Speed Optimization', 'modern-drone'); ?></td>
                        <td><?php esc_html_e('Standard', 'modern-drone'); ?></td>
                        <td class="pro-col"><?php esc_html_e('Advanced (3X Faster)', 'modern-drone'); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Sticky Header', 'modern-drone'); ?></td>
                        <td>❌</td>
                        <td class="pro-col">✅</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Preloader Options', 'modern-drone'); ?></td>
                        <td>❌</td>
                        <td class="pro-col">✅</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Blog Layouts', 'modern-drone'); ?></td>
                        <td><?php esc_html_e('1', 'modern-drone'); ?></td>
                        <td class="pro-col"><?php esc_html_e('Advanced', 'modern-drone'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="ot-pro-testimonials">
            <h2><?php esc_html_e('What Our Users Say', 'modern-drone'); ?></h2>
            
        <div class="testimonial-grid">
            <div class="testimonial-box">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p><?php esc_html_e('"I was looking for a clean and professional theme for my Drone website and this theme delivered exactly that. Setup was quick and the layout looks very modern."', 'modern-drone'); ?></p>
                <span class="author"><?php esc_html_e('- John D.', 'modern-drone'); ?></span>
            </div>

            <div class="testimonial-box">
                <div class="stars">⭐⭐⭐⭐</div>
                <p><?php esc_html_e('"The theme design is professional and easy to customize. The documentation helped me set up my accounting website without any issues."', 'modern-drone'); ?></p>
                <span class="author"><?php esc_html_e('- Sarah M.', 'modern-drone'); ?></span>
            </div>

            <div class="testimonial-box">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p><?php esc_html_e('"Very flexible and beginner-friendly. I was able to adjust colors, sections, and layouts directly from the Customizer. Highly recommended."', 'modern-drone'); ?></p>
                <span class="author"><?php esc_html_e('- Michael R.', 'modern-drone'); ?></span>
            </div>

            <div class="testimonial-box">
                <div class="stars">⭐⭐⭐⭐</div>
                <p><?php esc_html_e('"The mobile responsive design works perfectly. Most of my clients visit from their phones, and the site looks clean and professional."', 'modern-drone'); ?></p>
                <span class="author"><?php esc_html_e('- Emily T.', 'modern-drone'); ?></span>
            </div>

            <div class="testimonial-box">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p><?php esc_html_e('"Great theme for tax professionals. The service sections and homepage layout helped me present my services clearly to potential clients."', 'modern-drone'); ?></p>
                <span class="author"><?php esc_html_e('- David L.', 'modern-drone'); ?></span>
            </div>

            <div class="testimonial-box">
                <div class="stars">⭐⭐⭐⭐</div>
                <p><?php esc_html_e('"Customer support is very helpful and responsive. They guided me during setup and solved my issue quickly."', 'modern-drone'); ?></p>
                <span class="author"><?php esc_html_e('- Jennifer K.', 'modern-drone'); ?></span>
            </div>

            <div class="testimonial-box">
                <div class="stars">⭐⭐⭐⭐⭐</div>
                <p><?php esc_html_e('"Fast loading and SEO friendly. After launching my website with this theme, I started receiving more inquiries from clients."', 'modern-drone'); ?></p>
                <span class="author"><?php esc_html_e('- Robert H.', 'modern-drone'); ?></span>
            </div>

            <div class="testimonial-box">
                <div class="stars">⭐⭐⭐⭐</div>
                <p><?php esc_html_e('"A very good theme with useful features for financial and consulting businesses. Easy to install and configure."', 'modern-drone'); ?></p>
                <span class="author"><?php esc_html_e('- Lisa P.', 'modern-drone'); ?></span>
            </div>
        </div>
        </div>

        <div class="ot-pro-cta">
            <h2><?php esc_html_e('Ready to Upgrade?', 'modern-drone'); ?></h2>
            <p><?php esc_html_e('Join hundreds of satisfied customers who upgraded to Pro', 'modern-drone'); ?></p>
            <a href="<?php echo esc_url( MODERN_DRONE_BUY_PRO ); ?>" target="_blank" class="button button-primary button-hero">
                <?php esc_html_e('Get Modern Drone Pro Now', 'modern-drone'); ?> →
            </a>
        </div>

        <div class="ot-pro-footer">
            <p><?php printf( __('Need help? Contact our <a href="%s" target="_blank">support</a> team anytime.', 'modern-drone'), esc_url( MODERN_DRONE_SUPPORT ) ); ?></p>
        </div>
    </div>
    <?php
}
