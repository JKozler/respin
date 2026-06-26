jQuery(function ($) {

	// droppable & draggable
	mw_init_sorting();

	// tinymce
	mw_init_tinymce();

	$('.mw_page_builder_content').on("click", ".open_added_lightbox", function () {
		$(this).colorbox({maxWidth: "90%", maxHeight: "90%"});
	});

	$('body').on('submit', 'form', function (e) {
		if (!$(this).attr('target') && $(this).attr('action') != '')
			$(this).attr('target', '_parent');
	});

	$('body').on('mouseenter', '.element_content', function (e) {
		var row_offset = $(this).closest('.row').offset();
		$(this).find('.ce_editbar').each(function (index) {
			var el_offset = $(this).offset();
			if (el_offset.top < row_offset.top) {
				$(this).css({'top': '0'});
			} else {
				$(this).css({'top': '-30px'});
			}
		});
	});

});


function mwGetIframeContent()
{
	return window;
}


function mw_init_elements_scripts(target) {
	//console.log(target+' .open_element_lightbox');
	mw_init_element_popup(target + ' .open_element_lightbox');
	mw_init_video_popup(target + ' .open_video_lightbox');
	mw_init_basic_popup(target + ' .open_lightbox');
	mw_init_countdown(target + ' .ve_countdown')
}

function mw_init_facebook() {
	FB.XFBML.parse();
}

function mw_init_video_popup(target) {
	jQuery(target).mwVideoPopup();
}

function mw_init_element_popup(target) {
	jQuery(target).mwElementPopup();
}

function mw_init_basic_popup(target) {
	jQuery(target).colorbox({maxWidth: "90%", maxHeight: "90%"});
}

function mw_init_mw_popup(target) {
	jQuery(target).mwPopup();
}

function set_miocarousel(target) {
	jQuery(target).MioCarousel();
}

function setBackgroundVideo(target) {
	jQuery(target).mwBackgroundVideo();
}

function mw_init_countdown(target) {
	jQuery(target).countdown();
}

function mw_init_socials(twitter = false, linkedin = false) {
	var scheme = /^http:/.test(window.location) ? 'http' : 'https';

	if (twitter) {
		jQuery.getScript(scheme + "://platform.twitter.com/widgets.js");
	}
	if (linkedin) {
		jQuery.getScript(scheme + "://platform.linkedin.com/in.js");
	}
}

