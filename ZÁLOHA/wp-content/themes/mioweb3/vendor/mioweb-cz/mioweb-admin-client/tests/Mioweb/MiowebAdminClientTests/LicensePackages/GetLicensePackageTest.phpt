<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\LicensePackages;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\LicensePackages\MockHttpClients\GetLicensePackageMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/GetLicensePackageMockHttpClient.php';

class GetLicensePackageTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|GetLicensePackageMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetLicensePackage(): void
	{
		$licensePackage = $this->miowebAdminClient->getLicensePackage(5);

		Assert::same($this->getExpectedResult(), $licensePackage);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new GetLicensePackageMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/GetLicensePackageMockHttpClient.php',
				GetLicensePackageMockHttpClient::class,
			);
		}
	}

	/** @return array<string, int|string|null> */
	private function getExpectedResult(): array
	{
		return [
			'id' => 5,
			'customer_id' => 396414,
			'tariff' => 'start',
			'license_count' => 0,
			'first_sold_at' => null,
			'last_upgraded_at' => null,
		];
	}

}

\run(new GetLicensePackageTest());
