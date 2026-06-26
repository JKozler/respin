<?php

use Mioweb\Core\GettextTranslator;
use Nette\Utils\Strings;

class Core
{

	private static $instance = null;

	/** @var \Mioweb\Core\Core */
	private $core;

	private function __construct()
	{
		require_once __DIR__ . '/GettextTranslator.php';
		require_once __DIR__ . '/options.php';

		$this->core = new \Mioweb\Core\Core(
			get_temp_dir(),
			MW_LOG_DIR,
			MW_DEBUG,
			site_url() . '/core',
			[
				'dsn' => $this->getDsn(),
				'user' => DB_USER,
				'password' => DB_PASSWORD,
			],
			new Options(),
			new GettextTranslator(),
			[
				'mwaClient' => [
					'publicApiUrl' => LICENSE_SERVER,
				],
			]
		);
	}

	/** @return \Mioweb\Core\Core */
	public function getCore()
	{
		return $this->core;
	}

	/** @return Core */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function getDsn(): string
	{
		global $wpdb;
		$hostData = method_exists($wpdb, 'parse_db_host') ? $wpdb->parse_db_host(DB_HOST) : null;

		if ($hostData) {
			[$host, $port, $socket, $is_ipv6] = $hostData;
			$dsn = $socket ? 'mysql:unix_socket=' . $socket : 'mysql:host=' . $host;
			if ($port) {
				$dsn .= ';port=' . $port;
			}
		} else {
			$host = DB_HOST;
			$dsn = Strings::endsWith($host, '.sock') ? 'mysql:unix_socket=' . $host : 'mysql:host=' . $host;
		}

		return $dsn . ';dbname=' . DB_NAME;
	}
}
