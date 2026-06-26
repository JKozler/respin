<?php

$seo = get_option('seo_basic');
$foption = get_option('social_option');

mwApiConnect()->addApi('mioweb', [
	'name' => 'Mioweb',
]);
mwApiConnect()->addApi('fapi', [
	'name' => 'FAPI',
	'tags' => ['sell','shop'],
	'setting' => [
		[
			'name' => __('Přihlašovací jméno', 'cms'),
			'id' => 'login',
			'type' => 'text',
			'desc' => __('Zadejte přihlašovací jméno, které používáte ve FAPI.', 'cms'),
		],
		[
			'name' => __('API klíč', 'cms'),
			'id' => 'password',
			'type' => 'text',
			'desc' => '<a target="_blank" href="https://web.fapi.cz/account-settings/api-tokens?projectId=all">' . __('Získat API klíč z FAPI', 'cms') . '</a>',
		],
	],
]);
mwApiConnect()->addApi('se', [
	'name' => 'SmartEmailing',
	'tags' => ['email'],
	'setting' => [
		[
			'name' => __('Přihlašovací jméno', 'cms'),
			'id' => 'login',
			'type' => 'text',
		],
		[
			'name' => __('API token', 'cms'),
			'id' => 'password',
			'type' => 'text',
			'desc' => '<a target="_blank" href="https://napoveda.smartemailing.cz/article/344-vygenerovani-noveho-api-klice">' . __('Získat API klíč ze SmartEmailingu', 'cms') . '</a>',
		],
	],
]);

mwApiConnect()->addApi('mailerlite', [
	'name' => 'Mailerlite',
	'tags' => ['email'],
	'setting' => [
		[
			'name' => __('API token', 'cms'),
			'id' => 'password',
			'type' => 'text',
			'desc' => '<a target="_blank" href="https://www.mailerlite.com/help/where-to-find-the-mailerlite-api-key-and-documentation">' . __('Získat API klíč ze Mailerlite', 'cms') . '</a>',
		],
	],
]);

mwApiConnect()->addApi('simpleshop', [
	'name' => 'SimpleShop',
	'tags' => ['sell'],
	'setting' => [
		[
			'name' => __('Přihlašovací email', 'cms'),
			'id' => 'login',
			'type' => 'text',
			'desc' => __('Zadejte email, který používáte pro přihlášení do služby SimpleShop.', 'cms'),
		],
		[
			'name' => __('SimpleShop API Klíč', 'cms'),
			'id' => 'password',
			'type' => 'text',
			'desc' => '<a target="_blank" href="https://app.simpleshop.cz/nastaveni/api/">' . __('Najdete ho ve svém SimpleShop účtu v Nastavení.', 'cms') . '</a>',
		],
	],
]);

mwApiConnect()->addApi('ecomail', [
	'name' => 'Ecomail',
	'tags' => ['email'],
	'setting' => [
		[
			'name' => __('API token', 'cms'),
			'id' => 'password',
			'type' => 'text',
			'desc' => '<a target="_blank" href="https://support.ecomail.cz/cs/articles/66536-api-pro-praci-s-ecomailem">' . __('Získat API klíč ze Ecomail', 'cms') . '</a>',
		],
	],
]);
mwApiConnect()->addApi('aweber', [
	'name' => 'aWeber',
	'tags' => ['email'],
	'setting' => [
		[
			'name' => __('Získat autorizační kód', 'cms'),
			'id' => 'login',
			'content' => 'https://auth.aweber.com/1.0/oauth/authorize_app/d4198b5e',
			'type' => 'authorize_api',
			'desc' => __('Pro propojení je potřeba povolit připojení Miowebu k Vašemu účtu. Po kliknutí na odkaz se přihlaste k Vašemu "AWeber" účtu. '
				. 'Obdržíte unikátní kód, který zkopírujte do pole "Autorizační kód".', 'cms'),
		],
		[
			'name' => __('Autorizační kód', 'cms'),
			'id' => 'password',
			'type' => 'textarea',
		],
	],
]);
mwApiConnect()->addApi('getresponse', [
	'name' => 'GetResponse',
	'tags' => ['email'],
	'setting' => [
		[
			'name' => __('API klíč', 'cms'),
			'id' => 'password',
			'type' => 'text',
			'desc' => '<a target="_blank" href="https://app.getresponse.com/api">' . __('Získat API klíč z GetResponse', 'cms') . '</a>',
		],
	],
]);
mwApiConnect()->addApi('mailchimp', [
	'name' => 'MailChimp',
	'tags' => ['email'],
	'setting' => [
		[
			'name' => __('API klíč', 'cms'),
			'id' => 'password',
			'type' => 'text',
			'desc' => '<a target="_blank" href="https://admin.mailchimp.com/account/api/">' . __('Získat API klíč z MailChimp', 'cms') . '</a>',
		],
	],
]);

if (!mw_is_lite_editor()) {
	mwApiConnect()->addApi('google_maps', [
		'name' => 'Google Mapy',
		'tags' => ['others'],
		'setting' => [
			[
				'name' => __('API klíč pro napojení na Google mapy', 'cms'),
				'id' => 'api_key',
				'type' => 'text',
				'desc' => __('<a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank">' . __('Vygenerujte si svůj API klíč (zdarma).', 'cms') . '</a>', 'cms'),
			],
		],
	]);
}

mwApiConnect()->addApi('fbconversions', [
	'name' => 'Facebook Conversions API',
	'tags' => ['analytics'],
	'setting' => [
		[
			'name' => __('Pixel ID', 'cms'),
			'id' => 'pixel_id',
			'type' => 'text',
		],
		[
			'name' => __('Access token', 'cms'),
			'id' => 'access_token',
			'type' => 'text',
		],
		[
			'name' => __('Test ID', 'cms'),
			'id' => 'test_id',
			'type' => 'text',
		],
		[
			'name' => __('Testovací režim', 'cms'),
			'label' => __('Zasílání testovacích událostí.', 'cms'),
			'id' => 'debug_mode',
			'type' => 'switch',
		],
	],
]);

mwApiConnect()->addApi('google_analytics', [
	'name' => 'Google Analytics',
	'tags' => ['analytics'],
	'setting' => [
		[
			'name' => __('ID měření', 'cms'),
			'desc' => __('Chcete-li měřit web, musíte si nejprve vytvořit účet Google Analytics. Poté v Google Analytic skopírovat ID ve formátu G-XXXXXXX.
			 Pokaždé, když uživatel navštíví webovou stránku, bude sledovací kód shromažďovat pseudonymní informace o tom, jak daný uživatel se stránkou pracoval.
			 ID měření ve formátu G-XXXXXXX identifikuje datový stream, který odesílá data do služby Google Analytics 4.', 'cms'),
			'placeholder' => 'G-XXXXXXX',
			'id' => 'measurement_id',
			'type' => 'text',
		],
		[
			'name' => __('Testovací režim', 'cms'),
			'tooltip' => __('V testovacím režimu můžete živě sledovat události ve svém účtu Google analytics v záložce Konfigurovat -> DebugView. Testovací režim je určen pouze pro Google analytics 4.', 'cms'),
			'label' => __('Testovací režim Google Analytics 4 (DebugView)', 'cms'),
			'id' => 'debug_mode',
			'type' => 'switch',
		],
	],
]);

mwApiConnect()->addApi('recaptcha', [
	'name' => 'reCAPTCHA v3',
	'tags' => ['recaptcha'],
	'setting' => [
		[
			'type' => 'info',
			'content' => '<a target="_blank" href="https://www.google.com/u/0/recaptcha/admin">' . __('Vytvořit přihlašovací údaje v Google reCAPTCHA účtu.', 'cms') . '</a>',
		],
		[
			'name' => __('Site key', 'cms'),
			'id' => 'site_key',
			'type' => 'text',
		],
		[
			'name' => __('Secret key', 'cms'),
			'id' => 'secret_key',
			'type' => 'text',
		],
	],
]);

mwApiConnect()->addApi('gtm', [
	'name' => 'Google Tag Manager',
	'tags' => ['analytics'],
	'setting' => [
		[
			'name' => __('ID kontejneru', 'cms'),
			'desc' => __('ID kontejneru je textový řetězec ve tvaru GTM-XXXXXX, který najdete ve správci značek na svém účtu Google Tag Manageru.', 'cms'),
			'placeholder' => 'GTM-XXXXXX',
			'id' => 'container_id',
			'type' => 'text',
		],
	],
]);


MW()->add_fonts([
	'Arial',
//	'Arial Black',
	'Comic Sans MS',
	'Courier',
	'Georgia',
	'Impact',
	'Tahoma',
	'Times New Roman',
	'Trebuchet MS',
	'Verdana',
]);
MW()->add_google_fonts([

	'Alegreya Sans' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/alegreya.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'500' => 'Medium',
			'600' => 'Semi-Bold',
			'700' => 'Bold',
			'800' => 'Extra-Bold',
			'900' => 'Ultra-Bold',
		],
	],
	'Allura' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/allura.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Amatic SC' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/amatic_sc.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Anton' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/anton.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Arbutus Slab' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/arbutus_slab.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Archivo Narrow' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/archivo_narrow.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Archivo Black' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/archivo_black.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Arimo' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/arimo.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Autour One' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/autour_one.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Baloo' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/baloo.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Bree Serif' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/bree_serif.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Capriola' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/capriola.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Caveat' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/caveat.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Caveat Brush' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/caveat_brush.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Clicker Script' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/clicker_script.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Courgette' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/courgette.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Crete Round' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/crete_round.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Dosis' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/dosis.jpg',
		'weights' => [
			'200' => 'Extra-Light',
			'300' => 'Light',
			'400' => 'Normal',
			'500' => 'Medium',
			'600' => 'Semi-Bold',
			'700' => 'Bold',
			'800' => 'Extra-Bold',
		],
	],
	'Enriqueta' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/enriqueta.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Exo' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/exo.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'600' => 'Semi-Bold',
			'700' => 'Bold',
			'800' => 'Extra-Bold',
		],
	],
	'Fira Sans' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/fira_sans.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'500' => 'Medium',
			'700' => 'Bold',
		],
	],
	'Inder' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/inder.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Grand Hotel' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/grand_hotel.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Jaldi' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/jaldi.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Just Me Again Down Here' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/just_me_again_down_here.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Kaushan Script' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/kaushan_script.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Lora' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/lora.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'McLaren' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/mclaren.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Merriweather' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/merriweather.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'700' => 'Bold',
			'900' => 'Ultra-Bold',
		],
	],
	'Mouse Memoirs' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/mouse_memoirs.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Noticia Text' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/noticia_text.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Noto Sans' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/noto_sans.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Noto Serif' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/noto_serif.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Open Sans' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/open_sans.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'600' => 'Semi-Bold',
			'700' => 'Bold',
			'800' => 'Extra-Bold',
		],
	],
	'Open Sans Condensed' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/open_sans_condensed.jpg',
		'weights' => [
			'300' => 'Light',
			'700' => 'Bold',
		],
	],
	'Oswald' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/oswald.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Pacifico' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/pacifico.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Parisienne' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/parisienne.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Patrick Hand' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/patrick_hand.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Patrick Hand SC' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/patrick_hand_sc.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Petit Formal Script' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/petit_formal.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Play' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/play.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Playfair Display' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/playfair_display.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
			'900' => 'Black',
		],
	],
	'Ribeye' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/ribeye.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Roboto' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/roboto.jpg',
		'weights' => [
			'100' => 'Thin',
			'300' => 'Light',
			'400' => 'Normal',
			'500' => 'Medium',
			'700' => 'Bold',
			'900' => 'Ultra Bold',
		],
	],
	'Roboto Condensed' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/roboto_condensed.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Roboto Slab' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/roboto_slab.jpg',
		'weights' => [
			'100' => 'Thin',
			'300' => 'Light',
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Sacramento' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/sacramento.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Signika' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/signika.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'600' => 'Semi-Bold',
			'700' => 'Bold',
		],
	],
	'Slabo 27px' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/slabo.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Stint Ultra Condensed' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/stint_ultra_condensed.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Tinos' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/tinos.jpg',
		'weights' => [
			'400' => 'Normal',
			'700' => 'Bold',
		],
	],
	'Unica One' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/unica_one.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Ubuntu' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/ubuntu.jpg',
		'weights' => [
			'300' => 'Light',
			'400' => 'Normal',
			'500' => 'Medium',
			'700' => 'Bold',
		],
	],
	'Ubuntu Condensed' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/ubuntu_condensed.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
	'Voces' => [
		'img' => get_template_directory_uri() . '/library/admin/images/fonts/voces.jpg',
		'weights' => [
			'400' => 'Normal',
		],
	],
]);


