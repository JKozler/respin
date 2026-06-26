<?php

global $vePage;
global $mwContainer;

use Mioweb\VisualEditor\Lib\ButtonStyles;

define('VS_VERSION', '3.1.2');
MW()->add_version('visualeditor', VS_VERSION);

// language
MW()->load_theme_lang('cms_ve', get_template_directory() . '/library/visualeditor/languages');

//Classes loading
require_once(__DIR__ . '/lib/GDPR.php');
require_once(__DIR__ . '/lib/NavMenu.php');
require_once(__DIR__ . '/lib/Image.php');
require_once(__DIR__ . '/lib/Icon.php');
require_once(__DIR__ . '/lib/Colors.php');
require_once(__DIR__ . '/lib/Link.php');
require_once(__DIR__ . '/lib/Button.php');
require_once(__DIR__ . '/lib/ButtonStyles.php');

require_once(__DIR__ . '/lib/mwFrontComponents.php');
require_once(__DIR__ . '/lib/pageselector/PageSelectorItem.php');
require_once(__DIR__ . '/lib/pageselector/PageSelectorList.php');
require_once(__DIR__ . '/lib/pageselector/PageSelectorTabService.php');
require_once(__DIR__ . '/lib/pageselector/PageSelectorTab.php');
require_once(__DIR__ . '/lib/pageselector/PageSelector.php');
require_once(__DIR__ . '/pagebuilder_class.php');
require_once(__DIR__ . '/pagedisplay_class.php');
require_once(__DIR__ . '/visual_editor_class.php');
require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/lib/nbsp.php');
require_once(__DIR__ . '/elements-print.php');
require_once(__DIR__ . '/lib/weditor/weditor_class.php');
require_once(__DIR__ . '/lib/weditor/popups_class.php');
require_once(__DIR__ . '/lib/install/install_class.php');
require_once(__DIR__ . '/lib/shortcodes/shortcodes.php');
//require_once(__DIR__ . '/lib/blocks/blocks.php');
require_once(__DIR__ . '/lib/upload_limit.php');
require_once(__DIR__ . '/lib/back_compatibility.php');
require_once(__DIR__ . '/lib/css_generator.php');
require_once(__DIR__ . '/lib/set_container.php');
//require_once(__DIR__ . '/lib/tutorials_class.php');
require_once(__DIR__ . '/lib/tutorials/tutorials_class.php');
require_once(__DIR__ . '/lib/builder_messages.php');
require_once(__DIR__ . '/lib/Compatibility.php');

require_once(__DIR__ . '/lib/install/installator_class.php');
MWInstallator();
MwWebInstall();

// Image sizes
add_image_size('mio_columns_c1', 970); //dont crop
add_image_size('mio_columns_c2', 461); //dont crop
add_image_size('mio_columns_c3', 297); //dont crop
add_image_size('mio_columns_c4', 213); //dont crop
add_image_size('mio_columns_c5', 171); //dont crop

$vePage = new visualEditor();
$mwContainer = new mwElementsContainer();

function mwButtonStyles(): ButtonStyles
{
	return ButtonStyles::instance();
}

cmsPopups::registerPopupPostType();

require_once(__DIR__ . '/shortcodes.php');

mwPageSelector()->addTab([
	'id' => 'web',
	'title' => __('Web', 'cms_ve'),
], 0);

mwPageSelector()->addTab([
	'id' => 'all',
	'title' => __('Vše', 'cms_ve'),
], 100);

MWInstallator()->addInstallSteps('object', [
	'steps' => [
		[
			'id' => 'select_template',
			'title' => __('Vyber šablonu', 'cms_ve'),
			'type' => 'select_template',
			'click' => 'next',
			'import' => true,
		],
		[
			'id' => 'title',
			'title' => __('Zadej název', 'cms_ve'),
			'type' => 'objectForm',
			'content' => [
				'button_text' => __('Vytvořit stránku', 'cms_ve'),
			],
		],
	],
]);

MWInstallator()->addInstallSteps('weditorWithTemplate', [
	'steps' => [
		[
			'id' => 'select_template',
			'title' => __('Vyber šablonu', 'cms_ve'),
			'type' => 'select_template',
			'click' => 'next',
		],
		[
			'id' => 'title',
			'title' => __('Zadej název', 'cms_ve'),
			'type' => 'post_title',
			'content' => [
				'button_text' => __('Vytvořit', 'cms_ve'),
				'input_placeholder' => __('Název', 'cms_ve'),
				'desc' => __('Název můžete kdykoli změnit.', 'cms_ve'),
			],
		],
	],
]);

MWInstallator()->addInstallSteps('weditor', [
	'steps' => [
		[
			'id' => 'title',
			'title' => __('Zadej název', 'cms_ve'),
			'type' => 'post_title',
			'content' => [
				'button_text' => __('Vytvořit', 'cms_ve'),
				'input_placeholder' => __('Název', 'cms_ve'),
				'desc' => __('Název můžete kdykoli změnit.', 'cms_ve'),
			],
		],
	],
]);

if ($vePage->edit_mode || !MW()->installedWeb()) {
	$tags = [
		'all' => __('Vše', 'cms_ve'),
		'personal' => __('Osobní', 'cms_ve'),
		'business' => __('Firemní', 'cms_ve'),
		'blog' => __('Blog', 'cms_ve'),
		'expert' => __('Expertní', 'cms_ve'),
		'product' => __('Produktový', 'cms_ve'),
		'portfolio' => __('Portfolio', 'cms_ve'),
		'fitness' => __('Fitness', 'cms_ve'),
		'photographer' => __('Pro fotografy', 'cms_ve'),
		'restaurant' => __('Restaurace', 'cms_ve'),
		'reality' => __('Realitní', 'cms_ve'),
		'event' => __('Událost', 'cms_ve'),
		'empty' => __('Prázdný', 'cms_ve'),
	];

	$webs = [

		'personal' => get_template_directory() . '/library/visualeditor/web_templates/personal/',

		'firm' => get_template_directory() . '/library/visualeditor/web_templates/firm/',
		'firm2' => get_template_directory() . '/library/visualeditor/web_templates/firm2/',

		'expert3' => get_template_directory() . '/library/visualeditor/web_templates/expert3/',
		'expert4' => get_template_directory() . '/library/visualeditor/web_templates/expert4/',
		//'expert2' => get_template_directory() . '/library/visualeditor/web_templates/expert2/',
		//'expert' => get_template_directory() . '/library/visualeditor/web_templates/expert/',

		'blog_pzp' => get_template_directory() . '/library/visualeditor/web_templates/blog_pzp/',
		'blog' => get_template_directory() . '/library/visualeditor/web_templates/blog/',
		'blog2' => get_template_directory() . '/library/visualeditor/web_templates/blog2/',
		'blog3' => get_template_directory() . '/library/visualeditor/web_templates/blog3/',

		'photographer' => get_template_directory() . '/library/visualeditor/web_templates/photograph/',
		'photographer2' => get_template_directory() . '/library/visualeditor/web_templates/photographer2/',
		'photographer3' => get_template_directory() . '/library/visualeditor/web_templates/photographer3/',

		'portfolio1' => get_template_directory() . '/library/visualeditor/web_templates/portfolio1/',
		'portfolio3' => get_template_directory() . '/library/visualeditor/web_templates/portfolio3/',
		'portfolio2' => get_template_directory() . '/library/visualeditor/web_templates/portfolio2/',
		'portfolio4' => get_template_directory() . '/library/visualeditor/web_templates/portfolio4/',

		'fitness' => get_template_directory() . '/library/visualeditor/web_templates/fitness/',
		'yoga' => get_template_directory() . '/library/visualeditor/web_templates/yoga/',

		'book' => get_template_directory() . '/library/visualeditor/web_templates/book/',
		'servis' => get_template_directory() . '/library/visualeditor/web_templates/sluzba/',
		'interier' => get_template_directory() . '/library/visualeditor/web_templates/interier/',
		'jewelry' => get_template_directory() . '/library/visualeditor/web_templates/jewelry/',

		'conference' => get_template_directory() . '/library/visualeditor/web_templates/conference/',

		'reality' => get_template_directory() . '/library/visualeditor/web_templates/reality/',
		'reality2' => get_template_directory() . '/library/visualeditor/web_templates/reality2/',
		'reality3' => get_template_directory() . '/library/visualeditor/web_templates/reality3/',

		'restaurant' => get_template_directory() . '/library/visualeditor/web_templates/restaurant/',
		'restaurant2' => get_template_directory() . '/library/visualeditor/web_templates/restaurant2/',
		'cafe' => get_template_directory() . '/library/visualeditor/web_templates/cafe/',
		'cafe2' => get_template_directory() . '/library/visualeditor/web_templates/cafe2/',
		'pizzerie' => get_template_directory() . '/library/visualeditor/web_templates/pizzerie/',

		'empty' => get_template_directory() . '/library/visualeditor/web_templates/empty/',

	];

	$lite_tags = [
		'personal' => __('Osobní web', 'cms_ve'),
		'blog' => __('Blog', 'cms_ve'),
		'empty' => __('Prázdný web', 'cms_ve'),
	];

	if (mw_is_lite_editor()) {
		MwWebInstall()->add_web_tags($lite_tags);
	} else {
		MwWebInstall()->add_web_tags($tags);
	}

	//Webs
	//***********************************************************************************

	MwWebInstall()->add_webs($webs);
}
// Templates
//***********************************************************************************

MW()->add_templates([
	'page' => [
		'name' => __('Obsahové', 'cms_ve'),
		'icon' => 'file',
		'lite' => true,
		'path' => '/library/visualeditor/templates/page/',
		'list' => [
			'empty' => [
				'name' => __('Prázdné šablony', 'cms_ve'),
				'list' => ['1', '3'],
			],
			'content' => [
				'name' => __('Obsahové šablony', 'cms_ve'),
				'list' => ['5', '7', '9', '10'],
			],
		],
	],

	'landing' => [
		'name' => __('Domovské', 'cms_ve'),
		'icon' => 'home',
		'lite' => true,
		'path' => '/library/visualeditor/templates/landing/',
		'list' => [
			/*
			'personal' => array(
				'name' => __('Osobní domovské stránky', 'cms_ve'),
				'list' => array('personal1', 'personal2', 'personal3', 'personal4', 'personal5', 'personal6')
			),
			'land' => array(
				'name' => __('Univerzální domovské stránky', 'cms_ve'),
				'list' => array('land1', 'land2')
			),*/
			'ebook' => [
				'name' => __('Stránky vhodné pro prodej knih nebo ebooků', 'cms_ve'),
				'list' => ['book1', 'book2', 'ebook1', 'ebook2'],
				'lite_list' => ['book1', 'ebook1', 'ebook2'],
			],
		],
	],

	'webinar' => [
		'name' => __('Webinářové', 'cms_ve'),
		'icon' => 'video',
		'path' => '/library/visualeditor/templates/webinar/',
		'list' => [
			'registration' => [
				'name' => __('Stránky pro registraci na webinář', 'cms_ve'),
				'list' => ['4', '1', '2n', '3'],
			],
			'live' => [
				'name' => __('Stránky pro vysílání webináře', 'cms_ve'),
				'list' => ['live4', 'live1', 'live2n', 'live3'],
			],
		],
	],
	'sale' => [
		'name' => __('Prodejní', 'cms_ve'),
		'icon' => 'dollar-sign',
		'lite' => true,
		'path' => '/library/visualeditor/templates/sale/',
		'list' => [
			'sale_letters' => [
				'name' => __('Prodejní stránky', 'cms_ve'),
				'list' => ['1', '2'/*, '3'*/, '4'],
			],
			'sale_form' => [
				'name' => __('Stránky s prodejním formulářem', 'cms_ve'),
				'list' => ['form1', 'form2'],
			],
			'upsell' => [
				'name' => __('Upsell stránky', 'cms_ve'),
				'list' => ['upsell1'],
			],

		],
	],
	'thx' => [
		'name' => __('Děkovací', 'cms_ve'),
		'icon' => 'thumbs-up',
		'lite' => true,
		'path' => '/library/visualeditor/templates/others/',
		'list' => [
			'thx' => [
				'name' => __('Děkovací stránky', 'cms_ve'),
				'list' => ['1', 'thx2', 'thx3', 'thx4', 'thx5', 'thx_webinar1', 'thx_webinar2', 'thx1'],
				'lite_list' => ['1', 'thx2', 'thx3', 'thx4', 'thx5', 'thx1'],
			],
		],
	],
	'others' => [
		'name' => __('Ostatní', 'cms_ve'),
		'icon' => 'square',
		'lite' => true,
		'path' => '/library/visualeditor/templates/others/',
		'list' => [
			'thx' => [
				'name' => __('Již brzy', 'cms_ve'),
				'list' => ['comming1', 'comming2'],
			],
		],
	],

	'popups' => [
		'name' => __('Klasické pop-upy', 'cms_ve'),
		'path' => '/library/visualeditor/templates/popups/',
		'lite' => true,
		'type' => 'cms_popup',
		'list' => [
			'registration' => [
				'name' => '',
				'list' => ['1', '6', '7', '5', '3', '2', '4'],
			],
		],
	],
	've_elvar' => [
		'name' => __('Předdefinovaný obsah', 'cms_ve'),
		'path' => '/library/visualeditor/templates/element_variables/',
		'lite' => true,
		'type' => 've_elvar',
		'list' => [
			'registration' => [
				'name' => '',
				'list' => ['1'],
			],
		],
	],
	'mw_slider' => [
		'name' => __('Slider', 'cms_ve'),
		'path' => '/library/visualeditor/templates/sliders/',
		'type' => 'mw_slider',
		'list' => [
			'registration' => [
				'name' => '',
				'list' => ['1', '2', '3', '4', '5'],
			],
		],
	],
	've_header' => [
		'name' => __('Základní', 'cms_ve'),
		'lite' => true,
		'path' => '/library/visualeditor/templates/headers/before/',
		'type' => 've_header',
		'list' => [
			'bheaders' => [
				'name' => '',
				'list' => ['1', '2', '3', '4'],
			],
		],
	],
	'cms_footer' => [
		'name' => __('Klasické patičky', 'cms_ve'),
		'lite' => true,
		'path' => '/library/visualeditor/templates/footers/',
		'type' => 'cms_footer',
		'list' => [
			'cfooters' => [
				'name' => '',
				'list' => ['1', '2', 'empty'],
				'lite_list' => ['2', 'empty'],
			],
		],
	],
]);


