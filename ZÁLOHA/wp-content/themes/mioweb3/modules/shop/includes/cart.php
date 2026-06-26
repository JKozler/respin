<?php

/**
 * MioShop CART implementation.
 *
 * Cart is saved in the PHP session storage. Parallel updates of the cart are protected by PHP session handling mechanism
 * that protects session against multiple access (only one thread is allowed to access concurrently).
 *
 * User: kuba
 * Date: 26.02.16
 * Time: 12:12
 */

use Mioweb\Shop\MwsSessionSection;
use Nette\Http\SessionSection;

/**
 * MioShop's CART class.
 */
class MwsCart
{

	/** @var bool Is cart already loaded from session? */
	protected $_loaded = false;

	/** @var SessionSection Session storage object. */
	protected $_session;

	/** @var MwsCartItems Items of the cart. */
	protected $_items;

	/** @var bool When counted prices of the cart need a refresh then this is set to true. */
	protected $_recountNeeded = true;
	// Property storage

	/** @var string This is set to nonempty string when cart total price is not recounted due to an error during recounting. */
	protected $_recountError;

	/** @var string Exception text message. Not user friendly. */
	protected $_recountAdminError;

	/** @var MwsPrice */
	protected $_storedTotalPrice;

	/** @var MwsPrice Calculated price of shipping. */
	protected $_shippingPrice;

	/** @var MwsPrice Rounding. */
	protected $_rounding;

	/** @var array Invoice data from order step. */
	protected $_contact = [];

	/** @var MwsShipping|null */
	protected $_shipping;

	protected $_shippingInfo = [];

	protected $_shippingPriceIncluded; // internal

	/** @var MwsDiscountCode|null Discount code data from order step. */
	protected $_discountCode;

	/** @var MwsPaymentMethod|null */
	protected $_paymentMethod;

	/** @var bool */
	protected $_heurekaDisagree = false;

	/** @var array Status of fulfillment of each order step, indexed by MwsPayType. */
	protected $_stepsFulfilled = [];

	protected $_availabilityErrorsCount = 0;

	/** @var string|null URL, default will be used if NULL */
	protected $_thxPage = null;

	/** @var bool If selling form is in "test" mode */
	protected $_isTest = false;

	/** @var array|null */
	protected $_source = null;

	/** @var ?MwsForm $form NULL means e-shop */
	protected $_form = null;

	/** @var array */
	protected $_purposes = [];

	/**
	 * Returns true if the cart is empty.
	 */
	public function isEmpty(): bool
	{
		return $this->getItems()->isEmpty();
	}

	/**
	 * Load content of the cart from session into intern cache.
	 */
	protected function loadFromSession(bool $reload = false): void
	{
		if ($this->_loaded && !$reload) {
			return;
		}

		$items = $this->getSession()->items ?? [];
		$this->_items->clear();
		$this->_items->load($items);
		$this->_recountNeeded = (bool) $this->getSession()->recountNeeded;
		$this->_recountError = $this->getSession()->recountError;
		$this->_stepsFulfilled = $this->getSession()->stepsFulfilled ?: [];
		$this->_contact = $this->getSession()->contact ?: [];

		$shipping = $this->getSession()->shipping;
		$this->_shipping = $shipping ? MwsShipping::getOneById($shipping) : null;
		$this->_shippingInfo = $this->getSession()->shippingInfo ?: [];
		$this->_shippingPriceIncluded = (bool) $this->getSession()->shippingPriceIncluded;
		$shippingPrice = $this->getSession()->shippingPrice;
		$this->_shippingPrice = $shippingPrice ? MwsPrice::createByArray($shippingPrice) : null;

		$paymentMethodId = $this->getSession()->paymentMethod;
		$paymentMethod = $paymentMethodId ? MwsPaymentMethod::getOneById($paymentMethodId) : null;
		if ($paymentMethod !== null) {
			$paymentMethodBank = $this->getSession()->paymentMethodBank;
			if ($paymentMethodBank) {
				$paymentMethod->setBank($paymentMethodBank);
			}
		}
		$this->_paymentMethod = $paymentMethod;

		if ($this->_paymentMethod !== null) {
			$paymentMethodBank = $this->getSession()->paymentMethodBank;

			if ($paymentMethodBank !== null) {
				$this->_paymentMethod->setBank($paymentMethodBank);
			}
		}

		$discountCode = $this->getSession()->discountCode;
		$this->_discountCode = $discountCode ? MwsDiscountCode::getOneById($discountCode) : null;

		$storedTotalPrice = $this->getSession()->storedTotalPrice;
		$this->_storedTotalPrice = $storedTotalPrice ? MwsPrice::createByArray($storedTotalPrice) : null;
		$this->_availabilityErrorsCount = (int) ($this->getSession()->availabilityErrorsCount ?? 0);
		$this->_heurekaDisagree = (bool) $this->getSession()->heurekaDisagree;
		$this->_purposes = $this->getSession()->purposes ?? [];
		$this->_thxPage = $this->getSession()->thxPage;
		$this->_source = $this->getSession()->source;
		$this->_loaded = true;
	}

