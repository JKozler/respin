<?php declare(strict_types=1);

namespace Mioweb\Core\Router;

use Nette\Routing\RouteList;
use Nette\StaticClass;

final class RouterFactory
{
	use StaticClass;

	public static function createRouter(string $prefix = ''): RouteList
	{
		$router = new RouteList;
		$router->addRoute(($prefix ?: '') . '/<presenter>/<action>[/<id>]');
		return $router;
	}
}
