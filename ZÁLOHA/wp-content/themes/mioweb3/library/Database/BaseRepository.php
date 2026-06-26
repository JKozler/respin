<?php declare(strict_types=1);

namespace Mioweb\Database;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/** @template T */
abstract class BaseRepository
{

	abstract protected static function getTableBaseName(): string;

	/** @return T */
	abstract public static function createNew(ActiveRow $row, bool $useCache = true);

	/** @return T|null */
	public static function getOneById(int $id)
	{
		$entity = static::getTable()->get($id);

		if ($entity !== null) {
			return static::createNew($entity);
		}

		return null;
	}

	/** @param T $entity */
	public static function save(BaseEntity $entity): void
	{
		$table = static::getTable();
		$entityArr = $entity->toRowArray();

		if ($entity->getId() !== null) {
			$table
				->where('id = ?', $entity->getId())
				->update($entityArr);
		} else {
			$row = $table->insert($entityArr);
			$entity->setId($row->getPrimary());
		}
	}

	/** @return T[] */
	public static function findAll(): array
	{
		$table = static::getTable();

		return self::fetchAll($table);
	}

	/**
	 * @param array<string, mixed> $args
	 * @param ...$params
	 * @return T[]
	 */
	public static function findBy(array $args, ...$params): array
	{
		$table = static::getTable();

		return self::fetchAll($table->where($args, $params));
	}

	/** @param array<string, mixed> $args */
	public static function countBy(array $args): int
	{
		$table = static::getTable();

		return $table->where($args)->count('*');
	}

	public static function delete(BaseEntity $entity): void
	{
		$table = static::getTable();

		$table->where('id = ?', $entity->getId())->delete();
	}

	/** @return T[] */
	public static function fetchAll(Selection $selection): array
	{
		return array_map(static function (ActiveRow $row): BaseEntity {
			return static::createNew($row);
		}, $selection->fetchAll());
	}

	public static function getTableName(): string
	{
		global $wpdb;

		return $wpdb->prefix . static::getTableBaseName();
	}

	public static function getTable(): Selection
	{
		return core()->getExplorer()->table(static::getTableName());
	}

}
