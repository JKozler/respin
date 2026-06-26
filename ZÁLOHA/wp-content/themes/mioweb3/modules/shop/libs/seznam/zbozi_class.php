<?php

use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Seznam\ZboziFeedDeliveryEnum;

/**
 * Provides access to ZboziKonverze service.
 *
 * @author Zbozi.cz <zbozi@firma.seznam.cz>
 */
class MwZboziCz
{

	/**
	 * Endpoint URL
	 *
	 * @var string BASE_URL
	 */
	const BASE_URL = 'https://%%DOMAIN%%/action/%%SHOP_ID%%/conversion/backend';

	/**
	 * Private identifier of request creator
	 *
	 * @var string $PRIVATE_KEY
	 */
	public $PRIVATE_KEY;

	/**
	 * Public identifier of request creator
	 *
	 * @var string $SHOP_ID
	 */
	public $SHOP_ID;

	/**
	 * Identifier of this order
	 *
	 * @var string $orderId
	 */
	public $orderId;

	/**
	 * Customer email
	 * Should not be set unless customer allows to do so.
	 *
	 * @var string $email
	 */
	public $email;

	/**
	 * How the order will be transfered to the customer
	 *
	 * @var string $deliveryType
	 */
	public $deliveryType;

	/**
	 * Cost of delivery (in CZK)
	 *
	 * @var float $deliveryPrice
	 */
	public $deliveryPrice;

	/**
	 * How the order was paid
	 *
	 * @var string $paymentType
	 */
	public $paymentType;

	/**
	 * Other fees (in CZK)
	 *
	 * @var string $otherCosts
	 */
	public $otherCosts;

	/**
	 * Array of CartItem
	 *
	 * @var array $cart
	 */
	public $cart = [];

	/**
	 * Determine URL where the request will be send to
	 *
	 * @var bool $sandbox
	 */
	private $sandbox;

	public function useSandbox(bool $val)
	{
		$this->sandbox = $val;
	}

	/**
	 * Check if string is not empty
	 *
	 * @param string|null $question String to test
	 * @return boolean
	 */
	private static function isNullOrEmptyString($question)
	{
		return (!isset($question) || trim($question) === '');
	}