$mwContainer->list['patterns'] = [
	'1' => VS_DIR . 'images/patterns/',
	'2' => VS_DIR . 'images/patterns/',
	'3' => VS_DIR . 'images/patterns/',
	'4' => VS_DIR . 'images/patterns/',
	'5' => VS_DIR . 'images/patterns/',
	'6' => VS_DIR . 'images/patterns/',
	'7' => VS_DIR . 'images/patterns/',
	'8' => VS_DIR . 'images/patterns/',
	'9' => VS_DIR . 'images/patterns/',
	'10' => VS_DIR . 'images/patterns/',
	'11' => VS_DIR . 'images/patterns/',
	'12' => VS_DIR . 'images/patterns/',
	'13' => VS_DIR . 'images/patterns/',
	'14' => VS_DIR . 'images/patterns/',
	'15' => VS_DIR . 'images/patterns/',
	'16' => VS_DIR . 'images/patterns/',
	'17' => VS_DIR . 'images/patterns/',
	'18' => VS_DIR . 'images/patterns/',
	'19' => VS_DIR . 'images/patterns/',
	'20' => VS_DIR . 'images/patterns/',
	'21' => VS_DIR . 'images/patterns/',
	'22' => VS_DIR . 'images/patterns/',
	'23' => VS_DIR . 'images/patterns/',
	'24' => VS_DIR . 'images/patterns/',
	'25' => VS_DIR . 'images/patterns/',
	'26' => VS_DIR . 'images/patterns/',
];
$mwContainer->list['shape_dividers'] = [
	'tilt' => '<svg viewBox="0 0 1000 100" preserveAspectRatio="none"><path d="M0,6V0h1000v100L0,6z"></path></svg>',
	'ramp' => '<svg preserveAspectRatio="none" viewBox="0 0 1000 150"><path d="M0,0V2S216.952-1.19,475,34c287.9,39.262,525,116,525,116V0H0Z"></path></svg>',
	'wave' => '<svg preserveAspectRatio="none" viewBox="0 0 1000 150"><path d="M0,0H1000V75s-106.873,75-251,75C595.93,150,401.535,16,249,16,106.347,16,0,75,0,75"></path></svg>',
	'zigzag' => '<svg viewBox="0.5 0.5 1800 5.8">
      <path d="M5.4.4l5.4 5.3L16.5.4l5.4 5.3L27.5.4 33 5.7 38.6.4l5.5 5.4h.1L49.9.4l5.4 5.3L60.9.4l5.5 5.3L72 .4l5.5 5.3L83.1.4l5.4 5.3L94.1.4l5.5 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.4 5.3L161 .4l5.4 5.3L172 .4l5.5 5.3 5.6-5.3 5.4 5.3 5.7-5.3 5.4 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.5 5.3L261 .4l5.4 5.3L272 .4l5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.7-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.7-5.3 5.4 5.4h.2l5.6-5.4 5.5 5.3L361 .4l5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.7-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.6-5.4 5.5 5.3L461 .4l5.5 5.3 5.6-5.3 5.4 5.3 5.7-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1L550 .4l5.4 5.3L561 .4l5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.4 5.3 5.7-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2L650 .4l5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2L750 .4l5.5 5.3 5.6-5.3 5.4 5.3 5.7-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.7-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.4h.2L850 .4l5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.4 5.3 5.7-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.7-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.4 5.3 5.7-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.7-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.7-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.7-5.3 5.4 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.6-5.4 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.7-5.3 5.4 5.4h.2l5.6-5.4 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.7-5.4 5.4 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.5 5.4h.1l5.6-5.4 5.5 5.3 5.6-5.3 5.5 5.3 5.6-5.3 5.4 5.3 5.7-5.3 5.4 5.3 5.6-5.3 5.5 5.4V0H-.2v5.8z"></path>
    </svg>',
	'mountains' => '<svg preserveAspectRatio="none" viewBox="0 0 1000 150"><path d="M1-10V69.995L251,10,751,140l250-70.005V-10H1Z"></path></svg>',
	'mountains2' => '<svg preserveAspectRatio="none" viewBox="0 0 1000 150"><path d="M1000,15.647L842,114.706,713,35.294,614,150,368,52.941l-52,61.765L235,35.294,75,114.706,0,35.765V0H1000V15.647Z"/></svg>',
	'graph' => '<svg preserveAspectRatio="none" viewBox="0 0 1000 150"><path d="M0,19L52,8l66,12,47,26,77-8,65,35,60-12,47,28,75,11,52-14,68,21,85-23,58,22,56-14,64,24,81,8,47,26V0H0V19Z"></path></svg>',
	'triangle_negative' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none"><path d="M1000,0V150L500,2,0,150V0H1000Z"/></svg>',
	'triangle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none"><path d="M0,0V2L500,150,1000,2V0H0Z"/></svg>',
	'ramp_negative' => '<svg preserveAspectRatio="none" viewBox="0 0 1000 150"><path d="M1000,0V149.938s-216.952,3.189-475-31.987C237.1,78.706,0,2,0,2V0H1000Z"></path></svg>',
	'arrow' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none"><path d="M351,0L500,150,649,0H351Z"></path></svg>',
	'arrow_negative' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none"><path d="M1000,150V0H0V150H351L500,2,649,150h351Z"></path></svg>',
	'curve' => '<svg preserveAspectRatio="none"  viewBox="0 0 1000 150"><path d="M0,0V2S160.3,150,500,150C839.008,150,1000,2,1000,2V0H0Z"></path></svg>',
	'curve_negative' => '<svg preserveAspectRatio="none"  viewBox="0 0 1000 150"><path d="M1000,0V150S839.7,2,500,2C160.992,2,0,150,0,150V0H1000Z"></path></svg>',
	'asymetrical' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none"><path d="M0,0V2L749,150,1000,2V0H0Z"/></svg>',
	'asymetrical_negative' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none"><path d="M1000,0V150L749,2,0,150V0H1000Z"/></svg>',
	'brush1' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none">
      <path d="M810.059,39.76a2.834,2.834,0,0,0-.528-1.686c-1.77-1.812-3.347-1.5-3.693-5.059a11.763,11.763,0,0,1-4.221-.562c1.123-2.984,1.113-3.394,2.11-6.746h-2.638c-1.207,1.467-2.536,2.963-3.693,4.5h-1.055c-0.559-2.348-2.178-4.729-4.749-5.059-1,.689-4.032.882-5.276,1.124v2.811a2.989,2.989,0,0,1,.528,1.686c-1.629,1.095-1.81,1.623-4.749,1.686a15.2,15.2,0,0,0-11.08-4.5V25.706l2.638-.562V23.458c-2.432.457-2.5,1.378-5.276,1.687a7.8,7.8,0,0,1-1.055-4.5,1.777,1.777,0,0,0,1.055-.562c-3.771.686-5.71-1.545-9.5-2.249l0.528,2.811h-1.583c-4.386-3.106-14.6.493-17.412,1.686V22.9c0.857,0.759.592,0.36,1.056,1.686-1.279.509-.839,0.6-2.111,1.124-1.785-1.247-5.682-1.936-7.914-2.249v2.249c-3.385,0-5.542-.715-8.442-1.124q-0.264.562-.527,1.124c2.434-.744,5.73.47,9.5,0.562q0.526,0.843,1.055,1.686a16.337,16.337,0,0,1-5.276.562c-6.519-4.707-16.019,3.224-24.271.562-1.294-.418-2.277-2.481-3.165-2.811-1.487-.552-5.936,1.079-8.442.562-5.026-1.036-11.57-3.459-18.467-1.686-4.021,1.034-6.155-.919-7.914-1.687-2.228-.972-8.93,2.784-8.969,2.811,1.341,0.724,1.515.617,2.11,2.249a7.846,7.846,0,0,1-2.638.562,5.069,5.069,0,0,0-2.11-1.686,1.964,1.964,0,0,1,.527-1.124c0.407-.383,1.178-0.737,1.583-1.124-7.174-.23-23.675-6.533-30.074-2.249,2.183,0.342,3.882,1.922,6.859,1.124,0.569-.152.939-1.4,2.11-1.124,1.339,0.311,1.511,1.615,5.277,1.686q0.262,1.967.527,3.935a20.388,20.388,0,0,1-6.331.562,10.091,10.091,0,0,0-2.111-1.124c0.727,1.726-.072,1.634,3.166,1.687a1.87,1.87,0,0,0,1.055.562V31.89a17.432,17.432,0,0,0-5.276,1.124,4.556,4.556,0,0,1-2.638-.562h-15.3a2.835,2.835,0,0,0-.527-1.686c-2.07-1.424-6.132.216-8.97-.562-2.6-.713-11-3.709-14.773-1.124,2.141,0.289,2.152.114,2.638,2.249a6.649,6.649,0,0,1-4.221,1.124,9.1,9.1,0,0,0-4.221-2.811c-1.867,2.584-4.325,1.112-7.386.562l-2.111,2.249c0.59,1.114.315,1.016,0,2.249,1.731-.066,3.791-0.133,4.749.562a2.073,2.073,0,0,1,.527,1.124,16.331,16.331,0,0,1-5.276.562,1.87,1.87,0,0,0-1.055-.562l-1.055-1.124q0.262-1.405.527-2.811l-3.693-.562c0.511,2.263,1.1,1.554,1.583,3.935l-6.332.562c-0.63-.176-0.894-1.236-2.11-1.124-0.49.045-2.953,1.443-3.693,1.124-2.762-1.19-3.51-3.515-7.915-3.935,0.2-2.239,1.417-1.211,0-2.249V29.642l-3.165,1.124c0.826,1.011,1.037.72,1.583,2.249a1.777,1.777,0,0,0-1.056.562,62.373,62.373,0,0,1-6.859-2.811V29.642a3.255,3.255,0,0,0,2.111-.562H542.03c-0.891-.642-2.583-0.6-4.221-0.562-0.213-2.215.234-2.216-1.055-2.811-2.366-1.58-5.573.751-8.969,0-4.309-.953-21.048-3.87-27.964-2.249-2.318.543-5.258-.521-6.859,0.562,1.77,0.363,4.7.84,5.276,1.686l0.528,0.562-4.749.562c-1.523-1.608-2.785-1.47-3.165-4.5a2.073,2.073,0,0,0,.527-1.124,31.043,31.043,0,0,1-6.859-.562c-0.062-2.1.636-1.373-1.583-1.686-2.014-1.064-14.62-1.518-17.411.562,9.237,0.513,12.409,3.271,18.467,6.184v1.687c-5.375-.227-9.767-0.911-14.774,0a2.828,2.828,0,0,0-.527-1.687V25.144c-2.944-.367-3.442-0.669-6.332-0.562l-0.527.562v1.686c-5.192.27-9.28-1.192-9.5-6.184,1.144-1.209.838-.617,0.527-2.249-1.212.9-.8,1.2-2.638,1.686-0.92-1.1-1.074-1.574-2.11-1.124-1.663,1.691-1.714,2.279-5.276,2.249V20.085a2.989,2.989,0,0,0,.527-1.686l-6.331-.562a3.625,3.625,0,0,1,0,2.811l-1.583-.562V18.4c-1.686-.144-1.684-0.559-2.638,0-0.637,1.187-.255,1.254,0,2.811a2.456,2.456,0,0,0-1.583.562c4.694,0.016,1.832,1.177,4.221,2.249,2.222,1,13.173-1.972,14.773-2.249l0.528,1.686c-3.206,2-6.1,2.85-11.608,2.811v1.686c-2.507-.348-4.969.2-8.442,1.124a8.7,8.7,0,0,1-1.055-2.249,1.771,1.771,0,0,0,1.055-.562,6.356,6.356,0,0,1-2.11-.562c-0.5,3.346-.849,4.1-4.749,3.935-1.9-1.311-6.72-1.156-9.5-1.124V27.393h0.528c2.6-1.811,8.225-.115,11.607,0-0.623-1.49.474-1.009-1.055-1.687-1.56,1.026-6.08.55-8.442,0V24.02c1.631,0.053,3.34.079,4.221-.562h-1.583V21.771c1.889-.052,8.211,1.424,11.08-0.562-4.364-.094-21.913-2.068-23.215,0,2.164,0.486,3.247.888,3.694,3.373a1.777,1.777,0,0,0-1.056.562l0.528,0.562h1.583V21.771c1.737-.749,1.279-0.73,3.166-0.562V22.9l-1.583-.562c-0.116,3.016-1.043,6.194-3.694,6.746-1.512-.887-4.476-0.279-6.331-1.124l0.528-.562a6.075,6.075,0,0,1,1.582-1.124V25.706c-3.034-.831-4.713-1.442-5.8-4.5h0.527a3.4,3.4,0,0,1,2.111-.562,6.1,6.1,0,0,0,1.583,1.124c0.643-1.386.933-.867,1.582-2.249h-7.914c-1.607,1.874-3.98,3-6.331.562-1.33,1.442-4.6,2.534-7.914,1.686-6.374-1.631-16.647-2.463-21.1-1.124-1.517.455-4.171-1.675-5.276-2.249-0.7,1.3-1.139.858-2.111,1.686a2.981,2.981,0,0,1,.528,1.686c-1.73-.066-3.79-0.133-4.748.562L354.2,22.9v1.686c-2.838.017-3.838-.565-5.8-1.124-0.061-2.1.637-1.373-1.582-1.686-0.636.374-2.839-.764-4.221-1.124l-9.5.562V19.523c2.083-.014,6.93-0.048,8.441-1.124-3.308-.094-12.289-1.146-14.773.562,1.435,0,2.426.022,3.166,0.562a2.988,2.988,0,0,1,.528,1.686,5.824,5.824,0,0,1-3.694,1.124c-2.319-2.174-2.444-.007-5.8-1.124-1.062-.353-2.875-1.882-4.22-2.249l-16.357.562c-4.251-1.062-7.909-2.255-13.718-2.249v0.562c1.134,0.848,1.02.994,3.166,1.124,2.225,1.473,6.263.7,8.442,2.249H298.8v2.249c-1.762.084-2.591-.178-3.694,0.562l4.749,1.686c3.23,0.818,7.434-.373,10.025.562a7.512,7.512,0,0,1,.527-2.249h1.583v2.811a16.642,16.642,0,0,0,5.276,0,2.835,2.835,0,0,0-.527-1.686c-1.273.766-1.31,0.8-2.111-.562,1.564-.66.238-0.613,0-2.811,4.058,0.48,6.345,2.445,10.025,3.373l7.386-.562c0.426,0.123,1.685,1.542,2.111,1.686,1.8,0.61,4.429.265,5.8,0.562a8.378,8.378,0,0,1,.527,2.249l-0.527.562c-4.415-1.308-4.864.511-8.97,0.562-2.217.027-4.252-3.794-7.386-2.811l-1.583,1.686c-2.393.633-11.537-.43-15.829-0.562-3.032-.093-6.848,2.83-11.607,1.686a76.142,76.142,0,0,0-11.608-2.249l-0.527.562V30.2c-4.043-.723-3.16-3.522-7.387-3.935-0.865,1.254-.9,1.692-2.11,1.124h-0.528c0.273-1.24.6-1.115,0-2.249a2.59,2.59,0,0,0-1.583-.562,2.59,2.59,0,0,1-1.583.562v1.686a5.407,5.407,0,0,1-2.638,1.124c-1.019-2.176-1.568-2.344-4.748-2.249-3.731,3.257-6.622,2.516-11.08,3.373v0.562l2.638,1.124v1.686c-3.361-.147-4.676-1.049-8.442-1.124,0.207,1.649.863,0.7-.528,1.687-1.676-1.319-3.118-.856-5.276-0.562a2.835,2.835,0,0,0-.527-1.686c-1.369-1.145-3.9-1.792-5.8-2.249V30.2l-5.276-.562c-1.328.51-.852,2.653-3.166,2.249l-1.055-1.124-3.166.562-0.528-1.686-5.276.562c-1.192-.407-2.563-2.472-3.693-2.811l-5.276.562c-1.671-.566-2.825-1.875-5.276-2.249V23.458c-3.751-1.6-3.329-1.3-7.387-1.124a6.2,6.2,0,0,0-1.055-1.686c-0.976,1.35-1.5,1.531-3.694,1.686a9.329,9.329,0,0,1-.527-2.811h-15.3c-1.894-.63-2.955-1.618-6.331-1.686-1.315-2.7-1.655-4.019-5.8-3.935v0.562l3.166,0.562v1.686l-3.166-.562v0.562a3,3,0,0,1,.528,1.686c-6.957-.544-11.79-2.6-19-1.124v0.562c2.087,0.415,1.695.053,2.111,2.249l-0.528.562c-2.657-.528-3.859-1.249-7.386-1.124v1.686H132.6v2.249l-3.166-.562q-0.264-.843-0.527-1.687a3.978,3.978,0,0,1-.528-2.249h-4.221v1.686a26.108,26.108,0,0,1-5.8-1.124c-6.475-2.042-12.562,2.249-17.939-.562-2.794,1.817-5.353,2.272-10.552,2.249a2.83,2.83,0,0,1-.528,1.687,3.4,3.4,0,0,1-2.111.562c0-1.778.894-.756-0.528-1.686-3.077-2.095-14.684-.641-20.049-0.562-0.23,2.8-.2,2.3.528,4.5-2.777.447-3.041,1.726-5.8,2.249l-3.166-2.811c-1.651-2.658-.608-4.954-3.693-6.184A3.4,3.4,0,0,0,52.4,18.4c-0.532,2.419-1.914,4.494-4.221,5.059-0.778-1.429-1.118-1.128-3.166-1.124a12.508,12.508,0,0,1-1.055,2.811l-1.583-.562V23.458c1.015-1.065,1.539-2.244,1.055-3.373-0.917-.743-0.848-0.971-1.583-0.562-2.662,1.065-1.957,2.617-5.8,2.811-1.481-1.4-1.342-.3-4.221,0V20.647H23.912c-1.721-.635-1.536-1.872-4.221-2.249-1.4,1.7-2.708,3.441-5.276,3.935-1.236-1.359-.984-0.8-3.166-0.562-0.457-2.305-.833-3.46-3.166-3.935-0.857.84-1.068,0.872-2.638,1.124V17.274l2.11-.562V16.15H0.17c-1.161-.766-10.517-2.139-13.19-2.249V-1.277H1000v28.67h-4.749q-0.262-.843-0.527-1.687c-1.477-.526-4.619,1.567-7.387,1.124-3.127-.5-8.1-2.146-11.607-1.124-0.757.221-1.889,1.548-2.638,1.687l-10.025-1.687c-7.618-1.8-22.295.179-29.019,1.124-1.443.2-2.057-1.638-2.638-1.686-1.352-.113-4.649,1.19-6.859.562-2-.569-4.355-2.984-7.387-2.249h-0.527l-1.055,1.687c-6.748,2.9-23.186-5.064-30.074.562l1.055,1.124h-2.111c-2.838,1.681-7.527-2.43-10.024-3.373a8.512,8.512,0,0,1-.528,2.811c-1.585-.7-2.217-2.295-3.693-2.811-3.136-1.1-10.909-.128-12.663,1.124h7.387a1.964,1.964,0,0,0,.527,1.124v0.562H864.4c-5.944,3.773-21.924-6.825-25.853,1.124,1.3,0.728.359-.241,1.055,1.124l-0.528.562c-1.447-.6-4.346-2.033-6.331-1.686-0.723.126-1.772,1.615-3.166,1.124-1.8-.632-2.152-2.485-5.276-2.811-0.9,1.629-1.152,1.057-2.638.562-0.174,2.34.1,2.067-1.583,2.811-2.972,1.945-7.193-2.747-10.552-3.373v0.562c1.026,1.305,1.269,2.385,2.638,3.373,3.117,2.273,5.381.588,9.5,0l1.055,1.124v2.249c-2.576-.606-1.073-1.086-2.11-1.687-1.109-.643-3.648,2.047-4.221,2.249-1.223.431-1.414-.6-2.11-0.562l-0.528.562c1.217,1.4,1.394,3.036,3.166,3.935,2.345,1.546,5.077-.18,7.386,1.687a2.988,2.988,0,0,1,.528,1.686,1.771,1.771,0,0,0-1.055.562C818.682,40.406,814.691,39.448,810.059,39.76ZM556.8,32.452a3.69,3.69,0,0,0-2.11-1.124v1.124h2.11ZM784.733,30.2a2.652,2.652,0,0,0-3.166,0h3.166ZM581.074,27.955V26.831h-2.638v0.562l0.527,0.562h2.111Zm-148.26-.562a1.867,1.867,0,0,1,1.055-.562V26.269h-2.638v0.562Zm-110.8,0c-0.908-.5-0.675-0.5-1.583,0h1.583Zm-71.228-.562-0.528-1.686h-1.583v0.562A7.775,7.775,0,0,0,250.787,26.831Zm185.193-.562a1.87,1.87,0,0,0-1.056-.562A1.87,1.87,0,0,0,435.98,26.269Zm167.254-.562a8.672,8.672,0,0,1-1.056-2.249h-0.527a7.508,7.508,0,0,1-.528,2.249h2.111Zm252.2-.562,0.528-.562c1.015-.436-1.055-0.562-1.055-0.562l-0.528.562A1.87,1.87,0,0,0,855.433,25.144Zm-309.71,0c-0.907-.5-0.674-0.5-1.582,0h1.582ZM456.029,18.4c-0.166,2.031-.9,3.286-0.528,3.935,0.785,0.856,1.86,1.386,2.638,2.249a55.868,55.868,0,0,0,5.8-3.373h-0.527A11.191,11.191,0,0,0,456.029,18.4ZM289.83,24.02c0.859-.566,3.37-0.539,4.221-1.124l-5.276-.562v1.124ZM787.9,23.458a9.329,9.329,0,0,0,.527-2.811h-1.055a2.912,2.912,0,0,1-2.11,1.686V22.9A7.173,7.173,0,0,1,787.9,23.458Zm4.748-.562a6.363,6.363,0,0,0,2.111-.562l-0.528-1.124-1.583-.562V22.9Zm-255.893,0a2.827,2.827,0,0,0-.528-1.687,1.863,1.863,0,0,1-1.055.562v0.562Zm-254.31-.562a7.51,7.51,0,0,0-.528-2.249l-3.693.562-1.056,1.686H277.7c1.68-.631,6.433.86,8.969,0V21.771h-2.638A2.59,2.59,0,0,1,282.444,22.334Zm-11.08-.562c0.407-.383,1.177-0.737,1.583-1.124h-0.528a2.59,2.59,0,0,0-1.583-.562l-0.528,1.124ZM193.8,20.647a1.863,1.863,0,0,0-1.055-.562A1.87,1.87,0,0,0,193.8,20.647Zm64.369-.562c1.486-.956,5.387-0.142,6.859-1.124-3.025-.121-3.839-0.932-6.859-1.124a8.706,8.706,0,0,0-1.055,2.249h1.055Zm132.959-.562c-0.907-.5-0.674-0.5-1.583,0h1.583Zm-46.957,0c-0.908-.5-0.675-0.5-1.583,0h1.583Zm152.48-.562c-0.907-.5-0.674-0.5-1.583,0h1.583ZM192.221,17.274a1.863,1.863,0,0,0-1.055-.562A1.87,1.87,0,0,0,192.221,17.274Zm-3.165-.562c-1.234-1.358-1.294-.769-3.694-0.562,0.211-1.274.485-.609,0-1.686-1.136.543-1.78-.222-3.693-0.562-0.645,1.293-1.316,1.195-2.11,2.249l0.527,0.562h8.97Zm-11.608,0c-0.924-1.419-.97-1.483-3.166-1.686-0.531.389-2.109,0.724-2.638,1.124Zm-30.6-2.811c-0.72-.727-2.639-2.249-2.639-2.249a2.99,2.99,0,0,0-.527,1.687l0.527,0.562h2.639Zm29.018-1.124c-0.879-1.089-.759-0.938-2.638-1.124v0.562l0.528,0.562C175.023,12.567,174.566,12.423,175.865,12.777ZM146.319,9.4c0.454-.365,1.659-0.752,2.11-1.124h-2.638A1.97,1.97,0,0,0,146.319,9.4Z"/>
    </svg>',
	//'rectangle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none"><path d="M-1,0L1000-1V150H0Z"/></svg>',
	//'gradient_rectangle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 150" preserveAspectRatio="none"><path d="M-1,0L1000-1V150H0Z" fill="url(#fadeGrad)"/></svg>',
];

