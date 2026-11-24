(function ($) {
	'use strict';

	$(function () {
		if (typeof wcspcVars !== 'undefined' && wcspcVars.reload === 'yes') {
			wcspc_get_cart();
		}

		wcspc_perfect_scrollbar();

		// Qty minus
		$('body').on('click', '#wcspc-area .wcspc-item-qty-minus', function () {
			var $input = $(this).parent().find('.wcspc-item-qty-input');
			var qtyMin = parseFloat($input.attr('min')) || 1;
			var step = parseFloat($input.attr('step')) || 1;
			var qty = parseFloat($input.val()) || qtyMin;
			if (qty - step >= qtyMin) {
				$input.val(qty - step).trigger('change');
			}
		});

		// Qty plus
		$('body').on('click', '#wcspc-area .wcspc-item-qty-plus', function () {
			var $input = $(this).parent().find('.wcspc-item-qty-input');
			var qtyMax = parseFloat($input.attr('max')) || 9999;
			var step = parseFloat($input.attr('step')) || 1;
			var qty = parseFloat($input.val()) || 1;
			if (qty + step <= qtyMax) {
				$input.val(qty + step).trigger('change');
			}
		});

		// Qty change
		$('body').on('change', '#wcspc-area .wcspc-item-qty-input', function () {
			var item_key = $(this).attr('data-key');
			var item_qty = $(this).val();
			wcspc_update_qty(item_key, item_qty);
		});

		// Qty focus validate
		var qtyInterval = false;
		$('body').on('focus', '#wcspc-area .wcspc-item-qty-input', function () {
			var $thisQty = $(this);
			var min = parseFloat($thisQty.attr('min')) || 1;
			var max = parseFloat($thisQty.attr('max')) || 9999;
			qtyInterval = setInterval(function () {
				var v = parseFloat($thisQty.val());
				if (isNaN(v) || v < min) {
					$thisQty.val(min);
				}
				if (v > max) {
					$thisQty.val(max);
				}
			}, 50);
		});
		$('body').on('blur', '#wcspc-area .wcspc-item-qty-input', function () {
			if (qtyInterval) {
				clearInterval(qtyInterval);
				qtyInterval = false;
			}
			var item_key = $(this).attr('data-key');
			var item_qty = $(this).val();
			wcspc_update_qty(item_key, item_qty);
		});

		// Remove item
		$('body').on('click', '#wcspc-area .wcspc-item-remove', function () {
			var $item = $(this).closest('.wcspc-item');
			$item.addClass('wcspc-item-removing');
			var item_key = $(this).attr('data-key');
			wcspc_remove_item(item_key);
			$item.slideUp();
		});

		$('body').on('click tap', '.wcspc-overlay, .wcspc-close, #wcspc-continue', function () {
			wcspc_hide_cart();
		});

		$('body').on('click', '#wcspc-count, .single_add_to_cart_flyer', function () {
			wcspc_show_cart();
		});

		// Auto show after add to cart
		if (typeof wcspcVars !== 'undefined' && wcspcVars.auto_show === 'yes') {
			$('body').on('added_to_cart', function () {
				wcspc_get_cart();
				wcspc_show_cart();
			});
		} else {
			$('body').on('added_to_cart', function () {
				wcspc_get_cart();
			});
		}

		// Manual show
		if (typeof wcspcVars !== 'undefined' && wcspcVars.manual_show) {
			$('body').on('click', wcspcVars.manual_show, function (e) {
				e.preventDefault();
				wcspc_show_cart();
			});
		}
	});
})(jQuery);

function wcspc_handle_response(response, onSuccess) {
	if (response && response.success && response.data) {
		if (typeof onSuccess === 'function') {
			onSuccess(response.data);
		}
		return;
	}
	var msg = (wcspcVars && wcspcVars.i18n && wcspcVars.i18n.error)
		? wcspcVars.i18n.error
		: 'Something went wrong. Please try again.';
	if (response && response.data && response.data.message) {
		msg = response.data.message;
	}
	console.warn('ATA WCSPC:', msg);
}

