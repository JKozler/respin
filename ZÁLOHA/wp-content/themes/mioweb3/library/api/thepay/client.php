<?php declare(strict_types=1);

use Mioweb\Library\Api\ThePay\Exceptions\ThePayException;
use Mioweb\Shop\Order\Order;
use ThePay\ApiClient\Model\Address;
use ThePay\ApiClient\Model\Collection\PaymentMethodCollection;
use ThePay\ApiClient\Model\CreatePaymentCustomer;
use ThePay\ApiClient\Model\CreatePaymentItem;
use ThePay\ApiClient\Model\CreatePaymentParams;
use ThePay\ApiClient\TheClient;
use ThePay\ApiClient\TheConfig;
use ThePay\ApiClient\ValueObject\PaymentMethodCode;
use ThePay\ApiClient\ValueObject\PaymentState;

class mwAPIConnectItemClient_thepay extends mwAPIConnectItemClient implements MwsPaymentGateway
{

	public const CURRENCIES_MAP = [
		MwsCurrencyEnum::czk => 'CZK',
		MwsCurrencyEnum::eur => 'EUR',
		MwsCurrencyEnum::pln => 'PLN',
		MwsCurrencyEnum::usd => 'USD',
		MwsCurrencyEnum::gbp => 'GBP',
	];

	public const PAYMENT_METHODS_MAP = [
		MwsPayType::CreditCard => PaymentMethodCode::CARD,
		MwsPayType::WireOnline => PaymentMethodCode::TRANSFER,
		MwsPayType::Bitcoin => PaymentMethodCode::BITCOIN,
	];

	public const ITEM_TYPE_MAP = [
		MwsOrderItemType::Discount => 'discount',
		MwsOrderItemType::Shipping => 'delivery',
	];

	private ?TheClient $_thepay = null;

	private ?PaymentMethodCollection $_activePaymentMethods = null;

