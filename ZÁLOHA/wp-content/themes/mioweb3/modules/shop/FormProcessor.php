<?php declare(strict_types=1);

namespace Mioweb\Shop;

use Mioweb\HttpClient\Utils\Json;
use Mioweb\Shop\Order\OrderRepository;
use MwsCart;
use Mioweb\Shop\Order\Order;
use MwsPaymentMethod;
use MwsUserException;
use FormValidationException;
use MioShop;
use MwsAjax;
use MwsCartTemporary;
use MwsException;
use MwsForm;
use MwsOrderSource;
use MwsOrderSourceType;
use MwsOrderStep;
use MwsPrice;
use MwsProduct;
use MwsShipping;

final class FormProcessor
{

	/**
	 * Buy a product directly skipping the steps of full order. Validate input at first.
	 */
	public function processOrder(): void
	{
		$nonce = $_POST['nonce'] ?? '';
		if (!wp_verify_nonce($nonce, MioShop::MWS_FORM_NONCE)) {
			$this->sendErrorAndDie(__('Neověřený požadavek.', 'mwshop'));
		}

		$formData = MwsAjax::getFormValues();

		if (!isset($formData['product'])) {
			$this->sendErrorAndDie(__('Produkt se nepodařilo nalézt v nabídce.', 'mwshop'));
		}

		/** @var MwsProduct|null $product */
		$product = MwsProduct::getOneById((int) $formData['product']);
		if ($product === null) {
			$this->sendErrorAndDie(__('Produkt se nepodařilo nalézt v nabídce.', 'mwshop'));
		}

		$priceStr = $formData['price'] ?? null;
		if (!$priceStr) {
			throw new MwsUserException(__('Chyba při zpracování - chybí cena', 'mwshop'));
		}
		$price = MwsPrice::createByArray(Json::decode($priceStr, Json::FORCE_ARRAY)); // @TODO is this safe?

		try {
			$cart = $this->prepareCart($formData, $product, $price);
			$cart->recount(true, false);

			if ($cart instanceof MwsCartTemporary) {
				$this->finishOrder($cart);
			} elseif ($cart instanceof FormDatabaseCart) {
				$upsell = $cart->getNextValidUnprocessedUpsell();
				if ($upsell !== null) {
					$cart->setFormProcessed();
					$cart->save();

					wp_send_json_success([
						'nextUrl' => add_query_arg([Upsell::SECURITY_CODE_QUERY_PARAMETER => $cart->securityCode()], $upsell->getUrl()),
					]);
				} else {
					$this->finishOrder($cart);
				}
			}
		} catch (FormValidationException $e) {
			wp_send_json_error([
				'errors' => $e->getErrors(),
			]);
		} catch (MwsUserException $e) {
			$this->sendErrorAndDie('', $e->getMessage());
		} catch (\Throwable $e) {
			$errMsg = __('Neočekávaná chyba při zpracování.', 'mwshop');
			$errAdmin = $errMsg . ' ' . $e->getMessage();
		}

		$this->sendErrorAndDie(
			'',
			(empty($errMsg) ? __('Zopakujte objednání.', 'mwshop') : $errMsg),
			($errAdmin ?? '')
		);
	}

