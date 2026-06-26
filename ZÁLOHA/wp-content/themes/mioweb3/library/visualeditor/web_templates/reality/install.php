<?php
$web = [
	'title' => __('Osobní web pro makléře', 'cms_ve'),
	'desc' => __('Šablona je vhodná pro výstavbu osobního webu pro makléře. Obsahuje nabídku nemovitostí, osobní příběh, reference, magnet pro sbírání kontaktů a vše důležité co by na takovém webu nemělo chybět.', 'cms_ve'),
	'demo' => 'https://demo-reality.mioweb.cz',
	'tags' => ['reality'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/reality/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/reality/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'product' => [
			'title' => __('Nemovitost', 'cms_ve'),
		],
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
		],
		'offer' => [
			'title' => __('Nabídka', 'cms_ve'),
		],
		'request' => [
			'title' => __('Poptávka', 'cms_ve'),
		],
		'service' => [
			'title' => __('Služby', 'cms_ve'),
		],
		'testimonials' => [
			'title' => __('Reference', 'cms_ve'),
		],
		'story' => [
			'title' => __('Můj příběh', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
		],
		'thx' => [
			'title' => __('Poděkování', 'cms_ve'),
		],

		'home' => [
			'title' => __('Úvodní stránka', 'cms_ve'),
			'page' => 'home',
		],
	],
	// content blocks
	'content_blocks' => [
		'footer' => [],
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
					'page' => 'offer',
				],
				[
					'type' => 'page',
					'page' => 'request',
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
						'color1' => '#c3ae75',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
