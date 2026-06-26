<?php

use Mioweb\Shop\Document\Document;
use Mioweb\Shop\Order\Exceptions\OrderHasNoHashException;
use Mioweb\Shop\Order\IOrder;
use Mioweb\Shop\Order\IOrderGate;
use Mioweb\Shop\PacketSize;
use Nette\Http\UrlScript;
use Nette\Utils\Validators;

/**
 * @deprecated In favour of @see \Mioweb\Shop\Order\Order::class
 *
 * Encapsulates one order of MioShop - internally stored as WP custom post type {@link MWS_ORDER_SLUG}.
 * Contains ordering data: invoice address, optional shipping address, items with links to products (subset
 * of product data is stored within the item for case the product is not available anymore) and prices, total prices,
 * chosen shipping method, customer's note, result of payment gateway.
 * More to that contains status of the order, flag if fully paid.
 * Additional attachments can be present, like PDFs of invoice, proforma invoice, return payment, modification of
 * invoice - all stored as WP attachments.
 */
class MwsOrder implements IOrder
{

	public const MAXIMUM_FILE_SIZE_MB = 12;

	/** @var WP_Post Post object. */
	private $_post = null;

	/** @var array Loaded metadata. */
	private $_meta = null;

	private ?string $_orderNum = null;

	/** @var MwsOrderSource|null */
	private $_source = null;

	private $_status = MwsOrderStatus::Ordered;

	private $_opened = false;

	/** @var bool If order is in created from selling form in test mode */
	private $_isTest = false;

	private ?string $_gateId = null;

	private ?string $_currency = null;

	private ?string $_note = null;

	private ?string $_trackingNumber = null;

	/** @var MwsOrderGate Link to order data stored at the gate side. */
	private $_gateLive = null;

	/** @var MwsOrderItems */
	private $_items = null;

	/** @var array Loaded metadata. */
	private $_history = null;

	/** User-checked and saved total weight. */
	private ?float $_totalWeight = null;

	/** @var array */
	private array $_archive;

	public function __construct(?WP_Post $post = null, ?string $orderNum = null)
	{
		if ($post) {
			$this->_post = $post;
			$this->load();
		} else {
			if (!$orderNum) {
				throw new MwsException('Field [orderNum] need value.');
			}
			$this->_meta = [
				'orderNum' => $orderNum,
				'status' => MwsOrderStatus::Ordered,
				'hash' => uniqid(),
			];
		}
	}

	public function getPost(): ?WP_Post
	{
		return $this->_post;
	}

	public function getItems(): MwsOrderItems
	{
		if (!$this->_items) {
			$this->_items = new MwsOrderItems($this);
			foreach ($this->_meta['items'] ?? [] as $item) {
				$this->_items->add(MwsOrderItem::createByArray($item));
			}
		}

		return $this->_items;
	}

	public function getId(): ?int
	{
		return $this->_post ? $this->_post->ID : null;
	}

	public function getMeta(): ?array
	{
		return $this->_meta;
	}

	public function setMeta(string $key, $data): void
	{
		$this->_meta[$key] = $data;
	}

	/** @return MwsPayment[] */
	public function getPayments(): array
	{
		return MwsPayment::getAllByOrder($this);
	}

	/**
	 * ID of the order. This ID is sourced from the gateway at the moment when the order is successfully submitted to the gateway.
	 */
	public function getNumber(): string
	{
		return $this->_orderNum ?? $this->_meta['orderNum'];
	}

	/**
	 * ID of the gateway that has been used to fulfill the order.
	 */
	public function getGateIdentifier(): string
	{
		return $this->_gateId ?? $this->_meta['gateId'] ?? '';
	}

	public function setGateId(string $gateId): void
	{
		$this->_gateId = $gateId;
	}

