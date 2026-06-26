jQuery(document).ready(function ($) {

// sidebar name check
	$(".cms_create_new_sidebar").click(function () {
		if ($("#cms_new_sidebar_name").val() == "") {
			alert('Zadejte název sidebaru');
			$("#cms_new_sidebar_name").focus();
			return false;
		}
	});

//delete sidebar
	$('.widget-liquid-right').on("click", '.cms_delete_widget', function () {
		var id = $(this).closest('.widgets-sortables').attr('id');
		if (confirm($(this).attr('data-question'))) {
			$(this).parent().html('<div class="cms_loading"></div>');
			$.post(ajaxurl, {"action": "cms_delete_sidebar", "id": id}, function () {
				$("#" + id).parent().remove();
			});
		}
		return false;
	});

	// form widget

	$( document ).on( 'widget-updated', function( e, widget ){
		// widget is jquery instance of updated widget
		$( '.widget_setting_form_container', widget ).mwFormWidget();
	} );

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

	// mw tooltip
	var MwFormWidget = (function (element) {

		function _MwFormWidget(element) {

			this.$el = $(element);
			if(!this.$el.hasClass('mw_form_widget_js_loaded'))
			{
				this.$el.find('.cms_color_input').minicolors();
				this.$el.find('.mw_api_connection_container').mwEmailMarketingSelector();
				this.$el.find('.background_color_field_container').mwBackgroundColor();
				this.$el.find('.mw_slider').mwSlider();
				this.$el.find('.widget_form_font').mwFontSetting();

				this.$el.find('.mw_form_widget_setting_show_popup_setting').change(function () {

					var container = $(this).closest('.form_popup_setting').find('.form_popup_setting_container');
					if ($(this).prop("checked") == true) {
						container.show();
					}
					else {
						container.hide();
					}
				});
				this.$el.addClass('mw_form_widget_js_loaded');
			}

		}

		return _MwFormWidget;

	})();

	$.fn.mwFormWidget = function () {
		return this.each(function (index, el) {
			el.MwFormWidget = new MwFormWidget(el);
		});
	};
});
