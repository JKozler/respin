<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient\EndPoints\Traits;

use Mioweb\MPohodaClient\Rest\MPohodaRestClient;

trait Update
{

	private MPohodaRestClient $client;

	private string $path;

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public function update(int $id, array $data): array
	{
		return $this->client->updateResource($this->path, $id, $data);
	}

}
