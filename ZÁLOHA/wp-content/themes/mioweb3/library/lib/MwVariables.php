<?php
declare(strict_types=1);

use Mioweb\Lib\MwPrice;
use Mioweb\Shop\Order\OrderItem;
use Nette\Utils\Json;

class MwVariables
{

	public static function getVariables(): array
	{
		if (isset($_GET['gw']) && isset($_GET['vs']) && class_exists('MwsOrderVariables')) {
			return MwsOrderVariables::fromOrder($_GET['vs']);
		}

		if (isset($_GET['vs'])) {
			return mwSellingApi()->getInvoiceVariables('fapi', esc_js($_GET['vs']));
		}

		return [];
	}

	public static function replaceVariables(string $text, array $variables): string
	{
		$replacements = [];
		$code = preg_replace_callback('/%%([a-zA-Z_]+)%%/', function ($match) use ($variables) {
			$parameter = strtoupper($match[1]);

			return $variables[$parameter] ?? $match[0];
		}, $text);

		return strtr($code, $replacements);
	}

	public static function generateItemsForGA($items, $currency = '', $json = true)
	{
		$arr = [];

		foreach ($items as $item) {
			if ($item instanceof OrderItem) {
				if ($item->isProduct()) {
					$arrItem = [
						'item_id' => $item->getProductId(),
						'item_name' => $item->getName(),
	//                  'list_name' => null,
	//                  'brand' => null,
	//                  'category' => null,
	//                  'list_position' => null,
						'quantity' => $item->getCount(),
						'price' => $item->getPrice($currency)->getPriceVatExcluded(),
					];

					$product = $item->getProduct();
					if ($product && $product->isVariant()) {
						$arrItem['item_name'] = $product->getProduct()->getName();
						$arrItem['item_variant'] = $product->composeVariantDesc();
					}

					$arr[] = $arrItem;
				}
			} elseif (is_array($item)) {
				if ($item['type'] !== 'discount' && $item['type'] !== 'shipping') {
					$price = $item['including_vat'] ?? false ? round($item['price'] - MwPrice::calculateVatByPriceVatIncluded($item['price'], $item['vat']), 2) : $item['price'];

					$arr[] = [
						'item_id' => $item['id'],
						'item_name' => $item['name'],
						'quantity' => $item['count'],
						'price' => $price,
					];
				}
			}
		}

		return $json ? Json::encode($arr) : $arr;
	}