	public function getStatus(): int
	{
		return $this->_status;
	}

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
			$this->_status = $status;
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
			$this->processAutomations(MwsAutomationEvent::OnFinish);
		} elseif ($newStatus === MwsOrderStatus::Cancelled) {
			$this->processAutomations(MwsAutomationEvent::OnStorno);
		}
	}

	public function isCancelled(): bool
	{
		return ($this->getStatus() === MwsOrderStatus::Cancelled);
	}

	public function createInvoice()
	{
		$invoice = $this->getGateLive()->createInvoice();
		$this->addHistory('Faktura vystavena', MwsOrderEvent::InvoiceCreated);
		do_action('mw_invoice_created', $invoice);

		return $invoice;
	}

	public function processAutomations(string $event): void
	{
		if ($this->getSource() !== null && $this->getSource()->getType() === MwsOrderSourceType::Form) {
			/** @var MwsForm|null $form */
			$form = MwsForm::getOneById($this->getSource()->getFormId());
			$automations = $form !== null ? $form->getAutomations() : [];
		} else {
			$automations = MWS()->getGlobalAutomations();
		}

		foreach ($this->getItems()->getProducts() as $orderItem) {
			if ($product = $orderItem->getProduct()) {
				$automations = array_merge($automations, $product->getAutomations());
			}
		}

		$contact = $this->getGateLive() ? $this->getGateLive()->getInvoiceContact() : null;
		foreach ($automations as $automation) {
			if ($automation['event'] !== $event) {
				continue;
			}

			$done = false;

			$message = '';
			switch ($automation['action']) {
				case MwsAutomationAction::SendEmail:
					if ($contact) {
						$file = MWS()->getEmailAttachment(MwsEmailType::CustomEmails, $this, (int) $automation['email_index'] ?? '');
						if ($file && (filesize($file) > self::MAXIMUM_FILE_SIZE_MB * pow(2, 20))) {
							$message = __(sprintf(
								'Příloha e-mailu je příliš velká. Maximální velikost souboru je %d MB.',
								self::MAXIMUM_FILE_SIZE_MB
							), 'mwshop');

							break;
						}

						$attachments = $file ? [$file] : [];

						$contact->sendMail(
							MWS()->getEmailSubject(MwsEmailType::CustomEmails, $this, (int) $automation['email_index'] ?? ''),
							MWS()->getEmailContent(MwsEmailType::CustomEmails, $this, (int) $automation['email_index'] ?? ''),
							$attachments
						);
						$message = __('Email byl odeslán na adresu: ', 'mwshop') . $contact->getEmail();
						$done = true;
					}

					break;
				case MwsAutomationAction::AddContact:
					if ($contact && (bool) ($automation['contact_list']['id'] ?? null)) {
						$customer = $contact->getPerson()->getFullName();

						$result = mwEmailingApi()->save_to_list_details(
							$automation['contact_list']['api'],
							$automation['contact_list']['id'],
							$contact->getEmail(),
							$automation['contact_list']['purpose'] ?? null,
							[
								'name' => $contact->getPerson()->getFirstName(),
								'surname' => $contact->getPerson()->getLastName(),
								'phone' => $contact->getPhone(),
							],
						);

						if ($result['status'] === true) {
							$message = __('Kontakt byl přidán do seznamu s ID: ', 'mwshop') . $automation['contact_list']['id'] . ' (' . mwApiConnect()->getApi($automation['contact_list']['api'])->getName() . ')';
						} else {
							$message = __('Kontakt se nepodařilo přidat do seznamu s ID: ', 'mwshop') . $automation['contact_list']['id'] . ' (' . mwApiConnect()->getApi($automation['contact_list']['api'])->getName() . ')';
							if ($result['message']) {
								$message .= __('Chyba:', 'mwshop') . $result['message'];
							}
						}

						$done = true;
					}

					break;
				case MwsAutomationAction::RemoveContact:
					if ($contact && isset($automation['contact_list']['id'])) {
						$result = mwEmailingApi()->remove_from_list(
							$automation['contact_list']['api'],
							$automation['contact_list']['id'],
							$contact->getEmail()
						);

						if ($result['status'] === true) {
							$message = __('Kontakt byl odebrán ze seznamu s ID: ', 'mwshop') . $automation['contact_list']['id'] . ' (' . mwApiConnect()->getApi($automation['contact_list']['api'])->getName() . ')';
						} else {
							$message = __('Kontakt se nepodařilo odebrat ze seznamu s ID.', 'mwshop') . $automation['contact_list']['id'] . ' (' . mwApiConnect()->getApi($automation['contact_list']['api'])->getName() . ')';
							if ($result['message']) {
								$message .= __('Chyba:', 'mwshop') . $result['message'];
							}
						}

						$done = true;
					}

					break;
				case MwsAutomationAction::AddMembership:
					if ($contact && class_exists('mwMemberModule')) {
						$autSet = $automation['member_section'] ?? null;

						if ($autSet !== null) {
							$client = [
								'user_email' => $contact->getEmail(),
								'user_login' => $contact->getEmail(),
								'first_name' => $contact->getPerson()->getFirstName(),
								'last_name' => $contact->getPerson()->getLastName(),
							];

							/* back compatibility @TODO repair data in new migration and remove this code */
							$lVals = $autSet['levels'][$autSet['section']] ?? ($autSet[$autSet['section']]['levels'] ?? ($autSet[$autSet['section']] ?? []));
							/* end back compatibility */

							$params = [
								'send_email' => isset($autSet['nosend_email']) ? false : true,
								'levels' => $lVals,
							];

							$membershipType = $autSet['membership_type'];
							if ($membershipType === 'nolimit') {
								$params['setexp'] = '0';
							} elseif ($membershipType === 'limit') {
								$params['days'] = $autSet['membership_days'];
							} elseif ($membershipType === 'limit_date' && $autSet['membership_limit_date']) {
								$params['setexp'] = $autSet['membership_limit_date'];
							}

							$params['start'] = null;
							if (isset($autSet['set_date'])) {
								$regDate = $autSet['date']['date'] ?: date('Y-m-d', current_time('timestamp'));
								$regTime = $autSet['date']['hour'] . ':' . $autSet['date']['minute'];
								$params['start'] = $regDate . ' ' . $regTime;
							}
							$memberSection = MwMemberSection::getOneById($autSet['section']);
							if ($memberSection) {
								$return = Mioweb\Member\Notifications::addMembership($client, $memberSection->getId(), $params, false, 'by_automation');
								if ($return['status'] === 1) {
									$info = $memberSection->getName();
									if (count($params['levels'])) {
										$addedLevels = [];
										foreach ($params['levels'] as $levelId) {
											$level = Mioweb\Member\MemberLevel::getOneById($levelId);
											if ($level !== null) {
												$addedLevels[] = $level->getName();
											}
										}
										$info .= ' (';
										$info .= count($addedLevels) > 1 ? __('úrovně', 'mwshop') : __('úroveň', 'mwshop');
										$info .= ': ' . implode(', ', $addedLevels) . ')';
									}
									if ($return['user_existed']) {
										$message .= sprintf(__('Bylo upraveno členství pro email %s v členské sekci %s', 'mwshop'), $contact->getEmail(), $info);
									} else {
										$message .= sprintf(__('Bylo vytvořeno členství pro email %s v členské sekci %s', 'mwshop'), $contact->getEmail(), $info);
									}
								} else {
									$message .= __('Nepodařilo se vytvořit přístup do členské sekce.', 'mwshop');
									$message .= __('Chyba', 'mwshop') . ': ' . $return['message'];
								}
							} else {
								$message .= __('Nepodařilo se vytvořit přístup do členské sekce. Členská sekce s tímto ID neexistuje.', 'mwshop');
							}
						}

						$done = true;
					}

					break;
				case MwsAutomationAction::RemoveMembership:
					if ($contact && class_exists('mwMemberModule')) {
						$autSet = $automation['remove_member_section'];
						$email = $contact->getEmail();

						$memberSection = MwMemberSection::getOneById($autSet['section']);

						/* back compatibility @TODO repair data in new migration and remove this code */
						$lVals = $autSet['levels'][$autSet['section']] ?? ($autSet[$autSet['section']]['levels'] ?? ($autSet[$autSet['section']] ?? null));
						/* end back compatibility */

						if (isset($lVals)) {
							foreach ($lVals as $level) {
								$return = Mioweb\Member\Notifications::stopMembershipByEmail($email, $autSet['section'], $level);

								if ($return['status'] == 201) {
									$message = sprintf(__('Pro email %s byl zrušen přístup do některých úrovní v členské sekci %s', 'mwshop'), $contact->getEmail(), $memberSection ? $memberSection->getName() : __('(smazaná)', 'mwshop'));
								}
							}
						} else {
							$return = Mioweb\Member\Notifications::stopMembershipByEmail($email, $autSet['section']);

							if ($return['status'] == 201) {
								$message = sprintf(__('Pro email %s byl zrušen přístup do členské sekce %s', 'mwshop'), $contact->getEmail(), $memberSection ? $memberSection->getName() : __('(smazaná)', 'mwshop'));
							}
						}

						/** @phpstan-ignore-next-line */
						if ($return['status'] != 201) {
							/** @phpstan-ignore-next-line */
							$message = __('Nepodařilo se zrušit přístup do členské sekce.', 'mwshop') . ' ' . $return['message'];
						}

						$done = true;
					}

					break;
				case MwsAutomationAction::RunScript:
					try {
						$args = [
							'method' => 'POST',
							'timeout' => 45,
							'redirection' => 5,
							'httpversion' => '1.1',
							'blocking' => true,
							'headers' => [],
							'body' => [
								'ID' => $this->getId(),
								'number' => $this->getNumber(),
								'status' => $this->getStatus(),
								'variable_symbol' => $this->getNumber(),
								'exchange_rate' => $this->getCurrencyExchangeRate(),
								'paid' => $this->isPaid(),
								'paid_on' => $this->getPaidOnDateFormatted(),
								'source' => $this->getSource()->toArray(),
								'payment_type' => $this->getPayment()['type'] ?? null,
								'shipping' => $this->getShipping(),
								'heureka_disagree' => $this->getHeurekaDisagree(),
								'customer' => $this->getCustomer(),
								'customer_note' => $this->getCustomerNote(),
								'discount_code' => $this->getDiscountCode(),
								'items' => [],
								'simplified_invoice' => $this->useSimplifiedInvoice(),
								'currency' => $this->getGateLive()->getCurrency(),
								'native_currency' => $this->getNativeCurrency(),
								'total' => $this->getPrice()->getPriceVatIncluded(),
								'total_vat' => $this->getPrice()->getVatAmount(),
								'total_native' => $this->getNativePrice()->getPriceVatIncluded(),
								'total_vat_native' => $this->getNativePrice()->getVatAmount(),
							],
						];

						foreach ($this->getItems()->getProducts() as $orderItem) {
							if ($product = $orderItem->getProduct()) {
								$args['body']['items'][] = [
									'id' => $product->getId(),
									'name' => $product->getName(),

									'count' => $orderItem->getCount(),
									'type' => $product->getType(),
									//"oss" => false,
									'price' => $product->getPrice()->getPriceVatIncluded(),
									'price_novat' => $product->getPrice()->getPriceVatExcluded(),
									'price_vat' => $product->getPrice()->getVatAmount(),
									'vat' => $product->getPrice()->getVatPercentage(),
								];
							}
						}

						$response = wp_remote_post($automation['script_url'], $args);

						$return = json_decode(wp_remote_retrieve_body($response));

						$message = is_wp_error($response) || !isset($return->status) || (isset($return->error)) ? sprintf(__('Programový skript %s nebyl úspěšný: %s', 'mwshop'), $automation['script_url'], $return->error->message ?? '') : __('Byl úspěšně spuštěn programový skript: ', 'mwshop') . $automation['script_url'];

						$done = true;
					} catch (Throwable $e) {
					}

					break;
				case MwsAutomationAction::SendFile:
					if ($contact) {
						$file = $this->processFileName($automation['file']);

						if ($file) {
							if (filesize($file) > self::MAXIMUM_FILE_SIZE_MB * pow(2, 20)) {
								$message = __(sprintf(
									'Soubor je příliš velký. Maximální velikost souboru je %d MB.',
									self::MAXIMUM_FILE_SIZE_MB
								), 'mwshop');
							} else {
								$success = $contact->sendMail(
									MWS()->getEmailSubject(MwsEmailType::ElectronicDelivery, $this),
									MWS()->getEmailContent(MwsEmailType::ElectronicDelivery, $this),
									[$file]
								);

								$message = $success
									? __('Soubor byl odeslán na adresu: ', 'mwshop') . $contact->getEmail()
									: __('Soubor se nepodařilo odeslat.', 'mwshop');
							}
						} else {
							$message = __('Soubor neexistuje.', 'mwshop');
						}
					}
					$done = true;

					break;
			}

			if ($done) {
				$this->addHistory($message); // TODO: event and more info
			}
		}
	}

	public function processFileName(string $file): ?string
	{
		if (Validators::isUrl($file)) {
			$url = new UrlScript($file);
			$file = rtrim(ABSPATH, '/') . '/' . ltrim($url->getPath(), '/');
		}

		return file_exists($file) ? $file : null;
	}

	public function getHistory(): array
	{
		if ($this->_history === null) {
			$this->_history = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_HISTORY, true) ?: [];
		}

		return $this->_history;
	}

	public function addHistory(string $text, ?string $event = null): void
	{
		$history = $this->getHistory();
		$time = (new DateTimeImmutable())->getTimestamp();
		while (isset($history[$time])) {
			$time++;
		}
		$history[$time] = [
			'text' => $text,
			'event' => $event,
			'user_id' => mwUser::getCurrent()->getId(),
		];
		$this->_history = $history;
		update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_HISTORY, $history);
	}

	public function getLastHistoryTimestamp(string $event): ?int
	{
		$history = array_reverse($this->getHistory(), true);
		foreach ($history as $timestamp => $historyEntry) {
			if (isset($historyEntry['event']) && $historyEntry['event'] === $event) {
				return $timestamp;
			}
		}

		return null;
	}

	public function getCustomerNote(): ?string
	{
		return $this->_meta['customerNote'] ?? null;
	}

	public function setCustomerNote(?string $customerNote): void
	{
		$this->_meta['customerNote'] = mw_encode_emojis($customerNote);
	}

	public function isPaid(): bool
	{
		return $this->_meta['isPaid'] ?? false;
	}

	public function getPaidOn(): ?int
	{
		return $this->_meta['paidOn'] ?? null;
	}

	public function getPaidOnDate(): ?DateTimeImmutable
	{
		$paidOn = $this->getPaidOn();

		return $paidOn ? (new DateTimeImmutable())->setTimestamp($paidOn) : null;
	}

	public function getPaidOnDateFormatted(): string
	{
		return $this->getPaidOn() ? mwPrintDate($this->getPaidOn(), 'date', true) : '';
	}

	public function isPaymentFailedNotificationSent(): bool
	{
		return (bool) ($this->_meta['payment_failed_notification_sent'] ?? false);
	}

	public function getUrlDirectPay(): string
	{
		return $this->_meta['urlDirectPay'] ?? '';
	}

	public function getLinkDirectPay($text = ''): string
	{
		$payUrl = $this->getUrlDirectPay();

		return $payUrl ? ' <a href="' . $payUrl . '" target="_blank">' . ($text ?: __('Zaplatit online', 'mwshop')) . '</a>' : '';
	}

	public function setUrlDirectPay(string $urlDirectPay): void
	{
		$this->_meta['urlDirectPay'] = $urlDirectPay;
	}

	public function getShipping(): array
	{
		return $this->_meta['shipping'] ?? [];
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
		$shipingPrice = null;
		$orderLive = $this->getGateLive();
		if ($orderLive) {
			foreach ($orderLive->getItems() as $item) {
				if ($item->getType() === MwsOrderItemType::Shipping) {
					$shipingPrice = $item->getPrice($orderLive->getCurrency());
				}
			}
		}

		return $shipingPrice;
	}

	public function setShipping(array $shipping): void
	{
		$this->_meta['shipping'] = $shipping;
	}

	public function getDiscountCode(): array
	{
		return $this->_meta['discount_code'] ?? [];
	}

	public function setDiscountCode(?MwsDiscountCode $discountCode): void
	{
		$dsc = $discountCode !== null ? [
			'id' => $discountCode->getId(),
			'code' => $discountCode->getCode(),
			'type' => $discountCode->getType(),
			'value' => $discountCode->getValue(),
			'max_count' => $discountCode->getMaxCount(),
			'used_count' => $discountCode->getUsedCount(),
		] : null;
		$this->_meta['discount_code'] = $dsc;
	}

	public function getCurrency(): string
	{
		return $this->_currency ?? $this->_meta['currency'] ?? '';
	}

	public function setCurrency($currency): void
	{
		$this->_currency = $currency;
	}

	public function getNote(): ?string
	{
		return $this->_note;
	}

	public function setNote(string $note): void
	{
		$this->_note = $note;
	}

	public function getTrackingNumber(): ?string
	{
		return $this->_trackingNumber;
	}
	public function setTrackingNumber(string $trackingNumber): void
	{
		$this->_trackingNumber = $trackingNumber;
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

	/** @return MwsOrderItem[] */
	public function getItemsWithoutWeight(): array
	{
		$items = $this->getItems()->getProducts();

		return array_filter($items, function (MwsOrderItem $item): bool {
			$weight = $this->getItemWeight($item);

			return $weight === null || $weight <= 0.0;
		});
	}

	public function getTotalWeight(): float
	{
		// Get user-checked total weight
		if (isset($this->_meta['total_weight'])) {
			return (float) $this->_meta['total_weight'];
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
		$this->_meta['total_weight'] = $totalWeight;
	}

	public function getPacketSize(): ?array
	{
		return $this->_meta['packet_size'] ?? null;
	}

	public function setPacketSize(?PacketSize $size): void
	{
		$this->_meta['packet_size'] = $size !== null ? $size->toArray() : null;
	}

	public function getItemWeight(MwsOrderItem $item): ?float
	{
		$weight = $item->getWeight();

		if ($weight === null || $weight <= 0.0) {
			// Try to get weight from product
			$product = $item->getProduct();
			$weight = $product !== null ? $product->getWeight() : null;
		}

		return $weight * $item->getCount();
	}

	public function getNativeCurrency(): string
	{
		return $this->_meta['native_currency'] ?? '';
	}

	public function setNativeCurrency(string $currency): void
	{
		$this->_meta['native_currency'] = $currency;
	}

	public function setCurrencyExchangeRate(?float $currencyExchangeRate): void
	{
		$this->_meta['exchange_rate'] = $currencyExchangeRate;
	}

	public function getCurrencyExchangeRate(): ?float
	{
		return $this->_meta['exchange_rate'] ?? null;
	}

	public function isReverseChargeApplied(): bool
	{
		return $this->_meta['reverse_charge_applied'] ?? false;
	}

	public function setReverseCharge(bool $applied = true): void
	{
		$this->_meta['reverse_charge_applied'] = $applied;
	}

	public function getPayment(): ?array
	{
		$payment = $this->_meta['payment'] ?? null;

		// backward compatibility
		if (is_string($payment)) {
			return ['type' => $payment];
		}

		return $payment ?? null;
	}

	public function setTotal(array $total): void
	{
		$this->_meta['total'] = $total;
	}
	public function getTotal(): array
	{
		return $this->_meta['total'] ?? [];
	}

	public function setShopVersion(string $version): void
	{
		$this->_meta['shop_version'] = $version;
	}
	public function getShopVersion(): string
	{
		return $this->_meta['shop_version'] ?? '';
	}

	public function setPayment(?array $payment): void
	{
		$this->_meta['payment'] = $payment;
	}

	public function getVatAccounting(): ?string
	{
		return $this->_meta['vatAccounting'] ?? null;
	}

	public function setVatAccounting(?string $vatAccounting): void
	{
		if ($vatAccounting !== null && !MwsVatAccounting::isValidValue($vatAccounting)) {
			throw new MwsException('Invalid MwsVatAccounting values.');
		}

		$this->_meta['vatAccounting'] = $vatAccounting;
	}

	public function setShowVat(bool $showVat): void
	{
		$this->_meta['showVat'] = $showVat;
	}

	public function showVat(): bool
	{
		$orderLive = $this->getGateLive();

		return $orderLive ? $orderLive->showVat() : true;
	}

	public function setAsOpened(): void
	{
		delete_post_meta($this->_post->ID, MWS_ORDER_META_KEY_NOTOPENED);
	}

	public function isOpened(): bool
	{
		return $this->_opened;
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
		return (bool) ($this->_meta['use_simplified_invoice'] ?? false);
	}

	public function setSimplifiedInvoice(bool $isSimplified = true): void
	{
		$this->_meta['use_simplified_invoice'] = $isSimplified;
	}

	public function getPaymentTitle(): string
	{
		$paymentInfo = $this->getPayment();

		return $paymentInfo['name'] ?? MwsPayType::getCaption($paymentInfo['type'] ?? '');
	}

	public function getCreatedAt(): DateTimeImmutable
	{
		return (new DateTimeImmutable($this->_post->post_date_gmt, new DateTimeZone('GMT')))->setTimezone(wp_timezone());
	}

	public function getCreatedDate(): string
	{
		return mwPrintDate(strtotime($this->_post->post_date));
	}

	public function getCreatedDateTime(): string
	{
		return strtotime($this->_post->post_date);
	}

	public function getEditUrl(): string
	{
		return mwSetting()->getObject(MWS_ORDER_SLUG)->getEditUrl($this->getId());
	}

	/**
	 * Live connector to order at the gateway side. Use this to get realtime information from the gateway. Data is automatically loaded.
	 */
	public function getGateLive(): ?IOrderGate
	{
		if (!$this->_gateLive) {
			$this->_gateLive = MWS()->gateways()->loadOrderFor($this);

			if ($this->_gateLive instanceof MwsOrderGate_Fapi) {
				$this->_gateLive->updateInvoiceData();
			}
		}

		return $this->_gateLive;
	}

	public function getCustomerId(): ?int
	{
		return $this->_meta['customerId'] ?? null;
	}

	public function setCustomerId(int $userId): void
	{
		$this->_meta['customerId'] = $userId;
	}

	public function getCustomer(): ?MwsCustomer
	{
		$orderLive = $this->getGateLive();

		return $orderLive ? $orderLive->getCustomer() : null;
	}

	public function getSupplierContact(): ?MwsContact
	{
		$orderLive = $this->getGateLive();

		return $orderLive ? $orderLive->getSupplier() : null;
	}

	public function getInvoiceContact(): ?MwsContact
	{
		$orderLive = $this->getGateLive();

		return $orderLive ? $orderLive->getInvoiceContact() : null;
	}

	public function setInvoiceContact(MwsContact $contact): void
	{
		$this->_meta['invoiceContact'] = $contact->toArray();
	}

	public function getShippingContact(): ?MwsContact
	{
		$orderLive = $this->getGateLive();

		return $orderLive ? $orderLive->getShippingContact() : null;
	}

	public function setShippingContact(?MwsContact $contact): void
	{
		$this->_meta['shippingContact'] = $contact ? $contact->toArray() : null;
	}

	public function getPrice(): ?MwsPrice
	{
		$orderLive = $this->getGateLive();

		return $orderLive ? $orderLive->getPrice() : null;
	}

	public function getNativePrice(): ?MwsPrice
	{
		$orderLive = $this->getGateLive();

		return $orderLive ? $orderLive->getNativePrice() : null;
	}

	public function getGateOrderData(): array
	{
		return $this->_meta['gateOrderData'] ?? [];
	}

	public function setGateOrderData(array $gateOrderData): void
	{
		$this->_meta['gateOrderData'] = $gateOrderData;
	}

	public function getHeurekaDisagree(): bool
	{
		return (bool) ($this->_meta['heureka_disagree'] ?? false);
	}

	public function setHeurekaDisagree(bool $heurekaDisagree): void
	{
		$this->_meta['heureka_disagree'] = $heurekaDisagree;
	}

	public function getSource(): ?MwsOrderSource
	{
		return $this->_source;
	}

	public function setSource(?MwsOrderSource $source): void
	{
		$this->_source = $source;
	}


	private function load(): void
	{
		if ($this->_meta !== null) {
			return;
		}

		if ($this->_post) {
			$this->_meta = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY)[0] ?? [] ?: [];
			// as fallback load status from meta field
			$this->_archive = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_ARCHIVE) ?? [] ?: [];
			$this->_orderNum = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_ORDERNUM, true) ?: null;
			$this->_status = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_STATUS, true) ?: MwsOrderStatus::Ordered;
			$this->_opened = !get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_NOTOPENED, true);
			$this->_isTest = (bool) (get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_IS_TEST, true) ?: false);
			$this->_gateId = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_GATE_ID, true) ?: null;
			$this->_currency = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_CURRENCY, true) ?: null;
			$this->_note = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_NOTE, true) ?: null;
			$this->_trackingNumber = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_TRACKING_NUMBER, true) ?: null;
			$sourceType = get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_SOURCE_TYPE, true) ?: null;
			$this->_source = $sourceType !== null ? new MwsOrderSource(
				$sourceType,
				get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_SOURCE_PAGE_ID, true) ?: null,
				get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_SOURCE_URL, true) ?: null,
				get_post_meta($this->_post->ID, MWS_ORDER_META_KEY_SOURCE_FORM_ID, true) ?: null,
			) : null;
		} else {
			$this->_meta = [];
		}
	}

	public function setPaid(bool $paid = true): void
	{
		$this->_meta['isPaid'] = $paid;
		if ($paid) {
			$this->addHistory('Označeno za zaplaceno', MwsOrderEvent::OrderSetPaid);
			$this->processAutomations(MwsAutomationEvent::OnPaid);
		} else {
			$this->addHistory('Označeno za nezaplaceno', MwsOrderEvent::OrderSetUnpaid);
		}
	}

	public function setPaidOn(?int $paidOn): void
	{
		$this->_meta['paidOn'] = $paidOn;
	}

	public function setSendPaymentFailedNotification(bool $sent = true): void
	{
		$this->_meta['payment_failed_notification_sent'] = $sent;
	}

	/** Sends payment failure notification e-mail if not already sent before. */
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

	public function sendInvoiceToCustomer(MwsOrderGateDocument $document, string $emailType = MwsEmailType::PayedOrder)
	{
		$document->sendToCustomer($emailType);
		$this->addHistory('Faktura odeslána', MwsOrderEvent::InvoiceMailSend);
	}

	// check payments

	/** @throws OrderHasNoHashException */
	public function getHash(): string
	{
		if (!isset($this->_meta['hash'])) {
			throw new OrderHasNoHashException();
		}

		return $this->_meta['hash'];
	}

	public function getBaseUrl(array $query = []): string
	{
		$url = get_permalink($this->_post->ID);
		$query['hash'] = $this->getHash();

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

	/** Save temporary states in memory into {@link $_meta} field. */
	private function updateMeta()
	{
		$this->load();
		$this->_meta['items'] = $this->getItems()->toArray();
	}

	/**
	 * Save in-memory state of order. If order is not bound with a post (meaning {@link _post} property is empty) then
	 * it creates new post of custom post type {@link MWS_ORDER_SLUG}.
	 */
	public function save(): bool
	{
		$this->updateMeta();
		if (!$this->_post) {
			// Create new order.
			$orderNum = $this->getNumber();
			$args = [
				'post_title' => sprintf(__('Objednávka č. %s', 'mwshop'), $orderNum),
				'post_status' => 'publish',
				'post_type' => MWS_ORDER_SLUG,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_name' => sanitize_title(__('objednávka', 'mwshop') . sprintf('_%s', $orderNum)),
				'meta_input' => [
					MWS_ORDER_META_KEY => $this->_meta,
					MWS_ORDER_META_KEY_ORDERNUM => $orderNum,
					MWS_ORDER_META_KEY_STATUS => $this->_status,
					MWS_ORDER_META_KEY_NOTOPENED => 1,
					MWS_ORDER_META_KEY_IS_TEST => $this->_isTest,
					MWS_ORDER_META_KEY_GATE_ID => $this->_gateId,
					MWS_ORDER_META_KEY_CURRENCY => $this->_currency,
					MWS_ORDER_META_KEY_NOTE => $this->_note,
					MWS_ORDER_META_KEY_TRACKING_NUMBER => $this->_trackingNumber,
					MWS_ORDER_META_KEY_SOURCE_TYPE => $this->_source !== null ? $this->_source->getType() : null,
					MWS_ORDER_META_KEY_SOURCE_FORM_ID => $this->_source !== null ? $this->_source->getFormId() : null,
					MWS_ORDER_META_KEY_SOURCE_PAGE_ID => $this->_source !== null ? $this->_source->getPageId() : null,
					MWS_ORDER_META_KEY_SOURCE_URL => $this->_source !== null ? $this->_source->getUrl() : null,
				],
			];
			$postId = wp_insert_post($args, false);
			if ($postId) {
				$this->_post = get_post($postId);
				$this->_meta = null;
				$this->load();
			} else {
				mwshoplog('New order could not be saved into database.', MWLL_ERROR, 'order');

				return false;
			}
		} else {
			update_post_meta($this->_post->ID, MWS_ORDER_META_KEY, $this->_meta);
			update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_ORDERNUM, $this->getNumber());
			update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_STATUS, $this->_status);
			update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_IS_TEST, $this->_isTest);
			update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_GATE_ID, $this->getGateIdentifier());
			update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_CURRENCY, $this->getCurrency());
			update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_NOTE, $this->getNote());
			update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_TRACKING_NUMBER, $this->getTrackingNumber());

			if ($this->_source !== null) {
				update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_SOURCE_TYPE, $this->_source->getType());
				update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_SOURCE_FORM_ID, $this->_source->getFormId());
				update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_SOURCE_PAGE_ID, $this->_source->getPageId());
				update_post_meta($this->_post->ID, MWS_ORDER_META_KEY_SOURCE_URL, $this->_source->getUrl());
			}

			foreach (Document::getAllByOldOrderId($this->getId()) as $document) {
				$document->setCustomerContact($this->getInvoiceContact());
				$document->setShippingContact($this->getShippingContact());
				$document->save();
			}
		}

		return true;
	}

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 */
	public static function createNew(WP_Post $post, bool $useCache = true): ?self
	{
		if (get_post_type($post) != MWS_ORDER_SLUG) {
			throw new MwsException('Passed post is not of order type.');
		}

		if ($useCache) {
			//Is created already?
			$obj = MwObjectCache::get(self::class, $post->ID);
			if (!$obj) {
				$obj = new self($post);
				MwObjectCache::add($obj, $obj->getId());
			}

			return $obj;
		}

		return new self($post);
	}

	/**
	 * Get order instance by order ID.
	 */
	public static function getOneById(int $orderId): ?self
	{
		$post = get_post($orderId);
		if ($post) {
			try {
				return static::createNew($post);
			} catch (MwsException $e) {
				mwshoplog(
					sprintf(__('Nepodařilo se vytvořit instanci objednávky [%d] se zprávou: %s', 'mwshop'), $orderId, $e->getMessage()),
					MWLL_ERROR
				);
			}
		}

		return null;
	}

	/**
	 * @TODO what if multiple orders with same number
	 * Get order by its order number.
	 */
	public static function getOrderByOrderNum(string $orderNum): ?MwsOrder
	{
		if (!$orderNum) {
			return null;
		}

		$args = [
			'meta_key' => MWS_ORDER_META_KEY_ORDERNUM,
			'meta_value' => $orderNum,
			'post_type' => MWS_ORDER_SLUG,
			'post_status' => 'any',
			'posts_per_page' => -1,
		];
		$posts = get_posts($args);

		if (count($posts) > 0) {
			try {
				return static::createNew($posts[0]);
			} catch (Exception $e) {
			}
		}

		return null;
	}

	/** @return MwsOrder[] */
	public static function getAllOrders(int $limit = -1, int $page = 1): array
	{
		$args = [
			'post_type' => MWS_ORDER_SLUG,
			'post_status' => 'any',
			'posts_per_page' => $limit,
			'paged' => $page,
			'order' => 'ASC',
		];
		$posts = get_posts($args);

		return array_map(function (\WP_Post $post): self {
			return self::createNew($post);
		}, $posts);
	}

	public function isArchived(): bool
	{
		return (bool) metadata_exists('post', $this->getId(), MWS_ORDER_META_KEY_ARCHIVE);
	}

	public function getArchivedDate(): ?string
	{
		return get_post_meta($this->getId(), MWS_ORDER_META_KEY_ARCHIVE, true);
	}


}

