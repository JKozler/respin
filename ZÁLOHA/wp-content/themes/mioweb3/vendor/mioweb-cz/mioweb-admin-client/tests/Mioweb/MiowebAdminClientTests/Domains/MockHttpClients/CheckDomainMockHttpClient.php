<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Domains\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class CheckDomainMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/domains/check?name=mesour-101-test.mioweb.cz',
				'GET',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Sun, 05 Nov 2017 16:31:10 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=6v4i6c8l1fraljv3n5bc1nudh7; expires=Sun, 19-Nov-2017 16:31:10 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=2r0tmml87acev3v40ks4og2dt3; expires=Sun, 19-Nov-2017 16:31:10 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=2r0tmml87acev3v40ks4og2dt3; expires=Sun, 19-Nov-2017 16:31:10 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With,Accept-Encoding'],
					'Content-Length' => ['69'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"name":"mesour-101-test.mioweb.cz","expiration":null,"exists":false}',
			),
		);
	}

}
