<?php
/**
 * MioShop enumerations.
 * User: kuba
 * Date: 08.03.16
 * Time: 11:17
 */

use Mioweb\MiowebAdminClient\MiowebAdminPublicClientFactory;

/**
 * Basic helper routines for enumerations. Has routines to check validity of enumeration name an enumeration value.
 * Class BasicEnum
 *
 * @link http://stackoverflow.com/users/32536/brian-cline
 * @link http://stackoverflow.com/questions/254514/php-and-enumerations.
 */
abstract class MwsBasicEnum
{

	private static $constCacheArray = [];

	private static $captions = [];

	private static function getConstants(): array
	{
		$calledClass = get_called_class();
		if (!array_key_exists($calledClass, self::$constCacheArray)) {
			$reflect = new ReflectionClass($calledClass);
			self::$constCacheArray[$calledClass] = $reflect->getConstants();
		}

		return self::$constCacheArray[$calledClass];
	}

	public static function isValidValue($value, bool $strict = true): bool
	{
		$values = array_values(self::getConstants());

		return in_array($value, $values, $strict);
	}

	/**
	 * Checks if value is a valid value of enumeration. If not, default value is returned.
	 *
	 * @param mixed $value Value of enumeration to check.
	 * @param null $default Value used when check fails.
	 * @return mixed|null
	 */
	public static function checkedValue($value, $default = null)
	{
		return self::isValidValue($value) ? $value : $default;
	}

	/**
	 * Returns array of enumeration keys (=constant names).
	 */
	public static function getAllKeyNames(): array
	{
		return array_keys(self::getConstants());
	}

	/**
	 * Returns array of enumeration values (=constant values) in their order of definition.
	 */
	public static function getAll(): array
	{
		return array_values(self::getConstants());
	}

	/**
	 * Descendants should return array of strings indexed by enumeration values.
	 * These strings can be gained using {@link getCaption()} method.
	 */
	protected static function doInitCaptions(): array
	{
		return [];
	}

	private static function initCaptions(): void
	{
		$calledClass = get_called_class();
		if (!array_key_exists($calledClass, self::$captions)) {
			$strings = static::doInitCaptions();
			self::$captions[$calledClass] = $strings;
		}
	}

	public static function getCaption($value): string
	{
		$calledClass = get_called_class();
		self::initCaptions();

		return (string) (self::$captions[$calledClass][$value] ?? $value);
	}

	public static function getSelect($args = [], $val = '', $class = ''): string
	{
		$options = [];
		foreach (self::getAll() as $item) {
			if (!isset($args['exclude']) || !in_array($item, $args['exclude'])) {
				$options[] = [
					'name' => esc_html(self::getCaption($item)),
					'value' => esc_attr($item),
				];
			}
		}
		$args['options'] = $options;

		return mwAdminComponents::select($args, $val, $class);
	}

	public static function getCheckList($args = [], $val = [], $class = ''): string
	{
		$content = '';
		foreach (self::getAll() as $item) {
			if (!isset($args['exclude']) || !in_array($item, $args['exclude'])) {
				$content .= mwAdminComponents::checkbox([
					'name' => $args['name'] . '[]',
					'value' => $item,
					'label' => self::getCaption($item),
				], true, $class);
			}
		}

		return $content;
	}

}

/** Types of product as related to its delivery characteristic. */
abstract class MwsProductType extends MwsBasicEnum
{
	public const Physical = 'physical';
	public const PrintedPublication = 'printed-publication';
	public const ElectronicPublication = 'electronic-publication';
	public const Membership = 'membership';
	public const ElectronicService = 'electronic'; // "electronic" for backward compatibility (@TODO migration to "electronic-service" type)
	public const Service = 'service';
	public const LiveEvent = 'live-event';
	public const LiveEventForeign = 'live-event-foreign';

	protected static function doInitCaptions(): array
	{
		return [
			self::Physical => __('Fyzický (vyžaduje klasické metody doručení)', 'mwshop'),
			self::Service => __('Služba', 'mwshop'),
			self::Membership => __('Členství (členská sekce)', 'mwshop'),
			self::ElectronicService => __('Jiná elektronická služba', 'mwshop'),
			self::ElectronicPublication => __('Elektronická publikace', 'mwshop'),
			self::PrintedPublication => __('Tištěná publikace', 'mwshop'),
			self::LiveEvent => __('Živá akce (seminář, školení, koncert apod.)', 'mwshop'),
			self::LiveEventForeign => __('Živá akce v zahraničí', 'mwshop'),
		];
	}

	/** @return string[] */
	public static function getElectronicTypes(): array
	{
		return [self::ElectronicPublication, self::Membership, self::ElectronicService];
	}

	public static function isElectronic($type): bool
	{
		return in_array($type, self::getElectronicTypes(), true);
	}

	/** @return string[] */
	public static function getApplicableForOSS(): array
	{
		return [
			self::Physical,
			self::PrintedPublication,
			self::ElectronicPublication,
			self::Membership,
			self::ElectronicService,
			self::Service,
			self::LiveEventForeign,
		];
	}

	public static function isApplicableForOSS($type): bool
	{
		return in_array($type, self::getApplicableForOSS(), true);
	}

	/** @return string[] */
	public static function getTypesForReverseChargeApplication(): array
	{
		return [
			self::Physical,
			self::PrintedPublication,
			self::ElectronicPublication,
			self::Membership,
			self::ElectronicService,
			self::Service,
			self::LiveEventForeign,
		];
	}

	public static function isForReverseChargeApplication($type): bool
	{
		return in_array($type, self::getTypesForReverseChargeApplication(), true);
	}

