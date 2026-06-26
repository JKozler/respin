<?php

function mwEmailingApi(): MwEmailingApi
{
   return MwEmailingApi::instance();
}

class MwEmailingApi
{

	protected static $_instance = null;

	public $api_list = ['se', 'getresponse', 'mailchimp', 'aweber', 'mailerlite', 'ecomail'];

	public $cachedLists;

	public $cachedPurposes;

	function registerApiClass($id)
	{
		$this->api_list[] = $id;
	}

	function __construct()
	{
	}

	function generate_api_select($name, $id, $value, $type, $hidePurposes = false)
	{
		$content = [];

		if (!is_array($value)) {
			$content['id'] = $value;
		} else {
			$content = $value;
		}

		if (!isset($content['api'])) {
			$content['api'] = 'se';
		}
		if (!isset($content['id'])) {
			$content['id'] = '';
		}

		$sublabel = $type == 'forms' ? __('Formulář', 'cms') : __('Seznam', 'cms');

		$ret = '<div class="mw_api_connection_container">';
		$ret .= '<div>';
		$ret .= $this->apiSelector($name, $id, $content, $type);
		$ret .= '</div>';
		$ret .= '<div class="set_form_subrow">';
		$ret .= '<div class="sublabel">' . $sublabel . '</div>';
		$ret .= '<div class="mw_api_selector_container">';
		$ret .= $this->apiObjectSelector($name, $id, $content['id'], $content['api'], $type, $hidePurposes, $content['purpose'] ?? null);
		$ret .= '</div>';
		$ret .= '</div>';
		$ret .= '</div>';

		if ($content['api'] != 'se') {
			$ret .= '<style>.form_look_setting {display: none;}</style>';
		}

		return $ret;
	}

	function apiSelector($name, $id, $content, $type)
	{
		// back compatibility (temporary)
		if (!isset($content['api'])) {
			if (isset($content['id'])) {
				$content['api'] = 'se';
			} else {
				foreach ($this->api_list as $key => $api) {
					$login = mwApiConnect()->getApi($api)->getOption();
					if (isset($login['status']) && $login['status']) {
						$content['api'] = $key;
					}
				}
			}
		}
		// end temporary

		$select = '<select class="change_api_selector" autocomplete="off" name="' . $name . '[api]" id="' . $id . '_api" data-type="' . $type . '" data-name="' . $name . '" data-id="' . $id . '">';
		foreach ($this->api_list as $api) {
			$apiItem = mwApiConnect()->getApi($api);
			if ($this->showInApiSelector($apiItem, $type)) {
				$select .= '<option value="' . $apiItem->getId() . '" ' . ($content['api'] == $apiItem->getId() ? ' selected="selected"' : '') . '>' . $apiItem->getName() . '</option>';
			}
		}
		$select .= '</select>';

		return $select;
	}

	function apiObjectSelector($name, $id, $content, $api, $type, $hidePurposes = false, ?string $selectedPurpose = null)
	{
		$apiItem = mwApiConnect()->getApi($api);

		$ret = '';
		$purposes = null;

		if ($apiItem->isConnected()) {
			if ($type == 'forms') {
				$obj = $this->get_forms_list($api);
			} else {
				$obj = $this->get_lists_list($api);
				if (!$hidePurposes) {
					$purposes = $this->get_purposes($api);
				}
			}

			if ($obj) {
				$options = '';
				foreach ($obj->item as $option) {
					$options = '<option value=\'' . $option->id . '\' ' . ($content == $option->id ? ' selected="selected"' : '') . '>' . $option->name . '</option>' . $options;
				}

				$options = '<option value="" ' . (stripslashes($content) == '' ? ' selected="selected"' : '') . '>' . ($type == 'forms' ? __('- Vyberte formulář -', 'cms') : __('- Vyberte seznam -', 'cms')) . '</option>' . $options;

				$ret .= '<select class="change_api_item_selector mw_whisperer" name="' . $name . '[id]" id="' . $id . '_id">' . $options . '</select>';

				if ($purposes) {
					$ret .= '<div class="set_form_subrow">';
					$ret .= '<div class="sublabel">' . __('Účel zpracování', 'cms') . '</div>';
					$ret .= mwAdminComponents::select([
						'name' => $name . '[purpose]',
						'tag_id' => $id . '_purpose',
						'options' => $purposes,
					], $selectedPurpose);
					$ret .= '</div>';
				}
			} else {
				$form = mwAdminComponents::messageBox(__('Služba je dočasně nedostupná. Prosím, zkuste to později znovu.', 'cms_ve'), ['type' => 'error']);
				$ret .= $form;
			}
		} else {
			$ret .= '<div class="api_connection_container">';
			$ret .= $apiItem->printConnectionButton('data-tagid="' . $id . '" data-name="' . $name . '" data-type="' . $type . '"');
			$ret .= '</div>';
		}

		return $ret;
	}

