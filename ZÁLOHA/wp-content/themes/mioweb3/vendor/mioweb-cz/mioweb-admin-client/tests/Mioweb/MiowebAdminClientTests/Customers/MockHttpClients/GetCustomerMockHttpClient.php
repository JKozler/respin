<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Customers\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class GetCustomerMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/customers/396414',
				'GET',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Sun, 05 Nov 2017 14:08:55 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=a373uv0oqn0idaqnnkjm388qs0; expires=Sun, 19-Nov-2017 14:08:55 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=9th6ohlv018jej1k4t62gop041; expires=Sun, 19-Nov-2017 14:08:55 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=9th6ohlv018jej1k4t62gop041; expires=Sun, 19-Nov-2017 14:08:55 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With,Accept-Encoding'],
					'Content-Length' => ['222'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"id":396414,"email":"mw-client-test-124@mesour.com","billing_user_id":null,"is_agency":false,"vip":true,"support_expiration_date":null,"created_at":"2017-11-05 14:56:44","updated_at":"2017-11-05 15:00:35","language":"cs"}',
			),
		);
	}

}
