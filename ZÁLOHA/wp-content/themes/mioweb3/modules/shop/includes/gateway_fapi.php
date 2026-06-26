<?php

use Mioweb\Shop\Order\IOrder;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\OrderGate;
use Mioweb\Shop\Order\OrderGateDocument;
use Mioweb\Shop\Order\OrderItem;
use Mioweb\Shop\Order\OrderRepository;
use Nette\Http\Url;

define('AUTOMANAGED_TAG', 'automanaged');

class MwsGatewayImpl_Fapi extends MwsGatewayImpl
{

	/** @var MwShop\FapiClient\FapiClient */
	private $_api;

	/** @var array Array of preloaded invoices from FAPI. */
	private $_preloadedInvoices = [];

	/** @var array Array of cached forms received from FAPI, indexed by form id. In memory cache. */
	private $_forms = [];

	function __construct(MwsGatewayMeta $meta)
	{
		parent::__construct($meta);
		require_once(MWS_PATH_LIBS . '/fapi/autoload.php');
	}

	public function getApi(): \MwShop\FapiClient\FapiClient
	{
		if (!$this->_api) {
			$option = mwApiConnect()->getApi('fapi')->getOption();
			$login = $option['login'] ?? '';
			$password = $option['password'] ?? '';

			$httpCli = new MwShop\HttpClient\CurlHttpClient();
			// Log FAPI API communication?
			if (defined('MW_LOG_LEVEL_FAPI') && MW_LOG_LEVEL_FAPI !== MWLL_DISABLED) {
				MwLogger::instance()->setLevel(MWLS_FAPI, MW_LOG_LEVEL_FAPI);
			}

			$this->_api = new MwShop\FapiClient\FapiClient(
				$login,
				$password,
				defined('MW_TEST_GATEWAY_NOT_ACCESSIBLE') && MW_TEST_GATEWAY_NOT_ACCESSIBLE ? 'https://nonexisten' : 'https://api.fapi.cz/',
				$httpCli,
				['timeout' => 50, 'connect_timeout' => 10]
			);
		}

		return $this->_api;
	}

	public function loadRemoteSettings(): array
	{
		try {
			return $this->getApi()->getSettings();
		} catch (Exception $e) {
			throw new FapiGatewayCommunicationException(__('Globální nastavení FAPI se nepodařilo načíst.', 'mwshop'), 0, $e);
		}
	}

	public function getSupplierContact(): MwsContact
	{
		try {
			$supplier = $this->getApi()->getCurrentUser();
		} catch (Exception $e) {
			throw new FapiGatewayCommunicationException(__('Fakturační údaje dodavatele z FAPI se nepodařilo načíst.', 'mwshop'), 0, $e);
		}

		return new MwsContact(
			$supplier['sender_reply_to'] ?? get_option('admin_email'),
			null,
			null,
			new MwsCompany(
				$supplier['name'] ?? '',
				null,
				$supplier['ic'] ?? null,
				$supplier['dic'] ?? null
			),
			new MwsAddress(
				$supplier['address']['country'],
				$supplier['address']['city'] ?? '',
				$supplier['address']['zip'] ?? '',
				$supplier['address']['street'] ?? ''
			)
		);
	}

	/**
	 * Load form definition from Fapi. Use in memory cache.
	 */
	private function getForm(): array
	{
		$settings = $this->meta->loadSettings();
		$formId = $settings['form']['id'] ?? null;
		if (!$formId) {
			// if not form try create form
			if ($this->doSyncSettings()) {
				return $this->getForm();
			}

			// form not exist and not created
			throw new MwsException(__('Eshop nemá zvolen FAPI formulář.'));
		}
		if (!isset($this->_forms[$formId])) {
			$this->_forms[$formId] = $this->getApi()->getForm($formId, [
				'with_payment_methods' => 1,
			]);
		}

		$form = $this->_forms[$formId];

		return $this->updateFormUrlsIfNeeded($form);
	}

	public function updateForm(array $settings): void
	{
		$form = $this->getForm();

		try {
			$this->getApi()->updateForm($form['id'], $settings);
			mwshoplog(sprintf(__('FAPI formulář [%d] aktualizován', 'mwshop'), $form['id']), MWLL_INFO, 'paygate');
		} catch (Exception $e) {
			mwshoplog(sprintf(__('Chyba při aktualizaci FAPI formuláře [%d]:', 'mwshop'), $form['id']) . ' ' . $e->getMessage() . ' ' . __METHOD__, MWLL_ERROR, 'paygate');
		}
	}

	/**
	 * This method automatically updates thanks_url and error_url in FAPI form settings when web domain is changed
	 *
	 * @param mixed[] $form Fapi form
	 * @return mixed[] Fapi form
	 */
	private function updateFormUrlsIfNeeded(array $form): array
	{
		$orderUrl = MWS()->getUrl_Cart(MwsOrderStep::ThankYou);
		$thanksUrl = add_query_arg(['success' => 1, 'gw' => $this->getId()], $orderUrl);
		$errorUrl = add_query_arg(['success' => 0, 'gw' => $this->getId()], $orderUrl);
		$eshopUrl = new Url(get_site_url());
		$eshopUrlStr = rtrim($eshopUrl->getAbsoluteUrl(), '/');

		$updateArr = [];
		if (($form['thanks_url'] ?? null) !== $thanksUrl) {
			$form['thanks_url'] = $updateArr['thanks_url'] = $thanksUrl;
		}
		if (($form['error_url'] ?? null) !== $errorUrl) {
			$form['error_url'] = $updateArr['error_url'] = $errorUrl;
		}
		if (($form['mioweb_eshop_url'] ?? null) !== $eshopUrlStr) {
			$form['mioweb_eshop_url'] = $updateArr['mioweb_eshop_url'] = $eshopUrlStr;
		}

		$triggers = $form['triggers'] ?? [];
		$triggerUpdate = false;

		foreach ($triggers as $key => $trigger) {
			if (
				$trigger['action'] === 'url-notification'
				&& str_contains($trigger['notification_url'], 'mws_gate_callback&gw=fapi&operation=')
			) {
				try {
					$url = new Url($trigger['notification_url']);

					if ($url->getHost() !== $eshopUrl->getHost()) {
						$url->setHost($eshopUrl->getHost());
						$updateTriggerArr = $trigger;
						$updateTriggerArr['notification_url'] = $url->getAbsoluteUrl();
						$triggers[$key] = $updateTriggerArr;
						$triggerUpdate = true;
					}
				} catch (\Throwable $e) {
					// ignore
				}
			}
		}

		if ($triggerUpdate) {
			$updateArr['triggers'] = $triggers;
		}

		if ($updateArr) {
			$this->getApi()->updateForm($form['id'], $updateArr);
		}

		return $form;
	}

	public function getFormLink(): string
	{
		$errorMsg = __('Nastavení FAPI formuláře se nepodařilo načíst. Zkontrolujte prosím přihlašovací údaje k FAPI.', 'mwshop');

		try {
			$form = $this->getForm();

			return '<a href="https://web.fapi.cz/forms/preview/' . $form['id'] . '?projectId=all" target="_blank">' . $form['name'] . '</a>';
		} catch (MwsException $e) {
			throw new MwsException($errorMsg . ' ' . $e->getMessage());
		} catch (Exception $e) {
			throw new MwsException($errorMsg, 0, $e);
		}
	}

