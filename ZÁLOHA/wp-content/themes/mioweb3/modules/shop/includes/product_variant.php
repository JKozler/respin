<?php

use Mioweb\VisualEditor\Lib\Image;

/**
 * Product variations
 * User: kuba
 * Date: 29.08.16
 * Time: 16:32
 */

class MwsProductVariant extends MwsProduct
{

	private $_variantValues;

	/** @var MwsPrice Effective price. Depends on sale-price settings if full or sale price is here. */
	private $_price;

	/** @var bool */
	private $_isDiscountedNow;

	/** @var MwsPrice Full price without discount. */
	private $_priceFull;

	/** @var MwsSalePriceType */
	private $_salePriceType;

	private $_product;

	private $_order;

	private $_showSimilar = false;

	private ?float $_weight;

	public function __construct(?WP_Post $post, MwsProductRoot $product)
	{
		$this->_product = $product;
		parent::__construct($post);
	}

	public function getProduct(): MwsProductRoot
	{
		return $this->_product;
	}

	public function getStructure(): string
	{
		return MwsProductStructureType::OneVariant;
	}

	public function isStockEnabled(): bool
	{
		return $this->getProduct()->isStockEnabled();
	}

	public function stockAllowBackorders(): bool
	{
		return $this->getProduct()->stockAllowBackorders();
	}

	public function getVatId(): ?int
	{
		return $this->getProduct()->getVatId();
	}

	public function getDetailUrl(): string
	{
		return $this->getProduct()->getDetailUrl();
	}

	public function getSellRestriction(): string
	{
		return $this->getProduct()->getSellRestriction();
	}

	public function getSellEnabledFrom(): ?int
	{
		return $this->getProduct()->getSellEnabledFrom();
	}

	public function getSellEnabledTill(): ?int
	{
		return $this->getProduct()->getSellEnabledTill();
	}

	public function getSalePriceType(): string
	{
		return $this->getProduct()->getSalePriceType();
	}

	public function getSalePriceEnabledFrom(): ?int
	{
		return $this->getProduct()->getSalePriceEnabledFrom();
	}

	public function getSalePriceEnabledTill(): ?int
	{
		return $this->getProduct()->getSalePriceEnabledTill();
	}

	public function isShippingRequired(): bool
	{
		return $this->getProduct()->isShippingRequired();
	}

	/** @return MwsPropertyValue[] */
	public function getVariantValues(): array
	{
		if ($this->_variantValues === null) {
			$this->_variantValues = MwsPropertyValue::unserializeArray($this->_meta['variant_values'] ?? []);
		}

		return $this->_variantValues;
	}

	public function getType(): string
	{
		return $this->getProduct()->getType();
	}

	public function getGallery(): array
	{
		return $this->getProduct()->getGallery();
	}

	public function getProperties(): array
	{
		return $this->getProduct()->getProperties();
	}

	public function isDiscountDisabled(): bool
	{
		return $this->getProduct()->isDiscountDisabled();
	}

	public function getAutomations(): array
	{
		return $this->getProduct()->getAutomations();
	}

	public function getPrice(): MwsPrice
	{
		return $this->_price;
	}

	public function getVariantOrder(): int
	{
		return $this->_order;
	}

	public function getPriceFull(): MwsPrice
	{
		return $this->_priceFull;
	}

	public function getWeight(): ?float
	{
		return $this->_weight === null || $this->_weight === 0.0 ? $this->getProduct()->getWeight() : $this->_weight;
	}

	public function getTagsSet(): array
	{
		return $this->getProduct()->getTagsSet();
	}

	public function getBrand(): ?string
	{
		return $this->getProduct()->getBrand();
	}

	public function addConversionCodes(): void
	{
		$this->getProduct()->addConversionCodes();
	}

	public function isDiscountedNow(): bool
	{
		return $this->_isDiscountedNow;
	}

	public function showSimilar(): bool
	{
		return $this->_showSimilar;
	}

	public function showSocial(): bool
	{
		return false;
	}

	public function hideComments(): bool
	{
		return true;
	}

	public function hideInListings(): bool
	{
		return $this->getProduct()->hideInListings();
	}