	/** @return string[] */
	public static function getPhysical(): array
	{
		return [
			self::Physical,
			self::PrintedPublication,
		];
	}

	public static function isPhysical($type): bool
	{
		return in_array($type, self::getPhysical(), true);
	}

}

abstract class MwsShippingType extends MwsBasicEnum
{
	const Personal = 'personal';
	const Custom = 'custom';
	const Packeta = 'packeta';
	const PacketaCarriers = 'packeta_carriers';

	private static array $trackingUrls = [
		self::Personal => null,
		self::Custom => null,
		self::Packeta => 'https://tracking.packeta.com/cs/?id=',
		self::PacketaCarriers => 'https://tracking.packeta.com/cs/?id=',
	];

	public static function getTrackingUrl(string $type): ?string
	{
		return self::isValidValue($type) ? self::$trackingUrls[$type] : null;
	}
}

/** Types of product as related to its delivery characteristic. */
abstract class MwsDiscountCodeType extends MwsBasicEnum
{
	const Percent = 'percent';
	const Fixed = 'fixed';
}

/** Types of product as related to its delivery characteristic. */
abstract class MwsDiscountCodeExpirationType extends MwsBasicEnum
{
	const None = 'none';
	const Count = 'count';
	const DateRange = 'daterange';
}

/** Types of product a for its structure */
abstract class MwsProductStructureType extends MwsBasicEnum
{
	/** Basic product with simplest settings. */
	const Single = 'single';
	/** Product supporting multiple variants. Each variant is defined by a set of parameters. */
	const Variants = 'variants';

	/** Instance of one variant. Used internally. */
	const OneVariant = 'onevariant';
}

/** Enumeration of steps of an order. */
abstract class MwsOrderStep extends MwsBasicEnum
{
	const Cart = 1;
	const Contact = 2;
	const Shipping = 3;
	const Summarize = 4;
	const ThankYou = 5;

	protected static function doInitCaptions(): array
	{
		return [
			self::Cart => __('Nákupní košík', 'mwshop'),
			self::Contact => __('Osobní údaje', 'mwshop'),
			self::Shipping => __('Doprava a platba', 'mwshop'),
			self::Summarize => __('Shrnutí objednávky', 'mwshop'),
			self::ThankYou => __('Závěr', 'mwshop'),
		];
	}

	public static function getIcon($step): ?string
	{
		return [
			self::Cart => 'step_cart',
			self::Contact => 'step_contact',
			self::Shipping => 'step_shipping',
			self::Summarize => 'step_summarize',
			self::ThankYou => 'step_ok',
		][$step] ?? null;
	}

	public static function getId($step): string
	{
		return [
			self::Cart => 'cart',
			self::Contact => 'contact',
			self::Shipping => 'shipping',
			self::Summarize => 'summarize',
			self::ThankYou => 'thanks',
		][$step] ?? (string) $step;
	}
}

/**
 * @TODO enum of strings
 * Enumeration of status of an order.
 */
abstract class MwsOrderStatus extends MwsBasicEnum
{
	const Ordered = 1;
	const Processing = 2;
	//const Delivered = 3;
	const Closed = 10;
	const Cancelled = 20;

	/** @var array<int, int> */
	private static array $moneyS3Mapping = [
		self::Ordered => 0,
		self::Processing => 1,
//		self::Delivered => 3,
		self::Closed => 3,
		self::Cancelled => 4,
	];

	protected static function doInitCaptions(): array
	{
		return [
			self::Ordered => __('Nevyřízená', 'mwshop'),
			self::Processing => __('Vyřizuje se', 'mwshop'),
			//self::Delivered => __('doručeno', 'mwshop'),
			self::Closed => __('Vyřízená', 'mwshop'),
			self::Cancelled => __('Stornovaná', 'mwshop'),
		];
	}
	public static function getIcon($status): string
	{
		return [
			self::Ordered => 'x',
			self::Processing => 'clock',
			//self::Delivered => __('doručeno', 'mwshop'),
			self::Closed => 'check',
			self::Cancelled => 'x',
		][$status] ?? 'x';
	}

	public static function getMoneyS3Status(int $status): ?string
	{
		return self::$moneyS3Mapping[$status] ?? null;
	}

}


/** Supported currencies . */
abstract class MwsCurrencyEnum extends MwsBasicEnum
{
	const czk = 'czk';
	const eur = 'eur';
	const pln = 'pln';
	const usd = 'usd';
	const rub = 'rub';
	const gbp = 'gbp';

	protected static function doInitCaptions(): array
	{
		return [
			self::czk => __('CZK - Kč', 'mwshop'),
			self::eur => __('EUR - €', 'mwshop'),
			self::pln => __('PLN - Zł', 'mwshop'),
			self::usd => __('USD - $', 'mwshop'),
			self::rub => __('RUB - ₽', 'mwshop'),
			self::gbp => __('GBP - £', 'mwshop'),
		];
	}

	/**
	 * Get currency according to country. If not defined, then use default currency for eshop.
	 */
	public static function getByCountry(?string $country): string
	{
		return [
			MwsCountry::CZ => self::czk,
			MwsCountry::SK => self::eur,
			MwsCountry::DE => self::eur,
			MwsCountry::ES => self::eur,
			MwsCountry::BE => self::eur,
			MwsCountry::AT => self::eur,
			MwsCountry::CY => self::eur,
			MwsCountry::EE => self::eur,
			MwsCountry::FI => self::eur,
			MwsCountry::FR => self::eur,
			MwsCountry::GR => self::eur,
			MwsCountry::IE => self::eur,
			MwsCountry::IT => self::eur,
			MwsCountry::LV => self::eur,
			MwsCountry::LT => self::eur,
			MwsCountry::LU => self::eur,
			MwsCountry::MT => self::eur,
			MwsCountry::NL => self::eur,
			MwsCountry::PT => self::eur,
			MwsCountry::SI => self::eur,
			MwsCountry::PL => self::pln,
			MwsCountry::GB => self::gbp,
			MwsCountry::US => self::usd,
			MwsCountry::RU => self::rub,
		][$country] ?? MWS()->getDefaultCurrency('key');
	}

