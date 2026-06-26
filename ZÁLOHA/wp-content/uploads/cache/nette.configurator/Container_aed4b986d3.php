<?php
// source: /data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor/mioweb-cz/mioweb-core/src/config/config.neon
// source: array
// source: array

/** @noinspection PhpParamsInspection,PhpMethodMayBeStaticInspection */

declare(strict_types=1);

class Container_aed4b986d3 extends Nette\DI\Container
{
	protected $tags = ['nette.inject' => ['application.1' => true, 'application.2' => true]];
	protected $types = ['container' => 'Nette\DI\Container'];

	protected $aliases = [
		'application' => 'application.application',
		'cacheStorage' => 'cache.storage',
		'database.default' => 'database.default.connection',
		'database.default.context' => 'database.default.explorer',
		'httpRequest' => 'http.request',
		'httpResponse' => 'http.response',
		'nette.cacheJournal' => 'cache.journal',
		'nette.database.default' => 'database.default',
		'nette.database.default.context' => 'database.default.explorer',
		'nette.httpRequestFactory' => 'http.requestFactory',
		'nette.latteFactory' => 'latte.latteFactory',
		'nette.mailer' => 'mail.mailer',
		'nette.presenterFactory' => 'application.presenterFactory',
		'nette.templateFactory' => 'latte.templateFactory',
		'session' => 'session.session',
	];

	protected $wiring = [
		'Nette\DI\Container' => [['container']],
		'Nette\Application\Application' => [['application.application']],
		'Nette\Application\IPresenterFactory' => [['application.presenterFactory']],
		'Nette\Application\LinkGenerator' => [['application.linkGenerator']],
		'Nette\Caching\Storages\Journal' => [['cache.journal']],
		'Nette\Caching\Storage' => [['cache.storage']],
		'Nette\Database\Connection' => [['database.default.connection']],
		'Nette\Database\IStructure' => [['database.default.structure']],
		'Nette\Database\Structure' => [['database.default.structure']],
		'Nette\Database\Conventions' => [['database.default.conventions']],
		'Nette\Database\Conventions\DiscoveredConventions' => [['database.default.conventions']],
		'Nette\Database\Explorer' => [['database.default.explorer']],
		'Nette\Http\RequestFactory' => [['http.requestFactory']],
		'Nette\Http\IRequest' => [['http.request']],
		'Nette\Http\Request' => [['http.request']],
		'Nette\Http\IResponse' => [['http.response']],
		'Nette\Http\Response' => [['http.response']],
		'Nette\Bridges\ApplicationLatte\LatteFactory' => [['latte.latteFactory']],
		'Nette\Application\UI\TemplateFactory' => [['latte.templateFactory']],
		'Nette\Bridges\ApplicationLatte\TemplateFactory' => [['latte.templateFactory']],
		'Nette\Mail\Mailer' => [['mail.mailer']],
		'Nette\Http\Session' => [['session.session']],
		'Tracy\ILogger' => [['tracy.logger']],
		'Tracy\BlueScreen' => [['tracy.blueScreen']],
		'Tracy\Bar' => [['tracy.bar']],
		'Phinx\Console\Command\AbstractCommand' => [['phinx.create', 'phinx.migrate', 'phinx.rollback']],
		'Symfony\Component\Console\Command\Command' => [['phinx.create', 'phinx.migrate', 'phinx.rollback']],
		'Phinx\Console\Command\Create' => [['phinx.create']],
		'Phinx\Console\Command\Migrate' => [['phinx.migrate']],
		'Phinx\Console\Command\Rollback' => [['phinx.rollback']],
		'Mioweb\Core\Database\VersionChecker' => [['phinx.versionChecker']],
		'Mioweb\HttpClient\IHttpClient' => [['httpClient.httpClient']],
		'Mioweb\MiowebAdminClient\IMiowebAdminPublicClient' => [['mwaClient.miowebAdminPublicClient']],
		'Mioweb\MiowebAdminClient\IMiowebAdminPublicClientFactory' => [['mwaClient.miowebAdminPublicClientFactory']],
		'Mioweb\MiowebAdminClient\IMiowebAdminClientFactory' => [['mwaClient.miowebAdminClientFactory']],
		'Nette\Routing\Router' => [['router']],
		'Nette\Routing\RouteList' => [['router']],
		'Mioweb\Core\Utils\Options' => [['options']],
		'Mioweb\Core\Analytics\IAnalytics' => [['01']],
		'Mioweb\Core\Analytics\Analytics' => [['01']],
		'Nette\Application\IPresenter' => [2 => ['application.1', 'application.2']],
		'NetteModule\ErrorPresenter' => [2 => ['application.1']],
		'NetteModule\MicroPresenter' => [2 => ['application.2']],
	];


