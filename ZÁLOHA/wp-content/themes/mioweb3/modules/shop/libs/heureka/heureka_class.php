<?php

use Mioweb\Shop\Order\Order;

class MwHeureka
{
	/**
	 * Heureka endpoint URL
	 *
	 * @var string
	 */
	const BASE_URL = 'http://www.heureka.cz/direct/dotaznik/objednavka.php';
	const BASE_URL_SK = 'http://www.heureka.sk/direct/dotaznik/objednavka.php';

	/**
	 * Valid response value
	 *
	 * @var string
	 */
	const RESPONSE_OK = 'ok';

	/**
	 * Shop API key
	 *
	 * @var array
	 */
	private $setting;

	/**
	 * Shop API key
	 *
	 * @var string
	 */
	private $apiKey;

	private $secretKey;

	/**
	 * Customer email
	 *
	 * @var string
	 */
	private $email;

	/**
	 * Order ID
	 *
	 * @var int
	 */
	private $orderId;

	/**
	 * Current language identifier
	 *
	 * @var string
	 */
	private $language = 'cs_CZ';

	/**
	 * Ordered products provided using item ID
	 *
	 * @var array
	 */
	private $productsItemId = [];

	/**
	 * Initialize Heureka Overeno service
	 *
	 * @param string $apiKey Shop API key
	 * @param int $languageId Language version settings
	 */
	public function __construct()
	{
		$this->setting = mwApiConnect()->getApi('heureka')->getOption();

		//$this->setLogin();
		$this->language = get_locale();
		$this->secretKey = $this->setting['secret_key'] ?? '';
		$this->apiKey = $this->setting['api_key'] ?? '';
	}

	/**
	 * Sets API key and check well-formedness
	 *
	 * @param string $apiKey Shop api key
	 */
	public function checkSecretKey()
	{
		if (!preg_match('(^[0-9abcdef]{32}$)', $this->secretKey)) {
			throw new OverflowException('Secret key ' . $this->setting['secret_key'] . ' is invalid.');
		}
	}

	/**
	 * Sets customer email
	 *
	 * @param string $email Customer email address
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * Adds order ID
	 *
	 * @param int Order ID
	 */
	public function setOrderId($orderId): void
	{
		$this->orderId = $orderId;
	}

	/**
	 * Adds ordered products using item ID
	 *
	 * @param string $itemId Ordered product item ID
	 */
	public function addProductItemId($itemId)
	{
		$this->productsItemId[] = $itemId;
	}

	/**
	 * Creates HTTP request and returns response body
	 *
	 * @param string $url URL
	 * @return string Response body
	 */
	private function sendRequest($url)
	{
		$parsed = parse_url($url);
		$fp = fsockopen($parsed['host'], 80, $errno, $errstr, 5);
		if (!$fp) {
			throw new HeurekaOverenoException($errstr . ' (' . $errno . ')');
		}

		$return = '';
		$out = 'GET ' . $parsed['path'] . '?' . $parsed['query'] . " HTTP/1.1\r\n" .
		'Host: ' . $parsed['host'] . "\r\n" .
		"Connection: Close\r\n\r\n";
		fputs($fp, $out);
		while (!feof($fp)) {
			$return .= fgets($fp, 128);
		}
		fclose($fp);
		$returnParsed = explode("\r\n\r\n", $return);

		return empty($returnParsed[1]) ? '' : trim($returnParsed[1]);
	}

	/**
	 * Returns domain for given language version
	 *
	 * @return String
	 */
	private function getUrl()
	{
		return $this->language == 'cs_CZ' ? self::BASE_URL : self::BASE_URL_SK;
	}

	/**
	 * Sends request to Heureka Overeno service and checks for valid response
	 *
	 * @return boolean true
	 */
	public function send()
	{
		if (empty($this->email)) {
			throw new HeurekaOverenoException('Customer email address not set');
		}

		// create URL
		$url = $this->getUrl() . '?id=' . $this->secretKey . '&email=' . urlencode($this->email);
		/*
		foreach ($this->products as $product) {
		$url .= '&produkt[]=' . urlencode($product);
		}*/
		foreach ($this->productsItemId as $itemId) {
			$url .= '&itemId[]=' . urlencode($itemId);
		}

		// add order ID
		if (isset($this->orderId)) {
			$url .= '&orderid=' . urlencode($this->orderId);
		}

		// send request and check for valid response
		$contents = $this->sendRequest($url);
		if ($contents == false) {
			throw new HeurekaOverenoException('Unable to create HTTP request to Heureka Overeno service');
		}

		if ($contents == self::RESPONSE_OK) {
			return true;
		}

		throw new HeurekaOverenoException($contents);
	}

