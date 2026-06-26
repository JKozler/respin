<?php

use Mioweb\Shop\Exceptions\InvalidPacketSizeException;
use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\OrderRepository;
use Mioweb\Shop\Packeta\Exceptions\PacketaCreateException;
use Mioweb\Shop\PacketSize;
use Nette\Utils\Validators;

define('PACKETA_SOAP_URL', 'https://www.zasilkovna.cz/api/soap.wsdl');

// @TODO refactor
class MwsPacketa
{
	const MAX_LENGTH_NOTE = 32;

	protected $_login;

	function __construct($builder_mode = false)
	{
		$this->_login = mwApiConnect()->getApi('packeta')->getOption();

		if ($this->isConnected()) {
			if ($builder_mode && isset($_GET['mws_packeta_print_label']) && isset($_GET['skip_value'])) {
				$this->printLabelPdf($_GET['mws_packeta_print_label'], $_GET['skip_value']);
			}

			// ajax
			add_action('wp_ajax_mwsp_create_packet', [$this, 'createPacket_ajax']);
			add_action('wp_ajax_mwsp_print_packet', [$this, 'checkLabel_ajax']);
		}
	}

	function loadScripts($builder_mode = false)
	{
		// loaded in eshop module

		if ($this->isConnected()) {
			wp_register_script('mw_packeta_library_script', 'https://widget.packeta.com/www/js/library.js');
			wp_register_script('mw_packeta_script', get_bloginfo('template_url') . '/modules/shop/libs/packeta/packeta.js', ['jquery', 'mw_packeta_library_script'], '1');
			wp_localize_script('mw_packeta_script', 'mws_packeta_login', $this->_login);

			if ($builder_mode || is_admin()) {
				wp_register_script('mw_packeta_admin_script', get_bloginfo('template_url') . '/modules/shop/libs/packeta/packeta_admin.js', ['jquery'], '1');
				wp_enqueue_script('mw_packeta_admin_script');
			}
		}
	}

	function isConnected(): bool
	{
		return $this->_login && isset($this->_login['status']) && $this->_login['status'];
	}

	public static function isOrderWithPacketa(Order $order): bool
	{
		return isset($order->getShipping()['type']) && in_array($order->getShipping()['type'], [MwsShippingType::PacketaCarriers, MwsShippingType::Packeta]);
	}

	public static function getCarriersList()
	{
		return require_once(__DIR__ . '/carriers.php');
	}

	function generatePacketaBlock(Order $order)
	{
		$content = '';
		if ($this->isConnected()) {
			$content = '<div class="mws_packeta_info_container">';
			$content .= $this->generatePacketaBlockContent($order);
			$content .= '</div>';
		} else {
			$content = '<div class="mws_packeta_info_container">';
			$content .= mwAdminComponents::messageBox(sprintf(__('Nelze spravovat zásilky, web není propojen se zásilkovnou. Prosím zkontrolujte propojení v&nbsp;<a href="%s" target="_blank">propojení aplikací</a>.', 'cms_ve'), get_mw_admin_url('ve_connect')), ['type' => 'error']);
			$content .= '</div>';
		}

		return $content;
	}

