<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Hostings;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Hostings\MockHttpClients\UnSuspendHostingMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/UnSuspendHostingMockHttpClient.php';

class UnSuspendHostingTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|UnSuspendHostingMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testUnSuspendHosting(): void
	{
		$hosting = $this->miowebAdminClient->unSuspendHosting(5879);

		Assert::type('array', $hosting);
		Assert::true($hosting['success']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new UnSuspendHostingMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/UnSuspendHostingMockHttpClient.php',
				UnSuspendHostingMockHttpClient::class,
			);
		}
	}

}

\run(new UnSuspendHostingTest());
