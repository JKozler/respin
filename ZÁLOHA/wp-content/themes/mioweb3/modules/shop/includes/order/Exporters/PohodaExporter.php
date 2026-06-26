<?php declare(strict_types=1);

namespace Mioweb\Shop\Order\Exporters;

use Mioweb\Shop\Exceptions\MissingInvoiceContactException;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\OrderItems;
use MwsOrderStatus;
use MwsPayType;
use Nette\Utils\Strings;
use Spatie\ArrayToXml\ArrayToXml;

class PohodaExporter implements IOrderExporter
{

	private array $supplierIdentity;

	public function __construct()
	{
		$this->supplierIdentity = $this->formatSupplier();
	}

	/** @param iterable<Order> $orders */
	public function export(iterable $orders): string
	{
		$ordersTmp = [];

		// TODO use Generator and write to a file gradually to reduce memory usage
		foreach ($orders as $order) {
			$ordersTmp[] = $this->formatOrder($order);
		}

		$supplierContact = MWS()->getSupplierContact();
		$supplierCompany = $supplierContact !== null ? $supplierContact->getCompany() : null;

		return ArrayToXml::convert([
			'dat:dataPackItem' => $ordersTmp, // TODO what if $ordersTmp is empty?
			'_attributes' => [
				'note' => home_url(),
				'application' => 'Mioweb',
				'version' => '2.0',
				'xmlns:dat' => 'http://www.stormware.cz/schema/version_2/data.xsd',
				'xmlns:ord' => 'http://www.stormware.cz/schema/version_2/order.xsd',
				'xmlns:typ' => 'http://www.stormware.cz/schema/version_2/type.xsd',
				'id' => 'mioweb-export-' . time(),
				'ico' => $supplierCompany !== null ? $supplierCompany->getId() : '',
			],
		], 'dat:dataPack', true, 'UTF-8');
	}

	public function getIdentifier(): string
	{
		return 'pohoda';
	}

	public function getName(): string
	{
		return __('Ekonomický systém Pohoda', 'mwshop');
	}

	public function getFileExtension(): string
	{
		return 'xml';
	}

