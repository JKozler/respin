<?php

/**
 * MioShop product wrapper. It works as extension of {@link WP_Object}.
 * User: kuba
 * Date: 23.02.16
 * Time: 16:50
 */


/** Name of the meta key of product. */
define('MWS_PRODUCT_META_KEY', 'product');
/** Name of the meta key of product's number of ordered items. */
define('MWS_PRODUCT_META_KEY_ORDERED_COUNT', 'ordered_count');


/**
 * Class MwsProduct wraps information about a single product.
 */
abstract class MwsProduct extends mwPost
{

	/** @var null|array Cached settings of product. */
	protected $_meta = null;

	/** @var int */
	private $_stockCount = null;

	private $_gallery = null;

	/** @var MwsProductCodes List of codes. Lazy loaded. */
	protected $_codes = null;

	/**
	 * Creates new product object as a wrapper of WP_Post object.
	 *
	 * @param WP_Post $post
	 * @throws MwsException
	 */
	public function __construct(?WP_Post $post) // @TODO must by post nullable? -> becasue of create variant :(
	{
		parent::__construct($post);

		$this->loadMeta();
	}

	/** Load metadata of the product. Uses cached data if present. Does nothing if data has been already loaded. */
	protected function loadMeta()
	{
		if ($this->_meta === null) {
			$this->_meta = get_post_meta($this->getId(), MWS_PRODUCT_META_KEY)[0] ?? [];
			$this->loadProperties_Derived();
		}

		return $this->_meta;
	}

	abstract protected function loadProperties_Derived(): void;

	public function getOrderedCount(): int
	{
		return (int) get_post_meta($this->getId(), MWS_PRODUCT_META_KEY_ORDERED_COUNT, true);
	}

	public function getCodes(): MwsProductCodes
	{
		if (!$this->_codes) {
			$this->_codes = new MwsProductCodes($this->_meta['codes'] ?? []);
		}

		return $this->_codes;
	}

	public function getEditUrl(): string
	{
		return $this->isVariant() ? mwSetting()->getObject(MWS_PRODUCT_SLUG)->getEditUrl($this->getParentId()) : mwSetting()->getObject(MWS_PRODUCT_SLUG)->getEditUrl($this->getId());
	}

	public function hasVariants(): bool
	{
		return $this->getStructure() === MwsProductStructureType::Variants;
	}

	public function isVariant(): bool
	{
		return $this->getPostType() === MWS_VARIANT_SLUG;
	}

	private function showTimer(): bool
	{
		return $this->_meta['price_sale_enabled_till_show'] ?? false;
	}

	abstract public function isShippingRequired(): bool;

	abstract public function getStructure(): string;

	/**
	 * Effective price. This is either full price or discounted price, if discount is active.
	 */
	abstract public function getPrice(): MwsPrice;

	abstract public function isDiscountedNow(): bool;

	/**
	 * Full price without discount.
	 */
	abstract public function getPriceFull(): MwsPrice;

	abstract public function getType(): string;

	abstract public function isStockEnabled(): bool;

	/**
	 * Can be product ordered when there are no goods in stock?
	 */
	abstract public function stockAllowBackorders(): bool;

	/** @return MwsPropertyValue[] */
	abstract public function getProperties(): array;

	abstract public function getDetailUrl(): string;

	public function isDiscountDisabled(): bool
	{
		return isset($this->_meta['disabled_discount']);
	}

	public function getDiscountPercentage(): int
	{
		$fullPrice = $this->getPriceFull();
		$price = $this->getPrice();
		if ($fullPrice && $price) {
			$amountFullPrice = $fullPrice->getPriceVatIncluded();
			$amountSalePrice = $price->getPriceVatIncluded();
			$discount = $amountFullPrice - $amountSalePrice;
			if ($discount < 0 || $amountFullPrice <= 0) {
				return 0;
			}

			return round($discount / ($amountFullPrice / 100), 0);
		}

		return 0;
	}

	/**
	 * Get VAT id associated with the product. When no vat id is assigned null is returned
	 */
	public function getVatId(): ?int
	{
		return isset($this->_meta['vat_id']) ? (int) $this->_meta['vat_id'] : null;
	}

