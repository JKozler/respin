<?php
$web = [
	'title' => __('Interierový designer', 'cms_ve'),
	'desc' => __('Šablona je vhodná pro vytvoření osobního nebo firemního webu interierového designu, ale jiných podobných oborů jako zahradní architekt atd.', 'cms_ve'),
	'demo' => 'https://demo-interier.mioweb.cz',
	'tags' => ['personal', 'business'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/interier/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/interier/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'story' => [
			'title' => __('O nás', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'project' => [
			'title' => __('Stránka projektu', 'cms_ve'),
		],
		'mywork' => [
			'title' => __('Projekty', 'cms_ve'),
		],
		'home' => [
			'title' => __('Úvodní stránka', 'cms_ve'),
			'page' => 'home',
		],
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
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
					'page' => 'mywork',
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
						'color1' => '#a89b83',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
