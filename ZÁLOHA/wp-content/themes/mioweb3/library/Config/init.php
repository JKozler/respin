<?php declare(strict_types=1);

use Mioweb\Config\Config;
use Mioweb\Config\ConfigFactory;

require_once __DIR__ . '/ConfigFactory.php';

function mwConfig(): Config
{
	return ConfigFactory::getInstance();
}
