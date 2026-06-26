<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Licenses\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class UpdateLicenseMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/licenses/119848',
				'PUT',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
					'json' => ['modules' => ['cms', 'blog', 'mioweb', 'shop', 'advanced']],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Sun, 05 Nov 2017 14:44:48 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=mb5039neeo6h53q0qek6ocb9e4; expires=Sun, 19-Nov-2017 14:44:48 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=52hosmj08fekbigiajaip2mh54; expires=Sun, 19-Nov-2017 14:44:48 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=52hosmj08fekbigiajaip2mh54; expires=Sun, 19-Nov-2017 14:44:48 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With,Accept-Encoding'],
					'Content-Length' => ['405'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"id":119848,"customer_id":396414,"serial_number":"a52371b102f3004616323225d326078a","domain":"","https":false,"type":"lifetime","modules":["cms","blog","mioweb","shop","advanced"],"created_at":"2017-11-05 15:34:17","expire_at":"2018-12-31","support_expire_at":"2018-06-30","source":{"kind":"other","package_id":null,"hosting_id":null,"other":"Sample description","group":"plazova-platforma","year":2018}}',
			),
		);
	}

}
