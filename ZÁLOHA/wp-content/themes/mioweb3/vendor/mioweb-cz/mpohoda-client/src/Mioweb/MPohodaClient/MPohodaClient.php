<?php declare(strict_types=1);

namespace Mioweb\MPohodaClient;

use Mioweb\HttpClient\IHttpClient;
use Mioweb\MPohodaClient\EndPoints\Activities;
use Mioweb\MPohodaClient\EndPoints\IssuedInvoices;
use Mioweb\MPohodaClient\EndPoints\VatRates;
use Mioweb\MPohodaClient\Rest\MPohodaRestClient;

class MPohodaClient implements IMPohodaClient
{

	private MPohodaRestClient $restClient;

	private Activities $activities;

	private IssuedInvoices $issuedInvoices;

	private VatRates $vatRates;

	public function __construct(string $apiKey, string $apiUrl, IHttpClient $httpClient)
	{
		$this->restClient = new MPohodaRestClient($apiKey, $apiUrl, $httpClient);
		$this->activities = new Activities($this->restClient);
		$this->issuedInvoices = new IssuedInvoices($this->restClient);
		$this->vatRates = new VatRates($this->restClient);
	}

	public function getActivities(): Activities
	{
		return $this->activities;
	}

	public function getIssuedInvoices(): IssuedInvoices
	{
		return $this->issuedInvoices;
	}

	public function getVatRates(): VatRates
	{
		return $this->vatRates;
	}

}
