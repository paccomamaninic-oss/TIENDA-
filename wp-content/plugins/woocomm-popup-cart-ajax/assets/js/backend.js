(function ($) {
	'use strict';
	$(function () {
		$('.ata-wcspc-color-picker, .wcspc_color_picker').wpColorPicker();
		$('.ata-wcspc-tab').on('click', function (e) {
			e.preventDefault();
			var tab = $(this).data('tab');
			$('.ata-wcspc-tab').removeClass('active');
			$(this).addClass('active');
			$('.ata-wcspc-panel').removeClass('active');
			$('#ata-panel-' + tab).addClass('active');
		});
		function updateIconPreview() {
			var icon = $('#_wcspc_count_icon').val() || '';
			$('#wcspc_count_icon_view').attr('class', 'ata-wcspc-icon-preview ' + icon);
		}
		$('#_wcspc_count_icon').on('change', updateIconPreview);
		updateIconPreview();
	});
})(jQuery);