// fast add and copy setting

mwSetting()->addObjectFastSetting([
	'fields' => [
		[
			'type' => 'item_set',
			'object_id' => 'page',
			'fields' => [
				'post_title' => [
					'label' => __('Název stránky', 'cms'),
					'slug_type' => 'hidden',
				],
				'post_parent' => [
					'label' => __('Nadřazená stránka', 'cms'),
					'tooltip' => __('Stránka bude v hierarchii stránek zařazena pod stránku, kterou zde nastavíte. Toto nastavení se projeví i ve výsledné URL stránky. Změna nadřazené stránky mění celkovou URL stránky.', 'cms'),
				],
			],
		],
	],
], ['page']);

mwSetting()->addObjectFastSetting([
	'fields' => [
		[
			'type' => 'item_set',
			'fields' => [
				'post_title' => [
					'label' => __('Název', 'cms'),
					'slug' => false,
				],
			],
		],
	],
], ['cms_footer', 'weditor', 've_header', 've_elvar', 'mw_slider']);


// object setting

mwSetting()->addObjectSetting([
	'id' => 've_event',
	'title' => __('Akce', 'cms'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => MW_EVENT_SLUG,
					'fields' => [
						'post_title' => [
							'label' => __('Název akce', 'cms'),
							'slug' => false,
						],
						'post_excerpt' => [
							'label' => __('Popisek', 'cms'),
						],
					],
				],
				[
					'id' => 'mw_event_date_start',
					'type' => 'datetime',
					'name' => __('Datum a čas konání (začátek akce)', 'cms'),
					'save' => 'post_meta',
					'savehook' => function ($postId, $field, $fieldValue, &$fieldSaved) {
						$datetime = $fieldValue['date'] . ' ' . $fieldValue['hour'] . ':' . $fieldValue['minute'];
						update_post_meta($postId, 'mw_event_date_start', strtotime($datetime));
						$fieldSaved = true;
					},
					'convert' => 1,
				],
				[
					'id' => 'date_end',
					'type' => 'date',
					'name' => __('Konec akce', 'cms'),
				],
				[
					'name' => __('Místo konání', 'cms'),
					'id' => 'where',
					'type' => 'text',
				],
				[
					'name' => __('Stránka s popisem akce', 'cms'),
					'id' => 'event_page',
					'type' => 'page_link',
				],
			],
		],
	],
], [MW_EVENT_SLUG]);

mwSetting()->addObjectSetting([
	'id' => 'term',
	'title' => __('Kategorie', 'cms'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => MW_EVENT_CAT_SLUG,
					'fields' => [
						'term_title' => [
							'label' => __('Název kategorie', 'cms'),
							'slug' => false,
						],
					],
				],
			],
		],
	],
], [MW_EVENT_CAT_SLUG]);

mwSetting()->addObjectSetting([
	'id' => 'term',
	'title' => __('Komentář', 'cms'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => 'comments',
					'fields' => [
						'comment_author' => [
							'label' => __('Autor', 'cms'),
						],
						'comment_email' => [
							'label' => __('Email', 'cms'),
						],
						'comment_url' => [
							'label' => __('URL', 'cms'),
						],
						'comment_content' => [
							'label' => __('Komentář', 'cms'),
						],
					],
				],
			],
		],
	],
], ['comments']);



MW()->container['seo_setting'] = [
	[
		'type' => 'box',
		'setting' => [
			[
				'name' => __('Titulek', 'cms'),
				'id' => 'metatitle',
				'type' => 'text',
				'desc' => __('Maximální doporučená délka pro titulek je 70 znaků. Pokud necháte toto pole prázdné, bude tag <code>title</code> obsahovat název stránky.', 'cms'),
				'tooltip' => __('Tag <code>title</code> je druhým nejdůležitějším prvkem, který ovlivňuje on-page SEO. Jeho obsah se zároveň zobrazuje v záhlaví prohlížeče a jako název stránky při vyhledávání.', 'cms'),
			],
			[
				'name' => __('Popis', 'cms'),
				'id' => 'metadesc',
				'type' => 'textarea',
				'desc' => __('Maximální doporučená délka je 150 znaků.', 'cms'),
				'tooltip' => __('Meta tag <code>description</code> slouží jako krátký popis obsahu stránky. Některé vyhledávače tento tag používají pro zobrazení popisku stránky ve výsledku vyhledávání. Obsah by měl být tvořen souvislými větami s vhodně zvolenými klíčovými slovy.', 'cms'),
			],
			[
				'name' => __('Klíčová slova', 'cms'),
				'id' => 'metakey',
				'type' => 'textarea',
				'tooltip' => __('Vyplnění meta tag <code>keywords</code> je další možností, jak zvýšit on-page SEO stránky. Napište zde několik klíčových slov, které souvisejí s obsahem stránky. Nepřehánějte to ale s jejich množstvím.', 'cms'),
			],
			[
				'name' => __('Pro roboty', 'cms'),
				'id' => 'robots',
				'type' => 'multiple_checkbox',
				'options' => [
					['name' => __('Neindexovat tuto stránku (<code>noindex</code>)', 'cms'), 'value' => 'noindex'],
					['name' => __('Nesledovat odkazy této stránky (<code>nofollow</code>)', 'cms'), 'value' => 'nofollow'],
					['name' => __('Nearchivovat tuto stránku (<code>noarchive</code>)', 'cms'), 'value' => 'noarchive'],
				],
				'tooltip' => __('Zde můžete nastavit jak se mají na stránce chovat roboti. Můžete zakázat robotům indexování obsahu (noindex), sledování odkazů (nofollow) a zařazování stránky do archivu (noarchive).', 'cms'),
			],
		],
	],
];

MW()->container['facebook_setting'] = [
	[
		'type' => 'box',
		'setting' => [
			[
				'id' => 'fac_info',
				'type' => 'info',
				'color' => 'blue',
				'content' => __('Pro kontrolu zobrazení stránky při sdílení na Facebooku můžete použít <a target="_blank" href="https://developers.facebook.com/tools/debug/">Ladící nástroj pro sdílení</a>, kde stačí zadat URL stránky, kterou chcete zkontrolovat.', 'cms'),
			],
			[
				'name' => __('Facebook titulek', 'cms'),
				'id' => 'fac_title',
				'type' => 'text',
				'tooltip' => __('Meta tag <code>og:title</code> určuje nadpis stránky při jejím sdílení na Facebooku. Pokud jej nenastavíte, použije se název stránky.', 'cms'),
			],
			[
				'name' => __('Facebook popis', 'cms'),
				'id' => 'fac_desc',
				'type' => 'textarea',
				'tooltip' => __('Meta tag <code>og:description</code> určuje popis stránky při jejím sdílení na Facebooku.', 'cms'),
			],
			[
				'name' => __('Facebook obrázek', 'cms'),
				'id' => 'fac_image',
				'type' => 'image_url',
				'tooltip' => __('Tento obrázek se bude zobrazovat na facebooku při sdílení této stránky.', 'cms'),
				'desc' => __('Pokud obrázek nezadáte, použije se náhledový obrázek. Pokud není zadán ani náhledový obrázek, použije se defaultní facebookový obrázek, který můžete zadat v nastavení webu. Doporučená velikost obrázku je 1200 × 628 px.', 'cms'),
			],
		],
	],
];

MW()->container['redirect_setting'] = [
	[
		'type' => 'box',
		'setting' => [
			[
				'name' => __('Přesměrovat na', 'cms'),
				'id' => 'redirect_url',
				'type' => 'page_link',
				'target' => false,
				'tooltip' => __('Zde můžete zadat URL adresu, na kterou chcete, aby byl uživatel přesměrován. Budou přesměrováni všichni uživatelé na všech zařízeních.', 'cms'),
			],
			[
				'name' => __('Druh přesměrování', 'cms'),
				'id' => 'redirect_type',
				'type' => 'select',
				'content' => '302',
				'options' => [
					['name' => __('Dočasné přesměrování', 'cms'), 'value' => '302'],
					['name' => __('Trvalé přesměrování', 'cms'), 'value' => '301'],
				],
				'tooltip' => __('Informace zda jde o dočasné nebo trvalé přesměrování je důležitá pro SEO.', 'cms'),
				'show' => 'redirect_type',
			],
			[
				'name' => __('Přesměrovávat ode dne', 'cms'),
				'id' => 'redirect_date',
				'type' => 'datetime',
				'desc' => __('Pokud zadáte datum, bude přesměrování stránky platné až od tohoto data a času.', 'cms'),
				'show_group' => 'redirect_type',
				'show_val' => '302',
			],
			[
				'name' => __('Přesměrovávat do dne', 'cms'),
				'id' => 'redirect_till_date',
				'type' => 'datetime',
				'desc' => __('Pokud zadáte datum, bude přesměrování stránky platné k tomuto datu a času.', 'cms'),
				'show_group' => 'redirect_type',
				'show_val' => '302',
			],
			[
				'name' => __('Přesměrovávat po půlnoci po X dnech od vstupu do kampaně.', 'cms'),
				'id' => 'redirect_campaign',
				'type' => 'text',
				'desc' => __('Zde můžete zadat číslo odpovídající počtu dní, které určí, po kolika dnech od vstupu do kampaně se má začít uživatel přesměrovávat z této stránky. Lze použít například, když chcete po X dnech v evergreenové kampani zakázat uživateli přístup k nějaké stránce. Například k objednávce. Stránka, ze které chcete přesměrovávat, musí být zařazena do kampaně jako stránka s obsahem zdarma.', 'cms'),
				'show_group' => 'redirect_type',
				'show_val' => '302',
			],
		],
	],
	[
		'id' => 'mobile_redirect',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Přesměrování pro mobilní zařízení', 'cms'),
		'setting' => [
			[
				'name' => __('Přesměrovat mobilní zařízení na', 'cms'),
				'id' => 'redirect_mobile_url',
				'type' => 'page_link',
				'target' => false,
				'tooltip' => __('Zde můžete zadat URL adresu, na kterou chcete, aby byl uživatel na mobilním zařízení přesměrován. Návštěvníci, kteří na stránku přistupují pomocí notebooků a klasických stolních počítačů, nebudou přesměrováni. Toto nastavení je vhodné použít, například když chcete místo této stránky na mobilních zařízeních zobrazit jinou stránku.', 'cms'),
			],
		],
	],
];

MW()->container['popup_setting'] = [
	[
		'id' => 'clasic_popup_setting',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Klasický popup', 'cms'),
		'setting' => [
			[
				'name' => __('Klasický pop-up', 'cms'),
				'id' => 'clasic_popup',
				'type' => 'popupselect',
				'tooltip' => __('Tento pop-up se zobrazí po načtení stránky nebo při splnění zadané podmínky v pokročilém nastavení.', 'cms'),
			],
			[
				'id' => 'popup_type',
				'name' => __('Zobrazit pop-up', 'cms'),
				'type' => 'select',
				'show' => 'popup_type',
				'options' => [
					['name' => __('Po načtení stránky', 'cms'), 'value' => 'onload'],
					['name' => __('Pokročilé nastavení', 'cms'), 'value' => 'advance'],
				],
				'content' => 'onload',
			],
			[
				'name' => __('Zobrazit po x sekundách', 'cms'),
				'id' => 'time',
				'type' => 'text',
				'desc' => __('Pop-up se zobrazí po x sekundách od načtení stránky.', 'cms'),
				'show_group' => 'popup_type',
				'show_val' => 'advance',
			],
			[
				'name' => __('Zobrazit po odskrolování', 'cms'),
				'id' => 'scroll',
				'type' => 'size',
				'content' => [
					'size' => '',
					'unit' => 'px',
				],
				'desc' => __('Pop-up se zobrazí po odskrolování zadané části stránky (v % nebo v px).', 'cms'),
				'show_group' => 'popup_type',
				'show_val' => 'advance',
			],
			[
				'name' => __('Zobrazit po naskrolování na prvek s CSS selektorem', 'cms'),
				'id' => 'selector',
				'type' => 'text',
				'placeholder' => __('.class nebo #id', 'cms'),
				'desc' => __('Pop-up se zobrazí po naskrolování na prvek stránky se zadaným CSS selektorem.', 'cms'),
				'show_group' => 'popup_type',
				'show_val' => 'advance',
			],
		],
	],
	[
		'id' => 'clasic_popup_setting',
		'type' => 'toggle_group',
		'title' => __('Exit pop-up', 'cms'),
		'setting' => [
			[
				'name' => __('Exit pop-up', 'cms'),
				'id' => 'exit_popup',
				'type' => 'popupselect',
				'tooltip' => __('Tento pop-up se zobrazí v momentě, kdy uživatel vyjede myší do horní části prohlížeče.', 'cms'),
			],
		],
	],
];

