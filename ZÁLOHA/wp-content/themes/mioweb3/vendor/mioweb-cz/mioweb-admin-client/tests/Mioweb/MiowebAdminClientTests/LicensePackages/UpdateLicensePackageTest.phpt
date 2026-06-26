<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\LicensePackages;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\LicensePackages\MockHttpClients\UpdateLicensePackageMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/UpdateLicensePackageMockHttpClient.php';

class UpdateLicensePackageTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|UpdateLicensePackageMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testUpdateLicensePackage(): void
	{
		$licensePackage = $this->miowebAdminClient->updateLicensePackage(5, [
			'tariff' => 'premium',
			'license_count' => 10,
			'first_sold_at' => '2018-01-01 12:00:00',
		]);

		Assert::same($this->getExpectedResult(), $licensePackage);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new UpdateLicensePackageMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/UpdateLicensePackageMockHttpClient.php',
				UpdateLicensePackageMockHttpClient::class,
			);
		}
	}

	/** @return array<string, int|string> */
	private function getExpectedResult(): array
	{
		return [
			'id' => 5,
			'customer_id' => 396414,
			'tariff' => 'premium',
			'license_count' => 10,
			'first_sold_at' => '2018-01-01 12:00:00',
			'last_upgraded_at' => null,
		];
	}

}

\run(new UpdateLicensePackageTest());
