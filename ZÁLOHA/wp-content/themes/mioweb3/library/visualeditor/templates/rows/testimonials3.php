<?php
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
							'line-height' => '1.2',
							'letter-spacing' => '0',
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
						'content' => '<p style="text-align: center;">' . __('Reference', 'cms_ve') . '</p>',
						'mw30' => '1',
					],
					'type' => 'title',
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
						'margin_bottom' => '25',
						'max_width' => '680',
						'element_align' => 'center',
						'animate' => '',
						'id' => '',
						'class' => '',
						'delay' => '',
					],
				],
				'1' => [
					'style' => [
						'font' => [
							'font-family' => '',
							'weight' => '',
							'font-size' => '18',
							'color' => '#808080',
							'line-height' => '',
						],
						'style' => '1',
						'p-background-color' => [
							'color1' => '#e8e8e8',
							'transparency1' => '1',
							'rgba1' => 'rgba(232,232,232,1)',
						],
						'content' => '<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In volutpat volutpat blandit.</p>',
						'li' => '',
						'mw30' => '1',
					],
					'type' => 'text',
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
						'margin_bottom' => '70',
						'max_width' => '515',
						'element_align' => 'center',
						'animate' => '',
						'id' => '',
						'class' => '',
						'delay' => '',
					],
				],
				'2' => [
					'type' => 'testimonials',
					'style' => [
						'testimonials' => [
							'0' => [
								'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec pellentesque velit a dolor fermentum, eget viverra risus finibus. Nam libero quam, imperdiet at magna vel, tincidunt elementum blandit quam tincidunt sem. ',
								'name' => __('Petra', 'cms_ve'),
								'company' => __('Finanční poradce', 'cms_ve'),
								'image' => [
									'position' => '',
									'image' => MW_IMAGE_LIBRARY . 'misc/face-w.jpg',
									'imageid' => '',
								],
							],
							'1' => [
								'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec pellentesque velit a dolor fermentum, eget viverra risus finibus. Nam libero quam, imperdiet at magna vel, tincidunt elementum blandit quam tincidunt sem. ',
								'name' => __('Honza', 'cms_ve'),
								'company' => __('Informatik', 'cms_ve'),
								'image' => [
									'position' => '',
									'image' => MW_IMAGE_LIBRARY . 'misc/face-m.jpg',
									'imageid' => '',
								],
							],
							'2' => [
								'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec pellentesque velit a dolor fermentum, eget viverra risus finibus. Nam libero quam, imperdiet at magna vel, tincidunt elementum blandit quam tincidunt sem. ',
								'name' => __('Jana', 'cms_ve'),
								'company' => __('Účetní', 'cms_ve'),
								'image' => [
									'position' => '',
									'image' => MW_IMAGE_LIBRARY . 'misc/face-w2.jpg',
									'imageid' => '',
								],
							],
						],
						'cols' => 'three',
						'style' => '7',
						'background_set' => [
							'corner' => '',
							'shadow' => '1',
						],
						'image_size' => '2',
						'font' => [
							'font-size' => '',
							'color' => '',
						],
						'font-author' => [
							'font-size' => '',
							'color' => '',
						],
						'miocarousel_setting' => [
							'animation' => 'fade',
							'autoplay' => '1',
							'color_scheme' => '',
							'delay' => '3500',
							'speed' => '1000',
						],
						'mw30' => '1',
					],
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
						'max_width' => '',
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