$mwContainer->list['list_icons'] = [
	'1' => VS_DIR . 'images/image_select/li1.png',
	'2' => VS_DIR . 'images/image_select/li2.png',
	'3' => VS_DIR . 'images/image_select/li3.png',
	'4' => VS_DIR . 'images/image_select/li4.png',
	'5' => VS_DIR . 'images/image_select/li5.png',
	'6' => VS_DIR . 'images/image_select/li6.png',
	'7' => VS_DIR . 'images/image_select/li7.png',
	'8' => VS_DIR . 'images/image_select/li8.png',

	//'9' => VS_DIR . 'images/image_select/li9.png',
	'10' => VS_DIR . 'images/image_select/li10.png',
	'11' => VS_DIR . 'images/image_select/li11.png',
	'12' => VS_DIR . 'images/image_select/li12.png',
	'13' => VS_DIR . 'images/image_select/li13.png',
	'14' => VS_DIR . 'images/image_select/li14.png',
	'15' => VS_DIR . 'images/image_select/li15.png',
	'16' => VS_DIR . 'images/image_select/li16.png',

	'17' => VS_DIR . 'images/image_select/li17.png',
	'18' => VS_DIR . 'images/image_select/li18.png',
	'19' => VS_DIR . 'images/image_select/li19.png',
	'20' => VS_DIR . 'images/image_select/li20.png',
	'21' => VS_DIR . 'images/image_select/li21.png',
	'22' => VS_DIR . 'images/image_select/li22.png',
	'23' => VS_DIR . 'images/image_select/li23.png',
	'24' => VS_DIR . 'images/image_select/li24.png',

	'25' => VS_DIR . 'images/image_select/li25.png',
	'26' => VS_DIR . 'images/image_select/li26.png',
	'27' => VS_DIR . 'images/image_select/li27.png',
	'28' => VS_DIR . 'images/image_select/li28.png',
	'29' => VS_DIR . 'images/image_select/li29.png',
	'30' => VS_DIR . 'images/image_select/li30.png',
	'31' => VS_DIR . 'images/image_select/li31.png',
	'32' => VS_DIR . 'images/image_select/li32.png',

	'33' => VS_DIR . 'images/image_select/li33.png',
	'34' => VS_DIR . 'images/image_select/li34.png',
	'35' => VS_DIR . 'images/image_select/li35.png',
	'36' => VS_DIR . 'images/image_select/li36.png',
	'37' => VS_DIR . 'images/image_select/li37.png',
	'38' => VS_DIR . 'images/image_select/li38.png',
	'39' => VS_DIR . 'images/image_select/li39.png',
	'40' => VS_DIR . 'images/image_select/li40.png',

	'41' => VS_DIR . 'images/image_select/li41.png',
	'42' => VS_DIR . 'images/image_select/li42.png',
	'43' => VS_DIR . 'images/image_select/li43.png',
	'44' => VS_DIR . 'images/image_select/li44.png',
	'45' => VS_DIR . 'images/image_select/li45.png',
	'46' => VS_DIR . 'images/image_select/li46.png',
];

$mwContainer->list['headers'] = [
	'type1' => [
		'thumb' => VS_DIR . 'images/image_select/header1.jpg',
		'type' => '1',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type1.php',
	],
	'type1b' => [
		'thumb' => VS_DIR . 'images/image_select/header1b.jpg',
		'type' => '1',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type1.php',
	],
	'type1c' => [
		'thumb' => VS_DIR . 'images/image_select/header1c.jpg',
		'type' => '1',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type1.php',
	],
	'type5' => [
		'thumb' => VS_DIR . 'images/image_select/header5.jpg',
		'type' => '1',
		'menu_type' => '6',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type1.php',
	],
	'type12' => [
		'thumb' => VS_DIR . 'images/image_select/header12.jpg',
		'type' => '3',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type2.php',
	],
	'type11' => [
		'thumb' => VS_DIR . 'images/image_select/header11.jpg',
		'type' => '2',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type2.php',
	],
	'type13' => [
		'thumb' => VS_DIR . 'images/image_select/header13.jpg',
		'type' => '3',
		'menu_type' => '7',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type2.php',
	],
	'type8' => [
		'thumb' => VS_DIR . 'images/image_select/header8.jpg',
		'type' => '2',
		'menu_type' => '6',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type2.php',
	],
	'type6' => [
		'thumb' => VS_DIR . 'images/image_select/header6.jpg',
		'type' => '2',
		'menu_type' => '8',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type2.php',
	],
	'type9' => [
		'thumb' => VS_DIR . 'images/image_select/header9.jpg',
		'type' => '2',
		'menu_type' => '7',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type2.php',
	],
	'type10' => [
		'thumb' => VS_DIR . 'images/image_select/header10.jpg',
		'type' => '2',
		'menu_type' => '7',
		'file' => get_template_directory() . '/library/visualeditor/templates/headers/header-type2.php',
	],

];

