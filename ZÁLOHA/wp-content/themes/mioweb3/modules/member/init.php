<?php
use Mioweb\Member\MembershipEmailing;
use Mioweb\Member\MemberPage;

global $vePage;

define('MEMBER_VERSION', '3.5');
MW()->add_version('member', MEMBER_VERSION);

define('MEMBER_DIR', get_template_directory_uri() . '/modules/member/');

define('MW_MEMBER_NEWS_SLUG', 'member_news');
define('MW_MEMBER_CUSTOM_FIELDS_SLUG', 'member_custom_fields');

// language
MW()->load_theme_lang('cms_member', get_template_directory() . '/modules/member/languages');

require_once(__DIR__ . '/lib/MwMemberFields.php');
require_once(__DIR__ . '/lib/Installer.php');
require_once(__DIR__ . '/lib/RegisterForm.php');
require_once(__DIR__ . '/lib/MembershipVariables.php');
require_once(__DIR__ . '/lib/MembershipEmailing.php');
require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/elements.php');
require_once(__DIR__ . '/elements_print.php');
require_once(__DIR__ . '/lib/MwMemberApi.php');
require_once(__DIR__ . '/lib/objects/MwMemberNew.php');
require_once(__DIR__ . '/lib/objects/MwMemberCustomField.php');
require_once(__DIR__ . '/lib/objects/MwMember.php');
require_once(__DIR__ . '/lib/MemberLevel.php');
require_once(__DIR__ . '/lib/MemberPage.php');
require_once(__DIR__ . '/lib/Membership.php');
require_once(__DIR__ . '/lib/MembershipLevel.php');
require_once(__DIR__ . '/lib/MemberChecklist.php');
require_once(__DIR__ . '/lib/MemberTask.php');
require_once(__DIR__ . '/lib/MembersImport.php');
require_once(__DIR__ . '/lib/MemberAccess.php');
require_once(__DIR__ . '/lib/MemberAccess.php');
require_once(__DIR__ . '/Exceptions/InstallationInProgressException.php');
require_once(__DIR__ . '/lib/MonthMembership.php');
require_once(__DIR__ . '/lib/MemberProfileAdmin.php');
require_once(__DIR__ . '/lib/Dashboard.php');
require_once(__DIR__ . '/lib/notifications/Notifications.php');
require_once(__DIR__ . '/lib/notifications/NotificationsFapi.php');
require_once(__DIR__ . '/lib/notifications/NotificationsSimpleShop.php');
require_once(__DIR__ . '/lib/objects/MwMemberSection.php');
require_once(__DIR__ . '/lib/MwMemberStatistics.php');
require_once(__DIR__ . '/member_class.php');

mwMemberModule();

MemberPage::registerMemberPageObject();

mwPageSelector()->addTab([
	'id' => 'member',
	'title' => __('Členské sekce', 'cms_member'),
], 5);

// Templates
//***********************************************************************************
MW()->add_templates_topos(8, 'member', [
	'name' => __('Členské', 'cms_member'),
	'icon' => 'lock',
	'path' => '/modules/member/templates/member/',
	'list' => [
		'dashboard' => [
			'name' => __('Šablony nástěnky', 'cms_member'),
			'list' => ['dashboard1'],
		],
		'lesson' => [
			'name' => __('Šablony stránky s výpisem lekcí', 'cms_member'),
			'list' => ['list1'],
		],
		'list' => [
			'name' => __('Šablony stránky s obsahem lekce', 'cms_member'),
			'list' => ['lesson1'],
		],
		'login' => [
			'name' => __('Šablony přihlašovacích stránek', 'cms_member'),
			'list' => ['login1', 'login2'],
		],
	],
]);

// Nastavení stránek
//***********************************************************************************