function mw_init_sorting() {
	var speed = 50;
	if (('ontouchstart' in document.documentElement))
		speed = 30;
	jQuery(".element_container").draggable({
		handle: ".ece_move",
		revert: "invalid",
		helper: function (event) {
			return jQuery('<div class="mw_move_element_helper" data-element="' + jQuery(this).find('.ece_move').attr('data-element') + '"><svg role="img"><use xlink:href="' + jQuery(this).find('.ece_move').attr('data-icon') + '"></use></svg></div>');
		},
		cursorAt: {top: 0, left: 0},
		containment: "document",
		scrollSensitivity: 100,
		scrollSpeed: speed,
		cursor: "move",
		start: function (event, ui) {

			jQuery('body').addClass('mw_is_dragged');
			jQuery('body').addClass('mw_is_dragged_element');
			//ui.helper.hide();
			jQuery(this).hide();
			jQuery(this).closest('.elements_container').addClass('mw_element_moved_from');

		},
		stop: function (event, ui) {

			jQuery('body').removeClass('mw_is_dragged mw_is_dragged_element mw_is_dragged_row');
			jQuery(this).fadeIn();
			jQuery(this).closest('.elements_container').removeClass('empty_container');
			if (!jQuery('.mw_element_moved_from .element_container').length) jQuery('.mw_element_moved_from').addClass('empty_container');
			jQuery('.mw_element_moved_from').removeClass('mw_element_moved_from');

		}
	});

	jQuery(".row").draggable({
		handle: ".row_move",
		revert: "invalid",
		helper: function (event) {
			return jQuery('<div class="mw_move_row_helper"></div>');
		},
		cursorAt: {top: -10, left: -10},
		containment: "document",
		cursor: "move",
		scrollSensitivity: 100,
		scrollSpeed: speed,
		start: function (event, ui) {

			jQuery('body').addClass('mw_is_dragged');
			jQuery('body').addClass('mw_is_dragged_row');
			jQuery(this).hide();

		},
		stop: function (event, ui) {
			jQuery('body').removeClass('mw_is_dragged mw_is_dragged_element mw_is_dragged_row');
			jQuery(this).fadeIn();
		}
	});


	// droppable
	var droppableSetting = {
		accept: ".element_container, .row",
		hoverClass: "ui-droppable-hover",
		tolerance: "pointer",
		drop: function (event, ui) {

			if (ui.draggable.hasClass('row')) {
				if (jQuery(this).attr('data-group') == 'row') {
					if (jQuery(this).closest('.row').length) {
						var insertAfter = jQuery(this).closest('.row');
					} else {
						var insertAfter = jQuery(this);
					}

					ui.draggable.insertAfter(insertAfter);
					setForTransparentHeader();

				}
			} else {

				// protection for put subelement to subelement
				if (jQuery(this).closest('.subcol').length) {
					var element_name = ui.helper.attr('data-element');
					if (element_name == 'box' || element_name == 'twocols') {
						alert(texts.combine_subelements);
						return false;
					}
				}

				if (jQuery(this).attr('data-group') == 'element') {
					if (jQuery(this).closest('.element_container').length) {
						var insertAfter = jQuery(this).closest('.element_container');
					} else {
						var insertAfter = jQuery(this);
					}
					ui.draggable.insertAfter(insertAfter);
				} else if (jQuery(this).attr('data-group') == 'subelement') {
					if (jQuery(this).closest('.subcol .element_container').length) {
						var insertAfter = jQuery(this).closest('.subcol .element_container');
					} else {
						var insertAfter = jQuery(this);
					}
					ui.draggable.insertAfter(insertAfter);
				}
			}

			jQuery('body').attr('data-modified', '1');
			jQuery(window.frameElement).parent().closest('.mw_page_builder').find('.mw_save_page').addClass('is_edited');

		}
	};

	jQuery(".mw_page_builder_droparea").droppable(droppableSetting);
}

function setForTransparentHeader(change = false, check = false) {

	if (jQuery('#header').hasClass('mw_transparent_header') && !jQuery('#header').hasClass('ve_fixed_header_scrolled')) {
		var header_height = jQuery('#header').height();
		if (!header_height) header_height = 0;
		jQuery('#wrapper > .visual_content .mw_page_builder_content .row > .row_edit_container').css('top', 10);
		jQuery('#wrapper > .visual_content .mw_page_builder_content .row > .row_fix_width').css('paddingTop', 0);
		jQuery('#wrapper > .visual_content .mw_page_builder_content .row .miocarousel .row_fix_width').css('paddingTop', 0);
		jQuery('#wrapper > .visual_content:not(.mw_transparent_header_padding) .mw_page_builder_content .row:first > .row_edit_container').css('top', (header_height + 10));
		jQuery('#wrapper > .visual_content:not(.mw_transparent_header_padding) .mw_page_builder_content .row:first > .row_fix_width').css('paddingTop', (header_height - 20));
		jQuery('#wrapper > .visual_content:not(.mw_transparent_header_padding) .mw_page_builder_content .row:first .miocarousel .row_fix_width').css('paddingTop', (header_height + 10));
		jQuery('#wrapper > .visual_content:not(.empty_content):not(.mw_transparent_header_padding) .mw_page_builder_content > .mw_page_builder_droparea_row').css('top', (header_height - 20));
		jQuery('#wrapper > .empty_content .mw_page_builder_content > .mw_page_builder_droparea_row').css('top', 0);
		jQuery('#wrapper > .visual_content, .mw_transparent_header_padding').css('paddingTop', 0);
		jQuery('#wrapper > .empty_content, .mw_transparent_header_padding').css('paddingTop', header_height);

		console.log('setForTransparent2');
		//mw_header_height is defined in footer in pagedisplay_class
		if (check) {
			if (header_height != mw_header_height) {
				jQuery.post(ajaxurl, {
					'action': 'save_header_height',
					'header_height': header_height,
					'post_id': mw_page_id,
					'used_header': mw_used_header
				}, function (data) {
					console.log(data);
				});
			}
			console.log('real height: ' + header_height);
			console.log('option height: ' + mw_header_height);
		}
	} else if (change) {
		unsetForTransparentHeader();
	}
}

