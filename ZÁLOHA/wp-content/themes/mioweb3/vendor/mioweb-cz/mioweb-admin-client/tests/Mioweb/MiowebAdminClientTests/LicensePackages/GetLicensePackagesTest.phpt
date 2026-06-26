<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\LicensePackages;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\LicensePackages\MockHttpClients\GetLicensePackagesMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/GetLicensePackagesMockHttpClient.php';

class GetLicensePackagesTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|GetLicensePackagesMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetLicensePackages(): void
	{
		$licensePackages = $this->miowebAdminClient->getLicensePackages([
			'email' => 'mw-client-test-124@mesour.com',
			'limit' => 1,
		]);

		Assert::type('array', $licensePackages);
		Assert::count(1, $licensePackages);
		Assert::type('array', $licensePackages[0]);

		$licensePackage = $licensePackages[0];
		Assert::same($this->getExpectedResult(), $licensePackage);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new GetLicensePackagesMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/GetLicensePackagesMockHttpClient.php',
				GetLicensePackagesMockHttpClient::class,
			);
		}
	}

	/** @return array<string, int|string> */
	private function getExpectedResult(): array
	{
		return [
			'id' => 1,
			'customer_id' => 398450,
			'tariff' => 'start',
			'license_count' => 0,
			'first_sold_at' => '2014-05-20 10:51:21',
			'last_upgraded_at' => null,
		];
	}

}

\run(new GetLicensePackagesTest());
