<?php declare(strict_types=1);

namespace Mioweb\Shop\Order\History;

use Mioweb\Database\BaseEntity;
use Nette\Database\Table\ActiveRow;

class OrderHistory extends BaseEntity
{

	private ?int $id = null;

	private ?int $_orderId = null;

	private \DateTimeInterface $createdAt;

	private string $text;

	private ?string $event;

	private ?int $userId;

	public function __construct(string $text, ?int $userId, ?\DateTimeInterface $createdAt = null, ?string $event = null)
	{
		$this->text = $text;
		$this->userId = $userId;
		$this->createdAt = $createdAt ?? (new \DateTimeImmutable('now', wp_timezone()))->setTimezone(new \DateTimeZone('UTC'));
		$this->event = $event;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getOrderId(): ?int
	{
		return $this->_orderId;
	}

	public function setOrderId(int $orderId): void
	{
		$this->_orderId = $orderId;
	}

	public function getCreatedAt(): \DateTimeInterface
	{
		return $this->createdAt;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function getEvent(): ?string
	{
		return $this->event;
	}

	public function getUserId(): ?int
	{
		return $this->userId;
	}

	public static function getRepositoryClassName(): string
	{
		return OrderHistoryRepository::class;
	}

	/** @return mixed[] */
	public function toRowArray(): array
	{
		return [
			'id' => $this->getId(),
			'text' => $this->getText(),
			'event' => $this->getEvent(),
			'user_id' => $this->getUserId(),
			'order_id' => $this->getOrderId(),
			'created_at' => $this->getCreatedAt(),
		];
	}

	public static function createByRow(ActiveRow $row): self
	{
		$entity = new self(
			$row['text'],
			$row['user_id'],
			$row['created_at'],
			$row['event'],
		);

		$entity->setId($row['id']);
		$entity->setOrderId($row['order_id']);

		return $entity;
	}
}
