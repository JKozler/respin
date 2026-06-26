jQuery(document).ready(function ($) {

	$('.mws_buy_count').mwsProductCount();
	$('.mws_help_container').mwTooltips();
	$('.mws_order_form').mwsOrderForm();
	$('.mws_upsell_buttons_element').mwsUpsell();

	/**
	 * AJAX shop handler.
	 */
	$("body").on("click", ".shop-action", function () {

		var but = $(this);
		var action = but.attr('data-operation');
		var product = but.attr('data-product');
		var variantProduct = but.attr('data-variant-product');
		var count = but.attr('data-count');
		var thanksPage = but.attr('data-thanks-page');

		const allowDiscount = but.data('allowDiscount') === 1;

		if (count == 'input') {
			count = but.closest('.mws_add_to_cart_part').find('.mws_product_count_field input').val(); //
		}
		console.log(count);
		if (count < 1) {
			count = 1;
		}
		var isQuick = but.attr('data-isQuick');
		if (typeof isQuick === typeof undefined) {
			isQuick = false;
		} else {
			isQuick = (isQuick == 1);
		}

		const allowSimplified = but.data('allowSimplified') === 1;

		if (!product) {
			if (variantProduct) {
				// Variant product, no specific variation selected.
				//$(this).closest('.mws_add_to_cart_part').find('.mws_dropdown').addClass('mws_dropdown_opened');
				//alert($(this).closest('.mws_add_to_cart_part').find('.mws_dropdown').html());

				$.colorbox({
					html: but.closest('.mws_add_to_cart_part').find('.mws_variant_list_container').html(),
					maxWidth: '95%',
					width: '550px',
					reposition: false,
					className: 'mws_select_variant_lightbox',
					onComplete: function () {
						$(this).colorbox.resize();
						$('.shop-variant-select').attr('data-count', count);
					}
				});
			} else {
				//Variant can not be bought
				return false;
			}
		} else {

			but.addClass('working');

			switch (action) {
				case 'mws_cart_add':
					$.ajax({
						type: 'POST',
						data: {
							"action": action,
							"product": product,
							"count": count,
							"isQuick": isQuick,
							"allowDiscount": allowDiscount,
							"allowSimplified": allowSimplified,
							"thanksPage": thanksPage
						},
						url: ajaxurl,
						success: function (content) {
							var res = JSON.parse(content);
							$.colorbox({
								html: res.content,
								maxWidth: '95%',
								width: '550px',
								reposition: false,
								className: 'mws_add_to_cart_lightbox',
								onComplete: function () {
									$(this).colorbox.resize();
								}
							});
							if (!isQuick) {
								$('.mws_cart_items_count').text(res.cart_count);
								if (res.added) {
									if ($('#mw_header_cart tr#mws_product_id-' + res.added).length)
										$('#mw_header_cart tr#mws_product_id-' + res.added).replaceWith(res.added_hover);
									else
										$('#mw_header_cart table').append(res.added_hover);
									$('#mw_header_cart .mws_header_empty').hide();
									$('#mw_header_cart .mws_header_cart_footer').show();
								}
							}
							else
							{
								$('.mws_form_quick').mwsOrderForm();
							}
						},
						error: function (jqXHR, textStatus, errorThrown) {
							console.log('FAIL');
							console.log('xhr=', jqXHR);
							console.log('textStatus=', textStatus);
							console.log('error=', errorThrown);
							alert(window.textError_AjaxError);
						},
						complete: function (jqXHR, textStatus) {
							but.removeClass('working');
							var clearAfterBuy = but.attr('data-clearafterbuy');
							if (clearAfterBuy) {
								but.removeAttr('data-clearafterbuy');
								but.removeAttr('data-product');
							}
						}
					});
					break;
			}
		}

		return false;
	});


	$("body").on("click", ".mws_close_cart_box", function () {
		$.colorbox.close();
		return false;
	});

	/**
	 * Click on remove button in the cart removes the line.
	 */

	$(".mws_shop_order_content_cart").on("click", ".shop-action-remove", function (event) {
		//TODO animate pending progress of line, disable UI

		var button = $(this);
		var action = button.attr('data-operation');
		var product = button.attr('data-product');
		var item = ".mws_product_id-" + product;

		$(item).addClass('working');
		button.addClass('mws_loading');
		var hrefBkp = button.attr('href');
		button.removeAttr('href');

		$.ajax({
			type: 'POST',
			data: {
				"action": action,
				"product": product,
			},
			url: ajaxurl,
			success: function (content) {
				var res = JSON.parse(content);
				if (res.result == true) {
					//Delete line from cart icon
					$('#mw_header_cart tr#mws_product_item_' + res.productId).remove();
					//Update cart icon count
					$('.mws_cart_items_count').text(res.cart_count);
					//Cart is empty?
					if (!res.cart_count) {
						$('.mws_shop_order_content .mws_cart').addClass('mws_cart_empty');
						$('.mws_header_cart_hover').addClass('mws_header_cart_hover_empty');

						$('.mws_cart_continue_but').remove();
						//window.location.href = res.cart_url;
					}
					//Delete line from the cart with animation
					//$(item).slideUp('slow', function() {$(item).remove();});
					if (typeof res.newCart !== 'undefined') {
						$('.mws_cart_container').replaceWith(res.newCart);
					} else
						$(item).remove();
				} else {
					$.colorbox({html: res.message, className: 'mws_add_to_cart_lightbox'});
				}

				var html = res.html || undefined;
				if (html) {
					$('body').append(html);
				}
			},
			complete: function (jqXHR, textStatus) {
				button.removeClass('mws_loading');
				button.attr('href', hrefBkp);
			}
		});
		return false;
	});

	$(".mws_shop_order_content_cart").on("click", ".shop-discount-code-remove", function (event) {
		//TODO animate pending progress of line, disable UI

		var button = $(this);

		button.addClass('mws_loading');
		var hrefBkp = button.attr('href');
		button.removeAttr('href');

		$.ajax({
			type: 'POST',
			data: {
				"action": 'mws_discount_code_remove',
			},
			url: ajaxurl,
			success: function (content) {
				var res = JSON.parse(content);
				if (res.result == true) {
					//$('.mws_discount_code_row').hide();
					//$('.mws_add_discount_code').show();
					window.location.reload();
				} else {
					$.colorbox({html: res.message, className: 'mws_add_to_cart_lightbox'});
				}
			},
			complete: function (jqXHR, textStatus) {
				button.removeClass('mws_loading');
				button.attr('href', hrefBkp);
			}
		});
		return false;
	});

	/** Cart count modifications  */
	var reloadCallback = function (event, subaction) {
		var srcElem = $(this);
		var action = 'mws_order_step';
		var formdata = $("#mws_order_form").serialize();
		var nextUrl = '';
		var bkpUrl = srcElem.attr('href');
		//console.log(formdata);

		srcElem.addClass('mws_loading');
		srcElem.removeAttr('href');

		var performUrl = ajaxurl;
		var isReloading = false;

		var fncAjaxSuccess =
			function (content, textStatus, jqXHR) {
				//console.log('DONE');
				//console.log('content=', content);
				var formElems = $("#mws_order_form");

				//Clear all errors
				console.log('clearing errors');
				formElems.find('input').removeClass('mw_input_error');
				formElems.find('.mw_input_error_text').remove();

				if (content.success == true) {
					//Force implicit recount on fire reload
					isReloading = true;
					window.location.reload();
				} else {
					// Remove product that are not in the cart any more
					$.each(content.data.deleteProductIds, function (index, value) {
						console.log("removing product: " + value);
						var toDelete = formElems.find('tr.mws_product_id-' + value);
						toDelete.remove();
					});
					// Add error CSS + error instruction to invalid inputs
					console.log('validation errors');
					var errors = content.data.errors;
					var topMin = -1;
					$.each(errors, function (index, value) {
						console.log("error item: " + index + " = " + value);
						var filter = ":enabled[name='" + index + "']:first";
						//console.log("filter = " + filter);
						var elem = formElems.find(filter);
						elem.addClass('mw_input_error');
						elem.parent().after('<span class="mw_input_error_text">' + value + '</span>');
						var top = elem.first().offset().top;
						//console.log('elem error: ' + top);
						if ((topMin == -1) || (top < topMin)) {
							topMin = top;
						}
						// Clear data
						var row = elem.parents('tr.mws_cart_item');
						var cols = row.find(".mws_cart_item_availability,.mws_cart_item_price");
						//cols.empty();
					});
					// Scroll to first error
					//console.log('topMin='+topMin);
					if (topMin > -1) {
						$('html, body').stop().animate({
							'scrollTop': topMin - 50
						}, 500, 'swing');
					}
				}
			};

		var fncAjaxFail =
			function (jqXHR, textStatus, errorThrown) {
				console.log('FAIL');
				console.log('xhr=', jqXHR);
				console.log('textStatus=', textStatus);
				console.log('error=', errorThrown);
				alert(window.textError_AjaxError);
			};

		var fncAjaxComplete =
			function (jqXHR, textStatus) {
				//console.log('COMPLETE');
				if (isReloading) {
					//special drawing when reloading page?
					console.log('...RELOADING');
				} else {
					srcElem.attr('href', bkpUrl);
					srcElem.removeClass('mws_loading');
				}
			};

		$.ajax({
			type: 'POST',
			data: {
				"action": action,
				"form": formdata,
				"nextUrl": nextUrl,
				"curStep": window.orderStep,
				"subaction": subaction,
			},
			dataType: "json",
			timeout: 50000,
			url: performUrl,
			success: fncAjaxSuccess,
			error: fncAjaxFail,
			complete: fncAjaxComplete,
		});

		//console.log('EXIT');
		return event.preventDefault();
	};

	$(".mws_shop_order_content_cart").on("click", '.mws_count_reload', function (event) {
		return reloadCallback.apply(this, [event, 'recount']);
	});

	/** Cart discount code modifications  */

	$(".mws_shop_order_content_cart").on("click", '.mws_discount_code_reload', function (event) {
		return reloadCallback.apply(this, [event, 'discountCode']);
	});

	$(".mws_shop_order_content_cart").on("click", '.mws_add_discount_code', function (event) {
		$('.mws_discount_code_row').removeClass('ve_nodisp');
		$('.mws_add_discount_code').hide();
		$('.mws_cart_discount_code').focus();
		return false;
	});

	/** Order step submit */
	$(".mws_cart_continue_but").click(function (event) {
		var srcElem = $(this);
		var action = 'mws_order_step';
		var formdata = $("#mws_order_form").serialize();
		var nextUrl = srcElem.attr('href');
		var bkpUrl = nextUrl;
		var target = srcElem.attr('data-target');
		//console.log(formdata);

		srcElem.addClass('working');
		srcElem.attr('href', '');

		var performUrl = ajaxurl;
		var isReloading = false;

		var rq = $.ajax({
			type: 'POST',
			data: {
				"action": action,
				"form": formdata,
				"nextUrl": nextUrl,
				"curStep": window.orderStep,
			},
			dataType: "json",
			timeout: 50000,
			url: performUrl,
			success:
				function (content) {
					console.log(content);
					var formElems = $("#mws_order_form");

					var topMin = -1;

					//Flash message
					var elFlashMsgs = $(".mws_flash_messages");
					elFlashMsgs.html('');

					if (typeof content.data.flashMessage !== "undefined") {
						if ($.isArray(content.data.flashMessage)) {
							$.each(content.data.flashMessage, function (index, value) {
								elFlashMsgs.append(value);
							});
						} else {
							elFlashMsgs.html(content.data.flashMessage);
						}
						var top = elFlashMsgs.first().offset().top;
						if ((topMin == -1) || (top < topMin)) {
							topMin = top;
						}
					}

					if (content.success == true) {
						var data = content.data;
						if (data.stripe) {
							processStripe(data.stripe);
						} else if (data.twisto) {
							processTwisto(data.twisto, data.nextUrl);
						} else if (typeof data.nextUrl !== "undefined") {
							console.log("redirecting to " + data.nextUrl);
							nextUrl = data.nextUrl;
							isReloading = true;
							// similar behavior as an HTTP redirect

							if (target == 'parent') parent.location.replace(nextUrl);
							else window.location.replace(nextUrl);
						}
					} else {
						//Clear all errors
						if (content.data.deleteErrors) {
							formElems.find('.mw_input_error').removeClass('mw_input_error');
							formElems.find('.mw_input_error_text').remove();
						}
						//Add error CSS + error instruction to invalid inputs
						console.log('errors are present');
						if (typeof content.data.shouldReload !== "undefined") {
							console.log('should reload --> reloading page');
							isReloading = true;

							window.location.reload();

						} else {
							// Remove product that are not in the cart any more
							$.each(content.data.deleteProductIds, function (index, value) {
								console.log("removing product: " + value);
								var toDelete = formElems.find('tr.mws_product_id-' + value);
								toDelete.remove();
							});
							var errors = content.data.errors;
							$.each(errors, function (index, value) {
								console.log("error item: " + index + " = " + value);
								// Cart structure
								var filter = ":enabled[name='" + index + "']:first";
								//console.log("filter = " + filter);
								var elem = formElems.find(filter);
								elem.addClass('mw_input_error');
								if (window.orderStep == 1)
									elem.parent().after('<span class="mw_input_error_text">' + value + '</span>');
								else elem.parent().append('<span class="mw_input_error_text">' + value + '</span>');
								if (elem.first().length > 0) {
									var top = elem.first().offset().top;
									//console.log('elem error: ' + top);
									if ((topMin == -1) || (top < topMin)) {
										topMin = top;
									}
								}
								// Clear data in cart
								var row = elem.parents('tr.mws_cart_item');
								var cols = row.find(".mws_cart_item_availability,.mws_cart_item_price");
								cols.empty();

								// Summarize structure
								filter = ".mws_product_id-" + index + " td.mws_cart_item_title span";
								elem = formElems.find(filter);
								elem.first().after('<span class="mw_input_error_text">' + value + '</span>');
							});
							// Scroll to first error
							//console.log('topMin='+topMin);
							if (topMin > -1) {
								$('html, body').stop().animate({
									'scrollTop': topMin - 50
								}, 500, 'swing');
							}
						}
					}
				},
			error:
				function (jqXHR, textStatus) {
					alert(window.textError_AjaxError);
				},
			complete:
				function (jqXHR, textStatus) {
					//console.log('COMPLETE');
					if (isReloading) {
						//special drawing when reloading page?
						console.log('...RELOADING');
					} else {
						srcElem.attr('href', bkpUrl);
						srcElem.removeClass('working');
					}
				}
		});

		return event.preventDefault();
	});

	$('.mws_property_info a').click(function () {
		var elem = $(this);
		var text = elem.attr('title');
		var title = elem.attr('data-property');
		$.colorbox({
			html: '<div class="mw_colorbox_text_info"><h2>' + title + '</h2>' + text + '</div>',
			maxWidth: '95%',
			width: '550px'
		});

		return event.preventDefault();
	});

	/* ***************** Variant dropdown list ************** */

	$("body").on("click", ".shop-variant-select", function () {
		var elem = $(this);
		var productId = elem.attr('data-product');
		var availabilityHtml = elem.attr('data-msg-availability');
		var availabilityCss = elem.attr('data-availability-css');
		var priceHtml = elem.attr('data-msg-price');
		var saleHtml = elem.attr('data-msg-sale');
		var buttonHtml = elem.attr('data-msg-buy-button');
		var titleHtml = elem.find('.mws_product_title_variant').html();
		var canBuy = !!productId;

		var elemContent = elem.closest('.mws_dropdown_content');
		var allAvailabilityCSS = elemContent.attr('data-all-availability-css');

		console.log('variant selected', productId, canBuy);
		// console.log('availability:', availabilityCss, availabilityHtml);
		// console.log('texts: ', buttonHtml, priceHtml, saleHtml);

		var elemBuySection = elem.closest('.mws_add_to_cart_part,.pay_button_element_container');
		var elemBuyBtn = elemBuySection.find('.shop-action');
		if (elemBuyBtn.length) {
			if (canBuy) {
				elemBuyBtn.attr('data-product', productId);
				elemBuyBtn.attr('data-clearafterbuy', true);
				elemBuyBtn.click();
				// var txt = elemBuyBtn.text();
				// console.log('text=', txt);
			} else {
				elemBuyBtn.removeAttr('data-product');
			}
			// elemBuyBtn.find('span.ve_but_text').text(buttonHtml);
		}
		// var elemProduct = elem.closest('.mws_product');
		// if(elemProduct.length) {
		// elemProduct.removeClass(allAvailabilityCSS).addClass(availabilityCss);
		// elemProduct.find('.mws_product_availability').replaceWith(availabilityHtml);
		// elemProduct.find('.mws_product_title').html(titleHtml);
		// elemProduct.find('.mws_product_price').html(priceHtml);
		// elemProduct.find('.mws_product_sale').html(saleHtml);
		// }
		return false;
	});

	checkForMobileCategoryMenu();
});