/**
 * Group of backlinks from ordered items to products in shop.
 */
class MwsOrderItems
{

	private $_order;

	private $_data = [];

	public function __construct(MwsOrder $order)
	{
		$this->_order = $order;
	}

	public function getOrder(): MwsOrder
	{
		return $this->_order;
	}

	/** @return MwsOrderItem[] */
	public function getAll(): array
	{
		return $this->_data;
	}

	public function getProducts(): array
	{
		$products = [];
		foreach ($this->_data as $item) {
			if ($item->isProduct()) {
				$products[] = $item;
			}
		}

		return $products;
	}

	/**
	 * Add new ordered item.
	 */
	public function add(MwsOrderItem $item): void
	{
		$this->_data[] = $item;
	}

	public function toArray(): array
	{
		return array_map(function (MwsOrderItem $item) {
			return $item->toArray();
		}, $this->getAll());
	}


}

/**
 * One item of an order. Can provide direct access to product through properties.
 */
class MwsOrderItem
{

	private string $_name;

	private ?string $_type;

	private int $_count;

	/** @var MwsPrice[] */
	private array $_prices;

	private ?MwsProductCodes $_codes;

	private ?int $_productId;

	private bool $_ossApplied;

	private bool $_miniupsell;

	private ?float $_weight;

	public function __construct(string $name, ?string $type, array $prices, int $count = 1, ?MwsProductCodes $codes = null, ?int $productId = null, bool $ossApplied = false, bool $miniupsell = false, ?float $weight = null)
	{
		$this->_name = $name;
		$this->_type = $type;
		$this->_prices = $prices;
		$this->_count = $count;
		$this->_codes = $codes;
		$this->_productId = $productId;
		$this->_ossApplied = $ossApplied;
		$this->_miniupsell = $miniupsell;
		$this->_weight = $weight;
	}

