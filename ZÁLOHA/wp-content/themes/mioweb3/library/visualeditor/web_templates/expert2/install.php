<?php
$web = [
	'title' => __('Expertní web', 'cms_ve'),
	'desc' => __('Expertní web obsahující úvodní stránku, blog, reference, magnet, příběh, seznam služeb nebo produktů, objednávku, faq, webinář, kontaktní stránku, dotazník a děkovačku.', 'cms_ve'),
	'demo' => 'https://demo-expert2.mioweb.cz',
	'tags' => ['expert'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/expert2/thumb.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'home' => [
			'title' => __('Vítejte', 'cms_ve'),
			'page' => 'home',
		],
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
		],
		'magnet' => [
			'title' => __('Vstup', 'cms_ve'),
		],
		'magnetthx' => [
			'title' => __('Děkujeme', 'cms_ve'),
		],
		'products' => [
			'title' => __('Produkty/služby', 'cms_ve'),
		],
		'product' => [
			'title' => __('Můj produkt nebo služba', 'cms_ve'),
		],
		'order' => [
			'title' => __('Objednávka', 'cms_ve'),
		],
		'orderthx' => [
			'title' => __('Děkujeme za objednávku', 'cms_ve'),
		],
		'aboutme' => [
			'title' => __('O mně', 'cms_ve'),
		],
		'testimonials' => [
			'title' => __('Reference', 'cms_ve'),
		],
		'faq' => [
			'title' => __('Otázky a odpovědi', 'cms_ve'),
		],
		'questions' => [
			'title' => __('Hodnocení', 'cms_ve'),
		],
		'questionsthx' => [
			'title' => __('Děkujeme', 'cms_ve'),
		],
		'webinar_landing' => [
			'title' => __('Přednáška', 'cms_ve'),
		],
		'webinar_broadcast' => [
			'title' => __('Vysílání', 'cms_ve'),
		],
		'contact' => [
			'title' => __('Kontakt', 'cms_ve'),
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
					'page' => 'magnet',
					'target' => '_blank',
				],
				[
					'type' => 'page',
					'page' => 'blog',
				],
				[
					'type' => 'page',
					'page' => 'products',
				],
				[
					'type' => 'page',
					'page' => 'testimonials',
				],
				[
					'type' => 'page',
					'page' => 'aboutme',
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
	], /*
	// color variants
	'variants'=>array(
		'blue'=>array(
			'color'=>'#41a0a9',
			'thumb'=>get_template_directory_uri().'/library/visualeditor/web_templates/ea_web/variants/blue.jpg',
		),
		'green'=>array(
			'color'=>'#259072',
			'thumb'=>get_template_directory_uri().'/library/visualeditor/web_templates/ea_web/variants/green.jpg',
		),
	),  */
];