	public function loadRemotePayTypes(): array
	{
		try {
			$form = $this->getForm();
			$allowGoPay = $form['allow_gopay'];
			$allowed = [];
			$map = [
				MwsPayType::Cod => 'allow_collect_on_delivery',
				MwsPayType::Wire => 'allow_wire',
				// GoPay dependant
				MwsPayType::PayPal => 'allow_gopay_paypal',
				MwsPayType::Sms => 'allow_gopay_sms',
				MwsPayType::Twisto => 'allow_twisto',
				MwsPayType::WireOnline => 'allow_gopay_wire',
				MwsPayType::CreditCard => 'allow_gopay_card',
				MwsPayType::Bitcoin => 'allow_gopay_bitcoin',
				MwsPayType::ApplePay => 'allow_gopay_apple_pay',
				MwsPayType::GooglePay => 'allow_gopay_gpay',
			];
			foreach ($map as $type => $name) {
				if ($form[$name] ?? false) {
					if (in_array($type, [MwsPayType::PayPal, MwsPayType::Sms, MwsPayType::WireOnline, MwsPayType::CreditCard, MwsPayType::ApplePay, MwsPayType::GooglePay])) {
						if ($allowGoPay) {
							$allowed[] = $type;
						}
					} else {
						$allowed[] = $type;
					}
				}
			}

			return $allowed;
		} catch (MwsException $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop') . ' ' . $e->getMessage());
		} catch (Exception $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop'), 0, $e);
		}
	}

	public function loadRemotePayments(): array
	{
		try {
			$form = $this->getForm();
			$allowed = [];
			foreach ($form['payment_methods'] ?? [] as $payment) {
				if ($payment['payment_type'] === 'cash') {
					continue;
				}
				if ($payment['payment_type'] === 'collect on delivery') {
					$payment['payment_type'] = MwsPayType::Cod;
				} elseif ($payment['payment_type'] === MwsPayType::Wire && $payment['bank']) {
					$payment['payment_type'] = MwsPayType::WireOnline;
				} elseif ($payment['payment_type'] === 'credit card') {
					$payment['payment_type'] = MwsPayType::CreditCard;
				}
				$payment['currencies'] = $payment['currencies'] === ['EVERY'] ? null : array_map('strtolower', $payment['currencies']);
				$payment['countries'] = $payment['countries'] === ['EVERY'] ? null : $payment['countries'];
				$allowed[] = $payment;
			}

			return $allowed;
		} catch (MwsException $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop') . ' ' . $e->getMessage());
		} catch (Exception $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop'), 0, $e);
		}
	}

	public function doGetPurposes(): array
	{
		try {
			$formSet = $this->getForm();
			$purposes = $formSet['purposes'] ?? [];

			return $purposes;
		} catch (MwsException $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop') . ' ' . $e->getMessage());
		} catch (Exception $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop'), 0, $e);
		}
	}

	public function loadRemoteUseSimplifiedInvoiceForEshop(): bool
	{
		try {
			$form = $this->getForm();

			return $form['allow_simplified'];
		} catch (MwsException $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop') . ' ' . $e->getMessage());
		} catch (Exception $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop'), 0, $e);
		}
	}

	public function loadRemoteUseSimplifiedInvoiceForQuickBuy(): bool
	{
		return false;
	}

	public function loadRemoteUseSimplifiedInvoiceForForm(MwsForm $form): bool
	{
		// simplified invoice is handled by FAPI itself
		return false;
	}

	public function doGetEnabledCodes(bool $reload = false): array
	{
		$settings = $this->meta->getRemoteSettings($reload);
		$codes = [
			MwsProductCode::Filing,
		];
		if ($settings['accounting_codes'] ?? false) {
			$codes[] = MwsProductCode::Financial;
		}
		if ($settings['pohoda_accounting'] ?? false) {
			$codes[] = MwsProductCode::Assignment;
		}
		if ($settings['pohoda_centre'] ?? false) {
			$codes[] = MwsProductCode::Center;
		}
		if ($settings['pohoda_stock'] ?? false) {
			$codes[] = MwsProductCode::Stock;
			$codes[] = MwsProductCode::StockItem;
		}
		if ($settings['pohoda_store'] ?? false) {
			$codes[] = MwsProductCode::Stock;
		}
		if ($settings['pohoda_stock_item'] ?? false) {
			$codes[] = MwsProductCode::StockItem;
		}

		return $codes;
	}

	public function doGetSupportedCountries(): array
	{
		$countries = [];

		$settings = $this->meta->loadSettings();
		if ($settings['form']['allow_region_cz'] ?? false) {
			$countries[] = 'CZ';
		}
		if ($settings['form']['allow_region_sk'] ?? false) {
			$countries[] = 'SK';
		}
		if ($settings['form']['allow_region_eu'] ?? false) {
			$countries += [
				'CZ',
				'SK',
				'AT',
				'BE',
				'BG',
				'HR',
				'CY',
				'DK',
				'EE',
				'FI',
				'FR',
				'DE',
				'GR',
				'HU',
				'IE',
				'IT',
				'LV',
				'LT',
				'LU',
				'MT',
				'NL',
				'PL',
				'PT',
				'RO',
				'SI',
				'ES',
				'SE',
			];
		}
		if ($settings['form']['allow_region_world'] ?? false) {
			$countries += [
				'AF',
				'AL',
				'DZ',
				'AS',
				'AD',
				'AO',
				'AI',
				'AQ',
				'AG',
				'AR',
				'AM',
				'AW',
				'AU',
				'AZ',
				'BS',
				'BH',
				'BD',
				'BB',
				'BY',
				'BZ',
				'BJ',
				'BM',
				'BT',
				'BO',
				'BA',
				'BW',
				'BV',
				'BR',
				'IO',
				'BN',
				'BF',
				'BI',
				'KH',
				'CM',
				'CA',
				'CV',
				'KY',
				'CF',
				'CC',
				'CO',
				'KM',
				'CG',
				'CD',
				'CK',
				'CR',
				'CI',
				'CU',
				'DJ',
				'DM',
				'DO',
				'EC',
				'EG',
				'SV',
				'GQ',
				'ER',
				'ET',
				'FK',
				'FO',
				'FJ',
				'GF',
				'PF',
				'TF',
				'GA',
				'GB',
				'GM',
				'GE',
				'GH',
				'GI',
				'GL',
				'GD',
				'GP',
				'GU',
				'GT',
				'GG',
				'GN',
				'GW',
				'GY',
				'HT',
				'HM',
				'VA',
				'HN',
				'HK',
				'TD',
				'CL',
				'CN',
				'CX',
				'IS',
				'IN',
				'ID',
				'IR',
				'IQ',
				'IM',
				'IL',
				'JM',
				'JP',
				'JO',
				'KZ',
				'KE',
				'KI',
				'KP',
				'KR',
				'KW',
				'KG',
				'LA',
				'LB',
				'LS',
				'LR',
				'LY',
				'LI',
				'MO',
				'MK',
				'MG',
				'MW',
				'MY',
				'MV',
				'ML',
				'MH',
				'MQ',
				'MR',
				'MU',
				'YT',
				'MX',
				'FM',
				'MD',
				'MC',
				'MN',
				'MS',
				'MA',
				'MZ',
				'MM',
				'NA',
				'NR',
				'NP',
				'NC',
				'NZ',
				'NI',
				'NE',
				'NG',
				'NU',
				'NF',
				'MP',
				'NO',
				'OM',
				'PK',
				'PW',
				'PS',
				'PA',
				'PG',
				'PY',
				'PE',
				'PH',
				'PN',
				'PR',
				'QA',
				'RE',
				'RU',
				'RW',
				'SH',
				'KN',
				'LC',
				'PM',
				'VC',
				'WS',
				'SM',
				'ST',
				'SA',
				'SN',
				'CS',
				'SC',
				'SL',
				'SG',
				'SB',
				'SO',
				'ZA',
				'GS',
				'LK',
				'SD',
				'SR',
				'SJ',
				'SZ',
				'CH',
				'SY',
				'TW',
				'TJ',
				'TZ',
				'TH',
				'TL',
				'TG',
				'TK',
				'TO',
				'TT',
				'TN',
				'TR',
				'TM',
				'TC',
				'TV',
				'UG',
				'UA',
				'AE',
				'US',
				'UM',
				'UY',
				'UZ',
				'VU',
				'VE',
				'VN',
				'VG',
				'VI',
				'WF',
				'EH',
				'YE',
				'ZM',
				'ZW',
			];
		}

		if (!$countries) {
			$countries[] = 'CZ';
		}

		return array_intersect($countries, parent::doGetSupportedCountries());
	}

