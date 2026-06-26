<?php
/**
 * Simple single product
 * User: kuba
 * Date: 29.08.16
 * Time: 16:32
 */


/**
 * Instance of root product definition. It can be a SINGLE product or MASTER product of product variants.
 */
class MwsProductRoot extends MwsProduct
{

	/** @var MwsPrice Effective price. Depends on sale-price settings if full or sale price is here. */
	private $_price = null;

	/** @var bool */
	private $_isDiscountedNow = false;

	/** @var MwsPrice Full price without discount. */
	private $_priceFull = null;

	/** @var MwsPrice|null */
	private $_minPrice = null;

	/** @var MwsPrice|null */
	private $_maxPrice = null;

	/** @var MwsProductType */
	private $_type;

	/** @var bool */
	private $_stockEnabled;

	/** @var bool */
	private $_stockAllowBackorders;

	private $_sellRestriction = MwsSellRestriction::None;

	private $_sellEnabledFrom = null;

	private $_sellEnabledTill = null;

	private $_salePriceType = MwsSalePriceType::None;

	private $_salePriceEnabledFrom = null;

	private $_salePriceEnabledTill = null;

	private $_structure = null;

	private $_properties = null;

	private $_tags = null;

	private $_detailUrl = '';

	private $_hideComments = false;

	private $_showSimilar = false;

	private $_showSocial = true;

	private $_hideInListings = false;

	private ?float $_weight = null;

	private ?string $_brand = null;

	public function getType(): string
	{
		return $this->_type;
	}

	public function isShippingRequired(): bool
	{
		return MwsProductType::isPhysical($this->_type);
	}

	public function getStructure(): string
	{
		if (!$this->_structure) {
			$this->_structure = get_post_meta($this->getId(), MWS_PRODUCT_META_KEY_STRUCTURE)[0] ?? MwsProductStructureType::Single;
		}

		return $this->_structure;
	}

	public function getPrice(): MwsPrice // @TODO need to be nullable?
	{
		if (!$this->_price) {
			$this->loadVariantPrices();
		}

		return $this->_price;
	}

	public function getPriceFull(): MwsPrice
	{
		if (!$this->_priceFull) {
			$this->loadVariantPrices();
		}

		return $this->_priceFull;
	}

	public function setPrices(?float $priceFull, ?float $priceSale, bool $isDiscounted): void
	{
		if ($priceFull !== null) {
			$vatPercentage = $this->getVatPercentage();
			// Price full
			$this->_priceFull = new MwsPrice(
				$priceFull,
				$vatPercentage
			);

			$this->_isDiscountedNow = $isDiscounted;

			$this->_price = $isDiscounted && $priceSale !== null ? new MwsPrice(
					$priceSale,
					$vatPercentage
				) : $this->_priceFull;
		}
	}

	public function variantPricesAreEqual(): bool
	{
		$ignoreVisibility = $this->areAllVariantsInvisible();

		$minPriceVariant = $this->getMinPriceVariant($ignoreVisibility);
		$maxPriceVariant = $this->getMaxPriceVariant($ignoreVisibility);

		if ($minPriceVariant === null || $maxPriceVariant === null) {
			return true;
		}

		return $minPriceVariant->getPrice()->getPriceVatIncluded() === $maxPriceVariant->getPrice()->getPriceVatIncluded();
	}

	public function getWeight(): ?float
	{
		return $this->_weight;
	}

	public function getBrand(): ?string
	{
		return $this->_brand;
	}

	public function isDiscountedNow(): bool
	{
		return $this->_isDiscountedNow;
	}

	public function isStockEnabled(): bool
	{
		return $this->_stockEnabled;
	}

	public function stockAllowBackorders(): bool
	{
		return $this->_stockAllowBackorders;
	}

	public function availableCountToSell(): int
	{
		$stockCount = 999999999;
		if ($this->isStockEnabled() && !$this->stockAllowBackorders()) {
			$stockCount = $this->getStockCount();
		}

		return $stockCount;
	}

	public function getVariantDefinition(): ?array
	{
		$variants = get_post_meta($this->getId(), MWS_PRODUCT_META_KEY_VARIANTLIST, true);

		return $variants ?: [];
	}