	public function getVatPercentage(): ?int
	{
		$vatId = $this->getVatId();

		return $vatId !== null ? MWS()->getVATs()->getValueById($vatId, false) : null;
	}

	/**
	 * Active selling restriction
	 */
	abstract public function getSellRestriction(): string;

	/**
	 * Datetime in Unix epoch timestamp from which the product is available to be sold (inclusive).
	 */
	abstract public function getSellEnabledFrom(): ?int;

	/**
	 * Datetime in Unix epoch timestamp until which the product is available to be sold (exclusive)
	 */
	abstract public function getSellEnabledTill(): ?int;

	/**
	 * Active sale price type
	 */
	abstract public function getSalePriceType(): string;

	/**
	 * Datetime in Unix epoch timestamp from which the sale price is enabled (inclusive).
	 */
	abstract public function getSalePriceEnabledFrom(): ?int;

	/**
	 * Datetime in Unix epoch timestamp until which the sale price is enabled (exclusive).
	 */
	abstract public function getSalePriceEnabledTill(): ?int;

	abstract public function hideComments(): bool;

	abstract public function showSimilar(): bool;

	abstract public function getAutomations(): array;

	public function getSimilarProducts(): array
	{
		return $this->_meta['similar_products'] ?? [];
	}

	public function setSimilarProducts(array $similarProducts): void
	{
		$this->_meta['similar_products'] = $similarProducts;
		update_post_meta($this->getId(), MWS_PRODUCT_META_KEY, $this->_meta);
	}

	public function getSimilarProductsShowType(): string
	{
		return $this->_meta['show_type_similar_products'] ?? '';
	}

	abstract public function showSocial(): bool;

	abstract public function getWeight(): ?float;

	abstract public function hideInListings(): bool;

	abstract public function getBrand(): ?string;

	abstract public function addConversionCodes(): void;

	/**
	 * Product image gallery. It is lazy loaded upon first galery request
	 */
	public function getGallery(): array
	{
		if (!$this->_gallery) {
			$gallery = get_post_meta($this->getId(), MWS_PRODUCT_META_KEY_GALLERY, true);
			$this->_gallery = isset($gallery['gallery']) && $gallery['gallery'] ? $gallery['gallery'] : [];
		}

		return $this->_gallery;
	}

	/**
	 * Increment number of ordered items. Value is stored within own metavalue.
	 */
	public function addOrderedCount(int $add = 1): void
	{
		update_post_meta($this->getId(), MWS_PRODUCT_META_KEY_ORDERED_COUNT, $this->getOrderedCount() + $add);
	}

