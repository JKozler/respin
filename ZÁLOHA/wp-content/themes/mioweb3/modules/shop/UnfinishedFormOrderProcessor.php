<?php declare(strict_types=1);

namespace Mioweb\Shop;

class UnfinishedFormOrderProcessor
{

	private const CART_EXPIRATION_IN_DAYS = 1;

	public function processAll(): void
	{
		$now = (new \DateTimeImmutable('now', new \DateTimeZone('GMT')))->setTimezone(wp_timezone());

		$args = [
			'meta_query' => [
//				'relation' => 'AND',
//				[
//					'key' =>  MWS_FORM_CART_META_KEY_IS_FORM_PROCESSED,
//					'value' => true,
//				],
				[
					'key' => MWS_FORM_CART_META_KEY_DELAYED_AUTO_PROCESS_RESPONSE,
					'compare' => 'NOT EXISTS',
				],
			],
			'date_query' => [
				'before' => $now->modify('- 1 HOUR')->format('Y-m-d H:i:s'),
			],
			'post_type' => MWS_FORM_CART_SLUG,
			'post_status' => 'any',
			'posts_per_page' => -1,
		];

		$carts = FormDatabaseCart::getAll($args);
		foreach ($carts as $cart) {
			if ($cart->isFormProcessed()) {
				try {
					$this->finishOrder($cart);
				} catch (\Throwable $e) {
					$cart->setFormProcessed(false);
				}
			} else {
				// Delete old expired carts
				$createdAt = $cart->getCreatedAt();
				if ($createdAt !== null && abs($createdAt->diff($now, true)->days) >= self::CART_EXPIRATION_IN_DAYS) {
					$cart->delete();
				}
			}
		}
	}

	private function finishOrder(FormDatabaseCart $cart): void
	{
		$gw = MWS()->gateways()->getDefault();

		$res = $gw->sharedInstance()->makeOrder($cart);
		$ok = $res['success'];

		if ($ok) {
			$cart->incOrderedCount(); // Update statistics
			$cart->delete();
		} else {
			$cart->setDelayAutoProcessResponse($res);
			$cart->save();
		}
	}

}
