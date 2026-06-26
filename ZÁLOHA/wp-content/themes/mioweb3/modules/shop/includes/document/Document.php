<?php

namespace Mioweb\Shop\Document;

use JetBrains\PhpStorm\Deprecated;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\OrderGateDocument;
use Mioweb\Shop\Order\OrderRepository;
use MwObjectCache;
use MwsBankAccount;
use MwsContact;
use MwsDocumentItem;
use MwsDocumentType;
use MwsEmailType;
use MwsException;
use MwsPrice;
use MwsVatAccounting;
use WP_Post;

define('MWS_DOCUMENT_META_KEY', MWS_OPTION . 'document');
define('MWS_DOCUMENT_META_KEY_TYPE', MWS_OPTION . 'type');
define('MWS_DOCUMENT_META_KEY_ORDER_ID', MWS_OPTION . 'order_Id');
define('MWS_DOCUMENT_META_KEY_NUMBER', MWS_OPTION . 'number');
define('MWS_DOCUMENT_META_KEY_ORDER_NUMBER', MWS_OPTION . 'order_number');
define('MWS_DOCUMENT_META_KEY_MPOHODA_ID', MWS_OPTION . 'mpohoda_id');

class Document implements OrderGateDocument
{

	/** @var WP_Post Post object. */
	private $_post = null;

	private $_number;

	private $_hash;

	private $_type;

	private ?string $_mPohodaId = null;

	private ?Order $_order = null;

	#[Deprecated(reason: 'This is link for old unused WP order post objects')]
	private $_oldOrderId = null;

	private ?int $_orderId = null;

	private $_orderNumber = null;

	private $_currency;

	private $_nativeCurrency;

	private $_currencyExchangeRate = null;

	private $_supplierContact = null;

	private $_customerContact = null;

	private $_shippingContact = null;

	/** @var mixed[]|null */
	private $_paymentMethod = null;

	/** @var MwsBankAccount|null */
	private $_bankAccount = null;

	/** @var string|null */
	private $_invoiceNote = null;

	/** @var string|null */
	private $_vatAccounting = null;

	/** @var bool */
	private $_showVat = false;

	/** @var bool */
	private $_reverseChargeApplied = false;

	/** @var bool */
	private $_useSimplifiedInvoice = false;

	/** @var bool */
	private $_isTest = false;

	/** @var MwsDocumentItem[] */
	private $_items = [];

	/** @var MwsPrice */
	private $_price;

	/** @var bool|null */
	private $_paidWhenCreated = null;

	public function __construct(?WP_Post $post = null, ?string $number = null, ?string $type = null)
	{
		if ($post) { // existing document
			$this->_post = $post;
			$this->_oldOrderId = $post->post_parent;
			$this->load();
		} else { // new document
			if (!$number) {
				throw new MwsException('Number need value.');
			}
			if (!MwsDocumentType::isValidValue($type)) {
				throw new MwsException('Type is not valid.');
			}
			$this->_number = $number;
			$this->_hash = uniqid();
			$this->_type = $type;
			$this->_currency = MWS()->getDefaultCurrency('key');
			$this->_nativeCurrency = MWS()->getDefaultCurrency('key');
		}
	}

	public function getId(): ?int
	{
		return $this->_post ? $this->_post->ID : null;
	}

	#[Deprecated(reason: 'This is link for old unused WP order post objects')]
	public function getOldOrderId(): ?int
	{
		return $this->_oldOrderId;
	}

	#[Deprecated(reason: 'This is link for old unused WP order post objects')]
	public function setOldOrderId(int $orderId): void
	{
		$this->_oldOrderId = $orderId;
	}

	public function setOrder(?Order $order): void
	{
		$this->_order = $order;
		$this->_orderId = $order?->getId();
		$this->setOrderNumber($order?->getNumber());
	}

	/**
	 * Number is unique only for type
	 */
	public function getNumber(): string
	{
		return $this->_number;
	}

	public function getType(): string
	{
		return $this->_type;
	}

	public function getHash(): string
	{
		return $this->_hash;
	}

	public function getCurrency(): string
	{
		return $this->_currency;
	}

	public function setCurrency(string $currency): void
	{
		$this->_currency = $currency;
	}