	/**
	 * @TODO refactor
	 * Format all prices into one block, wrapped into div with CSS optionally. If sale price is active, than also
	 * full price is included.
	 * @param array $hideFields List of fields to be hidden. Possible values are <code>'salePrice', 'salePercentage',
	 * 'vatIncluded', 'vatExcluded'</code>.
	 * @param null|string $divCSS If not null then result will be wrapped within DIV element and value of this parameter
	 *                            will be used as value of element's CSS "class" attribute.
	 * @return string
	 */
	public function htmlPriceSaleFull($divCSS = null, $amount = 1, $hideFields = [], $priceCSS = null): string
	{
		$shouldShow = function ($field) use ($hideFields) {
			return !(is_array($hideFields) && in_array($field, $hideFields));
		};
		$beforeText = '';
		$res = '';
		$price = $this->getPrice();
		if ($this->getStructure() === MwsProductStructureType::Variants) {
			if ($shouldShow('salePrice') || $shouldShow('vatIncluded') || $shouldShow('vatExcluded') || $shouldShow('salePercentage')) {
				if ($price) {
					if ($this instanceof MwsProductRoot && !$this->variantPricesAreEqual()) {
						$beforeText = _x('od', 'Prepended text when price is counted as lowest price from product variants.', 'mwshop') . ' ';
					}
					if ($shouldShow('salePercentage') && $this->getDiscountPercentage() > 0) {
						$res .= ' <span class="mws_price_sale_percentage">-' . $this->getDiscountPercentage() . ' %</span>';
					}
				} else {
					$res .= _x('(neurčeno)', 'Text used when a price for variant product is not present.', 'mwshop');
				}
			}
		}
		if ($price) {
			$unit = MWS()->getDefaultCurrency();
			if (($shouldShow('salePrice') || $shouldShow('salePercentage'))
				&& $this->isDiscountedNow() && $this->getPriceFull()->getPriceVatIncluded() > 0
			) {
				if ($shouldShow('saleDuration') && $this->showTimer() &&
					!in_array($this->getSalePriceType(), [MwsSalePriceType::Continuous, MwsSalePriceType::EnabledFrom], true)
				) {
					$res .= '<div class="mws_discounted_countdown_container">
								<div class="mws_discounted_countdown">
									<strong>' . __('Sleva končí za:', 'mwshop') . '</strong>
									<span class="mws_discounted_countdown_fields">
										<span class="mws_discounted_countdown_days">
											<span class="mws_discounted_countdown_days_digits"></span>
											<span class="mws_discounted_countdown_days_text">
												<span class="mws_discounted_countdown_day_text">' . __('den', 'mwshop') . '</span>
												<span class="mws_discounted_countdown_few_days_text">' . __('dny', 'mwshop') . '</span>
												<span class="mws_discounted_countdown_many_days_text">' . __('dní', 'mwshop') . '</span>
											</span>
										</span>
										<span class="mws_discounted_countdown_hours">
											<span class="mws_discounted_countdown_hours_digits"></span>
											<span class="mws_discounted_countdown_hours_text">
												<span class="mws_discounted_countdown_hour_text">' . __('hodinu', 'mwshop') . '</span>
												<span class="mws_discounted_countdown_few_hours_text">' . __('hodiny', 'mwshop') . '</span>
												<span class="mws_discounted_countdown_many_hours_text">' . __('hodin', 'mwshop') . '</span>
											</span>
										</span>
										<span class="mws_discounted_countdown_minutes">
											<span class="mws_discounted_countdown_minutes_digits"></span>
											<span class="mws_discounted_countdown_minutes_text">
												<span class="mws_discounted_countdown_minute_text">' . __('minutu', 'mwshop') . '</span>
												<span class="mws_discounted_countdown_few_minutes_text">' . __('minuty', 'mwshop') . '</span>
												<span class="mws_discounted_countdown_many_minutes_text">' . __('minut', 'mwshop') . '</span>
											</span>
										</span>
										<span class="mws_discounted_countdown_seconds">
											<span class="mws_discounted_countdown_seconds_digits"></span>
											<span class="mws_discounted_countdown_seconds_text">
												<span class="mws_discounted_countdown_second_text">' . __('sekundu', 'mwshop') . '</span>
												<span class="mws_discounted_countdown_few_seconds_text">' . __('sekundy', 'mwshop') . '</span>
												<span class="mws_discounted_countdown_many_seconds_text">' . __('sekund', 'mwshop') . '</span>
											</span>
										</span>
									</span>
								</div>
							</div>';

					$enabled_till = $this->getSalePriceEnabledTill();
					$diff = $enabled_till - current_time('timestamp');

					$res .= '<script>' .
							'jQuery(function(){' .
							'mw_discount_countdown(' . $diff . ')' .
							'});' .
							'</script>';
				}

				$res .= '<div class="mws_price_sale">';
				if ($shouldShow('salePrice')) {
					$res .= htmlPriceSimple(
						$amount * $this->getPriceFull()->getPriceVatIncluded(),
						$unit,
						false,
						'mws_price_sale_vatincluded'
					);

					if ($shouldShow('discount')) {
						$discount = $this->getPriceFull()->getPriceVatIncluded() - $this->getPrice()->getPriceVatIncluded();
						$res .= $shouldShow('discountSave') && $discount > 0.0 ? '<span class="mws_discounted_info">, ' . __('ušetříte', 'mwshop') . ' ' . $discount . ' ' . $unit . '</span>' : '';
					}
				}
				if ($shouldShow('salePercentage') && $this->getDiscountPercentage() > 0) {
					$res .= ' <span class="mws_price_sale_percentage">-' . $this->getDiscountPercentage() . '%</span>';
				}
				$res .= '</div>';
			}

			$saleFullPrice = $this->getPriceForSaleFull();

			if ($shouldShow('vatIncluded')) {
				$res .= $saleFullPrice->htmlPriceVatIncluded($amount, true, $priceCSS, $beforeText);
			}

			if ($shouldShow('vatExcluded')) {
				$res .= $saleFullPrice->htmlPriceVatExcluded($amount);
			}
		}
		if ($divCSS !== null && !empty($res)) {
			$res = '<div class="' . $divCSS . '">' . $res . '</div>';
		}

		return $res;
	}

