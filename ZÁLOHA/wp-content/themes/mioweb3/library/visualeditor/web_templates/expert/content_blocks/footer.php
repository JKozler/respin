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
			'padding_top' => '70',
			'padding_bottom' => '70',
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
				'type' => 'col-twothree',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'title',
						'content' => '<p style="text-align: left;">' . __('Zde umístěte upoutávku na váš magnet nebo dárek za kontakt. Minimálně pak registraci do newsletteru.', 'cms_ve') . '</p>',
						'style' => [
							'font' => [
								'font-size' => '26',
								'font-family' => 'Open Sans',
								'weight' => '400',
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
				'type' => 'col-three',
				'class' => '',
				'content' => [
					'0' => [
						'type' => 'seform',
						'content' => '',
						'style' => [
							'type' => 'smartemailing',
							'html' => '',
							'email' => '',
							'subject' => '',
							'thx_url' => '',
							'url' => '',
							'form-style' => '1',
							'form-labels' => '1',
							'form-look' => '3',
							'form-font' => [
								'font-size' => '15',
								'color' => '',
							],
							'background' => '#ededed',
							'button_text' => '',
							'button' => [
								'style' => '1',
								'font' => [
									'font-size' => '30',
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
							'popup' => '1',
							'popup_title' => __('Zadejte svůj email a registrujte se', 'cms_ve'),
							'popup_type' => 'button',
							'image' => '',
							'popup_text' => __('Mám zájem', 'cms_ve'),
							'popupbutton' => [
								'style' => '1',
								'font' => [
									'font-size' => '28',
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
							'margin_bottom' => '0',
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
		'title' => __('Opt-in nad patičkou', 'cms_ve'),
		'post_type' => 'cms_footer',
		'theme' => 'footers/empty/',
	],
	'setting' => [],
	'layer' => base64_encode(serialize($temp_layer)),
];
