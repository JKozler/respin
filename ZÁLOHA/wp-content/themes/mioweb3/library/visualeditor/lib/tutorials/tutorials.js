jQuery(function ($) {


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

	var MwIntro = (function (element, settings, chapters, start_step) {

		function _MwIntro(element, settings, chapters, start_step) {

			this.defaults = {
				title: 'default',
				start_modal: false,
				end_modal: false,
				start: 0,
				temp: '',
			};


			this.settings = $.extend({}, this, this.defaults, settings);
			this.chapters = chapters;
			this.chapter = start_step;
			this.current_chapter = this.chapters[this.chapter];

			intro = this;

			if (start_step > 0) this.settings.start_modal = false;

			if (this.settings.start_modal) {
				this.openModal('start');
			}

			$('#mw_page_builder').load(function () {
				intro.$iframe = $('#mw_page_builder').contents();
				intro.addOverlays();
				intro.addStepNums();

				if (!intro.settings.start_modal)
					intro.setChapter();
			});

			$('body').on('click', '.mw_intro_skip', function () {

				var time = intro.getTime();
				$.post(ajaxurl, {"action": "mw_end_tutorial", 'time': time, 'step': this.chapter}, function (content) {
					window.location.reload();
				});

				return false;

			});

			$("input.mw_elements_search").prop('disabled', true);

		}

		return _MwIntro;

	})();

	MwIntro.prototype.setChapter = function () {

		this.steps = this.current_chapter.steps;
		this.step = 0;
		this.current_step = this.steps[this.step];

		this.changeStepNum();
		this.showInfo(this.current_chapter.title, this.current_chapter.text);
		intro.createStep();

	}

	MwIntro.prototype.nextChapter = function () {

		this.chapter = this.chapter + 1;
		if (this.chapters[this.chapter] !== undefined) {
			this.current_chapter = this.chapters[this.chapter];
			this.setChapter();
		} else {
			intro.end();
		}

	}

	MwIntro.prototype.end = function () {

		var time = this.getTime();

		this.removeIntro();
		this.openModal('end', time);

		$.post(ajaxurl, {"action": "mw_end_tutorial", 'time': time, 'step': this.chapter}, function (content) {
		});

	}

	MwIntro.prototype.getTime = function () {
		var end = new Date();
		return (end.getTime() - intro.settings.start) / 1000;
	}
	MwIntro.prototype.removeIntro = function () {
		this.hideStep();
		this.hideInfo();
		this.removeOverlays();
	}

	MwIntro.prototype.openModal = function (type, time = 0) {

		this.clearModalContent();
		this.showModal();

		var intro = this;

		$.post(ajaxurl, {
			"action": "mw_get_modal_content",
			"tutorial": this.settings.title,
			"type": type
		}, function (content) {
			intro.setModalContent(content);
			$('.mw_intro_modal').addClass('mw_intro_modal_' + intro.settings.title + '_' + type);
			if (type == 'end') {

				$('.mw_intro_modal').addClass('mw_intro_modal_a_' + intro.settings.temp);

				var m = Math.floor(time / 60);
				var s = Math.round(time - m * 60);

				var text = s + 's';
				if (m > 0) text = m + 'm ' + text;

				$('.mw_intro_time_result span').html(text);
			}
		});

	}

	MwIntro.prototype.showModal = function () {
		$('.mw_intro_modal_container').show();
	}
	MwIntro.prototype.closeModal = function () {
		$('.mw_intro_modal_container').hide();
	}
	MwIntro.prototype.clearModalContent = function () {
		$('.mw_intro_modal').html('');
		$('.mw_intro_modal_container').addClass('loading');
		$('.mw_intro_modal').removeClass('mw_intro_modal_' + intro.settings.title + '_start');
		$('.mw_intro_modal').removeClass('mw_intro_modal_a_mio mw_intro_modal_a_mia');
	}
	MwIntro.prototype.setModalContent = function (content) {
		var intro = this;
		$('.mw_intro_modal_container').removeClass('loading');
		$('.mw_intro_modal').html(content);
		$('.mw_intro_modal_close').click(function () {
			intro.closeModal();
		});
		$('.mw_intro_modal_end').click(function () {
			intro.clearModalContent();
			intro.$iframe.find('body').attr('data-modified', '0');
			window.location.reload();
		});
		$('.mw_intro_modal_continue').click(function () {
			var time = new Date();
			intro.settings.start = time.getTime();
			intro.setChapter();
		});
		$('.mw_intro_skip').click(function () {
			intro.clearModalContent();
			$.post(ajaxurl, {"action": "mw_end_tutorial", 'time': 0, 'step': 0}, function (content) {
				window.location.reload();
			});

			return false;
		});
	}

	MwIntro.prototype.showCheers = function (cheer_text) {

		var step = 'mw_intro_cheer_' + this.step;

		$('.mw_intro_cheer').append('<div class="pyro"><div class="before"></div><div class="after"></div></div>');
		$('.mw_intro_cheer').addClass('mw_intro_cheer_show ' + step);
		$('.mw_intro_cheer span').html(cheer_text);

		setTimeout(function () {
			$('.' + step).removeClass('mw_intro_cheer_show');
			$('.' + step + ' .pyro').remove();
		}, 4000);
	}

	MwIntro.prototype.createStep = function () {
		var intro = this;

		intro.hideStep();

		setTimeout(function () {

			intro.showDelayedStep();

		}, 200);

	}
	MwIntro.prototype.showDelayedStep = function () {
		var intro = this;
		var target = intro.getElement(intro.current_step.element);
		if (target.length) {
			intro.showStep();
		} else {
			setTimeout(function () {
				intro.showDelayedStep();
			}, 200);
		}
	}

	MwIntro.prototype.showStep = function () {

		var intro = this;

		$('.mw_intro_hide').removeClass('mw_intro_hide');
		if (this.$iframe !== undefined)
			this.$iframe.find('.mw_intro_hide').removeClass('mw_intro_hide');

		if (intro.current_step.hide !== undefined) {
			$(intro.current_step.hide).addClass('mw_intro_hide');
			if (intro.$iframe !== undefined) {
				intro.$iframe.find(intro.current_step.hide).addClass('mw_intro_hide');
			}
		}

		if (intro.current_step.scrollto !== undefined)
			intro.scrollTo(intro.$iframe.find(intro.current_step.scrollto), true);

		this.$target = intro.getElement(intro.current_step.element);

		this.$target.addClass('mw_current_hint_container');
		this.$target.append('<div class="mw_intro_hint mw_intro_hint_' + intro.current_step.align + '"><span>' + intro.current_step.hint_text + '</span></div>');

		$('body').removeClass('mw_intro_in_builder');
		if (this.current_step.target !== 'iframe')
			$('body').addClass('mw_intro_in_builder');

		setTimeout(function () {
			intro.$current_hint = intro.getElement('.mw_intro_hint');
			if (intro.current_step.top !== undefined)
				intro.$current_hint.find('span').css("top", intro.current_step.top)
			intro.$current_hint.addClass('mw_intro_hint_show');
		}, 100);

		intro.$action_el = intro.getElement(intro.current_step.action.target);
		if (intro.current_step.action.event == 'rewrite') {

			intro.$action_el.on('keyup.mw_intro_action', function () {
				if (intro.$action_el.text() == intro.current_step.action.text)
					intro.nextStep();
			});

		} else if (intro.current_step.action.event == 'drag') {

			intro.$action_el.on('dragstart.mw_intro_action', function () {

				intro.$iframe.find('body').addClass('mw_is_dragged_tutorial');

				var drop_area = intro.$iframe.find(intro.current_step.action.drop);
				drop_area.addClass('mw_tutorial_droparea_target');
				setTimeout(function () {
					intro.scrollTo(drop_area);
				}, 100);
				drop_area.off('.mw_intro_action');

				drop_area.append('<div class="mw_intro_hint mw_intro_hint_bottom-left"><span>' + intro.current_step.action.drop_text + '</span></div>');
				var help_hint = drop_area.find('.mw_intro_hint');
				intro.$current_hint.removeClass('mw_intro_hint_show');
				setTimeout(function () {
					help_hint.addClass('mw_intro_hint_show');
				}, 100);

				drop_area.on('drop.mw_intro_action', function (event) {
					intro.nextStep();
					drop_area.removeClass('mw_tutorial_droparea_target');
					intro.$iframe.find('body').removeClass('mw_is_dragged_tutorial');
				});

				$(document).on('mouseup.mw_intro_action', function (event) {

					help_hint.removeClass('mw_intro_hint_show');

					setTimeout(function () {
						help_hint.remove();
						intro.$current_hint.addClass('mw_intro_hint_show');
					}, 200);
				});

			});

		} else if (intro.current_step.action.event == 'open_media') {

			intro.$action_el.on('click.mw_intro_action', function () {

				setTimeout(function () {
					$('.media-modal').append('<div class="mw_intro_overlay"></div>');

					$('.media-router a:last-child').click();
				}, 200);
				intro.nextStep();

			});

		} else {

			intro.$action_el.on(intro.current_step.action.event + '.mw_intro_action', function () {

				if (intro.current_step.action.do !== undefined) {
					intro.doAction(intro.current_step.action.do, $(this));
				} else {
					intro.nextStep();
				}

			});

		}
		if (intro.current_step.back_action !== undefined) {
			intro.$back_action_el = intro.getElement(intro.current_step.back_action.target);
			intro.$back_action_el.on(intro.current_step.back_action.event + '.mw_intro_back_action', function () {
				intro.nextStep(-1);
			});
		}
	}

	MwIntro.prototype.hideStep = function () {

		var intro = this;
		if (intro.$current_hint !== undefined) {
			this.$target.removeClass('mw_current_hint_container');
			intro.$current_hint.removeClass('mw_intro_hint_show');
			intro.$action_el.off('.mw_intro_action');
			if (intro.$back_action_el !== undefined)
				intro.$back_action_el.off('.mw_intro_back_action');

			setTimeout(function () {
				intro.$current_hint.remove();
			}, 200);
		}
	}

	MwIntro.prototype.nextStep = function (step = 1) {

		if (step === 1 && intro.current_step.cheer !== undefined) {
			intro.showCheers(intro.current_step.cheer);
		}

		this.step = this.step + step;
		if (this.steps[this.step] !== undefined) {
			this.current_step = this.steps[this.step];
			this.createStep();
		} else {
			intro.nextChapter();
			//alert('konec');
		}

	}

	MwIntro.prototype.getElement = function (el_id) {
		if (this.current_step.target == 'iframe')
			return this.$iframe.find(el_id);
		else
			return $(el_id);
	}

	MwIntro.prototype.addStepNums = function () {
		var i = 1;
		var steps = '';
		for (i = 1; i <= this.chapters.length; i++) {
			steps += '<div class="mw_intro_step"><span>' + i + '</span></div>';
		}
		$('.mw_intro_info_steps').html(steps);
		$('.mw_intro_step:eq(0)').addClass('mw_intro_step_active');
	}
	MwIntro.prototype.changeStepNum = function () {
		$('.mw_intro_step').removeClass('mw_intro_step_active');
		$('.mw_intro_step:eq(' + this.chapter + ')').addClass('mw_intro_step_active');
	}

	MwIntro.prototype.addOverlays = function () {

		$('#ve_editor_panel').append('<div class="mw_intro_overlay"></div>');
		$('.ve_editor_top_panel').append('<div class="mw_intro_overlay"></div>');
		this.$iframe.find('body').append('<div class="mw_intro_overlay"></div>');

		var intro = this;

		$('body').on('click', '.mw_intro_overlay', function () {
			intro.hintBounce();
			return false;
		});


		this.$iframe.find('.mw_intro_overlay').click(function (event) {
			event.stopPropagation();

			if (intro.current_step.target == 'iframe') {

				intro.scrollTo(intro.$target, true);

			} else {
				intro.hintBounce();
			}
		});

	}
	MwIntro.prototype.scrollTo = function (el, bounce = false) {

		var elementTop = el.offset().top;
		var elementBottom = elementTop + el.outerHeight();
		var viewportTop = intro.$iframe.scrollTop();
		var viewportBottom = viewportTop + $('#mw_page_builder').height();

		if (elementBottom > viewportTop && elementTop < viewportBottom) {
			if (bounce) intro.hintBounce();
		} else {
			intro.$iframe.find('html,body').animate({
				scrollTop: (elementTop - 80)
			}, 500);
			if (bounce) {
				setTimeout(function () {
					intro.hintBounce();
				}, 500);
			}

		}

	}
	MwIntro.prototype.removeOverlays = function () {

		$('.mw_intro_overlay').remove();
		this.$iframe.find('.mw_intro_overlay').remove();

	}

	MwIntro.prototype.hintBounce = function () {
		var intro = this;
		intro.$current_hint.addClass('bounce-animation');
		setTimeout(function () {
			intro.$current_hint.removeClass('bounce-animation');
		}, 1000);
	}

	MwIntro.prototype.showInfo = function (title, text) {
		$('.mw_intro_info_container').addClass('mw_intro_info_container_show');
		$('.mw_intro_info_inn').fadeOut(300, function () {
			$('.mw_intro_info_title').html(title);
			$('.mw_intro_info_text').html(text);
			$('.mw_intro_info_inn').fadeIn(300);
		});


	}

	MwIntro.prototype.hideInfo = function () {
		$('.mw_intro_info_container').removeClass('mw_intro_info_container_show');
	}

	MwIntro.prototype.doAction = function (action, el) {
		var intro = this;
		if (action == 'select_template') {
			$('.mw_builder_setting_window').addClass('loading');
			var temp_id = el.attr('data-template');
			var time = this.getTime();
			intro.hideStep();
			intro.hideInfo();
			intro.showCheers(intro.current_step.cheer);

			$.post(ajaxurl, {
				"action": "mw_install_tut_web_template",
				"temp_id": temp_id,
				"step": intro.chapter,
				"time": time,
				"start": intro.settings.start
			}, function (data) {

			});

			setTimeout(function () {
				window.location.reload();
			}, 4000);

		}
	}

	$.fn.mwIntro = function (options, steps, start) {
		return this.each(function (index, el) {
			el.MwIntro = new MwIntro(el, options, steps, start);
		});
	};

});
