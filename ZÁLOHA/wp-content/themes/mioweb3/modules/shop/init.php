<?php
/**
 * global options prefix: "mwshop_" --> MWS_OPTION
 * textdomain: "mwshop"
 * hooks prefix: "mws_"
 * html css prefix: "mws_" --> MWS_CSS
 * html id prefix: "mws_" --> MWS_ID
 * html name prefix: "mws_" --> MWS_NAME
 */

/* MW Shop entry point. This handles loading of data, registration of taxonomies, arranging plugin tables. */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/* ==== Global shop DEFINES ==== */
/** Prefix of global options. */
define('MWS_OPTION', 'mwshop_');
/** Prefix of CSS classes in html elements. */
define('MWS_CSS', 'mws_');
/** Prefix of "name" attributes in html input editors. */
define('MWS_NAME', 'mws_');
/** Prefix of "id" attributes in html elements. */
define('MWS_ID', 'mws_');

/** Slug name for product. */
define('MWS_PRODUCT_SLUG', 'mwproduct');
/** Slug name for product variant. */
define('MWS_VARIANT_SLUG', 'mwvariant');
/**
 * Slug name for order.
 */
define('MWS_ORDER_SLUG', 'mworder');
/** Slug name for order. */
define('MWS_FORM_CART_SLUG', 'mw_form_cart');
/** Slug name for document. */
define('MWS_DOCUMENT_SLUG', 'mwdocument'); // @TODO register
/** Slug name for payment. */
define('MWS_PAYMENT_SLUG', 'mwpayment'); // @TODO register
/** Slug name for category of product. */
define('MWS_PRODUCT_CAT_SLUG', 'eshop_category');
/** Slug name for tag of product. */
define('MWS_PRODUCT_TAG_SLUG', 'products_tag');
/** Slug name for shipping methods. */
define('MWS_SHIPPING_SLUG', 'mwshipping');
/** Slug name for shipping methods. */
define('MWS_PAYMENT_METHOD_SLUG', 'mws_payment_method');
/** Slug name for discount code methods. */
define('MWS_DISCOUNT_CODE_SLUG', 'mwdisccode');
/** Slug name for product parametr. */
define('MWS_PROPERTY_SLUG', 'mwsproperty');
/** Slug name for sell form. */
define('MWS_FORM_SLUG', 'mwsform');
/** Slug name for upsell. */
define('MWS_UPSELL_SLUG', 'mwupsell');
/** Slug name for currencies. */
define('MWS_CURRENCY_SLUG', 'mwcurrency');
/** Slug name for shipping countries. */
define('MWS_SHIPPING_COUNTRY_SLUG', 'mwshipcountry');

/** Name for shop page. It lists products. */
define('MWS_PAGE_SHOP', 'shop');
/** Name for cart page. Used for manipulations with products within cart. */
define('MWS_PAGE_CART', 'shop');
/** Name for order page. Used to set billing and shipping details. Possible additional checkings */
define('MWS_PAGE_ORDER', 'shop');
/** Name for payment page. User fires up the payment here. It receives callbacks from payment gateways. */
define('MWS_PAGE_PAYMENT', 'shop');

/** Root path of the MioShop library. No ending slash. */
define('MWS_PATH_BASE', __DIR__);
/** URL of the MioShop library. No ending slash. */
define('MWS_URL_BASE', get_bloginfo('template_url') . '/modules/shop');
/** Directory with rendering templates for single product, product archive etc. No ending slash. */
define('MWS_PATH_TEMPLATE', __DIR__ . '/templates');
define('MWS_REL_PATH_TEMPLATE', 'modules/shop/templates');
/** Directory with classes, routines. */
define('MWS_PATH_INCLUDE', __DIR__ . '/includes');
/** Directory with classes, routines. */
define('MWS_PATH_LIBS', __DIR__ . '/libs');

