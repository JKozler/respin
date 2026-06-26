<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient;

interface IMiowebAdminClientFactory
{

	public function createMiowebAdminClient(string $username, string $password): IMiowebAdminClient;

}
