<?php

class mwAPIConnectItemClient_se extends mwAPIConnectItemClient
{

	public function getApiUrl()
	{
		return 'https://app.smartemailing.cz/api/v3';
	}

	public function getApi2Url()
	{
		return 'https://app.smartemailing.cz/api/v2';
	}

	function checkSavedSetting(&$tosave): bool
	{
		$login = $tosave['login'] ?? '';
		$password = $tosave['password'] ?? '';

		if ($login && $password) {
			$tosave['login'] = $login = trim($login);
			$tosave['password'] = $password = trim($password);

			$isConnected = $this->isConnected($login, $password);
			mwMessages()->error(__('Přihlašovací údaje nejsou správné. Spojení se nezdařilo.', 'cms'));

			return $isConnected;
		}

		mwMessages()->error(__('Musíte vyplnit přihlašovací jméno i API klíč.', 'cms'));

		return false;
	}

	function isConnected($login, $password): bool
	{
		$url = $this->getApiUrl() . '/check-credentials';

		$response = wp_remote_post($url, [
			'method' => 'GET',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode(trim($login) . ':' . trim($password)),
			],
		]);

		if (is_wp_error($response)) {
			return false;
		}


		$ret = json_decode(wp_remote_retrieve_body($response));

		return $ret->status != 'error';
	}

	// return list of all forms
	public function get_forms_list()
	{
		$result = $this->sendRequest('/web-forms');

		if ($result === false) {
			return false;
		} else {
			if (isset($result->data)) {
				//Normalize result.
				$arr = [];
				foreach ($result->data as $val) {
					$arr[] = $val;
				}
				if ($result->meta->total_count > 500) {
					$pages = ceil($result->meta->total_count / 500);

					for ($i = 1; $i < $pages; $i++) {
						$result2 = $this->sendRequest('/web-forms?offset=500' . ($i * 500));

						if ($result2 === false) {
						} else {
							if (isset($result2->data)) {
								foreach ($result2->data as $val) {
									$arr[] = $val;
								}
							}
						}
					}
				}

				$itemized = new stdClass();
				$itemized->item = $arr;

				return $itemized;
			}
		}

		return false;
	}

	public function get_form($id)
	{
		$ret = $this->sendRequest('/web-form-structure/' . $id);

		if ($ret->status == 'error') {
			return false;
		} else {
			$seform = json_decode(json_encode((array) $ret->data), true);

			$save_form = [];
			$save_form['url'] = $seform['form_action'];
			$save_form['fields'] = $seform['structure'];
			$save_form['submit_in_new_window'] = $seform['submit_in_new_window'];
			if (isset($seform['purposes'])) {
				$save_form['purposes'] = $seform['purposes'];
			}
			$save_form['fields']['do'] = [
				'label' => '',
				'html_input_name' => 'do',
				'content' => 'webFormRenderer-webForm-submit',
				'html_input_type' => 'hidden',
			];
			$save_form['submit'] = $seform['submit'];

			return $save_form;
		}

		return false;
	}

	public function print_form($element, $form, $css_id, $added)
	{
		global $vePage;

		$form['fields']['referrer'] = [
			'label' => '',
			'html_input_name' => 'referrer',
			'content' => 'http' . ($_SERVER['SERVER_PORT'] == 443 ? 's://' : '://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'html_input_type' => 'hidden',
		];
		$form['fields']['sessionid'] = [
			'label' => '',
			'html_input_name' => 'sessionid',
			'content' => '',
			'html_input_type' => 'hidden',
		];

		$content = $vePage->print_seform($element, $form, $css_id);

		if (!MwCookies()->isPermitted('analytics')) {
			$content .= '<script src="' . $form['url'] . '&trackOnly=1"></script>';
			$content .= "<script type=\"text/javascript\">
jQuery(function($) {
	window._ssaq = window._ssaq || [];
	window._ssaq.push(['getSessionId', function(sessionId) {
		$('input[name=sessionid]').val(sessionId);
	}]);
});
</script>";
		}

		return $content;
	}

	// return list of all lists
	public function get_lists_list()
	{
		$result = $this->sendRequest('/contactlists');

		if ($result === false) {
			return false;
		} else {
			if (isset($result->data)) {
				//Normalize result.
				$arr = [];
				foreach ($result->data as $val) {
					$arr[] = $val;
				}

				if ($result->meta->total_count > 500) {
					$pages = ceil($result->meta->total_count / 500);

					for ($i = 1; $i < $pages; $i++) {
						$result2 = $this->sendRequest('/contactlists?offset=' . ($i * 500));

						if ($result2 === false) {
						} else {
							if (isset($result2->data)) {
								foreach ($result2->data as $val) {
									$arr[] = $val;
								}
							}
						}
					}
				}

				$itemized = new stdClass();
				$itemized->item = $arr;

				return $itemized;
			}
		}

		return false;
	}

	// save data to SE list
	public function save_to_list($listId, $email)
	{
		$data = [
			'settings' => [], // required
			'data' => [
				[
					'emailaddress' => $email,
					'contactlists' => [
						[
							'id' => $listId,
							'status' => 'confirmed',
						],
					],
				],
			],
		];

		$args = [
			'data_format' => 'body',
			'body' => json_encode($data),
		];

		$result = $this->sendRequest('/import', $args, 'POST');

		if ($result === false) {
			return false;
		} else {
			if ($result->status == 'created') {
				return true;
			}

			$this->lastError = $result->message;
		}

		return false;
	}

	// save array data user to SE list
	public function save_to_list_details($listId, $email, $purpose = null, $contactDetails = [], $customFields = [])
	{
		$contact = [
			'emailaddress' => $email,
			'contactlists' => [
				[
					'id' => $listId,
					'status' => 'confirmed',
				],
			],
		];

		if (is_array($contactDetails) && count($contactDetails)) {
			foreach ($contactDetails as $key => $val) {
				if ($val) {
					$contact[$key] = $val;
				}
			}
		}

		//custom fields
		if (is_array($customFields) && count($customFields)) {
			foreach ($customFields as $key => $val) {
				$contact['customfields'][] = [
					'id' => $key,
					'value' => $val,
				];
			}
		}

		if ($purpose !== null) {
			$contact['purposes'] = [
				[
					'id' => intval($purpose),
				],
			];
		}

		$data = [
			'settings' => [], // required
			'data' => [$contact],
		];

		$args = [
			'data_format' => 'body',
			'body' => json_encode($data),
		];

		$result = $this->sendRequest('/import', $args, 'POST');

		if ($result === false) {
			return false;
		} else {
			if ($result->status == 'created') {
				return true;
			}

			$this->lastError = $result->message;
		}

		return false;
	}

	// remove from list
	public function remove_from_list($listId, $email)
	{
		$data = [
			'settings' => [], // required
			'data' => [
				[
					'emailaddress' => $email,
					'contactlists' => [
						[
							'id' => $listId,
							'status' => 'removed',
						],
					],
				],
			],
		];

		$args = [
			'data_format' => 'body',
			'body' => json_encode($data),
		];

		$result = $this->sendRequest('/import', $args, 'POST');

		if ($result === false) {
			return false;
		} else {
			if ($result->status == 'created') {
				return true;
			}

			$this->lastError = $result->message;
		}

		return false;
	}

	// get list count

	public function get_list_count($listId, $cached = false, $edit_mode = false, $deleted = false)
	{
		$count = 0;

		// get cached variant
		if ($cached) {
			if ($edit_mode) {
				delete_transient('mioweb_se_list_count_' . $listId);
			}
			$cached_count = get_transient('mioweb_se_list_count_' . $listId);
			if ($cached_count !== false) {
				$count = $cached_count;
			} else {
				$count = $this->get_list_count($listId, false, false, $deleted);

				if ($count != false) {
					if (!$edit_mode) {
						set_transient('mioweb_se_list_count_' . $listId, $count, 60 * 5);
					}
				}
			}
		} else {
			// not cached variant
			$result = $deleted ? $this->sendRequest('/contactlists/' . $listId . '/added-contacts') : $this->sendRequest('/contactlists/' . $listId . '/distribution');

			if ($result === false || (isset($result->status) && $result->status == 'error')) {
				return false;
			}

			$count = $deleted ? (int) $result->data->count : (int) $result->data->total;
		}

		return $count;
	}

	// return list of purposes
	public function get_purposes()
	{
		$result = $this->sendRequest('/purposes');

		if ($result === false) {
			return null;
		} else {
			if (isset($result->data)) {
				$return = [];
				foreach ($result->data as $purpose) {
					$return[] = [
						'value' => $purpose->id,
						'name' => $purpose->name,
					];
				}

				return $return;
			}
		}

		return null;
	}

	/**
	 * @param string $path path to API endpoint.
	 * @param string $args add options to API default options
	 * @return false|mixed Vrati false, pokud volani zcela selze. Jinak vrati prichozi data.
	 */
	public function sendRequest($path, $args = [], $method = 'GET')
	{
		$login = $this->_mwAPIConnectItem->getOption();

		$default = [
			'method' => $method,
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode(trim($login['login']) . ':' . trim($login['password'])),
			],
		];

		if ($method == 'POST') {
			$default['headers']['Content-Type'] = 'application/json; charset=utf-8';
		}

		$param = wp_parse_args($args, $default);

		$url = $this->getApiUrl() . $path;

		$response = wp_remote_post($url, $param);

		if (is_wp_error($response)) {
			$error = $response->get_error_message();
			$this->lastError = 'SmartEmailing API call failed. CURL error: ' . $error . '';

			return false;
		}


		$ret = json_decode(wp_remote_retrieve_body($response));

		return $ret;
	}

	public function getNewApi($login, $password)
	{
		$xml = '
            <xmlrequest>
                <username>' . $login . '</username>
                <usertoken>' . $password . '</usertoken>
                <requesttype>Users</requesttype>
                <requestmethod>generateApiKey</requestmethod>
                <details>
                    <application>' . get_home_url() . '</application>
                </details>
            </xmlrequest>
            ';

		$result = $this->sendOldRequest($xml);

		if ($result === false) {
			return 0;
		}

		$data = @simplexml_load_string($result);
		if ($data->status == 'SUCCESS') {
			return (string) $data->data; //return $data->data[0];
		}

		return 0;
	}

	/**
	 * @param string $payload Vlastni pozadavek, neni-li jiz specifikovan pomocu URL.
	 * @param string $url Volitelne URL, pokud se nema pouzit jednotne URL celeho API.
	 * @param array $curlOptions Optional override of default CURL parameters. Include values like <code>
	 *                           (CURLOPT_TIMEOUT => 20)</code> that should be overridden.
	 * @return false|mixed Vrati false, pokud volani zcela selze. Jinak vrati prichozi data.
	 */
	public function sendOldRequest($payload, $url = '', $curlOptions = [])
	{
		if (!$url) {
			$url = $this->getApi2Url();
		}
		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_POST, 1);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($handle, CURLOPT_TIMEOUT, empty($curlOptions[CURLOPT_TIMEOUT]) ? 5 : (int) $curlOptions[CURLOPT_TIMEOUT]);

		try {
			$result = curl_exec($handle);

			try {
				$cError = curl_error($handle);
			} catch (Exception $e) {
				$cError = $e->getMessage();
			}
			if ($cError) {
				$this->lastError = 'SmartEmailing API call failed. CURL error: ' . $cError . '';
				$result = false;
			}
		} catch (Exception $e) {
			$this->lastError = 'SmartEmailing API call failed. CURL exec error: ' . $e->getMessage() . '';
			$result = false;
		}

		curl_close($handle);

		return $result;
	}

	public function showInApiSelector(?string $type = null): bool
	{
		return true;
	}

}
