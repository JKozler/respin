<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests\MockHttpServer;

use React;

class DelayedRequestHandler
{

	private React\EventLoop\LoopInterface $eventLoop;

	public function __construct(React\EventLoop\LoopInterface $eventLoop)
	{
		$this->eventLoop = $eventLoop;
	}

	/**
	 * @param React\Http\Request $request
	 * @param React\Http\Response $response
	 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
	 */
	public function handleRequest(React\Http\Request $request, React\Http\Response $response): void
	{
		$this->eventLoop->addTimer(1.0, static function () use ($response): void {
			$response->writeHead(200, ['Content-Type' => 'text/plain']);
			$response->end("OK\n");
		});
	}

}