	/** @internal */
	protected function getPriceForSaleFull(): MwsPrice
	{
		return $this->getPrice();
	}

	/**
	 * Update stock count using one of three methods. Update is done directly in the database.
	 *
	 * @param int $count New count or difference of count.
	 * @param string|MwsStockUpdate $method Method, how the stock count should be updated.
	 * @return bool Returns true when stock was updated as requested. False indicates error.
	 * When stock is not enabled od trying to update product type that does not support stock directly, then false is returned.
	 */
	public function updateStockCount(int $count, string $method = MwsStockUpdate::Set, bool $force = false): bool
	{
		if (!$force && !$this->isStockEnabled()) {
			return true;
		}
		if (!$force && $this->getStructure() === MwsProductStructureType::Variants) {
			return true;
		}

		global $wpdb;
		// Ensure key exists
		add_post_meta($this->getId(), MWS_OPTION_STOCKCOUNT, 0, true);
		// Update stock in DB directly
		switch ($method) {
			case MwsStockUpdate::Inc:
				$res = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = meta_value + %d WHERE post_id = %d AND meta_key='" . MWS_OPTION_STOCKCOUNT . "'", $count, $this->getId()));
				$msg = _nx(
					'Stav skladu produktu %s zvýšen o %d kus.',
					'Stav skladu produktu %s zvýšen o %d kusů.',
					$count,
					'Shop log message when stock count has been incremented.',
					'mwshop'
				);

				break;
			case MwsStockUpdate::Dec:
				$res = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = meta_value - %d WHERE post_id = %d AND meta_key='" . MWS_OPTION_STOCKCOUNT . "'", $count, $this->getId()));
				$msg = _nx(
					'Stav skladu produktu %s snížen o %d kus.',
					'Stav skladu produktu %s snížen o %d kusů.',
					$count,
					'Shop log message when stock count has been decremented.',
					'mwshop'
				);

				break;
			default:
				$res = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %d WHERE post_id = %d AND meta_key='" . MWS_OPTION_STOCKCOUNT . "'", $count, $this->getId()));
				$msg = _nx(
					'Stav skladu produktu %s nastaven na %d kus.',
					'Stav skladu produktu %s nastaven na %d kusů.',
					$count,
					'Shop log message when stock count has been set.',
					'mwshop'
				);

				break;
		}
		if (is_int($res)) {
			mwshoplog(
				sprintf($msg, "'{$this->getName()}' [{$this->getId()}]", $count),
				MWLL_INFO,
				'stock'
			);
			$res = true;
		} else {
			mwshoplog(
				sprintf(__('Chyba při aktualizaci stavu skladu produktu %s.', 'mwshop'), "'{$this->getName()}' [{$this->getId()}]"),
				MWLL_ERROR,
				'stock'
			);
			$res = false;
		}

		// Clear caches
		wp_cache_delete($this->getId(), 'post_meta');
		$this->_stockCount = null;

		return $res;
	}

	/**
	 * Is is possible to use discounted price now? This checks only sale price type and dates.
	 *
	 * @return bool Returns true when the discounted price is active.
	 */
	public function canDiscountNow(): bool
	{
		// Is sale price enabled?
		$salePriceType = $this->getSalePriceType();

		$curTime = current_time('timestamp');

		if ($salePriceType === MwsSalePriceType::Continuous) {
			return true;
		}

		if ($salePriceType === MwsSalePriceType::EnabledFrom) {
			if ($curTime <= $this->getSalePriceEnabledFrom()) {
				return false;
			}
		} elseif ($salePriceType === MwsSalePriceType::EnabledTill) {
			if ($this->getSalePriceEnabledTill() < $curTime) {
				return false;
			}
		} elseif ($salePriceType === MwsSalePriceType::EnabledInterval) {
			if ($curTime <= $this->getSalePriceEnabledFrom()) {
				return false;
			}

			if ($this->getSalePriceEnabledTill() < $curTime) {
				return false;
			}
		} else {
			return false;
		}

		return true;
	}