	/** @return mixed[] */
	private function formatOrder(Order $order): array
	{
		$createdAt = $order->getCreatedAt();
		try {
			$invoiceContact = $order->getInvoiceContact();
		} catch (MissingInvoiceContactException $e) {
			$invoiceContact = null;
		}
		try {
			$shippingContact = $order->getShippingContact();
		} catch (MissingInvoiceContactException $e) {
			$shippingContact = null;
		}

		$customerCompany = $invoiceContact !== null ? $invoiceContact->getCompany() : null;
		$invoicePerson = $invoiceContact !== null ? $invoiceContact->getPerson() : null;
		$invoiceAddress = $invoiceContact !== null ? $invoiceContact->getAddress() : null;

		$shippingPerson = $shippingContact !== null ? $shippingContact->getPerson() : $invoicePerson;
		$shippingAddress = $shippingContact !== null ? $shippingContact->getAddress() : $invoiceAddress;

		$price = $order->getPrice();
		$nativePrice = $order->getNativePrice();
//		$isForeignPriceItem = $price !== null && $nativePrice !== null && $price->getCurrency() !== $nativePrice->getCurrency();
		$isForeignPriceItem = $price !== null && strtoupper($price->getCurrency()) !== 'CZK';

//		if ($isForeignPriceItem) {
//			throw new MwsUserException(__('V tuto chvíli je možné ve formátu Pohoda exportovat pouze objednávky uskutečněné v českých korunách. Prosím nastavte filtr "Měna" na hodnotu "CZK - Kč".'));
//		}

		$orderItems = $this->formatOrderItems($order->getItems());

		$header = [
			'ord:orderType' => 'receivedOrder',
			'ord:numberOrder' => $order->getNumber(),
			'ord:date' => $createdAt->format('Y-m-d'),
//			'ord:dateFrom' => $createdAt->format('Y-m-d'),
//					'ord:dateTo' => $createdAt->format('Y-m-d'),
//					'ord:text' => $order->getCustomerNote(),
			'ord:note' => $order->getCustomerNote(),
			'ord:partnerIdentity' => [
				'typ:address' => [
					'typ:company' => $customerCompany !== null ? $customerCompany->getName() : '',
					'typ:name' => $invoicePerson !== null ? $invoicePerson->getFullName() : '',
					'typ:city' => $invoiceAddress !== null ? $invoiceAddress->getCity() : '',
					'typ:street' => $invoiceAddress !== null ? $invoiceAddress->getStreet() : '',
					'typ:zip' => $invoiceAddress !== null ? $invoiceAddress->getZip() : '',
					'typ:country' => $invoiceAddress !== null ? ['typ:ids' => $invoiceAddress->getCountry()] : '',
					'typ:ico' => $customerCompany !== null ? $customerCompany->getId() : '',
					'typ:dic' => $customerCompany !== null ? $customerCompany->getTaxId() : '',
					'typ:icDph' => $customerCompany !== null ? $customerCompany->getVatId() : '',
//							'typ:VATPayerType' => '', // Can be "payer" or "non-payer"
					'typ:phone' => $invoiceContact !== null ? $invoiceContact->getPhone() : '',
					'typ:email' => $invoiceContact !== null ? $invoiceContact->getEmail() : '',
				],
				'typ:shipToAddress' => [
//							'typ:company' => $customerCompany !== null ? $customerCompany->getName() : '',
					'typ:name' => $shippingPerson !== null ? $shippingPerson->getFullName() : '',
					'typ:city' => $shippingAddress !== null ? $shippingAddress->getCity() : '',
					'typ:street' => $shippingAddress !== null ? $shippingAddress->getStreet() : '',
					'typ:zip' => $shippingAddress !== null ? $shippingAddress->getZip() : '',
					'typ:country' => $shippingAddress !== null ? ['typ:ids' => $shippingAddress->getCountry()] : '',
					'typ:phone' => $shippingContact !== null ? $shippingContact->getPhone() : '',
					'typ:email' => $invoiceContact !== null ? $invoiceContact->getEmail() : '',
				],
			],
			'ord:myIdentity' => $this->supplierIdentity,
			'ord:paymentType' => [
				'typ:ids' => Strings::truncate($order->getPaymentTitle(), 32, ''),
				'typ:paymentType' => isset($order->getPayment()['type']) ? (MwsPayType::getPohodaType((string) $order->getPayment()['type']) ?? '') : '',
			],
//					'ord:priceLevel' => [
//						'typ:ids' => $order->getDiscountCode()
//					],
			'ord:isExecuted' => $order->getStatus() === MwsOrderStatus::Closed ? 'true' : 'false',
			'ord:isDelivered' => $order->getStatus() === MwsOrderStatus::Closed ? 'true' : 'false',
//					'ord:isReserved' => '',
			'ord:carrier' => [
				'typ:ids' => Strings::truncate($order->getShipping()['name'] ?? '', 20, ''),
			],
			'ord:iShopName' => $this->getShopName(),
		];

		if ($order->isCancelled()) {
			$header['ord:storno'] = 'cancelledDocument';

			$historyItems = $order->getHistory();
			$cancelledAt = null;

			foreach (array_reverse($historyItems, true) as $historyItem) {
				if (($historyItem->getEvent() ?? null) === 'order_status_change_to_' . MwsOrderStatus::Cancelled) {
					$cancelledAt = $historyItem->getCreatedAt();
				}
			}

			if ($cancelledAt !== null) {
				$header['ord:dateCancellation'] = $cancelledAt->format('Y-m-d');
			}
		}


//		$vatRateType = $price !== null ? $this->resolveVatRateType($price->getVatPercentage()) : 'none';
//
//		if ($vatRateType !== 'none') {
//			if ($vatRateType === 'third') {
//				$vatRateType = '3';
//			}
//
//			$vatRateTypeKey = 'price' . ucfirst($vatRateType);
//
//			$summaryData[$vatRateTypeKey] = $price !== null ? $price->getPriceVatExcluded() : 0.0;
//			$summaryData[$vatRateTypeKey . 'VAT'] = $price !== null ? $price->getVatAmount() : 0.0;
//			$summaryData[$vatRateTypeKey . 'Sum'] = $price !== null ? $price->getPriceVatIncluded() : 0.0;
//		}
//
//
//		$summary = ['ord:homeCurrency' => $summaryData];

		if ($isForeignPriceItem) {
			$summary = [
				'ord:foreignCurrency' => [
					'typ:currency' => ['typ:ids' => $order->getCurrency()],
					'typ:rate' => 1 / $order->getExchangeRate(), // TODO test
					'typ:amount' => 1,
					'typ:priceSum' => $price !== null ? $price->getPriceVatIncluded() : 0.0,
				],
			];
		} else {
			$summary = [
				'ord:homeCurrency' => [
					'typ:priceNone' => $price !== null ? $price->getPriceVatExcluded() : 0.0,
					'typ:priceHigh' => $price !== null ? $price->getPriceVatExcluded() : 0.0,
					'typ:priceHighVAT' => $price !== null ? $price->getVatAmount() : 0.0,
					'typ:priceHighSum' => $price !== null ? $price->getPriceVatIncluded() : 0.0,
				],
			];
		}

		return [
			'_attributes' => [
				'version' => '2.0',
				'id' => $order->getId(),
			],
			'ord:order' => [
				'_attributes' => [
					'version' => '2.0',
				],
				'ord:orderHeader' => $header,
				'ord:orderDetail' => ['ord:orderItem' => $orderItems],
				'ord:orderSummary' => $summary,
			],
		];
	}