$mwContainer->list['footers'] = [
	'type1' => [
		'thumb' => VS_DIR . 'images/image_select/footer1.png',
		'file' => get_template_directory() . '/library/visualeditor/templates/footers/footer1.php',
	],
	'type2' => [
		'thumb' => VS_DIR . 'images/image_select/footer2.png',
		'file' => get_template_directory() . '/library/visualeditor/templates/footers/footer1.php',
	],
];
$mwContainer->list['iconsets'] = [
	'feather' => [
		'name' => 'Linecons',
		'icons' => ['activity', 'airplay', 'alert-circle', 'alert-octagon', 'alert-triangle', 'align-center', 'align-justify', 'align-left', 'align-right', 'anchor', 'aperture', 'archive', 'arrow-down-circle', 'arrow-down-left', 'arrow-down-right', 'arrow-down', 'arrow-left-circle', 'arrow-left', 'arrow-right-circle', 'arrow-right', 'arrow-up-circle', 'arrow-up-left', 'arrow-up-right', 'arrow-up', 'at-sign', 'award', 'bar-chart-2', 'bar-chart', 'battery-charging', 'battery', 'bell-off', 'bell', 'bluetooth', 'bold', 'book-open', 'book', 'bookmark', 'box', 'briefcase', 'calendar', 'camera-off', 'camera', 'cast', 'check-circle', 'check-square', 'check', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'chevrons-down', 'chevrons-left', 'chevrons-right', 'chevrons-up', 'chrome', 'circle', 'clipboard', 'clock', 'cloud-drizzle', 'cloud-lightning', 'cloud-off', 'cloud-rain', 'cloud-snow', 'cloud', 'code', 'codepen', 'command', 'compass', 'copy', 'corner-down-left', 'corner-down-right', 'corner-left-down', 'corner-left-up', 'corner-right-down', 'corner-right-up', 'corner-up-left', 'corner-up-right', 'cpu', 'credit-card', 'crop', 'crosshair', 'database', 'delete', 'disc', 'dollar-sign', 'download-cloud', 'download', 'droplet', 'edit-2', 'edit-3', 'edit', 'external-link', 'eye-off', 'eye', 'facebook', 'fast-forward', 'feather', 'file-minus', 'file-plus', 'file-text', 'file', 'film', 'filter', 'flag', 'folder-minus', 'folder-plus', 'folder', 'gift', 'git-branch', 'git-commit', 'git-merge', 'git-pull-request', 'github', 'gitlab', 'globe', 'grid', 'hard-drive', 'hash', 'headphones', 'heart', 'help-circle', 'home', 'image', 'inbox', 'info', 'instagram', 'italic', 'layers', 'layout', 'life-buoy', 'link-2', 'link', 'linkedin', 'list', 'loader', 'lock', 'log-in', 'log-out', 'mail', 'map-pin', 'map', 'maximize-2', 'maximize', 'menu', 'message-circle', 'message-square', 'mic-off', 'mic', 'minimize-2', 'minimize', 'minus-circle', 'minus-square', 'minus', 'monitor', 'moon', 'more-horizontal', 'more-vertical', 'move', 'music', 'navigation-2', 'navigation', 'octagon', 'package', 'paperclip', 'pause-circle', 'pause', 'percent', 'phone-call', 'phone-forwarded', 'phone-incoming', 'phone-missed', 'phone-off', 'phone-outgoing', 'phone', 'pie-chart', 'play-circle', 'play', 'plus-circle', 'plus-square', 'plus', 'pocket', 'power', 'printer', 'radio', 'refresh-ccw', 'refresh-cw', 'repeat', 'rewind', 'rotate-ccw', 'rotate-cw', 'rss', 'save', 'scissors', 'search', 'send', 'server', 'settings', 'share-2', 'share', 'shield-off', 'shield', 'shopping-bag', 'shopping-cart', 'shuffle', 'sidebar', 'skip-back', 'skip-forward', 'slack', 'slash', 'sliders', 'smartphone', 'speaker', 'square', 'star', 'stop-circle', 'sun', 'sunrise', 'sunset', 'tablet', 'tag', 'target', 'terminal', 'thermometer', 'thumbs-down', 'thumbs-up', 'toggle-left', 'toggle-right', 'trash-2', 'trash', 'trending-down', 'trending-up', 'triangle', 'truck', 'tv', 'twitter', 'type', 'umbrella', 'underline', 'unlock', 'upload-cloud', 'upload', 'user-check', 'user-minus', 'user-plus', 'user-x', 'user', 'users', 'video-off', 'video', 'voicemail', 'volume-1', 'volume-2', 'volume-x', 'volume', 'watch', 'wifi-off', 'wifi', 'wind', 'x-circle', 'x-square', 'x', 'youtube', 'zap-off', 'zap', 'zoom-in', 'zoom-out'],
	],
	'awesome' => [
		'name' => 'Awesome',
		'icons' => ['address', 'ambulance', 'anchor', 'android', 'apple', 'arrows-cw', 'asterisk', 'attach', 'attention-alt', 'attention', 'award', 'back-in-time', 'barcode', 'basket', 'battery', 'beaker-1', 'beer', 'bell-alt', 'bell', 'bitbucket', 'book-1', 'box', 'briefcase', 'bucket', 'bug', 'building', 'bullseye', 'calendar-empty', 'calendar', 'camera-alt', 'camera', 'cancel', 'certificate', 'chart-area', 'chart-bar-1', 'chart-line', 'chart-pie', 'chat-empty', 'chat', 'clock', 'cloud-1', 'code', 'coffee', 'cog-2', 'cog-alt', 'cog', 'comment-empty', 'comment', 'compass-1', 'compass', 'credit-card', 'cw', 'database-1', 'desktop', 'direction-1', 'direction', 'doc-inv', 'doc-text-inv', 'doc-text', 'doc', 'docs-1', 'docs', 'dollar', 'download-cloud', 'download', 'euro', 'extinguisher', 'eye-1', 'eye-off', 'facebook-squared', 'facebook', 'female', 'fighter-jet', 'filter', 'flag-checkered', 'flag-empty', 'flag', 'flash', 'flight', 'floppy-1', 'floppy', 'folder-empty', 'folder-open-empty', 'folder-open', 'folder', 'food', 'gamepad', 'gauge-1', 'gauge', 'gift', 'gittip', 'glass', 'globe', 'gplus-squared', 'gplus', 'graduation-cap', 'h-sigh', 'hammer', 'hdd', 'headphones', 'heart-1', 'heart-empty', 'help', 'home', 'hospital', 'html5', 'inbox-1', 'instagramm', 'key-1', 'keyboard', 'laptop', 'leaf', 'lifebuoy', 'lightbulb-1', 'link', 'linkedin-squared', 'linkedin', 'location-2', 'location', 'lock-1', 'lock-open-alt', 'lock-open', 'login', 'logout', 'magic', 'magnet', 'mail-1', 'mail-alt', 'male', 'map', 'medkit', 'megaphone', 'mic', 'mobile-1', 'mobile', 'money-1', 'monitor', 'moon', 'mouse', 'music-2', 'music', 'mute', 'note-beamed', 'note', 'off', 'ok', 'pencil-2', 'pencil-squared', 'pencil', 'phone-squared', 'phone', 'picture-1', 'picture', 'pin', 'play-circled', 'play-circled2', 'play', 'pound', 'print', 'progress-2', 'puzzle', 'qrcode', 'quote-left', 'quote-right', 'retweet', 'road', 'rocket', 'rss-squared', 'rss', 'scissors', 'search', 'shield', 'signal', 'sitemap', 'sort', 'star-1', 'star-empty', 'stethoscope', 'suitcase', 'sun', 'table', 'tablet', 'tag', 'tags', 'target', 'terminal', 'thermometer', 'thumbs-down-alt', 'thumbs-down', 'thumbs-up-alt', 'thumbs-up', 'ticket', 'tint', 'tools', 'trash-2', 'trash', 'truck-1', 'tumblr-squared', 'tumblr', 'twitter-squared', 'twitter', 'umbrella', 'upload-cloud', 'upload', 'user-2', 'user-add', 'user-md', 'user', 'users-1', 'users', 'vcard', 'video', 'videocam-1', 'volume-down', 'volume-off', 'volume-up', 'wallet', 'windows', 'wrench', 'youtube-play', 'youtube-squared', 'youtube', 'skype'],
	],
	'linecons' => [
		'name' => 'Linecons',
		'icons' => ['attach', 'beaker', 'calendar', 'camera', 'cd', 'clock', 'cloud', 'cog', 'comment', 'cup', 'database', 'desktop', 'diamond', 'doc', 'eye', 'fire', 'food', 'fork', 'globe', 'graduation-cap', 'heart', 'inbox', 'key', 'lightbulb', 'location', 'lock', 'mail', 'megaphone', 'mobile', 'money', 'music', 'note', 'paper-plane', 'params', 'pencil', 'photo', 'search', 'shop', 'sound', 'star', 't-shirt', 'tag', 'thumbs-up', 'trash', 'truck', 'tv', 'user', 'videocam'],
	],
	'hawcons' => [
		'name' => 'Linecons',
		'icons' => ['-cloud-sun-fog', 'air-sock', 'amused-face-closed-eyes', 'amused-face-closed-eyes2', 'amused-face', 'angry-face-eyebrows', 'angry-face-open-mouth-eyebrows', 'angry-face-teeth', 'angry-face', 'astonished-face', 'astonished-face2', 'award', 'award2', 'award3', 'award4', 'barometer', 'baseball-set', 'baseball', 'basketball-hoop', 'basketball', 'basketball2', 'billiard-ball', 'book-bookmark', 'book', 'bookmark-add', 'bookmark-remove', 'bookmark', 'bowling-ball', 'bowling-pin-ball', 'bowling-pins', 'box-bookmark', 'box-filled', 'box', 'box2', 'box3', 'boxing-glove', 'certificate', 'checkered-flag', 'clipboard-add', 'clipboard-checked', 'clipboard-download', 'clipboard-edit', 'clipboard-list', 'clipboard-move', 'clipboard-remove', 'clipboard-text', 'clipboard-upload', 'clipboard', 'cloud-add', 'cloud-download', 'cloud-error', 'cloud-error2', 'cloud-fog', 'cloud-lightning', 'cloud-moon-fog', 'cloud-moon-lightning', 'cloud-moon-rain', 'cloud-moon-raindrops', 'cloud-moon-snow', 'cloud-moon-snowflakes', 'cloud-moon', 'cloud-rain', 'cloud-raindrops', 'cloud-remove', 'cloud-snow', 'cloud-snowflakes', 'cloud-sun-lightning', 'cloud-sun-rain', 'cloud-sun-raindrops', 'cloud-sun-snow', 'cloud-sun-snowflakes', 'cloud-sun', 'cloud-upload', 'cloud-wind', 'cloud', 'cloud2', 'clouds', 'clouds2', 'combination-lock', 'compass-east', 'compass-north', 'compass-south', 'compass-west', 'compass', 'compass2', 'compass3', 'compass4', 'crescent', 'crescent2', 'degree-celsius', 'degree-fahrenheit', 'diving-goggles', 'document-add', 'document-bookmark', 'document-cancel', 'document-certificate', 'document-checked', 'document-cloud', 'document-code', 'document-diagrams', 'document-download', 'document-edit', 'document-error', 'document-font', 'document-forbidden', 'document-graph', 'document-information', 'document-list', 'document-locked', 'document-movie', 'document-music', 'document-play', 'document-recording', 'document-remove', 'document-scan', 'document-search', 'document-shred', 'document-star', 'document-table', 'document-text', 'document-text2', 'document-text3', 'document-time', 'document-unlocked', 'document-upload', 'document-zip', 'document', 'documents', 'documents2', 'eye-hidden', 'eye', 'face-closed-eyes-open-mouth', 'face-closed-eyes-open-mouth2', 'face-closed-eyes-open-mouth3', 'face-closed-meyes', 'face-glasses', 'face-missing-moth', 'face-moustache', 'face-moustache2', 'face-open-mouth-eyebrows', 'face-open-mouth-eyebrows2', 'face-open-mouth', 'face-open-mouth2', 'face-stuck-out-tongue', 'face-stuck-out-tongue2', 'face-sunglasses', 'fake-grinning-face-eyebrows', 'file-ai', 'file-app', 'file-html', 'file-jpg', 'file-mov', 'file-mp3', 'file-mp4', 'file-pdf', 'file-png', 'file-psd', 'file-txt', 'file-zip', 'flag', 'flag2', 'flag3', 'flag4', 'flashed-face-glasses', 'flashed-face', 'flashed-face2', 'folder-add', 'folder-bookmark', 'folder-cancel', 'folder-checked', 'folder-download', 'folder-error', 'folder-forbidden', 'folder-information', 'folder-locked', 'folder-remove', 'folder-search', 'folder-unlocked', 'folder-upload', 'folder', 'folder2', 'folders', 'football', 'football2', 'full-moon', 'gibbous-moon', 'gibbous-moon2', 'golf', 'grinning-face-eyebrows', 'grinning-face-eyebrows2', 'grinning-face-teeth', 'grinning-face-teeth2', 'grinning-face', 'grinning-face2', 'half-moon', 'half-moon2', 'high-five', 'hockey-stick', 'hockey-sticks', 'ice-skate', 'inbox-document-text', 'inbox-document', 'inbox-download', 'inbox-filled', 'inbox-upload', 'inbox', 'inboxes', 'information', 'information2', 'key', 'key2', 'kissing-face', 'kissing-face2', 'laughing-face', 'laughing-face2', 'laughing-face3', 'lightning', 'lock-open', 'lock-open2', 'lock-rounded-open', 'lock-rounded-open2', 'lock-rounded', 'lock-stripes', 'lock', 'mail--forbidden', 'mail-add', 'mail-cancel', 'mail-checked', 'mail-envelope-closed', 'mail-envelope-closed2', 'mail-envelope-open', 'mail-envelope-open2', 'mail-envelope-open3', 'mail-envelope-open4', 'mail-envelope', 'mail-error', 'mail-remove', 'medal', 'medal2', 'medal3', 'medal4', 'middle-finger', 'moon-stars', 'moon', 'moon2', 'moonrise', 'moonset', 'move', 'neutral-face-eyebrows', 'neutral-face', 'neutral-face2', 'note-add', 'note-checked', 'note-important', 'note-list', 'note-remove', 'note-text', 'note', 'notebook-list', 'notebook-text', 'notebook', 'notebook2', 'notebook3', 'notebook4', 'one-finger-click', 'one-finger-double-tap', 'one-finger-swipe-right', 'one-finger-swipe-right2', 'package', 'paperclip', 'pen-angled', 'pen', 'printer-text', 'printer-text2', 'printer', 'printer2', 'rainbow', 'raindrop', 'raindrops', 'rock-n-roll', 'sad-face--tightly-closed-eyes', 'sad-face-closed-eyes', 'sad-face-eyebrows', 'sad-face', 'sad-face2', 'sad-face3', 'sad-face4', 'sad-face5', 'sad-face6', 'sailing-boat-water', 'sailing-boat', 'search-minus', 'search-plus', 'search', 'shredder', 'shuttlecock', 'smiling-face-eyebrows', 'smiling-face', 'smiling-face2', 'smiling-face3', 'smiling-face4', 'smirking-face-sunglasses', 'smirking-face', 'snowflake', 'soccer-ball', 'soccer-court', 'soccer-shoe', 'sports-shoe', 'star', 'stars', 'stop-watch', 'stop-watch2', 'stubborn-face', 'sun', 'sunglasses', 'sunrise', 'sunset', 'sunset2', 'sunset3', 'table-tennis', 'tag-add', 'tag-cancel', 'tag-checked', 'tag-cord', 'tag-remove', 'tag', 'tags', 'target-arrow', 'target', 'tennis-ball', 'tennis-ball2', 'tennis-racket', 'thermometer-full', 'thermometer-half', 'thermometer-low', 'thermometer-quarter', 'thermometer-three-quarters', 'thermometer', 'thumb-down', 'thumb-finger-tap', 'thumb-up', 'tornado', 'trash-can', 'trash-can2', 'trophy-one', 'trophy', 'trophy2', 'two-fingers-rotate', 'two-fingers-swipe-down', 'umbrella', 'unamused-face-tightly-closed-eyes', 'volleyball-water', 'volleyball', 'warning', 'weights', 'whistle', 'wind-turbine', 'wind', 'winking-face', 'winking-face2', 'worried-face-eyebrows', 'worried-face-teeth', 'worried-face', 'zip'],
	],

];
$vePage->add_rows(
	[
		'tab' => __('Hlavička', 'cms_ve'),
		'id' => 'heads',
		'type' => 'template',
		'layouts' => [
			['title' => __('Hlavička s obsahem vlevo', 'cms_ve'), 'content' => 'header1', 'lite' => true],
			['title' => __('Hlavička s obsahem nahoře', 'cms_ve'), 'content' => 'header4', 'lite' => true],
			['title' => __('Hlavička s obsahem vpravo', 'cms_ve'), 'content' => 'header2', 'lite' => true],
			['title' => __('Hlavička s obsahem uprostřed', 'cms_ve'), 'content' => 'header3'],
			['title' => '', 'content' => 'header6', 'lite' => true],
			['title' => '', 'content' => 'header7', 'lite' => true],
			['title' => '', 'content' => 'header8', 'lite' => true],
			['title' => '', 'content' => 'header12', 'lite' => true],
			['title' => '', 'content' => 'header9', 'lite' => true],
			['title' => '', 'content' => 'header10'],
			['title' => '', 'content' => 'header11', 'lite' => true],
			['title' => '', 'content' => 'header13'],
			['title' => '', 'content' => 'header14', 'lite' => true],
			['title' => '', 'content' => 'header15', 'lite' => true],
			['title' => '', 'content' => 'header16', 'lite' => true],
			//array('title' => __('Hlavička s tučným textem', 'cms_ve'), 'content'=>'header5'),
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Obsah', 'cms_ve'),
		'id' => 'content',
		'type' => 'template',
		'layouts' => [
			['title' => __('Hlavička stránky s nadpisem uprostřed', 'cms_ve'), 'content' => 'page_title', 'lite' => true],
			['title' => __('Hlavička stránky s nadpisem vlevo', 'cms_ve'), 'content' => 'page_title2', 'lite' => true],
			['title' => __('Text s nadpisem', 'cms_ve'), 'content' => 'onecol', 'lite' => true],
			['title' => __('Dva sloupce', 'cms_ve'), 'content' => 'twocols2', 'lite' => true],
			['title' => __('Tři sloupce', 'cms_ve'), 'content' => 'threecols2', 'lite' => true],
			['title' => __('Dva sloupce s nadpisem', 'cms_ve'), 'content' => 'twocols', 'lite' => true],
			['title' => __('Tři sloupce s nadpisem', 'cms_ve'), 'content' => 'threecols', 'lite' => true],
			['title' => __('Důležitý nadpis', 'cms_ve'), 'content' => 'title', 'lite' => true],
			['title' => __('Nadpis nalevo', 'cms_ve'), 'content' => 'content1', 'lite' => true],
			['title' => __('Dva sloupce s obrázky', 'cms_ve'), 'content' => 'twocols_images', 'lite' => true],
			['title' => __('Tři sloupce s obrázky', 'cms_ve'), 'content' => 'threecols_images', 'lite' => true],
			['title' => '', 'content' => 'content2', 'lite' => true],
			['title' => __('Blok s obrázkem', 'cms_ve'), 'content' => 'image_row', 'lite' => true],
			['title' => '', 'content' => 'content15', 'lite' => true],
			['title' => '', 'content' => 'content16', 'lite' => true],
			['title' => '', 'content' => 'content11', 'lite' => true],
			['title' => '', 'content' => 'content12', 'lite' => true],
			//array('title' => __('Nadpis a text s obrázkem na levo', 'cms_ve'), 'content'=>'image_text1'),
			['title' => __('Text s obrázkem na pravo', 'cms_ve'), 'content' => 'image_text2', 'lite' => true],
			['title' => __('Text s obrázkem na levo', 'cms_ve'), 'content' => 'image_text3', 'lite' => true],
			['title' => '', 'content' => 'content3', 'lite' => true],
			['title' => '', 'content' => 'content4', 'lite' => true],
			['title' => '', 'content' => 'content5', 'lite' => true],
			['title' => '', 'content' => 'content6', 'lite' => true],
			['title' => '', 'content' => 'content7', 'lite' => true],
			['title' => '', 'content' => 'content8', 'lite' => true],
			['title' => '', 'content' => 'content9', 'lite' => true],
			['title' => '', 'content' => 'content10', 'lite' => true],
			['title' => '', 'content' => 'content13', 'lite' => true],
			['title' => '', 'content' => 'content14', 'lite' => true],


			['title' => __('Čísla', 'cms_ve'), 'content' => 'numbers'],
			['title' => __('FAQ', 'cms_ve'), 'content' => 'faq1'],
			['title' => __('FAQ', 'cms_ve'), 'content' => 'faq2'],

		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Slider', 'cms_ve'),
		'id' => 'sliders',
		'type' => 'template',
		'layouts' => [
			['title' => __('Slider', 'cms_ve'), 'content' => 'slider'],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Galerie', 'cms_ve'),
		'id' => 'gallery',
		'type' => 'template',
		'layouts' => [
			['title' => __('Galerie', 'cms_ve'), 'content' => 'gallery2', 'lite' => true],
			['title' => __('Galerie', 'cms_ve'), 'content' => 'gallery3', 'lite' => true],
			['title' => __('Galerie', 'cms_ve'), 'content' => 'gallery', 'lite' => true],
			['title' => __('Galerie', 'cms_ve'), 'content' => 'gallery4', 'lite' => true],
			['title' => __('Galerie', 'cms_ve'), 'content' => 'gallery5', 'lite' => true],
			['title' => __('Galerie', 'cms_ve'), 'content' => 'gallery6', 'lite' => true],
			['title' => __('Galerie', 'cms_ve'), 'content' => 'gallery7', 'lite' => true],
			['title' => __('Galerie', 'cms_ve'), 'content' => 'gallery8', 'lite' => true],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Výzva k akci', 'cms_ve'),
		'id' => 'magnet',
		'type' => 'template',
		'layouts' => [
			['title' => '', 'content' => 'cta1', 'lite' => true],
			['title' => '', 'content' => 'cta2', 'lite' => true],
			['title' => '', 'content' => 'cta3', 'lite' => true],
			['title' => '', 'content' => 'cta4'],
			['title' => '', 'content' => 'cta17', 'lite' => true],
			['title' => '', 'content' => 'cta18', 'lite' => true],
			['title' => '', 'content' => 'cta5', 'lite' => true],
			['title' => '', 'content' => 'cta19', 'lite' => true],
			['title' => '', 'content' => 'cta6', 'lite' => true],
			['title' => '', 'content' => 'cta7', 'lite' => true],
			['title' => '', 'content' => 'cta8', 'lite' => true],
			['title' => '', 'content' => 'cta9', 'lite' => true],
			['title' => '', 'content' => 'cta10', 'lite' => true],
			['title' => '', 'content' => 'cta11', 'lite' => true],
			['title' => '', 'content' => 'cta12', 'lite' => true],
			['title' => '', 'content' => 'cta13', 'lite' => true],
			['title' => '', 'content' => 'cta14', 'lite' => true],
			['title' => '', 'content' => 'cta15', 'lite' => true],
			['title' => '', 'content' => 'cta16', 'lite' => true],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('O nás', 'cms_ve'),
		'id' => 'aboutus',
		'type' => 'template',
		'layouts' => [
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus1', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus2', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus3', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus4', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus5', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus8', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus6', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus7', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus9', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus10', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus11', 'lite' => true],
			['title' => __('O nás', 'cms_ve'), 'content' => 'aboutus12', 'lite' => true],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Služby', 'cms_ve'),
		'id' => 'services',
		'type' => 'template',
		'layouts' => [
			['title' => __('Služby', 'cms_ve'), 'content' => 'service1', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service2', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service3', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service4', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service5', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service6', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service7', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service8', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service9', 'lite' => true],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service10'],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service11'],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service12'],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service13'],
			['title' => __('Služby', 'cms_ve'), 'content' => 'service14'],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Vlastnosti', 'cms_ve'),
		'id' => 'features',
		'type' => 'template',
		'layouts' => [
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features1', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features2', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features3', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features4', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features5', 'lite' => true],
			//array('title' => __('Vlastnosti', 'cms_ve'), 'content'=>'features6', 'lite'=>true),
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features7', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features8', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features9', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features10', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features11', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features12', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features13', 'lite' => true],
			['title' => __('Vlastnosti', 'cms_ve'), 'content' => 'features14', 'lite' => true],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Reference', 'cms_ve'),
		'id' => 'testimonials',
		'type' => 'template',
		'layouts' => [
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials1', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials2', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials3', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials4', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials5', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials6', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials7', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials9', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials10', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials11', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials12', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials8', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials14', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials13', 'lite' => true],
			['title' => __('Reference', 'cms_ve'), 'content' => 'testimonials15', 'lite' => true],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Lidé', 'cms_ve'),
		'id' => 'peoples',
		'type' => 'template',
		'layouts' => [
			['title' => __('Lidé', 'cms_ve'), 'content' => 'people5'],
			['title' => __('Lidé', 'cms_ve'), 'content' => 'people6'],
			['title' => __('Lidé', 'cms_ve'), 'content' => 'people7'],
			['title' => __('Lidé', 'cms_ve'), 'content' => 'people8'],
			['title' => __('Lidé', 'cms_ve'), 'content' => 'people9'],
			['title' => __('Lidé', 'cms_ve'), 'content' => 'people10'],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people4', 'lite' => true],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people1', 'lite' => true],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people2', 'lite' => true],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people11'],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people3', 'lite' => true],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people12'],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people13'],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people14', 'lite' => true],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people15', 'lite' => true],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people16', 'lite' => true],
			['title' => __('O mně', 'cms_ve'), 'content' => 'people17', 'lite' => true],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Ceník', 'cms_ve'),
		'id' => 'pricelist',
		'type' => 'template',
		'layouts' => [
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist1'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist2'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist3'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist4'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist5'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist6'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist7'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist8'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist9'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist10'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist11'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist12'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist13'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist15'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist16'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist17'],
			['title' => __('Ceník', 'cms_ve'), 'content' => 'pricelist18'],
		],
	]
);
$vePage->add_rows(
	[
		'tab' => __('Kontakt', 'cms_ve'),
		'id' => 'contact',
		'type' => 'template',
		'layouts' => [
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact1', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact2', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact3', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact4'],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact5'],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact6', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact7', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact8', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact9', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact10'],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact11', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact12'],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact13'],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact14'],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact15'],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact16', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact17', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact18', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact19', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact20', 'lite' => true],
			['title' => __('Kontakt', 'cms_ve'), 'content' => 'contact21'],
		],
	]
);
$mwContainer->empty_rows = [
	['title' => __('Jeden sloupec', 'cms_ve'), 'content' => 'one', 'thumb' => 'one'],
	['title' => __('Dva sloupce', 'cms_ve'), 'content' => 'two-two', 'thumb' => 'two'],
	['title' => __('Tři sloupce', 'cms_ve'), 'content' => 'three-three-three', 'thumb' => 'three'],
	['title' => __('Čtyři sloupce', 'cms_ve'), 'content' => 'four-four-four-four', 'thumb' => 'four'],
	['title' => __('Pět sloupců', 'cms_ve'), 'content' => 'five-five-five-five-five', 'thumb' => 'five'],
	['title' => '1/3 2/3', 'content' => 'three-twothree', 'thumb' => 'one', 'thumb' => 'one-two'],
	['title' => '2/3 1/3', 'content' => 'twothree-three', 'thumb' => 'one', 'thumb' => 'two-one'],

	['title' => '1/4 1/4 2/4', 'content' => 'four-four-twofour', 'thumb' => 'one', 'thumb' => 'one-one-two'],
	['title' => '2/4 1/4 1/4', 'content' => 'twofour-four-four', 'thumb' => 'one', 'thumb' => 'two-one-one'],
	['title' => '1/4 3/4', 'content' => 'four-threefour', 'thumb' => 'one', 'thumb' => 'one-three'],
	['title' => '3/4 1/4', 'content' => 'threefour-four', 'thumb' => 'one', 'thumb' => 'three-one'],
	['title' => '1/4 2/4 1/4', 'content' => 'four-twofour-four', 'thumb' => 'one', 'thumb' => 'one-two-one'],
	['title' => '1/5 1/5 1/5 2/5', 'content' => 'five-five-five-twofive', 'thumb' => 'one-one-one-two'],
	['title' => '2/5 1/5 1/5 1/5', 'content' => 'twofive-five-five-five', 'thumb' => 'two-one-one-one'],
	['title' => '1/5 1/5 3/5', 'content' => 'five-five-threefive', 'thumb' => 'one-one-three'],
	['title' => '3/5 1/5 1/5', 'content' => 'threefive-five-five', 'thumb' => 'three-one-one'],
	['title' => '1/5 4/5', 'content' => 'five-fourfive', 'thumb' => 'one-four'],
	['title' => '4/5 1/5', 'content' => 'fourfive-five', 'thumb' => 'four-one'],
	['title' => '1/5 3/5 1/5', 'content' => 'five-threefive-five', 'thumb' => 'one-three-one'],
	['title' => '2/5 3/5', 'content' => 'twofive-threefive', 'thumb' => 'two-three'],
	['title' => '3/5 2/5', 'content' => 'threefive-twofive', 'thumb' => 'three-two'],

	['title' => '1 - 1/2 1/2', 'content' => 'one/-two-two', 'thumb' => 'one-two-two'],
	['title' => '1 - 1/3 1/3 1/3', 'content' => 'one/-three-three-three', 'thumb' => 'one-three-three-three'],
	['title' => '1 - 2/3 1/3', 'content' => 'one/-twothree-three', 'thumb' => 'one-twothree-three'],
	['title' => '1 - 1/3 2/3', 'content' => 'one/-three-twothree', 'thumb' => 'one-three-twothree'],
];

