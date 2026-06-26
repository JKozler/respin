<?php

use Fapi\FapiClient\IFapiClient;
use Fapi\FapiClient\FapiClientFactory;
use Mioweb\Lib\MwPrice;
use Nette\Utils\Json;
use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Button;

class mwAPIConnectItemClient_fapi extends mwAPIConnectItemClient
{

	/** @var IFapiClient */
	private $client;

	public function __construct(mwAPIConnectItem $mwAPIConnectItem)
	{
		parent::__construct($mwAPIConnectItem);
		$options = $mwAPIConnectItem->getOption();
		$this->client = (new FapiClientFactory())->createFapiClient($options['login'] ?? '', $options['password'] ?? '');
	}

	function checkSavedSetting(&$tosave): bool
	{
		$login = $tosave['login'] ?? null;
		$apiKey = $tosave['password'] ?? null;

		if ($login && $apiKey) {
			$tosave['login'] = $login = trim($login);
			$tosave['password'] = $apiKey = trim($apiKey);

			$isConnected = $this->isConnected($login, $apiKey);

			if ($isConnected) {
				$this->recreateFormIfNeeded($login, $apiKey);
			} else {
				mwMessages()->error(__('Přihlašovací údaje nejsou správné. Spojení se nezdařilo.', 'cms'));
			}

			return $isConnected;
		}

		mwMessages()->error(__('Musíte vyplnit přihlašovací jméno i API klíč.', 'cms'));

		return false;
	}

	function isConnected($login, $apiKey): bool
	{
		require_once dirname(__FILE__) . '/FAPIClient.php';
		$fapiClient = new FAPIClient($login, $apiKey);
		try {
			$fapiClient->checkConnection();
			$validCredentials = true;
		} catch (Exception $e) {
			$validCredentials = false;
		}

		return $validCredentials;
	}

	public function printForm($element, $css_id, $post_id, $edit_mode, $added)
	{
		global $vePage;

		$user = wp_get_current_user();
		if ($user->ID) {
			$login = $this->_mwAPIConnectItem->getOption();

			if ($this->isConnected($login['login'], $login['password'])) {
				$clientDetails = FapiHelpers::getClientFields($login['login'], $login['password'], $user->user_email);
				$clientDetails = FapiHelpers::escapeJs($clientDetails);
			} else {
				$clientDetails = null;
			}
		} else {
			$clientDetails = null;
		}

		$class = Colors::isLightColor($element['style']['background-color']) ? ' light_color' : ' dark_color';

		$content = '<div class="in_element_content in_element_fapi_form in_element_fapi_form_' . $element['style']['form-style'] . ' ' . $class . '">';

		$button = new Button($element['style']['button'], '', $css_id . ' .ve_content_button');
		$vePage->display->element_css = $button->addButtonStyles($vePage->display->element_css, null, $edit_mode);
		$but_class = 've_content_button ' . $button->getButtonClasses();

		if ($added) {
			$fapi_form_c = str_replace('/', '\/', stripslashes($element['style']['content']['id']));
			$content .= 'Načítám formulář...<script type="text/javascript">
                jQuery(document).ready(function($) {
                    mwGetIframeContent().mw_load_added_fapi_form("' . $css_id . ' .in_element_content","' . $but_class . '",\'' . $fapi_form_c . '\',\'' . $clientDetails . '\');
                });

            </script>';
		} else {
			$content .= '<script type="text/javascript">
              jQuery(document).ready(function($) {
                  mw_load_fapi_form("' . $css_id . ' .in_element_content","' . $but_class . '",\'' . $clientDetails . '\');
              });
          	</script>';
			$content .= stripslashes($element['style']['content']['id']);
		}

		$content .= '</div>';

		$vePage->display->element_css->addStyles(['font' => $element['style']['font_title'] ?? ''], $css_id . ' .form_container_title');
		$vePage->display->element_css->addStyles(['font' => $element['style']['font_text']], $css_id . ' .in_element_fapi_form');
		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .form_container' => ['background-color'],
				$css_id . ' .fapi-form-submit' => ['background-color'],
			],
			'--fapi-background-color-' . $css_id,
			$element['style']['background-color']
		);

		return $content;
	}