function checkForMobileCategoryMenu() {
	var available_width = 0,
		all_items_width = 0;

	for (var i = 0, length = jQuery('.mws_top_panel .mws_category_menu_list li').length; i < length; i++) {
		all_items_width = all_items_width + jQuery('.mws_top_panel .mws_category_menu_list li').eq(i).outerWidth(true);
	}

	available_width = jQuery('.mws_category_list').width();

	if (available_width <= all_items_width) {
		jQuery('.mws_top_panel .mws_category_menu_list').hide();
		jQuery('.mws_top_panel .mws_category_menu_select_container').show();
	}
}

$ = jQuery;

function setDisabledPayType($el, disabled) {
	$el.prop("disabled", disabled);

	if (disabled) {
		$el.parent().addClass('mws_disabled');
	} else {
		$el.parent().removeClass('mws_disabled');
	}
}

function updatePrice($parent, prices, priceUnit, text_zeroPrice, text_makeSelection, text_invalidPayType) {
	var selected = null;
	var shipId = 0;
	var payId = 0;
	var isCod = '';
	var isAllowedPayType = true;
	selected_ship = $parent.find("input[name='mws_shipping']:checked");
	if (selected_ship.length > 0) {
		shipId = selected_ship.val();
	}
	selected_pay = $parent.find("input[name='mws_payment']:checked");
	if (selected_pay.length > 0) {
		isAllowedPayType = !selected_pay.prop('disabled');
		if (isAllowedPayType) {
			payId = selected_pay.val();
			isCod = selected_pay.data('is-cod');
		}
	}
	//console.log('ship&pay: ' + shipId + ' & ' + payType);

	$parent.find(".mws_cod_price_container").html(selected_ship.attr('data-cod-price'));

	var newPrice = 0;
	if (shipId != 0 && payId != 0) {
		//Both settings are present and are valid (COD is checked when getting value)
		newPrice = + prices[shipId].price.priceVatIncluded;
		if (isCod) {
			newPrice += prices[shipId].codPrice.priceVatIncluded;
		}
		newPrice = Math.round(newPrice * 100) / 100;
		if (newPrice != 0) {
			newPrice = (newPrice + ' ').replace('.', ',') + priceUnit;
		}
	}
	else if (!isAllowedPayType) {
		newPrice = text_InvalidPayType;
	}
	else {
		newPrice = text_makeSelection;
	}
	if (newPrice === 0) {
		newPrice = text_zeroPrice;
	}

	$parent.find(".mws_shipping_price span.mws_price_vatincluded").html(newPrice);
	$parent.find('.mws_shipping_more_' + shipId).show();

}

