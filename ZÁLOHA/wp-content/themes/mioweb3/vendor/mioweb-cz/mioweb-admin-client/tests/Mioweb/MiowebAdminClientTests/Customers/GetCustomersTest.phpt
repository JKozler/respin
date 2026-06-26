<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Customers;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Customers\MockHttpClients\GetCustomersMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/GetCustomersMockHttpClient.php';

class GetCustomersTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|GetCustomersMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetCustomers(): void
	{
		$customers = $this->miowebAdminClient->getCustomers([
			'email' => 'mw-client-test-124@mesour.com',
			'limit' => 1,
		]);

		Assert::type('array', $customers);
		Assert::count(1, $customers);
		Assert::type('array', $customers[0]);
		Assert::same('mw-client-test-124@mesour.com', $customers[0]['email']);
		Assert::true($customers[0]['vip']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new GetCustomersMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/GetCustomersMockHttpClient.php',
				GetCustomersMockHttpClient::class,
			);
		}
	}

}

\run(new GetCustomersTest());
