<?php declare(strict_types=1);

namespace Mioweb\Parameters;

class Parameters
{

	public const REQUIRED = 1;

	private const DATETIME_FORMAT = 'Y-m-d H:i:s';
	private const DATE_REGEXP = '#^\d\d\d\d-\d\d-\d\d\z#';
	private const DATETIME_REGEXP = '#^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d\z#';

	/** @var mixed[] */
	private array $data;

	private ?string $domain;

	/** @param mixed[] $data */
	private function __construct(array $data, ?string $domain = null)
	{
		$this->data = $data;
		$this->domain = $domain;
	}

	/** @param mixed[] $data */
	public static function from(array $data): Parameters
	{
		return new Parameters($data);
	}

	public function hasKey(string $key): bool
	{
		return array_key_exists($key, $this->data);
	}

	public function getString(string $key, int $options = 0): string
	{
		return $this->get($key, 'string', false, $options, '', null);
	}

	public function getStringOrNull(string $key, int $options = 0): ?string
	{
		return $this->get($key, 'string', true, $options, null, null);
	}

	/**
	 * @template T
	 * @param T $defaultValue
	 * @return string|T
	 */
	public function getStringOrDefault(string $key, $defaultValue = null)
	{
		return $this->get($key, 'string', true, 0, $defaultValue, null);
	}

	public function getInt(string $key, int $options = 0): int
	{
		return $this->get($key, 'int', false, $options, 0, null);
	}

	public function getIntOrNull(string $key, int $options = 0): ?int
	{
		return $this->get($key, 'int', true, $options, null, null);
	}

	/**
	 * @template T
	 * @param T $defaultValue
	 * @return int|T
	 */
	public function getIntOrDefault(string $key, $defaultValue = null)
	{
		return $this->get($key, 'int', true, 0, $defaultValue, null);
	}

	public function getFloat(string $key, int $options = 0): float
	{
		return $this->get($key, 'float', false, $options, 0.0, null);
	}

	public function getFloatOrNull(string $key, int $options = 0): ?float
	{
		return $this->get($key, 'float', true, $options, null, null);
	}

	/**
	 * @template T
	 * @param T $defaultValue
	 * @return float|T
	 */
	public function getFloatOrDefault(string $key, $defaultValue = null)
	{
		return $this->get($key, 'float', true, 0, $defaultValue, null);
	}

	public function getBool(string $key, int $options = 0): bool
	{
		return $this->get($key, 'bool', false, $options, false, null);
	}

	public function getBoolOrNull(string $key, int $options = 0): ?bool
	{
		return $this->get($key, 'bool', true, $options, null, null);
	}

	/**
	 * @template T
	 * @param T $defaultValue
	 * @return bool|T
	 */
	public function getBoolOrDefault(string $key, $defaultValue = null)
	{
		return $this->get($key, 'bool', true, 0, $defaultValue, null);
	}

	public function getDate(string $key, int $options = 0): \DateTimeImmutable
	{
		return $this->get($key, 'date', false, $options, new \DateTimeImmutable('0000-01-01 00:00:00'), null);
	}

	public function getDateOrNull(string $key, int $options = 0): ?\DateTimeImmutable
	{
		return $this->get($key, 'date', true, $options, null, null);
	}

	/**
	 * @template T
	 * @param T $defaultValue
	 * @return \DateTimeImmutable|T
	 */
	public function getDateOrDefault(string $key, $defaultValue = null)
	{
		return $this->get($key, 'date', true, 0, $defaultValue, null);
	}

	public function getDateTime(string $key, int $options = 0): \DateTimeImmutable
	{
		return $this->get($key, 'datetime', false, $options, new \DateTimeImmutable('0000-01-01 00:00:00'), null);
	}

	public function getDateTimeOrNull(string $key, int $options = 0): ?\DateTimeImmutable
	{
		return $this->get($key, 'datetime', true, $options, null, null);
	}

	/**
	 * @template T
	 * @param T $defaultValue
	 * @return \DateTimeImmutable|T
	 */
	public function getDateTimeOrDefault(string $key, $defaultValue = null)
	{
		return $this->get($key, 'datetime', true, 0, $defaultValue, null);
	}

