<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\MockHttpClients\MiowebAdminClientCreateEmailMockHttpClient;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/MockHttpClients/MiowebAdminClientCreateEmailMockHttpClient.php';

class MiowebAdminClientCreateEmailTest extends TestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|MiowebAdminClientCreateEmailMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testCreateEmail(): void
	{
		$this->miowebAdminClient->createEmailV1([
			'customerEmail' => 'vaclav@smartemailing.cz',
			'domain' => 'test-smartselling-15.mioweb.cz',
			'type' => 'mailbox',
			'username' => 'sample-mailbox',
			'password' => 'secretpassword',
		]);

		$this->miowebAdminClient->createEmailV1([
			'customerEmail' => 'vaclav@smartemailing.cz',
			'domain' => 'test-smartselling-15.mioweb.cz',
			'type' => 'redirect',
			'from' => 'sample-redirect',
			'to' => 'test@fabik.org',
		]);

		Environment::$checkAssertions = false;
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new MiowebAdminClientCreateEmailMockHttpClient();

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
				__DIR__ . '/MockHttpClients/MiowebAdminClientCreateEmailMockHttpClient.php',
				MiowebAdminClientCreateEmailMockHttpClient::class,
			);
		}
	}

}

\run(new MiowebAdminClientCreateEmailTest());
