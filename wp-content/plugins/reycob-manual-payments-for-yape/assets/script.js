(function ($) {
  'use strict';

  function setStatus(area, message, isError) {
    area.find('.pyr-upload-status').text(message || '').toggleClass('is-error', !!isError);
  }

  function resetCapture(area) {
    area.find('#pyr_capture_url').val('');
    area.find('.pyr-preview').attr('hidden', true).find('img').removeAttr('src');
    area.removeClass('is-uploaded');
    setStatus(area, '');
  }

  $(document).on('click', '.pyr-copy-btn', function () {
    var button = $(this);
    var text = button.attr('data-copy');
    var status = button.closest('.pyr-payment-info').find('.pyr-copy-status');

    function done(success) {
      status.toggleClass('is-error', !success)
        .text(success ? reycobPypYape.copied : reycobPypYape.copyError);
    }

    function fallback() {
      var temp = $('<textarea readonly>').val(text).css({
        position: 'fixed', top: 0, left: 0, opacity: 0
      }).appendTo('body').select();
      var copied = false;
      try {
        copied = document.execCommand('copy');
      } catch (error) {
        copied = false;
      }
      temp.remove();
      button.trigger('focus');
      done(copied);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function () { done(true); }, fallback);
    } else {
      fallback();
    }
  });

  $(document).on('change', '#pyr_capture_file', function () {
    var input = this;
    var area = $(input).closest('.pyr-upload-area');
    var file = input.files && input.files[0];
    var previous = area.data('pyrUpload');
    if (previous) {
      previous.abort();
    }
    resetCapture(area);
    if (!file) {
      return;
    }
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      input.value = '';
      setStatus(area, reycobPypYape.invalidType, true);
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      input.value = '';
      setStatus(area, reycobPypYape.tooLarge, true);
      return;
    }

    var formData = new FormData();
    formData.append('action', 'reycob_pyp_upload_capture');
    formData.append('nonce', reycobPypYape.nonce);
    formData.append('file', file);

    area.attr('aria-busy', 'true');
    setStatus(area, reycobPypYape.uploading);

    function failed() {
      input.value = '';
      setStatus(area, reycobPypYape.error, true);
    }

    area.data('pyrUpload', $.ajax({
      url: reycobPypYape.ajaxUrl,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (!response || !response.success || !response.data || !response.data.url) {
          failed();
          return;
        }

        area.find('#pyr_capture_url').val(response.data.url);
        area.find('.pyr-preview img').attr('src', response.data.url);
        area.find('.pyr-preview').removeAttr('hidden');
        area.addClass('is-uploaded');
        setStatus(area, reycobPypYape.uploaded);
      },
      error: function (request, status) {
        if (status !== 'abort') {
          failed();
        }
      },
      complete: function () {
        area.removeAttr('aria-busy').removeData('pyrUpload');
      }
    }));
  });

  $(document).on('click', '.pyr-remove-file', function () {
    var area = $(this).closest('.pyr-upload-area');
    area.find('#pyr_capture_file').val('').trigger('focus');
    resetCapture(area);
  });

  $(document.body).on('checkout_place_order_pago_yape_reycob', function () {
    var area = $('.pyr-payment-box .pyr-upload-area').first();
    if (area.length && !area.find('#pyr_capture_url').val()) {
      setStatus(area, reycobPypYape.required, true);
      return false;
    }
    return true;
  });
})(jQuery);
