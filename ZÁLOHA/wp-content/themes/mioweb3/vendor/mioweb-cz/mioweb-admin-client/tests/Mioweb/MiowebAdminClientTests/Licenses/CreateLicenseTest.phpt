<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Licenses;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Licenses\MockHttpClients\CreateLicenseMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/CreateLicenseMockHttpClient.php';

class CreateLicenseTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|CreateLicenseMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testCreateLicense(): void
	{
		$license = $this->miowebAdminClient->createLicense([
			'customer_id' => 396414,
			'type' => 'lifetime',
			'modules' => [
				'cms',
				'blog',
				'mioweb',
				'advanced',
			],
			'source' => [
				'kind' => 'other',
				'other' => 'Sample description',
				'group' => 'plazova-platforma',
				'year' => 2018,
			],
			'expire_at' => '2018-12-31',
			'support_expire_at' => '2018-06-30',
		]);

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
			: new CreateLicenseMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/CreateLicenseMockHttpClient.php',
				CreateLicenseMockHttpClient::class,
			);
		}
	}

}

\run(new CreateLicenseTest());
