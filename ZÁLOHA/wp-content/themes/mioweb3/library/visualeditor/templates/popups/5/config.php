<?php
$temp_layer = [
	'0' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#3e6e7a',
				'transparency1' => '1.00',
				'rgba1' => 'rgba(62, 110, 122, 1)',
				'color2' => '',
				'transparency2' => '100',
				'rgba2' => '',
			],
			'background_setting' => 'image',
			'background_image' => [
				'position' => '',
				'image' => '',
				'imageid' => '',
				'pattern' => '',
				'tablet' => [
					'position' => '',
					'image' => '',
					'imageid' => '',
					'pattern' => '',
				],
				'mobile' => [
					'position' => '',
					'image' => '',
					'imageid' => '',
					'pattern' => '',
				],
				'overlay_color' => [
					'color' => '#000000',
					'transparency' => '0.5',
					'rgba' => 'rgba(0,0,0,0.5)',
				],
				'efect' => '',
				'repeat' => 'no-repeat',
				'cover' => '1',
			],
			'slider_overlay_color' => [
				'color' => '',
				'transparency' => '70',
				'rgba' => '',
			],
			'video_type' => 'iframe',
			'video_url' => '',
			'background_video_mp4' => '',
			'background_video_webm' => '',
			'background_video_ogg' => '',
			'video_setting' => [
				'is_saved' => '1',
			],
			'video_overlay_color' => [
				'color' => '',
				'transparency' => '70',
				'rgba' => '',
			],
			'row_height' => 'default',
			'min-height' => '100',
			'arrow_color' => '#fff',
			'content_align' => 'center',
			'text' => 'auto',
			'font' => [
				'font-family' => '',
				'weight' => '',
				'font-size' => '',
				'color' => '',
			],
			'link_color' => '',
			'type' => 'full',
			'row_padding' => 'none',
			'padding_top' => '50',
			'tablet' => [
				'padding_top' => '',
				'padding_bottom' => '',
				'padding_left' => [
					'size' => '',
					'unit' => 'px',
				],
				'padding_right' => [
					'size' => '',
					'unit' => 'px',
				],
			],
			'mobile' => [
				'padding_top' => '',
				'padding_bottom' => '',
				'padding_left' => [
					'size' => '',
					'unit' => 'px',
				],
				'padding_right' => [
					'size' => '',
					'unit' => 'px',
				],
			],
			'padding_bottom' => '50',
			'padding_left' => [
				'size' => '',
				'unit' => 'px',
			],
			'padding_right' => [
				'size' => '',
				'unit' => 'px',
			],
			'margin_top' => '',
			'margin_bottom' => '',
			'border-top' => [
				'size' => '',
				'style' => 'solid',
				'color' => '',
			],
			'border-bottom' => [
				'size' => '',
				'style' => 'solid',
				'color' => '',
			],
			'css_class' => '',
			'row_anchor' => '',
			'delay' => '',
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [
					'0' => [
						'style' => [
							'image' => [
								'position' => '',
								'image' => MW_IMAGE_LIBRARY . 'misc/street.jpeg',
								'imageid' => '',
							],
							'title' => 'Lorem ipsum dolor sit amet',
							'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sodales ipsum ut leo condimentum, sed auctor ipsum.',
							'show_button' => 'basic',
							'button_text' => __('Více informací', 'cms_ve'),
							'button_link' => [
								'page' => '',
								'link' => '',
							],
							'visual_style' => '2',
							'background_color' => '#3e6e7a',
							'overlay_color' => [
								'color' => '#000000',
								'transparency' => '0.5',
								'rgba' => 'rgba(0,0,0,0.5)',
							],
							'image_ratio' => '34',
							'style' => 'two',
							'align' => 'right',
							'text-align' => 'left',
							'valign' => 'center',
							'font' => [
								'font-family' => '',
								'weight' => '',
								'font-size' => '38',
								'tablet' => [
									'font-size' => '',
								],
								'mobile' => [
									'font-size' => '',
								],
								'color' => '',
								'line-height' => '',
							],
							'font_text' => [
								'font-family' => '',
								'weight' => '',
								'font-size' => '',
								'tablet' => [
									'font-size' => '',
								],
								'mobile' => [
									'font-size' => '',
								],
								'color' => '',
								'line-height' => '1.7',
							],
							'button' => [
								'style' => 'inverse',
								'button_size' => 'medium',
								'custom_size' => '18',
							],
							'mw30' => '1',
						],
						'type' => 'image_text',
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
	],
];


$config['setting'] = [
	've_popup' => [
		'width' => ['size' => '800', 'unit' => 'px'],
	],
];
$config['layer'] = base64_encode(serialize($temp_layer));
$config['config'] = [];
