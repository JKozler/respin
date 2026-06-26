<?php
declare(strict_types=1);


class mwAPIConnectItemClient_mailerlite extends mwAPIConnectItemClient
{

	private ?string $apiKey;

	private MailerLiteApi\Api\Groups $groups;

	private static \MailerLiteApi\MailerLite $instance;

	public function __construct(mwAPIConnectItem $mwAPIConnectItem)
	{
		parent::__construct($mwAPIConnectItem);
		$this->apiKey = $this->getAPIKey() ?? '';
		self::getInstance($this->apiKey);
	}

	/** @throws \MailerLiteApi\Exceptions\MailerLiteSdkException */
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


	private function getAPIKey(): ?string
	{
		$key = $this->_mwAPIConnectItem->getOption();
		if (isset($key['password'])) {
			return $key['password'];
		}

		return null;
	}

	/** @throws \MailerLiteApi\Exceptions\MailerLiteSdkException */
	private function isConnected(string $apiKey): bool
	{
		$this->apiKey = $apiKey;

		return $this->sendHttpRequest('stats');
	}

	/**
	 * @return false|stdClass
	 * @throws \MailerLiteApi\Exceptions\MailerLiteSdkException
	 */
	public function get_lists_list()
	{
		$customs = [];
		if ($this->sendHttpRequest('group')) {
			// the output has to be reformatted because of the MwEmailingApi
			foreach ($this->groups->get() as $item) {
				$customs[] = $item;
			}
			$itemized = new stdClass();
			$itemized->item = $customs;

			return $itemized;
		}

		return false;
	}

	/** @throws \MailerLiteApi\Exceptions\MailerLiteSdkException */
	public function save_to_list(?string $groupId, string $email): bool
	{
		if ($this->sendHttpRequest('group')) {
			$subscribers = [
				[
					'email' => $email,
				],
			];

			$options = [
				'resubscribe' => false,
				'autoresponders' => true, // send autoresponders for successfully imported subscribers
			];
			if ($groupId !== null) {
				$response = $this->groups->importSubscribers($groupId, $subscribers, $options);

				return $response->errors === [];
			}
		}

		return false;
	}

	/** @throws \MailerLiteApi\Exceptions\MailerLiteSdkException */
	public function remove_from_list(?string $listId, string $email): bool
	{
		$this->sendHttpRequest('group');
		if ($listId !== null) {
			return $this->groups->removeSubscriber($listId, $email) === ''; // returns empty response
		}

		return false;
	}

	/** @throws \MailerLiteApi\Exceptions\MailerLiteSdkException */
	public function get_list_count(?string $listId, bool $cached = false, bool $edit_mode = false, bool $deleted = false): ?int
	{
		if ($this->sendHttpRequest('group')) {
			if ($listId !== null) {
				return count($this->groups->getSubscribers($listId));
			}
		}

		return 0;
	}

	// has to be created or it will fail
	public function get_purposes(): ?array
	{
		return null;
	}


	public function showInApiSelector(?string $type = null): bool
	{
		return $type !== 'forms';
	}

	/**
	 * @param string $method
	 * @return bool
	 * @throws \MailerLiteApi\Exceptions\MailerLiteSdkException
	 */
	private function sendHttpRequest(string $method): bool
	{
		if ($this->apiKey !== null) {
			switch ($method) {
				case 'stats':
					$stats = self::$instance->stats();
					$api = $stats->get();

					break;
				case 'group':
					$api = self::$instance->groups();
					$this->groups = $api;

					break;
			}

			return !isset($api->error->message);
		}

		return false;
	}

	public static function getInstance(string $api): \MailerLiteApi\MailerLite
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MailerLiteApi\MailerLite($api);
		}

		return self::$instance;
	}

	// save array data user to  list

	/** @throws \MailerLiteApi\Exceptions\MailerLiteSdkException */
	public function save_to_list_details(?string $groupId, string $email, $purpose = null, array $contactDetails = [], array $customFields = []): bool
	{
		if ($this->sendHttpRequest('group')) {
			$subscribers = [
				 [
					'email' => $email,
					'name' => $contactDetails['name'] ?? '',
					'surname' => $contactDetails['surname'] ?? '',
					'company' => $contactDetails['company_name'] ?? '',
					'phone' => $contactDetails['phone'] ?? '',
				 ],
			];

			$options = [
				'resubscribe' => false,
				'autoresponders' => true, // send autoresponders for successfully imported subscribers
			];
			if ($groupId !== null) {
				$response = $this->groups->importSubscribers($groupId, $subscribers, $options);

				return $response->errors === [];
			}
		}

		return false;
	}
}
