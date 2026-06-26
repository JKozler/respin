<?php
$temp_layer = [
	'0' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#ffffff',
				'transparency1' => '1',
				'rgba1' => 'rgba(255,255,255,1)',
				'color2' => '',
				'transparency2' => '1',
				'rgba2' => '',
			],
			'background_setting' => 'image',
			'background_image' => [
				'position' => '50% 30%',
				'image' => MW_IMAGE_LIBRARY . 'webs/restaurant/steak2.jpg',
				'imageid' => '',
				'pattern' => '',
				'tablet' => [
					'position' => '50% 50%',
					'image' => '',
					'imageid' => '',
				],
				'mobile' => [
					'position' => '50% 50%',
					'image' => '',
					'imageid' => '',
				],
				'cover' => '1',
				'color_filter' => '1',
				'overlay_color' => [
					'color' => '#000000',
					'transparency' => '0.59',
					'rgba' => 'rgba(0, 0, 0, 0.59)',
				],
				'efect' => '',
				'repeat' => 'no-repeat',
			],
			'slider_overlay_color' => [
				'color' => '',
				'transparency' => '0.7',
				'rgba' => '',
			],
			'video_type' => 'iframe',
			'video_url' => '',
			'background_video_mp4' => '',
			'background_video_webm' => '',
			'background_video_ogg' => '',
			'video_image' => [
				'position' => '50% 50%',
				'image' => '',
				'imageid' => '',
				'cover' => '1',
			],
			'video_overlay_color' => [
				'color' => '',
				'transparency' => '0.7',
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
			'shape_top' => [
				'shape' => 'tilt',
				'code' => '<svg viewBox="0 0 1000 100" preserveAspectRatio="none"><path d="M0,6V0h1000v100L0,6z"></path></svg>',
				'size' => '100',
				'tablet' => [
					'size' => '',
				],
				'mobile' => [
					'size' => '',
				],
				'color' => '',
			],
			'shape_bottom' => [
				'shape' => 'mountains2',
				'code' => '<svg preserveAspectRatio="none" viewBox="0 0 1000 150"><path d="M1000,15.647L842,114.706,713,35.294,614,150,368,52.941l-52,61.765L235,35.294,75,114.706,0,35.765V0H1000V15.647Z"></path></svg>',
				'size' => '15',
				'tablet' => [
					'size' => '',
				],
				'mobile' => [
					'size' => '',
				],
				'color' => 'rgb(255, 255, 255)',
				'flip' => '1',
			],
			'margin_top' => '',
			'margin_bottom' => '',
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
							'font' => [
								'font-size' => '64',
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
							'content' => '<p style="text-align: center;">' . __('Blog', 'cms_ve') . '</p>',
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
							'margin_bottom' => '54',
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
$page = ['page' => ['title' => __('Blog', 'cms_ve'), 'slug' => __('blog', 'cms_ve'), 'theme' => 'page/1/', 'page_type' => 'blog'], 'setting' => [], 'layer' => base64_encode(serialize($temp_layer))];