	/**
	 * Initialize ZboziKonverze service
	 *
	 * @param string $shopId Shop identifier
	 * @param string $privateKey Shop private key
	 * @throws ZboziKonverzeException can be thrown if \p $privateKey and/or \p $shopId
	 * is missing or invalid.
	 */
	public function __construct()
	{
		$setting = mwApiConnect()->getApi('zbozi')->getOption();

		$this->SHOP_ID = $setting['shop_id'] ?? '';
		$this->PRIVATE_KEY = $setting['private_key'] ?? '';

		$this->sandbox = (bool) ($setting['sandbox'] ?? false);
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
	 * @param int $orderId Order identifier
	 */
	public function addOrderId($orderId)
	{
		$this->orderId = $orderId;
	}

	/**
	 * Adds ordered product using name
	 *
	 * @param string $productName Ordered product name
	 */
	public function addProduct($productName)
	{
		$item = new CartItem();
		$item->productName = $productName;
		$this->cart[] = $item;
	}

	/**
	 * Adds ordered product using item ID
	 *
	 * @param string $itemId Ordered product item ID
	 */
	public function addProductItemId($itemId)
	{
		$item = new CartItem();
		$item->itemId = $itemId;
		$this->cart[] = $item;
	}

	/**
	 * Adds ordered product using array which can contains
	 * \p productName ,
	 * \p itemId ,
	 * \p unitPrice ,
	 * \p quantity
	 *
	 * @param array $cartItem Array of various CartItem attributes
	 */
	public function addCartItem($cartItem)
	{
		$item = new CartItem();
		if (array_key_exists('productName', $cartItem)) {
			$item->productName = $cartItem['productName'];
		}
		if (array_key_exists('itemId', $cartItem)) {
			$item->itemId = $cartItem['itemId'];
		}
		if (array_key_exists('unitPrice', $cartItem)) {
			$item->unitPrice = $cartItem['unitPrice'];
		}
		if (array_key_exists('quantity', $cartItem)) {
			$item->quantity = $cartItem['quantity'];
		}

		$this->cart[] = $item;
	}

	/**
	 * Sets order attributes within
	 * \p email ,
	 * \p deliveryType ,
	 * \p deliveryPrice ,
	 * \p orderId ,
	 * \p otherCosts ,
	 * \p paymentType ,
	 *
	 * @param array $orderAttributes Array of various order attributes
	 */
	public function setOrder($orderAttributes)
	{
		if (array_key_exists('email', $orderAttributes) && $orderAttributes['email']) {
			$this->email = $orderAttributes['email'];
		}
		$this->deliveryType = $orderAttributes['deliveryType'];
		$this->deliveryPrice = $orderAttributes['deliveryPrice'];
		$this->orderId = $orderAttributes['orderId'];
		$this->otherCosts = $orderAttributes['otherCosts'];
		$this->paymentType = $orderAttributes['paymentType'];
	}


	/**
	 * Creates HTTP request and returns response body
	 *
	 * @param string $url URL
	 * @return boolean true if everything is perfect else throws exception
	 * @throws ZboziKonverzeException can be thrown if connection to Zbozi.cz
	 * server cannot be established.
	 */
	protected function sendRequest($url)
	{
		$encoded_json = json_encode(get_object_vars($this));

		if (extension_loaded('curl')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3 /* seconds */);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			$response = curl_exec($ch);

			if ($response === false) {
				throw new ZboziKonverzeException('Unable to establish connection to ZboziKonverze service: ' . curl_error($ch));
			}
		} else {
			// use key 'http' even if you send the request to https://...
			$options = [
				'http' => [
					'header' => 'Content-type: application/json',
					'method' => 'POST',
					'content' => $encoded_json,
				],
			];
			$context = stream_context_create($options);
			$response = file_get_contents($url, false, $context);

			if ($response === false) {
				throw new ZboziKonverzeException('Unable to establish connection to ZboziKonverze service');
			}
		}

		$decoded_response = json_decode($response, true);
		if (isset($decoded_response['status'])) {
			if ((int) ($decoded_response['status'] / 100) === 2) {
				return true;
			}

			throw new ZboziKonverzeException('Request was not accepted: ' . $decoded_response['statusMessage']);
		}

		throw new ZboziKonverzeException('Bad Request');
	}

	/**
	 * Returns endpoint URL
	 *
	 * @return string URL where the request will be called
	 */
	private function getUrl()
	{
		$url = $this::BASE_URL;
		$url = str_replace('%%SHOP_ID%%', $this->SHOP_ID, $url);

		$url = $this->sandbox ? str_replace('%%DOMAIN%%', 'sandbox.zbozi.cz', $url) : str_replace('%%DOMAIN%%', 'www.zbozi.cz', $url);

		return $url;
	}

	/**
	 * Sends request to ZboziKonverze service and checks for valid response
	 *
	 * @return boolean true if everything is perfect else throws exception
	 * @throws ZboziKonverzeException can be thrown if connection to Zbozi.cz
	 * server cannot be established or mandatory values are missing.
	 */
	public function send()
	{
		$url = $this->getUrl();

		// send request and check for valid response
		try {
			$status = $this->sendRequest($url);

			return $status;
		} catch (Exception $e) {
			throw new ZboziKonverzeException($e->getMessage());
		}
	}

