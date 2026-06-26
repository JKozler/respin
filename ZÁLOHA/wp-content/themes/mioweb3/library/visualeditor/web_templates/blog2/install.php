<?php
$web = [
	'title' => __('Osobní blog s magnetem', 'cms_ve'),
	'desc' => __('Tato šablona je vhodným základem pro osobní nebo expertní blog.', 'cms_ve'),
	'demo' => 'https://demo-blog2.mioweb.cz',
	'tags' => ['blog', 'personal'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/blog2/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/blog2/thumb_en.jpg',
	'home' => 'posts',
	// list of web pages
	'pages' => [
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
		],
		'about' => [
			'title' => __('O mně', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'free' => [
			'title' => __('Zdarma', 'cms_ve'),
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
					'page' => 'free',
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
			'image' => MW_IMAGE_LIBRARY . 'gallery/tree-flowers.jpeg',
		],
		'p2' => [
			'image' => MW_IMAGE_LIBRARY . 'gallery/smiling-child.jpeg',
		],
		'p3' => [
			'image' => MW_IMAGE_LIBRARY . 'gallery/legs-window-car.jpeg',
		],
	],
	// sidebars
	'sidebars' => [
		'main' => [
			'name' => __('Hlavní', 'cms_ve'),
			'desc' => '',
			'widgets' => [
				'search' => ['title' => ''],
				'cms_option_widget' => [
					'title' => __('Ebook zdarma', 'cms_ve'),
					'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eget libero luctus, consectetur nulla eu, elementum metus.',
					'font' => [
						'font-size' => '20',
						'color' => '#ffffff',
					],
					'bg' => [
						'color1' => '#c42040',
						'color2' => '',
					],
				],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
				'cms_posts_widget' => [
					'title' => __('Nejnovější články', 'cms_ve'),
					'number' => 5,
					'show_date' => 1,
					'image' => 'mio_columns_5',
					'posts' => 'last',
				],
			],
		],
	],
];
