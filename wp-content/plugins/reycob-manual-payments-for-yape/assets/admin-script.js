(function ($) {
  'use strict';

  $(document).on('click', '.pyr-status-action', function () {
    var button = $(this);
    var row = button.closest('tr');
    var paymentId = row.data('payment-id');
    var status = button.data('status');
    var message = status === 'approved'
      ? reycobPypYapeAdmin.approveConfirm
      : reycobPypYapeAdmin.rejectConfirm;

    if (!window.confirm(message)) {
      return;
    }

    row.find('button').prop('disabled', true);

    $.ajax({
      url: reycobPypYapeAdmin.ajaxUrl,
      method: 'POST',
      data: {
        action: 'reycob_pyp_update_payment_status',
        nonce: reycobPypYapeAdmin.nonce,
        payment_id: paymentId,
        status: status
      },
      success: function (response) {
        if (!response || !response.success) {
          row.find('button').prop('disabled', false);
          window.alert(reycobPypYapeAdmin.updateError);
          return;
        }

        row.find('.pyr-status')
          .removeClass('pyr-status-pending pyr-status-approved pyr-status-rejected')
          .addClass('pyr-status-' + response.data.status)
          .text(response.data.label);

        row.find('td:last').empty().append($('<span>', {
          'class': 'pyr-muted',
          text: reycobPypYapeAdmin.noActions
        }));
      },
      error: function () {
        row.find('button').prop('disabled', false);
        window.alert(reycobPypYapeAdmin.connectionError);
      }
    });
  });
})(jQuery);