define('MWS_OPTION_SYNC_KEY', MWS_OPTION . 'sync');
define('MWS_OPTION_VATS_KEY', MWS_OPTION . 'vats');
define('MWS_OPTION_PERMALINKS', MWS_OPTION . 'permalinks');
define('MWS_OPTION_SHOP', 'eshop_option');
define('MWS_OPTION_SHOP_SETTING', 'mw_eshop_setting'); //option page for global shop settings
define('MWS_OPTION_SHOP_SETTING_VAT_RATES', 'mw_eshop_setting_vat_rates');
define('MWS_OPTION_SHOP_APPEARANCE', 'eshop_appearance');

define('MWS_OPTION_CURRENCIES', MWS_OPTION . 'currencies');
define('MWS_OPTION_DEFAULT_CURRENCY', MWS_OPTION . 'default_currency');

define('MWS_OPTION_SHIPPING_COUNTRIES', MWS_OPTION . 'shipping_countries');
define('MWS_OPTION_DEFAULT_SHIPPING_COUNTRY', MWS_OPTION . 'default_shipping_country');

/** Field name and meta key name for stock values of the product. */
define('MWS_OPTION_STOCKCOUNT', MWS_OPTION . 'stock_count');

/** Name of the meta key of product property settings. */
define('MWS_PROPERTY_META_KEY', 'mws_product_properties_set');
/** Name of the meta key of product's gallery. */
define('MWS_PRODUCT_META_KEY_GALLERY', 'product_gallery');
/** Name of the meta key of product's page codes. */
define('MWS_PRODUCT_META_KEY_PAGECODES', 'mw_page_codes');
/** Name of the meta key of product's price comparers. */
define('MWS_PRODUCT_META_KEY_COMPARATORS', 'price_comparators');
/** Name of the meta key of product. */
define('MWS_PRODUCT_META_KEY_STRUCTURE', 'product_structure');
/** Name of the meta key of product variant list - array defining variants. */
define('MWS_PRODUCT_META_KEY_VARIANTLIST', 'variant_list');

/**
 * Name of the meta key of order.
 * TODO delete
 */
define('MWS_ORDER_META_KEY', MWS_OPTION . 'order');
define('MWS_ORDER_META_KEY_ORDERNUM', MWS_OPTION . 'order_num');
define('MWS_ORDER_META_KEY_STATUS', MWS_OPTION . 'order_status');
define('MWS_ORDER_META_KEY_NOTOPENED', MWS_OPTION . 'order_not_opened');
define('MWS_ORDER_META_KEY_IS_TEST', MWS_OPTION . 'order_is_test');
define('MWS_ORDER_META_KEY_GATE_ID', MWS_OPTION . 'order_gate_id');
define('MWS_ORDER_META_KEY_CURRENCY', MWS_OPTION . 'order_currency');
define('MWS_ORDER_META_KEY_SOURCE_TYPE', MWS_OPTION . 'order_source_type');
define('MWS_ORDER_META_KEY_SOURCE_FORM_ID', MWS_OPTION . 'order_source_form_id');
define('MWS_ORDER_META_KEY_SOURCE_PAGE_ID', MWS_OPTION . 'order_source_page_id');
define('MWS_ORDER_META_KEY_SOURCE_URL', MWS_OPTION . 'order_source_url');
define('MWS_ORDER_META_KEY_HISTORY', MWS_OPTION . 'order_history');
define('MWS_ORDER_META_KEY_ARCHIVE', MWS_OPTION . 'order_archive');
define('MWS_ORDER_META_KEY_NOTE', MWS_OPTION . 'order_note');
define('MWS_ORDER_META_KEY_TRACKING_NUMBER', MWS_OPTION . 'order_tracking_number');

define('MWS_FORM_CART_META_KEY', MWS_OPTION . 'form_cart');
define('MWS_FORM_CART_META_KEY_SESSION_ID', MWS_OPTION . 'form_cart_session_id');
define('MWS_FORM_CART_META_KEY_IS_FORM_PROCESSED', MWS_OPTION . 'form_cart_is_form_processed');
define('MWS_FORM_CART_META_KEY_DELAYED_AUTO_PROCESS_RESPONSE', MWS_OPTION . 'form_cart_delayed_auto_process_response');

