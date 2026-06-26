<?php

class mwAPIConnectItemClient_mailchimp extends mwAPIConnectItemClient
{

	/**
	 * True to enable printing, false otherwise. Set using the constructor
	 *
	 * @var boolean
	 * @access private
	 */
	private $errorsOn = false;

	/**
	 * GetResponse API key
	 *
	 * @link http://www.getresponse.com/my_api_key.html
	 * @var string
	 */
	public $apiKey = 'USE setApiKey() METHOD';

	/** @var string URL endpoint for REST API calls. */
	public $apiUrl = '';

	public static function getApiName()
	{
		return 'MailChimp';
	}

	public function getApiUrl()
	{
		return 'NOT SUPPORTED';
	}

	/**
	 * Sets API key for further use.
	 *
	 * @param string $apiKey API key to be used in successive API calls.
	 */
	protected function setApiKey($apiKey)
	{
		$this->apiKey = $apiKey;
		$parts = explode('-', $this->apiKey);
		$datacentre = count($parts) > 1 && isset($parts[1])
		? $parts[1]
		: 'not_valid_api_key';
		$this->apiUrl = str_replace('<dc>', $datacentre, 'https://<dc>.api.mailchimp.com/3.0');
	}

	function checkSavedSetting(&$tosave): bool
	{
		$password = $tosave['password'] ?? '';

		if ($password) {
			$tosave['password'] = $password = trim($password);

			$isConnected = $this->isConnected($password);
			mwMessages()->error(__('Přihlašovací údaje nejsou správné. Spojení se nezdařilo.', 'cms'));

			return $isConnected;
		}

		mwMessages()->error(__('Musíte vyplnit přihlašovací jméno i API klíč.', 'cms'));

		return false;
	}

	function isConnected($password): bool
	{
		$this->setApiKey($password);
		$result = $this->sendMcRequest('get', '', ['fields' => 'account_id']);

		return $result && $result->account_id;
	}

