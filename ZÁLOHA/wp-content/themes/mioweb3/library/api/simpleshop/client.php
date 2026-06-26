<?php

class mwAPIConnectItemClient_simpleshop extends mwAPIConnectItemClient
{

	private const API_URL = 'https://api.simpleshop.cz/2.0/';

	/** @var bool If CURL call should verify SSL certificate. */
	private bool $verifySslPeer = false;

	// send info to simpleshop
	public function sendInfo($sendConversionTable = false)
	{
		$login = $this->_mwAPIConnectItem->getOption();

		return $this->initSS($login['login'], $login['password']);
	}

	public function sendConversionTable(array $convertTable, array $newSections)
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$SSClient = $this->getClient($login['login'], $login['password']);

		return $SSClient->sendConversionTable($convertTable, get_home_url(), $newSections);
	}

	function initSS(string $login, string $password)
	{
		$SSClient = $this->getClient($login, $password);

		$theme_data = wp_get_theme();

		$domain = get_home_url();
		$version = 'Mioweb ' . $theme_data->version . ' (' . get_bloginfo('version') . ')';
		$apiKey = '';

		if (MW()->is_module_active('member')) {
			$apiKey = mwMemberModule()->getApiKey();
		}

		return $SSClient->initWPPlugin($domain, $version, $apiKey);
	}

	function checkSavedSetting(&$tosave): bool
	{
		$login = $tosave['login'] ?? '';
		$apiKey = $tosave['password'] ?? '';

		if ($login && $apiKey) {
			$tosave['login'] = $login = trim($login);
			$tosave['password'] = $apiKey = trim($apiKey);

			$isConnected = $this->isConnected($login, $apiKey);
			if ($isConnected) {
				$this->initSS($login, $apiKey);
			} else {
				mwMessages()->error(__('Přihlašovací údaje nejsou správné. Spojení se nezdařilo.', 'cms'));
			}

			return $isConnected;
		} else {
			mwMessages()->error(__('Musíte vyplnit přihlašovací jméno i API klíč.', 'cms'));

			return false;
		}
	}

	function isConnected($login, $password): bool
	{
		$SSClient = $this->getClient($login, $password);

		try {
			$return = $SSClient->test();
		} catch (VyfakturujAPIException $e) {
			\Tracy\Debugger::log($e, \Tracy\ILogger::EXCEPTION);
			mwshoplog('SimpleShop connnection error: ' . $e->getMessage());

			return false;
		}

		return isset($return['date']);
	}

	public function getFormsList(): array
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$SSClient = $this->getClient($login['login'], $login['password']);

		return $SSClient->getProducts(); // vrací pole formulářů
	}

	public function getProductsList(): array
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$SSClient = $this->getClient($login['login'], $login['password']);

		return $SSClient->getProducts();
	}

	public function getOrder($id): ?array
	{
		return null;
	}

	public function getInvoice($id): ?array
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$SSClient = $this->getClient($login['login'], $login['password']);

		$invoice = $SSClient->getInvoice($id);

		if (!$invoice) {
			return null;
		}

		return [
			'invoice' => $invoice,
			'id' => $invoice['id'],
			'price' => $invoice['total'],
			'currency' => $invoice['currency_domestic'],
			'email' => $invoice['mail_to'][0] ?? '',
			'vs' => $invoice['number'],
		];
	}

	function getInvoiceVariables($id)
	{
		return [];
	}

	public function getPurchaseEventData($id, $funnel = null): ?array
	{
		$login = $this->_mwAPIConnectItem->getOption();
		$SSClient = $this->getClient($login['login'], $login['password']);

		$invoice = $SSClient->getInvoice($id);

		if (!$invoice) {
			return null;
		}

		return [
			'id' => $invoice['id'],
			'email' => $invoice['mail_to'][0] ?? '',
			'price' => $invoice['total'],
			'currency' => $invoice['currency_domestic'],
			'upsell' => 0,
			'bump' => 0,
			'vs' => $invoice['number'],
		];
	}

	public function printForm($element, $css_id, $edit_mode, $added): string
	{
		$content = '<div class="in_element_content in_element_ss_form">';

		$form_code = base64_decode($element['style']['content']['id']);

		if ($added) {
			$form_c = str_replace('/', '\/', $form_code);
			$form_c = str_replace("'", '"', $form_c);
			$form_c = str_replace(["\r", "\n"], '', $form_c);
			$content .= '<script type="text/javascript">
                jQuery(document).ready(function($) {
					mwGetIframeContent().mw_load_added_ss_form("' . $css_id . ' .in_element_content",\'' . $form_c . '\');
                });

            </script>';
		} else {
			$content .= $form_code;
		}
		$content .= '</div>';

		return $content;
	}

	/** @return mixed[] */
	function getSettings(): array
	{
		return [];
	}

	private function getClient(string $login, string $password): VyfakturujAPI
	{
		require_once __DIR__ . '/VyfakturujAPI.php';

		return new \Mioweb\Api\SimpleShop\VyfakturujAPI($login, $password, self::API_URL);
	}

}
