<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Statistics;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Statistics\MockHttpClients\GetBillingStatisticsMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/GetBillingStatisticsMockHttpClient.php';

class GetBillingStatisticsTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|GetBillingStatisticsMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetBillingStatistics(): void
	{
		$billingStatistics = $this->miowebAdminClient->getBillingStatistics([
			'min_date' => '2017-11-04',
			'max_date' => '2017-11-04',
		]);

		Assert::same($this->getBillingStatistics(), $billingStatistics);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new GetBillingStatisticsMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/GetBillingStatisticsMockHttpClient.php',
				GetBillingStatisticsMockHttpClient::class,
			);
		}
	}

	/** @return array<string, int> */
	private function getBillingStatistics(): array
	{
		return [
			'new_trial_hostings' => 590,
			'trial_hostings' => 590,
			'leaving_trial_hostings' => 590,
			'trial_to_full_hostings' => 77,
		];
	}

}

\run(new GetBillingStatisticsTest());
