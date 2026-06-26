<?php

use GoPay\Definition\Response\PaymentStatus;
use GoPay\Http\Response;
use Mioweb\Shop\Order\Order;

class mwAPIConnectItemClient_gopay extends mwAPIConnectItemClient implements MwsPaymentGateway
{

	public const PAYMENT_INSTRUMENTS_MAP = [
		MwsPayType::CreditCard => \GoPay\Definition\Payment\PaymentInstrument::PAYMENT_CARD,
		MwsPayType::WireOnline => \GoPay\Definition\Payment\PaymentInstrument::BANK_ACCOUNT,
		MwsPayType::Sms => \GoPay\Definition\Payment\PaymentInstrument::PREMIUM_SMS,
		MwsPayType::PayPal => \GoPay\Definition\Payment\PaymentInstrument::PAYPAL,
		MwsPayType::Bitcoin => \GoPay\Definition\Payment\PaymentInstrument::BITCOIN,
		MwsPayType::GooglePay => \GoPay\Definition\Payment\PaymentInstrument::GPAY,
		MwsPayType::ApplePay => \GoPay\Definition\Payment\PaymentInstrument::APPLE_PAY,
	];

	public const SWIFTS_MAP = [
		MwsBanks::Cz_csas => \GoPay\Definition\Payment\BankSwiftCode::CESKA_SPORITELNA,
		MwsBanks::Cz_rb => \GoPay\Definition\Payment\BankSwiftCode::RAIFFEISENBANK,
		MwsBanks::Cz_fb => \GoPay\Definition\Payment\BankSwiftCode::FIO_BANKA,
		MwsBanks::Cz_kb => \GoPay\Definition\Payment\BankSwiftCode::KOMERCNI_BANKA,
		MwsBanks::Cz_mb => \GoPay\Definition\Payment\BankSwiftCode::MBANK,
//		MwsBanks::Cz_moneta => '',
		MwsBanks::Cz_csob => \GoPay\Definition\Payment\BankSwiftCode::CSOB,
//		MwsBanks::Cz_equabank => '',
//		MwsBanks::Cz_sberbank => '',
		MwsBanks::Cz_unicredit => \GoPay\Definition\Payment\BankSwiftCode::UNICREDIT_BANK_CZ,
//		MwsBanks::Cz_era => \GoPay\Definition\Payment\BankSwiftCode::ERA,
		MwsBanks::Sk_sp => \GoPay\Definition\Payment\BankSwiftCode::SLOVENSKA_SPORITELNA,
		MwsBanks::Sk_uni => \GoPay\Definition\Payment\BankSwiftCode::UNICREDIT_BANK_SK,
		MwsBanks::Sk_csob => \GoPay\Definition\Payment\BankSwiftCode::CSOB_SK,
		MwsBanks::Sk_tatrabank => \GoPay\Definition\Payment\BankSwiftCode::TATRA_BANKA,
//		MwsBanks::Sk_sberbank => \GoPay\Definition\Payment\BankSwiftCode::SBERBANK_SLOVENSKO,
//		MwsBanks::Sk_otpbank => '',
		MwsBanks::Sk_pabank => \GoPay\Definition\Payment\BankSwiftCode::POSTOVA_BANKA,
		MwsBanks::Sk_vubbank => \GoPay\Definition\Payment\BankSwiftCode::VSEOBECNA_UVEROVA_BANKA,
//		MwsBanks::Sk_opt => '',
	];

	public const CURRENCIES_MAP = [
		MwsCurrencyEnum::czk => \GoPay\Definition\Payment\Currency::CZECH_CROWNS,
		MwsCurrencyEnum::eur => \GoPay\Definition\Payment\Currency::EUROS,
		MwsCurrencyEnum::pln => \GoPay\Definition\Payment\Currency::POLISH_ZLOTY,
		MwsCurrencyEnum::usd => \GoPay\Definition\Payment\Currency::US_DOLLAR,
//		MwsCurrencyEnum::rub => '',
		MwsCurrencyEnum::gbp => \GoPay\Definition\Payment\Currency::BRITISH_POUND,
	];

	const RESPONSE_ERROR_INVALID_AUTH = 202;

	private $_gopay = null;

