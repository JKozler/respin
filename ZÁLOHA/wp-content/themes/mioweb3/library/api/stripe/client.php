<?php

use Mioweb\Shop\Order\Order;
use Nette\Utils\Json;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;

class mwAPIConnectItemClient_stripe extends mwAPIConnectItemClient implements MwsPaymentGateway
{

	private $_stripe;

	private function getStripe(): \Stripe\StripeClient
	{
		if (!$this->_stripe) {
			$option = $this->_mwAPIConnectItem->getOption();
			$this->_stripe = $this->createStripe($option['secretKey']);
		}

		return $this->_stripe;
	}

	private function createStripe(string $secretKey): \Stripe\StripeClient
	{
//		\Stripe\Stripe::setVerifySslCerts(false); // For localhost debugging

		return new \Stripe\StripeClient([
			'api_key' => $secretKey,
		]);
	}

	public function createPayment(Order $order, string $paymentMethodType, ?string $thxPageUrl = null): MwsPayment
	{
		// https://stripe.com/docs/api/checkout/sessions/create?lang=php
		$priceInCents = $order->getPrice()->multiply(100);
		$currency = $priceInCents->getCurrency();
		$itemNames = [];
		foreach ($order->getItems()->getAll() as $orderItem) {
			if (in_array($orderItem->getType(), [MwsOrderItemType::Discount, MwsOrderItemType::Rounding])) {
				continue;
			}
			$itemNames[] = $orderItem->getName();
		}

		$productData = $itemNames ? [
			// Name cannot be empty
			'name' => implode(', ', $itemNames),
		] : [];

		$thxPageUrlEncoded = urlencode($thxPageUrl ?? '1');
		$paymentData = [
			'payment_intent_data' => [
//				'setup_future_usage' => 'on_session',
				'description' => $order->getNumber(),
			],
			'payment_method_types' => ['card'], // @TODO from payment method
			'locale' => explode('_', get_locale())[0],
			'line_items' => [ // inspired by fapi
				[
					'price_data' => [
						'product_data' => $productData,
						'unit_amount_decimal' => $priceInCents->getPriceVatIncluded(),
						'currency' => strtoupper($currency),
					],
					'quantity' => 1,
				],
			],
			'mode' => 'payment',
			'success_url' => $order->getCheckPaymentUrl($thxPageUrlEncoded),
			'cancel_url' => $order->getCheckPaymentUrl($thxPageUrlEncoded),
			'metadata' => [
				'order_number' => $order->getNumber(),
			],
			'customer_email' => $order->getInvoiceContact()->getEmail(),
//			'customer' => '',
		];
		$response = $this->getStripe()->checkout->sessions->create($paymentData);
		$paymentUrl = $response->url ?? null;
		$payment = new MwsPayment(null, $order, $paymentMethodType, $this, $response->id, $paymentUrl);
		$payment->setData([
			'nextUrl' => $paymentUrl,
			'stripe' => [
				'session_id' => $response->id,
				'public_key' => $this->_mwAPIConnectItem->getOption()['publishableKey'],
			],
		]);

		return $payment;
	}

	public function loadPaymentStatus(MwsPayment $payment): string
	{
		$response = $this->getStripe()->checkout->sessions->retrieve($payment->getPaymentGatewayPaymentId());

		$status = $response->payment_status;
		switch ($status) {
			case Session::PAYMENT_STATUS_PAID:
				return MwsPaymentStatus::Paid;
			case Session::PAYMENT_STATUS_UNPAID:
				return MwsPaymentStatus::Canceled;
		}

		throw new MwsException(sprintf('Status %s not mapped.', $status));
	}

	public function getEnabledPaymentMethodTypes(?string $currency = null): array
	{
		// @TODO load from stripe
		return [
			MwsPayType::CreditCard,
		];
	}

	/** @return string[] */
	public function getEnabledCurrenciesForPaymentMethod(MwsPaymentMethod $paymentMethod): array
	{
		// TODO
		return MWS()->getCurrencies();
	}

	public function getSupportedPaymentMethodTypes(): array
	{
		return [
			MwsPayType::CreditCard,
		];
	}

	/** @return string[] */
	public function getSupportedCurrencies(): array
	{
		// TODO
		return MWS()->getCurrencies();
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
		$secretKey = $tosave['secretKey'] ?? '';

		if ($secretKey) {
			$tosave['secretKey'] = trim($tosave['secretKey']);

			try {
				$stripe = $this->createStripe($tosave['secretKey']);
				$stripe->accounts->all();
			} catch (\Throwable $e) {
				\Tracy\Debugger::log($e, \Tracy\ILogger::EXCEPTION);
				mwMessages()->error($this->getError($e), 'stripe_log');

				return false;
			}

			return true;
		}

		mwMessages()->error(__('Musíte vyplnit tajný klíč.', 'cms'));

		return false;
	}

	private function getError(\Throwable $e): string
	{
		$defaultError = __('Připojení se nezdařilo. Zkontrolujte správnost zadaných údajů.', 'cms_ve');

		if ($e instanceof AuthenticationException) {
			return __('Přihlašovací údaje nejsou správné.', 'cms_ve');
		}

		return $defaultError;
	}
}