	protected function loadFromArray(array $data, bool $reload = false): void
	{
		if ($this->_loaded && !$reload) {
			return;
		}

		$this->_items->clear();
		$this->_items->load($data['items']);
		$this->_recountNeeded = (bool) $data['recountNeeded'];
		$this->_recountError = $data['recountError'];
		$this->_stepsFulfilled = $data['stepsFulfilled'];
		$this->_contact = $data['contact'];

		$shipping = $data['shipping'];
		$this->_shipping = $shipping !== null ? MwsShipping::getOneById($shipping) : null;
		$this->_shippingInfo = $data['shippingInfo'];
		$this->_shippingPriceIncluded = (bool) $data['shippingPriceIncluded'];
		$shippingPrice = $data['shippingPrice'];
		$this->_shippingPrice = $shippingPrice ? MwsPrice::createByArray($shippingPrice) : null;

		$paymentMethodId = $data['paymentMethod'];
		$paymentMethod = $paymentMethodId ? MwsPaymentMethod::getOneById($paymentMethodId) : null;
		if ($paymentMethod !== null) {
			$paymentMethod->setBank($data['paymentMethodBank'] ?? null);
		}
		$this->_paymentMethod = $paymentMethod;

		$discountCode = $data['discountCode'];
		$this->_discountCode = $discountCode ? MwsDiscountCode::getOneById($discountCode) : null;

		$storedTotalPrice = $data['storedTotalPrice'];
		$this->_storedTotalPrice = $storedTotalPrice ? MwsPrice::createByArray($storedTotalPrice) : null;
		$this->_availabilityErrorsCount = (int) $data['availabilityErrorsCount'];
		$this->_heurekaDisagree = (bool) $data['heurekaDisagree'];
		$this->_purposes = $data['purposes'];
		$this->_thxPage = $data['thxPage'];
		$this->_source = $data['source'];
		$this->_loaded = true;
	}

	protected function getSession(): MwsSessionSection
	{
		if ($this->_session === null) {
			$this->_session = MwsSession::getInstance()->getSection('eshop');
		}

		return $this->_session;
	}

	public function __construct(?array $data = null)
	{
		$this->_items = new MwsCartItems($this);
		if ($data !== null) {
			$this->loadFromArray($data);
		} else {
			$this->loadFromSession();
		}

		add_action('shutdown', [$this, 'save'], 10);
	}

	public function toArray(): array
	{
		return [
			'items' => $this->getItems()->toReducedArray(),
			'recountNeeded' => $this->_recountNeeded,
			'recountError' => $this->_recountError,
			'stepsFulfilled' => $this->_stepsFulfilled,
			'contact' => $this->_contact,
			'discountCode' => $this->_discountCode ? $this->_discountCode->getId() : null,
			'shipping' => $this->_shipping ? $this->_shipping->getId() : null,
			'shippingInfo' => $this->_shippingInfo,
			'paymentMethod' => $this->_paymentMethod ? $this->_paymentMethod->getId() : null,
			'paymentMethodBank' => $this->_paymentMethod ? $this->_paymentMethod->getBank() : null,
			'shippingPriceIncluded' => $this->_shippingPriceIncluded,
			'shippingPrice' => $this->_shippingPrice ? $this->_shippingPrice->toArray() : null,
			'storedTotalPrice' => $this->_storedTotalPrice ? $this->_storedTotalPrice->toArray() : null,
			'availabilityErrorsCount' => $this->_availabilityErrorsCount,
			'heurekaDisagree' => $this->_heurekaDisagree,
			'purposes' => $this->_purposes,
			'thxPage' => $this->_thxPage,
			'source' => $this->_source,
		];
	}

	/**
	 * Save content of the cart to session storage.
	 * Intern cache of the cart content is used.
	 */
	public function save(): void
	{
		if ($this->_loaded) {
			foreach ($this->toArray() as $key => $value) {
				$this->getSession()->$key = $value;
			}
		}
	}

	public function removeDiscountCode(): void
	{
		$this->_discountCode = null;
	}

	/** @return MwsPaymentMethod[] */
	public function getAllowedPaymentMethods(): array
	{
		$currency = $this->getCurrency();
		$country = $this->getInvoiceCountry();
		$isElectronicContained = $this->isElectronicContained();

		return MWS()->filterAllowedPaymentMethods(MWS()->getPaymentMethods(), $currency, $country, $isElectronicContained);
	}

