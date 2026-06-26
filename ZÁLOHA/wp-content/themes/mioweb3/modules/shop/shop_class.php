<?php

use Mioweb\Library\Api\ThePay\Exceptions\ThePayException;
use Mioweb\Shop\Document\Document;
use Mioweb\Shop\FormCart;
use Mioweb\Shop\FormDatabaseCart;
use Mioweb\Shop\FormProcessor;
use Mioweb\Shop\FormRenderer;
use Mioweb\Shop\InvoiceContactSettings;
use Mioweb\Shop\InvoicePdfGenerator;
use Mioweb\Shop\Listeners\MPohodaListener;
use Mioweb\Shop\Listeners\MwsFacebookConversionsListener;
use Mioweb\Shop\Order\Exporters\OrderExporterContainer;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\OrderAutomationProcessor;
use Mioweb\Shop\Order\OrderRepository;
use Mioweb\Shop\PacketSize;
use Mioweb\Shop\UnfinishedFormOrderProcessor;
use Mioweb\Shop\UpsellProcessor;
use Mioweb\VisualEditor\Lib\Image;
use Nette\Utils\Json;
use Mioweb\VisualEditor\Lib\Colors;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (class_exists('MioShop')) {
	return;
}

/**
 * Gets the main class of the shop. It is protected against multiple instances.
 */
function MWS(): MioShop
{
	return MioShop::instance();
}


/**
 * Core class of the Mioweb shop solution.
 *
 * @class MioShop
 * @since 1.0.0
 */
final class MioShop
{
	/** Release version number. */
	const version = '3.2.0';
	/** Minimal count of items in stock meaning enough items in stock. */
	const StockLimit_Plenty = 5;

	const MWS_FORM_NONCE = 'mws-form-nonce';

	/** @var MioShop Single instance holder. */
	private static $_instance = null;

	/** @var string Release version number. */
	public $version = self::version;

	/** @var array Shop setting options. */
	private $setting;

	/** @var array Shop visual setting options. */
	public $visual_setting; // @TODO private

	/** @var array Settings of permalink structure. */
	private $permalinks;

	/** @var bool True if current user an edit pages. */
	public $edit_mode = false; // @TODO private

	private $builder_mode = false;

	/** @var array|mixed Info about active template - url, dir... */
	private $template = [
		'url' => MWS_URL_BASE . '/templates/',
		'dir' => MWS_PATH_BASE . '/templates/',
	];

	/** @var string Name of thumbnail - notcroped/croped */
	public $thumb_name = '43'; // @TODO private

	/** @var array Two-dimensional array of currency conversion rater FROM unit TO unit. */
	private $_currencyConversionTable = [];

	/** @var MwsCurrent Property holder of current data for rendering phase. */
	private $_current = null;

	/** @var MwsAsyncLater */
	private $_async_SyncAll = null;

	/** @var MwsGateways List of available gateways for payments. */
	private $_gateways = null;

	/** @var MwsPaymentGateway[] */
	private $_paymentGateways = null;

	/** @var MwsVATs List of defined VATs. */
	private $_vats = null;

	/** @var MwsCart Content of shopping cart. */
	private $_cart;

	/** @var array<int, FormCart> */
	private $_formCarts;

	/** @var MwsPacketa */
	public $packeta = null; // @TODO private

	/** @var InvoicePdfGenerator */
	private $invoicePdfGenerator;

	/** @var FormRenderer */
	private $formRenderer;

	/** @var FormProcessor */
	private $formProcessor;

	private OrderAutomationProcessor $automationProcessor;

	private UnfinishedFormOrderProcessor $unfinishedFormOrdersProcessor;

	private UpsellProcessor $upsellProcessor;

	/** @var string currency code like czk */
	private $_defaultCurrency = null;

	/** @var string country code like CZ */
	private $_defaultShippingCountry = null;

	private ?OrderExporterContainer $_exporterContainer = null;

	private function __construct()
	{
		// check if edit mode
		if (current_user_can('edit_pages')) {
			$this->edit_mode = true;
		}
		$this->builder_mode = $this->edit_mode && !isset($_GET['mw_preview']);

		// load shop global settings
		$this->setting = get_option(MWS_OPTION_SHOP_SETTING);
		$this->setting['vat_rates'] = get_option(MWS_OPTION_SHOP_SETTING_VAT_RATES, []) ?: [];
		$this->reloadPermalinks();

		// load shop visual settings
		$this->visual_setting = get_option(MWS_OPTION_SHOP_APPEARANCE, []);
		$this->visual_setting = mwBackCompatibility::eshop_set($this->visual_setting);

		$this->thumb_name = $this->visual_setting['product_thumbnail'] ?? '43';

		$this->unfinishedFormOrdersProcessor = new UnfinishedFormOrderProcessor();

		$this->registerAutoloader();
		$this->registerHooks();
		$this->registerCronJobs();

		//Asynchronous support
		require_once('libs/wp-background-processing-master/wp-background-processing.php'); //direct
		require_once('libs/wp-async-task/wp-async-task.php'); //later
		new MwsAsyncNow();
		new MwsAsyncLater();

		require_once(__DIR__ . '/libs/packeta/Exceptions/PacketaCreateException.php');
		require_once(__DIR__ . '/libs/packeta/packeta_class.php');
		$this->packeta = new MwsPacketa($this->builder_mode);

		// Fire start
		do_action('mws_loaded');

		$this->invoicePdfGenerator = new InvoicePdfGenerator();
		$this->formRenderer = new FormRenderer();
		$this->formProcessor = new FormProcessor();
		$this->upsellProcessor = new UpsellProcessor($this->formProcessor);
		$this->automationProcessor = new OrderAutomationProcessor();

		MwsGoogleTagManagerListener::getInstance();
		MwsGoogleAnalyticsListener::getInstance();
		MPohodaListener::getInstance();

		if (!MW()->edit_mode) {
			MwsFacebookConversionsListener::getInstance();
		}
	}

	public function reloadPermalinks()
	{
		$this->permalinks = get_option(MWS_OPTION_PERMALINKS);
	}

	/** Register autoloading of MWS classes. */
	private function registerAutoloader()
	{
		spl_autoload_register([$this, 'autoload']);
	}

	private function registerHooks()
	{
		// Fire installation if not already installed.
		//add_action('cms_activation', [$this, 'checkInstallation']);

		// Hook template files search.
		add_filter('page_template_hierarchy', [$this, 'hookGetPageTemplate'], 10);
		add_filter('single_template_hierarchy', [$this, 'hookLocateTemplate_single'], 20);
		add_action('init', [$this, 'addRewriteRules']);
		add_action('query_vars', [$this, 'setQueryVars']);
		add_filter('template_include', [$this, 'includeTemplate'], 1000, 1);
		add_filter('taxonomy_template_hierarchy', [$this, 'hookLocateTemplate_taxonomy'], 20);
		add_filter('archive_template_hierarchy', [$this, 'hookLocateTemplate_archive'], 20);

		// Scripts, css
		add_action('wp_enqueue_scripts', [$this, 'hookEnqueueScripts'], 20);
		add_action('admin_enqueue_scripts', [$this, 'hookEnqueueScripts' /*'load_admin_scripts'*/]);

		// Ajax
		add_action('wp_ajax_nopriv_mws_gate_callback', ['MwsAjax', 'gateCallback']);
		add_action('wp_ajax_mws_gate_callback', ['MwsAjax', 'gateCallback']); //enabled in admin too
		// cart manipulation
		add_action('wp_ajax_nopriv_mws_cart_add', ['MwsAjax', 'cartAddItem']);
		add_action('wp_ajax_mws_cart_add', ['MwsAjax', 'cartAddItem']);
		add_action('wp_ajax_nopriv_mws_cart_remove', ['MwsAjax', 'cartRemoveItem']);
		add_action('wp_ajax_mws_cart_remove', ['MwsAjax', 'cartRemoveItem']);
		add_action('wp_ajax_mws_discount_code_remove', ['MwsAjax', 'cartRemoveDiscountCode']);
		add_action('wp_ajax_nopriv_mws_discount_code_remove', ['MwsAjax', 'cartRemoveDiscountCode']);
		// order manipulation
		add_action('wp_ajax_nopriv_mws_order_step', ['MwsAjax', 'orderStep']);
		add_action('wp_ajax_mws_order_step', ['MwsAjax', 'orderStep']);

		add_action('wp_ajax_mwsFastAddProductTag', ['MwsTag', 'fastAddProductTag_ajax']);

		// buy form
		add_action('wp_ajax_nopriv_mws_form_process', [$this, 'processForm']);
		add_action('wp_ajax_mws_form_process', [$this, 'processForm']);
		add_action('wp_ajax_nopriv_mws_form_reload_summary', [$this, 'reloadFormSummary']);
		add_action('wp_ajax_mws_form_reload_summary', [$this, 'reloadFormSummary']);

		// Upsells
		add_action('wp_ajax_nopriv_mws_upsell_process', [$this, 'processUpsell']);
		add_action('wp_ajax_mws_upsell_process', [$this, 'processUpsell']);
		add_action('wp_ajax_mws_add_upsell_item', ['MwShopFields', 'addUpsellItem_ajax']);

		// activation - create eshiop
		add_action('wp_ajax_mwsCreateEshop', ['MwsInstall', 'createEshop']);

		add_filter('mw_fast_nav_current', [$this, 'fast_nav_current']);

		// Administration
		if ($this->canEdit()) {
			require_once(__DIR__ . '/includes/order/order-admin.php');
			require_once(__DIR__ . '/includes/MwShopFields.php');
			add_filter('post_updated', [$this, 'hookPageSlugChanged'], 1000, 3);
			add_action('current_screen', [$this, 'hookAdminScreen']);

			add_filter('ve_page_type', [$this, 'hookChangePageType'], 10, 2);

			add_action('mw_add_list', [$this, 'hookAddList']);

			add_action('wp_ajax_mwsReloadDashboardStatistics', ['MwSellDashboard', 'dashboardStatistics_ajax']);

			// change option name in switch between global a local setting
			add_filter('mw_change_switch_option', [$this, 'change_switch_option'], 20, 2);

			add_action('admin_menu', [$this, 'modify_wp_admin_menu']); //add admin pages and setting fields
			add_filter('parent_file', [$this, 'modify_current_menu_parent']);
		}

		//Detection of need of synchronization
		add_action('pre_update_option_' . MWS_OPTION_SHOP_SETTING, [$this, 'hookShopOptionsChanged'], 10, 3);

		// Shop initialization
		add_action('init', [$this, 'init']);
		add_action('wp', [$this, 'wpLoaded'], 1000);

		// modify per page for product list
		add_action('pre_get_posts', [$this, 'hookModifyProductQuery']);

		if ($this->isCreated()) {
			add_action('mw_header_icon', [$this, 'hookInsertCartToHeader']);
		}
		if ($this->isCreated() && !$this->builder_mode) {
			//visual setting
			add_action('ve_global_setting', [$this, 'hookUseEshopVisual']);
			add_action('mw_global_styles', [$this, 'hookAddEshopStyles']);
		}

		if ($this->builder_mode && $this->isCreated()) {
			$page_id = $this->getHomePageId();
			$url = $page_id ? get_permalink($page_id) : '#';
			//$vePage->add_top_panel_menu(11,array('id'=>'eshop','title'=>'Eshop', 'url'=>$url, 'submenu'=>MWS()->create_eshop_menu()));
			global $vePage;
			$vePage->addFastNav(
				[
					'id' => 'eshop',
					'title' => __('Eshop', 'mwshop'),
					'url' => $url,
				],
				5
			);
		}
	}

