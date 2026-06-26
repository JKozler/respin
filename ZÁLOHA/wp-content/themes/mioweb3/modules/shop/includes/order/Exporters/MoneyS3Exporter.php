<?php declare(strict_types=1);

namespace Mioweb\Shop\Order\Exporters;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Mioweb\Shop\Exceptions\MissingInvoiceContactException;
use Mioweb\Shop\Order\Order;
use MwsCountry;
use Mioweb\Shop\Order\OrderItems;
use MwsOrderStatus;
use Spatie\ArrayToXml\ArrayToXml;

class MoneyS3Exporter implements IOrderExporter
{

	/** @var mixed[] */
	private array $supplierIdentity;

	private PhoneNumberUtil $phoneNumberUtil;

	public function __construct()
	{
		$this->phoneNumberUtil = PhoneNumberUtil::getInstance();
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

		return ArrayToXml::convert([
			'SeznamObjPrij' => ['ObjPrij' => $ordersTmp], // TODO what if $ordersTmp is empty?
			'_attributes' => [
				'note' => home_url(),
				'application' => 'Mioweb',
				'version' => '1.0',
			],
		], 'MoneyData', true, 'UTF-8');
	}

	public function getIdentifier(): string
	{
		return 'money-s3';
	}

	public function getName(): string
	{
		return __('Ekonomický systém Money S3', 'mwshop');
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
		$invoiceAddress = $invoiceContact !== null ? $invoiceContact->getAddress() : null;
		$shippingAddress = $shippingContact !== null ? $shippingContact->getAddress() : null;

		$price = $order->getPrice();

		$invoiceAddressFormatted = [
			'Misto' => $invoiceAddress !== null ? $invoiceAddress->getCity() : '',
			'Ulice' => $invoiceAddress !== null ? $invoiceAddress->getStreet() : '',
			'PSC' => $invoiceAddress !== null ? $invoiceAddress->getZip() : '',
			'Stat' => $invoiceAddress !== null ? MwsCountry::getCaption($invoiceAddress->getCountry()) : '',
			'KodStatu' => $invoiceAddress !== null ? $invoiceAddress->getCountry() : '',
		];

		$customerFormatted = [
			'ObchNazev' => $customerCompany !== null ? $customerCompany->getName() : '',
			'ObchAdresa' => $invoiceAddressFormatted,
			'FaktAdresa' => $invoiceAddressFormatted,
			'FaktNazev' => $customerCompany !== null ? $customerCompany->getName() : '',
			'Nazev' => $customerCompany !== null ? $customerCompany->getName() : '',
			'ICO' => $customerCompany !== null ? $customerCompany->getId() : '',
			'DIC' => $customerCompany !== null ? $customerCompany->getTaxId() : '',
			'DICSK' => $customerCompany !== null ? $customerCompany->getVatId() : '',
			'Adresa' => [
				'Misto' => $shippingAddress !== null ? $shippingAddress->getCity() : '',
				'Ulice' => $shippingAddress !== null ? $shippingAddress->getStreet() : '',
				'PSC' => $shippingAddress !== null ? $shippingAddress->getZip() : '',
				'Stat' => $shippingAddress !== null ? MwsCountry::getCaption($shippingAddress->getCountry()) : '',
				'KodStatu' => $shippingAddress !== null ? $shippingAddress->getCountry() : '',
			],
			'MojeFirma' => $this->supplierIdentity,
			'EMail' => $invoiceContact !== null ? $invoiceContact->getEmail() : '',
			'PlatceDPH' => $customerCompany !== null && $customerCompany->isVATPayer() ? 1 : 0,
		];

		$phoneRaw = $invoiceContact !== null ? $invoiceContact->getPhone() : null;

		if ($phoneRaw !== null) {
			try {
				$phone = $this->phoneNumberUtil->parse($phoneRaw);
				$customerFormatted['Tel'] = [
					'Pred' => $phone->getCountryCode(),
					'Cislo' => $phone->getNationalNumber(),
				];
			} catch (NumberParseException $e) {
				// Ignore
			}
		}

		return [
			'PrimDoklad' => $order->getNumber(),
			'Doklad' => $order->getNumber(),
			'VarSymbol' => $order->getNumber(),
			'Vystaveno' => $createdAt->format('Y-m-d'),
			'DatumVysta' => $createdAt->format('Y-m-d'),
			'CasVystave' => $createdAt->format('H:i:s'),
//				'Vyridit_do' => $createdAt->format('Y-m-d'),
//				'Vyridit_do' => $createdAt->format('Y-m-d'),
			'Poznamka' => $order->getCustomerNote(),
			'Vystavil' => 'Mioweb',
			'DodOdb' => $customerFormatted,
			'KonecPrij' => $customerFormatted,
//				'KPFromOdb' => 1 / 0 // TODO,
//				'ZpVypDPH' => 1, // Způsob výpočtu DPH (1 = matematicky, 2 = koeficient)
			'PlatPodm' => $order->getPaymentTitle(),
			'Doprava' => $order->getShipping()['name'] ?? '',
			'Prepravce' => $order->getShipping()['name'] ?? '',
			'Celkem' => $price !== null ? $price->getPriceVatExcluded() : 0.0,
			'eshop' => [
				'IN_ObjCis' => $order->getNumber(),
				'IN_Poznamk' => $order->getCustomerNote(),
				'IN_Stav' => MwsOrderStatus::getMoneyS3Status($order->getStatus()) ?? '',
				'IN_ReqStor' => $order->getStatus() === MwsOrderStatus::Cancelled ? 1 : 0,
				'IN_YesStor' => $order->getStatus() === MwsOrderStatus::Cancelled ? 1 : 0,
			],
			'Polozka' => [$this->formatOrderItems($order->getItems())],
		];
	}

