<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient\Rest;

use Mioweb\HttpClient\Exceptions\HttpClientException;
use Mioweb\HttpClient\HttpMethod;
use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\HttpClient\RedirectHelper;
use Mioweb\HttpClient\Utils\Json;
use Mioweb\MPohodaClient\AuthorizationException;
use Mioweb\MPohodaClient\NotFoundException;
use Mioweb\MPohodaClient\ValidationException;

class MPohodaRestClient
{

	private string $apiKey;

	private string $apiUrl;

	private IHttpClient $httpClient;

	public function __construct(string $apiKey, string $apiUrl, IHttpClient $httpClient)
	{
		$this->apiKey = $apiKey;
		$this->apiUrl = \rtrim($apiUrl, '/');
		$this->httpClient = $httpClient;
	}

	/**
	 * @param array<mixed> $parameters
	 * @return array<mixed>
	 */
	public function getResources(string $path, array $parameters = []): array
	{
		if ($parameters !== []) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourcesResponseData($httpResponse);
		}

		$this->processErrorStatusCodeIfNeeded($httpResponse);

		throw new InvalidStatusCodeException('Api return invalid status code: ' . $httpResponse->getStatusCode());
	}

	/**
	 * @param string|int $id
	 * @param array<mixed> $parameters
	 * @return array<mixed>|null
	 */
	public function getResource(string $path, $id, array $parameters = []): ?array
	{
		$path .= '/' . $id;

		if ($parameters !== []) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			return null;
		}

		$this->processErrorStatusCodeIfNeeded($httpResponse);

		throw new InvalidStatusCodeException('Api return invalid status code: ' . $httpResponse->getStatusCode());
	}

	/**
	 * @param array<mixed> $parameters
	 * @return array<mixed>
	 */
	public function getSingularResource(string $path, array $parameters = []): array
	{
		if ($parameters !== []) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$this->processErrorStatusCodeIfNeeded($httpResponse);

		throw new InvalidStatusCodeException('Api return invalid status code: ' . $httpResponse->getStatusCode());
	}

	/**
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public function createResource(string $path, array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, $path, $data);

		if (in_array($httpResponse->getStatusCode(), [HttpStatusCode::S200_OK, HttpStatusCode::S201_CREATED], true)) {
			return $this->getResourceResponseData($httpResponse);
		}

		$this->processErrorStatusCodeIfNeeded($httpResponse);

		throw new InvalidStatusCodeException('Api return invalid status code: ' . $httpResponse->getStatusCode());
	}

	/**
	 * @param int|string $id
	 * @param array<mixed> $data
	 * @return array<mixed>
	 */
	public function updateResource(string $path, $id, array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::PUT, $path . '/' . $id, $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$this->processErrorStatusCodeIfNeeded($httpResponse);

		throw new InvalidStatusCodeException('Api return invalid status code: ' . $httpResponse->getStatusCode());
	}

	/** @param int|string $id */
	public function deleteResource(string $path, $id): void
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::DELETE, $path . '/' . $id);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return;
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			return;
		}

		$this->processErrorStatusCodeIfNeeded($httpResponse);

		throw new InvalidStatusCodeException('Api return invalid status code: ' . $httpResponse->getStatusCode());
	}

	/**
	 * @param array<mixed>|null $data
	 * @param array<mixed> $headers
	 */
	private function sendHttpRequest(
		string $method,
		string $path,
		?array $data = null,
		array $headers = []
	): HttpResponse
	{
		$url = $this->apiUrl . $path;

		if (!isset($headers['Content-Type'])) {
			$headers['Content-Type'] = 'application/json';
		}

		if (!isset($headers['Accept'])) {
			$headers['Accept'] = 'application/json';
		}

		$headers['Api-Key'] = $this->apiKey;

		$options = [
			'headers' => $headers,
		];

		if ($data !== null) {
			$options['json'] = $data;
		}

		try {
			$httpRequest = new HttpRequest($url, $method, $options);
			$httpResponse = $this->httpClient->sendHttpRequest($httpRequest);

			return RedirectHelper::followRedirects($this->httpClient, $httpResponse, 3);
		} catch (HttpClientException $e) {
			throw new RestClientException('Failed to send an HTTP request.', 0, $e);
		}
	}

	/** @param array<mixed> $parameters */
	private function formatUrlParameters(array $parameters): string
	{
		return http_build_query($parameters);
	}

	/** @return array<mixed> */
	private function getResourcesResponseData(HttpResponse $httpResponse): array
	{
		$responseData = $this->getResponseData($httpResponse);

		if (
			!isset($responseData['Data'])
			|| !\is_array($responseData['Data'])
			|| !isset($responseData['Data']['Items'])
		) {
			throw new InvalidResponseBodyException('Response data does not contain attribute [Data][Items].');
		}

		$resources = $responseData['Data']['Items'];

		if (!is_array($resources)) {
			throw new InvalidResponseBodyException('resources must be an array.');
		}

		foreach ($resources as $key => $resource) {
			$resources[$key] = $this->validateResource($resource);
		}

		return $resources;
	}

	/** @return array<mixed> */
	private function getResourceResponseData(HttpResponse $httpResponse): array
	{
		$resource = $this->getResponseData($httpResponse);

		return $this->validateResource($resource);
	}

	/**
	 * @param mixed $resource
	 * @return array<mixed>
	 */
	private function validateResource($resource): array
	{
		\assert(\is_array($resource));

		return $resource;
	}

	private function getErrorMessage(HttpResponse $httpResponse): string
	{
		$responseData = $this->getResponseData($httpResponse);
		$errorArr = $responseData['Error'] ?? null;

		if (!\is_array($errorArr)) {
			return '';
		}

		return $errorArr['Message'] ?? '';
	}

	/** @return array<mixed> */
	private function getResponseData(HttpResponse $httpResponse): array
	{
		try {
			return (array) Json::decode($httpResponse->getBody(), Json::FORCE_ARRAY);
		} catch (\Throwable $e) {
			throw new InvalidResponseBodyException('Response body is not a valid JSON.', 0, $e);
		}
	}

	private function processErrorStatusCodeIfNeeded(HttpResponse $httpResponse): void
	{
		$message = $this->getErrorMessage($httpResponse);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S400_BAD_REQUEST) {
			throw new ValidationException($message);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S401_UNAUTHORIZED) {
			throw new AuthorizationException($message);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			throw new NotFoundException($message);
		}
	}

}