	/**
	 * Is selling disabled in current time?
	 *
	 * @return string|MwsProductAvailabilityStatus|false Returns one of UNAVAILABLE status if selling restriction is active.
	 *                                                   If no restriction is applied then false is returned.
	 */
	private function getSellingStatus(): ?int
	{
		$curTime = current_time('timestamp');
		// Are selling restrictions enabled?
		$sellRestriction = $this->getSellRestriction();
		if ($sellRestriction === MwsSellRestriction::FullDisable) {
			return MwsProductAvailabilityStatus::Unavailable_Disabled;
		}

		if ($sellRestriction === MwsSellRestriction::EnabledFrom) {
			if ($curTime <= $this->getSellEnabledFrom()) {
				return MwsProductAvailabilityStatus::Unavailable_NotStartedYet;
			}
		} elseif ($sellRestriction === MwsSellRestriction::EnabledTill) {
			if ($this->getSellEnabledTill() < $curTime) {
				return MwsProductAvailabilityStatus::Unavailable_AlreadyFinished;
			}
		} elseif ($sellRestriction === MwsSellRestriction::EnabledInterval) {
			if ($curTime <= $this->getSellEnabledFrom()) {
				return MwsProductAvailabilityStatus::Unavailable_NotStartedYet;
			}

			if ($this->getSellEnabledTill() < $curTime) {
				return MwsProductAvailabilityStatus::Unavailable_AlreadyFinished;
			}
		}

		return null;
	}

	/**
	 * Get CSS for selling status - enabled or disabled.
	 *
	 * @return string
	 */
	public function getSellingCSS(): string
	{
		return $this->getSellingStatus() ? 'mws_selling_disabled' : 'mws_selling_enabled';
	}

	/**
	 * Get the status of availability to sell the product. Status is further differentiated by the reason for that status.
	 * Positive value means AVAILABLE, negative values UNAVAILABLE.
	 *
	 * @param int $count Number of items that should be present, default 1.
	 * @param bool $skipSellRestriction If set to true than availability is evaluated without taking selling restriction
	 *                                  into account.
	 * @return int Value of enumeration <a href='psi_element://MwsProductAvailabilityStatus'>MwsProductAvailabilityStatus</a>.
	 */
	public function getAvailabilityStatus(int $count = 1, bool $skipSellRestriction = false): int
	{
		//TODO Add other cases, like variants etc.

		if (!$skipSellRestriction) {
			$sellingStatus = $this->getSellingStatus();
			if ($sellingStatus) {
				return $sellingStatus;
			}
		}

		if ($this->isStockEnabled()) {
			// Stock enabled
			if ($this->getStockCount() >= $count) { // @TODO what if count is 0?
				return MwsProductAvailabilityStatus::Available_InStock;
			}

			if ($this->stockAllowBackorders()) {
				return MwsProductAvailabilityStatus::Available_StockBackorder;
			}

			return MwsProductAvailabilityStatus::Unavailable_OutOfStock;
		}

		return MwsProductAvailabilityStatus::Available_StockDisabled;
	}

	/**
	 * Translate availability status into CSS for passed status or 1 items if default is used.
	 */
	public function getAvailabilityCSS(?int $availabilityStatus = null): string
	{
		return MwsProductAvailabilityStatus::getCSS($availabilityStatus ?: $this->getAvailabilityStatus(1));
	}

	/**
	 * Get text for BUY BUTTON depending on product availability status
	 */
	public function getBuyButtonText(?int $availabilityStatus = null): string
	{
		$availabilityStatus = $availabilityStatus ?: $this->getAvailabilityStatus(1);
		if ($this->canBuy($availabilityStatus)) {
			return _x('Koupit', 'Buy buttontext - product can be bought', 'mwshop');
		}

		switch ($availabilityStatus) {
			case MwsProductAvailabilityStatus::Unavailable_StockDisabled:
				return _x('Vyprodáno', 'Product is unavailable, stock is disabled.', 'mwshop');
			case MwsProductAvailabilityStatus::Unavailable_OutOfStock:
				return _x('Vyprodáno', 'Product is unavailable, stock is enabled.', 'mwshop');
			case MwsProductAvailabilityStatus::Unavailable_Disabled:
				return _x('Není v prodeji', 'Product is unavailable, manually disabled selling.', 'mwshop');
			case MwsProductAvailabilityStatus::Unavailable_NotStartedYet:
				$res = _x('K dispozici od', 'Product is unavailable, future sell.', 'mwshop');
				$res .= ' ' . date('d.m.Y', $this->getSellEnabledFrom());
				$time = date('H:i', $this->getSellEnabledFrom());
				if ($time != '00:00') {
					$res .= ' ' . $time;
				}

				return $res;
			case MwsProductAvailabilityStatus::Unavailable_AlreadyFinished:
				return _x('Ukončeno', 'Product is unavailable, past sell.', 'mwshop');
		}

		return _x('Nedostupné', 'Buy buttontext - Product availability status is not recognized.', 'mwshop');
	}

