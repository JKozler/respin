<?php
$temp_layer = [
	'0' => [
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
				'pattern' => '14',
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
						'type' => 'title',
						'content' => '<p style="text-align: center;">' . __('HLAVNÍ NADPIS, KTERÝ PŘITÁHNE POZORNOST NA MAGNET', 'cms_ve') . '</p>',
						'style' => [
							'font' => [
								'font-size' => '60',
								'font-family' => '',
								'weight' => '',
								'line-height' => '1.2',
								'color' => '#158ebf',
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
							'margin_bottom' => '40',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'twocols',
						'content' => [
							'0' => [
								'0' => [
									'type' => 'seform',
									'content' => [
										'api' => 'se',
									],
									'style' => [
										'type' => 'smartemailing',
										'html' => '',
										'email' => '',
										'subject' => '',
										'thx_url' => [
											'page' => '',
											'link' => '',
										],
										'url' => '',
										'form-style' => '1',
										'form-labels' => '1',
										'form-look' => '2',
										'form-font' => [
											'font-size' => '15',
											'color' => '',
										],
										'background' => '#eeeeee',
										'button_text' => '',
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
										'popup' => '1',
										'popup_title' => __('Zadejte svůj email a stáhněte si ebook.', 'cms_ve'),
										'popup_type' => 'image',
										'image' => MW_IMAGE_LIBRARY . 'book/ebook-cover.png',
										'popup_text' => __('STÁHNOUT ZDARMA', 'cms_ve'),
										'popupbutton' => [
											'style' => '9',
											'font' => [
												'font-size' => '24',
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
										'link_text' => __('Registrovat se', 'cms_ve'),
										'link_font' => [
											'font-size' => '',
											'color' => '',
										],
										'align' => 'center',
									],
									'config' => [
										'max_width' => '',
										'margin_top' => '0',
										'margin_bottom' => '20',
										'delay' => '',
										'animate' => '',
										'id' => '',
										'class' => '',
									],
								],
							],
							'1' => [
								'0' => [
									'type' => 'text',
									'content' => '<p style="text-align: center;"><strong>' . __('Otevřete tento sešit a odhalte postup</strong>,<br />jak udělat web a z něj toho nejlepšího<br />obchodníka, který vám bude<br /><strong>vydělávat </strong>i když spíte.', 'cms_ve') . '</p>',
									'style' => [
										'font' => [
											'font-size' => '22',
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
									'config' => [
										'max_width' => '',
										'margin_top' => '20',
										'margin_bottom' => '40',
										'delay' => '',
										'animate' => '',
										'id' => '',
										'class' => '',
									],
								],
								'1' => [
									'type' => 'seform',
									'content' => [
										'api' => 'se',
									],
									'style' => [
										'type' => 'smartemailing',
										'html' => '',
										'email' => '',
										'subject' => '',
										'thx_url' => [
											'page' => '',
											'link' => '',
										],
										'url' => '',
										'form-style' => '1',
										'form-labels' => '1',
										'form-look' => '2',
										'form-font' => [
											'font-size' => '15',
											'color' => '',
										],
										'background' => '#eeeeee',
										'button_text' => '',
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
										'popup' => '1',
										'popup_title' => __('Zadejte svůj email a stáhněte si ebook.', 'cms_ve'),
										'popup_type' => 'button',
										'image' => '',
										'popup_text' => __('STÁHNOUT ZDARMA', 'cms_ve'),
										'popupbutton' => [
											'style' => '9',
											'font' => [
												'font-size' => '24',
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
										'link_text' => __('Registrovat se', 'cms_ve'),
										'link_font' => [
											'font-size' => '',
											'color' => '',
										],
										'align' => 'center',
									],
									'config' => [
										'max_width' => '',
										'margin_top' => '0',
										'margin_bottom' => '40',
										'delay' => '',
										'animate' => '',
										'id' => '',
										'class' => '',
									],
								],
								'2' => [
									'type' => 'numbers',
									'content' => '',
									'style' => [
										'numbers' => [
											'0' => [
												'type' => 'load',
												'se' => [
													'api' => 'se',
												],
												'number' => '',
												'unit' => ' ' . __('lidí si už tento ebook stáhlo', 'cms_ve'),
												'title' => '',
											],
										],
										'style' => '1',
										'cols' => 'one',
										'number_font' => [
											'font-size' => '22',
											'font-family' => '',
											'weight' => '',
											'line-height' => '',
											'color' => '',
										],
										'text_font' => [
											'font-size' => '15',
											'font-family' => '',
											'weight' => '',
											'line-height' => '',
											'color' => '',
										],
									],
									'config' => [
										'max_width' => '',
										'margin_top' => '0',
										'margin_bottom' => '10',
										'delay' => '',
										'animate' => '',
										'id' => '',
										'class' => '',
									],
								],
								'3' => [
									'type' => 'like',
									'content' => [
										'page' => '44',
										'link' => '',
									],
									'style' => [
										'layout' => 'button',
										'scheme' => 'light',
										'setting' => [
											'share' => 'share',
										],
										'align' => 'center',
									],
								],
							],
						],
						'style' => [],
					],
				],
			],
		],
	],
	'1' => [
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
						'content' => '<p style="text-align: center;">' . __('V TOMTO EBOOKU SE DOZVÍTE', 'cms_ve') . '</p>',
						'style' => [
							'font' => [
								'font-size' => '40',
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
							'margin_bottom' => '40',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'features',
						'content' => '',
						'style' => [
							'features' => [
								'0' => [
									'icon' => [
										'icon' => 'flag',
										'size' => '40',
										'color' => '#ffffff',
										'image' => [
											'image' => '',
											'imageid' => '',
										],
										'tab' => 'icon',
									],
									'title' => __('PRVNÍ VÝHODA', 'cms_ve'),
									'text' => __('Popis bonusu.', 'cms_ve') . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
									'button_text' => __('Více informací →', 'cms_ve'),
									'link' => [
										'page' => '',
										'link' => '',
									],
								],
								'1' => [
									'icon' => [
										'icon' => 'lock-open-alt',
										'size' => '40',
										'color' => '#ffffff',
										'image' => [
											'image' => '',
											'imageid' => '',
										],
										'tab' => 'icon',
									],
									'title' => __('DRUHÁ VÝHODA', 'cms_ve'),
									'text' => __('Popis bonusu.', 'cms_ve') . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
									'button_text' => __('Více informací →', 'cms_ve'),
									'link' => [
										'page' => '',
										'link' => '',
									],
								],
								'2' => [
									'icon' => [
										'icon' => 'glass',
										'size' => '40',
										'color' => '#ffffff',
										'image' => [
											'image' => '',
											'imageid' => '',
										],
										'tab' => 'icon',
									],
									'title' => __('TŘETÍ VÝHODA', 'cms_ve'),
									'text' => __('Popis bonusu.', 'cms_ve') . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
									'button_text' => __('Více informací →', 'cms_ve'),
									'link' => [
										'page' => '',
										'link' => '',
									],
								],
							],
							'cols' => 'three',
							'style' => '1',
							'background-color' => '#209ccf',
							'font' => [
								'font-size' => '18',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '#ffffff',
							],
							'font_text' => [
								'font-size' => '',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
							],
							'button' => [
								'style' => '2',
								'font' => [
									'font-size' => '13',
									'font-family' => '',
									'weight' => '',
									'color' => '#737373',
									'text-shadow' => 'none',
								],
								'background_color' => [
									'color1' => '',
									'color2' => '',
								],
								'corner' => '90',
								'height_padding' => '0.5',
								'width_padding' => '1.2',
								'border-color' => '#bdbdbd',
								'hover_color' => [
									'color1' => '',
									'color2' => '#ebebeb',
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
	'2' => [
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
						'type' => 'title',
						'content' => '<p style="text-align: center;">' . __('CO O EBOOKU ŘEKLI JEHO ČTENÁŘI', 'cms_ve') . '</p>',
						'style' => [
							'font' => [
								'font-size' => '40',
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
								'transparency' => '100',
							],
							'align' => 'center',
						],
						'config' => [
							'max_width' => '',
							'margin_top' => '0',
							'margin_bottom' => '50',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'testimonials',
						'content' => '',
						'style' => [
							'testimonials' => [
								'0' => [
									'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec pellentesque velit a dolor fermentum, eget viverra risus finibus. Nam libero quam, imperdiet at magna vel, tincidunt elementum blandit quam tincidunt sem. ',
									'name' => __('Petra', 'cms_ve'),
									'company' => __('Finanční poradce', 'cms_ve'),
									'image' => [
										'image' => MW_IMAGE_LIBRARY . 'misc/face-w.jpg',
										'imageid' => '',
									],
								],
								'1' => [
									'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec pellentesque velit a dolor fermentum, eget viverra risus finibus. Nam libero quam, imperdiet at magna vel, tincidunt elementum blandit quam tincidunt sem. ',
									'name' => __('Honza', 'cms_ve'),
									'company' => __('Informatik', 'cms_ve'),
									'image' => [
										'image' => MW_IMAGE_LIBRARY . 'misc/face-m.jpg',
										'imageid' => '',
									],
								],
								'2' => [
									'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec pellentesque velit a dolor fermentum, eget viverra risus finibus. Nam libero quam, imperdiet at magna vel, tincidunt elementum blandit quam tincidunt sem. ',
									'name' => __('Jana', 'cms_ve'),
									'company' => __('Účetní', 'cms_ve'),
									'image' => [
										'image' => MW_IMAGE_LIBRARY . 'misc/face-w2.jpg',
										'imageid' => '',
									],
								],
							],
							'cols' => 'three',
							'style' => '7',
							'font' => [
								'font-size' => '15',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
							],
							'font-author' => [
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
	'3' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#efefef',
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
						'type' => 'title',
						'content' => '<p style="text-align: center;">' . __('NAPIŠTE ZDE HLAVNÍ VÝHODU MAGNETU', 'cms_ve') . '</p>',
						'style' => [
							'font' => [
								'font-size' => '40',
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
								'transparency' => '100',
							],
							'align' => 'center',
						],
						'config' => [
							'max_width' => '',
							'margin_top' => '0',
							'margin_bottom' => '40',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'seform',
						'content' => [
							'api' => 'se',
						],
						'style' => [
							'type' => 'smartemailing',
							'html' => '',
							'email' => '',
							'subject' => '',
							'thx_url' => [
								'page' => '',
								'link' => '',
							],
							'url' => '',
							'form-style' => '1',
							'form-labels' => '1',
							'form-look' => '2',
							'form-font' => [
								'font-size' => '15',
								'color' => '',
							],
							'background' => '#eeeeee',
							'button_text' => '',
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
							'popup' => '1',
							'popup_title' => __('Zadejte svůj email a stáhněte si ebook.', 'cms_ve'),
							'popup_type' => 'button',
							'image' => '',
							'popup_text' => __('STÁHNOUT ZDARMA', 'cms_ve'),
							'popupbutton' => [
								'style' => '9',
								'font' => [
									'font-size' => '24',
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
							'link_text' => __('Registrovat se', 'cms_ve'),
							'link_font' => [
								'font-size' => '',
								'color' => '',
							],
							'align' => 'center',
						],
						'config' => [
							'max_width' => '',
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
$setting = [
	've_header' => [
		'show' => 'noheader',
	],

	've_footer' => [
		'show' => 'page',
		'custom_footer' => '39',
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

];

$page = [
	'page' => [
		'title' => __('Název magnetu', 'cms_ve'),
		'slug' => __('magnet', 'cms_ve'),
		'theme' => 'page/1/',
	],
	'setting' => $setting,
	'layer' => base64_encode(serialize($temp_layer)),
];
