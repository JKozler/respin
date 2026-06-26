<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests\MockHttpServer;

use Mioweb\HttpClientTests\MockHttpServer\Exceptions\InvalidHttpRequestException;
use React;

class CheckCookieRequestHandler
{

	public function handleRequest(React\Http\Request $request, React\Http\Response $response): void
	{
		$headers = $request->getHeaders();

		if (!isset($headers['Cookie'])) {
			throw new InvalidHttpRequestException('Header Cookie is not present.');
		}

		if ($headers['Cookie'] !== 'sample-name=sample-value') {
			throw new InvalidHttpRequestException('Header Cookie has an unexpected value.');
		}

		$response->writeHead(200, ['Content-Type' => 'text/plain']);
		$response->end("OK\n");
	}

}
