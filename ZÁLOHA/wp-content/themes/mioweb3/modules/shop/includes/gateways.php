<?php
/**
 * Payment gateways support.
 * Global object of {@link MwsGateways} is instantiated in {@link MWC()} and contains description of all registered
 * gateways.
 */

use Mioweb\Shop\Gates\ShopGateRepository;
use Mioweb\Shop\Order\IOrder;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\OrderGate;
use Mioweb\Shop\Order\OrderItem;
use Mioweb\Shop\Order\OrderRepository;

/**
 * List of available gateways and their settings.
 */
class MwsGateways
{

	/** @var array List of gateway descriptors {@link MwsGatewayMeta}. */
	private $_gateways = [];

	public $_syncDisabled = false; // @TODO make private

	function __construct()
	{
		apply_filters('mws_gateway_register', $this);
		$this->_syncDisabled = (defined('MWS_DISABLE_PAYGATE_SYNC') && MWS_DISABLE_PAYGATE_SYNC); // @TODO why?
	}

	/**
	 * Adds new gateway into list of available gateways. Duplicate addition of gate with the same id is not available.
	 */
	public function registerGw(MwsGatewayMeta $gw): void
	{
		if (!isset($this->_gateways[$gw->id])) {
			$this->_gateways[$gw->id] = $gw;
		}
	}

	/**
	 * Returns gateway descriptor instance with passed ID or "null".
	 */
	public function getById(string $gwId): ?MwsGatewayMeta
	{
		return $this->_gateways[$gwId] ?? null;
	}

	/** @return MwsGatewayMeta[] */
	public function getAll(): array
	{
		return $this->_gateways;
	}

	/**
	 * Synchronize all enabled and connected gateways.
	 */
	public function synchronizeAll(): bool
	{
		mwshoplog(__METHOD__, MWLL_DEBUG);
		if ($this->_syncDisabled) {
			mwshoplog(__('Synchronizace platebních/fakturačních bran zakázáná, neprovádí se.', 'mwshop'), MWLL_WARNING, 'paygate');

			return false;
		}
		$res = true;
		foreach ($this->getAll() as $gw) {
			$res = $res && $gw->synchronize();
		}

		return $res;
	}

	/**
	 * Clear "isSynced" flag of all gates.
	 */
	public function clearSyncedAll(): void
	{
		foreach ($this->getAll() as $gw) {
			$gw->setSynced(false);
		}
	}