	public function setNativeCurrency(string $nativeCurrency): void
	{
		$this->_nativeCurrency = $nativeCurrency;
	}

	public function getNativeCurrency(): string
	{
		return $this->_nativeCurrency;
	}

	public function setCurrencyExchangeRate(?float $currencyExchangeRate): void
	{
		$this->_currencyExchangeRate = $currencyExchangeRate;
	}

	public function getCurrencyExchangeRate(): ?float
	{
		return $this->_currencyExchangeRate;
	}

	public function getPrice(): MwsPrice
	{
		/*
		$priceVatIncluded = 0.0;
		$priceVatExcluded = 0.0;
		$currency = $this->getCurrency();

		foreach ($this->getItems() as $item) {
			$totalPrice = $item->getTotalPrice();
			$priceVatIncluded += $totalPrice->getPriceVatIncluded();
			$priceVatExcluded += $totalPrice->getPriceVatExcluded();
		}

		return MwsPrice::createByFields(
			round($priceVatIncluded, 2),
			round($priceVatExcluded, 2),
			0.0,
			$currency
		);
		*/
		return $this->_price;
	}

	public function setPrice(MwsPrice $price): void
	{
		$this->_price = $price;
	}

	public function getOrderNumber(): ?string
	{
		return $this->_orderNumber;
	}

	public function getMPohodaId(): ?string
	{
		return $this->_mPohodaId;
	}

	/** @internal */
	public function getOrder(): ?Order
	{
		if ($this->_order !== null) {
			return $this->_order;
		}

		if ($this->_orderNumber === null) {
			return null;
		}

		$this->_order = OrderRepository::getOrderByOrderNum($this->_orderNumber);

		return $this->_order;
	}

	public function setOrderNumber(?string $orderNumber): void
	{
		$this->_orderNumber = $orderNumber;
	}

	public function setMPohodaId(?string $mPohodaId): void
	{
		$this->_mPohodaId = $mPohodaId;
	}

	public function getSupplierContact(): ?MwsContact
	{
		return $this->_supplierContact;
	}

	public function setSupplierContact(?MwsContact $supplierContact): void
	{
		$this->_supplierContact = $supplierContact;
	}

	public function getCustomerContact(): ?MwsContact
	{
		return $this->_customerContact;
	}

	public function setCustomerContact(?MwsContact $customerContact): void
	{
		$this->_customerContact = $customerContact;
	}

	public function getShippingContact(): ?MwsContact
	{
		return $this->_shippingContact;
	}

	public function setShippingContact(?MwsContact $shippingContact): void
	{
		$this->_shippingContact = $shippingContact;
	}

	public function getPaymentMethod(): ?array
	{
		return $this->_paymentMethod;
	}

	public function setPaymentMethod(?array $paymentMethod): void
	{
		$this->_paymentMethod = $paymentMethod;
	}

	public function getBankAccount(): ?MwsBankAccount
	{
		return $this->_bankAccount;
	}

	public function setBankAccount(?MwsBankAccount $bankAccount): void
	{
		$this->_bankAccount = $bankAccount;
	}

	public function getInvoiceNote(): ?string
	{
		return $this->_invoiceNote;
	}

	public function setInvoiceNote(?string $invoiceNote): void
	{
		$this->_invoiceNote = $invoiceNote;
	}

	public function isReverseChargeApplied(): bool
	{
		return $this->_reverseChargeApplied;
	}

	public function setReverseCharge(bool $applied = true): void
	{
		$this->_reverseChargeApplied = $applied;
	}

	public function getVatAccounting(): ?string
	{
		return $this->_vatAccounting;
	}

	public function setVatAccounting(?string $vatAccounting): void
	{
		if ($vatAccounting !== null && !MwsVatAccounting::isValidValue($vatAccounting)) {
			throw new MwsException('Invalid MwsVatAccounting values.');
		}

		$this->_vatAccounting = $vatAccounting;
	}

	public function setShowVat(bool $showVat): void
	{
		$this->_showVat = $showVat;
	}

	public function showVat(): bool
	{
		return $this->_showVat;
	}

	public function isTest(): bool
	{
		return $this->_isTest;
	}

