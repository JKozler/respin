<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests\MockHttpServer;

use Mioweb\HttpClientTests\MockHttpServer\Exceptions\InvalidHttpRequestException;
use React;

class MockHttpServer
{

	private React\EventLoop\LoopInterface $eventLoop;

	private React\Socket\Server $socketServer;

	private React\Http\Server $httpServer;

	private ApiRequestHandler $apiRequestHandler;

	private AssignCookieRequestHandler $assignCookieRequestHandler;

	private CheckCookieRequestHandler $checkCookieRequestHandler;

	private DelayedRequestHandler $delayedRequestHandler;

	private EmptyRequestHandler $emptyRequestHandler;

	private LoginRequestHandler $loginRequestHandler;

	public function run(): void
	{
		$this->eventLoop = React\EventLoop\Factory::create();
		$this->socketServer = new React\Socket\Server($this->eventLoop);
		$this->httpServer = new React\Http\Server($this->socketServer);
		$this->apiRequestHandler = new ApiRequestHandler();
		$this->assignCookieRequestHandler = new AssignCookieRequestHandler();
		$this->checkCookieRequestHandler = new CheckCookieRequestHandler();
		$this->delayedRequestHandler = new DelayedRequestHandler($this->eventLoop);
		$this->emptyRequestHandler = new EmptyRequestHandler();
		$this->loginRequestHandler = new LoginRequestHandler();
		$this->httpServer->on('request', [$this, 'handleRequest']);
		$this->eventLoop->addTimer(0.001, [$this, 'startServer']);
		$this->eventLoop->addTimer(5.0, [$this, 'handleTimeout']);
		$this->eventLoop->run();
	}

	public function startServer(): void
	{
		$this->socketServer->listen(1337);

		\fwrite(\STDOUT, "Server running at http://127.0.0.1:1337/\n");
		\fflush(\STDOUT);
	}

	public function handleRequest(React\Http\Request $request, React\Http\Response $response): void
	{
		try {
			$this->processRequest($request, $response);
		} catch (InvalidHttpRequestException $e) {
			$response->writeHead(400, ['Content-Type' => 'text/plain']);
			$response->end($e->getMessage() . "\n");
		}
	}

	public function handleTimeout(): void
	{
		$this->socketServer->shutdown();
		$this->eventLoop->stop();

		\fwrite(\STDOUT, "Time limit exceeded\n");
		\fflush(\STDOUT);
	}

	private function processRequest(React\Http\Request $request, React\Http\Response $response): void
	{
		$path = $request->getPath();

		if ($path === '/api') {
			$this->apiRequestHandler->handleRequest($request, $response);
		} elseif ($path === '/assign-cookie') {
			$this->assignCookieRequestHandler->handleRequest($request, $response);
		} elseif ($path === '/check-cookie') {
			$this->checkCookieRequestHandler->handleRequest($request, $response);
		} elseif ($path === '/delayed') {
			$this->delayedRequestHandler->handleRequest($request, $response);
		} elseif ($path === '/empty') {
			$this->emptyRequestHandler->handleRequest($request, $response);
		} elseif ($path === '/login') {
			$this->loginRequestHandler->handleRequest($request, $response);
		} else {
			throw new InvalidHttpRequestException('Unexpected path.');
		}
	}

}