	public static function getSupportedByCountry(?string $country): string
	{
		$currency = self::getByCountry($country);
		$supportedCurrencies = MWS()->getCurrencies();

		if (!in_array($currency, $supportedCurrencies)) {
			return MWS()->getDefaultCurrency('key');
		}

		return $currency;
	}

	/**
	 * Get default currency conversion rates.
	 */
	public static function getDefaultConversionTable(string $from, bool $reload = false): array
	{
		$trans = get_transient('mw_exchange_rates_trans_' . $from);
		$rates = get_option('mw_exchange_rates_' . $from);
		if (!$rates || !$trans || $reload) {
			$licence = get_option('web_option_license');
			if (!isset($licence['license'])) {
				mwshoplog(sprintf(__('Chyba při stahování tabulky kurzů měn: Licenční číslo nebylo nalezeno.', 'mwshop')) . ' ' . __METHOD__, MWLL_ERROR, 'paygate');

				return [];
			}

			try {
				$mwaClient = core()->getMwaPublicClient();
				$response = $mwaClient->getExchangeRates([
					'serialNumber' => $licence['license'],
					'base' => strtoupper($from),
				]);
				$rates = [];

				foreach ($response['rates'] as $currency => $rate) {
					$rates[strtolower($currency)] = 1.0 / $rate;
				}
				update_option('mw_exchange_rates_' . $from, $rates);
				$secondsToMidnight = strtotime('tomorrow') - time();
				set_transient('mw_exchange_rates_trans_' . $from, true, $secondsToMidnight);
			} catch (\Throwable $e) {
				mwshoplog(sprintf(__('Chyba při stahování tabulky kurzů měn:', 'mwshop')) . ' ' . $e->getMessage() . ' ' . __METHOD__, MWLL_ERROR, 'paygate');

				throw $e;
			}
		}

		return $rates;
	}

	/** Get short unit of the currency, like € symbor for EUR
	 *
	 * @param string $currency Requested currency as value of {@link MwsCurrencyEnum}.
	 * @return string Currency symbol or passed value if symbol is not defined.
	 */
	public static function getSymbol(string $currency): string
	{
		return [
			self::czk => __('Kč', 'mwshop'),
			self::eur => __('€', 'mwshop'),
			self::pln => __('Zł', 'mwshop'),
			self::usd => __('$', 'mwshop'),
			self::rub => __('₽', 'mwshop'),
			self::gbp => __('£', 'mwshop'),
		][strtolower($currency)] ?? $currency;
	}

	/**
	 * Get an attribute value "step" for HTML5 elements according to passed currency.
	 *
	 * @param string|MwsCurrencyEnum|null $currency When null then default currency is used.
	 * @return string Resulting string is according to HTML specifications. Can be an integer or decimal number or "any" string.
	 */
	public static function getHtmlInputStepAttribute(?string $currency = null): string
	{
		return [
			static::czk => '1',
			static::eur => '0.01',
		][$currency ?: MWS()->getDefaultCurrency('key')] ?? 'any';
	}
}

abstract class MwsCurrencyMode extends MwsBasicEnum
{
	const Default = 'default';
	const ByCountry = 'by-country';
}

/** Type of restriction of selling of a product - disabled or kind of enabling */
abstract class MwsSellRestriction extends MwsBasicEnum
{
	/** No restriction, selling is enabled. */
	const None = 'none';
	/** Full restriction, selling is disabled. */
	const FullDisable = 'full';
	/** Timed restrictions based on calendar. Selling is enabled within specified time period. */
	const EnabledInterval = 'interval';
	/** Timed restrictions based on calendar. Selling is enabled from time without end date. */
	const EnabledFrom = 'from';
	/** Timed restrictions based on calendar. Selling is enabled from now until specified time period. */
	const EnabledTill = 'to';
}

/** Type of sale price for product - disabled or which kind of enabling */
abstract class MwsSalePriceType extends MwsBasicEnum
{
	/** No sale price active. */
	const None = 'none';
	/** Sale price is active permanently. */
	const Continuous = 'continuous';
	/** Sale price is enabled within an interval */
	const EnabledInterval = 'interval';
	/** Sale price is enabled from time without end date. */
	const EnabledFrom = 'from';
	/** Sale price is enabled from now until specified time period. */
	const EnabledTill = 'to';
}

/** Type of product property. */
abstract class MwsPropertyType extends MwsBasicEnum
{
	const Enumeration = 'enum';
	const Text = 'text';
}

/** Type of product code. */
abstract class MwsProductCode extends MwsBasicEnum
{
	/** Evidencni */
	const Filing = 'filing';
	/** Ucetni */
	const Financial = 'financial';
	/** Predkontace */
	const Assignment = 'assignment';
	/** Stredisko */
	const Center = 'center';
	/** Sklad */
	const Stock = 'stock';
	/** Skladova polozka */
	const StockItem = 'stockItem';
	/** EAN kod */
	const EAN = 'ean';