	private function getShopName(): string
	{
		$maxLength = 35;
		$url = MWS()->getUrl_Home();
		if (mb_strlen($url) <= $maxLength) {
			return $url;
		}

		$shopName = get_bloginfo('name');

		return Strings::truncate($shopName, $maxLength - 1);
	}

	private function formatOrderItems(OrderItems $orderItems)
	{
		$order = $orderItems->getOrder();
		$currency = $order->getCurrency();
		$nativeCurrency = $order->getNativeCurrency();

		$result = [];

		foreach ($orderItems->getAll() as $item) {
			$price = $item->getPrice($currency);
			$nativePrice = $nativeCurrency !== null ? $item->getPrice($nativeCurrency) : null;
			$isForeignPriceItem = $price !== null && $nativePrice !== null && $price->getCurrency() !== $nativePrice->getCurrency();

			$rateVat = $price === null || $order->isReverseChargeApplied()
				? 'none'
				: $this->resolveVatRateType($price->getVatPercentage());

			$count = $item->getCount();

			$result[] = [
				'ord:text' => $item->getName(),
				'ord:quantity' => $count,
//				'ord:payVAT' => false, // false == price is without VAT
				'ord:rateVAT' => $rateVat,
				'ord:percentVAT' => $price !== null ? $price->getVatPercentage() : 0.0,
				$isForeignPriceItem ? 'ord:foreignCurrency' : 'ord:homeCurrency' => [
					'typ:unitPrice' => $price !== null ? $price->getPriceVatExcluded() : 0.0,
					'typ:price' => $price !== null ? $price->getPriceVatExcluded() * $count : 0.0,
					'typ:priceVAT' => $price !== null ? $price->getVatAmount() * $count : 0.0,
					'typ:priceSum' => $price !== null ? $price->getPriceVatIncluded() * $count : 0.0,
				],
				'ord:PDP' => $order->isReverseChargeApplied() ? 'true' : 'false',
			];
		}

		return $result;
	}

	private function resolveVatRateType(int $vatPercentage): string
	{
		if ($vatPercentage === 21) {
			return 'high';
		}
		if ($vatPercentage === 15) {
			return 'low';
		}
		if ($vatPercentage === 10) {
			return 'third';
		}
		if ($vatPercentage === 0) {
			return 'none';
		}

		return /*'third'*/ 'historyHigh';

//		throw new PohodaInvalidVatRateException(sprintf('Vat rate "%s" is not valid', $vatPercentage));
	}

	private function formatSupplier(): array
	{
		$supplierContact = MWS()->getSupplierContact();
		$supplierCompany = $supplierContact !== null ? $supplierContact->getCompany() : null;
		$supplierPerson = $supplierContact !== null ? $supplierContact->getPerson() : null;
		$supplierAddress = $supplierContact !== null ? $supplierContact->getAddress() : null;

		return [
			'typ:address' => [
				'typ:company' => $supplierCompany !== null ? $supplierCompany->getName() : '',
				'typ:surname' => $supplierPerson !== null ? $supplierPerson->getLastName() : '',
				'typ:name' => $supplierPerson !== null ? $supplierPerson->getFirstName() : '',
				'typ:city' => $supplierAddress !== null ? $supplierAddress->getCity() : '',
				'typ:street' => $supplierAddress !== null ? $supplierAddress->getStreet() : '',
				'typ:zip' => $supplierAddress !== null ? $supplierAddress->getZip() : '',
				'typ:ico' => $supplierCompany !== null ? $supplierCompany->getId() : '',
				'typ:dic' => $supplierCompany !== null ? $supplierCompany->getTaxId() : '',
				'typ:icDph' => $supplierCompany !== null ? $supplierCompany->getVatId() : '',
				'typ:phone' => $supplierContact !== null ? $supplierContact->getPhone() : '',
				'typ:email' => $supplierContact !== null ? $supplierContact->getEmail() : '',
				'typ:www' => MWS()->getUrl_Home(),
			],
		];
	}

}
