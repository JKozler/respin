
;(function (factory) {

	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports !== 'undefined') {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}

})(function ($) {

	// ab selector
	var MwOpenInstallator = (function (element, settings) {

		function _MwOpenInstallator(element, settings) {

			this.defaults = {
				oninstall: null,
				install: 'object',
				objectid: 'page',
				action: 'mwAddNewObject',
				ajax_url_data: '',
				front_redirect: 0,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			this.$el = $(element);
			var obj = this;

			this.$el.mwLightbox({
					type: 'full_modal',
					show: true,
					footer: false,
					header: false,
					scroll: false,
					white_bg: true,
					id: 'mw'+obj.settings.install+'InstallatorLightbox',
					onopen: function (lightbox) {
						$.post(ajaxurl, {"action": "mwOpenInstallator", 'install':obj.settings.install, 'objectid':obj.settings.objectid, 'front_redirect':obj.settings.front_redirect}, function (content) {
							lightbox.addContent(content);

							lightbox.$lightbox.find('.mw_installator_container').mwInstallator({
								oninstall: function() {

									if (lightbox.checkForm()) {

										var formdata = lightbox.getFormData();
										lightbox.addLoading();

										var lightboxInstallator = lightbox;
										var url = 'action=' + obj.settings.action + '&' + formdata;
										if(obj.settings.ajax_url_data) {
											url = url + obj.settings.ajax_url_data;
										}

										$.post(ajaxurl, url, function(data){

											if (obj.settings.oninstall)
											{
												obj.settings.oninstall.call(obj, lightboxInstallator, data);
											}
											else
											{
												if(data.success)
												{
													window.location = data.redirect;
												}
												else
												{
													lightbox.$lightbox.find('.mw_installator_error_area').html(data.html);
													lightbox.removeLoading();
												}
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


		}

		return _MwOpenInstallator;

	})();

	$.fn.mwOpenInstallator = function (options) {
		return this.each(function (index, el) {
			el.MwOpenInstallator = new MwOpenInstallator(el, options);
		});
	};

	// Template selector (on page create)
	var MwInstallator = (function (element, settings) {

		function _MwInstallator(element, settings) {

			this.defaults = {
				oninstall: null,
				onclose: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			this.$el = $(element);
			this.step = 1;

			var obj = this;

			this.$el.find(".mw_installator_go_back").hide();

			this.$el.find(".mw_installator_go_next").click(function (e) {
				obj.step ++;
				obj.changeStep();
				e.preventDefault();
			});

			this.$el.find(".mw_installator_go_back").click(function (e) {
				obj.step --;
				obj.changeStep();
				e.preventDefault();
			});

			this.$el.find(".mw_installator_close").click(function (e) {
				if (obj.settings.onclose) {
					obj.settings.onclose.call(this, obj);
				}
				e.preventDefault();
			});

			this.$el.find(".mw_installator_select_input").click(function (e) {
				$(this).closest('.mw_install_select_type_item').find('input').prop("checked", true);
			});
			this.$el.find(".mw_installator_select_empty_input").click(function (e) {
				$(this).closest('.mw_install_select_type_empty').find('input').prop("checked", true);
			});

			this.$el.find(".mw_installator_install").click(function (e) {
				if (obj.settings.oninstall) {
					obj.settings.oninstall.call(this, obj);
				}
				e.preventDefault();
			});

			this.$el.find(".mw_installator_select_template_install").click(function (e) {
				$(this).closest('.mw_template_item').find('input').prop("checked", true);
				if (obj.settings.oninstall) {
					obj.settings.oninstall.call(this, obj);
				}
				e.preventDefault();
			});
			this.$el.find(".mw_template_item_select").click(function (e) {
				$(this).closest('.mw_template_item').find('input').prop("checked", true);
				e.preventDefault();
			});


			this.$el.find('.mw_category_tabs').mwTabs();
			this.$el.find('.mw_ab_select_container').mwABSelect();

		}

		return _MwInstallator;

	})();

	MwInstallator.prototype.changeStep = function () {
		this.$el.find('.mw_installator_progress span').html(this.step);
		this.$el.find('.mw_installator_step').hide();
		this.$el.find('.mw_installator_step_'+this.step).show();
		if(this.step==1)
		{
			this.$el.find('.mw_installator_go_back').hide();
		}
		else
		{
			this.$el.find('.mw_installator_go_back').show();
		}
	}

	$.fn.mwInstallator = function (options) {
		return this.each(function (index, el) {
			el.MwInstallator = new MwInstallator(el, options);
		});
	};


	// Template selector (on page create)
	var MwWebTemplateSelector = (function (element, settings) {

		function _MwWebTemplateSelector(element, settings) {

			this.defaults = {
				confirm: 0,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			this.$el = $(element);
			var obj = this;

			this.$el.find(".mw_install_web_template").click(function () {
				var temp_id = $(this).closest('.mw_template_item').find('input').val();

				if (obj.settings.confirm) {
					$(this).mwConfirm({
						text: texts.confirm_install,
						show: true,
						but_text: texts.continue,
						onconfirm: function (confirm) {
							obj.install(temp_id);
						}
					});
				} else {
					obj.install(temp_id);
				}

			});

			this.$el.find(".mw_select_tag").click(function () {
				var target = $(this).attr('data-tag');
				var container = $(this).attr('data-container');
				$(".mw_select_tag").removeClass("active");
				$(this).addClass("active");
				$(".mw_tag_item").hide();
				$(".mw_tag_item_" + target).show();
				return false;
			});


		}

		return _MwWebTemplateSelector;

	})();

	MwWebTemplateSelector.prototype.install = function (temp_id) {
		$('.mw_builder_setting_window').addClass('loading');

		$.post(ajaxurl, {"action": "mw_install_web_template", "temp_id": temp_id}, function (data) {
			window.location.href = data;
		});
	}

	$.fn.mwWebTemplateSelector = function (options) {
		return this.each(function (index, el) {
			el.MwWebTemplateSelector = new MwWebTemplateSelector(el, options);
		});
	};

	// ab selector
	var MwABSelect = (function (element, settings) {

		function _MwABSelect(element) {

			this.$el = $(element);
			var obj = this;

			this.$el.find(".mw_select_ab_page_type").change(function () {
				var val = $(this).val();
				obj.$el.removeClass('mw_select_ab_type_new mw_select_ab_type_existing mw_select_ab_type_copy');
				obj.$el.addClass('mw_select_ab_type_'+val);
			});

			this.$el.find('.mw_select_ab_page_id').chosen({width: "100%"});

		}

		return _MwABSelect;

	})();

	$.fn.mwABSelect = function (options) {
		return this.each(function (index, el) {
			el.MwABSelect = new MwABSelect(el, options);
		});
	};

});
