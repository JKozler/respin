<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

use Mioweb\Database\BaseEntity;
use MwsOrderItemType;
use MwsPrice;
use MwsProduct;
use MwsProductCodes;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Json;

/**
 * One item of an order. Can provide direct access to product through properties.
 */
class OrderItem extends BaseEntity
{

	private ?int $_id = null;

	private ?int $_orderId = null;

	private string $_name;

	private ?string $_type;

	private int $_count;

	/** @var MwsPrice[] */
	private array $_prices;

	private ?MwsProductCodes $_codes;

	private ?int $_productId;

	private bool $_ossApplied;

	private bool $_miniupsell;

	private ?float $_weight;

	public function __construct(string $name, ?string $type, array $prices, int $count = 1, ?MwsProductCodes $codes = null, ?int $productId = null, bool $ossApplied = false, bool $miniupsell = false, ?float $weight = null)
	{
		$this->_name = $name;
		$this->_type = $type;
		$this->_prices = $prices;
		$this->_count = $count;
		$this->_codes = $codes;
		$this->_productId = $productId;
		$this->_ossApplied = $ossApplied;
		$this->_miniupsell = $miniupsell;
		$this->_weight = $weight;
	}

	public function getProductId(): ?int
	{
		return $this->_productId;
	}

	public function getId(): ?int
	{
		return $this->_id;
	}

	public function setId(int $id): void
	{
		$this->_id = $id;
	}

	public function getOrderId(): ?int
	{
		return $this->_orderId;
	}

	public function setOrderId(int $orderId): void
	{
		$this->_orderId = $orderId;
	}

	public function isProduct(): bool
	{
		return MwsOrderItemType::isActualProduct($this->_type);
	}

	public function getName(): string
	{
		return $this->_name;
	}

	/** Can be NULL for old orders created in 3.0 */
	public function getType(): ?string
	{
		return $this->_type;
	}


	/** @return MwsPrice[] */
	public function getPrices(): array
	{
		return $this->_prices;
	}

	public function pricesToArray(): array
	{
		$return = [];
		foreach ($this->_prices as $currency => $price) {
			$return[$currency] = $price->toArray();
		}

		return $return;
	}

	public function getPrice($currency): ?MwsPrice
	{
		return $this->_prices[$currency] ?? null;
	}

	public function changeVat(int $vatPercentage): void
	{
		foreach ($this->_prices as $currency => $price) {
			$price->changeVat($vatPercentage);
		}
	}
	public function removeVat(): void
	{
		foreach ($this->_prices as $currency => $price) {
			$price->removeVat();
		}
	}

	public function getTotalPrice($currency): MwsPrice
	{
		return $this->getPrice($currency)->multiply($this->getCount());
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

	public function getWeight(): ?float
	{
		return $this->_weight;
	}

	public function setWeight(float $weight): void
	{
		$this->_weight = $weight;
	}

	/** @return mixed[] */
	public function toArray(): array
	{
		$array = [
			'id' => $this->getId(),
			'order_id' => $this->getOrderId(),
			'name' => $this->getName(),
			'type' => $this->getType(),
			'prices' => $this->pricesToArray(),
			'count' => $this->getCount(),
			'codes' => ($codes = $this->getCodes()) ? $codes->toArray() : null,
			'productId' => $this->_productId,
			'weight' => $this->getWeight(),
		];
		if ($this->isOssApplied()) {
			$array['ossApplied'] = true;
		}
		if ($this->isMiniupsell()) {
			$array['miniupsell'] = true;
		}

		return $array;
	}

	/** @return mixed[] */
	public function toRowArray(): array
	{
		return [
			'id' => $this->getId(),
			'order_id' => $this->getOrderId(),
			'name' => $this->getName(),
			'type' => $this->getType(),
			'prices' => Json::encode($this->pricesToArray()),
			'count' => $this->getCount(),
			'codes' => $this->getCodes() !== null ? Json::encode($this->getCodes()->toArray()) : null,
			'product_id' => $this->getProductId(),
			'weight' => $this->getWeight(),
		];
	}

	public static function createByArray(array $values): self
	{
		return new self(
			$values['name'] ?? ($values['title'] ?? ''),
			$values['type'] ?? '',
			array_map(function (array $item) {
				return MwsPrice::createByArray($item);
			}, $values['prices'] ?? []),
			$values['count'],
			isset($values['codes']) ? new MwsProductCodes($values['codes']) : null,
			$values['productId'] ?? null,
			$values['ossApplied'] ?? false,
			(bool) ($values['miniupsell'] ?? false),
			isset($values['weight']) ? (float) $values['weight'] : null
		);
	}

	public static function createByRow(ActiveRow $row): self
	{
		$entity = new self(
			$row['name'],
			$row['type'],
			array_map(function (array $item) {
				return MwsPrice::createByArray($item);
			}, Json::decode($row['prices'], Json::FORCE_ARRAY)),
			$row['count'],
			isset($row['product_codes']) ? new MwsProductCodes($row['product_codes']) : null,
			$row['product_id'] ?? null,
			(bool) $row['oss_applied'],
			(bool) $row['is_miniupsell'],
			$row['weight'] !== null ? (float) $row['weight'] : null
		);

		$entity->setId($row['id']);
		$entity->setOrderId($row['order_id']);

		return $entity;
	}

	public function isMiniupsell(): bool
	{
		return $this->_miniupsell;
	}

	public static function getRepositoryClassName(): string
	{
		return OrderItemRepository::class;
	}
}
