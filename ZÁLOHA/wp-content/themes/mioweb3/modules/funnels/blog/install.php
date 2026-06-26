<?php
$web = [
	'title' => __('Jednoduchý blog', 'cms_ve'),
	'desc' => __('Tato šablona je dobrý začátek pro vytvoření vašeho blogu. Začněte jednoduchým blogem a postupně ho doplňujte o další funkcionalitu.', 'cms_ve'),
	'demo' => 'http://demo-blog.mioweb.cz',
	'tags' => ['blog', 'personal'],
	'lite' => true,
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/blog/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/blog/thumb_en.jpg',
	'home' => 'posts',
	// list of web pages
	'pages' => [
		'about' => [
			'title' => __('O mně', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
		],
	],
	// menus
	'menus' => [
		'main' => [
			'name' => __('Hlavní menu', 'cms_ve'),
			'items' => [
				[
					'type' => 'link',
					'title' => __('Blog', 'cms_ve'),
					'link' => get_home_url(),
				],
				[
					'type' => 'page',
					'page' => 'about',
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
			'image' => MW_IMAGE_LIBRARY . 'gallery/bwtunel.jpeg',
		],
		'p2' => [
			'image' => MW_IMAGE_LIBRARY . 'gallery/bwroom.jpeg',
		],
		'p3' => [
			'image' => MW_IMAGE_LIBRARY . 'gallery/bwtown.jpg',
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
