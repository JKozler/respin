jQuery(function ($) {

	$('.mw_setting_type_members_dashboard').mwMemberDashboard({});

	// user member setting
	$(".mw_user_member_item_checkbox").change(function () {
		$(this).closest('.mw_user_member_item').toggleClass('checked');
	});
	$(".mw_user_member_item .cms_datepicker").datepicker({dateFormat: "dd.mm.yy"});

	$('.mw_member_fast_add').click(function(){
		$(this).mwOpenFastAddModal({
			returnType: '',
			onsave: function (data) {
				if (data.success) {
					window.location = data.admin_url;
				}
			}
		});
		return false;
	});

	// save setting
	$('.mw_member_import_but').click(function(e) {

		var formdata = $('form').serialize();

		$('.mw_setting_content').addClass('mw_loading');

		$.post(ajaxurl, 'action=mwImportMembers&' + formdata, function (data) {

			$('.mw_setting_message_box').mwAddMessage({
				content: data.html,
				close: true,
				//autoHide: 10000,
			});

			if (data.success) {
				$("form").get(0).reset();
				$('.mw_setting_box').removeClass('checked');
				$('.cms_show_group__send_custom_email').hide();
				//obj.setEdited(0);
			}

			$('.mw_setting_content').removeClass('mw_loading');

		}).fail(function (data) {

		});

		e.preventDefault();
	});

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

	var MwMemberDashboard = (function (element, settings) {

		function _MwMemberDashboard(element, settings) {

			this.$el = $(element);
			this.container = this.$el.find('.mw_dashboard_statistics');
			var obj = this;


			// object list
			this.$el.find(".mw_dashboard_list_container").mwSettingList({
				ondelete: function () {
					obj.reloadStatistics();
				},
			});

		}

		return _MwMemberDashboard;

	})();

	MwMemberDashboard.prototype.reloadStatistics = function() {
		obj = this;
		obj.container.addClass('mw_loading');
		$.ajax({
			type: 'POST',
			data: {"action": "mwMemberReloadDashboardStatistics"},
			url: ajaxurl,
			success: function (content) {
				obj.$el.find('.mw_dashboard_statistics').html(content);
				obj.container.removeClass('mw_loading');
			}
		});
	}

	$.fn.mwMemberDashboard = function (options) {
		return this.each(function (index, el) {
			el.MwMemberDashboard = new MwMemberDashboard(el, options);
		});
	};

	// mw member selector
	var MwMemberSelector = (function (element, settings) {

		function _MwMemberSelector(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;
			//change member section and show right member levels
			this.$el.find(".mw_member_section_select").change(function () {
				obj.$el.find(".member_levels_container").hide();
				obj.$el.find("#member_levels_container_" + $(this).val()).show();
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.find(".member_level_item input").change(function () {
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});


		}

		return _MwMemberSelector;

	})();

	$.fn.mwMemberSelector = function (options) {
		return this.each(function (index, el) {
			el.MwMemberSelector = new MwMemberSelector(el, options);
		});
	};

	// mw membership creator
	var MwMembershipCreator = (function (element, settings) {

		function _MwMembershipCreator(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;
			//change member section and show right member levels
			this.$el.find(".mw_membership_creator_type").change(function () {
				if($(this).find('option:selected').val() == 'limit')
				{
					obj.$el.find('.mw_membership_creator_days').show();
					obj.$el.find('.mw_membership_creator_limit_date').hide();
				}
				else if($(this).find('option:selected').val() == 'limit_date')
				{
					obj.$el.find('.mw_membership_creator_limit_date').show();
					obj.$el.find('.mw_membership_creator_days').hide();
				}
				else
				{
					obj.$el.find('.mw_membership_creator_days').hide();
					obj.$el.find('.mw_membership_creator_limit_date').hide();
				}
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.find(".mw_membership_creator_limit_date .cms_datepicker").datepicker({
				dateFormat: "dd.mm.yy",
				onSelect: function (date, ui) {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});
			$.datepicker.setDefaults($.datepicker.regional["cs"]);

			this.$el.find(".mw_member_section_select").change(function () {
				obj.$el.find(".member_levels_container").hide();
				obj.$el.find("#member_levels_container_" + $(this).val()).show();
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.find(".mw_membership_creator_set_date input").change(function () {

				if($(this).prop("checked"))
				{
					obj.$el.find('.mw_membership_creator_date').show();
				}
				else
				{
					obj.$el.find('.mw_membership_creator_date').hide();
				}

				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

		}

		return _MwMembershipCreator;

	})();

	$.fn.mwMembershipCreator = function (options) {
		return this.each(function (index, el) {
			el.MwMembershipCreator = new MwMembershipCreator(el, options);
		});
	};

});
