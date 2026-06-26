<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient;

interface IMPohodaClientFactory
{

	public function createMPohodaClient(string $apiKey): IMPohodaClient;

}