mwSetting()->addObjectSettingCategory([
	'id' => 'appearance',
	'title' => __('Vzhled stránky', 'cms'),
	'include' => ['page'],
], ['page']);

mwSetting()->addObjectSetting([
	'id' => 'post_setting',
	'show_sidebar' => true,
	'hide_in_wp' => true,
	'title' => __('Stránka', 'cms'),
	'fields' => [
		[
			'id' => 'basic_page_setting',
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => 'page',
					'fields' => [
						'post_title' => [
							'label' => __('Název stránky', 'cms'),
						],
						'post_excerpt' => [
							'label' => __('Popisek', 'cms'),
							'tooltip' => __('Popisek se může používat ve výpisech stránek na webu. Také je používán jako popis stránky pro vyhledávače v případě, že není nastaven popis v nastavení SEO stránky.', 'cms'),
						],
						'post_parent' => [
							'label' => __('Nadřazená stránka', 'cms'),
							'tooltip' => __('Stránka bude v hierarchii stránek zařazena pod stránku, kterou zde nastavíte. Toto nastavení se projeví i ve výsledné URL stránky. Změna nadřazené stránky mění celkovou URL stránky.', 'cms'),
						],
						'menu_order' => [
							'label' => __('Pořadí stránky', 'cms'),
							'tooltip' => __('Tímto nastavením můžete určit pořadí stránek v různých výpisech stránek.', 'cms'),
						],
					],
				],
			],
		],
	],
], ['page']);

mwSetting()->addObjectSetting([
	'id' => 'post_setting',
	'show_sidebar' => true,
	'hide_in_wp' => true,
	'title' => __('Článek', 'cms'),
	'fields' => [
		[
			'id' => 'basic_page_setting',
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => 'post',
					'fields' => [
						'post_title' => [
							'label' => __('Název článku', 'cms'),
						],
						'post_excerpt' => [
							'label' => __('Popisek', 'cms'),
							'tooltip' => __('Popisek se používá ve výpisech článků. Také je používán jako popis stránky pro vyhledávače v případě, že není nastaven popis v nastavení SEO stránky.', 'cms'),
						],
						'post_author' => [
							'label' => __('Autor', 'cms'),
						],
						'stick_post' => [
							'label' => __('Připnout článek', 'cms'),
						],
						'post_format' => [
							'label' => __('Formát článku', 'cms'),
						],
					],
				],
			],
		],
	],
], ['post']);

if (!isset($seo['seo'])) {
	mwSetting()->addObjectSetting([
		'id' => 'page_seo',
		'title' => __('SEO', 'cms'),
		'fields' => MW()->container['seo_setting'],
	], ['page', 'post']);
}

if (!isset($foption['hide_facebook'])) {
	mwSetting()->addObjectSetting([
		'id' => 'page_facebook',
		'title' => __('Facebook atributy', 'cms'),
		'fields' => MW()->container['facebook_setting'],
	], ['page', 'post']);
}

MW()->container['custom_codes'] = [
	[
		'id' => '',
		'type' => 'box',
		'title' => __('Konverzní kódy pro tuto stránku', 'cms'),
		'setting' => [
			[
				'id' => 'conversion_codes',
				'type' => 'code_list',
				'list_type' => 'conversion',
			],
		],
	],
	[
		'id' => '',
		'type' => 'box',
		'title' => __('Kódy pro tuto stránku', 'cms'),
		'setting' => [
			[
				'id' => 'codes',
				'type' => 'code_list',
			],
		],
	],
	[
		'id' => 'custom_css',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('CSS styly pro tuto stránku', 'cms'),
		'setting' => [
			[
				'id' => 'css',
				'type' => 'textarea',
				'rows' => 8,
				'desc' => __('Zde můžete vložit vlastní CSS styly, které budou platit pouze pro tuto stránku.', 'cms'),
			],
		],
	],
];

mwSetting()->addObjectSetting([
	'id' => 'mw_page_codes',
	'title' => __('Vlastní kódy', 'cms'),
	'fields' => MW()->container['custom_codes'],
], ['page', 'post']);

mwSetting()->addObjectSetting([
	'id' => 'page_redirect',
	'title' => __('Přesměrování', 'cms'),
	'fields' => MW()->container['redirect_setting'],
], ['page', 'post']);


mwSetting()->addObjectSetting([
	'id' => 'page_statistics',
	'exclude_modules' => ['eshop', 'blog'],
	'title' => __('A/B testování', 'cms'),
	'fields' => [
		[
			'id' => 'a-b_testing_results',
			'type' => 'toggle_group',
			'open' => true,
			'title' => __('Výsledky testování', 'cms'),
			'setting' => [
				[
					'id' => 'statistics',
					'type' => 'page_statistics',
				],
			],
		],
		[
			'id' => 'a-b_testing_setting',
			'type' => 'toggle_group',
			'open' => true,
			'title' => __('Nastavení A/B testování', 'cms'),
			'setting' => [
				[
					'name' => __('Cílová stránka pro výpočet konverze', 'cms'),
					'id' => 'target',
					'type' => 'selectpage',
					'desc' => __('Pokud nastavíte cílovou stránku, bude se u této stránky počítat konverzní poměr, který bude informovat o tom, kolik procent návštěvníků se z této stránky dostalo na zadanou cílovou stránku. To znamená, kolik jich například kliklo na tlačítko, které odkazovalo na cílovou stránku.', 'cms'),
				],
				[
					'id' => 'pages',
					'name' => __('Další varianty stránky', 'cms'),
					'type' => 'simple_feature',
					'text_add' => __('Přidat stránku', 'cms'),
					'sortable' => false,
					'fields' => [
						'' => [
							'title' => __('Stránka', 'cms'),
							'type' => 'pageselect',
						],
					],
					'tooltip' => __('Každému návštěvníkovi této URL se zobrazí náhodně vybraná varianta stránky nebo originál stránky.', 'cms'),
					'desc' => __('Zadáním dalších variant stránek se bude návštěvníkům náhodně zobrazovat jedna ze zadaných stránek (včetně té originální). U každé varianty se bude počítat míra konverze. Můžete tak zjistit, která ze stránek lépe konvertuje. Pokud uživatel navštíví stránku, přiřadí se mu jedna z variant, kterou si to zapamatuje po 48 hodin. Po této době, když ten samý uživatel navštíví stránku znovu, může se mu zobrazit jiná varianta a zároveň se stránce přičte další návštěva.', 'cms'),
				],
				[
					'name' => __('Nepřímá konverze', 'cms'),
					'id' => 'undirect_conversion',
					'type' => 'switch',
					'label' => __('Umožnit nepřímou konverzi', 'cms'),
					'desc' => __('Defaultně se počítá pouze přímá konverze. To znamená, že se konverze započítá jen když návštěvník přejde z testované stránky přímo na cílovou stránku. Tedy nenavštíví mezi testovanou a cílovou stránkou žádnou jinou stránku. U nepřímé konverze může návštěvník vstoupit na více stránek, než se dostane na cílovou stránku.', 'cms'),
				],
			],
		],
	],
], ['page']);

mwSetting()->addObjectSetting([
	'id' => 've_popup',
	'title' => __('Pop-upy stránky', 'cms'),
	'fields' => [
		[
			'type' => 'box',
			'title' => __('Použít', 'cms'),
			'setting' => [
				[
					'id' => 'show',
					'type' => 'select',
					'show' => 'popupset',
					'options' => [
						['name' => __('Globální pop-upy', 'cms'), 'value' => 'global'],
						['name' => __('Vlastní pop-upy', 'cms'), 'value' => 'page'],
					],
					'content' => 'global',
				],
			],
		],
		[
			'id' => 'popup_group',
			'type' => 'group',
			'setting' => MW()->container['popup_setting'],
			'show_group' => 'popupset',
			'show_val' => 'page',
		],
	],
], ['page', 'post']);

MW()->container['slider_setting'] = [
	[
		'id' => 'use_slider',
		'title' => '',
		'type' => 'checkbox',
		'label' => __('Zobrazit jako slider', 'cms'),
		'show' => 'sliderset',
	],
	[
		'id' => 'sliderset_group',
		'type' => 'group',
		'setting' => [
			[
				'id' => 'animation',
				'title' => __('Typ animace', 'cms'),
				'type' => 'select',
				'content' => 'fade',
				'options' => [
					['name' => __('Prolínání', 'cms'), 'value' => 'fade'],
					['name' => __('Zprava doleva', 'cms'), 'value' => 'slide'],
				],
			],
			[
				'id' => 'delay',
				'title' => __('Zpoždění slidů', 'cms'),
				'type' => 'size',
				'unit' => 'ms',
				'content' => '3500',
			],
			[
				'id' => 'speed',
				'title' => __('Délka animace', 'cms'),
				'type' => 'size',
				'unit' => 'ms',
				'content' => '1000',
			],
			[
				'id' => 'off_autoplay',
				'title' => __('Autoplay', 'cms'),
				'type' => 'checkbox',
				'label' => __('Vypnout autoplay', 'cms'),
			],
			[
				'id' => 'color_scheme',
				'title' => __('Barva ovládacích prvků', 'cms'),
				'type' => 'select',
				'content' => '',
				'options' => [
					['name' => __('Světlé', 'cms'), 'value' => 'light'],
					['name' => __('Tmavé', 'cms'), 'value' => ''],
				],
			],

		],
		'show_group' => 'sliderset',
	],

];

