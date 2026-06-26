jQuery(document).ready(function ($) {

	$(".mws_create_eshop").click(function () {

		$('.mws_create_shop_container').addClass('mw_loading');

		$.post(
			ajaxurl,
			{
				'action': 'mwsCreateEshop',
				'mws_eshop_is_creating':'1',
			},
			function (content)
			{
				window.location.href = content;
			}
		);

		return false;

	});

	$('.mw_exchange_rate_field .mw_switch input').change(function () {
		if ($(this).prop("checked")) {
			$(this).closest('.mw_exchange_rate_field').addClass('mw_exchange_rate_field_fixed');
		} else {
			$(this).closest('.mw_exchange_rate_field').removeClass('mw_exchange_rate_field_fixed');
		}
	});

	$('.mws_add_currency_button').click(function(){
		var max = $(this).data('max');
		if(max <= $('.mw_setting_list_container tbody .mw_table_actions').length)
		{
			$(this).mwModal({
				text: $(this).data('info'),
			});

			return false;
		}
	});

	$('.mw_setting_page_edit.mw_setting_type_mworder').mwsOrderAdmin({});
	$('.mw_setting_type_eshop_dashboard').mwsDashboard({});
	$('.mw_setting_page_setting.mw_setting_type_mw_eshop_setting').mwsVatRatesAdmin({});

});

