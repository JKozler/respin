<?php
declare(strict_types=1);

use GuzzleHttp\Exception\ClientException;

class mwAPIConnectItemClient_ecomail extends mwAPIConnectItemClient
{

	private string $apiKey;

	private static GuzzleHttp\Client $client;

	private array $response;
	private const BASE_URI = 'https://api2.ecomailapp.cz/';

	/** @throws \GuzzleHttp\Exception\GuzzleException */
	public function __construct(mwAPIConnectItem $mwAPIConnectItem)
	{
		parent::__construct($mwAPIConnectItem);
		$this->apiKey = $this->getAPIKey();
		self::getInstance();
	}

	/** @throws \GuzzleHttp\Exception\GuzzleException */
	public function checkSavedSetting(&$tosave): bool
	{
		$password = $tosave['password'] ?? '';

		if ($password) {
			$tosave['password'] = $password = trim($password);
			$isConnected = $this->isConnected($password);
			mwMessages()->error(__('Přihlašovací údaje nejsou správné. Spojení se nezdařilo.', 'cms'));

			return $isConnected;
		}

		mwMessages()->error(__('Musíte vyplnit přihlašovací API klíč.', 'cms'));

		return false;
	}


	private function getAPIKey(): string
	{
		$key = $this->_mwAPIConnectItem->getOption();
		if ($key !== null && isset($key['password'])) {
			return $key['password'];
		}

		return '';
	}

	/** @throws \GuzzleHttp\Exception\GuzzleException */
	private function isConnected(string $apiKey): bool
	{
		$this->apiKey = $apiKey;

		return $this->sendHttpRequest('GET', 'campaigns');
	}

	/**
	 * @return false|stdClass
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function get_lists_list()
	{
		if ($this->sendHttpRequest('GET', 'lists')) {
			$customs = [];
			$lists = $this->response;
			// the output has to be reformatted because of the MwEmailingApi
			foreach ($lists as $item) {
				//(object) creat autom. stdClass
				$customs[] = (object) [
					'id' => $item['id'],
					'name' => $item['name'],
				];
			}

			$itemized = new stdClass();
			$itemized->item = $customs;

			return $itemized;
		}

		return false;
	}

	/** @throws \GuzzleHttp\Exception\GuzzleException */
	public function save_to_list(?string $listId, string $email): bool
	{
		if ($listId !== '' && $email !== '') {
			$path = sprintf('lists/%s/subscribe', $listId);
			$tmpArr = [
				'subscriber_data' => [
					'email' => $email,
				],
			];

			return $this->sendHttpRequest('POST', $path, $tmpArr);
		}

		return false;
	}

	/** @throws \GuzzleHttp\Exception\GuzzleException */
	public function remove_from_list(?string $listId, string $email): bool
	{
		if ($email !== '') {
			$path = sprintf('subscribers/%s/delete', trim($email));

			return $this->sendHttpRequest('DELETE', $path) && $this->response['deleted'];
		}

		return false;
	}

	/** @throws \GuzzleHttp\Exception\GuzzleException */
	public function get_list_count(?string $listId, bool $cached = false, bool $edit_mode = false, bool $deleted = false): int
	{
		$path = sprintf('lists/%s/subscribers', $listId);
		if ($this->sendHttpRequest('GET', $path)) {
			return $this->response['total'] ?? 0;
		}

		return 0;
	}

	// has to be created or it will fail
	public function get_purposes(): ?array
	{
		return null;
	}

	/** @throws \GuzzleHttp\Exception\GuzzleException */
	private function sendHttpRequest(string $method, string $path, array $data = []): bool
	{
		try {
			$client = self::$client;

			if ($method === 'POST') {
				$payload = json_encode($data);
				$response = $client->post($path, [
					'body' => $payload,
					'headers' => [
						'Content-Type' => 'application/json',
						'key' => $this->apiKey,
					],
				]);
			} else {
				$response = $client->request($method, $path, [
					'headers' => ['key' => $this->apiKey],
				]);
			}

			if ($response->getStatusCode() === 200) {
				$this->response = json_decode($response->getBody()->getContents(), true);

				return true;
			}
		} catch (ClientException $e) {
			return false;
		}

		return false;
	}

	public static function getInstance(): GuzzleHttp\Client
	{
		if (!isset(self::$client)) {
			self::$client = new GuzzleHttp\Client([
				'base_uri' => self::BASE_URI,
			]);
		}

		return self::$client;
	}

	public function showInApiSelector(?string $type = null): bool
	{
		return $type !== 'forms';
	}

	// save array data user to  list
	public function save_to_list_details(?string $listId, string $email, $purpose = null, array $contactDetails = [], array $customFields = []): bool
	{
		if ($listId !== '' && $email !== '') {
			$path = sprintf('lists/%s/subscribe', $listId);
			$tmpArr = [
				'subscriber_data' => [
					'email' => $email,
					'name' => $contactDetails['name'] ?? '',
					'surname' => $contactDetails['surname'] ?? '',
					'company' => $contactDetails['company_name'] ?? '',
					'phone' => $contactDetails['phone'] ?? '',
				],
			];

			return $this->sendHttpRequest('POST', $path, $tmpArr);
		}

		return false;
	}
}
