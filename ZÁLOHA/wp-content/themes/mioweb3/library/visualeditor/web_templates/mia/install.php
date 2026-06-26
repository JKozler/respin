<?php
$web = [
	'title' => __('Katka', 'cms_ve'),
	'tags' => ['tuts'],
	'lite' => true,
	'modules' => ['blog'],
	'thumb' => get_template_directory_uri() . '/library/visualeditor/web_templates/mia/thumb.jpg',
	'home' => 'page',
	'pages' => [
		'home' => [
			'title' => __('Katka', 'cms_ve'),
			'page' => 'home',
		],
	],
	'images_to_media' => [
		'mia' => [
			'url' => MW_IMAGE_LIBRARY . 'onboarding/mia4.jpg',
			'width' => '1002',
			'height' => '669',
		],
	],
];
