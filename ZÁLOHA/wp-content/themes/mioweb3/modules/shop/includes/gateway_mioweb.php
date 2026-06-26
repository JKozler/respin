<?php

use Mioweb\Shop\Exceptions\MissingInvoiceContactException;
use Mioweb\Shop\FormDatabaseCart;
use Mioweb\Shop\Order\IOrder;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Document\Document;
use Mioweb\Shop\Order\OrderGate;
use Mioweb\Shop\Order\OrderGateDocument;
use Mioweb\Shop\Order\OrderRepository;

class MwsGatewayImpl_Mioweb extends MwsGatewayImpl
{

	public function recountCart(MwsCart $cart, bool $includeShippingPrice, bool $ignoreSimplifiedInvoice, bool $includeRounding = false, bool $applyReverseCharge = false)
	{
		// total prices init
		$cartTotalPriceVatIncluded = 0.0;
		$cartTotalPriceVatExcluded = 0.0;

		$currency = $cart->getCurrency();

		$cartItems = $cart->getItems()->getAll();
		$orderItems = $this->prepareOrderItems($cart, $includeShippingPrice, $includeRounding, $applyReverseCharge, true);

		foreach ($orderItems as $orderItem) {
			$itemPrice = $orderItem->getPrice($currency);
			$itemTotalPrice = $itemPrice->multiply($orderItem->getCount());

			if ($orderItem->isProduct()) {
				$cartItem = array_shift($cartItems);
				$product = $cartItem->getProduct();
				$cartItem->setStoredPrice($itemPrice);
				$cartItem->setStoredShopPrice($product->getPrice()->asCurrency($currency));
				$cartItem->setStoredProductPrice($product->getPrice());
				$cartItem->setStoredTotalPrice($itemTotalPrice);
			} elseif ($orderItem->getType() === MwsOrderItemType::Shipping) {
				$cart->setShippingPrice($itemPrice);
			} elseif ($orderItem->getType() === MwsOrderItemType::Rounding) {
				$cart->setRounding($itemPrice);
			}

			$cartTotalPriceVatIncluded += $itemTotalPrice->getPriceVatIncluded();
			$cartTotalPriceVatExcluded += $itemTotalPrice->getPriceVatExcluded();
		}

		$cart->setStoredTotalPrice(MwsPrice::createByFields(
			round($cartTotalPriceVatIncluded, 2),
			round($cartTotalPriceVatExcluded, 2),
			0.0,
			$currency
		));
	}

	public function loadRemoteUseSimplifiedInvoiceForEshop(): bool
	{
		// simplified invoice is controlled in cart
		return false;
	}

	public function loadRemoteUseSimplifiedInvoiceForQuickBuy(): bool
	{
		return true;
	}

	public function loadRemoteUseSimplifiedInvoiceForForm(MwsForm $form): bool
	{
		return (bool) ($form->getVisibilitySettings()['allow_simply_form'] ?? false);
	}

	public function loadRemoteSettings(): array
	{
		// there are no specific settings
		return [];
	}

	protected function doSyncSettings(): bool
	{
		// there is no sync needed
		return true;
	}

	public function isConnected(): bool
	{
		// there is no connection needed
		return true;
	}

	protected function doMakeOrder(MwsCart $cart): array
	{
		$orderNumber = MWS()->getOrderNumberGenerator($cart->isTest())->next(true);
		$order = $this->createOrderBase($cart, $orderNumber);

		$order->setInvoiceContact($cart->getInvoiceContact());
		$order->setShippingContact($cart->getShippingContact());

		$total = $this->getTotalPriceFromOrderData($order);
		$order->setTotal($total);

		$thxPageUrl = $cart->getThxPage() ?? MWS()->getUrl_Cart(MwsOrderStep::ThankYou);
		$thxPageUrl = add_query_arg(['gw' => $this->getId()], $thxPageUrl);

		$order->save();

		$result = [
			'success' => true,
			'orderId' => $order->getId(),
			'orderNum' => $order->getNumber(),
			'nextUrl' => add_query_arg(['success' => 1, 'vs' => $orderNumber], $thxPageUrl), // @TODO use some order hash?
		];

		$paymentMethod = $cart->getPaymentMethod();
		if ($paymentMethod->isGateway() && ($paymentGatewayId = $paymentMethod->getPaymentGatewayId())) {
			$payment = MWS()->getPaymentGatewayById($paymentGatewayId)->createPayment($order, $paymentMethod->getType(), $thxPageUrl);
			$payment->save();
			// @TODO what if payment create error?
			$result = array_replace($result, $payment->getData());
			// set url for retry payment
			$order->setDirectPaymentUrl($order->getRetryPaymentUrl($thxPageUrl));
		}
		$order->save();

		if ($cart instanceof FormDatabaseCart) {
			$cart->clear();
		}

		// @TODO send summary after payment?
		$order->getGateLive()->sendSummary();

		return $result;
	}

