<?php
$temp_layer = [
	'0' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#158ebf',
				'color2' => '',
				'transparency' => '100',
			],
			'background_image' => [
				'position' => 'center center',
				'repeat' => 'no-repeat',
				'image' => '',
				'imageid' => '',
				'pattern' => '',
			],
			'font' => [
				'font-size' => '',
				'font-family' => '',
				'weight' => '',
				'color' => '#ffffff',
			],
			'link_color' => '#ffffff',
			'type' => 'basic',
			'padding_top' => '80',
			'padding_bottom' => '60',
			'padding_left' => [
				'size' => '',
				'unit' => 'px',
			],
			'padding_right' => [
				'size' => '',
				'unit' => 'px',
			],
			'margin_t' => [
				'size' => '',
			],
			'margin_b' => [
				'size' => '',
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
			'min-height' => '',
			'css_class' => '',
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'title',
						'content' => '<p style="text-align: center;">CO O NÁS ŘÍKAJÍ KLIENTI</p>',
						'style' => [
							'font' => [
								'font-size' => '50',
								'font-family' => '',
								'weight' => '',
								'line-height' => '1.2',
								'color' => '#ffffff',
								'text-shadow' => 'none',
							],
							'style' => '1',
							'border' => [
								'size' => '1',
								'color' => '#d5d5d5',
							],
							'background-color' => [
								'color1' => '#efefef',
								'color2' => '',
								'transparency' => '100',
							],
							'align' => 'center',
						],
						'config' => [
							'max_width' => '',
							'margin_top' => '0',
							'margin_bottom' => '5',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'text',
						'content' => '<p style="text-align: center;">Jsme rádi za takto nadšené klienty ...</p>',
						'style' => [
							'font' => [
								'font-size' => '',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
							],
							'li' => '',
							'style' => '1',
							'p-background-color' => [
								'color1' => '#e8e8e8',
								'color2' => '',
								'transparency' => '100',
							],
						],
					],
					'2' => [
						'type' => 'like',
						'content' => [
							'page' => '',
							'link' => '',
						],
						'style' => [
							'layout' => 'button_count',
							'scheme' => 'light',
							'setting' => [
								'share' => 'share',
							],
							'align' => 'center',
						],
					],
				],
			],
		],
	],
	'1' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#ffffff',
				'color2' => '',
				'transparency' => '100',
			],
			'background_image' => [
				'position' => 'center center',
				'repeat' => 'no-repeat',
				'image' => '',
				'imageid' => '',
				'pattern' => '',
			],
			'font' => [
				'font-size' => '',
				'font-family' => '',
				'weight' => '',
				'color' => '',
			],
			'link_color' => '',
			'type' => 'basic',
			'padding_top' => '80',
			'padding_bottom' => '60',
			'padding_left' => [
				'size' => '',
				'unit' => 'px',
			],
			'padding_right' => [
				'size' => '',
				'unit' => 'px',
			],
			'margin_t' => [
				'size' => '',
			],
			'margin_b' => [
				'size' => '',
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
			'min-height' => '',
			'css_class' => '',
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'testimonials',
						'content' => '',
						'style' => [
							'testimonials' => [
								'0' => [
									'text' => 'Byl jsem velmi spokojen.',
									'name' => 'Honza Novák',
									'company' => 'majitel společnosti ABC s.r.o.',
									'image' => [
										'image' => MW_IMAGE_LIBRARY . 'misc/face-m.jpg',
										'imageid' => '',
									],
								],
								'1' => [
									'text' => 'S Davidem Kiršem se mi dobře spolupracovalo. Mohu doporučit :)',
									'name' => 'Petra Svobodová',
									'company' => 'Marketing konzultant',
									'image' => [
										'image' => MW_IMAGE_LIBRARY . 'misc/face-w.jpg',
										'imageid' => '',
									],
								],
							],
							'cols' => 'two',
							'style' => '2',
							'font' => [
								'font-size' => '15',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
							],
							'font-author' => [
								'font-size' => '',
								'font-family' => '',
								'weight' => '',
								'color' => '',
							],
						],
					],
				],
			],
		],
	],
];

$page = [
	'page' => [
		'title' => __('Reference', 'cms_ve'),
		'slug' => __('testimonials', 'cms_ve'),
		'theme' => 'page/1/',
	],
	'setting' => [],
	'layer' => base64_encode(serialize($temp_layer)),
];