	/**
	 * Calculates prices for the cart content. Default gateway is used for calculation if not specified differently.
	 * As a result prices of cart items a filled and total prices too.
	 * When recounting is successful, flag $recountNeeded is reset.
	 *
	 * @param bool $includeShippingPrice Should calculation include shipping price?
	 * @param bool $ignoreSimplified If set to true, then counting will not use optional simplified invoice calculation.
	 * @param string $gwId ID of the gateway that should be used. If 'default' is used then global gateway defined
	 *                                   for counting is used.
	 * @param bool $force Forces recounting even if flag $recountNeeded is not set.
	 * @throw Exception If error occurs during recounting.
	 */
	public function recount(bool $includeShippingPrice, bool $ignoreSimplified, bool $includeRounding = false, bool $applyReverseCharge = false): void
	{
		$gw = MWS()->gateways()->getDefault();
		try {
			// @TODO is reset needed?
			foreach ($this->getItems()->getAll() as $cartItem) {
				$cartItem->setStoredPrice(null);
				$cartItem->setStoredShopPrice(null);
				$cartItem->setStoredProductPrice(null);
				$cartItem->setStoredTotalPrice(null);
				$cartItem->setAvailabilityStatus(MwsProductAvailabilityStatus::Unavailable_Disabled);
				$cartItem->setAvailabilityError('');
			}
			$this->setStoredTotalPrice(null);
			$this->setShippingPrice(null);
			$this->setRounding(null);

			// Check an Update availability of items
			$availabilityErrors = $this->checkAvailability();

			// Recount
			$gw->sharedInstance()->recountCart($this, $includeShippingPrice, $ignoreSimplified, $includeRounding, $applyReverseCharge);

			// not included shipping price in total price
			if (!$this->getShippingPrice() && ($shipping = $this->getShipping())) {
				$this->setShippingPrice($shipping->getTotalPrice($this->getPaymentMethod(), $this->getStoredTotalPrice(), $this->getItems()->getTotalWeight())->asCurrency($this->getCurrency()));
			}

			// Save the results.
			$this->_shippingPriceIncluded = $includeShippingPrice;
			$this->_recountNeeded = (bool) $availabilityErrors;
			$this->_recountError = ($availabilityErrors
				? sprintf(
					_nx(
						'Omlouváme se, %d položku nelze zajistit v požadovaném množství.',
						'Omlouváme se, %d položek nelze zajistit v požadovaném množství.',
						$availabilityErrors,
						'Cart error message when some of cart items are not available in specified amount.',
						'mwshop'
					),
					$availabilityErrors
				)
				: '');
		} catch (MwsUserException $e) {
			$this->_recountNeeded = true;
			$this->_recountError = $e->getMessage();
			$this->_recountAdminError = '';
			// @TODO rethrow?
		} catch (Exception $e) {
			$this->_recountNeeded = true;
			$this->_recountError = __('Omlouváme se, při výpočtu ceny došlo k chybě. Opakujte prosím pokus později.', 'mwshop');
			$this->_recountAdminError = $e->getMessage();
			if (!$this->_recountAdminError) {
				$this->_recountAdminError = 'unexpected error';
			}
			$this->_recountAdminError .= ' [' . get_class($e) . ']';
			// @TODO rethrow?
		}
	}

	/**
	 * Check availability of items in the cart. Method fills for every item its {@link MwsCartItem::availabilityStatus} and
	 * upon an error sets {@link MwsCartItem::availabilityError}. Method updates {@link availabilitErrorsCount} to the
	 * result value.
	 *
	 * @return int Number of items that has availability errors and can not be bought in requested order.
	 */
	private function checkAvailability(): int
	{
		$this->_availabilityErrorsCount = 0;
		foreach ($this->getItems()->getAll() as $cartItem) {
			if (!$cartItem->checkAvailability()) {
				$this->_availabilityErrorsCount++;
			}
		}

		return $this->_availabilityErrorsCount;
	}

	/**
	 * Add a new item into cart. If item is already present, then increment its count.
	 */
	public function addItem(MwsProduct $product, int $count): int
	{
		return $this->getItems()->add(new MwsCartItem($product, ['count' => $count]));
	}

	public function getItems(): MwsCartItems
	{
		return $this->_items;
	}

	public function getContact(): array
	{
		return $this->_contact;
	}

	public function setContact(array $contact): void
	{
		$this->_contact = $contact;
	}

	public function getNote(): ?string
	{
		return $this->_contact['note'] ?? null ?: null;
	}

	public function wantInvoice(): bool
	{
		return (bool) ($this->_contact['want_invoice'] ?? false);
	}

	public function getInvoiceContact(): MwsContact
	{
		$contact = $this->getContact();

		return new MwsContact(
			$contact['email'] ?? '',
			$contact['address']['phone'] ?? null,
			new MwsPerson(
				$contact['address']['firstname'] ?? '',
				$contact['address']['surname'] ?? ''
			),
			$contact['is_company'] ?? false ? new MwsCompany(
				$contact['company_info']['company_name'] ?? '',
				$contact['company_info']['company_id'] ?? null,
				$contact['company_info']['company_vat_id'] ?? null,
				$contact['company_info']['company_sk_vat_id'] ?? null,
			) : null,
			new MwsAddress(
				$this->getInvoiceCountry(),
				$contact['address']['city'] ?? '',
				$contact['address']['zip'] ?? '',
				$contact['address']['street'] ?? ''
			)
		);
	}

