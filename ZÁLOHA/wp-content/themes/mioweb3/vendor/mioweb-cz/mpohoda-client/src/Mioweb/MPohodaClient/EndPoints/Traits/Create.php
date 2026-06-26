<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient\EndPoints\Traits;

use Mioweb\MPohodaClient\Rest\MPohodaRestClient;

trait Create
{

	private MPohodaRestClient $client;

	private string $path;

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public function create(array $data): array
	{
		return $this->client->createResource($this->path, $data);
	}

}
