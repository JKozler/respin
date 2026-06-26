<?php declare(strict_types=1);

namespace Mioweb\Shop\Order\Exporters;

use Mioweb\Shop\Order\Order;

interface IOrderExporter
{

	/** @param iterable<Order> $orders */
	public function export(iterable $orders): string;

	public function getIdentifier(): string;

	public function getName(): string;

	public function getFileExtension(): string;

}
