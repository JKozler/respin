<?php

class PluginType
{

	const UNCLASSIFIED = 'unclassified';
	const DEFAULT = 'default';
	const RECOMMENDED = 'recommended';
	const FORBIDDEN = 'forbidden';
	const PROBLEMATIC = 'problematic';
	const OTHERS_CHECKED = 'others checked';

	/** @return string[] */
	public static function getAll(): array
	{
		return [
			static::UNCLASSIFIED,
			static::DEFAULT,
			static::RECOMMENDED,
			static::FORBIDDEN,
			static::PROBLEMATIC,
			static::OTHERS_CHECKED,
		];
	}

	public static function getDefault(): string
	{
		return self::UNCLASSIFIED;
	}

	public static function isValid(string $value): bool
	{
		return in_array($value, static::getAll(), true);
	}

	public static function getTypesForSelect(): array
	{
		return [
			self::UNCLASSIFIED => 'neklasifikovaný',
			self::DEFAULT => 'základní',
			self::RECOMMENDED => 'doporučený',
			self::FORBIDDEN => 'nekompatibilní',
			self::PROBLEMATIC => 'problémový',
			self::OTHERS_CHECKED => 'uživatelsky ověřený',
		];
	}

	public static function translate(string $type): string
	{
		$arr = self::getTypesForSelect();

		try {
			return $arr[$type];
		} catch (\Throwable $exception) {
			return '';
		}
	}

}
