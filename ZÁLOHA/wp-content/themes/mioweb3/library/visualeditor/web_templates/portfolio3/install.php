<?php
$web = [
	'title' => __('Portfolio tmavé', 'cms_ve'),
	'desc' => __('Šablona je vhodná pro výstavbu osobního webu a portfolia pro kreativce - designéry, webdesignéry, fotografy a další.', 'cms_ve'),
	'demo' => 'https://demo-portfolio3.mioweb.cz',
	'tags' => ['personal', 'portfolio', 'photographer'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/portfolio3/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/portfolio3/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'story' => [
			'title' => __('O mně', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'project' => [
			'title' => __('Stránka projektu', 'cms_ve'),
		],
		'home' => [
			'title' => __('Moje práce', 'cms_ve'),
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
						'color1' => '#c90a30',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