	protected static function doInitCaptions(): array
	{
		return [
			self::Filing => __('Evidenční kód', 'mwshop'),
			self::Financial => __('Účetní kód', 'mwshop'),
			self::Assignment => __('Kód předkontace', 'mwshop'),
			self::Center => __('Kód střediska', 'mwshop'),
			self::Stock => __('Kód skladu', 'mwshop'),
			self::StockItem => __('Kód skladové položky', 'mwshop'),
			self::EAN => __('EAN kód', 'mwshop'),
		];
	}

}

abstract class MwsCountry extends MwsBasicEnum
{
	const AD = 'AD';
	const AE = 'AE';
	const AF = 'AF';
	const AG = 'AG';
	const AI = 'AI';
	const AL = 'AL';
	const AM = 'AM';
	const AO = 'AO';
	const AQ = 'AQ';
	const AR = 'AR';
	const AS = 'AS';
	const AT = 'AT';
	const AU = 'AU';
	const AW = 'AW';
	const AZ = 'AZ';
	const BA = 'BA';
	const BB = 'BB';
	const BD = 'BD';
	const BE = 'BE';
	const BF = 'BF';
	const BG = 'BG';
	const BH = 'BH';
	const BI = 'BI';
	const BJ = 'BJ';
	const BM = 'BM';
	const BN = 'BN';
	const BO = 'BO';
	const BR = 'BR';
	const BS = 'BS';
	const BT = 'BT';
	const BV = 'BV';
	const BW = 'BW';
	const BY = 'BY';
	const BZ = 'BZ';
	const CA = 'CA';
	const CC = 'CC';
	const CD = 'CD';
	const CF = 'CF';
	const CG = 'CG';
	const CH = 'CH';
	const CI = 'CI';
	const CK = 'CK';
	const CL = 'CL';
	const CM = 'CM';
	const CN = 'CN';
	const CO = 'CO';
	const CR = 'CR';
	const CS = 'CS';
	const CU = 'CU';
	const CV = 'CV';
	const CX = 'CX';
	const CY = 'CY';
	const CZ = 'CZ';
	const DE = 'DE';
	const DJ = 'DJ';
	const DK = 'DK';
	const DM = 'DM';
	const DO = 'DO';
	const DZ = 'DZ';
	const EC = 'EC';
	const EE = 'EE';
	const EG = 'EG';
	const EH = 'EH';
	const ER = 'ER';
	const ES = 'ES';
	const ET = 'ET';
	const FI = 'FI';
	const FJ = 'FJ';
	const FK = 'FK';
	const FM = 'FM';
	const FO = 'FO';
	const FR = 'FR';
	const GA = 'GA';
	const GB = 'GB';
	const GD = 'GD';
	const GE = 'GE';
	const GF = 'GF';
	const GG = 'GG';
	const GH = 'GH';
	const GI = 'GI';
	const GL = 'GL';
	const GM = 'GM';
	const GN = 'GN';
	const GP = 'GP';
	const GQ = 'GQ';
	const GR = 'GR';
	const GS = 'GS';
	const GT = 'GT';
	const GU = 'GU';
	const GW = 'GW';
	const GY = 'GY';
	const HK = 'HK';
	const HM = 'HM';
	const HN = 'HN';
	const HR = 'HR';
	const HT = 'HT';
	const HU = 'HU';
	const ID = 'ID';
	const IE = 'IE';
	const IL = 'IL';
	const IM = 'IM';
	const IN = 'IN';
	const IO = 'IO';
	const IQ = 'IQ';
	const IR = 'IR';
	const IS = 'IS';
	const IT = 'IT';
	const JM = 'JM';
	const JO = 'JO';
	const JP = 'JP';
	const KE = 'KE';
	const KG = 'KG';
	const KH = 'KH';
	const KI = 'KI';
	const KM = 'KM';
	const KN = 'KN';
	const KP = 'KP';
	const KR = 'KR';
	const KW = 'KW';
	const KY = 'KY';
	const KZ = 'KZ';
	const LA = 'LA';
	const LB = 'LB';
	const LC = 'LC';
	const LI = 'LI';
	const LK = 'LK';
	const LR = 'LR';
	const LS = 'LS';
	const LT = 'LT';
	const LU = 'LU';
	const LV = 'LV';
	const LY = 'LY';
	const MA = 'MA';
	const MC = 'MC';
	const MD = 'MD';
	const MG = 'MG';
	const MH = 'MH';
	const MK = 'MK';
	const ML = 'ML';
	const MM = 'MM';
	const MN = 'MN';
	const MO = 'MO';
	const MP = 'MP';
	const MQ = 'MQ';
	const MR = 'MR';
	const MS = 'MS';
	const MT = 'MT';
	const MU = 'MU';
	const MV = 'MV';
	const MW = 'MW';
	const MX = 'MX';
	const MY = 'MY';
	const MZ = 'MZ';
	const NA = 'NA';
	const NC = 'NC';
	const NE = 'NE';
	const NF = 'NF';
	const NG = 'NG';
	const NI = 'NI';
	const NL = 'NL';
	const NO = 'NO';
	const NP = 'NP';
	const NR = 'NR';
	const NU = 'NU';
	const NZ = 'NZ';
	const OM = 'OM';
	const PA = 'PA';
	const PE = 'PE';
	const PF = 'PF';
	const PG = 'PG';
	const PH = 'PH';
	const PK = 'PK';
	const PL = 'PL';
	const PM = 'PM';
	const PN = 'PN';
	const PR = 'PR';
	const PS = 'PS';
	const PT = 'PT';
	const PW = 'PW';
	const PY = 'PY';
	const QA = 'QA';
	const RE = 'RE';
	const RO = 'RO';
	const RU = 'RU';
	const RW = 'RW';
	const SA = 'SA';
	const SB = 'SB';
	const SC = 'SC';
	const SD = 'SD';
	const SE = 'SE';
	const SG = 'SG';
	const SH = 'SH';
	const SI = 'SI';
	const SJ = 'SJ';
	const SK = 'SK';
	const SL = 'SL';
	const SM = 'SM';
	const SN = 'SN';
	const SO = 'SO';
	const SR = 'SR';
	const ST = 'ST';
	const SV = 'SV';
	const SY = 'SY';
	const SZ = 'SZ';
	const TC = 'TC';
	const TD = 'TD';
	const TF = 'TF';
	const TG = 'TG';
	const TH = 'TH';
	const TJ = 'TJ';
	const TK = 'TK';
	const TL = 'TL';
	const TM = 'TM';
	const TN = 'TN';
	const TO = 'TO';
	const TR = 'TR';
	const TT = 'TT';
	const TV = 'TV';
	const TW = 'TW';
	const TZ = 'TZ';
	const UA = 'UA';
	const UG = 'UG';
	const UM = 'UM';
	const US = 'US';
	const UY = 'UY';
	const UZ = 'UZ';
	const VA = 'VA';
	const VC = 'VC';
	const VE = 'VE';
	const VG = 'VG';
	const VI = 'VI';
	const VN = 'VN';
	const VU = 'VU';
	const WF = 'WF';
	const WS = 'WS';
	const YE = 'YE';
	const YT = 'YT';
	const ZA = 'ZA';
	const ZM = 'ZM';
	const ZW = 'ZW';

