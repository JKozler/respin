<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Hostings;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Hostings\MockHttpClients\GetHostingMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/GetHostingMockHttpClient.php';

class GetHostingTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|GetHostingMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetHosting(): void
	{
		$hosting = $this->miowebAdminClient->getHosting(5879);

		Assert::type('array', $hosting);
		Assert::same('creating', $hosting['status']);
		Assert::same('trial', $hosting['hosting_type']);
		Assert::type('array', $hosting['domain']);

		$domain = $hosting['domain'];
		Assert::same('mesour-100-test.mioweb.cz', $domain['name']);
		Assert::same('mioweb-cz-subdomain', $domain['managed']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new GetHostingMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/GetHostingMockHttpClient.php',
				GetHostingMockHttpClient::class,
			);
		}
	}

}

\run(new GetHostingTest());
