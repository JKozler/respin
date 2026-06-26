<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Hostings;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Hostings\MockHttpClients\CreateHostingMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/CreateHostingMockHttpClient.php';

class CreateHostingTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|CreateHostingMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testCreateHosting(): void
	{
		$hosting = $this->miowebAdminClient->createHosting([
			'customer_id' => 396414,
			'title' => 'Sample web',
			'server' => 'mioweb_10',
			'hosting_type' => 'trial',
			'notification_url' => 'https://app.smartselling.cz/public/mioweb-hostings/notify',
			'source' => [
				'group' => 'mioweb',
			],
			'status' => 'creating',
			'expire_at' => '2017-11-30',
			'domain' => [
				'name' => 'mesour-100-test.mioweb.cz',
				'managed' => 'mioweb-cz-subdomain',
			],
		]);

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
			: new CreateHostingMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/CreateHostingMockHttpClient.php',
				CreateHostingMockHttpClient::class,
			);
		}
	}

}

\run(new CreateHostingTest());
