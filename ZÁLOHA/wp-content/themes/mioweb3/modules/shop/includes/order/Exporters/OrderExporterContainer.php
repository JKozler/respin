<?php declare(strict_types=1);

namespace Mioweb\Shop\Order\Exporters;

use Mioweb\Shop\Order\Exporters\Exceptions\NoOrderExporterWithThisIdentifierException;
use Nette\Neon\Neon;

class OrderExporterContainer
{

	/** @var IOrderExporter[] */
	private array $exporters;

	public function __construct()
	{
		$content = file_get_contents(__DIR__ . '/exporters.neon');
		$this->exporters = array_map(static function (string $className): IOrderExporter {
			return new $className();
		}, Neon::decode($content));
	}

	public function getAll(): array
	{
		return $this->exporters;
	}

	public function getByIdentifier(string $identifier): IOrderExporter
	{
		foreach ($this->exporters as $exporter) {
			if ($identifier === $exporter->getIdentifier()) {
				return $exporter;
			}
		}

		throw new NoOrderExporterWithThisIdentifierException();
	}

}