	public function sendHeurekaOvereno(Order $order)
	{
		if (!empty($this->secretKey)) {
			if (!$order->getHeurekaDisagree() && ($customer = $order->getCustomer())) {
				// https://github.com/heureka/heureka-overeno-php-api
				try {
					$this->checkSecretKey();
					$this->setEmail($customer->getEmail());
					foreach ($order->getItems()->getProducts() as $item) {
						$this->addProductItemId($item->getProduct()->getId());
					}
					$this->setOrderId($order->getNumber());
					$this->send();
					$order->addHistory(__('Objednávka byla úspěšně odeslána do služby Ověřeno zákazníky.', 'mwshop'));
				} catch (OverflowException $o) {
					$order->addHistory(__('Odeslání dat pro službu Ověřeno zákazníky se nezdařilo. Tajný klíč nebyl správně nastaven: ', 'mwshop') . $o->getMessage());
				} catch (HeurekaOverenoException $e) {
					$order->addHistory(__('Odeslání dat pro službu Ověřeno zákazníky se nezdařilo: ', 'mwshop') . $e->getMessage());
				}
			} else {
				$order->addHistory(__('Zákazník nesouhlasil s odesláním dat pro službu Ověřeno zákazníky. ', 'mwshop'));
			}
		}
	}

	public function heurekaConversionCode(Order $order): string
	{
		if (!empty($this->apiKey)) {
			if ($this->language == 'sk_SK') {
				define('HEUREKA_MENA', MwsCurrencyEnum::eur);
				define('HEUREKA_KONVERZE', 'https://im9.cz/sk/js/ext/2-roi-async.js');
			} else {
				define('HEUREKA_MENA', MwsCurrencyEnum::czk);
				define('HEUREKA_KONVERZE', 'https://im9.cz/js/ext/1-roi-async.js');
			}

			$script = "<script type=\"text/javascript\">
				var _hrq = _hrq || [];
				_hrq.push(['setKey', '" . $this->apiKey . "']);
				_hrq.push(['setOrderId', '" . $order->getNumber() . "']);";
				foreach ($order->getItems()->getProducts() as $item) {
				$script .= "
					_hrq.push(['addProduct', '" . esc_html($item->getName()) . "', '" . $item->getPrice(HEUREKA_MENA)->getPriceVatIncluded() . "', '" . $item->getCount() . "']);";
				}
				$script .= "
				_hrq.push(['trackOrder']);
				(function () {
					var ho = document.createElement('script');
					ho.type = 'text/javascript';
					ho.async = true;
					ho.src = '" . HEUREKA_KONVERZE . "';
					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(ho, s);
				})();
			</script>";

			return $script;
		}

		return '';
	}

	public function writeDisagree(bool $checked = false)
	{
		$content = '';
		if (!empty($this->secretKey)) {
			$content = '<label class="heureka_overeno_zakazniky">'
			. '<input class="mw_checkbox" type="checkbox" name="heureka_disagree" value="rejected"' . ($checked ? ' checked' : '') . '/>'
			. __('Nesouhlasím se zasláním dotazníku spokojenosti v rámci programu Ověřeno zákazníky (Heureka), který pomáhá zlepšovat naše služby.', 'mwshop')
			. '</label>';
		}

		return $content;
	}

	public static function getCategoryList()
	{
		$tran = get_transient('mw_heureka_categories_t');
		$cats = get_option('mw_heureka_categories');
		if (!$tran) {
			if ($file = simplexml_load_file('https://www.heureka.cz/direct/xml-export/shops/heureka-sekce.xml')) {
				$new_cats = [];
				$array = json_decode(json_encode($file), true);
				$new_cats = self::getCategories($array['CATEGORY'], $new_cats);
				if (!empty($new_cats)) {
					$cats = $new_cats;
					set_transient('mw_heureka_categories_t', $cats, 24 * HOUR_IN_SECONDS);
					update_option('mw_heureka_categories', $cats, 24 * HOUR_IN_SECONDS);
				}
			}
		}

		return $cats;
	}

	public static function getCategories($array, $cats)
	{
		foreach ($array as $line) {
			if (isset($line['CATEGORY_FULLNAME'])) {
				$cats[$line['CATEGORY_ID']] = $line['CATEGORY_FULLNAME'];
			} elseif (isset($line['CATEGORY'])) {
				$cats = self::getCategories($line['CATEGORY'], $cats);
			}
		}

		return $cats;
	}

}

/**
 * Thrown when an service returns an exception
 */
class HeurekaOverenoException extends Exception
{
}
