<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

use Mioweb\Database\BaseRepository;
use Mioweb\Database\BaseEntity;
use Mioweb\Shop\Exceptions\MissingInvoiceContactException;
use Mioweb\Shop\Gates\ShopGate;
use Mioweb\Shop\Gates\ShopGateRepository;
use Mioweb\Shop\Order\History\OrderHistory;
use Mioweb\Shop\Order\History\OrderHistoryRepository;
use Mioweb\Shop\PacketSize;
use MwsAutomationEvent;
use MwsContact;
use MwsCustomer;
use Mioweb\Shop\Document\Document;
use MwsEmailType;
use MwsException;
use MwsForm;
use MwsOrderEvent;
use MwsOrderGate_Fapi;
use MwsOrderItemType;
use MwsOrderSource;
use MwsOrderSourceType;
use MwsOrderStatus;
use MwsPayment;
use MwsPayType;
use MwsPrice;
use MwsProductType;
use MwsShipping;
use MwsShippingElectronic;
use MwsShippingType;
use MwsVatAccounting;
use mwUser;
use Nette\Database\Table\ActiveRow;
use Nette\Http\UrlScript;
use Nette\Utils\Json;
use Nette\Utils\Validators;

class Order extends BaseEntity implements IOrder
{

	public const MAXIMUM_FILE_SIZE_MB = 12;

	private ?ActiveRow $_row = null;

	private ?int $id = null;

	/** Link to order data stored at the gate side. */
	private ?OrderGate $_gateLive = null;

	private bool $_loaded = false;

	private string $orderNum;

	// TODO enum in database
	private int $status = MwsOrderStatus::Ordered;

	private string $hash;

	private ?MwsOrderSource $source = null;

	private bool $isOpened = false;

	/** If order is in created from selling form in test mode */
	private bool $isTest = false;

	private int $gateId;

	private ?ShopGate $_gate = null;

	private ?string $currency = null;

	private ?string $nativeCurrency = null;

	private ?float $exchangeRate = null;

	private ?string $note = null;

	private ?string $customerNote = null;

	private bool $isPaid = false;

	private bool $isPaymentFailedNotificationSent = false;

	private ?\DateTimeInterface $paidAt = null;

	private ?string $directPaymentUrl = null;

	private array $shipping;

	private ?array $discountCode = null;

	private ?float $totalWeight = null;

	private ?bool $reverseChargeApplied = false;

	private array $total;

	private array $payment;

	private ?string $shopVersion = null;

	private ?string $vatAccounting = null;

	private bool $showVat = false;

	private bool $useSimplifiedInvoice = false;

	private bool $heurekaDisagree = false;

	private ?int $customerId = null;

	private ?array $gateData = null;

	private ?MwsContact $invoiceContact = null;

	private ?MwsContact $shippingContact = null;

	private ?\DateTimeImmutable $createdAt = null;

	private ?\DateTimeInterface $archivedAt = null;

	private ?PacketSize $packetSize = null;

	// TODO refactor to separate fields or embeddable value object
	private ?array $packetData = null;

	private ?string $trackingNumber = null;

	private ?OrderItems $items = null;

	/** @var array<OrderHistory>|null */
	private ?array $history = null;

	private bool $isArchived = false;

	public function __construct(?ActiveRow $row = null, ?string $orderNum = null)
	{
		if ($row !== null) {
			$this->_row = $row;
			$this->load();
		} else {
			if ($orderNum === null) {
				throw new MwsException('Field [orderNum] need value.');
			}

			$this->orderNum = $orderNum;
			$this->status = MwsOrderStatus::Ordered;
			$this->hash = $this->regenerateHash();
		}
	}

