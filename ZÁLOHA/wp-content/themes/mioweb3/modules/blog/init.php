<?php

global $vePage;

define('BLOG_VERSION', '3.1.1');
MW()->add_version('blog', BLOG_VERSION);

define('BLOG_DIR', get_template_directory_uri() . '/modules/blog/');

// language
MW()->load_theme_lang('cms_blog', get_template_directory() . '/modules/blog/languages');

require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/elements.php');
require_once(__DIR__ . '/elements_print.php');
require_once(__DIR__ . '/blog_class.php');
require_once(__DIR__ . '/lib/widgets.php');

mwBlog();

mwPageSelector()->addTab([
	'id' => 'blog',
	'title' => __('Blog', 'cms_blog'),
], 1);

// add blog templates
mwBlog()->add_template('style3', [
	'folder' => 'blog2',
	'style' => 'style',
	'thumb' => BLOG_DIR . 'images/image_select/blog3.jpg',
	'path' => 'modules/blog/templates/',
]);
mwBlog()->add_template('style4', [
	'folder' => 'blog2',
	'style' => 'style',
	'thumb' => BLOG_DIR . 'images/image_select/blog4.jpg',
	'path' => 'modules/blog/templates/',
]);
mwBlog()->add_template('style1', [
	'folder' => 'blog1',
	'style' => 'style1',
	'thumb' => BLOG_DIR . 'images/image_select/blog1.jpg',
	'path' => 'modules/blog/templates/',
]);
mwBlog()->add_template('style2', [
	'folder' => 'blog1',
	'style' => 'style2',
	'thumb' => BLOG_DIR . 'images/image_select/blog2.jpg',
	'path' => 'modules/blog/templates/',
]);

// Sidebar
//***********************************************************************************

MW()->add_sidebar([
	'name' => __('Defaultní sidebar', 'cms_blog'),
	'id' => 'default_sidebar',
	'description' => '',
]);

// Nastavení
//***********************************************************************************

mwSetting()->addObjectSetting([
	'id' => 'page_comments',
	'title' => __('Komentáře', 'cms_blog'),
	'info' => sprintf(__('Zobrazení komentářů lze nastavit v <a target="_blank" href="%s">nastavení komentářů</a>.', 'cms_blog'), mwSetting()->getPage('comments_setting')->getUrl()),
	'fields' => [
		[
			'id' => '',
			'type' => 'box',
			'setting' => [
				[
					'name' => __('Komentáře', 'cms_blog'),
					'id' => 'hide_comments',
					'type' => 'multiple_checkbox',
					'options' => [
						['name' => __('Skrýt wordpressové komentáře', 'cms_blog'), 'value' => 'wordpress'],
						['name' => __('Skrýt facebookové komentáře', 'cms_blog'), 'value' => 'facebook'],
					],
				],
				[
					'title' => __('Pořadí komentářů', 'cms_blog'),
					'id' => 'comments_order',
					'type' => 'select',
					'options' => [
						['name' => __('Použít globální nastavení', 'cms_blog'), 'value' => ''],
						['name' => __('První facebookové komentáře, druhé wordpressové komentáře', 'cms_blog'), 'value' => 'facebook'],
						['name' => __('První wordpressové komentáře, druhé facebookové komentáře', 'cms_blog'), 'value' => 'wordpress'],
					],
				],
			],
		],
	],
], ['post']);

mwSetting()->registerPageSettingType('blog_dashboard', [
	'class' => 'mwBlog',
	'function' => 'dashboard',
]);

mwSetting()->addGroup([
	'id' => 'blog_option',
	'icon' => 'feather',
	'title' => __('Blog', 'cms_blog'),
	'home' => 'blog_dashboard',
	'order' => 20,
]);

