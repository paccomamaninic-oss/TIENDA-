<?php
/**
 * Plugin Name: Reycob Pagos Manuales para Yape en Perú
 * Description: Añade pagos manuales con Yape mediante código QR, comprobante y verificación del pedido.
 * Version: 5.0.6
 * Author: REYCOB
 * Author URI: https://reycob.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: reycob-manual-payments-for-yape
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

define('REYCOB_PYP_VERSION', '5.0.6');
define('REYCOB_PYP_FILE', __FILE__);
define('REYCOB_PYP_PATH', plugin_dir_path(__FILE__));
define('REYCOB_PYP_URL', plugin_dir_url(__FILE__));
define('REYCOB_PYP_DB_VERSION', '1.0.0');

require_once REYCOB_PYP_PATH . 'class-init.php';
REYCOB_PYP_Manager::init();

add_action('before_woocommerce_init', 'reycob_pyp_declare_woocommerce_compatibility');
add_action('plugins_loaded', 'reycob_pyp_init_gateway');

function reycob_pyp_declare_woocommerce_compatibility() {
    if (!class_exists(Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        return;
    }

    Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', REYCOB_PYP_FILE, true);
    Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', REYCOB_PYP_FILE, false);
}

function reycob_pyp_init_gateway() {
    if (!class_exists('WooCommerce') || !class_exists('WC_Payment_Gateway')) {
        add_action('admin_notices', 'reycob_pyp_missing_woocommerce_notice');
        return;
    }

    add_filter('woocommerce_payment_gateways', 'reycob_pyp_add_gateway');

    class REYCOB_PYP_Yape_Gateway extends WC_Payment_Gateway {
        /** @var string Account phone number. */
        protected $phone = '';

        /** @var string Account holder name. */
        protected $holder_name = '';

        /** @var string QR image URL. */
        protected $qr_url = '';

        /** @var string Whether the receipt is required. */
        protected $capture_required = 'yes';

        /** @var string Order status applied after approval. */
        protected $approve_status = 'processing';

        public function __construct() {
            $this->id = 'pago_yape_reycob';
            $this->icon = REYCOB_PYP_URL . 'assets/yape-logo.png';
            $this->has_fields = true;
            $this->method_title = __('Pago con Yape', 'reycob-manual-payments-for-yape');
            $this->method_description = __('Acepta pagos con Yape mediante comprobante y verificación manual.', 'reycob-manual-payments-for-yape');
            $this->supports = array('products');

            $this->init_form_fields();
            $this->init_settings();

            $this->enabled = $this->get_option('enabled', 'yes');
            $this->title = $this->get_option('title', __('Yape', 'reycob-manual-payments-for-yape'));
            $this->description = $this->get_option('description', __('Paga con Yape y sube tu comprobante.', 'reycob-manual-payments-for-yape'));
            $legacy_descriptions = array(
                'Pay with Yape and upload your receipt.',
                'Pay with Yape by scanning the QR code or copying the phone number. Then upload your receipt.',
            );
            if (in_array($this->description, $legacy_descriptions, true)) {
                $this->description = __('Paga con Yape escaneando el código QR o copiando el número de teléfono. Luego, sube tu comprobante de pago.', 'reycob-manual-payments-for-yape');
                $this->settings['description'] = $this->description;
                update_option('woocommerce_' . $this->id . '_settings', $this->settings);
            }
            $this->phone = $this->get_option('phone', '');
            $this->holder_name = $this->get_option('holder_name', '');
            $this->qr_url = $this->get_option('qr_url', '');
            $this->capture_required = $this->get_option('capture_required', 'yes');
            $this->approve_status = $this->get_option('approve_status', 'processing');
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'show_order_meta'));
            add_filter('woocommerce_email_order_meta_fields', array($this, 'email_order_meta'), 10, 3);
            add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
            add_action('admin_enqueue_scripts', array($this, 'admin_settings_assets'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Activar', 'reycob-manual-payments-for-yape'),
                    'type' => 'checkbox',
                    'label' => __('Activar pagos con Yape', 'reycob-manual-payments-for-yape'),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => __('Título', 'reycob-manual-payments-for-yape'),
                    'type' => 'text',
                    'default' => 'Yape',
                    'description' => __('Nombre del método de pago que verán los clientes.', 'reycob-manual-payments-for-yape'),
                ),
                'description' => array(
                    'title' => __('Descripción', 'reycob-manual-payments-for-yape'),
                    'type' => 'textarea',
                    'default' => __('Paga con Yape escaneando el código QR o copiando el número de teléfono. Luego, sube tu comprobante de pago.', 'reycob-manual-payments-for-yape'),
                ),
                'phone' => array(
                    'title' => __('Número de Yape', 'reycob-manual-payments-for-yape'),
                    'type' => 'text',
                    'default' => '',
                    'description' => __('Número de teléfono asociado a la cuenta de Yape.', 'reycob-manual-payments-for-yape'),
                ),
                'holder_name' => array(
                    'title' => __('Titular de la cuenta', 'reycob-manual-payments-for-yape'),
                    'type' => 'text',
                    'default' => '',
                    'description' => __('Nombre mostrado junto al número de Yape.', 'reycob-manual-payments-for-yape'),
                ),
                'qr_url' => array(
                    'title' => __('Código QR', 'reycob-manual-payments-for-yape'),
                    'type' => 'qr_image',
                    'default' => '',
                    'description' => __('Sube o selecciona la imagen QR desde la biblioteca de medios.', 'reycob-manual-payments-for-yape'),
                ),
                'capture_required' => array(
                    'title' => __('Comprobante de pago', 'reycob-manual-payments-for-yape'),
                    'type' => 'checkbox',
                    'label' => __('Solicitar comprobante de pago', 'reycob-manual-payments-for-yape'),
                    'default' => 'yes',
                ),
                'approve_status' => array(
                    'title' => __('Estado después de aprobar', 'reycob-manual-payments-for-yape'),
                    'type' => 'select',
                    'default' => 'processing',
                    'options' => array(
                        'processing' => __('Procesando', 'reycob-manual-payments-for-yape'),
                        'completed' => __('Completado', 'reycob-manual-payments-for-yape'),
                    ),
                    'description' => __('Se recomienda Procesando para productos físicos.', 'reycob-manual-payments-for-yape'),
                ),
            );
        }

        public function payment_fields() {
            $total = WC()->cart ? WC()->cart->get_total() : '';
            ?>
            <div class="pyr-payment-box">
                <div class="pyr-payment-head">
                    <div>
                        <strong><span class="pyr-step" aria-hidden="true">1</span> <?php echo esc_html($this->title); ?></strong>
                        <?php if ($this->description) : ?>
                            <p><?php echo esc_html($this->description); ?></p>
                        <?php endif; ?>
                    </div>
                    <span class="pyr-badge"><?php esc_html_e('Verificación manual', 'reycob-manual-payments-for-yape'); ?></span>
                </div>

                <div class="pyr-payment-grid">
                    <?php if ($this->qr_url) : ?>
                        <div class="pyr-qr-card">
                            <img src="<?php echo esc_url($this->qr_url); ?>" alt="<?php esc_attr_e('Código QR de Yape', 'reycob-manual-payments-for-yape'); ?>">
                            <a class="pyr-qr-link" href="<?php echo esc_url($this->qr_url); ?>" target="_blank" rel="noopener noreferrer">
                                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M21 3l-7 7M9 21H3v-6m0 6 7-7"/></svg>
                                <?php esc_html_e('Ampliar QR', 'reycob-manual-payments-for-yape'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="pyr-payment-info">
                        <div class="pyr-amount">
                            <span><?php esc_html_e('Monto a pagar', 'reycob-manual-payments-for-yape'); ?></span>
                            <strong><?php echo wp_kses_post($total); ?></strong>
                        </div>

                        <?php if ($this->phone) : ?>
                            <div class="pyr-phone-row">
                                <span>
                                    <small><?php esc_html_e('Número de Yape', 'reycob-manual-payments-for-yape'); ?></small>
                                    <strong><?php echo esc_html($this->phone); ?></strong>
                                    <?php if ($this->holder_name) : ?>
                                        <em><?php echo esc_html($this->holder_name); ?></em>
                                    <?php endif; ?>
                                </span>
                                <button type="button" class="pyr-copy-btn" data-copy="<?php echo esc_attr($this->phone); ?>">
                                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    <span><?php esc_html_e('Copiar número', 'reycob-manual-payments-for-yape'); ?></span>
                                </button>
                            </div>
                            <div class="pyr-copy-status" role="status"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ('yes' === $this->capture_required) : ?>
                    <div class="pyr-upload-area">
                        <input id="pyr_capture_file" type="file" accept="image/jpeg,image/png,image/webp">
                        <label for="pyr_capture_file" class="pyr-upload-label">
                            <span class="pyr-step" aria-hidden="true">2</span>
                            <span class="pyr-upload-text">
                                <span class="pyr-upload-title"><?php esc_html_e('Comprobante de pago', 'reycob-manual-payments-for-yape'); ?></span>
                                <span class="pyr-upload-help"><?php esc_html_e('JPG, PNG o WebP · Máximo 5 MB', 'reycob-manual-payments-for-yape'); ?></span>
                            </span>
                            <span class="pyr-upload-button">
                                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m14-7-5-5-5 5m5-5v12"/></svg>
                                <?php esc_html_e('Subir imagen', 'reycob-manual-payments-for-yape'); ?>
                            </span>
                        </label>
                        <input type="hidden" id="pyr_capture_url" name="pyr_capture_url" value="">
                        <div class="pyr-upload-status" aria-live="polite"></div>
                        <div class="pyr-preview" hidden>
                            <img alt="<?php esc_attr_e('Comprobante de pago con Yape', 'reycob-manual-payments-for-yape'); ?>">
                            <button type="button" class="pyr-remove-file"><?php esc_html_e('Quitar', 'reycob-manual-payments-for-yape'); ?></button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }

        public function enqueue_assets($force = false) {
            if (!$force && !is_checkout() && !is_wc_endpoint_url('order-pay')) {
                return;
            }

            wp_enqueue_style('reycob-pyp-yape', REYCOB_PYP_URL . 'assets/style.css', array(), REYCOB_PYP_VERSION);
            wp_enqueue_script('reycob-pyp-yape', REYCOB_PYP_URL . 'assets/script.js', array('jquery'), REYCOB_PYP_VERSION, true);
            wp_localize_script('reycob-pyp-yape', 'reycobPypYape', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('reycob_pyp_yape_upload'),
                'uploading' => __('Subiendo comprobante...', 'reycob-manual-payments-for-yape'),
                'uploaded' => __('Comprobante subido.', 'reycob-manual-payments-for-yape'),
                'invalidType' => __('Sube una imagen JPG, PNG o WebP.', 'reycob-manual-payments-for-yape'),
                'tooLarge' => __('La imagen no debe superar los 5 MB.', 'reycob-manual-payments-for-yape'),
                'error' => __('No se pudo subir el comprobante.', 'reycob-manual-payments-for-yape'),
                'required' => __('Sube el comprobante antes de realizar el pedido.', 'reycob-manual-payments-for-yape'),
                'copied' => __('Copiado', 'reycob-manual-payments-for-yape'),
                'copyError' => __('No se pudo copiar. Selecciona el número para copiarlo manualmente.', 'reycob-manual-payments-for-yape'),
            ));
        }

        public function admin_settings_assets($hook) {
            if ('woocommerce_page_wc-settings' !== $hook) {
                return;
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing values determine whether settings assets are needed.
            $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing values determine whether settings assets are needed.
            $section = isset($_GET['section']) ? sanitize_key(wp_unslash($_GET['section'])) : '';

            if ('checkout' !== $tab || $this->id !== $section) {
                return;
            }

            wp_enqueue_media();
            wp_enqueue_style('reycob-pyp-yape-admin', REYCOB_PYP_URL . 'assets/admin-style.css', array(), REYCOB_PYP_VERSION);
            wp_enqueue_script('reycob-pyp-yape-admin-settings', REYCOB_PYP_URL . 'assets/admin-settings.js', array('jquery'), REYCOB_PYP_VERSION, true);
            wp_localize_script('reycob-pyp-yape-admin-settings', 'reycobPypYapeSettings', array(
                'mediaTitle' => __('Seleccionar imagen QR de Yape', 'reycob-manual-payments-for-yape'),
                'mediaButton' => __('Usar esta imagen QR', 'reycob-manual-payments-for-yape'),
                'imageAlt' => __('Código QR de Yape', 'reycob-manual-payments-for-yape'),
                'emptyText' => __('Sin imagen QR', 'reycob-manual-payments-for-yape'),
            ));
        }

        public function generate_qr_image_html($key, $data) {
            $field_key = $this->get_field_key($key);
            $value = $this->get_option($key, isset($data['default']) ? $data['default'] : '');
            $description = isset($data['description']) ? $data['description'] : '';

            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($field_key); ?>"><?php echo esc_html($data['title']); ?></label>
                </th>
                <td class="forminp">
                    <div class="pyr-qr-admin-field">
                        <input type="hidden" id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>" value="<?php echo esc_attr($value); ?>">
                        <div class="pyr-qr-admin-preview">
                            <?php if ($value) : ?>
                                <img src="<?php echo esc_url($value); ?>" alt="<?php esc_attr_e('Código QR de Yape', 'reycob-manual-payments-for-yape'); ?>">
                            <?php else : ?>
                                <span><?php esc_html_e('Sin imagen QR', 'reycob-manual-payments-for-yape'); ?></span>
                            <?php endif; ?>
                        </div>
                        <p>
                            <button type="button" class="button pyr-select-qr"><?php esc_html_e('Subir o seleccionar QR', 'reycob-manual-payments-for-yape'); ?></button>
                            <button type="button" class="button pyr-remove-qr"><?php esc_html_e('Quitar', 'reycob-manual-payments-for-yape'); ?></button>
                        </p>
                        <?php if ($description) : ?>
                            <p class="description"><?php echo esc_html($description); ?></p>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }

        public function validate_qr_image_field($key, $value) {
            return esc_url_raw($value);
        }

        public function validate_fields() {
            // WooCommerce verifies the checkout or order-pay nonce before invoking gateway validation.
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $capture_url = isset($_POST['pyr_capture_url']) ? $this->sanitize_capture_url(esc_url_raw(wp_unslash($_POST['pyr_capture_url']))) : '';
            if ('yes' === $this->capture_required && !$capture_url) {
                wc_add_notice(__('Sube el comprobante de pago con Yape.', 'reycob-manual-payments-for-yape'), 'error');
                return false;
            }

            return true;
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            if (!$order) {
                return array('result' => 'failure');
            }

            // WooCommerce verifies the checkout or order-pay nonce before invoking the gateway payment handler.
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $capture_url = isset($_POST['pyr_capture_url']) ? $this->sanitize_capture_url(esc_url_raw(wp_unslash($_POST['pyr_capture_url']))) : '';

            $order->update_status('on-hold', __('Pago con Yape recibido. Pendiente de verificación manual.', 'reycob-manual-payments-for-yape'));
            $order->update_meta_data('_pyr_yape_phone', $this->phone);
            $order->update_meta_data('_pyr_yape_holder', $this->holder_name);
            $order->update_meta_data('_pyr_yape_capture_url', $capture_url);
            $order->save();

            REYCOB_PYP_Manager::record_payment($order, $this->phone, $capture_url);

            WC()->cart->empty_cart();

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        }

        public function show_order_meta($order) {
            $capture_url = $order->get_meta('_pyr_yape_capture_url');
            if (!$capture_url) {
                return;
            }

            echo '<p><strong>' . esc_html__('Pago con Yape', 'reycob-manual-payments-for-yape') . '</strong><br>';
            echo esc_html__('Número de teléfono:', 'reycob-manual-payments-for-yape') . ' ' . esc_html($order->get_meta('_pyr_yape_phone')) . '<br>';
            echo '<a href="' . esc_url($capture_url) . '" target="_blank" rel="noopener">' . esc_html__('Ver comprobante', 'reycob-manual-payments-for-yape') . '</a></p>';
        }

        public function email_order_meta($fields, $sent_to_admin, $order) {
            $capture_url = $order->get_meta('_pyr_yape_capture_url');
            if (!$capture_url) {
                return $fields;
            }

            $fields['pyr_yape_payment'] = array(
                'label' => __('Pago con Yape', 'reycob-manual-payments-for-yape'),
                'value' => __('Pendiente de verificación manual', 'reycob-manual-payments-for-yape'),
            );

            if ($sent_to_admin) {
                $fields['pyr_yape_capture'] = array(
                    'label' => __('Comprobante de Yape', 'reycob-manual-payments-for-yape'),
                    'value' => $capture_url,
                );
            }

            return $fields;
        }

        private function sanitize_capture_url($url) {
            $url = esc_url_raw($url);
            $uploads = wp_get_upload_dir();
            $allowed_base = trailingslashit($uploads['baseurl']) . 'comprobantes-yape/';

            return 0 === strpos($url, $allowed_base) ? $url : '';
        }
    }
}

function reycob_pyp_add_gateway($gateways) {
    $gateways[] = 'REYCOB_PYP_Yape_Gateway';
    return $gateways;
}

function reycob_pyp_missing_woocommerce_notice() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    echo '<div class="notice notice-error"><p><strong>' . esc_html__('Reycob Pagos Manuales para Yape en Perú', 'reycob-manual-payments-for-yape') . '</strong> ' . esc_html__('necesita que WooCommerce esté activo.', 'reycob-manual-payments-for-yape') . '</p></div>';
}

require_once __DIR__ . '/reycob-related-plugins.php';