	public static function addAliases(array $variables): array
	{
		$variables['SLEVOVY_KOD'] = $variables['VOUCHER'] = $variables['DISCOUNT_CODE'] ?? null;
		$variables['MENA'] = $variables['CURRENCY'] ?? null;
		$variables['NATIVNI_MENA'] = $variables['NATIVE_CURRENCY'] ?? null;
		$variables['MENA_SYMBOL'] = $variables['CURRENCY_SYMBOL'] ?? null;
		$variables['NATIVNI_MENA_SYMBOL'] = $variables['NATIVE_CURRENCY_SYMBOL'] ?? null;
		$variables['PLATBA_ONLINE'] = $variables['ONLINE_PAY'] ?? null;
		$variables['PLATBA_URL'] = $variables['PAYMENT_URL'] ?? null;
		$variables['PODMINENY_PREVOD_INFO'] = $variables['IF_TRANSFER_INFO'] ?? null;
		$variables['PREVOD_INFO'] = $variables['TRANSFER_INFO'] ?? null;
		$variables['VARIABILNI_SYMBOL'] = $variables['VARIABLE_SYMBOL'] ?? null;
		$variables['CISLO_UCTU'] = $variables['BANK_ACCOUNT'] ?? null;
		$variables['ESHOP_TELEFON'] = $variables['ESHOP_PHONE'] ?? null;
		$variables['URL_WEBU'] = $variables['WEBSITE_URL'] ?? null;
		$variables['NAZEV_WEBU'] = $variables['WEBSITE_NAME'] ?? null;
		$variables['POZNAMKA'] = $variables['NOTE'] ?? null;
		$variables['DORUCOVACI_KONTAKT'] = $variables['SHIPPING_CONTACT'] ?? null;
		$variables['FAKTURACNI_KONTAKT'] = $variables['BILLING_CONTACT'] ?? null;
		$variables['DORUCOVACI_ADRESA'] = $variables['SHIPPING_ADDRESS'] ?? null;
		$variables['FAKTURACNI_ADRESA'] = $variables['BILLING_ADDRESS'] ?? null;
		$variables['CENA_DORUCENI_BEZ_DPH'] = $variables['SHIPPING_PRICE_NOVAT'] ?? null;
		$variables['CENA_DORUCENI'] = $variables['CENA_DORUCENI_S_DPH'] = $variables['SHIPPING_PRICE_VAT'] = $variables['SHIPPING_PRICE'] ?? null;
		$variables['ZPUSOB_DORUCENI'] = $variables['SHIPPING_METHOD'] ?? null;
		$variables['CENA_PLATBY_BEZ_DPH'] = $variables['PAYMENT_PRICE_NOVAT'] ?? null;
		$variables['CENA_PLATBY'] = $variables['CENA_PLATBY_S_DPH'] = $variables['PAYMENT_PRICE_VAT'] = $variables['PAYMENT_PRICE'] ?? null;
		$variables['ZPUSOB_PLATBY'] = $variables['PAYMENT_METHOD'] ?? null;
		$variables['CENA_BEZ_DPH'] = $variables['PRICE_NOVAT'] ?? null;
		$variables['CENA'] = $variables['CENA_S_DPH'] = $variables['PRICE_VAT'] = $variables['PRICE'] ?? null;
		$variables['NATIVNI_CENA_BEZ_DPH'] = $variables['NATIVE_PRICE_NOVAT'] ?? null;
		$variables['NATIVNI_CENA'] = $variables['NATIVNI_CENA_S_DPH'] = $variables['NATIVE_PRICE_VAT'] = $variables['NATIVE_PRICE'] ?? null;
		$variables['OBSAH_OBJEDNAVKY'] = $variables['ORDER_CONTENT'] ?? null;
		$variables['DATUM_OBJEDNAVKY'] = $variables['ORDER_DATE'] ?? null;
		$variables['CISLO_OBJEDNAVKY'] = $variables['ORDER_CODE'] ?? null;
		$variables['INFO_OBJEDNAVKY'] = $variables['ORDER_INFO'] ?? null;
		$variables['SLEDOVANI_ZASILKY'] = $variables['TRACKING_LINK'] ?? null;

		$variables['CENA_BEZ_DOPRAVY'] = $variables['PRICE_NO_SHIPPING'] ?? null;
		$variables['CENA_BEZ_DOPRAVY_BEZ_DPH'] = $variables['PRICE_NO_SHIPPING_NOVAT'] ?? null;

		$variables['NATIVNI_CENA_BEZ_DOPRAVY'] = $variables['NATIVE_PRICE_NO_SHIPPING'] ?? null;
		$variables['NATIVNI_CENA_BEZ_DOPRAVY_BEZ_DPH'] = $variables['NATIVE_PRICE_NO_SHIPPING_NOVAT'] ?? null;
		$variables['ID_VYDEJNIHO_MISTA_ZASILKOVNY'] = $variables['PARCEL_POINT_ID'] ?? null;
		$variables['KLIENT_TELEFON'] = $variables['CLIENT_PHONE'] ?? null;
		$variables['TELEFON'] = $variables['PHONE'] ?? null;
		$variables['KLIENT_EMAIL'] = $variables['CLIENT_EMAIL'] ?? null;

		return $variables;
	}

