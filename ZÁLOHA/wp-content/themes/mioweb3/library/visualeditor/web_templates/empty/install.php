<?php
$web = [
	'title' => __('Prázdný web', 'cms_ve'),
	'desc' => __('Předinstalovaný web bude obsahovat pouze stránku „Již brzy“ a blog. Tato instalace je vhodná pro vytvoření vlastního webu.', 'cms_ve'),
	'demo' => 'https://demo-empty.mioweb.cz',
	'tags' => ['empty'],
	'lite' => true,
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/empty/thumb.jpg',
	'thumb_en' => get_template_directory_uri() . '/library/visualeditor/web_templates/empty/thumb_en.jpg',
	'home' => 'page',
	'pages' => [
		'comming' => [
			'title' => __('Již brzy', 'cms_ve'),
			'page' => 'home',
		],
		'blog' => [
			'title' => __('Blog', 'cms_ve'),
			'page' => 'blog',
		],
	],
];
