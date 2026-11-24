=== Modal Fly Cart & AJAX Add to Cart for WooCommerce ===
Contributors: athemeart
Tags: woocommerce, mini cart, ajax cart, fly cart, modal cart, side cart, popup cart
Requires at least: 5.8
Tested up to: 7.2
Requires PHP: 7.4
Stable tag: 2.0.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Fast WooCommerce AJAX mini-cart modal with side drawer, floating button, & instant updates. Free edition; Pro adds full customization.

== Description ==
**Modal Fly Cart & AJAX Add to Cart for WooCommerce** is a powerful, lightweight mini cart plugin that enhances your store's user experience with customizable modal styles and dynamic effects. Allow customers to add, update, or remove products instantly without page reloads, reducing cart abandonment and boosting conversions.

With instantaneous updates, clear "View Cart" and "Checkout" buttons, and flexible navigation options, buyers can seamlessly manage their items and proceed straight to payment. Fully customizable to match your brand style, WooCommerce Popup Cart + Ajax bridges the gap between product selection and instant checkout.


= Free features =

* Fly cart positions: right, left, top, bottom, or center (modal)
* AJAX add to cart, quantity change, and remove item
* Compatible with **variable products** (select attributes, then add)
* Floating cart button with icon and position options
* Auto-open after AJAX or classic add to cart
* Optional CSS selector to open the cart from your theme header
* Basic panel colors (background, primary text, accent)
* Show or hide total, Cart / Checkout buttons, continue shopping
* Hide floating button when cart is empty or on cart/checkout pages
* Reload cart on page load (helpful with full-page cache)
* Secure nonces, sanitization, and WooCommerce cart APIs
* High-Performance Order Storage (HPOS) compatible
* Translation-ready (text domain: `atawc_lang`)

= Pro features (Upgrade to Pro) =

Marked with a **Pro** badge in the settings screens:

* Advanced panel design (item background, text, price colors)
* Quantity control colors (+ / − and input styling)
* Cart & Checkout button colors, hover, and border radius
* Floating button background, icon, and badge colors
* Panel background image
* Free shipping progress bar
* Custom empty-cart message
* Extra product-options display compatibility

Pro product page: [WooCommerce Modal Fly Cart PRO](https://athemeart.com/downloads/woocommerce-popup-cart-ajax/)

= Requirements =

* WordPress 5.8 or higher
* PHP 7.4 or higher
* WooCommerce 5.0 or higher (active)

If the Pro plugin is active, the free plugin does not load its frontend so the two do not conflict.

== Installation ==

1. Upload the `woocomm-popup-cart-ajax` folder to `/wp-content/plugins/`, or install the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate **Modal Fly Cart & AJAX Add to Cart for WooCommerce**.
3. Ensure **WooCommerce** is installed and activated.
4. Go to **WooCommerce → Modal / Ajax Cart** to configure settings.
5. Optional: on the Plugins screen, use **Settings** or **Upgrade to Pro** next to the plugin name.

== Frequently Asked Questions ==

= Does this plugin work with variable products? =

Yes. On the single product page, select the variation attributes, then click Add to cart. The free edition sends the variation via AJAX and updates the mini-cart. Variation attributes appear under the product title in the cart when available.

= Where are the settings? =

Go to **WooCommerce → Modal / Ajax Cart**, or click **Settings** on the Plugins list for this plugin.

= Why don’t my settings save? =

Use **Save Settings** at the bottom of the settings form. Options are stored in the WordPress options table (`ata_wcspc_free_options`). If you still see old behavior, clear any full-page or object cache and try again with “Reload cart on page load” enabled under Advanced.

= How do I open the cart from my theme header? =

Under **General → Manual trigger selector**, enter a CSS class or ID used by your header cart link, for example `.mini-cart-btn` or `#header-cart`. Clicking that element will open the fly cart.

= Can I change colors? =

Yes. Under **Design** you can set panel background, primary text, and accent colors. More detailed styling (line items, quantity controls, button hover states, floating button colors, background image) is available in **Pro**.

= What is limited in the free version? =

Core cart behavior is fully available. Sections labeled with a **Pro** badge (advanced design, quantity styles, free shipping bar, custom empty message, extra button colors) require [Upgrade to Pro](https://athemeart.com/downloads/woocommerce-popup-cart-ajax/).

= Does it work with caching plugins? =

Yes. If the item count looks outdated after add-to-cart, enable **Reload cart on page load** under Advanced so the cart is refreshed via AJAX when the page loads.

= Is it compatible with HPOS / custom order tables? =

Yes. The plugin declares compatibility with WooCommerce High-Performance Order Storage.

= Can I use free and Pro at the same time? =

Install only one. If Pro is active, the free plugin skips loading its cart so you do not get two mini-carts.

= How do I translate the plugin? =

The text domain is `atawc_lang`. Language files live in the `/languages` folder. You can use Loco Translate, WPML, or standard .po/.mo workflows.

= Where do I get support or Pro? =

* Free plugin: use the WordPress.org support forum for this plugin when published, or contact aThemeArt.
* Pro features and priority support: [https://athemeart.com/downloads/woocommerce-popup-cart-ajax/](https://athemeart.com/downloads/woocommerce-popup-cart-ajax/)

== Screenshots ==

1. Fly cart panel on the storefront
2. Floating cart button
3. Admin settings – General tab
4. Admin settings – Design (free + Pro badges)

== Changelog ==

= 2.0.1 =
* Upgrade to Pro link beside Settings on Plugins list
* Upgrade to Pro button on settings header (right side)
* Clear Pro badges on locked feature sections
* Expanded README with full FAQ section

= 2.0.0 =
* Modern tabbed admin UI with field descriptions
* Fixed settings persistence (options now save correctly)
* Variable product AJAX add to cart
* Security: nonces, capability checks, sanitization, escaping
* Proper AJAX JSON success/error responses
* Variation attributes shown automatically in the mini-cart
* Pro badges and Upgrade to Pro links (plugins list + settings header)
* HPOS compatibility
* Free/Pro coexistence (free does not load when Pro is active)

= 1.5.7 =
* Previous free release line

== Upgrade Notice ==

= 2.0.0 =
Recommended update: fixes settings save, improves variable product support, and hardens security. Configure under WooCommerce → Modal / Ajax Cart.
