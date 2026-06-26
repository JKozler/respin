jQuery(function ($) {

	var pressTimer, lockTimer;

	function touchHandler(e) {
		var el = event.target;

		switch (e.type) {
			case "touchstart":
				simulateMouse("mousedown", e);
				break;
			case "touchmove":
				simulateMouse("mousemove", e);
				break;
			case "touchend":
				simulateMouse("mouseup", e);
				break;
			default:
				return;
		}
		e.preventDefault();
	}

	function touchHandlerDelay(e) {
		var el = event.target;
		switch (e.type) {
			case "touchstart":
				if (lockTimer)
					return;
				pressTimer = setTimeout(function () {
					simulateMouse("mousedown", e);
				}, 200);
				lockTimer = true;
				break;
			case "touchmove":
				simulateMouse("mousemove", e);
				break;
			case "touchend":
				clearTimeout(pressTimer);
				lockTimer = false;
				simulateMouse("mouseup", e);
				break;
			default:
				return;
		}
		e.preventDefault();
	}

	function simulateMouse(type, e) {
		var touches = e.changedTouches;
		var first = touches[0];
		var simulatedEvent = document.createEvent("MouseEvent");
		simulatedEvent.initMouseEvent(type, true, true, window, 1, first.screenX, first.screenY, first.clientX, first.clientY, false, false, false, false, 0, null);
		first.target.dispatchEvent(simulatedEvent);
	}

	function simulateTouchFor(selector, delay = false) {
		var classname = document.getElementsByClassName(selector);
		for (var i = 0; i < classname.length; i++) {
			if (delay) {
				classname[i].addEventListener("touchstart", touchHandlerDelay, {passive: false});
				classname[i].addEventListener("touchend", touchHandlerDelay, {passive: false});
			} else {
				classname[i].addEventListener("touchstart", touchHandler, {passive: false});
				classname[i].addEventListener("touchend", touchHandler, {passive: false});
			}
			classname[i].addEventListener("touchmove", touchHandler, {passive: false});
			classname[i].addEventListener("touchcancel", touchHandler, {passive: false});
		}
	}

	function simulateLiveTouches() {
		document.addEventListener('touchstart', function (e) {
			if (event.target.matches('.cms_image_gallery__item img') || event.target.matches('.ve_sortable_handler svg') || event.target.matches('.mw_image_uploader_position_drag') || event.target.matches('.ui-slider-handle') || event.target.matches('.ece_move svg') || event.target.matches('.row_move svg') || event.target.matches('.mwcb_move_item svg')) {
				touchHandler(e);
			}
		}, {passive: false});
		document.addEventListener('touchend', function (e) {
			if (event.target.matches('.cms_image_gallery__item img') || event.target.matches('.ve_sortable_handler svg') || event.target.matches('.mw_image_uploader_position_drag') || event.target.matches('.ui-slider-handle') || event.target.matches('.ece_move svg') || event.target.matches('.row_move svg') || event.target.matches('.mwcb_move_item svg')) {
				touchHandler(e);
			}
		}, false);
		document.addEventListener('touchmove', function (e) {
			if (event.target.matches('.cms_image_gallery__item img') || event.target.matches('.ve_sortable_handler svg') || event.target.matches('.mw_image_uploader_position_drag') || event.target.matches('.ui-slider-handle') || event.target.matches('.ece_move svg') || event.target.matches('.row_move svg') || event.target.matches('.mwcb_move_item svg')) {
				touchHandler(e);
			}
		}, {passive: false});
	}

	function init() {
		simulateTouchFor("mw_page_builder_draggable", true);
		simulateLiveTouches();
	}

	init();
});
