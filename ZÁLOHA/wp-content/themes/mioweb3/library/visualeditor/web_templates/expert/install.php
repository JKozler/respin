<?php
$web = [
	'title' => __('Jednoduchý expertní web', 'cms_ve'),
	'desc' => __('Jednoduchý expertní web pro vaše podnikání. Obsahuje úvodní stránku, blog, reference, obsah zdarma, příběh, seznam služeb nebo produktů, obsahovou stránku, kontaktní stránku, dotazník a děkovačku.', 'cms_ve'),
	'demo' => 'https://demo-expert.mioweb.cz',
	'tags' => ['expert'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/expert/thumb.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'home' => [
			'title' => __('Úvodní stránka', 'cms_ve'),
			'page' => 'home',
		],
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
		],
		'sq' => [
			'title' => __('Zdarma', 'cms_ve'),
		],
		'service' => [
			'title' => __('Služby', 'cms_ve'),
		],
		'testimonials' => [
			'title' => __('Reference', 'cms_ve'),
		],
		'story' => [
			'title' => __('Příběh', 'cms_ve'),
		],
		'questions' => [
			'title' => __('Dotazník', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'content' => [
			'title' => __('Obsah', 'cms_ve'),
		],
		'thx' => [
			'title' => __('Poděkování', 'cms_ve'),
		],
	],
	// content blocks
	'content_blocks' => [
		'footer' => [],
	],
	// menus
	'menus' => [
		'main_header' => [
			'name' => __('Hlavní menu', 'cms_ve'),
			'items' => [
				[
					'type' => 'page',
					'page' => 'home',
				],
				[
					'type' => 'page',
					'page' => 'sq',
				],
				[
					'type' => 'page',
					'page' => 'service',
				],
				[
					'type' => 'page',
					'page' => 'testimonials',
				],
				[
					'type' => 'page',
					'page' => 'story',
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
						'color1' => '#e4960e',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
	// color variants
	/*
	'variants'=>array(
		'blue'=>array(
			'color'=>'#41a0a9',
			'thumb'=>get_template_directory_uri().'/library/visualeditor/web_templates/expert/variants/blue.jpg',
		),
		'green'=>array(
			'color'=>'#259072',
			'thumb'=>get_template_directory_uri().'/library/visualeditor/web_templates/expert/variants/green.jpg',
		),
		'blue2'=>array(
			'color'=>'#1f547e',
			'thumb'=>get_template_directory_uri().'/library/visualeditor/web_templates/expert/variants/blue2.jpg',
		),
		'purple'=>array(
			'color'=>'#524e73',
			'thumb'=>get_template_directory_uri().'/library/visualeditor/web_templates/expert/variants/purple.jpg',
		),
		'pink'=>array(
			'color'=>'#c0466f',
			'thumb'=>get_template_directory_uri().'/library/visualeditor/web_templates/expert/variants/pink.jpg',
		)
	),*/
];