$mwContainer->element_config = [
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'margin_top',
				'title' => __('Horní odsazení', 'cms_ve'),
				'type' => 'slider',
				'setting' => [
					'min' => '0',
					'max' => '200',
					'placeholder' => '0',
					'unit' => 'px',
				],
				'onedit' => [
					'action' => 'change_element_top_padding',
					'target' => ' > .element_content',
				],
				'formobile' => true,
				'content' => '',
			],
			[
				'id' => 'margin_bottom',
				'title' => __('Spodní odsazení', 'cms_ve'),
				'type' => 'slider',
				'setting' => [
					'min' => '0',
					'max' => '200',
					'placeholder' => '30',
					'default' => '30',
					'unit' => 'px',
				],
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'padding-bottom',
					'target' => ' > .element_content',
				],
				'formobile' => true,
				'content' => '',
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'max_width',
				'title' => __('Maximální šířka', 'cms_ve'),
				'type' => 'slider',
				'setting' => [
					'min' => '100',
					'max' => '1000',
					'default' => '1000',
					'unit' => 'px',
				],
				'formobile' => true,
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'max-width',
					'target' => ' > .element_content',
				],
				'show' => 'row_max_width',
				'content' => '',
			],
			[
				'name' => __('Zarovnání elementu', 'cms_ve'),
				'id' => 'element_align',
				'type' => 'imageoption',
				'options' => [
					'left' => [
						'icon' => 'onleft',
						'text' => __('Nalevo', 'cms_ve'),
					],
					'center' => [
						'icon' => 'oncenter',
						'text' => __('Na střed', 'cms_ve'),
					],
					'right' => [
						'icon' => 'onright',
						'text' => __('Napravo', 'cms_ve'),
					],
				],
				'onedit' => [
					'action' => 'change_class',
					'target' => ' .element_content',
					'class' => 'element_align_',
				],
				'show_group' => 'row_max_width',
				'show_val' => '1',
				'content' => 'center',
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'animate',
				'title' => __('Animace', 'cms_ve'),
				'type' => 'select',
				'content' => '',
				'options' => [
					['name' => __('Bez animace', 'cms_ve'), 'value' => ''],

					['name' => __('Odskočení svrchu', 'cms_ve'), 'value' => 'bounceInDown'],
					['name' => __('Odskočení zleva', 'cms_ve'), 'value' => 'bounceInLeft'],
					['name' => __('Odskočení zprava', 'cms_ve'), 'value' => 'bounceInRight'],
					['name' => __('Odskočení zezdola', 'cms_ve'), 'value' => 'bounceInUp'],

					['name' => __('Objevení', 'cms_ve'), 'value' => 'fadeIn'],
					['name' => __('Objevení svrchu', 'cms_ve'), 'value' => 'fadeInDown'],
					['name' => __('Objevení zleva', 'cms_ve'), 'value' => 'fadeInLeft'],
					['name' => __('Objevení zprava', 'cms_ve'), 'value' => 'fadeInRight'],
					['name' => __('Objevení zezdola', 'cms_ve'), 'value' => 'fadeInUp'],

					['name' => __('Otočení X', 'cms_ve'), 'value' => 'flipInX'],
					['name' => __('Otočení Y', 'cms_ve'), 'value' => 'flipInY'],

					['name' => __('Zoom', 'cms_ve'), 'value' => 'zoomIn'],
					['name' => __('Zoom svrchu', 'cms_ve'), 'value' => 'zoomInDown'],
					['name' => __('Zoom zleva', 'cms_ve'), 'value' => 'zoomInLeft'],
					['name' => __('Zoom zprava', 'cms_ve'), 'value' => 'zoomInRight'],
					['name' => __('Zoom zezdola', 'cms_ve'), 'value' => 'zoomInUp'],

					['name' => __('Odskočení', 'cms_ve'), 'value' => 'bounce'],
					['name' => __('Přiskočení', 'cms_ve'), 'value' => 'bounceIn'],
					['name' => __('Pulzování', 'cms_ve'), 'value' => 'pulse'],
					['name' => __('Roztažení a smrsknutí', 'cms_ve'), 'value' => 'rubberBand'],
					['name' => __('Zatřesení', 'cms_ve'), 'value' => 'shake'],
					['name' => __('Zahoupání', 'cms_ve'), 'value' => 'swing'],
					['name' => __('Tadá', 'cms_ve'), 'value' => 'tada'],
					['name' => __('Rozviklání', 'cms_ve'), 'value' => 'wobble'],
					['name' => __('Přijetí', 'cms_ve'), 'value' => 'lightSpeedIn'],

				],
				'onedit' => [
					'action' => 'change_animation',
				],
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'id',
				'title' => __('Kotva (id) elementu', 'cms_ve'),
				'type' => 'text',
				'tooltip' => __('Nastavení se projeví po reloadu stránky. Kotva nesmí začínat číslovkou!', 'cms_ve'),
			],
			[
				'id' => 'class',
				'title' => __('Vlastní css třída elementu', 'cms_ve'),
				'type' => 'text',
				'tooltip' => __('Nastavení se projeví po reloadu stránky.', 'cms_ve'),
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'delay',
				'title' => __('Zobrazit se zpožděním', 'cms_ve'),
				'type' => 'size',
				'unit' => 's',
				'content' => '',
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'mobile_visibility',
				'type' => 'switch',
				'label' => __('Skrýt na mobilu', 'cms_ve'),
				'onedit' => [
					'action' => 'toggle_class',
					'class' => 'hide_on_mobile',
				],
			],
			[
				'id' => 'tablet_visibility',
				'type' => 'switch',
				'label' => __('Skrýt na tabletu', 'cms_ve'),
				'onedit' => [
					'action' => 'toggle_class',
					'class' => 'hide_on_tablet',
				],
			],
			[
				'id' => 'desktop_visibility',
				'type' => 'switch',
				'label' => __('Skrýt na počítači', 'cms_ve'),
				'onedit' => [
					'action' => 'toggle_class',
					'class' => 'hide_on_desktop',
				],
			],
		],
	],
];


