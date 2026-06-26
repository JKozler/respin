<?php

define('MWS_DISCOUNT_CODE_META_KEY', 'discount_code');

define('MWS_DISCOUNT_CODE_META_KEY_USED_COUNT', 'used_count');

// @TODO migrate used count from FAPI
class MwsDiscountCode extends mwPost
{

	public const VALIDATION_VALID = 1;

	public const VALIDATION_INVALID = 0;

	public const VALIDATION_DISALLOWED_PRODUCT = -1;

	public const VALIDATION_LOW_PRICE = -2;

	public const VALIDATION_LOW_PRICE_DISALLOWED_PRODUCT = -3;

	/** @var null|array Settings of instance. */
	private $_meta = null;

	/** @var string Internal storage of the price. */
	private $_code;

	/** @var string */
	private $_type;

	/** @var string */
	private $_value;

	/** @var MwsPrice */
	private $_minPrice;

	/** @var string */
	private $_expirationType;

	/** @var int */
	private $_maxCount = 0;

	/** @var \DateTimeInterface|null */
	private $_expirationFrom = null;

	/** @var \DateTimeInterface|null */
	private $_expirationTo = null;

	function __construct(WP_Post $post)
	{
		parent::__construct($post);
		$this->loadMeta();
		// Properties
		$this->_code = $this->_meta['code'] ?? '';
		$this->_type = $this->_meta['type'] ?? '';
		$this->_value = (float) ($this->_meta['value'] ?? 0);
		$this->_minPrice = new MwsPrice((float) ($this->_meta['min_price'] ?? 0));
		$this->_expirationType = $this->_meta['expiration_type'] ?? MwsDiscountCodeExpirationType::None;
		if ($this->_expirationType === MwsDiscountCodeExpirationType::DateRange) {
			$this->_expirationFrom = $this->loadDateFromMeta('expiration_from');
			$this->_expirationTo = $this->loadDateFromMeta('expiration_to')->setTime(23, 59, 59);
		} elseif ($this->_expirationType === MwsDiscountCodeExpirationType::Count) {
			$this->_maxCount = (int) ($this->_meta['max_count'] ?? 0);
		}
	}

	private function equalsCode(string $code): bool
	{
		return strtolower($this->_code) === strtolower($code);
	}

	/**
	 * Specific codes by state
	 * 1 - valid
	 * 0 - not valid
	 * -1 - no allowed products
	 * -2 - small total price with not allowed product
	 * -3 - small total price
	 */
	public function isValid(MwsCart $cart): int
	{
		if ($this->getExpirationType() === MwsDiscountCodeExpirationType::Count) {
			if ($this->getMaxCount() <= $this->getUsedCount()) {
				return static::VALIDATION_INVALID;
			}
		} elseif ($this->getExpirationType() === MwsDiscountCodeExpirationType::DateRange) {
			$now = new DateTimeImmutable();
			if ($this->getExpirationFrom() && $this->getExpirationFrom() > $now) {
				return static::VALIDATION_INVALID;
			}
			if ($this->getExpirationTo() && $this->getExpirationTo() < $now) {
				return static::VALIDATION_INVALID;
			}
		}

		$totalAllowPrice = null;
		$totalAllowCount = 0;
		$totalCount = 0;
		foreach ($cart->getItems()->getAll() as $cartItem) {
			if (!$cartItem->getProduct()->isDiscountDisabled()) {
				$price = $cartItem->getStoredShopPrice()->multiply($cartItem->getCount());
				$totalAllowPrice = $totalAllowPrice ? $totalAllowPrice->add($price) : $price;
				$totalAllowCount++;
			}
			$totalCount++;
		}

		// no discount allowed product
		if (!$totalAllowCount) {
			return static::VALIDATION_DISALLOWED_PRODUCT;
		}

		// total allowed price is smaller then min discount price
		if ($this->getMinPrice()->sub($totalAllowPrice)->getPriceVatIncluded() > 0) {
			return $totalCount > $totalAllowCount ? static::VALIDATION_LOW_PRICE_DISALLOWED_PRODUCT : static::VALIDATION_LOW_PRICE;
		}

		return static::VALIDATION_VALID;
	}

	public function getValidationError(int $validationCode, MwsCart $cart): ?string
	{
		if ($validationCode === static::VALIDATION_INVALID) {
			return __('Slevový kód již není platný a nelze ho uplatnit.', 'mwshop');
		}

		if ($validationCode === static::VALIDATION_DISALLOWED_PRODUCT) {
			return __('V košíku není zboží na které lze uplatnit slevový kód.', 'mwshop');
		}

		if ($validationCode === static::VALIDATION_LOW_PRICE) {
			$minPrice = $this->getMinPrice();

			return __('Slevový kód nelze uplatnit. Slevu lze uplatnit pouze když objednávka přesáhne', 'mwshop') . ' ' . $minPrice->asCurrency($cart->getCurrency())->formatPrice() . ' ' . MwsCurrencyEnum::getSymbol($cart->getCurrency()) . '. ';
		}

		if ($validationCode === static::VALIDATION_LOW_PRICE_DISALLOWED_PRODUCT) {
			$minPrice = $this->getMinPrice();
			$error = __('Slevový kód nelze uplatnit. Slevu lze uplatnit pouze když je v košíku zboží, na které lze uplatnit slevu, za více než ', 'mwshop') . ' ' . $minPrice->asCurrency($cart->getCurrency())->formatPrice() . ' ' . MwsCurrencyEnum::getSymbol($cart->getCurrency()) . '. ';

			$nonAllowedProducts = [];
			foreach ($cart->getItems()->getAll() as $cartItem) {
				if ($cartItem->getProduct()->isDiscountDisabled()) {
					$nonAllowedProducts[] = $cartItem->getProduct()->getName();
				}
			}
			$error .= __('Slevu nelze uplatnit na: ', 'mwshop') . ' <strong>' . implode('</strong>, <strong>', $nonAllowedProducts) . '</strong>.';

			return $error;
		}

		return null;
	}

