<?php
$setting = [
	'title' => 'game',
	'start_modal' => true,
	'end_modal' => true,
];

$steps = [
	'0' => [
		'title' => __('Vyber šablonu.', 'cms_ve'),
		'text' => __('Čas běží. Hrajete s Katkou nebo Lukášem? Vyberte šablonu webu.', 'cms_ve'),
		'reload' => true,
		'steps' => [

			'0' => [
				'element' => '.ve_select_web_container',
				'hint_text' => __('Vyber šablonu', 'cms_ve'),
				'top' => '-40px',
				'align' => 'bottom',
				'action' => [
					'target' => '.mw_install_tut_web_template',
					'event' => 'click',
					'do' => 'select_template',
					'delay' => 3000,
				],
				'cheer' => __('Dobrá volba', 'cms_ve'),
			],

		],
	],

	'1' => [
		'title' => __('Doplň texty.', 'cms_ve'),
		'text' => sprintf(__('%s má velké touhy. Změň hlavní nadpis na text: Tvořím si svůj svět.', 'cms_ve'), $name),
		'steps' => [

			'0' => [
				'element' => '#element_0_0_0',
				'hint_text' => __('Klikni na text', 'cms_ve'),
				'align' => 'top',
				'action' => [
					'target' => '.mce-content-body',
					'event' => 'click',
				],
				'hide' => '.content_element_editbar, .row_edit_container',
				'target' => 'iframe',
			],
			'1' => [
				'element' => '#element_0_0_0',
				'hint_text' => __('Přepiš text na "Tvořím si svůj svět"', 'cms_ve'),
				'align' => 'bottom',
				'action' => [
					'target' => '.mce-content-body:eq(0)',
					'event' => 'rewrite',
					'text' => __('Tvořím si svůj svět', 'cms_ve'),
				],
				'back_action' => [
					'target' => '.mw_intro_overlay',
					'event' => 'click',
				],
				'hide' => '.content_element_editbar, .row_edit_container',
				'target' => 'iframe',
				'cheer' => __('Krásně napsáno', 'cms_ve'),
			],
		],
	],

	'2' => [
		'title' => __('Přidej fotky.', 'cms_ve'),
		'text' => sprintf(__('%s neví, jakou fotku vybrat. Přidej druhou fotku vedle té co už tam je.', 'cms_ve'), $name),
		'steps' => [
			'0' => [
				'element' => '.add_element_item_c:eq(3)',
				'scrollto' => '.element_image_container:eq(0)',
				'hint_text' => __('Přetáhni obrázek do obsahu stránky', 'cms_ve'),
				'align' => 'top',
				'action' => [
					'target' => '.add_element_item_c:eq(3)',
					'event' => 'drag',
					'drop' => '.mw_page_builder_droparea_element:eq(8)',
					'drop_text' => __('Přetáhni ho sem', 'cms_ve'),
				],
				'target' => '',
				'hide' => '.mce-tinymce-inline',
			],
			'1' => [
				'element' => '#row_1 .col-two.col-last .image_element_container',
				'hint_text' => __('Klikni na element', 'cms_ve'),
				'align' => 'top-left',
				'action' => [
					'target' => '#row_1 .col-two.col-last .image_element_container',
					'event' => 'click',
				],
				'hide' => '.content_element_editbar, .row_edit_container',
				'target' => 'iframe',
			],
			'2' => [
				'element' => '.mw_image_uploader:eq(0)',
				'hint_text' => __('Vyber obrázek', 'cms_ve'),
				'align' => 'bottom',
				'action' => [
					'target' => '.mw_image_uploader:eq(0) .mw_image_uploader_overlay',
					'event' => 'open_media',
				],
				'target' => '',
			],
			'3' => [
				'element' => '.media-frame-content .attachment:eq(0)',
				'hint_text' => __('Klikni na obrázek', 'cms_ve'),
				'align' => 'bottom',
				'action' => [
					'target' => '.media-frame-content .attachment:eq(0)',
					'event' => 'click',
				],

				'target' => '',
			],
			'4' => [
				'element' => '.media-toolbar-primary',
				'hint_text' => __('Potvrď výběr', 'cms_ve'),
				'align' => 'top-left',
				'action' => [
					'target' => '.media-toolbar-primary .button',
					'event' => 'click',
				],
				'target' => '',
				'cheer' => __('Skvělá práce', 'cms_ve'),
			],
		],
	],
	'3' => [
		'title' => __('Ulož stránku', 'cms_ve'),
		'text' => 'A teď už zbývá jen úpravy uložit.',
		'steps' => [
			'0' => [
				'element' => '.mw_editor_panel_save',
				'hint_text' => __('Ulož změny', 'cms_ve'),
				'align' => 'bottom-left',
				'action' => [
					'target' => '.mw_save_page',
					'event' => 'click',
				],
				'target' => '',
			],
		],
	],
];

$start_modal = '<h3 class="mw_intro_modal_title">' . __('Vítejte v Miowebu<br>a úvodní hře :)', 'cms_ve') . '</h3>'
. '<p>' . __('Katka a Lukáš si neví rady. A vy teď můžete jednoho<br>z nich doslova zachránit.', 'cms_ve') . '</p>'
. '<p>' . __('Jde o čas a rychlost. A tři jednoduché úkoly. Ti největší hrdinové to zvládnou do 2 minut. Katka a Lukáš čekají.<br>Můžeme začít?', 'cms_ve') . '</p>'
. '<div class="mw_intro_modal_buttons">'
. '<a class="cms_button mw_intro_modal_close mw_intro_modal_continue" href="#">' . __('Jdu na to!', 'cms_ve') . '</a>'
. '<a class="mw_intro_skip" href="#">' . __('Ne, nemám chuť na hraní', 'cms_ve') . '</a>'
. '</div>';

$end_modal = '<h3 class="mw_intro_modal_title">' . __('Jste v cíli.', 'cms_ve') . '</h3>'
. '<div class="mw_intro_time_result">' . __('Výsledný čas:', 'cms_ve') . ' <span></span></div>'
. '<p>' . __('Dobrá práce. Jsou tu i takoví, kteří do cíle<br>nikdy nedojdou.😉 Ale ta skutečná zábava a hra začíná právě teď. Váš nový web čeká.', 'cms_ve') . '</p>'
. '<div class="mw_intro_modal_buttons">'
. '<a class="cms_button mw_intro_modal_end" href="#">' . __('Vyberte si šablonu pro svůj nový web', 'cms_ve') . '</a>'
. '</div>';
