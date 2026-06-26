<?php
$temp_layer = [
	'0' => [
		'class' => '',
		'style' => [
			'font' => [
				'font-size' => '',
				'font-family' => '',
				'weight' => '',
				'color' => '',
			],
			'link_color' => '',
			'background_color' => [
				'color1' => '',
				'color2' => '',
				'transparency' => '1',
			],
			'background_image' => [
				'position' => 'center center',
				'repeat' => 'no-repeat',
				'image' => '',
				'pattern' => '0',
			],
			'border-top' => [
				'size' => '0',
				'style' => 'solid',
				'color' => '',
			],
			'border-bottom' => [
				'size' => '0',
				'style' => 'solid',
				'color' => '',
			],
			'padding_top' => '40',
			'padding_bottom' => '40',
			'padding_left' => [
				'size' => '',
				'unit' => 'px',
			],
			'padding_right' => [
				'size' => '',
				'unit' => 'px',
			],
			'type' => 'basic',
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [],
			],
		],
	],
];


$config['layer'] = base64_encode(serialize($temp_layer));
$config['config'] = [
	'delete_rows' => true,
];
