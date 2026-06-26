<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

interface IOrder
{

	public function getId(): ?int;

	public function getGateIdentifier(): string;

	public function getNumber(): string;

	public function getGateOrderData(): array;

}