	public function getShippingContact(): ?MwsContact
	{
		$contact = $this->getContact();
		if (!($contact['has_shipping_addr'] ?? false)) {
			return null;
		}

		return new MwsContact(
			'', // @TODO email?
			$contact['shipping_address']['phone'] ?? null,
			new MwsPerson(
				$contact['shipping_address']['firstname'] ?? '',
				$contact['shipping_address']['surname'] ?? ''
			),
			null,
			new MwsAddress(
				$this->getShippingCountry(),
				$contact['shipping_address']['city'] ?? '',
				$contact['shipping_address']['zip'] ?? '',
				$contact['shipping_address']['street'] ?? ''
			)
		);
	}

	public function getShipping(): ?MwsShipping
	{
		return $this->_shipping;
	}

	public function setShipping(?MwsShipping $shipping): void
	{
		$this->_shipping = $shipping;
	}

	public function getShippingInfo(): array
	{
		return $this->_shippingInfo;
	}

	public function setShippingInfo(array $shippingInfo): void
	{
		$this->_shippingInfo = $shippingInfo;
	}

	public function getShippingPrice(): ?MwsPrice
	{
		return $this->_shippingPrice;
	}

	public function setShippingPrice(?MwsPrice $shippingPrice): void
	{
		$this->_shippingPrice = $shippingPrice;
	}

	/**
	 * Stored total price include shipping
	 */
	public function isShippingPriceIncluded(): bool
	{
		return $this->_shippingPriceIncluded;
	}

	public function getPaymentMethod(): ?MwsPaymentMethod
	{
		return $this->_paymentMethod;
	}

	public function setPaymentMethod(?MwsPaymentMethod $paymentMethod): void
	{
		$this->_paymentMethod = $paymentMethod;
	}

	public function getDiscountCode(): ?MwsDiscountCode
	{
		return $this->_discountCode;
	}

	public function setDiscountCode(?MwsDiscountCode $discountCode): void
	{
		$this->_discountCode = $discountCode;
	}

	public function getStoredTotalPrice(): ?MwsPrice
	{
		return $this->_storedTotalPrice;
	}

	public function setStoredTotalPrice(?MwsPrice $storedTotalPrice): void
	{
		$this->_storedTotalPrice = $storedTotalPrice;
	}

	public function setRounding(?MwsPrice $rounding): void
	{
		$this->_rounding = $rounding;
	}

	public function getRounding(): ?MwsPrice
	{
		return $this->_rounding;
	}

	public function getRecountError(): ?string
	{
		return $this->_recountError;
	}

	public function getRecountAdminError(): ?string
	{
		return $this->_recountAdminError;
	}

	public function getAvailabilityErrorsCount(): int
	{
		return $this->_availabilityErrorsCount;
	}

	public function setAvailabilityErrorsCount(int $availabilityErrorsCount): void
	{
		$this->_availabilityErrorsCount = $availabilityErrorsCount;
	}

	public function isRecounted(): bool
	{
		return !$this->_recountNeeded && !$this->_recountError;
	}

	public function getHeurekaDisagree(): bool
	{
		return $this->_heurekaDisagree;
	}

	public function setHeurekaDisagree(bool $heurekaDisagree): void
	{
		$this->_heurekaDisagree = $heurekaDisagree;
	}

	public function getPurposes(): array
	{
		return $this->_purposes;
	}

	public function setPurposes(array $purposes): void
	{
		$this->_purposes = $purposes;
	}

	/**
	 * Return true if the step was fulfilled successfully.
	 */
	private function isFulfilledStep(string $step): bool
	{
		return (bool) ($this->_stepsFulfilled[$step] ?? false);
	}

