<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Hostings;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Hostings\MockHttpClients\GetHostingsMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/GetHostingsMockHttpClient.php';

class GetHostingsTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|GetHostingsMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetHostings(): void
	{
		$hostings = $this->miowebAdminClient->getHostings([
			'email' => 'mw-client-test-124@mesour.com',
			'limit' => 1,
		]);

		Assert::type('array', $hostings);
		Assert::count(1, $hostings);
		Assert::type('array', $hostings[0]);

		$hosting = $hostings[0];
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
			: new GetHostingsMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/GetHostingsMockHttpClient.php',
				GetHostingsMockHttpClient::class,
			);
		}
	}

}

\run(new GetHostingsTest());
