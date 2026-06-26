<?php
$temp_layer = [
	'0' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '',
				'color2' => '',
				'transparency' => '100',
			],
			'background_image' => [
				'position' => 'center bottom',
				'repeat' => 'no-repeat',
				'image' => $color_set['background_image'],
				'pattern' => '0',
			],
			'font' => [
				'font-size' => '',
				'font-family' => '',
				'weight' => '',
				'color' => $color_set['bg_text'],
			],
			'link_color' => '',
			'type' => 'basic',
			'padding_top' => '50',
			'padding_bottom' => '90',
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
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'title',
						'content' => '<h1 style="text-align: center;">' . __('Dotazník', 'cms_ve') . '</h1>',
						'style' => [
							'font' => [
								'font-size' => '55',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '#ffffff',
								'text-shadow' => 'none',
							],
							'style' => '1',
							'border' => [
								'size' => '0',
								'color' => '',
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
							'margin_bottom' => '15',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'text',
						'content' => '<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi eget <br />arcu id lacus laoreet porttitor ut nec velit. Duis ut ante vestibulum, <br />varius neque nec, mollis eros.</p>',
						'style' => [
							'font' => [
								'font-size' => '18',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
							],
							'li' => '',
						],
						'config' => [
							'max_width' => '800',
							'margin_top' => '0',
							'margin_bottom' => '30',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
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
				'pattern' => '0',
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
			'padding_bottom' => '80',
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
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [
					'1' => [
						'type' => 'seform',
						'content' => '',
						'style' => [
							'type' => 'smartemailing',
							'html' => '',
							'email' => '',
							'subject' => '',
							'thx_url' => '',
							'custom_form' => [
								'0' => [
									'title' => 'Jméno',
									'name' => '',
									'type' => 'text',
									'content' => '',
								],
								'1' => [
									'title' => __('Váš email', 'cms_ve'),
									'name' => '',
									'type' => 'text',
									'content' => '',
								],
							],
							'url' => '',
							'form-style' => '1',
							'form-labels' => '2',
							'form-look' => '3',
							'form-font' => [
								'font-size' => '15',
								'color' => '',
							],
							'background' => '#eeeeee',
							'button_text' => '',
							'button' => [
								'style' => '1',
								'font' => [
									'font-size' => '24',
									'font-family' => '',
									'weight' => '',
									'color' => '#ffffff',
									'text-shadow' => 'none',
								],
								'background_color' => [
									'color1' => $color_set['button_bg'],
									'color2' => '',
								],
								'hover_color' => [
									'color1' => $color_set['button_hover_bg'],
									'color2' => '',
								],
								'corner' => '0',
								'border-color' => '',
							],
							'popup_title' => __('Zadejte svůj email a registrujte se', 'cms_ve'),
							'popup_type' => 'button',
							'image' => '',
							'popup_text' => __('Registrovat se', 'cms_ve'),
							'popupbutton' => [
								'style' => '1',
								'font' => [
									'font-size' => '30',
									'font-family' => '',
									'weight' => '',
									'color' => '#2b2b2b',
									'text-shadow' => 'none',
								],
								'background_color' => [
									'color1' => '#ffde21',
									'color2' => '#ffcc00',
								],
								'hover_color' => [
									'color1' => '',
									'color2' => '',
								],
								'corner' => '8',
								'border-color' => '',
							],
							'link_text' => __('Registrovat se', 'cms_ve'),
							'link_font' => [
								'font-size' => '',
								'color' => '',
							],
							'align' => 'center',
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
		],
	],
];
$config['setting'] = [
	've_header' => [
		'show' => 'page',
		'appearance' => 'type1',
		'logo' => '/wp-content/themes/mioweb/library/visualeditor/images/default/logo2.png',
		'before_header' => '',
		'background_color' => [
			'color1' => '',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
		],
		'menu' => '12',
		'menu_font' => [
			'font-size' => '15',
			'font-family' => '',
			'weight' => '',
			'color' => '#cedee0',
		],
		'menu_active_color' => '#ffffff',
		'menu_submenu_text_color' => '#ffffff',
		'menu_bg' => [
			'color1' => '#121212',
			'color2' => '',
		],
		'header_width' => [
			'size' => '',
			'unit' => 'px',
		],
	],

	've_footer' => [
		'show' => 'page',
		'custom_footer' => '2759',
		'appearance' => 'type2',
		'text' => '',
		'menu' => '',
		'background_color' => [
			'color1' => '#181a1a',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
		],
		'font' => [
			'font-size' => '14',
			'font-family' => '',
			'weight' => '',
			'color' => '#a8a8a8',
		],
		'footer_width' => [
			'size' => '',
			'unit' => 'px',
		],
	],

	've_appearance' => [
		'page_width' => [
			'size' => '',
			'unit' => 'px',
		],
		'background_color' => '#41a0a9',
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'cover' => 'cover',
			'image' => '',
			'pattern' => '0',
		],
		'background_video_mp4' => '',
		'background_video_webm' => '',
		'background_video_ogg' => '',
		'font' => [
			'font-size' => '16',
			'font-family' => '',
			'weight' => '',
			'line-height' => '1.5',
			'color' => '',
		],
		'link_color' => '',
		'title_font' => [
			'font-family' => 'Open Sans',
			'weight' => '400',
			'color' => '',
		],
		'h1_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h2_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h3_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h4_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h5_font' => [
			'font-size' => '',
			'color' => '',
		],
		'h6_font' => [
			'font-size' => '',
			'color' => '',
		],
		'li' => '',
	],

];
$page = [
	'page' => [
		'title' => __('Dotazník', 'cms_ve'),
		'slug' => __('dotaznik', 'cms_ve'),
		'theme' => 'page/1/',
	],
	'setting' => [],
	'layer' => base64_encode(serialize($temp_layer)),
];