	public function getId(): ?int
	{
		return $this->_productId;
	}

	public function isProduct(): bool
	{
		return MwsOrderItemType::isActualProduct($this->_type);
	}

	public function getName(): string
	{
		return $this->_name;
	}

	/** Can be NULL for old orders created in 3.0 */
	public function getType(): ?string
	{
		return $this->_type;
	}


	/** @return MwsPrice[] */
	public function getPrices(): array
	{
		return $this->_prices;
	}

	public function pricesToArray(): array
	{
		$return = [];
		foreach ($this->_prices as $currency => $price) {
			$return[$currency] = $price->toArray();
		}

		return $return;
	}

	public function getPrice($currency): ?MwsPrice
	{
		return $this->_prices[$currency] ?? null;
	}

	public function changeVat(int $vatPercentage): void
	{
		foreach ($this->_prices as $currency => $price) {
			$price->changeVat($vatPercentage);
		}
	}
	public function removeVat(): void
	{
		foreach ($this->_prices as $currency => $price) {
			$price->removeVat();
		}
	}

	public function getTotalPrice($currency): MwsPrice
	{
		return $this->getPrice($currency)->multiply($this->getCount());
	}

	public function getCount(): int
	{
		return $this->_count;
	}

	public function getCodes(): ?MwsProductCodes
	{
		return $this->_codes;
	}

