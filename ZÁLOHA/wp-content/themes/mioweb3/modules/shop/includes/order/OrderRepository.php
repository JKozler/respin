<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

use Mioweb\Database\BaseEntity;
use Mioweb\Database\BaseRepository;
use Mioweb\Shop\Document\Document;
use Mioweb\Shop\Order\History\OrderHistoryRepository;
use MwObjectCache;
use MwsException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/** @implements BaseRepository<Order> */
class OrderRepository extends BaseRepository
{

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 */
	public static function createNew(ActiveRow $row, bool $useCache = true): Order
	{
		if ($useCache) {
			$obj = MwObjectCache::get(Order::class, $row->getPrimary());

			if (!$obj) {
				$obj = new Order($row);
				MwObjectCache::add($obj, $obj->getId());
			}

			return $obj;
		}

		return new Order($row);
	}

	/** Get order instance by order ID. */
	public static function getOneById(int $id): ?Order
	{
		$order = self::getTable()->get($id);

		if ($order !== null) {
			try {
				return static::createNew($order);
			} catch (MwsException $e) {
				mwshoplog(
					sprintf(__('Nepodařilo se vytvořit instanci objednávky [%d] se zprávou: %s', 'mwshop'), $id, $e->getMessage()),
					MWLL_ERROR
				);
			}
		}

		return null;
	}

	/**
	 * @TODO what if multiple orders with same number
	 * Get order by its order number.
	 */
	public static function getOrderByOrderNum(string $orderNum): ?Order
	{
		if (!$orderNum) {
			return null;
		}

		$order = self::getTable()->where('variable_symbol', $orderNum)->fetch();

		if ($order !== null) {
			return static::createNew($order);
		}

		return null;
	}

	public static function getOrderByHash(string $hash): ?Order
	{
		$order = self::getTable()->where('hash', $hash)->fetch();

		if ($order !== null) {
			return static::createNew($order);
		}

		return null;
	}

	/** @return Order[] */
	public static function findByRequest(MwsOrderFetchRequest $request): array
	{
		$orders = [];
		$selection = $request->buildQuery(self::getTable());

		foreach ($selection->fetchAll() as $order) {
			$orders[] = self::createNew($order);
		}

		return $orders;
	}

	public static function countByRequest(MwsOrderFetchRequest $request): int
	{
		return (int) $request->buildQuery(self::getTable())->count('*');
	}

	protected static function getTableBaseName(): string
	{
		return 'mw_orders';
	}

	public static function save(BaseEntity $entity): void
	{
		// TODO transactional run
		\assert($entity instanceof Order);

		$table = static::getTable();
		$entityArr = $entity->toRowArray();

		if ($entity->getId() !== null) {
			$table
				->where('id = ?', $entity->getId())
				->update($entityArr);
		} else {
			if (!isset($entityArr['created_at'])) {
				$entityArr['created_at'] = (new \DateTimeImmutable())->setTimezone(new \DateTimeZone('UTC'));
			}

			$row = $table->insert($entityArr);
			$entity->setId($row->getPrimary());
			$createdAt = $row->created_at;
			\assert($createdAt instanceof \DateTimeInterface);
			$entity->setCreatedAt(new \DateTimeImmutable($createdAt->format('Y-m-d H:i:s')));
		}

		$entity->getItems()->save();

		foreach ($entity->getHistory() as $historyItem) {
			if ($historyItem->getId() !== null) {
				continue;
			}

			if ($historyItem->getOrderId() === null) {
				$orderId = $entity->getId();
				if ($orderId === null) {
					throw new \Exception('Cannot save order history item without persisted order.');
				}

				$historyItem->setOrderId($orderId);
				OrderHistoryRepository::save($historyItem);
			}
		}
	}

	public static function delete(BaseEntity $entity): void
	{
		\assert($entity instanceof Order);

		// Delete documents
		foreach (Document::getAllByOrderId($entity->getId()) as $document) {
			wp_delete_post($document->getId(), true);
		}

		// Delete order history
		foreach ($entity->getHistory() as $historyItem) {
			OrderHistoryRepository::delete($historyItem);
		}

		// Delete order items
		foreach ($entity->getItems()->getAll() as $item) {
			OrderItemRepository::delete($item);
		}

		// Delete actual order
		$table = static::getTable();
		$table->where('id = ?', $entity->getId())->delete();
	}


}