	protected function loadProperties_Derived(): void
	{
		$priceFull = isset($this->_meta['price']['size'])
			? (float) $this->_meta['price']['size'] // Price full
			: (float) ($this->_meta['price'] ?? 0); // Backward compatibility, when price in variant was saved within subaray.
		$vatPercentage = $this->getVatPercentage();
		$this->_priceFull = new MwsPrice(
			$priceFull,
			$vatPercentage
		);

		$this->_showSimilar = isset(MWS()->visual_setting['hide_similar_products']) ? false : isset($this->_meta['show_similar_products']);

		// Price -- considering discounting
		$salePrice = $this->_meta['price_sale'] ?? null;
		$this->_isDiscountedNow = !empty($salePrice) || is_numeric($salePrice);
		if ($this->_isDiscountedNow) {
			//Sale price activated
			$this->_price = new MwsPrice(
				(float) $salePrice,
				$vatPercentage
			);
			// Sale price type
			$this->_salePriceType = MwsSalePriceType::Continuous;
		} else {
			//Ordinary price activated
			$this->_price = $this->_priceFull;
			// Sale price type
			$this->_salePriceType = MwsSalePriceType::None;
		}
		//set weight
		$this->_weight = isset($this->_meta['weight_variant']) ? (float) str_replace(',', '.', $this->_meta['weight_variant']) : null;
	}

	/**
	 * @TODO refactor
	 * Create a new variant of a product.
	 * @param MwsProduct $product Superior product where the variant belongs to
	 * @param array $properties Array key-ed by {@link MwsProperty::id} value-ed by {@link MwsPropertyValue::id}.
	 * @param float $price
	 * @param $priceSale
	 * @param int|false $stockCount New count of stock items. If false is passed then stock is not updated.
	 * @param array $codes List of codes and values.
	 * @return MwsProductVariant
	 */
	public static function createVariant(MwsProductRoot $product, $properties, $price, $priceSale, $stockCount, $codes, $order = 0, $weight = null)
	{
		$newItem = [
			'post_type' => MWS_VARIANT_SLUG,
			'post_status' => 'publish',
			'post_parent' => $product->getId(),
		];

		$itemId = wp_insert_post($newItem);

		if ($itemId) {
			$new = new MwsProductVariant(get_post($itemId), $product);
			if ($new->updateVariant($properties, $price, $priceSale, $stockCount, $codes, $order, $weight)) {
				return $new;
			}
		}

		return null;
	}

	/**
	 * @TODO refactor
	 * @param float $weight
	 * @param $properties
	 * @param float $price
	 * @param float $priceSale
	 * @param int|false $stockCount New count of stock items. If false is passed then stock is not updated.
	 * @param array $codes List of codes
	 * @return int Id of a variant. For update the value of <a href='psi_element://id'>id</a> persists. For new save this is new ID.
	 *                              persists. For new save this is new ID.
	 * @throws MwsException Raised when validation of parameters fails. Error message is localized and can be used in UI.
	 */
	public function updateVariant($properties, $price, $priceSale, $stockCount, $codes, $order = 0, $weight = null)
	{
		// Set properties
		$errorIds = [];
		$parsedProps = [];
		if (is_array($properties)) {
			foreach ($properties as $propId => $propVal) {
				$instProperty = MwsProperty::getOneById($propId);
				if ($instProperty) {
					if (empty($propVal) && $propVal !== '0') {
						$errorIds[$propId] = sprintf(__('Hodnota parametru "%s" je prázdná.', 'mwshop'), $instProperty->getName());
					} else {
						$instPropValue = $instProperty->getValue($propVal, true);
						if ($instPropValue) {
							$parsedProps[] = $instPropValue;
						} else {
							$errorIds[$propId] = sprintf(
								__('Hodnota "%s" parametru "%s" není platná.', 'mwshop'),
								(string) $propVal,
								$instProperty->getName()
							);
						}
					}
				} else {
					$errorIds[$propId] = sprintf(__('Parametr [%s] neexistuje.', 'mwshop'), (string) $propId);
				}
			}
		}
		if (!empty($errorIds)) {
			// Critical errors in properties. Interrupt processing.
			$errorMsg = implode("\n", $errorIds);

			throw new MwsException($errorMsg);
		}

		$this->_variantValues = $parsedProps;

		// Set price
		$vatPercentage = $this->getVatPercentage();
		$this->_priceFull = new MwsPrice((float) $price, $vatPercentage);
		$this->_isDiscountedNow = (!empty($priceSale) || is_numeric($priceSale));
		$this->_price = $this->_isDiscountedNow ? new MwsPrice((float) $priceSale, $vatPercentage) : $this->_priceFull;
		// Codes
		$this->_codes = new MwsProductCodes($codes);

		// Order
		$this->_order = $order;

		// set weight
		$this->setWeight((float) $weight);


		// Save
		try {
			$newId = $this->save();
			// Saving successful?
			if ($newId) {
				//Update stock count
				if ($stockCount !== false && $this->isStockEnabled() && $this->getStockCount() != $stockCount) {
					$this->updateStockCount($stockCount, MwsStockUpdate::Set);
				}
			}
		} catch (Exception $e) {
			$newId = 0;
		}

		return $newId;
	}