mwSetting()->addObjectSetting([
	'id' => 'page_member',
	'exclude_modules' => ['eshop', 'blog'],
	'title' => __('Členská stránka', 'cms_member'),
	'save_function' => 'Mioweb\Member\MemberPage::saveMemberPageSetting',
	'load_function' => 'Mioweb\Member\MemberPage::loadMemberPageSetting',
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'id' => 'member_page',
					'type' => 'status_switch',
					'desc' => __('Po zaškrtnutí bude tato stránka součástí vybrané členské sekce a budou k ní mít přístup jen její členové.', 'cms_member'),
					'show' => 'memberpage',
					'label' => __('Zpřístupnit stránku pouze registrovaným uživatelům.', 'cms_member'),
				],
				[
					'id' => 'member_group',
					'type' => 'group',
					'show_group' => 'memberpage',
					'show_val' => '1',
					'setting' => [
						[
							'name' => __('Zařadit do členské sekce', 'cms_member'),
							'id' => 'member_section',
							'type' => 'selectmember',
							'show_levels' => true,
							'tooltip' => __('Tato stránka bude zařazena do vybrané členské sekce a bude přistupná pouze těm registrovaným uživatelům, kteří mají do této členské sekce přístup.', 'cms_member'),
						],
						[
							'type' => 'tabs',
							'id' => 'member_page_setting',
							'tabs' => [
								'evergreen' => [
									'name' => __('Omezení přístupu', 'cms_member'),
									'setting' => [
										[
											'title' => __('Zpřístupnit uživateli', 'cms_member'),
											'id' => 'access_type',
											'type' => 'select',
											'options' => [
												['value' => '', 'name' => __('okamžitě po vstupu do členské sekce', 'cms_ve')],
												['value' => 'evergreen', 'name' => __('po X dnech od registrace do členské sekce', 'cms_ve')],
												['value' => 'date', 'name' => __('v určitý den (přesné datum a čas)', 'cms_ve')],
												['value' => 'month', 'name' => __('v měsíci (měsíční obsah)', 'cms_ve')],
												['value' => 'checklist', 'name' => __('Po splnění úkolů předešlé lekce', 'cms_ve')],
											],
											'content' => '',
											'show' => 'evergreen',
										],
										[
											'name' => __('Zpřístupnit po x dnech od registrace', 'cms_member'),
											'id' => 'evergreen',
											'type' => 'text',
											'desc' => __('Zadejte, po jakém počtu dní od registrace se má stránka členům zpřístupnit. Pokud pole nevyplníte, bude stránka přístupná od okamžiku registrace.', 'cms_member'),
											'show_group' => 'evergreen',
											'show_val' => 'evergreen',
										],
										[
											'name' => __('Zpřístupnit dne', 'cms_member'),
											'id' => 'evergreen_datetime',
											'type' => 'datetime',
											'convert' => 1,
											'desc' => __('Zadejte datum a čas chvíle, od které má být stránka viditelná.', 'cms_member'),
											'show_group' => 'evergreen',
											'show_val' => 'date',
										],

										[
											'type' => 'info',
											'content' => __('Pokud stránku nastavíte jako měsíční obsah, tak se automaticky uvolní v nastavený měsíc a budou na ní mít přístup jen členové s oprávněním pro tento měsíc. Oprávnění se dá přiřadit pomocí notifikace po zaplacení nebo v nastavení každého uživatele.', 'cms_member'),
											'show_group' => 'evergreen',
											'show_val' => 'month',
										],
										[
											'name' => __('Zpřístupnit v měsíci', 'cms_member'),
											'id' => 'month',
											'type' => 'month_member',
											'show_group' => 'evergreen',
											'show_val' => 'month',
										],
										[
											'name' => __('Zobrazení', 'cms_member'),
											'id' => 'hide_in_list',
											'type' => 'switch',
											'label' => __('Skrýt stránku ve výpisu a v archívu měsíčních lekcí', 'cms_member'),
											'show_group' => 'evergreen',
											'show_val' => 'month',
										],
										[
											'name' => __('Prodejní stránka pro tento měsíc', 'cms_member'),
											'id' => 'month_page_id',
											'type' => 'selectpage',
											'desc' => __('Pokud uživatel nebude mít tento obsah zakoupený, bude v archívu a výpisu měsíčních lekcí u této stránky zobrazeno tlačítko s odkazem na danou prodejní stránku.', 'cms_member'),
											'show_group' => 'evergreen',
											'show_val' => 'month',
										],
										[
											'name' => __('Po splnění úkolů stránky', 'cms_member'),
											'id' => 'checklist_page',
											'type' => 'selectpage',
											'desc' => __('Uživatel nebude mít na tuto stránku přístup dokud nesplní všechny úkoly stránky, kterou zde vyberete. Pokud nevyberete žádnou stránku, vezme se automaticky předchozí stránka v seznamu.', 'cms_member'),
											'show_group' => 'evergreen',
											'show_val' => 'checklist',
										],
									],
								],
								'checklist' => [
									'name' => __('Seznam úkolů', 'cms_member'),
									'setting' => [
										[
											'id' => 'checklist',
											'type' => 'simple_feature',
											'text_add' => __('Přidat úkol', 'cms_member'),
											'sortable' => true,
											'fields' => [
												'mpt_id' => [
													'type' => 'hidden',
												],
												'task' => [
													'empty' => __('Úkol', 'cms_member'),
													'type' => 'textarea',
												],
											],
										],
									],
								],
							],
						],
					],
				],
			],
		],
	],
], ['page', 'member_page']);

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
		[
			'name' => __('Zařadit do členské sekce', 'cms_member'),
			'id' => 'member_section',
			'type' => 'selectmember',
			'show_levels' => true,
			'tooltip' => __('Tato stránka bude zařazena do vybrané členské sekce a bude přistupná pouze těm registrovaným uživatelům, kteří mají do této členské sekce přístup.', 'cms_member'),
		],
	],
], ['member_page']);

