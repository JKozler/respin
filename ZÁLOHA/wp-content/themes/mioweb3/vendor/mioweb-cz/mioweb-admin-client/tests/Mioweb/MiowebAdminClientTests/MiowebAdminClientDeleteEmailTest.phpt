<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\MockHttpClients\MiowebAdminClientDeleteEmailMockHttpClient;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/MockHttpClients/MiowebAdminClientDeleteEmailMockHttpClient.php';

class MiowebAdminClientDeleteEmailTest extends TestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|MiowebAdminClientDeleteEmailMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testDeleteEmail(): void
	{
		$this->miowebAdminClient->deleteEmailV1([
			'type' => 'mailbox',
			'domain' => 'test-smartselling-15.mioweb.cz',
			'username' => 'sample-mailbox',
		]);

		$this->miowebAdminClient->deleteEmailV1([
			'type' => 'redirect',
			'domain' => 'test-smartselling-15.mioweb.cz',
			'username' => 'sample-redirect',
		]);

		Environment::$checkAssertions = false;
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new MiowebAdminClientDeleteEmailMockHttpClient();

			$this->miowebAdminClient = new MiowebAdminClient(
				'admin',
				'xxx',
				'https://admin.smartcluster.net/api/',
				$this->httpClient,
			);
	}

	protected function tearDown(): void
	{
		if ($this->generateMockHttpClient) {
			$this->httpClient->writeToPhpFile(
				__DIR__ . '/MockHttpClients/MiowebAdminClientDeleteEmailMockHttpClient.php',
				MiowebAdminClientDeleteEmailMockHttpClient::class,
			);
		}
	}

}

\run(new MiowebAdminClientDeleteEmailTest());
