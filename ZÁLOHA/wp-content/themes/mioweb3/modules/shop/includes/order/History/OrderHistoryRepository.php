<?php declare(strict_types=1);

namespace Mioweb\Shop\Order\History;

use Mioweb\Database\BaseRepository;
use Mioweb\Shop\Order\Order;
use MwObjectCache;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/** @implements BaseRepository<OrderHistory> */
class OrderHistoryRepository extends BaseRepository
{

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 */
	public static function createNew(ActiveRow $row, bool $useCache = true): ?OrderHistory
	{
		if ($useCache) {
			$obj = MwObjectCache::get(OrderHistory::class, $row->getPrimary());

			if (!$obj) {
				$obj = OrderHistory::createByRow($row);
				MwObjectCache::add($obj, $obj->getId());
			}

			return $obj;
		}

		return OrderHistory::createByRow($row);
	}

	/** @return array<OrderHistory> */
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
		return 'mw_order_history';
	}

}