	public function setIsTest(bool $isTest = true): void
	{
		$this->_isTest = $isTest;
	}

	public function useSimplifiedInvoice(): bool
	{
		return $this->_useSimplifiedInvoice;
	}

	public function setSimplifiedInvoice(bool $isSimplified = true): void
	{
		$this->_useSimplifiedInvoice = $isSimplified;
	}

	/** @return MwsDocumentItem[] */
	public function getItems(): array
	{
		return $this->_items;
	}

	public function addItem(MwsDocumentItem $item): void
	{
		$this->_items[] = $item;
	}

	public function getName(): string
	{
		return MwsDocumentType::getCaption($this->getType()) . ' - ' . $this->getNumber();
	}

	private function getBaseUrl(array $query = []): string
	{
		$url = get_permalink($this->_post->ID);
		$query['hash'] = $this->getHash();
		$url = add_query_arg($query, $url);

		return $url;
	}

	public function getDownloadUrl(): string
	{
		$url = $this->getBaseUrl([
			'downloadPdf' => true,
		]);

		return $url;
	}

	// @TODO implement document agenda
	public function getDetailUrl(): ?string
	{
		return null;
	}

	// @TODO implement document agenda
	public function getEditUrl(): ?string
	{
		return null;
	}

	public function isPaid(): bool
	{
		return $this->getOrder()?->getGateLive()->isPaid() ?? false;
	}

	public function getPaidAt(): ?\DateTimeInterface
	{
		$order = $this->getOrder();

		return $order?->getGateLive()->getPaidOn() !== null
			? (new \DateTimeImmutable())->setTimestamp($order->getGateLive()->getPaidOn())
			: null;
	}

	public function getCreatedAt(): \DateTimeImmutable
	{
		return (new \DateTimeImmutable($this->_post ? $this->_post->post_date_gmt : 'now', new \DateTimeZone('GMT')))->setTimezone(wp_timezone());
	}

	public function getDueDate(): \DateTimeImmutable
	{
		return $this->getCreatedAt()->modify('+ 14 DAYS');
	}

	public function getTaxableSupplyAt(): \DateTimeImmutable
	{
		return $this->getPaidAt() && $this->_paidWhenCreated ? $this->getPaidAt() : $this->getCreatedAt();
	}

	public function setPaidWhenCreated(bool $paidWhenCreated): void
	{
		$this->_paidWhenCreated = $paidWhenCreated;
	}

	public function isPaidWhenCreated(): bool
	{
		return $this->_paidWhenCreated ?? true;
	}