	public function makeConversion(Order $order): string
	{
		if ($this->SHOP_ID && $this->PRIVATE_KEY && ($customer = $order->getCustomer())) {
			$shipping = $order->getShipping();

			$shippingPrice = $shipping['price'] ?? null;
			if (is_array($shippingPrice)) {
				$shippingPrice = MwsPrice::createByArray($shippingPrice);
			}

			$shippingType = isset($shipping['type']) ? ZboziFeedDeliveryEnum::getByMwsType($shipping['type']) ?? $shipping['name'] ?? '' : $shipping['name'] ?? '';

			try {
				foreach ($order->getItems()->getProducts() as $item) {
					$this->addCartItem([
						'productName' => $item->getName(),
						'itemId' => $item->getProductId(),
						'unitPrice' => $item->getPrice(MwsCurrencyEnum::czk)->getPriceVatIncluded(),
						'quantity' => $item->getCount(),
					]);
				}
				$this->setOrder([
					'email' => $customer->getEmail(),
					'deliveryType' => $shippingType,
					'deliveryPrice' => $shippingPrice instanceof MwsPrice ? $shippingPrice->getPriceVatIncluded() : 0,
					'otherCosts' => 0,
					'orderId' => $order->getNumber(),
					'paymentType' => $order->getPaymentTitle(),
				]);
				$this->send();

				$script = '<script>
					(function (w, d, s, u, n, k, c, t) {
						w.ZboziConversionObject = n;
						w[n] = w[n] || function () {
							(w[n].q = w[n].q || []).push(arguments)
						};
						w[n].key = k;
						c = d.createElement(s);
						t = d.getElementsByTagName(s)[0];
						c.async = 1;
						c.src = u;
						t.parentNode.insertBefore(c, t)
					})(window, document, "script", "https://www.zbozi.cz/conversion/js/conv-v3.js", "zbozi", "' . $this->SHOP_ID . '");

					' . ($this->sandbox ? 'zbozi("useSandbox");' : '') . '

					zbozi("setOrder", {
						"orderId": "' . $order->getNumber() . '",
					});

					zbozi("send");
				</script>';

				return $script;
			} catch (ZboziKonverzeException $e) {
				// handle errors
				//echo $e->getMessage();
				$errorMsg = 'Chyba konverze: ' . $e->getMessage();
				mwlog(MWLS_SHOP, $errorMsg, MWLL_ERROR);
				error_log($errorMsg);
			}
		}

		return '';
	}

	public static function getCategoryList(): array
	{
		$tran = get_transient('mw_zbozicz_categories_t');
		$cats = get_option('mw_zbozicz_categories');
		if (!$tran) {
			$request = new \Mioweb\HttpClient\HttpRequest('https://www.zbozi.cz/static/categories.json');
			$response = core()->getHttpClient()->sendHttpRequest($request);

			if ($response->getStatusCode() === 200 && $response->getBody()) {
				$new_cats = [];
				$array = json_decode($response->getBody(), true);
				$new_cats = self::getCategories($array, $new_cats);
				if (!empty($new_cats)) {
					$cats = $new_cats;
					set_transient('mw_zbozicz_categories_t', $cats, 72 * HOUR_IN_SECONDS);
					update_option('mw_zbozicz_categories', $cats, 72 * HOUR_IN_SECONDS);
				}
			}
		}

		return (array) $cats;
	}

	public static function getCategories($array, $cats)
	{
		foreach ($array as $line) {
			if (isset($line['categoryText'])) {
				$cats[$line['id']] = $line['categoryText'];
			} elseif (isset($line['children'])) {
				$cats = self::getCategories($line['children'], $cats);
			}
		}

		return $cats;
	}

}

class CartItem
{

	/**
	 * Item name
	 *
	 * @var string $productName
	 */
	public $productName;

	/**
	 * Item identifier
	 *
	 * @var string $itemId
	 */
	public $itemId;

	/**
	 * Price per one item (in CZK)
	 *
	 * @var float $unitPrice
	 */
	public $unitPrice;

	/**
	 * Number of items ordered
	 *
	 * @var int $quantity
	 */
	public $quantity;
}

/**
 * Thrown when an service returns an exception
 */
class ZboziKonverzeException extends Exception
{
}
