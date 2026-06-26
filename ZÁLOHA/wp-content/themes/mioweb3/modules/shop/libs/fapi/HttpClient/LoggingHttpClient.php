<?php

namespace MwShop\HttpClient;

use MwShop\HttpClient\Utils\Json;
use MwShop\HttpClient\Utils\JsonException;
use Tracy\ILogger;

class LoggingHttpClient implements IHttpClient
{

	/** @var IHttpClient */
	private $httpClient;

	/** @var ILogger */
	private $logger;

	public function __construct(IHttpClient $httpClient, ILogger $logger = null)
	{
		$this->httpClient = $httpClient;
		$this->logger = $logger;
	}


	public function sendHttpRequest(HttpRequest $httpRequest)
	{
		$startedAt = microtime(true);

		try {
			$httpResponse = $this->httpClient->sendHttpRequest($httpRequest);
		} catch (HttpClientException $e) {
			$this->logFailedRequest($httpRequest, $e, microtime(true) - $startedAt);

			throw $e;
		}

		$this->logSuccessfulRequest($httpRequest, $httpResponse, microtime(true) - $startedAt);

		return $httpResponse;
	}


	/**
	 * @param HttpRequest $httpRequest
	 * @param HttpResponse $httpResponse
	 * @param float $elapsedTime
	 * @return void
	 */
	private function logSuccessfulRequest(HttpRequest $httpRequest, HttpResponse $httpResponse, $elapsedTime)
	{
		$this->log(
			'{' . $this->dumpValue($httpResponse->getStatusCode()) . '} '
			. $this->dumpHttpRequest($httpRequest)
			. $this->dumpHttpResponse($httpResponse)
			. $this->dumpElapsedTime($elapsedTime),
			ILogger::INFO
		);
	}


	/**
	 * @param HttpRequest $httpRequest
	 * @param HttpClientException $exception
	 * @param float $elapsedTime
	 * @return void
	 */
	private function logFailedRequest(HttpRequest $httpRequest, HttpClientException $exception, $elapsedTime)
	{
		$this->log(
			'{exc=' . $this->dumpValue(get_class($exception)) . '} '
			. $this->dumpHttpRequest($httpRequest)
			. $this->dumpException($exception)
			. $this->dumpElapsedTime($elapsedTime),
			ILogger::WARNING
		);
	}


	/**
	 * @param HttpRequest $httpRequest
	 * @return string
	 */
	private function dumpHttpRequest(HttpRequest $httpRequest)
	{
		return ''
		. ' Request method: ' . $this->dumpValue($httpRequest->getMethod())
		. ' Request URL: ' . $this->dumpValue($httpRequest->getUrl())
		. ' Request options: ' . $this->dumpValue($httpRequest->getOptions());
	}


	/**
	 * @param HttpResponse $httpResponse
	 * @return string
	 */
	private function dumpHttpResponse(HttpResponse $httpResponse)
	{
		$body = $httpResponse->getBody();
		try {
			$body = Json::decode($body);
		} catch (\Exception $e) {
		}

		return ' Response status code: ' . $this->dumpValue($httpResponse->getStatusCode())
		. ' Response headers: ' . $this->dumpValue($httpResponse->getHeaders())
		. ' Response body: ' . $this->dumpValue($body);
	}


	/**
	 * @param \Exception $exception
	 * @return string
	 */
	private function dumpException(\Exception $exception)
	{
		$dump = ' Exception type: ' . $this->dumpValue(get_class($exception))
		. ' Exception message: ' . $this->dumpValue($exception->getMessage());

		if ($exception->getPrevious() !== null) {
			$previousException = $exception->getPrevious();

			$dump .= ' Previous exception type: ' . $this->dumpValue(get_class($previousException))
			. ' Previous exception message: ' . $this->dumpValue($previousException->getMessage());
		}

		return $dump;
	}


	/**
	 * @param float $elapsedTime
	 * @return string
	 */
	private function dumpElapsedTime($elapsedTime)
	{
		return ' Elapsed time: ' . $this->dumpValue($elapsedTime);
	}


	/**
	 * @param mixed $value
	 * @return string
	 */
	private function dumpValue($value)
	{
		try {
			return Json::encode($value, JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
		} catch (JsonException $e) {
			return '(serialized) ' . base64_encode(serialize($value));
		}
	}


	/**
	 * @param string $message
	 * @param string $priority
	 * @return void
	 */
	private function log($message, $priority)
	{
		mwlog(
			MWLS_FAPI,
			'HTTP REQ [' . ($priority == ILogger::INFO ? 'OK' : 'FAIL') . '] >>>> ' . $message,
			($priority == ILogger::INFO ? MWLL_INFO : MWLL_ERROR),
			'fapi'
		);
	}
}
