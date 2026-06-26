<?php

use Mioweb\Shop\Order\Order;

interface MwsPaymentGateway
{

	public function createPayment(Order $order, string $paymentMethodType, ?string $thxPageUrl = null): MwsPayment;

	public function loadPaymentStatus(MwsPayment $payment): string;

	public function getName(): string;

	public function getId(): string;

	public function getEnabledPaymentMethodTypes(?string $currency = null): array;

	public function getEnabledCurrenciesForPaymentMethod(MwsPaymentMethod $paymentMethod): array;

	public function getSupportedPaymentMethodTypes(): array;

	/** @return MwsCurrencyEnum[] */
	public function getSupportedCurrencies(): array;

}