	/**
	 * @throws FormValidationException
	 * @throws MwsException
	 * @throws MwsUserException
	 */
	public function prepareCart(
		array $formData,
		MwsProduct $product,
		MwsPrice $price,
		bool $ignoreErrors = false
	): MwsCart
	{
		$errors = [];

		if (isset($formData['source']['formId']) && $formData['source']['formId']) {
			$form = MwsForm::getOneById((int) $formData['source']['formId']);
			if ($form === null) {
				throw new MwsUserException(__('Prodejní formulář nebyl nalezen.', 'mwshop'));
			}
			\assert($form instanceof MwsForm);

			$phoneRequired = (bool) ($form->getVisibilitySettings()['show_field_phone'] ?? false);
			$fullNameRequired = (bool) ($form->getVisibilitySettings()['show_field_name'] ?? false);
			$showCountry = (bool) ($form->getVisibilitySettings()['show_field_country'] ?? false);
			$allowSimplified = $form->isSimplifiedAllowed();
		} else {
			$form = null;
			$phoneRequired = false;
			$allowSimplified = isset($_POST['allowSimplified']) && (bool) filter_var($_POST['allowSimplified'], FILTER_VALIDATE_BOOLEAN);
			$fullNameRequired = $allowSimplified;
			$showCountry = true;
		}

		// miniupsell
		$miniupsellProduct = null;

		if ($formData['miniupsell'] ?? false) {
			/** @var MwsProduct|null $miniupsellProduct */
			$miniupsellProduct = $form->getMiniupsell();
		}

		// is shipping required
		$isShippingRequired = ($product->isShippingRequired() || ($miniupsellProduct !== null && $miniupsellProduct->isShippingRequired()));

		// validate contact
		$res = MwsAjax::validateContactForm($formData, $isShippingRequired, $phoneRequired, $fullNameRequired, $allowSimplified);
		if (!$res['success']) {
			$errors = array_merge($errors, $res['errors']);
		}
		// validate shipping and payment
		if (isset($formData['order_contact']['has_shipping_addr']) && $formData['order_contact']['shipping_address']['country']) {
			$shippingCountry = $formData['order_contact']['shipping_address']['country'];
		} elseif ($showCountry && isset($formData['order_contact']['address']['country'])) {
			$shippingCountry = $formData['order_contact']['address']['country'];
		} else {
			$shippingCountry = MWS()->getDefaultShippingCountry();
		}
		$res = MwsAjax::validateShippingAndPayment($formData, $formData['order_contact']['address'] ?? [], $isShippingRequired, $shippingCountry, $form);
		if (!$res['success']) {
			$errors = array_merge($errors, $res['errors']);
		}
		if (isset($res['shipping'])) {
			$shipping = $res['shipping'];
		}
		if (isset($res['paymentMethod'])) {
			$paymentMethod = $res['paymentMethod'];
		}
		if (isset($res['shippingInfo'])) {
			$shippingInfo = $res['shippingInfo'];
		}
		// validate terms and conditions
		$res = MwsAjax::validateTermsAndConditions($formData);
		if (!$res['success']) {
			$errors = array_merge($errors, $res['errors']);
		}

		if ((bool) $errors && !$ignoreErrors) {
			throw new FormValidationException($errors);
		}

		// order
		$count = (int) ($formData['count'] ?? 1);

		if ($price->getPriceVatIncluded() == 0) {
			throw new MwsUserException(__('U produktu je nulová cena? To je podezřelé.', 'mwshop'));
		}

		// Prepare temporary cart
		$cart = $this->getCart($form, $paymentMethod ?? null);
		$cart->addItem($product, $count);
		$item = $cart->getItems()->getOneById($product->getId());
		if ($item === null) {
			throw new MwsUserException(__('Chyba při vkládání do košíku.', 'mwshop'));
		}
		$item->setStoredPrice($price);
		$item->setStoredShopPrice($price);
		$item->setStoredProductPrice($product->getPrice());
		if (isset($shipping)) {
			$cart->setShipping($shipping);
			$cart->setShippingPrice($shipping->getPriceForCart($product->getPrice()));
			$cart->setShippingInfo($shippingInfo ?? []);
		} else {
			$cart->setShippingPrice(null);
		}
		$cart->setContact($formData['order_contact'] ?? []);
		if (isset($paymentMethod)) {
			$cart->setPaymentMethod($paymentMethod);
		} elseif (!$ignoreErrors) {
			throw new MwsUserException(__('Selhalo určení platební metody.', 'mwshop'));
		}

		if ($form !== null) {
			$cart->setHeurekaDisagree(true);

			try {
				$cart->setThxPage($form->getThxPage());
				$cart->setIsTest($form->isTest());
			} catch (MwsException $e) {
				throw new MwsUserException(__('Nepodařilo se získat nastavení prodejního formuláře', 'mwshop'));
			}

			// validate discount code
			$res = MwsAjax::validateDiscountCode($formData, $cart);
			if (!$res['success']) {
				if (!$ignoreErrors) {
					throw new FormValidationException($res['errors']);
				}
			} else {
				$discountCode = $res['discountCode'];

				if ($form->allowDiscountCodes()) {
					$cart->setDiscountCode($discountCode);
				}
			}

			$pageId = $formData['source']['pageId'] !== null ? (int) $formData['source']['pageId'] : null;
			$url = $formData['source']['url'] ?? null;
			$cart->setSource(new MwsOrderSource(MwsOrderSourceType::Form, $pageId, $url, $form->getId()));

			// MINIUPSELL
			if ($miniupsellProduct !== null) {
				$cart->addItem($miniupsellProduct, 1);
				$item = $cart->getItems()->getOneById($miniupsellProduct->getId());
				if ($item === null) {
					throw new MwsUserException(__('Chyba při vkládání do košíku.', 'mwshop'));
				}
				$item->setStoredPrice($miniupsellProduct->getPrice());
				$item->setStoredShopPrice($miniupsellProduct->getPrice());
				$item->setStoredProductPrice($miniupsellProduct->getPrice());
				$item->setMiniupsell();
			}
			//quick shop
		} else {
			// validate discount code
			$res = MwsAjax::validateDiscountCode($formData, $cart);
			if (!$res['success']) {
				if (!$ignoreErrors) {
					throw new FormValidationException($res['errors']);
				}
			} else {
				$cart->setDiscountCode($res['discountCode']);
			}

			if (isset($formData['source']['thanksPage']) && $formData['source']['thanksPage'] !== '') {
				$cart->setThxPage($formData['source']['thanksPage']);
			}

			$cart->setHeurekaDisagree(isset($formData['heureka_disagree']));
			$cart->setSource(new MwsOrderSource(MwsOrderSourceType::QuickBuy));
		}

		return $cart;
	}

