<?php
$web = [
	'title' => __('Pizzerie', 'cms_ve'),
	'desc' => '',
	'demo' => 'http://demo-pizzerie.mioweb.cz',
	'tags' => ['restaurant'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/pizzerie/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/pizzerie/thumb.jpg',
	'home' => 'page',
	'pages' => [
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
		],
		'kontakt' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'menu' => [
			'title' => __('Menu', 'cms_ve'),
		],
		'o-nas' => [
			'title' => __('O nás', 'cms_ve'),
		],
		'vitejte' => [
			'title' => __('Vítejte', 'cms_ve'),
			'page' => 'home',
		],
	],
	'menus' => [
		'main' => [
			'name' => __('Hlavní menu', 'cms_ve'),
			'items' => [
				'0' => [
					'type' => 'page',
					'page' => 'vitejte',
				],
				'1' => [
					'type' => 'page',
					'page' => 'menu',
				],
				'2' => [
					'type' => 'page',
					'page' => 'o-nas',
				],
				'3' => [
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
