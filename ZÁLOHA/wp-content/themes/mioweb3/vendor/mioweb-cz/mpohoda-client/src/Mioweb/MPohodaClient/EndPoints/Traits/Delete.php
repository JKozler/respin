<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient\EndPoints\Traits;

use Mioweb\MPohodaClient\Rest\MPohodaRestClient;

trait Delete
{

	private MPohodaRestClient $client;

	private string $path;

	public function delete(int $id): void
	{
		$this->client->deleteResource($this->path, $id);
	}

}