MW()->container['header_setting'] = [
	[
		'id' => 'logo_setting',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Logo', 'cms'),
		'setting' => [
			[
				'type' => 'tabs',
				'id' => 'logo_setting',
				'content' => 'image',
				'show' => 'logo_type',
				'onedit' => [
					'action' => 'reload_logo',
					'target' => ' #site_title',
				],
				'tabs' => [
					'image' => [
						'name' => __('Obrázek', 'cms'),
						'icon' => 'image',
						'setting' => [
							[
								'id' => 'logo',
								'type' => 'image',
								'content' => ['image' => VS_DEFAULT_DIR . 'images/default/logo1.png'],
								'onedit' => [
									'action' => 'change_img',
									'target' => ' #site_title img',
									'setting' => 'show',
								],
							],
							[
								'name' => __('Velikost loga', 'cms'),
								'id' => 'logo_size',
								'content' => '120',
								'type' => 'slider',
								'setting' => [
									'min' => '20',
									'max' => '500',
									'unit' => 'px',
								],
								'onedit' => [
									'action' => 'change_css',
									'target' => ' #site_title img',
									'css' => 'maxWidth',
								],
							],
						],
					],
					'text' => [
						'name' => __('Textové logo', 'cms'),
						'icon' => 'type',
						'setting' => [
							[
								'name' => __('Text loga', 'cms'),
								'id' => 'logo_text',
								'type' => 'text',
								'content' => __('Název webu', 'cms'),
								'onedit' => [
									'action' => 'change_text',
									'target' => ' #site_title',
								],
							],
							[
								'title' => __('Font loga', 'cms'),
								'id' => 'logo_font',
								'type' => 'font',
								'content' => [
									'font-size' => '20',
									'font-family' => '',
									'weight' => '',
									'color' => '#111111',
								],
								'setting' => [
									'max_font_size' => '30',
								],
								'onedit' => [
									'action' => 'change_font',
									'target' => ' #site_title',
								],
							],
						],
					],
				],
			],
		],
	],
	[
		'id' => 'basic_setting',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Základní nastavení', 'cms'),
		'setting' => [
			[
				'name' => __('Rozložení hlavičky', 'cms'),
				'id' => 'appearance',
				'type' => 'imageselect',
				'list' => 'headers',
				'content' => 'type1',
				'onedit' => [
					'action' => 'reload',
				],
				'show' => 'header_appearanace',
			],
			[
				'id' => 'menu_style',
				'title' => __('Typ menu', 'cms'),
				'type' => 'imageselect',
				'content' => '1',
				'options' => [
					'1' => VS_DIR . 'images/image_select/nav1.jpg',
					'2' => VS_DIR . 'images/image_select/nav2.jpg',
					'3' => VS_DIR . 'images/image_select/nav3.jpg',
					'4' => VS_DIR . 'images/image_select/nav4.jpg',
					'5' => VS_DIR . 'images/image_select/nav5.jpg',
				],
				'show' => 'menu_style',
				'show_group' => 'header_appearanace',
				'show_val' => 'type1,type1b,type1c,type12,type11',
				'onedit' => [
					'action' => 'change_class',
					'class' => 'menu_style_h',
				],
			],

			[
				'name' => __('Menu', 'cms'),
				'id' => 'menu',
				'type' => 'selectmenu',
				'onedit' => [
					'action' => 'change_menu',
					'target' => ' nav .mw_header_menu_wrap',
				],
			],
			[
				'name' => __('Barva pozadí hlavičky', 'cms'),
				'id' => 'background_color',
				'type' => 'background',
				'content' => [
					'color1' => '#ffffff',
					'transparency1' => '1',
					'rgba1' => 'rgba(255,255,255,1)',
					'color2' => '',
					'transparency2' => '1',
					'rgba2' => '',
					'gradient' => '0',
				],
				'onedit' => [
					'action' => 'change_header_bg',
				],
			],
			[
				'id' => 'header_padding',
				'title' => __('Odsazení obsahu hlavičky', 'cms'),
				'type' => 'slider',
				'setting' => [
					'min' => '0',
					'max' => '80',
					'unit' => 'px',
				],
				'onedit' => [
					'action' => 'change_tb_padding',
					'target' => ' #header_in',
				],
				'content' => '20',
			],
			[
				'id' => 'header_height',
				'type' => 'hidden_input',
			],
		],
	],
	[
		'id' => 'menu_setting',
		'type' => 'toggle_group',
		'title' => __('Vzhled menu', 'cms'),
		'setting' => [
			[
				'title' => __('Font menu', 'cms'),
				'id' => 'menu_font',
				'type' => 'font',
				'content' => [
					'font-size' => '15',
					'font-family' => '',
					'line-height' => '1.6',
					'weight' => '',
					'capitals' => '',
				],
				'setting' => [
					'max_font_size' => '20',
				],
				'onedit' => [
					'action' => 'change_font',
					'target' => ' .menu > li > a',
				],
			],
			[
				'name' => __('Barva textu', 'cms'),
				'id' => 'menu_color',
				'type' => 'color',
				'content' => '#575757',
				'onedit' => [
					'action' => 'change_styles',
					'css' => '--menu-item-color',
					'target' => ':root',
				],
			],
			[
				'name' => __('Barva aktivní položky', 'cms'),
				'id' => 'menu_active_color',
				'type' => 'color',
				'content' => '#158ebf',
				'onedit' => [
					'action' => 'change_menu_color',
					'css' => '--menu-item-active-color',
					'target' => ':root',
				],
			],
			[
				'name' => __('Barva pozadí podmenu', 'cms'),
				'id' => 'menu_submenu_bg',
				'type' => 'color',
				'content' => '#fff',
				//'show_group' => 'menu_style',
				//'show_val' => '1,2,3,4,8,9',
				'show_group' => 'header_appearanace',
				'show_val' => 'type1,type1b,type1c,type12,type11,type6',
				'onedit' => [
					'action' => 'change_menu_color',
					'css' => '--menu-item-submenu-color',
					'target' => ':root',
				],
			],
			[
				'name' => __('Pozadí menu', 'cms'),
				'id' => 'menu_bg',
				'type' => 'color',
				'content' => '#121212',
				'show_group' => 'header_appearanace',
				'show_val' => 'type5,type8,type9,type10,type13',
				'onedit' => [
					'action' => 'change_styles',
					'css' => '--menu-background-color',
					'target' => ':root',
				],
			],
		],
	],
	[
		'id' => 'menu_setting',
		'type' => 'toggle_group',
		'title' => __('Tlačítka', 'cms'),
		'setting' => [
			[
				'id' => 'show_primary_button',
				'type' => 'switch',
				'label' => __('Zobrazit primární tlačítko', 'cms'),
				'show' => 'show_primary_button',
				'onedit' => [
					'action' => 'reload',
				],
			],
			[
				'id' => 'primary_button_text',
				'title' => __('Text primárního tlačítka', 'cms_ve'),
				'type' => 'text',
				'content' => __('Text tlačítka', 'cms_ve'),
				'onedit' => [
					'action' => 'change_text',
					'target' => ' .mw_head_primary_button .ve_but_text',
				],
				'show_group' => 'show_primary_button',
				'show_val' => '1',
			],
			[
				'id' => 'primary_button_style',
				'title' => __('Vzhled primárního tlačítka', 'cms_ve'),
				'tooltip' => __('Nastavení vzhledu tlačítek naleznete v nastavení vzhledu webu.', 'cms_ve'),
				'type' => 'button',
				'content' => [
					'button_size' => 'small',
				],
				'onedit' => [
					'action' => 'change_button',
					'target' => ' .mw_head_primary_button',
				],
				'show_group' => 'show_primary_button',
				'show_val' => '1',
			],
			[
				'name' => __('Odkaz primárního tlačítka', 'cms'),
				'id' => 'primary_button_link',
				'type' => 'page_link',
				'onedit' => [
					'action' => 'change_link',
					'target' => ' .mw_head_primary_button',
				],
				'show_group' => 'show_primary_button',
				'show_val' => '1',
			],
			[
				'id' => 'show_secondary_button',
				'type' => 'switch',
				'label' => __('Zobrazit sekundární tlačítko', 'cms'),
				'show' => 'show_secondary_button',
				'onedit' => [
					'action' => 'reload',
				],
			],
			[
				'id' => 'secondary_button_text',
				'title' => __('Text sekundárního tlačítka', 'cms_ve'),
				'type' => 'text',
				'content' => __('Text tlačítka', 'cms_ve'),
				'onedit' => [
					'action' => 'change_text',
					'target' => ' .mw_head_secondary_button .ve_but_text',
				],
				'show_group' => 'show_secondary_button',
				'show_val' => '1',
			],
			[
				'id' => 'secondary_button_style',
				'title' => __('Vzhled sekundárního tlačítka', 'cms_ve'),
				'tooltip' => __('Nastavení vzhledu tlačítek naleznete v nastavení vzhledu webu.', 'cms_ve'),
				'type' => 'button',
				'content' => [
					'button_size' => 'small',
				],
				'onedit' => [
					'action' => 'change_button',
					'target' => ' .mw_head_secondary_button',
				],
				'show_group' => 'show_secondary_button',
				'show_val' => '1',
			],
			[
				'name' => __('Odkaz sekundárního tlačítka', 'cms'),
				'id' => 'secondary_button_link',
				'type' => 'page_link',
				'onedit' => [
					'action' => 'change_link',
					'target' => ' .mw_head_secondary_button',
				],
				'show_group' => 'show_secondary_button',
				'show_val' => '1',
			],
		],
	],
	[
		'id' => 'header_icons_set',
		'type' => 'toggle_group',
		'title' => __('Ikonky', 'cms'),
		'setting' => [
			[
				'id' => 'header_icons',
				'type' => 'multielement',
				'open' => isset($_GET['setting']) ? 'under' : 'inline',
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
							'icon' => 'facebook',
						],
						'onedit' => [
							'action' => 'change_icon_simple',
							'target' => ' .mw_header_icons a[qt]',
						],
					],
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
				'id' => 'header_icons_size',
				'title' => __('Velikost ikon', 'cms'),
				'type' => 'slider',
				'setting' => [
					'min' => '10',
					'max' => '40',
					'unit' => 'px',
				],
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'font-size',
					'target' => ' .mw_header_icons',
				],
				'content' => '20',
			],
		],
	],
	[
		'id' => 'advanced_setting',
		'type' => 'toggle_group',
		'title' => __('Pokročilé nastavení', 'cms'),
		'setting' => [

			[
				'id' => 'before_header',
				'title' => __('Obsah před hlavičkou', 'cms'),
				'type' => 'weditor',
				'setting' => [
					'post_type' => 've_header',
					'install' => 'weditorWithTemplate',
					'texts' => [
						'empty' => __(' - Bez obsahu - ', 'cms'),
						'edit' => __('Upravit vybraný obsah', 'cms'),
						'duplicate' => __('Duplikovat vybraný obsah', 'cms'),
						'create' => __('Vytvořit nový obsah', 'cms'),
						'delete' => __('Smazat vybraný obsah', 'cms'),
					],
				],
				'onedit' => [
					'action' => 'reload',
				],
			],
			[
				'title' => __('Spodní ohraničení', 'cms'),
				'id' => 'border-bottom',
				'type' => 'border',
				'content' => [
					'size' => '',
					'color' => '#eeeeee',
					'transparency' => '1',
				],
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'border-bottom',
				],
			],
			[
				'name' => __('Obrázek na pozadí hlavičky', 'cms'),
				'id' => 'background_image',
				'type' => 'bgimage',
				'hide' => ['paralax'],
				'content' => [
					'mobile_hide' => '',
					'cover' => '1',
				],
				'onedit' => [
					'action' => 'change_background',
					'target' => ' .header_background_container',
				],
			],
			/*
			array(
				'name' => __('Šířka obsahu hlavičky', 'cms'),
				'id' => 'header_width',
				'type' => 'size',
				'content' => array(
					'size' => '',
					'unit' => 'px'
				),
			),*/
			[
				'title' => __('Šířka obsahu hlavičky', 'cms'),
				'id' => 'header_width_preset',
				'type' => 'select',
				'options' => [
					['value' => '', 'name' => __('Defaultní', 'cms')],
					['value' => '970px', 'name' => __('Klasická (970px)', 'cms')],
					['value' => '1024px', 'name' => __('Širší (1024px)', 'cms')],
					['value' => '1200px', 'name' => __('Široká (1200px)', 'cms')],
					['value' => '90%', 'name' => __('Přes celou šířku (90%)', 'cms')],
				],
				'content' => '',
				'onedit' => [
					'action' => 'change_styles',
					'css' => '--header-width',
					'target' => ':root',
				],
			],
		],
	],
	[
		'id' => 'fixed_header',
		'type' => 'toggle_group',
		'action' => 'reload',
		'title' => __('Fixní hlavička', 'cms'),
		'checkbox' => 0,
		'setting' => [
			[
				'content' => __('Po odskrolování změnit nastavení hlavičky podle následujících parametrů.', 'cms'),
				'type' => 'info',
			],
			[
				'name' => __('Barva pozadí hlavičky', 'cms'),
				'id' => 'background_color_fix',
				'type' => 'background',
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'background',
					'target' => '.ve_fixed_header_scrolled',
				],
				'content' => [
					'color1' => '',
					'transparency1' => '1',
					'rgba1' => '',
					'color2' => '',
					'transparency2' => '1',
					'rgba2' => '',
					'gradient' => '0',
				],
			],
			[
				'name' => __('Barva textového loga', 'cms'),
				'id' => 'logo_color_fix',
				'type' => 'color',
				'content' => '',
				'onedit' => [
					'action' => 'change_styles',
					'css' => 'color',
					'target' => '.ve_fixed_header_scrolled #site_title',
				],
			],
			[
				'name' => __('Barva textu menu', 'cms'),
				'id' => 'fixed_menu_color',
				'type' => 'color',
				'content' => '',
				'onedit' => [
					'action' => 'change_styles',
					'css' => '--fixed-menu-item-color',
					'target' => ':root',
				],
			],
			[
				'name' => __('Barva aktivní položky', 'cms'),
				'id' => 'fixed_menu_active_color',
				'type' => 'color',
				'content' => '',
				'onedit' => [
					'action' => 'change_menu_color',
					'css' => '--fixed-menu-item-active-color',
					'target' => ':root',
				],
			],
			[
				'id' => 'header_padding_fix',
				'title' => __('Odsazení obsahu hlavičky', 'cms'),
				'type' => 'slider',
				'setting' => [
					'min' => '0',
					'max' => '80',
					'unit' => 'px',
				],
				'onedit' => [
					'action' => 'change_tb_padding',
					'target' => '.ve_fixed_header_scrolled #header_in',
				],
				'content' => '20',
			],
			[
				'id' => 'header_shadow_fix',
				'type' => 'switch',
				'label' => __('Zobrazit stín', 'cms'),
				'onedit' => [
					'action' => 'toggle_class',
					'class' => 've_fixed_with_shadow',
				],
			],
			[
				'id' => 'header_desktop_only_fix',
				'type' => 'switch',
				'label' => __('Nezobrazovat fixní hlavičku na malých obrazovkách', 'cms_ve'),
				'onedit' => [
					'action' => 'toggle_class',
					'class' => 've_fixed_desktop_only',
				],
			],
		],
	],

];

