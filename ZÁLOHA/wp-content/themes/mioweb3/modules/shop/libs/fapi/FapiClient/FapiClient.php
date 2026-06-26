<?php

namespace MwShop\FapiClient;

use MwShop\FapiClient\Rest\FapiRestClient;
use MwShop\FapiClient\Rest\FapiRestClientOptions;
use MwShop\HttpClient\IHttpClient;

class FapiClient implements IFapiClient
{

	/** @var FapiRestClient */
	private $restClient;

	/**
	 * @param string $username
	 * @param string $password
	 * @param string $apiUrl
	 * @param IHttpClient $httpClient
	 * @param array $options
	 */
	public function __construct($username, $password, $apiUrl, IHttpClient $httpClient, $options = [])
	{
		$this->restClient = new FapiRestClient($username, $password, $apiUrl, $httpClient, $options);
	}


	public function getApiTokens(array $parameters = [])
	{
		return $this->restClient->getResources('/api-tokens', 'api_tokens', $parameters);
	}


	public function getApiToken($id)
	{
		return $this->restClient->getResource('/api-tokens', $id);
	}


	public function createApiToken(array $data)
	{
		return $this->restClient->createResource('/api-tokens', $data);
	}


	public function updateApiToken($id, array $data)
	{
		return $this->restClient->updateResource('/api-tokens', $id, $data);
	}


	public function deleteApiToken($id)
	{
		$this->restClient->deleteResource('/api-tokens', $id);
	}


	public function getClients(array $parameters = [])
	{
		return $this->restClient->getResources('/clients', 'clients', $parameters);
	}


	public function getClient($id)
	{
		return $this->restClient->getResource('/clients', $id);
	}


	public function createClient(array $data)
	{
		return $this->restClient->createResource('/clients', $data);
	}


	public function updateClient($id, array $data)
	{
		return $this->restClient->updateResource('/clients', $id, $data);
	}


	public function deleteClient($id)
	{
		$this->restClient->deleteResource('/clients', $id);
	}


	public function getCountries(array $parameters = [])
	{
		$options = FapiRestClientOptions::STRING_RESOURCE;

		return $this->restClient->getResources('/countries', 'countries', $parameters, $options);
	}


	public function getForms(array $parameters = [])
	{
		return $this->restClient->getResources('/forms', 'forms', $parameters);
	}


	public function getForm($id, $parameters = [])
	{
		return $this->restClient->getResource('/forms', $id, $parameters);
	}


	public function createForm(array $data)
	{
		return $this->restClient->createResource('/forms', $data);
	}


	public function updateForm($id, array $data)
	{
		return $this->restClient->updateResource('/forms', $id, $data);
	}


	public function getItemTemplates(array $parameters = [])
	{
		return $this->restClient->getResources('/item-templates', 'item_templates', $parameters);
	}


	public function getItemTemplate($id)
	{
		return $this->restClient->getResource('/item-templates', $id);
	}


	public function createItemTemplate(array $data)
	{
		return $this->restClient->createResource('/item-templates', $data);
	}


	public function updateItemTemplate($id, array $data)
	{
		return $this->restClient->updateResource('/item-templates', $id, $data);
	}


	public function deleteItemTemplate($id)
	{
		$this->restClient->deleteResource('/item-templates', $id);
	}


	public function updateItem($id, array $data)
	{
		return $this->restClient->updateResource('/items', $id, $data);
	}


	public function getInvoices(array $parameters = [])
	{
		return $this->restClient->getResources('/invoices', 'invoices', $parameters);
	}


	public function getInvoice($id)
	{
		return $this->restClient->getResource('/invoices', $id);
	}


	public function getInvoicePdf($id)
	{
		return $this->restClient->getInvoicePdf($id);
	}


	public function createInvoice(array $data)
	{
		return $this->restClient->createResource('/invoices', $data);
	}


	public function updateInvoice($id, array $data)
	{
		return $this->restClient->updateResource('/invoices', $id, $data);
	}


	public function deleteInvoice($id)
	{
		$this->restClient->deleteResource('/invoices', $id);
	}


	public function getOrder($id)
	{
		return $this->restClient->getResource('/orders', $id);
	}


	public function createOrder(array $data)
	{
		return $this->restClient->createResource('/orders', $data);
	}


	public function updateOrder($id, array $data)
	{
		return $this->restClient->updateResource('/orders', $id, $data);
	}


	public function createDiscountCode(array $data)
	{
		return $this->restClient->createResource('/discount-codes', $data);
	}


	public function updateDiscountCode($id, array $data)
	{
		return $this->restClient->updateResource('/discount-codes', $id, $data);
	}


	public function deleteDiscountCode($id)
	{
		$this->restClient->deleteResource('/discount-codes', $id);
	}

	public function isValidDiscountCode($code, $form)
	{
		return $this->restClient->getSingularResource('/discount-codes/is-valid', ['code' => $code, 'form' => $form]);
	}


	public function getSettings(array $parameters = [])
	{
		return $this->restClient->getResources('/settings', 'settings', $parameters);
	}


	public function getSetting($key)
	{
		return $this->restClient->getResource('/settings', $key, [], FapiRestClientOptions::STRING_KEY);
	}


	public function createSetting(array $data)
	{
		return $this->restClient->createResource('/settings', $data, FapiRestClientOptions::STRING_KEY);
	}


	public function updateSetting($key, array $data)
	{
		return $this->restClient->updateResource('/settings', $key, $data, FapiRestClientOptions::STRING_KEY);
	}


	public function deleteSetting($key)
	{
		$this->restClient->deleteResource('/settings', $key, FapiRestClientOptions::STRING_KEY);
	}


	public function getCurrentUser()
	{
		return $this->restClient->getSingularResource('/user');
	}


	public function getUsers(array $parameters = [])
	{
		return $this->restClient->getResources('/users', 'users', $parameters);
	}


	public function getUser($id)
	{
		return $this->restClient->getResource('/users', $id);
	}


	public function updateUser($id, array $data)
	{
		return $this->restClient->updateResource('/users', $id, $data);
	}

	public function getExchangeRate(array $parameters = [])
	{
		//$options = FapiRestClientOptions::STRING_RESOURCE;
		return $this->restClient->getResources('/exchange-rates/list', 'exchange_rates', $parameters);
	}

	public function getExchangeRates(array $parameters = [])
	{
		//$options = FapiRestClientOptions::STRING_RESOURCE;
		return $this->restClient->getSingularResource('/exchange-rates/list-all-allowed-for-target', $parameters);
	}
}