	/**
	 * Return true if all previous steps are fulfilled. Current step is not taken into account.
	 */
	public function areFulfilledPriorSteps(string $step): bool
	{
		foreach (MwsOrderStep::getAll() as $current) {
			if ($current == $step) {
				break;
			}
			if (!$this->isFulfilledStep($current)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Update fulfilled step status.
	 */
	public function setFulfilledStep(string $step, bool $fulfilled): void
	{
		$this->_stepsFulfilled[$step] = $fulfilled;
	}

	/**
	 * Returns associative array indexed by steps with bool values of each step fulfillment.
	 */
	public function getStepsFulfillment(): array
	{
		$result = [];
		foreach (MwsOrderStep::getAll() as $step) {
			$result[$step] = $this->isFulfilledStep($step);
		}

		return $result;
	}

	public function getThxPage(): ?string
	{
		return $this->_thxPage;
	}

	public function setThxPage(?string $url): void
	{
		$this->_thxPage = $url;
	}

	public function isTest(): bool
	{
		return $this->_isTest;
	}

	public function setIsTest(bool $isTest = true): void
	{
		$this->_isTest = $isTest;
	}

	public function getSource(): ?MwsOrderSource
	{
		return $this->_source !== null && isset($this->_source['type']) ? MwsOrderSource::fromArray($this->_source) : null;
	}

	public function setSource(MwsOrderSource $source): void
	{
		$this->_source = $source->toArray();
	}

	/**
	 * Clear content of the cart, including clearing session data.
	 */
	public function clearAll(): void
	{
		// Properties
		$this->_loaded = false;
		$this->_recountNeeded = true;
		$this->_items->clear();
		$this->_stepsFulfilled = [];
		$this->_contact = [];
		$this->_discountCode = null;
		$this->_shipping = null;
		$this->_shippingPriceIncluded = null;
		$this->_shippingPrice = null;
		$this->_shippingInfo = [];
		$this->_paymentMethod = null;
		$this->_heurekaDisagree = false;
		$this->_purposes = [];
		$this->_storedTotalPrice = null;
		$this->_availabilityErrorsCount = 0;
		$this->_thxPage = null;
		$this->_source = null;

		// Session
		foreach (array_keys($this->toArray()) as $key) {
			unset($this->getSession()[$key]);
		}
	}

	/**
	 * @TODO move this
	 * Update ordered count of products within cart.
	 */
	public function incOrderedCount(): void
	{
		//Make statistics of ordered products.
		foreach ($this->getItems()->getAll() as $cartItem) {
			$product = $cartItem->getProduct();
			$product->addOrderedCount($cartItem->getCount());
		}
	}

	/**
	 * Returns if cart should be invoiced in simplified mode.
	 */
	public function useSimplifiedInvoice(): bool
	{
		$wantInvoice = $this->wantInvoice();

		$source = $this->getSource();
		if ($source !== null && $source->getFormId() !== null) {
			/** @var MwsForm|null $form */
			$form = MwsForm::getOneById($source->getFormId());
			if ($form !== null) {
				$canSimplified = MWS()->gateways()->getDefault()->isSimplifiedInvoiceAllowedForForm($form);

				return $canSimplified && !$wantInvoice;
			}
		} elseif ($source !== null && $source->getType() === MwsOrderSourceType::QuickBuy) {
			$canSimplified = MWS()->gateways()->getDefault()->isSimplifiedInvoiceAllowedForQuickBuy();

			return $canSimplified && !$wantInvoice;
		} else {
			// simplified invoice is allowed
			$canSimplified = MWS()->gateways()->getDefault()->isSimplifiedInvoiceAllowedForEshop();
			// in cart is possible force invoice

			return $canSimplified && !$wantInvoice;
		}

		return false;
	}

	/**
	 * If content of the cart requires shipping. That is in case when there is at least one product with physical delivery.
	 */
	public function isShippingRequired(): bool
	{
		foreach ($this->getItems()->getAll() as $item) {
			if (MwsProductType::isPhysical($item->getProduct()->getType())) {
				return true;
			}
		}

		return false;
	}

	public function isElectronicContained(): bool
	{
		foreach ($this->getItems()->getAll() as $item) {
			if (MwsProductType::isElectronic($item->getProduct()->getType())) {
				return true;
			}
		}

		return false;
	}

	public function isOnlyElectronicContained(): bool
	{
		$isContained = false;

		foreach ($this->getItems()->getAll() as $item) {
			if (MwsProductType::isElectronic($item->getProduct()->getType())) {
				$isContained = true;
			} else {
				return false;
			}
		}

		return $isContained;
	}

	public function isOSSApplicableContained(): bool
	{
		foreach ($this->getItems()->getAll() as $item) {
			if (MwsProductType::isApplicableForOSS($item->getProduct()->getType())) {
				return true;
			}
		}

		return false;
	}

	public function isProductTypeContained(string $productType, bool $ignoreMiniupsells = false): bool
	{
		foreach ($this->getItems()->getAll() as $item) {
			if ((!$ignoreMiniupsells || !$item->isMiniupsell()) && $item->getProduct()->getType() === $productType) {
				return true;
			}
		}

		return false;
	}

	public function isOnlyProductTypeContained(string $productType, bool $ignoreMiniupsells = false): bool
	{
		$isContained = false;

		foreach ($this->getItems()->getAll() as $item) {
			if ($item->getProduct()->getType() === $productType) {
				$isContained = true;
			} elseif (!$ignoreMiniupsells || !$item->isMiniupsell()) {
				return false;
			}
		}

		return $isContained;
	}

	/**
	 * Check conditions that must be fulfilled to allow reverse charge
	 *
	 * @throws ReverseChargeApplicationException
	 * @todo cache
	 */
	public function shouldApplyReverseCharge(): bool
	{
		// Seller must be the payer of VAT
		if (MWS()->getVATs()->getAccountingType() === MwsVatAccounting::noVat) {
			return false;
		}

		$seller = MWS()->getSupplierContact();
		$buyer = $this->getInvoiceContact();

		// Invoice address is filled
		if ($seller === null || $seller->getAddress() === null || $buyer->getAddress() === null) {
			return false;
		}

		// Buyer must be from other country then seller
		if ($seller->getAddress()->getCountry() === $buyer->getAddress()->getCountry()) {
			return false;
		}

		$buyerCompany = $buyer->getCompany();
		if ($buyerCompany === null) {
			return false;
		}

		$isOnlyService = $this->isOnlyProductTypeContained(MwsProductType::Service, true);
		if ($isOnlyService) {
			// For service, there must be filled IČO
			return (bool) $buyerCompany->getId();
		} else {
			// Buyer must be payer of VAT
			if (!(bool) $buyerCompany->getTaxId() && !(bool) $buyerCompany->getVatId()) {
				$containService = $this->isProductTypeContained(MwsProductType::Service, true);

				if ($containService && (bool) $buyerCompany->getId()) {
					throw new ReverseChargeApplicationException('Cannot apply reverse charge for this combination of items.');
				}

				return false;
			}
			// TODO check validity of tax id (vat id)
		}

		// Exception for live events organised in the seller's country
		$isOnlyEvent = $this->isOnlyProductTypeContained(MwsProductType::LiveEvent, true);

		if ($isOnlyEvent) {
			return false;
		}

		foreach ($this->getItems()->getAll() as $item) {
			if (!MwsProductType::isForReverseChargeApplication($item->getProduct()->getType()) && !$item->isMiniupsell()) {
				throw new ReverseChargeApplicationException('Cannot apply reverse charge for this combination of items.');
			}
		}

		return true;
	}

	/**
	 * Check conditions that must be fulfilled to allow OSS
	 * WARNING: Before applying OSS you must separately check that each product (type) is electronic or physical
	 *
	 * @todo cache
	 */
	public function shouldApplyOSS(): bool
	{
		// Seller must be the payer of VAT
		if (MWS()->getVATs()->getAccountingType() === MwsVatAccounting::noVat) {
			return false;
		}

		// OSS is not enabled
		if (MWS()->getVATs()->getEUInvoicing() === MwsVatElectronicInvoicing::Inland) {
			return false;
		}

		$seller = MWS()->getSupplierContact();
		$buyer = $this->getInvoiceContact();

		// Invoice address is filled
		if ($seller === null || $seller->getAddress() === null || $buyer->getAddress() === null) {
			return false;
		}

		$buyerCountry = $buyer->getAddress()->getCountry();
		// Buyer must be from other country then seller
		if ($seller->getAddress()->getCountry() === $buyerCountry) {
			return false;
		}

		// Buyer must be from EU
		if (!MwsCountry::isEUCountry($buyerCountry)) {
			return false;
		}

		$buyerCompany = $buyer->getCompany();
		// Buyer must not have IC or DIC
		if ($buyerCompany !== null) {
			return false;
		}

		// OSS can be applied just for this product types: electronic-product, membership, electronic-service
		return $this->isOSSApplicableContained();
	}


	/**
	 * Get currency for the cart. Currency is derived by selected country.
	 */
	public function getCurrency(): string
	{
		if (MWS()->getCurrencyMode() === MwsCurrencyMode::Default) {
			return MWS()->getDefaultCurrency('key');
		}

		$countryCurrency = MwsCurrencyEnum::getByCountry($this->getInvoiceCountry());

		if (!in_array($countryCurrency, MWS()->getCurrencies(), true)) {
			return MWS()->getDefaultCurrency('key');
		}

		return $countryCurrency;
	}

	public function getInvoiceCountry(): string
	{
		// @TODO check with $this->doGetSupportedCountries()
		return MwsCountry::checkedValue($this->getContact()['address']['country'] ?? null, MWS()->getDefaultShippingCountry());
	}

	public function getShippingCountry(): string
	{
		// @TODO check with $this->doGetSupportedCountries()
		$contact = $this->getContact();
		if (($contact['has_shipping_addr'] ?? false) && ($contact['shipping_address']['country'] ?? false)) {
			return MwsCountry::checkedValue($contact['shipping_address']['country'] ?? null, $this->getInvoiceCountry());
		}

		return $this->getInvoiceCountry();
	}

	public function setRecountNeeded(bool $recountNeeded = true): void
	{
		$this->_recountNeeded = $recountNeeded;
	}

	public function toAnalyticsArray(): array
	{
		$result = [];

		foreach ($this->getItems()->getAll() as $item) {
			$product = $item->getProduct();
			$pResult = $product->toAnalyticsArray($item->getCount());
			$storedPrice = $item->getStoredPrice();
			if ($storedPrice !== null) {
				$pResult['price'] = round($storedPrice->getPriceVatExcluded(), 2);
				$pResult['currency'] = $storedPrice->getCurrency();
			}
			$result[] = $pResult;
		}

		return $result;
	}

}

/**
 * List of cart items.
 */
class MwsCartItems
{

	/** @var MwsCart */
	private $_cart;

	/** @var MwsCartItem[] */
	private $_items = [];

	public function __construct(MwsCart $cart)
	{
		$this->_cart = $cart;
	}

	public function count()
	{
		return count($this->_items);
	}

	/** @return MwsCartItem[] */
	public function getAll(): array
	{
		return $this->_items;
	}

	/**
	 * Search for item by its product id.
	 */
	public function getOneById(int $productId): ?MwsCartItem
	{
		foreach ($this->_items as $item) {
			if ($item->getProduct()->getId() == $productId) {
				return $item;
			}
		}

		return null;
	}

	public function getTotalWeight(): float
	{
		$weight = 0.0;
		foreach ($this->_items as $item) {
			$weight += ($item->getProduct()->getWeight() ?? 0) * $item->getCount();
		}

		return $weight;
	}

	/**
	 * Add new item into the cart. If same product is already present then only increment number of items in the basket.
	 *
	 * @return int Returns number of added items. If no addition occurred, 0 is returned.
	 */
	public function add(MwsCartItem $item): int
	{
		$found = $this->getOneById($item->getProduct()->getId());
		if ($found) {
			// only increse amount
			$found->setCount($found->getCount() + $item->getCount());
		} else {
			$item->setParent($this);
			$this->_items[] = $item;
		}
		$this->setRecountNeeded();

		return $item->getCount();
	}

	/**
	 * Remove item from the cart.
	 */
	public function remove(MwsCartItem $item): bool
	{
		if (($key = array_search($item, $this->_items, true)) !== false) {
			unset($this->_items[$key]);
			$this->setRecountNeeded();

			return true;
		}

		return false;
	}

	/**
	 * Is cart empty?
	 */
	public function isEmpty(): bool
	{
		return !$this->_items;
	}

	/** @param mixed[] $items */
	public function load(array $items): void
	{
		foreach ($items as $productId => $data) {
			$product = MwsProduct::getOneById($productId);
			if ($product === null) {
				mwshoplog("Product with id=[$productId] does not exists or is not a product.", MWLL_WARNING, 'cart');
			} else {
				$this->add(new MwsCartItem($product, $data));
			}
		}
	}

	public function clear(): void
	{
		$this->_items = [];
	}

	public function setRecountNeeded(): void
	{
		$this->_cart->setRecountNeeded();
	}

	/** @return mixed[] */
	public function toReducedArray(): array
	{
		$reduced = [];

		foreach ($this->_items as $item) {
			$reduced[$item->getProduct()->getId()] = $item->toArray();
		}

		return $reduced;
	}
}

/**
 * One item of the cart.
 */
class MwsCartItem
{

	/** @var MwsCartItems|null */
	private $_parent = null;

	/** @var MwsProduct */
	private $_product;

	/** @var int */
	private $_count;

	private $_availabilityStatus;

	private $_availabilityError;

	// Property storage
	private $_storedPrice = null;

	/** Product price in set currency */
	private $_storedShopPrice = null;

	/** Always in base currency */
	private $_storedProductPrice = null;

	/** Total order item price (multiplied by an amount) */
	private $_storedTotalPrice = null;

	/** @var bool */
	private $_miniupsell = false;

	public function __construct(MwsProduct $product, array $data = [])
	{
		$this->_product = $product;
		$this->_count = (int) ($data['count'] ?? 1);
		$this->_availabilityStatus = (int) ($data['availabilityStatus'] ?? MwsProductAvailabilityStatus::Unavailable_Disabled);
		$this->_availabilityError = $data['availabilityError'] ?? '';

		if (isset($data['storedPrice'])) {
			$this->_storedPrice = MwsPrice::createByArray($data['storedPrice']);
		}
		if (isset($data['storedShopPrice'])) {
			$this->_storedShopPrice = MwsPrice::createByArray($data['storedShopPrice']);
		}
		if (isset($data['storedProductPrice'])) {
			$this->_storedProductPrice = MwsPrice::createByArray($data['storedProductPrice']);
		}
		if (isset($data['storedTotalPrice'])) {
			$this->_storedTotalPrice = MwsPrice::createByArray($data['storedTotalPrice']);
		}
		if (isset($data['miniupsell'])) {
			$this->_miniupsell = (bool) $data['miniupsell'];
		}
	}

	public function getProduct(): MwsProduct
	{
		return $this->_product;
	}

	public function getCount(): int
	{
		return $this->_count;
	}

	public function setCount(int $count): void
	{
		if ($this->_count !== $count) {
			$this->_count = $count;
			$this->setRecountNeeded();
		}
	}

	public function getStoredPrice(): ?MwsPrice
	{
		return $this->_storedPrice;
	}

	public function setStoredPrice(?MwsPrice $storedPrice): void
	{
		$this->_storedPrice = $storedPrice;
	}

	public function getStoredShopPrice(): ?MwsPrice
	{
		return $this->_storedShopPrice;
	}

	public function setStoredShopPrice(?MwsPrice $storedShopPrice): void
	{
		$this->_storedShopPrice = $storedShopPrice;
	}

	public function getStoredProductPrice(): ?MwsPrice
	{
		return $this->_storedProductPrice;
	}

	public function setStoredProductPrice(?MwsPrice $storedProductPrice): void
	{
		$this->_storedProductPrice = $storedProductPrice;
	}

	public function getStoredTotalPrice(): ?MwsPrice
	{
		return $this->_storedTotalPrice;
	}

	public function setStoredTotalPrice(?MwsPrice $storedTotalPrice): void
	{
		$this->_storedTotalPrice = $storedTotalPrice;
	}

	public function setParent(MwsCartItems $parent): void
	{
		$this->_parent = $parent;
	}

	private function setRecountNeeded(): void
	{
		if (!$this->_parent) {
			$this->_parent->setRecountNeeded();
		}
	}

	public function getAvailabilityStatus(): int
	{
		return $this->_availabilityStatus;
	}

	public function setAvailabilityStatus(int $availabilityStatus): void
	{
		$this->_availabilityStatus = $availabilityStatus;
	}

	public function getAvailabilityError(): string
	{
		return $this->_availabilityError;
	}

	public function setAvailabilityError(string $availabilityError): void
	{
		$this->_availabilityError = $availabilityError;
	}

	public function isMiniupsell(): bool
	{
		return $this->_miniupsell;
	}

	public function setMiniupsell(bool $miniupsell = true): void
	{
		$this->_miniupsell = $miniupsell;
	}

	public function toArray(): array
	{
		$result = [
			'count' => $this->getCount(),
			'miniupsell' => $this->isMiniupsell(),
		];
		if ($this->_storedPrice) {
			$result['storedPrice'] = $this->_storedPrice->toArray();
		}
		if ($this->_storedShopPrice) {
			$result['storedShopPrice'] = $this->_storedShopPrice->toArray();
		}
		if ($this->_storedProductPrice) {
			$result['storedProductPrice'] = $this->_storedProductPrice->toArray();
		}
		if ($this->_storedTotalPrice) {
			$result['storedTotalPrice'] = $this->_storedTotalPrice->toArray();
		}
		if ($this->_availabilityStatus) {
			$result['availabilityStatus'] = $this->_availabilityStatus;
		}
		if ($this->_availabilityError) {
			$result['availabilityError'] = $this->_availabilityError;
		}

		return $result;
	}

	/**
	 * Check availability of amount of requested items. Set {@link availabilityStatus} and {@link availabilityError}.
	 *
	 * @param bool $decreaseStock If true then try to decrease stock. If stock is enabled and gets bellow 0 then forms error message
	 *                            for insufficient stock status.
	 * @return bool Returns false if error is present, true on success (item is/was available).
	 */
	public function checkAvailability(bool $decreaseStock = false): bool
	{
		$product = $this->getProduct();
		$count = $this->getCount();
		$status = $product->getAvailabilityStatus($count);
		// For stock-enabled product pre-decrease items on stock
		if ($decreaseStock && $product->isStockEnabled() && $product->canBuy($status)) {
			if ($product->updateStockCount($count, MwsStockUpdate::Dec)) {
				$stockCount = $product->getStockCount();
				if ($stockCount >= 0 || $product->stockAllowBackorders()) {
					// There were enough items on stock
					$status = $product->getAvailabilityStatus(0);
					$error = $product->getAvailabilityError(0, $status);
				} else {
					// Not enough items on stock, can still be OK for backorders
					$status = $product->getAvailabilityStatus($stockCount + $count);
					$error = $product->getAvailabilityError($stockCount + $count, $status);
					// Return into stock
					$product->updateStockCount($count, MwsStockUpdate::Inc);
				}
			} else {
				// Error updating status
				// Logging was performed by "updatedStockCount()" routine.
				$status = MwsProductAvailabilityStatus::Unavailable_Disabled;
				$error = sprintf(__('Interní chyba (aktualizace skladových zásob produktu \'%s\')', 'mwshop'), $product->getName());
			}
		} else {
			$error = $product->getAvailabilityError($count, $status);
		}
		$this->setAvailabilityStatus($status);
		$this->setAvailabilityError($error);

		return $product->canBuy($status);
	}
}

/**
 * Temporary cart with disabled loading and saving.
 * Class MwsCartTemporary
 */
class MwsCartTemporary extends MwsCart
{

	public function save(): void
	{
		// Do not save anything
	}

	protected function loadFromSession(bool $reload = false): void
	{
		// Load nothing
	}

}