	/** @return string[] */
	public static function getEUCountries(): array
	{
		return [
			self::AT,
			self::BE,
			self::BG,
			self::CY,
			self::CZ,
			self::DE,
			self::DK,
			self::EE,
			self::ES,
			self::FI,
			self::FR,
			self::GR,
			self::HR,
			self::HU,
			self::IE,
			self::IT,
			self::LT,
			self::LU,
			self::LV,
			self::MT,
			self::NL,
			self::PL,
			self::PT,
			self::RO,
			self::SE,
			self::SI,
			self::SK,
		];
	}

	public static function isEUCountry(string $countryCode): bool
	{
		return in_array(strtoupper($countryCode), static::getEUCountries(), true);
	}

	protected static function doInitCaptions(): array
	{
		return [
			self::AD => 'Andorra',
			self::AE => 'Arab Emirates',
			self::AF => 'Afghanistan',
			self::AG => 'Antigua and Barbuda',
			self::AI => 'Anguilla',
			self::AL => 'Albania',
			self::AM => 'Armenia',
			self::AO => 'Angola',
			self::AQ => 'Antarctica',
			self::AR => 'Argentina',
			self::AS => 'American Samoa',
			self::AT => 'Austria',
			self::AU => 'Australia',
			self::AW => 'Aruba',
			self::AZ => 'Azerbaijan',
			self::BA => 'Bosnia and Herzegovina',
			self::BB => 'Barbados',
			self::BD => 'Bangladesh',
			self::BE => 'Belgium',
			self::BF => 'Burkina Faso',
			self::BG => 'Bulgaria',
			self::BH => 'Bahrain',
			self::BI => 'Burundi',
			self::BJ => 'Benin',
			self::BM => 'Bermuda',
			self::BN => 'Brunei Darussalam',
			self::BO => 'Bolivia',
			self::BR => 'Brazil',
			self::BS => 'Bahamas',
			self::BT => 'Bhutan',
			self::BV => 'Bouvet Island',
			self::BW => 'Botswana',
			self::BY => 'Belarus',
			self::BZ => 'Belize',
			self::CA => 'Canada',
			self::CC => 'Cocos (Keeling) Islands',
			self::CD => 'Congo, the Democratic Republic of the',
			self::CF => 'Central African Republic',
			self::CG => 'Congo',
			self::CH => 'Switzerland',
			self::CI => 'Cote D\'Ivoire',
			self::CK => 'Cook Islands',
			self::CL => 'Chile',
			self::CM => 'Cameroon',
			self::CN => 'China',
			self::CO => 'Colombia',
			self::CR => 'Costa Rica',
			self::CS => 'Serbia and Montenegro',
			self::CU => 'Cuba',
			self::CV => 'Cape Verde',
			self::CX => 'Christmas Island',
			self::CY => 'Cyprus',
			self::CZ => 'Česká republika',
			self::DE => 'Germany',
			self::DJ => 'Djibouti',
			self::DK => 'Denmark',
			self::DM => 'Dominica',
			self::DO => 'Dominican Republic',
			self::DZ => 'Algeria',
			self::EC => 'Ecuador',
			self::EE => 'Estonia',
			self::EG => 'Egypt',
			self::EH => 'Western Sahara',
			self::ER => 'Eritrea',
			self::ES => 'Spain',
			self::ET => 'Ethiopia',
			self::FI => 'Finland',
			self::FJ => 'Fiji',
			self::FK => 'Falkland Islands (Malvinas)',
			self::FM => 'Micronesia, Federated States of',
			self::FO => 'Faroe Islands',
			self::FR => 'France',
			self::GA => 'Gabon',
			self::GB => 'United Kingdom',
			self::GD => 'Grenada',
			self::GE => 'Georgia',
			self::GF => 'French Guiana',
			self::GG => 'Guernsey',
			self::GH => 'Ghana',
			self::GI => 'Gibraltar',
			self::GL => 'Greenland',
			self::GM => 'Gambia',
			self::GN => 'Guinea',
			self::GP => 'Guadeloupe',
			self::GQ => 'Equatorial Guinea',
			self::GR => 'Greece',
			self::GS => 'South Georgia and the South Sandwich Islands',
			self::GT => 'Guatemala',
			self::GU => 'Guam',
			self::GW => 'Guinea-Bissau',
			self::GY => 'Guyana',
			self::HK => 'Hong Kong',
			self::HM => 'Heard Island and Mcdonald Islands',
			self::HN => 'Honduras',
			self::HR => 'Croatia',
			self::HT => 'Haiti',
			self::HU => 'Hungary',
			self::ID => 'Indonesia',
			self::IE => 'Ireland',
			self::IL => 'Israel',
			self::IM => 'Isle of Man',
			self::IN => 'India',
			self::IO => 'British Indian Ocean Territory',
			self::IQ => 'Iraq',
			self::IR => 'Iran, Islamic Republic of',
			self::IS => 'Iceland',
			self::IT => 'Italy',
			self::JM => 'Jamaica',
			self::JO => 'Jordan',
			self::JP => 'Japan',
			self::KE => 'Kenya',
			self::KG => 'Kyrgyzstan',
			self::KH => 'Cambodia',
			self::KI => 'Kiribati',
			self::KM => 'Comoros',
			self::KN => 'Saint Kitts and Nevis',
			self::KP => 'Korea, Democratic People\'s Republic of',
			self::KR => 'Korea, Republic of',
			self::KW => 'Kuwait',
			self::KY => 'Cayman Islands',
			self::KZ => 'Kazakhstan',
			self::LA => 'Lao People\'s Democratic Republic',
			self::LB => 'Lebanon',
			self::LC => 'Saint Lucia',
			self::LI => 'Liechtenstein',
			self::LK => 'Sri Lanka',
			self::LR => 'Liberia',
			self::LS => 'Lesotho',
			self::LT => 'Lithuania',
			self::LU => 'Luxembourg',
			self::LV => 'Latvia',
			self::LY => 'Libyan Arab Jamahiriya',
			self::MA => 'Morocco',
			self::MC => 'Monaco',
			self::MD => 'Moldova, Republic of',
			self::MG => 'Madagascar',
			self::MH => 'Marshall Islands',
			self::MK => 'Macedonia, the Former Yugoslav Republic of',
			self::ML => 'Mali',
			self::MM => 'Myanmar',
			self::MN => 'Mongolia',
			self::MO => 'Macao',
			self::MP => 'Northern Mariana Islands',
			self::MQ => 'Martinique',
			self::MR => 'Mauritania',
			self::MS => 'Montserrat',
			self::MT => 'Malta',
			self::MU => 'Mauritius',
			self::MV => 'Maldives',
			self::MW => 'Malawi',
			self::MX => 'Mexico',
			self::MY => 'Malaysia',
			self::MZ => 'Mozambique',
			self::NA => 'Namibia',
			self::NC => 'New Caledonia',
			self::NE => 'Niger',
			self::NF => 'Norfolk Island',
			self::NG => 'Nigeria',
			self::NI => 'Nicaragua',
			self::NL => 'Netherlands',
			self::NO => 'Norway',
			self::NP => 'Nepal',
			self::NR => 'Nauru',
			self::NU => 'Niue',
			self::NZ => 'New Zealand',
			self::OM => 'Oman',
			self::PA => 'Panama',
			self::PE => 'Peru',
			self::PF => 'French Polynesia',
			self::PG => 'Papua New Guinea',
			self::PH => 'Philippines',
			self::PK => 'Pakistan',
			self::PL => 'Poland',
			self::PM => 'Saint Pierre and Miquelon',
			self::PN => 'Pitcairn',
			self::PR => 'Puerto Rico',
			self::PS => 'Palestinian Territory, Occupied',
			self::PT => 'Portugal',
			self::PW => 'Palau',
			self::PY => 'Paraguay',
			self::QA => 'Qatar',
			self::RE => 'Reunion',
			self::RO => 'Romania',
			self::RU => 'Russian Federation',
			self::RW => 'Rwanda',
			self::SA => 'Saudi Arabia',
			self::SB => 'Solomon Islands',
			self::SC => 'Seychelles',
			self::SD => 'Sudan',
			self::SE => 'Sweden',
			self::SG => 'Singapore',
			self::SH => 'Saint Helena',
			self::SI => 'Slovenia',
			self::SJ => 'Svalbard and Jan Mayen',
			self::SK => 'Slovenská republika',
			self::SL => 'Sierra Leone',
			self::SM => 'San Marino',
			self::SN => 'Senegal',
			self::SO => 'Somalia',
			self::SR => 'Suriname',
			self::ST => 'Sao Tome and Principe',
			self::SV => 'El Salvador',
			self::SY => 'Syrian Arab Republic',
			self::SZ => 'Swaziland',
			self::TC => 'Turks and Caicos Islands',
			self::TD => 'Chad',
			self::TF => 'French Southern Territories',
			self::TG => 'Togo',
			self::TH => 'Thailand',
			self::TJ => 'Tajikistan',
			self::TK => 'Tokelau',
			self::TL => 'Timor-Leste',
			self::TM => 'Turkmenistan',
			self::TN => 'Tunisia',
			self::TO => 'Tonga',
			self::TR => 'Turkey',
			self::TT => 'Trinidad and Tobago',
			self::TV => 'Tuvalu',
			self::TW => 'Taiwan, Province of China',
			self::TZ => 'Tanzania, United Republic of',
			self::UA => 'Ukraine',
			self::UG => 'Uganda',
			self::UM => 'United States Minor Outlying Islands',
			self::US => 'United States',
			self::UY => 'Uruguay',
			self::UZ => 'Uzbekistan',
			self::VA => 'Holy See (Vatican City State)',
			self::VC => 'Saint Vincent and the Grenadines',
			self::VE => 'Venezuela',
			self::VG => 'Virgin Islands, British',
			self::VI => 'Virgin Islands, U.s.',
			self::VN => 'Viet Nam',
			self::VU => 'Vanuatu',
			self::WF => 'Wallis and Futuna',
			self::WS => 'Samoa',
			self::YE => 'Yemen',
			self::YT => 'Mayotte',
			self::ZA => 'South Africa',
			self::ZM => 'Zambia',
			self::ZW => 'Zimbabwe',
		];
	}

