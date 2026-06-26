<?php declare(strict_types=1);

abstract class MwsOrderItemType extends MwsBasicEnum
{

	public const Physical = MwsProductType::Physical;
	public const ElectronicPublication = MwsProductType::ElectronicPublication;
	public const PrintedPublication = MwsProductType::PrintedPublication;
	public const Service = MwsProductType::Service;
	public const ElectronicService = MwsProductType::ElectronicService;
	public const Membership = MwsProductType::Membership;
	public const LiveEvent = MwsProductType::LiveEvent;
	public const LiveEventForeign = MwsProductType::LiveEventForeign;

	public const Discount = 'discount';
	public const Shipping = 'shipping';
	public const Rounding = 'rounding';

	public static function getFapiType(string $documentItemType): ?string
	{
		$countryCode = strtoupper(MWS()->getDefaultShippingCountry());
		if (!in_array($countryCode, [MwsCountry::CZ, MwsCountry::SK], true)) {
			$countryCode = MwsCountry::CZ;
		}

		$map = [
			self::Physical => 'good',
			self::ElectronicPublication => 'electronic-publication',
			self::PrintedPublication => 'printed-publication',
			self::Service => 'service',
			self::ElectronicService => 'electronic-service',
			self::Membership => 'electronic-service',
			self::LiveEvent => 'live-event-' . strtolower($countryCode),
			//self::Shipping => 'service',
			self::Rounding => 'good', // verified from api call
		];

		return $map[$documentItemType] ?? $documentItemType;
	}

	public static function getReverseFapiType(string $fapiItemType): ?string
	{
		$countryCode = strtoupper(MWS()->getDefaultShippingCountry());
		if (!in_array($countryCode, [MwsCountry::CZ, MwsCountry::SK], true)) {
			$countryCode = MwsCountry::CZ;
		}

		$map = [
			'good' => self::Physical,
			'live-event-' . strtolower($countryCode) => self::LiveEvent,
		];
		if ($countryCode === 'CZ') {
			$map['live-event-sk'] = self::LiveEventForeign;
		} else {
			$map['live-event-cz'] = self::LiveEventForeign;
		}

		return $map[$fapiItemType] ?? $fapiItemType;
	}

	/** @return string[] */
	public static function getActualProducts(): array
	{
		return [
			self::Physical,
			self::PrintedPublication,
			self::ElectronicPublication,
			self::ElectronicService,
			self::Membership,
			self::LiveEvent,
			self::LiveEventForeign,
			self::Service,
		];
	}

	public static function isActualProduct(string $type): ?bool
	{
		return in_array($type, self::getActualProducts(), true);
	}

}
