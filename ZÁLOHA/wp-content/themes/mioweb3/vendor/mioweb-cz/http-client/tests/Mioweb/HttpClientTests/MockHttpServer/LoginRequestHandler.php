<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests\MockHttpServer;

use Mioweb\HttpClientTests\MockHttpServer\Exceptions\InvalidHttpRequestException;
use React;

class LoginRequestHandler
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

		if ($headers['Content-Type'] !== 'application/x-www-form-urlencoded') {
			throw new InvalidHttpRequestException('Header Content-Type has an unexpected value.');
		}

		if (!isset($headers['X-Foo'])) {
			throw new InvalidHttpRequestException('Header X-Foo is not present.');
		}

		if ($headers['X-Foo'] !== ['Bar', 'Baz']) {
			throw new InvalidHttpRequestException('Header X-Foo has an unexpected value.');
		}

		if (!isset($headers['Content-Length'])) {
			throw new InvalidHttpRequestException('Header Content-Length is not present.');
		}

		if ($headers['Content-Length'] !== '27') {
			throw new InvalidHttpRequestException('Header Content-Length has an unexpected value.');
		}

		$buffer = '';
		$request->on('data', static function ($data) use (&$buffer, $response): void {
			$buffer .= $data;
			$expectedData = 'username=admin&password=xxx';

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