define('MWS_UPSELL_META_FORM_ID', MWS_OPTION . 'form_id');
define('MWS_UPSELL_META_PRODUCT_ID', MWS_OPTION . 'product_id');
define('MWS_UPSELL_META_PRICE', MWS_OPTION . 'prices');

/** Default number of products per page, if not defined by shop settings. */
define('MWS_DEFAULT_PER_PAGE', 10);

/** URL part for products, used in rewrite rules. */
define(
	'MWS_PERMALINK_PRODUCT_DEFAULT',
	_x('produkty', 'Část URL permalinků pro produkty. Lokalizace musí být URL friendly.', 'mwshop')
);
/** URL part for product categories when not nesting bellow products, used in rewrite rules. */
define(
	'MWS_PERMALINK_PRODUCT_CAT_DEFAULT',
	_x('kategorie-produktu', 'Část URL permalinků pro kategorie produktů. Lokalizace musí být URL friendly.', 'mwshop')
);
/** URL part for product categories when nesting bellow products, used in rewrite rules. */
define(
	'MWS_PERMALINK_PRODUCT_CAT_NESTED_DEFAULT',
	_x(
		'kategorie',
		'Část URL permalinků pro kategorie produktů, pokud jsou kategorie ' .
		'produktů zanořeny pod produkty. Lokalizace musí být URL friendly.',
		'mwshop'
	)
);
/** Identifies of wpnonece for verification of synchronization request.*/
define('MWS_GATEWAY_SYNC_NONCE', 'gateway_sync');
/**
 * Name of the target browser window, that is popuped from the administrations functions. Using it as A.TARGET means
 * that all administrated outer pages are opened within the same browser window.
 */

require_once(__DIR__ . '/shop_class.php');
require_once(__DIR__ . '/FormRenderer.php');
require_once(__DIR__ . '/InvoicePdfGenerator.php');
require_once(__DIR__ . '/FormProcessor.php');
require_once(__DIR__ . '/UnfinishedFormOrderProcessor.php');
require_once(__DIR__ . '/UpsellProcessor.php');
require_once(__DIR__ . '/AddressValidator.php');
require_once(__DIR__ . '/libs/heureka/heureka_class.php');
require_once(__DIR__ . '/libs/seznam/zbozi_class.php');
require_once(__DIR__ . '/functions.php');

mwPageSelector()->addTab([
	'id' => 'eshop',
	'title' => __('Eshop', 'mwshop'),
], 2);

