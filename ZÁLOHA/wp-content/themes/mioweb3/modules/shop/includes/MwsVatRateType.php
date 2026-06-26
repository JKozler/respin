<?php declare(strict_types=1);

abstract class MwsVatRateType extends MwsBasicEnum
{

	public const Standard = 'standard';
	public const ElectronicPublication = 'electronic-publication';
	public const PrintedPublication = 'printed-publication';

	public static function getByProductType(string $productType): string
	{
		if ($productType === MwsProductType::PrintedPublication) {
			return self::PrintedPublication;
		}

		if ($productType === MwsProductType::ElectronicPublication) {
			return self::ElectronicPublication;
		}

		return self::Standard;
	}

}
