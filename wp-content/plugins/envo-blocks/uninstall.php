<?php
/**
 * Removes the data of the plugin when it is deleted.
 *
 * Envo Blocks keeps the settings of Checkout Fields, the cached rating counts of the Rating Summary block,
 * temporary preview drafts and the kept copies of the template previews. Answers of checkout fields saved with
 * orders stay with the orders.
 *
 * @package EnvoBlocks
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_transient( 'envo_blocks_rating_counts' );
delete_option( 'envo_blocks_canvas_used' );
delete_transient( 'envo_blocks_theme_canvas' );
delete_option( 'envo_blocks_checkout_fields' );
delete_option( 'envo_blocks_payment_qr' );
// Invoices: only the settings. Issued documents, their PDF files, number series and font packs stay (archiving).
delete_option( 'envo_blocks_invoices' );
// Closed "WooCommerce is needed" notices of the users.
delete_metadata( 'user', 0, 'envo_blocks_woo_notice_dismissed', '', true );

// Store in 5 minutes: its state, the backups for "Undo everything" and the brand colours (the pages it made stay).
foreach ( array( 'state', 'snapshot', 'parts', 'parts_backup', 'palette', 'store_backup', 'notice_dismissed' ) as $envo_blocks_key ) {
	delete_option( 'envo_blocks_wizard_' . $envo_blocks_key );
}

// Price history of the products (Lowest Price) and its settings.
require_once __DIR__ . '/includes/class-envo-blocks-price-history.php';
Envo_Blocks_Price_History::uninstall();

// Temporary preview drafts of the template window.
require_once __DIR__ . '/includes/class-envo-blocks-preview.php';
Envo_Blocks_Preview::delete_all();

// Copies of the template previews in uploads/envo-blocks-previews/.
require_once __DIR__ . '/includes/class-envo-blocks-previews.php';
Envo_Blocks_Previews::delete_all();
