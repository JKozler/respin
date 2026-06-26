<?php declare(strict_types=1);

namespace Mioweb\Shop\Gates;

use Mioweb\Database\BaseEntity;
use Nette\Database\Table\ActiveRow;

class ShopGate extends BaseEntity
{

	private int $id;

	private string $identifier;

	public function __construct(string $identifier)
	{
		$this->identifier = $identifier;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getIdentifier(): string
	{
		return $this->identifier;
	}

	public function setIdentifier(string $identifier): void
	{
		$this->identifier = $identifier;
	}

	public static function getRepositoryClassName(): string
	{
		return ShopGateRepository::class;
	}

	/** @return mixed[] */
	public function toRowArray(): array
	{
		return [
			'id' => $this->getId(),
			'identifier' => $this->getIdentifier(),
		];
	}

	public static function createByRow(ActiveRow $row): self
	{
		$entity = new self(
			$row['identifier'],
		);

		$entity->setId($row['id']);

		return $entity;
	}
}
