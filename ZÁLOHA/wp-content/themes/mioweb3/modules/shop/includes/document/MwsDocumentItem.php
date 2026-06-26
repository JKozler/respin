<?php

class MwsDocumentItem
{

	private $_name;

	private $_type;

	private $_count;

	private MwsPrice $_price;

	/** @var MwsPrice[] */
	private array $_allPrices;

	private $_codes;

	private $_productId;

	private bool $_ossApplied;

	public function __construct(string $name, string $type, MwsPrice $price, array $allPrices, int $count = 1, ?MwsProductCodes $codes = null, ?int $productId = null, bool $ossApplied = false)
	{
		$this->_name = $name;
		$this->_type = $type;
		$this->_price = $price;
		$this->_allPrices = $allPrices;
		$this->_count = $count;
		$this->_codes = $codes;
		$this->_productId = $productId;
		$this->_ossApplied = $ossApplied;
	}

	public function getId(): ?int
	{
		return $this->_productId;
	}

	public function getName(): string
	{
		return $this->_name;
	}

	public function getType(): string
	{
		return $this->_type;
	}

	/** @return MwsPrice[] */
	public function getAllPrices(): array
	{
		return $this->_allPrices;
	}

	public function allPricesToArray(): array
	{
		$return = [];
		foreach ($this->_allPrices as $currency => $price) {
			$return[$currency] = $price->toArray();
		}

		return $return;
	}

	public function getPrice(): MwsPrice
	{
		return $this->_price;
	}

	public function getPriceInCurrency(string $currency): MwsPrice
	{
		if (!isset($this->_allPrices[$currency])) {
			throw new NoPriceForThatCurrencyException();
		}

		return $this->_allPrices[$currency];
	}

	public function getTotalPrice(): MwsPrice
	{
		return $this->getPrice()->multiply($this->getCount());
	}

	public function getTotalPriceInCurrency(string $currency): MwsPrice
	{
		return $this->getPriceInCurrency($currency)->multiply($this->getCount());
	}

	public function getCount(): int
	{
		return $this->_count;
	}

	public function getCodes(): ?MwsProductCodes
	{
		return $this->_codes;
	}

	public function getProduct(): ?MwsProduct
	{
		return $this->_productId ? MwsProduct::getOneById($this->_productId) : null;
	}

	public function isOssApplied(): bool
	{
		return $this->_ossApplied;
	}

	public function setOssApplied(bool $ossApplied = true): void
	{
		$this->_ossApplied = $ossApplied;
	}

	/** @return mixed[] */
	public function toArray(): array
	{
		return [
			'name' => $this->getName(),
			'type' => $this->getType(),
			'price' => $this->getPrice()->toArray(),
			'prices' => $this->allPricesToArray(),
			'count' => $this->getCount(),
			'codes' => ($codes = $this->getCodes()) ? $codes->toArray() : null,
			'productId' => $this->_productId,
			'ossApplied' => $this->isOssApplied(),
		];
	}

	public function jsonSerialize()
	{
		return $this->toArray();
	}

	public static function createByArray(array $values): self
	{
		return new self(
			$values['name'],
			$values['type'],
			MwsPrice::createByArray($values['price']),
			array_map(function ($item) {
				return $item instanceof MwsPrice ? $item : MwsPrice::createByArray($item);
			}, $values['prices'] ?? []),
			$values['count'],
			new MwsProductCodes($values['codes']),
			$values['productId'] ?? null,
			$values['ossApplied'] ?? false
		);
	}

}
