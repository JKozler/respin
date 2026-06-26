<?php
$image = [
	2417 => MW_IMAGE_LIBRARY . 'bg/sea3.jpg',
	2420 => MW_IMAGE_LIBRARY . 'bg/sea2.jpg',
	2421 => MW_IMAGE_LIBRARY . 'bg/sea4.jpg',
];

$content = [
	'class' => '',
	'style' => [
		'background_color' => [
			'color1' => '#ffffff',
			'transparency1' => '1.00',
			'rgba1' => 'rgba(255, 255, 255, 1)',
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
		'video_image' => [
			'position' => '50% 50%',
			'image' => '',
			'imageid' => '',
			'cover' => '1',
		],
		'video_overlay_color' => [
			'color' => '',
			'transparency' => '70',
			'rgba' => '',
		],
		'row_height' => 'default',
		'min-height' => '',
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
		'margin_top' => '',
		'margin_bottom' => '',
		'css_class' => '',
		'row_anchor' => '',
		'm_background_image' => [
			'position' => '50% 50%',
			'image' => '',
			'imageid' => '',
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
		],
		'm2_background_image' => [
			'position' => '50% 50%',
			'image' => '',
			'imageid' => '',
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
		],
		'delay' => '',
	],
	'content' => [
		'0' => [
			'type' => 'col-one',
			'class' => '',
			'content' => [
				'0' => [
					'type' => 'image_gallery',
					'style' => [
						'image_gallery_items' => [
							'2' => $image[2417],
							'3' => $image[2420],
							'4' => $image[2421],
						],
						'thumb_name' => '43',
						'cols' => '3',
						'cols_type' => 'fullcols',
						'hover' => 'zoom',
						'hover_color' => [
							'color' => '#000000',
							'transparency' => '0.46',
							'rgba' => 'rgba(0, 0, 0, 0.46)',
						],
						'gallery_style' => 'no_captions',
						'font' => [
							'use-font' => 'text',
							'font-size' => '16',
							'align' => 'center',
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
						'margin_bottom' => '0',
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
