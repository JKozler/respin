<?php

namespace MwShop\HttpClient\Rest;

use MwShop\HttpClient\HttpClientException;
use MwShop\HttpClient\HttpMethod;
use MwShop\HttpClient\HttpRequest;
use MwShop\HttpClient\HttpResponse;
use MwShop\HttpClient\HttpStatusCode;
use MwShop\HttpClient\IHttpClient;
use MwShop\HttpClient\InvalidArgumentException;
use MwShop\HttpClient\Utils\Json;

class RestClient
{

	/** @var string */
	private $username;

	/** @var string */
	private $password;

	/** @var string */
	private $apiUrl;

	/** @var IHttpClient */
	private $httpClient;

	/**
	 * @param string $username
	 * @param string $password
	 * @param string $apiUrl
	 * @param IHttpClient $httpClient
	 */
	public function __construct($username, $password, $apiUrl, IHttpClient $httpClient)
	{
		$this->username = $username;
		$this->password = $password;
		$this->apiUrl = rtrim($apiUrl, '/');
		$this->httpClient = $httpClient;
	}


	/**
	 * @param string $path
	 * @param array $parameters
	 * @return array
	 */
	public function getResources($path, array $parameters = [])
	{
		if ($parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getArrayOfArrayResponseData($httpResponse);
		}

		throw new InvalidStatusCodeException();
	}


	/**
	 * @param string $path
	 * @param int $id
	 * @param array $parameters
	 * @return array|null
	 */
	public function getResource($path, $id, array $parameters = [])
	{
		if (!is_int($id)) {
			throw new InvalidArgumentException('Parameter id must be an integer.');
		}

		$path .= '/' . $id;

		if ($parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getArrayResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			return null;
		}

		throw new InvalidStatusCodeException();
	}


	/**
	 * @param string $path
	 * @param array $parameters
	 * @return array
	 */
	public function getSingularResource($path, array $parameters = [])
	{
		if ($parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getArrayResponseData($httpResponse);
		}

		throw new InvalidStatusCodeException();
	}


	/**
	 * @param string $path
	 * @param array $data
	 * @return array
	 */
	public function createResource($path, array $data)
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, $path, $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getArrayResponseData($httpResponse);
		}

		throw new InvalidStatusCodeException();
	}


	/**
	 * @param string $path
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	public function updateResource($path, $id, array $data)
	{
		if (!is_int($id)) {
			throw new InvalidArgumentException('Parameter id must be an integer.');
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::PUT, $path . '/' . $id, $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getArrayResponseData($httpResponse);
		}

		throw new InvalidStatusCodeException();
	}


	/**
	 * @param string $path
	 * @param int $id
	 * @return void
	 */
	public function deleteResource($path, $id)
	{
		if (!is_int($id)) {
			throw new InvalidArgumentException('Parameter id must be an integer.');
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::DELETE, $path . '/' . $id);

		if (!in_array($httpResponse->getStatusCode(), [HttpStatusCode::S200_OK, HttpStatusCode::S204_NO_CONTENT], true)) {
			throw new InvalidStatusCodeException();
		}
	}


	/**
	 * @param string $method
	 * @param string $path
	 * @param array|null $data
	 * @return HttpResponse
	 */
	private function sendHttpRequest($method, $path, $data = null)
	{
		$url = $this->apiUrl . $path;

		$options = [
			'auth' => [$this->username, $this->password],
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			],
		];

		if ($data !== null) {
			$options['json'] = $data;
		}

		try {
			$httpRequest = new HttpRequest($url, $method, $options);

			return $this->httpClient->sendHttpRequest($httpRequest);
		} catch (HttpClientException $e) {
			throw new RestClientException('Failed to send an HTTP request.', 0, $e);
		}
	}


	/**
	 * @param array $parameters
	 * @return string
	 */
	private function formatUrlParameters(array $parameters)
	{
		return http_build_query($parameters, '', '&');
	}


	/**
	 * @param HttpResponse $httpResponse
	 * @return array
	 */
	private function getArrayOfArrayResponseData(HttpResponse $httpResponse)
	{
		$responseData = $this->getArrayResponseData($httpResponse);

		foreach ($responseData as $value) {
			if (!is_array($value)) {
				throw new InvalidResponseBodyException('Response data is not an array of array.');
			}
		}

		return $responseData;
	}


	/**
	 * @param HttpResponse $httpResponse
	 * @return array
	 */
	private function getArrayResponseData(HttpResponse $httpResponse)
	{
		$responseData = $this->getResponseData($httpResponse);

		if (!is_array($responseData)) {
			throw new InvalidResponseBodyException('Response data is not an array.');
		}

		return $responseData;
	}


	/**
	 * @param HttpResponse $httpResponse
	 * @return mixed
	 */
	private function getResponseData(HttpResponse $httpResponse)
	{
		try {
			return Json::decode($httpResponse->getBody(), Json::FORCE_ARRAY);
		} catch (\Exception $e) {
			throw new InvalidResponseBodyException('Response body is not a valid JSON.', 0, $e);
		}
	}
}
