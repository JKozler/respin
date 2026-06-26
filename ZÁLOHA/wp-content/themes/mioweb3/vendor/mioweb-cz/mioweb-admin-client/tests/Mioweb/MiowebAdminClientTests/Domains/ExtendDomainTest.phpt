<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Domains;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Domains\MockHttpClients\ExtendDomainMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/ExtendDomainMockHttpClient.php';

class ExtendDomainTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|ExtendDomainMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testExtendDomain(): void
	{
		$result = $this->miowebAdminClient->extendDomain([
			'domain' => 'mesour-101-test.mioweb.cz',
			'expiration' => '2019-01-01',
		]);

		Assert::type('array', $result);
		Assert::true($result['success']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new ExtendDomainMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/ExtendDomainMockHttpClient.php',
				ExtendDomainMockHttpClient::class,
			);
		}
	}

}

\run(new ExtendDomainTest());