	public function getTotalPriceFromOrderData(Order $order)
	{
		$total = [];
		foreach ($order->getItems()->getAll() as $item) {
			foreach ($item->getPrices() as $currency => $price) {
				$totalPrice = $item->getTotalPrice($currency);
				if (isset($total[$currency])) {
					$total[$currency]['vatIncluded'] += $totalPrice->getPriceVatIncluded();
					$total[$currency]['vatExcluded'] += $totalPrice->getPriceVatExcluded();
				} else {
					$total[$currency]['vatIncluded'] = $totalPrice->getPriceVatIncluded();
					$total[$currency]['vatExcluded'] = $totalPrice->getPriceVatExcluded();
				}
			}
		}

		return $total;
	}

	public function processPayments(): bool
	{
		return false;
	}

	public function loadRemotePayTypes(): array
	{
		return [];
	}

	public function loadRemotePayments(): array
	{
		return [];
	}

	public function getOrderFromThankYou(): ?Order
	{
		$orderNumber = $_REQUEST['vs'] ?? null; // @TODO use hash
		if ($orderNumber) {
			return OrderRepository::getOrderByOrderNum($orderNumber);
		}

		return null;
	}

	public function orderPaid(): ?Order
	{
		return null;
	}

	public function orderCancelled(): ?Order
	{
		return null;
	}

	public function loadOrderGate(IOrder $order, ?array $preloadedData = null): ?OrderGate
	{
		return new MwsOrderGate_Mioweb($order);
	}

	public function doGetEnabledCodes(bool $reload = false): array
	{
		return [
			MwsProductCode::EAN,
			MwsProductCode::Filing,
		];
	}
}

class MwsOrderGate_Mioweb extends OrderGate
{

	public function __construct(IOrder $order)
	{
		parent::__construct($order);
	}

	public function getItems(): array
	{
		return $this->_order->getItems()->getAll();
	}

	protected function getPriceByCurrency($currency)
	{
		$toal = $this->_order->getTotal();

		$priceVatIncluded = $toal[$currency]['vatIncluded'] ?? 0;
		$priceVatExcluded = $toal[$currency]['vatExcluded'] ?? 0;

		return MwsPrice::createByFields(
			round($priceVatIncluded, 2),
			round($priceVatExcluded, 2),
			0.0,
			$currency
		);
	}

	protected function doGetPrice(): MwsPrice
	{
		$currency = $this->_order->getCurrency();

		return $this->getPriceByCurrency($currency);
	}

	protected function doGetNativePrice(): MwsPrice
	{
		$currency = $this->_order->getNativeCurrency();

		if ($currency === null) {
			throw new MwsException('Native currency is not set for this order.');
		}

		return $this->getPriceByCurrency($currency);
	}

	protected function doGetCurrency(): string
	{
		return $this->_order->getCurrency();
	}

	protected function doGetBankAccount(string $currency): ?MwsBankAccount
	{
		return MWS()->getBankAccount($currency);
	}

	protected function doIsPaid(): bool
	{
		$payments = $this->_order->getPayments();
		if ($payments) {
			foreach ($payments as $payment) {
				// document is paid if all payments are paid
				if (!$payment->isPaid()) {
					return false;
				}
			}

			return true;
		}

		return $this->_order->isPaid();
	}

