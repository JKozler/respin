<?php declare(strict_types=1);

use Mioweb\Lib\LockFactory;
use Mioweb\Shop\Order\OrderRepository;
use Nette\Utils\Validators;

$hash = $_GET['hash'] ?? null;

if ($hash === null || $hash === '') {
	exit;
}

if (!($_GET['checkPayment'] ?? false)) {
	exit;
}

$lockFactory = new LockFactory();
$lock = $lockFactory->createLock('mw-check-payment-' . $hash);
if (!$lock->acquire(true)) {
	exit;
}


$order = OrderRepository::getOrderByHash($hash);
if ($order === null) {
	$lock->release();
	exit;
}

// document payment auto processing / only for MW orders
try {
	$paid = false;
	$paidNow = false;
	$payments = $order->getPayments();
	// it is ready for multiple payments but now is not possible create more then one
	foreach ($payments as $payment) {
		$paymentGatewayId = $payment->getPaymentGatewayId();
		if ($paymentGatewayId) {
			$paymentStatus = MWS()->getPaymentGatewayById($paymentGatewayId)->loadPaymentStatus($payment);
			if ($paymentStatus !== $payment->getStatus()) {
				$payment->setStatus($paymentStatus);
				$payment->save();
				if ($payment->isPaid()) {
					$paidNow = true;
				}
			}
		}
		// one payment is enough
		$paid = $paid || $payment->isPaid();
	}

	// if paid status determined now -> return from gateway
	if ($paid && $paidNow) {
		// if order and not set as paid
		if ($order && !$order->isPaid()) {
			$order->setPaid();
			$order->setPaidAt((new \DateTimeImmutable()));
			$order->save();

			if (MWS()->isAutoInvoiceEnabled()) {
				$invoice = $order->createInvoice();
				$invoice->sendToCustomer();
				$order->addHistory('Faktura odeslána', MwsOrderEvent::InvoiceMailSend);

				if ($order->getShippingType() === MwsShippingElectronic::id) {
					$order->changeStatus(MwsOrderStatus::Closed, true);
				}
			}
		}
	}

	// Resolve "thank you" page URL
	if (isset($_REQUEST['thankYou']) && Validators::isUrl($_REQUEST['thankYou'])) {
		$thxPageUrl = $_REQUEST['thankYou'];
	} else {
		$eshopThxUrl = MWS()->getUrl_Cart(MwsOrderStep::ThankYou) ?: null;

		if ($eshopThxUrl !== null) {
			$thxPageUrl = $eshopThxUrl;
		} else {
			// E-shop is probably not installed, redirect to selling form
			$orderSource = $order->getSource();
			$formUrl = $orderSource !== null ? ($orderSource->getUrl() ?: null) : null;

			$thxPageUrl = $formUrl ?? get_home_url();
		}
	}

	// if contain retry parameter then recreate payment
	if ($payments && !$paid && ($_REQUEST['retry'] ?? false)) {
		$lastPayment = end($payments);
		$paymentGatewayId = $lastPayment->getPaymentGatewayId();
		$paymentGateway = MWS()->getPaymentGatewayById($paymentGatewayId);
		if ($paymentGateway) {
			$newPayment = MWS()
				->getPaymentGatewayById($paymentGatewayId)
				->createPayment($order, $lastPayment->getPaymentMethodType(), $thxPageUrl);
			$newPayment->save();
			header('Location: ' . $newPayment->getPaymentUrl() ?? $newPayment->getData()['nextUrl']);
		}
	} elseif (($_REQUEST['thankYou'] ?? false) || $paid) {
		$success = !$payments || $paid;
		$thxPageUrl = add_query_arg([
			'success' => $success ? 1 : 0,
			'vs' => $order->getNumber(),
			'gw' => $order->getGateIdentifier(),
		], $thxPageUrl);

		if (!$success && (bool) $order->getDirectPaymentUrl()) {
			$order->sendPaymentFailedNotification();
		}

		header('Location: ' . $thxPageUrl);
	}
} catch (\Throwable $e) {
	$lock->release();

	throw $e;
}

$lock->release();
exit;
