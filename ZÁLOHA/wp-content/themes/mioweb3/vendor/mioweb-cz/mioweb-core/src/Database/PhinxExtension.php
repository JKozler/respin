<?php declare(strict_types=1);

namespace Mioweb\Core\Database;

use Nette\DI\CompilerExtension;
use Nette\Database\Connection;
use Nette\DI\Definitions\Statement;
use Phinx\Config\Config;
use Phinx\Console\Command\Create;
use Phinx\Console\Command\Migrate;
use Phinx\Console\Command\Rollback;

class PhinxExtension extends CompilerExtension
{

	/** @var string[] */
	private static $commands = [
		Create::class,
		Migrate::class,
		Rollback::class,
	];

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$config = [
			'paths' => [
				'migrations' => [
					'Mioweb\\Core\\Database\\Migrations' => __DIR__ . '/Migrations',
				],
				'seeds' => [
					'Mioweb\\Core\\Database\\Seeds' => __DIR__ . '/Seeds',
				],
			],
			'environments' => [
				'default_migration_table' => 'core_migrations',
				'default_environment' => 'default',
				'default' => [
					'name' => new Statement(self::class . '::getNameFromConnection'),
					'connection' => new Statement('@database.default.connection::getPdo'),
				],
			],
			'version_order' => 'creation',
		];

		foreach (static::$commands as $class) {
			$name = lcfirst(str_replace('Phinx\\Console\\Command\\', '', $class));
			$command = $this->name . ':' . strtolower((string) preg_replace('#([a-z])([A-Z])#', '$1-$2', $name));

			$builder->addDefinition($this->prefix($name))
				->setFactory($class)
				->addSetup('setName', [$command])
				->addSetup('setConfig', [new Statement(Config::class, [$config])]);
		}

		$builder->addDefinition($this->prefix('versionChecker'))
			->setFactory(VersionChecker::class);
	}

	public static function getNameFromConnection(Connection $connection): string
	{
		return $connection->fetchField('SELECT DATABASE() AS `name`');
	}

}
