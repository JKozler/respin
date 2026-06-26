<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClientTests\Hostings\MockHttpClients;

use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\MockHttpClient;

class GetHostingMockHttpClient extends MockHttpClient
{

	public function __construct()
	{
		$this->add(
			new HttpRequest(
				'http://mioweb-admin.dev/api/v3/hostings/5879',
				'GET',
				[
					'auth' => ['admin', 'admin'],
					'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
				],
			),
			new HttpResponse(
				200,
				[
					'Date' => ['Sun, 05 Nov 2017 15:05:06 GMT'],
					'Server' => ['Apache/2.4.20 (Ubuntu)'],
					'X-Powered-By' => ['Nette Framework'],
					'X-Frame-Options' => ['SAMEORIGIN'],
					'Expires' => ['Thu, 19 Nov 1981 08:52:00 GMT'],
					'Cache-Control' => ['no-store, no-cache, must-revalidate'],
					'Pragma' => ['no-cache'],
					'Set-Cookie' => [
						'PHPSESSID=hm6acc7cpheu3spng4juin2rr0; expires=Sun, 19-Nov-2017 15:05:06 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=1q8aglcd2b3ugkc7v7tdk4l1v0; expires=Sun, 19-Nov-2017 15:05:06 GMT; Max-Age=1209600; path=/; HttpOnly',
						'PHPSESSID=1q8aglcd2b3ugkc7v7tdk4l1v0; expires=Sun, 19-Nov-2017 15:05:06 GMT; Max-Age=1209600; path=/; HttpOnly',
					],
					'Vary' => ['X-Requested-With,Accept-Encoding'],
					'Content-Length' => ['712'],
					'Content-Type' => ['application/json; charset=utf-8'],
				],
				'{"id":5879,"customer_id":396414,"remote_id":null,"license_id":119849,"extra_license_id":null,"domain":{"name":"mesour-100-test.mioweb.cz","deleted_name":null,"managed":"mioweb-cz-subdomain","prepaid_count":0},"server":"mioweb_10","title":"Sample web","contact_data":[],"notification_url":"https://app.smartselling.cz/public/mioweb-hostings/notify","send_email":false,"mailbox_count":0,"vip":false,"hidden":false,"paid":false,"status":"creating","credentials":null,"is_billed_by_smartselling":false,"created_at":"2017-11-05 16:01:00","deleted_at":null,"done_at":null,"hosting_type":"trial","expire_at":"2017-11-30","source":{"group":"mioweb"},"description":null,"backup_file":null,"language":"cs","template":null}',
			),
		);
	}

}
