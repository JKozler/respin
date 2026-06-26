<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient;

interface IMiowebAdminPublicClientFactory
{

	public function createMiowebAdminPublicClient(): IMiowebAdminPublicClient;

}
