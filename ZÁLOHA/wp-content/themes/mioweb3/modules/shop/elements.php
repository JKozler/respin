<?php
global $vePage;

$vePage->add_element_groups([
	'eshop' => [
		'name' => __('Eshop', 'mwshop'),
		'subelement' => true,
	],
]);
$elementsList = [];
if (MWS()->isCreated()) {
	$defaultGw = MWS()->gateways()->getDefault();

	$elementsList['pay_button'] = [
		'name' => __('Tlačítko koupit', 'mwshop'),
		'help' => MW_HELP_URL . 'article/951-element-pro-eshop-tlacitko-koupit',
		'description' => __('Vyberte si z několika typů tlačítek a přizpůsobte ho barevně podle svých představ.', 'mwshop'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_ve'),
				'setting' => [
					[
						'id' => 'kind',
						'title' => __('Typ tlačítka', 'mwshop'),
						'type' => 'select',
						'options' => [
							['name' => __('Koupit', 'mwshop'), 'value' => 'quick'],
							['name' => __('Vložit do košíku', 'mwshop'), 'value' => 'cart'],
						],
						'content' => 'quick',
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'kind',
						'desc' => __('Typ "Koupit" umožní přímý nákup zboží bez nutnosti vkládat zboží do košíku a projít celým objednávkovým procesem.', 'mwshop'),
					],
					[
						'id' => 'product_id',
						'title' => __('Produkt', 'mwshop'),
						'type' => 'product_select',
						'empty_text' => __('- vyberte produkt -', 'mwshop'),
						'edit_button' => true,
						'only_published' => true,
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'thx_page',
						'title' => __('Děkovací stránka', 'mwshop'),
						'type' => 'selectpage',
						'whisperer' => true,
						'show_group' => 'kind',
						'show_val' => 'quick',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'content',
						'title' => __('Text tlačítka', 'mwshop'),
						'type' => 'text',
						'content' => __('Koupit', 'mwshop'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .ve_content_button .ve_but_text',
						],
					],
					[
						'id' => 'allow_discount_codes',
						'type' => 'switch',
						'label' => __('Umožnit uplatnit slevové kódy', 'mwshop'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'name' => '',
						'id' => 'allow_simply_form',
						'type' => 'switch',
						'label' => __('Zjednodušený formulář', 'mwshop'),
						'desc' => __('V prodejním formuláři nebude klient zadávat fakturační údaje. Na faktuře nebude uveden odběratel. Aby se zobrazil zjednodušený prodejní formulář, nesmí prodávaný produkt vyžadovat dopravu a maximální cena objednávky nemůže přesáhnout 10 000 Kč.', 'mwshop'),
						'show' => 'simply_form',
						'hide_field' => MWS()->getEshopCountry() !== MwsCountry::CZ || $defaultGw->getId() !== 'mioweb',
						'show_group' => 'kind',
						'show_val' => 'quick',
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
							'class' => 've_content_button_',
							'target' => ' .ve_content_button',
						],
						'content' => 'center',
					],
					//                        array(
					//                            'id' => 'count_default',
					//                            'title' => __('Výchozí počet kusů', 'cms_ve'),
					//                            'type' => 'number',
					//                            'content' => 1,
					//                        ),
					//                        array(
					//                            'id' => 'count_enable',
					//                            'label' => __('Zákazník může určit počet kusů', 'cms_ve'),
					//                            'type' => 'checkbox',
					//                            'content' => false,
					//                        ),
				],
			],
			[
				'id' => 'format',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'id' => 'button',
						'title' => __('Vzhled tlačítka', 'cms_ve'),
						'type' => 'button',
						'onedit' => [
							'action' => 'change_button',
							'target' => ' .ve_content_button',
						],
					],
				],
			],

		],
	];
	$elementsList['product_list'] = [
		'name' => __('Výpis produktů', 'mwshop'),
		'help' => MW_HELP_URL . 'article/953-element-pro-eshop-vypis-produktu',
		'description' => __('Vypíše buď všechny produkty, vybrané produkty, nejprodávanější produkty nebo produkty určité kategorie.', 'mwshop'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Výpis', 'mwshop'),
				'setting' => [
					[
						'id' => 'show',
						'title' => __('Vypsat', 'mwshop'),
						'type' => 'select',
						'options' => [
							['name' => __('Vybrané produkty', 'mwshop'), 'value' => 'custom'],
							['name' => __('Nejprodávanější produkty', 'mwshop'), 'value' => 'bestsellers'],
							['name' => __('Produkty z kategorie', 'mwshop'), 'value' => 'category'],
							['name' => __('Všechny produkty', 'mwshop'), 'value' => 'all'],
						],
						'content' => 'custom',
						'show' => 'show_product',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'custom_products',
						'type' => 'multielement',
						'onedit' => [
							'action' => 'reload',
						],
						'texts' => [
							'add' => __('Přidat produkt', 'mwshop'),
						],
						'setting' => [
							[
								'id' => 'product_id',
								'title' => __('Produkt', 'mwshop'),
								'type' => 'product_select',
								'edit_button' => true,
								'empty_text' => __('- vyberte produkt -', 'mwshop'),
								'only_published' => true,
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
						'show_group' => 'show_product',
						'show_val' => 'custom',
					],
					[
						'id' => 'category',
						'title' => __('Kategorie produktu', 'mwshop'),
						'type' => 'term_select',
						'term_id' => MWS_PRODUCT_CAT_SLUG,
						'empty_text' => __('- vyberte kategorii -', 'mwshop'),
						'show_group' => 'show_product',
						'show_val' => 'category',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'bestsellers_count',
						'title' => __('Počet produktů', 'mwshop'),
						'type' => 'text',
						'content' => '3',
						'show_group' => 'show_product',
						'show_val' => 'bestsellers',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'order',
						'title' => __('Řadit zboží podle', 'mwshop'),
						'type' => 'select',
						'content' => 'date',
						'options' => [
							['name' => __('Data vytvoření', 'mwshop'), 'value' => 'date'],
							['name' => __('Názvu', 'mwshop'), 'value' => 'title'],
							['name' => __('Vlastního řazení', 'mwshop'), 'value' => 'menu_order'],
							['name' => __('Nejprodávanější', 'mwshop'), 'value' => 'bestseller'],
						],
						'desc' => __('Pořadí pro vlastní řazení se určuje podle hodnoty "Pořadí" v nastavení každého produktu.', 'mwshop'),
						'show_group' => 'show_product',
						'show_val' => 'category,all',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'hide_desc',
						'type' => 'switch',
						'label' => __('Skrýt popisek', 'cms_member'),
						'onedit' => [
							'action' => 'reload',
						],
						'show' => 'hide_desc',
					],
					[
						'id' => 'excerpt_length',
						'title' => __('Maximální počet slov v popisku', 'cms_member'),
						'type' => 'slider',
						'setting' => [
							'min' => '10',
							'max' => '50',
						],
						'tooltip' => __('Pokud nezadáte žádnou hodnotu, použije se počet slov z nastavení eshopu', 'mwshop'),
						'content' => '',
						'onedit' => [
							'action' => 'reload',
						],
						'show_group' => 'hide_desc',
						'show_val' => '0',
					],
					[
						'id' => 'hide_buy',
						'type' => 'switch',
						'label' => __('Skrýt tlačítko koupit', 'cms_member'),
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
			[
				'id' => 'format',
				'name' => __('Vzhled', 'mwshop'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'cols',
								'title' => __('Počet sloupců', 'cms_ve'),
								'type' => 'select',
								'content' => 3,
								'options' => [
									['name' => '1', 'value' => 1],
									['name' => '2', 'value' => 2],
									['name' => '3', 'value' => 3],
									['name' => '4', 'value' => 4],
									['name' => '5', 'value' => 5],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'style',
								'show_val' => '4,4b,3',
							],
							[
								'id' => 'cols_type',
								'title' => __('Mezery mezi sloupci', 'cms_ve'),
								'type' => 'select',
								'content' => 'cols',
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
								'show_val' => '4,4b,7,7b',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'product_style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '4b',
								'options' => [
									'4b' => VS_DIR . 'images/image_select/item4b.jpg',
									'3' => VS_DIR . 'images/image_select/item3.jpg',
									'4' => VS_DIR . 'images/image_select/item4.jpg',
									'7b' => VS_DIR . 'images/image_select/item7b.jpg',
									'6' => VS_DIR . 'images/image_select/item6.jpg',
									'7' => VS_DIR . 'images/image_select/item7.jpg',
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
								'id' => 'image_ratio',
								'title' => __('Zobrazit obrázky v poměru:', 'cms_ve'),
								'type' => 'select',
								'content' => '43',
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
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
								],
							],
							[
								'id' => 'font_description',
								'title' => __('Font popisu', 'cms_ve'),
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
							],
							[
								'id' => 'font_price',
								'title' => __('Font ceny', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'text',
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
									'font_size_placeholder' => '17',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mws_price_vatincluded',
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
	];
	$elementsList['product_detail'] = [
		'name' => __('Detail produktu', 'mwshop'),
		'help' => MW_HELP_URL . 'article/954-element-pro-eshop-detail-produktu',
		'description' => __('Vypíše detail produktu', 'mwshop'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'mwshop'),
				'setting' => [
					[
						'id' => 'product_id',
						'title' => __('Vypsat detail produktu', 'mwshop'),
						'type' => 'product_select',
						'empty_text' => __('- vyberte produkt -', 'mwshop'),
						'only_published' => true,
						'edit_button' => true,
						'content' => '',
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
		],
	];
	$elementsList['eshop_category_list'] = [
		'name' => __('Kategorie eshopu', 'mwshop'),
		'help' => MW_HELP_URL . 'article/955-element-pro-eshop-kategorie-eshopu',
		'description' => __('Vypíše menu s kategoriemi eshopu.', 'mwshop'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Seznam kategorií', 'mwshop'),
				'setting' => [
					[
						'id' => 'show',
						'title' => __('Zobrazit', 'mwshop'),
						'type' => 'select',
						'show' => 'show_cat',
						'options' => [
							['name' => __('Všechny kategorie', 'mwshop'), 'value' => 'all'],
							['name' => __('Pouze podkategorie od', 'mwshop'), 'value' => 'sub'],
						],
						'onedit' => [
							'action' => 'reload',
						],
						'content' => 'all',
					],
					[
						'id' => 'category_parent',
						'title' => __('Zobrazit podkategorie od', 'mwshop'),
						'type' => 'term_select',
						'term_id' => MWS_PRODUCT_CAT_SLUG,
						'empty_text' => __('- vyberte kategorii -', 'mwshop'),
						'show_group' => 'show_cat',
						'show_val' => 'sub',
						'onedit' => [
							'action' => 'reload',
						],
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
						'show_group' => 'style',
						'show_val' => '1,1b,2,3,4,4b,5,6,7,7b',
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
								'show_val' => '1,1b,2,4,4b,5,7,7b',
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
									'1' => VS_DIR . 'images/image_select/item1.jpg',
									'1b' => VS_DIR . 'images/image_select/gallery1.jpg',
									'5' => VS_DIR . 'images/image_select/item5.jpg',
									'2' => VS_DIR . 'images/image_select/item2.jpg',
									'3' => VS_DIR . 'images/image_select/item3.jpg',
									'4' => VS_DIR . 'images/image_select/item4.jpg',
									'4b' => VS_DIR . 'images/image_select/item4b.jpg',
									/*
									'6' => VS_DIR.'images/image_select/item6.jpg',
									'7' => VS_DIR.'images/image_select/item7.jpg',
									'7b' => VS_DIR.'images/image_select/item7b.jpg',*/
									'v1' => VS_DIR . 'images/image_select/vmenu1.jpg',

								],
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'style',
							],
							[
								'id' => 'vmenu_font',
								'title' => __('Font menu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'use-font' => 'text',
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '30',
									//'font_size_placeholder' => '20',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' li',
								],
								'show_group' => 'style',
								'show_val' => 'v1',
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
								'show_val' => '7,4,7b,4b',
							],
							/*
							array(
								'id'=>'image_size',
								'title'=>__('Velikost obrázku','cms_ve'),
								'type'=>'select',
								'content'=> '2',
								'options' => array(
									array('name' => '1/2', 'value' => '2'),
									array('name' => '1/3', 'value' => '3'),
									array('name' => '1/4', 'value' => '4'),
								),
								'onedit' => array(
									'action' => 'reload',
								),
								'show_group' => 'style',
								'show_val' => '6,7,7b'
							),*/
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
								'show_group' => 'style',
								'show_val' => '1,2,3,4,4b,5,6,7,7b',
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
								'show_val' => '1,2,3,4,4b,5,6,7,7b',
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
								'show_val' => '2,3,4,5',
								'content' => 'left',
							],
							[
								'id' => 'font',
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
									'show_color' => '3,4,4b',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
								],
							],
						],

					],

				],
			],

		],
	];
}
$elementsList['product_price'] = [
	'name' => __('Cena produktu', 'mwshop'),
	'help' => MW_HELP_URL . 'article/956-element-pro-eshop-cena-produktu',
	'description' => __('Zobrazte vždy aktuální cenu produktu.', 'mwshop'),
	'tab_setting' => [
		[
			'id' => 'content',
			'name' => __('Obsah', 'mwshop'),
			'setting' => [
				[
					'id' => 'product_id',
					'title' => __('Zobrazit cenu produktu', 'mwshop'),
					'type' => 'product_select',
					'empty_text' => __('- vyberte produkt -', 'mwshop'),
					'only_published' => true,
					'edit_button' => true,
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
				[
					'id' => 'hide',
					'type' => 'multiple_checkbox',
					'options' => [
						['name' => __('Skrýt původní cenu', 'mwshop'), 'value' => 'salePrice'],
						['name' => __('Skrýt výši slevy', 'mwshop'), 'value' => 'discount'],
						['name' => __('Skrýt cenu bez DPH', 'mwshop'), 'value' => 'vatExcluded'],
					],
					'onedit' => [
						'action' => 'reload',
					],
				],
			],
		],
		[
			'id' => 'format',
			'name' => __('Vzhled', 'mwshop'),
			'setting' => [
				[
					'id' => 'font',
					'title' => __('Font', 'mwshop'),
					'type' => 'font',
					'content' => [
						'font-size' => '32',
						'use-font' => 'text',
						'color' => '',
					],
					'setting' => [
						'max_font_size' => '60',
					],
					'onedit' => [
						'action' => 'change_font',
						'target' => ' .mws_price_vatincluded',
					],
				],

			],
		],

	],
];

$vePage->add_elements($elementsList, 'eshop');

$vePage->add_element_groups([
	'upsell' => [
		'name' => __('Upsell', 'mwshop'),
		'subelement' => true,
	],
], true);
$vePage->add_elements([
	'upsell' => [
		'name' => __('Upsell tlačítka', 'mwshop'),
		'element_icon' => 'button',
//		'help' => MW_HELP_URL . 'article/',
		'description' => '',
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'mwshop'),
				'setting' => [
					[
						'id' => 'yes_text',
						'title' => __('Text tlačítka pro potvrzení', 'mwshop'),
						'type' => 'text',
						'content' => __('Ano, koupit', 'mwshop'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .mws_upsell_button_yes .ve_but_text',
						],
					],
					[
						'id' => 'yes_subtext',
						'title' => __('Podtext tlačítka pro potvrzení', 'mwshop'),
						'type' => 'text',
						'content' => '',
						'onedit' => [
							'action' => 'add_text',
							'target' => ' .mws_upsell_button_yes .ve_button_subtext',
						],
					],
					[
						'id' => 'no_text',
						'title' => __('Text tlačítka pro odmítnutí', 'mwshop'),
						'type' => 'text',
						'content' => __('Ne, děkuji', 'mwshop'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .mws_upsell_button_no .ve_but_text',
						],
					],
					[
						'id' => 'appearance',
						'title' => __('Zobrazení tlačítek', 'mwshop'),
						'type' => 'select',
						'content' => 'next',
						'options' => [
							['name' => __('Vedle sebe', 'mwshop'), 'value' => 'next'],
							['name' => __('Pod sebou', 'mwshop'), 'value' => 'under'],
							['name' => __('Pod sebou přes celou šířku', 'mwshop'), 'value' => 'under_full'],
						],
						'onedit' => [
							'action' => 'change_class',
							'class' => 'in_element_upsell_buttons_app_',
							'target' => ' .in_element_mws_upsell_buttons',
						],
						'show' => 'appearance',
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
							'target' => ' .mws_upsell_buttons_container',
						],
						'content' => 'center',
						'show_group' => 'appearance',
						'show_val' => 'next,under',
					],
				],
			],
			[
				'id' => 'format',
				'name' => __('Vzhled', 'mwshop'),
				'setting' => [
					[
						'id' => 'button_yes',
						'title' => __('Styl tlačítka', 'mwshop'),
						'type' => 'button',
						'content' => ['style' => 'basic'],
						'onedit' => [
							'action' => 'change_button',
							'target' => ' .mws_upsell_button',
						],
					],
				],
			],

		],
	],
	'upsell_price' => [
		'name' => __('Cena upsellu', 'mwshop'),
		'element_icon' => 'product_price',
		//'help' => MW_HELP_URL . 'article/956-element-pro-eshop-cena-produktu',
		'description' => __('Zobrazte vždy aktuální cenu upsellu.', 'mwshop'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'mwshop'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'price_before',
								'title' => __('Text před cenou', 'mwshop'),
								'type' => 'text',
								'content' => __('Nyní jen za', 'mwshop'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mws_upsell_price_before',
								],
							],
							[
								'id' => 'price_after',
								'title' => __('Text za cenou', 'mwshop'),
								'type' => 'text',
								'content' => '',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mws_upsell_price_after',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							/*
							[
								'id' => 'hide_sale',
								'type' => 'switch',
								'label' => __('Skrýt původní cenu', 'mwshop'),
								'onedit' => [
									'action' => 'reload'
								],
							],*/
							[
								'id' => 'sale_before',
								'title' => __('Text před původní cenou', 'mwshop'),
								'type' => 'text',
								'content' => __('Běžná cena:', 'mwshop'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mws_original_upsell_price_before',
								],
							],
							[
								'id' => 'sale_after',
								'title' => __('Text za původní cenou', 'mwshop'),
								'type' => 'text',
								'content' => '',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mws_original_upsell_price_after',
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
									'target' => ' .in_element_content',
									'class' => 've_',
								],
								'content' => 'center',
							],
						],
					],
				],
			],
			[
				'id' => 'format',
				'name' => __('Vzhled', 'mwshop'),
				'setting' => [
					[
						'id' => 'price_font',
						'title' => __('Font řádku s cenou', 'mwshop'),
						'type' => 'font',
						'content' => [
							'font-size' => '30',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '60',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .mws_upsell_price_container',
						],
					],
					[
						'id' => 'price_color',
						'title' => __('Barva ceny', 'mwshop'),
						'type' => 'color',
						'content' => '',
						'onedit' => [
							'action' => 'change_css',
							'css' => 'color',
							'target' => ' .mws_upsell_price',
						],
					],
					[
						'id' => 'sale_font',
						'title' => __('Font řádku s původní cenou', 'mwshop'),
						'type' => 'font',
						'content' => [
							'font-size' => '35',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '60',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .mws_original_upsell_price_container',
						],
					],
				],
			],

		],
	],
	'upsell_image' => [
		'name' => __('Obrázek upsellu', 'mwshop'),
		//'help' => MW_HELP_URL . 'article/956-element-pro-eshop-cena-produktu',
		'description' => '',
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
								'id' => 'upsell-max-width',
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
									'css' => '--upsell-image-width-',
								],
								'content' => '',
							],
							[
								'name' => __('Zarovnání obrázku', 'cms_ve'),
								'id' => 'upsell_image_align',
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
									'target' => ' .in_element_upsell_image',
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
								'id' => 'hide_sale',
								'type' => 'switch',
								'label' => __('Skrýt procentuální slevu', 'mwshop'),
								'onedit' => [
									'action' => 'reload',
								],
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
], 'upsell');
