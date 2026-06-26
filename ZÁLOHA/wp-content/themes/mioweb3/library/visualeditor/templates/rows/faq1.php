<?php
$content = [
	'class' => '',
	'style' => [
		'background_color' => [
			'color1' => '#ffffff',
			'transparency1' => '100',
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
		'min-height' => '',
		'arrow_color' => '#fff',
		'content_align' => 'top',
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
							'font-size' => '35',
							'font-family' => '',
							'weight' => '',
							'line-height' => '1.2',
							'color' => '',
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
							'transparency1' => '1',
							'rgba1' => 'rgba(239, 239, 239, 1)',
						],
						'align' => 'center',
						'mw30' => '1',
						'content' => '<h2 style="text-align: center;">' . __('Často kladené otázky', 'cms_ve') . '</h2>',
					],
					'config' => [
						'max_width' => '',
						'margin_top' => '0',
						'margin_bottom' => '15',
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
							'font-size' => '18',
							'font-family' => '',
							'weight' => '',
							'line-height' => '',
							'color' => '#7a7979',
						],
						'li' => '',
						'style' => '1',
						'p-background-color' => [
							'color1' => '#e8e8e8',
							'color2' => '',
							'transparency1' => '1',
							'rgba1' => 'rgba(232, 232, 232, 1)',
						],
						'mw30' => '1',
						'content' => '<p style="text-align: center;">' . __('Sem patří podnadpis', 'cms_ve') . '</p>',
					],
					'config' => [
						'max_width' => '600',
						'margin_top' => '0',
						'margin_bottom' => '60',
						'delay' => '',
						'animate' => '',
						'id' => '',
						'class' => '',
					],
				],
				'2' => [
					'type' => 'faq',
					'style' => [
						'faqs' => [
							'0' => [
								'question' => __('Často kladená otázka', 'cms_ve'),
								'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vulputate, quam id dignissim sagittis, eros justo scelerisque eros, eu vestibulum elit quam id leo.',
							],
							'1' => [
								'question' => __('Často kladená otázka', 'cms_ve'),
								'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vulputate, quam id dignissim sagittis, eros justo scelerisque eros, eu vestibulum elit quam id leo.',
							],
							'2' => [
								'question' => __('Často kladená otázka', 'cms_ve'),
								'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vulputate, quam id dignissim sagittis, eros justo scelerisque eros, eu vestibulum elit quam id leo.',
							],
							'3' => [
								'question' => __('Často kladená otázka', 'cms_ve'),
								'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vulputate, quam id dignissim sagittis, eros justo scelerisque eros, eu vestibulum elit quam id leo.',
							],
							'4' => [
								'question' => __('Často kladená otázka', 'cms_ve'),
								'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vulputate, quam id dignissim sagittis, eros justo scelerisque eros, eu vestibulum elit quam id leo.',
							],
							'5' => [
								'question' => __('Často kladená otázka', 'cms_ve'),
								'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse vulputate, quam id dignissim sagittis, eros justo scelerisque eros, eu vestibulum elit quam id leo.',
							],
						],
						'cols' => 'three',
						'style' => '1',
						'background-color' => '#efefef',
						'question_font' => [
							'use-font' => 'title',
							'font-size' => '22',
							'color' => '',
						],
						'answer_font' => [
							'font-size' => '',
						],
						'mw30' => '1',
						'content' => '',
					],
				],
			],
		],
	],
];