	/**
	 * Translate availability status into user friendly localized string for passed status or 1 item if default is used.
	 */
	public function getAvailabilityMessage(?int $availabilityStatus = null): string
	{
		$availabilityStatus = $availabilityStatus ?: $this->getAvailabilityStatus(1);
		switch ($availabilityStatus) {
			case MwsProductAvailabilityStatus::Available_StockDisabled:
				return __('Dostupné', 'mwshop');
			case MwsProductAvailabilityStatus::Available_InStock:
				$isElectronic = MwsProductType::isElectronic($this->getType());
				$count = $this->getStockCount();

				if ($isElectronic) {
					if ($count > MioShop::StockLimit_Plenty) {
						return __('Dostupný', 'mwshop');
					}

					if ($count > 1 && $count < 5) {
						return sprintf(__('Dostupné %s ks', 'mwshop'), $count);
					}

					return sprintf(
						_n('Dostupný 1 ks', 'Dostupných %s ks', $count, 'mwshop'),
						$count
					);
				}

				if ($count > MioShop::StockLimit_Plenty) {
					return __('Skladem', 'mwshop');
				}

				return __('Skladem', 'mwshop') . ' ' . sprintf(
					_n('1 ks', '%s ks', $count, 'mwshop'),
					$count
				);
			case MwsProductAvailabilityStatus::Available_StockBackorder:
				return _x('Na objednávku', 'Product is available using backorder.', 'mwshop');
			case MwsProductAvailabilityStatus::Unavailable_StockDisabled:
				return _x('Nedostupné', 'Product is unavailable, stock is disabled.', 'mwshop');
			case MwsProductAvailabilityStatus::Unavailable_OutOfStock:
				return _x('Vyprodáno', 'Product is unavailable, stock is enabled.', 'mwshop');
			case MwsProductAvailabilityStatus::Unavailable_Disabled:
				return _x('Nedostupné', 'Product is unavailable, manually disabled selling.', 'mwshop');
			case MwsProductAvailabilityStatus::Unavailable_NotStartedYet:
				$res = _x('K dispozici od', 'Product is unavailable, future sell.', 'mwshop');
				$res .= ' ' . date('d.m.Y', $this->getSellEnabledFrom());
				$time = date('H:i', $this->getSellEnabledFrom());
				if ($time != '00:00') {
					$res .= ' ' . $time;
				}

				return $res;
			case MwsProductAvailabilityStatus::Unavailable_AlreadyFinished:
				return _x('Prodej ukončen', 'Product is unavailable, past sell.', 'mwshop');
		}

		return _x('Dostupnost neznámá', 'Product availability status is not recognized.', 'mwshop');
	}

	/**
	 * Format HTML DIV element with availability status
	 *
	 * @param int $availabilityStatus Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                                Default value will count real value for 1 item.
	 * @return string
	 */
	public function htmlAvailabilityMessage(?int $availabilityStatus = null): string
	{
		if (!isset(MWS()->visual_setting['hide_availability'])) {
			return '<div class="mws_product_availability">' . esc_html($this->getAvailabilityMessage($availabilityStatus ?: $this->getAvailabilityStatus(1))) . '</div>';
		}

		return '';
	}

