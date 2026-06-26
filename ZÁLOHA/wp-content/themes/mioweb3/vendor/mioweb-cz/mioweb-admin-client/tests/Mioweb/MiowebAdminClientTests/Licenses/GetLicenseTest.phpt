<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Licenses;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Licenses\MockHttpClients\GetLicenseMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/GetLicenseMockHttpClient.php';

class GetLicenseTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|GetLicenseMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetLicense(): void
	{
		$license = $this->miowebAdminClient->getLicense(119848);

		Assert::type('array', $license);
		Assert::same(396414, $license['customer_id']);
		Assert::same('lifetime', $license['type']);
		Assert::same(['cms', 'blog', 'mioweb', 'advanced'], $license['modules']);
		Assert::same('2018-12-31', $license['expire_at']);
		Assert::same('2018-06-30', $license['support_expire_at']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new GetLicenseMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/GetLicenseMockHttpClient.php',
				GetLicenseMockHttpClient::class,
			);
		}
	}

}

\run(new GetLicenseTest());
