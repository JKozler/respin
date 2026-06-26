<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient\DI;

use Mioweb\HttpClient\CurlHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\MPohodaClient\IMPohodaClient;
use Mioweb\MPohodaClient\IMPohodaClientFactory;
use Mioweb\MPohodaClient\MPohodaClientFactory;
use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

final class MPohodaClientExtension extends CompilerExtension
{

	/** @var array<mixed> */
	public $defaults = [
		'apiKey' => '',
		'apiUrl' => 'https://api.mpohoda.cz/v1/',
		'httpClient' => null,
	];

	public function loadConfiguration(): void
	{
		$container = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		Validators::assertField($config, 'apiKey', 'string');
		Validators::assertField($config, 'apiUrl', 'string');

		if ($config['httpClient'] === 'curl') {
			$container->addDefinition($this->prefix('httpClient'))
				->setType(IHttpClient::class)
				->setFactory(CurlHttpClient::class);
		} elseif ($config['httpClient'] === 'guzzle') {
			$container->addDefinition($this->prefix('httpClient'))
				->setType(IHttpClient::class)
				->setFactory(GuzzleHttpClient::class);
		}

		$container->addDefinition($this->prefix('mPohodaClientFactory'))
			->setType(IMPohodaClientFactory::class)
			->setFactory(MPohodaClientFactory::class, [
				'apiUrl' => $config['apiUrl'],
			]);

		if ($config['apiKey'] === '' || $config['apiKey'] === null) {
			return;
		}

		$container->addDefinition($this->prefix('mPohodaClient'))
			->setType(IMPohodaClient::class)
			->setFactory('@' . $this->prefix('mPohodaClientFactory') . '::createMPohodaClient', [
				'apiKey' => $config['apiKey'],
			]);
	}

}
