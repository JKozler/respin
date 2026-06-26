<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient\EndPoints\Traits;

use Mioweb\MPohodaClient\Rest\MPohodaRestClient;

trait Find
{

	private MPohodaRestClient $client;

	private string $path;

	/**
	 * @param int|string $id
	 * @param array<mixed> $parameters
	 * @return array<mixed>|null
	 */
	public function find($id, array $parameters = []): ?array
	{
		return $this->client->getResource($this->path, $id, $parameters);
	}

}
