<?php
/**
 * What Envo Blocks PRO adds to a free settings page, shown on that page.
 *
 * Some free features have a PRO part: Customer Photos (review request and reward e-mails), Lowest Price (price
 * alerts, price history chart), Catalog Mode (Request a quote) and Checkout Fields (checkout in steps, PRO checkout
 * designs). Without the PRO plugin these pages get a locked card with the PRO settings (disabled, to see what is
 * possible) and a link to Envo Blocks › Get PRO; with PRO the PRO plugin draws its own cards through the same hooks.
 * The checkout steps live in the PRO block settings, so with PRO the Checkout Fields page links there.
 *
 * @package EnvoBlocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Locked PRO cards on free settings pages.
 */
final class Envo_Blocks_Pro_Settings_Teasers {

	/**
	 * Hooks.
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'envo_blocks_reviews_page_cards', array( __CLASS__, 'reviews' ) );
		add_action( 'envo_blocks_lowest_price_page', array( __CLASS__, 'lowest_price' ) );
		add_action( 'envo_blocks_catalog_settings_rows', array( __CLASS__, 'catalog_row' ) );
		add_action( 'envo_blocks_catalog_page_cards', array( __CLASS__, 'catalog' ) );
		add_action( 'envo_blocks_checkout_page_cards', array( __CLASS__, 'checkout' ) );
	}

	/**
	 * Whether the PRO plugin is missing (with PRO, also without a license, it draws its own cards).
	 *
	 * @return bool
	 */
	private static function without_pro() {
		return Envo_Blocks_License::STATE_NONE === Envo_Blocks_License::state();
	}

	/**
	 * Envo Blocks › Get PRO.
	 *
	 * @return string
	 */
	private static function pro_url() {
		return admin_url( 'admin.php?page=' . Envo_Blocks_Admin::SLUG . '-pro' );
	}

