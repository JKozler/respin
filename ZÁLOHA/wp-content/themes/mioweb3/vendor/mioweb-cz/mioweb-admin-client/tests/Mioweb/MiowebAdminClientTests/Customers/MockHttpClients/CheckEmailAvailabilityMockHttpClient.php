<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Customers\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class CheckEmailAvailabilityMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/customers/check-availability',
				'POST',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
					'json' => ['email' => 'test223@mesour.com'],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Thu, 30 Nov 2017 09:56:52 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=7a6g20asoggjpacvkm4ko2rrb0; expires=Thu, 14-Dec-2017 09:56:52 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=t6ll47m9vrtgkujrln7tanqrn0; expires=Thu, 14-Dec-2017 09:56:52 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=t6ll47m9vrtgkujrln7tanqrn0; expires=Thu, 14-Dec-2017 09:56:52 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With'],
					'Content-Length' => ['18'],
					'Connection' => ['close'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"available":true}',
			),
		);
	}

}
