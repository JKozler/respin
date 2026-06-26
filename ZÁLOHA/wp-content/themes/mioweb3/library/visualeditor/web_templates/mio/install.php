<?php
$web = [
	'title' => __('Lukáš', 'cms_ve'),
	'tags' => ['tuts'],
	'lite' => true,
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/mio/thumb.jpg',
	'home' => 'page',
	'pages' => [
		'home' => [
			'title' => __('Lukáš', 'cms_ve'),
			'page' => 'home',
		],
	],
	'images_to_media' => [
		'mio' => [
			'url' => MW_IMAGE_LIBRARY . 'onboarding/mio4.jpg',
			'width' => '1003',
			'height' => '670',
		],
	],
];
