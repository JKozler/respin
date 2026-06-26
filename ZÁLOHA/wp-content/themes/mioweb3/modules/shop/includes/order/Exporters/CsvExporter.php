<?php declare(strict_types=1);

namespace Mioweb\Shop\Order\Exporters;

use Mioweb\Shop\Order\Order;
use MwsAddress;
use MwsCompany;
use MwsContact;
use MwsOrderSource;
use MwsOrderSourceType;
use MwsOrderStatus;
use MwsPerson;
use MwsPrice;
use function array_merge_recursive;

class CsvExporter implements IOrderExporter
{

	private ?MwsContact $supplierContact;

	public function __construct()
	{
		$this->supplierContact = MWS()->getSupplierContact();
	}

	/** @inheritDoc */
	public function export(iterable $orders): string
	{
		$csv = '';

		$first = true;
		foreach ($orders as $order) {
			if ($first) {
				$csv .= implode(';', array_keys($this->formatOrder($order, true))) . "\n";
				$first = false;
			}
			$values = array_values($this->formatOrder($order));
			// Escape semicolons
			$values = array_map(function ($value) {
				if (!is_string($value)) {
					return $value;
				}

				return str_contains($value, ';') ? '"' . str_replace('"', '""', $value) . '"' : $value;
			}, $values);

			$csv .= implode(';', $values) . "\n";
		}

		$csv = rtrim($csv);

		return $csv;
	}

	public function getIdentifier(): string
	{
		return 'csv';
	}

	public function getName(): string
	{
		return __('CSV', 'mwshop');
	}

	public function getFileExtension(): string
	{
		return 'csv';
	}

	private function formatOrder(Order $order, bool $keys = false): array
	{
		try {
			$createdAt = $order->getCreatedAt();
		} catch (\Exception) {
			$createdAt = null;
		}

		$invoiceContact = $this->addMissingDataToContact($order->getInvoiceContact());
		$shippingContact = $this->addMissingDataToContact($order->getShippingContact());
		$shipping = $this->addMissingDataToShipping($order->getShipping());
		$price = $order->getPrice();
		$nativePrice = $order->getNativePrice();
		$paymentMethod = $order->getPayment() ?? [];
		$paymentMethod['title'] = $order->getPaymentTitle();

		$orderSource = $order->getSource();
		if ($orderSource === null) {
			$orderSource = new MwsOrderSource(MwsOrderSourceType::Eshop);
		}

		$array = [
			'number' => $order->getNumber(),
			'status' => MwsOrderStatus::getCaption($order->getStatus()),
			'createdAt' => $createdAt->format('Y-m-d H:i:s'),
			'paidAt' => $order->getPaidAtDateFormatted(),
			'note' => str_replace(["\r\n", "\n", "\r"], ' ', $order->getCustomerNote()),
			'customerInvoiceContact' => $invoiceContact->toArray(),
			'customerShippingContact' => $shippingContact->toArray(),
			'supplierContact' => $this->supplierContact?->toArray(),
			'paymentMethod' => $paymentMethod,
			'shippingMethod' => $shipping,
			'eshopUrl' => MWS()->getUrl_Home(),
			'discountCode' => $order->getDiscountCode()['code'] ?? null,
			'totalPrice' => $this->formatTotalPrice($price),
			'totalNativePrice' => $this->formatTotalPrice($nativePrice),
			'currency' => $order->getCurrency(),
			'nativeCurrency' => $order->getNativeCurrency(),
			'totalWeight' => $order->getTotalWeight(),
			'source' => $orderSource->toArray(),
			'id' => $order->getId(),
		];

		return $this->flattenArray($array);
	}

	private function flattenArray($array, $prefix = '')
	{
		$result = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				// Recurse into the nested array
				 $result += $this->flattenArray($value, $prefix . $key . '.');
			} else {
				// Add the flattened key to the result
				$result[$prefix . $key] = $value;
			}
		}

		return $result;
	}

	private function addMissingDataToContact(?MwsContact $contact): MwsContact
	{
		if ($contact === null) {
			$contact = new MwsContact('');
		}

		if ($contact->getCompany() === null) {
			$contact->setCompany(new MwsCompany(''));
		}

		if ($contact->getAddress() === null) {
			$contact->setAddress(new MwsAddress('', '', '', ''));
		}

		if ($contact->getPerson() === null) {
			$contact->setPerson(new MwsPerson('', ''));
		}

		return $contact;
	}

	/**
	 * @param mixed[] $shipping
	 * @return mixed[]
	 */
	private function addMissingDataToShipping(array $shipping): array
	{
		$requiredColumns = [
			'shippingId' => null,
			'name' => null,
			'type' => null,
			'price' => [
				'currency' => null,
				'vatPercentage' => null,
				'priceVatExcluded' => null,
				'priceVatIncluded' => null,
			],
			'externalId' => null,
			'pickupAddress' => null,
			'cod_price' => [
				'currency' => null,
				'vatPercentage' => null,
				'priceVatExcluded' => null,
				'priceVatIncluded' => null,
			],
		];

		return array_merge($requiredColumns, $shipping);
	}

	/**
	 * @param MwsPrice|null $price
	 * @return array|null
	 */
	private function formatTotalPrice(?MwsPrice $price): ?array
	{
		if ($price === null) {
			$price = new MwsPrice(0.0);
		}

		$priceFormatted = $price->toArray();
		unset($priceFormatted['vatPercentage']); // because it is always zero

		return $priceFormatted;
	}
}
