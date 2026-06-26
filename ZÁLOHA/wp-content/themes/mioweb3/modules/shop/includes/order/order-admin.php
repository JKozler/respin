<?php

use Mioweb\Admin\mwObjectExport;
use Mioweb\Shop\Document\Document;
use Mioweb\Shop\Order\Exporters\IOrderExporter;
use Mioweb\Shop\Order\Exporters\OrderExporterContainer;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\MwsOrderFetchRequest;
use Mioweb\Library\Api\MPohoda\MPohodaIssuer;
use Mioweb\Shop\Order\OrderRepository;

class MwsOrderAdmin
{

	const ORDER_PAGE_LIMIT = 500;

	public function __construct()
	{
		add_action('wp_ajax_mwsOpenOrderClientForm', [$this, 'openOrderClientForm_ajax']);
		add_action('wp_ajax_mwsSaveOrderClientForm', [$this, 'saveOrderClientForm_ajax']);

		add_action('wp_ajax_mwsOrderChangeStatus', [$this, 'changeOrderStatus_ajax']);
		add_action('wp_ajax_mwsChangeOrderStatusForm', [$this, 'changeOrderStatusForm_ajax']);

		add_action('wp_ajax_mwsSaveOrderNote', [$this, 'saveOrderNote_ajax']);

		add_action('wp_ajax_mwsChangePaidStatusForm', [$this, 'changePaidStatusForm_ajax']);
		add_action('wp_ajax_mwsOrderChangePaidStatus', [$this, 'changePaidStatus_ajax']);

		add_action('wp_ajax_mwsOrderCreateInvoice', [$this, 'createInvoice_ajax']);
		add_action('wp_ajax_mwsOrderSendInvoice', [$this, 'sendInvoice_ajax']);

		add_action('wp_ajax_mwsDocumentIssueMPohoda', [$this, 'issueMPohoda_ajax']);
	}

	/** @return Order[] */
	public static function getOrders(MwsOrderFetchRequest $request, bool $preload = true, bool $paged = true): array
	{
		$orders = OrderRepository::findByRequest($request);
		$ordersCnt = OrderRepository::countByRequest($request);

		if ($orders && $preload) {
			MWS()->gateways()->preloadOrdersGateLive(...$orders);
		}

		return $paged ? [
			'items' => $orders,
			'pages' => ceil($ordersCnt / $request->getLimit()),
			'count' => $ordersCnt,
		] : $orders;
	}

	/** @return Generator<Order> */
	public static function getOrdersGenerator(MwsOrderFetchRequest $request): \Generator
	{
		$toProcessNum = $request->getLimit();
		if ($toProcessNum === -1) {
			$toProcessNum = null;
		}

		$processedNum = $request->getOffset();

		$baseSelection = $request->buildQuery(OrderRepository::getTable());

		do {
			$tmpNum = $toProcessNum !== null ? min($toProcessNum, self::ORDER_PAGE_LIMIT) : self::ORDER_PAGE_LIMIT;
			$selection = clone $baseSelection;
			$selection->limit($tmpNum, $processedNum);

			$orders = $selection->fetchAll();
			foreach ($orders as $row) {
				$order = OrderRepository::createNew($row, false);

				yield $order;
			}

			$orderCount = count($orders);

			if ($toProcessNum !== null) {
				$toProcessNum -= $tmpNum;
			}

			$processedNum += $tmpNum;
			// TODO #3642 Test with some bigdata
		} while ($toProcessNum > 0 || ($toProcessNum === null && $orderCount > 0));
	}

	public static function getNewOrdersCount(): int
	{
		return OrderRepository::countBy([
			'is_opened' => false,
		]);
	}

	public static function orderStatus($orderStatus, $post_id, $isArchived = null)
	{
		switch ($orderStatus) {
			case MwsOrderStatus::Ordered:
				$status = 'non';
				$link = '<a class="mws_order_change_status mw_setting_action_link" href="#" data-set="' . MwsOrderStatus::Closed . '" data-id="' . $post_id . '" data-title="">' . __('Vyřídit objednávku', 'mwshop') . '</a>';

				break;
			case MwsOrderStatus::Processing:
				$status = 'processing';
				$link = '<a class="mws_order_change_status mw_setting_action_link" href="#" data-set="' . MwsOrderStatus::Closed . '" data-id="' . $post_id . '" data-title="">' . __('Vyřídit objednávku', 'mwshop') . '</a>';

				break;
			case MwsOrderStatus::Closed:
				$status = 'ok';
				$link = '<a class="mws_order_change_status mw_setting_action_link" href="#" data-set="' . MwsOrderStatus::Closed . '" data-id="' . $post_id . '" data-title="">' . __('Upravit', 'mwshop') . '</a>';

				break;
			case MwsOrderStatus::Cancelled:
				$status = 'fail';
				$link = '';

				break;
			default:
				$status = '';
				$link = '';

				break;
		}

		if ($isArchived) {
			$link = null;
			$list = null;
		} else {
			$list = [];
			foreach (MwsOrderStatus::getAll() as $item) {
				if ($item != $orderStatus) {
					$list[] = [
						'text' => MwsOrderStatus::getCaption($item),
						'class' => 'mws_order_change_status',
						'attrs' => 'data-set="' . $item . '" data-id="' . $post_id . '"  data-title=""',
					];
				}
			}
		}


		$statusText = MwsOrderStatus::getCaption($orderStatus);

		return mwAdminComponents::statusField([
			'title' => __('Stav', 'mwshop'),
			'link' => $link,
			'text' => $statusText,
			'status' => $status,
			'list' => $list,
		], 'mws_order_status');
	}

	public static function payedStatus($isPaid, $post_id, $isArchived = null)
	{
			if ($isPaid) {
			$status = 'ok';
			$statusText = __('Zaplaceno', 'mwshop');
			$link = '<a class="mws_order_change_payed_status mw_setting_action_link" href="#" data-set="1" data-id="' . $post_id . '">' . __('Upravit', 'mwshop') . '</a>';
			$list = [
			[
			'text' => __('Nezaplaceno', 'mwshop'),
			'class' => 'mws_order_change_payed_status',
			'attrs' => 'data-set="0" data-id="' . $post_id . '"',
			],
			];
			} else {
			$status = 'non';
			$statusText = __('Nezaplaceno', 'mwshop');
			$link = '<a class="mws_order_change_payed_status mw_setting_action_link" href="#" data-set="1" data-id="' . $post_id . '">' . __('Označit jako zaplaceno', 'mwshop') . '</a>';
			$list = [
			[
			'text' => __('Zaplaceno', 'mwshop'),
			'class' => 'mws_order_change_payed_status',
			'attrs' => 'data-set="1" data-id="' . $post_id . '"',
			],
			];
			}

		 if ($isArchived) {
			$link = null;
			$list = null;
		 }

		return mwAdminComponents::statusField([
			'title' => __('Platba', 'mwshop'),
			'link' => $link,
			'text' => $statusText,
			'status' => $status,
			'list' => $list,
		], 'mws_order_payed_status');
	}

