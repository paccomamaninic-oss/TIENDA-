<?php
/**
 * PRO plugin and license settings (filter: envo_blocks_license_config).
 *
 * - pro_plugin      Main file of the PRO plugin (folder/file.php). Without it, PRO templates show "Buy PRO".
 * - license_option  Site option with the license data, used only when the PRO plugin does not provide
 *                   ebp_license_status(): an array with "status" = active / expired / anything else.
 * - buy_url         "Buy PRO" link.
 * - renew_url       "Renew License" link.
 * - activate_url    "Activate License" link (the License page of the PRO plugin in the Envo Blocks menu).
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

return array(
	'pro_plugin'     => 'envo-blocks-pro/envo-blocks-pro.php',
	'license_option' => 'ebp_license',
	'buy_url'        => 'https://envoblocks.com/',
	'renew_url'      => defined( 'EBP_KEYS_URL' ) ? EBP_KEYS_URL : 'https://envoblocks.com/my-account/',
	'activate_url'   => admin_url( 'admin.php?page=envo-blocks-license' ),
);