	/**
	 * Performs the underlying HTTP request. Uses REST API interface of MailChimp
	 *
	 * @param string $http_verb The HTTP verb to use: get, post, put, patch, delete.
	 * @param string $resource The API resource/method to be called.
	 * @param array $args Associative array of parameters to be passed with the call. For GET request this
	 *                              is used as QUERY part of the request.
	 * @param int $timeout Timeout in seconds to wait for result.
	 * @return array Associative array of decoded result.
	 */
	private function sendMcRequest($http_verb, $resource, $args = [], $timeout = 10)
	{
		$url = $this->apiUrl . '/' . $resource;
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_HTTPHEADER, [
			'Accept: application/vnd.api+json',
			'Content-Type: application/vnd.api+json',
			'Authorization: apikey ' . $this->apiKey]);
		curl_setopt($handle, CURLOPT_USERAGENT, 'MioWeb/MailChimp-API/3.0 (mioweb.cz)');
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);

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
			$this->lastError = 'MailChimp API call failed. CURL error: ' . $cError . '';
			if ($this->errorsOn) {
				trigger_error($this->lastError, E_USER_ERROR);
			} else {
				return false;
			}
		}
		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		if (!($httpCode == '200') || ($httpCode == '204')) {
			$error = json_decode($response);
			$this->lastError = 'MailChimp API call failed. Server returned "' . $error->title . '", status code ' . $httpCode . '. '
			. $error->detail
			. (isset($error->errors) ? json_encode($error->errors, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE) : '');
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

	private function get_listform_fields($listId, $includeEmailField = true)
	{
		$result = $this->sendMcRequest('get', 'lists/' . $listId . '/merge-fields', [
			'fields' => 'merge_fields.merge_id,merge_fields.tag,merge_fields.name,merge_fields.required'
				. ',merge_fields.type,merge_fields.default_value,merge_fields.public'
				. ',merge_fields.help_text,merge_fields.display_order,merge_fields.options',
		]);
		if ($result) {
			//Standardize output format according to SE field format.
			foreach ($result->merge_fields as $key => $value) {
				$value->id = $value->merge_id;
				unset($value->merge_id);
			}

			if ($includeEmailField) {
				//Add the email field, which is not returned by the MailChimp api, but that is always present in the subscribe forms.
				$field = new stdClass();
				$field->id = 0;
				$field->tag = 'EMAIL';
				$field->name = __('E-mail', 'cms');
				$field->required = true;
				$field->type = 'email';
				$field->default_value = null;
				$field->public = true;
				$field->help_text = '';
				$field->display_order = $this->get_email_display_order($result->merge_fields);

				$result->merge_fields[] = $field;
			}

			//Make the array "itemized".
			$itemized = new stdClass();
			$itemized->item = $result->merge_fields;

			return $itemized;
		} else {
			return false;
		}
	}

	public function get_forms_list()
	{
		error_log(__METHOD__);

		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		$result = $this->sendMcRequest('get', 'lists', [
			'fields' => 'lists.id,lists.name,lists.subscribe_url_short,lists.subscribe_url_long',
		]);
		if (!$result) {
			return false;
		}

		$wrapped = [];
		foreach ($result->lists as $key => $val) {
			//Modify name of the list to be as subscribe form.
			$val->name .= ' ' . __('(signup form)', 'cms');

			$val->url = $val->subscribe_url_long;
			unset($val->subscribe_url_long);

			//Load available fields.
			$val->fields = $this->get_listform_fields($val->id, true); //in error case value will be FALSE, which is OK

			//Add to resulting array
			$wrapped[$val->id] = $val;
		}

		//Compose the result object with "item" field.
		$itemized = new stdClass();
		$itemized->item = $wrapped;

		return $itemized;
	}

	public function get_form($id)
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		$result = $this->sendMcRequest('get', 'lists/' . $id, [
			'fields' => 'id,name,subscribe_url_short,subscribe_url_long',
		]);
		if (!$result) {
			return false;
		}

		//Modify name of the list to be as subscribe form.
		$result->name .= ' ' . __('(signup form)', 'cms');

		$url = $result->subscribe_url_long;
		unset($result->subscribe_url_long);
		$url = str_replace('/subscribe?', '/subscribe/post?', $url);
		$result->url = $url;

		//Load available fields.
		$result->fields = $this->get_listform_fields($result->id, true); //in error case value will be FALSE, which is OK

		//Parse URL data
		//        $urlParts = explode('/', $result->url);

		//Generate output data
		$save_form = [];
		$save_form['url'] = $result->url;
		$save_form['fields'] = $result->fields->item;
		//        $save_form['fields']['referrer']=array(
		//            'label'=>'',
		//            'fieldname'=>'referrer',
		//            'defaultfield'=>'',
		//            'content'=>"http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		//            'customfield_type'=>'hidden',
		//            'required'=>'',
		//        );
		//        $save_form['fields']['do']=array(
		//            'label'=>'',
		//            'fieldname'=>'do',
		//            'defaultfield'=>'',
		//            'content'=>'webFormRenderer-webForm-submit',
		//            'customfield_type'=>'hidden',
		//            'required'=>'',
		//        );
		$save_form['submit'] = __('Odeslat', 'cms');

		return $save_form;
	}

	public function print_form($element, $form, $css_id, $added)
	{
		global $vePage;
		//Update fields according to need of vePage->print_form(). Each field definition must be an array.
		/*
		'label'=>'',
		'fieldname'=>'do',
		'defaultfield'=>'',
		'content'=>'webFormRenderer-webForm-submit',
		'customfield_type'=>'hidden',
		'required'=>'',
		*/

		// Sort fields by field->display_order
		usort($form['fields'], function ($a, $b) {
			return strcmp($a->display_order, $b->display_order);
		});

		$fieldsVE = [];

		foreach ($form['fields'] as $key => $value) {
			$fieldDef = [];

			$fieldDef['label'] = $value->name;
			$fieldDef['fieldname'] = $value->tag;
			$fieldDef['defaultfield'] = $value->default_value;
			$fieldDef['required'] = ($value->required ? '1' : '');
			$fieldDef['errormessage'] = $value->help_text;
			//            $fieldDef['content'] = '';
			//identifier for NAME in INPUT tag (<input name=XXX>) generated form
			$fieldDef['inputname'] = 'MERGE' . $value->id;

			//additional fields
			$fieldDef['display_order'] = $value->display_order;
			$fieldDef['id'] = $value->id;

			if (!$value->public) {
				$fieldDef['customfield_type'] = 'hidden';
			} else {
				switch ($value->type) {
					//TODO add support for all MailChimp datatypes
					case 'number':
						//TODO add some helpers and checks for integer input
						$fieldDef['customfield_type'] = 'text';

						break;
					case 'dropdown':
						$fieldDef['customfield_type'] = 'select';
						$choices = $value->options->choices ?? [];
						$options = [];
						foreach ($choices as $choice) {
							$options[] = [
								'id' => $choice,
								'name' => $choice,
							];
						}

						$fieldDef['options'] = $options;

						break;
					default:
						$fieldDef['customfield_type'] = $value->type;
				}
			}
			//Set the output, indext by original TAG value.
			$fieldsVE[$value->tag] = $fieldDef;
		}
		unset($form['fields']);
		$form['fields'] = $fieldsVE;

		$content = $vePage->print_form($element, $form, $css_id);
		//        $content.='<script src="'. $form['url'] . '&trackOnly=1"></script>';

		return $content;
		//        return '<div class="ve_content_form_container">'.$content.'</div>';
	}

	public function get_lists_list()
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		$result = $this->sendMcRequest('get', 'lists/', [
			'fields' => 'lists.id,lists.name,lists.subscribe_url_short,lists.subscribe_url_long',
		]);
		if (!$result) {
			return false;
		}

		$wrapped = [];
		foreach ($result->lists as $key => $val) {
			$wrapped[$val->id] = $val;
		}

		//Compose the result value as "item" field.
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
		/*
		{
		"email_type": "",
		"status": "",
		"status_if_new": "",
		"merge_fields": {},
		"interests": {},
		"language": "",
		"vip": "",
		"location": {
		"latitude": "",
		"longitude": ""
		}
		}
		*/
		$result = $this->sendMcRequest(
			'post',
			'lists/' . $listId . '/members?fields=unique_email_id,id,status,merge_fields',
			[
				'email_address' => $email,
				'email_type' => 'html',
				'status' => 'subscribed', // pending
			//            'merge_fields' => array('FNAME' => '', 'LNAME' => '', ...),
			]
		);
		if (!$result) {
			return false;
		}

		if (isset($result->status) && ($result->status == 'subscribed')) {
			return true;
		}

		if (@!empty($result->message)) {
			$this->lastError = $result->message . " ({$result->code})";
		}

		return false;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param array $contactDetails Unrecognized fields will be passed as custom fields. In MailChimp that means as MERGE_FIELDS.
	 *      Supported MERGE_FIELDS can be gained from the associated form with a {@link get_form()} call.
	 */
	public function save_to_list_details($listId, $email, $purpose = null, $contactDetails = [], $customFields = [])
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);
		/*
		{
		"email_type": "",
		"status": "",
		"status_if_new": "",
		"merge_fields": {},
		"interests": {},
		"language": "",
		"vip": "",
		"location": {
		"latitude": "",
		"longitude": ""
		}
		}
		*/

		//Adapt input to API format.
		$hContact = [];
		$hContact['merge_fields'] = [];
		foreach ($contactDetails as $key => $value) {
			if (empty($key) || empty($value)) {
				continue;
			}
			if (in_array($key, ['email_address', 'email_type', 'status', 'status_if_new', 'interests', 'language', 'vip', 'location'])) {
				$hContact[$key] = $value;
			} else {
				if (strtolower($key) === 'name') {
					$hContact['merge_fields']['FNAME'] = $hContact['merge_fields']['NAME'] = $hContact['merge_fields']['JMENO'] = $value;
				} elseif (strtolower($key) === 'surname') {
					$hContact['merge_fields']['LNAME'] = $hContact['merge_fields']['PRIJMENI'] = $value;
				} elseif (strtolower($key) === 'phone') {
					$hContact['merge_fields']['PHONE'] = $hContact['merge_fields']['TELEFON'] = $value;
				}
			}
		}

		//Email address
		$hContact['email_address'] = $email;
		//Some default values.
		if (!isset($hContact['status']) || !$hContact['status']) {
			$hContact['status'] = 'subscribed';
		}
		if (!isset($hContact['email_type']) || !$hContact['email_type']) {
			$hContact['email_type'] = 'html';
		}

		$result = $this->sendMcRequest(
			'post',
			'lists/' . $listId . '/members?fields=unique_email_id,id,status,merge_fields',
			$hContact
		);
		if (!$result) {
			return false;
		}

		if (isset($result->status) && ($result->status == 'subscribed')) {
			return true;
		}

		if (@!empty($result->message)) {
			$this->lastError = $result->message . " ({$result->code})";
		}

		return false;
	}

	// remove from list
	public function remove_from_list($listId, $email)
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);

		//Adapt input to API format.
		$hContact = [
			'merge_fields' => [],
			'email_address' => $email,
			'status' => 'unsubscribed',
			'email_type' => 'html',
		];

		$result = $this->sendMcRequest(
			'put',
			'lists/' . $listId . '/members/' . md5($email) . '?fields=unique_email_id,id,status,merge_fields',
			$hContact
		);
		if (!$result) {
			return false;
		}

		if (isset($result->status) && ($result->status === 'unsubscribed')) {
			return true;
		}

		if (@!empty($result->message)) {
			$this->lastError = $result->message . " ({$result->code})";
		}

		return false;
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

		$result = $this->sendMcRequest('get', 'lists/' . $listId . '/members', [
			'fields' => 'members.id,members.timestamp_opt,members.merge_fields',
		]);
		if (!$result) {
			return $emptyRes;
		}

		//Find youngest
		date_default_timezone_set('UTC'); //necessity for strtotime to behave correctly, we are working in UTC
		$maxDate = strtotime('1970-01-01 00:00:00');
		$foundCid = '';
		$count = 0;
		foreach ($result->members as $cid => $val) {
			$count++;
			$createdOn = strtotime($val->timestamp_opt);
			if ($createdOn > $maxDate) {
				$maxDate = $createdOn;
				$foundCid = $cid;
			}
		}
		//No contact found, list is empty.
		if (empty($foundCid)) {
			return $emptyRes;
		}
		$contact = $result->members[$foundCid];

		return [
			'name' => (string) trim($contact->merge_fields->FNAME . ' ' . $contact->merge_fields->LNAME),
			'time' => strtotime($contact->timestamp_opt),
			'count' => $count,
		];
	}


	public function get_list_count($listId, $cached = false, $edit_mode = false, $deleted = false)
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$this->setApiKey($login['password']);

		$result = $this->sendMcRequest('get', 'lists/' . $listId . '/members', [
			'fields' => 'total_items',
		]);
		if (!$result) {
			return false;
		}

		if (isset($result->total_items)) {
			return (int) $result->total_items;
		}

		return false;
	}

	// return list of purposes
	public function get_purposes()
	{
		return null;
	}

	/**
	 * @param stdClass[] $mergeFields
	 * @return int
	 */
	private function get_email_display_order(array $mergeFields): int
	{
		// Create array of returned display orders
		$displayOrders = [];
		foreach ($mergeFields as $mergeField) {
			$displayOrders[] = $mergeField->display_order;
		}

		// Generate range of integers from 1 to "number of fields"
		$orderRange = range(1, count($mergeFields));

		// Find missing `display_order` from Mailchimp response - that's e-mail field
		$unusedOrders = array_diff($orderRange, $displayOrders);

		return $unusedOrders ? reset($unusedOrders) : 0;
	}

	public function showInApiSelector(?string $type = null): bool
	{
		return true;
	}

}
