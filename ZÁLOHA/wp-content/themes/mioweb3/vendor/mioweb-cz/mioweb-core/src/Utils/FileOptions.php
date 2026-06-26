<?php declare(strict_types=1);

namespace Mioweb\Core\Utils;

final class FileOptions implements Options
{
	private $fileName;

	private $options = [];

	public function __construct(
		string $fileName
	)
	{
		$this->fileName = $fileName;
		if (file_exists($fileName)) {
			$this->options = json_decode(file_get_contents($fileName), true);
		}
	}

	public function getOption(string $option)
	{
		return $this->options[$option] ?? null;
	}

	public function setOption(string $option, $value): void
	{
		$this->options[$option] = $value;
		file_put_contents($this->fileName, json_encode($this->options, JSON_PRETTY_PRINT));
	}
}