	// TODO move to some cron library
	private function registerCronJobs()
	{
		add_filter('cron_schedules', function ($schedules) {
			$schedules['mw_thirty_minutes'] = [
				'interval' => 30 * 60,
				'display' => esc_html__('Every 30 Minutes'),];

			return $schedules;
		});

		add_action('unfinished_form_orders_process', [$this->unfinishedFormOrdersProcessor, 'processAll']);
		add_action('init', function () {
			$this->schedule_cron('unfinished_form_orders_process', 'mw_thirty_minutes');
		});
	}

	private function schedule_cron(string $hook, string $recurrence)
	{
		if (!wp_next_scheduled($hook)) {
			//condition to makes sure that the task is not re-created if it already exists
			wp_schedule_event(time(), $recurrence, $hook);
		}
	}

	public function canEdit(): bool
	{
		return current_user_can('edit_posts');
	}

	/** @return MioShop Returns singleton instance of MioShop. */
	public static function instance(): MioShop
	{
		if (!static::$_instance) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/** Autoloading mechanism of files. */
	public static function autoload($className)
	{
		static $paths = [
			'MwsInstall' => 'includes/install.php',
			'MwsTypesRegistration' => 'includes/types_registration.php',
			'MwsRewrite' => 'includes/rewrite.php',
			'MwsException' => 'includes/core.php',
			'ReverseChargeApplicationException' => 'includes/Exceptions/ReverseChargeApplicationException.php',
			'FormValidationException' => 'includes/Exceptions/FormValidationException.php',
			'Mioweb\Shop\Exceptions\InvalidPacketSizeException' => 'includes/Exceptions/InvalidPacketSizeException.php',
			'FapiGatewayCommunicationException' => 'includes/Exceptions/FapiGatewayCommunicationException.php',
			'Mioweb\Shop\Exceptions\MissingInvoiceContactException' => 'includes/Exceptions/MissingInvoiceContactException.php',
			'Mioweb\Shop\Order\Exceptions\OrderHasNoHashException' => 'includes/order/Exceptions/OrderHasNoHashException.php',
			'MwsUserException' => 'includes/core.php',
			'MwsCurrent' => 'includes/core.php',

			'MwsProduct' => 'includes/product.php',
			'MwsStockUpdate' => 'includes/product.php',
			'MwsProductAvailabilityStatus' => 'includes/product.php',

			'MwsProductRoot' => 'includes/product_root.php',
			'MwsProductVariant' => 'includes/product_variant.php',

			'MwsGateways' => 'includes/gateways.php',
			'MwsGatewayMeta' => 'includes/gateways.php',
			'MwsGatewayImpl' => 'includes/gateways.php',
			'MwsGatewayImpl_Fapi' => 'includes/gateway_fapi.php',

			'MwsCart' => 'includes/cart.php',
			'MwsCartTemporary' => 'includes/cart.php',
			'MwsCartTemp' => 'includes/cart.php',
			'MwsCartItems' => 'includes/cart.php',
			'MwsCartItem' => 'includes/cart.php',
			'Mioweb\Shop\FormCart' => 'includes/FormCart.php',
			'Mioweb\Shop\FormSessionCart' => 'includes/FormSessionCart.php',
			'Mioweb\Shop\FormDatabaseCart' => 'includes/FormDatabaseCart.php',

			'MwsVATs' => 'includes/VATs.php',
			'MwsVatAccounting' => 'includes/VATs.php',
			'MwsVatElectronicInvoicing' => 'includes/VATs.php',

			'MwsBasicEnum' => 'includes/enumerations.php',
			'MwsProductType' => 'includes/enumerations.php',
			'MwsDiscountCodeType' => 'includes/enumerations.php',
			'MwsSellRestriction' => 'includes/enumerations.php',
			'MwsSalePriceType' => 'includes/enumerations.php',
			'MwsOrderStep' => 'includes/enumerations.php',
			'MwsCurrency' => 'includes/MwsCurrency.php',
			'mwSettingObjectService_Currencies' => 'includes/MwsCurrency.php',
			'MwsCurrencyEnum' => 'includes/enumerations.php',
			'MwsCountry' => 'includes/enumerations.php',

			'MwsShippingCountry' => 'includes/MwsShippingCountry.php',
			'mwSettingObjectService_ShippingCountries' => 'includes/MwsShippingCountry.php',

			'MwsVatRateType' => 'includes/MwsVatRateType.php',

			'MwsSession' => 'includes/MwsSession.php',
			'Mioweb\Shop\MwsSessionSection' => 'includes/MwsSessionSection.php',
			'MwsAjax' => 'includes/ajax.php',

			'MwsAsyncNow' => 'includes/async.php',
			'MwsAsyncLater' => 'includes/async.php',

			'MwsShipping' => 'includes/shipping.php',
			'MwsDiscountCode' => 'includes/discount_code.php',
			'MwsShippingElectronic' => 'includes/shipping.php',
			'MwsPrice' => 'includes/MwsPrice.php',
			'MwsPayType' => 'includes/payments.php',
			'MwsPaymentMethod' => 'includes/MwsPaymentMethod.php',
			'MwsPayment' => 'includes/payment/MwsPayment.php',
			'MwsPaymentStatus' => 'includes/payment/MwsPaymentStatus.php',
			'MwsPaymentGateway' => 'includes/payment/MwsPaymentGateway.php',
			'MwsForm' => 'includes/MwsForm.php',
			'mwSettingObjectService_SaleForm' => 'includes/MwsForm.php',

			'Mioweb\Shop\Gates\ShopGate' => 'includes/Gates/ShopGate.php',
			'Mioweb\Shop\Gates\ShopGateRepository' => 'includes/Gates/ShopGateRepository.php',
			'Mioweb\Shop\Order\Order' => 'includes/order/Order.php',
			'Mioweb\Shop\Order\IOrder' => 'includes/order/IOrder.php',
			'Mioweb\Shop\Order\IOrderGate' => 'includes/order/IOrderGate.php',
			'Mioweb\Shop\Order\History\OrderHistory' => 'includes/order/History/OrderHistory.php',
			'Mioweb\Shop\Order\History\OrderHistoryRepository' => 'includes/order/History/OrderHistoryRepository.php',
			'MwsOrder' => 'includes/order/MwsOrder.php',
			'Mioweb\Shop\Order\OrderRepository' => 'includes/order/OrderRepository.php',
			'Mioweb\Shop\Order\OrderItems' => 'includes/order/OrderItems.php',
			'Mioweb\Shop\Order\OrderItem' => 'includes/order/OrderItem.php',
			'Mioweb\Shop\Order\OrderItemRepository' => 'includes/order/OrderItemRepository.php',
			'Mioweb\Shop\Order\OrderAutomationProcessor' => 'includes/order/OrderAutomationProcessor.php',
			'MwsOrderStatus' => 'includes/enumerations.php',
			'MwsOrderEvent' => 'includes/order/MwsOrderEvent.php',
			'Mioweb\Shop\Order\MwsOrderFetchRequest' => 'includes/order/MwsOrderFetchRequest.php',
			'MwsOrderSourceType' => 'includes/order/MwsOrderSourceType.php',
			'MwsOrderSource' => 'includes/order/MwsOrderSource.php',
			'Mioweb\Shop\Order\OrderGate' => 'includes/order/OrderGate.php',
			'mwSettingPageService_eshopEmails' => 'includes/mwSettingPageServiceEmails.php',
			'mwSettingPageService_eshopSetting' => 'includes/mwSettingPageServiceEshopSetting.php',
			'Mioweb\Shop\Upsell' => 'includes/Upsell.php',
			'Mioweb\Shop\mwSettingObjectService_Upsell' => 'includes/Upsell.php',
			'Mioweb\Shop\Exceptions\UpsellProcessException' => 'includes/Exceptions/UpsellProcessException.php',
			'Mioweb\Shop\InvoiceContactSettings' => 'includes/InvoiceContactSettings.php',

			'MwsProperty' => 'includes/property.php',
			'MwsPropertyValue' => 'includes/property.php',

			'MwsProductCodes' => 'includes/product-codes.php',

			'MwsRss' => 'includes/rss.php',

			'MwsNumberGenerator' => 'includes/MwsNumberGenerator.php',

			'MwsOrderVariables' => 'includes/MwsOrderVariables.php',

			'MwsCustomer' => 'includes/MwsCustomer.php',

			'MwsContact' => 'includes/MwsContact.php',
			'MwsPerson' => 'includes/MwsPerson.php',
			'MwsAddress' => 'includes/MwsAddress.php',
			'MwsCompany' => 'includes/MwsCompany.php',
			'MwsBankAccount' => 'includes/MwsBankAccount.php',

			'Mioweb\Shop\Document\Document' => 'includes/document/Document.php',
			'MwsDocumentType' => 'includes/document/MwsDocumentType.php',
			'MwsDocumentItem' => 'includes/document/MwsDocumentItem.php',
			'NoPriceForThatCurrencyException' => 'includes/document/Exceptions/NoPriceForThatCurrencyException.php',
			'MwsOrderItemType' => 'includes/order/MwsOrderItemType.php',
			'Mioweb\Shop\Order\OrderGateDocument' => 'includes/order/OrderGateDocument.php',

			'MwSellDashboard' => 'includes/MwSellDashboard.php',
			'MwSellStatistics' => 'includes/MwSellStatistics.php',

			'MwsInitSetting' => '/initSetting.php',

			// Exports
			'Mioweb\Shop\Order\Exporters\Exceptions\NoNoOrderExporterWithThisIdentifierException' => 'includes/order/Exporters/Exceptions/NoOrderExporterWithThisIdentifierException.php',
			'Mioweb\Shop\Order\Exporters\IOrderExporter' => 'includes/order/Exporters/IOrderExporter.php',
			'Mioweb\Shop\Order\Exporters\PohodaExporter' => 'includes/order/Exporters/PohodaExporter.php',
			'Mioweb\Shop\Order\Exporters\MoneyS3Exporter' => 'includes/order/Exporters/MoneyS3Exporter.php',
			'Mioweb\Shop\Order\Exporters\XmlExporter' => 'includes/order/Exporters/XmlExporter.php',
			'Mioweb\Shop\Order\Exporters\CsvExporter' => 'includes/order/Exporters/CsvExporter.php',
			'Mioweb\Shop\Order\Exporters\OrderExporterContainer' => 'includes/order/Exporters/OrderExporterContainer.php',

			// Zbozi
			'Mioweb\Shop\Seznam\ZboziFeedDeliveryEnum' => 'libs/seznam/ZboziFeedDeliveryEnum.php',

			// Listeners
			'MwsGoogleTagManagerListener' => 'includes/Listeners/MwsGoogleTagManagerListener.php',
			'MwsGoogleAnalyticsListener' => 'includes/Listeners/MwsGoogleAnalyticsListener.php',
			'Mioweb\Shop\Listeners\MPohodaListener' => 'includes/Listeners/MPohodaListener.php',
			'Mioweb\Shop\Listeners\MwsFacebookConversionsListener' => 'includes/Listeners/MwsFacebookConversionsListener.php',

			// API QRCODEPlatba
			'Mioweb\Shop\QrPlatba' => 'includes/QrPlatba.php',

			// product tags
			'MwsTag' => 'includes/MwsTag.php',

			// zásilkovna
			//'MwsPacketa' => 'libs/packeta/packeta_class.php',

			PacketSize::class => 'includes/PacketSize.php',

		//          'WP_Async_Task' => 'libs/wp-async-task/wp-async-task.php',
		];

		$className = ltrim($className, '\\'); // PHP namespace bug #49143

		if (isset($paths[$className])) {
			//          mwshoplog("$className ...autoloaded", MWLL_DEBUG, 'autoload');
			require_once(__DIR__ . '/' . $paths[$className]);
		}
	}

	/**
	 * Detect changes in critical pages.
	 *
	 * @param int $postId Post ID.
	 * @param WP_Post $postAfter Post object following the update.
	 * @param WP_Post $postBefore Post object before the update.
	 */
	public function hookPageSlugChanged($postId, $postAfter, $postBefore)
	{
		//Is this shop page or cart/order page?
		$orderPageId = $this->getOrderPageId();
		$homePageId = $this->getHomePageId();

		// Gateways callback hook
		if ($postId == $orderPageId || $postId == $homePageId) {
			$before = $postBefore->post_name;
			$after = $postAfter->post_name;
			if (!empty($after) && $after != $before) {
				mwshoplog('Slug-name for critical page (cart, eshop...) changed.', MWLL_INFO);
				$this->gateways()->clearSyncedAll();
				$this->async_SyncAllNeeded();
			}
		}
	}

	/** Buy a product directly skipping the steps of full order. Validate input at first. */
	public function processForm()
	{
		$this->formProcessor->processOrder();
	}

	public function getFormProcessor(): FormProcessor
	{
		return $this->formProcessor;
	}

	public function processUpsell()
	{
		$this->upsellProcessor->processUpsell();
	}

	public function getAutomationProcessor(): OrderAutomationProcessor
	{
		return $this->automationProcessor;
	}

	/**
	 * Returns ID of the cart/order shop page. If page id is not known, 0 is returned.
	 */
	public function getOrderPageId(): int
	{
		return (int) ($this->setting['order_page'] ?? 0);
	}

	public function setLinkLogoForHeader(): void
	{
		if (isset($this->setting['shop_logo_link']) && $this->setting['shop_logo_link'] === 'shop') {
			global $vePage;
			$vePage->display->home_url = get_permalink($this->getHomePageId());
			$vePage->display->home_id = $this->getHomePageId();
		}
	}
	/**
	 * Returns ID of the entry shop page with list of products. If page id is not known, 0 is returned.
	 */
	public function getHomePageId(): int
	{
		return (int) ($this->setting['home_page'] ?? 0);
	}

	public function hookChangePageType($page_type, $post_id): string
	{
		if ($page_type == 'page' && $post_id == $this->getOrderPageId()) {
			$page_type = 'cart';
		}

		return $page_type;
	}

	public function fast_nav_current($current): array
	{
		if ($this->isShop()) {
			$current['title'] = __('Eshop', 'mwshop');
			$current['url'] = get_permalink($this->getHomePageId());
		}

		return $current;
	}

	/**
	 * Get list of all registered gateways. Check gateway's "active" property to find out if it is enabled in shop's
	 * global settings.
	 */
	public function gateways(): MwsGateways
	{
		if (!$this->_gateways) {
			$this->_gateways = new MwsGateways();
		}

		return $this->_gateways;
	}

	/** @return MwsPaymentGateway[] */
	public function getPaymentGateways(): array
	{
		if ($this->_paymentGateways === null) {
			$this->_paymentGateways = [];
			foreach (['gopay', 'stripe', 'thepay'] as $paymentGatewayId) {
				$paymentGatewayApi = mwApiConnect()->getApi($paymentGatewayId);
				if ($paymentGatewayApi && $paymentGatewayApi->isConnected()) {
					$this->_paymentGateways[$paymentGatewayId] = $paymentGatewayApi->client();
				}
			}
		}

		return $this->_paymentGateways;
	}

	public function getPaymentGatewayById(string $id): ?MwsPaymentGateway
	{
		return $this->getPaymentGateways()[$id] ?? null;
	}

	/**
	 * Add synchronization request of gateways.
	 * Que resynchronization. Only gates requesting synchronization will be synchronized.
	 * Request is fired on WP_SHUTDOWN, that is after all option's modifications ARE SAVED! :o)
	 */
	public function async_SyncAllNeeded()
	{
		if ($this->gateways()->_syncDisabled) {
			mwshoplog(
				__('Požadovaná synchronizace platebních bran nebude provedena, neboť je zakázána nastavením.', 'mwshop'),
				MWLL_WARNING,
				'paygate'
			);

			return;
		}
		if (!$this->_async_SyncAll) {
			$this->_async_SyncAll = new MwsAsyncLater();
			$this->_async_SyncAll->data(['operation' => 'syncAll', 'sleep' => 0]);
			mwshoplog('Zařazen požadavek na synchronizaci platební brány.', MWLL_INFO, 'paygate');
		}
	}

	public static function hookLocateTemplate_single(array $templates): array
	{
		$obj = get_queried_object();

		//Redefine only product and order.
		if ($obj && in_array($obj->post_type, [MWS_PRODUCT_SLUG, MWS_DOCUMENT_SLUG, MWS_UPSELL_SLUG])) {
			$templates = [];
			$templates[] = MWS_REL_PATH_TEMPLATE . "/single-{$obj->post_type}.php";
			$templates[] = MWS_REL_PATH_TEMPLATE . '/single.php';
		}

		return $templates;
	}

	public function addRewriteRules(): void
	{
		add_rewrite_rule('^' . MWS_ORDER_SLUG . '?', 'index.php?' . MWS_ORDER_SLUG . '=1', 'top');
	}

	public function setQueryVars(array $vars): array
	{
		$vars[] = MWS_ORDER_SLUG;

		return $vars;
	}

	public function includeTemplate($template)
	{
		if ($this->isCurrentPageOrder()) {
			$newTemplate = TEMPLATEPATH . '/' . MWS_REL_PATH_TEMPLATE . '/order.php';

			$template = $newTemplate;
		}

		return $template;
	}

	public static function hookLocateTemplate_archive(array $templates): array
	{
		$postTypes = array_filter((array) get_query_var('post_type'));

		if (count($postTypes) == 1 && $postTypes[0] == MWS_PRODUCT_SLUG) {
			$templates = [];
			$postTypes = reset($postTypes);
			$templates[] = MWS_REL_PATH_TEMPLATE . "/archive-{$postTypes}.php";
		}
		//      $templates[] = 'archive.php';

		return $templates;
	}

	public static function hookLocateTemplate_taxonomy($templates)
	{
		$tax = array_filter((array) get_query_var('taxonomy'));

		if (count($tax) == 1 && $tax[0] == MWS_PRODUCT_CAT_SLUG) {
			$templates = [];
			$tax = reset($tax);
			$templates[] = MWS_REL_PATH_TEMPLATE . "/taxonomy-{$tax}.php";
		}
		//      $templates[] = 'archive.php';

		return $templates;
	}

	/**
	 * Returns ID of the important shop page. If page id is not known, -1 is returned.
	 *
	 * @param string $page Name of requested page.
	 * @return int
	 */
	public static function getPageId($page)
	{
		$page = (string) $page;

		return absint(intval(get_option(MWS_OPTION . $page, -1)));
	}

	public static function renderTplParts($slug, $name = '', $toString = false, array $args = [])
	{
		$templates = [];
		$name = (string) $name;
		if ($name !== '') {
			$templates[] = MWS_REL_PATH_TEMPLATE . "/parts/{$slug}-{$name}.php";
		}
		$templates[] = MWS_REL_PATH_TEMPLATE . "/parts/{$slug}.php";

		if ($toString) {
			ob_start();
			try {
				locate_template($templates, true, false, $args);
				$str = ob_get_contents();
				ob_end_clean();

				return $str;
			} catch (Exception $e) {
				ob_end_clean();

				throw $e;
			}
		} else {
			locate_template($templates, true, false, $args);
		}
	}

	/**
	 * Currently selected gateway for payments and order calculations
	 */
	public function getSelectedGatewayId(): string
	{
		return $this->setting['paygate'] ?? 'mioweb';
	}

	public function getVATs(): MwsVATs
	{
		if (!$this->_vats) {
			$this->_vats = new MwsVATs(
				$this->setting['vat_values'] ?? [],
				$this->setting['vat_accounting'] ?? MwsVatAccounting::noVat,
				$this->setting['vat_electronic_products_invoicing'] ?? MwsVatElectronicInvoicing::Inland,
				$this->setting['vat_rates'] ?? []
			);
		}

		return $this->_vats;
	}

	/**
	 * Alter query for product according to paging and product visibility settings.
	 */
	public function hookModifyProductQuery(WP_Query $query): WP_Query
	{
		global $wp_query;
		// do not modify local queries --> leads to infinite recursion
		if ($wp_query == $query) {
			// do not modify query for admin pages and detail pages
			if (
				!is_admin() && !isset($query->query_vars['own_per_page']) && !$query->is_single()
				&& (
					(isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == MWS_PRODUCT_SLUG)
					|| isset($query->query_vars[MWS_PRODUCT_CAT_SLUG])
				)
			) {
				// Paging
				$per_page = intval($this->visual_setting['per_page']);
				if (!$per_page) {
					$per_page = 16;
				}
				$query->set('posts_per_page', $per_page);

				// Visibility

				$invisibleIds = MwsProduct::getInvisibleProducts(true);
				if (!empty($invisibleIds)) {
					$query->set('post__not_in', $invisibleIds);
				}

				// Ordering
				if (isset($this->visual_setting['product_order'])) {
					if ($this->visual_setting['product_order'] == 'menu_order') {
						$orderby = ['menu_order' => 'ASC', 'date' => 'DESC'];
					} else {
						$orderby = $this->visual_setting['product_order'] == 'title' ? ['title' => 'ASC'] : $this->visual_setting['product_order'];
					}

					$query->set('orderby', $orderby);
				}

				$query->set('post_status', 'publish');
			}
		}

		return $query;
	}

	/**
	 * Alter query for product according to paging and product visibility settings.
	 */
	public function modifySearchQuery(WP_Query $query): WP_Query
	{
		if (isset($_GET['search_product']) && ((isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == MWS_PRODUCT_SLUG) || isset($query->query_vars[MWS_PRODUCT_CAT_SLUG]))) {
			$query->set('post_type', [MWS_PRODUCT_SLUG]);
			$query->set('s', $_GET['search_product']);
		}

		return $query;
	}

	/**
	 * Currently rendered entity. Stores information during rendering phase, like current product etc.
	 */
	public function current(): MwsCurrent
	{
		if (!$this->_current) {
			$this->_current = new MwsCurrent();
		}

		return $this->_current;
	}

	/** Add new item into the cart. Expects "count" and "product" to be set. */
	public function ajaxCartAdd()
	{
		$productId = $_REQUEST['product'];
		$count = $_REQUEST['count'];

		$product = MwsProduct::getOneById($productId);
		if (!$product) {
			echo 'Chyba pri vkladani do kosiku.';
		} else {
			$added = $this->getCart()->addItem($product, $count);
			//TODO Make output pretty.
			if ($added > 0) {
				echo "Do kosiku bylo vlozeno $count kus(u) polozky $productId.";
			} else {
				echo 'Do kosiku nebylo nic pridano';
			}
		}

		wp_die();
	}

	/**
	 * Initialization of MioShop. This is called in the Wordpress INIT hook, queued as the first item.
	 */
	public function init(): void
	{
		static::checkInstallation();

		MwsRss::addCustomRss();
	}

	/**
	 * Check installation status. Install or upgrade system if necessary.
	 */
	public function checkInstallation()
	{
		$installedVersion = get_option('mwshop_version');
		$latestVersion = static::version;

		if (!defined('IFRAME_REQUEST') && version_compare($installedVersion, $latestVersion, '<')) {
			MwsInstall::autoInstall();
		}
	}

	public function wpLoaded()
	{
		if ($this->isShop()) {
			global $vePage;
			$vePage->modul_type = 'eshop';

			//add eshop setting codes
			MwCodes()->addCodesFromOption('mw_eshop_codes', false);

			$step = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : '';
			$step = MwsOrderStep::checkedValue($step, MwsOrderStep::Cart);
			if ($step == MwsOrderStep::Cart) {
				if (isset($this->visual_setting['cart_content'])) {
					$google_fonts = get_post_meta($this->visual_setting['cart_content'], 've_google_fonts', true);
					$file_fonts = get_post_meta($this->visual_setting['cart_content'], 've_file_fonts', true);
					$vePage->display->google_fonts = $vePage->display->merge_fonts($vePage->display->google_fonts, $google_fonts);
					$vePage->display->file_fonts = $vePage->display->merge_fonts($vePage->display->file_fonts, $file_fonts);
				}
			} elseif ($step == MwsOrderStep::ThankYou) {
				if (isset($this->visual_setting['thanks_content'])) {
					$google_fonts = get_post_meta($this->visual_setting['thanks_content'], 've_google_fonts', true);
					$file_fonts = get_post_meta($this->visual_setting['thanks_content'], 've_file_fonts', true);
					$vePage->display->google_fonts = $vePage->display->merge_fonts($vePage->display->google_fonts, $google_fonts);
					$vePage->display->file_fonts = $vePage->display->merge_fonts($vePage->display->file_fonts, $file_fonts);
				}
			}
			$this->setLinkLogoForHeader();
		}

		if ($this->isCreated()) {
			add_action('body_class', [$this, 'hookAddBodyClass']);
		}

		$this->addShopConversionCodes();

		// modify search query
		add_action('pre_get_posts', [$this, 'modifySearchQuery']);
	}

	private function isCurrentPageOrder(): bool
	{
		return (bool) get_query_var(MWS_ORDER_SLUG);
	}

	private function isShop(): bool
	{
		global $vePage;

		return (is_post_type_archive(MWS_PRODUCT_SLUG) || is_singular(MWS_PRODUCT_SLUG) || is_tax(MWS_PRODUCT_CAT_SLUG)
				|| ($this->getHomePageId() && $vePage->post_id == $this->getHomePageId()) || ($this->getOrderPageId() && $vePage->post_id == $this->getOrderPageId()))
			&& !isset($_GET['window_editor']) ? true : false;
	}

	public function isCreated(): bool
	{
		return (bool) get_option('mw_eshop_created');
	}

	private function showCart(): bool
	{
		global $vePage;

		$modul_type = $vePage->modul_type;

		if (defined('DOING_AJAX') && DOING_AJAX) {
			$modul_type = $_POST['modul_type'];
		}

		$show = $modul_type == 'eshop' || ($modul_type == 'member' && isset($this->visual_setting['show_cart_header']['show_member'])) || ($modul_type == 'blog' && isset($this->visual_setting['show_cart_header']['show_blog'])) || ($modul_type == 'web' && isset($this->visual_setting['show_cart_header']['show_web'])) ? true : false;
		if (isset($vePage->display->header_setting['hide_cart'])) {
			$show = false;
		}

		return $show;
	}

	public function getCart(): MwsCart
	{
		if ($this->_cart === null) {
			$this->_cart = new MwsCart();
		}

		return $this->_cart;
	}

	public function getFormCart(MwsForm $form): FormCart
	{
		if (!isset($this->_formCarts[$form->getId()])) {
			$this->_formCarts[$form->getId()] = new FormDatabaseCart($form);
		}

		return $this->_formCarts[$form->getId()];
	}

	/**
	 * Get currency conversion rate of 1 unit in "from" into "to" currency. Assures positive result.
	 */
	public function getCurrencyConversionRate(string $from, string $to): float
	{
		// same to same currency
		if ($from === $to) {
			return 1.0;
		}
		$defaultCurrency = $this->getDefaultCurrency('key');
		// default currency use rate from table
		if ($from === $defaultCurrency) {
			return 1.0 / ($this->getDefaultCurrencyConversionTable()[$to] ?? 1);
		}

		return $this->getCurrencyConversionRate($defaultCurrency, $to) / $this->getCurrencyConversionRate($defaultCurrency, $from);
	}

	private function getDefaultCurrencyConversionTable(): array
	{
		$defaultCurrency = $this->getDefaultCurrency('key');
		if (!isset($this->_currencyConversionTable[$defaultCurrency])) {
			$defaultCurrencyConversionTable = MwsCurrencyEnum::getDefaultConversionTable($defaultCurrency, $this->edit_mode);
			foreach (MwsCurrency::getAll() as $currencySetting) {
				if ($currencySetting->isFixedExchangeRate() && $currencySetting->getFixedExchangeRate()) {
					$defaultCurrencyConversionTable[$currencySetting->getCurrency()] = $currencySetting->getFixedExchangeRate();
				}
			}
			$this->_currencyConversionTable[$defaultCurrency] = $defaultCurrencyConversionTable;
		}

		return $this->_currencyConversionTable[$defaultCurrency];
	}

	/** @return string[] All allowed currencies */
	public function getCurrencies(): array
	{
		return MwsCurrency::getList() ?: MwsCurrencyEnum::getAll(); // fallback return all currencies
	}

	public function getBankAccount(string $currency): ?MwsBankAccount
	{
		$curItem = MwsCurrency::getOneByCurrency($currency);
		if ($curItem) {
			return $curItem->getBankAccount();
		}

		return null;
	}

	/**
	 * Get default currency string.
	 *
	 * @param string $format Possible values are: "html"=printable into html, "attr"=printable into html attribute "raw"=no formatting=default formatting, "key"=value of {@link MwsCurrencyEnum}
	 */
	public function getDefaultCurrency(string $format = 'raw', $forceReload = false): string
	{
		if (!$this->_defaultCurrency || $forceReload) {
			$currency = $this->setting['currency'] ?? 'czk'; // fallback
			$this->_defaultCurrency = get_option(MWS_OPTION_DEFAULT_CURRENCY) ?: $currency;
		}

		$currency = MwsCurrencyEnum::checkedValue($this->_defaultCurrency, MwsCurrencyEnum::czk);
		$text = MwsCurrencyEnum::getSymbol($currency);
		switch ($format) {
			case 'html':
				return esc_html($text);
			case 'attr':
				return esc_attr($text);
			case 'key':
				return $currency;
			default:
				return $text;
		}
	}

	public function getCurrencyMode(): string
	{
		if (count($this->getCurrencies()) === 1) {
			return MwsCurrencyMode::Default;
		}

		return MwsCurrencyMode::ByCountry;
	}

	/**
	 * Returns list of availability statuses, that should be hidden.
	 *
	 * @param string $type One of [product|variant]
	 * @return array Array of <a href='psi_element://MwsProductAvailabilityStatus'>MwsProductAvailabilityStatus</a>.
	 */
	public function getHiddenAvailabilityStatusesFor($type = '')
	{
		if ($type == 'product') {
			$showThem = $this->stgShowUnavailableProduct();
		} elseif ($type = 'variant') {
			$showThem = $this->stgShowUnavailableVariant();
		} else {
			$showThem = false;
		}

		return $showThem
		? []
		: [
			MwsProductAvailabilityStatus::Unavailable_Disabled,
			MwsProductAvailabilityStatus::Unavailable_OutOfStock,
		];
	}

	/**
	 * Should be unavailable products displayed in catalog?
	 */
	public function stgShowUnavailableProduct(): bool
	{
		return $this->visual_setting['eshop_display_product']['unavailable_product'] ?? false;
	}

	/**
	 * Should be unavailable product variant displayed in catalog?
	 */
	public function stgShowUnavailableVariant(): bool
	{
		return $this->visual_setting['eshop_display_product']['unavailable_variant'] ?? false;
	}

	public function hookAdminScreen()
	{
		if (!$screen = get_current_screen()) {
			return;
		}

		switch ($screen->id) {
			case 'dashboard':
				break;
			case 'options-permalink':
				MwsRewrite::extendAdminPage();

				break;
			case 'users':
			case 'user':
			case 'profile':
			case 'user-edit':
				break;
		}
	}

	/**
	 * @param $value
	 * @param $oldValue
	 * @return mixed
	 */
	public function hookShopOptionsChanged($value, $oldValue)
	{
		mwshoplog(__METHOD__, MWLL_DEBUG);
		$resync = false;
		$gws = $this->gateways();

		// Optimization to check VAT and force resync only when needed. Gate's special settings sets the "isSynced" flag on its own.
		$oldVats = isset($oldValue['vat_values']) && is_array($oldValue['vat_values']) ? $oldValue['vat_values'] : [];
		$newVats = isset($value['vat_values']) && is_array($value['vat_values']) ? $value['vat_values'] : [];
		if (!($oldVats == $newVats)) {
			mwshoplog('VAT levels have been changed.', MWLL_INFO, 'settings');
			$resync = true;
		}

		$oldVatAccounting = $oldValue['vat_accounting'] ?? MwsVatAccounting::noVat;
		$newVatAccounting = $value['vat_accounting'] ?? MwsVatAccounting::noVat;
		if (!($oldVatAccounting == $newVatAccounting)) {
			mwshoplog('VAT accounting have been changed.', MWLL_INFO, 'settings');
			$resync = true;
		}

		// Order page changed? This is used in callbacks
		$oldCart = $oldValue['order_page'] ?? '';
		$newCart = $value['order_page'] ?? '';
		if (!($oldCart == $newCart)) {
			mwshoplog('Order/cart page changed.', MWLL_INFO, 'settings');
			$resync = true;
		}

		// Currency changed
		$oldCurrency = $oldValue['currency'] ?? '';
		$newCurrency = $value['currency'] ?? '';
		if (!($oldCurrency == $newCurrency)) {
			mwshoplog("Currency changed from [$oldCurrency] to [$newCurrency].", MWLL_INFO, 'settings');
			$resync = true;
		}

		// @TODO add check changes with influence to gate settings ... or sync all always after save?

		if ($resync) {
			$gws->clearSyncedAll();
		}

		$this->async_SyncAllNeeded();

		return $value;
	}

	public function hookGetPageTemplate($templates)
	{
		global $post;

		if ($this->getHomePageId() == $post->ID) {
			$templates = [MWS_REL_PATH_TEMPLATE . '/eshop-home.php'];
		} elseif ($this->getOrderPageId() == $post->ID) {
			$templates = [MWS_REL_PATH_TEMPLATE . '/eshop-order.php'];
		}

		if (isset($_GET['window_editor'])) {
			$templates = ['window_editor.php'];
		}

		return $templates;
	}

	public function hookEnqueueScripts()
	{
		$ver = filemtime($this->getTemplateFileDir('shop.css'));

		wp_register_style('mwsShop', $this->getTemplateFileUrl('shop.css'), ['ve-content-style'], $ver);
		wp_register_script('shop_front_script', $this->getTemplateFileUrl('shop.js'), ['ve_lightbox_script'], $ver);


		if ($this->builder_mode) {
			wp_enqueue_script('shop_admin_script', MWS_URL_BASE . '/js/admin.js', ['cms_lightbox_script'], filemtime(MWS_PATH_BASE . '/js/admin.js'));
			$this->load_admin_scripts();
		} elseif ($this->isShop() || ($this->edit_mode && !is_admin())) {
			wp_enqueue_script('shop_front_script');
			wp_enqueue_style('mwsShop');

			wp_enqueue_script('ve_lightbox_script');
			wp_enqueue_style('ve_lightbox_style');
		}

		$this->packeta->loadScripts($this->builder_mode);
	}

	function load_admin_scripts()
	{
		$ver = filemtime(MWS_PATH_BASE . '/css/admin.css');
		wp_enqueue_style('shop_admin_css', get_bloginfo('template_url') . '/modules/shop/css/admin.css', [], $ver);
		wp_enqueue_script('shop_admin_script', MWS_URL_BASE . '/js/admin.js', ['cms_lightbox_script'], filemtime(MWS_PATH_BASE . '/js/admin.js'));
	}

	private function getTemplateFileUrl($file): string
	{
		$file_url = $this->template['url'] . $file;

		return $file_url;
	}

	private function getTemplateFileDir($file): string
	{
		$file_dir = $this->template['dir'] . $file;

		return $file_dir;
	}

	public function getTemplateIcon($icon): string
	{
		return mw_icon('mwsi-' . $icon, '', $this->template['url'] . 'img/icons.svg');
	}

	/**
	 * Get URL for permalinks of product categories.
	 *
	 * @param array|null $stgPermProductCat Optional temporary settings as array that should be preferred over stored settings.
	 * @param array|null $stgPermProduct Optional temporary settings as array that should be preferred over stored settings.
	 */
	public function getPermalink_ProductCat($stgPermProductCat = null, $stgPermProduct = null): string
	{
		$permStg = $stgPermProductCat ?? ($this->permalinks['permalink_product_category'] ?? []);
		if (isset($permStg['use_nested']) && $permStg['use_nested']) {
			$valParent = $this->getPermalink_Products($stgPermProduct);
			$val = isset($permStg['value_nested']) && !empty($permStg['value_nested']) ? sanitize_title_with_dashes($permStg['value_nested'], MWS_PERMALINK_PRODUCT_CAT_NESTED_DEFAULT) : MWS_PERMALINK_PRODUCT_CAT_NESTED_DEFAULT;

			return $valParent . '/' . $val;
		}

		$val = isset($permStg['value']) && !empty($permStg['value']) ? sanitize_title_with_dashes($permStg['value'], MWS_PERMALINK_PRODUCT_CAT_DEFAULT) : MWS_PERMALINK_PRODUCT_CAT_DEFAULT;

		return $val;
	}

	/**
	 * Get URL for permalinks of products.
	 *
	 * @param array|null $stgPermProduct Optional temporary settings as array that should be preferred over stored settings.
	 */
	public function getPermalink_Products(?array $stgPermProduct = null): string
	{
		$permStg = $stgPermProduct ?? $this->permalinks['permalink_product'] ?? [];
		if (isset($permStg['value']) && !empty($permStg['value'])) {
			$val = sanitize_title_with_dashes($permStg['value'], MWS_PERMALINK_PRODUCT_DEFAULT);
		} else {
			$defaultVal = $val = MWS_PERMALINK_PRODUCT_DEFAULT;
			$iterator = 2;
			$maxIterations = 50; // Infinite loop prevention

			// Set first available slug
			while (get_page_by_path($val)) {
				$val = $defaultVal . $iterator;
				$iterator++;

				if ($iterator >= $maxIterations) {
					throw new \Exception('Cannot set product permalink. Maximum number of iterations reached.');
				}
			}
		}

		return $val;
	}

	/**
	 * Returns URL of home page of the shop - shop window
	 */
	public function getUrl_Home(): string
	{
		$pageId = $this->getHomePageId();
		if ($pageId) {
			$url = get_permalink($pageId);

			return $url;
		}

		return '';
	}

	/**
	 * Return URL of AJAX calls.
	 */
	public function getUrl_Ajax($queryParams = []): string
	{
		$url = admin_url('admin-ajax.php');
		if (!empty($queryParams) && is_array($queryParams)) {
			$url = add_query_arg($queryParams, $url);
		}

		return $url;
	}

	/**
	 * Returns URL of terms and conditions page of the shop
	 */
	public function getUrl_TermsAndConditions(): string
	{
		$pageId = $this->getTermsPageId();
		if ($pageId) {
			$url = get_permalink($pageId);

			return $url;
		}

		return '';
	}

	private function getTermsPageId(): int
	{
		return (int) ($this->setting['terms'] ?? 0);
	}

	public function isTermsAllowed(): bool
	{
		return !isset($this->setting['allow_terms']);
	}

	private function getMainTermsText(): string
	{
		return stripslashes($this->setting['terms_main_text'] ?? '');
	}

	public function printMissingTermsError()
	{
		if ($this->edit_mode) {
			$error = sprintf(__('Ještě vám chybí nastavit stránku s obchodními podmínkami. Stránku nastavíte v <a href="%s" target="_blank">základním nastavením e-shopu</a> v sekci Prodej a fakturace -> Obchodní podmínky a GDPR.', 'mwshop'), get_mw_admin_url(MWS_OPTION_SHOP_SETTING));

			return '<div class="mw_error_box">' . $error . '</div>';
		}

		$error = __('Nastavení e-shopu není správné, chybí souhlas s obchodními podmínkami, a objednávku nelze provést. Kontaktujte prosím správce webu.', 'mwshop');

		return '<div class="mws_info_box"><span class="info_icon">i</span>' . $error . '</div>';
	}

	/**
	 * Returns URL of personal data protection page
	 */
	public function getUrl_PersonalDataProtection(): string
	{
		$pageId = $this->getPersonalDataProtectionPageId();
		if ($pageId) {
			$url = get_permalink($pageId);
			if (!$url) {
				$url = get_home_url();
			}

			return $url;
		} else {
			return get_home_url(); // @TODO why return home?
		}
	}

	private function getPersonalDataProtectionPageId(): int
	{
		return (int) ($this->setting['gdpr_url'] ?? 0);
	}

	/**
	 * Generates URL to add a product/products into the cart.
	 *
	 * @param int|array $productId
	 * @return false|string
	 */
	public function getUrl_CartAdd($productId = null, $count = 1): string
	{
		$url = $this->getUrl_Cart();
		if (!empty($url)) {
			$arr = ['operation' => 'add'];
			if (!empty($productId)) {
				$arr['product'] = $productId;
				if ($count > 1) {
					$arr['count'] = $count;
				}
			}
			$url .= '?' . http_build_query($arr);
		}

		return $url;
	}

	/** Returns URL of cart/order page of the shop */
	public function getUrl_Cart(?int $step = null): string
	{
		$pageId = $this->getOrderPageId();
		if ($pageId) {
			$url = get_permalink($pageId);
			if (!empty($step)) {
				$arr = ['step' => $step];
				$url = add_query_arg($arr, $url);
			}

			return $url;
		} else {
			return '';
		}
	}

	/**
	 * Generates URL to remove a product/products from the cart.
	 *
	 * @param int|array $productId
	 * @return false|string
	 */
	public function getUrl_CartRemove($productId = null): string
	{
		$url = $this->getUrl_Cart();
		if (!empty($url)) {
			$arr = ['operation' => 'remove'];
			if (!empty($productId)) {
				$arr['product'] = $productId;
			}
			$url .= '?' . http_build_query($arr);
		}

		return $url;
	}

	function hookAddList()
	{
		if ($this->isCreated()) {
			echo '<li><a target="_blank" href="' . mwSetting()->getObject(MWS_PRODUCT_SLUG)->getAddUrl() . '">' . __('Produkt', 'mwshop') . '</a></li>';
		}
	}

	public function hookUseEshopVisual(): void
	{
		global $vePage;

		if ($this->isShop()) {
			$vePage->modul_type = 'eshop';

			$vePage->display->page_setting['background_color'] = $this->visual_setting['background_color'];
			$vePage->display->page_setting['background_image'] = $this->visual_setting['background_image'];

			$setting = get_option('eshop_header');
			//print_r($setting);
			if (isset($setting['show']) && $setting['show'] != 'global') {
				$vePage->display->header_setting = $setting;
				$vePage->display->used_header = 'eshop_header';
			}
			$setting = get_option('eshop_footer');
			if (isset($setting['show']) && $setting['show'] != 'global') {
				$vePage->display->footer_setting = $setting;
			}

			$vePage->display->popups->popups_setting = get_option('eshop_popups');
		}
	}

	public function hookAddEshopStyles()
	{
		global $vePage;

		$vePage->display->css->addGlobalStyles([
			'a.mws_product_title:hover,'
			. '.mws_top_panel .mw_vertical_menu li a:hover,'
			. '.mws_top_panel .mw_vertical_menu li a.mws_category_item_current,'
			. '.mws_shop_order_content h2 span.point' =>
			[
				'color' => $this->visual_setting['eshop_color'],
			],
			'.eshop_color_background,'
			. '.add_tocart_button,'
			. '.remove_fromcart_button,'
			. '.mws_shop_order_content .mw_checkbox:checked,'
			. '.mws_shop_order_content .mw_radio_button:checked::after,'
			. '.mws_form_quick .mw_checkbox:checked,'
			. '.mws_form_quick .mw_radio_button:checked::after,'
			. '.mws_cart_navigation:after' =>
			[
				'background-color' => $this->visual_setting['eshop_color'],
			],
			'a.eshop_color_background:hover, .add_tocart_button:hover, .mws_dropdown:hover .mws_dropdown_button, .mws_dropdown.mws_dropdown_opened .mws_dropdown_button' =>
			[
				'background-color' => Colors::shiftColor($this->visual_setting['eshop_color'], 0.9),
			],
			'.eshop_color_svg_hover:hover svg' =>
			[
				'fill' => $this->visual_setting['eshop_color'],
			],
			'.mw_tabs_element_style_3 .mw_tabs a.active, .mws_shop_order_content h2 span.point, .mws_shop_order_content .mw_checkbox:checked, .mws_shop_order_content .mw_radio_button:checked, .mws_form_quick .mw_checkbox:checked, .mws_form_quick .mw_radio_button:checked' =>
			[
				'border-color' => $this->visual_setting['eshop_color'],
			],
			'.mws_cart_step_item_a span.arrow' =>
			[
				'border-left' => '8px solid ' . $this->visual_setting['eshop_color'],
			],
			'.mws_shop_content .mws_product_list h3' =>
			[
				'font' => $this->visual_setting['font_title'] ?? '',
			],
			'.mws_shop_content .mws_product_list .mws_price_vatincluded' =>
			[
				'font' => $this->visual_setting['font_price'] ?? '',
			],
		]);
	}

	public function hookInsertCartToHeader(): void
	{
		if ($this->showCart()) {
			$cart = $this->getCart();
			$cartItemsCount = $cart->getItems()->count();

			?>
			<div id="mw_header_cart">
				<a class="mw_to_cart"
				   href="<?php echo $this->getUrl_Cart(); ?>"><?php echo mw_content_icon_set('shopping-cart'); ?>
					<span class="mws_cart_items_count"><?php echo $cartItemsCount; ?></span></a>
				<div
					class="mws_header_cart_hover <?php if ($cart->getItems()->isEmpty()) {
						echo 'mws_header_cart_hover_empty';
												 } ?>">
			<?php
			echo '<div class="mws_header_empty">' . __('Košík je prázdný', 'mwshop') . '</div>';
			echo '<table>';
			if (!$cart->getItems()->isEmpty()) {
				foreach ($cart->getItems()->getAll() as $cartItem) {
					$this->current()->setCartItem($cartItem);
					mwsRenderParts('cart', 'hover-items');
				}
			}
			echo '</table>';
			echo '<div class="mws_header_cart_footer">';
			echo '<a class="ve_content_button ve_content_button_1 eshop_color_background" href="' . $this->getUrl_Cart() . '">' . __('Do košíku', 'mwshop') . '</a>';
			echo '</div>';
			?>
				</div>
			</div>
			<?php
		}
	}

