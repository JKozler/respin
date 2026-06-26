<?php
$web = [
	'title' => __('Ruční výroba', 'cms_ve'),
	'desc' => __('Šablona je vhodná pro vytvoření prodejního nebo prezentačního webu pro vaše ručně vyráběné produkty.', 'cms_ve'),
	'demo' => 'https://demo-jewelry.mioweb.cz',
	'tags' => ['personal', 'business', 'product'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/jewelry/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/jewelry/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'order' => [
			'title' => __('Objednávka produktu', 'cms_ve'),
		],
		'story' => [
			'title' => __('O autorovi', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'home' => [
			'title' => __('Úvodní stránka', 'cms_ve'),
			'page' => 'home',
		],
	],
	// menus
	'menus' => [
		'main' => [
			'name' => __('Hlavní menu', 'cms_ve'),
			'items' => [
				[
					'type' => 'page',
					'page' => 'home',
				],
				[
					'type' => 'page',
					'page' => 'story',
				],
				[
					'type' => 'page',
					'page' => 'order',
				],
				[
					'type' => 'page',
					'page' => 'contact',
				],
			],
		],
	],
	// sidebars
	'sidebars' => [
		'main' => [
			'name' => __('Hlavní', 'cms_ve'),
			'desc' => '',
			'widgets' => [
				'cms_option_widget' => [
					'title' => __('Nadpis formuláře', 'cms_ve'),
					'text' => __('Text formuláře', 'cms_ve'),
					'font' => [
						'font-size' => '20',
						'color' => '#ffffff',
					],
					'bg' => [
						'color1' => '#d6521e',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
