<?php
/**
 * Template showing the cart's content.".
 */

use Mioweb\Library\Api\ThePay\Exceptions\ThePayException;

get_header('mwshop');
get_blog_sidebar('mwshop');

global $vePage;

$cart = MWS()->getCart();
$cartItemsCount = $cart->getItems()->count();
//Update first step fulfillment every time.
$cart->setFulfilledStep(MwsOrderStep::Cart, ($cartItemsCount > 0));

// Use template according to selected step
$step = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : '';
$step = MwsOrderStep::checkedValue($step, MwsOrderStep::Cart);

$target = MWS()->edit_mode ? 'parent' : '';

$isQuick = isset($_REQUEST['isQuick']) ? (bool) $_REQUEST['isQuick'] : false;
$fulfilment = $cart->getStepsFulfillment();
mwshoplog('step=' . $step . ' fullfilled=['
	. implode(',', array_map(function ($key, $item) {
		return ($item ? $key : '');
	}, array_keys($fulfilment), $fulfilment))
	. ']', MWLL_DEBUG, 'order');
//mwshoplog('fulfillment='.print_r($fulfilment, true), MWLL_DEBUG, 'order');

//We request nonempty cart for further processing of order. Otherwise keep up with cart content only.
if ($step == MwsOrderStep::ThankYou) {
	// Thank you page only when "success" argument is present.
	if (isset($_REQUEST['success'])) {
		$success = (bool) filter_var($_REQUEST['success'], FILTER_VALIDATE_BOOLEAN);
	} else {
		$step = MwsOrderStep::Cart;
	}
} elseif ($cartItemsCount == 0) {
	$step = MwsOrderStep::Cart;
} elseif (!$cart->areFulfilledPriorSteps($step)) {
	// Incorrect direct jump to following step without previous step is fulfilled.
	$step = MwsOrderStep::Cart;
}

$currency = $cart->getCurrency();
$unit = MwsCurrencyEnum::getSymbol($currency);

?>