function initShippingAndPaymentInputs(parentSelector, prices, priceUnit, text_zeroPrice, text_makeSelection, text_invalidPayType) {
	var $parent = $(parentSelector);

	$parent.find("input[name='mws_shipping']:radio").on("change", function () {
		var elem = $(this);
		var payId = elem.attr('value');
		var checked = elem.attr('checked');
		var isCodEnabled = elem.hasClass('mws_cod_enabled');
		var isPersonal = elem.hasClass('mws_personal_pickup');
		var dbgInfo = ((isCodEnabled) ? ' isCod' : '') + ((isPersonal) ? ' isPersonalPickup' : '');
		//console.log('/ switched to ' + payId + ' ' + (dbgInfo.length > 0 ? 'options:' + dbgInfo : ''));
		//Update correct method
		var $payType = $parent.find("input[type=radio][data-is-cod=1]");
		setDisabledPayType($payType, !isCodEnabled);
		updatePrice($parent, prices, priceUnit, text_zeroPrice, text_makeSelection, text_invalidPayType);
	});

	$parent.find("input[name='mws_payment']:radio").on("change", function () {
		updatePrice($parent, prices, priceUnit, text_zeroPrice, text_makeSelection, text_invalidPayType);
	});

	//Recount price upon load is finished.
	updatePrice($parent, prices, priceUnit, text_zeroPrice, text_makeSelection, text_invalidPayType);
}