require_once('elements.php');


// Nastavení stránek
//***********************************************************************************
/*
mwSetting()->addObjectSetting([
	'id' => 'change_name_set',
	'title' => __('Název', 'cms_ve'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'name' => __('Název', 'cms_ve'),
					'type' => 'post_title',
					'slug' => false,
				],
			]
		],

	]
], ['cms_popup','cms_footer','ve_header','mw_slider','weditor','ve_elvar']);*/

mwSetting()->addObjectSetting([
	'id' => 'change_name',
	'title' => __('Název', 'cms_ve'),
	'action' => 've_change_weditor_title',
], ['cms_popup', 'cms_footer', 've_header', 'mw_slider', 'weditor', 've_elvar']);

mwSetting()->addObjectFastSetting([
	'fields' => [
		[
			'id' => 'post_title',
			'name' => __('Název', 'cms_ve'),
			'type' => 'text',
		],
	],

], ['cms_popup']);

mwSetting()->addObjectSetting([
	'id' => 've_popup',
	'title' => __('Nastavení pop-upu', 'cms_ve'),
	'inpanel' => [
		'reload' => 'popup_body',
		'target' => 'body',
	],
	'fields' => [
		[
			'id' => 'appearance_setting',
			'type' => 'toggle_group',
			'open' => true,
			'title' => __('Vzhled', 'cms_ve'),
			'setting' => [
				[
					'name' => __('Maximální šířka pop-upu', 'cms_ve'),
					'id' => 'width',
					'type' => 'slider',
					'setting' => [
						'min' => '200',
						'max' => '1200',
						'unit' => 'px',
					],
					'content' => [
						'size' => '800',
						'unit' => 'px',
					],
					'onedit' => [
						'action' => 'change_styles',
						'css' => 'max-width',
						'target' => '.cms_popup_content_container .visual_content',
					],
				],
				[
					'name' => __('Pozadí za pop-upem', 'cms_ve'),
					'id' => 'background',
					'type' => 'color',
					'content' => '#000000',
					'onedit' => [
						'action' => 'change_styles',
						'css' => 'background-color',
						'target' => 'body',
					],
				],
				[
					'id' => 'corner',
					'name' => __('Míra zakulacení rohů (v px)', 'cms_ve'),
					'type' => 'slider',
					'setting' => [
						'min' => '0',
						'max' => '30',
						'unit' => 'px',
					],
					'content' => '0',
					'onedit' => [
						'action' => 'change_styles',
						'css' => '--popup-rounded-corners',
						'target' => ':root',
					],
				],
			],
		],
		[
			'id' => 'show_setting',
			'type' => 'toggle_group',
			'open' => true,
			'title' => __('Možnosti zobrazení', 'cms_ve'),
			'setting' => [
				[
					'id' => 'delay',
					'name' => __('Znovu zobrazit po x dnech', 'cms_ve'),
					'type' => 'text',
					'content' => '2',
					'tooltip' => __('Pokud se návštěvníkovi pop-up zobrazí a on jej zavře, tak se mu při další návštěvě znovu zobrazí až po x dnech.', 'cms_ve'),
				],
			],
		],
	],
], ['cms_popup']);

mwSetting()->addObjectSetting([
	'id' => 'change_template',
	'title' => __('Šablona', 'cms_ve'),
	'action' => 'mw_change_template',
], ['cms_popup', 'cms_footer', 've_header', 'mw_slider']);

$global_appearance = get_option('ve_appearance');
$default_li = $global_appearance['li'] ?? '';

MW()->container['page_appearance'] = [
	'id' => 've_appearance',
	'exclude_modules' => ['blog'],
	'category' => 'appearance',
	'inpanel' => [
		'reload' => 'body',
		'target' => 'body',
	],
	'title' => __('Pozadí a formátování', 'cms_ve'),
	'fields' => [
		[
			'id' => 'use_page_background',
			'type' => 'toggle_group',
			'checkbox' => true,
			'title' => __('Vlastní pozadí', 'cms_ve'),
			'action' => 'reload',
			'setting' => [
				[
					'name' => __('Barva pozadí', 'cms_ve'),
					'id' => 'background_color',
					'type' => 'color',
					'onedit' => [
						'action' => 'change_styles',
						'css' => 'background-color',
						'target' => 'body',
					],
					'content' => '#ffffff',
				],
				[
					'type' => 'tabs',
					'id' => 'background_setting',
					'content' => 'image',
					'onedit' => [
						'action' => 'reload',
						'target' => '.body_background_container',
					],
					'tabs' => [
						'image' => [
							'name' => __('Obrázek', 'cms_ve'),
							'icon' => 'image',
							'setting' => [
								[
									'id' => 'background_image',
									'type' => 'bgimage',
									'mobile' => true,
									'content' => [
										'pattern' => '',
										'efect' => 'fixed',
										'cover' => '1',
										'overlay_color' => [
											'color' => '#000000',
											'transparency' => '0.2',
											'rgba' => 'rgba(0, 0, 0, 0.2)',
										],
									],
									'hide' => ['paralax'],
									'onedit' => [
										'action' => 'change_background',
										'target' => '.body_background_container',
									],
								],
							],
						],
						'slider' => [
							'name' => __('Slider', 'cms_ve'),
							'icon' => 'code',
							'setting' => [
								[
									'id' => 'background_slides',
									'title' => __('Obrázky slidů', 'cms_ve'),
									'type' => 'image_gallery',
									'onedit' => [
										'action' => 'reload_body_background',
										'target' => '.body_background_container',
									],
								],
								[
									'id' => 'slider_overlay_color',
									'title' => __('Překrytí slideru barvou', 'cms_ve'),
									'type' => 'transparent_color',
									'content' => [
										'color' => '',
										'transparency' => '0.7',
									],
									'onedit' => [
										'action' => 'change_css',
										'css' => 'background',
										'target' => '.body_background_container .background_overlay',
									],
								],
							],
						],
						'video' => [
							'name' => __('Video', 'cms_ve'),
							'icon' => 'film',
							'setting' => [
								[
									'name' => __('Použít', 'cms_ve'),
									'id' => 'video_type',
									'type' => 'select',
									'content' => 'iframe',
									'options' => [
										['name' => __('Youtube/Vimeo', 'cms_ve'), 'value' => 'iframe'],
										['name' => __('Vlastní soubor videa', 'cms_ve'), 'value' => 'custom'],
									],
									'onedit' => [
										'action' => 'reload_body_background',
										'target' => '.body_background_container',
									],
									'show' => 'video_type',
								],
								[
									'name' => __('Odkaz na video', 'cms_ve'),
									'id' => 'video_url',
									'type' => 'text',
									'content' => '',
									'onedit' => [
										'action' => 'reload_body_background',
										'target' => '.body_background_container',
									],
									'show_group' => 'video_type',
									'show_val' => 'iframe',
								],
								[
									'name' => __('Video ve formátu .mp4', 'cms_ve'),
									'id' => 'background_video_mp4',
									'type' => 'upload_file',
									'show_group' => 'video_type',
									'show_val' => 'custom',
									'onedit' => [
										'action' => 'reload_body_background',
										'target' => '.body_background_container',
									],
								],
								[
									'name' => __('Video ve formátu .webm', 'cms_ve'),
									'id' => 'background_video_webm',
									'type' => 'upload_file',
									'show_group' => 'video_type',
									'show_val' => 'custom',
									'onedit' => [
										'action' => 'reload_body_background',
										'target' => '.body_background_container',
									],
								],
								[
									'name' => __('Video ve formátu .ogg', 'cms_ve'),
									'id' => 'background_video_ogg',
									'type' => 'upload_file',
									'show_group' => 'video_type',
									'show_val' => 'custom',
									'onedit' => [
										'action' => 'reload_body_background',
										'target' => '.body_background_container',
									],
								],
								[
									'id' => 'show_mobile',
									'label' => __('Zobrazit na mobilech', 'cms_ve'),
									'type' => 'switch',
									'show' => 'show_on_mobile',
									'onedit' => [
										'action' => 'reload_body_background',
										'target' => '.body_background_container',
									],
								],
								[
									'id' => 'video_image',
									'title' => __('Zástupný obrázek pro mobily', 'cms_ve'),
									'type' => 'bgimage',
									'respect_size' => false,
									'hide' => ['efect', 'cover', 'repeat', 'color_filter'],
									'content' => [
										'cover' => 1,
										'position' => '50% 50%',
									],
									'show_group' => 'show_on_mobile',
									'show_val' => '0',
									'onedit' => [
										'action' => 'change_background',
										'target' => '.body_background_container',
									],
								],
								[
									'id' => 'video_overlay_color',
									'title' => __('Překrýt video barvou', 'cms_ve'),
									'type' => 'transparent_color',
									'content' => [
										'color' => '',
										'transparency' => '0.7',
										'rgba' => '',
									],
									'onedit' => [
										'action' => 'change_css',
										'css' => 'background',
										'target' => '.body_background_container .background_overlay',
									],
								],
							],
						],
					],
				],
			],
		],

	],
];