	function change_switch_option($option)
	{
		$opt = $option;
		if ($_POST['modul_type'] == 'eshop') {
			if ($option == 've_header') {
				$setting = get_option('eshop_header');
				if (isset($setting['show']) && $setting['show'] != 'global') {
					$opt = 'eshop_header';
				}
			} elseif ($option == 've_footer') {
				$setting = get_option('eshop_footer');
				if (isset($setting['show']) && $setting['show'] != 'global') {
					$opt = 'eshop_footer';
				}
			}
		}

		return $opt;
	}

	public function getInvoicePdfGenerator(): InvoicePdfGenerator
	{
		return $this->invoicePdfGenerator;
	}

	public function reloadFormSummary()
	{
		$formData = MwsAjax::getFormValues();
		$product = MwsProduct::getOneById((int) $formData['product']);
		$productPrice = $product->getPrice();

		$this->formRenderer->initCart($product, $productPrice, $formData);
		$return = [
			'price' => $this->formRenderer->getTotalPrice()->getPriceVatIncluded(),
			'summary' => $this->formRenderer->summary(),
			'active_discount_code' => $this->formRenderer->getCart()->getDiscountCode() !== null,
			'discount_code_error' => '',
			'shipping_prices' => $this->formRenderer->getShippingPrices(),
		];
/*
		if (($_POST['isQuickBuy'] ?? false) === 'true') {
			$this->formRenderer->initPaymentMethods();
			$return['payment_select'] = $this->formRenderer->paymentSelect($_POST['htmlId']);
		}
*/
		if ($_POST['checkDiscountCode'] === 'true') {
			$res = MwsAjax::validateDiscountCode($formData, $this->formRenderer->getCart());
			if (!$res['success']) {
				$return['discount_code_error'] = $res['errors']['discount_code'];
			}
		}

		wp_send_json_success($return);
	}


