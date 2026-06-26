<?php declare(strict_types=1);

namespace Mioweb\Lib;

use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;

class LockFactory extends \Symfony\Component\Lock\LockFactory
{

	public function __construct()
	{
//		$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

		if (SemaphoreStore::isSupported()) {
			$store = new SemaphoreStore();
		} else {
			$tempDir = get_temp_dir() . 'mw-locks';
			if (!is_dir($tempDir)) {
				@mkdir($tempDir, 0777, true);
			}
			$store = new FlockStore($tempDir);
		}

		parent::__construct($store);
	}

}