	public function __construct(array $params = [])
	{
		parent::__construct($params);
		$this->parameters += [
			'isCrawler' => null,
			'appDir' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor/mioweb-cz/mioweb-core/src',
			'wwwDir' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-admin',
			'vendorDir' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor',
			'debugMode' => false,
			'productionMode' => true,
			'consoleMode' => false,
			'tempDir' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/uploads/',
			'routerPrefix' => null,
			'database' => null,
		];
		Nette\Utils\Validators::assert($this->parameters['database']['dsn'], 'string', 'dynamic parameter');
		Nette\Utils\Validators::assert($this->parameters['database']['user'], 'null|string', 'dynamic parameter');
		Nette\Utils\Validators::assert($this->parameters['database']['password'], 'null|string', 'dynamic parameter');
	}


	public function createService01(): Mioweb\Core\Analytics\Analytics
	{
		return new Mioweb\Core\Analytics\Analytics(
			(new Jaybizzle\CrawlerDetect\CrawlerDetect)->isCrawler(),
			$this->getService('options'),
			$this->getService('database.default.explorer'),
		);
	}


	public function createServiceApplication__1(): NetteModule\ErrorPresenter
	{
		return new NetteModule\ErrorPresenter($this->getService('tracy.logger'));
	}


	public function createServiceApplication__2(): NetteModule\MicroPresenter
	{
		return new NetteModule\MicroPresenter($this, $this->getService('http.request'), $this->getService('router'));
	}


	public function createServiceApplication__application(): Nette\Application\Application
	{
		$service = new Nette\Application\Application(
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('http.request'),
			$this->getService('http.response'),
		);
		$service->catchExceptions = true;
		$service->errorPresenter = 'Nette:Error';
		$this->initialize();
		Nette\Bridges\ApplicationDI\ApplicationExtension::initializeBlueScreenPanel(
			$this->getService('tracy.blueScreen'),
			$service,
		);
		return $service;
	}


	public function createServiceApplication__linkGenerator(): Nette\Application\LinkGenerator
	{
		return new Nette\Application\LinkGenerator(
			$this->getService('router'),
			$this->getService('http.request')->getUrl()->withoutUserInfo(),
			$this->getService('application.presenterFactory'),
		);
	}


	public function createServiceApplication__presenterFactory(): Nette\Application\IPresenterFactory
	{
		$service = new Nette\Application\PresenterFactory(new Nette\Bridges\ApplicationDI\PresenterFactoryCallback($this, 1, null));
		$service->setMapping(['*' => 'Mioweb\Core\Presenters\*Presenter']);
		return $service;
	}


	public function createServiceCache__journal(): Nette\Caching\Storages\Journal
	{
		return new Nette\Caching\Storages\SQLiteJournal('/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/uploads//cache/journal.s3db');
	}


	public function createServiceCache__storage(): Nette\Caching\Storage
	{
		return new Nette\Caching\Storages\FileStorage(
			'/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/uploads//cache',
			$this->getService('cache.journal'),
		);
	}


	public function createServiceContainer(): Container_aed4b986d3
	{
		return $this;
	}


	public function createServiceDatabase__default__connection(): Nette\Database\Connection
	{
		$service = new Nette\Database\Connection(
			$this->parameters['database']['dsn'],
			$this->parameters['database']['user'],
			$this->parameters['database']['password'],
			['lazy' => true],
		);
		Nette\Bridges\DatabaseTracy\ConnectionPanel::initialize(
			$service,
			false,
			'default',
			true,
			$this->getService('tracy.bar'),
			$this->getService('tracy.blueScreen'),
		);
		return $service;
	}