	public function getItems(): OrderItems
	{
		if ($this->items === null) {
			$this->items = new OrderItems($this);

			$items = $this->_row !== null ? $this->_row->related(OrderItemRepository::getTableName() . '.order_id') : [];
			foreach ($items as $item) {
				$this->items->add(OrderItem::createByRow($item));
			}
		}

		return $this->items;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	/** @return MwsPayment[] */
	public function getPayments(): array
	{
		return MwsPayment::getAllByOrder($this);
	}

	/** ID of the order. This ID is sourced from the gateway at the moment when the order is successfully submitted to the gateway. */
	public function getNumber(): string
	{
		return $this->orderNum;
	}

	public function setNumber(string $number): void
	{
		$this->orderNum = $number;
	}

	public function getGateId(): int
	{
		return $this->gateId;
	}

	/** ID of the gateway that has been used to fulfill the order. */
	public function getGate(): ShopGate
	{
		if ($this->_gate === null) {
			$this->_gate = ShopGateRepository::getOneById($this->getGateId());

			if ($this->_gate === null) {
				throw new MwsException(sprintf('Shop gate with ID %d not exist.', $this->getGateId()));
			}
		}

		return $this->_gate;
	}

	public function getGateIdentifier(): string
	{
		return $this->getGate()->getIdentifier();
	}

	public function setGate(ShopGate $gate): void
	{
		$this->_gate = $gate;
		$this->gateId = $gate->getId();
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	public function setStatus(int $status): void
	{
		$this->status = $status;
	}

	public function isCancelled(): bool
	{
		return $this->getStatus() === MwsOrderStatus::Cancelled;
	}

	/** @return mixed[] */
	public function getAutomations(): array
	{
		if ($this->getSource() !== null && $this->getSource()->getType() === MwsOrderSourceType::Form) {
			/** @var MwsForm|null $form */
			$form = MwsForm::getOneById($this->getSource()->getFormId());

			return $form !== null ? $form->getAutomations() : [];
		}

		return MWS()->getGlobalAutomations();
	}

	public function processFileName(string $file): ?string
	{
		if (Validators::isUrl($file)) {
			$url = new UrlScript($file);
			$file = rtrim(ABSPATH, '/') . '/' . ltrim($url->getPath(), '/');
		}

		return file_exists($file) ? $file : null;
	}

	/** @return array<OrderHistory> */
	public function getHistory(): array
	{
		if ($this->history === null && $this->_row !== null) {
			$historyItems = $this->_row->related(OrderHistoryRepository::getTableName() . '.order_id');

			foreach ($historyItems as $history) {
				$this->history[] = OrderHistory::createByRow($history);
			}
		}

		return $this->history ?? [];
	}

	public function addHistory(string $text, ?string $event = null): void
	{
		$historyItem = new OrderHistory($text, mwUser::getCurrent()->getId(), null, $event);
		$orderId = $this->getId();
		if ($orderId !== null) {
			$historyItem->setOrderId($orderId);
			OrderHistoryRepository::save($historyItem);
		}
		$this->addHistoryItem($historyItem);
	}

	public function addHistoryItem(OrderHistory $historyItem): void
	{
		if ($this->history === null) {
			$this->history = [];
		}

		$this->history[] = $historyItem;
	}

	public function getLastHistoryTimestamp(string $event): ?int
	{
		$historyItems = array_reverse($this->getHistory(), true);

		foreach ($historyItems as $historyItem) {
			if ($historyItem->getEvent() !== null && $historyItem->getEvent() === $event) {
				return $historyItem->getCreatedAt()->getTimestamp();
			}
		}

		return null;
	}

	public function getCustomerNote(): ?string
	{
		return $this->customerNote;
	}

	public function setCustomerNote(?string $customerNote): void
	{
		$this->customerNote = mw_encode_emojis($customerNote);
	}

	public function isPaid(): bool
	{
		return $this->isPaid;
	}

	public function getPaidAt(): ?\DateTimeInterface
	{
		return $this->paidAt;
	}

	public function getPaidAtDateFormatted(): string
	{
		return $this->getPaidAt() !== null ? mwPrintDate($this->getPaidAt()->getTimestamp(), 'date', true) : '';
	}

	public function isPaymentFailedNotificationSent(): bool
	{
		return $this->isPaymentFailedNotificationSent;
	}

	public function setSendPaymentFailedNotification(bool $sent = true): void
	{
		$this->isPaymentFailedNotificationSent = $sent;
	}

	public function getDirectPaymentUrl(): ?string
	{
		return $this->directPaymentUrl;
	}

	public function setDirectPaymentUrl(?string $directPaymentUrl): void
	{
		$this->directPaymentUrl = $directPaymentUrl ?: null;
	}

	public function getDirectPaymentLink(string $text = ''): string
	{
		$paymentUrl = $this->getDirectPaymentUrl();

		return $paymentUrl !== null && $paymentUrl !== ''
			? ' <a href="' . $paymentUrl . '" target="_blank">' . ($text ?: __('Zaplatit online', 'mwshop')) . '</a>'
			: '';
	}

	/** @return mixed[] */
	public function getShipping(): array
	{
		return $this->shipping;
	}

	public function getShippingType(): ?int
	{
		return $this->getShipping()['shippingId'] ?? null;
	}

	public function getTrackingUrl(): ?string
	{
		$trackingUrl = MwsShippingType::getTrackingUrl($this->getShipping()['type']);

		if (!$trackingUrl) {
			$shipping = MwsShipping::getOneById($this->getShippingType());
			if ($shipping) {
				$trackingUrl = $shipping->getTrackingUrl();
			}
		}

		return $trackingUrl;
	}

	public function getShippingPrice(): ?MwsPrice
	{
		$shippingPrice = null;
		$orderLive = $this->getGateLive();
		if ($orderLive) {
			foreach ($orderLive->getItems() as $item) {
				if ($item->getType() === MwsOrderItemType::Shipping) {
					$shippingPrice = $item->getPrice($orderLive->getCurrency());
				}
			}
		}

		return $shippingPrice;
	}

	/** @param mixed[] $shipping */
	public function setShipping(array $shipping): void
	{
		$this->shipping = $shipping;
	}

	/**
	 * @return mixed[]|null
	 * @todo refactor - return object of @see \MwsDiscountCode::class
	 */
	public function getDiscountCode(): ?array
	{
		return $this->discountCode;
	}

	/** @todo use \MwsDiscountCode::class type */
	public function setDiscountCode(?array $discountCode): void
	{
//		$this->discountCode = $discountCode !== null ? $discountCode->toArray() : null;
		$this->discountCode = $discountCode;
	}

	/** @todo refactor - return null instead of an empty string */
	public function getCurrency(): string
	{
		return $this->currency ?? '';
	}

	public function setCurrency(string $currency): void
	{
		$this->currency = $currency;
	}

	public function getNativeCurrency(): ?string
	{
		return $this->nativeCurrency;
	}

	public function setNativeCurrency(?string $nativeCurrency): void
	{
		$this->nativeCurrency = $nativeCurrency === '' ? null : $nativeCurrency;
	}

	public function getExchangeRate(): ?float
	{
		return $this->exchangeRate;
	}

	public function setExchangeRate(?float $exchangeRate): void
	{
		$this->exchangeRate = $exchangeRate;
	}

	public function getNote(): ?string
	{
		return $this->note;
	}

	public function setNote(?string $note): void
	{
		$this->note = $note;
	}

	public function getTrackingNumber(): ?string
	{
		return $this->trackingNumber;
	}

	public function setTrackingNumber(?string $trackingNumber): void
	{
		$this->trackingNumber = $trackingNumber;
	}

	public function hasAllPhysicalItemsWeight(): bool
	{
		foreach ($this->getItems()->getProducts() as $item) {
			$weight = $this->getItemWeight($item);

			if ($weight === null || $weight <= 0.0) {
				$product = $item->getProduct();
				if ($product === null || MwsProductType::isPhysical($product->getType())) {
					return false;
				}
			}
		}

		return true;
	}

	/** @return OrderItem[] */
	public function getItemsWithoutWeight(): array
	{
		$items = $this->getItems()->getProducts();

		return array_filter($items, function (OrderItem $item): bool {
			$weight = $this->getItemWeight($item);

			return $weight === null || $weight <= 0.0;
		});
	}

	public function getTotalWeight(): float
	{
		// Get user-checked total weight
		if ($this->totalWeight !== null) {
			return $this->totalWeight;
		}

		// Add up all weights from order items
		$totalWeight = 0.0;

		foreach ($this->getItems()->getAll() as $item) {
			$totalWeight += (float) $this->getItemWeight($item);
		}

		return $totalWeight;
	}

	public function setTotalWeight(float $totalWeight): void
	{
		$this->totalWeight = $totalWeight;
	}

	public function setPacketSize(?PacketSize $size): void
	{
		$this->packetSize = $size;
	}

	/** @return mixed[]|null */
	public function getPacketData(): ?array
	{
		return $this->packetData;
	}

	/** @param mixed[]|null $data */
	public function setPacketData(?array $data): void
	{
		$this->packetData = $data;
	}

	public function getItemWeight(OrderItem $item): ?float
	{
		$weight = $item->getWeight();

		if ($weight === null || $weight <= 0.0) {
			// Try to get weight from product
			$product = $item->getProduct();
			$weight = $product !== null ? $product->getWeight() : null;
		}

		return $weight * $item->getCount();
	}

	public function isReverseChargeApplied(): bool
	{
		return $this->reverseChargeApplied;
	}

	public function setReverseCharge(bool $applied = true): void
	{
		$this->reverseChargeApplied = $applied;
	}

	/** @param mixed[] $total */
	public function setTotal(array $total): void
	{
		$this->total = $total;
	}

	/** @return mixed[] */
	public function getTotal(): array
	{
		return $this->total;
	}

	/** @return mixed[]|null */
	public function getPayment(): ?array
	{
		$payment = $this->payment ?? null;

		return $payment ?? null;
	}

	public function setPayment(array $payment): void
	{
		$this->payment = $payment;
	}

	public function getPaymentTitle(): string
	{
		$paymentInfo = $this->getPayment();

		return $paymentInfo['name'] ?? MwsPayType::getCaption($paymentInfo['type'] ?? '');
	}

	public function getShopVersion(): ?string
	{
		return $this->shopVersion;
	}

	public function setShopVersion(string $shopVersion): void
	{
		$this->shopVersion = $shopVersion;
	}

	public function getVatAccounting(): ?string
	{
		return $this->vatAccounting;
	}

	public function setVatAccounting(?string $vatAccounting): void
	{
		if ($vatAccounting !== null && !MwsVatAccounting::isValidValue($vatAccounting)) {
			throw new MwsException('Invalid MwsVatAccounting values.');
		}

		$this->vatAccounting = $vatAccounting;
	}

	public function setShowVat(bool $showVat): void
	{
		$this->showVat = $showVat;
	}

	public function showVatFromRow(): bool
	{
		return $this->showVat;
	}

	/** @todo #3642 */
	public function showVat(): bool
	{
		$orderLive = $this->getGateLive();

		return $orderLive ? $orderLive->showVat() : true;
	}

	public function isOpened(): bool
	{
		return $this->isOpened;
	}

	public function setAsOpened(bool $isOpened = true): void
	{
		$this->isOpened = $isOpened;
	}

	public function isTest(): bool
	{
		return $this->isTest;
	}

	public function setIsTest(bool $isTest = true): void
	{
		$this->isTest = $isTest;
	}

	public function useSimplifiedInvoice(): bool
	{
		return $this->useSimplifiedInvoice;
	}

	public function setSimplifiedInvoice(bool $isSimplified = true): void
	{
		$this->useSimplifiedInvoice = $isSimplified;
	}

	public function getCreatedAt(bool $utc = false): \DateTimeImmutable
	{
//		return (new \DateTimeImmutable($this->_post->post_date_gmt, new \DateTimeZone('GMT')))->setTimezone(wp_timezone());

		$createdAt = $this->createdAt;

		if ($createdAt === null) {
			throw new \Exception('created_at is not set.');
		}

		if ($utc) {
			$createdAt = $createdAt->setTimezone(new \DateTimeZone('UTC'));
		}

		return $createdAt;
	}

	public function setCreatedAt(\DateTimeImmutable $createdAt): void
	{
		$this->createdAt = $createdAt;
	}

	public function getEditUrl(): string
	{
		return mwSetting()->getObject(MWS_ORDER_SLUG)->getEditUrl($this->getId());
	}

	/**
	 * Live connector to order at the gateway side. Use this to get realtime information from the gateway. Data is automatically loaded.
	 */
	public function getGateLive(): ?OrderGate
	{
		if (!$this->_gateLive) {
			$this->_gateLive = MWS()->gateways()->loadOrderFor($this);

			if ($this->_gateLive instanceof MwsOrderGate_Fapi) {
				$this->_gateLive->updateInvoiceData();
			}
		}

		return $this->_gateLive;
	}

	public function getCustomer(): ?MwsCustomer
	{
		return $this->getGateLive()?->getCustomer();
	}

	public function getCustomerId(): ?int
	{
		return $this->customerId;
	}

	public function setCustomerId(?int $customerId): void
	{
		$this->customerId = $customerId;
	}

	public function getSupplierContact(): ?MwsContact
	{
		return $this->getGateLive()?->getSupplier();
	}

	public function getInvoiceContactFromRow(): ?MwsContact
	{
		$invoiceContact = $this->invoiceContact;

		if ($invoiceContact === null) {
			throw new MissingInvoiceContactException();
		}

		return $invoiceContact;
	}

	public function getInvoiceContact(): ?MwsContact
	{
		return $this->getGateLive()?->getInvoiceContact();
	}

	public function setInvoiceContact(?MwsContact $contact): void
	{
		$this->invoiceContact = $contact;
	}

	public function getShippingContactFromRow(): ?MwsContact
	{
		return $this->shippingContact;
	}

	public function getShippingContact(): ?MwsContact
	{
		return $this->getGateLive()?->getShippingContact();
	}

	public function setShippingContact(?MwsContact $contact): void
	{
		$this->shippingContact = $contact;
	}

	public function getPrice(): ?MwsPrice
	{
		return $this->getGateLive()?->getPrice();
	}

	public function getNativePrice(): ?MwsPrice
	{
		return $this->getGateLive()?->getNativePrice();
	}

	public function getGateOrderData(): array
	{
		return $this->gateData ?? [];
	}

	public function setGateOrderData(array $gateData): void
	{
		$this->gateData = $gateData;
	}

	public function getHeurekaDisagree(): bool
	{
		return $this->heurekaDisagree;
	}

	public function setHeurekaDisagree(bool $heurekaDisagree): void
	{
		$this->heurekaDisagree = $heurekaDisagree;
	}

	public function getSource(): ?MwsOrderSource
	{
		return $this->source;
	}

	public function setSource(?MwsOrderSource $source): void
	{
		$this->source = $source;
	}

	public function setPaid(bool $paid = true, bool $runSubsequentEvents = true): void
	{
		$this->isPaid = $paid;

		if ($runSubsequentEvents) {
			if ($paid) {
				$this->addHistory('Označeno za zaplaceno', MwsOrderEvent::OrderSetPaid);
				MWS()->getAutomationProcessor()->process($this, MwsAutomationEvent::OnPaid);
			} else {
				$this->addHistory('Označeno za nezaplaceno', MwsOrderEvent::OrderSetUnpaid);
			}
		}
	}

	public function setPaidAt(?\DateTimeInterface $paidAt): void
	{
		$this->paidAt = $paidAt;
	}

	public function getHash(): string
	{
		return $this->hash;
	}

	public function setHash(string $hash): void
	{
		$this->hash = $hash;
	}

	public function regenerateHash(): string
	{
		return uniqid('', false);
	}

	public function getBaseUrl(array $query = []): string
	{
		$url = get_home_url(null, MWS_ORDER_SLUG);
		$query = ['hash' => $this->getHash(), 'number' => $this->getNumber()];

		return add_query_arg($query, $url);
	}

	public function getCheckPaymentUrl(string $thxPageUrl = null): string
	{
		return add_query_arg([
			'checkPayment' => true,
			'thankYou' => $thxPageUrl,
		], $this->getBaseUrl());
	}

	public function getRetryPaymentUrl(string $thxPageUrl = null): string
	{
		return add_query_arg([
			'checkPayment' => true,
			'retry' => true,
			'thankYou' => $thxPageUrl,
		], $this->getBaseUrl());
	}

	public function isArchived(): bool
	{
		return $this->isArchived;
	}

	public function setArchived(bool $archived): void
	{
		$this->isArchived = $archived;
	}

	public function getArchivedAt(): ?\DateTimeInterface
	{
		return $this->archivedAt;
	}

	public function setArchivedAt(?\DateTimeInterface $archivedAt): void
	{
		$this->archivedAt = $archivedAt;
	}

	/** @return mixed[] */
	public function toRowArray(): array
	{
		$isFetched = $this->_row !== null;

		$result = [
			'hash' => $this->hash,
			'gate_id' => $this->gateId,
			'status' => $this->status,
			'is_paid' => $this->isPaid,
			'paid_at' => $this->paidAt,
			'is_archived' => $this->isArchived,
			'archived_at' => $this->archivedAt,
			'is_opened' => $this->isOpened,
			'is_test' => $this->isTest,
			'variable_symbol' => $this->orderNum,
			'customer_id' => $this->customerId,
			'currency' => $this->currency,
			'native_currency' => $this->nativeCurrency,
			'exchange_rate' => $this->exchangeRate,
			'note' => $this->note,
			'tracking_number' => $this->trackingNumber,
			'shipping' => Json::encode($this->shipping),
			'payment' => Json::encode($this->payment),
			'invoice_contact' => $this->invoiceContact !== null ? Json::encode($this->invoiceContact->toArray()) : null,
			'shipping_contact' => $this->shippingContact !== null ? Json::encode($this->shippingContact->toArray()) : null,
			'total' => Json::encode($this->total),
			'discount_code' => Json::encode($this->discountCode),
			'packet_size' => $this->packetSize !== null ? Json::encode($this->packetSize->toArray()) : null,
			'packet_data' => $this->packetData !== null ? Json::encode($this->packetData) : null,
			'reverse_charge_applied' => $this->reverseChargeApplied,
			'is_payment_failed_notification_sent' => $this->isPaymentFailedNotificationSent,
			'use_simplified_invoice' => $this->useSimplifiedInvoice,
			'vat_accounting' => $this->vatAccounting,
			'show_vat' => $this->showVat,
			'customer_note' => $this->customerNote,
			'heureka_disagree' => $this->heurekaDisagree,
			'source_type' => $this->source !== null ? $this->source->getType() : null,
			'source_form_id' => $this->source !== null ? $this->source->getFormId() : null,
			'source_page_id' => $this->source !== null ? $this->source->getPageId() : null,
			'source_url' => $this->source !== null ? $this->source->getUrl() : null,
			'direct_payment_url' => $this->directPaymentUrl,
			'shop_version' => $this->shopVersion,
			'gate_data' => $this->gateData !== null ? Json::encode($this->gateData) : null,
		];

		if ($isFetched) {
			$result['id'] = $this->id;
			$result['created_at'] = $this->getCreatedAt(true);
		} elseif ($this->createdAt !== null) {
			$result['created_at'] = $this->getCreatedAt(true);
		}

		return $result;
	}

	private function load(): void
	{
		if ($this->_loaded) {
			return;
		}

		if ($this->_row !== null) {
			$this->id = $this->_row->id;
			$this->hash = $this->_row->hash;
			$this->isArchived = (bool) $this->_row->is_archived;
			$this->orderNum = $this->_row->variable_symbol;
			$this->status = $this->_row->status;
			$this->isOpened = (bool) $this->_row->is_opened;
			$this->isTest = (bool) $this->_row->is_test;
			$this->gateId = $this->_row->gate_id;
			$this->currency = $this->_row->currency;
			$this->nativeCurrency = $this->_row->native_currency;
			$this->exchangeRate = $this->_row->exchange_rate;
			$this->note = $this->_row->note;
			$this->customerNote = $this->_row->customer_note;
			$this->isPaid = (bool) $this->_row->is_paid;
			$this->paidAt = $this->_row->paid_at;
			$this->isPaymentFailedNotificationSent = (bool) $this->_row->is_payment_failed_notification_sent;
			$this->directPaymentUrl = $this->_row->direct_payment_url;
			$archivedAt = $this->_row->archived_at;
			$this->archivedAt = $archivedAt !== null ? new \DateTimeImmutable($archivedAt->setTimezone(wp_timezone())->format('Y-m-d H:i:s')) : null;
			$this->shipping = Json::decode($this->_row->shipping, Json::FORCE_ARRAY);
			$this->discountCode = Json::decode($this->_row->discount_code, Json::FORCE_ARRAY);
			$this->reverseChargeApplied = (bool) $this->_row->reverse_charge_applied;
			$this->trackingNumber = $this->_row->tracking_number;
			$this->total = Json::decode($this->_row->total, Json::FORCE_ARRAY);
			$this->payment = Json::decode($this->_row->payment, Json::FORCE_ARRAY);
			$this->gateData = $this->_row->gate_data !== null ? Json::decode($this->_row->gate_data, Json::FORCE_ARRAY) : null;
			$invoiceContact = $this->_row->invoice_contact;
			$this->invoiceContact = $invoiceContact !== null
				? MwsContact::createFromArray(Json::decode($invoiceContact, Json::FORCE_ARRAY))
				: null;
			$shippingContact = $this->_row->shipping_contact;
			$this->shippingContact = $shippingContact !== null
				? MwsContact::createFromArray(Json::decode($shippingContact, Json::FORCE_ARRAY))
				: null;
			$this->shopVersion = $this->_row->shop_version;
			$this->vatAccounting = $this->_row->vat_accounting;
			$this->showVat = $this->_row->show_vat !== null ? (bool) $this->_row->show_vat : null;
			$this->useSimplifiedInvoice = (bool) $this->_row->use_simplified_invoice;
			$this->createdAt = (new \DateTimeImmutable($this->_row->created_at->format('Y-m-d H:i:s')))->setTimezone(wp_timezone());
			$packetSize = $this->_row->packet_size;
			$this->packetSize = $packetSize !== null ? PacketSize::fromArray(Json::decode($packetSize, Json::FORCE_ARRAY)) : null;
			$packetData = $this->_row->packet_data;
			$this->packetData = $packetData !== null ? Json::decode($packetData, Json::FORCE_ARRAY) : null;
			$this->totalWeight = $this->_row->total_weight;
			$this->heurekaDisagree = (bool) $this->_row->heureka_disagree;
			$this->customerId = $this->_row->customer_id;
			$sourceType = $this->_row->source_type;
			$this->source = $sourceType !== null ? new MwsOrderSource(
				$sourceType,
				$this->_row->source_page_id,
				$this->_row->source_url,
				$this->_row->source_form_id,
			) : null;
		}
	}

	/** @todo Move outside of entity */
	public function save(): bool
	{
		$update = $this->_row !== null;
		OrderRepository::save($this);

		if ($update) {
			foreach (Document::getAllByOrderId($this->getId()) as $document) {
				$document->setCustomerContact($this->getInvoiceContact());
				$document->setShippingContact($this->getShippingContact());
				$document->save();
			}
		}

		return true;
	}

	/** @todo Move outside of entity */
	public function changeStatus(int $status, bool $notify = false): void
	{
		$newStatus = MwsOrderStatus::checkedValue($status);
		if (!$newStatus) {
			mwshoplog('Cannot change order status for ' . $this->getNumber() . ' to [' . $status . ']. Unsupported order status.', MWLL_ERROR, 'order');

			throw new MwsException('Invalid order status [' . $status . ']');
		}

		$oldStatus = $this->getStatus();
		if ($oldStatus == $newStatus) {
			mwshoplog('Order status for ' . $this->getNumber() . ' is already ' . MwsOrderStatus::getCaption($newStatus) . '. Nothing was changed.', MWLL_INFO, 'order');
		} else {
			$this->status = $status;
			$this->addHistory(sprintf(__('Změna stavu: %s &#8594; %s', 'mwshop'), MwsOrderStatus::getCaption($oldStatus), MwsOrderStatus::getCaption($newStatus)), 'order_status_change_to_' . $newStatus);
			$this->save();
			mwshoplog('Order status for ' . $this->getNumber() . ' changed from "' . MwsOrderStatus::getCaption($oldStatus) . '" to "' . MwsOrderStatus::getCaption($newStatus) . '".', MWLL_INFO, 'order');
		}

		if ($notify) {
			$shippingId = $this->getShipping()['shippingId'] ?? null;

			if ($newStatus === MwsOrderStatus::Closed && $shippingId !== MwsShippingElectronic::id) {
				$shippingType = $this->getShipping()['type'] ?? null;

				$emailType = $shippingType === MwsShippingType::Personal
					? MwsEmailType::OrderReadyToPickup
					: MwsEmailType::FinishedOrder;

				$contact = $this->getGateLive()->getInvoiceContact();
				$contact->sendMail(
					MWS()->getEmailSubject($emailType, $this),
					MWS()->getEmailContent($emailType, $this)
				);
				$this->addHistory(__('Informační email o vyřízení objednávky zaslán klientovi', 'mwshop'), MwsOrderEvent::OrderCloseMailSend);
			}
		}

		if ($newStatus === MwsOrderStatus::Closed) {
			MWS()->getAutomationProcessor()->process($this, MwsAutomationEvent::OnFinish);
		} elseif ($newStatus === MwsOrderStatus::Cancelled) {
			MWS()->getAutomationProcessor()->process($this, MwsAutomationEvent::OnStorno);
		}
	}

	/** @todo Move outside of entity */
	public function createInvoice(): OrderGateDocument
	{
		$invoice = $this->getGateLive()->createInvoice();

		$this->addHistory('Faktura vystavena', MwsOrderEvent::InvoiceCreated);
		do_action('mw_invoice_created', $invoice);

		return $invoice;
	}

	/**
	 * @todo Move outside of entity
	 * Sends payment failure notification e-mail if not already sent before.
	 */
	public function sendPaymentFailedNotification(): void
	{
		if (!$this->isPaymentFailedNotificationSent()) {
			$this->setSendPaymentFailedNotification();
			$this->addHistory(__('Odeslán e-mail o neprovedené platbě.', 'mwshop'), MwsOrderEvent::PaymentFailed);
			$this->save();

			$emailType = MwsEmailType::OrderPaymentFailed;
			$body = MWS()->getEmailContent($emailType, $this);
			$subject = MWS()->getEmailSubject($emailType, $this);

			$this->getInvoiceContact()->sendMail($subject, $body);
		}
	}

	/** @todo Move outside of entity */
	public function sendInvoiceToCustomer(OrderGateDocument $document, string $emailType = MwsEmailType::PayedOrder)
	{
		$document->sendToCustomer($emailType);
		$this->addHistory('Faktura odeslána', MwsOrderEvent::InvoiceMailSend);
	}

	/** @deprecated */
	public static function getOneById(int $orderId): ?self
	{
		return OrderRepository::getOneById($orderId);
	}

	/** @return class-string<BaseRepository> */
	public static function getRepositoryClassName(): string
	{
		return OrderRepository::class;
	}

}
