<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\LicensePackages\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class GetLicensePackagesMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/license-packages?email=mw-client-test-124%40mesour.com&limit=1',
				'GET',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Wed, 31 Jan 2018 15:26:27 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=rntf0gvbsdhu4hfngphu62sq23; expires=Wed, 14-Feb-2018 15:26:27 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=9qeiu7u6ddjc8l7lsh1mq97sc1; expires=Wed, 14-Feb-2018 15:26:27 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=9qeiu7u6ddjc8l7lsh1mq97sc1; expires=Wed, 14-Feb-2018 15:26:27 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With,Accept-Encoding'],
					'Content-Length' => ['128'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'[{"id":1,"customer_id":398450,"tariff":"start","license_count":0,"first_sold_at":"2014-05-20 10:51:21","last_upgraded_at":null}]',
			),
		);
	}

}