	/**
	 * Tells whether synchronization of some gate is needed.
	 */
	public function syncNeeded(): bool
	{
		foreach ($this->getAll() as $gw) {
			if (!$gw->isSynced()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns default gateway, that is currently active gateway selected in global shop options.
	 */
	public function getDefault(): MwsGatewayMeta
	{
		// @TODO what if gate not exist we need some...
		return $this->getById(MWS()->getSelectedGatewayId());
	}

	/**
	 * Load information from the gateway for volatile caching for a specified order.
	 */
	public function loadOrderFor(IOrder $order): ?OrderGate
	{
		mwshoplog(__METHOD__ . ' order=' . $order->getNumber(), MWLL_DEBUG, 'paygate');
		$gate = $this->getById($order->getGateIdentifier());
		if (!$gate) {
			mwshoplog('Paygate [' . $order->getGateIdentifier() . '] of order ' . $order->getNumber() . ' is not supported.', MWLL_WARNING, 'paygate');

			return null;
		}

		return $gate->sharedInstance()->loadOrderGate($order);
	}

	/**
	 * Fills property {@link Order::gateLive} of passed orders. It tries to make it effectively in less API gate calls.
	 */
	public function preloadOrdersGateLive(Order ...$orders): void
	{
		// Create array for splitting orders by gate.
		$gateOrders = [];
		// Split orders into array by its gateId.
		foreach ($orders as $order) {
			$gateOrders[$order->getGateIdentifier()][] = $order;
		}
		// Load gate data at once, separately for each gate
		foreach ($gateOrders as $gateId => $orders) {
			$gate = $this->getById($gateId);
			if (!$gate) {
				continue;
			}
			$gate->sharedInstance()->preloadOrdersGateLive(...$orders);
		}
	}
}

/**
 * @todo Refactor
 * Describing information of tha gateway. Basically its id, caption, capabilities. Can create new instance of gateway
 * or use shared global instance.
 */
class MwsGatewayMeta
{

	/** @var string Id of the gateway. Format of identifier (no spaces...). Can be used as ID. */
	public $id = ''; // @TODO private

	public $caption = '';

	/** @var string Class name of implementing class, ancestor of {@link MwsGatewayImpl}. */
	private $class = '';

	/** @var string Absolute path to the file with implementation of class {@link MwsGatewayMeta::class}. */
	private $filepath;

	/** @var null|array Setting of the gateway. Stored as option. Use {@link loadSettings()} and {@link saveSettings()} for manipulation. */
	private $_settings = null;

	/** @var null|array Remote settings of the gateway. Stored as transient option. Use {@link getRemoteSettings()}. */
	private $remoteGateStgs = null;

	/** @var null|array Remote paytypes of the gateway. Stored as transient option. Use {@link getEnabledPayTypes()}. */
	private $remotePayTypes = null;

	/** @var null|array Remote paytypes of the gateway. Stored as transient option. Use {@link getEnabledPayments()}. */
	private $remotePayments = null;

	/** @var null|array Remote purposes. Stored as transient option. Use {@link getPurposes()}. */
	private $purposes = null;

	/** @var MwsGatewayImpl Globally shared instance of the gateway, accessible by {@link instance()}, automatically created. */
	private $instance = null;

	/**
	 * Creates description of new gateway.
	 *
	 * @param string $id Id of the gateway. Must comply with format of PHP identifier.
	 * @param string $caption Localized title of the gateway. It is used in UI.
	 * @param string $class Name of the implementing class. Implementing class must be ancestor of {@MwsGatewayImpl}.
	 * @param string $filepath Absolute path to the file with implementing class. It is require_once() when the
	 *                  implementation is needed.
	 */
	function __construct(string $id, string $caption, string $class, string $filepath)
	{
		$this->id = $id;
		$this->caption = $caption ?: $id;
		$this->class = $class;
		$this->filepath = $filepath;
	}

	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Returns shared instance. It is save to reuse it withing same thread.
	 */
	public function sharedInstance(): MwsGatewayImpl
	{
		if (!$this->instance) {
			$this->instance = $this->newInstance();
		}

		return $this->instance;
	}

	/**
	 * Returns new unique instance of the gateway.
	 *
	 * @return MwsGatewayImpl
	 */
	private function newInstance(): MwsGatewayImpl
	{
		if ($this->filepath) {
			require_once($this->filepath);
		}

		return new $this->class($this);
	}

	/**
	 * Returns true if gateway is enabled for synchronization.
	 */
	public function isActive(): bool
	{
		$gateId = MWS()->getSelectedGatewayId();

		return $gateId === $this->id;
	}

	/**
	 * Synchronize one gateway. Flag of gate's synchronization status is updated at the end.
	 *
	 * @param bool $force Synchronization is not performed when saved synchronization flag "isSynced" is set to false. Setting
	 *                    this argument to true forces synchronization to perform.
	 * @return bool Returns true when there was no error during synchronization. Disabled and/od unconnected gates requires
	 *              no synchronization and therefore they returns true too.
	 */
	public function synchronize(bool $force = false): bool
	{
		$perform = $force || !$this->isSynced();
		if (!$perform) {
			// No synchronization necessary, quit.
			mwshoplog(__METHOD__ . ' [' . $this->id . '] ... skipped', MWLL_DEBUG, 'paygate');

			return true;
		}

		$res = true;
		if ($this->isActive()) {
			mwshoplog("Synchronization of [$this->id] started.", MWLL_INFO, 'paygate');

			//Clear invalid cache items.
			$this->dropCache_EnabledPayTypes();
			$this->dropCache_EnabledPayments();
			$this->dropCache_Purposes();

			//Perform sync.
			$inst = $this->sharedInstance();
			$res = $inst->isConnected();
			if ($res) {
				$res = $inst->syncSettings();
				if (!$res) {
					mwshoplog("Synchronization of settings of [$this->id] failed.", MWLL_ERROR, 'paygate');
				}
			} else {
				mwshoplog("Synchronization of gate [$this->id] failed. Paygate is not connected.", MWLL_ERROR, 'paygate');
			}
		} else {
			mwshoplog("Synchronization of settings of [$this->id] skipped. Gateway is disabled.", MWLL_DEBUG, 'paygate');
		}
		//Update saved flag.
		$this->setSynced($res);
		mwshoplog("Synchronization of [$this->id] finished " . ($res ? 'successfuly' : 'with errors') . '.', MWLL_INFO, 'paygate');

		return $res;
	}

	public function isSynced(): bool
	{
		return (bool) get_option(MWS_OPTION . 'gate_' . $this->getId() . '_synced', false);
	}

	public function setSynced(bool $synced): void
	{
		update_option(MWS_OPTION . 'gate_' . $this->getId() . '_synced', $synced);
	}

	/**
	 * Load gate's settings from option. Fills internal {@link $_settings}.
	 *
	 * @return array Copy of current settings as array. If settings are empty, then empty array is returned.
	 */
	public function loadSettings(): array
	{
		if ($this->_settings === null) {
			$this->_settings = get_option(MWS_OPTION . '_gate_' . $this->getId(), []);
		}

		return $this->_settings;
	}

	/**
	 * Saves internal {@link $_settings}.
	 */
	public function saveSettings(array $settings): void
	{
		$this->_settings = $settings;
		update_option(MWS_OPTION . '_gate_' . $this->getId(), $this->_settings, false);
	}

	/**
	 * Get remote settings of the gateway.
	 *
	 * @param bool $reload Transient cache usage can be skipped by setting to true.
	 * @return array
	 * @throws MwsException On error
	 */
	public function getRemoteSettings(bool $reload = false): array
	{
		/** @var bool $isReloaded Protection against multiple reloads in one PHP run. */
		static $isReloaded = false; // @TODO not static
		$save = false;
		if ($reload && !$isReloaded) {
			mwshoplog("Remote settings for paygate [$this->id] forced reload.", MWLL_DEBUG, 'paygate');
			$this->remoteGateStgs = $this->sharedInstance()->loadRemoteSettings();
			$save = true;
			$isReloaded = true;
		} else {
			if ($this->remoteGateStgs === null) {
				$this->remoteGateStgs = get_transient(MWS_OPTION . '_gateremote_' . $this->getId());
				if (!$this->remoteGateStgs) {
					mwshoplog("Remote settings for paygate [{$this->getId()}] need to be loaded.", MWLL_DEBUG, 'paygate');
					$this->remoteGateStgs = $this->sharedInstance()->loadRemoteSettings();
					$save = true;
					$isReloaded = true;
				} else {
					mwshoplog("Remote settings for paygate [{$this->getId()}] loaded from cache.", MWLL_DEBUG, 'paygate');
				}
			}
		}
		if ($save) {
			set_transient(MWS_OPTION . '_gateremote_' . $this->getId(), $this->remoteGateStgs, 60 * 60 * 24); // one day persistence
			mwshoplog("Remote settings for paygate [{$this->getId()}] saved.", MWLL_DEBUG, 'paygate');
		}

		return $this->remoteGateStgs;
	}

	public function getSupportedCountries(): array
	{
		return $this->sharedInstance()->doGetSupportedCountries();
	}

	public function processPayments(): bool
	{
		return $this->sharedInstance()->processPayments();
	}

	/**
	 * Get array of supported payment methods by the gateway. Values are sorted according to definition order within
	 * {@link MwsPayType}.
	 *
	 * @return array Array of values of enumeration {@link MwsPayType}.
	 */
	public function getSupportedPaymentMethodTypes(): array
	{
		return array_intersect(MwsPayType::getAll(), $this->sharedInstance()->doGetSupportedPayTypes());
	}

	/**
	 * Returns enabled payment methods for the gateway. This is stored within gateway settings.
	 *
	 * @param bool $reload When true than result is downloaded from remote gateway.
	 * @return array
	 */
	public function getEnabledPayTypes(bool $reload = false): array
	{
		/** @var bool $isReloaded Protection against multiple reloads in one PHP run. */
		static $isReloaded = false;
		$saveNeeded = false;
		try {
			if (!$isReloaded && ($reload || MWS()->canEdit())) {
				mwshoplog("Enabled paytypes for paygate [$this->id] forced reload.", MWLL_DEBUG, 'paygate');
				$this->remotePayTypes = $this->sharedInstance()->loadRemotePayTypes();
				$saveNeeded = true;
				$isReloaded = true;
			} else {
				if ($this->remotePayTypes === null) {
					$transient = get_transient(MWS_OPTION . '_gateremote_paytypes_' . $this->id);
					if (empty($transient)) {
						mwshoplog("Enabled paytypes for paygate [$this->id] need to be loaded.", MWLL_DEBUG, 'paygate');
						$this->remotePayTypes = $this->sharedInstance()->loadRemotePayTypes();
						$saveNeeded = true;
						$isReloaded = true;
					} else {
						mwshoplog("Enabled paytypes for paygate [$this->id] loaded from cache.", MWLL_DEBUG, 'paygate');
						$this->remotePayTypes = $transient;
					}
				}
			}
			if ($saveNeeded) {
				set_transient(MWS_OPTION . '_gateremote_paytypes_' . $this->id, $this->remotePayTypes, 60 * 60 * 24); //one day persistence
				mwshoplog("Enabled paytypes for paygate [$this->id] saved.", MWLL_DEBUG, 'paygate');
			}
		} catch (Exception $e) {
			mwshoplog("Enabled paytypes for paygate [$this->id] could not be loaded.", MWLL_ERROR, 'paygate');
			$this->remotePayTypes = [];
		}

		return $this->remotePayTypes;
	}

	public function dropCache_EnabledPayTypes()
	{
		mwshoplog("Enabled paytypes for paygate [$this->id] uncached.", MWLL_DEBUG, 'paygate');
		delete_transient(MWS_OPTION . '_gateremote_paytypes_' . $this->id);
	}

	public function getEnabledPayments(bool $reload = false): array
	{
		/** @var bool $isReloaded Protection against multiple reloads in one PHP run. */
		static $isReloaded = false;
		$saveNeeded = false;
		try {
			if (!$isReloaded && ($reload || MWS()->canEdit())) {
				mwshoplog("Enabled payments for paygate [$this->id] forced reload.", MWLL_DEBUG, 'paygate');
				$this->remotePayments = $this->sharedInstance()->loadRemotePayments();
				$saveNeeded = true;
				$isReloaded = true;
			} else {
				if ($this->remotePayments === null) {
					$transient = get_transient(MWS_OPTION . '_gateremote_payments_' . $this->id);
					if (empty($transient)) {
						mwshoplog("Enabled payments for paygate [$this->id] need to be loaded.", MWLL_DEBUG, 'paygate');
						$this->remotePayments = $this->sharedInstance()->loadRemotePayments();
						$saveNeeded = true;
						$isReloaded = true;
					} else {
						mwshoplog("Enabled payments for paygate [$this->id] loaded from cache.", MWLL_DEBUG, 'paygate');
						$this->remotePayments = $transient;
					}
				}
			}
			if ($saveNeeded) {
				set_transient(MWS_OPTION . '_gateremote_payments_' . $this->id, $this->remotePayments, 60 * 60 * 24); //one day persistence
				mwshoplog("Enabled payments for paygate [$this->id] saved.", MWLL_DEBUG, 'paygate');
			}
		} catch (Exception $e) {
			mwshoplog("Enabled payments for paygate [$this->id] could not be loaded.", MWLL_ERROR, 'paygate');
			$this->remotePayments = [];
		}

		return $this->remotePayments;
	}

	public function dropCache_EnabledPayments()
	{
		mwshoplog("Enabled payments for paygate [$this->id] uncached.", MWLL_DEBUG, 'paygate');
		delete_transient(MWS_OPTION . '_gateremote_payments_' . $this->id);
	}

	public function getPurposes(bool $reload = false): array
	{
		static $isReloaded = false;
		$saveNeeded = false;
		try {
			if (!$isReloaded && ($reload || MWS()->canEdit())) {
				mwshoplog("Purposes for paygate [$this->id] forced reload.", MWLL_DEBUG, 'paygate');
				$this->purposes = $this->sharedInstance()->doGetPurposes();
				$saveNeeded = true;
				$isReloaded = true;
			} else {
				if ($this->purposes === null) {
					$transient = get_transient(MWS_OPTION . '_gateremote_purposes_' . $this->id);
					if (empty($transient)) {
						mwshoplog("Purposes for paygate [$this->id] need to be loaded.", MWLL_DEBUG, 'paygate');
						$this->purposes = $this->sharedInstance()->doGetPurposes();
						$saveNeeded = true;
						$isReloaded = true;
					} else {
						mwshoplog("Purposes for paygate [$this->id] loaded from cache.", MWLL_DEBUG, 'paygate');
						$this->purposes = $transient;
					}
				}
			}
			if ($saveNeeded) {
				set_transient(MWS_OPTION . '_gateremote_purposes_' . $this->id, $this->purposes, 60 * 60 * 24); //one day persistence
				mwshoplog("Purposes for paygate [$this->id] saved.", MWLL_DEBUG, 'paygate');
			}
		} catch (Exception $e) {
			mwshoplog("Purposes for paygate [$this->id] could not be loaded.", MWLL_ERROR, 'paygate');
			$this->purposes = [];
		}

		return $this->purposes;
	}

	public function dropCache_Purposes()
	{
		mwshoplog("Purposes for paygate [$this->id] uncached.", MWLL_DEBUG, 'paygate');
		delete_transient(MWS_OPTION . '_gateremote_purposes_' . $this->id);
	}

	/**
	 * @param bool $reload When true than result is downloaded from remote gateway. This is done always when in admin mode.
	 * @return bool
	 */
	public function isSimplifiedInvoiceAllowedForEshop(bool $reload = false): bool
	{
		return false;
	}

	public function dropCache_UseSimplifiedInvoice()
	{
		mwshoplog("Simplified invoice for paygate [$this->id] uncached.", MWLL_DEBUG, 'paygate');
		delete_transient(MWS_OPTION . '_gateremote_simplifiedinvoice_' . $this->id);
	}

	public function isSimplifiedInvoiceAllowedForQuickBuy(): bool
	{
		return $this->sharedInstance()->loadRemoteUseSimplifiedInvoiceForQuickBuy();
	}

	public function isSimplifiedInvoiceAllowedForForm(MwsForm $form): bool
	{
		return $this->sharedInstance()->loadRemoteUseSimplifiedInvoiceForForm($form);
	}

	/**
	 * Get enabled codes.
	 */
	public function getEnabledCodes(bool $reload = false): array
	{
		$codes = $this->sharedInstance()->doGetEnabledCodes($reload);
		$codes[] = MwsProductCode::EAN;

		return array_values(array_unique($codes));
	}
}


/**
 * Basic class of payment gateway. It defines interface of derived instances of gateways.
 */
abstract class MwsGatewayImpl
{

	/** @var MwsGatewayMeta Contains description of the gateway instance, like id, caption etc. */
	protected $meta;

	public function __construct(MwsGatewayMeta $meta)
	{
		$this->meta = $meta;
	}

	public function getId(): string
	{
		return $this->meta->getId();
	}

	/**
	 * Gateway dependent check if gateway connection is valid. Override this to implement custom checks.
	 */
	public function isConnected(): bool
	{
		return true;
	}

	public function doGetPurposes(): array
	{
		return [];
	}

	public function doGetSupportedCountries(): array
	{
		return MwsCountry::getAll();
	}

	/**
	 * Prepares remote gateway to work with MioShop. This can perform different tasks for different gates. For example
	 * the FAPI gate assures that MioShop form is present and that its ID is properly known by MioShop.
	 */
	final public function syncSettings(): bool
	{
		mwshoplog(__METHOD__ . ' [' . $this->getId() . ']', MWLL_DEBUG);
		try {
			return $this->doSyncSettings();
		} catch (Exception $e) {
			mwshoplog(
				"Unexpected error when synchronizing paygate [{$this->getId()}]. " . $e->getMessage() . ' [' . get_class($e) . ']',
				MWLL_ERROR,
				'paygate'
			);
		}

		return false;
	}

	/**
	 * Real implementation of synchronization of MioShop settings into remote gateway. Ancestors should override this method.
	 * Possible task are saving some general data into remote gateway, saving some special setting into MioShop.
	 *
	 * @return bool Returns true if all passed successfully, false on some error.
	 */
	protected function doSyncSettings(): bool
	{
		return true;
	}

	/**
	 * Get remote global settings of the gateway.
	 */
	public function loadRemoteSettings(): array
	{
		return [];
	}

	/**
	 * Calculates prices within the cart. Stores calculated prices back into the cart.
	 *
	 * @param MwsCart $cart Cart that should be recalculated.
	 * @param bool $includeShippingPrice Should calculation include shipping price?
	 * @param bool $ignoreSimplifiedInvoice If set to true, simplified invoice counting is ignored.
	 */
	abstract public function recountCart(MwsCart $cart, bool $includeShippingPrice, bool $ignoreSimplifiedInvoice, bool $includeRounding = false, bool $applyReverseCharge = false);

	/**
	 * @return MwsDocumentItem[]
	 * @throws MwsUserException
	 */
	protected function prepareOrderItems(MwsCart $cart, bool $includeShipping, bool $includeRounding, bool $applyReverseCharge, bool $applyOSS): array
	{
		$currency = $cart->getCurrency();
		$cartCurrentTotalPrice = new MwsPrice(0.0, 0.0, $currency);
		$discountCode = $cart->getDiscountCode();

		$items = [];
		foreach ($cart->getItems()->getAll() as $cartItem) {
			$product = $cartItem->getProduct();
			$price = $cartItem->getStoredProductPrice() ?? $product->getPrice();

			//$nativePrice = $product->getPrice();

			$productName = $product->getName();

			// first apply discount code as percent sale
			if ($discountCode && // used discount code
				$discountCode->getType() === MwsDiscountCodeType::Percent && // percent type is applied as sale to items
				!$cartItem->getProduct()->isDiscountDisabled() // discount code use is disabled on product
			) {
				// percent discount code apply as percent sale on product
				$price = $price->multiply((100 - $discountCode->getValue()) / 100);
				// add sale info to product name
				$productName .= sprintf(__(' - sleva %d %%', 'mwshop'), $discountCode->getValue());
			}

			/** @var MwsPrice[] $prices */
			$prices = $this->getAllPrices($price, $currency);

			$items[] = new OrderItem(
				$productName,
				$product->getType(),
				$prices,
				$cartItem->getCount(),
				$product->getCodes(),
				$product->getId(),
				false,
				$cartItem->isMiniupsell(),
				$product->getWeight()
			);

			$cartCurrentTotalPrice = $cartCurrentTotalPrice->add($prices[$currency]->multiply($cartItem->getCount()));
		}

		// shipping
		if ($includeShipping && ($shipping = $cart->getShipping()) && !$shipping->isElectronic()) { // @TODO throw error if not shipping?
			$price = $shipping->getTotalPrice($cart->getPaymentMethod(), $cartCurrentTotalPrice, $cart->getItems()->getTotalWeight())->asCurrency($currency);
			$prices = $this->getAllPrices($price, $currency);
			// also include if is zero price
			$items[] = new OrderItem(
				$shipping->getName(),
				MwsOrderItemType::Shipping,
				$prices
			);
			$cartCurrentTotalPrice = $cartCurrentTotalPrice->add($prices[$currency]);
		}

		// Edit VAT (reverse-charge or OSS)
		try {
			$shouldApplyReverseCharge = $applyReverseCharge && $cart->shouldApplyReverseCharge();
			// Reverse-charge is applied later
		} catch (ReverseChargeApplicationException $e) {
			throw new MwsUserException(__('Kvůli přenesené daňové povinnosti nelze vystavit fakturu s touto kombinací
			produktů. Pro dokončení nákupu prosím nakupte produkty zvlášť ve více objednávkách.', 'mwshop'), 0, $e);
		}

		if (!$shouldApplyReverseCharge && $applyOSS && $cart->shouldApplyOSS()) {
			$buyerContact = $cart->getInvoiceContact();
			if ($buyerContact !== null) {
				$buyerAddress = $buyerContact->getAddress();
				if ($buyerAddress !== null) {
					$buyerCountry = $buyerAddress->getCountry();
					$mwsVATs = MWS()->getVATs();

					foreach ($items as $item) {
						$product = $item->getProduct();

						if ($product !== null && MwsProductType::isApplicableForOSS($product->getType())) {
							$vatRateType = MwsVatRateType::getByProductType($product->getType());
							$vat = $mwsVATs->getVatRate($buyerCountry, $vatRateType);
						} elseif ($item->getType() === MwsOrderItemType::Shipping) {
							$vat = $mwsVATs->getVatRate($buyerCountry);
						} else {
							$vat = null;
						}

						if ($vat !== null) {
							$item->changeVat($vat);
							$item->setOssApplied();
						}
					}
				}
			}
		}

		// discount code as negative item
		if ($discountCode && $discountCode->getType() === MwsDiscountCodeType::Fixed) {
			$defaultCurrency = MWS()->getDefaultCurrency('key');
			$vatPercentages = $itemsTmp = [];

			// Get all VAT percentages
			foreach ($items as $item) {
				$vatPercentages[] = $item->getPrice($defaultCurrency)->getVatPercentage();
			}
			$vatPercentages = array_unique($vatPercentages);
			rsort($vatPercentages); // Sort by highest VAT percentage

			$remainingDiscountAmount = $discountCode->getValue();

			foreach ($vatPercentages as $vatPercentage) {
				if ($remainingDiscountAmount <= 0.0) {
					break;
				}

				$itemAmountByVat = 0.0;
				foreach ($items as $item) {
					if ($item->getPrice($defaultCurrency)->getVatPercentage() === $vatPercentage) {
						$itemAmountByVat += $item->getTotalPrice($defaultCurrency)->getPriceVatIncluded();
					}
				}

				$discountAmount = max(min($itemAmountByVat, $remainingDiscountAmount), 0);
				$remainingDiscountAmount -= $discountAmount;

				if ($discountAmount <= 0) {
					continue;
				}

				// discount code is stored in default currency
				$price = new MwsPrice(-$discountAmount, $vatPercentage);
				$prices = $this->getAllPrices($price, $currency);
				$itemsTmp[] = new OrderItem(
					__('Sleva', 'mwshop'),
					MwsOrderItemType::Discount,
					$prices
				);
				$cartCurrentTotalPrice = $cartCurrentTotalPrice->add($prices[$currency]);
			}

			foreach ($itemsTmp as $item) {
				$items[] = $item;
			}
		}

		if ($shouldApplyReverseCharge) {
			foreach ($items as $item) {
				$item->removeVat();
			}
		}

		// rounding item
		if ($includeRounding) {
			$mwsCurrency = MwsCurrency::getOneByCurrency($currency);
			if ($mwsCurrency !== null && $mwsCurrency->roundingOrders()) {
				$roundingVat = 0;
				$highestPrice = 0;
				$vatPriceTotal = [];
				$cartTotalPrice = new MwsPrice(0.0, 0.0, $currency); // We must recalculate total price after applying OSS or reverse-charge

				foreach ($items as $item) {
					$itemPrice = $item->getTotalPrice($currency);
					$itemVat = $item->getPrice(MWS()->getDefaultCurrency('key'))->getVatPercentage();
					$cartTotalPrice = $cartTotalPrice->add($itemPrice);
					$vatPriceTotal[$itemVat] = ($vatPriceTotal[$itemVat] ?? 0) + $itemPrice->getPriceVatExcluded();
					if ($highestPrice < $vatPriceTotal[$itemVat]) {
						$roundingVat = $itemVat;
						$highestPrice = $vatPriceTotal[$itemVat];
					}
				}
				$roundedCartTotalPrice = $cartTotalPrice->roundBy($mwsCurrency->getRoundingFunction(), $mwsCurrency->getRoundingPrecision());
				$price = new MwsPrice($roundedCartTotalPrice->sub($cartTotalPrice)->getPriceVatIncluded(), $roundingVat, $currency);
				$prices = $this->getAllPrices($price, $currency);
				if ($price->abs()->getPriceVatIncluded() > 0) {
					$items[] = new OrderItem(
						__('Zaokrouhlení', 'mwshop'),
						MwsOrderItemType::Rounding,
						$prices
					);
				}
			}
		}

		return $items;
	}

	function getAllPrices($price, $mainCurrency)
	{
		$price = $price->asCurrency($mainCurrency);
		$allCurrencies = MwsCurrencyEnum::getAll();
		$prices = [];
		foreach ($allCurrencies as $cur) {
			$prices[$cur] = $price->asCurrency($cur);
		}

		return $prices;
	}

	/**
	 * Order content of the cart in a specific way for a gateway.
	 *
	 * @param MwsCart $cart
	 * @return array Returns array with several items:
	 *               "success" as bool - was operation successful?
	 *               "nextUrl" as string - URL where to redirect (in case payment gateway is involved)
	 *               "orderId" as int - on success has post ID of new Order object
	 *               "orderNum" as string - on success has number of the invoice of corresponding paygate, if supported
	 */
	abstract protected function doMakeOrder(MwsCart $cart): array;

	protected function createOrderBase(MwsCart $cart, string $orderNum): Order
	{
		$order = new Order(null, $orderNum);
		$gateIdentifier = $this->getId();
		$gate = ShopGateRepository::getOneByIdentifier($gateIdentifier);
		if ($gate === null) {
			throw new MwsException();
		}

		$order->setGate($gate);

		$shipping = $cart->getShipping();
		$paymentMethod = $cart->getPaymentMethod();
		if ($shipping) {
			$shippingInfo = [
				'shippingId' => $shipping->getId(),
				'name' => $shipping->getName(),
				'type' => $shipping->getType(),
				'price' => $cart->getShippingPrice()->toArray(),
				'externalId' => $cart->getShippingInfo()['id'] ?? '',
				'pickupAddress' => $cart->getShippingInfo()['address'] ?? '',
			];

			if ($shipping->getType() == MwsShippingType::PacketaCarriers) {
				$shippingInfo['externalId'] = $shipping->getCarrier();
			}
			if ($paymentMethod && $paymentMethod->isCod()) {
				$shippingInfo['cod_price'] = $shipping->getCodPrice()->asCurrency($cart->getCurrency())->toArray();
			}

			$order->setShipping($shippingInfo);
		}

		if ($paymentMethod !== null) {
			$order->setPayment([
				'id' => $paymentMethod->getId(),
				'name' => $paymentMethod->getName(),
				'type' => $paymentMethod->getType(),
				'gateway_id' => $paymentMethod->getPaymentGatewayId(),
			]);
		}

		$discountCode = $cart->getDiscountCode();
		$order->setDiscountCode($discountCode !== null ? $discountCode->toArray() : null);

		// customer id
		$currentUser = mwUser::getCurrent();
		if ($currentUser->getId()) {
			$order->setCustomerId($currentUser->getId());
		} else {
			$email = $cart->getInvoiceContact()->getEmail();
			$user = mwUser::getOneBy($email, 'email');
			if (!$user) {
				$user = mwUser::getOneBy($email, 'login');
			}
			if ($user) {
				$order->setCustomerId($user->getId());
			}
		}

		$order->setCustomerNote($cart->getNote());
		$order->setHeurekaDisagree($cart->getHeurekaDisagree());
		$order->setSource($cart->getSource());

		$orderItems = $this->prepareOrderItems($cart, true, true, true, true);
		foreach ($orderItems as $item) {
			$order->getItems()->add($item);
		}

		$currency = $cart->getCurrency();

		$order->setCurrency($currency);
		$order->setExchangeRate(MWS()->getCurrencyConversionRate(MWS()->getDefaultCurrency('key'), $currency));
		$order->setNativeCurrency(MWS()->getDefaultCurrency('key'));
		$order->setShopVersion(MioShop::version);
		$order->setReverseCharge($cart->shouldApplyReverseCharge());
		$order->setIsTest($cart->isTest());
		$order->setSimplifiedInvoice($cart->useSimplifiedInvoice());
		$order->setVatAccounting(MWS()->getVATs()->getAccountingType());

		$showVat = MWS()->getVATs()->isUsingVatAccounting() || $cart->shouldApplyReverseCharge() || $cart->shouldApplyOSS();

		$order->setShowVat($showVat);

		return $order;
	}

	/**
	 * Order content of the cart.
	 *
	 * @param MwsCart $cart
	 * @return array Returns array with several items:
	 *               "success" as bool - was operation successful?
	 *               "nextUrl" as string - URL where to redirect (in case payment gateway is involved)
	 *               "message" as string, optional - error text of the failure, localized, to be shown in UI
	 *               "orderId" as int - on success has post ID of new Order object
	 *               "orderNum" as string - on success has number of the invoice of corresponding paygate, if supported
	 */
	public function makeOrder(MwsCart $cart): array
	{
		mwshoplog(__METHOD__, MWLL_DEBUG);
		$res = [
			'success' => false,
			'nextUrl' => '',
		];
		// Check availability, compose errors for availability, decrement counts for each product
		$stockDecremented = [];
		$errorCount = 0;
		mwshoplog(__('Kontrola a snížení stavu skladových zásob před vytvořením objednávky.', 'mwshop'), MWLL_INFO, 'order');
		foreach ($cart->getItems()->getAll() as $cartItem) {
			if ($cartItem->checkAvailability(true)) {
				// Remember items whose stock has been successfully decremented
				if ($cartItem->getProduct()->isStockEnabled()) { // Product existence is assured within checkAvailability
					$stockDecremented[$cartItem->getProduct()->getId()] = $cartItem->getCount();
				}
			} else {
				$errorCount++;
			}
		}

		if ($errorCount === 0) {
			$discountCode = $cart->getDiscountCode();
			// discount code is use but is not valid anymore
			if ($discountCode && $discountCode->isValid($cart) !== 1) {
				$res['message'] = __('Objednávku se nepodařilo vytvořit. Zadaný slevový kód je již neplatný.', 'mwshop');
			} else {
				// try make order
				$res = $this->doMakeOrder($cart);
				// order successfully created
				if ($res['success']) {
					unset($stockDecremented);
					if ($discountCode) {
						$discountCode->setUsedCount($discountCode->getUsedCount() + 1);
					}

					// @TODO move this ... use wp hooks? events?
					$order = OrderRepository::getOrderByOrderNum($res['orderNum']);
					$heureka = new MwHeureka();
					$heureka->sendHeurekaOvereno($order);

					MWS()->getAutomationProcessor()->process($order, MwsAutomationEvent::OnOrder);

					if ($contact = MWS()->getNotificationContact()) {
						$orderVariables = MwsOrderVariables::fromMwOrder($order);
						$variables = $orderVariables->toArrayFormatted();
						$contact->sendMail(
							MwVariables::replaceVariables('Nová objednávka %%CISLO_OBJEDNAVKY%% z %%NAZEV_WEBU%%', $variables),
							MwVariables::replaceVariables(
							__('Vytvořena nová objednávka', 'mwshop') . ' <strong>%%CISLO_OBJEDNAVKY%%</strong><br>' .
								__('Objednávku lze spravovat zde:', 'mwshop') . ' ' . $order->getEditUrl() . '<br><br>' .
								'%%INFO_OBJEDNAVKY%%',
								$variables
							)
						);
					}
				}
			}
		} else {
			$cart->setAvailabilityErrorsCount($errorCount);
			$res['message'] = __('Omlouváme se, objednávku se nepodařilo vytvořit. Některé položky nejsou dostupné.', 'mwshop');
		}

		if (isset($stockDecremented)) {
			// Refund decremented stock items
			mwshoplog(__('Objednávku se nepodařilo vytvořit. Vrácím zásoby rezervované objednávkou na sklad.', 'mwshop'), MWLL_INFO, 'order');
			foreach ($cart->getItems()->getAll() as $cartItem) {
				$productId = $cartItem->getProduct()->getId();
				if (isset($stockDecremented[$productId])) {
					$cartItem->getProduct()->updateStockCount($stockDecremented[$productId], MwsStockUpdate::Inc);
				}
			}
		}

		return $res;
	}

	/**
	 * Determine if gateway process payment on own side
	 */
	abstract public function processPayments(): bool;

	/**
	 * Get array of payment methods supported by the gateway.
	 */
	public function doGetSupportedPayTypes(): array
	{
		return [];
	}

	/**
	 * Method to handle callback from the gateway. Ancestor should mark corresponding order as paid.
	 * Incoming data are present in $_REQUEST variable.
	 *
	 * @return Order Method returns order object that has been paid. If payment can not be proved
	 *                  then null is returned.
	 * @throws MwsException On failure method should throw an exception with message describing case of the error.
	 */
	abstract public function orderPaid(): ?Order;

	/**
	 * Method to handle callback from the gateway. Ancestor should mark corresponding order as cancelled.
	 * Incoming data are present in $_REQUEST variable.
	 *
	 * @return Order Method returns order object that has been cancelled. If operation can not be finished
	 *                  then null is returned.
	 * @throws MwsException On failure method should throw an exception with message describing case of the error.
	 */
	abstract public function orderCancelled(): ?Order;

	/**
	 * Get corresponding order object from $_REQUEST array. Translate it into {@link Order}.
	 */
	public function getOrderFromThankYou(): ?Order
	{
		return null;
	}

	/**
	 * Create caching object for order for the gateway.
	 *
	 * @param Order $order Order for which the caching object should be created.
	 * @param null $preloadedData Optionally preloaded data. Can be used when creating multiple caching object at once.
	 * @return OrderGate Caching object for specific gateway.
	 */
	abstract public function loadOrderGate(IOrder $order, ?array $preloadedData = null): ?OrderGate;

	/**
	 * Load gate live data for several orders at once. Default implementation calls {@link loadOrderGate()} separately for
	 * each order.
	 */
	public function preloadOrdersGateLive(Order ...$orders): void
	{
		foreach ($orders as $order) {
			$order->getGateLive();
		}
	}

	/**
	 * Get remote paytypes of the gateway.
	 */
	abstract public function loadRemotePayTypes(): array;

	abstract public function loadRemotePayments(): array;

	/** Get remote allow simplified invoice. */
	abstract public function loadRemoteUseSimplifiedInvoiceForEshop(): bool;

	/** Get remote allow simplified invoice. */
	abstract public function loadRemoteUseSimplifiedInvoiceForQuickBuy(): bool;

	/** Get remote allow simplified invoice. */
	abstract public function loadRemoteUseSimplifiedInvoiceForForm(MwsForm $form): bool;

	/**
	 * Get array of enabled codes.
	 */
	abstract public function doGetEnabledCodes(bool $reload = false): array;

}

/*
Register supported gateways. This is done only in case this file was really loaded. Otherwise whole gateway part
is not loaded.
*/
add_filter('mws_gateway_register', function (MwsGateways $gws) {
	$gws->registerGw(
		new MwsGatewayMeta(
			'mioweb',
			__('Mioweb', 'mwshop'),
			'MwsGatewayImpl_Mioweb',
			__DIR__ . '/gateway_mioweb.php'
		)
	);
	$gws->registerGw(
		new MwsGatewayMeta(
			'fapi',
			__('FAPI', 'mwshop'),
			'MwsGatewayImpl_Fapi',
			__DIR__ . '/gateway_fapi.php'
		)
	);
});
