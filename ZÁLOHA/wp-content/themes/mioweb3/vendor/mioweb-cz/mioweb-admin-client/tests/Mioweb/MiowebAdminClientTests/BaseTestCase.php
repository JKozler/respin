<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests;

use Mioweb\HttpClient\IHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Tester\TestCase;

abstract class BaseTestCase extends TestCase
{

	protected function createMiowebAdminClient(IHttpClient $httpClient): MiowebAdminClient
	{
		return new MiowebAdminClient('admin', 'admin', 'http://mioweb-admin.dev/api/', $httpClient);
	}

}
