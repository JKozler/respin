;(function (factory) {

	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports !== 'undefined') {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}

})(function ($) {

	var MwLightbox = (function (element, settings) {

		function _MwLightbox(element, settings) {

			this.defaults = {
				type: 'full', // window, full, modal, fixed
				ajax_action: '',
				title: '',
				help_link: '',
				width: null,
				onclose: null,
				onsave: null,
				onopen: null,
				beforeopen: null,
				oncreate: null,
				header: true,
				footer: true,
				form: true,
				close: false,
				class: '',
				id: 'mwlightbox',
				show: false,
				but_text: texts.save,
				storno: true,
				show_confirm: false,
				white_bg: false,
				scroll: true,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			this.$el = $(element);

			var self = this;

			if (this.$el.attr('data-show')) {
				self.open();
			}

			if (!this.$el.hasClass('mw_lightbox_added')) {
				if (this.settings.show) {
					self.open();
				} else {

					this.$el.click(function () {
						self.open();
						return false;
					});

					this.$el.addClass('mw_lightbox_added');
				}
			}

		}

		return _MwLightbox;

	})();

	// open
	MwLightbox.prototype.open = function () {
		var self = this;

		if (this.settings.beforeopen) {
			self.settings.beforeopen.call(this, self);
		}

		if (this.settings.show_confirm) {
			self.$el.mwConfirm({
				text: texts.close_confirm,
				show: true,
				but_text: texts.continue,
				onconfirm: function (confirm) {
					if (self.settings.close) self.close();
					else self.openLightbox();
				}
			});
		} else {
			self.openLightbox();
		}

	}

	MwLightbox.prototype.openLightbox = function () {
		var self = this;

		// remove existing lightbox
		if ($('#' + this.settings.id + '').length == 0) {
			this.createLightbox();
		} else {
			$('#' + this.settings.id).removeClass('mw_builder_setting_window_window');
			$('#' + this.settings.id).addClass('mw_builder_setting_window_' + this.settings.type);
			if (!this.settings.footer) $('#' + this.settings.id).addClass('mw_builder_setting_window_hide_footer');
		}


		this.$lightbox = $('#' + this.settings.id);
		this.$lightbox_overlay = $('#' + this.settings.id + '_overlay');
		this.addLoading();

		// set edited
		this.setEdited(0);

		// show hide help link
		if (this.settings.help_link)
			this.$lightbox.find('.mw_builder_setting_window_header .mw_builder_setting_window_help').attr('href', this.settings.help_link).show();
		else
			this.$lightbox.find('.mw_builder_setting_window_header .mw_builder_setting_window_help').attr('href', this.settings.help_link).hide();

		// set title
		if (!this.settings.title) this.settings.title = this.$el.attr('title');
		this.$lightbox.find('.mw_builder_setting_window_header span').html(this.settings.title);

		// bind actions
		this.unbindActions();
		this.bindActions();

		if (this.settings.onopen) {
			self.settings.onopen.call(this, self);
		}

		setTimeout(function () {
			self.$lightbox.addClass('mwb-show');
		}, 10);

	}

	MwLightbox.prototype.createLightbox = function () {

		if (this.settings.type != 'window' && this.settings.type != 'full')
			$('<div id="' + this.settings.id + '_overlay" class="mw_builder_lightbox_overlay"></div>').appendTo($('body'));

		var content;
		var lightbox_class = this.settings.class;

		if (!this.settings.footer) lightbox_class += ' mw_builder_setting_window_hide_footer';
		if (!this.settings.header) lightbox_class += ' mw_builder_setting_window_hide_header';
		if (!this.settings.scroll) lightbox_class += ' mw_builder_setting_window_noscroll';
		if (this.settings.white_bg) lightbox_class += ' mw_builder_setting_window_wbg';


		content = '<div id="' + this.settings.id + '" class="mw_builder_setting_window mw_builder_setting_window_' + this.settings.type + ' mwb_fade_animation loading ' + lightbox_class + '">';
		if(this.settings.form)
		{
			content += '<form id="mw_builder_setting_window_form" method="post" enctype="multipart/form-data" action="">';
		}
		content += '<div class="mw_builder_setting_window_header">';
		content += '<span></span>'
		content += '<a href="" target="_blank" class="mw_builder_setting_window_help" title="' + texts.lightbox_help_link_title + '">?</a>';
		content += '<a href="#" class="mw_builder_setting_window_close"><svg role="img" title="Zavřít"><use xlink:href="' + texts.loading_icons_url + 'symbol-defs.svg#icon-x"></use></svg></a>';
		content += '</div>';

		content += '<div class="mw_builder_setting_window_body">'
			+ '<div class="mw_builder_setting_window_content"></div>'
			+ '</div>'
			+ '<div class="mw_builder_setting_window_loading"><svg role="img"><use xlink:href="' + texts.loading_icons_url + 'loading.svg#icon-loading"></use></svg></div>';

		content += '<div class="mw_builder_setting_window_footer">';
		content += '<a href="#" class="mw_save_builder_setting_window mw_button" type="submit">' + this.settings.but_text + '</a>';
		if (this.settings.storno) content += '<a href="#" class="mw_builder_setting_window_close mw_button mw_button_style_secondary_gray">' + texts.storno + '</a>';
		content += '</div>';

		if(this.settings.form)
		{
		content += '</form>';
		}
		content += '</div>';

		$(content).appendTo($('body'));

	}

	// bind actions
	MwLightbox.prototype.bindActions = function () {
		var self = this;


		$('.mw_builder_close_save_lightbox, .mw_editor_panel_tabs').on('click.mw_lightbox_action', function () {
			self.close();
			return false;
		});
		this.$lightbox.find('.mw_builder_setting_window_close').on('click.mw_lightbox_action', function () {
			self.close();
			return false;
		});
		this.$lightbox.find('.mw_save_builder_setting_window').on('click.mw_lightbox_action', function () {
			self.save();
			return false;
		});

	}
	MwLightbox.prototype.unbindActions = function () {
		var self = this;

		$('.mw_builder_close_save_lightbox, .mw_editor_panel_tabs').off('click.mw_lightbox_action');
		this.$lightbox.find('.mw_builder_setting_window_close').off('click.mw_lightbox_action');
		this.$lightbox.find('.mw_save_builder_setting_window').off('click.mw_lightbox_action');

	}

	// close
	MwLightbox.prototype.close = function () {
		var self = this;
		var execute = true;

		if (this.isEdited()) {
			this.$lightbox.find('.mw_builder_setting_window_close').mwConfirm({
				text: texts.close_confirm,
				show: true,
				but_text: texts.close,
				onconfirm: function (confirm) {
					self.executeClose();
				}
			});
		} else {
			self.executeClose();
		}

	}
	MwLightbox.prototype.executeClose = function () {
		var self = this;

		this.unbindActions();

		this.$lightbox.removeClass('mwb-show');
		this.$lightbox_overlay.remove();


		if (self.settings.onclose) {
			self.settings.onclose.call();
		}

		setTimeout(function () {
			self.$lightbox.remove();

		}, 300);

	}

	// save
	MwLightbox.prototype.addContent = function (content) {

		this.$lightbox.find('.mw_builder_setting_window_content').html(content);
		this.removeLoading();

	}

	MwLightbox.prototype.save = function () {
		var self = this;

		this.addLoading();
		this.setEdited(0);

		if (self.settings.onsave) {
			self.settings.onsave.call(this, self);
		}

	}

	MwLightbox.prototype.checkForm = function () {

		var ret = mwCheckForm(this.$lightbox);
		if (!ret) {
			this.removeLoading();
		}

		return ret;

	}

	MwLightbox.prototype.sendForm = function () {
		this.$lightbox.find('form').submit()
	}
	MwLightbox.prototype.getFormData = function () {
		return this.$lightbox.find('form').serialize()
	}

	MwLightbox.prototype.addLoading = function () {
		this.$lightbox.addClass('loading');
	}
	MwLightbox.prototype.removeLoading = function () {
		this.$lightbox.removeClass('loading');
	}
	MwLightbox.prototype.hideHeader = function () {
		this.$lightbox.addClass('mw_builder_setting_window_hide_header');
	}
	MwLightbox.prototype.showHeader = function () {
		this.$lightbox.removeClass('mw_builder_setting_window_hide_header');
	}
	MwLightbox.prototype.hideFooter = function () {
		this.$lightbox.addClass('mw_builder_setting_window_hide_footer');
	}
	MwLightbox.prototype.showFooter = function () {
		this.$lightbox.removeClass('mw_builder_setting_window_hide_footer');
	}
	MwLightbox.prototype.setEdited = function (set = 1) {
		this.$lightbox.attr('data-edited', set);
	}
	MwLightbox.prototype.isEdited = function (set = 1) {
		val = this.$lightbox.attr('data-edited');
		if (val == '1') return true;
		else return false;
	}
	MwLightbox.prototype.changeType = function (type) {
		this.$lightbox.removeClass('mw_builder_setting_window_' + this.settings.type);
		this.$lightbox.addClass('mw_builder_setting_window_' + type);
	}
	MwLightbox.prototype.changeButtonText = function (text) {
		this.$lightbox.find('.mw_save_builder_setting_window').html(text);
	}

	$.fn.mwLightbox = function (options) {
		return this.each(function (index, el) {
			el.MwLightbox = new MwLightbox(el, options);
		});
	};



	var MwModal = (function (element, settings) {

		function _MwModal(element, settings) {

			this.defaults = {
				id: 'mw_modal',
				class: '',
				text: '',
				title: '',
				onsave: null,
				onopen: null,
				form: true,
				but_text: texts.save,
				storno: true,
			};
			this.settings = $.extend({}, this, this.defaults, settings);
			this.saveDisabled = false;
			this.$el = $(element);

			this.open();

		}

		return _MwModal;

	})();

	MwModal.prototype.open = function () {
		var self = this;

		this.createModal();

		if(!this.settings.text)
			this.addLoading();

		// bind actions
		this.$modal.on('click', '.mw_modal_close', function () {
			self.close();
			return false;
		});

		this.$modal.on('click', '.mw_modal_save', function () {
			self.save();
			return false;
		});

		if (this.settings.onopen) {
			self.settings.onopen.call(this, self);
		}

		setTimeout(function () {
			self.$modal.removeClass('mw-hidden').outerWidth();
			self.$modal.addClass('mw-show');
		}, 10);

	}

	MwModal.prototype.createModal = function () {

		var content;
		var modal_content = '';

		if (!this.settings.title && this.$el.attr('data-title') !== undefined)
		{
			this.settings.title = this.$el.attr('data-title');
		}

		if(this.settings.text)
		{
			this.settings.storno = false;
			this.settings.but_text = texts.close;
			modal_content = this.settings.text;
			this.settings.title = '';
			this.settings.class += ' mw_modal_type_text'
		}
		else if (!this.settings.title)
		{
			this.settings.class += ' mw_modal_notitle'
		}

		content = '<div id="'+this.settings.id+'" class="mw_modal_container mw-hidden mw_modal_close '+this.settings.class+'">';
		content += '<div class="mw_modal mw_rounded mwb_fade_animation">';

		content += '<div class="mw_modal_head">';
		content += '<div class="mw_modal_title"><span>' + this.settings.title + '</span></div>';
		content += '<a href="#" class="mw_close_icon mw_modal_close"><svg role="img"><svg role="img" title="Zavřít"><use xlink:href="' + texts.loading_icons_url + 'symbol-defs.svg#icon-x"></use></svg></a>';
		content += '</div>';

		content += '<div class="mw_modal_content_container">';
		if(this.settings.form)
		{
			content += '<form id="mw_modal_form" method="post" enctype="multipart/form-data" action="">';
		}

		content += '<div class="mw_modal_message_box"></div>';
		content += '<div class="mw_modal_content mw_admin_setting_container">' + modal_content + '</div>';

		if(this.settings.form)
		{
			content += '</form>';
		}
		content += '</div>';

		/* footer */
		content += '<div class="mw_modal_footer">';
		content += '<a href="#" class="mw_modal_save mw_button">' + this.settings.but_text + '</a>';
		if (this.settings.storno) content += '<a href="#" class="mw_modal_close mw_button mw_button_style_secondary_gray">' + texts.storno + '</a>';
		content += '</div>';

		content += '</div>';
		content += '</div>';

		$(content).appendTo($('body'));

		this.$modal=$('#' + this.settings.id);

	}

	// close
	MwModal.prototype.close = function () {

		var self = this;

		this.$modal.removeClass('mw-show');

		setTimeout(function () {
			self.$modal.remove();
		}, 300);

	}

	// save
	MwModal.prototype.addContent = function (content) {

		this.$modal.find('.mw_modal_content').html(content);
		this.removeLoading();

	}

	MwModal.prototype.save = function () {
		var self = this;
		if(!this.saveDisabled) {
			this.addLoading();

			if (self.settings.onsave) {
				self.settings.onsave.call(this, self);
			} else {
				self.close();
			}
		}
	}

	MwModal.prototype.checkForm = function () {

		var ret = mwCheckForm(this.$modal);
		if (!ret) {
			this.removeLoading();
		}

		return ret;

	}

	MwModal.prototype.addMessage = function (data) {
		this.$modal.find('.mw_modal_message_box').show().html(data.html);
		this.$modal.find('.mw_modal_content_container').stop().animate({
			'scrollTop': 0
		}, 500, 'swing');
	}

	MwModal.prototype.getFormData = function () {
		return this.$modal.find('form').serialize()
	}

	MwModal.prototype.addLoading = function () {
		this.$modal.find('.mw_modal_content_container').addClass('mw_loading_w');
		this.saveDisabled = true;
	}
	MwModal.prototype.removeLoading = function () {
		this.$modal.find('.mw_modal_content_container').removeClass('mw_loading_w');
		this.saveDisabled = false;
	}

	$.fn.mwModal = function (options) {
		return this.each(function (index, el) {
			el.MwModal = new MwModal(el, options);
		});
	};


	var MwConfirm = (function (element, settings) {

		function _MwConfirm(element, settings) {

			this.defaults = {
				text: '',
				but_text: texts.delete,
				show: false,
				confirm_text: '',
				onconfirm: null,
			};
			this.settings = $.extend({}, this, this.defaults, settings);

			this.$el = $(element);

			var self = this;

			if (!this.$el.hasClass('mw_confirm_link')) {
				if (this.settings.show) {
					self.open();
				} else {

					this.$el.click(function () {
						self.open();
						return false;
					});

					this.$el.addClass('mw_confirm_link');
				}
			}
		}

		return _MwConfirm;

	})();

	// open
	MwConfirm.prototype.open = function () {

		var self = this;

		$('<div class="mw_confirm_modal_overlay"></div>').appendTo($('body'));

		content = '<div class="mw_confirm_modal mwb_fade_animation">'
			+ '<div class="mw_confirm_modal_body"></div>'
			+ '<div class="mw_confirm_modal_footer">'
			+ '<a href="#" class="mw_storno_modal_button">' + texts.storno + '</a>'
			+ '<a href="#" class="mw_confirm_modal_button">' + this.settings.but_text + '</a>'
			+ '</div>'
			+ '</div>';

		$(content).appendTo($('body'));

		this.$confirm_modal = $('.mw_confirm_modal');
		this.$confirm_modal_overlay = $('.mw_confirm_modal_overlay');

		// set title
		if (!this.settings.text) this.settings.text = this.$el.attr('data-text');
		this.$confirm_modal.find('.mw_confirm_modal_body').html(this.settings.text);

		this.$confirm_modal.find('.mw_storno_modal_button').click(function () {
			self.close();
			return false;
		});


		this.$confirm_modal.find('.mw_confirm_modal_button').click(function (e) {

			if (self.settings.onconfirm) {
				self.settings.onconfirm.call(this, self);
			}
			self.close();

			e.preventDefault();

		});


		setTimeout(function () {
			self.$confirm_modal.find('.mw_confirm_modal_button').focus();
		}, 150);

		setTimeout(function () {
			self.$confirm_modal.addClass('mwb-show');
		}, 10);


	}

	// close
	MwConfirm.prototype.close = function () {

		var self = this;

		this.$confirm_modal_overlay.remove();
		this.$confirm_modal.removeClass('mwb-show');
		setTimeout(function () {
			self.$confirm_modal.remove();
		}, 300);

	}

	$.fn.mwConfirm = function (options) {
		return this.each(function (index, el) {
			el.MwConfirm = new MwConfirm(el, options);
		});
	};

});
