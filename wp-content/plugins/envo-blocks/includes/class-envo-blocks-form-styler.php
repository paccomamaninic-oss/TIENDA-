<?php
/**
 * Form Styler: a form of a contact form plugin (Contact Form 7, WPForms, Fluent Forms, Ninja Forms, Formidable Forms,
 * Forminator, Gravity Forms – or any form shortcode) in the look of the block: fields, labels, the button and the
 * messages.
 *
 * - The block renders the shortcode of the plugin inside its wrapper; form-styler.css styles the fields of all plugins
 *   from the custom properties of the wrapper (one set of settings for every plugin).
 * - The editor lists the forms of the active plugins through the REST route envo-blocks/v1/forms.
 * - Without own colours the button takes the button style of the theme (theme.json elements.button).
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Form Styler.
 */
final class Envo_Blocks_Form_Styler {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Form plugins: key => array( name, whether it is active ).
	 *
	 * @return array
	 */
	public static function plugins() {
		return array(
			'cf7'        => array( 'Contact Form 7', class_exists( 'WPCF7_ContactForm' ) ),
			'wpforms'    => array( 'WPForms', function_exists( 'wpforms' ) ),
			'fluentform' => array( 'Fluent Forms', defined( 'FLUENTFORM' ) || function_exists( 'wpFluentForm' ) ),
			'ninja'      => array( 'Ninja Forms', function_exists( 'Ninja_Forms' ) ),
			'formidable' => array( 'Formidable Forms', class_exists( 'FrmForm' ) ),
			'forminator' => array( 'Forminator', class_exists( 'Forminator_API' ) ),
			'gravity'    => array( 'Gravity Forms', class_exists( 'GFAPI' ) ),
		);
	}

