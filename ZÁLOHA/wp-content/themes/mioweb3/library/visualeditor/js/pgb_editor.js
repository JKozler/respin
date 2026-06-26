jQuery(function ($) {


	$('.mw_page_builder').mwPageBuilder();

	// editor fields in wp
	$('.postbox .mw_onedit_action').mwOnEditAction();

	/* old intro popup
    $('.start_intro_tutorial').mwLightbox({
        type: 'fixed',
        footer: false,
        onopen: function(lightbox){
            $.post(ajaxurl, {"action":"intro_open_tutorial"}, function(content) {
                lightbox.addContent(content);
                lightbox.$lightbox.find('.mw_tutorial_close_but').click(function(){
                    lightbox.close();
                });
            });

        },
        onclose: function(lightbox){
            $.post(ajaxurl, {"action":"intro_save_tutorial","id": 'intro_to_mw'}, function(data) {});
        },

    }); */

	$(".mw_tooltip").mwTooltip();

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

	var MwPageBuilder = (function (element, settings) {

		function _MwPageBuilder(element, settings) {

			this.defaults = {
				frame: '#mw_page_builder',
				type: 'row',
				saveto: 'var',
				target: '',
				editable: $(element).attr('data-editable')
			};

			this.settings = $.extend({}, this, this.defaults, settings);

			$(this.settings.frame).attr('src', $(this.settings.frame).attr('data-src') + window.location.hash);

			this.edit_settings = {}; // currently editing setting
			this.editing = false; // if is something editing

			this.opened_lightbox = null;

			this.$el = $(element);
			this.post_id = $("#save_id").val();
			this.page_type = $("#ve_page_type").val();
			this.modul_type = $("#ve_modul_type").val();
			this.loaded = false;

			this.revision_frame = $('#mw_page_builder_revisions');

			var builder = this;

			// new version popup
			$('.mioweb_new_version_info').mwLightbox({
				type: 'fixed',
				footer: false,
				onopen: function (lightbox) {
					var content = $('.mioweb_new_version_info_popup').html();
					lightbox.addContent(content);

				},
			});

			// after load iframe
			$(this.settings.frame).load(function () {

				builder.loaded = true;

				// iframe
				builder.$iframe = $(builder.settings.frame).contents();
				// builder content
				builder.pb_content = builder.$iframe.find(".mw_page_builder_content");
				// iframe body
				builder.pb_body = builder.$iframe.find("body");
				// post id
				//builder.post_id=builder.pb_body.attr('');

				builder.$el.removeClass('mw_page_builder_busy mw_page_builder_loading');

				// alert before leaving page
				window.onbeforeunload = function () {
					if (builder.isContentEdited()) {
						return texts.before_leave_page;
					}
				}

				//console.log(document.getElementById('mw_page_builder').contentWindow.mw_setting_container);

				builder.setting_container = document.getElementById('mw_page_builder').contentWindow.mw_setting_container;

				// on click to iframe body
				if (builder.settings.editable == '1') {
					builder.$iframe.find("html").click(function (e) {

						var target = $(e.target);

						//if(target.closest('#wrapper').length) {
						if (target.closest('.element_container_edited').length && builder.editing && builder.edit_settings.type == 'element') {
							// do nothing
							//} else if(target.closest('.mce-content-body').length) {
							//builder.openElementSet(target.closest('.element_container').find('.ece_edit'));
						} else {
							if (builder.editing) {
								$.when(
									builder.saveSetting()
								).then(
									builder.closePanelSetting()
								);
							}
							builder.closeRevisions();
							builder.closeRowLayouts();
							builder.switchTo('.mw_admin_content_tab', false);
							builder.panelIsUsing(0);

						}
						//}

					});
				}

				// click on plus
				builder.pb_content.on("click", ".mw_page_builder_droparea_element", function (e) {
					builder.switchTo('.mw_admin_elements_tab');
					e.preventDefault();
				});

				// open link in builder window

				builder.pb_body.on('click', 'a', function (e) {
					if (e.shiftKey || e.ctrlKey || e.metaKey
						|| ($(this).attr('href') && ($(this).attr('href').indexOf('mailto:') == 0 || $(this).attr('href').indexOf('tel:') == 0))
						|| $(this).hasClass('open_lightbox')
						|| $(this).hasClass('open_added_lightbox')
						|| $(this).hasClass('open_video_lightbox')
						|| $(this).hasClass('open_element_lightbox')
						|| $(this).hasClass('open_mw_popup')
						|| $(this).hasClass('element_image_alert')
						|| $(this).hasClass('mw_scroll_tonext')
						|| $(this).hasClass('mws_cart_continue_but')
						|| $(this).hasClass('comment-reply-link')
						|| $(this).hasClass('mw_feature_icon')
						|| $(this).closest('.mce-edit-focus').length) {
					} else if ($(this).attr('target') != '_blank') {

						var link = $(this).attr('href');
						if (link) {

							link = link.replace('&mw_preview=1', '');
							link = link.replace('?mw_preview=1', '');

							if (link.indexOf('http') == 0 || link.indexOf('//') == 0 || link.indexOf('/') == 0) {
								window.location = link;
							}
							//if($(this).attr('href')){}
						}

						console.log('link is blocked / pgb_editor.js / line 151');

						e.preventDefault();
					}
				});

				// after load iframe content
				builder.onLoad();

				// Save page
				$(".mw_save_page").click(function (el) {
					var layer;
					if (builder.editing) {
						builder.saveSetting();
						builder.closePanelSetting();
						builder.switchTo('.mw_admin_content_tab');
					}
					layer = JSON.stringify(builder.getLayer('.mw_page_builder_content'));
					//console.log(JSON.stringify(layer));
					builder.$el.addClass('mw_page_builder_busy');
					$.post(ajaxurl, {
						"action": 'save_page',
						"layer": layer,
						"post_id": $("#save_id").val(),
						"page_type": $("#ve_page_type").val(),
						"new_save": '1'
					}, function (data) {
						builder.$el.removeClass('mw_page_builder_busy');
						builder.isEdited('0');
					})
						.fail(function (data) {
							console.log(data);
							builder.$el.removeClass('mw_page_builder_busy');
							// write error /// texts.storno_save_page_info
						});

					return false;
				});

				// draggable
				$(".mw_page_builder_draggable").draggable({
					revert: "invalid", // when not dropped, the item will revert back to its initial position
					containment: 'document',
					appendTo: 'body',
					iframeFix: true,
					helper: "clone",
					iframeScroll: true,
					scrollSensitivity: 100,
					scrollSpeed: 100,
					cursor: "move",
					cursorAt: {top: -5, left: -5},
					start: function (event, ui) {
						builder.pb_body.addClass('mw_is_dragged');

						if (ui.helper.attr('data-type') == 'element')
							builder.pb_body.addClass('mw_is_dragged_element');
						else {
							builder.pb_body.addClass('mw_is_dragged_row');
							//builder.closeRowLayouts();
							if (!('ontouchstart' in document.documentElement))
								builder.closeRowLayouts();

						}
					},
					stop: function (event, ui) {
						builder.pb_body.removeClass('mw_is_dragged mw_is_dragged_element mw_is_dragged_row');
					}
				});

				// droppable
				builder.droppableSetting = {
					accept: ".mw_page_builder_draggable",
					iframeFix: true,
					hoverClass: "ui-droppable-hover",
					tolerance: "pointer",
					drop: function (event, ui) {

						var type = ui.helper.attr('data-type');

						if (type == 'row') {
							if ($(this).attr('data-group') == 'row') {
								if ($(this).closest('.row').length) {
									var insertAfter = $(this).closest('.row');
								} else {
									var insertAfter = $(this);
								}
								addRow(ui.helper, insertAfter);
								builder.closeRowLayouts();
							}
						} else {

							// protection for put subelement to subelement
							if ($(this).closest('.subcol').length) {
								var element_name = ui.helper.attr('data-element');
								if (element_name == 'box' || element_name == 'twocols') {
									alert(texts.combine_subelements);
									return false;
								}
							}

							if ($(this).attr('data-group') == 'element') {
								if ($(this).closest('.element_container').length) {
									var insertAfter = $(this).closest('.element_container');
								} else {
									var insertAfter = $(this);
								}
								addElement(ui.helper, insertAfter);
								builder.resetElSearch();
							} else if ($(this).attr('data-group') == 'subelement') {

								if ($(this).closest('.subcol .element_container').length) {
									var insertAfter = $(this).closest('.subcol .element_container');
								} else {
									var insertAfter = $(this);
								}
								addElement(ui.helper, insertAfter);
								builder.resetElSearch();
							}
						}
					}
				};

				builder.$iframe.find(".mw_page_builder_droparea").droppable(builder.droppableSetting);

				function addRow($item, $after) {

					var type = $item.attr('data-rowtype');
					var content = $item.attr('data-content');

					builder.$iframe.find(".empty_content").removeClass('empty_content');
					$('<div id="new_row_container"><div class="cms_loading"></div></div>').insertAfter($after);

					$.ajax({
						type: 'POST',
						data: {
							"action": "add_new_row",
							"rowtype": type,
							"content": content,
							'post_id': builder.post_id,
						},
						url: ajaxurl,
						success: function (content) {
							var container = builder.$iframe.find("#new_row_container")
							container.replaceWith(content.row).fadeIn();

							$.each(content.settings, function (index, value) {
								builder.setting_container[index] = value;
							});

							builder.setActions([
								'setEdited',
								'setHeader',
								'initSorting',
								'droppable'
							]);

						}
					});
				}

				function addElement($item, $after) {

					var type = $item.attr('data-element');

					$after.closest('.elements_container').removeClass('empty_container');

					$('<div id="new_element_container"><div class="cms_loading"></div></div>').insertAfter($after);

					$.post(ajaxurl, 'action=create_element&newelement=1&element_type=' + type + '&post_id=' + builder.post_id, function (data) {
						builder.$iframe.find("#new_element_container").replaceWith(data.content);
						if (data.font) builder.$iframe.find("head").append("<link href='https://fonts.googleapis.com/css?family=" + data.font + "&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");

						builder.setting_container[data.newkey] = data.code;
						builder.setActions([
							'setEdited',
							'initSorting',
							'droppable'
						]);

						document.getElementById('mw_page_builder').contentWindow.mw_init_facebook();


					});

				}

				// Open row setting
				builder.pb_content.on('click', '.row_edit', function () {

					var element = $(this).closest('.row');

					// if is row edited
					if (element.hasClass('row_edited') && builder.editing && builder.edit_settings.type == 'row') {
						return false;
					} else if (builder.editing) { // if is something edited
						builder.saveSetting();
					}

					builder.$iframe.find('.element_container_edited').removeClass('element_container_edited');

					builder.$iframe.find(".row_edited").removeClass('row_edited');
					element.addClass('row_edited');

					var rowid = element.attr('id');
					//console.log(builder.setting_container);
					var code = builder.setting_container[rowid];

					builder.switchTo('.mw_admin_content_tab', false);

					builder.openPanelSetting();

					$.post(ajaxurl, {"action": "open_row_setting", "code": code, "row_id": rowid}, function (content) {

						builder.setPanelSettingContent(content);
						builder.working(0);

						builder.openEdit('.mw_editor_panel_content_setting', {
							target: '.row_edited',
						});


					});
					return false;
				});

				// row delete
				builder.pb_content.on("click", ".row_delete", function (e) {
					$(this).mwConfirm({
						text: texts.delete_row,
						show: true,
						onconfirm: function (confirm) {

							if (confirm.$el.closest('.row').hasClass('row_edited') && builder.editing && builder.edit_settings.type == 'row') {
								builder.closePanelSetting();
							} else if (confirm.$el.closest('.row').find('.element_container_edited') && builder.editing && builder.edit_settings.type == 'element') {
								builder.closePanelSetting();
							}

							var container = confirm.$el.closest('.visual_content');
							confirm.$el.closest('.row').slideUp('slow', function () {
								$(this).remove();
								if (!container.find('.row').length) {
									container.addClass('empty_content');
									builder.switchTo('.mw_admin_rows_tab');
								}
								builder.setForTransparentHeader();
							});
							builder.isEdited();

						}
					});
					e.preventDefault();
				});

				// Copy row
				builder.pb_content.on("click", ".row_copy", function (e) {
					var element = $(this).closest('.row');
					var oldid = element.attr('id');
					var newid = 'row_copy_' + $.now();
					var elid;
					var newelid;

					builder.setting_container[newid] = builder.cloneSet(builder.setting_container[oldid]);

					if (builder.editing) {
						$.when(
							builder.saveSetting()
						).then(
							builder.closePanelSetting()
						);
					}

					var newelement = element.clone();

					newelement.insertAfter(element);
					newelement.attr('id', newid);
					newelement.removeClass('row_edited');
					newelement.find('.element_container_edited').removeClass('element_container_edited');
					newelement.find('style').each(function () {
						$(this).attr('id', $(this).attr('id').replace(new RegExp(oldid, "gi"), newid));
						$(this).html($(this).html().replace(new RegExp(oldid, "g"), newid));
					});
					var i = 0;
					newelement.find('.element_container').each(function () {
						elid = $(this).attr('id');
						newelid = 'element_copy_' + i + '_' + $.now();
						$(this).attr('id', newelid);
						$(this).find('style').each(function () {
							$(this).attr('id', $(this).attr('id').replace(new RegExp(elid, "gi"), newelid));
							$(this).html($(this).html().replace(new RegExp(elid, "g"), newelid));
						});
						builder.setting_container[newelid] = builder.cloneSet(builder.setting_container[elid]);
						i++;
					});

					builder.setActions([
						'setEdited',
						'setHeader',
						'initSorting',
						'droppable',
						'elementScripts',
					]);

					if (newelement.find('.miocarousel').length) {
						newelement.find('.miocarousel .indicators').remove();
						document.getElementById("mw_page_builder").contentWindow.set_miocarousel('#' + newid + ' .miocarousel');
					}

					newelement.find('.mce-content-body').attr('id', '').removeClass('mce-content-body');
					document.getElementById('mw_page_builder').contentWindow.mw_init_tinymce();

					//e.preventDefault();
					return false;
				});

				// Copy row to memory
				builder.pb_content.on("click", ".row_copy_memory", function (el) {

					if (builder.editing) {
						$.when(
							builder.saveSetting()
						).then(
							builder.closePanelSetting()
						);
					}

					var element = $(this).closest('.row');
					var content = builder.setting_container[element.attr('id')];

					builder.$el.addClass('mw_page_builder_busy');

					var c = 0;
					var e = 0;
					var s = 0;
					var i = 0;
					var elements = [];
					var subelements = [];

					elements = [];
					subelements = [];
					c = 0;
					$(".sortable-col", element).not(".subcol", element).each(function () {
						elements[c] = [];
						subelements[c] = [];
						e = 0;
						$(".element_container", this).not(".subcol .element_container", this).each(function () {
							elements[c][e] = builder.setting_container[$(this).attr('id')];
							subelements[c][e] = [];
							subelements[c][e][0] = [];
							s = 0;
							$(".subcol-first .element_container", this).each(function () {
								subelements[c][e][0][s] = [];
								subelements[c][e][0][s] = builder.setting_container[$(this).attr('id')];
								s++;
							});
							subelements[c][e][1] = [];
							s = 0;
							$(".subcol-last .element_container", this).each(function () {
								subelements[c][e][1][s] = [];
								subelements[c][e][1][s] = builder.setting_container[$(this).attr('id')];
								s++;
							});
							e++;
						});
						c++;
					});

					$.ajax({
						type: 'POST',
						data: {"action": "copy_row", "row": JSON.stringify(content), "element": JSON.stringify(elements), "subelement": JSON.stringify(subelements)},
						url: ajaxurl,
						success: function (content) {

							$('.row_paste').show();
							builder.$el.removeClass('mw_page_builder_busy');
							$(".mw_clipboard_row_item").addClass('mw_clipboard_row_item_show');
						}

					});

					return false;
					//el.preventDefault();
				});

				////////////////////////////////////////////////////////////////////////////////////////////////////////////
				////////////////////////////////////////////////////////////////////////////////////////////////////////////
				////////////////////////////////////////////////////////////////////////////////////////////////////////////

				////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Zmena layoutu radku
				////////////////////////////////////////////////////////////////////////////////////////////////////////////
				const original_layout = {};
				let row_edit_container_timeout;
				let row_mouseleave_timeout;

				const render_row_layout = ($row, layout) => {
					const $row_content = $row.find('> .row_fix_width');
					const row_id = $row.attr('id');
					const original_first_break = original_layout[row_id].content.reduce((result, col, index) => result || (col.break === '1' ? index + 1 : result), 0);
					const first_break = layout.reduce((result, col, index) => result || (col.break === '1' ? index + 1 : result), 0);

					const original_offsets = {};
					const new_offsets = {};

					$row_content.find('> .col.sortable-col > .element_container').each((index, element) => {
						$(element).stop(true);

						original_offsets[index] = {
							offset: $(element).offset(),
							height: $(element).height(),
							width: $(element).width(),
						};
					});

					$row_content.children().remove();

					layout.forEach((col, index, array) => {
						const col_class = [
							'col',
							col.type,
							'col_' + row_id + '_' + index,
							'empty_col',
							(index === 0 || array[index - 1].break === '1' ? 'col-first' : ''),
							(index === array.length - 1 || col.break === '1' ? 'col-last' : ''),
							'sortable-col'
						].join(' ');

						$row_content.append($([
							'<div class="' + col_class + '">',
							'<div class="mw_page_builder_droparea mw_page_builder_droparea_element" data-group="element">',
							'<div class="mw_page_builder_emptyelement_info admin_feature">+</div>',
							'</div>',
							'</div>',
						].join('')));

						if (col.break === '1') {
							$row_content.append($('<div class="ve_row_break"></div>'));
						}
					});

					original_layout[row_id].elements.forEach((col_elements, index) => $row_content.find('.col_' + row_id + '_' + Math.max(0, Math.min(index + (first_break - original_first_break), layout.length - 1))).append(col_elements));

					$row_content.find('> .col.sortable-col > .element_container').each((index, element) => {
						const new_offset = $(element).offset();
						const new_height = $(element).height();
						const new_width = $(element).width();
						const old_offset = original_offsets[index].offset;
						const height = original_offsets[index].height;
						const width = original_offsets[index].width;
						const top = old_offset.top - new_offset.top;
						const left = old_offset.left - new_offset.left;

						$(element).css({top: top, left: left, height: height, width: width}).animate({
							top: 0,
							left: 0,
							height: new_height,
							width: new_width
						}, 200, () => {
							$(element).css({height: '', width: ''});
						});
					});


					builder.setActions([
						'setHeader',
						'initSorting',
						'droppable'
					]);
				};

				builder.pb_content.on("click", ".row_layout.row_edit_more_item", function (event) {
					const $this = $(this);
					const $row_edit_container = $this.closest('.row_edit_container');
					const $row_edit_more = $row_edit_container.find('.row_edit_more');
					const $row_layouts = $row_edit_container.find('.row_layouts');
					const $row = $this.closest('.row.row_content');
					const $row_content = $row.find('.row_fix_width');
					const $cols = $row_content.find('.col.sortable-col');
					const row_id = $row.attr('id');
					const element_layout = [...$cols].map(col => $(col).children('.element_container'));

					$row.addClass('row_layout_edited');

					$row_layouts.toggleClass('hidden');
					$row_edit_more[($row_layouts.hasClass('hidden') ? 'add' : 'remove') + 'Class']('cms_nodisp');

					original_layout[row_id] = {
						'content': builder.setting_container[row_id].content,
						'elements': element_layout,
					};
				});

				builder.pb_content.on("mouseenter", ".row_layouts > .row_layout", function (event) {
					const $this = $(this);
					const $row = $this.closest('.row.row_content');

					if ($row.length !== 1) {
						return;
					}

					const layout = $this.data('content').split('-').map(col_type => {
						return {
							'type': 'col-' + (col_type.slice(-1) === '/' ? col_type.slice(0, -1) : col_type),
							'class': '',
							'break': (col_type.slice(-1) === '/' ? '1' : undefined),
						};
					});

					clearTimeout(row_mouseleave_timeout);
					render_row_layout($row, layout);
				});

				builder.pb_content.on("mouseleave", ".row_layouts > .row_layout", function (event) {
					const $this = $(this);
					const $row = $this.closest('.row.row_content');

					if ($row.length !== 1) {
						return;
					}

					const layout = builder.setting_container[$row.attr('id')].content;

					row_mouseleave_timeout = setTimeout(render_row_layout.bind(window, $row, layout), 10);
				});

				builder.pb_content.on("click", ".row_layouts > .row_layout", function (event) {
					const $this = $(this);
					const $row = $this.closest('.row.row_content');
					const $row_edit_container = $this.closest('.row_edit_container');

					if ($row.length !== 1) {
						return;
					}

					const row_id = $row.attr('id');

					builder.setting_container[row_id].content = $this.data('content').split('-').map(col_type => {
						return {
							'type': 'col-' + (col_type.slice(-1) === '/' ? col_type.slice(0, -1) : col_type),
							'class': '',
							'break': (col_type.slice(-1) === '/' ? '1' : undefined),
						};
					});

					builder.isEdited();

					$row_edit_container.find('.row_edit_more').addClass('cms_nodisp');
					$row_edit_container.find('.row_layouts').addClass('hidden');
				});

				builder.pb_content.on("mouseover", ".row_edit_container", function (event) {
					clearTimeout(row_edit_container_timeout);
				});

				builder.pb_content.on("mouseout", ".row_edit_container", function (event) {
					var $row_edit_container = $(this);
					const $row = $row_edit_container.closest('.row.row_content');

					row_edit_container_timeout = setTimeout(() => {
						$row.removeClass('row_layout_edited');
						$row_edit_container.find('.row_edit_more').addClass('cms_nodisp');
						$row_edit_container.find('.row_layouts').addClass('hidden');
					}, 300);
				});

				////////////////////////////////////////////////////////////////////////////////////////////////////////////
				////////////////////////////////////////////////////////////////////////////////////////////////////////////
				////////////////////////////////////////////////////////////////////////////////////////////////////////////

				$('.mw_clipboard_row_item_close').click(function (e) {
					$(".mw_clipboard_row_item").removeClass('mw_clipboard_row_item_show');
					$.ajax({
						type: 'POST',
						data: {"action": "clear_clipboard"},
						url: ajaxurl,
						success: function (content) {
						}
					});
					e.preventDefault();
				});

				// Open element setting
				builder.pb_content.on("click", ".ece_edit", function (e) {

					builder.openElementSet($(this));
					builder.panelIsUsing(1);

					return false;
				});

				builder.pb_content.on("click", ".ece_delete", function (e) {
					$(this).mwConfirm({
						text: texts.delete_element,
						show: true,
						onconfirm: function (confirm) {

							if (confirm.$el.closest('.element_container').hasClass('element_container_edited') && builder.editing && builder.edit_settings.type == 'element')
								builder.closePanelSetting();

							var container = confirm.$el.closest('.elements_container');

							confirm.$el.closest('.element_container').slideUp(function () {
								$(this).remove();
								if (!container.find('.element_container').length) container.addClass('empty_container');
							});
							builder.isEdited();
						}
					});
					e.preventDefault();
				});

				// Copy element
				builder.pb_content.on("click", ".ece_copy", function (e) {

					if (builder.editing) {
						$.when(
							builder.saveSetting()
						).then(
							builder.closePanelSetting()
						);
					}

					var element = $(this).closest('.element_container');

					if (element.find('input[name="element_id"]').val()) {
						alert(texts.element_copy);
					} else {
						var oldid = element.attr('id');
						var newid = 'element_copy_' + $.now();
						var newelement = element.clone();
						var subnewid;
						newelement.insertAfter(element);
						newelement.attr('id', newid);
						newelement.removeClass('element_container_edited');
						newelement.html(newelement.html().replace(new RegExp(oldid, "gi"), newid));

						if (element.find('.element_container').length && element.hasClass('subelement_container')) {
							var suboldid;
							var i = 0;
							element.find('.element_container').each(function () {
								suboldid = $(this).attr('id');

								subnewid = 'element_copy_' + i + '_' + $.now();

								newelement.html(newelement.html().replace(new RegExp(suboldid, "gi"), subnewid));
								var set = builder.setting_container[suboldid];
								if (set !== undefined) {
									builder.setting_container[subnewid] = builder.cloneSet(builder.setting_container[suboldid]);
								}
								i++;
							});
						}

						builder.setting_container[newid] = builder.cloneSet(builder.setting_container[oldid]);

						//console.log(builder.setting_container);

						builder.setActions([
							'setEdited',
							'initSorting',
							'droppable',
							'elementScripts',
						], '#' + newid);

						if (newelement.find('.miocarousel').length) {
							newelement.find('.miocarousel .indicators').remove();
							document.getElementById("mw_page_builder").contentWindow.set_miocarousel('#' + newid + ' .miocarousel');
						}

						newelement.find('.mce-content-body').attr('id', '').removeClass('mce-content-body');
						document.getElementById('mw_page_builder').contentWindow.mw_init_tinymce();

					}

					return false;
					//e.preventDefault();
				});

				builder.$iframe.find("body").on("click", ".mw_edit_option_onpage", function () {

					var edit = $(this).attr('data-edit');
					var itemid = $(this).attr('data-itemid');
					var title = $(this).attr('data-title');
					var objectid = $(this).attr('data-objectid');
					var target;
					var reload;
					var setid;

					if (edit == 'header') {
						target = '#header';
						reload = 'header';
						setid = 've_header';
					} else if (edit == 'footer') {
						target = '#footer';
						reload = 'footer';
						setid = 've_footer';
					}

					// if already opened
					if (builder.editing && builder.edit_settings.type == edit) {
						return false;
					} else if (builder.editing) {
						builder.saveSetting();
					}

					builder.openPanelSetting();

					var setting = $(this).attr('data-setting');

					$.post(ajaxurl, {
						"action": "openSettingInpanel",
						"item_id": itemid,
						"set_id": setid,
						"object_id": objectid,
						"modul_type": builder.modul_type,
						"title": title,
						"show_switch": 0,
					}, function (content) {

						builder.setPanelSettingContent(content);
						builder.working(0);

						builder.openEdit('.mw_editor_panel_content_setting', {
							target: target,
							type: reload,
							saveto: 'option',
							set_is_edited: false,
						});

					});

					return false;

				});

				builder.$iframe.find("body").on('click', '.ve_open_weditor_setting', function () {
					$(this).mwLightbox({
						type: 'fixed',
						show: true,
						onopen: function (lightbox) {

							var option = lightbox.$el.attr('data-option');
							var key = lightbox.$el.attr('data-key');
							var postid = lightbox.$el.attr('data-postid');
							var type = lightbox.$el.attr('data-type');

							var container = lightbox.$el.closest('.weditor_content_container');
							builder.$iframe.find(".weditor_edited").removeClass('weditor_edited');
							container.addClass('weditor_edited');

							$.post(ajaxurl, {
								"action": "ve_open_weditor_setting",
								"postid": postid,
								"option": option,
								"key": key,
								"type": type
							}, function (data) {
								lightbox.addContent(data);
								lightbox.$lightbox.find('.ve_windowselect_container').mwWeditorManager({
									change: function () {
										lightbox.setEdited();
									}
								});

							});

						},
						onsave: function (lightbox) {

							var form = lightbox.getFormData();
							//console.log(form);

							$.post(ajaxurl, 'action=ve_save_weditor_setting&' + form, function (data) {

								builder.$iframe.find(".weditor_edited").replaceWith(data.content);
								builder.$iframe.find(".weditor_edited").removeClass('weditor_edited');

								lightbox.close();

								if (data.font) $("head").append("<link href='https://fonts.googleapis.com/css?family=" + data.font + "&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");

							});

							return false;

						}
					});

				});


			}); // end after loading iframe

			$(".mw_open_revisions").click(function (e) {
				if (builder.editing) {
					builder.saveSetting();
					builder.closePanelSetting();
				}
				var weditor = $(this).attr('data-weditor');
				builder.openRevisions(weditor);
				builder.panelIsUsing(1);
				e.preventDefault();
			});
			$(".mw_close_revisions").click(function () {
				builder.revision_frame.hide().attr('src', '');
				$(builder.settings.frame).show();
				$('.mw_revision_item').removeClass('active');
				$('.mw_save_revision').addClass('unactive');
				builder.closeRevisions();
				builder.panelIsUsing(0);
				return false;
			});
			$('.mw_editor_revision_list').on('click', '.mw_revision_item', function () {
				builder.revision_frame.show().attr('src', $(this).attr('data-rev'));
				$(builder.settings.frame).hide();
				builder.$el.addClass('mw_page_builder_busy mw_page_builder_loading');
				$('.mw_revision_item').removeClass('active');
				$(this).addClass('active');
				$('.mw_save_revision').removeClass('unactive');
				builder.revision_frame.load(function () {
					builder.$el.removeClass('mw_page_builder_busy mw_page_builder_loading');
				});
				return false;
			});
			$('.mw_editor_revision_list').on('click', '.mw_revision_storno', function () {
				builder.revision_frame.hide().attr('src', '');
				$(builder.settings.frame).show();
				$('.mw_revision_item').removeClass('active');
				$('.mw_save_revision').addClass('unactive');
				return false;
			});
			$(".mw_save_revision").click(function () {
				if (!$(this).hasClass('unactive')) {
					var id = $('.mw_revision_item.active').attr('data-rev-id');
					var type = $('.mw_revision_item.active').attr('data-rev-type');
					$('.mw_save_revision').addClass('unactive');
					$('.mw_revision_item').removeClass('active');
					builder.$el.removeClass('mw_page_builder_busy mw_page_builder_loading');
					if (id) {
						$.post(ajaxurl, {
							"action": "mw_save_revision",
							"rev_id": id,
							"rev_type": type
						}, function (content) {
							window.location.reload();
						}).fail(function () {
							console.log("error");
						});
					}

				}
				return false;
			});

			// hide / show editor left panel
			$('.mw_open_preview').click(function (e) {
				if (builder.loaded)
					builder.showPreview();
				e.preventDefault();
			});

			$('.mw_open_editing_on_mobile').click(function () {
				builder.panelIsUsing();
			});

			$('.ve_open_page_selector').click(function (e) {
				$("#ve_page_selector").animate({left: "0"}, 200);
				$("#ve_page_selector_bg").show();
				if (!('ontouchstart' in document.documentElement))
					$("#ve_page_search").focus();

				var container = $('#ve_page_selector_content');
				var module_type = container.attr('data-modul');
				var loaded = container.attr('data-loaded');

				if(!loaded) {
					$.post(ajaxurl, {
						"action": "load_page_selector_content",
						"modul_type": module_type,
						"post_id": builder.post_id,
					}, function (content) {
						$('#ve_page_selector_content').attr('data-loaded','1');
						$('#ve_page_selector_content').html(content);
						$('#ve_page_selector').mwPageSelector();
					});
				}

				e.preventDefault();
			});
			$('.ve_close_page_selector, #ve_page_selector_bg').click(function () {
				builder.closePageSelector();
				return false;
			});



			// tabs

			this.$el.on('click', ".mw_admin_tabs a", function () {
				var target = $(this).attr('href');
				var group = $(this).attr('data-group');
				$(".mw_editor_panel_content_in").scrollTop(0);
				$("." + group + "_tabs a").removeClass("active");
				$(this).addClass("active");
				$("." + group + "_tab").hide();
				$("." + group + '_' + target).show();
				if (!('ontouchstart' in document.documentElement))
					$(".mw_elements_search").focus();
				return false;
			});

			$(".mw_editor_panel_content_elements").mouseenter(function () {
				var pos = $('.mw_editor_panel_content_in').scrollTop();
				$(".mw_elements_search").focus();
				$('.mw_editor_panel_content_in').scrollTop(pos);
				//var position = document.getElementById('id').scrollTop;
			});

			//search elements
			if (!('ontouchstart' in document.documentElement))
				$(".mw_elements_search").val('').focus();

			$(".mw_elements_search").keyup(function () {
				var searched = $(this).val().toLowerCase();
				var i, j;
				var text;
				if (searched != "") {

					$(".add_element_item_c").hide();
					$(".mw_elements_group_container").show();

					$(".mw_element_search_icon").hide();
					$(".mw_element_search_storno").show();
					j = 0;
					$(".mw_elements_group_container").each(function () {
						i = 0;
						$(this).find('.add_element_item_c').each(function () {
							text = $(this).attr('data-name').toLowerCase();
							if (text.search(searched) >= 0) {
								$(this).show();
								i++;
							}
						});
						if (i == 0) $(this).hide();
						else j++
					});

					if (j == 0) $('.mw_elements_search_empty').show();
					else $('.mw_elements_search_empty').hide();


				} else {
					$(".add_element_item_c").show();
					$(".mw_elements_group_container").show();
					$(".mw_element_search_icon").show();
					$(".mw_element_search_storno").hide();
				}

				$('.mw_editor_panel_content_in').scrollTop(0);
			});
			$(".mw_element_search_storno").click(function () {
				builder.resetElSearch();
			});
			$(".mw_element_search_icon").click(function () {
				$(".mw_elements_search").focus();
			});


			// setting
			// **************************************************************************************

			// save button
			$(".mw_save_setting").click(function () {
				//console.log('save');
				builder.saveSetting();
				builder.closePanelSetting();

				return false;
			});
			// storno button
			$(".mw_storno_setting").click(function () {

				builder.reload(builder.storno_code, false);
				builder.closePanelSetting();

				return false;
			});

			// Open setting
			$(".ve_editor_panel").on('click', '.ve_open_setting', function () {
				var opened_lightbox;
				$(this).mwLightbox({
					type: 'window',
					show: true,
					help_link: $(this).attr('data-help'),
					beforeopen: function (lightbox) {
						if ($('.mw_builder_setting_window').length) {
							if ($('.mw_builder_setting_window').attr('data-edited') == '1') {
								lightbox.settings.show_confirm = true;
							}
						}
					},
					onopen: function (lightbox) {

						builder.opened_lightbox = lightbox;

						var itemid = lightbox.$el.attr('data-itemid');
						var objectid = lightbox.$el.attr('data-id');
						setid = lightbox.$el.attr('data-setid');

						$('.mw_editor_panel_bottom').hide();
						$('.mw_editor_panel_menu a').removeClass('active');
						lightbox.$el.addClass('active');

						var layer;
						layer = JSON.stringify(builder.getLayer('.mw_page_builder_content'));

						$.post(ajaxurl, {
							"action": "openPopSetting",
							"item_id": itemid,
							"set_id": setid,
							"object_id": objectid,
							"edited": builder.isContentEdited(),
							"layer": layer
						}, function (content) {
							lightbox.addContent(content);
							lightbox.$lightbox.find('.mw_onedit_action').mwOnEditAction({
								change: function (field_setting) {
									lightbox.setEdited();
								}
							});
							// status select
							lightbox.$lightbox.find(".mw_setting_sidebar_visibility").mwStatusSelect({
								onchange: function (val, status) {

									if(val == 'password_protected')
									{
										lightbox.$lightbox.find('.mw_setting_password_protected_container').show();
										lightbox.$lightbox.find('.mw_setting_password_protected_container input').focus();
									}
									else
									{
										lightbox.$lightbox.find('.mw_setting_password_protected_container').hide();
										lightbox.$lightbox.find('.mw_setting_password_protected_container input').val('');
									}

									lightbox.setEdited();
								}
							});
						});
					},
					onclose: function (lightbox) {
						$('.mw_editor_panel_bottom').show();
						$('.mw_editor_panel_menu a').removeClass('active');

						builder.opened_lightbox = null;
					},
					onsave: function (lightbox) {

						if (lightbox.checkForm()) {
							var formdata = lightbox.$lightbox.find('form').serialize();
							builder.$el.addClass('mw_page_builder_busy mw_page_builder_loading');

							$.post(ajaxurl, 'action=mwSaveObjectSet&' + formdata, function (data) {
								//console.log(data);
								if (data.redirect) {
									window.location = data.redirect;
								} else {
									lightbox.$lightbox.find('.mw_messages_container').html(data.html);
									lightbox.removeLoading();
									if (data.success) {
										builder.loaded = false;
										builder.$iframe.find(".mw_page_builder_droparea").droppable("destroy"); // without it chrome dont work after refresh iframe
										document.getElementById('mw_page_builder').contentWindow.location.reload();
									} else {
										lightbox.$lightbox.find('.connection_status_container').addClass('connection_status_0').removeClass('connection_status_1');
										lightbox.$lightbox.find('.connection_status_invalid_error').hide();
									}

								}

							}).fail(function (data) {
								console.log(data);
								lightbox.$lightbox.removeClass('loading');
								builder.$el.removeClass('mw_page_builder_loading');
								console.log("error");
							});
						} else {
							lightbox.removeLoading();
						}

					}
				});
			});

			$(".ve_open_setting_inpanel").click(function () {

				var show_confirm = false;
				var el = $(this);

				if (builder.opened_lightbox != null && builder.opened_lightbox.isEdited()) {
					show_confirm = true;
				}

				if (show_confirm) {
					el.mwConfirm({
						text: texts.close_confirm,
						show: true,
						but_text: texts.continue,
						onconfirm: function (confirm) {
							builder.opened_lightbox.executeClose();
							builder.openSettingInPanel(el);
						}
					});
				} else {
					if (builder.opened_lightbox != null) builder.opened_lightbox.executeClose();
					builder.openSettingInPanel(el);
				}

				return false;
			});

			// Change page template
			$(".mw_change_template").mwLightbox({
				type: 'window',
				onopen: function (lightbox) {

					builder.opened_lightbox = lightbox;

					var itemid = lightbox.$el.attr('data-itemid');
					var objectid = lightbox.$el.attr('data-id');

					$('.show-hide-panel').hide();
					$('.mw_editor_panel_setting a').removeClass('active');
					lightbox.$el.addClass('active');

					$.post(ajaxurl, {action: 'mw_change_template', item_id: itemid, object_id: objectid}, function (content) {
						lightbox.addContent(content);
						$('.mw_page_template_selector').mwTemplateSelector();
					});
				},
				onclose: function (lightbox) {
					$('.show-hide-panel').show();
					$('.mw_editor_panel_setting a').removeClass('active');
					builder.opened_lightbox = null;
				},
				onsave: function (lightbox) {

					if (!lightbox.$lightbox.find("#keep_content").prop('checked')) {

						lightbox.removeLoading();

						lightbox.$lightbox.find('.mw_save_builder_setting_window').mwConfirm({
							text: texts.change_theme_confirm,
							but_text: texts.continue,
							show: true,
							onconfirm: function (confirm) {
								lightbox.sendForm();
								lightbox.addLoading();
							}
						});

					} else {
						lightbox.sendForm();
					}
				}
			});

			$(".mw_row_group_title").click(function () {

				var el = $(this);
				var id = el.attr('data-group');

				builder.closeRowLayouts();

				//$(".mw_row_layouts_bg").show();
				$(".mw_row_layouts_container_" + id).addClass('opened');
				el.addClass('active');

				return false;
			});
			$(".mw_row_layouts").click(function (event) {

				return false;

			});
			$(".ve_editor_panel:not(.mw_row_layouts), .ve_editor_top_panel").click(function (event) {

				builder.closeRowLayouts();

			});

			// Change weditor title
			$(".ve_change_weditor_title").click(function(){
				$(this).mwModal({
					title: $(this).attr('title'),
					onopen: function (modal) {

						modal.addContent($("#mw_change_weditor_title_container").html());
						modal.$modal.find(".mw_input_weditor_name").val($("#weditor_post_title").val());
						setTimeout(function () {
							modal.$modal.find(".mw_input_weditor_name").focus();
						}, 150);

					},
					onsave: function (modal) {

						if (modal.checkForm()) {
							var name = modal.$modal.find(".mw_input_weditor_name").val();

							$.post(ajaxurl, {
								"action": "ve_change_weditor_title",
								"title": name,
								"postid": modal.$el.attr('data-itemid')
							}, function (data) {
								$("#weditor_post_title").val(name);
							});

							modal.close();
						}

						return false;

					}
				});
			});

			// installator

			$(".create-new-page").click(function () {

				$(this).mwOpenInstallator({
					install: 'object',
					objectid: 'page',
					front_redirect: 1,
				});

				return false;
			});

			// duplicate object

			$(".mw_duplicate_page").click(function(){
				var objectId = $(this).attr('data-objectid');
				var itemId = $(this).attr('data-id');
				$(this).mwOpenFastCopyModal({
					action: 'mwSaveFastCopyObject',
					objectId: objectId,
					itemId: itemId,
					onsave: function (data) {
						window.location = data.url;
					}
				});
				return false;
			});

			// delete page
			this.$el.find(".ve_delete_page").mwConfirm({
				text: texts.delete_page_confirm,
				onconfirm: function (confirm) {
					builder.isEdited('0');

					var item_id = confirm.$el.attr('data-id');
					var object_id = confirm.$el.attr('data-objectid');

					$.post(ajaxurl, {
						"action": "mwDeleteObjectItem",
						"item_id": item_id,
						"object_id": object_id,
					}, function (data) {
						window.location = data.home_url;
					});

					return false;
				}
			});

			// change device preview
			$(".mw_change_device_preview_container").mwChangeView({onload: true});

			builder.setScroll('.mw_scroll');

		}

		return _MwPageBuilder;

	})();

	MwPageBuilder.prototype.panelIsUsing = function (using = -1) {
		if (using == -1) {
			$('.ve_editor_panel').toggleClass('ve_editor_panel_using');
		} else if (using) {
			$('.ve_editor_panel').addClass('ve_editor_panel_using');
		} else {
			$('.ve_editor_panel').removeClass('ve_editor_panel_using');
		}
	}
	MwPageBuilder.prototype.closeRowLayouts = function () {
		$(".mw_row_group_title").removeClass('active');
		$(".mw_row_layouts_container").removeClass('opened');
		//animate({ opacity: 0, left: "280px" },100).hide();
		//$(".mw_row_layouts_bg").hide();
	}
	MwPageBuilder.prototype.resetElSearch = function () {
		$(".add_element_item_c").show();
		$(".mw_elements_group_container").show();
		$(".mw_element_search_icon").show();
		$(".mw_element_search_storno").hide();
		if (!('ontouchstart' in document.documentElement))
			$(".mw_elements_search").val('').focus();
	}

	MwPageBuilder.prototype.setScroll = function (selector) {
		$(selector).each(function () {
			const ps = new PerfectScrollbar($(this)[0], {
				suppressScrollX: true,
				wheelPropagation: false,
			});
		});
	}

	MwPageBuilder.prototype.openElementSet = function (el) {
		var builder = this;
		var element = el.closest('.element_container');

		// if already opened
		if (element.hasClass('element_container_edited') && builder.editing && builder.edit_settings.type == 'element') {
			return false;
		} else if (this.editing) {

			$.when(
				builder.saveSetting()
			).then(
				builder.openElementSetting(el)
			);

		} else {
			builder.openElementSetting(el);
		}

	}

	MwPageBuilder.prototype.openElementSetting = function (el) {
		var builder = this;
		var element = el.closest('.element_container');

		this.$iframe.find('.row_edited').removeClass('row_edited');

		var type = el.attr('data-type');
		builder.$iframe.find(".element_container_edited").removeClass('element_container_edited');
		element.addClass('element_container_edited');
		var code = builder.setting_container[element.attr('id')];

		builder.switchTo('.mw_admin_content_tab', false);

		builder.openPanelSetting();

		$.ajax({
			type: 'POST',
			data: {"action": "open_element_setting", "code": code, "type": type},
			url: ajaxurl,
			success: function (content) {

				builder.setPanelSettingContent(content);
				builder.working(0);

				builder.openEdit('.mw_editor_panel_content_setting', {
					target: '.element_container_edited',
					type: 'element',
				});

			}

		});
	}

	MwPageBuilder.prototype.setActions = function (actions, target = '') {
		var builder = this;
		$.each(actions, function (i, val) {
			if (val == 'setEdited') {
				builder.isEdited();
			} else if (val == 'setHeader') {
				builder.setForTransparentHeader();
			} else if (val == 'initSorting') {
				document.getElementById('mw_page_builder').contentWindow.mw_init_sorting();
			} else if (val == 'droppable') {
				builder.$iframe.find(".mw_page_builder_droparea").droppable(builder.droppableSetting);
			} else if (val == 'elementScripts') {
				document.getElementById('mw_page_builder').contentWindow.mw_init_elements_scripts(target);
			}
		});

	}

	MwPageBuilder.prototype.working = function (working = true) {
		if (working) $(".ve_editor_panel").addClass('mw_editor_panel_loading');
		else $(".ve_editor_panel").removeClass('mw_editor_panel_loading');
	}

	// on load
	MwPageBuilder.prototype.onLoad = function () {
		this.setForTransparentHeader(false, true);

		if (this.$iframe.find('.visual_content').hasClass('empty_content')) {
			this.switchTo('.mw_admin_rows_tab');
		}

	}
	// set for transparent header
	MwPageBuilder.prototype.setForTransparentHeader = function (change = false, check = false) {
		document.getElementById('mw_page_builder').contentWindow.setForTransparentHeader(change, check);
		var header_height = this.$iframe.find('#header').height();
		if (!header_height) header_height = 0;
		//console.log('hh ' + header_height);
		$('#hidden_header_height').val(header_height);
	}
	// set for transparent header
	MwPageBuilder.prototype.switchTo = function (id, focus_search = false) {
		var tab = this.$el.find(id);
		var target = tab.attr('href');
		var group = tab.attr('data-group');
		$("." + group + "_tabs a").removeClass("active");
		tab.addClass("active");
		$("." + group + "_tab").hide();
		$("." + group + '_' + target).show();
		if (id == '.mw_admin_content_tab' && focus_search) $('.mw_elements_search').focus();
	}
	// if is edited
	MwPageBuilder.prototype.isEdited = function (set = '1') {
		this.$iframe.find('body').attr('data-modified', set);
		this.edit_settings.edited = set;

		if (set == '1')
			$('.mw_save_page').addClass('is_edited');
		else
			$('.mw_save_page').removeClass('is_edited');
		//this.edited=true;
	}
	MwPageBuilder.prototype.isContentEdited = function () {
		//console.log('edited');
		var modifi = this.$iframe.find('body').attr('data-modified');
		if (modifi && modifi == '1')
			return true;
		else return false;
		//this.edited=true;
	}
	// control if page can be leaved
	MwPageBuilder.prototype.confirmExit = function () {
		if (this.isContentEdited()) {
			return texts.before_leave_page;
		}
	}

	// show / close preview
	MwPageBuilder.prototype.showPreview = function (direction) {
		var builder = this;

		var animate_duration = 200;
		if (builder.$el.hasClass('mw_page_builder_preview_mode')) {
			builder.$el.removeClass('mw_page_builder_preview_mode');
			$("#ve_editor_panel").animate({left: "0"}, animate_duration);
			$(".mw_page_builder_container").animate({left: "250px"}, animate_duration);
			this.$iframe.find("body").removeClass('mw_hide_editable');
			//document.getElementById('mw_page_builder').contentWindow.mw_init_tinymce('');
			document.cookie = 'mw_preview_page_mode=0; path=/';
		} else {
			builder.$el.addClass('mw_page_builder_preview_mode');
			$("#ve_editor_panel").animate({left: "-250px"}, animate_duration);
			$(".mw_page_builder_container").animate({left: "0"}, animate_duration);
			this.$iframe.find("body").addClass('mw_hide_editable');
			//document.getElementById('mw_page_builder').contentWindow.mw_destroy_tinymce('');
			document.cookie = 'mw_preview_page_mode=1; path=/';

			// hide multielement
			builder.hideMultielementPanel();
		}
		//Let other scripts know about panel hiding and how long it takes
		//$( document ).trigger( 'mio_editor_hide_panel', { duration: animate_duration } );
	}
	/*
  MwPageBuilder.prototype.togglePanel = function(direction){
      var builder=this;

      $('.show-hide-panel').removeClass('shp-hide-panel shp-show-panel');
      var animate_duration = 200;
      if(direction=='left') {
          $('.show-hide-panel').addClass('shp-show-panel');
          $("#ve_editor_panel").animate({ left: "-250px"}, animate_duration );
          $(".mw_page_builder_container").animate({ left: "0"}, animate_duration );
          this.$iframe.find("body").addClass('mw_hide_editable');
          // hide multielement
          builder.hideMultielementPanel();

      } else {
          $('.show-hide-panel').addClass('shp-hide-panel');
          $("#ve_editor_panel").animate({ left: "0"}, animate_duration );
          $(".mw_page_builder_container").animate({ left: "250px"}, animate_duration );
          this.$iframe.find("body").removeClass('mw_hide_editable');
      }
      //Let other scripts know about panel hiding and how long it takes
      //$( document ).trigger( 'mio_editor_hide_panel', { duration: animate_duration } );
  }*/
	MwPageBuilder.prototype.hideMultielementPanel = function () {
		$('.mw_editor_panel_content_setting .ve_item_body').animate({left: "-250px"}, {
			duration: 200,
			queue: false
		}, function () {
			$(this).hide()
		});
	}
	MwPageBuilder.prototype.closePageSelector = function () {
		$("#ve_page_selector").animate({left: "-750px"}, 200);
		$("#ve_page_selector_bg").hide();
	}
	// setting panel
	MwPageBuilder.prototype.openPanelSetting = function () {
		this.working();
		this.panelIsUsing(1);
		$(".mw_editor_panel_content_setting").addClass('opened');
		$(".mw_editor_panel_content_setting .mw_editor_panel_container").html('');
		$(".mw_editor_panel_content_setting").css('left', "-250px");
		$(".mw_editor_panel_content_setting").animate({left: "+=250px"}, {duration: 200, queue: false});
	}

	MwPageBuilder.prototype.openSettingInPanel = function (el) {

		var itemid = el.attr('data-itemid');
		var objectid = el.attr('data-id');
		var setid = el.attr('data-setid');

		var target = el.attr('data-target');
		var reload = el.attr('data-reload');
		var title = el.attr('title');
		var builder = this;

		builder.openPanelSetting();

		$.post(ajaxurl, {
			"action": "openSettingInpanel",
			"item_id": itemid,
			"object_id": objectid,
			"modul_type": builder.modul_type,
			"set_id": setid,
			"title": title
		}, function (content) {

			builder.setPanelSettingContent(content);
			builder.working(0);

			builder.openEdit('.mw_editor_panel_content_setting', {
				target: target,
				type: reload,
				saveto: 'option',
				set_is_edited: false,
			});

		});
		return false;
	}

	MwPageBuilder.prototype.closePanelSetting = function () {
		this.editing = false;
		$(".mw_editor_panel_content_setting").removeClass('opened');
		$(".mw_editor_panel_content_setting").animate({left: "-=250px"}, {duration: 200, queue: false});
		this.hideMultielementPanel();

		this.$iframe.find('.row_edited').removeClass('row_edited');
		this.$iframe.find('.element_container_edited').removeClass('element_container_edited');

	}
	MwPageBuilder.prototype.setPanelSettingContent = function (content) {
		$(".mw_editor_panel_content_setting .mw_editor_panel_container").html(content);

		this.setScroll('.mw_editor_panel_content_setting .mw_scroll');

	}
	MwPageBuilder.prototype.closeRevisions = function () {
		this.$el.removeClass('mw_page_builder_revisions_mode');
		$(".mw_editor_revision_list").animate({left: "-250px"}, {duration: 200, queue: false});
	}
	MwPageBuilder.prototype.openRevisions = function (weditor) {
		this.$el.addClass('mw_page_builder_revisions_mode');
		$(".mw_editor_revision_list").animate({left: "0"}, {duration: 200, queue: false});

		builder = this;
		builder.working();

		$.post(ajaxurl, {
			'action': 'mw_load_page_revisions',
			'post_id': builder.post_id,
			'post_type': builder.page_type,
			'weditor': weditor
		}, function (content) {

			builder.working(0);
			$('.mw_editor_revision_list_content').html(content);

		}).fail(function (data) {
			console.log(data);
			builder.working(0);
		});
	}

	MwPageBuilder.prototype.getLayer = function (target) {
		var builder = this;
		var r = 0;
		var c = 0;
		var e = 0;
		var s = 0;
		var i = 0;
		var rows = [];
		var elements = [];
		var single_elements = {};
		var subelements = [];
		this.$iframe.find(target + " > .row").each(function () {
			elements[r] = [];
			subelements[r] = [];
			rows[r] = builder.setting_container[$(this).attr('id')];
			c = 0;
			$(".sortable-col", this).not(".subcol", this).each(function () {
				elements[r][c] = [];
				subelements[r][c] = [];
				e = 0;
				$(".element_container", this).not(".subcol .element_container", this).each(function () {
					elements[r][c][e] = builder.setting_container[$(this).attr('id')];
					subelements[r][c][e] = [];
					subelements[r][c][e][0] = [];
					s = 0;
					$(".subcol-first .element_container", this).each(function () {
						subelements[r][c][e][0][s] = [];
						subelements[r][c][e][0][s] = builder.setting_container[$(this).attr('id')];
						s++;
					});
					subelements[r][c][e][1] = [];
					s = 0;
					$(".subcol-last .element_container", this).each(function () {
						subelements[r][c][e][1][s] = [];
						subelements[r][c][e][1][s] = builder.setting_container[$(this).attr('id')];
						s++;
					});
					e++;
				});
				c++;
			});
			r++;
		});

		var tn;
		this.$iframe.find(".element_single").each(function () {
			textarea = $(".element_content_textarea", this);
			tn = textarea.attr('name');
			single_elements[tn] = {};
			single_elements[tn] = textarea.val();
		});

		var ret = {};

		ret['elements'] = elements;
		ret['rows'] = rows;
		ret['subelements'] = subelements;
		ret['single_elements'] = single_elements;

		//console.log(ret);

		return ret;
	}

	MwPageBuilder.prototype.openEdit = function (selector, settings) {

		var defaults = {
			type: 'row',
			saveto: 'var',
			target: '',
			edited: '0',
			set_is_edited: true,
		};

		this.editing = true;
		this.edit_settings = $.extend({}, this, defaults, settings);

		//this.edit_settings.edited_id=this.$iframe.find(this.edit_settings.target).attr('id');

		if (this.edit_settings.target == '#header')
			this.edit_settings.edited_id = 'header';
		else if (this.edit_settings.target == '#footer')
			this.edit_settings.edited_id = 'footer';
		else {
			this.edit_settings.edited_id = this.$iframe.find(this.edit_settings.target).attr('id');
		}

		// code for editing
		this.edited_code = this.getSetting();
		// code for storno
		this.storno_code = this.cloneSet(this.getSetting());

		// add actions on setting fields
		this.setEditActions(selector + ' .mw_onedit_action', this.edit_settings.set_is_edited);

		// change device preview
		$(selector + " .mw_change_device_preview_container").mwChangeView();

	};

	MwPageBuilder.prototype.cloneSet = function (set) {
		//console.log(JSON.stringify(set));
		return JSON.parse(JSON.stringify(set));

	};

	MwPageBuilder.prototype.setEditActions = function (selector, set_is_edited = true) {
		var builder = this;

		// bind hidden setting
		//this.$el.find(".mw_hidden_setting").mwHiddenSetting();

		this.$el.find(selector).mwOnEditAction({
			change: function (field_setting) {
				builder.set(field_setting, set_is_edited);
			},
			add_new_setting: function (selector) {
				builder.$el.find(selector + ' .mw_onedit_action').mwOnEditAction({
					change: function (field_setting) {
						builder.set(field_setting);
					},
				});
			},
		});
	}

	MwPageBuilder.prototype.getSetting = function () {
		var builder = this;

		if (this.edit_settings.saveto == 'var') {
			return builder.setting_container[this.edit_settings.edited_id]; // elements and forms
		} else {
			return this.getFormSetting(); // post meta and options (header, footer, page...)
		}
	}
	/*
  MwPageBuilder.prototype.close = function(){
     $(".mw_editor_panel_content_setting").animate({ left: "-=250px"}, { duration: 200, queue: false } );
     $(".mw_editor_panel_content_setting .ve_item_body").hide();
     //this.unbindActions();
  }*/

	MwPageBuilder.prototype.saveSetting = function () {

		//if(this.edit_settings.edited=='1') {
		var builder = this;

		// save to setting container
		if (this.edit_settings.saveto == 'var') {

			var newSetting = builder.getFormSetting();

			if (newSetting) {
				builder.setting_container[this.edit_settings.edited_id] = newSetting;
			}

			// save to option or meta
		} else {
			builder.working();
			var formdata = $('#ve_save_setting_form').serialize();
			$.post(ajaxurl, 'action=save_panel_setting&' + formdata, function (data) {
				builder.working(0);
			});
		}
		//console.log('setting saved');
		//console.log(builder.setting_container[this.edit_settings.edited_id]);
		//} else console.log('setting not saved');
	}
	MwPageBuilder.prototype.getFormSetting = function () {
		var newSetting = {};
		var formArray = this.$el.find('#ve_save_setting_form').serializeArray();
		var settingArray = {};
		var configArray = {};

		if (this.edit_settings.type == 'element' || this.edit_settings.type == 'row') {
			newSetting = this.getSetting();
		}

		if (!newSetting) {
			console.log('no setting');
			return false;
		}

		$.map(formArray, function (n, i) {
			var res = n['name'].match(/[^[\]]+(?=])/g);
			if (res) {
				// save config of element

				if (n['name'].substring(0, 10) == "ve_config[") {
					if (res.length == 1) {
						configArray[res[0]] = n['value'];
					} else if (res.length == 2) {
						if (configArray[res[0]] === undefined) configArray[res[0]] = {};
						configArray[res[0]][res[1]] = n['value'];
					}
				} else {
					if (res.length == 1) {
						settingArray[res[0]] = n['value'];

						if (n['value'] == '%%get%%') {
							settingArray[res[0]] = newSetting['style'][res[0]];
						}
					} else if (res.length == 2) {
						if (settingArray[res[0]] === undefined) settingArray[res[0]] = {};
						settingArray[res[0]][res[1]] = n['value'];
					} else if (res.length == 3) {
						if (settingArray[res[0]] === undefined) settingArray[res[0]] = {};
						if (settingArray[res[0]][res[1]] === undefined) settingArray[res[0]][res[1]] = {};
						settingArray[res[0]][res[1]][res[2]] = n['value'];
					} else if (res.length == 4) {
						if (settingArray[res[0]] === undefined) settingArray[res[0]] = {};
						if (settingArray[res[0]][res[1]] === undefined) settingArray[res[0]][res[1]] = {};
						if (settingArray[res[0]][res[1]][res[2]] === undefined) settingArray[res[0]][res[1]][res[2]] = {};
						settingArray[res[0]][res[1]][res[2]][res[3]] = n['value'];
					} else if (res.length == 5) {
						if (settingArray[res[0]] === undefined) settingArray[res[0]] = {};
						if (settingArray[res[0]][res[1]] === undefined) settingArray[res[0]][res[1]] = {};
						if (settingArray[res[0]][res[1]][res[2]] === undefined) settingArray[res[0]][res[1]][res[2]] = {};
						if (settingArray[res[0]][res[1]][res[2]][res[3]] === undefined) settingArray[res[0]][res[1]][res[2]][res[3]] = {};
						settingArray[res[0]][res[1]][res[2]][res[3]][res[4]] = n['value'];
					}
				}
			}
		});
		if (this.edit_settings.type == 'element' || this.edit_settings.type == 'row') {
			newSetting.style = settingArray;
			if (this.edit_settings.type == 'element') newSetting.config = configArray;
		} else {
			newSetting = settingArray;
		}
		return newSetting;
	}

	MwPageBuilder.prototype.addLoading = function () {
		var builder = this;
		this.$iframe.find(builder.edit_settings.target).addClass('loading');
		this.$iframe.find(builder.edit_settings.target).append('<div class="cms_loading"></div>');
	}
	MwPageBuilder.prototype.removeLoading = function () {
		var builder = this;
		this.$iframe.find(builder.edit_settings.target).removeClass('loading');
		this.$iframe.find(builder.edit_settings.target + ' .cms_loading').remove();
	}

	MwPageBuilder.prototype.reload = function (setting = '', wait = true) {
		var builder = this;
		var layer = '';
		if (setting) {
			layer = setting;
			builder.setting_container[builder.edit_settings.edited_id] = setting;
		} else {
			// save to setting container
			if (this.edit_settings.saveto == 'var') this.saveSetting();
			// get setting from setting container
			var layer = this.getSetting();
		}
		this.addLoading();

		if (this.edit_settings.type == 'element') {

			var replace_content = false;

			if (builder.$iframe.find('#' + builder.edit_settings.edited_id).hasClass('box_element_container')) {

				var box_content = builder.$iframe.find('#' + builder.edit_settings.edited_id + ' .ve_content_block_content');

				var empty_box = false;
				if (builder.$iframe.find('#' + builder.edit_settings.edited_id + ' .ve_content_block').hasClass('empty_container')) empty_box = true;

				replace_content = true;

			}
			$.ajax({
				url: ajaxurl,
				async: wait,
				type: 'POST',
				timeout: 30000,
				data: {
					'action': 'create_element',
					'layer': layer,
					'post_id': this.post_id,
					'el_id': this.edit_settings.edited_id
				},
				success: function (data) {
					builder.$iframe.find('#' + builder.edit_settings.edited_id).replaceWith(data.content);
					builder.$iframe.find('#' + data.newkey).addClass('element_container_edited');

					if (replace_content) {

						builder.$iframe.find('#' + data.newkey + ' .ve_content_block_content').html(box_content.html());

						if (empty_box) builder.$iframe.find('#' + data.newkey + ' .ve_content_block').addClass('empty_container');
						else builder.$iframe.find('#' + data.newkey + ' .ve_content_block').removeClass('empty_container');

					}

					if (builder.$iframe.find('#' + data.newkey).hasClass('ve_animation')) {
						builder.$iframe.find('#' + data.newkey).addClass('animated');
					}

					document.getElementById('mw_page_builder').contentWindow.mw_init_sorting();
					document.getElementById('mw_page_builder').contentWindow.mw_init_facebook();
					//console.log(data.content);
					builder.callOnReload('element');

				},
				fail: function (data) {
					console.log(data);
					obj.removeLoading();
				}
			});


		} else if (this.edit_settings.type == 'row') {
			var content_row = builder.$iframe.find(builder.edit_settings.target + ' .row_fix_width');
			var empty_row = false;
			if (builder.$iframe.find(builder.edit_settings.target).hasClass('empty_container')) empty_row = true;

			var obj = this;

			$.ajax({
				url: ajaxurl,
				async: wait,
				type: 'POST',
				timeout: 30000,
				data: {
					'action': 'add_new_row',
					'content': layer,
					'post_id': this.post_id,
					'row_id': this.edit_settings.edited_id,
					'rowtype': 'ajax'
				},
				success: function (content) {
					builder.$iframe.find(builder.edit_settings.target).replaceWith(content.row);
					builder.$iframe.find('#row_' + content.id).addClass('row_edited');

					if (content.row_type == 'slider') {
						//builder.$iframe.find('#row_'+content.id+' .miocarousel').MioCarousel({});
						//console.log('set_miocarousel');
						//document.getElementById("mw_page_builder").contentWindow.set_miocarousel('#row_'+content.id+' .miocarousel');
					} else {
						if (!empty_row) {
							builder.$iframe.find('#row_' + content.id + ' .row_fix_width').html(content_row.html());
							builder.$iframe.find('#row_' + content.id).removeClass('empty_container');
						}
					}

					document.getElementById('mw_page_builder').contentWindow.mw_init_sorting();
					document.getElementById('mw_page_builder').contentWindow.mw_destroy_tinymce('#row_' + content.id + ' ');
					document.getElementById('mw_page_builder').contentWindow.mw_init_tinymce();

					builder.callOnReload('row');
				},
				fail: function (data) {
					console.log(data);
					obj.removeLoading();
				}
			});

		} else if (this.edit_settings.type == 'header') {

			var obj = this;

			$.post(ajaxurl, {
				'action': 'reload_header',
				'header_setting': layer,
				'post_id': this.post_id,
				'modul_type': this.modul_type
			}, function (content) {
				builder.$iframe.find('header').html(content);
				document.getElementById("mw_page_builder").contentWindow.reload_replaced_header_menu();

				var header = builder.$iframe.find('#header');
				if (header.hasClass('mw_transparent_header')) builder.$iframe.find('body').addClass('page_with_transparent_header');
				else builder.$iframe.find('body').removeClass('page_with_transparent_header');
				builder.changeStyles("body:not(.page_with_transparent_header) .row_height_full", {'min-height': 'calc(100vh - ' + header.height() + 'px)'});
				$('#hidden_header_height').val(header.height());

				builder.callOnReload('header');

			}).fail(function (data) {
				console.log(data);
				obj.removeLoading();
			});

		} else if (this.edit_settings.type == 'footer') {

			var obj = this;

			$.post(ajaxurl, {
				'action': 'reload_footer',
				'footer_setting': layer,
				'post_id': this.post_id,
				'modul_type': this.modul_type
			}, function (content) {
				builder.$iframe.find('#footer').replaceWith(content);

				builder.callOnReload('footer');

			}).fail(function (data) {
				console.log(data);
				obj.removeLoading();
			});

		} else if (this.edit_settings.type == 'body') {

			var obj = this;
			//console.log(layer);
			$.post(ajaxurl, {
				'action': 'reload_body',
				'body_setting': layer,
				'post_id': this.post_id
			}, function (content) {
				obj.$iframe.find('style#body_style_mobile').remove();
				obj.$iframe.find('style#body_style_tablet').remove();
				obj.$iframe.find('style#body_style').replaceWith(content.styles);
				obj.$iframe.find('.body_background_container').replaceWith(content.background);
				obj.removeLoading();
			}).fail(function (data) {
				console.log(data);
				obj.removeLoading();
			});

		} else if (this.edit_settings.type == 'popup_body') {

			var obj = this;
			//console.log(layer);
			$.post(ajaxurl, {
				'action': 'reload_popup_body',
				'body_setting': layer,
				'post_id': this.post_id
			}, function (content) {
				obj.$iframe.find('style#body_style_mobile').remove();
				obj.$iframe.find('style#body_style_tablet').remove();
				obj.$iframe.find('style#body_style').replaceWith(content.styles);
				obj.removeLoading();
			}).fail(function (data) {
				console.log(data);
				obj.removeLoading();
			});

		}
	}

	MwPageBuilder.prototype.set = function (setting, set_is_edited = true) {
		/*
      var target_selector=this.edit_settings.target;
      if(setting.target && setting.target.length) target_selector=target_selector+' '+setting.target;
      var target=this.$iframe.find(target_selector);
      */

		var builder = this;

		var selector;
		var multielement_selector = '';
		if (this.edit_settings.target == 'body') selector = '';
		else if (this.edit_settings.target == 'body' && setting.target && setting.target.length) selector = 'body'
		else selector = '#' + this.edit_settings.edited_id;

		if (setting.multielement_selector) multielement_selector = selector + setting.multielement_selector;

		if (setting.target && setting.target.length) selector = selector + setting.target;

		var target = this.$iframe.find(selector);

		// change class
		if (setting.action == 'change_class') {
			target.removeClass(setting.remove);
			target.addClass(setting.class + setting.content);
		}
		if (setting.action == 'change_class_default') {
			var content = setting.content;
			if (setting.content == '' && setting.setting)
				content = setting.setting;
			target.removeClass(setting.remove);
			target.addClass(setting.class + content);
		}
		// change class
		if (setting.action == 'change_animation') {
			target.removeClass(setting.remove);
			target.addClass('ve_animation animated ' + setting.content);
			target.attr('data-animation', setting.content);
		}
		// change css
		else if (setting.action == 'change_css') {
			// gradient

			if (setting.type == 'gradient') {
				target.css("filter", "progid:DXImageTransform.Microsoft.gradient(gradientType=0, startColorstr='" + setting.content.color1 + "', endColorstr='" + setting.content.color2 + "')");
				target.css("background", "-webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(0%, " + setting.content.color1 + "), color-stop(100%, " + setting.content.color2 + "))");
				target.css("background", "-webkit-linear-gradient(top, " + setting.content.color1 + " 0%," + setting.content.color2 + " 100%)");
				target.css("background", "-moz-linear-gradient(top, " + setting.content.color1 + " 0%," + setting.content.color2 + " 100%)");
				target.css("background", "-o-linear-gradient(top, " + setting.content.color1 + " 0%," + setting.content.color2 + " 100%)");
				target.css("background", "linear-gradient(top, " + setting.content.color1 + " 0%," + setting.content.color2 + " 100%)");

				if (!isColorLight(setting.content.color1)) {
					target.addClass('dark_color');
					target.removeClass('light_color');
				} else {
					target.addClass('light_color');
					target.removeClass('dark_color');
				}
				// background-color
			} else if (setting.type == 'background-color') {
				var form_setting = this.getFormSetting();

				target.css('background', setting.content.color1);
				if (form_setting['style']['background_image']['image'] || !isColorLight(setting.content.color1)) {
					target.addClass('dark_color');
					target.removeClass('light_color');
				} else {
					target.addClass('light_color');
					target.removeClass('dark_color');
				}
			} else {
				//alert('target:'+target+' css:'+setting.css+' css:'+setting.content)
				console.log(selector);
				target.css(setting.css, setting.content);
			}

		} else if (setting.action == 'change_styles') {
			var content;

			// css variable
			if (setting.target == ':root') {
				selector = setting.target;
			}

			if (setting.type == 'background-color') {
				content = setting.content.color1;
			} else content = setting.content;

			if (setting.css == 'font-size-em') {
				setting.css = 'font-size';
				content = content + 'em';
				console.log(setting.css);
			}

			this.changeStyle(selector, setting.css, content, setting.device);
		} else if (setting.action == 'change_element_top_padding') {
			console.log(parseInt(setting.content));
			if (parseInt(setting.content) < 0) {
				this.changeStyle(selector, 'margin-top', setting.content, setting.device);
				this.changeStyle(selector, 'padding-top', '0', setting.device);
			} else {
				this.changeStyle(selector, 'margin-top', '0', setting.device);
				this.changeStyle(selector, 'padding-top', setting.content, setting.device);
			}

		} else if (setting.action == 'change_style_variable') {

			var content;
			var variable;

			if (setting.class && setting.type == 'color') {
				if (setting.content && isColorLight(setting.content)) {
					target.addClass('light' + setting.class);
					target.removeClass('dark' + setting.class);
				} else {
					target.addClass('dark' + setting.class);
					target.removeClass('light' + setting.class);
				}
			}

			content = setting.content;
			variable = setting.css + this.$iframe.find(this.edit_settings.target).attr('id');

			this.changeStyle(':root', variable, content, setting.device);
		} else if (setting.action == 'imageoption') {
			target.removeClass(setting.remove);
			target.addClass(setting.class + setting.content);

			if (setting.setting) {
				variable = setting.setting + this.$iframe.find(this.edit_settings.target).attr('id');
				this.changeStyle(':root', variable, setting.size, setting.device);
			}
		} else if (setting.action == 'change_menu_color') {

			var selector = setting.target;
			var t = this.$iframe.find(this.edit_settings.target);

			if (!isColorLight(setting.content)) {
				t.addClass(setting.setname + '_dark');
				t.removeClass(setting.setname + '_light');
			} else {
				t.addClass(setting.setname + '_light');
				t.removeClass(setting.setname + '_dark');
			}

			this.changeStyle(selector, setting.css, setting.content, setting.device);
		} else if (setting.action == 'change_background') {

			//target.find('.background_overlay').css('background',setting.content.filter_color);

			this.changeStyles(selector + ' .background_overlay', {'background-color': setting.content.filter_color});
			var newstyles = {};
			var new_mobile_styles = {};
			var new_tablet_styles = {};

			var used = '';

			if (setting.content.img) {
				if (setting.content.efect == 'parallax') {
					console.log(setting.content);
					var pos = setting.content.position.split(" ");
					setting.content.position = pos[0] + ' 0';
				} else {
					target.css('background-position-y', '');
				}
				var used_image = '';

				if(setting.content.cover && setting.content.fullimg !== '' ) {
					used_image = setting.content.fullimg;
				} else {
					used_image = setting.content.img;
				}
				newstyles = {
					'background-image': 'url(' + used_image + ')',
					'background-position': setting.content.position,
					'background-repeat': setting.content.repeat,
				};
				used = 'img';
			} else if (setting.content.pattern && setting.content.pattern != 'none') {
				newstyles = {
					'background-image': setting.content.pattern,
					'background-position': '',
					'background-repeat': 'repeat',
				};
				used = 'pattern';
			} else {
				newstyles = {
					'background-image': '',
					'background-position': '',
					'background-repeat': '',
				};
			}

			var form_setting = this.getFormSetting();

			var color_target = '';
			if (setting.target == ' .ve_content_block') color_target = setting.target;
			if (this.$iframe.find('#' + this.edit_settings.edited_id + color_target).hasClass('light_color') && setting.content.img) {
				this.$iframe.find('#' + this.edit_settings.edited_id + color_target).addClass('dark_color').removeClass('light_color');
			} else if (this.$iframe.find('#' + this.edit_settings.edited_id + color_target).is('.row, .ve_content_block') && !setting.content.img) {
				if (isColorLight(form_setting['style']['background_color']['color1'])) {
					this.$iframe.find('#' + this.edit_settings.edited_id + color_target).addClass('light_color').removeClass('dark_color');
				}
				if (!isColorLight(form_setting['style']['background_color']['color1'])) {
					this.$iframe.find('#' + this.edit_settings.edited_id + color_target).addClass('dark_color').removeClass('light_color');
				}
			}

			if (setting.content.size && used == 'img') {
				newstyles['background-size'] = setting.content.size + 'px';
			} else {
				newstyles['background-size'] = '';
			}

			this.changeStyles(selector, newstyles);

			if (setting.content.hide_mobile) {
				new_mobile_styles = {
					'background-image': 'none',
					'background-position': '',
					'background-size': ''
				};
				this.changeStyles(selector + ' .background_overlay', {'background-color': 'transparent'}, 'mobile');
			} else if (setting.content.mobile_img) {
				new_mobile_styles = {
					'background-image': 'url(' + setting.content.mobile_img + ')',
					'background-position': setting.content.mobile_position
				};
				this.changeStyles(selector + ' .background_overlay', {'background-color': ''}, 'mobile');
			} else if (setting.content.mobile_pattern && setting.content.mobile_pattern != 'none') {
				new_mobile_styles = {
					'background-image': setting.content.mobile_pattern,
					'background-size': ''
				};
				//this.changeStyles(selector,{'background-image': setting.content.mobile_pattern},'tablet');
				this.changeStyles(selector + ' .background_overlay', {'background-color': ''}, 'mobile');
			} else {
				new_mobile_styles = {
					'background-image': '',
					'background-position': '',
					'background-size': ''
				};
				this.changeStyles(selector + ' .background_overlay', {'background-color': ''}, 'mobile');
			}

			if (setting.content.mobile_size && used == 'img') {
				new_mobile_styles['background-size'] = setting.content.mobile_size + 'px';
			} else {
				new_mobile_styles['background-size'] = '';
			}

			this.changeStyles(selector, new_mobile_styles, 'mobile');

			if (setting.content.tablet_img) {

				new_tablet_styles = {
					'background-image': 'url(' + setting.content.tablet_img + ')',
					'background-position': setting.content.tablet_position
				};

			} else if (setting.content.tablet_pattern && setting.content.tablet_pattern != 'none') {

				new_tablet_styles = {
					'background-image': setting.content.tablet_pattern,
					'background-size': ''
				};

			} else {

				new_tablet_styles = {
					'background-image': '',
					'background-position': '',
					'background-size': ''
				};

			}

			if (setting.content.tablet_size && used == 'img') {
				new_tablet_styles['background-size'] = setting.content.tablet_size + 'px';
			} else {
				new_tablet_styles['background-size'] = '';
			}

			this.changeStyles(selector, new_tablet_styles, 'tablet');

			target.removeClass('background_cover');
			target.removeClass('background_fixed background_parallax');

			if (setting.content.img) {
				if (setting.content.cover) target.addClass('background_cover');
				if (setting.content.efect) {
					target.addClass('background_' + setting.content.efect);
					if (setting.content.efect != 'parallax') {
						target.attr('style', '');
					}
				}
			}

		} else if (setting.action == 'change_smart_background_color') {

			var color;

			if (setting.type == 'background-color') {
				color = setting.content.color1;
				// color
			} else {
				color = setting.content;
			}

			var uploaded_img = false;
			if (setting.setting == 'with_bg_image') {
				var form_setting = this.getFormSetting();
				if (form_setting['style']['background_image']['image']) uploaded_img = true;
			}

			if (uploaded_img || !isColorLight(color)) {
				target.addClass('dark_color');
				target.removeClass('light_color');
			} else {
				target.addClass('light_color');
				target.removeClass('dark_color');
			}

			this.changeStyle(selector, 'background-color', color);

		} else if (setting.action == 'change_rform_background_color') {

			var color;

			color = setting.content;

			if (uploaded_img || !isColorLight(color)) {
				target.addClass('input_dark_color');
				target.removeClass('input_light_color');
			} else {
				target.addClass('input_light_color');
				target.removeClass('input_dark_color');
			}

			this.changeStyle(selector + ' input, ' + selector + ' select, ' + selector + ' textarea', 'background-color', color);

		} else if (setting.action == 'change_background_color') {

			// gradient
			if (setting.type == 'gradient') {
				this.changeStyles(selector, {"background": "linear-gradient(to bottom, " + setting.content.color1 + " 0%," + setting.content.color2 + " 100%) no-repeat border-box"});
				// background-color
			} else if (setting.type == 'background-color') {
				this.changeStyles(selector, {'background': setting.content.color1});
				// color
			} else if (setting.type == 'color') {
				this.changeStyles(selector, {'background': setting.content});
			}

		}

		// change class
		if (setting.action == 'change_background_set') {

			target.removeClass('mw_element_item_corners1 mw_element_item_corners2 mw_element_item_shadow1 mw_element_item_shadow2 mw_element_item_shadow3 mw_element_item_shadow4 mw_element_item_shadow5 mw_element_item_borders');
			if (setting.content.shadow)
				target.addClass('mw_element_item_shadow' + setting.content.shadow);
			if (setting.content.corner)
				target.addClass('mw_element_item_corners' + setting.content.corner);
			if (setting.content.border) {
				target.addClass('mw_element_item_borders');
			}

			if (setting.content.color) {
				if (!isColorLight(setting.content.color)) {
					target.addClass('dark_color');
					target.removeClass('light_color');
				} else {
					target.addClass('light_color');
					target.removeClass('dark_color');
				}
			} else {
				target.removeClass('dark_color');
				target.removeClass('light_color');
			}

			this.changeStyle(selector, 'background-color', setting.content.color);

			var arrow = target.find('.mw_box_arrow .arrow');
			if (arrow.length) {

				this.changeStyle(selector + ' .mw_box_arrow .arrow', 'background-color', setting.content.color);

				arrow.removeClass('mw_element_item_shadow1 mw_element_item_shadow2 mw_element_item_shadow3 mw_element_item_shadow4 mw_element_item_shadow5 mw_element_item_borders');
				if (setting.content.shadow)
					arrow.addClass('mw_element_item_shadow' + setting.content.shadow);
				if (setting.content.border) {
					arrow.addClass('mw_element_item_borders');
				}

			}
		} else if (setting.action == 'change_slider') {

			target.removeClass('miocarousel_light miocarousel_auto');

			if (setting.scheme == 'light') {
				target.addClass('miocarousel_light');
			} else if (setting.scheme == 'auto') {
				target.addClass('miocarousel_auto');
			}

			if (setting.hide_nav) {
				target.addClass('miocarousel_hide_nav');
			} else {
				target.removeClass('miocarousel_hide_nav');
			}

			target.attr('data-autoplay', setting.autoplay);
			target.attr('data-animation', setting.animation);
			target.attr('data-duration', setting.duration);
			target.attr('data-speed', setting.speed);
			target.attr('data-duration', setting.delay);

			target.removeClass('miocarousel_fade');
			target.removeClass('miocarousel_slide');
			target.addClass('miocarousel_' + setting.animation);

			var content = target.clone(false);
			target.replaceWith(content);

			document.getElementById("mw_page_builder").contentWindow.set_miocarousel(selector);

		} else if (setting.action == 'change_row_slider_height') {
			if (setting.setname == 'row_height') {
				this.$iframe.find(this.edit_settings.target).removeClass(setting.remove);
				this.$iframe.find(this.edit_settings.target).addClass(setting.class + setting.content);
				this.$iframe.find(this.edit_settings.target + ' .slide').removeClass(setting.remove);
				this.$iframe.find(this.edit_settings.target + ' .slide').addClass(setting.class + setting.content);
				this.$iframe.find(this.edit_settings.target + ' .miocarousel').height(0);
				height = 0;
			} else if (setting.setname == 'min-height') {
				this.changeStyle(selector, setting.css, setting.content, setting.device);
				this.changeStyle(selector + ' .slide', setting.css, setting.content, setting.device);
				height = parseInt(setting.content);
			}

			this.$iframe.find(this.edit_settings.target + ' .slide').each(function () {
				console.log($(this).outerHeight() + ' > ' + height);
				if ($(this).outerHeight() > height) height = $(this).outerHeight();
			});
			this.$iframe.find(this.edit_settings.target + ' .miocarousel').outerHeight(height);
			this.changeStyle(selector + ' .slide', setting.css, height, setting.device);

		} else if (setting.action == 'change_img') {
			if (setting.content && target.length) {
				target.attr("src", setting.content);
				target.attr("srcset", '');
				target.attr("sizes", '');
				console.log(setting);
				if (setting.setting == 'show') target.show();
				if (setting.position) {
					if (multielement_selector) {
						this.changeStyle(multielement_selector, 'object-position', setting.position);
					} else {
						this.changeStyle(selector, 'object-position', setting.position);
					}
				}
			} else this.reload();
		} else if (setting.action == 'change_font') {

			var newstyles = {};
			var shadow = 'none';

			if (this.hasVal(setting.content.shadow)) {
				if (setting.content.shadow == 'dark') shadow = '1px 1px 1px rgba(0, 0, 0, 0.5)';
				else if (setting.content.shadow == 'light') shadow = '1px 1px 1px rgba(255, 255, 255, 0.5)';
			}
			if (this.hasVal(setting.content.font)) {
				if (setting.content.font) {
					newstyles['font-family'] = '\'' + setting.content.font + '\'';
				} else {
					newstyles['font-family'] = setting.content.font;
				}

			}
			if (this.hasVal(setting.content.line_height)) newstyles['line-height'] = setting.content.line_height;
			if (this.hasVal(setting.content.size)) newstyles['font-size'] = setting.content.size + 'px';
			if (this.hasVal(setting.content.align)) newstyles['text-align'] = setting.content.align;
			if (this.hasVal(setting.content.weight)) newstyles['font-weight'] = setting.content.weight;
			if (this.hasVal(setting.content.letter_spacing)) newstyles['letter-spacing'] = setting.content.letter_spacing + 'px';
			if (this.hasVal(setting.content.shadow)) newstyles['text-shadow'] = shadow;

			if (setting.setting == 'variable_color') {
				this.changeStyle(':root', setting.css, setting.content.color);
			} else if (setting.setting == 'variable_color_row') {
				variable = setting.css + this.$iframe.find(this.edit_settings.target).attr('id');
				this.changeStyle(':root', variable, setting.content.color);
			} else {
				if (this.hasVal(setting.content.color)) newstyles['color'] = setting.content.color;
			}

			if (setting.css == '--page-text-color' && this.hasVal(setting.content.line_height)) {
				if (!setting.content.line_height) this.changeStyle(':root', '--page-list-background-pos', '');
				else this.changeStyle(':root', '--page-list-background-pos', 'calc(' + (setting.content.line_height / 2) + 'em - 13px)');
			} else if (this.hasVal(setting.content.line_height) && setting.setting == 'set_list_background_position') {
				if (!setting.content.line_height) this.changeStyles(selector + ' ul li', {'background-position-y': ''});
				else this.changeStyles(selector + ' ul li', {'background-position-y': 'calc(' + (setting.content.line_height / 2) + 'em - 13px)'});
			}

			if (setting.content.capitals) newstyles['text-transform'] = 'uppercase';
			else newstyles['text-transform'] = '';

			if (this.hasVal(setting.content.use_font)) {
				target.removeClass('title_element_container text_element_container subtitle_element_container');
				target.addClass(setting.content.use_font + '_element_container');
			}

			this.changeStyles(selector, newstyles);
			// mobile size
			if (this.hasVal(setting.content.mobile_size))
				this.changeStyles(selector, {'font-size': setting.content.mobile_size + 'px'}, 'mobile');
			// tablet size
			if (this.hasVal(setting.content.tablet_size))
				this.changeStyles(selector, {'font-size': setting.content.tablet_size + 'px'}, 'tablet');

			if (this.hasVal(setting.content.font) && setting.content.font && !setting.content.file) {
				var font_id = setting.content.font.replace(" ", "_");
				var font_link = '<link id="mw_gf_' + font_id + '" href="https://fonts.googleapis.com/css?family=' + setting.content.font.replace(" ", "+") + ':' + setting.content.allweights + '&subset=latin,latin-ext" rel="stylesheet" type="text/css">';
				if (this.$iframe.find('#mw_gf_' + font_id).length == 0) {
					this.$iframe.find('head').append(font_link);
				} else {
					this.$iframe.find('#mw_gf_' + font_id).replaceWith(font_link);
				}
			}

		} else if (setting.action == 'change_variable_font') {

			if (this.hasVal(setting.content.font)) this.changeStyle(':root', setting.css + '-family', setting.content.font);
			if (this.hasVal(setting.content.line_height)) this.changeStyle(':root', setting.css + '-line-height', setting.content.line_height);
			if (this.hasVal(setting.content.weight)) this.changeStyle(':root', setting.css + '-weight', setting.content.weight);
			if (this.hasVal(setting.content.color)) this.changeStyle(':root', setting.css + '-color', setting.content.color);

			if (setting.content.capitals) this.changeStyle(':root', setting.css + '-text-transform', 'uppercase');
			else this.changeStyle(':root', setting.css + '-text-transform', '');


			if (this.hasVal(setting.content.font) && setting.content.font && !setting.content.file) {
				var font_id = setting.content.font.replace(" ", "_");
				var font_link = '<link id="mw_gf_' + font_id + '" href="https://fonts.googleapis.com/css?family=' + setting.content.font.replace(" ", "+") + ':' + setting.content.allweights + '&subset=latin,latin-ext" rel="stylesheet" type="text/css">';
				if (this.$iframe.find('#mw_gf_' + font_id).length == 0) {
					this.$iframe.find('head').append(font_link);
				} else {
					this.$iframe.find('#mw_gf_' + font_id).replaceWith(font_link);
				}
			}

		} else if (setting.action == 'change_icon') {

			if (setting.content.tab == 'icon') {
				if (target.length) {
					target.html(setting.content.code);

					if (this.hasVal(setting.content.size) && setting.content.size) {
						this.changeStyles(selector, {'font-size': setting.content.size + 'px'});
						this.changeStyles(selector + ' svg', {
							'width': setting.content.size + 'px',
							'height': setting.content.size + 'px'
						});
					} else {
						this.changeStyles(selector, {'font-size': ''});
						this.changeStyles(selector + ' svg', {'width': '', 'height': ''});
					}

					if (this.hasVal(setting.content.color)) {
						this.changeStyles(selector + ' svg', {'fill': setting.content.color});
					}
				} else {
					this.reload();
				}
			} else if (setting.content.tab == 'image') {

				selector = '#' + this.edit_settings.edited_id + setting.setting;
				target = this.$iframe.find(selector);

				if (setting.content.image && target.length)
					target.attr("src", setting.content.image);
				else this.reload();
			}

		} else if (setting.action == 'change_icon_simple') {
			target.html(setting.content.code);
		} else if (setting.action == 'change_button') {

			if (setting.reload) {
				this.reload();
				if (setting.custom_font) builder.$iframe.find("head").append("<link href='https://fonts.googleapis.com/css?family=" + setting.custom_font + "&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");
			} else {

				target.removeClass(setting.remove);
				target.addClass('ve_content_button_style_' + setting.content);

				target.removeClass('ve_content_button_size_small ve_content_button_size_medium ve_content_button_size_big ve_content_button_size_custom');
				target.addClass('ve_content_button_size_' + setting.size);

				target.removeClass('ve_content_button_type_1 ve_content_button_type_2 ve_content_button_type_3 ve_content_button_type_4 ve_content_button_type_5 ve_content_button_type_6 ve_content_button_type_7 ve_content_button_type_8 ve_content_button_type_9 ve_content_button_type_10 ve_content_button_type_11 ve_content_button_type_12 ve_content_button_type_13');
				target.addClass('ve_content_button_type_' + setting.type);

				target.removeClass('ve_cb_hover_lighter ve_cb_hover_scale ve_cb_hover_darker ve_cb_hover_custom');
				target.addClass('ve_cb_hover_' + setting.hover);

				if (setting.size == 'custom') {
					target.css('font-size', setting.custom_size + 'px');
				} else {
					target.css('font-size', '');
				}
			}
		} else if (setting.action == 'show_hide') {
			if (setting.content) target.show();
			else target.hide();
		} else if (setting.action == 'hide_show') {
			if (setting.content) target.hide();
			else target.show();
		} else if (setting.action == 'toggle_class') {

			if (setting.content) {

				target.addClass(setting.class);
			} else target.removeClass(setting.class);
		} else if (setting.action == 'toggle_class_inverse') {
			if (setting.content) target.removeClass(setting.class);
			else target.addClass(setting.class);
		} else if (setting.action == 'change_text') {
			target.html(setting.content);
		} else if (setting.action == 'change_float_formatted') {
			var content = setting.content !== '' ? setting.content : '0';
			var dotted = content.replace(',', '.');
			var number = parseFloat(dotted);
			var numericString = number.toString();
			var stringParts = dotted.split('.');
			var numberParts = numericString.split('.');

			numericString = numberParts.length >= 2
				? numberParts[0].replace(/(\d)(?=(\d{3})+$)/g, '$1 ') + ',' + numberParts[1]
				: numericString.replace(/(\d)(?=(\d{3})+$)/g, '$1 ');

			if (numberParts.length === 1 && stringParts.length >= 2) {
				numericString += ',0';
			}

			target.html(numericString);
		} else if (setting.action == 'change_default_text') {
			var content = setting.content;
			if (setting.content == '' && setting.setting)
				content = setting.setting;

			target.html(content);
		} else if (setting.action == 'change_default_text_footer') {
			var content = setting.content;
			content = content.replace("{current_year}", new Date().getFullYear());
			if (setting.content == '' && setting.setting)
				content = setting.setting;

			target.html(content);
		} else if (setting.action == 'change_form_field_text') {
			var old_text = '';
			if (target.find('.ve_form_label').length) {
				old_text = target.find('.ve_form_label').html();
				if (old_text.indexOf('*') !== -1) setting.content = setting.content + '*';
				target.find('.ve_form_label').html(setting.content);
			} else {
				if (target.hasClass('ve_form_row_type_select')) {
					old_text = target.find('select option:eq(0)').html();
					if (old_text.indexOf('*') !== -1) setting.content = setting.content + '*';
					target.find('option:eq(0)').html(setting.content);
				} else if (target.hasClass('ve_form_row_type_agree')) {
					old_text = target.find('span').html();
					if (old_text.indexOf('*') !== -1) setting.content = setting.content + '*';
					target.find('span').html(setting.content);
				} else {
					old_text = target.find('.ve_form_field').attr('placeholder');
					if (old_text.indexOf('*') !== -1) setting.content = setting.content + '*';
					target.find('.ve_form_field').attr('placeholder', setting.content);
				}
			}
		} else if (setting.action == 'reload_text') {

			if (target.length && setting.content != '') {
				target.html(setting.content);
			} else if (target.closest('.element_container').not('.loading')) {
				this.reload();
			}
		} else if (setting.action == 'add_text') {
			target.html(setting.content);
			if (setting.content) target.show();
			else target.hide();
		} else if (setting.action == 'add_text_nodisp') {
			target.html(setting.content);
			if (setting.content) target.removeClass('ve_nodisp');
			else target.addClass('ve_nodisp');
		} else if (setting.action == 'change_attr') {
			target.attr(setting.setting, setting.content);
		} else if (setting.action == 'change_link') {
			console.log(setting.link_target);
			target.attr('target', setting.link_target);
			target.attr('href', setting.content);
		} else if (setting.action == 'reload') {
			this.reload();
		} else if (setting.action == 'reload_logo') {
			var header_setting = this.getFormSetting();
			this.addLoading();
			$.post(ajaxurl, {
				"action": 'reload_header_logo',
				"header_setting": header_setting,
				"post_id": this.post_id
			}, function (data) {
				builder.$iframe.find('#site_title').replaceWith(data);
				builder.removeLoading();
			})
		} else if (setting.action == 'change_menu') {
			this.addLoading();

			$.post(ajaxurl, {
				"action": 'reload_menu',
				"menu_id": setting.content,
				"target": this.edit_settings.edited_id
			}, function (data) {
				target.html(data);
				builder.removeLoading();
				document.getElementById("mw_page_builder").contentWindow.reload_replaced_header_menu();
			});
		} else if (setting.action == 'reload_row_background') {
			this.addLoading();

			var row_setting = this.getFormSetting();
			$.post(ajaxurl, {
				"action": 'reload_row_background',
				"row": row_setting,
				'row_id': this.edit_settings.edited_id
			}, function (data) {
				target.replaceWith(data);
				builder.removeLoading();
			}).fail(function (data) {
				console.log(data);
				console.log('akce se nezdařila');
				builder.removeLoading();
			});
		} else if (setting.action == 'change_shape_divider') {

			if (setting.content.show) {

				if (target.css('display') == 'none') {
					var show_color;
					if (setting.setting == 'bottom') show_color = target.closest('.row').next('.row').css('background-color');
					else show_color = target.closest('.row').prev('.row').css('background-color');
					if (show_color) {
						setting.content.color = show_color;

						$('#ve_style_shape_' + setting.setting + '_color').minicolors('value', show_color);
					}
				}


				var old_shape = target.attr('data-shape');
				target.removeClass('mw_row_shape_' + old_shape);
				target.addClass('mw_row_shape_' + setting.content.shape);
				target.attr('data-shape', setting.content.shape);
				target.show();
				target.html(setting.content.code);

				this.changeStyle(selector, 'height', setting.content.size + 'px', 'desktop');

				// mobile size
				if (this.hasVal(setting.content.mobile_size))
					this.changeStyle(selector, 'height', setting.content.mobile_size + 'px', 'mobile');
				// tablet size
				if (this.hasVal(setting.content.tablet_size))
					this.changeStyle(selector, 'height', setting.content.tablet_size + 'px', 'tablet');


				this.changeStyle(selector + ' svg', 'fill', setting.content.color, 'desktop');
				target.removeClass('mw_row_shape_divider_flip');
				if (setting.content.flip)
					target.addClass('mw_row_shape_divider_flip');
			} else {
				target.html('');
				target.hide();
			}

		} else if (setting.action == 'reload_body_background') {
			this.addLoading();

			var body_setting = this.getFormSetting();
			$.post(ajaxurl, {
				"action": 'reload_body_background',
				"set": body_setting,
				'row_id': this.edit_settings.edited_id
			}, function (data) {
				target.replaceWith(data);
				builder.removeLoading();
			}).fail(function (data) {
				console.log(data);
				builder.removeLoading();
			});
		} else if (setting.action == 'switch_setting') {
			//console.log(builder.edit_settings);
			if (setting.content == 'none') {

				this.$el.find('.mw_panel_setting_toswitch_container').html('');
				this.$iframe.find(builder.edit_settings.target).html('').hide();

				if (this.edit_settings.edited_id == 'header') {
					this.$iframe.find('header .visual_content').remove();
				}

			} else {
				builder.working();
				this.addLoading();

				if (this.edit_settings.edited_id == 'header')
					this.$iframe.find('header').show();
				else
					this.$iframe.find(this.edit_settings.target).show();

				if (setting.content == 'global') builder.$el.find('.mw_panel_setting_toswitch_container').hide();
				else builder.$el.find('.mw_panel_setting_toswitch_container').show();
				$.post(ajaxurl, {
					"action": 'switch_editor_setting_inpanel',
					"type": setting.content,
					'set_id': setting.setid,
					'item_id': builder.post_id
				}, function (data) {
					builder.$el.find('.mw_panel_setting_toswitch_container').html(data);
					builder.reload();
					builder.setEditActions('.mw_panel_setting_toswitch_container .mw_onedit_action');
					builder.working(0);
				});
			}


		} else if (setting.action == 'change_tb_padding') {
			this.changeStyles(selector, {'padding-top': setting.content, 'padding-bottom': setting.content});
		} else if (setting.action == 'change_gmap') {

			var windowjQuery = document.getElementById('mw_page_builder').contentWindow.jQuery;
			var wmap = this.$iframe.find(builder.edit_settings.target + ' .mw_google_map_container');

			map = windowjQuery.data(wmap[0], 'map');
			map.map.setZoom(parseInt(setting.zoom));

			if (map.adress != setting.content)
				this.reload();

		} // change header bg
		else if (setting.action == 'change_header_bg') {
			// gradient
			var transparent = false;
			var $header = builder.$iframe.find('#header');
			var $body = builder.$iframe.find('body');

			if (setting.type == 'gradient') {
				this.changeStyles('#' + this.edit_settings.edited_id, {"background": "linear-gradient(to bottom, " + setting.content.color1 + " 0%," + setting.content.color2 + " 100%) no-repeat border-box"});

				if (parseFloat(setting.content.transparency1) < 1 || parseFloat(setting.content.transparency2) < 1)
					transparent = true;
				// background-color
			} else if (setting.type == 'background-color') {
				//target.css('background',setting.content.color1);
				this.changeStyles('#' + this.edit_settings.edited_id, {'background': setting.content.color1});
				if (parseFloat(setting.content.transparency1) < 1 || setting.content.color1 == 'transparent')
					transparent = true;
			}
			if (transparent) {
				$header.addClass('mw_transparent_header');
				$body.addClass('page_with_transparent_header');
			} else if ($header.hasClass('mw_transparent_header')) {
				document.getElementById('mw_page_builder').contentWindow.unsetForTransparentHeader();
				$header.removeClass('mw_transparent_header');
				$body.removeClass('page_with_transparent_header');
			}
		} else if (setting.action == 'change_progress') {
			var content;

			content = setting.content.replace('px', "%");

			this.$iframe.find('#' + this.edit_settings.edited_id + ' .ve_progressbar_percentage').html(content);

			var selector1 = '#' + this.edit_settings.edited_id + ' .ve_progressbar_prog'
			this.changeStyle(selector1, 'min-width', content);
		}

		this.callOnChange(set_is_edited);
		//console.log(JSON.stringify(this.el_setting));

	};
	MwPageBuilder.prototype.callOnChange = function (set_is_edited = true) {
		if (set_is_edited) this.isEdited();
		if (this.edit_settings.target == '#header')
			this.setForTransparentHeader();
	}
	MwPageBuilder.prototype.callOnReload = function (type) {

		if (type == 'header') {
			this.setForTransparentHeader(true);
		} else if (type == 'row') {
			this.setForTransparentHeader();
			this.$iframe.find(".mw_page_builder_droparea").droppable(this.droppableSetting);
		}
		if (type == 'element') {
			this.$iframe.find(".mw_page_builder_droparea").droppable(this.droppableSetting);
		}
	}

	MwPageBuilder.prototype.hasVal = function (val) {
		if (typeof (val) === 'undefined') return false;
		else return true;
	}
	MwPageBuilder.prototype.working = function (working = true) {
		if (working) $(".ve_editor_panel").addClass('mw_editor_panel_loading');
		else $(".ve_editor_panel").removeClass('mw_editor_panel_loading');
	}
	MwPageBuilder.prototype.changeStyle = function (selector, style, val, device = 'desktop') {
		var styles = this.getStyles(device);

		if (!styles[selector]) styles[selector] = {};
		if (!styles[selector][style]) styles[selector][style] = '';

		styles[selector][style] = val;

		this.saveStyles(styles, device);
	}
	MwPageBuilder.prototype.changeStyles = function (selector, newstyles, device = 'desktop') {

		var styles = this.getStyles(device);

		styles = mwChangeCssStyles(styles, selector, newstyles); // in admin.js

		this.saveStyles(styles, device);
	}
	MwPageBuilder.prototype.getStyles = function (device = 'desktop') {
		var css_id;
		if (this.edit_settings.target == 'body') css_id = "#body_style"
		else css_id = '#' + this.edit_settings.edited_id + '_style';
		if (device != 'desktop') css_id += '_' + device;
		var css = this.$iframe.find(css_id).text();
		var array = {};

		if (device == 'mobile') {
			css = css.replace('}}', '}');
			css = css.replace('@media screen and (max-width: 767px) {', '');
		} else if (device == 'tablet') {
			css = css.replace('}}', '}');
			css = css.replace('@media screen and (max-width: 969px) {', '');
		}

		array = mwGetCssStyles(css); // in admin.js

		return array;
	}
	MwPageBuilder.prototype.saveStyles = function (styles, device = 'desktop') {
		//var css=this.$iframe.find('#'+this.edit_settings.edited_id+'_style').text();
		var tosave = '';

		tosave = mwSaveCssStyles(styles); // in admin.js

		if (device == 'mobile') {
			tosave = '@media screen and (max-width: 767px) {' + tosave + '}';
		} else if (device == 'tablet') {
			tosave = '@media screen and (max-width: 969px) {' + tosave + '}';
		}

		var css_id;
		if (this.edit_settings.target == 'body') css_id = "#body_style"
		else css_id = '#' + this.edit_settings.edited_id + '_style';

		if (device != 'desktop') css_id += '_' + device;

		this.$iframe.find(css_id).text(tosave);

	}

	$.fn.mwPageBuilder = function (options) {
		return this.each(function (index, el) {
			el.MwPageBuilder = new MwPageBuilder(el, options);
		});
	};


	var MwOnEditAction = (function (element, settings) {

		var instanceUid = 0;

		function _MwOnEditAction(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
				type: this.$el.attr('data-type'),
				action: this.$el.attr('data-action'),
				setname: this.$el.attr('data-setname'),
				add_new_setting: null
			};
			this.settings = $.extend({}, this, this.defaults, settings);
			this.return = {};

			var obj = this;

			if (this.settings.type == 'text' || this.settings.type == 'size') {

				this.$el.find('.mw_input').keypress(function (event) {
					var keycode = (event.keyCode ? event.keyCode : event.which);
					if (keycode == '13') {
						$(this).blur();
						return false;
					}
				});

			}

			// background
			if (this.settings.type == 'background') {

				this.$el.find('.background_color_field_container').mwBackgroundColor({
					change: function () {
						obj.getVal();
					}
				});

			}
			// transparent color
			else if (this.settings.type == 'transparent_color') {

				this.$el.find('.cms_color_input').minicolors({
					opacity: true,
					format: 'hex',
					swatches: ve_used_colors,
					change: function (value, opacity) {
						var container = $(this).closest('.cms_transparent_color_container');
						container.find('.cms_transparent_color_transparency').val(opacity);
						container.find('.cms_color_transparency_view').html(Math.round(opacity * 100) + '%');
						if (value) {
							container.find('.cms_transparent_color_rgba').val($(this).minicolors('rgbaString'));
						} else {
							container.find('.cms_transparent_color_rgba').val('');
						}

						obj.getVal();
					}
				});
			}
			// color
			else if (this.settings.type == 'color') {
				var swatches = ve_used_colors;
				if(this.$el.find('.cms_color_input').data('hide_swatches') != undefined) {
					swatches = [];
				}
				this.$el.find('.cms_color_input').minicolors({
					position: this.$el.find('.cms_color_input').data('position'),
					swatches: swatches,
					change: function (value, opacity) {
						obj.getVal();
					}
				});
			}
			// image uploader
			else if (this.settings.type == 'bgimage' || this.settings.type == 'image' || this.settings.type == 'image_url') {
				this.$el.find('.mw_image_uploader').mwImageUplader({
					change: function () {
						obj.getVal();
					}
				});

			}
			// image selector
			else if (this.settings.type == 'imageselect' || this.settings.type == 'blogselect') {
				this.$el.mwImageSelect({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// icon selector
			else if (this.settings.type == 'iconselect') {
				this.$el.mwIconSelect({
					change: function () {
						obj.getVal();
					}
				});
			}
			// font
			else if (this.settings.type == 'font') {
				this.$el.mwFontSetting({
					change: function () {
						obj.getVal();
					}
				});
			}
			// select page
			else if (this.settings.type == 'selectpage') {
				this.$el.find('.mw_item_selector').mwPageSelect({
					change: function () {
						obj.getVal();
					}
				});
			}
			// select item and product
			else if (this.settings.type == 'item_select' || this.settings.type == 'product_select') {
				this.$el.find('.mw_item_selector').mwItemSelect({
					change: function () {
						obj.getVal();
					}
				});
			}
			// select
			else if (this.settings.type == 'select'
				|| this.settings.type == 'term_select'
				|| this.settings.type == 'item_select'
				|| this.settings.type == 'event_category_select'
				|| this.settings.type == 'category_select'
				|| this.settings.type == 'selectcampaign'
				|| this.settings.type == 'shippings_type_select'
				|| this.settings.type == 'payment_method_type_select'
				|| (typeof this.settings.type === 'string' && this.settings.type.indexOf('mw_select', this.settings.type.length - 9) !== -1)) {
				this.$el.find('select').change(function () {
					obj.getVal($(this).val());
				});
			}
			else if (this.settings.type == 'country_select') {
				this.$el.find('select').change(function () {
					obj.getVal($(this).val());
				});
				this.$el.find('.mw_whisperer').chosen({width: "100%"});
			}
			// radio
			else if (this.settings.type == 'radio') {
				this.$el.find('input').change(function () {
					obj.getVal($(this).val());
				});
			}
			// slider
			else if (this.settings.type == 'slider') {

				var saction = 'slide';
				if (this.settings.action == 'reload')
					saction = 'stop';

				this.$el.find('.mw_slider').mwSlider({
					action: saction,
					change: function () {
						var val = 1;
						if (obj.$el.find(".cms_slider_val").val() == '') val = 0;
						obj.getVal(val);
					}
				});

			}
			// border
			else if (this.settings.type == 'border') {
				this.$el.find('.cms_border_set_container').mwBorder({
					change: function () {
						obj.getVal();
					}
				});
			}
			// shape divider
			else if (this.settings.type == 'shape_divider') {
				this.$el.find('.cms_shape_divider_container').mwShapeDivider({
					change: function () {
						obj.getVal();
					}
				});
			}
			// background set
			else if (this.settings.type == 'background_set') {
				this.$el.find('.cms_background_set_container').mwBackgroundSet({
					change: function () {
						obj.getVal();
					}
				});
			}
			// gallery
			else if (this.settings.type == 'image_gallery') {
				this.$el.find('.cms_image_gallery_container').mwGallery({
					change: function () {
						obj.getVal();
					}
				});
			}
			// imageoption
			else if (this.settings.type == 'imageoption') {
				this.$el.mwImageOption({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// switch
			else if (this.settings.type == 'switch') {
				this.$el.find('.mw_switch input').change(function () {
					if ($(this).prop("checked")) {
						obj.getVal(1);
					} else {
						obj.getVal(0);
					}
				});

			}
			// status switch
			else if (this.settings.type == 'status_switch') {
				this.$el.find('.mw_switch_container').mwStatusSwitch({
					change: function (val) {
						console.log(val);
						obj.getVal(val);
					}
				});
			}
			// page selector
			else if (this.settings.type == 'page_link') {
				this.$el.mwLinkOption({
					change: function () {
						obj.getVal();
					}
				});
			}
			// button option
			else if (this.settings.type == 'button') {
				this.$el.mwButtonOption({
					change: function () {
						obj.getVal(0);
					},
					reload: function () {
						obj.getVal(1);
					}
				});
			}
			// multiple checkbox
			else if (this.settings.type == 'multiple_checkbox') {
				this.$el.find('.mw_switch input').change(function () {
					obj.getVal();
				});
			}
			// text
			else if (this.settings.type == 'text') {
				if (this.settings.action == 'reload') {
					this.$el.find('input').change(function () {
						obj.getVal();
					});
				} else {
					this.$el.find('input').keyup(function () {
						obj.getVal();
					});
				}
			}
			// textarea
			else if (this.settings.type == 'textarea') {
				if (this.settings.action == 'reload') {
					this.$el.find('textarea').change(function () {
						obj.getVal();
					});
				} else {
					this.$el.find('textarea').keyup(function () {
						obj.getVal();
					});
				}
			}
			// switch
			else if (this.settings.type == 'miocarousel') {
				this.$el.find('.mw_switch input').change(function () {
					obj.getVal();
				});
				this.$el.find('select').change(function () {
					obj.getVal();
				});
				this.$el.find('.mw_input').change(function () {
					obj.getVal();
				});
				this.$el.find('.mw_input').keypress(function (event) {
					var keycode = (event.keyCode ? event.keyCode : event.which);
					if (keycode == '13') {
						$(this).blur();
						return false;
					}
				});

			}
			// menu
			else if (this.settings.type == 'selectmenu') {
				this.$el.find('.ve_menuselect_container').mwMenu({
					change: function () {
						obj.getVal();
					}
				});
			}
			// tabs
			else if (this.settings.type == 'tabs') {
				this.$el.find('.mw_tabs').mwTabs({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// toggle group
			else if (this.settings.type == 'toggle_group') {
				this.$el.mwToggleGroup({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// switch setting
			else if (this.settings.type == 'switch_setting') {
				this.$el.find('select').change(function () {
					var val = $(this).val();
					obj.getVal(val);
				});
			}
			// multielement
			else if (this.settings.type == 'multielement') {
				this.$el.mwMultiElementManager({
					change: function () {
						obj.getVal();
					},
					add: function (selector) {
						obj.getVal();
						if (obj.settings.add_new_setting) {
							obj.settings.add_new_setting.call(this, selector);
						}
					}
				});
			}
			// simple feature
			else if (this.settings.type == 'simple_feature') {
				this.$el.mwSimpleFeatures({
					change: function () {
						obj.getVal();
					},
					add: function (selector) {
						obj.getVal();
						if (obj.settings.add_new_setting) {
							obj.settings.add_new_setting.call(this, selector);
						}
					}
				});
			}
			//interval table
			else if (this.settings.type == 'interval_table') {
				this.$el.mwIntervalTable({});
			}
			// window editor
			else if (this.settings.type == 'weditor' || this.settings.type == 'popupselect') {
				this.$el.find('.ve_windowselect_container').mwWeditorManager({
					change: function () {
						obj.getVal();
					}
				});
			}
			// google map
			else if (this.settings.type == 'google_map') {
				this.$el.find('.mw_google_map_container').mwGoogleMapSetting({
					change: function () {
						obj.getVal();
					}
				});
			}
			// menu
			else if (this.settings.type == 'date' || this.settings.type == 'datetime') {
				this.$el.mwDatePicker({
					change: function () {
						obj.getVal();
					}
				});
			}
			// buttons editor
			else if (this.settings.type == 'buttons_editor') {
				this.$el.find('.mw_buttons_editor').mwButtonsEditor({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// fapi form selector
			else if (this.settings.type == 'sale_form_select') {
				this.$el.mwSaleFormSelector({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// email marketing selector
			else if (this.settings.type == 'list_select' || this.settings.type == 'form_select') {
				this.$el.mwEmailMarketingSelector({
					change: function () {
						obj.getVal();
					}
				});
			}
			// upload file
			else if (this.settings.type == 'upload_file') {
				this.$el.mwUploadFile({
					change: function () {
						obj.getVal();
					}
				});
			}
			// ab testing statistics
			else if (this.settings.type == 'page_statistics') {
				this.$el.mwAbStatisticks({});
			// campaign
			}
			else if (this.settings.type == 'campaigns') {
				this.$el.find('.mioweb_campaign_setting_container').mwCampaignManager({
					change: function () {
						obj.getVal();
					}
				});
			// member selector
			}
			else if (this.settings.type == 'selectmember') {
				this.$el.mwMemberSelector({
					change: function () {
						obj.getVal();
					}
				});
			}
			else if (this.settings.type == 'membership_creator') {
				this.$el.mwMembershipCreator({
					change: function () {
						obj.getVal();
					}
				});
			}
			// code list
			else if (this.settings.type == 'code_list') {
				this.$el.mwCodeList({
					change: function () {
						obj.getVal();
					},
					// add: function (selector) {
					// 	obj.$el.find(selector + ' .mw_switch_container').mwStatusSwitch({
					// 		change: function (val) {
					// 			obj.getVal(val);
					// 		}
					// 	});
					// },
				});

				this.$el.find('.mw_switch_container').mwStatusSwitch({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// eshop variants
			else if (this.settings.type == 'variantList') {
				this.$el.find('.mws_variants_manager').mwsVariants({
					change: function () {
						obj.getVal();
					}
				});

			}
			// funnel sell setting
			else if (this.settings.type == 'funnel_sell_setting') {
				this.$el.find('.mw_funnel_sell_setting_container').mwFunnelSellSetting({
					change: function () {
						obj.getVal();
					}
				});

			}
			// post set
			else if (this.settings.type == 'item_set') {
				this.$el.mwPostSetField({
					change: function () {
						obj.getVal();
					}
				});
			}
			// post or term title
			else if (this.settings.type == 'post_title' || this.settings.type == 'term_title') {
				this.$el.mwPostTitleField({
					change: function () {
						obj.getVal();
					}
				});
			}
			// select product type
			else if (this.settings.type == 'select_product_type') {
				this.$el.mwsProductTypeSelector({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// select product type
			else if (this.settings.type == 'product_stock_setting') {
				this.$el.mwsStockSetting({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// upsells
			else if (this.settings.type == 'multielement') {
				this.$el.mwMultiElementManager({
					change: function () {
						obj.getVal();
					},
					add: function (selector) {
						obj.getVal();
						if (obj.settings.add_new_setting) {
							obj.settings.add_new_setting.call(this, selector);
						}
					}
				});
			}
			// paygate
			else if (this.settings.type == 'paygate_select') {
				this.$el.find('.mws_paygate_select').mwsSelectPaygate({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// number series
			else if (this.settings.type == 'number_series') {
				this.$el.mwsNumberSeries({
					change: function () {
						obj.getVal();
					},
				});
			}
			// eshop automations
			else if (this.settings.type == 'eshop_automations') {
				this.$el.mwsAutomations({
					change: function () {
						obj.getVal();
					},
				});
			}
			// eshop upsells
			else if (this.settings.type == 'upsells') {
				this.$el.mwsUpsellsField({
					change: function () {
						obj.getVal();
					},
				});
			}
			// blog select page
			else if (this.settings.type == 'blog_selectpage') {
				this.$el.mwBlogSelectPage({
					change: function () {
						obj.getVal();
					},
				});
			}
			// transaction email
			else if (this.settings.type == 'transaction_email') {
				this.$el.mwTransactionEmail({
					change: function () {
						obj.getVal();
					},
				});

				this.$el.find('.mw_switch_container').mwStatusSwitch({
					change: function (val) {
						obj.getVal(val);
					}
				});
			}
			// product tags
			else if (this.settings.type == 'product_tags') {
				this.$el.mwsProductTags({
					change: function () {
						obj.getVal();
					}
				});
			}
			// emails
			else if (this.settings.type == 'emails') {
				this.$el.find('.mw_email_field_item').mwTransactionEmail({
					change: function () {
						obj.getVal();
					},
				});
			}
			// user password
			else if (this.settings.type == 'user_password') {
				this.$el.find('.mw_user_password_fieldtype').mwUserPassword({
					change: function () {
						obj.getVal();
					},
				});
			}
			// currency exchange
			else if (this.settings.type == 'currency_exchange') {
				this.$el.find('.mw_switch').click(function () {
					obj.getVal();
				});
				this.$el.find('input').keyup(function () {
					obj.getVal();
				});
			}
			// noaccess
			else if (this.settings.type == 'noaccess_content') {
				this.$el.find('.mw_item_selector').mwPageSelect({
					change: function () {
						obj.getVal();
					}
				});
			}
			// smtp test
			else if (this.settings.type == 'smtp_test') {
				this.$el.find('.mw_smtp_test_container').mwTestSMTP();
			}
			else if (this.settings.type == 'heureka_category_list' || this.settings.type == 'zbozi_category_list' /*|| this.settings.type == 'shipping_country'*/) {
				this.$el.find('.mw_whisperer').chosen({width: "100%"});
			}
			else if(this.settings.type == 'funnel_page') {
				this.$el.find('.mw_funnel_page_setting_container').mwFunnelPageSelector({});
			}

			this.instanceUid = instanceUid++;
		}

		return _MwOnEditAction;

	})();

	// get setting for change
	MwOnEditAction.prototype.getVal = function (val = '') {
		// hide/show setting

		if ($.type(val) === "string")
			val = val.replace("%", "");

		var show_group = this.$el.attr('data-show');
		if (show_group) {
			$('.cms_show_group_' + show_group).hide();
			$('.cms_show_group_' + show_group + '_' + val).show();
		}

		// get setting of field
		var field_setting;

		field_setting = {
			action: this.settings.action, // action after change / field[onedit][action]
			setname: this.$el.attr('data-setname'), // of changed field / field[id]
			target: this.$el.attr('data-target'), // css target inside edited area / field[onedit][target]
			css: this.$el.attr('data-css'), // css atribute to change / field[onedit][css]
			class: this.$el.attr('data-class'), // class name to use / field[onedit][class]
			setting: this.$el.attr('data-setting'), // setting for action / field[onedit][class]
			device: this.$el.attr('data-device'), // setting for action / wich device setting is edited
		};

		field_setting.multielement_selector = '';
		if (this.$el.attr('data-multielement')) {
			var item_index = this.$el.closest('.ve_multielement_container').find('.ve_item_container').index(this.$el.closest('.ve_item_container'));
			field_setting.multielement_selector = field_setting.target.replace('[qt]', '_' + item_index);
			field_setting.target = field_setting.target.replace('[qt]', ':eq(' + item_index + ')');
			field_setting.setting = field_setting.setting.replace('[qt]', ':eq(' + item_index + ')');
		}

		// background
		if (this.settings.type == 'background') {
			var color1 = this.$el.find('.cms_background_start_color .cms_color_rgba').val();
			var color2 = this.$el.find('.cms_background_end_color .cms_color_rgba').val();
			var transparency1 = this.$el.find('.cms_background_start_color .cms_color_transparency').val();
			var transparency2 = this.$el.find('.cms_background_end_color .cms_color_transparency').val();

			if (color1 == '') color1 = 'transparent';

			if (this.$el.find('.mw_switch input').prop("checked") && color2) {
				field_setting.type = 'gradient';
				field_setting.content = {
					color1: color1,
					transparency1: transparency1,
					color2: color2,
					transparency2: transparency2,
					gradient: 1
				};
			} else {
				field_setting.type = 'background-color';
				field_setting.content = {
					color1: color1,
					transparency1: transparency1,
					color2: '',
				};
			}
		}
		// color
		if (this.settings.type == 'color') {
			field_setting.type = 'color';
			field_setting.content = this.$el.find('input').val();
		}
		// transparent color
		if (this.settings.type == 'transparent_color') {
			field_setting.type = 'transparent_color';
			field_setting.content = this.$el.find('input.cms_transparent_color_rgba').val();
		}
		// bgimage
		else if (this.settings.type == 'bgimage') {
			var img = this.$el.find('.desktop_setting img').attr('src');
			var fullimg = this.$el.find('.mw_image_uploader_full_image').val();
			var tablet_img = this.$el.find('.tablet_setting img').attr('src');
			var mobile_img = this.$el.find('.mobile_setting img').attr('src');
			var cover;
			var hide_mobile = 0;
			var efect = this.$el.find('.mw_bgimage_efect_container select').val();
			var repeat = this.$el.find('.mw_bgimage_repeat_container select').val();
			var position = this.$el.find('.mw_image_uploader_position').val();
			var tablet_position = this.$el.find('.tablet_setting .mw_image_uploader_position').val();
			var mobile_position = this.$el.find('.mobile_setting .mw_image_uploader_position').val();
			var filter_color = this.$el.find('.mw_bgimage_color_filter_val input.cms_transparent_color_rgba').val();
			var size = this.$el.find('.mw_bgimage_desktop_size_container .cms_slider_val').val();
			var tablet_size = this.$el.find('.mw_bgimage_tablet_size_container .cms_slider_val').val();
			var mobile_size = this.$el.find('.mw_bgimage_mobile_size_container .cms_slider_val').val();

			var pattern = this.$el.find('.mw_image_uploader_pattern').css('background-image');
			var tablet_pattern = this.$el.find('.tablet_setting .mw_image_uploader_pattern').css('background-image');
			var mobile_pattern = this.$el.find('.mobile_setting .mw_image_uploader_pattern').css('background-image');

			if (this.$el.find('.mw_bgimage_cover_container input').prop("checked")) cover = 1;
			else cover = 0;

			if (this.$el.find('.mw_bgimage_mobile_hide_container input').prop("checked")) hide_mobile = 1;
			else hide_mobile = 0;

			if (this.$el.find('.mw_bgimage_color_filter .mw_switch input').prop("checked") && img) {

			} else {
				filter_color = '';
			}

			field_setting.content = {
				img: img,
				fullimg: fullimg,
				tablet_img: tablet_img,
				mobile_img: mobile_img,
				pattern: pattern,
				tablet_pattern: tablet_pattern,
				mobile_pattern: mobile_pattern,
				filter_color: filter_color,
				cover: cover,
				efect: efect,
				repeat: repeat,
				position: position,
				tablet_position: tablet_position,
				mobile_position: mobile_position,
				hide_mobile: hide_mobile,
				size: size,
				tablet_size: tablet_size,
				mobile_size: mobile_size,
			};
			//console.log(field_setting);
		}
		// image
		else if (this.settings.type == 'image') {
			var img = this.$el.find('img').attr('src');
			field_setting.position = '';
			field_setting.content = img;
			if (this.$el.find('.mw_image_uploader_position').length) {
				field_setting.position = this.$el.find('.mw_image_uploader_position').val();
			}
		}
		// font
		if (this.settings.type == 'font') {
			var font = this.$el.find('.font_selected_input').val();
			var weight = this.$el.find('.font_weight_select').val();
			var file = this.$el.find('.font_weight_select').find(':selected').data('file');
			var size = this.$el.find('.font_size_slider .desktop_setting input').val();
			var align = this.$el.find('.font_align_select select').val();
			var mobile_size = this.$el.find('.font_size_slider .mobile_setting input').val();
			var tablet_size = this.$el.find('.font_size_slider .tablet_setting input').val();
			var line_height = this.$el.find('.font_line_height_slider input').val();
			var letter_spacing = this.$el.find('.font_letter_spacing_slider input').val();
			var color = this.$el.find('.cms_font_color').val();
			var shadow = this.$el.find('.cms_font_shadow').val();
			var use_font = this.$el.find('.cms_font_use').val();
			var allweights = this.$el.find('.font_weight_select option').map(function () {
				return this.value;
			}).get().join(',');
			var capitals = 0;

			if (this.$el.find('.cms_font_capitals input').prop("checked")) {
				capitals = 1;
			}

			if (this.$el.find('.font_size_slider .mobile_setting input').length && !mobile_size && !tablet_size) {
				if (size > 100) mobile_size = 70;
				else if (size > 50) mobile_size = 50;
				else if (size > 35) mobile_size = 35;
			}

			field_setting.type = 'font';
			field_setting.content = {
				font: font,
				use_font: use_font,
				weight: weight,
				file: file,
				allweights: allweights,
				line_height: line_height,
				letter_spacing: letter_spacing,
				color: color,
				size: size,
				align: align,
				mobile_size: mobile_size,
				tablet_size: tablet_size,
				shadow: shadow,
				capitals: capitals,
			};

		}
		// select
		else if (this.settings.type == 'select') {
			var remove_class = '';
			var change_class = this.$el.attr('data-class');

			this.$el.find('option').each(function () {
				remove_class = remove_class + ' ' + change_class + $(this).val();
			});

			field_setting.class = this.$el.attr('data-class');
			field_setting.remove = remove_class;
			field_setting.content = this.$el.find('select').val();
		}
		// image option
		else if (this.settings.type == 'imageoption') {
			var remove_class = '';
			var change_class = this.$el.attr('data-class');

			this.$el.find('input').each(function () {
				remove_class = remove_class + ' ' + change_class + $(this).val();
			});

			field_setting.class = this.$el.attr('data-class');
			field_setting.remove = remove_class;
			field_setting.size = this.$el.find('.cms_image_option_custom_container input').val() + 'px';
			field_setting.content = this.$el.find('input:checked').val();

		}
		// imageselect
		else if (this.settings.type == 'imageselect') {

			var remove_class = '';
			var change_class = this.$el.attr('data-class');

			this.$el.find('.cms_is_item a').each(function () {
				remove_class = remove_class + ' ' + change_class + $(this).attr('data-value');
			});

			field_setting.class = this.$el.attr('data-class');
			field_setting.remove = remove_class;
			field_setting.content = this.$el.find('.cms_image_select_val').val();

		} else if (this.settings.type == 'iconselect') {
			field_setting.content = {};
			field_setting.content.size = this.$el.find('.cms_icon_select_size input').val();
			field_setting.content.color = this.$el.find('.cms_icon_select_color input').val();
			field_setting.content.code = this.$el.find('.cms_icon_select_code').val();

			field_setting.content.image = this.$el.find('.cms_icon_select_image img').attr('src');
			field_setting.content.tab = this.$el.find('.cms_icon_select_tab_input').val();

		}
		// page link
		else if (this.settings.type == 'page_link') {

			var link = '';
			var target = '';

			if (this.$el.find('.field_link_target').prop("checked"))
				target = '_blank';

			if (this.$el.find('.mw_switch input').prop("checked")) {
				link = this.$el.find('.mw_input').val();
			} else {
				link = this.$el.find('select option:selected').attr('data-url');
			}

			field_setting.link_target = target;
			field_setting.content = link;

		}
		// button
		else if (this.settings.type == 'button') {

			var custom_size = this.$el.find('.cms_button_selector_size .mw_slider input').val();
			var size = this.$el.find('.cms_button_selector_size select').val();
			var style = this.$el.find('.cms_button_selector input[type="radio"]:checked').val();
			var type = this.$el.find('.cms_button_selector input[type="radio"]:checked').attr('data-type');
			var hover = this.$el.find('.cms_button_selector input[type="radio"]:checked').attr('data-hover');

			var remove_class = '';

			this.$el.find('.cms_button_selector input[type="radio"]').each(function () {
				remove_class = remove_class + ' ve_content_button_style_' + $(this).val();
			});

			field_setting.remove = remove_class;
			field_setting.size = size;
			field_setting.custom_size = custom_size;
			field_setting.content = style;
			field_setting.type = type;
			field_setting.hover = hover;
			field_setting.reload = val;

		}
		//slider
		else if (this.settings.type == 'slider') {
			var val = this.$el.find('.cms_slider_val').val();
			var unit = 'px';
			if (this.$el.find('.cms_slider_unit').length) {
				unit = this.$el.find('.cms_slider_unit:checked').val();
			}
			if (val !== '') val = val + unit;
			field_setting.content = val;
		}
		//switch
		else if (this.settings.type == 'switch') {
			field_setting.content = val;
		}
		//text
		else if (this.settings.type == 'text') {
			field_setting.content = this.$el.find('input').val();
		}
		//text
		else if (this.settings.type == 'textarea') {
			field_setting.content = this.$el.find('textarea').val();
		}
		//selectmenu
		else if (this.settings.type == 'selectmenu') {
			field_setting.content = this.$el.find('.ve_menuselect_selector').val();
		}
		// miocarousel
		else if (this.settings.type == 'miocarousel') {

			field_setting.animation = this.$el.find('.carousel_setting_animation select').val();
			field_setting.scheme = this.$el.find('.carousel_setting_color_scheme select').val();
			field_setting.delay = this.$el.find('.carousel_setting_delay input').val();
			field_setting.speed = this.$el.find('.carousel_setting_speed input').val();

			if (this.$el.find('.carousel_setting_autoplay input').prop("checked")) field_setting.autoplay = 1;
			else field_setting.autoplay = 0;

			if (this.$el.find('.carousel_setting_hide_navigation input').prop("checked")) field_setting.hide_nav = 1;
			else field_setting.hide_nav = 0;

		}
		//tabs
		else if (this.settings.type == 'tabs') {
			field_setting.content = this.$el.find('input:checked').val();
		}
		// border
		else if (this.settings.type == 'border') {
			var size = this.$el.find('.cms_border_set_size input').val();
			field_setting.content = '';
			if (parseInt(size)) {
				var style = 'solid';
				var color = '';
				style = this.$el.find('.cms_border_set_style_val').val();
				var color_input = this.$el.find('.cms_border_set_color .cms_color_input');
				if (color_input.hasClass('cms_color_input_transparent'))
					color = this.$el.find('.cms_border_set_color .cms_color_rgba').val();
				else color = color_input.val();
				field_setting.content = size + 'px ' + style + ' ' + color;
			}
		}
		// shape divider
		else if (this.settings.type == 'shape_divider') {

			field_setting.content = {};

			if (this.$el.find('.cms_shape_divider_show .mw_switch input').prop("checked")) {
				field_setting.content.show = true;
			} else field_setting.content.show = false;

			if (this.$el.find('.cms_shape_divider_flip .mw_switch input').prop("checked")) {
				field_setting.content.flip = true;
			} else field_setting.content.flip = false;

			field_setting.content.shape = this.$el.find('.cms_image_select_val').val();
			field_setting.content.code = this.$el.find('.cms_icon_select_code').val();
			field_setting.content.size = this.$el.find('.cms_shape_divider_height .desktop_setting .cms_slider_val').val();
			field_setting.content.mobile_size = this.$el.find('.cms_shape_divider_height .mobile_setting .cms_slider_val').val();
			field_setting.content.tablet_size = this.$el.find('.cms_shape_divider_height .tablet_setting .cms_slider_val').val();

			field_setting.content.color = this.$el.find('.cms_color_input').val();
			if (!field_setting.content.color) field_setting.content.color = '#ffffff';

		}
		// background set
		else if (this.settings.type == 'background_set') {
			var shadow = this.$el.find('.cms_border_set_shadow select').val();
			var use_font = this.$el.find('.cms_border_set_shadow select').val();
			var corner = this.$el.find('.cms_background_set_corners input:checked').val();
			var color = this.$el.find('.cms_background_set_color .cms_color_input').val();
			var border = false;
			if (this.$el.find('.cms_background_set_border input').prop("checked")) {
				border = true;
			}

			field_setting.content = {};
			field_setting.content.border = border;
			field_setting.content.shadow = shadow;
			field_setting.content.color = color;
			field_setting.content.corner = corner;

		}
		//switch global/local setting
		else if (this.settings.type == 'switch_setting') {
			field_setting.content = this.$el.find('select').val();
			field_setting.setid = this.$el.find('select option:selected').attr('data-setid');
		}
		// google map setting
		else if (this.settings.type == 'google_map') {
			field_setting.content = this.$el.find('.mw_gm_setting_address input').val();
			field_setting.zoom = this.$el.find('.mw_gm_setting_zoom input').val();
		}
		// upload file
		else if (this.settings.type == 'upload_file') {
			field_setting.content = this.$el.find('.cms_upload_file').val();
		}

		// call function on change
		if (this.settings.change) {
			this.settings.change.call(this, field_setting);
		}


	};

	$.fn.mwOnEditAction = function (options) {
		return this.each(function (index, el) {
			el.MwOnEditAction = new MwOnEditAction(el, options);
		});
	};


	// page selector
	var MwPageSelector = (function (element) {

		function _MwPageSelector(element) {

			this.$el = $(element);

			// delete page
			this.$el.find(".ve_delete_page").mwConfirm({
				text: texts.delete_page_confirm,
				onconfirm: function (confirm) {
					//builder.isEdited('0');
					var item_id = confirm.$el.attr('data-id');
					var object_id = confirm.$el.attr('data-objectid');
					var current = confirm.$el.attr('data-current');

					confirm.$el.closest('.ve_page_item_container').slideUp('slow', function () {
						confirm.$el.remove();
					});

					$.post(ajaxurl, {
						"action": "mwDeleteObjectItem",
						"item_id": item_id,
						"object_id": object_id,
					}, function (data) {
						if(current=='1')
						{
							window.location = data.home_url;
						}
					});

					return false;
				}
			});

			//search page
			this.$el.find("#ve_page_search").keyup(function () {
				var searched = $(this).val().toLowerCase();
				var i;
				var j = 0;
				var k;
				var text;
				if (searched != "") {
					$(".ve_page_search_container").addClass('active');
					$(".ve_page_selector_tabs a").removeClass('active');
					$(".ve_page_selector_tabs a[data-target='all']").addClass('active');
					$("#ve_pagelist_empty_search").hide();
					$(".ve_page_selector_list").hide();
					$(".ve_page_selector_list li .ve_page_item_container").hide();
					$(".ve_page_list_home").hide();

					$(".ve_page_selector_list:not(.exclude_search)").each(function () {
						i = 0;
						$("li > div", this).each(function () {
							text = $(".ve_page_item .ve_page_item_title", this).html().toLowerCase();
							url = $(this).find(".ve_page_item").attr('data-slug').toLowerCase();
							if (text.search(searched) >= 0 || url.search(searched) >= 0) {
								$(this).show();
								i++;
							}
						});
						if (i > 0) $(this).closest(".ve_page_selector_list").show();
						else j++;
					});
					if (j == 0) $("#ve_pagelist_empty_search").show();
				} else {
					$(".ve_page_list_home").show();
					$(".ve_page_selector_list li .ve_page_item_container").show();
					$(".ve_page_selector_list").show();
					$(".ve_page_search_container").removeClass('active');
				}
			});

			//page list

			this.$el.find(".ve_page_selector_tabs a").click(function () {
				var target = $(this).attr('data-target');
				$(".ve_page_selector_tabs a").removeClass('active');
				$(this).addClass('active');
				$('.ve_page_selector_list').hide();
				$('.ve_psl_' + target).show();
				$('#ve_page_list').scrollTop(0);
				return false;
			});
			this.$el.find(".ve_page_list_name").click(function () {
				$(this).toggleClass("ve_pln_open");
				$(this).toggleClass("ve_pln_close");
				$(this).closest('.ve_page_selector_list').find('.ve_page_list').toggle();
				return false;
			});

			// duplicate pages

			this.$el.find(".mw_duplicate_page").click(function(){
				var objectId = $(this).attr('data-objectid');
				var itemId = $(this).attr('data-id');
				$(this).mwOpenFastCopyModal({
					action: 'mwSaveFastCopyObject',
					objectId: objectId,
					itemId: itemId,
					onsave: function (data) {
						window.location = data.url;
					}
				});
				return false;
			});

		}

		return _MwPageSelector;

	})();

	$.fn.mwPageSelector = function (options) {
		return this.each(function (index, el) {
			el.MwPageSelector = new MwPageSelector(el, options);
		});
	};

	// change view
	var MwChangeView = (function (element) {

		function _MwChangeView(element, settings) {

			this.defaults = {
				frame: '#mw_page_builder',
				onload: false,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			this.$el = $(element);
			var self = this;

			if (this.settings.onload) {
				$(this.settings.frame).load(function () {
					self.bindActions();
				});
			} else {
				self.bindActions();
			}

		}

		return _MwChangeView;

	})();

	MwChangeView.prototype.bindActions = function (selector) {
		var self = this;

		self.$iframe = $(self.settings.frame).contents();

		self.$el.find(".mw_change_device_preview").click(function (e) {

			$('.mw_device_preview_container').removeClass('mw_device_preview-desktop mw_device_preview-mobile mw_device_preview-tablet');
			$('.mw_device_preview_container').addClass('mw_device_preview-' + $(this).attr('data-device'));
			$('body').removeClass('desktop_view mobile_view tablet_view');
			$('body').addClass($(this).attr('data-device') + '_view');

			self.$iframe.find('body').removeClass('desktop_view mobile_view tablet_view');
			self.$iframe.find('body').addClass($(this).attr('data-device') + '_view');

			e.preventDefault();
		});
	}

	$.fn.mwChangeView = function (options) {
		return this.each(function (index, el) {
			el.MwChangeView = new MwChangeView(el, options);
		});
	};


});

function isColorLight(a) {
	var r, b, g, hsp;

	if (a == 'transparent' || a == '') {
		return true;
	} else {

		if (a.match(/^rgb/)) {
			a = a.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);
			r = a[1];
			g = a[2];
			b = a[3];
		} else {
			a = +("0x" + a.slice(1).replace(
					a.length < 5 && /./g, '$&$&'
				)
			);
			r = a >> 16;
			g = a >> 8 & 255;
			b = a & 255;

		}

		hsp = Math.sqrt(
			0.299 * (r * r) +
			0.587 * (g * g) +
			0.114 * (b * b)
		);

		if (hsp > 200) {
			return true;
		} else {
			return false;
		}
	}
}

function createSortedItems(is_menu) {

	(function ($) {

		var common_options = {
			placeholder: "sortable-hilelight",
			start: function (event, ui) {
				ui.item.addClass('sortable-col-nob');
				ui.placeholder.height(50);
			},
			stop: function (event, ui) {
				ui.item.removeClass('sortable-col-nob');
			}
		};

		/*
        If we are dealing with menu editing, add update function which handles menu item parent id switching
         */
		if (is_menu === true) {
			$.extend(common_options, {
				maxLevels: 3,
				update: function (evt, ui) {

					var changed_item = ui.item,
						new_parent_id;

					if (changed_item.parent('.ve_nestedsortable').length > 0) {
						new_parent_id = 0;
					} else {
						var parent_item = changed_item.parents('.ve_nestedsortable__item:first');
						new_parent_id = parent_item.children('.ve_nestedsortable__item__wrap').find('.menu-item-data-db-id').val();
					}

					changed_item.children('.ve_nestedsortable__item__wrap').find('.menu-item-data-parent-id').val(new_parent_id);

				}
			});
		}

		/*
         Single level sortables
         */
		$(".ve_sortable_items").sortable(
			$.extend({
				handle: '.ve_sortable_handler',
				items: ".ve_sortable_item"
			}, common_options)
		);

		/*
         Nested sortables
         */
		$('.ve_nestedsortable').nestedSortable(
			$.extend({
				handle: '.ve_nestedsortable__item__wrap',
				items: '.ve_nestedsortable__item',
				toleranceElement: '> .ve_nestedsortable__item__wrap'
			}, common_options)
		);

	})(jQuery);

}

function mwGetIframeContent()
{
	return document.getElementById('mw_page_builder').contentWindow;
}

function initialize_google_maps() {
} // init for google map field type

function mw_init_uppy(
	inputId,
	token = null,
	allowedFileTypes = null,
	submitInputSelector = null,
	isRequired = false,
	processDirectly = true,
	autoProceed = true,
	maxNumberOfFiles = 1
) {
	var $input = jQuery('#' + inputId);
	if (!$input.length) {
		return;
	}

	$input.hide();
	var dragDropId = 'uppy-' + inputId;
	$input.after('<div id="' + dragDropId + '"><div class="drag-drop"></div><div class="status-bar"></div></div>')
	var $dragDrop = jQuery('#' + dragDropId);
	var $submit = submitInputSelector ? jQuery(submitInputSelector) : null;

	var uploadedCount = 0;

	if (isRequired && $submit !== null) {
		$submit.hide();
	}

	var onError = () =>
		() => {
			if (!isRequired && !processDirectly && $submit !== null) {
				$submit.show();
			}
		};

	var onCancelAll = onError;

	var onUploadError = () =>
		(file, error, response) => {
			onError();

			var msg = texts.tus_error;

			if (error.toString().indexOf('response code: 401') !== -1) {
				msg = texts.tus_unauthorized;
			}

			if (error.toString().indexOf('Forbidden file type') !== -1) {
				msg = texts.tus_forbidden_file_type;
			}
			$dragDrop.find('.uppy-StatusBar-details').attr('aria-label', msg);
		}

	var onUploadStart = () =>
		(data) => {
			if (!isRequired && !processDirectly && $submit !== null) {
				// Disable submit input until file(s) is/are upgraded
				$submit.hide();
			}
		}

	var onUploadSuccess = () =>
		(file, response) => {
			uploadedCount++;
			// Hide drag and drop zone if all maximum number of uploaded files is reached
			if (uploadedCount >= maxNumberOfFiles) {
				$dragDrop.find(".uppy-DragDrop-container").hide();
			}
			// console.log(file);
			var fileArr = {
				"name": file.name,
				"extension": file.extension,
				"meta": file.meta,
				"id": file.id,
				"size": file.size,
				"type": file.type,
			};

			// Add file info to the text input
			var inputValue = JSON.parse($input.val() || '[]');
			inputValue.push(fileArr);
			$input.val(JSON.stringify(inputValue));

			if (processDirectly) {
				// Send AJAX request with file info
				$dragDrop.append('<div class="mw_tus_result"><div class="miocms_loading"></div>' + texts.processing + '...</div>');

				jQuery.post(ajaxurl, {
					"action": "tus_file_uploaded",
					"input_id": inputId,
					"file": fileArr
				}, function (content) {
//					$el.find(".miocms_loading").remove();
					$dragDrop.find(".mw_tus_result").html(content);
				})
			} else if ($submit !== null) {
				$submit.show();

				// Submit form
				if (!autoProceed) {
					$submit.trigger('click');
				}
			}
		}
	const uppy = Uppy.Core({
		// debug: true,
		autoProceed: autoProceed,
		restrictions: {
			maxFileSize: 1073741824, // 1 GiB
			maxNumberOfFiles: maxNumberOfFiles,
			minNumberOfFiles: 1,
			allowedFileTypes: allowedFileTypes
		},
		locale: Uppy.locales[locale]
	})
		.use(Uppy.DragDrop, {
			target: "#" + dragDropId + " .drag-drop",
		})
		.use(Uppy.Tus, {
			endpoint: template_directory_uri + "/library/Tus/server.php",
			headers: {
				'tus-auth-token': token,
			},
			resume: true,
			autoRetry: true,
			retryDelays: [0, 1000, 3000, 5000],
			chunkSize: 10485760, // 10 MiB
			onBeforeRequest: function (req) {
				var xhr = req.getUnderlyingObject()
				var url = req._url || null;
				if (url) {
					var n = url.lastIndexOf('server.php/');
					if (n !== -1) {
						var key = url.substring(n + 11);

						// Modify URL to send upload key in query parameters instead of as URL path
						var modifiedUrl = url.substring(0, n + 10) + '?key=' + key;
						// req._url = modifiedUrl;
						// xhr._url = modifiedUrl;
						this.uploadUrl = modifiedUrl;
						xhr.open(req._method, modifiedUrl);

						// Re-add request headers
						if (xhr.headers) {
							for (var headerName in xhr.headers) {
								if (xhr.headers.hasOwnProperty(headerName)) {
									xhr.setRequestHeader(headerName, xhr.headers[headerName]);
								}
							}
						}
					}
				}
			}
		})
		.use(Uppy.StatusBar, {
			target: "#" + dragDropId + " .status-bar",
			hideAfterFinish: false,
			hideUploadButton: true,
		})
		.on("upload", onUploadStart())
		.on("upload-success", onUploadSuccess())
		.on("cancel-all", onCancelAll())
		.on("upload-error", onUploadError())
		.on("error", onError());

	if ($submit !== null && !autoProceed) {
		$submit.on('click', function (e) {
			uppy.upload();
			e.preventDefault();
		});
	}
}

/** @see https://stackoverflow.com/a/10517147 */
XMLHttpRequest.prototype.wrappedSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader;

// Override the existing setRequestHeader function so that it stores the headers
XMLHttpRequest.prototype.setRequestHeader = function(header, value) {
	// Call the wrappedSetRequestHeader function first
	// so we get exceptions if we are in an erronous state etc.
	this.wrappedSetRequestHeader(header, value);

	// Create a headers map if it does not exist
	if(!this.headers) {
		this.headers = {};
	}

	// Create a list for the header that if it does not exist
	if(!this.headers[header]) {
		this.headers[header] = [];
	}

	// Add the value to the header
	this.headers[header].push(value);
}
