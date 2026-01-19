(function ($) {
	// Tab navigation.
	$('.nav-tab').on('click', function (event) {
		event.preventDefault();
		$('.nav-tab-active').removeClass('nav-tab-active');
		$('.cludo__option_page_tab').hide();
		$(this).addClass('nav-tab-active');
		$($(this).attr('href')).show();
	});
	$('.nav-tab:first').trigger('click');

	// Show events if they are activated.
	$('#event_activate').on('change', function () {
		if (!this.checked) {
			$('table.event_purchase').hide();
			$('table.event_purchase_product').hide();
		} else {
			$('table.event_purchase').show();
			$('table.event_purchase_product').show();
		}
	}).change();

	// Save settings message.
	var inputData = [];
	$('.cludo__option_page').find('textarea,input,select').each(function () {
		inputData[$(this).attr('name')] = $(this).val();
	}).on('change', function () {
		if ($(this).val() !== inputData[$(this).attr('name')]) {
			$('#cludo__option_page_notice').css({'visibility': 'visible'});
		}
	});

})(jQuery);