	public function getFormsList()
	{
		require_once dirname(__FILE__) . '/FAPIClient.php';
		$forms = false;
		$login = $this->_mwAPIConnectItem->getOption();
		$fapiClient = new FAPIClient($login['login'], $login['password']);
		try {
			$fapiClient->checkConnection();
			$forms = $fapiClient->form->getAll(); // vrací pole formulářů
		} catch (Exception $e) {
		}

		return $forms;
	}

	public function getProductsList()
	{
		require_once dirname(__FILE__) . '/FAPIClient.php';
		$products = false;
		$login = $this->_mwAPIConnectItem->getOption();
		$fapiClient = new FAPIClient($login['login'], $login['password']);
		try {
			$fapiClient->checkConnection();
			$products = $fapiClient->itemTemplate->getAll();
		} catch (Exception $e) {
		}

		return $products;
	}

	function getOrder($id): ?array
	{
		return $this->client->getOrders()->find((int) $id);
	}

	function getInvoice($id)
	{
		require_once dirname(__FILE__) . '/FAPIClient.php';
		$login = $this->_mwAPIConnectItem->getOption();
		$invoice = false;
		$fapiClient = new FAPIClient($login['login'], $login['password']);
		try {
			$fapiClient->checkConnection();
			$fapi_invoice = $fapiClient->invoice->search(['variable_symbol' => intval($id), 'single' => true]);
			if (!$fapi_invoice) {
				return false;
			}

			$set = $fapiClient->settings->getAll();

			$invoice = [
				'invoice' => $fapi_invoice,
				'id' => $fapi_invoice['id'],
				'price' => $fapi_invoice['total_native'],
				'currency' => $set['currency'] ?? 'CZK',
				'email' => $fapi_invoice['customer']['email'] ?? '',
				'vs' => $fapi_invoice['number'],
			];
		} catch (Exception $e) {
		}

		return $invoice;
	}

	function getInvoiceVariables($id)
	{
		$invoice = $this->getInvoice($id);

		if (!$invoice) {
			return [];
		}

		$items = $invoice['invoice']['items'];
		$discountCode = '';
		foreach ($items as $item) {
			if ($item['type'] === 'discount' && isset($item['code'])) {
				$discountCode = $item['code'];

				break;
			}
		}

		$shippingPrice = 0;
		$shippingPriceNovat = 0;
		$shippingNativePrice = 0;
		$shippingNativePriceNovat = 0;

		foreach ($items as $item) {
			if ($item['type'] === 'shipping') {
				if ($item['including_vat'] ?? false) {
					$shippingPriceNovat = round($item['price'] - MwPrice::calculateVatByPriceVatIncluded($item['price'], $item['vat']), 2);
					$shippingPrice = $item['price'];
				} else {
					$shippingPriceNovat = $item['price'];
					$shippingPrice = round($item['price'] + MwPrice::calculateVatByPriceVatExcluded($item['price'], $item['vat'] ?? 0), 2);
				}

				$shippingNativePriceNovat = $shippingPriceNovat * $invoice['invoice']['exchange_rate'];
				$shippingNativePrice = $shippingPrice * $invoice['invoice']['exchange_rate'];
			}
		}

		$variables = [
			'CURRENCY' => $invoice['invoice']['currency'],
			//'NATIVE_CURRENCY' => $invoice['invoice']['currency_native'],
			'VARIABLE_SYMBOL' => $invoice['invoice']['variable_symbol'],
			'PRICE_NOVAT' => $invoice['invoice']['total'] - $invoice['invoice']['total_vat'],
			'PRICE' => $invoice['invoice']['total'],
			'NATIVE_PRICE_NOVAT' => $invoice['invoice']['total_native'] - $invoice['invoice']['total_vat_native'],
			'NATIVE_PRICE' => $invoice['invoice']['total_native'],
			'ORDER_CODE' => $invoice['invoice']['variable_symbol'],
			'GA_ITEMS' => MwVariables::generateItemsForGA($items),
			'DISCOUNT_CODE' => $discountCode,
			'SHIPPING_PRICE_NOVAT' => $shippingPriceNovat,
			'SHIPPING_PRICE' => $shippingPrice,
			'PRICE_NO_SHIPPING' => $invoice['invoice']['total'] - $shippingPrice,
			'PRICE_NO_SHIPPING_NOVAT' => $invoice['invoice']['total'] - $invoice['invoice']['total_vat'] - $shippingPriceNovat,
			'NATIVE_PRICE_NO_SHIPPING' => $invoice['invoice']['total_native'] - $shippingNativePrice,
			'NATIVE_PRICE_NO_SHIPPING_NOVAT' => $invoice['invoice']['total_native'] - $invoice['invoice']['total_vat_native'] - $shippingNativePriceNovat,
			//'SHIPPING_METHOD' => isset($invoice['invoice']['shipping_method'])? $invoice['invoice']['shipping_method']['name'] : '',
		];

		return MwVariables::addAliases($variables);
	}

