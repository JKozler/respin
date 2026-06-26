<?php
$temp_layer = [
	'0' => [
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
			'padding_top' => '50',
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
							'margin_top' => '5',
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
									'color1' => '#e4960e',
									'color2' => '',
								],
								'corner' => '0',
								'border-color' => '',
								'hover_color' => [
									'color1' => '#e6810e',
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
									'color1' => '#e4960e',
									'color2' => '',
								],
								'corner' => '0',
								'border-color' => '',
								'hover_color' => [
									'color1' => '#e6810e',
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
];
$page = [
	'page' => [
		'title' => __('Blog', 'cms_ve'),
		'slug' => __('blog', 'cms_ve'),
		'theme' => 'page/1/',
		'page_type' => 'blog',
	],
	'setting' => [],
	'layer' => base64_encode(serialize($temp_layer)),
];
