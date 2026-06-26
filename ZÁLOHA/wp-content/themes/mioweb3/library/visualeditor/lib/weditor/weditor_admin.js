var _custom_media = true;
;(function (factory) {

	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports !== 'undefined') {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}

})(function ($) {

	var MwWeditorManager = (function (element, settings) {

		function _MwWeditorManager(element, settings) {

			this.$el = $(element);

			this.defaults = {
				change: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			var obj = this;

			// select window post
			this.$el.find(".ve_windowselect_selector").change(function () {

				if ($(this).val() == "") obj.$el.removeClass('selected');
				else obj.$el.addClass('selected');

				// on change
				if (obj.settings.change) {
					obj.settings.change.call();
				}
			});

			// add window post

			this.$el.find(".add_new_object_item").click(function () {

				$(this).mwOpenInstallator({
					install: obj.$el.attr('data-install'),
					objectid: obj.$el.attr('data-type'),
					oninstall: function (lightbox, data) {

						lightbox.addContent('<iframe id="mw_weditor_iframe" src="' + data.redirect + '" width="100%" height="100%"></iframe>');
						lightbox.$lightbox.find('#mw_weditor_iframe').load(function () {
							var iframe = lightbox.$lightbox.find('#mw_weditor_iframe').contents();
							iframe.find('.mw_close_weditor').click(function (e) {

								var name = iframe.find('#weditor_post_title').val();

								if (iframe.find('.mw_save_page').hasClass('is_edited')) {

									$(this).mwConfirm({
										text: $(this).attr('data-confirm'),
										show: true,
										but_text: texts.close,
										onconfirm: function (confirm) {
											lightbox.close();
										}
									});

								} else {
									lightbox.close();
								}

								obj.$el.find(".ve_windowselect_selector option:selected").text(name).attr('data-title', name);

								// on change
								if (obj.settings.change) {
									obj.settings.change.call();
								}

								e.preventDefault();
							});
						});

						lightbox.removeLoading();

						lightbox.changeType('full');

						obj.$el.find('.ve_windowselect_selector').append('<option data-title="' + data.title + '" value="' + data.id + '">' + data.title + '</option>');
						obj.$el.find('.ve_windowselect_selector').val(data.id);
						obj.$el.addClass('selected');

						// update chosen
						obj.chosenUpdated();

					},

				});

				return false;
			});

			this.$el.find(".add_new_window_editor").mwLightbox({
				type: 'fixed',
				id: 'mw_lightbox_weditor',
				onopen: function (lightbox) {

					var type = obj.$el.attr('data-type');
					var themes = obj.$el.attr('data-themes');

					$.post(ajaxurl, {
						"action": "ve_create_window_post_form",
						'post_type': type,
						'theme_file': themes
					}, function (data) {
						lightbox.addContent(data);
						$('.mw_page_template_selector').mwTemplateSelector();
					});

				},
				onclose: function (lightbox) {

					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				},
				onsave: function (lightbox) {
					lightbox.addLoading();
					if (lightbox.checkForm()) {

						var form = lightbox.$lightbox.find('form').serialize();

						$.post(ajaxurl, 'action=ve_create_window_post&' + form, function (data) {

							lightbox.addContent('<iframe id="mw_weditor_iframe" src="' + data.url + '" width="100%" height="100%"></iframe>');
							lightbox.$lightbox.find('#mw_weditor_iframe').load(function () {
								var iframe = lightbox.$lightbox.find('#mw_weditor_iframe').contents();
								iframe.find('.mw_close_weditor').click(function (e) {

									var name = iframe.find('#weditor_post_title').val();

									if (iframe.find('.mw_save_page').hasClass('is_edited')) {

										$(this).mwConfirm({
											text: $(this).attr('data-confirm'),
											show: true,
											but_text: texts.close,
											onconfirm: function (confirm) {
												lightbox.close();
											}
										});

									} else {
										lightbox.close();
									}

									obj.$el.find(".ve_windowselect_selector option:selected").text(name).attr('data-title', name);

									e.preventDefault();
								});
							});

							lightbox.hideFooter();
							lightbox.hideHeader();
							lightbox.removeLoading();

							lightbox.changeType('full');

							obj.$el.find('.ve_windowselect_selector').append('<option data-title="' + data.title + '" value="' + data.id + '">' + data.title + '</option>');
							obj.$el.find('.ve_windowselect_selector').val(data.id);
							obj.$el.addClass('selected');

							// update chosen
							obj.chosenUpdated();

						});
					} else {
						lightbox.removeLoading();
						lightbox.$lightbox.find('.mw_builder_setting_window_body').animate({
							scrollTop: 0
						}, 200);
					}

					return false;

				}
			});
			this.$el.find(".open_window_editor").mwLightbox({
				type: 'full',
				id: 'mw_lightbox_weditor',
				footer: false,
				header: false,
				onopen: function (lightbox) {

					var id = obj.$el.find(".ve_windowselect_selector").val();
					var url = obj.$el.attr('data-url');

					lightbox.addContent('<iframe id="mw_weditor_iframe" src="' + url + '&id=' + id + '" width="100%" height="100%"></iframe>');
					lightbox.$lightbox.find('#mw_weditor_iframe').load(function () {
						var iframe = lightbox.$lightbox.find('#mw_weditor_iframe').contents();
						iframe.find('.mw_close_weditor').click(function (e) {

							var name = iframe.find('#weditor_post_title').val();

							if (iframe.find('.mw_save_page').hasClass('is_edited')) {
								$(this).mwConfirm({
									text: $(this).attr('data-confirm'),
									show: true,
									but_text: texts.close,
									onconfirm: function (confirm) {
										lightbox.close();
									}
								});

							} else {
								lightbox.close();
							}

							obj.$el.find(".ve_windowselect_selector option:selected").text(name).attr('data-title', name);

							e.preventDefault();
						});
					});

				},
				onclose: function (lightbox) {
					// on change
					if (obj.settings.change) {
						obj.settings.change.call();
					}
				}
			});

			// duplicate window post

			this.$el.find(".create_copy_window_editor").click(function(){
				var objectId = obj.$el.attr('data-type');
				var itemId = obj.$el.find(".ve_windowselect_selector").val();

				var el = $(this)

				el.mwOpenFastCopyModal({
					action: 'mwSaveFastCopyObject',
					objectId: objectId,
					itemId: itemId,
					onsave: function (data, modal) {

						obj.$el.find('.ve_windowselect_selector').append('<option data-title="' + data.title + '" value="' + data.id + '">' + data.title + '</option>');
						obj.$el.find('.ve_windowselect_selector').val(data.id);
						obj.$el.addClass('selected');

						// update chosen
						obj.chosenUpdated();

						modal.$modal.mwLightbox({
							type: 'full',
							show: true,
							id: 'mw_lightbox_weditor',
							footer: false,
							header: false,
							onopen: function (lightbox) {

								lightbox.addContent('<iframe id="mw_weditor_iframe" src="' + data.admin_url + '" width="100%" height="100%"></iframe>');
								lightbox.$lightbox.find('#mw_weditor_iframe').load(function () {
									var iframe = lightbox.$lightbox.find('#mw_weditor_iframe').contents();
									iframe.find('.mw_close_weditor').click(function (e) {

										var name = iframe.find('#weditor_post_title').val();

										if (iframe.find('.mw_save_page').hasClass('is_edited')) {
											$(this).mwConfirm({
												text: $(this).attr('data-confirm'),
												show: true,
												but_text: texts.close,
												onconfirm: function (confirm) {
													lightbox.close();
												}
											});

										} else {
											lightbox.close();
										}

										obj.$el.find(".ve_windowselect_selector option:selected").text(name).attr('data-title', name);

										e.preventDefault();
									});
								});

							},
							onclose: function (lightbox) {
								// on change
								if (obj.settings.change) {
									obj.settings.change.call();
								}
							}
						});

						modal.close();
					}
				});
				return false;
			});

			// delete window post

			this.$el.find(".delete_window_editor").mwConfirm({
				onconfirm: function (confirm) {

					var item_id = obj.$el.find(".ve_windowselect_selector").val();
					var object_id = obj.$el.attr('data-type');

					$.post(ajaxurl, {
						"action": "mwDeleteObjectItem",
						"item_id": item_id,
						"object_id": object_id,
					}, function (data) {
						obj.$el.find('.ve_windowselect_selector option:selected').removeAttr('selected').prev('option').attr('selected', 'selected');
						obj.$el.find(".ve_windowselect_selector option[value=" + item_id + "]").remove();

						if (obj.$el.find('.ve_windowselect_selector').val() == '') obj.$el.removeClass('selected');

						// update chosen
						obj.chosenUpdated();

						// on change
						if (obj.settings.change) {
							obj.settings.change.call();
						}
					});

					return false;
				}
			});

			// chosen select
			obj.chosen();

		}

		return _MwWeditorManager;

	})();

	MwWeditorManager.prototype.chosen = function () {

		var obj = this;

		this.$el.find('select.ve_windowselect_selector').on('chosen:ready', function(evt, params) {
			const ps = new PerfectScrollbar(obj.$el.find('.chosen-results')[0], {
				suppressScrollX: true,
				wheelPropagation: false,
			});
		});
		this.$el.find('select.ve_windowselect_selector').chosen({width: "100%"});

	}

	MwWeditorManager.prototype.chosenUpdated = function () {
console.log('chosen updated');
		this.$el.find('select.ve_windowselect_selector').trigger('chosen:updated');

	}

	$.fn.mwWeditorManager = function (options) {
		return this.each(function (index, el) {
			el.MwWeditorManager = new MwWeditorManager(el, options);
		});
	};
});