	protected function doGetPaidOn(): ?int
	{
		$paidAt = null;
		$payments = $this->_order->getPayments();
		if ($payments) {
			foreach ($payments as $payment) {
				// document is paid if all payments are paid
				if (!$payment->isPaid()) {
					return $this->_order->getPaidAt()?->getTimestamp(); // why?
				}

				$createdAt = $payment->getCreatedAt();
				if ($createdAt < $paidAt || $paidAt === null) {
					$paidAt = $payment->getCreatedAt();
				}
			}
		} else {
			return $this->_order->getPaidAt()?->getTimestamp();
		}

		return $paidAt;
	}

	public function getCustomer(): MwsCustomer
	{
		return new MwsCustomer_Mioweb($this->_order->getInvoiceContact()->getEmail());
	}

	public function getSupplier(): ?MwsContact
	{
		return MWS()->getSupplierContact();
	}


	/** @throws MissingInvoiceContactException */
	public function getInvoiceContact(): MwsContact
	{
		if ($this->_order instanceof MwsOrder) {
			if (!isset($this->_order->getMeta()['invoiceContact'])) {
				throw new MissingInvoiceContactException();
			}

			return MwsContact::createFromArray($this->_order->getMeta()['invoiceContact']);
		}

		return $this->_order->getInvoiceContactFromRow();
	}

	public function getShippingContact(): ?MwsContact
	{
		if ($this->_order instanceof MwsOrder) {
			$meta = $this->_order->getMeta();

			return isset($meta['shippingContact']) && $meta['shippingContact'] ? MwsContact::createFromArray($meta['shippingContact']) : null;
		}

		return $this->_order->getShippingContactFromRow();
	}

	public function getDocuments(): array
	{
		return Document::getAllByOrderId($this->_order->getId());
	}

	public function showVat(): bool
	{
		if ($this->_order instanceof MwsOrder) {
			$meta = $this->_order->getMeta();

			return $meta['showVat'] ?? false;
		}

		return $this->_order->showVatFromRow();
	}

	public function printOrderInvoiceInfo(): string
	{
		$content = '';
		$sended = $this->_order->getLastHistoryTimestamp(MwsOrderEvent::InvoiceMailSend);
		/** @var Document[] $docs */
		$docs = $this->getDocuments();
		if (empty($docs)) {
			$content .= mwAdminComponents::title([
				'text' => __('Faktura', 'mwshop'),
				'onright' => $this->_order->isArchived() ? null : '<a class="mws_order_create_invoice mws_order_hide_on_cancel mw_setting_action_link" href="#" data-id="' . $this->_order->getId() . '">' . __('Vystavit fakturu', 'mwshop') . '</a>',
			]);
			$content .= mwAdminComponents::messageBox(__('Není vytvořen žádný doklad.', 'cms_member'), ['type' => 'info_gray']);
		} else {
			$doc = end($docs);

			if ($sended) {
				$status = 'ok';
				$statusText = __('Odeslána', 'mwshop');
			} else {
				$status = 'non';
				$statusText = __('Neodeslána', 'mwshop');
			}
			$link = '<a class="mws_order_send_invoice mw_setting_action_link mws_order_hide_on_cancel" href="#" data-id="' . $this->_order->getId() . '">' . __('Odeslat zákazníkovi', 'mwshop') . '</a>';

			if ($this->_order->isArchived()) {
				$link = null;
			}

			$content .= mwAdminComponents::statusField([
				'title' => __('Faktura', 'mwshop'),
				'link' => $link,
				'text' => $statusText,
				'status' => $status,
			], 'mws_order_invoice_status');

			$content .= '<div class="mw_setting_sidebar_info">';

			$content .= '<div class="mw_setting_sidebar_info_row">';
			$content .= '<span>' . __('Číslo faktury', 'mwshop') . ':</span>';
			$content .= '<span>' . $doc->getNumber() . '</span>';
			$content .= '</div>';

			if ($sended) {
				$content .= '<div class="mw_setting_sidebar_info_row">';
				$content .= '<span>' . __('Odeslána', 'mwshop') . ':</span>';
				$content .= '<span>' . mwPrintDate($sended, 'datetime', true) . '</span>';
				$content .= '</div>';
			}
			if (!$this->_order->isArchived()) {
				$content .= '<div class="mw_setting_sidebar_info_row">';
				$content .= '<span>' . __('Stáhnout', 'mwshop') . ':</span>';
				$content .= '<span><a class="mw_setting_action_link" target="_blank" href="' . $doc->getDownloadUrl() . '">PDF</a></span>';
				$content .= '</div>';
			}

			if (MWMPohoda()->isActive()) {
				$content .= '<div class="mw_setting_sidebar_info_row">';
				$content .= '<span>' . __('mPOHODA', 'mwshop') . ':</span>';
				$content .= MwsOrderAdmin::getMPohodaRowHtml($doc);
				$content .= '</div>';
			}

			$content .= '</div>';
		}

		return $content;
	}

