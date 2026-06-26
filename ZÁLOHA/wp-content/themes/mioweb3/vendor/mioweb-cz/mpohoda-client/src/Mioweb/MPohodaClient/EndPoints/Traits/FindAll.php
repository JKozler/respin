<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient\EndPoints\Traits;

use Mioweb\MPohodaClient\Rest\MPohodaRestClient;

trait FindAll
{

	private MPohodaRestClient $client;

	private string $path;

	private string $resources;

	/**
	 * @param array<mixed> $parameters
	 * @return array<mixed>
	 */
	public function findAll(array $parameters = []): array
	{
		return $this->client->getResources($this->path, $parameters);
	}

}
