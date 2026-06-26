<?php

function mwSellingApi(): MwSellingApi
{
   return MwSellingApi::instance();
}

class MwSellingApi
{

	protected static $_instance = null;

	public $api_list = ['fapi', 'simpleshop'];

	function registerApiClass($id)
	{
		$this->api_list[] = $id;
	}

	function isApiRegistered($id)
	{
		return in_array($id, $this->api_list);
	}

	function __construct()
	{
	}

	function generate_api_select($name, $id, $value)
	{
		$content = [];

		if (!is_array($value)) {
			$content['id'] = $value;
		} else {
			$content = $value;
		}

		if (!isset($content['api'])) {
			$content['api'] = 'mioweb';
		}

		if (!$this->isApiRegistered($content['api'])) {
			$content['api'] = 'fapi';
			$content['id'] = '';
		}

		if (!isset($content['id'])) {
			$content['id'] = '';
		}
		?>

		<div class="mw_api_connection_container">
			<div>
				<?php
				echo $this->apiSelector($name, $id, $content);
				?>
			</div>
			<div class="set_form_subrow">
				<div class="mw_api_selector_container">
					<?php echo $this->apiObjectSelector($name, $id, $content['id'], $content['api']); ?>
				</div>
			</div>
			<div class="cms_clear"></div>
		</div>

		<?php

		if ($content['api'] != 'fapi') {
			echo '<style>.form_look_setting {display: none;}</style>';
		}
	}

	function apiSelector($name, $id, $content)
	{
		// back compatibility (temporary)
		if (!isset($content['api'])) {
			if (isset($content['id'])) {
				$content['api'] = 'fapi';
			} else {
				foreach ($this->api_list as $api) {
					$login = mwApiConnect()->getApi($api)->getOption();
					if (isset($login['status']) && $login['status']) {
						$content['api'] = $api;
					}
				}
			}
		}
		// end temporary

		$select = '<select class="change_api_selector" autocomplete="off" name="' . $name . '[api]" id="' . $id . '_api" data-name="' . $name . '" data-id="' . $id . '">';
		foreach ($this->api_list as $api) {
			$apiItem = mwApiConnect()->getApi($api);
			$select .= '<option value="' . $apiItem->getId() . '" ' . ($content['api'] == $apiItem->getId() ? ' selected="selected"' : '') . '>' . $apiItem->getName() . '</option>';
		}
		$select .= '</select>';

		return $select;
	}

	function apiObjectSelector($name, $id, $content, $api)
	{
		$apiItem = mwApiConnect()->getApi($api);

		$ret = '';

		if ($apiItem->isConnected() || $apiItem->getId() == 'mioweb') {
			$obj = $this->getFormsList($api);

			if ($obj) {
				$options = '';
				$currentUrl = '';
				foreach ($obj as $option) {
					$url = '';
					$info = '';
					if ($api === 'mioweb') {
						$code = $option['id'];
						$url = mwSetting()->getObject('mwsform')->getEditUrl($option['id']);
					} elseif ($api === 'fapi') {
						$code = $option['html_code_without_style'];
						$url = 'https://web.fapi.cz/forms/preview/' . $option['id'];
						$info = $option['new_layout'] ?? '';
					} elseif ($api === 'simpleshop') {
						$code = base64_encode($option['script_iframe']);
					}

					if ($api === 'mioweb' || !isset($option['mioweb_eshop']) || !$option['mioweb_eshop']) {
						$options = '<option value=\'' . $code . '\' ' . (str_replace('&', '&amp;', stripslashes($content)) == $code ? ' selected="selected"' : '') . ' data-url="' . $url . '" data-info="' . $info . '">' . $option['name'] . '</option>' . $options;

						if (str_replace('&', '&amp;', stripslashes($content)) == $code) {
							$currentUrl = $url;
						}
					}
				}

				$options = '<option value="" ' . (stripslashes($content) == '' ? ' selected="selected"' : '') . '>' . __('- Vyberte formulář -', 'cms') . '</option>' . $options;

				$ret .= '<div class="mw_api_item_selector_container mw_flex_field ' . ($content ? 'selected' : '') . '">';
				$ret .= '<select class="change_api_item_selector mw_whisperer" name="' . $name . '[id]" id="' . $id . '_id">' . $options . '</select>';
				if ($api === 'mioweb' || $api === 'fapi') {
					$ret .= mwAdminComponents::iconLink([
						'icon' => 'edit-2',
						'title' => __('Upravit formulář', 'cms'),
						'target' => '_blank',
						'link' => $content ? $currentUrl : '',
					], 'mw_icon_button mw_icon_button_edit');
				}
				$ret .= '</div>';
			} else {
				$ret .= mwAdminComponents::messageBox(__('Nebyly nalezeny žádné prodejní formuláře.', 'cms'), ['type' => 'info_gray']);
			}
		} else {
			$ret .= '<div class="api_connection_container">';
			$ret .= $apiItem->printConnectionButton('data-tagid="' . $id . '" data-name="' . $name . '"');
			$ret .= '</div>';
		}

		return $ret;
	}

