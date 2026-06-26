<?php declare(strict_types=1);

namespace Mioweb\Core\Database;

use Mioweb\Core\Utils\Options;
use Nette\DI\Container;
use Phinx\Console\Command\Migrate;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tracy\ILogger;

final class VersionChecker
{
	private const VERSION = '0.3';
	private const OPTION_VERSION = 'core_database_version';
	private const OPTION_LAST_FAIL = 'core_database_migration_last_fail';

	private $container;

	private $options;

	private ILogger $logger;

	public function __construct(
		Container $container,
		Options $options,
		ILogger $logger,
	)
	{
		$this->container = $container;
		$this->options = $options;
		$this->logger = $logger;
	}

	public function check(): void
	{
		$actualVersion = $this->options->getOption(self::OPTION_VERSION);
		$expectedVersion = self::VERSION;
		$lastFail = $this->options->getOption(self::OPTION_LAST_FAIL) ?: null;
		$failTryInterval = 300; // 5 minutes

		if ($actualVersion !== $expectedVersion && (!$lastFail || (int) $lastFail < time() - $failTryInterval)) {
			/** @var Migrate $migrateCommand */
			$migrateCommand = $this->container->getByType(Migrate::class);
			$output = new BufferedOutput();
			$success = $migrateCommand->run(new ArrayInput([]), $output) === 0;

			if ($success) {
				$this->options->setOption(self::OPTION_VERSION, $expectedVersion);
			} else {
				$this->logger->log('Database migration failed: ' . $output->fetch(), ILogger::EXCEPTION);
				$this->options->setOption(self::OPTION_LAST_FAIL, time());
			}
		}
	}

}