function initPaymentInputs(parentSelector) {
	var $parent = $(parentSelector);

	$parent.find("input[name='mws_payment']:radio").on("change", function () {
		$('.mws_payment_sub_radio').hide();
		$(this).closest('.mws_payment_radio_container').find('.mws_payment_sub_radio').show();
	});
}

function processStripe(payload) {
	if (typeof Stripe === "undefined") {
		var script = document.createElement('script');
		script.src = "https://js.stripe.com/v3/";
		script.onload = function () {
			processStripe(payload);
		};
		document.body.appendChild(script);
		return;
	}

	var stripe = Stripe(payload.public_key);
	stripe.redirectToCheckout({
		sessionId: payload.session_id
	}).then(function (result) {
		// @TODO show stripe load error
	});
}

function processTwisto (twisto, nextUrl) {
  if (typeof Twisto === "undefined") {
	var _twisto_config = {
	  public_key: twisto.public_key,
	  script: 'https://api.twisto.cz/v2/lib/twisto.js'
	};
	(function(e,g,a){function h(a){return function(){b._.push([a,arguments])}}var f=["check"],b=e||{},c=document.createElement(a);a=document.getElementsByTagName(a)[0];b._=[];for(var d=0;d<f.length;d++)b[f[d]]=h(f[d]);this[g]=b;c.type="text/javascript";c.async=!0;c.src=e.script;a.parentNode.insertBefore(c,a);delete e.script}).call(window,_twisto_config,"Twisto","script");
  }

  nextUrl = new URL(nextUrl);
  Twisto.check(
	twisto.payload,
	function (response) {
  	  if (response.status === 'accepted') {
		nextUrl.searchParams.set('state', response.status)
		nextUrl.searchParams.set('twistoTransactionId', response.transaction_id)
	  }
	  window.location.replace(nextUrl.toString());
	},
	function (response) {
	  window.location.replace(nextUrl.toString());
	}
  );
}

