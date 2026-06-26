<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class MiowebAdminClientDeleteEmailMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'https://admin.smartcluster.net/api/email?type=mailbox&domain=test-smartselling-15.mioweb.cz&username=sample-mailbox',
				'DELETE',
				[
					'auth' => ['admin', 'xxx'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Tue, 22 Sep 2015 13:37:29 GMT'],
					'Server' => ['Apache'],
					'X-Frame-Options' => ['SAMEORIGIN', 'sameorigin'],
					'X-Powered-By' => ['Nette Framework'],
					'Vary' => ['X-Requested-With'],
					'Cache-Control' => ['s-maxage=0, max-age=0, must-revalidate'],
					'Expires' => ['Mon, 23 Jan 1978 10:00:00 GMT'],
					'Strict-Transport-Security' => ['max-age=63072000; includeSubdomains; preload'],
					'X-Content-Type-Options' => ['nosniff'],
					'Transfer-Encoding' => ['chunked'],
					'Content-Type' => ['application/json'],
				],
				'{"mailboxes":[],"redirects":[{"from":"sample-redirect@test-smartselling-15.mioweb.cz","to":"test@fabik.org"}],"status":"ok"}',
			),
		);
		$this->add(
			new HttpRequest(
				'https://admin.smartcluster.net/api/email?type=redirect&domain=test-smartselling-15.mioweb.cz&username=sample-redirect',
				'DELETE',
				[
					'auth' => ['admin', 'xxx'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Tue, 22 Sep 2015 13:37:29 GMT'],
					'Server' => ['Apache'],
					'X-Frame-Options' => ['SAMEORIGIN', 'sameorigin'],
					'X-Powered-By' => ['Nette Framework'],
					'Vary' => ['X-Requested-With'],
					'Cache-Control' => ['s-maxage=0, max-age=0, must-revalidate'],
					'Expires' => ['Mon, 23 Jan 1978 10:00:00 GMT'],
					'Strict-Transport-Security' => ['max-age=63072000; includeSubdomains; preload'],
					'X-Content-Type-Options' => ['nosniff'],
					'Transfer-Encoding' => ['chunked'],
					'Content-Type' => ['application/json'],
				],
				'{"mailboxes":[],"redirects":[],"status":"ok"}',
			),
		);
	}

}