mwApiConnect()->addApi('thepay', [
	'name' => 'ThePay',
	'tags' => ['paygate','sell'],
	'setting' => [
		[
			'type' => 'info',
			'content' => '<a target="_blank" href="https://www.mioweb.cz/partneri/thepay">' . __('Vytvořit účet u platební brány ThePay a získat údaje pro připojení.', 'cms') . '</a>',
		],
		[
			'name' => __('ID Obchodníka', 'cms'),
			'id' => 'merchantId',
			'type' => 'text',
		],
		[
			'name' => __('ID Projektu', 'cms'),
			'id' => 'projectId',
			'type' => 'text',
		],
		[
			'name' => __('API heslo', 'cms'),
			'id' => 'apiPassword',
			'type' => 'text',
		],
		[
			'id' => 'testMode',
			'type' => 'switch',
			'label' => __('Testovací režim', 'cms_member'),
		],
	],
]);
mwApiConnect()->addApi('gopay', [
	'name' => 'GoPay',
	'tags' => ['paygate','sell'],
	'setting' => [
		[
			'name' => __('Client ID', 'cms'),
			'id' => 'clientId',
			'type' => 'text',
		],
		[
			'name' => __('Client secret', 'cms'),
			'id' => 'clientSecret',
			'type' => 'text',
		],
		[
			'name' => __('Goid', 'cms'),
			'id' => 'goid',
			'type' => 'text',
		],
		[
			'id' => 'testMode',
			'type' => 'switch',
			'label' => __('Testovací režim', 'cms'),
		],
	],
]);
mwApiConnect()->addApi('stripe', [
	'name' => 'Stripe',
	'tags' => ['paygate','sell'],
	'setting' => [
		[
			'type' => 'info',
			'content' => '<a target="_blank" href="https://dashboard.stripe.com/test/apikeys">' . __('Získat přihlašovací údaje ve Vašem Stripe účtu.', 'cms') . '</a>',
		],
		[
			'name' => __('Publishable key', 'cms'),
			'id' => 'publishableKey',
			'type' => 'text',
		],
		[
			'name' => __('Secret key', 'cms'),
			'id' => 'secretKey',
			'type' => 'text',
		],
	],
]);
mwApiConnect()->addApi('packeta', [
	'name' => 'Zásilkovna',
	'tags' => ['sell'],
	'setting' => [
		[
			'name' => __('API klíč', 'cms'),
			'id' => 'api_key',
			'type' => 'text',
		],
		[
			'name' => __('API heslo', 'cms'),
			'id' => 'api_pas',
			'type' => 'text',
		],
		[
			'name' => __('Odesílatel', 'cms'),
			'id' => 'sender',
			'type' => 'text',
		],
	],
]);
mwApiConnect()->addApi('heureka', [
	'name' => 'Heureka',
	'tags' => ['sell'],
	'setting' => [
		[
			'type' => 'tabs',
			'id' => 'heureka',
			'tabs' => [

				'overeno' => [
					'name' => __('Ověřeno zákazníky', 'cms'),
					'setting' => [
						[
							'type' => 'info',
							'content' => __('K aktivaci služby "Ověřeno zákazníky" je potřeba zadat tajný klíč. Tajný klíč naleznete <a target="_blank" href="https://sluzby.heureka.cz/n/sluzby/certifikat-spokojenosti/">na svém účtu na Heuréce</a>.', 'cms'),
						],
						[
							'name' => __('Tajný klíč pro službu ověřeno zákazníky', 'cms'),
							'id' => 'secret_key',
							'type' => 'text',
						],
					],
				],
				'conversion' => [
					'name' => __('Konverze', 'cms'),
					'setting' => [
						[
							'type' => 'info',
							'content' => __('Pro aktivaci měření konverzí heurékou je nutné zadat veřejný klíč. Veřejný klíč naleznete <a target="_blank" href="https://sluzby.heureka.cz/obchody/mereni-konverzi/">na svém účtu na Heuréce</a>. Dokud neproběhne první objednávka, může Heuréka hlásit, že služba nebyla zprovozněna.', 'cms'),
						],
						[
							'name' => __('Veřejný klíč Heureka', 'cms'),
							'id' => 'api_key',
							'type' => 'text',
						],
					],
				],
				'xmlfeed' => [
					'name' => __('XML feed', 'cms'),
					'setting' => [
						[
							'type' => 'info',
							'content' => __('Pro import vašeho zboží na vyhledávač zboží Heuréka, je potřeba XML soubor (XMl feed) obsahující data o vašich produktech. Odkaz na tento XML soubor je potřeba zadat na příslušné místo při <a href="https://sluzby.heureka.cz/obchody/" target="_blank">registraci eshopu na Heurece</a>.', 'cms'),
						],
						[
							'name' => __('Odkaz na XML feedu pro Heuréku', 'cms'),
							'id' => 'eshop_feed',
							'type' => 'eshop_feed',
							'feed' => 'heureka',
						],
					],
				],
			],
		],
	],
]);
mwApiConnect()->addApi('zbozi', [
	'name' => 'Zboží.cz',
	'tags' => ['sell'],
	'setting' => [
		[
			'type' => 'tabs',
			'id' => 'zbozicz',
			'tabs' => [
				'conversion' => [
					'name' => __('Konverze', 'cms'),
					'setting' => [
						[
							'type' => 'info',
							'content' => __('Pro aktivování standartního měření konverzí na Zboží.cz, je potřeba zadat ID provozovny a tajný klíč, které naleznete po přihlášení na <a target="_blank" href="https://admin.zbozi.cz/premiseListScreen">svém účtu na Zboží.cz</a>. V sekci Provozovny vyberte možnost "měření konverzí".', 'cms'),
						],
						[
							'name' => __('ID provozovny', 'cms'),
							'id' => 'shop_id',
							'type' => 'text',
						],
						[
							'name' => __('Tajný klíč', 'cms'),
							'id' => 'private_key',
							'type' => 'text',
						],
						[
							'type' => 'switch',
							'id' => 'sandbox',
							'label' => __('Testovací režim (sandbox)', 'cms_member'),
						],
					],
				],
				'xmlfeed' => [
					'name' => __('XML feed', 'cms'),
					'setting' => [
						[
							'type' => 'info',
							'content' => __('Pokud chcete importovat vaši nabídku produktů na Zboží.cz postupujte podle <a href="https://napoveda.seznam.cz/cz/zbozi/napoveda-pro-internetove-obchody/registrace-seznam-penezenka/pridani-obchodu/" target="_blank">tohoto návodu</a>, kde v registračním formuláři uveďte do pole URL obchodu adresu úvodní stránky vašeho e-shopu a do pole URL feedu zadejte adresu feedu pro Zboží.cz', 'cms'),
						],
						[
							'name' => __('Adresa XML feedu pro Zboží.cz', 'cms'),
							'id' => 'eshop_feed',
							'type' => 'eshop_feed',
							'feed' => 'zbozi',
						],
					],
				],
			],
		],
	],
]);
//mwApiConnect()->addApi('mpohoda',[
//	'name' => 'mPOHODA',
//	'tags' => ['sell'],
//	'setting' => [
//		[
//			'type' => 'info',
//			// TODO remove "beta" from URL
//			'content' => '<a target="_blank" href="https://beta.mpohoda.cz/otevrene-api">' . __('Získat API klíč ve Vašem účtu.', 'cms') . '</a>',
//		],
//		[
//			'name' => __('API klíč', 'cms'),
//			'id' => 'api_key',
//			'type' => 'text',
//		],
//	]
//]);