;(function (factory) {

	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports !== 'undefined') {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}

})(function ($) {

	var MwsStockSetting = (function (element, settings) {

		function _MwsStockSetting(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;
			this.variantStockUpdateCSS();

			this.$el.find('.mws_stock_setting_switch input').change(function () {

				var val;

				if ($(this).prop("checked")) {
					$('.mws_stock_setting_container').show();
					val = 1;
				} else {
					$('.mws_stock_setting_container').hide();
					val = 0;
				}

				// on change
				if (obj.settings.change) {
					obj.settings.change.call(obj, val);
				}

				obj.variantStockUpdateCSS();
			});

		}

		return _MwsStockSetting;

	})();

	MwsStockSetting.prototype.variantStockUpdateCSS = function() {

		var checkBox = this.$el.find('.mws_stock_setting_switch input');
		if (!checkBox.length) {
			return;
		}
		var isChecked = checkBox.prop('checked');
		var dest = $('.mws_variants_list');
		if (isChecked) {
			dest.removeClass('mws_stock_disabled');
			dest.addClass('mws_stock_enabled');
		} else {
			dest.addClass('mws_stock_disabled');
			dest.removeClass('mws_stock_enabled');
		}
	}

	$.fn.mwsStockSetting = function (options) {
		return this.each(function (index, el) {
			el.MwsStockSetting = new MwsStockSetting(el, options);
		});
	};

	var MwsProductTypeSelector = (function (element, settings) {

		function _MwsProductTypeSelector(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// hide variants for elecronical products
			this.$el.find('select').change(function () {
				var val = $(this).val();
				if(val == 'electronic-publication' || val == 'electronic' || val == 'membership')
				{
					if (!$('.mws_product_variant_setting').hasClass("mw_toggle_group_close"))
					{
						$('.mws_product_variant_setting .mw_toggle_group_head').click();
					}
				}

				// on change
				if (obj.settings.change) {
					obj.settings.change.call(obj, val);
				}

			});

		}

		return _MwsProductTypeSelector;

	})();

	$.fn.mwsProductTypeSelector = function (options) {
		return this.each(function (index, el) {
			el.MwsProductTypeSelector = new MwsProductTypeSelector(el, options);
		});
	};

	// Variants
	// *************************************************************************

	var MwsVariants = (function (element, settings) {

		function _MwsVariants(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);
			this.usedParams = this.getUsedParams();

			var obj = this;

			// multielement functions
			this.$el.mwMultiElementManager({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				},
				add: function (selector) {
					var item = obj.$el.find(selector);
					obj.changeItemHead(item);

					item.find('.mw_image_uploader').mwImageUplader({
						change: function () {
							if (obj.settings.change) {
								obj.settings.change.call();
							}
						}
					});

					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});


			this.$el.find('.mws_open_params_list').click(function () {
				obj.$el.find('.mws_variants_params').show();
				obj.$el.find('.mws_variants_container').hide();
				return false;
			});

			this.$el.find('.mws_close_params_list').click(function () {

				obj.$el.find('.mws_variants_params input').prop("checked", false);
				$.each( obj.usedParams, function( key, value ) {
				    obj.$el.find('.mws_variants_param_' + value).prop("checked", true);
				});

				obj.$el.find('.mws_variants_params').hide();
				obj.$el.find('.mws_variants_container').show();
				return false;
			});

			this.$el.find('.mws_save_params_list').click(function () {

				var paramsIds = obj.getUsedParams();
				if(obj.paramsChanged(paramsIds)) {

					var params = obj.getUsedParams('names');
					obj.$el.find('.mws_params_info_text strong').html(params.join(', '));

					obj.$el.find('.mws_variants_list').addClass('mw_loading_w');
					$.post(
						ajaxurl,
						{
							"action": "mwsChangeVariantParams",
							"params": paramsIds,
							"setting": obj.$el.find('.ve_add_multielement').attr('data-set'),
						},
						function (data) {
							obj.$el.find('.ve_item_container').remove();
							obj.$el.find('.ve_add_multielement').attr('data-set',data);
							obj.$el.find('.mws_variants_list').removeClass('mw_loading_w');
						}
					);

					obj.usedParams = paramsIds;

				}
				if(paramsIds.length) {
					obj.$el.addClass('mws_variants_parametres_seted');
				}
				else {
					obj.$el.removeClass('mws_variants_parametres_seted');
				}

				obj.$el.find('.mws_variants_params').hide();
				obj.$el.find('.mws_variants_container').show();

				return false;
			});

			this.$el.find('.mw_image_uploader').mwImageUplader({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			this.$el.on('change', '.mws_variant_parameter_input_container input, .mws_variant_parameter_input_container select', function(){
				var item = $(this).closest('.ve_item_container');
				obj.changeItemHead(item);
			});

			this.$el.on('change', '.mws_variant_subcol_stock input', function(){
				var item = $(this).closest('.ve_item_container');
				item.find('.mws_variant_item_head_count span').html(parseInt($(this).val()));
			});

			this.$el.on('change', '.mws_variant_subcol_price input, .mws_variant_subcol_sale input', function(){
				var item = $(this).closest('.ve_item_container');
				obj.changeItemHeadPrice(item);
			});



		}

		return _MwsVariants;

	})();

	MwsVariants.prototype.getUsedParams = function(ret = 'key') {

		var params = [];
		var i = 0;
		this.$el.find('.mws_variants_params input').each(function () {
			if ($(this).prop("checked") == true) {
				if(ret == 'key') {
					params[i] = $(this).val();
				}
				else {
					params[i] = $(this).closest('label').find('.mw_checkbox_label_text').html();
				}
				i++;
			}
		});
		return params;
	}

	MwsVariants.prototype.paramsChanged = function(newParams) {
		var obj = this;
		var ret = false;
		$.each( newParams, function( key, value ) {
		    var index = $.inArray( value, obj.usedParams );
		    if( index == -1 ) {
		        ret = true;
		    }
		});
		$.each( obj.usedParams, function( key, value ) {
		    var index = $.inArray( value, newParams );
		    if( index == -1 ) {
		        ret = true;
		    }
		});

		return ret;
	}

	MwsVariants.prototype.changeItemHead = function(item) {

		var obj = this;

		var headTexts = '';
		var text;

		item.find('.mws_variant_parameter_input_container').each(function () {
			text = '';
			if($(this).find('select').length) {
				text = $(this).find('option:selected').html();
			} else {
				text = $(this).find('input').val();
			}
			if(text)
				headTexts += '<span>' + text + '</span>';
			else
				headTexts += '<span> - </span>';
		});

		item.find('.mws_variant_item_head_name').html(headTexts);

	}

	MwsVariants.prototype.changeItemHeadPrice = function(item) {

		var obj = this;

		var price = 0;
		var sale = item.find('.mws_variant_subcol_sale input').val();
		if(sale) {
			price = sale;
		}
		else {
			price = item.find('.mws_variant_subcol_price input').val();
		}
		if(!price) {
			price = 0;
		}

		item.find('.mws_price_vatincluded .num').html(obj.numberFormat(price,2,',',' '));

	}

	MwsVariants.prototype.numberFormat = function(number, decimals, dec_point, thousands_sep) {
        number = parseFloat(number).toFixed(decimals);

        var nstr = number.toString();
        nstr += '';
        x = nstr.split('.');
        x1 = x[0];
        x2 = x.length > 1 && x[1]!='00' ? dec_point + x[1] : '';

        var rgx = /(\d+)(\d{3})/;

        while (rgx.test(x1))
            x1 = x1.replace(rgx, '$1' + thousands_sep + '$2');

		return x1 + x2;
    }

	$.fn.mwsVariants = function (options) {
		return this.each(function (index, el) {
			el.MwsVariants = new MwsVariants(el, options);
		});
	};

	// automations

	var MwsAutomations = (function (element, settings) {

		function _MwsAutomations(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// multielement functions
			this.$el.mwMultiElementManager({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				},
				add: function (selector) {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
					obj.$el.find(selector + ' .mw_api_connection_container').mwEmailMarketingSelector({
						change: function () {
							if (obj.settings.change) {
								obj.settings.change.call();
							}
						}
					});
					if(obj.$el.find(selector + ' .member_section_select_container').length)
					{
						obj.$el.find(selector + ' .member_section_select_container').mwMemberSelector({
							change: function () {
								if (obj.settings.change) {
									obj.settings.change.call();
								}
							}
						});
						obj.$el.find(selector + ' .mw_membership_creator_container').mwMembershipCreator({
							change: function () {
								if (obj.settings.change) {
									obj.settings.change.call();
								}
							}
						});
						obj.$el.find('.mw_datetime_field_container').mwDatePicker({
							change: function () {
								if (obj.settings.change) {
									obj.settings.change.call();
								}
							}
						});
					}

				}
			});

			// change event
			this.$el.on('change','.mws_automation_select_event',function(){
				$(this).closest('.ve_item_container').find('.mws_automation_item_head_event').html($(this).find('option:selected').html());
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			// change action
			this.$el.on('change','.mws_automation_select_action',function(){
				var id = $(this).find('option:selected').val();
				var container = $(this).closest('.ve_item_container');
				var action = $(this).find('option:selected').html();

				if(id == 'add_membership' || id == 'remove_membership')
				{
					action += ' <strong>' + container.find('.mw_member_section_select option:selected').html() + '</strong>';
				}
				container.find('.mws_automation_item_head_action').html(action);

				container.find('.mws_automation_action_setting').hide();
				container.find('.mws_aas_' + id).show();
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			// change action
			this.$el.on('change','.mw_member_section_select',function(){
				$(this).closest('.ve_item_container').find('.mws_automation_item_head_action strong').html($(this).find('option:selected').html());
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.find('.mw_api_connection_container').mwEmailMarketingSelector({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});
			if(this.$el.find('.member_section_select_container').length)
			{
				this.$el.find('.member_section_select_container').mwMemberSelector({
					change: function () {
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				});
				this.$el.find('.mw_membership_creator_container').mwMembershipCreator({
					change: function () {
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				});
				this.$el.find('.mw_datetime_field_container').mwDatePicker({
					change: function () {
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				});
			}
		}

		return _MwsAutomations;

	})();

	$.fn.mwsAutomations = function (options) {
		return this.each(function (index, el) {
			el.MwsAutomations = new MwsAutomations(el, options);
		});
	};

	var MwsSelectPaygate = (function (element, settings) {

		function _MwsSelectPaygate(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".mw_selectbox_item").mwConfirm({
				text: MioAdminjs.paygate_confirm,
				but_text: texts.continue,
				onconfirm: function (confirm) {

					if(confirm.$el.hasClass('mw_fapi_disconnected'))
					{
						obj.openConnect(confirm.$el);
					}
					else
					{
						obj.changeSelected(confirm.$el);
					}

					return false;
				}
			});

			this.$el.find(".mws_paygate_open_fapi_connect").click(function () {

				obj.openConnect($(this),false);

				return false;
			});

		}

		return _MwsSelectPaygate;

	})();

	MwsSelectPaygate.prototype.openConnect = function(item,change=true) {

		var obj = this;

		item.mwOpenApiConnectModal({
			onconnect: function (data) {
				if(change) obj.changeSelected(item);
				obj.$el.find('.mw_fapi_disconnected').removeClass('mw_fapi_disconnected');
				obj.$el.find('.mws_paygate_selectbox_fapi_info .mw_message_box_error').hide();
			},
		});

	}

	MwsSelectPaygate.prototype.changeSelected = function(item) {
		var obj = this;

		obj.$el.find(".mw_selectbox_item").removeClass('selected');
		item.addClass('selected');
		item.find('input').prop("checked", true);

		if(item.find('input').val() == 'fapi')
		{
			obj.$el.find('.mws_paygate_selectbox_fapi_info').show();
		}
		else
		{
			obj.$el.find('.mws_paygate_selectbox_fapi_info').hide();
		}

		// on change
		if (obj.settings.change) {
			obj.settings.change.call(obj, item.find('input').val());
		}

	}

	$.fn.mwsSelectPaygate = function (options) {
		return this.each(function (index, el) {
			el.MwsSelectPaygate = new MwsSelectPaygate(el, options);
		});
	};

	var MwsNumberSeries = (function (element, settings) {

		function _MwsNumberSeries(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);
			let pattern = /\D/g;
			var obj = this;

			// init

			this.correctLength(this.$el.find('.mws_num_series_prefix').val());
			this.createNumber();

			// prefix edit
			this.$el.on('keyup','.mws_num_series_prefix',function(){

				//$(this).val($(this).val().replace(/[^RMY\d]/g,''));

				obj.correctLength($(this).val());

				obj.createNumber();

				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});
			this.$el.on('change','.mws_num_series_prefix',function(){

				var old_val = $(this).val();
				let specificPattern = new RegExp('(YYYY|YY|MM|RR|\\d+)','g');
				let error = false;

				var new_val = old_val.replace(/([^R]|^)R([^R]|$)/g, "$1$2").replace(/([^R]|^)RRR([^R]|$)/g, "$1RR$2").replace(/RRRR+/g, "RRRR")
				.replace(/([^M]|^)M([^M]|$)/g, "$1$2").replace(/MM+/g, "MM")
				.replace(/([^Y]|^)Y([^Y]|$)/g, "$1$2").replace(/([^Y]|^)YYY([^Y]|$)/g, "$1YY$2").replace(/YYYY+/g, "YYYY");


				// return array of match if no match return null
				new_val = new_val.match(specificPattern);

				if(new_val !== null) {
					if(new_val.includes('MM')){
						// remove multiple MM
						new_val =  [...new Set(new_val)];
					}

					new_val = new_val.toString().replace(/,/g,'');

					if( new_val.length < old_val.length) {
					    error = true;
					}
				}

				//if no match trow an error message
				// !specificPattern.test(old_val) this means no match at all
				if (!specificPattern.test(old_val) || error ) {
					$( this ).addClass('cms_required_alert');
					let errorClass = $( this ).attr('class').split(' ')[2];
					$('.mw_onedit_action').find('[class*="' + errorClass + '"]').removeClass('cms_nodisp');
				} else {
				//remove all error messages
					$('.mws_num_series_prefix').removeClass('cms_required_alert');
					$('.mws_num_prefix_error_message').addClass('cms_nodisp');
				}
				if(old_val!=new_val)
				{
					$(this).val(new_val);
					obj.correctLength($(this).val());
					obj.createNumber();

					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			// number of characters edit
			this.$el.on('change','.mws_num_series_characters',function(){

				obj.correctStart(parseInt(obj.$el.find('.mws_num_series_characters option:selected').val()));

				obj.createNumber();

				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			// start number edit
			this.$el.on('keyup','.mws_num_series_start',function(){

				$(this).val($(this).val().replace(/\D/g,''));

				obj.correctStart(parseInt(obj.$el.find('.mws_num_series_characters option:selected').val()));

				obj.createNumber();

				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

		}

		return _MwsNumberSeries;

	})();

	MwsNumberSeries.prototype.correctLength = function(prefix) {
		var obj = this;

		var select = this.$el.find('.mws_num_series_characters option:selected');
		var allowLength = 10-prefix.length;

		this.$el.find('.mws_num_series_characters option').each(function () {
			if($(this).val()>allowLength)
			{
				$(this).prop('disabled', true).hide();
			}
			else
			{
				$(this).prop('disabled', false).show();
			}
		});

		if(this.$el.find('.mws_num_series_characters option:selected').val()>allowLength)
		{
			this.$el.find('.mws_num_series_characters').val(allowLength).change();
			obj.correctStart(allowLength);
		}
	}

	MwsNumberSeries.prototype.correctStart = function(length) {
		var obj = this;
		var number = this.$el.find('.mws_num_series_start').val();
		this.$el.find(".mws_num_series_start").attr('maxlength',length);
		if(number.length > length)
		{
			this.$el.find('.mws_num_series_start').val(number.substr(0, length));
		}
	}

	MwsNumberSeries.prototype.createNumber = function() {
		var prefix = this.$el.find('.mws_num_series_prefix').val();
		var length = this.$el.find('.mws_num_series_characters option:selected').val();
		var start = this.$el.find('.mws_num_series_start').val();
		let pattern = /\D/g;
		var number = prefix+('000000000' + start).slice(-length);

		number = number.replace(/RRRR|YYYY/g,new Date().getFullYear());
		number = number.replace(/RR|YY/g,new Date().getFullYear().toString().substr(-2));
		number = number.replace(/MM/g,("0" + (new Date().getMonth() + 1).toString()).substr(-2));
		number = number.replace(/[RMY]/g,'');
		//check if there is NoN
		if(pattern.test(number)) {
			number = number.replace(pattern,'');
		}
		this.$el.find('.mws_num_series_format strong').html(number);
	}



	$.fn.mwsNumberSeries = function (options) {
		return this.each(function (index, el) {
			el.MwsNumberSeries = new MwsNumberSeries(el, options);
		});
	};

	var MwsOrderAdmin = (function (element, settings) {

		function _MwsOrderAdmin(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// edit customer
			this.$el.find('.mws_order_content').on('click',".mws_edit_order_customer",function () {

				$(this).mwModal({
					onopen: function (modal) {

						var postid = modal.$el.attr('data-id');

						$.post(
							ajaxurl,
							{
								"action": "mwsOpenOrderClientForm",
								"postid": postid,
							},
							function (data) {
								modal.addContent(data);
							}
						);

					},
					onsave: function (modal) {

						var id = modal.$el.attr('data-id');
					  	var formdata = modal.getFormData();

						$.post(ajaxurl, 'action=mwsSaveOrderClientForm&postid=' + id + '&' + formdata, function (data) {

							$('.mws_order_customer_info').html(data);
							modal.close();

						});

						return false;
					}

				});

				return false;
			});

			// dropdown button
			this.$el.find(".mws_order_status .mw_dropdown_button, .mws_order_payed_status .mw_dropdown_button").mwDropdownButton({});

			// add or change order note
			this.$el.find('.mws_order_note_container').on('click', '.mws_order_add_note, .mws_order_change_note', function () {

				const noteText = $('#mws_order_note_text');
				const note = noteText.html().replaceAll('<br>', "\n");
				$(this).hide();
				noteText.hide();
				$('#mws_order_note_input').val(note).show();
				$('.mws_order_note_container').find('.mws_order_note_save_cancel_link').show();
			});

			//save order note or cancel the edit
			this.$el.find('.mws_order_note_container').on('click', '.mws_order_save_note, .mws_order_cancel_note', function () {

				const noteContainer = $('.mws_order_note_container');

				if($(this).hasClass('mws_order_save_note')) {
					const postid = $(this).attr('data-id');
					const noteInput = $('#mws_order_note_input');
					const noteText = $('#mws_order_note_text');
					const note = noteInput.val();

					if(!$(this).hasClass('mw_link_loading')) {
						$(this).addClass('mw_link_loading');

						$.post(
							ajaxurl,
							{
								"action": "mwsSaveOrderNote",
								"postid": postid,
								"value": note,
							},
							function () {
								noteContainer.find('.mws_order_note_save_cancel_link').hide();
								noteInput.hide();
								noteText.html(note.replaceAll("\n", '<br>'));
								if (note) {
									noteText.show();
									noteContainer.find('.mws_order_change_note').show();
								} else {
									noteContainer.find('.mws_order_add_note').show();
								}
								$('.mws_order_save_note').removeClass('mw_link_loading');
							}
						);
					}
				} else {
					const noteText = $('#mws_order_note_text');
					noteContainer.find('.mws_order_note_save_cancel_link').hide();
					$('#mws_order_note_input').hide();

					if (noteText.html()) {
						noteText.show();
						noteContainer.find('.mws_order_change_note').show();
					} else {
						noteContainer.find('.mws_order_add_note').show();
					}
				}
			});

			// change order status
			this.$el.find('.mws_order_status_container').on('click',".mws_order_change_status",function () {

				var postid = $(this).attr('data-id');
				var setStatus = $(this).attr('data-set');

				if(setStatus == 10)
				{
					$(this).mwModal({
						class: 'mws_tohandle_order_modal',
						but_text: texts.tohandle_order,
						onopen: function (modal) {

							$.post(
								ajaxurl,
								{
									"action": "mwsChangeOrderStatusForm",
									"postid": postid,
									"setStatus": setStatus,
								},
								function (data) {
									modal.addContent(data);
								}
							);
						},
						onsave: function (modal) {

							form = modal.getFormData();

							obj.saveOrderStatus(setStatus,postid,form);

							modal.close();

							return false;
						}

					});
				}
				else
				{
					obj.saveOrderStatus(setStatus,postid);
				}

				return false;
			});

			// set payed
			this.$el.find('.mws_order_payed_status_container').on('click',".mws_order_change_payed_status",function () {

				var postid = $(this).attr('data-id');
				var setStatus = $(this).attr('data-set');

				if(setStatus == 1)
				{
					$(this).mwModal({
						class: 'mws_tohandle_paid_modal',
						onopen: function (modal) {
							$.post(
								ajaxurl,
								{
									"action": "mwsChangePaidStatusForm",
									"postid": postid,
									"setStatus": setStatus,
								},
								function (data) {
									modal.addContent(data);
									modal.$modal.find('.mws_create_invoice_switch .mw_switch input').change(function(){

											modal.$modal.find('.mws_send_invoice_switch').toggle();


									});
								}
							);
						},
						onsave: function (modal) {

							form = modal.getFormData();

							obj.savePaidStatus(setStatus,postid,form);

							modal.close();

							return false;
						}

					});
				}
				else
				{
					obj.savePaidStatus(setStatus,postid);
				}

				return false;
			});

			// create invoice
			this.$el.find('.mw_setting_invoice_sidebar_container').on('click',".mws_order_create_invoice",function () {

				var link = $(this);

				if(!link.hasClass('mw_link_loading'))
				{
					link.addClass('mw_link_loading');

					$.post(
						ajaxurl,
						{
							"action": "mwsOrderCreateInvoice",
							"orderId": $(this).data('id'),
						},
						function (data) {
							obj.$el.find('.mw_setting_invoice_sidebar_container').html(data.invoice_list_html);
							link.remove();
						}
					);
				}
				return false;
			});

			// send invoice
			this.$el.find('.mw_setting_invoice_sidebar_container').on('click', ".mws_order_send_invoice", function () {

				var link = $(this);

				if(!link.hasClass('mw_link_loading'))
				{
					link.addClass('mw_link_loading');

					$.post(
						ajaxurl,
						{
							"action": "mwsOrderSendInvoice",
							"orderId": $(this).data('id'),
						},
						function (data) {
							obj.$el.find('.mw_setting_invoice_sidebar_container').html(data.invoice_list_html);
						}
					);
				}
				return false;
			});

			// mPOHODA
			this.$el.find('.mw_setting_invoice_sidebar_container').on('click', ".mws_document_issue_mpohoda", function () {

				var link = $(this);

				if(!link.hasClass('mw_link_loading'))
				{
					link.addClass('mw_link_loading');

					$.post(
						ajaxurl,
						{
							"action": "mwsDocumentIssueMPohoda",
							"documentId": $(this).data('id'),
						},
						function (data) {
							// link.removeClass('mw_link_loading');

							obj.$el.find('.mws_document_mpohoda_container').replaceWith(data.mpohoda_html);

							if (!data.success) {
								obj.$el.find('.mw_setting_message_box').mwAddMessage({
									content: data.html,
									close: true,
									//autoHide: 10000,
								})
							}
						}
					);
				}
				return false;
			});

		}

		return _MwsOrderAdmin;

	})();

	MwsOrderAdmin.prototype.saveOrderStatus = function(setStatus, postid, formdata = '') {
		var obj = this;

		obj.$el.find('.mws_order_status').addClass('mw_status_field_loading');

		$.post(ajaxurl, 'action=mwsOrderChangeStatus&postid=' + postid + '&setStatus=' + setStatus + '&' + formdata, function (data) {
			obj.$el.find('.mws_order_status_container').html(data);
			obj.$el.find(".mws_order_status .mw_dropdown_button").mwDropdownButton({});
			if(setStatus == 10)
			{
				$('.mws_order_closed_info').removeClass('cms_nodisp');
			}
			else
			{
				$('.mws_order_closed_info').addClass('cms_nodisp');
			}
			if(setStatus == 20)
			{
				obj.$el.find('.mw_setting_object_detail_content').addClass('mws_order_cancelled');
			}
			else
			{
				obj.$el.find('.mw_setting_object_detail_content').removeClass('mws_order_cancelled');
			}
		});

	}

	MwsOrderAdmin.prototype.savePaidStatus = function(setStatus, postid, formdata = '') {
		var obj = this;

		obj.$el.find('.mws_order_payed_status').addClass('mw_status_field_loading');

		$.post(ajaxurl, 'action=mwsOrderChangePaidStatus&postid=' + postid + '&setStatus=' + setStatus + '&' + formdata, function (data) {
			obj.$el.find('.mws_order_payed_status_container').html(data.status_html);
			obj.$el.find('.mw_setting_paidon_date_container').html(data.paid_date);
			obj.$el.find('.mw_setting_invoice_sidebar_container').html(data.invoice_list_html);
			obj.$el.find(".mws_order_payed_status .mw_dropdown_button").mwDropdownButton({});
			if(setStatus == 1)
			{
				$('.mws_order_paidon_info').removeClass('cms_nodisp');
			}
			else
			{
				$('.mws_order_paidon_info').addClass('cms_nodisp');
			}
		});
	}

	$.fn.mwsOrderAdmin = function (options) {
		return this.each(function (index, el) {
			el.MwsOrderAdmin = new MwsOrderAdmin(el, options);
		});
	};

	var MwsVatRatesAdmin = (function (element, settings) {

		function _MwsVatRatesAdmin(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// change order status
			this.$el.on('click', ".mws_edit_vat_rates", function () {
				$(this).mwModal({
					class: 'mws_vat_rates_modal',
					but_text: texts.save,
					onopen: function (modal) {
						$.post(
							ajaxurl,
							{
								"action": "mwsGetVatRatesForm",
							},
							function (data) {
								modal.addContent(data);
							}
						);
					},
					onsave: function (modal) {
						var form = modal.getFormData();
						obj.save(form);
						modal.close();
						return false;
					}

				});

				return false;
			});

		}

		return _MwsVatRatesAdmin;

	})();

	MwsVatRatesAdmin.prototype.save = function(formdata = '') {
		var obj = this;

		$.post(ajaxurl, {
			action: 'mwsSaveVatRatesForm',
			form: formdata
		}, function(content) {
			var success = content || false;

			if(!success) {
				// TODO show some error message
				console.error('Error while saving vat rates.');
			}

			return false;
		});
	}

	$.fn.mwsVatRatesAdmin = function (options) {
		return this.each(function (index, el) {
			el.MwsVatRatesAdmin = new MwsVatRatesAdmin(el, options);
		});
	};

	var MwsDashboard = (function (element, settings) {

		function _MwsDashboard(element, settings) {

			this.$el = $(element);
			this.container = this.$el.find('.mw_dashboard_statistics');
			var obj = this;

			// filter
			this.$el.find(".mw_range_select_container").mwRangeFilter({
				change: function (period, from, to) {

					obj.container.addClass('mw_loading');
					var source = obj.$el.find('.mw_source_select').val();

					obj.reloadStatisticsCall(period, from, to, source);

				}

			});

			// filter
			this.$el.find(".mw_source_select").change(function() {

				obj.reloadStatistics();

			});

			// reload statistics on delete from list on dashboard
			this.$el.find(".mw_dashboard_list_container").mwSettingList({
				ondelete: function () {
					console.log('ondelete');
					obj.reloadStatistics();
				},
			});

		}

		return _MwsDashboard;

	})();

	MwsDashboard.prototype.reloadStatistics = function(period, from, to, source) {
		obj = this;
		obj.container.addClass('mw_loading');

		var period = obj.$el.find(".mw_range_select_container select").val();
		var from = obj.$el.find( ".mw_range_select_container .mw_range_select_from" ).val();
		var to = obj.$el.find( ".mw_range_select_container .mw_range_select_to" ).val();
		var source = obj.$el.find('.mw_source_select').val();

		obj.reloadStatisticsCall(period, from, to, source);
	}

	MwsDashboard.prototype.reloadStatisticsCall = function(period, from, to, source) {
		obj = this;

		$.ajax({
			type: 'POST',
			data: {"action": "mwsReloadDashboardStatistics", "period": period, "from": from, "to": to, "source": source},
			url: ajaxurl,
			success: function (content) {
				obj.$el.find('.mw_dashboard_statistics').html(content);
				obj.container.removeClass('mw_loading');
			}
		});
	}

	$.fn.mwsDashboard = function (options) {
		return this.each(function (index, el) {
			el.MwsDashboard = new MwsDashboard(el, options);
		});
	};

	var MwsProductTags = (function (element, settings) {

		function _MwsProductTags(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.mw_input_whisperer').mwInputWhisperer({
				'addModalArgs': {
					action: "mwsFastAddProductTag",
					onsave: function (data) {
						obj.$el.find(".mws_product_tags_list").append(data.tag);
						obj.$el.find(".mw_input_whisperer_input").val('').keyup();
						obj.$el.find(".mw_input_whisperer_list li.mw_input_whisperer_add_item").before(data.whisperer_item);
					},
					afterOpen: function (modal) {
						var text = obj.$el.find(".mw_input_whisperer_input").val();
						modal.$modal.find('form input:first').val(text);
					},
				}
			});

			this.$el.on('click', ".mw_text_tag .mw_icon", function () {
				var id = $(this).data('itemid');
				$(this).closest('.mw_text_tag').remove();
				obj.$el.find('.mw_input_whisperer_item_'+id).removeClass('whisperer_item_used');
			});

			// on change
			if (obj.settings.change) {
				obj.settings.change.call(obj);
			}

		}

		return _MwsProductTags;

	})();

	$.fn.mwsProductTags = function (options) {
		return this.each(function (index, el) {
			el.MwsProductTags = new MwsProductTags(el, options);
		});
	};

	var MwsUpsellsField = (function (element, settings) {

		function _MwsUpsellsField(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// multielement functions
			this.$el.mwMultiElementManager({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				},
				add: function (selector) {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			// add new upsell
			this.$el.find('.mws_upsell_field_add').click(function () {

				var id = $(this).attr('data-id');
				var settings = $(this).attr('data-set');
				var elemContainer = obj.$el.find('.ve_multielement_container');

				$(this).attr('data-id', parseInt(id) + 1);



				$(this).mwOpenInstallator({
					install: 'upsell',
					objectid: 'mwupsell',
					action: 'mws_add_upsell_item',
					ajax_url_data: '&settings=' + settings + '&id=' + id,
					oninstall: function (lightboxInstallator, data) {
						//elemContainer.append('<div class="ve_multielement_loading ve_item_container mw_loading"></div>');
						elemContainer.append(data);
						var container = obj.$el.find('.added');
						container.find('.ve_item_body').slideDown();
						container.addClass('open').removeClass('added');

						if (obj.settings.change) {
							obj.settings.change.call();
						}

						lightboxInstallator.close();
					},

				});

				return false;
			});

			// custom price
			this.$el.on('change','.mws_upsell_custom_price_switch input', function () {

				var container = $(this).closest('.ve_item_container');

				if ($(this).prop("checked")) {
					container.find('.mws_upsell_price_container').removeClass('cms_nodisp');
					obj.changeHeadPrice($(this));
				} else {
					container.find('.mws_upsell_price_container').addClass('cms_nodisp');
					obj.changeHeadPrice($(this), false);
				}

				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}

			});

			// change price
			this.$el.on('change','.mws_upsell_price_input input, .mws_upsell_price_sale_input input',function(){
				obj.changeHeadPrice($(this));

				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

		}

		return _MwsUpsellsField;

	})();

	MwsUpsellsField.prototype.changeHeadPrice = function(el, custom = true) {
		obj = this;

		var container = el.closest('.ve_item_container');
		var full_price = parseFloat(container.find('.mws_upsell_price_input input').data('original'));
		var sale_price = parseFloat(container.find('.mws_upsell_price_sale_input input').data('original'));
		var end_price;
		var isDiscounted = false;

		if(custom) {
			var custom_price_full = parseFloat(container.find('.mws_upsell_price_input input').val());
			var custom_price_sale = parseFloat(container.find('.mws_upsell_price_sale_input input').val())

			if(!isNaN(custom_price_full)) {
				full_price = custom_price_full;
				sale_price = custom_price_sale;
			}
		}

		if(sale_price > 0 && sale_price < full_price) {
			end_price = sale_price;
			isDiscounted = true;
		} else {
			end_price = full_price;
		}

		container.find('.ve_item_head_title .mws_upsells_field_price_end span').html(end_price);

		if(isDiscounted) {
			container.find('.ve_item_head_title .mws_upsells_field_price_full').show();
			container.find('.ve_item_head_title .mws_upsells_field_price_full span').html(full_price);
		} else {
			container.find('.ve_item_head_title .mws_upsells_field_price_full').hide();
		}
	}

	$.fn.mwsUpsellsField = function (options) {
		return this.each(function (index, el) {
			el.MwsUpsellsField = new MwsUpsellsField(el, options);
		});
	};

});
