<?php
/**
 * AJAX call handlers.
 * User: kuba
 * Date: 14.03.16
 * Time: 18:11
 */

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mioweb\Shop\AddressValidator;
use Mioweb\Shop\FormProcessor;
use Nette\Utils\Helpers;

class MwsAjax
{

	/**
	 * Removes item from the cart.
	 *
	 * @param int $_REQUEST ['product'] ID of the product that should be removed.
	 *
	 * Sends JSON Fields "bool success" whether updated cart does not contain product;
	 * "int productId" id of removed product; "bool realyRemoved" it the item was really removed
	 */
	public static function cartRemoveItem()
	{
		$productId = $_REQUEST['product'] ?? null;
		$item = MWS()->getCart()->getItems()->getOneById($productId);
		if ($item === null) {
			// Removed product is not in the cart.
			$res = json_encode([
				'productId' => $productId,
				'result' => true,
				'realyRemoved' => false,
				'message' => __('Košík neobsahuje odebírané zboží.', 'mwshop'),
			]);
		} else {
			// Removed product is in the cart.
			$removed = false;
			try {
				$cart = MWS()->getCart();
				$removed = $cart->getItems()->remove($item);
				$cart->recount(false, true);

				if ($cart->getDiscountCode() !== null && ($cart->getItems()->isEmpty() || $cart->getDiscountCode()->isValid($cart) < 1)) {
					$cart->removeDiscountCode();
					$cart->recount(false, true);
				}

				//Render new cart content
				$newContent = '<div class="mws_cart_container ' . ($cart->getItems()->isEmpty() ? 'mws_cart_empty' : '') . '"
				<form id="mws_order_form">
					' . mwsRenderParts('cart', 'loop', true) . '
				</form></div>';

				$html = Helpers::capture(function () use ($item, $cart) {
					do_action('mw_product_removed_from_cart', $item, $cart);
				});

				$res = json_encode([
					'productId' => $productId,
					'result' => true,
					'realyRemoved' => $removed,
					'cart_count' => MWS()->getCart()->getItems()->count(),
					'message' => __('Zboží bylo odebráno z košíku', 'mwshop'),
					'newCart' => $newContent,
					'html' => $html,
				]);
			} catch (Exception $e) {
				$res = json_encode([
					'productId' => $productId,
					'result' => false,
					'realyRemoved' => $removed,
					'message' => __('Při odebírání zboží z košíku došlo k chybě.', 'mwshop') . "\n" . $e->getMessage(),
				]);
			}
		}
		wp_send_json($res);
	}

	/**
	 * Removes item from the cart.
	 *
	 * @param int $_REQUEST ['product'] ID of the product that should be removed.
	 *
	 * Sends JSON Fields "bool success" whether updated cart does not contain product;
	 * "int productId" id of removed product; "bool realyRemoved" it the item was really removed
	 */
	public static function cartRemoveDiscountCode()
	{
		$cart = MWS()->getCart();
		$discountCode = $cart->getDiscountCode();
		if (!$discountCode) {
			// Removed product is not in the cart.
			$res = json_encode([
				'result' => true,
				'realyRemoved' => false,
				'message' => __('Košík neobsahuje žádnou slevu.', 'mwshop'),
			]);
		} else {
			$removed = false;
			try {
				$cart->removeDiscountCode();
				$cart->recount(false, true);

				$res = json_encode([
					'result' => true,
					'realyRemoved' => $removed,
					'message' => __('Sleva byla odebrána z košíku', 'mwshop'),
				]);
			} catch (Exception $e) {
				$res = json_encode([
					'result' => false,
					'realyRemoved' => $removed,
					'message' => __('Při odebírání slevy z košíku došlo k chybě.', 'mwshop') . "\n" . $e->getMessage(),
				]);
			}
		}
		wp_send_json($res);
	}

