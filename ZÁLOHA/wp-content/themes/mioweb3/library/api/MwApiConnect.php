<?php

/**
 * Class for setting
 */
function mwApiConnect(): mwApiConnect
{
	return MwApiConnect::instance();
}

class MwApiConnect
{

	protected static $_instance = null;

	/** @var mwAPIConnectItem[] */
	protected $_apis;

	protected $_tags;

	function __construct()
	{
		add_action('wp_ajax_mwOpenApiItemSetting', [$this, 'openItemSetting_ajax']);
		add_action('wp_ajax_mwSaveApiItemSetting', [$this, 'saveItemSetting_ajax']);
		add_action('wp_ajax_mwSaveSellingApiItemSetting', [mwSellingApi(), 'saveItemSetting_ajax']);
		add_action('wp_ajax_mwSellingChangeSelector', [mwSellingApi(), 'changeSelector_ajax']);
		add_action('wp_ajax_mwSaveEmailingApiItemSetting', [mwEmailingApi(), 'saveItemSetting_ajax']);
		add_action('wp_ajax_mwEmailingChangeSelector', [mwEmailingApi(), 'changeSelector_ajax']);
	}

	function addApi($id, $args): void
	{
		$this->_apis[$id] = new mwAPIConnectItem($id, $args);
	}

	function getApi($id): ?mwAPIConnectItem
	{
		return $this->_apis[$id] ?? null;
	}

	/** @return mwAPIConnectItem[] */
	public function getApis(): array
	{
		return $this->_apis;
	}

	function printSettingList()
	{
		$content = '<div class="mw_api_list">';
		foreach ($this->_apis as $api) {
			if (!empty($api->getSetting())) {
				$content .= '<a href="#" class="mw_api_item mw_rounded mw_shadow ' . ($api->isConnected() ? 'mw_api_item_connected' : 'mw_api_item_disconnected') . '" data-api="' . $api->getId() . '">';
				$content .= '<img src="' . $api->getLogoUrl() . '" />';
				$content .= '<span class="mw_api_item_title">';
				$content .= $api->getName();
				if ($api->client()->isDebugMode()) {
					$content .= '<strong class="mw_api_item_debug_text">' . __('Testovací režim', 'cms') . '</strong>';
				}
				$content .= '</span>';
				$content .= mwAdminComponents::icon([
					'icon' => 'check',
				], 'mw_api_item_connected_icon');
				$content .= '</a>';
			}
		}
		$content .= '</div>';
		echo $content;
	}

	function openItemSetting_ajax()
	{
		$apiId = $_POST['api_id'];
		$api = $this->getApi($apiId);
		if ($api) {
			$api->getFormSetting();
		}
		die();
	}

	function saveItemSetting_ajax()
	{
		$apiId = $_POST['api_id'];
		$api = $this->getApi($apiId);
		if ($api) {
			$status = $api->saveSetting($_POST['setting']);

			wp_send_json([
				'status' => $status,
				'success' => mwMessages()->success,
				'errors' => mwMessages()->errors,
				'html' => mwMessages()->writeHtml(),
			]);
		}
		die();
	}

	/** @return MwApiConnect Returns singleton instance of MwApiConnect. */
	public static function instance(): self
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}

class mwAPIConnectItem
{

	private $_id;

	private $_name;

	private $_tags;

	private $_setting;

	private $_client;

	public function __construct($id, $args)
	{
		$this->_id = $id;
		$this->_name = $args['name'];
		$this->_tags = $args['tags'] ?? [];
		$this->_setting = $args['setting'] ?? [];
	}

	public function getId(): string
	{
		return $this->_id;
	}

	public function getName(): string
	{
		return $this->_name;
	}

	public function getTags(): array
	{
		return $this->_tags;
	}

	public function getSetting(): array
	{
		return $this->_setting;
	}

	/** @param mixed[] $settings */
	public function setSetting(array $settings): void
	{
		$this->_setting = $settings;
	}

	public function isConnected(): bool
	{
		$option = $this->getOption();

		return $this->_id == 'mioweb' ? true : $option['status'] ?? false;
	}

	public function getStatusText(): string
	{
		return $this->isConnected() ? '<span class="mw_api_status_text mw_api_status_text_connected">' . __('Připojeno', 'cms') . '<span>' : '<span class="mw_api_status_text mw_api_status_text_disconnected">' . __('Nepřipojeno', 'cms') . '<span>';
	}

	public function getLogoUrl(): string
	{
		return API_FILE_URL . '/' . $this->_id . '/logo.jpg';
	}

	public function client(): mwAPIConnectItemClient
	{
		if (!$this->_client) {
			$this->_client = $this->newClient();
		}

		return $this->_client;
	}

	private function newClient(): mwAPIConnectItemClient
	{
		require_once(__DIR__ . '/' . $this->_id . '/client.php');
		$clientClass = 'mwAPIConnectItemClient_' . $this->_id;

		return new $clientClass($this);
	}

	public function getFormSetting()
	{
		echo mwAdminComponents::title([
			'text' => $this->getName(),
			'onright' => $this->getStatusText(),
		], 'h2');
		write_meta($this->getSetting(), $this->getOption(), 'setting', 'setting');
		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="api_id" value="' . $this->getId() . '"/>';
	}

	public function getOption()
	{
		return get_option('mw_api_connection_' . $this->_id);
	}

	public function saveSetting($tosave)
	{
		$status = $this->client()->checkSavedSetting($tosave);
		$tosave['status'] = $status;

		$option = $this->getOption();

		if ($option !== false) {
			update_option('mw_api_connection_' . $this->_id, $tosave);
		} else {
			add_option('mw_api_connection_' . $this->_id, $tosave);
		}

		return $status;
	}

	function printConnectionButton($attrs = '')
	{
		$attrs .= ' data-api="' . $this->_id . '"';
		$content = '<div class="mw_api_connect_button_container">';
		$content .= mwAdminComponents::messageBox(sprintf(__('Nejdříve je nutné propojit %s s Miowebem.', 'cms'), $this->getName()), ['type' => 'info_gray']);
		$content .= mwAdminComponents::button([
			'button_text' => __('Propojit', 'cms'),
			'attrs' => $attrs,
		], 'mw_api_connect_button add_api_connection');
		$content .= '</div>';

		return $content;
	}

}

abstract class mwAPIConnectItemClient
{

	protected $_mwAPIConnectItem;

	protected $lastError;

	public function __construct(mwAPIConnectItem $mwAPIConnectItem)
	{
		$this->_mwAPIConnectItem = $mwAPIConnectItem;
	}

	abstract public function checkSavedSetting(&$tosave): bool;

	public function getLastError()
	{
		return $this->lastError;
	}

	public function isDebugMode(): bool
	{
		return false;
	}

}