	public function renderQuickBuyForm(string $htmlId, MwsProduct $product, int $count = 1, bool $allowSimplified = false, bool $allowDiscount = false, ?string $thanksPage = null): string
	{
		$gw = MWS()->gateways()->getDefault();
		$shopCountry = MWS()->getEshopCountry();


		$allowSimplified = $allowSimplified && $shopCountry === MwsCountry::CZ && $gw->isSimplifiedInvoiceAllowedForQuickBuy();

		$this->formRenderer->init($product, $allowSimplified, $allowDiscount, $thanksPage);

		return $this->formRenderer->render($htmlId, null, 'mws_form_quick', $count, 'mws_cart_but eshop_color_background');
	}

	public function renderForm(string $htmlId, MwsForm $form, int $post_id = 0, string $class = '', string $but_class = '', string $but_text = ''): string
	{
		try {
			$settings = $form->getBasicSettings();
		} catch (MwsException $e) {
			mwlog('shop', $e->getMessage(), MWLL_ERROR);

			return __('Při výpisu formuláře došlo k chybě.', 'mwshop');
		}

		$productId = $form->getProductId();
		$product = MwsProduct::getOneById($productId);

		//Check product existence.
		if ($product === null) {
			$shopUrl = MWS()->getUrl_Home();

			return '<div class="mws_colorbox_message">'
					. __('Vybraný produkt není v našem obchodě k dispozici.', 'mwshop')
					. ($shopUrl ? ' ' . sprintf(__('Přejete si zobrazit <a href="%s"> naši nabídku?', 'mwshop'), $shopUrl) : '')
				. '</div>';
		}
		\assert($product instanceof MwsProduct);

		$this->formRenderer->initByForm($form);

		try {
			return $this->formRenderer->render($htmlId, $post_id, $class, 1, $but_class, $but_text);
		} catch (ThePayException $e) {
			return '<div class="mws_error">'
					. __('Nepodařilo se napojit na platební bránu.', 'mwshop')
					. (MW()->edit_mode ? ('(' . $e->getMessage() . ')') : '')
					. '</div>';
		}
	}