// Nastavení
//***********************************************************************************

mwSetting()->registerPageSettingType('members_dashboard', [
	'static_class' => 'Mioweb\Member\Dashboard',
	'function' => 'getDashboard',
]);
mwSetting()->registerPageSettingType('members_import', [
	'static_class' => 'Mioweb\Member\MembersImport',
	'function' => 'importSet',
]);

mwSetting()->addGroup([
	'id' => 'member_option',
	'icon' => 'lock',
	'title' => __('Členské sekce', 'cms_member'),
	'home' => 'members_dashboard',
	'order' => 15,
]);
mwSetting()->addPage([
	'id' => 'members_dashboard',
	'icon' => 'bar-chart-2',
	'group' => 'member_option',
	'title' => __('Přehled', 'cms_member'),
	'type' => 'members_dashboard',
]);
mwSetting()->addPage([
	'id' => 'member_sections',
	'icon' => 'lock',
	'group' => 'member_option',
	'title' => __('Členské sekce', 'cms_member'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => 'members',
	'icon' => 'users',
	'group' => 'member_option',
	'title' => __('Seznam členů', 'cms_member'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => 'members_import',
	'parent' => 'members',
	'group' => 'member_option',
	'title' => __('Import členů', 'cms_member'),
	'description' => __('Vytvoří účty do členských sekcí pro zadané e-mailové adresy', 'cms_member'),
	'type' => 'members_import',
	'alert_on_leave' => false,
]);
mwSetting()->addPage([
	'id' => MW_MEMBER_CUSTOM_FIELDS_SLUG,
	'parent' => 'members',
	'group' => 'member_option',
	'title' => __('Vlastní pole členů', 'cms_member'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => MW_MEMBER_NEWS_SLUG,
	'icon' => 'edit-3',
	'group' => 'member_option',
	'title' => __('Členské novinky', 'cms_member'),
	'type' => 'list',
]);
mwSetting()->addPage([
	'id' => 'member_login',
	'group' => 'web_option',
	'title' => __('Wordpress login', 'cms_member'),
	'parent' => 've_appearance',
]);

mwSetting()->addPageSetting('member_login', [
	[
		'id' => 'wplogin_logo',
		'type' => 'toggle_group',
		'open' => true,
		'title' => __('Logo', 'cms_member'),
		'setting' => [
			[
				'name' => __('Logo', 'cms_member'),
				'id' => 'logo',
				'type' => 'image_url',
				'content' => get_bloginfo('template_url') . '/modules/member/images/login-logo.png',
			],
			[
				'name' => __('Šířka loga', 'cms_member'),
				'id' => 'width',
				'type' => 'slider',
				'setting' => [
					'min' => '50',
					'max' => '300',
					'unit' => 'px',
				],
				'content' => [
					'size' => '159',
					'unit' => 'px',
				],
			],
			[
				'name' => __('Výška loga', 'cms_member'),
				'id' => 'height',
				'type' => 'slider',
				'setting' => [
					'min' => '20',
					'max' => '200',
					'unit' => 'px',
				],
				'content' => [
					'size' => '36',
					'unit' => 'px',
				],
			],
		],
	],
	[
		'id' => 'wplogin_format',
		'type' => 'toggle_group',
		'title' => __('Pozadí a formátování přihlašovací stránky', 'cms_member'),
		'setting' => [
			[
				'name' => __('Barva pozadí', 'cms_member'),
				'id' => 'background_color',
				'type' => 'color',
				'content' => '#158ebf',
			],
			[
				'name' => __('Obrázek na pozadí', 'cms_member'),
				'id' => 'background_image',
				'type' => 'bgimage',
				'content' => [
					'pattern' => 0,
					'cover' => '1',
				],
				'hide' => ['efect', 'cover', 'repeat', 'color_filter'],
			],
			[
				'name' => __('Barva textu', 'cms_member'),
				'id' => 'font-color',
				'type' => 'color',
				'content' => '#bdd5e4',
			],
		],
	],
]);

mwSetting()->addPageSetting('members_import', [
	[
		'id' => 'import_member',
		'type' => 'box',
		'setting' => [
			[
				'name' => __('Seznam e-mailů (na každý řádek jeden e-mail)', 'cms_member'),
				'id' => 'emails',
				'type' => 'textarea',
				'tooltip' => __('Zadejte zde seznam e-mailů, pro který chcete vygenerovat nové účty. Každý e-mail musí být na novém řádku.', 'cms_member'),
				'required' => 1,
			],
			[
				'name' => __('Upravit existující členství', 'cms_member'),
				'id' => 'edit_exist',
				'type' => 'switch',
				'label' => __('Upravit dle zadaných kritérií i již existující členství', 'cms_member'),
				'tooltip' => __('Pokud zaškrtnete, že se má upravovat i existující členství, tak kontaktům, které jsou již zařazeny do vybrané členské sekce, bude upraveno členství podle zadaných kritérií. V opačném případě se tyto kontakty při importu přeskočí.', 'cms_member'),
			],
		],
	],
	[
		'id' => 'import_member',
		'type' => 'box',
		'setting' => [
			[
				'name' => __('Zaslat přístupy', 'cms_member'),
				'id' => 'send_mail',
				'type' => 'switch',
				'label' => __('Poslat novým členům e-mail s přístupovými údaji (informovat existující členy o změnách)', 'cms_member'),
				'content' => 1,
				'show' => 'send_email',
			],
			[
				'name' => __('Vlastní email', 'cms_member'),
				'id' => 'send_custom_mail',
				'type' => 'switch',
				'label' => __('Zadat vlastní znění emailu', 'cms_member'),
				'show_group' => 'send_email',
				'show_val' => 1,
				'show' => 'send_custom_email',
			],
			[
				'id' => 'email',
				'module' => 'member',
				'variables_class' => '\Mioweb\Member\MembershipVariables',
				'type' => 'transaction_email',
				'variables_list' => 'import',
				'content' => [
					'subject' => MembershipEmailing::getDefaultSubject('addMembership'),
					'content' => MembershipEmailing::getDefaultMessage('import'),
				],
				'show_group' => 'send_custom_email',
				'show_val' => 1,

			],
		],
	],
	[
		'type' => 'title',
		'name' => __('Importovat do', 'cms_member'),
	],
	[
		'id' => 'member_setting',
		'type' => 'member_sections',
	],
]);

mwSetting()->addObjectSetting([
	'id' => 'mw_news',
	'title' => __('Novinka', 'cms_member'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => MW_MEMBER_NEWS_SLUG,
					'fields' => [
						'post_title' => [
							'label' => __('Titulek', 'cms_member'),
							'slug' => false,
						],
						'post_content' => [
							'label' => __('Text', 'cms_member'),
							'editor' => true,
						],
					],
				],
			],
		],
	],
], [MW_MEMBER_NEWS_SLUG]);

