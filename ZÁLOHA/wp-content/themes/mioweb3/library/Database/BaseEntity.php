<?php declare(strict_types=1);

namespace Mioweb\Database;

abstract class BaseEntity
{

	/** @return class-string<BaseRepository> */
	abstract public static function getRepositoryClassName(): string;

	abstract public function getId(): ?int;

	abstract public function setId(int $id): void;

	/** @return mixed[] */
	abstract public function toRowArray(): array;

}