	public function createPayment(Order $order, string $paymentMethodType, ?string $thxPageUrl = null): MwsPayment
	{
		$paymentInstrument = self::PAYMENT_INSTRUMENTS_MAP[$paymentMethodType] ?? null;
		// @TODO what if $paymentInstrument is null?
		$currency = $order->getCurrency();
		$priceInCents = $order->getPrice()->multiply(100);
		$items = [];
		foreach ($order->getItems() as $orderItem) {
			$itemTotalPriceInCents = $orderItem->getTotalPrice($currency)->multiply(100);
			$itemData = [
				'type' => [
					MwsOrderItemType::Discount => \GoPay\Definition\Payment\PaymentItemType::DISCOUNT,
					MwsOrderItemType::Shipping => \GoPay\Definition\Payment\PaymentItemType::DELIVERY,
				][$orderItem->getType()] ?? \GoPay\Definition\Payment\PaymentItemType::ITEM,
				'name' => $orderItem->getName(),
				'amount' => $itemTotalPriceInCents->getPriceVatIncluded(),
				'count' => $orderItem->getCount(),
				'vat_rate' => $itemTotalPriceInCents->getVatPercentage(),
			];
			$codes = $orderItem->getCodes();
			if ($codes && ($ean = $codes->getCode(MwsProductCode::EAN))) {
				$itemData['ean'] = $ean;
			}
			$items[] = $itemData;
		}

		$customerContact = $order->getInvoiceContact();
		$payerAddress = $customerContact->getAddress();
		$paymentData = [
			'payer' => [
				'default_payment_instrument' => $paymentInstrument,
				'allowed_payment_instruments' => [$paymentInstrument],
				'contact' => [
					'first_name' => $customerContact->getPerson()->getFirstName(),
					'last_name' => $customerContact->getPerson()->getLastName(),
					'email' => $customerContact->getEmail(),
					'phone_number' => $customerContact->getPhone(),
					'city' => $payerAddress->getCity(),
					'street' => $payerAddress->getStreet(),
					'postal_code' => $payerAddress->getZip(),
					'country_code' => MwsCountry::getAlpha3($payerAddress->getCountry()),
				],
			],
			'amount' => $priceInCents->getPriceVatIncluded(), // price in cents -> 100Kc -> 10000
			'currency' => strtoupper($currency),
			'order_number' => $order->getNumber(),
//			'order_description' => '', // @TODO some order info?
			'items' => $items,
//			'additional_params' => [],
			'callback' => [
				'return_url' => $order->getCheckPaymentUrl(urlencode($thxPageUrl ?? '1')),
				'notification_url' => $order->getCheckPaymentUrl(),
			],
		];
//		$bank = $paymentMethod->getBank();
//		if ($bank) {
//			$swift = self::SWIFTS_MAP[$bank] ?? null;
//			// @TODO what if not swift for bank?
//			if ($swift) {
//				$paymentData['payer']['default_swift'] = $swift;
//				$paymentData['payer']['allowed_swifts'] = [$swift];
//			}
//		}

		$response = $this->getGopay()->createPayment($paymentData);
		if (!$response->hasSucceed()) {
			throw new MwsException(sprintf(
				'Payment not created on GoPay. GoPay status code: %d, request: %s response: %s',
				$response->statusCode,
				print_r($paymentData, true),
				print_r((array) $response->json, true)
			));
		}

		$paymentUrl = $response->json['gw_url'];
		$payment = new MwsPayment(null, $order, $paymentMethodType, $this, $response->json['id'], $paymentUrl);
		$payment->setData(['nextUrl' => $paymentUrl]);

		return $payment;
	}

	public function loadPaymentStatus(MwsPayment $payment): string
	{
		$response = $this->getGopay()->getStatus($payment->getPaymentGatewayPaymentId());
		if (!$response->hasSucceed()) {
			throw new MwsException('Payment status not loaded.');
		}

		$status = $response->json['state'];
		switch ($status) {
			case PaymentStatus::CREATED:
				return MwsPaymentStatus::Created;
			case PaymentStatus::PAID:
				return MwsPaymentStatus::Paid;
			case PaymentStatus::CANCELED:
			case PaymentStatus::TIMEOUTED:
			case PaymentStatus::PAYMENT_METHOD_CHOSEN:
				return MwsPaymentStatus::Canceled;
		}

		throw new MwsException(sprintf('Status %s not mapped.', $status));
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

		foreach ($this->getEnabledPaymentInstruments($currency) as $paymentInstrument) {
			$paymentMethodType = array_flip(self::PAYMENT_INSTRUMENTS_MAP)[$paymentInstrument['paymentInstrument']] ?? null;
			if ($paymentMethodType && !in_array($paymentMethodType, $paymentMethodTypes)) {
				$paymentMethodTypes[] = $paymentMethodType;
			}
		}

		return $paymentMethodTypes;
	}