	private function load(): void
	{
		$this->_number = get_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_NUMBER, true);
		$this->_orderNumber = get_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_ORDER_NUMBER, true) ?: null;
		$this->_type = get_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_TYPE, true);
		$this->_orderId = get_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_ORDER_ID, true) ?: null;
		$this->_mPohodaId = get_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_MPOHODA_ID, true) ?: null;
		$meta = get_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY, true);
		$this->_hash = $meta['hash'] ?? '';
		$this->_currency = $meta['currency'];
		$this->_nativeCurrency = $meta['native_currency'];
		$this->_currencyExchangeRate = $meta['currencyExchangeRate'] ?? null;
		$this->_items = array_map(function (array $item) {
			return MwsDocumentItem::createByArray($item);
		}, $meta['items']);
		$this->_supplierContact = $meta['supplierContact'] ? MwsContact::createFromArray($meta['supplierContact']) : null;
		$this->_customerContact = $meta['customerContact'] ? MwsContact::createFromArray($meta['customerContact']) : null;
		$this->_shippingContact = $meta['shippingContact'] ? MwsContact::createFromArray($meta['shippingContact']) : null;
		$this->_bankAccount = ($meta['bankAccount'] ?? null) !== null ? MwsBankAccount::createFromArray($meta['bankAccount']) : null;
		$this->_paymentMethod = $meta['paymentMethod'] ?? null;
		$this->_invoiceNote = $meta['invoiceNote'] ?? null;
		$this->_isTest = (bool) ($meta['isTest'] ?? false);
		$this->_vatAccounting = $meta['vatAccounting'] ?? null;
		$this->_showVat = $meta['showVat'] ?? true;
		$this->_reverseChargeApplied = (bool) ($meta['reverseChargeApplied'] ?? false);
		$this->_useSimplifiedInvoice = (bool) ($meta['useSimplifiedInvoice'] ?? false);
		$this->_price = $meta['price'] ?? new MwsPrice(0.0);
		$this->_paidWhenCreated = $meta['paidWhenCreated'] ?? null; //null for back compatibility
	}

	public function save(): bool
	{
		$meta = [
			'hash' => $this->getHash(),
			'currency' => $this->getCurrency(),
			'native_currency' => $this->getNativeCurrency(),
			'currencyExchangeRate' => $this->getCurrencyExchangeRate(),
			'items' => array_map(function (MwsDocumentItem $item) {
				return $item->toArray();
			}, $this->getItems()),
			'supplierContact' => ($supplierContact = $this->getSupplierContact()) ? $supplierContact->toArray() : null,
			'customerContact' => ($customerContact = $this->getCustomerContact()) ? $customerContact->toArray() : null,
			'shippingContact' => ($shippingContact = $this->getShippingContact()) ? $shippingContact->toArray() : null,
			'paymentMethod' => $this->getPaymentMethod(),
			'bankAccount' => ($bankAccount = $this->getBankAccount()) ? $bankAccount->toArray() : null,
			'invoiceNote' => $this->getInvoiceNote(),
			'isTest' => $this->isTest(),
			'vatAccounting' => $this->getVatAccounting(),
			'showVat' => $this->showVat(),
			'reverseChargeApplied' => $this->isReverseChargeApplied(),
			'useSimplifiedInvoice' => $this->useSimplifiedInvoice(),
			'price' => $this->getPrice(),
			'paidWhenCreated' => $this->_paidWhenCreated,
		];

		$number = $this->getNumber();
		if (!$this->_post) { // create new post
			$args = [
				'post_title' => $this->getName(),
				'post_parent' => $this->getOldOrderId(),
				'post_status' => 'publish',
				'post_type' => MWS_DOCUMENT_SLUG,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_name' => sanitize_title($this->getName()),
				'meta_input' => [
					MWS_DOCUMENT_META_KEY => $meta,
					MWS_DOCUMENT_META_KEY_TYPE => $this->getType(),
					MWS_DOCUMENT_META_KEY_ORDER_ID => $this->getOrder()?->getId(),
					MWS_DOCUMENT_META_KEY_NUMBER => $number,
					MWS_DOCUMENT_META_KEY_ORDER_NUMBER => $this->getOrderNumber(),
					MWS_DOCUMENT_META_KEY_MPOHODA_ID => $this->getMPohodaId(),
				],
			];
			$postId = wp_insert_post($args);
			if ($postId) {
				$this->_post = get_post($postId);
				$this->load();
			} else {
				mwshoplog('New document could not be saved into database.', MWLL_ERROR, 'document');

				return false;
			}
		} else {
			update_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY, $meta);
			update_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_TYPE, $this->getType());
			update_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_ORDER_ID, $this->getOrder()?->getId());
			update_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_NUMBER, $number);
			update_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_ORDER_NUMBER, $this->getOrderNumber());
			update_post_meta($this->_post->ID, MWS_DOCUMENT_META_KEY_MPOHODA_ID, $this->getMPohodaId());
		}

		return true;
	}

	public function copyTo(string $documentType): self
	{
		$number = MWS()->getDocumentNumberGenerator($documentType, $this->isTest())->next(true);
		$document = new Document(null, $number, $documentType);
		$document->setOrderNumber($this->getOrderNumber());
		$document->setMPohodaId($this->getMPohodaId());
		$document->setOldOrderId($this->getOldOrderId());
		$document->setOrder($this->getOrder());
		$document->setPrice($this->getPrice());
		$document->setCurrency($this->getCurrency());
		$document->setNativeCurrency($this->getNativeCurrency());
		$document->setCurrencyExchangeRate($this->getCurrencyExchangeRate());
		$document->setSupplierContact($this->getSupplierContact());
		$document->setCustomerContact($this->getCustomerContact());
		$document->setShippingContact($this->getShippingContact());
		$document->setPaymentMethod($this->getPaymentMethod());
		$document->setBankAccount($this->getBankAccount());
		$document->setInvoiceNote($this->getInvoiceNote());
		$document->setIsTest($this->isTest());
		$document->setReverseCharge($this->isReverseChargeApplied());
		$document->setVatAccounting($this->getVatAccounting());
		$document->setShowVat($this->showVat());
		$document->setSimplifiedInvoice($this->useSimplifiedInvoice());
		foreach ($this->getItems() as $documentItem) {
			$document->addItem($documentItem);
		}
		$document->save();

		return $document;
	}

	public function sendToCustomer(string $emailType = MwsEmailType::PayedOrder): void
	{
		$customerContact = $this->getCustomerContact();
		if (!$customerContact) {
			// @TODO exception
			return;
		}

		$order = OrderRepository::getOrderByOrderNum($this->getOrderNumber());
		if ($order === null) {
			// @TODO exception
			return;
		}

		$pdf = MWS()->getInvoicePdfGenerator()->generate($this);
		$pdf = $pdf->output();
		$tempDir = get_temp_dir();
		$filename = $tempDir . $this->getName() . '.pdf';
		file_put_contents($filename, $pdf);

		try {
			$body = MWS()->getEmailContent($emailType, $order);
			$subject = MWS()->getEmailSubject($emailType, $order);
			$customerContact->sendMail($subject, $body, [$filename]);
		} catch (\Throwable $e) {
			throw $e;
		} finally {
			unlink($filename);
		}
	}

	public static function createNew(WP_Post $post): ?self
	{
		if ($post->post_type !== MWS_DOCUMENT_SLUG) {
			throw new MwsException('Passed post is not of document type.');
		}
		$obj = MwObjectCache::get(self::class, $post->ID);
		if (!$obj) {
			$obj = new self($post);
			MwObjectCache::add($obj, $post->ID);
		}

		return $obj;
	}

	public static function getOneById(int $id): ?self
	{
		$post = get_post($id);

		if ($post) {
			try {
				return static::createNew($post);
			} catch (MwsException $e) {
				mwshoplog(
					sprintf(__('Nepodařilo se vytvořit instanci dokumentu [%d] se zprávou: %s', 'mwshop'), $id, $e->getMessage()),
					MWLL_ERROR
				);
			}
		}

		return null;
	}

	public static function getOneByNumber(string $type, string $number): ?self
	{
		if (!$number) {
			return null;
		}
		$args = [
			'meta_query' => [
				'relation' => 'AND',
				'type' => [
					'key' => MWS_DOCUMENT_META_KEY_TYPE,
					'value' => $type,
				],
				'number' => [
					'key' => MWS_DOCUMENT_META_KEY_NUMBER,
					'value' => $number,
				],
			],
			'post_type' => MWS_DOCUMENT_SLUG,
			'post_status' => 'any',
			'posts_per_page' => 1,
		];
		$posts = get_posts($args);
		$post = reset($posts);
		if ($post) {
			try {
				return self::createNew($post);
			} catch (\Exception $e) {
			}
		}

		return null;
	}

	/** @return Document[] */
	#[Deprecated(reason: 'This is link for old unused WP order post objects')]
	public static function getAllByOldOrderId(int $orderId): array
	{
		if (!$orderId) {
			return [];
		}
		$args = [
			'post_type' => MWS_DOCUMENT_SLUG,
			'post_parent' => $orderId,
			'post_status' => 'any',
			'posts_per_page' => -1,
		];

		return array_map(function (WP_Post $post) {
			return self::createNew($post);
		}, get_posts($args));
	}

	/** @return Document[] */
	public static function getAllByOrderId(int $orderId): array
	{
		if (!$orderId) {
			return [];
		}
		$args = [
			'meta_key' => MWS_DOCUMENT_META_KEY_ORDER_ID,
			'meta_value' => $orderId,
			'post_type' => MWS_DOCUMENT_SLUG,
			'post_status' => 'any',
			'posts_per_page' => -1,
		];

		return array_map(function (WP_Post $post) {
			return self::createNew($post);
		}, get_posts($args));
	}

}