mwSetting()->addObjectSetting([
	'id' => 'mw_custom_field',
	'title' => __('Vlastní pole', 'cms_member'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'type' => 'item_set',
					'object_id' => MW_MEMBER_CUSTOM_FIELDS_SLUG,
					'fields' => [
						'post_title' => [
							'label' => __('Titulek', 'cms_member'),
							'slug' => false,
						],
					],
				],
				[
					'id' => 'type',
					'name' => __('Typ', 'cms_member'),
					'type' => 'select',
					'options' => [
						['value' => 'text', 'name' => __('Jednoduchý text', 'cms_member')],
						['value' => 'textarea', 'name' => __('Textové pole', 'cms_member')],
					],
				],
				[
					'type' => 'item_set',
					'object_id' => MW_MEMBER_CUSTOM_FIELDS_SLUG,
					'fields' => [
						'post_excerpt' => [
							'label' => __('Popisek', 'cms_member'),
							'slug' => false,
						],
					],
				],
			],
		],
	],
], [MW_MEMBER_CUSTOM_FIELDS_SLUG]);

mwSetting()->addObjectFastSetting([
	'id' => 'member_basic',
	'fields' => [
		[
			'id' => 'name',
			'name' => __('Název členské sekce', 'cms_member'),
			'required' => 1,
			'type' => 'text',
		],
	],
], ['member_sections']);