	/**
	 * Registers the route with the forms for the editor.
	 */
	public static function register_routes() {
		register_rest_route(
			'envo-blocks/v1',
			'/forms',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'rest_forms' ),
				'permission_callback' => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * The active form plugins with their forms.
	 *
	 * @return WP_REST_Response
	 */
	public static function rest_forms() {
		$list = array();
		foreach ( self::plugins() as $key => $plugin ) {
			if ( $plugin[1] ) {
				$list[] = array(
					'value' => $key,
					'label' => $plugin[0],
					'forms' => self::forms( $key ),
				);
			}
		}
		return rest_ensure_response( $list );
	}

	/**
	 * Forms of a plugin: array of array( value => ID, label => title ).
	 *
	 * @param string $plugin Plugin key.
	 * @return array
	 */
	public static function forms( $plugin ) {
		global $wpdb;

		$forms = array();
		switch ( $plugin ) {
			case 'cf7':
			case 'wpforms':
			case 'forminator':
				$types = array(
					'cf7'        => 'wpcf7_contact_form',
					'wpforms'    => 'wpforms',
					'forminator' => 'forminator_forms',
				);
				$posts = get_posts(
					array(
						'post_type'      => $types[ $plugin ],
						'post_status'    => 'publish',
						'posts_per_page' => 100,
						'orderby'        => 'title',
						'order'          => 'ASC',
					)
				);
				foreach ( $posts as $post ) {
					$forms[ $post->ID ] = $post->post_title;
					if ( 'forminator' === $plugin ) {
						// Forminator keeps the slug as the post title, the name in its settings.
						$meta = get_post_meta( $post->ID, 'forminator_form_meta', true );
						if ( ! empty( $meta['settings']['formName'] ) ) {
							$forms[ $post->ID ] = $meta['settings']['formName'];
						}
					}
				}
				break;
			case 'fluentform':
				$table = $wpdb->prefix . 'fluentform_forms';
				// The forms table of Fluent Forms (no API for a list), read only when the editor asks for the forms.
				$rows = $wpdb->get_results( "SELECT id, title FROM {$table} WHERE status = 'published' ORDER BY title" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				foreach ( (array) $rows as $row ) {
					$forms[ (int) $row->id ] = $row->title;
				}
				break;
			case 'ninja':
				foreach ( (array) Ninja_Forms()->form()->get_forms() as $form ) {
					$forms[ (int) $form->get_id() ] = (string) $form->get_setting( 'title' );
				}
				break;
			case 'formidable':
				foreach ( (array) FrmForm::get_published_forms() as $form ) {
					$forms[ (int) $form->id ] = $form->name;
				}
				break;
			case 'gravity':
				foreach ( (array) GFAPI::get_forms() as $form ) {
					$forms[ (int) $form['id'] ] = $form['title'];
				}
				break;
		}

		$options = array();
		foreach ( $forms as $id => $title ) {
			$options[] = array(
				'value' => (string) $id,
				/* translators: %d: form ID. */
				'label' => '' !== trim( (string) $title ) ? wp_strip_all_tags( (string) $title ) : sprintf( __( 'Form #%d', 'envo-blocks' ), $id ),
			);
		}
		return $options;
	}

	/**
	 * The shortcode of the form chosen in the block, or '' when there is none (or its plugin is not active).
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function shortcode( array $attributes ) {
		$plugin = isset( $attributes['plugin'] ) ? (string) $attributes['plugin'] : '';

		if ( 'shortcode' === $plugin ) {
			// Any one form shortcode, e.g. of a plugin not in the list; text around it is not allowed.
			$code = isset( $attributes['shortcode'] ) ? trim( (string) $attributes['shortcode'] ) : '';
			return '' !== $code && preg_match( '/^' . get_shortcode_regex() . '$/s', $code ) ? $code : '';
		}

		$plugins = self::plugins();
		$id      = isset( $attributes['formId'] ) ? absint( $attributes['formId'] ) : 0;
		if ( ! $id || empty( $plugins[ $plugin ][1] ) ) {
			return '';
		}

		$codes = array(
			'cf7'        => '[contact-form-7 id="%d"]',
			'wpforms'    => '[wpforms id="%d"]',
			'fluentform' => '[fluentform id="%d"]',
			'ninja'      => '[ninja_form id=%d]',
			'formidable' => '[formidable id=%d]',
			'forminator' => '[forminator_form id="%d"]',
			'gravity'    => '[gravityform id="%d" title="false" ajax="true"]',
		);
		return sprintf( $codes[ $plugin ], $id );
	}

	/**
	 * Custom properties of the theme button (theme.json elements.button), the default look of the submit button.
	 *
	 * @return string
	 */
	public static function theme_button_vars() {
		static $vars = null;
		if ( null !== $vars ) {
			return $vars;
		}

		$vars   = '';
		$button = function_exists( 'wp_get_global_styles' ) ? wp_get_global_styles( array( 'elements', 'button' ) ) : array();
		$map    = array(
			'--envo-fs-theme-btn-bg'     => array( 'color', 'background' ),
			'--envo-fs-theme-btn-text'   => array( 'color', 'text' ),
			'--envo-fs-theme-btn-radius' => array( 'border', 'radius' ),
		);
		foreach ( $map as $property => $path ) {
			$value = isset( $button[ $path[0] ][ $path[1] ] ) && is_string( $button[ $path[0] ][ $path[1] ] ) ? $button[ $path[0] ][ $path[1] ] : '';
			// "var:preset|color|contrast" = var(--wp--preset--color--contrast).
			if ( preg_match( '/^var:preset\|([a-z0-9-]+)\|([a-z0-9-]+)$/', $value, $m ) ) {
				$value = 'var(--wp--preset--' . $m[1] . '--' . $m[2] . ')';
			}
			if ( '' !== $value && preg_match( '/^(#[0-9a-fA-F]{3,8}|var\(--wp--[a-z0-9-]+\)|[0-9.]+(px|rem|em|%)|0)$/', $value ) ) {
				$vars .= $property . ':' . $value . ';';
			}
		}
		return $vars;
	}
}