;(function (factory) {

	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof exports !== 'undefined') {
		module.exports = factory(require('jquery'));
	} else {
		factory(jQuery);
	}

})(function ($) {

	// mw product count input
	var MwsProductCount = (function (element) {

		function _MwsProductCount(element) {

			this.$el = $(element);
			var input = this.$el.find('input');
			var add_but = this.$el.find('.add');
			var remove_but = this.$el.find('.remove');
			var max = parseInt(input.data('max-count'));
			var val;

			add_but.click(function () {
				val = parseInt(input.val());
				new_val = val + 1;

				if (new_val <= max) {
					input.val(new_val).trigger('change');
				}
				return false;
			});
			remove_but.click(function () {
				val = parseInt(input.val());
				if (val > 1) {
					input.val(val - 1).trigger('change');
				}
				return false;
			});
			input.keyup(function () {
				val = input.val();
				if (isNaN(parseInt(val)) && val != '') input.val(1);
				else if (parseInt(val) < 1) input.val(parseInt(val)*-1);
				else if (parseInt(val) > max) input.val(max);
				else if (val != '') input.val(parseInt(val));
			});
			input.focusout(function () {
				val = input.val();
				if (isNaN(parseInt(val))) input.val(1);
				else input.val(parseInt(val));
			});


		}

		return _MwsProductCount;

	})();

	$.fn.mwsProductCount = function () {
		return this.each(function (index, el) {
			if (!el.MwsProductCount) {
				el.MwsProductCount = new MwsProductCount(el);
			}
		});
	};

	// mw shop hints
	var MwTooltips = (function (element) {

		function _MwTooltips(element) {

			this.$el = $(element);
			this.$info = this.$el.find('span');

			var obj = this;

			this.$el.click(function () {

				if (obj.$info.is(":hidden")) {
					obj.show();
				} else {
					obj.$info.hide();
				}

				return false;

			});
			this.$el.mouseenter(function () {
				obj.show();
			});
			this.$el.mouseleave(function () {
				obj.$info.hide();
			});
			this.$el.focusout(function () {
				obj.$info.hide();
			});

		}

		return _MwTooltips;

	})();

	MwTooltips.prototype.show = function () {
		$('.mws_help_container span').hide();
		this.$info.show();
		this.$el.removeClass('mws_help_container_left');
		if ($( window ).width() < (this.$info.offset().left + this.$info.width())) {
			this.$el.addClass('mws_help_container_left');
		} else {
			this.$el.removeClass('mws_help_container_left');
		}
	}

	$.fn.mwTooltips = function () {
		return this.each(function (index, el) {
			el.MwTooltips = new MwTooltips(el);
		});
	};

	// Upsells
	var MwsUpsell = (function (el) {

		function _MwsUpsell(element) {
			this.$el = $(element);
			this.$buttons = this.$el.find('.mws_upsell_button');

			var obj = this;

			this.$buttons.click(function () {
				if ($(this).hasClass('js-disabled')) {
					return;
				}

				const addToCart = $(this).hasClass('mws_upsell_button_yes');
				const upsellId = $(this).data('upsellId');
				const securityCode = $(this).data('securityCode');

				obj.$buttons.each(function () {
					if (!$(this).hasClass('disabled')) {
						$(this).addClass('disabled');
					}
				});

				if ($(this).hasClass('working')) {
					return false;
				}
				$(this).addClass('working');

				var target = $(this).attr('data-target');

				$.ajax({
					type: 'POST',
					data: {
						'action': 'mws_upsell_process',
						'upsellId': upsellId,
						'addToCart': addToCart,
						'u_code': securityCode,
					},
					dataType: "json",
					timeout: 10000,
					url: ajaxurl,
					success:
						function (content) {
							//Flash message
							let $flash = obj.$el.find('.mws_flash_messages');
							$flash.html(typeof content.data !== 'undefined' && typeof content.data.flashMessage !== 'undefined' ? content.data.flashMessage : '');

							if (content.success == true) {
								const data = content.data;

								if (data.stripe) {
									processStripe(data.stripe);
								} else if (data.twisto) {
									processTwisto(data.twisto, data.nextUrl);
								} else if (typeof data.nextUrl !== 'undefined') {
									console.log('redirecting to ' + data.nextUrl);
									nextUrl = data.nextUrl;
									isReloading = true;
									// similar behavior as an HTTP redirect

									if (target == 'parent') parent.location.replace(nextUrl);
									else window.location.replace(nextUrl);
								}
							} else {
								//Add error CSS + error instruction to invalid inputs
								const errors = content.data.errors;
								$.each(errors, function (index, value) {
									$flash.append('<div class="mw_input_error_text">' + value + '</div>');
								});
								obj.$buttons.removeClass('working');
							}
						},
					error:
						function (jqXHR, textStatus, errorThrown) {
							alert(window.textError_AjaxError + "\n\n(" + errorThrown + ')');
						},
					complete:
						function (jqXHR, textStatus) {
							obj.$buttons.removeClass('disabled');
						}
				})

				return false;

			});
		}

		return _MwsUpsell;

	})();

	MwsUpsell.prototype.show = function () {
		$('.mws_help_container span').hide();
		this.$info.show();
		this.$el.removeClass('mws_help_container_right');

		if (this.$info.offset().left < 0) {
			this.$el.addClass('mws_help_container_right');
		} else {
			this.$el.removeClass('mws_help_container_right');
		}
	}

	$.fn.mwsUpsell = function () {
		return this.each(function (index, el) {
			el.MwsUpsell = new MwsUpsell(el);
		});
	};

	// order form
	var MwsOrderForm = (function (element) {

		function _MwsOrderForm(element) {

			this.$form = $(element);
			this.isQuickBuy = this.$form.hasClass('mws_form_quick');
			this.isSimplified = this.$form.data('is-simplified');
			this.productShippingRequired = this.$form.data('is-product-shipping-required');
			this.miniupsellShippingRequired = false;
			var obj = this;

			// contact
			this.$form.on("click", ".disabled, .disabled input", function (e) {
				return false;
			});

			this.$form.find('.mws_help_container').mwTooltips();

			this.$form.find('.mws_order_form_want_invoice_check input').change(function () {

				if ($(this).prop("checked")) {
					obj.$form.find('.mws_invoice_needed_field').show();
				} else {
					obj.$form.find('.mws_invoice_needed_field').hide();
					obj.$form.find('.mws_order_form_company_check input').prop("checked", false).change();
				}

				obj.updateForm();

			});

			this.$form.find('.mws_order_form_company_check input').change(function () {

				if ($(this).prop("checked")) {
					obj.$form.find('.mws_order_form_company_container').show();
				} else {
					obj.$form.find('.mws_order_form_company_container').hide();
				}

			});

			this.$form.find('.mws_order_form_shipping_address_check input').change(function () {

				if ($(this).prop("checked")) {
					obj.$form.find('.mws_order_form_shipping_address_container').show();
				} else {
					obj.$form.find('.mws_order_form_shipping_address_container').hide();
				}

				obj.updateShippingsAndPayments();

			});

			//ZIP code - automatic ZIP code country recognition
			this.$form.on('input', '.mws_order_form_contact_zip, .mws_order_form_shipping_zip', function () {
				if ($(this).val().length) {
					//set the selector accordingly to invoice/shipping zip code
					const selector = this.classList.contains('mws_order_form_contact_zip') ? '.mws_order_form_contact_country' : '.mws_order_form_shipping_country';
					mw_set_country_by_zip($(this), obj.$form.find(selector));
				}
			});

			// invoice country
			this.$form.on('change', '.mws_order_form_contact_country', function () {

				obj.updateShippingsAndPayments();

				let country = obj.$form.find(".mws_order_form_contact_country").val();
				var elSkVatId = obj.$form.find(".order_contact_company_sk_vat_id");
				if (elSkVatId.length) {
					if (country == "SK")
						elSkVatId.removeClass("cms_nodisp");
					else
						elSkVatId.addClass("cms_nodisp");
				}

				obj.reloadSummary();
			});

			// shipping country
			this.$form.find('.mws_order_form_shipping_country').change(function () {

				obj.updateShippingsAndPayments();
			});

			// discount code
			this.$form.find('.mws_order_form_discount_code_check').change(function () {
				if ($(this).prop("checked")) {
					obj.$form.find('.mws_order_form_discount_code_form').show();
				} else {
					obj.$form.find('.mws_order_form_discount_code_form').hide();
					obj.$form.find('.mws_order_form_discount_code_form .mw_input_error_text').remove();
					obj.$form.find('.mws_order_form_discount_code_form input').val('');
				}
			});

			this.$form.find('.mws_order_form_apply_discount_code_but').click(function () {
				$(this).addClass('working');
				obj.reloadSummary(true);
				return false;
			});

			this.$form.on('click','.mws_order_form_remove_discount_code', function () {
				obj.$form.find('.mws_order_form_discount_code').show();
				obj.$form.find('.mws_order_form_discount_code_check').prop("checked", false).change();
				obj.$form.find('.mws_order_form_discount_code_form input').val('');
				obj.reloadSummary();
				return false;
			});

			this.$form.find('.mws_order_form_discount_code_form input').focus(function () {
				obj.$form.find('.mws_order_form_discount_code_form .mw_input_error_text').remove();
			});

			// change product count
			this.$form.find('.mws_product_count_field').mwsProductCount();

			this.$form.find('.mws_product_count_field input').change(function () {
				obj.reloadSummary();
			});

			// miniupsell
			this.$form.find('.mws_order_form_miniupsell input').change(function () {
				var shippingRequired = $(this).data('shipping-required');
				obj.miniupsellShippingRequired = false;
				if(shippingRequired == 1) {
					if($(this).prop("checked")) {
						obj.miniupsellShippingRequired = true;
						obj.$form.find('.mws_shipping_needed_field').show();
					} else {
						obj.$form.find('.mws_shipping_needed_field').hide();
						obj.$form.find('.mws_shipping_needed_field .mws_order_form_shipping_address_check input').prop("checked", false);
						obj.$form.find('.mws_shipping_radio input').prop("checked", false);
					}
					obj.updateForm();
				}
				else {
					obj.updateShippingsAndPayments();
				}
				obj.reloadSummary();
			});

			// select shipment
			this.$form.find('.mws_shipping_radio input').change(function () {

				// show selected shipping more info container (packeta)
				obj.$form.find('.mws_shipping_more').hide();
				$(this).closest('.mws_shipping_radio').find('.mws_shipping_more').show();

				// disable / enable cod
				if($(this).data('codenabled')) {
					obj.$form.find('.mws_cod_price_container').html($(this).data('cod-price'));
					obj.$form.find('.mws_payment_radio_cod').removeClass('mws_disabled');
					obj.$form.find('.mws_payment_radio_cod input').prop("disabled", false);
				} else {
					obj.$form.find('.mws_payment_radio_cod').addClass('mws_disabled');
					obj.$form.find('.mws_payment_radio_cod input').prop("disabled", true).prop("checked", false);
					obj.$form.find('.mws_cod_price_container').html('');
				}

				obj.$form.find('.mws_order_form_shipping_container .mw_input_error_text').remove();

				obj.reloadSummary();
			});

			// select payment
			this.$form.find('.mws_payment_radio input').change(function () {
				obj.$form.find('.mws_order_form_payment_container .mw_input_error_text').remove();
				obj.reloadSummary();
			});

			// packeta
			this.$form.find('.mws_open_packeta_select').click(function () {
				var country = obj.getShippingCountry().toLowerCase();

				Packeta.Widget.pick(mws_packeta_login.api_key, function(point) {

					if (point) {
						var address = point.name;
						if (point.zip) address = address + ", " + point.zip;
						if (point.city) address = address + " " + point.city;

						obj.$form.find('.mws_shipping_packeta_info_address').html(address);
						obj.$form.find('.mws_shipping_packeta_address').val(address);
						obj.$form.find('.mws_shipping_packeta_id').val(point.id).trigger('change');

					}

				}, {country:country})
			});

			// buy
			this.$form.find(".mws_order_form_send_button").click(function() {
				var button = $(this);
				var nonce = obj.$form.data('nonce');

				if (button.hasClass("working")) {
					return false;
				}

				button.addClass("working");

				var target = button.attr('data-target');

				const $form = button.closest('.mws_order_form');
				const allowSimplified = $form.length ? $form.data('isSimplified') === 1 : false;

				var rq = $.ajax({
					type: 'POST',
					data: {
						"action": 'mws_form_process',
						"nonce": nonce,
						"form": obj.$form.serialize(),
						"allowSimplified": allowSimplified,
					},
					dataType: "json",
					timeout: 50000,
					url: ajaxurl,
					success:
						function (content) {

							obj.$form.find('.mw_input_error').removeClass('mw_input_error');
							obj.$form.find('.mw_input_error_text').remove();

							var topMin = -1;

							//Flash message
							var flash = obj.$form.find(".mws_flash_messages");
							if (typeof content.data.flashMessage !== "undefined") {
								flash.html(content.data.flashMessage);
								var top = flash.first().offset().top;
								if ((topMin === -1) || (top < topMin)) {
									topMin = top;
								}
							} else {
								flash.html('');
							}

							if (content.success == true) {
								var data = content.data;
								if (data.stripe) {
									processStripe(data.stripe);
								} else if (data.twisto) {
									processTwisto(data.twisto, data.nextUrl);
								} else if (typeof data.nextUrl !== "undefined") {
									console.log("redirecting to " + data.nextUrl);
									nextUrl = data.nextUrl;
									isReloading = true;
									// similar behavior as an HTTP redirect

									if (target == 'parent') parent.location.replace(nextUrl);
									else window.location.replace(nextUrl);
								}
							} else {
								//Add error CSS + error instruction to invalid inputs
								console.log('errors are present');
								var errors = content.data.errors;
								$.each(errors, function (index, value) {
									console.log("error item: " + index + " = " + value);
									var filter = ":enabled[name='" + index + "']:first, label[for='" + index + "']:first";
									//console.log("filter = " + filter);
									var elem = obj.$form.find(filter);
									if (elem.length) {
										elem = elem.first();
										// Use label if associated for checkboxes
										if (elem.is(':checkbox')) {
											var label = $('label[for="' + elem.attr('id') + '"]');
											if (label.length) {
												elem = label.first();
											}
										}
										elem.addClass('mw_input_error');
										elem.after('<span class="mw_input_error_text">' + value + '</span>');
										var top = elem.parent().find('.mw_input_error_text').offset().top;
										if ((topMin === -1) || (top < topMin)) {
											topMin = top;
										}
									} else {
										var elFlashMsgs = obj.$form.find(".mws_flash_messages");
										elFlashMsgs.html('<span class="mw_input_error_text">' + value + '</span>');
									}

								});
								// Scroll to first error
								//console.log('topMin='+topMin);
								if (topMin > -1) {
									$('html, body').stop().animate({
									    'scrollTop': topMin - 80
									}, 500, 'swing');
								}
							}
						},
					error:
						function (jqXHR, textStatus, errorThrown) {
							alert(window.textError_AjaxError + "\n\n(" + errorThrown + ')');
						},
					complete:
						function (jqXHR, textStatus) {
							button.removeClass("working");
						}
				});

				return false;
			});

			this.$form.on('change','.mw_input_error', function () {
				$(this).removeClass('mw_input_error');
				$(this).parent().find('.mw_input_error_text').remove();
			});


		}

		return _MwsOrderForm;

	})();

	MwsOrderForm.prototype.updateForm = function() {

		if(this.isSimplified) {

			if(this.$form.find('.mws_order_form_want_invoice_check input').prop('checked')) {
				this.$form.find('.mws_order_form_shipping_address_check').removeClass('disabled');
			}
			else if(this.miniupsellShippingRequired || this.productShippingRequired) {
				this.$form.find('.mws_order_form_shipping_address_check input').prop('checked', true).change();
				this.$form.find('.mws_order_form_shipping_address_check').addClass('disabled');
			}

		}

	}

	MwsOrderForm.prototype.getShippingCountry = function() {
		var shippingCountry = this.$form.find(".mws_order_form_contact_country").val();
		//console.log(this.$form.find(".mws_order_form_shipping_country").length);
		if(this.$form.find('.mws_order_form_shipping_address_check input').prop("checked") && this.$form.find(".mws_order_form_shipping_country").length)
		{
			shippingCountry = this.$form.find(".mws_order_form_shipping_country").val();
		}
		return shippingCountry;
	}

	MwsOrderForm.prototype.getShippingCurrency = function() {
		if(this.$form.find(".mws_order_form_contact_country option:selected").length)
		{
			return this.$form.find(".mws_order_form_contact_country option:selected").attr("data-currency");
		}
		else
		{
			return this.$form.find(".mws_order_form_contact_country").attr("data-currency");
		}
	}

	MwsOrderForm.prototype.updateShippingsAndPayments = function() {

		let op_currencies;
		let miniupsell = this.$form.find('.mws_order_form_miniupsell input');
		let hideNotEl = false;
		let input;

		let country = this.getShippingCountry();
		let currency = this.getShippingCurrency().toLowerCase();

		if(miniupsell.length && miniupsell.prop("checked") && miniupsell.data('shipping-required') == 0) {
			hideNotEl = true;
		}

		// currency
		this.$form.find('.mws_shipping_radio').each(function () {
			let hide = false;
			input = $(this).find('input');

			if (input.attr('data-country') && country !== input.attr('data-country')) {
				hide =  true;
			}
			if (hideNotEl && input.hasClass('mws_personal_pickup')) {
				hide =  true;
			}

			if (hide) {
				$(this).addClass('novisible');

				if (input.is(':checked')) {
					input.prop('checked', false);
				}
			} else {
				$(this).removeClass('novisible');
			}
		});

		// payment
		if (!this.isQuickBuy) {
			this.$form.find('.mws_payment_radio').each(function () {

				let hide = false;
				input = $(this).find('input');

				op_currencies = JSON.parse(input.attr('data-currencies'));

				if (hideNotEl && $(this).hasClass('mws_payment_radio_cod')) {
					hide =  true;
				}

				if(op_currencies && $.inArray(currency, op_currencies) == -1) {
					hide = true;
				}

				if (hide) {
					$(this).addClass('novisible');

					if (input.is(':checked')) {
						input.prop('checked', false);
					}
				} else {
					$(this).removeClass('novisible');
				}
			});
		}
	}

	MwsOrderForm.prototype.reloadSummary = function (checkDiscountCode = false) {

		this.$form.addClass("reloading");

		var formData = this.$form.serialize();
		var obj = this;

		$.ajax({
			type: 'POST',
			data: {
				"action": 'mws_form_reload_summary',
				"form": formData,
				"htmlId": this.$form.attr('id'),
				"isQuickBuy": obj.isQuickBuy,
				"checkDiscountCode": checkDiscountCode,
			},
			dataType: "json",
			timeout: 10000,
			url: ajaxurl,
			success:
				function (content) {
					obj.$form.find('.mws_order_form_sumarize').replaceWith(content.data.summary);

					obj.$form.find('.mws_order_form_shipping_container label').each(function (){
						let id = $(this).find('input').val();
						let price = content.data.shipping_prices[id];
						$(this).find('.mws_radio_select_right').html(price);
						if (price === null) {
							$(this).addClass('overweight');
							$(this).prop('checked', false);
						} else {
							$(this).removeClass('overweight');
						}
					});

					if(obj.isSimplified) {
						// if simplified and price is more then 10 000
						if(content.data.price > 10000) {
							obj.$form.find('.mws_invoice_needed_field').show();
							obj.$form.find('.mws_order_form_want_invoice_check input').prop('checked', true);
							obj.$form.find('.mws_order_form_want_invoice_check_container').hide();
						}
						else {
							obj.$form.find('.mws_order_form_want_invoice_check_container').show();
						}

						obj.updateForm();
					}

					// discount code
					if(content.data.discount_code_error) {
						obj.$form.find('.mws_order_form_discount_code_form_in').after('<span class="mw_input_error_text">' + content.data.discount_code_error + '</span>');
					}

					if(content.data.active_discount_code) {
 						obj.$form.find('.mws_order_form_discount_code').hide();
					}

					let paymentSelect = content.data.payment_select || null;
					if (paymentSelect) {
						// Remember selected payment
						let $selectedPayment = obj.$form.find('.mws_payment_radio input:checked');
						let $selectedBank = $selectedPayment.closest('.mws_payment_radio_container').find('.mws_payment_bank_radio input:checked');

						// Replace payment methods with new ones
						obj.$form.find('.mws_order_form_payment_inner_container').html(paymentSelect);

						// Try to select previously selected payment
						if ($selectedPayment.length) {
							let $paymentRadio = obj.$form.find('.mws_payment_radio input[value="' + $selectedPayment.val() + '"]');
							$paymentRadio.prop('checked', true);
							$paymentRadio.closest('.mws_payment_radio_container').find('.mws_payment_sub_radio').show();
						}

						if ($selectedBank.length) {
							obj.$form.find('.mws_payment_bank_radio input[value="' + $selectedBank.val() + '"]').prop('checked', true);
						}
					}
				},
			error:
				function (jqXHR, textStatus, errorThrown) {
					alert(window.textError_AjaxError + "\n\n(" + errorThrown + ')');
				},
			complete:
				function (jqXHR, textStatus) {
					obj.$form.removeClass('reloading');
					obj.$form.find('.mws_order_form_apply_discount_code_but').removeClass('working');
				}
		});
	}

	$.fn.mwsOrderForm = function () {
		return this.each(function (index, el) {
			el.MwsOrderForm = new MwsOrderForm(el);
		});
	};

});

