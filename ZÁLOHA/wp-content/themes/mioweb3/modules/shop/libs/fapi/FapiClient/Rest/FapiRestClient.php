<?php

namespace MwShop\FapiClient\Rest;

use MwShop\FapiClient\InvalidArgumentException;
use MwShop\HttpClient\HttpClientException;
use MwShop\HttpClient\HttpMethod;
use MwShop\HttpClient\HttpRequest;
use MwShop\HttpClient\HttpResponse;
use MwShop\HttpClient\HttpStatusCode;
use MwShop\HttpClient\IHttpClient;
use MwShop\HttpClient\Utils\Json;

class FapiRestClient
{

	/** @var string */
	private $username;

	/** @var string */
	private $password;

	/** @var string */
	private $apiUrl;

	/** @var IHttpClient */
	private $httpClient;

	/** @var array Optional options for CURL HTTP request, like timeouts etc. */
	private $options;

	/**
	 * @param string $username
	 * @param string $password
	 * @param string $apiUrl
	 * @param IHttpClient $httpClient
	 * @param array $options Optional options for HTTP requests, like timeouts etc.
	 */
	public function __construct($username, $password, $apiUrl, IHttpClient $httpClient, $options = [])
	{
		$this->username = $username;
		$this->password = $password;
		$this->apiUrl = rtrim($apiUrl, '/');
		$this->httpClient = $httpClient;
		$this->options = $options;
	}


	/**
	 * @param string $path
	 * @param string $resourcesKey
	 * @param array $parameters
	 * @param int $options
	 * @return array
	 */
	public function getResources($path, $resourcesKey, array $parameters = [], $options = 0)
	{
		if ($parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}
		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourcesResponseData($httpResponse, $resourcesKey, $options);
		}

		throw new InvalidStatusCodeException($this->extractInvalidStatusCodeException($httpResponse, $options));
	}


	/**
	 * @param string $path
	 * @param string|int $id
	 * @param array $parameters
	 * @param int $options
	 * @return array|null
	 */
	public function getResource($path, $id, array $parameters = [], $options = 0)
	{
		$this->validateId($id, $options);

		$path .= '/' . $id;

		if ($parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse, $options);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			return null;
		}

		throw new InvalidStatusCodeException($this->extractInvalidStatusCodeException($httpResponse, $options));
	}


	/**
	 * @param string $path
	 * @param array $parameters
	 * @param int $options
	 * @return array
	 */
	public function getSingularResource($path, array $parameters = [], $options = 0)
	{
		if ($parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse, $options);
		}

		throw new InvalidStatusCodeException($this->extractInvalidStatusCodeException($httpResponse, $options));
	}


	/**
	 * @param string $path
	 * @param array $data
	 * @param int $options
	 * @return array
	 */
	public function createResource($path, array $data, $options = 0)
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, $path, $data);
		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse, $options);
		}

		throw new InvalidStatusCodeException($this->extractInvalidStatusCodeException($httpResponse, $options));
	}


	/**
	 * @param string $path
	 * @param int $id
	 * @param array $data
	 * @param int $options
	 * @return array
	 */
	public function updateResource($path, $id, array $data, $options = 0)
	{
		$this->validateId($id, $options);

		$httpResponse = $this->sendHttpRequest(HttpMethod::PUT, $path . '/' . $id, $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse, $options);
		}

		throw new InvalidStatusCodeException($this->extractInvalidStatusCodeException($httpResponse, $options));
	}

	/**
	 * @param HttpResponse $httpResponse
	 * @param int $options
	 * @return string
	 */
	private function extractInvalidStatusCodeException($httpResponse, $options = 0)
	{
		try {
			$json = $this->getResourceResponseData($httpResponse, $options);
			$error = isset($json['message']) && !empty($json['message']) ? $json['message'] : json_encode($json, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
		} catch (InvalidResponseBodyException $e) {
			$error = $e->getMessage();
		}

		$error = '[' . $httpResponse->getStatusCode() . '] ' . $error;

		return $error;
	}

	/**
	 * @param string $path
	 * @param int $id
	 * @param int $options
	 * @return void
	 */
	public function deleteResource($path, $id, $options = 0)
	{
		$this->validateId($id, $options);

		$httpResponse = $this->sendHttpRequest(HttpMethod::DELETE, $path . '/' . $id);

		if ($httpResponse->getStatusCode() !== HttpStatusCode::S200_OK) {
			throw new InvalidStatusCodeException($this->extractInvalidStatusCodeException($httpResponse, $options));
		}
	}


	/**
	 * @param int $id
	 * @return string|null
	 */
	public function getInvoicePdf($id)
	{
		$this->validateId($id, 0);
		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, '/invoices/' . $id . '.pdf');

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $httpResponse->getBody();
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			return null;
		}

		throw new InvalidStatusCodeException($this->extractInvalidStatusCodeException($httpResponse));
	}


	/**
	 * @param string|int $id
	 * @param int $options
	 * @return void
	 */
	private function validateId($id, $options)
	{
		if ($options & FapiRestClientOptions::STRING_KEY) {
			if (!is_string($id)) {
				throw new InvalidArgumentException('Parameter id must be a string.');
			}
		} else {
			if (!is_int($id)) {
				throw new InvalidArgumentException('Parameter id must be an integer.');
			}
		}
	}


	/**
	 * @param string $method
	 * @param string $path
	 * @param array|null $data
	 * @param array $headers
	 * @return HttpResponse
	 */
	private function sendHttpRequest($method, $path, $data = null, array $headers = [])
	{
		$url = $this->apiUrl . $path;

		if (!isset($headers['Content-Type'])) {
			$headers['Content-Type'] = 'application/json';
		}

		if (!isset($headers['Accept'])) {
			$headers['Accept'] = 'application/json';
		}

		$options = [
			'auth' => [$this->username, $this->password],
			'headers' => $headers,
		];

		if ($data !== null) {
			$options['json'] = $data;
		}

		//Use global client HTTP options.
		$options = array_merge($options, $this->options);

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
	 * @param string $resourcesKey
	 * @param int $options
	 * @return array
	 */
	private function getResourcesResponseData(HttpResponse $httpResponse, $resourcesKey, $options)
	{
		$responseData = $this->getResponseData($httpResponse);

		if (!is_array($responseData)) {
			throw new InvalidResponseBodyException('Response data is not an array.');
		}

		if (!isset($responseData[$resourcesKey])) {
			throw new InvalidResponseBodyException('Response data does not contain attribute with resources.');
		}

		$resources = $responseData[$resourcesKey];

		if (!is_array($resources)) {
			throw new InvalidResponseBodyException('Resources must be an array.');
		}

		foreach ($resources as $resource) {
			$this->validateResource($resource, $options);
		}

		return $resources;
	}


	/**
	 * @param HttpResponse $httpResponse
	 * @param int $options
	 * @return array|string
	 */
	private function getResourceResponseData($httpResponse, $options)
	{
		$resource = $this->getResponseData($httpResponse);

		$this->validateResource($resource, $options);

		return $resource;
	}


	/**
	 * @param array|string $resource
	 * @param int $options
	 * @return void
	 */
	private function validateResource($resource, $options)
	{
		if ($options & FapiRestClientOptions::STRING_RESOURCE) {
			if (!is_string($resource)) {
				throw new InvalidResponseBodyException('Resource must be a string.');
			}
		}
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
