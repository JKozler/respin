<?php
define('FUNNELS_VERSION', '1.2.0');
MW()->add_version('funnels', FUNNELS_VERSION);

define('FUNNELS_DIR', get_template_directory_uri() . '/modules/funnels/');

// language
MW()->load_theme_lang('mw_funnels', get_template_directory() . '/modules/funnels/languages');

require_once(TEMPLATEPATH . '/modules/funnels/elements.php');
require_once(TEMPLATEPATH . '/modules/funnels/functions.php');
require_once(TEMPLATEPATH . '/modules/funnels/elements_print.php');
require_once(TEMPLATEPATH . '/modules/funnels/lib/MwFunnel.php');
require_once(TEMPLATEPATH . '/modules/funnels/lib/FunnelPage.php');
require_once(TEMPLATEPATH . '/modules/funnels/funnels_class.php');


add_theme_support('menus'); // TODO why? is needed?

MWF();

// register setting objects
MwFunnel::registerFunnels();

mwPageSelector()->addTab([
	'id' => 'funnel',
	'title' => __('Cesty zákazníka', 'mw_funnels'),
], 3);

MW()->add_templates_topos(3, 'squeeze', [
	'name' => __('Vstupní', 'cms_ve'),
	'icon' => 'log-in',
	'path' => '/modules/funnels/templates/squeeze/',
	'list' => [
		'sq1' => [
			'name' => __('Základní typy vstupních stránek', 'cms_ve'),
			'list' => ['1', '2', '4', '5'],
		],
	],
]);
MW()->add_templates_topos(6, 'campaign', [
	'name' => __('Kampaňové', 'mw_funnels'),
	'icon' => 'play',
	'path' => '/modules/funnels/templates/funnel/',
	'list' => [
		'sale_letters' => [
			'name' => __('Video stránky', 'mw_funnels'),
			'list' => ['3', '1', '2'],
		],
	],
]);


// Nastavení
//***********************************************************************************


mwSetting()->registerPageSettingType('funnels_dashboard', [
	'class' => 'MWF',
	'function' => 'openFunnelsSetting',
]);

mwSetting()->addGroup([
	'id' => 'funnels',
	'icon' => 'filter',
	'title' => __('Cesty zákazníka', 'mw_funnels'),
	'home' => 'mw_funnels',
	'order' => 10,
]);
mwSetting()->addPage([
	'id' => 'mw_funnels',
	'icon' => 'bar-chart-2',
	'group' => 'funnels',
	'title' => __('Přehled', 'mw_funnels'),
	'type' => 'funnels_dashboard',
]);
