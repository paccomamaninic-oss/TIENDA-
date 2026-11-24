<?php
/**
 * Contextual links to complementary Reycob plugins.
 *
 * @package reycob-manual-payments-for-yape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Each plugin registers its own screen-scoped closure, avoiding shared globals.
( static function () {
	$matches_screen = static function () {
		$screen = get_current_screen();
		if ( ! $screen || ! current_user_can( 'install_plugins' ) ) {
			return false;
		}
		$parts = explode( '_page_', $screen->id );
		$page = end( $parts );
		return in_array( $page, array( 'pyr-yape-payments' ), true );
	};

	add_action(
		'admin_enqueue_scripts',
		static function () use ( $matches_screen ) {
			if ( $matches_screen() ) {
				wp_enqueue_style( 'reycob-manual-payments-for-yape-related', plugin_dir_url( __FILE__ ) . 'reycob-related-plugins.css', array(), '1.0.0' );
				wp_enqueue_script( 'reycob-manual-payments-for-yape-related', plugin_dir_url( __FILE__ ) . 'reycob-related-plugins.js', array(), '1.0.0', true );
			}
		}
	);

	add_action(
		'admin_footer',
		static function () use ( $matches_screen ) {
			if ( ! $matches_screen() ) {
				return;
			}
			$recommendations = array( array( 'slug' => 'reycob-checkout', 'name' => 'Reycob Checkout', 'description' => __( 'Checkout por pasos, campos y comisiones configurables.', 'reycob-manual-payments-for-yape' ) ),
				array( 'slug' => 'reycob-metodos-envio-peru', 'name' => 'Reycob Metodos de Envio Peru', 'description' => __( 'Delivery y recojo en tienda con etiquetas personalizadas.', 'reycob-manual-payments-for-yape' ) ),
				array( 'slug' => 'reycob-libro-de-reclamaciones-peru', 'name' => 'Reycob Libro de Reclamaciones Peru', 'description' => __( 'Formulario de reclamaciones y gestion de registros.', 'reycob-manual-payments-for-yape' ) ) );
			$installed = get_plugins();
			?>
			<details class="reycob-related-plugins">
				<summary><?php esc_html_e( 'Otros plugins de Reycob', 'reycob-manual-payments-for-yape' ); ?></summary>
				<ul>
					<?php foreach ( $recommendations as $plugin ) : ?>
						<?php
						$is_installed = false;
						foreach ( array_keys( $installed ) as $file ) {
							if ( dirname( $file ) === $plugin['slug'] ) {
								$is_installed = true;
								break;
							}
						}
						?>
						<li>
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'reycob-related-icons/' . $plugin['slug'] . '.png' ); ?>" alt="" width="48" height="48" loading="lazy">
							<div><strong><?php echo esc_html( $plugin['name'] ); ?></strong><p><?php echo esc_html( $plugin['description'] ); ?></p></div>
							<div class="reycob-related-links">
								<?php if ( $is_installed ) : ?><span><?php esc_html_e( 'Instalado', 'reycob-manual-payments-for-yape' ); ?></span><?php endif; ?>
								<a href="<?php echo esc_url( 'https://wordpress.org/plugins/' . $plugin['slug'] . '/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ver detalles', 'reycob-manual-payments-for-yape' ); ?><span class="screen-reader-text">: <?php echo esc_html( $plugin['name'] ); ?></span></a>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</details>
			<?php
		}
	);
} )();