function unsetForTransparentHeader() {
	jQuery('#wrapper > .visual_content .mw_page_builder_content .row > .row_edit_container').css('top', 10);
	jQuery('#wrapper > .visual_content .mw_page_builder_content .row > .row_fix_width, #wrapper > .visual_content, .mw_transparent_header_padding').css('paddingTop', 0);
	jQuery('#wrapper > .visual_content:not(.mw_transparent_header_padding) .mw_page_builder_content .row:first .miocarousel .row_fix_width').css('paddingTop', 0);
	jQuery('#wrapper > .visual_content:not(.empty_content) .mw_page_builder_content > .mw_page_builder_droparea_row').css('top', -30);
	console.log('unsetForTransparent');
}

function mw_init_tinymce() {
	//title
	mw_init_tinymce_editor({
		selector: '.mw_inline_editable_title',
		plugins: 'paste, textcolor, colorpicker, wordpress, wplink, wpdialogs, charmap, mioweb',
		toolbar: 'formatselect | bold italic | alignleft aligncenter alignright | mw_adv',
		toolbar2: "strikethrough underline forecolor backcolor | link unlink | superscript subscript | charmap",
	});

	// text
	mw_init_tinymce_editor({});

	// simple text
	mw_init_tinymce_editor({
		selector: '.mw_inline_editable_simple_text',
		plugins: 'lists, paste, textcolor, colorpicker,wordpress,wplink,wpdialogs,charmap,mioweb', // , code nonbreaking
		toolbar: 'bold italic strikethrough underline | link unlink | bullist numlist | mw_adv', // | code
		toolbar2: "formatselect | forecolor backcolor | superscript subscript | outdent indent charmap",
	});

	// simple title
	mw_init_tinymce_editor({
		selector: '.mw_inline_editable_simple_title',
		plugins: 'paste, textcolor, colorpicker,wordpress,wplink,wpdialogs,charmap,mioweb', // , code nonbreaking
		toolbar: 'bold italic underline | link unlink | mw_adv', // | code
		toolbar2: "strikethrough forecolor backcolor | superscript subscript | charmap",
	});

}

function mw_destroy_tinymce(id = '') {
	//tinymce.EditorManager.execCommand('mceRemoveEditor', true, '.mw_inline_editable_title');
	//tinymce.execCommand('mceRemoveEditor', true, ".mw_inline_editable_title");
	//tinymce.EditorManager.execCommand('mceAddEditor', true, ".mw_inline_editable_title");

	tinymce.remove(id + '.mw_inline_editable_text');
	tinymce.remove(id + '.mw_inline_editable_title');
	tinymce.remove(id + '.mw_inline_editable_simple_text');
}

