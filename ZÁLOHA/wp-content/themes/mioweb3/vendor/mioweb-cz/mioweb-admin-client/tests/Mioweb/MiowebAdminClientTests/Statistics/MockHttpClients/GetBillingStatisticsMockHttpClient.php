<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Statistics\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class GetBillingStatisticsMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/billing-statistics?min_date=2017-11-04&max_date=2017-11-04',
				'GET',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Sun, 05 Nov 2017 14:52:30 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=p7dm65sks3futgij0rm8hdqkk7; expires=Sun, 19-Nov-2017 14:52:30 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=gj3q1q65ivb1fnf92l96pkbdb0; expires=Sun, 19-Nov-2017 14:52:30 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=gj3q1q65ivb1fnf92l96pkbdb0; expires=Sun, 19-Nov-2017 14:52:30 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With,Accept-Encoding'],
					'Content-Length' => ['104'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"new_trial_hostings":590,"trial_hostings":590,"leaving_trial_hostings":590,"trial_to_full_hostings":77}',
			),
		);
	}

}
