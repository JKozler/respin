<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests;

use Mioweb\HttpClient\CapturingHttpClient;
use Mioweb\HttpClient\GuzzleHttpClient;
use Mioweb\MiowebAdminClient\MiowebAdminClient;
use Mioweb\MiowebAdminClientTests\MockHttpClients\MiowebAdminClientGetEmailsMockHttpClient;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/MockHttpClients/MiowebAdminClientGetEmailsMockHttpClient.php';

class MiowebAdminClientGetEmailsTest extends TestCase
{

	private bool $generateMockHttpClient = false;

	/** @var CapturingHttpClient|MiowebAdminClientGetEmailsMockHttpClient */
	private $httpClient;

	private MiowebAdminClient $miowebAdminClient;

	public function testGetEmails(): void
	{
		$emails = $this->miowebAdminClient->getEmailsV1([
			'domain' => 'test-smartselling-15.mioweb.cz',
		]);

		Assert::type('array', $emails);
		Assert::type('array', $emails['mailboxes']);
		Assert::same('sample-mailbox@test-smartselling-15.mioweb.cz', $emails['mailboxes'][0]['username']);
		Assert::type('array', $emails['redirects']);
		Assert::same('sample-redirect@test-smartselling-15.mioweb.cz', $emails['redirects'][0]['from']);
		Assert::same('test@fabik.org', $emails['redirects'][0]['to']);
	}

	protected function setUp(): void
	{
		Environment::lock('MiowebAdminClient', \LOCKS_DIR);

		$this->httpClient = $this->generateMockHttpClient
			? new CapturingHttpClient(new GuzzleHttpClient())
			: new MiowebAdminClientGetEmailsMockHttpClient();

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
				__DIR__ . '/MockHttpClients/MiowebAdminClientGetEmailsMockHttpClient.php',
				MiowebAdminClientGetEmailsMockHttpClient::class,
			);
		}
	}

}


\run(new MiowebAdminClientGetEmailsTest());
