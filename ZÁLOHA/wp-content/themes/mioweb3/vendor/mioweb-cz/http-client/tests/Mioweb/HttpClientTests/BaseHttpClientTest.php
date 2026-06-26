<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests;

use Mioweb\HttpClient\Exceptions\TimeLimitExceededException;
use Mioweb\HttpClient\HttpMethod;
use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\HttpClientTests\MockHttpServer\Exceptions\HttpServerException;
use Mioweb\HttpClientTests\MockHttpServer\MockHttpServerRunner;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

abstract class BaseHttpClientTest extends TestCase
{

	protected IHttpClient $httpClient;

	abstract protected function createHttpClient(): IHttpClient;

	/**
	 * @dataProvider getSampleHttpRequests
	 * @param string $url
	 * @param string $method
	 * @param mixed[] $options
	 * @param string $expectedBody
	 */
	public function testSendHttpRequest(string $url, string $method, array $options, string $expectedBody): void
	{
		$runner = new MockHttpServerRunner();
		$httpRequest = new HttpRequest($url, $method, $options);
		$httpClient = $this->httpClient;

		$runner->onStarted[] = static function (MockHttpServerRunner $runner) use (
			$httpClient,
			$httpRequest,
			$expectedBody
		): void {
			$httpResponse = $httpClient->sendHttpRequest($httpRequest);
			$headers = $httpResponse->getHeaders();

			Assert::same(HttpStatusCode::S200_OK, $httpResponse->getStatusCode());
			Assert::same(['text/plain'], $headers['Content-Type']);
			Assert::same($expectedBody, $httpResponse->getBody());

			$runner->stop();
		};

		$runner->run();
	}

	/** @return mixed[][] */
	public function getSampleHttpRequests(): array
	{
		return [
			[
				'http://127.0.0.1:1337/login',
				HttpMethod::POST,
				[
					'headers' => [
						'X-Foo' => [
							'Bar',
							'Baz',
						],
					],
					'form_params' => [
						'username' => 'admin',
						'password' => 'xxx',
					],
				],
				"OK\n",
			],
			[
				'http://127.0.0.1:1337/api',
				HttpMethod::POST,
				[
					'headers' => [
						'Content-Type' => 'application/json',
						'User-Agent' => 'ApiClient/1.0',
					],
					'auth' => ['admin', 'xxx'],
					'body' => '{"foo":"bar"}',
				],
				"OK\n",
			],
			[
				'http://127.0.0.1:1337/api',
				HttpMethod::POST,
				[
					'headers' => [
						'User-Agent' => 'ApiClient/1.0',
					],
					'auth' => ['admin', 'xxx'],
					'json' => [
						'foo' => 'bar',
					],
				],
				"OK\n",
			],
			[
				'http://127.0.0.1:1337/empty',
				HttpMethod::GET,
				[],
				'',
			],
		];
	}

	public function testSendHttpRequestWithNotExceededTimeout(): void
	{
		$runner = new MockHttpServerRunner();
		$httpClient = $this->httpClient;

		$runner->onStarted[] = static function (MockHttpServerRunner $runner) use ($httpClient): void {
			$httpRequest = new HttpRequest('http://127.0.0.1:1337/delayed', HttpMethod::GET, [
				'timeout' => 2,
			]);

			$httpResponse = $httpClient->sendHttpRequest($httpRequest);
			$headers = $httpResponse->getHeaders();

			Assert::same(HttpStatusCode::S200_OK, $httpResponse->getStatusCode());
			Assert::same(['text/plain'], $headers['Content-Type']);
			Assert::same("OK\n", $httpResponse->getBody());

			$runner->stop();
		};

		$runner->run();
	}

	public function testSendHttpRequestWithExceededTimeout(): void
	{
		$runner = new MockHttpServerRunner();
		$httpClient = $this->httpClient;

		$runner->onStarted[] = static function (MockHttpServerRunner $runner) use ($httpClient): void {
			$httpRequest = new HttpRequest('http://127.0.0.1:1337/delayed', HttpMethod::GET, [
				'timeout' => 1,
			]);

			Assert::exception(static function () use ($httpClient, $httpRequest): void {
				$httpClient->sendHttpRequest($httpRequest);
			}, TimeLimitExceededException::class);

			$runner->stop();
		};

		$runner->run();
	}

	public function testSendHttpRequestWithNotExceededConnectTimeout(): void
	{
		$runner = new MockHttpServerRunner();
		$httpClient = $this->httpClient;

		$runner->onStarted[] = static function (MockHttpServerRunner $runner) use ($httpClient): void {
			$httpRequest = new HttpRequest('http://127.0.0.1:1337/delayed', HttpMethod::GET, [
				'connect_timeout' => 1,
			]);

			$httpResponse = $httpClient->sendHttpRequest($httpRequest);
			$headers = $httpResponse->getHeaders();

			Assert::same(HttpStatusCode::S200_OK, $httpResponse->getStatusCode());
			Assert::same(['text/plain'], $headers['Content-Type']);
			Assert::same("OK\n", $httpResponse->getBody());

			$runner->stop();
		};

		$runner->run();
	}

	public function testSendHttpRequestWithExceededConnectTimeout(): void
	{
		$httpClient = $this->httpClient;
		$httpRequest = new HttpRequest('http://127.0.0.2/', HttpMethod::GET, [
			'connect_timeout' => 1,
		]);

		Assert::exception(static function () use ($httpClient, $httpRequest): void {
			$httpClient->sendHttpRequest($httpRequest);
		}, HttpServerException::class);
	}

	protected function setUp(): void
	{
		parent::setUp();

		Environment::lock('MockHttpServer', \LOCKS_DIR);
		$this->httpClient = $this->createHttpClient();
	}

}
