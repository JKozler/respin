<?php declare(strict_types=1);

namespace Mioweb\HttpClient;

use Mioweb\HttpClient\Exceptions\HttpClientException;
use Mioweb\HttpClient\Utils\Exceptions\JsonException;
use Mioweb\HttpClient\Utils\Json;
use Mioweb\Utils\Sanitizer;
use Nette\Http\Url;
use Tracy\ILogger;
use function array_search;

class LoggingHttpClient implements IHttpClient
{

	private IHttpClient $httpClient;

	private ILogger $logger;

	/** @var string[] */
	private array $sanitizedFieldNames;

	public function __construct(IHttpClient $httpClient, ILogger $logger)
	{
		$this->httpClient = $httpClient;
		$this->logger = $logger;

		$this->sanitizedFieldNames = Sanitizer::DEFAULT_SENSITIVE_FIELD_NAMES;
	}

	public function sendHttpRequest(HttpRequest $httpRequest): HttpResponse
	{
		$startedAt = \microtime(true);

		try {
			$httpResponse = $this->httpClient->sendHttpRequest($httpRequest);
		} catch (HttpClientException $e) {
			$this->logFailedRequest($httpRequest, $e, \microtime(true) - $startedAt);

			throw $e;
		}

		$this->logSuccessfulRequest($httpRequest, $httpResponse, \microtime(true) - $startedAt);

		return $httpResponse;
	}

	public function addFieldNameToSanitize(string $fieldName): void
	{
		$this->sanitizedFieldNames[] = $fieldName;
	}

	public function removeFieldNameToSanitize(string $fieldName): void
	{
		$key = array_search($fieldName, $this->sanitizedFieldNames, true);

		if ($key !== false) {
			unset($this->sanitizedFieldNames[$key]);
		}
	}

	private function logSuccessfulRequest(
		HttpRequest $httpRequest,
		HttpResponse $httpResponse,
		float $elapsedTime
	): void
	{
		$this->log('an HTTP request has been sent.'
			. $this->dumpHttpRequest($httpRequest)
			. $this->dumpHttpResponse($httpResponse)
			. $this->dumpElapsedTime($elapsedTime), ILogger::INFO);
	}

	private function logFailedRequest(
		HttpRequest $httpRequest,
		HttpClientException $exception,
		float $elapsedTime
	): void
	{
		$this->log('an HTTP request failed.'
			. $this->dumpHttpRequest($httpRequest)
			. $this->dumpException($exception)
			. $this->dumpElapsedTime($elapsedTime), ILogger::WARNING);
	}

	private function dumpHttpRequest(HttpRequest $httpRequest): string
	{
		return ' Request URL: ' . $this->dumpUrl($httpRequest->getUrl())
			. ' Request method: ' . $this->dumpValue($httpRequest->getMethod())
			. ' Request options: ' . $this->dumpDataValue($httpRequest->getOptions());
	}

	private function dumpHttpResponse(HttpResponse $httpResponse): string
	{
		return ' Response status code: ' . $this->dumpValue($httpResponse->getStatusCode())
			. ' Response headers: ' . $this->dumpValue($httpResponse->getHeaders())
			. ' Response body: ' . $this->dumpDataValue($httpResponse->getBody());
	}

	private function dumpException(\Throwable $exception): string
	{
		$dump = ' Exception type: ' . $this->dumpValue(\get_class($exception))
			. ' Exception message: ' . $this->dumpValue($exception->getMessage());

		if ($exception->getPrevious() !== null) {
			$previousException = $exception->getPrevious();

			$dump .= ' Previous exception type: ' . $this->dumpValue(\get_class($previousException))
				. ' Previous exception message: ' . $this->dumpValue($previousException->getMessage());
		}

		return $dump;
	}

	private function dumpElapsedTime(float $elapsedTime): string
	{
		return ' Elapsed time: ' . $this->dumpValue($elapsedTime);
	}

	private function dumpUrl(string $url): string
	{
		$url = Sanitizer::sanitize(new Url($url), $this->sanitizedFieldNames);

		try {
			return Json::encode($url, \JSON_UNESCAPED_UNICODE);
		} catch (JsonException $e) {
			return '(serialized) ' . \base64_encode(\serialize($url));
		}
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	private function dumpValue($value): string
	{
		try {
			return Json::encode($value, \JSON_UNESCAPED_UNICODE);
		} catch (JsonException $e) {
			return '(serialized) ' . \base64_encode(\serialize($value));
		}
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	private function dumpDataValue($value): string
	{
		if (\is_array($value)) {
			$value = Sanitizer::sanitize($value, $this->sanitizedFieldNames);
		}

		try {
			$json = Json::encode($value, \JSON_UNESCAPED_UNICODE);

			foreach ($this->sanitizedFieldNames as $key) {
				$json = \preg_replace('/\\\\\"' . $key . '\\\\\":.*?(,|})/', '\\"' . $key . '\\":\\"xxx\\"$1', $json);
			}

			return $json;
		} catch (JsonException $e) {
			return '(serialized) ' . \base64_encode(\serialize($value));
		}
	}

	private function log(string $message, string $priority): void
	{
		$this->logger->log('Mioweb\HttpClient: ' . $message, $priority);
	}

}
