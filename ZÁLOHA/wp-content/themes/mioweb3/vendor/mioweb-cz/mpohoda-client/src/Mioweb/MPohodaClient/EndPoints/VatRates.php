<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient\EndPoints;

use Mioweb\MPohodaClient\EndPoints\Traits\Find;
use Mioweb\MPohodaClient\EndPoints\Traits\FindAll;
use Mioweb\MPohodaClient\Rest\MPohodaRestClient;

final class VatRates
{

	use FindAll;
	use Find;

	public function __construct(MPohodaRestClient $client)
	{
		$this->client = $client;
		$this->path = '/VatRates';
	}

}