	public static function getAlpha3(string $country): string
	{
		return [
			self::AF => 'AFG',
			self::AL => 'ALB',
			self::DZ => 'DZA',
			self::AS => 'ASM',
			self::AD => 'AND',
			self::AO => 'AGO',
			self::AI => 'AIA',
			self::AQ => 'ATA',
			self::AG => 'ATG',
			self::AR => 'ARG',
			self::AM => 'ARM',
			self::AW => 'ABW',
			self::AU => 'AUS',
			self::AT => 'AUT',
			self::AZ => 'AZE',
			self::BS => 'BHS',
			self::BH => 'BHR',
			self::BD => 'BGD',
			self::BB => 'BRB',
			self::BY => 'BLR',
			self::BE => 'BEL',
			self::BZ => 'BLZ',
			self::BJ => 'BEN',
			self::BM => 'BMU',
			self::BT => 'BTN',
			self::BO => 'BOL',
			self::BA => 'BIH',
			self::BW => 'BWA',
			self::BV => 'BVT',
			self::BR => 'BRA',
			self::IO => 'IOT',
			self::VG => 'VGB',
			self::BN => 'BRN',
			self::BG => 'BGR',
			self::BF => 'BFA',
			self::BI => 'BDI',
			self::KH => 'KHM',
			self::CM => 'CMR',
			self::CA => 'CAN',
			self::CV => 'CPV',
			self::KY => 'CYM',
			self::CF => 'CAF',
			self::TD => 'TCD',
			self::CL => 'CHL',
			self::CN => 'CHN',
			self::CX => 'CXR',
			self::CC => 'CCK',
			self::CO => 'COL',
			self::KM => 'COM',
			self::CD => 'COD',
			self::CG => 'COG',
			self::CK => 'COK',
			self::CR => 'CRI',
			self::CI => 'CIV',
			self::CU => 'CUB',
			self::CY => 'CYP',
			self::CZ => 'CZE',
			self::DK => 'DNK',
			self::DJ => 'DJI',
			self::DM => 'DMA',
			self::DO => 'DOM',
			self::EC => 'ECU',
			self::EG => 'EGY',
			self::SV => 'SLV',
			self::GQ => 'GNQ',
			self::ER => 'ERI',
			self::EE => 'EST',
			self::ET => 'ETH',
			self::FO => 'FRO',
			self::FK => 'FLK',
			self::FJ => 'FJI',
			self::FI => 'FIN',
			self::FR => 'FRA',
			self::GF => 'GUF',
			self::PF => 'PYF',
			self::TF => 'ATF',
			self::GA => 'GAB',
			self::GM => 'GMB',
			self::GE => 'GEO',
			self::DE => 'DEU',
			self::GH => 'GHA',
			self::GI => 'GIB',
			self::GR => 'GRC',
			self::GL => 'GRL',
			self::GD => 'GRD',
			self::GP => 'GLP',
			self::GU => 'GUM',
			self::GT => 'GTM',
			self::GN => 'GIN',
			self::GW => 'GNB',
			self::GY => 'GUY',
			self::HT => 'HTI',
			self::HM => 'HMD',
			self::VA => 'VAT',
			self::HN => 'HND',
			self::HK => 'HKG',
			self::HR => 'HRV',
			self::HU => 'HUN',
			self::IS => 'ISL',
			self::IN => 'IND',
			self::ID => 'IDN',
			self::IR => 'IRN',
			self::IQ => 'IRQ',
			self::IE => 'IRL',
			self::IL => 'ISR',
			self::IT => 'ITA',
			self::JM => 'JAM',
			self::JP => 'JPN',
			self::JO => 'JOR',
			self::KZ => 'KAZ',
			self::KE => 'KEN',
			self::KI => 'KIR',
			self::KP => 'PRK',
			self::KR => 'KOR',
			self::KW => 'KWT',
			self::KG => 'KGZ',
			self::LA => 'LAO',
			self::LV => 'LVA',
			self::LB => 'LBN',
			self::LS => 'LSO',
			self::LR => 'LBR',
			self::LY => 'LBY',
			self::LI => 'LIE',
			self::LT => 'LTU',
			self::LU => 'LUX',
			self::MO => 'MAC',
			self::MK => 'MKD',
			self::MG => 'MDG',
			self::MW => 'MWI',
			self::MY => 'MYS',
			self::MV => 'MDV',
			self::ML => 'MLI',
			self::MT => 'MLT',
			self::MH => 'MHL',
			self::MQ => 'MTQ',
			self::MR => 'MRT',
			self::MU => 'MUS',
			self::YT => 'MYT',
			self::MX => 'MEX',
			self::FM => 'FSM',
			self::MD => 'MDA',
			self::MC => 'MCO',
			self::MN => 'MNG',
			self::MS => 'MSR',
			self::MA => 'MAR',
			self::MZ => 'MOZ',
			self::MM => 'MMR',
			self::NA => 'NAM',
			self::NR => 'NRU',
			self::NP => 'NPL',
//			self::AN => 'ANT',
			self::NL => 'NLD',
			self::NC => 'NCL',
			self::NZ => 'NZL',
			self::NI => 'NIC',
			self::NE => 'NER',
			self::NG => 'NGA',
			self::NU => 'NIU',
			self::NF => 'NFK',
			self::MP => 'MNP',
			self::NO => 'NOR',
			self::OM => 'OMN',
			self::PK => 'PAK',
			self::PW => 'PLW',
			self::PS => 'PSE',
			self::PA => 'PAN',
			self::PG => 'PNG',
			self::PY => 'PRY',
			self::PE => 'PER',
			self::PH => 'PHL',
			self::PN => 'PCN',
			self::PL => 'POL',
			self::PT => 'PRT',
			self::PR => 'PRI',
			self::QA => 'QAT',
			self::RE => 'REU',
			self::RO => 'ROU',
			self::RU => 'RUS',
			self::RW => 'RWA',
			self::SH => 'SHN',
			self::KN => 'KNA',
			self::LC => 'LCA',
			self::PM => 'SPM',
			self::VC => 'VCT',
			self::WS => 'WSM',
			self::SM => 'SMR',
			self::ST => 'STP',
			self::SA => 'SAU',
			self::SN => 'SEN',
			self::CS => 'SCG',
			self::SC => 'SYC',
			self::SL => 'SLE',
			self::SG => 'SGP',
			self::SK => 'SVK',
			self::SI => 'SVN',
			self::SB => 'SLB',
			self::SO => 'SOM',
			self::ZA => 'ZAF',
			self::GS => 'SGS',
			self::ES => 'ESP',
			self::LK => 'LKA',
			self::SD => 'SDN',
			self::SR => 'SUR',
			self::SJ => 'SJM',
			self::SZ => 'SWZ',
			self::SE => 'SWE',
			self::CH => 'CHE',
			self::SY => 'SYR',
			self::TW => 'TWN',
			self::TJ => 'TJK',
			self::TZ => 'TZA',
			self::TH => 'THA',
			self::TL => 'TLS',
			self::TG => 'TGO',
			self::TK => 'TKL',
			self::TO => 'TON',
			self::TT => 'TTO',
			self::TN => 'TUN',
			self::TR => 'TUR',
			self::TM => 'TKM',
			self::TC => 'TCA',
			self::TV => 'TUV',
			self::VI => 'VIR',
			self::UG => 'UGA',
			self::UA => 'UKR',
			self::AE => 'ARE',
			self::GB => 'GBR',
			self::UM => 'UMI',
			self::US => 'USA',
			self::UY => 'URY',
			self::UZ => 'UZB',
			self::VU => 'VUT',
			self::VE => 'VEN',
			self::VN => 'VNM',
			self::WF => 'WLF',
			self::EH => 'ESH',
			self::YE => 'YEM',
			self::ZM => 'ZMB',
			self::ZW => 'ZWE',
		][$country] ?? $country;
	}
}