<div class="mws_shop_container mw_transparent_header_padding">
	<?php
	$steps = MwsOrderStep::getAll();
	if (($key = array_search(MwsOrderStep::ThankYou, $steps)) !== false) {
		unset($steps[$key]);
	}
	?>

	<!-- NAVIGATION -->

	<div class="mws_cart_navigation <?php if ($step > 3) {
		echo 'eshop_color_background';
									} ?>">
		<div class="mws_cart_navigation_in row_fix_width">
			<?php
			foreach ($steps as $sid) {
				$icon = MwsOrderStep::getIcon($sid);

				$isStepFulfilled = $cart->areFulfilledPriorSteps($sid);
				$class = 'mws_cart_step_item mws_cart_step_item_s' . $sid;
				if ($step == $sid) {
					$class .= ' eshop_color_background mws_cart_step_item_a';
				} elseif ($step > $sid) {
					$class .= ' eshop_color_background mws_cart_step_item_f';
					$icon = 'step_ok';
				}
				$step_icon = '<span class="icon">' . MWS()->getTemplateIcon($icon) . '</span>';
				$class .= ($isStepFulfilled ? ' mws_order_step_fullfilled' : ' mws_order_step_pending');
				if ($step == $sid || !$cart->areFulfilledPriorSteps($sid)) {
					echo '<div class="' . $class . '">' . $step_icon . '<span class="text">' . MwsOrderStep::getCaption($sid) . '</span></div>';
				} else {
					echo '<a href="' . MWS()->getUrl_Cart($sid) . '" class="' . $class . '">' . $step_icon . '<span class="text">' . MwsOrderStep::getCaption($sid) . '</span></a>';
				}
			}
			?>
			<div class="cms_clear"></div>
		</div>
	</div>

	<!-- JAVA SCRIPTs -->
	<script>
		// MWS Order info
		/* Global variables */
		var orderStep =<?php echo $step;?>;
		var orderUrl = "<?php echo htmlspecialchars("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");?>";
		var textError_AjaxError = "<?php echo __('Komunikace se serverem se nezdařila. Prosím opakujte požadavek později.', 'mwshop'); ?>";
		var stepsFullfilment = JSON.parse('<?php echo json_encode($fulfilment) ?>');
		console.log('fulfillment = ' + stepsFullfilment);
	</script>

	<!-- CONTENT -->
	<div class="mws_shop_content mws_shop_order_content mws_shop_order_content_<?php echo MwsOrderStep::getId($step); ?> row_fix_width">
		<!-- FLASH MESSAGES LOCATION -->
		<div class="mws_flash_messages"></div>
		<!-- STEP CONTENT -->
		<?php if ($step == MwsOrderStep::Cart) {
			$error = false; ?>

			<div class="mws_cart_container <?php if ($cart->getItems()->isEmpty()) {
				echo 'mws_cart_empty';
										   } ?>">
				<?php
					$termsUrl = MWS()->getUrl_TermsAndConditions();
					if ($cartItemsCount > 0 && MWS()->isTermsAllowed() && empty($termsUrl)) {
					echo MWS()->printMissingTermsError();
					$error = true;
					}
				?>
				<form id="mws_order_form">
			<?php
			$cart = MWS()->getCart();
			$cart->recount(false, true);

			$cartTotalPrice = $cart->getStoredTotalPrice();
			$cartCurrency = $cartTotalPrice !== null ? $cartTotalPrice->getCurrency() : null;

			$cart_info = '';
			$minFreeFrom = MwsShipping::minFreeFrom();
			if ($cartCurrency !== null && $minFreeFrom && $cart->isShippingRequired()) {
				if (($missingPrice = $minFreeFrom->sub($cartTotalPrice))->getPriceVatIncluded() > 0) {
					$cart_info .= '<div class="mws_info_box"><span class="info_icon">i</span>' . sprintf(__('Nakupte ještě za %s a <strong>získate dopravu zdarma</strong>.', 'mwshop'), $missingPrice->asCurrency($cartCurrency)->htmlPriceVatIncluded()) . '</div>';
				} else {
					$cart_info .= '<div class="mws_info_box"><span class="info_icon">i</span>' . sprintf(__('Při nákupu nad %s máte nárok na dopravu zdarma.', 'mwshop'), $minFreeFrom->asCurrency($cartCurrency)->htmlPriceVatIncluded()) . '</div>';
				}
			}
			$minOrderPrice = MWS()->getMinOrderPrice();
			if ($cartTotalPrice !== null && $minOrderPrice && ($missingPrice = $minOrderPrice->sub($cartTotalPrice))->getPriceVatIncluded() > 0) {
				$cart_info .= '<div class="mws_info_box"><span class="info_icon">i</span>' . sprintf(__('<strong>Minimální výše objednávky je %s</strong>. Je nutné do košíku přidat ještě zboží minimálně za %s.', 'mwshop'), $minOrderPrice->asCurrency($cartCurrency)->htmlPriceVatIncluded(), $missingPrice->asCurrency($cartCurrency)->htmlPriceVatIncluded()) . '</div>';
			}

			if ($cart_info) {
				echo '<div class="mws_info_box_container">' . $cart_info . '</div>';
			}

			mwsRenderParts('cart', 'loop');
			?>
				</form>
			</div>

			<div class="mws_order_footer">
				<a class="mws_cart_back_but"
				   href="<?php echo MWS()->getUrl_Home(); ?>"><?php echo __('Zpět k nákupu', 'mwshop'); ?></a>
			<?php
			if ($cartItemsCount > 0 && !$error) {
				if ($cart->isRecounted()) {
					?>
						<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
						   data-target="<?php echo $target; ?>"
						   href="<?php echo MWS()->getUrl_Cart($step + 1); ?>"><?php echo __('Pokračovat', 'mwshop'); ?></a>
				<?php } else { ?>
						<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
						   data-target="<?php echo $target; ?>"
						   href="<?php echo MWS()->getUrl_Cart($step); ?>"><?php echo __('Přepočítat', 'mwshop'); ?></a>
					<?php
				}
			} ?>
				<div class="cms_clear"></div>

			</div>
			<?php
		} elseif ($step == MwsOrderStep::Contact) { ?>
			<div class="mws_contact">
				<form class="ve_content_form ve_form_style_1 ve_form_input_style_2" autocomplete="on"
					  id="mws_order_form">
			<?php

			$meta = $cart->getContact();

			$country = $meta['address']['country'] ?? MWS()->getDefaultShippingCountry();

			/** @var bool $canSimplified Can be simplified invoice used? */
			$canSimplified = MWS()->gateways()->getDefault()->isSimplifiedInvoiceAllowedForEshop();
			/** @var bool $shippingNeeded Is shipping required? */
			$shippingNeeded = $cart->isShippingRequired();
			/** @var bool $mustHaveInvoice Is invoice required? */
			$mustHaveInvoice = !($canSimplified
						&& $cart->getStoredTotalPrice() && $cart->getStoredTotalPrice()->getPriceVatIncluded() <= 10000
						&& $country === MwsCountry::CZ);
			$wantInvoiceChecked = $mustHaveInvoice ? true : isset($meta['want_invoice']) && $meta['want_invoice'];
			?>

					<input type="hidden" name="order_contact[totalPrice]" value="<?php
					echo $cart->getStoredTotalPrice() ? $cart->getStoredTotalPrice()->getPriceVatIncluded() : 0.1 ?>">

					<h2><label for="order_contact_email"><?php echo __('E-mail', 'mwshop'); ?></label></h2>
					<div class="ve_form_row">
						<input class="ve_form_text" type="text" autocomplete="email" name="order_contact[email]"
							   value="<?php echo get_array_field($meta, 'email'); ?>" id="order_contact_email"/>
					</div>
			<?php if (!$mustHaveInvoice) { // COUNTRY for simplified invoice is in front
				?>
						<h2><label for="order_contact_country"><?php echo __('Země', 'mwshop'); ?></label></h2>
						<div class="ve_form_row">
				<?php mws_generate_country_select('order_contact[address][country]', 'order_contact_country', 've_form_text', $country) ?>
						</div>
			<?php } ?>

					<script>
						var cartTotalPrice = <?php echo $cart->getStoredTotalPrice() ? $cart->getStoredTotalPrice()->getPriceVatIncluded() : 0 ?>;
						var canSimplifiedInvoice = <?php echo $canSimplified ? 'true' : 'false' ?>;

						jQuery(document).ready(function ($) {
							// Hook for automatic ZIP code country recognition
							$('#mws_order_form').on('input', '#order_contact_zip, #order_contact_shipping_zip', function () {
								if ($(this).val().length) {
									//set the selector accordingly to invoice/shipping zip code
									const selector = this.id === 'order_contact_zip' ? '#order_contact_country' : '#order_contact_shipping_country';
									mw_set_country_by_zip($(this), $(selector));
								}
							});

							// Hook for country selection change.
							$('#order_contact_country').on('change', function () {

								var country = $(this).val();
								var elSkVatId = $('#order_contact_company_sk_vat_id');
								if (elSkVatId.length) {
									elSkVatId = elSkVatId.parent(); // use parental wrapper element
									//console.log(country);
									if (country === 'SK')
										elSkVatId.removeClass('cms_nodisp');
									else
										elSkVatId.addClass('cms_nodisp');
								}
								if (canSimplifiedInvoice) {
									if (cartTotalPrice <= 10000 && country === 'CZ') {
										$('#order_contact_want_invoice_group').show();
										$('#order_contact_want_invoice').removeAttr('onclick');
									}
									else {
										$('#order_contact_want_invoice_group').hide();
										$('#order_contact_want_invoice')
											.attr('onclick', 'return false')
											.prop('checked', true)
										;
										$('#order_contact_invoice_container').show();
									}
								}
							}).change();
						});
					</script>

			<?php if ($canSimplified) { ?>
						<h2 id="order_contact_want_invoice_group" <?php if ($mustHaveInvoice) {
							echo 'class="cms_nodisp"';
																  } ?>>
							<input class="mw_toggle_container"
								   data-target="order_contact_invoice_container"
								   type="checkbox"
								   name="order_contact[want_invoice]" id="order_contact_want_invoice"
				<?php
				if ($wantInvoiceChecked) {
					echo ' checked="checked" ';
				}
				if ($mustHaveInvoice) {
					echo ' onclick="return false" ';
				}
				?>
							>
							<label
								for="order_contact_want_invoice"><?php echo __('Potřebuji vystavit fakturu', 'mwshop'); ?></label>
						</h2>
			<?php } else { ?>
						<input type="hidden" name="order_contact[want_invoice]" value="on">
			<?php } ?>

					<div id="order_contact_invoice_container" <?php echo !$wantInvoiceChecked ? 'class="cms_nodisp"' : ''; ?>>
						<h2><?php echo __('Fakturační údaje', 'mwshop'); ?></h2>
						<div class="ve_form_row ve_form_row_half">
							<label for="order_contact_firstname"><?php echo __('Jméno', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" autocomplete="given-name"
								   name="order_contact[address][firstname]"
								   value="<?php echo get_array_field($meta, 'address', 'firstname') ?>" id="order_contact_firstname"/>
						</div>
						<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
							<label for="order_contact_surname"><?php echo __('Příjmení', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" autocomplete="family-name"
								   name="order_contact[address][surname]"
								   value="<?php echo get_array_field($meta, 'address', 'surname') ?>" id="order_contact_surname"/>
						</div>
						<div class="cms_clear"></div>
						<div class="ve_form_row">
							<label for="order_contact_phone"><?php echo __('Telefon', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" autocomplete="tel"
								   name="order_contact[address][phone]"
								   value="<?php echo get_array_field($meta, 'address', 'phone') ?>" id="order_contact_phone"/>
						</div>
						<div class="ve_form_row">
							<label
								for="order_contact_street"><?php echo __('Ulice a číslo popisné', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" autocomplete="street-address"
								   name="order_contact[address][street]"
								   value="<?php echo get_array_field($meta, 'address', 'street') ?>" id="order_contact_street"/>
						</div>
						<div class="ve_form_row ve_form_row_half">
							<label for="order_contact_city"><?php echo __('Město', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" autocomplete="address-level2"
								   name="order_contact[address][city]"
								   value="<?php echo get_array_field($meta, 'address', 'city') ?>" id="order_contact_city"/>
						</div>
						<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
							<label for="order_contact_zip"><?php echo __('PSČ', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" autocomplete="postal-code"
								   name="order_contact[address][zip]"
								   value="<?php echo get_array_field($meta, 'address', 'zip') ?>" id="order_contact_zip"/>
						</div>
			<?php if ($mustHaveInvoice) { // COUNTRY for regular invoice is within invoice attributes
				?>
							<label for="order_contact_country"><?php echo __('Země', 'mwshop'); ?></label>
							<div class="ve_form_row">
				<?php mws_generate_country_select('order_contact[address][country]', 'order_contact_country', 've_form_text', $country) ?>
							</div>
			<?php } ?>
						<div class="cms_clear"></div>

						<h2>
							<input class="mw_toggle_container" data-target="order_contact_company_container"
								   type="checkbox"
								   name="order_contact[is_company]" id="order_contact_is_company"
			<?php if (isset($meta['is_company']) && $meta['is_company']) {
				echo ' checked="checked" ';
			} ?>
							>
							<label
								for="order_contact_is_company"><?php echo __('Nakupuji na firmu', 'mwshop'); ?></label>
						</h2>
						<div
							id="order_contact_company_container" <?php if (!(isset($meta['is_company']) && $meta['is_company'])) {
								echo ' class="cms_nodisp" ';
																 } ?>>
							<div class="ve_form_row">
								<label
									for="order_contact_company_name"><?php echo __('Název společnosti', 'mwshop'); ?></label>
								<input class="ve_form_text" type="text" name="order_contact[company_info][company_name]"
									   value="<?php echo get_array_field($meta, 'company_info', 'company_name') ?>"
									   id="order_contact_company_name"/>
							</div>
							<div class="ve_form_row ve_form_half">
								<label for="order_contact_company_id"><?php echo __('IČ', 'mwshop'); ?></label>
								<input class="ve_form_text" type="text" name="order_contact[company_info][company_id]"
									   value="<?php echo get_array_field($meta, 'company_info', 'company_id') ?>"
									   id="order_contact_company_id"/>
							</div>
							<div class="ve_form_row ve_form_half ve_form_half_r">
								<label for="order_contact_company_vat_id"><?php echo __('DIČ', 'mwshop'); ?></label>
								<input class="ve_form_text" type="text"
									   name="order_contact[company_info][company_vat_id]"
									   value="<?php echo get_array_field($meta, 'company_info', 'company_vat_id') ?>"
									   id="order_contact_company_vat_id"/>
							</div>
							<div class="ve_form_row ve_form_half ve_form_half_r">
								<label
									for="order_contact_company_sk_vat_id"><?php echo __('IČ DPH', 'mwshop'); ?></label>
								<input class="ve_form_text" type="text"
									   name="order_contact[company_info][company_sk_vat_id]"
									   value="<?php echo $meta['company_info']['company_sk_vat_id'] ?? '' ?>"
									   id="order_contact_company_sk_vat_id"/>
							</div>
						</div>
					</div>

					<h2 <?php if (!$shippingNeeded) {
						echo 'class="cms_nodisp"';
						} ?>>
						<input class="mw_toggle_container" data-target="order_contact_shipping_container"
							   type="checkbox" name="order_contact[has_shipping_addr]"
							   id="order_contact_has_shipping_addr"
								<?php if (isset($meta['has_shipping_addr']) && $meta['has_shipping_addr']) {
									echo ' checked="checked" ';
								} ?>
						>
						<label
							for="order_contact_has_shipping_addr"><?php echo __('Doručit na jinou než fakturační adresu', 'mwshop'); ?></label>
					</h2>
					<div id="order_contact_shipping_container" <?php
					if (!$shippingNeeded || !(isset($meta['has_shipping_addr']) && $meta['has_shipping_addr'])) {
						echo ' class="cms_nodisp" ';
					}
					?>>
						<div class="ve_form_row ve_form_row_half">
							<label for="order_contact_shipping_firstname"><?php echo __('Jméno', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" name="order_contact[shipping_address][firstname]"
								   value="<?php echo get_array_field($meta, 'shipping_address', 'firstname') ?>"
								   id="order_contact_shipping_firstname"/>
						</div>
						<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
							<label for="order_contact_shipping_surname"><?php echo __('Příjmení', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" name="order_contact[shipping_address][surname]"
								   value="<?php echo get_array_field($meta, 'shipping_address', 'surname') ?>"
								   id="order_contact_shipping_surname"/>
						</div>
						<div class="ve_form_row">
							<label for="order_contact_shipping_phone"><?php echo __('Telefon', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" name="order_contact[shipping_address][phone]"
								   value="<?php echo get_array_field($meta, 'shipping_address', 'phone') ?>"
								   id="order_contact_shipping_phone"/>
						</div>
						<div class="ve_form_row">
							<label for="order_contact_shipping_street"><?php echo __('Ulice a číslo popisné', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" name="order_contact[shipping_address][street]"
								   value="<?php echo get_array_field($meta, 'shipping_address', 'street') ?>"
								   id="order_contact_shipping_street"/>
						</div>
						<div class="ve_form_row ve_form_row_half">
							<label for="order_contact_shipping_city"><?php echo __('Město', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" name="order_contact[shipping_address][city]"
								   value="<?php echo get_array_field($meta, 'shipping_address', 'city') ?>"
								   id="order_contact_shipping_city"/>
						</div>
						<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
							<label for="order_contact_shipping_zip"><?php echo __('PSČ', 'mwshop'); ?></label>
							<input class="ve_form_text" type="text" name="order_contact[shipping_address][zip]"
								   value="<?php echo get_array_field($meta, 'shipping_address', 'zip') ?>"
								   id="order_contact_shipping_zip"/>
						</div>
						<div class="ve_form_row">
							<label for="order_contact_shipping_country"><?php echo __('Země', 'mwshop'); ?></label>
							<?php mws_generate_country_select('order_contact[shipping_address][country]', 'order_contact_shipping_country', 've_form_text', get_array_field($meta, 'shipping_address', 'country')) ?>
						</div>
					</div>

					<h2><label for="order_contact_note"><?php echo __('Poznámka', 'mwshop'); ?></label></h2>
					<div class="ve_form_row">
						<textarea class="ve_form_text" name="order_contact[note]" id="order_contact_note"
								  rows="4"><?php echo get_array_field($meta, 'note') ?></textarea>
					</div>
				</form>
			</div>

			<div class="mws_order_footer">
				<a class="mws_cart_back_but"
				   href="<?php echo MWS()->getUrl_Cart($step - 1); ?>"><?php echo __('Zpět', 'mwshop'); ?></a>
				<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
				   data-target="<?php echo $target; ?>"
				   href="<?php echo MWS()->getUrl_Cart($step + 1); ?>"><?php echo __('Uložit a pokračovat', 'mwshop'); ?></a>
				<div class="cms_clear"></div>
			</div>
		<?php } elseif ($step == MwsOrderStep::Shipping) {
			?>
			<div class="mws_shipping_payment">
				<?php $cart->recount(false, true); ?>
				<form id="mws_order_form">
					<div class="mws_shippings<?= ($cart->isShippingRequired() ? ' mws_shipping_payment_col' : ' cms_nodisp') ?>">
						<h2><span class="point">1</span><?php echo __('Zvolte způsob doručení', 'mwshop'); ?></h2>
						<?php
						$selected = ($shipping = $cart->getShipping()) ? $shipping->getId() : null;
						echo MwsShipping::htmlRadio(
								$cart->getStoredTotalPrice(),
								$cart->getCurrency(),
								$cart->getShippingCountry(),
								$cart->getShippingCountry(),
								'.mws_shop_container',
								$cart->isShippingRequired(),
								$selected,
								'',
								null,
								isset($cart->getShippingInfo()['id']) ? (int) $cart->getShippingInfo()['id'] : null,
								$cart->getShippingInfo()['address'] ?? null,
								$cart->getItems()->getTotalWeight()
						);
						?>
					</div>
					<div class="mws_payments<?= ($cart->isShippingRequired() ? ' mws_shipping_payment_col' : '') ?>"">
						<h2><?= ($cart->isShippingRequired() ? '<span class="point">2</span>' : '') ?><?php echo __('Zvolte způsob platby', 'mwshop'); ?></h2>
						<?php
						try {
							echo MwsPaymentMethod::htmlRadio($cart->getAllowedPaymentMethods(), $cart->getCurrency(), $cart->getInvoiceCountry(), $cart->getPaymentMethod(), '.mws_shop_container');
						} catch (ThePayException $e) {
							$ok = false;
							echo '<div class="mws_error">';
							echo __('Nepodařilo se napojit na platební bránu.', 'mwshop') .
									(MW()->edit_mode ? ('(' . $e->getMessage() . ')') : '');
							echo '</div>';
						}
						?>
					</div>
				</form>
				<div class="mws_shipping_price">
			<?php echo __('Cena dopravy', 'mwshop') . ': ' . htmlPriceSimpleIncluded($cart->getShippingPrice() !== null ? $cart->getShippingPrice()->getPriceVatIncluded() : 0); ?>
				</div>
			</div>
			<div class="mws_order_footer">
				<a class="mws_cart_back_but"
				   href="<?php echo MWS()->getUrl_Cart($step - 1); ?>"><?php echo __('Zpět', 'mwshop'); ?></a>
				<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
				   data-target="<?php echo $target; ?>"
				   href="<?php echo MWS()->getUrl_Cart($step + 1); ?>"><?php echo __('Uložit a pokračovat', 'mwshop'); ?></a>
				<div class="cms_clear"></div>
			</div>
		<?php } elseif ($step == MwsOrderStep::Summarize) { ?>
			<div class="mws_summarize_order">
			<?php
			$error = '';

			$isShippingRequired = $cart->isShippingRequired();
			$contactAsFormInput = ['order_contact' => $cart->getContact()];
			$contactAsFormInput['order_contact']['totalPrice'] = 1; // supress error message of empty order @TODO wtf
			$ok = true;
			$errors = [];
			$res = MwsAjax::validateContactForm($contactAsFormInput, $isShippingRequired, MWS()->isPhoneRequired());
			if (!$res['success']) {
				$ok = false;
				if (isset($res['flashMessage'])) {
					$errors += $res['flashMessage'];
				} elseif (isset($res['errors']) && !empty($res['errors'])) {
					$errors += $res['errors'];
				}
			}

			$res = MwsAjax::validateShippingAndPayment(
				[
					'mws_shipping' => $cart->getShipping()->getId(),
					'mws_shipping_info' => $cart->getShippingInfo(),
					'mws_payment' => $cart->getPaymentMethod()->getId(),
				],
				$cart->getContact()['address'] ?? [],
				$isShippingRequired,
				$cart->getShippingCountry()
			);

			if (!$res['success']) {
				$ok = false;
				if (isset($res['flashMessage'])) {
					$errors += $res['flashMessage'];
				} elseif (isset($res['errors']) && !empty($res['errors'])) {
					$errors += $res['errors'];
				}
			}

			if (!$ok) {
				// Cannot continue because of invalid order.
				echo '<div class="mws_error">';
				if (!empty($errors)) {
					echo __('Vaše objednávka obsahuje následující nesrovnalosti. Vraťte se zpět a potřebné údaje upravte.', 'mwshop');
					echo '<ul>';
					foreach ($errors as $errorLine) {
						echo '<li>' . esc_html(strip_tags($errorLine)) . '</li>';
					}
					echo '</ul>';
				} else {
					echo __('Při kontrole objednávky došlo k blíže neurčené chybě. Zkontrolujte prosím, zda váš košík není prázdný ' .
						'a jsou korektně vyplněné kontaktní a platební údaje.', 'mwshop');
				}
				echo '</div>';
			} else {
				// Recount cart content including price for shipping
				$cart->recount($isShippingRequired, false, true, true);

				if (!$cart->isRecounted()) {
					echo '<div class="mws_error">';
					echo empty($cart->getRecountError())
					? __('Omlouváme se, nepodařilo se spočítat cenu objednávky. Proveďte přepočítání.', 'mwshop')
					: $cart->getRecountError();
					if ($cart->getAvailabilityErrorsCount()) {
						echo ' ' . sprintf(__('Upravte obsah <a href="%s">košíku</a>.', 'mwshop'), MWS()->getUrl_Cart(MwsOrderStep::Cart));
					}
					if (MWS()->edit_mode && !empty($cart->getRecountAdminError())) {
						echo '<small>' . __('Technický popis chyby:', 'mwshop') . ' <span>' . $cart->getRecountAdminError() . '</span></small>';
					}
					echo '</div>';
				}
				?>
					<div class="mws_info_box"><span
							class="info_icon">i</span><?php echo __('Prosíme o pečlivou kontrolu vaší objednávky', 'mwshop'); ?>
					</div>
					<form id="mws_order_form">
						<div class="mws_summarize_cart">

							<h2><?php echo __('Vaše objednávka', 'mwshop'); ?></h2>
				<?php
				echo '<table>';
				foreach ($cart->getItems()->getAll() as $cartItem) {
					$product = $cartItem->getProduct();
					$status = $cartItem->getAvailabilityStatus();
					$product_info = '';
					$price = $cartItem->getStoredShopPrice() ?? $cartItem->getProduct()->getPrice();

					if ($cart->getDiscountCode() !== null && $cart->getDiscountCode()->getType() != 'fixed') {
						$product_info = $product->isDiscountDisabled() ? '<span class="mws_discount_code_info">' . __('Na produkt nelze uplatnit slevový kód', 'mwshop') . '</span>' : '<span class="mws_discount_code_info">' . __('Po uplatnění slevového kódu:', 'mwshop') . ' ' . htmlPriceSimple($cartItem->getStoredTotalPrice()->getPriceVatIncluded(), MwsCurrencyEnum::getSymbol($cart->getStoredTotalPrice()->getCurrency()), false) . '</span>';
						//$price = $cartItem->getStoredShopPrice() !== null ? $cartItem->getStoredShopPrice() : $cartItem->getProduct()->getPrice();
					}

					echo '<tr class="mws_product_id-' . $product->getId() . ' ' . $product->getAvailabilityCSS($status) . '">'
					. '<td><span class="mws_cart_item_count">' . $cartItem->getCount() . '</span>&nbsp;x&nbsp;</td>'
					. '<td class="mws_cart_item_title"><span>' . $product->getName() . '</span>'
					. ($cartItem->getAvailabilityError() ? '<span class="mw_input_error_text">' . $cartItem->getAvailabilityError() . '</span>' : '')
					. $product_info
					. '</td>'
					. '<td class="mws_cart_item_price">'
					. $price->htmlPriceFull('mws_product_price', $cartItem->getCount())
					. '</td>'
					. '</tr>';
				}
				$shipping = $cart->getShipping();
				$paymentMethod = $cart->getPaymentMethod();
				if ($shipping === null || $paymentMethod === null) {
					echo '<tr class="mws_shipping"><td class="mws_input_error" colspan="3">' . __('doprava či platba nebyla určena', 'mwshop') . '</td></tr>';
				} else {
					$shippingTotal = $cart->getShippingPrice();
					echo '<tr class="mws_shipping">';
					echo '<td colspan="2">';
					echo esc_html($shipping->getName());
					if ($paymentMethod->isCod() && $shipping->getCodPrice()->getPriceVatIncluded() > 0) {
						echo ' (' . __('platba při převzetí', 'mwshop') . ')';
					} else {
						echo ' (' . $paymentMethod->getName() . ')';
					}
					if ($shipping->isPacketaShipping()) {
						echo '<div class="mws_shipping_packeta_info">' . __('Výdejní místo:', 'mwshop') . ' ' . ($cart->getShippingInfo()['address'] ?? '') . '</div>';
					}
					echo '</td>';
					echo '<td  class="mws_cart_item_price">';
					echo ($shippingTotal === null ? __('neznámá', 'mwshop') : $shippingTotal->htmlPriceFull('mws_product_price', 1)) . '</td>';
					echo '</tr>';
				}

				$currency = null;
				$price = $cart->getStoredTotalPrice();
				if ($cart->isRecounted() && $price !== null) {
					$currency = MwsCurrencyEnum::getSymbol($price->getCurrency());

					$discountCode = $cart->getDiscountCode();
					if ($discountCode !== null) {
						echo '<tr class="mws_discount_code">'
						. '<td colspan="2">'
						. __('Slevový kód', 'mwshop') . ': ' . esc_html($discountCode->getCode())
						. '</td>'
						. '<td class="mws_cart_item_price">'
						. '<div class="mws_price_vatincluded">-' . $discountCode->printValue($price->getCurrency()) . '</div>'
						. '</tr>';
					}
				}

				if ($cart->isRecounted() && $cart->getRounding() !== null) {
					echo '<tr class="mws_discount_code">'
					. '<td colspan="2">'
					. __('Zaokrouhlení', 'mwshop')
					. '</td>'
					. '<td class="mws_cart_item_price">'
					. '<div class="mws_price_vatincluded">' . htmlPriceSimpleIncluded($cart->getRounding()->getPriceVatIncluded(), $currency) . '</div>'
					. '</tr>';
				}

				echo '<tr class="mws_cart_items_footer">'
				. '<td colspan="2">' . __('Celková cena', 'mwshop') . '</td>'
				. '<td class="mws_cart_item_price">';
				if ($currency !== null) {
					echo htmlPriceSimpleIncluded($price->getPriceVatIncluded(), $currency);
					echo htmlPriceSimpleExcluded($price->getPriceVatExcluded(), $currency);
				} else {
					echo __('(nutno přepočítat)', 'mwshop');
				}
				echo '</td>'
				. '</tr>';

				try {
					if ($cart->shouldApplyReverseCharge()) {
						echo '<tr>';
						echo '<td class="mws_cart_summary_reverse_charge" colspan="3">' . __('Daň odvede zákazník', 'mwshop') . '</td>';
						echo '</tr>';
					}
				} catch (ReverseChargeApplicationException $e) {
					// ignore
				}
				echo '</table>';
				?>
						</div>
				<?php
				// ----- CONTACT ----
				$invoiceContact = $cart->getInvoiceContact();
				?>
						<div class="mws_summarize_client">
							<div class="mws_summarize_invoice">
								<?php
								echo '<h2>' . esc_html(__('Kontakt', 'mwshop')) . '</h2>';
								echo '<div>' . __('Email', 'mwshop') . ': ' . esc_html($invoiceContact->getEmail()) . '</div>';
								if ($phone = $invoiceContact->getPhone()) {
									echo '<div>' . __('Telefon', 'mwshop') . ': ' . $phone . '</div>';
								}
								if ($cart->wantInvoice()) {
									echo '<h2>' . __('Fakturační údaje', 'mwshop') . '</h2>';
									if ($company = $invoiceContact->getCompany()) {
										echo '<div class="mws_company_info">' . $company->format(true) . '</div>';
									}
									echo $invoiceContact->getPerson()->format(true);
									echo $invoiceContact->getAddress()->format(true);
								} else {
									echo '<h2>' . __('Zjednodušený doklad', 'mwshop') . '</h2>';
									$country = $invoiceContact->getAddress()->getCountry();
									// Check if still possible to use simplified invoice
									$useSimplifiedInvoice = $cart->useSimplifiedInvoice();
									if (!$useSimplifiedInvoice) {
										$error = sprintf(
											__('Použití zjednodušeného dokladu není povoleno. Je potřeba <a href="%s">vyplnit fakturační údaje</a>.', 'mwshop'),
											MWS()->getUrl_Cart(MwsOrderStep::Contact)
										);
									} elseif (!($country === MwsCountry::CZ && $cart->getStoredTotalPrice() && $cart->getStoredTotalPrice()->getPriceVatIncluded() <= 10000)) {
										$error = sprintf(
											__('Použití zjendodušeného dokladu není přípustné pro vaši objednávku. <br />Je potřeba <a href="%s">vyplnit fakturační údaje</a>', 'mwshop'),
											MWS()->getUrl_Cart(MwsOrderStep::Contact)
										);
									}
									if ($error) {
										echo '<div class="mws_error">' . $error . '</div>';
									}

									echo '<div>' . esc_html(__('Země:', 'mwshop') . ' ' . MwsCountry::getCaption($country)) . '</div>';
								}
								?>
							</div>
							<div class="mws_summarize_shipping">
								<?php
								$shippingContact = $cart->getShippingContact();
								if ($shippingContact) {
									echo '<h2>' . __('Dodací adresa', 'mwshop') . '</h2>';
									echo $shippingContact->format(true, true);
								} elseif ($cart->isShippingRequired()) {
									if (!$cart->wantInvoice()) {
										$error = sprintf(__(
											'Objednáváte zboží, které vyžaduje fyzické doručení. <br />' .
												'Je potřeba <a href="%s">vyplnit fakturační anebo doručovací adresu</a>.',
											'mwshop'
										), MWS()->getUrl_Cart(MwsOrderStep::Contact));
										echo '<div class="mws_error">' . $error . '</div>';
									}
								}
								?>
							</div>
							<?php
							if ($note = $cart->getNote()) {
								echo '<div class="mws_summarize_note">'
								. '<h2>' . __('Poznámka', 'mwshop') . '</h2>'
								. '<div class="mws_order_note">'
								. wpautop(esc_html($note))
								. '</div></div>';
							}
							?>
						</div>
						<div class="cms_clear"></div>

						<ul class="mws_order_purposes">
						<?php
						$termsUrl = MWS()->getUrl_TermsAndConditions();

						//terms and conditions
						if (MWS()->isTermsAllowed()) {
							if (!empty($termsUrl)) {
								echo '<li>' . MWS()->renderTerms() . '</li>';
							} else {
								echo '<li>' . MWS()->printMissingTermsError() . '</li>';
								$error = true;
							}
						}

						$heureka = new MwHeureka();
						echo '<li>' . $heureka->writeDisagree() . '</li>';

						if (!$error) {
							echo MWS()->renderPurposes();
						}
						?>
						</ul>

					</form>
			<?php } ?>
			</div>
			<div class="mws_order_footer">
				<a class="mws_cart_back_but"
				   href="<?php echo MWS()->getUrl_Cart($step - 1); ?>"><?php echo __('Zpět', 'mwshop'); ?></a>
				<?php

				if ($error || !$ok) {
					// No continue button
				} elseif ($cartItemsCount > 0) {
					if ($cart->isRecounted()) {
						echo '<a class="mws_cart_continue_but mws_cart_order_but mws_cart_but eshop_color_background"'
							. ' data-target="' . $target . '"'
							. ' href="' . MWS()->getUrl_Cart($step + 1) . '">'
							. __('Závazně objednat', 'mwshop')
							. '<small>' . __('S povinností platby', 'mwshop') . '</small>'
							. '</a>';
					} else {
						echo '<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
							data-target="' . $target . '"
							href="' . MWS()->getUrl_Cart($step) . '">'
							. __('Přepočítat', 'mwshop')
							. '</a>';
					}
				} ?>
				<div class="cms_clear"></div>
			</div>
			<?php
		} elseif ($step == MwsOrderStep::ThankYou) {
			mwshoplog('request=' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG, 'order');
			//Get associated order
			$gwId = $_REQUEST['gw'] ?? '';
			$gw = MWS()->gateways()->getById($gwId);
			if ($gw === null) {
				$gw = MWS()->gateways()->getDefault();
			}
			$order = $gw->sharedInstance()->getOrderFromThankYou();
			if ($order === null) {
				// Order is not present or could not be identified.
				?>
				<div class="mws_order_finished">
					<h2><?php echo __('Při zpracování vaší objednávky došlo k chybě. Vraťte se prosím o krok zpět a pokus zopakujte. Omlouváme se za nepříjemnosti.', 'mwshop'); ?></h2>
					<p><?php echo sprintf(__('V případě trvajících potíží můžete %s.', 'mwshop'), '<a href="mailto:' . get_option('admin_email') . '">' . __('kontaktovat naši podporu', 'mwshop') . '</a>'); ?></p>
				</div>
			<?php } else {
				?>
				<div class="mws_order_finished">
					<h2><?php echo $success ?? false
					? __('Děkujeme za vaši objednávku, váš nákup byl v pořádku uložen a odeslán ke zpracování.', 'mwshop')
					: __('Vaši objednávku jsme přijali ke zpracování v pořádku, ale během úhrady objednávky došlo k chybě. ', 'mwshop');
					?>
					</h2>

					<p><?php
					echo __('Na váš e-mail bylo zasláno potvrzení s upřesňujícími informacemi.', 'mwshop');
					// Force to check payment status
					if (!$order->isPaid()) {
						$gwLive = $order->getGateLive();
						if ($gwLive && $gwLive->isPaid()) {
							$order->setPaid();
							$paidAt = (new \DateTimeImmutable())->setTimestamp($gwLive->getPaidOn());
							$order->setPaidAt($paidAt);
							$order->save();
						}
					}
					if (!$order->isPaid()) {
						// Direct pay URL formatting
						$payUrl = $order->getDirectPaymentUrl();
						if (!empty($payUrl)) {
							echo '<br />';
							if (($success ?? false)) {
								echo __('Pokud jste tak ještě neučinili, můžete cenu uhradit online', 'mwshop');
							} else {
								$order->sendPaymentFailedNotification(); // Duplicate to single-mworder (can be removed)
								echo __('Objednávku můžete uhradit online ', 'mwshop');
							}
							echo ' <a href="' . $payUrl . '" target="_blank">' . __('zde', 'mwshop') . '</a>.';
						}
					} else {
						echo '<br />' . __('Platbu za objednávku jsme přijali v pořádku. Děkujeme.', 'mwshop');
					}
					?>
					</p>


					<div class="mws_info_box">
						<span
							class="info_icon">i</span><?php echo __('Číslo vaší objednávky:', 'mwshop') . ' ' . $order->getNumber(); ?>
						<br/>
					</div>
					<div class="mws_order_finished_info entry_content">
				<?php
				if (!isset(MWS()->visual_setting['thanks_content'])) {
					MWS()->visual_setting['thanks_content'] = '';
				}
				$args = [
					'key' => 'thanks_content',
					'option' => MWS_OPTION_SHOP_APPEARANCE,
				];
				echo $vePage->display->weditor->weditor_content(MWS()->visual_setting['thanks_content'], $args);
				?>
					</div>
				</div>
				<?php if ($order->getSource() !== null && $order->getSource()->getType() === MwsOrderSourceType::Eshop) : ?>
					<div class="mws_order_finished_footer title_element_container">
						<a class="mws_cart_but eshop_color_background"
						   href="<?php echo MWS()->getUrl_Home(); ?>"><?php echo __('Vrátit se zpět do obchodu', 'mwshop'); ?></a>
					</div>
				<?php endif; ?>
			<?php }
		} ?>
	</div>
</div>

<?php

if ($step == MwsOrderStep::Cart) {
	// content after cart
	if (!isset(MWS()->visual_setting['cart_content'])) {
		MWS()->visual_setting['cart_content'] = '';
	}
	$args = [
		'key' => 'cart_content',
		'option' => MWS_OPTION_SHOP_APPEARANCE,
	];
	echo $vePage->display->weditor->weditor_content(MWS()->visual_setting['cart_content'], $args);
}

get_footer('mwshop');

?>
