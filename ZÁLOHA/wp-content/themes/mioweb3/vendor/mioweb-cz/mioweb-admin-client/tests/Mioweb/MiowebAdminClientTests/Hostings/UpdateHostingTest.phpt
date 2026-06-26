<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Hostings;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Hostings\MockHttpClients\UpdateHostingMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/UpdateHostingMockHttpClient.php';

class UpdateHostingTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|UpdateHostingMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testUpdateHosting(): void
	{
		$hosting = $this->miowebAdminClient->updateHosting(5879, [
			'hosting_type' => 'full',
			'vip' => true,
		]);

		Assert::type('array', $hosting);
		Assert::same('creating', $hosting['status']);
		Assert::same('full', $hosting['hosting_type']);
		Assert::true($hosting['vip']);
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
			: new UpdateHostingMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/UpdateHostingMockHttpClient.php',
				UpdateHostingMockHttpClient::class,
			);
		}
	}

}

\run(new UpdateHostingTest());