	public function createServiceDatabase__default__conventions(): Nette\Database\Conventions\DiscoveredConventions
	{
		return new Nette\Database\Conventions\DiscoveredConventions($this->getService('database.default.structure'));
	}


	public function createServiceDatabase__default__explorer(): Nette\Database\Explorer
	{
		return new Nette\Database\Explorer(
			$this->getService('database.default.connection'),
			$this->getService('database.default.structure'),
			$this->getService('database.default.conventions'),
			$this->getService('cache.storage'),
		);
	}


	public function createServiceDatabase__default__structure(): Nette\Database\Structure
	{
		return new Nette\Database\Structure($this->getService('database.default.connection'), $this->getService('cache.storage'));
	}


	public function createServiceHttp__request(): Nette\Http\Request
	{
		return $this->getService('http.requestFactory')->fromGlobals();
	}


	public function createServiceHttp__requestFactory(): Nette\Http\RequestFactory
	{
		$service = new Nette\Http\RequestFactory;
		$service->setProxy([]);
		return $service;
	}


	public function createServiceHttp__response(): Nette\Http\Response
	{
		$service = new Nette\Http\Response;
		$service->cookieSecure = $this->getService('http.request')->isSecured();
		return $service;
	}


	public function createServiceHttpClient__httpClient(): Mioweb\HttpClient\IHttpClient
	{
		return new Mioweb\HttpClient\GuzzleHttpClient;
	}


	public function createServiceLatte__latteFactory(): Nette\Bridges\ApplicationLatte\LatteFactory
	{
		return new class ($this) implements Nette\Bridges\ApplicationLatte\LatteFactory {
			private $container;


			public function __construct(Container_aed4b986d3 $container)
			{
				$this->container = $container;
			}


			public function create(): Latte\Engine
			{
				$service = new Latte\Engine;
				$service->setTempDirectory('/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/uploads//cache/latte');
				$service->setAutoRefresh(false);
				$service->setStrictTypes(false);
				$service->setContentType('html');
				return $service;
			}
		};
	}


	public function createServiceLatte__templateFactory(): Nette\Bridges\ApplicationLatte\TemplateFactory
	{
		return new Nette\Bridges\ApplicationLatte\TemplateFactory(
			$this->getService('latte.latteFactory'),
			$this->getService('http.request'),
			cacheStorage: $this->getService('cache.storage'),
			templateClass: null,
		);
	}


	public function createServiceMail__mailer(): Nette\Mail\Mailer
	{
		return new Nette\Mail\SendmailMailer;
	}


	public function createServiceMwaClient__miowebAdminClientFactory(
	): Mioweb\MiowebAdminClient\IMiowebAdminClientFactory
	{
		return new Mioweb\MiowebAdminClient\MiowebAdminClientFactory(
			'https://admin.smartcluster.net/api/',
			$this->getService('httpClient.httpClient'),
		);
	}


	public function createServiceMwaClient__miowebAdminPublicClient(): Mioweb\MiowebAdminClient\IMiowebAdminPublicClient
	{
		return new Mioweb\MiowebAdminClient\MiowebAdminPublicClient(
			'https://admin.smartcluster.net/public/',
			$this->getService('httpClient.httpClient'),
		);
	}


	public function createServiceMwaClient__miowebAdminPublicClientFactory(
	): Mioweb\MiowebAdminClient\IMiowebAdminPublicClientFactory
	{
		return new Mioweb\MiowebAdminClient\MiowebAdminPublicClientFactory(
			'https://admin.smartcluster.net/public/',
			$this->getService('httpClient.httpClient'),
		);
	}


	public function createServiceOptions(): Mioweb\Core\Utils\Options
	{
		return new Mioweb\Core\Utils\FileOptions('/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/uploads//options.json');
	}


	public function createServicePhinx__create(): Phinx\Console\Command\Create
	{
		$service = new Phinx\Console\Command\Create;
		$service->setName('phinx:create');
		$service->setConfig(new Phinx\Config\Config([
			'paths' => [
				'migrations' => [
					'Mioweb\Core\Database\Migrations' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor/mioweb-cz/mioweb-core/src/Database/Migrations',
				],
				'seeds' => [
					'Mioweb\Core\Database\Seeds' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor/mioweb-cz/mioweb-core/src/Database/Seeds',
				],
			],
			'environments' => [
				'default_migration_table' => 'core_migrations',
				'default_environment' => 'default',
				'default' => [
					'name' => Mioweb\Core\Database\PhinxExtension::getNameFromConnection($this->getService('database.default.connection')),
					'connection' => $this->getService('database.default.connection')->getPdo(),
				],
			],
			'version_order' => 'creation',
		]));
		return $service;
	}