	function generatePacketaBlockContent(Order $order = null)
	{
		$content = '';
		$packeta = $order->getPacketData();

		$packetaId = $packeta['id'] ?? null;

		if ($packetaId !== null) {
			if ($this->getPacketaStatus($packeta) === 11) {
				$status = 'fail';
				$statusText = __('Zásilka stornována', 'mwshop');
				$link = $this->generatePacketaLink($order);
			} else {
				$status = 'ok';
				$statusText = __('Zásilka vytvořena', 'mwshop');
				$link = '';
			}
		} else {
			$status = 'non';
			$statusText = __('Zásilka nevytvořena', 'mwshop');
			$link = $this->generatePacketaLink($order);
		}
		if ($order->isArchived()) {
			$link = null;
		}
		$content .= mwAdminComponents::statusField([
			'title' => __('Zásilkovna', 'mwshop'),
			'link' => $link,
			'text' => $statusText,
			'status' => $status,
		], 'mws_order_packeta_status');

		$content .= '<div class="mw_setting_sidebar_info">';
		if ($packetaId) {
			$content .= '<div class="mw_setting_sidebar_info_row">';
			$content .= '<span>' . __('Štítek', 'mwshop') . ':</span>';
			$content .= '<span><a class="mws_packeta_prepare_print mw_setting_action_link"
 			data-packeta="' . $packetaId . '"
			data-url ="' . get_home_url() . '?mws_packeta_print_label=' . $packetaId . '"
			href="#">' . __('Tisk štítku', 'mwshop') . '</a></span>';
			//$content .= '<span><a target="_blank" href="' . get_home_url() . '?mws_packeta_print_label=' . $packeta->id . '">Tisk štítku</a></span>';
			$content .= '</div>';
			$content .= '<div class="mw_setting_sidebar_info_row">';
			$content .= '<span>' . __('Stav zásilky', 'mwshop') . ':</span>';
			$content .= '<span>' . $this->getPacketaInfo($packeta) . '</span>';
			$content .= '</div>';
			$content .= $this->generatePacketaPrint();
		}
		if (isset($order->getShipping()['pickupAddress']) && $order->getShipping()['pickupAddress']) {
			$content .= '<div class="mw_setting_sidebar_info_row">';
			$externalId = $order->getShipping()['externalId'] ?? '';
			$content .= '<div class="mws_pickup_external_id cms_nodisp" data-external-id="' . $externalId . '">' . $externalId . '</div>';
			$content .= '<span>' . __('Výdejní místo', 'mwshop') . ':</span>';
			$content .= '<span>' . str_replace(', ', '<br>', $order->getShipping()['pickupAddress']) . '</span>';
			$content .= '</div>';
			$content .= '<div class="mw_setting_sidebar_info_row">';
			$content .= '<span>' . __('ID výdejního místa', 'mwshop') . ':</span>';
			$content .= '<span>' . $externalId . '</span>';
			$content .= '</div>';
		}
		$content .= '</div>';

		$content .= '<div class="mws_create_packeta_form_container cms_nodisp">';
		$content .= '<h3>' . __('Vytvořit zásilku', 'mwshop') . '</h3>';
		$content .= '<table class="mw_table mws_order_popup_form_table">';
		$content .= '<tr><td>' . __('Způsob doručení', 'mwshop') . ':</td><td>' . $order->getShipping()['name'] . '</td></tr>';
		if (isset($order->getShipping()['pickupAddress']) && $order->getShipping()['pickupAddress']) {
			$content .= '<tr><td>' . __('Výdejní místo', 'mwshop') . ':</td><td>' . str_replace(', ', '<br>', $order->getShipping()['pickupAddress']) . '</td></tr>';
		}

		$weight = round($order->getTotalWeight(), 3);
		$itemsWithoutWeight = $order->getItemsWithoutWeight();

		// Render warnings
		foreach ($itemsWithoutWeight as $item) {
			$product = $item->getProduct();
			if ($product === null || !MwsProductType::isPhysical($product->getType())) {
				continue;
			}

			$content .= '<tr>';
			$content .= '<td colspan="2">';
			$content .= '<div class="mw_message_box mw_message_box_error mw_message_box_wclose">';
			$content .= mwAdminComponents::icon(['icon' => 'info'], 'mw_message_box_icon');
			$content .= '<div class="mw_message_box_text">';
			$content .= sprintf(
				__('Produkt <strong><a href="%s" title="%s" target="_blank">%s</a></strong> nemá nastavenou hmotnost.', 'mwshop'),
				$product->getEditUrl(),
				__('Editace produktu', 'mwshop'),
				$product->getName(),
			);
			$content .= '</div>';
			$content .= '</div>';
			$content .= '</td>';
			$content .= '</tr>';
		}

		$content .= '<tr><td>' . __('Hmotnost zásilky', 'mwshop') . ':</td><td>'
			. mwAdminComponents::inputNumber([
				'step' => 0.1,
				'unit' => 'kg',
				'attrs' => 'min="0"',
				'name' => 'order_weight',
			], $weight, 'mws_packeta_weight')
			. '</td></tr>';
		$content .= '<tr><td>' . __('Poznámka na štítek', 'mwshop') . ':</td><td>'
			. mwAdminComponents::input([
				'name' => 'packeta_note',
				'rows' => 1,
				'maxlength' => self::MAX_LENGTH_NOTE,
			], '', 'mws_packeta_note')
			. '</td></tr>';
		$content .= '<tr>';
		$content .= '<td></td>';
		$content .= '<td>';
		$content .= mwAdminComponents::checkbox([
				'name' => 'fill_order_size',
				'label' => __('Zadat rozměry zásilky', 'mwshop'),
				'style' => 'blue',
				'value' => 0,
		], false, 'mws_packeta_fill_order_size');
		$content .= '</td>';
		$content .= '</tr>';

		$sizeInputsArr = [
			'length' => __('Délka', 'mwshop'),
			'width' => __('Šířka', 'mwshop'),
			'height' => __('Výška', 'mwshop'),
		];

		foreach ($sizeInputsArr as $key => $label) {
			$content .= '<tr class="packeta_size_field" style="display: none">';
			$content .= '<td>' . mwAdminComponents::inputLabel(['label' => $label]) . '</td>';
			$content .= '<td>';
			$content .= mwAdminComponents::inputNumber([
				'step' => 0.1,
				'unit' => 'cm',
				'attrs' => 'min="0"',
				'name' => 'size[' . $key . ']',
			], '', 'mws_packeta_size_' . $key);
			$content .= '</td>';
			$content .= '</tr>';
		}

		$content .= '</td></tr>';
		$content .= '</table>';
		$content .= '</div>';

		return $content;
	}

