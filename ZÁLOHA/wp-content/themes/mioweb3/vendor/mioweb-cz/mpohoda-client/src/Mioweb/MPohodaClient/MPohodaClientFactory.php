<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient;

use Mioweb\HttpClient\CurlHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\HttpClient\IHttpClient;
use function class_exists;
use function rtrim;

class MPohodaClientFactory implements IMPohodaClientFactory
{

	private string $apiUrl;

	private IHttpClient $httpClient;

	public function __construct(string $apiUrl = 'https://api.mpohoda.cz/v1', ?IHttpClient $httpClient = null)
	{
		$this->apiUrl = rtrim($apiUrl, '/');

		if ($httpClient === null) {
			$this->httpClient = class_exists('\GuzzleHttp\Client') ? new GuzzleHttpClient() : new CurlHttpClient();
		} else {
			$this->httpClient = $httpClient;
		}
	}

	public function createMPohodaClient(string $apiKey): IMPohodaClient
	{
		return new MPohodaClient($apiKey, $this->apiUrl, $this->httpClient);
	}

}
