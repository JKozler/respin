<?php

/**
 * List of payments methods. Types of payment methods is a fixed list. Methods can be allowed or disabled.
 * User: kuba
 * Date: 05.04.16
 * Time: 12:41
 */

/**
 * Enumeration of all payment methods supported by shop.
 *
 * @TODO rename to MwsPaymentMethodType
 */
class MwsPayType extends MwsBasicEnum
{
	const Wire = 'wire'; // bank transfer
	const Cod = 'cod'; // pay on delivery
	const CreditCard = 'creditCard';
	const WireOnline = 'wireOnline'; // fast bank transfer
	const Sms = 'sms';
	const Twisto = 'twisto';
	const PayPal = 'paypal';
	const Bitcoin = 'bitcoin';
	const GooglePay = 'googlePay';
	const ApplePay = 'applePay';

	private static array $pohodaMapping = [
		self::Wire => 'draft',
		self::Cod => 'delivery', // or "cash" ?
		self::CreditCard => 'creditcard',
		self::WireOnline => 'draft',
		self::Sms => 'creditcard',
		self::Twisto => 'draft',
		self::PayPal => 'creditcard',
		self::Bitcoin => 'creditcard',
		self::GooglePay => 'creditcard',
	];

	protected static function doInitCaptions(): array
	{
		return [
			self::Wire => __('Bankovní převod (1-2 dny)', 'mwshop'),
			self::CreditCard => __('Online platební karta (ihned)', 'mwshop'),
			self::WireOnline => __('Online bankovní převod (ihned)', 'mwshop'),
			self::Sms => __('SMS (m-platba) (ihned)', 'mwshop'),
			self::Twisto => __('Twisto', 'mwshop'),
			self::PayPal => __('PayPal (ihned)', 'mwshop'),
			self::Cod => __('Při převzetí', 'mwshop'),
			self::Bitcoin => __('Platba Bitcoiny', 'mwshop'),
			self::GooglePay => __('Google Pay', 'mwshop'),
			self::ApplePay => __('Apple Pay', 'mwshop'),
		];
	}

	public static function getDescription(string $paymentMethodType): string
	{
		return [
			self::Wire => __('Běžný bankovní převod je známý a oblíbený převod peněz mezi dvěma bankovními ústavy. Platbu lze zadat kdykoliv, ale zpracována je v úředních hodinách vaší banky. Zadejte platební příkaz ve vaší bance.', 'mwshop'),
			self::CreditCard => __('Platba platební kartou přes internet patří mezi dnes nejrozšířenější platební metody. Platební karty jsou chytře zabezpečené, rychlé a každý má v peněžence alespoň jednu. Informace o zaplacení se k obchodníkovi dostane ihned. Budete přesměrováni na platební bránu.', 'mwshop'),
			self::WireOnline => __('Jedná se o oblíbenou bankovní platbu na jedno kliknutí s předvyplněným platebním příkazem přímo z vašeho internetového bankovnictví a okamžitým převodem peněz na účet obchodníka. Budete přesměrováni na platební bránu.', 'mwshop'),
			self::Sms => __('m-platba je platební metoda, která umožňuje zadat příkaz k převodu peněz a zaplatit prostřednictvím mobilního telefonu. Tuto službu musíte mít povolenou u vašeho mobilního operátora. S pomocí mPlatby lze zaplatit částky až do výše 1500 Kč. Budete přesměrováni na platební bránu.', 'mwshop'),
			self::PayPal => __('Systém PayPal je elektronický internetový platební prostředek a nejrozšířenějším celosvětově používaným systémem pro online platby. Účet v systému PayPal funguje podobně jako běžný bankovní účet a přesun peněz z účtu kupujícího na účet prodávajícího probíhá okamžitě jako kdyby měli stejnou banku – PayPal. Budete přesměrováni na platební bránu.', 'mwshop'),
			self::Cod => __('Platba při převzetí je platební metoda, kdy se vybírá částka za produkt až při převzetí produktu od přepravce nebo v prodejně. Platba od vás bude vyžadována při převzetí zboží.', 'mwshop'),
			self::Bitcoin => __('Platba pomocí nejznámější virtuální měny Bitcoin. Budete přesměrováni na platební bránu a zprostředkovatele plateb BitcoinPay.', 'mwshop'),
		][$paymentMethodType] ?? '';
	}

	public static function isGateway(string $paymentMethodType): bool
	{
		return !in_array($paymentMethodType, [self::Cod, self::Wire]);
	}

	public static function getPohodaType(string $paymentMethodType): ?string
	{
		return self::$pohodaMapping[$paymentMethodType] ?? null;
	}

}

class MwsBanks extends MwsBasicEnum
{
	const Cz_csas = 'cz_csas';
	const Cz_rb = 'cz_rb';
	const Cz_fb = 'cz_fb';
	const Cz_kb = 'cz_kb';
	const Cz_mb = 'cz_mb';
	const Cz_moneta = 'cz_moneta';
	const Cz_csob = 'cz_csob';
	const Cz_equabank = 'cz_equabank';
	const Cz_sberbank = 'cz_sberbank';
	const Cz_unicredit = 'cz_unicredit';
	const Cz_era = 'cz_era';
	const Sk_sp = 'sk_sp';
	const Sk_uni = 'sk_uni';
	const Sk_csob = 'sk_csob';
	const Sk_tatrabank = 'sk_tatrabank';
	const Sk_sberbank = 'sk_sberbank';
	const Sk_otpbank = 'sk_otpbank';
	const Sk_pabank = 'sk_pabank';
	const Sk_vubbank = 'sk_vubbank';
	const Sk_opt = 'sk_opt';

	protected static function doInitCaptions(): array
	{
		return [
			self::Cz_csas => __('Česká Spořitelna', 'mwshop'),
			self::Cz_rb => __('Raiffeisen bank', 'mwshop'),
			self::Cz_fb => __('Fio banka', 'mwshop'),
			self::Cz_kb => __('Komerční banka', 'mwshop'),
			self::Cz_mb => __('mBank', 'mwshop'),
			self::Cz_moneta => __('Moneta Money Bank', 'mwshop'),
			self::Cz_csob => __('ČSOB', 'mwshop'),
			self::Cz_equabank => __('Equa bank', 'mwshop'),
			self::Cz_sberbank => __('Sperbank', 'mwshop'),
			self::Cz_unicredit => __('UniCredit Bank', 'mwshop'),
			self::Cz_era => __('Era Poštovní spořitelna', 'mwshop'),
			self::Sk_sp => __('Slovenská sporiteľňa', 'mwshop'),
			self::Sk_uni => __('UniCredit Bank', 'mwshop'),
			self::Sk_csob => __('ČSOB', 'mwshop'),
			self::Sk_tatrabank => __('Tatra Banka', 'mwshop'),
			self::Sk_sberbank => __('Sperbank', 'mwshop'),
			self::Sk_otpbank => __('OTP Bank', 'mwshop'),
			self::Sk_pabank => __('Poštová Banka', 'mwshop'),
			self::Sk_vubbank => __('VÚB banka', 'mwshop'),
			self::Sk_opt => __('OTP Bank', 'mwshop'),
		];
	}

}
