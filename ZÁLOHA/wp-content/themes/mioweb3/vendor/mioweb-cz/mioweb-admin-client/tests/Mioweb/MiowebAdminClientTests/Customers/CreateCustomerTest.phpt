<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Customers;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Customers\MockHttpClients\CreateCustomerMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/CreateCustomerMockHttpClient.php';

class CreateCustomerTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|CreateCustomerMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testCreateCustomer(): void
	{
		$customer = $this->miowebAdminClient->createCustomer([
			'email' => 'mw-client-test-124@mesour.com',
		]);

		Assert::type('array', $customer);
		Assert::same('mw-client-test-124@mesour.com', $customer['email']);
		Assert::false($customer['vip']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new CreateCustomerMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/CreateCustomerMockHttpClient.php',
				CreateCustomerMockHttpClient::class,
			);
		}
	}

}

\run(new CreateCustomerTest());