	public function getPurchaseEventData($id, $funnel = null): ?array
	{
		$invoice = $this->getInvoice($id);

		$upsell = 0;
		$bump = 0;

		if (!$invoice) {
			return null;
		}

		if ($funnel && ($funnel->upsell || $funnel->bump)) {
			foreach ($invoice['invoice']['items'] as $item) {
				if ($item['code'] == $funnel->upsell) {
					$upsell = 1;
				}

				if ($item['code'] == $funnel->bump) {
					$bump = 1;
				}
			}
		}

		return [
			'id' => $invoice['id'],
			'email' => $invoice['email'],
			'price' => $invoice['price'],
			'currency' => $invoice['currency'],
			'upsell' => $upsell,
			'bump' => $bump,
			'vs' => $invoice['vs'],
		];
	}

	function getSettings(): array
	{
		return $this->client->getSettings()->findAll();
	}

	/** Method does not actually "recreate" form but just delete current "form ID" */
	private function recreateFormIfNeeded($login, $apiKey): void
	{
		if (function_exists('MWS')) { // Function exists only with active `shop` module
			$gw = MWS()->gateways()->getById('fapi');

			if ($gw !== null) {
				$gwSettings = $gw->loadSettings();
				$currentFormId = $gwSettings['form']['id'] ?? null;

				if ($currentFormId !== null) {
					$fapiClient = new FAPIClient($login, $apiKey);

					try {
						$fapiClient->form->get($currentFormId);
						// ok
					} catch (FAPIClient_UnauthorizedException $e) {
						// Need to create new form, so we will set form ID to null and the form will be lazily recreated when it is necessary
						$gwSettings['form']['id'] = null;
						$gw->saveSettings($gwSettings);
					}
				}
			}
		}
	}

}

class FapiHelpers
{

	/**
	 * @param string $fapiUsername
	 * @param string $fapiPassword
	 * @param string $email
	 * @return mixed[]|null
	 */
	public static function getClientFields($fapiUsername, $fapiPassword, $email): ?array
	{
		$fapiClient = (new FapiClientFactory())->createFapiClient($fapiUsername, $fapiPassword);
		$clients = $fapiClient->getClients()->findAll(['email' => trim($email)]);

		if (!$clients) {
			return null;
		}

		$client = $clients[0];

		$fields = [
			'first_name' => $client['first_name'] ?: null,
			'last_name' => $client['last_name'] ?: null,
			'email' => $client['email'] ?: null,
			'phone' => $client['phone'] ?: null,
			'street' => $client['address']['street'] ?: null,
			'city' => $client['address']['city'] ?: null,
			'zip' => $client['address']['zip'] ?: null,
			'country' => $client['address']['country'] ?: null,
			'company' => isset($client['company']) ? ($client['company'] ?: null) : null,
			'ico' => isset($client['ic']) ? ($client['ic'] ?: null) : null,
			'dic' => isset($client['dic']) ? ($client['dic'] ?: null) : null,
		];

		// Fallback for old Fapi form
		$fields['name'] = $fields['first_name'];
		$fields['surname'] = $fields['last_name'];
		$fields['mobil'] = $fields['phone'];
		$fields['postcode'] = $fields['zip'];
		$fields['state'] = $fields['country'];
		$fields['ic'] = $fields['ico'];

		return $fields;
	}

	public static function escapeJs($s)
	{
		// Based on method Latte\Runtime\Filters::escapeJs() from Nette Framework.
		// Copyright (c) 2004 David Grudl (http://davidgrudl.com)
		// Licensed under the New BSD License.
		$json = json_encode($s);

		return str_replace(["\xe2\x80\xa8", "\xe2\x80\xa9", ']]>', '<!'], ['\u2028', '\u2029', ']]\x3E', '\x3C!'], $json);
	}
}
