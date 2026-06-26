<?php declare(strict_types=1);

namespace Mioweb\Config\Sources;

use Mioweb\Config\Exceptions\ParseException;
use Nette\Neon\Neon;

class NeonSource implements ISource
{

	/** @var string */
	private $filePath;

	public function __construct(string $file)
	{
		$this->filePath = $file;
	}

	/**
	 * @return mixed[]
	 * @throws ParseException
	 */
	public function parse(): array
	{
		$decoded = Neon::decode(\file_get_contents($this->filePath));

		if (!\is_array($decoded)) {
			throw new ParseException('Decoded neon is not an array.');
		}

		return $decoded;
	}

}
