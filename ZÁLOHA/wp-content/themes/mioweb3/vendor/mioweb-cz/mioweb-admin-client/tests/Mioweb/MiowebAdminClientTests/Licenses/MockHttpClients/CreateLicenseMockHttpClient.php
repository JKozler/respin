<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Licenses\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class CreateLicenseMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/licenses',
				'POST',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
					'json' => [
						'customer_id' => 396414,
						'type' => 'lifetime',
						'modules' => ['cms', 'blog', 'mioweb', 'advanced'],
						'source' => [
							'kind' => 'other',
							'other' => 'Sample description',
							'group' => 'plazova-platforma',
							'year' => 2018,
						],
						'expire_at' => '2018-12-31',
						'support_expire_at' => '2018-06-30',
					],
				],
			),
			new HttpResponse(
				201,
				[
					'Date' => ['Sun, 05 Nov 2017 14:34:17 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=ij666l454pnlo9pf18s9sm6ja3; expires=Sun, 19-Nov-2017 14:34:17 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=4hm587ikprp4o01uohjccqkq44; expires=Sun, 19-Nov-2017 14:34:17 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=4hm587ikprp4o01uohjccqkq44; expires=Sun, 19-Nov-2017 14:34:17 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With'],
					'Content-Length' => ['398'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"id":119848,"customer_id":396414,"serial_number":"a52371b102f3004616323225d326078a","domain":"","https":false,"type":"lifetime","modules":["cms","blog","mioweb","advanced"],"created_at":"2017-11-05 15:34:17","expire_at":"2018-12-31","support_expire_at":"2018-06-30","source":{"kind":"other","package_id":null,"hosting_id":null,"other":"Sample description","group":"plazova-platforma","year":2018}}',
			),
		);
	}

}
