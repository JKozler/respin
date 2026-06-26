<?php declare(strict_types=1);

namespace Mioweb\HttpClient\DI;

use Mioweb\HttpClient\CurlHttpClient;
use Mioweb\HttpClient\Exceptions\InvalidStateException;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\HttpClient\LoggingHttpClient;
use Nette\DI\CompilerExtension;

class HttpClientExtension extends CompilerExtension
{

	/** @var mixed[] */
	public array $defaults = [
		'type' => 'guzzle',
		'logging' => false,
	];

	/** @var string[] */
	private array $typeClasses = [
		'curl' => CurlHttpClient::class,
		'guzzle' => GuzzleHttpClient::class,
	];

	public function loadConfiguration(): void
	{
		$container = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		if (!isset($this->typeClasses[$config['type']])) {
			throw new InvalidStateException("Type '" . $config['type'] . "' is not supported.'");
		}

		$httpClientClass = $this->typeClasses[$config['type']];

		if ($config['logging']) {
			$container->addDefinition($this->prefix('innerHttpClient'))
				->setClass($httpClientClass)
				->setAutowired(false);

			$container->addDefinition($this->prefix('httpClient'))
				->setClass(IHttpClient::class)
				->setFactory(LoggingHttpClient::class, [
					$this->prefix('@innerHttpClient'),
				]);
		} else {
			$container->addDefinition($this->prefix('httpClient'))
				->setClass(IHttpClient::class)
				->setFactory($httpClientClass);
		}
	}

}
