<?php
global $vePage, $mwContainer;

$image_lang = get_locale() == 'en_US' ? '_en' : '';

$vePage->add_element_groups([
	'member' => [
		'name' => __('Členská sekce', 'cms_member'),
		'subelement' => true,
	],
]);
$vePage->add_elements([
	'member_login' => [
		'name' => __('Přihlašovací formulář', 'cms_member'),
		'help' => MW_HELP_URL . 'article/752-element-pro-clenskou-sekci-prihlasovaci-formular',
		'description' => __('Formulář pomocí, kterého se mohou návštěvníci přihlašovat do vámi vytvořené členské sekce.', 'cms_member'),
		'tab_setting' => [
			[
				'id' => 'form',
				'name' => __('Obsah', 'cms_member'),
				'setting' => [
					[
						'id' => 'loginto',
						'title' => __('Přihlásit do členské sekce', 'cms_member'),
						'type' => 'selectmember',
						'empty' => ' - ',
						'desc' => __('Pokud nevyberete žádnou členskou sekci, bude se uživatel přihlašovat do členské sekce, do které je zařazena tato stránka. Pokud členskou sekci vyberete, bude se uživatel přihlašovat do vybrané členské sekce.', 'cms_member'),
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_member'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'input-style',
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
								'id' => 'form-font',
								'title' => __('Písmo formulářových polí', 'cms_ve'),
								'type' => 'font',
								'group' => 'input',
								'content' => [
									'font-size' => '',
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
									'target' => ' .ve_content_button',
								],
							],
						],
					],
				],
			],

		],
	],
	'member_regform' => [
		'name' => __('Registrační formulář', 'cms_member'),
		'help' => MW_HELP_URL . 'article/753-element-pro-clenskou-sekci-registracni-formular',
		'description' => __('Formulář pomocí, kterého se mohou návštěvníci zdarma registrovat do členské sekce.', 'cms_member'),
		'tab_setting' => [
			[
				'id' => 'form',
				'name' => __('Obsah', 'cms_member'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'reginto',
								'title' => __('Registrovat do členské sekce:', 'cms_member'),
								'sublabel' => __('Členské úrovně', 'cms_member'),
								'type' => 'selectmember',
								'show_levels' => true,
								'content' => '',
								'empty' => __('- Vyberte členskou sekci -', 'cms_member'),
								'tooltip' => __('Vyberte členskou sekci (popřípadě členskou úroveň), do které se má uživatel registrovat.', 'cms_member'),
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'days',
								'title' => __('Registrovat na X dní', 'cms_member'),
								'type' => 'text',
								'tooltip' => __('Pokud chcete registraci zdarma časově omezit, zadejte na kolik dní se má uživatel registrovat. Po vypršení této doby se přístupy zablokují.', 'cms_member'),
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'redirect',
								'title' => __('Po registraci přesměrovat na', 'cms_member'),
								'type' => 'page_link',
								'target' => false,
								'onedit' => [
									'action' => 'reload',
								],
								'tooltip' => __('Zadejte URL adresu stránky, na kterou chcete uživatele po registraci přesměrovat.', 'cms_member'),
							],
							[
								'id' => 'sendtose',
								'title' => __('Uložit kontakt do seznamu', 'cms_member'),
								'type' => 'list_select',
								'onedit' => [
									'action' => 'reload',
								],
								'tooltip' => __('Po registraci bude kontakt uložen do vybraného email marketingového seznamu.', 'cms_member'),
							],
							[
								'id' => 'sendtomail',
								'title' => __('Informovat o nové registraci na e-mail', 'cms_member'),
								'type' => 'text',
								'tooltip' => __('Zadejte e-mailovou adresu, na kterou chcete zaslat informaci o nové registraci. Pokud e-mail nevyplníte, nebude se informace zasílat.', 'cms_member'),
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'hide',
								'type' => 'multiple_checkbox',
								'options' => [
									['name' => __('Skrýt jméno', 'cms_member'), 'value' => 'name'],
								],
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'generate_password',
								'type' => 'switch',
								'label' => __('Generovat heslo automaticky', 'cms_member'),
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'update',
								'type' => 'switch',
								'label' => __('Povolit i pro existující uživatele', 'cms_member'),
							],
							[
								'id' => 'no_email',
								'type' => 'switch',
								'label' => __('Po registraci nezasílat členům email s přístupy', 'cms_member'),
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'gdpr_info',
								'title' => __('GDPR informační text pod formulářem', 'cms'),
								'content' => __('Vaše osobní údaje budou použity pouze pro účely vytvoření a fungování vašeho účtu zdarma.', 'cms'),
								'type' => 'textarea',
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_field_gdpr_accept span',
								],
							],
							[
								'id' => 'gdpr_link_text',
								'title' => __('Text odkazu', 'cms'),
								'content' => __('Zásady zpracování osobních údajů', 'cms'),
								'type' => 'text',
								'tooltip' => __('Odkaz na zásady zpracování osobních údajů je nutné nastavit v Nastavení -> Nastavení webu -> Ochrana osobních údajů.', 'cms_member'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .mw_field_gdpr_accept a',
								],
							],
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_member'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'input-style',
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
								'id' => 'form-font',
								'title' => __('Písmo formulářových polí', 'cms_ve'),
								'type' => 'font',
								'group' => 'input',
								'content' => [
									'font-size' => '',
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
									'target' => ' .ve_content_button',
								],
							],
						],
					],
				],
			],
		],
	],
	'member_subpages' => [
		'name' => __('Seznam lekcí', 'cms_member'),
		'help' => MW_HELP_URL . 'article/754-element-pro-clenskou-sekci-seznam-lekci',
		'description' => __('Vypíše podstránky aktuální nebo vybrané stránky jako obrázkový seznam s náhledovým obrázkem a popisem stránky. Tento element lze využít například jako seznam lekcí výukového programu.', 'cms_member'),
		'tab_setting' => [
			[
				'id' => 'list',
				'name' => __('Obsah', 'cms_member'),
				'setting' => [
					[
						'id' => 'page',
						'title' => __('Vypsat podstránky od', 'cms_member'),
						'whisperer' => true,
						'type' => 'selectpage',
						'content' => '',
						'tooltip' => __('Vyberte stránku, jejíž podstránky chcete vypsat jako seznam lekcí. Pokud žádnou nevyberete, vypíšou se podstránky aktuální stránky.', 'cms_member'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					/*
						array(
							  'id'=>'setting',
							  'type' => 'multiple_checkbox',
							  'options' => array(
								  array('name' => __('Skrýt počet komentářů','cms_member'), 'value' => 'hide_comments'),
								  array('name' => __('Skrýt popisek','cms_member'), 'value' => 'hide_desc'),
								  array('name' => __('Skrýt obrázek','cms_member'), 'value' => 'hide_image'),
							  ),
							  'onedit'=>array(
								 'action'=>'reload'
							  )
						),*/
					[
						'id' => 'hide_comments',
						'type' => 'switch',
						'label' => __('Skrýt počet komentářů', 'cms_member'),
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
						'id' => 'hide_image',
						'type' => 'switch',
						'label' => __('Skrýt obrázek', 'cms_member'),
						'show_group' => 'style',
						'show_val' => '3,4,4b,6,7,7b',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'hide_progress',
						'type' => 'switch',
						'label' => __('Skrýt ukazatel pokroku', 'cms_member'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'words',
						'title' => __('Maximální počet slov v popisku', 'cms_member'),
						'type' => 'slider',
						'setting' => [
							'min' => '10',
							'max' => '50',
						],
						'content' => '17',
						'onedit' => [
							'action' => 'reload',
						],
						'show_group' => 'hide_desc',
						'show_val' => '0',
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_member'),
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
								'show_val' => '1,2,4,4b,5,7,7b',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'item_style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '4',
								'options' => [
									'3' => VS_DIR . 'images/image_select/item3.jpg',
									'4' => VS_DIR . 'images/image_select/item4.jpg',
									'4b' => VS_DIR . 'images/image_select/item4b.jpg',
									'6' => VS_DIR . 'images/image_select/item6.jpg',
									'7' => VS_DIR . 'images/image_select/item7.jpg',
									'7b' => VS_DIR . 'images/image_select/item7b.jpg',
									'5' => VS_DIR . 'images/image_select/item5.jpg',
									'1' => VS_DIR . 'images/image_select/item1.jpg',
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
								'show_val' => '7,4,7b,4b',
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
									['name' => '1/5', 'value' => '5'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'style',
								'show_val' => '6,7,7b',
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
							[
								'id' => 'progress_color',
								'title' => __('Barva progressbaru', 'cms_ve'),
								'type' => 'color',
								'content' => '#56b616',
								'onedit' => [
									'action' => 'change_style_variable',
									'css' => '--progress-color-',
									'target' => ' .mw_member_page_item_progress',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'font',
								'title' => __('Formátování názvu', 'cms_member'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'use-font' => 'title',
									'color' => '',
								],
								'setting' => [
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
								'title' => __('Formátování komentářů', 'cms_ve'),
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
								'id' => 'default_image',
								'title' => __('Defaultní obrázek', 'cms_member'),
								'type' => 'image_url',
								'onedit' => [
									'action' => 'reload',
								],
								'tooltip' => __('Tento obrázek se bude ve výpisu zobrazovat jako obrázek stránky v případě, že stránka nebude mít nastavený svůj obrázek.', 'cms_member'),
							],
						],
					],
				],
			],
		],
	],
	'member_months_pages' => [
		'name' => __('Měsíční lekce', 'cms_member'),
		'help' => MW_HELP_URL . 'article/927-element-pro-clenskou-sekci-mesicni-lekce',
		'description' => __('Vypíše seznam aktuálních a nadcházejících měsíčních lekcí.', 'cms_member'),
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
								'id' => 'show',
								'title' => __('Vypsat', 'cms_member'),
								'type' => 'select',
								'content' => 'month',
								'options' => [
									['name' => __('Měsíční lekce', 'cms_member'), 'value' => 'month'],
									['name' => __('Aktuální lekci', 'cms_member'), 'value' => 'current'],
									['name' => __('Archiv měsíčních lekcí', 'cms_member'), 'value' => 'archive'],
								],
								'show' => 'use',
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
								'id' => 'hide_desc',
								'type' => 'switch',
								'label' => __('Skrýt popisek', 'cms_member'),
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'hide_desc',
							],
							[
								'id' => 'hide_progress',
								'type' => 'switch',
								'label' => __('Skrýt ukazatel pokroku', 'cms_member'),
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'hide_current',
								'type' => 'switch',
								'label' => __('Skrýt aktuální lekci', 'cms_member'),
								'show_group' => 'use',
								'show_val' => 'month',
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'hide_desc',
						'show_val' => '0',
						'setting' => [
							[
								'id' => 'words',
								'title' => __('Maximální počet slov v popisku', 'cms_member'),
								'type' => 'slider',
								'setting' => [
									'min' => '10',
									'max' => '50',
								],
								'content' => '17',
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
					],
				],

			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_member'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'use',
						'show_val' => 'month,archive',
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
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'use',
						'show_val' => 'month,archive',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Styl', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '4',
								'options' => [
									'3' => VS_DIR . 'images/image_select/item3.jpg',
									'4' => VS_DIR . 'images/image_select/item4.jpg',
									'4b' => VS_DIR . 'images/image_select/item4b.jpg',
									'6' => VS_DIR . 'images/image_select/item6.jpg',
									'7' => VS_DIR . 'images/image_select/item7.jpg',
									'7b' => VS_DIR . 'images/image_select/item7b.jpg',
									'5' => VS_DIR . 'images/image_select/item5.jpg',
									'1' => VS_DIR . 'images/image_select/item1.jpg',
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
								'show_val' => '7,4,7b,4b',
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
								'show_val' => '6,7,7b',
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
						'show_group' => 'use',
						'show_val' => 'month,archive',
						'setting' => [
							[
								'id' => 'title_font',
								'title' => __('Formátování názvu', 'cms_member'),
								'type' => 'font',
								'content' => [
									'use-font' => 'title',
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
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
								'title' => __('Formátování podnadpisu', 'cms_member'),
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
								'title' => __('Formátování popisu', 'cms_member'),
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
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'show_group' => 'use',
						'show_val' => 'current',
						'setting' => [
							[
								'id' => 'current_style',
								'title' => __('Styl', 'cms_member'),
								'type' => 'imageselect',
								'content' => '7',
								'options' => [
									'6' => VS_DIR . 'images/image_select/item6.jpg',
									'7' => VS_DIR . 'images/image_select/item7.jpg',
									'7b' => VS_DIR . 'images/image_select/item7b.jpg',
									'5' => VS_DIR . 'images/image_select/item5.jpg',
									'1' => VS_DIR . 'images/image_select/item1.jpg',
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show' => 'current_style',
							],
							[
								'id' => 'current_background_set',
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
								'show_group' => 'current_style',
								'show_val' => '7,7b',
							],
							[
								'id' => 'current_image_size',
								'title' => __('Velikost obrázku', 'cms_ve'),
								'type' => 'select',
								'content' => '23',
								'options' => [
									['name' => '2/3', 'value' => '23'],
									['name' => '1/2', 'value' => '2'],
									['name' => '1/3', 'value' => '3'],
									['name' => '1/4', 'value' => '4'],
								],
								'onedit' => [
									'action' => 'reload',
								],
								'show_group' => 'current_style',
								'show_val' => '6,7,7b',
							],
							[
								'id' => 'current_hover',
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
								'id' => 'current_image_ratio',
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
						'show_group' => 'use',
						'show_val' => 'current',
						'setting' => [
							[
								'id' => 'current_title_font',
								'title' => __('Formátování názvu stránky', 'cms_member'),
								'type' => 'font',
								'content' => [
									'font-size' => '30',
									'use-font' => 'title',
									'color' => '',
								],
								'setting' => [
									'font_size_placeholder' => '30',
									'max_font_size' => '60',
									'show_group' => 'current_style',
									'show_color' => '6,7,7b',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
								],
							],
							[
								'id' => 'current_font_subtitle',
								'title' => __('Formátování podnadpisu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'color' => '',
								],
								'setting' => [
									'max_font_size' => '20',
									'font_size_placeholder' => '13',
									'show_group' => 'current_style',
									'show_color' => '6,7,7b',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' .mw_element_item_subtitle',
								],
							],
							[
								'id' => 'current_font_description',
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
								'show_group' => 'current_style',
								'show_val' => '6,7,7b',
							],
						],
					],
				],
			],

		],

	],
	'member_checklist' => [
		'name' => __('Seznam úkolů', 'cms_member'),
		'help' => MW_HELP_URL . 'article/756-element-pro-clenskou-sekci-seznam-ukolu',
		'description' => __('Tento element můžete umístit na stránku jako seznam úkolů. Uživatel si pak může odškrtnout ty úkoly, které už splnil.', 'cms_member'),
		'tab_setting' => [
			[
				'id' => 'checklist',
				'name' => __('Obsah', 'cms_member'),
				'setting' => [
					[
						'id' => 'title',
						'title' => __('Nadpis seznamu', 'cms_member'),
						'type' => 'text',
						'content' => '',
						'onedit' => [
							'action' => 'add_text',
							'target' => ' .mw_el_mem_checklist_title',
						],
					],
					[
						'id' => 'use',
						'title' => __('Zobrazit', 'cms_member'),
						'type' => 'select',
						'options' => [
							['name' => __('Seznam úkolů stránky', 'cms_member'), 'value' => 'page'],
							['name' => __('Vlastní seznam úkolů', 'cms_member'), 'value' => 'custom'],
						],
						'content' => 'page',
						'show' => 'use',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'info',
						'title' => '',
						'type' => 'info',
						'content' => __('Použije se seznam úkolů této stránky. Úkoly stránky můžete nastavit v Nastavení stránky -> Členská stránka. Takto vytvořený seznam úkolů se započítává do výsledku postupu v elementu Ukazatel pokroku.', 'cms_member'),
						'show_group' => 'use',
						'show_val' => 'page',
					],
					[
						'id' => 'custom_checklist',
						'type' => 'group',
						'setting' => [
							[
								'id' => 'info',
								'title' => '',
								'type' => 'info',
								'content' => __('Vytvořte si vlastní seznam úkolů. Tento seznam úkolů se však nebude započítávat do výsledku postupu.', 'cms_member'),
							],
							[
								'id' => 'content',
								'type' => 'multielement',
								'name' => __('Seznam úkolů', 'cms_member'),
								'texts' => [
									'add' => __('Přidat úkol', 'cms_member'),
									'empty' => __('Úkol', 'cms_member'),
								],
								'onedit' => [
									'action' => 'reload',
								],
								'setting' => [
									[
										'id' => 'text',
										'title' => __('Text úkolu', 'cms_member'),
										'type' => 'textarea',
										'content' => __('Text úkolu', 'cms_member'),
										'onedit' => [
											'action' => 'change_text',
											'target' => ' .mem_checklist_checkbox_text[qt]',
										],
									],
								],
							],
							[
								'id' => 'checklist',
								'type' => 'id_generator',
							],
						],
						'show_group' => 'use',
						'show_val' => 'custom',
					],
				],
			],
			[
				'id' => 'style',
				'name' => __('Vzhled', 'cms_member'),
				'setting' => [
					[
						'id' => 'checkbox_color',
						'title' => __('Barva splněného úkolu', 'cms_member'),
						'type' => 'color',
						'content' => '#52a303',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--checkbox-color-',
						],
					],
					[
						'name' => __('Zakulacení rohů zaškrtávatka', 'cms_member'),
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
							'class' => 'mw_corners_',
							'target' => ' .mem_checklist_checkbox',
						],
						'content' => 'sharp',
					],
					[
						'id' => 'font',
						'title' => __('Formátování nadpisu', 'cms_member'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'use-font' => 'title',
							'color' => '',
						],
						'setting' => [
							'font_size_placeholder' => '18',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' .mw_el_mem_checklist_title',
						],
					],
					[
						'id' => 'font_text',
						'title' => __('Formátování textu úkolu', 'cms_member'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
						],
						'setting' => [
							'max_font_size' => '20',
						],
						'onedit' => [
							'action' => 'change_font',
							'target' => ' li',
						],
					],
				],
			],
		],
	],
	'member_progress' => [
		'name' => __('Ukazatel pokroku', 'cms_member'),
		'help' => MW_HELP_URL . 'article/757-element-pro-clenskou-sekci-ukazatel-pokroku',
		'description' => __('Ukazatel pokroku zobrazuje, kolik procent úkolů vašich lekcí uživatel splnil. Úkoly vytváříte v Nastavení stránky -> Členská stránka a jejich seznam můžete zobrazit pomocí elementu Seznamu úkolů.', 'cms_member'),
		'tab_setting' => [
			[
				'id' => 'progressbar',
				'name' => __('Obsah', 'cms_member'),
				'setting' => [
					[
						'title' => __('Zobrazit pokrok pro', 'cms_member'),
						'id' => 'show',
						'type' => 'select',
						'show' => 'progressfor',
						'options' => [
							['name' => __('Stránku a její podstránky', 'cms_member'), 'value' => 'page'],
							['name' => __('Celou členskou sekci', 'cms_member'), 'value' => 'member'],
						],
						'onedit' => [
							'action' => 'reload',
						],
						'content' => 'page',
					],
					[
						'id' => 'member',
						'title' => __('Členská sekce', 'cms_member'),
						'type' => 'selectmember',
						'show_group' => 'progressfor',
						'show_val' => 'member',
						'onedit' => [
							'action' => 'reload',
						],
						'tooltip' => __('Do výsledku se budou započítávat seznamy úkolů všech stránek vybrané členské sekce. Pokud nevyberete žádnou členskou sekci, tak se bude brát členská sekce, do které je zařazená aktuální stránka', 'cms_member'),
					],
					[
						'id' => 'page',
						'title' => __('Stránka', 'cms_member'),
						'type' => 'selectpage',
						'content' => '',
						'show_group' => 'progressfor',
						'show_val' => 'page',
						'onedit' => [
							'action' => 'reload',
						],
						'tooltip' => __('Do výsledku se budou započítávat seznamy úkolů všech podstránek od vybrané stránky. Pokud nevyberete žádnou podstránku, tak se budou brát podstránky aktuální stránky.', 'cms_member'),
					],
					[
						'id' => 'text',
						'title' => __('Popisek pokroku', 'cms_member'),
						'type' => 'text',
						'content' => 'Splněno',
						'onedit' => [
							'action' => 'add_text',
							'target' => ' .ve_progressbar_text',
						],
					],
				],
			],
			$mwContainer->elements['progressbar']['tab_setting'][1],
		],
	],
	'member_news' => [
		'name' => __('Členské novinky', 'cms_member'),
		'help' => MW_HELP_URL . 'article/758-element-pro-clenskou-sekci-clenske-novinky',
		'description' => __('Pomocí tohoto elementu můžete vypsat všechny nebo i jen několik posledních členských novinek.', 'cms_member'),
		'tab_setting' => [
			[
				'id' => 'news',
				'name' => __('Obsah', 'cms_member'),
				'setting' => [
					[
						'id' => 'type',
						'title' => __('Typ výpisu', 'cms_member'),
						'type' => 'select',
						'show' => 'type',
						'content' => 'all',
						'options' => [
							['name' => __('Všechny novinky', 'cms_member'), 'value' => 'all'],
							['name' => __('Poslední novinky', 'cms_member'), 'value' => 'last'],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],

					[
						'id' => 'last_group',
						'type' => 'group',
						'setting' => [
							[
								'id' => 'number_news',
								'title' => __('Počet novinek', 'cms_member'),
								'type' => 'text',
								'content' => '3',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'words_last',
								'title' => __('Počet slov v náhledu novinky', 'cms_member'),
								'type' => 'text',
								'content' => '25',
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
						'show_group' => 'type',
						'show_val' => 'last',
					],
					[
						'id' => 'all_group',
						'type' => 'group',
						'setting' => [
							[
								'id' => 'per_page',
								'title' => __('Počet novinek na stránku', 'cms_member'),
								'type' => 'text',
								'content' => '12',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'words_all',
								'title' => __('Počet slov v náhledu novinky', 'cms_member'),
								'type' => 'text',
								'content' => '30',
								'onedit' => [
									'action' => 'reload',
								],
							],
						],
						'show_group' => 'type',
						'show_val' => 'all',
					],

				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_member'),
				'setting' => [
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
									'3' => VS_DIR . 'images/image_select/item3c.jpg',
									'4' => VS_DIR . 'images/image_select/item1.jpg',
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
								'show_val' => '7,4',
							],
						],
					],
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
									['name' => __('Automaticky', 'cms_member'), 'value' => 0],
									['name' => '1', 'value' => 1],
									['name' => '2', 'value' => 2],
									['name' => '3', 'value' => 3],
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
								'id' => 'font_title',
								'title' => __('Formátování názvu', 'cms_ve'),
								'type' => 'font',
								'content' => [
									'font-size' => '',
									'use-font' => 'title',
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
								'id' => 'font',
								'title' => __('Formátování textu', 'cms_ve'),
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
						],
					],

				],
			],
		],
	],
	'member_users' => [
		'name' => __('Katalog členů', 'cms_member'),
		'help' => MW_HELP_URL . 'article/759-element-pro-clenskou-sekci-katalog-clenu',
		'description' => __('Vypíše seznam členů vybrané členské sekce.', 'cms_member'),
		'tab_setting' => [
			[
				'id' => 'user_list',
				'name' => __('Obsah', 'cms_member'),
				'setting' => [
					[
						'id' => 'title',
						'title' => __('Nadpis katalogu', 'cms_member'),
						'type' => 'text',
						'content' => __('Katalog členů', 'cms_member'),
						'onedit' => [
							'action' => 'change_text',
							'target' => ' .mem_member_list_title',
						],
					],
					[
						'id' => 'show',
						'title' => __('Vypisovat', 'cms_member'),
						'type' => 'select',
						'show' => 'show_users',
						'content' => '1',
						'options' => [
							['name' => __('Všechny členy', 'cms_member'), 'value' => '1'],
							['name' => __('Jen vybrané členy', 'cms_member'), 'value' => '2'],
						],
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'name' => __('Vypsat uživatele z', 'cms_member'),
						'sublabel' => __('Členské úrovně', 'cms_member'),
						'id' => 'member_section',
						'type' => 'selectmember',
						'show_levels' => true,
						'show_group' => 'show_users',
						'show_val' => '2',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'per_page',
						'title' => __('Počet členů na stránku', 'cms_member'),
						'type' => 'text',
						'content' => '15',
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_member'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'style',
								'title' => __('Vzhled', 'cms_member'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/peoples4.jpg',
									'2' => VS_DIR . 'images/image_select/peoples1.jpg',
									'4' => VS_DIR . 'images/image_select/peoples6.jpg',
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
								'show_val' => '4',
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
							],
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
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'font_title',
								'title' => __('Font jména', 'cms_ve'),
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
						],

					],


				],
			],
		],
	],
	'members_list' => [
		'name' => __('Seznam členských sekcí', 'cms_member'),
		'help' => MW_HELP_URL . 'article/760-element-pro-clenskou-sekci-seznam-clenskych-sekci',
		'description' => __('Vypíše seznam členských sekcí s proklikem.', 'cms_member'),
		'tab_setting' => [
			[
				'id' => 'list',
				'name' => __('Obsah', 'cms_member'),
				'setting' => [
					[
						'id' => 'members',
						'type' => 'multielement',
						'texts' => [
							'add' => __('Přidat členskou sekci', 'cms_member'),
							'empty' => __('(Členská sekce)', 'cms_member'),
						],
						'title_function' => 'MwMemberFields::memberSectionsListItemHead',
						'onedit' => [
							'action' => 'reload',
						],
						'setting' => [
							[
								'id' => 'member',
								'title' => __('Členská sekce', 'cms_member'),
								'type' => 'selectmember',
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'image',
								'title' => __('Obrázek', 'cms_member'),
								'type' => 'image',
								'onedit' => [
									'action' => 'change_img',
									'target' => ' .mw_element_item[qt] img',
								],
							],
							[
								'id' => 'title',
								'title' => __('Název členské sekce', 'cms_member'),
								'type' => 'text',
								'onedit' => [
									'action' => 'change_text',
									'target' => ' .mw_element_item[qt] h3',
								],
							],
							[
								'id' => 'description',
								'title' => __('Prodejní popis', 'cms_member'),
								'type' => 'textarea',
								'tooltip' => __('Pokud nemá uživatel do této členské sekce přístup a klikne na ni, otevře se mu popup s tímto popisem a tlačítkem odkazujícím na stránku, kde může přístup získat.', 'cms_member'),
							],
							[
								'id' => 'link',
								'title' => __('Odkaz na prodejní stránku', 'cms_member'),
								'type' => 'page_link',
								'onedit' => [
									'action' => 'reload',
								],
								'tooltip' => __('Na stránce kterou zde nastavíte by měl uživatel mít možnost získat přístup do této členské sekce a to buď nákupem nebo registrací zdarma.', 'cms_member'),
							],
						],
					],
					[
						'id' => 'show_progress',
						'type' => 'switch',
						'label' => __('Zobrazit ukazatel pokroku', 'cms_member'),
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_member'),
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
								'show_val' => '4,5,1',
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
									'5' => VS_DIR . 'images/image_select/item5.jpg',
									'1' => VS_DIR . 'images/image_select/item1.jpg',

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
								'show_val' => '4',
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
								'show_val' => '3,4,5',
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
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' h3',
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
								'title' => __('Styl tlačítka v popupu', 'cms_member'),
								'type' => 'button',
								'tooltip' => __('Styl tlačítka které se zobrazuje v popupu u členských sekcí, do kterých nemá návštěvník přístup.', 'cms_member'),
							],
						],
					],

				],
			],

		],
	],
], 'member');
