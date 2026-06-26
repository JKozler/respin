<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\MockHttpClient;
use Mioweb\HttpClient\RedirectHelper;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';


class RedirectHelperTest extends TestCase
{

	public function testFollowRedirects(): void
	{
		$client = $this->getMockHttpClient();
		$response = $client->sendHttpRequest(new HttpRequest('http://example.com/a'));
		$response = RedirectHelper::followRedirects($client, $response);
		Assert::true($client->wereAllHttpRequestsSent());
		Assert::same($response->getStatusCode(), HttpStatusCode::S200_OK);
		Assert::same($response->getHeaders(), ['Content-Type' => ['text/plain']]);
		Assert::same($response->getBody(), 'OK');
	}

	public function testFollowTooManyRedirects(): void
	{
		$client = $this->getMockHttpClient();
		$response = $client->sendHttpRequest(new HttpRequest('http://example.com/a'));

		Assert::exception(static function () use ($client, $response): void {
			RedirectHelper::followRedirects($client, $response, 1);
		}, 'Mioweb\HttpClient\Exceptions\TooManyRedirectsException', 'Maximum number of redirections exceeded.');
	}

	public function testFollowRedirectToInvalidUrl(): void
	{
		$client = $this->getMockHttpClientWithInvalidRedirectUrl();
		$response = $client->sendHttpRequest(new HttpRequest('http://example.com/a'));
		Assert::true($client->wereAllHttpRequestsSent());
		Assert::same($response->getStatusCode(), HttpStatusCode::S301_MOVED_PERMANENTLY);
		Assert::same($response->getHeaders(), ['Location' => ['invalid']]);
		Assert::same($response->getBody(), '');
	}

	private function getMockHttpClient(): MockHttpClient
	{
		$client = new MockHttpClient();

		$client->add(
			new HttpRequest('http://example.com/a'),
			new HttpResponse(
				HttpStatusCode::S301_MOVED_PERMANENTLY,
				['Location' => ['http://example.com/b']],
				'',
			),
		);

		$client->add(
			new HttpRequest('http://example.com/b'),
			new HttpResponse(
				HttpStatusCode::S302_FOUND,
				['Location' => ['https://example.com/c']],
				'',
			),
		);

		$client->add(
			new HttpRequest('https://example.com/c'),
			new HttpResponse(
				HttpStatusCode::S200_OK,
				['Content-Type' => ['text/plain']],
				'OK',
			),
		);

		return $client;
	}

	private function getMockHttpClientWithInvalidRedirectUrl(): MockHttpClient
	{
		$client = new MockHttpClient();

		$client->add(
			new HttpRequest('http://example.com/a'),
			new HttpResponse(
				HttpStatusCode::S301_MOVED_PERMANENTLY,
				['Location' => ['invalid']],
				'',
			),
		);

		return $client;
	}

}

\run(new RedirectHelperTest());