	public function printValue(?string $currency = null): string
	{
		if ($this->_type === MwsDiscountCodeType::Percent) {
			return $this->_value . ' %';
		}

		$price = new MwsPrice($this->getValue());
		if ($currency) {
			$price = $price->asCurrency($currency);
		}

		return htmlPriceSimple($price->getPriceVatIncluded(), MwsCurrencyEnum::getSymbol($price->getCurrency()), false);
	}

	private function loadDateFromMeta($key): ?DateTimeImmutable
	{
		$date = $this->_meta[$key] ?? null;
		if ($date === null) {
			return null;
		}

		return new \DateTimeImmutable($date);
	}

	private function loadMeta()
	{
		if ($this->_meta === null) {
			$this->_meta = get_post_meta($this->getId(), MWS_DISCOUNT_CODE_META_KEY)[0] ?? [];
		}

		return $this->_meta;
	}

	public function getCode(): string
	{
		return $this->_code;
	}

	public function getType(): string
	{
		return $this->_type;
	}

	public function getValue(): float
	{
		return $this->_value;
	}

	public function getMinPrice(): MwsPrice
	{
		return $this->_minPrice;
	}

	public function getExpirationType(): string
	{
		return $this->_expirationType;
	}

	public function getExpirationFrom(): ?DateTimeInterface
	{
		return $this->_expirationFrom;
	}

	public function getExpirationTo(): ?DateTimeInterface
	{
		return $this->_expirationTo;
	}

	public function getMaxCount(): int
	{
		return $this->_maxCount;
	}

	public function getUsedCount(): int
	{
		return (int) get_post_meta($this->getId(), MWS_DISCOUNT_CODE_META_KEY_USED_COUNT, true);
	}

	public function setUsedCount(int $usedCount): void
	{
		update_post_meta($this->getId(), MWS_DISCOUNT_CODE_META_KEY_USED_COUNT, $usedCount);
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'code' => $this->getCode(),
			'type' => $this->getType(),
			'value' => $this->getValue(),
			'min_price' => $this->getMinPrice()->toArray(),
			'expiration_type' => $this->getExpirationType(),
			'expiration_from' => $this->getExpirationFrom(),
			'expiration_to' => $this->getExpirationTo(),
			'max_count' => $this->getMaxCount(),
			'used_count' => $this->getUsedCount(),
			'date-created' => $this->getDateCreated(),
			'is_visible' => $this->isVisible(),
		];
	}

	// @TODO sjednotit getAll a getAllForSetting. Případně používat funkci z classy mwPost

	/**
	 * Get all defined discount codes methods as an array of {@link MwsDiscountCode} instances.
	 *
	 * @return MwsDiscountCode[] array List of {@link MwsDiscountCode} instances.
	 */
	public static function getAll(): array
	{
		$res = [];
		$args = [
			'post_type' => MWS_DISCOUNT_CODE_SLUG,
			'post_status' => 'publish',
			'posts_per_page' => -1,
		];
		$query = new WP_Query($args);
		if ($query->have_posts()) {
			/** @var WP_Post $post */
			foreach ($query->posts as $post) {
				try {
					$res[] = static::createNew($post);
				} catch (Exception $e) {
				}
			}
		}

		return $res;
	}

	/**
	 * Get all defined discount codes methods for ADMIN as an array of {@link MwsDiscountCode} instances.
	 *
	 * @return MwsDiscountCode[] array List of {@link MwsDiscountCode} instances.
	 */
	public static function getAllForSetting($num = -1, $page = 1): array
	{
		$res = [];
		$args = [
			'post_type' => MWS_DISCOUNT_CODE_SLUG,
			'post_status' => 'any',
			'posts_per_page' => $num,
			'paged' => $page,
		];
		$query = new WP_Query($args);
		if ($query->have_posts()) {
			/** @var WP_Post $post */
			foreach ($query->posts as $post) {
				try {
					$res[] = static::createNew($post);
				} catch (Exception $e) {
				}
			}
		}

		return [
			'items' => $res,
			'pages' => $query->max_num_pages,
			'count' => $query->found_posts,
		];
	}

	public static function getOneByCode(string $code): ?self
	{
		foreach (static::getAll() as $discountCode) {
			if ($discountCode->equalsCode($code)) {
				return $discountCode;
			}
		}

		return null;
	}

}
