<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Domains\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class ExtendDomainMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/domains/extend',
				'POST',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
					'json' => ['domain' => 'mesour-101-test.mioweb.cz', 'expiration' => '2019-01-01'],
				],
			),
			new HttpResponse(
				201,
				[
					'Date' => ['Sun, 05 Nov 2017 16:32:50 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=1htaq742jlr1er7ub2ascac3n0; expires=Sun, 19-Nov-2017 16:32:50 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=clb1ht2s9m0ebouf8is9td5it6; expires=Sun, 19-Nov-2017 16:32:50 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=clb1ht2s9m0ebouf8is9td5it6; expires=Sun, 19-Nov-2017 16:32:50 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With'],
					'Content-Length' => ['62'],
					'Connection' => ['close'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"success":true}',
			),
		);
	}

}
