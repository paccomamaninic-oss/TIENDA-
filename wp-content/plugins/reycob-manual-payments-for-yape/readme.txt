=== Reycob Pagos Manuales para Yape en Perú ===
Contributors: reycob38
Tags: yape, pagos yape, peru, qr payments, woocommerce
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 5.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept manual mobile wallet payments with a QR image, receipt upload, and an order review dashboard.

== Description ==

Reycob Pagos Manuales para Yape en Perú adds a manual payment method for stores that receive mobile wallet payments in Peru.

Store administrators can configure the account phone number, account holder, QR image, receipt requirement, and the order status used after approval.

During checkout, customers see the QR image, exact order amount, phone number, and an optional receipt upload field. New payments remain on hold until a store manager reviews the receipt from WooCommerce > Yape Payments and approves or rejects it.

The plugin stores payment records and receipt URLs on the same WordPress installation. It does not send store, customer, order, or payment data to an external service.

This plugin requires WooCommerce. It is not affiliated with, endorsed by, or sponsored by WooCommerce, Yape, or their trademark owners.

== Installation ==

1. Install and activate WooCommerce.
2. Upload and activate Reycob Pagos Manuales para Yape en Perú.
3. Open WooCommerce > Settings > Payments.
4. Enable the Yape payment method.
5. Configure the phone number, account holder, QR image, receipt requirement, and approval status.
6. Review submitted payments from WooCommerce > Yape Payments.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes. WooCommerce must be active to register the payment gateway and manage orders.

= Does the plugin confirm payments automatically? =

No. A store manager reviews each receipt and approves or rejects the payment manually.

= Where are receipts stored? =

Accepted JPG, PNG, and WebP files are stored in the WordPress uploads directory under `comprobantes-yape`.

= Does the plugin connect to an external service? =

No. This free edition has no license server, telemetry, tracking, or external API connection.

= Does it support the Checkout block? =

Not currently. Use the classic WooCommerce checkout shortcode or a compatible checkout builder.

== Screenshots ==
1. WooCommerce payment receipt review dashboard.
2. Optional complementary plugins panel on the plugin admin screen.
3. Native WooCommerce gateway settings for manual Yape payments.


== Changelog ==

= 5.0.6 =
* Redesigned the classic checkout payment panel with a larger, expandable QR code, clearer amount and copy controls, and more visible receipt upload states.

= 5.0.5 =
* Add optional contextual links to complementary Reycob plugins on plugin-owned admin screens.
* Add real usage screenshots from a local demonstration store.


= 5.0.4 =
* Renamed the plugin to Reycob Pagos Manuales para Yape en Perú to clarify that it is an independent third-party integration.
* Aligned the text domain with the requested reycob-manual-payments-for-yape slug.
* Replaced the third-party logo with a neutral manual-payment icon.
* Kept Tested up to exclusively in this readme file.

= 5.0.3 =
* Translated the complete customer and store-management interface into Spanish.

= 5.0.2 =
* Aligned all translation calls with the plugin text domain.
* Improved public plugin metadata.

= 5.0.1 =
* Prepared the public-facing metadata and documentation for directory review.
* Removed the previous brand name from customer-facing and administrative messages.

= 5.0.0 =
* Released as a standalone GPL plugin without license activation or external service calls.
* Updated plugin metadata and documentation for WordPress.org.
* Improved validation and security for receipt uploads and payment review actions.

= 4.1.0 =
* Added QR selection from the WordPress media library.
* Added receipt thumbnails and manual payment review actions.
* Added a dedicated payment review dashboard.

= 4.0.0 =
* Added the manual QR payment gateway and receipt upload workflow.
