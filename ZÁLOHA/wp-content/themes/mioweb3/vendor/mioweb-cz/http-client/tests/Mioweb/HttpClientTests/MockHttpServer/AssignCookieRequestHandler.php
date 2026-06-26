<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests\MockHttpServer;

use React;

class AssignCookieRequestHandler
{

	/**
	 * @param React\Http\Request $request
	 * @param React\Http\Response $response
	 * @throws \Exception
	 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
	 */
	public function handleRequest(React\Http\Request $request, React\Http\Response $response): void
	{
		$response->writeHead(200, [
			'Content-Type' => 'text/plain',
			'Set-Cookie' => 'sample-name=sample-value; path=/',
		]);
		$response->end("OK\n");
	}

}
