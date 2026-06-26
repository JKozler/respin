<?php
$web = [
	'title' => __('Realitní web', 'cms_ve'),
	'desc' => __('Šablona je vhodná pro realitní kancelář s vlastní nabídkou nemovitostí.', 'cms_ve'),
	'demo' => 'https://demo-reality3.mioweb.cz',
	'tags' => ['reality'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/reality3/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/reality3/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
		],
		'product' => [
			'title' => __('Nemovitost', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'offer' => [
			'title' => __('Nabídka', 'cms_ve'),
		],
		'service' => [
			'title' => __('Služby', 'cms_ve'),
		],
		'home' => [
			'title' => __('Úvodní stránka', 'cms_ve'),
			'page' => 'home',
		],
		'thx' => [
			'title' => __('Poděkování', 'cms_ve'),
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
					'page' => 'offer',
				],
				[
					'type' => 'page',
					'page' => 'blog',
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
						'color1' => '#b8a528',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