	public function createServicePhinx__migrate(): Phinx\Console\Command\Migrate
	{
		$service = new Phinx\Console\Command\Migrate;
		$service->setName('phinx:migrate');
		$service->setConfig(new Phinx\Config\Config([
			'paths' => [
				'migrations' => [
					'Mioweb\Core\Database\Migrations' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor/mioweb-cz/mioweb-core/src/Database/Migrations',
				],
				'seeds' => [
					'Mioweb\Core\Database\Seeds' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor/mioweb-cz/mioweb-core/src/Database/Seeds',
				],
			],
			'environments' => [
				'default_migration_table' => 'core_migrations',
				'default_environment' => 'default',
				'default' => [
					'name' => Mioweb\Core\Database\PhinxExtension::getNameFromConnection($this->getService('database.default.connection')),
					'connection' => $this->getService('database.default.connection')->getPdo(),
				],
			],
			'version_order' => 'creation',
		]));
		return $service;
	}


	public function createServicePhinx__rollback(): Phinx\Console\Command\Rollback
	{
		$service = new Phinx\Console\Command\Rollback;
		$service->setName('phinx:rollback');
		$service->setConfig(new Phinx\Config\Config([
			'paths' => [
				'migrations' => [
					'Mioweb\Core\Database\Migrations' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor/mioweb-cz/mioweb-core/src/Database/Migrations',
				],
				'seeds' => [
					'Mioweb\Core\Database\Seeds' => '/data/web/virtuals/230071/virtual/www/domains/respinteam.cz/wp-content/themes/mioweb3/vendor/mioweb-cz/mioweb-core/src/Database/Seeds',
				],
			],
			'environments' => [
				'default_migration_table' => 'core_migrations',
				'default_environment' => 'default',
				'default' => [
					'name' => Mioweb\Core\Database\PhinxExtension::getNameFromConnection($this->getService('database.default.connection')),
					'connection' => $this->getService('database.default.connection')->getPdo(),
				],
			],
			'version_order' => 'creation',
		]));
		return $service;
	}


	public function createServicePhinx__versionChecker(): Mioweb\Core\Database\VersionChecker
	{
		return new Mioweb\Core\Database\VersionChecker($this, $this->getService('options'), $this->getService('tracy.logger'));
	}


	public function createServiceRouter(): Nette\Routing\RouteList
	{
		return Mioweb\Core\Router\RouterFactory::createRouter($this->parameters['routerPrefix']);
	}


	public function createServiceSession__session(): Nette\Http\Session
	{
		$service = new Nette\Http\Session($this->getService('http.request'), $this->getService('http.response'));
		$service->setExpiration('14 days');
		$service->setOptions(['cookieSamesite' => 'Lax']);
		return $service;
	}


	public function createServiceTracy__bar(): Tracy\Bar
	{
		return Tracy\Debugger::getBar();
	}


	public function createServiceTracy__blueScreen(): Tracy\BlueScreen
	{
		return Tracy\Debugger::getBlueScreen();
	}


	public function createServiceTracy__logger(): Tracy\ILogger
	{
		return Tracy\Debugger::getLogger();
	}


	public function initialize()
	{
		// http.
		(function () {
			$response = $this->getService('http.response');
			$response->setHeader('X-Powered-By', 'Nette Framework 3');
			$response->setHeader('Content-Type', 'text/html; charset=utf-8');
			$response->setHeader('X-Frame-Options', 'SAMEORIGIN');
			Nette\Http\Helpers::initCookie($this->getService('http.request'), $response);
		})();
		// tracy.
		(function () {
			if (!Tracy\Debugger::isEnabled()) { return; }
			$logger = $this->getService('tracy.logger');
			if ($logger instanceof Tracy\Logger) $logger->mailer = [
				new Tracy\Bridges\Nette\MailSender($this->getService('mail.mailer'), null),
				'send',
			];
		})();
	}
}