mwSetting()->addObjectSetting([
	'id' => 'member_basic',
	'group' => 'setting',
	'title' => __('Členská sekce', 'cms_member'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'id' => 'name',
					'name' => __('Název členské sekce', 'cms_member'),
					'type' => 'text',
					'required' => 1,
				],
				[
					'id' => 'dashboard_page_id',
					'name' => __('Nástěnka (hlavní stránka)', 'cms_member'),
					'type' => 'selectpage',
					'add_button' => true,
					'edit_button' => true,
					'whisperer' => true,
					'content' => '',
					'required' => 1,
					'tooltip' => __('Nástěnka je úvodní stránka členské sekce. Na tuto stránku je uživatel defaultně přesměrován po přihlášení.', 'cms_member'),
				],
				[
					'id' => 'login_page_id',
					'name' => __('Přihlašovací stránka', 'cms_member'),
					'type' => 'selectpage',
					'add_button' => true,
					'edit_button' => true,
					'whisperer' => true,
					'content' => '',
					'tooltip' => __('Přihlašovací stránka, slouží k přihlášení do této členské sekce. Pokud přihlašovací stránku nenastavíte, tak se k přihlášení použije defaultní přihlašovací stránka.', 'cms_member'),
				],
				[
					'id' => 'noaccess_page_id',
					'name' => __('Stránka pro členy bez přístupu', 'cms_member'),
					'type' => 'selectpage',
					'add_button' => true,
					'edit_button' => true,
					'whisperer' => true,
					'content' => '',
					'tooltip' => __('Tato stránka se zobrazí přihlášeným uživatelům (členům), kteří nemají přístup do této členské sekce. Defaultně se zobrazí jen infromace že do této členské sekce nemají přístup.', 'cms_member'),
				],
			],
		],
		[
			'id' => 'member_evergreen',
			'type' => 'toggle_group',
			'title' => __('Omezené členství', 'cms_member'),
			'setting' => [
				[
					'id' => 'extend_page_id',
					'name' => __('Stránka pro prodloužení členství', 'cms_member'),
					'type' => 'selectpage',
					'add_button' => true,
					'edit_button' => true,
					'whisperer' => true,
					'content' => '',
					'tooltip' => __('V menu uživatele, v seznamu členských sekcí nebo při přístupu do členské sekce po vypršení členství se zobrazí tlačítko pro prodloužení měsíčního nebo časově omezeného členství, které povede na zadanou stránku. Pokud stránku nezadáte, tlačítko se nezobrazí.', 'cms_member'),
				],
				[
					'id' => 'expire_page_id',
					'name' => __('Po vypršení časově omezeného členství zobrazit stránku', 'cms_member'),
					'type' => 'selectpage',
					'add_button' => true,
					'edit_button' => true,
					'whisperer' => true,
					'content' => '',
					'tooltip' => __('Tato stránka se uživateli zobrazí ve chvíli, kdy se pokusí vstoupit na členskou stránku po vypršení jeho časově omezeného členství. Defaultně se zobrazí informace že členství vypršelo s odkazem na prodloužení (Pokud je stránka pro prodloužení zadána).', 'cms_member'),
				],
			],
		],
		[
			'id' => 'member_evergreen',
			'type' => 'toggle_group',
			'title' => __('Evergreen', 'cms_member'),
			'setting' => [
				[
					'id' => 'hide_evergreen',
					'title' => '',
					'label' => __('Skrýt nezveřejněné stránky', 'cms_member'),
					'type' => 'switch',
					'desc' => __('Pokud tuto možnost nezaškrtnete, všechny stránky budou viditelné i pro ty, kteří k nim zatím nemají přístup. Budou však zašedlé a nepůjdou rozkliknout.', 'cms_member'),
				],
			],
		],
	],
], ['member_sections']);

