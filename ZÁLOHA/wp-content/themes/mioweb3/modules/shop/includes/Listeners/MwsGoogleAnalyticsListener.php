<?php

use Mioweb\Shop\Order\Order;

class MwsGoogleAnalyticsListener
{

	private static $instance = null;

	private function __construct()
	{
		if (MWGA()->isActive()) {
			$this->registerHooks();
		}
	}

	public static function purchaseEvent(Order $order): void
	{
		$gl = $order->getGateLive();
		$currency = $gl ? $gl->getCurrency() : $order->getCurrency();

		MwCodes()->addCode(MWGA()->printEvent('purchase', [
			'transaction_id' => $order->getNumber(),
			'affiliation' => get_bloginfo('name'),
			'value' => $order->getPrice()->getPriceVatIncluded(),
			'shipping' => $order->getShippingPrice() ? $order->getShippingPrice()->getPriceVatExcluded() : 0,
			'tax' => $order->getPrice()->getVatAmount(),
			'currency' => $currency,
			'coupon' => $order->getDiscountCode()['code'] ?? '',
			'items' => MwVariables::generateItemsForGA($gl->getItems(), $currency, false),
		]), 'header');
	}

	public function viewItemEvent(MwsProduct $product): string
	{
		$price = $product->getPrice();

		return MWGA()->printEvent('view_item', [
			'currency' => $price->getCurrency(),
			'items' => [$product->toAnalyticsArray()],
			'value' => $price->getPriceVatExcluded(),
		]);
	}

	public function addToCartEvent(MwsProduct $product, int $count, MwsCart $cart): void
	{
		echo MWGA()->printEvent('add_to_cart', [
			'currency' => $cart->getCurrency(),
			'items' => [$product->toAnalyticsArray($count)],
			'value' => $product->getPrice()->getPriceVatExcluded(),
		]);
	}

	public function removeFromCartEvent(MwsCartItem $item, MwsCart $cart): void
	{
		$product = $item->getProduct();

		echo MWGA()->printEvent('remove_from_cart', [
			'currency' => $cart->getCurrency(),
			'items' => [$product->toAnalyticsArray($item->getCount())],
			'value' => $product->getPrice()->getPriceVatExcluded(),
		]);
	}

	public function viewCartEvent(MwsCart $cart): string
	{
		$price = $cart->getStoredTotalPrice();

		return MWGA()->printEvent('view_cart', [
			'currency' => $cart->getCurrency(),
			'value' => $price !== null ? $price->getPriceVatExcluded() : 0.0,
			'items' => $cart->toAnalyticsArray(),
		]);
	}

	public function beginCheckoutEvent(MwsCart $cart): string
	{
		$price = $cart->getStoredTotalPrice();
		$priceVal = $price !== null ? $price->getPriceVatExcluded() : 0.0;
		if ($cart->isShippingPriceIncluded() && $cart->getShippingPrice() !== null) {
			$priceVal -= $cart->getShippingPrice()->getPriceVatExcluded();
		}

		return MWGA()->printEvent('begin_checkout', [
			'coupon' => $cart->getDiscountCode() !== null ? $cart->getDiscountCode()->getCode() : '',
			'currency' => $cart->getCurrency(),
			'items' => $cart->toAnalyticsArray(),
			'value' => $priceVal,
		]);
	}

	public function addPaymentInfoEvent(MwsCart $cart): string
	{
		$price = $cart->getStoredTotalPrice();
		$paymentMethod = $cart->getPaymentMethod();

		return MWGA()->printEvent('add_payment_info', [
			'coupon' => $cart->getDiscountCode() !== null ? $cart->getDiscountCode()->getCode() : '',
			'currency' => $cart->getCurrency(),
			'items' => $cart->toAnalyticsArray(),
			'payment_type' => $paymentMethod !== null ? $paymentMethod->getName() : '',
			'value' => $price !== null ? $price->getPriceVatExcluded() : 0.0,
		]);
	}

	public function addShippingInfoEvent(MwsCart $cart): string
	{
		$price = $cart->getStoredTotalPrice();
		$shippingMethod = $cart->getShipping();

		return MWGA()->printEvent('add_shipping_info', [
			'coupon' => $cart->getDiscountCode() !== null ? $cart->getDiscountCode()->getCode() : '',
			'currency' => $cart->getCurrency(),
			'items' => $cart->toAnalyticsArray(),
			'shipping_tier' => $shippingMethod !== null ? $shippingMethod->getName() : '',
			'value' => $price !== null ? $price->getPriceVatExcluded() : 0.0,
		]);
	}

	private function registerHooks()
	{
		add_action('mw_product_detail', [$this, 'viewItemEvent']);
		add_action('mw_product_added_to_cart', [$this, 'addToCartEvent'], 10, 3);
		add_action('mw_product_removed_from_cart', [$this, 'removeFromCartEvent'], 10, 2);

		add_action('wp_footer', function () {
			global $post;
			if (is_singular(MWS_PRODUCT_SLUG)) {
				MwCodes()->addCode($this->viewItemEvent(MwsProduct::createNew($post)), 'footer');
			} elseif (isset($post->ID) && $post->ID == MWS()->getOrderPageId()) {
				$step = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : '';
				$step = MwsOrderStep::checkedValue($step, MwsOrderStep::Cart);
				$cart = MWS()->getCart();
				if ($step == MwsOrderStep::Cart) {
					MwCodes()->addCode($this->viewCartEvent($cart), 'footer');
				} elseif ($step == MwsOrderStep::Contact) {
					MwCodes()->addCode($this->beginCheckoutEvent($cart), 'footer');
				} elseif ($step == MwsOrderStep::Summarize) {
					MwCodes()->addCode($this->addShippingInfoEvent($cart), 'footer');
					MwCodes()->addCode($this->addPaymentInfoEvent($cart), 'footer');
				}
			}
		}, 1);
	}

	/** @return MwsGoogleAnalyticsListener */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
