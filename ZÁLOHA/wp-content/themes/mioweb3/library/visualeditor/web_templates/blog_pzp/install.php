<?php
$web = [
	'title' => __('Osobní blog pro podnikatele z pláže', 'cms_ve'),
	'desc' => __('Speciální šablona určená jako výchozí bod pro účastníky kurzu podnikání z pláže.', 'cms_ve'),
	'demo' => 'https://demo-blog-pzp.mioweb.cz',
	'tags' => ['blog', 'personal'],
	'lite' => true,
	'modules' => ['blog'],
	'group' => ['plazova-platforma'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/blog_pzp/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/blog_pzp/thumb_en.jpg',
	'home' => 'posts',
	// list of web pages
	'pages' => [
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
		],
		'about' => [
			'title' => __('Můj příběh', 'cms_ve'),
		],
		'free' => [
			'title' => __('eBook zdarma', 'cms_ve'),
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
			],
		],
	],
	// posts
	'posts' => [
		'p1' => [
			'image' => MW_IMAGE_LIBRARY . 'gallery/tree-flowers.jpeg',
			'title' => __('Proč jsem se rozhodl/a psát blog?', 'cms_ve'),
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
				'cms_posts_widget' => [
					'title' => __('Nejnovější články', 'cms_ve'),
					'number' => 5,
					'show_date' => 1,
					'image' => 'thumbnail',
					'posts' => 'last',
				],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
		'article' => [
			'name' => __('S formulářem', 'cms_ve'),
			'desc' => '',
			'widgets' => [
				'cms_option_widget' => [
					'title' => __('Název eBooku', 'cms_ve'),
					'text' => __('Přitažlivý krátký text (max 2. věty), díky kterému se klient rozhodne stáhnout eBook.', 'cms_ve'),
					'button_text' => __('Stáhnout eBook ZDARMA', 'cms_ve'),
					'font' => [
						'font-size' => '18',
						'color' => '#ffffff',
					],
					'bg' => [
						'color1' => '#c42040',
						'color2' => '',
					],
				],
				'cms_posts_widget' => [
					'title' => __('Nejnovější články', 'cms_ve'),
					'number' => 5,
					'show_date' => 1,
					'image' => 'thumbnail',
					'posts' => 'last',
				],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
