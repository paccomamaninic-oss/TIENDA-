<?php
/**
 * Checkout Fields: the address fields of WooCommerce (show, required, label, order) and own fields (text, select,
 * checkbox) for the block checkout and the classic [woocommerce_checkout].
 *
 * - Address fields: the country locale of WooCommerce (default + every country), which both checkouts read.
 * - Own fields, block checkout: woocommerce_register_additional_checkout_field() (WooCommerce 8.9+); WooCommerce
 *   saves them and shows them with the order, in e-mails and in My account.
 * - Own fields, classic checkout: added by woocommerce_checkout_fields and saved under the same order meta, so they
 *   are shown in the same places.
 *
 * Settings: Envo Blocks > Checkout Fields (option envo_blocks_checkout_fields). Nothing changes until it is saved.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checkout fields.
 */
final class Envo_Blocks_Checkout_Fields {

	const OPTION    = 'envo_blocks_checkout_fields';
	const PAGE      = 'envo-blocks-checkout';
	const FIELD_NS  = 'envo-blocks';
	const MAX       = 30;

	/**
	 * Address fields that can be changed (key => can be hidden).
	 */
	const ADDRESS = array(
		'first_name' => true,
		'last_name'  => true,
		'company'    => true,
		'country'    => false,
		'address_1'  => true,
		'address_2'  => true,
		'city'       => true,
		'state'      => true,
		'postcode'   => true,
		'phone'      => true,
	);

