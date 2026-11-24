/**
 * AJAX Add to Cart – simple & variable products.
 * Ensures variation attributes are sent correctly and cart events fire.
 */
(function ($) {
	'use strict';

	$.fn.serializeArrayAll = function () {
		var rCRLF = /\r?\n/g;
		return this.map(function () {
			return this.elements ? jQuery.makeArray(this.elements) : this;
		}).map(function (i, elem) {
			var val = jQuery(this).val();
			if (val == null) {
				return null;
			}
			if (this.type === 'checkbox' && this.checked === false) {
				return { name: this.name, value: '' };
			}
			return jQuery.isArray(val)
				? jQuery.map(val, function (val) {
					return { name: elem.name, value: val.replace(rCRLF, '\r\n') };
				})
				: { name: elem.name, value: String(val).replace(rCRLF, '\r\n') };
		}).get();
	};

	/**
	 * Collect selected variation attributes from the form.
	 */
	function getVariationData($form) {
		var variation = {};
		$form.find('select[name^="attribute_"], input[name^="attribute_"]').each(function () {
			var name = $(this).attr('name');
			var val = $(this).val();
			if (name && val) {
				variation[name] = val;
			}
		});
		return variation;
	}

	$(document).on('click', '.single_add_to_cart_button:not(.disabled)', function (e) {
		var $thisbutton = $(this);
		var $form = $thisbutton.closest('form.cart');

		// Only intercept if form exists (single product / quick view).
		if (!$form.length) {
			return;
		}

		e.preventDefault();

		var product_id = $form.find('input[name="product_id"]').val() || $thisbutton.val();
		var variation_id = $form.find('input[name="variation_id"]').val() || 0;
		var quantity = $form.find('input[name="quantity"]').val() || 1;
		var is_variable = $form.find('input[name="variation_id"]').length > 0;

		// Variable product: require a selected variation.
		if (is_variable) {
			variation_id = parseInt(variation_id, 10) || 0;
			if (!variation_id) {
				// Let WooCommerce show its own "please select options" notice.
				$form.find('.single_add_to_cart_button').removeClass('loading');
				return false;
			}

			var variation = getVariationData($form);
			var data_send = {
				action: 'wcspc_add_variation_to_cart',
				product_id: product_id,
				variation_id: variation_id,
				quantity: quantity,
				variation: variation,
				security: (typeof wcspcVars !== 'undefined') ? wcspcVars.nonce : ''
			};

			$thisbutton.removeClass('added').addClass('loading');

			$.post(wcspcVars.ajaxurl, data_send)
				.done(function (response) {
					$thisbutton.removeClass('loading').addClass('added');

					// wp_send_json_error format
					if (response && response.success === false) {
						var url = (response.data && response.data.product_url) ? response.data.product_url : null;
						if (url) {
							window.location = url;
						}
						return;
					}

					// Legacy / WC fragments style
					if (response && response.error && response.product_url) {
						window.location = response.product_url;
						return;
					}

					var fragments = response.fragments || (response.data && response.data.fragments);
					var cart_hash = response.cart_hash || (response.data && response.data.cart_hash);

					$(document.body).trigger('added_to_cart', [fragments, cart_hash, $thisbutton]);
				})
				.fail(function () {
					$thisbutton.removeClass('loading');
				});

			return false;
		}

		// Simple / grouped / other: use core WC AJAX endpoint.
		var data = $form.find('input:not([name="product_id"]), select, button, textarea').serializeArrayAll() || [];
		$.each(data, function (i, item) {
			if (item.name === 'add-to-cart') {
				item.name = 'product_id';
				item.value = product_id;
			}
		});

		$(document.body).trigger('adding_to_cart', [$thisbutton, data]);

		$.ajax({
			type: 'POST',
			url: (typeof wc_add_to_cart_params !== 'undefined')
				? wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart')
				: woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
			data: data,
			beforeSend: function () {
				$thisbutton.removeClass('added').addClass('loading');
			},
			complete: function () {
				$thisbutton.addClass('added').removeClass('loading');
			},
			success: function (response) {
				if (response && response.error && response.product_url) {
					window.location = response.product_url;
					return;
				}
				$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
			}
		});

		return false;
	});
})(jQuery);
