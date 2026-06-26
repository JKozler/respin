<?php declare(strict_types=1);

namespace Mioweb\Lib;

class MwPrice
{

	public static function calculateVatByPriceVatIncluded(float $priceVatIncluded, int $vatPercentage): float
	{
		return round($vatPercentage / (100.0 + $vatPercentage) * $priceVatIncluded, 2);

		// implementation by Pohoda (old)
		//return round(round($vatPercentage / (100.0 + $vatPercentage), 4) * $priceVatIncluded, 2);
	}

	public static function calculateVatByPriceVatExcluded(float $priceVatExcluded, int $vatPercentage): float
	{
		return round($vatPercentage / 100.0 * $priceVatExcluded, 2);

		// implementation by Pohoda (old)
		//return round(round($vatPercentage / 100.0, 2) * $priceVatExcluded, 2);
	}

}
