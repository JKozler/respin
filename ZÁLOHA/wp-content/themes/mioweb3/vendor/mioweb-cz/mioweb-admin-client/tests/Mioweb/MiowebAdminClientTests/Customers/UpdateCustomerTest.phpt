<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Customers;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Customers\MockHttpClients\UpdateCustomerMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/UpdateCustomerMockHttpClient.php';

class UpdateCustomerTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|UpdateCustomerMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testUpdateCustomer(): void
	{
		$customer = $this->miowebAdminClient->updateCustomer(396414, [
			'vip' => true,
		]);

		Assert::type('array', $customer);
		Assert::same('mw-client-test-124@mesour.com', $customer['email']);
		Assert::true($customer['vip']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new UpdateCustomerMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/UpdateCustomerMockHttpClient.php',
				UpdateCustomerMockHttpClient::class,
			);
		}
	}

}

\run(new UpdateCustomerTest());