function wcspc_update_qty(cart_item_key, cart_item_qty) {
	jQuery('#wcspc-count').addClass('wcspc-count-loading').removeClass('wcspc-count-shake');
	jQuery.post(
		wcspcVars.ajaxurl,
		{
			action: 'wcspc_update_qty',
			cart_item_key: cart_item_key,
			cart_item_qty: cart_item_qty,
			security: wcspcVars.nonce
		},
		function (response) {
			wcspc_handle_response(response, function (data) {
				if (data.html) {
					jQuery('#wcspc-area').html(data.html);
					wcspc_perfect_scrollbar();
				}
				if (data.subtotal) {
					jQuery('#wcspc-subtotal').html(data.subtotal);
				}
				jQuery('#wcspc-count-number').html(data.count);
				jQuery('.fly_counter_load').html(data.count);
				jQuery('#wcspc-count').addClass('wcspc-count-shake').removeClass('wcspc-count-loading');
				if (wcspcVars.hide_count_empty === 'yes' && parseInt(data.count, 10) === 0) {
					jQuery('#wcspc-count').addClass('wcspc-count-hide');
				} else {
					jQuery('#wcspc-count').removeClass('wcspc-count-hide');
				}
			});
		}
	).fail(function () {
		jQuery('#wcspc-count').removeClass('wcspc-count-loading');
	});
}

function wcspc_remove_item(cart_item_key) {
	jQuery('#wcspc-count').addClass('wcspc-count-loading').removeClass('wcspc-count-shake');
	jQuery.post(
		wcspcVars.ajaxurl,
		{
			action: 'wcspc_remove_item',
			cart_item_key: cart_item_key,
			security: wcspcVars.nonce
		},
		function (response) {
			wcspc_handle_response(response, function (data) {
				if (data.html) {
					jQuery('#wcspc-area').html(data.html);
					wcspc_perfect_scrollbar();
				}
				if (data.subtotal) {
					jQuery('#wcspc-subtotal').html(data.subtotal);
				}
				jQuery('#wcspc-count-number').html(data.count);
				jQuery('.fly_counter_load').html(data.count);
				jQuery('#wcspc-count').addClass('wcspc-count-shake').removeClass('wcspc-count-loading');
				if (wcspcVars.hide_count_empty === 'yes' && parseInt(data.count, 10) === 0) {
					jQuery('#wcspc-count').addClass('wcspc-count-hide');
				} else {
					jQuery('#wcspc-count').removeClass('wcspc-count-hide');
				}
			});
		}
	).fail(function () {
		jQuery('#wcspc-count').removeClass('wcspc-count-loading');
	});
}

function wcspc_get_cart() {
	jQuery('#wcspc-area').addClass('wcspc-area-loading');
	jQuery('#wcspc-count').addClass('wcspc-count-loading').removeClass('wcspc-count-shake');
	jQuery.post(
		wcspcVars.ajaxurl,
		{
			action: 'wcspc_get_cart',
			security: wcspcVars.nonce
		},
		function (response) {
			wcspc_handle_response(response, function (data) {
				jQuery('#wcspc-area').html(data.html);
				jQuery('.fly_counter_load').html(data.count);
				wcspc_perfect_scrollbar();
				jQuery('#wcspc-count-number').html(data.count);
				jQuery('#wcspc-area').removeClass('wcspc-area-loading');
				jQuery('#wcspc-count').addClass('wcspc-count-shake').removeClass('wcspc-count-loading');
				var hideEmpty = wcspcVars.hide_count_empty === 'yes' && parseInt(data.count, 10) === 0;
				var hideCheckout = wcspcVars.hide_count_checkout === 'yes' &&
					(jQuery('body').hasClass('woocommerce-cart') || jQuery('body').hasClass('woocommerce-checkout'));
				if (hideEmpty || hideCheckout) {
					jQuery('#wcspc-count').addClass('wcspc-count-hide');
				} else {
					jQuery('#wcspc-count').removeClass('wcspc-count-hide');
				}
			});
		}
	).fail(function () {
		jQuery('#wcspc-area').removeClass('wcspc-area-loading');
		jQuery('#wcspc-count').removeClass('wcspc-count-loading');
	});
}

function wcspc_perfect_scrollbar() {
	if (jQuery.fn.perfectScrollbar) {
		jQuery('#wcspc-area .wcspc-area-top').perfectScrollbar({ suppressScrollX: true, theme: 'wcspc' });
	}
}

function wcspc_show_cart() {
	jQuery('body').addClass('wcspc-body-show');
	jQuery('#wcspc-area').addClass('wcspc-area-show');
}

function wcspc_hide_cart() {
	jQuery('#wcspc-area').removeClass('wcspc-area-show');
	jQuery('body').removeClass('wcspc-body-show');
}