	public static function printOrderCustomerInfo(Order $order)
	{
		$orderLive = $order->getGateLive();
		$invoiceContact = $orderLive->getInvoiceContact();

			echo mwAdminComponents::title([
				'text' => __('Kontakt na zákazníka', 'mwshop') ,
				'onright' => $order->isArchived() ? '' : $orderLive->formatContactEditing(),
			]);



		echo '<table class="mws_order_customer_info_contact">';
		echo '<tr>';
		echo '<td class="label">' . __('Email:') . '</td>';
		echo '<td>' . $invoiceContact->getEmail() . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="label">' . __('Telefon:') . '</td>';
		echo '<td>' . $invoiceContact->getPhone() . '</td>';
		echo '</tr>';
		echo '</table>';

		echo '<div class="mws_order_customer_info_address">';

		echo '<div class="mws_order_customer_info_address_col">'
		. mwAdminComponents::title(['text' => __('Fakturační adresa', 'mwshop')])
		. $orderLive->formatInvoiceContact(true)
		. '</div>';

		$s = $orderLive->formatShippingContact();
		if ($s) {
			echo '<div class="mws_order_customer_info_address_col">'
			. mwAdminComponents::title(['text' => __('Dodací adresa', 'mwshop')])
			. $s
			. '</div>';
		}

		echo '</div>';
	}

