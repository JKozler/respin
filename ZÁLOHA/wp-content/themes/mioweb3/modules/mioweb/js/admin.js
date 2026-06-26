;(function (factory) {

	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports !== 'undefined') {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}

})(function ($) {

	// mw campaign manager
	var MwCampaignManager = (function (element, settings) {

		function _MwCampaignManager(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".mw_tabs").mwTabs();

			// add page to campaign
			this.$el.on("click", ".mioweb_add_campaign_page", function () {
				var id = $(this).attr('data-id');
				var tagid = $(this).attr('data-tagid');
				var tagname = $(this).attr('data-name');
				$(this).attr('data-id', parseInt(id) + 1);
				link = $(this);
				link.before('<div class="miocms_loading"></div>');
				$.post(ajaxurl, {
					"action": "add_campaign_page",
					"id": id,
					"tagid": tagid,
					"tagname": tagname
				}, function (data) {
					link.closest('.campaign_set_button_container').before('<div class="campaign_set_box campaign_set_box_added campaign_set_box_page mw_rounded mw_shadow_b">' + data + '</div>');
					$(".miocms_loading").remove();

					$('.campaign_set_box_added').mwCampaignPageManager({
						change: function () {
							if (obj.settings.change) {
								obj.settings.change.call();
							}
						}
					});
					$('.campaign_set_box_added').removeClass('campaign_set_box_added');

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				});
				return false;
			});

			// select campaign page in selector
			this.$el.on("change", ".campaing_select_page", function () {
				var used = false;
				var val = $(this).val();
				var id = $(this).attr('id');
				if (val) {
					$(".campaing_select_page").each(function (index) {
						if (val == $(this).val() && $(this).attr('id') != id) used = true;
					});
					if (used) {
						alert(campaign_texts.page_conflict);
						$(this).val('');
					}
				}
			});

			this.$el.find('.campaign_set_box').mwCampaignPageManager({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

		}

		return _MwCampaignManager;

	})();

	$.fn.mwCampaignManager = function (options) {
		return this.each(function (index, el) {
			el.MwCampaignManager = new MwCampaignManager(el, options);
		});
	};

	// mw campaign page manager
	var MwCampaignPageManager = (function (element, settings) {

		function _MwCampaignPageManager(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// delete page from campaign
			this.$el.find(".mioweb_delete_campaign_page").click(function () {
				$(this).mwConfirm({
					text: campaign_texts.delete_page_confirm,
					show: true,
					onconfirm: function (confirm) {
						obj.$el.slideUp(200, function () {
							obj.$el.remove();
						});
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				});
				return false;
			});

			// toggle page setting
			this.$el.find(".mioweb_setting_campaign_page").click(function () {
				$(this).toggleClass('mw_icon_button_active');
				obj.$el.find('.campaign_page_set').toggle();
				return false;
			});

			// image uploader
			this.$el.find('.mw_image_uploader').mwImageUplader({
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

			this.$el.find('.mw_item_selector').mwPageSelect({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});


		}

		return _MwCampaignPageManager;

	})();

	$.fn.mwCampaignPageManager = function (options) {
		return this.each(function (index, el) {
			el.MwCampaignPageManager = new MwCampaignPageManager(el, options);
		});
	};

});
