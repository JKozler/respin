<?php declare(strict_types=1);

namespace Mioweb\Config;

use Mioweb\Config\Sources\ISource;
use function array_merge_recursive;

class Config
{

	/** @var string */
	private $licenseServer;

	/** @var array<string, string> */
	private $headers;

	/** @var int|null in seconds. Null means HSTS disabled */
	private $hstsAge;

	public function __construct(ISource $configSource)
	{
		$config = $configSource->parse();

		$this->licenseServer = \defined('LICENSE_SERVER') ? LICENSE_SERVER : $config['licenseServer'];
		$this->headers = \defined('RESPONSE_HEADERS') && \is_array(RESPONSE_HEADERS)
			? array_merge_recursive((array) $config['headers'], RESPONSE_HEADERS)
			: (array) $config['headers'];
		$this->hstsAge = \defined('HSTS_AGE') ? HSTS_AGE : $config['hstsAge'];
	}

	public function getLicenseServer(): string
	{
		return $this->licenseServer;
	}

	/** @return array<string, string> */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function getHstsAge(): ?int
	{
		return $this->hstsAge;
	}

}
