<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\LicensePackages\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class CreateLicensePackageMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/license-packages',
				'POST',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
					'json' => ['customer_id' => 396414, 'tariff' => 'start'],
				],
			),
			new HttpResponse(
				201,
				[
					'Date' => ['Wed, 31 Jan 2018 15:21:14 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=ck4isvo7oquvtiufda8e45e4a6; expires=Wed, 14-Feb-2018 15:21:14 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=9fcba1cv3p9js4ld68985an6k3; expires=Wed, 14-Feb-2018 15:21:14 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=9fcba1cv3p9js4ld68985an6k3; expires=Wed, 14-Feb-2018 15:21:14 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With'],
					'Content-Length' => ['109'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"id":6,"customer_id":396414,"tariff":"start","license_count":0,"first_sold_at":null,"last_upgraded_at":null}',
			),
		);
	}

}