	/**
	 * Get error message for availability like "Product sold out.", "Only 2 items in stock." when a count of items is requested.
	 * If availability of count is OK then empty string is returned.
	 *
	 * @param int $count Count of items whose error status message should be evaluated.
	 * @param int $status Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                    for specified count. Default value will count real value for $count of items.
	 * @return string
	 */
	public function getAvailabilityError(int $count, ?int $availabilityStatus = null): string
	{
		$availabilityStatus = $availabilityStatus ?: $this->getAvailabilityStatus($count);
		if (!$this->canBuy($availabilityStatus)) {
			// Product can not be bought in specified amount.
			if ($this->isStockEnabled()) {
				$stockCount = $this->getStockCount();
				if ($stockCount < 1) {
					return __('Produkt byl zcela vyprodán.', 'mwshop');
				}

				return sprintf(_nx(
					'V nabídce je poslední %d kus.',
					'V nabídce je posledních %d kusů.',
					$stockCount,
					'Cart print count error message when product is out of stock.',
					'mwshop'
				), $stockCount);
			}

			return __('Produkt není v prodeji.', 'mwshop');
		}

		return '';
	}

	/**
	 * Can be the product bought in specified amount?
	 */
	public function canBuy(?int $availabilityStatus = null): bool
	{
		return ($availabilityStatus ?: $this->getAvailabilityStatus(1)) > 0;
	}

	/**
	 * Can be the product bought in specified amount?
	 */
	public function canBuyCount(int $count = 1): bool
	{
		return $this->canBuy($this->getAvailabilityStatus($count));
	}

	/**
	 * Get current stock count without caching.
	 */
	public function getStockCount(): int
	{
		if ($this->_stockCount === null) {
			return $this->_stockCount = (int) get_post_meta($this->getId(), MWS_OPTION_STOCKCOUNT, true);
		}

		return $this->_stockCount;
	}

	/**
	 * Returns true if product is visible in catalog. This depends on global settings and availability of product.
	 *
	 * @param int $availabilityStatus Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                                Default value will count real status value for 1 item.
	 */
	public function isListVisible(?int $availabilityStatus = null): bool
	{
		if ($this->hideInListings()) {
			return false;
		}

		return !in_array($availabilityStatus ?: $this->getAvailabilityStatus(), MWS()->getHiddenAvailabilityStatusesFor('product'));
	}

	/**
	 * Get all defined published root products, that is single and variant product but without variations.
	 *
	 * @return MwsProduct[]
	 */
	public static function getAll(array $args = [], $paged = false): array
	{
		$query_args = array_merge(
			[
				'post_type' => MWS_PRODUCT_SLUG,
				'posts_per_page' => -1,
				'post_status' => 'publish',
			], //a must
			$args
		);

		return self::getQuery($query_args, $paged);
	}

