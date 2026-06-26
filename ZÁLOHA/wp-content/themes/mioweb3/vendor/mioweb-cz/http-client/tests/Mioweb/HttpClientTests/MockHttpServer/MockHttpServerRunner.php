<?php declare(strict_types=1);

namespace Mioweb\HttpClientTests\MockHttpServer;

use Mioweb\HttpClientTests\MockHttpServer\Exceptions\HttpServerException;
use React;

class MockHttpServerRunner
{

	/** @var callable[] function (MockHttpServerRunner $sender); Occurs when the mock HTTP server is started */
	public array $onStarted = [];

	private string $serverRunningMessage = "Server running at http://127.0.0.1:1337/\n";

	private React\EventLoop\LoopInterface $eventLoop;

	private React\ChildProcess\Process $process;

	private string $stdoutBuffer;

	public function run(): void
	{
		$this->eventLoop = React\EventLoop\Factory::create();
		$this->process = new React\ChildProcess\Process(\escapeshellarg(__DIR__ . '/bin/run-mock-http-server'));
		$this->eventLoop->addTimer(0.001, [$this, 'startChildProcess']);
		$this->eventLoop->addTimer(5.0, [$this, 'handleTimeout']);
		$this->eventLoop->run();
	}

	public function stop(): void
	{
		$this->process->terminate();
		$this->eventLoop->stop();
	}

	public function startChildProcess(): void
	{
		$this->process->start($this->eventLoop);

		$this->stdoutBuffer = '';

		$stdoutStream = $this->process->stdout;
		\assert($stdoutStream instanceof React\Stream\Stream);
		$stdoutStream->on('data', [$this, 'handleStdoutData']);
	}

	/** @param mixed $data */
	public function handleStdoutData($data): void
	{
		$this->stdoutBuffer .= $data;

		if (\strlen($this->stdoutBuffer) >= \strlen($this->serverRunningMessage)) {
			if ($this->stdoutBuffer !== $this->serverRunningMessage) {
				throw new HttpServerException('Unexpected stdout data ' . $this->stdoutBuffer . '.');
			}

			$this->fireStarted();
		}
	}

	public function handleTimeout(): void
	{
		$this->process->close();

		throw new HttpServerException('Time limit exceeded');
	}

	private function fireStarted(): void
	{
		foreach ($this->onStarted as $callback) {
			\call_user_func($callback, $this);
		}
	}

}