MW()->add_templates([
	'upsell' => [
		'name' => __('Upsell stránky', 'cms_ve'),
		'lite' => true,
		'path' => '/modules/shop/templates/upsells/',
		'type' => 'mwupsell',
		'list' => [
			'upsells' => [
				'name' => '',
				'list' => ['1','2','3','4'],
			],
		],
	],
]);

MWInstallator()->addInstallSteps('upsell', [
	'steps' => [
		[
			'id' => 'title',
			'title' => __('Vyber upsell produkt', 'cms_ve'),
			'type' => 'productSelect',
			'button_text' => __('Použít produkt', 'cms_ve'),
		],
		[
			'id' => 'select_template',
			'title' => __('Vyber šablonu upsell stránky', 'cms_ve'),
			'type' => 'select_template',
		],
	],
]);

// Startup the main instance..
MWS();
require_once(__DIR__ . '/elements.php');
require_once(__DIR__ . '/elements-print.php');
MwsTypesRegistration::registerAll();

if (MWS()->getSelectedGatewayId() == 'mioweb') {
	mwSellingApi()->registerApiClass('mioweb');
}

global $vePage;
$vePage->add_editable_type(MWS_PRODUCT_CAT_SLUG);
$vePage->add_editable_type(MWS_PRODUCT_SLUG);
$vePage->add_editable_type(MWS_UPSELL_SLUG);

define('SHOP_VERSION', MioShop::version);
MW()->add_version('shop', SHOP_VERSION);
MW()->load_theme_lang('mwshop', get_template_directory() . '/modules/shop/languages');

if (MWS()->edit_mode) {
	MwsInitSetting::init(MWS()->isCreated());
	MwsInitSetting::initObjects();
}
