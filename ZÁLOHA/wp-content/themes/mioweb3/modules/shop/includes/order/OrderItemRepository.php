<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

use Mioweb\Database\BaseRepository;
use MwObjectCache;
use MwsException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/** @implements BaseRepository<OrderItem> */
class OrderItemRepository extends BaseRepository
{

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 */
	public static function createNew(ActiveRow $row, bool $useCache = true): ?OrderItem
	{
		if ($useCache) {
			$obj = MwObjectCache::get(OrderItem::class, $row->getPrimary());

			if (!$obj) {
				$obj = OrderItem::createByRow($row);
				MwObjectCache::add($obj, $obj->getId());
			}

			return $obj;
		}

		return OrderItem::createByRow($row);
	}

	/** @return array<OrderItem> */
	public static function findByOrder(Order $order): array
	{
		$selection = self::getTable()->where('order_id = ?', $order->getId());
		$items = [];

		foreach ($selection->fetchAll() as $item) {
			$items[] = self::createNew($item);
		}

		return $items;
	}

	protected static function getTableBaseName(): string
	{
		return 'mw_order_items';
	}

}
