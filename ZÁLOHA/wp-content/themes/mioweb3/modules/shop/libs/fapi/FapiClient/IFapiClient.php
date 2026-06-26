<?php

namespace MwShop\FapiClient;


interface IFapiClient
{

	/**
	 * @param array $parameters
	 * @return array[]
	 */
	function getApiTokens(array $parameters = []);

	/**
	 * @param int $id
	 * @return array|null
	 */
	function getApiToken($id);

	/**
	 * @param array $data
	 * @return array
	 */
	function createApiToken(array $data);

	/**
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	function updateApiToken($id, array $data);

	/**
	 * @param int $id
	 * @return void
	 */
	function deleteApiToken($id);

	/**
	 * @param array $parameters
	 * @return array[]
	 */
	function getClients(array $parameters = []);

	/**
	 * @param int $id
	 * @return array|null
	 */
	function getClient($id);

	/**
	 * @param array $data
	 * @return array
	 */
	function createClient(array $data);

	/**
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	function updateClient($id, array $data);

	/**
	 * @param int $id
	 * @return void
	 */
	function deleteClient($id);

	/**
	 * @param array $parameters
	 * @return string[]
	 */
	function getCountries(array $parameters = []);

	/**
	 * @param array $parameters
	 * @return array[]
	 */
	function getForms(array $parameters = []);

	/**
	 * @param int $id
	 * @return array|null
	 */
	function getForm($id, $parameters = []);

	/**
	 * @param array $data
	 * @return array
	 */
	function createForm(array $data);

	/**
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	function updateForm($id, array $data);

	/**
	 * @param array $parameters
	 * @return array[]
	 */
	function getItemTemplates(array $parameters = []);

	/**
	 * @param int $id
	 * @return array|null
	 */
	function getItemTemplate($id);

	/**
	 * @param array $data
	 * @return array
	 */
	function createItemTemplate(array $data);

	/**
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	function updateItemTemplate($id, array $data);

	/**
	 * @param int $id
	 * @return void
	 */
	function deleteItemTemplate($id);

	/**
	 * @param array $parameters
	 * @return array[]
	 */
	function getInvoices(array $parameters = []);

	/**
	 * @param int $id
	 * @return array|null
	 */
	function getInvoice($id);

	/**
	 * @param int $id
	 * @return string|null
	 */
	function getInvoicePdf($id);

	/**
	 * @param array $data
	 * @return array
	 */
	function createInvoice(array $data);

	/**
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	function updateInvoice($id, array $data);

	/**
	 * @param int $id
	 * @return void
	 */
	function deleteInvoice($id);

	/**
	 * @param int $id
	 * @return array|null
	 */
	function getOrder($id);

	/**
	 * @param array $data
	 * @return array
	 */
	function createOrder(array $data);

	/**
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	function updateOrder($id, array $data);

	/**
	 * @param array $data
	 * @return array
	 */
	function createDiscountCode(array $data);

	/**
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	function updateDiscountCode($id, array $data);

	/**
	 * @param array $parameters
	 * @return array
	 */
	function getSettings(array $parameters = []);

	/**
	 * @param string $key
	 * @return array|null
	 */
	function getSetting($key);

	/**
	 * @param array $data
	 * @return array
	 */
	function createSetting(array $data);

	/**
	 * @param string $key
	 * @param array $data
	 * @return array
	 */
	function updateSetting($key, array $data);

	/**
	 * @param string $key
	 * @return void
	 */
	function deleteSetting($key);

	/** @return array */
	function getCurrentUser();

	/**
	 * @param array $parameters
	 * @return array[]
	 */
	function getUsers(array $parameters = []);

	/**
	 * @param int $id
	 * @return array|null
	 */
	function getUser($id);

	/**
	 * @param int $id
	 * @param array $data
	 * @return array
	 */
	function updateUser($id, array $data);
}