	public function getProduct(): ?MwsProduct
	{
		return $this->_productId ? MwsProduct::getOneById($this->_productId) : null;
	}

	public function isOssApplied(): bool
	{
		return $this->_ossApplied;
	}

	public function setOssApplied(bool $ossApplied = true): void
	{
		$this->_ossApplied = $ossApplied;
	}

	public function getWeight(): ?float
	{
		return $this->_weight;
	}

	public function setWeight(float $weight): void
	{
		$this->_weight = $weight;
	}

	/** @return mixed[] */
	public function toArray(): array
	{
		$array = [
			'name' => $this->getName(),
			'type' => $this->getType(),
			'prices' => $this->pricesToArray(),
			'count' => $this->getCount(),
			'codes' => ($codes = $this->getCodes()) ? $codes->toArray() : null,
			'productId' => $this->_productId,
			'weight' => $this->getWeight(),
		];
		if ($this->isOssApplied()) {
			$array['ossApplied'] = true;
		}
		if ($this->isMiniupsell()) {
			$array['miniupsell'] = true;
		}

		return $array;
	}

	public static function createByArray(array $values): self
	{
		return new self(
			$values['name'] ?? ($values['title'] ?? ''),
			$values['type'] ?? '',
			array_map(function (array $item) {
				return MwsPrice::createByArray($item);
			}, $values['prices'] ?? []),
			$values['count'],
			isset($values['codes']) ? new MwsProductCodes($values['codes']) : null,
			$values['productId'] ?? null,
			$values['ossApplied'] ?? false,
			(bool) ($values['miniupsell'] ?? false),
			isset($values['weight']) ? (float) $values['weight'] : null
		);
	}

