<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

use MwsEmailType;
use MwsPrice;

interface OrderGateDocument
{

	public function getName(): string;

	public function getCreatedAt(): \DateTimeInterface;

	public function getDueDate(): \DateTimeInterface;

	public function getTaxableSupplyAt(): ?\DateTimeInterface;

	public function getDownloadUrl(): string;

	public function getDetailUrl(): ?string;

	public function getEditUrl(): ?string;

	/** Total price */
	public function getPrice(): MwsPrice;

	public function isPaid(): bool;

	public function sendToCustomer(string $emailType = MwsEmailType::PayedOrder): void;

}
