(function ($) {
  'use strict';

  $(document).on('click', '.pyr-select-qr', function (event) {
    event.preventDefault();

    var field = $(this).closest('.pyr-qr-admin-field');
    var input = field.find('input[type="hidden"]');
    var preview = field.find('.pyr-qr-admin-preview');
    var frame = wp.media({
      title: reycobPypYapeSettings.mediaTitle,
      button: {
        text: reycobPypYapeSettings.mediaButton
      },
      multiple: false,
      library: {
        type: 'image'
      }
    });

    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      input.val(attachment.url);
      preview.empty().append($('<img>', {
        src: attachment.url,
        alt: reycobPypYapeSettings.imageAlt
      }));
    });

    frame.open();
  });

  $(document).on('click', '.pyr-remove-qr', function (event) {
    event.preventDefault();

    var field = $(this).closest('.pyr-qr-admin-field');
    field.find('input[type="hidden"]').val('');
    field.find('.pyr-qr-admin-preview').empty().append($('<span>', {
      text: reycobPypYapeSettings.emptyText
    }));
  });
})(jQuery);
