<?php
$web = [
	'title' => __('Lektorka Jógy', 'cms_ve'),
	'desc' => __('Osobní web pro lektorku jógy.', 'cms_ve'),
	'demo' => 'https://demo-yoga.mioweb.cz',
	'tags' => ['fitness', 'personal'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/yoga/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/yoga/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'story' => [
			'title' => __('O mně', 'cms_ve'),
		],
		'free' => [
			'title' => __('Zdarma', 'cms_ve'),
		],
		'lessons' => [
			'title' => __('Lekce', 'cms_ve'),
		],
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
		],
		'home' => [
			'title' => __('Úvod', 'cms_ve'),
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
					'page' => 'free',
				],
				[
					'type' => 'page',
					'page' => 'lessons',
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
	// posts
	'posts' => [
		'p1' => [
			'image' => MW_IMAGE_LIBRARY . 'webs/yoga/yoga-pic1.jpg',
		],
		'p2' => [
			'image' => MW_IMAGE_LIBRARY . 'webs/yoga/yoga-pic2.jpg',
		],
		'p3' => [
			'image' => MW_IMAGE_LIBRARY . 'webs/yoga/yoga-pic3.jpg',
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
						'color1' => '#d67cb8',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
