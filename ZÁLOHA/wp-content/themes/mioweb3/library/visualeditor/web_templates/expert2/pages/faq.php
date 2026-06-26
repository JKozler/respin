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
						'content' => '<p style="text-align: center;">' . __('NA CO SE NEJČASTĚJI PTÁTE', 'cms_ve') . '</p>',
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
						'content' => '<p style="text-align: center;">' . __('Zde najdete nejčastější otázky našich zákazníků.<br />Pokud však nenajdete odpověď na svou otázku, tak nám napište.', 'cms_ve') . '</p>',
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
						'type' => 'faq',
						'content' => '',
						'style' => [
							'faqs' => [
								'0' => [
									'question' => __('Otázka', 'cms_ve') . '. Lorem ipsum dolor sit amet.',
									'answer' => __('Odpověď', 'cms_ve') . '. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam. Nullam feugiat, turpis at pulvinar vulputate, erat libero tristique tellus, nec bibendum odio risus sit amet ante.',
								],
								'1' => [
									'question' => __('Otázka', 'cms_ve') . '. Lorem ipsum dolor sit amet.',
									'answer' => __('Odpověď', 'cms_ve') . '. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam. Nullam feugiat, turpis at pulvinar vulputate, erat libero tristique tellus, nec bibendum odio risus sit amet ante.',
								],
							],
							'cols' => 'two',
							'style' => '1',
							'background-color' => '#e6e6e6',
							'question_font' => [
								'font-size' => '20',
								'font-family' => '',
								'weight' => '',
								'color' => '',
							],
							'answer_font' => [
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
		'title' => __('Otázky a odpovědi', 'cms_ve'),
		'slug' => __('faq', 'cms_ve'),
		'theme' => 'page/1/',
	],
	'setting' => [],
	'layer' => base64_encode(serialize($temp_layer)),
];