	function openOrderClientForm_ajax()
	{
		$order = OrderRepository::getOneById($_POST['postid']);
		$orderLive = $order->getGateLive();

		$invoiceContact = $orderLive->getInvoiceContact();

		echo '<div class="mws_edit_order_customer_form">';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Email', 'mwshop'),
			'name' => 'email',
		], $invoiceContact->getEmail());
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Telefon', 'mwshop'),
			'name' => 'phone',
		], $invoiceContact->getPhone());
		echo '</div>';

		echo mwAdminComponents::title([
			'text' => __('Fakturační adresa', 'mwshop'),
		]);

		$company = $invoiceContact->getCompany();

		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Název firmy', 'mwshop'),
			'name' => 'company',
		], $company ? $company->getName() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('IČ', 'mwshop'),
			'name' => 'company_id',
		], $company ? $company->getId() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('DIČ', 'mwshop'),
			'name' => 'company_vat_id',
		], $company ? $company->getTaxId() : '');
		echo '</div>';

		$person = $invoiceContact->getPerson();

		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Jméno', 'mwshop'),
			'name' => 'firstname',
		], $person ? $person->getFirstName() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Příjmení', 'mwshop'),
			'name' => 'lastname',
		], $person ? $person->getLastName() : '');
		echo '</div>';

		$address = $invoiceContact->getAddress();

		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Ulice a čp.', 'mwshop'),
			'name' => 'street',
		], $address ? $address->getStreet() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Město', 'mwshop'),
			'name' => 'city',
		], $address ? $address->getCity() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('PSČ', 'mwshop'),
			'name' => 'zip',
		], $address ? $address->getZip() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo '<div class="label">' . __('Země', 'mwshop') . '</div>';
		echo MwShopFields::countrySelect($address ? $address->getCountry() : '', 'country');
		echo '</div>';

		$shippingContact = $orderLive->getShippingContact();
		echo mwAdminComponents::title([
			'text' => __('Dodací adresa', 'mwshop'),
		]);
		$person = $shippingContact ? $shippingContact->getPerson() : null;

		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Jméno', 'mwshop'),
			'name' => 'shipping_firstname',
		], $person ? $person->getFirstName() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Příjmení', 'mwshop'),
			'name' => 'shipping_lastname',
		], $person ? $person->getLastName() : '');
		echo '</div>';

		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Telefon', 'mwshop'),
			'name' => 'shipping_phone',
		], $shippingContact ? $shippingContact->getPhone() : '');
		echo '</div>';

		$address = $shippingContact ? $shippingContact->getAddress() : null;

		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Ulice a čp.', 'mwshop'),
			'name' => 'shipping_street',
		], $address ? $address->getStreet() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('Město', 'mwshop'),
			'name' => 'shipping_city',
		], $address ? $address->getCity() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo mwAdminComponents::input([
			'label' => __('PSČ', 'mwshop'),
			'name' => 'shipping_zip',
		], $address ? $address->getZip() : '');
		echo '</div>';
		echo '<div class="set_form_twocolrow">';
		echo '<div class="label">' . __('Země', 'mwshop') . '</div>';
		echo MwShopFields::countrySelect($address ? $address->getCountry() : '', 'shipping_country', '', ['allow_empty' => true]);
		echo '</div>';

		die();
	}

	public function saveOrderClientForm_ajax()
	{
		$postId = $_POST['postid'];
		$order = OrderRepository::getOneById($postId);

		$order->setInvoiceContact(new MwsContact(
			$_POST['email'],
			$_POST['phone'] ?: null,
			$_POST['firstname'] || $_POST['lastname'] ? new MwsPerson(
				$_POST['firstname'],
				$_POST['lastname']
			) : null,
			$_POST['company'] || $_POST['company_id'] || $_POST['company_vat_id'] ? new MwsCompany(
				$_POST['company'],
				$_POST['company_id'],
				$_POST['company_vat_id']
			) : null,
			new MwsAddress(
				$_POST['country'],
				$_POST['city'],
				$_POST['zip'],
				$_POST['street']
			)
		));

		// @TODO better detect and validation
		$order->setShippingContact($_POST['shipping_city'] ? new MwsContact(
			'',
			$_POST['shipping_phone'] ?: null,
			$_POST['shipping_firstname'] || $_POST['shipping_lastname'] ? new MwsPerson(
				$_POST['shipping_firstname'],
				$_POST['shipping_lastname']
			) : null,
			null,
			new MwsAddress(
				$_POST['shipping_country'],
				$_POST['shipping_city'],
				$_POST['shipping_zip'],
				$_POST['shipping_street']
			)
		) : null);

		$order->addHistory('Údaje o klientovi byly upraveny.', MwsOrderEvent::CustomerEdited);
		$order->save();

		self::printOrderCustomerInfo($order);

		die();
	}

	/**
	 * AJAX
	 */

	// change order status
	public function changeOrderStatus_ajax()
	{
		$newStatus = $_POST['setStatus'];
		$orderId = (int) $_POST['postid'];

		$order = OrderRepository::getOneById($orderId);
		if (!$order) {
			exit;
		}

		if (isset($_POST['tracking_number']) && $_POST['tracking_number']) {
			$order->setTrackingNumber(str_replace(' ', '', $_POST['tracking_number']));
			$order->save();
		}

		$order->changeStatus($newStatus, (bool) ($_POST['send_info'] ?? false));

		echo self::orderStatus($newStatus, $orderId);
		die();
	}

	public function changePaidStatus_ajax()
	{
		$setStatus = $_POST['setStatus'];
		$orderId = (int) $_POST['postid'];
		$paidDate = isset($_POST['paid_date']) && $_POST['paid_date'] ? $_POST['paid_date'] : date('Y-m-d', current_time('timestamp'));

		$order = OrderRepository::getOneById($orderId);
		if (!$order) {
			exit;
		}
		// @TODO what if set as unpaid?
		if ($setStatus) {
			if (!$order->isPaid()) {
				$order->setPaid();
				$order->setPaidAt(new \DateTimeImmutable($paidDate));
			}
		} else {
			$order->setPaid(false);
			$order->setPaidAt(null);
		}
		$order->save();

		// create only if not exist
		if (($_POST['create_invoice'] ?? false) && !$order->getGateLive()->getDocuments()) {
			$order->createInvoice();
		}
		if (($_POST['send_invoice'] ?? false) && ($documents = $order->getGateLive()->getDocuments())) {
			$order->sendInvoiceToCustomer(end($documents));
		}

		wp_send_json([
			'status_html' => self::payedStatus($setStatus, $orderId, false),
			'invoice_list_html' => self::invoicesList($order),
			'paid_date' => $order->getPaidAtDateFormatted(),
		]);

		die();
	}

	public function createInvoice_ajax()
	{
		$orderId = (int) $_POST['orderId'];

		$order = OrderRepository::getOneById($orderId);
		if (!$order) {
			exit;
		}

		if (!$order->getGateLive()->getDocuments()) {
			$invoice = $order->createInvoice();
		}

		wp_send_json([
			'invoice_list_html' => self::invoicesList($order),
		]);
		die();
	}

	public function sendInvoice_ajax()
	{
		$orderId = (int) $_POST['orderId'];

		$order = OrderRepository::getOneById($orderId);
		if (!$order) {
			exit;
		}

		$documents = $order->getGateLive()->getDocuments();

		if ($documents) {
			$order->sendInvoiceToCustomer(end($documents), MwsEmailType::SentInvoice);
		}

		wp_send_json([
			'invoice_list_html' => self::invoicesList($order),
		]);

		die();
	}

	public function issueMPohoda_ajax(): void
	{
		$id = (int) $_POST['documentId'];

		$document = Document::getOneById($id);
		if ($document === null) {
			exit;
		}

		MPohodaIssuer::getInstance()->issue($document);

		wp_send_json([
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
			'mpohoda_html' => self::getMPohodaRowHtml($document),
		]);

		die();
	}

	public static function getMPohodaRowHtml(Document $document): string
	{
		$mPohodaId = $document->getMPohodaId();

		$content = '<span class="mws_document_mpohoda_container">';
		if ($mPohodaId !== null) {
			// TODO #3401 Replace URL when stable mPOHODA is released
			$content .= '<a target="_blank" rel="nofollow" class="mw_setting_action_link" href="https://beta.mpohoda.cz/faktury/' . $document->getMPohodaId() . '">Zobrazit</a>';
		} else {
			$content .= '<a class="mws_document_issue_mpohoda mw_setting_action_link" href="#" data-id="' . $document->getId() . '">Vystavit</a>';
		}

		$content .= '</span>';

		return $content;
	}

	static function invoicesList(Order $order): string
	{
		$orderLive = $order->getGateLive();

		$content = '';

		if (!$orderLive) {
			$content .= mwAdminComponents::messageBox(sprintf(__('Nepodařilo se načíst data z platební brány [%s].', 'mwshop'), $order->getGate()->getIdentifier()), ['type' => 'error']);
		} else {
				$content .= $orderLive->printOrderInvoiceInfo();
		}

		return $content;
	}

	function changeOrderStatusForm_ajax()
	{
		echo '<h3>' . __('Vyřízení objednávky', 'mwshop') . '</h3>';

		$postId = $_POST['postid'];
		$order = OrderRepository::getOneById($postId);
		$hasPhysicalProduct = $this->hasPhysicalProduct($order);
		$shippingType = $order->getShipping()['type'] ?? null;

		$switch_label = $shippingType === MwsShippingType::Personal
			? __('Poslat zákazníkovi informaci o tom, že je zboží připraveno k vyzvednutí')
			: __('Poslat zákazníkovi potvrzení o odeslání objednávky', 'mwshop');

		if ($order->getStatus() == MwsOrderStatus::Closed) {
			$closedTimestamp = $order->getLastHistoryTimestamp(MwsOrderEvent::OrderStatusChangeToClosed);
			if ($closedTimestamp) {
				echo '<p class="sb">' . __('Tato objednávka byla vyřízena dne:', 'mwshop') . ' ' . mwPrintDate($closedTimestamp, 'datetime', true) . '</p>';
			}
			if ($hasPhysicalProduct) {
				$closeMailSendTimestamp = $order->getLastHistoryTimestamp(MwsOrderEvent::OrderCloseMailSend);
				if ($closeMailSendTimestamp) {
					$switch_label .= '<small>(' . __('Potvrzení o odeslání objednávky zasláno dne', 'mwshop') . ' ' . mwPrintDate($closeMailSendTimestamp, 'datetime', true) . ')</small>';
				}
			}
		} else {
			echo '<p class="sb">' . (
				$hasPhysicalProduct
					? __('Zde můžete zákazníkovi potvrdit odeslání objednávky', 'mwshop')
					: __('Objednávka neobsahuje žádný fyzický produkt. Vyřízením se tedy objednávka pouze přesune do příslušného stavu a označí zeleně.', 'mwshop')
				) . '</p>';
		}

		if ($shippingType === MwsShippingType::Custom && $order->getTrackingUrl()) {
			$trackingNumber = $order->getTrackingNumber() ?? '';

			echo '<table class="mw_table mws_order_popup_form_table mws_order_popup_form_table_bottom_margin">';
			echo '<tr><td>' . __('Číslo zásilky', 'mwshop')
				. mwAdminComponents::tooltip(['text' => 'Zde zadejte číslo zásilky, které se použije k vytvoření odkazu pro sledování zásilky.'])
				. '</td><td>'
				. mwAdminComponents::input(['name' => 'tracking_number'], $trackingNumber)
				. '</td></tr>';
			echo '</table>';
		}

		if ($hasPhysicalProduct) {
			echo mwAdminComponents::switch([
				'switch_label' => $switch_label,
				'name' => 'send_info',
			], 0);
		}

		die();
	}

	function changePaidStatusForm_ajax()
	{
		echo '<h3>' . __('Nastavení zaplacení objednávky', 'mwshop') . '</h3>';


		$postId = $_POST['postid'];
		$order = OrderRepository::getOneById($postId);

		$invoiceCreatedTimestamp = $order->getLastHistoryTimestamp(MwsOrderEvent::InvoiceCreated);

		$switch_label = __('Poslat zákazníkovi fakturu emailem', 'mwshop');

		if ($order->isPaid()) {
			$paidTimestamp = $order->getLastHistoryTimestamp(MwsOrderEvent::OrderSetPaid);
			if ($paidTimestamp) {
				echo '<p class="sb">' . __('Tato objednávka byla označena za zaplacenou:', 'mwshop') . ' ' . mwPrintDate($paidTimestamp, 'date', true) . '</p>';
			}

			$invoiceMailSendTimestamp = $order->getLastHistoryTimestamp(MwsOrderEvent::InvoiceMailSend);
			if ($invoiceMailSendTimestamp) {
				$switch_label .= '<small>(' . __('Faktura byla klientovi zaslána dne:', 'mwshop') . ' ' . mwPrintDate($invoiceMailSendTimestamp, 'datetime', true) . ')</small>';
			}
		} else {
			echo '<p class="sb">' . __('Zde můžete vystavit a odeslat zákazníkovi fakturu.', 'mwshop') . '</p>';
			echo '<table class="mw_table mws_order_popup_form_table mws_order_popup_form_table_bottom_margin">';
			echo '<tr><td>' . __('Datum zaplacení', 'mwshop') . ':</td><td>' . mwAdminComponents::input([
				'name' => 'paid_date',
				'type' => 'date',
				'attrs' => 'max="' . date('Y-m-d', current_time('timestamp')) . '"',
			], date('Y-m-d', current_time('timestamp'))) . '</td></tr>';
			echo '</table>';
		}

		if (!$invoiceCreatedTimestamp) {
			echo mwAdminComponents::switch([
				'switch_label' => __('Vystavit fakturu', 'mwshop'),
				'name' => 'create_invoice',
			], 0, 'mws_create_invoice_switch');
		}
		echo mwAdminComponents::switch([
			'switch_label' => $switch_label,
			'name' => 'send_invoice',
		], 0, 'mws_send_invoice_switch ' . ($invoiceCreatedTimestamp ? '' : 'cms_nodisp'));

		die();
	}

	public static function orderNote(Order $order): string
	{
		$note = $order->getNote();

		$link = '<a class="mws_order_change_note mw_setting_action_link" href="#" '
			. ($note ? '' : 'style="display: none" ')
			. '>' . __('Upravit', 'mwshop') . '</a>';

		$link .= '<div class="mws_order_note_save_cancel_link" style="display: none">'
			. '<a class="mws_order_save_note mw_setting_action_link" href="#" data-id="' . $order->getId() . '">' . __('Uložit', 'mwshop') . '</a>'
			. ' | '
			. '<a class="mws_order_cancel_note mw_setting_action_link" href="#">' . __('Zrušit', 'mwshop') . '</a>'
			. '</div>';

		$content = mwAdminComponents::title([
			'text' => __('Poznámka', 'mwshop'),
			'onright' => $link,
		]);

		$content .= '<div id="mws_order_note" class="mw_setting_sidebar_info_row">'
					. '<div id="mws_order_note_text" '
					. ($note ? '' : 'style="display: none" ')
					. '>' . str_replace("\n", '<br>', $note ?? '') . '</div>'
					. '<textarea id="mws_order_note_input" rows="7" style="display: none"></textarea>'
					. '<a class="mws_order_add_note mw_setting_action_link" href="#" '
					. ($note ? 'style="display: none" ' : '')
					. '>+ ' . __('Přidat poznámku', 'mwshop') . '</a>'
					. '</div>';

		return $content;
	}

	function saveOrderNote_ajax()
	{
		$order = OrderRepository::getOneById((int) $_POST['postid']);

		if ($order !== null) {
			$order->setNote($_POST['value']);
			$order->save();
		}
		die();
	}

	private function hasPhysicalProduct(?Order $order): bool
	{
		foreach ($order->getItems()->getAll() as $item) {
			if (MwsProductType::isPhysical($item->getType())) {
				return true;
			}
		}

		return false;
	}

}

