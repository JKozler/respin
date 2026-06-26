(function () {
	tinymce.create('tinymce.plugins.MioWebShortcodes', {
		init: function (ed, url) {
			ed.addButton('mw_addshortcode', {
				title: ed.getLang('mwshortcodes.title'),
				cmd: 'mw_addshortcode',
				image: url + '/addshortcode.png'
			});

			ed.addCommand('mw_addshortcode', function () {
				var selected_text = ed.selection.getContent({format: "text"});

				jQuery(this).mwLightbox({
					type: 'fixed',
					title: ed.getLang('mwshortcodes.title'),
					show: true,
					footer: false,
					onopen: function (lightbox) {
						jQuery.post(ajaxurl, {"action": "open_shortcode_select"}, function (content) {

							lightbox.addContent(content);

							lightbox.$lightbox.find(".open_new_shortcode_setting").click(function () {
								lightbox.$lightbox.addClass('loading');
								var eltype = jQuery(this).attr('data-type');
								jQuery.post(ajaxurl, {
									"action": "open_new_shortcode_setting",
									"type": eltype
								}, function (content) {
									lightbox.addContent(content);
									lightbox.$lightbox.find('.mw_onedit_action').mwOnEditAction({});
									lightbox.showFooter();
									lightbox.$lightbox.removeClass('loading');
								});
								return false;

							});
						});
					},
					onsave: function (lightbox) {

						//var selected_text = tinyMCE.activeEditor.selection.getContent( {format : "text"} );
						var formdata = lightbox.$lightbox.find('form').serialize();
						lightbox.close();
						jQuery.post(ajaxurl, 'action=save_shortcode_setting&text=' + selected_text + '&' + formdata, function (data) {
							tinyMCE.execCommand('mceInsertContent', 0, data);
						});

					}
				});
			});

		},
	});
	// Register plugin
	tinymce.PluginManager.add('mwshortcodes', tinymce.plugins.MioWebShortcodes);
})();