	public function isMiniupsell(): bool
	{
		return $this->_miniupsell;
	}
}

/**
 * @deprecated
 * Live connector to the order at the gateway. Works as caching wrapper object for order, where its data is loaded
 * directly from the gateway.
 */
abstract class MwsOrderGate implements IOrderGate
{

	/** @var MwsOrder */
	protected $_order;

	/** @var MwsPrice */
	private $_price = null;

	private $_nativePrice = null;

	private $_currency = null;

	private ?MwsBankAccount $_bankAccount = null;

	private $_isPaid = null;

	private $_paidOn = null;

	private $gw = null;

	public function __construct(MwsOrder $order)
	{
		$this->_order = $order;
	}

	public function getPrice(): MwsPrice
	{
		if (!$this->_price) {
			$this->_price = $this->doGetPrice();
		}

		return $this->_price;
	}

	public function getNativePrice(): MwsPrice
	{
		if (!$this->_nativePrice) {
			$this->_nativePrice = $this->doGetNativePrice();
		}

		return $this->_nativePrice;
	}

	public function getCurrency(): string
	{
		if (!$this->_currency) {
			$this->_currency = $this->doGetCurrency();
		}

		return $this->_currency;
	}

	public function getBankAccount(string $currency): ?MwsBankAccount
	{
		if (!$this->_bankAccount) {
			$this->_bankAccount = $this->doGetBankAccount($currency);
		}

		return $this->_bankAccount;
	}