	public static function getVariableList(): array
	{
		$list = [
			[
				'code' => 'CISLO_OBJEDNAVKY',
				'desc' => __('Číslo objednávky.'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'VARIABILNI_SYMBOL',
				'desc' => __('Variabilní symbol objednávky.'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'DATUM_OBJEDNAVKY',
				'desc' => __('Datum vytvoření objednávky.'),
				'show' => ['email'],
			],
			[
				'code' => 'INFO_OBJEDNAVKY',
				'desc' => __('Vypíšou se veškeré potřebné informace o objednávce.'),
				'show' => ['email'],
			],
			[
				'code' => 'CENA',
				'desc' => __('Celková cena objednávky (v&nbsp;měně, ve které byla objednávka vytvořena).'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'CENA_BEZ_DPH',
				'desc' => __('Celková cena objednávky bez DPH (v&nbsp;měně, ve které byla objednávka vytvořena).'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'CENA_BEZ_DOPRAVY',
				'desc' => __('Celková cena objednávky bez ceny za dopravu a platbu (v&nbsp;měně, ve které byla objednávka vytvořena).'),
				'show' => ['conversion'],
			],
			[
				'code' => 'CENA_BEZ_DOPRAVY_BEZ_DPH',
				'desc' => __('Celková cena objednávky bez ceny za dopravu a platbu bez DPH (v&nbsp;měně, ve které byla objednávka vytvořena).'),
				'show' => ['conversion'],
			],
			[
				'code' => 'MENA',
				'desc' => __('Měna objednávky (např. CZK)'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'MENA_SYMBOL',
				'desc' => __('Symbol měny (např. Kč)'),
				'show' => ['email'],
			],
			[
				'code' => 'NATIVNI_CENA',
				'desc' => __('Celková cena objednávky v&nbsp;nativní měně eshopu.'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'NATIVNI_CENA_BEZ_DPH',
				'desc' => __('Celková cena objednávky bez DPH v&nbsp;nativní měně eshopu.'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'NATIVNI_CENA_BEZ_DOPRAVY',
				'desc' => __('Celková cena objednávky bez ceny za dopravu a platbu v&nbsp;nativní měně eshopu.'),
				'show' => ['conversion'],
			],
			[
				'code' => 'NATIVNI_CENA_BEZ_DOPRAVY_BEZ_DPH',
				'desc' => __('Celková cena objednávky bez ceny za dopravu a platbu bez DPH v&nbsp;nativní měně eshopu.'),
				'show' => ['conversion'],
			],
			[
				'code' => 'NATIVNI_MENA',
				'desc' => __('Nativní měna'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'NATIVNI_MENA_SYMBOL',
				'desc' => __('Nativní měna'),
				'show' => ['email'],
			],
			[
				'code' => 'OBSAH_OBJEDNAVKY',
				'desc' => __('Tabulka se seznamem objednaných produktů a&nbsp;celkovou cenou.'),
				'show' => ['email'],
			],
			[
				'code' => 'GA_ITEMS',
				'desc' => __('Seznam objednaných produktů ve formátů vhodném pro konverzní kód Google Analytics.'),
				'show' => ['conversion'],
			],
			[
				'code' => 'SLEVOVY_KOD',
				'desc' => __('Slevový kód použitý v&nbsp;objednávce.'),
				'show' => ['email','conversion','invoice'],
			],
			[
				'code' => 'FAKTURACNI_ADRESA',
				'desc' => __('Fakturační adresa.'),
				'show' => ['email'],
			],
			[
				'code' => 'FAKTURACNI_KONTAKT',
				'desc' => __('Kontaktní údaje na zákazníka získané z&nbsp;fakturačních údajů.'),
				'show' => ['email'],
			],
			[
				'code' => 'DORUCOVACI_ADRESA',
				'desc' => __('Doručovací adresa.'),
				'show' => ['email'],
			],
			[
				'code' => 'DORUCOVACI_KONTAKT',
				'desc' => __('Kontaktní údaje na zákazníka získané z&nbsp;doručovacích údajů.'),
				'show' => ['email'],
			],
			[
				'code' => 'ZPUSOB_PLATBY',
				'desc' => __('Způsob platby, který si klient zvolil. Pokud je jako způsob platby zvolen nějaký druh online platby a objednávka není zaplacena tak se vypíše i odkaz na zaplacení online. Pokud je zvolen způsob platby bankovním převodem, vypíšou se taky údaje potřebné pro platbu převodem.'),
				'show' => ['email','invoice'],
			],
			[
				'code' => 'ZPUSOB_DORUCENI',
				'desc' => __('Způsob doručení, který si klient zvolil. Pokud je zvolen způsob doručení na výdejní místo zásilkovny, vypíše se také adresa zvoleného výdejního místa.'),
				'show' => ['email','invoice'],
			],
			[
				'code' => 'SLEDOVANI_ZASILKY',
				'desc' => __('Odkaz pro sledování zásilky. Při vyplnění sledovacího čísla zásilky se vypíše odkaz na web pro její sledování (pokud to způsob doručení podporuje). Při vlastním doručení je možné část odkazu před sledovacím číslem vložit vlastní v nastavení způsobu doručení.'),
				'show' => ['email'],
			],
			[
				'code' => 'CENA_DORUCENI',
				'desc' => __('Cena dopravy.'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'CENA_DORUCENI_BEZ_DPH',
				'desc' => __('Cena dopravy bez DPH.'),
				'show' => ['email','conversion'],
			],
			[
				'code' => 'CISLO_UCTU',
				'desc' => __('Číslo účtu pro měnu objednávky.'),
				'show' => ['email'],
			],
			[
				'code' => 'PREVOD_INFO',
				'desc' => __('Informace potřebné při platbě převodem na účet. Obsahuje číslo účtu (IBAN a&nbsp;SWIFT), variabilní symbol a&nbsp;částku k&nbsp;zaplacení.'),
				'show' => ['email'],
			],
			[
				'code' => 'PODMINENY_PREVOD_INFO',
				'desc' => __('Informace potřebné při platbě převodem na účet, které se vypíšou jen v&nbsp;případě, že si zákazník tento způsob platby vybral. Jinak se nevypíše nic.'),
				'show' => ['email'],
			],
			[
				'code' => 'PLATBA_ONLINE',
				'desc' => __('Odkaz na zaplacení online.'),
				'show' => ['email'],
			],
			[
				'code' => 'POZNAMKA',
				'desc' => __('Poznámka uživatele.'),
				'show' => ['email','invoice'],
			],
			[
				'code' => 'ESHOP_EMAIL',
				'desc' => __('E-mailová adresa eshopu.'),
				'show' => ['email'],
			],
			[
				'code' => 'ESHOP_TELEFON',
				'desc' => __('Telefonní číslo eshopu.'),
				'show' => ['email'],
			],
			[
				'code' => 'NAZEV_WEBU',
				'desc' => __('Název webu.'),
				'show' => ['email'],
			],
			[
				'code' => 'URL_WEBU',
				'desc' => __('URL adresa webu.'),
				'show' => ['email'],
			],
			[
				'code' => 'ID_VYDEJNIHO_MISTA_ZASILKOVNY',
				'desc' => __('ID výdejního místa zásilkovny.'),
				'show' => ['invoice'],
			],
			[
				'code' => 'KLIENT_TELEFON',
				'desc' => __('Telefon zadaný u fakturační adresy.'),
				'show' => ['email'],
			],
			[
				'code' => 'TELEFON',
				'desc' => __('Telefon, který je zadaný u doručovací adresy, nebo u fakturační adresy.'),
				'show' => ['email'],
			],
			[
				'code' => 'KLIENT_EMAIL',
				'desc' => __('Email klienta.'),
				'show' => ['email'],
			],
		];

		if (!function_exists('MWS') || MWS()->getSelectedGatewayId() === 'fapi') {
			$key = array_search('KLIENT_TELEFON', array_column($list, 'code'));
			unset($list[$key]);
		}

		return $list;

		/*

		$variables['CENA_PLATBY_BEZ_DPH'] = $variables['PAYMENT_PRICE_NOVAT'];
		$variables['CENA_PLATBY'] = $variables['CENA_PLATBY_S_DPH'] = $variables['PAYMENT_PRICE_VAT'] = $variables['PAYMENT_PRICE'];

		*/
	}

	public static function variableListPop(string $type, $message = ''): string
	{
		$content = '<div class="mw_order_variable_list">';
		$content .= mwAdminComponents::button([
			'button_text' => __('Seznam proměnných', 'mwshop'),
			'style' => 'secondary_gray',
			'attrs' => 'data-title="' . __('Seznam proměnných', 'mwshop') . '"',
		], 'mw_open_order_variable_list');

		$content .= '<div class="mw_order_variable_list_pop_content">';
		$content .= '<div class="mw_order_variable_list_content">';
		$content .= mwAdminComponents::messageBox($message, [
			'type' => 'info_gray',
		]);
		$content .= '<table class="mw_table mw_table_style_1">';
		foreach (static::getVariableList() as $variable) {
			if (in_array($type, $variable['show'])) {
				$content .= '<tr><td><strong>%%' . $variable['code'] . '%%</strong></td><td>' . $variable['desc'] . '</td></tr>';
			}
		}
		$content .= '</table>';
		$content .= '</div>';

		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

}
