<?php declare(strict_types = 1);

namespace Mioweb\Core;

use Mioweb\Core\Analytics\IAnalytics;
use Mioweb\Core\Database\VersionChecker;
use Mioweb\Core\Utils\Logger;
use Mioweb\Core\Utils\Options;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\MiowebAdminClient\IMiowebAdminPublicClient;
use Nette\Application\Application;
use Nette\Configurator;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Http\Session;
use Nette\Localization\Translator;
use Nette\Utils\FileSystem;
use Latte;
use function file_exists;

final class Core
{

	/** @var Container */
	private $container;

	/** @var string */
	private $tempDirectory;

	/** @var string */
	private $logDirectory;

	/** @var bool */
	private $debugMode;

	/** @var string */
	private $routerPrefix;

	/** @var mixed[] */
	private $database;

	/** @var Options */
	private $options;

	private ?Translator $translator;

	private ?array $config;

	public function __construct(
		string $tempDirectory,
		string $logDirectory,
		bool $debugMode,
		string $routerPrefix,
		array $database,
		Options $options,
		?Translator $translator = null,
		?array $config = null
	)
	{
		$this->tempDirectory = $tempDirectory;
		$this->debugMode = $debugMode;
		$this->routerPrefix = $routerPrefix;
		$this->database = $database;
		$this->options = $options;
		$this->translator = $translator;
		$this->config = $config;

		if (!file_exists($logDirectory)) {
			FileSystem::createDir($logDirectory);
		}
		$this->logDirectory = $logDirectory;

		$this->recreateContainer();
	}

	/**
	 * @param callable(Configurator &$configurator)|null $configurationCallback
	 * @return Container
	 */
	public function recreateContainer(callable $configurationCallback = null): Container
	{
		$configurator = new Configurator();

		$configurator->setTempDirectory($this->tempDirectory);
		$configurator->addConfig(__DIR__ . '/config/config.neon');
		if ($this->config !== null) {
			$configurator->addConfig($this->config);
		}
		$configurator->setDebugMode($this->debugMode);
		$configurator->enableTracy($this->logDirectory);
		$configurator->addDynamicParameters($dynamicParameters = [
			'routerPrefix' => $this->routerPrefix,
			'database' => $this->database,
		]);

		// dont used $configurator->createContainer() because initialization call only in request use
		if ($configurationCallback !== null) {
			$configurationCallback($configurator);
		}
		$class = $configurator->loadContainer();
		$container = new $class($dynamicParameters);
		$container->addService('options', $this->options);

		/** @var VersionChecker $versionChecker */
		$versionChecker = $container->getByType(VersionChecker::class);
		$versionChecker->check();

		if (session_status() !== PHP_SESSION_ACTIVE) {
			if (!headers_sent($file, $line)) {
				/** @var Session $session */
				$session = $container->getByType(Session::class);
				$session->start();
			} elseif (isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] !== '/wp-cron.php') {
				Logger::triggerSilentError(sprintf('Headers was already sent (in %s::%s) before initializing Mioweb template.', $file, $line), E_USER_WARNING);
			}
		} elseif ($configurationCallback === null) {
			Logger::triggerSilentError('Session was started before initializing Mioweb template.', E_USER_WARNING);
		}

		$this->container = $container;
		return $container;
	}

	public function getContainer(): Container
	{
		return $this->container;
	}

	public function getExplorer(): Explorer
	{
		/** @var Explorer $explorer */
		$explorer = $this->getServiceByType(Explorer::class);
		return $explorer;
	}

	public function getAnalytics(): IAnalytics
	{
		/** @var IAnalytics $analytics */
		$analytics = $this->getServiceByType(IAnalytics::class);
		return $analytics;
	}

	public function getLatte(): Latte\Engine
	{
		$latte = new Latte\Engine();
		$latte->setTempDirectory(rtrim($this->tempDirectory, '/\\'));
		$latte->setStrictTypes(true);
		if ($this->translator !== null) {
			$latte->addFilter('translate', [$this->translator, 'translate']);
		}
		return $latte;
	}

	public function getHttpClient(): IHttpClient
	{
		return $this->getServiceByType(IHttpClient::class);
	}

	public function getMwaPublicClient(): IMiowebAdminPublicClient
	{
		return $this->getServiceByType(IMiowebAdminPublicClient::class);
	}

	public function processRequest(): void
	{
		/** @var Application $application */
		$application = $this->getServiceByType(Application::class);
		$application->run();
	}

	public function getServiceByType(string $type)
	{
		return $this->container->getByType($type);
	}

}
