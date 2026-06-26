<?php declare(strict_types=1);

namespace Mioweb\Shop\Order\Exporters;

use Mioweb\Shop\Exceptions\MissingInvoiceContactException;
use Mioweb\Shop\Order\Order;
use MwsContact;
use Mioweb\Shop\Order\OrderItem;
use Mioweb\Shop\Order\OrderItems;
use MwsPrice;
use Spatie\ArrayToXml\ArrayToXml;

class XmlExporter implements IOrderExporter
{

	private ?MwsContact $supplierContact;

	public function __construct()
	{
		$this->supplierContact = MWS()->getSupplierContact();
	}

	/** @param iterable<Order> $orders */
	public function export(iterable $orders): string
	{
		$ordersTmp = [];

		// TODO use Generator and write to a file gradually to reduce memory usage
		foreach ($orders as $order) {
			$ordersTmp[] = $this->formatOrder($order);
		}

		return ArrayToXml::convert([
			'orderPackItem' => $ordersTmp ? ['order' => $ordersTmp] : [],
			'_attributes' => [
				'note' => home_url(),
				'application' => 'Mioweb',
				'version' => '1.0',
			],
		], 'orderPack', true, 'UTF-8');
	}

	public function getIdentifier(): string
	{
		return 'xml';
	}

	public function getName(): string
	{
		return __('XML', 'mwshop');
	}

	public function getFileExtension(): string
	{
		return 'xml';
	}

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

		$price = $order->getPrice();
		$nativePrice = $order->getNativePrice();

		$orderItems = $this->formatOrderItems($order->getItems());
		$paymentMethod = $order->getPayment() ?? [];
		$paymentMethod['title'] = $order->getPaymentTitle();

		return [
			'number' => $order->getNumber(),
			'status' => $order->getStatus(),
			'createdAt' => $createdAt->format('Y-m-d H:i:s'),
			'paidAt' => $order->getPaidAtDateFormatted(),
			'note' => $order->getCustomerNote(),
			'customer' => [
				'invoiceContact' => $invoiceContact !== null ? $invoiceContact->toArray() : null,
				'shippingContact' => $shippingContact !== null ? $shippingContact->toArray() : null,
			],
			'supplier' => [
				'contact' => $this->supplierContact !== null ? $this->supplierContact->toArray() : null,
			],
			'paymentMethod' => $paymentMethod,
			'shippingMethod' => $order->getShipping(),
			'eshopUrl' => MWS()->getUrl_Home(),
			'items' => ['item' => $orderItems],
			'discountCode' => $order->getDiscountCode()['code'] ?? null,
			'totalPrice' => $this->formatTotalPrice($price),
			'totalNativePrice' => $this->formatTotalPrice($nativePrice),
			'currency' => $order->getCurrency(),
			'nativeCurrency' => $order->getNativeCurrency(),
			'totalWeight' => $order->getTotalWeight(),
			'source' => $order->getSource() !== null ? $order->getSource()->toArray() : null,
			'_attributes' => [
				'id' => $order->getId(),
			],
		];
	}

	/** @return mixed[]|null */
	private function formatTotalPrice(?MwsPrice $price): ?array
	{
		if ($price !== null) {
			$priceFormatted = $price->toArray();
			unset($priceFormatted['vatPercentage']); // because it is alway zero
		} else {
			$priceFormatted = null;
		}

		return $priceFormatted;
	}

	private function formatOrderItems(OrderItems $items): array
	{
		return array_map(function (OrderItem $item): array {
			return $item->toArray();
		}, $items->getAll());
	}

}
