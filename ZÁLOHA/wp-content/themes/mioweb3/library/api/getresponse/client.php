<?php

class mwAPIConnectItemClient_getresponse extends mwAPIConnectItemClient
{

	/**
	 * True to enable printing, false otherwise. Set using the constructor
	 *
	 * @var boolean
	 * @access private
	 */
	private $errorsOn = false;

	/**
	 * GetResponse API key.
	 * API key can be retrieved from https://app.getresponse.com/manage_api.html (2015-01-12).
	 * http://www.getresponse.com/my_api_key.html
	 *
	 * @var string
	 */
	private $apiKey = 'PASS_API_KEY_WHEN_INSTANTIATING_CLASS';

	/** @var string URL for REST API calls. */
	private $apiUrlRest = 'BASIC_OR_ENTERPRISE_REST_URI';

	/** @var string Set by {@link setApiKey()} method when used in enterprise mode. */
	private $enterpriseHeader = '';

	public function getApiUrl()
	{
		return $this->apiUrlRest;
	}

	function checkSavedSetting(&$tosave): bool
	{
		$password = $tosave['password'] ?? '';

		if ($password) {
			$tosave['password'] = $password = trim($password);

			$isConnected = $this->isConnected($password);
			mwMessages()->error(__('Api klíč není správný. Spojení se nezdařilo.', 'cms'));

			return $isConnected;
		}

		mwMessages()->error(__('Musíte vyplnit API klíč.', 'cms'));

		return false;
	}

	function isConnected($password): bool
	{
		$this->setApiKey($password);
		$result = $this->sendGrRestRequest('get', 'accounts', ['fields' => 'accountId']);

		return $result && $result->accountId;
	}

