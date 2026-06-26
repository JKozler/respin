<?php
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
				'position' => '',
				'image' => '',
				'imageid' => '',
			],
			'mobile' => [
				'position' => '',
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
			'code' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none">
	<path d="M0,6V0h1000v100L0,6z"></path>
</svg>
',
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
			'code' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none">
	<path d="M0,6V0h1000v100L0,6z"></path>
</svg>
',
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
		'delay' => '',
	],
	'content' => [
		'0' => [
			'type' => 'col-one',
			'class' => '',
			'break' => '1',
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
						'content' => '<h2 style="text-align: center;">' . __('Hlavní nadpis bloku', 'cms_ve') . '</h2>',
						'mw30' => '1',
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
							'color' => '#e8e8e8',
							'rgba' => 'rgba(232, 232, 232, 1)',
							'transparency1' => '1',
							'rgba1' => 'rgba(232, 232, 232, 1)',
						],
						'content' => '<p style="text-align: center;">' . __('Sem patří podnadpis', 'cms_ve') . '</p>',
						'mw30' => '1',
					],
					'config' => [
						'max_width' => '600',
						'margin_top' => '0',
						'margin_bottom' => '20',
						'delay' => '',
						'animate' => '',
						'id' => '',
						'class' => '',
					],
				],
			],
		],
		'1' => [
			'type' => 'col-two',
			'class' => '',
			'content' => [
				'0' => [
					'type' => 'image',
					'style' => [
						'image' => [
							'position' => '',
							'image' => MW_IMAGE_LIBRARY . 'gallery/writing.jpg',
							'imageid' => '',
						],
						'thumb_name' => '',
						'label' => '',
						'click_action' => 'none',
						'alert' => '',
						'popup' => '',
						'link' => [
							'page' => '',
							'link' => '',
						],
						'large_image' => [
							'position' => '',
							'image' => '',
							'imageid' => '',
						],
						'hover' => '',
						'hover_color' => [
							'color' => '#000000',
							'transparency' => '30',
							'rgba' => 'rgba(0,0,0,0.3)',
						],
						'style' => '1',
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
		'2' => [
			'type' => 'col-two',
			'class' => '',
			'content' => [
				'0' => [
					'type' => 'text',
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
							'color' => '#e8e8e8',
							'rgba' => 'rgba(232, 232, 232, 1)',
							'transparency1' => '1',
							'rgba1' => 'rgba(232, 232, 232, 1)',
						],
						'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ac dolor at leo feugiat convallis. Donec congue porttitor scelerisque. Donec leo ligula, porttitor non enim id, mattis tristique tellus. Etiam ac elementum leo. Aenean nec magna quis mi mattis pretium et nec dui. Aliquam bibendum tellus sit amet consectetur vulputate. Etiam ac dolor at leo feugiat convallis. Donec congue porttitor scelerisque. Donec leo ligula, porttitor non enim id, mattis tristique tellus.</p>',
						'mw30' => '1',
					],
				],
			],
		],
	],
];
