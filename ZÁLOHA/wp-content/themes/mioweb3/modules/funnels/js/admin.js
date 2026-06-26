jQuery(function ($) {

	// installator
	$('body').on('click', '.mw_open_funnel_installator', function () {

		$(this).mwOpenInstallator({
			install: 'funnel',
			lightbox_id: 'mwFunnelInstallatorLightbox',
			action: 'mwInstallNewFunnel',
			oninstall: function (lightboxInstallator, data) {

				//lightboxInstallator.close();
				window.location = data;

			}
		});

		return false;
	});

	$('.mw_setting_page_funnels_dashboard').mwFunnelsSetting({});
	$('.mw_funnel_detail').mwFunnelManager({});

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

	var MwFunnelsSetting = (function (element, settings) {

		function _MwFunnelsSetting(element, settings) {

			this.$el = $(element);
			this.container = this.$el.find('.mw_dashboard_statistics');

			var obj = this;

			// filter
			this.$el.find(".mw_range_select_container").mwRangeFilter({
				change: function (period, from, to) {
					obj.container.addClass('mw_loading');
					obj.reloadStatisticsCall(period, from, to);
				}
			});

			// reload statistics on delete from list on dashboard
			this.$el.find(".mw_dashboard_list_container").mwSettingList({
				ondelete: function () {
					obj.reloadStatistics();
				},
			});

		}

		return _MwFunnelsSetting;

	})();

	MwFunnelsSetting.prototype.reloadStatistics = function(period, from, to, source) {
		obj = this;
		obj.container.addClass('mw_loading');

		var period = obj.$el.find(".mw_range_select_container select").val();
		var from = obj.$el.find( ".mw_range_select_container .mw_range_select_from" ).val();
		var to = obj.$el.find( ".mw_range_select_container .mw_range_select_to" ).val();

		obj.reloadStatisticsCall(period, from, to);
	}

	MwFunnelsSetting.prototype.reloadStatisticsCall = function(period, from, to) {
		obj = this;
		$.ajax({
			type: 'POST',
			data: {"action": "mwReloadDashboardStatistics", "period": period, "from": from, "to": to},
			url: ajaxurl,
			success: function (content) {
				obj.container.html(content);
				obj.container.removeClass('mw_loading');
			}
		});
	}

	$.fn.mwFunnelsSetting = function (options) {
		return this.each(function (index, el) {
			el.MwFunnelsSetting = new MwFunnelsSetting(el, options);
		});
	};

	// mw funnel manager
	var MwFunnelManager = (function (element, settings) {

		function _MwFunnelManager(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			// funnel id
			this.id = mw_funnel_id;
			// id of editing item
			this.editing = false;
			this.setting_edited = false;
			this.funnel_edited = false;
			// settings of items
			this.items_setting = [];
			this.items_setting = mw_funnel_items_setting;
			this.$loading = this.$el.find(".mwcb_loading");
			// edit panel
			this.$editpanel = this.$el.find(".mwcb_edit_panel");

			var obj = this;

			this.$el.find('.mw_funnel_tooltip').tooltip({
				track: true
		    });

			// tabs
			this.$el.find(".mw_modal_head ul a").click(function(e) {
				var target = $(this).attr('href');
				obj.$el.find(".mw_modal_head ul a").removeClass('active');
				obj.$el.find(".mw_funnel_tab").removeClass('active');
				$(this).addClass('active');
				obj.$el.find(target).addClass('active');
				obj.$el.find('.mw_messages_container').html('');
				e.preventDefault();
			});

			// setting
			/*
			this.$el.find('.mw_funnel_setting .mw_onedit_action').mwOnEditAction({
				change: function (field_setting) {
					obj.setting_edited = true;
					obj.setEdited();
				},
			});*/

			// filters
			this.$el.find(".mwcb_filter .mw_range_select_container").mwRangeFilter({
				change: function (period, from, to) {
					obj.reload();
				}
			});
			this.$el.find(".mw_statistic_filter_icon").click(function(e) {
				obj.$el.find('.mw_statistic_filter_inputs').toggle();
				e.preventDefault();
			});
			this.$el.find('.mw_statistic_filter_close').click(function (e) {
				obj.$el.find('.mw_statistic_filter_inputs').hide();
			});
			this.$el.find(".mw_statistic_filter_apply").click(function(e) {
				obj.reload();
				obj.$el.find('.mw_statistic_filter_inputs').hide();
				e.preventDefault();
			});
			this.$el.on("click", ".mw_statistic_filter_reset", function (e) {
				obj.$el.find('.mw_statistic_filter_inputs input').val('');
				obj.$el.find('.mw_statistic_filter_inputs select').val('');
				obj.$el.find('.mw_statistic_filter_input').show();
				obj.reload();
				obj.$el.find('.mw_statistic_filter_inputs').hide();
				e.preventDefault();
			});
			this.$el.find(".mw_statistic_filter_input_source select").change(function(e) {
				if($(this).val()=='')
				{
					obj.$el.find('.mw_statistic_filter_input').show();
				}
				else
				{
					obj.$el.find('.mw_statistic_filter_input').hide();
					obj.$el.find('.mw_statistic_filter_input_source').show();
				}
			});
			this.$el.find(".mw_statistic_filter_close").change(function(e) {
				obj.$el.find('.mw_statistic_filter_inputs').hide();
				e.preventDefault();
			});

			// enter to edit mode
			this.$el.find(".mwcb_edit_funnel").click(function(e) {
				obj.openFunnelEditing();
				e.preventDefault();
			});
			// sorting
			this.addSorting();

			// save new path
			this.$el.find(".mwcb_funnel_footer .mwcb_save_funnel").click(function(e) {
				obj.saveFunnelItems();
				e.preventDefault();
			});
			// leave edit model
			this.$el.find(".mwcb_funnel_footer .mw_setting_storno_but").click(function(e) {
				var el = $(this);
				if (obj.funnel_edited) {
					el.mwConfirm({
						text: texts.close_confirm,
						show: true,
						but_text: texts.continue,
						onconfirm: function (confirm) {
							obj.reload();
						}
					});
				} else {
					obj.reload();
				}

				e.preventDefault();
			});

			this.$el.find(".mw_statistics_reset").mwConfirm({
				text: texts.statistics_reset_confirm,
				but_text: texts.ab_reset_but,
				onconfirm: function (confirm) {
					obj.$el.find('.mw_funnel_setting').addClass('mw_loading');
					$.post(ajaxurl, {
						"action": "mwFunnelResetStatistics",
						"funnel_id": obj.id,
					}, function (content) {
						obj.$el.find('.mw_funnel_setting .mw_messages_container').mwAddMessage({
							content: content.html,
							close: true,
							autoHide: 10000,
						});
						if(content.success)
						{
							obj.setting_edited = false;
							obj.onReloadAction(content);
							obj.$el.find(".mw_range_select select").val('all');
							obj.$el.find(".mw_statistic_filter").removeClass("filtered");
							obj.$el.find('.mw_statistic_filter_inputs input').val('');
							obj.$el.find('.mw_statistic_filter_inputs select').val('');
							obj.$el.find('.mw_statistic_filter_input').show();
						}
						obj.$el.find('.mw_funnel_setting').removeClass('mw_loading');
					});
					return false;
				}
			});

			// save setting
			this.$el.find(".mwcb_setting_footer .mwcb_save_funnel_setting").click(function(e) {

				if(obj.checkSetting()) {

					var form = obj.$el.find('#mw_funnel_setting_form').serialize();
					obj.$el.find('.mw_funnel_setting').addClass('mw_loading');
					$.post(ajaxurl, 'action=mwSaveFunnelSetting&funnel_id=' + obj.id + '&' + form, function (content) {
						obj.$el.find('.mw_funnel_setting .mw_messages_container').mwAddMessage({
							content: content.html,
							close: true,
							autoHide: 10000,
						});
						if(content.success)
						{
							obj.setting_edited = false;
							obj.setEdited();
							obj.onReloadAction(content);
							obj.$el.find(".mw_modal_title span").html(content.title);
							obj.$el.find(".mw_range_select_container select").val('all');
							obj.$el.find(".mw_statistic_filter").removeClass("filtered");
							obj.$el.find('.mw_statistic_filter_inputs input').val('');
							obj.$el.find('.mw_statistic_filter_inputs select').val('');
							obj.$el.find('.mw_statistic_filter_input').show();
						}
						obj.$el.find('.mw_funnel_setting').removeClass('mw_loading');
					});

				}
				e.preventDefault();
			});

			// on content click
			this.$el.find(".mw_funnel_builder_tab").click(function(e) {

				var target = $(e.target);

				if (target.closest('.mwcb_edit_panel').length) {
					// do nothing
				} else {
					if (obj.editing) {
						obj.closeEditPanel();
					}

				}

			});

			// close edit panel
			this.$el.find('.mwcb_close_edit_panel').click(function(e) {
				obj.closeEditPanel();
				e.preventDefault();
			});

			// dropdown button
			this.$el.find(".mw_dropdown_button").mwDropdownButton({});

			// add item to funnel
			this.$el.find('.mwcb_add_item ul a').click(function(e) {
				var type = $(this).attr('href');

				$.post(ajaxurl, {
					"action": "mw_add_funnel_item",
					"type": type,
				}, function (data) {

					$(".mwcb_"+data.target+"_container").append(data.item);
					$(".mwcb_"+data.target+"_container").closest('.mwcb_funnel_row').removeClass('empty');
					$(".mw_funnel_builder_tab").removeClass('empty');

					obj.items_setting[data.item_id] = [];
					obj.items_setting[data.item_id] = data.setting;

					obj.setOrder();

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				});
				return false;
			});

			// delete item from funnel
			this.$el.on("click", ".mwcb_delete_item", function (e) {
				$(this).mwConfirm({
					text: texts.delete_item_confirm,
					show: true,
					onconfirm: function (confirm) {
						var container = confirm.$el.closest('.mwcb_funnel_row');
						var item = confirm.$el.closest('.mwcb_item');
						var id = item.attr('data-id');

						if(item.hasClass('item_editing'))
							obj.closeEditPanel(false);

						item.remove();

						if(container.find('.mwcb_item').length == 0)
							container.addClass('empty');

						delete obj.items_setting[id];

						if($('.mwcb_diagram').find('.mwcb_item').length == 0)
							$('.mw_funnel_builder_tab').addClass('empty');

						obj.setOrder();

						// on change
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				});
				e.preventDefault();
			});

			// edit funnel item
			this.$el.on("click", ".mwcb_edit_item", function (e) {
				var item = $(this).closest('.mwcb_item');

				if(obj.editing) {
					$.when(
						obj.saveItemSet(obj.editing)
					).then(
						obj.openItemSet(item)
					);
				} else {
					obj.openItemSet(item)
				}


				e.preventDefault();
			});

			this.$el.on("click", ".mwcb_item_url_to_clipboard", function (e) {
				var text = $(this).attr('data-url');
				if (typeof(navigator.clipboard)=='undefined') {
				    var textArea = document.createElement("textarea");
				    textArea.value = text;
				    textArea.style.position="fixed";  //avoid scrolling to bottom
				    document.body.appendChild(textArea);
				    textArea.focus();
				    textArea.select();

				    try {
				        var successful = document.execCommand('copy');
				        var msg = successful ? 'successful' : 'unsuccessful';
						alert(texts.url_to_clipboard);
				    } catch (err) {
				        console.error('Could not copy text: ', err);
				    }

				    document.body.removeChild(textArea)
				    return;
				}
				navigator.clipboard.writeText(text).then(function() {
				  alert(texts.url_to_clipboard);
				}, function(err) {
				  console.error('Could not copy text: ', err);
				});
				e.preventDefault();
			});

		}

		return _MwFunnelManager;

	})();
	MwFunnelManager.prototype.closeDetail = function (el) {
		el.mwLightbox({
			type: 'full',
			footer: false,
			show: true,
			form: false,
			id: 'mwFunnelSettingLightbox',
			class: 'mw_grey_bg',
			onopen: function (lightbox) {
				$.post(ajaxurl, {
					"action": "mw_open_funnels_setting",
				}, function (content) {
					lightbox.showHeader();
					lightbox.addContent(content);
					lightbox.$lightbox.find('.mw_funnels_dashboard').mwFunnelsSetting({});
				});
			}
		});
	}

	MwFunnelManager.prototype.addSorting = function () {
		var obj = this;
		this.$el.find('.mwcb_content_container').sortable(
			$.extend({
					handle: '.mwcb_move_item',
					items: ".mwcb_item"
				},
				{
					placeholder: "mwcb_item_placeholder",
					forcePlaceholderSize: true,
					stop: function (event, ui) {

						obj.setOrder();

						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				})
		);
	}
	MwFunnelManager.prototype.setOrder = function () {
		var obj = this;
		var i = 1;
		var id;
		this.$el.find('.mwcb_content_container .mwcb_item').each(function () {
			id = $(this).attr('data-id');
			obj.items_setting[id]['order'] = i;
			i++;
		});
	}

	MwFunnelManager.prototype.reload = function () {

		var obj = this;
		obj.$el.find(".mw_funnel_tab").addClass('mw_loading');
		var filter = obj.$el.find('.mw_statistic_filter_inputs form').serialize();
		var period = obj.$el.find(".mw_range_select_container select").val();
		var from = obj.$el.find( ".mw_range_select_container .mw_range_select_from" ).val();
		var to = obj.$el.find( ".mw_range_select_container .mw_range_select_to" ).val();

		$.post(ajaxurl, 'action=mwReloadFunnel&cId='+obj.id+'&period=' + period + '&from=' + from + '&to=' + to + '&' + filter, function (content) {
			obj.closeFunnelEditing();
			if(content.filter) {
				obj.$el.find(".mw_statistic_filter").addClass("filtered");
			} else {
				obj.$el.find(".mw_statistic_filter").removeClass("filtered");
			}
			obj.onReloadAction(content);
			obj.$el.find(".mw_funnel_tab").removeClass('mw_loading');
			obj.funnel_edited = false;
			obj.setEdited();
		});

	}

	MwFunnelManager.prototype.onReloadAction = function (content) {
		var obj = this;
		obj.$el.find('.mwcb_diagram').html(content.builder);
		obj.$el.find('.mwcb_statistics').html(content.statistics);
		obj.items_setting = mw_funnel_items_setting;
		obj.addSorting();
		this.$el.find('.mw_funnel_tooltip').tooltip({
			track: true
		});
		obj.$el.find(".mwcb_diagram_empty_info .mwcb_edit_funnel").click(function(e) {
			obj.openFunnelEditing();
			e.preventDefault();
		});
	}

	MwFunnelManager.prototype.openItemSet = function (item) {

		var obj = this;
		var id = item.attr('data-id');

		obj.$el.find(".item_editing").removeClass('item_editing');
		item.addClass('item_editing');
		this.$el.find(".mw_funnel_builder_tab").addClass('edit_panel_opened');
		this.$el.find(".mwcb_edit_panel").addClass('mw_loading');

		$.ajax({
			type: 'POST',
			data: {"action": "mwGenerateFunnelItemSetting", "setting": obj.items_setting[id]},
			url: ajaxurl,
			success: function (content) {
				obj.editing = id;
				obj.$el.find('#mwcb_item_setting').html(content);
				obj.$el.find(".mwcb_edit_panel").removeClass('mw_loading');
				obj.$editpanel.find('.mw_onedit_action').mwOnEditAction({
					change: function (field_setting) {
						obj.set(field_setting);
					}
				});
			}
		});
	}

	MwFunnelManager.prototype.closeEditPanel = function (save=true) {

		if(save && this.editing)
			this.saveItemSet(this.editing);

		$('.mwcb_item').removeClass('item_editing');
		this.$el.find(".mw_funnel_builder_tab").removeClass('edit_panel_opened');
		this.$el.find('#mwcb_item_setting').html('');
		this.editing = false;

	}
	MwFunnelManager.prototype.saveItemSet = function (id) {

		var obj = this;
		var newSetting = [];
		newSetting = this.$el.find('#mwcb_item_setting').serializeArray();
		if(newSetting) {
			obj.items_setting[id] = {};
			$.map(newSetting, function(n, i){
			  	var res=n['name'].match(/[^[\]]+(?=])/g);
			  	if(res){

					if (res.length == 1) {
						if(obj.items_setting[id][res[0]] === undefined) obj.items_setting[id][res[0]] = {};
						obj.items_setting[id][res[0]] = n['value'];
					} else if (res.length == 2) {
						if(obj.items_setting[id][res[0]] === undefined) obj.items_setting[id][res[0]] = {};
						if(obj.items_setting[id][res[0]][res[1]] === undefined) obj.items_setting[id][res[0]][res[1]] = {};
						obj.items_setting[id][res[0]][res[1]] = n['value'];
					}

				}
				else
				{
					obj.items_setting[id][n['name']] = n['value']
				}
	  		});

		}
		obj.editing = false;
		obj.funnel_edited = true;
		obj.setEdited();
	}

	MwFunnelManager.prototype.saveFunnelItems = function () {
		var obj = this;
		if(obj.editing)
			obj.closeEditPanel();

		obj.$el.find(".mw_funnel_tab").addClass('mw_loading');
		$.ajax({
			type: 'POST',
			data: {"action": "mwSaveFunnelItems", "set": obj.items_setting, 'funnel_id': obj.id},
			url: ajaxurl,
			success: function (content) {
				if(content.success)
				{
					obj.closeFunnelEditing();
					obj.onReloadAction(content);
					obj.$el.find(".mw_funnel_tab").removeClass('mw_loading');
					obj.funnel_edited = false;
					obj.setEdited();
				}
				else
				{
					obj.$el.find(".mw_funnel_tab").removeClass('mw_loading');
					obj.$el.find('.mwcb_diagram .mw_messages_container').mwAddMessage({
						content: content.html,
						close: true,
					});
				}
			}
		});

	}

	MwFunnelManager.prototype.closeFunnelEditing = function () {
		this.$el.find(".mw_funnel_builder_tab").removeClass('editing');
		this.$el.find(".mw_modal_head").show();
	}
	MwFunnelManager.prototype.openFunnelEditing = function () {
		this.$el.find(".mw_funnel_builder_tab").addClass('editing');
		this.$el.find(".mw_modal_head").hide();
	}

	MwFunnelManager.prototype.set = function (setting) {

		var selector = '.item_editing';

		if (setting.action == 'change_title') {
			$(selector+' .mwcb_item_title').html(setting.content);
		} else if (setting.action == 'change_icon') {
			$(selector+' .mwcb_source_item_icon').html(setting.content.code);
		} else if (setting.action == 'change_icon_color') {
			$(selector+' .mwcb_source_item_icon').css('color', setting.content);
		} else if (setting.action == 'reload') {
			//this.reload();
		}


	};

	MwFunnelManager.prototype.checkSetting = function () {

		var ret = true;
		var self = this;

		this.$el.find('.cms_error_message').remove();
		this.$el.find(".cms_required_alert").removeClass("cms_required_alert");

		this.$el.find(".mw_funnel_setting .required:visible").each(function () {
			if ($(this).val() == "") {
				ret = false;
				$(this).addClass('cms_required_alert');
				$(this).after('<div class="cms_error_message">' + texts.required + '</p>');
			}
		});

		$(".mw_funnel_setting .cms_required_alert").first().focus();

		return ret;

	}

	MwFunnelManager.prototype.setEdited = function () {

		if(this.setting_edited || this.funnel_edited) {
			$('body').attr('data-modified', 1);
		}
		else {
			$('body').attr('data-modified', 0);
		}

	}

	$.fn.mwFunnelManager = function (options) {
		return this.each(function (index, el) {
			el.MwFunnelManager = new MwFunnelManager(el, options);
		});
	};

	// mw funnel page manager
	var MwFunnelPageSelector = (function (element, settings) {

		function _MwFunnelPageSelector(element, settings) {

			this.$el = $(element);

			var obj = this;

			this.$el.find('.mw_item_selector').mwPageSelect({
				change: function (slector) {
					if(slector.val()=='')
					{
						obj.$el.find('.mw_fps_select').removeClass('mw_fps_selected');
					}
					else
					{
						$('.item_editing .mwcb_item_error_info').hide();
						obj.$el.find('.mw_fps_select').addClass('mw_fps_selected');
					}
				}
			});

			// installator ab test
			$('.mwcb_edit_panel_content').on('click', '.mwf_create_ab_test', function () {

					var pageId = $('.mw_funnel_page_setting_container #cp_page_id').val();

					$(this).mwLightbox({
						type: 'full_modal',
						show: true,
						footer: false,
						header: false,
						scroll: false,
						white_bg: true,
						id: 'mwAbTestInstallatorLightbox',
						onopen: function (lightbox) {

							$.post(ajaxurl, {"action": "mwOpenInstallator", 'install':'abtest', 'objectid':'page'}, function (content) {
								lightbox.addContent(content);

								lightbox.$lightbox.find('.mw_installator_container').mwInstallator({
									oninstall: function() {

										if (lightbox.checkForm()) {

											var formdata = lightbox.getFormData();
											lightbox.addLoading();

											var lightboxInstallator = lightbox;

											$.post(ajaxurl, 'action=mwFunnelCreateABTestPage&pageId='+pageId+'&' + formdata, function (content) {
												if(content.ab_id)
												{
													obj.$el.find('.mw_fps_ab_page').val(content.ab_id);
													obj.$el.addClass('mw_fps_with_ab');
													obj.$el.find('.mw_fps_ab').html(content.ab_content);
													lightbox.close();
												}

											});

										}

									},
									onclose: function() {
										lightbox.close();
									}
								});
							});

						}
					});

					return false;
			});

			this.$el.on("click", ".mw_fps_declare_winner", function (e) {
					var winner=$(this).attr('data-winner');
					$.post(ajaxurl, {
						"action" : "mwFunnelDeclareABTestWinner",
						'winner' : winner,
						'original_id' : obj.$el.find('.mw_fps_select select').val(),
						'variant_id' : obj.$el.find('.mw_fps_ab_page').val(),
					}, function (content) {
						obj.$el.removeClass('mw_fps_with_ab');
						obj.$el.find('.mw_fps_ab_page').val('');
						$('.item_editing').removeClass('mwcb_item_wtest');
					});
					e.preventDefault();
			});

		}

		return _MwFunnelPageSelector;

	})();

	$.fn.mwFunnelPageSelector = function (options) {
		return this.each(function (index, el) {
			el.MwFunnelPageSelector = new MwFunnelPageSelector(el, options);
		});
	};

	var MwFunnelSellSetting = (function (element, settings) {

		function _MwFunnelSellSetting(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.on('change', '.mw_api_product_selector', function () {
				// on change
				if (obj.settings.change) {
					var api = obj.$el.find(".change_api_selector").val();
					obj.settings.change.call(this, api);
				}
			});

			this.$el.on("change", ".change_api_selector", function () {

				var tagid = $(this).attr('data-id');
				var tagname = $(this).attr('data-name');
				var api = $(this).val();
				var selector = $(this);

				obj.$el.find('.mw_funnel_sell_setting').addClass('mw_loading_w');

				$.post(ajaxurl, {
					"action": "mw_generate_funnel_sell_setting",
					"api_id": api,
					"tag_id": tagid,
					"tag_name": tagname
				}, function (data) {
					obj.$el.find('.mw_funnel_sell_setting').removeClass('mw_loading_w').html(data);

					if (obj.settings.change) {
						obj.settings.change.call(this, api);
					}

				});
				return false;
			});

			this.$el.on('click', '.add_api_connection', function () {

				$(this).mwOpenApiConnectModal({
					onconnect: function (data) {
						console.log(data);
						obj.$el.find('.mw_funnel_sell_setting').html(data.content);
					},
					action: "mw_connect_funnel_sell_setting",
				});

				return false;
			});

		}

		return _MwFunnelSellSetting;

	})();

	$.fn.mwFunnelSellSetting = function (options) {
		return this.each(function (index, el) {
			el.MwFunnelSellSetting = new MwFunnelSellSetting(el, options);
		});
	};

});