function mw_init_product_count(target) {
	jQuery(target).mwsProductCount();
}

function mw_init_order_form(target) {
	jQuery(target).mwsOrderForm();
}

function mw_set_country_by_zip (zipEl, countryEl) {
	var options = [];
	countryEl.children().each(function() {
		options.push($(this).val());
	});

	if (options.includes('CZ') &&  options.includes('SK') && options.length === 2) {
		const countryRegex = {
			'CZ':/[1-7]/,
			'SK':/[890]/,
		}
		for (const key in countryRegex) {
			if(zipEl.val()[0].match(countryRegex[key])) {
				countryEl.val(key).change();
			}
		}
	}
}

function mw_discount_countdown(different) {

	var interval = different * 1000;

	if(interval <= 0) {
		$('.mws_discounted_countdown_container').hide();
		return window.location.reload();
	}

	var time_units = [];

	let days = Math.floor(interval / (1000 * 60 * 60 * 24));
	let hours = Math.floor((interval % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
	let min = Math.floor((interval % (1000 * 60 * 60)) / (1000 * 60));
	let seconds = Math.floor((interval % (1000 * 60)) / 1000);

	time_units.push(days);
	time_units.push(hours);
	time_units.push(min);
	time_units.push(seconds);

	let fields = $(".mws_discounted_countdown_fields").children();

	let i = 0;
	let show_zero_unit = false;
	for (unit of time_units) {
		var unit_fields = $(fields[i]).children();
		var unit_text_fields = $(unit_fields[1]).children();

		if(unit > 0 || show_zero_unit) {

			$(unit_fields[0]).text(unit);
			$(unit_fields[0]).show();

			if(unit > 0) {
				show_zero_unit = true;
			}

			if (unit === 1) {
				$(unit_text_fields[0]).siblings().hide();
				$(unit_text_fields[0]).show();
			} else if (unit <= 4 && unit !== 0) {
				$(unit_text_fields[1]).siblings().hide();
				$(unit_text_fields[1]).show();
			} else {
				$(unit_text_fields[2]).siblings().hide();
				$(unit_text_fields[2]).show();
			}
		} else {
			$(unit_fields[0]).hide();
			unit_text_fields.hide();
		}

		i++;
	}
	// -1 second
	different = different -1;

	setTimeout(function () {
		mw_discount_countdown(different);
	}, 1000);
}