abstract class MwsEmailType extends MwsBasicEnum
{
	const NewOrder = 'new_order';
	const FinishedOrder = 'finished_order';
	const OrderReadyToPickup = 'order_ready_to_pickup';
	const OrderPaymentFailed = 'order_payment_failed';
	const PayedOrder = 'payed_order';
	const SentInvoice = 'sent_invoice';
	const ElectronicDelivery = 'electronic_delivery';
	const CustomEmails = 'custom_emails';

	protected static function doInitCaptions(): array
	{
		return [
			self::NewOrder => __('Oznámení o přijetí objednávky', 'mwshop'),
			self::FinishedOrder => __('Oznámení o vyřízení objednávky', 'mwshop'),
			self::OrderReadyToPickup => __('Výzva k vyzvednutí zboží', 'mwshop'),
			self::PayedOrder => __('Oznámení o zaplacení objednávky', 'mwshop'),
			self::OrderPaymentFailed => __('Při pokusu o zaplacení došlo k chybě', 'mwshop'),
			self::SentInvoice => __('Ruční odeslání faktury', 'mwshop'),
			self::ElectronicDelivery => __('Doručení elektronických produktů (po zaplacení)', 'mwshop'),
			self::CustomEmails => __('Vlastní emaily', 'mwshop'),
		];
	}
}