// setting
mwSetting()->addPage([
	'id' => 'blog_dashboard',
	'icon' => 'bar-chart-2',
	'group' => 'blog_option',
	'title' => __('Přehled', 'cms_blog'),
	'type' => 'blog_dashboard',
]);
mwSetting()->addPage([
	'id' => 'post',
	'icon' => 'edit-3',
	'group' => 'blog_option',
	'title' => __('Články', 'cms_blog'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => 'category',
	'icon' => 'list',
	'group' => 'blog_option',
	'title' => __('Kategorie', 'cms_blog'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => 'post_tag',
	'icon' => 'tag',
	'group' => 'blog_option',
	'title' => __('Štítky', 'cms_blog'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => 'blog_sidebars_widgets',
	'icon' => 'sidebar',
	'group' => 'blog_option',
	'title' => __('Sidebary a widgety', 'cms_blog'),
	'type' => 'link',
	'link' => admin_url('widgets.php'),
]);
mwSetting()->addPage([
	'id' => 'blog_comments',
	'icon' => 'settings',
	'group' => 'blog_option',
	'service_class' => 'mwSettingPageService_blog',
	'title' => __('Nastavení', 'cms_blog'),
]);
mwSetting()->addPage([
	'id' => 'blog_sidebars',
	'parent' => 'blog_comments',
	'group' => 'blog_option',
	'title' => __('Sidebary', 'cms_blog'),
	'info' => sprintf(__('Každému typu stránky můžete přiřadit jiný sidebar. Nové sidebary lze vytvořit v administraci wordpressu v menu <a target="_blank" href="%s">Vzhled -> Widgety</a>.', 'cms_blog'), admin_url('widgets.php')),
]);
mwSetting()->addPage([
	'id' => 'mw_blog_codes',
	'parent' => 'blog_comments',
	'group' => 'blog_option',
	'title' => __('Vlastní kódy', 'cms_blog'),
]);
mwSetting()->addPage([
	'id' => 'blog_popups',
	'parent' => 'blog_comments',
	'group' => 'blog_option',
	'title' => __('Pop-upy blogu', 'cms_blog'),
]);
mwSetting()->addPage([
	'id' => 'mw_blog_seo',
	'parent' => 'blog_comments',
	'group' => 'blog_option',
	'title' => __('SEO blogu', 'cms_blog'),
]);
mwSetting()->addPage([
	'id' => 'blog_facebook',
	'parent' => 'blog_comments',
	'group' => 'blog_option',
	'title' => __('Facebook atributy', 'cms_blog'),
]);

// appearance
mwSetting()->addPage([
	'id' => 'blog_appearance',
	'icon' => 'layout',
	'group' => 'blog_option',
	'title' => __('Vzhled', 'cms_blog'),
]);
mwSetting()->addPage([
	'id' => 'blog_header',
	'parent' => 'blog_appearance',
	'group' => 'blog_option',
	'title' => __('Hlavička blogu', 'cms_blog'),
]);
mwSetting()->addPage([
	'id' => 'blog_footer',
	'parent' => 'blog_appearance',
	'group' => 'blog_option',
	'title' => __('Patička blogu', 'cms_blog'),
]);

mwSetting()->addPageSetting('mw_blog_seo', [
	[
		'id' => '',
		'type' => 'box',
		'title' => __('SEO úvodní stránky blogu', 'cms'),
		'setting' => [
			[
				'name' => __('Titulek', 'cms'),
				'id' => 'home_metatitle',
				'type' => 'text',
				'desc' => __('Maximální doporučená délka pro titulek je 70 znaků. Pokud necháte toto pole prázdné, bude tag <code>title</code> obsahovat název stránky.', 'cms'),
				'tooltip' => __('Tag <code>title</code> je druhým nejdůležitějším prvkem, který ovlivňuje on-page SEO. Jeho obsah se zároveň zobrazuje v záhlaví prohlížeče a jako název stránky při vyhledávání.', 'cms'),
			],
			[
				'name' => __('Popis', 'cms'),
				'id' => 'home_metadesc',
				'type' => 'textarea',
				'desc' => __('Maximální doporučená délka je 150 znaků.', 'cms'),
				'tooltip' => __('Meta tag <code>description</code> slouží jako krátký popis obsahu stránky. Některé vyhledávače tento tag používají pro zobrazení popisku stránky ve výsledku vyhledávání. Obsah by měl být tvořen souvislými větami s vhodně zvolenými klíčovými slovy.', 'cms'),
			],
			[
				'name' => __('Klíčová slova', 'cms'),
				'id' => 'home_metakey',
				'type' => 'textarea',
				'tooltip' => __('Vyplnění meta tag <code>keywords</code> je další možností, jak zvýšit on-page SEO stránky. Napište zde několik klíčových slov, které souvisejí s obsahem stránky. Nepřehánějte to ale s jejich množstvím.', 'cms'),
			],
			[
				'name' => __('Pro roboty', 'cms'),
				'id' => 'home_robots',
				'type' => 'multiple_checkbox',
				'options' => [
					['name' => __('Neindexovat tuto stránku (<code>noindex</code>)', 'cms'), 'value' => 'noindex'],
					['name' => __('Nesledovat odkazy této stránky (<code>nofollow</code>)', 'cms'), 'value' => 'nofollow'],
					['name' => __('Nearchivovat tuto stránku (<code>noarchive</code>)', 'cms'), 'value' => 'noarchive'],
				],
				'tooltip' => __('Zde můžete nastavit jak se mají na stránce chovat roboti. Můžete zakázat robotům indexování obsahu (noindex), sledování odkazů (nofollow) a zařazování stránky do archivu (noarchive).', 'cms'),
			],
		],
	],
]);
mwSetting()->addPageSetting('blog_facebook', [
	[
		'id' => '',
		'type' => 'box',
		'title' => __('Facebookové atributy hlavní stránky blogu', 'cms'),
		'setting' => [
			[
				'title' => '',
				'id' => 'info',
				'type' => 'info',
				'content' => __('Toto nastavení určí facebookové atributy úvodní stránky blogu. Pro kontrolu zobrazení na Facebooku můžete použít debugger na adrese: <a href="https://developers.facebook.com/tools/debug/" target="_blank">https://developers.facebook.com/tools/debug/</a>, kde stačí zadat URL stránky, kterou chcete zkontrolovat.', 'cms_blog'),
			],
			[
				'title' => __('Facebookový titulek', 'cms_blog'),
				'id' => 'fac_title',
				'type' => 'text',
				'tooltip' => __('Meta tag <code>og:title</code> určuje nadpis stránky při jejím sdílení na Facebooku. Pokud jej nenastavíte, použije se název stránky.', 'cms_blog'),
			],
			[
				'title' => __('Facebookový popis', 'cms_blog'),
				'id' => 'fac_desc',
				'type' => 'textarea',
				'tooltip' => __('Meta tag <code>og:description</code> určuje popis stránky při jejím sdílení na Facebooku.', 'cms_blog'),
			],
			[
				'title' => __('Facebookový obrázek (og:image)', 'cms_blog'),
				'id' => 'fac_image',
				'type' => 'image_url',
				'tooltip' => __('Pomocí meta tagu <code>og:image</code> můžete Facebooku přikázat, jaký obrázek má použít při sdílení této stránky.', 'cms_blog'),
			],
		],
	],
]);
mwSetting()->addPageSetting('blog_popups', MW()->container['popup_setting']);


// Nastavení blogu
//***********************************************************************************

mwSetting()->addPageSetting('blog_comments', [
	[
		'id' => 'blog_basic',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Základní nastavení', 'cms_blog'),
		'setting' => [
			[
				'title' => __('Stránka blogu', 'cms_blog'),
				'id' => 'blog_page',
				'type' => 'blog_selectpage',
				'options' => [
					'posts' => __('Zobrazit blog na úvodní stránce', 'cms_blog'),
					'page' => __('Zobrazit blog na stránce', 'cms_blog'),
				],
			],
			[
				'title' => __('Odkaz v logu v hlavičce blogu', 'cms_blog'),
				'id' => 'blog_logolink',
				'type' => 'radio',
				'options' => [
					'blog' => __('Odkazovat na úvodní stránku blogu', 'cms_blog'),
					'web' => __('Odkazovat na úvodní stránku webu', 'cms_blog'),
				],
				'content' => 'blog',
				'description' => __('Vyberte cíl odkazu loga blogu.', 'cms_blog'),
			],
			[
				'name' => __('Počet článků na stránku', 'cms'),
				'id' => 'posts_per_page',
				'type' => 'number',
				'save' => 'option',
			],
		],
	],
	[
		'id' => 'after_post_content',
		'type' => 'toggle_group',
		'title' => __('Obsah za článkem blogu', 'cms_blog'),
		'setting' => [
			[
				'title' => '',
				'id' => 'info',
				'type' => 'info',
				'content' => __('Tento obsah se bude zobrazovat na konci každého článku blogu, což je skvělé místo k umístění magnetu nebo reklamy.', 'cms_blog'),
			],
			[
				'id' => 'content_after_post',
				'title' => __('Obsah', 'cms_ve'),
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
		'id' => 'blog_comments',
		'type' => 'toggle_group',
		'title' => __('Komentáře', 'cms_blog'),
		'setting' => [
			[
				'title' => __('Komentáře', 'cms_blog'),
				'id' => 'comments',
				'type' => 'multiple_checkbox',
				'options' => [
					['name' => __('Zobrazit wordpressové komentáře pod každým článkem', 'cms_blog'), 'value' => 'wordpress'],
					['name' => __('Zobrazit facebookové komentáře pod každým článkem', 'cms_blog'), 'value' => 'facebook'],
				],
				'content' => ['wordpress' => 'wordpress'],
			],
			[
				'title' => __('Pořadí komentářů', 'cms_blog'),
				'id' => 'comments_order',
				'type' => 'select',
				'options' => [
					['name' => __('První facebookové komentáře, druhé wordpressové komentáře', 'cms_blog'), 'value' => 'facebook'],
					['name' => __('První wordpressové komentáře, druhé facebookové komentáře', 'cms_blog'), 'value' => 'wordpress'],
				],
			],
			[
				'title' => __('Podrobné nastavení komentářů', 'cms_blog'),
				'id' => 'wordpress_link_setting',
				'type' => 'info',
				'content' => __('Podrobné nastavení komentářů naleznete v', 'cms_blog') . ' <a href="' . mwSetting()->getPage('comments_setting')->getUrl() . '" target="_blank">' . __('sekci komentáře', 'cms_blog') . '</a>',
			],
		],
	],
	[
		'id' => 'blog_socials',
		'type' => 'toggle_group',
		'title' => __('Tlačítka sociálních sítí', 'cms_blog'),
		'setting' => [
			[
				'title' => __('V detailu článku', 'cms_blog'),
				'id' => 'show_share',
				'type' => 'multiple_checkbox',
				'options' => [
					['name' => __('Zobrazit tlačítko Facebooku', 'cms_blog'), 'value' => 'facebook'],
					['name' => __('Zobrazit tlačítko sdílet na Facebooku', 'cms_blog'), 'value' => 'facebook_share'],
					['name' => __('Zobrazit tlačítko Twitteru', 'cms_blog'), 'value' => 'twitter'],
					['name' => __('Zobrazit tlačítko LinkedIn', 'cms_blog'), 'value' => 'linkedin'],
				],
			],
			[
				'title' => __('Ve výpisu článků', 'cms_blog'),
				'id' => 'show_share_list',
				'type' => 'multiple_checkbox',
				'options' => [
					['name' => __('Zobrazit tlačítko Facebooku', 'cms_blog'), 'value' => 'facebook'],
					['name' => __('Zobrazit tlačítko sdílet na Facebooku', 'cms_blog'), 'value' => 'facebook_share'],
				],
			],
		],
	],
	[
		'id' => 'blog_showing',
		'type' => 'toggle_group',
		'title' => __('Zobrazení', 'cms_blog'),
		'setting' => [
			[
				'title' => '',
				'id' => 'hide',
				'type' => 'multiple_checkbox',
				'options' => [
					['name' => __('Skrýt autora článků', 'cms_blog'), 'value' => 'autorbox'],
					['name' => __('Skrýt výpis podobných článků', 'cms_blog'), 'value' => 'related_posts'],
					['name' => __('Skrýt popisek ve výpis podobných článků', 'cms_blog'), 'value' => 'related_posts_text'],
					['name' => __('Skrýt datum zveřejnění článků', 'cms_blog'), 'value' => 'date'],
				],
			],
			[
				'title' => '',
				'id' => 'show',
				'type' => 'multiple_checkbox',
				'options' => [
					['name' => __('Zobrazit počet návštěvníků článku', 'cms_blog'), 'value' => 'visitors'],
					['name' => __('Zobrazit datum aktualizace článku', 'cms_blog'), 'value' => 'updated'],
				],
			],
		],
	],
]);
mwSetting()->addPageSetting('blog_sidebars', [
	[
		'id' => '',
		'type' => 'box',
		'setting' => [
			[
				'id' => 'sidebars_info',
				'type' => 'info',
				'content' => sprintf(__('Sidebary můžete vytvářet nebo editovat v <a target="_blank" href="%s">administraci wordpressu</a>. Zde potom můžete nastavit, jaký sidebar se má zobrazovat na jakém typu stránky.', 'cms_blog'), admin_url('widgets.php')),
			],
			[
				'name' => __('Sidebar blogu (úvodní stránky)', 'cms_blog'),
				'id' => 'sidebar_blog',
				'type' => 'sidebarselect',
				'content' => 'default_sidebar',
			],
			[
				'name' => __('Sidebar kategorií', 'cms_blog'),
				'id' => 'sidebar_category',
				'type' => 'sidebarselect',
				'content' => 'default_sidebar',
			],
			[
				'name' => __('Sidebar příspěvků', 'cms_blog'),
				'id' => 'sidebar_post',
				'type' => 'sidebarselect',
				'content' => 'default_sidebar',
			],
			[
				'name' => __('Sidebar autorů', 'cms_blog'),
				'id' => 'sidebar_author',
				'type' => 'sidebarselect',
				'content' => 'default_sidebar',
			],
			[
				'name' => __('Sidebar tagů', 'cms_blog'),
				'id' => 'sidebar_tag',
				'type' => 'sidebarselect',
				'content' => 'default_sidebar',
			],
			[
				'name' => __('Sidebar vyhledávání', 'cms_blog'),
				'id' => 'sidebar_search',
				'type' => 'sidebarselect',
				'content' => 'default_sidebar',
			],
		],
	],
]);
mwSetting()->addPageSetting('mw_blog_codes', [
	[
		'id' => '',
		'type' => 'box',
		'title' => __('Kódy pro blog', 'cms_blog'),
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
		'title' => __('CSS styly pro blog', 'cms_blog'),
		'setting' => [
			[
				'id' => 'css',
				'type' => 'textarea',
				'rows' => 8,
				'desc' => __('Vložením vlastních CSS (kaskádových) stylů můžete ovlivnit vzhled blogu.', 'cms_blog'),
			],
		],
	],
]);
mwSetting()->addPageSetting('blog_appearance', [
	[
		'id' => 'appearance_setting',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Vzhled blogu', 'cms_blog'),
		'setting' => [
			[
				'title' => __('Vzhled blogu', 'cms_blog'),
				'id' => 'appearance',
				'type' => 'blogselect',
				'content' => 'style3',
				'show' => 'blog_style',
			],
			[
				'title' => __('Barva pozadí', 'cms_blog'),
				'id' => 'background_color',
				'type' => 'color',
				'content' => '#ebebeb',
				'show_group' => 'blog_style',
				'show_val' => 'style1,style2',
			],
			[
				'title' => __('Obrázek na pozadí', 'cms_blog'),
				'id' => 'background_image',
				'type' => 'bgimage',
				'hide' => ['paralax'],
				'content' => [
					'pattern' => 0,
					'fixed' => 'fixed',
				],
				'show_group' => 'blog_style',
				'show_val' => 'style1,style2',
			],
		],
	],
	[
		'id' => 'blog_sidebar',
		'type' => 'toggle_group',
		'checkbox' => 1,
		'content' => 1,
		'title' => __('Zobrazit sidebar blogu', 'cms_blog'),
		'setting' => [
			[
				'title' => __('Zarovnání sidebaru', 'cms_blog'),
				'id' => 'structure',
				'type' => 'imageoption',
				'size' => 'medium',
				'options' => [
					'right' => [
						'icon' => 'onright',
						'text' => __('Sidebar napravo', 'cms_blog'),
					],
					'left' => [
						'icon' => 'onleft',
						'text' => __('Sidebar nalevo', 'cms_blog'),
					],
				],
				'desc' => __('Můžete zvolit, zda chcete mít sidebar na pravé nebo levé straně.', 'cms_blog'),
				'content' => 'right',
			],
			[
				'title' => __('Font nadpisu sidebaru', 'cms_blog'),
				'id' => 'sidebar_font',
				'type' => 'font',
				'content' => [
					'font-size' => '16',
					//'font-family'=>'',
					//'weight'=>'',
					'use-font' => 'title',
					'line-height' => '',
					'color' => '',
				],
			],
		],
	],
	[
		'id' => 'title_setting',
		'type' => 'toggle_group',
		'title' => __('Vzhled pruhu s nadpisem', 'cms_blog'),
		'setting' => [
			[
				'title' => __('Barva pozadí', 'cms_blog'),
				'id' => 'tb_background',
				'type' => 'background',
				'hide_transparency' => true,
				'content' => ['color1' => '#111111', 'rgba' => 'rgba(20, 20, 20, 1)', 'color2' => '', 'gradient' => ''],
			],
			[
				'title' => __('Font nadpisu', 'cms_blog'),
				'id' => 'tb_font',
				'type' => 'font',
				'content' => [
					'font-size' => '35',
					'use-font' => 'title',
					'line-height' => '',
					'color' => '#ffffff',
				],
			],

		],
	],
	[
		'id' => 'posts_feed_setting',
		'type' => 'toggle_group',
		'title' => __('Vzhled výpisu příspěvků', 'cms_blog'),
		'setting' => [
			[
				'title' => __('Struktura výpisu příspěvků', 'cms_blog'),
				'id' => 'post_look',
				'content' => '1',
				'type' => 'imageoption',
				'size' => 'medium',
				'options' => [
					'1' => [
						'icon' => 'post1',
						'text' => __('Výpis s obrázkem nad textem', 'cms_blog'),
					],
					'2' => [
						'icon' => 'post2',
						'text' => __('Výpis s obrázkem napravo', 'cms_blog'),
					],
					'3' => [
						'icon' => 'post3',
						'text' => __('Více sloupcový výpis článků', 'cms_blog'),
					],
				],
				'show' => 'post_look',
			],
			[
				'id' => 'masonry',
				'title' => __('Mansory zobrazení', 'cms_blog'),
				'type' => 'switch',
				'label' => __('Aktivovat mansory zobrazení', 'cms_blog'),
				'desc' => __('Masonry zobrazení znamená, že jednotlivé boxy s články se budou inteligentně skládat do mřížky pod sebe podle jejich délky a podle dostupného místa.', 'cms_blog'),
				'show_group' => 'post_look',
				'show_val' => '3',
			],
			[
				'id' => 'blog_thumbnail',
				'title' => __('Zobrazit náhledové obrázky v poměru:', 'cms_ve'),
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
				'id' => 'excerpt_length',
				'title' => __('Délka popisku článku (počet slov)', 'cms_blog'),
				'type' => 'size',
				'unit' => __('Slov', 'cms_blog'),
				'desc' => __('Defaultně 55 slov.', 'cms_blog'),
			],
			[
				'id' => 'show_button',
				'title' => __('Tlačítko ve výpisu příspěvků', 'cms_blog'),
				'type' => 'switch',
				'label' => __('Zobrazit tlačítko "Celý článek" ve výpisu článků', 'cms_blog'),
				'show' => 'show_button',
			],
			[
				'id' => 'button_color',
				'title' => __('Barva tlačítka', 'cms_blog'),
				'type' => 'color',
				'content' => '#209bce',
				'show_group' => 'show_button',
				'show_val' => '1',
			],
			[
				'title' => __('Font nadpisu ve výpisu článků', 'cms_blog'),
				'id' => 'article_font',
				'type' => 'font',
				'content' => [
					'font-size' => '27',
					'use-font' => 'title',
					'line-height' => '',
					'color' => '',
				],
			],
			[
				'title' => __('Font popisku ve výpisu článků', 'cms_blog'),
				'id' => 'article_font_text',
				'type' => 'font',
				'content' => [
					'font-size' => '',
					'line-height' => '',
					'color' => '',
				],
			],
		],
	],

	[
		'id' => 'post_detail_setting',
		'type' => 'toggle_group',
		'title' => __('Vzhled detailu článku', 'cms_blog'),
		'setting' => [
			[
				'id' => 'post_detail_look',
				'title' => __('Vzhled detailu článku', 'cms_blog'),
				'type' => 'imageselect',
				'options' => [
					'3' => BLOG_DIR . 'images/image_select/post_detail2.jpg',
					'4' => BLOG_DIR . 'images/image_select/post_detail3.jpg',
					'2' => BLOG_DIR . 'images/image_select/post_detail1.jpg',
					'1' => BLOG_DIR . 'images/image_select/post_detail4.jpg',
					'5' => BLOG_DIR . 'images/image_select/post_detail5.jpg',
				],
				'content' => '3',
			],
		],
	],
	[
		'id' => 'element_text_setting',
		'type' => 'toggle_group',
		'title' => __('Nadpisy a odrážky v textech', 'cms_blog'),
		'setting' => [
			[
				'title' => __('Nadpis 1 (H1)', 'cms_blog'),
				'id' => 'h1_font',
				'type' => 'font',
				'content' => [
					'font-size' => '30',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 2 (H2)', 'cms_blog'),
				'id' => 'h2_font',
				'type' => 'font',
				'content' => [
					'font-size' => '23',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 3 (H3)', 'cms_blog'),
				'id' => 'h3_font',
				'type' => 'font',
				'content' => [
					'font-size' => '18',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 4 (H4)', 'cms_blog'),
				'id' => 'h4_font',
				'type' => 'font',
				'content' => [
					'font-size' => '14',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 5 (H5)', 'cms_blog'),
				'id' => 'h5_font',
				'type' => 'font',
				'content' => [
					'font-size' => '14',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 6 (H6)', 'cms_blog'),
				'id' => 'h6_font',
				'type' => 'font',
				'content' => [
					'font-size' => '14',
					'color' => '',
				],
			],
			[
				'name' => __('Odrážky v textu', 'cms_blog'),
				'type' => 'title',
			],
			[
				'id' => 'li',
				'title' => __('Styl odrážek', 'cms_blog'),
				'type' => 'imageselect',
				'content' => '1',
				'list' => 'list_icons',
			],
		],
	],
	[
		'id' => 'custom_blog_fonts',
		'type' => 'toggle_group',
		'checkbox' => true,
		'title' => __('Vlastní fonty pro blog (nepřebírat fonty webu)', 'cms_blog'),
		'setting' => [
			[
				'title' => __('Font nadpisů', 'cms_blog'),
				'id' => 'title_font',
				'type' => 'font',
				'content' => [
					'font-family' => 'Open Sans',
					'weight' => '600',
					'color' => '',
					'line-height' => '1.2',
					'capitals' => '',
				],
			],
			[
				'title' => __('Font podnadpisů', 'cms_ve'),
				'id' => 'subtitle_font',
				'type' => 'font',
				'content' => [
					'font-family' => 'Open Sans',
					'weight' => '700',
					'color' => '',
					'line-height' => '1.2',
					'capitals' => '',
				],
			],
			[
				'title' => __('Font textů', 'cms_blog'),
				'id' => 'font',
				'type' => 'font',
				'content' => [
					'font-size' => '16',
					'font-family' => 'Open Sans',
					'weight' => '400',
					'line-height' => '',
					'color' => '#111111',
				],
				'setting' => [
					'max_font_size' => '25',
				],
			],
			[
				'name' => __('Barva inverzních textů', 'cms_ve'),
				'id' => 'inverse_text_color',
				'type' => 'color',
				'content' => '#ffffff',
			],
			[
				'title' => __('Barva odkazů', 'cms_blog'),
				'id' => 'link_color',
				'type' => 'color',
				'content' => '#158ebf',
			],
			[
				'name' => __('Barva odkazů po najetí myši', 'cms_ve'),
				'id' => 'hover_color',
				'type' => 'color',
				'content' => '',
			],
		],
	],
	[
		'id' => 'advanced_setting',
		'type' => 'toggle_group',
		'title' => __('Pokročilé', 'cms_ve'),
		'setting' => [
			[
				'title' => __('Šířka obsahu stránky', 'cms_ve'),
				'id' => 'page_width_preset',
				'type' => 'select',
				'options' => [
					['value' => '970px', 'name' => __('Klasická (970px)', 'cms_ve')],
					['value' => '1024px', 'name' => __('Širší (1024px)', 'cms_ve')],
					['value' => '1200px', 'name' => __('Široká (1200px)', 'cms_ve')],
					['value' => '90%', 'name' => __('Přes celou šířku (90%)', 'cms_ve')],
				],
				'content' => '970px',
			],
		],
	],
]);
mwSetting()->addPageSetting('blog_footer', [
	[
		'id' => 'show',
		'type' => 'switch',
		'class' => 'hide_in_toswitch_container',
		'show' => 'footerset',
		'label' => __('Použít pro blog vlastní patičku', 'cms_blog'),
	],
	[
		'id' => 'footer_info',
		'type' => 'info',
		'content' => __('Defaultně se pro blog používá patička webu. Pokud chcete pro blog vytvořit speciální patičku, zaškrtněte volbu "Použít pro blog vlastní patičku".', 'cms_blog'),
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
mwSetting()->addPageSetting('blog_header', [
	[
		'label' => __('Použít pro blog vlastní hlavičku', 'cms_blog'),
		'id' => 'show',
		'class' => 'hide_in_toswitch_container',
		'type' => 'switch',
		'show' => 'headerset',
	],
	[
		'id' => 'header_info',
		'type' => 'info',
		'content' => __('Defaultně se pro blog používá hlavička webu. Pokud chcete pro blog vytvořit speciální hlavičku, zaškrtněte volbu "Použít pro blog vlastní hlavičku".', 'cms_blog'),
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

mwSetting()->addObjectSetting([
	'id' => 'term',
	'title' => __('Kategorie', 'cms_ve'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => 'category',
					'fields' => [
						'term_title' => [
							'label' => __('Název kategorie', 'cms'),
						],
						'term_parent' => [
							'label' => __('Nadřazená kategorie', 'cms'),
						],
						'term_description' => [
							'label' => __('Popisek', 'cms'),
						],
					],
				],
			],
		],
	],
], ['category']);

mwSetting()->addObjectSetting([
	'id' => 'term',
	'title' => __('Štítek', 'cms'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => 'post_tag',
					'fields' => [
						'term_title' => [
							'label' => __('Název štítku', 'cms'),
						],
						'term_description' => [
							'label' => __('Popisek', 'cms'),
						],
					],
				],
			],
		],
	],
], ['post_tag']);
