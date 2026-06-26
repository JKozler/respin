<?php declare(strict_types=1);

namespace Mioweb\Mailing;

class EmailStatus
{

	public const CREATED = 'created';
	public const SKIPPED = 'skipped';
	public const ERROR = 'error';

	/** @var string[] $values */
	protected static array $values = [
		self::CREATED,
		self::SKIPPED,
		self::ERROR,
	];

	/** @return string[] */
	public static function getAll(): array
	{
		return self::$values;
	}

	public static function isValid(string $type): bool
	{
		return in_array($type, self::getAll(), true);
	}

}