	function generatePacketaLink(Order $order)
	{
		try {
			if ($order->getGateLive() === null) {
				throw new PacketaCreateException(__('Zásilku nelze vytvořit', 'mwshop'));
			}

			$invoiceContact = $order->getInvoiceContact();
			$price = $order->getPrice();
			$content = $invoiceContact && $price ? '<a class="mws_packeta_create_packet mw_setting_action_link" data-order="' . $order->getId() . '" href="#">' . __('Vytvořit zásilku', 'mwshop') . '</a>' : mwAdminComponents::messageBox(__('Zásilku nelze vytvořit', 'mwshop'), ['type' => 'error']);
		} catch (PacketaCreateException $e) {
			$content = mwAdminComponents::messageBox($e->getMessage(), ['type' => 'error']);
		} catch (\Throwable $e) {
			$content = mwAdminComponents::messageBox(__('Zásilku nelze vytvořit', 'mwshop'), ['type' => 'error']);
		}

		return $content;
	}

	private function generatePacketaPrint(): string
	{
		$content = '<div class="mws_print_packeta_form_container cms_nodisp">';
		$content .= '<h3>' . __('Tisk  štítku', 'mwshop') . '</h3>';
		$content .= '<div class="mw_modal_message_box">';
		$content .= '<div class="mw_message_box mw_message_box_error mw_message_box_wclose ">';
		$content .= mwAdminComponents::icon(['icon' => 'info'], 'mw_message_box_icon');
		$content .= '<div class="mw_message_box_text ">';
		$content .= __(' <strong>Došlo k chybě během vytváření pdf, prosím zkuste to později.</strong> ', 'mwshop');
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</br>';
		$content .= mwAdminComponents::inputLabel(['label' => __('Zvolit pořadí při tisku', 'mwshop')]);
		$content .= '</br>';
		$content .= mwAdminComponents::select([
			'name' => 'print_packeta',
			'options' => self::printSkipList(),
		], '', ' mws_print_packeta_form_position');

		$content .= '</div>';

		return $content;
	}

	function separateStreetAndNumber($address) // @TODO move to address
	{
		$number = '';

		$matches = [];
		if (preg_match('/(?P<street>.*) (?P<number>.*)/', $address, $matches)) {
			$street = $matches['street'];
			$number = $matches['number'];
		} else { // no whitespace found, it is only a house number (packeta requires it to be in the 'street' field for some reason)
			$street = $address;
		}

		return [
			'street' => $street,
			'number' => $number,
		];
	}

	/** @param mixed[] $data */
	function getPacketaInfo(array $data)
	{
		$client = new SoapClient(PACKETA_SOAP_URL);

		try {
			$packet = $client->packetStatus($this->_login['api_pas'], $data['id']);

			return $packet->statusText;
		} catch (SoapFault $e) {
			$err = $this->getError($e);

			return $err['errorMessage'] ?: __('Stav neznámý, vyskytla se neznámá chyba.', 'mwshop');
		}
	}

