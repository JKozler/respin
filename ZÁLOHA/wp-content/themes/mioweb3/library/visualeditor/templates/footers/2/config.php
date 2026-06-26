<?php
$temp_layer = [
	'0' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#262626',
				'transparency1' => '100',
				'rgba1' => 'rgba(38, 38, 38, 1)',
				'color2' => '',
				'transparency2' => '100',
				'rgba2' => '',
			],
			'background_setting' => 'image',
			'background_image' => [
				'position' => 'center center',
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
				'cover' => '1',
				'overlay_color' => [
					'color' => '#000000',
					'transparency' => '0.5',
					'rgba' => 'rgba(0,0,0,0.5)',
				],
				'efect' => '',
				'repeat' => 'no-repeat',
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
			'type' => 'basic',
			'row_padding' => 'big',
			'padding_top' => '100',
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
			'padding_bottom' => '100',
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
				'size' => '0',
				'style' => 'solid',
				'color' => '',
			],
			'border-bottom' => [
				'size' => '0',
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
						'type' => 'title',
						'style' => [
							'font' => [
								'font-size' => '22',
								'font-family' => '',
								'weight' => '',
								'line-height' => '1.2',
								'color' => '#ffffff',
								'text-shadow' => 'none',
							],
							'content' => '<p style="text-align: center;">' . __('Kontaktujte nás', 'cms_ve') . '</p>',
							'mw30' => '1',
							'background-color' => [
								'color1' => '#e8e8e8',
								'transparency1' => '1',
								'rgba1' => 'rgba(232,232,232,1)',
							],
						],
						'config' => [
							'max_width' => '',
							'margin_top' => '0',
							'margin_bottom' => '25',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'text',
						'style' => [
							'font' => [
								'font-family' => '',
								'weight' => '',
								'font-size' => '',
								'color' => '',
								'line-height' => '',
							],
							'style' => '1',
							'p-background-color' => [
								'color' => '#e8e8e8',
								'transparency' => '100',
								'rgba' => 'rgba(232,232,232,1)',
							],
							'content' => '<p style="text-align: center;">' . __('Pokud máte dotaz kontaktujte nás:<br />(+420) 777 123456 nebo na email@email.cz', 'cms_ve') . '</p>',
							'li' => '',
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
							'margin_bottom' => '15',
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


$config['setting'] = [];
$config['layer'] = base64_encode(serialize($temp_layer));
$config['config'] = [];