$mwsOrderAdmin = new MwsOrderAdmin();

class mwSettingObjectService_Order extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Objednávka', 'cms'),
				],
				[
					'content' => __('Zákazník', 'cms'),
				],
				[
					'content' => __('Platba a Doprava', 'cms'),
				],
				[
					'content' => __('Cena', 'cms'),
					'align' => 'right',
				],
				[
					'content' => '',
				],
				[
					'content' => '',
					'align' => 'right',
				],
			],
		];
		$filter = $this->object()->getSavedListFilter();

		$orderStatuses = isset($filter['show']) && (bool) $filter['show'] ? explode(',', $filter['show']) : null;

		$search = $filter['s'] ?? '';
		$archive = isset($filter['archives']) && (bool) $filter['archives'];
		$offset = ($page - 1) * $perPage;

		if ($archive) {
			$action = ['edit', 'renew'];
			$request = new MwsOrderFetchRequest(
				$perPage,
				$offset,
				$orderStatuses,
				$search,
				hasArchive: true,
				orderDirection: 'DESC'
				);
		} else {
			$request = new MwsOrderFetchRequest($perPage, $offset, $orderStatuses, $search, hasArchive: false, orderDirection: 'DESC');
			$action = ['edit', 'createArchive', 'delete'];
		}


		$query = MwsOrderAdmin::getOrders($request);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		/** @var Order $order */
		foreach ($query['items'] as $order) {
			$price = $order->getPrice();
			$printPrice = '';
			if ($price) {
				$unit = MwsCurrencyEnum::getSymbol($price->getCurrency());
				$printPrice = htmlPriceSimpleIncluded($price->getPriceVatIncluded(), $unit);
			}

			$items_list = '<table><tbody>';
			foreach ($order->getItems()->getAll() as $item) {
				$item_price = $item->getPrice($order->getCurrency());
				$print_price = htmlPriceSimpleIncluded(
					$item_price === null ? 0 : $item_price->getPriceVatIncluded(),
					$item_price !== null ? MwsCurrencyEnum::getSymbol($item_price->getCurrency()) : null,
				);

				$items_list .=
					'<tr>' .
					'<td>' . $item->getCount() . ' x ' . $item->getName() . '</td>' .
					'<td class="text_right">' . $print_price . '</td>' .
					'</tr>';
			}
			$items_list .= '</tbody></table>';

			$order_title = '<div class="mws_order_list_title">';
			$order_title .= mwAdminComponents::icon([
					'icon' => MwsOrderStatus::getIcon($order->getStatus()),
			], 'mws_order_list_status_' . $order->getStatus());
			$order_title .= '<div class="mws_order_list_title_content">';
			$order_title .= mwAdminComponents::tooltip([
				'type' => 'text',
				'icon' => '<a class="mw_link" href="' . $this->object()->getEditUrl($order->getId()) . '">' . $order->getNumber() . '</a> ',
				'text' => $items_list,
				'tooltip_align' => 'bottom_right',
			]);
			$order_title .= '<span class="mws_setting_list_date">' . mwPrintDate($order->getCreatedAt()->getTimestamp(), 'datetime', true) . '</span>';
			$order_title .= '</div>';
			$order_title .= $order->isTest() ? mwAdminComponents::textLabel(['text' => __('Testovací', 'cms')]) : '';
			$order_title .= '</div>';

			$customerNote = '';
			if ($order->getCustomerNote()) {
				$customerNote = mwAdminComponents::tooltip([
					'icon' => mwAdminComponents::icon(['icon' => 'message-circle']),
					'text' => str_replace("\n", '<br>', $order->getCustomerNote()),
					'type' => 'text',
				], 'mws_order_list_customer_note');
			}

			$note = '';
			if ($order->getNote()) {
				$note = mwAdminComponents::tooltip([
					'icon' => mwAdminComponents::icon(['icon' => 'info']),
					'text' => str_replace("\n", '<br>', $order->getNote()),
					'type' => 'text',
					'tooltip_align' => 'left',
				], 'mws_order_list_note');
			}

			$invoice = '';
			$orderLive = $order->getGateLive();
			if ($orderLive && !empty($docs = $orderLive->getDocuments())) {
				foreach ($docs as $doc) {
					$documentUrl = $doc->getDownloadUrl();
				}
				$invoice = mwAdminComponents::iconLink([
					'icon' => 'file-text',
					'target' => '_blank',
					'link' => $documentUrl,
					'title' => __('Faktura', 'cms'),
				], 'mws_order_list_invoice');
			}

			$packeta = MWS()->packeta->isOrderWithPacketa($order);
			$shipping = $order->getShipping()['name'] ?? '';
			$status = 'inprogress';
			if ($packeta) {
				$packetaInfo = $order->getPacketData();

				if (isset($packetaInfo['id'])) {
					$status = MWS()->packeta->getPacketaStatus($packetaInfo) === 11 || MWS()->packeta->getPacketaStatus($packetaInfo) === -1 ? 'fail' : 'completed';
				}
			} elseif ($order->getStatus() === 10) {
				$status = 'completed';
			}

			$shipping = '<div class="mws_order_list_shipping mws_order_list_shipping_' . $status . '">' . $shipping . '</div>';

			$args['rows'][] = [
				'bulk_id' => $order->getId(),
				'class' => $order->isOpened() ? '' : 'mws_order_list_not_opened',
				'cols' => [
					[
						'content' => $order_title,
					],
					[
						'content' => ($order->getInvoiceContact() ? $order->getInvoiceContact()->getPerson() ? $order->getInvoiceContact()->getPerson()->getFullName() : '' : '') . $customerNote,
					],

					[
						'content' => '<div class="mws_order_list_payment ' . ($order->isPaid() ? 'mws_order_list_payment_paid' : '') . '">' . $order->getPaymentTitle() . '</div>'
										. $shipping,
					],
					[
						'content' => $printPrice,
						'align' => 'right',
					],
					[
						'content' => $note,
						'align' => 'right',
					],
					[
						'content' => $invoice,
						'align' => 'right',
					],
					[
						'content' => mwSetting::printSettingActions($action, $order->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function printTitle($item = null): string
	{
		// TODO refactor
		\assert($item instanceof Order || $item === null);

		if (isset($_GET['edit'])) {
			if ($item) {
				$args = [
					'text' => __('Objednávka č.', 'mwshop') . ' ' . $item->getNumber(),
					'description' => $item->isArchived() && $item->getArchivedAt() !== null ? sprintf(__('Archivováno dne: %s', 'cms'), mwPrintDate($item->getArchivedAt()->getTimestamp(), 'date', true)) : '',
				];
			} else {
				$args = [
					'text' => __('Objednávka č.', 'mwshop') . ' ?',
				];
			}
		} else {
			$args = [
				'text' => $this->object()->getLabel('title'),
			];
		}

		$content = mwAdminComponents::title($args, 'h2');

		return $content;
	}

	/** @param Order $order */
	public function printForm($order, $add = false)
	{
		// set order as opened
		if (!$order->isOpened()) {
			$order->setAsOpened();
			$order->save();
		}

		echo '<div class="mw_setting_object_detail_content ' . ($order->isCancelled() ? 'mws_order_cancelled' : '') . '">';
		echo '<div class="mw_setting_object_detail_form">';

		echo '<div class="mw_setting_box">';
		echo '<div class="mws_order_content">';

		if ($order->getGateIdentifier() == 'fapi') {
			echo mwAdminComponents::messageBox(__('Objednávka byla vytvořena pomocí FAPI. Pokud chcete objednávku upravit, je třeba upravit příslušnou fakturu ve FAPI.', 'mwshop'), ['type' => 'info']);
		}

		$info = '<div>' . __('Přijato:', 'mwshop') . ' ' . mwFormatAsDateTime($order->getCreatedAt()->getTimestamp()) . '</div>';

		if ($order->getGateIdentifier() !== 'fapi' && $order->getSource() !== null && $order->getSource()->getType() == 'form') {
			$form = MwsForm::getOneById($order->getSource()->getFormId());
			if (mwSetting()->getObject('mwsform')) {
				$info .= '<div>' . __('Formulář:', 'mwshop') . ' <a href="' . mwSetting()->getObject('mwsform')->getEditUrl($form->getId()) . '" target="_blank">formulář ' . $form->getName() . '</a></div>';
			}
		}

		if ($info) {
			echo '<div class="mws_order_main_info">' . $info . '</div>';
		}

		$orderLive = $order->getGateLive();

		if (!$orderLive) {
			echo mwAdminComponents::messageBox(sprintf(__('Nepodařilo se načíst data z [%s].', 'mwshop'), $order->getGateIdentifier()), ['type' => 'error']);
			echo '</div>'; //mws_order_content div
		} else {
			echo '<div class="mws_order_customer_info">';

			MwsOrderAdmin::printOrderCustomerInfo($order);

			echo '</div>'; //mws_order_customer_info div

			// customer note
			if ($order->getCustomerNote()) {
				echo '<div class="mws_order_customer_note">'
				. mwAdminComponents::title(['text' => __('Poznámka zákazníka', 'mwshop')])
				. '<p>' . wpautop(esc_html($order->getCustomerNote())) . '</p>'
				. '</div>';
			}

			echo '</div>'; //mws_order_content div

			$items = $orderLive->getItems();
			if ($items) {
				$currency = $orderLive->getCurrency();
				$unit = MwsCurrencyEnum::getSymbol($currency);
				$showVat = $order->showVat();

				echo '<table class="mws_order_products"><thead><tr>'
				. '<th align="left">' . __('Položka', 'mwshop') . '</th>'
				. '<th align="right">' . __('Množství', 'mwshop') . '</th>'
				. '<th align="right">' . __('Cena za položku', 'mwshop') . '</th>'
				. ($showVat ? '<th align="right">' . __('DPH', 'mwshop') . '</th>' : '')
				. '<th align="right">' . __('Celkem', 'mwshop') . '</th>'
				. '</tr></thead><tbody>';
				foreach ($items as $item) {
					echo '<tr>'
					. '<td>' . (($product = $item->getProduct()) ? ('<a href="' . $product->getEditUrl() . '" target="_blank">' . esc_html($item->getName()) . '</a>') : esc_html($item->getName())) . '</td>'
					. '<td align="right">' . $item->getCount() . '</td>'
					. '<td align="right">' . $item->getPrice($currency)->htmlPriceVatIncluded(1, false) . '</td>'
					. ($showVat ? '<td align="right">' . $item->getPrice($currency)->getVatPercentage() . '&nbsp;%</td>' : '')
					. '<td align="right">' . $item->getTotalPrice($currency)->htmlPriceVatIncluded(1, false) . '</td>'
					. '</tr>';
				}

				echo '</tbody>';
				echo '<tfoot>';
				$discountCode = $order->getDiscountCode();
				if (isset($discountCode['code'])) {
					echo '<tr>';
					echo '<td colspan="3">' . __('Použitý slevový kód', 'mwshop') . '</td>';
					echo '<td colspan="2">';
					echo $discountCode['code'];
					echo '</td>';
					echo '</tr>';
				}

				if ($orderLive->getPrice()) {
					$priceIncludingVat = $orderLive->getPrice()->getPriceVatIncluded();
				}
				echo '<tr>';
				echo '<td colspan="3">' . __('Celkem', 'mwshop') . '</td>';
				echo '<td colspan="2">';
				if (isset($priceIncludingVat)) {
					echo '<strong>' . htmlPriceSimpleIncluded($priceIncludingVat, $unit) . '</strong>';
					/*
					if ($orderLive->getNativePrice() && $orderLive->getNativePrice()->getPriceVatIncluded() !== $priceIncludingVat) {
						$nativeUnit = MwsCurrencyEnum::getSymbol($order->getNativeCurrency());
						echo '<div>'.htmlPriceSimpleIncluded($orderLive->getNativePrice()->getPriceVatIncluded(), $nativeUnit).'</div>';
					}*/
				} else {
					echo __('(chyba při zjišťování hodnoty)', 'mwshop');
				}
				echo '</td>';
				echo '</tr>';

				if ($order->isReverseChargeApplied()) {
					echo '<tr>';
					echo '<td colspan="5">' . __('Daň odvede zákazník', 'mwshop') . '</td>';
					echo '</tr>';
				}
				echo '</tfoot>';
				echo '</table>';
			} else {
				echo '<div class="mws_order_content">' . __('Objednávka neobsahuje žádné položky.', 'mwshop') . '</div>';
			}
		}
		echo '</div>';

		// order history
		echo '<div class="mw_setting_box">';
		echo '<div class="mw_setting_box_head">' . __('Historie objednávky', 'mwshop') . '</div>';
		echo '<div class="mw_setting_box_content">';
		echo '<table class="mws_order_history">';
		$historyItems = array_reverse($order->getHistory(), true);
		foreach ($historyItems as $historyItem) {
			$user = '';
			$userId = $historyItem->getUserId();

			if ((bool) $userId) {
				$mwUser = mwUser::getOneById($userId);
				$user = $mwUser === null ? '' : ' (<a href="' . $mwUser->getEditUrl() . '" target="_blank">' . $mwUser->getName() . ')';
			}

			$timestamp = $historyItem->getCreatedAt()->getTimestamp();
			echo '<tr><td>' . mwPrintDate($timestamp, 'datetime', true) . '</td>' . '<td>' . $historyItem->getText() . $user . '</td></tr>';
		}
		$overeno = get_post_meta($order->getId(), 'heureka_overeno_zakazniky', true);
		if (!empty($overeno) && is_array($overeno)) {
			foreach ($overeno as $date => $value) {
				echo '<tr><td>' . mwFormatAsDateTime(mwConvDateTimeUTC2TimestampUTC($date)) . '</td>' . '<td>' . $value . '</td></tr>';
			}
		}
		echo '<tr><td>'
		. mwFormatAsDateTime($order->getCreatedAt()->getTimestamp())
		. '</td>'
		. '<td>' . __('Objednáno', 'mwshop') . '</td></tr>';
		echo '</table>';
		echo '</div>';
		echo '</div>';

		echo '</div>';

		echo $this->printFormSidebar($order, $add);

		echo '</div>';

		if (defined('MW_SHOW_DEBUGS') && MW_SHOW_DEBUGS) {
			if ($order) {
				echo '<h3>GateLive</h3><pre>' . esc_html(print_r($order->toRowArray(), true)) . '</pre>';
			}
			echo '<h3>GateOrderData</h3><pre>' . esc_html(print_r($order->getGateOrderData(), true)) . '</pre>';
		}
	}

	/** @param Order $order */
	function printFormSidebar($order, $add = false, $inPopup = false): string
	{
		$content = '<div class="mw_setting_object_detail_sidebar">';

		// status sidebar box
		$content .= '<div class="mw_setting_sidebar_box">';

		$curStatus = $order->getStatus();

		$content .= '<div class="mws_order_status_container">';
		$content .= MwsOrderAdmin::orderStatus($order->getStatus(), $order->getId(), $order->isArchived());
		$content .= '</div>';

		$payInfo = '<div class="mw_setting_sidebar_info_row">';
		$payInfo .= '<span>' . __('Platební metoda', 'mwshop') . ':</span>';
		$payInfo .= '<span>' . ($order->getPayment()['name'] ?? MwsPayType::getCaption($order->getPayment()['type'] ?? '')) . '</span>';
		$payInfo .= '</div>';
		$payInfo .= '<div class="mw_setting_sidebar_info_row mws_order_paidon_info ' . ($order->isPaid() ? '' : 'cms_nodisp') . '">';
		$payInfo .= '<span>' . __('Zaplaceno', 'mwshop') . ':</span>';
		$payInfo .= '<span class="mw_setting_paidon_date_container">' . $order->getPaidAtDateFormatted() . '</span>';
		$payInfo .= '</div>';

		$content .= '<div class="mws_order_hide_on_cancel">';
			// Shipping
			if ($order->getShipping()) { // back compatibility condition
			$packeta = MWS()->packeta->isOrderWithPacketa($order);

			$content .= '<div class="mw_setting_sidebar_info">';
			$content .= '<div class="mw_setting_sidebar_info_row mws_order_closed_info ' . ($curStatus != MwsOrderStatus::Closed ? 'cms_nodisp' : '') . '">';
			$closedTimestamp = $order->getLastHistoryTimestamp(MwsOrderEvent::OrderStatusChangeToClosed);
			if ($closedTimestamp) {
				$content .= '<span>' . __('Vyřízeno', 'mwshop') . ':</span>';
				$content .= '<span>' . mwPrintDate($closedTimestamp, 'datetime', true) . '</span>';
			}
			$content .= '</div>';
			$content .= '<div class="mw_setting_sidebar_info_row">';
			$content .= '<span>' . __('Doručení', 'mwshop') . ':</span>';
			$content .= '<span>' . $order->getShipping()['name'] . ($packeta ? ' (' . __('Zásilkovna', 'mwshop') . ')' : '') . '</span>';
			$content .= '</div>';
			if ($order->getTotalWeight()) {
				$content .= '<div class="mw_setting_sidebar_info_row">';
				$content .= '<span>' . __('Váha', 'mwshop') . ':</span>';
				$content .= '<span>' . $order->getTotalWeight() . ' ' . __('kg', 'mwshop') . '</span>';
				$content .= '</div>';
			}


			if ($order->getGateIdentifier() == 'fapi') {
				$content .= $payInfo;
			}

			$content .= '</div>';

			if ($packeta && $order->getGateLive()) {
				$content .= MWS()->packeta->generatePacketaBlock($order);
			}
			}

			// payment
			if ($order->getGateIdentifier() != 'fapi') {
			$content .= '<div class="mws_order_payed_status_container">';
			$content .= MwsOrderAdmin::payedStatus($order->isPaid(), $order->getId(), $order->isArchived());
			$content .= '</div>';
			$content .= '<div class="mw_setting_sidebar_info">';
			$content .= $payInfo;
			$content .= '</div>';
			}
		$content .= '</div>';
		$content .= $order->isArchived() ? $this->removeAndShowDateOfArchive($order) : $this->getDetailActionList($order);



		/*
		if($order->getGateId()) {
		$gate = MWS()->gateways()->getById($order->getGateId());
		if ($gate) {
		$gateCaption = $gate->caption;
		echo '<tr><td>'.__('Platební brána', 'mwshop').'</td><td>'.$gateCaption.'</td></tr>';
		}
		}*/

		//  echo mwsOrder_formatField(__('Zaplaceno', 'mwshop'),
		//      ($order->isPaid ? __('ano', 'mwshop') : __('ne', 'mwshop'))
		//  );

		$content .= '</div>';

		// status sidebar box

		$content .= '<div class="mw_setting_sidebar_box">';
		$content .= '<div class="mw_setting_invoice_sidebar_container">';
		$content .= MwsOrderAdmin::invoicesList($order);
		$content .= '</div>';
		$content .= '</div>';

		$content .= '<div class="mw_setting_sidebar_box">';
		$content .= '<div class="mws_order_note_container">';
		$content .= MwsOrderAdmin::orderNote($order);
		$content .= '</div>';
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}


	private function removeAndShowDateOfArchive(Order $order): string
	{
		$content = '<ul class="mw_setting_detail_action_list">';
		$content .= '<li>';
		$content .= mwAdminComponents::link([
			'text' => __('Obnovit objednávku', 'mwshop'),
			'attrs' => 'data-id="' . $order->getId() . '" data-objectid="' . $this->object()->getId() . '"',
		], 'mw_setting_action_link mw_setting_detail_remove_archive_item');
		$content .= '</li>';
		$content .= '<li>';
		$content .= '<span>' . sprintf(__('(archivováno dne: %s)', 'cms'), mwPrintDate($order->getArchivedAt()->getTimestamp(), 'date', true)) . '</span>';
		$content .= '</li>';
		$content .= '</ul>';

		return $content;
	}


	public function getDetailActionList($order): string
	{
		$content = '<ul class="mw_setting_detail_action_list">';
		$content .= '<li>';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'file',
			'text' => __('Archivovat objednávku', 'mwshop'),
			'attrs' => 'data-id="' . $order->getId() . '" data-objectid="' . $this->object()->getId() . '"',
		], 'mw_setting_action_link mw_setting_detail_archive_item');
		$content .= '</li>';
		$content .= '<li>';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'trash-2',
			'text' => __('Smazat objednávku', 'mwshop'),
			'attrs' => 'data-id="' . $order->getId() . '" data-objectid="' . $this->object()->getId() . '"',
		], 'mw_setting_action_link mw_setting_detail_delete_item');
		$content .= '</li>';
		$content .= '</ul>';

		return $content;
	}
	public function printSaveBar()
	{
	}

	public function exportForm(): string
	{
		$content = '<div class="mws_export_form">';
		// Status
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Stav objednávky', 'mwshop')]);
		$content .= MwsOrderStatus::getCheckList(['name' => 'order_status']);
		$content .= '</div>';
//		// Payment status
//		$content .= '<div class="set_form_row">';
//		$content .= mwAdminComponents::inputLabel(['label' => __('Stav platby', 'mwshop')]);
//		$content .= MwsPaymentStatus::getCheckList(['name' => 'payment_status']);
//		$content .= '</div>';

		// Is paid
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Stav platby', 'mwshop')]);
		$content .= mwAdminComponents::select([
			'name' => 'is_paid',
			'options' => [
				['value' => null, 'name' => ''],
				['value' => '1', 'name' => __('Zaplaceno', 'mwshop')],
				['value' => '0', 'name' => __('Nezaplaceno', 'mwshop')],
			],
		]);
		$content .= '</div>';

		// Source
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Zdroj', 'mwshop')]);
		$content .= MwsOrderSource::getSelect(['name' => 'source']);
		$content .= '</div>';

		// Dates
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Datum', 'mwshop')]);
		$content .= mwAdminComponents::select([
			'name' => 'date_type',
			'options' => [
				['value' => null, 'name' => ''],
				['value' => MwsOrderFetchRequest::DATE_TYPE_ISSUED_AT, 'name' => __('Datum vystavení', 'mwshop')],
				['value' => MwsOrderFetchRequest::DATE_TYPE_PAID_AT, 'name' => __('Datum zaplacení', 'mwshop')],
			],
		]);
		$content .= '<div class="mws_export_form__dates cms_nodisp">';
		$content .= '<div class="set_form_subrow">';
		$content .= mwAdminComponents::inputSublabel(['label' => __('Od', 'mwshop')]);
		$content .= mwAdminComponents::input([
			'name' => 'date_from',
			'type' => 'date',
		]);
		$content .= '</div>';

		$content .= '<div class="set_form_subrow">';
		$content .= mwAdminComponents::inputSublabel(['label' => __('Do', 'mwshop')]);
		$content .= mwAdminComponents::input([
			'name' => 'date_to',
			'type' => 'date',
		]);
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</div>';

		// Number
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Číslo objednávky', 'mwshop')]);
		$content .= mwAdminComponents::input(['name' => 'order_number']);
		$content .= '</div>';

		// Currency
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Měna', 'mwshop')]);
		$content .= MwsCurrencyEnum::getSelect([
			'name' => 'currency',
			'with_empty' => true,
		]);
		$content .= '</div>';

		// Payment method
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Způsob platby', 'mwshop')]);
//		$content .= MwsPayType::getCheckList(['name' => 'pay_type']);
		foreach (MwsPaymentMethod::getAll(['post_status' => 'any']) as $paymentMethod) {
			$content .= mwAdminComponents::checkbox([
				'name' => 'payment_method[]',
				'value' => $paymentMethod->getId(),
				'label' => $paymentMethod->getName(),
			], true);
		}
		$content .= '</div>';

		// Shipping method
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Způsob doručení', 'mwshop')]);
//		$content .= MwsShippingType::getCheckList(['name' => 'shipping_type']);
		$shippingMethods = MwsShipping::getAll(['post_status' => 'any']);
		$shippingMethods[] = MwsShippingElectronic::getInstance();
		foreach ($shippingMethods as $shippingMethod) {
			$content .= mwAdminComponents::checkbox([
				'name' => 'shipping_method[]',
				'value' => $shippingMethod->getId(),
				'label' => $shippingMethod->getName(),
			], true);
		}
		$content .= '</div>';

		// Is test
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel(['label' => __('Testovací objednávky', 'mwshop')]);
		$content .= mwAdminComponents::checkbox([
			'name' => 'include_test',
			'label' => __('Exportovat i testovací objednávky', 'mwshop'),
		]);
		$content .= '</div>';

		// Exporter
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('Formát exportu', 'mwshop'),
		]);
		$exporters = MWS()->getExporterContainer()->getAll();
		$options = array_map(static function (IOrderExporter $exporter): array {
			return [
				'value' => $exporter->getIdentifier(),
				'name' => $exporter->getName(),
			];
		}, $exporters);
		$content .= mwAdminComponents::select([
			'name' => 'format',
			'options' => $options,
		]);
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	/** @param mixed[] $args */
	public function createExport(string $format, array $args): mwObjectExport
	{
		$orderStatuses = $args['order_status'] ?? [];
		$exporter = MWS()->getExporterContainer()->getByIdentifier($format);
		$isPaid = isset($args['is_paid']) && $args['is_paid'] !== '' ? (bool) $args['is_paid'] : null;
		$source = isset($args['source']) && $args['source'] !== '' ? (int) $args['source'] : null;
		$dateType = isset($args['date_type']) && $args['date_type'] ? (string) $args['date_type'] : null;
		$dateFrom = isset($args['date_from']) && $args['date_from'] ? new \DateTimeImmutable($args['date_from']) : null;
		$dateTo = isset($args['date_to']) && $args['date_to'] ? (new \DateTimeImmutable($args['date_to']))->setTime(23, 59) : null;
		$orderNumber = $args['order_number'] ?: null;
		$currency = $args['currency'] ?: null;
		$paymentMethods = array_map('intval', $args['payment_method'] ?? []);
		$shippingMethods = array_map('intval', $args['shipping_method'] ?? []);
		$includeTestOrders = (bool) ($args['include_test'] ?? false);

		if ($dateFrom > $dateTo && $dateTo !== null) {
			throw new MwsUserException(__('Datum "Od" nesmí být později než datum "Do".', 'mwshop'));
		}

		$request = new MwsOrderFetchRequest(-1, null, $orderStatuses, '', $isPaid, $source, $dateType, $dateFrom, $dateTo, $orderNumber, $currency, $paymentMethods, $shippingMethods, $includeTestOrders, 'mioweb', null, 'ASC');
		$orders = MwsOrderAdmin::getOrdersGenerator($request);

		$now = new \DateTimeImmutable('now', wp_timezone());
		$fileExtension = $exporter->getFileExtension();
		$attachmentFileName = 'objednavky.' . $now->format('Y-m-d-H-i-s') . ($fileExtension ? '.' . $fileExtension : '');
		$content = $exporter->export($orders);

		return new mwObjectExport($content, $fileExtension, $attachmentFileName);
	}

	public function delete($id, $force_delete = false)
	{
		$order = OrderRepository::getOneById((int) $id);
		if ($order !== null) {
			OrderRepository::delete($order);
		}

		wp_delete_post($id, true); // Deprecated, can be removed in future
	}

	public function deArchive(int $id): bool
	{
		$order = OrderRepository::getOneById($id);
		if ($order !== null) {
			if ($order->isArchived()) {
				$order->setArchived(false);
				$order->setArchivedAt(null);
				$order->addHistory(__('Archivace objednávky zrušena', 'mwshop'), MwsOrderEvent::OrderDeArchived);

				return $order->save();
			}
		}

		return false;
	}

	public function createArchive(int $id): bool
	{
		$order = OrderRepository::getOneById($id);

		if ($order !== null && !$order->isArchived()) {
			$order->setArchived(true);
			$now = (new \DateTimeImmutable('GMT'))->setTimezone(wp_timezone());
			$order->setArchivedAt($now);
			$order->addHistory(__('Objednávka archivována', 'mwshop'), MwsOrderEvent::OrderArchived);

			return $order->save();
		}

		return false;
	}


}
