<?php declare(strict_types=1);

namespace Mioweb\Library\Api\MPohoda;

use Mioweb\Library\Api\MPohoda\Exceptions\MPohodaInvalidVatRateException;
use Mioweb\MPohodaClient\ValidationException;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Document\Document;
use MwsDocumentItem;
use MwsOrderEvent;
use Mioweb\Shop\Order\OrderGateDocument;
use MwsVATs;

class MPohodaIssuer
{

	private static ?self $instance = null;

	public function issue(OrderGateDocument $document): void
	{
		if (!$document instanceof Document) {
			return; // Currently, works only with Mioweb gateway
		}

		$client = MWMPohoda()->getClient();

		$order = $document->getOrder();

		try {
			$response = $client->getIssuedInvoices()->create($this->formatInvoice($document));
			$message = __('Faktura odeslána do systému mPOHODA', 'mwshop');

			$this->addHistory($order, $message, MwsOrderEvent::MPohodaInvoiceIssued);

			$document->setMPohodaId($response['Data']['Id']);
			$document->save();
		} catch (MPohodaInvalidVatRateException $e) {
			$message = __('Fakturu se nepodařilo odeslat do systému mPOHODA z důvodu nepodporované sazby DPH.', 'mwshop');

			$this->addHistory($order, $message, MwsOrderEvent::MPohodaInvoiceIssueFailed);
			mwMessages()->error($message);
		} catch (ValidationException $e) {
			$errMessage = htmlspecialchars(strip_tags($e->getMessage()));
			$message = __('Fakturu se nepodařilo odeslat do systému mPOHODA z důvodu chyby: ', 'mwshop') . $errMessage;

			$this->addHistory($order, $message, MwsOrderEvent::MPohodaInvoiceIssueFailed);
			mwMessages()->error($message);
		}
	}

	private function addHistory(?Order $order, string $message, string $event): void
	{
		if ($order !== null) {
			$order->addHistory($message, $event);
		}
	}

	/** @throws MPohodaInvalidVatRateException */
	private function formatInvoice(Document $document): array
	{
		$items = array_map(function (MwsDocumentItem $item) use ($document): array {
			return $this->formatInvoiceItem($item, $document);
		}, $document->getItems());

		$order = $document->getOrder();

		$result = [
			'Text' => 'Fakturujeme Vám dle Vaší objednávky',
			'IssueDate' => $document->getCreatedAt()->format('c'),
			'DueDate' => $document->getDueDate()->format('c'),
			'TaxDate' => $document->getTaxableSupplyAt()->format('c'),
			'Items' => $items,
			'Note' => $document->getInvoiceNote(),
		];

		if ($order !== null) {
			$result['OrderDate'] = $order->getCreatedAt()->format('c');
			$result['OrderNumber'] = $order->getNumber();
		}

		return $result;
	}

	/** @throws MPohodaInvalidVatRateException */
	private function formatInvoiceItem(MwsDocumentItem $item, Document $document): array
	{
		$price = $item->getPrice();

		if ($document->isReverseChargeApplied()) {
			$unitPriceType = 'ReverseCharge';
			$vatRateType = 'ZeroVatRate';
		} elseif ($price->getVatPercentage() === 0) {
			$unitPriceType = 'WithoutVat';
			$vatRateType = 'ZeroVatRate';
		} else {
			$unitPriceType = 'WithVat';

			$vatRateType = $this->resolveVatRateType($price->getVatPercentage());
		}

		return [
			'TextItem' => [
				'Text' => $item->getName(),
				'Quantity' => $item->getCount(),
				'UnitPrice' => $price->getPriceVatIncluded(),
				'UnitPriceType' => $unitPriceType,
				'VatRateType' => $vatRateType,
			],
		];
	}

	/** @throws MPohodaInvalidVatRateException */
	private function resolveVatRateType(int $vatPercentage): string
	{
//			$vatRateType = null;
//			foreach (MWS()->getVATs()->toArray() as $key => $vatRate) {
//				if ($vatRate !== null && $vatRate > 0 && (int) $vatRate === $price->getVatPercentage()) {
//					if ($key === 0) {
//						return 'BasicVatRate';
//					}
//					if ($key === 1) {
//						return 'FirstReducedVatRate';
//					}
//					if ($key === 2) {
//						return 'SecondReducedVatRate';
//					}
//				}
//			}

		if ($vatPercentage === 21) {
			return 'BasicVatRate';
		}
		if ($vatPercentage === 15) {
			return 'FirstReducedVatRate';
		}
		if ($vatPercentage === 10) {
			return 'SecondReducedVatRate';
		}
		if ($vatPercentage === 0) {
			return 'ZeroVatRate';
		}

		throw new MPohodaInvalidVatRateException(sprintf('Vat rate "%s" is not valid', $vatPercentage));
	}

	public static function getInstance(): MPohodaIssuer
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