MW()->container['footer_setting'] = [
	[
		'id' => 'footer_content',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Obsah patičky', 'cms'),
		'setting' => [
			[
				'id' => 'custom_footer',
				'title' => __('Obsah patičky', 'cms'),
				'type' => 'weditor',
				'setting' => [
					'post_type' => 'cms_footer',
					'install' => 'weditorWithTemplate',
					'texts' => [
						'empty' => __(' - Bez patičky - ', 'cms'),
						'edit' => __('Upravit vybranou patičku', 'cms'),
						'duplicate' => __('Duplikovat vybranou patičku', 'cms'),
						'create' => __('Vytvořit novou patičku', 'cms'),
						'delete' => __('Smazat vybranou patičku', 'cms'),
					],
				],
				'tooltip' => __('Obsah patičky můžete vytvářet nebo editovat pomocí vizuálního editoru.', 'cms'),
				'onedit' => [
					'action' => 'reload',
				],
			],
		],
	],
	[
		'id' => 'hide_footer_end',
		'type' => 'toggle_group',
		'invert' => true,
		'title' => __('Zobrazit zápatí', 'cms'),
		'checkbox' => 1,
		'action' => 'reload',
		'setting' => [
			[
				'name' => __('Vzhled zápatí', 'cms'),
				'id' => 'appearance',
				'type' => 'imageselect',
				'list' => 'footers',
				'content' => 'type1',
				'onedit' => [
					'action' => 'reload',
				],
			],
			[
				'name' => __('Copyright text', 'cms'),
				'id' => 'text',
				'type' => 'text',
				'onedit' => [
					'action' => 'change_default_text_footer',
					'target' => ' #site_copyright',
					'setting' => '&copy; ' . date('Y') . ' ' . get_bloginfo('name'),
				],
				'tooltip' => __('Pokud chcete v textu zobrazovat aktuální rok, vložte řetězec {current_year}', 'cms'),
			],
			[
				'name' => __('Menu', 'cms'),
				'id' => 'menu',
				'type' => 'selectmenu',
				'onedit' => [
					'action' => 'reload',
				],
			],
			[
				'name' => __('Barva pozadí', 'cms'),
				'id' => 'background_color',
				'type' => 'background',
				'content' => [
					'color1' => '',
					'transparency1' => '1',
					'rgba1' => '',
					'color2' => '',
					'transparency2' => '1',
					'rgba2' => '',
					'gradient' => '0',
				],
				'onedit' => [
					'action' => 'change_background_color',
					'target' => ' .footer_end',
				],
			],

			[
				'title' => __('Font', 'cms'),
				'id' => 'font',
				'type' => 'font',
				'content' => [
					'font-size' => '14',
					'font-family' => '',
					'weight' => '',
					'color' => '#7a7a7a',
				],
				'setting' => [
					'max_font_size' => '20',
				],
				'onedit' => [
					'action' => 'change_font',
					'css' => 'background',
					'target' => ' .footer_end',
				],
			],

			[
				'name' => __('Obrázek na pozadí', 'cms'),
				'id' => 'background_image',
				'type' => 'bgimage',
				'hide' => ['efect'],
				'onedit' => [
					'action' => 'change_background',
					'target' => ' .footer_end',
				],
			],
		],
	],
	[
		'id' => 'footer_advanced_setting',
		'type' => 'toggle_group',
		'title' => __('Pokročilé nastavení', 'cms'),
		'setting' => [
			/*
						array(
							'name' => __('Šířka patičky', 'cms'),
							'id' => 'footer_width',
							'type' => 'size',
							'content' => array(
								'size' => '',
								'unit' => 'px'
							),
						),*/
			[
				'title' => __('Šířka obsahu patičky', 'cms'),
				'id' => 'footer_width_preset',
				'type' => 'select',
				'options' => [
					['value' => '', 'name' => __('Defaultní', 'cms')],
					['value' => '970px', 'name' => __('Klasická (970px)', 'cms')],
					['value' => '1024px', 'name' => __('Širší (1024px)', 'cms')],
					['value' => '1200px', 'name' => __('Široká (1200px)', 'cms')],
					['value' => '90%', 'name' => __('Přes celou šířku (90%)', 'cms')],
				],
				'content' => '',
				'onedit' => [
					'action' => 'change_styles',
					'css' => '--footer-width',
					'target' => ':root',
				],
			],
		],
	],
];

