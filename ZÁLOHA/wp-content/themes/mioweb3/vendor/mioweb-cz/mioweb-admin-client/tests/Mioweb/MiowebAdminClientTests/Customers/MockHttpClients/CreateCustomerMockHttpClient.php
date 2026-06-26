<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Customers\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class CreateCustomerMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/customers',
				'POST',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
					'json' => ['email' => 'mw-client-test-124@mesour.com'],
				],
			),
			new HttpResponse(
				201,
				[
					'Date' => ['Sun, 05 Nov 2017 13:56:44 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=qeckloq0fa3algbmo30dus4qn1; expires=Sun, 19-Nov-2017 13:56:44 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=ak602ivs77k5pl0se5p23s9ib7; expires=Sun, 19-Nov-2017 13:56:44 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=ak602ivs77k5pl0se5p23s9ib7; expires=Sun, 19-Nov-2017 13:56:44 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With'],
					'Content-Length' => ['206'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"id":396414,"email":"mw-client-test-124@mesour.com","billing_user_id":null,"is_agency":false,"vip":false,"support_expiration_date":null,"created_at":"2017-11-05 14:56:44","updated_at":null,"language":"cs"}',
			),
		);
	}

}
