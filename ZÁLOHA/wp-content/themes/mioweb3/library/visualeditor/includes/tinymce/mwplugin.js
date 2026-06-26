tinymce.PluginManager.add('mioweb', function (editor, url) {
	var mwAdvButton,
		each = tinymce.each;

	// Add a button that opens a window
	editor.addButton('mw_adv', {
		text: '',
		image: texts.weburl + '/library/visualeditor/includes/tinymce/togglepanel.png',
		icon: false,
		onclick: function () {
			toggleToolbars();
		}
	});


	function toggleToolbars(state) {
		var initial,
			toolbars,
			panel_id = '#' + editor.theme.panel._id,
			position;

		if (state === 'hide') {
			initial = true;
		}

		if (editor.theme.panel) {
			toolbars = editor.theme.panel.find('.toolbar:not(.menubar)');

			if (toolbars && toolbars.length > 1) {

				if (!state && toolbars[1].visible()) {
					state = 'hide';
				}

				toolbar = toolbars[1];

				position = jQuery('#' + editor.id).offset();

				if (state === 'hide') {
					//if(toolbar.visible()) {
					//tinymce.DOM.setStyle( tinymce.DOM.select(panel_id), 'top', parseInt(tinymce.DOM.getStyle(tinymce.DOM.select(panel_id),'top'))+30 );
					//}

					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id), 'top', position.top - 34);

					toolbar.hide();
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id), 'height', 32);
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id + ' .mce-container-body.mce-stack-layout'), 'height', 32);
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id + ' .mce-container-body.mce-abs-layout'), 'height', 32);

				} else {
					//if(!toolbar.visible()) {
					//tinymce.DOM.setStyle( tinymce.DOM.select(panel_id), 'top', parseInt(tinymce.DOM.getStyle(tinymce.DOM.select(panel_id),'top'))-30 );
					//}
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id), 'top', position.top - 64);

					toolbar.show();
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id), 'height', 62);
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id + ' .mce-container-body.mce-stack-layout'), 'height', 62);
					tinymce.DOM.setStyle(tinymce.DOM.select(panel_id + ' .mce-container-body.mce-abs-layout'), 'height', 62);
				}

			}

		}

		if (!initial) {
			if (state === 'hide') {
				setUserSetting('hidetb', '0');
			} else {
				setUserSetting('hidetb', '1');
			}
		}

	}

	return {
		getMetadata: function () {
			return {
				name: "MioWeb plugin",
				url: "https://mioweb.cz"
			};
		}
	};
});
