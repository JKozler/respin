<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests;

use GuzzleHttp\Cookie\CookieJar;
use Mioweb\HttpClient\CurlHttpClient;
use Mioweb\HttpClient\HttpMethod;
use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\IHttpClient;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/BaseHttpClientTest.php';
require __DIR__ . '/MockHttpServer/MockHttpServerRunner.php';


class CurlHttpClientTest extends BaseHttpClientTest
{

	public function testThrowingOfNotSupportedExceptionWhenSendingHttpRequestWithCookies(): void
	{
		$httpClient = $this->httpClient;
		$cookieJar = new CookieJar();
		$httpRequest = new HttpRequest('https://example.com', HttpMethod::GET, [
			'cookies' => $cookieJar,
		]);

		Assert::exception(static function () use ($httpClient, $httpRequest): void {
			$httpClient->sendHttpRequest($httpRequest);
		}, 'Mioweb\HttpClient\Exceptions\NotSupportedException', 'CurlHttpClient does not support option cookies.');
	}

	protected function createHttpClient(): IHttpClient
	{
		return new CurlHttpClient();
	}

}

\run(new CurlHttpClientTest());
