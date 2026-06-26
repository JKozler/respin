<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Rest;

use Mioweb\HttpClient\HttpMethod;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\Utils\Json;
use Mioweb\MiowebAdminClient\Rest\Exceptions\InvalidResponseBodyException;
use Mioweb\MiowebAdminClient\Rest\Exceptions\InvalidStatusCodeException;

abstract class MiowebAdminBaseRestClient
{

	/**
	 * @param string $method
	 * @param string $path
	 * @param mixed[]|null $data
	 * @return HttpResponse
	 */
	abstract public function sendHttpRequest(string $method, string $path, ?array $data = null): HttpResponse;

	/**
	 * @param string $path
	 * @param string|null $resourcesKey
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getResources(string $path, ?string $resourcesKey, array $parameters = []): array
	{
		if ((bool) $parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourcesResponseData($httpResponse, $resourcesKey);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * @param string $path
	 * @param int $id
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getResource(string $path, int $id, array $parameters = []): ?array
	{
		$path .= '/' . $id;

		if ((bool) $parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			return null;
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * @param string $path
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getSingularResource(string $path, array $parameters = []): array
	{
		$path = $this->addQueryParameters($path, $parameters);

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * @param string $path
	 * @param mixed[] $data
	 * @param int $successStatus
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function createResource(
		string $path,
		array $data,
		int $successStatus = HttpStatusCode::S201_CREATED,
		array $parameters = []
	): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, $this->addQueryParameters($path, $parameters), $data);

		if ($httpResponse->getStatusCode() === $successStatus) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * @param string $path
	 * @param int $id
	 * @param mixed[] $data
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function updateResource(string $path, int $id, array $data, array $parameters = []): array
	{
		$path = $this->addQueryParameters($path . '/' . $id, $parameters);

		$httpResponse = $this->sendHttpRequest(HttpMethod::PUT, $path, $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * @param string $path
	 * @param int $id
	 * @param mixed[] $data
	 * @param mixed[] $parameters
	 * @return void
	 */
	public function deleteResource(string $path, int $id, array $data = [], array $parameters = []): void
	{
		$path = $this->addQueryParameters($path . '/' . $id, $parameters);

		$httpResponse = $this->sendHttpRequest(HttpMethod::DELETE, $path, $data);

		if (!\in_array($httpResponse->getStatusCode(), [HttpStatusCode::S200_OK, HttpStatusCode::S204_NO_CONTENT], true)) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			throw new InvalidStatusCodeException($errorMessage);
		}
	}

	/**
	 * Check if passed value is an integer.
	 *
	 * @param string|int $id
	 * @param string $paramName Name of the parameter. This is used in error message.
	 * @return void
	 */
	public function validateId($id, string $paramName = 'id'): void
	{
		if (!\is_int($id)) {
			throw new \InvalidArgumentException('Parameter ' . $paramName . ' must be an integer.');
		}
	}

	/**
	 * @param mixed[] $parameters
	 * @return string
	 */
	protected function formatUrlParameters(array $parameters): string
	{
		return \http_build_query($parameters, '', '&');
	}

	/**
	 * @param string $path
	 * @param mixed[] $parameters
	 * @return string
	 */
	protected function addQueryParameters(string $path, array $parameters): string
	{
		if ((bool) $parameters) {
			$path .= '?' . $this->formatUrlParameters($parameters);
		}

		return $path;
	}

	/**
	 * @param HttpResponse $httpResponse
	 * @param string|null $resourcesKey
	 * @return mixed[]
	 */
	protected function getResourcesResponseData(HttpResponse $httpResponse, ?string $resourcesKey): array
	{
		$responseData = $this->getResponseData($httpResponse);

		if ($resourcesKey !== null) {
			if (!isset($responseData[$resourcesKey])) {
				throw new InvalidResponseBodyException('Response data does not contain attribute with resources.');
			}

			$resources = $responseData[$resourcesKey];
		} else {
			$resources = $responseData;
		}

		if (!\is_array($resources)) {
			throw new InvalidResponseBodyException('Resources must be an array.');
		}

		foreach ($resources as $resource) {
			$this->validateResource($resource);
		}

		return $resources;
	}

	/**
	 * @param HttpResponse $httpResponse
	 * @return mixed[]
	 */
	protected function getResourceResponseData(HttpResponse $httpResponse): array
	{
		$resource = $this->getResponseData($httpResponse);

		$this->validateResource($resource);

		return $resource;
	}

	/**
	 * Get error message from the response, that is $response[error][message]. Validates that response is an error.
	 *
	 * @param HttpResponse $httpResponse
	 * @return string
	 * @throws InvalidResponseBodyException
	 */
	protected function getErrorMessageResponseData(HttpResponse $httpResponse): string
	{
		$errorData = $this->getErrorResponseData($httpResponse);

		if (!isset($errorData['message'])) {
			throw new InvalidResponseBodyException('Error must contain string attribute error.');
		}

		if (\is_array($errorData['message']) && \count($errorData['message']) !== 0) {
			return (string) $errorData['message'][0];
		}

		if (\is_string($errorData['message'])) {
			return $errorData['message'];
		}

		throw new InvalidResponseBodyException('Unsupported format of "error.message" field.');
	}

	/**
	 * Get field `error` content of a response, that is an error response. Validates `status===error`.
	 *
	 * @param HttpResponse $httpResponse
	 * @return mixed[]
	 * @throws InvalidResponseBodyException If not an error message
	 */
	protected function getErrorResponseData(HttpResponse $httpResponse): array
	{
		$responseData = $this->getArrayResponseData($httpResponse);

		if (!isset($responseData['status']) || $responseData['status'] !== 'error') {
			throw new InvalidResponseBodyException('Response data must contain attribute status = "error".');
		}

		if (!isset($responseData['error']) || !\is_array($responseData['error'])) {
			throw new InvalidResponseBodyException('Response data must contain an array attribute error.');
		}

		return $responseData['error'];
	}

	/**
	 * @param HttpResponse $httpResponse
	 * @return mixed[]
	 */
	protected function getArrayResponseData(HttpResponse $httpResponse): array
	{
		try {
			$responseData = $this->getResponseData($httpResponse);
		} catch (\Throwable $e) {
			throw new InvalidResponseBodyException('Response body is not a valid JSON.', 0, $e);
		}

		return $responseData;
	}

	/**
	 * @param mixed[]|string $resource
	 * @return void
	 */
	protected function validateResource($resource): void
	{
		if (!\is_array($resource)) {
			throw new InvalidResponseBodyException('Resource must be an array.');
		}
	}

	/**
	 * @param HttpResponse $httpResponse
	 * @return mixed[]
	 */
	protected function getResponseData(HttpResponse $httpResponse): array
	{
		try {
			/** @var mixed[] $result */
			$result = Json::decode($httpResponse->getBody(), Json::FORCE_ARRAY);

			return $result;
		} catch (\Throwable $e) {
			throw new InvalidResponseBodyException('Response body is not a valid JSON.', 0, $e);
		}
	}

}
