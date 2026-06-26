<?php declare(strict_types=1);

namespace Mioweb\Shop\Listeners;

use Mioweb\Shop\Order\Order;
use MwsCart;
use MwsOrderStep;
use MwsProduct;
use MwVariables;

class MwsFacebookConversionsListener
{

	private static ?self $instance = null;

	private function __construct()
	{
		if (MWFBC()->isActive()) {
			$this->registerHooks();
		}
	}

	public function viewContentEvent(MwsProduct $product): void
	{
		$price = $product->getPrice();

		MWFBC()->sendEvent('ViewContent', [
			'currency' => $price->getCurrency(),
			'items' => [$product->toAnalyticsArray()],
			'value' => $price->getPriceVatExcluded(),
		]);
	}

	public function initiateCheckoutEvent(MwsCart $cart): void
	{
		$price = $cart->getStoredTotalPrice();
		$priceVal = $price !== null ? $price->getPriceVatExcluded() : 0.0;
		if ($cart->isShippingPriceIncluded() && $cart->getShippingPrice() !== null) {
			$priceVal -= $cart->getShippingPrice()->getPriceVatExcluded();
		}

		MWFBC()->sendEvent('InitiateCheckout', [
			'currency' => $cart->getCurrency(),
			'items' => $cart->toAnalyticsArray(),
			'value' => $priceVal,
		]);
	}

	public static function purchaseEvent(Order $order): void
	{
		$gl = $order->getGateLive();
		$currency = $gl ? $gl->getCurrency() : $order->getCurrency();

		MWFBC()->sendEvent('Purchase', [
			'transaction_id' => $order->getNumber(),
			'value' => $order->getPrice()->getPriceVatIncluded(),
			'currency' => $currency,
			'items' => MwVariables::generateItemsForGA($gl->getItems(), $currency, false),
		]);
	}

	public static function getInstance(): MwsFacebookConversionsListener
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function registerHooks()
	{
		add_action('wp_footer', function () {
			global $post;
			if (is_singular(MWS_PRODUCT_SLUG)) {
				$this->viewContentEvent(MwsProduct::createNew($post));
			} elseif (isset($post->ID) && $post->ID == MWS()->getOrderPageId()) {
				$step = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : '';
				$step = MwsOrderStep::checkedValue($step, MwsOrderStep::Cart);
				if ($step == MwsOrderStep::Shipping) {
					$cart = MWS()->getCart();
					$this->initiateCheckoutEvent($cart);
				}
			}
		}, 1);
	}
}
