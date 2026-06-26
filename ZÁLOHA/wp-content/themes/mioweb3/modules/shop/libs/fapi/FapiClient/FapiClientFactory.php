<?php

namespace MwShop\FapiClient;

use MwShop\HttpClient\IHttpClient;

class FapiClientFactory implements IFapiClientFactory
{

	/** @var string */
	private $apiUrl;

	/** @var IHttpClient */
	private $httpClient;

	/**
	 * @param string $apiUrl
	 * @param IHttpClient $httpClient
	 */
	public function __construct($apiUrl, IHttpClient $httpClient)
	{
		$this->apiUrl = rtrim($apiUrl, '/');
		$this->httpClient = $httpClient;
	}


	/**
	 * @param string $username
	 * @param string $password
	 * @return IFapiClient
	 */
	public function createFapiClient($username, $password)
	{
		return new FapiClient($username, $password, $this->apiUrl, $this->httpClient);
	}
}