	/** @return mixed[] */
	public function getArray(string $key, int $options = 0): array
	{
		return $this->get($key, 'array', false, $options, [], null);
	}

	/** @return mixed[]|null */
	public function getArrayOrNull(string $key, int $options = 0): ?array
	{
		return $this->get($key, 'array', true, $options, null, null);
	}

	/**
	 * @template T
	 * @param T $defaultValue
	 * @return mixed[]|T
	 */
	public function getArrayOrDefault(string $key, $defaultValue = null)
	{
		return $this->get($key, 'array', true, 0, $defaultValue, null);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $type
	 * @return T
	 */
	public function getObject(string $key, string $type, int $options = 0): object
	{
		if (!($options & self::REQUIRED)) {
			throw new InvalidArgumentException('Option Parameters::REQUIRED is required.');
		}

		return $this->get($key, 'object', false, $options, null, $type);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $type
	 * @return T|null
	 */
	public function getObjectOrNull(string $key, string $type, int $options = 0): ?object
	{
		return $this->get($key, 'object', true, $options, null, $type);
	}

	/**
	 * @template T of object
	 * @template U
	 * @param class-string<T> $type
	 * @param U|null $defaultValue
	 * @return T|U|null
	 */
	public function getObjectOrDefault(string $key, string $type, $defaultValue = null)
	{
		return $this->get($key, 'object', true, 0, $defaultValue, $type);
	}

	public function getParameters(string $key, int $options = 0): Parameters
	{
		$array = $this->get($key, 'array', false, $options, [], null);

		return new self($array, $this->prefixKey($key));
	}

	public function getParametersOrNull(string $key, int $options = 0): ?Parameters
	{
		$array = $this->get($key, 'array', true, $options, null, null);

		return $array !== null ? new self($array, $this->prefixKey($key)) : null;
	}

	/** @return string[] */
	public function getStringList(string $key, int $options = 0): array
	{
		return $this->getList($key, 'string', false, $options, null);
	}

	/** @return int[] */
	public function getIntList(string $key, int $options = 0): array
	{
		return $this->getList($key, 'int', false, $options, null);
	}

	/** @return float[] */
	public function getFloatList(string $key, int $options = 0): array
	{
		return $this->getList($key, 'float', false, $options, null);
	}

	/** @return bool[] */
	public function getBoolList(string $key, int $options = 0): array
	{
		return $this->getList($key, 'bool', false, $options, null);
	}

	/** @return \DateTimeImmutable[] */
	public function getDateList(string $key, int $options = 0): array
	{
		return $this->getList($key, 'date', false, $options, null);
	}

	/** @return \DateTimeImmutable[] */
	public function getDateTimeList(string $key, int $options = 0): array
	{
		return $this->getList($key, 'datetime', false, $options, null);
	}

	/** @return mixed[][] */
	public function getArrayList(string $key, int $options = 0): array
	{
		return $this->getList($key, 'array', false, $options, null);
	}

	/**
	 * @template T
	 * @param class-string<T> $type
	 * @return T[]
	 */
	public function getObjectList(string $key, $type, int $options = 0): array
	{
		return $this->getList($key, 'object', false, $options, $type);
	}

	/** @return Parameters[] */
	public function getParametersList(string $key, int $options = 0): array
	{
		$list = $this->getList($key, 'array', false, $options, null);

		foreach ($list as $k => &$value) {
			$value = new self($value, $this->prefixKey($key) . '[' . $k . ']');
		}

		return $list;
	}

	/**
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	private function get(string $key, string $type, bool $nullable, int $options, $defaultValue, ?string $objectType)
	{
		if (!$this->hasKey($key)) {
			if ($options & self::REQUIRED) {
				throw new InvalidParameterException('Parameter ' . $this->prefixKey($key) . ' is required.');
			}

			$value = $defaultValue;
		} else {
			$value = $this->data[$key];
		}

		if (!$this->checkType($value, $type, $nullable, $objectType)) {
			throw new InvalidParameterException('Parameter ' . $this->prefixKey($key) . ' must be '
				. $this->formatTypeLabel($type, $nullable, $objectType) . '.');
		}

		return $value;
	}

	/** @return mixed[] */
	private function getList(string $key, string $type, bool $nullable, int $options, ?string $objectType): array
	{
		$list = $this->get($key, 'list', false, $options, [], null);

		foreach ($list as $k => &$v) {
			if (!$this->checkType($v, $type, $nullable, $objectType)) {
				throw new InvalidParameterException(
					'Parameter ' . $this->prefixKey($key . '[' . $k . ']')
					. ' must be ' . $this->formatTypeLabel($type, $nullable, $objectType) . '.',
				);
			}
		}

		return $list;
	}

	/** @param mixed $value */
	private function checkType(&$value, string $type, bool $nullable, ?string $objectType): bool
	{
		if ($nullable && $value === null) {
			return true;
		}

		if ($type === 'string') {
			return is_string($value);
		}

		if ($type === 'int' && $this->isNumericInt($value)) {
			$value = (int) $value;

			return true;
		}

		if ($type === 'float' && $this->isNumeric($value)) {
			$value = (float) $value;

			return true;
		}

		if ($type === 'bool') {
			if (in_array($value, [false, 0, '0', 'false'], true)) {
				$value = false;

				return true;
			}

			if (in_array($value, [true, 1, '1', 'true'], true)) {
				$value = true;

				return true;
			}
		}

		if ($type === 'list') {
			return $this->isList($value);
		}

		if ($type === 'array') {
			return is_array($value);
		}

		if ($type === 'object') {
			return $objectType !== null && is_object($value) && is_a($value, $objectType);
		}

		if ($type === 'date') {
			if ($value instanceof \DateTimeInterface) {
				$value = $value->format('Y-m-d');
			}

			if (is_string($value) && preg_match(self::DATE_REGEXP, $value)) {
				$value = \DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, $value . ' 00:00:00');

				return true;
			}
		}

		if ($type === 'datetime') {
			if ($value instanceof \DateTimeInterface) {
				$value = $value->format('Y-m-d H:i:s');
			}

			if (is_string($value) && preg_match(self::DATETIME_REGEXP, $value)) {
				$value = \DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, $value);

				return true;
			}
		}

		return false;
	}

