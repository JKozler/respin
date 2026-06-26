<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Domains;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\BaseTestCase;
use Mioweb\MiowebAdminClientTests\Domains\MockHttpClients\CheckDomainMockHttpClient;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/MockHttpClients/CheckDomainMockHttpClient.php';

class CheckDomainTest extends BaseTestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|CheckDomainMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testCheckDomain(): void
	{
		$result = $this->miowebAdminClient->checkDomain([
			'name' => 'mesour-101-test.mioweb.cz',
		]);

		Assert::same($this->getExpectedData(), $result);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new CheckDomainMockHttpClient();

		$this->miowebAdminClient = $this->createMiowebAdminClient($this->httpClient);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/CheckDomainMockHttpClient.php',
				CheckDomainMockHttpClient::class,
			);
		}
	}

	/** @return array<string, string|false|null> */
	private function getExpectedData(): array
	{
		return [
			'name' => 'mesour-101-test.mioweb.cz',
			'expiration' => null,
			'exists' => false,
		];
	}

}

\run(new CheckDomainTest());
