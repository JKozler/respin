<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Customers;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Customers\MockHttpClients\CheckEmailAvailabilityMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/CheckEmailAvailabilityMockHttpClient.php';

class CheckEmailAvailabilityTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|CheckEmailAvailabilityMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testCheckEmailAvailability(): void
	{
		$available = $this->miowebAdminClient->isEmailAvailableForCustomer([
			'email' => 'test223@mesour.com',
		]);

		Assert::true($available);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new CheckEmailAvailabilityMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/CheckEmailAvailabilityMockHttpClient.php',
				CheckEmailAvailabilityMockHttpClient::class,
			);
		}
	}

}

\run(new CheckEmailAvailabilityTest());