	public static function cartAddItem()
	{
		$cart = MWS()->getCart();

		$productId = isset($_REQUEST['product']) ? (int) $_REQUEST['product'] : null;
		$count = isset($_REQUEST['count']) ? (int) $_REQUEST['count'] : 1;


		$res = [
			'cart_count' => $cart->getItems()->count(),
		];

		//Check product existence.
		$product = MwsProduct::getOneById($productId);
		if (!$product instanceof MwsProduct) {
			$shopUrl = MWS()->getUrl_Home();
			$res['content'] = '<div class="mws_colorbox_message">'
			. __('Vybraný produkt není v našem obchodě k dispozici.', 'mwshop')
			. ($shopUrl ? ' ' . sprintf(__('Přejete si zobrazit <a href="%s"> naši nabídku?', 'mwshop'), $shopUrl) : '')
			. '</div>';
			$res['added'] = 0;

			wp_send_json(json_encode($res));
			wp_die();
		}

		// Product exists
		$isQuick = isset($_REQUEST['isQuick']) ? (bool) filter_var($_REQUEST['isQuick'], FILTER_VALIDATE_BOOLEAN) : false;
		if (!$isQuick) {
			// ORDINARY add to cart
			MWS()->current()->setProduct($product);
			$target = MWS()->edit_mode ? 'parent' : '';

			$buttons = '<div class="mws_add_to_cart_footer">
					<a class="mws_cart_back_but mws_close_cart_box" data-target="' . $target . '" href="#">' . __('Zavřít', 'mwshop') . '</a>
					<div class="cms_clear"></div>
				</div>';
			$title = __('Nepodařilo se vložit do košíku', 'mwshop');
			$content = '';
			$error = '';

			$isElectronic = MwsProductType::isElectronic($product->getType());

			if ($product->canBuyCount($count)) {
				// Product is available in necessary amount
				$added = $isElectronic && $cart->getItems()->getOneById($productId) !== null ? 0 : $cart->addItem($product, $count); // @TODO item is added always
				//TODO Make output pretty.
				if ($added > 0) {
					MWS()->current()->setCartItem($cart->getItems()->getOneById($productId));

					$res = [
						'cart_count' => $cart->getItems()->count(),
						'added' => $productId,
						'added_hover' => mwsRenderParts('cart', 'hover-items', true),
					];

					$title = sprintf(_n(
						'Zboží jsme vložili do košíku',
						'Zboží jsme vložili do košíku v počtu %d kusů.',
						$added,
						'mwshop'
					), $added);
					$content = mwsRenderParts('cart', 'added', true);
					$buttons = '<a class="mws_cart_back_but mws_close_cart_box" href="#">' . __('Pokračovat v nákupu', 'mwshop') . '</a>
		  <a class="mws_cart_but eshop_color_background" href="' . MWS()->getUrl_Cart() . '">'
					. __('Přejít do košíku', 'mwshop') . '</a>';

					$content .= Helpers::capture(function () use ($product, $count, $cart) {
						do_action('mw_product_added_to_cart', $product, $count, $cart);
					});
				} elseif ($added === 0) {
					// Although product is available, something stopped from adding required amount of product into the cart.
					MWS()->current()->setShowAvailabilityInAdded(true);
					$content .= mwsRenderParts('cart', 'added', true);

					if (!$isElectronic) {
						$error .= '<div class="mws_error">'
							. __('Do košíku jsme nic nevložili. Zkuste obnovit stránku a pokus opakovat.', 'mwshop')
							. '</div>';
					} else {
						$title = __('Tento produkt je již v košíku');
						$buttons = '<a class="mws_cart_back_but mws_close_cart_box" href="#">' . __('Pokračovat v nákupu', 'mwshop') . '</a>
		  							<a class="mws_cart_but eshop_color_background" href="' . MWS()->getUrl_Cart() . '">' . __('Přejít do košíku', 'mwshop') . '</a>';
					}
				} else {
					// Unknown error
					MWS()->current()->setShowAvailabilityInAdded(true);
					$content .= mwsRenderParts('cart', 'added', true);
					$error .= '<div class="mws_error">'
					. __('Při vkládání zboží do košíku došlo k chybě. Zkuste obnovit stránku a pokus opakovat.', 'mwshop')
					. '</div>';
				}
			} else {
				// Cannot add to cart required amount
				MWS()->current()->setShowAvailabilityInAdded(true);
				$content .= mwsRenderParts('cart', 'added', true);
				$error .= '<div class="mws_error">'
				. __('Do košíku jsme nic nevložili, zboží není v požadovaném množství dostupné. ' .
						'Zkuste obnovit stránku a pokus opakovat později.', 'mwshop')
				. '</div>';
			}
			$availability = $product->getAvailabilityStatus();
			$res['content'] = '
				<div class="mws_add_to_cart_box">
					<div class="mws_add_to_cart_header"> ' . $title . '
							<a href="#" class="mws_close_cart_box">' . MWS()->getTemplateIcon('close') . '</a>
					</div>
					' . $error . '
					<div class="mws_add_to_cart_content ' . $product->getAvailabilityCSS($availability) . '">
							' . $content . '
					</div>
					<div class="mws_add_to_cart_footer">
						' . $buttons . '
							<div class="cms_clear"></div>
					</div>
				</div>';
		} else {
			$availability = $product->getAvailabilityStatus();
			$allowSimplified = isset($_POST['allowSimplified']) && (bool) filter_var($_POST['allowSimplified'], FILTER_VALIDATE_BOOLEAN);
			$allowDiscount = isset($_POST['allowDiscount']) ? (bool) filter_var($_POST['allowDiscount'], FILTER_VALIDATE_BOOLEAN) : false;
			$thanksPage = $_POST['thanksPage'] ?? null;
			$res['content'] = '
				<div class="mws_add_to_cart_box">
					<div class="mws_add_to_cart_header">
						' . __('Koupit', 'mwshop') . '
						<a href="#" class="mws_close_cart_box">' . MWS()->getTemplateIcon('close') . '</a>
					</div>

					' . MWS()->renderQuickBuyForm('mws_quick_order', $product, $count, $allowSimplified, $allowDiscount, $thanksPage) . '

				</div>';
		}
		wp_send_json(json_encode($res));
		wp_die();
	}

