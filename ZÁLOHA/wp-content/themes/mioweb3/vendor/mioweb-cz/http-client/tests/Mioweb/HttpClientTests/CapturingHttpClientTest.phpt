<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\HttpMethod;
use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\MockHttpClient;
use Tester\Assert;
use Tester\FileMock;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';


class CapturingHttpClientTest extends TestCase
{

	public function testWriteToPhpFile(): void
	{
		$mockHttpRequest = new HttpRequest(
			'http://localhost/',
			HttpMethod::GET,
			[
				'headers' => [
					'User-Agent' => 'Nette Tester',
				],
			],
		);

		$mockHttpResponse = new HttpResponse(
			HttpStatusCode::S200_OK,
			[
				'Content-Type' => [
					'text/plain',
				],
			],
			"It works!\n",
		);

		$mockHttpClient = new MockHttpClient();
		$mockHttpClient->add($mockHttpRequest, $mockHttpResponse);

		$capturingHttpClient = new CapturingHttpClient($mockHttpClient);
		$capturingHttpClient->sendHttpRequest($mockHttpRequest);

		$fileName = FileMock::create('', '.php');
		$capturingHttpClient->writeToPhpFile(
			$fileName,
			'Mioweb\\HttpClientTests\\MockHttpClients\\SampleMockHttpClient',
		);

		$expected = \file_get_contents(__DIR__ . '/MockHttpClients/SampleMockHttpClient.php');
		$actual = \file_get_contents($fileName);
		Assert::same($expected, $actual);
	}

}

\run(new CapturingHttpClientTest());