	/**
	 * Render HTML for purposes.
	 */
	public function renderPurposes(): string
	{
		$content = '';
		$purposes = $this->gateways()->getDefault()->getPurposes();

		foreach ($purposes as $purpose) {
			if (!$purpose['is_primary']) {
				$content .= '<li>';
				$content .= '<label class="mws_purpose mws_purpose_primary">';
				$content .= '<input class="mw_checkbox" type="checkbox" name="purposes[' . $purpose['id'] . ']" value="' . $purpose['id'] . '"/>';
				$content .= $purpose['html'];
				$content .= '</label>';
				$content .= '</li>';
			}
		}

		$personalDataProductionText = $this->setting['gdpr_text'] ?? '';

		$personalDataProductionUrl = $this->getUrl_PersonalDataProtection();
		if ($personalDataProductionUrl) {
			$personalDataProductionUrlText = $this->setting['gdpr_url_text'] ?? '';
			$personalDataProductionText .= ' <a target="_blank" href="' . $personalDataProductionUrl . '">' . ($personalDataProductionUrlText ?: __('Zásady zpracování osobních údajů', 'mwshop')) . '</a>';
		}

		if ($personalDataProductionText) {
			$content .= '<li><div class="mws_purpose mws_purpose_primary">' . $personalDataProductionText . '</div></li>';
		} else {
			$content .= '<li><div class="mw_error_box">' . __('Nastavení eshopu postrádá zásady zpracování osobních údajů.', 'mwshop') . '</div></li>';
		}

		return $content;
	}

