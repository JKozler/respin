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
			'css_class' => '',
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'title',
						'content' => '<p style="text-align: center;">' . __('ZDE JE VÁŠ EBOOK', 'cms_ve') . '</p>',
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
							'max_width' => '750',
							'margin_top' => '0',
							'margin_bottom' => '40',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'box',
						'content' => [
							'0' => [
								'0' => [
									'type' => 'text',
									'content' => '<p style="text-align: center;"><strong>' . __('Nyní si můžete ebook stáhnout:', 'cms_ve') . '</strong></p>',
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
								'1' => [
									'type' => 'image',
									'content' => '',
									'style' => [
										'image' => [
											'image' => MW_IMAGE_LIBRARY . 'book/ebook-cover.png',
											'imageid' => '',
										],
										'click_action' => 'none',
										'alert' => '',
										'popup' => '',
										'link' => [
											'page' => '',
											'link' => '',
										],
										'large_image' => [
											'image' => '',
											'imageid' => '',
										],
										'align' => 'center',
										'max-width' => '',
										'label' => '',
										'style' => '1',
									],
								],
								'2' => [
									'type' => 'button',
									'content' => __('STÁHNOUT EBOOK V PDF', 'cms_ve'),
									'style' => [
										'show' => 'url',
										'link' => [
											'page' => '',
											'link' => '#',
											'use_url' => '1',
										],
										'popup' => '',
										'align' => 'center',
										'button' => [
											'style' => '9',
											'font' => [
												'font-size' => '19',
												'font-family' => '',
												'weight' => '',
												'color' => '#ffffff',
												'text-shadow' => 'none',
											],
											'background_color' => [
												'color1' => '#158ebf',
												'color2' => '',
											],
											'corner' => '0',
											'height_padding' => '0.8',
											'width_padding' => '1.6',
											'border-color' => '',
											'hover_color' => [
												'color1' => '',
												'color2' => '',
											],
											'hover_effect' => 'scale',
											'hover_font_color' => '',
											'border_hover-color' => '',
										],
										'text2' => 'Text tlačítka',
										'show2' => 'url',
										'link2' => [
											'page' => '',
											'link' => '',
										],
										'popup2' => '',
										'button2' => [
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
											'corner' => '0',
											'height_padding' => '0.5',
											'width_padding' => '1.2',
											'border-color' => '',
											'hover_color' => [
												'color1' => '',
												'color2' => '',
											],
											'hover_effect' => '',
											'hover_font_color' => '',
											'border_hover-color' => '',
										],
									],
									'config' => [
										'max_width' => '',
										'margin_top' => '0',
										'margin_bottom' => '60',
										'delay' => '',
										'animate' => '',
										'id' => '',
										'class' => '',
									],
								],
								'3' => [
									'type' => 'image_text',
									'content' => '<p>' . __('Mé jméno je Pavel Novotný a jsem rád, že se zajímáte ... Krátký text o mne. Představte se ve 3 větách.', 'cms_ve') . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed aliquam efficitur nisl, ac ullamcorper justo dictum vel. Phasellus maximus elementum cursus. Vestibulum vel fermentum lectus. Integer finibus nibh nec quam imperdiet iaculis. Vivamus tempus dui eu nisi facilisis vehicula.</p>',
									'style' => [
										'title' => '',
										'image' => [
											'image' => MW_IMAGE_LIBRARY . 'webs/expert2/man.jpg',
											'imageid' => '',
										],
										'style' => '2',
										'align' => 'left',
										'font' => [
											'font-size' => '24',
											'font-family' => '',
											'weight' => '',
											'line-height' => '',
											'color' => '',
										],
										'font_text' => [
											'font-size' => '',
											'font-family' => '',
											'weight' => '',
											'line-height' => '',
											'color' => '',
										],
									],
								],
							],
						],
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
								'line-height' => '',
								'color' => '#111111',
							],
							'link-color' => '#158ebf',
							'border' => [
								'size' => '0',
								'color' => '',
							],
							'corner' => '0',
							'padding' => [
								'top' => '40',
								'bottom' => '30',
								'left' => '40',
								'right' => '40',
							],
							'box-shadow' => [
								'horizontal' => '0',
								'vertical' => '0',
								'size' => '0',
								'transparency' => '10',
							],
							'title' => '',
							'title-font' => [
								'font-size' => '20',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'align' => 'center',
								'color' => '',
							],
							'title_bg' => [
								'color1' => '#eeeeee',
								'color2' => '',
								'transparency' => '100',
							],
							'title_border' => [
								'size' => '1',
								'color' => '#dddddd',
							],
						],
						'config' => [
							'max_width' => '800',
							'margin_top' => '0',
							'margin_bottom' => '20',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'2' => [
						'type' => 'button',
						'content' => __('ZPĚT NA ÚVODNÍ STRÁNKU', 'cms_ve'),
						'style' => [
							'show' => 'url',
							'link' => [
								'page' => '8',
								'link' => '',
							],
							'popup' => '',
							'align' => 'center',
							'button' => [
								'style' => '9',
								'font' => [
									'font-size' => '19',
									'font-family' => '',
									'weight' => '',
									'color' => '#1a1a1a',
									'text-shadow' => 'none',
								],
								'background_color' => [
									'color1' => '#ffffff',
									'color2' => '',
								],
								'corner' => '0',
								'height_padding' => '0.8',
								'width_padding' => '1.6',
								'border-color' => '',
								'hover_color' => [
									'color1' => '',
									'color2' => '',
								],
								'hover_effect' => 'scale',
								'hover_font_color' => '',
								'border_hover-color' => '',
							],
							'text2' => __('Text tlačítka', 'cms_ve'),
							'show2' => 'url',
							'link2' => [
								'page' => '',
								'link' => '',
							],
							'popup2' => '',
							'button2' => [
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
								'corner' => '0',
								'height_padding' => '0.5',
								'width_padding' => '1.2',
								'border-color' => '',
								'hover_color' => [
									'color1' => '',
									'color2' => '',
								],
								'hover_effect' => '',
								'hover_font_color' => '',
								'border_hover-color' => '',
							],
						],
					],
				],
			],
		],
	],
];
$setting = [
	've_header' => [
		'show' => 'noheader',
		'appearance' => 'type1',
		'logo_setting' => 'image',
		'logo' => MW_IMAGE_LIBRARY . 'logo/mojelogo.png',
		'logo_text' => __('Název webu', 'cms_ve'),
		'logo_font' => [
			'font-size' => '25',
			'font-family' => '',
			'weight' => '',
			'color' => '',
		],
		'before_header' => '',
		'background_color' => [
			'color1' => '#ffffff',
			'color2' => '',
			'transparency' => '90',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
			'imageid' => '',
		],
		'menu' => '',
		'menu_font' => [
			'font-size' => '15',
			'font-family' => 'Open Sans',
			'weight' => '700',
			'color' => '#363636',
		],
		'menu_active_color' => '#d93d41',
		'menu_submenu_text_color' => '#ffffff',
		'menu_bg' => [
			'color1' => '#121212',
			'color2' => '',
		],
		'header_width' => [
			'size' => '',
			'unit' => 'px',
		],
		'header_padding' => '20',
		'background_color_fix' => [
			'color1' => '',
			'color2' => '',
			'transparency' => '100',
		],
		'header_padding_fix' => '',
	],

	've_footer' => [
		'show' => 'page',
		'custom_footer' => '39',
		'hide_footer_end' => '1',
		'appearance' => 'type2',
		'text' => '',
		'menu' => '',
		'background_color' => [
			'color1' => '#171616',
			'color2' => '',
			'transparency' => '100',
		],
		'background_image' => [
			'position' => 'center center',
			'repeat' => 'no-repeat',
			'image' => '',
			'imageid' => '',
		],
		'font' => [
			'font-size' => '',
			'font-family' => '',
			'weight' => '',
			'color' => '#7a7a7a',
		],
		'footer_width' => [
			'size' => '',
			'unit' => 'px',
		],
	],


	've_appearance' => [],

];

$page = [
	'page' => [
		'title' => __('Děkujeme', 'cms_ve'),
		'slug' => __('dekujeme', 'cms_ve'),
		'parent' => 'magnet',
		'theme' => 'page/1/',
	],
	'setting' => $setting,
	'layer' => base64_encode(serialize($temp_layer)),
];