	public function setVariantDefinition(?array $variantDefinition): void
	{
		update_post_meta($this->getId(), MWS_PRODUCT_META_KEY_VARIANTLIST, $variantDefinition);
	}

	public function addConversionCodes(): void
	{
		$data = get_post_meta($this->getId(), MWS_PRODUCT_META_KEY_PAGECODES, true);
		MwCodes()->addConversionCodesFromData($data);
	}

	public function getProperties(): array
	{
		if ($this->_properties === null) {
			$this->_properties = [];
			foreach ($this->_meta['properties'] ?? [] as $propId => $propValue) {
				$property = MwsProperty::getOneById($propId);
				// Use only properties with assigned values
				if ($property) {
					$newValue = $property->getValue($propValue, true);
					if ($newValue !== null) {
						$this->_properties[] = $newValue;
					}
				}
			}
		}

		return $this->_properties;
	}

	public function getTags(): array
	{
		if ($this->_tags === null) {
			$this->_tags = MwsTag::getPostTerms($this->getId(), MWS_PRODUCT_TAG_SLUG, ['published' => true]);
		}

		return $this->_tags;
	}
	public function getTagsSet(): array
	{
		$set = [];
		$tags = $this->getTags();
		foreach ($tags as $tag) {
			$set[] = [
				'text' => $tag->getName(),
				'color' => $tag->getColor(),
			];
		}

		return $set;
	}

	public function getDetailUrl(): string
	{
		return $this->_detailUrl;
	}

	public function getSalePriceType(): string
	{
		return $this->_salePriceType;
	}

	public function getSalePriceEnabledFrom(): ?int
	{
		return $this->_salePriceEnabledFrom;
	}

	public function getSalePriceEnabledTill(): ?int
	{
		return $this->_salePriceEnabledTill;
	}

	public function getSellRestriction(): string
	{
		return $this->_sellRestriction;
	}

	public function getSellEnabledFrom(): ?int
	{
		return $this->_sellEnabledFrom;
	}

	public function getSellEnabledTill(): ?int
	{
		return $this->_sellEnabledTill;
	}

	public function showSimilar(): bool
	{
		return $this->_showSimilar;
	}

	public function showSocial(): bool
	{
		return $this->_showSocial;
	}

	public function hideComments(): bool
	{
		return $this->_hideComments;
	}

	public function hideInListings(): bool
	{
		return $this->_hideInListings;
	}

	protected function loadProperties_Derived(): void
	{
		// Product type according to delivery
		$defaultType = MwsProductType::ElectronicService;
		$this->_type = isset($this->_meta['type'])
			? MwsProductType::checkedValue($this->_meta['type'], $defaultType)
			: $defaultType;

		// detailUrl
		if (isset($this->_meta['custom_detail']) && $this->_meta['detail_page']) {
			$this->_detailUrl = get_permalink($this->_meta['detail_page']);
		} else {
			$this->_detailUrl = $this->getId() ? get_permalink($this->getId()) : '';
		}

		// hiding of UI elements
		$this->_hideComments = isset(MWS()->visual_setting['hide_comments']) || $this->getCommentStatus() !== 'open';
		$this->_showSocial = !isset(MWS()->visual_setting['hide_social']);
		$this->_showSimilar = isset(MWS()->visual_setting['hide_similar_products']) ? false : isset($this->_meta['show_similar_products']);

		// hiding product from product listings
		$this->_hideInListings = (bool) ($this->_meta['hide_in_listings'] ?? false);

		// Sell restriction
		if ($this->_meta['selling_restrict'] ?? false) {
			$this->_sellRestriction = MwsSellRestriction::checkedValue($this->_meta['selling_restrict_type'], MwsSellRestriction::None);
			switch ($this->_sellRestriction) {
				case MwsSellRestriction::FullDisable:
					break;
				case MwsSellRestriction::EnabledFrom:
					$this->_sellEnabledFrom = mwExtractDateTimeFromField($this->_meta['selling_enabled_from'] ?? []);

					break;
				case MwsSellRestriction::EnabledTill:
					$this->_sellEnabledTill = mwExtractDateTimeFromField($this->_meta['selling_enabled_till'] ?? []);

					break;
				case MwsSellRestriction::EnabledInterval:
					$this->_sellEnabledFrom = mwExtractDateTimeFromField($this->_meta['selling_enabled_from'] ?? []);
					$this->_sellEnabledTill = mwExtractDateTimeFromField($this->_meta['selling_enabled_till'] ?? []);

					break;
				default:
					$this->_sellRestriction = MwsSellRestriction::None;

					break;
			}
		}

		// Weight
		$this->_weight = isset($this->_meta['weight']['size']) ? (float) str_replace(',', '.', $this->_meta['weight']['size']) : null;

		// Brand
		$this->_brand = $this->_meta['brand'] ?? null;

		// Stock enabled
		$this->_stockEnabled = (bool) ($this->_meta['stock_enabled'] ?? false);
		$this->_stockAllowBackorders = $this->_stockEnabled && ($this->_meta['stock_allow_backorders'] ?? false);

		switch ($this->getStructure()) {
			case MwsProductStructureType::Single:
				$this->loadProperties_Single();

				break;
			case MwsProductStructureType::Variants:
				$this->loadProperties_Variant();

				break;
		}
	}