if (!mw_is_lite_editor()) {
	MW()->container['page_appearance']['fields'][] = [
		'id' => 'text_setting',
		'type' => 'toggle_group',
		'title' => __('Formátování textů', 'cms_ve'),
		'setting' => [
			[
				'title' => __('Font nadpisů', 'cms_ve'),
				'id' => 'title_font',
				'type' => 'font',
				'content' => [
					'font-family' => '',
					'weight' => '',
					'color' => '',
					'line-height' => '',
					'capitals' => '',
				],
				'onedit' => [
					'action' => 'change_variable_font',
					'css' => '--page-title-font',
				],
			],
			[
				'title' => __('Font podnadpisů', 'cms_ve'),
				'id' => 'subtitle_font',
				'type' => 'font',
				'content' => [
					'font-family' => '',
					'weight' => '',
					'color' => '',
					'line-height' => '',
					'capitals' => '',
				],
				'onedit' => [
					'action' => 'change_variable_font',
					'css' => '--page-subtitle-font',
				],
			],
			[
				'title' => __('Font stránky', 'cms_ve'),
				'id' => 'font',
				'type' => 'font',
				'content' => [
					'font-size' => '',
					'font-family' => '',
					'weight' => '',
					'line-height' => '',
					'color' => '',
				],
				'setting' => [
					'max_font_size' => '25',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => 'body',
					'setting' => 'variable_color',
					'css' => '--page-text-color',
				],
			],
			[
				'name' => __('Barva inverzních textů', 'cms_ve'),
				'id' => 'inverse_text_color',
				'type' => 'color',
				'content' => '',
				'onedit' => [
					'action' => 'change_styles',
					'target' => ':root',
					'css' => '--page-text-inverse-color',
				],
			],
			[
				'name' => __('Barva odkazů', 'cms_ve'),
				'id' => 'link_color',
				'type' => 'color',
				'content' => '',
				'onedit' => [
					'target' => ':root',
					'action' => 'change_styles',
					'css' => '--page-link-color',
				],
			],
			[
				'name' => __('Barva odkazů po najetí myši', 'cms_ve'),
				'id' => 'hover_color',
				'type' => 'color',
				'content' => '',
				'onedit' => [
					'action' => 'change_styles',
					'target' => ':root',
					'css' => '--page-link-hover-color',
				],
			],
		],
	];
	MW()->container['page_appearance']['fields'][] = [
		'id' => 'element_text_setting',
		'type' => 'toggle_group',
		'title' => __('Texty v textovém elementu', 'cms_ve'),
		'setting' => [
			[
				'title' => __('Nadpis 1 (H1)', 'cms_ve'),
				'id' => 'h1_font',
				'type' => 'font',
				'content' => [
					'font-size' => '',
					'color' => '',
				],
				'setting' => [
					'max_font_size' => '60',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => '.entry_content h1',
				],
			],
			[
				'title' => __('Nadpis 2 (H2)', 'cms_ve'),
				'id' => 'h2_font',
				'type' => 'font',
				'content' => [
					'font-size' => '',
					'color' => '',
				],
				'setting' => [
					'max_font_size' => '60',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => '.entry_content h2',
				],
			],
			[
				'title' => __('Nadpis 3 (H3)', 'cms_ve'),
				'id' => 'h3_font',
				'type' => 'font',
				'content' => [
					'font-size' => '',
					'color' => '',
				],
				'setting' => [
					'max_font_size' => '60',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => '.entry_content h3',
				],
			],
			[
				'title' => __('Nadpis 4 (H4)', 'cms_ve'),
				'id' => 'h4_font',
				'type' => 'font',
				'content' => [
					'font-size' => '',
					'color' => '',
				],
				'setting' => [
					'max_font_size' => '60',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => '.entry_content h4',
				],
			],
			[
				'title' => __('Nadpis 5 (H5)', 'cms_ve'),
				'id' => 'h5_font',
				'type' => 'font',
				'content' => [
					'font-size' => '',
					'color' => '',
				],
				'setting' => [
					'max_font_size' => '60',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => '.entry_content h5',
				],
			],
			[
				'title' => __('Nadpis 6 (H6)', 'cms_ve'),
				'id' => 'h6_font',
				'type' => 'font',
				'content' => [
					'font-size' => '',
					'color' => '',
				],
				'setting' => [
					'max_font_size' => '60',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => '.entry_content h6',
				],
			],
			[
				'id' => 'li',
				'name' => __('Styl odrážek', 'cms_ve'),
				'type' => 'imageselect',
				'content' => '',
				'list' => 'list_icons',
				'empty' => ['' => VS_DIR . 'images/image_select/li0.png'],
				'onedit' => [
					'action' => 'change_class_default',
					'class' => 've_list_style',
					'target' => 'body',
					'setting' => $default_li,
				],
			],
		],
	];
}

MW()->container['page_appearance']['fields'][] = [
	'id' => 'page_advanced_setting',
	'type' => 'toggle_group',
	'title' => __('Pokročilé', 'cms_ve'),
	'setting' => [
		[
			'title' => __('Šířka stránky', 'cms_ve'),
			'id' => 'page_width_preset',
			'type' => 'select',
			'options' => [
				['value' => '', 'name' => __('Defaultí', 'cms_ve')],
				['value' => '800px', 'name' => __('Užší (800px)', 'cms_ve')],
				['value' => '970px', 'name' => __('Klasická (970px)', 'cms_ve')],
				['value' => '1024px', 'name' => __('Širší (1024px)', 'cms_ve')],
				['value' => '1200px', 'name' => __('Široká (1200px)', 'cms_ve')],
				['value' => '90%', 'name' => __('Přes celou šířku (90%)', 'cms_ve')],
				['value' => 'custom', 'name' => __('Vlastní', 'cms_ve')],
			],
			'content' => '',
			'show' => 'page_width',
			'onedit' => [
				'action' => 'change_styles',
				'css' => '--page-width',
				'target' => ':root',
			],
		],
		[
			'name' => __('Vlastní šířka stránky', 'cms_ve'),
			'id' => 'page_width',
			'type' => 'slider',
			'setting' => [
				'min' => '350',
				'max' => '1250',
				'default' => '970',
				'unit' => ['px', '%'],
			],
			'content' => [
				'size' => '',
				'unit' => 'px',
			],
			'show_group' => 'page_width',
			'show_val' => 'custom',
			'onedit' => [
				'action' => 'change_styles',
				'css' => '--page-width',
				'target' => ':root',
			],
		],
		[
			'label' => __('Omezit obsah šířkou', 'cms_ve'),
			'id' => 'narrow_content',
			'type' => 'switch',
			'onedit' => [
				'action' => 'toggle_class',
				'class' => 'fixed_width_page',
				'target' => 'body',
			],
		],
	],
];

mwSetting()->addObjectSetting(MW()->container['page_appearance'], ['page']);

$page_header_setting = MW()->container['header_setting'];
if (MW()->is_module_active('shop')) {
	$page_header_setting[3]['setting'][] = [
		'id' => 'hide_cart',
		'type' => 'switch',
		'label' => __('Skrýt košík na této stránce', 'cms_ve'),
		'onedit' => [
			'action' => 'reload',
		],
	];
}

mwSetting()->addObjectSetting([
	'id' => 've_header',
	'exclude_modules' => ['blog'],
	'category' => 'appearance',
	'inpanel' => [
		'reload' => 'header',
		'target' => '#header',
	],
	'title' => __('Hlavička stránky', 'cms_ve'),
	'switch' => [
		'id' => 'show',
		'label' => __('Použít na této stránce', 'cms_ve'),
		'options' => [
			'global' => [
				'name' => __('Globální hlavičku', 'cms_ve'),
				'option' => 've_header',
			],
			'page' => [
				'name' => __('Vlastní hlavičku', 'cms_ve'),
				'option' => 've_header',
			],
			'none' => [
				'name' => __('Bez hlavičky', 'cms_ve'),
			],
		],
		'content' => 'global',
	],
	'fields' => [
		[
			'id' => 'header_group',
			'type' => 'group',
			'show_group' => 'use_header',
			'show_val' => 'page',
			'setting' => $page_header_setting,
		],

	],
], ['page']);

mwSetting()->addObjectSetting([
	'id' => 've_footer',
	'exclude_modules' => ['blog'],
	'category' => 'appearance',
	'inpanel' => [
		'reload' => 'footer',
		'target' => '#footer',
	],
	'title' => __('Patička stránky', 'cms_ve'),
	'switch' => [
		'id' => 'show',
		'label' => __('Použít na této stránce', 'cms_ve'),
		'options' => [
			'global' => [
				'name' => __('Globální patičku', 'cms_ve'),
				'option' => 've_footer',
			],
			'page' => [
				'name' => __('Vlastní patičku', 'cms_ve'),
				'option' => 've_footer',
			],
			'none' => [
				'name' => __('Bez patičky', 'cms_ve'),
			],
		],
		'content' => 'global',
	],
	'fields' => [
		[
			'id' => 'footer_group',
			'type' => 'group',
			'setting' => MW()->container['footer_setting'],
		],
	],
], ['page']);

mwSetting()->addObjectSetting([
	'id' => 'change_template',
	'category' => 'appearance',
	'exclude_modules' => ['eshop', 'blog'],
	'hide_in_wp' => 1,
	'title' => __('Šablona stránky', 'cms_ve'),
	'action' => 'mw_change_template',
], ['page']);


// Nastavení
//***********************************************************************************


$mwContainer->row_setting['slide_set'] = [
	[
		'id' => 'background_color',
		'title' => __('Barva pozadí', 'cms_ve'),
		'type' => 'background',
		'content' => [
			'transparency' => '1',
			'gradient' => '0',
		],
		'onedit' => [
			'action' => 'change_css',
		],
	],
	[
		'id' => 'background_image',
		'type' => 'bgimage',
		'mobile' => true,
		'hide' => ['efect'],
		'content' => [
			'pattern' => '',
			'cover' => '1',
		],
		'onedit' => [
			'action' => 'change_background',
			'target' => ' .row_background_container',
		],
	],
	[
		'id' => 'text',
		'title' => __('Texty', 'cms_ve'),
		'type' => 'select',
		'options' => [
			['name' => __('Automatické', 'cms_ve'), 'value' => 'auto'],
			['name' => __('Defaultní', 'cms_ve'), 'value' => 'default'],
			['name' => __('Inverzní', 'cms_ve'), 'value' => 'invers'],
			['name' => __('Vlastní', 'cms_ve'), 'value' => 'custom'],
		],
		'content' => 'auto',
		'onedit' => [
			'action' => 'change_class',
			'class' => 'row_text_',
		],
		'show' => 'text_setting',
	],
	[
		'id' => 'font',
		'type' => 'font',
		'title' => __('Font bloku', 'cms_ve'),
		'content' => [
			'font-size' => '',
			'font-family' => '',
			'weight' => '',
			'color' => '',
		],
		'setting' => [
			'max_font_size' => '30',
		],
		'onedit' => [
			'action' => 'change_font',
			'target' => '.row_text_custom',
			'setting' => 'variable_color_row',
			'css' => '--font-row-color-',
		],
		'show_group' => 'text_setting',
		'show_val' => 'custom',
	],
	[
		'title' => __('Barva odkazů', 'cms_ve'),
		'id' => 'link_color',
		'type' => 'color',
		'onedit' => [
			'action' => 'change_styles',
			'css' => 'color',
			'target' => '.row_text_custom a:not(.ve_content_button)',
		],
		'show_group' => 'text_setting',
		'show_val' => 'custom',
	],

];
$mwContainer->row_setting['slider'] = [
	[
		'id' => 'slides',
		'type' => 'multielement',
		'texts' => [
			'add' => __('Přidat slide', 'cms_ve'),
			'empty' => __('Slide', 'cms_ve'),
		],
		'onedit' => [
			'action' => 'reload',
		],
		'setting' => [
			[
				'id' => 'slider_content',
				'title' => __('Obsah', 'cms_ve'),
				'type' => 'weditor',
				'setting' => [
					'post_type' => 'mw_slider',
					'install' => 'weditorWithTemplate',
					'texts' => [
						'empty' => __(' - Bez obsahu - ', 'cms_ve'),
						'edit' => __('Upravit vybraný obsah', 'cms_ve'),
						'duplicate' => __('Duplikovat vybraný obsah', 'cms_ve'),
						'create' => __('Vytvořit nový obsah', 'cms_ve'),
						'delete' => __('Smazat vybraný obsah', 'cms_ve'),
					],
				],
				'onedit' => [
					'action' => 'reload',
				],
			],
		],

	],
];
$mwContainer->row_setting['slider_set'] = [
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'row_height',
				'title' => __('Výška slideru', 'cms_ve'),
				'type' => 'select',
				'options' => [
					//array('name' => __('Defaultní','cms_ve'), 'value' => 'default'),
					['name' => __('Na celou obrazovku', 'cms_ve'), 'value' => 'full'],
					['name' => __('Vlastní', 'cms_ve'), 'value' => 'custom'],
				],
				'content' => 'full',
				'onedit' => [
					'action' => 'change_row_slider_height',
					'class' => 'row_height_',
				],
				'show' => 'height_setting',
			],
			[
				'id' => 'min-height',
				'title' => __('Minimální výška slideru', 'cms_ve'),
				'type' => 'slider',
				'setting' => [
					'min' => '10',
					'max' => '1000',
					'unit' => 'px',
				],
				'content' => '100',
				'onedit' => [
					'action' => 'change_row_slider_height',
					'css' => 'min-height',
					'target' => '.row_height_custom',
				],
				'show_group' => 'height_setting',
				'show_val' => 'custom',
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'miocarousel_setting',
				'type' => 'miocarousel',
				'content' => [
					'animation' => 'fade',
					'delay' => '3500',
					'speed' => '1000',
					'color_scheme' => '',
				],
				'onedit' => [
					'action' => 'change_slider',
					'target' => ' .miocarousel',
				],
				'show_group' => 'sliderset',
				'show_val' => '1',
			],
		],
	],
];
$mwContainer->row_setting['slide_advance'] = [
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [

			[
				'id' => 'row_padding',
				'name' => __('Odsazení obsahu', 'cms_ve'),
				'type' => 'select',
				'content' => 'small',
				'show' => 'padding_setting',
				'options' => [
					['name' => __('Žádné', 'cms_ve'), 'value' => 'none'],
					['name' => __('Malé', 'cms_ve'), 'value' => 'small'],
					['name' => __('Střední', 'cms_ve'), 'value' => 'big'],
					['name' => __('Velké', 'cms_ve'), 'value' => 'biger'],
					['name' => __('Vlastní', 'cms_ve'), 'value' => 'custom'],
				],
				'onedit' => [
					'action' => 'change_class',
					'class' => 'row_padding_',
				],
			],

			[
				'id' => 'padding_group',
				'type' => 'group',
				'setting' => [

					[
						'id' => 'padding_top',
						'title' => __('Horní', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => 'px',
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'padding-top',
							'target' => '.row_padding_custom',
						],
						'formobile' => true,
						'content' => '50',
					],
					[
						'id' => 'padding_bottom',
						'title' => __('Spodní', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => 'px',
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'padding-bottom',
							'target' => '.row_padding_custom',
						],
						'formobile' => true,
						'content' => '50',
					],
					[
						'id' => 'padding_left',
						'title' => __('Levé', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => ['px', '%'],
						],
						'formobile' => true,
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'padding-left',
							'target' => '.row_padding_custom .row_fix_width',
						],
						'content' => ['size' => '', 'unit' => 'px'],
					],
					[
						'id' => 'padding_right',
						'title' => __('Pravé', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => ['px', '%'],
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'padding-right',
							'target' => '.row_padding_custom .row_fix_width',
						],
						'formobile' => true,
						'content' => ['size' => '', 'unit' => 'px'],
					],
				],
				'show_group' => 'padding_setting',
				'show_val' => 'custom',
			],
		],
	],
];

