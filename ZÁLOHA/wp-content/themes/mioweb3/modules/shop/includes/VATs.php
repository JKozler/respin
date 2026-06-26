<?php

/**
 * Accessor to global VAT definitions. Works as proxy on global shop definitions.
 * User: kuba
 * Date: 07.03.16
 * Time: 13:55
 */
class MwsVATs
{

	private $_vatValues;

	/** @var string */
	private $_vatAccounting;

	/** @var string */
	private $_vatEUInvoicing;

	/** @var array<string, int> */
	private $_vatRates;

	/** @var array<string, array<string, int|float>> */
	private array $defaultRates = [
		MwsVatRateType::Standard => [
			MwsCountry::AT => 20,
			MwsCountry::BE => 21,
			MwsCountry::BG => 20,
			MwsCountry::CY => 19,
			MwsCountry::CZ => 21,
			MwsCountry::DE => 19,
			MwsCountry::DK => 25,
			MwsCountry::EE => 20,
			MwsCountry::ES => 21,
			MwsCountry::FI => 24,
			MwsCountry::FR => 20,
			MwsCountry::GR => 24,
			MwsCountry::HR => 25,
			MwsCountry::HU => 27,
			MwsCountry::IE => 23,
			MwsCountry::IT => 22,
			MwsCountry::LT => 21,
			MwsCountry::LU => 17,
			MwsCountry::LV => 21,
			MwsCountry::MT => 18,
			MwsCountry::NL => 21,
			MwsCountry::PL => 23,
			MwsCountry::PT => 23,
			MwsCountry::RO => 19,
			MwsCountry::SE => 25,
			MwsCountry::SI => 22,
			MwsCountry::SK => 20,
		],
		MwsVatRateType::ElectronicPublication => [
			MwsCountry::AT => 10,
			MwsCountry::BE => 6,
			MwsCountry::BG => 9,
			MwsCountry::CY => 19,
			MwsCountry::CZ => 10,
			MwsCountry::DE => 19,
			MwsCountry::DK => 25,
			MwsCountry::EE => 9,
			MwsCountry::ES => 21,
			MwsCountry::FI => 24,
			MwsCountry::FR => 5.5,
			MwsCountry::GR => 24,
			MwsCountry::HR => 5,
			MwsCountry::HU => 5,
			MwsCountry::IE => 23,
			MwsCountry::IT => 4,
			MwsCountry::LT => 9,
			MwsCountry::LU => 3,
			MwsCountry::LV => 21,
			MwsCountry::MT => 18,
			MwsCountry::NL => 9,
			MwsCountry::PL => 23,
			MwsCountry::PT => 6,
			MwsCountry::RO => 19,
			MwsCountry::SE => 6,
			MwsCountry::SI => 5,
			MwsCountry::SK => 20,
		],
		MwsVatRateType::PrintedPublication => [
			MwsCountry::AT => 10,
			MwsCountry::BE => 6,
			MwsCountry::BG => 9,
			MwsCountry::CY => 5,
			MwsCountry::CZ => 10,
			MwsCountry::DE => 7,
			MwsCountry::DK => 25,
			MwsCountry::EE => 9,
			MwsCountry::ES => 4,
			MwsCountry::FI => 10,
			MwsCountry::FR => 5.5,
			MwsCountry::GR => 6,
			MwsCountry::HR => 5,
			MwsCountry::HU => 27,
			MwsCountry::IE => 9,
			MwsCountry::IT => 4,
			MwsCountry::LT => 21,
			MwsCountry::LU => 3,
			MwsCountry::LV => 12,
			MwsCountry::MT => 5,
			MwsCountry::NL => 9,
			MwsCountry::PL => 5,
			MwsCountry::PT => 6,
			MwsCountry::RO => 5,
			MwsCountry::SE => 6,
			MwsCountry::SI => 5,
			MwsCountry::SK => 10,
		],
	];

	public function __construct(array $vatValues, string $vatAccounting, string $EUInvoicing, array $rates)
	{
		$this->_vatValues = array_map(function ($item) {
			return !empty($item) || is_numeric($item) ? (int) $item : null;
		}, $vatValues);
		$this->_vatAccounting = MwsVatAccounting::checkedValue($vatAccounting, MwsVatAccounting::noVat);
		$this->_vatEUInvoicing = MwsVatElectronicInvoicing::checkedValue($EUInvoicing, MwsVatElectronicInvoicing::Inland);
		$this->_vatRates = $rates;
	}

	public function getAccountingType(): string
	{
		return $this->_vatAccounting;
	}

	public function isUsingVatAccounting(): bool
	{
		return $this->_vatAccounting === MwsVatAccounting::withVat;
	}

	public function getEUInvoicing(): string
	{
		return $this->_vatEUInvoicing;
	}

	/** @return array<string, array<string, int|float>> */
	public function getVatRates(): array
	{
		return $this->_vatRates;
	}

	public function getVatRate(string $countryCode, string $vatRateType = MwsVatRateType::Standard): ?float
	{
		return $this->getVatRates()[$vatRateType][$countryCode] ?? ($this->defaultRates[$vatRateType][$countryCode] ?? null);
	}

	/**
	 * Get value of a VAT level.
	 *
	 * @param int $vatId ID of requested VAT level.
	 * @param bool $stored If set to <code>true</code>, stored value is returned (usefull only for setting controls). Otherwise effective value is returned.
	 * @param int null $default Default value of VAT for the case VAT level is not used.
	 */
	public function getValueById(int $vatId, bool $stored, ?int $default = null): ?int
	{
		return $stored || $this->isUsingVatAccounting() ? $this->_vatValues[$vatId] ?? $default : 0;
	}

	/**
	 * Get default VAT value. It is value of first VAT level. If first VAT level is not defined, it behaves like VAT is 0%.
	 *
	 * @param bool $stored If set to <code>true</code>, stored value is returned (usefull only for setting controls). Otherwise effective value is returned.
	 * @param int|null $default If default VAT is not defined, then this values is returned.
	 */
	public function getValueDefault(bool $stored, ?int $default = 0): ?int
	{
		return $this->getValueById(0, $stored, $default);
	}

	/**
	 * Is some VAT value set? If not it means VAT accounting is not used or undefined.
	 */
	public function hasValues(): bool
	{
		foreach ($this->_vatValues as $value) {
			if ($value !== null) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return all VATs in an array. Keys are vat IDs, values are percents of VAT.
	 */
	public function toArray(): array
	{
		return $this->_vatValues;
	}

}

class MwsVatAccounting extends MwsBasicEnum
{
	/** No VAT usage, no VAT identification. */
	const noVat = 'noVat';
	/** No VAT usage, using VAT identification. */
	const noVatIdentified = 'noVatIdentified';
	/** Full VAT accounting. */
	const withVat = 'withVat';
}

class MwsVatElectronicInvoicing extends MwsBasicEnum
{
	const Oss = 'oss';

	const Inland = 'inland';

	private static array $fapiValuesMapping = [
		1 => self::Oss,
		2 => self::Inland,
	];

	/** @throws \Exception */
	public static function getByFapiValue(int $fapiValue): string
	{
		if (!array_key_exists($fapiValue, self::$fapiValuesMapping)) {
			throw new \Exception(sprintf('"%d" is not valid FAPI value.', $fapiValue));
		}

		return self::$fapiValuesMapping[$fapiValue];
	}

}
