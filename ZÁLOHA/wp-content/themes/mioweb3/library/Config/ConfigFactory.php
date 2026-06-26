<?php declare(strict_types=1);

namespace Mioweb\Config;

use Mioweb\Config\Sources\NeonSource;

class ConfigFactory
{

	/** @var Config */
	private static $instance = null;

	public static function getInstance(): Config
	{
		if (self::$instance === null) {
			self::$instance = new Config(new NeonSource(__DIR__ . '/../../config/common.neon'));
		}

		return self::$instance;
	}

}
