<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\DI;

use Mioweb\MiowebAdminClient\IMiowebAdminClient;
use Mioweb\MiowebAdminClient\IMiowebAdminClientFactory;
use Mioweb\MiowebAdminClient\IMiowebAdminPublicClient;
use Mioweb\MiowebAdminClient\IMiowebAdminPublicClientFactory;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClient\MiowebAdminClientFactory;
use Mioweb\MiowebAdminClient\MiowebAdminPublicClient;
use Mioweb\MiowebAdminClient\MiowebAdminPublicClientFactory;
use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

class MiowebAdminClientExtension extends CompilerExtension
{

	/** @var array<string, string> */
	public array $defaults = [
		'username' => '',
		'password' => '',
		'apiUrl' => 'https://admin.smartcluster.net/api/',
		'publicApiUrl' => 'https://admin.smartcluster.net/public/',
	];

	public function loadConfiguration(): void
	{
		$container = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		Validators::assertField($config, 'username', 'string');
		Validators::assertField($config, 'password', 'string');
		Validators::assertField($config, 'apiUrl', 'string');
		Validators::assertField($config, 'publicApiUrl', 'string');

		$container->addDefinition($this->prefix('miowebAdminPublicClient'))
			->setClass(IMiowebAdminPublicClient::class)
			->setFactory(MiowebAdminPublicClient::class, [
				'apiUrl' => $config['publicApiUrl'],
			]);

		$container->addDefinition($this->prefix('miowebAdminPublicClientFactory'))
			->setClass(IMiowebAdminPublicClientFactory::class)
			->setFactory(MiowebAdminPublicClientFactory::class, [
				'apiUrl' => $config['publicApiUrl'],
			]);

		$container->addDefinition($this->prefix('miowebAdminClientFactory'))
			->setClass(IMiowebAdminClientFactory::class)
			->setFactory(MiowebAdminClientFactory::class, [
				'apiUrl' => $config['apiUrl'],
			]);

		if ((bool) $config['username']) {
			$container->addDefinition($this->prefix('miowebAdminClient'))
				->setClass(IMiowebAdminClient::class)
				->setFactory(MiowebAdminClient::class, [
					'username' => $config['username'],
					'password' => $config['password'],
					'apiUrl' => $config['apiUrl'],
				]);
		}
	}

}