$mwContainer->row_setting['basic'] = [
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'background_color',
				'title' => __('Barva pozadí', 'cms_ve'),
				'type' => 'background',
				'content' => [
					'transparency' => '1',
					'gradient' => '0',
				],
				'onedit' => [
					'action' => 'change_css',
				],
			],
			[
				'type' => 'tabs',
				'id' => 'background_setting',
				'content' => 'image',
				'onedit' => [
					'action' => 'reload',
					//'target'=>' .row_background_container',
				],
				'tabs' => [
					'image' => [
						'name' => __('Obrázek', 'cms_ve'),
						'icon' => 'image',
						'setting' => [
							[
								'id' => 'background_image',
								'type' => 'bgimage',
								'mobile' => true,
								'content' => [
									'pattern' => '',
								],
								'onedit' => [
									'action' => 'change_background',
									'target' => ' .row_background_container',
								],
							],
						],
					],
					'slider' => [
						'name' => __('Slider', 'cms_ve'),
						'icon' => 'code',
						'setting' => [
							[
								'id' => 'background_slides',
								'title' => __('Obrázky slidů', 'cms_ve'),
								'type' => 'image_gallery',
								'onedit' => [
									'action' => 'reload_row_background',
									'target' => ' .row_background_container',
								],
							],
							[
								'id' => 'slider_overlay_color',
								'title' => __('Překrytí slideru barvou', 'cms_ve'),
								'type' => 'transparent_color',
								'content' => [
									'color' => '',
									'transparency' => '0.7',
									'rgba' => '',
								],
								'onedit' => [
									'action' => 'change_css',
									'css' => 'background',
									'target' => ' .row_background_container .background_overlay',
								],
							],

							/*
							array(
							'id' => 'background_delay',
							'title' => __('Zpoždění slidů','cms_ve'),
							'type' => 'size',
							'unit' => 'ms',
							'content'=> '3000',
							),
							array(
							'id' => 'background_speed',
							'title' => __('Délka animace','cms_ve'),
							'type' => 'size',
							'unit' => 'ms',
							'content'=> '1500',
							),
							*/

						],
					],
					'video' => [
						'name' => __('Video', 'cms_ve'),
						'icon' => 'film',
						'setting' => [
							[
								'name' => __('Použít', 'cms_ve'),
								'id' => 'video_type',
								'type' => 'select',
								'content' => 'iframe',
								'options' => [
									['name' => __('Youtube/Vimeo', 'cms_ve'), 'value' => 'iframe'],
									['name' => __('Vlastní soubor videa', 'cms_ve'), 'value' => 'custom'],
								],
								'onedit' => [
									'action' => 'reload_row_background',
									'target' => ' .row_background_container',
								],
								'show' => 'video_type',
							],
							[
								'name' => __('Odkaz na video', 'cms_ve'),
								'id' => 'video_url',
								'type' => 'text',
								'content' => '',
								'onedit' => [
									'action' => 'reload_row_background',
									'target' => ' .row_background_container',
								],
								'show_group' => 'video_type',
								'show_val' => 'iframe',
							],
							[
								'name' => __('Video ve formátu .mp4', 'cms_ve'),
								'id' => 'background_video_mp4',
								'type' => 'upload_file',
								'show_group' => 'video_type',
								'show_val' => 'custom',
								'onedit' => [
									'action' => 'reload_row_background',
									'target' => ' .row_background_container',
								],
							],
							[
								'name' => __('Video ve formátu .webm', 'cms_ve'),
								'id' => 'background_video_webm',
								'type' => 'upload_file',
								'show_group' => 'video_type',
								'show_val' => 'custom',
								'onedit' => [
									'action' => 'reload_row_background',
									'target' => ' .row_background_container',
								],
							],
							[
								'name' => __('Video ve formátu .ogg', 'cms_ve'),
								'id' => 'background_video_ogg',
								'type' => 'upload_file',
								'show_group' => 'video_type',
								'show_val' => 'custom',
								'onedit' => [
									'action' => 'reload_row_background',
									'target' => ' .row_background_container',
								],
							],
//							[
//								'id' => 'show_mobile',
//								'label' => __('Zobrazit na mobilech', 'cms_ve'),
//								'type' => 'switch',
//								'show' => 'show_on_mobile',
//								'onedit' => [
//									'action' => 'reload_row_background',
//									'target' => ' .row_background_container',
//									/*
//							'action'=>'toggle_class_inverse',
//							'class'=>'background_video_hide_onmobile',
//							'target'=>' .row_background_container', */
//								],
//							],
							[
								'id' => 'video_image',
								'title' => __('Zástupný obrázek pro mobily', 'cms_ve'),
								'type' => 'bgimage',
								'respect_size' => false,
								'hide' => ['efect', 'cover', 'repeat', 'color_filter'],
								'content' => [
									'cover' => 1,
									'position' => '50% 50%',
								],
								'show_group' => 'show_on_mobile',
								'show_val' => '0',
								'onedit' => [
									'action' => 'change_background',
									'target' => ' .background_video_container',
								],
							],
							[
								'id' => 'video_overlay_color',
								'title' => __('Překrýt video barvou', 'cms_ve'),
								'type' => 'transparent_color',
								'content' => [
									'color' => '',
									'transparency' => '0.7',
									'rgba' => '',
								],
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'background-color',
									'target' => ' .row_background_container .background_overlay',
								],
							],
						],
					],
				],
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'row_height',
				'title' => __('Výška bloku', 'cms_ve'),
				'type' => 'select',
				'options' => [
					['name' => __('Defaultní', 'cms_ve'), 'value' => 'default'],
					['name' => __('Na celou obrazovku', 'cms_ve'), 'value' => 'full'],
					['name' => __('Vlastní', 'cms_ve'), 'value' => 'custom'],
				],
				'content' => 'default',
				'onedit' => [
					'action' => 'change_class',
					'class' => 'row_height_',
				],
				'show' => 'height_setting',
			],
			[
				'id' => 'min-height',
				'title' => __('Minimální výška bloku', 'cms_ve'),
				'type' => 'slider',
				'setting' => [
					'min' => '10',
					'max' => '1000',
					'unit' => 'px',
				],
				'content' => '100',
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'min-height',
					'target' => '.row_height_custom',
				],
				'show_group' => 'height_setting',
				'show_val' => 'custom',
			],
			[
				'id' => 'sliderset_group',
				'type' => 'group',
				'setting' => [
					[
						'id' => 'scroll_arrow',
						'type' => 'switch',
						'label' => __('Zobrazit šipku', 'cms_ve'),
						'onedit' => [
							'action' => 'toggle_class',
							'class' => 'row_with_arrow',
						],
						'show' => 'row_arrow',
					],
					[
						'id' => 'arrow_color',
						'title' => __('Barva šipky', 'cms_ve'),
						'type' => 'select',
						//'row_class'=>'',
						'options' => [
							['name' => __('Světlé', 'cms_ve'), 'value' => '#fff'],
							['name' => __('Tmavé', 'cms_ve'), 'value' => '#000'],
						],
						'content' => '#fff',
						'onedit' => [
							'action' => 'change_css',
							'css' => 'color',
							'target' => ' .mw_scroll_tonext_icon',
						],
						'show_group' => 'row_arrow',
						'show_val' => '1',
					],
				],
				'show_group' => 'height_setting',
				'show_val' => 'full',
			],
			[
				'id' => 'content_align',
				'title' => __('Vertikální zarovnání obsahu', 'cms_ve'),
				'type' => 'imageoption',
				'options' => [
					'top' => [
						'icon' => 'valign-top',
						'text' => __('Nahoru', 'cms_ve'),
					],
					'center' => [
						'icon' => 'valign-center',
						'text' => __('Na střed', 'cms_ve'),
					],
					'bottom' => [
						'icon' => 'valign-bottom',
						'text' => __('Dolů', 'cms_ve'),
					],
				],
				'content' => 'top',
				'onedit' => [
					'action' => 'change_class',
					'class' => 've_valign_',
				],
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'text',
				'title' => __('Texty', 'cms_ve'),
				'type' => 'select',
				'options' => [
					['name' => __('Automatické', 'cms_ve'), 'value' => 'auto'],
					['name' => __('Tmavé', 'cms_ve'), 'value' => 'default'],
					['name' => __('Světlé', 'cms_ve'), 'value' => 'invers'],
					['name' => __('Vlastní', 'cms_ve'), 'value' => 'custom'],
				],
				'content' => 'auto',
				'onedit' => [
					'action' => 'change_class',
					'class' => 'row_text_',
				],
				'show' => 'text_setting',
			],
			[
				'id' => 'font',
				'type' => 'font',
				'title' => __('Font bloku', 'cms_ve'),
				'content' => [
					'font-size' => '',
					'font-family' => '',
					'weight' => '',
					'color' => '',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => '.row_text_custom',
					'setting' => 'variable_color_row',
					'css' => '--font-row-color-',
				],
				'show_group' => 'text_setting',
				'show_val' => 'custom',
			],
			[
				'title' => __('Barva odkazů', 'cms_ve'),
				'id' => 'link_color',
				'type' => 'color',
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'color',
					'target' => '.row_text_custom a:not(.ve_content_button)',
				],
				'show_group' => 'text_setting',
				'show_val' => 'custom',
			],
		],
	],
];
$mwContainer->row_setting['advance'] = [
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'type',
				'name' => __('Typ bloku', 'cms_ve'),
				'type' => 'select',
				'content' => 'basic',
				'options' => [
					['name' => __('Základní', 'cms_ve'), 'value' => 'basic'],
					['name' => __('Box', 'cms_ve'), 'value' => 'fixed'],
					['name' => __('Obsah přes celou šířku', 'cms_ve'), 'value' => 'full'],
				],
				'onedit' => [
					'action' => 'change_class',
					'class' => 'row_',
				],
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [

			[
				'id' => 'row_padding',
				'name' => __('Odsazení obsahu', 'cms_ve'),
				'type' => 'select',
				'content' => 'big',
				'show' => 'padding_setting',
				'options' => [
					['name' => __('Žádné', 'cms_ve'), 'value' => 'none'],
					['name' => __('Malé', 'cms_ve'), 'value' => 'small'],
					['name' => __('Střední', 'cms_ve'), 'value' => 'big'],
					['name' => __('Velké', 'cms_ve'), 'value' => 'biger'],
					['name' => __('Vlastní', 'cms_ve'), 'value' => 'custom'],
				],
				'onedit' => [
					'action' => 'change_class',
					'class' => 'row_padding_',
				],
			],

			[
				'id' => 'padding_group',
				'type' => 'group',
				'setting' => [

					[
						'id' => 'padding_top',
						'title' => __('Horní', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => 'px',
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'padding-top',
							'target' => '.row_padding_custom',
						],
						'formobile' => true,
						'content' => '50',
					],
					[
						'id' => 'padding_bottom',
						'title' => __('Spodní', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => 'px',
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'padding-bottom',
							'target' => '.row_padding_custom',
						],
						'formobile' => true,
						'content' => '50',
					],
					[
						'id' => 'padding_left',
						'title' => __('Levé', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => ['px', '%'],
						],
						'formobile' => true,
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'padding-left',
							'target' => '.row_padding_custom .row_fix_width',
						],
						'content' => ['size' => '', 'unit' => 'px'],
					],
					[
						'id' => 'padding_right',
						'title' => __('Pravé', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => ['px', '%'],
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'padding-right',
							'target' => '.row_padding_custom .row_fix_width',
						],
						'formobile' => true,
						'content' => ['size' => '', 'unit' => 'px'],
					],
				],
				'show_group' => 'padding_setting',
				'show_val' => 'custom',
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'title' => __('Horní ohraničení', 'cms_ve'),
				'id' => 'border-top',
				'type' => 'border',
				'content' => [
					'size' => '',
					'style' => 'solid',
					'color' => '',
				],
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'border-top',
				],
			],
			[
				'title' => __('Spodní ohraničení', 'cms_ve'),
				'id' => 'border-bottom',
				'type' => 'border',
				'content' => [
					'size' => '',
					'style' => 'solid',
					'color' => '',
				],
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'border-bottom',
				],
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'label' => __('Horní ohraničení tvarem', 'cms_ve'),
				'id' => 'shape_top',
				'type' => 'shape_divider',
				'content' => [
					'shape' => '',
					'size' => '100',
					'repeat' => '1',
				],
				'onedit' => [
					'action' => 'change_shape_divider',
					'target' => ' .mw_row_shape_divider_top',
					'setting' => 'top',
				],
			],
			[
				'label' => __('Spodní ohraničení tvarem', 'cms_ve'),
				'id' => 'shape_bottom',
				'type' => 'shape_divider',
				'bottom' => '1',
				'content' => [
					'shape' => 'tilt',
					'size' => '100',
				],
				'onedit' => [
					'action' => 'change_shape_divider',
					'target' => ' .mw_row_shape_divider_bottom',
					'setting' => 'bottom',
				],
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'margin_top',
				'title' => __('Horní posunutí', 'cms_ve'),
				'type' => 'slider',
				'setting' => [
					'min' => '0',
					'max' => '300',
					'unit' => 'px',
				],
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'margin-top',
				],
				'content' => '',
			],
			[
				'id' => 'margin_bottom',
				'title' => __('Spodní posunutí', 'cms_ve'),
				'type' => 'slider',
				'setting' => [
					'min' => '0',
					'max' => '300',
					'unit' => 'px',
				],
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'margin-bottom',
				],
				'content' => '',
			],
		],
	],
	[
		'type' => 'group',
		'class' => 'mw_visual_group',
		'setting' => [
			[
				'id' => 'css_class',
				'title' => __('Vlastní css třída bloku', 'cms_ve'),
				'type' => 'text',
				'content' => '',
				'tooltip' => __('Nastavení se projeví po reloadu stránky', 'cms_ve'),
			],
			[
				'id' => 'row_anchor',
				'title' => __('Kotva bloku', 'cms_ve'),
				'type' => 'text',
				'content' => '',
				'tooltip' => __('Nastavení se projeví po reloadu stránky', 'cms_ve'),
			],
		],
	],
];
$mwContainer->row_setting['show'] = [
	[
		'id' => 'mobile_visibility',
		'type' => 'switch',
		'label' => __('Skrýt na mobilu', 'cms_ve'),
		'onedit' => [
			'action' => 'toggle_class',
			'class' => 'hide_on_mobile',
		],
	],
	[
		'id' => 'tablet_visibility',
		'type' => 'switch',
		'label' => __('Skrýt na tabletu', 'cms_ve'),
		'onedit' => [
			'action' => 'toggle_class',
			'class' => 'hide_on_tablet',
		],
	],
	[
		'id' => 'desktop_visibility',
		'type' => 'switch',
		'label' => __('Skrýt na počítači', 'cms_ve'),
		'onedit' => [
			'action' => 'toggle_class',
			'class' => 'hide_on_desktop',
		],
	],
	[
		'id' => 'delay',
		'title' => __('Zobrazit se zpožděním', 'cms_ve'),
		'type' => 'size',
		'unit' => 's',
		'content' => '',
	],
];

$vePage->getSubmodules();