	/**
	 * A locked card: title with the PRO badge, what it does, the PRO settings (disabled) and the way to PRO.
	 *
	 * @param string $title  Title.
	 * @param string $intro  What PRO adds.
	 * @param array  $points Short points (plain text).
	 * @param string $fields Disabled settings (form-table rows), already escaped.
	 */
	private static function card( $title, $intro, array $points, $fields = '' ) {
		?>
		<section class="eb-cf__card eb-pro-teaser">
			<h2><?php echo esc_html( $title ); ?> <span class="eb-pro-teaser__badge">PRO</span></h2>
			<p class="eb-cf__intro"><?php echo esc_html( $intro ); ?></p>
			<?php if ( $points ) : ?>
				<ul class="eb-pro-teaser__points">
					<?php foreach ( $points as $point ) : ?>
						<li><?php echo esc_html( $point ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( '' !== $fields ) : ?>
				<fieldset class="eb-pro-teaser__fields" disabled>
					<table class="form-table" role="presentation"><?php echo $fields; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped while built. ?></table>
				</fieldset>
			<?php endif; ?>
			<p class="eb-pro-teaser__foot">
				<span class="dashicons dashicons-lock" aria-hidden="true"></span>
				<?php esc_html_e( 'Part of Envo Blocks PRO.', 'envo-blocks' ); ?>
				<a class="button button-primary" href="<?php echo esc_url( self::pro_url() ); ?>"><?php esc_html_e( 'Get Envo Blocks PRO', 'envo-blocks' ); ?></a>
			</p>
		</section>
		<?php
	}

	/**
	 * One disabled settings row.
	 *
	 * @param string $label Label.
	 * @param string $field Field HTML, already escaped.
	 * @param string $note  Description.
	 * @return string
	 */
	private static function row( $label, $field, $note = '' ) {
		return '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . $field . ( '' !== $note ? '<p class="description">' . esc_html( $note ) . '</p>' : '' ) . '</td></tr>';
	}

	/**
	 * A disabled number field with a unit.
	 *
	 * @param int|float $value Value.
	 * @param string    $unit  Unit after the field.
	 * @return string
	 */
	private static function number( $value, $unit ) {
		return '<input type="number" class="small-text" value="' . esc_attr( (string) $value ) . '" disabled> ' . esc_html( $unit );
	}

	/**
	 * A disabled check box.
	 *
	 * @param string $label   Label.
	 * @param bool   $checked Checked.
	 * @return string
	 */
	private static function check( $label, $checked = false ) {
		return '<label><input type="checkbox"' . ( $checked ? ' checked' : '' ) . ' disabled> ' . esc_html( $label ) . '</label>';
	}

	/**
	 * Envo Blocks › Settings › Customer Photos: review requests and rewards.
	 */
	public static function reviews() {
		if ( ! self::without_pro() ) {
			return;
		}
		self::card(
			__( 'Review requests and rewards', 'envo-blocks' ),
			__( 'Get more reviews – and more photos – without asking by hand.', 'envo-blocks' ),
			array(
				__( 'Some days after an order is completed the customer gets an e-mail with the bought products and "Write a review" buttons.', 'envo-blocks' ),
				__( 'When you approve a review of a bought product, the customer gets a one-time coupon as a thank-you – if you like, only for reviews with a photo.', 'envo-blocks' ),
				__( 'Both are WooCommerce e-mails in the design of your store, with their own texts.', 'envo-blocks' ),
			),
			self::row( __( 'Review request', 'envo-blocks' ), self::check( __( 'Send', 'envo-blocks' ), true ) . ' &nbsp; ' . self::number( 7, __( 'days after the order is completed', 'envo-blocks' ) ) )
			. self::row( __( 'Review reward', 'envo-blocks' ), self::number( 10, __( '% coupon', 'envo-blocks' ) ) . '<br>' . self::check( __( 'Only reviews with a photo', 'envo-blocks' ), true ) )
		);
	}

	/**
	 * Envo Blocks › Settings › Lowest Price: price alerts and the price history chart.
	 */
	public static function lowest_price() {
		if ( ! self::without_pro() ) {
			return;
		}
		self::card(
			__( 'Price alerts and price history chart', 'envo-blocks' ),
			__( 'The price history kept for the lowest price can bring shoppers back.', 'envo-blocks' ),
			array(
				__( 'Price Alert block: visitors leave their e-mail on a product and get one e-mail when its price drops.', 'envo-blocks' ),
				__( 'Price History Chart block: how the price of a product changed, from the same history.', 'envo-blocks' ),
				__( 'An overview of waiting alerts and the most watched products on this page.', 'envo-blocks' ),
			),
			self::row( __( 'Minimum price drop', 'envo-blocks' ), self::number( 1, '%' ), __( 'An alert goes out only when the price falls at least this much below the price at the sign-up.', 'envo-blocks' ) )
		);
	}

	/**
	 * Envo Blocks › Settings › Catalog Mode, inside the settings: the quote page (locked).
	 */
	public static function catalog_row() {
		if ( ! self::without_pro() ) {
			return;
		}
		?>
		<tr class="eb-pro-teaser__row">
			<th scope="row"><?php esc_html_e( 'Quote page', 'envo-blocks' ); ?> <span class="eb-pro-teaser__badge">PRO</span></th>
			<td>
				<select disabled><option><?php esc_html_e( '— Choose a page —', 'envo-blocks' ); ?></option></select>
				<p class="description">
					<span class="dashicons dashicons-lock" aria-hidden="true"></span>
					<?php esc_html_e( 'With Envo Blocks PRO the button can be "Request a quote": customers collect products in a quote list and send it from this page.', 'envo-blocks' ); ?>
					<a href="<?php echo esc_url( self::pro_url() ); ?>"><?php esc_html_e( 'Get Envo Blocks PRO', 'envo-blocks' ); ?></a>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Envo Blocks › Settings › Catalog Mode: quote requests.
	 */
	public static function catalog() {
		if ( ! self::without_pro() ) {
			return;
		}
		self::card(
			__( 'Request a quote and wholesale prices', 'envo-blocks' ),
			__( 'Sell to businesses and on demand: let customers ask for a price instead of buying.', 'envo-blocks' ),
			array(
				__( 'A "Request a quote" button instead of Add to cart on catalog products, and a quote list.', 'envo-blocks' ),
				__( 'Each request is an order with the status "Quote requested": set the prices and send the customer a link to pay.', 'envo-blocks' ),
				__( 'E-mails for you and the customer, in WooCommerce › Settings › Emails.', 'envo-blocks' ),
				__( 'Wholesale prices: own prices for approved business customers, a discount by category and a minimum order.', 'envo-blocks' ),
			)
		);
	}

	/**
	 * Envo Blocks › Settings › Checkout Fields: checkout in steps and the PRO checkout designs.
	 */
	public static function checkout() {
		if ( ! self::without_pro() ) {
			self::checkout_link();
			return;
		}
		self::card(
			__( 'Checkout in steps and checkout designs', 'envo-blocks' ),
			__( 'Make a long checkout feel short.', 'envo-blocks' ),
			array(
				__( 'Checkout in steps: contact, shipping and payment one after another, with a progress bar.', 'envo-blocks' ),
				__( 'More checkout designs in Store Layouts – distraction-free, split, with trust badges or products to add – with your checkout fields inside.', 'envo-blocks' ),
			),
			self::row( __( 'Checkout in steps', 'envo-blocks' ), self::check( __( 'Show the checkout in steps', 'envo-blocks' ) ) )
		);
	}

	/**
	 * With PRO: where the checkout steps are set.
	 */
	private static function checkout_link() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<section class="eb-cf__card">
			<h2><?php esc_html_e( 'Checkout in steps', 'envo-blocks' ); ?> <span class="eb-pro-teaser__badge">PRO</span></h2>
			<p class="eb-cf__intro"><?php esc_html_e( 'Contact, shipping and payment one after another, with a progress bar. Turned on in the block settings of Envo Blocks PRO; the fields set here stay in the steps.', 'envo-blocks' ); ?></p>
			<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Envo_Blocks_Admin::SETTINGS . '#eb-ebp_checkout_steps_section' ) ); ?>"><?php esc_html_e( 'Checkout steps settings', 'envo-blocks' ); ?></a></p>
		</section>
		<?php
	}
}
