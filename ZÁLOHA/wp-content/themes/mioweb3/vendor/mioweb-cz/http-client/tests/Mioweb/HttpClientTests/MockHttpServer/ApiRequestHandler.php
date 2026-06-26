<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests\MockHttpServer;

use Mioweb\HttpClientTests\MockHttpServer\Exceptions\InvalidHttpRequestException;
use React;

class ApiRequestHandler
{

	public function handleRequest(React\Http\Request $request, React\Http\Response $response): void
	{
		$method = $request->getMethod();
		$headers = $request->getHeaders();

		if ($method !== 'POST') {
			throw new InvalidHttpRequestException('Unexpected HTTP method.');
		}

		if (!isset($headers['Content-Type'])) {
			throw new InvalidHttpRequestException('Header Content-Type is not present.');
		}

		if ($headers['Content-Type'] !== 'application/json') {
			throw new InvalidHttpRequestException('Header Content-Type has an unexpected value.');
		}

		if (!isset($headers['User-Agent'])) {
			throw new InvalidHttpRequestException('Header User-Agent is not present.');
		}

		if ($headers['User-Agent'] !== 'ApiClient/1.0') {
			throw new InvalidHttpRequestException('Header User-Agent has an unexpected value.');
		}

		if (!isset($headers['Authorization'])) {
			throw new InvalidHttpRequestException('Header Authorization is not present.');
		}

		if ($headers['Authorization'] !== 'Basic ' . \base64_encode('admin:xxx')) {
			throw new InvalidHttpRequestException('Header Authorization has an unexpected value.');
		}

		$buffer = '';
		$request->on('data', static function ($data) use (&$buffer, $response): void {
			$buffer .= $data;
			$expectedData = '{"foo":"bar"}';

			if (\strlen($buffer) >= \strlen($expectedData)) {
				if ($buffer !== $expectedData) {
					throw new InvalidHttpRequestException('Response body is not valid.');
				}

				$response->writeHead(200, ['Content-Type' => 'text/plain']);
				$response->end("OK\n");
			}
		});
	}

}
