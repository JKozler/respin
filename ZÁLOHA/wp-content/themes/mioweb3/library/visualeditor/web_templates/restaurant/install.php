<?php
$web = [
	'title' => __('Restaurace', 'cms_ve'),
	'desc' => '',
	'demo' => 'https://demo-restaurant.mioweb.cz',
	'tags' => ['restaurant'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/restaurant/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/restaurant/thumb.jpg',
	'home' => 'page',
	'pages' => [
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
		],
		'galerie' => [
			'title' => __('Galerie', 'cms_ve'),
		],
		'kontakt' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'nase-menu' => [
			'title' => __('Naše menu', 'cms_ve'),
		],
		'o-restauraci' => [
			'title' => 'O restauraci',
		],
		'uvod' => [
			'title' => __('Úvod', 'cms_ve'),
			'page' => 'home',
		],
	],
	'menus' => [
		'main' => [
			'name' => __('Hlavní menu', 'cms_ve'),
			'items' => [
				'0' => [
					'type' => 'page',
					'page' => 'uvod',
				],
				'1' => [
					'type' => 'page',
					'page' => 'o-restauraci',
				],
				'2' => [
					'type' => 'page',
					'page' => 'nase-menu',
				],
				'3' => [
					'type' => 'page',
					'page' => 'galerie',
				],
				'4' => [
					'type' => 'page',
					'page' => 'kontakt',
				],
			],
		],
	],
	'content_blocks' => [
		'footer' => [],
	],
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
						'color1' => '#c42040',
						'color2' => '',
					],
				],
				'search' => [
					'title' => __('Hledat', 'cms_ve'),
				],
				'categories' => [
					'title' => __('Kategorie', 'cms_ve'),
				],
			],
		],
	],
];
