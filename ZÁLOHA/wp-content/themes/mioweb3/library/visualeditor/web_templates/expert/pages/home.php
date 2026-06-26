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
			'padding_top' => '70',
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
			'css_class' => '',
		],
		'content' => [
			'0' => [
				'type' => 'col-one',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'title',
						'content' => '<h1 style="text-align: center;">' . __('Hlavní nadpis stránky <br />pro přitáhnutí pozornosti', 'cms_ve') . '</h1>',
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
							'margin_bottom' => '20',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'2' => [
						'type' => 'like',
						'content' => '',
						'style' => [
							'layout' => 'button_count',
							'scheme' => 'light',
							'setting' => [
								'share' => 'share',
							],
							'align' => 'center',
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
				'color1' => '#000000',
				'color2' => '',
				'transparency' => '40',
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
				'color' => $color_set['bg_text'],
			],
			'link_color' => '',
			'type' => 'basic',
			'padding_top' => '40',
			'padding_bottom' => '0',
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
				'type' => 'col-four',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'image',
						'content' => '',
						'style' => [
							'image' => MW_IMAGE_LIBRARY . 'book/book.png',
							'click_action' => 'none',
							'alert' => '',
							'popup' => '',
							'link' => [
								'link' => '',
							],
							'large_image' => '',
							'align' => 'center',
							'max-width' => '',
							'label' => '',
							'style' => '1',
						],
						'config' => [
							'max_width' => '190',
							'margin_top' => '0',
							'margin_bottom' => '0',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
				],
			],
			'1' => [
				'type' => 'col-threefour',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'text',
						'content' => '<p style="text-align: left;">' . __('Vyplňte jméno a email a získejte ebook nebo jiný bonus zdarma', 'cms_ve') . '</p>',
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
							'max_width' => '',
							'margin_top' => '10',
							'margin_bottom' => '2',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'title',
						'content' => '<h1 style="text-align: left;">' . __('Název ebooku nebo jiného bonusu', 'cms_ve') . '</h1>',
						'style' => [
							'font' => [
								'font-size' => '40',
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
					'2' => [
						'type' => 'seform',
						'content' => '',
						'style' => [
							'type' => 'smartemailing',
							'html' => '',
							'email' => '',
							'subject' => '',
							'thx_url' => '',
							'url' => '',
							'form-style' => '2',
							'form-labels' => '1',
							'form-look' => '1',
							'form-font' => [
								'font-size' => '18',
								'color' => '',
							],
							'background' => '#ffffff',
							'button_text' => '',
							'button' => [
								'style' => '1',
								'font' => [
									'font-size' => '25',
									'font-family' => '',
									'weight' => '',
									'color' => '#ffffff',
									'text-shadow' => 'none',
								],
								'background_color' => [
									'color1' => $color_set['button_bg'],
									'color2' => '',
								],
								'corner' => '0',
								'border-color' => '',
								'hover_color' => [
									'color1' => $color_set['button_hover_bg'],
									'color2' => '',
								],
								'hover_effect' => '',
								'hover_font_color' => '',
								'border_hover-color' => '',
							],
							'popup_title' => __('Zadejte svůj email a registrujte se', 'cms_ve'),
							'popup_type' => 'button',
							'image' => '',
							'popup_text' => __('Mám zájem', 'cms_ve'),
							'popupbutton' => [
								'style' => '1',
								'font' => [
									'font-size' => '25',
									'font-family' => 'Open Sans',
									'weight' => '700',
									'color' => '#ffffff',
									'text-shadow' => 'none',
								],
								'background_color' => [
									'color1' => $color_set['button_bg'],
									'color2' => '',
								],
								'corner' => '0',
								'border-color' => '',
								'hover_color' => [
									'color1' => $color_set['button_hover_bg'],
									'color2' => '',
								],
								'hover_effect' => '',
								'hover_font_color' => '',
								'border_hover-color' => '',
							],
							'link_text' => __('Registrovat se', 'cms_ve'),
							'link_font' => [
								'font-size' => '',
								'color' => '',
							],
							'align' => 'left',
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
			'padding_top' => '100',
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
				'type' => 'col-two',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'title',
						'content' => '<h2>' . __('"Core message" projektu', 'cms_ve') . '</h2>',
						'style' => [
							'font' => [
								'font-size' => '35',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
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
							'margin_bottom' => '30',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'text',
						'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi eget arcu id lacus laoreet porttitor ut nec velit. Duis ut ante vestibulum, varius neque nec, mollis eros. Integer scelerisque ante et vestibulum tincidunt. Curabitur vel mi a odio condimentum dignissim. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi eget arcu id lacus laoreet porttitor ut nec velit. Duis ut ante vestibulum, varius neque nec, mollis eros.</p>',
						'style' => [
							'font' => [
								'font-size' => '',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
							],
							'li' => '',
						],
						'config' => [
							'max_width' => '',
							'margin_top' => '0',
							'margin_bottom' => '28',
							'delay' => '',
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
						'content' => '',
						'style' => [
							'image' => '',
							'click_action' => 'none',
							'alert' => '',
							'popup' => '',
							'link' => [
								'link' => '',
							],
							'large_image' => '',
							'align' => 'center',
							'max-width' => '',
							'label' => '',
							'style' => '1',
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
				'color1' => '#f5f5f5',
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
			'padding_top' => '100',
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
				'type' => 'col-two',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'image',
						'content' => '',
						'style' => [
							'image' => '',
							'click_action' => 'none',
							'alert' => '',
							'popup' => '',
							'link' => [
								'link' => '',
							],
							'large_image' => '',
							'align' => 'center',
							'max-width' => '',
							'label' => '',
							'style' => '1',
						],
					],
				],
			],
			'1' => [
				'type' => 'col-two',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'title',
						'content' => '<h2>' . __('Vize a příběh stručně', 'cms_ve') . '</h2>',
						'style' => [
							'font' => [
								'font-size' => '35',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
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
							'margin_bottom' => '30',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'text',
						'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi eget arcu id lacus laoreet porttitor ut nec velit. Duis ut ante vestibulum, varius neque nec, mollis eros. Integer scelerisque ante et vestibulum tincidunt. Curabitur vel mi a odio condimentum dignissim. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi eget arcu id lacus laoreet porttitor ut nec velit. Duis ut ante vestibulum, varius neque nec, mollis eros.</p>',
						'style' => [
							'font' => [
								'font-size' => '',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
							],
							'li' => '',
						],
						'config' => [
							'max_width' => '',
							'margin_top' => '0',
							'margin_bottom' => '28',
							'delay' => '',
							'class' => '',
						],
					],
				],
			],
		],
	],
	'4' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '',
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
				'color' => $color_set['bg_text'],
			],
			'link_color' => '',
			'type' => 'basic',
			'padding_top' => '80',
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
						'content' => '<h2 style="text-align: center;">' . __('Proč budete nadšení?', 'cms_ve') . '</h2>',
						'style' => [
							'font' => [
								'font-size' => '42',
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
							'margin_bottom' => '60',
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
										'icon' => 'rocket',
										'size' => '50',
										'color' => '#ffffff',
										'image' => '',
										'tab' => 'icon',
									],
									'title' => 'Lorem ipsum dolor',
									'text' => 'Curabitur vel mi a odio condimentum dignissim. Mauris iaculis quam id accumsan aliquam. Proin luctus erat in rutrum bibendum.',
									'link' => [
										'link' => '',
									],
									'button_text' => '',
								],
								'1' => [
									'icon' => [
										'icon' => 'lightbulb-1',
										'size' => '50',
										'color' => '#ffffff',
										'image' => '',
										'tab' => 'icon',
									],
									'title' => 'Lorem ipsum dolor',
									'text' => 'Curabitur vel mi a odio condimentum dignissim. Mauris iaculis quam id accumsan aliquam. Proin luctus erat in rutrum bibendum.',
									'link' => [
										'link' => '',
									],
									'button_text' => '',
								],
								'2' => [
									'icon' => [
										'icon' => 'gift',
										'size' => '50',
										'color' => '#ffffff',
										'image' => '',
										'tab' => 'icon',
									],
									'title' => 'Lorem ipsum dolor',
									'text' => 'Curabitur vel mi a odio condimentum dignissim. Mauris iaculis quam id accumsan aliquam. Proin luctus erat in rutrum bibendum.',
									'link' => [
										'link' => '',
									],
									'button_text' => '',
								],
								'3' => [
									'icon' => [
										'icon' => 'location',
										'size' => '50',
										'color' => '#ffffff',
										'image' => '',
										'tab' => 'icon',
									],
									'title' => 'Lorem ipsum dolor',
									'text' => 'Curabitur vel mi a odio condimentum dignissim. Mauris iaculis quam id accumsan aliquam. Proin luctus erat in rutrum bibendum.',
									'link' => [
										'link' => '',
									],
									'button_text' => __('Více informací →', 'cms_ve'),
								],
								'4' => [
									'icon' => [
										'icon' => 'camera',
										'size' => '50',
										'color' => '#ffffff',
										'image' => '',
										'tab' => 'icon',
									],
									'title' => 'Lorem ipsum dolor',
									'text' => 'Curabitur vel mi a odio condimentum dignissim. Mauris iaculis quam id accumsan aliquam. Proin luctus erat in rutrum bibendum.',
									'link' => [
										'link' => '',
									],
									'button_text' => __('Více informací →', 'cms_ve'),
								],
								'5' => [
									'icon' => [
										'icon' => 'chat',
										'size' => '50',
										'color' => '#ffffff',
										'image' => '',
										'tab' => 'icon',
									],
									'title' => 'Lorem ipsum dolor',
									'text' => 'Curabitur vel mi a odio condimentum dignissim. Mauris iaculis quam id accumsan aliquam. Proin luctus erat in rutrum bibendum.',
									'link' => [
										'link' => '',
									],
									'button_text' => __('Více informací →', 'cms_ve'),
								],
							],
							'cols' => 'three',
							'style' => '1',
							'background-color' => '#209ccf',
							'font' => [
								'font-size' => '24',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '#ffffff',
							],
							'font_text' => [
								'font-size' => '15',
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
								'hover_color' => [
									'color1' => '',
									'color2' => '#ebebeb',
								],
								'corner' => '90',
								'border-color' => '#bdbdbd',
							],
						],
					],
				],
			],
		],
	],
	'5' => [
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
			'padding_bottom' => '100',
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
						'content' => '<h2 style="text-align: center;">' . __('Poslední články na blogu', 'cms_ve') . '</h2>',
						'style' => [
							'font' => [
								'font-size' => '42',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
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
							'margin_bottom' => '60',
							'delay' => '',
							'animate' => '',
							'id' => '',
							'class' => '',
						],
					],
					'1' => [
						'type' => 'recent_posts',
						'content' => '',
						'style' => [
							'number' => '3',
							'show' => [
								'more' => 'more',
							],
							'style' => '1',
							'cols' => 'three',
							'font' => [
								'font-size' => '20',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'color' => '',
							],
							'font_text' => [
								'font-size' => '15',
								'font-family' => '',
								'weight' => '',
								'line-height' => '1.5',
								'color' => '#6b6b6b',
							],
						],
					],
				],
			],
		],
	],
];

$page = [
	'page' => [
		'title' => __('Úvodní stránka', 'cms_ve'),
		'slug' => __('home', 'cms_ve'),
		'theme' => 'page/1/',
	],
	'setting' => [],
	'layer' => base64_encode(serialize($temp_layer)),
];
