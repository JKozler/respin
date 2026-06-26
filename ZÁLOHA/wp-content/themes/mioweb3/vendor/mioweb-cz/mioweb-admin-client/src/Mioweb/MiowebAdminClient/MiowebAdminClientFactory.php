<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient;

use Mioweb\HttpClient\IHttpClient;

class MiowebAdminClientFactory implements IMiowebAdminClientFactory
{

	private string $apiUrl;

	private IHttpClient $httpClient;

	public function __construct(string $apiUrl, IHttpClient $httpClient)
	{
		$this->apiUrl = \rtrim($apiUrl, '/');
		$this->httpClient = $httpClient;
	}

	public function createMiowebAdminClient(string $username, string $password): IMiowebAdminClient
	{
		return new MiowebAdminClient($username, $password, $this->apiUrl, $this->httpClient);
	}

}
