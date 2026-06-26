<?php

global $vePage;

define('MIOWEB_VERSION', '0.9');
MW()->add_version('mioweb', MIOWEB_VERSION);

define('MIOWEB_DIR', get_template_directory_uri() . '/modules/mioweb/');

// language
MW()->load_theme_lang('cms_mioweb', get_template_directory() . '/modules/mioweb/languages');

require_once(TEMPLATEPATH . '/modules/mioweb/functions.php');
require_once(TEMPLATEPATH . '/modules/mioweb/elements.php');
require_once(TEMPLATEPATH . '/modules/mioweb/elements_print.php');
require_once(TEMPLATEPATH . '/modules/mioweb/mioweb_class.php');
require_once(TEMPLATEPATH . '/modules/mioweb/objects/MwCampaignPage.php');
require_once(TEMPLATEPATH . '/modules/mioweb/objects/MwCampaign.php');

MwCampaign::registerCamapaigns();

MwCampaigns();

mwPageSelector()->addTab([
	'id' => 'campaign',
	'title' => __('Kampaně', 'cms_mioweb'),
], 4);

MW()->add_templates_topos(3, 'squeeze', [
	'name' => __('Vstupní', 'cms_ve'),
	'icon' => 'log-in',
	'path' => '/modules/mioweb/templates/squeeze/',
	'list' => [
		'sq1' => [
			'name' => __('Základní typy vstupních stránek', 'cms_ve'),
			'list' => ['1', '2', '4', '5'],
		],
	],
]);
MW()->add_templates_topos(6, 'campaign', [
	'name' => __('Kampaňové', 'cms_mioweb'),
	'icon' => 'play',
	'path' => '/modules/mioweb/templates/campaign/',
	'list' => [
		'sale_letters' => [
			'name' => __('Video stránky', 'cms_mioweb'),
			'list' => ['3', '1', '2'],
		],
	],
]);

// Top panel menu
//***********************************************************************************
if (isset(MwCampaigns()->first_campaign)) {
	$vePage->addFastNav(
		[
			'id' => 'campaign',
			'title' => __('Kampaně', 'cms_mioweb'),
			'url' => (isset(MwCampaigns()->first_campaign['squeeze']) && MwCampaigns()->first_campaign['squeeze'] ? get_permalink(MwCampaigns()->first_campaign['squeeze']) : '#'),
			'submenu' => MwCampaigns()->create_mioweb_fast_submenu(),
		],
		20
	);
}

// Nastavení
//***********************************************************************************

mwSetting()->addGroup([
	'id' => 'campaigns',
	'icon' => 'target',
	'title' => __('Kampaně', 'cms_mioweb'),
	'home' => 'campaigns',
	'order' => 10,
]);
mwSetting()->addPage([
	'id' => 'campaigns',
	'icon' => 'target',
	'group' => 'campaigns',
	'title' => __('Kampaně', 'cms_mioweb'),
	'type' => 'list',
]);

mwSetting()->addObjectFastSetting([
	'id' => 'campaign',
	'fields' => [
		[
			'id' => 'name',
			'name' => __('Název kampaně', 'cms_mioweb'),
			'required' => 1,
			'type' => 'text',
		],
	],
], ['campaigns']);

mwSetting()->addObjectSetting([
	'id' => 'pages',
	'title' => __('Stránky kampaně', 'cms_mioweb'),
	'fields' => [
		[
			'id' => '',
			'type' => 'campaigns',
		],
	],
], ['campaigns']);

mwSetting()->addObjectSetting([
	'id' => 'settings',
	'title' => __('Nastavení', 'cms_mioweb'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'title' => __('Název kampaně', 'cms_mioweb'),
					'id' => 'name',
					'type' => 'text',
				],
				[
					'title' => __('Přístupový kód', 'cms_mioweb'),
					'id' => 'code',
					'type' => 'text',
					'tooltip' => __('Tento kód budete používat jako hodnotu atributu setuser v URL adrese, na kterou budete směrovat registrované návštěvníky. Díky tomuto kódu se jim uloží cookies a umožní se jim přístup na stránky kampaně. Přístupový kód musí být jedinečný pro každou kampaň.', 'cms_mioweb'),
				],
				[
					'name' => __('Délka platnosti přístupu', 'cms_mioweb'),
					'id' => 'duration',
					'type' => 'number',
					'unit' => __('dní', 'cms_mioweb'),
					'placeholder' => '365',
					'tooltip' => __('Po vypršení této doby se zneplatní přístup uživatele do kampaně. Při dalším přístupu se vynulují všechny odpočty a kampaň začne od začátku. Pokud nic nevyplníte platnost se nastaví na jeden rok.', 'cms_mioweb'),
				],
				[
					'name' => __('Přesměrování ze vstupní stránky', 'cms_mioweb'),
					'id' => 'noredirect',
					'type' => 'switch',
					'label' => __('Nepřesměrovávat uživatele ze vstupní stránky, když je v kampani přihlášený', 'cms_mioweb'),
					'tooltip' => __('Defaultně je každý uživatel, který má do kampaně přístup automatický přesměrován na 1. stránku s obsahem zdarma.', 'cms_mioweb'),
				],
			],
		],
		[
			'type' => 'box',
			'title' => __('Evergreen', 'cms_mioweb'),
			'setting' => [
				[
					'name' => __('Evergreen', 'cms_mioweb'),
					'id' => 'evergreen',
					'type' => 'switch',
					'label' => __('Aktivovat evergreen mód', 'cms_mioweb'),
					'desc' => __('Pokud je evergreen mód u kampaně aktivní, znamená to, že každému novému návštěvníkovi, který se registruje, budou zpřístupněny pouze ty stránky, na které ho přímo odkážete (za použití výše nastaveného přístupového kódu v URL). Můžete tak stránky kampaně zpřístupňovat postupně například pomocí e-mailové kampaně, s e-maily odkazujícími na jednotlivé stránky. Pokud je evergreen mód aktivní, deaktivuje se nastavení data zveřejnění u všech stránek kampaně.', 'cms_mioweb'),
				],
			],
		],
	],
], ['campaigns']);
