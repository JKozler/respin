<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\LicensePackages;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\LicensePackages\MockHttpClients\CreateLicensePackageMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/CreateLicensePackageMockHttpClient.php';

class CreateLicensePackageTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|CreateLicensePackageMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testCreateLicensePackage(): void
	{
		$licensePackage = $this->miowebAdminClient->createLicensePackage([
			'customer_id' => 396414,
			'tariff' => 'start',
		]);

		Assert::same($this->getExpectedResult(), $licensePackage);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new CreateLicensePackageMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/CreateLicensePackageMockHttpClient.php',
				CreateLicensePackageMockHttpClient::class,
			);
		}
	}

	/** @return array<string, int|string> */
	private function getExpectedResult(): array
	{
		return [
			'id' => 6,
			'customer_id' => 396414,
			'tariff' => 'start',
			'license_count' => 0,
			'first_sold_at' => null,
			'last_upgraded_at' => null,
		];
	}

}

\run(new CreateLicensePackageTest());