	/**
	 * Settings of this request.
	 *
	 * @var array|null
	 */
	private static $settings = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'envo_blocks_admin_pages', array( __CLASS__, 'admin_page' ) );
		add_action( 'admin_post_envo_blocks_checkout_fields', array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );

		if ( ! self::settings()['saved'] ) {
			return;
		}
		add_filter( 'woocommerce_get_country_locale_default', array( __CLASS__, 'locale_default' ), 20 );
		add_filter( 'woocommerce_get_country_locale', array( __CLASS__, 'locale_countries' ), 20 );
		foreach ( array( 'company', 'address_2', 'phone' ) as $key ) {
			add_filter( "option_woocommerce_checkout_{$key}_field", array( __CLASS__, 'visibility_option' ), 20, 2 );
			add_filter( "default_option_woocommerce_checkout_{$key}_field", array( __CLASS__, 'visibility_option' ), 20, 2 );
		}
		add_filter( 'woocommerce_default_address_fields', array( __CLASS__, 'default_address_fields' ), 20 );
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'classic_fields' ), 20 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'classic_save' ), 10, 2 );
		add_action( 'woocommerce_init', array( __CLASS__, 'register_block_fields' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Settings                                                           */
	/* ------------------------------------------------------------------ */

	/**
	 * Saved settings: array( saved, address => key => array( visible, required, label, order ), fields => list ).
	 *
	 * @return array
	 */
	public static function settings() {
		if ( null === self::$settings ) {
			$raw            = get_option( self::OPTION, array() );
			self::$settings = self::sanitize( is_array( $raw ) ? $raw : array() );
			self::$settings['saved'] = ! empty( $raw );
		}
		return self::$settings;
	}

	/**
	 * Cleans settings (from the option or from the form).
	 *
	 * @param array $raw Raw settings.
	 * @return array
	 */
	private static function sanitize( array $raw ) {
		$out   = array(
			'address' => array(),
			'fields'  => array(),
		);
		$order = 0;
		$given = isset( $raw['address'] ) && is_array( $raw['address'] ) ? $raw['address'] : array();
		// The order of the form (or of the saved settings), then any field that is missing.
		foreach ( array_keys( $given + self::ADDRESS ) as $key ) {
			if ( ! isset( self::ADDRESS[ $key ] ) ) {
				continue;
			}
			$item                   = isset( $given[ $key ] ) && is_array( $given[ $key ] ) ? $given[ $key ] : array();
			$out['address'][ $key ] = array(
				'visible'  => ! self::ADDRESS[ $key ] || ! empty( $item['visible'] ) || ! isset( $given[ $key ] ),
				'required' => isset( $item['required'] ) ? (bool) $item['required'] : null,
				'label'    => isset( $item['label'] ) ? sanitize_text_field( wp_unslash( (string) $item['label'] ) ) : '',
				'order'    => ++$order,
			);
		}

		$ids = array();
		foreach ( array_slice( isset( $raw['fields'] ) && is_array( $raw['fields'] ) ? array_values( $raw['fields'] ) : array(), 0, self::MAX ) as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$label = isset( $item['label'] ) ? sanitize_text_field( wp_unslash( (string) $item['label'] ) ) : '';
			if ( '' === $label ) {
				continue;
			}
			$id = sanitize_key( isset( $item['id'] ) && '' !== $item['id'] ? (string) $item['id'] : sanitize_title( $label ) );
			$id = '' === $id ? 'field' : substr( $id, 0, 40 );
			for ( $base = $id, $n = 2; isset( $ids[ $id ] ); $n++ ) {
				$id = $base . '-' . $n;
			}
			$ids[ $id ] = true;
			$type       = isset( $item['type'] ) && in_array( $item['type'], array( 'text', 'select', 'checkbox' ), true ) ? $item['type'] : 'text';
			$options    = array();
			if ( 'select' === $type && isset( $item['options'] ) ) {
				$lines   = is_array( $item['options'] ) ? $item['options'] : preg_split( '/\r\n|\r|\n/', wp_unslash( (string) $item['options'] ) );
				$options = array_slice( array_values( array_unique( array_filter( array_map( 'sanitize_text_field', $lines ), 'strlen' ) ) ), 0, 50 );
				if ( ! $options ) {
					$type = 'text';
				}
			}
			$out['fields'][] = array(
				'id'       => $id,
				'label'    => $label,
				'type'     => $type,
				'location' => isset( $item['location'] ) && in_array( $item['location'], array( 'contact', 'address', 'order' ), true ) ? $item['location'] : 'order',
				'required' => ! empty( $item['required'] ),
				'options'  => $options,
			);
		}
		return $out;
	}

	/**
	 * Default settings of the address fields of WooCommerce (label, required).
	 *
	 * @return array key => array( label, required )
	 */
	private static function woo_defaults() {
		$out = array();
		if ( ! function_exists( 'WC' ) || ! WC()->countries ) {
			return $out;
		}
		// The fields of WooCommerce without the changes of this page (a hidden company is not in the list otherwise).
		$visibility = array( 'company', 'address_2', 'phone' );
		remove_filter( 'woocommerce_default_address_fields', array( __CLASS__, 'default_address_fields' ), 20 );
		foreach ( $visibility as $key ) {
			remove_filter( "option_woocommerce_checkout_{$key}_field", array( __CLASS__, 'visibility_option' ), 20 );
			add_filter( "option_woocommerce_checkout_{$key}_field", array( __CLASS__, 'shown' ), 30 );
		}
		$fields = WC()->countries->get_default_address_fields();
		foreach ( $visibility as $key ) {
			remove_filter( "option_woocommerce_checkout_{$key}_field", array( __CLASS__, 'shown' ), 30 );
			add_filter( "option_woocommerce_checkout_{$key}_field", array( __CLASS__, 'visibility_option' ), 20, 2 );
		}
		add_filter( 'woocommerce_default_address_fields', array( __CLASS__, 'default_address_fields' ), 20 );
		$fallback = array(
			'company' => __( 'Company name', 'envo-blocks' ),
			'phone'   => __( 'Phone', 'envo-blocks' ),
		);
		foreach ( array_keys( self::ADDRESS ) as $key ) {
			$out[ $key ] = array(
				'label'    => isset( $fields[ $key ]['label'] ) ? wp_strip_all_tags( (string) $fields[ $key ]['label'] ) : ( isset( $fallback[ $key ] ) ? $fallback[ $key ] : $key ),
				'required' => isset( $fields[ $key ]['required'] ) ? (bool) $fields[ $key ]['required'] : false,
			);
		}
		return $out;
	}

	/**
	 * A field of WooCommerce's own visibility setting as shown (only while the default labels are read).
	 *
	 * @param mixed $value Option value.
	 * @return mixed
	 */
	public static function shown( $value ) {
		return 'hidden' === $value ? 'optional' : $value;
	}

	/* ------------------------------------------------------------------ */
	/* Address fields (both checkouts)                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Changes of one address field as locale settings.
	 *
	 * @param string $key  Field key.
	 * @param array  $item Settings of the field.
	 * @param bool   $all  All settings (default locale) or only what must win over a country (hidden, optional).
	 * @return array
	 */
	private static function locale_item( $key, array $item, $all ) {
		$out = array();
		if ( ! $item['visible'] ) {
			return array(
				'hidden'   => true,
				'required' => false,
			);
		}
		if ( false === $item['required'] ) {
			$out['required'] = false;
		} elseif ( $all && true === $item['required'] ) {
			$out['required'] = true;
		}
		if ( $all ) {
			if ( '' !== $item['label'] ) {
				$out['label'] = $item['label'];
				/* translators: %s: field label. */
				$out['optionalLabel'] = sprintf( __( '%s (optional)', 'envo-blocks' ), $item['label'] );
			}
			$out['priority'] = $item['order'] * 10;
			$out['index']    = $item['order'] * 10;
			$out['hidden']   = false;
		}
		return $out;
	}

	/**
	 * Default address locale (both checkouts, every country without own rules).
	 *
	 * @param array $locale Default locale.
	 * @return array
	 */
	public static function locale_default( $locale ) {
		foreach ( self::settings()['address'] as $key => $item ) {
			$locale[ $key ] = array_merge( isset( $locale[ $key ] ) && is_array( $locale[ $key ] ) ? $locale[ $key ] : array(), self::locale_item( $key, $item, true ) );
		}
		return $locale;
	}

	/**
	 * Country locales. The block checkout reads only these (not the default locale), so every country gets the
	 * changes: a hidden or optional field stays so everywhere; a new label, "required" and a new order apply where
	 * the country has no own rule for them (a country's own label such as "County" or its own order stay).
	 *
	 * @param array $locales Locales by country.
	 * @return array
	 */
	public static function locale_countries( $locales ) {
		$locales   = (array) $locales;
		$countries = function_exists( 'WC' ) && WC()->countries ? array_keys( (array) WC()->countries->get_countries() ) : array();
		$moved     = self::order_changed();
		foreach ( array_unique( array_merge( array_keys( $locales ), $countries ) ) as $country ) {
			$fields = isset( $locales[ $country ] ) && is_array( $locales[ $country ] ) ? $locales[ $country ] : array();
			foreach ( self::settings()['address'] as $key => $item ) {
				$own    = isset( $fields[ $key ] ) && is_array( $fields[ $key ] ) ? $fields[ $key ] : array();
				$change = self::locale_item( $key, $item, false );
				if ( $item['visible'] ) {
					if ( '' !== $item['label'] && ! isset( $own['label'] ) ) {
						$change['label'] = $item['label'];
						/* translators: %s: field label. */
						$change['optionalLabel'] = sprintf( __( '%s (optional)', 'envo-blocks' ), $item['label'] );
					}
					if ( true === $item['required'] && ! isset( $own['required'] ) && empty( $own['hidden'] ) ) {
						$change['required'] = true;
					}
					if ( $moved && ! isset( $own['priority'] ) ) {
						$change['priority'] = $item['order'] * 10;
					}
				}
				if ( $change ) {
					$locales[ $country ][ $key ] = array_merge( $own, $change );
				}
			}
		}
		return $locales;
	}

	/**
	 * Whether the address fields were put in another order than the one of WooCommerce.
	 *
	 * @return bool
	 */
	private static function order_changed() {
		return array_keys( self::settings()['address'] ) !== array_keys( self::ADDRESS );
	}

	/**
	 * The fields WooCommerce shows or hides by its own setting (Customizer / block settings): company, address line 2
	 * and phone. The block checkout and the classic checkout read these options, so they follow the Envo Blocks
	 * settings too (otherwise a hidden phone could still be required when the order is placed).
	 *
	 * @param mixed  $value  Option value.
	 * @param string $option Option name.
	 * @return string
	 */
	public static function visibility_option( $value, $option ) {
		$key  = str_replace( array( 'woocommerce_checkout_', '_field' ), '', (string) $option );
		$item = isset( self::settings()['address'][ $key ] ) ? self::settings()['address'][ $key ] : null;
		if ( ! $item || null === $item['required'] ) {
			return $value;
		}
		if ( ! $item['visible'] ) {
			return 'hidden';
		}
		return $item['required'] ? 'required' : 'optional';
	}

	/**
	 * Default address fields (classic forms): label, required and order.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	public static function default_address_fields( $fields ) {
		foreach ( self::settings()['address'] as $key => $item ) {
			if ( ! isset( $fields[ $key ] ) ) {
				continue;
			}
			$fields[ $key ]['priority'] = $item['order'] * 10;
			if ( '' !== $item['label'] ) {
				$fields[ $key ]['label'] = $item['label'];
				// Address line 2 shows its label only as the placeholder.
				if ( isset( $fields[ $key ]['placeholder'] ) ) {
					$fields[ $key ]['placeholder'] = $item['label'];
				}
			}
			if ( ! $item['visible'] || false === $item['required'] ) {
				$fields[ $key ]['required'] = false;
			} elseif ( true === $item['required'] ) {
				$fields[ $key ]['required'] = true;
			}
		}
		return $fields;
	}

	/* ------------------------------------------------------------------ */
	/* Own fields                                                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Block checkout: registers the own fields (WooCommerce saves and shows them).
	 */
	public static function register_block_fields() {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
			return;
		}
		foreach ( self::settings()['fields'] as $field ) {
			$args = array(
				'id'       => self::FIELD_NS . '/' . $field['id'],
				'label'    => $field['label'],
				'location' => $field['location'],
				'type'     => $field['type'],
				'required' => $field['required'],
			);
			if ( 'select' === $field['type'] ) {
				$args['options'] = array_map(
					static function ( $option ) {
						return array(
							'value' => $option,
							'label' => $option,
						);
					},
					$field['options']
				);
			}
			woocommerce_register_additional_checkout_field( $args );
		}
	}

	/**
	 * Classic checkout: hides fields and adds the own fields.
	 *
	 * @param array $groups Checkout fields by group (billing, shipping, account, order).
	 * @return array
	 */
	public static function classic_fields( $groups ) {
		foreach ( array( 'billing', 'shipping' ) as $group ) {
			foreach ( self::settings()['address'] as $key => $item ) {
				$name = $group . '_' . $key;
				if ( ! isset( $groups[ $group ][ $name ] ) ) {
					continue;
				}
				if ( ! $item['visible'] ) {
					unset( $groups[ $group ][ $name ] );
					continue;
				}
				// The phone is not a default address field: its order, label and "required" are set here.
				$groups[ $group ][ $name ]['priority'] = $item['order'] * 10;
				if ( '' !== $item['label'] ) {
					$groups[ $group ][ $name ]['label'] = $item['label'];
				}
				if ( null !== $item['required'] && 'country' !== $key ) {
					$groups[ $group ][ $name ]['required'] = $item['required'];
				}
			}
		}
		$priority = 300;
		foreach ( self::settings()['fields'] as $field ) {
			$form = array(
				'type'     => $field['type'],
				'label'    => $field['label'],
				'required' => $field['required'],
				'class'    => array( 'form-row-wide' ),
				'priority' => ++$priority,
			);
			if ( 'select' === $field['type'] ) {
				$form['options'] = array( '' => __( 'Select an option…', 'envo-blocks' ) ) + array_combine( $field['options'], $field['options'] );
			}
			if ( 'address' === $field['location'] ) {
				$groups['billing'][ 'billing_' . self::classic_key( $field ) ]   = $form;
				$groups['shipping'][ 'shipping_' . self::classic_key( $field ) ] = $form;
			} elseif ( 'contact' === $field['location'] ) {
				$groups['billing'][ self::classic_key( $field ) ] = $form;
			} else {
				$groups['order'][ self::classic_key( $field ) ] = $form;
			}
		}
		return $groups;
	}

	/**
	 * Name of an own field in the classic checkout form.
	 *
	 * @param array $field Field.
	 * @return string
	 */
	private static function classic_key( array $field ) {
		return 'envo_blocks_' . str_replace( '-', '_', $field['id'] );
	}

	/**
	 * The checkout fields service of WooCommerce (it saves the values of additional fields), or null.
	 *
	 * @return object|null
	 */
	private static function storage() {
		$package = 'Automattic\WooCommerce\Blocks\Package';
		$service = 'Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields';
		if ( ! class_exists( $package ) || ! class_exists( $service ) ) {
			return null;
		}
		try {
			$fields = $package::container()->get( $service );
		} catch ( \Throwable $e ) {
			return null;
		}
		return is_object( $fields ) && method_exists( $fields, 'persist_field_for_order' ) ? $fields : null;
	}

	/**
	 * Classic checkout: saves the own fields like the block checkout does (same order meta).
	 *
	 * @param WC_Order $order Order.
	 * @param array    $data  Posted checkout data.
	 */
	public static function classic_save( $order, $data ) {
		$storage = self::storage();
		foreach ( self::settings()['fields'] as $field ) {
			$key = self::FIELD_NS . '/' . $field['id'];
			$set = array();
			if ( 'address' === $field['location'] ) {
				$set['billing'] = 'billing_' . self::classic_key( $field );
				if ( ! empty( $data['ship_to_different_address'] ) ) {
					$set['shipping'] = 'shipping_' . self::classic_key( $field );
				}
			} else {
				$set['other'] = self::classic_key( $field );
			}
			foreach ( $set as $group => $name ) {
				if ( ! array_key_exists( $name, (array) $data ) ) {
					continue;
				}
				$value = 'checkbox' === $field['type'] ? (bool) $data[ $name ] : sanitize_text_field( (string) $data[ $name ] );
				if ( $storage ) {
					$storage->persist_field_for_order( $key, $value, $order, $group, false );
				} else {
					$order->update_meta_data( '_wc_' . $group . '/' . $key, is_bool( $value ) ? ( $value ? '1' : '0' ) : $value );
				}
			}
			if ( isset( $set['billing'] ) && ! isset( $set['shipping'] ) && $storage && array_key_exists( $set['billing'], (array) $data ) ) {
				// One address for both: copy it to shipping, as the classic checkout does with the address.
				$storage->persist_field_for_order( $key, sanitize_text_field( (string) $data[ $set['billing'] ] ), $order, 'shipping', false );
			}
		}
	}

	/* ------------------------------------------------------------------ */
	/* Admin page                                                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Envo Blocks > Checkout Fields (only with WooCommerce).
	 *
	 * @param array $pages Pages of the Envo Blocks menu.
	 * @return array
	 */
	public static function admin_page( $pages ) {
		if ( class_exists( 'WooCommerce' ) ) {
			$pages[ self::PAGE ] = array(
				'menu'       => __( 'Checkout Fields', 'envo-blocks' ),
				'title'      => __( 'Checkout Fields', 'envo-blocks' ),
				'callback'   => array( __CLASS__, 'render' ),
				'capability' => 'manage_woocommerce',
			);
		}
		return $pages;
	}

	/**
	 * Styles and script of the page.
	 *
	 * @param string $hook Admin page.
	 */
	public static function assets( $hook ) {
		if ( false === strpos( (string) $hook, self::PAGE ) ) {
			return;
		}
		wp_enqueue_style( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/css/checkout-fields.css', array(), Envo_Blocks_Blocks::ver( 'assets/css/checkout-fields.css' ) );
		wp_enqueue_script( 'envo-blocks-checkout-fields', ENVO_BLOCKS_URL . 'assets/js/checkout-fields.js', array( 'jquery', 'jquery-ui-sortable' ), Envo_Blocks_Blocks::ver( 'assets/js/checkout-fields.js' ), true );
	}

	/**
	 * Saves the form (admin-post.php).
	 */
	public static function save() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change the checkout fields.', 'envo-blocks' ), 403 );
		}
		check_admin_referer( 'envo_blocks_checkout_fields' );
		$back = admin_url( 'admin.php?page=' . self::PAGE );

		if ( isset( $_POST['reset'] ) ) {
			delete_option( self::OPTION );
			wp_safe_redirect( add_query_arg( 'eb-cf', 'reset', $back ) );
			exit;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cleaned field by field in sanitize().
		$raw      = isset( $_POST['eb_cf'] ) && is_array( $_POST['eb_cf'] ) ? wp_unslash( $_POST['eb_cf'] ) : array();
		$settings = self::sanitize( $raw );
		// Unchecked boxes send nothing: "required" is a yes/no for every row of the form.
		foreach ( $settings['address'] as $key => &$item ) {
			$item['required'] = ! empty( $raw['address'][ $key ]['required'] );
		}
		unset( $item );
		update_option( self::OPTION, $settings, true );
		wp_safe_redirect( add_query_arg( 'eb-cf', 'saved', $back ) );
		exit;
	}

	/**
	 * The page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$settings = self::settings();
		$defaults = self::woo_defaults();
		if ( ! $settings['saved'] ) {
			// Before the first save the page shows what WooCommerce does now (its own setting of these three fields).
			$utils = 'Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils';
			foreach ( array( 'company', 'address_2', 'phone' ) as $key ) {
				$getter = 'get_' . $key . '_field_visibility';
				$value  = class_exists( $utils ) && method_exists( $utils, $getter ) ? $utils::$getter() : get_option( 'woocommerce_checkout_' . $key . '_field', 'optional' );
				$settings['address'][ $key ]['visible']  = 'hidden' !== $value;
				$settings['address'][ $key ]['required'] = 'required' === $value;
			}
		}
		$types    = array(
			'text'     => __( 'Text', 'envo-blocks' ),
			'select'   => __( 'Select', 'envo-blocks' ),
			'checkbox' => __( 'Checkbox', 'envo-blocks' ),
		);
		$places   = array(
			'contact' => __( 'Contact', 'envo-blocks' ),
			'address' => __( 'Address (billing and shipping)', 'envo-blocks' ),
			'order'   => __( 'Order (additional information)', 'envo-blocks' ),
		);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only which notice to show after a redirect.
		$done = isset( $_GET['eb-cf'] ) ? sanitize_key( wp_unslash( $_GET['eb-cf'] ) ) : '';
		?>
		<div class="eb-cf">
			<?php if ( 'saved' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The checkout fields were saved.', 'envo-blocks' ); ?></p></div>
			<?php elseif ( 'reset' === $done ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'The checkout fields are back to the settings of WooCommerce.', 'envo-blocks' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="envo_blocks_checkout_fields">
				<?php wp_nonce_field( 'envo_blocks_checkout_fields' ); ?>

				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'Address fields', 'envo-blocks' ); ?></h2>
					<p class="eb-cf__intro"><?php esc_html_e( 'The billing and shipping address of the checkout (block checkout and classic checkout). Drag the rows to change the order. Some countries have their own rules (for example no postcode or state); a field you hide or make optional stays so in every country.', 'envo-blocks' ); ?></p>
					<table class="eb-cf__table widefat">
						<thead><tr>
							<th class="eb-cf__handle-col"><span class="screen-reader-text"><?php esc_html_e( 'Order', 'envo-blocks' ); ?></span></th>
							<th><?php esc_html_e( 'Field', 'envo-blocks' ); ?></th>
							<th><?php esc_html_e( 'Label', 'envo-blocks' ); ?></th>
							<th class="eb-cf__check-col"><?php esc_html_e( 'Show', 'envo-blocks' ); ?></th>
							<th class="eb-cf__check-col"><?php esc_html_e( 'Required', 'envo-blocks' ); ?></th>
						</tr></thead>
						<tbody class="eb-cf__sortable">
							<?php
							foreach ( $settings['address'] as $key => $item ) :
								$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : array(
									'label'    => $key,
									'required' => false,
								);
								$required = null === $item['required'] ? $default['required'] : $item['required'];
								$name     = 'eb_cf[address][' . $key . ']';
								?>
								<tr>
									<td class="eb-cf__handle" title="<?php esc_attr_e( 'Drag to change the order', 'envo-blocks' ); ?>" aria-hidden="true">⋮⋮</td>
									<td><strong><?php echo esc_html( $default['label'] ); ?></strong><code><?php echo esc_html( $key ); ?></code></td>
									<td><input type="text" class="regular-text" name="<?php echo esc_attr( $name . '[label]' ); ?>" value="<?php echo esc_attr( $item['label'] ); ?>" placeholder="<?php echo esc_attr( $default['label'] ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: field name. */ __( 'Label of %s', 'envo-blocks' ), $default['label'] ) ); ?>"></td>
									<td class="eb-cf__check-col">
										<?php if ( self::ADDRESS[ $key ] ) : ?>
											<input type="checkbox" name="<?php echo esc_attr( $name . '[visible]' ); ?>" value="1" <?php checked( $item['visible'] ); ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %s: field name. */ __( 'Show %s', 'envo-blocks' ), $default['label'] ) ); ?>">
										<?php else : ?>
											<input type="hidden" name="<?php echo esc_attr( $name . '[visible]' ); ?>" value="1"><span class="eb-cf__always" title="<?php esc_attr_e( 'Needed for shipping and taxes', 'envo-blocks' ); ?>">✓</span>
										<?php endif; ?>
									</td>
									<td class="eb-cf__check-col"><input type="checkbox" name="<?php echo esc_attr( $name . '[required]' ); ?>" value="1" <?php checked( $required ); ?> <?php disabled( 'country' === $key ); ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %s: field name. */ __( '%s is required', 'envo-blocks' ), $default['label'] ) ); ?>"><?php echo 'country' === $key ? '<input type="hidden" name="' . esc_attr( $name . '[required]' ) . '" value="1">' : ''; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</section>

				<section class="eb-cf__card">
					<h2><?php esc_html_e( 'Additional fields', 'envo-blocks' ); ?></h2>
					<p class="eb-cf__intro"><?php esc_html_e( 'Own fields of the checkout. The answers are saved with the order and shown in the order screen, in the order e-mails and in My account. Contact fields are shown under the e-mail address, address fields in both addresses, order fields under "Additional information".', 'envo-blocks' ); ?></p>
					<table class="eb-cf__table eb-cf__fields widefat">
						<thead><tr>
							<th class="eb-cf__handle-col"><span class="screen-reader-text"><?php esc_html_e( 'Order', 'envo-blocks' ); ?></span></th>
							<th><?php esc_html_e( 'Label', 'envo-blocks' ); ?></th>
							<th><?php esc_html_e( 'Type', 'envo-blocks' ); ?></th>
							<th><?php esc_html_e( 'Place', 'envo-blocks' ); ?></th>
							<th><?php esc_html_e( 'Options (select)', 'envo-blocks' ); ?></th>
							<th class="eb-cf__check-col"><?php esc_html_e( 'Required', 'envo-blocks' ); ?></th>
							<th class="eb-cf__check-col"><span class="screen-reader-text"><?php esc_html_e( 'Remove', 'envo-blocks' ); ?></span></th>
						</tr></thead>
						<tbody class="eb-cf__sortable eb-cf__rows">
							<?php
							foreach ( $settings['fields'] as $i => $field ) {
								self::field_row( (string) $i, $field, $types, $places );
							}
							?>
						</tbody>
					</table>
					<p class="eb-cf__empty" <?php echo $settings['fields'] ? 'hidden' : ''; ?>><?php esc_html_e( 'No additional fields yet.', 'envo-blocks' ); ?></p>
					<p><button type="button" class="button eb-cf__add" data-max="<?php echo esc_attr( (string) self::MAX ); ?>"><?php esc_html_e( 'Add field', 'envo-blocks' ); ?></button></p>
					<template class="eb-cf__template">
						<?php
						self::field_row(
							'__i__',
							array(
								'id'       => '',
								'label'    => '',
								'type'     => 'text',
								'location' => 'order',
								'required' => false,
								'options'  => array(),
							),
							$types,
							$places
						);
						?>
					</template>
				</section>

				<p class="eb-cf__buttons">
					<?php submit_button( __( 'Save changes', 'envo-blocks' ), 'primary', 'submit', false ); ?>
					<button type="submit" name="reset" value="1" class="button eb-cf__reset" data-confirm="<?php esc_attr_e( 'Go back to the checkout fields of WooCommerce? Your changes and additional fields are removed (answers already saved with orders stay).', 'envo-blocks' ); ?>"><?php esc_html_e( 'Reset to WooCommerce', 'envo-blocks' ); ?></button>
				</p>
			</form>
			<?php
			/**
			 * More cards of the Checkout Fields page (Envo Blocks: what PRO adds, or with PRO the checkout steps).
			 */
			do_action( 'envo_blocks_checkout_page_cards' );
			?>
		</div>
		<?php
	}

	/**
	 * One row of an additional field.
	 *
	 * @param string $i      Row index.
	 * @param array  $field  Field.
	 * @param array  $types  Types.
	 * @param array  $places Places.
	 */
	private static function field_row( $i, array $field, array $types, array $places ) {
		$name = 'eb_cf[fields][' . $i . ']';
		?>
		<tr>
			<td class="eb-cf__handle" title="<?php esc_attr_e( 'Drag to change the order', 'envo-blocks' ); ?>" aria-hidden="true">⋮⋮</td>
			<td>
				<input type="hidden" name="<?php echo esc_attr( $name . '[id]' ); ?>" value="<?php echo esc_attr( $field['id'] ); ?>">
				<input type="text" class="regular-text" name="<?php echo esc_attr( $name . '[label]' ); ?>" value="<?php echo esc_attr( $field['label'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. VAT number', 'envo-blocks' ); ?>" aria-label="<?php esc_attr_e( 'Label', 'envo-blocks' ); ?>">
			</td>
			<td>
				<select class="eb-cf__type" name="<?php echo esc_attr( $name . '[type]' ); ?>" aria-label="<?php esc_attr_e( 'Type', 'envo-blocks' ); ?>">
					<?php foreach ( $types as $value => $text ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $field['type'], $value ); ?>><?php echo esc_html( $text ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<select name="<?php echo esc_attr( $name . '[location]' ); ?>" aria-label="<?php esc_attr_e( 'Place', 'envo-blocks' ); ?>">
					<?php foreach ( $places as $value => $text ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $field['location'], $value ); ?>><?php echo esc_html( $text ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td><textarea class="eb-cf__options" name="<?php echo esc_attr( $name . '[options]' ); ?>" rows="2" placeholder="<?php esc_attr_e( 'One option per line', 'envo-blocks' ); ?>" aria-label="<?php esc_attr_e( 'Options, one per line', 'envo-blocks' ); ?>" <?php echo 'select' === $field['type'] ? '' : 'hidden'; ?>><?php echo esc_textarea( implode( "\n", $field['options'] ) ); ?></textarea></td>
			<td class="eb-cf__check-col"><input type="checkbox" name="<?php echo esc_attr( $name . '[required]' ); ?>" value="1" <?php checked( $field['required'] ); ?> aria-label="<?php esc_attr_e( 'Required', 'envo-blocks' ); ?>"></td>
			<td class="eb-cf__check-col"><button type="button" class="button-link button-link-delete eb-cf__remove"><?php esc_html_e( 'Remove', 'envo-blocks' ); ?></button></td>
		</tr>
		<?php
	}
}
