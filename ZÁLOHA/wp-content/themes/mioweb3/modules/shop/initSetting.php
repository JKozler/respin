<?php

use Mioweb\Shop\Order\Order;

class MwsInitSetting
{

	public static bool $isInitialized = false;

	public static function init(bool $isEshopCreated = true)
	{
		$defaultGw = MWS()->gateways()->getDefault();

				mwSetting()->registerPageSettingType('eshop_dashboard', [
					'static_class' => 'MwSellDashboard',
					'function' => 'printDashboard',
				]);

				mwSetting()->addGroup([
					'id' => MWS_OPTION_SHOP,
					'icon' => 'shopping-bag',
					'title' => __('Prodej', 'mwshop'),
					'home' => 'eshop_dashboard',
					'order' => 5,
				]);

				mwSetting()->addPage([
					'id' => 'eshop_dashboard',
					'icon' => 'bar-chart-2',
					'group' => MWS_OPTION_SHOP,
					'title' => __('Přehled', 'mwshop'),
					'type' => 'eshop_dashboard',
				]);

				mwSetting()->addPage([
					'id' => MWS_ORDER_SLUG,
					'icon' => 'shopping-bag',
					'group' => MWS_OPTION_SHOP,
					'title' => __('Objednávky', 'mwshop'),
					'info_function' => function () {
						$count = MwsOrderAdmin::getNewOrdersCount();

						return $count ? '<span class="mw_setting_menu_count">' . MwsOrderAdmin::getNewOrdersCount() . '</span>' : '';
					},
					'type' => 'list',
				]);
				mwSetting()->addPage([
					'id' => MWS_PRODUCT_SLUG,
					'icon' => 'package',
					'group' => MWS_OPTION_SHOP,
					'title' => __('Produkty', 'mwshop'),
					'type' => 'list',
				]);
				if ($isEshopCreated) {
			mwSetting()->addPage([
		'id' => MWS_PROPERTY_SLUG,
		'parent' => MWS_PRODUCT_SLUG,
		'group' => MWS_OPTION_SHOP,
		'title' => __('Parametry produktů', 'mwshop'),
		'type' => 'list',
			]);
	mwSetting()->addPage([
'id' => MWS_PRODUCT_CAT_SLUG,
'icon' => 'list',
'group' => MWS_OPTION_SHOP,
'title' => __('Kategorie', 'mwshop'),
'type' => 'list',
	]);
	mwSetting()->addPage([
'id' => MWS_PRODUCT_TAG_SLUG,
'icon' => 'tag',
'group' => MWS_OPTION_SHOP,
'title' => __('Štítky', 'mwshop'),
'type' => 'list',
	]);
				}
				if (MWS()->getSelectedGatewayId() == 'mioweb') {
			mwSetting()->addPage([
		'id' => MWS_FORM_SLUG,
		'icon' => 'dollar-sign',
		'group' => MWS_OPTION_SHOP,
		'title' => __('Prodejní formuláře', 'mwshop'),
		'type' => 'list',
			]);
				}
				mwSetting()->addPage([
					'id' => MWS_DISCOUNT_CODE_SLUG,
					'icon' => 'percent',
					'group' => MWS_OPTION_SHOP,
					'title' => __('Slevové kódy', 'mwshop'),
					'type' => 'list',
				]);
				mwSetting()->addPage([
					'id' => MWS_SHIPPING_SLUG,
					'icon' => 'truck',
					'group' => MWS_OPTION_SHOP,
					'title' => __('Způsoby doručení', 'mwshop'),
					'type' => 'list',
				]);
				mwSetting()->addPage([
					'id' => MWS_SHIPPING_COUNTRY_SLUG,
					'parent' => MWS_SHIPPING_SLUG,
					'group' => MWS_OPTION_SHOP,
					'title' => __('Země doručení', 'cms_member'),
					'type' => 'list',
				]);
				mwSetting()->addPage([
					'id' => MWS_PAYMENT_METHOD_SLUG,
					'icon' => 'credit-card',
					'group' => MWS_OPTION_SHOP,
					'title' => __('Způsoby platby', 'mwshop'),
					'type' => 'list',
				]);
				// setting
				mwSetting()->addPage([
					'id' => MWS_OPTION_SHOP_SETTING,
					'icon' => 'settings',
					'group' => MWS_OPTION_SHOP,
					'title' => __('Nastavení', 'mwshop'),
					'service_class' => 'mwSettingPageService_eshopSetting',
				]);
				mwSetting()->addPage([
					'id' => MWS_CURRENCY_SLUG,
					'parent' => MWS_OPTION_SHOP_SETTING,
					'group' => MWS_OPTION_SHOP,
					'title' => __('Měny', 'cms_member'),
					'type' => 'list',
				]);
				if ($isEshopCreated) {
			mwSetting()->addPage([
		'id' => 'eshop_actions',
		'parent' => MWS_OPTION_SHOP_SETTING,
		'group' => MWS_OPTION_SHOP,
		'title' => __('Automatizace', 'mwshop'),
			]);
				}
				mwSetting()->addPage([
					'id' => 'eshop_emails',
					'parent' => MWS_OPTION_SHOP_SETTING,
					'group' => MWS_OPTION_SHOP,
					'service_class' => 'mwSettingPageService_eshopEmails',
					'title' => __('Emaily', 'mwshop'),
				]);
				if ($isEshopCreated) {
			mwSetting()->addPage([
		'id' => 'eshop_comparers',
		'parent' => MWS_OPTION_SHOP_SETTING,
		'group' => MWS_OPTION_SHOP,
		'title' => __('Srovnávače cen', 'mwshop'),
			]);
	mwSetting()->addPage([
'id' => 'mw_eshop_codes',
'parent' => MWS_OPTION_SHOP_SETTING,
'group' => MWS_OPTION_SHOP,
'title' => __('Vlastní kódy', 'mwshop'),
	]);
	mwSetting()->addPage([
'id' => 'eshop_popups',
'parent' => MWS_OPTION_SHOP_SETTING,
'group' => MWS_OPTION_SHOP,
'title' => __('Pop-upy eshopu', 'mwshop'),
	]);

	// appearance
	mwSetting()->addPage([
'id' => MWS_OPTION_SHOP_APPEARANCE,
'icon' => 'layout',
'group' => MWS_OPTION_SHOP,
'title' => __('Vzhled a obsah', 'mwshop'),
	]);
	mwSetting()->addPage([
'id' => 'eshop_header',
'parent' => MWS_OPTION_SHOP_APPEARANCE,
'group' => MWS_OPTION_SHOP,
'title' => __('Hlavička eshopu', 'mwshop'),
	]);
	mwSetting()->addPage([
'id' => 'eshop_footer',
'parent' => MWS_OPTION_SHOP_APPEARANCE,
'group' => MWS_OPTION_SHOP,
'title' => __('Patička eshopu', 'mwshop'),
	]);
				}

				$eshopSetting = [];
				$eshopSetting = $isEshopCreated ? [
				[
			'id' => 'home_page',
			'name' => __('Úvodní stránka obchodu', 'mwshop'),
			'type' => 'selectpage',
			'add_button' => true,
			'edit_button' => true,
			'whisperer' => true,
			'content' => '',
			'tooltip' => __('Vyberte stránku, na které chcete mít úvodní stránku eshopu. Defaultně se bude na této stránce vypisovat seznam produktů. Ve nastavení vzhledu si ale můžete zobrazení úvodní stránky přizpůsobit svým potřebám.', 'mwshop'),
				],
				[
			'id' => 'order_page',
			'name' => __('Stránka košíku', 'mwshop'),
			'type' => 'selectpage',
			'add_button' => true,
			'edit_button' => true,
			'whisperer' => true,
			'content' => '',
			'tooltip' => __('Vyberte stránku na které chcete mít nákupní košík. Tato stránka poté bude sloužit k editaci nákupního košíku a k dokončení a vytvoření objednávky.', 'mwshop'),
				],
				[
			'title' => __('Odkaz v logu v hlavičce e-shopu', 'mwshop'),
			'id' => 'shop_logo_link',
			'type' => 'radio',
			'options' => [
				'web' => __('Odkazovat na úvodní stránku webu', 'mwshop'),
				'shop' => __('Odkazovat na úvodní stránku e-shopu', 'mwshop'),
			],
			'content' => 'web',
			'description' => __('Vyberte cíl odkazu loga e-shopu.', 'mwshop'),
				],
				] : [
				[
			'id' => 'install_shop',
			'type' => 'install_shop',
				],
				];

				$smtp = get_option('web_option_smtp');
				$isSmtpEnabled = isset($smtp['use_smtp'], $smtp['smtp_host'], $smtp['smtp_email']) && is_email($smtp['smtp_email']) && (bool) $smtp['smtp_host'] && (bool) $smtp['use_smtp'];
				mwSetting()->addPageSetting(MWS_OPTION_SHOP_SETTING, [
					[
						'type' => 'tabs',
						'id' => 'emailing',
						'tabs' => [
							'basic' => [
								'name' => __('Obecné', 'cms_ve'),
								'setting' => [
									[
										'id' => 'eshop',
										'type' => 'toggle_group',
										'title' => __('Eshop', 'mwshop'),
										'open' => true,
										'setting' => $eshopSetting,
									],
									[
										'id' => 'basic',
										'type' => 'toggle_group',
										'title' => __('Základní nastavení', 'mwshop'),
										'open' => true,
										'setting' => [
											[
												'id' => 'notification_mail',
												'name' => __('Upozornění na novou objednávku zasílat na email', 'mwshop'),
												'type' => 'text',
												'content' => '',
												'tooltip' => __('Na zadanou emailovou adresu bude odesílány upozornění na nové objednávky.', 'mwshop'),
											],
										],
									],
									[
										'id' => 'email',
										'type' => 'toggle_group',
										'title' => __('E-mailová komunikace', 'mwshop'),
										'open' => true,
										'setting' => [
											[
												'type' => 'info',
												'class' => $isSmtpEnabled ? '' : 'cms_nodisp',
												'content' => sprintf(__('Pokud máte v nastavení <a href="%s" target="_blank">E-mail (SMTP)</a> nastavené že se k odesílání e-mailů má použít vlastní SMTP server a chcete aby e-maily prodejního modulu zákazníkům chodily z jiného e-mailu, než máte nastavený tam, doporučujeme kvůli lepší doručitelnosti nastavit zde e-mail, který bude mít stejnou doménu, jako ten v nastavení SMTP.', 'mwshop'), get_mw_admin_url('web_option_smtp')),
											],
											[
												'id' => 'sender_mail',
												'name' => $isSmtpEnabled ? __('E-mailová adresa pro odpovědi od zákazníků', 'mwshop') : __('E-mailová adresa odesílatele', 'mwshop'),
												'type' => 'text',
												'content' => '',
												'tooltip' => $isSmtpEnabled ? __('Odpovědi od zákazníků na automatické emaily budou zasílány na tuto adresu.', 'mwshop') : __('Automatické emaily budou zákazníkům odesílány z této emailové adresy.', 'mwshop'),
											],
											[
												'id' => 'sender_name',
												'name' => __('Jméno odesílatele', 'mwshop'),
												'type' => 'text',
												'content' => '',
												'tooltip' => __('Automatické emaily budou zákazníkům odesílány pod tímto jménem.', 'mwshop'),
											],
										],
									],
									[
										'id' => 'vat_setting',
										'type' => 'toggle_group',
										'title' => __('Nastavení DPH', 'mwshop'),
										'setting' => [
											[
												'content' => __('V případě že máte prodej napojený na FAPI je potřeba aby bylo toto nastavení shodné s tím co máte nastaveno ve FAPI. Jinak nebudou sedět výpočty košíku a vystavované faktury.', 'mwshop'),
												'id' => 'vat_accounting_info',
												'type' => 'info',
												'show_group' => 'paygate',
												'show_val' => 'fapi',
											],
											[
												'id' => 'vat_accounting',
												'title' => __('Účtování DPH', 'mwshop'),
												'type' => 'select',
												'content' => MwsVatAccounting::noVat,
												'options' => [
													['name' => __('Neplátce DPH', 'mwshop'), 'value' => MwsVatAccounting::noVat],
													['name' => __('Identifikovaná osoba', 'mwshop'), 'value' => MwsVatAccounting::noVatIdentified],
													['name' => __('Plátce DPH', 'mwshop'), 'value' => MwsVatAccounting::withVat],
												],
												'show' => 'vat_setting',
											],
											[
												'content' => __('Zadejte až 5 sazeb DPH. ' .
													'Nechcete-li některou sazbu DPH používat, vymažte její hodnotu. ' .
													'Změna hodnoty DPH u jednotlivé sazby se projeví u všech produktů, které tuto sazbu používají. ' .
													'"Sazba 1" slouží jako výchozí sazba a bude použita u produktů, které nemají žádnou sazbu přiřazenou. ', 'mwshop'),
												'id' => 'vat_info',
												'type' => 'info',
												'show_group' => 'vat_setting',
												'show_val' => 'withVat',
											],
											[
												'id' => 'vat_values',
												'type' => 'vatvalues',
												'content' => [21, 15, 10, 0],
												'show_group' => 'vat_setting',
												'show_val' => 'withVat',
											],
											[
												'id' => 'vat_electronic_products_invoicing',
												'title' => __('Prodej zboží a elektronicky poskytovaných služeb', 'mwshop'),
												'type' => 'select',
												'options' => [
													['name' => __('do EU fakturovat v režimu OSS', 'mwshop'), 'value' => MwsVatElectronicInvoicing::Oss],
													['name' => __('do EU fakturovat s místem plnění v tuzemsku', 'mwshop'), 'value' => MwsVatElectronicInvoicing::Inland],
												],
												'content' => MwsVatElectronicInvoicing::Inland,
												'show_group' => 'vat_setting',
												'show_val' => 'withVat,noVatIdentified',
												'tooltip' => __('Do obratu 10000 EUR v kalendářním roce je možné prodávat zboží a elektronicky poskytované služby do zemí EU s místem plnění v tuzemsku. Při překročení tohoto limitu je třeba se zaregistrovat do systému OSS, popř. se stát plátcem DPH v cílové zemi EU.', 'mwshop'),
											],
											[
												'id' => 'vat_rates_open_modal',
												'title' => __('Sazby DPH v EU', 'mw_funnels'),
												'type' => 'static',
												'content' => mwAdminComponents::button([
		//                                          'icon' => 'dollar-sign',
													'button_text' => __('Upravit sazby DPH v EU', 'mw_funnels'),
												], 'mws_edit_vat_rates'),
												'show_group' => 'vat_setting',
												'show_val' => 'withVat,noVatIdentified',
											],
										],
									],
									[
										'id' => 'biling_setting',
										'type' => 'toggle_group',
										'title' => __('Fakturační údaje', 'mwshop'),
										'show_group' => 'paygate',
										'show_val' => 'mioweb',
										'setting' => [
											[
												'id' => 'company_id',
												'title' => __('IČ', 'mwshop'),
												'type' => 'text',
											],
											[
												'id' => 'company_tax_id',
												'title' => __('DIČ', 'mwshop'),
												'type' => 'text',
											],
											[
												'id' => 'company_vat_id',
												'title' => __('IČ DPH', 'mwshop'),
												'type' => 'text',
												'show_group' => 'country',
												'show_val' => 'SK',
											],
											[
												'id' => 'company_name',
												'title' => __('Jméno firmy/Jméno a příjmení', 'mwshop'),
												'type' => 'text',
											],
											[
												'id' => 'street',
												'title' => __('Ulice a číslo domu', 'mwshop'),
												'type' => 'text',
											],
											[
												'id' => 'city',
												'title' => __('Město', 'mwshop'),
												'type' => 'text',
											],
											[
												'id' => 'zip',
												'title' => __('PSČ', 'mwshop'),
												'type' => 'text',
											],
											[
												'id' => 'country',
												'show' => 'country',
												'title' => __('Země', 'mwshop'),
												'type' => 'country_select',
												'content' => 'CZ',
											],
				//                          [
				//                              'id' => 'country',
				//                              'title' => __('Spisová značka', 'mwshop'),
				//                              'type' => 'text',
				//                          ],
											[
												'id' => 'phone',
												'title' => __('Telefon', 'mwshop'),
												'type' => 'text',
											],
										],
									],
								],
							],
							'orders' => [
								'name' => __('Prodej a fakturace', 'cms_ve'),
								'setting' => [
									[
										'id' => 'paygate_setting',
										'type' => 'toggle_group',
										'title' => __('Napojení', 'mwshop'),
										'open' => true,
										'setting' => [
											[
												'name' => __('Faktury a platby zpracovávat pomocí', 'cms_ve'),
												'id' => 'paygate',
												'type' => 'paygate_select',
												'options' => [
													'mioweb' => [
														'image' => MWS_URL_BASE . '/img/mw_logo.jpg',
														'text' => __('Mioweb', 'cms_ve'),
													],
													'fapi' => [
														'image' => MWS_URL_BASE . '/img/fapi_logo.jpg',
														'text' => __('Fapi', 'cms_ve'),
													],
												],
												'content' => 'mioweb',
												'show' => 'paygate',
											],
										],
									],
									[
										'id' => 'orders_setting',
										'type' => 'toggle_group',
										'title' => __('Nastavení objednávek', 'mwshop'),
										'setting' => [
											[
												'id' => 'order_nums',
												'title' => __('Číselná řada objednávky', 'mwshop'),
												'type' => 'number_series',
												'content' => [
													'prefix' => 'YYYY',
													'characters' => 4,
													'start' => 1,
												],
												'show_group' => 'paygate',
												'show_val' => 'mioweb',
											],
											[
												'id' => 'min_order',
												'title' => __('Výše minimální objednávky', 'mwshop'),
												'type' => 'text',
												'content' => '0',
												'tooltip' => __('Objednávka nepůjde dokončit dokud nebude v košíku zboží minimálně v této hodnotě.', 'mwshop'),
											],
											[
												'id' => 'phone_required',
												'title' => __('Povinnost zadat telefon', 'mwshop'),
												'label' => __('Telefonní číslo je vyžadováno jako povinný údaj', 'mwshop'),
												'type' => 'switch',
												'tooltip' => __('Pokud klient nezadá platné telefonní číslo, nebude možné objednávku dokončit.', 'mwshop'),
											],
										],
									],
									[
										'id' => 'invoice_setting',
										'type' => 'toggle_group',
										'title' => __('Nastavení Faktur', 'mwshop'),
										'setting' => [
											[
												'id' => 'automatic_invoice_disabled',
												'title' => __('Automatické vystavování faktur', 'mwshop'),
												'type' => 'switch',
												'label' => __('Nevystavovat a nezasílat faktury automaticky po zaplacení', 'mwshop'),
												'show_group' => 'paygate',
												'show_val' => 'mioweb',
											],
											[
												'id' => 'invoice_nums',
												'title' => __('Číselná řada faktur', 'mwshop'),
												'type' => 'number_series',
												'content' => [
													'prefix' => 'YYYY1',
													'characters' => 4,
													'start' => 1,
												],
												'show_group' => 'paygate',
												'show_val' => 'mioweb',
											],
											[
												'id' => MwsDocumentType::SimplifiedInvoice . '_nums',
												'title' => __('Číselná řada zjednodušených daňových dokladů', 'mwshop'),
												'type' => 'number_series',
												'content' => [
													'prefix' => 'YYYY2',
													'characters' => 4,
													'start' => 1,

												],
												'show_group' => 'paygate',
												'show_val' => 'mioweb',
											],
											[
												'id' => 'invoice_note',
												'name' => __('Poznámka na faktuře', 'mwshop'),
												'content' => __('Podnikatel zapsán v živnostenském rejstříku', 'mwshop'),
												'type' => 'invoice_note',
												'show_group' => 'paygate',
												'show_val' => 'mioweb',
											],
											[
												'id' => 'invoice_logo',
												'title' => __('Logo na faktuře', 'mwshop'),
												'type' => 'image',
												'show_group' => 'paygate',
												'show_val' => 'mioweb',
											],
											[
												'id' => 'invoice_contact',
												'title' => __('Zobrazení kontaktních informací na faktuře', 'mwshop'),
												'type' => 'multiple_checkbox',
												'options' => [
													['name' => __('Tisknout telefon klienta na faktuře', 'mwshop'), 'value' => 'show_phone'],
													['name' => __('Tisknout e-mail klienta na faktuře', 'mwshop'), 'value' => 'show_email'],
												],
											],
										],
									],
									[
										'id' => 'gdpr_setting',
										'type' => 'toggle_group',
										'title' => __('Obchodní podmínky a GDPR', 'mwshop'),
										'setting' => [
											[
												'id' => 'allow_terms',
												'name' => __('Souhlas s obchodními podmínkami', 'mwshop'),
												'type' => 'switch',
												'label' => __('Nevyžadovat souhlas s obchodními podmínkami', 'mwshop'),
												'show' => 'terms',
											],
											[
												'id' => 'terms',
												'name' => __('Stránka s obchodními podmínkami', 'mwshop'),
												'type' => 'selectpage',
												'add_button' => true,
												'edit_button' => true,
												'whisperer' => true,
												'desc' => __(
													'Vyberte stránku, která obsahuje vaše obchodní podmínky. ' .
													'Podmínky jsou zobrazeny zákazníkovi před vlastním objednáním. Objednáním vyjadřuje souhlas s obchodními podmínkami. ',
													'mwshop'
												),
												'target' => false,
												'show_group' => 'terms',
												'show_val' => '0',
											],
											[
												'id' => 'terms_main_text',
												'name' => __('Hlavní část obchodních podmínek', 'mwshop'),
												'tooltip' => __('Tento text se bude zobrazovat ve skrolovatelném boxu nad tlačítkem pro objednání v posledním kroku objednávky eshopu, v prodejním formuláři a ve formuláři pro objednání u tlačítka koupit.', 'mwshop'),
												'content' => '',
												'type' => 'terms_editor',
												'show_group' => 'terms',
												'show_val' => '0',
											],
											[
												'id' => 'gdpr_text',
												'name' => __('Text informační povinnosti', 'mwshop'),
												'content' => __('Odesláním objednávky nám dáváte souhlas se zpracováním osobních údajů za účelem zpracování Vaší objednávky.', 'mwshop'),
												'type' => 'textarea',
											],
											[
												'id' => 'gdpr_url_text',
												'name' => __('Text odkazu informační povinnosti', 'mwshop'),
												'content' => __('Zásady zpracování osobních údajů', 'mwshop'),
												'type' => 'text',
											],
											[
												'id' => 'gdpr_url',
												'name' => __('Stránka se zásadami zpracování osobních údajů', 'mwshop'),
												'type' => 'selectpage',
												'add_button' => true,
												'edit_button' => true,
												'whisperer' => true,
												'target' => false,
											],
										],
									],

								],
							],
						],
					],

				]);
				mwSetting()->addPageSetting('eshop_comparers', [
					[
						'type' => 'box',
						'setting' => [
							[
								'name' => __('Odkazy na XML feedy pro srovnávače cen', 'mwshop'),
								'id' => 'eshop_feeds',
								'type' => 'eshop_feeds',
							],
						],
					],
				]);
				mwSetting()->addPageSetting('eshop_actions', [
					[
						'type' => 'box',
						'setting' => [
							[
								'id' => 'actions_info',
								'type' => 'info',
								'content' => __('Zde si můžete nastavit akce, které se mají provádět automaticky při změně stavu objednávky vytvořené v eshopu. Akce pro jednotlivé produkty pak můžete nastavit přímo v editaci produktů v záložce Automatizace.', 'mwshop'),
							],
							[
								'id' => 'actions',
								'type' => 'eshop_automations',
							],
						],
					],
				]);
				mwSetting()->addPageSetting('eshop_emails', [
					[
						'type' => 'tabs',
						'id' => 'emails',
						'tabs' => [
							'emails' => [
								'name' => __('Systémové emaily', 'cms_ve'),
								'setting' => [
									[
										'id' => MwsEmailType::NewOrder,
										'type' => 'toggle_group',
										'title' => MwsEmailType::getCaption(MwsEmailType::NewOrder),
										'setting' => [
											[
												'id' => MwsEmailType::NewOrder,
												'type' => 'transaction_email',
												'attachment' => true,
												'content' => [
													'enabled' => $defaultGw->getId() === 'fapi' ? true : null,
													'subject' => __('Potvrzení objednávky %%CISLO_OBJEDNAVKY%%', 'mwshop'),
													'content' => __('Vážený zákazníku,

Vaši objednávku jsme v pořádku přijali. Kód objednávky: %%CISLO_OBJEDNAVKY%%

%%INFO_OBJEDNAVKY%%

Budeme vás informovat jakmile bude zboží připraveno k odeslání. V případě jakýkoli dotazů nás neváhejte kontaktovat.

Děkujeme za objednávku.
S přátelskými pozdravy,
%%NAZEV_WEBU%%', 'mwshop'),
												],
											],
										],
									],
									[
										'id' => MwsEmailType::PayedOrder,
										'type' => 'toggle_group',
										'title' => MwsEmailType::getCaption(MwsEmailType::PayedOrder),
										'class' => $defaultGw->getId() !== 'mioweb' ? 'cms_nodisp' : '',
										'setting' => [
											[
												'id' => MwsEmailType::PayedOrder,
												'type' => 'transaction_email',
												'content' => [
													'subject' => __('Přijali jsme platbu za objednávku %%CISLO_OBJEDNAVKY%%', 'mwshop'),
													'content' => __('Vážený zákazníku,

děkujeme za uhrazení vaší objednávky. Platbu jsme v pořádku přijali.

S přátelskými pozdravy,
%%NAZEV_WEBU%%', 'mwshop'),
												],
											],
										],
									],
									[
						'id' => MwsEmailType::OrderPaymentFailed,
						'type' => 'toggle_group',
						'title' => MwsEmailType::getCaption(MwsEmailType::OrderPaymentFailed),
//						'class' => $defaultGw->getId() !== 'mioweb' ? 'cms_nodisp' : '',
						'setting' => [
							[
								'id' => MwsEmailType::OrderPaymentFailed,
								'type' => 'transaction_email',
								'content' => [
									'subject' => __('Platba objednávky %%CISLO_OBJEDNAVKY%% se nezdařila', 'mwshop'),
									'content' => __('Vážený zákazníku,

Při pokusu o zaplacení objednávky %%CISLO_OBJEDNAVKY%% došlo k chybě.
Zopakujte prosím platbu zde: %%PLATBA_URL%%

nebo zaplaťte převodem na účet:

%%PREVOD_INFO%%

%%NAZEV_WEBU%%', 'mwshop'),
								],
							],
						],
									],
									[
										'id' => MwsEmailType::FinishedOrder,
										'type' => 'toggle_group',
										'title' => MwsEmailType::getCaption(MwsEmailType::FinishedOrder),
										'setting' => [
											[
												'id' => MwsEmailType::FinishedOrder,
												'type' => 'transaction_email',
												'content' => [
													'subject' => __('Objednávka %%CISLO_OBJEDNAVKY%% je na cestě k vám', 'mwshop'),
													'content' => __('Vážený zákazníku,

Vaši objednávku jsme předali dopravci a je na cestě k vám. O konkrétním času doručení vás bude informovat dopravce.

%%SLEDOVANI_ZASILKY%%

S přátelskými pozdravy,
%%NAZEV_WEBU%%', 'mwshop'),
												],
											],
										],
									],
									[
										'id' => MwsEmailType::OrderReadyToPickup,
										'type' => 'toggle_group',
										'title' => MwsEmailType::getCaption(MwsEmailType::OrderReadyToPickup),
										'setting' => [
											[
												'id' => MwsEmailType::OrderReadyToPickup,
												'type' => 'transaction_email',
												'content' => [
													'subject' => __('Zboží je připraveno k vyzvednutí', 'mwshop'),
													'content' => __('Vážený zákazníku,

vaše zboží objednávky č. %%CISLO_OBJEDNAVKY%% je připraveno k vyzvednutí.

S přátelskými pozdravy,
%%NAZEV_WEBU%%', 'mwshop'),
												],
											],
										],
									],
									[
										'id' => MwsEmailType::ElectronicDelivery,
										'type' => 'toggle_group',
										'title' => MwsEmailType::getCaption(MwsEmailType::ElectronicDelivery),
										'setting' => [
											[
												'id' => MwsEmailType::ElectronicDelivery,
												'type' => 'transaction_email',
												'content' => [
													'subject' => __('Doručení objednávky %%CISLO_OBJEDNAVKY%%', 'mwshop'),
													'content' => __('Vážený zákazníku,

zasíláme vám vámi objednaný elektronický produkt(y). Elektronický produkt naleznete v příloze.

S přátelskými pozdravy,
%%NAZEV_WEBU%%', 'mwshop'),
												],
											],
										],
									],
									[
										'id' => MwsEmailType::SentInvoice,
										'type' => 'toggle_group',
										'title' => MwsEmailType::getCaption(MwsEmailType::SentInvoice),
										'class' => $defaultGw->getId() !== 'mioweb' ? 'cms_nodisp' : '',
										'setting' => [
											[
												'id' => MwsEmailType::SentInvoice,
												'type' => 'transaction_email',
												'content' => [
													'subject' => __('Faktura k objednávce %%CISLO_OBJEDNAVKY%%', 'mwshop'),
													'content' => __('Vážený zákazníku,

zasíláme Vám fakturu k objednávce č. %%CISLO_OBJEDNAVKY%%. Fakturu naleznete v příloze.

S přátelskými pozdravy,
%%NAZEV_WEBU%%', 'mwshop'),
												],
											],
										],
									],
								],
							],
							'custom_emails' => [
								'name' => __('Vlastní emaily', 'mwshop'),
								'setting' => [
									[
										'id' => 'custom_emails',
										'type' => 'multielement',
										'sortable' => false,
										'keep_id' => true,
										'open' => 'under',
										'style' => 'shadow',
										'texts' => [
											'add' => __('Přidat vlastní email', 'mwshop'),
											'empty' => __('Nový email', 'mwshop'),
										],
										'setting' => [
											[
												'id' => 'name',
												'title' => __('Název emailu', 'mwshop'),
												'tooltip' => __('Název emailu slouží pro vaši orientaci mezi vlastními maily', 'mwshop'),
												'type' => 'text',
											],
											[
												'id' => 'email',
												'type' => 'transaction_email',
												'attachment' => true,

											],
										],
									],
								],
							],
							'appearance' => [
								'name' => __('Vzhled', 'mwshop'),
								'setting' => [
									[
										'type' => 'box',
										'setting' => [
											[
												'id' => 'appearance_type',
												'title' => __('Vzhled emailů', 'cms_ve'),
												'type' => 'select',
												'content' => 'text',
												'options' => [
													['name' => __('Textový', 'cms_ve'), 'value' => 'text'],
													['name' => __('Grafický', 'cms_ve'), 'value' => 'graphic'],
												],
												'show' => 'appearance_type',
											],
											[
												'name' => __('Barva hlavičky', 'mwshop'),
												'id' => 'header_color',
												'type' => 'color',
												'content' => '#158ebf',
												'show_group' => 'appearance_type',
												'show_val' => 'graphic',
											],
											[
												'name' => __('Logo v hlavičce emailu', 'mwshop'),
												'id' => 'logo',
												'type' => 'image',
												'show_group' => 'appearance_type',
												'show_val' => 'graphic',
											],
											[
												'name' => __('Šířka loga', 'mwshop'),
												'id' => 'logo_width',
												'type' => 'slider',
												'setting' => [
													'min' => '50',
													'max' => '500',
												],
												'content' => '150',
												'show_group' => 'appearance_type',
												'show_val' => 'graphic',
											],
										],
									],
								],
							],
						],
					],
				]);
				mwSetting()->addPageSetting('eshop_popups', [
					[
						'id' => 'classic_popup',
						'type' => 'toggle_group',
						'title' => __('Klasický pop-up', 'cms'),
						'open' => true,
						'setting' => [
							[
								'name' => __('Klasický pop-up', 'mwshop'),
								'id' => 'clasic_popup',
								'type' => 'popupselect',
								'tooltip' => __('Tento pop-up se zobrazí po načtení stránky nebo při splnění zadané podmínky v pokročilém nastavení.', 'mwshop'),
							],
							[
								'id' => 'popup_type',
								'name' => __('Zobrazit pop-up', 'mwshop'),
								'type' => 'radio',
								'show' => 'popup_type',
								'options' => [
									'onload' => __('Po načtení stránky', 'mwshop'),
									'advance' => __('Pokročilé nastavení', 'mwshop'),
								],
								'content' => 'onload',
							],
							[
								'name' => __('Zobrazit po x sekundách', 'mwshop'),
								'id' => 'time',
								'type' => 'text',
								'desc' => __('Pop-up se zobrazí po x sekundách od načtení stránky.', 'mwshop'),
								'show_group' => 'popup_type',
								'show_val' => 'advance',
							],
							[
								'name' => __('Zobrazit po odskrolování', 'mwshop'),
								'id' => 'scroll',
								'type' => 'size',
								'content' => [
									'size' => '',
									'unit' => 'px',
								],
								'desc' => __('Pop-up se zobrazí po odskrolování zadané části stránky (v % nebo v px).', 'mwshop'),
								'show_group' => 'popup_type',
								'show_val' => 'advance',
							],
							[
								'name' => __('Zobrazit po naskrolování na prvek s CSS selektorem', 'mwshop'),
								'id' => 'selector',
								'type' => 'text',
								'placeholder' => __('.class nebo #id', 'mwshop'),
								'desc' => __('Pop-up se zobrazí po naskrolování na prvek stránky se zadaným CSS selektorem.', 'mwshop'),
								'show_group' => 'popup_type',
								'show_val' => 'advance',
							],
						],
					],
					[
						'id' => 'classic_popup',
						'type' => 'toggle_group',
						'title' => __('Exit pop-up', 'cms'),
						'open' => true,
						'setting' => [
							[
								'name' => __('Exit pop-up', 'mwshop'),
								'id' => 'exit_popup',
								'type' => 'popupselect',
								'tooltip' => __('Tento pop-up se zobrazí v momentě, kdy uživatel vyjede myší do horní části prohlížeče.', 'mwshop'),
							],
						],
					],
				]);

				mwSetting()->addPageSetting('mw_eshop_codes', [
					[
						'id' => '',
						'type' => 'box',
						'title' => __('Konverzní kódy eshopu', 'mwshop'),
						'setting' => [
							[
								'type' => 'info',
								'content' => __('Zde zadané konverzní kódy se spustí na děkovacích stránkách po prodeji jak v eshopu, tak i skrz prodejní formuláře.', 'mwshop'),
							],
							[
								'id' => 'conversion_codes',
								'type' => 'code_list',
								'list_type' => 'conversion',
							],
						],
					],
					[
						'id' => '',
						'type' => 'box',
						'title' => __('Kódy eshopu', 'mwshop'),
						'setting' => [
							[
								'id' => 'codes',
								'type' => 'code_list',
							],
						],
					],
					[
						'id' => 'custom_css',
						'type' => 'toggle_group',
						'open' => true,
						'title' => __('CSS styly (platné pouze na stránkách eshopu)', 'mwshop'),
						'setting' => [
							[
								'id' => 'css',
								'type' => 'textarea',
								'rows' => 8,
								'desc' => __('Vložením vlastních CSS (kaskádových) stylů můžete ovlivnit vzhled e-shopu.', 'mwshop'),
							],
						],
					],
				]);
				mwSetting()->addPageSetting(MWS_OPTION_SHOP_APPEARANCE, [
					[
						'id' => 'basic_setting',
						'type' => 'toggle_group',
						'title' => __('Základní nastavení vzhledu', 'mwshop'),
						'open' => true,
						'setting' => [
							[
								'name' => __('Barva', 'mwshop'),
								'id' => 'eshop_color',
								'type' => 'color',
								'content' => '#158ebf',
								'desc' => __('Tato barva se použije pro obarvení základních prvků jako jsou tlačítka.', 'mwshop'),
							],
							[
								'name' => __('Barva pozadí eshopu', 'mwshop'),
								'id' => 'background_color',
								'type' => 'color',
								'content' => '#ffffff',
							],
							[
								'name' => __('Obrázek na pozadí eshopu', 'mwshop'),
								'id' => 'background_image',
								'type' => 'bgimage',
								'hide' => ['paralax'],
								'content' => [
									'pattern' => 0,
									'fixed' => 'fixed',
								],
							],
							[
								'id' => 'show_cart_header',
								'title' => __('Zobrazení košíku v hlavičce', 'mwshop'),
								'type' => 'multiple_checkbox',
								'options' => [
									['name' => __('Zobrazit košík v hlavičce webu', 'mwshop'), 'value' => 'show_web'],
									['name' => __('Zobrazit košík v hlavičce blogu', 'mwshop'), 'value' => 'show_blog'],
									['name' => __('Zobrazit košík v hlavičce členských sekcí', 'mwshop'), 'value' => 'show_member'],
								],
								'content' => ['show_web', 'show_blog'],
							],
						],
					],
					[
						'id' => 'basic_setting',
						'type' => 'toggle_group',
						'title' => __('Obecné zobrazení produktů', 'mwshop'),
						'setting' => [
							[
								'id' => 'product_thumbnail',
								'title' => __('Zobrazit produktové obrázky v poměru:', 'cms_ve'),
								'type' => 'select',
								'content' => '43',
								'options' => [
									['name' => __('Původní', 'cms_ve'), 'value' => 'original'],
									['name' => __('Široký (16:9)', 'cms_ve'), 'value' => '169'],
									['name' => __('Základní (3:2)', 'cms_ve'), 'value' => '32'],
									['name' => __('Střední (4:3)', 'cms_ve'), 'value' => '43'],
									['name' => __('Čtverec (1:1)', 'cms_ve'), 'value' => '11'],
									['name' => __('Základní na výšku (2:3)', 'cms_ve'), 'value' => '23'],
									['name' => __('Střední na výšku (3:4)', 'cms_ve'), 'value' => '34'],
								],
							],
							[
								'id' => 'eshop_display_product',
								'title' => __('Viditelnost nedostupných produktů', 'cms_ve'),
								'type' => 'multiple_checkbox',
								'options' => [
									['name' => __('Zobrazovat nedostupné produkty v seznamu produktů', 'mwshop'), 'value' => 'unavailable_product'],
									['name' => __('Zobrazovat nedostupné varianty ve výběru varianty', 'mwshop'), 'value' => 'unavailable_variant'],
								],
								'desc' => __('Týká se těch produktů a jejich variant, které mají "omezení prodeje" nastaveno na ' .
									'"Produkt nelze koupit" anebo byly vyčerpány jejich skladové zásoby bez možnosti nákupu i po vyčerpání skladu.'),
							],
						],
					],
					[
						'id' => 'productlist_setting',
						'type' => 'toggle_group',
						'title' => __('Výpis produktů', 'mwshop'),
						'setting' => [
							[
								'id' => 'product_order',
								'title' => __('Řadit zboží podle', 'mwshop'),
								'type' => 'select',
								'content' => 'date',
								'options' => [
									['name' => __('Data vytvoření', 'mwshop'), 'value' => 'date'],
									['name' => __('Názvu', 'mwshop'), 'value' => 'title'],
									['name' => __('Vlastního řazení', 'mwshop'), 'value' => 'menu_order'],
								],
								'desc' => __('Pořadí pro vlastní řazení se určuje podle hodnoty "Pořadí" v nastavení každého produktu.', 'mwshop'),
							],
							[
								'id' => 'product_style',
								'title' => __('Vzhled výpisu produktů', 'mwshop'),
								'type' => 'imageselect',
								'content' => 'pre1',
								'options' => [
									'pre1' => MWS_URL_BASE . '/img/image_select/product1.png',
									'pre3' => MWS_URL_BASE . '/img/image_select/product3.png',
									'pre2' => MWS_URL_BASE . '/img/image_select/product2.png',
									'pre4' => MWS_URL_BASE . '/img/image_select/product4.png',
								],
								'show' => 'p_style',
							],
							[
								'id' => 'font_title',
								'title' => __('Formátování názvu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'title',
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '50',
									'font_size_placeholder' => '20',
								],
							],
							[
								'id' => 'font_price',
								'title' => __('Formátování ceny', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'text',
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
									'font_size_placeholder' => '17',
								],
							],
							[
								'id' => 'cols',
								'title' => __('Počet sloupců', 'mwshop'),
								'type' => 'select',
								'content' => 3,
								'options' => [
									['name' => '3', 'value' => 3],
									['name' => '4', 'value' => 4],
									['name' => '5', 'value' => 5],
								],
								'show_group' => 'p_style',
								'show_val' => 'pre1,pre3,pre4',
							],
							[
								'name' => __('Počet produktů na stránku', 'mwshop'),
								'id' => 'per_page',
								'type' => 'text',
								'content' => '15',
							],
							[
								'id' => 'hide_desc',
								'title' => __('Viditelnost popisku', 'mwshop'),
								'type' => 'switch',
								'label' => __('Skrýt popisek', 'mwshop'),
								'show' => 'hide_desc',
							],
							[
								'name' => __('Maximální počet slov popisku ve výpisu', 'mwshop'),
								'id' => 'excerpt_length',
								'type' => 'text',
								'content' => '10',
								'show_group' => 'hide_desc',
								'show_val' => '0',
							],
							[
								'id' => 'hide_home_product_list',
								'title' => __('Výpis na úvodní stránce eshopu', 'mwshop'),
								'type' => 'switch',
								'label' => __('Skrýt výpis produktů na úvodní stránce', 'mwshop'),
							],
							[
								'id' => 'hide_categories',
								'title' => __('Kategorie', 'mwshop'),
								'type' => 'switch',
								'label' => __('Skrýt kategorie', 'mwshop'),
							],
							[
								'id' => 'hide_search',
								'title' => __('Vyhledávání', 'mwshop'),
								'type' => 'switch',
								'label' => __('Skrýt vyhledávání', 'mwshop'),
							],
						],
					],
					[
						'id' => 'detail_setting',
						'type' => 'toggle_group',
						'title' => __('Detailu produktu', 'mwshop'),
						'setting' => [
							[
								'id' => 'hide_comments',
								'title' => __('Komentáře', 'cms_ve'),
								'type' => 'switch',
								'label' => __('Skrýt diskuze u produktů', 'mwshop'),
							],
							[
								'id' => 'hide_similiar_products',
								'title' => __('Podobné produkty', 'cms_ve'),
								'type' => 'switch',
								'label' => __('Skrýt podobné produkty', 'mwshop'),
							],
							[
								'id' => 'hide_social',
								'title' => __('Sociální tlačítka', 'cms_ve'),
								'type' => 'switch',
								'label' => __('Skrýt sociální tlačítka v detailu produktu', 'mwshop'),
							],
							[
								'id' => 'hide_availability',
								'title' => __('Dostupnost', 'cms_ve'),
								'type' => 'switch',
								'label' => __('Skrýt dostupnost u produktů', 'mwshop'),
							],
							[
								'id' => 'show_product_count',
								'title' => __('Možnost zadání počtu kusů', 'cms_ve'),
								'type' => 'switch',
								'label' => __('V detailu produktu zobrazit pole pro nastavení počtu kusů při vkládání do košíku.', 'mwshop'),
							],
						],
					],
					[
						'id' => 'cart_content',
						'type' => 'toggle_group',
						'title' => __('Obsah košíku', 'mwshop'),
						'setting' => [
							[
								'id' => 'cart_content',
								'title' => __('Obsah pod výpisem zboží v košíku', 'cms_ve'),
								'type' => 'weditor',
								'setting' => [
									'post_type' => 'weditor',
									'texts' => [
										'empty' => __(' - Bez obsahu - ', 'cms_ve'),
										'edit' => __('Upravit vybraný obsah', 'cms_ve'),
										'duplicate' => __('Duplikovat vybraný obsah', 'cms_ve'),
										'create' => __('Vytvořit nový obsah', 'cms_ve'),
										'delete' => __('Smazat vybraný obsah', 'cms_ve'),
									],
								],
							],
						],
					],
					[
						'id' => 'thx_content',
						'type' => 'toggle_group',
						'title' => __('Obsah děkovací stránky', 'mwshop'),
						'setting' => [
							[
								'id' => 'thanks_content',
								'title' => __('Obsah na děkovací stránce objednávky', 'cms_ve'),
								'type' => 'weditor',
								'setting' => [
									'post_type' => 'weditor',
									'texts' => [
										'empty' => __(' - Bez obsahu - ', 'cms_ve'),
										'edit' => __('Upravit vybraný obsah', 'cms_ve'),
										'duplicate' => __('Duplikovat vybraný obsah', 'cms_ve'),
										'create' => __('Vytvořit nový obsah', 'cms_ve'),
										'delete' => __('Smazat vybraný obsah', 'cms_ve'),
									],
								],
							],
						],
					],
				]);
				mwSetting()->addPageSetting('eshop_footer', [
					[
						'id' => 'show',
						'class' => 'hide_in_toswitch_container',
						'type' => 'switch',
						'show' => 'footerset',
						'label' => __('Použít pro eshop vlastní patičku', 'mwshop'),
					],
					[
						'id' => 'footer_info',
						'type' => 'info',
						'content' => __('Defaultně se pro eshop používá patička webu. Pokud chcete pro eshop vytvořit speciální patičku, zaškrtněte volbu "Použít pro eshop vlastní patičku".', 'mwshop'),
						'show_group' => 'footerset',
						'show_val' => '0',
					],
					[
						'id' => 'footer_group',
						'type' => 'group',
						'setting' => MW()->container['footer_setting'],
						'show_group' => 'footerset',
						'show_val' => '1',
					],

				]);
				mwSetting()->addPageSetting('eshop_header', [
					[
						'label' => __('Použít pro eshop vlastní hlavičku', 'mwshop'),
						'id' => 'show',
						'class' => 'hide_in_toswitch_container',
						'type' => 'switch',
						'show' => 'headerset',
					],
					[
						'id' => 'header_info',
						'type' => 'info',
						'content' => __('Defaultně se pro eshop používá hlavička webu. Pokud chcete pro eshop vytvořit speciální hlavičku, zaškrtněte volbu "Použít pro eshop vlastní hlavičku".', 'mwshop'),
						'show_group' => 'headerset',
						'show_val' => '0',
					],
					[
						'id' => 'header_group',
						'type' => 'group',
						'setting' => MW()->container['header_setting'],
						'show_group' => 'headerset',
						'show_val' => '1',
					],
				]);

		self::$isInitialized = true;
	}

	public static function initObjects()
	{
				// Discount code settings
				//***********************************************************************************

				mwSetting()->addObjectSetting([
					'id' => 'mws_sale_form',
					'title' => __('Prodej', 'mwshop'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'type' => 'item_set',
									'object_id' => MWS_FORM_SLUG,
									'fields' => [
										'post_title' => [
											'label' => __('Název formuláře', 'mwshop'),
											'slug' => false,
										],
									],
								],
								[
									'id' => 'product',
									'title' => __('Produkt', 'mwshop'),
									'type' => 'product_select',
									'hide_variants' => true,
									'add_button' => true,
									'edit_button' => true,
									'whisperer' => true,
								],
								[
									'id' => 'thx_page',
									'title' => __('Děkovací stránka', 'mwshop'),
									'name' => __('Děkovací stránka', 'mwshop'),
									'type' => 'selectpage',
									'add_button' => true,
									'edit_button' => true,
									'whisperer' => true,
									'required' => (!function_exists('MWS') || !MWS()->isCreated()),
								],
							],
						],
						[
							'id' => 'sell_miniupsell',
							'type' => 'toggle_group',
							'checkbox' => 1,
							'content' => 0,
							'title' => __('Nabízet miniupsell', 'mwshop'),
							'setting' => [
								[
									'id' => 'miniupsell',
									'title' => __('Miniupsell', 'mwshop'),
									'type' => 'product_select',
									'hide_variants' => true,
									'add_button' => true,
									'edit_button' => true,
									'whisperer' => true,
								],
								[
									'name' => __('Nadpis miniupsellu', 'mwshop'),
									'id' => 'miniupsell_title',
									'type' => 'text',
									'content' => __('Ano, chci přidat do objednávky tento miniupsell produkt', 'mwshop'),
								],
								[
									'name' => __('Popis miniupsellu', 'mwshop'),
									'id' => 'miniupsell_description',
									'type' => 'editor',
								],
							],
						],
						[
							'id' => 'own_payment_shipping',
							'type' => 'toggle_group',
							'checkbox' => 1,
							'content' => 0,
							'title' => __('Vlastní doprava a platba', 'mwshop'),
							'setting' => [
								[
									'name' => __('Způsoby platby', 'mwshop'),
									'id' => 'payments',
									'type' => 'item_multi_select',
									'object_id' => MWS_PAYMENT_METHOD_SLUG,
									'only_published' => false,
								],
								[
									'name' => __('Způsoby dopravy', 'mwshop'),
									'id' => 'shippings',
									'type' => 'item_multi_select',
									'object_id' => MWS_SHIPPING_SLUG,
									'only_published' => false,
								],
							],
						],
						[
							'type' => 'toggle_group',
							'open' => true,
							'title' => __('Slevové kódy', 'mwshop'),
							'setting' => [
								[
									'id' => 'allow_discount_codes',
									'type' => 'switch',
									'label' => __('Umožnit ve formuláři uplatnit slevové kódy', 'mwshop'),
								],
							],
						],
					],
				], [MWS_FORM_SLUG]);

				mwSetting()->addObjectSetting([
					'id' => 'mws_sale_form_upsell',
					'title' => __('Upselly', 'mwshop'),
					'fields' => [
						[
							'id' => 'upsells',
							'type' => 'upsells',
						],
					],
				], [MWS_FORM_SLUG]);

				mwSetting()->addObjectSetting([
					'id' => 'mws_sale_form_visibility',
					'title' => __('Zobrazení', 'mwshop'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'name' => __('Umožnit zadat množství', 'mwshop'),
									'id' => 'show_product_count',
									'type' => 'switch',
									'label' => __('Zobrazit políčko pro zadání množství', 'mwshop'),
								],
							],
						],
						[
							'type' => 'toggle_group',
							'open' => true,
							'title' => __('Pole formuláře', 'mwshop'),
							'setting' => [
								[
									'name' => __('Zjednodušený formulář', 'mwshop'),
									'id' => 'allow_simply_form',
									'type' => 'switch',
									'label' => __('Pokud je to možné zobrazit zjednodušený formulář a vystavovat zjednodušený daňový doklad', 'mwshop'),
									'tooltip' => __('V prodejním formuláři nebude klient zadávat fakturační údaje. Na faktuře nebude uveden odběratel. Aby se zobrazil zjednodušený prodejní formulář, nesmí formulář obsahovat produkt vyžadující dopravu a maximální cena objednávky nemůže přesáhnout 10 000 Kč.', 'mwshop'),
									'show' => 'simply_form',
									'hide_field' => MWS()->getEshopCountry() !== MwsCountry::CZ,
								],
								[
									'name' => __('Jméno', 'mwshop'),
									'id' => 'show_field_name',
									'type' => 'switch',
									'label' => __('Zobrazit jméno a nastavit ho jako povinné', 'mwshop'),
									'show_group' => 'simply_form',
									'show_val' => 1,
									'hide_field' => MWS()->getEshopCountry() !== MwsCountry::CZ,
								],
								[
									'name' => __('Telefon', 'mwshop'),
									'id' => 'show_field_phone',
									'type' => 'status_switch',
									'content' => '1',
									'label' => __('Zobrazit telefon a nastavit ho jako povinný', 'mwshop'),
								],
								[
									'name' => __('Výběr země', 'mwshop'),
									'id' => 'show_field_country',
									'type' => 'status_switch',
									'content' => '1',
									'label' => __('Umožnit výběr země u fakturační a doručovací adresy', 'mwshop'),
								],
								[
									'name' => __('Poznámka', 'mwshop'),
									'id' => 'show_field_note',
									'type' => 'status_switch',
									'content' => '1',
									'label' => __('Zobrazit poznámku', 'mwshop'),
								],
							],
						],
					],
				], [MWS_FORM_SLUG]);

				mwSetting()->addObjectSetting([
					'id' => 'mws_sale_form_automation',
					'title' => __('Automatizace', 'mwshop'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'id' => 'actions_info',
									'type' => 'info',
									'content' => __('Zde si můžete nastavit akce, které se mají provádět automaticky při změně stavu objednávky vytvořené tímto prodejním formulářem. Akce pro jednotlivé produkty pak můžete nastavit přímo v editaci produktů v záložce Automatizace.', 'mwshop'),
								],
								[
									'id' => 'actions',
									'type' => 'eshop_automations',
								],
							],
						],
					],
				], [MWS_FORM_SLUG]);

				// Discount code settings
				//***********************************************************************************

				mwSetting()->addObjectSetting([
					'id' => 'discount_code',
					'title' => __('Slevové kódy', 'mwshop'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'name' => __('Slevový kód', 'mwshop'),
									'desc' => __('Slevový kód může obsahovat pouze číslice, pomlčku a znaky bez diakritiky. Maximální délka je 30 znaků.', 'mwshop'),
									'id' => 'code',
									'maxlength' => 30,
									'required' => 1,
									'type' => 'text',
								],
								[
									'name' => __('Výše slevy', 'mwshop'),
									'id' => 'value',
									'content' => '50',
									'type' => 'number',
								],
								[
									'id' => 'type',
									'type' => 'select',
									'name' => __('Typ slevy', 'mwshop'),
									'options' => [
										['value' => MwsDiscountCodeType::Fixed, 'name' => MWS()->getDefaultCurrency() . ' (pevná částka)'],
										['value' => MwsDiscountCodeType::Percent, 'name' => '% (procentuální)'],
									],
								],
							],
						],
						[
							'id' => 'basic_setting',
							'type' => 'toggle_group',
							'open' => true,
							'title' => __('Omezení částkou', 'mwshop'),
							'setting' => [
								[
									'name' => __('Slevu lze uplatnit když objednávka přesáhne', 'mwshop'),
									'id' => 'min_price',
									'type' => 'number',
									'unit' => MWS()->getDefaultCurrency(),
									'content' => '500',
								],
							],
						],
						[
							'id' => 'validity_setting',
							'type' => 'toggle_group',
							'open' => true,
							'title' => __('Platnost slevového kódu', 'mwshop'),
							'setting' => [
								[
									'id' => 'expiration_type',
									'type' => 'select',
									'name' => __('Omezení platnosti', 'mwshop'),
									'tooltip' => __('Můžete omezit platnost slevového kódu určitým počtem použití, případně že má platit jen v zadaném časovém intervalu.', 'mwshop'),
									'options' => [
										['value' => MwsDiscountCodeExpirationType::None, 'name' => __('Neomezeně', 'mwshop')],
										['value' => MwsDiscountCodeExpirationType::Count, 'name' => __('Omezení počtem použití', 'mwshop')],
										['value' => MwsDiscountCodeExpirationType::DateRange, 'name' => __('Omezení časem', 'mwshop')],
									],
									'show' => 'show_expiration_type',
								],
								[
									'name' => __('Omezení počtem použití', 'mwshop'),
									'id' => 'max_count',
									'type' => 'number',
									'show_group' => 'show_expiration_type',
									'show_val' => MwsDiscountCodeExpirationType::Count,
								],
								[
									'id' => 'expiration_from',
									'type' => 'date',
									'name' => __('Začátek platnosti', 'mwshop'),
									'show_group' => 'show_expiration_type',
									'show_val' => MwsDiscountCodeExpirationType::DateRange,
								],
								[
									'id' => 'expiration_to',
									'type' => 'date',
									'name' => __('Konec platnosti', 'mwshop'),
									'show_group' => 'show_expiration_type',
									'show_val' => MwsDiscountCodeExpirationType::DateRange,
								],
							],
						],
					],
				], [MWS_DISCOUNT_CODE_SLUG]);


				// Shipping setting
				//***********************************************************************************

				mwSetting()->addObjectSetting([
					'id' => 'shipping',
					'title' => __('Nastavení doručování', 'cms_ve'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'type' => 'item_set',
									'object_id' => MWS_SHIPPING_SLUG,
									'fields' => [
										'post_title' => [
											'label' => __('Název doručení', 'mwshop'),
											'slug' => false,
										],
									],
								],
								[
									'id' => 'type',
									'type' => 'shippings_type_select',
									'name' => __('Typ dopravy', 'mwshop'),
									'show' => 'shipping_type',
									'content' => MwsShippingType::Custom,
								],
								[
									'name' => __('Odkaz pro sledování zásilky', 'mwshop'),
									'id' => 'tracking_url',
									'type' => 'text',
									'show_group' => 'shipping_type',
									'show_val' => MwsShippingType::Custom,
									'desc' => __('Zadejte adresu pro sledování zásilky daného dopravce. Místo čísla zásilky zadejte proměnnou {CISLO_ZASILKY}.
									Tato proměnná bude potom nahrazena konkrétním číslem zásilky objednávky.', 'mwshop'),
								],
								[
									'id' => 'carrier',
									'type' => 'packeta_carriers_select',
									'name' => __('Externí dopravce', 'mwshop'),
									'show_group' => 'shipping_type',
									'show_val' => MwsShippingType::PacketaCarriers,
								],
								[
									'name' => __('Země', 'mwshop'),
									'id' => 'country',
									'type' => 'country_select',
									'only_shipping_countries' => true,
									'allow_all' => true,
								],
								[
									'name' => __('Cena (včetně DPH)', 'mwshop'),
									'id' => 'price',
									'type' => 'number',
									'data_type' => 'float',
									'step' => MwsCurrencyEnum::getHtmlInputStepAttribute(),
									'unit' => MWS()->getDefaultCurrency(),
									'show_group' => 'use_weight_prices',
									'show_val' => 0,
								],
								[
									'name' => __('DPH', 'mwshop'),
									'id' => 'vat_id',
									'type' => 'vat_select',
								],
								[
									'name' => __('Popis', 'mwshop'),
									'id' => 'post_excerpt',
									'type' => 'textarea',
									'save' => 'post',
								],
							],
						],
						[
							'id' => 'use_weight_prices',
							'type' => 'toggle_group',
							'title' => __('Určit cenu doručení podle váhy', 'mwshop'),
							'checkbox' => 1,
							'action' => 'reload',
							'show' => 'use_weight_prices',
							'setting' => [
								[
									'id' => 'weight_prices',
									'type' => 'interval_table',
									'fields' => [
										'max_name' => __('Hmotnost zásilky', 'mwshop'),
										'max_unit' => 'kg',
										'decimals' => 3,
										'int_name' => __('Rozmezí hmotnosti', 'mwshop'),
										'int_val_name' => __('Cena doručení', 'mwshop'),
										'int_val_unit' => 'Kč',
									],
									'content' => [
										[
											'max_val' => null,
											'int_val' => 199,
										],
										[
											'max_val' => 2.000,
											'int_val' => 99,
										],

									],
								],
							],
						],
						[
							'id' => 'basic_setting',
							'type' => 'toggle_group',
							'open' => true,
							'title' => __('Další nastavení', 'mwshop'),
							'setting' => [
								[
									'name' => __('Zdarma od ceny (včetně DPH)', 'mwshop'),
									'id' => 'free_from',
									'type' => 'number',
									'data_type' => 'float',
									'step' => MwsCurrencyEnum::getHtmlInputStepAttribute(),
									'unit' => MWS()->getDefaultCurrency(),
									'desc' => __('Pokud cena košíku přesáhne tuto částku, bude se tento druh dopravy nabízet zdarma.', 'mwshop'),
								],
								[
									'label' => __('Umožňuje zaplatit při převzetí', 'mwshop'),
									'id' => 'cod_enabled',
									'type' => 'switch',
									'show' => 'cod_detail',
									'desc' => __('Platbu lze provést až při převzetí zboží. Při využití platby při převzetí bude cena dopravy navýšena o nastavitelnou výši příplatku.', 'mwshop'),
								],
								[
									'name' => __('Příplatek za dobírku (včetně DPH)', 'mwshop'),
									'id' => 'cod_price',
									'type' => 'number',
									'data_type' => 'float',
									'step' => MwsCurrencyEnum::getHtmlInputStepAttribute(),
									'unit' => MWS()->getDefaultCurrency(),
									'show_group' => 'cod_detail',
									'show_val' => '1',
									'placeholder' => '0',
								],
							],
						],
						/*
						array(
							'id' => 'personal_setting',
							'type' => 'toggle_group',
							'open' => true,
							'title' => __('Pobočky osobního vyzvednutí', 'mwshop'),
							'show_group' => 'shipping_type',
							'show_val' => MwsShippingType::Personal,
							'setting' => array(
									array(
											'name' => __('Seznam míst pro osobní vyzvednutí', 'mwshop'),
											'id' => 'values',
											'type' => 'multielement',
											'open'=>'under',
											'texts'=>array(
												'add'=>__('Přidat místo','mwshop'),
												'empty'=>__('Místo','cms'),
											),
											'setting' => array(
													array(
														'id'=>'address',
														'title'=>__('Adresa','mwshop'),
														'type'=>'text',
													),
											),
									),
							),
						),*/
					],
				], [MWS_SHIPPING_SLUG]);

				// Payment setting
				//***********************************************************************************

				mwSetting()->addObjectSetting([
					'id' => 'payment_method',
					'title' => __('Nastavení způsobu platby', 'cms_ve'),
					'fields' => [
						[
							'id' => 'basic_setting',
							'type' => 'toggle_group',
							'open' => true,
							'title' => __('Základní nastavení', 'mwshop'),
							'setting' => [
								[
									'type' => 'item_set',
									'object_id' => MWS_PAYMENT_METHOD_SLUG,
									'fields' => [
										'post_title' => [
											'label' => __('Název způsobu platby', 'mwshop'),
											'slug' => false,
										],
									],
								],
								[
									'id' => 'type',
									'type' => 'payment_method_type_select',
									'name' => __('Typ platby', 'mwshop'),
								],
								[
									'name' => __('Popisek způsobu platby', 'mwshop'),
									'id' => 'post_excerpt',
									'type' => 'textarea',
									'save' => 'post',
								],
							],
						],
					],
				], [MWS_PAYMENT_METHOD_SLUG]);

				// Product setting
				//***********************************************************************************

				$productSetting = [
					[
						'type' => 'item_set',
						'object_id' => MWS_PRODUCT_SLUG,
						'fields' => [
							'post_title' => [
								'label' => __('Název produktu', 'mwshop'),
								'slug' => MWS()->isCreated(),
							],
							'post_excerpt' => [
								'label' => __('Krátký popis', 'mwshop'),
							],
						],
					],
					[
						'id' => 'type',
						'type' => 'select_product_type',
						'name' => __('Typ produktu', 'mwshop'),
						'content' => MwsProductType::Physical, // default value
						'show' => 'product_type',
					],
					[
						'id' => 'electronic_product_file',
						'type' => 'upload_file',
						'name' => __('Elektronický soubor', 'mwshop'),
						'tooltip' => sprintf(
								__('Zadaný soubor bude automaticky poslán klientovi po zaplacení objednávky.
								Pokud chcete produkt doručit jinou cestou, nic nezadávejte.
								Maximální velikost souboru je %d MB.', 'mwshop'),
								Order::MAXIMUM_FILE_SIZE_MB
						),
						'show_group' => 'product_type',
						'show_val' => MwsProductType::ElectronicPublication . ',' . MwsProductType::ElectronicService,
						'max_file_size_bytes' => Order::MAXIMUM_FILE_SIZE_MB * pow(2, 20),
					],
				];

				if (MW()->is_module_active('member')) {
					$productSetting[] = [
						'id' => 'membership_setting',
						'type' => 'membership_creator',
						'name' => __('Vytvořit členství v členské sekci', 'mwshop'),
						'tooltip' => __('Po zaplacení bude zákazníkovi automaticky vygenerován přístup do zadané členské sekce.', 'mwshop'),
						'show_group' => 'product_type',
						'show_val' => MwsProductType::Membership,
					];
				}

				$fastProductSetting = $productSetting;
				/*
				if(MWS()->isCreated())
				{
					$productSetting[] = [
						'id' => MWS_PRODUCT_META_KEY_STRUCTURE,
						'type' => 'select',
						'name' => __('Varianty produktu', 'mwshop'),
						'options' => [
							['value' => MwsProductStructureType::Single,
								'name' => __('Bez variant', 'mwshop')],
							['value' => MwsProductStructureType::Variants,
								'name' => __('Produkt má více variant', 'mwshop')],
						],
						'content' => MwsProductStructureType::Single, //default value

						'show' => 'structure_type',
						'save' => 'post_meta',
					];
				}*/

				//********* SINGLE PRODUCT SETTINGS
				// Price
				$productSetting[] = $fastProductSetting[] = [
					'name' => (MWS()->getVATs()->isUsingVatAccounting()
						? __('Cena (včetně DPH)', 'mwshop')
						: __('Cena', 'mwshop')
					),
					'id' => 'price',
					'type' => 'size',
					'content' => '100',
					'unit' => MWS()->getDefaultCurrency(),
					'data_type' => 'float',
					'show_group' => 'structure_type',
					'show_val' => '0',
				];
				$productSetting[] = $fastProductSetting[] = [
					'name' => __('DPH', 'mwshop'),
					'id' => 'vat_id',
					'type' => 'vat_select',
					'class' => (MWS()->getVATs()->isUsingVatAccounting() ? '' : 'cms_nodisp'),
				];

				$productFields = [
					[
						'id' => '',
						'type' => 'box',
						'setting' => $productSetting,
					],
				];

				// Sale price
				$productFields[] = [
					'id' => 'price_sale_enabled',
					'type' => 'toggle_group',
					'checkbox' => true,
					'title' => __('Sleva / akční cena'),
					'show_group' => 'structure_type',
					'show_val' => '0',
					'setting' => [
						[
							'name' => (MWS()->getVATs()->isUsingVatAccounting()
								? __('Cena po slevě (včetně DPH)', 'mwshop')
								: __('Cena po slevě', 'mwshop')
							),
							'id' => 'price_sale',
							'type' => 'size',
							'data_type' => 'float',
							'unit' => MWS()->getDefaultCurrency(),
							'tooltip' => __('Zadáním hodnoty "Cena po slevě" zapnete u produktu zobrazování se slevou. ' .
								'Zákazníkům se bude produkt nabízet za cenu po slevě. Výše slevy bude zobrazena procentuelně v nabídce. ' .
								'Pro cenu ZDARMA zadejte nulu.', 'mwshop'),
						],
						[
							'id' => 'price_sale_type',
							'type' => 'select',
						//                      'name' => __('Platnost slevy', 'mwshop'),
							'options' => [
						//                          array('value' => MwsSellRestriction::None, 'name' => __('prodej povolen')),
								['value' => MwsSalePriceType::Continuous, 'name' => __('trvalá (sleva je aktivní až do svého vypnutí)')],
								['value' => MwsSalePriceType::EnabledFrom, 'name' => __('budoucí sleva (sleva je aktivní od určitého okamžiku)')],
								['value' => MwsSalePriceType::EnabledTill, 'name' => __('končící sleva (sleva je aktivní do určitého okamžiku)')],
								['value' => MwsSalePriceType::EnabledInterval, 'name' => __('v intervalu (sleva je aktivní ve vymezeném období)')],
							],
							'show' => 'show_price_sell_type',
						],
						[
							'id' => 'price_sale_enabled_from',
							'type' => 'datetime',
							'name' => __('Slevu aktivovat v termínu', 'mwshop'),
							'show_group' => 'show_price_sell_type',
							'show_val' => MwsSalePriceType::EnabledFrom . ',' . MwsSalePriceType::EnabledInterval,
						],
						[
							'id' => 'price_sale_enabled_till',
							'type' => 'datetime',
							'name' => 'Slevu deaktivovat v termínu',
							'show_group' => 'show_price_sell_type',
							'show_val' => MwsSalePriceType::EnabledTill . ',' . MwsSalePriceType::EnabledInterval,
						],
						[
							'id' => 'price_sale_enabled_till_show',
							'checkbox' => false,
							'type' => 'switch',
							'label' => __('Zobrazit v detailu produktu odpočet, kolik času zbývá do konce slevy', 'mwshop'),
							'show_group' => 'show_price_sell_type',
							'show_val' => MwsSalePriceType::EnabledTill . ',' . MwsSalePriceType::EnabledInterval,
						],
					],
				];

				if (MWS()->isCreated()) {
					//*********** VARIANT PRODUCT SETTINGS

					$productFields[] = [
						'id' => MWS_PRODUCT_META_KEY_STRUCTURE,
						'type' => 'toggle_group',
						'status_switch' => true,
						'true_val' => MwsProductStructureType::Variants,
						'false_val' => MwsProductStructureType::Single,
						'title' => __('Produkt má více variant', 'mwshop'),
						'show' => 'structure_type',
						'save' => 'post_meta',
						'class' => 'mws_product_variant_setting',
						'setting' => [
							[
								'id' => MWS_PRODUCT_META_KEY_VARIANTLIST,
								'name' => '',
								'type' => 'variantList',
								'save' => 'post_meta',
							],
						],
						'show_group' => 'product_type',
						'show_val' => MwsProductType::Physical . ',' . MwsProductType::PrintedPublication . ',' . MwsProductType::Service . ',' . MwsProductType::LiveEvent . ',' . MwsProductType::LiveEventForeign,
					];
				}

				// Sale price
				$productFields[] = [
					'id' => '',
					'type' => 'toggle_group',
					'title' => __('Ostatní informace'),
					'setting' => [
						[
							'name' => __('Hmotnost', 'mwshop'),
							'id' => 'weight',
							'type' => 'size',
							'unit' => 'kg',
							'data_type' => 'float',
							'tooltip' => __('Tento údaj je používán k určení váhy zásilky dopravce Zásilkovna a pro určení ceny dopravy podle váhy objednávky.', 'mwshop'),
							'show_group' => 'product_type',
							'show_val' => implode(',', MwsProductType::getPhysical()),
						],
						[
							'name' => __('Výrobce', 'mwshop'),
							'id' => 'brand',
							'type' => 'text',
							'tooltip' => __('Tento údaj používají srovnávače cen. V rámci Google se jedná o tag "brand", u Heureky a Zboží je to tag "manufacturer"', 'mwshop'),
						],
						[
							'name' => __('Uplatnění slevových kódů', 'mwshop'),
							'id' => 'disabled_discount',
							'label' => __('Neuplatňovat na tento produkt slevové kódy', 'mwshop'),
							'type' => 'switch',
						],
					],
				];

				// Stock management group
				/*
				$productFields[] = [
					'id' => 'stock_enabled',
					'type' => 'toggle_group',
					'checkbox' => true,
					'title' => __('Sledovat sklad', 'mwshop'),
					'class' => 'mws_stock_toggle',
					'setting' => [
						[
							'id' => MWS_OPTION_STOCKCOUNT,
							'save' => 'post_meta',
							'savehook' => function ($postId, $field, $fieldValue, &$fieldSaved) {
								$product = MwsProduct::getOneById($postId, true);
								if (!$product) {
									return;
								}
								if ($_REQUEST['product']['stock_enabled'] ?? false) {
									$fieldSaved = $product->updateStockCount((int)$fieldValue, MwsStockUpdate::Set, true);
								}
							},
							'name' => __('Položek skladem', 'mwshop'),
							'type' => 'number',
							'step' => 1,
							'placeholder' => 0,
							'show_group' => 'structure_type',
							'show_val' => '0',
						],
						[
							'id' => 'stock_allow_backorders',
							'label' => __('Ponechat v nabídce i po vyprodání skladu', 'mwshop'),
							'type' => 'switch',
						],
					],
				]; */
				// Selling restrictions
				$productFields[] = [
					'id' => 'selling_restrict',
					'type' => 'toggle_group',
					'checkbox' => true,
					'title' => __('Omezit prodej'),
				//              'show_group' => 'structure_type',
				//              'show_val' => MwsProductStructureType::Single,
					'setting' => [
						[
				//                      'name' => __('Omezení prodeje', 'mwhop'),
							'id' => 'selling_restrict_type',
							'type' => 'select',
							'options' => [
				//                          array('value' => MwsSellRestriction::None, 'name' => __('prodej povolen')),
								['value' => MwsSellRestriction::FullDisable, 'name' => __('Produkt nelze koupit')],
								['value' => MwsSellRestriction::EnabledFrom, 'name' => __('Produkt lze koupit až od určeného data')],
								['value' => MwsSellRestriction::EnabledTill, 'name' => __('Produkt lze koupit do určitého data')],
								['value' => MwsSellRestriction::EnabledInterval, 'name' => __('Produkt lze koupit v období od do')],
							],
							'show' => 'show_selling_restrict_type',
						],
						[
							'id' => 'selling_enabled_from',
							'type' => 'datetime',
							'name' => __('Prodej zahájit v termínu', 'mwshop'),
							'show_group' => 'show_selling_restrict_type',
							'show_val' => MwsSellRestriction::EnabledFrom . ',' . MwsSellRestriction::EnabledInterval,
						],
						[
							'id' => 'selling_enabled_till',
							'type' => 'datetime',
							'name' => 'Prodej ukončit v termínu',
							'show_group' => 'show_selling_restrict_type',
							'show_val' => MwsSellRestriction::EnabledTill . ',' . MwsSellRestriction::EnabledInterval,
						],
					],
				];

				$productFields[] = [
					'id' => 'codes_setting',
					'type' => 'toggle_group',
					'show_group' => 'structure_type',
					'show_val' => '0',
					'title' => __('Kódy produktu', 'mwshop'),
					'setting' => [
						[
							'id' => 'codes',
							'name' => '',
							'type' => 'product_codes',
						],
					],
				];

				if (MWS()->isCreated()) {
					// ************* Product detail settings
					$productFields[] = [
						'id' => 'selling_restrict',
						'type' => 'toggle_group',
						'title' => __('Detail produktu'),
						'setting' => [
							[
								'name' => __('Dlouhý popis', 'mwshop'),
								'id' => '',
								'type' => 'info',
								'content' => __('Obsáhlejší popis produktu zobrazovaný v detailu produktu na záložce "Popis" upravíte přímo na stránce produktu pomocí vizuálního editoru.', 'mwshop'),
							],
							[
								'name' => __('Vlastní detail', 'mwshop'),
								'label' => __('Použít jako detail vlastní stránku', 'mwshop'),
								'id' => 'custom_detail',
								'type' => 'switch',
								'show' => 'custom_detail',
							],
							[
								'name' => __('Stránka', 'mwshop'),
								'id' => 'detail_page',
								'type' => 'selectpage',
								'show_group' => 'custom_detail',
								'show_val' => '1',
								'desc' => __('Vybraná stránka se bude zobrazovat jako detail tohoto produktu.', 'mwshop'),
							],
						],
					];
					// Properties group
					$productFields[] = [
						'id' => 'properties_setting',
						'type' => 'toggle_group',
						'title' => __('Parametry produktu', 'mwshop'),
						'setting' => [
							[
								'name' => '',
								'id' => 'properties',
								'type' => 'product_properties',
							],
						],
					];
					// Similar products
					$productFields[] = [
						'id' => 'show_similar_products',
						'type' => 'toggle_group',
						'checkbox' => 0,
						'title' => __('Podobné zboží', 'mwshop'),
						'setting' => [
							[
								'id' => 'show_type_similar_products',
								'title' => '',
								'type' => 'select',
								'options' => [
									['name' => __('Vytvořit vlastní výběr', 'mwshop'), 'value' => 'custom'],
									['name' => __('Vypisovat zboží ze stejné kategorie', 'mwshop'), 'value' => 'category'],
								],
								'content' => 'custom',
								'show' => 'show_similar',
							],
							[
								'id' => 'similar_products',
								'type' => 'multielement',
								'texts' => [
									'add' => __('Přidat podobný produkt', 'mwshop'),
									'empty' => __('Podobný produkt', 'mwshop'),
								],
								'open' => 'under',
								'setting' => [
									[
										'id' => 'product_id',
										'title' => __('Produkt', 'mwshop'),
										'type' => 'product_select',
										'empty_text' => __('- vyberte produkt -', 'mwshop'),
										'only_published' => true,
										'edit_button' => true,
									],
								],
								'show_group' => 'show_similar',
								'show_val' => 'custom',
							],

						],
					];
				}

				mwSetting()->addObjectSetting([
					'id' => 'product',
					'title' => __('Produkt', 'mwshop'),
					'show_sidebar' => true,
					'fields' => $productFields,
				], [MWS_PRODUCT_SLUG]);

				mwSetting()->addObjectFastSetting([
					'id' => 'product',
					'fields' => $fastProductSetting,
				], [MWS_PRODUCT_SLUG]);

				/*
				mwSetting()->addObjectSetting([
					'id' => MWS_PRODUCT_META_KEY_VARIANTLIST,
					'title' => __('Varianty', 'mwshop'),
					'fields' => [
						[
							'id' => '',
							'type' => 'box',
							'open' => true,
							'title' => __('Varianty produktu', 'mwshop'),
							'class' => 'mws_variants',
							'setting' => [
								[
									'id' => 'variants',
									'name' => '',
									'type' => 'variantList',
								],
							],
						],

					]
				], [MWS_PRODUCT_SLUG]);
				*/

				if (MWS()->isCreated()) {
			mwSetting()->addObjectSetting([
		'id' => MWS_PRODUCT_META_KEY_GALLERY,
		'title' => __('Galerie', 'mwshop'),
		'fields' => [
			[
		'type' => 'box',
		'setting' => [
			[
		'id' => 'gallery_info',
		'content' => __('Jako hlavní obrázek produktu se použije náhledový obrázek, který můžete nastavit v pravém sloupci v nastavení tohoto produktu.', 'mwshop'),
		'type' => 'info',
			],
			[
		'id' => 'gallery',
		'title' => '',
		'type' => 'image_gallery',
		'editable' => false,
		'empty_input' => true,
			],
		],
			],
		],
			], [MWS_PRODUCT_SLUG]);
				}

				mwSetting()->addObjectSetting([
					'id' => 'automations',
					'title' => __('Automatizace', 'mwshop'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'id' => 'actions_info',
									'type' => 'info',
									'content' => __('Zde si můžete nastavit akce, které se mají automaticky provádět u tohoto produktu v určitém stavu objednávky. Globální akce nad celou objednávkou nezávislé na objednaných produktech naleznete v nastavení prodeje pod názvem Automatizace.', 'mwshop'),
								],
								[
									'id' => 'actions',
									'type' => 'eshop_automations',
								],
							],
						],
					],
				], [MWS_PRODUCT_SLUG]);

				if (MWS()->isCreated()) {
			mwSetting()->addObjectSetting([
		'id' => MWS_PRODUCT_META_KEY_COMPARATORS,
		'title' => __('Srovnávače cen', 'mwshop'),
		'fields' => [
			[
		'id' => 'comparators_basic_setting',
		'type' => 'toggle_group',
		'open' => 'true',
		'title' => __('Základní nastavení', 'mwshop'),
		'setting' => [
			[
		'name' => __('Zobrazení v XML feedech', 'mwshop'),
		'id' => 'product_hide_xml',
		'type' => 'switch',
		'label' => __('Nezobrazovat v XML feedech', 'mwshop'),
			],
			[
		'name' => __('Název pro XML feedy', 'mwshop'),
		'id' => 'productname',
		'type' => 'text',
		'tooltip' => __('Pokud je toto pole vyplněno, použije se v XML feedech uvedený název jako název produktu.', 'mwshop'),
			],
			[
		'name' => __('Doplněk k názvu', 'mwshop'),
		'id' => 'productname_addition',
		'type' => 'text',
		'tooltip' => __('Doplněk k názvu se bude zobrazovat za názvem produktu. Název produktu by měl obsahovat pouze výrobce, název produktu popřípadě variantu. Infromace o dopňcích nebo dárcích by potom měly být přidány pomocí tohoto pole.', 'mwshop'),
			],
		],
			],
			[
		'id' => 'comparators_heureka',
		'type' => 'toggle_group',
		'open' => 'true',
		'title' => __('Heureka.cz', 'mwshop'),
		'setting' => [
			[
		'name' => __('Kategorie na Heureka.cz', 'mwshop'),
		'id' => 'heureka_categoryname',
		'type' => 'heureka_category_list',
		'tooltip' => __('Produkt bude zařazen do vybrané kategorie na Heureka.cz.', 'mwshop'),
		'desc' => __('Vyberte kategorii, která je pro váš produkt nejvhodnější.', 'mwshop'),
			],
		],
			],
			[
		'id' => 'comparators_zbozi',
		'type' => 'toggle_group',
		'open' => 'true',
		'title' => __('Zboží.cz', 'mwshop'),
		'setting' => [
			[
		'name' => __('Kategorie na Zboží.cz', 'mwshop'),
		'id' => 'zbozi_categoryname',
		'type' => 'zbozi_category_list',
		'tooltip' => __('Produkt bude zařazen do vybrané kategorie na Zboží.cz.', 'mwshop'),
		'desc' => __('Vyberte kategorii, která je pro váš produkt nejvhodnější.', 'mwshop'),
			],
		],
			],
		],
			], [MWS_PRODUCT_SLUG]);


	if (!isset($seo['seo'])) {
		mwSetting()->addObjectSetting([
			'id' => 'page_seo',
			'title' => __('SEO', 'mwshop'),
			'fields' => MW()->container['seo_setting'],
		], [MWS_PRODUCT_SLUG]);
	}

	if (!isset($foption['hide_facebook'])) {
		mwSetting()->addObjectSetting([
			'id' => 'page_facebook',
			'title' => __('Facebook atributy', 'mwshop'),
			'fields' => MW()->container['facebook_setting'],
		], [MWS_PRODUCT_SLUG]);
	}

	mwSetting()->addObjectSetting([
'id' => MWS_PRODUCT_META_KEY_PAGECODES,
'title' => __('Vlastní kódy', 'cms'),
'fields' => [
	[
'id' => '',
'type' => 'box',
'title' => __('Konverzní kódy produktu', 'cms'),
'desc' => __('V případě že objednávka bude obsahovat tento produkt, zadaný konverzní kód se vypíše na děkovací stránce objednávky.', 'mwshop'),
'setting' => [
	[
'type' => 'info',
'content' => __('V případě že objednávka bude obsahovat tento produkt, zadaný konverzní kód se vypíše na děkovací stránce objednávky.', 'mwshop'),
	],
	[
'id' => 'conversion_codes',
'type' => 'code_list',
'list_type' => 'conversion',
	],
],
	],
	[
'id' => '',
'type' => 'box',
'title' => __('Kódy pro tento produkt', 'cms'),
'setting' => [
	[
'id' => 'codes',
'type' => 'code_list',
	],
],
	],
	[
'id' => 'codes_css_code',
'type' => 'toggle_group',
'open' => true,
'title' => __('CSS styly pro stránku produktu', 'cms'),
'setting' => [
	[
'id' => 'css',
'type' => 'textarea',
'rows' => 8,
'desc' => __('Zde můžete vložit vlastní CSS styly, které budou platit pouze pro stránku tohoto produktu.', 'cms'),
	],
],
	],
],
	], [MWS_PRODUCT_SLUG]);

	mwSetting()->addObjectSetting([
'id' => 'page_redirect',
'title' => __('Přesměrování', 'mwshop'),
'fields' => MW()->container['redirect_setting'],
	], [MWS_PRODUCT_SLUG]);
				}

				// Product category
				//***********************************************************************************

				mwSetting()->addObjectSetting([
					'id' => 'term',
					'title' => __('Kategorie', 'cms_ve'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'type' => 'item_set',
									'object_id' => MWS_PRODUCT_CAT_SLUG,
									'fields' => [
										'term_title' => [
											'label' => __('Název kategorie', 'cms'),
										],
										'term_parent' => [
											'label' => __('Nadřazená kategorie', 'cms'),
										],
									],
								],
							],
						],
					],
				], [MWS_PRODUCT_CAT_SLUG]);

		// Product tags
		//***********************************************************************************

		mwSetting()->addObjectSetting([
			'id' => 'mw_product_tag',
			'title' => __('Štítek', 'cms_ve'),
			'fields' => [
				[
					'type' => 'box',
					'setting' => [
						[
							'type' => 'item_set',
							'object_id' => MWS_PRODUCT_TAG_SLUG,
							'fields' => [
								'term_title' => [
									'label' => __('Text štítku', 'cms'),
									'slug' => false,
								],
							],
						],
						[
							'name' => __('Barva štítku', 'mwshop'),
							'id' => 'color',
							'type' => 'color',
							'position' => 'top right',
							'hide_swatches' => true,
							'content' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
						],
					],
				],
			],
		], [MWS_PRODUCT_TAG_SLUG]);

				// Product properties setting
				//***********************************************************************************

				mwSetting()->addObjectSetting([
					'id' => MWS_PROPERTY_META_KEY,
					'title' => __('Nastavení', 'mwshop'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'type' => 'item_set',
									'object_id' => MWS_PROPERTY_SLUG,
									'fields' => [
										'post_title' => [
											'label' => __('Název parametru', 'mwshop'),
											'slug' => false,
										],
									],
								],
								[
									'name' => __('Typ parametru', 'mwshop'),
									'id' => 'type',
									'type' => 'select',
									'options' => [
										['value' => MwsPropertyType::Text, 'name' => __('Text (hodnota se zadává jako text)', 'mwshop')],
										['value' => MwsPropertyType::Enumeration, 'name' => __('Výčet (hodnota se vybírá ze sady hodnot)', 'mwshop')],
									],
									'content' => MwsPropertyType::Text, // default value
									'show' => 'parameter_type',
								],
								[
									'id' => 'values',
									'name' => __('Seznam hodnot', 'mwshop'),
									'type' => 'simple_feature',
									'text_add' => __('Přidat hodnotu', 'mwshop'),
									'sortable' => true,
									'fields' => [
										'name' => [
											'title' => __('Hodnota', 'mwshop'),
										],
									],
									'show_group' => 'parameter_type',
									'show_val' => MwsPropertyType::Enumeration,
								],
								[
									'name' => __('Jednotka', 'mwshop'),
									'id' => 'unit',
									'type' => 'text',
									'tooltip' => __('Jednotka je volitelná. Ve výpisu parametrů produktu se zobrazuje za hodnotou.'),
								],
								[
									'name' => __('Popis', 'mwshop'),
									'id' => 'post_excerpt',
									'type' => 'textarea',
									'save' => 'post',
								],
							],
						],
					],
				], [MWS_PROPERTY_SLUG]);

				mwSetting()->addObjectSetting([
					'id' => 'currency',
					'title' => __('Měna', 'mwshop'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'name' => __('Měna', 'mwshop'),
									'id' => 'currency',
									'type' => 'currency',
								],
								[
									'type' => 'info',
									'class' => MWS()->getSelectedGatewayId() === 'mioweb' ? 'cms_nodisp' : '',
									'content' => __('V případě že je prodej napojen na FAPI, tak nastavení pro bankovní spojení pro danou měnu najdete ve FAPI v <a href="https://web.fapi.cz/settings/importers?projectId=all" target="_blank">Nastavení -> Způsoby platby</a>.', 'mwshop'),
								],
								[
									'name' => __('Číslo účtu', 'mwshop'),
									'id' => 'account_number',
									'type' => 'text',
									'class' => MWS()->getSelectedGatewayId() === 'fapi' ? 'cms_nodisp' : '',
								],
								[
									'name' => __('IBAN', 'mwshop'),
									'id' => 'iban',
									'type' => 'text',
									'class' => MWS()->getSelectedGatewayId() === 'fapi' ? 'cms_nodisp' : '',
								],
								[
									'name' => __('BIC (SWIFT)', 'mwshop'),
									'id' => 'bic',
									'type' => 'text',
									'class' => MWS()->getSelectedGatewayId() === 'fapi' ? 'cms_nodisp' : '',
								],
							],
						],
						[
							'id' => 'round_orders',
							'type' => 'toggle_group',
							'action' => 'reload',
							'title' => __('Zaokrouhlovat celkovou cenu objednávky', 'cms'),
							'checkbox' => 0,
							'setting' => [
								[
									'name' => __('Zaokrouhlovat', 'mwshop'),
									'id' => 'round_function',
									'type' => 'select',
									'options' => [
										['name' => __('Matematicky', 'cms'), 'value' => 'round'],
										['name' => __('Nahoru', 'cms'), 'value' => 'ceil'],
										['name' => __('Dolů', 'cms'), 'value' => 'floor'],
									],
									'content' => 'ceil',

								],
								[
									'name' => __('Zaokrouhlovat na', 'mwshop'),
									'id' => 'round_precision',
									'type' => 'select',
									'options' => [
										['name' => __('Celá čísla', 'cms'), 'value' => '0'],
										['name' => __('Na desetiny', 'cms'), 'value' => '1'],
										['name' => __('Na setiny', 'cms'), 'value' => '2'],
									],
									'content' => '0',
								],
							],
						],
					],
				], [MWS_CURRENCY_SLUG]);

				mwSetting()->addObjectSetting([
					'id' => 'country',
					'title' => __('Země doručení', 'mwshop'),
					'fields' => [
						[
							'type' => 'box',
							'setting' => [
								[
									'name' => __('Země', 'mwshop'),
									'id' => 'country',
									'type' => 'shipping_country',
								],
							],
						],
					],
				], [MWS_SHIPPING_COUNTRY_SLUG]);

		// Upsell

		mwSetting()->addObjectSetting([
			'id' => 'mw_page_codes',
			'title' => __('Vlastní kódy', 'cms'),
			'fields' => MW()->container['custom_codes'],
		], [MWS_UPSELL_SLUG]);

		mwSetting()->addObjectSettingCategory([
			'id' => 'appearance',
			'title' => __('Vzhled stránky', 'cms'),
		], [MWS_UPSELL_SLUG]);

		mwSetting()->addObjectSetting(MW()->container['page_appearance'], [MWS_UPSELL_SLUG]);

		mwSetting()->addObjectSetting([
			'id' => 'change_template',
			'category' => 'appearance',
			'title' => __('Šablona stránky', 'cms_ve'),
			'action' => 'mw_change_template',
		], [MWS_UPSELL_SLUG]);
	}

}
