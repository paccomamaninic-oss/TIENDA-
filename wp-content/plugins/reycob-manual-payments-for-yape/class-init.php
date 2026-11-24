<?php

if (!defined('ABSPATH')) {
    exit;
}

final class REYCOB_PYP_Manager {
    public static function init() {
        register_activation_hook(REYCOB_PYP_FILE, array(__CLASS__, 'activate'));

        add_action('wp_ajax_reycob_pyp_upload_capture', array(__CLASS__, 'upload_capture'));
        add_action('wp_ajax_nopriv_reycob_pyp_upload_capture', array(__CLASS__, 'upload_capture'));
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_assets'));
        add_action('wp_ajax_reycob_pyp_update_payment_status', array(__CLASS__, 'update_payment_status'));
    }

    public static function activate() {
        global $wpdb;

        $table_name = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        // The table name comes only from the trusted WordPress database prefix.
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL,
            phone_number varchar(30) DEFAULT '',
            amount decimal(12,2) NOT NULL DEFAULT 0,
            capture_url text DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY order_id (order_id),
            KEY status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- dbDelta requires the complete schema statement.
        dbDelta($sql);

        update_option('pyr_db_version', REYCOB_PYP_DB_VERSION);
    }

    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'pyr_yape_payments';
    }

    public static function record_payment($order, $phone, $capture_url) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Payment records use a dedicated table.
        $wpdb->replace(
            self::table_name(),
            array(
                'order_id' => $order->get_id(),
                'phone_number' => sanitize_text_field($phone),
                'amount' => $order->get_total(),
                'capture_url' => esc_url_raw($capture_url),
                'status' => 'pending',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%f', '%s', '%s', '%s', '%s')
        );
    }

    public static function upload_capture() {
        check_ajax_referer('reycob_pyp_yape_upload', 'nonce');

        if (!function_exists('WC') || !WC()->session) {
            wp_send_json_error(array('message' => __('La sesión de pago no está disponible.', 'reycob-manual-payments-for-yape')));
        }

        $session_id = sanitize_key((string) WC()->session->get_customer_id());
        if (!$session_id) {
            wp_send_json_error(array('message' => __('La sesión de pago no está disponible.', 'reycob-manual-payments-for-yape')));
        }

        $rate_key = 'reycob_pyp_upload_rate_' . md5($session_id);
        $upload_count = (int) get_transient($rate_key);
        if ($upload_count >= 10) {
            wp_send_json_error(array('message' => __('Demasiados intentos de subida. Espera un momento e inténtalo nuevamente.', 'reycob-manual-payments-for-yape')));
        }

        if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
            wp_send_json_error(array('message' => __('No se recibió ningún archivo.', 'reycob-manual-payments-for-yape')));
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each file property is validated and sanitized below.
        $raw_file = wp_unslash($_FILES['file']);
        $file = array(
            'name' => isset($raw_file['name']) ? sanitize_file_name($raw_file['name']) : '',
            'type' => isset($raw_file['type']) ? sanitize_mime_type($raw_file['type']) : '',
            'tmp_name' => isset($raw_file['tmp_name']) ? sanitize_text_field($raw_file['tmp_name']) : '',
            'error' => isset($raw_file['error']) ? absint($raw_file['error']) : UPLOAD_ERR_NO_FILE,
            'size' => isset($raw_file['size']) ? absint($raw_file['size']) : 0,
        );
        $allowed_types = array('image/jpeg', 'image/png', 'image/webp');

        if (UPLOAD_ERR_OK !== $file['error'] || !$file['tmp_name'] || !$file['name'] || !is_uploaded_file($file['tmp_name'])) {
            wp_send_json_error(array('message' => __('No se pudo subir el archivo.', 'reycob-manual-payments-for-yape')));
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $checked_type = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        if (empty($checked_type['type']) || !in_array($checked_type['type'], $allowed_types, true)) {
            wp_send_json_error(array('message' => __('Este tipo de archivo no está permitido.', 'reycob-manual-payments-for-yape')));
        }

        if ((int) $file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(array('message' => __('El archivo supera el límite de 5 MB.', 'reycob-manual-payments-for-yape')));
        }

        add_filter('upload_dir', array(__CLASS__, 'capture_upload_dir'));
        $uploaded = wp_handle_upload($file, array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
            ),
        ));
        remove_filter('upload_dir', array(__CLASS__, 'capture_upload_dir'));

        if (isset($uploaded['error'])) {
            wp_send_json_error(array('message' => sanitize_text_field($uploaded['error'])));
        }

        set_transient($rate_key, $upload_count + 1, HOUR_IN_SECONDS);

        wp_send_json_success(array(
            'url' => esc_url_raw($uploaded['url']),
        ));
    }

    public static function capture_upload_dir($dirs) {
        $subdir = '/comprobantes-yape';

        $dirs['subdir'] = $subdir;
        $dirs['path'] = $dirs['basedir'] . $subdir;
        $dirs['url'] = $dirs['baseurl'] . $subdir;

        return $dirs;
    }

    public static function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Pagos con Yape', 'reycob-manual-payments-for-yape'),
            __('Pagos con Yape', 'reycob-manual-payments-for-yape'),
            'manage_woocommerce',
            'pyr-yape-payments',
            array(__CLASS__, 'render_admin_page')
        );
    }

    public static function admin_assets($hook) {
        if ('woocommerce_page_pyr-yape-payments' !== $hook) {
            return;
        }

        wp_enqueue_style('reycob-pyp-yape-admin', REYCOB_PYP_URL . 'assets/admin-style.css', array(), REYCOB_PYP_VERSION);
        wp_enqueue_script('reycob-pyp-yape-admin', REYCOB_PYP_URL . 'assets/admin-script.js', array('jquery'), REYCOB_PYP_VERSION, true);
        wp_localize_script('reycob-pyp-yape-admin', 'reycobPypYapeAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reycob_pyp_yape_admin'),
            'approveConfirm' => __('¿Seguro que deseas aprobar este pago?', 'reycob-manual-payments-for-yape'),
            'rejectConfirm' => __('¿Seguro que deseas rechazar este pago?', 'reycob-manual-payments-for-yape'),
            'updateError' => __('No se pudo actualizar el pago.', 'reycob-manual-payments-for-yape'),
            'connectionError' => __('Error de conexión.', 'reycob-manual-payments-for-yape'),
            'noActions' => __('No hay acciones disponibles', 'reycob-manual-payments-for-yape'),
        ));
    }

    public static function render_admin_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tienes permisos para acceder a esta página.', 'reycob-manual-payments-for-yape'));
        }

        global $wpdb;

        $table = self::table_name();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- The dashboard reads the plugin's dedicated table.
        $payments = $wpdb->get_results($wpdb->prepare('SELECT * FROM %i ORDER BY created_at DESC LIMIT %d', $table, 100));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- The dashboard reads the plugin's dedicated table.
        $pending = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM %i WHERE status = %s', $table, 'pending'));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- The dashboard reads the plugin's dedicated table.
        $approved = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM %i WHERE status = %s', $table, 'approved'));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- The dashboard reads the plugin's dedicated table.
        $rejected = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM %i WHERE status = %s', $table, 'rejected'));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- The dashboard reads the plugin's dedicated table.
        $total = (float) $wpdb->get_var($wpdb->prepare('SELECT SUM(amount) FROM %i WHERE status = %s', $table, 'approved'));
        ?>
        <div class="wrap pyr-admin">
            <div class="pyr-admin-header">
                <div>
                    <h1><?php esc_html_e('Pagos con Yape', 'reycob-manual-payments-for-yape'); ?></h1>
                    <p><?php esc_html_e('Revisa los comprobantes y confirma los pagos manualmente.', 'reycob-manual-payments-for-yape'); ?></p>
                </div>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=pago_yape_reycob')); ?>"><?php esc_html_e('Ajustes del método de pago', 'reycob-manual-payments-for-yape'); ?></a>
            </div>

            <div class="pyr-stats">
                <div><strong><?php echo esc_html($pending); ?></strong><span><?php esc_html_e('Pendientes', 'reycob-manual-payments-for-yape'); ?></span></div>
                <div><strong><?php echo esc_html($approved); ?></strong><span><?php esc_html_e('Aprobados', 'reycob-manual-payments-for-yape'); ?></span></div>
                <div><strong><?php echo esc_html($rejected); ?></strong><span><?php esc_html_e('Rechazados', 'reycob-manual-payments-for-yape'); ?></span></div>
                <div><strong><?php echo wp_kses_post(wc_price($total)); ?></strong><span><?php esc_html_e('Total aprobado', 'reycob-manual-payments-for-yape'); ?></span></div>
            </div>

            <table class="widefat striped pyr-payments-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Pedido', 'reycob-manual-payments-for-yape'); ?></th>
                        <th><?php esc_html_e('Cliente', 'reycob-manual-payments-for-yape'); ?></th>
                        <th><?php esc_html_e('Monto', 'reycob-manual-payments-for-yape'); ?></th>
                        <th><?php esc_html_e('Estado', 'reycob-manual-payments-for-yape'); ?></th>
                        <th><?php esc_html_e('Comprobante', 'reycob-manual-payments-for-yape'); ?></th>
                        <th><?php esc_html_e('Fecha', 'reycob-manual-payments-for-yape'); ?></th>
                        <th><?php esc_html_e('Acciones', 'reycob-manual-payments-for-yape'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)) : ?>
                        <tr><td colspan="7"><?php esc_html_e('Todavía no se han registrado pagos con Yape.', 'reycob-manual-payments-for-yape'); ?></td></tr>
                    <?php endif; ?>

                    <?php foreach ($payments as $payment) :
                        $order = wc_get_order($payment->order_id);
                        $customer = $order ? trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) : __('Pedido no encontrado', 'reycob-manual-payments-for-yape');
                        ?>
                        <tr data-payment-id="<?php echo esc_attr($payment->id); ?>">
                            <td>
                                <?php if ($order) : ?>
                                    <a href="<?php echo esc_url($order->get_edit_order_url()); ?>">#<?php echo esc_html($payment->order_id); ?></a>
                                <?php else : ?>
                                    #<?php echo esc_html($payment->order_id); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($customer); ?></td>
                            <td><?php echo wp_kses_post(wc_price((float) $payment->amount)); ?></td>
                            <td><span class="pyr-status pyr-status-<?php echo esc_attr($payment->status); ?>"><?php echo esc_html(self::status_label($payment->status)); ?></span></td>
                            <td class="pyr-capture-cell">
                                <?php if ($payment->capture_url) : ?>
                                    <img src="<?php echo esc_url($payment->capture_url); ?>" alt="<?php esc_attr_e('Comprobante de pago con Yape', 'reycob-manual-payments-for-yape'); ?>">
                                    <a class="button button-small" href="<?php echo esc_url($payment->capture_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Ver comprobante', 'reycob-manual-payments-for-yape'); ?></a>
                                <?php else : ?>
                                    <span class="pyr-muted"><?php esc_html_e('Sin archivo', 'reycob-manual-payments-for-yape'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html(mysql2date('d/m/Y H:i', $payment->created_at)); ?></td>
                            <td>
                                <?php if ('pending' === $payment->status) : ?>
                                    <button class="button button-primary pyr-status-action" data-status="approved"><?php esc_html_e('Aprobar pago', 'reycob-manual-payments-for-yape'); ?></button>
                                    <button class="button pyr-status-action" data-status="rejected"><?php esc_html_e('Rechazar', 'reycob-manual-payments-for-yape'); ?></button>
                                <?php else : ?>
                                    <span class="pyr-muted"><?php esc_html_e('No hay acciones disponibles', 'reycob-manual-payments-for-yape'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function update_payment_status() {
        check_ajax_referer('reycob_pyp_yape_admin', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('No tienes permisos para realizar esta acción.', 'reycob-manual-payments-for-yape')));
        }

        $payment_id = isset($_POST['payment_id']) ? absint(wp_unslash($_POST['payment_id'])) : 0;
        $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';

        if (!$payment_id || !in_array($status, array('approved', 'rejected'), true)) {
            wp_send_json_error(array('message' => __('Los datos del pago no son válidos.', 'reycob-manual-payments-for-yape')));
        }

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Payment records use a dedicated table.
        $payment = $wpdb->get_row($wpdb->prepare('SELECT * FROM %i WHERE id = %d', self::table_name(), $payment_id));
        if (!$payment) {
            wp_send_json_error(array('message' => __('No se encontró el pago.', 'reycob-manual-payments-for-yape')));
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Payment records use a dedicated table.
        $wpdb->update(
            self::table_name(),
            array(
                'status' => $status,
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $payment_id),
            array('%s', '%s'),
            array('%d')
        );

        $order = wc_get_order($payment->order_id);
        if ($order) {
            if ('approved' === $status) {
                $gateway_settings = get_option('woocommerce_pago_yape_reycob_settings', array());
                $approve_status = isset($gateway_settings['approve_status']) ? $gateway_settings['approve_status'] : 'processing';
                $order->payment_complete();
                $order->update_status($approve_status, __('Pago con Yape aprobado manualmente.', 'reycob-manual-payments-for-yape'));
                $order->add_order_note(__('Pago con Yape aprobado desde el panel de pagos.', 'reycob-manual-payments-for-yape'), true);
            } else {
                $order->update_status('failed', __('Pago con Yape rechazado manualmente.', 'reycob-manual-payments-for-yape'));
                $order->add_order_note(__('Pago con Yape rechazado desde el panel de pagos.', 'reycob-manual-payments-for-yape'), true);
            }
        }

        wp_send_json_success(array(
            'label' => self::status_label($status),
            'status' => $status,
        ));
    }

    public static function status_label($status) {
        $labels = array(
            'pending' => __('Pendiente', 'reycob-manual-payments-for-yape'),
            'approved' => __('Aprobado', 'reycob-manual-payments-for-yape'),
            'rejected' => __('Rechazado', 'reycob-manual-payments-for-yape'),
        );

        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }
}