	private function loadProperties_Variant(): void
	{
		// back compatibility
		$this->_stockEnabled = isset($this->_meta['variant_stock_enabled']) ? (bool) $this->_meta['variant_stock_enabled'] : $this->_stockEnabled;
		$this->_stockAllowBackorders = isset($this->_meta['variant_stock_allow_backorders']) ? $this->_stockEnabled && $this->_meta['variant_stock_allow_backorders'] : $this->_stockAllowBackorders;
	}

	private function loadProperties_Single(): void
	{
		$vatPercentage = $this->getVatPercentage();
		// Price full
		$this->_priceFull = new MwsPrice(
			(float) ($this->_meta['price']['size'] ?? $this->_meta['price'] ?? 0),
			$vatPercentage
		);

		// Sale price type
		if ($this->_meta['price_sale_enabled'] ?? false) {
			$this->_salePriceType = MwsSalePriceType::checkedValue($this->_meta['price_sale_type'], MwsSalePriceType::None);
			switch ($this->_salePriceType) {
				case MwsSalePriceType::Continuous:
					break;
				case MwsSalePriceType::EnabledFrom:
					$this->_salePriceEnabledFrom = mwExtractDateTimeFromField($this->_meta['price_sale_enabled_from'] ?? []);

					break;
				case MwsSalePriceType::EnabledTill:
					$this->_salePriceEnabledTill = mwExtractDateTimeFromField($this->_meta['price_sale_enabled_till'] ?? []);

					break;
				case MwsSalePriceType::EnabledInterval:
					$this->_salePriceEnabledFrom = mwExtractDateTimeFromField($this->_meta['price_sale_enabled_from'] ?? []);
					$this->_salePriceEnabledTill = mwExtractDateTimeFromField($this->_meta['price_sale_enabled_till'] ?? []);

					break;
				default:
					$this->_salePriceType = MwsSalePriceType::None;

					break;
			}
		}

		// Price -- considering discounting
		$salePrice = $this->_meta['price_sale']['size'] ?? null;
		$this->_isDiscountedNow = (!empty($salePrice) || is_numeric($salePrice)) && $this->canDiscountNow();

		$this->_price = $this->_isDiscountedNow
			? new MwsPrice((float) $salePrice, $vatPercentage) // Sale price activated
			: $this->_priceFull; //Ordinary price activated
	}

	/**
	 * Get all defined variant children of the product.
	 *
	 * @return MwsProductVariant[]
	 */
	public function getVariants(array $queryArgs = ['post_status' => 'publish']): array
	{
		$args = array_merge(
			[
				'post_type' => MWS_VARIANT_SLUG,
				'post_parent' => $this->getId(),
				'orderby' => 'menu_order post_date',
				'order' => 'ASC',
			], //a must
			$queryArgs, //user customization
			['posts_per_page' => -1] //default values
		);
		$qry = new WP_Query($args);
		$res = [];
		if ($qry->have_posts()) {
			foreach ($qry->posts as $post) {
				$variant = MwsProductVariant::createNew($post);
				if ($variant) {
					$res[] = $variant;
				}
			}
		}

		return $res;
	}