	/** @param mixed[] $data */
	function getPacketaStatus(array $data)
	{
		$client = new SoapClient(PACKETA_SOAP_URL);

		try {
			$packet = $client->packetStatus($this->_login['api_pas'] ?? '', $data['id']);

			return $packet->statusCode;
		} catch (SoapFault $e) {
			$err = $this->getError($e);

			return -1;
		}
	}

	function createPacket_ajax()
	{
		$order_id = $_POST['order_id'];
		$content = '';
		$attributes = [];
		$weight = isset($_POST['weight']) ? str_replace(',', '.', $_POST['weight']) : null;
		$fillSize = $_POST['fill_order_size'] ?? false;
		$note = $_POST['note'] ?? null;
		$packetSize = null;

		if ($fillSize) {
			try {
				$packetSize = new PacketSize(
					(float) ($_POST['size_length'] ?? 0.0),
					(float) ($_POST['size_width'] ?? 0.0),
					(float) ($_POST['size_height'] ?? 0.0)
				);
			} catch (InvalidPacketSizeException $e) {
				// ignore
			}
		}

		if ($weight === null || $weight === '') {
			mwMessages()->error(__('Vyplňte prosím celkovou hmotnost zásilky.', 'mwshop'));
		} elseif (!Validators::isNumeric($weight)) {
			mwMessages()->error(__('Celková hmotnost zásilky není platné číslo.', 'mwshop'));
		} elseif ((float) $weight <= 0.0) {
			mwMessages()->error(__('Celková hmotnost zásilky musí být kladné nenulové číslo.', 'mwshop'));
		} elseif ($fillSize && $packetSize === null) {
			mwMessages()->error(__('Zadejte prosím všechny 3 rozměry zásilky.', 'mwshop'));
		} else {
			$attributes['weight'] = (float) $weight;
			if ($packetSize !== null) {
				$attributes['size'] = $packetSize->toPacketaArray();
			}
			if ($note !== null) {
				$attributes['note'] = $note;
			}
			// Save user-checked total weight to the order
			$order = OrderRepository::getOneById($order_id);
			if ($order !== null) {
				if ($order->getGateLive() === null) {
					mwMessages()->error(__('Zásilku nelze vytvořit. Nepodařily se načíst data objednávky.', 'mwshop'));
				}

				$invoiceContact = $order->getInvoiceContact();
				$price = $order->getPrice();
				if ($invoiceContact && $price) {
					$shippingContact = $order->getShippingContact() ?: $invoiceContact;
					$shippingPerson = $shippingContact->getPerson();
					$invoicePerson = $invoiceContact->getPerson();
					$attributes += [
						'number' => $order->getNumber(),
						'name' => $shippingPerson ? $shippingPerson->getFirstName() : ($invoicePerson ? $invoicePerson->getFirstName() : ''),
						'surname' => $shippingPerson ? $shippingPerson->getLastName() : ($invoicePerson ? $invoicePerson->getLastName() : ''),
						'email' => $invoiceContact->getEmail(),
						'phone' => $invoiceContact->getPhone(),
						'addressId' => $order->getShipping()['externalId'],
						'value' => $price->getPriceVatIncluded(),
						'currency' => strtoupper($price->getCurrency()),
					];
					if ($order->getShipping()['type'] == MwsShippingType::PacketaCarriers) {
						//size
						//province
						$street = $shippingContact->getAddress()->getStreet();
						$address = $this->separateStreetAndNumber($street);
						$attributes['street'] = $address['street'];
						$attributes['houseNumber'] = $address['number'];
						$attributes['city'] = $shippingContact->getAddress()->getCity();
						$attributes['zip'] = $shippingContact->getAddress()->getZip();
					}

					$paymentType = $order->getPayment()['type'] ?? null;
					if ($paymentType === MwsPayType::Cod) {
						$attributes['cod'] = $price->getPriceVatIncluded();
					}
				}

				$attributes['eshop'] = $this->_login['sender'];

				$order->setTotalWeight($attributes['weight']);
				$order->setPacketSize($packetSize);
				// Create packet
				$packet = $this->createPacket($attributes);

				if ($packet['success'] && $packet['data']->id) {
					$order->setPacketData((array) $packet['data']);
					$order->addHistory(__('Byla vytvořena zásilka v Zásilkovně.', 'mwshop'), MwsOrderEvent::PacketaCreated);
					$order->setTrackingNumber($packet['data']->id);
					$content = $this->generatePacketaBlockContent($order);
				} else {
					$order->addHistory(__('Nepodařilo se vytvořit zásilku v Zásilkovně.', 'mwshop') . ($packet['errorMessage'] ?? ''), MwsOrderEvent::PacketaFailed);
					mwMessages()->error($packet['errorMessage'] ?: __('Zásilku nelze vytvořit, vyskytla se neznámá chyba.', 'mwshop'));
				}
				$order->save();
			} else {
				mwMessages()->error(__('Zásilku nelze vytvořit. Nepodařilo se načíst objednávku.', 'mwshop'));
			}
		}

		wp_send_json([
			'content' => $content,
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
		]);

		die();
	}

