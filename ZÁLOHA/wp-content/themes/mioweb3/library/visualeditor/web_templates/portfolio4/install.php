<?php
$web = [
	'title' => __('Jednostránkové portfolio', 'cms_ve'),
	'desc' => __('Šablona je vhodná pro výstavbu jednostránkového osobního webu a portfolia.', 'cms_ve'),
	'demo' => 'https://demo-portfolio4.mioweb.cz',
	'tags' => ['personal', 'portfolio'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/portfolio4/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/portfolio4/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
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
					'type' => 'link',
					'link' => '#wrapper',
					'title' => 'Úvodem',
				],
				[
					'type' => 'link',
					'link' => '#row_1',
					'title' => 'Služby',
				],
				[
					'type' => 'link',
					'link' => '#row_2',
					'title' => 'O mně',
				],
				[
					'type' => 'link',
					'link' => '#row_4',
					'title' => 'Moje práce',
				],
				[
					'type' => 'link',
					'link' => '#row_6',
					'title' => 'Kontakt',
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
						'color1' => '#cf731d',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
