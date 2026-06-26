<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Hostings\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class UnSuspendHostingMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/hostings/5879/unsuspend',
				'POST',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
					'json' => [],
				],
			),
			new HttpResponse(
				201,
				[
					'Date' => ['Sun, 05 Nov 2017 16:21:02 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=2mi4543cblfntdjdcj5klpuot3; expires=Sun, 19-Nov-2017 16:21:02 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=7kl8ralqmpj2avoqcp8e78csj4; expires=Sun, 19-Nov-2017 16:21:02 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=7kl8ralqmpj2avoqcp8e78csj4; expires=Sun, 19-Nov-2017 16:21:02 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With'],
					'Content-Length' => ['81'],
					'Connection' => ['close'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"success":true}',
			),
		);
	}

}
