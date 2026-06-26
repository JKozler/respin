<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient;

use Mioweb\MPohodaClient\EndPoints\Activities;
use Mioweb\MPohodaClient\EndPoints\IssuedInvoices;
use Mioweb\MPohodaClient\EndPoints\VatRates;

interface IMPohodaClient
{

	public function getActivities(): Activities;

	public function getIssuedInvoices(): IssuedInvoices;

	public function getVatRates(): VatRates;

}