function mw_init_tinymce_editor(setting) {

	var defaults = {
		selector: '.mw_inline_editable_text',
		plugins: 'lists, paste, textcolor, colorpicker,wordpress,wplink,wpdialogs,charmap,mioweb', // , code nonbreaking
		toolbar: 'bold italic strikethrough | alignleft aligncenter alignright | link unlink | bullist numlist | mw_adv', // | code
		toolbar2: "formatselect | underline forecolor backcolor | superscript subscript | outdent indent charmap",
	};

	//console.log(tinyMCEPreInit); // generated by wp_editor in pagedisplay_class footer

	setting = jQuery.extend({}, defaults, setting);

	tinymce.init({
		selector: setting.selector,
		inline: true,
		menubar: false,
		convert_urls: false,
		entity_encoding: "raw",
		paste_as_text: true,
		preview_styles: false,
		inline_boundaries: false,
		plugins: setting.plugins,
		skin_url: texts.weburl + '/library/visualeditor/includes/tinymce/mioweb',
		skin: 'mioweb',
		block_formats: 'Odstavec=p;Nadpis 1=h1;Nadpis 2=h2;Nadpis 3=h3;Nadpis 4=h4;Nadpis 5=h5;Nadpis 6=h6',
		toolbar1: setting.toolbar,
		toolbar2: setting.toolbar2,
		external_plugins: {
			'mioweb': texts.weburl + '/library/visualeditor/includes/tinymce/mwplugin.js',
		},
		setup: function (editor) {
			editor.on('init', function (e) {
				var content = editor.getContent({format: 'text'}) || '';
				if (content.trim() === '') {
					editor.setContent('<p class="mw_editable_text_info">Začněte psát zde...</p>', {format: 'raw'});
				}
			});
		},
		init_instance_callback: function (editor) {
			editor.on('focus', function () {
				const placeholder = jQuery('#' + editor.id + ' .mw_editable_text_info');

				if (placeholder.length > 0) {
					editor.setContent('');
				}

				// click outside of iframe

				editor.getWin().onblur = function () {
					if (tinymce.EditorManager.activeEditor) {
						mw_save_tiny_mce_content(editor);
						tinymce.EditorManager.activeEditor.getElement().blur();
					}
				};

				// hide toolbar
				if (getUserSetting('hidetb', '0') === '0') {

					var toolbars,
						position,
						panel_id = '#' + editor.theme.panel._id;

					toolbars = editor.theme.panel.find('.toolbar:not(.menubar)');
					if (toolbars.length > 1) {
						toolbars[1].hide();
					}

					position = jQuery('#' + editor.id).offset();

					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id), 'top', position.top - 34);

					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id), 'height', 32);
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id + ' .mce-container-body.mce-stack-layout'), 'height', 32);
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id + ' .mce-container-body.mce-abs-layout'), 'height', 32);

					setUserSetting('hidetb', '0');

				}

			});

			// Change body position to "static" to work around issue https://github.com/tinymce/tinymce/issues/3248
			var origBodyPosition = jQuery('body').css('position') || 'relative';
			var origColorPickerCallback = editor.settings.color_picker_callback || null;
			if (origColorPickerCallback) {
				editor.settings.color_picker_callback = function (callback, value) {
					origColorPickerCallback(callback, value);
					jQuery('body').css('position', 'static');
				};
			}

			editor.on('change', function () {
				jQuery('body').attr('data-modified', '1');
				jQuery(window.frameElement).parent().closest('.mw_page_builder').find('.mw_save_page').addClass('is_edited');
				jQuery('#' + editor.id).addClass('is_edited');
			});
			editor.on('blur', function () {
				mw_save_tiny_mce_content(editor);
			});
			editor.on('blur change click', function () {
				jQuery('body').css('position', origBodyPosition); // Set position back to "relative" (default)
			});
		},
	});
}

function mw_save_tiny_mce_content(editor) {
	if (jQuery('#' + editor.id).hasClass('is_edited')) {

		var el_id = jQuery('#' + editor.id).closest('.element_container').attr('id');
		var set = mw_setting_container[el_id];
		var target = jQuery('#' + editor.id).attr('data-target');

		jQuery('#' + editor.id).removeClass('is_edited');

		set['style'][target] = editor.getContent();
		mw_setting_container[el_id] = set;
		//textarea.val(JSON.stringify(set));
		console.log('text saved');
		console.log(mw_setting_container[el_id]);
	}

	var content = editor.getContent({format: 'text'}) || '';
	if (content.trim() === '') {
		editor.setContent('<p class="mw_editable_text_info">Začněte psát zde...</p>', {format: 'raw'});
	}
}