	protected function doSyncSettings(): bool
	{
		// Create or update existing FAPI form.
		$settings = $this->meta->loadSettings();
		$formId = $settings['form']['id'] ?? null;
		$api = $this->getApi();

		// Check whether FAPI form really exists in FAPI.
		$form = null;
		if ($formId) {
			try {
				$form = $api->getForm($formId, [
					'with_payment_methods' => 1,
				]);
				// check if form is not deleted and exists
				if (!(isset($form['id']) && $form['id'] == $formId && !$form['deleted'])) {
					// form not exist or is deleted
					$formId = null;
					$form = null;
				}
				// Update of current form value withing gate settings by real setting at the gate.
				$settings['form'] = $form;
			} catch (Exception $e) {
				$formId = null;
			}
		}

		$callbackUrl = MWS()->getUrl_Ajax();
		$trigger_ordered = [
			'event' => 'paid',
			'action' => 'url-notification',
			'notification_url' => add_query_arg(
				[
					AUTOMANAGED_TAG => 1,
					'action' => 'mws_gate_callback',
					'gw' => $this->getId(),
					'operation' => 'paid',
				],
				$callbackUrl
			),
		];
		$trigger_cancelled = [
			'event' => 'cancelled',
			'action' => 'url-notification',
			'notification_url' => add_query_arg(
				[
					AUTOMANAGED_TAG => 1,
					'action' => 'mws_gate_callback',
					'gw' => $this->getId(),
					'operation' => 'cancelled',
				],
				$callbackUrl
			),
		];
		$orderUrl = MWS()->getUrl_Cart(MwsOrderStep::ThankYou);
		$thanksUrl = add_query_arg(['success' => 1, 'gw' => $this->getId()], $orderUrl);
		$errorUrl = add_query_arg(['success' => 0, 'gw' => $this->getId()], $orderUrl);

		$formDefaults = [
			'thanks_url' => $thanksUrl,
			'error_url' => $errorUrl,
			'mioweb_eshop' => true,
			'mioweb_eshop_url' => get_home_url(),
			'allow_region_cz' => true,
			'allow_region_sk' => true,
			'allow_region_eu' => true,
			'allow_region_world' => true,
			'currency_setting' => 'choice',
			'allowed_currencies' => array_map('strtoupper', MwsCurrencyEnum::getAll()),
			'allow_cash' => true,
			'allow_collect_on_delivery' => true,
			'allow_wire' => true,
			'allow_terms' => false,
			//'purposes' => [],
			'allow_simplified' => false,
			'new_layout' => false,
			'show_field_phone' => true,
			'print_phone_on_invoice' => true,
		];

		// FAPI form does not exist or is not accessible. Create new one.
		if (!$form) {
			try {
				mwshoplog(__METHOD__ . ' creating FAPI form', MWLL_DEBUG, 'paygate');
				$form = $api->createForm(array_replace($formDefaults, [
					'name' => sprintf(_x('MioWeb - %s', 'name of FAPI form', 'mwshop'), get_bloginfo('name')) . ' [' . date('Y/m/d G:i') . ']',
					'triggers' => [
						$trigger_ordered,
						$trigger_cancelled,
					],
				]));
				$settings['form'] = $form;
				$formId = $form['id'];
				mwshoplog(sprintf(__('Vytvořen nový FAPI formulář [%d]', 'mwshop'), $formId), MWLL_INFO, 'paygate');
			} catch (Exception $e) {
				$formId = null;
				mwshoplog(sprintf(__('Chyba při vytváření FAPI formuláře:', 'mwshop')) . ' ' . $e->getMessage() . ' ' . __METHOD__, MWLL_ERROR, 'paygate');
			}
		} else {
			// Updating form
			mwshoplog(__METHOD__ . " updating FAPI form [$formId]", MWLL_DEBUG, 'paygate');

			//Update hooks, callbacks, triggers.
			try {
				// Update automatically managed triggers, preserve other triggers.
				$triggers = [$trigger_ordered, $trigger_cancelled];
				foreach ($form['triggers'] ?? [] as $trigger) {
					// skip automanaged triggers
					if ($trigger['action'] === 'url-notification' && (strpos($trigger['notification_url'], AUTOMANAGED_TAG) !== false)) {
						continue;
					}
					$triggers[] = $trigger;
				}

				// Store new settings
				$form = $api->updateForm($formId, array_replace($formDefaults, [
					'triggers' => $triggers,
				]));
				$settings['form'] = $form;
				mwshoplog(sprintf(__('FAPI formulář [%d] aktualizován', 'mwshop'), $formId), MWLL_INFO, 'paygate');
			} catch (Exception $e) {
				$formId = null;
				mwshoplog(sprintf(__('Chyba při aktualizaci FAPI formuláře [%d]:', 'mwshop'), $formId) . ' ' . $e->getMessage() . ' ' . __METHOD__, MWLL_ERROR, 'paygate');
			}
		}

		$this->meta->saveSettings($settings);

		return (bool) $formId;
	}

	public function isConnected(): bool
	{
		$api = $this->getApi();
		try {
			$user = $api->getCurrentUser();

			return isset($user['id']) && is_int($user['id']);
		} catch (Exception $e) {
		}

		return false;
	}

	private function loadPurposesFromCart(array $cartPurposes): array
	{
		$purposes = [];
		foreach ($cartPurposes as $purpose) {
			$purposes[] = [
				'form_purpose_id' => $purpose,
				'text' => '',
				'checked' => true,
			];
		}

		return $purposes;
	}