	public function renderTerms(bool $checked = false): string
	{
		$termsUrl = $this->getUrl_TermsAndConditions();

		$terms = '<label class="mws_summarize_terms">
			<input class="mw_checkbox" type="checkbox" name="summarize[terms]" value="confirmed" ' . ($checked ? ' checked' : '') . '/>
			' . __('Souhlasím s', 'mwshop')
			. ' '
			. '<a href="' . $termsUrl . '" target="_blank">' . __('obchodními podmínkami', 'mwshop') . '</a>'
			. '.'
			. '
		</label>';

		if ($this->getMainTermsText()) {
			$terms .= '<div class="mws_main_terms_box">' . $this->getMainTermsText() . '</div>';
		}

		return $terms;
	}

	public function writeProducts($posts, $cols, $product_style, $element = [], $col_type = 'col-one', $css_id = '', $added = false, $row_set = [])
	{
		global $vePage;

		$content = '';

		$hide_desc = isset($this->visual_setting['hide_desc']) ? true : false;
		$hide_button = false;
		if (isset($element['product_style'])) {
			$hide_desc = isset($element['hide_desc']) ? true : false;
			$hide_button = isset($element['hide_buy']) ? true : false;
		}

		switch ($product_style) {
			case 'pre1':
				$style = '3';

				break;
			case 'pre2':
				$style = '6';

				break;
			case 'pre3':
				$style = '3';
				$hide_button = true;

				break;
			case 'pre4':
				$style = '3';
				$hide_desc = true;

				break;
			default:
				$style = $product_style;

				break;
		}

		if ($style == '6' || $style == '7' || $style == '7b') {
			$cols = 1;
		}

		$excerpt_length = $this->visual_setting['excerpt_length'] ?: 10;
		if (isset($element['excerpt_length']) && $element['excerpt_length']) {
			$excerpt_length = $element['excerpt_length'];
		}

		$font_title = $this->visual_setting['font_title'] ?? '';
		if (isset($element['font_title'])) {
			$font_title = $element['font_title'];
		}

		$font_price = $this->visual_setting['font_price'] ?? '';
		if (isset($element['font_price'])) {
			$font_price = $element['font_price'];
		}

		$font_description = '';
		if (isset($element['font_description'])) {
			$font_description = $element['font_description'];
		}


		$items_set = [];
		foreach ($posts as $product) {
			$class = '';

			$hide_item_button = $hide_button;

			$this->current()->setProduct($product);

			if ($hide_desc) {
				$class .= 'mws_product_hide_desc';
			}

			if ($hide_button) {
				$class .= ' mws_product_hide_button';
			}

			if ($product->getSoldOutText() !== '') {
				$hide_item_button = true;
				$class .= ' mws_product_not_available';
			}



			$product_footer = $hide_item_button ? '<div class="mws_product_footer mws_product_footer_nobut"><div class="mws_product_price">' . $product->htmlPriceSaleFull(null, 1, ['vatExcluded', 'salePercentage', 'saleDuration', 'discountSave']) . '</div></div>' : '<div class="mws_product_footer">
								<div class="mws_product_price">' . $product->htmlPriceSaleFull(null, 1, ['vatExcluded', 'vatExcluded', 'salePercentage', 'saleDuration', 'discountSave'], $vePage->display->get_font_class($font_price, 'text')) . '</div>
								<div class="mws_product_button">' . mwsRenderParts('cart', 'action-add', true) . '</div>
						</div>';

			$after_image = '<div class="mws_product_sale">' . $product->htmlPriceSaleFull(null, 1, ['vatExcluded', 'vatIncluded', 'salePrice', 'saleDuration', 'discountSave']) . '</div>';

			$availability = $product->getAvailabilityStatus(1);
			$class .= ' ' . $product->getAvailabilityCSS($availability);

			$args = [
				'link' => $product->getDetailUrl(),
				'image' => $product->getThumbnail(),
				'title' => $product->getName(),
				'description' => $product->getExcerpt($excerpt_length),
				'custom_footer' => $product_footer,
				'after_image' => $after_image,
				'edit_button' => $product->getEditButton(),
				'class' => $class,
				'labels' => $product->getTagsSet(),
			];

			$items_set[] = $args;
		}

		$valign = $hide_desc && $hide_button ? '' : 'between';

		$items_args = [
			'style' => $style,
			'cols' => $cols,
			'inside_col_type' => $col_type,
			'cols_type' => $element['cols_type'] ?? 'cols',
			'image_ratio' => $element['image_ratio'] ?? $this->thumb_name,
			'align' => $style == '6' || $style == '7' || $style == '7b' || $product_style == 'pre4' ? 'left' : 'center',
			'img_col_size' => 2,
			'styles' => [
				'font_title' => $font_title,
				'font_description' => $font_description,
			],
			'cssid' => $css_id,
			'added' => $added,
			'show_description' => $hide_desc ? false : true,
			'slider' => isset($element['use_slider']) ? true : false,
			'slider_setting' => $element['miocarousel_setting'] ?? '',
			'vertical_align' => $style == '6' || $style == '7' || $style == '7b' ? 'center' : $valign,
		];

		if (isset($element['background_set'])) {
			$items_args['background_set'] = $element['background_set'];
		}

		$content .= $vePage->display->generate_element_items($items_args, $items_set, $added, $row_set);

		return $content;
	}

