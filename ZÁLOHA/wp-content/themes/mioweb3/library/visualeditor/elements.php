<?php

global $vePage;

$image_lang = get_locale() == 'en_US' ? '_en' : '';

if (get_locale() == 'sk_SK') {
	$guarantee = [
		'guarantee2_sk' => VS_DIR . 'images/image_select/guarantee2_sk.png',
		'guarantee3_sk' => VS_DIR . 'images/image_select/guarantee3_sk.png',
		'guarantee4_sk' => VS_DIR . 'images/image_select/guarantee4_sk.png',
		'guarantee5_sk' => VS_DIR . 'images/image_select/guarantee5_sk.png',
		'guarantee6_sk' => VS_DIR . 'images/image_select/guarantee6_sk.png',
		'guarantee7_sk' => VS_DIR . 'images/image_select/guarantee7_sk.png',
		'guarantee8_sk' => VS_DIR . 'images/image_select/guarantee8_sk.png',
		'guarantee9_sk' => VS_DIR . 'images/image_select/guarantee9_sk.png',
	];
} elseif (get_locale() == 'en_US') {
	$guarantee = [
		'guarantee2_en' => VS_DIR . 'images/image_select/guarantee2_en.png',
		'guarantee3_en' => VS_DIR . 'images/image_select/guarantee3_en.png',
		'guarantee4_en' => VS_DIR . 'images/image_select/guarantee4_en.png',
		'guarantee5_en' => VS_DIR . 'images/image_select/guarantee5_en.png',
		'guarantee6_en' => VS_DIR . 'images/image_select/guarantee6_en.png',
		'guarantee7_en' => VS_DIR . 'images/image_select/guarantee7_en.png',
		'guarantee8_en' => VS_DIR . 'images/image_select/guarantee8_en.png',
		'guarantee9_en' => VS_DIR . 'images/image_select/guarantee9_en.png',
	];
} elseif (get_locale() == 'de_DE') {
	$guarantee = [
		'guarantee2_de' => VS_DIR . 'images/image_select/guarantee2_de.png',
		'guarantee3_de' => VS_DIR . 'images/image_select/guarantee3_de.png',
		'guarantee4_de' => VS_DIR . 'images/image_select/guarantee4_de.png',
		'guarantee5_de' => VS_DIR . 'images/image_select/guarantee5_de.png',
		'guarantee6_de' => VS_DIR . 'images/image_select/guarantee6_de.png',
		'guarantee7_de' => VS_DIR . 'images/image_select/guarantee7_de.png',
		'guarantee8_de' => VS_DIR . 'images/image_select/guarantee8_de.png',
		'guarantee9_de' => VS_DIR . 'images/image_select/guarantee9_de.png',
	];
} else {
	$guarantee = [
		'guarantee1' => VS_DIR . 'images/image_select/guarantee1.png',
		'guarantee2' => VS_DIR . 'images/image_select/guarantee2.png',
		'guarantee3' => VS_DIR . 'images/image_select/guarantee3.png',
		'guarantee4' => VS_DIR . 'images/image_select/guarantee4.png',
		'guarantee5' => VS_DIR . 'images/image_select/guarantee5.png',
		'guarantee6' => VS_DIR . 'images/image_select/guarantee6.png',
		'guarantee7' => VS_DIR . 'images/image_select/guarantee7.png',
		'guarantee8' => VS_DIR . 'images/image_select/guarantee8.png',
		'guarantee9' => VS_DIR . 'images/image_select/guarantee9.png',
	];
}

$vePage->add_element_groups([
	'basic' => [
		'name' => __('Základní', 'cms_ve'),
		'subelement' => true,
	],
	'social' => [
		'name' => __('Sociální sítě', 'cms_ve'),
		'subelement' => true,
	],
	'structure' => [
		'name' => __('Struktura', 'cms_ve'),
		'subelement' => false,
	],
]);

