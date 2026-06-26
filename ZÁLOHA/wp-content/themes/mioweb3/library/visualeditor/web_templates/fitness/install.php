<?php
$web = [
	'title' => __('Fitness trenér', 'cms_ve'),
	'desc' => __('Osobní web pro fitness ternéry a prezentaci jejich práce.', 'cms_ve'),
	'demo' => 'https://demo-fitness.mioweb.cz',
	'tags' => ['fitness', 'personal', 'expert'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/fitness/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/fitness/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'story' => [
			'title' => __('O mně', 'cms_ve'),
		],
		'service' => [
			'title' => __('Služby', 'cms_ve'),
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
					'page' => 'service',
				],
				[
					'type' => 'page',
					'page' => 'story',
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
						'color1' => '#e0653d',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
