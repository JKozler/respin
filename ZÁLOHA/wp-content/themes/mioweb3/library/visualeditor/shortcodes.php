<?php
global $vePage;

$vePage->add_shortcode_groups([
	'basic' => [
		'name' => __('Základní', 'cms_ve'),
		'subelement' => true,
	],
]);

$vePage->add_shortcodes([
	'popup' => [
		'name' => __('Odkaz s pop-upem', 'cms_ve'),
		'type' => 'text',
		'setting' => [
			[
				'title' => __('Zobrazit pop-up', 'cms_ve'),
				'id' => 'id',
				'type' => 'popupselect',
			],
		],
	],
	'box' => [
		'name' => __('Text na pozadí', 'cms_ve'),
		'type' => 'text',
		'description' => __('Vybraný text vloží do boxu, kterému můžete nastavit libovolnou barvu pozadí.', 'cms_ve'),

		'setting' => [
			[
				'id' => 'background',
				'title' => __('Barva pozadí', 'cms_ve'),
				'type' => 'color',
				'content' => '#e8e8e8',
			],
			[
				'id' => 'color',
				'title' => __('Barva textu', 'cms_ve'),
				'type' => 'color',
			],

		],
	],
	'mwvideo' => [
		'name' => __('Video', 'cms_ve'),
		'description' => __('Vložte do článku video jednoduše zadáním odkazu na YouTube nebo Vimeo stránku s videem.', 'cms_ve'),

		'setting' => [
			[
				'id' => 'url',
				'title' => __('URL videa', 'cms_ve'),
				'type' => 'text',
				'desc' => __('Vložte URL stránky s YouTube nebo Vimeo videem.', 'cms_ve'),
			],
			[
				'id' => 'setting',
				'title' => __('Nastavení videa', 'cms_ve'),
				'type' => 'multiple_checkbox',
				'options' => [
					['name' => __('Přehrát automaticky', 'cms_ve'), 'value' => 'autoplay'],
					['name' => __('Zobrazit název videa', 'cms_ve'), 'value' => 'showinfo'],
					['name' => __('Skrýt ovládání videa (funguje pouze pro YouTube)', 'cms_ve'), 'value' => 'hide_control'],
					['name' => __('Zobrazit související videa na konci (funguje pouze pro YouTube)', 'cms_ve'), 'value' => 'rel'],
				],
			],

		],
	],
	'content' => [
		'name' => __('Předdefinovaný obsah', 'cms_ve'),
		'description' => __('Pomocí tohoto shortcodu můžete na stránku vložit obsah vytvořený pomocí vizuálního editoru.', 'cms_ve'),
		'setting' => [
			[
				'id' => 'contentinfo',
				'type' => 'info',
				'content' => __('Obsah je vždy ovlivněn nastavením vzhledu stránky. Pokud tedy stejný obsah umístíte na různě nastavené stránky, může se lišit například písmem, šířkou atd.', 'cms_ve'),
			],
			[
				'id' => 'id',
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
			],
		],
	],
], 'basic');