	/** @return string[] */
	public function getEnabledCurrenciesForPaymentMethod(MwsPaymentMethod $paymentMethod): array
	{
		$result = [];

		$enabledCurrencies = MWS()->getCurrencies();
		$supportedCurrencies = $this->getSupportedCurrencies();
		$currenciesForCheck = array_intersect($supportedCurrencies, $enabledCurrencies);

		foreach ($currenciesForCheck as $currency) {
			foreach ($this->getEnabledPaymentInstruments($currency) as $paymentInstrument) {
				$paymentMethodType = array_flip(self::PAYMENT_INSTRUMENTS_MAP)[$paymentInstrument['paymentInstrument']] ?? null;
				if ($paymentMethodType === $paymentMethod->getType()) {
					$result[] = $currency;

					break;
				}
			}
		}

		return $result;
	}

	public function getSupportedPaymentMethodTypes(): array
	{
		return array_keys(self::PAYMENT_INSTRUMENTS_MAP);
	}

	/** @return string[] */
	public function getSupportedCurrencies(): array
	{
		return array_keys(self::CURRENCIES_MAP);
	}

	public function getName(): string
	{
		return $this->_mwAPIConnectItem->getName();
	}

	public function getId(): string
	{
		return $this->_mwAPIConnectItem->getId();
	}

	public function checkSavedSetting(&$tosave): bool
	{
		$tosave['goid'] = trim($tosave['goid']);
		$tosave['clientId'] = trim($tosave['clientId']);
		$tosave['clientSecret'] = trim($tosave['clientSecret']);

		$gopay = $this->createGopay(
			$tosave['goid'],
			$tosave['clientId'],
			$tosave['clientSecret'],
			$tosave['testMode'] ?? false
		);
		$response = $gopay->getAuth()->authorize()->response;
		$success = $response->hasSucceed();
		if (!$success) {
			$errors = $this->getErrors($response);

			foreach ($errors as $error) {
				mwMessages()->error($error, 'gopay_log');
			}
		}

		return $success;
	}

	private function getErrors(Response $response): array
	{
		if ($response->hasSucceed()) {
			return [];
		}

		$defaultError = __('Připojení se nezdařilo. Zkontrolujte správnost zadaných údajů.', 'cms_ve');
		$result = [];

		if (isset($response->json['errors'])) {
			foreach ($response->json['errors'] as $error) {
				if (!isset($error['error_code'])) {
					continue;
				}

				if ($error['error_code'] === self::RESPONSE_ERROR_INVALID_AUTH) {
					$result[] = $error['message'] ?? $defaultError;
					$result[] = $error['message'] ?? $defaultError;
				}
			}
		}

		return $result ?: [$defaultError];
	}

	private function getEnabledPaymentInstruments(string $currency): array
	{
		if (!isset(self::CURRENCIES_MAP[$currency])) {
			return [];
		}
		$key = implode('_', [
			MWS_OPTION,
			'payment_gateway',
			$this->getId(),
			'enabled_payment_instruments',
			$currency,
		]);
		$enabledPaymentInstruments = get_transient($key);
		if ($enabledPaymentInstruments === false) {
			$goid = $this->getGopay()->getGopay()->getConfig('goid');
			$response = $this->getGopay()->getPaymentInstruments($goid, self::CURRENCIES_MAP[$currency]);
			if (!$response->hasSucceed()) {
				throw new MwsException('Payment instruments not loaded.');
			}
			$enabledPaymentInstruments = $response->json['enabledPaymentInstruments'] ?? [];
			set_transient($key, $enabledPaymentInstruments, 60 * 60 * 24); // 1 day
		}

		return $enabledPaymentInstruments;
	}

	private function getGopay(): \GoPay\Payments
	{
		if (!$this->_gopay) {
			$option = $this->_mwAPIConnectItem->getOption();
			// @TODO what if option not set or invalid?
			$this->_gopay = $this->createGopay(
				$option['goid'],
				$option['clientId'],
				$option['clientSecret'],
				$option['testMode'] ?? false
			);
		}

		return $this->_gopay;
	}

	private function createGopay(string $goid, string $clientId, string $clientSecret, bool $testMode = false): \GoPay\Payments
	{
		return \GoPay\payments([
			'goid' => $goid,
			'clientId' => $clientId,
			'clientSecret' => $clientSecret,
			'isProductionMode' => !$testMode,
			'language' => strtoupper(explode('_', get_locale())[0]),
		]);
	}

	public function isDebugMode(): bool
	{
		$option = $this->_mwAPIConnectItem->getOption();

		return ($this->_mwAPIConnectItem->isConnected() && isset($option['testMode']));
	}

}