	private function formatOrderItems(OrderItems $orderItems)
	{
		$order = $orderItems->getOrder();
		$createdAt = $order->getCreatedAt();
		$currency = $order->getCurrency();
		$nativeCurrency = $order->getNativeCurrency();

		$result = [];

		foreach ($orderItems->getAll() as $item) {
			$price = $item->getPrice($currency);
			$nativePrice = $nativeCurrency !== null ? $item->getPrice($nativeCurrency) : null;
			$isForeignPriceItem = $price !== null && $nativePrice !== null && $price->getCurrency() !== $nativePrice->getCurrency();
			$vatPercentage = $price !== null ? $price->getVatPercentage() : 0.0;

			$arr = [
				'Popis' => $item->getName(),
				'PocetMJ' => $item->getCount(),
				'SazbaDPH' => $vatPercentage,
				'Cena' => $nativePrice !== null ? $nativePrice->getPriceVatExcluded() : 0.0,
				'TypCeny' => (bool) $vatPercentage ? 1 : 0,
				'Vystaveno' => $createdAt->format('Y-m-d'),
				'Hmotnost' => $item->getWeight(),
			];

			if ($isForeignPriceItem) {
				$arr['Valuty'] = [
					'Mena' => $currency,
					'Celkem' => $price !== null ? $price->getPriceVatIncluded() : '',
				];
			}

			$result[] = $arr;
		}

		return $result;
	}

	private function formatSupplier(): array
	{
		$supplierContact = MWS()->getSupplierContact();
		$supplierCompany = $supplierContact !== null ? $supplierContact->getCompany() : null;
		$supplierPerson = $supplierContact !== null ? $supplierContact->getPerson() : null;
		$supplierAddress = $supplierContact !== null ? $supplierContact->getAddress() : null;

		$addressFormatted = [
			'Misto' => $supplierAddress !== null ? $supplierAddress->getCity() : '',
			'Ulice' => $supplierAddress !== null ? $supplierAddress->getStreet() : '',
			'PSC' => $supplierAddress !== null ? $supplierAddress->getZip() : '',
			'Stat' => $supplierAddress !== null ? MwsCountry::getCaption($supplierAddress->getCountry()) : '',
			'KodStatu' => $supplierAddress !== null ? $supplierAddress->getCountry() : '',
		];

		$companyName = $supplierCompany !== null ? $supplierCompany->getName() : '';

		$phoneRaw = $supplierContact !== null ? $supplierContact->getPhone() : null;

		if ($phoneRaw !== null) {
			try {
				$phone = $this->phoneNumberUtil->parse($phoneRaw);
			} catch (NumberParseException $e) {
				$phone = null;
			}
		} else {
			$phone = null;
		}

		$nativeCurrencySymbol = MWS()->getDefaultCurrency();
		$nativeCurrency = MWS()->getDefaultCurrency('key');
		$bankAccount = MWS()->getBankAccount($nativeCurrency);
		$bankAccountNumber = $bankAccount !== null ? $bankAccount->getNumber() : null;
		$bankAccountNumberParts = $bankAccountNumber !== null ? explode('/', $bankAccountNumber) : null;

		return [
			'Nazev' => $companyName,
			'ObchNazev' => $companyName,
			'FaktNazev' => $companyName,
			'Adresa' => $addressFormatted,
			'ObchAdresa' => $addressFormatted,
			'FaktAdresa' => $addressFormatted,
			'ICO' => $supplierCompany !== null ? $supplierCompany->getId() : '',
			'DIC' => $supplierCompany !== null ? $supplierCompany->getTaxId() : '',
			'DICSK' => $supplierCompany !== null ? $supplierCompany->getVatId() : '',
			'Tel' => $phone !== null ? [
				'Pred' => $phone->getCountryCode(),
				'Cislo' => $phone->getNationalNumber(),
			] : '',
			'EMail' => $supplierContact !== null ? $supplierContact->getEmail() : '',
			'WWW' => MWS()->getUrl_Home(),
			'Ucet' => $bankAccountNumberParts[0] ?? '',
			'KodBanky' => $bankAccountNumberParts[1] ?? '',
			'MenaSymb' => $nativeCurrencySymbol,
			'MenaKod' => $nativeCurrency,
		];
	}

}
