<?php
/**
 * My Account: [woocommerce_my_account] (every section and the login / register form) with the menu as a sidebar,
 * tabs, tiles or a compact list.
 *
 * A sample account (menu, dashboard, orders, addresses, account details – nothing real, nothing is saved) is shown
 * in the editor preview and where the filter envo_blocks_my_account_sample allows it (Store Demos: the layout demos
 * for visitors who are not signed in). Its menu links switch the sample sections with ?account_tab=<endpoint>.
 *
 * @package EnvoBlocks
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_account_menu_items' ) ) {
	return;
}

$envo_blocks_style = Envo_Blocks_Blocks::choice( $attributes, 'menuStyle', array( 'sidebar', 'tabs', 'tiles', 'compact' ) );
$envo_blocks_preview = Envo_Blocks_Blocks::is_preview();
/**
 * Filters whether the My Account block shows the sample account instead of the real one.
 *
 * @param bool $sample False (true in the editor preview).
 */
$envo_blocks_sample = $envo_blocks_preview || (bool) apply_filters( 'envo_blocks_my_account_sample', false );

if ( $envo_blocks_sample ) {
	$envo_blocks_items = wc_get_account_menu_items();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only picks a sample section.
	$envo_blocks_tab = ! $envo_blocks_preview && isset( $_GET['account_tab'] ) ? sanitize_key( wp_unslash( $_GET['account_tab'] ) ) : 'dashboard';
	if ( ! isset( $envo_blocks_items[ $envo_blocks_tab ] ) || 'customer-logout' === $envo_blocks_tab ) {
		$envo_blocks_tab = 'dashboard';
	}
	$envo_blocks_link = static function ( $endpoint ) use ( $envo_blocks_preview ) {
		if ( $envo_blocks_preview ) {
			return '#';
		}
		return 'dashboard' === $endpoint || 'customer-logout' === $endpoint ? remove_query_arg( 'account_tab' ) : add_query_arg( 'account_tab', $endpoint );
	};

	$envo_blocks_nav = '';
	foreach ( $envo_blocks_items as $envo_blocks_endpoint => $envo_blocks_label ) {
		$envo_blocks_nav .= '<li class="woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--' . esc_attr( $envo_blocks_endpoint ) . ( $envo_blocks_endpoint === $envo_blocks_tab ? ' is-active' : '' ) . '"><a href="' . esc_url( $envo_blocks_link( $envo_blocks_endpoint ) ) . '"' . ( $envo_blocks_endpoint === $envo_blocks_tab ? ' aria-current="page"' : '' ) . '>' . esc_html( $envo_blocks_label ) . '</a></li>';
	}

	$envo_blocks_name  = __( 'Alex Morgan', 'envo-blocks' );
	$envo_blocks_price = static function ( $amount ) {
		return wp_kses_post( wc_price( $amount ) );
	};
	switch ( $envo_blocks_tab ) {
		case 'orders':
			$envo_blocks_rows = '';
			foreach ( array(
				array( 1048, '-3 days', __( 'Processing', 'envo-blocks' ), 'processing', 1099.99, 1 ),
				array( 1031, '-3 weeks', __( 'Completed', 'envo-blocks' ), 'completed', 349.99, 2 ),
				array( 1017, '-2 months', __( 'Completed', 'envo-blocks' ), 'completed', 129.00, 1 ),
			) as $envo_blocks_row ) {
				$envo_blocks_rows .= '<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-' . esc_attr( $envo_blocks_row[3] ) . ' order">'
					. '<th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" scope="row"><a href="#">#' . (int) $envo_blocks_row[0] . '</a></th>'
					. '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">' . esc_html( wp_date( get_option( 'date_format' ), strtotime( $envo_blocks_row[1] ) ) ) . '</td>'
					. '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status">' . esc_html( $envo_blocks_row[2] ) . '</td>'
					/* translators: 1: order total, 2: number of items. */
					. '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total">' . sprintf( esc_html( _n( '%1$s for %2$s item', '%1$s for %2$s items', $envo_blocks_row[5], 'envo-blocks' ) ), $envo_blocks_price( $envo_blocks_row[4] ), (int) $envo_blocks_row[5] ) . '</td>'
					. '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions"><a href="#" class="woocommerce-button button view">' . esc_html__( 'View', 'envo-blocks' ) . '</a></td></tr>';
			}
			$envo_blocks_content = '<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table"><thead><tr>'
				. '<th class="woocommerce-orders-table__header"><span class="nobr">' . esc_html__( 'Order', 'envo-blocks' ) . '</span></th>'
				. '<th class="woocommerce-orders-table__header"><span class="nobr">' . esc_html__( 'Date', 'envo-blocks' ) . '</span></th>'
				. '<th class="woocommerce-orders-table__header"><span class="nobr">' . esc_html__( 'Status', 'envo-blocks' ) . '</span></th>'
				. '<th class="woocommerce-orders-table__header"><span class="nobr">' . esc_html__( 'Total', 'envo-blocks' ) . '</span></th>'
				. '<th class="woocommerce-orders-table__header"><span class="nobr">' . esc_html__( 'Actions', 'envo-blocks' ) . '</span></th>'
				. '</tr></thead><tbody>' . $envo_blocks_rows . '</tbody></table>';
			break;
		case 'edit-address':
			$envo_blocks_address = '<address>' . esc_html( $envo_blocks_name ) . '<br>' . esc_html__( '12 Market Street', 'envo-blocks' ) . '<br>' . esc_html__( 'Springfield 10115', 'envo-blocks' ) . '</address>';
			$envo_blocks_content = '<p>' . esc_html__( 'The following addresses will be used on the checkout page by default.', 'envo-blocks' ) . '</p>'
				. '<div class="u-columns woocommerce-Addresses col2-set addresses">'
				. '<div class="u-column1 col-1 woocommerce-Address"><header class="woocommerce-Address-title title"><h2>' . esc_html__( 'Billing address', 'envo-blocks' ) . '</h2><a href="#" class="edit">' . esc_html__( 'Edit', 'envo-blocks' ) . '</a></header>' . $envo_blocks_address . '</div>'
				. '<div class="u-column2 col-2 woocommerce-Address"><header class="woocommerce-Address-title title"><h2>' . esc_html__( 'Shipping address', 'envo-blocks' ) . '</h2><a href="#" class="edit">' . esc_html__( 'Edit', 'envo-blocks' ) . '</a></header>' . $envo_blocks_address . '</div>'
				. '</div>';
			break;
		case 'edit-account':
			$envo_blocks_field   = static function ( $label, $value, $type = 'text' ) {
				return '<p class="woocommerce-form-row form-row"><label>' . esc_html( $label ) . '</label><input class="woocommerce-Input input-text" type="' . esc_attr( $type ) . '" value="' . esc_attr( $value ) . '" readonly></p>';
			};
			$envo_blocks_content = '<form class="woocommerce-EditAccountForm edit-account" onsubmit="return false">'
				. $envo_blocks_field( __( 'First name', 'envo-blocks' ), 'Alex' )
				. $envo_blocks_field( __( 'Last name', 'envo-blocks' ), 'Morgan' )
				. $envo_blocks_field( __( 'Email address', 'envo-blocks' ), 'alex@example.com', 'email' )
				. '<p><button type="button" class="woocommerce-Button button">' . esc_html__( 'Save changes', 'envo-blocks' ) . '</button></p></form>';
			break;
		case 'downloads':
			$envo_blocks_content = '<div class="woocommerce-info">' . esc_html__( 'No downloads available yet.', 'envo-blocks' ) . ' <a class="button" href="#">' . esc_html__( 'Browse products', 'envo-blocks' ) . '</a></div>';
			break;
		case 'dashboard':
			$envo_blocks_content = '<p>' . sprintf(
				/* translators: %s: customer name. */
				esc_html__( 'Hello %s', 'envo-blocks' ),
				'<strong>' . esc_html( $envo_blocks_name ) . '</strong>'
			) . '</p><p>' . esc_html__( 'From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.', 'envo-blocks' ) . '</p>';
			break;
		default:
			/* translators: %s: account section, e.g. "Wishlist". */
			$envo_blocks_content = '<h2>' . esc_html( $envo_blocks_items[ $envo_blocks_tab ] ) . '</h2><p>' . esc_html( sprintf( __( 'Your %s is shown here.', 'envo-blocks' ), strtolower( $envo_blocks_items[ $envo_blocks_tab ] ) ) ) . '</p>';
	}

	$envo_blocks_html = '<div class="woocommerce"><nav class="woocommerce-MyAccount-navigation" aria-label="' . esc_attr__( 'Account pages', 'envo-blocks' ) . '"><ul>' . $envo_blocks_nav . '</ul></nav>'
		. '<div class="woocommerce-MyAccount-content">' . $envo_blocks_content . '</div></div>';
	$envo_blocks_signed = true;
} else {
	$envo_blocks_html   = do_shortcode( '[woocommerce_my_account]' );
	$envo_blocks_signed = is_user_logged_in();
}

$envo_blocks_vars = Envo_Blocks_Blocks::color_vars(
	$attributes,
	array(
		'accentColor'  => '--envo-accent',
		'surfaceColor' => '--envo-surface',
	)
);
printf(
	'<div %1$s>%2$s</div>',
	get_block_wrapper_attributes( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array_filter(
			array(
				'class' => 'envo-account envo-account--' . $envo_blocks_style . ( $envo_blocks_signed ? ' is-signed-in' : ' is-signed-out' ) . ( $envo_blocks_sample ? ' is-sample' : '' ),
				'style' => $envo_blocks_vars,
			)
		)
	),
	$envo_blocks_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce templates escape their output; the sample is escaped above.
);
