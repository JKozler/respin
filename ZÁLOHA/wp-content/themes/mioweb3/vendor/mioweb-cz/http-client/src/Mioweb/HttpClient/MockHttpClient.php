<?php declare(strict_types=1);

namespace Mioweb\HttpClient;

use Mioweb\HttpClient\Exceptions\InvalidArgumentException;

class MockHttpClient implements IHttpClient
{

	/** @var HttpRequest[] */
	private array $httpRequests = [];

	/** @var HttpResponse[] */
	private array $httpResponses = [];

	public function add(HttpRequest $httpRequest, HttpResponse $httpResponse): void
	{
		$this->httpRequests[] = $httpRequest;
		$this->httpResponses[] = $httpResponse;
	}

	public function sendHttpRequest(HttpRequest $httpRequest): HttpResponse
	{
		if (!isset($this->httpRequests[0]) || !$this->matchHttpRequest($this->httpRequests[0], $httpRequest)) {
			throw new InvalidArgumentException('Invalid HTTP request.');
		}

		\array_shift($this->httpRequests);

		return \array_shift($this->httpResponses);
	}

	public function wereAllHttpRequestsSent(): bool
	{
		return !(bool) $this->httpRequests;
	}

	private function matchHttpRequest(HttpRequest $expected, HttpRequest $actual): bool
	{
		return $expected->getUrl() === $actual->getUrl()
			&& $expected->getMethod() === $actual->getMethod()
			&& $expected->getOptions() === $actual->getOptions();
	}

}
