<?php

/**
 * @TODO refactor
 * Global cache for object. Instances are saved keyed as (class name, id).
 */
class MwObjectCache
{

	private static $items = [];

	public static function init()
	{
		static::$items = [];
	}

	/**
	 * Add new instance into cache. If there is a cached instance with the same classname and same id then it will be
	 * overwritten by passed instance.
	 *
	 * @param object $obj Instance to be added into the cache.
	 * @param int|string $id ID bellow which the object will be accessible.
	 * @return bool|object On success added object is returned. On error <code>false</code> is returned.
	 */
	public static function add($obj, $id)
	{
		$className = get_class($obj);
		if (!$className) {
			return false;
		}

		if (!isset(static::$items[$className])) {
			static::$items[$className] = [];
		}

		static::$items[$className][$id] = $obj;

		return $obj;
	}

	/**
	 * Get object from the cache.
	 *
	 * @param string $className Class name of the requested object.
	 * @param int|string $id ID bellow which the object was stored into cache.
	 * @return object|null Returns found object or <code>null</code>.
	 */
	public static function get($className, $id)
	{
		return static::$items[$className][$id] ?? null;
	}

	/**
	 * Remove object from cache.
	 *
	 * @param string $className Class name of the requested object.
	 * @param int|string $id ID bellow which the object was stored into cache.
	 * @return bool Return true when object was present in cache.
	 */
	public static function remove($obj, $id)
	{
		$className = get_class($obj);
		$res = isset(static::$items[$className][$id]);
		if ($res) {
			unset(static::$items[$className][$id]);
		}

		return $res;
	}

}
