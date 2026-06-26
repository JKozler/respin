jQuery(function ($) {
	$('.mws_packeta_create_packet').click(function () {

		var order_id = $(this).attr('data-order');

		$(this).mwModal({
			class: 'mws_tohandle_order_packeta',
			but_text: texts.tohandle_packeta,
			onopen: function (modal) {
				modal.addContent($('.mws_create_packeta_form_container').html());

				// Size
				modal.$modal.find('[name="fill_order_size"]').on('change', function () {
					const inputs = modal.$modal.find('.packeta_size_field');

					if (this.checked) {
						inputs.css('display', 'table-row');
					} else {
						inputs.css('display', 'none');
					}
				});
			},
			onsave: function (modal) {

				const weight = modal.$modal.find('.mws_packeta_weight input').val();
				const fill_order_size = modal.$modal.find('.mws_packeta_fill_order_size').is(':checked') ? 1 : 0;
				const length = modal.$modal.find('.mws_packeta_size_length input').val();
				const width = modal.$modal.find('.mws_packeta_size_width input').val();
				const height = modal.$modal.find('.mws_packeta_size_height input').val();
				const note = modal.$modal.find('.mws_packeta_note').val();

				$.post(ajaxurl, {
					"action": "mwsp_create_packet",
					'order_id': order_id,
					'weight': weight,
					'fill_order_size': fill_order_size,
					'size_length': length,
					'size_width': width,
					'size_height': height,
					'note': note
				}, function (data) {
					if(data.success)
					{
						$('.mws_packeta_info_container').html(data.content);
						modal.close();
					}
					else
					{
						modal.addMessage(data);
						modal.removeLoading();
					}
				});
			}

		});

		return false;

	});

	$('.mws_packeta_info_container').on('click','.mws_packeta_prepare_print',function () {
		var url = $(this).attr('data-url');

		$(this).mwModal({
			class: 'mws_tohandle_packeta_print',
			but_text: texts.tohandle_packeta_print,

			onopen: function (modal) {
				modal.addContent($('.mws_print_packeta_form_container').html());
			},
			onsave: function (modal) {
				var skip_value =  modal.$modal.find('.mws_print_packeta_form_position').val();
				var expandedUrl = url+'&skip_value='+ skip_value;
				window.open( expandedUrl, '_blank' );
				modal.close();
			}
		});

		return false;

	});
});


