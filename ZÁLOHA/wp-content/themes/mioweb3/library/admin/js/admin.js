jQuery(function ($) {

	$('.postbox .mw_tabs').mwTabs();

	$('body').on('click', '.mw_setting_fast_add', function () {
		var $t = $(this);
		$(this).mwOpenFastAddModal({
			returnType: $t.attr('data-return'),
			returnTarget: $t.attr('data-target'),
			returnName: $t.attr('data-name'),
		});
	});

	// alert info bar
	$('.mw_builder_alert_info .close').click(function () {
		$('.mw_builder_alert_info').hide();
	});

	// variables list
	$('body').on('click', '.mw_open_order_variable_list', function() {
		$(this).mwModal({
			class: 'mw_modal_size_wide',
			but_text: texts.close,
			storno: false,
			onopen: function (modal) {
				modal.addContent(modal.$el.closest('.mw_order_variable_list').find('.mw_order_variable_list_pop_content').html());
			},
			onsave: function (modal) {
				modal.close();
				return false;
			}

		});

		return false;
	});

// Permalink selector
	function permalinkGetEditValue(elEdit) {
		var perm = elEdit.val();
		if (!perm)
			perm = elEdit.attr("placeholder");
		return perm;
	}

	function permalinkGetParentValue(parentId) {
		var elParent = $('.field_permalink_id_' + parentId);
		if (elParent.length > 0)
			return permalinkGetActiveValue(elParent);
		else
			return '';
	}

	function permalinkGetActiveValue(elDivPermalink) {
		var elBasic = elDivPermalink.find('input[type="text"].field_permalink_basic');
		var elNested = elDivPermalink.find('input[type="text"].field_permalink_nested');
		if (elBasic.length && elBasic.is(':visible'))
			return permalinkGetEditValue(elBasic.first());
		else if (elNested.length && elNested.is(':visible'))
			return permalinkGetEditValue(elNested.first());
		else
			return '';
	}

	function permalinkPreview(elDivPermalink, perm, parentPerm) {
		var elPreview = elDivPermalink.find('.field_permalink_preview');
		var baseUri = elDivPermalink.attr('data-base-uri');
		var newPerm = (baseUri ? baseUri + '/' : 'http:/???/') + (parentPerm ? parentPerm + '/' : '') + perm;
		elPreview.html(newPerm);
	}

	$('.options-permalink-php form').on('keyup', '.field_permalink_basic', function () {
		var elContainer = $(this).closest('.field_permalink_container');
		var elActive = elContainer.find('.field_permalink_basic');
		if (elActive.length /*&& elActive.is(':visible')*/) {
			var perm = permalinkGetEditValue(elActive);
			//console.log("edit basic permalink=", perm);
			permalinkPreview(elContainer, perm, '');
			//Update possible derivated permalink setters.
			$('.field_permalink_nested').keyup();
		}
	});

	$('.options-permalink-php form').on('keyup', '.field_permalink_nested', function () {
		var elContainer = $(this).closest('.field_permalink_container');
		var elActive = elContainer.find('.field_permalink_nested');
		if (elActive.length && elActive.is(':visible')) {
			var perm = permalinkGetEditValue(elActive);
			var parentId = elActive.attr('data-parent-id');
			var parentPerm = permalinkGetParentValue(parentId);
			//console.log("edit nested permalink=" + perm + " parentPermalink=" + parentPerm);
			permalinkPreview(elContainer, perm, parentPerm);
		}
	});

	$('.options-permalink-php form').on('change', '.field_permalink_use_nested', function () {
		var elContainer = $(this).closest('.field_permalink_container');
		var checked = (elContainer.find('.field_permalink_use_nested').prop("checked") == true);
		var elBasic = elContainer.find('.field_permalink_basic');
		var elNested = elContainer.find('.field_permalink_nested');
		var elActive, parentPerm = '';
		if (checked) {
			elBasic.hide();
			elNested.show();
			elActive = elNested;
			var parentId = elNested.attr('data-parent-id');
			parentPerm = permalinkGetParentValue(parentId);
			//console.log("switch parent permalink=",parentPerm);
		} else {
			elBasic.show();
			elNested.hide();
			elActive = elBasic;
		}
		var perm = permalinkGetEditValue(elActive);
		//console.log("switch permalink=",perm);
		permalinkPreview(elContainer, perm, parentPerm);
	});//.change();

});

function mwCheckForm(element) {

	var ret = true;

	element.find('.cms_error_message').remove();
	element.find(".cms_required_alert").removeClass("cms_required_alert");
	element.find(".required:visible").each(function () {
		if (jQuery(this).val() == "") {
			ret = false;
			jQuery(this).addClass('cms_required_alert');
			jQuery(this).after('<div class="cms_error_message">' + texts.required + '</p>');
		}
	});

	element.find(".cms_required_alert").first().focus();

	return ret;

}

function mwGetCssStyles(css) {
	var array = {};
	var ident;
	var styles;
	var style;

	css = css.replace('://', '////');

	var def = css.split('}');

	if (def) {

		jQuery.each(def, function (def_key, def_val) {

			ident = def_val.split("{");
			if (ident[1]) {
				array[ident[0]] = {};

				styles = ident[1].split(';');

				jQuery.each(styles, function (s_key, s_val) {
					style = s_val.split(':');
					if (styles[s_key][1] && style[1]) {
						array[ident[0]][style[0]] = {};
						array[ident[0]][style[0]] = style[1];
					}
				});

			}

		});
	}

	return array;
}

function mwSaveCssStyles(styles) {
	var tosave = '';

	jQuery.each(styles, function (s_key, s_val) {
		if (s_val !== '') {
			tosave += s_key + '{';
			jQuery.each(s_val, function (key, val) {
				if (val !== '' && val != 'px' && val != 'em') {
					tosave += key + ':' + val + ';';
				}
			});
			tosave += '}';
		}
	});

	tosave = tosave.replace('////', '://');

	return tosave;
}

function mwChangeCssStyles(styles, selector, newstyles) {

	if (!styles[selector]) styles[selector] = {};

	jQuery.each(newstyles, function (index, value) {
		if (!styles[selector][index]) styles[selector][index] = '';
		styles[selector][index] = value;
	});

	return styles;
}

