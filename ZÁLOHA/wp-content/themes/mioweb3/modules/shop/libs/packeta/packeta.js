jQuery(function ($) {
	// eshop
	$('.mws_shop_order_content .mws_open_packeta_select').click(function () {
		var $form = $(this).closest('form');

		Packeta.Widget.pick(mws_packeta_login.api_key, function(point) {

			if (point) {
				var address = point.name;
				if (point.zip) address = address + ", " + point.zip;
				if (point.city) address = address + " " + point.city;

				$form.find('.mws_shipping_packeta_info_container').show();
				$form.find('.mws_shipping_packeta_info_address').html(address);
				$form.find('.mws_shipping_packeta_address').val(address);
				$form.find('.mws_shipping_packeta_id').val(point.id).trigger('change'); // Trigger one reload

			}

		}, {})
	});

});
