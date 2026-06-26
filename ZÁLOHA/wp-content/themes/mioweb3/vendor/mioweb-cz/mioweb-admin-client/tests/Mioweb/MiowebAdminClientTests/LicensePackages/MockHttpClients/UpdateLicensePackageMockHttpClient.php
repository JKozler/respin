<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\LicensePackages\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class UpdateLicensePackageMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/license-packages/5',
				'PUT',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
					'json' => [
						'tariff' => 'premium',
						'license_count' => 10,
						'first_sold_at' => '2018-01-01 12:00:00',
					],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Wed, 31 Jan 2018 15:29:52 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=28b2v7kd1ar6s75h3la77i2rh3; expires=Wed, 14-Feb-2018 15:29:52 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=v7qlji8lg54183augqmsbjcm11; expires=Wed, 14-Feb-2018 15:29:52 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=v7qlji8lg54183augqmsbjcm11; expires=Wed, 14-Feb-2018 15:29:52 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With,Accept-Encoding'],
					'Content-Length' => ['129'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"id":5,"customer_id":396414,"tariff":"premium","license_count":10,"first_sold_at":"2018-01-01 12:00:00","last_upgraded_at":null}',
			),
		);
	}

}