$vePage->add_elements([
	'text' => [
		'name' => __('Textové pole', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/732-element-textove-pole',
		//'description'=>__('Vložte na stránku textové pole, editovatelné pomocí textového editoru.','cms_ve'),
		'tab_setting' => [
			[
				'id' => 'format',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'font',
								'type' => 'font',
								'hidden_setting' => true,
								'content' => [
									'font-size' => '',
									'font-family' => '',
									'weight' => '',
									'line-height' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
									'visible' => true,
								],
								'mobile' => true,
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .in_element_content',
									'setting' => 'set_list_background_position',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Vzhled textu', 'cms_ve'),
								'type' => 'imageselect',
								'options' => [
									'1' => VS_DIR . 'images/image_select/text.jpg',
									'2' => VS_DIR . 'images/image_select/text2.jpg',
									'3' => VS_DIR . 'images/image_select/text3.jpg',
									'4' => VS_DIR . 'images/image_select/text4.jpg',
								],
								'content' => '1',
								'show' => 'p_style',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'border',
								'title' => __('Formátování čar', 'cms_ve'),
								'type' => 'border',
								'content' => [
									'size' => '3',
									'color' => '#d5d5d5',
								],
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'border',
									'target' => ' .ve_text',
								],
								'show_group' => 'p_style',
								'show_val' => '4,3',
							],
							[
								'id' => 'p-background-color',
								'title' => __('Barva pozadí', 'cms_ve'),
								'type' => 'background',
								'content' => ['color1' => '#e8e8e8', 'transparency1' => '1', 'rgba1' => 'rgba(232,232,232,1)'],
								'show_group' => 'p_style',
								'show_val' => '2',
								'onedit' => [
									'action' => 'change_smart_background_color',
									'target' => ' .element_text_style_2',
								],
							],
							[
								'id' => 'content',
								'type' => 'textarea',
								'content' => '',
								'inline' => 1,
							],
							[
								'id' => 'li',
								'title' => __('Styl odrážek', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '',
								'list' => 'list_icons',
								'empty' => ['' => VS_DIR . 'images/image_select/li0.png'],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .in_element_content',
									'class' => 'element_text_li',
								],
							],
						],
					],
				],
			],
		],
	],
	'title' => [
		'name' => __('Nadpis', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/762-element-nadpis',
		'description' => __('Pro vkládání nadpisů do stránky. Každému nadpisu lze nastavit font, barvu a velikost.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'format',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'font',
								'type' => 'font',
								'content' => [
									'font-size' => '30',
									'font-family' => '',
									'weight' => '',
									'line-height' => '1.2',
									'letter-spacing' => '0',
									'color' => '',
									'text-shadow' => '',
								],
								'setting' => [
									'max_font_size' => '100',
									'visible' => true,
								],
								'mobile' => true,
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .ve_title',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Vzhled nadpisu', 'cms_ve'),
								'type' => 'imageselect',
								'options' => [
									'1' => VS_DIR . 'images/image_select/title1.jpg',
									'2' => VS_DIR . 'images/image_select/title2.jpg',
									'3' => VS_DIR . 'images/image_select/title3.jpg',
									'8' => VS_DIR . 'images/image_select/title8.jpg',
									'4' => VS_DIR . 'images/image_select/title4.jpg',
									'5' => VS_DIR . 'images/image_select/title5.jpg',
									'7' => VS_DIR . 'images/image_select/title7.jpg',
									'6' => VS_DIR . 'images/image_select/title6.jpg',

								],
								'content' => '1',
								'show' => 'title_style',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'border',
								'title' => __('Formátování čar', 'cms_ve'),
								'type' => 'border',
								'content' => [
									'size' => '3',
									'color' => '#d5d5d5',
								],
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'border',
									'target' => ' .ve_title',
								],
								'show_group' => 'title_style',
								'show_val' => '4,5,7,8',
							],
							[
								'id' => 'background-color',
								'title' => __('Barva pozadí', 'cms_ve'),
								'type' => 'background',
								'content' => ['color1' => '#e8e8e8', 'transparency1' => '1', 'rgba1' => 'rgba(232,232,232,1)'],
								'show_group' => 'title_style',
								'show_val' => '2,3',
								'onedit' => [
									'action' => 'change_smart_background_color',
									'target' => ' .ve_title',
								],
							],
							[
								'id' => 'decoration-color',
								'title' => __('Barva podrtžení', 'cms_ve'),
								'type' => 'color',
								'content' => '#158ebf',
								'onedit' => [
									'action' => 'change_css',
									'css' => 'background',
									'target' => ' .ve_title_decoration',
								],
								'show_group' => 'title_style',
								'show_val' => '6',
							],
							[
								'id' => 'decoration-padding',
								'title' => __('Odsazení podrtžení', 'cms_ve'),
								'type' => 'slider',
								'content' => '',
								'setting' => [
									'min' => '5',
									'max' => '50',
									'default' => '5',
									'unit' => 'px',
								],
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'padding-bottom',
									'target' => ' .ve_title',
								],
								'show_group' => 'title_style',
								'show_val' => '6',
							],
							[
								'name' => __('Zarovnání', 'cms_ve'),
								'id' => 'align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'align-left',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'align-center',
										'text' => __('Na střed', 'cms_ve'),
									],
									'right' => [
										'icon' => 'align-right',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .ve_title',
									'class' => 've_title_',
								],
								'content' => 'center',
								'show_group' => 'title_style',
								'show_val' => '3,6,7,8',
							],
							[
								'id' => 'content',
								'type' => 'textarea',
								'content' => '',
								'inline' => 1,
							],
						],
					],
				],
			],
		],

	],
	'button' => [
		'name' => __('Tlačítko', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/761-element-tlacitko',
		'description' => __('Vyberte si z několika typů tlačítek a přizpůsobte ho barevně podle svých představ.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Tlačítko', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'content',
								'title' => __('Text tlačítka', 'cms_ve'),
								'type' => 'text',
								'content' => __('Text tlačítka', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_content_first_button .ve_but_text',
								],
							],
							[
								'id' => 'subtext',
								'title' => __('Podtext', 'cms_ve'),
								'type' => 'text',
								'content' => '',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .ve_content_first_button .ve_button_subtext',
								],
							],
							[
								'name' => __('Zarovnání', 'cms_ve'),
								'id' => 'align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'align-left',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'align-center',
										'text' => __('Na střed', 'cms_ve'),
									],
									'right' => [
										'icon' => 'align-right',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'class' => 'in_element_content_button_',
									'target' => ' .in_element_content_button',
								],
								'content' => 'center',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'show',
								'title' => __('Po kliknutí na tlačítko', 'cms_ve'),
								'type' => 'select',
								'content' => 'url',
								'options' => [
									['name' => __('Otevřít stránku', 'cms_ve'), 'value' => 'url'],
									['name' => __('Zobrazit pop-up', 'cms_ve'), 'value' => 'popup'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'buttonaction',
							],
							[
								'id' => 'link',
								'title' => __('Odkazovat na', 'cms_ve'),
								'type' => 'page_link',
								'show_group' => 'buttonaction',
								'show_val' => 'url',
								'onedit' => [
									'action' => 'change_link',
									'target' => ' .ve_content_first_button',
								],
							],
							[
								'title' => __('Zobrazit pop-up', 'cms_ve'),
								'id' => 'popup',
								'type' => 'popupselect',
								'show_group' => 'buttonaction',
								'show_val' => 'popup',
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'button_style',
								'title' => __('Vzhled tlačítka', 'cms_ve'),
								'tooltip' => __('Nastavení vzhledu tlačítek naleznete v nastavení vzhledu webu.', 'cms_ve'),
								'type' => 'button',
								'content' => [
									'icon' => [
										'icon' => 'download',
										'icon_set' => 'feather',
										'color' => '',
									],

								],
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_first_button',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'show_icon',
								'title' => '',
								'type' => 'switch',
								'label' => __('Zobrazit ikonu tlačítka', 'cms_ve'),
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'icon_setting',
							],
							[
								'id' => 'icon',
								'title' => __('Ikona', 'cms_ve'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'download',
									'icon_set' => 'feather',
								],
								'onedit' => [
									'action' => 'change_icon_simple',
									'target' => ' .ve_content_first_button .ve_but_icon',
								],
								'show_group' => 'icon_setting',
								'show_val' => '1',
							],
							[
								'name' => __('Velikost ikony', 'cms_ve'),
								'id' => 'icon_size',
								'type' => 'imageoption',
								'options' => [
									'1.1' => [
										'icon' => 'ti-medium',
										'text' => __('Základní', 'cms_ve'),
									],
									'1.5' => [
										'icon' => 'ti-big',
										'text' => __('Větší', 'cms_ve'),
									],
									'2' => [
										'icon' => 'ti-bigest',
										'text' => __('Největší', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'font-size-em',
									'target' => ' .ve_content_first_button .ve_but_icon',
								],
								'content' => '1.1',
								'show_group' => 'icon_setting',
								'show_val' => '1',
							],
							[
								'name' => __('Umístění ikony', 'cms_ve'),
								'id' => 'icon_align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'onleft',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'right' => [
										'icon' => 'onright',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'content' => 'left',
								'show_group' => 'icon_setting',
								'show_val' => '1',
							],
						],
					],
				],
			],
			[
				'id' => 'double',
				'name' => __('Dvojtlačítko', 'cms_ve'),
				'setting' => [
					[
						'id' => 'show_but2',
						'title' => '',
						'type' => 'switch',
						'label' => __('Zobrazit druhé tlačítko', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'but2_setting',
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'but2_setting',
						'show_val' => '1',
						'setting' => [
							[
								'id' => 'text2',
								'title' => __('Text tlačítka', 'cms_ve'),
								'type' => 'text',
								'content' => __('Text tlačítka', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_content_second_button .ve_but_text',
								],
							],
							[
								'id' => 'subtext2',
								'title' => __('Podtext', 'cms_ve'),
								'type' => 'text',
								'content' => '',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .ve_content_second_button .ve_button_subtext',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'but2_setting',
						'show_val' => '1',
						'setting' => [
							[
								'id' => 'show2',
								'title' => __('Po kliknutí na tlačítko', 'cms_ve'),
								'type' => 'select',
								'content' => 'url',
								'options' => [
									['name' => __('Otevřít stránku', 'cms_ve'), 'value' => 'url'],
									['name' => __('Zobrazit pop-up', 'cms_ve'), 'value' => 'popup'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'buttonaction2',
							],
							[
								'id' => 'link2',
								'title' => __('Odkazovat na', 'cms_ve'),
								'type' => 'page_link',
								'show_group' => 'buttonaction2',
								'show_val' => 'url',
							],
							[
								'title' => __('Zobrazit pop-up', 'cms_ve'),
								'id' => 'popup2',
								'type' => 'popupselect',
								'show_group' => 'buttonaction2',
								'show_val' => 'popup',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'but2_setting',
						'show_val' => '1',
						'setting' => [
							[
								'id' => 'button_style2',
								'title' => __('Vzhled druhého tlačítka', 'cms_ve'),
								'type' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_second_button',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'but2_setting',
						'show_val' => '1',
						'setting' => [
							[
								'id' => 'show_icon2',
								'title' => '',
								'type' => 'switch',
								'label' => __('Zobrazit ikonu tlačítka', 'cms_ve'),
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'icon_setting2',
							],
							[
								'id' => 'icon2',
								'title' => __('Ikona', 'cms_ve'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'download',
									'icon_set' => 'feather',
								],
								'onedit' => [
									'action' => 'change_icon_simple',
									'target' => ' .ve_content_second_button .ve_but_icon',
								],
								'show_group' => 'icon_setting2',
								'show_val' => '1',
							],
							[
								'name' => __('Velikost ikony', 'cms_ve'),
								'id' => 'icon_size2',
								'type' => 'imageoption',
								'options' => [
									'1.1' => [
										'icon' => 'ti-medium',
										'text' => __('Základní', 'cms_ve'),
									],
									'1.5' => [
										'icon' => 'ti-big',
										'text' => __('Větší', 'cms_ve'),
									],
									'2' => [
										'icon' => 'ti-bigest',
										'text' => __('Největší', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'font-size-em',
									'target' => ' .ve_content_second_button .ve_but_icon',
								],
								'content' => '1.1',
								'show_group' => 'icon_setting2',
								'show_val' => '1',
							],
							[
								'name' => __('Umístění ikony', 'cms_ve'),
								'id' => 'icon_align2',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'onleft',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'right' => [
										'icon' => 'onright',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'content' => 'left',
								'show_group' => 'icon_setting2',
								'show_val' => '1',
							],
						],
					],


				],
			],
		],
	],
	'image' => [
		'name' => __('Obrázek', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/765-element-obrazek',
		'description' => __('Pro vkládání obrázků. Můžete zadat popisek, vybrat z několika druhů rámečků a zadat velký obrázek, který se otevře po kliknutí. Obrázek může sloužit i jako odkaz.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'image',
								'title' => '',
								'type' => 'image',
								'respect_size' => true,
								'content' => [
									'position' => '50% 50%',
								],
								'onedit' => [
									'action' => 'change_img',
									'target' => ' .element_image_container img',
								],
							],
							[
								'id' => 'thumb_name',
								'title' => __('Zobrazit obrázek v poměru:', 'cms_ve'),
								'type' => 'select',
								'content' => '',
								'options' => [
									['name' => __('Původní', 'cms_ve'), 'value' => ''],
									['name' => __('Široký (16:9)', 'cms_ve'), 'value' => '169'],
									['name' => __('Základní (3:2)', 'cms_ve'), 'value' => '32'],
									['name' => __('Střední (4:3)', 'cms_ve'), 'value' => '43'],
									['name' => __('Čtverec (1:1)', 'cms_ve'), 'value' => '11'],
									['name' => __('Základní na výšku (2:3)', 'cms_ve'), 'value' => '23'],
									['name' => __('Střední na výšku (3:4)', 'cms_ve'), 'value' => '34'],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'max-width',
								'title' => __('Maximální šířka obrázku', 'cms_ve'),
								'type' => 'slider',
								'setting' => [
									'min' => '100',
									'max' => '1000',
									'default' => '1000',
									'unit' => 'px',
								],
								'onedit' => [
									'action' => 'change_style_variable',
									'css' => '--image-width-',
								],
								'content' => '',
							],
							[
								'name' => __('Zarovnání obrázku', 'cms_ve'),
								'id' => 'align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'onleft',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'oncenter',
										'text' => __('Na střed', 'cms_ve'),
									],
									'right' => [
										'icon' => 'onright',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .element_image',
									'class' => 've_',
								],
								'show_group' => 'row_max_width',
								'show_val' => '1',
								'content' => 'center',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'label',
								'title' => __('Popisek pod obrázkem', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .element_image_label',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'click_action',
								'title' => __('Akce po kliku na obrázek', 'cms_ve'),
								'type' => 'select',
								'options' => [
									['name' => __('Žádná', 'cms_ve'), 'value' => 'none'],
									['name' => __('Otevřít obrázek', 'cms_ve'), 'value' => 'image'],
									['name' => __('Otevřít odkaz', 'cms_ve'), 'value' => 'link'],
									['name' => __('Vyskakovací zpráva (alert)', 'cms_ve'), 'value' => 'alert'],
									['name' => __('Vlastní pop-up', 'cms_ve'), 'value' => 'popup'],
								],
								'content' => 'none',
								'show' => 'caction',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'alert',
								'title' => __('Vyskakovací zpráva', 'cms_ve'),
								'type' => 'text',
								'show_group' => 'caction',
								'show_val' => 'alert',
								'onedit' => [
									'action' => 'change_attr',
									'setting' => 'data-alert',
									'target' => ' .element_image_container',
								],
							],
							[
								'title' => __('Zobrazit pop-up', 'cms_ve'),
								'id' => 'popup',
								'type' => 'popupselect',
								'show_group' => 'caction',
								'show_val' => 'popup',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'link',
								'title' => __('Odkaz (URL adresa)', 'cms_ve'),
								'type' => 'page_link',
								'onedit' => [
									'action' => 'change_link',
									'target' => ' .element_image_container a',
								],
								'show_group' => 'caction',
								'show_val' => 'link',
							],
							[
								'id' => 'large_image',
								'title' => __('Otevřít obrázek', 'cms_ve'),
								'type' => 'image',
								'onedit' => [
									'action' => 'change_attr',
									'setting' => 'href',
									'target' => ' .element_image_large',
								],
								'show_group' => 'caction',
								'show_val' => 'image',
							],
						],
					],
				],
			],
			[
				'id' => 'appearance',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'hover',
								'title' => __('Efekt po najetí myši', 'cms_ve'),
								'type' => 'select',
								'content' => '',
								'options' => [
									['name' => 'Žádný', 'value' => ''],
									['name' => 'Zoom', 'value' => 'zoom'],
									['name' => 'Zvětšení', 'value' => 'scale'],
									['name' => 'Podbarvení s ikonkou', 'value' => 'overlay_icon'],
									['name' => 'Odbarvení', 'value' => 'greyout'],
									['name' => 'Zbarvení', 'value' => 'colorout'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'hover_efect',
							],
							[
								'id' => 'hover_color',
								'title' => __('Barva podbarvení po najetí myši', 'cms_ve'),
								'type' => 'transparent_color',
								'content' => [
									'color' => '#000000',
									'transparency' => '0.3',
									'rgba' => 'rgba(0,0,0,0.3)',
								],
								'onedit' => [
									'action' => 'change_css',
									'css' => 'background',
									'target' => ' .element_image_overlay_icon_container',
								],
								'show_group' => 'hover_efect',
								'show_val' => 'overlay_icon',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'img_style',
								'title' => __('Styl obrázku', 'cms_ve'),
								'type' => 'imageselect',
								'options' => [
									'1' => VS_DIR . 'images/image_select/image1.jpg',
									//'2' => VS_DIR.'images/image_select/image2.png',
									//'3' => VS_DIR.'images/image_select/image3.png',
									//'4' => VS_DIR.'images/image_select/image4.png',
									//'5' => VS_DIR.'images/image_select/image5.png',
									'3' => VS_DIR . 'images/image_select/image3.jpg',
									'2' => VS_DIR . 'images/image_select/image2.jpg',
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'img_style',
								'content' => '1',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'border',
								'title' => __('Rámeček obrázku', 'cms_ve'),
								'type' => 'border',
								'group' => 'input',
								'content' => [
									'size' => '0',
									'color' => '#eeeeee',
								],
								'setting' => [
									'max_size' => 30,
								],
								'onedit' => [
									'action' => 'change_styles',
									'target' => ' .element_image_container',
									'css' => 'border',
								],
							],
							[
								'name' => __('Zakulacení rohů', 'cms_ve'),
								'id' => 'corner',
								'type' => 'imageoption',
								'options' => [
									'' => [
										'icon' => 'sharp_corner',
										'text' => __('Ostré', 'cms_ve'),
									],
									'1' => [
										'icon' => 'rounded_corner',
										'text' => __('Zakulacené', 'cms_ve'),
									],
									'2' => [
										'icon' => 'round_corner',
										'text' => __('Kulaté', 'cms_ve'),
									],
								],
								'show_group' => 'img_style',
								'show_val' => '1,2',
								'onedit' => [
									'action' => 'change_class',
									'class' => 'mw_element_item_corners',
									'target' => ' .element_image_container',
								],
								'content' => '',
							],
							[
								'id' => 'shadow',
								'title' => __('Stín', 'cms_ve'),
								'type' => 'select',
								'content' => '',
								'options' => [
									['name' => __('Bez stínu', 'cms_ve'), 'value' => ''],
									['name' => __('Malý stín', 'cms_ve'), 'value' => '5'],
									['name' => __('Základní stín', 'cms_ve'), 'value' => '1'],
									['name' => __('Větší stín', 'cms_ve'), 'value' => '3'],
									['name' => __('Spodní stín', 'cms_ve'), 'value' => '4'],
									['name' => __('Stín vpravo dole', 'cms_ve'), 'value' => '2'],
								],
								'onedit' => [
									'action' => 'change_class',
									'class' => 'mw_element_item_shadow',
									'target' => ' .element_image_container',
								],
							],
						],
					],

				],
			],
		],

	],


	'video' => [
		'name' => __('Video', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/763-element-video',
		'description' => __('Vložte na stránku video jednoduše zadáním odkazu na YouTube nebo Vimeo stránku s videem. Můžete zadat i vlastní embed kód videa.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'video',
				'name' => __('Video', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'content',
								'content' => '',
								'title' => __('URL videa', 'cms_ve'),
								'type' => 'text',
								'tooltip' => __('Vložte URL stránky s YouTube nebo Vimeo videem.', 'cms_ve'),
								'show_group' => 'ownvideo',
								'show_val' => '0',
								'class' => 'set_form_row_nb',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'video_code',
								'title' => __('Vlastní kód videa', 'cms_ve'),
								'type' => 'textarea',
								'show_group' => 'ownvideo',
								'show_val' => '1',
								'class' => 'set_form_row_nb',
								'onedit' => [
									'action' => 'reload',
								],
								'desc' => __('Zde můžete vložit kód videa. Video se vygeneruje podle tohoto kódu a bude ignorovat ostatní nastavení elementu, kromě zarovnání. Video bude responzivní, a proto bude ignorovat nastavení velikosti.', 'cms_ve'),
							],
							[
								'id' => 'own_video',
								'type' => 'switch',
								'label' => __('Vlastní kód videa', 'cms_ve'),
								'show' => 'ownvideo',
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							/*
								array(
									'id'=>'setting',
									'type' => 'multiple_checkbox',
									'options' => array(
										array('name' => __('Přehrát automaticky','cms_ve'), 'value' => 'autoplay'),
										array('name' => __('Skrýt ovládání videa','cms_ve'), 'value' => 'hide_control'),
									),
									'onedit'=>array(
										'action'=>'reload'
									)
								),*/
							[
								'id' => 'autoplay',
								'type' => 'switch',
								'label' => __('Přehrát automaticky', 'cms_ve'),
								'desc' => __('Automatické přehrání je některými prohlížeči blokováno, proto se v některých prohlížečích video automaticky nespustí a musí jej spustit uživatel.', 'cms_ve'),
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'hide_control',
								'type' => 'switch',
								'label' => __('Skrýt ovládání videa', 'cms_ve'),
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'noclick',
								'type' => 'switch',
								'label' => __('Na video nelze kliknout', 'cms_ve'),
								'desc' => __('Video se bude automaticky přehrávat a skryjou se ovládací prvky. Na video nepůjde kliknout, proto nepůjde zastavit ani posunout. V prohlížečích, které blokují automatické spuštění, půjde kliknout pro spuštění videa.', 'cms_ve'),
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],

				],
			],
			[
				'id' => 'popup',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'popup',
								'title' => '',
								'label' => __('Otevírat v pop-upu', 'cms_ve'),
								'type' => 'switch',
								'show' => 'popupset',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'popup_type',
								'title' => __('Otevírat pomocí', 'cms_ve'),
								'type' => 'select',
								'show' => 'popup_type',
								'content' => 'image',
								'options' => [
									['name' => __('Obrázku', 'cms_ve'), 'value' => 'image'],
									['name' => __('Tlačítka', 'cms_ve'), 'value' => 'button'],
									['name' => __('Ikony', 'cms_ve'), 'value' => 'icon'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'popupset',
								'show_val' => '1',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [

							[
								'id' => 'image',
								'title' => __('Obrázek videa', 'cms_ve'),
								'type' => 'image',
								'onedit' => [
									'action' => 'change_img',
									'target' => '.element_image img',
								],
								'show_group' => 'popup_type',
								'show_val' => 'image',
							],
							[
								'id' => 'play',
								'title' => __('Ikona „Play“', 'cms_ve'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'play1',
									'size' => '60',
									'color' => '#f60002',
								],
								'icons' => [
									'play1' => MW_ICONS_URL . 'content-icons.svg',
									'play2' => MW_ICONS_URL . 'content-icons.svg',
									'play3' => MW_ICONS_URL . 'content-icons.svg',
									'play5' => MW_ICONS_URL . 'content-icons.svg',
									'play6' => MW_ICONS_URL . 'content-icons.svg',
								],
								'onedit' => [
									'action' => 'change_icon',
									'target' => ' .video_play_button',
								],
								'show_group' => 'popup_type',
								'show_val' => 'image,icon',
							],
							[
								'id' => 'icon_text',
								'title' => __('Text u ikony', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_icon_text-text',
								],
								'show_group' => 'popup_type',
								'show_val' => 'icon',
							],
							[
								'id' => 'icon_text_align',
								'title' => __('Umístění textu', 'cms_ve'),
								'type' => 'select',
								'options' => [
									['name' => __('Vedle ikony', 'cms_ve'), 'value' => 'beside'],
									['name' => __('Pod ikonou', 'cms_ve'), 'value' => 'under'],
								],
								'content' => 'beside',
								'show' => 'style',
								'onedit' => [
									'action' => 'change_class',
									'class' => 'mw_icon_text_style_',
									'target' => ' .mw_icon_text',
								],
								'show_group' => 'popup_type',
								'show_val' => 'icon',
							],
							[
								'id' => 'button_text',
								'title' => __('Text tlačítka', 'cms_ve'),
								'type' => 'text',
								'content' => __('Spustit video', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_content_button .ve_but_text',
								],
								'show_group' => 'popup_type',
								'show_val' => 'button',
							],
							[
								'id' => 'popupbutton',
								'title' => __('Styl tlačítka', 'cms_ve'),
								'type' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_button',
								],
								'show_group' => 'popup_type',
								'show_val' => 'button',
							],
							[
								'name' => __('Zarovnání', 'cms_ve'),
								'id' => 'align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'align-left',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'align-center',
										'text' => __('Na střed', 'cms_ve'),
									],
									'right' => [
										'icon' => 'align-right',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'class' => 've_content_button_',
									'target' => ' .ve_content_button',
								],
								'content' => 'center',
								'show_group' => 'popup_type',
								'show_val' => 'button,icon',
							],
						],
						'show_group' => 'popupset',
						'show_val' => '1',
					],
				],
			],
		],
	],

	'image_gallery' => [
		'name' => __('Galerie', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/799-element-galerie',
		'description' => __('Vloží na stránku galerii obrázků.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'basic',
				'name' => __('Galerie', 'cms_ve'),
				'setting' => [
					[
						'id' => 'image_gallery_items',
						'title' => '',
						'type' => 'image_gallery',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'click_action',
								'title' => __('Akce po kliku na obrázek', 'cms_ve'),
								'type' => 'select',
								'options' => [
									['name' => __('Žádná', 'cms_ve'), 'value' => 'none'],
									['name' => __('Otevřít obrázek', 'cms_ve'), 'value' => 'image'],

								],
								'content' => 'image',
								'show' => 'caction',
								'onedit' => [
									'action' => 'reload',
								],
							],

						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'thumb_name',
						'title' => __('Zobrazit obrázky v poměru:', 'cms_ve'),
						'type' => 'select',
						'content' => '32',
						'options' => [
							['name' => __('Původní', 'cms_ve'), 'value' => ''],
							['name' => __('Široký (16:9)', 'cms_ve'), 'value' => '169'],
							['name' => __('Základní (3:2)', 'cms_ve'), 'value' => '32'],
							['name' => __('Střední (4:3)', 'cms_ve'), 'value' => '43'],
							['name' => __('Čtverec (1:1)', 'cms_ve'), 'value' => '11'],
							['name' => __('Základní na výšku (2:3)', 'cms_ve'), 'value' => '23'],
							['name' => __('Střední na výšku (3:4)', 'cms_ve'), 'value' => '34'],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'cols',
						'title' => __('Počet sloupců galerie', 'cms_ve'),
						'type' => 'select',
						'content' => 0,
						'options' => [
							['name' => __('Automaticky', 'cms_ve'), 'value' => 0],
							['name' => '1', 'value' => 1],
							['name' => '2', 'value' => 2],
							['name' => '3', 'value' => 3],
							['name' => '4', 'value' => 4],
							['name' => '5', 'value' => 5],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'cols_type',
						'title' => __('Mezery mezi obrázky', 'cms_ve'),
						'type' => 'select',
						'content' => 's',
						'options' => [
							['name' => 'Velké', 'value' => ''],
							['name' => 'Malé', 'value' => 'smallcols'],
							['name' => 'Žádné', 'value' => 'fullcols'],
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .image_gallery_element',
							'class' => '',
						],
					],
					[
						'id' => 'hover',
						'title' => __('Efekt po najetí myši', 'cms_ve'),
						'type' => 'select',
						'content' => 'zoom',
						'options' => [
							['name' => 'Žádný', 'value' => ''],
							['name' => 'Zoom', 'value' => 'zoom'],
							['name' => 'Zvětšení', 'value' => 'scale'],
							['name' => 'Podbarvení s ikonkou', 'value' => 'overlay_icon'],
							['name' => 'Odbarvení', 'value' => 'greyout'],
							['name' => 'Zbarvení', 'value' => 'colorout'],
						],
						'show' => 'hover_efect',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'hover_color',
						'title' => __('Barva podbarvení po najetí myši', 'cms_ve'),
						'type' => 'transparent_color',
						'content' => [
							'color' => '#179edc',
							'transparency' => '0.7',
						],
						'onedit' => [
							'action' => 'change_css',
							'css' => 'background',
							'target' => ' .element_image_overlay_icon_container',
						],
						'show_group' => 'hover_efect',
						'show_val' => 'overlay_icon',
					],
					[
						'id' => 'gallery_style',
						'title' => __('Způsob zobrazení', 'cms_ve'),
						'type' => 'imageselect',
						'options' => [
							'no_captions' => VS_DIR . 'images/image_select/gallery1.jpg',
							'captions_over' => VS_DIR . 'images/image_select/gallery2.jpg',
							'captions_below' => VS_DIR . 'images/image_select/gallery3.jpg',
						],
						'content' => 'no_captions',
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'gallery_style',
					],
					[
						'id' => 'font',
						'title' => __('Písmo popisků', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '16',
							'use-font' => 'text',
							'align' => '',
						],
						'setting' => [
							'max_font_size' => '30',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .image_gallery_element__item__caption',
						],
						'show_group' => 'gallery_style',
						'show_val' => 'captions_over,captions_below',
					],
				],
			],
			[
				'id' => 'slider',
				'name' => __('Slider', 'cms_ve'),
				'setting' => [
					[
						'id' => 'use_slider',
						'title' => '',
						'type' => 'switch',
						'label' => __('Zobrazit jako slider', 'cms_ve'),
						'show' => 'sliderset',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'miocarousel_setting',
						'type' => 'miocarousel',
						'content' => [
							'animation' => 'fade',
							'delay' => '3500',
							'speed' => '1000',
							'color_scheme' => '',
						],
						'onedit' => [
							'action' => 'change_slider',
							'target' => ' .miocarousel',
						],
						'show_group' => 'sliderset',
						'show_val' => '1',
					],
				],
			],
		],
	],
	'image_text' => [
		'name' => __('Obrázek s textem', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/800-element-obrazek-s-textem',
		'description' => __('Obrázek s textem na levé nebo pravé straně je vhodný do situací, kdy chcete obsah rozdělit na dvě části, kdy na jedné straně bude obrázek a na druhé text.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'text',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'image',
						'title' => __('Obrázek', 'cms_ve'),
						'type' => 'image',
						'content' => [
							'image' => '',
							'position' => '50% 50%',
						],
						'onedit' => [
							'action' => 'change_img',
							'target' => ' .el_it_image img',
						],
					],
					[
						'title' => __('Nadpis', 'cms_ve'),
						'id' => 'title',
						'type' => 'text',
						'content' => __('Nadpis elementu', 'cms_ve'),
						'inline' => 1,
					],
					[
						'title' => __('Text', 'cms_ve'),
						'id' => 'content',
						'type' => 'editor',
						'content' => __('Začněte psát zde.', 'cms_ve') . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sodales ipsum ut leo condimentum, sed auctor ipsum mattis.',
						'inline' => 1,
					],
					[
						'label' => __('Skrýt nadpis', 'cms_ve'),
						'id' => 'hide_title',
						'type' => 'switch',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'label' => __('Skrýt text', 'cms_ve'),
						'id' => 'hide_text',
						'type' => 'switch',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'label' => __('Zobrazit tlačítko', 'cms_ve'),
						'id' => 'show_button',
						'type' => 'switch',
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'show_button',
					],
					[
						'title' => __('Text tlačítka', 'cms_ve'),
						'id' => 'button_text',
						'type' => 'text',
						'content' => __('Více informací', 'cms_ve'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .ve_content_button .ve_but_text',
						],
						'show_group' => 'show_button',
						'show_val' => '1',
					],
					[
						'title' => __('Odkaz tlačítka', 'cms_ve'),
						'id' => 'button_link',
						'type' => 'page_link',
						'onedit' => [
							'action' => 'change_link',
							'target' => ' .ve_content_button',
						],
						'show_group' => 'show_button',
						'show_val' => '1',
					],

				],

			],
			[
				'id' => 'format',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'visual_style',
								'title' => __('Styl elementu', 'cms_ve'),
								'type' => 'imageselect',
								'options' => [
									'1' => VS_DIR . 'images/image_select/image-text1.jpg',
									'2' => VS_DIR . 'images/image_select/image-text2.jpg',
									'3' => VS_DIR . 'images/image_select/image-text3.jpg',
									//'4' => VS_DIR.'images/image_select/image-text2'.$image_lang.'.jpg',
									'5' => VS_DIR . 'images/image_select/image-text5.jpg',
									'6' => VS_DIR . 'images/image_select/image-text6.jpg',
								],
								'content' => '1',
								'show' => 'visual_style',
								'onedit' => [
									'action' => 'reload',
								],

							],
							[
								'id' => 'background_color',
								'title' => __('Barva pozadí', 'cms_ve'),
								'type' => 'color',
								'show_group' => 'visual_style',
								'show_val' => '2,3',
								'content' => '#eeeeee',
								'onedit' => [
									'action' => 'change_smart_background_color',
									'target' => ' .el_it_background',
								],
							],
							[
								'id' => 'overlay_color',
								'title' => __('Barva pozadí', 'cms_ve'),
								'type' => 'transparent_color',
								'show_group' => 'visual_style',
								'show_val' => '5',
								'content' => [
									'color' => '#000000',
									'transparency' => '0.5',
									'rgba' => 'rgba(0,0,0,0.5)',
								],
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'background',
									'target' => ' .el_it_background',
								],
							],
							[
								'label' => __('Zobrazit obsah po najetí', 'cms_ve'),
								'id' => 'hide_content',
								'type' => 'switch',
								'onedit' => [
									'action' => 'toggle_class',
									'target' => ' .in_element_content',
									'class' => 'el_it_hover_content',
								],
								'show_group' => 'visual_style',
								'show_val' => '5',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'image_ratio',
								'title' => __('Zobrazit obrázek v poměru:', 'cms_ve'),
								'type' => 'select',
								'content' => '',
								'options' => [
									['name' => __('Původní', 'cms_ve'), 'value' => ''],
									['name' => __('Široký (16:9)', 'cms_ve'), 'value' => '169'],
									['name' => __('Základní (3:2)', 'cms_ve'), 'value' => '32'],
									['name' => __('Střední (4:3)', 'cms_ve'), 'value' => '43'],
									['name' => __('Čtverec (1:1)', 'cms_ve'), 'value' => '11'],
									['name' => __('Základní na výšku (2:3)', 'cms_ve'), 'value' => '23'],
									['name' => __('Střední na výšku (3:4)', 'cms_ve'), 'value' => '34'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'visual_style',
								'show_val' => '1,2,3,5',
							],
							[
								'id' => 'style',
								'title' => __('Velikost obrázku', 'cms_ve'),
								'type' => 'select',
								'options' => [
									['name' => '1/2', 'value' => 'two'],
									['name' => '1/3', 'value' => 'three'],
									['name' => '2/3', 'value' => 'twothree'],
									['name' => '1/4', 'value' => 'four'],
									['name' => '1/5', 'value' => 'five'],
								],
								'content' => 'two',
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'visual_style',
								'show_val' => '1,2,3,6',
							],
							[
								'name' => __('Umístění obrázku', 'cms_ve'),
								'id' => 'align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'onleft',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'right' => [
										'icon' => 'onright',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'content' => 'left',
								'show_group' => 'visual_style',
								'show_val' => '1,2,3,6',
							],

						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'name' => __('Zarovnání textu', 'cms_ve'),
								'id' => 'text-align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'align-left',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'align-center',
										'text' => __('Na střed', 'cms_ve'),
									],
									'right' => [
										'icon' => 'align-right',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .el_it_text',
									'class' => 've_',
								],
								'content' => 'left',
							],
							[
								'name' => __('Vertikální zarovnání', 'cms_ve'),
								'id' => 'valign',
								'type' => 'imageoption',
								'options' => [
									'top' => [
										'icon' => 'valign-top',
										'text' => __('Nahoru', 'cms_ve'),
									],
									'center' => [
										'icon' => 'valign-center',
										'text' => __('Na střed', 'cms_ve'),
									],
									'bottom' => [
										'icon' => 'valign-bottom',
										'text' => __('Dolů', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .in_element_content',
									'class' => 've_valign_',
								],
								'content' => 'center',
							],
							[
								'id' => 'font',
								'title' => __('Formátování nadpisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '30',
									'use-font' => 'title',
									'color' => '',
								],
								'mobile' => true,
								'setting' => [
									'max_font_size' => '70',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .el_it_text h3',
								],
							],
							[
								'id' => 'font_text',
								'title' => __('Formátování textu', 'cms_ve'),
								'type' => 'font',
								'mobile' => true,
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .el_it_text .entry_content',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'button',
								'title' => __('Vzhled tlačítka', 'cms_ve'),
								'type' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_button',
								],
								'content' => [
									'button_size' => 'small',
								],
							],
						],

					],
				],
			],
		],
	],
	'icon' => [
		'name' => __('Ikona', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/862-element-ikona',
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'icon',
								'title' => __('Ikona', 'cms_ve'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'star',
									'icon_set' => 'feather',
								],
								'onedit' => [
									'action' => 'change_icon',
									'target' => ' .mw_icon i',
								],
							],
							[
								'id' => 'title',
								'title' => __('Nadpis u ikony', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_icon_text-title',
								],
							],
							[
								'id' => 'text',
								'title' => __('Text u ikony', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_icon_text-text',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'link',
								'title' => __('Odkaz', 'cms_ve'),
								'type' => 'page_link',
								'target' => true,
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'name' => __('Zarovnání', 'cms_ve'),
								'id' => 'align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'align-left',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'align-center',
										'text' => __('Na střed', 'cms_ve'),
									],
									'right' => [
										'icon' => 'align-right',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_icon_text',
									'class' => 'mw_icon_text_align_',
								],
								'content' => 'center',
							],
						],
					],

				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Umístění textu', 'cms_ve'),
								'type' => 'select',
								'options' => [
									['name' => __('Vedle ikony', 'cms_ve'), 'value' => 'beside'],
									['name' => __('Pod ikonou', 'cms_ve'), 'value' => 'under'],
								],
								'content' => 'beside',
								'show' => 'style',
								'onedit' => [
									'action' => 'change_class',
									'class' => 'mw_icon_text_style_',
									'target' => ' .mw_icon_text',
								],
							],
							[
								'name' => __('Vertikální zarovnání ikony', 'cms_ve'),
								'id' => 'vertical_align',
								'type' => 'imageoption',
								'options' => [
									'top' => [
										'icon' => 'valign-top',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'valign-center',
										'text' => __('Na střed', 'cms_ve'),
									],
								],
								'show_group' => 'style',
								'show_val' => 'beside',
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_icon_text',
									'class' => 'mw_icon_text_vertical_align_',
								],
								'content' => 'center',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'size',
								'title' => __('Velikost ikony', 'cms_ve'),
								'type' => 'slider',
								'setting' => [
									'min' => '15',
									'max' => '100',
									'unit' => 'px',
								],
								'content' => '26',
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'font-size',
									'target' => ' .mw_icon_text-icon',
								],
							],
							[
								'id' => 'icon_style',
								'title' => __('Styl ikonky', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/icon1.jpg',
									'2' => VS_DIR . 'images/image_select/icon2.jpg',
									'3' => VS_DIR . 'images/image_select/icon3.jpg',
									'4' => VS_DIR . 'images/image_select/icon4.jpg',
									'5' => VS_DIR . 'images/image_select/icon5.jpg',
								],
								'onedit' => [
									'action' => 'change_class',
									'class' => 'mw_icon_style_',
									'target' => ' .mw_icon',
								],
							],
							[
								'id' => 'color',
								'title' => __('Barva ikonky', 'cms_ve'),
								'type' => 'color',
								'content' => '#219ed1',
								'onedit' => [
									'action' => 'change_style_variable',
									'css' => '--icon-color-',
									'class' => '_color',
									'target' => ' .mw_icon',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'title_font',
								'title' => __('Formátování nadpisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'use-font' => 'title',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_icon_text-title',
								],
							],
							[
								'id' => 'font',
								'title' => __('Formátování textu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'use-font' => 'text',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_icon_text-text',
								],
							],
						],
					],
				],
			],
		],

	],
	'bullets' => [
		'name' => __('Odrážky', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/764-element-odrazky',
		'description' => __('Grafické i číselné seznamy s možností zadat nadpis u každé odrážky', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'list',
				'name' => __('Odrážky', 'cms_ve'),
				'setting' => [
					[
						'id' => 'bullets',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat odrážku', 'cms_ve'),
							'empty' => __('Odrážka', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'title',
								'title' => __('Nadpis', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_bullet_item[qt] .bullet_text_title',
								],
							],
							[
								'id' => 'text',
								'title' => __('Text', 'cms_ve'),
								'type' => 'textarea',
								'content' => __('Text odrážky', 'cms_ve'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_bullet_item[qt] .bullet_text_text',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'type',
						'title' => __('Typ odrážek', 'cms_ve'),
						'type' => 'select',
						'options' => [
							['name' => 'Obrázkové odrážky', 'value' => 'image'],
							['name' => 'Číselné odrážky', 'value' => 'decimal'],
							['name' => 'Vlastní odrážky', 'value' => 'own_image'],
						],
						'content' => 'image',
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'typeset',
					],
					[
						'id' => 'style',
						'title' => __('Vzhled odrážek', 'cms_ve'),
						'type' => 'imageselect',
						'options' => [
							'2' => VS_DIR . 'images/image_select/bullet2.jpg',
							'3' => VS_DIR . 'images/image_select/bullet3.jpg',
							'1' => VS_DIR . 'images/image_select/bullet1.jpg',
							'5' => VS_DIR . 'images/image_select/bullet5.jpg',
							'4' => VS_DIR . 'images/image_select/bullet4.jpg',
						],
						'content' => '2',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'start_number',
						'title' => __('Začít od čísla', 'cms_ve'),
						'type' => 'text',
						'content' => '1',
						'show_group' => 'typeset',
						'show_val' => 'decimal',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'bullet_icon',
						'title' => __('Ikonka', 'cms_ve'),
						'type' => 'iconselect',
						'content' => [
							'icon' => 'right1',
						],
						'icons' => [
							'right1' => MW_ICONS_URL . 'content-icons.svg',
							'right2' => MW_ICONS_URL . 'content-icons.svg',
							'right3' => MW_ICONS_URL . 'content-icons.svg',
							'right4' => MW_ICONS_URL . 'content-icons.svg',
							'check1' => MW_ICONS_URL . 'content-icons.svg',
							'check2' => MW_ICONS_URL . 'content-icons.svg',
							'circle1' => MW_ICONS_URL . 'content-icons.svg',
							'plus1' => MW_ICONS_URL . 'content-icons.svg',
							'cross1' => MW_ICONS_URL . 'content-icons.svg',
							'cross2' => MW_ICONS_URL . 'content-icons.svg',
							'minus1' => MW_ICONS_URL . 'content-icons.svg',
							'star1' => MW_ICONS_URL . 'content-icons.svg',
							'heart1' => MW_ICONS_URL . 'content-icons.svg',

						],
						'onedit' => [
							'action' => 'change_icon_simple',
							'target' => ' .bullet_icon',
						],
						'show_group' => 'typeset',
						'show_val' => 'image',
					],
					[
						'id' => 'custom_image',
						'title' => __('Vlastní obrázek odrážky', 'cms_ve'),
						'type' => 'image',
						'show_group' => 'typeset',
						'show_val' => 'own_image',
						'tooltip' => __('Obrázek o maximální velikosti 80 × 80 px', 'cms_ve'),
						'onedit' => [
							'action' => 'change_img',
							'target' => ' .bullet_icon img',
						],
					],
					[
						'id' => 'size',
						'title' => __('Velikost', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '10',
							'max' => '60',
							'unit' => '',
						],
						'content' => '20',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--bullet-size-',
						],
					],
					[
						'id' => 'space',
						'title' => __('Rozestup', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '60',
							'unit' => 'px',
						],
						'content' => '15',
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'margin-bottom',
							'target' => ' .mw_element_bullets li',
						],
					],
					[
						'id' => 'bullet_color',
						'title' => __('Barva odrážek', 'cms_ve'),
						'type' => 'color',
						'content' => '#219ED1',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--bullet-color-',
							'class' => '_bullet_color',
							'target' => ' .mw_element_bullets',
						],
					],
					[
						'id' => 'title_font',
						'title' => __('Formátování nadpisu', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'use-font' => 'subtitle',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '50',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .bullet_text_title',
						],
					],
					[
						'id' => 'text_font',
						'title' => __('Formátování textů', 'cms_ve'),
						'type' => 'font',
						'setting' => [
							'max_font_size' => '30',
						],
						'content' => [
							'font-size' => '',
							'color' => '',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .bullet_text',
						],
					],
				],
			],
		],
	],

	'graphic' => [
		'name' => __('Grafika', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/801-element-grafika',
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'type',
						'title' => __('Grafický prvek', 'cms_ve'),
						'type' => 'select',
						'content' => '',
						'options' => [
							['name' => __('Vyberte typ grafiky', 'cms_ve'), 'value' => ''],
							['name' => __('Oddělovače', 'cms_ve'), 'value' => 'hr'],
							['name' => __('Garance', 'cms_ve'), 'value' => 'img'],
							['name' => __('Mockupy', 'cms_ve'), 'value' => 'mockups'],
							['name' => __('Obálky knih', 'cms_ve'), 'value' => 'books'],
						],
						'options_lite' => [
							['name' => __('Vyberte typ grafiky', 'cms_ve'), 'value' => ''],
							['name' => __('Oddělovače', 'cms_ve'), 'value' => 'hr'],
							['name' => __('Garance', 'cms_ve'), 'value' => 'img'],
							['name' => __('Obálky knih', 'cms_ve'), 'value' => 'books'],
						],
						'show' => 'graphic_type',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'graphic_hr_group',
						'type' => 'group',
						'setting' => [
							[
								'id' => 'graphic_hr',
								'title' => __('Oddělovač', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/hr1.jpg',
									'7' => VS_DIR . 'images/image_select/hr7.jpg',
									'9' => VS_DIR . 'images/image_select/hr9.jpg',
									'11' => VS_DIR . 'images/image_select/hr11.jpg',
								],
								'show' => 'hr_type',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'border',
								'title' => __('Formátování čáry', 'cms_ve'),
								'type' => 'border',
								'content' => [
									'size' => '',
									'color' => '',
									'transparency' => '0.2',
									'rgba' => '',
									'style' => 'solid',
								],
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'border-top',
									'target' => ' .graphic_element_hr',
								],
								'show_group' => 'hr_type',
								'show_val' => '1',
							],
							[
								'id' => 'hr_color',
								'title' => __('Barva', 'cms_ve'),
								'type' => 'select',
								'options' => [
									['name' => __('Automaticky', 'cms_ve'), 'value' => 'auto'],
									['name' => __('Tmavá', 'cms_ve'), 'value' => 'dark'],
									['name' => __('Světlá', 'cms_ve'), 'value' => 'light'],
								],
								'content' => 'auto',
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .graphic_element',
									'class' => 'graphic_element_hr_color_',
								],
								'show_group' => 'hr_type',
								'show_val' => '7,9,11',
							],
						],
						'show_group' => 'graphic_type',
						'show_val' => 'hr',
					],

					[
						'id' => 'graphic_img',
						'title' => __('Garance', 'cms_ve'),
						'type' => 'imageselect',
						'content' => 'guarantee2' . $image_lang,
						'options' => $guarantee,
						'show_group' => 'graphic_type',
						'show_val' => 'img',
						'onedit' => [
							'action' => 'reload',
						],
					],

					[
						'id' => 'graphic_mockups_group',
						'type' => 'group',
						'setting' => [
							[
								'id' => 'graphic_mockups',
								'title' => __('Mockup', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/mockup1.jpg',
									'2' => VS_DIR . 'images/image_select/mockup2.jpg',
									'3' => VS_DIR . 'images/image_select/mockup3.jpg',
									'4' => VS_DIR . 'images/image_select/mockup4.jpg',
									'5' => VS_DIR . 'images/image_select/mockup5.jpg',
								],
								'show' => 'mockup_style',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'mockup_image',
								'title' => __('Obrazovka mockupu', 'cms_ve'),
								'type' => 'image',
								'content' => [
									'image' => '',
									'position' => '50% 50%',
								],
								'onedit' => [
									'action' => 'change_img',
									'target' => ' .graphic_mockup_image img',
								],
							],
							[
								'content' => __('Doporučené rozlišení obrázku: 268x476px (9:16)', 'cms_ve'),
								'type' => 'info',
								'show_group' => 'mockup_style',
								'show_val' => '1',
							],
							[
								'content' => __('Doporučené rozlišení obrázku: 845x528px (16:10)', 'cms_ve'),
								'type' => 'info',
								'show_group' => 'mockup_style',
								'show_val' => '2',
							],
							[
								'content' => __('Doporučené rozlišení obrázku: 466x621px (3:4)', 'cms_ve'),
								'type' => 'info',
								'show_group' => 'mockup_style',
								'show_val' => '3,4',
							],
							[
								'content' => __('Doporučené rozlišení obrázku: 772x482px (16:10)', 'cms_ve'),
								'type' => 'info',
								'show_group' => 'mockup_style',
								'show_val' => '5',
							],
							[
								'id' => 'click_action',
								'title' => __('Akce po kliknutí', 'cms_ve'),
								'type' => 'select',
								'options' => [
									['name' => __('Žádná', 'cms_ve'), 'value' => 'none'],
									['name' => __('Otevřít odkaz', 'cms_ve'), 'value' => 'link'],
									['name' => __('Otevřít pop-up', 'cms_ve'), 'value' => 'popup'],
								],
								'content' => 'none',
								'show' => 'caction',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'title' => __('Zobrazit pop-up', 'cms_ve'),
								'id' => 'popup',
								'type' => 'popupselect',
								'show_group' => 'caction',
								'show_val' => 'popup',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'link',
								'title' => __('Odkaz (URL adresa)', 'cms_ve'),
								'type' => 'page_link',
								'onedit' => [
									'action' => 'change_link',
									'target' => ' a',
								],
								'show_group' => 'caction',
								'show_val' => 'link',
							],
						],
						'show_group' => 'graphic_type',
						'show_val' => 'mockups',
					],

					[
						'id' => 'graphic_book_group',
						'type' => 'group',
						'setting' => [
							[
								'id' => 'graphic_book',
								'title' => __('Obálka knihy', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/book1.jpg',
									'2' => VS_DIR . 'images/image_select/book2.jpg',
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'book_title',
								'title' => __('Název knihy', 'cms_ve'),
								'type' => 'text',
								'content' => __('NÁZEV KNIHY', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .graphic_element_book_title span',
								],
							],
							[
								'id' => 'book_author',
								'title' => __('Autor knihy', 'cms_ve'),
								'type' => 'text',
								'content' => __('Jméno Příjmení', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .graphic_element_book_author',
								],
							],
							[
								'id' => 'content_align',
								'title' => __('Zarovnání obsahu', 'cms_ve'),
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'align-left',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'align-center',
										'text' => __('Na střed', 'cms_ve'),
									],
								],
								'content' => 'left',
								'onedit' => [
									'action' => 'change_class',
									'class' => 've_',
									'target' => ' .graphic_element_book_content',
								],
							],
							[
								'id' => 'content_vertical_align',
								'title' => __('Zarovnání nadpisu', 'cms_ve'),
								'type' => 'imageoption',
								'options' => [
									'top' => [
										'icon' => 'valign-top',
										'text' => __('Nahoru', 'cms_ve'),
									],
									'center' => [
										'icon' => 'valign-center',
										'text' => __('Na střed', 'cms_ve'),
									],
									'bottom' => [
										'icon' => 'valign-bottom',
										'text' => __('Dolů', 'cms_ve'),
									],
								],
								'content' => 'center',
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .graphic_element_book_title',
									'class' => 've_align_title_',
								],
							],
							[
								'id' => 'title_font',
								'title' => __('Formátování názvu', 'cms_ve'),
								'type' => 'font',
								'mobile' => true,
								'group' => 'input',
								'content' => [
									'font-family' => '',
									'weight' => '',
									'line-height' => '1.1',
									'font-size' => '60',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '80',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .graphic_element_book_title',
								],
							],
							[
								'id' => 'author_font',
								'title' => __('Formátování autora', 'cms_ve'),
								'type' => 'font',
								'mobile' => true,
								'group' => 'input',
								'content' => [
									'font-family' => '',
									'weight' => '',
									'font-size' => '17',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '25',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .graphic_element_book_author',
								],
							],
							[
								'id' => 'book_background_color',
								'title' => __('Barva pozadí', 'cms_ve'),
								'type' => 'color',
								'content' => '#6599a3',
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'background-color',
									'target' => ' .graphic_element_book_cover',
								],
							],
							[
								'id' => 'book_background_image',
								'type' => 'bgimage',
								'hide' => ['efect', 'cover'],
								'content' => [
									'cover' => 1,
									'image' => MW_IMAGE_LIBRARY . 'bg/coast.jpeg',
									'position' => '10% 50%',
									'overlay_color' => [
										'color' => '#000000',
										'transparency' => '0.2',
										'rgba' => 'rgba(0, 0, 0, 0.2)',
									],
								],
								'onedit' => [
									'action' => 'change_background',
									'target' => ' .graphic_element_book_cover',
								],
							],

						],
						'show_group' => 'graphic_type',
						'show_val' => 'books',
					],
				],
			],
		],
	],

	'seform' => [
		'name' => __('Formulář', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/802-element-formular',
		'tab_setting' => [
			[
				'id' => 'form',
				'name' => __('Formulář', 'cms_ve'),
				'setting' => [
					[
						'id' => 'type',
						'title' => __('Použít', 'cms_ve'),
						'type' => 'select',
						'content' => 'smartemailing',
						'options' => [
							['name' => __('E-mail marketingový nástroj', 'cms_ve'), 'value' => 'smartemailing'],
							['name' => __('Vlastní formulář na e-mail', 'cms_ve'), 'value' => 'custom'],
							['name' => __('Vlastní formulář na URL', 'cms_ve'), 'value' => 'custom_url'],
						],
						'show' => 'seform',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'content',
						'title' => __('Zvolte formulář', 'cms_ve'),
						'type' => 'form_select',
						'tooltip' => __('Formulář se vygeneruje podle vybraného formuláře, který jste si vytvořili ve svém e-mail marketingovém nástroji. Vyplněný formulář odešle data do vámi používaného nástroje, kde se uloží a poté uživatele přesměruje na zadanou děkovací stránku.', 'cms_ve'),
						'tooltip_align' => 'bottom',
						'api' => 'se',
						'show_group' => 'seform',
						'show_val' => 'smartemailing',
						'onedit' => [
							'action' => 'reload',
						],
					],

					[
						'id' => 'email',
						'title' => __('Odeslat na e-mail', 'cms_ve'),
						'type' => 'text',
						'show_group' => 'seform',
						'show_val' => 'custom',
					],
					[
						'id' => 'subject',
						'title' => __('Předmět e-mailu', 'cms_ve'),
						'type' => 'text',
						'show_group' => 'seform',
						'show_val' => 'custom',
					],
					[
						'id' => 'button_text',
						'title' => __('Text tlačítka', 'cms_ve'),
						'type' => 'text',
						'onedit' => [
							'action' => 'change_default_text',
							'setting' => __('Odeslat', 'cms_ve'),
							'target' => '_form .ve_form_button_row .ve_but_text',
						],
					],
					[
						'id' => 'thx_url',
						'title' => __('Děkovací stránka', 'cms_ve'),
						'type' => 'page_link',
						'target' => false,
						'tooltip' => __('URL stránky, na kterou bude uživatel přesměrován po odeslání formuláře.', 'cms_ve'),
						'show_group' => 'seform',
						'show_val' => 'custom',
					],
					[
						'id' => 'url',
						'title' => __('Odeslat na URL.', 'cms_ve'),
						'type' => 'text',
						'show_group' => 'seform',
						'show_val' => 'custom_url',
					],

					[
						'id' => 'custom_form',
						'type' => 'multielement',
						'title' => __('Pole formuláře', 'cms_ve'),
						'show_group' => 'seform',
						'show_val' => 'custom,custom_url',
						'texts' => [
							'add' => __('Přidat pole', 'cms_ve'),
							'empty' => __('Pole', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'title',
								'title' => __('Popisek pole', 'cms_ve'),
								'type' => 'text',
								'content' => __('Název pole', 'cms_ve'),
								'onedit' => [
									'action' => 'change_form_field_text',
									'target' => ' .ve_form_row[qt]',
								],
							],
							[
								'id' => 'required',
								'label' => __('Povinné pole', 'cms_ve'),
								'type' => 'switch',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'email',
								'label' => __('Emailová adresa', 'cms_ve'),
								'type' => 'switch',
								'show_group' => 'field_type',
								'show_val' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'type',
								'title' => __('Typ pole', 'cms_ve'),
								'type' => 'select',
								'content' => 'text',
								'options' => [
									['name' => __('Jednořádkové textové pole (text)', 'cms_ve'), 'value' => 'text'],
									['name' => __('Víceřádkové textové pole (textarea)', 'cms_ve'), 'value' => 'textarea'],
									['name' => __('Výběr jedné možnosti z přednastavených hodnot v roletce (select)', 'cms_ve'), 'value' => 'select'],
									['name' => __('Výběr více možností v seznamu zatrhávacích polí (seznam zaškrtavátek)', 'cms_ve'), 'value' => 'checkbox'],
									['name' => __('Výběr jedné položky z přednastavených hodnot v seznamu přepínačů (radio)', 'cms_ve'), 'value' => 'radio'],
									['name' => __('Heslo (password)', 'cms_ve'), 'value' => 'password'],
									['name' => __('Souhlas (zaškrtávátko)', 'cms_ve'), 'value' => 'agree'],
								],
								'show' => 'field_type',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'subitems',
								'title' => __('Možnosti výběru', 'cms_ve'),
								'type' => 'simple_feature',
								'text_add' => __('Přidat možnost', 'cms_ve'),
								//'sortable' => true,
								'fields' => [
									'text' => [
										'title' => __('Možnost', 'cms_ve'),
									],
								],
								'show_group' => 'field_type',
								'show_val' => 'select,checkbox,radio',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'agree_link_text',
								'title' => __('Text odkazu', 'cms_ve'),
								'type' => 'text',
								'show_group' => 'field_type',
								'show_val' => 'agree',
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_form_row[qt] a',
								],
							],
							[
								'id' => 'agree_link',
								'title' => __('Odkaz tlačítka', 'cms_ve'),
								'type' => 'page_link',
								'target' => false,
								'content' => [
									'target' => '1',
								],
								'onedit' => [
									'action' => 'change_link',
									'target' => ' .ve_form_row[qt] a',
								],
								'show_group' => 'field_type',
								'show_val' => 'agree',
							],


						],
					],

					/*
						array(
							'id'=>'custom_form_url',
							'title'=>__('Pole vlastního formuláře','cms_ve'),
							'type'=>'customform',
							'setting'=>array(
								'type'=>'url'
							),
							'tooltip'=> __('Zde si můžete vytvořit vlastní formulář, jehož obsah se bude odesílat na zadanou URL adresu. Tato možnost je určena pro pokročilé uživatele, kteří chtějí data formuláře odeslat na svůj vlastní skript.','cms_ve'),
							'show_group' => 'seform',
							'show_val' => 'custom_url',
						),*/
				],
			],
			[
				'id' => 'look',
				'class' => 'form_look_setting',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'form-style',
								'title' => __('Zarovnání formulářových polí', 'cms_ve'),
								'type' => 'select',
								'content' => '1',
								'options' => [
									['name' => __('Pod sebou', 'cms_ve'), 'value' => '1'],
									['name' => __('Vedle sebe', 'cms_ve'), 'value' => '2'],
								],
								'show' => 'form-style',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'form-labels',
								'title' => __('Zobrazení popisku polí', 'cms_ve'),
								'type' => 'select',
								'content' => '1',
								'options' => [
									['name' => __('Uvnitř polí', 'cms_ve'), 'value' => '1'],
									['name' => __('Nad poli', 'cms_ve'), 'value' => '2'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'form-style',
								'show_val' => '1',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'form-look',
								'title' => __('Vzhled formulářových polí', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/forminput1.png',
									'2' => VS_DIR . 'images/image_select/forminput2.png',
									'3' => VS_DIR . 'images/image_select/forminput3.png',
									'4' => VS_DIR . 'images/image_select/forminput4.png',
									'5' => VS_DIR . 'images/image_select/forminput5.png',
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => '_form',
									'class' => 've_form_input_style_',
								],
							],
							[
								'id' => 'background',
								'title' => __('Barva formulářových polí', 'cms_ve'),
								'type' => 'color',
								'group' => 'input',
								'content' => '#eeeeee',
								'onedit' => [
									'action' => 'change_smart_background_color',
									'target' => '_form .ve_form_field',
								],
							],
							[
								'name' => __('Zakulacení rohů', 'cms_ve'),
								'id' => 'corners',
								'type' => 'imageoption',
								'options' => [
									'sharp' => [
										'icon' => 'sharp_corner',
										'text' => __('Ostré', 'cms_ve'),
									],
									'rounded' => [
										'icon' => 'rounded_corner',
										'text' => __('Zakulacené', 'cms_ve'),
									],
									'round' => [
										'icon' => 'round_corner',
										'text' => __('Kulaté', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'imageoption',
									'class' => 've_form_corners_',
									'target' => '_form',
								],
								'content' => 'sharp',
							],
							[
								'id' => 'form-font',
								'title' => __('Písmo formulářových polí', 'cms_ve'),
								'type' => 'font',
								'group' => 'input',
								'content' => [
									'font-size' => '',
									//'color'=>'',
								],
								'setting' => [
									'max_font_size' => '25',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => '_form .ve_form_field',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'button',
								'title' => __('Styl tlačítka', 'cms_ve'),
								'type' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => '_form .ve_form_button_row .ve_form_button',
								],
							],
						],
					],
				],
			],

			[
				'id' => 'popup',
				'name' => __('Pop-up formulář', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'popup',
								'title' => '',
								'label' => __('Otevírat v popupu', 'cms_ve'),
								'type' => 'switch',
								'show' => 'popupset',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'popup_type',
								'title' => __('Otevírat pomocí', 'cms_ve'),
								'type' => 'select',
								'show' => 'popup_type',
								'options' => [
									['name' => __('Tlačítko', 'cms_ve'), 'value' => 'button'],
									['name' => __('Obrázek', 'cms_ve'), 'value' => 'image'],
									['name' => __('Odkaz', 'cms_ve'), 'value' => 'link'],
								],
								'content' => 'button',
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'popupset',
								'show_val' => '1',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [

							[
								'id' => 'image',
								'title' => __('Obrázek', 'cms_ve'),
								'type' => 'image',
								'show_group' => 'popup_type',
								'show_val' => 'image',
								'onedit' => [
									'action' => 'change_img',
									'target' => ' .in_element_content img',
								],
							],
							[
								'id' => 'popup_text',
								'title' => __('Text tlačítka', 'cms_ve'),
								'type' => 'text',
								'content' => __('Registrovat se', 'cms_ve'),
								'show_group' => 'popup_type',
								'show_val' => 'button',
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_pupup_button_container .ve_but_text',
								],
							],
							[
								'id' => 'popupbutton',
								'title' => __('Styl tlačítka', 'cms_ve'),
								'type' => 'button',
								'show_group' => 'popup_type',
								'show_val' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_pupup_button_container .ve_content_button',
								],
							],
							[
								'id' => 'link_text',
								'title' => __('Text odkazu, který otevírá formulář', 'cms_ve'),
								'type' => 'text',
								'content' => __('Registrovat se', 'cms_ve'),
								'show_group' => 'popup_type',
								'show_val' => 'link',
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .open_element_lightbox',
								],
							],
							[
								'id' => 'link_font',
								'title' => __('Font odkazu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '50',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .open_element_lightbox',
								],
								'show_group' => 'popup_type',
								'show_val' => 'link',
							],

							[
								'name' => __('Zarovnání', 'cms_ve'),
								'id' => 'align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'align-left',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'align-center',
										'text' => __('Na střed', 'cms_ve'),
									],
									'right' => [
										'icon' => 'align-right',
										'text' => __('Napravo', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'class' => 've_',
									'target' => ' .ve_pupup_button_container',
								],
								'content' => 'center',
							],

						],
						'show_group' => 'popupset',
						'show_val' => '1',
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'popup_title',
								'title' => __('Nadpis popupu', 'cms_ve'),
								'type' => 'text',
								'content' => __('Zadejte svůj e-mail a registrujte se.', 'cms_ve'),
								'onedit' => [
									'action' => 'add_text',
									'target' => '_popup .popup_form_title',
								],
							],
							[
								'id' => 'textinpopup',
								'title' => __('Text v popupu', 'cms_ve'),
								'type' => 'textarea',
								'onedit' => [
									'action' => 'add_text',
									'target' => '_popup .popup_form_text',
								],
							],
						],
						'show_group' => 'popupset',
						'show_val' => '1',
					],

				],
			],
		],
	],
	'contactform' => [
		'name' => __('Kontaktní formulář', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/804-element-kontaktni-formular',
		'tab_setting' => [
			[
				'id' => 'form',
				'name' => __('Formulář', 'cms_ve'),
				'setting' => [
					[
						'id' => 'email',
						'title' => __('E-mailová adresa', 'cms_ve'),
						'type' => 'text',
						'content' => '@',
						'desc' => __('Zadejte e-mailové adresy, na které se bude formulář odesílat. E-mailové adresy oddělte čárkou.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'button_text',
						'title' => __('Text tlačítka', 'cms_ve'),
						'type' => 'text',
						'content' => __('Odeslat dotaz', 'cms_ve'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .ve_but_text',
						],
					],
					[
						'id' => 'thx_url',
						'title' => __('Děkovací stránka', 'cms_ve'),
						'type' => 'page_link',
						'target' => false,
						'tooltip' => __('URL stránky, na kterou bude uživatel přesměrován po odeslání formuláře.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'hide',
						'type' => 'multiple_checkbox',
						'options' => [
							['name' => __('Skrýt pole telefon', 'cms_ve'), 'value' => 'phone'],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'form-appearance',
						'title' => __('Vzhled formuláře', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'1' => VS_DIR . 'images/image_select/contactform1.png',
							'2' => VS_DIR . 'images/image_select/contactform2.png',
							'3' => VS_DIR . 'images/image_select/contactform3.png',
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .ve_content_form',
							'class' => 've_contact_form_',
						],
					],
					[
						'id' => 'form-style',
						'title' => __('Vzhled formulářových polí', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'1' => VS_DIR . 'images/image_select/forminput1.png',
							'2' => VS_DIR . 'images/image_select/forminput2.png',
							'3' => VS_DIR . 'images/image_select/forminput3.png',
							'4' => VS_DIR . 'images/image_select/forminput4.png',
							'5' => VS_DIR . 'images/image_select/forminput5.png',
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .ve_content_form',
							'class' => 've_form_input_style_',
						],
					],
					[
						'id' => 'background',
						'title' => __('Barva formulářových polí', 'cms_ve'),
						'type' => 'color',
						'group' => 'input',
						'content' => '#eeeeee',
						'onedit' => [
							'action' => 'change_smart_background_color',
							'target' => ' .ve_form_field',
						],
					],
					[
						'name' => __('Zakulacení rohů', 'cms_ve'),
						'id' => 'corners',
						'type' => 'imageoption',
						'options' => [
							'sharp' => [
								'icon' => 'sharp_corner',
								'text' => __('Ostré', 'cms_ve'),
							],
							'rounded' => [
								'icon' => 'rounded_corner',
								'text' => __('Zakulacené', 'cms_ve'),
							],
							'round' => [
								'icon' => 'round_corner',
								'text' => __('Kulaté', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'imageoption',
							'class' => 've_form_corners_',
							'target' => ' .ve_content_form',
						],
						'content' => 'sharp',
					],
					[
						'id' => 'button',
						'title' => __('Styl tlačítka', 'cms_ve'),
						'type' => 'button',
						'onedit' => [
							'action' => 'change_button',
							'target' => ' .ve_content_button',
						],
					],
					[
						'id' => 'form-font',
						'title' => __('Písmo formulářových polí', 'cms_ve'),
						'type' => 'font',
						'group' => 'input',
						'content' => [
							'font-size' => '15',
							//'color'=>'',
						],
						'setting' => [
							'max_font_size' => '25',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_form_field',
						],
					],
				],
			],

		],
	],

	'features' => [
		'name' => __('Vlastnosti', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/808-element-vlastnosti',
		'tab_setting' => [
			[
				'id' => 'features',
				'name' => __('Vlastnosti', 'cms_ve'),
				'setting' => [
					[
						'id' => 'features',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat vlastnost', 'cms_ve'),
							'empty' => __('Vlastnost', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'icon',
								'title' => __('Ikona', 'cms_ve'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'flight',
									'image' => '',
								],
								'onedit' => [
									'action' => 'change_icon',
									'target' => ' .mw_feature[qt] .mw_icon i',
									'setting' => ' .mw_feature[qt] .mw_feature_icon img',
								],
							],
							[
								'id' => 'title',
								'title' => __('Nadpis', 'cms_ve'),
								'content' => __('Vlastnost', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_feature[qt] .feature_text h3',
								],
							],
							[
								'id' => 'text',
								'title' => __('Text', 'cms_ve'),
								'type' => 'textarea',
								'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_feature[qt] .feature_text .mw_feature_description',
								],
							],
							[
								'id' => 'button_text',
								'title' => __('Text tlačítka (pokud je zobrazeno)', 'cms_ve'),
								'type' => 'text',
								'content' => __('Více informací', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .mw_feature[qt] .feature_text .ve_but_text',
								],
							],
							[
								'id' => 'link',
								'title' => __('Odkaz', 'cms_ve'),
								'type' => 'page_link',
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'cols',
								'title' => __('Počet sloupců', 'cms_ve'),
								'type' => 'select',
								'content' => 'auto',
								'options' => [
									['name' => __('Automaticky', 'cms_ve'), 'value' => 'auto'],
									['name' => '1', 'value' => 'one'],
									['name' => '2', 'value' => 'two'],
									['name' => '3', 'value' => 'three'],
									['name' => '4', 'value' => 'four'],
									['name' => '5', 'value' => 'five'],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'cols_type',
								'title' => __('Mezery mezi sloupci', 'cms_ve'),
								'type' => 'select',
								'content' => '',
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .in_element_content',
									'class' => '',
								],
								'options' => [
									['name' => __('Velké', 'cms_ve'), 'value' => 'cols'],
									['name' => __('Malé', 'cms_ve'), 'value' => 'smallcols'],
									['name' => __('Žádné', 'cms_ve'), 'value' => 'fullcols'],
								],
								'show_group' => 'feature_style',
								'show_val' => '4',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'show' => 'feature_style',
								'options' => [
									'1' => VS_DIR . 'images/image_select/feature1.jpg',
									'2' => VS_DIR . 'images/image_select/feature2.jpg',
									'5' => VS_DIR . 'images/image_select/feature5.jpg',
									'3' => VS_DIR . 'images/image_select/feature3.jpg',
									'4' => VS_DIR . 'images/image_select/feature4.jpg',
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'background_set',
								'title' => __('Nastavení pozadí', 'cms_ve'),
								'type' => 'background_set',
								'content' => [
									'corner' => '',
									'border' => '',
									'shadow' => '1',
									'color' => '#ffffff',
								],
								'onedit' => [
									'action' => 'change_background_set',
									'target' => ' .mw_feature',
								],
								'show_group' => 'feature_style',
								'show_val' => '4',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'icon_size',
								'title' => __('Velikost ikon', 'cms_ve'),
								'type' => 'slider',
								'setting' => [
									'min' => '10',
									'max' => '120',
									'unit' => 'px',
								],
								'content' => '30',
								'onedit' => [
									'action' => 'change_style_variable',
									'css' => '--icon-size-',
									'target' => ' .mw_icon',
								],
							],
							[
								'id' => 'background-color',
								'title' => __('Barva ikony', 'cms_ve'),
								'type' => 'color',
								'content' => '#209ccf',
								'onedit' => [
									'action' => 'change_style_variable',
									'css' => '--icon-color-',
									'class' => '_color',
									'target' => ' .mw_icon',
								],
							],
							[
								'id' => 'icon_style',
								'title' => __('Styl ikonky', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/icon1.jpg',
									'2' => VS_DIR . 'images/image_select/icon2.jpg',
									'3' => VS_DIR . 'images/image_select/icon3.jpg',
									'4' => VS_DIR . 'images/image_select/icon4.jpg',
									'5' => VS_DIR . 'images/image_select/icon5.jpg',
								],
								'onedit' => [
									'action' => 'change_class',
									'class' => 'mw_icon_style_',
									'target' => ' .mw_icon',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'show_button',
								'label' => __('Zobrazit tlačítko', 'cms_ve'),
								'type' => 'switch',
								'show' => 'feature_button',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'button',
								'title' => __('Styl tlačítka', 'cms_ve'),
								'type' => 'button',
								'show_group' => 'feature_button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_button',
								],
								'content' => [
									'style' => 'basic',
									'button_size' => 'small',
								],
								'show_val' => '1',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'font',
								'title' => __('Formátování nadpisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '18',
									'use-font' => 'title',
									//'font-family'=>'',
									//'weight'=>'',
									//'line-height'=>'',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '35',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
								],
							],
							[
								'id' => 'font_text',
								'title' => __('Formátování popisku', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'use-font' => 'text',
									//'font-family'=>'',
									//'weight'=>'',
									//'line-height'=>'',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_feature_description',
								],
							],
						],
					],

				],
			],
		],
	],
	'catalog' => [
		'name' => __('Katalog', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/818-element-katalog',
		'exclude' => ['slide'],
		'tab_setting' => [
			[
				'id' => 'items',
				'name' => __('Položky katalogu', 'cms_ve'),
				'setting' => [
					[
						'title' => __('Typy položek', 'cms_ve'),
						'id' => 'item_type',
						'type' => 'select',
						'show' => 'type',
						'options' => [
							['name' => __('Vlastní položky', 'cms_ve'), 'value' => 'own'],
							['name' => __('Položky jako podstránky', 'cms_ve'), 'value' => 'subpage'],
						],
						'onedit' => [
							'action' => 'reload',
						],
						'content' => 'own',
					],

					[
						'id' => 'items',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat položku', 'cms_ve'),
							'empty' => __('Položka', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'title',
								'title' => __('Název', 'cms_ve'),
								'type' => 'text',
								'content' => __('Název', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .mw_element_item[qt] h3',
								],
							],
							[
								'id' => 'image',
								'title' => __('Obrázek', 'cms_ve'),
								'type' => 'image',
								'content' => [
									'image' => '',
									'position' => '50% 50%',
								],
								'onedit' => [
									'action' => 'change_img',
									'target' => ' .mw_element_item[qt] img',
								],
							],
							[
								'id' => 'subtitle',
								'title' => __('Podnázev', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_element_item[qt] .mw_element_item_subtitle',
								],
							],
							[
								'id' => 'description',
								'title' => __('Popisek', 'cms_ve'),
								'type' => 'textarea',
								'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_element_item[qt] .mw_element_item_description',
								],
							],
							[
								'id' => 'price',
								'title' => __('Cena', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_element_item[qt] .mw_element_item_price',
								],
							],
							[
								'id' => 'button_text',
								'title' => __('Text tlačítka (pokud je zobrazeno)', 'cms_ve'),
								'type' => 'text',
								'content' => __('Více informací', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .mw_element_item[qt] .ve_content_button',
								],
							],
							[
								'id' => 'link',
								'title' => __('Odkaz', 'cms_ve'),
								'type' => 'page_link',
								'onedit' => [
									'action' => 'reload',
								],
							],

						],
						'show_group' => 'type',
						'show_val' => 'own',
					],
					[
						'type' => 'info',
						'show_group' => 'type',
						'show_val' => 'subpage',
						'content' => __('Náhledový obrázek a název položky se načte z nastavení stránky.', 'cms_ve'),
					],
					[
						'id' => 'page',
						'title' => __('Vypsat podstránky', 'cms_ve'),
						'type' => 'selectpage',
						'whisperer' => true,
						'show_group' => 'type',
						'show_val' => 'subpage',
						'desc' => __('Pokud nezvolíte žádnou stránku, vypíšou se podstránky této stránky.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'button_text',
						'title' => __('Text tlačítka (pokud je zobrazeno)', 'cms_ve'),
						'type' => 'text',
						'show_group' => 'type',
						'show_val' => 'subpage',
						'content' => __('Více informací', 'cms_ve'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .ve_content_button',
						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'cols',
								'title' => __('Počet sloupců', 'cms_ve'),
								'type' => 'select',
								'content' => 0,
								'options' => [
									['name' => __('Automaticky', 'cms_ve'), 'value' => 0],
									['name' => '1', 'value' => 1],
									['name' => '2', 'value' => 2],
									['name' => '3', 'value' => 3],
									['name' => '4', 'value' => 4],
									['name' => '5', 'value' => 5],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'cols_type',
								'title' => __('Mezery mezi sloupci', 'cms_ve'),
								'type' => 'select',
								'content' => '',
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_element_items',
									'class' => '',
								],
								'options' => [
									['name' => __('Velké', 'cms_ve'), 'value' => 'cols'],
									['name' => __('Malé', 'cms_ve'), 'value' => 'smallcols'],
									['name' => __('Žádné', 'cms_ve'), 'value' => 'fullcols'],
								],
								'show_group' => 'style',
								'show_val' => '1,2,4,4b,5,7,7b,8',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '3',
								'options' => [
									'3' => VS_DIR . 'images/image_select/item3.jpg',
									'4' => VS_DIR . 'images/image_select/item4.jpg',
									'4b' => VS_DIR . 'images/image_select/item4b.jpg',
									'6' => VS_DIR . 'images/image_select/item6.jpg',
									'7' => VS_DIR . 'images/image_select/item7.jpg',
									'7b' => VS_DIR . 'images/image_select/item7b.jpg',
									'2' => VS_DIR . 'images/image_select/item2.jpg',
									'5' => VS_DIR . 'images/image_select/item5.jpg',
									'1' => VS_DIR . 'images/image_select/item1.jpg',
									'8' => VS_DIR . 'images/image_select/item8.jpg',
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'style',
							],
							[
								'id' => 'background_set',
								'title' => __('Nastavení pozadí', 'cms_ve'),
								'type' => 'background_set',
								'content' => [
									'corner' => '',
									'border' => '',
									'shadow' => '1',
									'color' => '#ffffff',
								],
								'onedit' => [
									'action' => 'change_background_set',
									'target' => ' .mw_element_item',
								],
								'show_group' => 'style',
								'show_val' => '7,4,4b,7b',
							],
							[
								'label' => __('Skrýt obrázek', 'cms_ve'),
								'id' => 'hide_img',
								'type' => 'switch',
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'style',
								'show_val' => '3,4,4b,6,7,7b',
								'show' => 'show_image',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'show_image',
						'show_val' => '0',
						'setting' => [
							[
								'id' => 'image_size',
								'title' => __('Velikost obrázku', 'cms_ve'),
								'type' => 'select',
								'content' => '2',
								'options' => [
									['name' => '1/2', 'value' => '2'],
									['name' => '1/3', 'value' => '3'],
									['name' => '1/4', 'value' => '4'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'style',
								'show_val' => '6,7,7b',
							],
							[
								'label' => __('Zobrazit obsah po najetí', 'cms_ve'),
								'id' => 'hide_content',
								'type' => 'switch',
								'onedit' => [
									'action' => 'toggle_class',
									'target' => ' .in_element_content',
									'class' => 'mw_element_item_hover_content',
								],
								'show_group' => 'style',
								'show_val' => '1',
							],
							[
								'id' => 'hover_color',
								'title' => __('Barva podbarvení po najetí myši', 'cms_ve'),
								'type' => 'transparent_color',
								'content' => [
									'color' => '#000000',
									'transparency' => '0.5',
									'rgba' => 'rgba(0,0,0,0.5)',
								],
								'onedit' => [
									'action' => 'change_css',
									'css' => 'background',
									'target' => ' .mw_element_item_image_hover',
								],
								'show_group' => 'style',
								'show_val' => '1',
							],
							[
								'id' => 'hover',
								'title' => __('Efekt po najetí myši', 'cms_ve'),
								'type' => 'select',
								'content' => 'zoom',
								'options' => [
									['name' => __('Žádný', 'cms_ve'), 'value' => ''],
									['name' => __('Zoom', 'cms_ve'), 'value' => 'zoom'],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'image_ratio',
								'title' => __('Zobrazit obrázky v poměru:', 'cms_ve'),
								'type' => 'select',
								'content' => '32',
								'options' => [
									['name' => __('Původní', 'cms_ve'), 'value' => 'original'],
									['name' => __('Široký (16:9)', 'cms_ve'), 'value' => '169'],
									['name' => __('Základní (3:2)', 'cms_ve'), 'value' => '32'],
									['name' => __('Střední (4:3)', 'cms_ve'), 'value' => '43'],
									['name' => __('Čtverec (1:1)', 'cms_ve'), 'value' => '11'],
									['name' => __('Základní na výšku (2:3)', 'cms_ve'), 'value' => '23'],
									['name' => __('Střední na výšku (3:4)', 'cms_ve'), 'value' => '34'],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_image_ratio',
									'class' => 'mw_image_ratio_',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'style',
						'show_val' => '3,4,6,7,4b,7b',
						'setting' => [
							[
								'id' => 'show_button',
								'label' => __('Zobrazit tlačítko', 'cms_ve'),
								'type' => 'switch',
								'show' => 'feature_button',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'button',
								'title' => __('Styl tlačítka', 'cms_ve'),
								'type' => 'button',
								'show_group' => 'feature_button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_button',
								],
								'content' => [
									'style' => 'basic',
									'button_size' => 'small',
								],
								'show_val' => '1',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'style',
						'show_val' => '1,2,3,4,4b,5,6,7,7b',
						'setting' => [
							[
								'name' => __('Zarovnání textů', 'cms_ve'),
								'id' => 'text_align',
								'type' => 'imageoption',
								'options' => [
									'left' => [
										'icon' => 'align-left',
										'text' => __('Nalevo', 'cms_ve'),
									],
									'center' => [
										'icon' => 'align-center',
										'text' => __('Na střed', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_element_item_content',
									'class' => 've_',
								],
								'show_group' => 'style',
								'show_val' => '2,3,4,5,6,7,4b,7b',
								'content' => 'left',
							],
							/*
							  array(
								  'id' => 'font_color',
								  'title' => __('Barva textů', 'cms_ve'),
								  'type' => 'color',
								  'onedit'=>array(
									  'action'=>'change_styles',
									  'target'=>' .mw_element_item',
									  'css'=>'color',
								  ),
								  'show_group' => 'style',
								  'show_val' => '3,6',
							  ),*/
							[
								'id' => 'font_title',
								'title' => __('Formátování názvu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'title',
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '50',
									'font_size_placeholder' => '20',
									'show_group' => 'style',
									'show_color' => '3,4,6,7,4b,7b',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
								],
							],
							[
								'id' => 'font_subtitle',
								'title' => __('Formátování podnadpisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
									'font_size_placeholder' => '13',
									'show_group' => 'style',
									'show_color' => '3,4,6,7,4b,7b',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_element_item_subtitle',
								],
							],
							[
								'id' => 'font_description',
								'title' => __('Formátování popisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_element_item_description',
								],
								'show_group' => 'style',
								'show_val' => '3,4,6,7,4b,7b',
							],
							[
								'id' => 'font_price',
								'title' => __('Formátování ceny', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'subtitle',
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
									'font_size_placeholder' => '16',
									'show_group' => 'style',
									'show_color' => '3,4,6,7,4b,7b',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_element_item_price',
								],
								'show_group' => 'style',
								'show_val' => '1,3,7,4,6,4b,7b',
							],
						],
					],
				],
			],
			[
				'id' => 'slider',
				'name' => __('Slider', 'cms_ve'),
				'setting' => [
					[
						'id' => 'use_slider',
						'title' => '',
						'type' => 'switch',
						'label' => __('Zobrazit jako slider', 'cms_ve'),
						'show' => 'sliderset',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'miocarousel_setting',
						'type' => 'miocarousel',
						'content' => [
							'animation' => 'fade',
							'delay' => '3500',
							'speed' => '1000',
							'color_scheme' => '',
						],
						'onedit' => [
							'action' => 'change_slider',
							'target' => ' .miocarousel',
						],
						'show_group' => 'sliderset',
						'show_val' => '1',
					],
				],
			],
		],
	],
	'testimonials' => [
		'name' => __('Reference', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/809-element-reference',
		'description' => __('Výpis textových referencí, které lze formátovat do více sloupců.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'testimonials',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'testimonials',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat referenci', 'cms_ve'),
							'empty' => __('Reference', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'text',
								'title' => __('Text reference', 'cms_ve'),
								'type' => 'textarea',
								'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec egestas urna et ex molestie viverra. Etiam sollicitudin massa nulla, a malesuada nulla vulputate id.',
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_content_testimonial_text[qt]',
								],
							],
							[
								'id' => 'name',
								'title' => __('Jméno', 'cms_ve'),
								'type' => 'text',
								'content' => __('Jméno Příjmení', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_content_testimonial_author_name[qt]',
								],
							],
							[
								'id' => 'company',
								'title' => __('Firma/Pozice', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .ve_content_testimonial_company[qt]',
								],
							],

							[
								'id' => 'image',
								'title' => __('Fotografie', 'cms_ve'),
								'type' => 'image',
								'content' => [
									'image' => '',
									'position' => '50% 50%',
								],
								'onedit' => [
									'action' => 'change_img',
									'target' => ' .ve_content_testimonial_item[qt] img',
								],
							],

						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'cols',
								'title' => __('Počet sloupců', 'cms_ve'),
								'type' => 'select',
								'content' => 'one',
								'options' => [
									['name' => '1', 'value' => 'one'],
									['name' => '2', 'value' => 'two'],
									['name' => '3', 'value' => 'three'],
									['name' => '4', 'value' => 'four'],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Styl referencí', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '7',
								'show' => 'style',
								'options' => [
									'7' => VS_DIR . 'images/image_select/testimonial7.jpg',
									'8' => VS_DIR . 'images/image_select/testimonial8.jpg',
									'9' => VS_DIR . 'images/image_select/testimonial9.jpg',

									'6' => VS_DIR . 'images/image_select/testimonial6.jpg',
									'10' => VS_DIR . 'images/image_select/testimonial10.jpg',
									'3' => VS_DIR . 'images/image_select/testimonial3.jpg',

									'1' => VS_DIR . 'images/image_select/testimonial1.jpg',
									'11' => VS_DIR . 'images/image_select/testimonial11.jpg',
									'2' => VS_DIR . 'images/image_select/testimonial2.jpg',

									'5' => VS_DIR . 'images/image_select/testimonial5.jpg',
									'4' => VS_DIR . 'images/image_select/testimonial4.jpg',
									'12' => VS_DIR . 'images/image_select/testimonial12.jpg',
									'13' => VS_DIR . 'images/image_select/testimonial13.jpg',
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'background_set',
								'title' => __('Nastavení pozadí', 'cms_ve'),
								'type' => 'background_set',
								'content' => [
									'corner' => '',
									'border' => '',
									'shadow' => '1',
									//'color'=>'#ffffff',
								],
								'onedit' => [
									'action' => 'change_background_set',
									'target' => ' .ve_content_testimonial_box',
								],
								'show_group' => 'style',
								'show_val' => '8,9,10,3,11,2,4,12,13',
							],
							[
								'name' => __('Velikost obrázku', 'cms_ve'),
								'id' => 'image_size',
								'type' => 'imageoption',
								'options' => [
									'1' => [
										'icon' => 'ti-small',
										'text' => __('Malé', 'cms_ve'),
									],
									'2' => [
										'icon' => 'ti-medium',
										'text' => __('Střední', 'cms_ve'),
									],
									'3' => [
										'icon' => 'ti-big',
										'text' => __('Velké', 'cms_ve'),
									],
									'4' => [
										'icon' => 'ti-bigest',
										'text' => __('Největší', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'change_class',
									'class' => 've_content_testimonial_img',
									'target' => ' .ve_content_testimonial',
								],
								'content' => '',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'font',
								'title' => __('Font reference', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '15',
									//'font-family'=>'',
									//'line-height'=>'',
									//'weight'=>'',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .ve_content_testimonial_text',
								],
							],
							[
								'id' => 'font-author',
								'title' => __('Font autora', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									//'font-family'=>'',
									//'weight'=>'',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .ve_content_testimonial_name',
								],
							],
						],
					],

				],
			],

			[
				'id' => 'slider',
				'name' => __('Slider', 'cms_ve'),
				'setting' => [
					[
						'id' => 'use_slider',
						'title' => '',
						'type' => 'switch',
						'label' => __('Zobrazit jako slider', 'cms_ve'),
						'show' => 'sliderset',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'miocarousel_setting',
						'type' => 'miocarousel',
						'content' => [
							'animation' => 'fade',
							'delay' => '3500',
							'speed' => '1000',
							'color_scheme' => '',
						],
						'onedit' => [
							'action' => 'change_slider',
							'target' => ' .miocarousel',
						],
						'show_group' => 'sliderset',
						'show_val' => '1',
					],

				],
			],
		],
	],
	'peoples' => [
		'name' => __('Autor/Lidé', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/812-element-autor-lide',
		'description' => __('Pro výpis informací jedné nebo více osob. Výpis lze formátovat do více sloupců. Tento element je vhodný například jako info o autorovi knihy nebo jako přehled lidí ve firmě.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'peoples',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'peoples',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat osobu', 'cms_ve'),
							'empty' => __('Osoba', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'title',
								'title' => __('Jméno', 'cms_ve'),
								'type' => 'text',
								'content' => __('Jméno Příjmení', 'cms_ve'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_element_item[qt] h3',
								],
							],
							[
								'id' => 'position',
								'title' => __('Pozice', 'cms_ve'),
								'content' => __('Pozice', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_element_item[qt] .mw_element_item_subtitle',
								],
							],
							[
								'id' => 'text',
								'title' => __('Popis', 'cms_ve'),
								'type' => 'textarea',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_element_item[qt] .mw_element_item_description',
								],
							],
							[
								'id' => 'image',
								'title' => __('Fotografie', 'cms_ve'),
								'type' => 'image',
								'content' => [
									'image' => '',
									'position' => '50% 50%',
								],
								'onedit' => [
									'action' => 'change_img',
									'target' => ' .mw_element_item[qt] img',
								],
							],
							[
								'id' => 'link',
								'title' => __('Odkaz', 'cms_ve'),
								'type' => 'page_link',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'phone',
								'title' => __('Telefon', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'email',
								'title' => __('Email', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'facebook',
								'title' => __('Odkaz na Facebook', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'linkedin',
								'title' => __('Odkaz na LinkedIn', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'youtube',
								'title' => __('Odkaz na YouTube', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'twitter',
								'title' => __('Odkaz na Twitter', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'instagram',
								'title' => __('Odkaz na Instagram', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'web',
								'title' => __('Odkaz na web', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],

						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'cols',
								'title' => __('Počet sloupců', 'cms_ve'),
								'type' => 'select',
								'content' => 'auto',
								'options' => [
									['name' => __('Automaticky', 'cms_ve'), 'value' => 'auto'],
									['name' => '1', 'value' => 'one'],
									['name' => '2', 'value' => 'two'],
									['name' => '3', 'value' => 'three'],
									['name' => '4', 'value' => 'four'],
									['name' => '5', 'value' => 'five'],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'cols_type',
								'title' => __('Mezery mezi sloupci', 'cms_ve'),
								'type' => 'select',
								'content' => '',
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_element_items',
									'class' => '',
								],
								'options' => [
									['name' => __('Velké', 'cms_ve'), 'value' => 'cols'],
									['name' => __('Malé', 'cms_ve'), 'value' => 'smallcols'],
									['name' => __('Žádné', 'cms_ve'), 'value' => 'fullcols'],
								],
								'show_group' => 'style',
								'show_val' => '3,5,6',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/peoples1.jpg',
									'2' => VS_DIR . 'images/image_select/peoples2.jpg',
									'3' => VS_DIR . 'images/image_select/peoples3.jpg',
									'5' => VS_DIR . 'images/image_select/peoples5.jpg',
									'4' => VS_DIR . 'images/image_select/peoples4.jpg',
									'6' => VS_DIR . 'images/image_select/item1.jpg',
								],
								'show' => 'style',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'background_set',
								'title' => __('Nastavení pozadí', 'cms_ve'),
								'type' => 'background_set',
								'content' => [
									'corner' => '',
									'border' => '',
									'shadow' => '1',
									'color' => '#ffffff',
								],
								'onedit' => [
									'action' => 'change_background_set',
									'target' => ' .mw_element_item',
								],
								'show_group' => 'style',
								'show_val' => '3,5',
							],

							[
								'id' => 'hover_color',
								'title' => __('Barva podbarvení po najetí myši', 'cms_ve'),
								'type' => 'transparent_color',
								'content' => [
									'color' => '#000000',
									'transparency' => '0.5',
									'rgba' => 'rgba(0,0,0,0.5)',
								],
								'onedit' => [
									'action' => 'change_css',
									'css' => 'background',
									'target' => ' .mw_element_item_image_hover',
								],
								'show_group' => 'style',
								'show_val' => '6',
							],
							[
								'id' => 'image_size',
								'title' => __('Velikost obrázku', 'cms_ve'),
								'type' => 'select',
								'content' => '3',
								'options' => [
									['name' => '1/2', 'value' => '2'],
									['name' => '1/3', 'value' => '3'],
									['name' => '1/4', 'value' => '4'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'style',
								'show_val' => '4',
							],
							[
								'id' => 'image_ratio',
								'title' => __('Zobrazit obrázky v poměru:', 'cms_ve'),
								'type' => 'select',
								'content' => '32',
								'options' => [
									['name' => __('Původní', 'cms_ve'), 'value' => 'original'],
									['name' => __('Široký (16:9)', 'cms_ve'), 'value' => '169'],
									['name' => __('Základní (3:2)', 'cms_ve'), 'value' => '32'],
									['name' => __('Střední (4:3)', 'cms_ve'), 'value' => '43'],
									['name' => __('Čtverec (1:1)', 'cms_ve'), 'value' => '11'],
									['name' => __('Základní na výšku (2:3)', 'cms_ve'), 'value' => '23'],
									['name' => __('Střední na výšku (3:4)', 'cms_ve'), 'value' => '34'],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_image_ratio',
									'class' => 'mw_image_ratio_',
								],
								'show_group' => 'style',
								'show_val' => '3,5,6',
							],
							[
								'id' => 'hover',
								'title' => __('Efekt po najetí myši', 'cms_ve'),
								'type' => 'select',
								'content' => 'zoom',
								'options' => [
									['name' => __('Žádný', 'cms_ve'), 'value' => ''],
									['name' => __('Zoom', 'cms_ve'), 'value' => 'zoom'],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'icons_color',
								'title' => __('Barva sociálních ikon', 'cms_ve'),
								'type' => 'color',
								'content' => '#ababab',
								'onedit' => [
									'action' => 'change_style_variable',
									'css' => '--social-icon-color-',
									'class' => '_hover_color',
									'target' => ' .mw_social_icons_container',
								],

							],
						],
						'show_group' => 'style',
						'show_val' => '1,2,3,4,5',
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'font',
								'title' => __('Formátování nadpisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'use-font' => 'title',
									//'font-family'=>'',
									//'weight'=>'',
									//'line-height'=>'',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
									'show_group' => 'style',
									'show_color' => '1,2,3,4,5',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
								],
							],
							[
								'id' => 'font_position',
								'title' => __('Formátování textu pozice', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									//'font-family'=>'',
									//'weight'=>'',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
									'show_group' => 'style',
									'show_color' => '1,2,3,4,5',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_element_item_subtitle',
								],
							],
							[
								'id' => 'font_text',
								'title' => __('Formátování popisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_element_item_description',
								],
								'show_group' => 'style',
								'show_val' => '1,2,3,4,5',
							],
							[
								'id' => 'font_contacts',
								'title' => __('Formátování kontaktu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
									'show_group' => 'style',
									'show_color' => '1,2,3,4,5',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_peoples_contacts_container',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'slider',
				'name' => __('Slider', 'cms_ve'),
				'setting' => [
					[
						'id' => 'use_slider',
						'title' => '',
						'type' => 'switch',
						'label' => __('Zobrazit jako slider', 'cms_ve'),
						'show' => 'sliderset',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'miocarousel_setting',
						'type' => 'miocarousel',
						'content' => [
							'animation' => 'fade',
							'delay' => '3500',
							'speed' => '1000',
							'color_scheme' => '',
						],
						'onedit' => [
							'action' => 'change_slider',
							'target' => ' .miocarousel',
						],
						'show_group' => 'sliderset',
						'show_val' => '1',
					],
				],
			],
		],
	],
	'pricelist' => [
		'name' => __('Ceník', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/813-element-cenik',
		'tab_setting' => [
			[
				'id' => 'pricelist',
				'name' => __('Ceník', 'cms_ve'),
				'setting' => [
					[
						'title' => __('Typ ceníku', 'cms_ve'),
						'id' => 'pricelist_type',
						'type' => 'select',
						'show' => 'pricelist_type',
						'options' => [
							['name' => __('Sloupcový ceník', 'cms_ve'), 'value' => 'cols'],
							['name' => __('Řádkový ceník', 'cms_ve'), 'value' => 'rows'],
						],
						'onedit' => [
							'action' => 'reload',
						],
						'content' => 'cols',
					],
					[
						'id' => 'pricelist',
						'type' => 'multielement',
						'show_group' => 'pricelist_type',
						'show_val' => 'cols',
						'texts' => [
							'add' => __('Přidat sloupec ceníku', 'cms_ve'),
							'empty' => __('Sloupec', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'title',
								'title' => __('Název položky', 'cms_ve'),
								'type' => 'text',
								'content' => __('Varianta', 'cms_ve'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .pricelist_col[qt] .pricelist_title',
								],
							],
							[
								'id' => 'price',
								'title' => __('Cena', 'cms_ve'),
								'type' => 'text',
								'content' => __('100 Kč', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .pricelist_col[qt] .pricelist_price',
								],
							],
							[
								'id' => 'sale_price',
								'title' => __('Cena před slevou', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .pricelist_col[qt] .pricelist_sale_price',
								],
							],
							[
								'id' => 'per',
								'title' => __('Časové období', 'cms_ve'),
								'type' => 'text',
								'content' => __('Ročně', 'cms_ve'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .pricelist_col[qt] .pricelist_per',
								],
							],
							[
								'id' => 'description',
								'title' => __('Popis', 'cms_ve'),
								'type' => 'textarea',
								'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce viverra dui et dolor tristique, id auctor.',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .pricelist_col[qt] .pricelist_description',
								],
							],
							[
								'id' => 'features',
								'title' => __('Vlastnosti', 'cms_ve'),
								'type' => 'simple_feature',
								'sortable' => true,
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'button_hide',
								'type' => 'switch',
								'label' => __('Skrýt tlačítko', 'cms_ve'),
								'onedit' => [
									'action' => 'hide_show',
									'target' => ' .pricelist_col[qt] .pricelist_button',
								],

							],
							[
								'id' => 'button_text',
								'title' => __('Text tlačítka', 'cms_ve'),
								'type' => 'text',
								'content' => __('Objednat', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .pricelist_col[qt] .ve_but_text',
								],
							],
							[
								'id' => 'link',
								'title' => __('Odkaz tlačítka', 'cms_ve'),
								'type' => 'page_link',
								'onedit' => [
									'action' => 'change_link',
									'target' => ' .pricelist_col[qt] .ve_content_button',
								],
							],
							[
								'id' => 'text',
								'title' => __('Popisek pod tlačítkem', 'cms_ve'),
								'type' => 'textarea',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .pricelist_col[qt] .pricelist_info',
								],
							],
							[
								'id' => 'popular',
								'type' => 'switch',
								'label' => __('Označit jako nejoblíbenější', 'cms_ve'),
								'show' => 'popular',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'popular_text',
								'title' => __('Popisek zvýrazněné položky', 'cms_ve'),
								'type' => 'text',
								'content' => __('NEJPRODÁVANĚJŠÍ', 'cms_ve'),
								'show_group' => 'popular',
								'show_val' => '1',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .pricelist_col[qt] .pricelist_popular_text',
								],
							],

						],
					],
					[
						'id' => 'row_pricelist',
						'type' => 'multielement',
						'show_group' => 'pricelist_type',
						'show_val' => 'rows',
						'texts' => [
							'add' => __('Přidat řádek ceníku', 'cms_ve'),
							'empty' => __('Řádek', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'title',
								'title' => __('Název položky', 'cms_ve'),
								'type' => 'text',
								'content' => __('Položka ceníku', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' tr[qt] .pricelist_title',
								],
							],
							[
								'id' => 'text',
								'title' => __('Popis', 'cms_ve'),
								'type' => 'textarea',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' tr[qt] .ve_row_pricelist_desc',
								],
							],
							[
								'id' => 'price',
								'title' => __('Cena', 'cms_ve'),
								'type' => 'text',
								'content' => __('100 Kč', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' tr[qt] .pricelist_price span',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_val' => 'cols',
						'show_group' => 'pricelist_type',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '3',
								'show' => 'col_pricelist_style',
								'options' => [
									'1' => VS_DIR . 'images/image_select/pricelist1' . $image_lang . '.jpg',
									'2' => VS_DIR . 'images/image_select/pricelist2' . $image_lang . '.jpg',
									'3' => VS_DIR . 'images/image_select/pricelist3' . $image_lang . '.jpg',
									'4' => VS_DIR . 'images/image_select/pricelist4' . $image_lang . '.jpg',
								],
								'onedit' => [
									'action' => 'reload',
								],
							],

							[
								'id' => 'background_set',
								'title' => __('Nastavení pozadí', 'cms_ve'),
								'type' => 'background_set',
								'content' => [
									'corner' => '',
									'border' => '',
									'shadow' => '1',
									'color' => '#ffffff',
								],
								'onedit' => [
									'action' => 'change_background_set',
									'target' => ' .pricelist_col_bg',
								],
							],
							[
								'id' => 'cols_type',
								'title' => __('Mezery mezi sloupci', 'cms_ve'),
								'type' => 'select',
								'content' => '',
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_cols_pricelist',
									'class' => '',
								],
								'options' => [
									['name' => __('Velké', 'cms_ve'), 'value' => 'cols'],
									['name' => __('Malé', 'cms_ve'), 'value' => 'smallcols'],
									['name' => __('Žádné', 'cms_ve'), 'value' => 'fullcols'],
								],
								'show_group' => 'col_pricelist_style',
								'show_val' => '1,2,3',
							],
							[
								'id' => 'button',
								'title' => __('Styl tlačítka', 'cms_ve'),
								'type' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_nopopular_button',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_val' => 'cols',
						'show_group' => 'pricelist_type',
						'setting' => [
							[
								'id' => 'popular_color',
								'title' => __('Barva nejoblíbenější položky', 'cms_ve'),
								'type' => 'color',
								'content' => '#158ebf',
								'onedit' => [
									'action' => 'change_style_variable',
									'css' => '--popular-color-',
								],
							],
							[
								'id' => 'popular_button',
								'title' => __('Styl tlačítka nejoblíbenější položky', 'cms_ve'),
								'type' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_popular_button',
								],
							],
						],


					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'pricelist_type',
						'show_val' => 'rows',
						'setting' => [
							[
								'id' => 'row_table_style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '3',
								'options' => [
									'1' => VS_DIR . 'images/image_select/table1.png',
									'2' => VS_DIR . 'images/image_select/table2.png',
									'3' => VS_DIR . 'images/image_select/table3.png',
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'pricelist_type',
						'show_val' => 'cols',
						'setting' => [
							[
								'id' => 'font_title',
								'title' => __('Formátování názvu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '40',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .pricelist_title',
								],
							],
							[
								'id' => 'font',
								'title' => __('Formátování ceny', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'use-font' => 'subtitle',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '50',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .pricelist_price',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'pricelist_type',
						'show_val' => 'rows',
						'setting' => [
							[
								'id' => 'row_font',
								'title' => __('Formátování názvu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'title',
									'font-size' => '',
									'color' => '',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .pricelist_title',
								],
							],
							[
								'id' => 'row_font_price',
								'title' => __('Font ceny', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'title',
									'font-size' => '',
									'color' => '',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .pricelist_price',
								],
							],
							[
								'id' => 'row_font_desc',
								'title' => __('Formátování popisku', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .ve_row_pricelist_desc',
								],
							],
						],
					],
				],
			],
		],
	],
	'fapi' => [
		'name' => __('Prodejní formulář', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/815-element-prodejni-formular',
		'tab_setting' => [
			[
				'id' => 'fapi',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'content',
						'title' => __('Prodejní formulář', 'cms_ve'),
						'type' => 'sale_form_select',
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'api_type',
					],
				],
			],
			[
				'id' => 'setting',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					// fapi
					[
						'id' => 'contentinfo',
						'type' => 'info',
						'content' => __('Toto nastavení je funkční pouze pro staré vzhledy FAPI formulářů. U nových vzhledů můžete nastavit vzhled v administraci FAPI.', 'cms_ve'),
						'class' => 'mw_fapi_form_setting mw_newfapi_form_setting', // replace show_val a show group for showing or hiding visual setting for new fapi or old fapi form
					],
					[
						'id' => 'form-style',
						'title' => __('Styl formuláře', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'1' => VS_DIR . 'images/image_select/fapiform1.png',
							'2' => VS_DIR . 'images/image_select/fapiform2.png',
							'3' => VS_DIR . 'images/image_select/fapiform3.png',
							'4' => VS_DIR . 'images/image_select/fapiform4.png',
						],
						'onedit' => [
							'action' => 'change_class',
							'class' => 'in_element_fapi_form_',
							'target' => ' .in_element_content',
						],
						'class' => 'mw_fapi_form_setting mw_oldfapi_form_setting', // replace show_val a show group for showing or hiding visual setting for new fapi or old fapi form
					],
					[
						'id' => 'background-color',
						'title' => __('Barva pozadí', 'cms_ve'),
						'type' => 'color',
						'content' => '#ffffff',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--fapi-background-color-',
							'class' => '_color',
							'target' => ' .form_container',
						],
						'class' => 'mw_fapi_form_setting mw_oldfapi_form_setting', // replace show_val a show group for showing or hiding visual setting for new fapi or old fapi form
					],
					[
						'id' => 'button',
						'title' => __('Styl tlačítka', 'cms_ve'),
						'type' => 'button',
						'content' => ['style' => 'basic'],
						'onedit' => [
							'action' => 'change_button',
							'target' => ' .ve_content_button',
						],
						'class' => 'mw_fapi_form_setting mw_oldfapi_form_setting', // replace show_val a show group for showing or hiding visual setting for new fapi or old fapi form
					],
					[
						'id' => 'font_title',
						'title' => __('Font nadpisů', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'font-family' => '',
							'weight' => '',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '50',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .form_container_title',
						],
						'class' => 'mw_fapi_form_setting mw_oldfapi_form_setting', // replace show_val a show group for showing or hiding visual setting for new fapi or old fapi form
					],
					[
						'id' => 'font_text',
						'title' => __('Font textů', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '14',
						],
						'setting' => [
							'max_font_size' => '20',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .in_element_fapi_form',
						],
						'class' => 'mw_fapi_form_setting mw_oldfapi_form_setting', // replace show_val a show group for showing or hiding visual setting for new fapi or old fapi form
					],
					// simple shop
					[
						'id' => 'contentinfo',
						'type' => 'info',
						'content' => __('Vzhled SimpleShop formuláře můžete nastavit v administraci SimpleShopu.', 'cms_ve'),
						'show_group' => 'api_type',
						'show_val' => 'simpleshop',
					],
					// Mioweb
					[
						'id' => 'mw_active_color',
						'title' => __('Aktivní barva', 'cms_ve'),
						'type' => 'color',
						'content' => '#219ed1',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--order-form-active-color-',
						],
						'show_group' => 'api_type',
						'show_val' => 'mioweb',
					],
					[
						'id' => 'mw_background_set',
						'title' => __('Nastavení pozadí', 'cms_ve'),
						'type' => 'background_set',
						'content' => [
							'corner' => '',
							'border' => '',
							'shadow' => '1',
						],
						'onedit' => [
							'action' => 'change_background_set',
							'target' => ' .mws_order_form',
						],
						'show_group' => 'api_type',
						'show_val' => 'mioweb',
					],
					[
						'id' => 'mw_button',
						'title' => __('Styl tlačítka', 'cms_ve'),
						'type' => 'button',
						'content' => ['style' => 'basic'],
						'onedit' => [
							'action' => 'change_button',
							'target' => ' .ve_content_button',
						],
						'show_group' => 'api_type',
						'show_val' => 'mioweb',
					],
					[
						'id' => 'mw_button_text',
						'title' => __('Text tlačítka', 'cms_ve'),
						'type' => 'text',
						'content' => __('Objednat s povinností platby', 'cms_ve'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .ve_content_button .ve_but_text',
						],
						'show_group' => 'api_type',
						'show_val' => 'mioweb',
					],
				],
			],
		],
	],
	'menu' => [
		'name' => __('Menu', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/806-element-menu',
		'description' => __('Pro vkládání navigace do obsahu stránek. Pomocí tohoto elementu můžete jako menu vypsat seznam podstránek určité stránky anebo klasické wordpressové menu.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'menu',
				'name' => __('Menu', 'cms_ve'),
				'setting' => [
					[
						'id' => 'type',
						'title' => __('Vypsat', 'cms_ve'),
						'type' => 'select',
						'content' => 'menu',
						'show' => 'menuset',
						'options' => [
							['name' => __('Wordpressové menu', 'cms_ve'), 'value' => 'menu'],
							['name' => __('Seznam podstránek jako menu', 'cms_ve'), 'value' => 'subpage'],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'menu',
						'title' => __('Menu', 'cms_ve'),
						'type' => 'selectmenu',
						'show_group' => 'menuset',
						'show_val' => 'menu',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'page',
						'title' => __('Vypsat podstránky od', 'cms_ve'),
						'whisperer' => true,
						'type' => 'selectpage',
						'show_group' => 'menuset',
						'show_val' => 'subpage',
						'tooltip' => __('Pokud nic nezvolíte, vypíšou se podstránky této stránky.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'title',
						'content' => '',
						'title' => __('Nadpis menu', 'cms_ve'),
						'type' => 'text',
						'onedit' => [
							'action' => 'add_text',
							'target' => ' .menu_element_title',
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'menu_style',
						'title' => __('Vzhled menu', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [

							'1' => VS_DIR . 'images/image_select/menu1.jpg',
							'2' => VS_DIR . 'images/image_select/menu2.jpg',
							'8' => VS_DIR . 'images/image_select/menu8.jpg',
							'9' => VS_DIR . 'images/image_select/menu7.jpg',
							'6' => VS_DIR . 'images/image_select/menu6.jpg',
							'3' => VS_DIR . 'images/image_select/menu3.jpg',
							'4' => VS_DIR . 'images/image_select/menu4.jpg',
							'5' => VS_DIR . 'images/image_select/menu5.jpg',

						],
						'show' => 'style',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'background_set',
						'title' => __('Nastavení pozadí', 'cms_ve'),
						'type' => 'background_set',
						'content' => [
							'corner' => '',
							'border' => '',
							'shadow' => '1',
							'color' => '#ffffff',
						],
						'onedit' => [
							'action' => 'change_background_set',
							'target' => ' .mw_menu_element',
						],
						'show_group' => 'style',
						'show_val' => '2,9',
					],
					[
						'id' => 'color-active',
						'title' => __('Barva aktivní položky', 'cms_ve'),
						'type' => 'color',
						'content' => '#219ed1',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--active-color-',
						],
						'show_group' => 'style',
						'show_val' => '1,2,3,4,6,7,8',
					],
					[
						'id' => 'title_font',
						'title' => __('Formátování nadpisu', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'use-font' => 'subtitle',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '30',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .menu_element_title',
						],

					],
					[
						'id' => 'font',
						'title' => __('Formátování položek menu', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'color' => '',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' li',
						],
						'setting' => [
							'max_font_size' => '20',
						],
					],


				],
			],
		],
	],
	'countdown' => [
		'name' => __('Odpočet', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/807-element-odpocet',
		'description' => __('Časový odpočet události nebo akce. Stačí zadat datum a čas vypršení a vybrat z několika vzhledů.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'setting',
				'name' => __('Nastavení', 'cms_ve'),
				'setting' => [
					[
						'id' => 'countdown_type',
						'title' => __('Nastavit odpočet', 'cms_ve'),
						'type' => 'select',
						'options' => [
							['name' => __('Na konkrétní datum', 'cms_ve'), 'value' => 'date'],
							['name' => __('Od vstupu na stránku', 'cms_ve'), 'value' => 'page'],
						],
						'show' => 'countdown',
						'content' => 'date',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'content',
						'title' => __('Odpočet do', 'cms_ve'),
						'tooltip' => __('Nastavte datum a čas, do kdy má jít odpočet.', 'cms_ve'),
						'type' => 'datetime',
						'onedit' => [
							'action' => 'reload',
						],
						'show_group' => 'countdown',
						'show_val' => 'date',
					],
					[
						'id' => 'campaign_evergreen',
						'title' => __('Nastavit odpočet na (od vstupu)', 'cms_ve'),
						'type' => 'row_set',
						'show_group' => 'countdown',
						'show_val' => 'campaign,page',
						'setting' => [
							[
								'id' => 'evergreen_days',
								'title' => __('Dní', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'evergreen_hours',
								'title' => __('Hodin', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'evergreen_minutes',
								'title' => __('Minut', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'id' => 'evergreen_start',
						'title' => __('Začít odpočet od', 'cms_ve'),
						'type' => 'select',
						'options' => [
							['name' => __('Půlnoci dne vstupu', 'cms_ve'), 'value' => 'mid'],
							['name' => __('Začátku dne vstupu', 'cms_ve'), 'value' => 'start'],
							['name' => __('Času vstupu', 'cms_ve'), 'value' => 'enter'],
						],
						'content' => 'enter',
						'onedit' => [
							'action' => 'reload',
						],
						'show_group' => 'countdown',
						'show_val' => 'campaign,page',
					],
					[
						'id' => 'redirect',
						'title' => __('Po skončení přesměrovat:', 'cms_ve'),
						'type' => 'page_link',
						'target' => false,
						'tooltip' => __('Pokud nenastavíte žádnou URL, přesměrování se neprovede.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],

				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'style',
						'title' => __('Vzhled odpočtu', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'1' => VS_DIR . 'images/image_select/countdown1' . $image_lang . '.png',
							//'2' => VS_DIR.'images/image_select/countdown2'.$image_lang.'.png',
							'3' => VS_DIR . 'images/image_select/countdown3' . $image_lang . '.png',
							//'4' => VS_DIR.'images/image_select/countdown4'.$image_lang.'.png',
							'5' => VS_DIR . 'images/image_select/countdown5' . $image_lang . '.png',
							'6' => VS_DIR . 'images/image_select/countdown6' . $image_lang . '.png',
							'7' => VS_DIR . 'images/image_select/countdown7' . $image_lang . '.png',
						],
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'countdown_style',
					],
					[
						'id' => 'text_before',
						'title' => __('Text na začátku', 'cms_ve'),
						'type' => 'text',
						'content' => __('Akce končí za', 'cms_ve'),
						'show_group' => 'countdown_style',
						'show_val' => '7',
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .position_before',
						],
					],
					[
						'id' => 'size',
						'title' => __('Velikost', 'cms_ve'),
						'type' => 'slider',
						'formobile' => true,
						'content' => '40',
						'setting' => [
							'min' => '10',
							'max' => '80',
							'unit' => 'px',
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'font-size',
							'target' => ' .ve_countdown',
						],
					],
					[
						'id' => 'background-color',
						'title' => __('Barva odpočtu', 'cms_ve'),
						'type' => 'color',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--countdown-background-',
							'class' => '_countdown_color',
							'target' => ' .ve_countdown',
						],
					],
					[
						'id' => 'font',
						'title' => __('Font čísel', 'cms_ve'),
						'type' => 'font',
						'content' => [
							//'font-size'=>'40',
							'use-font' => 'text',
							//'color'=>'',
						],
						'setting' => [
							'max_font_size' => '80',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_countdown',
						],
					],

					[
						'id' => 'font-text',
						'title' => __('Font popisků', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'color' => '',
						],
						'show_group' => 'countdown_style',
						'show_val' => '1,5,6',
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_countdown .position_title',
						],
					],
				],
			],
		],
	],
	'numbers' => [
		'name' => __('Čísla', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/810-element-cisla',
		'description' => __('Chcete se pochlubit nějakými čísly? Například kolikrát si někdo stáhl váš ebook nebo kolik má stran? Použijte tento element.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'form',
				'name' => __('Čísla', 'cms_ve'),
				'setting' => [
					[
						'id' => 'numbers',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat číslo', 'cms_ve'),
							'empty' => __('Číslo', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'type',
								'title' => '',
								'type' => 'select',
								'content' => 'custom',
								'options' => [
									['name' => __('Zadat číslo', 'cms_ve'), 'value' => 'custom'],
									['name' => __('Načíst číslo', 'cms_ve'), 'value' => 'load'],
								],
								'show' => 'number',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'se',
								'title' => __('Zobrazit počet kontaktů v seznamu', 'cms_ve'),
								'type' => 'list_select',
								'hide_purposes' => true,
								'show_group' => 'number',
								'show_val' => 'load',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'show_deleted',
								'title' => '',
								'type' => 'switch',
								'label' => __('Započítat i smazané kontakty', 'cms_ve'),
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'number',
								'show_val' => 'load',
								'class' => 'se_switch mail_api_switch',
							],
							[
								'id' => 'number',
								'title' => __('Číslo', 'cms_ve'),
								'type' => 'text',
								'content' => '100',
								'show_group' => 'number',
								'show_val' => 'custom',
								'onedit' => [
									'action' => 'change_float_formatted',
									'target' => ' .ve_number_count_item[qt] .num',
								],
							],
							[
								'id' => 'unit',
								'title' => __('Jednotka (zobrazí se za číslem)', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'add_text_nodisp',
									'target' => ' .ve_number_count_item[qt] .unit',
								],
							],
							[
								'id' => 'title',
								'title' => __('Text pod číslem', 'cms_ve'),
								'type' => 'text',
								'content' => __('Klientů', 'cms_ve'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .ve_number_count_item[qt] .ve_number_text',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'style',
						'title' => __('Vzhled', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'1' => VS_DIR . 'images/image_select/number1.png',
							'2' => VS_DIR . 'images/image_select/number2.png',
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'cols',
						'title' => __('Počet sloupců', 'cms_ve'),
						'type' => 'select',
						'content' => '',
						'options' => [
							['name' => __('Automaticky', 'cms_ve'), 'value' => ''],
							['name' => '1', 'value' => 'one'],
							['name' => '2', 'value' => 'two'],
							['name' => '3', 'value' => 'three'],
							['name' => '4', 'value' => 'four'],
							['name' => '5', 'value' => 'five'],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'number_font',
						'title' => __('Font čísla', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '40',
							'use-font' => 'subtitle',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '60',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_number_count',
						],
					],
					[
						'id' => 'text_font',
						'title' => __('Font textu', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '15',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '20',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_number_text',
						],
					],
				],
			],

		],

	],

	'google_map' => [
		'name' => __('Mapa', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/820-element-mapa',
		'tab_setting' => [
			[
				'id' => 'map',
				'name' => __('Mapa', 'cms_ve'),
				'setting' => [

					[
						'id' => 'map_setting',
						'title' => '',
						'type' => 'google_map',
						'content' => [
							'address' => __('Praha', 'cms_ve'),
							'zoom' => '12',
						],
						'onedit' => [
							'action' => 'change_gmap',
						],
					],
					[
						'id' => 'height',
						'title' => __('Výška mapy', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '100',
							'max' => '1000',
							'unit' => 'px',
						],
						'content' => '400',
						'show_group' => 'google_map',
						'onedit' => [
							'action' => 'change_css',
							'css' => 'height',
							'target' => ' .mw_google_map_container',
						],
					],
					[
						'id' => 'setting',
						'title' => __('Nastavení mapy', 'cms_ve'),
						'type' => 'multiple_checkbox',
						'options' => [
							['name' => __('Povolit zoomování myší', 'cms_ve'), 'value' => 'scrollwheel'],
						],
						'show_group' => 'google_map',
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
		],

	],

	'table' => [
		'name' => __('Tabulka', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/817-element-tabulka',
		'description' => __('Pro vykreslení jednoduchých dvousloupcových tabulek', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'items',
				'name' => __('Tabulka', 'cms_ve'),
				'setting' => [
					[
						'id' => 'lines',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat řádek tabulky', 'cms_ve'),
							'empty' => __('Řádek', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'title',
								'title' => __('První sloupec (nadpis)', 'cms_ve'),
								'type' => 'text',
								'content' => __('Nadpis', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' tr[qt] th',
								],
							],
							[
								'id' => 'text',
								'title' => __('Druhý sloupec', 'cms_ve'),
								'type' => 'textarea',
								'content' => 'Lorem ipsum dolor',
								'onedit' => [
									'action' => 'change_text',
									'target' => ' tr[qt] td',
								],
							],

						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'style',
						'title' => __('Styl', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '3',
						'options' => [
							'1' => VS_DIR . 'images/image_select/table1.png',
							'2' => VS_DIR . 'images/image_select/table2.png',
							'3' => VS_DIR . 'images/image_select/table3.png',
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'width',
						'title' => __('Šířka prvního sloupce', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '300',
							'unit' => ['%', 'px'],
						],
						'content' => [
							'size' => '20',
							'unit' => '%',
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'width',
							'target' => ' .mw_table th',
						],
					],
					[
						'name' => __('Zarovnání druhého sloupce', 'cms_ve'),
						'id' => 'text_align',
						'type' => 'imageoption',
						'options' => [
							'left' => [
								'icon' => 'align-left',
								'text' => __('Nalevo', 'cms_ve'),
							],
							'right' => [
								'icon' => 'align-right',
								'text' => __('Napravo', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .mw_table',
							'class' => 'mw_table_',
						],
						'content' => 'left',
					],
					[
						'id' => 'font',
						'title' => __('Formát textů', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'color' => '',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .mw_table',
						],
					],
				],
			],
		],
	],

	'event_calendar' => [
		'name' => __('Kalendář akcí', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/819-element-kalendar-akci',
		'exclude' => ['slide'],
		'description' => __('Pomocí tohoto elementu můžete vytvářet výpis chystaných akcí.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'items',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'show',
								'title' => __('Zobrazit', 'cms_ve'),
								'type' => 'select',
								'content' => '>',
								'options' => [
									['name' => 'Jen budoucí akce', 'value' => '>'],
									['name' => 'Jen minulé akce', 'value' => '<'],
									['name' => 'Budoucí i minulé akce', 'value' => ''],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'category',
								'title' => __('Vypisovat akce z kategorie', 'cms_ve'),
								'type' => 'event_category_select',
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'categories',
							],
							[
								'id' => 'num',
								'title' => __('Počet akcí', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'word_count',
								'title' => __('Počet slov popisu', 'cms_ve'),
								'type' => 'slider',
								'setting' => [
									'min' => '5',
									'max' => '50',
									'unit' => '',
								],
								'content' => '10',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'show_cats',
								'type' => 'switch',
								'label' => __('Zobrazit kategorie', 'cms_ve'),
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'categories',
								'show_val' => '',
							],
							[
								'id' => 'hide_image',
								'type' => 'switch',
								'label' => __('Skrýt obrázky', 'cms_ve'),
								'show_group' => 'style',
								'show_val' => '3,7,4,6',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'hide_description',
								'type' => 'switch',
								'label' => __('Skrýt popis', 'cms_ve'),
								'show_group' => 'style',
								'show_val' => '3,7,4,6',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'hide_date',
								'type' => 'switch',
								'label' => __('Skrýt zvýrazněné datum', 'cms_ve'),
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'page',
								'title' => __('Seznam akcí', 'cms_ve'),
								'type' => 'events_list',
							],
						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'cols',
								'title' => __('Počet sloupců', 'cms_ve'),
								'type' => 'select',
								'content' => 0,
								'options' => [
									['name' => __('Automaticky', 'cms_ve'), 'value' => 0],
									['name' => '1', 'value' => 1],
									['name' => '2', 'value' => 2],
									['name' => '3', 'value' => 3],
									['name' => '4', 'value' => 4],
									['name' => '5', 'value' => 5],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'cols_type',
								'title' => __('Mezery mezi sloupci', 'cms_ve'),
								'type' => 'select',
								'content' => 'cols',
								'options' => [
									['name' => __('Velké', 'cms_ve'), 'value' => 'cols'],
									['name' => __('Malé', 'cms_ve'), 'value' => 'smallcols'],
									['name' => __('Žádné', 'cms_ve'), 'value' => 'fullcols'],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_element_items',
								],
								'show_group' => 'style',
								'show_val' => '4,7,5,2',
							],
						],
					],

					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '3',
								'options' => [
									'3' => VS_DIR . 'images/image_select/item3.jpg',
									'4' => VS_DIR . 'images/image_select/item4.jpg',
									'6' => VS_DIR . 'images/image_select/item6.jpg',
									'7' => VS_DIR . 'images/image_select/item7.jpg',
									'5' => VS_DIR . 'images/image_select/item5.jpg',
								],
								'show' => 'style',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'background_set',
								'title' => __('Nastavení pozadí', 'cms_blog'),
								'type' => 'background_set',
								'content' => [
									'corner' => '',
									'border' => '',
									'shadow' => '1',
									'color' => '#ffffff',
								],
								'onedit' => [
									'action' => 'change_background_set',
									'target' => ' .mw_element_item',
								],
								'show_group' => 'style',
								'show_val' => '4,7',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'image_size',
								'title' => __('Velikost obrázku', 'cms_ve'),
								'type' => 'select',
								'content' => '2',
								'options' => [
									['name' => '1/2', 'value' => '2'],
									['name' => '1/3', 'value' => '3'],
									['name' => '1/4', 'value' => '4'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'style',
								'show_val' => '6,7',
							],
							[
								'id' => 'image_ratio',
								'title' => __('Zobrazit obrázky v poměru:', 'cms_ve'),
								'type' => 'select',
								'content' => '32',
								'options' => [
									['name' => __('Původní', 'cms_ve'), 'value' => 'original'],
									['name' => __('Široký (16:9)', 'cms_ve'), 'value' => '169'],
									['name' => __('Základní (3:2)', 'cms_ve'), 'value' => '32'],
									['name' => __('Střední (4:3)', 'cms_ve'), 'value' => '43'],
									['name' => __('Čtverec (1:1)', 'cms_ve'), 'value' => '11'],
									['name' => __('Základní na výšku (2:3)', 'cms_ve'), 'value' => '23'],
									['name' => __('Střední na výšku (3:4)', 'cms_ve'), 'value' => '34'],
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .mw_image_ratio',
									'class' => 'mw_image_ratio_',
								],
							],
							[
								'id' => 'hover',
								'title' => __('Efekt po najetí myši', 'cms_ve'),
								'type' => 'select',
								'content' => 'zoom',
								'options' => [
									['name' => __('Žádný', 'cms_ve'), 'value' => ''],
									['name' => __('Zoom', 'cms_ve'), 'value' => 'zoom'],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'color',
								'title' => __('Barva zvýrazněného datumu', 'cms_ve'),
								'type' => 'color',
								'content' => '#158ebf',
								'onedit' => [
									'action' => 'change_styles',
									'css' => 'background-color',
									'target' => ' .mw_event_date_container',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'style',
						'show_val' => '3,4,6,7',
						'setting' => [
							[
								'id' => 'show_button',
								'label' => __('Zobrazit tlačítko', 'cms_ve'),
								'type' => 'switch',
								'show' => 'feature_button',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'button_text',
								'title' => __('Text tlačítka', 'cms_ve'),
								'type' => 'text',
								'content' => __('Více informací', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_content_button',
								],
								'show_group' => 'feature_button',
								'show_val' => '1',
							],
							[
								'id' => 'button',
								'title' => __('Styl tlačítka', 'cms_ve'),
								'type' => 'button',
								'show_group' => 'feature_button',
								'show_val' => '1',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_button',
								],
								'content' => [
									'style' => 'basic',
									'button_size' => 'small',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'font_title',
								'title' => __('Formátování názvu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'title',
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '50',
									'font_size_placeholder' => '20',
									'show_group' => 'style',
									'show_color' => '3,4,6,7',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
								],
							],
							[
								'id' => 'font_subtitle',
								'title' => __('Formátování podnadpisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
									'font_size_placeholder' => '13',
									'show_group' => 'style',
									'show_color' => '3,4,6,7',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_element_item_subtitle',
								],
							],
							[
								'id' => 'font_description',
								'title' => __('Formátování popisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_element_item_description',
								],
								'show_group' => 'style',
								'show_val' => '3,4,6,7',
							],
						],
					],
				],
			],
		],
	],
	'faq' => [
		'name' => __('FAQ', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/811-element-faq',
		'description' => __('Časté dotazy. Tímto je přidáte.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'form',
				'name' => __('FAQ', 'cms_ve'),
				'setting' => [
					[
						'id' => 'faqs',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat otázku', 'cms_ve'),
							'empty' => __('Otázka', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'question',
								'title' => __('Otázka', 'cms_ve'),
								'content' => __('Otázka', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .faq_item[qt] .ve_faq_question_text',
								],
							],
							[
								'id' => 'answer',
								'title' => __('Odpověď', 'cms_ve'),
								'content' => __('Odpověď na otázku', 'cms_ve'),
								'type' => 'textarea',
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .faq_item[qt] .ve_faq_answer',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'cols',
						'title' => __('Počet sloupců', 'cms_ve'),
						'type' => 'select',
						'content' => 'one',
						'options' => [
							['name' => '1', 'value' => 'one'],
							['name' => '2', 'value' => 'two'],
							['name' => '3', 'value' => 'three'],
							['name' => '4', 'value' => 'four'],
							['name' => '5', 'value' => 'five'],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'clickable',
						'type' => 'switch',
						'label' => __('Rozbalovací odpovědi', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'clickable',
					],
					[
						'id' => 'open_first',
						'type' => 'switch',
						'label' => __('Rozbalit první', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
						'show_group' => 'clickable',
						'show_val' => '1',
					],
					[
						'id' => 'style',
						'title' => __('Vzhled', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'1' => VS_DIR . 'images/image_select/faq1.jpg',
							'2' => VS_DIR . 'images/image_select/faq2.jpg',
							'3' => VS_DIR . 'images/image_select/faq3.jpg',
							'4' => VS_DIR . 'images/image_select/faq4.jpg',
							'5' => VS_DIR . 'images/image_select/faq5.jpg',
						],
						'show' => 'faq_style',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'background_set',
						'title' => __('Nastavení pozadí', 'cms_ve'),
						'type' => 'background_set',
						'content' => [
							'corner' => '',
							'border' => '',
							'shadow' => '1',
							'color' => '#ffffff',
						],
						'onedit' => [
							'action' => 'change_background_set',
							'target' => ' .faq_element_bg',
						],
						'show_group' => 'faq_style',
						'show_val' => '2,5',
					],
					[
						'id' => 'question_font',
						'title' => __('Font otázky', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '20',
							'use-font' => 'title',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '30',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_faq_question',
						],
					],
					[
						'id' => 'answer_font',
						'title' => __('Font odpovědi', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '20',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_faq_answer',
						],
					],
				],
			],
		],
	],


	'progressbar' => [
		'name' => __('Progress bar', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/814-element-progress-bar',
		'description' => __('Grafické znázornění procentuálního postupu. Vhodné jako ukazatel, kolik procent je už splněno, nebo jako znázornění úrovně znalostí na osobním webu.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'progressbar',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'percent',
						'title' => __('Procento', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '100',
							'unit' => '%',
						],
						'content' => '50',
						'onedit' => [
							'action' => 'change_progress',
						],
					],
					[
						'id' => 'text',
						'title' => __('Text', 'cms_ve'),
						'type' => 'text',
						'content' => __('Vlastnost', 'cms_ve'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .ve_progressbar_text',
						],
					],

				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'style',
						'title' => __('Styl', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'1' => VS_DIR . 'images/image_select/progressbar1.png',
							'2' => VS_DIR . 'images/image_select/progressbar2.png',
							'3' => VS_DIR . 'images/image_select/progressbar3.png',
							'4' => VS_DIR . 'images/image_select/progressbar4.png',
							'5' => VS_DIR . 'images/image_select/progressbar5.png',
							'6' => VS_DIR . 'images/image_select/progressbar6' . $image_lang . '.png',
						],
						'show' => 'progressbar_style',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'color1',
						'title' => __('Barva progressbaru', 'cms_ve'),
						'type' => 'color',
						'content' => '#56b616',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--progress-color-',
							'class' => '_progressbar_color',
							'target' => ' .ve_progressbar',
						],
					],
					[
						'id' => 'color2',
						'title' => __('Barva pozadí', 'cms_ve'),
						'type' => 'color',
						'content' => '#eeeeee',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--progress-color-bg-',
						],
						'show_group' => 'progressbar_style',
						'show_val' => '1,2,4,5,6',
					],
					[
						'id' => 'rounded',
						'type' => 'switch',
						'label' => __('Zakulatit rohy', 'cms_ve'),
						'onedit' => [
							'action' => 'toggle_class',
							'target' => ' .in_element_content',
							'class' => 've_progressbar_rounded',
						],
					],
					[
						'id' => 'font',
						'title' => __('Font', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							//'color'=>'',
						],
						'setting' => [
							'max_font_size' => '40',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_progressbar',
						],
					],
				],
			],
		],
	],

	'link' => [
		'name' => __('Odkaz', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/814-element-progress-bar',
		'description' => __('Vytvořte odkaz na stránku nebo na pop-up.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Odkaz', 'cms_ve'),
				'setting' => [
					[
						'id' => 'content',
						'title' => __('Text odkazu', 'cms_ve'),
						'type' => 'text',
						'content' => __('Text odkazu', 'cms_ve'),
						'onedit' => [
							'action' => 'reload_text',
							'target' => ' .ve_content_link a',
						],
					],
					[
						'id' => 'show',
						'title' => __('Po kliknutí na odkaz', 'cms_ve'),
						'type' => 'select',
						'content' => 'url',
						'options' => [
							['name' => __('Otevřít stránku', 'cms_ve'), 'value' => 'url'],
							['name' => __('Zobrazit pop-up', 'cms_ve'), 'value' => 'popup'],
						],
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'linkaction',
					],
					[
						'id' => 'link',
						'title' => __('Odkazovat na', 'cms_ve'),
						'type' => 'page_link',
						'content' => ['page' => ''],
						'show_group' => 'linkaction',
						'show_val' => 'url',
						'onedit' => [
							'action' => 'change_link',
							'target' => ' .ve_content_link a',
						],
					],
					[
						'title' => __('Zobrazit pop-up', 'cms_ve'),
						'id' => 'popup',
						'type' => 'popupselect',
						'show_group' => 'linkaction',
						'show_val' => 'popup',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'name' => __('Zarovnání', 'cms_ve'),
						'id' => 'align',
						'type' => 'imageoption',
						'options' => [
							'left' => [
								'icon' => 'align-left',
								'text' => __('Nalevo', 'cms_ve'),
							],
							'center' => [
								'icon' => 'align-center',
								'text' => __('Na střed', 'cms_ve'),
							],
							'right' => [
								'icon' => 'align-right',
								'text' => __('Napravo', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'change_class',
							'class' => 've_',
							'target' => ' .ve_content_link',
						],
						'content' => 'center',
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'font',
						'title' => __('Font odkazu', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'use-font' => 'text',
							'color' => '',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_content_link a',
						],
					],
				],
			],
		],
	],


	'member_download' => [
		'name' => __('Ke stažení', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/755-element-ke-stazeni',
		'tab_setting' => [
			[
				'id' => 'multifiles',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'content',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat soubor ke stažení', 'cms_ve'),
							'empty' => __('Ke stažení', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'name',
								'title' => __('Název souboru', 'cms_ve'),
								'type' => 'text',
								'content' => __('Název souboru', 'cms_ve'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_icon_text[qt] .mw_icon_text-text a',
								],
							],
							[
								'id' => 'file',
								'title' => __('Soubor ke stažení', 'cms_ve'),
								'type' => 'upload_file',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'desc',
								'title' => __('Popisek souboru', 'cms_ve'),
								'type' => 'textarea',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_icon_text[qt] p',
								],
							],
							[
								'id' => 'icon',
								'title' => __('Ikona', 'cms_ve'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'download',
									'icon_set' => 'feather',
								],
								'onedit' => [
									'action' => 'change_icon',
									'target' => ' .mw_icon_text[qt] .mw_icon i',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'style',
						'title' => __('Styl', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'3' => VS_DIR . 'images/image_select/download3.jpg',
							'2' => VS_DIR . 'images/image_select/download2.jpg',
							'1' => VS_DIR . 'images/image_select/download1.jpg',
							'4' => VS_DIR . 'images/image_select/download4.jpg',
						],
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'style',
					],
					[
						'id' => 'background_set',
						'title' => __('Nastavení pozadí', 'cms_ve'),
						'type' => 'background_set',
						'content' => [
							'corner' => '',
							'border' => '1',
							'shadow' => '',
							'color' => '#ffffff',
						],
						'onedit' => [
							'action' => 'change_background_set',
							'target' => ' .mw_download_element_background',
						],
						'show_group' => 'style',
						'show_val' => '1,4',
					],
					[
						'id' => 'icon_style',
						'title' => __('Styl ikonky', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '1',
						'options' => [
							'1' => VS_DIR . 'images/image_select/icon1.jpg',
							'2' => VS_DIR . 'images/image_select/icon2.jpg',
							'3' => VS_DIR . 'images/image_select/icon3.jpg',
							'4' => VS_DIR . 'images/image_select/icon4.jpg',
							'5' => VS_DIR . 'images/image_select/icon5.jpg',
						],
						'onedit' => [
							'action' => 'change_class',
							'class' => 'mw_icon_style_',
							'target' => ' .mw_icon_text-icon',
						],
					],
					[
						'id' => 'color',
						'title' => __('Barva ikonky', 'cms_ve'),
						'type' => 'color',
						'content' => '#219ed1',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--icon-color-',
							'class' => '_color',
							'target' => ' .mw_icon',
						],
					],
					[
						'id' => 'font',
						'title' => __('Formátování nadpisu', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '14',
						],
						'setting' => [
							'max_font_size' => '20',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .in_element_content',
						],
					],
				],
			],
		],

	],
	'wpcomments' => [
		'name' => __('Komentáře', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/805-element-komentare',
		'tab_setting' => [
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'style',
						'title' => __('Vzhled komentářů', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '3',
						'options' => [
							'3' => VS_DIR . 'images/image_select/comment3.png',
							'1' => VS_DIR . 'images/image_select/comment1.png',
							'2' => VS_DIR . 'images/image_select/comment2.png',
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .in_element_content',
							'class' => 'element_comment_',
						],
					],
					[
						'id' => 'button',
						'title' => __('Styl tlačítka', 'cms_ve'),
						'type' => 'button',
						'content' => [
							'style' => 'basic',
							'button_size' => 'medium',
						],
						'hide' => ['custom_size'],
						'onedit' => [
							'action' => 'change_button',
							'target' => ' .ve_content_button',
						],
					],
				],
			],
		],
	],
	'breadcrumbs' => [
		'name' => __('Drobečková navigace', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/925-element-drobeckove-navigace',
		'description' => __('Vložte na stránku drobečkovou navigaci.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'appearance',
				'name' => __('Vzhled', 'cms'),
				'setting' => [
					[
						'id' => 'font',
						'title' => __('Písmo', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'color' => '',
						],
						'visible' => ['font-size', 'color'],
						'setting' => [
							'max_font_size' => '30',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .mw_breadcrumbs',
						],
					],
				],
			],
		],
	],
	'cookie_management' => [
		'name' => __('Nastavení cookies', 'cms_ve'),
		//'help' => MW_HELP_URL . 'article/821-element-html',
		//'description' => __('Pomocí tohoto elementu můžete na stránky vkládat vlastní HTML nebo Javascript kódy.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'button_text',
						'title' => __('Text tlačítka', 'cms_ve'),
						'type' => 'text',
						'content' => __('Změnit nastavení cookies', 'cms_ve'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .mw_cookie_element_set_but .ve_but_text',
						],
						'show_group' => 'popup_type',
						'show_val' => 'button',
					],
					[
						'id' => 'button',
						'title' => __('Styl tlačítka', 'cms_ve'),
						'type' => 'button',
						'onedit' => [
							'action' => 'change_button',
							'target' => ' .mw_cookie_element_set_but',
						],
						'show_group' => 'popup_type',
						'show_val' => 'button',
					],
					[
						'name' => __('Zarovnání', 'cms_ve'),
						'id' => 'align',
						'type' => 'imageoption',
						'options' => [
							'left' => [
								'icon' => 'align-left',
								'text' => __('Nalevo', 'cms_ve'),
							],
							'center' => [
								'icon' => 'align-center',
								'text' => __('Na střed', 'cms_ve'),
							],
							'right' => [
								'icon' => 'align-right',
								'text' => __('Napravo', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'change_class',
							'class' => 've_content_button_',
							'target' => ' .mw_cookie_element_set_but',
						],
						'content' => 'center',
						'show_group' => 'popup_type',
						'show_val' => 'button,icon',
					],
				],
			],
		],

	],
	'html' => [
		'name' => __('HTML', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/821-element-html',
		'description' => __('Pomocí tohoto elementu můžete na stránky vkládat vlastní HTML nebo Javascript kódy.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'html',
				'name' => __('HTML', 'cms_ve'),
				'setting' => [
					[
						'id' => 'purpose',
						'content' => 'necessary',
						'title' => __('Účel kódu', 'cms_ve'),
						'type' => 'gdpr_purpose_select',
						'tooltip' => __('Dávejte pozor na to, aby byl HTML kód validní. Neukončené tagy mohou narušit strukturu stránky a při uložení pak může docházet k chybám. Pokud kód obsahuje Javascript, může se stát, že bude fungovat až po znovunačtení stránky.', 'cms_ve'),
					],

					[
						'id' => 'content',
						'title' => __('HTML/Javascript kód', 'cms_ve'),
						'type' => 'textarea',
						'rows' => '20',
						'desc' => __('Dávejte pozor na to, aby byl HTML kód validní. Neukončené tagy mohou narušit strukturu stránky a při uložení pak může docházet k chybám. Pokud kód obsahuje Javascript, může se stát, že bude fungovat až po znovunačtení stránky.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
		],

	],
], 'basic');

$vePage->add_elements([
	'social_icons' => [
		'name' => __('Sociální ikonky', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/924-element-pro-socialni-site-socialni-ikonky',
		'description' => __('Výpis ikonek sociálních sítí s odkazy na vaše profily.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'list',
				'name' => __('Ikony', 'cms_ve'),
				'setting' => [
					[
						'id' => 'socials',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat ikonku', 'cms_ve'),
							'empty' => __('Ikonka', 'cms_ve'),
						],
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'icon',
								'title' => __('Ikonka', 'cms_ve'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'facebook1',
								],
								'icons' => [
									'facebook1' => MW_ICONS_URL . 'social-icons.svg',
									'google-plus1' => MW_ICONS_URL . 'social-icons.svg',
									'google1' => MW_ICONS_URL . 'social-icons.svg',
									'linkedin1' => MW_ICONS_URL . 'social-icons.svg',
									'twitter1' => MW_ICONS_URL . 'social-icons.svg',
									'twitter2' => MW_ICONS_URL . 'social-icons.svg',
									'youtube1' => MW_ICONS_URL . 'social-icons.svg',
									'pinterest1' => MW_ICONS_URL . 'social-icons.svg',
									'instagram1' => MW_ICONS_URL . 'social-icons.svg',
									'vimeo1' => MW_ICONS_URL . 'social-icons.svg',
									'dribbble1' => MW_ICONS_URL . 'social-icons.svg',
									'behance1' => MW_ICONS_URL . 'social-icons.svg',
									'tumblr1' => MW_ICONS_URL . 'social-icons.svg',
									'flickr1' => MW_ICONS_URL . 'social-icons.svg',
									'skype1' => MW_ICONS_URL . 'social-icons.svg',
									'spotify' => MW_ICONS_URL . 'social-icons.svg',
									'itunes' => MW_ICONS_URL . 'social-icons.svg',
									'tiktok' => MW_ICONS_URL . 'social-icons.svg',
								],
								'onedit' => [
									'action' => 'change_icon_simple',
									'target' => ' .mw_social_icon_bg[qt]',
								],
							],
							[
								'id' => 'link',
								'title' => __('Odkaz', 'cms_ve'),
								'type' => 'text',
								'onedit' => [
									'action' => 'change_attr',
									'target' => ' .mw_social_icon_bg[qt]',
									'setting' => 'href',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'style',
						'title' => __('Vzhled', 'cms_ve'),
						'type' => 'imageselect',
						'content' => '2',
						'options' => [
							'2' => VS_DIR . 'images/image_select/social_icons2.jpg',
							'3' => VS_DIR . 'images/image_select/social_icons3.jpg',
							'1' => VS_DIR . 'images/image_select/social_icons1.jpg',
							'4' => VS_DIR . 'images/image_select/social_icons4.jpg',
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'name' => __('Zarovnání', 'cms_ve'),
						'id' => 'align',
						'type' => 'imageoption',
						'options' => [
							'left' => [
								'icon' => 'align-left',
								'text' => __('Nalevo', 'cms_ve'),
							],
							'center' => [
								'icon' => 'align-center',
								'text' => __('Na střed', 'cms_ve'),
							],
							'right' => [
								'icon' => 'align-right',
								'text' => __('Napravo', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .mw_social_icons_container',
							'class' => 've_',
						],
						'content' => 'center',
					],
					[
						'id' => 'size',
						'title' => __('Velikost', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '15',
							'max' => '40',
							'unit' => 'px',
						],
						'content' => '20',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--social-icon-size-',
						],
					],
					[
						'id' => 'space',
						'title' => __('Rozestup', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '0',
							'max' => '50',
							'unit' => 'px',
						],
						'content' => '15',
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'margin-right',
							'target' => ' .mw_social_icon_bg',
						],
					],
					[
						'id' => 'color',
						'title' => __('Barva', 'cms_ve'),
						'type' => 'color',
						'content' => '#158ebf',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--social-icon-color-',
							'class' => '_color',
							'target' => ' .mw_social_icons_container',
						],
					],
					[
						'id' => 'hover_color',
						'title' => __('Barva po najetí myši', 'cms_ve'),
						'type' => 'color',
						'content' => '',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--social-icon-hover-',
							'class' => '_hover_color',
							'target' => ' .mw_social_icons_container',
						],
					],
				],
			],
		],
	],

	'share' => [
		'name' => __('Sociální tlačítka', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/742-element-pro-socialni-site---socialni-tlacitka',
		'description' => __('Tento element vloží na stránku tlačítka pro sdílení na sociálních sítích.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'show',
						'title' => __('Zobrazit', 'cms_ve'),
						'type' => 'multiple_checkbox',
						'options' => [
							['name' => 'Facebook', 'value' => 'facebook'],
							['name' => 'Twitter', 'value' => 'twitter'],
							['name' => 'LinkedIn', 'value' => 'linkedin'],
						],
						'content' => ['facebook' => 'facebook', 'twitter' => 'twitter', 'linkedin' => 'linkedin'],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'name' => __('Zarovnání', 'cms_ve'),
						'id' => 'align',
						'type' => 'imageoption',
						'options' => [
							'left' => [
								'icon' => 'align-left',
								'text' => __('Nalevo', 'cms_ve'),
							],
							'center' => [
								'icon' => 'align-center',
								'text' => __('Na střed', 'cms_ve'),
							],
							'right' => [
								'icon' => 'align-right',
								'text' => __('Napravo', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .in_share_element',
							'class' => 've_',
						],
						'content' => 'center',
					],
					[
						'id' => 'content',
						'title' => __('Lajkovat stránku', 'cms_ve'),
						'type' => 'page_link',
						'content' => ['page' => ''],
						'target' => false,
						'tooltip' => __('Pokud nevyberete stránku nebo nezadáte žádnou adresu, použije se adresa aktuální stránky.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
		],
	],

	'like' => [
		'name' => __('Like button', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/743-element-pro-socialni-site-like-button',
		'description' => __('Pro vložení facebookového tlačítka &quot;To se mi líbí&quot;, pomocí kterého lze stránku sdílet na Facebooku.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'setting',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'content',
						'title' => __('Lajkovat stránku', 'cms_ve'),
						'type' => 'page_link',
						'content' => ['page' => ''],
						'target' => false,
						'tooltip' => __('Pokud nevyberete stránku nebo nezadáte žádnou adresu, použije se adresa aktuální stránky.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'layout',
						'title' => __('Vzhled', 'cms_ve'),
						'type' => 'imageselect',
						'options' => [
							'standard' => VS_DIR . 'images/image_select/like1.jpg',
							'button_count' => VS_DIR . 'images/image_select/like2.jpg',
							'box_count' => VS_DIR . 'images/image_select/like3.jpg',
							'button' => VS_DIR . 'images/image_select/like4.jpg',
						],
						'content' => 'button_count',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'scheme',
						'title' => __('Barevné schéma', 'cms_ve'),
						'type' => 'select',
						'options' => [
							['name' => __('Světlé', 'cms_ve'), 'value' => 'light'],
							['name' => __('Tmavé', 'cms_ve'), 'value' => 'dark'],
						],
						'content' => 'light',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'setting',
						'title' => __('Nastavení', 'cms_ve'),
						'type' => 'multiple_checkbox',
						'options' => [
//							['name' => __('Zobrazit obrázky přátel', 'cms_ve'), 'value' => 'faces'],
							['name' => __('Zobrazit tlačítko „Sdílet“', 'cms_ve'), 'value' => 'share'],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'name' => __('Zarovnání', 'cms_ve'),
						'id' => 'align',
						'type' => 'imageoption',
						'options' => [
							'left' => [
								'icon' => 'align-left',
								'text' => __('Nalevo', 'cms_ve'),
							],
							'center' => [
								'icon' => 'align-center',
								'text' => __('Na střed', 'cms_ve'),
							],
							'right' => [
								'icon' => 'align-right',
								'text' => __('Napravo', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .in_element_content',
							'class' => 've_',
						],
						'content' => 'center',
					],
				],
			],

		],
	],
	'fac_share' => [
		'name' => __('Sdílet na Facebooku', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/744-element-pro-socialni-site-sdilet-na-facebooku',
		'description' => __('Facebookové tlačítko pro sdílení stránky na Facebooku. Můžete si vybrat z několika vzhledů tlačítka.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'setting',
				'name' => __('Nastavení', 'cms_ve'),
				'setting' => [
					[
						'id' => 'content',
						'title' => __('Stránka, kterou chcete sdílet', 'cms_ve'),
						'type' => 'page_link',
						'target' => false,
						'tooltip' => __('Pokud nevyberete stránku nebo nezadáte žádnou adresu, použije se adresa aktuální stránky.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
			[
				'id' => 'appearance',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'appearance',
						'title' => __('Zobrazit', 'cms_ve'),
						'type' => 'select',
						'options' => [
							['name' => __('Klasické zobrazení', 'cms_ve'), 'value' => 'classic'],
							['name' => __('Tlačítko', 'cms_ve'), 'value' => 'button'],
							['name' => __('Vlastní obrázek', 'cms_ve'), 'value' => 'image'],
						],
						'content' => 'classic',
						'show' => 'share_style',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'button_group',
						'type' => 'group',
						'setting' => [
							[
								'id' => 'button_text',
								'title' => __('Text tlačítka', 'cms_ve'),
								'type' => 'text',
								'content' => __('Sdílet na Facebooku', 'cms_ve'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .ve_but_text',
								],
							],
							[
								'id' => 'button',
								'title' => __('Vzhled tlačítka', 'cms_ve'),
								'type' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_button',
								],
							],
							[
								'id' => 'icon',
								'title' => __('Ikona', 'cms_ve'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'facebook1',
								],
								'icons' => [
									'facebook1' => MW_ICONS_URL . 'social-icons.svg',
									'facebook2' => MW_ICONS_URL . 'social-icons.svg',
									'facebook3' => MW_ICONS_URL . 'social-icons.svg',
								],
								'onedit' => [
									'action' => 'change_icon',
									'target' => ' .ve_content_button .ve_but_icon',
								],
							],
						],
						'show_group' => 'share_style',
						'show_val' => 'button',
					],
					[
						'id' => 'layout',
						'title' => __('Vzhled tlačítka', 'cms_ve'),
						'type' => 'imageselect',
						'options' => [
							'button_count' => VS_DIR . 'images/image_select/fac_share1.png',
							'button' => VS_DIR . 'images/image_select/fac_share2.png',
							'box_count' => VS_DIR . 'images/image_select/fac_share3.png',
						],
						'content' => 'button_count',
						'show_group' => 'share_style',
						'show_val' => 'classic',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'image',
						'title' => __('Obrázek', 'cms_ve'),
						'type' => 'image',
						'show_group' => 'share_style',
						'show_val' => 'image',
						'onedit' => [
							'action' => 'change_img',
							'target' => '.in_element_content img',
						],
					],

					[
						'name' => __('Zarovnání', 'cms_ve'),
						'id' => 'align',
						'type' => 'imageoption',
						'options' => [
							'left' => [
								'icon' => 'align-left',
								'text' => __('Nalevo', 'cms_ve'),
							],
							'center' => [
								'icon' => 'align-center',
								'text' => __('Na střed', 'cms_ve'),
							],
							'right' => [
								'icon' => 'align-right',
								'text' => __('Napravo', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'change_class',
							'target' => ' .in_element_content',
							'class' => 've_',
						],
						'content' => 'center',
					],
				],
			],
		],
	],
	'likebox' => [
		'name' => __('Page plugin', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/745-element-pro-socialni-site-page-plugin',
		'description' => __('Page plugin zobrazí seznam příspěvků z vaší facebookovské stránky.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'setting',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'content',
						'title' => __('URL vaší facebookové stránky', 'cms_ve'),
						'type' => 'text',
						'content' => __('https://www.facebook.com/mioweb.cz/', 'cms_ve'),
						'tooltip' => __('Adresu zadejte včetně <code>https://</code>.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'width',
						'title' => __('Šířka', 'cms_ve'),
						'type' => 'text',
						'content' => '500',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'height',
						'title' => __('Výška', 'cms_ve'),
						'type' => 'text',
						'content' => '340',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'tabs',
						'title' => __('Zobrazit', 'cms_ve'),
						'type' => 'multiple_checkbox',
						'options' => [
							['name' => __('Timeline', 'cms_ve'), 'value' => 'timeline'],
							['name' => __('Události', 'cms_ve'), 'value' => 'events'],
							['name' => __('Zprávy', 'cms_ve'), 'value' => 'messages'],
						],
						'content' => ['timeline'],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'setting',
						'title' => __('Nastavení', 'cms_ve'),
						'type' => 'multiple_checkbox',
						'options' => [
							['name' => __('Skrýt úvodní fotku', 'cms_ve'), 'value' => 'cover'],
							['name' => __('Skrýt avatary přátel', 'cms_ve'), 'value' => 'faces'],
							['name' => __('Zobrazit tlačítka akcí', 'cms_ve'), 'value' => 'cta'],
							['name' => __('Zobrazit malou úvodní fotku', 'cms_ve'), 'value' => 'header'],
						],
						'content' => [],
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
		],
	],
	'fcomments' => [
		'name' => __('Facebook komentáře', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/746-element-pro-socialni-site-facebookove-komentare',
		'description' => __('Facebookové komentáře jsou vhodné jako nástroj virtuálního šíření stránky na Facebooku.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'setting',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'content',
						'title' => __('URL komentované stránky', 'cms_ve'),
						'type' => 'page_link',
						'target' => false,
						'tooltip' => __('Pokud nevyberete stránku nebo nezadáte žádnou adresu, použije se adresa aktuální stránky.', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'per_page',
						'title' => __('Počet komentářů na stránku', 'cms_ve'),
						'type' => 'text',
						'content' => '10',
						'onedit' => [
							'action' => 'reload',
						],
					],
// Currently not working, @see https://developers.facebook.com/support/bugs/1759174414250782/
//					[
//						'id' => 'scheme',
//						'title' => __('Barevné schéma', 'cms_ve'),
//						'type' => 'select',
//						'content' => 'dark',
//						'options' => [
//							['name' => __('Světlé', 'cms_ve'), 'value' => 'light'],
//							['name' => __('Tmavé', 'cms_ve'), 'value' => 'dark'],
//						],
//						'onedit' => [
//							'action' => 'reload'
//						]
//					],
				],
			],
		],

	],

], 'social');

$vePage->add_elements([

	'twocols' => [
		'name' => __('Dva sloupce', 'cms_ve'),
		'subelements' => 1,
		'description' => __('Rozdělí obsah na dva sloupce, do kterých lze vkládat další elementy.', 'cms_ve'),
		'setting' => [],
	],
	'box' => [
		'name' => __('Blok', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/748-element-pro-strukturu-blok',
		'subelements' => 1,
		'tab_setting' => [
			[
				'id' => 'setting',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'background_color',
						'title' => __('Barva pozadí', 'cms_ve'),
						'type' => 'background',
						'content' => [
							'color1' => '#eeeeee',
							'rgba1' => 'rgba(221,221,221,1)',
							'transparency1' => '1',
						],
						'onedit' => [
							'action' => 'change_smart_background_color',
							'target' => ' .ve_content_block',
							'setting' => 'with_bg_image',
						],
					],
					[
						'id' => 'background_image',
						'title' => __('Obrázek na pozadí', 'cms_ve'),
						'type' => 'bgimage',
						'content' => [
							'pattern' => 0,
							'cover' => 1,
						],
						'hide' => ['efect'],
						'onedit' => [
							'action' => 'change_background',
							'target' => ' .ve_content_block',
						],
					],

					/*
						array(
							  'id'=>'link-color',
							  'title'=>__('Barva odkazů','cms_ve'),
							  'type'=>'color',
							  'content'=>'',
						), */
					[
						'id' => 'border',
						'title' => __('Ohraničení bloku', 'cms_ve'),
						'type' => 'border',
						'group' => 'input',
						'content' => [
							'size' => '0',
							'color' => '#eeeeee',
						],
						'onedit' => [
							'action' => 'change_styles',
							'target' => ' .ve_content_block',
							'css' => 'border',
						],
					],
					/*
						array(
							  'id'=>'corner',
							  'title'=>__('Míra zakulacení rohů','cms_ve'),
							  'type'=>'slider',
							  'setting'=>array(
								  'min'=>'0',
								  'max'=>'100',
								  'unit'=>'px'
							  ),
							  'content'=>'0',
							  'onedit'=>array(
								  'action'=>'change_styles',
								  'target'=>' .ve_content_block',
								  'css'=>'corner',
							  ),
						), */
					[
						'name' => __('Zakulacení rohů', 'cms_ve'),
						'id' => 'corner',
						'type' => 'imageoption',
						'options' => [
							'' => [
								'icon' => 'sharp_corner',
								'text' => __('Ostré', 'cms_ve'),
							],
							'1' => [
								'icon' => 'rounded_corner',
								'text' => __('Zakulacené', 'cms_ve'),
							],
							'2' => [
								'icon' => 'round_corner',
								'text' => __('Kulaté', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'change_class',
							'class' => 'mw_element_item_corners',
							'target' => ' .ve_content_block',
						],
						'content' => '',
					],
					[
						'id' => 'padding',
						'title' => __('Odsazení vnitřního obsahu', 'cms_ve'),
						'type' => 'slider',
						'setting' => [
							'min' => '15',
							'max' => '100',
							'unit' => 'px',
						],
						'content' => ['size' => '40'],
						'onedit' => [
							'action' => 'change_styles',
							'target' => ' .ve_content_block_content',
							'css' => 'padding',
						],
						'tooltip' => __('Určuje, jak daleko bude obsah odsazen od okraje elementu.', 'cms_ve'),
					], /*
						array(
							  'id'=>'padding',
							  'title'=>__('Odsazení vnitřního obsahu (padding) v px','cms_ve'),
							  'type'=>'padding',
							  'content'=>array('top'=>'40','right'=>'40','bottom'=>'30','left'=>'40'),
							  'desc'=>__('Určuje, jak daleko bude obsah odsazen od okraje elementu.','cms_ve'),
						),
						array(
							  'id'=>'shadow',
							  'title'=>__('Stín','cms_ve'),
							  'type'=>'shadow',
							  'content'=>array('horizontal'=>'0','vertical'=>'0','size'=>'0','left'=>'10'),
							  'desc'=>__('Pokud je velikost stínu nastavena na 0, pak je element bez stínu.','cms_ve'),
						), */
					[
						'id' => 'shadow',
						'title' => __('Stín', 'cms_ve'),
						'type' => 'select',
						'content' => '',
						'options' => [
							['name' => __('Bez stínu', 'cms_ve'), 'value' => ''],
							['name' => __('Malý stín', 'cms_ve'), 'value' => '5'],
							['name' => __('Základní stín', 'cms_ve'), 'value' => '1'],
							['name' => __('Větší stín', 'cms_ve'), 'value' => '3'],
							['name' => __('Spodní stín', 'cms_ve'), 'value' => '4'],
							['name' => __('Stín vpravo dole', 'cms_ve'), 'value' => '2'],
						],
						'onedit' => [
							'action' => 'change_class',
							'class' => 'mw_element_item_shadow',
							'target' => ' .ve_content_block',
						],
					],
					/*
						array(
							'id'=>'font',
							'title'=>__('Písmo','cms_ve'),
							'type'=>'font',
							'content'=>array(
								'font-size'=>'',
								//'font-family'=>'',
								//'line-height'=>'',
								//'weight'=>'',
								'color'=>'',
							),
							'setting'=>array(
								'max_font_size'=>'20'
							),
							'onedit'=>array(
								'action'=>'change_font',
								'target'=>' .ve_content_block',
							),
						),
						*/
					[
						'id' => 'text',
						'title' => __('Texty', 'cms_ve'),
						'type' => 'select',
						'options' => [
							['name' => __('Automatické', 'cms_ve'), 'value' => 'auto'],
							['name' => __('Tmavé', 'cms_ve'), 'value' => 'default'],
							['name' => __('Světlé', 'cms_ve'), 'value' => 'invers'],
						],
						'content' => 'auto',
						'onedit' => [
							'action' => 'change_class',
							'class' => 'text_',
							'target' => ' .ve_content_block',
						],
					],
				],
			],
			[
				'id' => 'title',
				'name' => __('Nadpis', 'cms_ve'),
				'setting' => [
					[
						'id' => 'title',
						'title' => __('Nadpis bloku', 'cms_ve'),
						'type' => 'text',
						'content' => '',
						'onedit' => [
							'action' => 'add_text',
							'target' => ' .ve_content_block_title',
						],
					],
					[
						'id' => 'title_bg',
						'title' => __('Barva pozadí nadpisu', 'cms_ve'),
						'type' => 'background',
						'content' => [
							'color1' => '',
							'rgba1' => '',
							'transparency' => '1',
						],
						'onedit' => [
							'action' => 'change_smart_background_color',
							'target' => ' .ve_content_block_title',
						],
					],
					[
						'id' => 'title_border',
						'title' => __('Spodní ohraničení nadpisu', 'cms_ve'),
						'type' => 'border',
						'group' => 'input',
						'content' => [
							'size' => '1',
							'color' => '#000000',
							'transparency' => '0.2',
							'rgba' => 'rgba(0,0,0,0.2)',
						],
						'onedit' => [
							'action' => 'change_styles',
							'css' => 'border-bottom',
							'target' => ' .ve_content_block_title',
						],
					],
					[
						'id' => 'title-font',
						'title' => __('Písmo nadpisu', 'cms_ve'),
						'type' => 'font',
						'content' => [
							'font-size' => '20',
							'use-font' => 'title',
							//'font-family'=>'',
							//'line-height'=>'',
							'align' => 'center',
							//'weight'=>'',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '30',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .ve_content_block_title',
						],
					],

				],
			],

		],
	],

	'variable_content' => [
		'name' => __('Předdefinovaný obsah', 'cms_ve'),
		'help' => MW_HELP_URL . 'article/749-element-pro-strukturu-preddefinovany-obsah',
		'description' => __('Pomocí tohoto elementu můžete na stránku vložit předem vytvořený obsah, který lze umístit na více stránek. Změna předdefinovaného obsahu se projeví ve všech jeho umístěních.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'setting',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					/*
						array(
						  'id' => 'contentinfo',
						  'type' => 'info',
						  'content' => __('Obsah je vždy ovlivněn nastavením vzhledu stránky. Pokud tedy stejný obsah umístíte na různě nastavené stránky, může se lišit například písmem, šířkou atd.','cms_ve'),
						), */
					[
						'id' => 'content',
						'title' => __('Obsah', 'cms_ve'),
						'type' => 'weditor',
						'setting' => [
							'post_type' => 've_elvar',
							'install' => 'weditorWithTemplate',
							'texts' => [
								'empty' => __(' - Bez obsahu - ', 'cms_ve'),
								'edit' => __('Upravit vybraný obsah', 'cms_ve'),
								'duplicate' => __('Duplikovat vybraný obsah', 'cms_ve'),
								'create' => __('Vytvořit nový obsah', 'cms_ve'),
								'delete' => __('Smazat vybraný obsah', 'cms_ve'),
							],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
		],
	],

], 'structure');