// NEW SETTING

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
	var MwTooltip = (function (element) {

		function _MwTooltip(element) {

			this.$el = $(element);

			this.$el.append('<div class="mw_tooltip_hover">' + this.$el.attr('data-title') + '</div>');

			// TODO JQMIGRATE: jQuery.hover is deprecated, use "mouseenter" and "mouseleave" events (https://stackoverflow.com/a/13004732)
			this.$el.hover(function () {
				var tooltip = $(this).find('.mw_tooltip_hover');
				var width = tooltip.outerWidth();
				tooltip.css({marginLeft: '-' + (width / 2) + 'px'});
			});


		}

		return _MwTooltip;

	})();

	$.fn.mwTooltip = function () {
		return this.each(function (index, el) {
			el.MwTooltip = new MwTooltip(el);
		});
	};

	// mw dropdown button
	var MwDropdownButton = (function (element) {

		function _MwDropdownButton(element) {

			this.$el = $(element);
			var obj = this;

			this.$el.find('a').click(function (e) {
				obj.$el.toggleClass('open');
				if($(this).attr('href') == '#')
					e.preventDefault();
			});
			this.$el.find('ul').mouseleave(function (e) {
				obj.$el.removeClass('open');
			});

		}

		return _MwDropdownButton;

	})();

	$.fn.mwDropdownButton = function () {
		return this.each(function (index, el) {
			el.MwDropdownButton = new MwDropdownButton(el);
		});
	};

	// mw link select
	var MwLinkSelect = (function (element, settings) {

		function _MwLinkSelect(element, settings) {

			this.$el = $(element);

			this.defaults = {
				onchange: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('a').click(function (e) {
				obj.$el.find('.mw_dropdown_list').toggleClass('open');
				e.preventDefault();
			});

			this.$el.find('ul a').click(function (e) {

				var val = $(this).attr('data-val');
				var text = $(this).text();

				obj.$el.find('input').val(val);
				obj.$el.find('.mw_link_select_current_text').text(text);

				// on change
				if (obj.settings.onchange) {
					obj.settings.onchange.call();
				}

				e.preventDefault();
			});

			this.$el.find('ul').mouseleave(function (e) {
				obj.$el.find('.mw_dropdown_list').removeClass('open');
			});

		}

		return _MwLinkSelect;

	})();

	$.fn.mwLinkSelect = function (options) {
		return this.each(function (index, el) {
			el.MwLinkSelect = new MwLinkSelect(el, options);
		});
	};

	// mw link select
	var MwClickSearch = (function (element, settings) {

		function _MwClickSearch(element, settings) {

			this.$el = $(element);

			this.defaults = {
				onchange: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.click(function (e) {
				obj.$el.addClass('open');
				setTimeout(function () {
					obj.$el.find('input').focus();
				}, 150);
				e.preventDefault();
			});

			var globalTimeout = null;

			obj.$el.find('input').keyup(function(event) {

				var keycode = (event.keyCode ? event.keyCode : event.which);
				if (keycode == '13') {
					return false;
				}

				if($(this).val())
				{
					obj.$el.addClass('filled');
				}
				else
				{
					obj.$el.removeClass('filled');
				}

				if (globalTimeout != null) {
			      clearTimeout(globalTimeout);
			    }
			    globalTimeout = setTimeout(function() {
			    	globalTimeout = null;
					// on change
					if (obj.settings.onchange) {
  						obj.settings.onchange.call();
  					}
			    }, 500);

			});

			obj.$el.find('input').keypress(function (event) {
				var keycode = (event.keyCode ? event.keyCode : event.which);
				if (keycode == '13') {
					return false;
				}
			});

			obj.$el.find('.mw_icon_close').click(function(){
				obj.$el.find('input').val('');
				obj.$el.removeClass('filled');
				if (obj.settings.onchange) {
					obj.settings.onchange.call();
				}
			});

			obj.$el.find('input').focusout(function(){
				if(!$(this).val())
				{
					obj.$el.removeClass('open');
				}
			});

		}

		return _MwClickSearch;

	})();

	$.fn.mwClickSearch = function (options) {
		return this.each(function (index, el) {
			el.MwClickSearch = new MwClickSearch(el, options);
		});
	};

	// image uploader
	var MwImageUplader = (function (element, settings) {

		function _MwImageUplader(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// upload button
			this.$el.find('.mw_image_uploader_upload').click(function (e) {

				e.preventDefault();

				var workflow = wp.media.editor.get('mw_image_upload');
				var mw_target = $(this);

				//If WF already exist, just open modal
				if (typeof workflow !== 'undefined') {
					workflow.options.mw_target = mw_target;
					workflow.options.mw_obj = obj;
					workflow.open();
					return;
				}

				//Create new WF and bind events
				workflow = wp.media.editor.add('mw_image_upload', {
					frame: 'post',
					state: 'insert',
					title: wp.media.view.l10n.addMedia,
					multiple: false,
					library: {type: 'image'},
					mw_target: mw_target,
					mw_obj: obj
				});

				workflow.on('open', function () {

					jQuery('.media-frame').addClass('hide-menu');

				});

				//Insert new images to image list
				workflow.on('insert', function (selection) {

					var button = workflow.options.mw_target;
					var mw_obj = workflow.options.mw_obj;
					var target = button.attr('target');
					var container = button.closest('.mw_image_uploader_container');
					var main_container = button.closest('.mw_image_uploader');
					var respect_size = container.data('respect_size');

					var state = workflow.state();
					var image = state.get('selection').first().toJSON();
					//console.log(image);
					if(respect_size === 1) {
						var selected_size = $('.media-sidebar .size').val();
					} else {
						var selected_size = 'full';
					}

					var imgurl = image['sizes'][selected_size]['url'].replace(MioAdminjs.siteurl, "");
					var full_imgurl = image['sizes']['full']['url'];

					container.find('.mw_image_uploader_image').val(imgurl);
					container.find('.mw_image_uploader_full_image').val(full_imgurl);
					container.find('.mw_image_uploader_imageid').val(image.id);
					container.find('.mw_image_uploader_pattern').val('');
					container.find('.mw_image_uploader_position').val('50% 50%');
					container.find('.mw_image_uploader_position_drag').css('top','50%');
					container.find('.mw_image_uploader_position_drag').css('left','50%');
					container.find('.mw_image_uploader_selected_size').val(selected_size);
					container.removeClass('mw_image_uploader_empty');
					container.addClass('image_used');
					main_container.addClass('image_used');
					mw_obj.$el.find('.mw_image_uploader_setting').removeClass('mw_image_uploader_empty');

					var img_complete_url = MioAdminjs.siteurl + imgurl;
					if (imgurl.includes('https')) img_complete_url = imgurl;
					container.find('.mw_image_uploader_image_container img').attr('src', img_complete_url);

					// on change
					if (mw_obj.settings.change) {
						mw_obj.settings.change.call();
					}

				});

				//Open editor
				workflow.open();

			});

			// clear button
			this.$el.find('.mw_image_uploader_clear').click(function (e) {
				var container = $(this).closest('.mw_image_uploader_container');
				var main_container = $(this).closest('.mw_image_uploader');

				container.find(".mw_image_uploader_pattern").css({'background-image': ''});
				container.removeClass('image_used pattern_used');
				main_container.removeClass('image_used pattern_used');
				main_container.find('.mw_image_uploader_container').each(function () {
					if ($(this).hasClass('image_used')) main_container.addClass('image_used');
				});
				container.addClass('mw_image_uploader_empty');

				if (obj.$el.find('.mw_image_uploader_image_container').length == obj.$el.find('.mw_image_uploader_container.mw_image_uploader_empty').length) {
					obj.$el.find('.mw_image_uploader_setting').addClass('mw_image_uploader_empty');
				}

				container.find('.mw_image_uploader_image').val('');
				container.find('.mw_image_uploader_imageid').val('');
				container.find('.mw_image_uploader_pattern_val').val('');
				container.find('.mw_image_uploader_position').val('50% 50%');
				container.find('.mw_image_uploader_position_drag').css('top','50%');
				container.find('.mw_image_uploader_position_drag').css('left','50%');
				container.find('.mw_image_uploader_image_container img').attr('src', '');

				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}
				return false;
			});

			//color
			this.$el.find(".cms_color_input").minicolors({
				swatches: ve_used_colors,
				opacity: true,
				change: function (value, opacity) {
					var container = $(this).closest('.cms_transparent_color_container');
					container.find('.cms_transparent_color_transparency').val(opacity);
					container.find('.cms_color_transparency_view').html(Math.round(opacity * 100) + '%');
					if (value) {
						container.find('.cms_transparent_color_rgba').val($(this).minicolors('rgbaString'));
					} else {
						container.find('.cms_transparent_color_rgba').val('');
					}
					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			this.$el.find('select').change(function (e) {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});
			this.$el.find('.mw_switch').click(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.find('.mw_bgimage_color_filter .mw_switch').click(function () {
				if ($(this).find('input').prop("checked")) {
					obj.$el.find('.mw_bgimage_color_filter_val').show();
				} else {
					obj.$el.find('.mw_bgimage_color_filter_val').hide();
				}
			});

			this.$el.find('.mw_bgimage_cover_container .mw_switch').click(function () {
				if ($(this).find('input').prop("checked")) {
					obj.$el.find('.mw_bgimage_repeat_container').hide();
					obj.$el.find('.mw_bgimage_size_container').hide();
				} else {
					obj.$el.find('.mw_bgimage_repeat_container').show();
					obj.$el.find('.mw_bgimage_size_container').show();
				}
			});

			if (this.$el.find('.mw_image_uploader_position_drag').length) {
				this.$el.find('.mw_image_uploader_position_drag').draggable({
					containment: "parent",
					drag: function () {
						var l = Math.round(100 * parseFloat($(this).position().left / parseFloat($(this).parent().width()))) + "%";
						var t = Math.round(100 * parseFloat($(this).position().top / parseFloat($(this).parent().height()))) + "%";

						$(this).closest('.mw_image_uploader_container').find('.mw_image_uploader_position').val(l + ' ' + t);
						// on change
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				});
			}

			// select pattern
			$('.mw_image_uploader_pattern_container').mwImageSelect({
				change: function (val) {
					var container = $(this).closest('.mw_image_uploader_container');
					var main_container = $(this).closest('.mw_image_uploader');
					var pattern = $(this).attr('data-pattern');
					container.addClass('pattern_used');
					main_container.addClass('pattern_used');
					container.removeClass('mw_image_uploader_empty');
					container.find(".mw_image_uploader_pattern").css({'background-image': 'url(' + pattern + ')'});
					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			// sliders
			this.$el.find(".mw_slider").mwSlider({
				change: function (event, ui) {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

		}

		return _MwImageUplader;

	})();

	$.fn.mwImageUplader = function (options) {
		return this.each(function (index, el) {
			el.MwImageUplader = new MwImageUplader(el, options);
		});
	};

	// toggle group
	var MwToggleGroup = (function (element, settings) {

		function _MwToggleGroup(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.mw_toggle_group_head').click(function (e) {
				if (obj.$el.hasClass("mw_toggle_group_close"))
					obj.$el.find('.mw_toggle_group_content').slideDown();
				else obj.$el.find('.mw_toggle_group_content').slideUp();
				obj.$el.toggleClass("mw_toggle_group_close");

				var checkBox = obj.$el.find('.mw_toggle_group_head input');
				if (checkBox.length) {
					var isChecked = checkBox.prop("checked");
					checkBox.prop("checked", !isChecked);

					if (obj.$el.hasClass("mw_toggle_group_status_switch"))
					{
						var switchVal;
						if (checkBox.prop("checked")) {
							switchVal = obj.$el.find('.mw_switch_container').data('true');
						} else {
							switchVal = obj.$el.find('.mw_switch_container').data('false');
						}
						obj.$el.find('.mw_status_switch_val').val(switchVal);
					}

					if (checkBox.prop("checked")) {
						var val = 1;
					} else {
						var val = 0;
					}

					// on change
					if (obj.settings.change) {
						obj.settings.change.call(this, val);
					}
				}
				e.preventDefault();
			});
		}

		return _MwToggleGroup;

	})();

	$.fn.mwToggleGroup = function (options) {
		return this.each(function (index, el) {
			el.MwToggleGroup = new MwToggleGroup(el, options);
		});
	};

	// tabs
	var MwTabs = (function (element, settings) {

		function _MwTabs(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// clear button
			this.$el.find('a').click(function (e) {

				var target = $(this).attr('href');
				var group = $(this).attr('data-group');
				$("." + group + "_tab a").removeClass("active");
				$(this).addClass("active");
				$("." + group + "_container").hide();
				$(target).show();
				if ($(target + '_radio').length) {
					$(target + '_radio').prop('checked', true);

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
				return false;
			});

		}

		return _MwTabs;

	})();

	$.fn.mwTabs = function (options) {
		return this.each(function (index, el) {
			el.MwTabs = new MwTabs(el, options);
		});
	};

	// mioweb slider
	var MwSlider = (function (element, settings) {

		function _MwSlider(element, settings) {

			this.$el = $(element);

			var unit = 'px';
			var max = parseFloat(this.$el.attr('data-max'));
			var slider_max = max;
			var def = this.$el.attr('data-default');

			if (def) def = parseFloat(def);

			if (this.$el.get(0).hasAttribute('data-unit')) unit = this.$el.attr('data-unit');
			if (unit == '%') slider_max = 100;

			this.defaults = {
				change: null,
				val: this.$el.attr('data-val'),
				min: parseFloat(this.$el.attr('data-min')),
				max: max,
				step: parseFloat(this.$el.attr('data-step')),
				unit: unit,
				default: def,
				action: 'slide'
			};

			this.settings = $.extend({}, this, this.defaults, settings);
			var obj = this;

			this.$el.find('.mw_slider_container').slider({
				value: obj.settings.val,
				min: obj.settings.min,
				max: slider_max,
				step: obj.settings.step,
				slide: function (event, ui) {

					obj.$el.find('.cms_slider_val').val(ui.value).trigger('change');

					if (obj.settings.action == 'slide' && obj.settings.change) {
						obj.settings.change.call();
					}
				},
				stop: function (event, ui) {
					if (obj.settings.action == 'stop' && obj.settings.change) {
						obj.settings.change.call();
					}
				}

			});

			this.$el.find('input.cms_slider_val').change(function () {

				obj.$el.find('.mw_slider_container').slider('value', obj.getWorkVal());

				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});
			this.$el.find('input.cms_slider_val').keypress(function (event) {
				var keycode = (event.keyCode ? event.keyCode : event.which);
				if (keycode == '13') {
					$(this).blur();
					return false;
				}
			});

			this.$el.find('.mw_slider_value_up').click(function (e) {
				var val = obj.getWorkVal(true);
				var newVal = val + obj.settings.step;
				if (obj.settings.step == 0.1) newVal = newVal.toFixed(1);

				obj.$el.find('.cms_slider_val').val(newVal).trigger('change');
				obj.$el.find('.mw_slider_container').slider('value', newVal);

				if (obj.settings.change) {
					obj.settings.change.call();
				}

				e.preventDefault();
			});
			this.$el.find('.mw_slider_value_down').click(function (e) {
				var val = obj.getWorkVal(true);
				var newVal = val;

				if (newVal != obj.settings.min) newVal = newVal - obj.settings.step;
				if (obj.settings.step == 0.1) newVal = newVal.toFixed(1);

				obj.$el.find('.cms_slider_val').val(newVal).trigger('change');
				obj.$el.find('.mw_slider_container').slider('value', newVal);

				if (obj.settings.change) {
					obj.settings.change.call();
				}
				e.preventDefault();
			});

			this.$el.find('.mw_slider_unit_container input').change(function () {
				var val = $(this).val();
				var new_value = '';
				if (val == obj.settings.unit) new_value = obj.settings.val;
				if (val == '%') {
					var newsetting = {max: 100, min: 0, value: new_value};
				} else {
					var newsetting = {max: obj.settings.max, min: obj.settings.min, value: new_value};
				}
				obj.$el.find('.mw_slider_container').slider(newsetting);
				obj.$el.find('input.cms_slider_val').val(newsetting.value);
				obj.$el.find('.mw_slider_unit').removeClass('mw_slider_unit_a');
				obj.$el.find('.mw_slider_unit input:checked').closest('.mw_slider_unit').addClass('mw_slider_unit_a');
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});


		}

		return _MwSlider;

	})();

	MwSlider.prototype.getWorkVal = function (parse = false) {
		var val = this.$el.find('.cms_slider_val').val();
		if (val == '') val = this.settings.default;
		if (parse) {
			if (val) val = parseFloat(val);
			else val = this.settings.min;
		}
		return val;
	}

	$.fn.mwSlider = function (options) {
		return this.each(function (index, el) {
			el.MwSlider = new MwSlider(el, options);
		});
	};

	// mioweb border
	var MwBorder = (function (element, settings) {

		function _MwBorder(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".mw_hidden_setting").mwHiddenSetting();

			// sliders
			this.$el.find(".mw_slider").mwSlider({
				change: function (event, ui) {

					var size = ~~parseInt(obj.$el.find(".cms_slider_val").val());
					obj.$el.find(".mw_hidden_setting_label_size").html(size+'px');
					if(size>0) obj.$el.find(".mw_hidden_setting_label_border").addClass('seted');
					else obj.$el.find(".mw_hidden_setting_label_border").removeClass('seted');

					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			this.$el.find("select").change(function () {

				obj.$el.find(".mw_hidden_setting_label_border hr").css('border-style',$(this).val());

				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});
			//color
			this.$el.find(".cms_color_input_notransparent").minicolors({
				swatches: ve_used_colors,
				position: 'bottom right',
				change: function (value, opacity) {

					// change hidden setting label
					obj.$el.find(".mw_hidden_setting_label_color span").css('background-color', value);

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});
			//color
			this.$el.find(".cms_color_input_transparent").minicolors({
				swatches: ve_used_colors,
				opacity: true,
				position: 'bottom right',
				change: function (value, opacity) {
					var container = $(this).closest('.cms_border_set_color');
					container.find('.cms_color_transparency').val(opacity);
					container.find('.cms_color_transparency_view').html(Math.round(opacity * 100) + '%');
					if (value) {
						container.find('.cms_color_rgba').val($(this).minicolors('rgbaString'));
						obj.$el.find(".mw_hidden_setting_label_color span").css('background-color', value);
					} else {
						container.find('.cms_color_rgba').val('');
						obj.$el.find(".mw_hidden_setting_label_color span").css('background-color', '');
					}
					// change hidden setting label
					obj.$el.find(".mw_hidden_setting_label_color span").css('background-color', value);

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});


		}

		return _MwBorder;

	})();

	$.fn.mwBorder = function (options) {
		return this.each(function (index, el) {
			el.MwBorder = new MwBorder(el, options);
		});
	};

	// shape divider
	var MwShapeDivider = (function (element, settings) {

		function _MwShapeDivider(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.mw_shape_divider_setting_open').click(function () {
				if (obj.$el.hasClass("mw_shape_divider_setting_opened")) {
					obj.$el.find('.cms_shape_divider_shape_setting').slideUp();
				} else {
					obj.$el.find('.cms_shape_divider_shape_setting').slideDown();
				}
				obj.$el.toggleClass('mw_shape_divider_setting_opened');
			});

			this.$el.find('.cms_shape_divider_show .mw_switch').click(function () {
				if ($(this).find('input').prop("checked")) {
					obj.$el.find('.cms_shape_divider_setting').show();
				} else {
					obj.$el.find('.cms_shape_divider_setting').hide();
				}
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});
			this.$el.find('.cms_shape_divider_flip .mw_switch').click(function () {
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			// select shape
			this.$el.find('.cms_shape_divider_shape').mwImageSelect({
				items: 'icon',
				change: function (val) {
					if (obj.settings.change) {
						obj.settings.change.call();
					}

				}
			});

			// sliders
			this.$el.find(".mw_slider").mwSlider({
				change: function (event, ui) {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});
			//color
			this.$el.find(".cms_color_input").minicolors({
				swatches: ve_used_colors,
				change: function (value, opacity) {
					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

		}

		return _MwShapeDivider;

	})();

	$.fn.mwShapeDivider = function (options) {
		return this.each(function (index, el) {
			el.MwShapeDivider = new MwShapeDivider(el, options);
		});
	};

	// background color
	var MwBackgroundColor = (function (element, settings) {

		function _MwBackgroundColor(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;
			var use_opacity;

			if (this.$el.attr('data-opacity') == '1') use_opacity = true;
			else use_opacity = false;

			this.$el.find('.cms_background_color_input').minicolors({
				opacity: use_opacity,
				format: 'hex',
				swatches: ve_used_colors,
				change: function (value, opacity) {

					var container = $(this).closest('.cms_background_color');
					container.find('.cms_color_transparency').val(opacity);
					if (use_opacity) container.find('.cms_color_transparency_view').html(Math.round(opacity * 100) + '%');

					if (value)
						container.find('.cms_color_rgba').val($(this).minicolors('rgbaString'));
					else
						container.find('.cms_color_rgba').val('');

					if (obj.settings.change) {
						obj.settings.change.call(obj);
					}
				}
			});
			this.$el.find('.mw_switch').click(function () {
				if ($(this).find('input').prop("checked")) {
					obj.$el.find('.cms_background_end_color').show();
					obj.$el.addClass('background_color_field_container_wg');
				} else {
					obj.$el.find('.cms_background_end_color').hide();
					obj.$el.find('.cms_background_end_color input').val('');
					obj.$el.removeClass('background_color_field_container_wg');
					obj.$el.find('.cms_background_end_color .minicolors-input-swatch .minicolors-swatch-color').css('background', 'transparent');
				}

				if (obj.settings.change) {
					obj.settings.change.call(obj);
				}
			});


		}

		return _MwBackgroundColor;

	})();

	$.fn.mwBackgroundColor = function (options) {
		return this.each(function (index, el) {
			el.MwBackgroundColor = new MwBackgroundColor(el, options);
		});
	};

	// date picker
	var MwDatePicker = (function (element, settings) {

		function _MwDatePicker(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".cms_datepicker").datepicker({
				dateFormat: "dd.mm.yy",
				onClose: function (date, ui) {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});
			$.datepicker.setDefaults($.datepicker.regional["cs"]);

			this.$el.find("select").change(function () {
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

		}

		return _MwDatePicker;

	})();

	$.fn.mwDatePicker = function (options) {
		return this.each(function (index, el) {
			el.MwDatePicker = new MwDatePicker(el, options);
		});
	};

	// google map setting
	var MwGoogleMapSetting = (function (element, settings) {

		function _MwGoogleMapSetting(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.on('click', '.add_api_connection', function () {

				$(this).mwOpenApiConnectModal({
					onconnect: function (data) {
						console.log(data);
						obj.$el.find('.mw_google_map_setting_container').show();
						$('.cms_show_group_ve_style_google_map').show();
						obj.$el.find('.mw_api_connect_button_container').hide();
					}
				});

				return false;
			});

			// sliders
			this.$el.find(".mw_slider").mwSlider({
				change: function (event, ui) {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			this.$el.find('.mw_gm_setting_address input').change(function () {
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			var autocomplete;

			autocomplete = new google.maps.places.Autocomplete(
				/** @type {HTMLInputElement} */(document.getElementById('mw_gm_autocomplete')),
				{types: ['geocode']});
			google.maps.event.addListener(autocomplete, 'place_changed', function () {
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});


		}

		return _MwGoogleMapSetting;

	})();

	$.fn.mwGoogleMapSetting = function (options) {
		return this.each(function (index, el) {
			el.MwGoogleMapSetting = new MwGoogleMapSetting(el, options);
		});
	};

	// mioweb hidden setting
	var MwHiddenSetting = (function (element) {

		function _MwHiddenSetting(element) {

			this.$el = $(element);

			var obj = this;
			//open
			this.$el.find(".mw_hidden_setting_open").click(function () {
				if (obj.$el.hasClass("mw_hidden_setting_opened")) {
					obj.$el.find('.mw_hidden_setting_container').slideUp();
				} else {
					obj.$el.find('.mw_hidden_setting_container').slideDown();
				}
				obj.$el.toggleClass('mw_hidden_setting_opened');
				return false;
			});

		}

		return _MwHiddenSetting;

	})();

	$.fn.mwHiddenSetting = function () {
		return this.each(function (index, el) {
			el.MwHiddenSetting = new MwHiddenSetting(el);
		});
	};

	// mioweb menu
	var MwMenu = (function (element, settings) {

		function _MwMenu(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".open_menuselect_editor").click(function(){
				$(this).mwModal({
					class: 'mw_modal_size_wide',
					id: 'mw_modal_menu_edit',
					onopen: function (modal) {

						var id = obj.$el.find(".ve_menuselect_selector").val();

						$.post(ajaxurl, {"action": "open_single_menu_setting", "menu_id": id}, function (data) {

							modal.addContent(data);

							modal.$modal.find('.mw_menu_manager_container').mwMenuManager({
								edited: function () {
									if (obj.settings.change) {
										obj.settings.change.call();
									}
								}
							});

						});

					},
					onsave: function (modal) {

						var formdata = modal.getFormData();
						$.post(ajaxurl, 'action=save_menu_setting&' + formdata, function (data) {
							modal.close();
							if (obj.settings.change) {
								obj.settings.change.call();
							}
						})


						return false;
					}
				});
			});

			this.$el.find(".create_menuselect_editor").click(function(){
				$(this).mwModal({
					but_text: texts.create_menu,
					onopen: function (modal) {

						$.post(ajaxurl, {"action": "open_create_menu"}, function (data) {

							modal.addContent(data);

						});

					},
					onsave: function (modal) {

						var formdata = modal.getFormData();

						if (modal.checkForm()) {
							var name = $('#add_new_menu_name').val();
							if (name) {
								$.post(ajaxurl, {"action": "ve_create_new_menu", "name": name}, function (data) {
									if (data.content == 'false') {
										$('#add_new_menu_name').addClass('cms_required_alert');
										$('#add_new_menu_name').after('<div class="cms_error_message">' + texts.menu_conflict + '</p>');
										modal.removeLoading();
									} else {
										//modal.changeButtonText(texts.save);

										obj.$el.find('.ve_menuselect_selector').append('<option value="' + data.id + '">' + data.title + '</option>').val(data.id);
										obj.$el.addClass('selected');

										modal.close();
										obj.$el.find(".open_menuselect_editor").click();

										if (obj.settings.change) {
											obj.settings.change.call();
										}

										$('.mw_menu_manager_container').mwMenuManager({});

									}
								});
							}
						}

						return false;
					}
				});
			});

			this.$el.find(".ve_menuselect_selector").change(function () {

				if ($(this).val() == "") obj.$el.removeClass('selected');
				else obj.$el.addClass('selected');

				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.find(".delete_menuselect_editor").mwConfirm({
				text: texts.delete_menu_confirm,
				onconfirm: function (confirm) {
					var id = obj.$el.find(".ve_menuselect_selector").val();
					var newid;

					$.post(ajaxurl, {"action": "delete_menu", 'page_id': id}, function (data) {
						obj.$el.find('.ve_menuselect_selector option:selected').removeAttr('selected').prev('option').attr('selected', 'selected');
						obj.$el.find(".ve_menuselect_selector option[value=" + id + "]").remove();
						newid = obj.$el.find('.ve_menuselect_selector').val();
						if (newid == '') obj.$el.removeClass('selected');

						if (obj.settings.change) {
							obj.settings.change.call();
						}
					});
					return false;
				}
			});


		}

		return _MwMenu;

	})();

	$.fn.mwMenu = function (options) {
		return this.each(function (index, el) {
			el.MwMenu = new MwMenu(el, options);
		});
	};

	// mioweb menu manager
	var MwMenuManager = (function (element, settings) {

		function _MwMenuManager(element, settings) {

			this.$el = $(element);

			this.defaults = {
				edited: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.init();

		}

		return _MwMenuManager;

	})();

	MwMenuManager.prototype.init = function () {
		var obj = this;

		this.$el.on('click', '.ve_sortable_handler', function () {
			return false;
		});

		this.$el.on('click', '.ve_item_head', function () {
			var container = $(this).closest('.ve_item_container');
			if (container.hasClass("open")) {
				container.find('.ve_item_body').slideUp();
			} else {
				container.find('.ve_item_body').slideDown();
			}
			container.toggleClass('open');
			return false;
		});

		// delete menu item
		this.$el.on("click", ".ve_delete_setting", function () {
			var el = $(this);
			$(this).mwConfirm({
				text: texts.delete_item_confirm,
				show: true,
				onconfirm: function (confirm) {
					el.closest('.ve_item_container').find('.ve_item_body').remove();
					el.closest('.ve_item_container').slideUp('slow', function () {
						$(this).remove();
					});

				}
			});
			return false;
		});

		// switch url
		this.$el.on("click", ".mw_menu_manager_item_switch_url", function () {
			if ($(this).find('input').prop("checked")) {
				$(this).closest('.mw_menu_manager_item_set').addClass('mw_menu_item_type_custom');
			} else {
				$(this).closest('.mw_menu_manager_item_set').removeClass('mw_menu_item_type_custom');
			}
		});

		// add menu item
		this.$el.find(".mw_add_menu_item").click(function () {

			var id = $(this).attr('data-id');
			var elemContainer = obj.$el.find('.ve_menu_manager_list');

			$(this).attr('data-id', parseInt(id) + 1);

			var new_item = $('<li/>');
			new_item.attr('class', 've_nestedsortable__item');
			new_item.attr('id', 've_item_' + id);

			elemContainer.append(
				'<div class="ve_item_container mw_loading"></div>'
			);

			obj.$el.find('.ve_menu_manager_list').append(new_item);

			$.post(ajaxurl, {"action": "ve_generate_edit_menu_item", "id": id}, function (data) {
				$(".ve_item_container.mw_loading").remove();
				$("#ve_item_" + id).html(data);
			});

			return false;
		});

		createSortedItems(true);
	}

	$.fn.mwMenuManager = function (options) {
		return this.each(function (index, el) {
			el.MwMenuManager = new MwMenuManager(el, options);
		});
	};

	// multielementmanager
	var MwMultiElementManager = (function (element, settings) {

		function _MwMultiElementManager(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
				add: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);
			this.open_style = this.$el.find('.ve_multielement_container').attr('data-open');

			//this.name=this.$el.attr('data-name');

			var obj = this;

			this.$el.find('.ve_add_multielement').click(function () {

				var id = $(this).attr('data-id');
				var settings = $(this).attr('data-set');
				var elemContainer = obj.$el.find('.ve_multielement_container');

				$(this).attr('data-id', parseInt(id) + 1);

				elemContainer.append(
					'<div class="ve_multielement_loading ve_item_container mw_loading"></div>'
				);
				$.post(
					ajaxurl,
					{
						"action": "cms_generate_multielement",
						"id": id,
						"setting": settings,
					},
					function (data) {
						elemContainer.find('.ve_multielement_loading').replaceWith(data);
						var container = obj.$el.find('.added');
						if (obj.open_style == 'under') {
							container.find('.ve_item_body').slideDown();
						}

						$(container.find('.mw_scroll')).each(function () {
							const ps = new PerfectScrollbar($(this)[0], {
								suppressScrollX: true,
								wheelPropagation: false,
							});
						});

						container.addClass('open').removeClass('added');
						obj.addSorting();
						// on add
						if (obj.settings.add) {
							obj.settings.add.call(this, '.ve_multielement-' + id);
						}
					}
				);
				return false;
			});

			this.$el.on('click', '.ve_delete_setting', function () {
				var el = $(this);
				$(this).mwConfirm({
					text: texts.delete_item_confirm,
					show: true,
					onconfirm: function (confirm) {
						el.closest('.ve_item_container').find('.ve_item_body').remove();
						el.closest('.ve_item_container').slideUp('slow', function () {
							$(this).remove();
						});
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				});
				return false;
			});
			this.$el.on('click', '.ve_item_head', function (e) {
				if (!$(e.target).hasClass('ve_item_head_link')) {
					if (obj.open_style == 'under') {
						var container = $(this).closest('.ve_item_container');
						//$(this).closest('.ve_item_container').find('.ve_item_body').toggle();
						if (container.hasClass("open")) {
							container.find('.ve_item_body').slideUp();
						} else {
							container.find('.ve_item_body').slideDown();
						}
						container.toggleClass('open');
					} else {
						$(this).closest('.ve_item_container').find('.ve_item_body').show().animate({left: "0"}, {
							duration: 200,
							queue: false
						});
					}
					return false;
				}
			});
			this.$el.on('click', '.ve_item_close', function () {
				if (obj.open_style == 'under') {
					$(this).closest('.ve_item_container').removeClass('open');
				} else {
					$(this).closest('.ve_item_body').animate({left: "-250px"}, {
						duration: 200,
						queue: false
					}, function () {
						$(this).hide()
					});
				}
				return false;
			});

			this.$el.on('click', '.ve_sortable_handler', function () {
				return false;
			});

			this.addSorting();

		}

		return _MwMultiElementManager;

	})();

	MwMultiElementManager.prototype.addSorting = function () {
		var obj = this;
		this.$el.find('.ve_sortable_items').sortable(
			$.extend({
					handle: '.ve_sortable_handler',
					items: ".ve_sortable_item"
				},
				{
					placeholder: "ve_multielement_item_placeholder",
					forcePlaceholderSize: true,
					/*
					start: function (event, ui) {
						ui.item.find('.ve_item_body').hide();
						ui.item.removeClass('open');
					},*/
					stop: function (event, ui) {

						var i = 0;
						var regex = new RegExp("\[+[0-9]+\]");
						obj.$el.find('.ve_item_body').each(function () {
							$(this).find('[name]').each(function () {

								var newname = $(this).attr('name').replace(regex, '[' + i + ']');
								$(this).attr('name', newname);
							});
							i++;
						});

						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				})
		);
	}

	$.fn.mwMultiElementManager = function (options) {
		return this.each(function (index, el) {
			el.MwMultiElementManager = new MwMultiElementManager(el, options);
		});
	};

	// code list

	var MwCodeList = (function (element, settings) {

		function _MwCodeList(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
				add: null,
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
					if (obj.settings.add) {
						obj.settings.add.call(this, selector);
					}
				}
			});

			// change event
			this.$el.on('keyup','.mw_code_list_title_input',function(){
				$(this).closest('.ve_item_container').find('.mw_codes_item_title').html($(this).val());
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.on('change', '.mw_code_list_checkbox_disabled', function () {
				const $statusEl = $(this).closest('.ve_item_container').find('.mw_codes_item_disabled_status');

				if ($statusEl.length) {
					const checkboxInput = $(this).find('input');
					const disabledText = $statusEl.data('disabledText') || '';
					const disabledStatus = checkboxInput.prop('checked') ? disabledText : '';

					$statusEl.html(disabledStatus);
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			this.$el.on('change','.mw_code_list_select_type',function(){
				$(this).closest('.ve_item_container').find('.mw_codes_item_type').html($(this).find('option:selected').data('title'));
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.on('change','.mw_code_list_select_position',function(){
				$(this).closest('.ve_item_container').find('.mw_codes_item_position').html($(this).find('option:selected').data('title'));
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.on('change','textarea',function(){
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			// switch
			this.$el.find('.mw_switch input').change(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

		}

		return _MwCodeList;

	})();

	$.fn.mwCodeList = function (options) {
		return this.each(function (index, el) {
			el.MwCodeList = new MwCodeList(el, options);
		});
	};

	// simple feature
	var MwSimpleFeatures = (function (element, settings) {

		function _MwSimpleFeatures(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
				add: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			obj.$el.find('.cms_upload_file_container').mwUploadFile({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call(this);
					}
				}
			});

			// add feature
			this.$el.find(".ve_add_simple_feature").click(function () {
				var id = $(this).attr('data-id');
				var tagid = $(this).attr('data-tagid');
				var tagname = $(this).attr('data-name');
				var fields = $(this).attr('data-fields');
				var link = $(this);

				$(this).attr('data-id', parseInt(id) + 1);

				obj.$el.find('.ve_features_container').append('<div class="ve_item_feature_' + id + ' ve_item_feature_container mw_flex_field"><div class="miocms_loading"></div></div>');
				$.post(ajaxurl, {
					"action": "ve_generate_simple_feature_ajax",
					"id": id,
					"tagid": tagid,
					"tagname": tagname,
					"fields": fields
				}, function (data) {
					var featureItem = obj.$el.find('.ve_item_feature_' + id);
					featureItem.html(data);
					obj.addSorting();

					featureItem.find('.cms_upload_file_container').mwUploadFile({
						change: function () {
							if (obj.settings.change) {
								obj.settings.change.call(this);
							}
						}
					});

				});
				return false;
			});
			// edit feature
			this.$el.on('change', '.mw_input', function () {
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});
			// delete feature
			this.$el.on('click', '.ve_delete_feature', function () {
				var text = $(this).attr('title');
				var self = $(this).closest('.ve_item_feature_container');
				$(this).mwConfirm({
					text: text,
					show: true,
					onconfirm: function (confirm) {
						self.remove();
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				});
				return false;
			});


			this.addSorting();

		}

		return _MwSimpleFeatures;

	})();

	MwSimpleFeatures.prototype.addSorting = function () {
		var obj = this;
		this.$el.find('.ve_sortable_items').sortable(
			$.extend({
					handle: '.ve_sortable_handler',
					items: ".ve_item_feature_container"
				},
				{
					placeholder: "ve_simple_feature_placeholder",
					forcePlaceholderSize: true,
					stop: function (event, ui) {

						var i = 0;
						var regex = new RegExp("\[+[0-9]+\](?!.*?\[+[0-9]+\])", "g");
						obj.$el.find('.ve_item_feature_container').each(function () {
							$(this).find('[name]').each(function () {
								var newname = $(this).attr('name').replace(regex, '[' + i + ']');
								$(this).attr('name', newname);
							});
							i++;
						});

						if (obj.settings.change) {
							obj.settings.change.call();
						}
					}
				})
		);
	}

	$.fn.mwSimpleFeatures = function (options) {
		return this.each(function (index, el) {
			el.MwSimpleFeatures = new MwSimpleFeatures(el, options);
		});
	};

	//interval table
	var MwIntervalTable = (function (element, settings) {

		function _MwIntervalTable(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
				add: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			var table = this.$el.find('.mw_interval_table');

			const max_name = table.attr('data-max-name');
			const int_val_name = table.attr('data-int-val-name');
			const int_name = table.attr('data-int-name');
			const decimals = table.attr('data-decimals');
			const max_unit = table.attr('data-max-unit');
			const int_val_unit = table.attr('data-int-val-unit');
			const confirm = table.attr('data-confirm');
			const name = table.attr('data-name');

			this.$el.find('.mw_dropdown_button').mwDropdownButton({});

			const fnEdit = function () {

				const id = $(this).attr('data-id');
				const edit = $(this).hasClass('mw_interval_edit');
				var intervals = obj.getIntervals();

				$(this).mwModal( {
					onopen: function (modal) {

						const max_val = edit ? intervals[id].max_val : '';
						const int_val = edit ? intervals[id].int_val : '';

						$.post(
							ajaxurl,
							{
								'action': 'cms_add_or_change_interval_form',
								'edit': edit,
								'max_name': max_name,
								'int_name': int_name,
								'int_val_name': int_val_name,
								'max_unit': max_unit,
								'int_val_unit': int_val_unit,
								'decimals': decimals,
								'max_val': max_val,
								'int_val': int_val,
							},
							function (data) {
								modal.addContent(data);
							}
						);
					},
					onsave: function (modal) {

						let params = new URLSearchParams(modal.getFormData());

						if (edit) {
							intervals[id].max_val = params.get('max_val');
							intervals[id].int_val = params.get('int_val');
						} else {
							intervals.push({max_val: params.get('max_val'), int_val: params.get('int_val')});
						}

						$.post(
							ajaxurl,
							{
								'action': 'cms_generate_intervals',
								'content': intervals,
								'max_name': max_name,
								'int_val_name': int_val_name,
								'max_unit': max_unit,
								'int_val_unit': int_val_unit,
								'decimals': decimals,
								'name': name,
							},
							function (data) {
								if (data.success) {
									table.find('tbody').html(data.content);
									table.find('.mw_dropdown_button').mwDropdownButton({});
									table.find('.mw_interval_edit').click(fnEdit);
									table.find('.mw_interval_delete').click(fnDel);
									modal.close();
								} else {
									if (!edit) {
										intervals.pop();
									}
									modal.removeLoading();
									modal.addMessage(data);
								}
							}
						);

						return false;
					}
				});
				return false;
			};

			const fnDel = function () {

				const id = $(this).attr('data-id');

				$(this).mwConfirm({
					text: confirm,
					show: true,
					onconfirm: function (confirm) {
						var intervals = obj.getIntervals();
						intervals.splice(id, 1);
						$.post(
							ajaxurl,
							{
								'action': 'cms_generate_intervals',
								'content': intervals,
								'max_name': max_name,
								'int_val_name': int_val_name,
								'max_unit': max_unit,
								'int_val_unit': int_val_unit,
								'decimals': decimals,
								'name': name,
							},
							function (data) {

								table.find('tbody').html(data.content);
								table.find('.mw_dropdown_button').mwDropdownButton({});
								table.find('.mw_interval_edit').click(fnEdit);
								table.find('.mw_interval_delete').click(fnDel);
							}
						);
					}
				});
				return false;
			}

			// add or edit interval
			this.$el.find('.mw_interval_add, .mw_interval_edit').click(fnEdit);

			// delete interval
			this.$el.find('.mw_interval_delete').click(fnDel);

		}

		return _MwIntervalTable;

	})();

	MwIntervalTable.prototype.getIntervals = function () {
		var intervals = [];
		this.$el.find('.mw_interval_table').find('tbody tr').each(function (i, el) {
			intervals.push({max_val: $(el).find('.max_val').val(), int_val: $(el).find('.int_val').val()});
		});
		return intervals;
	}

	$.fn.mwIntervalTable = function (options) {
		return this.each(function (index, el) {
			el.MwIntervalTable = new MwIntervalTable(el, options);
		});
	};

	//a/b testing statistics
	var MwAbStatisticks = (function (element, settings) {

		function _MwAbStatisticks(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// reset page statistics
			this.$el.find("#ve_reset_page_statistics").click(function () {
				var id = $(this).attr('data-id');
				$(this).mwConfirm({
					text: texts.ab_reset,
					but_text: texts.ab_reset_but,
					show: true,
					onconfirm: function (confirm) {
						confirm.$el.closest('.mw_onedit_action').html(texts.ab_nodata);
						$.post(ajaxurl, {"action": "ve_reset_page_statistics", "post_id": id}, function () {
						});
					}
				});
				return false;
			});

		}

		return _MwAbStatisticks;

	})();

	$.fn.mwAbStatisticks = function (options) {
		return this.each(function (index, el) {
			el.MwAbStatisticks = new MwAbStatisticks(el, options);
		});
	};

	// mioweb gallery
	var MwGallery = (function (element, settings) {

		function _MwGallery(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			//this.image_list = this.$el.find( '.cms_image_gallery__wrap' );

			var obj = this;

			//Image Gallery sorting
			this.$el.find('.cms_image_gallery__wrap').sortable({
				placeholder: "cms_image_gallery__item__placeholder",
				stop: function (event, ui) {
					var name = obj.$el.find('.cms_upload_gallery_button').attr('data-name');
					var i = 0;
					obj.$el.find('.cms_image_gallery__wrap input').each(function (index) {
						$(this).attr('name', name + '[' + i + ']');
						i++;
					});
					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			//Upload gallery
			this.$el.find('.cms_upload_gallery_button').click(function (e) {
				e.preventDefault();

				var workflow = wp.media.editor.get('mio_gallery_upload');

				//If WF already exist, just open modal
				if (typeof workflow !== 'undefined') {
					workflow.open();
					return;
				}

				//Create new WF and bind events
				workflow = wp.media.editor.add('mio_gallery_upload', {
					frame: 'post',
					state: 'insert',
					title: wp.media.view.l10n.addMedia,
					multiple: true,
					library: {type: 'image'},
				});

				workflow.on('open', function () {
					jQuery('.media-frame').addClass('hide-menu');
				});

				//Insert new images to image list
				workflow.on('insert', function (selection) {

					var name = $('.cms_upload_gallery_button').attr('data-name');
					var editable = $('.cms_upload_gallery_button').attr('data-editable');

					//var image_list = obj.$el.find( '.cms_image_gallery__wrap' ); dont work second time
					var image_list = $('.cms_image_gallery__wrap');

					var state = workflow.state();
					selection = selection || state.get('selection');

					if (!selection)
						return;

					//show image list
					$('.cms_image_gallery').removeClass('cms_nodisp');

					//add new images to the end of list
					$.each(selection.models, function (index, image) {
						var new_image = $('<img/>');
						var new_image_url;

						if (typeof image.attributes.sizes.thumbnail !== 'undefined') new_image_url = image.attributes.sizes.thumbnail.url;
						else new_image_url = image.attributes.sizes.full.url;

						new_image.attr('src', new_image_url);

						var but_container = $('<div/>');
						but_container.addClass('mw_image_uploader_control');

						var close_button = $('<a/>');
						var close_button_text = (typeof MioAdminjs !== 'undefined') ? MioAdminjs.image_gallery_delete_image : 'Odstranit obrázek';
						close_button.attr('title', close_button_text);
						close_button.addClass('mw_icon cms_image_gallery__item__close_button');
						close_button.html('<svg role="img"><use xlink:href="' + texts.loading_icons_url + 'symbol-defs.svg#icon-trash-2"></use></svg>')
						if (editable == '1') {
							var edit_button = $('<a/>');
							var edit_button_text = (typeof MioAdminjs !== 'undefined') ? MioAdminjs.image_gallery_edit_image : 'Upravit obrázek';
							edit_button.attr('title', edit_button_text);
							edit_button.addClass('mw_icon cms_image_gallery__item__edit_button');
							edit_button.html('<svg role="img"><use xlink:href="' + texts.loading_icons_url + 'symbol-defs.svg#icon-edit-2"></use></svg>')
						} else {
							var edit_button = '';
						}

						but_container.append(edit_button);
						but_container.append(close_button);

						var input = $('<input/>');
						input.attr('type', 'hidden');
						input.attr('name', name + '[]');
						input.attr('value', image.attributes.id);
						input.attr('style', 'display: none;');

						var new_element = $('<div/>');
						new_element.addClass('cms_image_gallery__item');
						new_element.prepend(new_image);
						new_element.append(but_container);
						new_element.append(input);


						image_list.append(new_element);

					});

					var i = 0;
					image_list.find('input').each(function (index) {
						$(this).attr('name', name + '[' + i + ']');
						i++;
					});


					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}

				});

				//Open editor
				workflow.open();

			});

			this.$el.on('click', '.cms_image_gallery__item__close_button', function (evt) {

				var item_wraper = $(this).closest('.cms_image_gallery__item');

				item_wraper.addClass('is-deleted');
				setTimeout(function () {
					item_wraper.remove();
					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}, 300);

				evt.preventDefault();
			});

			this.$el.on('click', '.cms_image_gallery__item__edit_button', function (evt) {
				evt.preventDefault();

				var clicked_button = $(this),
					attachment_id = parseInt(clicked_button.closest('.cms_image_gallery__item').find('input').val());


				var query_frame = wp.media.query({post__in: [attachment_id]});

				var spinner = $('.cms_image_gallery__spinner');
				spinner.show();

				query_frame.more().done(function () {

					spinner.hide();

					var attachment = this.first();

					attachment.set('attachment_id', attachment.get('id')); //WP 'bug'

					var media_frame = Object.create(wp.media({
						title: 'test',
						frame: 'image',
						state: 'image-details',
						metadata: attachment.toJSON(),
						id: 'mio-image-gallery-detail-modal',
						editing: true
					}));

					media_frame.on('open', function () {
						var title_text = (typeof MioAdminjs !== 'undefined') ? MioAdminjs.image_gallery__image_detail__heading : '';
						$('.media-frame-title h1').text(title_text);
					});

					media_frame.on('update', function (attachmentObj) {

						spinner.show();

						var xhr = $.ajax({
							method: 'POST',
							url: ajaxurl,
							data: {
								action: 'mio_image_gallery_edit_meta',
								id: attachmentObj.id,
								caption: attachmentObj.caption,
								alt: attachmentObj.alt
							}
						});

						attachment.set('alt', attachmentObj.alt);
						attachment.set('caption', attachmentObj.caption);

						xhr.done(function () {
							spinner.hide();
						});

					});


					media_frame.open(); // finally open the frame

				});


			});


		}

		return _MwGallery;

	})();

	$.fn.mwGallery = function (options) {
		return this.each(function (index, el) {
			el.MwGallery = new MwGallery(el, options);
		});
	};

	// font selector
	var MwFontSetting = (function (element, settings) {

		function _MwFontSetting(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var selector = this.$el.find(".cms_style_selector");
			var obj = this;

			this.$el.find(".mw_hidden_setting").mwHiddenSetting();

			// choose font
			this.$el.find(".font_select a").click(function () {
				var fonttext = $(this).html();
				var font = $(this).attr('data-font');

				var weights = eval("new Array(" + $(this).data('weights') + ")");
				var options = '';
				for (i = 0; i < weights.length; ++i) {
					options += '<option value="' + weights[i].id + '" data-file="' + (weights[i].file ?? '') + '">' + weights[i].name + '</option>';
				}
				obj.$el.find(".font_weight_select").html(options);

				if (font) obj.$el.find(".cms_font_family_container").addClass('cms_font_family_selected');
				else obj.$el.find(".cms_font_family_container").removeClass('cms_font_family_selected');

				obj.$el.find(".font_selected_input").val(font);
				if (font == "") font = $(this).attr('data-text');
				obj.$el.find(".font_selected").html(font);

				// change hidden setting label
				obj.$el.find(".mw_font_label_target_font").html(font);

				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}

				return false;
			});
			// open font selector
			this.$el.find(".cms_open_font_selector").click(function () {
				selector.show();
				setTimeout(function () {
					selector.addClass('cms_style_selector_opened');
				}, 20);
				obj.$el.find(".cms_style_selector_bg").show();
				new PerfectScrollbar(obj.$el.find(".mw_scroll")[0]); // without it scrollbar has height 0
				return false;
			});

			// close font selector
			this.$el.find(".cms_close_style_selector").click(function () {
				selector.removeClass('cms_style_selector_opened');
				setTimeout(function () {
					selector.hide();
					obj.$el.find(".cms_style_selector_bg").hide();
				}, 100);
				return false;
			});

			// sliders
			this.$el.find(".mw_slider").mwSlider({
				change: function (event, ui) {

					// change hidden setting label
					if (obj.$el.find('.font_size_slider').length) {
						var size = obj.$el.find(".font_size_slider .desktop_setting input").val();
						if(size != '') size = size + 'px';
						obj.$el.find(".mw_font_label_target_size").html(size);
					}

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			//color
			this.$el.find(".cms_font_color").minicolors({
				swatches: ve_used_colors,
				change: function (value, opacity) {

					// change hidden setting label
					obj.$el.find(".mw_hidden_setting_label_color span").css('background-color', value);

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			// switch url type
			this.$el.find('.mw_switch').click(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

			// selects
			this.$el.find("select").change(function () {

				if($(this).hasClass('cms_font_use'))
				{
					var text = $(this).find('option:selected').attr('title');
					obj.$el.find(".mw_font_label_target_usefont").html(text);
				}

				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

		}

		return _MwFontSetting;

	})();

	$.fn.mwFontSetting = function (options) {
		return this.each(function (index, el) {
			el.MwFontSetting = new MwFontSetting(el, options);
		});
	};

	// image selector
	var MwImageSelect = (function (element, settings) {

		function _MwImageSelect(element, settings) {

			this.$el = $(element);

			this.defaults = {
				items: 'img', // icon
				change: null,
				init: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			if (this.$el.closest('.mw_admin_setting_container').length) this.type = 'setting';

			var selector = this.$el.find(".cms_image_selector");
			var animation;
			var obj = this;

			this.$el.find(".cms_open_image_selector").click(function () {

				obj.$el.find(".cms_image_selector_bg").show();
				obj.$el.find(".cms_image_selector_container").addClass('opened');
				new PerfectScrollbar(obj.$el.find(".mw_scroll")[0]);  // without it scrollbar has height 0
				return false;
			});
			this.$el.find(".cms_close_image_selector").click(function () {

				obj.$el.find(".cms_image_selector_container").removeClass('opened');
				obj.$el.find(".cms_image_selector_bg").hide();

				return false;
			});

			// choose image
			this.$el.find(".cms_is_item a").click(function () {
				var val = $(this).attr('data-value');
				var source;
				var hide = $(this).attr('data-hide');

				if (obj.settings.items == 'img') {
					source = $(this).find('img').attr('src');
					obj.$el.find(".cms_image_selected img").attr('src', source);
				} else if (obj.settings.items == 'icon') {
					source = $(this).html();
					obj.$el.find(".cms_image_select_container").html(source);
					obj.$el.find(".cms_icon_select_code").val(source);
				}

				obj.$el.find(".cms_is_item_active").removeClass('cms_is_item_active');
				$(this).closest('.cms_is_item').addClass('cms_is_item_active');

				obj.$el.find(".cms_image_select_val").val(val);

				obj.$el.find(".cms_image_selector_container").removeClass('opened');
				obj.$el.find(".cms_image_selector_bg").hide();

				if (hide) $(hide).hide();

				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this, val);
				}

				return false;
			});

			// on init
			if (obj.settings.init) {
				var val = obj.$el.find(".cms_image_select_val").val();
				obj.settings.init.call(this, val);
			}

		}

		return _MwImageSelect;

	})();

	$.fn.mwImageSelect = function (options) {
		return this.each(function (index, el) {
			el.MwImageSelect = new MwImageSelect(el, options);
		});
	};

	// image selector
	var MwIconSelect = (function (element, settings) {

		function _MwIconSelect(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".cms_open_style_selector").click(function () {
				var selector = obj.$el.find(".cms_style_selector");
				selector.show(); //.animate({ opacity: 1, top: "5vh" }, 200);
				setTimeout(function () {
					selector.addClass('cms_style_selector_opened');
				}, 20);
				obj.$el.find(".cms_style_selector_bg").show();
				new PerfectScrollbar(obj.$el.find(".mw_scroll")[0]);  // without it scrollbar has height 0
				return false;
			});
			this.$el.find(".cms_close_style_selector").click(function () {
				var selector = obj.$el.find(".cms_style_selector");
				var bg = obj.$el.find(".cms_style_selector_bg");
				selector.removeClass('cms_style_selector_opened');
				setTimeout(function () {
					selector.hide();
					bg.hide();
				}, 100);
				return false;
			});

			this.$el.find(".cms_icon_item").click(function () {

				var val = $(this).attr('data-value');
				var set = $(this).attr('data-set');
				var con = $(this).html();

				obj.$el.find(".cms_icon_item_active").removeClass('cms_icon_item_active');
				$(this).addClass('cms_icon_item_active');

				obj.$el.find(".cms_icon_select_container").html(con);

				obj.$el.find(".cms_icon_select_icon").val(val);
				obj.$el.find(".cms_icon_select_code").val(con);
				obj.$el.find(".cms_icon_select_icon_set").val(set);

				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this, val);
				}

				//return false;
			});

			this.$el.find(".cms_icon_select_size .mw_slider").mwSlider({
				change: function () {
					// on change
					if (obj.settings.change) {
						obj.settings.change.call(this);
					}
				}
			});
			this.$el.find(".cms_color_input").minicolors({
				swatches: ve_used_colors,
				change: function () {
					// on change
					if (obj.settings.change) {
						obj.settings.change.call(this);
					}
				}
			});
			this.$el.find('.mw_image_uploader').mwImageUplader({
				change: function () {
					// on change
					if (obj.settings.change) {
						obj.settings.change.call(this);
					}
				}
			});
			this.$el.find(".cms_icon_select_switch a").click(function () {
				var tab = $(this).attr('data-tab');

				obj.$el.find('.cms_icon_selector_container').removeClass('cms_icon_selector_container_image cms_icon_selector_container_icon').addClass('cms_icon_selector_container_' + tab);
				obj.$el.find('.cms_icon_select_tab_input').val(tab);

				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

		}

		return _MwIconSelect;

	})();

	$.fn.mwIconSelect = function (options) {
		return this.each(function (index, el) {
			el.MwIconSelect = new MwIconSelect(el, options);
		});
	};

	// Template selector (on page create)
	var MwTemplateSelector = (function (element, settings) {

		function _MwTemplateSelector(element) {

			this.$el = $(element);
			var obj = this;

			this.$el.find('.mw_category_tabs').mwTabs();

			this.$el.find(".mw_template_item_select").click(function (e) {
				$(".mw_template_item.selected").removeClass('selected');
				$(this).closest('.mw_template_item').addClass('selected').find('input').prop("checked", true);

				e.preventDefault();
			});

		}

		return _MwTemplateSelector;

	})();

	$.fn.mwTemplateSelector = function (options) {
		return this.each(function (index, el) {
			el.MwTemplateSelector = new MwTemplateSelector(el, options);
		});
	};

	// Create page fields control
	var MwPostSetField = (function (element, settings) {

		function _MwPostSetField(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.mw_post_title_field_container').mwPostTitleField({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			})

		}

		return _MwPostSetField;

	})();

	$.fn.mwPostSetField = function (options) {
		return this.each(function (index, el) {
			el.MwPostSetField = new MwPostSetField(el, options);
		});
	};

	// Create page fields control
	var MwPostTitleField = (function (element, settings) {

		function _MwPostTitleField(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".mw_post_title_field").keyup(function () {
				if (!obj.$el.find(".mw_post_slug_field").hasClass('norewrite')) {
					var url = $(this).val();
					obj.$el.find(".mw_post_slug_field").val(obj.makeSlug(url));
				}
				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.find(".mw_post_slug_field").keyup(function () {
				var url = $(this).val();
				if (url != '') $(this).addClass('norewrite');
				else $(this).removeClass('norewrite');
				$(this).val(obj.makeSlug(url));
				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			this.$el.find(".mw_post_title_field_but_edit").click(function () {
				obj.$el.find('.mw_post_title_field_slug_url').addClass('editing');
			});

			this.$el.find(".mw_post_title_field_but_ok").click(function () {
				var slug = obj.$el.find('.mw_post_slug_field').val();
				var item_id = $(this).attr('data-itemid');
				var object_id = $(this).attr('data-objectid');
				if(slug)
				{
					$.post(ajaxurl, {
						"action": "mwCheckSlug",
						"slug": slug,
						"item_id": item_id,
						"object_id": object_id,
					}, function (data) {
						obj.$el.find('.mw_post_title_field_slug span').html(data.slug);
						obj.$el.find('.mw_post_slug_field').val(data.slug);
						obj.$el.find('.mw_post_title_field_slug_url').removeClass('editing');
					});


				}
			});

		}

		return _MwPostTitleField;

	})();

	MwPostTitleField.prototype.makeSlug = function (url) {
		url = url.toLowerCase();
		var from = "ãàáäâčďẽèéëêěìíïîňõòóöôřšťůùúüûñžý";
		var to = "aaaaacdeeeeeeiiiinooooorstuuuuunzy";
		for (var i = 0, l = from.length; i < l; i++) {
			url = url.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
		}
		url = url.replace(/[^a-zA-Z0-9]+/g, '-');
		return url;
	}

	$.fn.mwPostTitleField = function (options) {
		return this.each(function (index, el) {
			el.MwPostTitleField = new MwPostTitleField(el, options);
		});
	};

	// User password
	var MwUserPassword = (function (element, settings) {

		function _MwUserPassword(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.mw_user_password_fieldtype_input').keyup(function () {

				obj.checkPasswordStrength();

				if (obj.settings.change) {
					obj.settings.change.call();
				}

			});

			this.$el.find('.mw_user_password_fieldtype_setnew').click(function () {
				obj.$el.removeClass('hide_setting');
				var pass = obj.$el.find('.mw_user_password_fieldtype_input').attr('data-password');
				obj.$el.find('.mw_user_password_fieldtype_input').val(pass);
				obj.checkPasswordStrength(pass);
			});

			this.$el.find('.mw_user_password_fieldtype_cancel').click(function () {
				obj.$el.addClass('hide_setting');
				obj.$el.find('.mw_user_password_fieldtype_strength').hide();
				obj.$el.find('.mw_user_password_fieldtype_input').attr('data-password', obj.$el.find('.mw_user_password_fieldtype_input').val());
				obj.$el.find('.mw_user_password_fieldtype_input').val('');
			});

			this.$el.find('.mw_user_password_fieldtype_hide').click(function () {
				$(this).toggleClass('mw_icon_button_active');
				if($(this).hasClass('mw_icon_button_active')) {
					obj.$el.find('.mw_user_password_fieldtype_input').attr('type','password');
				}
				else {
					obj.$el.find('.mw_user_password_fieldtype_input').attr('type','text');
				}
			});

			this.$el.find('.mw_user_password_fieldtype_generate').click(function () {
				$.post(ajaxurl, {
					"action": "mwGeneratePassword",
				}, function (content) {
					obj.$el.find('.mw_user_password_fieldtype_input').val(content);
					obj.checkPasswordStrength();
				});
			});

		}

		return _MwUserPassword;

	})();

	MwUserPassword.prototype.checkPasswordStrength = function(pass='') {

		var $strengthResult = this.$el.find('.mw_user_password_fieldtype_strength');
		if(!pass) {
			pass = this.$el.find('.mw_user_password_fieldtype_input').val();
		}
		// Reset the form & meter
	    $strengthResult.removeClass( 'short bad good strong' ).hide();

		if(pass)
		{
		    // Extend our blacklist array with those from the inputs & site data
		    blacklistArray = wp.passwordStrength.userInputDisallowedList();

		    // Get the password strength
		    var strength = wp.passwordStrength.meter( pass, blacklistArray );

		    // Add the strength meter results
		    switch ( strength ) {

		        case 2:
		            $strengthResult.addClass( 'bad' ).html( pwsL10n.bad ).show();
		            break;

		        case 3:
		            $strengthResult.addClass( 'good' ).html( pwsL10n.good ).show();
		            break;

		        case 4:
		            $strengthResult.addClass( 'strong' ).html( pwsL10n.strong ).show();
		            break;

		        case 5:
		            $strengthResult.addClass( 'short' ).html( pwsL10n.mismatch ).show();
		            break;

		        default:
		            $strengthResult.addClass( 'short' ).html( pwsL10n.short ).show();

		    }

		}
	}

	$.fn.mwUserPassword = function (options) {
		return this.each(function (index, el) {
			el.MwUserPassword = new MwUserPassword(el, options);
		});
	};

	// Imageoption

	var MwImageOption = (function (element, settings) {

		function _MwImageOption(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// choose image
			this.$el.find(".cms_image_option_item").click(function () {

				obj.$el.find(".cms_image_option_item").removeClass('cms_current_image_option_item');
				$(this).addClass('cms_current_image_option_item');
				$(this).find('input').prop("checked", true);

				if ($(this).find('input').hasClass('cms_image_option_custom_val')) {
					obj.$el.find('.cms_image_option_custom_container').show();
				} else {
					obj.$el.find('.cms_image_option_custom_container').hide();
				}

				// on change
				if (obj.settings.change) {
					obj.settings.change.call(obj, $(this).find('input').val());
				}

				return false;
			});

			// sliders
			this.$el.find(".mw_slider").mwSlider({
				change: function () {
					var val = obj.$el.find(".mw_slider .cms_slider_val").val();
					obj.$el.find('.cms_image_option_custom_val').val(val);
					// on change
					if (obj.settings.change) {
						obj.settings.change.call(obj);
					}
				}
			});

		}

		return _MwImageOption;

	})();

	$.fn.mwImageOption = function (options) {
		return this.each(function (index, el) {
			el.MwImageOption = new MwImageOption(el, options);
		});
	};

	// Select box

	var MwSelectBox = (function (element, settings) {

		function _MwSelectBox(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// choose image
			this.$el.find(".mw_selectbox_item").click(function () {

				obj.$el.find(".mw_selectbox_item").removeClass('selected');
				$(this).addClass('selected');
				$(this).find('input').prop("checked", true);

				// on change
				if (obj.settings.change) {
					obj.settings.change.call(obj, $(this).find('input').val());
				}

				return false;
			});

		}

		return _MwSelectBox;

	})();

	$.fn.mwSelectBox = function (options) {
		return this.each(function (index, el) {
			el.MwSelectBox = new MwSelectBox(el, options);
		});
	};

	// Link option

	var MwLinkOption = (function (element, settings) {

		function _MwLinkOption(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// switch url type
			this.$el.find('.mw_switch').click(function () {

				if ($(this).find('input').prop("checked") == true) {
					obj.$el.find('.fl_custom_url_container').show();
					obj.$el.find('.fl_page_selector_container').hide();
				} else {
					obj.$el.find('.fl_page_selector_container').show();
					obj.$el.find('.fl_custom_url_container').hide();
				}

				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});
			this.$el.find('.mw_switch_button').click(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});
			this.$el.find('select').change(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});
			this.$el.find('.mw_input').change(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

			this.$el.find(".mw_select_page").on('chosen:ready', function(evt, params) {
				const ps = new PerfectScrollbar(obj.$el.find('.chosen-results')[0], {
					suppressScrollX: true,
					wheelPropagation: false,
				});
			});
			this.$el.find(".mw_select_page").chosen({width: "100%"});

		}

		return _MwLinkOption;

	})();

	$.fn.mwLinkOption = function (options) {
		return this.each(function (index, el) {
			el.MwLinkOption = new MwLinkOption(el, options);
		});
	};

	// buttons editor
	var MwButtonsEditor = (function (element, settings) {

		function _MwButtonsEditor(element) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".mw_buttons_editor_add").click(function () {
				var id = $(this).attr('data-id');
				var name = $(this).attr('data-name');
				var bkey = $(this).attr('data-bkey');

				$(this).attr('data-bkey', parseInt(bkey) + 1);

				$.post(ajaxurl, {
					"action": "mw_add_button_item",
					"id": id,
					"name": name,
					"bkey": bkey
				}, function (content) {
					obj.$el.find('.mw_buttons_editor_items').append(content);
					obj.$el.find(".ftb_button_item_added").mwButtonEdit().removeClass('ftb_button_item_added');
				});

				return false;
			});


			this.$el.find(".mw_buttons_editor_items").on('click', '.ve_content_button', function () {
				return false;
			});

			this.$el.find(".ftb_button_item").mwButtonEdit();

		}

		return _MwButtonsEditor;

	})();

	$.fn.mwButtonsEditor = function (options) {
		return this.each(function (index, el) {
			el.MwButtonsEditor = new MwButtonsEditor(el, options);
		});
	};

	// button edit
	var MwButtonEdit = (function (element) {

		function _MwButtonEdit(element) {

			this.$el = $(element);

			this.button_id = this.$el.attr('data-id');

			this.$button = this.$el.find('.ve_content_button');

			var self = this;

			this.setInverseBg();
			this.setOptionals(this.getStyle());

			this.$el.find(".mw_ftb_button_item_duplicate").click(function () {

				var id = $('.mw_buttons_editor_add').attr('data-id');
				var name = $('.mw_buttons_editor_add').attr('data-name');
				var bkey = $('.mw_buttons_editor_add').attr('data-bkey');
				var duplicate = $(this).closest('.ftb_button_item').attr('data-id');
				var but_set = $(this).closest('.ftb_button_item').find('select, input').serialize();

				//console.log(but_set);

				$('.mw_buttons_editor_add').attr('data-bkey', parseInt(bkey) + 1);

				$.post(ajaxurl, 'action=mw_duplicate_button_item&' + but_set + '&id=' + id + '&name=' + name + '&bkey=' + bkey + '&duplicate=' + duplicate, function (content) {
					$('.mw_buttons_editor_items').append(content);
					$(".ftb_button_item_added").mwButtonEdit().removeClass('ftb_button_item_added');
				});

				return false;
			});

			this.$el.find(".mw_ftb_button_item_save").click(function () {

				var but_set = $(this).closest('.ftb_button_item').find('select, input').serialize();
				var name = $(this).attr('data-name');
				self.$el.find('.mw_ftb_button_item_saved').show();
				self.$el.find('.mw_ftb_button_item_save').hide();
				//console.log(but_set);

				$.post(ajaxurl, 'action=mw_add_button_from_custom&' + but_set + '&name=' + name, function (content) {
					self.$el.find('.mw_ftb_button_item_saved span').show();
					self.$el.find('.mw_ftb_button_item_saved svg').hide();

					$('.cms_button_selector').append(content.button_item);
					$('#mw_page_builder').contents().find('footer').append(content.button_css);
					console.log(content.button_css);

				});

				return false;
			});

			this.$el.find(".mw_ftb_button_item_edit").click(function () {
				self.$el.find('.ftb_button_item_setting').toggle();
				self.$el.toggleClass('ftb_button_item_opened');
				return false;
			});

			this.$el.find(".mw_ftb_button_item_delete").click(function () {
				$(this).mwConfirm({
					text: texts.delete_button_confirm,
					show: true,
					onconfirm: function (confirm) {
						self.$el.remove();
					}
				});
				return false;
			});
			this.$el.find(".ftb_button_setting_tabs li a").click(function () {
				self.$el.find(".ftb_button_setting_tabs li a").removeClass('active');
				$(this).addClass('active');
				var target = $(this).attr('href');
				self.$el.find(".ftb_button_tab").hide();
				self.$el.find(target).show();
				return false;
			});

			// select pattern
			this.$el.find('.ftb_button_item_style').mwImageSelect({
				change: function (val) {

					self.setInverseBg();
					self.setOptionals(val);

					self.$button.removeClass('ve_content_button_type_1 ve_content_button_type_2 ve_content_button_type_3 ve_content_button_type_4 ve_content_button_type_5 ve_content_button_type_6 ve_content_button_type_7 ve_content_button_type_8 ve_content_button_type_9 ve_content_button_type_10 ve_content_button_type_11 ve_content_button_type_12 ve_content_button_type_13');
					self.$button.addClass('ve_content_button_type_' + val);

				}
			});

			this.$el.find(".ftb_button_item_corner").mwImageOption({
				change: function () {
					var val = this.$el.find('input:checked').val();
					if (val == 'custom') val = this.$el.find('.cms_image_option_custom_container input').val();
					val += 'px';
					var styles = {};
					styles["--button-corner-" + self.button_id] = val;
					self.changeStyles(styles, ':root');
				}
			});

			this.$el.find(".ftb_button_item_height_padding .mw_slider").mwSlider({
				change: function () {
					var val = self.$el.find(".ftb_button_item_height_padding .cms_slider_val").val();
					self.changeStyles({"padding-top": val + "em"});
					self.changeStyles({"padding-bottom": val + "em"});
				}
			});

			this.$el.find(".ftb_button_item_width_padding .mw_slider").mwSlider({
				change: function () {
					var val = self.$el.find(".ftb_button_item_width_padding .cms_slider_val").val();
					self.changeStyles({"padding-left": val + "em"});
					self.changeStyles({"padding-right": val + "em"});
				}
			});

			this.$el.find(".ftb_button_item_border_width .mw_slider").mwSlider({
				change: function () {
					self.setBorderWidth();
				}
			});

			this.$el.find(".ftb_button_item_font_color .cms_color_input").minicolors({
				swatches: ve_used_colors,
				change: function (value, opacity) {
					self.changeStyles({"color": value});
					self.setInverseBg();
				}
			});

			this.$el.find(".ftb_button_item_border_color .cms_color_input").minicolors({
				swatches: ve_used_colors,
				change: function (value, opacity) {
					self.changeStyles({"border-color": value});
					self.setInverseBg();
				}
			});

			this.$el.find('.ftb_button_item_background_color .background_color_field_container').mwBackgroundColor({
				change: function () {
					self.setBackgroundColor();
					self.setInverseBg();
				}
			});

			this.$el.find(".ftb_button_item_font").mwFontSetting({
				change: function () {
					var font = self.$el.find('.ftb_button_item_font .font_selected_input').val();
					var weight = self.$el.find('.ftb_button_item_font .font_weight_select').val();
					var file = self.$el.find('.ftb_button_item_font .font_weight_select').find(':selected').data('file');
					var allweights = self.$el.find('.ftb_button_item_font .font_weight_select option').map(function () {
						return this.value;
					}).get().join(',');

					self.changeStyles({"font-weight": weight});
					self.changeStyles({"font-family": font});

					if (font && !file) {
						var font_id = font.replace(" ", "_");
						if ($('#mw_gf_' + font_id).length == 0) {
							$('head').append('<link id="mw_gf_' + font_id + '" href="https://fonts.googleapis.com/css?family=' + font.replace(" ", "+") + ':' + allweights + '&subset=latin,latin-ext" rel="stylesheet" type="text/css">');
						}
					}

				}
			});

			/* hover */
			this.$el.find('.ftb_button_item_hover select').change(function () {
				if ($(this).val() == '') self.$el.find('.ftb_button_hover_setting').show();
				else self.$el.find('.ftb_button_hover_setting').hide();
			});
			this.$el.find(".ftb_button_item_hover_border_color .cms_color_input").minicolors({
				swatches: ve_used_colors,
			});
			this.$el.find(".ftb_button_item_hover_font_color .cms_color_input").minicolors({
				swatches: ve_used_colors,
			});
			this.$el.find('.ftb_button_item_hover_background_color .background_color_field_container').mwBackgroundColor({});

		}

		return _MwButtonEdit;

	})();


	MwButtonEdit.prototype.getStyle = function () {
		return this.$el.find('.ftb_button_item_style input').val();
	}

	MwButtonEdit.prototype.setInverseBg = function () {

		var set = false;
		var style = this.getStyle();

		var bg_color = this.$el.find('.ftb_button_item_background_color .cms_background_start_color .cms_background_color_input').val();
		var border_color = this.$el.find('.ftb_button_item_border_color .cms_color_input').val();
		var font_color = this.$el.find('.ftb_button_item_font_color .cms_color_input').val();

		if (bg_color && style != '12' && style != '13') {
			if (isColorLight(bg_color)) set = true;
		} else {
			if (border_color && isColorLight(border_color) && style != '13') set = true;
			else if (font_color && isColorLight(font_color)) set = true;
		}

		if (set) this.$el.find('.ftb_button_item_head').addClass('ftb_button_item_head_inverse');
		else this.$el.find('.ftb_button_item_head').removeClass('ftb_button_item_head_inverse');
	}

	MwButtonEdit.prototype.setOptionals = function (val) {

		this.$el.find('.ftb_button_optional_set').hide();
		this.setBorderColor(true);
		this.setBorderWidth(true);
		this.setBackgroundColor(true);

		if (val == '4') {
			this.$el.find('.ftb_button_optional_set_border').show();
			this.$el.find('.ftb_button_optional_set_background').show();
			this.$el.find('.ftb_button_optional_set_hover_background').show();
			this.$el.find('.ftb_button_optional_set_padding').show();
			this.$el.find('.ftb_button_optional_set_corner').show();
			this.setBorderColor();
			this.setBorderWidth();
			this.setBackgroundColor();
		} else if (val == '12') {
			this.$el.find('.ftb_button_optional_set_border').show();
			this.$el.find('.ftb_button_optional_set_padding').show();
			this.$el.find('.ftb_button_optional_set_hover_background').show();
			this.$el.find('.ftb_button_optional_set_corner').show();
			this.setBorderColor();
			this.setBorderWidth();
		} else if (val == '13') {

			this.setBorderColor();
			this.setBorderWidth();
		} else {
			this.$el.find('.ftb_button_optional_set_background').show();
			this.$el.find('.ftb_button_optional_set_hover_background').show();
			this.$el.find('.ftb_button_optional_set_padding').show();
			this.$el.find('.ftb_button_optional_set_corner').show();
			this.setBackgroundColor();
		}

	}
	MwButtonEdit.prototype.setBorderColor = function (clear = false) {
		var value = '';
		if (!clear) value = this.$el.find('.ftb_button_item_border_color .cms_color_input').val();
		this.changeStyles({"border-color": value});
	}
	MwButtonEdit.prototype.setBorderWidth = function (clear = false) {
		var value = '';
		if (!clear) value = this.$el.find(".ftb_button_item_border_width .cms_slider_val").val();
		this.changeStyles({"border-width": value + "px"});
	}
	MwButtonEdit.prototype.setBackgroundColor = function (clear = false) {
		if (clear) {
			this.changeStyles({"background": ''});
		} else {
			var color1 = this.$el.find('.ftb_button_item_background_color .cms_background_start_color .cms_background_color_input').val();
			var color2 = this.$el.find('.ftb_button_item_background_color .cms_background_end_color .cms_background_color_input').val();

			if (color1 == '') color1 = 'transparent';

			if (this.$el.find('.ftb_button_item_background_color .mw_switch input').prop("checked") && color2) {
				this.changeStyles({"background": "linear-gradient(to bottom, " + color1 + " 0%," + color2 + " 100%) no-repeat border-box"});
			} else {
				this.changeStyles({"background": color1});
			}
		}
	}

	MwButtonEdit.prototype.changeStyles = function (newstyles, sel = null) {
		var selector = '.ftb_button_item .ve_content_button_style_' + this.button_id;
		if (sel) selector = sel;
		// get styles
		var styles = mwGetCssStyles(this.$el.find('style').text());
		// change styles
		styles = mwChangeCssStyles(styles, selector, newstyles); // in admin.js
		// save styles
		this.$el.find('style').text(mwSaveCssStyles(styles));
	}

	$.fn.mwButtonEdit = function () {
		return this.each(function (index, el) {
			el.MwButtonEdit = new MwButtonEdit(el);
		});
	};

	var MwButtonOption = (function (element, settings) {

		function _MwButtonOption(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
				reload: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;
			var selected_container = this.$el.find('.cms_button_selected');
			var selected = this.$el.find('.cms_button_selected div');

			this.$el.find('.cms_button_selected').click(function () {

				obj.$el.find('.cms_button_selected').hide();
				obj.$el.find('.cms_button_selector').show();

				return false;
			});

			this.$el.on("click", ".cms_button_selector_item", function () {

				obj.$el.find('.cms_button_selector_item').removeClass('cms_button_selector_item_selected');
				$(this).addClass('cms_button_selector_item_selected');
				let bgType = $(this).data('bgtype');

				var selected_input = $(this).find('input');

				selected.removeClass();
				selected_container.removeClass('cms_button_custom_selected');
				selected.addClass('ve_content_button ve_content_button_type_' + selected_input.attr('data-type') + ' ve_content_button_style_' + selected_input.val());
				if (selected_input.val() == 'custom_button') selected_container.addClass('cms_button_custom_selected');

				obj.$el.find('.cms_button_selected').show().removeClass('cms_button_selected_bg_dark cms_button_selected_bg_normal cms_button_selected_bg_invers').addClass('cms_button_selected_bg_' + bgType);
				obj.$el.find('.cms_button_selector').hide();

				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

			this.$el.find('select').change(function () {
				if ($(this).val() == 'custom') obj.$el.find('.mw_button_field_size_container').show();
				else obj.$el.find('.mw_button_field_size_container').hide();
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

			// sliders
			this.$el.find(".mw_slider").mwSlider({
				change: function (event, ui) {
					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			this.$el.find(".mw_custom_button_setting_window .ftb_button_item").mwButtonEdit();

			this.$el.find(".mw_open_custom_button_edit").click(function () {
				obj.$el.find('.mw_custom_button_setting_window_container').show();
				setTimeout(function () {
					obj.$el.find('.mw_custom_button_setting_window').addClass('mwb-show');
				}, 10);

				return false;
			});
			this.$el.find(".mw_custom_button_setting_close").click(function () {
				var hover = obj.$el.find('.mw_custom_button_setting_window .ftb_button_item_hover select').val();
				var type = obj.$el.find('.mw_custom_button_setting_window .cms_image_select_val').val();

				obj.$el.find('.cms_button_selector_custom_item input').attr('data-hover', hover);
				obj.$el.find('.cms_button_selector_custom_item input').attr('data-type', type);
				obj.$el.find('.mw_custom_button_setting_window').removeClass('mwb-show');
				setTimeout(function () {
					obj.$el.find('.mw_custom_button_setting_window_container').hide();
				}, 200);

				// on change
				if (obj.settings.reload) {
					obj.settings.reload.call();
				}
				return false;
			});


		}

		return _MwButtonOption;

	})();

	$.fn.mwButtonOption = function (options) {
		return this.each(function (index, el) {
			el.MwButtonOption = new MwButtonOption(el, options);
		});
	};

	var MwBackgroundSet = (function (element, settings) {

		function _MwBackgroundSet(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".mw_hidden_setting").mwHiddenSetting();

			this.$el.find(".cms_color_input").minicolors({
				swatches: ve_used_colors,
				change: function (value, opacity) {

					// change hidden setting label
					obj.$el.find(".cms_background_set_preview").css('background', value);

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			this.$el.find(".cms_background_set_corners").mwImageOption({
				change: function (value) {

					var radius = '';
					if(value == '1')
						radius = '5';
					else if(value == '2')
						radius = '10';
					else
						radius = '0';

					// change hidden setting label
					obj.$el.find(".cms_background_set_preview").css('border-radius', radius+'px');

					// on change
					if (obj.settings.change) {
						obj.settings.change.call(this);
					}
				}
			});

			this.$el.find('select').change(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

			// switch
			this.$el.find('.mw_switch input').change(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

		}

		return _MwBackgroundSet;

	})();

	$.fn.mwBackgroundSet = function (options) {
		return this.each(function (index, el) {
			el.MwBackgroundSet = new MwBackgroundSet(el, options);
		});
	};

	var mwSaleFormSelector = (function (element, settings) {

		function _mwSaleFormSelector(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.on('change', '.change_api_item_selector', function () {
				// on change
				if (obj.settings.change) {
					var api = obj.$el.find(".change_api_selector").val();
					var selectedOption = $(this).find("option:selected");
					if($(this).val())
					{
						obj.$el.find(".mw_api_item_selector_container").addClass('selected');
						obj.$el.find(".mw_icon_button_edit").attr('href', selectedOption.attr('data-url'));
					}
					else
					{
						obj.$el.find(".mw_api_item_selector_container").removeClass('selected');
					}

					obj.showHideSetting();

					obj.settings.change.call(this, api);
				}
			});

			this.$el.on('click', '.add_api_connection', function () {

				$(this).mwOpenApiConnectModal({
					onconnect: function (data) {
						obj.$el.find('.api_connection_container').html(data.selector);
					},
					action: 'mwSaveSellingApiItemSetting',
				});

				return false;
			});

			this.$el.on("change", ".change_api_selector", function () {

				var tagid = $(this).attr('data-id');
				var tagname = $(this).attr('data-name');
				var api = $(this).val();
				var selector = $(this);

				if (api != 'se') $('.form_look_setting').hide();
				else $('.form_look_setting').show();

				obj.$el.find('.mw_api_selector_container').html('<div class="miocms_loading"></div>');

				$.post(ajaxurl, {
					"action": "mwSellingChangeSelector",
					"api": api,
					"tag_id": tagid,
					"tag_name": tagname
				}, function (data) {
					obj.$el.find('.mw_api_selector_container').html(data);

					// chosen select
					obj.$el.find('select.mw_whisperer').on('chosen:ready', function(evt, params) {
						const ps = new PerfectScrollbar(obj.$el.find('.chosen-results')[0], {
							suppressScrollX: true,
							wheelPropagation: false,
						});
					});
					obj.$el.find('select.mw_whisperer').chosen({width: "100%"});

					obj.showHideSetting();

					if (obj.settings.change) {
						obj.settings.change.call(this, api);
					}

				});
				return false;
			});

			// chosen select
			this.$el.find('select.mw_whisperer').on('chosen:ready', function(evt, params) {
				const ps = new PerfectScrollbar(obj.$el.find('.chosen-results')[0], {
					suppressScrollX: true,
					wheelPropagation: false,
				});
			});
			this.$el.find('select.mw_whisperer').chosen({width: "100%"});

			obj.showHideSetting();
		}

		return _mwSaleFormSelector;

	})();

	mwSaleFormSelector.prototype.showHideSetting = function () {
		var api = this.$el.find(".change_api_selector").val();
		var selectedOption = this.$el.find(".change_api_item_selector").find("option:selected");
		$('.mw_fapi_form_setting').hide();
		if(api === 'fapi')
		{
			var newForm = selectedOption.attr('data-info');

			if(newForm === '1')
			{
				$('.mw_newfapi_form_setting').show();
			}
			else
			{
				$('.mw_oldfapi_form_setting').show();
			}
		}
	}

	$.fn.mwSaleFormSelector = function (options) {
		return this.each(function (index, el) {
			el.mwSaleFormSelector = new mwSaleFormSelector(el, options);
		});
	};

	var MwEmailMarketingSelector = (function (element, settings) {

		function _MwEmailMarketingSelector(element, settings) {

			this.$el = $(element);
			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			let api = this.$el.find('.change_api_selector').val();

			if(api === 'ecomail' || api === 'mailerlite'){
				$('.mail_api_switch').hide();
			} else {
				$('.mail_api_switch').show();
			}
			var obj = this;

			this.$el.on('change', '.change_api_item_selector', function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

			this.$el.on('click', '.add_api_connection', function () {

				$(this).mwOpenApiConnectModal({
					onconnect: function (data) {
						obj.$el.find('.api_connection_container').html(data.selector);
					},
					action: 'mwSaveEmailingApiItemSetting',
				});

				return false;
			});

			this.$el.on("change", ".change_api_selector", function () {

				var tagid = $(this).attr('data-id');
				var tagname = $(this).attr('data-name');
				var api = $(this).val();
				var type = $(this).attr('data-type');

				if (api != 'se') $('.form_look_setting').hide();
				else $('.form_look_setting').show();

				obj.$el.find('.mw_api_selector_container').html('<div class="miocms_loading"></div>');

				if(api === 'ecomail' || api === 'mailerlite'){
					$('.mail_api_switch').hide();
				} else {
					$('.mail_api_switch').show();
				}

				$.post(ajaxurl, {
					"action": "mwEmailingChangeSelector",
					"api": api,
					"tag_id": tagid,
					"tag_name": tagname,
					"type": type
				}, function (data) {
					obj.$el.find('.mw_api_selector_container').html(data);

					// chosen select
					obj.$el.find('select.mw_whisperer').on('chosen:ready', function(evt, params) {
						const ps = new PerfectScrollbar(obj.$el.find('.chosen-results')[0], {
							suppressScrollX: true,
							wheelPropagation: false,
						});
					});
					obj.$el.find('select.mw_whisperer').chosen({width: "100%"});

					if (obj.settings.change) {
						obj.settings.change.call(this);
					}
				});

				return false;
			});

			// chosen select
			this.$el.find('select.mw_whisperer').on('chosen:ready', function(evt, params) {
				const ps = new PerfectScrollbar(obj.$el.find('.chosen-results')[0], {
					suppressScrollX: true,
					wheelPropagation: false,
				});
			});
			this.$el.find('select.mw_whisperer').chosen({width: "100%"});

		}

		return _MwEmailMarketingSelector;

	})();

	$.fn.mwEmailMarketingSelector = function (options) {
		return this.each(function (index, el) {
			el.MwEmailMarketingSelector = new MwEmailMarketingSelector(el, options);
		});
	};

	// mw page manager
	var MwPageSelect = (function (element, settings) {

		function _MwPageSelect(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			if (obj.$el.find('.mw_select_page').hasClass('mw_select_page_lazy_loading')) {
				obj.$el.click(function() {
					if (!obj.$el.find('.mw_select_page').hasClass('lazy_loaded')) {
						obj.$el.find('.chosen-drop').addClass('mw_loading');
						if ($('.mw_select_page_options').length) {
							obj.lazyLoad();
						} else {
							$.post(
								ajaxurl,
								{
									"action": "mwLoadSelectPageOptions",
								},
								function (content) {
									$('body').append(content);
									obj.lazyLoad();
								}
							);
						}
					}
				});
			}

			this.$el.find('.mw_select_page').change(function(){
				if($(this).val()=='')
				{
					obj.$el.removeClass('selected');
				}
				else
				{
					obj.$el.addClass('selected');
					obj.$el.find(".mw_icon_button_edit").attr('href', obj.$el.find(".mw_select_page option:selected").attr('data-url'));
				}
				if (obj.settings.change) {
					obj.settings.change.call(obj, $(this));
				}
			});

			obj.$el.find(".mw_icon_button_add").click(function () {

				$(this).mwOpenInstallator({
					install: 'object',
					objectid: 'page',
					oninstall: function (lightboxInstallator, data) {
						obj.$el.find('.mw_select_page').append('<option value="'+data.id+'" data-title="'+data.title+'"  data-url="'+data.url+'">'+data.title+'</option>');
						obj.$el.find('.mw_select_page').val(data.id);
						obj.$el.find('.mw_select_page.mw_whisperer').trigger("chosen:updated");
						obj.$el.addClass('selected');
						obj.$el.find(".mw_icon_button_edit").attr('href', data.url);
						lightboxInstallator.close();

						if (obj.settings.change) {
							obj.settings.change.call(obj, obj.$el.find('.mw_select_page'));
						}
					}
				});

				return false;
			});

			// chosen select
			this.$el.find('.mw_select_page.mw_whisperer').on('chosen:ready', function(evt, params) {
				const ps = new PerfectScrollbar(obj.$el.find('.chosen-results')[0], {
					suppressScrollX: true,
					wheelPropagation: false,
				});
			});
			this.$el.find('.mw_select_page.mw_whisperer').chosen({width: "100%"});

		}

		return _MwPageSelect;

	})();

	MwPageSelect.prototype.lazyLoad = function () {
		let options = $('.mw_select_page_options').html();
		let val = this.$el.find('.mw_select_page').val();
		if(val) {
			this.$el.find('.mw_select_page option[value="' + val + '"]').remove();
		}
		this.$el.find('.mw_select_page').append(options).change();
		this.$el.find('.mw_select_page').val(val).addClass('lazy_loaded');
		this.$el.find('.mw_select_page.mw_whisperer').trigger("chosen:updated");
		this.$el.find('.chosen-drop').removeClass('mw_loading');
	}

	$.fn.mwPageSelect = function (options) {
		return this.each(function (index, el) {
			el.MwPageSelect = new MwPageSelect(el, options);
		});
	};

	// mw page manager
	var MwItemSelect = (function (element, settings) {

		function _MwItemSelect(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('select').change(function(){
				if($(this).find('option:selected').val()=='0')
				{
					obj.$el.removeClass('selected');
				}
				else
				{
					obj.$el.addClass('selected');
					obj.$el.find(".mw_icon_button_edit").attr('href', obj.$el.find("select option:selected").attr('data-url'));
				}
				if (obj.settings.change) {
					obj.settings.change.call(obj, $(this));
				}
			});

			obj.$el.find(".mw_icon_button_add").click(function () {

				$(this).mwOpenFastAddModal({
					onsave: function (data) {
						obj.$el.find('select').append(data.content);
						obj.$el.find('select').val(data.id);
						obj.$el.find('select.mw_whisperer').trigger("chosen:updated");
						obj.$el.addClass('selected');
						obj.$el.find(".mw_icon_button_edit").attr('href', data.edit_url);

						if (obj.settings.change) {
							obj.settings.change.call(obj, obj.$el.find('select'));
						}
					}

				});

				return false;
			});

			// chosen select
			this.$el.find('select.mw_whisperer').on('chosen:ready', function(evt, params) {
				const ps = new PerfectScrollbar(obj.$el.find('.chosen-results')[0], {
					suppressScrollX: true,
					wheelPropagation: false,
				});
			});
			this.$el.find('select.mw_whisperer').chosen({width: "100%"});

		}

		return _MwItemSelect;

	})();

	$.fn.mwItemSelect = function (options) {
		return this.each(function (index, el) {
			el.MwItemSelect = new MwItemSelect(el, options);
		});
	};

	// Upload file - NEW

	var MwUploadFile = (function (element, settings) {

		function _MwUploadFile(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.cms_upload_file').click(function (e) {

				e.preventDefault();

				var workflow = wp.media.editor.get('mw_file_upload');

				//If WF already exist, just open modal
				if (typeof workflow !== 'undefined') {
					workflow.options.mw_obj = obj;
					workflow.open();
					return;
				}

				//Create new WF and bind events
				workflow = wp.media.editor.add('mw_file_upload', {
					frame: 'post',
					state: 'insert',
					title: wp.media.view.l10n.addMedia,
					multiple: false,
					mw_obj: obj
				});

				workflow.on('open', function () {
					jQuery('.media-frame').addClass('hide-menu');
				});

				//Insert new images to image list
				workflow.on('insert', function (selection) {

					var mw_obj = workflow.options.mw_obj;

					var state = workflow.state();
					var image = state.get('selection').first().toJSON();

					mw_obj.$el.find('.cms_text_upload').val(image.url);
					mw_obj.$el.find('.cms_upload_file_container').addClass('cms_upload_file_uploaded');

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}

				});

				//Open editor
				workflow.open();

				/*
            var _orig_send_attachment = wp.media.editor.send.attachment;

            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            _custom_media = true;
            wp.media.editor.send.attachment = function(props, attachment){
              if ( _custom_media ) {
                //alert(attachment.toSource());
                imgurl = attachment['url'];
                obj.$el.find('.cms_text_upload').val(imgurl);
              } else {
                return _orig_send_attachment.apply( this, [props, attachment] );
              }
              obj.$el.find('.cms_upload_file_container').addClass('cms_upload_file_uploaded');

              // on change
              if(obj.settings.change) {
                  obj.settings.change.call(this);
              }
            };

            wp.media.editor.open(button);

            return false;
            */
			});

			// clear upload file
			this.$el.find('.cms_delete_uploaded_file').click(function () {
				obj.$el.find('.cms_text_upload').val('');
				obj.$el.find('.cms_upload_file_container').removeClass('cms_upload_file_uploaded');
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
				return false;
			});

		}

		return _MwUploadFile;

	})();

	$.fn.mwUploadFile = function (options) {
		return this.each(function (index, el) {
			el.MwUploadFile = new MwUploadFile(el, options);
		});
	};

	// status switch
	var MwStatusSwitch = (function (element, settings) {

		function _MwStatusSwitch(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.mw_switch').click(function () {
				var val;

				if ($(this).find('input').prop("checked")) {
					val = obj.$el.data('true');
				} else {
					val = obj.$el.data('false');
				}
				obj.$el.find('.mw_status_switch_val').val(val);

				if (obj.settings.change) {
					obj.settings.change.call(this, val);
				}
			});

		}

		return _MwStatusSwitch;

	})();

	$.fn.mwStatusSwitch = function (options) {
		return this.each(function (index, el) {
			el.MwStatusSwitch = new MwStatusSwitch(el, options);
		});
	};

	// transaction emails
	var MwTransactionEmail = (function (element, settings) {

		function _MwTransactionEmail(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find('.mw_reset_email_content').click(function (e) {

				var el = $(this);
				$(this).mwConfirm({
					text: texts.email_refresh_info,
					but_text: texts.email_refresh,
					show: true,
					onconfirm: function (confirm) {
						const data = el.data('content');
						const subject = data['subject'] || '';
						const content = data['content'] || '';
						const name = el.data('name');

						obj.$el.find('[name="' + name + '[subject]"]').val(subject);
						obj.$el.find('[name="' + name + '[content]"]').val(content);
						el.fadeOut();

						if (obj.settings.change) {
							obj.settings.change.call(this);
						}

					}
				});
				return false;

			});

			this.$el.on('change keyup keypress', '.mw_email_subject_editor, .mw_email_content_editor', function() {

				if (obj.settings.change) {
					obj.settings.change.call(this);
				}

				const $button = obj.$el.find('.mw_reset_email_content');
				if (!$button.length) {
					return;
				}

				const name = $button.data('name');
				const $textarea = obj.$el.find('[name="' + name + '[content]"]');
				const $subject = obj.$el.find('[name="' + name + '[subject]"]');

				if (!$textarea.length || !$subject.length) {
					$button.fadeIn().css("display","flex");
					return;
				}

				const data = $button.data('content');
				if (!data) {
					$button.fadeIn().css("display","flex");
					return;
				}

				const defaultSubject = data['subject'] || null;
				const defaultContent = data['content'] || null;
				if (!defaultContent || !defaultSubject) {
					$button.fadeIn().css("display","flex");
					return;
				}

				const content = $textarea.val();
				const subject = $subject.val();
				if (defaultContent !== content || defaultSubject !== subject) {
					$button.fadeIn().css("display","flex");
					return;
				}

				$button.fadeOut();

			})

			// switch
			this.$el.find('.mw_switch input').change(function () {
				// on change
				if (obj.settings.change) {
					obj.settings.change.call(this);
				}
			});

			// attachment
			this.$el.find('.cms_upload_file_container').mwUploadFile({
				change: function () {
					if (obj.settings.change) {
						obj.settings.change.call(this);
					}
				}
			});

		}

		return _MwTransactionEmail;

	})();

	$.fn.mwTransactionEmail = function (options) {
		return this.each(function (index, el) {
			el.MwTransactionEmail = new MwTransactionEmail(el, options);
		});
	};

	// range filter selector
	var MwRangeFilter = (function (element, settings) {

		function _MwRangeFilter(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find("select").change(function(e) {
				var period = $(this).val();
				if(period=='custom')
				{
					obj.$el.addClass('mw_range_select_custom_selected');
				}
				else
				{
					obj.$el.removeClass('mw_range_select_custom_selected');
					if (obj.settings.change) {
						obj.settings.change.call(obj,period,'','');
					}
				}

			});

			from_input = $( ".mw_range_select_from" ).datepicker({
				changeMonth: true,
				changeYear: true,
			}).on( "change", function() {
				var date = $.datepicker.parseDate( "dd.mm.yy", this.value );
				to_input.datepicker( "option", "minDate", date );
			}),
	      	to_input = $( ".mw_range_select_to" ).datepicker({
				changeMonth: true,
				changeYear: true,
			}).on( "change", function() {
				var date = $.datepicker.parseDate( "dd.mm.yy", this.value );
				from_input.datepicker( "option", "maxDate", date );
	      	});

			this.$el.find(".mw_range_select_custom_send").click(function(e) {
				var period = obj.$el.find("select").val();
				var from = obj.$el.find( ".mw_range_select_from" ).val();
				var to = obj.$el.find( ".mw_range_select_to" ).val();

				if (obj.settings.change) {
					obj.settings.change.call(obj,period,from,to);
				}
			});

		}

		return _MwRangeFilter;

	})();

	$.fn.mwRangeFilter = function (options) {
		return this.each(function (index, el) {
			el.MwRangeFilter = new MwRangeFilter(el, options);
		});
	};

	// add builder message
	var MwAddMessage = (function (element, settings) {

		function _MwAddMessage(element, settings) {

			this.$el = $(element);

			this.defaults = {
				close: false,
				autoHide: 0,
				content: '',
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.html(this.settings.content);

			if(this.settings.close)
			{
				this.$el.find('.mw_message_box').click(function(e) {
					$(this).remove();
				});
			}
			if(this.settings.autoHide)
			{
				setTimeout(function() {
					obj.$el.find('.mw_message_box').fadeOut();
				}, this.settings.autoHide );
			}

		}

		return _MwAddMessage;

	})();

	$.fn.mwAddMessage = function (options) {
		return this.each(function (index, el) {
			el.MwAddMessage = new MwAddMessage(el, options);
		});
	};

	// add builder message
	var MwStatusSelect = (function (element, settings) {

		function _MwStatusSelect(element, settings) {

			this.$el = $(element);

			this.defaults = {
				onchange: null
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.find(".mw_dropdown_button").mwDropdownButton({});

			this.$el.find(".mw_dropdown_list ul a").click(function(){
				var val = $(this).attr('data-val');
				var text = $(this).attr('data-text');
				var status = $(this).attr('data-status');
				var icon = $(this).attr('data-icon');

				var container = obj.$el.find('.mw_status_field');

				obj.$el.find('input').val(val);
				obj.$el.find('.mw_status_field_text').html(text);
				obj.$el.find('.mw_status_field_icon').html('<svg role="img"><use xlink:href="' + icon + '"></use></svg>');

				container.removeClass();
				container.addClass('mw_status_field mw_status_field_'+status);

				// on change
				if (obj.settings.onchange) {
					obj.settings.onchange.call(obj, val, status);
				}

			});

		}

		return _MwStatusSelect;

	})();

	$.fn.mwStatusSelect = function (options) {
		return this.each(function (index, el) {
			el.MwStatusSelect = new MwStatusSelect(el, options);
		});
	};

	// open api connection button
	var MwOpenApiConnectModal = (function (element, settings) {

		function _MwOpenApiConnectModal(element, settings) {

			this.$el = $(element);

			this.defaults = {
				action: "mwSaveApiItemSetting",
				onconnect: null,
				ondisconnect: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			$(this).mwModal({
				onopen: function (modal) {

						var apiId = obj.$el.attr('data-api');

						$.post(
							ajaxurl,
							{
								"action": "mwOpenApiItemSetting",
								"api_id": apiId,
							},
							function (data) {
								modal.addContent(data);
								modal.$modal.find('.mw_onedit_action').mwOnEditAction();
							}
						);

				},
				onsave: function (modal) {

					var formdata = modal.getFormData();
					var tag_id = obj.$el.attr('data-tagid');
					var tag_name = obj.$el.attr('data-name');
					var type = obj.$el.attr('data-type');
					if(type === undefined) type = '';

					$.post(ajaxurl, 'action=' + obj.settings.action + '&tag_id=' + tag_id + '&tag_name=' + tag_name + '&type=' + type + '&' + formdata, function (data) {
						if(data.status)
						{
							// on connect
							if (obj.settings.onconnect) {
								obj.settings.onconnect.call(this, data);
							}
							modal.close();
						}
						else
						{
							// on connect
							if (obj.settings.ondisconnect) {
								obj.settings.ondisconnect.call(this, data);
							}
							modal.removeLoading();
							modal.addMessage(data);
						}

					});

					return false;
				}

			});

		}

		return _MwOpenApiConnectModal;

	})();

	$.fn.mwOpenApiConnectModal = function (options) {
		return this.each(function (index, el) {
			el.MwOpenApiConnectModal = new MwOpenApiConnectModal(el, options);
		});
	};

	// open fast add modal
	var MwOpenFastAddModal = (function (element, settings) {

		function _MwOpenFastAddModal(element, settings) {

			this.$el = $(element);

			this.defaults = {
				action: "mwSaveFastAddObject",
				onsave: null,
				afterOpen: null,
				returnType: 'select',
				returnTarget: '',
				returnName: '',
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			var objectId = this.$el.attr('data-object');
			this.$el.mwModal({
				onopen: function (modal) {

						$.post(
							ajaxurl,
							{
								"action": "mwOpenFastAdd",
								"object_id": objectId,
							},
							function (data) {
								modal.addContent(data);
								modal.$modal.find('.mw_onedit_action').mwOnEditAction();
								setTimeout(function () {
									modal.$modal.find('form input:first').focus();

									// afterOpen
									if (obj.settings.afterOpen) {
										obj.settings.afterOpen.call(this, modal);
									}

								}, 150);
							}
						);

				},
				onsave: function (modal) {

					var formdata = modal.getFormData();

					$.post(ajaxurl, 'action=' + obj.settings.action + '&return_type=' + obj.settings.returnType + '&return_name=' + obj.settings.returnName + '&' + formdata, function (data) {
						if(data.success)
						{
							if (obj.settings.returnType == 'redirect') {
								window.location = data.admin_url;
							}
							else if(obj.settings.returnTarget) {
								$(obj.settings.returnTarget).append(data.content).show();
							}

							// on save
							if (obj.settings.onsave) {
								obj.settings.onsave.call(this, data, obj.settings.returnTarget);
							}

							if (obj.settings.returnType !== 'redirect') {
								modal.close();
							}
						}
						else
						{
							modal.removeLoading();
							modal.addMessage(data);
						}

					});

					return false;
				}

			});

		}

		return _MwOpenFastAddModal;

	})();

	$.fn.mwOpenFastAddModal = function (options) {
		return this.each(function (index, el) {
			el.MwOpenFastAddModal = new MwOpenFastAddModal(el, options);
		});
	};

	// open fast add modal
	var MwOpenFastCopyModal = (function (element, settings) {

		function _MwOpenFastCopyModal(element, settings) {

			this.$el = $(element);

			this.defaults = {
				action: "mwSaveFastCopyObject",
				objectId: "",
				itemId: "",
				onsave: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			this.$el.mwModal({
				onopen: function (modal) {

						$.post(
							ajaxurl,
							{
								"action": "mwOpenFastCopy",
								"object_id": obj.settings.objectId,
								"item_id": obj.settings.itemId,
								"copy": 1,
							},
							function (data) {
								modal.addContent(data);
								modal.$modal.find('.mw_onedit_action').mwOnEditAction();
								setTimeout(function () {
									modal.$modal.find('form input:first').focus();
								}, 150);
							}
						);

				},
				onsave: function (modal) {

					var formdata = modal.getFormData();

					$.post(ajaxurl, 'action=' + obj.settings.action + '&' + formdata, function (data) {
						if(data.success)
						{
							// on save
							if (obj.settings.onsave) {
								obj.settings.onsave.call(this, data, modal);
							}
							else {
								modal.close();
							}
						}
						else
						{
							modal.removeLoading();
							modal.addMessage(data);
						}

					});

					return false;
				}

			});

		}

		return _MwOpenFastCopyModal;

	})();

	$.fn.mwOpenFastCopyModal = function (options) {
		return this.each(function (index, el) {
			el.MwOpenFastCopyModal = new MwOpenFastCopyModal(el, options);
		});
	};

	// smtp tester
	var MwTestSMTP = (function (element) {

		function _MwTestSMTP(element) {

			this.$el = $(element);
			var obj = this;

			this.$el.find('.mw_smtp_test_send').click(function (e) {
				var button = $(this);
				var email = obj.$el.find('.mw_smtp_test_email').val();
				var info_box = obj.$el.find('.mw_smtp_test_info');
				button.addClass('mw_button_wicon');
				button.attr('disabled', 'disabled');

				$.post(ajaxurl, {"action": "mw_smtp_test_email", "email": email}, function (data) {
					info_box.html(data.html);
					info_box.show();
					button.removeAttr('disabled');
					button.removeClass('mw_button_wicon');
				});

				return false;

			});
		}

		return _MwTestSMTP;

	})();

	$.fn.mwTestSMTP = function () {
		return this.each(function (index, el) {
			el.MwTestSMTP = new MwTestSMTP(el);
		});
	};

	// blog select page
	var MwBlogSelectPage = (function (element) {

		function _MwBlogSelectPage(element, settings) {

			this.defaults = {
				onchange: null
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			this.$el = $(element);
			var obj = this;

			this.$el.find(".cms_radio_container input").change(function () {
				if($(this).val() == 'page')
				{
					$(".cms_show_group_blogpage").show();
				}
				else
				{
					$(".cms_show_group_blogpage").hide();
				}
				// on change
				if (obj.settings.onchange) {
					obj.settings.onchange.call(obj);
				}
			});

			this.$el.find("select").change(function () {
				// on change
				if (obj.settings.onchange) {
					obj.settings.onchange.call(obj);
				}
			});

			this.$el.find("select").chosen({width: "100%"});

		}

		return _MwBlogSelectPage;

	})();

	$.fn.mwBlogSelectPage = function (options) {
		return this.each(function (index, el) {
			el.MwBlogSelectPage = new MwBlogSelectPage(el, options);
		});
	};

	// input whisperer
	var MwInputWhisperer = (function (element) {

		function _MwInputWhisperer(element, settings) {

			this.defaults = {
				onchange: null,
				addModalArgs: {},
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			this.$el = $(element);
			var obj = this;

			this.$el.find(".mw_input_whisperer_input").focus(function () {
				obj.$el.addClass('open');
			});
			$('body').click(function(event){
				if(!$(event.target).is('.mw_input_whisperer') && !$(event.target).parents('.mw_input_whisperer').length){
					obj.$el.removeClass('open');
					obj.$el.find(".mw_input_whisperer_input").val('').keyup();
				}
			});

			this.$el.find(".mw_input_whisperer_input").keyup(function (event) {
				var searched = $(this).val().toLowerCase();
				var text;
				if (event.keyCode === 13) {
					var added = false;
					obj.$el.find(".mw_input_whisperer_item:not(.whisperer_item_used) a").each(function () {

						text = $(this).data('text').toLowerCase();

						if (text === searched && added === false) {
							$(this).click();
							added = true;
						}

					});

					if(added === false) {
						obj.$el.find(".mw_input_whisperer_add").click();
					}
				}
				else {
					obj.$el.find(".mw_input_whisperer_item").addClass('cms_nodisp');
					if (searched !== "") {
						obj.$el.find(".mw_input_whisperer_item:not(.whisperer_item_used)").each(function () {

							text = $(this).find('a').data('text').toLowerCase();

							if (text.search(searched) >= 0) {
								$(this).removeClass('cms_nodisp');
							}

						});
					} else {
						obj.$el.find(".mw_input_whisperer_item").removeClass('cms_nodisp');
					}
				}
			});

			this.$el.on('click', ".mw_input_whisperer_item a", function () {
				var li = $(this).closest('li');
				li.addClass('whisperer_item_used');
				var html = li.find("textarea").val();
				$(".mws_product_tags_list").append(html);
				return false;
			});

			this.$el.find(".mw_input_whisperer_add").click(function () {
				$(this).mwOpenFastAddModal(settings.addModalArgs);
			});

		}

		return _MwInputWhisperer;

	})();

	$.fn.mwInputWhisperer = function (options) {
		return this.each(function (index, el) {
			el.MwInputWhisperer = new MwInputWhisperer(el, options);
		});
	};

});