	/**
	 * Update variant prices. As a result a price/saleprice for VARIANT definition is set to the cheapest price from available variants.
	 */
	private function loadVariantPrices(): void
	{
		$minPriceVariant = $this->getMinPriceVariant();
		$maxPriceVariant = $this->getMaxPriceVariant();

		$this->_price = $minPriceVariant !== null ? $minPriceVariant->getPrice() : new MwsPrice(0);
		$this->_priceFull = $minPriceVariant !== null ? $minPriceVariant->getPriceFull() : new MwsPrice(0);
		$this->_minPrice = $minPriceVariant !== null ? $minPriceVariant->getPrice() : new MwsPrice(0);
		$this->_maxPrice = $maxPriceVariant !== null ? $maxPriceVariant->getPrice() : new MwsPrice(0);
	}

	public function getMinPriceVariant(bool $ignoreVisibility = false): ?MwsProductVariant
	{
		$minPriceVariant = null;
		foreach ($this->getVariants() as $variant) {
			if (!$variant->isListVisible() && !$ignoreVisibility) {
				continue;
			}

			$currentPrice = $variant->getPrice();
			if ($minPriceVariant === null || $currentPrice->getPriceVatIncluded() < $minPriceVariant->getPrice()->getPriceVatIncluded()) {
				$minPriceVariant = $variant;
			}
		}

		return $minPriceVariant;
	}

	public function getMaxPriceVariant(bool $ignoreVisibility = false): ?MwsProductVariant
	{
		$maxPriceVariant = null;
		foreach ($this->getVariants() as $variant) {
			if (!$variant->isListVisible() && !$ignoreVisibility) {
				continue;
			}

			$currentPrice = $variant->getPrice();
			if ($maxPriceVariant === null || $currentPrice->getPriceVatIncluded() > $maxPriceVariant->getPrice()->getPriceVatIncluded()) {
				$maxPriceVariant = $variant;
			}
		}

		return $maxPriceVariant;
	}

	public function areAllVariantsInvisible(): bool
	{
		foreach ($this->getVariants() as $variant) {
			if ($variant->isListVisible()) {
				return false;
			}
		}

		return true;
	}

	/** @internal */
	protected function getPriceForSaleFull(): MwsPrice
	{
		$saleFullPrice = $this->getPrice();

		$amount = $saleFullPrice->getPriceVatIncluded();
		if ($amount === 0.0 && $this->areAllVariantsInvisible()) {
			$minPriceVariant = $this->getMinPriceVariant(true);

			if ($minPriceVariant !== null) {
				$saleFullPrice = $minPriceVariant->getPrice();
			}
		}

		return $saleFullPrice;
	}

	public function getAutomations(): array
	{
		$actions = [];
		if ($this->getType() === MwsProductType::Membership && isset($this->_meta['membership_setting'])) {
			$actions[] = [
				'event' => MwsAutomationEvent::OnPaid,
				'action' => MwsAutomationAction::AddMembership,
				'member_section' => $this->_meta['membership_setting'],
			];
			$actions[] = [
				'event' => MwsAutomationEvent::OnStorno,
				'action' => MwsAutomationAction::RemoveMembership,
				'remove_member_section' => $this->_meta['membership_setting'],
			];
		} elseif (MwsProductType::isElectronic($this->getType()) && ($file = $this->_meta['electronic_product_file'] ?? null)) {
			$actions[] = [
				'event' => MwsAutomationEvent::OnPaid,
				'action' => MwsAutomationAction::SendFile,
				'file' => $file,
			];
		}

		$meta = get_post_meta($this->getPost()->ID, 'automations', true);
		$metaActions = isset($meta['actions']) && is_array($meta['actions']) ? $meta['actions'] : [];

		return array_merge($actions, $metaActions);
	}

	public function getStockCount(): int
	{
		if ($this->getStructure() === MwsProductStructureType::Variants) {
			return array_sum(array_map(function (MwsProductVariant $variant) {
				return max($variant->getStockCount(), 0);
			}, $this->getVariants()));
		}

		return parent::getStockCount();
	}

}