	/**
	 * @param MwsPaymentMethod[] $allMethods
	 * @return MwsPaymentMethod[]
	 */
	public function filterAllowedPaymentMethods(array $allMethods, string $currency, string $country, bool $isElectronicContained): array
	{
		return array_filter($allMethods, function (MwsPaymentMethod $paymentMethod) use ($currency, $country, $isElectronicContained) {
			return ($paymentMethod->isVisible() && $this->isPaymentMethodAllowed($paymentMethod, $currency, $country, $isElectronicContained));
		});
	}

	public function isPaymentMethodAllowed(MwsPaymentMethod $paymentMethod, string $currency, string $country, bool $isElectronicContained): bool
	{
		$gateway = MWS()->gateways()->getDefault();

		// cash on delivery is not available if contain electronic product
		if ($paymentMethod->isCod() && $isElectronicContained) {
			return false;
		}

		if ($gateway->processPayments()) {
			foreach ($gateway->getEnabledPayments() as $payment) {
				if (
					$payment['payment_type'] === $paymentMethod->getType()
					&& ($paymentMethod->getBank() === null || $paymentMethod->getBank() === $payment['bank'])
				) {
					$currencies = $payment['currencies'];
					$countries = $payment['countries'];

					if (($currencies === null || in_array($currency, $currencies)) && ($countries === null || in_array(strtoupper($country), $countries))) {
						return true;
					}
				}
			}

			return false;
		}

		// payment method is from payment gateway

		if ($paymentMethod->isGateway()) {
			if (!$paymentMethod->isPaymentGatewayConnected()) {
				return false;
			}

			$gateway = MWS()->getPaymentGatewayById($paymentMethod->getPaymentGatewayId());

			return $gateway !== null && in_array($paymentMethod->getType(), $gateway->getEnabledPaymentMethodTypes($currency));
		}

		return $paymentMethod->getType() !== MwsPayType::Twisto || strtoupper($country) === 'CZ';
	}

	/* Admin edit page
	************************************************************************** */

	public function hookAddBodyClass(array $classes): array
	{
		if ($this->isShop()) {
			$classes[] = 'eshop_page';
		}
		if ($this->showCart()) {
			$classes[] = 'eshop_cart_header';
		}

		if ($this->isBoxedStyle()) {
			$classes[] = 'mws_content_fixed';
		}

		return $classes;
	}

	function isBoxedStyle()
	{
		return $this->isShop() && ((isset($this->visual_setting['background_color']) && $this->visual_setting['background_color'] && $this->visual_setting['background_color'] !== '#ffffff') || (isset($this->visual_setting['background_image']) && isset($this->visual_setting['background_image']['image']) && $this->visual_setting['background_image']['image'])) || (isset($this->visual_setting['background_image']) && isset($this->visual_setting['background_image']['pattern']) && $this->visual_setting['background_image']['pattern']);
	}


	/* Create eshop
	************************************************************************** */

	function getShopCategories($class = '', $all = 1, $categories = null)
	{
		if (!$categories) {
			$categories = mwTerm::getAll(MWS_PRODUCT_CAT_SLUG, ['parent' => 0, 'published' => 0]);
		}

		$cur_cat = get_queried_object();

		$cur = isset($cur_cat->term_id) ? false : true;

		$content = '<ul class="mws_category_menu_list">';
		if ($all) {
			$content .= '<li><a class="mws_category_item ' . $class . ' ' . ($cur ? 'mws_category_item_current' : '') . '" title="' . __('Vše', 'mwshop') . '" href="' . get_permalink($this->getHomePageId()) . '">' . __('Vše', 'mwshop') . '</a></li>';
		}
		foreach ($categories as $cat) {
			$cur = isset($cur_cat->term_id) && $cur_cat->term_id == $cat->getId() ? true : false;
			$content .= '<li><a class="mws_category_item ' . $class . ' ' . ($cur ? 'mws_category_item_current' : '') . '" title="' . $cat->getName() . '" href="' . $cat->getUrl() . '">' . $cat->getName() . '</a></li>';
		}
		$content .= '</ul>';

		$script = $this->edit_mode ? 'parent.location.href=this.value' : 'document.location.href=this.value';

		$content .= '<div class="mws_category_menu_select_container">';
		$content .= '<div class="mws_top_panel_label">' . __('Kategorie', 'mwshop') . '</div>';
		$content .= '<select class="mws_category_menu_select" onchange="' . $script . '">';
		$content .= '<option value="' . get_permalink($this->getHomePageId()) . '">' . __('Vše', 'mwshop') . '</option>';
		foreach ($categories as $cat) {
			$cur = isset($cur_cat->term_id) && $cur_cat->term_id == $cat->getId() ? true : false;
			$content .= '<option ' . ($cur ? 'selected="selected"' : '') . '" title="' . $cat->getName() . '" value="' . $cat->getUrl() . '">' . $cat->getName() . '</option>';
		}
		$content .= '</select></div>';

		return $content;
	}

	public function getGetPropertyDefs()
	{
		global $wp_query;
		$paged = $wp_query->query['paged'] ?? 1;

		$args = ['post_type' => MWS_PRODUCT_SLUG, 'paged' => $paged];
		query_posts($args);
	}

	/** @return MwsPaymentMethod[] */
	public function getPaymentMethods(): array
	{
		// @TODO some filtering?
		return MwsPaymentMethod::getAll();
	}

	/**
	 * Get list of supported countries.
	 */
	public function getSupportedCountries(): array
	{
		return $this->gateways()->getDefault()->getSupportedCountries();
	}

	public function getShippingCountries(): array
	{
		$selectedCountries = MwsShippingCountry::getList();
		$supportedCountries = $this->getSupportedCountries();

		return $selectedCountries ? array_intersect($selectedCountries, $supportedCountries) : $supportedCountries;
	}

