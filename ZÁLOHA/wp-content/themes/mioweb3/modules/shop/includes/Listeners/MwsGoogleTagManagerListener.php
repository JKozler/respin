<?php

use Mioweb\Shop\Order\Order;

class MwsGoogleTagManagerListener
{

	private static $instance = null;

	private function __construct()
	{
		if (MWGTM()->isActive()) {
			$this->registerHooks();
		}
	}

	/** @param array $data */
	private function set($data)
	{
		MWGTM()->setCurrentPageDataLayer($data);
	}

	private function getBaseData()
	{
		$cart = [];
		foreach (MWS()->getCart()->getItems()->getAll() as $item) {
			$product = $item->getProduct();
			$cart[] = [
				'id' => $product->getId(),
				'code' => $product->getCodes()->getCode(MwsProductCode::Filing),
				'quantity' => $item->getCount(),
			];
		}

		return [
			'currency' => strtoupper(MWS()->getDefaultCurrency('key')),
			'cartCurrency' => strtoupper(MWS()->getCart()->getCurrency()),
			'cart' => $cart,
		];
	}

	private function pushHomepage()
	{
		$this->set(array_replace([
				'pageType' => 'eshopHome',
		], $this->getBaseData()));
	}

	private function pushCategory()
	{
		$category = get_queried_object();
		$this->set(array_replace([
				'pageType' => 'eshopCategory',
				'pageInfo' => [
					'id' => $category->term_id,
					'name' => $category->name,
				],
		], $this->getBaseData()));
	}

	/** @param MwsProduct $product */
	private function pushProductDetail($product)
	{
		$categories = array_map(function ($category) {
			return $category->name;
		}, get_the_terms($product->getId(), MWS_PRODUCT_CAT_SLUG) ?: []);
		$productData = [
			'id' => $product->getId(),
			'name' => $product->getName(),
			'categories' => $categories,
			'priceVat' => $product->getPrice()->getPriceVatIncluded(),
			'price' => $product->getPrice()->getPriceVatExcluded(),
			'tax' => $product->getPrice()->getVatAmount(),
		];
		if ($product->getStructure() === MwsProductStructureType::Variants) {
			$productData['hasVariants'] = true;
			$codes = [];
			foreach ($product->getVariants() as $variant) {
				$code = $variant->getCodes()->getCode(MwsProductCode::Filing);
				if ($code) {
					$codes[] = [
						'code' => $code,
					];
				}
			}
			if ($codes) {
				$productData['codes'] = $codes;
			}
		} else {
			$productData['hasVariants'] = false;
			$code = $product->getCodes()->getCode(MwsProductCode::Filing);
			if ($code) {
				$productData['code'] = $code;
			}
		}
		$this->set(array_replace([
				'pageType' => 'productDetail',
				'pageInfo' => $productData,
		], $this->getBaseData()));
	}

	private function pushCart()
	{
		$this->set(array_replace([
				'pageType' => 'cart',
		], $this->getBaseData()));
	}

	private function pushContact()
	{
		$this->set(array_replace([
				'pageType' => 'purchaseContact',
		], $this->getBaseData()));
	}

	private function pushShipping()
	{
		$this->set(array_replace([
				'pageType' => 'purchaseShipping',
		], $this->getBaseData()));
	}

	private function pushSummarize()
	{
		$this->set(array_replace([
				'pageType' => 'purchaseSummarize',
		], $this->getBaseData()));
	}

	private function pushThankYou()
	{
		$this->set(array_replace([
				'pageType' => 'purchaseThankYou',
		], $this->getBaseData()));
	}

	public static function pushPurchase(Order $order)
	{
		$transactionProducts = [];

		$gl = $order->getGateLive();
		$currency = $gl ? $gl->getCurrency() : $order->getCurrency();

		foreach ($order->getItems()->getProducts() as $item) {
			$codes = $item->getCodes();

			$transactionProduct = [
				'id' => $item->getProductId(),
				'sku' => $codes !== null ? $codes->getCode(MwsProductCode::Filing) : '',
				'name' => $item->getName(),
				'price' => $item->getPrice($currency)->getPriceVatExcluded(),
				'tax' => $item->getPrice($currency)->getVatAmount(),
				'quantity' => $item->getCount(),
				//'variant' => '',
				//'category' => '',
				//'brand' => '',
			];

			$product = $item->getProduct();
			if ($product && $product->isVariant()) {
				$transactionProduct['name'] = $product->getProduct()->getName();
				$transactionProduct['variant'] = $product->composeVariantDesc();
			}

			$transactionProducts[] = $transactionProduct;
		}

		MWGTM()->pushDataLayer([
			'event' => 'purchase',
			'ecommerce' => [
				'currencyCode' => $currency,
				'purchase' => [
					'actionField' => [
						'id' => $order->getNumber(),
						'affiliation' => get_bloginfo('name'),
						'revenue' => $order->getPrice()->getPriceVatExcluded(),
						'shipping' => $order->getShippingPrice() ? $order->getShippingPrice()->getPriceVatExcluded() : 0,
						'tax' => $order->getPrice()->getVatAmount(),
						'coupon' => $order->getDiscountCode()['code'] ?? '',
					],
					'products' => $transactionProducts,
				],
			],
		]);
	}

	private function registerHooks()
	{
		add_action('wp', function () {
			global $post;
			if (is_tax(MWS_PRODUCT_CAT_SLUG)) {
				$this->pushCategory();
			} elseif (is_singular(MWS_PRODUCT_SLUG)) {
				$this->pushProductDetail(MwsProduct::createNew($post));
			} elseif (isset($post->ID) && $post->ID == MWS()->getOrderPageId()) {
				$step = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : '';
				$step = MwsOrderStep::checkedValue($step, MwsOrderStep::Cart);
				if ($step == MwsOrderStep::Cart) {
					$this->pushCart();
				} elseif ($step == MwsOrderStep::Contact) {
					$this->pushContact();
				} elseif ($step == MwsOrderStep::Shipping) {
					$this->pushShipping();
				} elseif ($step == MwsOrderStep::Summarize) {
					$this->pushSummarize();
				} elseif ($step == MwsOrderStep::ThankYou) {
					$this->pushThankYou();
				}
			} elseif (isset($post->ID) && $post->ID == MWS()->getHomePageId()) {
				$this->pushHomepage();
			}
		}, 1000);
	}

	/** @return MwsGoogleTagManagerListener */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
