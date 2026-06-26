<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Licenses;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Licenses\MockHttpClients\UpdateLicenseMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/UpdateLicenseMockHttpClient.php';

class UpdateLicenseTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|UpdateLicenseMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testUpdateLicense(): void
	{
		$license = $this->miowebAdminClient->updateLicense(119848, [
			'modules' => ['cms', 'blog', 'mioweb', 'shop', 'advanced'],
		]);

		Assert::type('array', $license);
		Assert::same(396414, $license['customer_id']);
		Assert::same('lifetime', $license['type']);
		Assert::same(['cms', 'blog', 'mioweb', 'shop', 'advanced'], $license['modules']);
		Assert::same('2018-12-31', $license['expire_at']);
		Assert::same('2018-06-30', $license['support_expire_at']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new UpdateLicenseMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/UpdateLicenseMockHttpClient.php',
				UpdateLicenseMockHttpClient::class,
			);
		}
	}

}

\run(new UpdateLicenseTest());