	/**
	 * This method is a part of the Nette Framework (c) 2004 David Grudl (https://davidgrudl.com), new BSD license
	 *
	 * @param mixed $value
	 */
	private function isNumericInt($value): bool
	{
		return is_int($value) || is_string($value) && preg_match('#^-?[0-9]+\z#', $value);
	}

	/**
	 * This method is a part of the Nette Framework (c) 2004 David Grudl (https://davidgrudl.com), new BSD license
	 *
	 * @param mixed $value
	 */
	private function isNumeric($value): bool
	{
		return is_float($value) || is_int($value) || is_string($value) && preg_match('#^-?[0-9]*[.]?[0-9]+\z#', $value);
	}

	/**
	 * This method is a part of the Nette Framework (c) 2004 David Grudl (https://davidgrudl.com), new BSD license
	 *
	 * @param mixed $value
	 */
	private function isList($value): bool
	{
		return is_array($value) && (!$value || array_keys($value) === range(0, count($value) - 1));
	}

	private function prefixKey(string $key): string
	{
		return ($this->domain !== null ? $this->domain . '.' : '') . $key;
	}

	private function formatTypeLabel(string $type, bool $nullable, ?string $objectType): string
	{
		static $labels = [
			'string' => 'a string',
			'int' => 'an integer',
			'float' => 'a float',
			'bool' => 'a boolean',
			'list' => 'a list',
			'array' => 'an array',
			'date' => 'a date',
			'datetime' => 'a datetime',
		];

		if (isset($labels[$type])) {
			$label = $labels[$type];
		} elseif ($type === 'object') {
			$label = 'an instance of ' . $objectType;
		} else {
			$label = $type;
		}

		return $label . ($nullable ? ' or null' : '');
	}

}