	public function checkSavedSetting(&$tosave): bool
	{
		$merchantId = $tosave['merchantId'] ?? '';
		$projectId = $tosave['projectId'] ?? '';
		$apiPassword = $tosave['apiPassword'] ?? '';

		if ($merchantId && $projectId && $apiPassword) {
			$tosave['merchantId'] = $merchantId = trim($merchantId);
			$tosave['projectId'] = $projectId = trim($projectId);
			$tosave['apiPassword'] = $apiPassword = trim($apiPassword);

			$thepay = $this->createThePay(
				$merchantId,
				(int) $projectId,
				$apiPassword,
				(bool) ($tosave['testMode'] ?? false)
			);

			try {
				$thepay->getProjects();
			} catch (Exception $e) {
				if (str_contains($e->getMessage(), 'Ip address')) {
					$httpHost = $_SERVER['HTTP_HOST'] ?? null;
					$serverAddress = $httpHost !== null ? (dns_get_record($httpHost, \DNS_A)[0]['ip'] ?? null) : null;

					if ($serverAddress !== null) {
						mwMessages()->error(
							sprintf(__('Propojení se nezdařilo. IP adresa %s není v nastavení platební brány povolena.
										Tuto IP adresu musíte povolit v nastavení platební brány podle nápovědy,
										kterou najdete <a href="https://web.thepay.cz/implementace-2/" target="_blank">zde</a>.', 'cms_ve'), $serverAddress),
							'thepay_log'
							);
					} else {
						mwMessages()->error(
							__('Propojení se nezdařilo. IP adresa serveru, na kterém je váš web umístěn, není v nastavení platební brány povolena.
								Tuto IP adresu zjistíte u provozovatele Vašeho hostingu a musíte ji povolit v nastavení platební brány podle nápovědy
								<a href="https://web.thepay.cz/implementace-2/" target="_blank">zde</a>.', 'cms_ve'),
							'thepay_log'
							);
					}
				} else {
					mwMessages()->error(__('Připojení se nezdařilo. Zkontrolujte správnost zadaných údajů.', 'cms_ve') . ' (' . $e->getMessage() . ')', 'thepay_log');
				}

				return false;
			}

			return true;
		}

		mwMessages()->error(__('Je potřeba vyplnit všechny údaje.', 'cms_ve'));

		return false;
	}

	public function createPayment(Order $order, string $paymentMethodType, ?string $thxPageUrl = null): MwsPayment
	{
		$paymentMethod = self::PAYMENT_METHODS_MAP[$paymentMethodType] ?? null;
		$currency = $order->getCurrency();
		$currencyCode = self::CURRENCIES_MAP[$order->getCurrency()] ?? null;
		// @TODO what if $paymentMethod and $currencyCode is null?

		$params = new CreatePaymentParams(
			(int) $order->getPrice()->multiply(100)->getPriceVatIncluded(),
			$currencyCode,
			$order->getId()
		);

		foreach ($order->getItems()->getAll() as $orderItem) {
			$item = new CreatePaymentItem(
				self::ITEM_TYPE_MAP[$orderItem->getType()] ?? 'item',
				$orderItem->getName(),
				(int) $orderItem->getTotalPrice($currency)->multiply(100)->getPriceVatIncluded(),
				$orderItem->getCount(),
				(($orderItem->getCodes() !== null) && ($ean = $orderItem->getCodes()->getCode(MwsProductCode::EAN))) ?
					$ean : null
			);
			$params->addItem($item);
		}

		$customerContact = $order->getInvoiceContact();
		$payerAddress = $customerContact->getAddress();

		$customer = new CreatePaymentCustomer(
			$customerContact->getPerson()->getFirstName(),
			$customerContact->getPerson()->getLastName(),
			$customerContact->getEmail(),
			$customerContact->getPhone() ?: null,
			new Address(
				$payerAddress->getCountry(),
				$payerAddress->getCity(),
				$payerAddress->getZip(),
				$payerAddress->getStreet()
			)
		);

		$params->setCustomer($customer);
		$params->setOrderId($order->getNumber());
		$params->setReturnUrl($order->getCheckPaymentUrl(urlencode($thxPageUrl ?? '1')));
		$params->setNotifUrl($order->getCheckPaymentUrl());

		try {
			$payment = $this->getThePay()->createPayment($params, $paymentMethod);
		} catch (Exception $e) {
			throw new ThePayException(sprintf(
				'Payment not created on ThePay. ThePay status code: %d, payment parameters: %s response: %s',
				$e->getCode(),
				print_r($params, true),
				$e->getMessage()
			));
		}

		$paymentUrl = $payment->getPayUrl();
		$payment = new MwsPayment(null, $order, $paymentMethodType, $this, $params->getUid()->getValue(), $paymentUrl);
		$payment->setData(['nextUrl' => $paymentUrl]);

		return $payment;
	}

	public function loadPaymentStatus(MwsPayment $payment): string
	{
		try {
			$retPayment = $this->getThePay()->getPayment($payment->getPaymentGatewayPaymentId());
		} catch (\Throwable $e) {
			throw new ThePayException($e->getMessage());
		}

		switch ($retPayment->getState()) {
			case PaymentState::EXPIRED:
			case PaymentState::PREAUTH_CANCELLED:
			case PaymentState::PREAUTH_EXPIRED:
				return MwsPaymentStatus::Canceled;
			case PaymentState::WAITING_FOR_CONFIRMATION:
			case PaymentState::WAITING_FOR_PAYMENT:
			case PaymentState::PREAUTHORIZED:
				return MwsPaymentStatus::Created;
			case PaymentState::PAID:
				return MwsPaymentStatus::Paid;
		}

		throw new ThePayException(sprintf('Status %s not mapped.', $retPayment->getState()));
	}

	public function getName(): string
	{
		return $this->_mwAPIConnectItem->getName();
	}

	public function getId(): string
	{
		return $this->_mwAPIConnectItem->getId();
	}

	private function getActivePaymentMethods(): PaymentMethodCollection
	{
		if (!$this->_activePaymentMethods) {
			try {
				$this->_activePaymentMethods = $this->getThePay()->getActivePaymentMethods();
			} catch (\Throwable $e) {
				throw new ThePayException($e->getMessage());
			}
		}

		return $this->_activePaymentMethods;
	}

	public function getEnabledPaymentMethodTypes(?string $currency = null): array
	{
		$paymentMethodTypes = [];

		if (!$currency) {
			foreach (MWS()->getCurrencies() as $currency) {
				foreach ($this->getEnabledPaymentMethodTypes($currency) as $paymentMethodType) {
					if (!in_array($paymentMethodType, $paymentMethodTypes)) {
						$paymentMethodTypes[] = $paymentMethodType;
					}
				}
			}

			return $paymentMethodTypes;
		}

		if (!isset(self::CURRENCIES_MAP[$currency])) {
			return [];
		}

		$flippedArray = array_flip(self::PAYMENT_METHODS_MAP);

		try {
			foreach ($this->getThePay()->getActivePaymentMethods() as $thePayMethod) {
				if (
					in_array(self::CURRENCIES_MAP[$currency], $thePayMethod->getAvailableCurrencies(), true)
					&& isset($flippedArray[$thePayMethod->getCode()])
				) {
					$paymentMethodTypes[] = $flippedArray[$thePayMethod->getCode()];
				}
			}
		} catch (\Throwable $e) {
			throw new ThePayException($e->getMessage());
		}

		return $paymentMethodTypes;
	}

	public function getEnabledCurrenciesForPaymentMethod(MwsPaymentMethod $paymentMethod): array
	{
		$enabledCurrencies = MWS()->getCurrencies();
		$supportedCurrencies = $this->getSupportedCurrencies();
		$currenciesForCheck = array_intersect($supportedCurrencies, $enabledCurrencies);

		$thePayMethod = $this->getActivePaymentMethods()->get(self::PAYMENT_METHODS_MAP[$paymentMethod->getType()]);

		if (!$thePayMethod) {
			return [];
		}

		$supportedCurrencies = [];
		$flippedArray = array_flip(self::CURRENCIES_MAP);

		foreach ($thePayMethod->getAvailableCurrencies() as $thePayCurrency) {
			if (isset($flippedArray[$thePayCurrency])) {
				$supportedCurrencies[] = $flippedArray[$thePayCurrency];
			}
		}

		return array_intersect($supportedCurrencies, $currenciesForCheck);
	}

	public function getSupportedPaymentMethodTypes(): array
	{
		return array_keys(self::PAYMENT_METHODS_MAP);
	}

	/** @inheritDoc */
	public function getSupportedCurrencies(): array
	{
		return array_keys(self::CURRENCIES_MAP);
	}

	private function getThePay(): TheClient
	{
		if (!$this->_thepay) {
			$option = $this->_mwAPIConnectItem->getOption();
			// @TODO what if option not set or invalid?
			$this->_thepay = $this->createThePay(
				$option['merchantId'],
				(int) $option['projectId'],
				$option['apiPassword'],
				(bool) ($option['testMode'] ?? false)
			);
		}

		return $this->_thepay;
	}

	private function createThePay(string $merchantId, int $projectId, string $apiPassword, bool $testMode = false): TheClient
	{
		$config = new TheConfig(
			$merchantId,
			$projectId,
			$apiPassword,
			$testMode ? 'https://demo.api.thepay.cz/' : 'https://api.thepay.cz/',
			$testMode ? 'https://demo.gate.thepay.cz/' : 'https://gate.thepay.cz/',
			explode('_', get_locale())[0]
		);

		return new TheClient($config);
	}

	public function isDebugMode(): bool
	{
		$option = $this->_mwAPIConnectItem->getOption();

		return ($this->_mwAPIConnectItem->isConnected() && isset($option['testMode']));
	}
}
