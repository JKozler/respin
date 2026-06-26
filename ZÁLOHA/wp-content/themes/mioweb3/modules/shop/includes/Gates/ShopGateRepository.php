<?php declare(strict_types=1);

namespace Mioweb\Shop\Gates;

use Mioweb\Database\BaseRepository;
use Mioweb\Shop\Order\OrderGate;
use MwObjectCache;
use Nette\Database\Table\ActiveRow;

/** @implements BaseRepository<OrderGate> */
class ShopGateRepository extends BaseRepository
{

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 */
	public static function createNew(ActiveRow $row, bool $useCache = true): ?ShopGate
	{
		if ($useCache) {
			$obj = MwObjectCache::get(ShopGate::class, $row->getPrimary());

			if (!$obj) {
				$obj = ShopGate::createByRow($row);
				MwObjectCache::add($obj, $obj->getId());
			}

			return $obj;
		}

		return ShopGate::createByRow($row);
	}

	public static function getOneByIdentifier(string $identifier): ?ShopGate
	{
		$shopGate = static::getTable()->where('identifier', $identifier)->fetch();

		if ($shopGate !== null) {
			return static::createNew($shopGate);
		}

		return null;
	}

	protected static function getTableBaseName(): string
	{
		return 'mw_shop_gates';
	}

}