	public function finishOrder(MwsCart $cart)
	{
		$gw = MWS()->gateways()->getDefault();

		$res = $gw->sharedInstance()->makeOrder($cart);
		$ok = $res['success'];

		if ($ok) {
			// Update statistics
			$cart->incOrderedCount();

			$order = isset($res['orderId']) ? OrderRepository::getOneById($res['orderId']) : null;

			if ($cart instanceof FormCart) {
				$cart->clear(false);
			}

			wp_send_json_success([
				'nextUrl' => ($res['nextUrl'] ?? add_query_arg([
						'success' => true,
						'gw' => $order !== null ? $order->getGateIdentifier() : null,
				], MWS()->getUrl_Cart(MwsOrderStep::ThankYou))),
				'stripe' => $res['stripe'] ?? null,
				'twisto' => $res['twisto'] ?? null,
			]);
		} elseif (isset($res['message'])) {
			$this->sendErrorAndDie($res['message'], __('Zopakujte objednání.', 'mwshop'));
		}
	}

	private function getCart(?MwsForm $form, ?MwsPaymentMethod $paymentMethod): MwsCart
	{
		if (
			$form !== null
			&& $paymentMethod !== null
			&& count($form->getValidUpsells()) >= 1
			&& $paymentMethod->getType() !== 'stripe'
//			&& $paymentMethod->getType() === MwsPayType::CreditCard
		) {
			$formCart = new FormDatabaseCart($form);
			$formCart->clear();

			return $formCart;
		}

		return new MwsCartTemporary();
	}

	/**
	 * Send flash error message and die. Optionally add detailed message, different for user and for administrator.
	 *
	 * @param string $mainMsg Overridable message
	 * @param string $detailUser Optional message for user.
	 * @param string $detailAdmin Optional message for admin. If empty, then $detailUser is used.
	 */
	private function sendErrorAndDie(string $mainMsg = '', string $detailUser = '', string $detailAdmin = '')
	{
		if (empty($mainMsg)) {
			$mainMsg = __('Objednávku se nepodařilo odeslat. %s', 'mwshop');
		}

		wp_send_json_error([
			'flashMessage' => '<div class="mws_error">' . sprintf(
					$mainMsg,
					(MWS()->edit_mode ? (empty($detailAdmin) ? $detailUser : $detailAdmin) : $detailUser)
				) . '</div>',
		]);
	}

}
