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
				'color1' => '#1a1a1a',
				'transparency1' => '1',
				'rgba1' => 'rgba(26, 26, 26, 1)',
				'color2' => '',
				'transparency2' => '1',
				'rgba2' => '',
			],
			'background_image' => [
				'position' => '50% 50%',
				'repeat' => 'no-repeat',
				'image' => '',
				'efect' => '',
				'overlay_color' => [
					'color' => '#000000',
					'transparency' => '0.5',
					'rgba' => 'rgba(0,0,0,0.5)',
				],
				'cover' => '1',
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
			'padding_top' => '100',
			'padding_bottom' => '100',
			'video_type' => 'custom',
			'content_align' => 'top',
			'row_height' => '',
			'text' => 'auto',
			'row_padding' => 'big',
			'margin_top' => '',
			'margin_bottom' => '',
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'title',
						'style' => [
							'font' => [
								'font-size' => '40',
								'tablet' => [
									'font-size' => '',
								],
								'mobile' => [
									'font-size' => '',
								],
								'color' => '',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'letter-spacing' => '',
								'text-shadow' => 'none',
							],
							'style' => '1',
							'border' => [
								'size' => '1',
								'style' => 'solid',
								'color' => '#d5d5d5',
							],
							'background-color' => [
								'color1' => '#e8e8e8',
								'transparency1' => '1',
								'rgba1' => 'rgba(232,232,232,1)',
							],
							'decoration-color' => '#158ebf',
							'align' => 'center',
							'content' => '<h1 style="text-align: center;">' . __('Jednoduchá stránka s videem', 'cms_ve') . '</h1>',
							'mw30' => '1',
						],
						'config' => [
							'margin_top' => '0',
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
							'margin_bottom' => '13',
							'max_width' => '',
							'element_align' => 'center',
							'animate' => '',
							'id' => '',
							'class' => '',
							'delay' => '',
						],
					],
					'1' => [
						'type' => 'title',
						'style' => [
							'font' => [
								'font-size' => '18',
								'font-family' => '',
								'weight' => 'normal',
								'color' => '',
								'text-shadow' => 'none',
							],
							'mw30' => '1',
							'content' => '<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
							'background-color' => [
								'color1' => '#e8e8e8',
								'transparency1' => '1',
								'rgba1' => 'rgba(232,232,232,1)',
							],
						],
						'config' => [
							'max_width' => '',
							'margin_top' => '0',
							'margin_bottom' => '40',
							'delay' => '',
							'class' => '',
						],
					],
					'2' => [
						'type' => 'video',
						'style' => [
							'max-width' => '',
							'code' => '',
							'align' => 'center',
							'mw30' => '1',
							'content' => '',
						],
					],
					'3' => [
						'type' => 'share',
						'style' => [
							'show' => [
								'facebook' => 'facebook',
								'twitter' => 'twitter',
								'google' => 'google',
							],
							'scheme' => '1',
							'mw30' => '1',
							'content' => '',
						],
					],
				],
			],
		],
	],
];
$config['layer'] = base64_encode(serialize($temp_layer));
$config['setting'] = [];
$config['config'] = [];
