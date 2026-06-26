<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient;

use Mioweb\HttpClient\IHttpClient;

class MiowebAdminPublicClientFactory implements IMiowebAdminPublicClientFactory
{

	private string $apiUrl;

	private IHttpClient $httpClient;

	public function __construct(string $apiUrl, IHttpClient $httpClient)
	{
		$this->apiUrl = \rtrim($apiUrl, '/');
		$this->httpClient = $httpClient;
	}

	public function createMiowebAdminPublicClient(): IMiowebAdminPublicClient
	{
		return new MiowebAdminPublicClient($this->apiUrl, $this->httpClient);
	}

}