	// @TODO refactor
	private function getPostArray($includeMeta = true)
	{
		$postArr = [];
		$postArr['ID'] = $this->getId();
		$varDesc = $this->composeVariantDesc();
		$postArr['post_title'] = $this->getProduct()->getName() . ($varDesc ? ' - ' . $varDesc : '');
		$postArr['comment_status'] = !(bool) $this->getProduct()->hideComments();
		$postArr['menu_order'] = $this->getVariantOrder();

		$meta = $this->loadMeta();
		$meta['weight_variant'] = $this->getWeight();
		$meta['structure'] = $this->getStructure();
		$meta['variant_values'] = MwsPropertyValue::serializeArray($this->getVariantValues());
		$meta['vat_id'] = $this->getVatId();
		$priceFull = $this->getPriceFull();
		if ($priceFull) {
			$meta['price'] = $priceFull->getPriceVatIncluded();
			if ($this->_isDiscountedNow) {
				$meta['price_sale'] = $this->getPrice()->getPriceVatIncluded();
			} else {
				unset($meta['price_sale']);
			}
		}
		$meta['codes'] = $this->getCodes()->toArray();

		$postArr['meta_input'] = [
			MWS_PRODUCT_META_KEY => $meta,
		];

		return $postArr;
	}

	/**
	 * @TODO refactor
	 * Save instance of a product variant. Saving uses properties relevant to variants.
	 * @return int Current or new post id = variant id. Failure raises exception.
	 * @throws MwsException
	 */
	private function save(): int
	{
		$this->loadMeta();
		$postArr = $this->getPostArray();
		cms_save_disable();
		$newId = wp_update_post($postArr);
		cms_save_enable();
		if ($newId) {
			// Saved successfully. Refresh post content.
			$this->_post = get_post($newId);
			$this->_meta = null;
			$this->loadMeta();
		}

		return $newId;
	}

	/**
	 * @TODO refactor
	 * Compose text description of variant. That is list of comma-separated values of each variant parameter.
	 * Name of variant parameter and od variant parameter unit can be included optionally.
	 * @param bool $includeParameterName Include name of parameter before its value.
	 * @param bool $includeUnit Include unit of parameter after its value.
	 * @param string $glue Textual glue to join each parameter string into final string.
	 * @param bool $includeEmptyValues Include parameters with empty values.
	 * @return string
	 */
	public function composeVariantDesc($includeParameterName = false, $includeUnit = true, $glue = ', ', $includeEmptyValues = false)
	{
		$arr = [];
		/** @var MwsPropertyValue $variantVal */
		foreach ($this->getVariantValues() as $variantVal) {
			$item = $variantVal->getName();
			if (empty($item) && $item != '0') {
				if (!$includeEmptyValues) {
					continue;
				}
				$item = '-';
			}
			if ($includeUnit) {
				$unit = ($variantVal->getProperty()->getUnit());
				if (!empty($unit)) {
					$item .= ' ' . $unit;
				}
			}
			if ($includeParameterName) {
				$item = $variantVal->getProperty()->getName() . ' ' . $item;
			}
			$arr[] = $item;
		}
		$res = implode($glue, $arr);

		return $res;
	}

	public function getThumbnail(): Image
	{
		$thumb = parent::getThumbnail();
		if (!$thumb->getId()) {
			$thumb = $this->getProduct()->getThumbnail();
		}

		return $thumb;
	}

	/**
	 * Gets if the variant should be visible in catalog or not. Variant is visible, when parenting product is visible,
	 * variant's availability is OK or visibility setting for unavailable variants is set to "show unavailable variants".
	 */
	public function isListVisible(?int $availabilityStatus = null): bool
	{
		return !in_array($availabilityStatus ?: $this->getAvailabilityStatus(), MWS()->getHiddenAvailabilityStatusesFor('variant'));
	}



	public function setWeight(?float $weight): void
	{
		$this->_weight = $weight;
	}



}