mwSetting()->addObjectSetting([
	'id' => 'member_basic',
	'group' => 'setting',
	'tab_id' => 'member_levels',
	'title' => __('Členské úrovně', 'cms_member'),
	'fields' => [
		[
			'id' => 'levels',
			'type' => 'multielement',
			'sortable' => false,
			'keep_id' => true,
			'open' => 'under',
			'style' => 'shadow',
			'texts' => [
				'add' => __('Přidat členskou úroveň', 'cms_member'),
				'empty' => __('Nová členská úroveň', 'cms_member'),
			],
			'setting' => [
				[
					'id' => 'id',
					'type' => 'hidden_input',
				],
				[
					'id' => 'name',
					'title' => __('Název členské úrovně', 'cms_member'),
					'content' => __('Nová členská úroveň', 'cms_member'),
					'type' => 'text',
				],
				[
					'id' => 'show_level_pages',
					'type' => 'switch',
					'title' => __('Zobrazení', 'cms_member'),
					'label' => __('Zobrazit stránky této úrovně v menu i pro členy, kteří do ní nemají přístup', 'cms_member'),
				],
				[
					'id' => '',
					'type' => 'noaccess_content',
					'title' => __('Obsah stránky pro členy bez oprávnění', 'cms_member'),
					'content' => [
						'noaccess_page_id' => '',
						'noaccess_text' => '<p style="text-align:center;">' . __('Pro přístup k této stránce nemáte dostatečné oprávnění.', 'cms_member') . '</p>',
					],
				],
				[
					'id' => 'extend_page_id',
					'name' => __('Stránka pro prodloužení členství', 'cms_member'),
					'type' => 'selectpage',
					'add_button' => true,
					'edit_button' => true,
					'whisperer' => true,
					'content' => '',
					'tooltip' => __('V menu uživatele se zobrazí tlačítko pro prodloužení měsíčního nebo časově omezeného členství, které povede na zadanou stránku. Pokud stránku nezadáte, tlačítko se nezobrazí.', 'cms_member'),
				],
				[
					'id' => 'expire_page_id',
					'name' => __('Po vypršení časově omezeného členství zobrazit stránku', 'cms_member'),
					'type' => 'selectpage',
					'add_button' => true,
					'edit_button' => true,
					'whisperer' => true,
					'content' => '',
					'tooltip' => __('Tato stránka se uživateli zobrazí ve chvíli, kdy se pokusí přihlásit po vypršení jeho časově omezeného členství v této úrovni.', 'cms_member'),
				],

			],
		],
	],
], ['member_sections']);

