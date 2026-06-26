<?php declare(strict_types=1);

namespace Mioweb\Shop\Seznam;

use MwsBasicEnum;
use MwsShippingType;

class ZboziFeedDeliveryEnum extends MwsBasicEnum
{

	// Delivery points
	const CESKA_POSTA_BALIKOVNA = 'CESKA_POSTA_BALIKOVNA';
	const CESKA_POSTA_NA_POSTU = 'CESKA_POSTA_NA_POSTU';
	const DPD_PICKUP = 'DPD_PICKUP';
	const GEIS_POINT = 'GEIS_POINT';
	const GLS_PACELSHOP = 'GLS_PACELSHOP';
	const PPL_PARCELSHOP = 'PPL_PARCELSHOP';
	const TOPTRANS_DEPO = 'TOPTRANS_DEPO';
	const WEDO_ULOZENKA = 'WEDO_ULOZENKA';
	const ZASILKOVNA = 'ZASILKOVNA';
	const VLASTNI_VYDEJNI_MISTA = 'VLASTNI_VYDEJNI_MISTA';

	// Carriers
	const CESKA_POSTA = 'CESKA_POSTA';
	const DPD = 'DPD';
	const DHL = 'DHL';
	const DSV = 'DSV';
	const FOFR = 'FOFR';
	const GEBRUDER_WEISS = 'GEBRUDER_WEISS';
	const GEIS = 'GEIS';
	const FLS = 'FLS';
	const HDS = 'HDS';
	const WEDO_HOME = 'WEDO_HOME';
	const MESSENGER = 'MESSENGER';
	const PPL = 'PPL';
	const TNT = 'TNT';
	const TOPTRANS = 'TOPTRANS';
	const UPS = 'UPS';
	const FEDEX = 'FEDEX';
	const RABEN_LOGISTICS = 'RABEN_LOGISTICS';
	const RHENUS = 'RHENUS';
	const ZASILKOVNA_NA_ADRESU = 'ZASILKOVNA_NA_ADRESU';
	const VLASTNI_PREPRAVA = 'VLASTNI_PREPRAVA';

	private static array $mapping = [
		MwsShippingType::Custom => self::VLASTNI_PREPRAVA,
		MwsShippingType::PacketaCarriers => self::ZASILKOVNA_NA_ADRESU,
		MwsShippingType::Packeta => self::ZASILKOVNA,
		MwsShippingType::Personal => self::VLASTNI_VYDEJNI_MISTA,
	];

	public static function getByMwsType(string $mwsShippingType): ?string
	{
		return self::$mapping[$mwsShippingType] ?? null;
	}

}