MW()->container['appearance_setting'] = [
	[
		'id' => 'background_setting',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Pozadí', 'cms'),
		'setting' => [
			[
				'name' => __('Barva pozadí', 'cms'),
				'id' => 'background_color',
				'type' => 'color',
				'content' => '#ebebeb',
			],
			[
				'name' => __('Obrázek na pozadí', 'cms'),
				'id' => 'background_image',
				'type' => 'bgimage',
				'hide' => ['paralax'],
				'content' => [
					'pattern' => '',
					'cover' => '1',
					'efect' => 'fixed',
					'overlay_color' => [
						'color' => '#000000',
						'transparency' => '0.2',
						'rgba' => 'rgba(0, 0, 0, 0.2)',
					],
				],
			],
		],
	],
	[
		'id' => 'text_setting',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Formátování textů', 'cms'),
		'setting' => [
			[
				'title' => __('Font nadpisů', 'cms'),
				'id' => 'title_font',
				'type' => 'font',
				'content' => [
					'font-family' => 'Open Sans',
					'weight' => '600',
					'color' => '',
					'line-height' => '1.2',
					'capitals' => '',
				],
			],
			[
				'title' => __('Font podnadpisů', 'cms'),
				'id' => 'subtitle_font',
				'type' => 'font',
				'content' => [
					'font-family' => 'Open Sans',
					'weight' => '700',
					'color' => '',
					'line-height' => '1.2',
					'capitals' => '',
				],
			],
			[
				'title' => __('Font textů', 'cms'),
				'id' => 'font',
				'type' => 'font',
				'content' => [
					'font-size' => '16',
					'font-family' => 'Open Sans',
					'line-height' => '1.7',
					'weight' => '400',
					'color' => '#111111',
				],
				'setting' => [
					'max_font_size' => '25',
				],
			],
			[
				'name' => __('Barva inverzních textů', 'cms'),
				'id' => 'inverse_text_color',
				'type' => 'color',
				'content' => '#ffffff',
			],
			[
				'name' => __('Barva odkazů', 'cms'),
				'id' => 'link_color',
				'type' => 'color',
				'content' => '#158ebf',
			],
			[
				'name' => __('Barva odkazů po najetí myši', 'cms'),
				'id' => 'hover_color',
				'type' => 'color',
				'content' => '',
			],
		],
	],
	[
		'id' => 'element_text_setting',
		'type' => 'toggle_group',
		'title' => __('Formátování textu v textovém elementu', 'cms'),
		'setting' => [
			[
				'title' => __('Nadpis 1 (H1)', 'cms'),
				'id' => 'h1_font',
				'type' => 'font',
				'content' => [
					'font-size' => '40',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 2 (H2)', 'cms'),
				'id' => 'h2_font',
				'type' => 'font',
				'content' => [
					'font-size' => '30',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 3 (H3)', 'cms'),
				'id' => 'h3_font',
				'type' => 'font',
				'content' => [
					'font-size' => '20',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 4 (H4)', 'cms'),
				'id' => 'h4_font',
				'type' => 'font',
				'content' => [
					'font-size' => '16',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 5 (H5)', 'cms'),
				'id' => 'h5_font',
				'type' => 'font',
				'content' => [
					'font-size' => '16',
					'color' => '',
				],
			],
			[
				'title' => __('Nadpis 6 (H6)', 'cms'),
				'id' => 'h6_font',
				'type' => 'font',
				'content' => [
					'font-size' => '16',
					'color' => '',
				],
			],
			[
				'id' => 'li',
				'name' => __('Styl odrážek', 'cms'),
				'type' => 'imageselect',
				'content' => '1',
				'list' => 'list_icons',
			],
		],
	],
	[
		'id' => 'advanced_setting',
		'type' => 'toggle_group',
		'title' => __('Pokročilé', 'cms'),
		'setting' => [
			[
				'title' => __('Šířka stránky', 'cms'),
				'id' => 'page_width_preset',
				'type' => 'select',
				'options' => [
					['value' => '970px', 'name' => __('Klasická (970px)', 'cms')],
					['value' => '1024px', 'name' => __('Širší (1024px)', 'cms')],
					['value' => '1200px', 'name' => __('Široká (1200px)', 'cms')],
					['value' => '90%', 'name' => __('Přes celou šířku (90%)', 'cms')],
				],
				'content' => '970px',
			],
		],
	],
];


/* Setting
********************************************
********************************************
********************************************
*/

// setting
mwSetting()->addGroup([
	'id' => 'web_option',
	'icon' => 'settings',
	'title' => __('Web', 'cms'),
	'home' => 'web_option_basic',
	'order' => 30,
]);

mwSetting()->addPage([
	'id' => 'web_option_basic',
	'group' => 'web_option',
	'title' => __('Nastavení', 'cms'),
]);
mwSetting()->addPage([
	'id' => 'seo_basic',
	'group' => 'web_option',
	'title' => __('SEO', 'cms'),
	'parent' => 'web_option_basic',
]);
if (MW()->edit_mode && !MW()->getLicense()->isHosting()) {
	mwSetting()->addPage([
		'id' => 'web_option_license',
		'group' => 'web_option',
		'title' => __('Licence', 'cms'),
		'parent' => 'web_option_basic',
		'reload_on_save' => true,
	]);
}
mwSetting()->addPage([
	'id' => 'web_option_affiliate',
	'group' => 'web_option',
	'title' => __('Affiliate', 'cms'),
	'parent' => 'web_option_basic',
]);
mwSetting()->addPage([
	'id' => 'web_option_smtp',
	'group' => 'web_option',
	'title' => __('E-mail (SMTP)', 'cms'),
	'parent' => 'web_option_basic',
]);
mwSetting()->addPage([
	'id' => 'social_option',
	'group' => 'web_option',
	'title' => __('Sociální sítě', 'cms'),
	'parent' => 'web_option_basic',
]);
mwSetting()->addPage([
	'id' => 've_popups',
	'group' => 'web_option',
	'title' => __('Pop-upy webu', 'cms'),
	'parent' => 'web_option_basic',
]);

// appearance
mwSetting()->addPage([
	'id' => 've_appearance',
	'group' => 'web_option',
	'icon' => 'layout',
	'title' => __('Vzhled', 'cms'),
]);
mwSetting()->addPage([
	'id' => 've_header',
	'group' => 'web_option',
	'title' => __('Hlavička webu', 'cms'),
	'parent' => 've_appearance',
]);
mwSetting()->addPage([
	'id' => 've_footer',
	'group' => 'web_option',
	'title' => __('Patička webu', 'cms'),
	'parent' => 've_appearance',
]);
mwSetting()->addPage([
	'id' => 've_buttons',
	'group' => 'web_option',
	'title' => __('Tlačítka webu', 'cms'),
	'parent' => 've_appearance',
]);
mwSetting()->addPage([
	'id' => 'mw_custom_fonts',
	'group' => 'web_option',
	'title' => __('Vlastní fonty', 'cms'),
	'parent' => 've_appearance',
]);

mwSetting()->registerPageSettingType('web_tempaltes', [
	'static_class' => 'mwInstallator',
	'function' => 'printWebTemplateSelector',
]);
mwSetting()->addPage([
	'id' => 'mw_web_template',
	'group' => 'web_option',
	'title' => __('Šablona webu', 'cms'),
	'parent' => 've_appearance',
	'type' => 'web_tempaltes',
]);

mwSetting()->registerPageSettingType('web_import_export', [
	'static_class' => 'mwInstallator',
	'function' => 'printImportExport',
]);
mwSetting()->addPage([
	'id' => 'mw_web_import_export',
	'group' => 'web_option',
	'title' => __('Import/export webu', 'cms'),
	'parent' => 've_appearance',
	'type' => 'web_import_export',
	'alert_on_leave' => false,
]);

// gdpr
mwSetting()->addPage([
	'id' => 'web_option_gdpr',
	'group' => 'web_option',
	'icon' => 'briefcase',
	'title' => __('GDPR', 'cms'),
]);

mwSetting()->addPage([
	'id' => 'web_option_others',
	'group' => 'web_option',
	'title' => __('Cookie lišta', 'cms'),
	'parent' => 'web_option_gdpr',
]);

mwSetting()->addPage([
	'id' => 'mw_script_blocker',
	'group' => 'web_option',
	'title' => __('Blokování pluginů', 'cms'),
	'service_class' => 'mwSettingPageService_scriptBlocker',
	'parent' => 'web_option_gdpr',
]);

// custom codes
mwSetting()->addPage([
	'id' => 'mw_web_codes',
	'group' => 'web_option',
	'icon' => 'code',
	'title' => __('Vlastní kódy', 'cms'),
]);

// users
mwSetting()->addPage([
	'id' => 'users',
	'group' => 'web_option',
	'icon' => 'users',
	'title' => __('Uživatelé', 'cms'),
	'type' => 'list',
]);

// events
mwSetting()->addPage([
	'id' => MW_EVENT_SLUG,
	'group' => 'web_option',
	'icon' => 'calendar',
	'title' => __('Kalendář akcí', 'cms'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => MW_EVENT_CAT_SLUG,
	'group' => 'web_option',
	'parent' => MW_EVENT_SLUG,
	'title' => __('Kategorie akcí', 'cms'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => 'media_library',
	'group' => 'web_option',
	'icon' => 'image',
	'title' => __('Knihovna médií', 'cms'),
	'type' => 'link',
	'link' => admin_url('upload.php'),
]);
mwSetting()->addPage([
	'id' => 'plugins',
	'group' => 'web_option',
	'icon' => 'cpu',
	'title' => __('Pluginy', 'cms'),
	'type' => 'link',
	'link' => admin_url('plugins.php'),
]);

// connection
mwSetting()->addGroup([
	'id' => 'comments',
	'icon' => 'message-square',
	'title' => __('Komentáře', 'cms'),
	'home' => 'comments',
	'order' => 22,
]);
mwSetting()->addPage([
	'id' => 'comments',
	'group' => 'comments',
	'icon' => 'message-square',
	'title' => __('Komentáře', 'cms'),
	'info_function' => function () {
		$count = mwComment::getNotApprovedCount();

		return $count ? '<span class="mw_setting_menu_count">' . $count . '</span>' : '';
	},
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => 'comments_setting',
	'group' => 'comments',
	'icon' => 'settings',
	'title' => __('Nastavení', 'cms'),
	'service_class' => 'mwSettingPageService_comments',
	//'type' => 'list',
]);

// connection
mwSetting()->addGroup([
	'id' => 'connections',
	'icon' => 'git-pull-request',
	'title' => __('Propojení aplikací', 'cms'),
	'home' => 've_connect',
	'order' => 25,
]);
mwSetting()->addPage([
	'id' => 've_connect',
	'group' => 'connections',
	'icon' => 'git-pull-request',
	'title' => __('Propojení aplikací', 'cms'),
	'type' => 'connections',
]);

mwSetting()->addPageSetting('web_option_basic', [
	[
		'type' => 'box',
		'setting' => [
			[
				'name' => __('Název webu', 'cms'),
				'id' => 'blogname',
				'type' => 'text',
				'save' => 'option',
			],
			[
				'name' => __('Popisek webu', 'cms'),
				'id' => 'blogdescription',
				'type' => 'text',
				'save' => 'option',
			],
			[
				'name' => __('Jazyk webu', 'cms'),
				'id' => 'WPLANG',
				'type' => 'language_select',
				'save' => 'option',
			],
			[
				'name' => __('Administrátorský email', 'cms'),
				'id' => 'admin_email',
				'type' => 'text',
				'tooltip' => __('Tato emailová adresa se používá k administrátorským účelům.', 'cms'),
				'save' => 'option',
			],
			[
				'name' => __('Zástupce webu (favicon)', 'cms'),
				'id' => 'favicon',
				'type' => 'image_url',
				'tooltip' => __('Favicon je ikona webu zobrazující se vedle URL adresy nebo v záložce prohlížeče vedle názvu stránky. Nahrajte ikonu ve formátu .png, ideálně ve velikosti 180 ×180 px.', 'cms'),
			],

			[
				'name' => __('Vlastní chybová stránka (404)', 'cms'),
				'id' => '404page',
				'type' => 'selectpage',
				'add_button' => true,
				'edit_button' => true,
				'whisperer' => true,
				'tooltip' => __('Tato stránka se zobrazí v případě že uživatel zadá adresu stránky, která neexistuje. Pokud žádnou stránku nevyberete bude se zobrazovat defaultní stránka.', 'cms'),
			],
		],
	],
	[
		'type' => 'box',
		'title' => __('Datum a čas', 'cms'),
		'setting' => [
			[
				'name' => __('Časové pásmo', 'cms'),
				'id' => 'timezone_string',
				'type' => 'timezone_select',
				'save' => 'savehook',
				'savehook' => function ($field, $fieldValue, &$fieldSaved) {
					if ($fieldValue && preg_match('/^UTC[+-]/', $fieldValue)) {
						$gmt_offset = $fieldValue;
						$gmt_offset = preg_replace('/UTC\+?/', '', $gmt_offset);
						update_option('gmt_offset', $gmt_offset);
						$fieldValue = '';
					}
					update_option('timezone_string', $fieldValue);
					$fieldSaved = true;
				},
				'tooltip' => __('Vyberte město, které je ve stejném časovém pásmu nebo UTC (Coordinated Universal Time) časový posun.', 'cms'),
			],
			[
				'name' => __('Formát data', 'cms'),
				'id' => 'date_format',
				'type' => 'date_time_format_select',
				'formats' => ['j.n.Y', 'Y-m-d', 'm/d/Y', 'd/m/Y'],
				'save' => 'option',
			],
			[
				'name' => __('Formát času', 'cms'),
				'id' => 'time_format',
				'type' => 'date_time_format_select',
				'formats' => ['G:i', 'g:i A', 'H:i'],
				'save' => 'option',
			],
		],
	],
	[
		'type' => 'box',
		'title' => __('Ověření webu', 'cms'),
		'setting' => [
			[
				'name' => __('Google Site verification kód', 'cms'),
				'id' => 'site_verification',
				'type' => 'text',
				'tooltip' => __('Jedna z možností jak ověřit u googlu že jste vlastníkem tohoto webu, například pro napojení na google analytics, je pomocí google site verification kódů. Tento kód, který vám vygeneruje google, zadejte do tohoto pole. <a href="https://napoveda.mioweb.cz/article/698-jak-vlozit-overovaci-kod-google" target="_blank">Návod na získání a vložení site verification kódu</a>', 'cms'),
			],
		],
	],
]);

mwSetting()->addPageSetting('mw_web_codes', [
	[
		'id' => '',
		'type' => 'box',
		'title' => __('Kódy pro celý web', 'cms'),
		'setting' => [
			[
				'id' => 'codes',
				'type' => 'code_list',
			],
		],
	],
	[
		'id' => 'custom_css',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('CSS styly pro celý web', 'cms'),
		'setting' => [
			[
				'id' => 'css',
				'type' => 'textarea',
				'rows' => 8,
				'desc' => __('Vložením vlastních CSS (kaskádových) stylů můžete ovlivnit vzhled webu.', 'cms'),
			],
		],
	],
]);
mwSetting()->addPageSetting('web_option_license', [
	[
		'type' => 'box',
		'setting' => [
			[
				'name' => __('Vaše licenční číslo', 'cms'),
				'id' => 'license',
				'type' => 'license',
			],
		],
	],
]);
mwSetting()->addPageSetting('web_option_affiliate', [
	[
		'type' => 'box',
		'setting' => [
			[
				'name' => __('Váš affiliate odkaz', 'cms'),
				'id' => 'affiliate_link',
				'type' => 'text',
				'content' => __('https://mioweb.cz', 'cms'),
				'desc' => __('Zde vložte odkaz na https://mioweb.cz s vaším affiliate kódem. Odkaz se zobrazí v patičce webu a pokaždé, když se přes něj někdo proklikne na náš web a koupí Mioweb, dostanete z prodeje provizi. Pokud necháte pole prázdné, text v patičce zmizí. Jako affiliate partner se můžete registrovat v <a target="_blank" href="https://www.mioweb.cz/money/">našem partnerském programu</a>.', 'cms'),
			],
		],
	],
]);
mwSetting()->addPageSetting('web_option_smtp', [
	[
		'name' => '',
		'id' => 'use_smtp',
		'type' => 'switch',
		'show' => 'smtp',
		'label' => __('Použít k zasílání e-mailů vlastní SMTP server.', 'cms'),
	],
	[
		'id' => 'smtp_setting',
		'type' => 'toggle_group',
		'title' => __('Nastavení SMTP', 'cms_blog'),
		'open' => true,
		'show_group' => 'smtp',
		'show_val' => '1',
		'setting' => [
			[
				'name' => __('E-mailová adresa', 'cms'),
				'id' => 'smtp_email',
				'type' => 'text',
				'content' => '',
				'desc' => __('Zadejte adresu, ze které chcete, aby byly e-maily posílány.', 'cms'),
			],
			[
				'name' => __('Jméno', 'cms'),
				'id' => 'smtp_name',
				'type' => 'text',
				'content' => '',
				'desc' => __('Zadejte jméno, které se má zobrazovat v kolonce „Od:“.', 'cms'),
			],
			[
				'name' => __('SMTP host', 'cms'),
				'id' => 'smtp_host',
				'type' => 'text',
				'content' => '',
			],
			[
				'name' => __('SMTP zabezpečení', 'cms'),
				'id' => 'smtp_secure',
				'type' => 'radio',
				'options' => [
					'' => __('Nezabezpečené', 'cms'),
					'ssl' => __('SSL', 'cms'),
					'tls' => __('TLS', 'cms'),
				],
				'content' => 'ssl',
			],
			[
				'name' => __('SMTP port', 'cms'),
				'id' => 'smtp_port',
				'type' => 'text',
				'content' => '',
			],
			[
				'name' => __('SMTP autentifikace', 'cms'),
				'id' => 'smtp_authentication',
				'type' => 'radio',
				'options' => [
					'yes' => __('Ano', 'cms'),
					'no' => __('Ne', 'cms'),
				],
				'content' => 'yes',
				'show' => 'auth',
			],
			[
				'name' => __('Přihlašovací jméno', 'cms'),
				'id' => 'smtp_login',
				'type' => 'text',
				'content' => '',
				'show_group' => 'auth',
				'show_val' => 'yes',
			],
			[
				'name' => __('Heslo', 'cms'),
				'id' => 'smtp_password',
				'type' => 'password',
				'content' => '',
				'show_group' => 'auth',
				'show_val' => 'yes',
			],
		],
	],
	[
		'id' => 'smtp_test',
		'type' => 'toggle_group',
		'title' => __('Test nastavení SMTP', 'cms_blog'),
		'show_group' => 'smtp',
		'show_val' => '1',
		'setting' => [
			[
				'id' => 'smtp_test_info',
				'name' => '',
				'type' => 'info',
				'content' => __('Pokud chcete otestovat správnost nastavení SMTP, zadejte níže emailovou adresu a pošlete na ní testovací email. Pokud se email podaří odeslat, je vše nastaveno správně. Pozor, před vyzkoušením je potřeba nejdříve nastavení SMTP uložit.', 'cms'),
			],
			[
				'name' => __('Poslat testovací email na emailovou adresu:', 'cms'),
				'id' => 'smtp_test',
				'type' => 'smtp_test',
				'content' => '',
			],
		],
	],
]);
mwSetting()->addPageSetting('web_option_others', [
	[
		'id' => 'use_cookie',
		'type' => 'switch',
		'show' => 'cookie_info',
		'label' => __('Zobrazit návštěvníkům lištu se souhlasem použití cookies na tomto webu', 'cms'),
	],
	/*
	[
		'id' => 'cookie_info',
		'type' => 'info',
		'show_group' => 'cookie_info',
		'show_val' => '0',
		'content' => __('U samotného Miowebu není potřeba mít tuto možnost aktivní. Mioweb používá cookie pouze v administraci webu a při A/B testování a do cookie neukládá žádné osobní údaje. Některé skripty (například reklamy) nebo pluginy ale mohou pracovat s osobními údaji v cookie a podle evropského práva je nutné o tomto uživatele informovat.', 'cms'),
	],
	*/
	[
		'id' => 'cookie_setting_tabs',
		'type' => 'tabs',
		'show_group' => 'cookie_info',
		'show_val' => '1',
		'tabs' => [
			'cookie_banner' => [
				'name' => __('Cookie lišta', 'cms'),
				'icon' => 'image',
				'setting' => [
					[
						'id' => '',
						'type' => 'box',
						'setting' => [
							[
								'name' => __('Nadpis', 'cms'),
								'id' => 'main_title',
								'type' => 'text',
								'content' => __('Tato stránka používá cookies', 'cms'),
							],
							[
								'name' => __('Informační text', 'cms'),
								'id' => 'main_text',
								'type' => 'textarea',
								'content' => __('Na stránkách používáme soubory cookies. Některé jsou nezbytné pro fungování stránek, jiné nám umožňují poskytnout vám lepší zkušenost při návštěvě našich stránek nebo zobrazování reklamy, pomáhají nám analyzovat návštěvnost a stránky zlepšovat.', 'cms'),
							],
							[
								'name' => __('Text tlačítka pro přijmutí všech cookies', 'cms'),
								'id' => 'allow_all_text',
								'type' => 'text',
								'content' => __('Přijmout vše', 'cms'),
							],
							[
								'name' => __('Zobrazení tlačítka pro zamítnutí všech cookies', 'cms'),
								'id' => 'show_deny',
								'show' => 'show_deny',
								'type' => 'status_switch',
								'content' => '1',
								'label' => __('Zobrazit tlačítko pro zamítnutí všech cookies', 'cms'),
							],
							[
								'name' => __('Text tlačítka pro zamítnutí všech cookies', 'cms'),
								'id' => 'deny_all_text',
								'type' => 'text',
								'content' => __('Odmítnout vše', 'cms'),
								'show_group' => 'show_deny',
								'show_val' => '1',
							],
							[
								'name' => __('Stránka s více informacemi', 'cms'),
								'id' => 'cookie_url_info',
								'type' => 'page_link',
								'target' => false,
								'desc' => __('Zde vložte odkaz na vaše obchodní podmínky nebo podmínky užití, které by měly obsahovat také podrobnosti o užití souborů cookie na vašem webu a možnost změnit nastavení. Změnit nastavení mužete návštěvníkům umožnit pomocí elementu Nastavení cookies.', 'cms'),
							],
						],
					],
				],
			],
			'cookie_popup' => [
				'name' => __('Popup s nastavení cookies', 'cms'),
				'icon' => 'image',
				'setting' => [
					[
						'id' => '',
						'type' => 'box',
						'setting' => [
							[
								'name' => __('Nadpis popupu', 'cms'),
								'id' => 'popup_title',
								'type' => 'text',
								'content' => __('Souhlas s používáním cookies', 'cms'),
							],
							[
								'name' => __('Informační text', 'cms'),
								'id' => 'popup_text',
								'type' => 'textarea',
								'rows' => 14,
								'content' => __('Cookies jsou malé soubory, které se dočasně ukládají ve vašem počítači a pomáhají nám k lepší uživatelské zkušenosti na našich stránkách. Cookies používáme k personalizaci obsahu stránek a reklam, poskytování funkcí sociálních sítí a k analýze návštěvnosti. Informace o vašem používání našich stránek také sdílíme s našimi partnery v oblasti sociálních sítí, reklamy a analýzy, kteří je mohou kombinovat s dalšími informacemi, které jste jim poskytli nebo které shromáždili při vašem používání jejich služeb.

Ze zákona můžeme na vašem zařízení ukládat pouze soubory cookie, které jsou nezbytně nutné pro provoz těchto stránek. Pro všechny ostatní typy souborů cookie potřebujeme vaše svolení. Budeme vděční, když nám ho poskytnete a pomůžete nám tak, naše stránky a služby zlepšovat. Svůj souhlas s používáním cookies na našem webu můžete samozřejmě kdykoliv změnit nebo odvolat.', 'cms'),
							],
						],
					],
					[
						'id' => '',
						'type' => 'box',
						'title' => __('Nezbytné cookies', 'cms'),
						'setting' => [
							[
								'name' => __('Nadpis', 'cms'),
								'id' => 'necessary_title',
								'type' => 'text',
								'content' => __('Nezbytné', 'cms'),
							],
							[
								'name' => __('Krátký text', 'cms'),
								'id' => 'necessary_description',
								'type' => 'text',
								'content' => __('aby stránky fungovaly, jak mají.', 'cms'),
							],
							[
								'name' => __('Informační text', 'cms'),
								'id' => 'necessary_text',
								'type' => 'textarea',
								'content' => __('Nezbytné soubory cookie pomáhají učinit webové stránky použitelnými tím, že umožňují základní funkce, jako je navigace na stránce a přístup k zabezpečeným oblastem webové stránky. Bez těchto souborů cookie nemůže web správně fungovat.', 'cms'),
							],
						],
					],
					[
						'id' => '',
						'type' => 'box',
						'title' => __('Preferenční cookies', 'cms'),
						'setting' => [
							[
								'name' => __('Nadpis', 'cms'),
								'id' => 'preferences_title',
								'type' => 'text',
								'content' => __('Preferenční', 'cms'),
							],
							[
								'name' => __('Krátký text', 'cms'),
								'id' => 'preferences_description',
								'type' => 'text',
								'content' => __('abychom si pamatovali vaše preference.', 'cms'),
							],
							[
								'name' => __('Informační text', 'cms'),
								'id' => 'preferences_text',
								'type' => 'textarea',
								'content' => __('Preferenční cookies umožňují, aby si stránka pamatovala informace, které upravují, jak se stránka chová nebo vypadá. Např. vaše přihlášení, obsah košíku, zemi, ze které stránku navštěvujete.', 'cms'),
							],
							[
								'name' => __('Zobrazení', 'cms'),
								'tooltip' => __('Pokud zaškrtnete že tento typ cookies na webu nepoužíváte, bude možnost povolit preferenční cookies v popupu skryta.', 'cms'),
								'id' => 'preferences_hide',
								'type' => 'switch',
								'label' => __('Preferenční cookies na webu nepoužívám', 'cms'),
							],
						],
					],
					[
						'id' => '',
						'type' => 'box',
						'title' => __('Statistické cookies', 'cms'),
						'setting' => [
							[
								'name' => __('Nadpis', 'cms'),
								'id' => 'analytics_title',
								'type' => 'text',
								'content' => __('Statistické', 'cms'),
							],
							[
								'name' => __('Krátký text', 'cms'),
								'id' => 'analytics_description',
								'type' => 'text',
								'content' => __('abychom věděli, co na webu děláte a co zlepšit.', 'cms'),
							],
							[
								'name' => __('Informační text', 'cms'),
								'id' => 'analytics_text',
								'type' => 'textarea',
								'content' => __('Statistické cookies pomáhají provozovateli stránek pochopit, jak návštěvníci stránek stránku používají, aby mohl stránky optimalizovat a nabídnout jim lepší zkušenost. Veškerá data se sbírají anonymně a není možné je spojit s konkrétní osobou. ', 'cms'),
							],
							[
								'name' => __('Zobrazení', 'cms'),
								'tooltip' => __('Pokud zaškrtnete že tento typ cookies na webu nepoužíváte, bude možnost povolit statistické cookies v popupu skryta.', 'cms'),
								'id' => 'analytics_hide',
								'type' => 'switch',
								'label' => __('Statistické cookies na webu nepoužívám', 'cms'),
							],
						],
					],
					[
						'id' => '',
						'type' => 'box',
						'title' => __('Marketingové cookies', 'cms'),
						'setting' => [
							[
								'name' => __('Nadpis', 'cms'),
								'id' => 'marketing_title',
								'type' => 'text',
								'content' => __('Marketingové', 'cms'),
							],
							[
								'name' => __('Krátký text', 'cms'),
								'id' => 'marketing_description',
								'type' => 'text',
								'content' => __('abychom vám ukazovali pouze relevantní reklamu.', 'cms'),
							],
							[
								'name' => __('Informační text', 'cms'),
								'id' => 'marketing_text',
								'type' => 'textarea',
								'content' => __('Marketingové cookies se používají ke sledování pohybu návštěvníků napříč webovými stránkami s cílem zobrazovat jim pouze takovou reklamu, která je pro daného člověka relevantní a užitečná. Veškerá data se sbírají a používají anonymně a není možné je spojit s konkrétní osobou.', 'cms'),
							],
							[
								'name' => __('Zobrazení', 'cms'),
								'tooltip' => __('Pokud zaškrtnete že tento typ cookies na webu nepoužíváte, bude možnost povolit marketingové cookies v popupu skryta.', 'cms'),
								'id' => 'marketing_hide',
								'type' => 'switch',
								'label' => __('Marketingové cookies na webu nepoužívám', 'cms'),
							],
						],
					],
				],
			],
			'appearance' => [
				'name' => __('Vzhled', 'cms'),
				'icon' => 'image',
				'setting' => [
					[
						'id' => '',
						'type' => 'box',
						'setting' => [
							[
								'id' => 'position',
								'title' => __('Umístění cookie lišty', 'cms'),
								'type' => 'select',
								'options' => [
									['name' => __('Dole', 'cms'), 'value' => 'bottom'],
									['name' => __('Dole vlevo', 'cms'), 'value' => 'bottom-left'],
									['name' => __('Dole vpravo', 'cms'), 'value' => 'bottom-right'],
									['name' => __('Nahoře', 'cms'), 'value' => 'top'],
								],
								'content' => 'bottom',
							],
							[
								'id' => 'style',
								'title' => __('Vzhled cookie lišty', 'cms'),
								'type' => 'select',
								'options' => [
									['name' => __('Světlý motiv', 'cms'), 'value' => 'light'],
									['name' => __('Tmavý motiv', 'cms'), 'value' => 'dark'],
								],
								'content' => 'light',
							],
							[
								'id' => 'button_color',
								'title' => __('Barva tlačítek', 'cms'),
								'type' => 'color',
								'content' => '#27a8d7',
							],
						],
					],
				],
			],
		],
	],
]);
mwSetting()->addPageSetting('web_option_gdpr', [
	[
		'id' => '',
		'type' => 'box',
		'setting' => [
			[
				'id' => 'gdpr_info',
				'name' => '',
				'type' => 'info',
				'content' => __('Pod každým formulářem, pomocí kterého vám návštěvník webu může předat své osobní údaje, musíte informovat, jak s těmito údaji budete nakládat.', 'cms'),
			],
			[
				'name' => __('URL stránky se zásadami zpracování osobních údajů', 'cms'),
				'id' => 'gdpr_url',
				'type' => 'page_link',
				'target' => false,
				'desc' => __('Zde vložte odkaz na stránku, která obsahuje podrobnosti o vašich zásadách zpracování osobních údajů.', 'cms'),
			],
			[
				'name' => __('Souhlas se zpracováním osobních údajů', 'cms'),
				'id' => 'gdpr_check',
				'type' => 'switch',
				'label' => __('Vyžadovat souhlas se zásadami zpacování osobních údajů', 'cms'),
			],

		],
	],
	[
		'id' => 'contact_form_setting',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Kontaktní formulář', 'cms'),
		'setting' => [
			[
				'id' => 'contact_form_info',
				'title' => __('Informační text pod kontaktním formulářem', 'cms'),
				'content' => __('Vaše osobní údaje budou použity pouze pro účely vyřešení vašeho dotazu.', 'cms'),
				'type' => 'textarea',
			],
			[
				'id' => 'contact_form_link_text',
				'title' => __('Text odkazu na zásady zpracování osobních údajů', 'cms'),
				'content' => __('Zásady zpracování osobních údajů', 'cms'),
				'type' => 'text',
				'tooltip' => __('Aby se odkaz zobrazoval, je nutné mít vybranou stránku se zásadami zpracování osobních údajů.', 'cms'),
			],
		],
	],
	[
		'id' => 'comments_setting',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Komentáře', 'cms'),
		'setting' => [
			[
				'id' => 'comment_form_info',
				'title' => __('Informační text pod formulářem pro přidání komentáře', 'cms'),
				'content' => __('Vaše osobní údaje budou použity pouze pro účely zpracování tohoto komentáře.', 'cms'),
				'type' => 'textarea',
			],
			[
				'id' => 'comment_form_link_text',
				'title' => __('Text odkazu na zásady zpracování osobních údajů', 'cms'),
				'content' => __('Zásady zpracování osobních údajů', 'cms'),
				'type' => 'text',
				'tooltip' => __('Aby se odkaz zobrazoval, je nutné mít vybranou stránku se zásadami zpracování osobních údajů.', 'cms'),
			],
		],
	],
]);
mwSetting()->addPageSetting('mw_script_blocker', [
	[
		'type' => 'info',
		'content' => __('Pomocí tohoto nastavení můžete zajistit aby pluginy nainstalované na tomto webu neukládaly cookies v rozporu s preferencemi návštěvníka webu. Pluginy u kterých nastavíte "Povolit blokování" budou blokovány podle nastaveného účelu pluginu a podle souhlasu návštěníka, uděleného pomocí cookie lišty.', 'cms'),
	],
	[
		'id' => 'plugins',
		'type' => 'plugin_blocker',
	],
]);

mwSetting()->addPageSetting('social_option', [
	[
		'type' => 'box',
		'title' => __('Facebook', 'cms'),
		'setting' => [
			[
				'name' => '',
				'id' => 'hide_facebook',
				'type' => 'switch',
				'show' => 'facebook',
				'show_type' => 'hide',
				'label' => __('Vypnout Facebook atributy Mioweb šablony', 'cms'),
				'desc' => __('Vypnout Facebook atributy šablony je vhodné, pokud chcete pro nastavení Facebook atributů webu nebo stránek používat některý z Wordpress pluginů.', 'cms'),
			],
			[
				'name' => __('Defaultní Facebook obrázek (og:image)', 'cms'),
				'id' => 'fac_img',
				'type' => 'image_url',
				'desc' => __('Tento obrázek se bude zobrazovat na Facebooku při sdílení jakékoli stránky vašeho webu. V nastavení každé stránky ale můžete nastavit jiný obrázek, který bude jedinečný právě pro danou stránku. Doporučená velikost obrázku je 1200 × 628 px.', 'cms'),
				'show_group' => 'facebook',
				'show_val' => '0',
			],
			[
				'name' => __('Facebook Application ID', 'cms'),
				'id' => 'fac_api',
				'type' => 'text',
			],
			[
				'name' => __('Administrator ID', 'cms'),
				'id' => 'fac_admin_id',
				'type' => 'text',
				'desc' => __('Zde zadejte ID Facebook uživatele, který bude mít oprávnění moderovat Facebook komentáře. Svoje ID získáte například tak, že se v prohlížeči přepnete na svůj facebookový profil. V URL pak změňte řetězec „www“ na „graph“. Zobrazí se seznam údajů, kdy první z nich je ID.', 'cms'),
			],
		],
	],
]);


mwSetting()->addPageSetting('seo_basic', [
	[
		'type' => 'box',
		'setting' => [
			[
				'id' => 'seo',
				'type' => 'switch',
				'label' => __('Vypnout SEO Mioweb šablony', 'cms'),
				'desc' => __('Vypnout SEO šablony je vhodné, pokud chcete pro nastavení SEO atributů webu nebo stránek používat některý z Wordpress pluginů.', 'cms'),
			],
		],
	],
]);



mwSetting()->addPageSetting('ve_popups', MW()->container['popup_setting']);

mwSetting()->addPageSetting('ve_footer', MW()->container['footer_setting']);

mwSetting()->addPageSetting('ve_header', MW()->container['header_setting']);

mwSetting()->addPageSetting('ve_appearance', MW()->container['appearance_setting']);

mwSetting()->addPageSetting('ve_buttons', [
	[
		'id' => 'buttons',
		'type' => 'buttons_editor',
		'content' => [
			'basic' => [
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
			'inverse' => [
				'style' => '12',
				'background_color' => [
					'color1' => '',
					'transparency1' => '',
					'rgba1' => '',
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
				'border-color' => '#ffffff',
				'border_width' => '',
				'hover_effect' => '',
				'hover_color' => [
					'color1' => '#eb1e47',
					'transparency1' => '1.00',
					'rgba1' => 'rgba(235, 30, 71, 1)',
					'color2' => '',
					'transparency2' => '',
					'rgba2' => '',
				],
				'hover_font_color' => '',
				'border_hover-color' => '#eb1e47',
			],
		],
	],
]);

mwSetting()->addPageSetting('mw_custom_fonts', [
	[
		'id' => 'fonts_info',
		'name' => '',
		'type' => 'info',
		'content' => __('Nahrajte vlastní fonty ze souboru, nebo vložte kódy vlastních google fontů. Kódy můžete získat zde: <a href="https://fonts.google.com/" target="_blank">fonts.google.com</a>. Po přidání se tyto fonty objeví ve výběru fontů v nastavení a editaci elementů.', 'cms'),
	],
	[
		'id' => 'fonts',
		'type' => 'multielement',
		'sortable' => false,
		'open' => 'under',
		'style' => 'shadow',
		'texts' => [
			'add' => __('Přidat font', 'cms'),
			'empty' => __('Font', 'cms'),
		],
		'setting' => [
			[
				'id' => 'type',
				'title' => __('Google font, nebo ze souboru', 'cms'),
				'type' => 'select',
				'content' => 'google',
				'options' => [
					['name' => __('Google font', 'cms'), 'value' => 'google'],
					['name' => __('Vlastní font ze souboru', 'cms'), 'value' => 'file'],
				],
				'show' => 'font_type',
			],
			[
				'id' => 'title',
				'title' => __('Název fontu', 'cms'),
				'type' => 'text',
			],
			[
				'id' => 'font_code',
				'title' => __('Kód fontu', 'cms'),
				'type' => 'text',
				'show_group' => 'font_type',
				'show_val' => 'google',
			],
			[
				'id' => 'font_weights',
				'title' => __('Řezy fontu', 'cms'),
				'tooltip' => __('Nahrajte soubory fontu pro jednotlivé řezy a vyberte pro jaký řez je soubor určen.', 'cms'),
				'type' => 'simple_feature',
				'class' => 'mw_simple_feature_fonts',
				'text_add' => __('Přidat řez', 'cms_ve'),
				'fields' => [
					'font_weight_file' => [
						'title' => __('Soubor řezu', 'cms'),
						'type' => 'upload_file',
						'hide_delete' => true,
						'name' => __('Soubor řezu', 'cms'),
						//'max_file_size_bytes' => 12 * pow(2, 20),
					],
					'font_weight' => [
						'title' => __('Typ řezu', 'cms'),
						'type' => 'select',
						'content' => 'thin',
						'options' => [
							['name' => __('Thin', 'cms'), 'value' => 100],
							['name' => __('Extra-light', 'cms'), 'value' => 200],
							['name' => __('Light', 'cms'), 'value' => 300],
							['name' => __('Normal', 'cms'), 'value' => 400],
							['name' => __('Medium', 'cms'), 'value' => 500],
							['name' => __('Semi-bold', 'cms'), 'value' => 600],
							['name' => __('Bold', 'cms'), 'value' => 700],
							['name' => __('Extra-bold', 'cms'), 'value' => 800],
							['name' => __('Black', 'cms'), 'value' => 900],
						],
					],
				],
				'show_group' => 'font_type',
				'show_val' => 'file',
			],
		],
	],
]);

mwSetting()->addPageSetting('comments_setting', [
	[
		'type' => 'box',
		'setting' => [
			[
				'id' => 'default_comment_status',
				'type' => 'switch',
				'label' => __('Povolit komentáře u nových stránek a článků', 'cms'),
				'desc' => __('Toto nastavení může být změněno individuálně u každé stránky nebo článku.', 'cms'),
			],
			[
				'id' => 'require_name_email',
				'title' => __('Poviné jméno a email', 'cms'),
				'type' => 'switch',
				'label' => __('Autor komentáře musí vyplnit jméno a email', 'cms'),
			],
			[
				'id' => 'comment_registration',
				'title' => __('Komentáře pouze pro registrované', 'cms'),
				'type' => 'switch',
				'label' => __('Přidávat komentáře mohou pouze registrovaní a přihlášení uživatelé', 'cms'),
			],
			[
				'id' => 'thread_comments',
				'title' => __('Povolit vlákna komentářů', 'cms'),
				'type' => 'switch',
				'label' => __('Povolit odpovídat návštěvníkům na komentáře', 'cms'),
				'show' => 'show_threads',
			],
			[
				'id' => 'thread_comments_depth',
				'title' => __('Povolit vlákna komentářů až do úrovně', 'cms'),
				'type' => 'select',
				'options' => [
					['name' => '2', 'value' => '2'],
					['name' => '3', 'value' => '3'],
					['name' => '4', 'value' => '4'],
					['name' => '5', 'value' => '5'],
					['name' => '6', 'value' => '6'],
					['name' => '7', 'value' => '7'],
					['name' => '8', 'value' => '8'],
					['name' => '9', 'value' => '9'],
					['name' => '10', 'value' => '10'],
				],
				'show_group' => 'show_threads',
				'show_val' => '1',
			],
		],
	],
	[
		'title' => __('Stránkovat komentáře', 'cms'),
		'id' => 'page_comments',
		'type' => 'toggle_group',
		'checkbox' => 1,
		'setting' => [
			[
				'id' => 'comments_per_page',
				'title' => __('Počet komentářů nejvyšší úrovně na stránku', 'cms'),
				'type' => 'number',
			],
			[
				'id' => 'default_comments_page',
				'title' => __('Jako výchozí zobrazit', 'cms'),
				'type' => 'select',
				'options' => [
					['name' => __('poslední stránku', 'cms'), 'value' => 'newest'],
					['name' => __('první stránku', 'cms'), 'value' => 'oldest'],
				],
			],
			[
				'id' => 'comment_order',
				'title' => __('Seřadit komentáře na každé stránce', 'cms'),
				'type' => 'select',
				'options' => [
					['name' => __('od nejstarších', 'cms'), 'value' => 'asc'],
					['name' => __('od nejnovějších', 'cms'), 'value' => 'desc'],
				],
			],
		],
	],
	[
		'type' => 'toggle_group',
		'title' => __('Poslat notifikační email', 'cms'),
		'tooltip' => __('Notifikační e-mail bude odeslán na adresu autora článku.', 'cms'),
		'setting' => [
			[
				'id' => 'comments_notify',
				'type' => 'switch',
				'label' => __('Když někdo přidá komentář', 'cms'),
			],
			[
				'id' => 'moderation_notify',
				'type' => 'switch',
				'label' => __('Když nějaký komentář čeká na schválení', 'cms'),
			],
		],
	],
	[
		'type' => 'toggle_group',
		'title' => __('Pravidla pro schvalování', 'cms'),
		'setting' => [
			[
				'id' => 'comment_moderation',
				'type' => 'switch',
				'label' => __('Každý komentář musí být schválen ručně', 'cms'),
			],
			[
				'id' => 'comment_previously_approved',
				'type' => 'switch',
				'label' => __('Autor komentáře už napsal alespoň jeden schválený komentář', 'cms'),
			],
		],
	],
	[
		'type' => 'toggle_group',
		'title' => __('Automatické zadržování', 'cms'),
		'setting' => [
			[
				'id' => 'comment_max_links',
				'title' => __('Zdržet komentář ve frontě, pokud obsahuje více než', 'cms'),
				'type' => 'number',
				'unit' => __('odkazů', 'cms'),
				'tooltip' => __('Základní charakteristikou komentářového spamu je velké množství odkazů.', 'cms'),
			],
			[
				'id' => 'moderation_keys',
				'title' => __('Zadržet komentář, když obsahuje jedno ze slov', 'cms'),
				'type' => 'textarea',
				'rows' => 10,
				'tooltip' => __('Pokud bude komentář obsahovat ve svém obsahu, jménu autora, URL adrese, IP adrese nebo v hlavičce prohlížeče, jedno ze zadaných slov, tak bude komentář zadržen a bude vyžadovat schválení. Zadejte každé slovo nebo IP adresu na nový řádek.', 'cms'),
			],
		],
	],
	[
		'type' => 'toggle_group',
		'title' => __('Nepovolené komentáře', 'cms'),
		'setting' => [
			[
				'id' => 'disallowed_keys',
				'title' => __('Hodit komentář do koše, když obsahuje jedno ze slov', 'cms'),
				'type' => 'textarea',
				'rows' => 10,
				'tooltip' => __('Pokud bude komentář obsahovat ve svém obsahu, jménu autora, URL adrese, IP adrese nebo v hlavičce prohlížeče, jedno ze zadaných slov, tak bude komentář automaticky hozen do koše. Zadejte každé slovo nebo IP adresu na nový řádek.', 'cms'),
			],
		],
	],
]);

mwSetting()->addUserSetting([
	'id' => 'user',
	'title' => __('Uživatel', 'cms_member'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'name' => __('Uživatelské jméno', 'cms'),
					'id' => 'user_login',
					'type' => 'text',
					//'desc' => __('Uživatelská jména nelze měnit.', 'cms'),
				],
				[
					'name' => __('Email', 'cms'),
					'id' => 'user_email',
					'type' => 'text',
				],
				[
					'name' => __('Jméno', 'cms'),
					'id' => 'first_name',
					'type' => 'text',
				],
				[
					'name' => __('Příjmení', 'cms'),
					'id' => 'last_name',
					'type' => 'text',
				],
			],
		],
		[
			'type' => 'box',
			'title' => __('Heslo', 'cms'),
			'setting' => [
				[
					'name' => __('Heslo', 'cms'),
					'id' => 'password',
					'type' => 'user_password',
				],
			],
		],
	],
]);

mwSetting()->addUserSetting([
	'id' => 'informations',
	'name' => 'user',
	'title' => __('Informace', 'cms'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'name' => __('O uživateli', 'cms'),
					'id' => 'description',
					'type' => 'textarea',
				],
				[
					'name' => __('Webová stránka', 'cms'),
					'id' => 'user_url',
					'type' => 'text',
				],
				[
					'id' => '',
					'type' => 'user_contact_info',
				],
			],
		],
	],
]);