	public function isPaid(): bool
	{
		if ($this->_isPaid === null) {
			$this->_isPaid = $this->doIsPaid();
		}

		return $this->_isPaid;
	}

	public function getPaidOn(): ?int
	{
		if ($this->_paidOn === null) {
			$this->_paidOn = $this->doGetPaidOn() ?: false;
		}

		return $this->_paidOn ?: null;
	}

	/** Get associated gateway instance. */
	protected function getGateway(): ?MwsGatewayMeta
	{
		if ($this->gw === null) {
			$this->gw = MWS()->gateways()->getById($this->_order->getGateIdentifier());
		}

		return $this->gw;
	}

	/**
	 * Get information about the ordering person.
	 *
	 * @param bool $short Set to <code>true</code> to output short version of the contact, e.g. like a title.
	 * @return string If none is present then empty string is returned.
	 */
	public function formatInvoiceContact(bool $short = false): string
	{
		return $this->getInvoiceContact()->format($short);
	}

	/**
	 * Get shipping contact.
	 */
	public function formatShippingContact(): string
	{
		return ($contact = $this->getShippingContact()) ? $contact->format(true, true) : '';
	}

	public function getCreateInvoiceLink(): string
	{
		return '';
	}

	/**
	 * Get contact edit buttons for WP administration.
	 *
	 * @return string If none is present then empty string is returned.
	 */
	abstract public function formatContactEditing(): string;

