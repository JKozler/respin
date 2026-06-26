<?php
$temp_layer = [
	'0' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#ffffff',
				'transparency1' => '1.00',
				'rgba1' => 'rgba(255, 255, 255, 1)',
				'color2' => '',
				'transparency2' => '1',
				'rgba2' => '',
			],
			'background_setting' => 'image',
			'background_image' => [
				'position' => '50% 50%',
				'image' => '',
				'imageid' => '',
				'pattern' => '13',
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
				'transparency' => '0.7',
				'rgba' => '',
			],
			'video_type' => 'custom',
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
			'border-top' => [
				'size' => '0',
				'style' => 'solid',
				'color' => '',
			],
			'border-bottom' => [
				'size' => '1',
				'style' => 'solid',
				'color' => '#ededed',
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
				'content' => [
					'0' => [
						'type' => 'title',
						'style' => [
							'font' => [
								'font-size' => '42',
								'font-family' => '',
								'weight' => '700',
								'color' => '',
								'text-shadow' => 'none',
							],
							'mw30' => '1',
							'content' => '<h1 style="text-align: center;">' . __('Název vysílaného webináře', 'cms_ve') . '</h1>',
							'background-color' => [
								'color1' => '#e8e8e8',
								'transparency1' => '1',
								'rgba1' => 'rgba(232,232,232,1)',
							],
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
					'1' => [
						'type' => 'like',
						'style' => [
							'layout' => 'button_count',
							'scheme' => 'light',
							'setting' => [
								'share' => 'share',
							],
							'align' => 'center',
							'mw30' => '1',
							'content' => '',
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
					'2' => [
						'type' => 'video',
						'style' => [
							'content' => '',
							'video_code' => '',
							'setting' => [
								'is_saved' => '1',
							],
							'popup_type' => 'image',
							'image' => [
								'position' => '',
								'image' => '',
								'imageid' => '',
							],
							'play' => [
								'icon' => 'play1',
								'code' => '',
								'color' => '#ffffff',
								'size' => '60',
								'tab' => 'icon',
							],
							'button_text' => __('Spustit video', 'cms_ve'),
							'popupbutton' => [
								'style' => 'basic',
								'custom_setting' => [
									'style' => '1',
									'background_color' => [
										'color1' => '#eb1e47',
										'transparency1' => '1.00',
										'rgba1' => 'rgba(235, 30, 71, 1)',
										'color2' => '',
										'transparency2' => '',
										'rgba2' => '',
									],
									'font-color' => '',
									'corner' => '8',
									'height_padding' => '1.1',
									'width_padding' => '1.8',
									'font' => [
										'font-family' => '',
										'weight' => '',
									],
									'border-color' => '',
									'border_width' => '',
									'hover_effect' => 'darker',
									'hover_color' => [
										'color1' => '',
										'transparency1' => '',
										'rgba1' => '',
										'color2' => '',
										'transparency2' => '',
										'rgba2' => '',
									],
									'hover_font_color' => '',
									'border_hover-color' => '',
								],
								'button_size' => 'medium',
								'custom_size' => '18',
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
							'margin_bottom' => '80',
							'max_width' => '',
							'element_align' => 'center',
							'animate' => '',
							'id' => '',
							'class' => '',
							'delay' => '',
						],
					],
					'3' => [
						'type' => 'title',
						'style' => [
							'font' => [
								'font-size' => '25',
								'tablet' => [
									'font-size' => '',
								],
								'mobile' => [
									'font-size' => '',
								],
								'color' => '',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'letter-spacing' => '',
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
							'content' => '<p style="text-align: center;">' . __('Informace o webináři', 'cms_ve') . '</p>',
							'mw30' => '1',
						],
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
							'margin_bottom' => '',
							'max_width' => '',
							'element_align' => 'center',
							'animate' => '',
							'id' => '',
							'class' => '',
							'delay' => '',
						],
					],
					'4' => [
						'type' => 'text',
						'style' => [
							'font' => [
								'font-family' => '',
								'weight' => '',
								'font-size' => '15',
								'color' => '',
								'line-height' => '',
							],
							'style' => '1',
							'p-background-color' => [
								'color1' => '#e8e8e8',
								'transparency1' => '1',
								'rgba1' => 'rgba(232,232,232,1)',
							],
							'content' => '<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ornare nibh non tellus varius egestas. Nunc quis purus justo. Etiam sodales sagittis dui eu luctus. Nullam id leo nec orci varius porttitor vehicula in ante. Duis ut ante vestibulum, varius neque nec, mollis eros.</p>',
							'li' => '',
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
	],
	'1' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#ffffff',
				'transparency1' => '1',
				'rgba1' => 'rgba(255, 255, 255, 1)',
				'color2' => '',
				'transparency2' => '1',
				'rgba2' => '',
			],
			'background_setting' => 'image',
			'background_image' => [
				'position' => '50% 50%',
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
				'transparency' => '0.7',
				'rgba' => '',
			],
			'video_type' => 'custom',
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
			'content_align' => 'top',
			'text' => 'auto',
			'font' => [
				'font-family' => '',
				'weight' => '',
				'font-size' => '15',
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
				'content' => [
					'0' => [
						'type' => 'title',
						'style' => [
							'font' => [
								'font-size' => '25',
								'tablet' => [
									'font-size' => '',
								],
								'mobile' => [
									'font-size' => '',
								],
								'color' => '',
								'font-family' => '',
								'weight' => '',
								'line-height' => '',
								'letter-spacing' => '',
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
							'content' => '<p style="text-align: center;">' . __('Máte dotaz?', 'cms_ve') . '</p>',
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
					'1' => [
						'type' => 'html',
						'style' => [
							'content' => '<p>' . __('Zde vložte javascript kód pluginu pro vypsání komentářů, které chcete na stránce použít. Pro účely vysílání webináře je potřeba, aby bylo možné napsat komentář bez nutnosti znovunačtení stránky. Vhodné jsou například komentáře', 'cms_ve') . ' <a href="https://disqus.com" target="_blank">disqus</a>.</p>',
							'mw30' => '1',
						],
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
		],
	],
	'2' => [
		'class' => '',
		'style' => [
			'background_color' => [
				'color1' => '#ffffff',
				'transparency1' => '1.00',
				'rgba1' => 'rgba(255, 255, 255, 1)',
				'color2' => '',
				'transparency2' => '1',
				'rgba2' => '',
			],
			'background_setting' => 'image',
			'background_image' => [
				'position' => '50% 50%',
				'image' => '',
				'imageid' => '',
				'pattern' => '13',
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
				'transparency' => '0.7',
				'rgba' => '',
			],
			'video_type' => 'custom',
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
			'row_padding' => 'small',
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
				'size' => '1',
				'style' => 'solid',
				'color' => '#ededed',
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
				'content' => [
					'0' => [
						'type' => 'like',
						'style' => [
							'layout' => 'button_count',
							'scheme' => 'light',
							'setting' => [
								'share' => 'share',
							],
							'align' => 'center',
							'mw30' => '1',
							'content' => '',
						],
					],
				],
			],
		],
	],
];

$config['layer'] = base64_encode(serialize($temp_layer));

$config['setting'] = [
	've_header' => ['show' => 'none'],
	've_footer' => ['show' => 'none'],
	've_appearance' => ['page_width_preset' => '800px'],
];
$config['config'] = [];
