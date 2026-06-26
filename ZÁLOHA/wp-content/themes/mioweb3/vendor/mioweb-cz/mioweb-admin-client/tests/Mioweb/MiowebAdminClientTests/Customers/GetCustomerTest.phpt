<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Customers;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Customers\MockHttpClients\GetCustomerMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/GetCustomerMockHttpClient.php';

class GetCustomerTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|GetCustomerMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetCustomer(): void
	{
		$customer = $this->miowebAdminClient->getCustomer(396414);

		Assert::type('array', $customer);
		Assert::same('mw-client-test-124@mesour.com', $customer['email']);
		Assert::true($customer['vip']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new GetCustomerMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/GetCustomerMockHttpClient.php',
				GetCustomerMockHttpClient::class,
			);
		}
	}

}

\run(new GetCustomerTest());
