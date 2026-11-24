<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$reycob_pyp_table_name = $wpdb->prefix . 'pyr_yape_payments';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Removing the plugin's dedicated table during uninstall.
$wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $reycob_pyp_table_name));
delete_option('woocommerce_pago_yape_reycob_settings');
delete_option('pyr_db_version');