	public function setInvoiceContact(MwsContact $contact): void
	{
		throw new MwsException('Not implemented.');
	}

	public function setShippingContact(?MwsContact $contact): void
	{
		throw new MwsException('Not implemented.');
	}

	public function createInvoice(): MwsOrderGateDocument
	{
		throw new MwsException('Not implemented.');
	}

	abstract public function sendSummary(): void;

	/**
	 * Get items of the order.
	 *
	 * @return MwsOrderItem[]
	 */
	abstract public function getItems(): array;

	/**
	 * Get documents of the order.
	 *
	 * @return MwsOrderGateDocument[]
	 */
	abstract public function getDocuments(): array;

	abstract public function getCustomer(): MwsCustomer;

	abstract public function getInvoiceContact(): MwsContact;

	abstract public function getSupplier(): ?MwsContact;

	abstract public function getShippingContact(): ?MwsContact;

	/**
	 * Ancestor loads real price.
	 */
	abstract protected function doGetPrice(): MwsPrice;

	abstract protected function doGetNativePrice(): MwsPrice;

	/**
	 * Ancestor loads real price.
	 */
	abstract protected function doGetCurrency(): string;

	abstract protected function doGetBankAccount(string $currency): ?MwsBankAccount;

	/**
	 * Ancestor load real status of payments.
	 */
	abstract protected function doIsPaid(): bool;

	/**
	 * Ancestor load real time of payment as Unix timestamp in UTC.
	 */
	abstract protected function doGetPaidOn(): ?int;



}

interface MwsOrderGateDocument
{

	public function getName(): string;

	public function getCreatedAt(): DateTimeInterface;

	public function getDueDate(): DateTimeInterface;

	public function getTaxableSupplyAt(): ?DateTimeInterface;

	public function getDownloadUrl(): string;

	public function getDetailUrl(): ?string;

	public function getEditUrl(): ?string;

	/**
	 * Total price
	 */
	public function getPrice(): MwsPrice;

	public function isPaid(): bool;

	public function sendToCustomer(string $emailType = MwsEmailType::PayedOrder): void;



}