	private function getItemsFromOrderItems(OrderItem ...$items): array
	{
		$formId = $this->getForm()['id'];

		return array_map(function (OrderItem $item) use ($formId) {
			$prices = $item->getPrices();
			$result = [
				'name' => $item->getName(),
				'type' => $item->getType() !== null ? MwsOrderItemType::getFapiType($item->getType()) : null,
				'count' => $item->getCount(),
				'prices' => array_map(function (MwsPrice $price) {
					return [
						'type' => 'one_time',
						'price' => $price->getPriceVatIncluded(),
						'currency_code' => strtoupper($price->getCurrency()),
					];
				}, $prices),
				'vat' => $item->getPrice(MWS()->getDefaultCurrency('key'))->getVatPercentage(),
				'including_vat' => true,
			];

			$codes = $item->getCodes();
			if ($codes) {
				$result['code'] = $codes->getCode(MwsProductCode::Filing); // posible connect with product in FAPI by FAPI product ID
				$result['accounting_code'] = $codes->getCode(MwsProductCode::Financial);
				$result['pohoda_accounting'] = $codes->getCode(MwsProductCode::Assignment);
				$result['pohoda_centre'] = $codes->getCode(MwsProductCode::Center);
				$result['pohoda_store'] = $codes->getCode(MwsProductCode::Stock);
				$result['pohoda_stock_item'] = $codes->getCode(MwsProductCode::StockItem);
			}

			// @deprecated connect old products with products in FAPI for form triggers
			if (!isset($result['code']) || !$result['code']) {
				$syncData = get_post_meta($item->getProductId(), 'mwshop_sync_fapi_' . $formId, true);
				if ($syncData && isset($syncData['id'])) {
					$result['code'] = $syncData['id'];
				}
			}

			return $result;
		}, $items);
	}

