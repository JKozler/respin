<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

final class SampleMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://localhost/',
				'GET',
				['headers' => ['User-Agent' => 'Nette Tester']]
			),
			new HttpResponse(
				200,
				['Content-Type' => ['text/plain']],
				"It works!\n"
			)
		);
	}

}
