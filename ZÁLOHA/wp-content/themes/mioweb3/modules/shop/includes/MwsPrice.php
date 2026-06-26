<?php

use Mioweb\Lib\MwPrice;

/**
 * Class MwsPrice is a helper class for price manipulation and output formatting.
 */
class MwsPrice
{

	private $_price;

	private $_vatIncluded;

	private $_vatPercentage;

	private $_currency;

	// forced
	private $_priceVatIncluded = null;

	// forced
	private $_priceVatExcluded = null;

	/** @var array List of cached converted values. */
	private $_currencies = [];

	/**
	 * Creates new priced item.
	 */
	public function __construct(float $price, ?int $vatPercentage = null, ?string $currency = null, bool $vatIncluded = true)
	{
		$this->_price = $price;
		$this->_vatIncluded = $vatIncluded;

		$this->_vatPercentage = $vatPercentage;
		$this->_currency = $currency ? MwsCurrencyEnum::checkedValue($currency, null) : null;
	}

	public function getPriceVatIncluded(): float
	{
		if ($this->_priceVatIncluded !== null) {
			return $this->_priceVatIncluded;
		}

		if ($this->_vatIncluded) {
			return $this->_price;
		}

		return round($this->_price + MwPrice::calculateVatByPriceVatExcluded($this->_price, $this->getVatPercentage()), 2);
	}

	public function getPriceVatExcluded(): float
	{
		if ($this->_priceVatExcluded !== null) {
			return $this->_priceVatExcluded;
		}

		if (!$this->_vatIncluded) {
			return $this->_price;
		}

		return round($this->_price - MwPrice::calculateVatByPriceVatIncluded($this->_price, $this->getVatPercentage()), 2);
	}

	public function getVatAmount(): float
	{
		return round($this->getPriceVatIncluded() - $this->getPriceVatExcluded(), 2);
	}

	public function removeVat(): void
	{
		$this->_price = $this->_priceVatIncluded = $this->getPriceVatExcluded();
		$this->_vatIncluded = true;
		$this->_vatPercentage = 0;
	}

	public function changeVat(int $vatPercentage): void
	{
		$this->_price = $this->getPriceVatIncluded();
		$this->_vatIncluded = true;
		$this->_vatPercentage = $vatPercentage;
		$this->_priceVatIncluded = null;
	}

	/**
	 * Returns effective value of VAT. If VAT is not used, then default VAT value is returned.
	 */
	public function getVatPercentage(): int
	{
		if ($this->_vatPercentage === null) {
			// if not vat rate then use default
			$this->_vatPercentage = MWS()->getVATs()->getValueDefault(false, 0);
		}

		return $this->_vatPercentage;
	}

	public function getCurrency(): string
	{
		if (!$this->_currency) {
			// if not currency then use default
			$this->_currency = MWS()->getDefaultCurrency('key');
		}

		return $this->_currency;
	}

	/**
	 * Format price including VAT.
	 *
	 * @param int $amount Amount of pieces.
	 * @param bool $use0text When true and price is 0 then text "free" is output.
	 * @param string $divCSS Optional CSS text for wrapping DIV element.
	 */
	public function htmlPriceVatIncluded(int $amount = 1, bool $use0text = true, ?string $divCSS = null, string $beforeText = ''): string
	{
		return htmlPriceSimpleIncluded($this->multiply($amount)->getPriceVatIncluded(), MwsCurrencyEnum::getSymbol($this->getCurrency()), $use0text, $divCSS, $beforeText);
	}

	/**
	 * Format price without VAT.
	 */
	public function htmlPriceVatExcluded(int $amount = 1): string
	{
		return htmlPriceSimpleExcluded($this->multiply($amount)->getPriceVatExcluded(), MwsCurrencyEnum::getSymbol($this->getCurrency()), false);
	}

	/**
	 * Format all prices into one block, wrapped into div with CSS optionally.
	 *
	 * @param null|string $divCSS If not null then result will be wrapped within DIV element and value of this parameterwill be used as value of element's CSS "class" attribute.
	 */
	public function htmlPriceFull(?string $divCSS = null, int $amount = 1): string
	{
		$res = '';
		$res .= $this->htmlPriceVatIncluded($amount, true);
		$res .= $this->htmlPriceVatExcluded($amount);
		if ($divCSS !== null && !empty($res)) {
			$res = '<div class="' . $divCSS . '">' . $res . '</div>';
		}

		return $res;
	}