abstract class MwsAutomationEvent extends MwsBasicEnum
{
	const OnOrder = 'onorder';
	const OnPaid = 'onpaid';
	const OnFinish = 'onfinish';
	const OnStorno = 'onstorno';

	protected static function doInitCaptions(): array
	{
		return [
			self::OnOrder => __('Při objednání', 'mwshop'),
			self::OnPaid => __('Při zaplacení', 'mwshop'),
			self::OnFinish => __('Při vyřízení', 'mwshop'),
			self::OnStorno => __('Při stornování', 'mwshop'),
		];
	}
}

abstract class MwsAutomationAction extends MwsBasicEnum
{
	const AddContact = 'add_contact';
	const RemoveContact = 'remove_contact';
	const AddMembership = 'add_membership';
	const RemoveMembership = 'remove_membership';
	const RunScript = 'run_script';
	const SendFile = 'send_file';
	const SendEmail = 'send_email';

	protected static function doInitCaptions(): array
	{
		return [
			self::AddContact => __('Přidat kontakt do seznamu', 'mwshop'),
			self::RemoveContact => __('Odebrat kontakt ze seznamu', 'mwshop'),
			self::AddMembership => __('Vytvořit/upravit přístup do členské sekce', 'mwshop'),
			self::RemoveMembership => __('Zrušit přístup do členské sekce', 'mwshop'),
			self::RunScript => __('Spustit programový skript', 'mwshop'),
			self::SendEmail => __('Odeslat email', 'mwshop'),
		];
	}
}
