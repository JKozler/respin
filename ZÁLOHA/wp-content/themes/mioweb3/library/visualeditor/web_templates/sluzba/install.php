<?php
$web = [
	'title' => __('Prodej služby', 'cms_ve'),
	'desc' => __('Šablona je vhodná pro vytvoření osobního nebo firemního webu zaměřeného na prodej služby.', 'cms_ve'),
	'demo' => 'https://demo-servis.mioweb.cz',
	'tags' => ['personal', 'business', 'product'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/sluzba/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/sluzba/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'contact' => [
			'title' => __('Objednávka služby', 'cms_ve'),
		],
		'mywork' => [
			'title' => __('Ukázky prací', 'cms_ve'),
		],
		'home' => [
			'title' => __('Úvodní stránka', 'cms_ve'),
			'page' => 'home',
		],
	],
	// content blocks
	'content_blocks' => [
		'footer' => [],
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
					'page' => 'mywork',
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
						'color1' => '#1d9fe0',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