	public function htmlPrice(?string $divCSS = null, int $amount = 1, $noVatFirst = false): string
	{
		$res = '';
		if ($noVatFirst) {
			$res .= htmlPriceSimpleIncluded($this->multiply($amount)->getPriceVatExcluded(), MwsCurrencyEnum::getSymbol($this->getCurrency()));
			$res .= htmlPriceSimple(
				$this->multiply($amount)->getPriceVatIncluded(),
				MwsCurrencyEnum::getSymbol($this->getCurrency()),
				false,
				'mws_price_vatexcluded',
				' ' . __('s&nbsp;DPH', 'mwshop')
			);
		} else {
			$res .= $this->htmlPriceVatIncluded($amount, true);
			$res .= $this->htmlPriceVatExcluded($amount);
		}

		if ($divCSS !== null && !empty($res)) {
			$res = '<div class="' . $divCSS . '">' . $res . '</div>';
		}

		return $res;
	}

	public function formatPrice(): string
	{
		return self::doFormatPrice($this->getPriceVatIncluded());
	}

	public static function doFormatPrice(float $price): string
	{
		$dec = $price != floor($price) ? 2 : 0;
		$formatedPrice = number_format(round($price, 2), $dec, ',', ' ');
		$formatedPrice = str_replace(' ', '&nbsp;', $formatedPrice);

		return $formatedPrice;
	}

	public function asCurrency(string $currency, float $exRate = 0): self
	{
		if ($currency === $this->getCurrency()) {
			return $this;
		}
		if (!isset($this->_currencies[$currency])) {
			$rate = $exRate ?: MWS()->getCurrencyConversionRate($this->getCurrency(), $currency); // @TODO different vat conversion?
			$this->_currencies[$currency] = new MwsPrice(
				round($this->_price * $rate, 2),
				$this->_vatPercentage,
				$currency,
				$this->_vatIncluded
			);
		}

		return $this->_currencies[$currency];
	}

	public function add(self $price): self
	{
		$priceToAdd = $this->_vatIncluded ?
			$price->asCurrency($this->getCurrency())->getPriceVatIncluded() :
			$price->asCurrency($this->getCurrency())->getPriceVatExcluded();

		return new self(
			round($this->_price + $priceToAdd, 2),
			$this->_vatPercentage,
			$this->_currency,
			$this->_vatIncluded
		);
	}

	public function sub(self $price): self
	{
		$priceToSub = $this->_vatIncluded ?
			$price->asCurrency($this->getCurrency())->getPriceVatIncluded() :
			$price->asCurrency($this->getCurrency())->getPriceVatExcluded();

		return new self(
			round($this->_price - $priceToSub, 2),
			$this->_vatPercentage,
			$this->_currency,
			$this->_vatIncluded
		);
	}

	public function multiply(float $multiplier): self
	{
		return new self(
			round($this->_price * $multiplier, 2),
			$this->_vatPercentage,
			$this->_currency,
			$this->_vatIncluded
		);
	}

	public function ceil(int $decimals = 0): self
	{
		return new self(
			ceil($this->_price * pow(10, $decimals)) / pow(10, $decimals),
			$this->_vatPercentage,
			$this->_currency,
			$this->_vatIncluded
		);
	}

	public function roundBy(string $function, int $decimals = 0): self
	{
		return new self(
			$function($this->_price * pow(10, $decimals)) / pow(10, $decimals),
			$this->_vatPercentage,
			$this->_currency,
			$this->_vatIncluded
		);
	}

	public function abs(): self
	{
		return new self(
			abs($this->_price),
			$this->_vatPercentage,
			$this->_currency,
			$this->_vatIncluded
		);
	}

	/**
	 * Get prices as array indexed by property names.
	 */
	public function toArray(): array
	{
		return [
			'priceVatIncluded' => $this->getPriceVatIncluded(),
			'priceVatExcluded' => $this->getPriceVatExcluded(),
			'vatPercentage' => $this->getVatPercentage(),
			'currency' => $this->getCurrency(), // @TODO store conversion rate?
		];
	}

	/**
	 * Get prices as JSON encoded string.
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), JSON_PRESERVE_ZERO_FRACTION | $options);
	}

	public static function createByFields(float $priceVatIncluded, float $priceVatExcluded, int $vatPercentage, string $currency): self
	{
		$price = new self($priceVatIncluded, $vatPercentage, $currency, true);
		$price->_priceVatExcluded = $priceVatExcluded;

		return $price;
	}

	public static function createByArray(array $values): self
	{
		return self::createByFields(
			$values['priceVatIncluded'],
			$values['priceVatExcluded'],
			$values['vatPercentage'],
			$values['currency']
		);
	}

}