	public function get_forms_list()
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);

		$result = $this->sendGrRestRequest('get', 'forms', [
			'fields' => 'name,camapign,status,scriptUrl',
		]);
		if (!$result) {
			return false;
		}
		// Normalize output format according to SE data format. Arrays and subarrays are returned in a wrapping object
		// in its "item" field.
		/*
		id
		name
		fields (obj)
		item (obj)
		id
		label - caption
		required - 1|0
		type
		defaults
		*/

		$wrapped = [];
		foreach ($result as $val) {
			$val->url = $val->scriptUrl;
			unset($val->scriptUrl);

			$val->id = $val->formId;
			unset($val->formId);
			unset($val->webformId);

			//Add to resulting array
			$wrapped[$val->id] = $val;

			//!! Hack form GetResponse printing forms. Use script URL as ID.
			$val->id = $val->url;
		}

		//Compose the result object with "item" field.
		$itemized = new stdClass();
		$itemized->item = $wrapped;

		return $itemized;
	}

	public function get_form($id)
	{
		//!! Hack form GetResponse printing forms. Using script URL as ID.

		return $id;
	}

	public function print_form($content, $form, $css_id, $added)
	{
		$content = $added ? '
            <script type="text/javascript">
            	jQuery(function($) {
					mwGetIframeContent().mw_load_added_script("' . $css_id . ' .ve_content_form_container", "' . $form . '", "");
                });
          	</script>' : '<script type="text/javascript" src="' . $form . '"></script>';

		return '<div class="ve_content_form_container">' . $content . '</div>';
	}

	public function get_lists_list()
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		$result = $this->sendGrRestRequest('get', 'campaigns');
		if (!$result) {
			return false;
		}

		// Zde prehazet vysledky tak, aby to bylo podobnejsi SE. GR vraci asociativni pole, kde klic je ID formu.
		// SE vraci vysledky v poli items + pouziva field "id" jako unikatni ID.
		// Tj. je potreba vzit index asociativniho pole a pouzit jej jako field 'id' zaznamu + zmenit asociativni
		// pole na indexovane.

		$wrapped = [];
		foreach ($result as $key => $val) {
			//rozsirit objekt o field ID
			$val->id = $val->campaignId;
			$wrapped[] = $val;
		}

		//Nakonec prehodit pole do fieldu 'item' nejakeho objektu.
		$itemized = new stdClass();
		$itemized->item = $wrapped;

		return $itemized;
	}

	/**
	 * {@inheritdoc}
	 *
	 * <b>Warning</b>: Adding contact is not an instant action. It will appear on your list after validation or after validation and confirmation (in case of double-optin procedure). You can set subscribe callback to be notified about successful adding.
	 *
	 * <b>Warning</b>: To update existing contact use methods such as set_contact_name, set_contact_customs or set_contact_cycle. Old param action is deprecated and ignored.
	 *
	 * <b>Warning</b>: Optin setting is locked to double optin by default - confirmation email will be sent to newly added contacts. If you want to add contacts already confirmed on your side please contact us using this form and provide us with your campaign name and the description of your business model. We will set single optin for this campaign after short verification.
	 *
	 * <b>Warning</b>: If you use this method as a way to handle your registration form, then you need to remember that this method does not allow to resubscribe a contact that was unsubscribed via a link, and also it is impossible to resend confirmation email using this kind of form.
	 */
	public function save_to_list($listId, $email)
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		$result = $this->sendGrRestRequest('post', 'contacts', [
			'campaign' => ['campaignId' => $listId],
			'email' => $email,
		]);

		return $result !== false;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param array $contactDetails Ty podporovane na urovni hlavni objektu se predaji u nej, nezname se predaji jako
	 * "custom" fieldy (viz odkaz nize na seznam podporovanych atributu).
	 *
	 * @see http://apidocs.getresponse.com/en/api/1.5.0/Contacts/add_contact Seznam podporovanych atributu
	 */
	public function save_to_list_details($listId, $email, $purpose = null, $contactDetails = [], $customFields = [])
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		$customs = [];
		$params = [
			'campaign' => ['campaignId' => $listId],
			'email' => $email,
		];
		//Preformatuj vstup dle pozadavku API.
		foreach ($contactDetails as $key => $value) {
			if (empty($key) || empty($value)) {
				continue;
			}
			if (in_array($key, ['name', 'dayOfCycle', 'ipAddress'])) {
				//globalni hodnoty nasyp k zakladni entite
				$params[$key] = $value;
			} else {
				//nepodporovane hodnoty jako custom atributy
				$item = new stdClass();
				$item->name = $key;
				$item->content = $value;
				$customs[] = $item;
			}
		}
		if (!empty($customs)) {
			$params['customs'] = $customs;
		}
		//Vlastni API call
		$result = $this->sendGrRestRequest('post', 'contacts', $params);

		return $result !== false;
	}

	/** @return mixed[] */
	public function get_contact_in_list_by_email(string $listId, string $email): ?array
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);

		$params = [
			'query[email]' => $email,
		];

		$contacts = $this->sendGrRestRequest('get', 'campaigns/' . $listId . '/contacts', $params);

		return $contacts ? (array) array_shift($contacts) : null;
	}

	// remove from list
	public function remove_from_list($listId, $email)
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);

		$contact = $this->get_contact_in_list_by_email($listId, $email);
		if (!isset($contact['contactId'])) {
			return false;
		}

		$result = $this->sendGrRestRequest('delete', 'contacts/' . $contact['contactId']);

		return !($result === false);
	}

	public function get_last_enter($listId)
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		$emptyRes = [
			'name' => '',
			'time' => 0,
			'count' => 0,
		];

		//Vlastni API call
		$result = $this->sendGrRestRequest('get', 'campaigns/' . $listId . '/contacts');
		if (!$result) {
			return $emptyRes;
		}

		//Najit nejmladsi
		date_default_timezone_set('UTC'); //potreba pro korektni funkci strtotime()
		$maxDate = strtotime('1970-01-01 00:00:00');
		$foundCid = '';
		$count = 0;
		foreach ($result as $cid => $val) {
			$count++;
			$createdOn = strtotime($val->createdOn);
			if ($createdOn > $maxDate) {
				$maxDate = $createdOn;
				$foundCid = $cid;
			}
		}
		//neexistuje zadny kontakt, seznam je prazdny
		if (empty($foundCid)) {
			return $emptyRes;
		}
		$contact = $result[$foundCid];

		return [
			'name' => (string) $contact->name,
			'time' => strtotime($contact->createdOn),
			'count' => $count,
		];
	}

	public function get_list_count($listId, $cached = false, $edit_mode = false, $deleted = false)
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		$result = $this->sendGrRestRequest('get', 'campaigns/statistics/list-size', [
			'query' => [
				'campaignId' => $listId,
				'groupBy' => 'total',
			],
		]);
		if (!$result) {
			return false;
		}

		if (isset($result[0]->totalSubscribers)) {
			return (int) $result[0]->totalSubscribers;
		}

		return false;
	}

	// return list of purposes
	public function get_purposes()
	{
		return null;
	}

	/**
	 * Sets API key for further use.
	 *
	 * @param string $apiKey API key to be used in successive API calls.
	 * @param string $enterpriseDomain If the GetResponse account is registered as 360/enterprise account, then this
	 *      value should be set to a domain to with the account belongs to.
	 */
	protected function setApiKey($apiKey, $enterpriseDomain = '')
	{
		$this->apiKey = $apiKey;
		if ($enterpriseDomain) {
			$this->apiUrlRest = 'https://api3.getresponse360.com/v3';
			$this->enterpriseHeader = 'X-Domain: ' . trim($enterpriseDomain);
		} else {
			$this->apiUrlRest = 'https://api.getresponse.com/v3';
			$this->enterpriseHeader = null;
		}
	}

	/**
	 * Performs the underlying HTTP request. Uses REST API interface.
	 *
	 * @param string $http_verb The HTTP verb to use: get, post, put, patch, delete.
	 * @param string $resource The API resource/method to be called.
	 * @param array $args Associative array of parameters to be passed with the call. For GET request this
	 *      is used as QUERY part of the request.
	 * @param int $timeout Timeout in seconds to wait for result.
	 * @return array Associative array of decoded result.
	 */
	private function sendGrRestRequest($http_verb, $resource, $args = [], $timeout = 10)
	{
		$url = $this->apiUrlRest . '/' . $resource;
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
		$headers = [
			'Accept: application/json',
			'Content-Type: application/json',
			'X-Auth-Token: api-key ' . $this->apiKey];
		if (!$this->enterpriseHeader) {
			$headers[] = $this->enterpriseHeader;
		}
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($handle, CURLOPT_USERAGENT, 'MioWeb/GetResponse/v3 (mioweb.cz)');
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($handle, CURLOPT_ENCODING, 'gzip,deflate');

		switch ($http_verb) {
			case 'post':
				$jsonData = json_encode($args, JSON_FORCE_OBJECT);
				curl_setopt($handle, CURLOPT_POST, true);
				curl_setopt($handle, CURLOPT_POSTFIELDS, $jsonData);

				break;
			case 'get':
				$query = http_build_query($args);
				curl_setopt($handle, CURLOPT_URL, $url . '?' . $query);

				break;
			case 'delete':
				curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');

				break;
			case 'patch':
				$jsonData = json_encode($args, JSON_FORCE_OBJECT);
				curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($handle, CURLOPT_POSTFIELDS, $jsonData);

				break;
			case 'put':
				$jsonData = json_encode($args, JSON_FORCE_OBJECT);
				curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($handle, CURLOPT_POSTFIELDS, $jsonData);

				break;
		}

		$response = curl_exec($handle);

		try {
			$cError = curl_error($handle);
		} catch (Exception $e) {
			$cError = $e->getMessage();
		}

		if ($cError) {
			$this->lastError = $this->_mwAPIConnectItem->getName() . ' API call failed. CURL error: ' . $cError . '';
			if ($this->errorsOn) {
				trigger_error($this->lastError, E_USER_ERROR);
			} else {
				return false;
			}
		}
		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		if (!(($httpCode == '200') || ($httpCode == '202') || ($httpCode == '204'))) {
			$error = json_decode($response);
			$this->lastError = $this->_mwAPIConnectItem->getName() . ' API call failed. Server returned "' . $error->message
			. '", http code=' . $httpCode . '.'
			. "\nCode " . $error->code . ': ' . ($error->codeDescription ?: '');//                . (isset($error->context) ? "\nContext" . print_r($error->context, true) : '')

			if ($this->errorsOn) {
				trigger_error($this->lastError, E_USER_ERROR);
			} else {
				return false;
			}
		}
		curl_close($handle);
		$response = json_decode($response);

		return $response;
	}

	public function showInApiSelector(?string $type = null): bool
	{
		return true;
	}

}
