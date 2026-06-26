<?php
global $vePage;

$vePage->add_elements([
	'recent_posts' => [
		'name' => __('Články blogu', 'cms_blog'),
		'help' => MW_HELP_URL . 'article/822-element-clanky-blogu',
		'description' => __('Výpis posledních nebo nejčtenějších článků z blogu', 'cms_blog'),
		'tab_setting' => [
			[
				'id' => 'setting',
				'name' => __('Nastavení výpisu článků', 'cms_blog'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'type',
								'title' => __('Vypsat', 'cms_blog'),
								'options' => [
									['name' => 'Poslední články', 'value' => 'last_posts'],
									['name' => 'Nejčtenější články', 'value' => 'most_viewed_posts'],
								],
								'type' => 'select',
								'content' => 'last_posts',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'category',
								'title' => __('Vypisovat články z kategorie', 'cms_blog'),
								'type' => 'category_select',
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
								'id' => 'number',
								'title' => __('Počet článků', 'cms_blog'),
								'type' => 'text',
								'content' => 3,
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'excerpt_words',
								'title' => __('Počet slov v popisku', 'cms_blog'),
								'type' => 'text',
								'content' => 17,
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
								'id' => 'show',
								'title' => __('Zobrazení', 'cms_blog'),
								'type' => 'multiple_checkbox',
								'options' => [
									['name' => __('Skrýt popisek', 'cms_blog'), 'value' => 'excerpt'],
									['name' => __('Skrýt odkaz na článek', 'cms_blog'), 'value' => 'more'],
									['name' => __('Skrýt obrázek', 'cms_blog'), 'value' => 'images'],
								],
								'content' => [
									'excerpt' => '1',
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
								'id' => 'but_text',
								'title' => __('Text odkazu na článek', 'cms_blog'),
								'type' => 'text',
								'content' => __('Celý článek', 'cms_blog'),
							],
						],
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_blog'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'style',
						'show_val' => '1,2,3,5,6,7',
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
								'show_val' => '2,7,6,5',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Struktura', 'cms_blog'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/item3.jpg',
									'2' => VS_DIR . 'images/image_select/item4.jpg',
									'6' => VS_DIR . 'images/image_select/item1.jpg',
									'5' => VS_DIR . 'images/image_select/item5.jpg',
									'3' => VS_DIR . 'images/image_select/item6.jpg',
									'7' => VS_DIR . 'images/image_select/item7.jpg',
									'4' => VS_DIR . 'images/image_select/blog4.jpg',
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'style',
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
								'show_val' => '2,7',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'style',
						'show_val' => '1,2,3,5,6,7',
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
								'show_val' => '3,7',
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
						'show_group' => 'style',
						'show_val' => '1,2,3,5,6,7',
						'setting' => [
							[
								'id' => 'font',
								'title' => __('Formátování nadpisu', 'cms_blog'),
								'type' => 'font',
								'content' => [
									'font-size' => '20',
									'use-font' => 'title',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '50',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
								],
							],
							[
								'id' => 'font_text',
								'title' => __('Formátování textu', 'cms_blog'),
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
								'show_val' => '1,3,5,7',
							],
						],
					],
				],
			],
		],
	],
], 'basic');
