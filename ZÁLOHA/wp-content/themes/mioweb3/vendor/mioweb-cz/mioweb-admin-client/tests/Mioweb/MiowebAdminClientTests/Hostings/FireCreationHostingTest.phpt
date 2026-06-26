<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Hostings;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Hostings\MockHttpClients\FireCreationHostingMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/FireCreationHostingMockHttpClient.php';

class FireCreationHostingTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|FireCreationHostingMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testFireCreationHosting(): void
	{
		$data = $this->miowebAdminClient->fireCreationOnHosting(5879, [
			'expire_at' => '2019-12-31',
		]);

		Assert::type('array', $data);
		Assert::true($data['success']);
		Assert::type('array', $data['hosting']);

		$hosting = $data['hosting'];
		Assert::type('array', $hosting);
		Assert::same('2019-12-31', $hosting['expire_at']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new FireCreationHostingMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/FireCreationHostingMockHttpClient.php',
				FireCreationHostingMockHttpClient::class,
			);
		}
	}

}

\run(new FireCreationHostingTest());