	public function createInvoice(): OrderGateDocument
	{
		$documentType = $this->_order->useSimplifiedInvoice()
			? MwsDocumentType::SimplifiedInvoice
			: MwsDocumentType::Invoice;

		$currency = $this->_order->getCurrency();

		$paidOn = $this->_order->getGateLive()->getPaidOn() !== null
			? (new \DateTimeImmutable())->setTimestamp($this->_order->getGateLive()->getPaidOn())
			: null;

		$number = MWS()->getDocumentNumberGenerator($documentType, $this->_order->isTest())->next(true, $paidOn);
		$document = new Document(null, $number, $documentType);
		$document->setOrderNumber($this->_order->getNumber());
		$document->setCurrency($this->_order->getCurrency());
		$nativeCurrency = $this->_order->getNativeCurrency();
		if ($nativeCurrency === null) {
			throw new MwsException('Native currency is not set for this order.');
		}
		$document->setNativeCurrency($nativeCurrency);
		$document->setOldOrderId($this->_order->getId());
		if ($this->_order instanceof Order) {
			$document->setOrder($this->_order);
		}
		$document->setCurrencyExchangeRate($this->_order->getExchangeRate());
		$document->setSupplierContact(MWS()->getSupplierContact());
		$document->setCustomerContact($this->_order->getInvoiceContact());
		$document->setShippingContact($this->_order->getShippingContact());
		$document->setPaymentMethod($this->_order->getPayment());
		$document->setBankAccount(MWS()->getBankAccount($currency));
		$document->setInvoiceNote($this->_order ? MWS()->getInvoiceNote($this->_order) : null);
		$document->setVatAccounting($this->_order->getVatAccounting());
		$document->setShowVat($this->_order->showVat());
		$document->setReverseCharge($this->_order->isReverseChargeApplied());
		$document->setSimplifiedInvoice($this->_order->useSimplifiedInvoice());
		$document->setIsTest($this->_order->isTest());
		$document->setPaidWhenCreated($this->_order->isPaid());
		foreach ($this->_order->getItems()->getAll() as $item) {
			$document->addItem(MwsDocumentItem::createByArray([
				'name' => $item->getName(),
				'type' => $item->getType(),
				'price' => $item->getPrice($currency)->toArray(),
				'prices' => $item->getPrices(),
				'count' => $item->getCount(),
				'codes' => ($codes = $item->getCodes()) ? $codes->toArray() : null,
				'productId' => $item->getProductId(),
				'ossApplied' => $item->isOssApplied(),
			]));
		}
		$document->setPrice($this->_order->getPrice());
		$document->save();

		return $document;
	}

	public function sendSummary(): void
	{
		$file = MWS()->getEmailAttachment(MwsEmailType::NewOrder, $this->_order);
		$attachments = $file ? [$file] : [];
		$this->getInvoiceContact()->sendMail(
			MWS()->getEmailSubject(MwsEmailType::NewOrder, $this->_order),
			MWS()->getEmailContent(MwsEmailType::NewOrder, $this->_order),
			$attachments
		);
	}

	public function formatContactEditing(): string
	{
		return '<a class="mws_edit_order_customer mws_order_hide_on_cancel mw_setting_action_link" href="#" data-id="' . $this->_order->getId() . '" data-title="' . __('Upravit údaje objednávky', 'mwshop') . '">' . __('Upravit údaje objednávky', 'mwshop') . '</a>';
	}

}

class MwsCustomer_Mioweb implements MwsCustomer
{

	private $_email;

	public function __construct(string $email)
	{
		$this->_email = $email;
	}

	public function getEmail(): string
	{
		return $this->_email;
	}

	public function getDetailUrl(): ?string
	{
		// @TODO implement detail
		return null;
	}

	public function getEditUrl(): ?string
	{
		// @TODO implement edit
		return null;
	}

}