	public function getDefaultShippingCountry(bool $forceReload = false): string
	{
		if (!$this->_defaultShippingCountry || $forceReload) {
			$this->_defaultShippingCountry = get_option(MWS_OPTION_DEFAULT_SHIPPING_COUNTRY);
		}

		// fallback if default country not set
		return MwsCountry::checkedValue($this->_defaultShippingCountry, MwsCountry::CZ);
	}

	public function getEshopCountry(): string
	{
		$country = $this->setting['country'] ?? null;

		// fallback if default country not set
		return MwsCountry::checkedValue($country, MwsCountry::CZ);
	}

	public function getMinOrderPrice(): ?MwsPrice
	{
		$minOrderPrice = $this->setting['min_order'] ?? null;
		if ($minOrderPrice) {
			return new MwsPrice((float) $minOrderPrice);
		}

		return null;
	}

	public function getOrderNumberGenerator(bool $isTest = false): MwsNumberGenerator
	{
		$prefix = $isTest ? 'TEST' : '';
		$prefix .= $this->setting['order_nums']['prefix'] ?? '';

		return new MwsNumberGenerator(
			'order',
			$prefix,
			(int) ($this->setting['order_nums']['characters'] ?? 10),
			(int) ($this->setting['order_nums']['start'] ?? 1),
			function (string $number) {
				return OrderRepository::getOrderByOrderNum($number) !== null;
			}
		);
	}

	public function getDocumentNumberGenerator(string $documentType, bool $isTest = false): MwsNumberGenerator
	{
		$prefix = $isTest ? 'TEST' : '';
		$prefix .= $this->setting[$documentType . '_nums']['prefix'] ?? '';

		return new MwsNumberGenerator(
			$documentType,
			$prefix,
			(int) ($this->setting[$documentType . '_nums']['characters'] ?? 10),
			(int) ($this->setting[$documentType . '_nums']['start'] ?? 1),
			function (string $number) use ($documentType) {
				return Document::getOneByNumber($documentType, $number) !== null;
			}
		);
	}

	public function getInvoiceLogo(): ?array
	{
		$logo = $this->setting['invoice_logo'] ?? null;

		$imageExist = is_array($logo) && Image::existImage((int) $logo['imageid']);

		return $logo && $logo['imageid'] && $imageExist ? $logo : null;
	}

	public function getInvoiceNote(Order $order): string
	{
		if (isset($this->setting['invoice_note'])) {
			$variables = MwsOrderVariables::fromMwOrder($order);
			$content = nl2br(strip_tags($this->setting['invoice_note'] ?? '', '<b><i><strong>'));

			return MwVariables::replaceVariables($content, $variables->toArrayFormatted());
		}

		return '';
	}

	/** @return InvoiceContactSettings Value object with various settings. 'showPhone' and 'showEmail' setting contains bool value indicating whether are those customer contact fields allowed to print on the invoice. */
	public function getInvoiceContactSettings(): InvoiceContactSettings
	{
		return new InvoiceContactSettings(
				isset($this->setting['invoice_contact']['show_phone']),
				isset($this->setting['invoice_contact']['show_email'])
		);
	}

	public function isAutoInvoiceEnabled(): bool
	{
		return isset($this->setting['automatic_invoice_disabled']) ? false : true;
	}

	public function getSupplierContact(): ?MwsContact
	{
		if ($this->getSelectedGatewayId() === 'mioweb') {
			if (!($this->setting['company_name'] ?? null)) {
				return null;
			}

			return new MwsContact(
					$this->setting['sender_mail'] ?? get_option('admin_email'),
					$this->setting['phone'] ?? null,
					null,
					new MwsCompany(
							$this->setting['company_name'] ?? '',
							$this->setting['company_id'] ?? null,
							$this->setting['company_tax_id'] ?? null,
							$this->setting['company_vat_id'] ?? null
					),
					new MwsAddress(
							$this->setting['country'],
							$this->setting['city'] ?? '',
							$this->setting['zip'] ?? '',
							$this->setting['street'] ?? ''
					)
			);
		}

		return $this->getSelectedGatewayId() === 'fapi' ? $this->gateways()->getById('fapi')->sharedInstance()->getSupplierContact() : null;
	}

	public function getEmailSetting(): array
	{
		return get_option('eshop_emails') ?: [];
	}

	public function isEmailEnabled(string $emailType): bool
	{
		if ($this->getSelectedGatewayId() === 'mioweb') {
			// All e-mails are enabled for "mioweb" gateway
			return true;
		}

		$eshopEmails = get_option('eshop_emails') ?: [];

		return (bool) ($eshopEmails[$emailType]['enabled'] ?? true);
	}

	/** @param int $customEmailIndex This parameter is mandatory, if the emailType is CustomEmails. It specifies the custom email index */
	public function getEmailSubject(string $emailType, Order $order, int $customEmailIndex = 0): string
	{
		$eshopEmails = get_option('eshop_emails') ?: [];
		$variables = MwsOrderVariables::fromMwOrder($order);
		$subject = $emailType === MwsEmailType::CustomEmails ?
				($eshopEmails[$emailType][$customEmailIndex]['email']['subject'] ?? '') :
				($eshopEmails[$emailType]['subject'] ?? '');

		return MwVariables::replaceVariables($subject, $variables->toArrayFormatted());
	}

	/** @param int $customEmailIndex This parameter is mandatory, if the emailType is CustomEmails. It specifies the custom email index */
	public function getEmailContent(string $emailType, Order $order, int $customEmailIndex = 0): ?string
	{
		$eshopEmails = get_option('eshop_emails') ?: [];
		$content = $emailType === MwsEmailType::CustomEmails ?
				($eshopEmails[$emailType][$customEmailIndex]['email']['content'] ?? '') :
				($eshopEmails[$emailType]['content'] ?? '');

		$content = nl2br(strip_tags($content, '<b><i><strong>'));
		$subject = $this->getEmailSubject($emailType, $order, $customEmailIndex);
		if ($subject) {
			$content = '<h1>' . $subject . '</h1><br>' . $content;
		}
		$variables = MwsOrderVariables::fromMwOrder($order);

		return MwVariables::replaceVariables($content, $variables->toArrayFormatted());
	}

	/** @param int $customEmailIndex This parameter is mandatory, if the emailType is CustomEmails. It specifies the custom email index */
	public function getEmailAttachment(string $emailType, Order $order, int $customEmailIndex = 0): ?string
	{
		$eshopEmails = get_option('eshop_emails') ?: [];
		$file = $emailType === MwsEmailType::CustomEmails ?
				($eshopEmails[$emailType][$customEmailIndex]['email']['attachment'] ?? '') :
				($eshopEmails[$emailType]['attachment'] ?? '');

		return $order->processFileName($file);
	}

	public function getSenderName(): ?string
	{
		return $this->setting['sender_name'] ?? null;
	}

	public function getSenderEmail(): ?string
	{
		return $this->setting['sender_mail'] ?? null;
	}

	public function getNotificationContact(): ?MwsContact
	{
		$email = ($this->setting['notification_mail'] ?? null);

		return $email ? new MwsContact($email) : null;
	}

	public function isPhoneRequired(): bool
	{
		return $this->setting['phone_required'] ?? false;
	}

	public function getGlobalAutomations(): array
	{
		$actions = get_option('eshop_actions') ?: [];

		return isset($actions['actions']) && is_array($actions['actions']) ? $actions['actions'] : [];
	}

	/** @TODO move */
	public function isPhoneValid($phoneNumber, $country = 'CZ'): array
	{
		if ($country != 'PL' && $country != 'CZ' && $country != 'SK') {
			return [
				'valid' => true,
				'error_msg' => '',
			];
		}


		$pre = '420';
		if ($country == 'PL') {
			$pre = '48';
		} elseif ($country == 'SK') {
			$pre = '421';
		}

		$pattern = '~^(\+\d{2,3}|\+\(\d{2,3}\)|00\d{2,3})? ?0?\d{3} ?\d{3} ?\d{3}$~';

		return preg_match($pattern, $phoneNumber) ? [
				'valid' => true,
				'error_msg' => '',
		] : [
				'valid' => false,
				'error_msg' => __('Zadané telefonní číslo není ve vaší fakturační zemi platné. V případě zahraničních čísel uveďte před číslem i mezinárodní předvolbu. Správný formát telefonního čísla je např.: "+420733987123.', 'mwshop'),
		];
	}

	public function getExporterContainer(): OrderExporterContainer
	{
		if ($this->_exporterContainer === null) {
			$this->_exporterContainer = new OrderExporterContainer();
		}

		return $this->_exporterContainer;
	}

	function modify_wp_admin_menu()
	{
		add_menu_page(__('Mioweb', 'cms'), __('Mioweb', 'cms'), 'manage_options', 'mioweb', '', '', 30);
		add_submenu_page('mioweb', 'Custom Post Type Admin', 'Kategorie eshopu', 'manage_options', 'edit-tags.php?taxonomy=' . MWS_PRODUCT_CAT_SLUG);
	}

	function modify_current_menu_parent($parent_file)
	{
		global $current_screen;
		if ($current_screen->taxonomy === 'eshop_category') {
			$parent_file = 'mioweb';
		}

		return $parent_file;
	}

	/** Print conversion codes of successfully ordered products. */
	function addShopConversionCodes()
	{
		global $vePage;

		$order = null;
		$gwId = $_REQUEST['gw'] ?? null;
		if ($gwId !== null) {
			$gw = $this->gateways()->getById($gwId);
			if ($gw === null) {
				$gw = $this->gateways()->getDefault();
			}

			/** @var Order $order */
			$order = $gw->sharedInstance()->getOrderFromThankYou();
		}

		if ($order === null) {
			return;
		}

		foreach ($order->getItems()->getProducts() as $item) {
			$item->getProduct()->addConversionCodes();
		}

		$data = MWDB()->getOption('mw_eshop_codes');
		MwCodes()->addConversionCodesFromData($data);

		// heureka
		$heureka = new MwHeureka();
		MwCodes()->addCode($heureka->heurekaConversionCode($order));

		// Zboží.cz - měření konverzí
		$zbozi = new MwZboziCz();
		//$zbozi->useSandbox(true);
		MwCodes()->addCode($zbozi->makeConversion($order));

		//Google tag manager
		MwsGoogleTagManagerListener::pushPurchase($order);
		// GA
		MwsGoogleAnalyticsListener::purchaseEvent($order);
		//Facebook Conversions API
		if (!MW()->edit_mode && MWFBC()->isActive()) {
			MwsFacebookConversionsListener::purchaseEvent($order);
		}
	}

}