	function createPacket($attributes)
	{
		$client = new SoapClient(PACKETA_SOAP_URL);

		try {
			$packet = $client->createPacket($this->_login['api_pas'], $attributes);

			return [
				'success' => 1,
				'data' => $packet,
			];
		} catch (SoapFault $e) {
			return $this->getError($e);
		}
	}

	function printLabelPdf(string $packet_id, string $offset = '0')
	{
			$client = new SoapClient(PACKETA_SOAP_URL);
			$format = 'A6 on A4';

			try {
			$pdf = $client->packetLabelPdf($this->_login['api_pas'], $packet_id, $format, $offset);
			//$pdf = $client->packetCourierLabelPdf($apiPassword, $packetId, $courierNumber);
			header('Content-Type: application/pdf');
			echo $pdf;
			} catch (SoapFault $e) {
			mwMessages()->error(__('Dané parametry zásilky neodpovídají standardu API zásilkovny.', 'mwshop'));
			}
	}

	public static function getError($e)
	{
		if (isset($e->detail->PacketAttributesFault)) {
			if (is_array($e->detail->PacketAttributesFault->attributes->fault)) {
				$errorId = $e->detail->PacketAttributesFault->attributes->fault[0]->name;
				$errorMessage = $e->detail->PacketAttributesFault->attributes->fault[0]->fault;
			} else {
				$errorId = $e->detail->PacketAttributesFault->attributes->fault->name;
				$errorMessage = $e->detail->PacketAttributesFault->attributes->fault->fault;
			}
		} else {
			$errorId = 0;
			$errorMessage = $e->getMessage();
		}
		if ($errorMessage == 'Incorrect API password.') {
			$errorMessage = __('Nesprávné API heslo', 'cms_ve');
		}

		return [
			'success' => 0,
			'errorId' => $errorId,
			'errorMessage' => $errorMessage,
		];
	}

	private static function printSkipList(): array
	{
		return [
			['value' => 0, 'name' => __('Nevynechávat žádná pole', 'mwshop')],
			['value' => 1, 'name' => __('Vynechat 1 pole', 'mwshop')],
			['value' => 2, 'name' => __('Vynechat 2 pole', 'mwshop')],
			['value' => 3, 'name' => __('Vynechat 3 pole', 'mwshop')],
			['value' => 4, 'name' => __('Vynechat 4 pole', 'mwshop')],
			['value' => 5, 'name' => __('Vynechat 5 polí', 'mwshop')],
			['value' => 6, 'name' => __('Vynechat 6 polí', 'mwshop')],
			['value' => 7, 'name' => __('Vynechat 7 polí', 'mwshop')],
		];
	}


}

function field_type_packeta_carriers_select($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? 13);

	$items = MwsPacketa::getCarriersList();
	$options = [];

	foreach ($items as $val) {
		$options[] = [
			'value' => $val['id'],
			'name' => $val['name'] . ' (' . strtoupper($val['country']) . ')',
		];
	}

	$field['options'] = $options;

	cms_generate_field_select(
		$group_name . '[' . $field['id'] . ']',
		$group_id . '_' . $field['id'],
		$content,
		$field
	);
}
