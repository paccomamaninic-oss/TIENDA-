<?php 

function modern_drone_add_admin_menu() {
    add_menu_page(
        'Theme Settings', // Page title
        'Theme Settings', // Menu title
        'manage_options', // Capability
        'modern-drone-theme-settings', // Menu slug
        'modern_drone_settings_page' // Function to display the page
    );
}
add_action( 'admin_menu', 'modern_drone_add_admin_menu' );

function modern_drone_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Theme Settings', 'modern-drone' ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'modern_drone_settings_group' );
            do_settings_sections( 'modern-drone-theme-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function modern_drone_register_settings() {
    register_setting( 'modern_drone_settings_group', 'modern_drone_enable_animations' );

    add_settings_section(
        'modern_drone_settings_section',
        __( 'Animation Settings', 'modern-drone' ),
        null,
        'modern-drone-theme-settings'
    );

    add_settings_field(
        'modern_drone_enable_animations',
        __( 'Enable Animations', 'modern-drone' ),
        'modern_drone_enable_animations_callback',
        'modern-drone-theme-settings',
        'modern_drone_settings_section'
    );
}
add_action( 'admin_init', 'modern_drone_register_settings' );

function modern_drone_enable_animations_callback() {
    $checked = get_option( 'modern_drone_enable_animations', true );
    ?>
    <input type="checkbox" name="modern_drone_enable_animations" value="1" <?php checked( 1, $checked ); ?> />
    <?php
}