	function changeSelector_ajax()
	{
		echo $this->apiObjectSelector($_POST['tag_name'], $_POST['tag_id'], '', $_POST['api']);
		die();
	}

	function saveItemSetting_ajax()
	{
		$api = mwApiConnect()->getApi($_POST['api_id']);
		if ($api) {
			$status = $api->saveSetting($_POST['setting']);
			$selector = '';

			if ($status) {
				$selector = $this->apiObjectSelector($_POST['tag_name'], $_POST['tag_id'], '', $api->getId());
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

	function sendMemberInfo()
	{
		$mwAPIConnectItem = mwApiConnect()->getApi('simpleshop');
		if ($mwAPIConnectItem->isConnected()) {
			$mwAPIConnectItem->client()->sendInfo();
		}
	}

	function sendConversionTable(array $convertTable, array $newSections)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi('simpleshop');
		if ($mwAPIConnectItem->isConnected()) {
			return $mwAPIConnectItem->client()->sendConversionTable($convertTable, $newSections);
		}

		return null;
	}

	function getFormsList($api)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$result = $mwAPIConnectItem->client()->getFormsList();

		return $result;
	}

	function getProductsList($api)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$result = $mwAPIConnectItem->client()->getProductList();

		return $result;
	}

	function printForm($api, $element, $css_id, $post_id, $edit_mode, $added)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		$result = $mwAPIConnectItem->client()->printForm($element, $css_id, $post_id, $edit_mode, $added);

		return $result;
	}

	function getOrder($api, $id): ?array
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);

		return $mwAPIConnectItem->isConnected() ? $mwAPIConnectItem->client()->getOrder($id) : null;
	}

	function getInvoice($api, $id)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		if ($mwAPIConnectItem->isConnected()) {
			$result = $mwAPIConnectItem->client()->getInvoice($id);

			return $result;
		}

		return false;
	}

	function getInvoiceVariables($api, $id)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		if ($mwAPIConnectItem->isConnected()) {
			$result = $mwAPIConnectItem->client()->getInvoiceVariables($id);

			return $result;
		}

		return [];
	}

	function getPurchaseEventData($api, $id, $funnel = null)
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);
		if ($mwAPIConnectItem->isConnected()) {
			$result = $mwAPIConnectItem->client()->getPurchaseEventData($id, $funnel);

			return $result;
		}

		return false;
	}

	function getSettings($api): array
	{
		$mwAPIConnectItem = mwApiConnect()->getApi($api);

		return $mwAPIConnectItem->isConnected() ? $mwAPIConnectItem->client()->getSettings() : [];
	}

	function productSelector($name, $id, $value, $api, $products)
	{
		$content = '';

		if ($products) {
			$options = '';

			foreach ($products as $option) {
				if (!isset($option['mioweb_eshop']) || !$option['mioweb_eshop']) {
					$options = '<option value="' . $option['code'] . '" ' . ($value == $option['code'] ? ' selected="selected"' : '') . '>' . $option['name'] . '</option>' . $options;
				}
			}
			$options = '<option value="" ' . ($value == '' ? ' selected="selected"' : '') . '>' . __('- Vyberte produkt -', 'cms_ve') . '</option>' . $options;
			$content .= '<select class="mw_api_product_selector" name="' . $name . '" id="' . $id . '_id">' . $options . '</select>';
		} else {
			$form = mwAdminComponents::messageBox(__('Nebyly nalezeny žádné produkty.', 'cms_ve'), ['type' => 'error']);
			$content .= $form;
		}

		return $content;
	}

	/** @return MwSellingApi. */
	public static function instance(): self
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}
}

function field_type_sale_form_select($field, $meta, $group_id)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');

	MwSellingApi()->generate_api_select($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content);
}