	function changeSelector_ajax()
	{
		echo $this->apiObjectSelector($_POST['tag_name'], $_POST['tag_id'], '', $_POST['api'], $_POST['type']);
		die();
	}

	function saveItemSetting_ajax()
	{
		$api = mwApiConnect()->getApi($_POST['api_id']);
		if ($api) {
			$status = $api->saveSetting($_POST['setting']);
			$selector = '';

			if ($status) {
				$selector = $this->apiObjectSelector($_POST['tag_name'], $_POST['tag_id'], '', $api->getId(), $_POST['type']);
			}

			wp_send_json([
				'status' => $status,
				'selector' => $selector,
				'success' => mwMessages()->success,
				'errors' => mwMessages()->errors,
				'html' => mwMessages()->writeHtml(),
			]);
		}
		die();
	}

	function get_forms_list($api)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$result = $mwAPIConnectItem->client()->get_forms_list();

		return $result;
	}

	function save_to_list_details($api, $listId, $email, $purpose = null, $contactDetails = [], $customFields = [])
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$status = $mwAPIConnectItem->client()->save_to_list_details($listId, $email, $purpose, $contactDetails, $customFields);

		return [
			'status' => $status,
			'message' => $mwAPIConnectItem->client()->getLastError(),
		];
	}

	function remove_from_list($api, $listId, $email)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$status = $mwAPIConnectItem->client()->remove_from_list($listId, $email);

		return [
			'status' => $status,
			'message' => $mwAPIConnectItem->client()->getLastError(),
		];
	}

	function get_lists_list($api)
	{
		if (isset($this->cachedLists[$api]) && !empty($this->cachedLists[$api])) {
			return $this->cachedLists[$api];
		}


		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$result = $mwAPIConnectItem->client()->get_lists_list();
		$this->cachedLists[$api] = $result;

		return $result;
	}

	function get_purposes($api)
	{
		if (isset($this->cachedPurposes[$api]) && !empty($this->cachedPurposes[$api])) {
			return $this->cachedPurposes[$api];
		}


		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$result = $mwAPIConnectItem->client()->get_purposes();
		$this->cachedPurposes[$api] = $result;

		return $result;
	}

	public function showInApiSelector(mwAPIConnectItem $apiItem, string $type): bool
	{
		return $apiItem->client()->showInApiSelector($type);
	}

	function get_form($content, $edit_mode)
	{
		$api = $content['api'];
		$form_id = $content['id'];

		$mwAPIConnectItem = mwApiConnect()->getApi($api);

		$form = [];

		// cached variant
		if ($api == 'mailchimp' || $api == 'se') {
			$cached_form = get_option('mioweb_' . $api . 'form_' . $form_id);

			if ($edit_mode) {
				delete_transient('mioweb_' . $api . 'form_' . $form_id . 'transient');
			}
			if (get_transient('mioweb_' . $api . 'form_' . $form_id . 'transient') && $cached_form) {
				$form = $cached_form;
			} else {
				$form = $mwAPIConnectItem->client()->get_form($form_id);

				if (!empty($form) && $form != false) {
					update_option('mioweb_' . $api . 'form_' . $form_id, $form);
					if (!$edit_mode) {
						set_transient('mioweb_' . $api . 'form_' . $form_id . 'transient', 1, 60 * 5);
					}
				} elseif ($cached_form) {
					if (!$edit_mode) {
						$form = $cached_form;
					}
					if (!$edit_mode) {
						set_transient('mioweb_' . $api . 'form_' . $form_id . 'transient', 1, 60 * 5);
					}
				}
			}
		} else {
			// not cached variant
			$form = $mwAPIConnectItem->client()->get_form($form_id);
		}

		return $form;
	}

	function print_form($api, $element, $form, $css_id, $added)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$result = $mwAPIConnectItem->client()->print_form($element, $form, $css_id, $added);

		return $result;
	}

	function repair_content_val($val)
	{
		if (!is_array($val)) {
			$old_content = $val;
			$val = [];
			$val['id'] = $old_content;
		}
		if (!isset($val['api'])) {
			$val['api'] = 'se';
		}

		return $val;
	}

	/** @return MwEmailingApi. */
	public static function instance(): self
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}
}

function field_type_form_select($field, $meta, $group_name, $group_id)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');

	echo mwEmailingApi()->generate_api_select($group_name . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, 'forms');
}

function field_type_list_select($field, $meta, $group_name, $group_id)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');

	$hidePurposes = $field['hide_purposes'] ?? false;

	echo mwEmailingApi()->generate_api_select($group_name . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, 'lists', $hidePurposes);
}

// field type - authorization link/button to authorize external API (Aweber)
function field_type_authorize_api($field, $meta, $group_id)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');
	echo '<a class="cms_authorization_link" href="' . $content . '" target="_blank">' . __('Vygenerovat autorizační kód.', 'cms') . '</a>';
}
