jQuery(function ($) {

	$('.el_mem_checklist').mwMemberChecklist();

	$('.mw_element_item_popup .mw_element_item_title a, .mw_element_item_popup .mw_element_item_image_hover').mwElementPopup();

	// TODO JQMIGRATE: jQuery.hover is deprecated, use "mouseenter" and "mouseleave" events (https://stackoverflow.com/a/13004732)
	$("#member_user_avatar").hover(
		function () {
			$(this).addClass('member_user_menu_open');
			$(this).removeClass('member_user_menu_close');
		},
		function () {
			$(this).removeClass('member_user_menu_open');
			$(this).addClass('member_user_menu_close');
		});

	//show user profile
	$("#member_show_profile").click(function () {
		$("#member_profile_background").show();
		$("#member_profile").show();
		return false;
	});
	$("#member_close_profile").click(function () {
		$("#member_profile_background").hide();
		$("#member_profile").hide();
		return false;
	});

	//news
	$(".member_new_show_text").click(function () {
		$(this).closest(".mw_element_item_description").find(".member_new_short").toggle();
		$(this).closest(".mw_element_item_description").find(".member_new_text").toggle();
		return false;
	});

	mw_init_register_form('.mw_member_register_form');
});

function mw_init_register_form(target) {
	jQuery(target).mwForm({
		onsubmit: function (self, $form) {
			var form = $form.serialize();
			var loading = self.$el.find("button");
			loading.addClass('working');

			jQuery.post(ajaxurl, 'action=send_registration_form&' + form, function (data) {

				if (data.redirect) {
					if (data.target) {
						window.parent.location = data.redirect;
					} else {
						window.location = data.redirect;
					}
				}
				else {
					self.showMessage(data);
					loading.removeClass('working');
				}

			}).fail(function (data) {
				console.log(data);
				self.showMessage({'sended': 'error', 'message': mem_front_texts.reg_nosended});
				loading.removeClass('working');
			});

		}
	});
}

function mw_init_member_checklist(target) {
	jQuery(target).mwMemberChecklist();
}

;(function (factory) {

	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports !== 'undefined') {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}

})(function ($) {

	// checklist
	var MwMemberChecklist = (function (element) {

		function _MwMemberChecklist(element) {
			this.$el = $(element);
			this.userId = this.$el.data('user_id');
			this.checklistId = this.$el.data('checklist_id');
			this.type = this.$el.data('type');
			var obj = this;

			this.$el.find('li').click(function() {

				$(this).find('.mem_checklist_checkbox').toggleClass('mem_checklist_checkbox_checked');
				var checkbox = $(this).find('input');

				if($(this).find('.mem_checklist_checkbox').hasClass('mem_checklist_checkbox_checked')) {
					checkbox.attr('checked', true);
					var checked = 1;
				} else {
					checkbox.attr('checked', false);
					var checked = 0;
				}
				var id = checkbox.val();

				$.post(ajaxurl, {
					"action": "mwmSaveMemberChecklist",
					"taskId": id,
					"checked": checked,
					"type": obj.type,
					"userId": obj.userId,
					"checklistId": obj.checklistId,
				}, function (data) {
					//console.log(data);
				});

			});

		}

		return _MwMemberChecklist;

	})();

	$.fn.mwMemberChecklist = function (options) {
		return this.each(function (index, el) {
			el.MwMemberChecklist = new MwMemberChecklist(el, options);
		});
	};

});