	public function recountCart(MwsCart $cart, bool $includeShippingPrice, bool $ignoreSimplifiedInvoice, bool $includeRounding = false, bool $applyReverseCharge = false)
	{
		try {
			$form = $this->getForm();
			$currency = $cart->getCurrency();

			$orderItems = $this->prepareOrderItems($cart, $includeShippingPrice, $includeRounding, $applyReverseCharge, true);
			$contact = $cart->getInvoiceContact();
			$company = $contact->getCompany();
			$data = [
				'form' => $form['id'],
				'only_calculate' => true,
				'currency' => strtoupper($currency),
				'simplified' => $ignoreSimplifiedInvoice ? false : $cart->useSimplifiedInvoice(),
				'reverse_charge' => $applyReverseCharge && $cart->shouldApplyReverseCharge(),
				'first_name' => 'Josef',
				'last_name' => 'Novák',
				'email' => 'josef.novak@example.com',
				'phone' => '+420 123 456 789',
				'company' => $company !== null ? $company->getName() : null,
				'ic' => $company !== null ? $company->getId() : null,
				'dic' => $company !== null ? $company->getVatId() : null,
				'ic_dph' => $company !== null ? $company->getVatId() : null,
				'address' => [
					'street' => 'Ulice 1',
					'city' => 'Město',
					'zip' => '123 45',
					'country' => $cart->getInvoiceCountry(),
				],
				'shipping_address' => [
					'name' => 'Josef',
					'surname' => 'Novák',
					'street' => 'Ulice 1',
					'city' => 'Město',
					'zip' => '123 45',
					'country' => $cart->getShippingCountry(),
				],

				'items' => $this->getItemsFromOrderItems(...$orderItems),
				// @TODO is possible make own rounding
				'round_precision' => 2,
				'round_function' => 'round', // ceil, floor, round
			];

			$data = apply_filters('mw_gateway_fapi_create_order_data', $data, $cart);
			\assert(is_array($data));

			$order = $this->getApi()->createOrder($data);

			// Get back currency according to FAPI -> if form setting is changed in FAPI
			$currency = MwsCurrencyEnum::checkedValue(strtolower($order['currency']), $currency);

			// update card prices by recounted data
			$cartItems = $cart->getItems()->getAll();
			foreach ($order['items'] as $key => $fapiItem) {
				$orderItem = $orderItems[$key] ?? null; // @TODO add better mapping -> now it counts with preserving order
				if (!$orderItem) {
					break;
				}
				if ($orderItem->isProduct()) {
					$cartItem = array_shift($cartItems);
					$product = $cartItem->getProduct();
					$cartItem->setStoredPrice(new MwsPrice(
						$fapiItem['unit_price'],
						$fapiItem['vat'],
						$currency
					));
					$cartItem->setStoredShopPrice($product->getPrice()->asCurrency($currency));
					$cartItem->setStoredProductPrice($product->getPrice());
					// Total price in final currency
					$cartItem->setStoredTotalPrice(MwsPrice::createByFields(
						$fapiItem['total_price_including_vat'],
						$fapiItem['total_price'],
						$fapiItem['vat'],
						$currency
					));
				} elseif ($orderItem->getType() === MwsOrderItemType::Shipping) {
					$cart->setShippingPrice(MwsPrice::createByFields(
						$fapiItem['total_price_including_vat'],
						$fapiItem['total_price'],
						$fapiItem['vat'],
						$currency
					));
				} elseif ($orderItem->getType() === MwsOrderItemType::Rounding) {
					$cart->setRounding(MwsPrice::createByFields(
						$fapiItem['total_price_including_vat'],
						$fapiItem['total_price'],
						$fapiItem['vat'],
						$currency
					));
				}
			}

			$cart->setStoredTotalPrice(MwsPrice::createByFields(
				$order['total_price_including_vat'],
				$order['total_price'],
				0,
				$currency
			));
		} catch (\MwShop\FapiClient\Rest\InvalidStatusCodeException $e) {
			if ($e->getMessage() === '[400] Simplified invoice can only be issued when the total price does not exceed 10000 CZK.') {
				throw new MwsUserException(__('Zjednodušený doklad lze využít pouze pro objednávky do 10000Kč včetně DPH. Je potřeba zadat fakturační údaje.', 'mwshop'));
			}

			throw $e;
		} catch (Exception $e) {
			mwshoplog(__('Chyba při přepočítávání košíku pomocí FAPI formuláře:', 'mwshop') . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');

			throw $e;
		}
	}

	// @TODO refactor
	protected function doMakeOrder(MwsCart $cart): array
	{
		mwshoplog(__METHOD__, MWLL_DEBUG);
		$res = [
			'success' => false,
		];

		try {
			$form = $this->getForm();

			$invoiceContact = $cart->getInvoiceContact();
			$company = $invoiceContact->getCompany();

			$shippingContact = $cart->getShippingContact();

			$currency = $cart->getCurrency();

			$documentItems = $this->prepareOrderItems($cart, true, true, true, true);
			$data = [
				'form' => $form['id'],
				'simplified' => $cart->useSimplifiedInvoice(),
				'currency' => strtoupper($currency),

				'form_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
				'form_title' => get_bloginfo('name'),

				'first_name' => $invoiceContact->getPerson()->getFirstName(),
				'last_name' => $invoiceContact->getPerson()->getLastName(),
				'email' => $invoiceContact->getEmail(),
				'phone' => ($shippingContact ? $shippingContact->getPhone() : null) ?: $invoiceContact->getPhone(),
				'company' => $company ? $company->getName() : null,
				'ic' => $company ? $company->getId() : null,
				'dic' => $company ? $company->getTaxId() : null,
				'ic_dph' => $company ? $company->getVatId() : null,
				'address' => [
					'street' => $invoiceContact->getAddress()->getStreet(),
					'city' => $invoiceContact->getAddress()->getCity(),
					'zip' => $invoiceContact->getAddress()->getZip(),
					'country' => $invoiceContact->getAddress()->getCountry(),
				],
				'shipping_address' => $shippingContact ? [
					'name' => $shippingContact->getPerson()->getFirstName(),
					'surname' => $shippingContact->getPerson()->getLastName(),
					'street' => $shippingContact->getAddress()->getStreet(),
					'city' => $shippingContact->getAddress()->getCity(),
					'zip' => $shippingContact->getAddress()->getZip(),
					'country' => $shippingContact->getAddress()->getCountry(),
				] : [],
				'items' => $this->getItemsFromOrderItems(...$documentItems),
				'notes' => $cart->getNote(),
				'reverse_charge' => $cart->shouldApplyReverseCharge(),
				'exchange_rate' => MWS()->getCurrencyConversionRate($currency, MWS()->getDefaultCurrency('key')),
				'exchange_rate_czk' => MWS()->getCurrencyConversionRate($currency, 'czk'),
				'purposes' => $this->loadPurposesFromCart($cart->getPurposes()),

				// @TODO is possible make own rounding
				'round_precision' => 2,
				'round_function' => 'round', // ceil, floor, round
			];

			//mwshoplog(json_encode($data['items']), MWLL_DEBUG, 'order');

			//Payment type
			$paymentMethod = $cart->getPaymentMethod();
			\assert($paymentMethod instanceof MwsPaymentMethod);
			$canPayOnline = false;
			$data['bank'] = null;
			switch ($paymentMethod->getType()) {
				case MwsPayType::Wire:
					$data['payment_type'] = 'wire';
					$data['bank'] = 'wire';

					break;
				case MwsPayType::CreditCard:
					$data['payment_type'] = 'credit card';
					$canPayOnline = true;

					break;
				case MwsPayType::WireOnline:
					$data['payment_type'] = 'wire';
					$data['bank'] = $paymentMethod->getBank();
					$canPayOnline = true;

					break;
				case MwsPayType::Sms:
					$data['payment_type'] = 'sms';
					$canPayOnline = true;

					break;
				case MwsPayType::Twisto:
					$data['payment_type'] = 'twisto';
					$canPayOnline = true;

					break;
				case MwsPayType::PayPal:
					$data['payment_type'] = 'paypal';
					$canPayOnline = true;

					break;
				case MwsPayType::Bitcoin:
					$data['payment_type'] = 'bitcoin';
					$canPayOnline = true;

					break;
				case MwsPayType::Cod:
					$data['payment_type'] = 'collect on delivery';

					break;
				default:
					throw new MwsException(sprintf(__('Nepodporovaná platební metoda [%s].', 'mwshop'), $paymentMethod->getType()));
			}

			$data = apply_filters('mw_gateway_fapi_create_order_data', $data, $cart);
			\assert(is_array($data));

			mwshoplog('[FAPI] payment_type=' . $data['payment_type'] . ' (bank=' . ($data['bank'] ?: '') . ')', MWLL_DEBUG, 'paygate');

			$api = $this->getApi();
			try {
				$order = $api->createOrder($data);
			} catch (\MwShop\FapiClient\Rest\InvalidStatusCodeException $e) {
				if ($e->getMessage() === '[400] Simplified invoice can only be issued when the total price does not exceed 10000 CZK.') {
					throw new MwsUserException(__('Zjednodušený doklad lze využít pouze pro objednávky do 10000Kč včetně DPH. Je potřeba zadat fakturační údaje.', 'mwshop'));
				}

				throw $e;
			}

			$idInvoice = $order['invoice'] ?? null;
			if ($idInvoice) {
				// Is URL redirection for payment present?
				if (isset($order['next_url'])) {
					$res['nextUrl'] = $order['next_url'];
				} elseif ($cart->getThxPage() !== null) {
					$res['nextUrl'] = $cart->getThxPage();
				}
				// to use redirect url element of quick shop
				if ($cart->getThxPage() !== null && !$canPayOnline) {
					$res['nextUrl'] = $cart->getThxPage();
				}

				if (isset($order['stripe'])) {
					// contains session_id and public_key values
					$res['stripe'] = $order['stripe'];
				}

				if (isset($order['twisto'])) {
					$res['twisto'] = $order['twisto'];
				}

				$idInvoice = $order['invoice'];
				$numOrder = 0;
				$invoice = null;
				try {
					$invoice = $api->getInvoice($idInvoice);
					$numOrder = $invoice['number'];
				} catch (Exception $e) {
				}
				$orderNum = $invoice['number'];

				$orderObj = $this->createOrderBase($cart, $orderNum);
				// Prepare specific data of gate concerning new order.
				$orderObj->setGateOrderData([
					'idForm' => $order['form'],
					'idOrder' => $order['id'],
					'dataOrder' => $order,
					'idInvoice' => $idInvoice,
					'dataInvoice' => $invoice,
				]);

				$orderObj->setTotal([
					'price' => $invoice['total'],
					'price_novat' => $invoice['total'] - $invoice['total_vat'],
					'price_native' => $invoice['total_native'],
					'price_novat_native' => $invoice['total_native'] - $invoice['total_vat_native'],
				]);

				// TODO #3642
				$orderGate = $orderObj->getGateLive();

				$orderObj->setShowVat($orderGate?->showVat());
				$orderObj->setInvoiceContact($orderGate?->getInvoiceContact());
				$orderObj->setShippingContact($orderGate?->getShippingContact());

				// URL for direct payments
				$formPath = $form['path'] ?? '';
				$payUrl = $formPath && $canPayOnline ? 'https://form.fapi.cz/gateway/?' . http_build_query(['id' => $formPath, 'vs' => $orderNum]) : '';
				mwshoplog('[FAPI] urlDirectPay=' . $payUrl, MWLL_DEBUG, 'paygate');
				$orderObj->setDirectPaymentUrl($payUrl);

				$orderObj->save();

				$res['orderId'] = $orderObj->getId();
				$res['orderNum'] = $orderNum;

				$res['success'] = true;

				// @TODO send summary after payment?
				$orderGate->sendSummary();
			}
		} catch (\Throwable $e) {
			$exceptionMsg = $e->getMessage();
			$previous = $e->getPrevious();

			if ($previous !== null) {
				$exceptionMsg .= ' | ' . $previous->getMessage();
			}

			mwshoplog(__('Chyba při vytváření objednávky pomocí FAPI formuláře:', 'mwshop') . ' ' . $exceptionMsg, MWLL_ERROR, 'paygate');
			$res['message'] = __('Objednávku se nepodařilo vytvořit.', 'mwshop') . " <br />\n" . $exceptionMsg;
		}

		return $res;
	}

	public function processPayments(): bool
	{
		return true;
	}

	public function doGetSupportedPayTypes(): array
	{
		return [
			MwsPayType::Cod,
			MwsPayType::CreditCard,
			MwsPayType::PayPal,
			MwsPayType::Sms,
			MwsPayType::Wire,
			MwsPayType::Twisto,
			MwsPayType::Bitcoin,
			MwsPayType::WireOnline,
		];
	}

	// @TODO refactor -> is possible move to up
	public function getOrderFromThankYou(): ?Order
	{
		mwshoplog(__METHOD__, MWLL_DEBUG);

		$orderNum = $_REQUEST['vs'] ?? null;
		if ($orderNum) {
			return OrderRepository::getOrderByOrderNum($orderNum);
		}

		return null;
	}

	// @TODO refactor
	public function orderPaid(): ?Order
	{
		$res = null;

		$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
		if ($id) {
			$api = $this->getApi();
			try {
				$invoice = $api->getInvoice($id);
				if ($invoice === null) {
					mwshoplog(sprintf(__('Faktura [%d] nebyla nalezena ve FAPI', 'mwshop'), $id), MWLL_WARNING, 'paygate');

					return $res;
				}

				if ($invoice['paid']) {
					//Paid
					$orderNum = $invoice['number'];
					mwshoplog("Invoice {$orderNum} of id=[$id] was paid in FAPI.", MWLL_DEBUG, 'paygate');
					$order = OrderRepository::getOrderByOrderNum($orderNum);
					if ($order === null) {
						mwshoplog(sprintf(__('Objednávka pro FAPI fakturu {%s} [%d] nebyla nalezena ve FAPI', 'mwshop'), $orderNum, $id), MWLL_WARNING, 'paygate');

						return $res;
					}

					$paidAt = $invoice['paid_on']; //2016-05-05 12:11:11
					$paidAt = new \DateTimeImmutable($paidAt);
					//$paidOn->setTimezone(new DateTimeZone('GMT')); //times are saved in UTC

					//Update order
					$order->setPaid();
					$order->setPaidAt($paidAt);
					if ($order->getShippingType() === MwsShippingElectronic::id) {
						$order->changeStatus(MwsOrderStatus::Closed, true);
					}
					$order->save();
					$res = $order;
					mwshoplog(sprintf(__('FAPI objednávka {%s} odbavena jako UHRAZENÁ.', 'mwshop'), $orderNum), MWLL_INFO, 'paygate');
				} else {
					//Not paid
					mwshoplog(
						sprintf(__('FAPI faktura {%s} [%d] není ve FAPI označena jako UHRAZENÁ. Podvádíš?', 'mwshop'), $invoice['number'], $id),
						MWLL_WARNING,
						'paygate'
					);
				}
			} catch (Exception $e) {
				mwshoplog(sprintf(__('Chyba při zpracování FAPI faktury [%d]:', 'mwshop'), $id) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');

				return $res;
			}
		} else {
			mwshoplog(sprintf(__('Chybí důležitý argument "id" ve FAPI callbacku.', 'mwshop')) . ' ' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_WARNING, 'paygate');
		}

		return $res;
	}

	// @TODO refactor
	public function orderCancelled(): ?Order
	{
		$res = null;

		$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
		if ($id) {
			$api = $this->getApi();
			try {
				$invoice = $api->getInvoice($id);
				if ($invoice === null) {
					mwshoplog(sprintf(__('Faktura [%d] nebyla nalezena ve FAPI', 'mwshop'), $id), MWLL_WARNING, 'paygate');

					return $res;
				}

				if ($invoice['cancelled']) {
					//Paid
					$orderNum = $invoice['number'];
					mwshoplog("FAPI invoice {$orderNum} of id=[$id] was cancelled at gateway.", MWLL_DEBUG, 'paygate');
					$order = OrderRepository::getOrderByOrderNum($orderNum);
					if ($order === null) {
						mwshoplog(sprintf(__('Objednávka pro FAPI fakturu {%s} [%d] nebyla nalezena ve FAPI', 'mwshop'), $orderNum, $id), MWLL_WARNING, 'paygate');

						return $res;
					}
					//Update order
					$order->changeStatus(MwsOrderStatus::Cancelled);
					$res = $order;
					mwshoplog(sprintf(__('FAPI objednávka {%s} odbavena jako STORNOVANÁ.', 'mwshop'), $orderNum), MWLL_INFO, 'paygate');
				} else {
					//Not cancelled
					mwshoplog(
						sprintf(__('FAPI faktura {%s} [%d] není ve FAPI označena jako STORNOVANÁ. Podvádíš?', 'mwshop'), $invoice['number'], $id),
						MWLL_WARNING,
						'paygate'
					);
				}
			} catch (Exception $e) {
				mwshoplog(sprintf(__('Chyba při zpracování FAPI faktury [%d]:', 'mwshop'), $id) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');

				return $res;
			}
		} else {
			mwshoplog(sprintf(__('Chybí důležitý argument "id" ve FAPI callbacku.', 'mwshop')) . ' ' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_WARNING, 'paygate');
		}

		return $res;
	}

	public function loadOrderGate(IOrder $order, ?array $preloadedData = null): ?OrderGate
	{
		try {
			if (array_key_exists($order->getId(), $this->_preloadedInvoices)) {
				$preloadedData = $this->_preloadedInvoices[$order->getId()];
			} else {
				$saveOrderData = $order->getGateOrderData();
				if (!isset($saveOrderData['idInvoice'])) {
					throw new MwsException('Id of invoice is missing from the order.');
				}
				$idInvoice = $saveOrderData['idInvoice'];
				$api = $this->getApi();
				$preloadedData = $api->getInvoice($idInvoice);
			}

			if ($preloadedData === null) {
				throw new \Exception('Invoice not found.');
			}
		} catch (\Throwable $e) {
			mwshoplog(sprintf(__('FAPI fakturu se nepodařilo načíst z FAPI [%s]:', 'mwshop'), $order->getNumber()) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');

			return null;
		}

		return new MwsOrderGate_Fapi($order, $preloadedData);
	}

	public function preloadOrdersGateLive(Order ...$orders): void
	{
		$invoiceIds = [];
		$orderMap = [];
		/** @var Order $order */
		foreach ($orders as $order) {
			$saveOrderData = $order->getGateOrderData();
			if (!isset($saveOrderData['idInvoice'])) {
				continue; //ignore unloadable orders
			}
			$invoiceIds[$order->getId()] = $saveOrderData['idInvoice'];
			$orderMap[$saveOrderData['idInvoice']] = $order;
		}

		$api = $this->getApi();
		try {
			$preloadedData = $api->getInvoices(['id' => $invoiceIds]);
			foreach ($preloadedData as $data) {
				$invoiceId = $data['id'];
				if (isset($orderMap[$invoiceId])) {
					$order = $orderMap[$invoiceId];
					$this->_preloadedInvoices[$order->getId()] = $data;
					unset($invoiceIds[$order->getId()]);
				}
			}
			foreach ($invoiceIds as $orderId => $invoiceId) {
				$this->_preloadedInvoices[$orderId] = null;
			}
		} catch (Exception $e) {
			mwshoplog(sprintf(__('FAPI faktury se nepodařilo přednačíst:', 'mwshop')) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');
		}
	}
}

class MwsOrderGate_Fapi extends OrderGate
{
	const UrlFapiUI = 'https://web.fapi.cz';

	private $_data;

	private $api = null;

	public function __construct(IOrder $order, array $data)
	{
		parent::__construct($order);

		$this->_data = $data;
	}

	public function updateInvoiceData()
	{
		$gateData = $this->_order->getGateOrderData();

		if ($gateData !== null) {
			$gateOrderData = $gateData;
			$gateOrderData['dataInvoice'] = $this->_data;
			$this->_order->setGateOrderData($gateOrderData);
			$this->_order->setShowVat(isset($this->_data['vat_date']));
			$this->_order->setInvoiceContact($this->getInvoiceContact());
			$this->_order->setShippingContact($this->getShippingContact());
			$this->_order->save();
		}
	}

	public function getItems(): array
	{
		$orderItems = $this->_order->getItems()->getAll();
		$nativeCurrency = $this->_order->getNativeCurrency();
		$items = [];
		foreach ($this->_data['items'] ?? [] as $key => $item) {
			$prices = [
				$this->getCurrency() => new MwsPrice((float) $item['price'], $item['vat'] ?? 0, $this->getCurrency(), $item['including_vat'] ?? true),
			];

			$productId = null;
			$miniupsell = false;
			$itemType = $item['type'] !== null ? MwsOrderItemType::getReverseFapiType($item['type']) : null;
			$weight = null;

			// TODO better item mapping. If item is deleted or changed on invoice = problem.
			if (isset($orderItems[$key])) {
				$productId = $orderItems[$key]->getProductId();
				$miniupsell = $orderItems[$key]->isMiniupsell();
				$itemType = $orderItems[$key]->getType() ?: null;
				$weight = $orderItems[$key]->getWeight();

				if ($nativeCurrency !== null && $this->getCurrency() !== $nativeCurrency) {
					$prices[$nativeCurrency] = $orderItems[$key]->getPrice($nativeCurrency);
				}
			}

			$items[] = new OrderItem(
				$item['name'],
				$itemType,
				$prices,
				$item['count'],
				null,
				$productId,
				false,
				$miniupsell,
				$weight
			);
		}

		return $items;
	}

	public function formatContactEditing(): string
	{
		return ''; // <a target="_blank" href="'.$this->getCustomer()->getEditUrl().'">'.__('Upravit zákazníka ve FAPI', 'mwshop').'</a> // @TODO why is commented?
	}

	public function getDocuments(): array
	{
		$invoiceId = $this->_data['id'] ?? 0;
		if (!$invoiceId) {
			return [];
		}

		$invoices = [$this->_data];
		$processedParentIds = [];
		$this->getChildInvoices($invoices, $processedParentIds);

		return array_map(function (array $invoice) {
			$invoiceId = $invoice['id'];
			$title = null;
			if (isset($invoice['type'])) {
				switch ($invoice['type']) {
					case 'proforma':
						$title = sprintf(__('%s - zálohová faktura', 'mwshop'), $invoice['number']);

						break;
					case 'payment_confirmation':
						$title = sprintf(__('%s - přijetí platby', 'mwshop'), $invoice['number']);

						break;
					case 'invoice':
						$title = sprintf(__('%s - faktura', 'mwshop'), $invoice['number']);

						break;
					case 'simplified_invoice':
						$title = sprintf(__('%s - zjednodušená daň. doklad', 'mwshop'), $invoice['number']);

						break;
					case 'credit_note':
						$title = sprintf(__('%s - opravný daň. doklad', 'mwshop'), $invoice['number']);

						break;
				}
			}

			return new OrderGateDocument_Fapi(
				$title ?: sprintf(__('Doklad č. %s', 'mwshop'), $invoice['number']),
				new \DateTimeImmutable($invoice['created_on'], new \DateTimeZone('Europe/Prague')),
				new \DateTimeImmutable($invoice['payday_date']),
				($invoice['vat_date'] ?? null) !== null ? new \DateTimeImmutable($invoice['vat_date']) : null,
				$this::UrlFapiUI . '/invoice/pdf/' . $invoiceId,
				$this::UrlFapiUI . '/invoice/detail/' . $invoiceId,
				$this::UrlFapiUI . '/invoice/update/' . $invoiceId,
				MwsPrice::createByFields(
					$invoice['total'],
					$invoice['total'] - $this->_data['total_vat'],
					0.0,
					$this->getCurrency()
				),
				(bool) $invoice['paid']
			);
		}, $invoices);
	}

	/**
	 * Get all dependent invoices or documents within FAPI for documents already in array.
	 */
	private function getChildInvoices(&$invoices, &$processedIds)
	{
		$toProcess = [];
		foreach ($invoices as $invoice) {
			if (isset($invoice['id']) && !in_array($invoice['id'], $processedIds)) {
				$toProcess[] = $invoice['id'];
			}
		}
		while (!empty($toProcess)) {
			$parentId = array_shift($toProcess);
			if ($parentId) {
				$processedIds[] = $parentId;
				$newInvoices = $this->getApi()->getInvoices(['parent' => $parentId]);
				foreach ($newInvoices as $newInvoice) {
					$invoices[] = $newInvoice;
					if (isset($newInvoice['id']) && !empty($newInvoice['id'])) {
						$newInvoiceId = $newInvoice['id'];
						if (!in_array($newInvoiceId, $processedIds) && !in_array($newInvoiceId, $toProcess)) {
							$toProcess[] = $newInvoiceId;
						}
					}
				}
			}
		}
	}

	public function printOrderInvoiceInfo(): string
	{
		$content = '';
		$docs = $this->getDocuments();
		$content .= mwAdminComponents::title([
			'text' => __('Faktury', 'mwshop'),
		]);

		if (empty($docs)) {
			$content .= mwAdminComponents::messageBox(__('Není vytvořen žádný doklad.', 'cms_member'), ['type' => 'info_gray']);
		} else {
			foreach ($docs as $doc) {
					$content .= '<div class="mws_order_document">';
					$content .= '<div class="mws_order_document_title">';
					$content .= $this->_order->isArchived() ? null : '<a href="' . $doc->getDownloadUrl() . '" target="_blank">' . esc_html($doc->getName()) . '</a>';
					$content .= '<div class="mws_order_document_editbar">';
				if (!$this->_order->isArchived()) {
					if ($doc->getDetailUrl()) {
						$content .= mwAdminComponents::iconLink([
							'icon' => 'eye',
							'target' => '_blank',
							'title' => __('Detail faktury', 'mwshop'),
							'link' => $doc->getDetailUrl(),
						]);
					}
					if ($doc->getEditUrl()) {
						$content .= mwAdminComponents::iconLink([
							'icon' => 'edit-2',
							'target' => '_blank',
							'title' => __('Upravit fakturu', 'mwshop'),
							'link' => $doc->getEditUrl(),
						]);
					}
					if ($doc->getDownloadUrl()) {
						$content .= mwAdminComponents::iconLink([
							'icon' => 'file-text',
							'target' => '_blank',
							'title' => __('Zobrazit fakturu', 'mwshop'),
							'link' => $doc->getDownloadUrl(),
						]);
					}
				}
				$content .= '</div>';
				$content .= '</div>';

				$content .= '<div class="mw_setting_sidebar_info_row mws_order_hide_on_cancel">';
				$content .= '<span>' . __('Stav', 'mwshop') . ':</span>';
				$content .= $doc->isPaid() ? '<span class="mws_order_payed">' . __('Zaplaceno', 'mwshop') . '</span>' : '<span class="mws_order_notpayed">' . __('Nezaplaceno', 'mwshop') . '</span>';
				$content .= '</div>';

				$content .= '<div class="mw_setting_sidebar_info_row">';
				$content .= '<span>' . __('Cena', 'mwshop') . ':</span>';
				$content .= '<span><strong>' . $doc->getPrice()->htmlPriceVatIncluded() . '</strong></span>';
				$content .= '</div>';

				$content .= '</div>';
			}
		}

		return $content;
	}


	public function getCustomer(): MwsCustomer
	{
		$customerId = $this->_data['client'] ?? null;

		return new MwsCustomer_Fapi(
			$this->_data['customer']['email'] ?? '',
			$customerId ? (self::UrlFapiUI . '/client/detail/' . $customerId) : null,
			$customerId ? (self::UrlFapiUI . '/client/update/' . $customerId) : null
		);
	}

	public function getSupplier(): ?MwsContact
	{
		return new MwsContact(
			$this->_data['supplier']['email'] ?? '',
			$this->_data['supplier']['phone'] ?? null,
			null,
			new MwsCompany(
				$this->_data['supplier']['name'] ?? '',
				$this->_data['supplier']['id'] ?? null,
				$this->_data['supplier']['dic'] ?? null
			),
			new MwsAddress(
				$this->_data['supplier']['address']['country'] ?? '',
				$this->_data['supplier']['address']['city'] ?? '',
				$this->_data['supplier']['address']['zip'] ?? '',
				$this->_data['supplier']['address']['street'] ?? ''
			)
		);
	}

	public function getInvoiceContact(): MwsContact
	{
		$customer = $this->_data['customer'];
		$address = $customer['address'];

		return new MwsContact(
			$customer['email'] ?? '',
			$this->_order->getGateOrderData()['dataOrder']['phone'] ?? null,
			new MwsPerson(
				$customer['first_name'] ?? '',
				$customer['last_name'] ?? ''
			),
			$customer['ic'] ?? false ? new MwsCompany(
				$customer['name'] ?? '', // @TODO company name?
				$customer['ic'] ?? null,
				$customer['dic'] ?? null,
				$customer['ic_dph'] ?? null
			) : null,
			new MwsAddress(
				$address['country'] ?? '',
				$address['city'] ?? '',
				$address['zip'] ?? '',
				$address['street'] ?? ''
			)
		);
	}

	public function getShippingContact(): ?MwsContact
	{
		$address = $this->_data['customer']['shipping_address'] ?? null;
		if (!$address) {
			return null;
		}

		return new MwsContact(
			'',
			null, // @TODO phone
			new MwsPerson(
				$address['name'] ?? '',
				$address['surname'] ?? ''
			),
			null,
			new MwsAddress(
				$address['country'] ?? '',
				$address['city'] ?? '',
				$address['zip'] ?? '',
				$address['street'] ?? ''
			)
		);
	}

	protected function doGetPrice(): MwsPrice
	{
		return MwsPrice::createByFields(
			$this->_data['total'],
			$this->_data['total'] - $this->_data['total_vat'],
			0, // total price not contains vat percent
			$this->doGetCurrency()
		);
	}

	protected function doGetNativePrice(): MwsPrice
	{
		$defaultCurrency = MWS()->getDefaultCurrency('key');

		/*
		if($this->doGetCurrency() == $defaultCurrency)
		{
			return $this->doGetPrice();
		}
		elseif(isset($this->_data['total_'.$defaultCurrency]))
		{
			return MwsPrice::createByFields(
				$this->_data['total_'.$defaultCurrency],
				$this->_data['total_'.$defaultCurrency] - $this->_data['total_vat_'.$defaultCurrency],
				0, // total price not contains vat percent
				$defaultCurrency
			);
		}
		else
		{ */
			return MwsPrice::createByFields(
				$this->_data['total_native'],
				$this->_data['total_native'] - $this->_data['total_vat_native'],
				0, // total price not contains vat percent
				$defaultCurrency
			);
		/* } */
	}

	protected function doGetCurrency(): string
	{
		return strtolower($this->_data['currency']);
	}

	protected function doGetBankAccount(string $currency): ?MwsBankAccount
	{
		$number = $this->_data['supplier']['bank_account'] ?? null;

		if ($number !== null) {
			$iban = $this->_data['supplier']['iban'] ?? null;
			$swift = $this->_data['supplier']['swift'] ?? null;

			return new MwsBankAccount($number, $iban, $swift);
		}

		return null;
	}

	/** @deprecated Remove when MwsOrder is gone */
	public function showVat(): bool
	{
		return isset($this->_data['vat_date']);
	}

	protected function doIsPaid(): bool
	{
		return (bool) ($this->_data['paid'] ?? false);
	}

	protected function doGetPaidOn(): ?int
	{
		if (isset($this->_data['paid_on'])) {
			$datetime = new DateTimeImmutable($this->_data['paid_on'], new DateTimeZone('Europe/Prague'));
			// get the unix timestamp (adjusted for the site's timezone already)
			$timestamp = $datetime->format('U');

			return $timestamp;
		}

		return null;
	}

	/** Get API for FAPI. */
	private function getApi(): \MwShop\FapiClient\FapiClient
	{
		if ($this->api === null) {
			$api = null;
			$gw = $this->getGateway();
			if ($gw) {
				try {
					$api = $gw->sharedInstance()->getApi(); //direct call into the MwsGatewayImpl_Fapi class
				} catch (Exception $e) {
				}
			}

			$this->api = $api;
		}

		return $this->api;
	}

	public function sendSummary(): void
	{
		$emailType = MwsEmailType::NewOrder;
		$file = MWS()->getEmailAttachment(MwsEmailType::NewOrder, $this->_order);
		$attachments = $file ? [$file] : [];
		if (MWS()->isEmailEnabled($emailType)) {
			$this->getInvoiceContact()->sendMail(
				MWS()->getEmailSubject($emailType, $this->_order),
				MWS()->getEmailContent($emailType, $this->_order),
				$attachments
			);
		}
	}

}

class MwsCustomer_Fapi implements MwsCustomer
{

	private $_email;

	private $_detailUrl;

	private $_editUrl;

	public function __construct(string $email, ?string $detailUrl, ?string $editUrl)
	{
		$this->_email = $email;
		$this->_detailUrl = $detailUrl;
		$this->_editUrl = $editUrl;
	}

	public function getEmail(): string
	{
		return $this->_email;
	}

	public function getDetailUrl(): ?string
	{
		return $this->_detailUrl;
	}

	public function getEditUrl(): ?string
	{
		return $this->_editUrl;
	}

}

class OrderGateDocument_Fapi implements OrderGateDocument
{

	private $_name;

	private $_createdTimestamp;

	private \DateTimeInterface $_dueDate;

	private ?\DateTimeInterface $_taxableSupplyAt;

	private $_downloadUrl;

	private $_detailUrl;

	private $_editUrl;

	private $_price;

	private $_paid;

	public function __construct(
		string $name,
		\DateTimeInterface $createdAt,
		\DateTimeInterface $dueDate,
		?\DateTimeInterface $taxableSupplyAt,
		string $downloadUrl,
		?string $detailUrl,
		?string $editUrl,
		MwsPrice $price,
		bool $paid
	)
	{
		$this->_name = $name;
		$this->_createdTimestamp = $createdAt;
		$this->_dueDate = $dueDate;
		$this->_taxableSupplyAt = $taxableSupplyAt;
		$this->_downloadUrl = $downloadUrl;
		$this->_detailUrl = $detailUrl;
		$this->_editUrl = $editUrl;
		$this->_price = $price;
		$this->_paid = $paid;
	}

	public function getName(): string
	{
		return $this->_name;
	}

	public function getCreatedAt(): \DateTimeInterface
	{
		return $this->_createdTimestamp;
	}

	public function getDueDate(): \DateTimeInterface
	{
		return $this->_dueDate;
	}

	public function getTaxableSupplyAt(): ?\DateTimeInterface
	{
		return $this->_taxableSupplyAt;
	}

	public function getDownloadUrl(): string
	{
		return $this->_downloadUrl;
	}

	public function getDetailUrl(): ?string
	{
		return $this->_detailUrl;
	}

	public function getEditUrl(): ?string
	{
		return $this->_editUrl;
	}

	public function getPrice(): MwsPrice
	{
		return $this->_price;
	}

	public function isPaid(): bool
	{
		return $this->_paid;
	}

	public function sendToCustomer(string $emailType = MwsEmailType::PayedOrder): void
	{
		throw new MwsException('Not implemented.');
	}

}
