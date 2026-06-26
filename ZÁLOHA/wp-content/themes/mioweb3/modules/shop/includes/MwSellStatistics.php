<?php declare(strict_types=1);

use Mioweb\Shop\Order\MwsOrderFetchRequest;
use Mioweb\Shop\Order\OrderRepository;

class MwSellStatistics
{

	//protected static $_instance = null;
	private $_average = 0;

	private $_totalPrice;

	private $_paidPrice;

	private $_notPaidPrice;

	private $_canceledPrice;

	private $_countAll = 0;

	private $_countNoCancelled = 0;

	private $_bySource = [];

	public function __construct($from = null, $to = null, ?string $source = null)
	{
		$this->loadStatistics($from, $to, $source);
	}

	public function loadStatistics($from = null, $to = null, ?string $source = null): void
	{
		$this->_totalPrice = new MwsPrice(0.0);
		$this->_paidPrice = new MwsPrice(0.0);
		$this->_notPaidPrice = new MwsPrice(0.0);
		$this->_canceledPrice = new MwsPrice(0.0);
		$defaultCurrency = MWS()->getDefaultCurrency('key');

		// TODO #3642 Maybe do not fetch orders and use raw ActiveRow for better performance
//		$q = OrderRepository::getTable();
//
//		if ($from) {
//			$q->where('created_at >= ?', $from);
//		}
//		if ($to) {
//			$q->where('created_at <= ?', $to);
//		}
//		if ($source == '0') {
//			$q->where('source_type = ?', MwsOrderSourceType::Eshop);
//		} elseif ($source) {
//			$q->where('source.form_id = ?', $source);
//		}

		$sourceParsed = $source !== null ? (int) $source : null;
		$request = new MwsOrderFetchRequest(-1, null, source: $sourceParsed, from: $from, to: $to, orderDirection: 'DESC');
		$orders = MwsOrderAdmin::getOrdersGenerator($request);

		foreach ($orders as $order) {
			if (!$order->isTest()) {
				$orderPrice = new MwsPrice(0.0);

				$gateId = $order->getGateIdentifier();
				$gateData = $order->getGateOrderData();

				if ($gateId === 'mioweb') {
					$total = $order->getTotal();
					if (isset($total[$defaultCurrency])) {
						$orderPrice = new MwsPrice($total[$defaultCurrency]['vatIncluded']);
					}
				} elseif (isset($gateData['dataInvoice']['total_native'])) {
					$orderPrice = new MwsPrice($gateData['dataInvoice']['total_native']);
				}

				if (!$order->isCancelled()) {
					$this->_totalPrice = $this->_totalPrice->add($orderPrice);

					if ($order->isPaid()) {
						$this->_paidPrice = $this->_paidPrice->add($orderPrice);
					} else {
						$this->_notPaidPrice = $this->_notPaidPrice->add($orderPrice);
					}
					$this->_countNoCancelled++;

					// source
					$orderSource = $order->getSource();
					if ($orderSource !== null && $orderSource->getType() !== MwsOrderSourceType::Eshop) {
						$sourceId = $orderSource->getType() === MwsOrderSourceType::Form ? $orderSource->getFormId() : $orderSource->getType();
					} else {
						$sourceId = 0;
					}

					if (!isset($this->_bySource[$sourceId])) {
						$this->_bySource[$sourceId]['total'] = new MwsPrice(0.0);
						$this->_bySource[$sourceId]['count'] = 0;
					}

					$this->_bySource[$sourceId]['total'] = $this->_bySource[$sourceId]['total']->add($orderPrice);
					$this->_bySource[$sourceId]['count']++;
				} else {
					$this->_canceledPrice = $this->_canceledPrice->add($orderPrice);
				}
				$this->_countAll++;
			}
		}
		$this->_average = $this->_countNoCancelled ? $this->_totalPrice->getPriceVatIncluded() / $this->_countNoCancelled : 0;

		/*
		echo '<br><br>';

		$orders = MwsOrderAdmin::getOrders(-1, 1, '', '', $from, $to);

		foreach($orders['items'] as $order)
		{
			$sourceId = $order->getSource() && $order->getSource()->getFormId()? $order->getSource()->getFormId() : 0;

			if(!$order->isTest() && ($source === '' || $sourceId == $source))
			{
				$price = $order->getNativePrice();

				echo $price->getPriceVatIncluded();
				echo '<br>';
				if ($price) {

					if($order->getStatus() != MwsOrderStatus::Cancelled)
					{
						$this->_totalPrice = $this->_totalPrice->add($price);
						if($order->isPaid())
						{
							$this->_paidPrice = $this->_paidPrice->add($price);
						}
						else
						{
							$this->_notPaidPrice = $this->_notPaidPrice->add($price);
						}
						$this->_countNoCancelled++;

						// source

						if(!isset($this->_bySource[$sourceId]))
						{
							$this->_bySource[$sourceId]['total'] = new MwsPrice(0.0);
							$this->_bySource[$sourceId]['count'] = 0;
						}

						$this->_bySource[$sourceId]['total'] = $this->_bySource[$sourceId]['total']->add($price);
						$this->_bySource[$sourceId]['count']++;

					}
					else
					{
						$this->_canceledPrice = $this->_canceledPrice->add($price);
					}
					$this->_countAll++;
				}
			}
		}
		$this->_average = $this->_countNoCancelled ? $this->_totalPrice->getPriceVatIncluded() / $this->_countNoCancelled : 0;
		*/
	}

	public function getOrdersCount()
	{
		return $this->_countNoCancelled;
	}

	public function getAveragePrice()
	{
		return $this->_average;
	}

	public function getTotalPrice()
	{
		return $this->_totalPrice;
	}

	public function getPaidPrice()
	{
		return $this->_paidPrice;
	}

	public function getNotPaidPrice()
	{
		return $this->_notPaidPrice;
	}

	public function getCanceledPrice()
	{
		return $this->_canceledPrice;
	}

	public function getBySource($id = 0)
	{
		return $this->_bySource[$id] ?? null;
	}

}