	/**
	 * Get list of root products that are not visible according to global settings and per product settings.
	 *
	 * @return MwsProduct[]
	 */
	public static function getInvisibleProducts(bool $onlyIds = false): array
	{
		$invisible = [];
		foreach (self::getAll() as $product) {
			if (!$product->isListVisible()) {
				$invisible[] = $onlyIds ? $product->getId() : $product;
			}
		}

		return $invisible;
	}

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 */
	public static function createNew(WP_Post $post, bool $forceUpdateCache = false): ?self
	{
		$postType = get_post_type($post);
		if (!in_array($postType, [MWS_PRODUCT_SLUG, MWS_VARIANT_SLUG])) {
			throw new MwsException('Passed post type is not of product type.');
		}

		if ($post->post_status === 'auto-draft') {
			// Newly created post
			mwshoplog('Newly created unsaved PRODUCT post: ' . json_encode($post, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG);

			return null;
		}

		//Is created already or must be updated in cache?
		if ($forceUpdateCache || !($obj = MwObjectCache::get(MwsProduct::class, $post->ID))) { // @TODO check cache class
			if ($postType === MWS_VARIANT_SLUG) {
				if (self::class === MwsProductRoot::class) {
					throw new MwsException('Invalid class.');
				}
				$rootProduct = MwsProductRoot::getOneById($post->post_parent);
				if (!$rootProduct) {
					throw new MwsException('Missing root product.');
				}
				$obj = new MwsProductVariant($post, $rootProduct);
			} else {
				if (self::class === MwsProductVariant::class) {
					throw new MwsException('Invalid class.');
				}
				$obj = new MwsProductRoot($post);
			}
			// @TODO add MwsProduct class to cache key
			MwObjectCache::add($obj, $obj->getId());
		}

		return $obj;
	}

	public function toAnalyticsArray(int $quantity = 1): array
	{
		$priceFull = $this->getPriceFull();
		$price = $this->getPrice();

		$arr = [
			'item_id' => $this->getId(),
			'item_name' => $this->getName(),
//			'coupon' => '',
//			'discount' => round($priceFull->getPriceVatExcluded() - $price->getPriceVatExcluded(),2),
//			'affiliation' => '',
//			'item_brand' => '',
//			'item_category' => '',
//			'item_variant' => 'black',
			'price' => $price->getPriceVatExcluded(), //$priceFull->getPriceVatExcluded(),
			'currency' => $priceFull->getCurrency(),
			'quantity' => $quantity,
		];

		if ($this->isVariant() && $this instanceof MwsProductVariant) {
			$arr['item_name'] = $this->getProduct()->getName();
			$arr['item_variant'] = $this->composeVariantDesc();
		}

		return $arr;
	}

	/**
	 * This function will return text if is not possible to buy a product.
	 *
	 * @return string
	 */
	public function getSoldOutText(): string
	{
		$status = $this->getAvailabilityStatus();

		if (!$this->canBuy($status)) {
			switch ($status) {
				case MwsProductAvailabilityStatus::Unavailable_OutOfStock:
					return _x('Produkt je vyprodaný', 'Product is unavailable, stock is enabled.', 'mwshop');
				case MwsProductAvailabilityStatus::Unavailable_Disabled:
					return _x('Produkt nelze koupit', 'Product is unavailable, manually disabled selling.', 'mwshop');
				case MwsProductAvailabilityStatus::Unavailable_NotStartedYet:
					$res = _x('Produkt nelze koupit. V prodeji bude od ', 'Product is unavailable, future sell.', 'mwshop');
					$res .= ' ' . date('d.m.Y', $this->getSellEnabledFrom());
					$time = date('H:i', $this->getSellEnabledFrom());
					if ($time != '00:00') {
						$res .= ' ' . $time;
					}

					return $res;
				case MwsProductAvailabilityStatus::Unavailable_AlreadyFinished:
					return _x('Produkt nelze koupit', 'Product is unavailable, past sell.', 'mwshop');
			}

			return _x('Produkt nelze koupit', 'The product can not be purchased.', 'mwshop');
		}

		return '';
	}

}

/** Update methods of a stock. */
class MwsStockUpdate extends MwsBasicEnum
{
	/** Increment the stock count */
	const Inc = 'inc';
	/** Decrement stock count */
	const Dec = 'dec';
	/** Set stock count */
	const Set = 'set';
}

class MwsProductAvailabilityStatus extends MwsBasicEnum
{
	const Available_StockDisabled = 1;
	const Available_InStock = 2;
	const Available_StockBackorder = 3;

	const Unavailable_StockDisabled = -1;
	const Unavailable_OutOfStock = -2;
	const Unavailable_Disabled = -3;
	const Unavailable_NotStartedYet = -10;
	const Unavailable_AlreadyFinished = -11;

	private static function getCSSMatrix()
	{
		return [
			self::Available_StockDisabled => 'mws_available mws_available_stockdisabled',
			self::Available_InStock => 'mws_available mws_available_instock',
			self::Available_StockBackorder => 'mws_available mws_available_stockbackorder',

			self::Unavailable_StockDisabled => 'mws_unavailable mws_unavailable_stockdisabled',
			self::Unavailable_OutOfStock => 'mws_unavailable mws_unavailable_outofstock',
			self::Unavailable_Disabled => 'mws_unavailable mws_unavailable_disabled',
			self::Unavailable_NotStartedYet => 'mws_unavailable mws_unavailable_futuresell',
			self::Unavailable_AlreadyFinished => 'mws_unavailable mws_unavailable_pastsell',
		];
	}

	/**
	 * Get array of all CSS classes without starting dot.
	 */
	public static function getAllCSSArray(): array
	{
		return array_unique(explode(' ', implode(' ', static::getCSSMatrix())));
	}

	/**
	 * Convert status into CSS classes.
	 */
	public static function getCSS(int $status): string
	{
		return static::getCSSMatrix()[$status] ?? '';
	}

}
