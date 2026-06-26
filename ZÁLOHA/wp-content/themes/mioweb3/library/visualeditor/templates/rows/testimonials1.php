<?php
$image = [
	2793 => MW_IMAGE_LIBRARY . 'misc/face-w.jpg',
];

$content = [
	'class' => '',
	'style' => [
		'background_color' => [
			'color1' => '#ffffff',
			'rgba1' => 'rgba(255,255,255,1)',
			'color2' => '',
			'rgba2' => '',
			'transparency1' => '1',
			'transparency2' => '1',
		],
		'background_image' => [
			'cover' => '1',
			'overlay_color' => [
				'color' => '#000000',
				'transparency' => '0.2',
				'rgba' => 'rgba(0, 0, 0, 0.2)',
			],
			'efect' => '',
		],
		'content_align' => 'top',
		'link_color' => '',
		'row_padding' => 'big',
		'font' => [
			'font-size' => '',
			'font-family' => '',
			'weight' => '',
			'color' => '',
		],
		'text' => 'auto',
		'video_type' => 'custom',
		'row_height' => '',
		'margin_top' => '',
		'margin_bottom' => '',
	],
	'content' => [
		'0' => [
			'type' => 'col-one',
			'class' => '',
			'content' => [
				'0' => [
					'style' => [
						'testimonials' => [
							'0' => [
								'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec egestas urna et ex molestie viverra. Etiam sollicitudin massa nulla, a malesuada nulla vulputate id.',
								'name' => __('Jméno Příjmení', 'cms_ve'),
								'company' => __('Pozice', 'cms_ve'),
								'image' => [
									'position' => '',
									'image' => $image[2793],
									'imageid' => '',
								],
							],
						],
						'cols' => 'one',
						'style' => '7',
						'background_set' => [
							'corner' => '',
							'shadow' => '1',
							'color' => '#ffffff',
						],
						'font' => [
							'font-size' => '20',
							'color' => '',
						],
						'font-author' => [
							'font-size' => '',
							'color' => '',
						],
						'miocarousel_setting' => [
							'animation' => 'fade',
							'color_scheme' => '',
							'delay' => '3500',
							'speed' => '1000',
						],
						'mw30' => '1',
					],
					'type' => 'testimonials',
					'config' => [
						'margin_top' => '',
						'tablet' => [
							'margin_top' => '',
							'margin_bottom' => '',
							'max_width' => '',
						],
						'mobile' => [
							'margin_top' => '',
							'margin_bottom' => '',
							'max_width' => '',
						],
						'margin_bottom' => '',
						'max_width' => '574',
						'element_align' => 'center',
						'animate' => '',
						'id' => '',
						'class' => '',
						'delay' => '',
					],
				],
			],
		],
	],
	'type' => 'basic',
];
