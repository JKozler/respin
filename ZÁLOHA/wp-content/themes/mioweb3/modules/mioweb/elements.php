<?php
global $vePage, $mwContainer;

$mwContainer->elements['countdown']['tab_setting'][0]['setting'][0]['options'][] = ['name' => __('Od vstupu do kampaně', 'cms_mioweb'), 'value' => 'campaign'];

$vePage->add_element_groups([
	'mioweb' => [
		'name' => __('Kampaně', 'cms_mioweb'),
		'subelement' => true,
	],
]);

$vePage->add_elements([
	'mioweb_nav' => [
		'name' => __('Navigace kampaně', 'cms_mioweb'),
		'help' => MW_HELP_URL . 'article/750-element-pro-kampane-navigace-kampane',
		'description' => __('Vypíše seznam stránek s obsahem zdarma vybrané kampaně. Slouží jako navigace mezi těmito stránkami.', 'cms_mioweb'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_mioweb'),
				'setting' => [
					[
						'id' => 'campaign',
						'content' => '',
						'title' => __('Navigace kampaně', 'cms_mioweb'),
						'type' => 'selectcampaign',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'show_info',
						'label' => __('Zobrazit info o zveřejnění', 'cms_mioweb'),
						'type' => 'switch',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'show_play',
						'label' => __('Zobrazit ikonu Play', 'cms_mioweb'),
						'type' => 'switch',
						'onedit' => [
							'action' => 'reload',
						],
					],
				],
			],
			[
				'id' => 'appearance',
				'name' => __('Vzhled', 'cms_mioweb'),
				'setting' => [
					[
						'id' => 'style',
						'title' => __('Vzhled navigace', 'cms_mioweb'),
						'type' => 'imageselect',
						'content' => '2',
						'options' => [
							'1' => MIOWEB_DIR . 'images/image_select/mionav1.jpg',
							'2' => MIOWEB_DIR . 'images/image_select/mionav2.jpg',
							'3' => MIOWEB_DIR . 'images/image_select/mionav3.jpg',
							'4' => MIOWEB_DIR . 'images/image_select/mionav4.jpg',
							'5' => MIOWEB_DIR . 'images/image_select/mionav5.jpg',
							'6' => MIOWEB_DIR . 'images/image_select/mionav6.jpg',
						],
						'show' => 'appearance',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'background-color',
						'title' => __('Barva pozadí menu', 'cms_mioweb'),
						'type' => 'color',
						'content' => '#ffffff',
						'show_group' => 'appearance',
						'show_val' => '6',
						'onedit' => [
							'action' => 'change_smart_background_color',
							'css' => 'background-color',
							'target' => ' ul',
						],
					],
					[
						'id' => 'color-active',
						'title' => __('Barva aktivní položky', 'cms_mioweb'),
						'type' => 'color',
						'content' => '',
						'onedit' => [
							'action' => 'change_style_variable',
							'css' => '--campaign-nav-color-',
							'class' => '_hover_color',
							'target' => ' ul',
						],
					],
					[
						'id' => 'font',
						'title' => __('Font navigace', 'cms_mioweb'),
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
							'target' => ' .mioweb_campaign_menu li',
						],
					],
				],
			],
		],
	],
	'se_count' => [
		'name' => __('Počet stažení / koupení', 'cms_mioweb'),
		'help' => MW_HELP_URL . 'article/751-element-pro-kampane---pocet-stazeni-koupeni',
		'description' => __('Vypíše kontakty z vybraného seznamu ze SmartEmailingu. Můžete tak vypsat informaci o tom, kolik lidí si stáhlo váš ebook nebo koupilo váš produkt (pokud je ukládáte do SmartEmailingu).', 'cms_mioweb'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_mioweb'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'list',
								'title' => __('Načíst počet ze seznamu', 'cms_mioweb'),
								'type' => 'list_select',
								'hide_purposes' => true,
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
								'class' => ' mail_api_switch',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'text1',
								'title' => __('Text před číslem', 'cms_mioweb'),
								'type' => 'text',
								'content' => __('Tento ebook si stáhlo již', 'cms_mioweb'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .ve_download_count_text_before',
								],
							],
							[
								'id' => 'text2',
								'title' => __('Text za číslem', 'cms_mioweb'),
								'type' => 'text',
								'content' => __('lidí', 'cms_mioweb'),
								'onedit' => [
									'action' => 'add_text',
									'target' => ' .ve_download_count_text_after',
								],
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'limit',
								'title' => __('Odečítat počet kontaktů od čísla', 'cms_mioweb'),
								'type' => 'text',
								'tooltip' => __('Počet kontaktů z vybraného seznamu se bude odečítat od tohoto čísla. Pokud zde zadáte například číslo 100 a v seznamu bude 20 kontaktů, tak se vypíše číslice 80. Pokud zde žádné číslo nezadáte, bude se zobrazovat klasicky počet kontaktů ze seznamu.', 'cms_mioweb'),
								'onedit' => [
									'action' => 'reload',
								],
							],
							[
								'id' => 'limit_redirect',
								'title' => __('Po vynulování přesměrovat na', 'cms_mioweb'),
								'type' => 'page_link',
								'target' => false,
							],
						],
					],
				],
			],
			[
				'id' => 'look',
				'name' => __('Vzhled', 'cms_mioweb'),
				'setting' => [
					[
						'id' => 'font',
						'title' => __('Písmo', 'cms_mioweb'),
						'type' => 'font',
						'content' => [
							'font-size' => '',
							'use-font' => 'text',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '40',
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
	'campaign_date' => [
		'name' => __('Proměnlivé datum', 'cms_mioweb'),
		'help' => MW_HELP_URL . 'article/926-element-pro-kampane-promenlive-datum',
		'description' => __('Vypíše datum závislé na vstupu do kampaně.', 'cms_mioweb'),
		'tab_setting' => [
			[
				'id' => 'content',
				'name' => __('Obsah', 'cms_mioweb'),
				'setting' => [
					[
						'id' => 'days',
						'title' => __('Vypsat datum posunuté o x dní od vstupu do kampaně', 'cms_mioweb'),
						'type' => 'text',
						'content' => '2',
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'time',
						'title' => __('Čas (ve formátu hh:mm)', 'cms_mioweb'),
						'type' => 'text',
						'content' => __('20:00', 'cms_mioweb'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'hide_year',
						'title' => '',
						'type' => 'switch',
						'label' => __('Skrýt rok', 'cms_ve'),
						'onedit' => [
							'action' => 'reload',
						],
					],
					[
						'id' => 'font',
						'title' => __('Písmo', 'cms_mioweb'),
						'type' => 'font',
						'content' => [
							'font-size' => '30',
							'use-font' => 'text',
							'align' => 'center',
							'color' => '',
						],
						'setting' => [
							'max_font_size' => '40',
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
], 'mioweb');