	public static function orderStep()
	{
		mwshoplog(__METHOD__, MWLL_DEBUG);

		//String constants
		$text_IntegerExpected = __('Zadejte celé číslo', 'mwshop');

		$step = isset($_REQUEST['curStep']) ? MwsOrderStep::checkedValue((int) $_REQUEST['curStep'], MwsOrderStep::Cart) : MwsOrderStep::Cart;
		$subaction = $_REQUEST['subaction'] ?? '';
		$formData = self::getFormValues();
		$nextUrl = $_REQUEST['nextUrl'] ?? MWS()->getUrl_Cart($step + 1);
		$cart = MWS()->getCart();

		$res = [];
		$errors = [];
		$ok = false;

		// Validate if step is allowed
		$res['deleteErrors'] = true;
		$continue = $cart->areFulfilledPriorSteps($step);
		if ($continue) {
			switch ($step) {
				case MwsOrderStep::Cart:
					$ok = !$cart->isEmpty();
					if ($ok) {
						$deleteProductIds = [];
						//Update modifications of count of items
						foreach ($formData['count'] ?? [] as $productId => $newCountRaw) {
							if (!ctype_digit((string) $newCountRaw)) {
								//Not an integer number as input.
								$errors['count[' . $productId . ']'] = $text_IntegerExpected;
								$ok = false;
							} else {
								$newCount = (int) $newCountRaw;
								/** @var MwsCartItem $item */
								$item = $cart->getItems()->getOneById($productId);
								if ($item) {
									if ($newCount > 0) {
										$item->setCount($newCount);
										if (!$item->checkAvailability()) {
											$errors['count[' . $productId . ']'] = $item->getAvailabilityError();
											$ok = false;
										}
									} else {
										$errors['count[' . $productId . ']'] = __('Zadejte celé kladné číslo.', 'mwshop');
										$ok = false;
										//$cart->getItems()->remove($productId);
										//$deleteProductIds[] = $productId;
									}
								} else {
									// Line should be be removed
									$deleteProductIds[] = $productId;
								}
							}
						} // for-end
						$res['deleteProductIds'] = $deleteProductIds;

						if (!$subaction) {
							$res = static::validateCart($cart);
							$res['deleteErrors'] = true;
							$ok = $res['success'];
							if (!$ok) {
								$res['flashMessage'] = '<div class="mws_error">';
								foreach ($res['errors'] as $error) {
									$res['flashMessage'] .= $error;
								}
								$res['flashMessage'] .= '</div>';
								$errors = array_merge($errors, $res['errors']);
							}
						}

						if (array_key_exists('discount_code', $formData)) {
							if ($formData['discount_code']) {
								$discountCode = MwsDiscountCode::getOneByCode($formData['discount_code']);

								$validDisCode = $discountCode === null ? 0 : $discountCode->isValid(MWS()->getCart());

								if ($discountCode === null) {
									$errors['discount_code'] = __('Zadaný slevový kód není platný.', 'mwshop');
									$ok = false;
								} elseif ($validDisCode < 1) {
									$errors['discount_code'] = $discountCode->getValidationError($validDisCode, $cart);
									$ok = false;

									if ($subaction == 'recount') {
										//$errors['discount_code'] = __('Zadaný slevový kód nelze použít.', 'mwshop');
										$cart->removeDiscountCode();
										$ok = true;
									}
								} else {
									$cart->setDiscountCode($discountCode);
								}
							} else {
								$cart->setDiscountCode(null);
							}
						}
					}

					break;
				case MwsOrderStep::Contact:
					$res = static::validateContactForm($formData, $cart->isShippingRequired(), MWS()->isPhoneRequired());
					$res['deleteErrors'] = true;
					$ok = $res['success'];
					if (!$ok) {
						$errors = array_merge($errors, $res['errors']);
					}

					//Note
					//              if(empty($formData['order_contact']['note'])) {
					//                  $errors['order_contact[note]'] = $text_CanNotBeEmpty;
					//                  $ok = false;
					//              }

					$cart->setContact($formData['order_contact']);

					break;
				case MwsOrderStep::Shipping:
					$res = static::validateShippingAndPayment($formData, $cart->getContact()['address'] ?? [], $cart->isShippingRequired(), $cart->getShippingCountry());
					$res['deleteErrors'] = true;
					$ok = $res['success'];
					/** @var MwsShipping $shipping */
					$shipping = null;
					if (!$ok) {
						$errors = array_merge($errors, $res['errors']);
					}
					if (isset($res['shipping'])) {
						$shipping = $res['shipping'];
					}
					if (isset($res['shippingInfo'])) {
						$shippingInfo = $res['shippingInfo'];
					}
					if (isset($res['paymentMethod'])) {
						/** @var MwsPaymentMethod $paymentMethod */
						$paymentMethod = $res['paymentMethod'];
					}

					//Update price
					if ($ok && isset($paymentMethod) && isset($shipping)) {
						// @TODO exclude shippjngPrice from storedTotalPrice ??
						$cart->setShippingPrice($shipping->getTotalPrice($paymentMethod, $cart->getStoredTotalPrice())->asCurrency($cart->getCurrency()));
					} else {
						$cart->setShippingPrice(null);
					}

					$cart->setShipping($shipping ?? null);
					$cart->setShippingInfo($shippingInfo ?? []);
					$cart->setPaymentMethod($paymentMethod ?? null);

					break;
				case MwsOrderStep::Summarize:
					$ok = false;

					// Validate terms and conditions
					$res = static::validateTermsAndConditions($formData);
					if (!$res['success']) {
						$errors = array_merge($errors, $res['errors']);
					}
					$cart->setHeurekaDisagree(isset($formData['heureka_disagree']));
					$cart->setPurposes($formData['purposes'] ?? []);
					$cart->setSource(new MwsOrderSource(MwsOrderSourceType::Eshop));

					$res = static::validateCart($cart);
					if (!$res['success']) {
						$res['flashMessage'] = '<div class="mws_error">';
						foreach ($res['errors'] as $error) {
							$res['flashMessage'] .= $error;
						}
						$res['flashMessage'] .= '</div>';
						$errors = array_merge($errors, $res['errors']);
					} else {
						$res = static::validateShippingAndPayment([
							'mws_shipping' => $cart->getShipping()->getId(),
							'mws_shipping_info' => $cart->getShippingInfo(),
							'mws_payment' => $cart->getPaymentMethod()->getId(),
						], $cart->getContact()['address'] ?? [], $cart->isShippingRequired(), $cart->getShippingCountry());
						if (!$res['success']) {
							$res['flashMessage'] = '<div class="mws_error">';
							foreach ($res['errors'] as $error) {
								$res['flashMessage'] .= $error;
							}
							$res['flashMessage'] .= '</div>';
							$errors = array_merge($errors, $res['errors']);
						}
					}

					if (empty($errors)) {
						$ok = true;
					}
					// Clear all possible errors from UI.
					$res['deleteErrors'] = true;

					// Input of summary is OK
					if ($ok) {
						$ok = false;
						// If prices are fixed within cart...
						if ($cart->isRecounted()) {
							//...make an order
							$res = MWS()->gateways()->getDefault()->sharedInstance()->makeOrder($cart);
							mwshoplog('gw_result=' . json_encode($res, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG, 'order');
							if (isset($res['success']) && $res['success']) {
								// Order created
								mwshoplog(sprintf(
									__('Vytvořena nová objednávka [%s], v.s. [%s].', 'mwshop'),
									isset($res['orderId']) ? (string) $res['orderId'] : '-',
									isset($res['orderNum']) ? (string) $res['orderNum'] : '-'
								), MWLL_INFO, 'order');
								$closeAndClear = true;
								$nextUrl = isset($res['nextUrl']) && !empty($res['nextUrl'])
								? $res['nextUrl']
								: add_query_arg(['success' => true], MWS()->getUrl_Cart(MwsOrderStep::ThankYou));

								$ok = true;

								// Update statistics
								$cart->incOrderedCount();
							} else {
								// Creating order failed
								$nextUrl = add_query_arg(['success' => false], MWS()->getUrl_Cart(MwsOrderStep::Summarize));
								$res['flashMessage'] = '<div class="mws_error">'
								. ($res['message'] ?? __('Objednávku se nepodařilo odeslat. Zopakujte objednání.', 'mwshop')
									)
								. '</div>';
								unset($res['message']); // just to have clean output
								/** @var MwsCartItem $cartItem */
								foreach ($cart->getItems()->getAll() as $cartItem) {
									if ($cartItem->getAvailabilityError()) {
										$errors[$cartItem->getProduct()->getId()] = $cartItem->getAvailabilityError();
									}
								}
								$res['deleteErrors'] = true;
							}
						} elseif ($cart->getAvailabilityErrorsCount()) {
							// Not enough items on stock
							$res['deleteErrors'] = false;
							$res['shouldReload'] = true;
						} else {
							// force recount of the cart upon page reload
							$res['shouldReload'] = true;
						}
					}

					break;
			}

			$cart->setFulfilledStep($step, $ok);
			$fulfillment = $cart->getStepsFulfillment();
			$res['stepsFulfilled'] = $fulfillment;

			if (isset($closeAndClear) && $closeAndClear) {
				// Special handling for the last step.
				$cart->clearAll();
			}

			unset($res['purposes']);
			$res['success'] = $ok;
			// On failures disable forwarding redirect. Then error are displayed by JS at client side.
			if ($ok) {
				$res['nextUrl'] = $nextUrl;
				wp_send_json_success($res);
			} else {
				unset($res['nextUrl']);
				if (!empty($errors)) {
					$res['errors'] = $errors;
				}
				wp_send_json_error($res);
			}
		} else {
			//Incorrect usage. Redirect back first step.
			$nextUrl = MWS()->getUrl_Cart(MwsOrderStep::Cart);
			$res['nextUrl'] = $nextUrl;
			wp_send_json_success($res);
		}

		// Saving of session is done at wp_die automatically.

		wp_die();
	}

	/** Process callback hooks from gateway. Registered as non-admin AJAX call. */
	public static function gateCallback()
	{
		mwshoplog(__METHOD__ . 'REQUEST=' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG);
		$res = false;

		//Which gateway?
		$gwId = $_REQUEST['gw'] ?? '';
		$gws = MWS()->gateways();
		$gw = $gws->getById($gwId);
		if (!$gw) {
			mwshoplog("Paygate callback for invalid gate id [$gwId] received.", MWLL_WARNING);
			wp_send_json_error(['message' => "Gateway with id=[$gwId] is no recognized."]);
			$gw = $gws->getDefault();
		}
		if (!$gw) {
			mwshoplog('No default paygate available to process paygate callback.', MWLL_ERROR);
			mwshoplog('Received callback data: ' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_ERROR);
			wp_send_json_error(['message' => 'No gateway found.']);
		}

		//Operation?
		$order = null;
		$operation = $_REQUEST['operation'] ?? '';
		try {
			switch ($operation) {
				case 'paid':
					$order = $gw->sharedInstance()->orderPaid();
					if ($order) {
						mwshoplog("Order [{$order->getId()}/{$order->getNumber()}] marked as PAID by paygate callback.", MWLL_INFO);
					}

					break;
				case 'cancelled':
					$order = $gw->sharedInstance()->orderCancelled();
					if ($order) {
						mwshoplog("Order [{$order->getId()}/{$order->getNumber()}] marked as CANCELLED by paygate callback.", MWLL_INFO);
					}

					break;
				default:
					mwshoplog("Unsupported operation [$operation] paygate callback.", MWLL_WARNING);

					throw new Exception('Unsupported operation [' . $operation . '].');
			}
		} catch (Exception $e) {
			mwshoplog('Paygate callback failed. Received callback data: ' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_ERROR);
			wp_send_json_error(['message' => 'Callback failed. ' . $e->getMessage()]);
		}

		if ($order) {
			wp_send_json_success();
		} else {
			mwshoplog('Paygate callback failed for unknown reason. Received callback data: ' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_ERROR);
		}
		wp_send_json_error(['message' => 'Some error occured during callback processing. Look into logs for further details.']);
	}

	/**
	 * Validate cart.
	 */
	private static function validateCart(MwsCart $cart): array
	{
		$res = [
			'success' => true,
		];

		$cartTotalPrice = $cart->getStoredTotalPrice();
		$minOrderPrice = MWS()->getMinOrderPrice();

		if ($minOrderPrice !== null && $cartTotalPrice !== null && ($missingPrice = $minOrderPrice->sub($cartTotalPrice))->getPriceVatIncluded() > 0) {
			$res['errors'][] = sprintf(__('<strong>Minimální výše objednávky je %s</strong>. Je nutné do košíku přidat ještě zboží minimálně za %s.', 'mwshop'), $minOrderPrice->asCurrency($cartTotalPrice->getCurrency())->htmlPriceVatIncluded(), $missingPrice->asCurrency($cartTotalPrice->getCurrency())->htmlPriceVatIncluded());
			$res['success'] = false;
		}

		return $res;
	}

	/**
	 * @TODO refactor
	 * Validate contact information from the input form.
	 * @param array $formData Data from input elements.
	 * @param bool $shippingRequired Whether shipping is required. In that case presence of at least one address will be checked.
	 * @return array Returns array with 'success' status (bool). In case of failure the field 'errors' contains list of
	 *                                       errors indexed by name of fields with error with error text as value.
	 */
	public static function validateContactForm(
		array &$formData,
		bool $shippingRequired = false,
		bool $phoneRequired = false,
		bool $fullNameRequired = false,
		bool $canSimplifiedInvoice = false
	)
	{
		//String constants
		$text_CanNotBeEmpty = __('Pole je vyžadované', 'mwshop');
		$text_CountryNotSupported = __('Zvolte zemi dostupnou v nabídce.', 'mwshop');

		$countries = MWS()->getShippingCountries();
		$gateway = MWS()->gateways()->getDefault();

//		$canSimplifiedInvoice = $form !== null ? $form->isSimplifiedAllowed() : false;

		$ok = true;
		$errors = [];
		$flashMsg = [];

		//Total price
		$totalPrice = isset($formData['order_contact']['totalPrice']) ? (float) $formData['order_contact']['totalPrice'] : 0;
		if ($totalPrice == 0) {
			$flashMsg[] = '<div class="mws_error">' . __('Cena objednávky nemůže být nulová.', 'mwshop') . '</div>';
			$ok = false;
		}

		//Email
		if (empty($formData['order_contact']['email'])) {
			$errors['order_contact[email]'] = $text_CanNotBeEmpty;
			$ok = false;
		} elseif (!filter_var($formData['order_contact']['email'], FILTER_VALIDATE_EMAIL)) {
			$errors['order_contact[email]'] = __('Zadaná hodnota není platný email.', 'mwshop');
			$ok = false;
		}

		// phone is required
		if ($phoneRequired && empty($formData['order_contact']['address']['phone'])) {
			$errors['order_contact[address][phone]'] = $text_CanNotBeEmpty;
			$ok = false;
		}

		// Full name is required
		if ($fullNameRequired) {
			if (empty($formData['order_contact']['address']['firstname'])) {
				$errors['order_contact[address][firstname]'] = $text_CanNotBeEmpty;
				$ok = false;
			}
			if (empty($formData['order_contact']['address']['surname'])) {
				$errors['order_contact[address][surname]'] = $text_CanNotBeEmpty;
				$ok = false;
			}
		}

		//Country
		$country = '';
		if (empty($formData['order_contact']['address']['country'])) {
			$errors['order_contact[address][country]'] = $text_CanNotBeEmpty;
			$ok = false;
		} elseif (!in_array($formData['order_contact']['address']['country'], $countries)) {
			$errors['order_contact[address][country]'] = $text_CountryNotSupported;
			$ok = false;
		} else {
			$country = $formData['order_contact']['address']['country'];
		}

		$fullContactRequired = !($canSimplifiedInvoice && $country === 'CZ' && $totalPrice <= 10000);
		//Want invoice
		if ($fullContactRequired && !(isset($formData['order_contact']['want_invoice']) && $formData['order_contact']['want_invoice'])) {
			$flashMsg[] = '<div class="mws_error">'
			. __('Vyplňte prosím fakturační údaje.', 'mwshop')
			. ($canSimplifiedInvoice ? '<br /><br /> ' . __('Zjednodušený daňový doklad je možné vystavit pouze zákazníkům z ČR při objednávce do 10000Kč včetně DPH.', 'mwshop') : '')
			. '</div>';
			$ok = false;
		}

		$invoiceAddressWillBeFilled = false;
		if ($fullContactRequired
			|| (isset($formData['order_contact']['want_invoice']) && $formData['order_contact']['want_invoice'])
		) {
			$invoiceAddressWillBeFilled = true;
			//Primary address
			$formData['order_contact']['want_invoice'] = true;
			if (isset($formData['order_contact']['want_invoice']) && filter_var($formData['order_contact']['want_invoice'], FILTER_VALIDATE_BOOLEAN)) {
				if (empty($formData['order_contact']['address']['firstname'])) {
					$errors['order_contact[address][firstname]'] = $text_CanNotBeEmpty;
					$ok = false;
				}

				if (empty($formData['order_contact']['address']['surname'])) {
					$errors['order_contact[address][surname]'] = $text_CanNotBeEmpty;
					$ok = false;
				}

				$phoneUtil = PhoneNumberUtil::getInstance();
				//Phone in address
				$phoneError = __('Zadané telefonní číslo není ve vaší fakturační zemi platné. V případě zahraničních čísel uveďte před číslem i mezinárodní předvolbu. Správný formát telefonního čísla je např.: "+420733987123.', 'mwshop');
				if (isset($formData['order_contact']['address']['phone']) && $formData['order_contact']['address']['phone']) {
					try {
						$phoneNumber = $phoneUtil->parse($formData['order_contact']['address']['phone'], $formData['order_contact']['address']['country']);

						if ($phoneUtil->isValidNumber($phoneNumber)) {
							$formData['order_contact']['address']['phone'] = $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);
						} else {
							$errors['order_contact[address][phone]'] = $phoneError;
							$ok = false;
						}
					} catch (\Throwable $e) {
						$errors['order_contact[address][phone]'] = $phoneError;
						$ok = false;
					}
				}
				//Phone in shipping address
				if (isset($formData['order_contact']['has_shipping_addr']) && isset($formData['order_contact']['shipping_address']['phone']) && $formData['order_contact']['shipping_address']['phone']) {
					try {
						$phoneNumber = $phoneUtil->parse($formData['order_contact']['shipping_address']['phone'], $formData['order_contact']['shipping_address']['country']);

						if ($phoneUtil->isValidNumber($phoneNumber)) {
							$formData['order_contact']['shipping_address']['phone'] = $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);
						} else {
							$errors['order_contact[shipping_address][phone]'] = $phoneError;
							$ok = false;
						}
					} catch (\Throwable $e) {
						$errors['order_contact[shipping_address][phone]'] = $phoneError;
						$ok = false;
					}
				}

				if (empty($formData['order_contact']['address']['street'])) {
					$errors['order_contact[address][street]'] = $text_CanNotBeEmpty;
					$ok = false;
				} else {
					$streetValidation = AddressValidator::validateAddressStreet($formData['order_contact']['address']['street']);
					if ($streetValidation === AddressValidator::MISSING_WHITESPACE) {
						$errors['order_contact[address][street]'] = __('Číslo popisné musí být odděleno od ulice mezerou.', 'mwshop');
						$ok = false;
					}
					if ($streetValidation === AddressValidator::MISSING_HOUSE_NUMBER) {
						$errors['order_contact[address][street]'] = __('Zadejte ulici včetně čísla popisného. Pokud ulici nemáte, zadejte jen číslo popisné.', 'mwshop');
						$ok = false;
					}
				}
				if (empty($formData['order_contact']['address']['city'])) {
					$errors['order_contact[address][city]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
				if (empty($formData['order_contact']['address']['zip'])) {
					$errors['order_contact[address][zip]'] = $text_CanNotBeEmpty;
					$ok = false;
				} else {
					if (!AddressValidator::validateCountryAndZipCode($formData['order_contact']['address']['country'], $formData['order_contact']['address']['zip'])) {
						$errors['order_contact[address][zip]'] = __('PSČ neodpovídá vybrané zemi.', 'mwshop');
						$ok = false;
					}
				}
			}

			//Company info
			if (isset($formData['order_contact']['is_company']) && filter_var($formData['order_contact']['is_company'], FILTER_VALIDATE_BOOLEAN)) {
				if (empty($formData['order_contact']['company_info']['company_name'])) {
					$errors['order_contact[company_info][company_name]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
				$vat_required_for = ['SK', 'CZ', 'PL', 'DE', 'FR', 'GB', 'ES', 'SI', 'IT']; // @TODO move to country or vat enum?
				if (empty($formData['order_contact']['company_info']['company_id']) && isset($formData['order_contact']['address']['country']) && in_array($formData['order_contact']['address']['country'], $vat_required_for)) {
					$errors['order_contact[company_info][company_id]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
				// VAT ID
				//                  if(empty($formData['order_contact']['company_info']['company_vat_id'])) {
				//                      $errors['order_contact[company_info][company_vat_id]'] = $text_CanNotBeEmpty;
				//                      $ok = false;
				//                  }
				// SK VAT ID
				//              if(isset($formData['order_contact']['address']['country']) && $formData['order_contact']['address']['country'] == 'SK') {
				//                  if (empty($formData['order_contact']['company_info']['company_sk_vat_id'])) {
				//                      $errors['order_contact[company_info][company_sk_vat_id]'] = $text_CanNotBeEmpty;
				//                      $ok = false;
				//                  }
				//              }
			}
		}

		//Secondary address
		$shippingAddrWillBeFilled = false;

		if (isset($formData['order_contact']['has_shipping_addr']) && filter_var($formData['order_contact']['has_shipping_addr'], FILTER_VALIDATE_BOOLEAN)) {
			$shippingAddrWillBeFilled = true;

			if (empty($formData['order_contact']['shipping_address']['firstname'])) {
			  $errors['order_contact[shipping_address][firstname]'] = $text_CanNotBeEmpty;
			  $ok = false;
			}

			if (empty($formData['order_contact']['shipping_address']['surname'])) {
				$errors['order_contact[shipping_address][surname]'] = $text_CanNotBeEmpty;
				$ok = false;
			}
			//          if (empty($formData['order_contact']['shipping_address']['phone'])) {
			//              $errors['order_contact[shipping_address][phone]'] = $text_CanNotBeEmpty;
			//              $ok = false;
			//          }
			if (empty($formData['order_contact']['shipping_address']['street'])) {
				$errors['order_contact[shipping_address][street]'] = $text_CanNotBeEmpty;
				$ok = false;
			} else {
				$streetValidation = AddressValidator::validateAddressStreet($formData['order_contact']['shipping_address']['street']);
				if ($streetValidation === AddressValidator::MISSING_WHITESPACE) {
					$errors['order_contact[shipping_address][street]'] = __('Číslo popisné musí být odděleno od ulice mezerou.', 'mwshop');
					$ok = false;
				}
				if ($streetValidation === AddressValidator::MISSING_HOUSE_NUMBER) {
					$errors['order_contact[shipping_address][street]'] = __('Zadejte ulici včetně čísla popisného. Pokud ulici nemáte, zadejte jen číslo popisné.', 'mwshop');
					$ok = false;
				}
			}
			if (empty($formData['order_contact']['shipping_address']['city'])) {
				$errors['order_contact[shipping_address][city]'] = $text_CanNotBeEmpty;
				$ok = false;
			}
			if (empty($formData['order_contact']['shipping_address']['zip'])) {
				$errors['order_contact[shipping_address][zip]'] = $text_CanNotBeEmpty;
				$ok = false;
			} else {
				if (!AddressValidator::validateCountryAndZipCode($formData['order_contact']['shipping_address']['country'], $formData['order_contact']['shipping_address']['zip'])) {
					$errors['order_contact[shipping_address][zip]'] = __('PSČ neodpovídá vybrané zemi.', 'mwshop');
					$ok = false;
				}
			}
			if (empty($formData['order_contact']['shipping_address']['country'])) {
				$errors['order_contact[shipping_address][country]'] = $text_CanNotBeEmpty;
				$ok = false;
			} elseif (!in_array($formData['order_contact']['shipping_address']['country'], $countries)) {
				$errors['order_contact[shipping_address][country]'] = $text_CountryNotSupported;
				$ok = false;
			}
		}

		//Check that at least one address is filled when shipping is necessary.
		if ($shippingRequired && !$shippingAddrWillBeFilled && !$invoiceAddressWillBeFilled) {
			$errors['order_contact_shipping_is_invoice'] = __('Pro doručení je potřeba zadat fakturační anebo doručovací adresu.', 'mwshop');
			$flashMsg[] = '<div class="mws_error">' . __('Pro doručení zboží je potřeba zadat fakturační anebo doručovací adresu.', 'mwshop') . '</div>';
			$ok = false;
		}

		$res = ['success' => $ok, 'errors' => $errors];
		if (!empty($flashMsg)) {
			$res['flashMessage'] = $flashMsg;
		}

		return $res;
	}

	/**
	 * Validate form input for terms and conditions.
	 *
	 * @param $formData
	 * @return array Bool value of "success" tells whether validation is ok.
	 *               Value of "errors" contains array with error messages indexed by "HTML input name" attribute.
	 */
	public static function validateTermsAndConditions($formData)
	{
		if (MWS()->isTermsAllowed() && (!isset($formData['summarize']['terms']) || $formData['summarize']['terms'] !== 'confirmed')) {
			$res['errors']['summarize[terms]'] = __('Bez souhlasu s obchodními podmínkami není možné objednávku dokončit.', 'mwshop');
			$res['success'] = false;
		} else {
			$res['success'] = true;
		}
		if ($res['success']) {
			$purposes = MWS()->gateways()->getDefault()->getPurposes();
			foreach ($purposes as $purpose) {
				if (!$purpose['is_primary'] && $purpose['required'] && !isset($formData['purposes'][$purpose['id']])) {
					$res['errors']['purposes[' . $purpose['id'] . ']'] = __('Bez souhlasu s není možné objednávku dokončit.', 'mwshop');
					$res['success'] = false;
				}
			}
		}

		return $res;
	}

	/**
	 * Validate form input for discount code.
	 *
	 * @param $formData
	 * @return array Bool value of "success" tells whether validation is ok.
	 *               Value of "errors" contains array with error messages indexed by "HTML input name" attribute.
	 */
	public static function validateDiscountCode($formData, MwsCart $cart): array
	{
		$res = [
			'success' => true,
			'discountCode' => null,
		];

		if (!isset($formData['discount_code']) || !$formData['discount_code']) {
			return $res;
		}

		$discountCode = MwsDiscountCode::getOneByCode($formData['discount_code']);
		$res['discountCode'] = $discountCode;

		if ($discountCode === null) {
			$res['errors']['discount_code'] = __('Zadaný slevový kód není platný.', 'mwshop');
			$res['success'] = false;
		} else {
			$validationCode = $discountCode->isValid($cart);

			if ($validationCode < 1) {
				$res['errors']['discount_code'] = $discountCode->getValidationError($validationCode, $cart);
				$res['success'] = false;
			}
		}

		return $res;
	}

	/**
	 * Validate form input for shipping and payment.
	 *
	 * @param $formData
	 * @param bool|true $mustHaveShipping
	 * @param MwsForm|null $form
	 * @return array Value of "success" tells whether validation is ok. Value of "errors" contains array with error messages
	 *               indexed by HTML input name attribute. Optionally "shipping" {@link MwsShipping} and "paymentMethod"
	 *                             {@link MwsPaymentMethod} values are filled.
	 */
	public static function validateShippingAndPayment($formData, array $contactAddress, $mustHaveShipping = true, $cartCountry = 'CZ', ?MwsForm $form = null)
	{
		//String constants
		$text_MakeSelectionPayment = __('Zvolte platební metodu', 'mwshop');
		$text_MakeSelectionShipping = __('Zvolte způsob doručení', 'mwshop');
		$text_InvalidValue = __('Zadaná hodnota není platná', 'mwshop');

		$ok = true;
		$errors = [];

		//Shipping
		if ($mustHaveShipping) {
			if (empty($formData['mws_shipping']) || $formData['mws_shipping'] == '0') {
				$errors['mws_shipping'] = $text_MakeSelectionShipping;
				$ok = false;
			} else {
				$shippingId = (int) $formData['mws_shipping'];
				// Non electronic delivery is forbidden.
				if ($shippingId === MwsShippingElectronic::id) {
					$errors['mws_shipping'] = __('Pro vaši objednávku není možné použít elektronické doručení, neboť obsahuje produkty vyžadující doručení.', 'mwshop');
					$ok = false;
				} else {
					$post = get_post($shippingId);
					if ($post === null) {
						$errors['mws_shipping'] = __('Zvolený způsob dopravy není dostupný.', 'mwshop');
						$ok = false;
					} else {
						try {
							$shipping = MwsShipping::getOneById($post->ID);
						} catch (Exception $e) {
							$shipping = null;
						}

						if ($shipping !== null && $shipping->getType() === MwsShippingType::Packeta) {
							if (isset($formData['mws_shipping_info']) && isset($formData['mws_shipping_info']['id']) && $formData['mws_shipping_info']['id']) {
								$shippingInfo = $formData['mws_shipping_info'];
							} else {
								$shipping = null;
								$errors['mws_shipping'] = __('Je nutné vybrat místo vyzvednutí Zásilkovny.', 'mwshop');
								$ok = false;
							}
						}

						if ($shipping !== null && $shipping->getCountry() && $cartCountry && $cartCountry != $shipping->getCountry()) {
							$shipping = null;
							$errors['mws_shipping'] = __('Vybraný způsob doručení, není dostupný pro zvolenou zemi.', 'mwshop');
							$ok = false;
						}
					}
				}
			}
		} else {
			// No physical shipping. Only electronical shipping is allowed.
			$shippingId = isset($formData['mws_shipping']) && $formData['mws_shipping'] ? (int) $formData['mws_shipping'] : null;
			// Non electronic delivery is forbidden.
			if ($shippingId !== null && $shippingId !== MwsShippingElectronic::id) {
				$errors['mws_shipping'] = __('Pro vaši objednávku je přípustný pouze elektronický způsob doručení.', 'mwshop');
				$ok = false;
			} else {
				$shipping = MwsShippingElectronic::getInstance();
			}
		}

		//Payment
//		var_dump($formData['mws_payment']);
		if (empty($formData['mws_payment']) || $formData['mws_payment'] == '0') {
			$errors['mws_payment'] = $text_MakeSelectionPayment;
			$ok = false;
		} else {
			$paymentMethodId = (int) $formData['mws_payment'];
			$paymentMethod = MwsPaymentMethod::getOneById($paymentMethodId);
			$allowedPaymentMethods = $form === null ? MWS()->getPaymentMethods() : $form->getPaymentMethods();
			$allowedPaymentMethodIds = array_map(function (MwsPaymentMethod $paymentMethod): int {
				return $paymentMethod->getId();
			}, $allowedPaymentMethods);

			if ($paymentMethod === null) {
				$errors['mws_payment'] = $text_InvalidValue;
				$ok = false;
			} elseif (!in_array($paymentMethod->getId(), $allowedPaymentMethodIds, true)) {
				$errors['mws_payment'] = __('Zvolený způsob platby není dostupný.', 'mwshop');
				$ok = false;
			} elseif (isset($shipping) && $paymentMethod->isCod() && !$shipping->isCodSupported()) {
				//Check COD delivery only in case shipping method is valid and no other errors concerning payType were detected.
				$errors['mws_payment'] = __('Zvolený způsob platby není přípustný pro zvolenou dopravu.', 'mwshop');
				$ok = false;
			} elseif ($paymentMethod->getType() === MwsPayType::Twisto) {
				if (!isset($contactAddress['country']) || $contactAddress['country'] !== 'CZ') {
					$errors['mws_payment'] = __('Zvolený způsob platby je dostupný pouze pro zákazníky z České republiky.', 'mwshop');
					$ok = false;
				} elseif (!isset($contactAddress['phone']) || !$contactAddress['phone']) {
					$errors['mws_payment'] = __('Pro tuto platební metodu je nutné zadat telefonní číslo.', 'mwshop');
					$ok = false;
				} else {
					$phoneUtil = PhoneNumberUtil::getInstance();
					try {
						$phoneNumber = $phoneUtil->parse($contactAddress['phone'], $contactAddress['country']);
						if ($phoneUtil->getRegionCodeForNumber($phoneNumber) !== 'CZ') {
							$errors['mws_payment'] = __('Pro tuto platební metodu je nutné zadat telefonní číslo s českou předvolbou.', 'mwshop');
							$ok = false;
						}
					} catch (\Throwable $e) {
						$errors['mws_payment'] = __('Formát telefonního čísla není platný.', 'mwshop');
						$ok = false;
					}
				}
			}
		}

		$arr = ['success' => $ok, 'errors' => $errors];
		if (isset($shipping)) {
			$arr['shipping'] = $shipping;
		}
		if (isset($shippingInfo)) {
			$arr['shippingInfo'] = $shippingInfo;
		}
		if (isset($paymentMethod)) {
			$bankFieldName = 'mws_payment_bank_' . $paymentMethod->getId();
			if (isset($formData[$bankFieldName]) && $paymentMethod->getType() === MwsPayType::WireOnline) {
				$paymentMethod->setBank($formData[$bankFieldName]);
			}

			$arr['paymentMethod'] = $paymentMethod;
		}

		return $arr;
	}

	public static function getFormValues()
	{
		$form = $_REQUEST['form'] ?? '';
		parse_str($form, $formData);

		// Remove whitespaces from begin and end of an inputs
		array_walk_recursive($formData, function (&$value) {
			if (is_string($value)) {
				$value = trim($value);
			}
		});

		return $formData;
	}
}
