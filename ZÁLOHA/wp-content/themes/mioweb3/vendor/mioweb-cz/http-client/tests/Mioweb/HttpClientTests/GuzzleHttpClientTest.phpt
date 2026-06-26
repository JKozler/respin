<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests;

use GuzzleHttp\Cookie\CookieJar;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\HttpClient\HttpMethod;
use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\HttpClientTests\MockHttpServer\MockHttpServerRunner;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/BaseHttpClientTest.php';
require __DIR__ . '/MockHttpServer/MockHttpServerRunner.php';


class GuzzleHttpClientTest extends BaseHttpClientTest
{

	public function testSendHttpRequestWithCookies(): void
	{
		$runner = new MockHttpServerRunner();
		$httpClient = $this->httpClient;

		$runner->onStarted[] = static function (MockHttpServerRunner $runner) use ($httpClient): void {
			$cookieJar = new CookieJar();
			$httpRequest = new HttpRequest('http://127.0.0.1:1337/assign-cookie', HttpMethod::GET, [
				'cookies' => $cookieJar,
			]);
			$httpResponse = $httpClient->sendHttpRequest($httpRequest);
			$headers = $httpResponse->getHeaders();

			Assert::same(HttpStatusCode::S200_OK, $httpResponse->getStatusCode());
			Assert::same(['text/plain'], $headers['Content-Type']);
			Assert::same("OK\n", $httpResponse->getBody());

			$httpRequest = new HttpRequest('http://127.0.0.1:1337/check-cookie', HttpMethod::GET, [
				'cookies' => $cookieJar,
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

	protected function createHttpClient(): IHttpClient
	{
		return new GuzzleHttpClient();
	}

}

\run(new GuzzleHttpClientTest());
