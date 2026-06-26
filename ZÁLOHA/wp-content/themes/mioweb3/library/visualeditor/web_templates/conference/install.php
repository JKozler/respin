<?php
$web = [
	'title' => __('Konference', 'cms_ve'),
	'desc' => __('Šablona je vhodná pro výstavbu jednostránkového osobního webu a portfolia.', 'cms_ve'),
	'demo' => 'https://demo-conference.mioweb.cz',
	'tags' => ['event'],
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/conference/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/conference/thumb_en.jpg',
	'home' => 'page',
	// list of web pages
	'pages' => [
		'home' => [
			'title' => __('Stránka konference', 'cms_ve'),
			'page' => 'home',
		],
	],
	// menus
	'menus' => [
		'main' => [
			'name' => __('Hlavní menu', 'cms_ve'),
			'items' => [
				[
					'type' => 'link',
					'link' => '#uvod',
					'title' => 'Úvod',
				],
				[
					'type' => 'link',
					'link' => '#okonferenci',
					'title' => 'O konferenci',
				],
				[
					'type' => 'link',
					'link' => '#program',
					'title' => 'Program',
				],
				[
					'type' => 'link',
					'link' => '#recnici',
					'title' => 'Řečníci',
				],
				[
					'type' => 'link',
					'link' => '#rezervovat',
					'title' => 'Rezervovat místo',
				],
				[
					'type' => 'link',
					'link' => '#kontakt',
					'title' => 'Kontakt',
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
						'color1' => '#d12e3e',
						'color2' => '',
					],

				],
				'search' => ['title' => __('Hledat', 'cms_ve')],
				'categories' => ['title' => __('Kategorie', 'cms_ve')],
			],
		],
	],
];