mwSetting()->addObjectSetting([
	'id' => 'member_emails',
	'group' => 'setting',
	'title' => __('Emaily', 'cms_member'),
	'fields' => [
		[
			'id' => 'emails',
			'ignore_id_in_field_names' => true,
			'type' => 'emails',
			'module' => 'member',
			'variables_class' => '\Mioweb\Member\MembershipVariables',
			'content' => [
				// email for add section
				'addMembership' => [
					'title' => __('E-mail po přidání do členské sekce', 'cms_member'),
					'subject' => MembershipEmailing::getDefaultSubject('addMembership'),
					'content' => MembershipEmailing::getDefaultMessage('addMembership'),
				],
				// email for add levels
				'addLevel' => [
					'title' => __('E-mail po přidání do členské úrovně', 'cms_member'),
					'subject' => MembershipEmailing::getDefaultSubject('addLevel'),
					'content' => MembershipEmailing::getDefaultMessage('addLevel'),
				],
				// email for extend membership
				'extendMembership' => [
					'title' => __('E-mail po prodloužení členství', 'cms_member'),
					'subject' => MembershipEmailing::getDefaultSubject('extendMembership'),
					'content' => MembershipEmailing::getDefaultMessage('extendMembership'),
				],
			],
		],
		//'desc' => __('Proměnná %%login%% bude nahrazena vygenerovanými přihlašovacími údaji a URL adresou s přihlašovacím formulářem do odpovídající členské sekce. Text e-mailu musí tuto proměnnou obsahovat.', 'cms_member'),
	],
], ['member_sections']);

mwSetting()->addObjectSetting([
	'id' => 'member_popups',
	'group' => 'setting',
	'title' => __('Pop-upy', 'cms_member'),
	'fields' => MW()->container['popup_setting'],
], ['member_sections']);

mwSetting()->addObjectSetting([
	'id' => 'member_basic',
	'group' => 'setting',
	'tab_id' => 'member_notifications',
	'title' => __('Notifikace', 'cms_member'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'name' => 'Notifikační url',
					'tooltip' => 'Notifikační url slouží k automatickému vytváření členských účtů např. po zaplacení faktury.',
					'id' => 'fapi_notification',
					'type' => 'fapi_notification',
				],
				[
					'name' => 'Další atributy notifikační url',
					'tooltip' => 'Atributy se přidají na konec notifikační url ve formátu &atribut=hodnota',
					'id' => 'notification_atributes',
					'type' => 'notification_atributes',
				],
			],
		],
		[
			'id' => 'member_notif',
			'type' => 'toggle_group',
			'title' => __('Log proběhlých notifikací', 'cms_member'),
			'setting' => [
				[
					'name' => __('Upozornění', 'cms_member'),
					'id' => 'send_notifications',
					'type' => 'switch',
					'label' => __('Posílat upozornění na neúspěšné notifikace na e-mail', 'cms_member'),
					'show' => 'send_notifications',
				],
				[
					'name' => __('E-mail pro upozornění', 'cms_member'),
					'id' => 'notification_email',
					'type' => 'text',
					'desc' => __('Zde zadejte e-mailovou adresu, na kterou chcete notifikace zasílat.', 'cms_member'),
					'show_group' => 'send_notifications',
					'show_val' => '1',
				],
				[
					'name' => __('Tabulka notifikací', 'cms_member'),
					'id' => 'fapi_notification_log',
					'type' => 'fapi_notification_log',
				],
			],
		],
	],
], ['member_sections']);

mwSetting()->addObjectSetting([
	'id' => 'member_appearance',
	'group' => 'appearance',
	'title' => __('Vzhled', 'cms_member'),
	'fields' => MW()->container['appearance_setting'],
], ['member_sections']);

mwSetting()->addObjectSetting([
	'id' => 'member_header',
	'group' => 'appearance',
	'title' => __('Hlavička', 'cms_member'),
	'fields' => MW()->container['header_setting'],
], ['member_sections']);

mwSetting()->addObjectSetting([
	'id' => 'member_footer',
	'group' => 'appearance',
	'title' => __('Patička', 'cms_member'),
	'fields' => MW()->container['footer_setting'],
], ['member_sections']);

// user setting

mwSetting()->addUserSetting([
	'id' => 'member_info',
	'title' => __('Člen', 'cms'),
	'fields' => [
		[
			'type' => 'box',
			'setting' => [
				[
					'id' => '',
					'type' => 'member_fields',
				],
			],
		],
	],
]);

mwSetting()->addUserSetting([
	'id' => 'members',
	'title' => __('Členské sekce', 'cms_member'),
	'fields' => [
		[
			'id' => 'member_setting',
			'type' => 'member_sections',
		],
	],
]);
