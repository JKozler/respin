<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;
/** Group of backlinks from ordered items to products in shop. */
class OrderItems
{

	private Order $order;

	/** @var Orderitem[] */
	private array $_data = [];

	public function __construct(Order $order)
	{
		$this->order = $order;
	}

	public function getOrder(): Order
	{
		return $this->order;
	}

	/** @return OrderItem[] */
	public function getAll(): array
	{
		return $this->_data;
	}

	/** @return Orderitem[] */
	public function getProducts(): array
	{
		$products = [];
		foreach ($this->_data as $item) {
			if ($item->isProduct()) {
				$products[] = $item;
			}
		}

		return $products;
	}

	public function add(OrderItem $item): void
	{
		$orderId = $this->order->getId();
		if ($orderId !== null) {
			$item->setOrderId($orderId);
		}
		$this->_data[] = $item;
	}

	/** @return mixed[][] */
	public function toArray(): array
	{
		return array_map(function (OrderItem $item) {
			return $item->toArray();
		}, $this->getAll());
	}

	public function save(): void
	{
		foreach ($this->getAll() as $item) {
			if ($item->getOrderId() === null) {
				$orderId = $this->order->getId();
				if ($orderId === null) {
					throw new \Exception('Cannot save order item without persisted order.');
				}
				$item->setOrderId($orderId);
			}
			OrderItemRepository::save($item);
		}
	}

}
